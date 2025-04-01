<?php

require_once('../config.php');
require_once('../functions.php');

$user = getloggedinuser();

$connection = new mysqli(DB_HOST, DB_USR, DB_PWSD, DB_DB);
// Check if the company exists

$companies = array();

if($user->user_origin == 1){
	$query = $connection->query("SELECT * FROM companies WHERE unique_id = '" .$connection->real_escape_string($user->unique_id). "'");
	if($query->num_rows > 0){
		$companies[] = $query->fetch_object();
	}
	$companies[] = defaultcompany();
} else {
	$query = $connection->query("SELECT * FROM companies WHERE invitation_code = '" .$connection->real_escape_string($user->invitation_code). "'");
	$companies[] = $query->fetch_object();
}

// $company = getcompanies($user->unique_id);

// $companies = array();

// $companies[] = defaultcompany();

// if($company){
// 	$companies[] = $company;
// }

?>
<p>Please search for the customer below and fill in the details below before proceeding</p>
<div class="alert alert-warning save-project-alert">
	<p>Please make sure that you add the customer to your <strong>customer list</strong> before saving your <strong>project</strong>. This will allow you to create another project for that customer easily in the future. Once done, you can proceed to <strong>save</strong> your project.</p>
	<hr />
	<p>Make sure also that you've updated your <strong>company</strong> details.</p>
</div>
<form>
	<div class="form-group customer-name-group">
		<input type="hidden" name="action" value="saveproject">
		<input type="text" name="customer_name" class="form-control customer-name" placeholder="Please enter the customer name to search below" required="required">
		<input type="hidden" name="customer_id">
		<input type="hidden" name="customer_name_search">
		<div class="dropdown-menu"></div>
	</div>
	<div class="form-group">
		<select name="company_id" class="form-control" required="required">
			<!-- <option value="">-- Select Company --</option> -->
			<?php foreach($companies as $company){ ?>
				<option value="<?php echo $company->company_id ?>" selected="selected"><?php echo $company->company_name ?></option>
			<?php } ?>
		</select>
	</div>
	<div class="form-group">
		<input type="text" name="project_name" class="form-control" placeholder="Please enter the project name below" required="required">
	</div>
	<div class="form-group">
		<textarea placeholder="Please enter any notes of the project" class="form-control project-notes" name="project_notes" required="required"></textarea>
	</div>
</form>