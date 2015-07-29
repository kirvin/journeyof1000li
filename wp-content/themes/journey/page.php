<?php
	while (have_posts()) {
		the_post();
		$customVars = get_post_custom();
		display_header($customVars["mainNav"][0]);
	}
?>

<div id="contentContainer">
	<table border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td valign="top" class="left-column">
				<div class="left-container">
					<?php if (have_posts()) { ?>
					<?php 	while (have_posts()) { ?>
					<?php 		the_post() ?>
									<h1><?php the_title(); ?></h1>
									<div class="page-content">
										<div class="post-content">
											<?php
												the_content();
											?>
										</div>
										<br class="clear" />
									</div>
					<?php 	} ?>
							<div class="pageNav">
							    <?php posts_nav_link('', '<strong class="pageNext"><span>Next</span></strong>', '<strong class="pagePrev"><span>Previous</span></strong>'); ?>
							</div>
					<?php } else { ?>
							Looking for something?  Try searching our site--if you can't find what you were looking for, <a href="/contact">drop us a line</a>.
							<?php include (TEMPLATEPATH . "/searchform.php"); ?>
					<?php }?>
				</div>
			</td>
			<td class="right-column" valign="top">
				<div class="sidebar">
					<?php
						// FIXME this needs to be a plugin
						follow_widget();
						get_sidebar();
					?>
				</div>			
			</td>
		</tr>
	</table>
</div>

<?php get_footer(); ?>
