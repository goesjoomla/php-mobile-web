<?php
/**
 * Core class of F6 Liquidus web template system.
 *
 * @copyright  Copyright (c) 2011 Manh-Cuong Nguyen
 * @author     Manh-Cuong Nguyen [nmc] <cuongnm@f6studio.com>
 * @license    http://f6studio.com/license
 *
 * @package    Liquidus
 * @link       http://f6studio.com/Liquidus
 *
 * @version    1.0.0
 */

// Mark the availability of Liquidus core class
define('LIQUIDUS', 1);

class Liquidus {
	/**
	 * Liquidus root directory.
	 *
	 * This is the directory where Liquidus is installed and must be set in the
	 * bootstrap file right after this class is loaded:
	 *
	 * Liquidus::setPath('/abs/path/to/liquidus/directory');
	 *
	 * Unless you move this file around, this path is always the parent directory
	 * of the directory that contains this file.
	 *
	 * @var string
	 */
	static private $path;

	/**
	 * Assets directory.
	 *
	 * This is the relative path from Liquidus root directory to the directory
	 * containing web accessible assets such as stylesheet, script and image
	 * files. If you move the 'assets' directory to another location, you must
	 * override the default setting using following method:
	 *
	 * Liquidus::setPath('rel/path/to/assets/directory', 'assets');
	 *
	 * @var string
	 */
	static private $assets = 'assets';

	/**
	 * Cache directory.
	 *
	 * This is the relative path from Liquidus root directory to the directory
	 * for storing cache files. If you move the 'cache' directory to another
	 * location, you must override the default setting using following method:
	 *
	 * Liquidus::setPath('rel/path/to/cache/directory', 'cache');
	 *
	 * @var string
	 */
	static private $cache = 'cache';

	/**
	 * Templates directory.
	 *
	 * This is the relative path from Liquidus root directory to the directory
	 * containing header and footer templates for all website UI. If you move the
	 * 'templates' directory to another location, you must override the default
	 * setting using following method:
	 *
	 * Liquidus::setPath('rel/path/to/templates/directory', 'templates');
	 *
	 * @var string
	 */
	static private $templates = 'templates';

	/**
	 * Themes directory.
	 *
	 * This is the relative path from Liquidus root directory to the directory
	 * containing XML-based theme declaration. If you move the 'themes' directory
	 * to another location, you must override the default setting using following
	 * method:
	 *
	 * Liquidus::setPath('rel/path/to/themes/directory', 'themes');
	 *
	 * @var string
	 */
	static private $themes = 'themes';

	/**
	 * User prefered or auto-detected website user interface.
	 *
	 * User can override the auto-detected UI by adding the following name/value
	 * pair to the website URL:
	 *
	 * ui=default|tablet|mobile|wap
	 *
	 * My wife has a 5-inch tablet PC and she feel more comfortable with my
	 * website's UI for smart-phone than the auto-served one for tablet PC. She
	 * then manually overrides the auto-detected UI by inputting following URL
	 * into the address bar of her tablet PC's web browser:
	 *
	 * http://f6studio.com/?ui=mobile
	 *
	 * After that, if she does not clear all cookies stored by her tablet PC's
	 * web browser, she would always see the prefered mobile UI every time she
	 * comes back to my website.
	 */
	static private $ui;

	/**
	 * Liquidus class prefix.
	 *
	 * This should be cleaned out from all Liquidus core class name when
	 * searching for appropriated class declaration file.
	 *
	 * @var string
	 */
	static private $prefix = 'Liquidus';

	/**
	 * An array of imported codebase files.
	 *
	 * @var array
	 */
	static private $imported = array();

	/**
	 * Set the Liquidus root directory.
	 *
	 * @param   string  $path  A file system path
	 * @param   string  $type  Directory purpose
	 * @return  void
	 */
	static public function setPath($path, $type = NULL)
	{
		// Prepare Liquidus root directory.
		$root = isset(self::$path) ? self::$path : dirname(dirname(__FILE__));

		switch ($type)
		{
			case 'assets':
				self::$assets = str_replace($root.DS, '', $path);
			break;

			case 'cache':
				self::$cache = str_replace($root.DS, '', $path);
			break;

			case 'templates':
				self::$templates = str_replace($root.DS, '', $path);
			break;

			case 'themes':
				self::$themes = str_replace($root.DS, '', $path);
			break;

			default:
				self::$path = $path;
			break;
		}
	}

