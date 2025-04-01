<?php
// Require Config
require_once('config.php');
require_once('functions.php');

require_once 'libs/dompdf/lib/html5lib/Parser.php';
require_once 'libs/dompdf/lib/php-font-lib/src/FontLib/Autoloader.php';
require_once 'libs/dompdf/lib/php-svg-lib/src/autoload.php';
require_once 'libs/dompdf/src/Autoloader.php';

Dompdf\Autoloader::register();
use Dompdf\Dompdf;

$action = $_GET['action'];
$data = $_POST;

$connection = new mysqli(DB_HOST, DB_USR, DB_PWSD, DB_DB);

switch ($action) {
	case 'register':

		$account = (Object)$data;

		// Let us check the Invitation Code
		$invite_code = $account->invite_code;

		// Check if the Invitation Code is valid
		$company_query = $connection->query("SELECT * FROM companies WHERE invitation_code = '" .$connection->real_escape_string($account->invite_code). "'");

		if($company_query->num_rows > 0){

			$company = $company_query->fetch_object();
			$query = $connection->query("SELECT * FROM users WHERE email = '" .$connection->real_escape_string(strtolower($account->email)). "'");
			if($query->num_rows > 0){
				$response = array(
					'text' 	=>	'EMAIL ALREADY IN USE <i class="fas fa-exclamation-triangle"></i>',
					'status'	=>	2
				);
			} else {

				$unique_id = randomstring(15);
				$timestamp = randomstring(2) . date('dmYHis') . $unique_id;
				$password = hash('sha256', strtolower($account->email) . $unique_id). ':' .hash('sha1', $timestamp . $account->password);
				$insert = $connection->query("INSERT INTO users SET unique_id = '" .$connection->real_escape_string($unique_id). "', fullname = '" .$connection->real_escape_string(ucwords($account->fullname)). "', email = '" .$connection->real_escape_string($account->email). "', phone = '" .$connection->real_escape_string($account->phone). "', country = '" .$connection->real_escape_string($account->country). "', company = '" .$connection->real_escape_string($company->company_name). "', invitation_code = '" .$connection->real_escape_string($company->invitation_code). "', agree = '" .$connection->real_escape_string($account->agree). "', password = '" .$connection->real_escape_string($password). "', timestamp = '" .$connection->real_escape_string($timestamp). "', date_created = NOW(), date_modified = NOW(), status = 1, user_origin = 2");

				if($insert){
					// Set Session
					// setsession($unique_id, '', $connection);
					setsitecookie($unique_id, 2, $connection);
					// Send Email
					sendmail($account->email, $account->fullname, false);
					// Send Text
					$response = array(
						'text'	=>	'CONGRATULATIONS. ACCOUNT CREATED <i class="fas fa-check-circle"></i>',
						'status'	=>	1
					);
				} else {
					$response = array(
						'text'	=>	'ERROR, TRY AGAIN <i class="fas fa-exclamation-triangle"></i>',
						'status'	=>	0
					);
				}
			}
		} else {
			$response = array(
				'text' 	=>	'INVALID INVITE CODE <i class="fas fa-exclamation-triangle"></i>',
				'status'	=>	2
			);
		}

		print_r(json_encode($response));

		break;

	case 'msregister':

		$account = (Object)$data;
		$query = $connection->query("SELECT * FROM users WHERE email = '" .$connection->real_escape_string(strtolower($account->userPrincipalName)). "'");

		if($query->num_rows > 0){

			$response = array(
				'status' => 2
			);

			$item = $query->fetch_object();

			$unique_id = $item->unique_id;
			$timestamp = randomstring(2) . date('dmYHis') . $unique_id;
			$password = hash('sha256', strtolower($account->userPrincipalName) . $unique_id). ':' .hash('sha1', $timestamp . $account->id);

			// Update the Record and then Log in the User
			$update = $connection->query("UPDATE users SET unique_id = '" .$connection->real_escape_string($unique_id). "', fullname = '" .$connection->real_escape_string($account->displayName). "', phone = '" .$connection->real_escape_string($account->mobilePhone). "', company = '" .$connection->real_escape_string('D&S'). "', agree = '" .$connection->real_escape_string('on'). "', password = '" .$connection->real_escape_string($password). "', timestamp = '" .$connection->real_escape_string($timestamp). "', user_origin = 'ms', date_modified = NOW(), status = 1 WHERE email = '" .$connection->real_escape_string($account->userPrincipalName). "'");

		} else {

			$unique_id = randomstring(15);
			$timestamp = randomstring(2) . date('dmYHis') . $unique_id;
			$password = hash('sha256', strtolower($account->userPrincipalName) . $unique_id). ':' .hash('sha1', $timestamp . $account->id);

			$insert = $connection->query("INSERT INTO users SET unique_id = '" .$connection->real_escape_string($unique_id). "', fullname = '" .$connection->real_escape_string($account->displayName). "', email = '" .$connection->real_escape_string($account->userPrincipalName). "', phone = '" .$connection->real_escape_string($account->mobilePhone). "', country = '" .$connection->real_escape_string('KE'). "', company = '" .$connection->real_escape_string('D&S'). "', agree = '" .$connection->real_escape_string('on'). "', password = '" .$connection->real_escape_string($password). "', timestamp = '" .$connection->real_escape_string($timestamp). "', user_origin = 'ms', date_created = NOW(), date_modified = NOW()");

			if($insert){

				sendmail($account->userPrincipalName, $account->displayName, true);
				// Send Text
				$response = array(
					'status' => 1
				);

			} else {
				$response = array(
					'status' => 0
				);
			}

		}

		print_r(json_encode($response));

		break;

	case 'login':

			$account = (Object)$data;

			if(strpos(strtolower($account->email), 'dayliff.com')){
				
				// Get Staff Information from Microsoft Graph
				$staff_details = getstaffdetails($account);
				$response = $staff_details;
				// Add the user or update the use in Database
				if($response->staff_status == true){
					$add = addupdateuser($response, $connection);
					// Handle Login Hack
					$query = $connection->query("SELECT * FROM users WHERE LOWER(email) = '" .$connection->real_escape_string(strtolower($account->email)). "' AND status = 1");
					$item = $query->fetch_object();
					// setsession($item->unique_id, $item->user_origin, $connection);
					setsitecookie($item->unique_id, $item->user_origin, $connection);
					$result = array(
						'text'		=> 'WELCOME TO SOLARCALC <i class="fas fa-check-circle"></i>',
						'status' 		=> 1,
						'user_origin'	=> $item->user_origin,
						'response'	=> $response,
						'add'		=> $add
					);
				} else {
					$result = array(
						'text' => 'INVALID EMAIL / PASSWORD. TRY AGAIN <i class="fas fa-exclamation-triangle"></i>',
						'status' => 2,
						'response'	=> $response,
					);
				}

			} else {

				$query = $connection->query("SELECT * FROM users WHERE LOWER(email) = '" .$connection->real_escape_string(strtolower($account->email)). "'");

				if($query->num_rows > 0){

					$item = $query->fetch_object();
					$password = hash('sha256', strtolower($account->email) . $item->unique_id). ':' .hash('sha1', $item->timestamp . $account->password);
					$query_2 = $connection->query("SELECT * FROM users WHERE LOWER(email) = '" .$connection->real_escape_string(strtolower($account->email)). "' AND password = '" .$connection->real_escape_string($password). "'");
					if($query_2->num_rows == 1){

						setsitecookie($item->unique_id, $item->user_origin, $connection);
						// $user = $query_2->fetch_object();
						$result = array(
							'text'		=>	'WELCOME TO SOLARCALC <i class="fas fa-check-circle"></i>',
							'status'		=>	1,
							'user_origin'	=>	$item->user_origin
						);

					} else {

						$result = array(
							'text' 		=>	'INVALID EMAIL / PASSWORD. TRY AGAIN <i class="fas fa-exclamation-triangle"></i>',
							'status' 		=>	3,
							'response'	=>	$password,
							'item'		=>	$item,
							'query'		=>	$query_2->num_rows
						);

					}
				} else {

					$result = array(
						'text'	=>	'NO SUCH USER / ACCOUNT <i class="fas fa-exclamation-triangle"></i>',
						'status'	=>	2
					);

				}
			}

			print_r(json_encode($result));

		break;

	case 'logout':

		endsitecookie($connection);

		break;

	case 'getlocations':

		$country = explode('|', $data['country_code']);
		$country_code = $country[0];
		$location_name = strtolower($data['location_name']);

		$query = $connection->query("SELECT location_id, location_name, location_code, latitude, longitude FROM world_locations WHERE LOWER(country_code) = '" .$connection->real_escape_string(strtolower($country_code)). "' AND location_name LIKE '%" .$connection->real_escape_string($location_name). "%' ORDER BY location_name ASC LIMIT 10");

		$locations = array();
		while($item = $query->fetch_object()){
			$locations[] = array(				
				'location_details'	=>	ucwords(str_replace($location_name, '<b>' .$location_name. '</b>', strtolower($item->location_name))),
				'location'			=> $item->location_id. '|' .$item->location_name. '|' .$item->location_code. '|' .$item->latitude. '|' .$item->longitude
			);
		}

		print_r(json_encode($locations));

		break;

	case 'getlocationdetails':

		$location = explode('|', $data['location_code']);
		$location_code = $location[0];
		$location_name = $location[1];
		$country_code = $location[2];

		// Check if location coordinates is empty
		$query = $connection->query("SELECT location_coordinates FROM world_locations WHERE LOWER(location_code) = '" .$connection->real_escape_string(strtolower($location_code)). "'");

		$location_details = $query->fetch_object();
		if($location_details->location_coordinates == ''){
			$geocode = getlocationcoordinates($location_name. '-' .$country_code);
			if($geocode){
				if(count($geocode['results']) > 0){
					$coordinates = (Object)$geocode['results'][0]['geometry']['location'];
					// updatelocation($coordinates, $connection, $location_code, $country_code);
					$irradiation = array(
						'status' => 1,
						'details' => getlocationirradiation($coordinates),
						'gps' => $coordinates
					);
				} else {
					$irradiation = array(
						'status' => 0,
						'text' => 'ZERO RESULTS'
					);
				}
			} else {
				$irradiation = array(
					'status' => 0,
					'text' => 'NO RETURNED RESULTS'
				);
			}
		} else {
			$coordinates_details = explode(',', $location_details->location_coordinates);
			$coordinates = (Object)array(
				'lat'	=>	$coordinates_details[0],
				'lng'	=>	$coordinates_details[1]
			);
			$irradiation = getlocationirradiation($coordinates);
		}

		print_r(json_encode($irradiation));

		break;

	case 'getpreciselocation':

		$address = getlocationdetails($data['latlng']);
		$location_name_details = array();
		$country_codes = array();
		foreach($address['results'][0]['address_components'] as $address_components){
			$location_name_details[] = $address_components['long_name'];
			$country_codes[] = $address_components['short_name'];
		}
		
		$location_name = implode(', ', $location_name_details);

		$location = explode(',', $data['latlng']);
		$coordinates = (Object)array(
			'lat' => $location[0],
			'lng' => $location[1]
		);
		if(count($address['results'][0]['address_components']) == 2){
			$location_code = array(
				'long_name'		=>	$address['results'][0]['address_components'][0]['long_name'],
				'short_name'	=>	$address['results'][0]['address_components'][0]['short_name']
			);
		} else {
			$location_code = array(
				'long_name'		=>	$address['results'][0]['address_components'][1]['long_name'],
				'short_name'	=>	$address['results'][0]['address_components'][1]['short_name']
			);			
		}
		$irradiation = array(
			'status'				=> 1,
			'details'				=> getlocationirradiation($coordinates),
			'gps'				=> $coordinates,
			'address'				=> $address,
			'location_name' 		=> $location_name,
			'country_code'			=> $country_codes[count($country_codes) - 1],
			'location_code'		=> $location_code
		);

		print_r(json_encode($irradiation));
		break;

	case 'getirradiation':

		$location = array();
		foreach ($data['location_form'] as $key => $value) {

			if($value['name'] == 'country'){
				$country = explode('|', $value['value']);
				$location['country_code'] = $country[0];
				$location['country_name'] = $country[1];
			} else {
				$location[$value['name']] = $value['value'];
			}

		}

		$location = (Object)$location;

		$coordinates = (Object)array(
			'lat'	=>	(float)$location->latitude_place,
			'lng'	=>	(float)$location->longitude_place
		);

		// Let us check if the Irradiation has been set before
		$query = $connection->query("SELECT location_id, irradiation FROM world_locations WHERE country_code = '" .$connection->real_escape_string($location->country_code). "' AND location_id = '" .(int)$location->location_id. "'");
		$stored_irradiation = $query->fetch_object();

		if($stored_irradiation->irradiation == ''){

			$nasairradiation = getlocationirradiation($coordinates);
			$dnr = $nasairradiation['features'][0]['properties']['parameter'];

			if($dnr != null){
				$storequery = $connection->query("UPDATE world_locations SET irradiation = '" .$connection->real_escape_string(serialize($dnr)). "' WHERE country_code = '" .$connection->real_escape_string($location->country_code). "' AND location_id = '" .(int)$location->location_id. "'");
				$status = (Object)array(
					'text'		=>	'Confirm',
					'status'	=>	1
				);
			} else {
				$status = (Object)array(
					'text'		=>	'Error, try again',
					'status'	=>	0
				);
			}
			
		} else {
			$dnr = unserialize($stored_irradiation->irradiation);
			$status = (Object)array(
				'text'		=>	'Confirm',
				'status'		=>	1
			);
		}

		$irradiation = array(
			'status'			=> $status,
			'details'			=> $dnr,
			'gps'			=> $coordinates,
			'location_name' 	=> $location->location_name,
			'country_code'		=> $location->country_code,
			'location_code'	=> $location->location_code,
			'location_id'		=> (int)$location->location_id,
			'country_name'		=> $location->country_name
		);

		print_r(json_encode($irradiation));

		break;

	case 'getirradiationoffline':

		$location = array();
		foreach ($data['location_form'] as $key => $value) {

			if($value['name'] == 'country'){
				$country = explode('|', $value['value']);
				$location['country_code'] = $country[0];
				$location['country_name'] = $country[1];
			} else {
				$location[$value['name']] = $value['value'];
			}

		}

		$location = (Object)$location;

		$coordinates = (Object)array(
			'lat'	=>	(float)$location->latitude_place,
			'lng'	=>	(float)$location->longitude_place
		);

		// Get the Nearest Latitudes to this point
		$irradiation_data = getofflineirradiation($coordinates, $connection);
		$dailyirratiation_data = getdailyirratiation($irradiation_data->closest, $coordinates, $connection);

		$irradiation = array(
			'status'				=>	(Object)array(
				'text'	=>	'Confirm',
				'status'	=>	1
			),
			'details'				=>	$irradiation_data->item,
			'gps'				=>	$coordinates,
			'location_name' 		=>	$location->location_name,
			'country_code'			=>	$location->country_code,
			'location_code'		=>	$location->location_code,
			'location_id'			=>	(int)$location->location_id,
			'country_name'			=>	$location->country_name,
			'irradiation_data'		=>	$irradiation_data,
			'daily_irradiation'		=>	$dailyirratiation_data
		);

		print_r(json_encode($irradiation));

		break;

	case 'getirradiationappoffline':

		$data = (object)$data;

		$coordinates = (Object)array(
			'lat'	=>	(float)$data->lat,
			'lng'	=>	(float)$data->lng
		);

		// Get the Nearest Latitudes to this point
		$irradiation_data = getofflineirradiation($coordinates, $connection);
		$dailyirratiation_data = getdailyirratiation($irradiation_data->closest, $coordinates, $connection);

		$irradiation = array(
			'status'				=>	(Object)array(
										'text'	=>	'Confirm',
										'status'	=>	1
									),
			'details'				=>	$irradiation_data->item,
			'gps'				=>	$coordinates,
			// 'location_name' 		=>	$location->location_name,
			// 'country_code'			=>	$location->country_code,
			// 'location_code'		=>	$location->location_code,
			// 'location_id'			=>	(int)$location->location_id,
			// 'country_name'			=>	$location->country_name,
			'irradiation_data'		=>	$irradiation_data,
			'daily_irradiation'		=>	$dailyirratiation_data
		);

		print_r(json_encode($irradiation));

		break;

	case 'getcoordinatedetails':

		$address = getlocationdetails($data['latlng']);
		$location_name_details = array();
		$country_codes = array();
		$formatted_address = array();

		foreach($address['results'][0]['address_components'] as $address_components){			
			$country_codes[] = $address_components['short_name'];
		}

		foreach($address['results'] as $address_details){
			$name = $address_details['address_components'][0]['long_name'];
			if(strpos(strtolower($name), 'nnamed') == false){
				$formatted_address[] = $name;
			}
		}


		$location_name = $formatted_address[0];
		if($formatted_address[0] != $formatted_address[1]){
			$location_name .= ' - ' .$formatted_address[1];
		}

		if($formatted_address[1] != $formatted_address[(count($formatted_address) - 2)]){
			$location_name .= ', ' .$formatted_address[(count($formatted_address) - 2)];
		}
		
		$location_name .= ', ' .$formatted_address[(count($formatted_address) - 1)];		

		if(count($address['results'][0]['address_components']) == 2){
			$location_code = array(
				'long_name'		=>	$address['results'][0]['address_components'][0]['long_name'],
				'short_name'		=>	$address['results'][0]['address_components'][0]['short_name']
			);
		} else {
			$location_code = array(
				'long_name'		=>	$address['results'][0]['address_components'][1]['long_name'],
				'short_name'		=>	$address['results'][0]['address_components'][1]['short_name']
			);			
		}

		$sitefullname = array();

		foreach($formatted_address as $formatted_location_address){

			if(!in_array($formatted_location_address, $sitefullname)){
				array_push($sitefullname, $formatted_location_address);
			}

		}

		$country = $address['results'][(count($address['results']) - 1)]['address_components'][0];

		$location = array(
			'status'				=> 1,
			'address'				=> $address,
			'location_name' 		=> implode(', ', $sitefullname),
			'location_name_full' 	=> $formatted_address,
			'country_code'			=> $country['short_name'],
			'country'				=> $country['long_name'],
			'location_code'		=> $location_code,
			'full_address'			=> $location_name_details,
			'results'				=> $country,
			'sitefullname'			=> $sitefullname
		);

		print_r(json_encode($location));

		break;

	case 'getcoordinatesgeodecoding':

		$location_information = array();
		foreach ($data['location_information'] as $key => $value) {

			if($value['name'] == 'country'){
				$country = explode('|', $value['value']);
				$location_information['country_code'] = $country[0];
				$location_information['country_name'] = $country[1];
			} else {
				$location_information[$value['name']] = $value['value'];
			}

		}

		$location_information = (Object)$location_information;
		$location_details = getlocationcoordinates($location_information->location_name. ',' .$location_information->country_name);

		// Check Results
		if(count($location_details['results']) > 0){

			$coordinates = (Object)$location_details['results'][0]['geometry']['location'];
			$result = (Object)array(
				'status'		=>	1,
				'lat'			=>	$coordinates->lat,
				'lng'			=>	$coordinates->lng
			);

		} else {
			$result = (Object)array(
				'status'	=>	0,
				'text'		=>	'No Such Location'
			);
		}

		print_r(json_encode($result));

		break;

	case 'setcoordinates':

		$location_information = array();
		foreach ($data['location_information'] as $key => $value) {

			if($value['name'] == 'country'){
				$country = explode('|', $value['value']);
				$location_information['country_code'] = $country[0];
				$location_information['country_name'] = $country[1];
			} else {
				$location_information[$value['name']] = $value['value'];
			}

		}

		$location_information = (Object)$location_information;

		if($location_information->location_id == ''){

			$query = $connection->query("INSERT INTO world_locations SET latitude = '" .$connection->real_escape_string($location_information->latitude_place). "', longitude = '" .$connection->real_escape_string($location_information->longitude_place). "', country_code = '" .$connection->real_escape_string($location_information->country_code). "', location_name = '" .$connection->real_escape_string($location_information->location_name). "'");

			$result = (Object)array(
				'status'					=>	1,
				'location_id'				=>	$connection->insert_id,
				'latitude'					=>	$location_information->latitude_place,
				'longitude'					=>	$location_information->longitude_place,
				'location_information'		=>	$location_information
			);

		} else {

			$query = $connection->query("UPDATE world_locations SET latitude = '" .$connection->real_escape_string($location_information->latitude_place). "', longitude = '" .$connection->real_escape_string($location_information->longitude_place). "' WHERE location_id = " .(int)$location_information->location_id. " AND country_code = '" .$connection->real_escape_string($location_information->country_code). "' AND location_code = '" .$connection->real_escape_string($location_information->location_code). "'");

			$result = (Object)array(
				'status'						=>	2,
				'latitude'					=>	$location_information->latitude_place,
				'longitude'					=>	$location_information->longitude_place,
				'location_information'			=>	$location_information
			);

		}

		print_r(json_encode($result));

		break;

	case 'getappirradiation':

		$data = (Object)$data;
		// $irradiation = getlocationirradiation($data);
		// $dnr = $irradiation['features'][0]['properties']['parameter']['DNR'];

		$dnr = array();
		$dnr['1'] = 6.31;
		$dnr['2'] = 6.76;
		$dnr['3'] = 6.32;
		$dnr['4'] = 5.75;
		$dnr['5'] = 5.94;
		$dnr['6'] = 6.06;
		$dnr['7'] = 5.77;
		$dnr['8'] = 5.8;
		$dnr['9'] = 6.16;
		$dnr['10'] = 5.74;
		$dnr['11'] = 5.25;
		$dnr['12'] = 5.94;
		$dnr['13'] = 5.98;

		$months = array(
			array(
				'text'		=>	'January',
				'irradiation'	=>	$dnr['1']
			),
			array(
				'text'		=>	'February',
				'irradiation'	=>	$dnr['2']
			),
			array(
				'text'		=>	'March',
				'irradiation'	=>	$dnr['3']
			),
			array(
				'text'		=>	'April',
				'irradiation'	=>	$dnr['4']
			),
			array(
				'text'		=>	'May',
				'irradiation'	=>	$dnr['5']
			),
			array(
				'text'		=>	'June',
				'irradiation'	=>	$dnr['6']
			),
			array(
				'text'		=>	'July',
				'irradiation'	=>	$dnr['7']
			),
			array(
				'text'		=>	'August',
				'irradiation'	=>	$dnr['8']
			),
			array(
				'text'		=>	'September',
				'irradiation'	=>	$dnr['9']
			),
			array(
				'text'		=>	'October',
				'irradiation'	=>	$dnr['10']
			),
			array(
				'text'		=>	'November',
				'irradiation'	=>	$dnr['11']
			),
			array(
				'text'		=>	'December',
				'irradiation'	=>	$dnr['12']
			),
			array(
				'text'		=>	'Average',
				'irradiation'	=>	$dnr['13']
			)
		);

		$details = (Object)array(
			'irradiation_string'	=>	$dnr['1']. '|' .$dnr['2']. '|' .$dnr['3']. '|' .$dnr['4']. '|' .$dnr['5']. '|' .$dnr['6']. '|' .$dnr['7']. '|' .$dnr['8']. '|' .$dnr['9']. '|' .$dnr['10']. '|' .$dnr['11']. '|' .$dnr['12']. '|' .$dnr['13'],
			'months'				=>	$months
		);

		print_r(json_encode($details));

		break;

	case 'solarization':

		$data = (Object)$data;
		// Get Inverter and the Panels

		// Save Sizing Details
		$params = (Object)array(
			'post_data' => $data,
			'get_data' => $_GET
		);
		$params = serialize($params);
		saveparameters($params, 'solarization', $connection);

		$count = 1;
		$custom_modules = array();
		$inverter_count = 0;

		if(isset($data->custom_module)){
			$custom_module_panels = explode('|', $data->custom_module_panels);
			for($j = 0; $j < count($custom_module_panels); $j++){
				array_push($custom_modules, $custom_module_panels[$j]);
			}
		}

		$inverter = getinverter($connection, (float)$data->pump_motor, $data->hybrid, $data->phase);

		if($inverter != false){

			$panels = getpanels($inverter->min_mpp_voltage, $inverter->max_dc_input_voltage, (float)$data->pump_motor * 1000, $custom_modules, $connection, $data->min_panel_uplift, $data->max_panel_uplift);

			$equipment = array(
				'equipment_count'					=>	1,
				'equipment_id'						=>	'',
				'equipment_model'					=>	$data->pump_model,
				'max_flow_rate'						=>	'',
				'min_flow_rate'						=>	'',
				'max_head'							=>	'',
				'min_head'							=>	'',
				'motor_kw'							=>	(float)$data->pump_motor,
				'motor_hp'							=>	(float)$data->pump_motor * 1000 * 0.00134102,
				'fullload_current_single_phase'		=>	'',
				'fullload_current_three_phase'		=>	'',
				'pipe_outlet'						=>	'',
				'inverter_details'					=>	$inverter,
				'inverter_model'					=>	$inverter->inverter_model,
				'rated_voltage'					=>	$inverter->rated_voltage,
				'output_current'					=>	$inverter->output_current,
				'product_id'						=>	'',
				'inverter_id'						=>	(int)$inverter->product_id,
				'pv_generator'						=>	'PV Generator',
				'panels'							=>	$panels,
				'cable'								=>	'',
				'curve_head'						=>	'',
				'efficiency'						=>	''
			);

		} else {

			$equipment = array(
				'equipment_count'					=>	1,
				'equipment_id'						=>	'',
				'equipment_model'					=>	$data->pump_model,
				'max_flow_rate'						=>	'',
				'min_flow_rate'						=>	'',
				'max_head'							=>	'',
				'min_head'							=>	'',
				'motor_kw'							=>	(float)$data->pump_motor,
				'motor_hp'							=>	(float)$data->pump_motor * 1000 * 0.00134102,
				'fullload_current_single_phase'		=>	'',
				'fullload_current_three_phase'		=>	'',
				'pipe_outlet'						=>	'',
				'inverter_details'					=>	'None',
				'inverter_model'					=>	'',
				'rated_voltage'						=>	'',
				'output_current'					=>	'',
				'product_id'						=>	'',
				'inverter_id'						=>	'',
				'pv_generator'						=>	'PV Generator',
				'panels'							=>	'',
				'cable'								=>	'',
				'curve_head'						=>	'',
				'efficiency'						=>	''
			);

		}

		// Check Panels
		if($equipment['inverter_id'] != false){

			$pv_generator = array();
			$strings_content = '';
			$strings_array = array();
			$string_options = array();

			foreach($equipment['panels'] as $panel){
				if(count($panel->strings) > 0){					
					foreach($panel->strings as $string_details){
						$strings_array[] = array(
							'total_power'					=>	$string_details->total_power,
							'number_of_panels_per_string'		=>	$string_details->number_of_panels_per_string,
							'strings'						=>	$string_details->strings,
							'panel_model'					=>	$string_details->panel_model,
							'panel_rated_power_w'			=>	$panel->panel_rated_power_w,
							'nominal_voltage'				=>	$panel->nominal_voltage,
							'panel_id'					=>	$panel->panel_id,
							'array_motor_fraction'			=>	$string_details->array_motor_fraction,
							'short_circuit_current'			=>	$panel->short_circuit_current,
							'open_circuit_voltage'			=>	$panel->open_circuit_voltage,
							'peak_voltage'					=>	(float)$panel->peak_voltage,
							'max_voltage'					=>	(float)$panel->peak_voltage * $string_details->number_of_panels_per_string
						);
					}
				}
			}

			// Group the Panels based on the Model
			$panel_models = array_unique(array_column($strings_array, 'panel_model'));
			$panel_models_grouped = array();
			$panels_top = array();

			foreach($strings_array as $key => $value){

				$panel_models_grouped[$value['panel_model']][] = array(

					'total_power'					=>	$value['total_power'],
					'number_of_panels_per_string'		=>	$value['number_of_panels_per_string'],
					'strings'						=>	$value['strings'],
					'panel_rated_power_w'			=>	$value['panel_rated_power_w'],
					'nominal_voltage'				=>	$value['nominal_voltage'],
					'panel_id'					=>	$value['panel_id'],
					'array_motor_fraction'			=>	$value['array_motor_fraction'],
					'short_circuit_current'			=>	$value['short_circuit_current'],
					'open_circuit_voltage'			=>	$value['open_circuit_voltage'],
					'peak_voltage'					=>	$value['peak_voltage'],
					'max_voltage'					=>	$value['max_voltage'],
					'panel_model'					=>	$value['panel_model']

				);
				
			}

			foreach($panel_models_grouped as $key => $value){
				$panels_top[] = $panel_models_grouped[$key][(count($panel_models_grouped[$key]) - 1)];
			}			
			
			$motor_fraction = array_column($panels_top, 'panel_rated_power_w');
			$top_strings = array_multisort($motor_fraction, SORT_DESC, $panels_top);
			$top_choices = array_slice($panels_top, 0, 3);

			for($g = 0; $g < count($top_choices); $g++){

				$string_options[] = (Object)array(
					'total_power'						=>	number_format($top_choices[$g]['total_power'] / 1000, 1),
					'number_of_panels_per_string'			=>	$top_choices[$g]['number_of_panels_per_string'],
					'strings'							=>	$top_choices[$g]['strings'],
					'panel_model'						=>	$top_choices[$g]['panel_model'],
					'panel_rated_power_w'				=>	$top_choices[$g]['panel_rated_power_w'],
					'nominal_voltage'					=>	$top_choices[$g]['nominal_voltage'],
					'panel_id'						=>	$top_choices[$g]['panel_id'],
					'array_motor_fraction'				=>	$top_choices[$g]['array_motor_fraction'],
					'short_circuit_current'				=>	$top_choices[$g]['short_circuit_current'],
					'open_circuit_voltage'				=>	$top_choices[$g]['open_circuit_voltage'],
					'max_voltage'						=>	round($top_choices[$g]['max_voltage'])
				);

			}

			if(count($string_options) > 0){
				$list = array(
					'equipment_count'					=>		$equipment['equipment_count'],
					'equipment_id'						=>		$equipment['equipment_id'],
					'equipment_model'					=>		$equipment['equipment_model'],
					'pump_id'							=>		$equipment['product_id'],
					'max_flow_rate'					=>		$equipment['max_flow_rate'],
					'min_flow_rate'					=>		$equipment['min_flow_rate'],
					'max_head'						=>		$equipment['max_head'],
					'min_head'						=>		$equipment['min_head'],
					'motor_kw'						=>		$equipment['motor_kw'],
					'motor_hp'						=>		$equipment['motor_hp'],
					'fullload_current_single_phase'		=>		$equipment['fullload_current_single_phase'],
					'fullload_current_three_phase'		=>		$equipment['fullload_current_three_phase'],
					'pipe_outlet'						=>		$equipment['pipe_outlet'],
					'product_id'						=>		$equipment['product_id'],
					'inverter_details'					=>		$equipment['inverter_details'],
					'inverter'						=>		1,
					'inverter_id'						=>		$equipment['inverter_id'],
					'inverter_model_name'				=>		$equipment['inverter_model'],
					'inverter_model'					=>		$equipment['inverter_model']. ' - ' .$equipment['rated_voltage']. ' ' .$equipment['output_current']. 'A',
					'panels'							=>		$equipment['panels'],
					'pv_generator'						=>		$strings_content,
					'strings'							=>		$strings_array,
					'string_options'					=>		$string_options,
					'string_options_count'				=>		count($string_options),
					'motor_fraction'					=>		$motor_fraction,
					'panel_models'						=>		$panel_models,
					'panel_models_grouped'				=>		$panel_models_grouped,
					'panels_top'						=>		$panels_top,
					'top_choices'						=>		$top_choices,
					'sizing'							=>		$custom_modules,
					'cable'							=>		$equipment['cable'],
					'curve_head'						=>		$equipment['curve_head'],
					'efficiency'						=>		$equipment['efficiency'],
					'data'							=>		$data
				);
			}

		} else {

			$list = array(
					'equipment_count'					=>		$equipment['equipment_count'],
					'equipment_id'						=>		$equipment['equipment_id'],
					'equipment_model'					=>		$equipment['equipment_model'],
					'pump_id'							=>		$equipment['product_id'],
					'max_flow_rate'					=>		$equipment['max_flow_rate'],
					'min_flow_rate'					=>		$equipment['min_flow_rate'],
					'max_head'						=>		$equipment['max_head'],
					'min_head'						=>		$equipment['min_head'],
					'motor_kw'						=>		$equipment['motor_kw'],
					'motor_hp'						=>		$equipment['motor_hp'],
					'fullload_current_single_phase'		=>		$equipment['fullload_current_single_phase'],
					'fullload_current_three_phase'		=>		$equipment['fullload_current_three_phase'],
					'pipe_outlet'						=>		$equipment['pipe_outlet'],
					'product_id'						=>		$equipment['product_id'],
					'inverter_details'					=>		'',
					'inverter'						=>		0,
					'inverter_id'						=>		'',
					'inverter_model_name'				=>		'',
					'inverter_model'					=>		'',
					'panels'							=>		'',
					'pv_generator'						=>		'',
					'strings'							=>		'',
					'string_options'					=>		'',
					'string_options_count'				=>		'',
					'motor_fraction'					=>		'',
					'top_choices'						=>		'',
					'sizing'							=>		'',
					'cable'							=>		$equipment['cable'],
					'curve_head'						=>		$equipment['curve_head'],
					'efficiency'						=>		$equipment['efficiency'],
					'data'							=>		$data
				);
		}

		print_r(json_encode($list));

		break;

	// Sizing for AC Pumps
	case 'sizing':

		$tdh = $_GET['tdh'];
		$flow_rate = $_GET['flow_rate'];
		$cable_length = $_GET['cable'];

		$inverter_status = isset($_GET['inverter']) ? true : false;

		// Save Sizing Details
		$params = (Object)array(
			'post_data' => $data,
			'get_data' => $_GET
		);
		$params = serialize($params);

		// saveparameters($params, 'ac', $connection);

		// Run SQL Request
		if($data['phase'] == '1'){
			$query = $connection->query("SELECT * FROM equipment WHERE min_flow_rate <= " .(float)$flow_rate. " AND max_flow_rate >= " .(float)$flow_rate. " AND min_head <= " .$tdh. " AND max_head >= " .$tdh. " AND fullload_current_single_phase != ''");
		} elseif($data['phase'] == '3'){
			$query = $connection->query("SELECT * FROM equipment WHERE min_flow_rate <= " .(float)$flow_rate. " AND max_flow_rate >= " .(float)$flow_rate. " AND min_head <= " .$tdh. " AND max_head >= " .$tdh. " AND fullload_current_three_phase != ''");
		}		

		$equipment = array();
		$count = 1;
		$custom_modules = array();

		if(isset($data['custom_module'])){

			$custom_module_panels = explode('|', $data['custom_module_panels']);
			for($j = 0; $j < count($custom_module_panels); $j++){
				array_push($custom_modules, $custom_module_panels[$j]);
			}

		}

		$inverter_count = 0;

		while($item = $query->fetch_object()){

			// Get Inverter
			$inverter = getinverter($connection, $item->motor_kw, $data['hybrid'], $data['phase'], $inverter_status);
			$cable = getcable($connection, $item->motor_kw, $cable_length, $data['phase']);

			if($inverter != false) {


				// Get Panels
				if((int)$data['hybrid'] == 1){

					$panels = getpanels($inverter->min_mpp_voltage_hybrid, $inverter->max_dc_input_voltage, (float)$item->motor_kw * 1000, $custom_modules, $connection, $data['min_panel_uplift'], $data['max_panel_uplift']);

				} else {

					$panels = getpanels($inverter->min_mpp_voltage, $inverter->max_dc_input_voltage, (float)$item->motor_kw * 1000, $custom_modules, $connection, $data['min_panel_uplift'], $data['max_panel_uplift']);
				}

				// $panels = getpanels((int)$data['hybrid'] == 1 ? $inverter->min_mpp_voltage_hybrid : $inverter->min_mpp_voltage, $inverter->max_dc_input_voltage, (float)$item->motor_kw * 1000, $custom_modules, $connection, $data['min_panel_uplift'], $data['max_panel_uplift']);


				$equipment[] = array(
					'equipment_count'						=>	$count,
					'equipment_id'							=>	$item->equipment_id,
					'equipment_model'						=>	$item->equipment_model,
					'max_flow_rate'						=>	$item->max_flow_rate,
					'min_flow_rate'						=>	$item->min_flow_rate,
					'max_head'							=>	$item->max_head,
					'min_head'							=>	$item->min_head,
					'motor_kw'							=>	$item->motor_kw,
					'motor_hp'							=>	$item->motor_hp,
					'fullload_current_single_phase'			=>	$item->fullload_current_single_phase,
					'fullload_current_three_phase'			=>	$item->fullload_current_three_phase,
					'pipe_outlet'							=>	makefraction($item->pipe_outlet),
					'inverter_details'						=>	$inverter,
					'inverter_model'						=>	$inverter->inverter_model,
					'rated_voltage'						=>	$inverter->rated_voltage,
					'output_current'						=>	$inverter->output_current,
					'product_id'							=>	$item->product_id,
					'inverter_id'							=>	$inverter->product_id,
					'pv_generator'							=>	'PV Generator',
					'panels'								=>	$panels,
					'cable'								=>	$cable,
					'curve_head'							=>	getproperhead($item->curve, $flow_rate, $tdh),
					'efficiency'							=>	getefficiency($item->efficiency, $flow_rate)
				);
				$count++;
				$inverter_count++;

			} else {

				$equipment[] = array(
					'equipment_count'						=>	$count,
					'equipment_id'							=>	$item->equipment_id,
					'equipment_model'						=>	$item->equipment_model,
					'max_flow_rate'						=>	$item->max_flow_rate,
					'min_flow_rate'						=>	$item->min_flow_rate,
					'max_head'							=>	$item->max_head,
					'min_head'							=>	$item->min_head,
					'motor_kw'							=>	$item->motor_kw,
					'motor_hp'							=>	$item->motor_hp,
					'fullload_current_single_phase'			=>	$item->fullload_current_single_phase,
					'fullload_current_three_phase'			=>	$item->fullload_current_three_phase,
					'pipe_outlet'							=>	makefraction($item->pipe_outlet),
					'inverter_details'						=>	'',
					'inverter_model'						=>	'',
					'rated_voltage'						=>	'',
					'output_current'						=>	'',
					'product_id'							=>	$item->product_id,
					'inverter_option'						=>	$inverter,
					'inverter_id'							=>	false,
					'pv_generator'							=>	'PV Generator',
					'panels'								=>	'',
					'cable'								=>	$cable,
					'curve_head'							=>	getproperhead($item->curve, $flow_rate, $tdh),
					'efficiency'							=>	getefficiency($item->efficiency, $flow_rate)
				);

				$count++;

			}
		}

		$list = array();

		foreach($equipment as $item){

			if($item['curve_head']->status){

				if($item['inverter_id'] != false){
					$pv_generator = array();
					$strings_content = '';
					$strings_array = array();
					$string_options = array();
					foreach($item['panels'] as $panel){
						if(count($panel->strings) > 0){					
							foreach($panel->strings as $string_details){
								$strings_array[] = array(
									'total_power'					=>	$string_details->total_power,
									'number_of_panels_per_string'		=>	$string_details->number_of_panels_per_string,
									'strings'						=>	$string_details->strings,
									'panel_model'					=>	$string_details->panel_model,
									'panel_rated_power_w'			=>	$panel->panel_rated_power_w,
									'nominal_voltage'				=>	$panel->nominal_voltage,
									'panel_id'					=>	$panel->panel_id,
									'array_motor_fraction'			=>	$string_details->array_motor_fraction,
									'short_circuit_current'			=>	$panel->short_circuit_current,
									'open_circuit_voltage'			=>	$panel->open_circuit_voltage,
									'peak_voltage'					=>	(float)$panel->peak_voltage,
									'max_voltage'					=>	(float)$panel->peak_voltage * $string_details->number_of_panels_per_string
								);
							}
						}
					}

					// Group the Panels based on the Model
					$panel_models = array_unique(array_column($strings_array, 'panel_model'));
					$panel_models_grouped = array();
					$panels_top = array();

					foreach($strings_array as $key => $value){

						$panel_models_grouped[$value['panel_model']][] = array(

							'total_power'					=>	$value['total_power'],
							'number_of_panels_per_string'		=>	$value['number_of_panels_per_string'],
							'strings'						=>	$value['strings'],
							'panel_rated_power_w'			=>	$value['panel_rated_power_w'],
							'nominal_voltage'				=>	$value['nominal_voltage'],
							'panel_id'					=>	$value['panel_id'],
							'array_motor_fraction'			=>	$value['array_motor_fraction'],
							'short_circuit_current'			=>	$value['short_circuit_current'],
							'open_circuit_voltage'			=>	$value['open_circuit_voltage'],
							'peak_voltage'					=>	$value['peak_voltage'],
							'max_voltage'					=>	$value['max_voltage'],
							'panel_model'					=>	$value['panel_model']

						);
						
					}

					foreach($panel_models_grouped as $key => $value){
						$panels_top[] = $panel_models_grouped[$key][(count($panel_models_grouped[$key]) - 1)];
					}
					
					$motor_fraction = array_column($panels_top, 'panel_rated_power_w');
					$top_strings = array_multisort($motor_fraction, SORT_DESC, $panels_top);
					$top_choices = array_slice($panels_top, 0, 3);

					for($g = 0; $g < count($top_choices); $g++){

						$string_options[] = (Object)array(
							'total_power'						=>	number_format($top_choices[$g]['total_power'] / 1000, 1),
							'number_of_panels_per_string'			=>	$top_choices[$g]['number_of_panels_per_string'],
							'strings'							=>	$top_choices[$g]['strings'],
							'panel_model'						=>	$top_choices[$g]['panel_model'],
							'panel_rated_power_w'				=>	$top_choices[$g]['panel_rated_power_w'],
							'nominal_voltage'					=>	$top_choices[$g]['nominal_voltage'],
							'panel_id'						=>	$top_choices[$g]['panel_id'],
							'array_motor_fraction'				=>	$top_choices[$g]['array_motor_fraction'],
							'short_circuit_current'				=>	$top_choices[$g]['short_circuit_current'],
							'open_circuit_voltage'				=>	$top_choices[$g]['open_circuit_voltage'],
							'peak_voltage'						=>	$top_choices[$g]['peak_voltage'],
							'total_peak_voltage'				=>	round($top_choices[$g]['max_voltage'])
						);

					}

					if(count($string_options) > 0){

						$system_details = getcurvedetails($item['equipment_id'], $tdh, $flow_rate, $connection);
						$list[] = array(
							'equipment_count'					=>		$item['equipment_count'],
							'equipment_id'						=>		$item['equipment_id'],
							'equipment_model'					=>		$item['equipment_model'],
							'pump_id'							=>		$item['product_id'],
							'max_flow_rate'					=>		$item['max_flow_rate'],
							'min_flow_rate'					=>		$item['min_flow_rate'],
							'max_head'						=>		$item['max_head'],
							'min_head'						=>		$item['min_head'],
							'motor_kw'						=>		$item['motor_kw'],
							'motor_hp'						=>		$item['motor_hp'],
							'fullload_current_single_phase'		=>		$item['fullload_current_single_phase'],
							'fullload_current_three_phase'		=>		$item['fullload_current_three_phase'],
							'pipe_outlet'						=>		$item['pipe_outlet'],
							'product_id'						=>		$item['product_id'],
							'inverter_details'					=>		$item['inverter_details'],
							'inverter'						=>		1,
							'inverter_id'						=>		$item['inverter_id'],
							'inverter_model_name'				=>		$item['inverter_model'],
							'inverter_model'					=>		$item['inverter_model']. ' - ' .$item['rated_voltage']. ' ' .$item['output_current']. 'A',
							'panels'							=>		$item['panels'],
							'pv_generator'						=>		$strings_content,
							'strings'							=>		$strings_array,
							'string_options'					=>		$string_options,
							'string_options_count'				=>		count($string_options),
							'motor_fraction'					=>		$motor_fraction,
							'panel_models'						=>		$panel_models,
							'panel_models_grouped'				=>		$panel_models_grouped,
							'panels_top'						=>		$panels_top,
							'top_choices'						=>		$top_choices,
							'sizing'							=>		$custom_modules,
							'cable'							=>		$item['cable'],
							'curve_head'						=>		$item['curve_head'],
							'efficiency'						=>		round($system_details->efficiency, 2),
							'efficiency_sort'					=>		$system_details->efficiency,
							'pump_qh_fit'						=>		$system_details->efficiency * $item['curve_head']->appropriate,
							'data'							=>		$data,
							'tdh'							=>		$tdh,
							'flow_rate'						=>		$flow_rate,
							'system_head'						=>		getsystemhead($tdh, $flow_rate),
							'system_details'					=>		$system_details
							// 'get_deta'						=>		$_GET
						);
					}
				} else {

					$system_details = getcurvedetails($item['equipment_id'], $tdh, $flow_rate, $connection);
					$list[] = array(
							'equipment_count'					=>		$item['equipment_count'],
							'equipment_id'						=>		$item['equipment_id'],
							'equipment_model'					=>		$item['equipment_model'],
							'pump_id'							=>		$item['product_id'],
							'max_flow_rate'					=>		$item['max_flow_rate'],
							'min_flow_rate'					=>		$item['min_flow_rate'],
							'max_head'						=>		$item['max_head'],
							'min_head'						=>		$item['min_head'],
							'motor_kw'						=>		$item['motor_kw'],
							'motor_hp'						=>		$item['motor_hp'],
							'fullload_current_single_phase'		=>		$item['fullload_current_single_phase'],
							'fullload_current_three_phase'		=>		$item['fullload_current_three_phase'],
							'pipe_outlet'						=>		$item['pipe_outlet'],
							'product_id'						=>		$item['product_id'],
							'inverter_details'					=>		'',
							'inverter'							=>		0,
							'inverter_id'						=>		'',
							'inverter_model_name'				=>		'',
							'inverter_model'					=>		'',
							'panels'							=>		'',
							'pv_generator'						=>		'',
							'strings'							=>		'',
							'string_options'					=>		'',
							'string_options_count'				=>		'',
							'motor_fraction'					=>		'',
							'panel_models'						=>		'',
							'top_choices'						=>		'',
							'sizing'							=>		'',
							'cable'							=>		$item['cable'],
							'curve_head'						=>		$item['curve_head'],
							'efficiency'						=>		round($system_details->efficiency, 2),
							'efficiency_sort'					=>		$system_details->efficiency,
							'pump_qh_fit'						=>		$system_details->efficiency * $item['curve_head']->appropriate,
							'data'							=>		$data,
							'tdh'							=>		$tdh,
							'flow_rate'						=>		$flow_rate,
							'system_details'					=>		$system_details
							// 'get_deta'						=>		$_GET
						);
				}

			}

		}

		// $efficiency_sort = array_column($list, 'efficiency_sort');
		// array_multisort($efficiency_sort, SORT_DESC, $list);

		$pump_qh_fit_sort = array_column($list, 'pump_qh_fit');
		array_multisort($pump_qh_fit_sort, SORT_DESC, $list);

		print_r(json_encode($list));

		break;

	// Sizing for Surface Pumps
	case 'surface':

		$tdh = $_GET['tdh'];
		$flow_rate = $_GET['flow_rate'];
		$cable_length = $_GET['cable'];

		$inverter_status = ISSET($_GET['inverter']) ? true : false;

		// Save Sizing Details
		$params = (Object)array(
			'post_data' => $data,
			'get_data' => $_GET
		);
		$params = serialize($params);
		saveparameters($params, 'ac', $connection);

		// Run SQL Request
		if($data['phase'] == '1'){
			$query = $connection->query("SELECT * FROM equipment WHERE min_flow_rate <= " .(float)$flow_rate. " AND max_flow_rate >= " .(float)$flow_rate. " AND min_head <= " .$tdh. " AND max_head >= " .$tdh. " AND category = '" .$data['pump_category']. "' AND fullload_current_single_phase != ''");
		} elseif($data['phase'] == '3'){
			$query = $connection->query("SELECT * FROM equipment WHERE min_flow_rate <= " .(float)$flow_rate. " AND max_flow_rate >= " .(float)$flow_rate. " AND min_head <= " .$tdh. " AND max_head >= " .$tdh. " AND category = '" .$data['pump_category']. "' AND fullload_current_three_phase != ''");
		}		

		$equipment = array();
		$count = 1;
		$custom_modules = array();

		if(isset($data['custom_module'])){

			$custom_module_panels = explode('|', $data['custom_module_panels']);
			for($j = 0; $j < count($custom_module_panels); $j++){
				array_push($custom_modules, $custom_module_panels[$j]);
			}

		}

		$inverter_count = 0;

		$itemssss = array();

		while($item = $query->fetch_object()){

			// Get Inverter
			$inverter = getinverter($connection, $item->motor_kw, $data['hybrid'], $data['phase'], $inverter_status);
			$cable = getcable($connection, $item->motor_kw, $cable_length, $data['phase']);

			if($inverter != false) {
				// Get Panels
				$panels = getpanels($inverter->min_mpp_voltage, $inverter->max_dc_input_voltage, (float)$item->motor_kw * 1000, $custom_modules, $connection, $data['min_panel_uplift'], $data['max_panel_uplift']);
				$equipment[] = array(
					'equipment_count'						=>	$count,
					'equipment_id'							=>	$item->equipment_id,
					'equipment_model'						=>	$item->equipment_model,
					'max_flow_rate'						=>	$item->max_flow_rate,
					'min_flow_rate'						=>	$item->min_flow_rate,
					'max_head'							=>	$item->max_head,
					'min_head'							=>	$item->min_head,
					'motor_kw'							=>	$item->motor_kw,
					'motor_hp'							=>	$item->motor_hp,
					'fullload_current_single_phase'			=>	$item->fullload_current_single_phase,
					'fullload_current_three_phase'			=>	$item->fullload_current_three_phase,
					'pipe_outlet'							=>	makefraction($item->pipe_outlet),
					'inverter_details'						=>	$inverter,
					'inverter_model'						=>	$inverter->inverter_model,
					'rated_voltage'						=>	$inverter->rated_voltage,
					'output_current'						=>	$inverter->output_current,
					'product_id'							=>	$item->product_id,
					'inverter_id'							=>	$inverter->product_id,
					'pv_generator'							=>	'PV Generator',
					'panels'								=>	$panels,
					'cable'								=>	$cable,
					'curve_head'							=>	getproperhead($item->curve, $flow_rate, $tdh),
					'efficiency'							=>	getefficiency($item->efficiency, $flow_rate)
				);
				$count++;
				$inverter_count++;

			} else {

				$equipment[] = array(
					'equipment_count'						=>	$count,
					'equipment_id'							=>	$item->equipment_id,
					'equipment_model'						=>	$item->equipment_model,
					'max_flow_rate'						=>	$item->max_flow_rate,
					'min_flow_rate'						=>	$item->min_flow_rate,
					'max_head'							=>	$item->max_head,
					'min_head'							=>	$item->min_head,
					'motor_kw'							=>	$item->motor_kw,
					'motor_hp'							=>	$item->motor_hp,
					'fullload_current_single_phase'			=>	$item->fullload_current_single_phase,
					'fullload_current_three_phase'			=>	$item->fullload_current_three_phase,
					'pipe_outlet'							=>	makefraction($item->pipe_outlet),
					'inverter_details'						=>	'',
					'inverter_model'						=>	'',
					'rated_voltage'						=>	'',
					'output_current'						=>	'',
					'product_id'							=>	$item->product_id,
					'inverter_option'						=>	$inverter,
					'inverter_id'							=>	false,
					'pv_generator'							=>	'PV Generator',
					'panels'								=>	'',
					'cable'								=>	$cable,
					'curve_head'							=>	getproperhead($item->curve, $flow_rate, $tdh),
					'efficiency'							=>	getefficiency($item->efficiency, $flow_rate)
				);

				$count++;

			}
		}

		$list = array();

		foreach($equipment as $item){

			if($item['curve_head']->status){

				if($item['inverter_id'] != false){
					$pv_generator = array();
					$strings_content = '';
					$strings_array = array();
					$string_options = array();
					foreach($item['panels'] as $panel){
						if(count($panel->strings) > 0){					
							foreach($panel->strings as $string_details){
								$strings_array[] = array(
									'total_power'					=>	$string_details->total_power,
									'number_of_panels_per_string'		=>	$string_details->number_of_panels_per_string,
									'strings'						=>	$string_details->strings,
									'panel_model'					=>	$string_details->panel_model,
									'panel_rated_power_w'			=>	$panel->panel_rated_power_w,
									'nominal_voltage'				=>	$panel->nominal_voltage,
									'panel_id'					=>	$panel->panel_id,
									'array_motor_fraction'			=>	$string_details->array_motor_fraction,
									'short_circuit_current'			=>	$panel->short_circuit_current,
									'open_circuit_voltage'			=>	$panel->open_circuit_voltage,
									'peak_voltage'					=>	(float)$panel->peak_voltage,
									'max_voltage'					=>	(float)$panel->peak_voltage * $string_details->number_of_panels_per_string
								);
							}
						}
					}

					// Group the Panels based on the Model
					$panel_models = array_unique(array_column($strings_array, 'panel_model'));
					$panel_models_grouped = array();
					$panels_top = array();

					foreach($strings_array as $key => $value){

						$panel_models_grouped[$value['panel_model']][] = array(

							'total_power'					=>	$value['total_power'],
							'number_of_panels_per_string'		=>	$value['number_of_panels_per_string'],
							'strings'						=>	$value['strings'],
							'panel_rated_power_w'			=>	$value['panel_rated_power_w'],
							'nominal_voltage'				=>	$value['nominal_voltage'],
							'panel_id'					=>	$value['panel_id'],
							'array_motor_fraction'			=>	$value['array_motor_fraction'],
							'short_circuit_current'			=>	$value['short_circuit_current'],
							'open_circuit_voltage'			=>	$value['open_circuit_voltage'],
							'peak_voltage'					=>	$value['peak_voltage'],
							'max_voltage'					=>	$value['max_voltage'],
							'panel_model'					=>	$value['panel_model']

						);
						
					}

					foreach($panel_models_grouped as $key => $value){
						$panels_top[] = $panel_models_grouped[$key][(count($panel_models_grouped[$key]) - 1)];
					}
					
					$motor_fraction = array_column($panels_top, 'panel_rated_power_w');
					$top_strings = array_multisort($motor_fraction, SORT_DESC, $panels_top);
					$top_choices = array_slice($panels_top, 0, 3);

					for($g = 0; $g < count($top_choices); $g++){

						$string_options[] = (Object)array(
							'total_power'						=>	number_format($top_choices[$g]['total_power'] / 1000, 1),
							'number_of_panels_per_string'			=>	$top_choices[$g]['number_of_panels_per_string'],
							'strings'							=>	$top_choices[$g]['strings'],
							'panel_model'						=>	$top_choices[$g]['panel_model'],
							'panel_rated_power_w'				=>	$top_choices[$g]['panel_rated_power_w'],
							'nominal_voltage'					=>	$top_choices[$g]['nominal_voltage'],
							'panel_id'						=>	$top_choices[$g]['panel_id'],
							'array_motor_fraction'				=>	$top_choices[$g]['array_motor_fraction'],
							'short_circuit_current'				=>	$top_choices[$g]['short_circuit_current'],
							'open_circuit_voltage'				=>	$top_choices[$g]['open_circuit_voltage'],
							'peak_voltage'						=>	$top_choices[$g]['peak_voltage'],
							'total_peak_voltage'				=>	round($top_choices[$g]['max_voltage'])
						);

					}

					if(count($string_options) > 0){

						$system_details = getcurvedetails($item['equipment_id'], $tdh, $flow_rate, $connection);
						$list[] = array(
							'equipment_count'					=>		$item['equipment_count'],
							'equipment_id'						=>		$item['equipment_id'],
							'equipment_model'					=>		$item['equipment_model'],
							'pump_id'							=>		$item['product_id'],
							'max_flow_rate'					=>		$item['max_flow_rate'],
							'min_flow_rate'					=>		$item['min_flow_rate'],
							'max_head'						=>		$item['max_head'],
							'min_head'						=>		$item['min_head'],
							'motor_kw'						=>		$item['motor_kw'],
							'motor_hp'						=>		$item['motor_hp'],
							'fullload_current_single_phase'		=>		$item['fullload_current_single_phase'],
							'fullload_current_three_phase'		=>		$item['fullload_current_three_phase'],
							'pipe_outlet'						=>		$item['pipe_outlet'],
							'product_id'						=>		$item['product_id'],
							'inverter_details'					=>		$item['inverter_details'],
							'inverter'						=>		1,
							'inverter_id'						=>		$item['inverter_id'],
							'inverter_model_name'				=>		$item['inverter_model'],
							'inverter_model'					=>		$item['inverter_model']. ' - ' .$item['rated_voltage']. ' ' .$item['output_current']. 'A',
							'panels'							=>		$item['panels'],
							'pv_generator'						=>		$strings_content,
							'strings'							=>		$strings_array,
							'string_options'					=>		$string_options,
							'string_options_count'				=>		count($string_options),
							'motor_fraction'					=>		$motor_fraction,
							'panel_models'						=>		$panel_models,
							'panel_models_grouped'				=>		$panel_models_grouped,
							'panels_top'						=>		$panels_top,
							'top_choices'						=>		$top_choices,
							'sizing'							=>		$custom_modules,
							'cable'							=>		$item['cable'],
							'curve_head'						=>		$item['curve_head'],
							'efficiency'						=>		round($system_details->efficiency, 2),
							'efficiency_sort'					=>		$system_details->efficiency,
							'data'							=>		$data,
							'tdh'							=>		$tdh,
							'flow_rate'						=>		$flow_rate,
							'system_head'						=>		getsystemhead($tdh, $flow_rate),
							'system_details'					=>		$system_details
							// 'get_deta'						=>		$_GET
						);
					}
				} else {

					$system_details = getcurvedetails($item['equipment_id'], $tdh, $flow_rate, $connection);
					$list[] = array(
							'equipment_count'					=>		$item['equipment_count'],
							'equipment_id'						=>		$item['equipment_id'],
							'equipment_model'					=>		$item['equipment_model'],
							'pump_id'							=>		$item['product_id'],
							'max_flow_rate'					=>		$item['max_flow_rate'],
							'min_flow_rate'					=>		$item['min_flow_rate'],
							'max_head'						=>		$item['max_head'],
							'min_head'						=>		$item['min_head'],
							'motor_kw'						=>		$item['motor_kw'],
							'motor_hp'						=>		$item['motor_hp'],
							'fullload_current_single_phase'		=>		$item['fullload_current_single_phase'],
							'fullload_current_three_phase'		=>		$item['fullload_current_three_phase'],
							'pipe_outlet'						=>		$item['pipe_outlet'],
							'product_id'						=>		$item['product_id'],
							'inverter_details'					=>		'',
							'inverter'						=>		0,
							'inverter_id'						=>		'',
							'inverter_model_name'				=>		'',
							'inverter_model'					=>		'',
							'panels'							=>		'',
							'pv_generator'						=>		'',
							'strings'							=>		'',
							'string_options'					=>		'',
							'string_options_count'				=>		'',
							'motor_fraction'					=>		'',
							'panel_models'						=>		'',
							'top_choices'						=>		'',
							'sizing'							=>		'',
							'cable'							=>		$item['cable'],
							'curve_head'						=>		$item['curve_head'],
							'efficiency'						=>		round($system_details->efficiency, 2),
							'efficiency_sort'					=>		$system_details->efficiency,
							'data'							=>		$data,
							'tdh'							=>		$tdh,
							'flow_rate'						=>		$flow_rate,
							'system_details'					=>		$system_details
							// 'get_deta'						=>		$_GET
						);
				}

			}

		}

		$efficiency_sort = array_column($list, 'efficiency_sort');
		array_multisort($efficiency_sort, SORT_DESC, $list);

		print_r(json_encode($list));

		break;

	// Sizing for DC Pumps
	case 'sizingdc':

		$tdh = $_GET['tdh'];
		$flow_rate = $_GET['flow_rate'];
		$cable_length = $_GET['cable'];

		// Save Sizing Details
		$params = (Object)array(
			'post_data' => $data,
			'get_data' => $_GET
		);

		$params = serialize($params);
		saveparameters($params, 'dc', $connection);

		// Run SQL Request
		$query = $connection->query("SELECT * FROM dc_equipment WHERE min_flow_rate <= " .(float)$flow_rate. " AND max_flow_rate >= " .(float)$flow_rate. " AND min_head <= " .$tdh. " AND max_head >= " .$tdh. "");
		$equipment = array();
		$count = 1;
		$custom_modules = array();

		if(isset($data['custom_module'])){
			$custom_module_panels = explode('|', $data['custom_module_panels']);
			for($j = 0; $j < count($custom_module_panels); $j++){
				array_push($custom_modules, $custom_module_panels[$j]);
			}
		}

		while($item = $query->fetch_object()){
				
			// Get Panels
			$panels = getpanelsdc($item->peak_voltage, $item->open_circuit_voltage, (float)$item->motor_w, $custom_modules, $cable_length, $connection, $data['min_panel_uplift'], $data['max_panel_uplift']);

			$equipment[] = array(
				'equipment_count'					=>	$count,
				'equipment_id'						=>	$item->equipment_id,
				'equipment_model'					=>	$item->equipment_model,
				'max_flow_rate'					=>	$item->max_flow_rate,
				'min_flow_rate'					=>	$item->min_flow_rate,
				'max_head'						=>	$item->max_head,
				'min_head'						=>	$item->min_head,
				'motor_w'							=>	$item->motor_w,
				'input_power_w'					=>	$item->input_power_w,
				'peak_voltage'						=>	$item->peak_voltage,
				'open_circuit_voltage'				=>	$item->open_circuit_voltage,
				'pipe_outlet'						=>	makefraction($item->dimensions_dn),
				'product_id'						=>	$item->product_id,
				'pv_generator'						=>	'PV Generator',
				'panels'							=>	$panels,
				// 'cable'								=>	$cable,
				'curve_head'						=>	getproperhead($item->curve, $flow_rate, $tdh)
			);
			$count++;
		}

		$list = array();

		foreach($equipment as $item){

			if($item['curve_head']->status){
				
				$pv_generator = array();
				$strings_content = '';
				$strings_array = array();
				$string_options = array();
				foreach($item['panels'] as $panel){
					if(count($panel->strings) > 0){					
						foreach($panel->strings as $string_details){
							$strings_array[] = array(
								'total_power'					=>	$string_details->total_power,
								'number_of_panels_per_string'		=>	$string_details->number_of_panels_per_string,
								'strings'						=>	$string_details->strings,
								'panel_model'					=>	$string_details->panel_model,
								'panel_rated_power_w'			=>	$panel->panel_rated_power_w,
								'nominal_voltage'				=>	$panel->nominal_voltage,
								'panel_id'					=>	$panel->panel_id,
								'array_motor_uplift'			=>	$string_details->array_motor_uplift,
								'short_circuit_current'			=>	$panel->short_circuit_current,
								'open_circuit_voltage'			=>	$panel->open_circuit_voltage,
								'cable'						=>	$string_details->cable
								// 'peak_voltage'					=>	(float)$panel->peak_voltage,
								// 'max_voltage'					=>	(float)$panel->peak_voltage * $string_details->number_of_panels_per_string
							);
						}
					}
				}

				// Group the Panels based on the Model
				$panel_models = array_unique(array_column($strings_array, 'panel_model'));
				$panel_models_grouped = array();
				$panels_top = array();

				foreach($strings_array as $key => $value){

					$panel_models_grouped[$value['panel_model']][] = array(

						'total_power'					=>	$value['total_power'],
						'number_of_panels_per_string'		=>	$value['number_of_panels_per_string'],
						'strings'						=>	$value['strings'],
						'panel_rated_power_w'			=>	$value['panel_rated_power_w'],
						'nominal_voltage'				=>	$value['nominal_voltage'],
						'panel_id'					=>	$value['panel_id'],
						'array_motor_uplift'			=>	$value['array_motor_uplift'],
						'short_circuit_current'			=>	$value['short_circuit_current'],
						'open_circuit_voltage'			=>	$value['open_circuit_voltage'],
						// 'peak_voltage'					=>	$value['peak_voltage'],
						// 'max_voltage'					=>	$value['max_voltage'],
						'panel_model'					=>	$value['panel_model'],
						'cable'						=>	$value['cable']

					);
					
				}

				foreach($panel_models_grouped as $key => $value){
					$panels_top[] = $panel_models_grouped[$key][(count($panel_models_grouped[$key]) - 1)];
				}
				
				$motor_fraction = array_column($panels_top, 'panel_rated_power_w');
				$top_strings = array_multisort($motor_fraction, SORT_DESC, $panels_top);
				$top_choices = array_slice($panels_top, 0, 3);

				for($g = 0; $g < count($top_choices); $g++){
					$string_options[] = (Object)array(
						'total_power'						=>	$top_choices[$g]['total_power'],
						'number_of_panels_per_string'			=>	$top_choices[$g]['number_of_panels_per_string'],
						'strings'							=>	$top_choices[$g]['strings'],
						'panel_model'						=>	$top_choices[$g]['panel_model'],
						'panel_rated_power_w'				=>	$top_choices[$g]['panel_rated_power_w'],
						'nominal_voltage'					=>	$top_choices[$g]['nominal_voltage'],
						'panel_id'						=>	$top_choices[$g]['panel_id'],
						'array_motor_uplift'				=>	$top_choices[$g]['array_motor_uplift'],
						'short_circuit_current'				=>	$top_choices[$g]['short_circuit_current'],
						'open_circuit_voltage'				=>	$top_choices[$g]['open_circuit_voltage'],
						'cable'							=>	$top_choices[$g]['cable']
					);
				}

				if(count($string_options) > 0){
					$list[] = array(
						'equipment_count'					=>		$item['equipment_count'],
						'equipment_id'						=>		$item['equipment_id'],
						'equipment_model'					=>		$item['equipment_model'],
						'pump_id'							=>		$item['product_id'],
						'max_flow_rate'					=>		$item['max_flow_rate'],
						'min_flow_rate'					=>		$item['min_flow_rate'],
						'max_head'						=>		$item['max_head'],
						'min_head'						=>		$item['min_head'],
						'motor_w'							=>		$item['motor_w'],
						'pipe_outlet'						=>		$item['pipe_outlet'],
						'product_id'						=>		$item['product_id'],
						'panels'							=>		$item['panels'],
						'pv_generator'						=>		$strings_content,
						'strings'							=>		$strings_array,
						'string_options'					=>		$string_options,
						'string_options_count'				=>		count($string_options),
						'motor_fraction'					=>		$motor_fraction,
						'panel_models'						=>		$panel_models,
						'panel_models_grouped'				=>		$panel_models_grouped,
						'panels_top'						=>		$panels_top,
						'top_choices'						=>		$top_choices,
						'sizing'							=>		$custom_modules,
						'cable'							=>		'',
						'curve_head'						=>		$item['curve_head'],
						'data'							=>		$data,
						'efficiency'						=>		'',
						'inverter_model_name'				=>		'',
						'inverter_id'						=>		0,
						'tdh'							=>		$tdh,
						'flow_rate'						=>		$flow_rate
					);
				}
			}
		}

		print_r(json_encode($list));

		break;

	case 'sizingsunflo':

		$data = (Object)$data;

		// Save Sizing Details
		$params = (Object)array(
			'post_data' => $data,
			'get_data' => $_GET
		);
		$params = serialize($params);
		saveparameters($params, 'sunflo', $connection);

		$query = $connection->query("SELECT sk.pump_id, sk.cable_length_2_5mm, sk.pv_model_id, p.panel_model, p.panel_rated_power_w, p.product_id AS panel_product_id, de.equipment_id, de.equipment_model, de.input_voltage, de.peak_voltage, de.open_circuit_voltage, de.motor_w, de.curve, de.product_id, sk.panel_count, de.min_head, de.max_head, de.min_flow_rate, de.max_flow_rate, de.inv_curve, de.dimensions_dn FROM sunflo_kits sk, dc_equipment de, panels p WHERE sk.pump_model_id = de.equipment_id AND sk.pv_model_id = p.panel_id AND de.min_head <= " .$data->pipe_head. " AND de.max_head >= " .$data->pipe_head. " ORDER BY sk.panel_count ASC");

		$irradiation = explode('|', $data->average_irradiation);
		$average_irradiation = $irradiation[(count($irradiation) - 1)];

		$row = 1;

		$items = array();
		while($item = $query->fetch_object()){

			$system_flow = systemflow($item->inv_curve, $data->pipe_head, $item->min_flow_rate, $item->max_flow_rate);

			if($system_flow->status){
				$items[] = (Object)array(
					'row'					=>	(int)$row,
					'pump_id'					=>	(int)$item->pump_id,
					'equipment_model'			=>	$item->equipment_model,
					'equipment_id'				=>	$item->equipment_id,
					'motor_w'					=>	$item->motor_w,
					'curve'					=>	$item->curve,
					'inv_curve'				=>	$item->inv_curve,
					'input_voltage'			=>	$item->input_voltage,
					'peak_voltage'				=>	(float)$item->peak_voltage,
					'open_circuit_voltage'		=>	$item->open_circuit_voltage,
					'product_id'				=>	$item->product_id,
					'cable_length_2_5mm'		=>	$item->cable_length_2_5mm,
					'panel_model'				=>	$item->panel_model,
					'panel_rated_power_w'		=>	$item->panel_rated_power_w,
					'panel_count'				=>	(int)$item->panel_count,
					'panel_id'				=>	$item->panel_product_id,
					'min_head'				=>	(float)$item->min_head,
					'max_head'				=>	(float)$item->max_head,
					'pipe_head'				=>	(float)$data->pipe_head,
					'pump_outlet'				=>	makefraction($item->dimensions_dn),
					'system_flow'				=>	$system_flow->flow,
					'flow_per_day'				=>	round(($system_flow->flow * (float)$average_irradiation), 2),
					'average_irradiation'		=>	$average_irradiation
				);

				$row++;
			}
		}

		print_r(json_encode($items));

		break;

	case 'getpage' :

		$page = file_get_contents('pages/' .$data['page']. '.php');
		print_r($page);

		break;

	case 'account':

		$query = $connection->query("UPDATE users SET fullname = '" .$connection->real_escape_string($data['fullname']). "', phone = '" .$connection->real_escape_string($data['phone']). "', email = '" .$connection->real_escape_string($data['email']). "', country = '" .$connection->real_escape_string($data['country']). "', company = '" .$connection->real_escape_string($data['company']). "', date_modified = NOW() WHERE user_id = " .(int)$data['user_id']. " AND unique_id = '" .$connection->real_escape_string($data['unique_id']). "'");

		if($query){
			$result = array(
				'text' => 'Save',
				'status' => 1,
				'reload' => 1
			);
		} else {
			$result = array(
				'text' => 'Error',
				'status' => 0
			);
		}

		print_r(json_encode($result));

		break;

	case 'company':

		// Check if the company exists
		$user = getloggedinuser();
		$data = (object)$data;

		if($user->user_origin == 1){

			// Let us check if the user has a company
			$query = $connection->query("SELECT company_id FROM companies WHERE unique_id = '" .$connection->real_escape_string($user->unique_id). "'");

			if($query->num_rows){

				// Update
				$update = $connection->query("UPDATE companies SET company_name = '" .$connection->real_escape_string($data->company). "', company_email = '" .$connection->real_escape_string($data->email). "', company_logo = 'Report-Logo.jpg', company_phone = '" .$connection->real_escape_string($data->phone_number). "', physical_location = '" .$connection->real_escape_string($data->physical_address). "', postal_address = '" .$connection->real_escape_string($data->postal_address). "', company_status = '" .(int)$data->status. "', company_website = '" .$connection->real_escape_string($data->website). "', date_modified = NOW() WHERE unique_id = '" .$connection->real_escape_string($user->unique_id). "'");

				if($update){
					$status = array(
						'status' => 1,
						'text' => 'Save',
						'reload' => 0
					);
				} else {
					$status = array(
						'status' => 2,
						'text' => 'Error',
						'connection' => $connection->error
					);				
				}

			} else {

				// Insert
				$insert = $connection->query("INSERT INTO companies SET unique_id = '" .$connection->real_escape_string($user->unique_id). "', company_name = '" .$connection->real_escape_string($data->company). "', company_email = '" .$connection->real_escape_string($data->email). "', company_logo = 'Report-Logo.jpg', company_phone = '" .$connection->real_escape_string($data->phone_number). "', physical_location = '" .$connection->real_escape_string($data->physical_address). "', postal_address = '" .$connection->real_escape_string($data->postal_address). "', company_status = '" .(int)$data->status. "', company_website = '" .$connection->real_escape_string($data->website). "', date_added = NOW(), date_modified = NOW()");

				if($insert){
					$status = array(
						'status' => 1,
						'text' => 'Save',
						'reload' => 0
					);
				} else {
					$status = array(
						'status' => 2,
						'text' => 'Error',
						'connection' => $connection->error
					);				
				}

			}

		} else {

			$logo = $data->logo != '' ? $data->logo : 'No-Logo.png';

			// Update the company details
			$update = $connection->query("UPDATE companies SET company_email = '" .$connection->real_escape_string($data->email). "', company_logo = '" .$connection->real_escape_string($logo). "', company_phone = '" .$connection->real_escape_string($data->phone_number). "', physical_location = '" .$connection->real_escape_string($data->physical_address). "', postal_address = '" .$connection->real_escape_string($data->postal_address). "', company_status = 1, company_website = '" .$connection->real_escape_string($data->website). "', date_modified = NOW() WHERE invitation_code = '" .$connection->real_escape_string($user->invitation_code). "'");

			if($update){
				$status = array(
					'status' => 1,
					'text' => 'Save',
					'reload' => 0
				);
			} else {
				$status = array(
					'status' => 2,
					'text' => 'Error',
					'connection' => $connection->error
				);				
			}

		}

		print_r(json_encode($status));

		break;

	case 'upload':

		$file = $_FILES;
		$path = $file['file']['name'];

		$filename = 'logo';
		$ext = pathinfo($path, PATHINFO_EXTENSION);
		$acceptedexts = array('png','jpg','jpeg');

		$extensionlist = array();

		for($j = 0; $j < count($acceptedexts); $j++){
			if($j != (count($acceptedexts) - 1)){
				array_push($extensionlist, $acceptedexts[$j]);
			}
		}

		$extensiontext = implode(', ', $extensionlist). ' and ' .$acceptedexts[(count($acceptedexts) - 1)];

		if(in_array($ext, $acceptedexts)){

			$user = $_COOKIE['unique_id'];
			$newfile = $user. '-logo-' .date('dmYHis'). '.' .$ext;

			if(move_uploaded_file($file['file']['tmp_name'], 'img/logos/' .$newfile)){				

				$status = array(
					'status' => 1,
					'text' => '<i class="fas fa-check"></i> Logo has been uploaded Successfully',
					'classstatus' => 'success',
					'file' => $newfile
				); 

			} else {

				$status = array(
					'status' => 2,
					'text' => '<i class="fas fa-exclamation-triangle"></i> An error has occurred. Please try again.',
					'classstatus' => 'warning'
				);

			}

		} else {

			$status = array(
				'status' => 3,
				'text' => '<i class="fas fa-exclamation-triangle"></i> The only excepted files are ' .$extensiontext,
				'classstatus' => 'warning'
			);

		}

		print_r(json_encode($status));

		break;

	case 'addcustomer':

		$unique_id = $_COOKIE['unique_id'];

		$query = $connection->query("INSERT INTO customers SET customer_name = '" .$connection->real_escape_string($data['customer_name']). "', customer_account = '" .$connection->real_escape_string($data['customer_account']). "', customer_telephone = '" .$connection->real_escape_string($data['customer_telephone']). "', customer_email = '" .$connection->real_escape_string($data['customer_email']). "', customer_physical = '" .$connection->real_escape_string($data['customer_physical']). "', customer_postal = '" .$connection->real_escape_string($data['customer_postal']). "', customer_country = '" .$connection->real_escape_string($data['country']). "', unique_id = '" .$connection->real_escape_string($unique_id). "', date_added = NOW(), date_modified = NOW()");

		if($query){

			$result = array(
				'status'	=>	1,
				'text'		=>	'Success',
				'reload' 	=> 1
			);

		} else {

			$result = array(
				'status'	=>	0,
				'text'		=>	'Error'
			);

		}

		print_r(json_encode($result));

		break;

	case 'deletecustommodule':

		$unique_id = $_COOKIE['unique_id'];
		$data = (Object)$data;
		$query = $connection->query("SELECT panel_id FROM panels WHERE panel_id = '" .(int)$data->panel_id. "' AND user = '" .$connection->real_escape_string($unique_id). "'");
		if($query->num_rows > 0){
			$delete = $connection->query("DELETE FROM panels WHERE panel_id = '" .(int)$data->panel_id. "' AND user = '" .$connection->real_escape_string($unique_id). "'");
			if($delete){
				$result = (Object)array(
					'status'	=> 1,
					'text'		=> 'Deleted'	
				);
			} else {
				$result = (Object)array(
					'status'	=> 2,
					'text'		=> 'Error'	
				);
			}
		} else {
			$result = (Object)array(
				'status'	=> 0,
				'text'		=> 'Error'	
			);
		}
		print_r(json_encode($result));

		break;

	case 'searchcustomer':

		$unique_id = $_COOKIE['unique_id'];
		$search = $data['customer_name'];

		$query = $connection->query("SELECT * FROM customers WHERE (customer_name LIKE '%" .$connection->real_escape_string($search). "%' OR customer_account LIKE '%" .$connection->real_escape_string($search). "%' OR customer_telephone LIKE '%" .$connection->real_escape_string($search). "%' OR customer_email LIKE '%" .$connection->real_escape_string($search). "%' OR customer_physical LIKE '%" .$connection->real_escape_string($search). "%') AND unique_id = '" .$connection->real_escape_string($unique_id). "'");

		$customers = array();

		while($item = $query->fetch_object()){
			$customers[] = (Object)array(
				'customer_id' 		=> (int)$item->customer_id,
				'customer_details'	=> $item->customer_name . ($item->customer_account != '' ? ' (' .$item->customer_account. ')' : '') . ' - ' .$item->customer_physical,
				'customer_name'		=> $item->customer_name
			);
		}

		print_r(json_encode($customers));

		break;

	case 'searchcrmnavcustomer':

		$query = array(
			'query'		=>	$data['customer_name'],
			'action'	=>	'searchCustomer'
		);

		$customers = searchcustomers($query);

		print_r(json_encode($customers));

		break;

	case 'saveproject':

		$status = array(
			'status' => 1,
			'reload' => 0,
			'details' => $data,
			'text' => 'Save',
			'project_id' => randomstring(15),
			'company' => getusercompany($data['company_id']),
			'customer' => getcustomer($data['customer_id'])
		);

		print_r(json_encode($status));

		break;

	case 'getcustommodules':

		$panels = implode('|', $data['panel_id']);

		$status = array(
			'status' => 1,
			'reload' => 0,
			'details' => $panels,
			'text' => 'Save'
		);

		print_r(json_encode($status));

		break;

	case 'saveprojectdetails':

		$unique_id = $_COOKIE['unique_id'];

		$sizing = array();
		foreach ($data['sizing'] as $key => $value) {
			$sizing[$value['name']] = $value['value'];
		}

		$project_details = (Object)array(
			'average_irradiation'	=>		$data['average_irradiation'],
			'average_output'		=>		$data['average_output'],
			'company_id'			=>		(int)$data['company_id'],
			'customer_name'		=>		$data['customer_name'],
			'customer_id'			=>		$data['customer_id'],
			'delivery_output'		=>		$data['delivery_output'],
			'location_name'		=>		$data['location_name'],
			'pipe_details'			=>		$data['pipe_details'],
			'pipe_length_details'	=>		$data['pipe_length_details'],
			'project_id'			=>		$data['project_id'],
			'project_name'			=>		$data['project_name'],
			'project_notes'		=>		$data['project_notes'],
			'sizing'				=>		$sizing,
			'solutionstring'		=>		$data['solutionstring'],
			'tdh'				=>		$data['tdh'],
			'wiringdiagram'		=>		$data['wiringdiagram']
		);

		// Check if the project already exists
		$query = $connection->query("SELECT project_id FROM projects WHERE project_code = '" .$connection->real_escape_string($project_details->project_id). "'");

		$insert = false;
		$update = false;

		if($query->num_rows > 0){

			// Update
			$sqlquery = $connection->query("UPDATE projects SET project_name = '" .$connection->real_escape_string($project_details->project_name). "', customer_id = '" .(int)$project_details->customer_id. "', user_id = '" .$connection->real_escape_string($unique_id). "', project_details = '" .$connection->real_escape_string(serialize($project_details)). "', location_name = '" .$connection->real_escape_string($project_details->location_name). "', company_id = '" .(int)$project_details->company_id. "', date_modified = NOW() WHERE project_code = '" .$connection->real_escape_string($project_details->project_id). "'");

			$update = true;

		} else {

			// Insert
			$sqlquery = $connection->query("INSERT INTO projects SET project_code = '" .$connection->real_escape_string($project_details->project_id). "', project_name = '" .$connection->real_escape_string($project_details->project_name). "', customer_id = '" .(int)$project_details->customer_id. "', user_id = '" .$connection->real_escape_string($unique_id). "', project_details = '" .$connection->real_escape_string(serialize($project_details)). "', location_name = '" .$connection->real_escape_string($project_details->location_name). "', company_id = '" .(int)$project_details->company_id. "', date_added = NOW(), date_modified = NOW()");

			$insert = true;

		}

		if($insert || $update){
			$result = (Object)array(
				'status'	=>	1,
				'text'		=>	'success'
			);
		} else {			
			$result = (Object)array(
				'status'	=>	0,
				'text'		=>	'error'
			);
		}

		print_r(json_encode($result));

		break;

	case 'addcustommodule':

		$unique_id = $_COOKIE['unique_id'];

		// Check if the Part number Exists
		$query = $connection->query("INSERT INTO panels SET panel_model = '" .$connection->real_escape_string($data['panel_model']). "', part_number = '" .$connection->real_escape_string($data['part_number']). "', panel_rated_power_w = '" .$connection->real_escape_string($data['panel_rated_power_w']). "', peak_voltage = '" .$connection->real_escape_string($data['peak_voltage']). "', open_circuit_voltage = '" .$connection->real_escape_string($data['open_circuit_voltage']). "', nominal_voltage = '" .$connection->real_escape_string($data['nominal_voltage']). "', short_circuit_current = '" .$connection->real_escape_string($data['short_circuit_current']). "', module_dimensions = '" .$connection->real_escape_string($data['module_dimensions']). "', custom_status = 1, user = '" .$connection->real_escape_string($unique_id). "'");

		if($query){
			$result = array(
				'status' => 1,
				'text' => 'Added'
			);
		} else {
			$result = array(
				'status' => 0,
				'text' => 'Error'
			);
		}
		print_r(json_encode($result));

		break;

	case 'checkpartavailability':

		$user_origin = (int)$_COOKIE['user_origin'];

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://www.davisandshirtliff.com/shop/index.php?route=product/product/productskuapi',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => 'product_sku=' .$data['part_number'],
			CURLOPT_SSL_VERIFYPEER => false,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/x-www-form-urlencoded',
				'cache-control: no-cache'
			),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);

		if(!$err){
			$response = json_decode($response);
		} else {
			$response = false;
		}

		if($user_origin == 1){
			$result = array(
				'text' 		=>	'<span class="badge badge-success">' .(int)$response->quantity. '</span>',
				'title'		=>	$data['part_name']. ' is Available',
				'response'	=>	$response,
				'user_origin'	=>	$user_origin
			);
		} else {

			if($response != false){
				if((int)$response->quantity > 0){
					$result = array(
						'text'		=>	'<span class="badge badge-success"><i class="fas fa-check"></i></span>',
						'title'		=>	$data['part_name']. ' is Available',
						'response'	=>	$response,
						'user_origin'	=>	$user_origin
					);
				} else {
					$result = array(
						'text'		=>	'<i class="far fa-sad-tear"></i>',
						'title'		=>	$data['part_name']. ' is Unavailable',
						'response'	=>	$response,
						'user_origin'	=>	$user_origin
					);
				}
			} else {
				$result = array(
					'text'		=>	'<i class="far fa-sad-tear"></i>',
					'title'		=>	$data['part_name']. ' is Unavailable',
					'response'	=>	$response,
					'user_origin'	=>	$user_origin
				);
			}
			
		}

		print_r(json_encode($result));

		break;

	case 'feedback':

		$user = getloggedinuser();
		$query = $connection->query("INSERT INTO feedback SET customer_name = '" .$connection->real_escape_string($user->fullname). "', unique_id = '" .$connection->real_escape_string($user->unique_id). "', user_subject = '" .$connection->real_escape_string($data['user_subject']). "', user_feedback = '" .$connection->real_escape_string($data['user_feedback']). "', date_added = NOW()");
		if($query){
			$result = array(
				'status' => 1,
				'text' => 'Feedback Submitted'
			);
		} else {
			$result = array(
				'status' => 0,
				'text' => 'Error, Try again'
			);
		}

		print_r(json_encode($result));

		break;

	case 'deleteproject':

		$unique_id = $_COOKIE['unique_id'];
		$data = (Object)$data;
		$query = $connection->query("SELECT project_id FROM projects WHERE project_id = '" .(int)$data->project_id. "' AND user_id = '" .$connection->real_escape_string($unique_id). "'");		
		if($query->num_rows > 0){
			$delete = $connection->query("DELETE FROM projects WHERE project_id = '" .(int)$data->project_id. "' AND user_id = '" .$connection->real_escape_string($unique_id). "'");
			if($delete){
				$result = (Object)array(
					'status'	=> 1,
					'text'		=> 'Deleted'	
				);
			} else {
				$result = (Object)array(
					'status'	=> 2,
					'text'		=> 'Error'
				);
			}
		} else {
			$result = (Object)array(
				'status'	=> 0,
				'text'		=> 'Error'	
			);
		}
		print_r(json_encode($result));

		break;

	case 'getpvdisconnect':

		// Was intially used for Lorentz PV Disconnect
		// $details = (Object)$data;
		// $vdc = $details->panel_voc * $details->panels_count;
		// $query = $connection->query("SELECT * FROM pv_disconnect WHERE max_vdc > " .(float)$vdc. " AND status = 1 LIMIT 1");
		// $item = $query->fetch_object();
		// $equipment = (Object)array(
		// 	'max_current'			=> (float)$item->max_current,
		// 	'max_vdc'				=> (float)$item->max_vdc,
		// 	'pv_disconnect_make'	=> $item->pv_disconnect_make,
		// 	'pv_disconnect_model' 	=> $item->pv_disconnect_model
		// );

		// $option_a = $details->strings_count / 5;
		// $option_b = ($details->short_circuit_current * $details->strings_count  / 40);
		// if($details->strings_count >= 5){
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
		$details = (Object)$data;
		$total_system_currect = $details->strings_count * $details->short_circuit_current;
		// Possible 2ST options
		$x = $details->strings_count / 2;
		// Possible 4ST options
		$y = $details->strings_count / 4;
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

			$a = 32 / $details->short_circuit_current;
			$b = floor($a);
			$c = $details->strings_count / $b;
			$d = ceil($c);
			$e = $details->strings_count / 4;
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
			'details' => $details,
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
		print_r(json_encode($pv_disconnect));

		break;

	case 'getmotorpowers':

		$data = (Object)$data;
		$motors = getmotorpowers((int)$data->phase);
		print_r(json_encode($motors));

		break;

	case 'getproductcurve':

		$data = (Object)$data;
		if($data->power_type == 'ac' || $data->power_type == 'surface'){
			$query = $connection->query("SELECT * FROM equipment WHERE equipment_id = " .(int)$data->equipment_id. "");
		} else {
			$query = $connection->query("SELECT * FROM dc_equipment WHERE equipment_id = " .(int)$data->equipment_id. "");		
		}
		$details = $query->fetch_object();
		// Use 10 Points to Draw the curve
		$pumpcurve = array();
		$efficiencycurve = array();
		$systemcurve = array();

		$curve_differences = array();
		$max_system_flow_rate = $details->max_flow_rate * 1.05;

		// Get Max Head at Max Flow
		$max_head_curve = gethead($details->curve, $max_system_flow_rate);
		if($max_head_curve < 0){
			$max_flow_rate = $details->max_flow_rate * 1;
		} else {
			$max_flow_rate = $max_system_flow_rate;
		}

		// Get System Curve
		$q = getq($data->pump_tdh, $data->pump_flow);
		for($j = 0; $j <= 10; $j++){

			$flow = $max_flow_rate * $j / 10;
			$flow = round($flow, 4);
			$head = gethead($details->curve, $flow);
			$efficiency = getefficiencydetails($details->efficiency, $flow);
			$h = getsystemhead($q, $flow);
			if($efficiency < 0){
				$efficiency = 0;
			}
			$pumpcurve[] = array(
				'flow_rate'		=>	$flow,
				'head'			=>	round($head, 2)
			);
			$efficiencycurve[] = array(
				'flow_rate'		=>	$flow,
				'efficiency'		=>	round($efficiency, 2)
			);

		}

		// Loop through a 50000 points and return the values
		for($m = 0; $m < 50000; $m++){

			$flow = $max_flow_rate * $m / 50000;
			$flow = round($flow, 4);
			$head = gethead($details->curve, $flow);
			$h = getsystemhead($q, $flow);
			$difference_h = $head - $h;

			if($difference_h < 0){				
				$curve_differences[] = array(
					'head'			=>	round($head, 3),
					'system_head'		=>	$h,
					'difference_h'		=>	($difference_h * -1),
					'flow'			=>	round($flow, 3)
				);
			} else {				
				$curve_differences[] = array(
					'head'			=>	round($head, 3),
					'system_head'		=>	$h,
					'difference_h'		=>	$difference_h,
					'flow'			=>	round($flow, 3)
				);
			}

		}

		$head_differences = array_column($curve_differences, 'difference_h');
		array_multisort($head_differences, SORT_ASC, $curve_differences);
		$leastpoint = array_slice($curve_differences, 0, 1);

		// Get the System Curve
		for($j = 0; $j <= 10; $j++){

			$flow = $leastpoint[0]['flow'] * $j / 10;
			$flow = round($flow, 4);
			$h = getsystemhead($q, $flow);
			$systemcurve[] = array(
				'flow_rate'		=>	$flow,
				'system_head'		=>	round($h, 2)
			);
		}

		$duty = array();
		$duty[] = (Object)array(
			'flow_rate'		=>	(float)round($data->pump_flow, 2),
			'pump_tdh'		=>	(float)round($data->pump_tdh, 2)
		);

		$efficiency_points = array();

		// Get efficiency at least point
		$duty_efficiency = getefficiencydetails($details->efficiency, $leastpoint[0]['flow']);
		$efficiency_points[] = (Object)array(
			'flow_rate'		=>	$leastpoint[0]['flow'],
			'efficiency'		=>	round($duty_efficiency, 3)
		);

		$curve = (Object)array(
			'curve'				=>	$pumpcurve,
			'efficiency'			=>	$efficiencycurve,
			'system'				=>	$systemcurve,
			'name'				=>	$details->equipment_model,
			'q'					=>	$q,
			'duty'				=>	$duty,
			'leastpoint'			=>	$leastpoint,
			'duty_efficiency'		=>	$efficiency_points
		);

		print_r(json_encode($curve));

		break;

	case 'getproductsunflocurve':

		$data = (Object)$data;

		$query = $connection->query("SELECT * FROM dc_equipment WHERE equipment_id = " .(int)$data->equipment_id. "");
		$details = $query->fetch_object();

		// Use 10 Points to Draw the curve
		$pumpcurve = array();
		$systemcurve = array();
		$curve_differences = array();
		$max_system_flow_rate = $details->max_flow_rate * 1.05;

		// Get Max Head at Max Flow
		$max_head_curve = gethead($details->curve, $max_system_flow_rate);
		if($max_head_curve < 0){
			$max_flow_rate = $details->max_flow_rate * 1;
		} else {
			$max_flow_rate = $max_system_flow_rate;
		}

		// // Get System Curve
		$q = getq($data->pump_head, $data->pump_flow);
		for($j = 0; $j <= 10; $j++){

			$flow = $max_flow_rate * $j / 10;
			$flow = round($flow, 4);

			$head = gethead($details->curve, $flow);
			$h = getsystemhead($q, $flow);

			$pumpcurve[] = array(
				'flow_rate'		=>	$flow,
				'head'			=>	round($head, 2)
			);

		}

		// // Loop through a 50000 points and return the values
		for($m = 0; $m < 50000; $m++){

			$flow = $max_flow_rate * $m / 50000;
			$flow = round($flow, 4);

			$head = gethead($details->curve, $flow);
			$h = getsystemhead($q, $flow);
			$difference_h = $head - $h;

			if($difference_h < 0){				
				$curve_differences[] = array(
					'head'			=>	round($head, 3),
					'system_head'		=>	$h,
					'difference_h'		=>	($difference_h * -1),
					'flow'			=>	round($flow, 3)
				);
			} else {				
				$curve_differences[] = array(
					'head'			=>	round($head, 3),
					'system_head'		=>	$h,
					'difference_h'		=>	$difference_h,
					'flow'			=>	round($flow, 3)
				);
			}

		}

		$head_differences = array_column($curve_differences, 'difference_h');
		array_multisort($head_differences, SORT_ASC, $curve_differences);
		$leastpoint = array_slice($curve_differences, 0, 1);

		// Get the System Curve
		for($j = 0; $j <= 10; $j++){

			$flow = $leastpoint[0]['flow'] * $j / 10;
			$flow = round($flow, 4);

			$h = getsystemhead($q, $flow);

			$systemcurve[] = array(
				'flow_rate'		=>	$flow,
				'system_head'	=>	round($h, 2)
			);

		}

		$duty = array();
		$duty[] = (Object)array(
			'flow_rate'		=>	(float)round($data->pump_flow, 2),
			'pump_tdh'		=>	(float)round($data->pump_head, 2)
		);

		$curve = (Object)array(
			'curve'				=>	$pumpcurve,
			'system'				=>	$systemcurve,
			'name'				=>	$details->equipment_model,
			'q'					=>	$q,
			'duty'				=>	$duty,
			'leastpoint'			=>	$leastpoint,
		);

		print_r(json_encode($curve));

		break;

	case 'uplift':

		$data = (Object)$data;
		$unique_id = $_COOKIE['unique_id'];

		// Save the details based on the User
		$update = $connection->query("UPDATE users SET panel_uplift = '" .$connection->real_escape_string(serialize($data)). "' WHERE unique_id = '" .$connection->real_escape_string($unique_id). "'");

		print_r(json_encode($update));

		break;

	case 'printproject':

		$user = getloggedinuser();
		$data = (Object)$data;

		$project = getprojectbyid($user->unique_id, $data->project_id);
		$company = getusercompanybyid($project->company_id);
		$solution = explode('|', $project->project_details->solutionstring);

		$irradiation = explode('|', $project->project_details->average_irradiation);
		$avg_irradiation = array();
		$output = array();

		foreach($irradiation as $item){
			array_push($avg_irradiation, (float)$item);
			array_push($output, round($solution[10] * (float)$item, 2));
		}

		$date = date('l, dS M Y', strtotime($project->project_date));
		$url = gethost();

		$html_content = getsunflotemplate($project, $company, $solution, $irradiation, $output, $url, $date, $data);

		$projectfile = $project->project_code. '.html';
		file_put_contents('projects/html/' .$projectfile, $html_content);

		$projectpdf = new Dompdf();
		$projectpdf->set_option('isHtml5ParserEnabled', true);
		$projectpdf->set_option('defaultFont', 'OpenSans');
		$html = file_get_contents('projects/html/' .$projectfile);

		$projectpdf->loadHtml($html, 'UTF-8');
		$projectpdf->set_option('defaultMediaType', 'all');
		$projectpdf->setPaper('A4', 'portrait');
		$projectpdf->render();

		$canvas = $projectpdf->getCanvas();
		$footer = $canvas->open_object();

		$canvas->page_text(30, 815, 'Powered By SolarCalc', '', 8, array(255, 0, 0));
		$canvas->page_text(270, 815, 'Page {PAGE_NUM} of {PAGE_COUNT}', '', 8, array(255, 0, 0));
		$canvas->page_text(455, 815, 'solarcalc.davisandshirtliff.com', '', 8, array(255, 0, 0));
		$canvas->page_line(20, 810, 576, 810, array(255, 0, 0), 1, '');
		$canvas->close_object();
		$canvas->add_object($footer, 'all');

		$pdf_generated = $projectpdf->output();
		file_put_contents('projects/pdf/' .$project->project_code. '.pdf', $pdf_generated);
		$file_status = file_exists('projects/pdf/' .$project->project_code. '.pdf');

		if($file_status){
			// Set Print PDF status as True
			$update_project_file = printprojectpdf($project->project_code, $connection);
		}

		$result = (Object)array(
			'data'		=>	$data,
			'project'		=>	$project,
			'pdf'		=>	$url . 'projects/pdf/' .$project->project_code. '.pdf',
			'file_status'	=>	$file_status
		);

		unlink('projects/html/' .$projectfile);
		print_r(json_encode($result));

		break;

	case 'printacproject':

		$user = getloggedinuser();
		$data = (Object)$data;

		$project = getprojectbyid($user->unique_id, $data->project_id);
		$company = getusercompanybyid($project->company_id);
		$solution = explode('|', $project->project_details->solutionstring);

		$irradiation = explode('|', $project->project_details->average_irradiation);
		$avg_irradiation = array();
		$output = array();

		foreach($irradiation as $item){
			array_push($avg_irradiation, (float)$item);
			array_push($output, round($solution[19] * (float)$item, 2));
		}

		$date = date('l, dS M Y', strtotime($project->project_date));
		$url = gethost();
		$panel = getpanel($solution[4], $solution[5]);
		$pv_disconnect = getpvdisconnect($solution[6], $solution[7], $panel->short_circuit_current, $panel->open_circuit_voltage);
		$html_content = gettemplate($project, $company, $solution, $irradiation, $output, $url, $date, $data, $pv_disconnect);

		$projectfile = $project->project_code. '.html';
		file_put_contents('projects/html/' .$projectfile, $html_content);

		$projectpdf = new Dompdf();
		$projectpdf->set_option('isHtml5ParserEnabled', true);
		$projectpdf->set_option('defaultFont', 'OpenSans');
		$html = file_get_contents('projects/html/' .$projectfile);

		$projectpdf->loadHtml($html, 'UTF-8');
		$projectpdf->set_option('defaultMediaType', 'all');
		$projectpdf->setPaper('A4', 'portrait');
		$projectpdf->render();

		$canvas = $projectpdf->getCanvas();
		$footer = $canvas->open_object();

		$canvas->page_text(30, 815, 'Powered By SolarCalc', '', 8, array(255, 0, 0));
		$canvas->page_text(270, 815, 'Page {PAGE_NUM} of {PAGE_COUNT}', '', 8, array(255, 0, 0));
		$canvas->page_text(455, 815, 'solarcalc.davisandshirtliff.com', '', 8, array(255, 0, 0));
		$canvas->page_line(20, 810, 576, 810, array(255, 0, 0), 1, '');
		$canvas->close_object();
		$canvas->add_object($footer, 'all');

		$pdf_generated = $projectpdf->output();
		file_put_contents('projects/pdf/' .$project->project_code. '.pdf', $pdf_generated);
		$file_status = file_exists('projects/pdf/' .$project->project_code. '.pdf');

		if($file_status){
			// Set Print PDF status as True
			$update_project_file = printprojectpdf($project->project_code, $connection);
		}

		$result = (Object)array(
			'data'		=>	$data,
			'project'		=>	$project,
			'pdf'		=>	$url . 'projects/pdf/' .$project->project_code. '.pdf',
			'file_status'	=>	$file_status
		);

		unlink('projects/html/' .$projectfile);
		print_r(json_encode($result));

		break;

	case 'printsurfaceproject':

		$user = getloggedinuser();
		$data = (Object)$data;

		$project = getprojectbyid($user->unique_id, $data->project_id);
		$company = getusercompanybyid($project->company_id);
		$solution = explode('|', $project->project_details->solutionstring);

		$irradiation = explode('|', $project->project_details->average_irradiation);
		$avg_irradiation = array();
		$output = array();

		foreach($irradiation as $item){
			array_push($avg_irradiation, (float)$item);
			array_push($output, round($solution[19] * (float)$item, 2));
		}

		$date = date('l, dS M Y', strtotime($project->project_date));
		$url = gethost();
		$panel = getpanel($solution[4], $solution[5]);
		$pv_disconnect = getpvdisconnect($solution[6], $solution[7], $panel->short_circuit_current, $panel->open_circuit_voltage);
		$html_content = gettemplatesurface($project, $company, $solution, $irradiation, $output, $url, $date, $data, $pv_disconnect);

		$projectfile = $project->project_code. '.html';
		file_put_contents('projects/html/' .$projectfile, $html_content);

		$projectpdf = new Dompdf();
		$projectpdf->set_option('isHtml5ParserEnabled', true);
		$projectpdf->set_option('defaultFont', 'OpenSans');
		$html = file_get_contents('projects/html/' .$projectfile);

		$projectpdf->loadHtml($html, 'UTF-8');
		$projectpdf->set_option('defaultMediaType', 'all');
		$projectpdf->setPaper('A4', 'portrait');
		$projectpdf->render();

		$canvas = $projectpdf->getCanvas();
		$footer = $canvas->open_object();

		$canvas->page_text(30, 815, 'Powered By SolarCalc', '', 8, array(255, 0, 0));
		$canvas->page_text(270, 815, 'Page {PAGE_NUM} of {PAGE_COUNT}', '', 8, array(255, 0, 0));
		$canvas->page_text(455, 815, 'solarcalc.davisandshirtliff.com', '', 8, array(255, 0, 0));
		$canvas->page_line(20, 810, 576, 810, array(255, 0, 0), 1, '');
		$canvas->close_object();
		$canvas->add_object($footer, 'all');

		$pdf_generated = $projectpdf->output();
		file_put_contents('projects/pdf/' .$project->project_code. '.pdf', $pdf_generated);
		$file_status = file_exists('projects/pdf/' .$project->project_code. '.pdf');

		if($file_status){
			// Set Print PDF status as True
			$update_project_file = printprojectpdf($project->project_code, $connection);
		}

		$result = (Object)array(
			'data'		=>	$data,
			'project'		=>	$project,
			'pdf'		=>	$url . 'projects/pdf/' .$project->project_code. '.pdf',
			'file_status'	=>	$file_status
		);

		unlink('projects/html/' .$projectfile);
		print_r(json_encode($result));

		break;

	case 'printsolarizationproject':
	
		$user = getloggedinuser();
		$data = (Object)$data;

		$project = getprojectbyid($user->unique_id, $data->project_id);
		$company = getusercompanybyid($project->company_id);
		$solution = explode('|', $project->project_details->solutionstring);

		$irradiation = explode('|', $project->project_details->average_irradiation);
		$avg_irradiation = array();

		foreach($irradiation as $item){
			array_push($avg_irradiation, (float)$item);
		}

		$date = date('l, dS M Y', strtotime($project->project_date));
		$url = gethost();
		$panel = getpanel($solution[4], $solution[5]);

		$pv_disconnect = getpvdisconnect($solution[6], $solution[7], $panel->short_circuit_current, $panel->open_circuit_voltage);
		$html_content = getsolarizationtemplate($project, $company, $solution, $irradiation, $url, $date, $data, $pv_disconnect);

		$projectfile = $project->project_code. '.html';
		file_put_contents('projects/html/' .$projectfile, $html_content);

		$projectpdf = new Dompdf();
		$projectpdf->set_option('isHtml5ParserEnabled', true);
		$projectpdf->set_option('defaultFont', 'OpenSans');
		$html = file_get_contents('projects/html/' .$projectfile);

		$projectpdf->loadHtml($html, 'UTF-8');
		$projectpdf->set_option('defaultMediaType', 'all');
		$projectpdf->setPaper('A4', 'portrait');
		$projectpdf->render();

		$canvas = $projectpdf->getCanvas();
		$footer = $canvas->open_object();

		$canvas->page_text(30, 815, 'Powered By SolarCalc', '', 8, array(255, 0, 0));
		$canvas->page_text(270, 815, 'Page {PAGE_NUM} of {PAGE_COUNT}', '', 8, array(255, 0, 0));
		$canvas->page_text(455, 815, 'solarcalc.davisandshirtliff.com', '', 8, array(255, 0, 0));
		$canvas->page_line(20, 810, 576, 810, array(255, 0, 0), 1, '');
		$canvas->close_object();
		$canvas->add_object($footer, 'all');

		$pdf_generated = $projectpdf->output();
		file_put_contents('projects/pdf/' .$project->project_code. '.pdf', $pdf_generated);
		$file_status = file_exists('projects/pdf/' .$project->project_code. '.pdf');

		if($file_status){
			// Set Print PDF status as True
			$update_project_file = printprojectpdf($project->project_code, $connection);
		}

		$result = (Object)array(
			'data'		=>	$data,
			'project'		=>	$project,
			'pdf'		=>	$url . 'projects/pdf/' .$project->project_code. '.pdf',
			'file_status'	=>	$file_status
		);

		unlink('projects/html/' .$projectfile);
		print_r(json_encode($result));

		break;
			
	default:

		# code...
		break;

}

