<?php 

namespace Newton; 

class Redirect extends Response {

	/**
	 * Create a redirect response to application root.
	 *
	 * @param  int       $status
	 * @param  bool      $secure
	 * @return Redirect
	 */
	public static function home($status = 302, $https = false)
	{
		return static::to(URL::home($https), $status)->send();
	}

	/**
	 * Create a redirect response to the HTTP referrer.
	 *
	 * @param  int       $status
	 * @return Redirect
	 */
	public static function back($status = 302)
	{
		return static::to(Request::referrer(), $status)->send();
	}

	/**
	 * Create a redirect response.
	 *
	 * <code>
	 *		// Create a redirect response to a location within the application
	 *		return Redirect::to('user/profile');
	 *
	 *		// Create a redirect response with a 301 status code
	 *		return Redirect::to('user/profile', 301);
	 * </code>
	 *
	 * @param  string    $url
	 * @param  int       $status
	 * @param  bool      $https
	 * @return Redirect
	 */
	public static function to($url, $status = 302, $https = false)
	{
		return static::make('', $status)->header('Location', URL::to($url, $https))->send();
	}

	/**
	 * Same as above, except generates a url to admin
	 * 
	 * @param  [type]  $url    [description]
	 * @param  integer $status [description]
	 * @param  boolean $https  [description]
	 * @return [type]          [description]
	 */
	public static function toAdmin($url, $status = 302, $https = false)
	{
		return static::make('', $status)->header('Location', URL::toAdmin($url, $https))->send();
	}
	

	/**
	 * Create a redirect response to a HTTPS URL.
	 *
	 * @param  string    $url
	 * @param  int       $status
	 * @return Redirect
	 */
	public static function toSecure($url, $status = 302)
	{
		return static::to($url, $status, true)->send();
	}
}