<?php defined('LIQUIDUS') or die('Direct access not permitted.');
/**
 * A class for generating HTML code for skip-to links.
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

class LiquidusRendererGeneratorSkiptoLinks {
	/**
	 * Generate HTML code for skip-to links.
	 *
	 * @param   object  $renderer    LiquidusRenderer object
	 * @param   object  $attributes  SimpleXMLElement object representing generator attributes
	 * @return  string
	 */
	public function process( & $renderer, $attributes)
	{
		$html = NULL;
		$opts = array();
		$link = $_SERVER['REQUEST_URI'];

		if (($available_places = $renderer->get('places')) != NULL)
		{
			// Get IDs for skip-to links.
			$places = $renderer->theme->xpath('liquidus:ui[@for="'.Liquidus::getUI().'"]/liquidus:code//*[@liquidus:place]');

			if ($places AND count($places))
			{
				// Generate HTML code for skip-to links injection.
				foreach ($places AS $place)
				{
					// Get place name.
					$place = $place->attributes('liquidus', TRUE);
					$place = (string) $place['place'];

					if (in_array($place, array_keys($available_places)))
					{
						$opts[] = '<li><a href="'.$link.'#'.$available_places[$place].'" title="'.fText::compose('Skip to %s', ($place = str_replace('-', ' ', $place))).'">'.fText::compose(ucfirst($place)).'</a></li>';
					}
				}
			}
		}

		if (count($opts))
		{
			$html = '<dl class="inline skip-to"><dt>'.fText::compose('Skip to').'</dt><dd><ul class="inline">'.implode('', $opts).'</ul></dd></dl>';
		}

		return $html;
	}
}
?>