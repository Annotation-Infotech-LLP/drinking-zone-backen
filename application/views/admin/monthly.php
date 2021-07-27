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
        .mb-50{
            margin-bottom: 50px;
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
				<li class="active">Monthly</li>
			</ol>
		</div><!--/.row-->
		
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header">Monthly<button id="addButton" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Add New</button></h1>
			</div>
		</div><!--/.row-->
		<div class="row">
			<div class="col-sm-12 mb-50">
				<table class="table table-striped table-hover" id="table">
					<thead class="thead-dark">
						<tr>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Category</th>
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
                        <h4 class="modal-title">Edit Data</h4>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="Category">Category:</label>
                            <select name="Category" id="Category" class="form-control" required>
                                <option value="">-Select Category-</option>
                                <?php foreach($cats as $row){?>
                                <option value="<?= $row->Id?>"><?= $row->Name?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="Product">Product:</label>
                            <select name="Product" id="Product" class="form-control" required>
                                <option value="">-Select Category First-</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="Status">Status:</label>
                            <select name="Status" id="Status" class="form-control" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php require_once "bottom-js.php"?>
    <script>
    $('#addButton').click(()=>{
        $('#hidId').val('0');
        $('#editModal .modal-title').html('Add New');
        $('#editModal').modal('show');
        $('#form')[0].reset();
        $('#File').attr('required',true);
    });
    function editData(id){
        $('#editModal .modal-title').html('Edit Data');
        $('#File').removeAttr('required');
        $.ajax({
            url:"<?= base_url()?>AjaxCalls/get_edit/monthly_products/"+id,
            type:"get",
            dataType:"json"
        }).done((res)=>{
            $('#Category').val(res.CategoryId);
            $.ajax({
                url:"<?php echo base_url()?>AjaxCalls/getProducts/"+$("#Category").val(),
            }).done(function(data){
                $("#Product").html(data);
                $("#Product").val(res.ProductId);
            })
            $('#Status').val(res.Status);
            $('#hidId').val(res.Id);
            $('#editModal').modal('show');
        }).fail(()=>{
            bootbox.alert("Network Error!!!");
        })
    }
    $('#form').submit(function(e){
        e.preventDefault();
        $.ajax({
            url:"<?= base_url()?>AjaxCalls/update_monthly",
            data:new FormData($(this)[0]),
            encType:"multipart/form-data",
            contentType:false,
            processData:false,
            cache:false,
            dataType:"json",
            type:"post"
        }).done((res)=>{
            if(res.status)
            {
                reloadTable();
                $('#editModal').modal('hide');
            }
            else
                bootbox.alert(res.msg);
        }).fail(()=>{
            alert("Network Error!!!");
        });
    });
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
                    url:"<?= base_url()?>AjaxCalls/delete_db_n_file/products/"+id,
                    type:"get",
                    dataType:"json"
                }).done((res)=>{
                    if(res.status)
                        reloadTable();
                    else
                        bootbox.alert('Error While Deleting!!!');
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
                    url: "<?= base_url("FillTable/monthly")?>",
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
                    "targets": [ 0,-1 ],
                    "orderable": false
                },
            ],
        });
    } );
    
    function reloadTable()
    {
        table.ajax.reload(null,false); //reload datatable ajax 
    }
    $('#Category').change(function(){
        console.log(this.value);
        if( $(this).val() == "1" )
        {
            $('#deposit-div').slideDown(500);
            $('#Deposit').attr('required',true);
        }
        else
        {
            $('#deposit-div').slideUp(500);
            $('#Deposit').removeAttr('required');
        }
    });
    $("#Category").change(function(){
        $.ajax({
            url:"<?php echo base_url()?>AjaxCalls/getProducts/"+$(this).val(),
        }).done(function(res){
            $("#Product").html(res);
        }).fail(function(){
            alert("Network Error!!!");
        });
    });
    </script>
</body>

<!-- Mirrored from medialoot.com/preview/lumino/charts.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 19 Aug 2019 07:17:22 GMT -->
</html>
