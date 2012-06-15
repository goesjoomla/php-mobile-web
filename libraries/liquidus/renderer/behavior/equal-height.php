<?php defined('LIQUIDUS') or die('Direct access not permitted.');
/**
 * A class for generating Javascript code for creating tableless equal height columns.
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

class LiquidusRendererBehaviorEqualHeight {
	/**
	 * Generate Javascript code for creating tableless equal height columns.
	 *
	 * @param   object  $renderer    LiquidusRenderer object
	 * @param   object  $node        SimpleXMLElement object representing node XML declaration
	 * @param   object  $attributes  SimpleXMLElement object representing behavior attributes
	 * @param   string  $html        Rendered HTML code
	 * @return  string
	 */
	public function process( & $renderer, $node, $attributes, $html)
	{
		// Load base Javascript libraries.
		$renderer->addAsset('js', 'http://ajax.googleapis.com/ajax/libs/dojo/1.7.1/dojo/dojo.js');
		$renderer->addAsset('js', 'liquidus');

		$renderer->add(
			'dom-ready',
			'Liquidus.equalHeight('.($attributes['columns'] ? $attributes['columns'] : 'document.getElement(\'#'.$node['id'].'\').getChildren()').');'
		);

		return $html;
	}
}
?>