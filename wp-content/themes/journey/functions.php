<?php
define('TRIP_START', "5/3/2010");

require_once("classes.php");

// widgets
/**
 * Add function to widgets_init that'll load our widget.
 * @since 0.1
 */
add_action( 'widgets_init', 'example_load_widgets' );

/**
 * Register our widget.
 * 'Example_Widget' is the widget class used below.
 *
 * @since 0.1
 */
function example_load_widgets() {
	register_widget( 'Journey_Posts_Widget' );
	register_widget('Journey_Route_Widget');
}




/**********************************************************
 * 
 * Utility functions
 * 
 **********************************************************/
function dateDiff($startDate, $endDate) {
    // Parse dates for conversion
    $startArry = date_parse($startDate);
    $endArry = date_parse($endDate);
    //var_dump ($endDate);
    //var_dump($endArry);

    // Convert dates to Julian Days
    $start_date = gregoriantojd($startArry["month"], $startArry["day"], $startArry["year"]);
    //var_dump ($start_date);
    $end_date = gregoriantojd($endArry["month"], $endArry["day"], $endArry["year"]);
    //var_dump ($end_date);

    // Return difference
    return round(($end_date - $start_date), 0);
}


/**********************************************************
 * 
 * Widget functions
 * 
 **********************************************************/
function blog_intro_widget () {
	echo get_blog_intro_widget();
}
function get_blog_intro_widget() {
	$html = '
	<div class="blog-intro-widget">
		<img class="splash-image" src="' . get_bloginfo('template_url') . '/images/splash.jpg" 
			alt="The \'' . get_bloginfo('name') . '\' Authors"
		/>
		<p class="emphasis">
			<img style="float: left;height: 30px;" src="' . get_bloginfo('template_url') . '/images/chinesecharacters1000li.gif" />
			<span style="display: block;padding-top: 3px;padding-left: 30px;float: left;color: #ff6600;font-size: 20px;">"A Journey of a thousand li, begins with a single step<span class="highlight">*</span>"</span>
			<span class="right">
				-- Lao-Tzu (604BC-531BC)
			</span>
			<div class="footnote">
				<span class="highlight">*</span> A traditional Chinese saying attributed to the philosopher Lao Tzu.  A "li" is a Chinese
				measure of distance.
			</div>
		</p>
		<p>
			Welcome to our blog! We are a couple of 30-somethings from San Diego, California who decided to take a small break from the real world to 
			have a little adventure. Our goal is to explore the country of Japan by biking and camping from Fukuoka in the south to Wakkanai, the 
			northernmost point of the country.&nbsp;<a href="/about">Read more about us &gt;&gt;</a>
		</p>
		<div class="right">
			<a href="/journal">
				<img src="' . get_bloginfo('template_url') . '/images/button_enter-journal.png" alt="Enter the Journal" />
			</a>
		</div>
		<br class="clear" />
	</div>
	';
	return $html;
}

function route_map () {
	echo get_route_map ();
}

function get_route_map () {
//	$html = '
//		<iframe width="750" height="900" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" style="margin: auto;"
//			src="http://maps.google.com/maps/ms?ie=UTF8&amp;hl=en&amp;msa=0&amp;msid=107608591138069620838.000483c9381e263dc2585&amp;ll=39.181175,136.999512&amp;spn=13.614794,14.260254&amp;z=6&amp;output=embed">
//		</iframe>
//		<br />
//		<small>
//			View <a href="http://maps.google.com/maps/ms?ie=UTF8&amp;hl=en&amp;msa=0&amp;msid=107608591138069620838.000483c9381e263dc2585&amp;ll=39.181175,136.999512&amp;spn=13.614794,14.260254&amp;z=6&amp;source=embed" style="color:#0000FF;text-align:left">Journey of 1000 Li</a>
//			in a larger map
//		</small>
//	';
	$html = '';
	return $html;
}

function post_layout ($post, $tripStart, $displayFull = false, $displayComments = false, $displayMoreLink = true) {
	echo get_post_layout ($post, $tripStart, $displayFull, $displayComments, $displayMoreLink);
}

