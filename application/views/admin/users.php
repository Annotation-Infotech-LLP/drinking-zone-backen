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
				<li class="active">Customers</li>
			</ol>
		</div><!--/.row-->
		
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header">Customers</h1>
			</div>
		</div><!--/.row-->
		<div class="row">
			<div class="col-sm-12">
				<table class="table table-striped table-hover" id="table">
					<thead class="thead-dark">
						<tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Mobile</th>
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
    
    <div class="modal fade" id="editModal" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <form id="form">
                    <input type="hidden" id="hidId" name="hidId" value="0"/>
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Customer Details</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group row">
                            <label for="FirstName" class="col-sm-4 col-form-label">First Name</label>
                            <div class="col-sm-8">
                                <input type="text" name="FirstName" id="FirstName" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="LastName" class="col-sm-4 col-form-label">Last Name</label>
                            <div class="col-sm-8">
                                <input type="text" name="LastName" id="LastName" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="Mobile" class="col-sm-4 col-form-label">Mobile</label>
                            <div class="col-sm-8">
                                <input type="text" name="Mobile" id="Mobile" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="Email" class="col-sm-4 col-form-label">Email</label>
                            <div class="col-sm-8">
                                <input type="text" name="Email" id="Email" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="Status" class="col-sm-4 col-form-label">Status</label>
                            <div class="col-sm-8">
                                <input type="text" name="Status" id="Status" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="CreatedOn" class="col-sm-4 col-form-label">Registered</label>
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

    <?php require_once "bottom-js.php"?>
    <script>
    function showUser(id){
        $.ajax({
            url:"<?= base_url()?>AjaxCalls/get_edit/users/"+id,
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
    <?php if(isset($_SESSION['ADMIN'])){?>
    function deleteData(id){
        bootbox.confirm({
            message: "Are You Sure to Delete this Data?",
            buttons: {
                confirm: {
                    label: 'Yes',
                    className: 'btn-success'
                },
                cancel: {
                    label: 'No',
                    className: 'btn-danger'
                }
            },
            callback: function (result) {
                if(!result)
                    return;
                $.ajax({
                    url:"<?= base_url()?>AjaxCalls/delete_db/users/"+id,
                    type:"get",
                    dataType:"json"
                }).done((res)=>{
                    if(res.status)
                        reloadTable();
                    else
                        bootbox.alert('<b>Error While Deleting!!!</b><p>Testimonial is still there by this user, Please Delete that first...</p>');
                }).fail(()=>{
                    bootbox.alert("Network Error!!!");
                });
            }
        });
    }
    <?php } ?>
    var table="";
    $(document).ready( function () {
        table=$('#table').DataTable({ 
            "ajax":{
                    url: "<?= base_url("FillTable/users")?>",
                    type:  "post",
                },
            "lengthMenu": [[10,50,100,500],["10","50","100","500"]],
            "serverSide": true, //Feature control DataTables' server-side processing mode.
            "processing": true, //Feature control the processing indicator.
            "ordering": true,
            "searching": true,
            "order": [],
            //Set column definition initialisation properties.
            "columnDefs": [
                { 
                    "targets": [ -1 ],
                    "orderable": false
                },
                { 
                    "targets": [ -1 ],
                    "orderable": false
                },
            ],
        });
    } );
    
    function reloadTable()
    {
        table.ajax.reload(null,false); //reload datatable ajax 
    }

    $('.react-btn').click(function(e){
        e.preventDefault();
        var reaction = $(this).attr('data-reaction');
        bootbox.confirm({
            message: "Are You Sure to " + reaction + " this User?",
            buttons: {
                confirm: {
                    label: 'Yes',
                    className: 'btn-success'
                },
                cancel: {
                    label: 'No',
                    className: 'btn-danger'
                }
            },
            callback: function (result) {
                if(!result)
                    return;
                $.ajax({
                    url:"<?= base_url()?>AjaxCalls/react_client/"+$('#hidId').val()+"/"+reaction,
                    dataType:"json"
                }).done((res)=>{
                    if(res.status)
                        location.reload();
                    else
                        bootbox.alert("Sorry!!! Can't Process...");
                }).fail(()=>{
                    bootbox.alert("Network Error!!!");
                });
            }
        });
        
    });
    </script>
</body>

<!-- Mirrored from medialoot.com/preview/lumino/charts.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 19 Aug 2019 07:17:22 GMT -->
</html>
