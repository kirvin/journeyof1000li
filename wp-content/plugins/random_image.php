<?php

// Random Image widget
//
// Copyright (c) 2008 Marcel Proulx
// http://www.district30.net/random-image/
//
// This is an add-on for WordPress
// http://wordpress.org/
//
// **********************************************************************
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// *****************************************************************

/*
Plugin Name: Random Image Widget
Plugin URI: http://www.district30.net/random-image/
Description: Display a random image from a directory located on the webserver
Author: Marcel Proulx
Version: 1.6beta3
Author URI: http://www.district30.net
*/ 

/* Function: disp_random_image
	** This function begins the display of the widget in the sidebar
	**
	** args: $args (environment variables handled automatically by the hook)
	** widget_args (array or int containing instance number to be displayed)
	** returns: nothing
*/
// $widget_args: number
//    number: which of the several widgets of this type do we mean
function disp_random_image( $args, $widget_args = 1 ) {
	extract( $args, EXTR_SKIP );
	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );
	
	$options = get_option('widget_random_image');
	if ( !isset($options[$number]) )
		return;
	if (!is_array($options[$number]) )
		return;
		
	extract($options[$number]);
	
	if ($show_in_sb == TRUE) {
	
		echo "\n" . '<!---Begin Display of Random Image sidebar widget-->' . "\n";
		echo $before_widget;
		
		if (strlen($title) > 0) {  //don't display an empty tag if the title field is empty
			echo $before_title
				. $title 
			 	. $after_title;
		}
		
		
		echo_random_image($number);
		
		echo $after_widget;
	}
}

/* Function: return_id()
** This function recursively searches an array for the existence
** of a string. It returns the text of the string after $string
** 
** args: $array(array to search), $string(string to search for)
** returns: 0, if $array is not an array; the remaining characters in 
** a string of text if the string contains $string 
*/

function return_id($array, $string) {
	if (! is_array($array) )
		return 0;
		
	foreach ($array as $index) {
		
		if (is_array($index)) {
			$recursive_id = return_id($index, $string);
			if($recursive_id) {
				$result=$recursive_id; //avoid null values
			}
	}
		elseif (strpos($index,$string)===0) {  //if the string is found at the beginning of the array
			$result = substr($index,strlen($string)+1);
		}
	}
	return $result;
}

/* Function: echo_random_image
** This function echoes the html to display the random image widget 
** If it is called from a sidebar function (i.e., disp_random_image),
** it will get the proper widget id (for extracting options) in the
** function call.  If it is called manually, it will search through
** available sidebar widgets for the id.
**
** args: $number(index number of a sidebar widget)
**       $display(boolean,controls whether the html is echoed here)
**		 $sidebar(boolean,if true, forces scaling to 150px)
** returns: html to display the widget
*/