function get_post_layout ($post, $tripStart, $displayFull = false, $displayComments = false, $displayMoreLink = true) {
	$html = '
		<div class="post-entry">
			<p class="date">
				<span class="trip-day-number">DAY&#160;' . dateDiff(TRIP_START, get_the_time('m/d/Y', $post)) . '</span>
				<br />
				<span class="post-date">' . get_the_time('M d') . '</span>
				<br />
				<span class="post-year">' . get_the_time('Y') . '</span>
			</p>
	';
	$avtr = get_avatar($post->post_author, 60);
	$titleTag = $displayFull ? "h1" : "h2";
	$html .= $avtr . '
			<' . $titleTag . '>
				<a href="' . apply_filters('the_permalink', get_permalink()) . '" rel="bookmark" title="Permanent Link to ' . get_the_title() . '">' .
					get_the_title() . '
				</a>
			</' . $titleTag . '>
			<div class="post-category">Category:&#160;' . get_the_category_list('; ') . '</div>
	';
	$customVars = get_post_custom();
	if (count($customVars["location"]) > 0) {
		$html .= '<div class="post-category">Location:&#160;' . $customVars["location"][0] . '</div>';
	}
	ob_start();
	comments_popup_link('0 Comments &gt;&gt;', '1 Comment &gt;&gt;', '% Comments &gt;&gt;');
	$comments = ob_get_contents();
	ob_end_clean();

	ob_start();
	$displayFull ? the_content() : the_excerpt();
	$content = ob_get_contents();
	ob_end_clean();

	$commentsForm = "";
	if ($displayComments) {
		ob_start();
		comments_template();
		$commentsForm = '<div class="comments-form">' . ob_get_contents() . '</div>';
		ob_end_clean();
	}

	$html .= '
			<div class="post-content">' . 
				$content;
	$html .= $displayMoreLink ? '<a href="' . apply_filters('the_permalink', get_permalink()) . '">read full post &gt;&gt;</a>' : "";
	$html .= $displayComments ? "" : ' &nbsp;' . $comments;
	$html .= $commentsForm;
	$html .= '
			</div>
			<br class="clear" />
		</div>
	';
	return $html;
}

function follow_widget () {
	echo get_follow_widget ();
}
function get_follow_widget ($btnImage = "images/button_subscribe.png") {
	remove_filter("the_content", array("Geotag", "filterTheContent"));
	remove_action("the_content", "DMSGuestBook");
	$subscribeContent = apply_filters('the_content', '<p><!--subscribe2--></p>');
	add_action("the_content", "DMSGuestBook");
	add_filter("the_content", array("Geotag", "filterTheContent"));
//	$subscribeContent = '
//		<div class="subscribe-form-container">
//			<form id="subscribeForm" method="post" action="">
//				<input type="hidden" name="subscribe" value="subscribe">
//				<input type="hidden" name="ip" value="127.0.0.1" />
//				<input class="subscribe-text-input" type="text" name="email" 
//					value="Enter email address..." size="20" 
//					onfocus="if (this.value == \'Enter email address...\') {this.value = \'\';}" onblur="if (this.value == \'\') {this.value = \'Enter email address...\';}" 
//				/>
//				<input class="subscribe-button" type="image" name="subscribe" src="/wp-content/themes/journey/images/btn_home_subscribe.gif" />
//			</form>
//		</div>'
//	;
	//var_dump ($subscribeContent);die();
	$html = '
		<script type="text/javascript">
			$(document).ready(function () {
				$("#email_address").coolinput({hint:"e-mail address"});
				$("#followWidget .tabs li:lt(3)").click(function() {
					$("#followWidget .activeTab").removeClass("activeTab");
					$(this).addClass("activeTab");
					setFollowContent(this);
				});
				setFollowContent($("#followWidget .tabs li:eq(2)").get(0));
			});
			function setFollowContent (tab) {
				var content = $("#followContent" + $(tab).index()).html();
				$("#followWidget #currentContent").html(content);
			}
		</script>
		<div id="followWidget">
			<ul class="tabs">
				<li>RSS</li>
				<li>Twitter</li>
				<li class="activeTab">Email</li>
				<li class="widget-header">KEEP IN TOUCH!</li>
			</ul>
			<br class="clear" />
			<div id="currentContent" class="content active-content">
			</div>
		</div>
		<div id="followContent0" class="content inactive-content">
			<a href="http://www.journeyof1000li.com/feed/" target="_new" rel="nofollow">Follow our RSS feed!</a>
		</div>
		<div id="followContent1" class="content inactive-content">
			<a href="http://www.twitter.com/journeyof1000li" target="_new" rel="nofollow">Follow us on Twitter!</a>
		</div>
		<div id="followContent2" class="content inactive-content">' .
			((isset($_POST['subscribe'])) ? '' : 'Subscribe to weekly blog updates:<br />') . '
	';
	$html .= $subscribeContent;
	$html .= '
		</div>
		';
	return $html;
}


