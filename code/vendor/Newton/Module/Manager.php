<?php

namespace Newton\Module;

use Newton;
use Newton\Event;
use Newton\Config;
use Newton\Loader\Autoloader;
use Zend_Controller_Plugin_ErrorHandler;

class Manager
{
    protected static $_modules = array();

    /**
     * Sets up the ModuleManager
     */
    public static function init()
    {
        // Load the global configuration file, search for the enabled modules and load them
        $config = Config::load('newton');

        // Loads up each enabled module. Note - A module can actually load another module not loaded through
        // configuration via Newton\ModuleManager::loadModule($name, $path).
        foreach($config->modules->enabled as $moduleName => $modulePath) {
            // Check for config and merge in...
            static::loadModule($moduleName, $modulePath);

            // If this modules configuration has not yet been merged into the configuration cache, do so now
            if(true !== $config->mergedConfigurations) {

                // If config exists...
                if(false !== ($moduleConfig = Config::load('module', $moduleName, null))) {
                    Config::merge('newton', null, $moduleConfig->newton);
                }
            }
        }

        // Only merge again if not already merged into the cache
        if(true !== $config->mergedConfigurations) {

            // Finally, merge the local config into the main config to override all module configurations
            $localConfig = Config::load('local');
            $localConfig->newton->mergedConfigurations = true;
            Config::merge('newton', null, $localConfig->newton);
        }

        // List for when the front controller has dispatched, then set the default module
        Event::listen('front.init', function() use ($config) {

            // Now, add the module directories
            foreach(Manager::getModules() as $name => $path) {
                
                // Module has initied, lets add this modules directories for the controller
                Newton::resolve('front')->addControllerDirectory($path . DS . 'controllers', $name);
            }

            // Also set the default Module to the once specified in the configuration   
            Newton::resolve('front')->getDispatcher()->setDefaultModule($config->modules->default);


        });
    }

    /**
     * Adds a module to the module manager
     * 
     * @param [string] $moduleName 
     * @param [string] $moduleDirectory
     */
    public static function loadModule($name, $path)
    {
        $moduleFile = $path . '/Module.php';

        // Check it exists
        if(file_exists($moduleFile)) {

            // Load the file
            require_once ($moduleFile);
            $className = '\\' . $name . '\\Module';

            try {

                static::$_modules[$name] = $path;

                // Also, create a namespace autoloader for this module
                Autoloader::namespaces(array(
                    $name   => $path
                ));

                $className::init();

                // Fire module loaded event for this module
                Event::fire('module.loaded.' . $name);

            } catch (\Exception $e) {

                // Couldn't load the module
                throw new \Exception ("Module '$name' could not be initialized. Error thrown: " . $e->getMessage());
            }
        } else {
            throw new \Exception ("Module '$name' had no Module.php file. Please create one.");
        }
    }

    /**
     * Returns a list of the modules
     *
     * @return array
     */
    public static function getModules()
    {
        return static::$_modules;
    }


    /**
     * Retrieves the correct case module name from a lowercased module
     * @param  [type] $moduleName [description]
     * @return [type]             [description]
     */
    public static function getCorrectCaseModuleName($moduleName)
    {
        if(false !== ($key = array_key_exists_nc($moduleName, static::$_modules))) {
            return $key;
        }
    }


    /**
     * Returns the path to a module. This is case insensitive
     * @param  [type] $moduleName
     * @return [type]  
     */
    public static function getModulePath($moduleName)
    {
        if(false !== ($key = array_key_exists_nc($moduleName, static::$_modules))) {
            return static::$_modules[$key];
        }

        return null;
    }
}