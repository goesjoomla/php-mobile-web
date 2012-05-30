<?php defined('_JEXEC') or die('Direct access not permitted.');

require dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'bootstrap.php';

if ($tmpl = LiquidusRendererHookJoomla::getTemplateOverride(__FILE__))
{
	require $tmpl;
}
?>