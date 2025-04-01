<?php

define('MIN_PANEL_UPLIFT', 1.3);
define('MAX_PANEL_UPLIFT', 1.5);
define('MIN_PANEL_UPLIFT_DC', 1.1);
define('MAX_PANEL_UPLIFT_DC', 1.7);

function page_header($title){
	$html ='<!DOCTYPE html>
			<html>
				<head>
					<meta charset="UTF-8" />
					<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
					<meta http-equiv="X-UA-Compatible" content="IE=edge">
					<meta name="description" content="Davis & Shirtliff SolarCalc Solar Sizing" />
					<meta name="author" content="Davis & Shirtliff">
					<meta name="generator" content="Davis & Shirtliff Digital Business">
					<meta name="theme-color" content="#eb1b23">
					<title>' .$title. '</title>
					<link href="img/Favicon.png" rel="icon" />
					<link href="css/bootstrap.min.css" rel="stylesheet">
					<link href="css/layout.css" rel="stylesheet">
					<link href="css/all.min.css" rel="stylesheet">
				</head>
			<body>
			<div class="page-wrapper">
				<div class="page-preloader"><i class="fas fa-sun fa-spin"></i></div>
			</div>';
	return $html;
}

function page_footer(){

	$html ='<a href="#" class="btn-dark btn feedback-button" data-toggle="modal" data-target=".feedback-modal">Do you have any feedback?</a>
			<div class="modal fade feedback-modal" tabindex="-1" role="dialog">
			<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
			<div class="modal-content">
			<div class="modal-header">
			<h5 class="modal-title">Feedback</h5>
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
			<form class="feedback-form">
			<div class="form-group">
			<input type="text" name="user_subject" class="form-control" placeholder="Please enter subject" required="required">
			</div>
			<div class="form-group">
			<textarea class="form-control" name="user_feedback" placeholder="Please enter your feedback below" required="required"></textarea>
			</div>
			</form>
		    </div>
		    <div class="modal-footer">
		    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
		    <button type="button" class="btn btn-primary submit-feedback">Send</button>
		    </div>
		    </div>
		    </div>
		    </div>
			<!-- End Feedback Modal -->
			<script src="js/jquery-3.3.1.min.js"></script>
			<script src="js/bootstrap.bundle.min.js"></script>
			<script src="js/highchart.min.js"></script>
			<script src="https://maps.google.com/maps/api/js?key=AIzaSyDc7DcO9HEL0__epR4GTePnExXkyfduo58"></script>
			<script src="js/highchart.export.js"></script>
			<script src="js/canvasg.js"></script>
			<script src="js/app.js"></script>
			</body>
			</html>';
	return $html;
}

function page_login_footer(){

	$html ='<script src="js/jquery-3.3.1.min.js"></script>
			<script src="js/bootstrap.bundle.min.js"></script>
			<script src="js/app.js"></script>
			</body>
			</html>';
	return $html;

}

function flat_menu($items){

	$html = '<nav class="login-menu">';
	foreach($items as $item){
		if($item->status){
			$html .= '<a href="' .$item->url. '" class="' .$item->class. '" target="' .$item->target. '">' .$item->text. '</a>';
		} else {
			$html .= '<a class="' .$item->class. '" target="' .$item->target. '">' .$item->text. '</a>';
		}
		
	}
	$html .= '</nav>';
	return $html;

}

function getloggedinuser(){

	$connection = new mysqli(DB_HOST, DB_USR, DB_PWSD, DB_DB);
	$unique_id = $_COOKIE['unique_id'];
	$token = $_COOKIE['unique_token'];
	$query = $connection->query("SELECT u.user_id, u.unique_id, u.fullname, u.email, u.phone, u.country, u.company, u.user_origin, u.invitation_code FROM users u, user_sessions us WHERE u.unique_id = us.user_unique_id AND u.unique_id = '" .$connection->real_escape_string($unique_id). "' AND us.user_session_token = '" .$connection->real_escape_string($token). "'");
	// echo '<pre>';
	// print_r($connection);
	// echo '</pre>';

	return $query->fetch_object();

}

