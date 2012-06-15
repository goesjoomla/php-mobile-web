<?php defined('LIQUIDUS') or die('Direct access not permitted.');
/**
 * A class for generating Javascript code for creating slideshow presentation.
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

class LiquidusRendererBehaviorSlideshow {
	/**
	 * Generate Javascript code for creating slideshow.
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

		// Load slideshow assets.
		$renderer->addAsset('css', 'plugins.slideshow');
		$renderer->addAsset('js', 'plugins.slideshow');

		// Parse attributes to slideshow options.
		$options = array();

		foreach ($attributes AS $k => $v)
		{
			if ($k == 'behavior' OR $k == 'type')
				continue;

			$options[] = $k.': '.$v;
		}

		// Set slideshow initialization right after DOM is completely loaded.
		$renderer->add(
			'dom-ready',
			'Liquidus.slideshow_'.$node['id'].' = '.($attributes['type'] == 'image' ? 'ImageSlideshow' : 'new Liquidus.slideshow').'(document.getElementById(\''.$node['id'].'\'), {'.implode(', ', $options).'});'
		);

		return $html;
	}
}
?>