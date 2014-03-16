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
defined( '_JEXEC' ) or die('Restricted access');
?>
<!-- Xpert Contents Module ##VERSION## start | Layout-Default -->
<div id="<?php echo $module_id; ?>" class="xco-wrap xco-<?php echo $layout;?>">
	<!-- Primary Column Start -->
	<div class="pri-col">
		<div class="inner">
			<?php for($i=0; $i<$primary_count; $i++):?>
			<div class="item item-<?php echo $i+1; ?>">
				<a href="<?php echo $items[$i]->link; ?>">
					<div class="image">
						<img src="<?php echo $items[$i]->image;?>" alt="<?php echo $items[$i]->title;?>">
					</div>

					<h3 class="heading"><?php echo $items[$i]->title;?></h3>	
				</a>

				<?php /*Meta data*/ if( $params->get('primary_category') OR $params->get('primary_date')  ): ?>
				<!-- Meta data -->
				<div class="meta">
					<?php if( $params->get('primary_category') ) : ?>
						<span class="cat"><?php echo $items[$i]->category_title; ?></span>
					<?php endif; ?>

					<?php if( $params->get('primary_date') ): ?>
						<span class="date"><?php echo $items[$i]->date; ?></span>
					<?php endif; ?>
				</div>	
				<?php endif; ?>

				<?php /*Intro*/ if($params->get('primary_intro', 1)): ?>

				<?php
					$filter_by = $params->get('primary_intro_limit_type');
		            // Trim intro text based on filter type
		            if( $filter_by == 'words' )
		            {
		                $introtext = XEFUtility::wordLimit($items[$i]->introtext, $params->get('primary_intro_limit',10) );

		            }elseif($filter_by == 'chars')
		            {
		                $introtext = XEFUtility::characterLimit($items[$i]->introtext, $params->get('primary_intro_limit',30) );
		            }else{
		            	$introtext = $items[$i]->introtext;
		            }
				?>

				<div class="intro"><?php echo $introtext; ?></div>
				<?php endif; ?>

				<?php if( $params->get('primary_readmore', 1) ):?>
					<a class="xco-btn" href="<?php echo $items[$i]->link; ?>"><?php echo $params->get('primary_readmore_text'); ?></a>
				<?php endif; ?>
			</div>	
			<?php endfor;?>
		</div>
	</div>
	<!-- Primary Column End -->

	<?php if($secondary_show): ?>
	<?php $sec_count += $i; ?>
	<!-- Secondary Column Start -->
		<div class="sec-col">
			<div class="inner">
				<?php for( $i; $i<$sec_count; $i++ ): ?>
				<div class="item item-<?php echo $i+1; ?> clearfix">
					<a href="<?php echo $items[$i]->link; ?>">
						<h3 class="heading"><?php echo $items[$i]->title;?></h3>	
					</a>
					<?php /*Meta data*/ if( $params->get('sec_date') ) :?>
					<!-- Meta data -->
					<div class="meta">
						<?php if( $params->get('sec_date') ): ?>
							<span class="date"><?php echo $items[$i]->date; ?></span>
						<?php endif; ?>
					</div>	
					<?php endif; ?>

					<?php /*Intro*/ if($params->get('sec_intro', 1)): ?>

					<?php
						$filter_by = $params->get('sec_intro_limit_type');
			            // Trim intro text based on filter type
			            if( $filter_by == 'words' )
			            {
			                $introtext = XEFUtility::wordLimit($items[$i]->introtext, $params->get('sec_intro_limit',10) );

			            }elseif($filter_by == 'chars')
			            {
			                $introtext = XEFUtility::characterLimit($items[$i]->introtext, $params->get('sec_intro_limit',30) );
			            }else{
			            	$introtext = $items[$i]->introtext;
			            }
					?>

					<div class="intro"><?php echo $introtext; ?></div>
					<?php endif; ?>

				</div>
				<?php endfor; ?>
			</div>
		</div>
	<!-- Secondary Column End -->
	<?php endif; ?>

</div>
<!-- Xpert Contents Module ##VERSION## end | Layout - Default -->