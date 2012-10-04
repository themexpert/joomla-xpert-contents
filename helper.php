<?php
/**
 * @package Xpert Contents
 * @version 1.2
 * @author ThemeXpert http://www.themexpert.com
 * @copyright Copyright (C) 2009 - 2011 ThemeXpert
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 *
 */

// no direct access
defined( '_JEXEC' ) or die('Restricted access');

abstract class modXpertContentsHelper{
    
    public static function getLists(&$params){

        $total_items = (int) $params->get('primary_item_count', 4) + (int) $params->get('sec_item_count',4) ;
        $content_source = $params->get('content_source','joomla');

        switch($content_source){
            case 'joomla':
                $lists = self::_getJoomlaItems($params,$total_items);
                break;
            case 'k2':
                $lists = self::_getK2Items($params, $total_items);
                break;
            case 'easyblog':
                $lists = self::_getEasyBlogItems($params, $total_items);
                break;
        }
        //echo "<pre>";print_r($lists);echo "</pre>";

        return $lists;
    }
    
    public static function loadScripts($params, $module_id){
        $doc =& JFactory::getDocument();

        //load jquery first
        self::loadJquery($params);

        //primary settings
        $primary_scrollable = $params->get('primary_scrollable');

        //seconday settings
        $sec_scrollable = $params->get('sec_scrollable');

        $navigator = '.navigator({indexed: true})';

        $js = '';

        $js = "\njQuery(document).ready(function(){\n";
        if($primary_scrollable) $js .= "jQuery('#{$module_id}-primary').scrollable(){$navigator};";
        if($sec_scrollable) $js .= "\njQuery('#{$module_id}-sec').scrollable(){$navigator};";

        $js .= "\n});\n";

        $doc->addScriptDeclaration($js);

        if(!defined('XPERT_CONTENTS')){
            //xpert contents js file
            $doc->addScript(JURI::root(true).'/modules/mod_xpertcontents/interface/js/xpertcontents.js');
            define('XPERT_CONTENTS',1);
        }
    }

    public static function loadJquery($params){
        $doc =& JFactory::getDocument();    //document object
        $app =& JFactory::getApplication(); //application object

        static $jqLoaded;

        if ($jqLoaded) {
            return;
        }

        if($params->get('load_jquery') AND !$app->get('jQuery')){
            //get the cdn
            $cdn = $params->get('jquery_source');
            switch ($cdn){
                case 'google_cdn':
                    $file = 'https://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js';
                    break;
                case 'local':
                    $file = JURI::root(true).'/modules/mod_xpertcontents/interface/js/jquery-1.6.1.min.js';
                    break;
            }
            $app->set('jQuery','1.6');
            $doc->addScript($file);
            $doc->addScriptDeclaration("jQuery.noConflict();");
            $jqLoaded = TRUE;
        }

    }

    public static function loadStyles($params,$module_id){
        $app                = &JApplication::getInstance('site', array(), 'J');
        $template           = $app->getTemplate();
        $doc                = & JFactory::getDocument();

        static $isStyleLoaded;

        if($isStyleLoaded) return;

        if (file_exists(JPATH_SITE.DS.'templates'.DS.$template.'/css/xpertcontents.css')) {
           $doc->addStyleSheet(JURI::root(true).'/templates/'.$template.'/css/xpertcontents.css');
        }    
        else {
            $doc->addStyleSheet(JURI::root(true).'/modules/mod_xpertcontents/interface/css/xpertcontents.css');
            $isStyleLoaded = TRUE;
        }
        
    }

    /***
     *
     * Get only large image from k2 image source, if failed then search for introtext.
     *
     * @params $id
     * @params $title
     * @params $text
     * @return $image_path
     *
     **/
    public static function getK2Images($id, $title, $text){
        if (file_exists(JPATH_SITE.DS.'media'.DS.'k2'.DS.'items'.DS.'cache'.DS.md5("Image".$id).'_L.jpg')) {
            $image_path = 'media/k2/items/cache/'.md5("Image".$id).'_L.jpg';
            $image_path = JURI::Root(true).'/'.$image_path;
            return $image_path;
        }
        elseif($text != NULL){

            return self::getImage($text);
        }
        else{
            echo "Image not found for article $title \n";
        }

    }

    /***
     *
     * Get image from given text.
     *
     * @params $text
     * @return $image path
     *
     */
    public static function getImage($text)
    {
        if(preg_match("/\<img.+?src=\"(.+?)\".+?\>/", $text, $matches)){
            $image_path='';

            $paths = array();

            if (isset($matches[1])) {
                $image_path = $matches[1];
                //$image_path = JURI::Root(True)."/".$image_path;
            }
            return $image_path;
        }
        return false;

    }

