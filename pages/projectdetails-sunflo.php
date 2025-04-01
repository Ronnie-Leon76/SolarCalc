<?php

require_once('../config.php');
require_once('../functions.php');

$user = getloggedinuser();

$get = (Object)$_GET;

$project = getprojectbyid($user->unique_id, $get->project_id);
$company = getusercompanybyid($project->company_id);
$host = gethost();

$solution = explode('|', $project->project_details->solutionstring);
$irradiation = explode('|', $project->project_details->average_irradiation);
$avg_irradiation = array();
$output = array();

foreach($irradiation as $item){
	array_push($avg_irradiation, (float)$item);
	array_push($output, round($solution[10] * (float)$item, 2));
}

$average_irradiation = json_encode($avg_irradiation);
$wateroutput = json_encode($output);
$date = date('l, dS M Y', strtotime($project->project_date));

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
							<td class="pump-details-table"></td>
						</tr>
						<tr>
							<td><strong>Panels</strong> - <span class="panels-name"><?php echo $solution[2]; ?></span></td>
							<td class="panels-details-content" style="text-align: center;"><?php echo $solution[4]; ?></td>
							<td class="panels-details-table"></td>
						</tr>
						<tr>
							<td><strong>Cable</strong> - <span class="panels-name">2.5mm<sup>3</sup></span></td>
							<td class="panels-details-content" style="text-align: center;"><?php echo $solution[5]; ?>m</td>
							<td class="panels-details-table"></td>
						</tr>
					</tbody>
				</table>
				<table class="table table-sm irradiation-data-table">
					<thead>
						<th style="width: 90%">
							<h3>Daily output in average month - <span class="output-average-month"><?php echo round($irradiation[(count($irradiation) - 1)] * $solution[10], 2) ?> m<sup>3</sup></span></h3>
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
			<div class="col-12">
				<table class="table table-sm output-data-table">
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
							<?php foreach($output as $item){ ?>
							<td><?php echo $item; ?></td>
							<?php } ?>
						</tr>
					</tbody>
				</table>
			</div>
			<h2 class="product-details-header">Products Details</h2>
			<div class="col-12 product-content pump-content">
				<div class="progress">
					<div class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
				</div>
			</div>
			<div class="col-12 product-content panels-content">
				<div class="progress">
					<div class="progress-bar progress-bar-striped progress-bar-animated bg-warning" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
				</div>
			</div>
			<canvas class="irradiation-canvas" id="irradiation-canvas" width="1000px" height="700px"></canvas>
			<canvas class="output-canvas" id="output-canvas" width="1000px" height="700px"></canvas>
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
				<button class="btn btn-primary print-pdf float-right" data-project="<?php echo $get->project_id; ?>">Print Project <i class="far fa-save"></i></button>
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
			type: 'column'
		},
		title: {
			text: 'Direct Normal Irradiation'
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
	var water = <?php echo $wateroutput; ?>;
	Highcharts.chart('output-data-report', {
		chart: {
			type: 'column'
		},
		title: {
			text: 'Monthly Output'
		},
		// subtitle: {
		// 	text: 'Source: NASA.gov POWER Single Point Data Access'
		// },
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
	            text: 'm<sup>3<sup>/day'
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
	        data: [water[0], water[1], water[2], water[3], water[4], water[5], water[6], water[7], water[8], water[9], water[10], water[11], water[12]]

	    }]
	});
	
	getproduct(<?php echo $solution[1]; ?>, '.pump-content', '<?php echo $solution[0]; ?>');
	getproduct(<?php echo $solution[3]; ?>, '.panels-content', '<?php echo $solution[2]; ?>');

	function getproduct(product_id, container, product_name){

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
		        	containercontent += '<h3>' +product_name+ '</h3>';

		        	// Product Images
		        	containercontent += '<div class="product-content-image">';
		        	for(var j = 1; j < data.images.length; j++){
		        		containercontent += '<img src="' +data.images[j].file_image+ '" alt="' +data.images[j].file_name+ '" title="' +data.images[j].file_name+ '" class="img-fluid" />';
		        	}
		        	containercontent += '</div>';

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
	
</script>