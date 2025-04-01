<?php
// Require Config
require_once('config.php');
require_once('functions.php');

$connection = new mysqli(DB_HOST, DB_USR, DB_PWSD, DB_DB);
$query = $connection->query("SELECT * FROM projects ORDER BY project_id DESC");

$projects = array();

while($item = $query->fetch_object()){
	
	$details = unserialize($item->project_details);
	$solution = explode('|', $details->solutionstring);

	if($solution[9] == 'sunflo'){

		$panel = getpanel($solution[2], $solution[3]);
		$projects[] = (Object)array(
			'customer_name'		=>		$details->customer_name,
			'project_type'			=>		'SUNFLO',
			'pump'				=>		$solution[0],
			'inverter'			=>		'',
			'panel_type'			=>		$solution[2],
			'panel_power'			=>		$panel->panel_rated_power_w,
			'panel_arrangement'		=>		$solution[4] . ' x 1',
			'in_series'			=>		$solution[4],
			'in_parallel'			=>		'1',
			'project_location'		=>		$item->location_name,
			'borehole_depth'		=>		'',
			'solution'			=>		$solution,
			'project_date'			=>		$item->date_added,
			'project_owner'		=>		getuser($item->user_id)
		);

	} else {

		$panel = getpanel($solution[4], $solution[5]);
		$projects[] = (Object)array(
			'customer_name'		=>		$details->customer_name,
			'project_type'			=>		isset($solution[16]) ? strtoupper($solution[16]) : '',
			'pump'				=>		$solution[0],
			'inverter'			=>		$solution[2],
			'panel_type'			=>		$solution[4],
			'panel_power'			=>		$panel->panel_rated_power_w,
			'panel_arrangement'		=>		$solution[6] . ' x ' .$solution[7],
			'in_series'			=>		$solution[6],
			'in_parallel'			=>		$solution[7],
			'project_location'		=>		$item->location_name,
			'borehole_depth'		=>		$details->sizing['borehole_depth'],
			'solution'			=>		$solution,
			'project_date'			=>		$item->date_added,
			'project_owner'		=>		getuser($item->user_id)
		);

	}

}

// echo '<pre>';
echo '<table border="1" style="font-size:11px; font-family: Arial; border-collapse: collapse" cellpadding="3">
<tr>
	<td>Report Date</td>
	<td>Customer Name</td>
	<td>Type</td>
	<td>Pump</td>
	<td>Inverter</td>
	<td>Panels</td>
	<td>Watt</td>
	<td>Arrangement</td>
	<td>Number/String</td>
	<td>String</td>
	<td></td>
	<td></td>
	<td>Location</td>
	<td>BH Depth</td>
	<td>Engineer</td>
	</tr>';
// print_r($projects);
foreach($projects as $project){
	echo '<tr>';
	echo '<td>' .$project->project_date. '</td>';
	echo '<td>' .$project->customer_name. '</td>';
	echo '<td>' .$project->project_type. '</td>';
	echo '<td>' .$project->pump. '</td>';
	echo '<td>' .$project->inverter. '</td>';
	echo '<td>' .$project->panel_type. '</td>';
	echo '<td>' .$project->panel_power. '</td>';
	echo '<td>' .$project->panel_arrangement. '</td>';
	echo '<td>' .$project->in_series. '</td>';
	echo '<td>' .$project->in_parallel. '</td>';
	echo '<td></td>';
	echo '<td></td>';
	echo '<td>' .$project->project_location. '</td>';
	echo '<td>' .$project->borehole_depth. '</td>';
	echo '<td>' .$project->project_owner. '</td>';
	echo '</tr>';
}
echo '</table>';
// echo '</pre>';