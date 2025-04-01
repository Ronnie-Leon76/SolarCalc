<?php

require_once('config.php');
require_once('functions.php');

if(!ISSET($_GET['update_id'])){

	$title = 'System Updates';
	$update_id = false;
	$updates = getUpdates();

} else {

	$update_id = $_GET['update_id'];
	$update = getUpdate($update_id);
	$title = $update->update_title;

}

echo page_header('SOLARCALC | ' .$title);
echo top_nav_update();

?>
<div class="wrapper main-page">
<?php if($update_id) { ?>
	<div class="container">
		<div class="row">
			<div class="col mt-5 pt-5 mb-3 pb-3">
				<img src="img/<?php echo $update->update_image ?>" class="img-fluid">
			</div>
		</div>
		<div class="row">
			<div class="col update-details">
				<h1><?php echo $update->update_title ?></h1>
				<div class="update-content pb-5 mb-5">
					<?php echo $update->update_description; ?>
					<p class="footer-details mt-4 pt-4 pb-5 mb-5"><i class="far fa-calendar-alt"></i> <?php echo date('dS, M Y', strtotime($update->update_date)) ?> | <i class="far fa-user"></i> <?php echo $update->update_author ?></p>		
				</div>
			</div>
		</div>
	</div>

<?php } else { ?>
	<div class="container">
		<div class="row">
			<div class="col mt-5 pt-5 mb-3 pb-3">
				<img src="img/System-Updates.jpg" class="img-fluid">
			</div>
		</div>
		<?php foreach($updates as $update){ ?>
		<?php
			$month = date('M', strtotime($update->update_date));
			$day = date('d', strtotime($update->update_date));
			$year = date('Y', strtotime($update->update_date));
		?>
		<div class="media pt-4 pb-4">
			<div class="col-2 d-sm-block d-none">
				<div class="date-holder text-center">
					<span><?php echo $month ?></span><br />
					<span class="day"><?php echo $day ?></span><br />
					<span><?php echo $year ?></span>
				</div>
			</div>
			<div class="media-body pr-3 pl-3">
				<h2><?php echo $update->update_title ?></h2>
				<p><?php echo $update->update_introduction ?></p>
				<a href="updates?update_id=<?php echo $update->update_id ?>" class="btn btn-outline-danger rounded-0">Read More <i class="fas fa-chevron-right"></i></a>
			</div>
		</div>
		<?php } ?>
	</div>
<?php } ?>
</div>
<?php echo page_login_footer(); ?>