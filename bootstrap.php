<?php
/**
 * A bootstrap file for initializing Liquidus.
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

if ( ! defined('LIQUIDUS'))
{
	// Shorten the directory separator constant.
	defined('DS') OR define('DS', DIRECTORY_SEPARATOR);

	// Include the Liquidus core file.
	require dirname(__FILE__).DS.'libraries'.DS.'liquidus.php';

	// Register the root Liquidus path.
	Liquidus::setPath(dirname(__FILE__));

	// Enable error and exception handling.
	error_reporting(E_ALL & ~E_NOTICE);
	fCore::enableErrorHandling('html');
	fCore::enableExceptionHandling('html');
}

// Initialize Joomla! 1.6 template parameters.
$document = & JFactory::getDocument();

if ( ! isset($document->params))
{
	$app              = JFactory::getApplication();
	$document->params = $app->getTemplate(true)->params;
}

if ( ! $document->params->get('theme'))
{
	isset($document->params) OR $document->params = new JRegistry('');

	$params = new LiquidusXml(dirname(__FILE__).DS.'templateDetails.xml');
	$params = $params->xpath('config/fields[@name="params"]//field');

	foreach ($params AS $param)
	{
		if ($param['name']) {
			$document->params->def(
				(string) $param['name'],
				preg_match('/^\d+$/', (string) $param['default']) ? intval($param['default']) : (string) $param['default']
			);
		}
	}
}
?>