function echo_random_image($number=1, $display=TRUE, $sidebar=TRUE) {
	$f_then = microtime();
	if ($number==1) {
		$sidebars_widgets = wp_get_sidebars_widgets();
		$number = return_id($sidebars_widgets,"random_image");
	}
	
	// Data should be stored as array:  array( number => data for that instance of the widget, ... )
	$options = get_option('widget_random_image');
	if ( !isset($options[$number]) )
		return;
	extract($options[$number]);
	
	//Do some validation on the options
	if (!is_numeric($scale_x)) 
		$scale_x = 320; //blank or invalid numbers should revert to a default
	if (!is_numeric($scale_y))
		$scale_y = 320; //blank or invalid numbers should revert to a default
	if (!is_numeric($sb_scale_x))
		$sb_scale_x = 150; //blank or invalid numbers should revert to a default
	if (!is_numeric($sb_scale_y))
		$sb_scale_y = 150; //blank or invalid numbers should revert to a default
			
	
	$my_code = '<div id="random_image_wrapper" style="text-align:center;">' ."\n";
	

	if($yapb_support) {
		//query the database for YAPB photoblog posts.  YAPB automatically generates thumbnails,
		//this will pick up the links to those images.
		global $wpdb;

		$sql = 'SELECT p.ID, p.post_title FROM ' . $wpdb->posts . ' p LEFT JOIN ' . YAPB_TABLE_NAME . ' yi ON p.ID = yi.post_id WHERE p.post_type = \'post\' AND yi.URI IS NOT NULL AND p.post_status = \'publish\'' . $order . ' LIMIT 0,' . get_option('yapb_sidebarwidget_imagecount');
		$posts = $wpdb->get_results($sql);
		
		$thumb = array();
		$thumb[] = 'q=100';
		$images = array();
		
		foreach($posts as $post) {
			$yapbImage = YapbImage::getInstanceFromDb($post->ID);
			$post_data['uri'] = $yapbImage->getThumbnailHref($thumb);
			$post_data['link'] = get_permalink($post->ID);
			$post_data['caption'] = $post->post_title;
			
			$images[] = $post_data;  //add the new image to the array
		}
	
	}

	/*code modifications to read photo info from a delimited text file*/
	//read the directory and return a list of files
	
	$path = get_bloginfo('wpurl');
	$image_directory = substr(ABSPATH,0,-1) . $directory; //remove redundant slash

	if($show_csv_info)
		$content = read_file($image_directory . '/' . $csv_path,","); //reads a comma-delimited text file into $content
			
	$local_pics = image_list($image_directory,$path . $directory, $recurse);
	foreach($local_pics as $pic) {
		$pic_data['uri'] = $pic;
		if ($show_csv_info && is_array($content)) {
			// Search a CSV file for a matching file name.  If one is found, extract the caption and link data 	
			
			/*	The next part of the function may be more efficient with a recursive call of array_search
				based on the following code:
				
			echo substr(strrchr($pic,'/'),1) . "<br>"; //since all files will include path info, this should work.  Will it work in Windows?
			if($key = array_search(substr(strrchr($pic,'/'),1),$content)) {
				echo $key;
			}
			*/
			
			foreach($content as $lines) {
				if(stripos($pic,$lines[0])) {
					$pic_data['link'] = $lines[2];
					$pic_data['caption'] = $lines[1];
				}
			}
			
		}
		else {
			// If there is no CSV file, use the URI for caption and link data
			$pic_data['link'] = $pic;
			$pic_data['caption'] = substr(strrchr($pic,'/'),1); //just the filename, not the path
		}	
			
		$images[] = $pic_data; //add the new image to the array
	}
	
	if (!is_array($images))
		return;  //the directory isn't valid or doesn't contain recognized images
	$available_image_count = sizeof($images);
	usort($images, ri_rand_compare);
	usort($images, ri_rand_compare); //if you do it twice, it's twice as random, right?  Seemed like the last image came up too much.
	
	$image_count = min($image_count,$available_image_count);
  	for ($count = 0; $count < $image_count; $count ++) {
		
		$item_url = $images[$count];
		//This section generates code for displaying "embed" files
		if (strpos($item_url['uri'],".embed") != FALSE) {
			//display the contents of a file (e.g., a YouTube clip)
			$my_code .= file_get_contents($item_url['uri']);  //add the contents of the file.  Scaling and other format issues need to be dealt with in the html in the file
			$my_code .="\n";
			$my_code .= $show_caption ? '<p>' . $caption . '</p>' : '';  //if a caption is called for, add it here.  Otherwise, do nothing.
			$my_code .="\n\n";
		}
		
		//This section generates code for displaying images
		else {
			//display an image
			//perform scaling
			$img_info = @getimagesize($item_url['uri']);  //image width is in element 0 and height is element 1
			$img_width = $img_info[0];
			$img_height = $img_info[1];
	
			if($display_option == 1) { //CODE FOR SCALING/CROPPING IMAGE TO FRAME
				//Generate the common HTML for both cases
				if ($sidebar) {
					$sb_div = '<div style="width:' . $sb_scale_x . 'px; height:' . $sb_scale_y . 'px; overflow:hidden; position:relative; margin: auto; text-align:left;">';
					$sb_close_div = '</div></div>';	
					if ($img_width > 0 && $img_height > 0 && ($img_width/$img_height) > ($sb_scale_x/$sb_scale_y)) {
						//the ratio of horizontal edge to vertical edge is larger for the image than the frame 
						//scale the original vertical edge to the frame vertical and calculate the distance in px
						//to move the image inside the frame to center it.
						$scoot = (int) ((($sb_scale_y/$img_height)*$img_width)-$sb_scale_x)/2;
						$img_scale = ' height="' . $sb_scale_y . '"';
						$sb_div .= '<div style="position:absolute; display:block; left:-' . $scoot . 'px;">';
					}
					else {
						//the ratio of vertical edge to horizontal edge is larger for the image than the frame 
						//scale the original horizontal edge to the frame horizontal and calculate the distance in px
						//to move the image inside the frame to center it.
						//could still have bad values for $img_width or $img_height. 
						if ($img_width > 0) //$img_width could cause a division-by-zero error if we don't check
							$scoot = (int) ((($sb_scale_x/$img_width)*$img_height)-$sb_scale_y)/2;
						else
							$scoot = 0; //a safe default value
						$img_scale = ' width="' . $sb_scale_x . '"';
						$sb_div .= '<div style="position:absolute; display:block; top:-' . $scoot . 'px;">';
					}
				}
				else { //code for displaying in post body
					$sb_div = '<div style="width:' . $scale_x . 'px; height:' . $scale_y . 'px; overflow:hidden; position:relative; margin: auto; text-align:left;">';
					$sb_close_div = '</div></div>';	
					if ($img_width > 0 && $img_height > 0 && ($img_width/$img_height) > ($scale_x/$scale_y)) {
						//the ratio of horizontal edge to vertical edge is larger for the image than the frame 
						//scale the original vertical edge to the frame vertical and calculate the distance in px
						//to move the image inside the frame to center it.
						$scoot = (int) ((($scale_y/$img_height)*$img_width)-$scale_x)/2;
						$img_scale = ' height="' . $scale_y . '"';
						$sb_div .= '<div style="position:absolute; display:block; left:-' . $scoot . 'px;">';
					}
					else {
						//the ratio of vertical edge to horizontal edge is larger for the image than the frame 
						//scale the original horizontal edge to the frame horizontal and calculate the distance in px
						//to move the image inside the frame to center it.
						//could still have bad values for $img_width or $img_height. 
						if ($img_width > 0) //$img_width could cause a division-by-zero error if we don't check
							$scoot = (int) ((($scale_x/$img_width)*$img_height)-$scale_y)/2;
						else
							$scoot = 0; //a safe default value
						$img_scale = ' width="' . $scale_x . '"';
						$sb_div .= '<div style="position:absolute; display:block; top:-' . $scoot . 'px;">';
					}
				}
			}			
			
			elseif($display_option == 2) { // CODE FOR STRECHING IMAGE
				$sidebar ? $img_scale = 'width="' . $sb_scale_x . '" height="' . $sb_scale_y . '"' 
				 : $img_scale = ' width="' . $scale_x . '" height="' . $scale_y . '"'; 
				$sb_close_div = $sb_div = ''; //this option doesn't require the extra div
			}			
			
			else { // CODE FOR CLASSIC BEHAVIOR ($display_option = 0 or improperly defined)
				if ($img_width < $img_height) {
					//this is probably a "portrait" image so we'll scale vertically to user set sidebar size if
					//the image is in the sidebar or the user other setting if it's not.
					$sidebar ? $img_scale = ' height="' . $sb_scale_y . '"' : $img_scale = ' height="' . $scale_y . '"';
				}
				else {
					//otherwise, this is probably a "landscape" image so we'll scale horizontally to 150 pixels
					//or the user setting
					$sidebar ? $img_scale = ' width="' . $sb_scale_x . '"' : $img_scale = ' width="' . $scale_x . '"';
				}
				$sb_close_div = $sb_div = ''; //this option doesn't require the extra div
			}				
			
			//generate the common code to show the image/embedded content and drop in the previously
			//generated code from above
						
			$my_code .= $sb_div; //echoes the additional container divs to display the framed image, if needed
			$my_code .= '<a href="';
			$my_code .= $item_url['link'];  //this line has been modified from 1.5 and earlier to remove the condition.  Link is now always shown.
			if ($display_option > 0) //padding is in the div element when the frame option is selected 
				$my_code .= '"><img src="' . $item_url['uri'] . '"';
			else
				$my_code .= '"><img style = "padding: 3px 3px 3px 3px;" src="' . $item_url['uri'] . '"';
			$my_code .= $img_scale;
			$my_code .= ' alt="' . $item_url['caption'] . '"';
			$my_code .= ' /></a>';
			$my_code .= $sb_close_div; //echoes the close tags for the container divs for framed image, if needed
			
			$my_code .= "\n";
			$my_code .= $show_caption ? '<p>' . $item_url['caption'] . '</p>' : '';
			$my_code .= "\n\n";
		}			
		
	}

	//finish sidebar code
	$my_code .= '</div>' . "\n";
	
	//show the code
	if ($display == TRUE) 
		echo $my_code;
	
	$f_now = microtime();
	$f_elapsed = $f_now-$f_then;
	echo '<!---Displayed in ' . $f_elapsed . ' seconds.' . "-->" . "\n";
	return $my_code;
}	

