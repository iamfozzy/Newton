<?php

namespace Newton;

class HTML {

	/**
	 * The registered custom macros.
	 *
	 * @var array
	 */
	public static $macros = array();

    /**
     * Registers a custom macro.
     *
     * @param  string   $name
     * @param  Closure  $input
     * @return void
     */
	public static function macro($name, $macro)
	{
		static::$macros[$name] = $macro;
	}

	/**
	 * Convert HTML characters to entities.
	 *
	 * The encoding specified in the application configuration file will be used.
	 *
	 * @param  string  $value
	 * @return string
	 */
	public static function entities($value)
	{
		return htmlentities($value, ENT_QUOTES, Config::load('newton')->encoding, false);
	}

	/**
	 * Convert entities to HTML characters.
	 *
	 * @param  string  $value
	 * @return string
	 */
	public static function decode($value)
	{
		return html_entity_decode($value, ENT_QUOTES, Config::load('newton')->encoding);
	}

	/**
	 * Generate a link to a JavaScript file.
	 *
	 * <code>
	 *		// Generate a link to a JavaScript file
	 *		echo HTML::script('js/jquery.js');
	 *
	 *		// Generate a link to a JavaScript file and add some attributes
	 *		echo HTML::script('js/jquery.js', array('defer'));
	 * </code>
	 *
	 * @param  string  $url
	 * @param  array   $attributes
	 * @return string
	 */
	public static function script($url, $attributes = array())
	{
		$url = static::entities(URL::toAsset($url));

		return '<script src="'.$url.'"'.static::attributes($attributes).'></script>'.PHP_EOL;
	}

	/**
	 * Generate a link to a CSS file.
	 *
	 * If no media type is selected, "all" will be used.
	 *
	 * <code>
	 *		// Generate a link to a CSS file
	 *		echo HTML::style('css/common.css');
	 *
	 *		// Generate a link to a CSS file and add some attributes
	 *		echo HTML::style('css/common.css', array('media' => 'print'));
	 * </code>
	 *
	 * @param  string  $url
	 * @param  array   $attributes
	 * @return string
	 */
	public static function style($url, $attributes = array())
	{
		$defaults = array('media' => 'all', 'type' => 'text/css', 'rel' => 'stylesheet');

		$attributes = $attributes + $defaults;

		$url = static::entities(URL::toAsset($url));

		return '<link href="'.$url.'"'.static::attributes($attributes).'>'.PHP_EOL;
	}

	/**
	 * Generate a HTML span.
	 *
	 * @param  string  $value
	 * @param  array   $attributes
	 * @return string
	 */
	public static function span($value, $attributes = array())
	{
		return '<span'.static::attributes($attributes).'>'.static::entities($value).'</span>';
	}

	/**
	 * Generate a HTML link.
	 *
	 * <code>
	 *		// Generate a link to a location within the application
	 *		echo HTML::link('user/profile', 'User Profile');
	 *
	 *		// Generate a link to a location outside of the application
	 *		echo HTML::link('http://google.com', 'Google');
	 * </code>
	 *
	 * @param  string  $url
	 * @param  string  $title
	 * @param  array   $attributes
	 * @param  bool    $https
	 * @return string
	 */
	public static function link($url, $title, $attributes = array(), $https = false)
	{
		$url = static::entities(URL::to($url, $https));

		return '<a href="'.$url.'"'.static::attributes($attributes).'>'.static::entities($title).'</a>';
	}

	/**
	 * Copy of above but for admin key
	 * @param  [type]  $url        [description]
	 * @param  [type]  $title      [description]
	 * @param  array   $attributes [description]
	 * @param  boolean $https      [description]
	 * @return [type]              [description]
	 */
	public static function linkToAdmin($url, $title, $attributes = array(), $https = false)
	{
		$url = static::entities(URL::toAdmin($url, $https));

		return '<a href="'.$url.'"'.static::attributes($attributes).'>'.static::entities($title).'</a>';
	}

	/**
	 * Generate a HTTPS HTML link.
	 *
	 * @param  string  $url
	 * @param  string  $title
	 * @param  array   $attributes
	 * @return string
	 */
	public static function linkToSecure($url, $title, $attributes = array())
	{
		return static::link($url, $title, $attributes, true);
	}

	/**
	 * Generate an HTML link to an asset.
	 *
	 * The application index page will not be added to asset links.
	 *
	 * @param  string  $url
	 * @param  string  $title
	 * @param  array   $attributes
	 * @param  bool    $https
	 * @return string
	 */
	public static function linkToAsset($url, $title, $attributes = array(), $https = null)
	{
		$url = static::entities(URL::toAsset($url, $https));

		return '<a href="'.$url.'"'.static::attributes($attributes).'>'.static::entities($title).'</a>';
	}

