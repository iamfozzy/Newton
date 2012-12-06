<?php

namespace Newton;

class Str {

	/**
	 * The pluralizer instance.
	 *
	 * @var Pluralizer
	 */
	public static $pluralizer;

	/**
	 * Get the default string encoding for the application.
	 *
	 * This method is simply a short-cut to Config::get('application.encoding').
	 *
	 * @return string
	 */
	public static function encoding()
	{
		return Config::load('newton')->encoding;
	}

	/**
	 * Get the length of a string.
	 *
	 * <code>
	 *		// Get the length of a string
	 *		$length = Str::length('Taylor Otwell');
	 *
	 *		// Get the length of a multi-byte string
	 *		$length = Str::length('Τάχιστη')
	 * </code>
	 *
	 * @param  string  $value
	 * @return int
	 */
	public static function length($value)
	{
		return (defined('MB_STRING')) ? mb_strlen($value, static::encoding()) : strlen($value);
	}

	/**
	 * Convert a string to lowercase.
	 *
	 * <code>
	 *		// Convert a string to lowercase
	 *		$lower = Str::lower('Taylor Otwell');
	 *
	 *		// Convert a multi-byte string to lowercase
	 *		$lower = Str::lower('Τάχιστη');
	 * </code>
	 *
	 * @param  string  $value
	 * @return string
	 */
	public static function lower($value)
	{
		return (defined('MB_STRING')) ? mb_strtolower($value, static::encoding()) : strtolower($value);
	}

	/**
	 * Convert a string to uppercase.
	 *
	 * <code>
	 *		// Convert a string to uppercase
	 *		$upper = Str::upper('Taylor Otwell');
	 *
	 *		// Convert a multi-byte string to uppercase
	 *		$upper = Str::upper('Τάχιστη');
	 * </code>
	 *
	 * @param  string  $value
	 * @return string
	 */
	public static function upper($value)
	{
		return (defined('MB_STRING')) ? mb_strtoupper($value, static::encoding()) : strtoupper($value);
	}

	/**
	 * Convert a string to title case (ucwords equivalent).
	 *
	 * <code>
	 *		// Convert a string to title case
	 *		$title = Str::title('taylor otwell');
	 *
	 *		// Convert a multi-byte string to title case
	 *		$title = Str::title('νωθρού κυνός');
	 * </code>
	 *
	 * @param  string  $value
	 * @return string
	 */
	public static function title($value)
	{
		if (defined('MB_STRING'))
		{
			return mb_convert_case($value, MB_CASE_TITLE, static::encoding());
		}

		return ucwords(strtolower($value));
	}

	/**
	 * Limit the number of characters in a string.
	 *
	 * <code>
	 *		// Returns "Tay..."
	 *		echo Str::limit('Taylor Otwell', 3);
	 *
	 *		// Limit the number of characters and append a custom ending
	 *		echo Str::limit('Taylor Otwell', 3, '---');
	 * </code>
	 *
	 * @param  string  $value
	 * @param  int     $limit
	 * @param  string  $end
	 * @return string
	 */
	public static function limit($value, $limit = 100, $end = '...')
	{
		if (static::length($value) <= $limit) return $value;

		if (defined('MB_STRING'))
		{
			return mb_substr($value, 0, $limit, static::encoding()).$end;
		}

		return substr($value, 0, $limit).$end;
	}

	/**
	 * Limit the number of words in a string.
	 *
	 * <code>
	 *		// Returns "This is a..."
	 *		echo Str::words('This is a sentence.', 3);
	 *
	 *		// Limit the number of words and append a custom ending
	 *		echo Str::words('This is a sentence.', 3, '---');
	 * </code>
	 *
	 * @param  string  $value
	 * @param  int     $words
	 * @param  string  $end
	 * @return string
	 */
	public static function words($value, $words = 100, $end = '...')
	{
		preg_match('/^\s*+(?:\S++\s*+){1,'.$words.'}/', $value, $matches);

		if (static::length($value) == static::length($matches[0]))
		{
			$end = '';
		}

		return rtrim($matches[0]).$end;
	}


	/**
	 * Generate a URL friendly "slug" from a given string.
	 *
	 * <code>
	 *		// Returns "this-is-my-blog-post"
	 *		$slug = Str::slug('This is my blog post!');
	 *
	 *		// Returns "this_is_my_blog_post"
	 *		$slug = Str::slug('This is my blog post!', '_');
	 * </code>
	 *
	 * @param  string  $title
	 * @param  string  $separator
	 * @return string
	 */
	public static function slug($title, $separator = '-')
	{
		// Remove all characters that are not the separator, letters, numbers, or whitespace.
		$title = preg_replace('![^'.preg_quote($separator).'\pL\pN\s]+!u', '', static::lower($title));

		// Replace all separator characters and whitespace by a single separator
		$title = preg_replace('!['.preg_quote($separator).'\s]+!u', $separator, $title);

		return trim($title, $separator);
	}


	/**
	 * Convert a string to an underscored, camel-cased class name.
	 *
	 * This method is primarily used to format task and controller names.
	 *
	 * <code>
	 *		// Returns "Task_Name"
	 *		$class = Str::classify('task_name');
	 *
	 *		// Returns "Taylor_Otwell"
	 *		$class = Str::classify('taylor otwell')
	 * </code>
	 *
	 * @param  string  $value
	 * @return string
	 */
	public static function classify($value)
	{
		$search = array('_', '-', '.');

		return str_replace(' ', '_', static::title(str_replace($search, ' ', $value)));
	}

	/**
	 * Return the "URI" style segments in a given string.
	 *
	 * @param  string  $value
	 * @return array
	 */
	public static function segments($value)
	{
		return array_diff(explode('/', trim($value, '/')), array(''));
	}

	/**
	 * Generate a random alpha or alpha-numeric string.
	 *
	 * <code>
	 *		// Generate a 40 character random alpha-numeric string
	 *		echo Str::random(40);
	 *
	 *		// Generate a 16 character random alphabetic string
	 *		echo Str::random(16, 'alpha');
	 * <code>
	 *
	 * @param  int	   $length
	 * @param  string  $type
	 * @return string
	 */
	public static function random($length, $type = 'alnum')
	{
		return substr(str_shuffle(str_repeat(static::pool($type), 5)), 0, $length);
	}

	/**
	 * Get the character pool for a given type of random string.
	 *
	 * @param  string  $type
	 * @return string
	 */
	protected static function pool($type)
	{
		switch ($type)
		{
			case 'alpha':
				return 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

			case 'alnum':
				return '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

			default:
				throw new \Exception("Invalid random string type [$type].");
		}
	}

}