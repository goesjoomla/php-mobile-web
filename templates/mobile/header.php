<?php defined('LIQUIDUS') or die('Direct access not permitted.');
/**
 * A header template for mobile device that has a web browser support XHTML Mobile Profile.
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

// Set Content-Type header so client knows that this is a XHTML MP document.
if (strpos($_SERVER['HTTP_ACCEPT'], 'application/vnd.wap.xhtml+xml') !== FALSE)
{
	header('Content-Type: application/vnd.wap.xhtml+xml');
}
elseif (strpos($_SERVER['HTTP_ACCEPT'], 'application/vnd.wap.xhtml+xml') !== FALSE)
{
	header('Content-Type: application/xhtml+xml');
}
?>
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php
	$this->add('meta', array(
		'name'    => 'viewport',
		'content' => 'width=device-width, initial-scale=1'
	));

	$this->place('title');
	$this->place('meta');
	$this->place('base');
	$this->place('css');
	$this->place('js');
	$this->place('inline-css');
	$this->place('inline-js');
?>
</head>
<body>
