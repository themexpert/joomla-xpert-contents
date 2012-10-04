<?php
/**
 * @package Expose
 * @subpackage Xpert Contents
 * @version 1.3
 * @author ThemeXpert http://www.themexpert.com
 * @copyright Copyright (C) 2009 - 2011 ThemeXpert
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldSpacer extends JFormField{

    protected $type = 'Spacer';

    protected function getInput(){
        return '';
    }

    protected function getLabel(){
        $html   = array();
        $class  = (string) $this->element['class'];
        $label  = '';

        // Get the label text from the XML element, defaulting to the element name.
        $text = $this->element['text'] ? (string) $this->element['text'] : '';
        $text = $this->translateLabel ? JText::_($text) : $text;


        // Add the label text and closing tag.
        if($text != NULL){
            $label .= '<div class="expose-spacer'.(($text != '') ? ' hasText hasTip' : '').'" title="'. JText::_($this->description) .'"><span>' . JText::_($text) . '</span></div>';
        }

        $html[] = $label;

        return implode('', $html);
    }
}