function top_nav($title){

	$user = getloggedinuser();

	$html = '<nav class="main-navigation fixed-top">
				<a href="index" class="float-left"><img src="img/Main-Logo.png" class="img-fluid" title="SOLARCALC" /></a>
				<ul class="nav nav-tabs float-right top-navigation">
					<a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Hi <strong>' .$user->fullname. '</strong></a>
					<div class="dropdown-menu">
						<a class="dropdown-item navigation-item" href="index">Home</a>
						<a class="dropdown-item navigation-item" href="#" data-toggle="modal" data-target=".account-settings" data-page="account">Account / Profile</a>
						<a class="dropdown-item navigation-item" href="#" data-toggle="modal" data-target=".account-settings" data-page="company">Company Settings</a>
						<a class="dropdown-item navigation-item d-none" href="#" data-toggle="modal" data-target=".account-settings" data-page="projects">Saved Projects</a>
						<a class="dropdown-item navigation-item" href="#" data-toggle="modal" data-target=".account-settings" data-page="customers">Customers List</a>
						<a class="dropdown-item navigation-item save-project-button d-none" href="#" data-toggle="modal" data-target=".account-settings" data-page="saveproject">Save Project</a>
						<a class="dropdown-item navigation-item" href="updates">Updates</a>
						<div class="dropdown-divider"></div>
						<a class="dropdown-item log-out" href="#">Log Out</a>
				    </div>
				</ul>
				<!--<p class="float-right">Hi <strong>' .$user->fullname. '</strong> <i class="fas fa-power-off log-out"></i></p>-->
				<div class="clearfix"></div>
			</nav>';

	return $html;

}

function top_nav_update(){

	$html ='<nav class="main-navigation fixed-top updates-page">
				<a href="index" class="float-left"><img src="img/Main-Logo.png" class="img-fluid" title="SOLARCALC" /></a>
				<div class="dropdown float-right">
					<button class="btn dropdown-toggle btn-lg" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
						<i class="fas fa-bars"></i>
					</button>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="index">Home</a>
						<a class="dropdown-item" href="updates">Updates</a>
				    </div>
				</div>
			</nav>
			';

	return $html;

}

function getcompany($user_origin, $unique_id, $invitation_code){

	$connection = new mysqli(DB_HOST, DB_USR, DB_PWSD, DB_DB);
	if($user_origin == 1){
		$query = $connection->query("SELECT * FROM companies WHERE unique_id = '" .$connection->real_escape_string($unique_id). "'");
		if($query->num_rows){
			return $query->fetch_object();
		} else {
			return false;
		}
	} else {
		$query = $connection->query("SELECT * FROM companies WHERE invitation_code = '" .$connection->real_escape_string($invitation_code). "'");
		return $query->fetch_object();
	}

}

function getcustomerslist($unique_id){

	$connection = new mysqli(DB_HOST, DB_USR, DB_PWSD, DB_DB);
	$query = $connection->query("SELECT * FROM customers where unique_id = '" .$connection->real_escape_string($unique_id). "' ORDER BY customer_id DESC");

	$customers = array();
	while($item = $query->fetch_object()){
		$customers[] = (Object)array(
			'customer_name' => $item->customer_name,
			'customer_account' => $item->customer_account,
			'customer_telephone' => $item->customer_telephone,
			'customer_email' => $item->customer_email,
			'customer_physical' => $item->customer_physical,
			'customer_postal' => $item->customer_postal,
			'customer_country' => $item->customer_country
		);
	}

	return $customers;

}

function systempanels(){

	$connection = new mysqli(DB_HOST, DB_USR, DB_PWSD, DB_DB);
	$query = $connection->query("SELECT * FROM panels WHERE custom_status = 0 AND status = 1 ORDER BY panel_rated_power_w DESC");

	$panels = array();
	while($item = $query->fetch_object()){
		$panels[] = (Object)array(
			'panel_id' => $item->panel_id,
			'panel_model' => $item->panel_model,
			'part_number' => $item->part_number,
			'panel_rated_power_w' => $item->panel_rated_power_w,
			'peak_voltage' => $item->peak_voltage,
			'open_circuit_voltage' => $item->open_circuit_voltage,
			'short_circuit_current' => $item->short_circuit_current,
			'nominal_voltage' => $item->nominal_voltage,
			'module_dimensions' => $item->module_dimensions,
		);
	}

	return $panels;

}

