<?php defined('_JEXEC') or die('Direct access not permitted.');
/**
 * A bridge file to integrate Joomla! 1.6 and Liquidus.
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

// Bootstrap Liquidus web template system.
require dirname(__FILE__).DIRECTORY_SEPARATOR.'bootstrap.php';

// Load Joomla! 1.6 framework assets.
JHTML::_('behavior.framework', true);

// Now, let's Liquidus render the Joomla! 1.6 template.
if ($document->params->get('cache-interval') >= 0)
{
	LiquidusCache::setPath(JPATH_ROOT.DS.$document->params->get('cache-directory'));

	$cache    = & LiquidusCache::getInstance($_SERVER['REQUEST_URI'], intval($document->params->get('cache-interval')) * 60);
	$renderer = $cache->get('renderer');
}

if ( ! isset($renderer))
{
	$renderer = & LiquidusRenderer::getSingleton($document->params->get('theme'));

	// Register hook for integrating with Joomla! 1.6.
	//$renderer->hook->register('LiquidusRendererHookJoomla');
	$renderer->hook->register('LiquidusRendererHookPreview');

	// Pass necessary parameters to the renderer engine.
	$renderer->set('base',       $document->baseurl);
	$renderer->set('site-logo',  $document->params->get('logo'));
	$renderer->set('site-title', $document->params->get('title'));
	$renderer->set('site-desc',  $document->params->get('description'));

	$document->params->get('cache-interval') < 0 OR $cache->set('renderer', $renderer);
}

$renderer->render();
?>