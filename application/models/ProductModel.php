<?php

defined('BASEPATH') or exit('No direct script access allowed');
class ProductModel extends ANNOT_Model
{
    function __construct()
    {
        parent::__construct();
    }
    function get_categories()
    {
        $res = $this->db
            ->select("Id,Name")
            ->get('category')
            ->result_array();
        return json_encode(array('status' => count($res) > 0 ? TRUE : FALSE, 'data' => $res));
    }
    function get_all_products()
    {
        $category = $this->input->post('CategoryId');
        if (!empty($category))
            $this->db->where('CategoryId', $category);
        $res = $this->db
            ->select("products.Id,products.Name,CategoryId,CT.Name AS Category,Deposit,Price,UptoQuantity,CONCAT('" . base_url() . "',FilePath) AS FilePath,MaxQuantity,MinQuantity")
            ->join("product_prices", "ProductId = products.Id")
            ->join("category AS CT", "products.CategoryId = CT.Id")
            ->group_by("ProductId")
            ->having("UptoQuantity = MIN(UptoQuantity) AND ProductId = products.Id")
            ->order_by('products.CategoryId')
            ->get_where('products', "Status = 'Active'")
            ->result_array();
        return json_encode(array('status' => count($res) > 0 ? TRUE : FALSE, 'data' => $res));
    }
    function get_price_chart()
    {
        $productId  =   $this->input->post("ProductId");
        $res = $this->db
            ->select("Price,UptoQuantity")
            ->order_by("UptoQuantity")
            ->get_where("product_prices", "ProductId = '$productId'")
            ->result_array();
        return json_encode(array('status' => count($res) > 0 ? TRUE : FALSE, 'data' => $res));
    }
    function update_cart()
    {
        $id         = $this->input->post("Id");
        $userId     = $this->input->post("UserId");
        $productId  = $this->input->post("ProductId");
        $deviceId   = $this->input->post("DeviceId");

        if ($this->db->where("Id = '$productId'")->count_all_results("products") < 1)
            return json_encode(array("status" => FALSE, 'msg' => "Invalid ProductId"));

        $quantity   = $this->input->post("Quantity");
        if ($this->db->where("MaxQuantity < '$quantity' AND Id = '$productId'")->count_all_results("products") > 0)
            return json_encode(array("status" => FALSE, 'msg' => "Sorry!!! Quantity Exceeds Limit."));
        if ($this->db->where("MinQuantity > '$quantity' AND Id = '$productId'")->count_all_results("products") > 0)
            return json_encode(array("status" => FALSE, 'msg' => "Sorry!!! Please Rise Up to Minimum Quantity ."));

        $data = array(
            'ProductId' => $productId,
            'UserId'    => $userId,
            'DeviceId'  => $deviceId,
            'Quantity'  => $quantity,
            'IsFresh'   => $this->input->post("IsFresh")
        );
        if (empty($id)) {
            if ($this->db->where("((UserId = '$userId' AND UserId != '' AND UserId IS NOT NULL AND UserId != 0) OR (DeviceId = '$deviceId' AND DeviceId != '' AND DeviceId IS NOT NULL)) AND ProductId = '$productId'")->count_all_results("cart") > 0)
                return json_encode(array('status' => FALSE, 'msg' => "This Product Already Exists in Your Cart"));
            $this->db->insert("cart", $data);
            return json_encode(array('status' => TRUE, 'msg' => 'Successfully Added to Cart'));
        }
        $this->db->update("cart", $data, "Id = '$id' AND ((UserId = '$userId' AND UserId != '' AND UserId IS NOT NULL AND UserId != 0) OR (DeviceId = '$deviceId' AND DeviceId != '' AND DeviceId IS NOT NULL))");
        return json_encode(array('status' => TRUE, 'msg' => "Successfully Updated the Cart"));
    }
    function view_cart()
    {
        $userId     =   $this->input->post("UserId");
        $deviceId   =   $this->input->post("DeviceId");
        $res        =   $this->db
            ->select("CT.Id,PD.Id AS ProductId,PD.Name,PR.Price,PD.Deposit,CONCAT('" . base_url() . "',PD.FilePath) AS FilePath,PD.Status AS ProductStatus,CT.Quantity,CT.IsFresh,PR.UptoQuantity")
            ->join("products AS PD", "CT.ProductId = PD.Id")
            ->join("product_prices AS PR", "PR.ProductId = CT.ProductId")
            ->order_by("UptoQuantity")
            ->order_by("CT.Id", "DESC")
            ->get_where("cart AS CT", "((CT.UserId = '$userId' AND CT.UserId != '' AND CT.UserId IS NOT NULL AND CT.UserId != 0) OR (CT.DeviceId = '$deviceId' AND CT.DeviceId != '' AND CT.DeviceId IS NOT NULL)) AND CT.Quantity <= PR.UptoQuantity")
            ->result_array();
        $cart = array();
        $total = 0;
        foreach ($res as $value) {
            $flag = FALSE;
            foreach ($cart as $row) {
                if ($row['Id'] == $value["Id"])
                    $flag = TRUE;
            }
            if ($flag)
                continue;
            $value['SubTotal'] = 0;
            if ($value['ProductStatus'] == "Active") {
                if ($value['IsFresh'] != "Yes")
                    $value['Deposit'] = 0;
                $value['SubTotal'] += $value['Quantity'] * ($value['Price'] + $value['Deposit']);
            }
            $total += $value['SubTotal'];
            $cart[] = $value;
        }
        return json_encode(array('status' => count($res) > 0 ? TRUE : FALSE, 'data' => $cart, 'Total' => $total));
    }
    function delete_cart()
    {
        $id =   $this->input->post("Id");
        $userId =   $this->input->post("UserId");
        $deviceId   =   $this->input->post("DeviceId");
        $this->db->delete("cart", "Id = '$id' AND ((UserId = '$userId' AND UserId != '' AND UserId IS NOT NULL AND UserId != 0) OR (DeviceId = '$deviceId' AND DeviceId != '' AND DeviceId IS NOT NULL))");
        if ($this->db->affected_rows() > 0)
            return json_encode(array('status' => TRUE, 'msg' => 'Succesfully Item Removed from the Cart'));
        else
            return json_encode(array('status' => FALSE, 'msg' => 'Opps!!! Try again Later...'));
    }
    function place_order()
    {
        $userId =   $this->input->post("UserId");
        $this->db->delete("orders", "Confirmed = 'No' AND UserId = '$userId'");
        $cart = array();
        $cartIds = array();
        foreach (json_decode($this->view_cart())->data as $row) {
            if ($row->ProductStatus == "Active") {
                $cartIds[] = $row->Id;
                $cartRow = array();
                $cartRow["ProductId"] = $row->ProductId;
                $cartRow["Quantity"] = $row->Quantity;
                $cartRow["IsFresh"] = $row->IsFresh;
                $cartRow["Price"] = $row->Price;
                $cartRow["Deposit"] = $row->Deposit;
                $cartRow["SubTotal"] = $row->SubTotal;
                $cart[] = $cartRow;
            }
        }
        if (count($cart) < 1)
            return json_encode(array("status" => FALSE, "msg" => "Sorry!!! No Available Products in Your Cart."));
        $orderNum   = "DRNKZN_" . rand(10, 99);
        $addressId  = $this->input->post("AddressId");
        $address    = $this->db->get_where("address", "Id = '$addressId' AND UserId = '$userId'");
        if ($address->num_rows() < 1)
            return json_encode(array("status" => FALSE, "msg" => "Sorry!!! Problem With Delivery Address."));
        $address    =   $address->row();
        if (!in_array($address->PIN, $this->config->item("PinCodes")))
            return json_encode(array("status" => FALSE, "msg" => "Sorry!!! Delivery not available in this Pin Code"));
        $data   = array(
            "OrderNumber"           => $orderNum,
            "UserId"                => $userId,
            "Confirmed"             => "No",
            "DeliveryTime"          => $this->input->post("DeliveryTime"),
            "IsQuick"               => $this->input->post("IsQuick"),
            "Status"                => "Ordered",

            'DeliveryMobile'        =>  $address->Mobile,
            'DeliveryName'          =>  $address->Name,
            'DeliveryLine1'         =>  $address->Line1,
            'DeliveryLine2'         =>  $address->Line2,
            'DeliveryLandMark'      =>  $address->LandMark,
            'DeliveryPIN'           =>  $address->PIN,
            'DeliveryAddressType'   =>  $address->AddressType,
            'DeliveryState'         =>  $this->db->select("Name")->get_where("states", "Id = '$address->StateId'")->row("Name"),
            'DeliveryDistrict'      =>  $this->db->select("Name")->get_where("districts", "Id = '$address->DistrictId'")->row("Name"),

            "CreatedOn"             => date("Y-m-d H:i:s"),
            "CreatedBy"             => $this->input->ip_address(),
            "ModifiedOn"            => date("Y-m-d H:i:s"),
            "ModifiedBy"            => $this->input->ip_address()
        );
        $isFirst    =   FALSE;
        if ($this->db->where("UserId", $userId)->count_all_results("orders") == 0 && $this->db->where("UserId", $userId)->count_all_results("bookings") == 0)
            $isFirst =   TRUE;
        $this->db->insert("orders", $data);
        $orderId    =   $this->db->insert_id();
        $orderNum   =   "DRNKZN_" . $orderId;
        $this->db->update("orders", ["OrderNumber" => $orderNum], ["Id" => $orderId]);
        foreach ($cart as $value) {
            $value["OrderId"]   =   $orderId;
            $this->db->insert("ordered_items", $value);
        }
        $walletEmpty = TRUE;
        if ($isFirst)
            $this->db->query("UPDATE `orders` SET `TotalAmount`=`TotalAmount`-20 WHERE Id = '$orderId'");
        else if ($this->input->post("UseWalletCash") == "Yes") {
            $this->load->helper("custom");
            $closingBalance =   $this->db->select("ClosingBalance")->order_by("CreatedOn", "DSEC")->get_where("transaction_history", "UserId = '$userId'")->row("ClosingBalance");
            $closingBalance =   empty($closingBalance) ? 0 : cover_me($closingBalance, "d");
            if ($closingBalance >= 20) {
                $walletEmpty = FALSE;
                $this->db->query("UPDATE `orders` SET `TotalAmount`=`TotalAmount`-20 WHERE Id = '$orderId'");
            }
        }
        if ($this->input->post("IsQuick") == "Yes")
            $this->db->query("UPDATE `orders` SET `TotalAmount`=`TotalAmount`+20 WHERE Id = '$orderId'");
        $bp = array(
            "MyId"      =>  $orderNum,
            "UserId"    =>  $userId,
            "Amount"    =>  $this->db->select("TotalAmount")->get_where("orders", "Id = '$orderId'")->row("TotalAmount"),
            "Data"      =>  json_encode(
                array(
                    "IsFirst"       => $isFirst,
                    "WalletEmpty"   => $walletEmpty,
                    "OrderId"       => $orderId,
                    "OrderNumber"   => $orderNum,
                    "Items"         => count($cart),
                    "CartIds"       => $cartIds,
                )
            ),
            "CreatedOn" =>  date("Y-m-d H:i:s"),
            "CreatedBy" =>  $this->input->ip_address()
        );
        $this->db->insert("before_payment", $bp);
        return json_encode(array("status" => TRUE, "OrderId" => $orderNum));
    }
    function pay_now()
    {
        $myId =   $this->input->post("MyId");
        $userId =   $this->input->post("UserId");
        if (empty($myId))
            $myId = $this->db->order_by("Id", "DESC")->select("OrderNumber")->get_where("orders", ["UserId" => $userId])->row("OrderNumber");
        $amount =   $this->db->order_by("CreatedOn", "DESC")->get_where("before_payment", "MyId = '$myId' AND UserId = '$userId'")->row("Amount");
        return json_encode(array("status" => empty($amount) ? FALSE : TRUE, "Amount" => $amount));
    }
    function confirm_order()
    {
        $file = fopen("logs/called_api.log", "a+");
        fputs($file, json_encode(["Date" => date("Y-m-d H:i:s"), "IP" => $this->input->ip_address() ?? "self", "Data" => $_POST]) . "\n\n");
        fclose($file);
        $userId =   $this->input->post("UserId");
        $myId =   $this->input->post("MyId");
        $payment =   $this->input->post("PaymentStatus");
        if ($payment == "Paid")
            return json_encode(array("status" => TRUE, "msg" => "Order Placed Succesfully...You will get an sms once the payment confirmed by our system"));
        $user   =   $this->db->get_where("users", "Id = '$userId'")->row();
        if (empty($myId))
            $myId = $this->db->order_by("Id", "DESC")->select("OrderNumber")->get_where("orders", ["UserId" => $userId])->row("OrderNumber");
        $bp     =   json_decode($this->db->select("Data")->get_where("before_payment", "UserId = '$userId' AND MyId = '$myId'")->row("Data"));
        if (empty($bp))
            return json_encode(array("status" => FALSE, "msg" => "No Data Error!!!"));
        $this->db->update("orders", array("PaymentStatus" => $payment, "Confirmed" => "Yes"), "Id = '$bp->OrderId'");
        $status_confirm =   $this->db->affected_rows() > 0;
        if ($bp->IsFirst) {
            $referredBy =   $this->db->select("ReferredBy")->get_where("users", "Id = '$userId'")->row("ReferredBy");
            $referredBy =   $this->db->select("Id")->get_where("users", "ReferralCode = '$referredBy'")->row("Id");
            if (!empty($referredBy)) {
                $closingBalance =   $this->db->order_by("CreatedOn", "DESC")->select("ClosingBalance")->get_where("transaction_history", "UserId = '$referredBy'")->row("ClosingBalance");
                $this->load->helper("custom");
                $closingBalance =   empty($closingBalance) ? 0 : cover_me($closingBalance, "d");
                $closingBalance += 20;
                $closingBalance = cover_me($closingBalance);
                $history = array(
                    "UserId"            => $referredBy,
                    "Title"             => "Refering Bonus of $user->FirstName $user->LastName",
                    "Type"              => "Credit",
                    "Amount"            => 20,
                    "ClosingBalance"    => $closingBalance,
                    "CreatedOn"         => date("Y-m-d H:i:s"),
                    "CreatedBy"         => $this->input->ip_address()
                );
                $this->db->insert("transaction_history", $history);
                $this->db->update("users", array("CurrentBalance" => $closingBalance), "Id = '$referredBy'");
            }
        } else if (!$bp->WalletEmpty) {
            $this->load->helper("custom");
            $closingBalance =   $this->db->select("ClosingBalance")->order_by("CreatedOn", "DSEC")->get_where("transaction_history", "UserId = '$userId'")->row("ClosingBalance");
            $closingBalance =   empty($closingBalance) ? 0 : cover_me($closingBalance, "d");
            $closingBalance -= 20;
            $closingBalance = cover_me($closingBalance);
            $history = array(
                "UserId"            => $userId,
                "Title"             => "Used for " . $bp->OrderNumber,
                "Type"              => "Debit",
                "Amount"            => 20,
                "ClosingBalance"    => $closingBalance,
                "CreatedOn"         => date("Y-m-d H:i:s"),
                "CreatedBy"         => $this->input->ip_address()
            );
            $this->db->insert("transaction_history", $history);
            $this->db->update("users", array("CurrentBalance" => $closingBalance), "Id = '$userId'");
        }



        if ($status_confirm) {
            $additional_message = "";
            $now =  date("H");
            if ($now >= 18)
                $additional_message =   "Your order will be delivered by tomorrow after 10 am.";
            else if ($now <= 9)
                $additional_message =   "Your order will be delivered after 10 am.";
            $msg    =   "Hello $user->FirstName $user->LastName,\nYour Order $bp->OrderNumber for $bp->Items item(s) is Placed Succesfully.\nAmount : " . $this->db->get_where("orders", "Id = '$bp->OrderId'")->row('TotalAmount') . ".00\nTime : " . date("d-m-Y h:i:s a") . (empty($additional_message) ? "" : "\n$additional_message");
            $this->sendSMS($user->Mobile, $msg);
            $this->db->delete("cart", "Id IN (" . implode(",", $bp->CartIds) . ")");
            $this->db->delete("before_payment", "UserId = '$userId' AND MyId = '$myId'");
            // $headers = "MIME-Version: 1.0" . "\r\n";
            // $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            // // More headers
            // $headers .= 'From: <noreply@annotation.co.in>' . "\r\n";
            // $mailRes    =   mail("drinkingzone.v@gmail.com","New Order Palced : $bp->OrderNumber","New Order $bp->OrderNumber for $bp->Items item(s) is Placed Succesfully.<br/>Amount : ".number_format($this->db->get_where("orders","Id = '$bp->OrderId'")->row('TotalAmount'),2)."<br/>Time : ".date("d-m-Y h:i:s a")."<br>Payment Status : ".$payment,$headers);
            $this->load->helper("php_mailer_helper");
            sendEmail("drinkingzone.v@gmail.com", "New Order Palced : $bp->OrderNumber", "New Order <b>$bp->OrderNumber</b> for <b>$bp->Items</b> item(s) is Placed Succesfully.<br/>Amount : <b>" . number_format($this->db->get_where("orders", "Id = '$bp->OrderId'")->row('TotalAmount'), 2) . "</b><br/>Time : <b>" . date("d-m-Y h:i:s a") . "</b><br>Payment Status : <b>" . $payment . "</b>");

            return json_encode(array("status" => TRUE, "msg" => "Order Placed Succesfully... $additional_message"));
        }
        return json_encode(array("status" => FALSE, "msg" => "There is Some Error!!!"));
    }
    function cancel_confirm()
    {
        $userId =   $this->input->post("UserId");
        $myId =   $this->input->post("MyId");
        $this->db->delete("before_payment", "UserId = '$userId' AND MyId = '$myId'");
        $this->db->delete("orders", "Confirmed = 'No' AND UserId = '$userId' AND OrderNumber = '$myId'");
        $this->db->delete("bookings", "Confirmed = 'No' AND UserId = '$userId' AND BookingNumber = '$myId'");
        return json_encode(array("status" => TRUE, "msg" => "Cancelled Success"));
    }
    function view_orders()
    {
        $userId =   $this->input->post("UserId");
        $order  =   $this->db
            ->order_by("Id", "DESC")
            ->select("Id,OrderNumber,TotalAmount,PaymentStatus,DeliveryTime,DeliveryName,IsQuick,Status")
            ->get_where("orders", "UserId = '$userId' AND Confirmed = 'Yes'");
        if ($order->num_rows() < 1)
            return json_encode(array("status" => FALSE, "msg" => "Sorry!!! There is no order history for you."));
        return json_encode(array("status" => TRUE, "msg" => "Success", "data" => $order->result_array()));
    }
    function view_single_order()
    {
        $id     =   $this->input->post("Id");
        $userId =   $this->input->post("UserId");
        $order  =   $this->db
            ->select("Id,OrderNumber,TotalAmount,PaymentStatus,DeliveryTime,DeliveryName,DeliveryLine1,DeliveryLine2,DeliveryLandMark,DeliveryMobile,DeliveryPIN,DeliveryAddressType,DeliveryDistrict,DeliveryState,Status")
            ->get_where("orders", "Id = '$id' AND UserId = '$userId'");
        if ($order->num_rows() < 1)
            return json_encode(array("status" => FALSE, "msg" => "Sorry!!! Problem With Order Id."));
        $order  =   $order->row_array();
        $order["CanBeCancelled"]    =   ($order["Status"] == "Ordered" || $order["Status"] == "Approved") ? "Yes" : "No";
        $items  =   $this->db
            ->select("OI.*,PD.Name,CONCAT('" . base_url() . "',PD.FilePath) AS FilePath,CT.Name AS Category")
            ->join("products AS PD", "OI.ProductId = PD.Id")
            ->join("category AS CT", "PD.CategoryId = CT.Id")
            ->get_where("ordered_items AS OI", "OI.OrderId = '$id'")
            ->result_array();
        return json_encode(array("status" => TRUE, "msg" => "Success", "data" => array("order" => $order, "items" => $items)));
    }
    function cancel_order()
    {
        $id     =   $this->input->post("Id");
        $userId =   $this->input->post("UserId");
        $values =   array("Cancelled", "Ordered", "Approved", "Packed", "Shipped", "Delivered");
        $curr   =   $this->db->select("Status")->get_where("orders", "Id = '$id' AND UserId = '$userId'");
        if ($curr->num_rows() < 1)
            return json_encode(array("status" => FALSE, "msg" => "Sorry!!! Problem With Order Id."));
        $curr   =   $curr->row();
        $val    =   array_search($curr->Status, $values);
        if ($val > 2)
            return json_encode(array('status' => FALSE, 'msg' => 'Sorry!!! Can\'t Cancel Now.'));
        $data   =   array(
            "Status"        => "Cancelled",
            "ModifiedOn"    => date("Y-m-d H:i:s"),
            "ModifiedBy"    => $this->input->ip_address()
        );
        $this->db->update("orders", $data, "Id = '$id'");
        if ($this->db->affected_rows() < 1)
            return json_encode(array('status' => FALSE, 'msg' => 'Sorry!!! No Change Made.'));
        return json_encode(array('status' => TRUE, 'msg' => 'Success!!! Order Cancelled.'));
    }
    function get_bookable_products()
    {
        $res =  $this->db
            ->select("MP.ProductId AS Id,PD.Name,MP.CategoryId,CT.Name AS Category,PD.Deposit,Price,UptoQuantity,CONCAT('" . base_url() . "',PD.FilePath) AS FilePath,MaxQuantity,MinQuantity")
            ->join("products AS PD", "MP.ProductId = PD.Id")
            ->join("product_prices AS PR", "MP.ProductId = PD.Id AND PR.ProductId = MP.ProductId")
            ->join("category AS CT", "MP.CategoryId = CT.Id")
            ->group_by("PR.ProductId")
            ->having("UptoQuantity = MIN(UptoQuantity)")
            ->order_by('PD.Id', 'DESC')
            ->get_where("monthly_products AS MP", "MP.Status = 'Active'")
            ->result_array();
        return json_encode(array('status' => count($res) > 0 ? TRUE : FALSE, 'data' => $res));
    }
    function book_now()
    {
        $userId =   $this->input->post("UserId");
        $bookingNum =   "DRNKBK_";


        $quantity   = $this->input->post("Quantity");
        $productId  = $this->input->post("ProductId");

        if ($this->db->where("MaxQuantity < '$quantity' AND Id = '$productId'")->count_all_results("products") > 0)
            return json_encode(array("status" => FALSE, 'msg' => "Sorry!!! Quantity Exceeds Limit."));
        if ($this->db->where("MinQuantity > '$quantity' AND Id = '$productId'")->count_all_results("products") > 0)
            return json_encode(array("status" => FALSE, 'msg' => "Sorry!!! Please Rise Up to Minimum Quantity ."));


        $addressId  = $this->input->post("AddressId");
        $address    = $this->db->get_where("address", "Id = '$addressId' AND UserId = '$userId'");
        if ($address->num_rows() < 1)
            return json_encode(array("status" => FALSE, "msg" => "Sorry!!! Problem With Delivery Address."));
        $address    =   $address->row();

        if (!in_array($address->PIN, $this->config->item("PinCodes")))
            return json_encode(array("status" => FALSE, "msg" => "Sorry!!! Delivery not available in this Pin Code"));

        $data   = array(
            "BookingNumber"         => $bookingNum,
            "UserId"                => $userId,
            "Confirmed"             => "No",
            "Status"                => "Booked",
            "StartDate"             => $this->input->post("StartDate"),
            "EndDate"               => date("Y-m-d", strtotime($this->input->post("StartDate") . " + 30 days")),

            'DeliveryMobile'        =>  $address->Mobile,
            'DeliveryName'          =>  $address->Name,
            'DeliveryLine1'         =>  $address->Line1,
            'DeliveryLine2'         =>  $address->Line2,
            'DeliveryLandMark'      =>  $address->LandMark,
            'DeliveryPIN'           =>  $address->PIN,
            'DeliveryAddressType'   =>  $address->AddressType,
            'DeliveryState'         =>  $this->db->select("Name")->get_where("states", "Id = '$address->StateId'")->row("Name"),
            'DeliveryDistrict'      =>  $this->db->select("Name")->get_where("districts", "Id = '$address->DistrictId'")->row("Name"),


            "CreatedOn"             => date("Y-m-d H:i:s"),
            "CreatedBy"             => $this->input->ip_address(),
            "ModifiedOn"            => date("Y-m-d H:i:s"),
            "ModifiedBy"            => $this->input->ip_address(),
            "ProductId"             => $productId,
            "Quantity"              => $quantity,
            "IsFresh"               => $this->input->post("IsFresh"),
            "AddressId"             => $this->input->post("AddressId"),
        );
        $prd    =   $this->db->get_where("products", "Id = '$productId' AND Status = 'Active'");
        if ($prd->num_rows() < 1)
            return json_encode(array("status" => TRUE, "msg" => "Sorry Product is not available"));
        $prd = $prd->row();
        $data["ProductName"]    =   $prd->Name;


        $data["Price"]  =   $this->db->order_by("UptoQuantity")->get_where("product_prices", "ProductId = '$productId' AND UptoQuantity >= $quantity")->row("Price");
        $data["Deposit"]    = ($data["IsFresh"] == "Yes") ? $prd->Deposit * $quantity : 0;
        $data["SubTotal"]   =   $data["Price"] * $quantity;
        $data["SubTotal"]   *= ($address->AddressType == "Office") ? 26 : 30;
        $data["TotalAmount"]   =   $data["SubTotal"] + $data["Deposit"];

        $isFirst    =   FALSE;
        $walletEmpty =   TRUE;
        if ($this->db->where("UserId", $userId)->count_all_results("orders") == 0 && $this->db->where("UserId", $userId)->count_all_results("bookings") == 0)
            $isFirst =   TRUE;
        $this->db->insert("bookings", $data);
        $bookingId    =   $this->db->insert_id();
        $bookingNum =   $bookingNum . $bookingId;
        $this->db->update("bookings", ["BookingNumber" => $bookingNum], ["Id" => $bookingId]);
        $user   =   $this->db->get_where("users", "Id = '$userId'")->row();
        if ($isFirst) {
            $this->db->query("UPDATE `bookings` SET `TotalAmount`=`TotalAmount`-20 WHERE Id = '$bookingId'");
            $data["TotalAmount"] -= 20;
        } else if ($this->input->post("UseWalletCash") == "Yes") {
            $this->load->helper("custom");
            $closingBalance =   $this->db->select("ClosingBalance")->order_by("CreatedOn", "DSEC")->get_where("transaction_history", "UserId = '$userId'")->row("ClosingBalance");
            $closingBalance =   empty($closingBalance) ? 0 : cover_me($closingBalance, "d");
            if ($closingBalance >= 20) {
                $walletEmpty = FALSE;
                $this->db->query("UPDATE `bookings` SET `TotalAmount`=`TotalAmount`-20 WHERE Id = '$bookingId'");
            }
        }
        $this->db->delete("before_payment", "UserId = '$userId'");
        $bp = array(
            "MyId"      =>  $bookingNum,
            "UserId"    =>  $userId,
            "Amount"    =>  $this->db->select("TotalAmount")->get_where("bookings", "Id = '$bookingId'")->row("TotalAmount"),
            "Data"      =>  json_encode(
                array(
                    "IsFirst"       => $isFirst,
                    "WalletEmpty"   => $walletEmpty,
                    "BookingId"     => $bookingId,
                    "BookingNumber" => $bookingNum,
                    "Quantity"      => $quantity,
                    "Name"          => $prd->Name,
                )
            ),
            "CreatedOn" =>  date("Y-m-d H:i:s"),
            "CreatedBy" =>  $this->input->ip_address()
        );
        $this->db->insert("before_payment", $bp);
        return json_encode(array("status" => TRUE, "OrderId" => $bookingNum));
    }
    function confirm_booking()
    {
        $userId =   $this->input->post("UserId");
        $payment =   $this->input->post("PaymentStatus");
        if ($payment == "Paid")
            return json_encode(array("status" => TRUE, "msg" => "Order Placed Succesfully...You will get an sms once the payment confirmed by our system"));
        $user   =   $this->db->get_where("users", "Id = '$userId'")->row();
        if (empty($myId))
            $myId = $this->db->order_by("Id", "DESC")->select("BookingNumber")->get_where("orders", ["UserId" => $userId])->row("BookingNumber");
        $bp     =   json_decode($this->db->order_by("CreatedOn", "DESC")->select("Data")->get_where("before_payment", "UserId = '$userId' AND MyId '$myId'")->row("Data"));
        if ($bp->IsFirst) {
            $referredBy =   $this->db->select("ReferredBy")->get_where("users", "Id = '$userId'")->row("ReferredBy");
            $referredBy =   $this->db->select("Id")->get_where("users", "ReferralCode = '$referredBy'")->row("Id");
            if (!empty($referredBy)) {
                $closingBalance =   $this->db->order_by("CreatedOn", "DESC")->select("ClosingBalance")->get_where("transaction_history", "UserId = '$referredBy'")->row("ClosingBalance");
                $this->load->helper("custom");
                $closingBalance =   empty($closingBalance) ? 0 : cover_me($closingBalance, "d");
                $closingBalance += 20;
                $closingBalance = cover_me($closingBalance);
                $history = array(
                    "UserId"            => $referredBy,
                    "Title"             => "Refering Bonus of $user->FirstName $user->LastName",
                    "Type"              => "Credit",
                    "Amount"            => 20,
                    "ClosingBalance"    => $closingBalance,
                    "CreatedOn"         => date("Y-m-d H:i:s"),
                    "CreatedBy"         => $this->input->ip_address()
                );
                $this->db->insert("transaction_history", $history);
                $this->db->update("users", array("CurrentBalance" => $closingBalance), "Id = '$referredBy'");
            }
        } else if (!$bp->WalletEmpty) {
            $this->load->helper("custom");
            $closingBalance =   $this->db->select("ClosingBalance")->order_by("CreatedOn", "DSEC")->get_where("transaction_history", "UserId = '$userId'")->row("ClosingBalance");
            $closingBalance =   empty($closingBalance) ? 0 : cover_me($closingBalance, "d");
            $closingBalance -= 20;
            $closingBalance = cover_me($closingBalance);
            $history = array(
                "UserId"            => $userId,
                "Title"             => "Used for $bp->BookingNumber",
                "Type"              => "Debit",
                "Amount"            => 20,
                "ClosingBalance"    => $closingBalance,
                "CreatedOn"         => date("Y-m-d H:i:s"),
                "CreatedBy"         => $this->input->ip_address()
            );
            $this->db->insert("transaction_history", $history);
            $this->db->update("users", array("CurrentBalance" => $closingBalance), "Id = '$userId'");
        }
        $this->db->update("bookings", array("PaymentStatus" => $payment, "Confirmed" => "Yes"), "Id = '$bp->BookingId'");
        if ($this->db->affected_rows() > 0) {
            $msg    =   "Hello $user->FirstName $user->LastName,\nYour Booking $bp->BookingNumber for " . substr($bp->Name, 0, 15) . (substr($bp->Name, 15) ? "..." : "") . " (Qty: $bp->Quantity) for 1 month is Placed Succesfully.\nAmount : " . $this->db->get_where("bookings", "Id = '$bp->BookingId'")->row('TotalAmount') . ".00\nTime : " . date("d-m-Y h:i:s a");
            $this->sendSMS($user->Mobile, $msg);
            $this->db->delete("before_payment", "UserId = '$userId' AND MyId '$myId'");
            return json_encode(array("status" => TRUE, "msg" => "Booked Succesfully..."));
        }
        return json_encode(array("status" => FALSE, "msg" => "There is Some Error!!!"));
    }
    function view_bookings()
    {
        $userId     =   $this->input->post("UserId");
        $booking    =   $this->db
            ->order_by("Id", "DESC")
            ->select("BK.Id,BK.BookingNumber,BK.TotalAmount,BK.Price,BK.Deposit,BK.Quantity,BK.SubTotal,PaymentStatus,DeliveryName,DeliveryLine1,DeliveryLine2,DeliveryLandMark,DeliveryMobile,DeliveryPIN,DeliveryAddressType,DeliveryDistrict,DeliveryState,PD.Name AS ProductName,CONCAT('" . base_url() . "',PD.FilePath) AS FilePath,CT.Name AS Category,StartDate,EndDate,BK.CreatedOn AS BookedOn,BK.Status,BK.AddressId,IF(EndDate < '" . date("Y-m-d", strtotime("+ 7 days")) . "','Yes','No') AS TimeForRenew")
            ->join("products AS PD", "BK.ProductId = PD.Id")
            ->join("category AS CT", "PD.CategoryId = CT.Id")
            ->get_where("bookings AS BK", "UserId = '$userId' AND Confirmed = 'Yes'");
        if ($booking->num_rows() < 1)
            return json_encode(array("status" => FALSE, "msg" => "Sorry!!! There is no booking history for you."));
        return json_encode(array("status" => TRUE, "msg" => "Success", "data" => $booking->result_array()));
    }
    function cancel_booking()
    {
        $id     =   $this->input->post("Id");
        $userId =   $this->input->post("UserId");
        $data   =   array(
            "Status"        => "Cancelled",
            "ModifiedOn"    => date("Y-m-d H:i:s"),
            "ModifiedBy"    => $this->input->ip_address()
        );
        $this->db->update("bookings", $data, "Id = '$id' AND Status != 'Cancelled'");
        if ($this->db->affected_rows() < 1)
            return json_encode(array('status' => FALSE, 'msg' => 'Sorry!!! No Change Made.'));
        return json_encode(array('status' => TRUE, 'msg' => 'Success!!! Booking Cancelled.'));
    }
}