function randomstring($length){

	$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnpqrstuvwxyz';
	$charactersLength = strlen($characters);
    $randomString = '';
    for ($j = 0; $j < $length; $j++) {
        $randomString .= $characters[mt_rand(0, $charactersLength - 1)];
    }
    return $randomString;

}

// function setsession($unique_id, $origin, $connection){

// 	// $token = randomstring(179);
// 	// $insert = $connection->query("INSERT INTO user_sessions SET user_unique_id = '" .$connection->real_escape_string($unique_id). "', user_session_token = '" .$connection->real_escape_string($token). "', user_session_start = NOW()");
// 	// session_start();
// 	// $_SESSION['unique_id'] = $unique_id;
// 	// $_SESSION['unique_token'] = $token;
// 	// $_SESSION['user_origin'] = $origin;

// }

function setsitecookie($unique_id, $origin, $connection){

	$token = randomstring(179);
	$insert = $connection->query("INSERT INTO user_sessions SET user_unique_id = '" .$connection->real_escape_string($unique_id). "', user_session_token = '" .$connection->real_escape_string($token). "', user_session_start = NOW()");

	$time = time() + (86400 * 7);
	setcookie('unique_id', $unique_id, $time, '/');
	setcookie('unique_token', $token, $time, '/');
	setcookie('user_origin', $origin, $time, '/');

}

