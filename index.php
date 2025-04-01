<?php
// Require Config
require_once('config.php');
require_once('functions.php');

$cookie = $_COOKIE;
if(!(ISSET($cookie['unique_id']) && ISSET($cookie['unique_token']) && ISSET($cookie['user_origin']))){
	header('location: login');
}

$countries_json = file_get_contents('files/countries.json');
$countries = json_decode($countries_json, true);

$date = date('l, dS M Y');

// Check if this user has a customized company
$user = getloggedinuser();
$company = getcompany($user->user_origin, $user->unique_id, $user->invitation_code);
$defaultcompany = defaultcompany();

if($company){
	if($company->company_status == 1){
		$details = $company;
	} else {
		$details = $defaultcompany;
	}	
} else {
	$details = $defaultcompany;
}

$host = gethost();

$cards = array();

$cards[] = (Object)array(
	'title'		=>	'AC Borehole Pumps',
	'link'		=>	'ac',
	'img'		=>	'ac-pumps.jpg',
	'status'		=>	'0',
	'modal'		=>	'',
	'description'	=>	'Comprehensive sising for AC Borehole System with Borehole Parameters as optional'
);

$cards[] = (Object)array(
	'title'		=>	'Eazy AC Sizing',
	'link'		=>	'easy',
	'img'		=>	'easy-sizing.jpg',
	'status'		=>	'0',
	'modal'		=>	'',
	'description'	=>	'Allows full sizing of AC Solar Pumping Systems by inputting a few parameters to ease the process'
);

$cards[] = (Object)array(
	'title'		=>	'SUNFLO Systems',
	'link'		=>	'sunflo',
	'img'		=>	'sunflo.jpg',
	'status'		=>	'0',
	'modal'		=>	'',
	'description'	=>	'DC Systems based on the SUNFLO range. Mandatory fields are just the Location and Head'
);

// $cards[] = (Object)array(
// 	'title'		=>	'DC Borehole / Well Pumps',
// 	'link'		=>	'dc.php',
// 	'img'		=>	'dc-pumps.jpg',
// 	'status'	=>	'0',
// 	'modal'		=>	''
// );

$cards[] = (Object)array(
	'title'		=>	'Solarization (Electric to Solar)',
	'link'		=>	'solarization',
	'img'		=>	'solarization.jpg',
	'status'		=>	'0',
	'modal'		=>	'',
	'description'	=>	'Simple conversion of Electric Powered Borehole to Solar'
);

$cards[] = (Object)array(
	'title'		=>	'Surface Pumps',
	'link'		=>	'surface',
	'img'		=>	'surface-pumps.jpg',
	'status'		=>	'0',
	'modal'		=>	'',
	'description'	=>	'Comprehensive sising for AC Surface Pumps'
);

$cards[] = (Object)array(
	'title'		=>	'Projects',
	'link'		=>	'#',
	'img'		=>	'projects.jpg',
	'status'		=>	'1',
	'modal'		=>	'projects',
	'description'	=>	''
);

$cards[] = (Object)array(
	'title'		=>	'Customers',
	'link'		=>	'#',
	'img'		=>	'customers.jpg',
	'status'		=>	'1',
	'modal'		=>	'customers',
	'description'	=>	''
);

$cards[] = (Object)array(
	'title'		=>	'My Profile',
	'link'		=>	'#',
	'img'		=>	'profile.jpg',
	'status'		=>	'1',
	'modal'		=>	'account',
	'description'	=>	''
);

$alert = false;
if(isset($_GET['int']) && $_GET['int'] == 1){
	$alert = true;
}

?>
<?php echo page_header('SOLARCALC | Home'); ?>
<?php echo top_nav('SOLARCALC'); ?>
<div class="wrapper main-page">
	<div class="container main-page-cards">
		<div class="row">
			<?php if($alert){ ?>
			<div class="col-12 welcome-message">
				<div class="alert alert-success mt-3" role="alert">
					<h4 class="alert-heading">Congratulations
						<i class="fas fa-star"></i>
						<i class="fas fa-star"></i>
						<i class="fas fa-star"></i>
						<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<i class="fas fa-times"></i>
						</button>
					</h4>
					<hr>
					<p class="mb-0">Hello <strong><?php echo $user->fullname ?></strong>!!! Welcome to the <strong>Dayliff SolarCalc</strong> Solar Sizing Solution from <strong>Davis & Shirtliff</strong>. You can easy reach support on the feedback button on the button right of the page and we shall get back to you ASAP. You can also contact your account manager for support. </p>
					<br />
					<p><strong>Solar Know H₂Ow</strong></p>
				</div>
			</div>
			<?php } ?>
			<?php foreach($cards as $card){ ?>
			<div class="col-3">
				<a class="card card-item" href="<?php echo $card->link ?>" data-status="<?php echo $card->status ?>" title="<?php echo $card->description ?>" data-placement="top" <?php echo $card->modal == '' ? 'data-toggle="tooltip"' : 'data-modal="' .$card->modal. '"' ?>>
					<img src="img/cards/<?php echo $card->img ?>" class="card-img-top img-fluid">
					<div class="card-body">
						<button class="btn btn-block"><?php echo $card->title ?> <i class="fas fa-arrow-right"></i></button>		
					</div>
				</a>
			</div>
			<?php } ?>
		</div>
	</div>
</div>
<!-- Profile and Company Profile Modal -->
<div class="modal fade account-settings" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title"></h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<div class="loading-content">
					<img src="img/loader.gif">
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary close-action" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary save-action">Save</button>
			</div>
		</div>
	</div>
</div>
<!-- End Modal -->
<?php echo page_footer(); ?>