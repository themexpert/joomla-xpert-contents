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
$count = count($lists);
$total_items = (int)$params->get('primary_item_count',4) + (int) $params->get('sec_item_count',4);

if($total_items > $count) $total_items = $count;
elseif($total_items == 0) $total_items = $count;

//Primary settings
$primary_items          = (int) $params->get('primary_item_count',4);
$primary_cols           = (int) $params->get('primary_num_col',2);
if($primary_items > $total_items) $primary_items = $total_items;

if($params->get('primary_item_flow') == 1){
    $primary_item_width = 100/$primary_cols;
}else{
    $primary_item_width = 100;
}
$primary_pans           = ceil($primary_items/$primary_cols);
$primary_width          = $params->get('primary_width',70);
$primary_height          = $params->get('primary_height');
$primary_image_width    = $params->get('primary_image_width');
$primary_image_height   = $params->get('primary_image_height');

//Secondary Settings
$sec_items          = (int) $params->get('sec_item_count',4);
$sec_cols           = (int) $params->get('sec_num_col',1);
if($total_items >= $primary_items) $sec_items = $total_items - $primary_items;

if($params->get('sec_item_flow') == 1){
    $sec_item_width = 100/$sec_cols;
}else{
    $sec_item_width = 100;
}
$sec_pans           = ceil($sec_items/$sec_cols);
$sec_width          = $params->get('sec_width',30);
$sec_height          = $params->get('sec_height');
$sec_image_width    = $params->get('sec_image_width');
$sec_image_height   = $params->get('sec_image_height');

//index
$i = 0;

