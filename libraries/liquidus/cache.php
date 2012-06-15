<?php defined('LIQUIDUS') or die('Direct access not permitted.');
/**
 * A class for caching data to a file.
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

class LiquidusCache {
	/**
	 * Path to the file for storing cached data.
	 *
	 * @var string
	 */
	public $file;

	/**
	 * A LiquidusRegistry object for storing cached data.
	 *
	 * @var object
	 */
	protected $cache;

	/**
	 * The number of seconds to keep the cache valid for, 0 for no limit.
	 *
	 * @var integer
	 */
	protected $expire;

	/**
	 * An array contains LiquidusCache singletons.
	 *
	 * @var array
	 */
	static protected $instances;

	/**
	 * Set location for storing cache file.
	 *
	 * @param   string  $path  A file system path
	 * @return  void
	 */
	static public function setPath($path)
	{
		Liquidus::setPath($path, 'cache');
	}

	/**
	 * Get location for storing cache file.
	 *
	 * @return  string
	 */
	static public function getPath()
	{
		return Liquidus::getPath('cache');
	}

	/**
	 * Return a LiquidusCache singleton.
	 *
	 * @param   string   $signature  An unique signature string
	 * @param   integer  $expire     The number of seconds to wait before the cache is refreshed
	 * @return  mixed    LiquidusCache object on success, NULL on failure
	 */
	static public function & getInstance($signature, $expire = 3600)
	{
		isset(self::$instances) OR self::$instances = array();

		// Instantiate a new LiquidusCache object only if the given signature is not instantiated before.
		if ( ! isset(self::$instances[$signature]))
		{
			self::$instances[$signature] = NULL;

			try
			{
				self::$instances[$signature] = new LiquidusCache($signature, $expire);
			}
			catch (Exception $e)
			{
				echo '<p class="exception-msg">'.$e->getMessage().'</p>';
			}
		}

		return self::$instances[$signature];
	}

	/**
	 * Construct a LiquidusCache object.
	 *
	 * @param   string   $signature  An unique signature string
	 * @param   integer  $expire     The number of seconds to wait before the cache is refreshed
	 * @return  void
	 */
	public function __construct($signature, $expire = 3600)
	{
		if (($path = self::getPath()) != NULL)
		{
			// Create cache directory if not exists.
			is_readable($path) OR mkdir($path, 0755, TRUE);

			$this->file = $path.DS.md5($signature).'.cache';
			$exists     = file_exists($this->file);

			if ( ! $exists && ! is_writable($path))
			{
				throw new Exception(fText::compose(
					'Cache file for the specified signature, %s, does not exist and the directory for storing cache file is not writable.',
					$signature
				));
			}

			if ($exists && ! is_writable($this->file))
			{
				throw new Exception(fText::compose(
					'Cache file for the specified signature, %s, exists but is not writable.',
					$signature
				));
			}

			// Init the cache.
			$this->expire = intval($expire);
			$this->cache  = $exists ? unserialize(file_get_contents($this->file)) : new LiquidusRegistry();
			$this->state  = 'clean';
		}
		else
		{
			throw new Exception(fText::compose(
				'Directory for storing cache file is not defined. Please define it with %s::setPath method first.',
				__CLASS__
			));
		}
	}

	/**
	 * Always save cached data to cache file on object destruction.
	 *
	 * @return  void
	 */
	public function __destruct()
	{
		$this->save();
	}

	/**
	 * Set the number of seconds to keep the cache valid for, 0 for no limit.
	 *
	 * @param   integer  $expire  Number of seconds
	 * @return  void
	 */
	public function setExpire($expire)
	{
		$this->expire = intval($expire);
	}

	/**
	 * Calculate the expiration time for cached data.
	 *
	 * @return  integer
	 */
	protected function getExpire()
	{
		return $this->expire > 0 ? time() + $this->expire : $this->expire;
	}

	/**
	 * Get a value from the cache.
	 *
	 * @param   string  $key    The key to get value for
	 * @param   mixed   $value  The default value if cached data expired or does not exist
	 * @return  mixed
	 */
	public function get($key, $value = NULL)
	{
		if ($exists = $this->cache->get($key))
		{
			if ( ! $exists['expire'] || $exists['expire'] >= time())
			{
				$value = $exists['value'];

				// Recreate singleton if necessary.
				if (is_object($value))
				{
					$c = get_class($value);

					if (method_exists($c, 'getSingleton') AND method_exists($c, 'setSingleton'))
					{
						call_user_func_array(array($c, 'setSingleton'), array( & $value));
					}
				}
			}
			elseif ($exists['expire'])
			{
				// Remove expired data.
				$this->remove($key);
			}
		}

		return $value;
	}

	/**
	 * Try to set a value to the cache, but stop if a value already exists.
	 *
	 * @param   string   $key    The key to store as
	 * @param   mixed    $value  The value to store
	 * @return  boolean
	 */
	public function add($key, $value)
	{
		if (($exists = $this->cache->get($key)) && $exists['expire'] && $exists['expire'] >= time())
			return FALSE;

		// Store data to cache.
		$this->cache->set($key, array('value' => $value, 'expire' => $this->getExpire()));

		// Indicate that cache file should be refreshed.
		$this->state = 'dirty';

		return TRUE;
	}

	/**
	 * Set a value to the cache, overriding any existing value.
	 *
	 * @param   string  $key    The key to store as
	 * @param   mixed   $value  The value to store
	 * @return  void
	 */
	public function set($key, $value)
	{
		$this->cache->set($key, array('value' => $value, 'expire' => $this->getExpire()));

		// Indicate that cache file should be refreshed.
		$this->state = 'dirty';
	}

	/**
	 * Remove a key from the cache.
	 *
	 * @param   string  $key    The key to remove
	 * @return  void
	 */
	public function remove($key) {
		$this->cache->remove($key);

		// Indicate that cache file should be refreshed.
		$this->state = 'dirty';
	}

	/**
	 * Clear the WHOLE cache of every key.
	 *
	 * @return  void
	 */
	public function clear()
	{
		$this->cache->clear();

		// Indicate that cache file should be refreshed.
		$this->state = 'dirty';
	}

	/**
	 * Save cached data to file and randomly clean up expired values.
	 *
	 * @return  void
	 */
	public function save()
	{
		// Randomly clean the cache out.
		if (rand(0, 99) == 50) {
			foreach ($this->cache->export() AS $key => $value) {
				if ($value['expire'] && $value['expire'] < time()) {
					// Remove expired data.
					$this->remove($key);
				}
			}
		}

		if ($this->state == 'dirty') {
			// Save cached data to file.
			file_put_contents($this->file, serialize($this->cache));

			// Indicate that cache file is valid.
			$this->state = 'clean';
		}
	}
}
?>