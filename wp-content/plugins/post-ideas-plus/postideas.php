<?php
/*
	Plugin Name: Post Ideas+
	Plugin URI: http://nooshu.com/wordpress-plug-in-post-ideas-plus/
	Description: Jot down ideas for future blog posts directly from the dashboard. Based heavily on Aaron Robbins' Post ideas.
	Author: <a href="http://www.nooshu.com/">Matt Hobbs</a> & <a href="http://www.aaronrobbins.com/">Aaron Robbins</a>
	Version: 2.1.0.5
	Author URI: http://nooshu.com/
*/

/*
Wordpress Plug-in Post Ideas+
Copyright (C) 2010 Matt Hobbs  (matt AT nooshu DOT com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

/**
 * TODO:
 * Done: Highlight an admins post idea
 * Done: User can only view admin post ideas
 * Done: Used can only write admin ideas not edit / delete
 * Done: Convert dash widgets to reflect changes
 * Done: Disable dash widgets for users when needed
 * Done: Say admin ideas in blue
 * 
 * v2.1.0.5
 * Done: Add option to set order on dash
 * Done: Fix annoying layout bug
 * Add an idea status for the admin
 * Add option to see who is writing
 * Option to delete all settings
 * Make checkboxes into radios...maybe.
 * Better way of checking for admin
 */

/*
 * Post Ideas Class
 */
