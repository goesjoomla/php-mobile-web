<?php defined('LIQUIDUS') or die('Direct access not permitted.');
/**
 * A class for generating HTML code for web site logo.
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

class LiquidusRendererGeneratorSiteLogo {
	/**
	 * Generate HTML code for web site logo.
	 *
	 * @param   object  $renderer    LiquidusRenderer object
	 * @param   object  $attributes  SimpleXMLElement object representing generator attributes
	 * @return  string
	 */
	public function process( & $renderer, $attributes)
	{
		$html = NULL;

		// Parse attributes.
		parse_str($attributes, $attributes);

		$url   = $renderer->get('base')       ? $renderer->get('base')       : (string) $attributes['url'];
		$img   = $renderer->get('site-logo')  ? $renderer->get('site-logo')  : (string) $attributes['image'];
		$title = $renderer->get('site-title') ? $renderer->get('site-title') : (string) $attributes['title'];
		$desc  = $renderer->get('site-desc')  ? $renderer->get('site-desc')  : (string) $attributes['description'];

		// Fine-tune logo image URL.
		if ( ! preg_match('#^(https?://|/)#', $img))
		{
			if (is_readable(Liquidus::getPath('assets').DS.'img'.DS.$img))
			{
				$img = Liquidus::getWebPath(Liquidus::getPath('assets').DS.'img'.DS.$img);
			}
			elseif (is_readable(Liquidus::getPath().DS.$img))
			{
				$img = Liquidus::getWebPath(Liquidus::getPath().DS.$img);
			}
			elseif (is_readable($_SERVER['DOCUMENT_ROOT'].DS.trim($renderer->get('base'), '/\\').DS.$img))
			{
				$img = Liquidus::getWebPath($_SERVER['DOCUMENT_ROOT'].DS.trim($renderer->get('base'), '/\\').DS.$img);
			}
			elseif (is_readable($_SERVER['DOCUMENT_ROOT'].DS.$img))
			{
				$img = Liquidus::getWebPath($_SERVER['DOCUMENT_ROOT'].DS.$img);
			}
		}

		if ( ! empty($url))
		{
			// Generate HTML code for logo injection.
			$html  = '<h1 class="logo"><a href="'.$url.'" title="'.$title.'">';

			// Site logo or title.
			if ( ! empty($img))
			{
				$html .= '<img src="'.$img.'" alt="'.$title.'" />';
			}
			else
			{
				$html .= $title;
			}

			$html .= '</a>';

			// Site description.
			if ( ! empty($desc))
			{
				$html .= '<br /><span class="description">'.$desc.'</span>';
			}

			// Finalize HTML code.
			$html .= '</h1>';
		}

		return $html;
	}
}
?>