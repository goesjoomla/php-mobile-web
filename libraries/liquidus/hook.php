<?php defined('LIQUIDUS') or die('Direct access not permitted.');
/**
 * A class for hooking support.
 *
 * Usage:
 *
 * $hook = new LiquidusHook();
 *
 * $hook->add('pre-render', 'checkEnvironment');           // checkEnvironment is a function
 * $hook->add('pre-render', array($obj, 'filterInput'));   // $obj is an object, filterInput is a method of that object
 *
 * $hook->set('post-render', array('Output', 'compress')); // Output is a class name, compress is a static method of that class
 *
 * $hook->call('pre-render', array( & $this, & $content));
 *
 * { ... }
 *
 * $hook->call('post-render', array( & $this, & $rendered_content));
 *
 * See registry.php file for all other methods besides add, set and call.
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

class LiquidusHook extends LiquidusRegistry {
	/**
	 * Register an universal hook class or object.
	 *
	 * When a hook point is called, the call method will check if a registered
	 * hook class/object has appropriate method. If has then the method will be
	 * called.
	 *
	 * See following example to understand how appropriate method is generated
	 * from hook point:
	 *
	 * Hook point   Appropriate method
	 * ----------   ------------------
	 *
	 * pre-render   PreRender
	 * post-render  PostRender
	 * pre-output   PreOutput
	 * post-output  PostOutput
	 *
	 * @param   string  $hook  An object or a class name
	 * @return  void
	 */
	public function register($hook) {
		$this->add('*', $hook);
	}

	/**
	 * Call all registered hooks of a hook point.
	 *
	 * @param   string  $point  Hook point
	 * @param   array   $args   Array of arguments to pass to every registered hook
	 * @return  array   Array of returned variables
	 */
	public function call($point, $args)
	{
		$results = array();
		$hooks   = $this->get($point, array(), TRUE);

		foreach ($hooks AS $hook)
		{
			$results[] = call_user_func_array($hook, $args);
		}

		if (($hooks = $this->get('*', NULL, TRUE)) != NULL)
		{
			foreach ($hooks AS $hook)
			{
				// Generate method name.
				$method = preg_replace('/\-([a-z])/e', 'strtoupper(\'$1\')', ucfirst($point));

				if (method_exists($hook, $method))
				{
					$results[] = call_user_func_array(array($hook, $method), $args);
				}
			}
		}

		return $results;
	}
}
?>