function custompanels(){

	$unique_id = $_COOKIE['unique_id'];

	$connection = new mysqli(DB_HOST, DB_USR, DB_PWSD, DB_DB);
	$query = $connection->query("SELECT * FROM panels WHERE custom_status = 1 AND status = 1 AND user = '" .$connection->real_escape_string($unique_id). "' ORDER BY panel_rated_power_w DESC");

	$panels = array();

	while($item = $query->fetch_object()){
		$panels[] = (Object)array(
			'panel_id' => $item->panel_id,
			'panel_model' => $item->panel_model,
			'part_number' => $item->part_number,
			'panel_rated_power_w' => $item->panel_rated_power_w,
			'peak_voltage' => $item->peak_voltage,
			'open_circuit_voltage' => $item->open_circuit_voltage,
			'short_circuit_current' => $item->short_circuit_current,
			'nominal_voltage' => $item->nominal_voltage,
			'module_dimensions' => $item->module_dimensions,
		);
	}

	return $panels;

}

function getprojects($user_id){
	$connection = new mysqli(DB_HOST, DB_USR, DB_PWSD, DB_DB);
	$query = $connection->query("SELECT * FROM projects WHERE user_id = '" .$connection->real_escape_string($user_id). "' ORDER BY project_id DESC");
	$projects = array();
	while($item = $query->fetch_object()){
		$projects[] = (Object)array(
			'project_id'		=>		$item->project_id,
			'project_code'		=>		$item->project_code,
			'project_name'		=>		$item->project_name,
			'customer'			=>		getcustomer($item->customer_id),
			'location_name'		=>		$item->location_name,
			'project_details'	=>		unserialize($item->project_details),
			'project_date'		=>		date('D, d/M/Y', strtotime($item->date_added))
		);
	}
	return $projects;
}

function getproject($user_id, $project_code){
	$connection = new mysqli(DB_HOST, DB_USR, DB_PWSD, DB_DB);
	$query = $connection->query("SELECT * FROM projects WHERE user_id = '" .$connection->real_escape_string($user_id). "' AND project_code = '" .$connection->real_escape_string($project_code). "' ORDER BY project_id DESC");

	$item = $query->fetch_object();	
	$project = (Object)array(
		'project_code'		=>		$item->project_code,
		'project_name'		=>		$item->project_name,
		'customer'			=>		getcustomer($item->customer_id),
		'location_name'		=>		$item->location_name,
		'project_details'	=>		unserialize($item->project_details),
		'project_date'		=>		$item->date_added
	);

	return $project;

}

function getprojectbyid($user_id, $project_id){
	$connection = new mysqli(DB_HOST, DB_USR, DB_PWSD, DB_DB);
	$query = $connection->query("SELECT * FROM projects WHERE user_id = '" .$connection->real_escape_string($user_id). "' AND project_id = '" .(int)$project_id. "' ORDER BY project_id DESC");
	
	$item = $query->fetch_object();
	$project = (Object)array(
		'project_code'		=>		$item->project_code,
		'project_name'		=>		$item->project_name,
		'customer'		=>		getcustomer($item->customer_id),
		'location_name'	=>		$item->location_name,
		'project_details'	=>		unserialize($item->project_details),
		'project_date'		=>		$item->date_added,
		'company_id'		=>		$item->company_id,
		'print_pdf'		=>		$item->print_pdf
	);

	return $project;

}

function getcustomer($customer_id){

	$connection = new mysqli(DB_HOST, DB_USR, DB_PWSD, DB_DB);
	$query = $connection->query("SELECT * FROM customers WHERE customer_id = '" .(int)$customer_id. "'");
	if($query->num_rows > 0){
		return $query->fetch_object();
	} else {
		return false;
	}
	
}

function getcompanies($unique_id){

	$connection = new mysqli(DB_HOST, DB_USR, DB_PWSD, DB_DB);
	$query = $connection->query("SELECT * FROM companies WHERE unique_id = '" .$connection->real_escape_string($unique_id). "'");

	if($query->num_rows > 0){
		return $query->fetch_object();
	} else {
		return false;
	}

}

function defaultcompany(){

	 return (Object)array(
		'company_id' => 000000,
		'company_name' => 'Davis & Shirtliff',
		'physical_location' => 'Industrial Area, Dundori Road, Nairobi',
		'postal_address' => 'P.O. Box: 41762-00100, Kenya',
		'company_phone' => '+254 020 6968 000, +254 711 079 200',
		'company_website' => 'www.davisandshirtliff.com',
		'company_email' => 'contactcenter@dayliff.com',
		'company_logo' => 'Report-Logo.jpg'
	);

}