	/**
	 * Get the Liquidus root directory.
	 *
	 * @param   string  $type  Directory purpose
	 * @return  string
	 */
	static public function getPath($type = NULL)
	{
		// Prepare Liquidus root directory.
		$root = isset(self::$path) ? self::$path : dirname(dirname(__FILE__));

		switch ($type)
		{
			case 'assets':
				$path = preg_match('#^(/|[a-z]:)#i', self::$assets) ? self::$assets : $root.DS.self::$assets;
			break;

			case 'cache':
				$path = preg_match('#^(/|[a-z]:)#i', self::$cache) ? self::$cache : $root.DS.self::$cache;
			break;

			case 'templates':
				$path = preg_match('#^(/|[a-z]:)#i', self::$templates) ? self::$templates : $root.DS.self::$templates;
			break;

			case 'themes':
				$path = preg_match('#^(/|[a-z]:)#i', self::$themes) ? self::$themes : $root.DS.self::$themes;
			break;

			default:
				$path = $root;
			break;
		}

		return $path;
	}

	/**
	 * Set website user interface.
	 *
	 * @param   string  $ui  Either default, popup, tablet, mobile or wap
	 * @return  void
	 */
	static public function setUI($ui)
	{
		// Validate UI.
		if (in_array($ui, array('default', 'popup', 'tablet', 'mobile', 'wap')))
		{
			self::$ui = $ui;
		}
	}

	/**
	 * Get user prefered or auto-detected website user interface.
	 *
	 * @return  string  Either default, popup, tablet, mobile or wap
	 */
	static public function getUI()
	{
		if ( ! isset(self::$ui))
		{
			// Get user preference.
			if (isset($_GET['ui']))
			{
				$prefered = trim(strtolower($_GET['ui']));
			}
			elseif (isset($_POST['ui']))
			{
				$prefered = trim(strtolower($_POST['ui']));
			}
			elseif (isset($_COOKIE['ui']))
			{
				$prefered = trim(strtolower($_COOKIE['ui']));
			}

			// Validate user preference.
			if (isset($prefered) AND in_array($prefered, array('default', 'popup', 'tablet', 'mobile', 'wap')))
			{
				// Remember user preference.
				if ($prefered != 'popup' AND ( ! isset($_COOKIE['ui']) OR $prefered != $_COOKIE['ui']))
				{
					setcookie('ui', $prefered, time() + (60 * 60 * 24 * 365), self::getWebPath(self::getPath()));
				}

				self::$ui = $prefered;
			}

			// Auto-detect if user does not prefer a specific UI.
			if ( ! isset(self::$ui))
			{
				self::$ui = LiquidusClientDeviceDetector::detect();
			}
		}

		return self::$ui;
	}

	/**
	 * Get web path from file system path.
	 *
	 * @param   string  $path  A file system path
	 * @return  string
	 */
	static public function getWebPath($path)
	{
		if (DS == '/')
		{
			$path = str_replace('\\', '/', $path);
		}
		else
		{
			$path = str_replace('/', '\\', $path);
		}

		return fFilesystem::translateToWebPath($path);
	}

