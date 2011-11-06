<!doctype html>
<html>
<head>

	<meta charset="utf-8">

	<title>Geoply - The MongoDB Geo-Location Toolkit</title>

	<script src="http://maps.googleapis.com/maps/api/js?sensor=true"></script>
	<script src="js/jquery-1.6.4.min.js"></script>

	<link rel="stylesheet" href="css/g-spot.css">
	<script src="js/g-spot.js"></script>

	<!--[if lt IE 9]>
		<style>.git-ribbon { top: -2em; left: -2em; }</style>
	<![endif]-->

	<script>

		/* OPTIONS */
		options = {
            zoom: 13, // This allows you to select a zoom level
			type: 'ROADMAP', // This allows you to pick the tap of map to use
			lat: false, // This is a custom coordinate to use for centering map, otherwise browser attempts to use HTML5 to find current location
			lng: false, // This is a custom coordinate to use for centering map, otherwise browser attempts to use HTML5 to find current location
			imgs: 'img', // This is the base location of images and markers
			markers: false, // This allows you to manually input a marker array
			check_markers: false // This takes time but prevents missing icons from NOT being display by automatically using the default icon if custom icon is not found
        };

		/* CREATING A MAP IS THIS SIMPLE */
		$(document).ready(function(){
			$('#gspot').gSpot(options);
		});

	</script>

	<?php

	/* THIS PHP EXAMPLE ALLOWS FOR CUSTOM QUERIES VIA URL */
	/* AS DOES THE JSON.php FILE DIRECTLY */
	
	/* CURRENTLY DYNAMIC VARIABLES INCLUDE */

	/* limit = number of markers to query */
	if(isset($_GET['limit'])) $limit = (int)$_GET['limit'];
	else $limit = 500;

	/* feature_code = only show places with this feature_code */
	if(isset($_GET['feature_code'])) $feature_code = $_GET['feature_code'];
	else $feature_code = false;

	?>

</head>
<body>

	<div class="git-ribbon"><a href="https://github.com/msmalley/Geoply">Fork Me on GitHub</a></div>

	<div id="header">
		<h1>Geoply</h1>
		<span class="social-media-links">
			<a href="http://twitter.com/m_smalley" class="twitter">Twitter</a>
			<a href="http://facebook.com/mark.smalley" class="facebook">Facebook</a>
			<a href="http://www.linkedin.com/profile/view?id=10311122" class="linkedin">Linked-In</a>
		</span>
	</div>
	
	<div id="map-wrapper">

		<div id="gspot" data-ajax="json.php?limit=<?php echo $limit; ?>&feature_code=<?php echo $feature_code; ?>"></div>

	</div>

</body>
</html>