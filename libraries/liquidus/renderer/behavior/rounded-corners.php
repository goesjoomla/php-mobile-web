<?php defined('LIQUIDUS') or die('Direct access not permitted.');
/**
 * A class for generating HTML code for creating image based rounded corners box.
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

class LiquidusRendererBehaviorRoundedCorners {
	/**
	 * Alter rendered HTML code for creating image based rounded corners box.
	 *
	 * @param   object  $renderer    LiquidusRenderer object
	 * @param   object  $node        SimpleXMLElement object representing node XML declaration
	 * @param   object  $attributes  SimpleXMLElement object representing behavior attributes
	 * @param   string  $html        Rendered HTML code
	 * @return  string
	 */
	public function process( & $renderer, $node, $attributes, $html)
	{
		// Alter HTML code.
		$html = '<div class="rounded-corners">'
		      . "\n\t".'<div class="hd"><div class="c"></div></div>'
		      . "\n\t".'<div class="bd"><div class="c"><div class="s clearfix">'
		      . "\n".$html
		      . "\n\t".'</div></div></div>'
		      . "\n\t".'<div class="ft"><div class="c"></div></div>'
		      . "\n".'</div><!-- end .rounded-corners -->';

		return $html;
	}
}
?>