function endsitecookie($connection){

	$cookie = $_COOKIE;

	$update = $connection->query("UPDATE user_sessions SET user_session_end = NOW() WHERE user_unique_id = '" .$connection->real_escape_string($cookie['unique_id']). "' AND user_session_token = '" .$connection->real_escape_string($cookie['unique_token']). "'");

	unset($cookie['unique_id']);
	unset($cookie['unique_token']);
	unset($cookie['user_origin']);

	$time = time() - 3600;

	setcookie('unique_id', null, $time, '/');
	setcookie('unique_token', null, $time, '/');
	setcookie('user_origin', null, $time, '/');

}

// function endsession($connection){

// 	session_start();
// 	$session = $_SESSION;
// 	$update = $connection->query("UPDATE user_sessions SET user_session_end = NOW() WHERE user_unique_id = '" .$connection->real_escape_string($session['unique_id']). "' AND user_session_token = '" .$connection->real_escape_string($session['unique_token']). "'");
// 	session_unset();
// 	session_destroy();

// }

function getlocationcoordinates($location_name){
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://maps.googleapis.com/maps/api/geocode/json?address=' .$location_name. '&key=AIzaSyDc7DcO9HEL0__epR4GTePnExXkyfduo58',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_POSTFIELDS => '',
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

function getlocationdetails($latlng){
	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://maps.googleapis.com/maps/api/geocode/json?latlng=' .$latlng. '&key=AIzaSyDc7DcO9HEL0__epR4GTePnExXkyfduo58',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_POSTFIELDS => '',
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

function updatelocation($coordinates, $connection, $location_code, $country_code){

	$query = $connection->query("UPDATE world_locations SET location_coordinates = '" .$coordinates->lat. ',' .$coordinates->lng. "' WHERE LOWER(location_code) = '" .strtolower($location_code). "' AND LOWER(country_code) = '" .strtolower($country_code). "'");

}

function getlocationirradiation($coordinates){

	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://power.larc.nasa.gov/cgi-bin/v1/DataAccess.py?request=execute&identifier=SinglePoint&parameters=DNR&userCommunity=SSE&tempAverage=CLIMATOLOGY&outputList=ASCII&lat=' .$coordinates->lat. '&lon=' .$coordinates->lng,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_POSTFIELDS => '',
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_HTTPHEADER => array(
			'cache-control: no-cache'
		),
	));
	$response = json_decode(curl_exec($curl), true);
	return $response;

}