/**********************************************************
 * 
 * Layout functions
 * 
 **********************************************************/
function display_header ($nav) {
	get_header();

	$html = '
		<div id="headerContainer">
			<a href="' . get_settings('home') . '">
				<img src="' . get_bloginfo('template_url') . '/images/img_header.gif" 
					alt="' . get_bloginfo('name') . '"
				/>
			</a>
			<div id="primaryNav">
				<ul>
					<li class="first ' . (is_front_page() == "home" ? "active" : "inactive") . '">
						<a href="' . get_settings('home') . '">HOME</a>
					</li>
					<li class="' . ($nav == 'about' ? 'active' : 'inactive') . '">
						<a href="/about">ABOUT</a>
					</li>
					<li class="' . ($nav == 'route' ? 'active' : 'inactive') . '">
						<a href="/route">ROUTE</a>
					</li>
					<li class="' . ($nav == 'journal' ? 'active' : 'inactive') . '">
						<a href="/journal">JOURNAL</a>
					</li>
					<li class="' . ($nav == 'photos' ? 'active' : 'inactive') . '">
						<a href="/pictures">PHOTOS</a>
					</li>
					<li class="' . ($nav == 'resources' ? 'active' : 'inactive') . '">
						<a href="/resources">RESOURCES</a>
					</li>
					<li style="width: 99px;" class="' . ($nav == 'guest book' ? 'active' : 'inactive') . '">
						<a href="/guestbook">GUEST BOOK</a>
					</li>
					<li class="' . ($nav == 'contact' ? 'active' : 'inactive') . '">
						<a href="/contact">CONTACT</a>
					</li>
				</ul>
			</div>
		</div>
	';

	echo $html;
}



/**********************************************************
 * 
 * WP Lifecycle functions
 * 
 **********************************************************/
if ( function_exists('register_sidebar') )
	register_sidebar(array(
    'before_widget' => '<div class="widget-container">',
    'after_widget' => '</div>',
    'before_title' => '<h3>',
    'after_title' => '</h3>',
	));

// Function: Get Recent Comments 

function get_tenrecentcomments($mode = '', $limit = 10) {     
	global $wpdb, $post; 	$where = ''; 	
	if($mode == 'post') { 			
		$where = 'post_status = \'publish\''; 	} 
	elseif($mode == 'page') { 			
		$where = 'post_status = \'static\''; 	} 
	else { 			
		$where = '(post_status = \'publish\' OR post_status = \'static\')'; 	}     
$tenrecentcomments = $wpdb->get_results("SELECT $wpdb->posts.ID, post_title, post_name, post_status, comment_author, post_date, comment_date FROM $wpdb->posts INNER JOIN $wpdb->comments ON $wpdb->posts.ID = $wpdb->comments.comment_post_ID WHERE comment_approved = '1' AND post_date < '".current_time('mysql')."' AND $where AND post_password = '' ORDER  BY comment_date DESC LIMIT $limit"); 	
	if($tenrecentcomments) { 		
	foreach ($tenrecentcomments as $post) { 				
		$post_title = htmlspecialchars(stripslashes($post->post_title)); 				
		$comment_author = htmlspecialchars(stripslashes($post->comment_author)); 				
		$comment_date = mysql2date('m.d', $post->comment_date); 				
		echo "<li><a href=\"".get_permalink()."\">$comment_author</a></li>\n"; 		} 	} 
	else { 		echo '<li>'.__('N/A').'</li>'; 	} }  

    function widget_mytheme_search() {
?>-->
        <h3>Search</h3>
        <?php include (TEMPLATEPATH . '/searchform.php'); ?>
	  
<?php
}
if ( function_exists('register_sidebar_widget') )
    register_sidebar_widget(__('Search'), 'widget_mytheme_search');

    function widget_mytheme_links() {
?>
      <h3>Links</h3>
	    <ul>
		  <?php wp_list_bookmarks(); ?>
		</ul>
	  
<?php
}
if ( function_exists('register_sidebar_widget') )
    register_sidebar_widget(__('Links'), 'widget_mytheme_links');
	
	    function widget_mytheme_pages() {
?>
        <h3>Pages</h3>
	    <ul>
	      <?php wp_list_pages('title_li='); ?>
		</ul>
	  
<?php
}
if ( function_exists('register_sidebar_widget') )
    register_sidebar_widget(__('Pages'), 'widget_mytheme_pages');

?>
