<?php

namespace NewtonContent\Model;

use Newton\Image as NewtonImage;

/**
 * USAGE:
 *
 * $image = new Image($data, $attr, $options);
 * WHERE:
 *     data = $item->getData()
 *     attr = The name you used when doing $this->addImageElement(attr) in the form
 *     options = width, height, thumbnail and autofil. Usually you can ignore autofill,
 *               thumbnail should probably be true if the image should also be cropped to
 *               fill a specific area.
 *
 * Then, to output the image. Just do:
 * echo $image;
 *
 * If errors are being thrown, use echo $image->toHtml() to find what the error is 
 * because __toString cannot throw exceptions and echoing $image is just invoking the __toStirng 
 * method in this class.
 */

/**
 * This class has helper functions for working with images stored within the content model
 */
class Image
{
    /**
     * Options
     */
    protected $_options = array(
        'width'         => null,
        'height'        => null,
        'thumbnail'     => null,
        'autofill'      => null
    );  

    /**
     * [__construct description]
     * @param array  $data [description]
     * @param [type] $attr [description]
     */
    public function __construct($data = array(), $attr = null, $options = array())
    {
        // If its a StdObject, toArray on the data
        if($data instanceof \Newton\StdObject) {
            $data = $data->toArray();
        }
        $this->data = $data;
        $this->attr = $attr;

        $this->_options = $options;
    }

    /**
     * Sets the data
     * @param [type] $data [description]
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Sets Attr
     * @param [type] $attr [description]
     */
    public function setAttr($attr)
    {
        $this->attr = $attr;

        return $this;
    }

    /**
     * [__toString description]
     * @return string [description]
     */
    public function __toString()
    {
        return $this->toHtml();
    }

    /**
     * Loads a new instance of Content_Model_Image and sets the data and image attribute
     * @param  array  $data [description]
     * @param  string $attr [description]
     * @return Content_Model_Image               [description]
     */
    public static function load($data = array(), $attr = 'string')
    {
        return new static($data, $attr);
    }


    /**
     *  Gets the src of the image
     * @param  boolean $baseUrl [description]
     * @return [type]           [description]
     */
    public function src($baseUrl = false)
    {
        if(null !== ($src = $this->_get('src'))) {
            $src = $baseUrl ? BASEURL . $src : $src;
        } else {
            return null;
        }

        // If no width or height are specific, use original
        if(empty($this->_options['width']) && empty($this->_options['height'])) {
            return $src;
        }

        $src = urldecode($src);

        // Do we need to do any resizing etc?
        // If we're still here it means that if either width or height are empty,
        // then we need to suggest a value for the other. We'll use 2000 pixels.
        if(empty($this->_options['width'])) {
            $this->_options['width'] = 2000;
        }
        if(empty($this->_options['height'])) {
            $this->_options['height'] = 2000;
        }
        
        
        // Extensions
        $append    = '.';
        $eThumb    = '-thumb.';
        $eAutoFill = '-filled.';
        
        // Choose what we need to append
        if(true == $this->_options['thumbnail']) {
            $append = $eThumb;
        } else if (true == $this->_options['autofill']) {
            $append = $eAutoFill;
        }

        // split the name 
        $parts = pathinfo(PUBLIC_PATH . $src);
        
        // Choose the new filename
        $newFileName = $parts['filename'] . $append . $parts['extension'];
        
        // MD5 the options
        $cachedFolder = $this->_options['width'] . 'x' . $this->_options['height'];
        
        // Setup some directories
        $base                   = $parts['dirname'];
        $cachedFileName         = $parts['dirname'] . '/' . $cachedFolder . '/' . $newFileName;
        $cachedPublicFilename   = substr($parts['dirname'], strlen(PUBLIC_PATH), strlen($parts['dirname'])) . '/' . $cachedFolder . '/' . $newFileName;

        
        // Is this already cached?
        if(file_exists($cachedFileName)) {
            return $cachedPublicFilename;
        }
        
        // It's not cached, lets make the directory (if it doesn't already exist);
        if(!file_exists($parts['dirname'] . '/' . $cachedFolder)) {
            @mkdir($parts['dirname'] . '/' . $cachedFolder);
        }
        
        // Lets start procesing the cache...
        $image = new NewtonImage(PUBLIC_PATH . $src);
        
        // Thumbnail
        if($this->_options['thumbnail'] == true) {
            $image->thumbnail($this->_options['width'], $this->_options['height']);
        }
        
        else {
            // Autofill?
            if($this->_options['autofill']) {
                $image->setAutofill(true);
            } else {
                $image->setAutofill(false);
            }
            
            $image->resize($this->_options['width'], $this->_options['height']);
        }
        
        
        // Now save the image and return the path
        $image->save($cachedFileName);
        
        return $cachedPublicFilename;
    }


    /**
     * Returns the alt tag for the image
     * @param  string $default [description]
     * @return [type]          [description]
     */
    public function alt($default = '')
    {
        if(null !== ($alt = $this->_get('alt'))) {
            return $alt;
        }

        return $default;
    }


    /**
     * Converts the image to html
     * 
     * @param  array  $attributes [description]
     * @return [type]             [description]
     */
    public function toHtml($attributes = array()) 
    {
        $output = '<img src="' . $this->src() . '" alt="' . $this->alt() . '"';

        // Add any attributes
        foreach($attributes as $k => $v) {
            $output .= ' ' . $key . '="' . $v . '"';
        }

        // Add the width and height if specified
        if(!empty($this->_data['width']) && !empty($this->_data['height'])) {
            $output .= ' width="' . $this->_data['width'] . '" height="' . $this->_data['height'] . '"';
        }

        $output .= '/>';

        return $output;
    }


    /**
     * Gets an element by suffix
     * 
     * @param  [type] $suffix [description]
     * @return [type]         [description]
     */
    protected function _get($suffix)
    {
        if(isset($this->data[$this->attr . '_' . $suffix])
        && !empty($this->data[$this->attr . '_' . $suffix])) {
            return $this->data[$this->attr . '_' . $suffix];
        }

        return null;
    }


    public function baseSrc()
    {
        if(null !== ($src = $this->_get('src'))) {
            $src = $baseUrl ? BASEURL . $src : $src;
        } else {
            return null;
        }

        return $src;
    }

}