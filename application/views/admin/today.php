<!DOCTYPE html>
<html>

<!-- Mirrored from medialoot.com/preview/lumino/charts.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 19 Aug 2019 07:17:22 GMT -->
<!-- Added by HTTrack --><meta http-equiv="content-type" content="text/html;charset=utf-8" /><!-- /Added by HTTrack -->
<head>
	<?php require_once "top-css.php"?>
	<style>
		.table .thead-dark th {
			color: #fff;
			background-color: #343a40;
			border-color: #454d55;
		}
		.table thead th {
			vertical-align: bottom;
			border-bottom: 2px solid #dee2e6;
				border-bottom-color: rgb(222, 226, 230);
		}
        .file{
            position:absolute;
            z-index:2;
            top:0;
            left:0;
            filter:alpha(opacity=0);
            -ms-filter:"progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";
            opacity:0;
            background-color:transparent;
            color:transparent;
        }
        .small-ic{
            width:50px;
        }
	</style>
</head>
<body>
	<?php require_once "header.php"?>
	<?php require_once "sidebar.php"?>
		
	<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2 main">
		<div class="row">
			<ol class="breadcrumb">
				<li><a href="<?= base_url().$this->config->item('AdminController');?>/home">
					<em class="fa fa-home"></em>
				</a></li>
				<li class="active">Today (Monthly)</li>
			</ol>
		</div><!--/.row-->
		
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header">Today (Monthly)</h1>
			</div>
		</div><!--/.row-->
		<div class="row">
			<div class="col-sm-12">
				<table class="table table-striped table-hover" id="table">
					<thead class="thead-dark">
						<tr>
                            <th>Id</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Orders (Total)</th>
                            <th>Quantity (Total)</th>
                            <th>Action</th>
						</tr>
					</thead>
					<tbody>
                        <?php foreach($today as $row){
                            ?>
                            <tr>
                                <td><?= $row->Id; ?></td>
                                <td><a href="<?= base_url($row->FilePath)?>" target="_blank"><img src="<?= base_url($row->FilePath)?>" class="small-ic"/></a></td>
                                <td><?= $row->Name;?></td>
                                <td><?= $row->Orders;?></td>
                                <td><?= $row->Quantity;?></td>
                                <td><a class="btn btn-info" href="<?= base_url($this->config->item('AdminController')."/view_today/$row->ProductId")?>"><i class="fa fa-eye"></i> View</a></td>
                            </tr>
                            
                            <?php
                        }?>
					</tbody>
				</table>
			</div>
		</div>
	</div>	<!--/.main-->
    
    <?php require_once "bottom-js.php"?>
    <script>
    var table="";
    $(document).ready( function () {
        table=$('#table').DataTable({ 
            "lengthMenu": [[10,50,100,500],["10","50","100","500"]],
            "ordering": true,
            "searching": true,
            "order": ["1","desc"],
            //Set column definition initialisation properties.
            "columnDefs": [
                { "targets": [ -1 ],"orderable": false},
                { "targets": [ 0 ],"orderable": false,"visible": false}
            ],
        });
    } );
    </script>
</body>

<!-- Mirrored from medialoot.com/preview/lumino/charts.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 19 Aug 2019 07:17:22 GMT -->
</html>