function getinverter($connection, $kw, $hybrid, $phase, $status = false){

	// if($status != false){

	// 	$query = $connection->query("SELECT * FROM inverters WHERE motor_rated_kw >= " .(float)$kw. " AND status = 1 AND hybrid_status = " .(int)$hybrid. " AND phase = " .(int)$phase. " AND inverter_model LIKE 'SV3%' ORDER BY motor_rated_kw ASC LIMIT 1");

	// } else {

		$query = $connection->query("SELECT * FROM inverters WHERE motor_rated_kw >= " .(float)$kw. " AND status = 1 AND hybrid_status = " .(int)$hybrid. " AND phase = " .(int)$phase. " ORDER BY motor_rated_kw ASC, inverter_model DESC LIMIT 1");

	// }
	
	if($query->num_rows > 0){
		$inverter = $query->fetch_object();
		return $inverter;
	} else {
		return false;
	}

}

function getcable($connection, $kw, $cable, $phase){
	$query = $connection->query("SELECT * FROM dropcable_sizes WHERE motor_kw >= " .(float)$kw. " AND phase = " .(int)$phase. " ORDER BY cable_id ASC LIMIT 1");
	// return $query->num_rows;
	if($query->num_rows){
		// Get all the cable length allowed
		$item = $query->fetch_object();
		$cable_lengths = array();
		if($item->cable_15mm != 'x'){
			$cable_lengths[] = (Object)array(
				'max_cable_length'	=>	(float)$item->cable_15mm,
				'cable_name'		=>	'1.5mm²'
			);
		}
		if($item->cable_25mm != 'x'){
			$cable_lengths[] = (Object)array(
				'max_cable_length'	=>	(float)$item->cable_25mm,
				'cable_name'		=>	'2.5mm²'
			);
		}
		if($item->cable_4mm != 'x'){
			$cable_lengths[] = (Object)array(
				'max_cable_length'	=>	(float)$item->cable_4mm,
				'cable_name'		=>	'4mm²'
			);
		}
		if($item->cable_6mm != 'x'){
			$cable_lengths[] = (Object)array(
				'max_cable_length'	=>	(float)$item->cable_6mm,
				'cable_name'		=>	'6mm²'
			);
		}
		if($item->cable_10mm != 'x'){
			$cable_lengths[] = (Object)array(
				'max_cable_length'	=>	(float)$item->cable_10mm,
				'cable_name'		=>	'10mm²'
			);
		}
		if($item->cable_16mm != 'x'){
			$cable_lengths[] = (Object)array(
				'max_cable_length'	=>	(float)$item->cable_16mm,
				'cable_name'		=>	'16mm²'
			);
		}
		if($item->cable_250mm != 'x'){
			$cable_lengths[] = (Object)array(
				'max_cable_length'	=>	(float)$item->cable_250mm,
				'cable_name'		=>	'25mm²'
			);
		}
		$cable = (float)$cable;
		// Check if the cable length will fit
		$suitable_cables = array();
		$next_alternatives = array();

		foreach($cable_lengths as $cable_length){
			if($cable_length->max_cable_length >= $cable){
				$suitable_cables[] = $cable_length;
			}
			if($cable_length->max_cable_length == ''){
				$next_alternatives[] = $cable_length;
			}
		}
		if(count($suitable_cables) == 0 && count($next_alternatives) == 0){
			return (Object)array(
				'max_cable_length'	=> 0,
				'cable_name'		=> '> 25mm²'
			);			
		} else if (count($suitable_cables) == 0){
			return $next_alternatives[0];
		} else {
			return $suitable_cables[0];
		}
	} else {
		return (Object)array(
			'max_cable_length'	=> 0,
			'cable_name'		=> 'None'
		);
	}
}

