<?php

namespace ExampleModule\Form\Type;

use Newton\Form;

class TeamMember extends Form
{
    public function init()
    {
        parent::init();

        $this->addElement('text', 'name', array(
            'label' => 'Name'
        ));

        $this->addElement('text', 'age', array(
            'label' => 'Age'
        ));

        $this->addElement('text', 'number', array(
            'label' => 'Number'
        ));


        // Saves us having to find each element, this does it for us.
        $this->addDefaultDisplayGroup();

        // Add the meta data group and elements automagically
        $this->addMetaDataGroup();

        // Add the actions group
        $this->addActionGroup();
    }
}