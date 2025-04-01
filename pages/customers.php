<?php

require_once('../config.php');
require_once('../functions.php');
$user = getloggedinuser();

$countries_json = file_get_contents('../files/countries.json');
$countries = json_decode($countries_json, true);

$customers = getcustomerslist($user->unique_id);

?>
<div class="container customers-list">
	<div class="row">
		<div class="col-12 customer-form">
			<div class="row customer-search-form">
				<div class="col-2 action-form">
					<button class="btn btn-secondary showhide-customer-form">Show New Customer Form <i class="fas fa-eye"></i></button>
				</div>
				<?php if($user->user_origin == 1){ ?>
				<div class="col customer-name-input">
					<input type="text" name="search_nav" class="form-control" placeholder="Search customer from CRM / NAV e.g using Customer Name or Account Number" required="required">
					<div class="dropdown-menu"></div>
				</div>
				<div class="col-1">
					<a href="#" class="btn btn-success search-customer">Search</a>
				</div>
				<?php } ?>
			</div>
			<form class="new-customer-form d-none">
				<div class="row">
					<div class="col-2">
						<input type="text" name="customer_name" class="form-control" placeholder="Customer Name" required="required">
					</div>
					<div class="col-1">
						<input type="text" name="customer_account" class="form-control" placeholder="A/C No.">
					</div>
					<div class="col-1">
						<input type="tel" name="customer_telephone" class="form-control" placeholder="Telephone" required="required">
					</div>
					<div class="col-2">
						<input type="text" name="customer_email" class="form-control" placeholder="Email" required="required">
					</div>
					<div class="col-2">
						<input type="tel" name="customer_physical" class="form-control" placeholder="Physical Location" required="required">
					</div>
					<div class="col-2">
						<input type="tel" name="customer_postal" class="form-control" placeholder="Postal Address">
					</div>
					<div class="col-1">
						<select name="country" class="form-control" required="required">
							<?php foreach($countries as $country){ ?>
								<?php if($country['status'] == true){ ?>
								<option value="<?php echo $country['code']; ?>" selected="selected"><?php echo $country['name']; ?></option>
								<?php } else { ?>
								<option value="<?php echo $country['code']; ?>"><?php echo $country['name']; ?></option>
								<?php } ?>
							<?php } ?>
						</select>
					</div>
					<div class="col-1">
						<a href="#" class="btn btn-success add-new-customer">
							<i class="fas fa-plus"></i> Customer
						</a>
					</div>
				</div>
			</form>
		</div>
<?php if(count($customers) > 0){ ?>
		<table class="table table-striped table-bordered table-sm">
			<thead>
				<th style="width: 20%">Name</th>
				<th style="width: 10%">Account</th>
				<th style="width: 10%">Telephone</th>
				<th style="width: 15%">Email</th>
				<th style="width: 20%">Physical</th>
				<th style="width: 20%">Postal</th>
				<th style="width: 5%">Country</th>
			</thead>
			<tbody>
				<?php foreach($customers as $customer){ ?>
				<tr>
					<td><?php echo $customer->customer_name ?></td>
					<td><?php echo $customer->customer_account ?></td>
					<td><?php echo $customer->customer_telephone ?></td>
					<td><?php echo $customer->customer_email ?></td>
					<td><?php echo $customer->customer_physical ?></td>
					<td><?php echo $customer->customer_postal ?></td>
					<td><?php echo $customer->customer_country ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>
<?php } else { ?>
		<div class="col-12 no-customers">
			<i class="far fa-surprise"></i>
			<p>You've not added any customers</p>
		</div>
<?php } ?>
	</div>
</div>