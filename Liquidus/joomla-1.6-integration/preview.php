<?php
require dirname(__FILE__).DIRECTORY_SEPARATOR.'bootstrap.php';

$cache = & LiquidusCache::getInstance('rendererTest', 1);
$test  = $cache->get('rendererTest');

if (empty($test))
{
	$test = & LiquidusRenderer::getSingleton('default');

	// Register hooks.
	$test->hook->register('LiquidusRendererHookPreview');

	// Set page title.
	$test->set('title', 'Liquidus - The Universal Web Template System for PHP-powered Mobile Web Site, Mobile Web Application');

	// Set base URL
	$test->set('base', '/');

	// Adding some sample meta tags.
	$test->add('meta', array(
		'http-equiv' => 'content-type',
		'content'    => 'text/html; charset=utf-8'
	));

	$test->add('meta', array(
		'name'    => 'robots',
		'content' => 'index, follow'
	));

	$test->add('meta', array(
		'name'    => 'keywords',
		'content' => 'liquidus, what is mobile web, web for mobile, web to mobile, mobile web site, web site for mobile, mobile internet web, mobile web page, web page for mobile, mobile phone web, web for mobile phone, template for mobile, mobile web design, web design for mobile, design for mobile web, mobile web application, web application for mobile, mobile web app, make mobile web, mobile web development, web development for mobile, mobile website template, template for mobile website, mobile web software, web 2 mobile, mobile web template, build mobile web site, develop mobile web, mobile web templates, web templates for mobile, mobile device web, web for mobile devices, how to build a mobile web site, web template for mobile, mobile web designs, mobile web site design, make mobile web page, how to make mobile web page, mobile site template, how to make a mobile web page, convert web to mobile, joomla mobile template, mobile template for joomla, joomla template for mobile'
	));

	$test->add('meta', array(
		'name'    => 'description',
		'content' => 'Liquidus - the universal web template system for PHP-powered mobile web site, mobile web application. Liquidus provides support for serving appropriated mobile web design, content adaptation for different types of mobile devices. Thus enabling proactive mobile web development, web to mobile content adaptation for feature phone, smartphone, tablet PC and other types of mobile handheld devices.'
	));

	$test->add('meta', array(
		'name'    => 'generator',
		'content' => 'Liquidus - The Universal Web Template System for PHP-powered Mobile Web Site, Mobile Web Application'
	));

	// Test LiquidusCache class also.
	$cache->set('rendererTest', $test);
}
else
{
	$test->set('title', $test->get('title').' .: loaded from cache :.');
}

// Render.
$test->render();
?>