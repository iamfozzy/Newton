<?php

namespace NewtonCore\Model;

use Newton\Mongo\Document;

class Setting extends Document
{
    /**
     * Collection name
     * @var string
     */
    protected static $_collection = 'settings';

    /**
     * Local cache when retrieving settings
     * 
     * @var Settings
     */
    protected static $localCache = array();


    /**
     * Gets a specific set of settings
     * @param  string $name [description]
     * @return [type]       [description]
     */
    public static function findSetting($name, $lang = null,  $site = null)
    {
        $doc = null; 

        // If no site, get the active
        if(null === $site) {
            $site = Site::current();
        }

        // If no language, get the active one
        if(null === $lang) {
            $lang = Lang::current();
        }

        if(!isset(static::$localCache[$name])) {

            // We have multiple ways of trying to find a value for this setting...
            foreach(array(
                array(
                    'name'  => $name,
                    'site'  => $site,
                    'lang'  => $lang
                ), array(
                    'name'  => $name,
                    'site'  => $site,
                    'lang'  => Lang::base()
                ), array(
                    'name'  => $name,
                    'site'  => Site::base(),
                    'lang'  => $lang
                ), array(
                    'name'  => $name,
                    'site'  => Site::base(),
                    'lang'  => Lang::base()
                )
            ) as $search) {
                if(null === $doc) {
                    $doc = static::fetchOne($search);
                } else {
                    break;
                }
            }

            // If no document still exists, create it.
            if(null === $doc) {
                $doc = new static();
                $doc->name = $name;
                $doc->site = $site;
                $doc->lang = $lang;
            }

            static::$localCache[$name] = $doc;
        }

        return static::$localCache[$name];
    }


    /**
     * Retrieves a setting
     *
     * Format could be any of the below
     *     Setting::get('core/site');
     *     Setting::get('core/site.defaultMetaData');
     * 
     * @param  [type] $name [description]
     * @param  [type] $lang [description]
     * @param  [type] $site [description]
     * @return [type]       [description]
     */
    public static function get($name, $lang = null, $site = null)
    {
        $key = null;

        if(false !== strpos($name, '.')) {
            list($name, $key) = explode('.', $name, 1);
        }

        $doc    = static::findSetting($name, $lang, $site);
        $value  = $doc->value;

        // The value has to be exported incase it looks like an embedded document
        if($value instanceof \Shanty_Mongo_Document) {
            $value = $value->export();
        } 

        if(null !== $key) {
            return array_get($value, $key);
        }

        return $value;
    }

    /**
     * Sets a setting value
     * 
     * @param [type] $name  [description]
     * @param [type] $value [description]
     * @param [type] $lang  [description]
     * @param [type] $site  [description]
     */
    public static function set($name, $value, $lang = null, $site = null)
    {   
        $key = null;

        if(false !== strpos($name, '.')) {
            list($name, $key) = explode('.', $name, 1);
        }

        $doc = static::findSetting($name, $lang, $site);

        // Is the key null or not? Are we setting an individual property
        if(null !== $key) {

            // Store the current value
            $oldValue = $doc->value;

            // The value has to be exported incase it looks like an embedded document
            if($oldValue instanceof \Shanty_Mongo_Document) {
                $oldValue = $oldValue->export();
            }

            // Set the new value
            array_set($oldValue, $key, $value);

            $doc->value = $oldValue;
        } else {
            $doc->value = $value;
        }

        $doc->save();

        // Update local cache
        static::$localCache[$name] = $doc;
    }
}