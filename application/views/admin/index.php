<!DOCTYPE html>
<html>

<!-- Mirrored from medialoot.com/preview/lumino/ by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 19 Aug 2019 07:17:04 GMT -->
<!-- Added by HTTrack --><meta http-equiv="content-type" content="text/html;charset=utf-8" /><!-- /Added by HTTrack -->
<head>
	<?php require_once "top-css.php"?>
	<style>
		a{
			text-decoration:none!important;
		}
	</style>
</head>
<body>
	<?php require_once "header.php"?>
	<?php require_once "sidebar.php"?>
		
	<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
		<div class="row">
			<ol class="breadcrumb">
				<li><a href="#">
					<em class="fa fa-home"></em>
				</a></li>
				<li class="active">Dashboard</li>
			</ol>
		</div><!--/.row-->
		
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header">Dashboard</h1>
			</div>
		</div><!--/.row-->
		
		<div class="panel panel-container">
			<div class="row">
				<div class="col-md-4 no-padding">
					<a href="<?= base_url().$this->config->item('AdminController');?>/orders">
						<div class="panel panel-blue panel-widget border-right">
							<div class="row no-padding"><em class="fa fa-xl fa-cart-arrow-down color-orange"></em>
								<div class="large"><?= $orders?></div>
								<div class="text-muted">Orders</div>
							</div>
						</div>
					</a>
				</div>
				<div class="col-md-4 no-padding">
					<a href="<?= base_url().$this->config->item('AdminController');?>/today">
						<div class="panel panel-teal panel-widget border-right">
							<div class="row no-padding"><em class="fa fa-xl fa fa-truck color-red"></em>
								<div class="large"><?= $today?></div>
								<div class="text-muted">Today</div>
							</div>
						</div>
					</a>
				</div>
				<div class="col-md-4 no-padding">
					<a href="<?= base_url().$this->config->item('AdminController');?>/bookings">
						<div class="panel panel-blue panel-widget border-right">
							<div class="row no-padding"><em class="fa fa-xl fa-book color-gray"></em>
								<div class="large"><?= $bookings?></div>
								<div class="text-muted">Bookings</div>
							</div>
						</div>
					</a>
				</div>
			</div><!--/.row-->
			<div class="row">
				<div class="col-md-6 no-padding">
					<a href="<?= base_url().$this->config->item('AdminController');?>/products">
						<div class="panel panel-teal panel-widget border-right">
							<div class="row no-padding"><em class="fa fa-xl fa fa-bars color-blue"></em>
								<div class="large"><?= $products?></div>
								<div class="text-muted">Products</div>
							</div>
						</div>
					</a>
				</div>
				<div class="col-md-6 no-padding">
					<a href="<?= base_url().$this->config->item('AdminController');?>/users">
						<div class="panel panel-orange panel-widget border-right">
							<div class="row no-padding"><em class="fa fa-xl fa-users color-teal"></em>
								<div class="large"><?= $users?></div>
								<div class="text-muted">Customers</div>
							</div>
						</div>
					</a>
				</div>
			</div>
		</div>
	</div>	<!--/.main-->
	
	<?php require_once "bottom-js.php"?>
		
</body>

<!-- Mirrored from medialoot.com/preview/lumino/ by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 19 Aug 2019 07:17:15 GMT -->
</html>