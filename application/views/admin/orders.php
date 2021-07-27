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
				<li class="active">Orders</li>
			</ol>
		</div><!--/.row-->
		
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header">Orders</h1>
			</div>
		</div><!--/.row-->
		<div class="row">
			<div class="col-sm-12">
				<table class="table table-striped table-hover" id="table">
					<thead class="thead-dark">
						<tr>
                            <th>Order #</th>
                            <th>From</th>
                            <th>Scheduled</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Action</th>
						</tr>
					</thead>
					<tbody>
                        
					</tbody>
				</table>
			</div>
		</div>
	</div>	<!--/.main-->
    
    <?php require_once "bottom-js.php"?>
    <script>
    function updateOrder(id){
        bootbox.confirm({
            message: "Are You Sure to Change Status of this Order?",
            buttons: {
                confirm: {
                    label: 'Yes',
                    className: 'btn-success'
                },
                cancel: {
                    label: 'No',
                    className: 'btn-danger'
                },
            },
            closeButton: false,
            callback: function (result) {
                if(!result)
                    return;
                $.ajax({
                    url:"<?= base_url()?>AjaxCalls/change_order_status/"+id,
                    type:"get",
                    dataType:"json"
                }).done((res)=>{
                    if(res.status)
                        reloadTable();
                    else
                        bootbox.alert('Error While Changing Status!!!');
                }).fail(()=>{
                    bootbox.alert("Network Error!!!");
                });
            }
        });
    }
    var table="";
    $(document).ready( function () {
        table=$('#table').DataTable({ 
            "ajax":{
                    url: "<?= base_url("FillTable/orders")?>",
                    type:  "post",
                },
            "lengthMenu": [[10,50,100,500],["10","50","100","500"]],
            "serverSide": true, //Feature control DataTables' server-side processing mode.
            "ordering": true,
            "searching": true,
            "order": [],
            //Set column definition initialisation properties.
            "columnDefs": [
                { 
                    "targets": [ -1 ],
                    "orderable": false
                }
            ],
        });
    } );
    
    function reloadTable()
    {
        table.ajax.reload(null,false); //reload datatable ajax 
    }
    setInterval(() => {
        reloadTable();
    }, 10000);
    </script>
</body>

<!-- Mirrored from medialoot.com/preview/lumino/charts.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 19 Aug 2019 07:17:22 GMT -->
</html>
