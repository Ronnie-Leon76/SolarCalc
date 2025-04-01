<?php

require_once('../config.php');
require_once('../functions.php');

$user = getloggedinuser();
$get = (Object)$_GET;
$project = getprojectbyid($user->unique_id, $get->project_id);
$company = getusercompanybyid($project->company_id);
$host = gethost();
$solution = explode('|', $project->project_details->solutionstring);
$average_output = explode('|', $project->project_details->sizing['average_output']);
$irradiation = explode('|', $project->project_details->average_irradiation);
$avg_irradiation = array();
$output = array();
foreach($irradiation as $item){
	array_push($avg_irradiation, (float)$item);
	array_push($output, round((float)$item * $solution[19], 2));
}

$average_irradiation = json_encode($avg_irradiation);
$wateroutput = json_encode($output);
$date = date('l, dS M Y', strtotime($project->project_date));
$panel = getpanel($solution[4], $solution[5]);
$pv_disconnect = getpvdisconnect($solution[6], $solution[7], $panel->short_circuit_current, $panel->open_circuit_voltage);
$sizing = $project->project_details->sizing;

$daily_irradiation = json_encode(explode('|', $project->project_details->sizing['daily_irradiation']));
$daily_output = json_encode(explode('|', $project->project_details->sizing['daily_output']));

// echo '<pre>';
// print_r($daily_irradiation);
// echo '</pre>';

