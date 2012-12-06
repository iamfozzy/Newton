<?php

namespace Newton;

/**
 * Newton Images - Image Resize, Crop
 *
 * @author Newton Ltd
 * @version $Id$
 * @copyright Gravitywell Ltd, 23 November, 2009
 * @package default
 **/ 
class Image
{
    /**
     * Image Resource
     *
     */
    protected $_image;
    
    /**
     * Image Type
     *
     */
    protected $_imageType;
    
    /**
     * Background color
     *
     */
    protected $_bgColor = array('255', '255', '255');
    
    /**
     * Border width
     *
     */
    protected $_borderWidth = 0;
    
    /**
     * Border Color
     *
     */
    protected $_borderColor = array('255', '255', '255');
    
    
    /**
     * Auto fill the image
     *
     */
    protected $_autofill = true;
    
    
    /**
     * Constructor, Can optionally take a filename for an 
     * image and automatically load it
     *
     * @param string $image 
     * @author Gravitywell Ltd
     */
    public function __construct($image = null)
    {
        if(!is_null($image)) {
            $this->load($image);
        }
    }
    
    
    /**
     * Loads the image and stores it
     *
     * @param string $filename 
     * @return void
     * @author Gravitywell Ltd
     */
    public function load($filename)
    {
        if(!file_exists($filename)) {
            throw new \Zend_Exception("Image doesn't exist.");
        }
        
        // Get some image information
        list($w, $h, $this->_imageType) = getimagesize($filename);
        
        // Switch on the image type and load the image
        switch($this->_imageType) {
            // JPEG
            case IMAGETYPE_JPEG: 
                $this->_image = imagecreatefromjpeg($filename);
                break;
            
            // GIF
            case IMAGETYPE_GIF:
                $this->_image = imagecreatefromgif($filename);
                break;
            
            // PNG
            case IMAGETYPE_PNG:
                $this->_image = imagecreatefrompng($filename);
                break;
        }
    }
    
    
    
