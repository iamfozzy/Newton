<?php 

namespace Newton;

use Newton;
use Newton\Config;

class URL {

	/**
	 * The cached base URL.
	 *
	 * @var string
	 */
	public static $base;

	/**
	 * Get the full URI including the query string.
	 *
	 * @return string
	 */
	public static function full()
	{
		return static::to(URI::full());
	}

	/**
	 * Get the full URL for the current request.
	 *
	 * @return string
	 */
	public static function current()
	{
		return static::to(URI::current());
	}

	/**
	 * Get the URL for the application root.
	 *
	 * @param  bool    $https
	 * @return string
	 */
	public static function home($https = false)
	{
		return static::to('/', $https);
	}

	/**
	 * Get the base URL of the application.
	 *
	 * @return string
	 */
	public static function base()
	{
		if (isset(static::$base)) return static::$base;

		$base = 'http://localhost';

		// If the application URL configuration is set, we will just use that
		// instead of trying to guess the URL from the $_SERVER array's host
		// and script variables as this is more reliable.
		if (($url = Config::load('newton')->url) !== null)
		{
			$base = $url;
		}
		elseif (isset($_SERVER['HTTP_HOST']))
		{
			$base = static::guess();
		}

		return static::$base = $base;
	}

	/**
	 * Guess the application URL based on the $_SERVER variables.
	 *
	 * @return string
	 */
	protected static function guess()
	{
		$protocol = (Request::secure()) ? 'https://' : 'http://';

		// Basically, by removing the basename, we are removing everything after
		// the and including the front controller from the URI. Leaving us with
		// the installation path for the application.
		$script = $_SERVER['SCRIPT_NAME'];

		$path = str_replace(basename($script), '', $script);

		// Now that we have the URL, all we need to do is attach the protocol
		// protocol and HTTP_HOST to build the URL for the application, and
		// we also trim off trailing slashes for cleanliness.
		$uri = $protocol.$_SERVER['HTTP_HOST'].$path;

		return rtrim($uri, '/');
	}

	/**
	 * Generate an application URL.
	 *
	 * <code>
	 *		// Create a URL to a location within the application
	 *		$url = URL::to('user/profile');
	 *
	 *		// Create a HTTPS URL to a location within the application
	 *		$url = URL::to('user/profile', true);
	 * </code>
	 *
	 * @param  string  $url
	 * @param  bool    $https
	 * @return string
	 */
	public static function to($url = '', $https = false)
	{
		if (filter_var($url, FILTER_VALIDATE_URL) !== false) return $url;

		$root = static::base();

		// Since SSL is not often used while developing the application, we allow the
		// developer to disable SSL on all framework generated links to make it more
		// convenient to work with the site while developing locally.
		if ($https and Config::load('newton')->ssl)
		{
			$root = preg_replace('~http://~', 'https://', $root, 1);
		}

		return rtrim($root, '/').'/'.ltrim($url, '/');
	}

	/**
	 * Function to create url to admin frontend
	 * 
	 * @param  string  $url   URL to link to
	 * @param  boolean $https Use SSL?
	 * @return string         Absolute URL
	 */
	public static function toAdmin($url = '', $https = false)
	{
		return static::to(Config::load('newton')->adminKey . '/' . $url, $https);
	}

	/**
	 * Generate an application URL with HTTPS.
	 *
	 * @param  string  $url
	 * @return string
	 */
	public static function toSecure($url = '')
	{
		return static::to($url, true);
	}


	/**
	 * Generate a action URL from a route definition
	 *
	 * @param  array   $route
	 * @param  string  $action
	 * @param  array   $parameters
	 * @return string
	 */
	protected static function explicit($route, $action, $parameters)
	{
		$https = array_get(current($route), 'https', false);

		return static::to(static::transpose(key($route), $parameters), $https);
	}


	/**
	 * Generate an application URL to an asset.
	 *
	 * @param  string  $url
	 * @param  bool    $https
	 * @return string
	 */
	public static function toAsset($url, $https = null)
	{
		if (is_null($https)) $https = Request::secure();

		$url = static::to($url, $https);

		return $url;
	}
	

	/**
	 * Generates a url to a named route
	 * 
	 * @param  array   $urlOptions 
	 * @param  string  $name       
	 * @param  boolean $reset      
	 * @param  boolean $encode     
	 * @return string           
	 */
	public static function route(array $urlOptions = array(), $name = null, $reset = false, $encode = true)
    {
        $router = Newton::resolve('front')->getRouter();
        return static::to($router->assemble($urlOptions, $name, $reset, $encode));
    }


	/**
     * Similar to Asset::add(), however this links to a source file thats part of that theme
     * 
     * @param  [type] $name         [description]
     * @param  [type] $source       [description]
     * @param  array  $dependencies [description]
     * @param  array  $attributes   [description]
     * @return [type]               [description]
     */
    public static function toThemeAsset($urlinput = '', $https = null)
    {
        $frontendName = Newton::resolve('frontendName');
        $url = 'themes/' . Config::load('newton')->theme . '/' . $frontendName . '/' . $urlinput;

        // Does this exist? If not, rotate back to default
        if(!file_exists(PUBLIC_PATH . DS . $url)) {
            $url = 'themes/default/' . $frontendName . '/' . $urlinput;
        }

        return static::toAsset($url, $https);
    }
}