<?php
class WSAdmin extends ANNOT_Controller
{
    function __construct()
    {
        parent::__construct();
    }
    function index()
    {
        $this->load->view('admin/top-css');
        echo '<script>window.location.replace("' . base_url($this->config->item('AdminController') . "/login") . '")</script>';
    }
    function login()
    {
        if (isset($_SESSION['ADMIN'])) {
            header('location:' . base_url($this->config->item('AdminController') . "/home"));
            return;
        }
        $this->load->view('admin/login');
    }
    function home()
    {
        if (!isset($_SESSION['ADMIN'])) {
            $this->login();
            return;
        }
        $data['products'] = $this->db->count_all_results('products');
        $data['orders'] = $this->db->where("Confirmed", "Yes")->count_all_results('orders');
        $data['users'] = $this->db->count_all_results('users');
        $data['bookings'] = $this->db->where("Confirmed", "Yes")->count_all_results('bookings');
        $data['today'] = $this->db->where(array("Confirmed" => "Yes", "StartDate <=" => date("Y-m-d"), "EndDate >=" => date("Y-m-d")))->count_all_results('bookings');
        $this->load->view("admin/index", $data);
    }

    function users()
    {
        if (!isset($_SESSION['ADMIN'])) {
            $this->login();
            return;
        }
        $this->load->view("admin/users");
    }
    function products()
    {
        if (!isset($_SESSION['ADMIN'])) {
            $this->login();
            return;
        }
        $data['cats'] = $this->db->get('category')->result();
        $this->load->view("admin/products", $data);
    }
    function monthly()
    {
        if (!isset($_SESSION['ADMIN'])) {
            $this->login();
            return;
        }
        $data['cats'] = $this->db->get('category')->result();
        $this->load->view("admin/monthly", $data);
    }
    function product_prices($id)
    {
        if (!isset($_SESSION['ADMIN'])) {
            $this->login();
            return;
        }
        $data = $this->db->get_where("products", "Id = '$id'")->row_array();
        $data['prices'] = $this->db->get_where('product_prices', "ProductId = '$id'")->result();
        $this->load->view("admin/product_prices", $data);
    }
    function orders()
    {
        if (!isset($_SESSION['ADMIN'])) {
            $this->login();
            return;
        }
        $this->load->view("admin/orders");
    }
    function view_order($id)
    {
        if (!isset($_SESSION['ADMIN'])) {
            $this->login();
            return;
        }
        $data['order']  = $this->db->get_where("orders", "Id = '$id'")->row();
        $data['items']  = $this->db
            ->select("OI.*,PD.Name,PD.FilePath,CT.Name AS Category")
            ->join("products AS PD", "OI.ProductId = PD.Id")
            ->join("category AS CT", "PD.CategoryId = CT.Id")
            ->get_where("ordered_items AS OI", "OI.OrderId = '$id'")
            ->result();
        $data['user']   = $this->db->get_where("users", "Id = '{$data['order']->UserId}'")->row();
        $this->load->view("admin/view_order", $data);
    }
    function bookings()
    {
        if (!isset($_SESSION['ADMIN'])) {
            $this->login();
            return;
        }
        $this->load->view("admin/bookings");
    }
    function view_booking($id)
    {
        if (!isset($_SESSION['ADMIN'])) {
            $this->login();
            return;
        }
        $data['booking']  = $this->db->get_where("bookings", "Id = '$id'")->row();
        $data['user']     = $this->db->get_where("users", "Id = '{$data['order']->UserId}'")->row();
        $this->load->view("admin/view_booking", $data);
    }
    function today()
    {
        if (!isset($_SESSION['ADMIN'])) {
            $this->login();
            return;
        }

        $today          =   date("Y-m-d");

        $data["today"]  =   $this->db
            ->select("bookings.Id,FilePath,Name,Count(ProductId) AS Orders,ProductId,SUM(Quantity) AS Quantity")
            ->join("products AS PD", "PD.Id = ProductId")
            ->group_by("ProductId")
            ->get_where("bookings", "Confirmed = 'Yes' AND StartDate <= '$today' AND EndDate >= '$today' AND bookings.Status = 'Approved'")
            ->result();
        $this->load->view("admin/today", $data);
    }
    function view_today($id)
    {
        if (!isset($_SESSION['ADMIN'])) {
            $this->login();
            return;
        }
        $today          =   date("Y-m-d");
        $data['bookings']  = $this->db
            ->select("BK.Id,CONCAT(US.FirstName,' ',US.LastName) AS Name,US.Mobile AS Mobile,DeliveryName,DeliveryMobile,DeliveryLine1,DeliveryLine2,DeliveryLandMark,DeliveryPIN,DeliveryDistrict,DeliveryState,IsFresh,Quantity,DeliveryAddressType,StartDate")
            ->join("users AS US", "US.Id = BK.UserId")
            ->get_where("bookings AS BK", "ProductId = '$id' AND Confirmed = 'Yes' AND StartDate <= '$today' AND EndDate >= '$today' AND BK.Status = 'Approved'")
            ->result();
        $data['product']     = $this->db->get_where("products", "Id = '$id'")->row();
        $this->load->view("admin/view_today", $data);
    }
    function logout()
    {
        unset($_SESSION['ADMIN']);
        header('location:' . base_url());
    }
    function confirm_order()
    {
        $file = fopen("logs/called_api_cc_avenvue.log", "a+");
        fputs($file, json_encode(["Date" => date("Y-m-d H:i:s"), "IP" => $this->input->ip_address()??"self", "Data" => $_POST]) . "\n\n");
        fclose($file);
        require_once 'cc_av/MyHandler.php';
        $decrypt=decrypt_cc($_POST["encResp"]);
        $orderStatus =   $decrypt["order_status"];
        if ($orderStatus != "Success")
            exit("Invalid Payment");
        $myId =   $decrypt["order_id"];
        $payment =   "Paid";
        $this->load->helper("sms");
        $this->load->helper("custom");
        $userId =   $this->db->select("UserId")->get_where("orders", ["OrderNumber" => $myId])->row("UserId");
        $user   =   $this->db->get_where("users", "Id = '$userId'")->row();
        $bp     =   json_decode($this->db->select("Data")->get_where("before_payment", "MyId = '$myId'")->row("Data"));
        if (preg_match("/^DRNKZN_[0-9]+$/", $myId)) {
            
            // FOR ORDERS
            
            if (empty($bp))
                return json_encode(array("status" => FALSE, "msg" => "No Data Error!!!"));
            $this->db->update("orders", array("PaymentStatus" => $payment, "Confirmed" => "Yes"), "Id = '$bp->OrderId'");
            $status_confirm =   $this->db->affected_rows() > 0;
            if ($bp->IsFirst) {
                $referredBy =   $this->db->select("ReferredBy")->get_where("users", "Id = '$userId'")->row("ReferredBy");
                $referredBy =   $this->db->select("Id")->get_where("users", "ReferralCode = '$referredBy'")->row("Id");
                if (!empty($referredBy)) {
                    $closingBalance =   $this->db->order_by("CreatedOn", "DESC")->select("ClosingBalance")->get_where("transaction_history", "UserId = '$referredBy'")->row("ClosingBalance");
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
                sendSMS($user->Mobile, urlencode($msg));
                $this->db->delete("cart", "Id IN (" . implode(",", $bp->CartIds) . ")");
                $this->db->delete("before_payment", "MyId = '$myId'");
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
        } else if (preg_match("/^DRNKBK_[0-9]+$/", $myId)) {

            // FOR BOOKINGS

            if ($bp->IsFirst) {
                $referredBy =   $this->db->select("ReferredBy")->get_where("users", "Id = '$userId'")->row("ReferredBy");
                $referredBy =   $this->db->select("Id")->get_where("users", "ReferralCode = '$referredBy'")->row("Id");
                if (!empty($referredBy)) {
                    $closingBalance =   $this->db->order_by("CreatedOn", "DESC")->select("ClosingBalance")->get_where("transaction_history", "UserId = '$referredBy'")->row("ClosingBalance");
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
                sendSMS($user->Mobile, urlencode($msg));
                $this->db->delete("before_payment", "MyId = '$myId'");
                return json_encode(array("status" => TRUE, "msg" => "Booked Succesfully..."));
            }
            return json_encode(array("status" => FALSE, "msg" => "There is Some Error!!!"));
        }
    }
}
