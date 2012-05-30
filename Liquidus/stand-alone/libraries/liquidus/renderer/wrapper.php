<?php defined('LIQUIDUS') or die('Direct access not permitted.');
/**
 * A class for generating HTML code that wraps around content.
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

class LiquidusRendererWrapper {
	/**
	 * Generate HTML code for wrapping around content.
	 *
	 * @param   mixed   $content     Either a string or an object with title and content variables
	 * @param   mixed   $attributes  Either a SimpleXMLElement object or an associative array
	 * @return  string
	 */
	static public function wrap($content, $attributes)
	{
		$wrapper = isset($attributes['wrapper']) ? (string) $attributes['wrapper'] : 'LiquidusRaw';

		// Localize wrapper attributes.
		$attribs = $attributes instanceof SimpleXMLElement ? array_shift(get_object_vars($attributes))
		                                                   : (array) $attributes;

		// Define common wrapper attributes.
		foreach (array('class' => '', 'hideIfEmpty' => 0, 'showHeading' => 1, 'headingLevel' => 3) AS $k => $v)
		{
			if (isset($attribs[$k]))
				continue;

			$attribs[$k] = $v;
		}

		// Convert string content to object.
		if (is_string($content))
		{
			$tmp     = $content;
			$content = new stdClass;

			// Try to get title.
			if ($wrapper != 'LiquidusRaw' AND preg_match('/<h([1-6])([^>]*)>/', $tmp, $match))
			{
				list($content->content, $content->title) = explode($match[0], $tmp, 2);
				list($content->title, $tmp)              = explode('</h'.$match[1].'>', $content->title, 2);

				$content->content .= $tmp;

				// Make sure content is not empty.
				if (empty($content->content))
				{
					$content->content = $content->title;
					$content->title   = NULL;
				}
				else
				{
					// Embedded title found, reset heading level attribute.
					$attribs['headingLevel'] = $match[1].$match[2];
				}
			}
			else
			{
				$content->title   = NULL;
				$content->content = $tmp;
			}
		}

		$content->content = trim($content->content);

		// Generate class attribute if content is visible.
		if ( ! empty($content->content) OR ! $attribs['hideIfEmpty'])
		{
			$classes = empty($attribs['class']) ? array() : array($attribs['class']);

			// Add class prefix.
			$wrapper == 'LiquidusRaw' OR array_unshift($classes, trim(preg_replace('/([A-Z])/e', '\'-\'.strtolower(\'$1\')', $wrapper), '-'));

			// Add class suffix.
			$classSuffix = isset($content->classSuffix) ? $content->classSuffix : $attribs['classSuffix'];

			if ( ! empty($classSuffix))
			{
				if (substr($classSuffix, 0, 1) == ' ' OR ! isset($classes[0]))
				{
					$classes[] = trim($classSuffix);
				}
				elseif (isset($classes[0]))
				{
					$classes[] = $classes[0].$classSuffix;
				}
			}

			// Finalize class attribute.
			$attribs['class'] = implode(' ', $classes);

			// Call appropriate method to wrap content.
			if (method_exists(__CLASS__, $wrapper))
			{
				try
				{
					$content = call_user_func_array(array(__CLASS__, $wrapper), array($content, $attribs));
				}
				catch (Exception $e)
				{
					throw $e;
				}
			}
			else
			{
				throw new Exception(fText::compose(
					'Wrapper method <q>%s</q> is not declared.',
					$wrapper
				));
			}
		}
		else
		{
			$content = NULL;
		}

		return $content;
	}

	/**
	 * LiquidusRaw wrapper.
	 *
	 * @param   mixed   $content  An object with title and content variables
	 * @param   mixed   $attribs  An associative array of wrapper attributes
	 * @return  string
	 */
	static private function LiquidusRaw($content, $attribs)
	{
		$html = $content->content;

		if ( ! empty($attribs['class']))
		{
			$html = '<div class="'.$attribs['class'].'">'."\n".$html."\n".'</div>';
		}

		return $html;
	}

	/**
	 * LiquidusStandard wrapper.
	 *
	 * @param   mixed   $content  An object with title and content variables
	 * @param   mixed   $attribs  An associative array of wrapper attributes
	 * @return  string
	 */
	static private function LiquidusStandard($content, $attribs)
	{
		$html = "\t".'<div class="content">'."\n".$content->content."\n\t".'</div>';

		if (isset($content->title) AND intval($attribs['showHeading']))
		{
			$html = "\t".'<h'.$attribs['headingLevel'].(strpos($attribs['headingLevel'], 'class="') === FALSE ? ' class="heading"' : '').'>'
			      . '<span>'.$content->title.'</span></h'.intval($attribs['headingLevel']).'>'."\n".$html;
		}

		$html = '<div class="'.$attribs['class'].'">'."\n".$html."\n".'</div>';

		return $html;
	}

	/**
	 * LiquidusInline wrapper.
	 *
	 * @param   mixed   $content  An object with title and content variables
	 * @param   mixed   $attribs  An associative array of wrapper attributes
	 * @return  string
	 */
	static private function LiquidusInline($content, $attribs)
	{
		$html = '<dl class="'.str_replace('liquidus-inline', 'inline liquidus-inline', $attribs['class']).'">'."\n\t"
		      . '<dt'.(intval($attribs['showHeading']) ? '' : ' class="hidden"').'><span>'.$content->title.'</span></dt>'."\n\t"
		      . '<dd>'."\n".$content->content."\n\t".'</dd>'."\n"
		      . '</dl>';

		return $html;
	}

	/**
	 * LiquidusHover wrapper.
	 *
	 * @param   mixed   $content  An object with title and content variables
	 * @param   mixed   $attribs  An associative array of wrapper attributes
	 * @return  string
	 */
	static private function LiquidusHover($content, $attribs)
	{
		$html = '<dl class="'.str_replace('liquidus-hover', 'hover-menu liquidus-hover', $attribs['class']).'">'."\n\t"
		      . '<dt><span>'.$content->title.'</span></dt>'."\n\t"
		      . '<dd>'."\n"
		      . $content->content."\n\t"
		      . '</dd>'."\n"
		      . '</dl>';

		return $html;
	}

	/**
	 * LiquidusTab wrapper.
	 *
	 * @param   mixed   $content  An object with title and content variables
	 * @param   mixed   $attribs  An associative array of wrapper attributes
	 * @return  string
	 */
	static private function LiquidusTab($content, $attribs)
	{
		static $processing;
		isset($processing) OR $processing = array();

		// Get placeholder name for separating tab group.
		$name = isset($attribs['name']) ? (string) $attribs['name'] : NULL;

		if (empty($name))
		{
			throw new Exception('Placeholder name is required for rendering tabbed layout.');
		}

		// Get maximum number of tabs.
		if ( ! isset($attribs['maxTabs']))
		{
			throw new Exception('Number of tabs is required for rendering tabbed layout.');
		}

		// Check processing status.
		if ( ! isset($processing[$name]))
		{
			// Create an object to hold processing status.
			$processing[$name]           = new stdClass();
			$processing[$name]->max_tabs = intval($attribs['maxTabs']);
			$processing[$name]->rendered = 0;

			// Generate tab wrapper.
			$html = '<div class="tabber">'."\n";
		}
		else
		{
			$html = '';
		}

		if ($processing[$name]->rendered < $processing[$name]->max_tabs)
		{
			$html .= "\t".'<div class="'.str_replace('liquidus-tab', 'tabbertab liquidus-tab', $attribs['class']).'"';

			if (intval($attribs['showHeading']))
			{
				$html = $html.'>'."\n\t\t"
				      . '<h'.$attribs['headingLevel'].(strpos($attribs['headingLevel'], 'class="') === FALSE ? ' class="heading"' : '').'>'
				      . $content->title.'</h'.intval($attribs['headingLevel']).'>'."\n\t\t";
			}
			else
			{
				$html .= ' title="'.$content->title.'">'."\n\t\t";
			}

			$html .= '<div class="content">'."\n".$content->content."\n\t\t".'</div>'."\n\t".'</div>';

			// Update processing status.
			$processing[$name]->rendered++;

			if ($processing[$name]->rendered == $processing[$name]->max_tabs)
			{
				// Finalize tab wrapper.
				$html = $html."\n".'</div>'."\n"
				      . '<script type="text/javascript">tabberAutomatic(typeof tabberOptions != \'undefined\' ? tabberOptions : null);</script>';
			}
		}
		else
		{
			// Reached number of tabs limitation, render module in standard style.
			$html = self::LiquidusStandard($content, $attribs, $name);
		}

		return $html;
	}
}
?>