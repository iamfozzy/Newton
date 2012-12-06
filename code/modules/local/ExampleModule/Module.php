<?php

namespace ExampleModule;

use NewtonCore\Model\Site;
use NewtonCore\Model\Lang;
use Newton\Event;
use Newton\Controller\Action;
use Newton\Module\ModuleInterface;
use ExampleModule\Form\Type\PageDefault;
use ExampleModule\Form\Type\PageContact;
use ExampleModule\Form\Type\TeamMember;
use NewtonContent\Model\Type;

class Module implements ModuleInterface
{
    /**
     * Module::init() is called by Newton early-on, even before the 
     * front controller,router, dispatcher, request and response have been initialized.
     *
     * You can use event hooks inside init() to attach functions/methods to specific events.
     * 
     * @return [type] [description]
     */
    public static function init()
    {

        // Listen for the content.types.init event of the content module
        Event::listen('content.types.init', function($typeManager) {

            $categories = array(
                'fashion' => 'Fashion',
                'food-and-drink' => 'Food and Drink'
            );

            foreach($categories as $k => $v) { 
                $typeManager->addType(new Type(array(
                    'name'          => $k,
                    'label'         => $v,
                    'is_page'       => false,
                    'url_prefix'    => $k,
                    'plural'        => false,
                    'linked_content'    => 'fashion',
                    'form'  => function() {
                        return new PageDefault();
                    }
                )));
            }
            
            // Page Default Type
            $typeManager->addType(new Type(array(
                'name'              => 'example-pagedefault',
                'label'             => 'Default Page',
                'form'              => function() { 
                    return new PageDefault();
                }
            )));

            // Contact Page Type
            $typeManager->addType(new Type(array(
                'name'              => 'example-pagecontact',
                'label'             => 'Contact Page',
                'form'              => new PageContact()
            )));

            // Team Members Type - This isn't a 'page', so it can be managed slightly different
            $typeManager->addType(new Type(array(
                'name'                      => 'example-teammember',
                'label'                     => 'Team Member',
                'form'                      => new TeamMember(),
                'is_page'                   => false,
                'rewrite_key'               => 'name',
                //'url_rewrites_enabled'      => false,
                'template'                  => 'team-member',
                'datatable_mappings'        => array(
                    'name'      => 'Name',
                    'age'       => 'Age',
                    'number'    => 'Number'
                )
            )));
        });
    }
}