/* Function: rand_image_filter
** Function searches post content for the string (randomimage) and
** replaces it with the widget code when found
**
** args: $content: post content, handled by hook
** returns: post content with appropriate code added
*/

function rand_image_filter($content) {
	//disabled due to concerns about effects on the core plugin functionality
	$tag = '(randomimage)';
	if (strpos($content,$tag)) { //only continues if $tag is in $content (ie the post text)
		$result = echo_random_image(1,FALSE,FALSE);
		$content = str_replace($tag, $result, $content);
	}
	//otherwise, don't do anything
	
	return $content;
}


/* Function: ri_rand_compare
** To be used with an array sort function (i.e. usort), this function 
** will randomly sort an array
**
** args: $x=string; $y=string (handled by the function call)
** returns: a random integer between -1 and 1
*/

function ri_rand_compare($xt, $yt) {
	// We don't care about the array elements passed on so we'll
	// create a random value for the return value
	// The random function doesn't need to be seeded
	return mt_rand(-1,1);
}

/* Function: image_list
** This function recursively searches directories for image files 
** Because these are accessed via their path on the server
** but we want to return URL's (based on the web root), both paths
** are required arguments.
**
** args: $image_directory=string; $image_url=string; $recursive=boolean
** returns: an array containing image filenames; FALSE in case of error
*/

