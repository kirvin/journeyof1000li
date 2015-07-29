<?php
/*
Plugin Name: Picasa and Google Plus Express
Plugin URI: http://wordpress.org/extend/plugins/picasa-express-x2
Description: Browse and select photos from any public or private Google+ album and add them to your posts/pages.
Version: 2.2.10
Author: gjanes
Author URI: http://www.janesfamily.org
Text Domain: pe2
Domain Path: /

Thank you to Wott (wotttt@gmail.com | http://wott.info/picasa-express) for plugin 
Picasa Express 2.0 version 1.5.4.  This plugin and version contained a large 
re-write and many improvements of the plugin: Picasa Image Express 2.0 RC2

Thank you to Scrawl ( scrawl@psytoy.net ) for plugin Picasa Image Express 2.0 RC2
for main idea and Picasa icons

Copyright 2013 gjanes ( email : gcj.wordpress@janesfamily.org )

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

define( 'PE2_VERSION', '2.2.10' );
define( 'PE2_PHOTOSWIPE_VERSION', '3.0.5');

if (!class_exists("PicasaExpressX2")) {
	class PicasaExpressX2 {

		/**
		 * Define options and default values
		 * @var array
		 */
		var $options = array(
			'pe2_configured'        => false,
			'pe2_icon'              => 1,
			'pe2_roles'        		=> array('administrator'=>1),
			'pe2_level'         	=> 'blog',
			'pe2_user_name'         => 'undefined',

			'pe2_caption'           => 0,
			'pe2_caption_css'       => '',
			'pe2_caption_style'     => '',
			'pe2_caption_p_css'     => '',
			'pe2_caption_p_style'   => '',
			'pe2_title'             => 1,
			'pe2_parse_caption'     => 1,
			'pe2_link'              => 'photoswipe',
			'pe2_photoswipe_caption_num'	=> '1',
			'pe2_photoswipe_caption_view'	=> '1',
			'pe2_photoswipe_caption_dl'	=> '1',
			'pe2_relate_images'     => '1',

			'pe2_img_align'         => 'left',
			'pe2_auto_clear'        => '1',
			'pe2_img_css'           => '',
			'pe2_img_style'         => '',
			'pe2_a_img_css'         => '',
			'pe2_a_img_style'       => '',
			'pe2_return_single_image_html'	=> '',

			'pe2_phototile'			=> '',
			'pe2_featured_tag'		=> 1,
			'pe2_additional_tags'	=> '',

			'pe2_gal_align'         => 'left',
			'pe2_gal_css'           => '',
			'pe2_gal_style'         => '',

			'pe2_img_sort'          => 0,
			'pe2_img_asc'           => 1,
			'pe2_dialog_crop'		=> 1,
			'pe2_max_albums_displayed'	=> '',

			'pe2_gal_order'         => 0,

			'pe2_token'				=> '',

			'pe2_footer_link'		=> 1,
			'pe2_donate_link'		=> 1,

			'pe2_save_state'		=> 1,
			'pe2_saved_state'		=> '',
			'pe2_last_album'		=> '',
			'pe2_saved_user_name'	=> '',

			'pe2_large_limit'		=> '',
			'pe2_single_image_size'		=> 'w400',
			'pe2_single_image_size_format'	=> 'P',
			'pe2_single_video_size'		=> 'w400',
			'pe2_single_video_size_format'	=> 'P',
		);

		/**
		 * plugin URL
		 * @var string
		 */
		var $plugin_URL;

		/**
		 * Google auth plugin URL
		 * @var string
		 */
		var $google_authorize_plugin_URL;

		/**
		 * Phototile template array
		 * @var array
		 */
		var $pe2_phototile_template;

		/**
		 * plugin photos displayed
		 * @var array
		 */
		var $photos_displayed;

		function PicasaExpressX2() {
			// Hook for plugin de/activation
			$multisite = false;
			if (
				(function_exists('is_multisite') && is_multisite()) || // new version check
				(function_exists('activate_sitewide_plugin'))		   // pre 3.0 version check
			){
				$multisite = true;
				register_activation_hook( __FILE__, array (&$this, 'init_site_options' ) );
				register_deactivation_hook( __FILE__, array (&$this, 'delete_site_options' ) );
			} else {
				register_activation_hook( __FILE__, array (&$this, 'init_options' ) );
				register_deactivation_hook( __FILE__, array (&$this, 'delete_options' ) );
			}

			// Retrieve plugin options from database if plugin configured
			if (!$this->options['pe2_configured']) {
				// get plugin URL
				$this->plugin_URL = plugins_url().'/'.dirname(plugin_basename(__FILE__));

				// -----------------------------------------------------------------------
				// figure out the authorize URL and capitalize the URL properly to get by
				// the bug in google authorize
				$tmp = parse_url(plugins_url());
				$tmp2 = explode('.', $tmp['host']);
				$tmp2 = array_map('ucwords', $tmp2);
				$tmp_host = implode('.', $tmp2);
				$this->google_authorize_plugin_URL = $tmp['scheme'].'://'.$tmp_host.$tmp['path'].'/'.plugin_basename(__FILE__);
				unset($tmp, $tmp2, $tmp_host);

				// -----------------------------------------------------------------------
				// define the phototile array template
				$this->pe2_phototile_template = array();

				// https://lh4.googleusercontent.com/-3jmux-dXxzk/UNh-W44QB5I/AAAAAAAAHSg/AKXz6my4lMg/w619-h414-o-k/DSC_0785.JPG
				// https://lh4.googleusercontent.com/-klsfBvgXcqc/UNh7wHQH7wI/AAAAAAAAHMA/Sfl7nj7Y-Xo/w566-h424-p-o-k/DSC_0750.JPG
				// build the different layout options and their max number of photos
				// 1, 1
				$this->pe2_phototile_template[] = array(
					'max' => 2,
					'format' => array(
						// 619x414
						array('w' => 500, 'h' => 350),
						// 619x414
						array('w' => 500, 'h' => 350)
					));
				// 1, 1, 1
				$this->pe2_phototile_template[] = array(
					'max' => 3,
					'format' => array(
						// 225x336
						array('w' => 186, 'h' => 270),
						// 503x336
						array('w' => 407, 'h' => 270),
						// 503x336
						array('w' => 407, 'h' => 270)
					));
				// 1, 1, 1
				$this->pe2_phototile_template[] = array(
					'max' => 3,
					'format' => array(
						// 648x434
						array('w' => 528, 'h' => 380),
						// 291x434
						array('w' => 236, 'h' => 380),
						// 291x434
						array('w' => 236, 'h' => 380)
					));
				// 1, 1, 1, 1
				$this->pe2_phototile_template[] = array(
					'max' => 4,
					'format' => array(
						// 234x349
						array('w' => 191, 'h' => 280),
						// 234x349
						array('w' => 191, 'h' => 280),
						// 234x349
						array('w' => 191, 'h' => 280),
						// 521x349
						array('w' => 427, 'h' => 280)
					));
				// 1, 1, 1, 1, 1
				$this->pe2_phototile_template[] = array(
					'max' => 5,
					'format' => array(
						array('w' => 200, 'h' => 300),
						array('w' => 200, 'h' => 300),
						array('w' => 200, 'h' => 300),
						array('w' => 200, 'h' => 300),
						array('w' => 200, 'h' => 300)
					));
				// 1, 2, 3
				$this->pe2_phototile_template[] = array(
					'max' => 6,
					'format' => array(
						array('w' => 466, 'h' => 339),
						array(
							array(
								array('w' => 267, 'h' => 190),
								array('w' => 267, 'h' => 190)
							),
							array(
								array('w' => 100, 'h' => 149),
								array('w' => 217, 'h' => 149),
								array('w' => 217, 'h' => 149)
							),
							'width' => 534
						)
					));
				// 1, 3, 3
				$this->pe2_phototile_template[] = array(
					'max' => 7,
					'format' => array(
						// 566x419
						array('w' => 466, 'h' => 339),
						array(
							array(
								// 155x231
								array('w' => 132, 'h' => 190),
								// 155x231
								array('w' => 132, 'h' => 190),
								// 346x231
								array('w' => 270, 'h' => 190)
							),
							array(
								// 120x179
								array('w' => 100, 'h' => 149),
								// 267x179
								array('w' => 217, 'h' => 149),
								// 267x179
								array('w' => 217, 'h' => 149)
							),
							'width' => 534
						)
					));
				// 1, 4, 2
				$this->pe2_phototile_template[] = array(
					'max' => 7,
					'format' => array(
						// 566x424
						array('w' => 480, 'h' => 480),
						array(
							array(
								// 123x185
								array('w' => 130, 'h' => 190),
								// 123x185
								array('w' => 130, 'h' => 190),
								// 123x185
								array('w' => 130, 'h' => 190),
								// 277x185
								array('w' => 130, 'h' => 190)
							),
							array(
								array('w' => 320, 'h' => 290),
								array('w' => 200, 'h' => 290)
							),
							'width' => 520
						)
					));

				// -----------------------------------------------------------------------
				// define the empty array used to keep track of photos displayed
				// (thus preventing duplicates in lightbox/thickbox/highslide nav
				$this->photos_displayed = array();

				// -----------------------------------------------------------------------
				// read all of the options from the database and store them in our local
				// options array
				foreach ($this->options as $key => $option) {
					$this->options[$key] = get_option($key,$option);
					if (!preg_match('/^[whs]\d+$/',$this->options['pe2_large_limit'])){
						$this->options['pe2_large_limit'] = '';
					}
				}

				// -----------------------------------------------------------------------
				// determine if photoswipe is the selected link option.  If so, check to
				// see if IE is the browser that is connecting, and if so switch the 
				// link option to thickbox_custom
				// FIXME - when photoswipe fixes the problem with IE, remove this logic
				if($this->options['pe2_link'] == 'photoswipe'){
					// check to see if the browser is IE
					if(stripos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false){
						// the client is using IE, we must switch the pe2_link option
						$this->options['pe2_link'] = 'thickbox_custom';
					}
				}// end if the user has configured photoswipe

				// ########################################################################
				if (is_admin()) {

					// loading localization if exist
					add_action('init', array(&$this, 'load_textdomain'));

					// Add settings to the plugins management page under
					add_filter('plugin_action_links_'.plugin_basename(__FILE__), array(&$this, 'add_settings_link'));
					// Add a page which will hold the  Options form
					add_action('admin_menu', array(&$this, 'add_settings_page'));
					add_filter('contextual_help', array(&$this, 'contextual_help'), 10 , 2);

					// Add media button to editor
					add_action('media_buttons', array(&$this, 'add_media_button'), 20);
					// Add iframe page creator
					add_action('media_upload_picasa', array(&$this, 'media_upload_picasa'));

					// AJAX request from media_upload_picasa iframe script ( pe2-scripts.js )
					add_action('wp_ajax_pe2_get_gallery', array(&$this, 'get_gallery'));
					add_action('wp_ajax_pe2_get_images', array(&$this, 'get_images'));
					add_action('wp_ajax_pe2_save_state', array(&$this, 'save_state'));
					add_action('wp_ajax_pe2_process_shortcode', array(&$this, 'pe2_process_shortcode'));

					// Add setting for user profile if capable
					add_action('show_user_profile', array(&$this, 'user_profile'));
					add_action('personal_options_update', array(&$this, 'user_update'));
				
					// new site creation
					if ($multisite && function_exists('get_site_option') && get_site_option('pe2_multisite')){
						add_action('wpmu_new_blog', array(&$this, 'wpmu_new_blog') );
					}

				// ########################################################################
				} else {
					// add the shortcode processing
					add_shortcode('pe2-gallery', array(&$this, 'gallery_shortcode'));
					add_shortcode('pe2-image', array(&$this, 'image_shortcode'));
					add_shortcode('clear', array(&$this, 'clear_shortcode'));

					// add the footer link
					if ($this->options['pe2_footer_link']) {
						add_action('wp_footer', array(&$this, 'add_footer_link'));
					}

					// add the pe2 display css file
					add_action('init', array(&$this, 'pe2_add_display_css'));

					// to use the default thickbox script with wordpress:
					if($this->options['pe2_link'] == 'thickbox_integrated'){
						// they chose the option to use the internal Wordpress version 
						// of Thickbox
						add_action('init', array(&$this, 'pe2_add_thickbox_script'));
					}

					// to use a custom thickbox script for display:
					if($this->options['pe2_link'] == 'thickbox_custom'){
						// they chose the option to use the custom thickbox from this plugin
						add_action('init', array(&$this, 'pe2_add_custom_thickbox_script'));
					}

					// to use the photoswipe script for display:
					if($this->options['pe2_link'] == 'photoswipe'){
						// they chose the option to use photoswipe from this plugin,
						// check to see if we're on the login page, and if so skip
						// loading the photoswipe stuff.  jquery.mobile seems to 
						// really goof up certain stuff with the login form
						if(!in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))){
							// add the action to init photoswipe
							add_action('init', array(&$this, 'pe2_add_photoswipe_script'));
						}
					}

					// function to add the caption width correction javascript, if 
					// captions are enabled and image width is defined by height
					if($this->options['pe2_caption'] == '1'){
						// captions are enabled, we need to perform the check in
						// the footer
						add_action('wp_footer', array(&$this, 'pe2_add_caption_width_javascript'));
					}

					// determine if we need to filter the caption shortcode to add our
					// class and style attributes
					if(($this->options['pe2_caption_css'] != null) || ($this->options['pe2_caption_style'] != null) || ($this->options['pe2_caption_p_css'] != null) || ($this->options['pe2_caption_p_style'] != null)){
						// add the filter to parse the content for the caption HTML
						// and add the additional class/style attributes.
						// Filter: the_content was chosen instead of img_caption_shortcode
						// because media.php doesn't allow you to simply "edit" a generated
						// caption shortcode with this filter, but instead forces a complete
						// replacement of it.  So instead of taking over the caption 
						// shortcode creation, this plugin will simply parse the content 
						// and modify any caption tags it finds
						add_filter('the_content', array(&$this, 'pe2_img_caption_shortcode_filter'), 12);
					}
				// ########################################################################
				}// end else for if this is in the admin
			}// end if we're initializing
		}// end constructor

		/**
		 * Walk all blogs and apply $func to every founded
		 *
		 * @global integer $blog_id
		 * @param function $func Function to apply changes to blog
		 */
		function walk_blogs($func) {

			$walk = isset($_GET['networkwide'])||isset($_GET['sitewide']); // (de)activate by command from site admin

			if (function_exists('get_site_option')) {
				$active_sitewide_plugins = (array) maybe_unserialize( get_site_option('active_sitewide_plugins') );
				$walk = $walk || isset($active_sitewide_plugins[plugin_basename(__FILE__)]);
			}

			if ( $walk && function_exists('switch_to_blog')) {

				add_site_option('pe2_multisite', true);
				
				global $blog_id, $switched_stack, $switched;
				$saved_blog_id = $blog_id;

				global $wpdb;
				$blogs = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs WHERE public = '1' AND archived = '0' AND mature = '0' AND spam = '0' AND deleted = '0'");
				if( is_array( $blogs ) ) {
					reset( $blogs );
					foreach ( (array) $blogs as $new_blog_id ) {
						switch_to_blog($new_blog_id);
						$this->$func();
						array_pop( $switched_stack ); // clean
					}
					switch_to_blog($saved_blog_id);
					array_pop( $switched_stack ); // clean
					$switched = ( is_array( $switched_stack ) && count( $switched_stack ) > 0 );
				}
			} else {
				$this->$func();
			}
		}

		function init_site_options() {
			$this->walk_blogs('init_options');
		}
		function delete_site_options() {
			$this->walk_blogs('delete_options');
			if (function_exists('delete_site_option')) delete_site_option('pe2_multisite');
		}
		function wpmu_new_blog($new_blog_id) {
			switch_to_blog($new_blog_id);
			$this->init_options();
			restore_current_blog();
		}

		/**
		 * Enable plugin configuration and set roles by config
		 */
		function init_options() {
			add_option('pe2_configured',true);

			foreach (get_option('pe2_roles',$this->options['pe2_roles']) as $role=>$data) {
				if ($data) {
					$role = get_role($role);
					$role->add_cap('picasa_dialog');
				}
			}
		}

		/**
		 * Delete plugin configuration flag
		 */
		function delete_options() {
			delete_option('pe2_configured');
		}

		function load_textdomain() {
			if ( function_exists('load_plugin_textdomain') ) {
				load_plugin_textdomain('pe2', false, dirname( plugin_basename( __FILE__ ) ) );
			}
		}

		function add_footer_link() {
			echo "<p class=\"footer-link\" style=\"font-size:75%;text-align:center;\"><a href=\"http://wordpress.org/extend/plugins/picasa-express-x2\">".__('With Google+ plugin by Geoff Janes','pe2')."</a></p>";
		}

		/**
		 * Echo the link with icon to run plugin dialog
		 *
		 * @param string $id optinal id for link to plugin dialog
		 * @return void
		 */
		function add_media_button($id = '') {

			if (!current_user_can('picasa_dialog')) return;

			$plugin_URL = $this->plugin_URL;
			$icon = $this->options['pe2_icon'];
			// 'type=picasa' => 'media_upload_picasa' action above
			$media_picasa_iframe_src = "media-upload.php?type=picasa&tab=type&TB_iframe=true&width=640&height=566";
			$media_picasa_title = __("Add Google+ image or gallery", 'pe2');
			$put_id = ($id)?"id=\"$id-picasa_dialog\"":'';

			echo "<a href=\"$media_picasa_iframe_src\" $put_id class=\"thickbox\" title=\"$media_picasa_title\"><img src=\"$plugin_URL/icon_picasa$icon.gif\" alt=\"$media_picasa_title\" /></a>";

		}

		/**
		 * Config scrips and styles and print iframe content for dialog
		 *
		 */
		function media_upload_picasa() {

			if (!current_user_can('picasa_dialog')) return;

			// add script and style for dialog
			add_action('admin_print_styles', array(&$this, 'add_style'));
			add_action('admin_enqueue_scripts', array(&$this, 'add_script'));

			// we do not need default script for media_upload
			$to_remove = explode(',', 'swfupload-all,swfupload-handlers,image-edit,set-post-thumbnail,imgareaselect');
			foreach ($to_remove as $handle) {
				if (function_exists('wp_dequeue_script')) wp_dequeue_script($handle);
				else wp_deregister_script($handle);
			}
			
			// but still reuse code for make media_upload iframe
			return wp_iframe(array(&$this, 'type_dialog'));
		}

		/**
		 * Attach script and localisation text in dialog
		 * run from action 'admin_enqueue_scripts' from {@link media_upload_picasa()}
		 *
		 * @global object $wp_scripts
		 */
		function add_script() {
			// load the appropriate older thickbox-based media-upload scripts that
			// we will depend upon:
			wp_enqueue_style('thickbox');
			wp_enqueue_script('media-upload');
			wp_enqueue_script('thickbox');

			// now add our pe2-script:
			global $wp_scripts;
			$wp_scripts->add('pe2-script', $this->plugin_URL.'/pe2-scripts.js', array('jquery'),PE2_VERSION);
			$options = array(
				'waiting'   => str_replace('%pluginpath%', $this->plugin_URL, __("<img src='%pluginpath%/loading.gif' height='16' width='16' /> Please wait", 'pe2')),
				'env_error' => __("Error: Cannot insert image(s) due to incorrect or missing /wp-admin/js/media-upload.js\nConfirm that media-upload.js is loaded in the parent/editor window.\n\nThis error can be caused by:\n\n#1 - opening the image selection dialog before the Wordpress admin window\nis fully loaded.  Reload the post/page editor page in wp-admin and try again.\n\n#2 - the CKEditor for WP plugin is known to conflict with media-upload.js\nDisable the plugin and try again.", 'pe2'),
				'image'     => __('Image', 'pe2'),
				'gallery'   => __('Gallery', 'pe2'),
				'reload'    => __('Reload', 'pe2'),
				'options'   => __('Options', 'pe2'),
				'album'	    => __('Album', 'pe2'),
				'shortcode'	=> __('Shortcode', 'pe2'),
				'thumb_w'   => get_option('thumbnail_size_w'),
				'thumb_h'   => get_option('thumbnail_size_h'),
				'thumb_crop'=> get_option('thumbnail_crop'),
				'state'		=> 'albums',
			);
			foreach ( $this->options as $key => $val ) {
                if (!is_array($val)) // skip arrays: pe2_roles
                    $options[$key]=$val;
			}
			if ($this->options['pe2_level'] == 'user') {
				global $current_user;
				$options['pe2_save_state']  = get_user_meta($current_user->data->ID,'pe2_save_state',true);
				$options['pe2_saved_state'] = get_user_meta($current_user->data->ID,'pe2_saved_state',true);
				$options['pe2_last_album']  = get_user_meta($current_user->data->ID,'pe2_last_album',true);
				$options['pe2_saved_user_name']  = get_user_meta($current_user->data->ID,'pe2_saved_user_name',true);
				$options['pe2_user_name']   = get_user_meta($current_user->data->ID,'pe2_user_name',true);
			}

			if ($options['pe2_save_state']) {
				if ($options['pe2_saved_state']) $options['state'] = $options['pe2_saved_state'];
				if ($options['pe2_saved_user_name']) $options['pe2_user_name'] = $options['pe2_saved_user_name'];
			}
			
			$options['pe2_user_name'] = trim($options['pe2_user_name']);
			if (''==$options['pe2_user_name']) $options['pe2_user_name']='undefined';
			if ('undefined'==$options['pe2_user_name']) $options['state']= 'nouser';

			foreach ( $options as $key => $val ) {
					$options[$key] = rawurlencode($val);
			}
			$wp_scripts->localize( 'pe2-script', 'pe2_options', $options );

			$wp_scripts->enqueue('pe2-script');
		}

		/**
		 * Request styles
		 * run by action 'admin_print_styles' from {@link media_upload_picasa()}
		 *
		 * @global boolean $is_IE
		 */
		function add_style() {
			global $is_IE;
			wp_enqueue_style('media');
			wp_enqueue_style('pe2-style', $this->plugin_URL.'/picasa-express-2.css',array(),PE2_VERSION,'all');
			if ($is_IE)
				wp_enqueue_style('pe2-style-ie', $this->plugin_URL.'/picasa-express-2-IE.css',array(),PE2_VERSION,'all');
		}

		/**
		 * Print dialog html
		 * run by parameter in (@link wp_iframe()}
		 *
		 * @global object $current_user
		 */
		function type_dialog() {

			/*
				<a href="#" class="button alignright">Search</a>
				<form><input type="text" class="alignright" value="Search ..."/></form>
			 */
			?>
			<div id="pe2-nouser" class="pe2-header" style="display:none;">
				<input type="text" class="alignleft" value="user name"/>
				<a id="pe2-change-user" href="#" class="button alignleft pe2-space"><?php _e('Change user', 'pe2')?></a>
				<a id="pe2-cu-cancel" href="#" class="button alignleft pe2-space"><?php _e('Cancel', 'pe2')?></a>
				<div id="pe2-message1" class="alignleft"></div>
				<br style="clear:both;"/>
			</div>
			<div id="pe2-albums" class="pe2-header" style="display:none;">
				<a id="pe2-user" href="#" class="button alignleft"></a>
				<div id="pe2-message2" class="alignleft"><?php _e('Select an Album', 'pe2')?></div>
				<a id="pe2-switch2" href="#" class="button alignleft"><?php _e('Album', 'pe2')?></a>
				<a href="#" class="pe2-options button alignright pe2-space" ><?php _e('Options','pe2'); ?></a>
				<a href="#" class="pe2-reload button alignright" ></a>
				<br style="clear:both;"/>
			</div>
			<div id="pe2-images" class="pe2-header" style="display:none;">
				<a id="pe2-album-name" href="#" class="button alignleft"><?php _e('Select an Album', 'pe2')?></a>
				<div id="pe2-message3" class="alignleft"><?php _e('Select images', 'pe2')?></div>

				<a id="pe2-switch" href="#" class="button alignleft"><?php _e('Image', 'pe2')?></a>
				<a id="pe2-insert" href="#" class="button alignleft pe2-space" style="display:none;"><?php _e('Insert', 'pe2')?></a>
				<a href="#" class="pe2-options button alignright pe2-space" ></a>
				<a href="#" class="pe2-reload button alignright" ></a>
				<br style="clear:both;"/>
			</div>

			<div id="pe2-options" style="display:none;">
<?php		// print out the shared options for the dialog
			$this->pe2_shared_options(false);

?>
			</div><!-- end pe2-options -->
			<div id="pe2-main">
			</div>
		<?php
		}// end function type_dialog()


		/**
		 * Print the shared options (between settings and dialog)
		 *
		 * @param $settings - boolean - whether or not we're displaying options from plugin settings
		 * @return none, outputs the options
		 */
		 function pe2_shared_options($settings){
		 	// print out the shared options, decyphering between the main settings
			// page and the limited overrides in the dialog
			if($settings){
				// we're in the main settings
?>				<h3><?php _e('Google+ Express access', 'pe2') ?></h3>
				<table class="form-table">

					<?php 
					$option = $this->options['pe2_roles'];
					$editable_roles = get_editable_roles();

					$pe2_roles = array();
					foreach( $editable_roles as $role => $details ) {
						$name = translate_user_role($details['name'] );
						$pe2_roles[] = "<label><input name=\"pe2_roles[$role]\" type=\"checkbox\" value=\"1\" ".checked(isset($option[$role]),true,false)."/> $name</label>";
					}
					$out = implode('<br/>', $pe2_roles);
					unset($pe2_roles);
					
					$this->make_settings_row(
						__('Assign capability to Roles', 'pe2'),
						$out,
						__('Roles for users who can use Google+ albums access via plugin', 'pe2')
					);

					$option = $this->options['pe2_level'];
					
					$this->make_settings_row(
						__('Google+ Express access level', 'pe2'),
						'<label><input type="radio" name="pe2_level" value="blog" '.checked($option,'blog',false).' onclick="jQuery(\'.picasa-site-user\').show();" /> '.__('Blog').'</label> &nbsp; '.
			        	'<label><input type="radio" name="pe2_level" value="user" '.checked($option,'user',false).' onclick="jQuery(\'.picasa-site-user\').hide();" /> '.__('User').'</label> ',
						__('Google user name ( including private album access ) defined for whole blog or for every user independently', 'pe2')
					);

					?>

				</table>

				<h3><?php _e('Display properties', 'pe2') ?></h3>
				<table class="form-table">

					<?php
					$user = $this->options['pe2_user_name'];

					if ('blog'==$this->options['pe2_level'] && $user) {
						$result = 'ok';
						$feed_url = "http://picasaweb.google.com/data/feed/base/user/$user?alt=rss&kind=album&hl=en_US";
						$response = $this->get_feed($feed_url);
						if ( is_wp_error( $response ) )
							$result = 'error: '.$response->get_error_message();
						else if (!$this->get_item($response,'atom:id')) {
							$result = 'error: wrong answer';
						}

						if (method_exists('WP_Http', '_getTransport')) {
							$ta = array(); $transports = WP_Http::_getTransport(array());
							foreach ($transports as $t) $ta[] = strtolower(str_replace('WP_Http_','',get_class($t)));
							if ($ta) $result = sprintf(__("Checking user: %s - Transport: %s - <a href=\"%s\" target=\"_blank\">URL</a>",'pe2'),$result,implode(',',$ta),$feed_url);
						} else if (method_exists('WP_Http', '_get_first_available_transport')) {
							$transport = WP_Http::_get_first_available_transport(array());
							if ($transport) {
								$transport_name = strtolower(str_replace('WP_HTTP_','',$transport));
								$result = sprintf(' '.__("Checking user: %s - Transport: %s - <a href=\"%s\" target=\"_blank\">URL</a>",'pe2'),$result,$transport_name,$feed_url);
							}
							
						}
					} else $result='';

					// get our token variable
					if ($this->options['pe2_level'] == 'user') {
						global $current_user;
						$token = get_user_meta($current_user->data->ID,'pe2_token',true);
					} else {
						$token  = $this->options['pe2_token'];
					}

					$this->make_settings_row(
						__('Google user name for site', 'pe2'),
						'<input type="text" class="regular-text" name="pe2_user_name" value="'.esc_attr($user).'" />'.$result.
						(($token == null)?'<br /><a href="https://www.google.com/accounts/AuthSubRequest?next='.urlencode($this->google_authorize_plugin_URL.'?authorize').'&scope=http%3A%2F%2Fpicasaweb.google.com%2Fdata%2F&session=1&secure=0">'.__('Requesting access to private albums', 'pe2').'</a>':'<br/><a href="?page=picasa-express-2&revoke=true">'.__('Revoke access to private albums', 'pe2').'</a>'),
						(($token != null)?__('You have successfully authorized access to private albums.', 'pe2'):__('By clicking this link you will be redirected to the Google authorization page. Please use the same username as what is listed above to login before authorizing.', 'pe2')),
						'class="picasa-site-user" style="display:'.(('blog'==$this->options['pe2_level'])?'table-row':'none').'"'
					);

					$option = $this->options['pe2_save_state'];
					$this->make_settings_row(
						__('Save last state', 'pe2'),
						'<label><input type="checkbox" name="pe2_save_state" value="1" '.checked($option,'1',false).' /> '.__('Save last state in dialog', 'pe2').'</label> ',
						__('Save the last used username when it changes, the last selected album if you insert images, or the albums list if you insert an album shorcode', 'pe2'),
						'class="picasa-site-user" style="display:'.(('blog'==$this->options['pe2_level'])?'table-row':'none').'"'
					);

					$opts = array(
						1 => __('Picasa square icon', 'pe2'),
						2 => __('Picasa square grayscale icon', 'pe2'),
						3 => __('Picasa round icon', 'pe2'),
						4 => __('Picasa round grayscale icon', 'pe2'),
					);
					$option = $this->options['pe2_icon'];
					$out = '';
					foreach ($opts as $i=>$text) {
						$out .= '<label>';
						$out .= "<input type=\"radio\" name=\"pe2_icon\" value=\"$i\" ".checked($option,$i,false)." />";
						$out .= "<img src=\"{$this->plugin_URL}/icon_picasa$i.gif\" alt=\"$text\" title=\"$text\"/> &nbsp; ";
						$out .= '</label>';
			        	}

					$this->make_settings_row(
						__('Picasa icon', 'pe2'),
						$out,
						__('This icon marks the dialog activation link in the edit post page', 'pe2')
					);

					$opts = array(
						0 => __('None', 'pe2'),
						1 => __('Date', 'pe2'),
						2 => __('Title', 'pe2'),
						3 => __('File name', 'pe2'),
					);
					$option = $this->options['pe2_img_sort'];
					$out = '';
					foreach ($opts as $i=>$text) {
						$out .= '<label>';
						$out .= "<input type=\"radio\" name=\"pe2_img_sort\" value=\"$i\" ".checked($option,$i,false)." /> $text &nbsp; ";
						$out .= '</label>';
					}
					$this->make_settings_row(
						__('Sorting images in album', 'pe2'),
						$out,
						__('This option drives image sorting in the dialog', 'pe2')
					);

					$option = $this->options['pe2_img_asc'];
					$this->make_settings_row(
						__('Sorting order', 'pe2'),
						'<label><input type="radio" name="pe2_img_asc" value="1" '.checked($option,'1',false).' /> '.__('Ascending',  'pe2').'</label> &nbsp; '.
						'<label><input type="radio" name="pe2_img_asc" value="0" '.checked($option,'0',false).' /> '.__('Descending', 'pe2').'</label> '
					);

					$option = $this->options['pe2_dialog_crop'];
					$this->make_settings_row(
						__('Selection dialog thumbnail style', 'pe2'),
						'<label><input type="radio" name="pe2_dialog_crop" value="1" '.checked($option,'1',false).' /> '.__('Crop into a square',  'pe2').'</label> &nbsp; '.
						'<label><input type="radio" name="pe2_dialog_crop" value="0" '.checked($option,'0',false).' /> '.__('Scale proportionally', 'pe2').'</label> ',
						__('This applies to image thumbnails only, not album cover thumbnails')
					);

					$option = $this->options['pe2_max_albums_displayed'];
					$this->make_settings_row(
						__('Maximum albums displayed', 'pe2'),
						'<label>'.__('Max number of albums to display in the dialog:', 'pe2').'  <input type="text" name="pe2_max_albums_displayed" value="'.$option.'" size=4 />',
						__('Leave blank to display all albums',  'pe2').'</label> '
					);

					?>

				</table>
<?php			}// end if we're in the main settings page

?>
				<h3><?php _e('Image properties', 'pe2') ?></h3>

				<table class="form-table">

					<?php
					// ---------------------------------------------------------------------
					// single image thunbnail size (override)
					$format = $this->options['pe2_single_image_size_format'];
					$option = $this->options['pe2_single_image_size'];
					preg_match('/(\w)(\d+)-*([whs]*)(\d*)/',$option,$mode);
					if(strpos($option, '-c') !== false)
						$crop = true;
					else
						$crop = false;
					if (!$mode) $mode=array('','','');
					$this->make_settings_row(
						__('Single image thumbnail size', 'pe2'),
						'<input type="radio" name="pe2_single_image_size_format" value="P" '.checked($format, 'P', false).' /> Proportionally &nbsp; '.
						'<input type="radio" name="pe2_single_image_size_format" value="N" '.checked($format, 'N', false).' /> Non-proportionally &nbsp; '.
						'<input type="radio" name="pe2_single_image_size_format" value="C" '.checked($format, 'C', false).' /> Custom<br/>'.
						'<div id="pe2_single_image_proportional">'.
						'	Scale: &nbsp; &nbsp;[ <label><input type="radio" name="pe2_single_image_size_mode" class="pe2_single_image_size" value="w" '.checked($mode[1], 'w', false).' /> '.__('width','pe2').'</label> &nbsp; '.
						'	<label><input type="radio" name="pe2_single_image_size_mode" class="pe2_single_image_size" value="h" '.checked($mode[1], 'h', false).' /> '.__('height','pe2').'</label> &nbsp; '.
						'	<label><input type="radio" name="pe2_single_image_size_mode" class="pe2_single_image_size" value="s" '.checked($mode[1], 's', false).' /> '.__('any','pe2').'</label> '.
						__(' ]&nbsp; &nbsp; proportionally to ','pe2').
						'	<input type="text" name="pe2_single_image_size_dimension" class="pe2_single_image_size" style="width:60px;" id="pe2_single_image_size_dimension" value="'.$mode[2].'" />'.
						__(' pixels.','pe2').
						'	<label> &nbsp; &nbsp; &nbsp; <input type="checkbox" name="pe2_single_image_size_crop" class="pe2_single_image_size" value="-c" '.checked($crop, true, false).' /> '.__(' Crop image into a square.','pe2').'</label> '.
						'</div>'.
						'<div id="pe2_single_image_non">'.
						'	Width: <input type="text" name="pe2_single_image_size_width" style="width: 60px;" id="pe2_single_image_size_width" value="'.$mode[2].'" /> &nbsp; &nbsp; '.
						'	Height: <input type="text" name="pe2_single_image_size_height" style="width: 60px;" id="pe2_single_image_size_height" value="'.$mode[4].'" />'.
						'</div>'.
						'<div id="pe2_single_image_custom">'.
						'	Custom request string: <input type="text" name="pe2_single_image_size" style="width: 120px;" id="pe2_single_image_size_input" value="'.$option.'" />'.
						'</div>'
						,
						'',
						'',
						'id="pe2_single_image_size_message" style="display:'.(($option) ? 'block' : 'none').';"'
					);
					?>
					<style type="text/css">
						input:disabled {
							background-color: #eee;
						}
					</style>
					<script type="text/javascript">
						// ------------------- TOGGLE SIZE MODE -------------------
						function pe2_toggle_image_size_mode(type){
							var format = jQuery('input[name=pe2_' + type + '_size_format]:checked').val();
							if(format == 'C'){
								// custom, hide the others
								jQuery('#pe2_' + type + '_proportional, #pe2_' + type + '_non').hide();
								jQuery('#pe2_' + type + '_custom').show();
							}else if(format == 'N'){
								// non-proportionally
								jQuery('#pe2_' + type + '_proportional, #pe2_' + type + '_custom').hide();
								jQuery('#pe2_' + type + '_non').show();

								// execute our calculation based on data provided
								pe2_compute_nonpro_image_size(jQuery('input[name=pe2_' + type + '_size_width], input[name=pe2_' + type + '_size_height]'), type);
							}else{
								// proportionally
								jQuery('#pe2_' + type + '_non, #pe2_' + type + '_custom').hide();
								jQuery('#pe2_' + type + '_proportional').show();

								// execute our calculation based on data provided
								pe2_compute_image_size('mode', jQuery('input[name=pe2_' + type + '_size_mode]:checked').val(), type);
								pe2_compute_image_size('size', jQuery('input[name=pe2_' + type + '_size_dimension]').val(), type);
								pe2_determine_crop(type, jQuery('input[name=pe2_' + type + '_size_crop]'));
							}
						}// end function pe2_toggle_image_size_mode()

						// on load, toggle the appropriate section based on the mode
						// from the db
						pe2_toggle_image_size_mode('single_image');

						// on image size format change, update the input fields
						jQuery('input[name=pe2_single_image_size_format]').click(function(){
							pe2_toggle_image_size_mode('single_image');
						});

						// ------------------- PROPORTIONAL IMAGES -------------------
						function pe2_compute_image_size(mode,value,type) {
							var target_input = jQuery('input[name=pe2_' + type + '_size]');
							if(target_input.length == 0){
								// this is the large image size selection
								target_input = jQuery('input[name=pe2_' + type + '_limit]');
							}
							var val = target_input.val();
							
							// check for the case where it was just enabled after having
							// been disabled and saved
							if((val == '') || (val == 'w')){
								// override with some default
								val = 'w600';
							}

							// split into our parts
							var parts = {
								mode : val.substring(0, 1),
								size : val.replace(/^[a-z]*([0-9]+).*$/,'$1'),
								crop : val.replace(/^[^\-]+(.*)$/,'$1')};

							// override the particular part that was just changed
							parts[mode] = value;

							// make sure our crop variable is correct
							if((parts.crop != '') && (parts.crop != '-c')){
								parts.crop = '';
							}

							// store the value back in our target
							target_input.val(parts.mode+parts.size+parts.crop);

							// update the text that displays the setting being used
							jQuery('#pe2_' + type + '_size_message_option').text(parts.mode+parts.size+parts.crop);

							// trigger the .change event since we're changing
							// the value of the target with js, the event
							// doesn't get triggered
							target_input.trigger('change');
						}// end function pe2_compute_image_size(..)

						// if mode changes, update image size
						jQuery('input[name=pe2_single_image_size_mode]').change(function(){ if (jQuery(this).attr('checked')) pe2_compute_image_size('mode',jQuery(this).val(), 'single_image'); });

						// if size changes, update image size
						jQuery('input[name=pe2_single_image_size_dimension]').change(function(){
							pe2_compute_image_size('size',jQuery('input[name=pe2_single_image_size_dimension]').val(), 'single_image');
						});

						// function to determine size crop attribute
						function pe2_determine_crop(name, obj){
							// use the checked selector to determine if the checkbox is 
							// checked or not
							if(jQuery('input[name=pe2_' + name + '_size_crop]:checked').length > 0){
								// the checkbox is checked
								pe2_compute_image_size('crop',jQuery(obj).val(), name);
							}else{
								// the checkbox is not checked
								pe2_compute_image_size('crop','',name);
							}
						}// end function pe2_determine_crop(..)

						// if crop changes, update image size
						jQuery('input[name=pe2_single_image_size_crop]').change(function(){
							pe2_determine_crop('single_image', this);
						});

						// ------------------- NON-PROPORTIONAL -------------------
						function pe2_compute_nonpro_image_size(obj, type){
							var target_input = jQuery('input[name=pe2_' + type + '_size]');
							var val = target_input.val();
							
							// check for the case where it was just enabled after having
							// been disabled and saved
							if((val == '') || (val == 'w')){
								// override with some default
								val = 'w600-h450';
							}

							// split into our parts
							var width = val.match(/w([0-9]+)/);
							if(width == null){
								width = '600';
							}else{
								width = width[1];
							}
							var height = val.match(/h([0-9]+)/);
							if(height == null){
								height = '450';
							}else{
								height = height[1];
							}

							// determine if the width or the height was changed
							if(obj.attr('value') != ''){
								// we have a value, set it appropriately
								if(obj.attr('name').indexOf('width') !== -1){
									// this is width
									width = obj.attr('value');
								}else{
									// this is height
									height = obj.attr('value');
								}
							}

							// store the value back in our target
							target_input.val('w' + width + '-h' + height + '-p');

							// update the text that displays the setting being used
							jQuery('#pe2_' + type + '_size_message_option').text('w' + width + 'h' + height + '-c');

							// if the target inputs type is hidden, then also trigger
							// the .change event (hiddens don't automatically trigger
							// the .change event for some reason)
							if(target_input.attr('type') == 'hidden'){
								target_input.trigger('change');
							}
						}// end function pe2_compute_nonpro_image_size(..)

						// if the width or height changes, update the value
						jQuery('input[name=pe2_single_image_size_width], input[name=pe2_single_image_size_height]').change(function(){ pe2_compute_nonpro_image_size(jQuery(this), 'single_image'); });
					</script>
					<?php
					// ---------------------------------------------------------------------
					// single video thunbnail size (override)
					$format = $this->options['pe2_single_video_size_format'];
					$option = $this->options['pe2_single_video_size'];
					preg_match('/(\w)(\d+)-*([whs]*)(\d*)/',$option,$mode);
					if(strpos($option, '-c') !== false)
						$crop = true;
					else
						$crop = false;
					if (!$mode) $mode=array('','','');
					$this->make_settings_row(
						__('Single video thumbnail size', 'pe2'),
						'<input type="radio" name="pe2_single_video_size_format" value="P" '.checked($format, 'P', false).' /> Proportionally &nbsp; '.
						'<input type="radio" name="pe2_single_video_size_format" value="N" '.checked($format, 'N', false).' /> Non-proportionally &nbsp; '.
						'<input type="radio" name="pe2_single_video_size_format" value="C" '.checked($format, 'C', false).' /> Custom<br/>'.
						'<div id="pe2_single_video_proportional">'.
						'	Scale: &nbsp; &nbsp;[ <label><input type="radio" name="pe2_single_video_size_mode" class="pe2_single_video_size" value="w" '.checked($mode[1], 'w', false).' /> '.__('width','pe2').'</label> &nbsp; '.
						'	<label><input type="radio" name="pe2_single_video_size_mode" class="pe2_single_video_size" value="h" '.checked($mode[1], 'h', false).' /> '.__('height','pe2').'</label> &nbsp; '.
						'	<label><input type="radio" name="pe2_single_video_size_mode" class="pe2_single_video_size" value="s" '.checked($mode[1], 's', false).' /> '.__('any','pe2').'</label> '.
						__(' ]&nbsp; &nbsp; proportionally to ','pe2').
						'	<input type="text" name="pe2_single_video_size_dimension" class="pe2_single_video_size" style="width:60px;" id="pe2_single_video_size_dimension" value="'.$mode[2].'" />'.
						__(' pixels.','pe2').
						'	<label> &nbsp; &nbsp; &nbsp; <input type="checkbox" name="pe2_single_video_size_crop" class="pe2_single_video_size" value="-c" '.checked($crop, true, false).' /> '.__(' Crop image into a square.','pe2').'</label> '.
						'</div>'.
						'<div id="pe2_single_video_non">'.
						'	Width: <input type="text" name="pe2_single_video_size_width" style="width: 60px;" id="pe2_single_video_size_width" value="'.$mode[2].'" /> &nbsp; &nbsp; '.
						'	Height: <input type="text" name="pe2_single_video_size_height" style="width: 60px;" id="pe2_single_video_size_height" value="'.$mode[4].'" />'.
						'</div>'.
						'<div id="pe2_single_video_custom">'.
						'	Custom request string: <input type="text" name="pe2_single_video_size" style="width: 120px;" id="pe2_single_video_size_input" value="'.$option.'" />'.
						'</div>'
						,
						'',
						'',
						'id="pe2_single_video_size_message" style="display:'.(($option) ? 'block' : 'none').';"'
					);
					?>
					<script type="text/javascript">
						// ------------------- TOGGLE SIZE MODE -------------------

						// on load, toggle the appropriate section based on the mode
						// from the db
						pe2_toggle_image_size_mode('single_video');

						// on image size format change, update the input fields
						jQuery('input[name=pe2_single_video_size_format]').click(function(){
							pe2_toggle_image_size_mode('single_video');
						});

						// ------------------- PROPORTIONAL IMAGES -------------------
						// if mode changes, update image size
						jQuery('input[name=pe2_single_video_size_mode]').change(function(){ if (jQuery(this).attr('checked')) pe2_compute_image_size('mode',jQuery(this).val(), 'single_video'); });

						// if size changes, update image size
						jQuery('input[name=pe2_single_video_size_dimension]').change(function(){
							pe2_compute_image_size('size',jQuery('input[name=pe2_single_video_size_dimension]').val(), 'single_video');
						});

						// if crop changes, update image size
						jQuery('input[name=pe2_single_video_size_crop]').change(function(){
							pe2_determine_crop('single_video', this);
						});

						// ------------------- NON-PROPORTIONAL -------------------
						// if the width or height changes, update the value
						jQuery('input[name=pe2_single_video_size_width], input[name=pe2_single_video_size_height]').change(function(){ pe2_compute_nonpro_image_size(jQuery(this), 'single_video'); });
					</script>
<?php

					// ---------------------------------------------------------------------
					// large image size
					$option = $this->options['pe2_large_limit'];
					preg_match('/(\w)(\d+)/',$option,$mode);
					if (!$mode) $mode=array('','','');
					$this->make_settings_row(
						__('Large image size', 'pe2'),
						'<label><input type="checkbox" name="pe2_large_limit" value="'.$option.'" '.checked(($option)?1:0,1,false).' /> '.__('Set / Limit: ','pe2').'</label> '.
						'<label> &nbsp; &nbsp;[ <input type="radio" name="pe2_large_size_mode" class="pe2_large_limit" value="w" '.checked($mode[1], 'w', false).' '.disabled(($option)?1:0,0,false).' /> '.__('width','pe2').'</label> &nbsp; '.
						'<label><input type="radio" name="pe2_large_size_mode" class="pe2_large_limit" value="h" '.checked($mode[1], 'h', false).' '.disabled(($option)?1:0,0,false).' /> '.__('height','pe2').'</label> &nbsp; '.
						'<label><input type="radio" name="pe2_large_size_mode" class="pe2_large_limit" value="s" '.checked($mode[1], 's', false).' '.disabled(($option)?1:0,0,false).' /> '.__('any','pe2').' ]&nbsp; &nbsp; </label> '.
						__(' proportionally to ','pe2').
						'<input type="text" name="pe2_large_size_dimension" class="pe2_large_limit" style="width:60px;" id="pe2_large_size" value="'.$mode[2].'" '.disabled(($option)?1:0,0,false).' />'.
						__(' pixels.','pe2').
						'<label> &nbsp; &nbsp; &nbsp; <input type="checkbox" name="pe2_large_size_crop" class="pe2_large_limit" value="-c" '.checked($crop, true, false).' /> '.__(' Crop image into a square.','pe2').'</label> '
						,
						sprintf(__('Value \'%s\' will be used to set / limit large image'),"<span id=\"pe2_large_size_message_option\">$option</span>"),
						'',
						'id="large-limit-message" style="display:'.(($option) ? 'block' : 'none').';"'
					);
					?>
					<script type="text/javascript">
						jQuery('input[name=pe2_large_limit]').change(function(){
							if (jQuery(this).attr('checked')) {
								// the checkbox is set
								jQuery('input.pe2_large_limit').removeAttr('disabled');
								jQuery('#large-limit-message').show();

								// set the default for the input boxes
								jQuery('input[name=pe2_large_size_mode][value=w]').attr('checked', 'true');
								jQuery('input[name=pe2_large_size_dimension]').val('600');

								// call the calculation function for each section
								pe2_compute_image_size('mode',jQuery('input[name=pe2_large_size_mode]').val(), 'large');
								pe2_compute_image_size('size',jQuery('input[name=pe2_large_size_dimension]').val(), 'large');
							} else {
								jQuery('input.pe2_large_limit').removeAttr('checked').attr('disabled','disabled');
								jQuery('input[name=pe2_large_size]').val('');
								jQuery('#pe2_large_size_message_option').text('');
								jQuery('input[name=pe2_large_limit]').val('');
								jQuery('#large-limit-message').hide();
							}
						});
						// if mode changes, update image size
						jQuery('input[name=pe2_large_size_mode]').change(function(){ if (jQuery(this).attr('checked')) pe2_compute_image_size('mode',jQuery(this).val(), 'large'); });
						// if size changes, update image size
						jQuery('input[name=pe2_large_size_dimension]').change(function(){
							pe2_compute_image_size('size',jQuery('input[name=pe2_large_size_dimension]').val(), 'large');
						});
						// if crop changes, update image size
						jQuery('input[name=pe2_large_size_crop]').change(function(){
							pe2_determine_crop('large', this);
						});
					</script>
					<?php

					$option = $this->options['pe2_caption'];
					$this->make_settings_row(
						__('Display caption', 'pe2'),
						'<label><input type="checkbox" name="pe2_caption" id="pe2_caption" value="1" '.checked($option,'1',false).' onclick="toggle_caption_children()" /> '.__('Show the caption under thumbnail image', 'pe2').'</label> ',
						null,
						'id="pe2_caption"'
					);

					$this->make_settings_row(
						__('Caption container CSS class', 'pe2'),
						'<input type="text" name="pe2_caption_css" class="regular-text pe2_caption_child" value="'.esc_attr($this->options['pe2_caption_css']).'"/>',
						__("You can define one or more classes for the caption container tag", 'pe2'),
						' class="pe2_caption_child"'
					);
					$this->make_settings_row(
						__('Caption container style', 'pe2'),
						'<input type="text" name="pe2_caption_style" class="regular-text pe2_caption_child" value="'.esc_attr($this->options['pe2_caption_style']).'"/>',
						__('You can hardcode any css attributes for the caption container tag', 'pe2'),
						' class="pe2_caption_child"'
					);

					$this->make_settings_row(
						__('Caption P CSS class', 'pe2'),
						'<input type="text" name="pe2_caption_p_css" class="regular-text pe2_caption_child" value="'.esc_attr($this->options['pe2_caption_p_css']).'"/>',
						__("You can define one or more classes for the caption P tag", 'pe2'),
						' class="pe2_caption_child"'
					);
					$this->make_settings_row(
						__('Caption P style', 'pe2'),
						'<input type="text" name="pe2_caption_p_style" class="regular-text pe2_caption_child" value="'.esc_attr($this->options['pe2_caption_p_style']).'"/>',
						__('You can hardcode any css attributes for the caption P tag', 'pe2'),
						' class="pe2_caption_child"'
					);

					// end the caption child container and add the script to handle 
					// hiding and clearing the class values if they don't have the 
					// caption checkbox set
?><script>
function toggle_caption_children(){
	var val = jQuery('input[name=pe2_caption]:checked').val();
	if(val == '1'){
		// the checkbox is checked, show the children
		jQuery('tr.pe2_caption_child').show();
	}else{
		// the checkbox is unchecked, hide the children and clear any values
		jQuery('tr.pe2_caption_child').hide();
		jQuery('input.pe2_caption_child').val('');
	}
}
jQuery('document').ready(function(){
	// execute the toggle for caption children to hide them if the box is unchecked
	toggle_caption_children();
});
</script>
<?php

					$option = $this->options['pe2_title'];
					$this->make_settings_row(
						__('Add caption as title', 'pe2'),
						'<label><input type="checkbox" name="pe2_title" value="1" '.checked($option,'1',false).' /> '.__('Show the caption by mouse hover tip', 'pe2').'</label> '
					);

					$option = $this->options['pe2_parse_caption'];
					$this->make_settings_row(
						__('Remove filename captions', 'pe2'),
						'<label><input type="checkbox" name="pe2_parse_caption" value="1" '.checked($option,'1',false).' /> '.__('If a caption is detected as the image filename, replace it with blank', 'pe2').'</label> '
					);

			// link option for photos
			if($settings){
					$opts = array (
						'none'     => __('No link', 'pe2'),
						'direct'   => __('Direct link', 'pe2'),
						'picasa'   => __('Link to Google+ Web Album', 'pe2'),
						'lightbox' => __('Lightbox (External)', 'pe2'),
						'thickbox' => __('Thickbox (External)', 'pe2'),
						'thickbox_integrated' => __('Thickbox (Integrated Wordpress version)', 'pe2'),
						'thickbox_custom' => __('Thickbox (Custom from this plugin)', 'pe2'),
						'highslide'=> __('Highslide (External)', 'pe2'),
						'photoswipe'=> __('PhotoSwipe (Mobile friendly)', 'pe2')
					);

					$out = '<select name="pe2_link" id="pe2_link" onchange="pe2_toggle_large_image_link_options()">';
					$option = $this->options['pe2_link'];
					foreach ($opts as $key => $val ) {
						$out .= "<option value=\"$key\" ".selected($option, $key, false ).">$val</option>";
					}
					$out .= '</select>';
					$this->make_settings_row(
						__('Link to larger image', 'pe2'),
						$out,
						'<span id="pe2_external_message">'.__('To use external libraries like Thickbox, Lightbox or Highslide you need to install and integrate the library independently','pe2').'</span>'
					);

					$this->make_settings_row(
						__('PhotoSwipe caption options', 'pe2'),
						'<label><input type="checkbox" name="pe2_photoswipe_caption_num" value="1" '.checked($this->options['pe2_photoswipe_caption_num'],'1',false).' /> '.__('Add the "Image X of X" text to the second row of the caption', 'pe2').'</label> '.
						'<br/><label><input type="checkbox" name="pe2_photoswipe_caption_view" value="1" '.checked($this->options['pe2_photoswipe_caption_view'],'1',false).' /> '.__('Add the "View on Google+" link to the second row of the caption', 'pe2').'</label> '.
						'<br/><label><input type="checkbox" name="pe2_photoswipe_caption_dl" value="1" '.checked($this->options['pe2_photoswipe_caption_dl'],'1',false).' /> '.__('Add the "Download" link to the second row of the caption', 'pe2').'</label> ',
						null,
						' id="pe2_photoswipe_options"'
					);

					$option = $this->options['pe2_relate_images'];
					$this->make_settings_row(
						__('Relate all of a post\'s images', 'pe2'),
						'<label><input type="checkbox" name="pe2_relate_images" value="1" '.checked($option,'1',false).' /> '.__('If using PhotoSwipe, Thickbox, Lightbox or Highslide, relate all images in the page/post together for fluid next/prev navigation', 'pe2').'</label> ',
						null,
						'id="pe2_relate_row"'
					);

					// add the scripts to handle hiding the external script sentence
					// if one of the external options are selected, to handle hiding
					// the "relate images" option if one of the gallery options is
					// enabled, and to handle hiding and clearing the of the second
					// row caption options if they don't have photoswipe selected
?><script>
function pe2_toggle_large_image_link_options(){
	// get the selected value of the selection box
	var val = jQuery('#pe2_link').val();
	var external = false;
	var gallery = false;
	var photoswipe = false;
	
	// check to see if we need to show or hide the external script message
	if((val == 'lightbox') || (val == 'thickbox') || (val == 'highslide')){
		// these are external utilities that need to have the message added,
		// and of course also a gallery
		external = true;
		gallery = true;
	}else if((val == 'thickbox_integrated') || (val == 'thickbox_custom')){
		// the two integrated versions of thickbox, gallery = true
		gallery = true;
	}else if(val == 'photoswipe'){
		// integrated version of PhotoSwipe - gallery & photoswipe are true
		gallery = true;
		photoswipe = true;
	}

	// determine if we're hiding/showing the external library message
	if(external){
		jQuery('#pe2_external_message').show();
	}else{
		jQuery('#pe2_external_message').hide();
	}

	// determine if we're hiding/showing the "relate images" gallery option
	if(gallery){
		jQuery('#pe2_relate_row').show();
	}else{
		// hide the option and clear a checkbox if it is checked
		jQuery('#pe2_relate_row').hide();
		jQuery('#pe2_relate_row input').attr('checked', false);
	}
		
	// determine if we're using photoswipe and hide/show the related options
	if(photoswipe){
		jQuery('#pe2_photoswipe_options').show();
	}else{
		// hide the options and clear any checkboxes if any are checked
		jQuery('#pe2_photoswipe_options').hide();
		jQuery('#pe2_photoswipe_options input').attr('checked', false);
	}
}// end function pe2_toggle_large_image_link_options()
jQuery('document').ready(function(){
	// execute the toggle for large image link options on ready
	pe2_toggle_large_image_link_options();
});
</script>
<?php

			}// end if we're on the main settings page

					$opts = array (
						'none'   => __('None'),
						'left'   => __('Left'),
						'center' => __('Center'),
						'right'  => __('Right'),
					);
					$option = $this->options['pe2_img_align'];
					$out = '';
					foreach ($opts as $key => $val ) {
						$out .= "<input type=\"radio\" name=\"pe2_img_align\" id=\"img-align$key\" value=\"$key\" ".checked($option, $key, false)." /> ";
						$out .= "<label for=\"img-align$key\" style=\"padding-left:22px;margin-right:13px;\" class=\"image-align-$key-label\">$val</label>";
					}
					$this->make_settings_row(
						__('Image alignment', 'pe2'),
						$out
					);

					$option = $this->options['pe2_auto_clear'];
					$this->make_settings_row(
						__('Auto clear: both', 'pe2'),
						'<label><input type="checkbox" name="pe2_auto_clear" value="1" '.checked($option,'1',false).' /> '.__('Automatically add &lt;p class="clear"&gt;&lt;/p&gt; after groups of images inserted together', 'pe2').'</label> '
					);

					$this->make_settings_row(
						__('Image CSS class', 'pe2'),
						'<input type="text" name="pe2_img_css" class="regular-text" value="'.esc_attr($this->options['pe2_img_css']).'"/>',
						__("You can define one or more classes for img tags", 'pe2')
					);
					$this->make_settings_row(
						__('Image style', 'pe2'),
						'<input type="text" name="pe2_img_style" class="regular-text" value="'.esc_attr($this->options['pe2_img_style']).'"/>',
						__('You can hardcode any css attributes for the img tags', 'pe2')
					);

					$this->make_settings_row(
						__('Image A CSS class', 'pe2'),
						'<input type="text" name="pe2_a_img_css" class="regular-text" value="'.esc_attr($this->options['pe2_a_img_css']).'"/>',
						__("You can define one or more classes for the a tags wrapping the img tags", 'pe2')
					);
					$this->make_settings_row(
						__('Image A style', 'pe2'),
						'<input type="text" name="pe2_a_img_style" class="regular-text" value="'.esc_attr($this->options['pe2_a_img_style']).'"/>',
						__('You can hardcode any css attributes for the a tags wrapping the img tags', 'pe2')
					);

			// check to see if we're on the main settings page, and if so add
			// the option for returning HTML rather than shortcode
			if($settings){
				// option to translate shortcode into HTML
				$option = $this->options['pe2_return_single_image_html'];
				$this->make_settings_row(
					__('Return HTML instead of shortcode', 'pe2'),
					'<label><input type="checkbox" name="pe2_return_single_image_html" value="1" '.checked($option,'1',false).' /> '.__('Return HTML for images selected from the dialog instead of the [pe2-image] shortcode', 'pe2').'</label> ',
					__('NOTE: Enabling this feature limits the ability of this plugin to update old posts when image size options are updated or if Google\'s migration from Picasaweb to Google+ breaks existing URLs', 'pe2')
				);
			}// end if we're on main settings page

?>
				</table>

				<h3><?php _e('Gallery properties', 'pe2') ?></h3>

				<table class="form-table">

<?php
				// ---------------------------------------------------------------------
				// album format / thumbnail size
				$this->make_settings_row(
					__('Album format', 'pe2'),
					'<label><input type="radio" name="pe2_gal_format" value="phototile" '.($this->options['pe2_phototile'] != null ? 'checked="checked"' : '').' /> '.__('Use the Google+ phototile style for album layout and thumbnail size selection', 'pe2').'</label>'.
					'<span id="pe2_phototile_container"><br/> &nbsp; &nbsp; &nbsp; <label for="pe2_phototile" style="vertical-align: top;">'.__('Phototile album width:', 'pe2').'</label>'.
					'<div style="display: inline-block;"><input type="text" name="pe2_phototile" id="pe2_phototile" class="regular-text" value="'.esc_attr($this->options['pe2_phototile']).'" size="6" maxlength="10" style="width: 50px;" />px; &nbsp; &nbsp; ('.__('Example: 600px;', 'pe2').')<br/><span style="font-size: 9px;">'.__('This is used to to calculate how much room thumbnails can consume', 'pe2').'<br/>'.__('NOTE: By enabling the phototile option, gallery alignment and caption display are disabled', 'pe2').'</span></div></span>'.
					'<br/><label><input type="radio" name="pe2_gal_format" value="standard" '.($this->options['pe2_phototile'] == null ? 'checked="checked"' : '').' /> '.__('Use default thumbnail size configured using the <a href="options-media.php">Settings-&gt;Media</a> page.', 'pe2').'</label>'
				);

				// script for enabling phototile input / clearing its value
?><script>
function pe2_toggle_phototile_option(){
	// get the selected value of the selection box
	var val = jQuery('input[name=pe2_gal_format]:checked').val();
	if(val == 'phototile'){
		// show the phototile option
		jQuery('#pe2_phototile_container').show();

		// hide the other options that are no longer allowed to be
		// set
		jQuery('#pe2_caption').hide();
		jQuery('tr.pe2_caption_child').hide();
		jQuery('#pe2_gal_align').hide();
	}else{
		// hide the phototile option and clear any value
		jQuery('#pe2_phototile_container').hide();
		jQuery('#pe2_phototile_container input').val('');

		// show the other options that can now be set
		jQuery('#pe2_gal_align').show();
		jQuery('#pe2_caption').show();

		// determine if we should show the caption children
		// or not, based on the caption checkbox
		var val = jQuery('input[name=pe2_caption]:checked').val();
		if(val == '1'){
			// we can show them
			jQuery('tr.pe2_caption_child').show();
		}
	}
}// end function pe2_toggle_phototile_option()
jQuery('input[name=pe2_gal_format]').click(function(){
	// execute the toggle for large image link options on ready
	pe2_toggle_phototile_option();
});
jQuery('document').ready(function(){
	// execute the toggle for large image link options on ready
	pe2_toggle_phototile_option();
});
</script>
<?php

				// ---------------------------------------------------------------------
				// display tag options
				$this->make_settings_row(
					__('Photo tag options', 'pe2'),
					'<input name="pe2_featured_tag" type="checkbox" id="pe2_featured_tag" value="1" '.checked('1', get_option('pe2_featured_tag'),false).'/> '.
					'<label for="pe2_featured_tag">'.__('Include photos from albums only if they contain the "Featured" tag').'</label><br />'.
					'<label for="pe2_additional_tags" style="vertical-align: top;">'.__('Additional tag(s) required').'</label> '.
					'<div style="display: inline-block;"><input type="text" name="pe2_additional_tags" id="pe2_additional_tags" class="regular-text" value="'.esc_attr($this->options['pe2_additional_tags']).'"/><br/><span style="font-size: 9px;">'.__('Separate multiple tags by commas.  NOTE: currently Google requires private album access for tags to work').'</span></div>'
				);

				// ---------------------------------------------------------------------
				// remaining gallery options
				$this->make_settings_row(
					__('Selection order', 'pe2'),
					'<label><input type="checkbox" name="pe2_gal_order" value="1" '.checked($this->options['pe2_gal_order'],'1',false).' /> '.__("Click images in your preferred order", 'pe2').'</label>'
				);

				$option = $this->options['pe2_gal_align'];
				$out = '';
				foreach ($opts as $key => $val ) {
					$out .= "<input type=\"radio\" name=\"pe2_gal_align\" id=\"gal-align$key\" value=\"$key\" ".checked($option, $key, false)." /> ";
					$out .= "<label for=\"gal-align$key\" style=\"padding-left:22px;margin-right:13px;\" class=\"image-align-$key-label\">$val</label>";
				}
				$this->make_settings_row(
					__('Gallery alignment', 'pe2'),
					$out,
					null,
					'id="pe2_gal_align"'
				);

				$this->make_settings_row(
					__('Gallery CSS class', 'pe2'),
					'<input type="text" name="pe2_gal_css" class="regular-text" value="'.esc_attr($this->options['pe2_gal_css']).'"/>',
					__("You can define one or more classes for the gallery container tag", 'pe2')
				);
				$this->make_settings_row(
					__('Gallery style', 'pe2'),
					'<input type="text" name="pe2_gal_style" class="regular-text" value="'.esc_attr($this->options['pe2_gal_style']).'"/>',
					__('You can hardcode any css attributes for the gallery container tag', 'pe2')
				);
?>

				</table>
			</div>
<?php
			// check to see if we're on the main settings page, and if so add the last
			// section for advertising
			if($settings){
				// we're on main settings, add the remaining entries
?>
				<h3><?php _e('Advertising', 'pe2') ?></h3>

				<table class="form-table">

					<?php
					$this->make_settings_row(
						__('Footer link', 'pe2'),
						'<label><input type="checkbox" name="pe2_footer_link" value="1" '.checked($this->options['pe2_footer_link'],'1',false).' /> '.__('Enable footer link "With Google+ plugin by Geoff Janes"','pe2').'</label>'
					);

					$this->make_settings_row(
						__('PayPal donation banner', 'pe2'),
						'<label><input type="checkbox" name="pe2_donate_link" value="1" '.checked($this->options['pe2_donate_link'],'1',false).' /> '.__('Enable PayPal banner on this page','pe2').'</label>'
					);


					?>

				</table>
<?php
			}// end if we're on main settings page
		}// end function pe2_shared_options(..)


		/**
		 * Request server with token if defined
		 *
		 * @param string $url URL for request data
		 * @param boolean $token use token from settings
		 * @return string received data
		 */
		function get_feed($url,$token=false) {
			global $wp_version;
			// add Auth later
			$options = array(
				'timeout' => 30 ,
				'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' ),
				'sslverify' => false // prevent some problems with Google in token request
			);

			if (!$token) {
				if ($this->options['pe2_level'] == 'user') {
					global $current_user;
					$token = get_user_meta($current_user->data->ID,'pe2_token',true);
				} else {
					$token  = $this->options['pe2_token'];
				}
			}
			if ($token) $options['headers'] = array ( 'Authorization' =>"AuthSub token=\"$token\"" );

			$response = wp_remote_get($url, $options);

			if ( is_wp_error( $response ) )
				return $response;

			if ( 200 != $response['response']['code'] )
				return new WP_Error('http_request_failed', __('Response code is ').$response['response']['code']);

			// preg sensitive for \n\n, but we not need any formating inside
			return (str_replace("\n",'',trim( $response['body'] )));
		}

		/**
		 * Find tag in content by attribute
		 *
		 * @param string $content
		 * @param string $tag
		 * @param string $attr
		 * @return string attribute value or all parameters if not found. false if no tag found
		 */
		function get_item_attr($content,$tag,$attr) {
			if (!preg_match("|<$tag\s+([^>]+)/?>|u",$content,$m))
				return false;
			$a = preg_split("/[\s=]/",$m[1]);
			for ($i=0; $i<count($a); $i+=2) {
				if ($a[$i]==$attr) return trim($a[$i+1],"'\" ");
			}
			return join(',',$a);
		}
		/**
		 * Find tag in content
		 *
		 * @param string $content
		 * @param string $tag
		 * @param boolean $first Search only first. False by default
		 * @return bool|string|array content of the found node. false if not found
		 */
		function get_item($content,$tag,$first=false) {
			if (!preg_match_all("|<$tag(?:\s[^>]+)?>(.+?)</$tag>|u",$content,$m,PREG_PATTERN_ORDER))
				return false;
//			echo "$tag: ".count($m[1])."<br/>";
			if (count($m[1])>1 && !$first) return ($m[1]);
			else return ($m[1][0]);
		}

		/**
		 * wp_ajax_pe2_get_gallery
		 * print html for gallery
		 *
		 */
		function get_gallery() {

			if (!current_user_can('picasa_dialog')) {
				echo json_encode((object) array('error'=>__('Insufficient privelegies','pe2')));
				die();
			}

			$out = (object)array();

			if (isset($_POST['user'])) {
				$user = $_POST['user'];
			} else die();

			$rss = $this->get_feed("http://picasaweb.google.com/data/feed/base/user/$user?alt=rss&kind=album&hl=en_US");
			if (is_wp_error($rss)) {
				$out->error = $rss->get_error_message();
			} else if (!$this->get_item($rss,'atom:id')) {
				$out->error = __('Invalid picasa username: ', 'pe2').$user;
		    } else {
			    $items = $this->get_item($rss,'item');
			    $output = '';
			    if ($items) {
			    	if (!is_array($items)) $items = array($items);
			    	$output .= "\n<table><tr>\n";
			    	$i = 0;
					$max_albums = get_option('pe2_max_albums_displayed');
					foreach($items as $item) {
						// http://picasaweb.google.com/data/entry/base/user/wotttt/albumid/5408701349107410241?alt=rss&amp;hl=en_US
						$guid  = str_replace("entry","feed",$this->get_item($item,'guid'))."&kind=photo";
						$title = $this->escape($this->get_item($item,'title'));
						$desc  = $this->escape($this->get_item($item,'media:description'));
						$url   = $this->get_item_attr($item,'media:thumbnail','url');
						$item_type  = (strpos($item, 'medium=\'video\'') !== false ? 'video' : 'image');

						// resize the thumbnail URL so that it fits properly in the media
						// window
						$url = str_replace('s160-c', 's140-c-o', $url);

						// generate the output
						$output .= "<td><a href='#$guid'><img src='$url' alt='$desc' type='$item_type'/><span>$title</span></a></td>\n";

						// increment the shared image counter for the following
						// two checks
						$i++;

						// determine if we need to stop outputting albums
						if(($max_albums > 0) && ($i >= $max_albums)){
							// we've reached our max, break out of the loop
							break;
						}

						// determine if we need to break this row and start a new
						// one
						if ($i % 4 == 0) $output .= "</tr><tr>\n";
					}// end foreach album item to output
					$output .= "</tr></table>\n";
			    }// end if we have items to output

			    $out->items = $this->get_item($rss,'openSearch:totalResults');
			    $out->title = $this->get_item($rss,'title',true);
			    $out->data  = $output;
			    $out->cache  = $_POST['cache'];
		    }// end else for if there were any errors

			echo json_encode($out);
			die();
		}

		/**
		 * wp_ajax_pe2_get_images
		 * print html for images
		 *
		 */
		function get_images() {

			if (!current_user_can('picasa_dialog')) {
				echo json_encode((object) array('error'=>__('Insufficient privelegies','pe2')));
				die();
			}

			$out = (object)array();

			if (isset($_POST['guid'])) {
				// determine if this guid is base64 encoded or a straight album URL,
				// decoding if necessary
				if(strpos($_POST['guid'], 'http') !== 0){
					// decode it
					$album = base64_decode($_POST['guid']);
				}else{
					// simply store it after decoding any entities that may have been
					// created by the editor or elsewhere
					$album = html_entity_decode($_POST['guid']);
				}
			} else die();

			$rss = $this->get_feed($album);
			if (is_wp_error($rss)) {
				$out->error = $rss->get_error_message();
			} else if (!$this->get_item($rss,'atom:id')) {
				$out->error = __('Invalid album ', 'pe2');
		    } else {
			    $items = $this->get_item($rss,'item');
		    	$output = '';
		    	$key = 1; $images = array();
		    	$sort = $this->options['pe2_img_sort'];
		    	$dialog_crop = ($this->options['pe2_dialog_crop'] == 1 ? '-c' : '');
			    if ($items) {
			    	if (!is_array($items)) $items = array($items);
			    	foreach($items as $item) {
			    		switch ($sort) {
			    			case 0: $key++; break;
			    			case 1: $key = strtotime($this->get_item($item,'pubDate',true)); break;
			    			case 2: $key = $this->get_item($item,'title',true); break;
			    			case 3: $key = $this->get_item($item,'media:title',true); break;
			    		}
			    		$images[$key] = array (
						'album'  => $this->get_item($item,'link'), // picasa album image
						'title' => $this->parse_caption($this->escape($this->get_item($item,'title'))),
						'file'  => $this->escape($this->get_item($item,'media:title')),
						'desc'  => $this->escape($this->get_item($item,'media:description')),
						'item_type'  => (strpos($item, 'medium=\'video\'') !== false ? 'video' : 'image'),
						'url'   => str_replace('s72','s144'.$dialog_crop.'-o',$this->get_item_attr($item,'media:thumbnail','url')),
			    		);
			    	}
			    	if ($this->options['pe2_img_asc']) ksort($images);
			    	else krsort($images);
			    	$output .= "\n<table><tr>\n";
			    	$i = 0;
			    	foreach($images as $item) {
						$output .= "<td><a href='{$item['album']}'><img src='{$item['url']}' alt='{$item['file']}' type='{$item['item_type']}' title='{$item['desc']}' /><span>{$item['title']}</span></a></td>\n";
						if ($i++%4==3) $output .= "</tr><tr>\n";
					}
					$output .= "</tr></table>\n";
			    }

				// do our action for dialog footer
				$output = apply_filters('pe2_get_images_footer', $output);

				// add our successful results to the output to return
			    $out->items = $this->get_item($rss,'openSearch:totalResults');
			    $out->title = $this->get_item($rss,'title',true);
				$out->data  = $output;
			    $out->cache  = $_POST['cache'];
		    }// end else for if we had an error getting the images

			// output the result and exit
			echo json_encode($out);
			die();
		}

		/**
		 * wp_ajax_pe2_process_shortcode
		 * convert image shortcode into HTML to return to the editor
		 *
		 */
		function pe2_process_shortcode() {

			if (!current_user_can('picasa_dialog')) {
				echo json_encode(array('error'=>__('Insufficient privelegies','pe2')));
				die();
			}

			// verify that we have our data
			if(!isset($_POST['data'])){
				// we don't have any data
				echo json_encode(array('error' => __('No shortcode to process', 'pe2')));
				die();
			}

			// add the shortcode handler for images so do_shortcode knows what to do
			add_shortcode('pe2-image', array(&$this, 'image_shortcode'));

			// process the shortcodes and return the result to the ajax caller
			echo json_encode(array('html' => do_shortcode(stripslashes($_POST['data']))));
			die();
		}// end function pe2_process_shortcode()

		/**
		 * Escape quotes to html entinty
		 *
		 * @param <type> $str
		 * @return <type>
		 */
		function escape($str) {
			$str = preg_replace('/"/', '&quot;', $str);
			$str = preg_replace("/'/", '&#039;', $str);
			return $str;
		}

		function parse_caption($string){
			// determine if there was just a file-name returned as the caption,
			// if so simply make it a blank caption
			if($this->options['pe2_parse_caption'] == '1'){
				// perform the replacement
				return preg_replace('/^[^\s]+\.[^\s]+$/', '', $string);
			}else{
				// simply return the caption untouched
				return $string;
			}
		}// end function parse_caption(..)

		/**
		 * wp_ajax_pe2_save_state
		 * save state of dialog
		 */
		function save_state() {
			if (!current_user_can('picasa_dialog')) {
				echo json_encode((object) array('error'=>__('Insufficient privelegies','pe2')));
				die();
			}

			if (!isset($_POST['state'])) die();
			global $current_user;

			switch ( $saved_state = sanitize_text_field($_POST['state']) ) {
				case 'nouser' :
				case 'albums' :
					if ($this->options['pe2_level'] == 'user')
						update_user_meta($current_user->data->ID, 'pe2_saved_user_name', sanitize_text_field($_POST['last_request']) );
					else
						update_option( 'pe2_saved_user_name', sanitize_text_field($_POST['last_request']) );
					break;
				case 'images' :
					if ($this->options['pe2_level'] == 'user')
						update_user_meta($current_user->data->ID, 'pe2_last_album', sanitize_text_field($_POST['last_request']) );
					else
						update_option( 'pe2_last_album', sanitize_text_field($_POST['last_request']) );
					break;
				default:
					die();
			}
			if ($this->options['pe2_level'] == 'user')
				update_user_meta($current_user->data->ID, 'pe2_saved_state', $saved_state );
			else
				update_option( 'pe2_saved_state', $saved_state );
			die();
		}

		/**
		 * Envelope content with tag
		 * used by shortcode 'pe2_gallery'
		 *
		 * @param array $atts tag, class and style defined. album also
		 * @param string $content
		 * @return string
		 */
		function gallery_shortcode($atts, $content) {
			// go through any attributes and set string values of "false" 
			// to boolean false to fix lazy evaluation issues
			if(is_array($atts)){
				// we have at least one attribute, we can process them:
				foreach($atts AS $key => $value){
					if($atts[$key] === 'false'){
						// set this attribute to boolean false
						$atts[$key] = false;
					}
				}
			}

			// extract the attributes
			extract(shortcode_atts(array_merge(array(
					'tag'   => 'div',
					'album' => '',
				
					'thumb_w' => get_option('thumbnail_size_w'),
					'thumb_h' => get_option('thumbnail_size_h'),
					'thumb_crop' => get_option('thumbnail_crop'),
				
					'limit' => '',
					'hide_rest'  => ''
				), $this->options
			), $atts ));

			if ($album) {
				// request images for album - generate the request URL
				if(strpos($album, 'http') !== 0){
					// for backwards compatibility, decode the base64 encoded album data
					// stored in the tag
					$feed_url = base64_decode($album);
				}else{
					// simply store the album url after decoding any entities created by
					// the visual/HTML editor
					$feed_url = html_entity_decode($album);
				}

				// determine if we have any tags to send with the query
				if(isset($atts['tags'])){
					// we also have tags to query, append them
					$feed_url .= '&tag='.str_replace('-', '+', urlencode($atts['tags'])).'&orderby=date&showall';
				}

				// grab the data and process it
				$rss = $this->get_feed($feed_url);
				if (is_wp_error($rss)) {
					$content = $rss->get_error_message();
				} else if ($this->get_item($rss,'atom:id')) {
					$items = $this->get_item($rss,'item');
					$output = '';

					// determine if we're relating all images, or just those
					// in this gallery
					if($pe2_relate_images){
						// use the per-post unique ID so all images in the post
						// are related
						$uniqid = 'post-'.get_the_ID();
					}else{
						// generate a unique id for this gallery
						$uniqid = uniqid('');
					}

					// prepare common image attributes
					$iclass = explode(' ',$pe2_img_css);
					$istyle = array($pe2_img_style);

					// determine if we have a static thumbnail size, or if it needs to
					// be calculated per image based on the phototile template
					if($pe2_phototile != null){
						// this is a phototile, add our class to the main div
						$pe2_gal_css .= ' pe2-phototile';

						// force some other settings required for this to work
						// properly
						$pe2_align = 'left';

						// turn captions off
						$pe2_caption = 0;

						// now create our reference array for the various sizes
						// available in our template
						$tile_template_sizes = array();
						$tile_template_max_size = 0;
						foreach($this->pe2_phototile_template AS $tmp_tile_template){
							// store this if it doesn't already exist
							if(!in_array($tmp_tile_template['max'], $tile_template_sizes)){
								// add it
								$tile_template_sizes[] = $tmp_tile_template['max'];

								// see if this is the largest so far
								if($tmp_tile_template['max'] > $tile_template_max_size){
									$tile_template_max_size = $tmp_tile_template['max'];
								}
							}// end if we haven't stored this max size yet
						}// end foreach tile template we have
						unset($tmp_tile_template);

						// determine if we have a PHP version new enough to use the
						// 3rd parameter of round
						if(!function_exists('version_compare') || version_compare(PHP_VERSION, '5.3.0', '<')){
							// cannot use the 3rd parameter of round when performing
							// the phototile scaling
							$round_without_3rd_parameter = true;
						}
					}else{
						// standard gallery, calculate the thumbnail size from wp
						// settings
						$new_thumb_size = '';
						if ($thumb_w && $thumb_h) {
							// both sizes and crop
							if ($thumb_w == $thumb_h) {
								if ($thumb_crop) $new_thumb_size = '/s'.$thumb_w.'-c';
								else $new_thumb_size = '/s'.$thumb_w;
							}
							else if ($thumb_w > $thumb_h) $new_thumb_size = '/w'.$thumb_w;
							else $new_thumb_size = '/h'.$thumb_h;
						}
						else if ($thumb_w) $new_thumb_size = '/w'.$thumb_w;
						else if ($thumb_h) $new_thumb_size = '/h'.$thumb_h;

						// add the overlay option
						$new_thumb_size .= '-o';
					}// end else for if we're using a phototile gallery

					// create align vars
					// for caption - align="alignclass" including alignnone also
					$calign = '';
					if ($pe2_caption) {
						$calign = 'align="align'.$pe2_img_align.'" ';
					}

					// new size for large image
					$new_large_size='/s0';
					if ($pe2_large_limit) $new_large_size = '/'.$pe2_large_limit;

					$cdim  = ($thumb_w)?('width="'.$thumb_w.'" '):'';

					// determine if we have an a_img_css
					if($pe2_a_img_css != null){
						$aclass = ' '.$pe2_a_img_css;
					}else{
						$aclass = '';
					}
					if($pe2_a_img_style != null){
						$astyle = ' style="'.$pe2_a_img_style.'"';
					}else{
						$astyle = '';
					}

					// link and gallery additions
					$amore='';
					switch ($pe2_link) {
						case 'thickbox':
						case 'thickbox_integrated':
						case 'thickbox_custom':
							$amore = 'class="thickbox'.$aclass.'" rel="'.$uniqid.'" ';
							break;
						case 'lightbox':
							$amore = 'class="'.$aclass.'" rel="lightbox-'.$uniqid.'" ';
							break;
						case 'highslide':
							$amore = 'class="highslide'.$aclass.'" onclick="return hs.expand(this,{ slideshowGroup: \''.$uniqid.'\' })" ';
							break;
						case 'photoswipe':
							$amore = 'class="photoswipe'.$aclass.'" rel="'.$uniqid.'" ';
							break;
					}

					// append the astyle to the amore
					$amore .= $astyle;

					// set image classes and styles
					$iclass = implode(' ',array_diff($iclass,array(''))); $iclass = ($iclass)?('class="'.$iclass.'" '):'';
					$istyle = implode(' ',array_diff($istyle,array(''))); $istyle = ($istyle)?('style="'.$istyle.'" '):'';
					
					$key = 1; $images = array();
					
					if ($items) {
						if (!is_array($items)) $items = array($items);

						// if we're searching by tags, the RSS feed returned the results
						// in the order of most-recent first, which doesn't make very
						// much sense when considering how this works.  reverse teh 
						// array order
						if(isset($atts['tags'])){
							// we searched for tags and messed up the order, perform our
							// sorting on the items array to correct the order
							$items = array_reverse($items);
							usort($items, 'PicasaExpressX2::pe2_items_sorting_callback');
						}

						// loop through each and build the HTML
						$image_count = 0;
						foreach($items as $item) {
							// keep track of the image number we're on
							$image_count++;

							// init/reset/any prefix/suffix
							$tile_wrap_prefix = $tile_wrap_suffix = '';

							// -------------------------------------------------------------
							// gallery type
							if($pe2_phototile != null){
								// we're tiling the photos, using our width, define the
								// dimensions using our template array of size combinations

								// if necessary, start a new row
								if(!isset($tile_row)){
									// randomly select a row from the template
									$tmp = 0;
									do{
										$tmp++; if($tmp > 100) break;
										// select a tile row
										$tile_row = rand(0, count($this->pe2_phototile_template) - 1);

										// determine if this size is appropriate for the 
										// number of photos that remain to be displayed
										$continue_looping = false;
										$tmp_tile_images_left = count($items) - $image_count + 1;
										if($tmp_tile_images_left == 0){
											// we're all done
											break;
										}elseif($tmp_tile_images_left <= ($tile_template_max_size + 1)){
											// we need to confirm that our selected row is
											// appropriate
											if(in_array($tmp_tile_images_left, $tile_template_sizes)){
												// we have one that matches exactly, force one
												// of those
												if($this->pe2_phototile_template[$tile_row]['max'] == $tmp_tile_images_left){
													// we selected one that's ok, we can stop
													// looping
													$continue_looping = false;
												}else{
													// we haven't selected one of the templates
													// that fits this size, force a loop right now
													// that locates the correct matching record
													foreach($this->pe2_phototile_template AS $tmp_key => $tmp_row){
														// if we find the one with the correct max value,
														// then we select it and break
														if($tmp_row['max'] == $tmp_tile_images_left){
															// use this one
															$tile_row = $tmp_key;
															unset($tmp_key, $tmp_row);
															break 2;
														}
													}// end foreach template record
												}// end else if our randomly selected row matches
											}elseif($tmp_tile_images_left == 1){
												// we need to find a template that serves 2
												// images, that's the best we can do
												if($this->pe2_phototile_template[$tile_row]['max'] == 2){
													// we're ok
													$continue_looping = false;
												}else{
													// not ok, keep searching
													$continue_looping = true;
												}
											}else{
												// we don't have an exact match and we have
												// more than 1 photo left, find one that
												// is smaller than our current number
												if(($this->pe2_phototile_template[$tile_row]['max'] + 1) < $tmp_tile_images_left){
													// we're ok, we're less than the images left,
													// but our remainder must be at least 2, thus
													// preventing leaving just 1 photo left in the
													// last row
													$continue_looping = false;
												}else{
													// not ok, keep searching
													$continue_looping = true;
												}
											}
										}// end if we have a small number and the template is important
									}while($continue_looping);
									unset($tmp_tile_images_left, $continue_looping);

									// initialize some variables for this row
									$tile_row_column = 0;
									$tile_row_count = 0;
									$tile_row_row_count = 0;

									// cols is the count of the format array
									$row_order = $this->pe2_phototile_template[$tile_row]['format'];
									shuffle($row_order);

									// reset round up/down
									$round_down = false;

									// set up our wrapper for the row
									$tile_wrap_prefix = '<div style="float: left; width: '.$pe2_phototile.'px;">';
								}// end if we need to start a new row

								// go through the row order and get the template dimensions
								// for the column we're currently on
								if(!isset($row_order[$tile_row_column]['w'])){
									// we have sub-arrays for a multi-row phototile, 
									// determine where we are
									if(!isset($tile_row_row)){
										// we're just entering this multi-row section,
										// set it up
										$tile_row_row = rand(0, 1);
										$tile_row_row_column = 0;
										$tile_row_row_count++;

										// check to see if we need to create the container
										// for the images (if the multi-row is the first
										// tile column)
										if($tile_row_column == 0){
											// we're doing the multi-row column first, we
											// need our wrapper
											$tile_multi_row_wrapper = true;
											$tile_wrap_prefix .= '<div style="float: left; width: '.($row_order[$tile_row_column]['width'] / 1000 * $pe2_phototile).'px;">';
										}
									}elseif($tile_row_row_column == (count($row_order[$tile_row_column][$tile_row_row]) - 1)){
										// we're on the very last image for this row,
										// flag the rounding check
										$tile_row_row_round_check = true;
									}elseif($tile_row_row_column >= count($row_order[$tile_row_column][$tile_row_row])){
										// we've surpassed the images for this row, we
										// need to start the next
										$tile_row_row = ($tile_row_row == 1 ? 0 : 1);
										$tile_row_row_column = 0;
										$tile_row_row_count++;
									}

									// check to see if we've surpassed the images for
									// this tile_row_column
									if($tile_row_row_count <= 2){
										// check to see if we need to randomize our row-row
										if($tile_row_row_column == 0){
											// randomize this array before using it
											$row_order_tmp = $row_order[$tile_row_column][$tile_row_row];
											shuffle($row_order_tmp);
										}

										// assign the current column to the tile_row_row_column
										$current_column = $tile_row_row_column;

										// mark that we're on a multi-row column
										$tile_multi_row = true;
									}else{
										// we're done with this row_row column, 
										// increment our $tile_row_column counter
										$tile_row_column++;

										// if we need to end our wrapper, do it now
										if(isset($tile_multi_row_wrapper)){
											// define our prefix
											$tile_wrap_prefix = '</div>';
										}

										// and simulate the variables for a
										// standard row (same code as the else
										// immediately below)
										$row_order_tmp = $row_order;
										$current_column = $tile_row_column;
										$tile_multi_row = false;
									}
								}else{
									// this is a direct image configuration for a
									// non-multi row, assign the row_order_tmp
									// simply from $row_order
									$row_order_tmp = $row_order;

									// assign the current column to the $tile_row_column
									$current_column = $tile_row_column;

									// mark that we're on a single-row column
									$tile_multi_row = false;
								}// end else for if we have a multi-row row

								// define our width, subtracting 10 pixels for image pad
								$new_width = ($row_order_tmp[$current_column]['w'] / 1000 * $pe2_phototile) - 4;
								if($new_width != round($new_width)){
									// store our original width temporarily
									$tmp_new_width = $new_width;

									// we need to round, determine if we round half-up
									// or half-down which should hopefully make multiple
									// .5 rounds add up correctly
									if(!isset($round_without_3rd_parameter) && $round_down){
										// we're going down
										$new_width = round($new_width, null, PHP_ROUND_HALF_DOWN);
									}else{
										// we're rounding normally
										$new_width = round($new_width);
									}
									$round_down = !$round_down;

									// now figure out the difference between the two
									$new_width_round_diff = $tmp_new_width - $new_width;
								}else{
									// width round diff = 0
									$new_width_round_diff = 0;
								}// end else for if we need to round

								// define our height
								$new_height = round($row_order_tmp[$current_column]['h'] / 1000 * $pe2_phototile);

								// perform any special processing for multi-row
								if($tile_multi_row){
									// we need to subtract 1 pixel from our height so that
									// we account for the padding between rows
									$new_height = $new_height - 1;

									// sum up our rounding differences
									$new_width_round_diff_total += $new_width_round_diff;

									// check to see if we need to calculate our rounding
									// difference and adjust this image's width
									if(isset($tile_row_row_round_check)){
										// we need to perform the check
										unset($tile_row_row_round_check);

										// now adjust the width of the last image
										// in the row to help compensate
										$new_width += round($new_width_round_diff_total);

										// reset our round_diff_total
										$new_width_round_diff_total = 0;
									}
								}// end if we're on multi-row

								// generate this image's thumbnail size from the phototile
								// template
								#print "tr=$tile_row cc=$current_column count=$tile_row_count trc=$tile_row_column trr=$tile_row_row trrc=$tile_row_row_column w={$row_order_tmp[$current_column]['w']} h={$row_order_tmp[$current_column]['h']} new_thumb_size=/w$new_width-h$new_height-p-o-k<br/>";
								$new_thumb_size = "/w$new_width-h$new_height-p-k-o";

								// determine if we're done with this row
								$tile_row_count++;
								if($tile_row_count >= $this->pe2_phototile_template[$tile_row]['max']){
									// we're done with this row, unset the $tile_row variable
									// so it gets re-calculated during the next iteration
									unset($tile_row, $tile_row_row, $tile_multi_row_wrapper);

									// and add our suffix for the end of the row
									$tile_wrap_suffix = '</div>';
								}elseif(!isset($row_order[$tile_row_column]['w'])){
									// this is a multi-row column, increment our row-row column 
									// counter appropriately
									$tile_row_row_column++;
								}else{
									// this is a standard column, increment our column counter
									// appropriately
									$tile_row_column++;
								}
							}// end if we're using phototile and need to calculate this image's size

							// -------------------------------------------------------------
							// other information for this item
							switch ((string)$pe2_img_sort) {
								case 'None':
								case 'none':
								case '0': $key++; break;
								case 'date':
								case '1': $key = strtotime($this->get_item($item,'pubDate',true)); break;
								case 'Title':
								case 'title':
								case '2': $key = $this->get_item($item,'title',true); break;
								case 'File name':
								case 'File':
								case 'file':
								case '3': $key = $this->get_item($item,'media:title',true); break;
								case 'Random':
								case 'random':
								case '4': $key = rand(); break;
								default: $key++; break;
							}
							$url = $this->get_item_attr($item,'media:thumbnail','url');
							$title = $this->parse_caption($this->escape($this->get_item($item,'title')));
							$picasa_link = $this->get_item($item, 'link');
							$images[$key] = array (
								'ialbum'   => $this->get_item($item,'link'), // picasa album image
								'icaption' => $title,
								'ialt'     => $this->escape($this->get_item($item,'media:title')),
								'isrc'     => str_replace('/s72',$new_thumb_size,$url),
								'iorig'    => str_replace('/s72',$new_large_size,$url),
								'ititle'   => ($pe2_title)?'title="'.$title.'" ':'',
								'ilink'    => $picasa_link,
//FIXME - CSS needs to be corrected
								//'itype'	   => (strpos($item, 'medium=\'video\'') !== false ? 'video' : 'image')
								'itype'	   => '',
								'prefix'    => $tile_wrap_prefix,
								'suffix'    => $tile_wrap_suffix
							);
							if ($limit && !$hide_rest) {
								if (++$count>=$limit) break;
							}
						}// end foreach items to process
						if ($pe2_img_asc) ksort($images);
						else krsort($images);
						
						if ($limit && $hide_rest && $limit==absint($limit)) $count=0;
						else $limit=false;
						
						foreach($images as $item) {
							$img = "<img src=\"{$item['isrc']}\" alt=\"{$item['ialt']}\" type=\"{$item['itype']}\" {$item['ititle']}{$iclass}{$istyle} />";

							if ($pe2_link != 'none') {
								if ($pe2_link == 'picasa') $item['iorig'] = $item['ialbum'];

								// determine if this particular link has been displayed
								// already or not (to prevent multiple copies related
								// to each other from busting the navigation)
								if(in_array($item['iorig'], $this->photos_displayed)){
									// this photo has already been displayed, skip relating
									// it to the rest and instead make up a new relationship
									// for it so that we don't break the navigation
									$amore_this = str_replace($uniqid, uniqid(), $amore);
								}else{
									// this photo hasn't been displayed yet, it can be related
									// without issue
									$amore_this = $amore;
								}

								// store this photo in our list of displayed photos
								$this->photos_displayed[] = $item['iorig'];

								// create the image link
								$img = "<a href=\"{$item['iorig']}\" link=\"{$item['ilink']}\" {$item['ititle']}{$amore_this}>$img</a>";
							}
							if ($pe2_caption) {
								// add caption
//FIXME - add any pe2_caption attributes to the caption shorttag
								$img = "[caption id=\"\" {$calign}{$cdim}caption=\"{$item['icaption']}\"]{$img}[/caption] ";
							}

							// wrap our img/a/caption with the prefix/suffix
							$img = $item['prefix'].$img.$item['suffix'];

							// append this image to our output
							$output .= $img;
							
							if ($limit) {
								if (++$count>=$limit) {
									$istyle=$hstyle;
									$amore .= ' style="display:none;"';
									$pe2_caption=false;
								}
							}
						}// end going through all of the images in the gallery

						// 
						$pe2_gal_css = array_merge( array(($pe2_gal_align!='none')?'align'.$pe2_gal_align:''), explode(' ',$pe2_gal_css) );
						$pe2_gal_css = array_diff($pe2_gal_css, array('') );
						$pe2_gal_css = implode(' ', $pe2_gal_css );

					}// end if we have items
				}//end if we were able to get the rss
				$content .= $output;
			}// end if($album) ???

			// check to see if we're setting up a phototile
			if($pe2_phototile != null){
				// we're using a phototile, so we need to constrain our gallery
				// container to the width of the phototile, thus not letting any
				// funky wrapping occur
				// determine if we already have a width in the pe2_gal_style, and if
				// not, set our width for the container to 
				if(strpos($pe2_gal_style, 'width:') === false){
					// there isn't a width style already, set it
					$pe2_gal_style = 'width: '.$pe2_phototile.'px;'.$pe2_gal_style;
				}
			}// end if we're doing a phototile

			// create the gallery code and return it
			$code = "<$tag class=\"pe2-album $pe2_gal_css\" style=\"$pe2_gal_style\">".do_shortcode($content)."</$tag><div class='clear'></div>";
			return $code;
		}// end function gallery_shortcode(..)

		/**
		 * Callback function to assist with sorting the items array returned by
		 * a "tag" search to Google+, ordering by EXIF photo taken date
		 *
		 * @param mixed $item1
		 * @param mixed $item2
		 * @return int -> -1 if item1 < item2, 0 if item1 == item2, 1 if item1 > item2
		 */
		function pe2_items_sorting_callback($item1, $item2) {
			// determine which of the two elements is greater
			$item1 = $this->pe2_items_sorting_get_timestamp($item1);
			$item2 = $this->pe2_items_sorting_get_timestamp($item2);

			// determien if we were successfully able to get both timestamps
			if(($item1 === false) || ($item2 === false)){
				// darn, can't sort
				return 0;
			}

			// return the difference in the two
			return $item1 - $item2;
		}// end function pe2_items_sorting_callback(..)

		/**
		 * Function to assist with the above callback in parsing an RSS item's 
		 * EXIF image date and returning it as a timestamp
		 *
		 * @param string $item
		 * @return int the resulting timestamp
		 */
		function pe2_items_sorting_get_timestamp($item){
			// get just the content of the <description> tag
			$tmp = $this->get_item($item, 'description');
			// strip off the garbage ahead of Date:
			$tmp = substr($tmp, strpos($tmp, 'Date:'));
			// decode the HTML enties
			$tmp = html_entity_decode($tmp);
			// strip off all after and including the next <br/>
			$tmp = substr($tmp, 0, strpos($tmp, '<br/>'));
			// strip out any other garbage HTML tags and trim it
			$tmp = trim(strip_tags($tmp));
			// strip off the "Date: " prefix
			$tmp = substr($tmp, 6);
			// return the strtotime
			return strtotime($tmp);
		}// end function pe2_items_sorting_get_timestamp(..)

		/**
		 * Envelope content with tag
		 * used by shortcode 'pe2_image'
		 *
		 * @param array $atts tag, class and style defined.
		 * @param string $content
		 * @return string
		 */
		function image_shortcode($atts, $content) {
			// extract all of the variables from defaults/options with
			// any tag attribute overrides

			// go through any attributes and set string values of "false" 
			// to boolean false to fix lazy evaluation issues
			foreach($atts AS $key => $value){
				if($atts[$key] === 'false'){
					// set this attribute to boolean false
					$atts[$key] = false;
				}
			}

			// extract our attributes
			extract(shortcode_atts(array_merge(array(
						'src'	=> '',
						'href'	=> '',
						'caption'	=> '',
						'type'	=> '',
						'alt'	=> '',
						'limit' => '',
						'hide_rest'  => ''
					), $this->options
				), $atts )
			);

			// create align vars
			// for caption - align="alignclass" including alignnone also
			// else add alignclass to iclass
			$calign = '';
			$iclass = array();
			if ($pe2_caption) {
				// captions have a surrounding div that must be aligned properly
				$calign = 'align="align'.$pe2_img_align.'" ';
			}
			// also put the align variable on the image itself
			array_push($iclass,'align'.$pe2_img_align);

			if($pe2_a_img_css != null){
				$aclass = ' '.$pe2_a_img_css;
			}else{
				$aclass = '';
			}
			if($pe2_a_img_style != null){
				$astyle = ' style="'.$pe2_a_img_style.'"';
			}else{
				$astyle = '';
			}

			// generate the unique id if we're relating images
			$uniqid = 'post-'.get_the_ID();

			// link and gallery additions
			$a_link_additions = '';
			switch ($pe2_link) {
				case 'thickbox':
				case 'thickbox_integrated':
				case 'thickbox_custom':
					$a_link_additions = 'class="thickbox'.$aclass.'"'.$astyle.' ';
					if($pe2_relate_images){
						// they have chosen to relate all of the images, use the post id
						$a_link_additions .= 'rel="'.$uniqid.'" ';
					}
					break;
				case 'lightbox':
					if($pe2_relate_images){
						// they have chosen to relate all of the images, use the post id
						$a_link_additions = 'rel="lightbox-'.$uniqid.'" ';
					}else{
						// separate images without navigation
						$a_link_additions = 'rel="lightbox" ';
					}
					$a_link_additions .= 'class="'.$aclass.'"'.$astyle.' ';
					break;
				case 'highslide':
					if($pe2_relate_images){
						// they have chosen to relate all of the images, use the post id
						$a_link_additions = 'class="highslide'.$aclass.'"'.$astyle.' onclick="return hs.expand(this,{ slideshowGroup: \''.$uniqid.'\' })"';
					}else{
						// separate images without navigation
						$a_link_additions = 'class="highslide'.$aclass.'"'.$astyle.' onclick="return hs.expand(this)"';
					}
					break;
				case 'photoswipe':
					$a_link_additions = 'class="photoswipe'.$aclass.'" ';
					if($pe2_relate_images){
						// they've chosen to relate, use the post id
						$a_link_additions .= 'rel="'.$uniqid.'" ';
					}
					break;
			}// end switch
			$a_link_additions .= $astyle;
			
			// determine the type and then set the thumbnail url
			$amore = '';
			$imore = '';
			if($type == 'image'){
				// use the image size
				$thumb_size = $pe2_single_image_size;

				// set the link href to the large size.  determine if the
				// size has been defined, or if we just use the default
				if($pe2_large_limit == null){
					// none set, use a default
					$large_size = 's0';
				}else{
					// use the large limit from the configuration
					$large_size = $pe2_large_limit;
				}

				// create the a link, linking to the larger version of the image
				$a_href = preg_replace('/\/(w|h|s)[0-9]+(-c-o|-c|-o|)\//', '/'.$large_size.'/', $src);

				// set the amore to our a_link_additions
				$amore = $a_link_additions;
			}else{
				// use the video size
				$thumb_size = $pe2_single_video_size;

				// set the link href to the picasa HREF
				$a_href = $href;

				// set the amore to make it open in a new tab and mark it as a video
				// type, and not add in the a_link_additions configuration
				$amore .= ' target="_blank" type="video"';
				$imore = ' type="video"';
			}// end else for if we're displaying an image

			// determine width if captions are enabled
			if($pe2_caption){
				// extract (or calculate) the width for the caption box
				if(preg_match('/(w|h|s)([0-9]+)(-c-o|-c|-o|)/', $thumb_size, $matches) > 0){
					// we were able to match it, figure out what our width is
					if($matches[1] == 'w'){
						// our width is this number
						$cwidth = $matches[2];
					}elseif(($matches[1] == 's') && (strpos($matches[3], '-c') !== false)){
						// our width is always = height in a square, we can use
						// the raw number
						$cwidth = $matches[2];
					}else{
						// for height or uncropped square, we have no idea what the 
						// width is going to be.  This is a very tricky situation.  
						// The width really needs to be determined via JavaScript.
						// Perform our best guess here (which is not a good one as
						// it only works for portrait photos), then include a 
						// bit of JavaScript that will adjust the width appropriately
						// on page load
						$cwidth = round($matches[2] * 3 / 4);

						// set our variable to enable the JavaScript calculation
						$GLOBALS['pe2_include_caption_width_correction_javascript'] = true;
					}
				}else{
					// unable to parse the thumb size, simply set to a numeric 
					// value of something small, then enable the JavaScript to 
					// calculate the width
					$cwidth = 75;

					// set our variable to enable the JavaScript calculation
					$GLOBALS['pe2_include_caption_width_correction_javascript'] = true;
				}
			}// end if we need to calculate width for the caption

			// generate the URL for the thumbnail image
			$thumb_src = preg_replace('/\/(w|h|s)[0-9]+(-c-o|-c|-o|)\//', '/'.$thumb_size.'-o/', $src);

			// add our pe2 class to the image class
			$iclass[] = 'pe2-photo';

			// generate the other image attributes we need
			$ititle = ($pe2_title) ? 'title="'.$caption.'" ' : '';
			$iclass = implode(' ', $iclass);
			if($pe2_img_css){
				$iclass .= ' '.$pe2_img_css;
			}
			if($iclass){
				$iclass = 'class="'.$iclass.'" ';
			}
			if($pe2_img_style){
				$istyle = 'style="'.$pe2_img_style.'" ';
			}else{
				$istyle = '';
			}

			// create the HTML for the image tag
			$html = "<img src=\"{$thumb_src}\" alt=\"{$alt}\" {$ititle}{$iclass}{$istyle}{$imore} />";

			// add the link?
			if ($pe2_link != 'none') {
				// the image should also have a link, determine if this particular 
				// link has been displayed already or not (to prevent multiple 
				// copies related to each other from busting the navigation)
				if(in_array($a_href, $this->photos_displayed)){
					// this photo has already been displayed, skip relating
					// it to the rest and instead make up a new relationship
					// for it so that we don't break the navigation
					$amore_this = str_replace($uniqid, uniqid(), $amore);
				}else{
					// this photo hasn't been displayed yet, it can be related
					// without issue
					$amore_this = $amore;
				}

				// store this photo in our list of displayed photos
				$this->photos_displayed[] = $a_href;
				
				// figure out what the link is
				if($pe2_link == 'picasa'){
					// the large_url gets switched for the href
					$a_href = $href;
				}

				// wrap the current image tag with the A tag, adding the "link" 
				// attribute so the thickbox-custom can add the link to picasa
				$html = "<a href=\"{$a_href}\" link=\"{$href}\" {$ititle}{$amore_this}>$html</a>";
			}// end if we need to add the link

			if ($pe2_caption) {
				// add caption
				$html = "[caption id=\"\" {$calign} width=\"{$cwidth}\" caption=\"{$caption}\"]{$html}[/caption] ";
			}

			// return our processed shortcode with teh image link
			return do_shortcode($html);
		}// end function image_shortcode(..)

		/**
		 * Envelope content with tag with additinoal class 'clear'
		 * used by shortcode 'clear'
		 *
		 * @param array $atts tag and class
		 * @param string $content
		 * @return string
		 */
		function clear_shortcode($atts, $content) {
			extract(shortcode_atts(array(
				'class' => '',
				'tag'   => 'div',
			), $atts ));

			$class .= (($class)?' ':'').'clear';

			$code = "<$tag class='$class'>".do_shortcode($content)."</$tag>";

			return $code;
		}

		/**
		 * Print and request user for Google in profile. Token link present
		 * uses if settings in user level
		 * run by action 'show_user_profile' from user-edit.php
		 *
		 * @param object $user
		 */
		function user_profile($user) {

			if (!current_user_can('picasa_dialog')) return;
			if ($this->options['pe2_level'] != 'user') return;

			$user_id = $user->ID;

			if ( isset($_GET['revoke']) ) {
				$response = $this->get_feed("https://www.google.com/accounts/AuthSubRevokeToken");
				if ( is_wp_error( $response ) ) {
					$message = __('Google returned error: ','pe2').$response->get_error_message();
				} else {
					$message = __('Private access revoked','pe2');
				}
				delete_user_meta($user_id,'pe2_token');
				$this->options['pe2_token'] = '';
			}

			if ( isset($_GET['message']) && $_GET['message']) {
				$message = esc_html(stripcslashes($_GET['message']));
			}


			if (!get_user_meta($user_id,'pe2_user_name',true) && current_user_can('manage_options') ) {
				update_user_meta($user_id,'pe2_user_name',$this->options['pe2_user_name']);
				if ($this->options['pe2_token'])
					update_user_meta($user_id,'pe2_token',$this->options['pe2_token']);
			} 

			?>
				<h3><?php _e('Google+ Express access', 'pe2') ?></h3>

				<?php
					if ($message) {
						echo '<div id="picasa-express-x2-message" class="updated"><p><strong>'.$message.'</strong></p></div>';
					}
				?>

				<table class="form-table">
					<?php
					$user = get_user_meta($user_id,'pe2_user_name',true);
					$result = 'ok';
					$feed_url = "http://picasaweb.google.com/data/feed/base/user/$user?alt=rss&kind=album&hl=en_US";
					$response = $this->get_feed($feed_url);
					if ( is_wp_error( $response ) )
						$result = 'error: '.$response->get_error_message();
				    else if (!$this->get_item($response,'atom:id')) {
						$result = 'error: wrong answer';
					}

					if (method_exists('WP_Http', '_getTransport')) {
						$ta = array(); $transports = WP_Http::_getTransport(array());
						foreach ($transports as $t) $ta[] = strtolower(str_replace('WP_Http_','',get_class($t)));
						if ($ta) $result = sprintf(__("Checking user: %s - Transport: %s - <a href=\"%s\" target=\"_blank\">URL</a>",'pe2'),$result,implode(',',$ta),$feed_url);
					} else if (method_exists('WP_Http', '_get_first_available_transport')) {
						$transport = WP_Http::_get_first_available_transport(array());
						if ($transport) {
							$transport_name = strtolower(str_replace('WP_HTTP_','',$transport));
							$result = sprintf(' '.__("Checking user: %s - Transport: %s - <a href=\"%s\" target=\"_blank\">URL</a>",'pe2'),$result,$transport_name,$feed_url);
						}
					} else {
						$result = '';
					}

					$this->make_settings_row(
						__('Google user name', 'pe2'),
						'<input type="text" class="regular-text" name="pe2_user_name" value="'.esc_attr($user).'" />'.$result.
						((!get_user_meta($user_id,'pe2_token',true))?'<br /><a href="https://www.google.com/accounts/AuthSubRequest?next='.urlencode($this->google_authorize_plugin_URL.'?authorize&user='.$user_id).'&scope=http%3A%2F%2Fpicasaweb.google.com%2Fdata%2F&session=1&secure=0">'.__('Requesting access to private albums', 'pe2').'</a>':'<br/><a href="?revoke=true">'.__('Revoke access to private albums', 'pe2').'</a>'),
						((get_user_meta($user_id,'pe2_token',true))?__('You already received the access to private albums', 'pe2'):__('By this link you will be redirected to the Google authorization page. Please, use same name as above to login before accept.', 'pe2'))
					);
					$option = get_user_meta($user_id,'pe2_save_state',true);
					$this->make_settings_row(
						__('Save last state', 'pe2'),
						'<label><input type="checkbox" name="pe2_save_state" value="1" '.checked($option,'1',false).' /> '.__('Save last state in dialog', 'pe2').'</label> ',
						__('Save user when changes, album if you insert images or albums list if you shorcode for album', 'pe2')
					);
					?>
				</table>
			<?php
		}

		/**
		 * Save parameters and save profile
		 * by action 'personal_options_update' in user-edit.php
		 */
		function user_update() {

			if (!current_user_can('picasa_dialog')) return;

			$user_id = sanitize_text_field($_POST['user_id']);
			if ($user_id && isset($_POST['pe2_user_name']) && sanitize_text_field($_POST['pe2_user_name']) != get_user_meta($user_id,'pe2_user_name',true)) {
				$picasa_user = sanitize_text_field($_POST['pe2_user_name']);
				if (!$picasa_user) $picasa_user='undefined';
				update_user_meta($user_id,'pe2_user_name', $picasa_user);
				delete_user_meta($user_id,'pe2_token');
			}
			update_user_meta($user_id,'pe2_save_state', ((isset($_POST['pe2_save_state']) && $_POST['pe2_save_state'])?'1':'0'));
		}

		/**
		 * Add setting link to plugin action
		 * run by action 'plugin_action_links_*'
		 *
		 */
		function add_settings_link($links) {
			if (!current_user_can('manage_options')) return $links;
			$settings_link = '<a href="options-general.php?page=picasa-express-2">'.__('Settings', 'pe2').'</a>';
			array_unshift( $links, $settings_link );
			return $links;
		}

		/**
		 * Config settings, add actions for registry setting and add styles
		 * run by action 'admin_menu'
		 *
		 */
		function add_settings_page() {
			if (!current_user_can('manage_options')) return;
			add_options_page(__('Picasa and Google Plus Express', 'pe2'), __('Picasa and Google Plus Express', 'pe2'), 'manage_options', 'picasa-express-2', array(&$this, 'settings_form'));
			add_action('admin_init', array(&$this, 'settings_reg'));
			add_action('admin_print_styles-settings_page_picasa-express-2', array(&$this, 'settings_style'));
		}

		/**
		 * Register all option for save
		 *
		 */
		function settings_reg() {
			foreach ($this->options as $key => $option) {
				if ($key != 'pe2_token') // skip token in non secure requests
					register_setting( 'picasa-express-2', $key );
			}
		}

		/**
		 * Define misseed style for setting page
		 */
		function settings_style() {
			$images = admin_url('images');
			echo<<<STYLE
			<style type="text/css" id="pe2-media" name="pe2-media">
				.image-align-none-label {
					background: url($images/align-none.png) no-repeat center left;
				}
				.image-align-left-label {
					background: url($images/align-left.png) no-repeat center left;
				}
				.image-align-center-label {
					background: url($images/align-center.png) no-repeat center left;
				}
				.image-align-right-label {
					background: url($images/align-right.png) no-repeat center left;
				}
			</style>
STYLE;
		}

		/**
		 * Add help to the top of the setting page
		 */
		function contextual_help($help, $screen) {
			if ( 'settings_page_picasa-express-2' == $screen ) {
				$homepage = __('Plugin homepage','pe2');
				$messages = array(
					__('To receive access for private album press link under username. You will be redirected to Google for grant access. If you press "Grant access" button you will be returned to settings page, but access will be granted.','pe2'),
					__("In the album's images you have to press button with 'Image' button. The 'Gallery' will appear on the button and you can select several images. This can be happen if you use Thickbox, Lightbox or Highslide support.",'pe2'),
					__("By default images inserted in the displayed order. If you need control the order in gallery - enable 'Selection order'.", 'pe2'),
					__('To use external libraries like Thickbox, Lightbox or Highslide you need to install and integrate the library independently','pe2'),
					);
				$message = '<p>'.implode('</p><p>',$messages).'</p>';
				$help .= <<<HELP_TEXT
				<h5>Small help</h5>
				$message
				<div class="metabox-prefs">
					<a href="http://wordpress.org/extend/plugins/picasa-express-x2">$homepage</a>
				</div>
HELP_TEXT;
			}
			return $help;
		}

		/**
		 * Make the row from parameters for setting tables
		 */
		function make_settings_row($title, $content, $description='', $title_pars='', $description_pars='') {
			?>
					<tr valign="top" <?php echo $title_pars; ?>>
			        <th scope="row"><?php echo $title; ?></th>
			        <td>
						<?php echo $content; ?>
			        	<br />
			        	<span class="description" <?php echo $description_pars; ?>><?php echo $description; ?></span>
			        </td>
			        </tr>
			<?php
		}

		/**
		 * Show the main settings form
		 */
		function settings_form(){

			if ( 
				( isset($_GET['updated']) && 'true' == $_GET['updated'] ) ||
				( isset($_GET['settings-updated']) && 'true' == $_GET['settings-updated'] ) 
			) {
				// successfully performed an update, execute any custom
				// logic that can't be performed by automatic settings storage

				// change 'picasa_dialog' capability to new role
				$roles = get_editable_roles();

				foreach ( $roles as $role => $data) {
					$_role = get_role($role);
					if (isset($this->options['pe2_roles'][$role]) && $this->options['pe2_roles'][$role]) {
						$_role->add_cap('picasa_dialog');
					} else {
						$_role->remove_cap('picasa_dialog');
					}
				}

				// update the path to wordpress, using a PHP include so that
				// the file cannot be read by a request directly from the web
				// For more information, see line ~2430
				if(!file_exists(dirname(__FILE__).'/pe2-wp-path.php')){
					// create the file containing the path
					@file_put_contents(dirname(__FILE__).'/pe2-wp-path.php', '<?php // AUTO-GENERATED by picasa-express-2 settings page, used in google token retrival
$pe2_wp_path = \''.str_replace('wp-admin', '', getcwd()).'\';');
				}// end if we need to generate the wordpress path storage file
			}// end successful settings update

			if ( isset($_GET['revoke']) ) {
				$response = $this->get_feed("https://www.google.com/accounts/AuthSubRevokeToken");
				if ( is_wp_error( $response ) ) {
					$message = __('Google return error: ','pe2').$response->get_error_message();
				} else {
					$message = __('Private access revoked','pe2');
				}
				delete_option('pe2_token');
				$this->options['pe2_token'] = '';
			}

			if ( isset($_GET['message']) && $_GET['message']) {
				$message = esc_html(stripcslashes($_GET['message']));
			}

			?>

			<div class="wrap">
			<div id="icon-options-general" class="icon32"><br /></div>
			<h2><?php _e('Picasa and Google Plus Express settings', 'pe2')?></h2>

			<?php
				if (isset($message) && $message) {
					echo '<div id="picasa-express-x2-message" class="updated"><p><strong>'.$message.'</strong></p></div>';
				}
			?>

			<form method="post" action="options.php">
    			<?php settings_fields( 'picasa-express-2' ); ?>

				<?php if ($this->options['pe2_donate_link']) { ?>
				<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=SLJSLP2325MNE" target="_blank" style="position:relative;display:block;float:right;margin:10px;height: 10px;">
					<img src="http://lh6.ggpht.com/_P3-vavBhxe8/TKAJwocDI3I/AAAAAAAAAhQ/VZ9rmzWqXA4/s128/Paypal_button1.png" alt="PayPal donate" title="Donate via PayPal to support plugin development" width="128" height="62" />
				</a>
				<?php } ?>

				<input type="hidden" name="pe2_configured" value="1" />
<?php

				// call the function to get the shared options between these
				// preferences and the option-overrides in the dialog
				$this->pe2_shared_options(true);

				// finish the form with the submit button
?>
				<p class="submit">
			    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			    </p>

			</form>
			</div>
			<?php
		}// end function settings_form()

		// add the public display css
		function pe2_add_display_css(){
			// add the pe2-display.css file
			wp_enqueue_style('pe2-display.css',plugins_url('/pe2-display.css', __FILE__), null, PE2_VERSION);
		}// end function pe2_add_display_css()

		// add the built-in wordpress thickbox script (if they selected the option)
		function pe2_add_thickbox_script(){
			// add in the thickbox script built into wordpress if not in the admin
			// and the user has selected thickbox as the display method
			wp_enqueue_script('thickbox',null,array('jquery'));
			wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, '1.0');
		}// end function pe2_add_thickbox_script()

		// add the custom thickbox script included with this plugin (if they selected
		// the option)
		function pe2_add_custom_thickbox_script(){
			// add in the thickbox script built into wordpress if not in the admin
			// and the user has selected thickbox as the display method
			wp_enqueue_script('jquery');
			wp_enqueue_style('thickbox.css', '/'.WPINC.'/js/thickbox/thickbox.css', null, PE2_VERSION);
			add_action('wp_footer', array(&$this, 'pe2_add_custom_thickbox_config'));
		}// end function pe2_add_custom_thickbox_script()
		function pe2_add_custom_thickbox_config(){
			?><script type='text/javascript'>
/* <![CDATA[ */
var thickboxL10n = {"next":"Next >","prev":"< Prev","image":"Image","of":"of","close":"Close","noiframes":"This feature requires inline frames. You have iframes disabled or your browser does not support them.","loadingAnimation":"<?= str_replace('/', '\/', includes_url('/js/thickbox/loadingAnimation.gif')) ?>","closeImage":"<?= str_replace('/', '\/', includes_url('/js/thickbox/tb-close.png')) ?>"};
/* ]]> */
</script>
<script src="<?= plugins_url('/thickbox-custom.js', __FILE__) ?>?ver=<?= PE2_VERSION ?>"></script>
<?php
		}// end function pe2_add_custom_thickbox_config()

		// add the photoswipe script included with this plugin (if they selected
		// the option)
		function pe2_add_photoswipe_script(){
			// add in the photoswipe script and related files
			wp_enqueue_script('pe2_photoswipe_klass', plugins_url('/photoswipe/lib/klass.min.js', __FILE__), array('jquery'), PE2_PHOTOSWIPE_VERSION);
			wp_enqueue_script('pe2_photoswipe_jquery_js', plugins_url('/photoswipe/code.photoswipe.jquery-3.0.5.min.js', __FILE__), array('jquery'), PE2_PHOTOSWIPE_VERSION);
			wp_enqueue_style('pe2_photoswipe_css', plugins_url('/photoswipe/photoswipe.css', __FILE__), null, PE2_PHOTOSWIPE_VERSION);

			// add the action to wp_footer to init photoswipe
			add_action('wp_footer', array(&$this, 'pe2_init_photoswipe'));
		}// end function pe2_add_photoswipe_script()
		function pe2_init_photoswipe(){
			// output the jQuery call to setup photoswipe
?><script>
jQuery(document).ready(function(){
	// ready event, get a list of unique rel values for the photoswiped images
	var rels = [];
	var rel = '';
	jQuery('a.photoswipe').each(function(){
		// for each photoswipe rel, if the rel value doesn't exist yet,
		// add it to our array
		rel = jQuery(this).attr('rel');
		if(rel != undefined){
			if(!pe2_in_array(rels, rel)){
				// add this rel to our array
				rels.push(jQuery(this).attr('rel'));
			}
		}
	});

	// check to see if our rels array has been built and has any values
	if(rels.length > 0){
		// we have at least one individual set of unique rels, setup photoswipe
		// for each
		jQuery.each(rels, function(key, value){
			// get this rel and create the collection
			pe2_setup_photoswipe(jQuery('a.photoswipe[rel=' + value + ']'));
		});
	}else{
		// we didn't get any rels, so attempt without rel checking
		pe2_setup_photoswipe(jQuery('a.photoswipe'));
	}
});
function pe2_setup_photoswipe(collection){
	// check to make sure our collection has records
	if(collection.length == 0){
		// nothing to do
		return;
	}

	// otherwise, setup photoswipe
	var collection_counter = 0;
	var myPhotoSwipe = collection.photoSwipe({
		// enable settings of photoswipe
		enableMouseWheel: true,
		enableKeyboard: true,
		captionAndToolbarAutoHideDelay: 0,
		imageScaleMethod: 'fitNoUpscale',
		// set the caption from the A tag's title attribute
		getImageCaption: function(item){
			// increment our image counter
			collection_counter++;

			// create the caption
			var caption = document.createElement('span');
			caption.appendChild(document.createTextNode(jQuery(item).attr('title')));

<?php		// determine if we have any second row items to display
			if(($this->options['pe2_photoswipe_caption_num'] == 1) || ($this->options['pe2_photoswipe_caption_view'] == 1) || ($this->options['pe2_photoswipe_caption_dl'] == 1)){
				// we have the second row

?>			// append a br
			caption.appendChild(document.createElement('br'));

			// define our second row separator
			var second_row_separator = false;

			// create the second row container
			var second_row = document.createElement('span');
			second_row.setAttribute('style', 'color: #BBB;');

<?php			// determine if we're display the image number
				if($this->options['pe2_photoswipe_caption_num'] == 1){
					// we need to display the image number text
?>			// create the "Image X of X"
			var num = document.createElement('span');
			num.appendChild(document.createTextNode('Image ' + collection_counter + ' of ' + collection.length));
			num.setAttribute('style', 'margin-right: 10px; margin-left: 10px;');
			second_row.appendChild(num);
			second_row_separator = true;
<?php
				}// end if we're displaying the caption number

				// determine if we're displaying the view link
				if($this->options['pe2_photoswipe_caption_view'] == 1){
					// we need to display the "View on Google+" link
?>
			// check to see if we need to output the separator
			if(second_row_separator){
				second_row.appendChild(document.createTextNode('-'));
			}

			// create the link to Google+
			var link = document.createElement('a');
			link.setAttribute('href', jQuery(item).attr('link'));
			link.setAttribute('target', '_blank');
			link.setAttribute('style', 'font-style: italic; font-weight: normal; color: #BBB; margin-right: 10px; margin-left: 10px;');
			link.setAttribute('onmouseover', 'this.style.color = \'#FF6666\';');
			link.setAttribute('onmouseout', 'this.style.color = \'#BBB\';');
			link.appendChild(document.createTextNode('View on Google+'));
			
			// append links to the second row
			second_row.appendChild(link);
			second_row_separator = true;
<?php
				}// end if we're displaying the caption number

				// determine if we're displaying the download link
				if($this->options['pe2_photoswipe_caption_dl'] == 1){
					// we need to display the "Download" link
?>
			// check to see if we need to output the separator
			if(second_row_separator){
				second_row.appendChild(document.createTextNode('-'));
			}

			// create the link to download
			// https://lh4.googleusercontent.com/-3qAvtWntPCg/UPS4VhKFDbI/AAAAAAAAF80/Fu-YgcWCdGo/s0-d/DSC_1276.JPG
			var download= document.createElement('a');
			download.setAttribute('href', jQuery(item).attr('href').replace(/\/[^\/]+\/([^\/]+)$/, '/s0-d/$1'));
			download.setAttribute('style', 'font-style: italic; font-weight: normal; color: #BBB; margin-right: 10px; margin-left: 10px;');
			download.setAttribute('onmouseover', 'this.style.color = \'#FF6666\';');
			download.setAttribute('onmouseout', 'this.style.color = \'#BBB\';');
			download.appendChild(document.createTextNode('Download'));

			// append the download link ot the second row
			second_row.appendChild(download);
<?php
				}// end if we're displaying the caption number
?>
			// append the second row to the caption
			caption.appendChild(second_row);
<?php		}// end if we're outputting any part of the second row

?>
			// return the generated caption
			return caption;
		}// end function to create the caption
	});
}// end function pe2_setup_photoswipe(..)
function pe2_in_array(array, value){
	for(var i = 0; i < array.length; i++){
		if(array[i] === value){
			return true;
		}
	}
	return false;
}
</script>
<?php
		}// end function pe2_init_photoswipe()

		// add the caption width calculation javascript if necessary
		function pe2_add_caption_width_javascript(){
			// check to see if our global variable is defined
			if(isset($GLOBALS['pe2_include_caption_width_correction_javascript'])){
				// we must perform the correction, setup the javascript
				?><script>
jQuery(document).ready(function(){
	// ready event, find any images inside a caption, grab their width
	// and then use that width to update the corresponding caption
	jQuery('div.wp-caption').each(function(){
		// for each caption, we need to locate the image inside, get the
		// width of it, then set the width of the caption element
		pe2_add_caption_width_javascript_helper(jQuery(this));
	});
});
function pe2_add_caption_width_javascript_helper(caption_obj){
	// check to make sure we have our image dimensions
	if(caption_obj.find('img').width() > 0){
		// we're good, we have image dimensions.  wait one more second
		// to make sure they're correct, then update our caption
		// element's width correctly
		setTimeout(function(){pe2_add_caption_width_javascript_helper_run(caption_obj)}, 1000);
	}else{
		// no dimensions yet, delay and retry
		setTimeout(function(){pe2_add_caption_width_javascript_helper(caption_obj)}, 1000);
	}
}// end function pe2_add_caption_width_javascript_helper(..)
function pe2_add_caption_width_javascript_helper_run(caption_obj){
	// we have our width, adjust the caption's width appropriately
	caption_obj.css('width', caption_obj.find('img').width());
}// end function pe2_add_caption_width_javascript_helper_run(..)
</script>
<?php
			}// end if we have the width correction indicator set
		}// end function pe2_add_caption_width_javascript()

		// filter the caption shortcode to add our class/style attributes
		function pe2_img_caption_shortcode_filter($content){
			// parse our content for each caption pass the matched area to our
			// callback for appropriate modification

			// perform the preg_replace if they have set either the pe2_caption_css
			// or pe2_caption_style
			if(($this->options['pe2_caption_css'] != null) || ($this->options['pe2_caption_style'] != null)){
				// one of hte container styles is configured, set them
				$content = preg_replace_callback('/class="([^"]*)wp-caption([^"]*)"([^>]+)/', 'PicasaExpressX2::pe2_img_caption_shortcode_filter_container_callback', $content);
			}

			// perform the preg_replace if they have set either the pe2_caption_css
			// or pe2_caption_style
			if(($this->options['pe2_caption_p_css'] != null) || ($this->options['pe2_caption_p_style'] != null)){
				// one of the p styles is configured, set them
				$content = preg_replace_callback('/class="([^"]*)wp-caption-text([^"]*)"([^>]*)/', 'PicasaExpressX2::pe2_img_caption_shortcode_filter_p_callback', $content);
			}

			// now return our modified content
			return $content;
		}// end function pe2_img_caption_shortcode_filter(..)
		function pe2_img_caption_shortcode_filter_container_callback($matches, $prefix = 'pe2_caption'){
			// this function gets called for every match found in the function
			// above.  Process each one properly

			// start our return
			$return = 'class="'.$matches[1].'wp-caption';

			// see if we need to the class if we're on the P
			if($prefix == 'pe2_caption_p'){
				$return .= '-text';
			}

			// check to see if we need to add the class
			if($this->options[$prefix.'_css'] != null){
				// yep, lets add our class
				$return .= ' '.$this->options[$prefix.'_css'];
			}

			// finish up the class attribute
			$return .= $matches[2].'"';

			// check to see if we need to add the style attribute
			if($this->options[$prefix.'_style'] != null){
				// yep, lets parse the $matches[2] for the style attribute, if found
				// add our style to it, if not, add the style attribute
				if(preg_match('/^(.+)style\s*=\s*"(.+)$/', $matches[3], $style_matches) > 0){
					// we matched a style attribute we need to modify

					// determine if our style option has a trailing semi-colon
					$add_style = $this->options[$prefix.'_style'];
					if((strrpos($this->options[$prefix.'_style'], ';') + 1) != strlen($this->options[$prefix.'_style'])){
						// the last character of our style option value that we're
						// adding is not a semi-colon, we need to add one
						$add_style .= ';';
					}

					// determine if our existing style matches contains a width, and 
					// our set style has a width, if so remove the existing style width
					if(strpos($add_style, 'width:') !== false){
						// we have width, so we need to remove any width from the 
						// $style_matches[2]
						$style_matches[2] = preg_replace('/width:\s*[^;"]+/', '', $style_matches[2]);
					}

					// add the appropriate style data to the return
					$return .= $style_matches[1].'style="'.$add_style.' '.$style_matches[2];
				}else{
					// no style attribute found, lets add one
					$return .= ' style="'.$this->options[$prefix.'_style'].'"'.$matches[3];
				}
			}else{
				// no style modification, simply add our $matches[3]
				$return .= $matches[3];
			}

			// return the modified caption output
			return $return;
		}// end function pe2_img_caption_shortcode_filter_callback(..)
		function pe2_img_caption_shortcode_filter_p_callback($matches){
			// this function gets called for every match found in the function
			// 2 above.  Simply configure the function 1 above to do the other
			// option setting
			return PicasaExpressX2::pe2_img_caption_shortcode_filter_container_callback($matches, 'pe2_caption_p');
		}
	}// end class PicasaExpressX2
}// end if the class doesn't already exist