    /***
     *
     * Stripe unnecessary html tags from given text and trim according to given limit.
     *
     * @params $text
     * @params $num_character
     * @return $text
     *
     */
    public static function prepareIntroText ($text, $num_character){
        $text = strip_tags($text,"</strong></em></a></span></p>");

        if(strlen($text)>$num_character && $num_character!=0){
            //$text1 = substr ($text, 0, $num_character) . "..";
            $text1 = JHtml::_('string.truncate', $text, $num_character, true, false )  . "..";
            return $text1;
        }
        else return $text;
    }

    private static function _getJoomlaItems($params,$total_items){
        require_once JPATH_SITE.'/components/com_content/helpers/route.php';
        jimport('joomla.application.component.model');
        JModel::addIncludePath(JPATH_SITE.'/components/com_content/models');

        // Get the dbo
        $db = JFactory::getDbo();

        // Get an instance of the generic articles model
        $model = JModel::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

        // Set application parameters in model
        $app = JFactory::getApplication();
        $appParams = $app->getParams();
        $model->setState('params', $appParams);

        // Set the filters based on the module params
        $model->setState('list.start', 0);
        $model->setState('list.limit', $total_items);
        $model->setState('filter.published', 1);

        // Access filter
        $access = !JComponentHelper::getParams('com_content')->get('show_noauth');
        $authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
        $model->setState('filter.access', $access);

        // Category filter
        $model->setState('filter.category_id', $params->get('catid', array()));

        // User filter
        $userId = JFactory::getUser()->get('id');
        switch ($params->get('user_id'))
        {
                case 'by_me':
                        $model->setState('filter.author_id', (int) $userId);
                        break;
                case 'not_me':
                        $model->setState('filter.author_id', $userId);
                        $model->setState('filter.author_id.include', false);
                        break;

                case '0':
                        break;

                default:
                        $model->setState('filter.author_id', (int) $params->get('user_id'));
                        break;
        }
        // Filter by language
        $model->setState('filter.language',$app->getLanguageFilter());

        //  Featured switch
        switch ($params->get('show_featured'))
        {
                case '1':
                        $model->setState('filter.featured', 'only');
                        break;
                case '0':
                        $model->setState('filter.featured', 'hide');
                        break;
                default:
                        $model->setState('filter.featured', 'show');
                        break;
        }

        // Set ordering
        $order_map = array(
                'm_dsc' => 'a.modified DESC, a.created',
                'mc_dsc' => 'CASE WHEN (a.modified = '.$db->quote($db->getNullDate()).') THEN a.created ELSE a.modified END',
                'c_dsc' => 'a.created',
                'p_dsc' => 'a.publish_up',
        );
        $ordering = JArrayHelper::getValue($order_map, $params->get('ordering'), 'a.publish_up');
        $dir = 'DESC';

        $model->setState('list.ordering', $ordering);
        $model->setState('list.direction', $dir);
        $items = $model->getItems();

       foreach ($items as &$item) {
            $item->slug = $item->id.':'.$item->alias;
            $item->catslug = $item->catid.':'.$item->category_alias;

            if ($access || in_array($item->access, $authorised))
            {
                    // We know that user has the privilege to view the article
                    $item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catslug));
            }
            else {
                    $item->link = JRoute::_('index.php?option=com_user&view=login');
            }

            // category name & link
            $item->catname = $item->category_title;
            $item->catlink = JRoute::_(ContentHelperRoute::getCategoryRoute($item->catid));

            $item->introtext = JHtml::_('content.prepare', $item->introtext);

            //Take advantage from joomla default Intro image system
            $images = json_decode($item->images);

