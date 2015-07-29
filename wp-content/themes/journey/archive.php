<?php
	display_header("journal");
?>
<div id="contentContainer">
	<table border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td valign="top" class="left-column">
				<div class="left-container">
		<?php if (have_posts()) : ?>

		 <?php $post = $posts[0]; // Hack. Set $post so that the_date() works. ?>
<?php /* If this is a category archive */ if (is_category()) { ?>
		<h1 class="pageH2">Archive for the &#8216;<?php echo single_cat_title(); ?>&#8217; Category</h1>

 	  <?php /* If this is a daily archive */ } elseif (is_day()) { ?>
		<h1 class="pageH2">Archive for <?php the_time('j F, Y'); ?></h1>

	 <?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
		<h1 class="pageH2">Archive for <?php the_time('F, Y'); ?></h1>

		<?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
		<h1 class="pageH2">Archive for <?php the_time('Y'); ?></h1>

	  <?php /* If this is an author archive */ } elseif (is_author()) { ?>
		<h1 class="pageH2">Author Archive</h1>

		<?php /* If this is a paged archive */ } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
		<h1 class="pageH2">Blog Archives</h1>

		<?php } ?>

		<?php while (have_posts()) : the_post(); ?>
		<div class="archive-entry">
			<p class="date"><span class="dateDay"><?php the_time('j') ?></span>&nbsp;<span class="dateMonth"><?php the_time('F') ?></span>&nbsp;<span class="dateYear"><?php the_time('Y') ?></span></p>
	  		<h3><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>"><?php the_title(); ?></a></h3>
	  		&nbsp;<?php the_excerpt(); ?>
			<p class="textbkg"><span>Posted by <?php the_author() ?> under: <?php the_category('; ') ?>.</span></p>
			<p class="comments"><?php comments_popup_link('0', '1', '%'); ?><span>&nbsp;</span></p>
		</div>
		<?php endwhile; ?>

			  <div class="pageNav">
			    <?php posts_nav_link('', '<strong class="pageNext"><span>Next</span></strong>', '<strong class="pagePrev"><span>Previous</span></strong>'); ?>
			  </div>

	<?php else : ?>
		      <h2>Oops, nothing found</h2>
		      <p class="center">Sorry, but you are looking for something that isn't here.</p>
		      <?php include (TEMPLATEPATH . "/searchform.php"); ?>
	<?php endif; ?>
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
