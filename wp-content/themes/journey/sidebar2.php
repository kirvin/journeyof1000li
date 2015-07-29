<div id="related">
  <div class="box01">
    <div class="cols01">
      <h3>Recent Posts</h3>
      <ul>
        <?php query_posts('showposts=10');?>
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <li><a href="<?php the_permalink() ?>">
          <?php the_title() ?>
          </a></li>
        <?php endwhile; endif; ?>
      </ul>
    </div>
    <div class="cols01">
      <h3>Recent Comments</h3>
      <ul>
        <?php get_tenrecentcomments(); ?>
      </ul>
    </div>
    <div class="cols01">
      <h3>Subscribe</h3>
      <ul>
        <li><a href="<?php bloginfo('rss2_url'); ?>">Entries (RSS)</a></li>
        <li><a href="<?php bloginfo('comments_rss2_url'); ?>">Comments (RSS)</a></li>
      </ul>
    </div>
  </div>
</div>
