<?php

namespace NewtonCore\Model;

use NewtonCore\Model\Site;
use Newton\Event;
use Newton\Log;
use Newton\Mongo\Document;
use Zend_Session_Namespace as Session;

class Lang extends Document
{
    /**
     * Collection name
     * @var string
     */
    protected static $_collection = 'languages';

    /**
     * The base language
     * 
     * @var string
     */
    protected static $base = 'en';

    /**
     * The current active language
     * @var string
     */
    protected static $current = 'en';

    /**
     * Session Key
     * @var string
     */
    protected static $sessionKey = 'core/lang';


    /**
     *  Initialises the language
     *  
     * @param  string $lang The name of the lang
     * @return void       
     */
    public static function load($lang = 'default')
    {
        // Check the default site exists
        static::checkDefaultExists();

        // Set the current
        static::current($lang);

        // Fire the lang load after event..
        Event::fire('lang.load_after');
    }

    /**
     * Retrieves the current active lang, or optionally sets it.
     * 
     * @return MongoID
     */
    public static function current($set = false)
    {
        if($set) {
            if(static::findByName($set)) {
                static::$current = $set;
            }
        }

        return static::$current;
    }

    /**
     * Retrieves the base lang, or optionally sets it.
     * 
     * @param  boolean $set [description]
     * @return [type]       [description]
     */
    public static function base($set = false)
    {
        if($set) {
            if(static::findByName($set)) {
                static::$base = $set;
            }
        }

        return static::$base;
    }

    /**
     * This function attempts to detect the current language based on a session value
     *
     * @param  string $method 
     * @return void         
     */
    public static function detectSession()
    {
        $session = new Session(static::$sessionKey);

        if(!empty($session->activeLang)) {

            try {
                static::current($session->activeLang);

            } catch (\Exception $e) {
                
                // Log that the language could not be cached...
                Log::info($e);
            }
        }
    }

    /**
     * This method attempts to save the current selected language in a cookie to be retrieved later
     * 
     * @return void
     */     
    public static function saveSession()
    {
        $session = new Session(static::$sessionKey);

        $session->activeLang = static::current();
    }   


    /**
     * Checks to see if the 'en' language exists
     * 
     * @return void 
     */
    public static function checkDefaultExists()
    {
        $doc = static::findByName('en');

        if(empty($doc)) {

            // If no doc exists - create one...
            $doc = new static();
            $doc->name  = 'en';
            $doc->site  = Site::current();
            $doc->title = 'English';
            $doc->save();
        }
    }


    /**
     * Loads a language by name
     * 
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public static function findByName($name)  
    {
        // Try to load by name
        $site = static::fetchOne(array(
            'name'  => $name,
            'site'  => Site::current()
        ));

        return $site;
    }


    /**
     * Fetches all languages ordered by title
     * 
     * @return [type] [description]
     */
    public static function fetchAllLanguages()
    {
        return static::all(array(
            'site'      => Site::current()
        ))->sort(array(
            'title'     => 1
        ));
    } 
}