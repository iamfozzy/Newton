<?php 

namespace NewtonContent\Form\Settings;

use Newton\Form;
use NewtonContent\Model\TypeManager;
use NewtonContent\Model\TemplateManager;

class Page extends Form
{
    public function init()
    {
        parent::init();

        // Get the types and templates that are available 
        $typeOptions = $templateOptions = array();

        // Types
        foreach(TypeManager::getInstance()->getTypes() as $type) {
            $typeOptions[$type['name']] = $type['label'];
        }

        // Templates
        foreach(TemplateManager::getInstance()->getTemplates() as $template) {
            $templateOptions[$template['name']] = $template['label'];
        }

        // Add the type element
        $this->addElement('select', 'type', array(
            'label'         => 'Page Type',
            'description'   => 'Be careful - changing the type could mean you lose content.',
            'multioptions'  => $typeOptions
        ));

        // Add the page template element
        $this->addElement('select', 'page_template', array(
            'label'         => 'Page Template',
            'description'   => 'Choose what template renders this item.',
            'multioptions'  => $templateOptions
        ));

        $this->addActionGroup();
    }
}