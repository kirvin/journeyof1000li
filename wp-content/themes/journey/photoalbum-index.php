<?php
/*
Template Name: Photo Album

If you want to customize the look and feel of your photo album, follow these steps. 

You'll probably need a good understanding of HTML and CSS!

1. Copy this file into your current active theme's directory
2. Also copy all the files starting with "photoalbum-" into your theme's directory
      * Alternatively, you could only copy just the "photoalbum-" file you want to customize into your current themes directory.
3. Customize the CSS in photoalbum-header.html to your liking.
4. That's it :)

The main template files:
- photoalbum-albums-index.html shows all your Flickr sets (aka albums)
- photoalbum-album.html displays a highlight photo and all the photos for an album
- photoalbum-photo.html displays one photo, along with next and previous photo links 

Troubleshooting Tips:
Not all WordPress themes are created equal, so default look and feel might look a little weird
on your blog. Try looking at your theme's "index.php" and copy and paste any extra HTML or
PHP into this file.

$Revision: 128 $
$Date: 2008-04-24 00:16:32 -0400 (Thu, 24 Apr 2008) $
$Author: joetan54 $

*/
global $TanTanFlickrPlugin;
if (!is_object($TanTanFlickrPlugin)) wp_die('Flickr Photo Album plugin is not installed / activated!');

display_header("photos");

// load the appropriate albums index, album's photos, or individual photo template.
// $photoTemplate contains the template being used

// HACK:  if displaying an album, then set up the layout differently
$tpl = $TanTanFlickrPlugin->getDisplayTemplate($photoTemplate);

$singleColumn = (strpos($tpl, "photoalbum-album.html") !== 0 || strpos($photoTemplate, "photoalbum-photo.html") !== 0);
?>
<div id="contentContainer" class="photoContent">
	<table border="0" cellpadding="0" cellspacing="0">
		<tr>
<?php 
		// album display - one column
		if ($singleColumn) {
?>
			<td valign="top" class="left-column">
			<?php
				/*
				echo '<img class="photos-splash-image" src="' . get_bloginfo('template_url') . '/images/img_photos_header.jpg" 
					alt="The \'' . get_bloginfo('name') . '\' Authors"
				/>';
				 */
			?>
			</td>
			<td class="right-column" valign="top">
				<div class="sidebar">
					<?php
						//follow_widget();
					?>
				</div>			
			</td>
		</tr>
		<tr>
			<td colspan="2" valign="top">
				<div class="single-column-container">
				<?php
					//include($tpl = $TanTanFlickrPlugin->getDisplayTemplate($photoTemplate));
					include($tpl);

					// uncomment this line to print out the template being used
					//echo 'Photo Album Template: '.$tpl;
					if (!is_object($Silas)) {
				?>
						<div class="flickr-meta-links">
							Powered by the <a href="http://tantannoodles.com/toolkit/photo-album/">Flickr Photo Album</a> plugin for WordPress.
						</div>
				<?php
					}
				?>
				</div>
			</td>
<?php 
		}
		// other displays - two columns
		else {
?>
			<td valign="top" class="left-column">
				<div class="left-container">
				<?php
					echo '<img class="photos-splash-image" src="' . get_bloginfo('template_url') . '/images/img_photos_header.jpg" 
						alt="The \'' . get_bloginfo('name') . '\' Authors"
					/>';
					include($tpl = $TanTanFlickrPlugin->getDisplayTemplate($photoTemplate));

					// uncomment this line to print out the template being used
					//echo 'Photo Album Template: '.$tpl;
					if (!is_object($Silas)) {
				?>
						<div class="flickr-meta-links">
							Powered by the <a href="http://tantannoodles.com/toolkit/photo-album/">Flickr Photo Album</a> plugin for WordPress.
						</div>
				<?php
					}
				?>
				</div>
			</td>
			<td class="right-column" valign="top">
				<div class="sidebar">
					<?php
						follow_widget();
						get_sidebar();
					?>
				</div>			
			</td>
<?php 
		}
?>
		</tr>
	</table>
</div>

<?php get_footer(); ?>
