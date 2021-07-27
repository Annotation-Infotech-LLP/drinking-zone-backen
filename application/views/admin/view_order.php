<!DOCTYPE html>

<html>



<!-- Mirrored from medialoot.com/preview/lumino/charts.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 19 Aug 2019 07:17:22 GMT -->

<!-- Added by HTTrack -->
<meta http-equiv="content-type" content="text/html;charset=utf-8" /><!-- /Added by HTTrack -->

<head>

    <?php require_once "top-css.php" ?>

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

        .file {

            position: absolute;

            z-index: 2;

            top: 0;

            left: 0;

            filter: alpha(opacity=0);

            -ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=0)";

            opacity: 0;

            background-color: transparent;

            color: transparent;

        }

        .small-ic {

            width: 50px;

            display: block;

            margin: auto;

        }

        .card {

            background: #fff;

        }

        .card .card-body {

            padding: 10px;

        }

        .card .card-title {

            text-align: center;

            font-weight: bold;

        }

        .fa.fa-inr {

            font-size: .9em;

        }

        .mb-0 {

            margin-bottom: 0;

        }
    </style>

</head>

<body>

    <?php require_once "header.php" ?>

    <?php require_once "sidebar.php" ?>



    <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2 main">

        <div class="row">

            <ol class="breadcrumb">

                <li><a href="<?= base_url() . $this->config->item('AdminController'); ?>/home">

                        <em class="fa fa-home"></em>

                    </a></li>

                <li><a href="<?= base_url() . $this->config->item('AdminController'); ?>/orders">

                        <em class="fa fa-cart-arrow-down"></em>

                    </a></li>

                <li class="active">Ordered Items</li>

            </ol>

        </div>
        <!--/.row-->



        <div class="row">

            <div class="col-lg-12">

                <?php



                $class = "btn-default";

                if ($order->Status == "Ordered")

                    $class = "btn-warning";

                else if ($order->Status == "Delivered")

                    $class = "btn-success";

                else if ($order->Status == "Cancelled")

                    $class = "btn-danger";

                else if ($order->Status == "Packed")

                    $class = "btn-info";

                else if ($order->Status == "Shipped")

                    $class = "btn-primary";



                ?>

                <h1 class="page-header"><?= $order->OrderNumber ?> - <i class="fa fa-inr"></i> <?= $order->TotalAmount ?><label class="label <?= $class; ?> pull-right"><?= $order->Status ?></label></h1>

            </div>

        </div>
        <!--/.row-->

        <div class="row">

            <div class="col-md-3">

                <div class="card">

                    <div class="card-body">

                        <h4 class="card-title"><?= "$user->FirstName $user->LastName"; ?></h4>

                        <address class="text-center mb-0"><?= $user->Mobile; ?></address>

                        <?php

                        $class = "btn-warning";

                        if ($order->DeliveryAddressType == "Home")

                            $class = "btn-info";

                        ?>

                        <h6 class="text-center">Delivery Details <label class="label <?= $class ?>"><?= $order->DeliveryAddressType; ?></label></h6>

                        <p>

                            <?= $order->DeliveryName ?><br>

                            <?= $order->DeliveryLine1; ?> ,<br />

                            <?= $order->DeliveryLine2; ?>, <?= empty($order->DeliveryLandMark) ? "" : "$order->DeliveryLandMark,"; ?><br />

                            <?= $order->DeliveryDistrict; ?>, <?= $order->DeliveryState; ?><br />

                            <?= "PIN : $order->DeliveryPIN"; ?><br />

                            <?= "Mob : $order->DeliveryMobile"; ?> <br />

                            <?= "Order Date : " . date("j<\s\up>S</\s\up> F Y", strtotime($order->CreatedOn)); ?><br>

                            <?= "Order Time : " . date("h:i:s a", strtotime($order->CreatedOn)); ?><br>

                            <?php 
                                $quick = $order->IsQuick == "Yes" ? "<label class=\"label btn-primary\">Quick</label>" : "";
                                $order->DeliveryTime=empty(strtotime($order->DeliveryTime))?"<label class=\"label btn-default\">Normal</label>":$order->DeliveryTime;
                             ?>

                            <?= "Delivery : " . (!empty($quick) ? $quick : $order->DeliveryTime); ?>

                        </p>

                        <div class="text-center"><strong><?= $order->PaymentStatus ?></strong></div>

                    </div>

                </div>



            </div>

            <div class="col-sm-9">

                <table class="table table-striped table-hover" id="table">

                    <thead class="thead-dark">

                        <tr>

                            <th>Id</th>

                            <th>Image</th>

                            <th>Name</th>

                            <th>Category</th>

                            <th>Price</th>

                            <th>Deposit</th>

                            <th>Quantity</th>

                            <th>Subtotal</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($items as $row) { ?>

                            <tr>

                                <td><?= $row->Id ?></td>

                                <td><a href="<?= base_url($row->FilePath) ?>"><img src="<?= base_url($row->FilePath) ?>" alt="<?= $row->Id ?>" class="small-ic"></a></td>

                                <td><?= $row->Name ?></td>

                                <td><?= $row->Category ?></td>

                                <td><?= $row->Price ?></td>

                                <td><?= $row->Deposit ?></td>

                                <td><?= $row->Quantity ?></td>

                                <td><?= $row->SubTotal ?></td>

                            </tr>

                        <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>
    <!--/.main-->



    <?php require_once "bottom-js.php" ?>

    <script>
        function updateOrder(id) {

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

                callback: function(result) {

                    if (!result)

                        return;

                    $.ajax({

                        url: "<?= base_url() ?>AjaxCalls/change_order_status/" + id,

                        type: "get",

                        dataType: "json"

                    }).done((res) => {

                        if (res.status)

                            location.reload();

                        else

                            bootbox.alert('Error While Changing Status!!!');

                    }).fail(() => {

                        bootbox.alert("Network Error!!!");

                    });

                }

            });

        }

        $(document).ready(function() {

            $('#table').DataTable({

                "processing": true, //Feature control the processing indicator.

                "ordering": true,

                "searching": true,

                "order": [0, 'desc'], //Initial no order.

                //Set column definition initialisation properties.

                "columnDefs": [

                    {
                        "targets": [0],
                        "searchable": false,
                        "orderable": false,
                        "visible": false
                    }, //first hidden coloumn

                    // { "targets": [ -1 ], "orderable": false, "searchable": false,},//last coloumn

                ],

            });

        });
    </script>

</body>



<!-- Mirrored from medialoot.com/preview/lumino/charts.html by HTTrack Website Copier/3.x [XR&CO'2014], Mon, 19 Aug 2019 07:17:22 GMT -->

</html>