function image_list($image_directory,$image_url,$recursive=FALSE) { 
	$then = microtime();
	$images = array();
	if (!is_dir($image_directory))
		return FALSE;
	$dirhandle = opendir($image_directory);
	while (($filename = readdir($dirhandle))!== FALSE) {
		if (strcmp($filename, '.') == 0 || strcmp($filename, '..') == 0) continue;  //skip the rest of the while loop if we're looking at . or ..
		$abs_filename = $image_directory . '/' . $filename;
		$file_extention=strtolower(substr($filename,strrpos($filename, '.')+1));
		if ($file_extention == 'jpg' or $file_extention == 'gif' or $file_extention == 'embed') {
			$images[] = $image_url . '/' . $filename;
		}
		elseif (is_dir($abs_filename) && $recursive) {
			$images = array_merge($images,image_list($abs_filename, $image_url . '/' . $filename,TRUE));
		}
	}
	closedir($dirhandle);
	$now = microtime();
	$elapsed = $now-$then;
	$n = sizeof($images);
	echo '<!--Searching ' .  $image_directory . ': found ' . $n . ' images in ' . $elapsed . ' seconds-->' . "\n";
	return $images;
}

/* Function: validate_path
** This function checks user input for proper "slash" usage
** A user-input backslash should be converted to a slash
**
** args: $path=string containing user-input path
** returns: a string containing a validated version of the input
*/

function validate_path($path) {
	$path = str_replace('\\\\', '/', $path); //convert backslashes to slashes.  '\' is an escaped character. 
	$path = stripslashes($path); //remove slashes from characters that were escaped (e.g. \\, etc)
	if ($path[0] != '/') {
		$path = '/' . $path; //make sure the string starts with a slash
	}
	if ($path[strlen($path)-1] == '/') {
		$path = substr($path,0,strlen($path)-1); //remove trailing slash
	}
	return($path);
}