?>
<div class="report-wrapper report-modal">
	<div class="container-fluid">
		<div class="row top-header-report">
			<div class="col-3">
				<img src="<?php echo $host; ?>img/logos/<?php echo $company->company_logo; ?>" class="img-fluid company-logo" alt="<?php echo $company->company_name; ?>">
			</div>
			<div class="col-9 text-right contacts">
				<ul>
					<input type="hidden" name="company_id" value="<?php echo $company->company_id; ?>">
					<li class="company-name"><?php echo $company->company_name; ?></li>
					<li class="physical-address"><?php echo $company->physical_location; ?></li>
					<li class="postal-address"><?php echo $company->postal_address; ?></li>
					<li class="company-phone"><?php echo $company->company_phone; ?></li>
					<li class="company-email"><?php echo $company->company_email; ?></li>
					<li class="company-website"><?php echo $company->company_website; ?></li>
				</ul>
			</div>
		</div>
		<div class="row">
			<div class="col-12 contacts">
				<ul>
					<li class="report-date"><?php echo $date; ?></li>
					<li>
						<h1><?php echo $project->project_name; ?></h1>
					</li>
					<li>
						<h2><?php echo $project->customer->customer_name; ?><?php echo $project->customer->customer_account ? ' - ' .$project->customer->customer_account : '' ?></h2>
					</li>
					<li class="customer-physical customer-report-details"><?php echo $project->customer->customer_physical; ?></li>
					<li class="customer-telephone customer-report-details"><?php echo $project->customer->customer_telephone; ?></li>
					<li class="customer-email customer-report-details"><?php echo $project->customer->customer_email; ?></li>
					<li class="customer-postal customer-report-details"><?php echo $project->customer->customer_postal; ?></li>
					<li>
						<p><?php echo $project->project_details->project_notes; ?></p>
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
							<td class="location-details" style="width: 90%" colspan="9"><?php echo $project->location_name; ?> (<?php echo $project->project_details->sizing['latitude_info']. ', ' .$project->project_details->sizing['longitude_info']; ?>)</td>
						</tr>
						<tr>
							<td style="width: 15%">Required Daily Output : </td>
							<td style="width: 5%" class="output-details"><?php echo $project->project_details->sizing['pump_output']; ?> m<sup>3</sup></td>
							<td style="width: 8%">Pipe Type : </td>
							<td style="width: 5%" class="pipe-details"><?php echo pipematerials($project->project_details->sizing['pipe_material']); ?></td>
							<td style="width: 10%">Motor Cable : </td>
							<td style="width: 10%" class="cable-details"><?php echo $project->project_details->sizing['cable_length']; ?>m, <?php echo $solution[8]; ?></td>
							<td style="width: 20%">Pipe Length & Inner Diameter : </td>
							<td style="width: 7%" class="pipe-length-details"><?php echo $project->project_details->sizing['pipe_length']; ?>m, <?php echo $project->project_details->sizing['inner_diameter']; ?>"</td>
							<td style="width: 5%">Head : </td>
							<td style="width: 15%" class="total-dynamic-details">TDH <?php echo $project->project_details->tdh; ?></td>
						</tr>
					</tbody>
				</table>
				<table class="table table-sm">
					<thead>
						<th style="width: 40%">
							<h3>Products</h3>
						</th>
						<th style="width: 10%; text-align: center">
							<h4>Quantity</h4>
						</th>
						<th style="width: 50%">
							<h4>Details</h4>
						</th>
					</thead>
					<tbody>
						<tr>
							<td><strong>Pump</strong> - <span class="pump-name"><?php echo $solution[0]; ?></span></td>
							<td style="text-align: center;">1</td>
							<td class="pump-details-table">Suitability <strong><?php echo $solution[9]; ?>%</strong>, Efficiency <strong><?php echo $solution[10]; ?>%</strong></td>
						</tr>
						<tr>
							<td><strong>Inverter</strong> - <span class="inverter-name"><?php echo $solution[2]; ?></span></td>
							<td style="text-align: center;">1</td>
							<td class="inverter-details-table"></td>
						</tr>
						<tr>
							<td><strong>Panels</strong> - <span class="panels-name"><?php echo $solution[4]; ?></span></td>
							<td class="panels-details-content" style="text-align: center;"><?php echo $solution[6]. ' x ' .$solution[7]; ?></td>
							<td class="panels-details-table"><strong><?php echo $solution[7]; ?></strong> string(s) each with <strong><?php echo $solution[6]; ?></strong> Solar panels.</td>
						</tr>
						<tr>
							<td><strong>Motor Cable</strong></td>
							<td colspan="2" class="motor-cable-table">Length <?php echo $project->project_details->sizing['cable_length']; ?>m, Cross Sectional Area <?php echo $solution[8]; ?></td>
						</tr>
						<tr>
							<td colspan="3"><strong>Other Accessories</strong></td>
						</tr>
						<tr>
							<td><strong>Water Level Switch / Well Probe</strong></td>
							<td colspan="2">1</td>
						</tr>
						<tr>
							<td><strong>Water Level Sensor Cable</strong></td>
							<td colspan="2">2 Core x 1.0mm<sup>2</sup>, Length - <span class="cable-details-water-level"><?php echo $project->project_details->sizing['cable_length']; ?>m</span></td>
						</tr>
						<tr>
							<td><strong>PV Disconnect</strong></td>
							<?php if($pv_disconnect->pv_disconnect->other->quantity > 0){ ?>
							<td colspan="2" class="pv-disconnect-details"><?php echo $pv_disconnect->pv_count ?> No. <?php echo $pv_disconnect->pv_disconnect->pv_disconnect_model ?> and <?php echo $pv_disconnect->pv_disconnect->other->quantity ?> No. <?php echo $pv_disconnect->pv_disconnect->other->model ?></td>
							<?php } else { ?>
							<td colspan="2" class="pv-disconnect-details"><?php echo $pv_disconnect->pv_count ?> No. <?php echo $pv_disconnect->pv_disconnect->pv_disconnect_model ?></td>
							<?php } ?>
						</tr>
						<tr>
							<td><strong>Earthrod C/W Clamp</strong></td>
							<td colspan="2">1</td>
						</tr>
						<tr>
							<td><strong>6mm<sup>2</sup> DC Cable for Earthrod</strong></td>
							<td colspan="2">(As required)</td>
						</tr>
					</tbody>
				</table>
				<table class="table table-sm irradiation-data-table">
					<thead>
						<th style="width: 90%">
							<h3>Daily output in average month - <span class="output-average-month"><?php echo $output[(count($output) - 1)] ?> m<sup>3</sup></span></h3>
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
				<div class="irradiation-data" id="irradiation-data-report"></div>
			</div>
			<div class="col-12">
				<div class="daily-irradiation-data" id="daily-irradiation-data-report"></div>
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
						<tr class="average-irradiation-data">
							<?php foreach($avg_irradiation as $item){ ?>
							<td><?php echo $item; ?></td>
							<?php } ?>
						</tr>
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
				<div class="output-data" id="output-data-report"></div>
			</div>
			<div class="col-12">
				<div class="daily-output-data" id="daily-output-data-report"></div>
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
						<tr class="average-output-data">
							<?php foreach($average_output as $item){ ?>
							<td><?php echo $item; ?></td>
							<?php } ?>
						</tr>
					</tbody>
				</table>
			</div>
			<h2 class="product-details-header">Pump and System Curves</h2>
			<!-- Pump, System and Efficiency Curves -->
			<div class="col-12 product-content curve-content">
				<div class="pump-curve-report" id="pump-curve-report"></div>
				<div class="efficiency-curve-report" id="efficiency-curve-report"></div>
			</div>
			<!-- End Curves -->
			<div class="page-break-two"></div>
			<h2 class="product-details-header">Products Details</h2>
			<div class="col-12 product-content pump-content">
				<div class="progress">
					<div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
				</div>
			</div>
			<div class="col-12 product-content inverter-content">
				<div class="progress">
					<div class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
				</div>
			</div>
			<div class="col-12 product-content panels-content">
				<div class="progress">
					<div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
				</div>
			</div>
			<h2 class="product-details-header">Wiring Diagram</h2>
			<div class="col-12 product-content wiring-content">
				<div class="product-content-image">
					<img src="<?php echo $host; ?>img/wiring/<?php echo $project->project_details->wiringdiagram; ?>.jpg" class="img-fluid">
				</div>
				<div class="product-content-text">
					<div class="panels-holder"><?php echo $solution[6] ?> modules per string</div>
					<div class="strings-holder"><?php echo $solution[7] ?> strings in parallel</div>
				</div>
			</div>
			<canvas class="irradiation-canvas" id="irradiation-canvas" width="1000px" height="700px"></canvas>
			<canvas class="irradiation-canvas" id="daily-irradiation-canvas" width="1000px" height="700px"></canvas>
			<canvas class="output-canvas" id="output-canvas" width="1000px" height="700px"></canvas>
			<canvas class="output-canvas" id="daily-output-canvas" width="1000px" height="700px"></canvas>
			<canvas class="pump-curve-canvas" id="pump-curve-canvas" width="1000px" height="700px"></canvas>
			<canvas class="efficiency-canvas" id="efficiency-canvas" width="1000px" height="700px"></canvas>
		</div>
	</div>
