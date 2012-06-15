<?php defined('LIQUIDUS') or die('Direct access not permitted.');
/**
 * A class for serving appropriate website UI for detected client device.
 *
 * This class parse the given XML-based theme file, look for and render a
 * website UI that is declared for the auto-detected client device. If no any
 * suitable website UI found then the default UI declared in the theme file
 * will be rendered.
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

class LiquidusRenderer extends LiquidusTemplate {
	/**
	 * A LiquidusXML object.
	 *
	 * This variable contains all website layout declarations parsed from the
	 * given theme file.
	 *
	 * @var object
	 */
	public $theme;

	/**
	 * A LiquidusHook object.
	 *
	 * The LiquidusRenderer class provides following hook points for integration
	 * and/or customization purpose:
	 *
	 * pre-render               : hook point is triggered before rendering website UI
	 * post-render              : hook point is triggered after website UI is rendered
	 * pre-add-assets           : hook point is triggered before adding website UI assets
	 * post-add-assets          : hook point is triggered after website UI assets is added
	 * pre-render-node          : hook point is triggered before rendering a XML node
	 * post-render-node         : hook point is triggered after a XML node is rendered
	 * pre-render-placholders   : hook point is triggered before rendering all declared placeholders of a XML node
	 * post-render-placeholders : hook point is triggered after all declared placeholders of a XML node is rendered
	 * pre-render-placeholder   : hook point is triggered before rendering a placeholder
	 * post-render-placeholder  : hook point is triggered after a placeholder is rendered
	 * pre-render-generators    : hook point is triggered before rendering all declared generators of a placeholder
	 * post-render-generators   : hook point is triggered after all declared generators of a placeholder is rendered
	 *
	 * A hook can be registered using following methods:
	 *
	 * class myCustomizationClass {
	 *     static public function PreRender( & $renderer) {}
	 *     static public function PostRender( & $renderer) {}
	 *     static public function PreAddAssets( & $renderer) {}
	 *     static public function PostAddAssets( & $renderer) {}
	 *     static public function PreRenderNode( & $renderer, & $nodes, & $html, $tab_level, $selector) {}
	 *     static public function PostRenderNode( & $renderer, & $nodes, & $html, $tab_level, $selector) {}
	 *     static public function PreRenderPlaceholders( & $renderer, & $nodes, & $html) {}
	 *     static public function PostRenderPlaceholders( & $renderer, & $nodes, & $html) {}
	 *     static public function PreRenderPlaceholder( & $renderer, & $node, & $html) {}
	 *     static public function PostRenderPlaceholder( & $renderer, & $node, & $html) {}
	 *     static public function PreRenderGenerators( & $renderer, & $nodes, & $html, & $injection, $name) {}
	 *     static public function PostRenderGenerators( & $renderer, & $nodes, & $html, & $injection, $name) {}
	 * }
	 *
	 * $renderer = new LiquidusRenderer('my-theme');
	 *
	 * // Method #1
	 * $renderer->hook->register('myCustomizationClass');
	 *
	 * // Method #2
	 * $renderer->hook->add(
	 *     'pre-render',
	 *     array('myCustomizationClass', 'PreRender')
	 * );
	 * $renderer->hook->add(
	 *     'pre-render-placeholder',
	 *     array('myCustomizationClass', 'PreRenderPlaceholder')
	 * );
	 *
	 * $renderer->render();
	 *
	 * @var object
	 */
	public $hook;

	/**
	 * LiquidusRenderer singleton.
	 *
	 * @var object
	 */
	static protected $instance;

	/**
	 * Return a LiquidusRenderer singleton.
	 *
	 * @param   string  $theme  Theme name, only needed on first-time method calling
	 * @return  mixed   LiquidusRenderer object on success, NULL on failure
	 */
	static public function & getSingleton($theme = NULL)
	{
		// Instantiate a new LiquidusRenderer object only if not instantiated before.
		if ( ! isset(self::$instance))
		{
			self::$instance = NULL;

			try
			{
				self::$instance = new LiquidusRenderer($theme);
			}
			catch (Exception $e)
			{
				echo '<p class="exception-msg">'.$e->getMessage().'</p>';
			}
		}

		return self::$instance;
	}

	/**
	 * Set an object as LiquidusRenderer singleton.
	 *
	 * This method needs to be called to recreate a singleton from cached data.
	 *
	 * @param   object  $obj  A LiquidusRenderer object
	 * @return  void
	 */
	static public function setSingleton( & $obj)
	{
		if ($obj instanceof LiquidusRenderer)
		{
			self::$instance = & $obj;
		}
	}

	/**
	 * Construct a LiquidusRenderer object.
	 *
	 * @param   object  $theme  Theme name, e.g. default
	 * @return  void
	 */
	public function __construct($theme)
	{
		if ( ! empty($theme) AND is_readable($theme = Liquidus::getPath('themes').DS.$theme.DS.$theme.'.xml'))
		{
			// Parse the given theme.
			$this->theme = new LiquidusXml($theme);

			// Check if given theme has HTML code structure declared for requested website UI.
			$code = $this->theme->xpath('liquidus:ui[@for="'.Liquidus::getUI().'"]/liquidus:code');

			if ( ! $code OR ! count($code))
			{
				$code = $this->theme->xpath('liquidus:ui[@for="default"]/liquidus:code');

				if ($code AND count($code))
				{
					Liquidus::setUI('default');
				}
				else
				{
					if (Liquidus::getUI() != 'default')
					{
						throw new Exception(fText::compose(
							'The theme specified, <q>%1$s</q>, does not have neither <q>%2$s</q> nor <q>default</q> website UI declared.',
							$theme,
							Liquidus::getUI()
						));
					}
					else
					{
						throw new Exception(fText::compose(
							'The theme specified, <q>%s</q>, does not have <q>default</q> website UI declared.',
							$theme
						));
					}
				}
			}
		}
		else
		{
			if (empty($theme))
			{
				throw new Exception('Missing theme for rendering website UI.');
			}
			else
			{
				throw new Exception(fText::compose(
					'The theme specified, <q>%s</q>, does not exist under the <q>themes</q> directory.',
					$theme
				));
			}
		}

		// Initialize hooking support.
		$this->hook = new LiquidusHook();
	}

	/**
	 * Render requested website UI.
	 *
	 * @return  void
	 */
	public function render()
	{
		try
		{
			if ( ! isset($this->rendered) OR ! $this->rendered)
			{
				// Call pre-render hooks.
				$this->hook->call('pre-render', array( & $this));

				// Set templates.
				$this->set('header', Liquidus::getPath('templates').DS.Liquidus::getUI().DS.'header.php');
				$this->set('footer', Liquidus::getPath('templates').DS.Liquidus::getUI().DS.'footer.php');

				// Add stylesheet and script assets.
				$this->addAssets();

				// Render HTML code structure.
				$this->renderCode();

				// Call post-render hooks.
				$this->hook->call('post-render', array( & $this));

				// Unset LiquidusXml object as we do not need it any more.
				unset($this->theme);

				// Indicate that rendering is completed.
				$this->rendered = TRUE;
			}

			// Finalize output.
			$this->place('header');
			$this->place('body');
			$this->place('footer');
		}
		catch (Exception $e)
		{
			echo '<p class="exception-msg">'.$e->getMessage().'</p>';
		}
	}

	/**
	 * Render requested website UI's HTML code structure.
	 *
	 * @return  void
	 */
	protected function renderCode()
	{
		$html = array();

		// Get code structure declared for requested website UI.
		$code = array_shift($this->theme->xpath('liquidus:ui[@for="'.Liquidus::getUI().'"]/liquidus:code'));

		// Now, recursively render all declared code structure's XML nodes.
		foreach ($code->children() AS $node)
		{
			if (($node = $this->renderNode($node)) != NULL)
			{
				$html[] = $node;
			}
		}

		// Finalize HTML code.
		$html = implode("\n", $html);

		// Replace all inline injection code with real content.
		if (preg_match_all('/\{inject ([^\s^\}]+)\s?([^\}]+)?\}/', $html, $matches, PREG_SET_ORDER))
		{
			foreach ($matches AS $match)
			{
				$html = str_replace($match[0], call_user_func_array(array($match[1], 'process'), array( & $this, $match[2])), $html);
			}
		}

		$this->set('body', $html);
	}

	/**
	 * Render HTML code for a XML node.
	 *
	 * @param   object   $node             SimpleXMLElement object
	 * @param   integer  $tab_level        Number of tabs to prepend to the generated HTML
	 * @param   string   $selector_prefix  CSS selector prefix for setting inline styles
	 * @return  string
	 */
	protected function renderNode($node, $tab_level = 1, $selector_prefix = NULL)
	{
		$html   = NULL;
		$nested = array();

		// Generate CSS selector.
		$selector = $this->generateSelector($node['class'], $node['id'], $selector_prefix);

		// Call pre-render-node hooks.
		$this->hook->call('pre-render-node', array( & $this, & $node, & $html, $tab_level, $selector));

		if ($node->count() > 0)
		{
			// Recursively render nested nodes.
			foreach ($node->children() AS $child)
			{
				// Get Liquidus proprietary attributes.
				$attributes = $child->attributes('liquidus', TRUE);

				// Is this a colapsible/expansible column?
				if ($attributes['collapsible'] OR $attributes['expansible'])
				{
					isset($max_cols) OR $max_cols = 0;
					isset($num_cols) OR $num_cols = 0;
					isset($hidden)   OR $hidden   = array();

					// Create a standard object for storing column data.
					$column = new stdClass();

					// Store SimpleXMLElement object.
					$column->xml = $child;

					// Store Liquidus proprietary attributes.
					$column->attr = $attributes;

					// Indicate that enclosing markup tag generation does not needed.
					$child['skipMarkupTagGeneration'] = TRUE;

					// Render HTML code.
					$column->html = trim($this->renderNode($child, $tab_level + 1, $selector));

					// Update column count.
					$max_cols++;

					if ($column->html != NULL)
					{
						$num_cols++;
					}
					else
					{
						$hidden[] = $child['id'];
					}

					$nested[] = $column;
				}
				elseif (($child = $this->renderNode($child, $tab_level + 1, $selector)) != NULL)
				{
					$nested[] = $child;
				}
			}

			// Deal with colapsible/expansible columns.
			if (isset($max_cols))
			{
				foreach ($nested AS $k => $v)
				{
					if (is_object($v))
					{
						// Collapse column if necessary.
						if ($v->html == NULL AND $v->attr['collapsible'])
						{
							unset($nested[$k]);
						}
						else
						{
							// Clean unnecessary attributes.
							unset($v->xml['skipMarkupTagGeneration']);

							// Finalize rendered HTML code.
							$nested[$k] = $this->renderColumn($node, $v, $max_cols, $num_cols, $hidden, $tab_level + 1, $selector);
						}
					}
				}
			}

			// Combine rendered HTML code.
			count($nested) AND $html = implode("\n", $nested);
		}
		elseif (($children = $node->xpath('liquidus:placeholder')) AND count($children))
		{
			// Inject content for placeholders.
			$html = $this->renderPlaceholders($children);
		}
		else
		{
			// Inject hard-coded content.
			$html = $node->asXML();

			// Convert XML markup tag to HTML if necessary.
			if (substr($html, -2) == '/>' AND ! in_array($node->getName(), array('br', 'hr')))
			{
				$html = str_replace('/>', '></'.$node->getName().'>', $html);
			}

			// Indicate that enclosing markup tag generation does not needed.
			$node['skipMarkupTagGeneration'] = TRUE;
		}

		if ( ! empty($html))
		{
			$attributes = $node->attributes('liquidus', TRUE);

			// Process behavior if necessary.
			if ($attributes['behavior'])
			{
				// Generate class name.
				$c = 'LiquidusRendererBehavior'.preg_replace('/\-([a-z])/e', 'strtoupper(\'$1\')', ucfirst($attributes['behavior']));

				if ( ! class_exists($c, FALSE))
				{
					// Generate class declaration file name.
					$f = Liquidus::getPath().DS.'libraries'.DS.'liquidus'.DS.'renderer'.DS.'behavior'.DS.$attributes['behavior'].'.php';

					if (is_readable($f))
					{
						require $f;
					}
				}

				if (class_exists($c, FALSE))
				{
					$html = call_user_func_array(array($c, 'process'), array( & $this, $node, $attributes, $html));
				}
			}

			// Store available place for skip-to links generation.
			if ($attributes['place'])
			{
				isset($node['id']) OR $node->addAttribute('id', $attributes['place']);

				// Update available places.
				$places = $this->get('places', array());
				$places[(string) $attributes['place']] = (string) $node['id'];

				$this->set('places', $places);
			}

			// Finalize HTML code.
			$html = $this->renderMarkup($node, $html, $tab_level, $selector);
		}

		// Call post-render-node hooks.
		$this->hook->call('post-render-node', array( & $this, & $node, & $html, $tab_level, $selector));

		return $html;
	}

	/**
	 * Render HTML code for a collapsible/expansible column.
	 *
	 * @param   object   $node             SimpleXMLElement object
	 * @param   object   $column           Standard object
	 * @param   integer  $max              Number of declared column
	 * @param   integer  $num              Number of visible columns
	 * @param   array    $hidden           Array of invisible column ID
	 * @param   integer  $tab_level        Number of tabs to prepend to the generated HTML
	 * @param   string   $selector_prefix  CSS selector prefix for setting inline styles
	 * @return  string
	 */
	protected function renderColumn($node, $column, $max, $num, $hidden, $tab_level, $selector_prefix)
	{
		if ($max != $num AND $column->attr['expansible'])
		{
			// Get alteration conditions.
			$conditions = $node->xpath('liquidus:expansible');

			// Loop thru alteration conditions, in reverse order, to find the first match.
			foreach (array_reverse($conditions) AS $condition)
			{
				// Continue checking if 'collapsed' not match.
				if ($condition['collapsed'])
				{
					$match = TRUE;
					$count = 0;

					foreach (explode(',', $condition['collapsed']) AS $col)
					{
						if ( ! in_array(trim($col), $hidden))
						{
							$match = FALSE;
							break;
						}

						// Count number of 'collapsed' condition declared.
						$count++;
					}

					if ( ! $match)
						continue;
				}

				// Continue checking if 'column-count' not match.
				if ($condition['collapsed'] AND ! $condition['column-count'] AND ($max - $count) != $num)
					continue;

				if ($condition['column-count'] AND intval($condition['column-count']) != $num)
					continue;

				// Match found, get alterations.
				$alters    = $condition->xpath('liquidus:alter');
				$column_id = (string) $column->xml['id'];

				// Loop thru alterations, in reverse order, to find the first match.
				foreach (array_reverse($alters) AS $alter)
				{
					$alter_for = (string) $alter['for'];

					// Continue if alteration rule not match column ID.
					if ( ! in_array($alter_for, array($column_id, '*')))
						continue;

					// Match found, merge alteration to column attributes.
					foreach ($alter->attributes() AS $k => $v)
					{
						// Skip setting unnecessary attributes.
						if ($k == 'for')
							continue;

						if (isset($column->xml[$k]))
						{
							$column->xml[$k] = $v;
						}
						else
						{
							$column->xml->addAttribute($k, $v);
						}
					}

					break;
				}

				break;
			}
		}

		// Generate CSS selector.
		$selector = $this->generateSelector($column->xml['class'], $column->xml['id'], $selector_prefix);

		return $this->renderMarkup($column->xml, $column->html, $tab_level, $selector);
	}

	/**
	 * Render HTML markup tag from a XML node.
	 *
	 * @param   object   $node       SimpleXMLElement object
	 * @param   string   $html       Nested HTML code
	 * @param   integer  $tab_level  Number of tabs to prepend to the generated HTML
	 * @param   string   $selector   CSS selector for setting inline styles
	 * @return  string
	 */
	protected function renderMarkup($node, $html, $tab_level, $selector)
	{
		// Generate source code alignment space.
		$tabs = '';
		for ($i = 0; $i < $tab_level; $i++)
		{
			$tabs .= "\t";
		}

		if ( ! $node['skipMarkupTagGeneration'])
		{
			// Generate markup tag attributes.
			$attributes = array();

			foreach ($node->attributes() AS $k => $v)
			{
				$v = trim($v);

				// Continue if attribute value is empty.
				if (empty($v))
					continue;

				if ($k == 'style' AND ! empty($selector))
				{
					$this->add('inline-css', $selector.' { '.trim($v).' }');
				}
				else
				{
					$attributes[] = $k.'="'.trim($v).'"';
				}
			}

			$html = $tabs.'<'.$node->getName().(count($attributes) ? ' '.implode(' ', $attributes) : '').'>'."\n"
			      . $html
			      . "\n".$tabs.'</'.$node->getName().'>'.(($node->count() < 1 AND $node['id']) ? '<!-- end #'.$node['id'].' -->' : '');
		}
		else
		{
			$html = $tabs.$html;
		}

		return $html;
	}

	/**
	 * Render HTML code for all declared placeholders of a XML node.
	 *
	 * @param   array   $nodes  Array of SimpleXMLElement object
	 * @return  string
	 */
	protected function renderPlaceholders($nodes)
	{
		$html = array();

		// Call pre-render-placeholders hooks.
		$this->hook->call('pre-render-placeholders', array( & $this, & $nodes, & $html));

		foreach (array_keys($nodes) AS $k)
		{
			$node = & $nodes[$k];

			// Render HTML code for placeholder.
			$code = $this->renderPlaceholder($node);

			// Render auto-generators if necessary.
			if (($children = $node->xpath('liquidus:generator')) AND count($children))
			{
				$code = $this->renderGenerators( (string) $node['name'], $children, $code);
			}

			$html[] = $code;
		}

		// Call post-render-placeholders hooks.
		$this->hook->call('post-render-placeholders', array( & $this, & $nodes, & $html));

		// Combine rendered HTML code.
		count($html) AND $html = implode("\n", $html);

		return $html;
	}

	/**
	 * Render HTML code for a placeholder.
	 *
	 * @param   object  $node  SimpleXMLElement object
	 * @return  string
	 */
	protected function renderPlaceholder( & $node)
	{
		$html = NULL;

		// Camelize wrapper name if necessary.
		strpos($node['wrapper'], '-') === FALSE OR $node['wrapper'] = preg_replace('/\-([a-z])/e', 'strtoupper(\'$1\')', ucfirst($node['wrapper']));

		// Call pre-render-placeholder hooks.
		$this->hook->call('pre-render-placeholder', array( & $this, & $node, & $html));

		// Prepare attributes.
		$name    = (string) $node['name'];
		$wrapper = (string) $node['wrapper'];

		if ($name AND ($html = $this->get($name, $html)) != NULL)
		{
			is_array($html) OR $html = array($html);

			// Set additional attributes if necessary.
			$wrapper == 'LiquidusTab' AND ! $node['maxTabs'] AND $node->addAttribute('maxTabs', count($html));

			// Wrap content inside prefered wrapper.
			foreach ($html AS $k => $v)
			{
				try
				{
					if (is_string($v))
					{
						ob_start();
						$this->place($v);
						$v = ob_get_clean();
					}

					$html[$k] = LiquidusRendererWrapper::wrap($v, $node);
				}
				catch (Exception $e)
				{
					echo '<p class="exception-msg">'.$e->getMessage().'</p>';
				}
			}

			// Load :hover fail-over Javascript code snippet.
			if ($wrapper == 'LiquidusHover' AND ! defined('LIQUIDUS_HOVER_FAIL_OVER'))
			{
				define('LIQUIDUS_HOVER_FAIL_OVER', 1);

				$this->add('dom-ready', '(function() {
			for (var t in {ul: \'\', dl: \'\'}) {
				var lists = document.getElementsByTagName(t);
				for (var i = 0; i < lists.length; i++) {
					if (lists[i].className.match(/\bhover-menu\b/)) {
						lists[i].onmouseover = function() { this.className.indexOf(\' mouseover\') > -1 || (this.className += \' mouseover\'); };
						lists[i].onmouseout = function(evt) { if (typeof Liquidus != \'undefined\' && !Liquidus.mouseReallyOut(this, evt)) return; this.className = this.className.replace(\' mouseover\', \'\'); };
					}
				}
			}
		})();');
			}

			// Load tabbed layout assets.
			if ($wrapper == 'LiquidusTab' AND ! defined('LIQUIDUS_TAB_ASSETS'))
			{
				define('LIQUIDUS_TAB_ASSETS', 1);

				$this->addAsset('css', 'plugins.tabber');
				$this->addAsset('js', 'libraries.tabber.tabber-minimized');
			}
		}

		// Call post-render-placeholder hooks.
		$this->hook->call('post-render-placeholder', array( & $this, & $node, & $html));

		// Finalize content for injection.
		is_array($html) AND $html = implode("\n", $html);

		return $html;
	}

	/**
	 * Create inclusion syntax for injecting auto-generator content.
	 *
	 * @param   string  $name   Placeholder name
	 * @param   array   $nodes  Array of SimpleXMLElement object
	 * @param   string  $html   Rendered HTML code
	 * @return  string
	 */
	protected function renderGenerators($name, $nodes, $html)
	{
		$injection   = array();

		// Call pre-render-generators hooks.
		$this->hook->call('pre-render-generators', array( & $this, & $nodes, & $html, & $injection, $name));

		foreach ($nodes AS $node)
		{
			$evaluate              = ($node['condition'] == 'if-empty'     AND empty($html));
			$evaluate OR $evaluate = ($node['condition'] == 'if-not-empty' AND ! empty($html));

			if ($evaluate)
			{
				$evaluate = NULL;

				if ( ! $node['content'] AND ! empty($node))
				{
					// Evaluate hard-coded content.
					$evaluate = $node;
				}
				elseif ($node['content'])
				{
					// Generate content generator class name.
					$c = 'LiquidusRendererGenerator'.preg_replace('/\-([a-z])/e', 'strtoupper(\'$1\')', ucfirst($node['content']));

					if ( ! class_exists($c, FALSE))
					{
						// Generate class declaration file name.
						$f = Liquidus::getPath().DS.'libraries'.DS.'liquidus'.DS.'renderer'.DS.'generator'.DS.$node['content'].'.php';

						if (is_readable($f))
						{
							require $f;
						}
					}

					// Evaluate only if content generator class exists.
					if (class_exists($c, FALSE))
					{
						unset($args);

						// Create query string from XML node attributes.
						foreach ($node->attributes() AS $k => $v)
						{
							if (in_array($k, array('type', 'method', 'content')))
								continue;

							$args[] = $k.'='.rawurlencode($v);
						}

						$evaluate = '{inject '.$c.(isset($args) ? ' '.implode('&', $args) : '').'}';
					}
				}

				if (isset($evaluate))
				{
					// Append evaluated content by default.
					switch ($node['method'])
					{
						case 'prepend':
							$injection['prepend'][] = $evaluate;
						break;

						case 'append':
						default:
							$injection['append'][] = $evaluate;
						break;
					}
				}
			}
		}

		// Call post-render-generators hooks.
		$this->hook->call('post-render-generators', array( & $this, & $nodes, & $html, & $injection, $name));

		// Finalize HTML code.
		isset($injection['prepend']) AND $html = implode("\n", $injection['prepend']).$html;
		isset($injection['append'])  AND $html = $html.implode("\n", $injection['append']);

		return $html;
	}

	/**
	 * Add requested website UI's declared assets.
	 *
	 * @return  void
	 */
	protected function addAssets()
	{
		// Call pre-add-assets hooks.
		$this->hook->call('pre-add-assets', array( & $this));

		foreach (array('js', 'css') AS $type)
		{
			// Get declared assets.
			$assets = $this->theme->xpath('liquidus:ui[@for="'.Liquidus::getUI().'"]/liquidus:assets/liquidus:'.$type.'/liquidus:file');

			// Add declared assets for placing later.
			if ($assets AND count($assets))
			{
				foreach ($assets AS $asset)
				{
					// Get asset file name/path.
					$attrs = $asset->attributes('liquidus', TRUE);
					$path  = $attrs['link'] ? trim($attrs['link']) : trim($attrs['name']);

					// Add asset to registry.
					$this->addAsset($type, $path, $asset->attributes());
				}
			}
		}

		// Call post-add-assets hooks.
		$this->hook->call('post-add-assets', array( & $this));
	}

	/**
	 * Generate CSS selector for setting inline styles.
	 *
	 * @param   string  $classes  Classes separated by white-space character
	 * @param   string  $id       Element ID
	 * @param   string  $prefix   Selector prefix
	 * @return  string
	 */
	protected function generateSelector($classes, $id = NULL, $prefix = NULL)
	{
		$selector = '';

		if (($prefix = trim($prefix)) != NULL)
		{
			$selector = $prefix.' ';
		}

		if ( ! empty($id))
		{
			$selector .= '#'.$id;
		}

		if ( ! empty($classes))
		{
			$selector .= '.'.preg_replace('/\s+/', '.', $classes);
		}

		return $selector;
	}
}
?>