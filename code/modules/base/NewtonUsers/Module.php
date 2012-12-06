<?php

namespace NewtonUsers;

use Newton;
use Newton\Event;
use Newton\Module\ModuleInterface;
use Zend_Controller_Router_Route as Route;

class Module implements ModuleInterface
{

    /**
     * Init is called early on to initialize this module
     * 
     * @return void 
     */
    public static function init()
    {
        // On front.init add routes
        Event::listen('front.init', function() {
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

        // Add the /newtonusers route
        // public routing, basically back to default but with the key
        $router->addRoute(
            'public.users',
            new Route(
                'users/:controller/:action',
                array(
                    'frontendName'  => 'public',
                    'module'        => 'NewtonUsers',
                    'controller'    => 'index',
                    'action'        => 'index'
                )
            )
        );

        // Add the Admin route for this module
        $router->addRoute(
            'admin.users',
            new Route(
                ADMINKEY . '/users/:controller/:action',
                array(
                    'frontendName'  => 'admin',
                    'module'        => 'NewtonUsers',
                    'controller'    => 'index',
                    'action'        => 'index'
                )
            )
        );
    }
}