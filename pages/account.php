<?php

require_once('../config.php');
require_once('../functions.php');
$user = getloggedinuser();

$countries_json = file_get_contents('../data/countries.json');
$countries = json_decode($countries_json, true);

?>
<p>Please find the basic information about your account below. You can update the information below at anytime. Please note that you cannot update your Email because this is your username used to created your profile. If you need to change this, please contact <a href="mailto:contactcenter@dayliff.com">ContactCenter@dayliff.com</a>.</p>
<form>
	<div class="form-group">
		<input type="hidden" name="action" value="account">
		<input type="hidden" name="unique_id" value="<?php echo $user->unique_id; ?>">
		<input type="hidden" name="user_id" value="<?php echo $user->user_id; ?>">
		<input type="text" class="form-control" name="fullname" value="<?php echo $user->fullname; ?>" placeholder="Please enter your full name" required="required">
	</div>
	<div class="form-group">
		<input type="email" class="form-control" name="email" readonly value="<?php echo $user->email; ?>" placeholder="Please enter your email" required="required">
	</div>
	<div class="form-group">
		<input type="text" class="form-control" name="phone" value="<?php echo $user->phone; ?>" placeholder="Please enter your phone number e.g 254711079000" required="required">
	</div>
	<div class="form-group">
		<input type="text" class="form-control" name="company" value="<?php echo $user->company; ?>" placeholder="Please enter your Company / Organization" required="required">
	</div>
	<div class="form-group">		
		<select name="country" class="form-control" required="required">
			<?php foreach($countries as $country){ ?>
				<?php if($country['code'] == $user->country){ ?>
				<option value="<?php echo $country['code']; ?>" selected="selected"><?php echo $country['name']; ?></option>
				<?php } else { ?>
				<option value="<?php echo $country['code']; ?>"><?php echo $country['name']; ?></option>
				<?php } ?>
			<?php } ?>
		</select>
	</div>
</form>