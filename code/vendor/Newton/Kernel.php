<?php
/**
 * Newton
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. 
 *
 * @category    Newton
 * @package     Newton
 * @license     http://mozilla.org/MPL/2.0/     Mozilla Public Licence
 */

namespace Newton;

use Newton\Module;
use Newton\Loader\Autoloader;
use Newton\Controller\Dispatcher;
use Newton\Controller\Plugin;
use Newton\Controller\Request;
use Newton\Session;

use Zend_Controller_Front;
use Zend_Controller_Plugin_ErrorHandler;
use Zend_Controller_Router_Rewrite;
use Zend_Controller_Response_Http;
use Zend_Layout;

use NewtonCore\Model\Site;
use NewtonCore\Model\Lang;

// Set some constants for easy access
define('DS', DIRECTORY_SEPARATOR);                                                  // Directory Separator
define('PS', PATH_SEPARATOR);                                                       // Path Separator
define('BP', ROOT);                                                                 // Base Path // directory should be set to the root

// Set some path constants
define('APP_PATH'   , BP);                                                          // Application Path
define('VENDOR_PATH', BP . DS . 'code' . DS . 'vendor');                            // Library Path
define('PUBLIC_PATH', BP . DS . 'public');                                          // Public Path
define('UPLOAD_PATH', PUBLIC_PATH . DS . 'uploads');                                // Upload Path
define('LOCAL_MODULE_PATH', BP . DS . 'code' . DS . 'modules' . DS . 'local');      // Local Module Path         
define('BASE_MODULE_PATH' , BP . DS . 'code' . DS . 'modules' . DS . 'base');       // Base Module Path

// And finally the application environment
define('APPLICATION_ENV', $_GLOBAL['ENV']);

// Required files at this stage
require_once(VENDOR_PATH . DS . 'Newton' . DS . 'IoC.php');
require_once(VENDOR_PATH . DS . 'Newton' . DS . 'Loader' . DS . 'Autoloader.php');
require_once(VENDOR_PATH . DS . 'Newton' . DS . 'Helpers.php');

// Register the autoloader
spl_autoload_register(array('Newton\\Loader\\Autoloader', 'load'));

// Set includepath to the vendor's directory
set_include_path(VENDOR_PATH . PATH_SEPARATOR . get_include_path());


/**
 * Main Newton Class. This is also an IoC container
 *
 * Some accessable objects during and after init:
 *     front            The Front Controller (Zend_Controller_Front)
 *     frontendName     The Name of the frontend ('default', 'admin' or 'mobile')
 * 
 */
final class Kernel extends IoC
{
    /**
     * Initialize the app
     *
     * @return void
     */
    public static function initWeb($site = 'default', $lang = 'en')
    {
        static::initAutoloader();
        static::initPHP();
        static::initSession();
        static::initMvc();
        static::initSite($site);
        static::initLanguage($lang);
        static::dispatch();  
    }


    /**
     * Initializes the site manager
     * 
     * @param  string $site Name of the site
     * @return void       
     */
    public static function initSite($site)
    {
        // Set the site
        Site::load($site);
    }

    /**
     * Initialises the language manager
     * 
     * @param  string $lang Language of the site
     * @link http://www.w3schools.com/tags/ref_language_codes.asp  ISO 639-1 Language Codes
     * @return void
     */
    public static function initLanguage($lang)
    {
        Lang::load($lang);
    }


    /**
     * Initializes the autoloader for default namespaces and loads 
     * the aliases from the config
     * 
     * @return void
     */
    public static function initAutoloader()
    {
        // Add the vendor directories to the autoloader
        Autoloader::underscored(array(
            'Zend'                  => VENDOR_PATH . DS . 'Zend',
            'Gravitywell'           => VENDOR_PATH . DS . 'Gravitywell',
            'Shanty'                => VENDOR_PATH . DS . 'Shanty',
            'Twitter'               => VENDOR_PATH . DS . 'Twitter',
            'SAuth'                 => VENDOR_PATH . DS . 'SAuth'
        ));

        // Add the Newton namespaces directory to autoloader
        Autoloader::namespaces(array(
            'Newton'                => VENDOR_PATH . DS . 'Newton',
            'Symfony'               => VENDOR_PATH . DS . 'Symfony'
        ));

        // Assign all aliases from the config
        foreach(Config::load('alias')->alias as $alias => $class) {
            Autoloader::alias($class, $alias);
        }
    }


    /**
     * Initializes any PHP settings required. Could also break here if 
     * no requirements are met.
     *  
     * @return void
     */
    public static function initPHP()
    {
        // Set the date and time
        date_default_timezone_set(Config::load('newton')->timezone);
    }

    /**
     * Initializes the session
     * @return [type] [description]
     */
    public static function initSession()
    {
        Session::start();
    }


    /**
     * Init the MVC Components of Newton.
     * 
     * @return [type] [description]
     */
    public static function initMvc()
    {
        // Initialize the module manager early on
        Module\Manager::init();

        // Set the Front Controller
        static::singleton('front', function() {

            // Initalize the Front Controller...
            $front = Zend_Controller_Front::getInstance();

            $router = new Zend_Controller_Router_Rewrite();
            $router->removeDefaultRoutes();

            // Router, Request, Response, Dispatcher etc...
            $front->setRouter($router);
            $front->setRequest(new Request\Http());
            $front->setResponse(new Zend_Controller_Response_Http());
            $front->setDispatcher(new Dispatcher\Standard());

            $front->registerPlugin(new Plugin\ThemeManager());
            $front->registerPlugin(new Plugin\AdminManager());
            $front->registerPlugin(new Zend_Controller_Plugin_ErrorHandler(array(
                'module'        => Config::load('newton')->modules->default
            )), 100);

            return $front;
        });

        // Fire the front controller initialized event
        Event::fire('front.init');

        // Start Zend_Layout
        Zend_Layout::startMvc();        
    }

    /**
     * Dispatch the App
     * 
     * @return void 
     */
    public static function dispatch()
    {
        // Event: kernel.dispatch_before
        Event::fire('front.dispatch_before');

        // Dispatch the front controller
        static::resolve('front')->dispatch();

        // Event: kernel.dispatch_after
        Event::fire('front.dispatch_after');
    }

}