	/**
	 * Load a codebase file using given pattern.
	 *
	 * The pattern is simply the relative path from the base directory to the
	 * file needs to be included with:
	 *
	 * 1. All occurrences of the directory separator character, / on *nix system
	 * and \ on Windows system, must be replaced with . character.
	 *
	 * 2. The .php file extension must be removed.
	 *
	 * For easy understanding, let's take a look at following example:
	 *
	 * Liquidus::import('libraries.liquidus.template');
	 *
	 * Assuming Liquidus is installed in the directory /var/www/liquidus, the
	 * method call above will attempt to load the file which is accessible using
	 * the following file system path:
	 *
	 * /var/www/liquidus/libraries/liquidus/template.php
	 *
	 * If this method is registered as __autoload() implementation then only
	 * class name is available when this method is called for auto-loading a
	 * class. In this case, the method will search thru all sub-directories of
	 * the base directory until a matched file is found or there is no any
	 * sub-directory left.
	 *
	 * The default base directory is the directory where Liquidus is installed.
	 * If you intend to call this method manually for loading a class instead of
	 * register it as __autoload() implementation, then you can change the base
	 * directory when calling this method. To do this, you simply need to pass
	 * either a relative file system path from the Liquidus root directory or an
	 * absolute file system path as the second argument for this method as shown
	 * below:
	 *
	 * Liquidus::import('liquidus.template', 'libraries');
	 *
	 * or
	 *
	 * Liquidus::import('template', '/var/www/liquidus/libraries/liquidus');
	 *
	 * The two method calls above will work just like the first example.
	 *
	 * @param   string   $pattern  A pattern that describes file to be included
	 * @param   string   $base     A base directory to look for codebase file
	 * @return  boolean  TRUE on success, FALSE on failure
	 */
	static public function import($pattern, $base = NULL)
	{
		// Prepare base directory.
		if (empty($base))
		{
			$base = self::getPath();
		}
		elseif (substr($base, 0, 1) != '/' AND ! in_array(substr($base, 1, 2), array(':\\', ':/')))
		{
			$base = self::getPath().DS.$base;
		}

		// Import result.
		$success = FALSE;

		if (strpos($pattern, '.') > 0)
		{
			// Convert pattern to file path.
			$path = $base.DS.str_replace('.', DS, $pattern).'.php';

			// Simply return true if the codebase file described by given pattern is already included.
			if (in_array($path, self::$imported))
				return TRUE;

			// Include the codebase file if exists.
			if (is_readable($path))
			{
				require $path;

				// Add file path to the imported codebase files array.
				self::$imported[] = $path;

				// Update import result.
				$success = TRUE;
			}
		}
		else
		{
			// Simply return true if the needed class is already declared.
			if (class_exists($pattern, FALSE))
				return TRUE;

			// Check if the needed class is either a Liquidus core class or Flourish library's class.
			if (strpos($pattern, self::$prefix) === 0)
			{
				// Clean the Liquidus core class prefix from class name.
				$pattern = str_replace(self::$prefix, '', $pattern);

				// Get directory structure from class name.
				$pattern = trim(preg_replace('/([A-Z])/e', 'DS.strtolower(\'$1\')', $pattern), DS);

				// Change the base directory accordingly.
				$base = self::getPath().DS.'libraries'.DS.'liquidus';
			}
			elseif (preg_match('/^f[A-Z]/', $pattern))
			{
				// Change base directory to the Flourish library directory.
				$base = self::getPath().DS.'libraries'.DS.'flourish';
			}

			// Try to search for class declaration file.
			$path = self::search($pattern.'.php', $base);

			if ( ! empty($path))
			{
				require $path;

				// Add file path to the imported codebase files array.
				self::$imported[] = $path;

				// Update import result.
				$success = TRUE;
			}
		}

		return $success;
	}

	/**
	 * Recursively search thru a directory and its sub-directories for a file.
	 *
	 * @param   string   $file  A file to search for
	 * @param   boolean  $base  A directory to start searching from
	 * @return  string   Full file path on success, NULL on failure
	 */
	static private function search($file, $base = NULL)
	{
		// Prepare base directory.
		if (empty($base))
		{
			$base = self::getPath();
		}
		elseif (substr($base, 0, 1) != '/' AND ! in_array(substr($base, 1, 2), array(':\\', ':/')))
		{
			$base = self::getPath().DS.$base;
		}

		// Search result.
		$found = NULL;

		if (is_readable($base.DS.$file))
		{
			// Store the full file system path if file exists.
			$found = $base.DS.$file;
		}
		elseif ($dp = opendir($base))
		{
			// Otherwise, look in sub-directories.
			while (FALSE !== ($f = readdir($dp)))
			{
				if (substr($f, 0, 1) != '.' AND is_dir($base.DS.$f))
				{
					if (($found = self::search($file, $base.DS.$f)) != NULL)
						break;
				}
			}
			closedir($dp);
		}

		return $found;
	}
}

// Register Liquidus::import as __autoload() implementation.
spl_autoload_register(array('Liquidus', 'import'));

// Then enable auto class loader for unserialization callback.
ini_set('unserialize_callback_func', 'spl_autoload_call');
?>