<?php 

namespace Newton; 
use Closure, FilesystemIterator as fIterator;

class File {

	/**
	 * Determine if a file exists.
	 *
	 * @param  string  $path
	 * @return bool
	 */
	public static function exists($path)
	{
		return file_exists($path);
	}

	/**
	 * Get the contents of a file.
	 *
	 * <code>
	 *		// Get the contents of a file
	 *		$contents = File::get(path('app').'routes'.EXT);
	 *
	 *		// Get the contents of a file or return a default value if it doesn't exist
	 *		$contents = File::get(path('app').'routes'.EXT, 'Default Value');
	 * </code>
	 *
	 * @param  string  $path
	 * @param  mixed   $default
	 * @return string
	 */
	public static function get($path, $default = null)
	{
		return (file_exists($path)) ? file_get_contents($path) : value($default);
	}

	/**
	 * Write to a file.
	 *
	 * @param  string  $path
	 * @param  string  $data
	 * @return int
	 */
	public static function put($path, $data)
	{
		return file_put_contents($path, $data, LOCK_EX);
	}

	/**
	 * Append to a file.
	 *
	 * @param  string  $path
	 * @param  string  $data
	 * @return int
	 */
	public static function append($path, $data)
	{
		return file_put_contents($path, $data, LOCK_EX | FILE_APPEND);
	}

	/**
	 * Delete a file.
	 *
	 * @param  string  $path
	 * @return void
	 */
	public static function delete($path)
	{
		if (static::exists($path)) @unlink($path);
	}

	/**
	 * Move a file to a new location.
	 *
	 * @param  string  $path
	 * @param  string  $target
	 * @return void
	 */
	public static function move($path, $target)
	{
		return rename($path, $target);
	}

	/**
	 * Copy a file to a new location.
	 *
	 * @param  string  $path
	 * @param  string  $target
	 * @return void
	 */
	public static function copy($path, $target)
	{
		return copy($path, $target);
	}

	/**
	 * Extract the file extension from a file path.
	 * 
	 * @param  string  $path
	 * @return string
	 */
	public static function extension($path)
	{
		return pathinfo($path, PATHINFO_EXTENSION);
	}

	/**
	 * Get the file type of a given file.
	 *
	 * @param  string  $path
	 * @return string
	 */
	public static function type($path)
	{
		return filetype($path);
	}

	/**
	 * Get the file size of a given file.
	 *
	 * @param  string  $path
	 * @return int
	 */
	public static function size($path)
	{
		return filesize($path);
	}

	/**
	 * Get the file's last modification time.
	 *
	 * @param  string  $path
	 * @return int
	 */
	public static function modified($path)
	{
		return filemtime($path);
	}


	/**
	 * Create a new directory.
	 *
	 * @param  string  $path
	 * @param  int     $chmod
	 * @return void
	 */
	public static function mkdir($path, $chmod = 0777)
	{
		return ( ! is_dir($path)) ? mkdir($path, $chmod, true) : true;
	}

	/**
	 * Move a directory from one location to another.
	 *
	 * @param  string  $source
	 * @param  string  $destination
	 * @param  int     $options
	 * @return void
	 */
	public static function mvdir($source, $destination, $options = fIterator::SKIP_DOTS)
	{
		return static::cpdir($source, $destination, true, $options);
	}

	/**
	 * Recursively copy directory contents to another directory.
	 *
	 * @param  string  $source
	 * @param  string  $destination
	 * @param  bool    $delete
	 * @param  int     $options
	 * @return void
	 */
	public static function cpdir($source, $destination, $delete = false, $options = fIterator::SKIP_DOTS)
	{
		if ( ! is_dir($source)) return false;

		// First we need to create the destination directory if it doesn't
		// already exists. This directory hosts all of the assets we copy
		// from the installed bundle's source directory.
		if ( ! is_dir($destination))
		{
			mkdir($destination, 0777, true);
		}

		$items = new fIterator($source, $options);

		foreach ($items as $item)
		{
			$location = $destination.DS.$item->getBasename();

			// If the file system item is a directory, we will recurse the
			// function, passing in the item directory. To get the proper
			// destination path, we'll add the basename of the source to
			// to the destination directory.
			if ($item->isDir())
			{
				$path = $item->getRealPath();

				if (! static::cpdir($path, $location, $delete, $options)) return false;

				if ($delete) @rmdir($item->getRealPath());
			}
			// If the file system item is an actual file, we can copy the
			// file from the bundle asset directory to the public asset
			// directory. The "copy" method will overwrite any existing
			// files with the same name.
			else
			{
				if(! copy($item->getRealPath(), $location)) return false;

				if ($delete) @unlink($item->getRealPath());
			}
		}

		if ($delete) rmdir($source);
		
		return true;
	}

	/**
	 * Recursively delete a directory.
	 *
	 * @param  string  $directory
	 * @param  bool    $preserve
	 * @return void
	 */
	public static function rmdir($directory, $preserve = false)
	{
		if ( ! is_dir($directory)) return;

		$items = new fIterator($directory);

		foreach ($items as $item)
		{
			// If the item is a directory, we can just recurse into the
			// function and delete that sub-directory, otherwise we'll
			// just deleete the file and keep going!
			if ($item->isDir())
			{
				static::rmdir($item->getRealPath());
			}
			else
			{
				@unlink($item->getRealPath());
			}
		}

		if ( ! $preserve) @rmdir($directory);
	}

	/**
	 * Empty the specified directory of all files and folders.
	 *
	 * @param  string  $directory
	 * @return void
	 */
	public static function cleandir($directory)
	{
		return static::rmdir($directory, true);
	}

	/**
	 * Get the most recently modified file in a directory.
	 *
	 * @param  string       $directory
	 * @param  int          $options
	 * @return SplFileInfo
	 */
	public static function latest($directory, $options = fIterator::SKIP_DOTS)
	{
		$time = 0;

		$items = new fIterator($directory, $options);

		// To get the latest created file, we'll simply spin through the
		// directory, setting the latest file if we encounter a file
		// with a UNIX timestamp greater than the latest one.
		foreach ($items as $item)
		{
			if ($item->getMTime() > $time) $latest = $item;
		}

		return $latest;
	}



	/**
	 * Return the directory for the storage folders
	 * 
	 * @param  [type] $folder [description]
	 * @return [type]         [description]
	 */
	public static function storage($folder)
	{
		return BP . DS . 'storage' . DS . $folder;
	}

}