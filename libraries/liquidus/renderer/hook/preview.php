<?php defined('LIQUIDUS') or die('Direct access not permitted.');
/**
 * A class for rendering preview for a Liquidus theme.
 *
 * Usage:
 *
 * $renderer = new LiquidusRenderer('my-theme');
 * $renderer->hook->register('LiquidusRendererHookPreview');
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

class LiquidusRendererHookPreview {
	/**
	 * A method to be executed when the hook point 'pre-render' is triggered.
	 *
	 * @param   object  $renderer  LiquidusRenderer object
	 * @return  void
	 */
	static public function PreRender( & $renderer) {
		$renderer->add(
			'inline-css',
			'.liquidus-placeholder { position: static; border: 0; margin: 0 }'."\n".
			'.liquidus-placeholder:hover, .liquidus-placeholder.mouseover { position: relative; border: 1px solid red; margin: -1px }'."\n".
			'.liquidus-placeholder .liquidus-placeholder-name { display: none }'."\n".
			'.liquidus-placeholder:hover .liquidus-placeholder-name, .liquidus-placeholder.mouseover .liquidus-placeholder-name { display: block; position: absolute; top: 0; background: red; color: white }'
		);
	}

	/**
	 * A method to be executed when the hook point 'post-render' is triggered.
	 *
	 * @param   object  $renderer  LiquidusRenderer object
	 * @return  void
	 */
	static public function PostRender( & $renderer) {}

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
	static public function PostRenderPlaceholders( & $renderer, & $nodes, & $html) {
		$keys = array_keys($nodes);

		for ($i = 0, $n = count($keys); $i < $n; $i++)
		{
			$html[$i] = '<div class="liquidus-placeholder clearfix">'."\n"
			          . $html[$i]."\n"
			          . '<span class="liquidus-placeholder-name">'.$nodes[$keys[$i]]['name'].'</span>'."\n"
			          . '</div>';
		}
	}

	/**
	 * A method to be executed when the hook point 'pre-render-placeholder' is triggered.
	 *
	 * @param   object  $renderer  LiquidusRenderer object
	 * @param   object  $node      SimpleXMLElement object
	 * @param   string  $html      Rendered HTML code
	 * @return  void
	 */
	static public function PreRenderPlaceholder( & $renderer, & $node, & $html) {
		$name = (string) $node['name'];

		if ($renderer->get($name) != NULL)
			return;

		// Add sample content if neccessary.
		if ( ! ($children = $node->xpath('liquidus:generator[@condition="if-empty"]')) OR count($children) <= 0)
		{
			if (is_readable(($sample = dirname($renderer->theme->file).DS.'preview'.DS.$name.'.php')))
			{
				ob_start();
				require $sample;
				if (($sample = ob_get_clean()) != NULL)
				{
					$renderer->set($name, $sample);
				}
			}
			else
			{
				$renderer->set($name, fText::compose('This is a placeholder for content associated with <strong>%s</strong> key name', $name));
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
	static public function PreRenderGenerators( & $renderer, & $nodes, & $html, & $injection, $name) {}

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
}
?>