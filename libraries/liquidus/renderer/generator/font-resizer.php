<?php defined('LIQUIDUS') or die('Direct access not permitted.');
/**
 * A class for generating HTML code for font resizer.
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

class LiquidusRendererGeneratorFontResizer {
	/**
	 * Generate HTML code for font resizer.
	 *
	 * @param   object  $renderer    LiquidusRenderer object
	 * @param   object  $attributes  SimpleXMLElement object representing generator attributes
	 * @return  string
	 */
	public function process( & $renderer, $attributes)
	{
		// Load base Javascript libraries.
		$renderer->addAsset('js', 'http://ajax.googleapis.com/ajax/libs/dojo/1.7.1/dojo/dojo.js');
		$renderer->addAsset('js', 'liquidus');

		// Parse attributes.
		parse_str($attributes, $attributes);

		isset($attributes['maxFontSize']) OR $attributes['maxFontSize'] = 16;
		isset($attributes['minFontSize']) OR $attributes['minFontSize'] = 8;
		isset($attributes['resizeStep'])  OR $attributes['resizeStep']  = 1;

		// Add document onUnload event.
		$renderer->add(
			'dom-ready',
			'dojo.connect(window, \'onunload\', function() { Liquidus.cookie(\'font-size\', parseInt(dojo.getStyle(dojo.query(\'body\')[0], \'fontSize\'))); });'
		);

		// Generate HTML code.
		$html = '<dl class="inline font-resizer"><dt>'.fText::compose('Font size').'</dt><dd><ul class="inline">';

		// Increasement link.
		$html .= '<li><a href="javascript:void(0)" title="'.fText::compose('Increase font size').'" onclick="Liquidus.fontResizer(\'+\')">'.fText::compose('Bigger').'</a></li>';

		// Reset link.
		$html .= '<li><a href="javascript:void(0)" title="'.fText::compose('Reset to default font size').'" onclick="Liquidus.fontResizer()">'.fText::compose('Reset').'</a></li>';

		// Decreasement link.
		$html .= '<li><a href="javascript:void(0)" title="'.fText::compose('Decrease font size').'" onclick="Liquidus.fontResizer(\'-\')">'.fText::compose('Smaller').'</a></li>';

		// Finalize HTML code.
		$html .= '</ul></dd></dl>';

		// Attach script to load user font size.
		$html .= '<script type="text/javascript">(function() { Liquidus.fontResizer.max = '.(int) $attributes['maxFontSize'].'; Liquidus.fontResizer.min = '.(int) $attributes['minFontSize'].'; Liquidus.fontResizer.step = '.(int) $attributes['resizeStep'].'; Liquidus.fontResizer.normal = parseInt(dojo.getStyle(dojo.query(\'body\')[0], \'fontSize\')); var ufs = Liquidus.cookie(\'font-size\'); if (ufs && ufs != Liquidus.fontResizer.normal) dojo.setStyle(dojo.query(\'body\')[0], \'fontSize\', ufs + \'px\'); })();</script>';

		return $html;
	}
}
?>