function getusercompany($company_id){

	if($company_id == 0){
		return defaultcompany();
	} else {
		
		// $unique_id = $_COOKIE['unique_id'];
		$connection = new mysqli(DB_HOST, DB_USR, DB_PWSD, DB_DB);
		// $query = $connection->query("SELECT * FROM companies WHERE company_id = " .(int)$company_id. " AND unique_id = '" .$connection->real_escape_string($unique_id). "'");		
		$query = $connection->query("SELECT * FROM companies WHERE company_id = " .(int)$company_id. "");
		return $query->fetch_object();

	}

}

function getusercompanybyid($company_id){

	if($company_id == 0){
		return defaultcompany();
	} else {

		$unique_id = $_COOKIE['unique_id'];

		$connection = new mysqli(DB_HOST, DB_USR, DB_PWSD, DB_DB);
		$query = $connection->query("SELECT * FROM companies WHERE company_id = " .(int)$company_id. " AND unique_id = '" .$connection->real_escape_string($unique_id). "'");

		return $query->fetch_object();

	}

}

function gethost(){
	return HOST_LINK;
}

function pipematerials($index){
	$material = array('HDPE', 'LDPE', 'uPVC', 'Rubber Lined', 'New Steel', 'Medium Lined', 'Corroded Steel');
	return $material[(int)$index];	
}

function getproduct($product_id){

	$fields = array(
		'product_id' => (int)$product_id
	);
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://www.davisandshirtliff.com/index.php?option=com_hikashop&ctrl=productinfo&format=raw',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => http_build_query($fields),
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_HTTPHEADER => array(
			'cache-control: no-cache'
		),
	));
	
	$response = json_decode(curl_exec($curl), true);
	$err = curl_error($curl);
	curl_close($curl);
	return $response;

}

function searchcustomers($query){

	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://davisandshirtliff.com/davis-api/apiKE.php',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => http_build_query($query),
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_HTTPHEADER => array(
			'cache-control: no-cache'
		),
	));

	$response = json_decode(curl_exec($curl), true);
	$err = curl_error($curl);
	curl_close($curl);
	return $response;

}

function getmotorpowers($phase){

	$connection = new mysqli(DB_HOST, DB_USR, DB_PWSD, DB_DB);
	if($phase == 1){
		$query = $connection->query("SELECT DISTINCT(motor_kw) FROM equipment WHERE fullload_current_single_phase != '' ORDER BY motor_kw ASC");
	} else {
		$query = $connection->query("SELECT DISTINCT(motor_kw) FROM equipment WHERE fullload_current_three_phase != '' ORDER BY motor_kw ASC");
	}

	$list = array();
	while($item = $query->fetch_object()){
		$list[] = $item->motor_kw;
	}

	return $list;

}

function getpanel($panel_model, $product_id){

	$connection = new mysqli(DB_HOST, DB_USR, DB_PWSD, DB_DB);
	$query = $connection->query("SELECT * FROM panels WHERE panel_model = '" .$connection->real_escape_string($panel_model). "' AND product_id = " .(int)$product_id. "");
	return $query->fetch_object();

}

