<?php

namespace NewtonCore\Model;

use Newton\Event;
use Newton\Log;
use Newton\Mongo\Document;
use Zend_Session_Namespace as Session;

class Site extends Document
{
    /**
     * Collection name
     * 
     * @var string
     */
    protected static $_collection = 'sites';

    /**
     * Base site name
     * @var string
     */
    protected static $base = 'default';

    /**
     * The current active site
     * @var string
     */
    protected static $current = 'default';

    /**
     * Session Key
     * @var string
     */
    protected static $sessionKey = 'core/site';


    /**
     *  Initialises the site manager
     *  
     * @param  string $site The name of the site
     * @return void       
     */
    public static function load($site = 'default')
    {
        // Check the default site exists
        static::checkDefaultExists();

        // Set the current
        static::current($site);

        // Fire the site load after event..
        Event::fire('site.load_after');
    }

    /**
     * Retrieves the current active site, or optionally sets it.
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
     * Retrieves the base site, or optionally sets it.
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
     * This function attempts to detect the current site based on a session value
     *
     * @param  string $method 
     * @return void         
     */
    public static function detectSession()
    {
        $session = new Session(static::$sessionKey);

        if(!empty($session->activeSite)) {

            try {
                static::current($session->activeSite);

            } catch (\Exception $e) {
                
                // Log that the site could not be cached...
                Log::info($e);
            }
        }
    }

    /**
     * This method attempts to save the current selected site in a cookie to be retrieved later
     * 
     * @param string $site
     */     
    public static function saveSession()
    {
        $session = new Session(static::$sessionKey);

        $session->activeSite = static::current();
    }   


    /**
     * Checks to see if the 'default' site exists
     * 
     * @return void 
     */
    public static function checkDefaultExists()
    {
        $doc = static::findByName('default');

        if(empty($doc)) {

            // If no doc exists - create one...
            $doc = new static();
            $doc->name  = 'default';
            $doc->title = 'Default Site';
            $doc->save();
        }
    }


    /**
     * Loads a site by name
     * 
     * @param  [type] $name [description]
     * @return [type]       [description]
     */
    public static function findByName($name)  
    {
        // Try to load by name
        $site = static::fetchOne(array(
            'name'  => $name
        ));

        return $site;
    }


    /**
     * Fetches all sites ordered by label
     * 
     * @return [type] [description]
     */
    public static function fetchAllSites()
    {
        return static::all()->sort(array(
            'title'     => 1
        ));
    }
}