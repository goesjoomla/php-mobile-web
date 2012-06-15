<?php defined('LIQUIDUS') or die('Direct access not permitted.');
/**
 * A class for parsing XML file.
 *
 * This class uses the PHP's SimpleXMLElement class at its core and provides
 * additional serialization support.
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

class LiquidusXml {
	/**
	 * File system path to the loaded XML file.
	 *
	 * @var string
	 */
	public $file;

	/**
	 * A SimpleXMLElement object for working with XML data.
	 *
	 * @var object
	 */
	private $handler;

	/**
	 * A LiquidusRegistry object for storing all xpath query results.
	 *
	 * @var object
	 */
	private $registry = array();

	/**
	 * Construct a LiquidusXml object.
	 *
	 * @param   string  $xml_file  XML file path
	 * @return  void
	 */
	public function __construct($xml_file)
	{
		if (is_readable($xml_file))
		{
			$this->file    = $xml_file;
			$this->handler = simplexml_load_file($this->file);
			$this->registry = new LiquidusRegistry();
		}
		else
		{
			throw new Exception(fText::compose(
				'The file specified, %s, does not exist under the specified location.',
				basename($this->file)
			));
		}
	}

	/**
	 * Get XML nodes using xpath query.
	 *
	 * @param   string  $path  An XPath path
	 * @return  array
	 */
	public function xpath($path)
	{
		// Try to get result from registry first.
		$result = $this->registry->get($path);

		if (empty($result))
		{
			// Call the SimpleXMLElement's xpath method to get result.
			$result = $this->handler->xpath($path);

			// Then store the result to registry.
			$this->registry->set($path, $result);
		}

		return $result;
	}

	/**
	 * Forward all other method calls to SimpleXMLElement object.
	 *
	 * @param   string  $name  Method to call
	 * @param   array   $args  Arguments to pass to method call
	 * @return  mixed
	 */
	public function __call($name, $args)
	{
		if (method_exists($this->handler, $name))
		{
			return call_user_func_array(array($this->handler, $name), $args);
		}
		else
		{
			throw new Exception(fText::compose(
				'The class <q>%1$s</q> does not have any method named <q>%2$s</q>.',
				__CLASS__,
				$name
			));
		}
	}

	/**
	 * Convert the SimpleXMLElement object to string before serializing.
	 *
	 * @return  array  The object's variables to serialize
	 */
	public function __sleep()
	{
		$this->xml = $this->handler->asXML();

		return array('file', 'xml');
	}

	/**
	 * Recreate SimpleXMLElement object when woken up.
	 *
	 * @return void
	 */
	public function __wakeup()
	{
		$this->registry = new LiquidusRegistry();
		$this->handler  = simplexml_load_string($this->xml);
		$this->xml      = NULL;
	}
}
?>