function getpvdisconnect($panels_count, $strings_count, $short_circuit_current, $panel_voc){

	// $connection = new mysqli(DB_HOST, DB_USR, DB_PWSD, DB_DB);
	// $vdc = $panel_voc * $panels_count;
	// $query = $connection->query("SELECT * FROM pv_disconnect WHERE max_vdc > " .(float)$vdc. " AND  LIMIT 1");
	// $item = $query->fetch_object();
	// $equipment = (Object)array(
	// 	'max_current'			=> (float)$item->max_current,
	// 	'max_vdc'				=> (float)$item->max_vdc,
	// 	'pv_disconnect_make'	=> $item->pv_disconnect_make,
	// 	'pv_disconnect_model' 	=> $item->pv_disconnect_model
	// );		
	// $option_a = $strings_count / 5;
	// $option_b = ($short_circuit_current * $strings_count  / 40);
	// if($strings_count >= 5){
	// 	$a = ceil($option_a);
	// 	$b = ceil($option_b);
	// 	if($a > $b){
	// 		$n = $a;
	// 	} else {
	// 		$n = $b;
	// 	}
	// } else {
	// 	$n = ceil($option_b);
	// }
	// $pv_disconnect = (Object)array(
	// 	'pv_count'		=>	$n,
	// 	'pv_disconnect'	=>	$equipment
	// );

	// Dayliff PV Disconnect
	// $details = (Object)$data;
	$total_system_currect = $strings_count * $short_circuit_current;
	// Possible 2ST options
	$x = $strings_count / 2;
	// Possible 4ST options
	$y = $strings_count / 4;
	if($total_system_currect < 16){
		if($x <= 1){
			$n = (Object)array(
				'quantity' => 1,
				'model' => 'DAYLIFF 2ST 1000V/16A PV Disconnect Switch',
				'other' => (Object)array(
					'quantity' => 0
				)
			);
		} else if($y <= 1) {
			$n = (Object)array(
				'quantity' => 1,
				'model' => 'DAYLIFF 4ST 1000V/32A PV Disconnect Switch',
				'other' => (Object)array(
					'quantity' => 0
				)
			);
		} else {				
			$parts = explode('.', $y);
			$fours = (int)$parts[0];
			$twos = ((int)$parts[1] / 100) * 4;

			if($twos <= 2){
				$quantity = 1;
				$model = 'DAYLIFF 2ST 1000V/16A PV Disconnect Switch';
				$n = (Object)array(
					'twos' => $twos,
					'fours' => $fours,
					'quantity' => $fours,
					'model' => 'DAYLIFF 4ST 1000V/32A PV Disconnect Switch',
					'other' => (Object)array(
							'quantity' => $quantity,
							'model' => $model
						)
					);
			} else {
				$n = (Object)array(
					'twos' => $twos,
					'fours' => $fours,
					'quantity' => ceil($y),
					'model' => 'DAYLIFF 4ST 1000V/32A PV Disconnect Switch',
					'other' => (Object)array(
						'quantity' => 0
					)
				);

			}
		}
	} else {
		$a = 32 / $short_circuit_current;
		$b = floor($a);
		$c = $strings_count / $b;
		$d = ceil($c);
		$e = $strings_count / 4;
		$f = ceil($e);
		$n = (Object)array(
			'params' => (Object)array(
				'a' => $a,
				'b' => $b,
				'c' => $c,
				'd' => $d,
				'e' => $e,
				'f' => $f
			),
			'quantity' => $d > $f ? $d : $f,
			'model' => 'DAYLIFF 4ST 1000V/32A PV Disconnect Switch',
			'other' => (Object)array(
						'quantity' => 0
				)
		);
	}
	$result = (Object)array(
		'total_system_currect' => $total_system_currect,
		'x' => $x,
		'y' => $y,
		'n' => $n
	);
	$equipment = (Object)array(
		'max_current'			=> $total_system_currect,
		'max_vdc'				=> 1000,
		'pv_disconnect_make'	=> 'DAYLIFF',
		'pv_disconnect_model' 	=> $n->model,
		'other' 				=> $n->other,
		'n' 					=> $n,
		'result' 				=> $result
	);
	$pv_disconnect = (Object)array(
		'pv_count'		=>	$n->quantity,
		'pv_disconnect'	=>	$equipment
	);
	
	return $pv_disconnect;

}

function getuser($user_id) {

	$connection = new mysqli(DB_HOST, DB_USR, DB_PWSD, DB_DB);
	$query = $connection->query("SELECT fullname FROM users WHERE unique_id = '" .$connection->real_escape_string($user_id). "' LIMIT 1");

	if($query->num_rows > 0){
		$user = $query->fetch_object();
	} else {
		$user = (Object)array(
			'fullname' => ''
		);
	}

	return $user->fullname;

}

function getUpdate($update_id){

	$connection = new mysqli(DB_HOST, DB_USR, DB_PWSD, DB_DB);
	$query = $connection->query("SELECT * FROM updates WHERE update_id = " .(int)$update_id. "");
	return $query->fetch_object();

}

function getUpdates(){

	$connection = new mysqli(DB_HOST, DB_USR, DB_PWSD, DB_DB);
	$query = $connection->query("SELECT * FROM updates ORDER BY update_id DESC");

	$updates = array();
	while($item = $query->fetch_object()){
		$updates[] = $item;
	}
	return $updates;

}

function getpumpcategories($notincluded){

	$connection = new mysqli(DB_HOST, DB_USR, DB_PWSD, DB_DB);
	$query = $connection->query("SELECT * FROM categories WHERE category_status = 1 AND category_id NOT IN (" .implode(',', $notincluded). ") ORDER By category_name ASC");

	$list = array();
	while($item = $query->fetch_object()){
		$list[] = $item;
	}
	return $list;

}