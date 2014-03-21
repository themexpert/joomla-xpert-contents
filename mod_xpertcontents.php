<?php
/**
 * @package Xpert Contents
 * @version ##VERSION##
 * @author ThemeXpert http://www.themexpert.com
 * @copyright Copyright (C) 2009 - 2011 ThemeXpert
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted accessd');

// Check for the framework, if not found eject
include_once JPATH_LIBRARIES . '/xef/bootstrap.php';

if( !defined('XEF_INCLUDED'))
{
    echo 'Your Module installation is broken; please re-install. Alternatively, extract the installation archive and copy the xef directory inside your site\'s libraries directory.';
    return ;
}

// Include the syndicate functions only once
require_once (dirname(__FILE__). '/helper.php');

//set module id
$module_id = XEFUtility::getModuleId($module, $params);

// Content source
$content_source = $params->get('content_source','joomla');

// Import source and get the class name
$class_name = importSource($content_source);

// Create instance of the provider class
$source = new $class_name($module, $params);

// Get the primary and secondary item count
$primary_count = (int)$params->get('primary_count');
$sec_count = (int)$params->get('sec_count');
$count = $primary_count+$sec_count;

// Set the counted number as total item to fetch
$source->set('count', $count);
// Get the items
$items = $source->getItems();
// Determine total items count based on total and count check
$total = count($items);
$total = ( $count > $total ) ? $total : $count;

// Check Primary count
$primary_count = ( $primary_count > $total ) $total : $primary_count;

// Based on primary column value we'll set secondary visibility
$secondary_show = ( $primary_count < $total ) ? TRUE : FALSE;

$layout = $params->get('layout', 'default');


// Load Stylesheet file
XEFUtility::loadStyleSheet($module, $params);

// Load Module specific script
// XEFXpertScrollerHelper::load_script($module, $params);

// Load Module specific style defination
// XEFXpertScrollerHelper::load_style($module, $params);


require JModuleHelper::getLayoutPath($module->module, $params->get('layout', 'default'));
