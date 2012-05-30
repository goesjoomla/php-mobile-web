<?php defined('LIQUIDUS') or die('Direct access not permitted.');
/**
 * A class for generating HTML code for creating ribbon using CSS3.
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

class LiquidusRendererBehaviorRibbon {
	/**
	 * Alter rendered HTML code for creating ribbon using CSS3.
	 *
	 * @param   object  $renderer    LiquidusRenderer object
	 * @param   object  $node        SimpleXMLElement object representing node XML declaration
	 * @param   object  $attributes  SimpleXMLElement object representing behavior attributes
	 * @param   string  $html        Rendered HTML code
	 * @return  string
	 */
	public function process( & $renderer, $node, $attributes, $html)
	{
		// Load CSS3 ribbon stylesheet.
		$renderer->addAsset('css', 'plugins.ribbon');

		// Alter HTML code.
		$html = '<div class="ribbon'.($attributes['orientation'] == 'vertical' ? ' vertical' : '').'">'
		      . "\n\t".'<div class="rectangle">'."\n".$html."\n\t".'</div>'
		      . "\n\t".'<div class="triangle-l"></div>'
		      . "\n\t".'<div class="triangle-r"></div>'
		      . "\n".'</div><!-- end .ribbon'.($attributes['orientation'] == 'vertical' ? '.vertical' : '').' -->';

		return $html;
	}
}
?>