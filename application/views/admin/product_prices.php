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
				<li><a href="<?= base_url().$this->config->item('AdminController');?>/products">
					<em class="fa fa-bars"></em>
				</a></li>
				<li class="active">Product Prices</li>
			</ol>
		</div><!--/.row-->
		
		<div class="row">
			<div class="col-lg-12">
				<h1 class="page-header"><?= $Name;?><button id="addButton" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Add New</button></h1>
			</div>
		</div><!--/.row-->
		<div class="row">
			<div class="col-sm-12">
				<table class="table table-striped table-hover" id="table">
					<thead class="thead-dark">
						<tr>
                            <th>Id</th>
                            <th>Price</th>
                            <th>This Pricce Upto (Qty)</th>
                            <th>Action</th>
						</tr>
					</thead>
					<tbody>
                        <?php foreach($prices as $row){?>
						<tr>
                            <td><?= $row->Id?></td>
                            <td><?= $row->Price?></td>
                            <td><?= $row->UptoQuantity?></td>
                            <td>
                                <button class="btn btn-info" onClick="editData('<?= $row->Id?>');"><i class="fa fa-edit"></i> Edit</button>
                                <button class="btn btn-danger" onClick="deleteData('<?= $row->Id?>');"><i class="fa fa-trash"></i> Delete</button>
                            </td>
                        </tr>
                        <?php }?>
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
                            <label for="Price">Price:</label>
                            <input type="number" class="form-control" id="Price" name="Price" placeholder="Enter Price" required/>
                        </div>
                        <div class="form-group">
                            <label for="UptoQuantity">UptoQuantity:</label>
                            <input type="number" class="form-control" id="UptoQuantity" name="UptoQuantity" placeholder="Upto How Much Quantity This Price Applicable" required min="1"/>
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
            url:"<?= base_url()?>AjaxCalls/get_edit/product_prices/"+id,
            type:"get",
            dataType:"json"
        }).done((res)=>{
            $('#Price').val(res.Price);
            $('#UptoQuantity').val(res.UptoQuantity);
            $('#hidId').val(res.Id);
            $('#editModal').modal('show');
        }).fail(()=>{
            bootbox.alert("Network Error!!!");
        })
    }
    $('#form').submit(function(e){
        e.preventDefault();
        $.ajax({
            url:"<?= base_url()?>AjaxCalls/update_product_price/<?= $Id?>",
            data:new FormData($(this)[0]),
            encType:"multipart/form-data",
            contentType:false,
            processData:false,
            cache:false,
            dataType:"json",
            type:"post"
        }).done((res)=>{
            if(res.status)
                location.reload();
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
                    url:"<?= base_url()?>AjaxCalls/delete_product_price/<?= $Id?>/"+id,
                    type:"get",
                    dataType:"json"
                }).done((res)=>{
                    if(res.status)
                        location.reload();
                    else
                        bootbox.alert(res.msg);
                }).fail(()=>{
                    bootbox.alert("Network Error!!!");
                });
            }
        });
    }
    $(document).ready( function () {
        $('#table').DataTable({ 
            "processing": true, //Feature control the processing indicator.
            "ordering": true,
            "searching": true,
            "order": [ 0, 'desc' ] , //Initial no order.
            //Set column definition initialisation properties.
            "columnDefs": [
                { "targets": [0], "searchable": false, "orderable": false, "visible": false },//first hidden coloumn
                { "targets": [ -1 ], "orderable": false, "searchable": false,},//last coloumn
            ],
        });
    } );
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
    </script>
</body>

<!-- Mirrored from medialoot.com/preview/lumino/charts.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 19 Aug 2019 07:17:22 GMT -->
</html>
