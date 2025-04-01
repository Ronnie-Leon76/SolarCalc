<?php
// Require Config
require_once('config.php');
require_once('functions.php');

$menu = array();
$login_menu[] = (Object)array(
	'text'		=>		'Create Account',
	'url'		=>		'create',
	'class'		=>		'',
	'status'		=>		true,
	'target'		=>		''
);
$login_menu[] = (Object)array(
	'text'		=>		'Forgot Password',
	'url'		=>		'forgot',
	'class'		=>		'',
	'status'		=>		true,
	'target'		=>		''
);
$login_menu[] = (Object)array(
	'text'		=>		'Updates',
	'url'		=>		'updates',
	'class'		=>		'',
	'status'		=>		true,
	'target'		=>		''
);

$login_footer_menu = array();
$login_footer_menu[] = (Object)array(
	'text'		=>		'© ' .date('Y'). ' Davis & Shirtliff',
	'url'		=>		'https://www.davisandshirtliff.com',
	'class'		=>		'',
	'status'		=>		true,
	'target'		=>		'_blank'
);
$login_footer_menu[] = (Object)array(
	'text'		=>		'About',
	'url'		=>		'https://www.davisandshirtliff.com',
	'class'		=>		'',
	'status'		=>		true,
	'target'		=>		'_blank'
);
$login_footer_menu[] = (Object)array(
	'text'		=>		'FAQs',
	'url'		=>		'https://www.davisandshirtliff.com',
	'class'		=>		'_blank',
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
	'status'	=>		false,
	'target'	=>		'_blank'
);
?>
<?php echo page_header('SOLARCALC | Login'); ?>
<!-- Login Container -->
<div class="wrapper login">
	<div class="container-fluid">
		<div class="row">
			<div class="col-md-4 col-lg-8 col-xl-4 col-sm-10 col-10 form-container">
				<div class="col logo-container p-0">
					<img src="img/Login-Logo.png" class="login-logo img-fluid">
				</div>
				<form class="login-form">
					<div class="form-group">
						<input type="email" name="email" class="form-control form-control-lg" placeholder="Please enter your email address">
					</div>
					<div class="form-group">
						<input type="password" name="password" class="form-control form-control-lg password" placeholder="Please enter your password" autocomplete="off">
					</div>
					<div class="form-group">
						<button class="btn btn-primary btn-lg login-btn" value="login">SIGN IN / LOGIN <i class="fas fa-lock"></i></button>
					</div>
				</form>
				<?php echo flat_menu($login_menu); ?>
			</div>
		</div>
		<div class="footer-login-menu text-center">Built By <strong>Davis & Shirtliff</strong> © <?php echo date('Y') ?></div>
	</div>
</div>
<?php echo page_login_footer(); ?>