function makefraction($float){
	// Get Bit after the decimal
	$details = explode('.', $float);

	if($details[0] == 0){
		$wholenumber = '';
	} else {
		$wholenumber = $details[0];
	}

	if(count($details) > 1){
		if($details[1] == 5){

			return $wholenumber. '½';

		} else if($details[1] == 25){

			return $wholenumber. '¼';

		} else if($details[1] == 75){

			return $wholenumber. '¾';

		} else if($details[1] == 0){

			return $wholenumber. '';

		}
	} else {
		return $wholenumber. '';
	}
}

function getpanels($min, $max, $motor_size, $custom_modules, $connection, $min_uplift, $max_uplift){

	$min_efficiency = 1 / $min_uplift;

	// check Motor Size and then determine the Max Efficiency
	// if((float)$motor_size <= 750){
	// 	$max_efficiency = 1 / $max_uplift;
	// } else {
		$max_efficiency = 1 / $max_uplift;
	// }
	

	$list = array();
	$panels = array();

	if(count($custom_modules) > 0){

		$sql = "SELECT * FROM panels WHERE status = 1 AND panel_id IN (" .implode(',', $custom_modules). ") AND status = 1 ORDER BY panel_rated_power_w ASC";
		$query = $connection->query($sql);
		while($item = $query->fetch_object()){
			$panels[] = (Object)array(
				'peak_voltage'				=>	$item->peak_voltage,
				'open_circuit_voltage'		=>	$item->open_circuit_voltage,
				'panel_rated_power_w'		=>	$item->panel_rated_power_w,
				'panel_id'				=>	$item->product_id,
				'panel_model'				=>	$item->panel_model,
				'nominal_voltage'			=>	$item->nominal_voltage,
				'short_circuit_current'		=>	$item->short_circuit_current
			);
		}	

	} else {

		$sql = "SELECT * FROM panels WHERE custom_status = 0 AND status = 1 ORDER BY panel_rated_power_w ASC";
		$query = $connection->query($sql);
		while($item = $query->fetch_object()){
			$panels[] = (Object)array(
				'peak_voltage'				=>	$item->peak_voltage,
				'open_circuit_voltage'		=>	$item->open_circuit_voltage,
				'panel_rated_power_w'		=>	$item->panel_rated_power_w,
				'panel_id'				=>	$item->product_id,
				'panel_model'				=>	$item->panel_model,
				'nominal_voltage'			=>	$item->nominal_voltage,
				'short_circuit_current'		=>	$item->short_circuit_current
			);
		}

	}

	foreach($panels as $item){

		// Get the Upper and Lower limits for each Panel
		$lower_limit = $min / $item->peak_voltage;
		$upper_limit = $max * 0.96 / $item->open_circuit_voltage; // 0.96 is to cater for temperature fractuations since VOC is as 25C. A fall in temperature leads to a rise in voltage

		$max_lower_limit = ceil($lower_limit);
		$min_upper_limit = floor($upper_limit);

		// Determine no. of panels in series / string
		$options = array();
		for($i = $max_lower_limit; $i <= $min_upper_limit; $i++){
			$options[] = $i;
		}
		// for($i = $min_upper_limit; $i >= $max_lower_limit; $i--){
		// 	$options[] = $i;
		// }

		// Determine no. of strings
		$strings = array();
		foreach($options as $option){
			$array_motor_fraction = $motor_size / ($option * $item->panel_rated_power_w);
			// Determine total number of strings
			// 1. Determining 1 string
			if($option * $item->panel_rated_power_w >= $motor_size * (1 / $min_efficiency)){
				if($array_motor_fraction >= $max_efficiency && $array_motor_fraction <= $min_efficiency){
					$strings[] = (Object)array(
						'array_motor_fraction'			=> $array_motor_fraction,
						'panel_model' 					=> $item->panel_model,
						'number_of_panels_per_string'		=> $option,
						'total_power'					=> $option * $item->panel_rated_power_w,
						'motor_size'					=> $motor_size,
						'strings'						=> 1
					);
				}
			} else {
				// Number of Strings
				$estimated_number_of_strings = $motor_size * (1 / $min_efficiency) / ($option * $item->panel_rated_power_w);
				$strings_number = ceil($estimated_number_of_strings);
				$array_motor_fraction_new = $motor_size / ($option * $item->panel_rated_power_w * $strings_number);
				if($array_motor_fraction_new >= $max_efficiency && $array_motor_fraction_new <= $min_efficiency){
					$strings[] = (Object)array(
						'array_motor_fraction'			=> $array_motor_fraction_new,
						'panel_model' 					=> $item->panel_model,
						'number_of_panels_per_string'		=> $option,
						'total_power'					=> $option * $item->panel_rated_power_w * $strings_number,
						'motor_size'					=> $motor_size,
						'strings'						=> $strings_number,
						'estimated_strings'				=> $estimated_number_of_strings
					);
				}
			}
		}

		// Get the option that is closest to 0.74
		$list[] = (Object)array(
			'panel_model'				=>	$item->panel_model,
			'panel_rated_power_w'		=>	$item->panel_rated_power_w,
			'peak_voltage'				=>	$item->peak_voltage,
			'nominal_voltage'			=>	$item->nominal_voltage,
			'lower_limit'				=>	$lower_limit,
			'upper_limit'				=>	$upper_limit,
			'max_lower_limit'			=>	$max_lower_limit,
			'min_upper_limit'			=>	$min_upper_limit,
			'motor_size'				=>	$motor_size,
			'panel_id'				=>	$item->panel_id,
			'strings'					=>	$strings,
			'short_circuit_current'		=>	(float)$item->short_circuit_current,
			'open_circuit_voltage'		=>	(float)$item->open_circuit_voltage
		);
	}

	return $list;
}

