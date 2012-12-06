<?php

namespace NewtonTest;

use Newton;
use Newton\Event;
use Zend_Controller_Router_Route as Route;
use Newton\Module\ModuleInterface;

class Module implements ModuleInterface
{

    public static function init()
    {
        // On front.init add routes
        Event::listen('front.init', function() {
           
            // Initialise routes
            Module::initRoutes();
        });
    }

    /**
     * Initializes the routes for this module
     * @return void
     */
    public static function initRoutes()
    {
        $router = Newton::resolve('front')->getRouter();

        // Add the admin.content
        $router->addRoute(
            'admin.test',
            new Route(
                ADMINKEY . '/test/:controller/:action/*',
                array(
                    'frontendName'  => 'admin',
                    'module'        => 'NewtonTest',
                    'controller'    => 'index',
                    'action'        => 'index'
                )
            )
        );
    }

}