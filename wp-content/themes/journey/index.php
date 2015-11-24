<?php
	display_header("journal");
?>

<div id="contentContainer">
	<table border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td valign="top" class="left-column">
				<div class="left-container single-post-content">
					<div class="journal-nav">
						<span class="left">
							<?php previous_post('&lt;&lt;&nbsp;%', 'Previous Post', 'no')?>
						</span>
						<span class="middle">
							<a href="/journal">Journal Home</a>
						</span>
						<span class="right">
							<?php next_post('%&nbsp;&gt;&gt;', 'Next Post', 'no')?>
						</span>
					</div>
				<?php
					posts_nav_link(' &#8212; ', __('&laquo; Newer Posts'), __('Older Posts &raquo;'));
					if (have_posts()) {
						while (have_posts()) {
							the_post();
							echo get_post_layout ($post, $tripStart, true, true, false);
						}
				?>
					<div class="journal-nav">
						<span class="left">
							<?php previous_post('&lt;&lt;&nbsp;%', 'Previous Post', 'no')?>
						</span>
						<span class="middle">
							<a href="/journal">Journal Home</a>
						</span>
						<span class="right">
							<?php next_post('%&nbsp;&gt;&gt;', 'Next Post', 'no')?>
						</span>
					</div>
				<?php
					} else {
				?>
						Looking for something?  Try searching our site--if you can't find what you were looking for, <a href="/contact">drop us a line</a>.
						<?php include (TEMPLATEPATH . "/searchform.php"); ?>
				<?php
					}
				?>
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
