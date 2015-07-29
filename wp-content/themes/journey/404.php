<?php get_header(); ?>

<?php 
	// TODO:  some custom vars used by the template (need to move to configurable properties)
	$tripStart = "5/3/2010";
?>

<div id="contentContainer">
	<table border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td valign="top" class="left-column">
				<div class="left-container">
					<div class="page-content">
						Looking for something?  Try searching our site--if you can't find what you were looking for, <a href="/contact">drop us a line</a>.
						<?php include (TEMPLATEPATH . "/searchform.php"); ?>
					</div>
				</div>
			</td>
			<td class="right-column" valign="top">
				<div class="sidebar">
					<?php
						// FIXME this needs to be a plugin
						follow_widget();
						route_widget(); 
					?>
					<?php get_sidebar(); ?>
				</div>			
			</td>
		</tr>
	</table>
</div>

<?php get_footer(); ?>
