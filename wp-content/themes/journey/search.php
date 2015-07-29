<?php get_header(); ?>
  <div id="content">
    <div class="box01">
      <div class="left">
        <div class="cols02">
		  	  <div class="entry">
	<?php if (have_posts()) : ?>

		<h2 class="pageH2">Search Results</h2>

		<?php while (have_posts()) : the_post(); ?>

		<div class="archive entry">
				<p class="date"><span class="dateDay"><?php the_time('j') ?></span><span class="dateMonth"><?php the_time('F') ?></span><span class="dateYear"><?php the_time('Y') ?></span></p>
		  		<h3><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>"><?php the_title(); ?></a></h3>
				<p class="textbkg"><span>Posted by <?php the_author() ?> under: <?php the_category('; ') ?>.</span></p>
		      <p class="comments"><?php comments_popup_link('0', '1', '%'); ?><span>&nbsp;</span></p>
				</div>

		<?php endwhile; ?>

			  <div class="pageNav">
			    <?php posts_nav_link('', '<strong class="pageNext"><span>Next</span></strong>', '<strong class="pagePrev"><span>Previous</span></strong>'); ?>
			  </div>

	<?php else : ?>
		      <h2>Oops, nothing found</h2>
		      <p>Sorry, but you are looking for something that isn't here.</p>
		      <?php include (TEMPLATEPATH . "/searchform.php"); ?>
	<?php endif; ?>
			  </div>
		</div>
		<?php get_sidebar(); ?>
        <?php include("related.php"); ?>
		<?php get_footer(); ?>
