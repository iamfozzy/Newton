<?php 

namespace NewtonContent\Form\Settings;

use Newton\Form;

class Url extends Form
{
    public function init()
    {
        parent::init();

        $this->addElement('text', 'url', array(
            'label'     =>  \URL::base() . '/',
            'class'     => 'restrict-input-url',
            'description'   => 'Choose a friendly URL of this page.'
        ));

        $this->addActionGroup();
    }
}