function getpanelsdc($vmp_pump, $voc_pump, $motor_size, $custom_modules, $cable_length, $connection, $min_panel_uplift, $max_panel_uplift){

	$min_efficiency = $min_panel_uplift;
	$max_efficiency = $max_panel_uplift;

	$list = array();
	$panels = array();

	if(count($custom_modules) > 0){

		$sql = "SELECT * FROM panels WHERE status = 1 AND panel_id IN (" .implode(',', $custom_modules). ") AND status = 1 ORDER BY panel_rated_power_w ASC";
		$query = $connection->query($sql);
		while($item = $query->fetch_object()){
			$panels[] = (Object)array(
				'vmp_panel'					=>	$item->peak_voltage,
				'voc_panel'					=>	$item->open_circuit_voltage,
				'panel_rated_power_w'			=>	$item->panel_rated_power_w,
				'panel_id'					=>	$item->product_id,
				'panel_model'					=>	$item->panel_model,
				'nominal_voltage'				=>	$item->nominal_voltage,
				'short_circuit_current'			=>	$item->short_circuit_current
			);
		}

	} else {

		$sql = "SELECT * FROM panels WHERE custom_status = 0 AND status = 1 ORDER BY panel_rated_power_w ASC";
		$query = $connection->query($sql);
		while($item = $query->fetch_object()){
			$panels[] = (Object)array(
				'vmp_panel'					=>	$item->peak_voltage,
				'voc_panel'					=>	$item->open_circuit_voltage,
				'panel_rated_power_w'			=>	$item->panel_rated_power_w,
				'panel_id'					=>	$item->product_id,
				'panel_model'					=>	$item->panel_model,
				'nominal_voltage'				=>	$item->nominal_voltage,
				'short_circuit_current'			=>	$item->short_circuit_current
			);
		}

	}

	foreach($panels as $item){

		// Get the Upper and Lower limits for each Panel
		$lower_limit = $vmp_pump / $item->vmp_panel;
		$upper_limit = $voc_pump / $item->voc_panel;

		$max_lower_limit = ceil($lower_limit);
		$min_upper_limit = floor($upper_limit);

		// // Determine number of panels in series / a string
		$options = array();
		for($i = $max_lower_limit; $i <= $min_upper_limit; $i++){
			$options[] = $i;
		}

		// // Determine number of strings
		$strings = array();
		foreach($options as $option){

			$array_motor_uplift = $option * $item->panel_rated_power_w / $motor_size;
			// Determine total number of strings
			// 1. Determining 1 string
			if($option * $item->panel_rated_power_w >= $motor_size * $min_efficiency){
				if($array_motor_uplift >= $min_efficiency && $array_motor_uplift <= $max_efficiency){
					$strings[] = (Object)array(
						'array_motor_uplift'			=> $array_motor_uplift,
						'panel_model' 					=> $item->panel_model,
						'number_of_panels_per_string'		=> $option,
						'total_power'					=> $option * $item->panel_rated_power_w,
						'motor_size'					=> $motor_size,
						'strings'						=> 1,
						'cable'						=> getdccable($cable_length, $option * $item->panel_rated_power_w, $option, $item->vmp_panel, $connection),
						'peak_voltage'					=> $item->vmp_panel
					);
				}
			} else {

				// Number of Strings
				$estimated_number_of_strings = $motor_size * $min_efficiency / ($option * $item->panel_rated_power_w);
				$strings_number = ceil($estimated_number_of_strings);

				$array_motor_uplift_new = $option * $item->panel_rated_power_w * $strings_number / $motor_size;
				if($array_motor_uplift_new >= $min_efficiency && $array_motor_uplift_new <= $max_efficiency){
					$strings[] = (Object)array(
						'array_motor_uplift'			=> $array_motor_uplift_new,
						'panel_model' 					=> $item->panel_model,
						'number_of_panels_per_string'		=> $option,
						'total_power'					=> $option * $item->panel_rated_power_w * $strings_number,
						'motor_size'					=> $motor_size,
						'strings'						=> $strings_number,
						'estimated_strings'				=> $estimated_number_of_strings,
						'cable'						=> getdccable($cable_length, $option * $item->panel_rated_power_w, $option, $item->vmp_panel, $connection),
						'peak_voltage'					=> $item->vmp_panel
					);
				}

			}
		}


		if(count($strings) > 0){
			$list[] = (Object)array(
				'panel_model'				=>	$item->panel_model,
				'panel_rated_power_w'		=>	$item->panel_rated_power_w,
				'vmp_panel'				=>	$item->vmp_panel,
				'voc_panel'				=>	$item->voc_panel,
				'nominal_voltage'			=>	$item->nominal_voltage,
				'lower_limit'				=>	$lower_limit,
				'upper_limit'				=>	$upper_limit,
				'max_lower_limit'			=>	$max_lower_limit,
				'min_upper_limit'			=>	$min_upper_limit,
				'motor_size'				=>	$motor_size,
				'panel_id'				=>	$item->panel_id,
				'strings'					=>	$strings,
				'panels'					=>	$options,
				'short_circuit_current'		=>	(float)$item->short_circuit_current,
				'open_circuit_voltage'		=>	(float)$item->voc_panel
			);
		}

	}

	return $list;

}

function getdccable($cable_length, $total_power, $panels, $vmp, $connection){

	$cross_section = 1.11533 * $cable_length * $total_power / pow($panels * $vmp, 2);
	$query = $connection->query("SELECT * FROM dc_cable_sizes WHERE cross_section >= " .$cross_section. " ORDER BY cross_section ASC LIMIT 1");

	$cable = $query->fetch_object();
	return (float)$cable->cross_section;

}

function getproperhead($curve, $flow_rate, $tdh){

	$flow_rate = (float)$flow_rate;
	$tdh = (float)$tdh;
	$coefficients = explode('|', $curve);
	$powers = count($coefficients);

	$head = 0;
	for($j = $powers; $j > 0; $j--){
		$head += (float)$coefficients[($powers - $j)] * pow($flow_rate, ($j - 1));
	}

	if($tdh > $head){

		return (Object)array(
			'pump_curve_head'		=>	$head,
			'total_dynamic_head'	=>	$tdh,
			'flow_rate'			=>	$flow_rate,
			'status'				=>	false
		);

	} else {

		$appropriate = 100 - ((($head - $tdh) / $head) * 100);
		return (Object)array(
			'pump_curve_head'		=>	$head,
			'total_dynamic_head'	=>	$tdh,
			'flow_rate'			=>	$flow_rate,
			'appropriate'			=>	round($appropriate, 2),
			'status'				=>	true
		);
	}
	
}

function gethead($curve, $flow_rate){

	$flow_rate = (float)$flow_rate;
	$coefficients = explode('|', $curve);
	$powers = count($coefficients);

	$head = 0;
	for($j = $powers; $j > 0; $j--){
		$head += (float)$coefficients[($powers - $j)] * pow($flow_rate, ($j - 1));
	}

	return $head;
	
}

function systemflow($curve, $head, $min_flow_rate, $max_flow_rate){

	$head = (float)$head;
	$coefficients = explode('|', $curve);
	$powers = count($coefficients);

	$flow = 0;
	for($j = $powers; $j > 0; $j--){
		$flow += (float)$coefficients[($powers - $j)] * pow($head, ($j - 1));
	}

	if($flow < $min_flow_rate || $flow > $max_flow_rate){
		$status = false;
	} else {
		$status = true;
	}

	$system_flow = (Object)array(
		'status'	=>	$status,
		'flow'	=>	$flow
	);

	return $system_flow;

}

// Find Flow from Head
// function 

function getefficiency($curve, $flow_rate){

	$flow_rate = (float)$flow_rate;
	$coefficients = explode('|', $curve);
	$powers = count($coefficients);

	$efficiency = 0;
	for($j = $powers; $j > 0; $j--){
		$efficiency += (float)$coefficients[($powers - $j)] * pow($flow_rate, ($j - 1));
	}

	return (Object)array(
		'rounded'	=>	round($efficiency, 2),
		'precise'	=>	$efficiency
	);

}

function getefficiencydetails($curve, $flow_rate){

	$flow_rate = (float)$flow_rate;
	$coefficients = explode('|', $curve);
	$powers = count($coefficients);

	$efficiency = 0;
	for($j = $powers; $j > 0; $j--){
		$efficiency += (float)$coefficients[($powers - $j)] * pow($flow_rate, ($j - 1));
	}

	return $efficiency;

}

function getq($tdh, $flow){

	$q = 1.5269 * pow($flow, 1.85) / $tdh;
	return $q;

}

function getsystemhead($d, $flow){

	$h = 1.5269 * pow($flow, 1.85) / $d;
	return $h;

}

function curveequation($curve){

	$coefficients = explode('|', $curve);
	$powers = count($coefficients);

	$equation = '';
	for($j = $powers; $j > 0; $j--){
		$equation .= ' ' .$coefficients[($powers - $j)] . 'Q' .($j - 1);
	}

	return $equation;

}

function systemequation($d, $flow){

	$coefficient = 1.5269 / $d;
	$equation = $coefficient . 'Q' . '1.85';
	return $equation;

}

function getcurvedetails($equipment_id, $pump_tdh, $pump_flow, $connection){

	$query = $connection->query("SELECT * FROM equipment WHERE equipment_id = " .(int)$equipment_id. "");
	$details = $query->fetch_object();
	// Use 10 Points to Draw the curve
	$pumpcurve = array();
	$efficiencycurve = array();
	$systemcurve = array();

	$curve_differences = array();
	$max_system_flow_rate = $details->max_flow_rate * 1.05;

	// Get Max Head at Max Flow
	$max_head_curve = gethead($details->curve, $max_system_flow_rate);
	if($max_head_curve < 0){
		$max_flow_rate = $details->max_flow_rate * 1;
	} else {
		$max_flow_rate = $max_system_flow_rate;
	}

	// Get System Curve
	$q = getq($pump_tdh, $pump_flow);
	for($j = 0; $j <= 10; $j++){

		$flow = $max_flow_rate * $j / 10;
		$flow = round($flow, 4);
		$head = gethead($details->curve, $flow);
		$efficiency = getefficiencydetails($details->efficiency, $flow);
		$h = getsystemhead($q, $flow);
		if($efficiency < 0){
			$efficiency = 0;
		}

		$pumpcurve[] = array(
			'flow_rate'		=>	$flow,
			'head'			=>	round($head, 2)
		);
		$efficiencycurve[] = array(
			'flow_rate'		=>	$flow,
			'efficiency'		=>	round($efficiency, 2)
		);

	}

	// Loop through a 50000 points and return the values
	for($m = 0; $m < 50000; $m++){

		$flow = $max_flow_rate * $m / 50000;
		$flow = round($flow, 4);
		$head = gethead($details->curve, $flow);
		$h = getsystemhead($q, $flow);
		$difference_h = $head - $h;

		if($difference_h < 0){				
			$curve_differences[] = array(
				'head'			=>	round($head, 3),
				'system_head'		=>	$h,
				'difference_h'		=>	($difference_h * -1),
				'flow'			=>	round($flow, 3)
			);
		} else {				
			$curve_differences[] = array(
				'head'			=>	round($head, 3),
				'system_head'		=>	$h,
				'difference_h'		=>	$difference_h,
				'flow'			=>	round($flow, 3)
			);
		}

	}

	$head_differences = array_column($curve_differences, 'difference_h');
	array_multisort($head_differences, SORT_ASC, $curve_differences);
	$leastpoint = array_slice($curve_differences, 0, 1);

	// Get the System Curve
	for($j = 0; $j <= 10; $j++){

		$flow = $leastpoint[0]['flow'] * $j / 10;
		$flow = round($flow, 4);
		$h = getsystemhead($q, $flow);
		$systemcurve[] = array(
			'flow_rate'		=>	$flow,
			'system_head'		=>	round($h, 2)
		);
	}

	$duty = array();
	$duty[] = (Object)array(
		'flow_rate'		=>	(float)round($pump_flow, 2),
		'pump_tdh'		=>	(float)round($pump_tdh, 2)
	);

	$efficiency_points = array();

	// Get efficiency at least point
	$duty_efficiency = getefficiencydetails($details->efficiency, $leastpoint[0]['flow']);
	$efficiency_points[] = (Object)array(
		'flow_rate'		=>	$leastpoint[0]['flow'],
		'efficiency'		=>	round($duty_efficiency, 3)
	);

	$curve = (Object)array(
		'efficiency'				=>	$efficiency_points[0]->efficiency,
		'flow_rate'				=>	$efficiency_points[0]->flow_rate,
		'system_head'				=>	$leastpoint[0]['system_head']
	);

	return $curve;
}

function sendmail($email, $fullname, $origin){

	// Send Email
	require('libs/PHPMailer/PHPMailerAutoload.php');
	$mail = new PHPMailer;

	$mail->SMTPDebug = 0;
	$mail->SMTPDebug = 0;
	$mail->CharSet = 'UTF-8';
	$mail->isSMTP();
	$mail->SMTPAuth = true;
	$mail->Host = 'premium50.web-hosting.com';
	$mail->Port = 465;
	$mail->Username = 'info@davisandshirtliff.com';
	$mail->Password = 'EzLxd.EY3}rGTCXO$';
	$mail->SMTPSecure = 'ssl';
	
	$mail->setFrom('info@davisandshirtliff.com', 'SolarCalc');
	$mail->addAddress($email, $fullname);
	$mail->AddCC('ContactCenter@dayliff.com', 'Contact Center');
	$mail->AddCC('Arnold.Rotich@dayliff.com', 'Arnold Rotich');
	$mail->addBCC('KMuturi@dayliff.com');

	$mail->addReplyTo('Arnold.Rotich@dayliff.com', 'SolarCalc');
	$mail->isHTML(true);
	$mail->Subject = 'New User Registration';
	$mail->AddEmbeddedImage('img/welcome-email.jpg', 'welcomeemail', 'welcomeemail.jpg');

	$mail->Body = '<!DOCTYPE html><html><head><title>New User Registration</title></head><body style="font-size: 12px; font-family: arial; color: #555555"><table border="0" style="width: 700px; margin: 0px auto; border-collapse: collapse; border: solid 1px #888888"><tr><td style="padding: 0px 0px; border-bottom: solid 1px #888888" colspan="2"><img src="cid:welcomeemail" alt="Welcome New User" /></td></tr><tr><td style="padding:40px 10px 20px 10px;" colspan="2"><p style="padding: 0; margin: 0">Dear <strong>' .$fullname. '</strong>, welcome to the <strong>Dayliff SolarCalc</strong>. The amazing calculator for the Dayliff range of pumps.</p><p>The SolarCalc allows for easy and quick sizing of Solar Pumps. With basic input parameters specified, the SolarCalc specifies the required solutions including the Pump, Inverter and Solar panels required based on the Solar Irradiation of a location.</p><p>The specified solution can then be printed and shared with the client. The project can also be saved for later retrieval.</p>Try it today</td></tr><tr><td style="padding: 20px 10px 40px 10px;" colspan="2"><p style="padding: 0; margin: 0">Kind regards<br/><strong>DAYLIFF Contact Center</strong></p></td></tr><tr style="background: #444;"><td style="padding: 20px 10px; background: #eb1820; color: #ffffff" colspan="2"><p style="padding: 0; margin: 0">Industrial Area, Dundori Road<br/>Nairobi<br/>P.O. Box: 41762-00100<br/>Kenya<br/>+254 711 079 200<br/>contactcenter@dayliff.com</p></td></tr></table></body></html>';
	$mail->send();

}

function getofflineirradiation($coordinates, $connection){
	
	// Point X1Y1
	$query_x0 = $connection->query("SELECT lat, lng FROM irradiation WHERE lat < " .(float)$coordinates->lat. " AND lng < " .(float)$coordinates->lng. " ORDER BY lat DESC, lng DESC LIMIT 1");
	$x1y1 = $query_x0->fetch_object();

	// Point X2Y2
	$query_x1 = $connection->query("SELECT lat, lng FROM irradiation WHERE lat > " .(float)$coordinates->lat. " AND lng > " .(float)$coordinates->lng. " ORDER BY lat ASC, lng ASC LIMIT 1");
	$x2y2 = $query_x1->fetch_object();


	// Closest Latitude
	$diff_lat_x0 = abs($coordinates->lat - (float)$x1y1->lat);
	$diff_lat_x1 = abs($coordinates->lat - (float)$x2y2->lat);

	if($diff_lat_x0 < $diff_lat_x1){
		$lat = $x1y1->lat;
	} else {
		$lat = $x2y2->lat;		
	}

	// Closest Longitude
	$diff_lng_y0 = abs($coordinates->lng - (float)$x1y1->lng);
	$diff_lng_y1 = abs($coordinates->lng - (float)$x2y2->lng);

	if($diff_lng_y0 < $diff_lng_y1){
		$lng = $x1y1->lng;
	} else {
		$lng = $x2y2->lng;		
	}

	$closest = (Object)array(
		'lat'	=>	(float)$lat,
		'lng'	=>	(float)$lng
	);

	$differences = (Object)array(
		'diff_lat_x0'	=>	$diff_lat_x0,
		'diff_lat_x1'	=>	$diff_lat_x1,
		'diff_lng_y0'	=>	$diff_lng_y0,
		'diff_lng_y1'	=>	$diff_lng_y1
	);

	$query = $connection->query("SELECT * FROM irradiation WHERE lat = " .(float)$closest->lat. " AND lng = " .(float)$closest->lng. "");
	$item = $query->fetch_object();

	$dnr = (Object)array(
		'DNR'	=>	(Object)array(
			'1'	=>	(float)$item->jan_rad,
			'2'	=>	(float)$item->feb_rad,
			'3'	=>	(float)$item->mar_rad,
			'4'	=>	(float)$item->apr_rad,
			'5'	=>	(float)$item->may_rad,
			'6'	=>	(float)$item->jun_rad,
			'7'	=>	(float)$item->jul_rad,
			'8'	=>	(float)$item->aug_rad,
			'9'	=>	(float)$item->sep_rad,
			'10'	=>	(float)$item->oct_rad,
			'11'	=>	(float)$item->nov_rad,
			'12'	=>	(float)$item->dec_rad,
			'13'	=>	(float)$item->avg_rad
		)
	);

	$points = (Object)array(
		'coordinates'	=>	$coordinates,
		'differences'	=>	$differences,
		'x1y1'		=>	$x1y1,
		// 'y0'			=>	$y0,
		'x2y2'		=>	$x2y2,
		'closest'		=>	$closest,
		'item'		=>	$dnr
	);

	return $points;

}

function getdailyirratiation($closest, $coordinates, $connection){

	$query = $connection->query("SELECT * FROM daily_irradiation WHERE lat = " .(float)$closest->lat. " AND lng = " .(float)$closest->lng. " AND status = 1 ORDER By time_parameter ASC");

	$gmttime = getgmttimedifference($coordinates);

	$irradiation = array();
	$points = 0;

	while($item = $query->fetch_object()){

		// Get the Average Irradition for this time of the day over the 12 months excluding the Average Irradiation
		$sum_irradition = array((float)$item->jan_rad, (float)$item->feb_rad, (float)$item->mar_rad, (float)$item->apr_rad, (float)$item->may_rad, (float)$item->jun_rad, (float)$item->jul_rad, (float)$item->aug_rad, (float)$item->sep_rad, (float)$item->oct_rad, (float)$item->nov_rad, (float)$item->dec_rad);

		$avg_rad = array_sum($sum_irradition) / 12;
		$real_time = str_replace('ALLSKY_SFC_SW_DWN_', '', strtoupper($item->time_parameter));
		$real_time = str_replace('_GMT', '', strtoupper($real_time));

		$time = floor((int)$real_time + $gmttime->timediff);

		if($avg_rad > 0){

			$irradiation[] = (object)array(
				// 'item'				=>	$item,
				'avg_rad' 			=>	$avg_rad > 0 ? round($avg_rad, 2) : 0,
				'real_gmt_time_string'	=>	$item->time_parameter,
				'real_gmt_time'		=>	(int)$real_time,
				'real_time'			=>	$time > 24 ? ($time - 24) : $time,
				'irradiation_id'		=>	$item->daily_irradition_id,
				'gmttime'				=>	$gmttime
			);

		}

	}

	$times = array_column($irradiation, 'real_time');
	array_multisort($times, SORT_ASC, $irradiation);

	$mapped = array();

	// Add additional Points
	foreach($irradiation as $item){

		$mapped[] = (object)array(
			'avg_rad'		=>	$item->avg_rad,
			'real_time'	=>	str_pad($item->real_time, 2, '0', STR_PAD_LEFT). '00Hrs',
		);

		if($points != (count($irradiation) - 1)){

			// Add two points between each 2 points already mapped
			$diff = $irradiation[($points + 1)]->real_time - $irradiation[$points]->real_time;
			$rad_diff = $irradiation[($points + 1)]->avg_rad - $irradiation[$points]->avg_rad;

			$point1 = $item->real_time + ($diff / 3);
			$avg_rad1 = $item->avg_rad + ($rad_diff / 3);
			// $avg_rad1 = getpointbellpoint($point1);

			$mapped[] = (object)array(
				'avg_rad' 	=>	round($avg_rad1, 2),
				'real_time'	=>	str_pad($point1, 2, '0', STR_PAD_LEFT). '00Hrs'
			);

			$point2 = $item->real_time + ($diff * 2 / 3);
			$avg_rad2 = $item->avg_rad + ($rad_diff * 2 / 3);
			// $avg_rad2 = getpointbellpoint($point2);

			$mapped[] = (object)array(
				'avg_rad' 	=>	round($avg_rad2, 2),
				'real_time'	=>	str_pad($point2, 2, '0', STR_PAD_LEFT). '00Hrs'
			);

		}
		$points++;


	}

	return $mapped;

}

function getpointbellpoint($x){

	// Equation = y = 0.3659x4 - 17.791x3 + 291.93x2 - 1840.2x + 3927.6
	$y = (0.3659 * pow($x, 4)) + (-17.791 * pow($x, 3)) + (291.93 * pow($x, 2)) + (-1840.2 * pow($x, 1)) + (3927.6 * pow($x, 0));
	return $y;

}

function getgmttimedifference($coordinates){

	$curl = curl_init();
	curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://maps.googleapis.com/maps/api/timezone/json?location=' .$coordinates->lat. ','. $coordinates->lng. '&timestamp=' .time(). '&key=AIzaSyDc7DcO9HEL0__epR4GTePnExXkyfduo58',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_POSTFIELDS => '',
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_HTTPHEADER => array(
			'cache-control: no-cache'
		),
	));

	$response = json_decode(curl_exec($curl));
	$timediff = $response->rawOffset / 3600;
	$err = curl_error($curl);
	curl_close($curl);

	$result = (object)array(
		'timediff'	=>	$timediff,
		'timezone'	=>	$response->timeZoneName,
		'timezoneid'	=>	$response->timeZoneId
	);
	return $result;

}