/* Function: read_file
** This function reads a delimited file into an array
** 
**
** args: $file=string containing a filename to read
**		 $delimiter=delimiter for the file
** returns: an array containing the parsed contents of the file
** returns 0 on error
*/
function read_file($file,$delimiter=',') {
	//check that the filename is valid and readable.  If so, open it for read-only.
	if (is_readable($file)) {
		$fc=fopen($file,"r");
	}
	//Otherwise, return 0 now.
	else {
		return 0;
	}
	//read the file into contents and close it.
	$contents = fread($fc,filesize($file));
	fclose($fc);
	
	//break the file into its component lines (using the end-of-line character)
	$lines = explode("\n",$contents);
	
	//break each line at the delimiter.  Use the referenced value of $line to maintain the multi-dimensional array
	
	//This only works in PHP 5
	/*
	foreach($lines as &$line){
		$line = explode($delimiter,$line);
	}
	*/
	
	//This will work on PHP 4 as well
	while(list($key,$value) = each($lines)) {
		$value = explode($delimiter,$value);
		$lines[$key] = $value;
	}
	return $lines;
}


/* Function: random_image_control
** 
** This function draws the controls form on the widget page and 
** saves the settings when the "Save" button is clicked
**
** args: $widget_args-array or int containing the instance number being controlled
** returns: nothing
*/

