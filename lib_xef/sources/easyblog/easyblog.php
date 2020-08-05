<?php
/**
 *  @package ThemeXpert Extension Framework (XEF)
 *  @copyright Copyright (c)2010-2012 ThemeXpert.com
 *  @license GNU General Public License version 3, or later
 **/

// Protect from unauthorized access
defined('_JEXEC') or die();
jimport('joomla.system.file');
jimport('joomla.system.folder');

// Require XEF helper class
require_once JPATH_LIBRARIES . '/xef/xef.php';

// if( file_exists( JPATH_ROOT . '/components/com_easyblog/constants.php' ) )
if( file_exists( JPATH_ROOT . '/components/com_easyblog/includes/easyblog.php' ) )
{
    // Load Easyblog helper and router class
    require_once JPATH_SITE . '/components/com_easyblog/helpers/helper.php';
    require_once JPATH_SITE . '/components/com_easyblog/helpers/date.php';
    require_once JPATH_SITE . '/administrator/components/com_easyblog/tables/profile.php';

    class XEFSourceEasyblog extends XEFHelper
    {
        public function getItems()
        {
            $catid      = ($this->get('ezb_catfilter')) ? $this->get('ezb_catid',NULL) : '';
            $ordering   = $this->get('ezb_ordering','latest');
            $user 	    = JFactory::getUser();
            $category	= EasyBlogHelper::getTable( 'Category', 'Table' );

            $category->load($catid);

            if($category->private && $user->id == 0){
                echo JText::_('This category is set to private');
                return;
            }

            if( !class_exists( 'EasyBlogModelBlog' ) ){
                jimport( 'joomla.application.component.model' );
                JLoader::import( 'blog' , EBLOG_ROOT . '/' . 'models' );
            }

            $model = EasyBlogHelper::getModel( 'Blog' );

            if( $this->get( 'ezfeatured') )
            {
                $items = $model->getFeaturedBlog( $catid , $this->get('count') );
            }
            else
            {
                $items = $model->getBlogsBy('category', $catid, $ordering , $this->get('count') , EBLOG_FILTER_PUBLISHED, null, false );
            }

            $config = EasyBlogHelper::getConfig();

            if(! empty($items)){
                for($i = 0; $i < count($items); $i++)
                {
                    $data 	=& $items[$i];
                    $row 	= EasyBlogHelper::getTable( 'Blog', 'Table' );
                    $row->bind( $data );

                    //$row    	=& $items[$i];
                    $author 	= EasyBlogHelper::getTable( 'Profile', 'Table' );

                    $row->author		= $author->load( $row->created_by );
                    $row->commentCount 	= EasyBlogHelper::getCommentCount($row->id);


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
                        $row->introtext		= $theme->fetch( 'blog.protected.php' );
                        $row->content		= $row->introtext;
                        $row->showRating	= false;
                        $row->protect		= true;
                    }
                    else
                    {
                        $row->introtext		= EasyBlogHelper::getHelper( 'Videos' )->strip( $row->content );
                        $row->showRating	= true;
                        $row->protect		= false;

                    }
                }//end foreach
            }

    //        XEFUtility::debug($items);
            $items = $this->prepareItems($items);

            return $items;
        }

        public function getLink($item)
        {
            return  EasyBlogRouter::_('index.php?option=com_easyblog&view=entry&id=' . $item->id . $this->getMenuItemId() );
        }

        public function getCategory($item)
        {
            return $item->category;
        }

        public function getCategoryLink($item)
        {
            return EasyBlogRouter::_('index.php?option=com_easyblog&view=categories&layout=listings&id='.$item->category_id . $this->getMenuItemId());
        }

        public function getImage($item)
        {
            if( isset($item->image) AND ($item->image != null) )
            {
                $image = json_decode($item->image);
                return $image->url;
            }
            return ( $item->intro ) ? XEFUtility::getImage($item->intro) : XEFUtility::getImage($item->content);
        }

        public function getDate($item)
        {
            $config = EasyBlogHelper::getConfig();

            return EasyBlogDateHelper::toFormat( JFactory::getDate( $item->created ), $config->get('layout_dateformat', '%A, %d %B %Y') );
        }
    }
}
else
{
    // Load Easyblog helper and router class
    require_once JPATH_ADMINISTRATOR . '/components/com_easyblog/includes/easyblog.php';
    if( !class_exists( 'EasyBlogHelper' ) ){
        class EasyBlogHelper extends EB {}
    }
    
    class XEFSourceEasyblog extends XEFHelper
    {
        public function getItems()
        {
            $catid      = ($this->get('ezb_catfilter')) ? $this->get('ezb_catid',NULL) : '';
            $ordering   = $this->get('ezb_ordering','latest');
            $user 	    = JFactory::getUser();

            $model = EB::model('Blog');
            $items = $model->getBlogsBy('category', $catid, $ordering, $this->get('count'), EBLOG_FILTER_PUBLISHED, null, $respectFrontpage = true, $excludeBlog = false, false, false, true, array(), array(), null, 'listlength', true, array(), array(), $excludeFeatured = $this->get( 'ezfeatured'), array(), array('paginationType' => 'none'));


            $config = EasyBlogHelper::getConfig();

            if(! empty($items)){
                for($i = 0; $i < count($items); $i++)
                {
                    $data 	=& $items[$i];
                    $data->introtext = $data->intro;
                    $data->image = $this->getImage($data);
                    
                }//end foreach
            }

            $items = $this->prepareItems($items);

            return $items;
        }

        public function getLink($item)
        {
            return  EBR::_('index.php?option=com_easyblog&view=entry&id=' . $item->id . $this->getMenuItemId() );
        }

        public function getCategory($item)
        {
            // Load up the category table
            $category = EB::table('Category');
            $category->load($item->category_id);

            return $item->category_title = $category->title;
        }

        public function getCategoryLink($item)
        {
            return EBR::_('index.php?option=com_easyblog&view=categories&layout=listings&id='.$item->category_id . $this->getMenuItemId());
        }

        public function getImage($item)
        {
            $post = EB::post();
            $post->load($item->id);

            $cover = false;

            // This is the standard method when authors adds a post cover on the post
            if ($post->hasImage()) {
                $cover = $post->getImage($this->params->get('photo_size', 'medium'));
            }

            // Get the first image to be used as the post cover
            if (!$post->hasImage() && $this->params->get('photo_legacy', true)) {
                $cover = $post->getContentImage();
            }

            // If we still cannot get the cover, determines if we should be showing a place holder
            if (!$cover && $this->params->get('show_photo_placeholder', false)) {
                $cover = $post->getImage($this->params->get('photo_size', 'medium'));
            }
            
            $prefix = str_replace(['http:', 'https:'], '', JUri::root());
            $cover = str_replace($prefix, '', $cover);            

            return $cover;
        }

        public function getDate($item)
        {

            return JFactory::getDate( $item->created );
        }
    }
}
