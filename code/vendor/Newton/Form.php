<?php

namespace Newton;

use Twitter_Bootstrap_Form_Horizontal;
use Zend_Form_Element_Hidden;

class Form extends Twitter_Bootstrap_Form_Horizontal

{

    public function init()
    {
        //$this->setIsArray(true);
        $this->_addClassNames('well tab-content');
    }



    public function getFileSelector($select = 'Select File', $clear = 'Clear Image', $dataTarget = '')
    {
        $r =  '<a data-target-element="' . $dataTarget . '" class="btn btn-primary btn-select-file">' . $select . '</a>';

        $r .= (null === $clear) ? '' : '<a data-target-element="' . $dataTarget . '" class="btn btn-clear-file">' . $clear . '</a>';

        return $r;
    }


    /**
     * Adds meta data to the form
     *
     */
    public function addMetaDataGroup($legend = 'Meta Data')
    {
        $this->addElement('text', 'metaTitle', array(
            'label'     => 'Meta Title'
        ));

        $this->addElement('textarea', 'metaKeywords', array(
            'label'     => 'Meta Keywords',
            'rows'      => 5,
        ));

        $this->addElement('textarea', 'metaDescription', array(
            'label'     => 'Meta Description',
            'rows'      => 5,
        ));

        $this->addDisplayGroup(
            array('metaTitle', 'metaKeywords', 'metaDescription'),
            $legend
        );
    }


    /**
     * This method works very simply. It takes all elements and wraps them in a display group.
     *
     */
    public function addDefaultDisplayGroup($title = 'Content', $options = array())
    {
        $elements = array();

        foreach($this->getElements() as $el) {
            $elements[] = $el->getName();
        }

        $this->addDisplayGroup(
            $elements,
            $title,
            $options
        );
    }


    /**
     * Adds the action group to the form. This should be at the bottom
     *
     */
    public function addActionGroup()
    {
        $this->addElement('button', 'submit', array(
            'label'         => 'Save Changes',
            'type'          => 'submit',
            'class'         => 'btn btn-primary'
        ));

        $this->addElement('button', 'reset', array(
            'label'         => 'Reset',
            'type'          => 'reset'
        ));

        $this->addDisplayGroup(
            array('submit', 'reset'),
            'actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions')
            )
        );
    }

    /**
     * Disable the submit buttons
     * @return [type] [description]
     */
    public function disableForm($message = 'Saving is not allowed')
    {
        foreach($this->getElements() as $element) {
            $element->setAttrib('disabled', true);
            $element->setAttrib('readonly', true);
        }

        $this->getElement('submit')->setLabel($message);

        return $this;
    }

    /**
     * Override add display group to add data-title to the fieldset
     *
     */
    public function addDisplayGroup(array $elements = array(), $title = '', $options = array())
    {
        // modify the options to add the class
        if(!isset($options['class'])) {
            $options['data-title'] = $title;
        }

        parent::addDisplayGroup($elements, $title, $options);
    }


    /** 
     * Adds a file element to the form 
     *
     */
    public function addFilemanagerElement($name, $label)
    {
        return $this->addElement('text', $name, array(
            'label'     => $label,
            'readonly'  => true,
            'required'  => true,
            'append'    => $this->getFileSelector()
        ));
    }

    /**
     * Adds an image element to the form 
     *
     */
    public function addImageElement($name, $label, $alt = true)
    {
        $this->addElement('text', $name . '_src', array(
            'label'     => $label,
            'readonly'  => true,
            'required'  => true,
            'append'    => $this->getFileSelector('Select Image', 'Clear Image', $name . '_src')
        ));

        // Also, add the alt text
        if(true == $alt) {
            $this->addElement('text', $name . '_alt', array(
                'label'         => $label . ' Alt',
                'class'         => 'image-alt-element'
            ));
        }
    }


    /**
     * Adds a hidden field without any of the stupid decorators Zend Form adds to elements
     *
     */
    public function addHiddenElement($name, $value = null)
    {
        $hidden = new Zend_Form_Element_Hidden($name);

        if(null !== $value) {
            $hidden->setValue($value);
        }
       
        $hidden->setDecorators(array('ViewHelper'));
        $this->addElement($hidden);
    }


    
}