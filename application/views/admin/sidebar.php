	<div id="sidebar-collapse" class="col-sm-3 col-lg-2 sidebar">
		<div class="profile-sidebar">
			<div class="profile-userpic">
				<img src="<?= base_url()?>img/logo.png" class="img-responsive" alt="">
			</div>
			<div class="profile-usertitle">
				<div class="profile-usertitle-name"><?= $_SESSION['ADMIN']?></div>
				<div class="profile-usertitle-status"><span class="indicator label-success"></span>Online</div>
			</div>
			<div class="clear"></div>
		</div>
		<div class="divider"></div>
		<ul class="nav menu">
			<li><a href="<?= base_url().$this->config->item('AdminController');?>/home"><em class="fa fa-dashboard">&nbsp;</em> Dashboard</a></li>
			<li><a href="<?= base_url().$this->config->item('AdminController');?>/products"><em class="fa fa-bars">&nbsp;</em> Products</a></li>
			<li><a href="<?= base_url().$this->config->item('AdminController');?>/monthly"><em class="fa fa-calendar">&nbsp;</em> Monthly</a></li>
			<li><a href="<?= base_url().$this->config->item('AdminController');?>/bookings"><em class="fa fa-book">&nbsp;</em> Bookings</a></li>
			<li><a href="<?= base_url().$this->config->item('AdminController');?>/today"><em class="fa fa-truck">&nbsp;</em> Today(Monthly)</a></li>
			<li><a href="<?= base_url().$this->config->item('AdminController');?>/orders"><em class="fa fa-cart-arrow-down">&nbsp;</em> Orders</a></li>
			<li><a href="<?= base_url().$this->config->item('AdminController');?>/users"><em class="fa fa-users">&nbsp;</em> Customers</a></li>
			<li><a href="<?= base_url().$this->config->item('AdminController');?>/logout"><em class="fa fa-power-off">&nbsp;</em> Logout</a></li>
		</ul>
	</div><!--/.sidebar-->
	