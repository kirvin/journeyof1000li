<?php
/*
Template Name: Route Page
*/
	display_header("route");
?>

<div id="contentContainer">
	<table border="0" cellpadding="0" cellspacing="0">
		<tr>
			<td valign="top" class="left-column">
			<?php
				if (have_posts()) {
					while (have_posts()) {
						the_post();
						echo '<h1>';
						the_title();
						echo '</h1>';
						the_content();
					}
				} else { ?>
					Looking for something?  Try searching our site--if you can't find what you were looking for, <a href="/contact">drop us a line</a>.
					<?php include (TEMPLATEPATH . "/searchform.php"); ?>
			<?php
				}
			?>
			</td>
			<td class="right-column" valign="top">
				<div class="sidebar">
					<?php
						follow_widget();
					?>
				</div>			
			</td>
		</tr>
		<tr>
			<td colspan="2" align="center" valign="top">
				<div id="routeLinksContainer">
					<ul class="route-list">
						<li>
							<a href="#" onclick="setRoute(0);return false;">Route Overview</a>
						</li>
						<li>
							<a href="#" onclick="setRoute(1);return false;">Part 1:&nbsp;Fukuoka to Onomichi</a>
						</li>
						<li>
							<a href="#" onclick="setRoute(2);return false;">Part 2:&nbsp;Onomichi to Himeji</a>
						</li>
						<li>
							<a href="#" onclick="setRoute(3);return false;">Part 3:&nbsp;Himeji to Kyoto</a>
						</li>
						<li>
							<a href="#" onclick="setRoute(4);return false;">Part 4:&nbsp;Kyoto to Nagano</a>
						</li>
						<li>
							<a href="#" onclick="setRoute(5);return false;">Part 5:&nbsp;Nagano to Tokyo</a>
						</li>
						<li>
							<a href="#" onclick="setRoute(6);return false;">Part 6:&nbsp;Tokyo to Yamagata</a>
						</li>
						<li>
							<a href="#" onclick="setRoute(7);return false;">Part 7:&nbsp;Yamagata to Aomori</a>
						</li>
						<li>
							<a href="#" onclick="setRoute(8);return false;">Part 8:&nbsp;Hakodate to Wakkanai</a>
						</li>
					</ul>
				</div>
				<div id="routeMap" style="margin: auto; width: 690px; height: 900px;"></div>
<?php 
				$temp_query = $wp_query;
				// get posts that are geotagged
				query_posts('meta_key=_geotag_lon&posts_per_page=100');
				if (have_posts()) {
					$markers = Array();
					while (have_posts()) {
						the_post();
						$customVars = get_post_custom();
						if (count($customVars["_geotag_lon"]) > 0 && count($customVars["_geotag_lat"]) > 0) {
							$marker = Array();
							$marker["latitude"] = $customVars["_geotag_lat"][0];
							$marker["longitude"] = $customVars["_geotag_lon"][0];
							$marker["title"] = get_the_title();
							$marker["link"] = apply_filters('the_permalink', get_permalink());
							$markers[] = $marker;
						}
					}
				}
?>
				<!--<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>-->
				<script type="text/javascript" src="http://www.google.com/jsapi?key=<?php echo MAPS_API_KEY; ?>"></script>
				<script type="text/javascript">
					var routes = Array(
						Array("http://maps.google.com/maps/ms?ie=UTF8&hl=en&msa=0&msid=107608591138069620838.000483c9381e263dc2585&output=kml", 39.181175, 136.999512, 6),
						Array("http://maps.google.com/maps/ms?ie=UTF8&hl=en&msa=0&msid=107608591138069620838.000460fa26e4fa3f3388d&output=kml", 34.139088, 131.824951, 8),
						Array("http://maps.google.com/maps/ms?ie=UTF8&hl=en&msa=0&msid=107608591138069620838.0004690d080fa205dd59b&output=kml", 34.372912, 133.868408, 9),
						Array("http://maps.google.com/maps/ms?ie=UTF8&hl=en&msa=0&msid=107608591138069620838.00046bb1a9525bcb339b9&output=kml", 34.818313, 135.237579, 9),
						Array("http://maps.google.com/maps/ms?ie=UTF8&hl=en&msa=0&msid=107608591138069620838.00046d627c53dceb92c30&output=kml", 35.906849, 137.120361, 8),
						Array("http://maps.google.com/maps/ms?ie=UTF8&hl=en&msa=0&msid=107608591138069620838.00046e5d3c2fbae660a34&output=kml", 36.239843, 139.073181, 9),
						Array("http://maps.google.com/maps/ms?ie=UTF8&hl=en&msa=0&msid=107608591138069620838.00046e8a89c3b0f42312d&output=kml", 37.185958, 139.980009, 8),
						Array("http://maps.google.com/maps/ms?ie=UTF8&hl=en&msa=0&msid=118207083490334577275.00046e879bf787643386d&output=kml", 39.75788, 141.328125, 8),
						Array("http://maps.google.com/maps/ms?ie=UTF8&hl=en&msa=0&msid=107608591138069620838.00045c2454629c948b4f8&output=kml", 43.612217, 141.778564, 7)
					);
					var markers = eval('<?php echo json_encode($markers); ?>');
					var map = null;
					var routeOverlay = null;
					google.load("maps", "2.x");

					function initializeRouteMap() {
						// create basic map
						map = new google.maps.Map2(document.getElementById("routeMap"));
					    map.setCenter(new google.maps.LatLng(routes[0][1], routes[0][2]), routes[0][3]);
					    map.addControl(new GLargeMapControl());
					    map.addControl(new GMapTypeControl());
					    map.enableScrollWheelZoom(); 
					    // add route overlay
					    setRoute(0);
					    // add markers for each post
					    var marker;
					    for (var i=0; i<markers.length; i++) {
						    opts = {title: markers[i].title};
						    marker = new GMarker (new google.maps.LatLng(markers[i].latitude, markers[i].longitude), opts);
						    marker.linkUrl = markers[i].link;
						    map.addOverlay(marker);
						    GEvent.addListener (marker, "click", function () {
							    document.location.href = this.linkUrl;
						    });
					    }
					}
					function setRoute (routeIndex) {
						if (routeOverlay != null) {
							map.removeOverlay(routeOverlay);
						}
					    //var geoxml = new GGeoXml("http://maps.google.com/maps/ms?ie=UTF8&hl=en&msa=0&msid=107608591138069620838.000483c9381e263dc2585&output=kml");
					    routeOverlay = new GGeoXml(routes[routeIndex][0]);
					    map.addOverlay(routeOverlay);
					    map.setCenter(new google.maps.LatLng(routes[routeIndex][1], routes[routeIndex][2]), routes[routeIndex][3]);
					}
					google.setOnLoadCallback(initializeRouteMap);
				</script>
				<?php 
					route_map();
				?>
			</td>
		</tr>
	</table>
</div>

<?php get_footer(); ?>