function random_image_control($widget_args) {
	global $wp_registered_widgets;
	static $updated = false; // Whether or not we have already updated the data after a POST submit

	if ( is_numeric($widget_args) )
		$widget_args = array( 'number' => $widget_args );
	$widget_args = wp_parse_args( $widget_args, array( 'number' => -1 ) );
	extract( $widget_args, EXTR_SKIP );

	// Data should be stored as array:  array( number => data for that instance of the widget, ... )
	$options = get_option('widget_random_image');
	if ( !is_array($options) )
		$options = array();

	// We need to update the data
	if ( !$updated && !empty($_POST['sidebar']) ) {
		// Tells us what sidebar to put the data in
		$sidebar = (string) $_POST['sidebar'];

		$sidebars_widgets = wp_get_sidebars_widgets();
		if ( isset($sidebars_widgets[$sidebar]) )
			$this_sidebar =& $sidebars_widgets[$sidebar];
		else
			$this_sidebar = array();

		foreach ( $this_sidebar as $_widget_id ) {
			// Remove all widgets of this type from the sidebar.  We'll add the new data in a second.  This makes sure we don't get any duplicate data
			// since widget ids aren't necessarily persistent across multiple updates
			if ( 'disp_random_image' == $wp_registered_widgets[$_widget_id]['callback'] && isset($wp_registered_widgets[$_widget_id]['params'][0]['number']) ) {
				$widget_number = $wp_registered_widgets[$_widget_id]['params'][0]['number'];
				if ( !in_array( "random_image-$widget_number", $_POST['widget-id'] ) ) // the widget has been removed. "many-$widget_number" is "{id_base}-{widget_number}
					unset($options[$widget_number]);
			}
		}
				
		foreach ( (array) $_POST['random_image'] as $widget_number => $widget_random_image_instance ) {
			// compile data from $widget_random_image_instance
			
			$title = strip_tags(stripslashes( $widget_random_image_instance['title']));
			$image_count= (int) strip_tags(stripslashes( $widget_random_image_instance['image_count']));
			$directory=strip_tags(validate_path($widget_random_image_instance['directory']));
			$recurse= (bool) strip_tags(stripslashes( $widget_random_image_instance['recurse']));
			$show_caption= (bool) strip_tags(stripslashes( $widget_random_image_instance['show_caption']));
			$show_in_sb= (bool) strip_tags(stripslashes( $widget_random_image_instance['show_in_sb']));
			$scale_x = (int) strip_tags(stripslashes( $widget_random_image_instance['scale_x']));
			$scale_y = (int) strip_tags(stripslashes( $widget_random_image_instance['scale_y']));
			$sb_scale_x = (int) strip_tags(stripslashes( $widget_random_image_instance['sb_scale_x']));
			$sb_scale_y = (int) strip_tags(stripslashes( $widget_random_image_instance['sb_scale_y']));
			$display_option = (int) strip_tags(stripslashes ($widget_random_image_instance['display_option']));
			$show_csv_info = (bool) strip_tags(stripslashes( $widget_random_image_instance['show_csv_info']));  //allow the plugin to read comma-delimited file for caption and link, based on filename
			$csv_path = strip_tags(stripslashes( $widget_random_image_instance['csv_path']));
			$yapb_support = (bool) strip_tags(stripslashes( $widget_random_image_instance['yapb_support']));
			
			//Do a little validation
			if (!is_numeric($scale_x))
				$scale_x = 640; //use the default
			if (!is_numeric($scale_y))
				$scale_y = 480; //use the default
			if (!is_numeric($sb_scale_x))
				$sb_scale_x = 150; //use the default
			if (!is_numeric($sb_scale_y))
				$sb_scale_y = 150; //use the default
			
			$options[$widget_number] = compact('title', 'image_count', 'directory', 'recurse', 'show_caption', 'show_in_sb', 'scale_x', 'scale_y', 'sb_scale_x', 'sb_scale_y', 'display_option', 'show_csv_info', 'csv_path', 'yapb_support');
		}

		update_option('widget_random_image', $options);

		$updated = true; // So that we don't go through this more than once
	}


	// Here we echo out the form
	if ( -1 == $number ) { // We echo out a template for a form which can be converted to a specific form later via JS
		$title = 'Random';
		$image_count='1';
		$directory='/wp-content/plugins/random-image';
		$recurse=FALSE;
		$show_caption=FALSE;
		$show_in_sb=TRUE;
		$scale_x = 640;
		$scale_y = 480;
		$sb_scale_x = 150;
		$sb_scale_y = 150;
		$display_option = 0;
		$show_csv_info=FALSE;
		$csv_path='';
		$yapb_support=FALSE;
		$number = '%i%';
	} 
	else {
		$title = attribute_escape($options[$number]['title']);
		$image_count=attribute_escape($options[$number]['image_count']);
		$directory=$options[$number]['directory'];
		$recurse=attribute_escape($options[$number]['recurse']);
		$show_caption=attribute_escape($options[$number]['show_caption']);
		$show_in_sb=attribute_escape($options[$number]['show_in_sb']);
		$scale_x = attribute_escape($options[$number]['scale_x']);
		$scale_y = attribute_escape($options[$number]['scale_y']);
		$sb_scale_x = attribute_escape($options[$number]['sb_scale_x']);
		$sb_scale_y = attribute_escape($options[$number]['sb_scale_y']);
		$display_option = attribute_escape($options[$number]['display_option']);
		$show_csv_info=attribute_escape($options[$number]['show_csv_info']);
		$csv_path=attribute_escape($options[$number]['csv_path']);
		$yapb_support=attribute_escape($options[$number]['yapb_support']);
	}

	// The form has inputs with names like widget-many[$number][something] so that all data for that instance of
	// the widget are stored in one $_POST variable: $_POST['widget-many'][$number]
	?>
	<p><label for="random_image-title-<?php echo $number; ?>">Title:  </label><input class="widefat" style="width: 200px;" id="random_image-title-<?php echo $number; ?>" name="random_image[<?php echo $number; ?>][title]" type="text" value="<?php echo $title; ?>" /></p>
	<!---create an option box with a set number of values generated by a for loop -->
	<p><label for="random_image-image_count-<?php echo $number; ?>">Number of images to show:  </label><select name="random_image[<?php echo $number; ?>][image_count]" id="random_image-image_count-<?php echo $number; ?>" style="width: 50px;">
	<?php
	for ($list_val=1; $list_val <= 10; $list_val ++) {
		if ($list_val == $image_count)
			echo '<option selected>' . $list_val . '</option>';
		else 
			echo '<option>' . $list_val . '</option>';
	}
	?>
	</select></p>
	<p style="text-align:left; line-height=1.2em"><label for="random_image-directory-<?php echo $number; ?>">Path to image directory:  </label><input style="width: 350px; margin-left: 10;" id="random_image-directory-<?php echo $number; ?>" name="random_image[<?php echo $number; ?>][directory]" type="text" value="<?php echo $directory; ?>" /></p>
	<p style="text-align:left; line-height=1.2em"><input style="margin-left: 10;" id="random_image-recurse-<?php echo $number; ?>" name="random_image[<?php echo $number; ?>][recurse]" type="checkbox" value="1"<?php
	echo  $recurse ? 'checked' : ''; //if the option was selected, make the box checked when the form is open; otherwise leave it unchecked
	echo '>';
	?>
	</input>
	<label for="random_image-recurse-<?php echo $number; ?>">Look for photos in subdirectories</label></p>
	
	<p style="text-align:left; line-height=1.2em"><input style="margin-left: 10;" id="random_image-show_caption-<?php echo $number; ?>" name="random_image[<?php echo $number; ?>][show_caption]" type="checkbox" value="1"<?php
	echo  $show_caption ? 'checked' : ''; //if the option was selected, make the box checked when the form is open; otherwise leave it unchecked
	echo '>';
	?>
	</input>

	<label for="random_image-show_caption-<?php echo $number; ?>">Show caption beneath photo? </label>
	</p>

	<p style="text-align:left; line-height=1.2em"><input style="margin-left: 10;" id="random_image-show_csv_info-<?php echo $number; ?>" name="random_image[<?php echo $number; ?>][show_csv_info]" type="checkbox" value="1"<?php
	echo  $show_csv_info ? 'checked' : ''; //if the option was selected, make the box checked when the form is open; otherwise leave it unchecked
	echo '>';
	?>
	</input>

	<label for="random_image-show_csv_info-<?php echo $number; ?>">Use caption and link information from this CSV file: </label>
	<input style="width: 300px; margin-right: 20" id="random_image-csv_path-<?php echo $number; ?>" name="random_image[<?php echo $number; ?>][csv_path]" type="text" value="<?php echo $csv_path; ?>"/></p>

	<p style="text-align:left; line-height=1.2em"><input style="margin-left: 10;" id="random_image-show_in_sb-<?php echo $number; ?>" name="random_image[<?php echo $number; ?>][show_in_sb]" type="checkbox" value="1"<?php
	echo  $show_in_sb ? 'checked' : ''; //if the option was selected, make the box checked when the form is open; otherwise leave it unchecked
	echo '/>';
	?>
	<label for="random_image_show_in_sb-<?php echo $number; ?>">Show widget in sidebar(uncheck to hide)</label>
	</p>
	
	<p style="text-align:left; line-height=1.2em"><label for="random_image[<?php echo $number; ?>][scale_x]">Scale to </label>
	<input style="width: 35px; margin-right: 20;" id="random_image-scale_x-<?php echo $number; ?>" name="random_image[<?php echo $number; ?>][scale_x]" type="text" value="<?php echo $scale_x; ?>"/>
	<label for="random_image[<?php echo $number; ?>][scale_y]"> pixels wide by </label>
	<input style="width: 35px; margin-right: 20;" id="random_image-scale_y-<?php echo $number; ?>" name="random_image[<?php echo $number; ?>][scale_y]" type="text" value="<?php echo $scale_y; ?>"/>
	high</p>

	<p style="text-align:left; line-height=1.2em"><label for="random_image[<?php echo $number; ?>][sb_scale_x]">Scale sidebar image to </label>
	<input style="width: 35px; margin-right: 20;" id="random_image-sb_scale_x-<?php echo $number; ?>" name="random_image[<?php echo $number; ?>][sb_scale_x]" type="text" value="<?php echo $sb_scale_x; ?>"/>
	<label for="random_image[<?php echo $number; ?>][sb_scale_y]"> pixels wide by </label>
	<input style="width: 35px; margin-right: 20;" id="random_image-sb_scale_y-<?php echo $number; ?>" name="random_image[<?php echo $number; ?>][sb_scale_y]" type="text" value="<?php echo $sb_scale_y; ?>"/>
	high</p>
	
	<p style="text-align:left; line-height=1.2em"><label for="random_image[<?php echo $number; ?>][display_option]">Choose a display option:</label></p>
	
	<!--Unordered lists here break the control when the widget is "added" via the widget panel.  WP bug?-->
	
	<p>Scale long edge (classic)<input type="radio" name="random_image[<?php echo $number; ?>][display_option]" value=0 <?php
	echo  $display_option == 0 ? 'checked="checked"' : ''; //if the option was selected, make the box checked when the form is open; otherwise leave it unchecked
	echo '/>';
	?>
	</p>
	
	<p>Scale and fit to frame<input type="radio" name="random_image[<?php echo $number; ?>][display_option]" value=1 <?php
	echo  $display_option == 1 ? 'checked="checked"' : ''; //if the option was selected, make the box checked when the form is open; otherwise leave it unchecked
	echo '/>';
	?>
	</p>
	
	<p>Scale both dimensions (stretch)<input type="radio" name="random_image[<?php echo $number; ?>][display_option]" value=2 <?php
	echo  $display_option == 2 ? 'checked="checked"' : ''; //if the option was selected, make the box checked when the form is open; otherwise leave it unchecked
	echo '/>';
	?>
	</p>
	
	<p><input style="margin-left: 10;" id="random_image-yapb_support-<?php echo $number; ?>" name="random_image[<?php echo $number; ?>][yapb_support]" type="checkbox" value="1"<?php
	echo  $yapb_support ? 'checked' : ''; //if the option was selected, make the box checked when the form is open; otherwise leave it unchecked
	echo '/>';
	?>
	<label for="random_image_yapb_support-<?php echo $number; ?>">Enable YAPB support</label>
	</p>
	
	<input type="hidden" id="random_image-submit-<?php echo $number; ?>" name="random_image-submit-<?php echo $number; ?>" value="1" />
	<?php
}

