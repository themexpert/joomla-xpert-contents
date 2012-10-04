<?php
/**
 * @package Xpert Contents
 * @version 1.3
 * @author ThemeXpert http://www.themexpert.com
 * @copyright Copyright (C) 2009 - 2011 ThemeXpert
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 */
 
// no direct access
defined('_JEXEC') or die('Restricted accessd');


// Include the syndicate functions only once
require_once (dirname(__FILE__).DS.'helper.php');

$lists = modXpertContentsHelper::getLists($params);

//set moduleid
$module_id = ($params->get('auto_module_id',1)==1) ? 'xc-'.$module->id : $params->get('module_unique_id');


modXpertContentsHelper::loadStyles($params,$module_id);
modXpertContentsHelper::loadScripts($params,$module_id);

require JModuleHelper::getLayoutPath('mod_xpertcontents', $params->get('layout', 'default'));
