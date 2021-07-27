<!DOCTYPE html>
<html>

<!-- Mirrored from medialoot.com/preview/lumino/login.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 19 Aug 2019 07:17:23 GMT -->
<!-- Added by HTTrack --><meta http-equiv="content-type" content="text/html;charset=utf-8" /><!-- /Added by HTTrack -->
<head>
	<?php require_once "top-css.php"?>
</head>
<body>
	<div class="row">
		<div class="col-xs-10 col-xs-offset-1 col-sm-8 col-sm-offset-2 col-md-4 col-md-offset-4">
			<div class="login-panel panel panel-default">
				<div class="panel-heading">Log in</div>
				<div class="panel-body">
					<form role="form" id="LoginForm">
						<fieldset>
							<div class="form-group">
								<input class="form-control" placeholder="Username" name="Username" type="text" autofocus="" required id="Username">
							</div>
							<div class="form-group">
								<input class="form-control" placeholder="Password" name="Password" type="password" required id="Password">
							</div>
							<button class="btn btn-primary" type="submit">Login</button>
						</fieldset>
					</form>
				</div>
			</div>
		</div><!-- /.col-->
	</div><!-- /.row -->	
	
<script src="<?= base_url()?>vendor/jquery/jquery.min.js"></script>
<script src="<?= base_url()?>assets_admin/js/bootstrap.min.js"></script>
<script>
$('#LoginForm').submit((e)=>{
	e.preventDefault();
	$.ajax({
		url:"<?= base_url()?>AjaxCalls/admin_login_check",
        data:"Username="+$('#Username').val()+"&Password="+$('#Password').val(),
        type:"post",
        dataType:"json"
    }).done(function(res){
        if(!res.status)
        {
          alert('Sorry!!! Try again....');
          return;
        }
        window.location.reload();
    }).fail(function(){
        alert('Network Error');
    });
});
</script>
</body>

<!-- Mirrored from medialoot.com/preview/lumino/login.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 19 Aug 2019 07:17:23 GMT -->
</html>
