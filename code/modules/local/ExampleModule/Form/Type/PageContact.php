<?php

namespace ExampleModule\Form\Type;

use Newton\Form;

class PageContact extends Form
{
    public function init()
    {
        parent::init();

        $this->addElement('text', 'title', array(
            'label' => 'Title'
        ));

        $this->addElement('textarea', 'address', array(
            'label' => 'Address',
            'rows'  => 4
        ));


        // Saves us having to find each element, this does it for us.
        $this->addDefaultDisplayGroup();

        // Add the meta data group and elements automagically
        $this->addMetaDataGroup();

        // Add the actions group
        $this->addActionGroup();
    }
}