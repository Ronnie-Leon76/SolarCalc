<?php
// Require Config
require_once('config.php');
require_once('functions.php');

$cookie = $_COOKIE;
if(!(ISSET($cookie['unique_id']) && ISSET($cookie['unique_token']) && ISSET($cookie['user_origin']))){
	header('location: login');
}

$countries_json = file_get_contents('files/countries.json');
$countries = json_decode($countries_json, true);

$date = date('l, dS M Y');

// Check if this user has a customized company
$user = getloggedinuser();
$company = getcompany($user->user_origin, $user->unique_id, $user->invitation_code);
$defaultcompany = defaultcompany();

if($company){
	if($company->company_status == 1){
		$details = $company;
	} else {
		$details = $defaultcompany;
	}	
} else {
	$details = $defaultcompany;
}

$pump_categories = getpumpcategories($notincluded = array(2));
$host = gethost();

?>
<?php echo page_header('SOLARCALC | Surface Pump Sizing'); ?>
<?php echo top_nav('SOLARCALC'); ?>
<div class="wrapper main-page">
	<div class="container-fluid">
		<div class="row">
			<nav class="col-md-3 d-none d-md-block bg-light sidebar sizing">
				<div class="sidebar-sticky">
					<form class="sizing sizing-form surface-sizing">
						<h4 class="top-header location-header"></h4>
						<br/>
						<div class="form-group row first-child first-child-row">
							<!-- <label class="col-md-4 required">Select / Search Location</label> -->
							<div class="col-md-12" data-toggle="tooltip" title="To be more precise, pick the location from Google Maps or set the GPS coordinates" data-placement="left">
								<a href="#" class="btn btn-outline-dark get-gps btn-block" data-toggle="modal" data-target=".googlemaps-modal">
									<i class="fas fa-bullseye"></i> Select / Search Location
								</a>
							</div>
							<div class="col-md-1 d-none">
								<input type="hidden" name="country" required="required">							
							</div>
							<div class="col-md-1 d-none">
								<input type="hidden" name="location_name" required="required">
							</div>
						</div>
						<div class="form-row latitude-longitude d-none">
							<div class="col">
								<input type="text" class="form-control" name="latitude_info" readonly="readonly" required="required">
							</div>
							<div class="col">
								<input type="text" class="form-control" name="longitude_info" readonly="readonly" required="required">
								<input type="hidden" name="average_irradiation" readonly="readonly" required="required">
							</div>
						</div>
						<div class="form-group row mt-3">
							<label class="col-md-6 required">Pump Type / Category</label>
							<div class="col-md-6">
								<select class="form-control" name="pump_category" required="required">
									<option value="">-- Select Category --</option>
									<?php foreach($pump_categories as $pump_category){ ?>
									<option value="<?php echo $pump_category->category_id ?>"><?php echo $pump_category->category_name ?></option>
									<?php } ?>
								</select>
							</div>
						</div>
						<div class="divider"></div>
						<div class="form-group row">
							<label class="col-md-6" for="custom-sizing">Advanced Sizing Option? <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="right" title="Please select advanced if you have such paramaters as the Pipe Material, Pipe length etc"></i></label>
							<div class="col-md-6">
								<div class="custom-control custom-switch">
									<input type="checkbox" class="custom-control-input" id="custom-sizing" name="sizing_option" value="1">
									<label class="custom-control-label" for="custom-sizing"></label>
								</div>
							</div>
						</div>
						<div class="form-group row total-dynamic-head">
							<label class="col-md-6 required">Total Dynamic Head (m) <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="right" title="The height from the Pumping Water Level to the Delivery Point "></i></label>
							<div class="col-md-6">
								<input type="number" class="form-control" name="total_dynamic_head" required="required">
							</div>
						</div>

						<div class="form-group row d-none sizing-option-field">
							<label class="col-md-6 required">Static Head (m)</label>
							<div class="col-md-6">
								<input type="number" class="form-control" name="static_head">
							</div>
						</div>
						<div class="form-group row d-none sizing-option-field">
							<label class="col-md-6 required">Pipe Length (m)</label>
							<div class="col-md-6">
								<input type="number" class="form-control" name="pipe_length">
							</div>
						</div>
						<div class="form-group row d-none sizing-option-field">
							<label class="col-md-6 required">Pipe Inner Diameter Ø - Inch</label>
							<div class="col-md-6">
								<input type="number" class="form-control" name="inner_diameter">
							</div>
						</div>
						<div class="form-group row d-none sizing-option-field">
							<label class="col-md-6 required">Pipe Material</label>
							<div class="col-md-6">
								<select class="form-control" name="pipe_material">
									<option value="">-- Select Pipe Material --</option>
									<option value="0">HDPE</option>
									<option value="1">LDPE</option>
									<option value="2">uPVC</option>
									<option value="3">Rubber Lined</option>
									<option value="4">New Steel</option>
									<option value="5">Medium Lined</option>
									<option value="6">Corroded Steel</option>
								</select>
							</div>
						</div>
						<div class="divider"></div>
						<!-- <h4>Submersible Cable, Panels, Pipe & Pump</h4> -->
						<div class="form-group row">
							<label class="col-md-6">Inverter Cable Length (m)</label>
							<div class="col-md-6">
								<input type="number" class="form-control" name="cable_length">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-md-6">Output Option <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="right" title="Please select Output per Day or per Hour"></i></label>
							<div class="col-md-6">
								<div class="custom-control custom-radio custom-control-inline">
									<input type="radio" name="output_option" value="1" checked="checked" id="output_option1" class="custom-control-input">
									<label class="custom-control-label" for="output_option1">m³/day</label>
								</div>
								<div class="custom-control custom-radio custom-control-inline">
									<input type="radio" name="output_option" value="2" id="output_option2" class="custom-control-input">
									<label class="custom-control-label" for="output_option2">m³/hr</label>
								</div>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-md-6 required">Output <span class="expected-daily-output"></span></label>
							<div class="col-md-6">
								<input type="number" class="form-control" name="pump_option_output" required="required">
							</div>
						</div>
						<div class="form-group row d-none">
							<label class="col-md-6"></label>
							<div class="col-md-6">
								<input type="number" class="form-control" name="pump_output" required="required">
							</div>
						</div>
						<div class="form-group row">
							<label class="col-md-6">Sizing for <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="right" title="This will allow you to size the Pumping system based on the highest or lowest output. See the Irradiation tab to check this"></i></label>
							<div class="col-md-6">
								<select class="form-control" name="sizing_for" required="required">
									<option value="0">Sizing for Average Monthly Irradiation</option>
									<option value="1">Sizing for Month with most Output / Solar Irradiation</option>
									<option value="2">Sizing for Month with least Output / Solar Irradiation</option>
								</select>
								<input type="hidden" name="location_details">
							</div>
						</div>
						<div class="divider"></div>
						<div class="form-group row last-row">
							<label class="col-md-6 custom-module" for="custom-module">Use Custom Modules</label>
							<div class="col-md-6">
								<!-- <input type="checkbox" name="custom_module" class="check-input" id="custom-module"> -->
								<input type="hidden" name="custom_module_panels">
								<div class="custom-control custom-switch">
									<input type="checkbox" class="custom-control-input" id="custom-module" name="custom_module">
									<label class="custom-control-label" for="custom-module"></label>
								</div>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-md-6">Motor Type? <i class="fas fa-info-circle phase-error" data-toggle="tooltip" data-placement="right" title="Non-hybrid inverters are incompatible with Single phase motors"></i></label>
							<div class="col-md-6">
								<div class="custom-control custom-radio custom-control-inline">
									<input type="radio" name="phase" value="1" id="phase1" class="custom-control-input">
									<label class="custom-control-label" for="phase1">1 Phase</label>
								</div>
								<div class="custom-control custom-radio custom-control-inline">
									<input type="radio" name="phase" value="3" id="phase3" checked="checked" class="custom-control-input">
									<label class="custom-control-label" for="phase3">3 Phase</label>
								</div>
							</div>
						</div>
						<div class="form-group row">
							<label class="col-md-6">Hybrid Inverter? <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="right" title="A Hybrid inverter allows connection to the Grid while a Non-Hybrid one does not allow connection to the Grid"></i></label>
							<div class="col-md-6">
								<div class="custom-control custom-radio custom-control-inline">
									<input type="radio" name="hybrid" value="1" checked="checked" id="hybrid1" class="custom-control-input">
									<label class="custom-control-label" for="hybrid1">Yes</label>
								</div>
								<div class="custom-control custom-radio custom-control-inline">
									<input type="radio" name="hybrid" value="2" id="hybrid2" class="custom-control-input">
									<label class="custom-control-label" for="hybrid2">No</label>
								</div>
							</div>
						</div>
						<div class="form-group row last-row">
							<label class="col-md-6 custom-module" for="custom-factor">Module Oversizing Factor <i class="fas fa-info-circle" data-toggle="tooltip" data-placement="right" title="Please note that the default minimum module oversizing factor is set at <?php echo MIN_PANEL_UPLIFT ?> and maximum of <?php echo MAX_PANEL_UPLIFT ?>"></i></label>
							<div class="col-md-6">
								<!-- <input type="checkbox" name="custom_panel_uplift" class="check-input" id="custom-factor"> -->
								<div class="custom-control custom-switch">
									<input type="checkbox" class="custom-control-input" id="custom-factor" name="custom_panel_uplift">
									<label class="custom-control-label" for="custom-factor"></label>
								</div>
							</div>

						</div>
						<div class="form-group row panel-uplift d-none">
							<label class="col-md-6" for="min-panel-uplift">Min. Module Oversizing - <span data-minpaneluplift="<?php echo MIN_PANEL_UPLIFT ?>" class="minpaneluplift"><?php echo MIN_PANEL_UPLIFT ?></span></label>
							<div class="col-md-6">
								<input type="range" name="min_panel_uplift" class="custom-range" id="min-panel-uplift" min="1" max="2" step="0.01" value="<?php echo MIN_PANEL_UPLIFT ?>">
							</div>
						</div>
						<div class="form-group row panel-uplift d-none">
							<label class="col-md-6" for="max-panel-uplift">Max. Module Oversizing - <span data-maxpaneluplift="<?php echo MAX_PANEL_UPLIFT ?>" class="maxpaneluplift"><?php echo MAX_PANEL_UPLIFT ?></span></label>
							<div class="col-md-6">
								<input type="range" name="max_panel_uplift" class="custom-range" id="max-panel-uplift" min="1" max="2" step="0.01" value="<?php echo MAX_PANEL_UPLIFT ?>">
							</div>
						</div>
						<input type="hidden" name="average_output" readonly="readonly">
						<input type="hidden" name="daily_irradiation" readonly="readonly">
						<input type="hidden" name="daily_output" readonly="readonly">
						<button class="btn btn-dark btn-lg calculate-sizing-surface">Start / Calculate <i class="fas fa-arrow-right"></i></button>
					</form>
				</div>
			</nav>
			<div class="col-md-9 col-lg-9 result-area">
				<ul class="nav nav-tabs" id="myTab" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" id="one-tab" data-toggle="tab" href="#one" role="tab" aria-controls="one" aria-selected="true">Schema - Surface Pump</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="two-tab" data-toggle="tab" href="#two" role="tab" aria-controls="two" aria-selected="false">Irradiation</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="three-tab" data-toggle="tab" href="#three" role="tab" aria-controls="three" aria-selected="false">Output</a>
					</li>
					<li class="nav-item">
						<a class="nav-link d-nonex" id="six-tab" data-toggle="tab" href="#six" role="tab" aria-controls="six" aria-selected="false">Report</a>
					</li>
				</ul>
				<div class="tab-content" id="myTabContent">
					<div class="tab-pane fade show active" id="one" role="tabpanel" aria-labelledby="one-tab">
						<div class="col results-image">
							<img src="img/Pump-Placement-Schema-Surface.png" class="img-fluid schema-pump" usemap="#results-diagram">
							<svg viewBox="0 0 100 100">

								<!-- Pipe from Motor -->
								<line x1="69.5%" y1="45.7%" x2="99.2" y2="45.7%" class="pipe-length-line" style="stroke:rgb(100, 100, 100); stroke-width:2.3" />
								<line x1="98" y1="2.9%" x2="98" y2="45.8%" class="pipe-length-line" style="stroke:rgb(100, 100, 100); stroke-width:2.3" />
								<line x1="96.9%" y1="3.2%" x2="115.85" y2="3.2%" class="pipe-length-line" style="stroke:rgb(100, 100, 100); stroke-width:2.3" />
								<line x1="114.7" y1="3.2%" x2="114.7" class="pipe-length-line" y2="6%" style="stroke:rgb(100, 100, 100); stroke-width:2.3" />

								<!-- Static Head Line -->
								<polygon points="72,5 73,3 74,5" class="system-head-polygon" style="fill: black" />
								<polygon points="72,47.5 73,49.5 74,47.5" class="system-head-polygon" style="fill: black" />
								<line x1="73" y1="3.8%" x2="73" y2="48%" class="system-head-line" style="stroke:rgb(0, 0, 0); stroke-width:0.2" />

								<!-- Motor Cable -->
								<line x1="29.1" y1="35.5" x2="29.1" y2="40" class="motor-cable-line" style="stroke:rgb(0, 0, 0); stroke-width:0.2" />
								<line x1="58" y1="23" x2="58" y2="40" class="motor-cable-line" style="stroke:rgb(0, 0, 0); stroke-width:0.3" />
								<line x1="29" y1="40" x2="58" y2="40" class="motor-cable-line" style="stroke:rgb(0, 0, 0); stroke-width:0.2" />
								<line x1="57.85" y1="23" x2="61" y2="23" class="motor-cable-line" style="stroke:rgb(0, 0, 0); stroke-width:0.2" />
							</svg>
							<div class="pipe-length"></div>
							<div class="pipe-length-name"></div>
							<div class="system-head"></div>
							<div class="system-head-name">Total Dynamic Head</div>
							<div class="system-head"></div>
							<!-- <div class="system-head-name">Head</div> -->
							<div class="motor-cable"></div>
							<div class="motor-cable-name">Inverter cable</div>
						</div>						
					</div>
					<div class="tab-pane fade" id="two" role="tabpanel" aria-labelledby="two-tab">
						<div class="results-chart" id="results-chart"></div>
						<div class="results-daily-irradiation-chart" id="results-daily-irradiation-chart"></div>
					</div>
					<div class="tab-pane fade" id="three" role="tabpanel" aria-labelledby="three-tab">
						<div class="output-results"></div>
						<div class="output-results-chart" id="output-results-chart"></div>
						<!-- <div class="output-daily-results-chart" id="output-daily-results-chart"></div> -->
					</div>
					<div class="tab-pane fade report" id="six" role="tabpanel" aria-labelledby="six-tab">
						<div class="report-actions">
							<button type="button" class="btn btn-primary save-project">Save Project Report <i class="far fa-save"></i></button>
							<input type="hidden" name="project_id">
							<input type="hidden" name="solutionstring">
							<!-- <button type="button" class="btn btn-dark print-project">Print Project Report <i class="fas fa-print"></i></button> -->
						</div>
						<div class="report-wrapper">
							<div class="container-fluid">
								<div class="row top-header-report">
									<div class="col-3">
										<img src="<?php echo $host; ?>img/logos/<?php echo $details->company_logo; ?>" class="img-fluid company-logo" alt="<?php echo $details->company_name; ?>">
									</div>
									<div class="col-9 text-right contacts">
										<ul>
											<input type="hidden" name="company_id" value="<?php echo $details->company_id; ?>">
											<li class="company-name"><?php echo $details->company_name; ?></li>
											<li class="physical-address"><?php echo $details->physical_location; ?></li>
											<li class="postal-address"><?php echo $details->postal_address; ?></li>
											<li class="company-phone"><?php echo $details->company_phone; ?></li>
											<li class="company-email"><?php echo $details->company_email; ?></li>
											<li class="company-website"><?php echo $details->company_website; ?></li>
										</ul>
									</div>
								</div>
								<div class="row">
									<div class="col-12 contacts">
										<ul>
											<li class="report-date"><?php echo $date; ?></li>
											<li>
												<h1>New Project</h1>
											</li>
											<li>
												<h2>Solar Pumping Project</h2>
												<input type="hidden" name="customer_id">
											</li>
											<li class="customer-physical customer-report-details d-none">Physical Address</li>
											<li class="customer-telephone customer-report-details d-none">Physical Address</li>
											<li class="customer-email customer-report-details d-none">Physical Address</li>
											<li class="customer-postal customer-report-details d-none">Physical Address</li>
											<li>
												<p>Notes - </p>
											</li>
										</ul>
									</div>
								</div>
								<div class="row">
									<div class="col-12 parameters">
										<table class="table table-sm">
											<tbody>
												<tr>
													<td colspan="10">
														<h3>Parameters</h3>
													</td>
												</tr>
												<tr>
													<td style="width: 10%">Location : </td>
													<td class="location-details" style="width: 90%" colspan="9"></td>
												</tr>
												<tr>
													<td style="width: 15%">Required Daily Output : </td>
													<td style="width: 5%" class="output-details"></td>
													<td style="width: 10%">Pipe Type : </td>
													<td style="width: 10%" class="pipe-details"></td>
													<td style="width: 10%">Motor Cable : </td>
													<td style="width: 10%" class="cable-details"></td>
													<td style="width: 15%">Pipe Length : </td>
													<td style="width: 5%" class="pipe-length-details"></td>
													<td style="width: 15%">Total Dynamic Head : </td>
													<td style="width: 5%" class="total-dynamic-details"></td>
												</tr>
											</tbody>
										</table>
										<table class="table table-sm borehole-conditions-table d-none">
											<tbody>
												<tr>
													<td colspan="10">
														<h3>Borehole & Site Conditions</h3>
													</td>
												</tr>
												<tr>
													<td style="width: 15%">Min. Borehole Diameter ("):</td>
													<td style="width: 10%" class="min-borehole-diameter">--</td>
													<td style="width: 15%">Static Water Level (m):</td>
													<td style="width: 10%" class="static-water-level">--</td>
													<td style="width: 15%">Borehole Tested Yield (m):</td>
													<td style="width: 10%" class="borehole-tested-yield">--</td>
													<td style="width: 15%">Pump Inlet Depth (m):</td>
													<td style="width: 10%" class="pump-inlet-depth">--</td>
												</tr>
												<tr>
													<td style="width: 15%">Main Aquifer (m):</td>
													<td style="width: 10%" class="main-aquifer">--</td>
													<td style="width: 15%">Distance to Solar Array (m):</td>
													<td style="width: 10%" class="distance-solar-details">--</td>
													<td style="width: 15%">Distance to Delivery Point (m):</td>
													<td style="width: 35%" class="distance-delivery" colspan="3">--</td>
												</tr>
											</tbody>
										</table>
										<table class="table table-sm">
											<thead>
												<th style="width: 40%">
													<h3>Products</h3>
												</th>
												<th style="width: 10%">
													<h4>Quantity</h4>
												</th>
												<th style="width: 50%">
													<h4>Details</h4>
												</th>
											</thead>
											<tbody>
												<tr>
													<td><strong>Pump</strong> - <span class="pump-name"></span></td>
													<td>1</td>
													<td></td>
												</tr>
												<tr>
													<td><strong>Inverter</strong> - <span class="inverter-name"></span></td>
													<td>1</td>
													<td></td>
												</tr>
												<tr>
													<td><strong>Panels</strong> - <span class="panels-name"></span></td>
													<td class="panels-details-content"></td>
													<td></td>
												</tr>
												<tr>
													<td><strong>Motor Cable</strong></td>
													<td colspan="2" class="motor-cable-table"></td>
												</tr>
												<tr>
													<td colspan="3"><strong>Other Accessories</strong></td>
												</tr>
												<!-- <tr>
													<td>Water Level Switch / Well Probe</td>
													<td colspan="2">1</td>
												</tr>
												<tr>
													<td>Water Level Sensor Cable</td>
													<td colspan="2">2 Core x 1.0mm<sup>2</sup>, Length - <span class="cable-details-water-level"></span></td>
												</tr> -->
												<tr>
													<td>PV Disconnect</td>
													<td colspan="2" class="pv-disconnect-details"></td>
												</tr>
												<tr>
													<td>Earthrod C/W Clamp</td>
													<td colspan="2">1</td>
												</tr>
												<tr>
													<td>6mm<sup>2</sup> DC Cable for Earthrod</td>
													<td colspan="2">(As required)</td>
												</tr>
											</tbody>
										</table>
										<table class="table table-sm irradiation-data-table">
											<thead>
												<th style="width: 90%">
													<h3>Daily output in average month - <span class="output-average-month"></span></h3>
												</th>
												<th style="width: 10%">
													<h4></h4>
												</th>
											</thead>
										</table>
									</div>
									<div class="col-12">
										<table class="table table-sm irradiation-data-table">
											<tbody>
												<tr>
													<td colspan="6">
														<h3>Monthly Irradiation Data</h3>
													</td>
												</tr>
											</tbody>
										</table>
									</div>
									<div class="col-12">
										<div class="irradiation-data" id="irradiation-data"></div>
									</div>
									<div class="col-12">
										<div class="daily-irradiation-chart" id="daily-irradiation-chart"></div>
									</div>
									<div class="col-12 output-table">
										<table class="table table-sm table-bordered irradiation-report">
											<tbody>
												<tr>
													<td rowspan="2">Irradiation [kWh/m<sup>2</sup>]</td>
													<td style="width: 6%">Jan</td>
													<td style="width: 6%">Feb</td>
													<td style="width: 6%">Mar</td>
													<td style="width: 6%">Apr</td>
													<td style="width: 6%">May</td>
													<td style="width: 6%">Jun</td>
													<td style="width: 6%">Jul</td>
													<td style="width: 6%">Aug</td>
													<td style="width: 6%">Sep</td>
													<td style="width: 6%">Oct</td>
													<td style="width: 6%">Nov</td>
													<td style="width: 6%">Dec</td>
													<td style="width: 6%">Avg</td>
												</tr>
												<tr class="average-irradiation-data"></tr>
											</tbody>
										</table>
									</div>
									<div class="page-break-one"></div>
									<div class="col-12">										
										<table class="table table-sm irradiation-data-table">
											<tbody>
												<tr>
													<td colspan="6">
														<h3>Monthly Output Data</h3>
													</td>
												</tr>
											</tbody>
										</table>
									</div>
									<div class="col-12">
										<div class="output-data" id="output-data"></div>
									</div>
									<div class="col-12">
										<div class="output-daily-results-chart" id="output-daily-results-chart"></div>
									</div>
									<div class="col-12 output-table">
										<table class="table table-sm table-bordered output-report">
											<tbody>
												<tr>
													<td rowspan="2">Output [m<sup>3</sup>/day]</td>
													<td style="width: 6%">Jan</td>
													<td style="width: 6%">Feb</td>
													<td style="width: 6%">Mar</td>
													<td style="width: 6%">Apr</td>
													<td style="width: 6%">May</td>
													<td style="width: 6%">Jun</td>
													<td style="width: 6%">Jul</td>
													<td style="width: 6%">Aug</td>
													<td style="width: 6%">Sep</td>
													<td style="width: 6%">Oct</td>
													<td style="width: 6%">Nov</td>
													<td style="width: 6%">Dec</td>
													<td style="width: 6%">Avg</td>
												</tr>
												<tr class="average-output-data"></tr>
											</tbody>
										</table>
									</div>
									<h2 class="product-details-header">Pump and System Curves</h2>
									<!-- Pump, System and Efficiency Curves -->
									<div class="col-12 product-content curve-content">
										<div class="pump-curve-content" id="pump-curve-content"></div>
										<div class="efficiency-curve-content" id="efficiency-curve-content"></div>
									</div>									
									<!-- End Curves -->
									<h2 class="product-details-header">Products Details</h2>
									<div class="col-12 product-content pump-content">
										<h3></h3>
										<div class="product-content-image"></div>
										<div class="product-content-description"></div>
									</div>
									<div class="col-12 product-content inverter-content">
										<h3></h3>
										<div class="product-content-image"></div>
										<div class="product-content-description"></div>
									</div>
									<div class="col-12 product-content panels-content">
										<h3></h3>
										<div class="product-content-image"></div>
										<div class="product-content-description"></div>
									</div>
									<h2 class="product-details-header">Wiring Diagram</h2>
									<div class="col-12 product-content wiring-content">
										<div class="product-content-image"></div>
										<div class="product-content-text"></div>
										<input type="hidden" name="wiringdiagram">
									</div>
								</div>
							</div>
						</div>
						<div class="report-actions">
							<button type="button" class="btn btn-primary save-project">Save Project Report <i class="far fa-save"></i></button>
							<!-- <button type="button" class="btn btn-dark print-project">Print Project Report <i class="fas fa-print"></i></button> -->
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Google Maps Modal -->
<div class="modal fade googlemaps-modal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Select Location (<span class="latitude-display"></span>, <span class="longitude-display"></span>)</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="modal-body-overlay">
					<form class="location-form">
						<div class="row">
							<div class="col-2">
								<select class="form-control" name="country" required="required">
									<option value="">-- Select Country --</option>
									<?php foreach($countries as $item){ ?>
									<option value="<?php echo $item['code']. '|' .$item['name']; ?>"><?php echo $item['name'] ?></option>
									<?php } ?>
								</select>
							</div>
							<div class="col-4 dropdown-locations dropdown">
								<input type="hidden" name="location_id">
								<input type="hidden" name="location_code">
								<input type="text" class="form-control" name="location_name" placeholder="Select / Type Location" required="required">
								<div class="dropdown-menu"></div>
							</div>
							<div class="col-2">
								<input type="text" class="form-control" name="latitude_place" placeholder="Latitude" required="required">
							</div>
							<div class="col-2">
								<input type="text" class="form-control" name="longitude_place" placeholder="Longitude" required="required">
							</div>
							<div class="col-2">
								<button class="btn btn-dark btn-block get-location">Get / Search Location</button>
							</div>
						</div>
					</form>
				</div>
				<div class="googlemap" id="googlemap"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-primary confirm-location d-none">Confirm</button>
			</div>
		</div>
	</div>
</div>
<!-- Product Modal -->
<div class="modal fade product-modal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<ul class="nav nav-tabs" role="tablist">
					<li class="nav-item">
						<a class="nav-link active" id="images-tab" data-toggle="tab" href=".images" role="tab" aria-controls="images" aria-selected="true"><i class="far fa-images"></i> Images</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="description-tab" data-toggle="tab" href=".description" role="tab" aria-controls="description" aria-selected="false"><i class="fas fa-list-ul"></i> Description</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" id="documents-tab" data-toggle="tab" href=".documents" role="tab" aria-controls="documents" aria-selected="false"><i class="far fa-file-pdf"></i> Documents</a>
					</li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane fade show active images" id="images" role="tabpanel" aria-labelledby="images-tab"></div>
					<div class="tab-pane fade description" id="description" role="tabpanel" aria-labelledby="description-tab"></div>
					<div class="tab-pane fade documents" id="documents" role="tabpanel" aria-labelledby="documents-tab"></div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<!-- End Product Modal -->
<!-- Profile and Company Profile Modal -->
<div class="modal fade account-settings" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="loading-content">
					<img src="img/loader.gif">
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary close-action" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary save-action">Save</button>
			</div>
		</div>
	</div>
</div>
<!-- End Modal -->
<!-- Curves Modal -->
<div class="modal fade curve-settings" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="loading-content">
					<img src="img/loader.gif">
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary close-action" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>
<!-- End Curves Modal -->
<a href="#" class="btn-dark btn feedback-button" data-toggle="modal" data-target=".feedback-modal">Do you have any feedback?</a>
<!-- Feedback Modal -->
<div class="modal fade feedback-modal" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Feedback</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
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
<?php echo page_footer(); ?>