<?php

require_once('../config.php');
require_once('../functions.php');
$user = getloggedinuser();

$projects = getprojects($user->unique_id);

?>
<div class="container-fluid custom-module">
	<div class="row">
		<div class="col-12 saved-projects">
			<?php if(count($projects) > 0){ ?>
			<div class="table-responsive">
				<table class="table table-sm table-bordered table-hover">
					<thead>
						<tr>
							<th style="width: 25%; text-align: left;">Project Name</th>
							<th style="width: 20%; text-align: left;">Customer Name</th>
							<th style="width: 30%; text-align: left;">Location Name</th>
							<th style="width: 10%">Delivery Output</th>
							<th style="width: 10%">Project Date</th>
							<td style="width: 5%">Action</td>
						</tr>
					</thead>
					<tbody>
						<?php foreach($projects as $project){ ?>
						<?php
							$product = explode('|', $project->project_details->solutionstring);
						?>
						<tr>
							<td class="project-name" style="text-align: left;"><?php echo $project->project_name; ?><i class="far fa-trash-alt delete-project" data-project="<?php echo $project->project_id; ?>" title=""></i></td>
							<td style="text-align: left;"><?php echo $project->customer->customer_name; ?></td>
							<td style="text-align: left;"><?php echo $project->location_name; ?></td>
							<td><?php echo $project->project_details->delivery_output; ?></td>
							<td><?php echo $project->project_date; ?></td>
							<td>
								<button class="btn btn-light open-project" data-project="<?php echo $project->project_id; ?>" title="View <?php echo $project->project_name ?> details" data-projectname="<?php echo $project->project_name; ?>" data-projecttype="<?php echo $product[9]; ?>" data-projectpower="<?php echo $product[16]; ?>" title="View <?php echo $project->project_name ?> details" data-productdetails="<?php echo $product[1]; ?>" data-toggle="tooltip"><i class="far fa-eye"></i></button>
							</td>
						</tr>
						<?php } ?>
					</tbody>
				</table>
			</div>
			<?php } else { ?>
			<p class="no-projects">You do not have any saved projects. Once you create a new project, you can access it here</p>
			<?php } ?>
		</div>
	</div>
</div>