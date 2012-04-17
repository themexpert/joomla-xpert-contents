<?php
/**
 * @package Expose
 * @subpackage Xpert Contents
 * @version 1.1
 * @author ThemeXpert http://www.themexpert.com
 * @copyright Copyright (C) 2009 - 2011 ThemeXpert
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldToggle extends JFormField{

    protected $type = 'Toggle';

    public function getInput(){
        global $expose;
        $output = NULL;

        // Initialize some field attributes.
        $class		= $this->element['class'];
        $disabled	= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
        $checked	= ((string) $this->element['value'] == $this->value) ? ' checked="checked"' : '';

        return '<input class="toggle '.$class.'" type="checkbox" name="'.$this->name.'" id="'.$this->id.'"' .
                        ' value="'.htmlspecialchars((string) $this->element['value'], ENT_COMPAT, 'UTF-8').'"' .
                        $checked.$disabled.'/>';
    }
}