// #####################################################################
// #####################################################################
// #####################################################################
// AUTHORIZATION CODE
// #####################################################################
// #####################################################################
// #####################################################################
if (isset($_GET['authorize'])&&!defined('ABSPATH')) {

	if (!isset($_GET['token'])||!$_GET['token']||strlen($_GET['token'])>256) {
		header('Location: '.preg_replace('/wp-content.*/','',$_SERVER["REQUEST_URI"]).'wp-admin/options-general.php?page=picasa-express-2');
		die();
	}

	// require wp-load so that wordpress loads allowing us to perform the updates
	// to the appropriate setting
	if(file_exists(dirname(__FILE__).'/pe2-wp-path.php')){
		// include the pe2-wp-path.php to define the wordpress root
		// (this allows a customized path (sometimes necessary in certain
		// wordpress installations) to be set when saving the pe2 preferences page 
		// and written to the file system so it can be loaded here prior 
		// to any wordpress filters or functionality being available)
		// (using an include that sets a variable so that if the file is
		// accessed from the web nothing is visible, thus not causing a
		// security problem)
		include(dirname(__FILE__).'/pe2-wp-path.php');
	}
	if(!isset($pe2_wp_path)){
		// for some reason the include doesn't exist (preferences haven't
		// been written, or web server doesn't have write access to the
		// plugin directory) or the include didn't set the appropriate
		// variable.
		// We have no choice but to determine the path as best as we can 
		// and hope it works with this installation
		$pe2_wp_path = preg_replace('/wp-content.*/','',__FILE__);
	}
	// require wp-load.php for the core wordpress functions we need
	require_once($pe2_wp_path.'wp-load.php');

	// create our instance and continue updating
	if (!isset($pe2_instance)) $pe2_instance = new PicasaExpressX2();

	if ('user' == $pe2_instance ->options['pe2_level'] && isset($_GET['user']) ) {
		$user_id = sanitize_text_field($_GET['user']);
		$user = new WP_User( $user_id );

		global $wp_roles;
		if ( ! isset( $wp_roles ) )	$wp_roles = new WP_Roles();

		$allow = false;
		foreach ( $user->roles as $role) {
			if (isset($wp_roles->roles[$role]['capabilities']['picasa_dialog'])) {
				$allow=true; break;
			}
		}

		if (!$allow) {
			header('Location: '.preg_replace('/wp-content.*/','',$_SERVER["REQUEST_URI"]).'wp-admin/profile.php');
			die();
		}
	}

	$response = $pe2_instance->get_feed("https://www.google.com/accounts/AuthSubSessionToken",sanitize_text_field($_GET['token']));

	$message='';
	if (is_wp_error($response)) {
		$message = 'Can\'t request token: ' .$response->get_error_message();
	} else if ($response) {
		$lines  = explode("\n", $response);

		// grab our current logged in user
		$current_user = wp_get_current_user();
		$user_id = $current_user->ID;

		// go thorugh all lines in the reponse looking for the token
		foreach ($lines as $line) {
			$pair = explode("=", $line, 2);
			if (0==strcasecmp($pair[0],'token')) {
				if ((get_option('pe2_level') == 'user') && isset($user_id))
					update_user_meta($user_id,'pe2_token',sanitize_text_field($pair[1]));
				else
					update_option('pe2_token',sanitize_text_field($pair[1]));
				$message = 'Private access received';
			}
		}
	}

	if (isset($user_id))
		header('Location: '.preg_replace('/wp-content.*/','',$_SERVER["REQUEST_URI"]).'wp-admin/profile.php?message='.rawurlencode($message));
	else
		header('Location: '.preg_replace('/wp-content.*/','',$_SERVER["REQUEST_URI"]).'wp-admin/options-general.php?page=picasa-express-2&message='.rawurlencode($message));
	die();

} else {
	if (!isset($pe2_instance)) $pe2_instance = new PicasaExpressX2();
}
?>
