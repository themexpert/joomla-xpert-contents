/**
 * @package Xpert Contents
 * @version 1.3
 * @author ThemeXpert http://www.themexpert.com
 * @copyright Copyright (C) 2009 - 2011 ThemeXpert
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 */

jQuery.noConflict();
jQuery(document).ready(function(){

    //Joomla, k2, Easyblog Accordion hide and show effect depend on content source.
    function showJoomla(){
        jQuery('#JOOMLA_CONTENT_SETTINGS-options').parent().show();
        jQuery('#K2_CONTENT_SETTINGS-options').parent().hide();
        jQuery('#EASY_BLOG_CONTENT_SETTINGS-options').parent().hide();
    }
    function showK2(){
        jQuery('#JOOMLA_CONTENT_SETTINGS-options').parent().hide();
        jQuery('#K2_CONTENT_SETTINGS-options').parent().show();
        jQuery('#EASY_BLOG_CONTENT_SETTINGS-options').parent().hide();
    }
    function showEasyblog(){
        jQuery('#JOOMLA_CONTENT_SETTINGS-options').parent().hide();
        jQuery('#K2_CONTENT_SETTINGS-options').parent().hide();
        jQuery('#EASY_BLOG_CONTENT_SETTINGS-options').parent().show();
    }

    //determine which settings is enable in content source and show related container
    switch(jQuery('#jform_params_content_source').val()){
        case 'joomla':
            showJoomla();
            break;
        case 'k2':
            showK2();
            break;
        case 'easyblog':
            showEasyblog();
            break;
    }

    //change accordion realtime
    jQuery('#jform_params_content_source').change(function(){
        switch(jQuery('#jform_params_content_source').val()){
        case 'joomla':
            showJoomla();
            break;
        case 'k2':
            showK2();
            break;
        case 'easyblog':
            showEasyblog();
            break;
    }
    });
    jQuery(".toggle").exposeToggle();
});