            if( isset($images->image_intro) and !empty($images->image_intro))
            {
               $item->image = $images->image_intro;
            }else{
               //get image from article intro text
               $item->image = self::getImage($item->introtext);
            }
            //$item->image = self::getImage($item->introtext);

        }
        //echo "<pre>"; print_r($items); echo "</pre>";
        return $items;
    }

    private static function _getK2Items($params,$total_items){
        require_once(JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'route.php');
        require_once(JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'helpers'.DS.'utilities.php');

        jimport('joomla.filesystem.file');
		$mainframe = &JFactory::getApplication();
		$limit = $total_items;
		$cid = $params->get('category_id', NULL);
		$ordering = $params->get('itemsOrdering','');
		$componentParams = &JComponentHelper::getParams('com_k2');
		$limitstart = JRequest::getInt('limitstart');

		$user = &JFactory::getUser();
		$aid = $user->get('aid');
		$db = &JFactory::getDBO();

		$jnow = &JFactory::getDate();
		$now = $jnow->toMySQL();
		$nullDate = $db->getNullDate();

        $query = "SELECT i.*, c.name AS categoryname,c.id AS categoryid, c.alias AS categoryalias, c.params AS categoryparams";

        if ($ordering == 'best')
        $query .= ", (r.rating_sum/r.rating_count) AS rating";

        if ($ordering == 'comments')
        $query .= ", COUNT(comments.id) AS numOfComments";

        $query .= " FROM #__k2_items as i LEFT JOIN #__k2_categories c ON c.id = i.catid";

        if ($ordering == 'best')
        $query .= " LEFT JOIN #__k2_rating r ON r.itemID = i.id";

        if ($ordering == 'comments')
        $query .= " LEFT JOIN #__k2_comments comments ON comments.itemID = i.id";

        if(K2_JVERSION=='16'){
            $query .= " WHERE i.published = 1 AND i.access IN(".implode(',', $user->authorisedLevels()).") AND i.trash = 0 AND c.published = 1 AND c.access IN(".implode(',', $user->authorisedLevels()).")  AND c.trash = 0";
        }
        else {
            $query .= " WHERE i.published = 1 AND i.access <= {$aid} AND i.trash = 0 AND c.published = 1 AND c.access <= {$aid} AND c.trash = 0";
        }

        $query .= " AND ( i.publish_up = ".$db->Quote($nullDate)." OR i.publish_up <= ".$db->Quote($now)." )";
        $query .= " AND ( i.publish_down = ".$db->Quote($nullDate)." OR i.publish_down >= ".$db->Quote($now)." )";


        if ($params->get('catfilter')) {
            if (!is_null($cid)) {
                if (is_array($cid)) {
                    if ($params->get('getChildren')) {
                        require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'models'.DS.'itemlist.php');
                        $categories = K2ModelItemlist::getCategoryTree($cid);
                        $sql = @implode(',', $categories);
                        $query .= " AND i.catid IN ({$sql})";

                    } else {
                        JArrayHelper::toInteger($cid);
                        $query .= " AND i.catid IN(".implode(',', $cid).")";
                    }

                } else {
                    if ($params->get('getChildren')) {
                        require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'models'.DS.'itemlist.php');
                        $categories = K2ModelItemlist::getCategoryTree($cid);
                        $sql = @implode(',', $categories);
                        $query .= " AND i.catid IN ({$sql})";
                    } else {
                        $query .= " AND i.catid=".(int)$cid;
                    }

                }
            }
        }

        if ($params->get('FeaturedItems') == '0')
        $query .= " AND i.featured != 1";

        if ($params->get('FeaturedItems') == '2')
        $query .= " AND i.featured = 1";

        if ($ordering == 'comments')
        $query .= " AND comments.published = 1";

        if(K2_JVERSION=='16'){
            if($mainframe->getLanguageFilter()) {
                $languageTag = JFactory::getLanguage()->getTag();
                $query .= " AND c.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').") AND i.language IN (".$db->Quote($languageTag).", ".$db->Quote('*').")";
            }
        }

        switch ($ordering) {

            case 'date':
                $orderby = 'i.created ASC';
                break;

            case 'rdate':
                $orderby = 'i.created DESC';
                break;

            case 'alpha':
                $orderby = 'i.title';
                break;

            case 'ralpha':
                $orderby = 'i.title DESC';
                break;

            case 'order':
                if ($params->get('FeaturedItems') == '2')
                $orderby = 'i.featured_ordering';
                else
                $orderby = 'i.ordering';
                break;

            case 'rorder':
                if ($params->get('FeaturedItems') == '2')
                $orderby = 'i.featured_ordering DESC';
                else
                $orderby = 'i.ordering DESC';
                break;

            case 'hits':
                if ($params->get('popularityRange')){
                    $datenow = &JFactory::getDate();
                    $date = $datenow->toMySQL();
                    $query.=" AND i.created > DATE_SUB('{$date}',INTERVAL ".$params->get('popularityRange')." DAY) ";
                }
                $orderby = 'i.hits DESC';
                break;

            case 'rand':
                $orderby = 'RAND()';
                break;

            case 'best':
                $orderby = 'rating DESC';
                break;

            case 'comments':
                if ($params->get('popularityRange')){
                    $datenow = &JFactory::getDate();
                    $date = $datenow->toMySQL();
                    $query.=" AND i.created > DATE_SUB('{$date}',INTERVAL ".$params->get('popularityRange')." DAY) ";
                }
                $query.=" GROUP BY i.id ";
                $orderby = 'numOfComments DESC';
                break;

            case 'modified':
                $orderby = 'i.modified DESC';
                break;

            default:
                $orderby = 'i.id DESC';
                break;
        }

        $query .= " ORDER BY ".$orderby;
        $db->setQuery($query, 0, $limit);
        $items = $db->loadObjectList();

        require_once (JPATH_SITE.DS.'components'.DS.'com_k2'.DS.'models'.DS.'item.php');
		$model = new K2ModelItem;

		if (count($items)) {

			foreach ($items as $item) {

				//Clean title
				$item->title = JFilterOutput::ampReplace($item->title);

				//Read more link
				$item->link = urldecode(JRoute::_(K2HelperRoute::getItemRoute($item->id.':'.urlencode($item->alias), $item->catid.':'.urlencode($item->categoryalias))));

                //category name & link
                $item->catname = $item->categoryname;
                $item->catlink = urldecode(JRoute::_(K2HelperRoute::getCategoryRoute($item->catid)));

                //Item Image
                $item->image = self::getK2Images($item->id,$item->title,$item->introtext);

            }
        }

        return $items;
    }

    private static function _getEasyBlogItems($params,$total_items){

        $helper = JPATH_ROOT.DS.'components'.DS.'com_easyblog'.DS.'helpers'.DS.'helper.php';

        jimport( 'joomla.filesystem.file' );

        if( !JFile::exists( $helper ) ) return;

        require_once ($helper);
        require_once (JPATH_ROOT.DS.'components'.DS.'com_easyblog'.DS.'helpers'.DS.'date.php');
        require_once (JPATH_ROOT.DS.'administrator'.DS.'components'.DS.'com_easyblog'.DS.'tables'.DS.'profile.php');

        $catid = $params->get('ezcatid','');
        $user 			=& JFactory::getUser();
        $category		=& JTable::getInstance( 'ECategory', 'Table' );
        $category->load($catid);

        if($category->private && $user->id == 0){
            echo JText::_('MOD_XPERTCONTENTS_IS_CURRENTLY_SET_TO_PRIVATE');
            return;
        }

        if( !class_exists( 'EasyBlogModelBlog' ) ){
			jimport( 'joomla.application.component.model' );
			JLoader::import( 'blog' , EBLOG_ROOT . DS . 'models' );
		}

		$model = JModel::getInstance( 'Blog' , 'EasyBlogModel' );
		$posts = $model->getBlogsBy('category', $catid, 'latest' , $total_items , EBLOG_FILTER_PUBLISHED, null, false );
        $config =& EasyBlogHelper::getConfig();

        if(! empty($posts)){
            for($i = 0; $i < count($posts); $i++){
                $row    	=& $posts[$i];
                $author 	=& JTable::getInstance( 'Profile', 'Table' );
                $row->author		= $author->load( $row->created_by );
                $row->commentCount 	= EasyBlogHelper::getCommentCount($row->id);
                $row->date			= EasyBlogDateHelper::toFormat( JFactory::getDate( $row->created ) , $config->get('layout_dateformat', '%A, %d %B %Y') );

                $requireVerification = false;
                if($config->get('main_password_protect', true) && !empty($row->blogpassword))
                {
                    $row->title	= JText::sprintf('COM_EASYBLOG_PASSWORD_PROTECTED_BLOG_TITLE', $row->title);
                    $requireVerification = true;
                }

                if($requireVerification && !EasyBlogHelper::verifyBlogPassword($row->blogpassword, $row->id))
                {
                    $theme = new CodeThemes();
                    $theme->set('id', $row->id);
                    $theme->set('return', base64_encode(EasyBlogRouter::_('index.php?option=com_easyblog&view=entry&id='.$row->id)));
                    $row->intro			= $theme->fetch( 'blog.protected.php' );
                    $row->content		= $row->intro;
                    $row->showRating	= false;
                    $row->protect		= true;
                }
                else
                {
                    $row->introtext		= EasyBlogHelper::getHelper( 'Videos' )->strip( $row->content );
                    $row->image         = self::getImage($row->content);
                    $row->showRating	= true;
                    $row->protect		= false;
                    $row->link = EasyBlogRouter::_('index.php?option=com_easyblog&view=entry&id=' . $row->id );
                }
            }//end foreach
        }

        return $posts;
    }
    
    public static function getResizedImage($path, $width, $height, $params){
        
        jimport('joomla.filesystem.folder');
        jimport('joomla.filesystem.file');

        if( !file_exists($path) ) return ;

        include_once 'libs/xpertthumb.php';
        $xt = new XpertThumb($path);

        $image_info = pathinfo($path);
        $image_size = getimagesize($path);

        $cache_path = JPATH_ROOT. '/cache/mod_xpertcontents';

        // create cache folder if not exist
        JFolder::create($cache_path, 0755);

        $name = md5( $image_info['basename'].$width.$height).'_resized';

        $newpath = $cache_path . '/' . $name . '.' . $image_info['extension'];

        $image_uri = JURI::base(true). '/cache/mod_xpertcontents/'  . $name . '.' . $image_info['extension'];

        if(!file_exists($newpath))
        {
            $xt->resize( $width, $height , true, 1 )
                ->toFile( $newpath );
        }
        return $image_uri;
    }

}