if(!class_exists("PostIdeasPlus")){
	class PostIdeasPlus {
		var $adminOptionsName = "pipAdminOptions";
		
		//Plugin install
		function pip_install(){
			//Set DB version
			$pip_db_version = "1.11";
			//Grab wp DB 
			global $wpdb;
			
			//Get current user info
			global $current_user;
			get_currentuserinfo();
			//Current users ID
			$user_id = $current_user->ID;
			
			//Set new table name
			$table_name = $wpdb->prefix . "piplus";
			//Version of the DB installed
			$installed_ver = get_option( "pip_db_version" );
			
			//Does table already exist?
			if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
				//Setup table parameters
				$sql = "CREATE TABLE " . $table_name . " (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					user smallint(2) DEFAULT '" . $user_id . "' NOT NULL,
					time bigint(11) DEFAULT '0' NOT NULL,
					title text NOT NULL,
					description text NOT NULL,
					keywords text NOT NULL,
					urls text NOT NULL,
					priority smallint(2) DEFAULT '1' NOT NULL,
					PRIMARY KEY  (id)
					);";
				
					//Create the table
					require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
					dbDelta($sql);
					
					//Sample entry
					$sample_title = "My great idea for a blog post";
					$sample_description = "This will be a blog post that changes the world.";
					$sample_keywords = "amazing, witty, funny, not, at, all, boring";
					$sample_urls = "http://www.google.com/, http://www.bing.com/";
					$sample_priority = 5;
					
					//Insert example entry
					$insert = "INSERT INTO " . $table_name .
					" (user, time, title, description, keywords, urls, priority) " .
					"VALUES ('" . $current_user->ID . "','" . time() . "','" . $wpdb->escape( $sample_title ) . "','" . $wpdb->escape( $sample_description ) . "','" . $wpdb->escape( $sample_keywords ) . "','" . $wpdb->escape( $sample_urls ) . "','" . $wpdb->escape( $sample_priority ) . "')";
					
					//Run insert
					$results = $wpdb->query( $insert );
					
					//Add version option
					add_option("pip_db_version", $pip_db_version);
			} else if($installed_ver != $pip_db_version){//Upgrade the database
				//New SQL layout
				$sql = "CREATE TABLE " . $table_name . " (
					id mediumint(9) NOT NULL AUTO_INCREMENT,
					user smallint(2) DEFAULT '" . $user_id . "' NOT NULL,
					time bigint(11) DEFAULT '0' NOT NULL,
					title text NOT NULL,
					description text NOT NULL,
					keywords text NOT NULL,
					urls text NOT NULL,
					priority smallint(2) DEFAULT '1' NOT NULL,
					PRIMARY KEY  (id)
					);";
				
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      			dbDelta($sql);
				
				update_option("pip_db_version", $pip_db_version);
			} else {
				//Table name unavailable
				$table_name . " does exist.";
			}
		}//End pip_install
		
		//Initialise admin options
		function init(){
			$this->admin_options();
		}//End init
		
		//The admin options
		function admin_options(){
			//Set default number of rows to view on dash
			add_option("pip_row_length", '5', '', 'yes');
			add_option("pip_dash_sort", 'newest', '', 'yes');
		}//End admin_options
		
		//Init admin page
		function pip_admin() {
			if(function_exists('add_management_page')) {
				add_management_page('Post Ideas+ Options', 'Post Ideas+', 1, basename(__FILE__), array($this,'admin_page'));
			}
		}//End pip_admin
		
		//Get all admin user ID's in the DB
		function admin_user_ids(){
			//Grab wp DB 
			global $wpdb;
			//Get all users in the DB
			$wp_user_search = $wpdb->get_results("SELECT ID, display_name FROM $wpdb->users ORDER BY ID");
			
			//Blank array
			$adminArray = array();
			//Loop through all users
			foreach ( $wp_user_search as $userid ) {
				//Current user ID
				$curID = $userid->ID;
				//Grab the user info
				$curuser = get_userdata($curID);
				//Current user level
				$user_level = $curuser->user_level;
				//Only look for admins
				if($user_level >= 8){
					$adminArray[] = $curID;
					//Current display name
				}
			}
			return $adminArray;
		}//End admin_user_ids
		
		//Get PI's from the DB. Accepts argument of "admin" or "dash" depending on where called
		function get_post_ideas($area, $orderby){
			//Grab vars
			global $wpdb;
			global $current_user;
			//Table name
			$table_name = $wpdb->prefix . "piplus";
			
			//Get current user info
			get_currentuserinfo();
			$user_id = $current_user->ID;
			
			//Check for "see admin posts" and "only admin posts"
			if(get_option( "pip_adminIdeas" ) == "on"){
				$adminIdArray = $this->admin_user_ids();
				//If user can also see there own ideas push ID in
				if(get_option( "pip_adminIdeasOnly" ) != "on"){
					//Push user id into the array
					$adminIdArray[] = $user_id;
				}
				//Array to a comma seperated string
				$adminIDs = implode(",", $adminIdArray);
			}
			
			//Look to see if the user has added a setting for number of rows
			$checkSet = get_option("pip_row_length$user_id");
			if(isset($checkSet) && $checkSet != ""){
				$numberRows = get_option("pip_row_length$user_id");
			} else {
				$numberRows = get_option("pip_row_length");
			}
			
			switch($area){
				case "admin":
					if(get_option( "pip_adminIdeas" ) == "on"){
						$postideas = $wpdb->get_results("SELECT * FROM $table_name WHERE user IN($adminIDs) $orderby");
					} else {
						//Grab only user posts ideas from the DB
						$postideas = $wpdb->get_results("SELECT * FROM $table_name WHERE user='$user_id' $orderby");
					}
					break;
				case "dash":
					//Can the user see admin ideas / their own ideas?
					if(get_option( "pip_adminIdeas" ) == "on"){
						$postideas = $wpdb->get_results("SELECT * FROM $table_name WHERE user IN($adminIDs) $orderby $numberRows");
					} else {
						//Grab only user posts ideas from the DB
						$postideas = $wpdb->get_results("SELECT * FROM $table_name WHERE user='$user_id' $orderby $numberRows");
					}
					break;
			}
			return $postideas;
		}//End get_post_ideas
		
		//Generate the admin page
		function admin_page(){
			global $wpdb;
			//Set current user info
			global $current_user;
			
			//Get current user info
			get_currentuserinfo();
			$userLevel = $current_user->user_level;
			$user_id = $current_user->ID;
						
			//Table name
			$table_name = $wpdb->prefix . "piplus";
			
			//Add a post to the DB
			if(isset($_POST['submit_Add']) || isset($_POST['submit_Edit'])){
				//Referer check
        		check_admin_referer('pip_postidea_add');
        		
	            //Set variables
	            $title =  $_POST['pip_addIdeaTitle'];
	            $description = $_POST['pip_addIdeaDescription'];
	            $keywords = $_POST['pip_addIdeaKeywords'];
	            $urls = $_POST['pip_addIdeaURLS'];
	            $priority = $_POST['pip_addIdeaPriority'];
	            
	            //Action for add / edit
	            if (isset($_POST['submit_Add'])){
					//Build insert
					$insert = "INSERT INTO " . $table_name .
					" (user, time, title, description, keywords, urls, priority) " .
					"VALUES ('" . $current_user->ID . "','" . time() . "','" . $wpdb->escape($title) . "','" . $wpdb->escape($description) . "','" . $wpdb->escape($keywords) . "','" . $wpdb->escape($urls) . "','" . $wpdb->escape($priority) . "')";
	            
					//Add insert to DB
					$results = $wpdb->query( $insert );
					$update_fade = '<div id="message" class="updated fade"><p>Your post idea was added.</p></div>';
	            } else {
	            	//Grab the record id to be edited
	            	$editid = $_POST['pip_editid'];
	            	
					//Build update
					$update = "UPDATE " . $table_name . " SET title='" . $wpdb->escape($title) . "', description='" . $wpdb->escape($description) . "', keywords='" . $wpdb->escape($keywords) . "', urls='" . $wpdb->escape($urls) . "', priority='" . $wpdb->escape($priority) ."' WHERE id=$editid";
					
					//Add update to DB
					$results = $wpdb->query( $update );
					$update_fade = '<div id="message" class="updated fade"><p>Your post idea was updated.</p></div>';
	            }
			}
			
			//Delete and entry from the DB
			if($_GET['delete']){
				//Get variables
				$postid = $_GET['delete'];
				$posttitle = $_GET['title'];
				$posttitle = htmlentities(stripslashes($posttitle));
	        
				//Confirmation message
				$update_fade = '<div id="message" class="updated fade"><p>Are you sure you want to delete "'.$posttitle.'"? <a href="?page=postideas.php&amp;deleteconfirmed='.$postid.'">Yes</a> | <a href="?page=postideas.php">No</a></p></div>';
    		} else if($_GET['deleteconfirmed']){
				//Get variables
        		$postid = $_GET['deleteconfirmed'];
        
				//Build delete
				$delete =  "DELETE FROM $table_name WHERE id=$postid";
        
		        //Delete record
		        $results = $wpdb->query( $delete );
				
		        //If you want to write post, and post idea has a title
		        if($_GET['writeconfirmed'] && $_GET['writetitle']){
					$title = $_GET['writetitle'];
					$update_fade = "<div id='message' class='updated fade'><p>Post Idea deleted. &raquo; <a href=\"post-new.php?post_title=$title\">Begin Writing</a></p></div>";
		        } else {
					$update_fade = '<div id="message" class="updated fade"><p>Post Idea deleted.</p></div>';
		        }
		    }
		    
			//Write the post from the post idea
			if($_GET['write']){
				//Get variables
				$postid = $_GET['write'];
				$posttitle = $_GET['title'];
				$posttitle = htmlentities(stripslashes($posttitle));
				$postuserid = $_GET['uid'];
				
				//If the user writing the post idea is the user who added it to the DB, usual confirm / delete
				if($postuserid == $user_id){
					//Make confirmation
					$update_fade = '<div id="message" class="updated fade"><p>Writing "'.$posttitle.'" will delete it from the Post Ideas list. <a href="?page=postideas.php&amp;deleteconfirmed='.$postid.'&amp;writeconfirmed=yes&amp;writetitle='.$posttitle.'">Continue</a> | <a href="?page=postideas.php">Cancel</a></p></div>';	
				} else {
					$update_fade = "<div id='message' class='updated fade'><p>Are you sure you want to write ".$posttitle."? <a href=\"post-new.php?post_title=$posttitle\">Yes</a> | <a href='?page=postideas.php'>No</a></p></div>";
				}
			}
			
			//Get post idea sort order
			$sortOrder = $_GET['orderby'];
			switch($sortOrder){
				case "newest":
					$orderby = "ORDER BY time DESC";
					$currentOrder = "newest";
					break;
				case "oldest":
					$orderby = "ORDER BY time ASC";
					$currentOrder = "oldest";
					break;
				case "title":
					$orderby = "ORDER BY title ASC";
					$currentOrder = "title";
					break;
				case "priority":
					$orderby = "ORDER BY priority ASC";
					$currentOrder = "priority";
					break;
				default:
					$orderby = "ORDER BY time DESC";
					$currentOrder = "newest";
					break;
			}
			
			//Default form word
			$form_word = "Add";
			//Blank output
			$output = "";
			//Blank row
			$altRow = "";
			
			//Grab the post ideas, returns array of post ideas.
			$postideas = $this->get_post_ideas("admin", $orderby);
			
			if(!empty($postideas)){
				//Loop through results
				foreach ($postideas as $postidea) {
					//If we are editing a post idea
					if($_GET['edit'] && $postidea->id == $_GET['edit']){
						$edit_id = $postidea->id;
						$edit_title = htmlentities(stripslashes($postidea->title));
						$edit_description = htmlentities(stripslashes($postidea->description));
						$edit_keywords = htmlentities(stripslashes($postidea->keywords));
						$edit_urls = htmlentities(stripslashes($postidea->urls));
						$edit_priority = $postidea->priority;
					}
					
					//Generate the link list explode on ,
					$links = explode(",",$postidea->urls);
					$link_list = "";
					foreach($links as $link){
						$link_list .= "<a href=\"$link\">$link</a><br/>";
					}
	        
					//Zebra striped rows
					$altRow = 'alternate' == $altRow ? '' : 'alternate';
	        		//If current user ID doesn't match post idea user ID add a class
					if($user_id != $postidea->user){
	        			$adminClass = "admin-idea";
	        		} else {
	        			$adminClass = "";
	        		}
					
					//Generate the list table
					$output .= "<tr class='$altRow $adminClass'>";
					$output .= "<td>".stripslashes($postidea->title)."</td>";
					$output .= "<td>".stripslashes($postidea->description)."</td>";
					$output .= "<td>".stripslashes($postidea->keywords)."</td>";
					$output .= "<td>".$link_list."</td>";
					$output .= "<td>".stripslashes($postidea->priority)."</td>";
					$output .= "<td>";
					$output .= "<a href='".$_SERVER['PHP_SELF']."?page=postideas.php&amp;write=".$postidea->id."&amp;uid=".$postidea->user."&amp;title=".htmlentities(stripslashes($postidea->title))."'>Write</a>";
					if($postidea->user == $user_id){
						$output .= " | <a href=\"".$_SERVER['PHP_SELF']."?page=postideas.php&amp;edit=".$postidea->id."&amp;title=".$postidea->title."\">Edit</a>";
						$output .= " | <a href=\"".$_SERVER['PHP_SELF']."?page=postideas.php&amp;delete=".$postidea->id."&amp;title=".htmlentities(stripslashes($postidea->title))."\">Delete</a>";
					}
					$output .= "</td>";
					$output .= "</tr>";
					
					//Setup add or edit variable for buttons / header
					if($_GET['edit']){
						$form_word = "Edit";
						$switch = "<a href=\"".$_SERVER['PHP_SELF']."?page=postideas.php\">Cancel</a>";
					} else {
						$form_word = "Add";
					}
				}
			} else {
				$output .= "<tr class='empty'><td colspan='6'>No Post Ideas to display</td><tr>";
			}
			
			//Update admin settings
			if(isset($_POST['submit_settings'])){
				//Set number of rows a user sees on the dashboard
				$updateRows =  $_POST['pip_numberRows'];
				update_option( "pip_row_length$user_id", $updateRows );
				
				$updateDashSort =  $_POST['pip_dashSort'];
				update_option( "pip_dash_sort", $updateDashSort );
				
				//Set if a user sees admin post ideas
				$updateAdminIdeas =  $_POST['pip_adminIdeas'];
				update_option( "pip_adminIdeas", $updateAdminIdeas );
				
				//Set if a user ONLY sees admin post ideas
				$updateAdminIdeasOnly =  $_POST['pip_adminIdeasOnly'];
				update_option( "pip_adminIdeasOnly", $updateAdminIdeasOnly );
				
				$update_fade = '<div id="message" class="updated fade"><p>Your settings have been saved.</p></div>';
			}
			
			//Look to see if the user has added a setting for number of rows
			$checkSet = get_option("pip_row_length$user_id");
			if(isset($checkSet) && $checkSet != ""){
				$numberRows = get_option("pip_row_length$user_id");
			} else {
				$numberRows = get_option("pip_row_length");
			}
			
			//Check if view admin ideas is checked
			$adminIdeasChecked;
			if(get_option( "pip_adminIdeas" ) == "on"){
				$adminIdeasChecked = "checked='yes'";
			}
			
			//Check if view admin ideas ONLY is checked
			$adminIdeasOnlyChecked;
			if(get_option( "pip_adminIdeasOnly" ) == "on"){
				$adminIdeasOnlyChecked = "checked='yes'";
			}
			
			//Display info about admin posts if needed
			$adminIdArray = $this->admin_user_ids();
			$adminPostStatus = "";
			
			//Used to select dashboard sort
			$dashSort = get_option("pip_dash_sort");
			
			if( !in_array($user_id, $adminIdArray) &&  get_option( "pip_adminIdeas" ) == "on"){
				$adminPostStatus .= "<p class='adminInfo'>Administrator Post Ideas are highlighted in blue.</p>";
			}
			
			//Include admin page template
			$base = dirname(__FILE__);
			include($base."/includes/admin_page.inc.php");
		}//End pip_admin_page
		

		//Init 'add post idea' dashboard widget
		function pip_add_post_dashboard() {
			//Get current user info
			global $current_user;
			get_currentuserinfo();
			$user_id = $current_user->ID;
			//Admin users
			$adminIdArray = $this->admin_user_ids();
			
			if(in_array($user_id, $adminIdArray) || get_option( "pip_adminIdeasOnly" ) != "on"){
				wp_add_dashboard_widget('add_post_ideas_widget', 'Add Post Idea', array($this,'add_post_idea_widget'));
			}
		}//End pip_add_post_dashboard

		//'Add post idea' widget on dashboard
		function add_post_idea_widget(){
			$siteurl = get_option('siteurl');
			$url = $siteurl.'/wp-admin/tools.php?page=postideas.php';
			
			//Include 'Add post idea' template
			$base = dirname(__FILE__);
			include($base."/includes/add_post_idea_widget.inc.php");
		}//End add_post_idea_widget
		
		
		//Init 'view post idea' dashboard widget
		function pip_view_post_dashboard() {
			wp_add_dashboard_widget('view_post_ideas_widget', 'Latest Post Ideas', array($this,'view_post_idea_widget'));	
		}//End pip_view_post_dashboard
		
		//'View post idea' widget on dashboard
		function view_post_idea_widget(){
			global $wpdb;
			//Set current user info
			global $current_user;
			
			//Get current user info
			get_currentuserinfo();
			$user_id = $current_user->ID;
			
			//Grab site URL
			$siteurl = get_option('siteurl');
			$url = $siteurl.'/wp-admin/tools.php?page=postideas.php';

			//Set table name
			$table_name = $wpdb->prefix . "piplus";
    
		    //Blank view output
		    $viewOutput = "";
			//Blank row
			$altRow = '';
			
			//Get post idea sort order
			$sortOrder = get_option("pip_dash_sort");
				switch($sortOrder){
					case "newest":
						$orderby = "ORDER BY time DESC LIMIT";
						break;
					case "oldest":
						$orderby = "ORDER BY time ASC LIMIT";
						break;
					case "title":
						$orderby = "ORDER BY title ASC LIMIT";
						break;
					case "priority":
						$orderby = "ORDER BY priority ASC LIMIT";
						break;
					default:
						$orderby = "ORDER BY time DESC LIMIT";
						break;
				}
			
			$postideas = $this->get_post_ideas("dash", $orderby);
			
			//Loop through results
			if(!empty($postideas)){
				foreach ($postideas as $postidea){
					//Zebra striped rows
					$altRow = 'alternate' == $altRow ? '' : 'alternate';
					//If current user ID doesn't match post idea user ID add a class
					if($user_id != $postidea->user){
	        			$adminClass = "admin-idea";
	        		} else {
	        			$adminClass = "";
	        		}
					
					//Generate the list table
					$viewOutput .= "<tr class='$altRow $adminClass'>";
					$viewOutput .= "<td>".stripslashes($postidea->title)."</td>";
					$viewOutput .= "<td>".stripslashes($postidea->description)."</td>";
					$viewOutput .= "<td>".stripslashes($postidea->priority)."</td>";
					$viewOutput .= "<td>";
					$viewOutput .= "<a href='".$url."&amp;write=".$postidea->id."&amp;uid=".$postidea->user."&amp;title=".htmlentities(stripslashes($postidea->title))."'>Write</a>";
					if($postidea->user == $user_id){
						$viewOutput .= " | <a href='".$url."&amp;edit=".$postidea->id."&amp;title=".$postidea->title."'>Edit</a>";
						$viewOutput .= " | <a href='".$url."&amp;delete=".$postidea->id."&amp;title=".htmlentities(stripslashes($postidea->title))."'>Delete</a>";
					}
					$viewOutput .= "</td></tr>";
				}
			} else {
				$viewOutput .= "<tr class='empty'><td colspan='4'>No Post Ideas to display</td><tr>";
			}
			
			//Display info about admin posts if needed
			$adminIdArray = $this->admin_user_ids();
			$adminPostStatus = "";
			
			if( !in_array($user_id, $adminIdArray) &&  get_option( "pip_adminIdeas" ) == "on"){
				$adminPostStatus .= "<p class='adminInfo'>Administrator Post Ideas are highlighted in blue.</p>";
			}
			
			//Include 'View post idea' template
			$base = dirname(__FILE__);
			include($base."/includes/view_post_idea_widget.inc.php");
		}//End view_post_idea_widget
		
		//Add the custom stylesheet to admin header
		function admin_register_head() {
			$siteurl = get_option('siteurl');
			$url = $siteurl . '/wp-content/plugins/' . basename(dirname(__FILE__)) . '/css/post-ideas.css';
			echo "<link rel='stylesheet' type='text/css' href='$url' />\n";
		}
	}//End sampleClassSeries
}

/*
 * Initialise the class
 */
if(class_exists("PostIdeasPlus")){
	$postIdea = new PostIdeasPlus();
}

/*
 * Attach actions and filters
 */
if(isset($postIdea)){
	//Start install
	register_activation_hook(__FILE__,array(&$postIdea, 'pip_install'));
	//Initialise admin options
	register_activation_hook(__FILE__,array(&$postIdea, 'init'));
	//Init add admin page
	add_action('admin_menu', array(&$postIdea, 'pip_admin'), 1);
	//'Add new post ideas' dashboard widget 
	add_action('wp_dashboard_setup', array(&$postIdea, 'pip_add_post_dashboard'), 1);
	//View post ideas dashboard widget
	add_action('wp_dashboard_setup', array(&$postIdea, 'pip_view_post_dashboard'), 1);
	//Add css to admin header
	add_action('admin_head', array(&$postIdea, 'admin_register_head'), 1);
}
?>