function getsunflotemplate($project, $company, $solution, $irradiation, $output, $url, $date, $data){

	// QR Code
	$qr_path = HOST_LINK . 'img/QR-Code-Generated.jpg';
	$qr_type = pathinfo($qr_path, PATHINFO_EXTENSION);
	$rl_data = file_get_contents($qr_path);
	$qr = 'data:image/' .$qr_type. ';base64,' .base64_encode($rl_data);

	$html = '<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<title></title>
	<style type="text/css">
		*{
			font-family: "quotefont";
			font-size: 12px;
		}
		body{
			margin: 10px;
			}
		@font-face {
			font-family: "quotefont";
			src:url("libs/dompdf/lib/fonts/OpenSans-Regular.ttf") format("truetype");
			font-weight: normal
		}@font-face {
			font-family: "quotefont";
			src:url("libs/dompdf/lib/fonts/OpenSans-Bold.ttf") format("truetype");
			font-weight: bold
		}@page{
			margin: 30px;
		}
		.products-container img{
			max-width: 100%;
			height: auto;
		}
		.products-container table{
			width: 100%;
			border-right: solid 1px #ddd;
			border-bottom: solid 1px #ddd;
		}
		.products-container table td{
			border-top: solid 1px #ddd;
			border-left: solid 1px #ddd;
			font-size: 10px;
			padding: 1px;
			text-align: center;
		}
		.products-container table .highlight-row{
			background: #ddd;
		}

		.irradiation-image, .output-image{
			text-align: center;
			padding: 10px;
		}
		.irradiation-image img, .output-image img{
			width : 94%;
			height: auto;
		}
	</style>
</head>
<body>
	<div style="border: solid 1px #cccccc; padding: 10px;">
		<table border="0" cellpadding="0" cellspacing="0" style="width: 100%; margin-top: 10px;">
			<tr>
				<td width="60%">
					<img src="img/logos/SolarCalc-Report-Logo.png" alt="SolarCalc Solar Sizing">
				</td>
				<td style="width: 40%; text-align: right;">
					<img src="img/logos/Report-Logo.jpg" class="img-fluid company-logo" alt="' .$company->company_name. '">
					<ul>
						<li style="list-style-type: none;"><strong>' .$company->company_name. '</strong></li>
						<li style="list-style-type: none;">' .$company->physical_location. '</li>
						<li style="list-style-type: none;">' .$company->postal_address. '</li>
						<li style="list-style-type: none;">' .$company->company_phone. '</li>
						<li style="list-style-type: none;">' .$company->company_email. '</li>
						<li style="list-style-type: none;">' .$company->company_website. '</li>
					</ul>
				</td>
			</tr>
		</table>
		<div style="border-bottom: solid 1px #eee; background: none; margin-bottom: 5px;"></div>
		<table border="0" cellpadding="0" cellspacing="0" style="width: 100%">
			<tr>
				<td>
					<ul style="display: block; margin: 0; padding: 5px 0;">
						<li style="list-style-type: none; margin: 0;">' .$date. '</li>
						<li style="list-style-type: none; margin: 0;">' .$project->project_name. '</li>
						<li style="list-style-type: none; margin: 0;">' .$project->customer->customer_name. '</li>
						<li style="list-style-type: none; margin: 0;">' .$project->location_name. '</li>
						<li style="list-style-type: none; margin: 0;">' .$project->customer->customer_email. '</li>
						<li style="list-style-type: none; margin: 0;">' .$project->customer->customer_telephone. '</li>
						<li style="list-style-type: none; margin: 0;">' .$project->project_details->project_notes. '</li>
					</ul>
				</td>
			</tr>
		</table>
		<div style="border-bottom: solid 1px #eee; background: none; margin-bottom: 10px; margin-top: 15px;"></div>
		<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; border: solid 1px #eee; border-color: #f1f1f1">
			<tr>
				<td colspan="3">
					<h3 style="padding: 10px 0; margin: 0">Paramaters</h3>
				</td>
			</tr>
			<tr>
				<td colspan="3">Project Location - ' .$project->project_details->sizing['location_name']. '(' .$project->project_details->sizing['latitude_info']. ', ' .$project->project_details->sizing['longitude_info']. ')</td>
			</tr>
			<tr>
				<td>Head - ' .$project->project_details->tdh. '</td>
				<td colspan="2">Pipe Length - ' .$project->project_details->pipe_length_details. '</td>
			</tr>
			<tr>
				<td style="width: 40%">
					<strong>Product</strong>
				</td>
				<td style="width: 10%; text-align: center;">
					<strong>Quantity</strong>
				</td>
				<td style="width: 50%">
					<strong>Details</strong>
				</td>
			</tr>
			<tr>
				<td style="width: 40%"><strong>Pump</strong> - ' .$solution[0]. '</td>
				<td style="width: 10%; text-align: center;">1</td>
				<td style="width: 50%"></td>
			</tr>
			<tr>
				<td style="width: 40%"><strong>Panels</strong> - ' .$solution[2]. '</td>
				<td style="width: 10%; text-align: center;">' .$solution[4]. '</td>
				<td style="width: 50%"></td>
			</tr>
			<tr>
				<td colspan="3">
					<h3 style="padding: 10px 0; margin: 0">Daily output in average month - ' .$project->project_details->delivery_output. '</h3>
				</td>
			</tr>
		</table>
		<h2 style="padding: 15px 0; font-size: 15px; margin: 15px 0 0 0;">Monthly Irradiation Data</h2>
		<div class="irradiation-image"><img src="' .$data->irradiation_img. '" /></div>
		<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; border: solid 1px #eee; border-color: #f1f1f1">
			<tr>
				<td rowspan="2" style="width: 20%; text-align: center;"><strong>Irradiation [kWh/m²]</strong></td>
				<td style="text-align: center;">Jan</td>
				<td style="text-align: center;">Feb</td>
				<td style="text-align: center;">Mar</td>
				<td style="text-align: center;">Apr</td>
				<td style="text-align: center;">May</td>
				<td style="text-align: center;">Jun</td>
				<td style="text-align: center;">Jul</td>
				<td style="text-align: center;">Aug</td>
				<td style="text-align: center;">Sep</td>
				<td style="text-align: center;">Oct</td>
				<td style="text-align: center;">Nov</td>
				<td style="text-align: center;">Dec</td>
				<td style="text-align: center;">Avg</td>
			</tr>
			<tr>';

			foreach($irradiation as $item){
				$html .= '<td style="text-align: center;">' .$item. '</td>';
			}

			$html .= '</tr>
		</table>
		<h2 style="padding: 15px 0; font-size: 15px; margin: 15px 0 0 0;">Monthly Output Data</h2>
		<div class="output-image"><img src="' .$data->output_img. '" /></div>
		<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; border: solid 1px #eee; border-color: #f1f1f1">
			<tr>
				<td rowspan="2" style="width: 20%; text-align: center;"><strong>Output [m³/day]</strong></td>
				<td style="text-align: center;">Jan</td>
				<td style="text-align: center;">Feb</td>
				<td style="text-align: center;">Mar</td>
				<td style="text-align: center;">Apr</td>
				<td style="text-align: center;">May</td>
				<td style="text-align: center;">Jun</td>
				<td style="text-align: center;">Jul</td>
				<td style="text-align: center;">Aug</td>
				<td style="text-align: center;">Sep</td>
				<td style="text-align: center;">Oct</td>
				<td style="text-align: center;">Nov</td>
				<td style="text-align: center;">Dec</td>
				<td style="text-align: center;">Avg</td>
			</tr>
			<tr>';

			foreach($output as $item){
				$html .= '<td style="text-align: center;">' .$item. '</td>';
			}

			$html .= '</tr>
		</table>
		<div class="products-container">' .$data->pump_content. '</div>
		<div class="products-container">' .$data->panels_content. '</div>
		<table border="0" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; border: solid 1px #f1f1f1; border-color: #f1f1f1" bordercolor="#f1f1f1">
			<tr>
				<td style="text-align: center; width: 70%">
				</td>
				<td style="text-align: center">
					<img src="' .$qr. '" ><br/>
					Scan with the Dayliff App
				</td>
			</tr>
		</table>
	</div>
</body>
</html>';

	return $html;

}