</div>
<div class="action-content">
	<div class="container-fluid">
		<div class="row">
			<div class="col footer-buttons">
				<?php if($project->print_pdf){ ?>
				<a href="<?php echo HOST_LINK . 'projects/pdf/' .$project->project_code. '.pdf' ?>" target="_blank" class="btn btn-success float-right">Download PDF</a>
				<?php } else { ?>
				<button class="btn btn-primary print-surface-pdf float-right" data-project="<?php echo $get->project_id; ?>">Print Project <i class="far fa-save"></i></button>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">

	// Irradiation
	var DNR = <?php echo $average_irradiation ?>;
	Highcharts.chart('irradiation-data-report', {
		chart: {
			type: 'column',
			height: 500
		},
		title: {
			text: 'Direct Normal Irradiation',
			style: {
				fontSize: "14px"
			}
		},
		subtitle: {
			text: 'Source: NASA.gov POWER Single Point Data Access'
		},
		xAxis: {
			categories: [
				'JAN',
				'FEB',
				'MAR',
				'APR',
				'MAY',
				'JUN',
				'JUL',
				'AUG',
				'SEP',
				'OCT',
				'NOV',
				'DEC',
				'AVG'
				],
			crosshair: true
		},
		yAxis: {
			min: 0,
	        title: {
	            text: 'kW-hr/m<sup>2<sup>/day'
	        }
	    },
	    tooltip: {
	        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
	        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
	            '<td style="padding:0"><b>{point.y:.1f} kW-hr/m<sup>2</sup>/day</b></td></tr>',
	        footerFormat: '</table>',
	        shared: true,
	        useHTML: true
	    },
	    colors : ['#fad72b'],
	    plotOptions: {
	        column: {
	            pointPadding: 0.2,
	            borderWidth: 0,
	            dataLabels: {
	                enabled: true,
	                color: 'black'
	            },
	            colorByPoint : true
	        }
	    },
	    series: [{
	        name: '<?php echo $project->location_name; ?>',
	        data: [DNR[0], DNR[1], DNR[2], DNR[3], DNR[4], DNR[5], DNR[6], DNR[7], DNR[8], DNR[9], DNR[10], DNR[11], DNR[12]]

	    }]
	});

	// Output
	var wateroutput = <?php echo $wateroutput ?>;
	Highcharts.chart('output-data-report', {
		chart: {
			type: 'column',
			height: 500
		},
		title: {
			text: 'Output - <?php echo $project->location_name; ?>',
			style: {
				fontSize: "14px"
			}
		},
		xAxis: {
			categories: [
				'JAN',
				'FEB',
				'MAR',
				'APR',
				'MAY',
				'JUN',
				'JUL',
				'AUG',
				'SEP',
				'OCT',
				'NOV',
				'DEC',
				'AVG'
				],
			crosshair: true
		},
		yAxis: {
			min: 0,
	        title: {
	            text: 'Q[m<sup>3<sup>/day]'
	        }
	    },
	    tooltip: {
	        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
	        pointFormat: '<tr><td style="color:{series.color};padding:0">{series.name}: </td>' +
	            '<td style="padding:0"><b>{point.y:.1f} m<sup>3</sup>/day</b></td></tr>',
	        footerFormat: '</table>',
	        shared: true,
	        useHTML: true
	    },
	    colors : ['#029bf4'],
	    plotOptions: {
	        column: {
	            pointPadding: 0.2,
	            borderWidth: 0,
	            dataLabels: {
	                enabled: true,
	                color: 'black'
	            },
	            colorByPoint : true
	        }
	    },
	    series: [{
	        name: '<?php echo $project->location_name; ?>',
	        data: [wateroutput[0], wateroutput[1], wateroutput[2], wateroutput[3], wateroutput[4], wateroutput[5], wateroutput[6], wateroutput[7], wateroutput[8], wateroutput[9], wateroutput[10], wateroutput[11], wateroutput[12]]
	    }]
	});

	var daily_output_str = <?php echo $daily_output ?>;
	var daily_output = daily_output_str.map(Number);
	var daytimes = [];
	for(var g = 0; g < daily_output.length; g++){
		var newtime = g + 6;
		if(newtime < 10){
			newtime = '0' +newtime;
		}
		daytimes.push(newtime+ '00Hrs');
	}

	var daily_irradiation_str = <?php echo $daily_irradiation ?>;
	var daily_irradiation = daily_irradiation_str.map(Number);

	console.log(daily_output);
	Highcharts.chart('daily-irradiation-data-report', {
	   	chart: {
	        height : 500					        
		    },
		    title: {
		        text: 'Daily Irradiation'
		    },
		    subtitle: {
		        text: 'Source: NASA.gov POWER Single Point Data Access'
		    },
		    xAxis: {
		        categories: daytimes,
		        crosshair: true
		    },
		    yAxis: {
		        min: 0,
		        title: {
		            text: 'Irradiation (W-hr/m²/hr)'
		        }
		    },
		    tooltip: {
		        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
		        pointFormat: '<tr><td style="color:{series.color};padding:0">{point.y}: </td>' +
		            '<td style="padding:0"><b>W-hr/m²/hr</b></td></tr>',
		        footerFormat: '</table>',
		        shared: true,
		        useHTML: true
		    },
		    plotOptions: {
		        column: {
		            pointPadding: 0.2,
		            borderWidth: 0,
		            dataLabels: {
		            	enabled: true,
		            	color: 'black'
		            }
		        }
		    },
		    series: [{
		    		type: 'column',
		        name: '<?php echo $project->location_name; ?>',
		        data: daily_irradiation,
		        color: '#fad82b'
		    }]
	   });

	Highcharts.chart('daily-output-data-report', {
   	chart: {
	        height : 500					        
		    },
		    title: {
		        text: 'Output - <?php echo $project->location_name; ?>'
		    },
		    xAxis: {
		        categories: daytimes,
		        crosshair: true
		    },
		    yAxis: {
		        min: 0,
		        title: {
		            text: 'Output (m³)'
		        }
		    },
		    tooltip: {
		        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
		        pointFormat: '<tr><td style="color:{series.color};padding:0">{point.y}: </td>' +
		            '<td style="padding:0"><b>Q[m³]</b></td></tr>',
		        footerFormat: '</table>',
		        shared: true,
		        useHTML: true
		    },
		    plotOptions: {
		        column: {
		            pointPadding: 0.2,
		            borderWidth: 0,
		            dataLabels: {
		            	enabled: true,
		            	color: 'black'
		            }
		        }
		    },
		    series: [{
		    		type: 'column',
		        name: '<?php echo $project->location_name; ?>',
		        data: daily_output,
		        color: '#0082d6'
		    }]
   });

	getproduct(<?php echo $solution[1]; ?>, '.pump-content', '<?php echo $solution[0]; ?>');
	getproduct(<?php echo $solution[3]; ?>, '.inverter-content', '<?php echo $solution[2]; ?>');


	var panel_id = <?php echo $solution[5] ? $solution[5] : 0; ?>;
	if(panel_id != 0){
		getproduct(panel_id, '.panels-content', '<?php echo $solution[4]; ?>');
	} else {
		$('.panels-content').html('');
	}
	
	generatepumpcurves(<?php echo $solution[15]; ?>, <?php echo $solution[17]; ?>, <?php echo $solution[18]; ?>, '<?php echo $solution[16]; ?>');

	function getproduct(product_id, container, product_name){

		var product_name_description = product_name;
		if(product_id != 0){

			$.ajax({
				url: 'https://www.davisandshirtliff.com/index.php?option=com_hikashop&ctrl=productinfo&format=raw',
		        data: {
		        	product_id : product_id
		        },
		        method: 'POST',
		        dataType: 'JSON',
		        success: function(data, status, xhr){

		        	var containercontent = '';
		        	// Product Title
		        	containercontent += '<div class="page-break-header-before"></div><h3>' +product_name_description+ '</h3>';

		        	// Product Images
		        	containercontent += '<div class="page-break-image-before"></div><div class="product-content-image">';
		        	for(var j = 1; j < data.images.length; j++){
		        		containercontent += '<img src="' +data.images[j].file_image+ '" alt="' +data.images[j].file_name+ '" title="' +data.images[j].file_name+ '" class="img-fluid" />';
		        	}
		        	containercontent += '</div><div class="page-break-image-after"></div>';

		        	// Product Description
		        	containercontent += '<div class="product-content-description">' +data.product_description+ '</div>';

		        	// Display Content
		        	$(container).html(containercontent);
		        	$(container).removeClass('d-none');
		        	$(container+ ' .product-content-description').find('table').addClass('table table-bordered table-sm');
		        	$(container+ ' .product-content-description').find('table td').removeAttr('width');
		        	$(container+ ' .product-content-description').find('table td').removeAttr('height');
		        	// Find the Table and loop through it
		        	$(container+ ' .product-content-description').find('table tr').each(function(){
		        		// var get the value of the first element text
		        		var product_model = $(this).find('td:first-child').text();
		        		product_model = product_model.replace(/\s+/g, '');
		        		product_model = product_model.toLowerCase();
		        		product_name = product_name.replace(/\s+/g, '');
		        		product_name = product_name.toLowerCase();
		        		if(product_model == product_name){
		        			$(this).addClass('highlight-row');
		        		}

		        	});

		        },
		        complete : function(xhr, status){
		          // console.log(xhr);
		        },
		        error : function(status){
		          console.log(status);
		        }
			});

		} else {
			$(container).addClass('d-none');
		}
	}

	function generatepumpcurves(equipment_id, pump_tdh, pump_flow, power_type){

		$.ajax({
			url: 'data.php?action=getproductcurve',
	        data: {
	        	equipment_id : equipment_id,
	        	pump_tdh : pump_tdh,
	        	pump_flow : pump_flow,
	        	power_type : power_type
	        },
	        method: 'POST',
	        dataType: 'JSON',
	        beforeSend: function(xhr, settings){
	        	// $('.curve-settings .modal-body').html('<div class="pump-curve-details" id="pump-curve-details"></div><div class="efficiency-curve-details" id="efficiency-curve-details"></div>');
	        },
	        success: function(result, status, xhr){

	        	console.log(result);

	        	var curve = result.curve;
	        	var efficiency = result.efficiency;
	        	var system = result.system;
	        	var duty = result.duty;
	        	var leastpoint = result.leastpoint;
	        	var duty_efficiency = result.duty_efficiency;

				Highcharts.chart('pump-curve-report', {
				    chart: {
				        type: 'spline',
				        height: 700
				    },
				    title: {
				        text: 'PUMP CURVE - ' +result.name,
				        style: {
				        	fontSize: "14px"
				        }
				    },
					credits: {
					    	enabled: true,
					    	text: '<strong>Flow</strong>: ' +duty[0].flow_rate+ 'm³/h<br/><strong>Head</strong>: ' +duty[0].pump_tdh+ 'm',
					    	position: {
					    		align: 'right',
					    		verticalAlign: 'top',
					    		y: 70
					    	},
					    	style: {
					    		color: 'black',
					    		fontSize: '12px'
					    	}
				    	},
				    xAxis: {
				        title: {
				            text: 'FLOW RATE (m<sup>3</sup>/hr)'
				        },
				        gridLineWidth: 1,
				        labels: {
				        	formatter: function() {
				        		return this.value
				        	}
				        }
				    },
				    yAxis: {
				        title: {
				            text: 'PUMP HEAD (m)'
				        },
				        gridLineWidth: 1,
				    },
				    tooltip: {
				        headerFormat: '<span style="font-size:10px">{point.key}</span><table>',
				        pointFormat: '<tr><td style="padding:0">Head: </td>' + '<td style="padding:0"><b>{point.y:.1f}m</b></td></tr><tr><td style="padding:0">Flow: </td>' + '<td style="padding:0"><b>{point.x:.1f}m<sup>3</sup>/hr</b></td></tr>',
				        footerFormat: '</table>',
				        shared: true,
				        useHTML: true
				    },
				    plotOptions: {
				        spline: {
				        	marker : {
				                radius: 4,
				                lineColor: '#ff6c00',
				                lineWidth: 1
				            },
				        	lineWidth : 0.7
				        }
				    },
	    			colors : ['#0082d6', '#ff0000', '#ff6c00', '#ff0000', '#cccccc', '#cccccc'],
				    series: [
				    {
				    	name : 'PUMP CURVE',
				    	marker : false,
				        data: [
				        	[curve[0].flow_rate, curve[0].head],
				        	[curve[1].flow_rate, curve[1].head],
				        	[curve[2].flow_rate, curve[2].head],
				        	[curve[3].flow_rate, curve[3].head],
				        	[curve[4].flow_rate, curve[4].head],
				        	[curve[5].flow_rate, curve[5].head],
				        	[curve[6].flow_rate, curve[6].head],
				        	[curve[7].flow_rate, curve[7].head],
				        	[curve[8].flow_rate, curve[8].head],
				        	[curve[9].flow_rate, curve[9].head],
				        	[curve[10].flow_rate, curve[10].head]
				        ]
				    }, 
				    {
				    	name : 'SYSTEM CURVE',
				    	marker : false,
				        data: [
				        	[system[0].flow_rate, system[0].system_head],
				        	[system[1].flow_rate, system[1].system_head],
				        	[system[2].flow_rate, system[2].system_head],
				        	[system[3].flow_rate, system[3].system_head],
				        	[system[4].flow_rate, system[4].system_head],
				        	[system[5].flow_rate, system[5].system_head],
				        	[system[6].flow_rate, system[6].system_head],
				        	[system[7].flow_rate, system[7].system_head],
				        	[system[8].flow_rate, system[8].system_head],
				        	[system[9].flow_rate, system[9].system_head],
				        	[system[10].flow_rate, system[10].system_head]
				        ]
				    },
				    {
				    	name : 'Q1',
				    	marker : {
				    		symbol : 'circle',
				    		radius : 2,
				    		lineColor : '#ff6c00',
				    		lineWidth : 1
				    	},
				        data: [
				        	[duty[0].flow_rate, duty[0].pump_tdh]
				        ]
				    },
				    {
				    	name : 'Q2',
				    	marker : {
				    		symbol : 'circle',
				    		radius : 2,
				    		lineColor : '#ff0000',
				    		lineWidth : 1
				    	},
				        data: [
				        	[leastpoint[0].flow, leastpoint[0].head]
				        ]
				    },
				    {
				    	name : 'Line Q',
				    	marker : false,
				    	tooltip : false,
				    	title : false,
				        data: [
				        	[duty[0].flow_rate, 0],
				        	[duty[0].flow_rate, duty[0].pump_tdh]
				        ]
				    },
				    {
				    	name : 'Line H',
				    	marker : false,
				    	tooltip : false,
				        data: [
				        	[0, duty[0].pump_tdh],
				        	[duty[0].flow_rate, duty[0].pump_tdh]
				        ]
				    }]
				});

				if(power_type == 'ac' || power_type == 'surface'){
					Highcharts.chart('efficiency-curve-report', {
					    chart: {
					        type: 'spline',
					        height: 700
					    },
						credits: {
						    	enabled: true,
						    	text: '<strong>Flow</strong>: ' +duty_efficiency[0].flow_rate+ 'm³/h<br/><strong>Efficiency</strong>: ' +duty_efficiency[0].efficiency+ 'm', 
						    	position: {
						    		align: 'right',
						    		verticalAlign: 'top',
						    		y: 70
						    	},
						    	style: {
						    		color: 'black',
					    			fontSize: '12px'
						    	}
					    	},
					    title: {
					        text: 'PUMP EFFICIENCY CURVE - ' +result.name,
					        style: {
					        	fontSize: "14px"
					        }
					    },
					    xAxis: {
					        title: {
					            text: 'FLOW RATE (m<sup>3</sup>/hr)'
					        },
					        gridLineWidth: 1,
					        labels: {
					        	formatter: function() {
					        		return this.value
					        	}
					        }
					    },
					    yAxis: {
					        title: {
					            text: 'ETA (%)'
					        },
					        gridLineWidth: 1,
					    },
					    tooltip: {
					        headerFormat: '<table>',
					        pointFormat: '<tr><td style="padding:0">ETA: </td>' + '<td style="padding:0"><b>{point.y:.1f}%</b></td></tr><tr><td style="padding:0">Flow: </td>' + '<td style="padding:0"><b>{point.x:.1f}m<sup>3</sup>/hr</b></td></tr>',
					        footerFormat: '</table>',
					        shared: true,
					        useHTML: true
					    },
		    			colors : ['#2ba42b'],
					    plotOptions: {
					        spline: {
					        	marker : {
					                radius: 4,
					                lineColor: '#ff6c00',
					                lineWidth: 1
					            },
					        	lineWidth : 0.7
					        }
					    },
					    series: [{
					    	name : 'PUMP CURVE',
					    	marker : false,
					        data: [
					        	[efficiency[0].flow_rate, efficiency[0].efficiency],
					        	[efficiency[1].flow_rate, efficiency[1].efficiency],
					        	[efficiency[2].flow_rate, efficiency[2].efficiency],
					        	[efficiency[3].flow_rate, efficiency[3].efficiency],
					        	[efficiency[4].flow_rate, efficiency[4].efficiency],
					        	[efficiency[5].flow_rate, efficiency[5].efficiency],
					        	[efficiency[6].flow_rate, efficiency[6].efficiency],
					        	[efficiency[7].flow_rate, efficiency[7].efficiency],
					        	[efficiency[8].flow_rate, efficiency[8].efficiency],
					        	[efficiency[9].flow_rate, efficiency[9].efficiency],
					        	[efficiency[10].flow_rate, efficiency[10].efficiency]
					        ]
					    },
					    {
					    	name : 'E1',
					    	marker : {
					    		symbol : 'circle',
					    		radius : 2,
					    		lineColor : '#ff0000',
					    		lineWidth : 1
					    	},
					        data: [
					        	[duty_efficiency[0].flow_rate, duty_efficiency[0].efficiency]
					        ]
					    }]
					});
				}

	        },
	        complete : function(xhr, status){
	          console.log(xhr);
	        },
	        error : function(status){
	          console.log(status);
	        }
		});
	}

</script>