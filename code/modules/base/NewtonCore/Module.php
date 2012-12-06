<?php

namespace NewtonCore;

use Newton;
use Newton\Event;
use Newton\Config;
use Newton\Module\ModuleInterface;
use NewtonCore\Model\Site;
use NewtonCore\Model\Lang;
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
        // Define the admin key
        define('ADMINKEY', Config::load('newton')->adminKey);
        
        // On front.init add routes
        Event::listen('front.init', function() {
            Module::initRoutes();
        });

        // On Language load - detect the session
        Event::listen('lang.load_after', function() {
            Lang::detectSession();
        });

        // On Site load - detect the session
        Event::listen('site.load_after', function() {
            Site::detectSession();
        });
	}

    /**
     * Initializes the routes for this module
     * @return void
     */
    public static function initRoutes()
    {
        $router = Newton::resolve('front')->getRouter();

        // Add the /admin route
        // Admin routing, basically back to default but with the key
        $router->addRoute(
            'admin.default',
            new Route(
                ADMINKEY,
                array(
                    'frontendName'  => 'admin',
                    'module'        => 'NewtonCore',
                    'controller'    => 'dashboard',
                    'action'        => 'index'
                )
            )
        );
        
        $router->addRoute(
            'admin.core',
            new Route(
                ADMINKEY . '/core/:controller/:action/*',
                array(
                    'frontendName'  => 'admin',
                    'module'        => 'NewtonCore',
                    'controller'    => 'index',
                    'action'        => 'index'
                )
            )
        );
    }
}