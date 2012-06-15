<?php defined('LIQUIDUS') or die('Direct access not permitted.');
/**
 * A class for integrating Liquidus web template system with Joomla.
 *
 * Usage:
 *
 * $renderer = new LiquidusRenderer('my-theme');
 * $renderer->hook->register('LiquidusRendererHookJoomla');
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

class LiquidusRendererHookJoomla {
	/**
	 * Parsed content injection parameters.
	 *
	 * @var array
	 */
	static private $injection;

	/**
	 * A method to be executed when the hook point 'pre-render' is triggered.
	 *
	 * @param   object  $renderer  LiquidusRenderer object
	 * @return  void
	 */
	static public function PreRender( & $renderer) {}

	/**
	 * A method to be executed when the hook point 'post-render' is triggered.
	 *
	 * @param   object  $renderer  LiquidusRenderer object
	 * @return  void
	 */
	static public function PostRender( & $renderer) {
		if (class_exists('JFactory', FALSE))
		{
			$document = & JFactory::getDocument();

			// Migrate meta-data to Joomla.
			if ($meta = $renderer->get('meta', NULL, TRUE))
			{
				foreach ($meta AS $tag)
				{
					if (is_string($tag))
					{
						$document->_custom[] = trim($tag);
					}
					elseif (is_array($tag))
					{
						if (isset($tag['http-equiv']))
						{
							$document->_metaTags['http-equiv'][$tag['http-equiv']] = $tag['content'];
						}
						else
						{
							$document->_metaTags['standard'][$tag['name']] = $tag['content'];
						}
					}
				}
			}

			// Migrate stylesheet links to Joomla.
			if ($css = $renderer->get('css', NULL, TRUE))
			{
				foreach ($css AS $f)
				{
					if (is_string($f))
					{
						$document->_styleSheets[Liquidus::getWebPath($f)] = array(
							'mime'  => 'text/css',
							'media' => 'all'
						);
					}
					elseif (is_array($f) AND isset($f['href']))
					{
						$f['mime'] = $f['type'];
						$document->_styleSheets[$f['href']] = $f;
					}
				}
			}

			// Migrate script links to Joomla.
			if ($js = $renderer->get('js', NULL, TRUE))
			{
				foreach ($js AS $f)
				{
					if ($fname = is_string($f) ? $f : ((is_array($f) AND isset($f['src'])) ? $f['src'] : NULL))
					{
						$fname = substr($fname, -16);

						if ($fname == 'mootools-core.js' OR $fname == 'mootools-more.js')
						{
							JHTML::_('behavior.framework', $fname == 'mootools-more.js' ? TRUE : FALSE);
							continue;
						}
					}

					if (is_string($f))
					{
						$document->_scripts[Liquidus::getWebPath($f)] = 'text/javascript';
					}
					elseif (is_array($f) AND isset($f['src']))
					{
						$document->_scripts[$f['src']] = 'text/javascript';
					}
				}
			}

			// Migrate inline style declaration to Joomla.
			if ($css = $renderer->get('inline-css', NULL, TRUE))
			{
				$document->_style['text/css'] .= (isset($document->_style['text/css']) ? "\n" : '').implode("\n", $css);
			}

			// Migrate inline script declaration to Joomla.
			if ($js = $renderer->get('inline-js', NULL, TRUE))
			{
				$document->_script['text/javascript'] .= (isset($document->_script['text/javascript']) ? "\n" : '').implode("\n", $js);
			}

			// Alter header template.
			$header = $renderer->get('header');
			$header = is_readable($header) ? file_get_contents($header) : $header;

			list($header, $remaining) = explode('<head>', $header);
			list($remove, $remaining) = explode('</head>', $remaining);

			$header .= '<head>'."\n".'<jdoc:include type="head" /></head>'.$remaining;

			$renderer->set('header', $header);
		}
	}

	/**
	 * A method to be executed when the hook point 'pre-add-assets' is triggered.
	 *
	 * @param   object  $renderer  LiquidusRenderer object
	 * @return  void
	 */
	static public function PreAddAssets( & $renderer) {}

	/**
	 * A method to be executed when the hook point 'post-add-assets' is triggered.
	 *
	 * @param   object  $renderer  LiquidusRenderer object
	 * @return  void
	 */
	static public function PostAddAssets( & $renderer) {}

	/**
	 * A method to be executed when the hook point 'pre-render-node' is triggered.
	 *
	 * @param   object   $renderer   LiquidusRenderer object
	 * @param   object   $node       SimpleXMLElement object
	 * @param   string   $html       Rendered HTML code
	 * @param   integer  $tab_level  Number of tabs to prepend to the generated HTML
	 * @param   string   $selector   CSS selector for setting inline styles
	 * @return  void
	 */
	static public function PreRenderNode( & $renderer, & $node, & $html, $tab_level, $selector) {}

	/**
	 * A method to be executed when the hook point 'post-render-node' is triggered.
	 *
	 * @param   object   $renderer   LiquidusRenderer object
	 * @param   object   $node       SimpleXMLElement object
	 * @param   string   $html       Rendered HTML code
	 * @param   integer  $tab_level  Number of tabs to prepend to the generated HTML
	 * @param   string   $selector   CSS selector for setting inline styles
	 * @return  void
	 */
	static public function PostRenderNode( & $renderer, & $node, & $html, $tab_level, $selector) {}

	/**
	 * A method to be executed when the hook point 'pre-render-placeholders' is triggered.
	 *
	 * @param   object  $renderer  LiquidusRenderer object
	 * @param   object  $nodes     Array of SimpleXMLElement object
	 * @param   array   $html      Array of rendered HTML code
	 * @return  void
	 */
	static public function PreRenderPlaceholders( & $renderer, & $nodes, & $html) {}

	/**
	 * A method to be executed when the hook point 'post-render-placeholders' is triggered.
	 *
	 * @param   object  $renderer  LiquidusRenderer object
	 * @param   object  $nodes     Array of SimpleXMLElement object
	 * @param   array   $html      Array of rendered HTML code
	 * @return  void
	 */
	static public function PostRenderPlaceholders( & $renderer, & $nodes, & $html) {}

	/**
	 * A method to be executed when the hook point 'pre-render-placeholder' is triggered.
	 *
	 * @param   object  $renderer  LiquidusRenderer object
	 * @param   object  $node      SimpleXMLElement object
	 * @param   string  $html      Rendered HTML code
	 * @return  void
	 */
	static public function PreRenderPlaceholder( & $renderer, & $node, & $html) {
		if (class_exists('JFactory', FALSE))
		{
			$hasInjectionParams = self::parseInjectionParams();

			$document = & JFactory::getDocument();
			$name     = (string) $node['name'];
			$chrome   = (isset(self::$injection['tabbed-layout']) AND in_array($name, self::$injection['tabbed-layout']['p']))
			          ? 'LiquidusTab'
			          : (string) $node['wrapper'];

			if ($document->countModules($name))
			{
				// Generate Joomla modules inclusion code.
				$code = '<jdoc:include type="modules" name="'.$name.'" style="'.(empty($chrome) ? 'LiquidusRaw' : $chrome).'" />';

				// Set content for renderer.
				$renderer->set($name, $code);

				// Remove unnecessary attributes.
				unset($node['wrapper']);
			}
			elseif ($chrome == 'LiquidusTab')
			{
				// Switch wrapper to tabbed layout.
				isset($node['wrapper']) ? ($node['wrapper'] = $chrome) : $node->addAttribute('wrapper', $chrome);
			}

			// Create auto-generator node if necessary.
			if ($hasInjectionParams)
			{
				foreach (self::$injection AS $content => $params)
				{
					if (in_array($name, $params['p']))
					{
						if ( ! ($exists = $node->xpath('liquidus:generator[@content="'.$content.'"]')) OR count($exists) == 0)
						{
							unset($generator);

							if ($content == 'system-message' AND $document->getBuffer('message'))
							{
								// Load system message assets.
								$renderer->addAsset('css', 'plugins.message');

								$generator = $node->addChild(
									'liquidus:generator',
									'<div id="message-box" class="clearfix">'."\n".
										'<jdoc:include type="message" />'."\n".
									'</div><!-- end #message-box -->'
								);
							}
							elseif ($content == 'component-output')
							{
								$generator = $node->addChild(
									'liquidus:generator',
									'<div id="component-output" class="clearfix">'."\n".
										'<jdoc:include type="component" />'."\n".
									'</div><!-- end #component-output -->'
								);
							}
							elseif ($content != 'tabbed-layout')
							{
								$generator = $node->addChild('liquidus:generator');
								$generator->addAttribute('content', $content);
							}

							isset($generator) AND $generator->addAttribute('method', $params['m']);
						}
					}
				}
			}
		}
	}

	/**
	 * A method to be executed when the hook point 'post-render-placeholder' is triggered.
	 *
	 * @param   object  $renderer  LiquidusRenderer object
	 * @param   object  $node      SimpleXMLElement object
	 * @param   array   $html      Array of rendered HTML code
	 * @return  void
	 */
	static public function PostRenderPlaceholder( & $renderer, & $node, & $html) {}

	/**
	 * A method to be executed when the hook point 'pre-render-generators' is triggered.
	 *
	 * @param   object  $renderer   LiquidusRenderer object
	 * @param   object  $nodes      Array of SimpleXMLElement object
	 * @param   string  $html       Rendered HTML code
	 * @param   array   $injection  Array of generated injection code.
	 * @param   string  $name       Placeholder name
	 * @return  void
	 */
	static public function PreRenderGenerators( & $renderer, & $nodes, & $html, & $injection, $name) {
		if (self::parseInjectionParams())
		{
			foreach (array_keys($nodes) AS $k)
			{
				$node    = & $nodes[$k];
				$content = (string) $node['content'];
				$method  = (string) $node['method'];

				if (isset(self::$injection[$content]))
				{
					if (in_array($name, self::$injection[$content]['p']) AND self::$injection[$content]['m'] != $method)
					{
						isset($node['method']) ? $node['method'] = self::$injection[$content]['m']
						                       : $node->addAttribute('method', self::$injection[$content]['m']);
					}
					elseif ( ! in_array($name, self::$injection[$content]['p']))
					{
						unset($nodes[$k]);
					}
				}
			}
		}
	}

	/**
	 * A method to be executed when the hook point 'post-render-generators' is triggered.
	 *
	 * @param   object  $renderer   LiquidusRenderer object
	 * @param   object  $nodes      Array of SimpleXMLElement object
	 * @param   string  $html       Rendered HTML code
	 * @param   array   $injection  Array of generated injection code.
	 * @param   string  $name       Placeholder name
	 * @return  void
	 */
	static public function PostRenderGenerators( & $renderer, & $nodes, & $html, & $injection, $name) {}

	/**
	 * Parse content injection parameters.
	 *
	 * @return  boolean
	 */
	static private function parseInjectionParams() {
		if (class_exists('JFactory', FALSE) AND ! isset(self::$injection))
		{
			$document = & JFactory::getDocument();
			$params   = & $document->params;

			foreach (
				array('system-message', 'component-output', 'site-logo', 'ui-switcher', 'font-resizer', 'skipto-links', 'tabbed-layout')
				AS
				$content
			)
			{
				foreach (explode('|', $params->get($content)) AS $ui)
				{
					list($ui, $positions, $method) = explode(':', $ui, 3);

					if ($ui == Liquidus::getUI())
					{
						self::$injection[$content]['p'] = explode(',', $positions);
						self::$injection[$content]['m'] = $method;
					}
				}
			}
		}

		return isset(self::$injection) ? TRUE : FALSE;
	}

	/**
	 * Get appropriated template override file for the requested website UI.
	 *
	 * @param   string  $overridePath  Template override file path
	 * @return  string
	 */
	public static function getTemplateOverride($overridePath)
	{
		static $directory;

		$document = & JFactory::getDocument();
		$params   = & $document->params;
		$template = basename($overridePath);

		if ( ! isset($directory) OR ! isset($directory[$overridePath]))
		{
			// Get extension info from override path.
			if (preg_match('#/com_community/[a-z0-9\-\._]+\.php$#i', str_replace(DS, '/', $overridePath), $match))
			{
				// JomSocial use its own template override directory structure.
				$defaultPath = dirname(dirname(Liquidus::getPath())).DS.'components'.DS.'com_community'.DS.'templates'.DS.'default';
			}
			elseif (preg_match('#/(com_[a-z0-9\-_]+)/([a-z0-9\-_]+)/([a-z0-9\-_]+\.php)$#i', str_replace(DS, '/', $overridePath), $match))
			{
				// Standard template override directory structure for Joomla! component.
				$defaultPath = dirname(dirname(Liquidus::getPath())).DS.'components'.DS.$match[1].DS.'views'.DS.$match[2].DS.'tmpl';
			}
			elseif (preg_match('#/(mod_[a-z0-9\-_]+)/([a-z0-9\-_]+\.php)$#i', str_replace(DS, '/', $overridePath), $match))
			{
				// Standard template override directory structure for Joomla! module.
				$defaultPath = dirname(dirname(Liquidus::getPath())).DS.'modules'.DS.$match[1].DS.'tmpl';
			}

			// Setup template override directories stack.
			$directory[$overridePath] = array(
				// First, the extension default template path.
				$defaultPath,

				// Second, the template override path for default website UI of the default theme.
				str_replace(
					DS.'html'.DS,
					DS.'themes'.DS.'default'.DS.'html'.DS.'default'.DS,
					dirname($overridePath)
				)
			);

			if (Liquidus::getUI() != 'default')
			{
				// Third, the requested website UI of the default theme.
				$directory[$overridePath][] = str_replace(
					DS.'html'.DS,
					DS.'themes'.DS.'default'.DS.'html'.DS.Liquidus::getUI().DS,
					dirname($overridePath)
				);
			}

			if ($params->get('theme') != 'default')
			{
				// Then, the template override path for default website UI of the current theme.
				$directory[$overridePath][] = str_replace(
					DS.'html'.DS,
					DS.'themes'.DS.$params->get('theme').DS.'html'.DS.'default'.DS,
					dirname($overridePath)
				);

				// Finally, the requested website UI of the current theme.
				if (Liquidus::getUI() != 'default')
				{
					$directory[$overridePath][] = str_replace(
						DS.'html'.DS,
						DS.'themes'.DS.$params->get('theme').DS.'html'.DS.Liquidus::getUI().DS,
						dirname($overridePath)
					);
				}
			}
		}

		// Now, find template override file from the directories stack.
		foreach (array_reverse($directory[$overridePath]) AS $d)
		{
			if (is_readable($d.DS.$template))
				return $d.DS.$template;
		}

		return NULL;
	}
}
?>