<?php

namespace Newton;

use Newton\Module\Manager as ModuleManager;
use Zend_Config;
use Zend_Config_Yaml;

class Config
{
    /**
     * Loads a configuration file
     *
     * Format is: module.config file. If no module is defined will load the global configuration.
     * 
     * @param  [string] $config
     * @return [Zend_Config]
     */
    public static function load($file, $module = null, $section = APPLICATION_ENV)
    {
        $key = static::getCacheId($file . '_' . $module);
        $cache = Cache::factory('config');

        if(!($config = $cache->load($key))) {

            if($module === null) {
                $configPath = BP . DS . 'etc' . DS . $file . '.yaml';
            } else {
                $modulePath = ModuleManager::getModulePath($module);

                if(false === $modulePath) {
                    return false;
                }

                $configPath = $modulePath . DS . 'etc' . DS . $file . '.yaml';
            }

            if(!file_exists($configPath)) {
                return false;
            }

            // Parse the Yaml file
            $config = new Zend_Config_Yaml($configPath, $section);

            $config = $config->toArray();

            $cache->save($config);
        }
        
        return new Zend_Config($config, true);
    }


    /**
     * Merges to configuration files together and then replaces the main cache with the new one
     * 
     * @param  [type] $file      [description]
     * @param  [type] $module    [description]
     * @param  [type] $newConfig [description]
     * @return [type]            [description]
     */
    public static function merge($file, $module, $newConfig)
    {
        $key = static::getCacheId($file . '_' . $module);
        $config = static::load($file, $module);

        if(null !== $newConfig) {
            $config->merge($newConfig);
        }

        Cache::factory('config')->save($config->toArray(), $key);
    }

    /**
     * Returns the cache id
     * 
     * @param  [type] $key [description]
     * @return [type]      [description]
     */
    public static function getCacheId($key)
    {
        return md5('config_' . $key . '_' . Request::env());
    }
}