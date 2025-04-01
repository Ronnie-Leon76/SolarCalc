<?php
// Require Config
require_once('config.php');
require_once('functions.php');

$cookie = $_COOKIE;
if((ISSET($cookie['unique_id']) && ISSET($cookie['unique_token']) && ISSET($cookie['user_origin']))){
	header('location: index');
}

$menu = array();
$login_menu[] = (Object)array(
	'text'		=>		'Sign in / Login',
	'url'			=>		'login',
	'class'		=>		'',
	'status'	=>		true,
	'target'	=>		''
);
$login_menu[] = (Object)array(
	'text'		=>		'Forgot Password',
	'url'			=>		'forgot',
	'class'		=>		'',
	'status'	=>		true,
	'target'	=>		''
);
$login_menu[] = (Object)array(
	'text'		=>		'Updates',
	'url'			=>		'updates',
	'class'		=>		'',
	'status'	=>		true,
	'target'	=>		''
);

$login_footer_menu = array();
$login_footer_menu[] = (Object)array(
	'text'		=>		'© ' .date('Y'). ' Davis & Shirtliff',
	'url'		=>		'https://www.davisandshirtliff.com',
	'class'		=>		'',
	'status'	=>		true,
	'target'	=>		'_blank'
);
$login_footer_menu[] = (Object)array(
	'text'		=>		'About',
	'url'		=>		'https://www.davisandshirtliff.com',
	'class'		=>		'',
	'status'	=>		true,
	'target'	=>		'_blank'
);
$login_footer_menu[] = (Object)array(
	'text'		=>		'FAQs',
	'url'		=>		'https://www.davisandshirtliff.com',
	'class'		=>		'',
	'status'	=>		true,
	'target'	=>		'_blank'
);
$login_footer_menu[] = (Object)array(
	'text'		=>		'Tutorials',
	'url'		=>		'https://www.davisandshirtliff.com',
	'class'		=>		'',
	'status'	=>		true,
	'target'	=>		'_blank'
);
$login_footer_menu[] = (Object)array(
	'text'		=>		'Built by D&S',
	'url'		=>		'https://www.davisandshirtliff.com',
	'class'		=>		'',
	'status'	=>		true,
	'target'	=>		'_blank'
);

$countries_json = file_get_contents('files/countries.json');
$countries = json_decode($countries_json, true);

?>
<?php echo page_header('SOLARCALC | Create Account'); ?>
<!-- Login Container -->
<div class="wrapper create">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-4 col-lg-4 col-xl-4 col-sm-10 col-10 form-container">
				<div class="col logo-container p-0">
					<img src="img/Login-Logo.png" class="login-logo img-fluid">
				</div>
				<form class="create-form">
					<div class="row">
						<div class="form-group col-md-6 col-xs-12 pr-md-1 pr-lg-1">
							<input type="text" name="fullname" class="form-control form-control-lg" placeholder="Full name">
						</div>
						<div class="form-group col-md-6 col-xs-12 pl-md-1 pl-lg-1">
							<input type="email" name="email" class="form-control form-control-lg" placeholder="Email address">
						</div>						
					</div>
					<div class="form-group">
							<input type="text" name="phone" class="form-control form-control-lg" placeholder="Phone number e.g 254711079000. Include Country Code Prefix">
					</div>
					<div class="form-group">
						<input type="text" name="invite_code" class="form-control form-control-lg" placeholder="Please enter your Company invitation code" maxlength="11" minlength="11">
					</div>
					<div class="form-group">
						<select name="country" class="form-control form-control-lg">
							<?php foreach($countries as $country){ ?>
								<?php if($country['status'] == true){ ?>
								<option value="<?php echo $country['code']; ?>" selected="selected"><?php echo $country['name']; ?></option>
								<?php } else { ?>
								<option value="<?php echo $country['code']; ?>"><?php echo $country['name']; ?></option>
								<?php } ?>
							<?php } ?>
						</select>
					</div>
					<div class="form-group">
						<input type="password" name="password" class="form-control form-control-lg" placeholder="Please enter your password">
					</div>
					<div class="form-group form-check">
					    <input type="checkbox" name="agree" class="form-check-input" id="agree-input">
					    <label class="form-check-label" for="agree-input">I have read and agree to the <a class="terms-and-conditions" href="https://www.davisandshirtliff.com/terms-and-conditions" target="_blank">Terms & Conditions</a></label>
					  </div>
					<div class="form-group">
						<button class="btn btn-primary btn-lg create-btn">CREATE ACCOUNT <i class="fas fa-plus"></i></button>
					</div>
				</form>
				<?php echo flat_menu($login_menu); ?>
			</div>
		</div>
		<div class="footer-create-menu footer-login-menu text-center">Built By <strong>Davis & Shirtliff</strong> © <?php echo date('Y') ?></div>
	</div>
</div>
<!-- <div class="modal fade terms-and-conditions-modal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Terms & Conditions</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      	<h3>Header One</h3>
        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.</p>
      	<h3>Header Two</h3>
        <p>Contrary to popular belief, Lorem Ipsum is not simply random text. It has roots in a piece of classical Latin literature from 45 BC, making it over 2000 years old. Richard McClintock, a Latin professor at Hampden-Sydney College in Virginia, looked up one of the more obscure Latin words, consectetur, from a Lorem Ipsum passage, and going through the cites of the word in classical literature, discovered the undoubtable source. Lorem Ipsum comes from sections 1.10.32 and 1.10.33 of "de Finibus Bonorum et Malorum" (The Extremes of Good and Evil) by Cicero, written in 45 BC. This book is a treatise on the theory of ethics, very popular during the Renaissance. The first line of Lorem Ipsum, "Lorem ipsum dolor sit amet..", comes from a line in section 1.10.32.</p>
      	<h3>Header Three</h3>
        <p>The standard chunk of Lorem Ipsum used since the 1500s is reproduced below for those interested. Sections 1.10.32 and 1.10.33 from "de Finibus Bonorum et Malorum" by Cicero are also reproduced in their exact original form, accompanied by English versions from the 1914 translation by H. Rackham.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div> -->
<?php echo page_login_footer(); ?>