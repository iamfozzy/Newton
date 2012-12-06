<?php

namespace NewtonCore\Form;

use Newton\Form;

class SiteSettings extends Form
{
    public function init()
    {
        parent::init();

        // GOOGLE API
        // Add the Google Analytics details
        $this->addElement('text', 'ga_account', array(
            'label' => 'Google Analytics Account',
            'description' => 'Your google analytics account code, in the form UA-XXXXX-X'
        ));

         $this->addDisplayGroup(
            array('ga_account'),
            'Google API'
        );


        // META INFORMATION
        $this->addElement('text', 'metaSeperator', array(
            'label'     => 'Meta Title Seperator',
            'value'     => ' - '
        ));


        $this->addElement('select', 'metaDisposition', array(
            'label'         => 'Meta Disposition',
            'value'         => 'append',
            'description'   => 'Should the default meta tag be appended or prepended to the page title?',
            'multiOptions'  => array(
                'append'        => 'Append',
                'prepend'       => 'Prepend',
                'none'          => 'None, remove the default title'
            )
        ));

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
            array('metaSeperator', 'metaDisposition', 'metaTitle', 'metaKeywords', 'metaDescription'),
            'Default Meta Data'
        );
                

        // Add the actions group
        $this->addActionGroup();
    }
}