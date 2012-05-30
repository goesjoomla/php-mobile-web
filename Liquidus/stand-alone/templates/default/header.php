<?php defined('LIQUIDUS') or die('Direct access not permitted.');
/**
 * A template header for device that has full-featured web browser.
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

// Should not output the XML declaration to prevent IE6 from using quirks mode.
// See http://en.wikipedia.org/wiki/Quirks_mode#Comparison_of_document_types for details.
// echo '<?xml version="1.0" encoding="utf-8"?'.">\n";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php
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