    /**
     * Saves the image to disk
     *
     * @param string $filename 
     * @param string $filetype 
     * @param int $compression 
     * @param string $permissions 
     * @return void
     * @author Gravitywell Ltd
     */
    public function save($filename, $compression = 90, $permissions = null)
    {
        // Add any borders we've added
        $this->_addBorders();
        
        // Switch onthe type of image
        switch($this->_imageType) {
            case IMAGETYPE_JPEG:
                imagejpeg($this->_image, $filename, $compression);
                break;
            case IMAGETYPE_GIF:
                imagegif($this->_image, $filename);
                break;
            case IMAGETYPE_PNG:
                imagepng($this->_image, $filename);
                break;
        }
        
        // Permissions
        if(!is_null($permissions)) {
            chmod($filename, $permissions);
        }
    }
    
    
    /**
     * Output the image directly to the browser
     *
     * @return void
     * @author Gravitywell Ltd
     */
    public function output()
    {        
        // Add any borders we've added
        $this->_addBorders();
        
        // Switch onthe type of image
        switch($this->_imageType) {
            case IMAGETYPE_JPEG:
                header('Content-type: image/jpeg');
                imagejpeg($this->_image);
                break;
            case IMAGETYPE_GIF:
                header('Content-type: image/gif');
                imagegif($this->_image);
                break;
            case IMAGETYPE_PNG:
                header('Content-type: image/png');
                imagepng($this->_image);
                break;
        }
    }
    
    
    /**
     * Returns the width of the image
     *
     * @return void
     * @author Gravitywell Ltd
     */
    public function getWidth()
    {
        return imagesx($this->_image);
    }
    
    
    /**
     * Returns the height of the image
     *
     * @return void
     * @author Gravitywell Ltd
     */
    public function getHeight()
    {
        return imagesy($this->_image);
    }
    
    
    /**
     * Resizes the image to the specified dims, does not crop any of the image
     *
     * @param string $mwidth 
     * @param string $mheight 
     * @return void
     * @author Gravitywell Ltd
     */
    public function resize($mwidth = null, $mheight = null)
    {
        if(is_null($mwidth) || is_null($mheight)) {
            throw new \Zend_Exception('resize() requires both a max width and a max height passed.');
        }
        
        // Get the original Image ratio
        $originalRatio = $this->getWidth() / $this->getHeight();
        
        // Find the new width and height
        if ($mwidth/$mheight > $originalRatio) {
            $newHeight = $mheight;
            $newWidth = $mheight * $originalRatio;
        } else {
            $newWidth = $mwidth;
            $newHeight = $mwidth / $originalRatio;
        }
        
        // Create a blank canvas
        if($this->_autofill) {
            $newImage = imagecreatetruecolor($mwidth, $mheight);
        }
        else {
            $newImage = imagecreatetruecolor($newWidth, $newHeight);
        }
        
        // Add the background
        $bgColor = imagecolorallocate($newImage, $this->_bgColor[0], $this->_bgColor[1], $this->_bgColor[2]);
        imagefilledrectangle($newImage, 0, 0, $this->getWidth(), $this->getHeight(), $bgColor);

        
        // Copy the old image to it
        imagecopyresampled($newImage, $this->_image, 0, 0, 0, 0, $newWidth, $newHeight, $this->getWidth(), $this->getHeight());
        
        // Replace the image
        $this->_image = $newImage;
        
        return $this;
    }
    
    
    /**
     * Crops an image to a specified size, if either width or height aren't specified, crops a 1:1 AR version of it, without any white space
     *
     * @param int $x 
     * @param int $y 
     * @param int $width 
     * @param int $height 
     * @return void
     * @author Gravitywell Ltd
     */
    public function crop($width = 0, $height = 0, $x = 0, $y = 0) 
    {        
        $newImage = imagecreatetruecolor($width, $height);
        
        imagecopyresampled($newImage, $this->_image, 0, 0, $x, $y, $width, $height, $width, $height);
        
        // Replace the image
        $this->_image = $newImage;      
        
        return $this;  
    }
    
    
    /**
     * Creates a thumbnail of the image, cropping to the width and height to avoid white space
     *
     * @param int $width 
     * @param int $height 
     * @param string $x 
     * @param string $y 
     * @return void
     * @author Gravitywell Ltd
     */
    public function thumbnail($width = 100, $height = 100, $x = null, $y = null)
    {
        // Get the original Image ratio
        $originalRatio = $this->getWidth() / $this->getHeight();
        
        // Find the new width and height
        if ($width/$height > $originalRatio) {
            $newHeight = $width / $originalRatio;
            $newWidth = $width;
        } else {
            $newWidth = $height * $originalRatio;
            $newHeight = $height;
        }
        
        // If both x and y are null, center the crop
        if(is_null($x) && is_null($y)) {
            $xCenter = $newWidth / 2;
            $x = $xCenter - ($width / 2);
            $yCenter = $newHeight / 2;
            $y = $yCenter - ($height / 2);
        }
        
        if(is_null($x)) {
            $x = 0;
        }
        
        if(is_null($y)) {
            $y = 0;
        }
        
        // Resize the image
        $this->resize($newWidth, $newHeight);
        
        // Crop the image
        $this->crop($width, $height, $x, $y);
        
        return $this;
    }
    
    
    /**
     * Trims whitespace (any color you want) from the edge of the image.
     * Similar to photoshops trim. Should be used before resize/thumbnail.
     *
     * @param string Color  The color [white/black]
     * @return Gravitywell_Image
     */
    public function trim($color = 'white')
    {
        switch($color) {
            case 'white':
                $color = 0xFFFFFF;
                break;
            case 'black':
                $color = 0xFFFFFF;
                break;
            default:
                throw new \Exception ("Invalid color passed to " . __METHOD__ . ".");
        }
        
        //load the image
        $img = $this->_image;
        
        //find the size of the borders
        $b_top = 0;
        $b_btm = 0;
        $b_lft = 0;
        $b_rt  = 0;
        
        //top
        for(; $b_top < imagesy($img); ++$b_top) {
          for($x = 0; $x < imagesx($img); ++$x) {
            if(imagecolorat($img, $x, $b_top) != $color) {
               break 2; //out of the 'top' loop
            }
          }
        }
        
        //bottom
        for(; $b_btm < imagesy($img); ++$b_btm) {
          for($x = 0; $x < imagesx($img); ++$x) {
            if(imagecolorat($img, $x, imagesy($img) - $b_btm-1) != $color) {
               break 2; //out of the 'bottom' loop
            }
          }
        }
        
        //left
        for(; $b_lft < imagesx($img); ++$b_lft) {
          for($y = 0; $y < imagesy($img); ++$y) {
            if(imagecolorat($img, $b_lft, $y) != $color) {
               break 2; //out of the 'left' loop
            }
          }
        }
        
        //right
        for(; $b_rt < imagesx($img); ++$b_rt) {
          for($y = 0; $y < imagesy($img); ++$y) {
            if(imagecolorat($img, imagesx($img) - $b_rt-1, $y) != $color) {
               break 2; //out of the 'right' loop
            }
          }
        }
        
        //copy the contents, excluding the border
        $newimg = imagecreatetruecolor(
        imagesx($img)-($b_lft+$b_rt), imagesy($img)-($b_top+$b_btm));
        
        imagecopy($newimg, $img, 0, 0, $b_lft, $b_top, imagesx($newimg), imagesy($newimg));
        
        
        // Set the image
        $this->_image = $image;
        
    }
    
    
    
