<?php defined('LIQUIDUS') or die('Direct access not permitted.');
/**
 * A class for storing and retrieving data.
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

class LiquidusRegistry {
	/**
	 * Registry for storing data.
	 *
	 * @var array
	 */
	protected $registry;

	/**
	 * Construct a LiquidusRegistry object.
	 *
	 * @return  void
	 */
	public function __construct()
	{
		$this->registry = array();
	}

	/**
	 * Get data from registry.
	 *
	 * @param   string   $key          Key name to retrieve data from registry
	 * @param   mixed    $value        Default value if no data found for the given key name
	 * @param   boolean  $returnArray  TRUE to always return associated data as array
	 * @return  mixed    The associated data
	 */
	public function get($key, $value = NULL, $returnArray = FALSE) {
		if ( ! isset($this->registry[$key]))
			return $value;

		if ( ! $returnArray AND count($this->registry[$key]) == 1)
			return $this->registry[$key][0];

		return $this->registry[$key];
	}

	/**
	 * Prepend or append data to a key in registry and return the final data.
	 *
	 * @param   string   $key      Key name
	 * @param   mixed    $value    Value to add
	 * @param   boolean  $unshift  TRUE to prepend, FALSE to append
	 * @return  mixed    The final data
	 */
	public function add($key, $value, $unshift = FALSE) {
		if ( ! isset($this->registry[$key]))
		{
			$this->registry[$key] = array($value);
		}
		else
		{
			$unshift ? array_unshift($this->registry[$key], $value) : array_push($this->registry[$key], $value);
		}

		return $this->get($key);
	}

	/**
	 * Add data to a key in registry if not already exists.
	 *
	 * @param   string  $key    Key name
	 * @param   mixed   $value  Value to add
	 * @return  mixed   The associated data
	 */
	public function def($key, $value) {
		if ( ! isset($this->registry[$key]))
		{
			$this->add($key, $value);
		}

		return $this->get($key);
	}

	/**
	 * Set (override) data for a key in registry and return the overridden data.
	 *
	 * @param   string  $key    Key name
	 * @param   mixed   $value  Value to set
	 * @return  mixed   The overridden data
	 */
	public function set($key, $value) {
		$overridden           = $this->get($key);
		$this->registry[$key] = array($value);

		return $overridden;
	}

	/**
	 * Remove a key from registry and return its data.
	 *
	 * @param   string  $key    Key name
	 * @param   mixed   $value  Default value if no data found for the given key name
	 * @return  mixed   The associated data
	 */
	public function remove($key, $value = NULL) {
		$value = $this->get($key, $value);

		unset($this->registry[$key]);

		return $value;
	}

	/**
	 * Clear the WHOLE registry of every key.
	 *
	 * @return  void
	 */
	public function clear() {
		$this->registry = array();
	}

	/**
	 * Export the WHOLE registry of every key.
	 *
	 * @param   boolean  $clear  TRUE to clear the WHOLE registry after exporting
	 * @return  array
	 */
	public function export($clear = FALSE) {
		$registry = array();

		foreach (array_keys($this->registry) AS $key)
		{
			$registry[$key] = $this->get($key);
		}

		// Clear the WHOLE registry if requested.
		$clear AND $this->clear();

		return $registry;
	}
}
?>