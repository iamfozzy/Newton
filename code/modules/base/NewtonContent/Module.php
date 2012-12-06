<?php

namespace NewtonContent;

use Newton;
use Newton\Event;
use Newton\Config;
use Newton\Module\ModuleInterface;
use Zend_Controller_Router_Route as Route;
use NewtonCore\Router\UrlRewriter as UrlRewriteRouter;
use NewtonContent\Model\TemplateManager;

class Module implements ModuleInterface
{
	public static function init()
	{
        // On front.init add routes
        Event::listen('front.init', function() {
           
            // Initialise routes
            Module::initRoutes();

            // Initialise templates
            Module::initTemplates();
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
            'admin.content',
            new Route(
                ADMINKEY . '/content/:controller/:action/*',
                array(
                    'frontendName'  => 'admin',
                    'module'        => 'NewtonContent',
                    'controller'    => 'index',
                    'action'        => 'index'
                )
            )
        );

        // Add the normal url rewrite rules for content
        $router->addRoute(
            'public.content',
            new UrlRewriteRouter(
                '/:page',
                array(
                    'module'        => 'NewtonContent',
                    'controller'    => 'index',
                    'action'        => 'index'
                )
            )
        );
    }


    /**
     * Initialises all templates with the template manager
     * 
     * @return [type] [description]
     */
    public static function initTemplates()
    {
        // This finds all templates for the default and active theme
        TemplateManager::findTemplates();
    }
}