    /**
     * Sets a background color, accepts hash or RGB comma seperated
     *
     * @param int $red 
     * @param int $green 
     * @param int $blue 
     * @return void
     * @author Gravitywell Ltd
     */
    public function setBackgroundColor($red, $green, $blue)
    {
        $this->_bgColor[0] = $red;
        $this->_bgColor[1] = $green;
        $this->_bgColor[2] = $blue;
        
        return $this;
    }
    
    
    /**
     * Adds a border to the image
     *
     * @param int $width 
     * @param int $red 
     * @param int $green 
     * @param int $blue 
     * @return void
     * @author Gravitywell Ltd
     */
    public function setBorder($width, $red, $green, $blue)
    {
        $this->_borderWidth = $width;
        $this->_borderColor[0] = $red;
        $this->_borderColor[1] = $green;
        $this->_borderColor[2] = $blue;
        
        return $this;
    }
    
    
    /**
     * Adds the borders to the image resource
     *
     * @return void
     * @author Gravitywell Ltd
     */
    public function _addBorders()
    {
        if($this->_borderWidth > 0) {
            // Rererence the width
            $border = &$this->_borderWidth;
            
            // Make the color
            $borderColor = imagecolorallocate($this->_image, $this->_borderColor[0], $this->_borderColor[1], $this->_borderColor[2]);
            
            // Gooo
            imagefilledrectangle($this->_image, 0, $this->getHeight(), $this->getWidth(), $this->getHeight() - $border, $borderColor);          // Bottom
            imagefilledrectangle($this->_image, 0, 0, $this->getWidth(), $border - 1, $borderColor);                                            // Top
            imagefilledrectangle($this->_image, 0, 0, $border - 1, $this->getHeight(), $borderColor);                                           // Left
            imagefilledrectangle($this->_image, $this->getWidth() - $border, 0, $this->getWidth(), $this->getHeight(), $borderColor);           // Right
        }
    }
    
    /**
     * Sets whether we should autofill the image or not
     *
     * @param bool $autofill 
     * @return void
     * @author Gravitywell Ltd
     */
    public function setAutofill($autofill)
    {
        $this->_autofill = $autofill;
        
        return $this;
    }
    
}