function gettemplate($project, $company, $solution, $irradiation, $output, $url, $date, $data, $pv_disconnect){

	// QR Code
	$qr_path = HOST_LINK . 'img/QR-Code-Generated.jpg';
	$qr_type = pathinfo($qr_path, PATHINFO_EXTENSION);
	$rl_data = file_get_contents($qr_path);
	$qr = 'data:image/' .$qr_type. ';base64,' .base64_encode($rl_data);

	$sizing = $project->project_details->sizing;
	$html = '<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<title></title>
	<style type="text/css">
		*{
			font-family: "quotefont";
			font-size: 12px;
		}
		body{
			margin: 0px;
			}
		@font-face {
			font-family: "quotefont";
			src:url("libs/dompdf/lib/fonts/OpenSans-Regular.ttf") format("truetype");
			font-weight: normal
		}@font-face {
			font-family: "quotefont";
			src:url("libs/dompdf/lib/fonts/OpenSans-Bold.ttf") format("truetype");
			font-weight: bold
		}@page{
			margin: 0px;
		}
		.products-container img{
			max-width: 100%;
			height: auto;
		}
		.products-container table{
			width: 100%;
			border-right: solid 1px #ddd;
			border-bottom: solid 1px #ddd;
		}
		.products-container table td{
			border-top: solid 1px #ddd;
			border-left: solid 1px #ddd;
			font-size: 10px;
			padding: 1px;
			text-align: center;
		}
		.products-container table .highlight-row{
			background: #ddd;
		}

		.irradiation-image, .output-image{
			text-align: center;
			padding: 10px;
		}
		.irradiation-image img, .output-image img{
			width : 94%;
			height: auto;
		}
	</style>
</head>
<body>
	<div style="">
		<img src="img/logos/Header.jpg" style="width: 100%; height: auto" alt="SolarCalc Solar Sizing">
	</div>
	<div style="padding-left: 50px; padding-right: 50px;">
		<table border="0" cellpadding="0" cellspacing="0" style="width: 100%; font-size: 14px; font-weight: bold;">
			<tr>
				<td style="width: 100%; text-align: right;">
					<ul>
						<li style="list-style-type: none;">{REFNUM}</li>
						<li style="list-style-type: none;">' .date('D, d-M-y'). '</li>
					</ul>
				</td>
			</tr>
			<tr>
				<td style="width: 100%; text-align: left;">
					<ul>
						<li style="list-style-type: none; margin: 0;">' .$date. '</li>
						<li style="list-style-type: none; margin: 0;">' .$project->project_name. '</li>
						<li style="list-style-type: none; margin: 0;">' .$project->customer->customer_name. '</li>
						<li style="list-style-type: none; margin: 0;">' .$project->location_name. '</li>
						<li style="list-style-type: none; margin: 0;">' .$project->customer->customer_email. '</li>
						<li style="list-style-type: none; margin: 0;">' .$project->customer->customer_telephone. '</li>
						<li style="list-style-type: none; margin: 0;">' .$project->project_details->project_notes. '</li>
					</ul>
				</td>
			</tr>
		</table>
		<div style="border-bottom: solid 1px #eee; background: none; margin-bottom: 5px;"></div>
		<div style="border-bottom: solid 1px #eee; background: none; margin-bottom: 10px; margin-top: 15px;"></div>
		<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; border: solid 1px #eee; border-color: #f1f1f1">
			<tr>
				<td colspan="10">
					<h3 style="padding: 10px 0; margin: 0">Paramaters</h3>
				</td>
			</tr>
			<tr>
				<td>Location</td>
				<td colspan="9">' .$project->location_name. '(' .$project->project_details->sizing['latitude_info']. ', ' .$project->project_details->sizing['longitude_info']. ')</td>
			</tr>
			<tr>
				<td>Required Daily Output</td>
				<td>' .$project->project_details->delivery_output. '</td>
				<td>Pipe Type</td>
				<td>' .$project->project_details->pipe_details. '</td>
				<td>Motor Cable</td>
				<td>' .$project->project_details->sizing['cable_length']. 'm</td>
				<td>Pipe Length & Inner Diameter</td>
				<td>' .$project->project_details->pipe_length_details. ', ' .$project->project_details->sizing['inner_diameter']. '"</td>
				<td>Head (TDH)</td>
				<td>' .$project->project_details->tdh. '</td>
			</tr>
		</table>
		<br/>';

		if(isset($sizing['add_site_borehole_conditions']) && $sizing['add_site_borehole_conditions'] == 'on'){
			$html .= '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; border: solid 1px #eee; border-color: #f1f1f1">
					<tr>
						<td colspan="8">
							<h3>Borehole & Site Conditions</h3>
						</td>
					</tr>
					<tr>
						<td>Min. Borehole Diameter ("):</td>
						<td>' .$sizing['minimum_borehole_diameter']. '</td>
						<td>Static Water Level (m):</td>
						<td>' .$sizing['static_water_level']. '</td>
						<td>Borehole Tested Yield (m):</td>
						<td>' .$sizing['borehole_tested_yield']. '</td>
						<td>Pump Inlet Depth (m):</td>
						<td>' .$sizing['pump_inlet_depth']. '</td>
					</tr>
					<tr>
						<td>Main Aquifer (m):</td>
						<td>' .$sizing['main_aquifer']. '</td>
						<td>Distance to Solar Array (m):</td>
						<td>' .$sizing['distance_to_solar_array']. '</td>
						<td>Distance to Delivery Point (m):</td>
						<td colspan="3">' .$sizing['distance_to_delivery_point']. '</td>
					</tr>
				</table>
				<br/>';
		}

		$html .= '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; border: solid 1px #eee; border-color: #f1f1f1">
			<tr>
				<td style="width: 40%">
					<strong>Product</strong>
				</td>
				<td style="width: 10%; text-align: center;">
					<strong>Quantity</strong>
				</td>
				<td style="width: 50%">
					<strong>Details</strong>
				</td>
			</tr>
			<tr>
				<td style="width: 40%"><strong>Pump</strong> - ' .$solution[0]. '</td>
				<td style="width: 10%; text-align: center;">1</td>
				<td style="width: 50%">Suitability <strong>' .$solution[9]. '</strong>%, Efficiency <strong>' .$solution[10]. '</strong>%</td>
			</tr>
			<tr>
				<td style="width: 40%"><strong>Inverter</strong> - ' .$solution[2]. '</td>
				<td style="width: 10%; text-align: center;">1</td>
				<td style="width: 50%"></td>
			</tr>
			<tr>
				<td style="width: 40%"><strong>Panels</strong> - ' .$solution[4]. '</td>
				<td style="width: 10%; text-align: center;">' .$solution[6]. ' x ' .$solution[7]. '</td>
				<td style="width: 50%"><strong>' .$solution[7]. '</strong> string(s) each with <strong>' .$solution[6]. '</strong> Solar panels.</td>
			</tr>
			<tr>
				<td style="width: 40%"><strong>Motor Cable</strong></td>
				<td colspan="2" style="width: 10%; text-align: left;">	Length <strong>' .$project->project_details->sizing['cable_length']. '</strong>, Cross Sectional Area <strong>' .$solution[11]. '</strong></td>
			</tr>
			<tr>
				<td colspan="3" style="width: 10%; text-align: left;">Other Accessories</td>
			</tr>
			<tr>
				<td style="width: 40%"><strong>Water Level Switch / Well Probe</strong></td>
				<td colspan="2" style="width: 10%; text-align: left;">1</td>
			</tr>
			<tr>
				<td style="width: 40%"><strong>Water Level Sensor Cable</strong></td>
				<td colspan="2" style="width: 10%; text-align: left;">2 Core x 1.0mm2, Length - ' .$project->project_details->sizing['cable_length']. '</td>
			</tr>';
	if($pv_disconnect->pv_disconnect->other->quantity > 0){
	$html .= '<tr>
				<td style="width: 40%" rowspan="2"><strong>PV Disconnect</strong></td>
				<td style="width: 10%; text-align: center;">' .$pv_disconnect->pv_count. '</td>
				<td style="text-align: left;">' .$pv_disconnect->pv_disconnect->pv_disconnect_model. '</td>
			</tr>
			<tr>
				<td style="width: 10%; text-align: center;">' .$pv_disconnect->pv_disconnect->other->quantity. '</td>
				<td style="text-align: left;">' .$pv_disconnect->pv_disconnect->other->model. '</td>
			</tr>';
	} else {
	$html .= '<tr>
				<td style="width: 40%"><strong>PV Disconnect</strong></td>
				<td style="width: 10%; text-align: center;">' .$pv_disconnect->pv_count. '</td>
				<td style="text-align: left;">' .$pv_disconnect->pv_disconnect->pv_disconnect_model. '</td>
			</tr>';
	}
	$html .= '<tr>
				<td style="width: 40%"><strong>Earthrod c/w Clamp</strong></td>
				<td colspan="2" style="width: 10%; text-align: left;">1</td>
			</tr>
			<tr>
				<td style="width: 40%"><strong>6mm² DC Cable for Earthrod</strong></td>
				<td colspan="2" style="width: 10%; text-align: left;">(As required)</td>
			</tr>
			<tr>
				<td colspan="3">
					<h3 style="padding: 10px 0; margin: 0">Daily output in average month - ' .$output[(count($output) - 1)]. '</h3>
				</td>
			</tr>
		</table>
		<h2 style="padding: 15px 0; font-size: 15px; margin: 15px 0 0 0;">Monthly Irradiation Data</h2>
		<div class="irradiation-image"><img src="' .$data->irradiation_img. '" /></div>
		<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; border: solid 1px #eee; border-color: #f1f1f1">
			<tr>
				<td rowspan="2" style="width: 20%; text-align: center;"><strong>Irradiation [kWh/m²]</strong></td>
				<td style="text-align: center;">Jan</td>
				<td style="text-align: center;">Feb</td>
				<td style="text-align: center;">Mar</td>
				<td style="text-align: center;">Apr</td>
				<td style="text-align: center;">May</td>
				<td style="text-align: center;">Jun</td>
				<td style="text-align: center;">Jul</td>
				<td style="text-align: center;">Aug</td>
				<td style="text-align: center;">Sep</td>
				<td style="text-align: center;">Oct</td>
				<td style="text-align: center;">Nov</td>
				<td style="text-align: center;">Dec</td>
				<td style="text-align: center;">Avg</td>
			</tr>
			<tr>';
			foreach($irradiation as $item){
				$html .= '<td style="text-align: center;">' .$item. '</td>';
			}
		$html .= '</tr>
		</table>
		<h2 style="padding: 15px 0; font-size: 15px; margin: 15px 0 0 0;">Monthly Output Data</h2>
		<div class="output-image"><img src="' .$data->output_img. '" /></div>
		<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; border: solid 1px #eee; border-color: #f1f1f1">
			<tr>
				<td rowspan="2" style="width: 20%; text-align: center;"><strong>Output [m³/day]</strong></td>
				<td style="text-align: center;">Jan</td>
				<td style="text-align: center;">Feb</td>
				<td style="text-align: center;">Mar</td>
				<td style="text-align: center;">Apr</td>
				<td style="text-align: center;">May</td>
				<td style="text-align: center;">Jun</td>
				<td style="text-align: center;">Jul</td>
				<td style="text-align: center;">Aug</td>
				<td style="text-align: center;">Sep</td>
				<td style="text-align: center;">Oct</td>
				<td style="text-align: center;">Nov</td>
				<td style="text-align: center;">Dec</td>
				<td style="text-align: center;">Avg</td>
			</tr>
			<tr>';

			foreach($output as $item){
				$html .= '<td style="text-align: center;">' .$item. '</td>';
			}

		$html .= '</tr>
		</table>
		<h2 style="padding: 15px 0; font-size: 15px; margin: 15px 0 0 0;">Pump & System Curves</h2>
		<div class="output-image"><img src="' .$data->pump_img. '" /></div>
		<div class="output-image"><img src="' .$data->efficiency_img. '" /></div>
		<div class="products-container">' .$data->pump_content. '</div>
		<div class="products-container">' .$data->inverter_content. '</div>
		<div class="products-container">' .$data->panels_content. '</div>
		<h2 style="padding: 15px 0; font-size: 15px; margin: 15px 0 0 0;">Wiring Diagram</h2>
		<div class="output-image"><img src="img/wiring/' .$project->project_details->wiringdiagram. '.jpg" /></div>
		<p style="text-align: center"><strong>' .$solution[6]. '</strong> panels by <strong>' .$solution[7]. '</strong> string(s)</p>
		<table border="0" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; border: solid 1px #f1f1f1; border-color: #f1f1f1" bordercolor="#f1f1f1">
			<tr>
				<td style="text-align: center; width: 70%">
				</td>
				<td style="text-align: center">
					<img src="' .$qr. '" ><br/>
					Scan with the Dayliff App
				</td>
			</tr>
		</table>
	</div>
</body>
</html>';
	return $html;
}

function gettemplatesurface($project, $company, $solution, $irradiation, $output, $url, $date, $data, $pv_disconnect){

	// QR Code
	$qr_path = HOST_LINK . 'img/QR-Code-Generated.jpg';
	$qr_type = pathinfo($qr_path, PATHINFO_EXTENSION);
	$rl_data = file_get_contents($qr_path);
	$qr = 'data:image/' .$qr_type. ';base64,' .base64_encode($rl_data);
	
	$sizing = $project->project_details->sizing;
	$html = '<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<title></title>
	<style type="text/css">
		*{
			font-family: "quotefont";
			font-size: 12px;
		}
		body{
			margin: 10px;
			}
		@font-face {
			font-family: "quotefont";
			src:url("libs/dompdf/lib/fonts/OpenSans-Regular.ttf") format("truetype");
			font-weight: normal
		}@font-face {
			font-family: "quotefont";
			src:url("libs/dompdf/lib/fonts/OpenSans-Bold.ttf") format("truetype");
			font-weight: bold
		}@page{
			margin: 30px;
		}
		.products-container img{
			max-width: 100%;
			height: auto;
		}
		.products-container table{
			width: 100%;
			border-right: solid 1px #ddd;
			border-bottom: solid 1px #ddd;
		}
		.products-container table td{
			border-top: solid 1px #ddd;
			border-left: solid 1px #ddd;
			font-size: 10px;
			padding: 1px;
			text-align: center;
		}
		.products-container table .highlight-row{
			background: #ddd;
		}

		.irradiation-image, .output-image{
			text-align: center;
			padding: 10px;
		}
		.irradiation-image img, .output-image img{
			width : 94%;
			height: auto;
		}
	</style>
</head>
<body>
	<div style="border: solid 1px #cccccc; padding: 10px;">
		<table border="0" cellpadding="0" cellspacing="0" style="width: 100%; margin-top: 10px;">
			<tr>
				<td width="60%">
					<img src="img/logos/SolarCalc-Report-Logo.png" alt="SolarCalc Solar Sizing">
				</td>
				<td style="width: 40%; text-align: right;">
					<img src="img/logos/Report-Logo.jpg" class="img-fluid company-logo" alt="' .$company->company_name. '">
					<ul>
						<li style="list-style-type: none;"><strong>' .$company->company_name. '</strong></li>
						<li style="list-style-type: none;">' .$company->physical_location. '</li>
						<li style="list-style-type: none;">' .$company->postal_address. '</li>
						<li style="list-style-type: none;">' .$company->company_phone. '</li>
						<li style="list-style-type: none;">' .$company->company_email. '</li>
						<li style="list-style-type: none;">' .$company->company_website. '</li>
					</ul>
				</td>
			</tr>
		</table>
		<div style="border-bottom: solid 1px #eee; background: none; margin-bottom: 5px;"></div>
		<table border="0" cellpadding="0" cellspacing="0" style="width: 100%">
			<tr>
				<td>
					<ul style="display: block; margin: 0; padding: 5px 0;">
						<li style="list-style-type: none; margin: 0;">' .$date. '</li>
						<li style="list-style-type: none; margin: 0;">' .$project->project_name. '</li>
						<li style="list-style-type: none; margin: 0;">' .$project->customer->customer_name. '</li>
						<li style="list-style-type: none; margin: 0;">' .$project->location_name. '</li>
						<li style="list-style-type: none; margin: 0;">' .$project->customer->customer_email. '</li>
						<li style="list-style-type: none; margin: 0;">' .$project->customer->customer_telephone. '</li>
						<li style="list-style-type: none; margin: 0;">' .$project->project_details->project_notes. '</li>
					</ul>
				</td>
			</tr>
		</table>
		<div style="border-bottom: solid 1px #eee; background: none; margin-bottom: 10px; margin-top: 15px;"></div>
		<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; border: solid 1px #eee; border-color: #f1f1f1">
			<tr>
				<td colspan="10">
					<h3 style="padding: 10px 0; margin: 0">Paramaters</h3>
				</td>
			</tr>
			<tr>
				<td>Location</td>
				<td colspan="9">' .$project->location_name. '(' .$project->project_details->sizing['latitude_info']. ', ' .$project->project_details->sizing['longitude_info']. ')</td>
			</tr>
			<tr>
				<td>Required Daily Output</td>
				<td>' .$project->project_details->delivery_output. '</td>
				<td>Pipe Type</td>
				<td>' .$project->project_details->pipe_details. '</td>
				<td>Motor Cable</td>
				<td>' .$project->project_details->sizing['cable_length']. 'm</td>
				<td>Pipe Length & Inner Diameter</td>
				<td>' .$project->project_details->pipe_length_details. ', ' .$project->project_details->sizing['inner_diameter']. '"</td>
				<td>Head (TDH)</td>
				<td>' .$project->project_details->tdh. '</td>
			</tr>
		</table>
		<br/>';

		$html .= '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; border: solid 1px #eee; border-color: #f1f1f1">
			<tr>
				<td style="width: 40%">
					<strong>Product</strong>
				</td>
				<td style="width: 10%; text-align: center;">
					<strong>Quantity</strong>
				</td>
				<td style="width: 50%">
					<strong>Details</strong>
				</td>
			</tr>
			<tr>
				<td style="width: 40%"><strong>Pump</strong> - ' .$solution[0]. '</td>
				<td style="width: 10%; text-align: center;">1</td>
				<td style="width: 50%">Suitability <strong>' .$solution[9]. '</strong>%, Efficiency <strong>' .$solution[10]. '</strong>%</td>
			</tr>
			<tr>
				<td style="width: 40%"><strong>Inverter</strong> - ' .$solution[2]. '</td>
				<td style="width: 10%; text-align: center;">1</td>
				<td style="width: 50%"></td>
			</tr>
			<tr>
				<td style="width: 40%"><strong>Panels</strong> - ' .$solution[4]. '</td>
				<td style="width: 10%; text-align: center;">' .$solution[6]. ' x ' .$solution[7]. '</td>
				<td style="width: 50%"><strong>' .$solution[7]. '</strong> string(s) each with <strong>' .$solution[6]. '</strong> Solar panels.</td>
			</tr>
			<tr>
				<td style="width: 40%"><strong>Motor Cable</strong></td>
				<td colspan="2" style="width: 10%; text-align: left;">	Length <strong>' .$project->project_details->sizing['cable_length']. '</strong>, Cross Sectional Area <strong>' .$solution[11]. '</strong></td>
			</tr>
			<tr>
				<td colspan="3" style="width: 10%; text-align: left;">Other Accessories</td>
			</tr>';
	if($pv_disconnect->pv_disconnect->other->quantity > 0){
	$html .= '<tr>
				<td style="width: 40%" rowspan="2"><strong>PV Disconnect</strong></td>
				<td style="width: 10%; text-align: center;">' .$pv_disconnect->pv_count. '</td>
				<td style="text-align: left;">' .$pv_disconnect->pv_disconnect->pv_disconnect_model. '</td>
			</tr>
			<tr>
				<td style="width: 10%; text-align: center;">' .$pv_disconnect->pv_disconnect->other->quantity. '</td>
				<td style="text-align: left;">' .$pv_disconnect->pv_disconnect->other->model. '</td>
			</tr>';
	} else {
	$html .= '<tr>
				<td style="width: 40%"><strong>PV Disconnect</strong></td>
				<td style="width: 10%; text-align: center;">' .$pv_disconnect->pv_count. '</td>
				<td style="text-align: left;">' .$pv_disconnect->pv_disconnect->pv_disconnect_model. '</td>
			</tr>';
	}
	$html .= '<tr>
				<td style="width: 40%"><strong>Earthrod c/w Clamp</strong></td>
				<td colspan="2" style="width: 10%; text-align: left;">1</td>
			</tr>
			<tr>
				<td style="width: 40%"><strong>6mm² DC Cable for Earthrod</strong></td>
				<td colspan="2" style="width: 10%; text-align: left;">(As required)</td>
			</tr>
			<tr>
				<td colspan="3">
					<h3 style="padding: 10px 0; margin: 0">Daily output in average month - ' .$output[(count($output) - 1)]. '</h3>
				</td>
			</tr>
		</table>
		<h2 style="padding: 15px 0; font-size: 15px; margin: 15px 0 0 0;">Monthly Irradiation Data</h2>
		<div class="irradiation-image"><img src="' .$data->irradiation_img. '" /></div>
		<h2 style="padding: 15px 0; font-size: 15px; margin: 15px 0 0 0;">Daily Irradiation Data</h2>
		<div class="irradiation-image"><img src="' .$data->daily_irradiation_img. '" /></div>
		<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; border: solid 1px #eee; border-color: #f1f1f1">
			<tr>
				<td rowspan="2" style="width: 20%; text-align: center;"><strong>Irradiation [kWh/m²]</strong></td>
				<td style="text-align: center;">Jan</td>
				<td style="text-align: center;">Feb</td>
				<td style="text-align: center;">Mar</td>
				<td style="text-align: center;">Apr</td>
				<td style="text-align: center;">May</td>
				<td style="text-align: center;">Jun</td>
				<td style="text-align: center;">Jul</td>
				<td style="text-align: center;">Aug</td>
				<td style="text-align: center;">Sep</td>
				<td style="text-align: center;">Oct</td>
				<td style="text-align: center;">Nov</td>
				<td style="text-align: center;">Dec</td>
				<td style="text-align: center;">Avg</td>
			</tr>
			<tr>';
			foreach($irradiation as $item){
				$html .= '<td style="text-align: center;">' .$item. '</td>';
			}
		$html .= '</tr>
		</table>
		<h2 style="padding: 15px 0; font-size: 15px; margin: 15px 0 0 0;">Monthly Output Data</h2>
		<div class="output-image"><img src="' .$data->output_img. '" /></div>
		<h2 style="padding: 15px 0; font-size: 15px; margin: 15px 0 0 0;">Daily Output Data</h2>
		<div class="output-image"><img src="' .$data->daily_output_img. '" /></div>
		<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; border: solid 1px #eee; border-color: #f1f1f1">
			<tr>
				<td rowspan="2" style="width: 20%; text-align: center;"><strong>Output [m³/day]</strong></td>
				<td style="text-align: center;">Jan</td>
				<td style="text-align: center;">Feb</td>
				<td style="text-align: center;">Mar</td>
				<td style="text-align: center;">Apr</td>
				<td style="text-align: center;">May</td>
				<td style="text-align: center;">Jun</td>
				<td style="text-align: center;">Jul</td>
				<td style="text-align: center;">Aug</td>
				<td style="text-align: center;">Sep</td>
				<td style="text-align: center;">Oct</td>
				<td style="text-align: center;">Nov</td>
				<td style="text-align: center;">Dec</td>
				<td style="text-align: center;">Avg</td>
			</tr>
			<tr>';

			foreach($output as $item){
				$html .= '<td style="text-align: center;">' .$item. '</td>';
			}

		$html .= '</tr>
		</table>
		<h2 style="padding: 15px 0; font-size: 15px; margin: 15px 0 0 0;">Pump & System Curves</h2>
		<div class="output-image"><img src="' .$data->pump_img. '" /></div>
		<div class="output-image"><img src="' .$data->efficiency_img. '" /></div>
		<div class="products-container">' .$data->pump_content. '</div>
		<div class="products-container">' .$data->inverter_content. '</div>
		<div class="products-container">' .$data->panels_content. '</div>
		<h2 style="padding: 15px 0; font-size: 15px; margin: 15px 0 0 0;">Wiring Diagram</h2>
		<div class="output-image"><img src="img/wiring/' .$project->project_details->wiringdiagram. '.jpg" /></div>
		<p style="text-align: center"><strong>' .$solution[6]. '</strong> panels by <strong>' .$solution[7]. '</strong> string(s)</p>
		<table border="0" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; border: solid 1px #f1f1f1; border-color: #f1f1f1" bordercolor="#f1f1f1">
			<tr>
				<td style="text-align: center; width: 70%">
				</td>
				<td style="text-align: center">
					<img src="' .$qr. '" ><br/>
					Scan with the Dayliff App
				</td>
			</tr>
		</table>
	</div>
</body>
</html>';
	return $html;
}

function getsolarizationtemplate($project, $company, $solution, $irradiation, $url, $date, $data, $pv_disconnect){

	// QR Code
	$qr_path = HOST_LINK . 'img/QR-Code-Generated.jpg';
	$qr_type = pathinfo($qr_path, PATHINFO_EXTENSION);
	$rl_data = file_get_contents($qr_path);
	$qr = 'data:image/' .$qr_type. ';base64,' .base64_encode($rl_data);

	$sizing = $project->project_details->sizing;
	$html = '<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<title></title>
	<style type="text/css">
		*{
			font-family: "quotefont";
			font-size: 12px;
		}
		body{
			margin: 10px;
			}
		@font-face {
			font-family: "quotefont";
			src:url("libs/dompdf/lib/fonts/OpenSans-Regular.ttf") format("truetype");
			font-weight: normal
		}
		@font-face {
			font-family: "quotefont";
			src:url("libs/dompdf/lib/fonts/OpenSans-Bold.ttf") format("truetype");
			font-weight: bold
		}
		@page{
			margin: 30px;
		}
		.products-container img{
			max-width: 100%;
			height: auto;
		}
		.products-container table{
			width: 100%;
			border-right: solid 1px #ddd;
			border-bottom: solid 1px #ddd;
		}
		.products-container table td{
			border-top: solid 1px #ddd;
			border-left: solid 1px #ddd;
			font-size: 10px;
			padding: 1px;
			text-align: center;
		}
		.products-container table .highlight-row{
			background: #ddd;
		}

		.irradiation-image, .output-image{
			text-align: center;
			padding: 10px;
		}
		.irradiation-image img, .output-image img{
			width : 94%;
			height: auto;
		}
	</style>
</head>
<body>
	<div style="border: solid 1px #cccccc; padding: 10px;">
		<table border="0" cellpadding="0" cellspacing="0" style="width: 100%; margin-top: 10px;">
			<tr>
				<td width="60%">
					<img src="img/logos/SolarCalc-Report-Logo.png" alt="SolarCalc Solar Sizing">
				</td>
				<td style="width: 40%; text-align: right;">
					<img src="img/logos/Report-Logo.jpg" class="img-fluid company-logo" alt="' .$company->company_name. '">
					<ul>
						<li style="list-style-type: none;"><strong>' .$company->company_name. '</strong></li>
						<li style="list-style-type: none;">' .$company->physical_location. '</li>
						<li style="list-style-type: none;">' .$company->postal_address. '</li>
						<li style="list-style-type: none;">' .$company->company_phone. '</li>
						<li style="list-style-type: none;">' .$company->company_email. '</li>
						<li style="list-style-type: none;">' .$company->company_website. '</li>
					</ul>
				</td>
			</tr>
		</table>
		<div style="border-bottom: solid 1px #eee; background: none; margin-bottom: 5px;"></div>
		<table border="0" cellpadding="0" cellspacing="0" style="width: 100%">
			<tr>
				<td>
					<ul style="display: block; margin: 0; padding: 5px 0;">
						<li style="list-style-type: none; margin: 0;">' .$date. '</li>
						<li style="list-style-type: none; margin: 0;">' .$project->project_name. '</li>
						<li style="list-style-type: none; margin: 0;">' .$project->customer->customer_name. '</li>
						<li style="list-style-type: none; margin: 0;">' .$project->location_name. '</li>
						<li style="list-style-type: none; margin: 0;">' .$project->customer->customer_email. '</li>
						<li style="list-style-type: none; margin: 0;">' .$project->customer->customer_telephone. '</li>
						<li style="list-style-type: none; margin: 0;">' .$project->project_details->project_notes. '</li>
					</ul>
				</td>
			</tr>
		</table>
		<div style="border-bottom: solid 1px #eee; background: none; margin-bottom: 10px; margin-top: 15px;"></div>
		<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; border: solid 1px #eee; border-color: #f1f1f1">
			<tr>
				<td colspan="10">
					<h3 style="padding: 10px 0; margin: 0">Paramaters</h3>
				</td>
			</tr>
			<tr>
				<td>Location</td>
				<td colspan="9">' .$project->location_name. '(' .$project->project_details->sizing['latitude_info']. ', ' .$project->project_details->sizing['longitude_info']. ')</td>
			</tr>
		</table>
		<br/>';

		$html .= '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; border: solid 1px #eee; border-color: #f1f1f1">
			<tr>
				<td style="width: 40%">
					<strong>Product</strong>
				</td>
				<td style="width: 10%; text-align: center;">
					<strong>Quantity</strong>
				</td>
				<td style="width: 50%">
					<strong>Details</strong>
				</td>
			</tr>
			<tr>
				<td style="width: 40%"><strong>Pump</strong> - ' .$solution[0]. '</td>
				<td style="width: 10%; text-align: center;">1</td>
				<td style="width: 50%">---</td>
			</tr>
			<tr>
				<td style="width: 40%"><strong>Inverter</strong> - ' .$solution[2]. '</td>
				<td style="width: 10%; text-align: center;">1</td>
				<td style="width: 50%"></td>
			</tr>
			<tr>
				<td style="width: 40%"><strong>Panels</strong> - ' .$solution[4]. '</td>
				<td style="width: 10%; text-align: center;">' .$solution[6]. ' x ' .$solution[7]. '</td>
				<td style="width: 50%"><strong>' .$solution[7]. '</strong> string(s) each with <strong>' .$solution[6]. '</strong> Solar panels.</td>
			</tr>
			<tr>
				<td colspan="3"><strong>Other Accessories</strong></td>
			</tr>';
	if($pv_disconnect->pv_disconnect->n->other->quantity > 0){
	$html .= '<tr>
				<td style="width: 40%" rowspan="2">PV Disconnect</td>
				<td style="width: 10%; text-align: center;">' .$pv_disconnect->pv_count. '</td>
				<td style="width: 50%">' .$pv_disconnect->pv_disconnect->pv_disconnect_model. '</td>
			</tr>
			<tr>
				<td style="text-align: center;">' .$pv_disconnect->pv_disconnect->n->other->quantity. '</td>
				<td>' .$pv_disconnect->pv_disconnect->n->other->model.' </td>
			</tr>';
	} else {
	$html .= '<tr>
				<td style="width: 40%">PV Disconnect</td>
				<td style="width: 10%; text-align: center;">' .$pv_disconnect->pv_count. '</td>
				<td style="width: 50%">' .$pv_disconnect->pv_disconnect->pv_disconnect_model. '</td>
			</tr>';

	}
	$html .= '<tr>
				<td style="width: 40%">DC Level Control Box</td>
				<td style="width: 10%; text-align: center;">1</td>
				<td>Dayliff DC Level Control Box</td>
			</tr>
		</table>
		<h2 style="padding: 15px 0; font-size: 15px; margin: 15px 0 0 0;">Monthly Irradiation Data</h2>
		<div class="irradiation-image"><img src="' .$data->irradiation_img. '" /></div>
		<table border="1" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; border: solid 1px #eee; border-color: #f1f1f1">
			<tr>
				<td rowspan="2" style="width: 20%; text-align: center;"><strong>Irradiation [kWh/m²]</strong></td>
				<td style="text-align: center;">Jan</td>
				<td style="text-align: center;">Feb</td>
				<td style="text-align: center;">Mar</td>
				<td style="text-align: center;">Apr</td>
				<td style="text-align: center;">May</td>
				<td style="text-align: center;">Jun</td>
				<td style="text-align: center;">Jul</td>
				<td style="text-align: center;">Aug</td>
				<td style="text-align: center;">Sep</td>
				<td style="text-align: center;">Oct</td>
				<td style="text-align: center;">Nov</td>
				<td style="text-align: center;">Dec</td>
				<td style="text-align: center;">Avg</td>
			</tr>
			<tr>';
			foreach($irradiation as $item){
				$html .= '<td style="text-align: center;">' .$item. '</td>';
			}
		$html .= '</tr>
		</table>
		<div class="products-container">' .$data->inverter_content. '</div>
		<div class="products-container">' .$data->panels_content. '</div>
		<h2 style="padding: 15px 0; font-size: 15px; margin: 15px 0 0 0;">Wiring Diagram</h2>
		<div class="output-image"><img src="img/wiring/' .$project->project_details->wiringdiagram. '.jpg" /></div>
		<p style="text-align: center"><strong>' .$solution[6]. '</strong> panels by <strong>' .$solution[7]. '</strong> string(s)</p>
		<table border="0" cellpadding="5" cellspacing="0" style="width: 100%; border-collapse: collapse; border: solid 1px #f1f1f1; border-color: #f1f1f1" bordercolor="#f1f1f1">
			<tr>
				<td style="text-align: center; width: 70%">
				</td>
				<td style="text-align: center">
					<img src="' .$qr. '" ><br/>
					Scan with the Dayliff App
				</td>
			</tr>
		</table>
	</div>
</body>
</html>';
	return $html;
}
function printprojectpdf($project_code, $connection){	// 
	$update = $connection->query("UPDATE projects SET print_pdf = 1 WHERE project_code = '" .$connection->real_escape_string($project_code). "'");
	// 
}
function saveparameters($params, $saved_type, $connection){

	$user = $_COOKIE['unique_id'];
	$query = $connection->query("SELECT * FROM saved_parameters WHERE saved_data = '" .$connection->real_escape_string($params). "'");
	if($query->num_rows == 0){
		$update = $connection->query("INSERT INTO saved_parameters SET saved_data = '" .$connection->real_escape_string($params). "', saved_type = '" .$connection->real_escape_string($saved_type). "', user_id = '" .$connection->real_escape_string($user). "', saved_date = NOW()");
	}

}


function getstaffdetails($data){

	$token = gettoken($data);
	if($token->access_token){
		$profile = getprofile($token);
		// logaction('Profile', $profile);
		if($profile == false){
			// $status = false;
			$status = (Object)array(
			'staff_status'		=>	false,
			'token'			=>	$token->access_token,
			'profile'			=>	$profile,
			'data'			=>	$data,
			'text'			=>	'INVALID ACCESS. TRY AGAIN <i class="fas fa-exclamation-triangle"></i>'
		);
		} else {

			$status = (Object)array(
				'staff_status'		=>	true,
				'data'			=>	$data,
				'staff_details'	=>	$profile
			);
		}
	} else {
		$status = (Object)array(
			'staff_status'		=>	false,
			'data'			=>	$data,
			'token'			=>	$token,
			'text'			=>	$token->response_details
		);
	}

	return $status;
}

function gettoken($data){

	// logaction('Credentials', $data);

	$curl = curl_init();

	// Get Access Token
	curl_setopt_array($curl, array(
		CURLOPT_URL 			=>	'https://login.microsoftonline.com/' .TENANT_ID. '/oauth2/v2.0/token',
		CURLOPT_RETURNTRANSFER	=>	true,
		CURLOPT_ENCODING		=>	'',
		CURLOPT_MAXREDIRS		=>	10,
		CURLOPT_TIMEOUT		=>	0,
		CURLOPT_FOLLOWLOCATION	=>	true,
		CURLOPT_SSL_VERIFYPEER	=>	false,
		CURLOPT_HTTP_VERSION	=>	CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST	=>	'POST',
		CURLOPT_POSTFIELDS		=>	'grant_type=password&client_id=' .CLIENT_ID. '&client_secret=' .CLIENT_SECRET. '&scope=https%3A//graph.microsoft.com/.default&userName=' .strtolower($data->email). '&password=' .urlencode($data->password),
		CURLOPT_HTTPHEADER		=>	array(
			'Content-Type: application/x-www-form-urlencoded'
		),
	));

	$response = json_decode(curl_exec($curl));
	
	curl_close($curl);

	// property_exists(class, property)

	if(property_exists($response, 'access_token')){
		$status = (Object)array(
			'access_token'		=>	$response->access_token,
			'bearer'			=>	$response->token_type,
			'response'		=>	$response
		);
	} else {
		$status = (Object)array(
			'access_token'		=>	false,
			'response'		=>	$response,
			'response_details'	=>	'INVALID EMAIL / PASSWORD. TRY AGAIN <i class="fas fa-exclamation-triangle"></i>'
		);
	}

	// logaction('Token', $response);

	return $status;

}

function getprofile($token){
	$curl = curl_init();
	// Get Profile
	curl_setopt_array($curl, array(
		CURLOPT_URL			=>	"https://graph.microsoft.com/v1.0/me",
		CURLOPT_RETURNTRANSFER	=>	true,
		CURLOPT_ENCODING		=>	"",
		CURLOPT_MAXREDIRS		=>	10,
		CURLOPT_TIMEOUT		=>	0,
		CURLOPT_FOLLOWLOCATION	=>	true,
		CURLOPT_SSL_VERIFYPEER	=>	false,
		CURLOPT_HTTP_VERSION	=>	CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST	=>	"GET",
		CURLOPT_HTTPHEADER		=>	array(
			"Authorization: " .$token->bearer. " " .$token->access_token. ""
		),
	));
	$response = json_decode(curl_exec($curl));
	$err = curl_error($curl);
	curl_close($curl);

	if(property_exists($response, 'userPrincipalName')){
		$status = $response;
	} else {
		$status = false;
	}
	return $status;

}

function addupdateuser($account, $connection){

	$query = $connection->query("SELECT * FROM users WHERE email = '" .$connection->real_escape_string(strtolower($account->data->email)). "'");
	if($query->num_rows > 0){
		
		$item = $query->fetch_object();

		$unique_id = $item->unique_id;
		$timestamp = randomstring(3) . date('dmYHis') . $unique_id;
		$password = hash('sha256', strtolower($account->data->email) . $unique_id). '.' .hash('sha1', $timestamp . $account->staff_details->id);
		// Update
		$update = $connection->query("UPDATE users SET unique_id = '" .$connection->real_escape_string($unique_id). "', fullname = '" .$connection->real_escape_string($account->staff_details->displayName). "', phone = '" .$connection->real_escape_string(str_replace('+', '', $account->staff_details->mobilePhone)). "', company = '" .$connection->real_escape_string('Davis & Shirtliff'). "', location = '" .$connection->real_escape_string($account->staff_details->officeLocation). "', user_origin = 1, password = '" .$connection->real_escape_string($password). "', timestamp = '" .$connection->real_escape_string($timestamp). "', date_modified = NOW(), status = 1 WHERE email = '" .$connection->real_escape_string($account->data->email). "'");
		return $update;

	} else {

		// Insert
		$unique_id = randomstring(17);
		$timestamp = randomstring(3) . date('dmYHis') . $unique_id;
		$password = hash('sha256', strtolower($account->data->email) . $unique_id). '.' .hash('sha1', $timestamp . $account->staff_details->id);
		$insert = $connection->query("INSERT INTO users SET unique_id = '" .$connection->real_escape_string($unique_id). "', fullname = '" .$connection->real_escape_string($account->staff_details->displayName). "', email = '" .$connection->real_escape_string($account->data->email). "', phone = '" .$connection->real_escape_string(str_replace('+', '', $account->staff_details->mobilePhone)). "', company = '" .$connection->real_escape_string('Davis & Shirtliff'). "', location = '" .$connection->real_escape_string($account->staff_details->officeLocation). "', user_origin = 1, password = '" .$connection->real_escape_string($password). "', timestamp = '" .$connection->real_escape_string($timestamp). "', date_created = NOW(), date_modified = NOW(), status = 1");

		// Send Welcome Email
		sendmail($account->data->email, $account->staff_details->displayName, true);
		return $insert;

	}

	// return $connection->error;
}