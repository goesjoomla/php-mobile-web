<?php defined('_JEXEC') or die;

/**
 * Output module content as is.
 *
 * If module class suffix set, wrap module content inside a div with class set
 * to the module class suffix.
 *
 * @param   object  rendered module content
 * @param   object  module parameters
 * @param   array   attributes for rendering
 * @return  void
 */
function modChrome_LiquidusRaw($module, &$params, &$attribs)
{
	try
	{
		// Always hide module title.
		$attribs['showHeading'] = 0;

		// Set other parameters to atrributes.
		$attribs['wrapper']     = 'LiquidusRaw';
		$attribs['classSuffix'] = $params->get('moduleclass_sfx');

		echo LiquidusRendererWrapper::wrap($module, $attribs);
	}
	catch (Exception $e)
	{
		echo '<p class="exception-msg">'.$e->getMessage().'</p>';
	}
}

/**
 * Output module content in standard style.
 *
 * @param   object  rendered module content
 * @param   object  module parameters
 * @param   array   attributes for rendering
 * @return  void
 */
function modChrome_LiquidusStandard($module, &$params, &$attribs)
{
	try
	{
		// Set parameters to atrributes.
		$attribs['wrapper']     = 'LiquidusStandard';
		$attribs['classSuffix'] = $params->get('moduleclass_sfx');
		$attribs['showHeading'] = isset($attribs['showHeading']) ? $attribs['showHeading'] : $module->showtitle;

		echo LiquidusRendererWrapper::wrap($module, $attribs);
	}
	catch (Exception $e)
	{
		echo '<p class="exception-msg">'.$e->getMessage().'</p>';
	}
}

/**
 * Output module content inline.
 *
 * @param   object  rendered module content
 * @param   object  module parameters
 * @param   array   attributes for rendering
 * @return  void
 */
function modChrome_LiquidusInline($module, &$params, &$attribs)
{
	try
	{
		// Set parameters to atrributes.
		$attribs['wrapper']     = 'LiquidusInline';
		$attribs['classSuffix'] = $params->get('moduleclass_sfx');
		$attribs['showHeading'] = isset($attribs['showHeading']) ? $attribs['showHeading'] : $module->showtitle;

		echo LiquidusRendererWrapper::wrap($module, $attribs);
	}
	catch (Exception $e)
	{
		echo '<p class="exception-msg">'.$e->getMessage().'</p>';
	}
}

/**
 * Output module content in collapsed state.
 *
 * Module content is expansible when mouse-over.
 *
 * @param   object  rendered module content
 * @param   object  module parameters
 * @param   array   attributes for rendering
 * @return  void
 */
function modChrome_LiquidusHover($module, &$params, &$attribs)
{
	try
	{
		// Always show module title.
		$attribs['showHeading'] = 1;

		// Set parameters to atrributes.
		$attribs['wrapper']     = 'LiquidusHover';
		$attribs['classSuffix'] = $params->get('moduleclass_sfx');

		// Fine-tune Login module if user is logged in.
		if ($module->module == 'mod_login' AND ($user = JFactory::getUser()) AND ! $user->get('guest'))
		{
			$regex = '#<(p|div) class="login-greeting">[\r\n][\s\t]+('
			       . JText::sprintf('MOD_LOGIN_HINAME', $user->get('name'))
			       . '|'
			       . JText::sprintf('MOD_LOGIN_HINAME', $user->get('username'))
			       . ')[\s\t]+</(p|div)>#u';

			if (preg_match($regex, $module->content, $match))
			{
				$module->title   = trim($match[2], ',');
				$module->content = str_replace(array($match[0], 'class="button"'), array('', 'class="button alone"'), $module->content);

				// Set custom class for content.
				$attribs['classSuffix'] .= ' equal-width';
			}
			else
			{
				$module->title   = $module->content;
				$module->content = '';

				// Always disable hideIfEmpty.
				$attribs['hideIfEmpty'] = 0;
			}
		}

		// Get Joomla! 1.6 document object.
		$document =& JFactory::getDocument();

		// Load :hover fail-over Javascript code snippet.
		if ( ! defined('LIQUIDUS_HOVER_FAIL_OVER'))
		{
			define('LIQUIDUS_HOVER_FAIL_OVER', 1);

			$document->_script['text/javascript'] .= (isset($document->_script['text/javascript']) ? "\n" : '').'(function() {
	for (var t in {ul: \'\', dl: \'\'}) {
		var lists = document.getElementsByTagName(t);
		for (var i = 0; i < lists.length; i++) {
			if (lists[i].className.match(/\bhover-menu\b/)) {
				lists[i].onmouseover = function() { this.className.indexOf(\' mouseover\') > -1 || (this.className += \' mouseover\'); };
				lists[i].onmouseout = function(evt) { if (typeof Liquidus != \'undefined\' && !Liquidus.mouseReallyOut(this, evt)) return; this.className = this.className.replace(\' mouseover\', \'\'); };
			}
		}
	}
})();';
		}

		echo LiquidusRendererWrapper::wrap($module, $attribs);
	}
	catch (Exception $e)
	{
		echo '<p class="exception-msg">'.$e->getMessage().'</p>';
	}
}

/**
 * Output module content in tabbed style.
 *
 * @param   object  rendered module content
 * @param   object  module parameters
 * @param   array   attributes for rendering
 * @return  void
 */
function modChrome_LiquidusTab($module, &$params, &$attribs)
{
	try
	{
		// Set parameters to atrributes.
		$attribs['wrapper']     = 'LiquidusTab';
		$attribs['classSuffix'] = $params->get('moduleclass_sfx');
		$attribs['showHeading'] = isset($attribs['showHeading']) ? $attribs['showHeading'] : $module->showtitle;

		// Get Joomla! 1.6 document object.
		$document =& JFactory::getDocument();

		// Set tabbed layout attributes.
		$attribs['name']    = isset($attribs['group']) ? $attribs['group'] : $attribs['name'];
		$attribs['maxTabs'] = isset($attribs['max'])   ? $attribs['max']   : $document->countModules($attribs['name']);

		// Load tabbed layout assets.
		if ( ! defined('LIQUIDUS_TAB_ASSETS'))
		{
			define('LIQUIDUS_TAB_ASSETS', 1);

			$asset = Liquidus::getWebPath(Liquidus::getPath('assets').DS.'css'.DS.'plugins'.DS.'tabber.css');
			$document->_styleSheets[$asset] = array('mime' => 'text/css', 'media' => 'all');

			$asset = Liquidus::getWebPath(Liquidus::getPath('assets').DS.'js'.DS.'libraries'.DS.'tabber-minimized.js');
			$document->_scripts[$asset] = 'text/javascript';
		}

		echo LiquidusRendererWrapper::wrap($module, $attribs);
	}
	catch (Exception $e)
	{
		echo '<p class="exception-msg">'.$e->getMessage().'</p>';
	}
}
?>