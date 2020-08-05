<?php
/**
 * @package   Xpert Installer
 * @version   2.1
 * @author    Parvez Akther http://www.themexpert.com
 * @copyright Copyright (C) 20010 - 2012 Parvez Akther
 * @license   GNU/GPL v3 or later license
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');


class PlgSystemInstallerInstallerScript
{

    private $sourcedir = '';
    private $manifest = '';
    private $extensions = array();

    /**
	 * method to run before an install/update/uninstall method
	 *
	 * @return void
	 */
	function preflight($type, $parent) 
	{
        $this->installExtensions($parent);
	}

    /*function install($parent)
    {

    }*/

	/**
	 * method to run after an install/update/uninstall method
	 *
	 * @return void
	 */
	function postflight($type, $parent) 
	{
        $parent->getParent()->abort();
	}

    private function installExtensions($parent)
    {
        $src = $parent->getParent()->getPath('source');
        $xml = $parent->getParent()->getManifest();
        $db = JFactory::getDbo();
        $status = array();
        $buffer = '';

        // Opening HTML
        ob_start();
    ?>
    <div id="xei-logo">
        <ul id="xei-status">
    <?php
        $buffer .= ob_get_clean();
        if ( count($xml->extlist->children()) )
        {
            foreach ($xml->extlist->children() as $ext)
            {
                $path = $src . '/' . $ext->folder;
                $type = $ext->attributes()->type;

                if (is_dir($path))
                {
                    // Was the extension already installed?
                    // we'll set the query based on ext type
                    switch ($type)
                    {
                        case 'module':
                        case 'library':
                            $query = $db->qn('element').' = '.$db->q($ext->folder);
                            break;
                        case 'plugin':
                            $query = array();
                            $query[] = $db->qn('element').' = '.$db->q(str_replace('plg_','',$ext->folder));
                            $query[] = $db->qn('folder').' = '.$db->q($ext->attributes()->group);
                            break;
                        case 'template':
                            $folder = str_replace('tpl_','', $ext->folder);
                            $query = $db->qn('element').' = '.$db->q($folder);
                            break;
                    }

                    $sql = $db->getQuery(true)
                        ->select('COUNT(*)')
                        ->from('#__extensions')
                        ->where($query);

                    $db->setQuery($sql);
                    $count = $db->loadResult();

                    // if extension is found on database then its upgrade state
                    if( $count ) $state = 'update';
                    else $state = 'install';

                    //take new installer instance for installing sub extensions
                    $installer = new JInstaller;
                    $result = $installer->install($path);;

                    if($result)
                    {
                        $version = $installer->getManifest()->version;

                        if($state == 'install')
                        {
                            $buffer .= $this->printInstall($ext->name, $version);
                        }elseif($state == 'update')
                        {
                            $buffer .= $this->printUpdate($ext->name, $version);
                        }
                    }

                    // We'll publish the plugin if it set to enable
                    if( $type == 'plugin' AND $ext->attributes()->enabled == 'true')
                    {
                        $query = array();
                        $query[] = $db->qn('element').' = '.$db->q(str_replace('plg_','',$ext->folder));
                        $query[] = $db->qn('folder').' = '.$db->q($ext->attributes()->group);

                        $sql = $db->getQuery(true)
                            ->update('#__extensions')
                            ->where($query)
                            ->set('enabled = 1');

                        $db->setQuery($sql)
                            ->execute();
                    }

                }
            }
        }

    // Closing HTML
        ob_start();
    ?>
        </ul>
    </div>
    <?php
        $buffer .= ob_get_clean();
        // Return stuff
        echo $this->getCSS();
        echo $buffer;
    }

    public function printInstall($name, $version)
    {
        ob_start();
        ?>
    <li class="xei-success">
        <span class="icon"></span><?php echo $name;?> installed successfully <span class="version">v <?php echo $version?></span>
    </li>
    <?php
            $out = ob_get_clean();
        return $out;
    }

    public function printUpdate($name, $version)
    {
        ob_start();
        ?>
    <li class="xei-update">
        <span class="icon"></span><?php echo $name;?> updated successfully <span class="version">v <?php echo $version?></span>
    </li>
    <?php
            $out = ob_get_clean();
        return $out;
    }

    function getCSS()
    {
        $css = "
            #xei-logo {background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAUoAAABkCAYAAADg+Hn3AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyRpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoTWFjaW50b3NoKSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDo1RDA1RjJFRjFDMTgxMUUyOEZFMEE1NTdENDIzRUM5OCIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDo1RDA1RjJGMDFDMTgxMUUyOEZFMEE1NTdENDIzRUM5OCI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjVEMDVGMkVEMUMxODExRTI4RkUwQTU1N0Q0MjNFQzk4IiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOjVEMDVGMkVFMUMxODExRTI4RkUwQTU1N0Q0MjNFQzk4Ii8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+mBLzXgAANZJJREFUeNrsXQeA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) no-repeat 50% 0;
                        padding-top: 110px;}
            #xei-status li .icon { background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABsAAAAbCAYAAACN1PRVAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyRpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoTWFjaW50b3NoKSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDo1RDA1RjJGMzFDMTgxMUUyOEZFMEE1NTdENDIzRUM5OCIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDo1RDA1RjJGNDFDMTgxMUUyOEZFMEE1NTdENDIzRUM5OCI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjVEMDVGMkYxMUMxODExRTI4RkUwQTU1N0Q0MjNFQzk4IiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOjVEMDVGMkYyMUMxODExRTI4RkUwQTU1N0Q0MjNFQzk4Ii8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+EwvTaQAAAaJJREFUeNrElr1KxEAUhV1xNdV2tm6pjRb+4CO4uJbmAWzUThAFYUEfQbCycI3sM6iFhW+wC1kVESsbIYKStVIsxjMyCddhZjKZBD3wsWRy557M3fmrMMaG/krDFjFjoAGOQQhiwMRvKNobIs4sPjINI2AdPDM7RWAbjOpy6owmQcjcxPtN2ZotgFdWTG9gMcuMf1HMylEsj5AaVQuUTqe+yPvjQWfjFpgpMLMPwRL4JG3TIu+v2eiJ2eSqNqnQPPiSZqlHy7hawChQTLJZ8EhifFrGpmPpOmBN0d4D9+R5mZaxr/nqB9AEl4p3Z4YN4USKvaFljDVG4yTBFXnXMRi1NcsgNZPFR1JTJOIVuM4xIqrUbCC9ONAkqxqMTg1GA2p2qwjYMyS2KR3VHTULNEGtEozS5ZF08A2BJsPAci361CxrB2nl/I+oXuQdhLOT0Wk/Z+kS7bru+tzwyHXX/7fzjO7aUUGjSJz4VneQCdB1NOqBep4LT3K72sxxu+JxG6KfMmfF4pLqgRVxDM2BOqiBd/AEuuACnIMPUyIbs9L0LcAAvg0RArBcnq4AAAAASUVORK5CYII=) no-repeat 0 0;
            float:left; width: 27px; height: 27px; margin-right: 10px;
            }
            #xei-status {list-style:none; padding:20px 20px 5px; width: 600px; margin: 10px auto; font-size: 16px; color: #fff; box-shadow: 0 0 10px #ddd inset; text-shadow: 1px 1px 1px #888;}
            #xei-status li{padding: 6px 20px 6px 10px; margin-bottom: 15px; line-height: 28px;}
            #xei-status li.xei-success {background-color: #6bbf3d;}
            #xei-status li.xei-update {background-color: #2a92da;}
            #xei-status span.version{float: right; background: #fff; padding: 0 5px; color: #333; text-shadow: 1px 1px 0 #fff; border-radius: 3px; font-size: 12px; line-height: 20px;margin-top: 4px;box-shadow: 0 0 2px #000 inset; font-weight: bold;}
        ";

        return '<style>' . $css . '</style>';
    }
}
