<?php
/**
 * A class for selecting content positions in Joomla! admin panel.
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

if (defined('JPATH_BASE'))
{
	jimport('joomla.form.formfield');

	class JFormFieldSelectPositions extends JFormField {
		protected        $type        = 'SelectPositions';
		protected static $initialised = false;

		protected function getInput()
		{
			if ( ! self::$initialised)
			{
				// Load the modal behavior script.
				JHtml::_('behavior.modal');

				self::$initialised = true;
			}

			// Initialize variables.
			$html = array();
			$attr = '';

			// Initialize some field attributes.
			$this->element['class'] AND $attr .= ' class="'.$this->element['class'].'"';
			$this->element['size']  AND $attr .= ' size="'.intval($this->element['size']).'"';

			// Initialize JavaScript field attributes.
			$this->element['onchange'] AND $attr .= ' onchange="'.$this->element['onchange'].'"';

			// Generate URL for positions selection.
			$url = JURI::root().'templates/liquidus/libraries/joomla/form/fields/selectpositions.php?id='.$this->id.'&amp;';

			$this->element['multi_selection'] AND $url .= 'multi_selection=1&';
			$this->element['hide_injection']  AND $url .= 'hide_injection=1&';
			$this->element['exclude']         AND $url .= 'exclude='.$this->element['exclude'].'&amp;';
			$this->element['cache']           AND $url .= 'cache='.intval($this->element['cache']).'&amp;';

			$url .= 'theme=';

			// Get element name.
			preg_match('/\[([^\]]+)\]$/', $this->name, $name) AND $name = str_replace('-', '_', $name[1]);

			// The text field.
			$html[] = '<div class="fltlft">';
			$html[] = '	<input type="text" name="'.$this->name.'" id="'.$this->id.'" value="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" readonly="readonly"'.$attr.' />';
			$html[] = '</div>';

			// The button.
			$html[] = '<div class="button2-left">';
			$html[] = '	<div class="blank">';
			$html[] = '		<a class="modal" title="'.JText::_('JSELECT').'" href="#" onclick="var theme = document.getElementById(\''.str_replace($name, '', $this->id).$this->element['theme_field'].'\'); this.href = \''.$url.'\' + theme.options[theme.selectedIndex].value + \'&amp;selection=\' + document.getElementById(\''.$this->id.'\').value;" rel="{handler: \'iframe\', size: {x: 800, y: 500}}">';
			$html[] = '			'.JText::_('JSELECT').'</a>';
			$html[] = '	</div>';
			$html[] = '</div>';

			$html[] = '<div class="button2-left">';
			$html[] = '	<div class="blank">';
			$html[] = '		<a title="'.JText::_('JCLEAR').'" href="#" onclick="document.getElementById(\''.$this->id.'\').value = \' \';">';
			$html[] = '			'.JText::_('JCLEAR').'</a>';
			$html[] = '	</div>';
			$html[] = '</div>';

			return implode("\n", $html);
		}
	}
}
elseif (isset($_REQUEST['theme']))
{
	// Initialize Joomla's administrator application.
	define('_JEXEC',     1);
	define('DS',         DIRECTORY_SEPARATOR);
	define('JPATH_BASE', dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__))))))).DS.'administrator');

	require JPATH_BASE.DS.'includes'.DS.'defines.php';
	require JPATH_BASE.DS.'includes'.DS.'framework.php';
	require JPATH_BASE.DS.'includes'.DS.'helper.php';
	require JPATH_BASE.DS.'includes'.DS.'toolbar.php';

	$mainframe = & JFactory::getApplication('administrator');
	$mainframe->initialise(array('language' => $mainframe->getUserState('application.lang', 'lang')));

	// Make sure this file is requested from Joomla! administration panel.
	$user = & JFactory::getUser();
	! $user->get('guest') OR die('Please login to your Joomla! 1.6 administration panel first.');

	// Make sure requested theme exists.
	$theme = dirname(dirname(dirname(dirname(dirname(__FILE__))))).DS.'themes'.DS.$_REQUEST['theme'].DS.$_REQUEST['theme'].'.xml';
	is_readable($theme) OR die('The specified theme, <q>'.$_REQUEST['theme'].'</q>, does not exists.');

	// Make sure we have the id of positions selection field.
	isset($_REQUEST['id']) OR die('Invalid request.');

	// Prepare parameters.
	$multi_selection = isset($_REQUEST['multi_selection']) ? $_REQUEST['multi_selection']       : FALSE;
	$hide_injection  = isset($_REQUEST['hide_injection'])  ? $_REQUEST['hide_injection']        : FALSE;
	$exclude         = isset($_REQUEST['exclude'])         ? explode(',', $_REQUEST['exclude']) : array();
	$cache           = isset($_REQUEST['cache'])           ? $_REQUEST['cache']                 : -1;

	// Initialize Liquidus framework.
	require dirname(dirname(dirname(dirname(dirname(__FILE__))))).DS.'bootstrap.php';

	if ($cache >= 0)
	{
		$cache    = & LiquidusCache::getInstance($_SERVER['REQUEST_URI'], $cache);
		$renderer = $cache->get('renderer');
	}

	if ( ! isset($renderer))
	{
		$renderer = new LiquidusTemplate();

		// Load base stylesheets.
		$renderer->addAsset('css', 'reset');
		$renderer->addAsset('css', 'typography');
		$renderer->addAsset('css', 'plugins.layout');
		$renderer->addAsset('css', 'plugins.form');
		$renderer->addAsset('css', 'plugins.button');

		// Define Javascript function for updating positions selection.
		$code   = array();
		$code[] = 'function updatePositionsSelection() {';
		$code[] = '			var form = document.getElementById("positions-selection-form");';
		$code[] = '			var ui = form.getElementsByTagName("fieldset"), selections = "";';
		$code[] = '			for (var i = 0; i < ui.length; i++) {';
		$code[] = '				var selection = "";';
		$code[] = '				for (var j = 0; j < form[ui[i].id + "-position"].length; j++) {';
		$code[] = '					if (form[ui[i].id + "-position"][j].checked) {';

		if ( ! $multi_selection)
		{
			$code[] = '						selection = form[ui[i].id + "-position"][j].value;';
			$code[] = '						break;';
		}
		else
		{
			$code[] = '						selection += (selection == "" ? "" : ",") + form[ui[i].id + "-position"][j].value;';
		}

		$code[] = '					}';
		$code[] = '				}';
		$code[] = '				if (selection != "") {';
		$code[] = '					selections += (selections == "" ? "" : "|") + ui[i].id + ":" + selection;';

		if ( ! $hide_injection)
		{
			$code[] = '					selection = "";';
			$code[] = '					for (var j = 0; j < form[ui[i].id + "-injection"].length; j++) {';
			$code[] = '						if (form[ui[i].id + "-injection"][j].checked) {';
			$code[] = '							selection = ":" + form[ui[i].id + "-injection"][j].value;';
			$code[] = '							break;';
			$code[] = '						}';
			$code[] = '					}';
			$code[] = '					if (selection == "") {';
			$code[] = '						alert("'.fText::compose('Please select content injection method for `_UI_` UI.').'".replace("_UI_", ui[i].id));';
			$code[] = '						return false;';
			$code[] = '					}';
			$code[] = '					selections += selection;';
		}

		$code[] = '				}';
		$code[] = '			}';
		$code[] = '			window.parent.document.getElementById("'.$_REQUEST['id'].'").value = selections;';
		$code[] = '			window.parent.SqueezeBox.close();';
		$code[] = '		}';

		$renderer->set('inline-js', implode("\n", $code));

		// Set header template.
		$code   = array();
		$code[] = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
		$code[] = '<html xmlns="http://www.w3.org/1999/xhtml">';
		$code[] = '<head>';
		$code[] = '<?php $this->place("css"); $this->place("inline-js"); ?>';
		$code[] = '</head>';
		$code[] = '<body>';

		$renderer->set('header', implode("\n", $code));

		// Set footer template.
		$code   = array();
		$code[] = '</body>';
		$code[] = '</html>';

		$renderer->set('footer', implode("\n", $code));

		// Get current selection.
		$selection = array();
		if (isset($_REQUEST['selection']))
		{
			foreach (explode('|', $_REQUEST['selection']) AS $ui)
			{
				list($ui, $positions, $method) = explode(':', $ui, 3);
				$selection[$ui]['p']           = explode(',', $positions);
				$selection[$ui]['m']           = $method;
			}
		}

		// Parse theme declaration file.
		$theme = new LiquidusXml($theme);

		// Generate HTML code for positions selection.
		$code   = array();
		$code[] = '	<form id="positions-selection-form" method="post" action="'.$_SERVER['REQUEST_URI'].'" onsubmit="updatePositionsSelection(); return false;">';

		foreach ($theme->xpath('liquidus:ui[@for]') AS $ui)
		{
			$positions = $theme->xpath('liquidus:ui[@for="'.$ui['for'].'"]//liquidus:placeholder');

			if ($positions AND count($positions) > 0)
			{
				if ( ! in_array( (string) $ui['for'], $exclude))
				{
					$code[] = '		<fieldset id="'.$ui['for'].'">';
					$code[] = '			<legend>'.fText::compose('%s UI', ucfirst($ui['for'])).'</legend>';

					// Positions list.
					$code[] = '			<div class="split-2 clearfix">';
					$code[] = '				<div class="fl w30p"><span class="label">'.fText::compose('Select a Position').'</span></div>';
					$code[] = '				<div class="fr w70p">';
					$code[] = '					<ul class="no-bullet split-3 clearfix">';

					$counting = 1;

					foreach ($positions AS $position)
					{
						$code[] = '						<li class="'.($counting % 3 == 0 ? 'fr' : 'fl').'">';
						$code[] = '							<input id="'.$ui['for'].'-position-'.$position['name'].'" type="'.($multi_selection ? 'checkbox' : 'radio').'" name="'.$ui['for'].'-position" value="'.$position['name'].'"'.((isset($selection[ (string) $ui['for']]) AND in_array( (string) $position['name'], $selection[ (string) $ui['for']]['p'])) ? ' checked="checked"' : '').' />';
						$code[] = '							<label for="'.$ui['for'].'-position-'.$position['name'].'" style="font-weight:normal">'.fText::compose($position['name']).'</label>';
						$code[] = '						</li>';

						$counting++;
					}

					$code[] = '					</ul>';
					$code[] = '				</div>';
					$code[] = '			</div>';

					// Injection method.
					if ( ! $hide_injection)
					{
						$code[] = '			<br /><hr />';
						$code[] = '			<div class="split-2 clearfix">';
						$code[] = '				<div class="fl w30p"><span class="label">'.fText::compose('Content Injection Method').'</span></div>';
						$code[] = '				<div class="fr w70p">';
						$code[] = '					<ul class="no-bullet split-3 clearfix">';

						foreach (array('prepend', 'append', 'if-empty') AS $method)
						{
							$code[] = '						<li class="'.($method == 'if-empty' ? 'fr' : 'fl').'">';
							$code[] = '							<input id="'.$ui['for'].'-injection-'.$method.'" type="radio" name="'.$ui['for'].'-injection" value="'.$method.'"'.((isset($selection[ (string) $ui['for']]) AND $method == $selection[ (string) $ui['for']]['m']) ? ' checked="checked"' : '').' />';
							$code[] = '							<label for="'.$ui['for'].'-injection-'.$method.'" style="font-weight:normal">'.fText::compose(ucfirst(str_replace('-', ' ', $method))).'</label>';
							$code[] = '						</li>';
						}

						$code[] = '					</ul>';
						$code[] = '				</div>';
						$code[] = '			</div>';
					}

					$code[] = '		</fieldset>';
				}
			}
		}

		// Generate HTML code for buttons.
		$code[] = '		<br />';
		$code[] = '		<p class="a-center">';
		$code[] = '			<input class="button" type="submit" name="submit" value="Submit" />';
		$code[] = '			<input class="button" type="button" name="cancel" value="Cancel" onclick="window.parent.SqueezeBox.close();" />';
		$code[] = '		</p>';

		$code[] = '	</form>';

		$renderer->set('body', implode("\n", $code));

		is_object($cache) AND $cache->set('renderer', $renderer);
	}

	$renderer->place('header');
	$renderer->place('body');
	$renderer->place('footer');
}
?>