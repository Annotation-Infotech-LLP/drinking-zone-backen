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
				<li class="active">Bookings</li>
			</ol>
		</div><!--/.row-->
		
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header">Bookings</h1>
			</div>
		</div><!--/.row-->
		<div class="row">
			<div class="col-sm-12">
				<table class="table table-striped table-hover" id="table">
					<thead class="thead-dark">
						<tr>
                            <th>Image</th>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>From</th>
                            <th>StartDate</th>
                            <th>EndDate</th>
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

    <div class="modal fade" id="editModal" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <form id="form">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Delivery Details</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="DeliveryName" class="col-sm-4 col-form-label">Name</label>
                            <div class="col-sm-8">
                                <input type="text" name="DeliveryName" id="DeliveryName" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="DeliveryMobile" class="col-sm-4 col-form-label">Mobile</label>
                            <div class="col-sm-8">
                                <input type="text" name="DeliveryMobile" id="DeliveryMobile" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="DeliveryLine1" class="col-sm-4 col-form-label">Address</label>
                            <div class="col-sm-8">
                                <input type="text" name="DeliveryLine1" id="DeliveryLine1" class="form-control" readonly>
                            </div>
                            <div class="col-sm-offset-4 col-sm-8">
                                <input type="text" name="DeliveryLine2" id="DeliveryLine2" class="form-control" readonly>
                            </div>
                            <div class="col-sm-offset-4 col-sm-8">
                                <input type="text" name="DeliveryLandMark" id="DeliveryLandMark" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="DeliveryPIN" class="col-sm-4 col-form-label">PIN</label>
                            <div class="col-sm-8">
                                <input type="text" name="DeliveryPIN" id="DeliveryPIN" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="DeliveryAddressType" class="col-sm-4 col-form-label">Address Type</label>
                            <div class="col-sm-8">
                                <input type="text" name="DeliveryAddressType" id="DeliveryAddressType" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="DeliveryState" class="col-sm-4 col-form-label">State</label>
                            <div class="col-sm-8">
                                <input type="text" name="DeliveryState" id="DeliveryState" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="DeliveryDistrict" class="col-sm-4 col-form-label">District</label>
                            <div class="col-sm-8">
                                <input type="text" name="DeliveryDistrict" id="DeliveryDistrict" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="Status" class="col-sm-4 col-form-label">Status</label>
                            <div class="col-sm-8">
                                <input type="text" name="Status" id="Status" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="CreatedOn" class="col-sm-4 col-form-label">Booking Time</label>
                            <div class="col-sm-8">
                                <input type="text" name="CreatedOn" id="CreatedOn" class="form-control" readonly>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    </div>




    <script>
    function acceptBooking(id){
        bootbox.confirm({
            message: "Are You Sure to accept this Booking?",
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
                    url:"<?= base_url()?>AjaxCalls/accept_booking/"+id,
                    type:"get",
                    dataType:"json"
                }).done((res)=>{
                    if(res.status)
                        reloadTable();
                    else
                        bootbox.alert('Error While Accepting!!!');
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
                    url: "<?= base_url("FillTable/bookings")?>",
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
    function showAddress(id){
        $.ajax({
            url:"<?= base_url()?>AjaxCalls/get_edit/bookings/"+id,
            type:"get",
            dataType:"json"
        }).done((res)=>{
            $.each(res,function(key,value){
                $('#'+key).val(value);
            });
            $('#hidId').val(res.Id);
            $('#editModal').modal('show');
        }).fail(()=>{
            bootbox.alert("Network Error!!!");
        })
    }
    </script>
</body>

<!-- Mirrored from medialoot.com/preview/lumino/charts.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 19 Aug 2019 07:17:22 GMT -->
</html>
