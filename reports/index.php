<?php

require_once('../config.php');
require_once('../functions.php');
$action = $_GET['action'];
$data = $_POST;
$connection = new mysqli(DB_HOST, DB_USR, DB_PWSD, DB_DB);

$projects = array();

switch ($action) {

	case 'projects':

		// $projects = array();
		$query = $connection->query("SELECT * FROM projects ORDER BY project_id ASC LIMIT 900");

		while($item = $query->fetch_object()){

			$details = unserialize($item->project_details);
			$solution = explode('|', $details->solutionstring);

			if($solution[9] == 'sunflo'){

				$panel = getpanel($solution[2], $solution[3]);
				$projects[] = (Object)array(
					'project_name'			=>		$item->project_name,
					'customer_name'		=>		$details->customer_name,
					'project_type'			=>		'SUNFLO',
					'project_type_icon'		=>		'sunflo.png',
					'pump'				=>		$solution[0],
					'inverter'			=>		'',
					'panel_type'			=>		$solution[2],
					'panel_power'			=>		$panel->panel_rated_power_w,
					'panel_arrangement'		=>		$solution[4] . ' x 1',
					'in_series'			=>		$solution[4],
					'in_parallel'			=>		'1',
					'project_location'		=>		$item->location_name,
					'borehole_depth'		=>		'',
					// 'solution'			=>		$solution,
					'project_date'			=>		$item->date_added,
					'gps'				=>		$details->sizing['latitude_info']. ',' .$details->sizing['longitude_info'],
					'lat'				=>		(float)$details->sizing['latitude_info'],
					'lng'				=>		(float)$details->sizing['longitude_info'],
					'project_info' 		=>		'<div class="project-information"><h3>' .strtoupper($item->project_name). '</h3><p>Customer : ' .$details->customer_name. '</p><p>Location : ' .$item->location_name. '</p><p>Project Type : SUNFLO</p><p>Pump : ' .$solution[0]. '</p><p>Panels : ' .$solution[2]. ' (Rating : ' .$panel->panel_rated_power_w. 'W. Arrangement : ' .$solution[4]. ' x 1)</p></div>',
				);

			} else {

				$panel = getpanel($solution[4], $solution[5]);

				$project_type = isset($solution[16]) ? strtoupper($solution[16]) : 'Solarization';

				$projects[] = (Object)array(
					'project_name'			=>		$item->project_name,
					'customer_name'		=>		$details->customer_name,
					'project_type'			=>		isset($solution[16]) ? strtoupper($solution[16]) : '',
					'project_type_icon'		=>		isset($solution[16]) ? strtolower($solution[16]). '.png' : 'solarization.png',
					'pump'				=>		$solution[0],
					'inverter'			=>		$solution[2],
					'panel_type'			=>		$solution[4],
					'panel_power'			=>		$panel->panel_rated_power_w,
					'panel_arrangement'		=>		$solution[6] . ' x ' .$solution[7],
					'in_series'			=>		$solution[6],
					'in_parallel'			=>		$solution[7],
					'project_location'		=>		$item->location_name,
					'borehole_depth'		=>		$details->sizing['borehole_depth'],
					// 'solution'			=>		$solution,
					'project_date'			=>		$item->date_added,
					'gps'				=>		$details->sizing['latitude_info']. ',' .$details->sizing['longitude_info'],
					'lat'				=>		(float)$details->sizing['latitude_info'],
					'lng'				=>		(float)$details->sizing['longitude_info'],
					'project_info' 		=>		'<div class="project-information"><h3>' .strtoupper($item->project_name). '</h3><p>Customer : ' .$details->customer_name. '</p><p>Location : ' .$item->location_name. '</p><p>Project Type : ' .$project_type. '</p><p>Pump : ' .$solution[0]. '</p><p>Inverter : ' .$solution[2]. '</p><p>Panels : ' .$solution[4]. ' (Rating : ' .$panel->panel_rated_power_w. 'W. Arrangement : ' .$solution[6] . ' x ' .$solution[7]. ')</p><p>Borehole Depth : ' .$details->sizing['borehole_depth']. 'm</p></div>',
				);

			}
			// $projects[] = $details;

		}

		// echo '<pre>';
		// print_r($projects);
		// echo '</pre>';

		break;

	case 'saved':
		# code...
		break;
	
	default:
		# code...
		break;

}

?>
<!DOCTYPE html>
<html>
<head>
	<title>SolarCalc  - <?php echo ucwords($action); ?></title>
	<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>
	<script src="https://maps.google.com/maps/api/js?key=AIzaSyB_FkVvtgP7ZqL7VU1wdMfdF_DYjpoHoj4" type="text/javascript"></script>
	<link rel="stylesheet" type="text/css" href="layout.css">
</head>
<body>
	<div id="map" class="map"></div>
	<script type="text/javascript" src="jquery-3.3.1.min.js"></script>
	<script type="text/javascript" src="app.js"></script>
	<script type="text/javascript">
		var locations = <?php echo json_encode($projects); ?>;
		var map = new google.maps.Map(document.getElementById('map'), {
			zoom: 6,
			center: new google.maps.LatLng(-1.504634, 37.330890),
			mapTypeId: 'roadmap'
		});

		// Load the locations
		var infowindow = new google.maps.InfoWindow();
		var markers = locations.map(function(location, i) {

			var image = new google.maps.MarkerImage(
				'images/' +location.project_type_icon,
				null,
				null,
				new google.maps.Point(8, 8),
				new google.maps.Size(17, 17)
			);

			var marker = new google.maps.Marker({
				flat : true,
				position: location,
				icon : image,
				optimized : false,
				visible : true
			});

			google.maps.event.addListener(marker, 'click', function(evt) {
				infowindow.setContent(location.project_info);
				infowindow.open(map, marker);
				// getstockist(location.stockist_ref, location.colour);
			});
			return marker;
		});

		var markerCluster = new MarkerClusterer(map, markers, {
			imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m',
			maxZoom : 19
		});
	</script>
</body>
</html>