	/**
	 * Generate an HTTPS HTML link to an asset.
	 *
	 * @param  string  $url
	 * @param  string  $title
	 * @param  array   $attributes
	 * @return string
	 */
	public static function linkToSecureAsset($url, $title, $attributes = array())
	{
		return static::linkToAsset($url, $title, $attributes, true);
	}


	/**
	 * Generate an HTML mailto link.
	 *
	 * The E-Mail address will be obfuscated to protect it from spam bots.
	 *
	 * @param  string  $email
	 * @param  string  $title
	 * @param  array   $attributes
	 * @return string
	 */
	public static function mailTo($email, $title = null, $attributes = array())
	{
		$email = static::email($email);

		if (is_null($title)) $title = $email;

		$email = '&#109;&#097;&#105;&#108;&#116;&#111;&#058;'.$email;

		return '<a href="'.$email.'"'.static::attributes($attributes).'>'.static::entities($title).'</a>';
	}

	/**
	 * Obfuscate an e-mail address to prevent spam-bots from sniffing it.
	 *
	 * @param  string  $email
	 * @return string
	 */
	public static function email($email)
	{
		return str_replace('@', '&#64;', static::obfuscate($email));
	}

	/**
	 * Generate an HTML image element.
	 *
	 * @param  string  $url
	 * @param  string  $alt
	 * @param  array   $attributes
	 * @return string
	 */
	public static function image($url, $alt = '', $attributes = array())
	{
		$attributes['alt'] = $alt;

		return '<img src="'.static::entities(URL::toAsset($url)).'"'.static::attributes($attributes).'>';
	}

	/**
	 * Generate an ordered list of items.
	 *
	 * @param  array   $list
	 * @param  array   $attributes
	 * @return string
	 */
	public static function ol($list, $attributes = array())
	{
		return static::listing('ol', $list, $attributes);
	}

	/**
	 * Generate an un-ordered list of items.
	 *
	 * @param  array   $list
	 * @param  array   $attributes
	 * @return string
	 */
	public static function ul($list, $attributes = array())
	{
		return static::listing('ul', $list, $attributes);
	}

	/**
	 * Generate an ordered or un-ordered list.
	 *
	 * @param  string  $type
	 * @param  array   $list
	 * @param  array   $attributes
	 * @return string
	 */
	private static function listing($type, $list, $attributes = array())
	{
		$html = '';

		foreach ($list as $key => $value)
		{
			// If the value is an array, we will recurse the function so that we can
			// produce a nested list within the list being built. Of course, nested
			// lists may exist within nested lists, etc.
			if (is_array($value))
			{
				$html .= static::listing($type, $value);
			}
			else
			{
				$html .= '<li>'.static::entities($value).'</li>';
			}
		}

		return '<'.$type.static::attributes($attributes).'>'.$html.'</'.$type.'>';
	}

	/**
	 * Build a list of HTML attributes from an array.
	 *
	 * @param  array   $attributes
	 * @return string
	 */
	public static function attributes($attributes)
	{
		$html = array();

		foreach ((array) $attributes as $key => $value)
		{
			// For numeric keys, we will assume that the key and the value are the
			// same, as this will conver HTML attributes such as "required" that
			// may be specified as required="required", etc.
			if (is_numeric($key)) $key = $value;

			if ( ! is_null($value))
			{
				$html[] = $key.'="'.static::entities($value).'"';
			}
		}

		return (count($html) > 0) ? ' '.implode(' ', $html) : '';
	}

	/**
	 * Obfuscate a string to prevent spam-bots from sniffing it.
	 *
	 * @param  string  $value
	 * @return string
	 */
	protected static function obfuscate($value)
	{
		$safe = '';

		foreach (str_split($value) as $letter)
		{
			// To properly obfuscate the value, we will randomly convert each
			// letter to its entity or hexadecimal representation, keeping a
			// bot from sniffing the randomly obfuscated letters.
			switch (rand(1, 3))
			{
				case 1:
					$safe .= '&#'.ord($letter).';';
					break;

				case 2:
					$safe .= '&#x'.dechex(ord($letter)).';';
					break;

				case 3:
					$safe .= $letter;
			}
		}

		return $safe;
	}

	/**
	 * Dynamically handle calls to custom macros.
	 *
	 * @param  string  $method
	 * @param  array   $parameters
	 * @return mixed
	 */
	public static function __callStatic($method, $parameters)
	{
	    if (isset(static::$macros[$method]))
	    {
	        return call_user_func_array(static::$macros[$method], $parameters);
	    }
	    
	    throw new \Exception("Method [$method] does not exist.");
	}

}
