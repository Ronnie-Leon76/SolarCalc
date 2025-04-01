<?php
	
require_once('../config.php');
require_once('../functions.php');

$systempanels = systempanels();
$custompanels = custompanels();

?>
<div class="container-fluid custom-module">
	<div class="row">
		<div class="col-12 custom-add-module">
			<div class="custom-module-form">
				<div class="row">
					<div class="col">
						<h2>Add New Custom Module</h2>
					</div>
				</div>
				<div class="row form-container">
					<div class="col-3">
						<input type="text" class="form-control" name="panel_model" placeholder="Module Name" required="required">
					</div>
					<div class="col">
						<input type="text" class="form-control" name="part_number" placeholder="Part Number">
					</div>
					<div class="col" title="Rated Power">
						<input type="text" class="form-control" name="panel_rated_power_w" placeholder="Watts" required="required">
					</div>
					<div class="col" title="Peak Voltage">
						<input type="text" class="form-control" name="peak_voltage" placeholder="Vmpt" required="required">
					</div>
					<div class="col" title="Open Circuit Voltage">
						<input type="text" class="form-control" name="open_circuit_voltage" placeholder="Voc" required="required">
					</div>
					<div class="col" title="Nominal Voltage">
						<input type="text" class="form-control" name="nominal_voltage" placeholder="Vnv">
					</div>
					<div class="col">
						<input type="text" class="form-control" name="short_circuit_current" placeholder="Isc" required="required">
					</div>
					<div class="col" title="Module Dimenions i.e L(mm) x W(mm)">
						<input type="text" class="form-control" name="module_dimensions" placeholder="L(mm) x W(mm)">
					</div>
					<div class="col-1">
						<button class="btn btn-danger btn-block add-cstom-module">Add</button>
					</div>
				</div>
			</div>
		</div>
		<!-- Show Custom Modules -->
		<div class="col-12">
			<form>
				<input type="hidden" name="getcustommodules" value="getcustommodules">
				<p>Please select the <strong>custom panel</strong> you would like to use. You can select as many as you would like. If the panel you're looking for is not available in the list below below, please add it using the form above.</p>
				<div class="table-responsive">
					<table class="table table-sm table-bordered table-hover">
						<thead>
							<tr>
								<th>#</th>
								<th style="width: 25%; text-align: left;">Module</th>
								<th style="width: 10%">Part Number</th>
								<th style="width: 10%">Rated Power</th>
								<th style="width: 10%">Nominal Voltage</th>
								<th style="width: 10%">Peak Voltage</th>
								<th style="width: 10%">Open Circuit Voltage</th>
								<th style="width: 10%">Dimensions</th>
								<th style="width: 10%">Availability</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($systempanels as $panel){ ?>
							<tr>
								<td><input type="checkbox" name="panel_id[]" value="<?php echo $panel->panel_id; ?>"></td>
								<td class="panel-model" style="text-align: left;"><?php echo $panel->panel_model; ?></td>
								<td><?php echo $panel->part_number ?></td>
								<td><?php echo $panel->panel_rated_power_w ?></td>
								<td><?php echo $panel->nominal_voltage ?></td>
								<td><?php echo $panel->peak_voltage ?></td>
								<td><?php echo $panel->open_circuit_voltage ?></td>
								<td><?php echo $panel->module_dimensions ?></td>
								<td><?php echo $panel->part_number != '' ? '<button class="btn btn-sm btn-block check-availability" title="Click here to check availability" data-partnumber="' .$panel->part_number. '" data-partname="' .$panel->panel_model. '"><i class="far fa-question-circle"></i></button>' : '<button class="btn" disabled title="Can\'t check availability. Missing Part Number"><i class="fas fa-exclamation-triangle"></i></button>' ?></td>
							</tr>
							<?php } ?>
							<tr>
								<td colspan="9" class="table-row-header">Custom Modules / Panels</td>
							</tr>
							<?php if(count($custompanels) > 0){ ?>
							<?php foreach($custompanels as $panel){ ?>
							<tr>
								<td><input type="checkbox" name="panel_id[]" value="<?php echo $panel->panel_id; ?>"></td>
								<td class="panel-model" style="text-align: left;"><?php echo $panel->panel_model; ?><i class="far fa-trash-alt delete-panel" data-panel="<?php echo $panel->panel_id; ?>" title=""></i></td>
								<td><?php echo $panel->part_number ?></td>
								<td><?php echo $panel->panel_rated_power_w ?></td>
								<td><?php echo $panel->nominal_voltage ?></td>
								<td><?php echo $panel->peak_voltage ?></td>
								<td><?php echo $panel->open_circuit_voltage ?></td>
								<td><?php echo $panel->module_dimensions ?></td>
								<td><?php echo $panel->part_number != '' ? '<button class="btn btn-sm btn-block check-availability" title="Click here to check availability" data-partnumber="' .$panel->part_number. '" data-partname="' .$panel->panel_model. '"><i class="far fa-question-circle"></i></button>' : '<button class="btn" disabled title="Can\'t check availability. Missing Part Number"><i class="fas fa-exclamation-triangle"></i></button>' ?></td>
							</tr>
							<?php } ?>
							<?php } else { ?>
							<tr>
								<td colspan="9" class="table-row-message">You do not have any custom modules / panel that you have added. You can add custom module using the form above.</td>
							</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</form>
		</div>
	</div>
</div>