<div class="wrap">
	<h2>Geotag Documentation</h2>
	<h3>The <em>[gmap]</em> Shortcode</h3>
	<p>If you want to add a map to your post, you have to add the term <code>[gmap]</code> into your post's text. When the post is beeing 
		displayed, the [gmap] shortcode will be replaced by a Google Map pointing to the coordinates of your post. It is also possible to add multiple 
		maps to a single post to display different features.</p>
	<p>You can set the default map apperance at the configuration page, but you may also overwrite the default setting within the [gmap] shortcode. 
		The basic syntax therefore is <code>[gmap property_1="value" property_2="value" ...]</code>. For a list of 
		all possible properties see below:</p>
	<table>
		<tr>
			<td style="padding: 10px;"><code>width="..."</code></td>
			<td style="padding: 10px;">Changes the width of the map container. You must add the unit <em>px</em> or <em>%</em> to the value like <code>width="50%"</code>.</td>
		</tr>
		<tr>
			<td style="padding: 10px;"><code>height="..."</code></td>
			<td style="padding: 10px;">Changes the height of the map container. You must add the unit <em>px</em> or <em>%</em> to the value like <code>height="600px"</code>.</td>
		</tr>
		<tr>
			<td style="padding: 10px;"><code>type="..."</code></td>
			<td style="padding: 10px;">Changes the type of the map. Possible values are <code>G_NORMAL_MAP</code>, <code>G_SATELLITE_MAP</code>, <code>G_HYBRID_MAP</code>, 
				<code>G_PHYSICAL_MAP</code> or <code>G_STATIC_MAP</code> (a static map with no features).<br />
				<strong>Note</strong>: If you use the static map, some restritions apply concerning the other properties, because it is not possible to use the <em>marker_query</em>, the <em>file</em> or 
				the <em>photos</em> properties.</td>
		</tr>
		<tr>
			<td style="padding: 10px;"><code>zoom="..."</code></td>
			<td style="padding: 10px;">Sets the zoom level from <code>0</code> (zoomed out) to <code>19</code> (zoomed in). If you set the zoom to <code>auto</code>, it will be automatically set the zoom to
				display all items on which the map automatically centers (see below). </td>
		</tr>
		<tr>
			<td style="padding: 10px;"><code>lat="..." lon="..."</code></td>
			<td style="padding: 10px;">Adds the given coordinates to the post, if you haven't set some for this post before. This may be useful for posting via email as you may add 
				your current position just into the post's text and it will be processed for the map. See below for formatting the coordinates.<br />
				<strong>Note</strong>: This will only add the given position, if no position was set before.</td>
		</tr>
		<tr>
			<td style="padding: 10px;"><code>center="..."</code></td>
			<td style="padding: 10px;">Declares on which items the map should automatically be centered. Possible values are <code>markers</code> (the markers of the posts), <code>photos</code> 
				(the markers of geotaged photos) and <code>file</code> (the KML/KMF file). Multiple Value may be seperated (only) by a <code>,</code> (comma, without space). This value will
				be overwritten, if the <code>center_lat</code> and <code>center_lon</code> values are set.</td>
		</tr>
		<tr>
			<td style="padding: 10px;"><code>center_lat="..."<br />center_lon="..."</code></td>
			<td style="padding: 10px;">Changes the center of the map manually. See below for formatting the coordinates.</td>
		</tr>
		<tr>
			<td style="padding: 10px;"><code>marker_lat="..."<br />marker_lon="..."</code></td>
			<td style="padding: 10px;">Displays a marker at the given position instead of the position saved with the post or set with <code>lat="..." lon="..."</code>. See below 
				for formatting the coordinates.</td>
		</tr>
		<tr>
			<td style="padding: 10px;"><code>marker_query="..."</code></td>
			<td style="padding: 10px;">Adds a marker to the map for every post which matches to the query. The query parameters are the same like the ones for the <em>get_posts()</em>
			function. You may have a look at the <a href="http://codex.wordpress.org/Template_Tags/get_posts">Wordpress Codex</a> for a complete documentation. You can 
			click on the marker to see a link in a bubble.
			Here are some examples:<br />
			<code>marker_query="numberposts=5&offset=1&category=1"</code> Shows the position of the previous five posts (if there is any) of the category 1, 
			not including the current post</td>
		</tr>
		<tr>
			<td style="padding: 10px;"><code>file="..."</code></td>
			<td style="padding: 10px;">Specifies the URL of a KML or KMZ file to display at the map. This allows you to display individual markers, routes, photos etc. Have a look at the
				<a href="http://code.google.com/intl/de/apis/kml/documentation/kml_tut.html">Google KML documentation</a> for more details.<br />
				<strong>Note</strong>: If you place your KML/KMZ-files within your default upload directory, you may use the term <code>__UPLOAD__</code> instead of the full url, e.g. 
				<code>file="__UPLOAD__/2009/03/Track_20080713_171705.kml"</code></td>
		</tr>
		<tr>
			<td style="padding: 10px;"><code>display_photos="..."</code></td>
			<td style="padding: 10px;">Overrides the "Geotaged Photos" setting of the configuration menu. If set to <code>true</code>, the plugin scans every image of the post for a 
				geotag within the exif header and displays a icon at the appropriate position at the map. If set to <code>false</code>, the plugin won't do this.</td>
		</tr>
		<tr>
			<td style="padding: 10px;"><code>photos_icon="..."</code></td>
			<td style="padding: 10px;">Overrides the setting for the icon which is displayed at the map for thegeotaged photos. Possible values are <code>DEFAULT</code> (the default marker of Google Maps), 
				<code>CAMERA</code> (a camera icon) or <code>THUMBNAIL</code> (displays a thumbnail of every photo; this will be not a very nice one because it is not a resampled 
				but only a resized version of the images; however this speeds up the loading time).<br />
				If you click on a marker, you will see a thumbnail of the image in the bubble.</td>
		</tr>
	</table>
	<p>Now lets have an example:<br />
		<code>[gmap center_lat="10 20 3 N" center_lon="-50.125412" file="__UPLOAD__/2009/03/Track_20080713_171705.kml" type="G_SATELLITE_MAP" display_photos="true" zoom="auto" center="markers,photos,file"]</code>
	
	<h3 style='margin-top: 3em;'>The Auto Map</h3>
	<p>Maybe you would like to have a map automatically added to every post which contains a position. Then you might activate the Auto Map feature at the configuration page and set up 
		the position where the map should be displayed (at the top or at the bottom of every post).</p>
	<p>Using the Auto Map doesn't mean that you have to pass the possibility to configure the maps individually. If the Auto Map feature is activated, the first <code>[gmap]</code> 
		with all its properties will be processed for the Auto Map. If you would like to add a second map to the post, you have to add two <code>[gmap]</code> shortcodes - the first one 
		with properties for the Auto Map (or leave it blank if you don't want to change anything) and the second one with the properties for the manually added map.</p>
	<p>Please note, that there may apply some restritions if you set the default map appearance to a static map. In this case it is not possible to configre the Auto Map with a 
		<code>[gmap]</code> shortcode. Nevertheless, if you want to add a second map, you have to put two <code>[gmap]</code> shortcode to the post anyway. This is for compatibility reasons 
		if you change between the default maps types from time to time.</p>
	
	<h3 style='margin-top: 3em;'>The Coordinates</h3>
	<p>If you want to specify coordinates, you may always use the follwing geographic coordinate conversion: DMS (ddd° mm' ss.ss"), MinDec (ddd° mm.mmmm') or DegDec (ddd.dddddd°) - but don't use any 
		units like ° or '. For example, these coordinates are all the same:<br />
		<code>50 10 3 S</code>, <code>-50 10.05</code>, <code>-50.1675</code><p>
		
	<h3 style='margin-top: 3em;'>The Centering and Zooming</h3>
	<p>The map centers automatically on the items you have selected in the options menu or you have declared in the <code>[gmap]</code> shortcode.<p>
	<p>In case the standard zoom level is too close to display all the seleced items, you may configure <i>Geotag</i> to automatically zoom out so that
		all items will be displayed. In case the standard zoom level shows too much, you can configure <i>Geotag</i> to automatically zoom in and display all items completly filling the map.<p> 
	
	<h3 style='margin-top: 3em;'>Styling the Google Map bubbles</h3>
	<p>You can costumize the styling of the bubbles which are shown for the <code>marker_query</code> or the <code>display_photos</code> with some CSS. The DIV container uses the class 
		<code>gmap_infowindow</code>, the P for the headline uses the class <code>headline</code>, the one for the date uses the class <code>date</code>.</p>
	
	<h3 style='margin-top: 3em;'>Miscellaneous</h3>
	<p>If you have used the <a href="http://wordpress.org/extend/plugins/wp-geo/"><em>WP Geo</em> plugin by Ben Huson</a> before, you may activate the <em>WP Geo read compatibility</em> and the <em>WP Geo shortcode processing</em> at the configuration page. If 
		you would like to use <em>WP Geo</em> again, you should activate the <em>WP Geo read and write compatibility</em>, so all news coordinates will be saved to the database in the same 
		way which <em>WP Geo</em> would have done.<p>
	
	<h3 style='margin-top: 3em;'>Themes</h3>
	<p>The features for creating custom themes are not developed very well at the current time. Right now it is only possible to display the coordinates with a link to the 
		Google Maps page by calling <code>the_coordinates()</code> within <a href="http://codex.wordpress.org/The_Loop">the loop</a>.
	
	<h3 style='margin-top: 3em;'>About</h3>
	<p>The <em>Geotag</em> plugin was made by <a href="http://www.bobsp.de">Boris Pulyer</a>. You find additional information at the <em>Geotag</em> website at 
		<a href="http://www.bobsp.de/weblog/geotag">http://www.bobsp.de/weblog/geotag</a>.</p>
	<p>Copyright (C) 2009 by Boris Pulyer.<br />
		You may use, redistribute and/or modify this programm under the terms of the <a href="http://www.gnu.org/licenses/gpl.html">GNU General Public License</a> by the Free Software Foundation.<br />
		The programm is distributed as free software without any warranty.</p>
</div>