/* Function: random_image_register
** 
** Registers the random_image widgets with the widget page
**
** args: none
** returns: nothing
*/

function random_image_register() {
	if ( !$options = get_option('widget_random_image') )
		$options = array();

	$widget_ops = array('classname' => 'widget_many', 'description' => __('Widget which allows multiple instances'));
	$control_ops = array('width' => 450, 'height' => 600, 'id_base' => 'random_image');
	$name = __('Random Image');

	$registered = false;
	foreach ( array_keys($options) as $o ) {
		// Old widgets can have null values for some reason
		if ( !isset($options[$o]['title']) ) // we used 'something' above in our exampple.  Replace with with whatever your real data are.
			continue;

		// $id should look like {$id_base}-{$o}
		$id = "random_image-$o"; // Never never never translate an id
		$registered = true;
		wp_register_sidebar_widget( $id, $name, 'disp_random_image', $widget_ops, array( 'number' => $o ) );
		wp_register_widget_control( $id, $name, 'random_image_control', $control_ops, array( 'number' => $o ) );
	}

	// If there are none, we register the widget's existance with a generic template
	if ( !$registered ) {
		wp_register_sidebar_widget( 'random_image-1', $name, 'disp_random_image', $widget_ops, array( 'number' => -1 ) );
		wp_register_widget_control( 'random_image-1', $name, 'random_image_control', $control_ops, array( 'number' => -1 ) );
	}
}

function random_image_css() {
?>
<style type="text/css">
.wraptocenter {
    display: table-cell;
    text-align: center;
    vertical-align: middle;
    width: ...;
    height: ...;
}
.wraptocenter * {
    vertical-align: middle;
}
/*\*//*/
.wraptocenter {
    display: block;
}
.wraptocenter span {
    display: inline-block;
    height: 100%;
    width: 1px;
}
/**/
</style>
<!--[if lt IE 8]><style>
.wraptocenter span {
    display: inline-block;
    height: 100%;
}
</style><![endif]-->
<?php
}

// This is important
add_action( 'widgets_init', 'random_image_register' );
add_filter( 'the_content', 'rand_image_filter' );
add_action( 'wp_head', 'random_image_css' );
?>