<?php 
class Journey_Route_Widget extends WP_Widget {

	function Journey_Route_Widget() {
		$widget_ops = array('classname' => 'journey_route', 'description' => __( 'A widget displaying this trip\'s route') );
		$this->WP_Widget('journey_route_widget', __('Journey Route Widget'), $widget_ops);
	}

	function widget ($args, $instance) {
		$title = apply_filters('widget_title', empty($instance['title']) ? '&nbsp;' : $instance['title']);
		$html = '
			<div class="journey-widget" id="routeWidget">
				<h3>
					' . $title . '
					<span class="right">
						<a href="/route">View More&gt;&gt;</a>
					</span>
				</h3>
				<div>
					<a href="/route">
						<img src="' . get_bloginfo('stylesheet_directory') . '/images/map.gif" />
					</a>
				</div>
			</div>
		';
		echo $html;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = strip_tags($instance['title']);
		echo '<p><label for="' . $this->get_field_id('title') . '">' . _e('Title:') . '</label>';
		echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . esc_attr($title) . '"></p>';
	}

}


/**
 * Calendar widget class
 *
 * @since 2.8.0
 */
class Journey_Posts_Widget extends WP_Widget {

	function Journey_Posts_Widget() {
		$widget_ops = array('classname' => 'journey_posts', 'description' => __( 'A calendar of your blog&#8217;s posts') );
		$this->WP_Widget('journey_posts_archive', __('Journey Posts Archive'), $widget_ops);
	}

	function widget( $args, $instance ) {
		global $monthnum;
		wp_reset_query();
		if (!is_front_page()) {
			extract($args);
			$title = apply_filters('widget_title', empty($instance['title']) ? '&nbsp;' : $instance['title']);
			echo $before_widget;
			echo '<div class="journey-widget posts-archive-widget">';
			if ( $title ) {
				//echo $before_title . $title . $after_title;
				echo '<h3>RECENT POSTS</h3>';
			}
			echo '<div class="inner">';
			$this->recent_posts_content($args, $instance);
			echo '<div id="calendar_wrap">';
			echo "<strong>FIND POSTS BY DATE:</strong>";

			$prevMonthNum = $monthnum;
			//$monthnum = $monthnum - 1;
			get_calendar();

			echo '</div></div>';
			echo '</div>';
			echo $after_widget;
		}
		$monthnum = $prevMonthNum;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);

		return $instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ) );
		$title = strip_tags($instance['title']);
		echo '<p><label for="' . $this->get_field_id('title') . '">' . _e('Title:') . '</label>';
		echo '<input class="widefat" id="' . $this->get_field_id('title') . '" name="' . $this->get_field_name('title') . '" type="text" value="' . esc_attr($title) . '"></p>';
	}

	function recent_posts_content ($args, $instance) {
		$cache = wp_cache_get('widget_recent_posts', 'widget');
	
		if ( !is_array($cache) )
			$cache = array();
	
		if ( isset($cache[$args['widget_id']]) ) {
			echo $cache[$args['widget_id']];
			return;
		}
	
		ob_start();
		extract($args);
	
		$title = apply_filters('widget_title', empty($instance['title']) ? __('Recent Posts') : $instance['title']);
		if ( !$number = (int) $instance['number'] )
			$number = 10;
		else if ( $number < 1 )
			$number = 1;
		else if ( $number > 15 )
			$number = 15;
	
		$r = new WP_Query(array('showposts' => $number, 'nopaging' => 0, 'post_status' => 'publish', 'caller_get_posts' => 1));
		if ($r->have_posts())  {
			echo $before_widget;
	//		if ($title) {
	//			echo $before_title . $title . $after_title;
	//		}
			echo '<ul class="recent-posts">';
			while ($r->have_posts()) {
				$r->the_post();
				echo '<li><a href="';
				the_permalink();
				echo '" title="' . esc_attr(get_the_title() ? get_the_title() : get_the_ID()) . '">';
				echo "Day " . (dateDiff(TRIP_START, get_the_time('m/d/Y', $post))) . "&nbsp;-&nbsp;";
				if ( get_the_title() ) {
					the_title();
				}
				else {
					the_ID();
				}
				echo '</a></li>';
			}
			echo '</ul>';
			echo $after_widget;
			wp_reset_query();
		}
	
		$cache[$args['widget_id']] = ob_get_flush();
		wp_cache_add('widget_recent_posts', $cache, 'widget');
	}

}




?>