<?php defined('LIQUIDUS') or die('Direct access not permitted.');
/**
 * A header template for tablet PC in which the primary input method is touching the screen.
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

// Consider a tablet PC as a desktop/laptop PC that has full-featured web browser.
require dirname(dirname(__FILE__)).DS.'default'.DS.'header.php';

// Consider a tablet PC as a mobile device that usually does not have a full-featured web browser.
//require dirname(dirname(__FILE__)).DS.'mobile'.DS.'header.php';
?>