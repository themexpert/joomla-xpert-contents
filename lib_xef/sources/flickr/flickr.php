<?php
/**
 *  @package ThemeXpert Extension Framework (XEF)
 *  @copyright Copyright (c)2010-2012 ThemeXpert.com
 *  @license GNU General Public License version 3, or later
 **/

// Protect from unauthorized access
defined('_JEXEC') or die();

// Require XEF helper class
require_once JPATH_LIBRARIES . '/xef/xef.php';

class XEFSourceFlickr extends XEFHelper
{
	public function getItems()
	{
		jimport('joomla.filesystem.folder');

        $api_key = ($this->get('api_key')) ? $this->get('api_key') : '2a4dbf07ad5341b2b06d60c91d44e918';
        $cache_path = JPATH_ROOT. '/cache/mod_'.$this->module->name . $this->module->id .'/flickr';

        // create cache folder if not exist
        JFolder::create($cache_path, 0755);

        // Include Flickr wrapper
        if( !class_exists('phpFlickr'))
        {
            require_once 'api/phpFlickr.php';    
        }

        // Flickr instance
        $f = new phpFlickr($api_key, '30422154f1627821');

        // Enable file system cache
        $f->enableCache('fs',$cache_path, $this->get('cache_time')); //enable caching

        // Get the use NSID first
        $username = $this->get('flickr_user_name');
        if($username != NULL)
        {
            $person = $f->people_findByUsername($username);
            $nsid = $person['id'];
        }
        // Get photos from people
        $photos = $f->people_getPublicPhotos($nsid, NULL, NULL, $this->get('count'));
        $source = $photos['photos']['photo'];

        $items = array();
        $i = 0;
        if(count($source)>0){
            foreach ($source as $photo)
            {
                $obj = new stdClass();

                $obj->title = $photo['title'];
                $obj->image = $f->buildPhotoURL($photo,'_b');
//                $obj->link = $info['urls']['url'][0]['_content'];
//                $obj->introtext = $info['description'];
//                $obj->date = date('Y.M.d : H:i:s A', $info['dateuploaded']);

                $items[$i] = $obj;
                $i++;
            }
        }

        return $items;
	}

	public function getLink($item)
	{
		return $item->link;

	}
	
	public function getImage($item) 
	{
		return $item->image;
	}

	public function getDate($item)
	{
		return $item->date;
	}

	public function getCategory($item) { return ; }
	public function getCategoryLink($item) { return; }
}
