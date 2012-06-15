<?php defined('LIQUIDUS') or die('Direct access not permitted.');
/**
 * A quick and flexible class for rendering HTML template.
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

class LiquidusTemplate extends LiquidusRegistry {
	/**
	 * Add a script or stylesheet file to registry.
	 *
	 * @param   string   $type        File type: 'js' for script, 'css' for stylesheet
	 * @param   string   $path        Path to script/stylesheet file
	 * @param   mixed    $attributes  Attributes to set in markup tag
	 * @param   boolean  $prepend     TRUE to prepend, FALSE to append the asset to the assets array
	 * @return  void
	 */
	public function addAsset($type, $path, $attributes = NULL, $prepend = FALSE)
	{
		// Validate file type.
		if ( ! in_array($type, array('js', 'css'))) {
			throw new Exception(fText::compose(
				'The asset type specified, <q>%s</q>, is invalid.',
				$type
			));
		}

		// An array containing all added assets.
		static $assets;
		isset($assets) OR $assets = array('js' => array(), 'css' => array());

		// Prepare asset URL.
		if ( ! preg_match('/https?:/', $path))
		{
			// Prepare asset file system path.
			if ( ! is_readable($path))
			{
				// Remove file extension and any leading directory separator.
				$path = str_replace('.'.$type, '', trim($path, '/\\'));

				// Replace dot (.) character with the real directory separator.
				if (strpos($path, '/') === FALSE AND strpos($path, '\\') === FALSE)
				{
					$path = str_replace('.', DS, $path);
				}

				if (is_readable(Liquidus::getPath('assets').DS.$type.DS.$path.'.'.$type))
				{
					$path = Liquidus::getPath('assets').DS.$type.DS.$path.'.'.$type;
				}
				elseif (is_readable(Liquidus::getPath().DS.$path.'.'.$type))
				{
					$path = Liquidus::getPath().DS.$path.'.'.$type;
				}
			}

			// Check if asset file exists.
			if (is_readable($path))
			{
				$path = Liquidus::getWebPath($path);
			}
			else
			{
				throw new Exception(fText::compose(
					'The asset specified, <q>%1$s</q>, does not exist under neither the specified location nor the <q>assets/%2$s</q> directory.',
					basename($path).'.'.$type,
					$type
				));
			}
		}

		// Set attributes if asset not already added.
		if ( ! isset($assets[$type][$path]))
		{
			$assets[$type][$path] = is_array($attributes) ? $attributes : array();

			if (isset($attributes) AND is_string($attributes))
			{
				// Convert attributes string to array.
				parse_str(
					preg_replace(
						array(
							'/([^\s^=]+)\s*=\s*["\']([^"^\']+)["\']/e',
							'/\s+/'
						),
						array(
							'\'$1=\'.urlencode(trim(\'$2\'))',
							'&'
						),
						$attributes
					),
					$assets[$type][$path]
				);
			}

			// Set asset path to attributes array.
			$assets[$type][$path][$type == 'js' ? 'src' : 'href'] = $path;

			// Add asset to registry.
			$this->add($type, $assets[$type][$path], $prepend);
		}
	}

	/**
	 * Render a template variable to HTML code then place.
	 *
	 * @param   string  $key  Key name to get value from registry
	 * @return  mixed
	 */
	public function place($key)
	{
		// Get appropriate place method.
		$method = 'place'.preg_replace('/\-([a-z])/e', 'strtoupper(\'$1\')', ucfirst($key));
		$method = method_exists($this, $method) ? $method : 'placeRaw';

		// Get method specific arguments.
		if ($method == 'placeRaw')
		{
			$args = func_get_args();
		}
		else
		{
			$args = func_num_args() > 1 ? array_slice(func_get_args(), 1) : array();
		}

		return call_user_func_array(array($this, $method), $args);
	}

	/**
	 * Render HTML title tag then place.
	 *
	 * @param   string  $key  Key name to get value from registry
	 * @return  void
	 */
	protected function placeTitle($key = 'title')
	{
		$value = $this->get($key);

		if ($value)
		{
			$value = is_array($value) ? array_pop($value) : $value;

			if (is_string($value)) {
				echo "\t".'<title>'.htmlentities($value).'</title>'."\n";
			}
		}
	}

	/**
	 * Render HTML base tag then place.
	 *
	 * @param   string  $key  Key name to get value from registry
	 * @return  void
	 */
	protected function placeBase($key = 'base')
	{
		$value = $this->get($key);

		if ($value)
		{
			$value = is_array($value) ? array_pop($value) : $value;

			if (is_string($value)) {
				echo "\t".'<base href="'.htmlentities($value).'" />'."\n";
			}
		}
	}

	/**
	 * Render HTML meta tags then place.
	 *
	 * @param   string  $key  Key name to get value from registry
	 * @return  void
	 */
	protected function placeMeta($key = 'meta')
	{
		$value = $this->get($key, NULL, TRUE);

		if ($value)
		{
			foreach ($value AS $meta)
			{
				$meta = is_object($meta) ? get_object_vars($meta) : $meta;

				if (is_string($meta))
				{
					echo "\t".$meta."\n";
				}
				elseif (is_array($meta))
				{
					echo "\t".'<meta';

					foreach ($meta AS $k => $v)
					{
						echo ' '.$k.'="'.$v.'"';
					}

					echo ' />'."\n";
				}
			}
		}
	}

	/**
	 * Render HTML link tags then place.
	 *
	 * @param   string   $key      Key name to get value from registry
	 * @param   boolean  $isValue  TRUE to indicate that $key is a value
	 * @return  void
	 */
	protected function placeLink($key = 'link', $isValue = FALSE)
	{
		$value = $isValue ? $key : $this->get($key, NULL, TRUE);

		if ($value)
		{
			foreach ($value AS $link)
			{
				$link = is_object($link) ? get_object_vars($link) : $link;

				if (is_array($link) AND isset($link['href']))
				{
					echo "\t".'<link';

					foreach ($link AS $k => $v)
					{
						echo ' '.$k.'="'.$v.'"';
					}

					echo ' />'."\n";
				}
			}
		}
	}

	/**
	 * Render HTML stylesheet link tags then place.
	 *
	 * @param   string   $key      Key name to get value from registry
	 * @param   boolean  $isValue  TRUE to indicate that $key is a value
	 * @return  void
	 */
	protected function placeCss($key = 'css', $isValue = FALSE)
	{
		$value = $isValue ? $key : $this->get($key, NULL, TRUE);

		if ($value)
		{
			$css = array();

			foreach ($value AS $link)
			{
				$link = is_object($link) ? get_object_vars($link) : $link;

				// If just path is given, add necessary attributes.
				if (is_string($link))
				{
					$link = array(
						'rel'   => 'stylesheet',
						'type'  => 'text/css',
						'media' => 'all',
						'href'  => Liquidus::getWebPath($link)
					);
				}

				if (is_array($link) AND isset($link['href']))
				{
					// Prepare necessary attributes.
					isset($link['rel'])   OR $link['rel']   = 'stylesheet';
					isset($link['type'])  OR $link['type']  = 'text/css';
					isset($link['media']) OR $link['media'] = 'all';

					$css[] = $link;
				}
			}

			$this->placeLink($css, TRUE);
		}
	}

	/**
	 * Render inline style declaration then place.
	 *
	 * @param   string  $key  Key name to get value from registry
	 * @return  void
	 */
	protected function placeInlineCss($key = 'inline-css')
	{
		$value = $this->get($key);

		if ($value)
		{
			echo "\t".'<style type="text/css"><!--'."\n";
			echo "\t\t".implode("\n\t\t", (array) $value)."\n";
			echo "\t".'--></style>'."\n";
		}
	}

	/**
	 * Render HTML script link tags then place.
	 *
	 * @param   string   $key      Key name to get value from registry
	 * @param   boolean  $isValue  TRUE to indicate that $key is a value
	 * @return  void
	 */
	protected function placeJs($key = 'js', $isValue = FALSE)
	{
		$value = $isValue ? $key : $this->get($key, NULL, TRUE);

		if ($value)
		{
			foreach ($value AS $script)
			{
				$script = is_object($script) ? get_object_vars($script) : $script;

				// If just path is given, add necessary attributes.
				if (is_string($script))
				{
					$script = array(
						'type' => 'text/javascript',
						'src'  => Liquidus::getWebPath($script)
					);
				}

				if (is_array($script) AND isset($script['src']))
				{
					// Prepare necessary attributes.
					isset($script['type']) OR $script['type'] = 'text/javascript';

					echo "\t".'<script';

					foreach ($script AS $k => $v)
					{
						echo ' '.$k.'="'.$v.'"';
					}

					echo '></script>'."\n";
				}
			}
		}
	}

	/**
	 * Render inline script declaration then place.
	 *
	 * @param   string  $key  Key name to get value from registry
	 * @return  void
	 */
	protected function placeInlineJs($key = 'inline-js')
	{
		$value = @array_unique($this->get($key, NULL, TRUE), SORT_REGULAR);

		if ($value)
		{
			echo "\t".'<script type="text/javascript"><!-- // --><![CDATA['."\n";
			echo "\t\t".implode("\n\t\t", (array) $value)."\n";
			echo "\t".'// ]]></script>'."\n";
		}
	}

	/**
	 * Render inline onDomReady script execution then place.
	 *
	 * @param   string  $key  Key name to get value from registry
	 * @return  void
	 */
	protected function placeDomReady($key = 'dom-ready')
	{
		$value = @array_unique($this->get($key, NULL, TRUE), SORT_REGULAR);

		if ($value AND ($js = $this->get('js', NULL, TRUE)) != NULL)
		{
			foreach ($js AS $script)
			{
				$script = is_object($script) ? get_object_vars($script) : $script;
				$script = is_array($script)  ? @$script['src']          : $script;
				
				if ($script AND strpos($script, 'dojo') !== FALSE)
				{
					$this->set($key, 'dojo.ready(function(){'."\n\t\t\t".implode("\n\t\t\t", (array) $value)."\n\t\t".'});');
				}
			}
		}
		
		$this->placeInlineJs($key);
	}

	/**
	 * Include a HTML or PHP file dynamically.
	 *
	 * @param   string   $key      Key name to get value from registry
	 * @param   boolean  $isValue  TRUE to indicate that $key is a value
	 * @return  void
	 */
	protected function placeFile($key = 'file', $isValue = FALSE)
	{
		$value = $isValue ? $key : $this->get($key);

		if ($value)
		{
			foreach ( (array) $value AS $file)
			{
				if (is_string($file) AND is_readable($file))
				{
					require $file;
				}
			}
		}
	}

	/**
	 * Auto-detect variable value then render to HTML code and place.
	 *
	 * @param   string   $key   Value or key name to get value from registry
	 * @param   boolean  $eval  TRUE to auto-evaluate PHP code embeded in string value
	 * @return  void
	 */
	protected function placeRaw($key, $eval = TRUE)
	{
		$data = (is_string($key) AND isset($this->registry[$key])) ? $this->get($key, NULL, TRUE) : $key;

		if ($data)
		{
			foreach ( (array) $data AS $value)
			{
				if (is_string($value))
				{
					// Check if value is a file path.
					if (strpos($value, "\n") === FALSE AND ($dot = strpos($value, '.', ($dot = strlen($value) - 6) > 0 ? $dot : 0)) !== FALSE AND is_readable($value))
					{
						$ext = strtolower(substr($value, $dot + 1));

						switch ($ext)
						{
							case 'css':
								$this->placeCss($value, TRUE);
							break;

							case 'js':
								$this->placeJs($value, TRUE);
							break;

							case 'php':
							case 'inc':
							case 'htm':
							case 'html':
							case 'phtml':
								$this->placeFile($value, TRUE);
							break;
						}
					}
					else
					{
						// Evaluate embeded PHP code if necessary.
						$eval AND strpos($value, '<?php') !== FALSE AND $value = $this->evaluate($value);

						// Output the string.
						echo $value."\n";
					}
				}
			}
		}
	}

	protected function evaluate($text) {
		$html = array();

		while (TRUE)
		{
			unset($code);

			// Split the string by PHP (full) open tag.
			list($text, $code) = explode('<?php', $text, 2);

			// Store plain text.
			$html[] = $text;

			// Simply break the infinite while loop if no more PHP code found.
			if ( ! isset($code) OR $code == '')
				break;

			// Split the code by PHP close tag.
			list($code, $text) = explode('?>', $code, 2);

			// Evaluate PHP code then store the output.
			ob_start();
			eval($code);
			$html[] = ob_get_clean();
		}

		// Finalize HTML code.
		$html = implode('', $html);

		foreach (array("\r\n", "\r", "\n") AS $nl)
		{
			substr($html, 0, strlen($nl))  != $nl OR $html = substr($html, strlen($nl));
			substr($html, 0 - strlen($nl)) != $nl OR $html = substr($html, 0, 0 - strlen($nl));
		}

		return $html;
	}
}
?>