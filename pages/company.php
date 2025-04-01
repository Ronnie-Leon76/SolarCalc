<?php

session_start();

require_once('../config.php');
require_once('../functions.php');
$user = getloggedinuser();

$connection = new mysqli(DB_HOST, DB_USR, DB_PWSD, DB_DB);
// Check if the company exists

if($user->user_origin == 1){
	$query = $connection->query("SELECT * FROM companies WHERE unique_id = '" .$connection->real_escape_string($user->unique_id). "'");
} else {
	$query = $connection->query("SELECT * FROM companies WHERE invitation_code = '" .$connection->real_escape_string($user->invitation_code). "'");
}

if($query->num_rows > 0){
	$company = $query->fetch_object();
} else {
	$company = (Object)array(
		'company_name' 	=> $user->company,
		'postal_address' 	=> '',
		'company_phone' 	=> '',
		'company_email' 	=> '',
		'company_logo' 	=> '',
		'company_status' 	=> '',
		'physical_location' => '',
		'company_website' 	=> ''
	);
}

$statuses = array(
	(Object)array(
		'value' => 1,
		'text' => 'Primary'
	),
	(Object)array(
		'value' => 2,
		'text' => 'Secondary'
	)
);

if($company->company_logo == 'No-Logo.png'){
	$company_logo = '';
} else {
	$company_logo = $company->company_logo;
}

?>
<p>Hello <strong><?php echo $user->fullname ?></strong>, in order to create customized <strong>reports</strong> that you can share with your customers, please update information concerning your company. Please fill the form below.</p>
<form>
	<div class="form-group">
		<input type="hidden" name="action" value="company">
		<input type="text" class="form-control" name="company" value="<?php echo $company->company_name; ?>" placeholder="Please enter your Company / Organization" required="required" <?php echo $user->user_origin == 2 ? 'readonly' : '' ?>>
	</div>
	<div class="form-group">
		<input type="text" class="form-control" name="physical_address" value="<?php echo $company->physical_location; ?>" placeholder="Please enter your company Physical Address" required="required">
	</div>
	<div class="form-group">
		<input type="text" class="form-control" name="postal_address" value="<?php echo $company->postal_address; ?>" placeholder="Please enter your company Postal Address">
	</div>
	<div class="form-group">
		<input type="text" class="form-control" name="phone_number" value="<?php echo $company->company_phone; ?>" placeholder="Please enter your company Phone Number" required="required">
	</div>
	<div class="form-group">
		<input type="email" class="form-control" name="email" value="<?php echo $company->company_email; ?>" placeholder="Please enter your company Email Address" required="required">
	</div>
	<div class="form-group">
		<input type="text" class="form-control" name="website" value="<?php echo $company->company_website; ?>" placeholder="Please enter your Company Website">
	</div>
	<?php if($user->user_origin == 2){ ?>
	<div class="form-group">
		<?php if($company_logo){ ?>
			<div class="logo-holder"><i class="fas fa-times remove-image"></i><img src="img/logos/<?php echo $company->company_logo; ?>" class="img-fluid" /></div>
		<?php } else { ?>
			<div class="logo-holder"><i class="fas fa-plus"></i> Click here to add Logo</div>
		<?php } ?>		
		<input type="hidden" class="form-control" name="logo" value="<?php echo $company_logo; ?>" placeholder="Please select your Company Logo">
	</div>
	<?php } ?>
	<?php if($user->user_origin == 1){ ?>
	<div class="form-group">
		<select name="status" required="required" class="form-control">
			<option value="">-- Set this address as --</option>
			<?php foreach($statuses as $status){ ?>
				<?php if($status->value == $company->company_status){ ?>
				<option value="<?php echo $status->value; ?>" selected="selected"><?php echo $status->text; ?></option>
				<?php } else { ?>
				<option value="<?php echo $status->value; ?>"><?php echo $status->text; ?></option>
				<?php } ?>
			<?php } ?>
		</select>
	</div>
	<?php } ?>
</form>