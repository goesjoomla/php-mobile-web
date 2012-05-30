<?php defined('LIQUIDUS') or die('Direct access not permitted.');
/**
 * A class for generating HTML code for UI switcher.
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

class LiquidusRendererGeneratorUiSwitcher {
	/**
	 * Generate HTML code for UI switcher.
	 *
	 * @param   object  $renderer    LiquidusRenderer object
	 * @param   object  $attributes  SimpleXMLElement object representing generator attributes
	 * @return  string
	 */
	public function process( & $renderer, $attributes)
	{
		$html = NULL;
		$opts = array();
		$link = $_SERVER['REQUEST_URI'].(strpos($_SERVER['REQUEST_URI'], '?') !== FALSE ? '&' : '?');

		// Generate HTML code for switching website UI.
		foreach (array('default', 'tablet', 'mobile', 'wap') AS $ui)
		{
			if ($ui != Liquidus::getUI() AND count($renderer->theme->xpath('liquidus:ui[@for="'.$ui.'"]/liquidus:code')))
			{
				$opts[] = '<li><a href="'.$link.'ui='.$ui.'" title="'.fText::compose('Switch to %s UI', $ui).'" class="'.strtolower($ui).'">'.fText::compose(ucfirst($ui)).'</a></li>';
			}
		}

		// Finalize HTML code.
		if (count($opts) > 1)
		{
			$html = '<dl class="inline switch-ui"><dt>'.fText::compose('Switch UI').'</dt><dd><ul class="inline">'.implode('', $opts).'</ul></dd></dl>';
		}

		return $html;
	}
}
?>