?>
<?php if($total_items != 0):?>
    <!--Xpert Contents by ThemeXpert- Start (www.themexpert.com)-->
    <div class="xc-container <?php echo $params->get('moduleclass_sfx')?>" width="100%">
        <!-- Primary Content Start -->
        <div class="xc-primary <?php echo ((int)$params->get('primary_item_flow') == 1)? 'xc-cols' : 'xc-rows' ?> <?php echo $params->get('sec_col_position')?>" style="width:<?php echo $primary_width ;?>px;">

            <?php /*Scroller*/ if($params->get('primary_scrollable') AND $params->get('primary_navigation_position') == 'top'):?>
                <!--Navigation Button Start-->
                <div class="xc-navigator <?php echo $params->get('primary_navigation_type'); ?>">
                    <div class="navi"></div>
                    <div class="clear"></div>
                </div>
                <!--Navigation Button End-->
            <?php endif;?>

            <div id="<?php echo $module_id.'-primary'?>" class="xc-scroller" style="height:<?php echo $primary_height;?>px;width:<?php echo $primary_width; ?>px">
                <div class="xc-items">
                    <?php for($pan=0;$pan<$primary_pans;$pan++):?>
                        <div class="xc-pane" style="width:<?php echo $primary_width; ?>px;">
                            <?php for($item=0;$item<$primary_cols;$item++,$i++):?>
                                <?php if($i >= $total_items) break;?>
                                <div class="xc-item-wrap <?php echo ($i%2)? 'even': 'odd';?>" style="width:<?php echo $primary_item_width?>%">
                                    <div class="xc-item">

                                    <?php /*Image position top*/ if($params->get('primary_show_image') AND $params->get('primary_image_position') != 'bottom'):?>
                                        <div class="xc-image <?php echo $params->get('primary_image_position'); ?>">
                                            <?php if($params->get('primary_image_link')):?>
                                                <a href="<?php echo $lists[$i]->link; ?>">
                                            <?php endif;?>
                                                <img src="<?php echo modXpertContentsHelper::getResizedImage($lists[$i]->image, $primary_image_width, $primary_image_height, $params); ?>" alt="<?php echo $lists[$i]->title; ?>" />
                                             <?php if($params->get('primary_image_link')):?>
                                                </a>
                                            <?php endif;?>
                                        </div>
                                    <?php endif;?>

                                    <?php /*Item Title*/ if($params->get('primary_show_tile')):?>
                                        <h3 class="xc-title">
                                            <?php if($params->get('primary_tile_link')):?>
                                                <a href="<?php echo $lists[$i]->link; ?>">
                                            <?php endif;?>
                                                <?php echo $lists[$i]->title;?>
                                            <?php if($params->get('primary_tile_link')):?>
                                                </a>
                                            <?php endif;?>
                                        </h3>
                                    <?php endif;?>

                                    <?php /*Item category*/ if($params->get('primary_show_category')):?>
                                        <p class="xc-cat">
                                            <?php if($params->get('primary_category_link')):?>
                                                <a href="<?php echo $lists[$i]->catlink; ?>">
                                            <?php endif;?>
                                                <?php echo $lists[$i]->catname; ?>
                                            <?php if($params->get('primary_category_link')):?>
                                                </a>
                                            <?php endif;?>
                                        </p>
                                    <?php endif;?>

                                    <?php /*Item date*/ if($params->get('primary_show_date')):?>
                                        <p class="xc-date"><?php echo JHTML::_('date',$lists[$i]->created, JText::_('DATE_FORMAT_LC3')); ?></p>
                                    <?php endif;?>

                                    <?php /*Image position bottom*/ if($params->get('primary_show_image') AND $params->get('primary_image_position') == 'bottom'):?>
                                        <div class="xc-image <?php echo $params->get('primary_image_position'); ?>">
                                            <?php if($params->get('primary_image_link')):?>
                                                <a href="<?php echo $lists[$i]->link; ?>">
                                            <?php endif;?>
                                                <img src="<?php echo modXpertContentsHelper::getResizedImage($lists[$i]->image, $primary_image_width, $primary_image_height, $params); ?>" alt="<?php echo $lists[$i]->title; ?>" />
                                             <?php if($params->get('primary_image_link')):?>
                                                </a>
                                            <?php endif;?>
                                        </div>
                                    <?php endif;?>

                                    <?php /*Item Intro*/ if($params->get('primary_show_intro')):?>
                                        <div class="xc-intro">
                                            <?php echo modXpertContentsHelper::prepareIntroText($lists[$i]->introtext,$params->get('primary_intro_limit',120)); ?>
                                            <?php /*Readmore lik*/ if($params->get('primary_readmore')):?>
                                                <p class="xc-readmore">
                                                    <a href="<?php echo $lists[$i]->link; ?>"><?php echo $params->get('primary_readmore_text', 'Read more'); ?></a>
                                                </p>
                                            <?php endif;?>
                                        </div>
                                    <?php endif;?>

                                    </div>
                                </div>
                            <?php endfor ;?>
                        </div>
                    <?php endfor;?>
                </div>
            </div>
            <?php /*Scroller*/ if($params->get('primary_scrollable') AND $params->get('primary_navigation_position') == 'bottom'):?>
                <!--Navigation Button Bottom Start-->
                 <div class="xc-navigator <?php echo $params->get('primary_navigation_type'); ?>">
                    <div class="navi"></div>
                    <div class="clear"></div>
                </div>
                <!--Navigation Button Bottom End-->
            <?php endif;?>
        </div>
        <!-- Primary Content End -->

        <?php if($params->get('show_secondary',0)) :?>
            <?php if($sec_items != 0):?>
            <!-- Secondary Content Start -->
            <div class="xc-sec <?php echo ((int)$params->get('sec_item_flow') == 1)? 'xc-cols' : 'xc-rows' ?> <?php echo $params->get('sec_col_position')?>" style="width:<?php echo $sec_width; ?>px;">
                <?php /*Scroller*/ if($params->get('sec_scrollable') AND $params->get('sec_navigation_position') == 'top'):?>
                    <!--Navigation Button Start-->
                    <div class="xc-navigator <?php echo $params->get('sec_navigation_type'); ?>">
                        <div class="navi"></div>
                        <div class="clear"></div>
                    </div>
                    <!--Navigation Button Top End-->
                <?php endif;?>

                <div id="<?php echo $module_id.'-sec'; ?>" class="xc-scroller" style="height:<?php echo $sec_height;?>px;width:<?php echo $sec_width; ?>px">
                    <div class="xc-items">
                        <?php for($pan=0;$pan<$sec_pans;$pan++):?>
                            <div class="xc-pane" style="width:<?php echo $sec_width; ?>px;">
                                <?php for($item=0;$item<$sec_cols;$item++,$i++):?>
                                    <?php if($i >= $total_items) break;?>
                                    <div class="xc-item-wrap <?php echo ($i%2)? 'even': 'odd';?>" style="width:<?php echo $sec_item_width?>%">
                                        <div class="xc-item">

                                            <?php /*Image position top*/ if($params->get('sec_show_image') AND $params->get('sec_image_position') != 'bottom'):?>
                                                <div class="xc-image <?php echo $params->get('sec_image_position'); ?>">
                                                    <?php if($params->get('sec_image_link')):?>
                                                        <a href="<?php echo $lists[$i]->link; ?>">
                                                    <?php endif;?>
                                                        <img src="<?php echo modXpertContentsHelper::getResizedImage($lists[$i]->image, $sec_image_width, $sec_image_height, $params); ?>" alt="<?php echo $lists[$i]->title; ?>" />
                                                     <?php if($params->get('sec_image_link')):?>
                                                        </a>
                                                    <?php endif;?>
                                                </div>
                                            <?php endif;?>

                                            <?php /*Item Title*/ if($params->get('sec_show_tile')):?>
                                                <h4 class="xc-title">
                                                    <?php if($params->get('sec_tile_link')):?>
                                                        <a href="<?php echo $lists[$i]->link; ?>">
                                                    <?php endif;?>
                                                        <?php echo $lists[$i]->title;?>
                                                    <?php if($params->get('sec_tile_link')):?>
                                                        </a>
                                                    <?php endif;?>
                                                </h4>
                                            <?php endif;?>

                                            <?php /*Image position bottom*/ if($params->get('sec_show_image') AND $params->get('sec_image_position') == 'bottom'):?>
                                               <div class="xc-image <?php echo $params->get('sec_image_position'); ?>">
                                                    <?php if($params->get('sec_image_link')):?>
                                                        <a href="<?php echo $lists[$i]->link; ?>">
                                                    <?php endif;?>
                                                        <img src="<?php echo modXpertContentsHelper::getResizedImage($lists[$i]->image, $sec_image_width, $sec_image_height, $params); ?>" alt="<?php echo $lists[$i]->title; ?>" />
                                                     <?php if($params->get('sec_image_link')):?>
                                                        </a>
                                                    <?php endif;?>
                                                </div>
                                            <?php endif;?>

                                            <?php /*Item category*/ if($params->get('sec_show_category')):?>
                                                <p class="xc-cat">
                                                    <?php if($params->get('sec_category_link')):?>
                                                        <a href="<?php echo $lists[$i]->catlink; ?>">
                                                    <?php endif;?>
                                                        <?php echo $lists[$i]->catname; ?>
                                                    <?php if($params->get('sec_category_link')):?>
                                                        </a>
                                                    <?php endif;?>
                                                </p>
                                            <?php endif;?>

                                            <?php /*Item date*/ if($params->get('sec_show_date')):?>
                                                <p class="xc-date"><?php echo JHTML::_('date',$lists[$i]->created, JText::_('DATE_FORMAT_LC3')); ?></p>
                                            <?php endif;?>

                                            <?php /*Item Intro*/ if($params->get('sec_show_intro')):?>
                                                <div class="xc-intro">
                                                    <?php echo modXpertContentsHelper::prepareIntroText($lists[$i]->introtext,$params->get('sec_intro_limit',120)); ?>
                                                    <?php /*Readmore lik*/ if($params->get('sec_readmore')):?>
                                                        <p class="xc-readmore">
                                                            <a href="<?php echo $lists[$i]->link; ?>"><?php echo $params->get('sec_readmore_text', 'Read more');?></a>
                                                        </p>
                                                    <?php endif;?>
                                                </div>
                                            <?php endif;?>

                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <?php /*Scroller*/ if($params->get('sec_scrollable') AND $params->get('sec_navigation_position') == 'bottom'):?>
                    <!--Navigation Button Start-->
                    <div class="xc-navigator <?php echo $params->get('sec_navigation_type'); ?>">
                        <div class="navi"></div>
                        <div class="clear"></div>
                    </div>
                    <!--Navigation Button End-->
                <?php endif;?>

            </div>
           <!-- Secondary Content End -->
            <?php endif;?>
        <?php endif;?>
        <div class="clear"></div>

    </div>
    <!--Xpert Contents by ThemeXpert- End-->
<?php endif;?>