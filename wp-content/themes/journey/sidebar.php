<div class="cols01">
  <?php if ( !function_exists('dynamic_sidebar') || !dynamic_sidebar('Sidebar 1') ) : ?>
  <h3>Browse</h3>
  <select name="archivemenu" onchange="document.location.href=this.options[this.selectedIndex].value;">
    <option value="">Monthly Archives</option>
    <?php get_archives('monthly','','option','','',''); ?>
  </select>
  <h3>Calendar</h3>
  <?php get_calendar(); ?>
  <h3>Categories</h3>
  <ul>
    <?php wp_list_cats('sort_column=name&hide_empty=0'); ?>
  </ul>
      <h3>Links</h3>
	    <ul>
		  <?php 
		  wp_list_bookmarks('title_before=<strong>&title_after=</strong>'); ?>
		  <!-- if using older versions of wordpress (below 2.1), replace the line above with:
		  get_links_list(); ?>
		   -->
		</ul>
<?php endif; ?>
</div>

</div>
</div>
</div>
