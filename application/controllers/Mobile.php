<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class Mobile extends ANNOT_Controller
{
    function __construct()
    {
        session_start();
        parent :: __construct();
        if(date('Y-m-d') >= '2021-05-13' && date('Y-m-d') < '2021-05-14'){
            exit(json_encode(array('status' => FALSE, 'msg' => "Sorry!!! We're offline today as our delivery boys have their vaccination drive scheduled, will resume at full capacity from tomorrow.")));
        }else{
            $this->apiCheck();    
        }
        // $this->apiCheck();
    }

    //-------- User Section Starts Here -----------
    function get_states()
    {
        $this->load->model('UserModel','UM');
        echo $this->UM->get_states();
    }
    function get_districts()
    {
        $this->load->model('UserModel','UM');
        echo $this->UM->get_districts();
    }
    function pre_register_user()
    {
        $this->load->model('UserModel','UM');
        echo $this->UM->pre_register_user();
    }
    function verify_otp()
    {
        $this->load->model('UserModel','UM');
        echo $this->UM->verify_otp();
    }
    function user_login_check()
    {
        $this->load->model('UserModel','UM');
        echo $this->UM->user_login_check();
    }
    function get_my_profile()
    {
        $this->load->model('UserModel','UM');
        echo $this->UM->get_my_profile();
    }
    function update_address()
    {
        $this->load->model("UserModel","UM");
        echo $this->UM->update_address();
    }
    function get_address()
    {
        $this->load->model("UserModel","UM");
        echo $this->UM->get_address();
    }
    function delete_address()
    {
        $this->load->model("UserModel","UM");
        echo $this->UM->delete_address();
    }
    function forgot_password()
    {
        $this->load->model("UserModel","UM");
        echo $this->UM->forgot_password();
    }
    function reset_password()
    {
        $this->load->model("UserModel","UM");
        echo $this->UM->reset_password();
    }
    function change_password()
    {
        $this->load->model("UserModel","UM");
        echo $this->UM->change_password();
    }
    function get_my_wallet()
    {
        $this->load->model("UserModel","UM");
        echo $this->UM->get_my_wallet();
    }
    function check_update()
    {
        $this->load->model("UserModel","UM");
        echo $this->UM->check_update();
    }
    function update_profile()
    {
        $this->load->model("UserModel","UM");
        echo $this->UM->update_profile();
    }
    //-------- Products Section Starts Here -----------
    function get_categories()
    {   
        $this->load->model('ProductModel','PM');
        echo $this->PM->get_categories();
    }
    function get_all_products()
    {   
        $this->load->model('ProductModel','PM');
        echo $this->PM->get_all_products();
    }
    function get_price_chart()
    {
        $this->load->model('ProductModel','PM');
        echo $this->PM->get_price_chart();
    }
    function view_cart()
    {
        $this->load->model('ProductModel','PM');
        echo $this->PM->view_cart();
    }
    function update_cart()
    {
        $this->load->model('ProductModel','PM');
        echo $this->PM->update_cart();
    }
    function delete_cart()
    {
        $this->load->model('ProductModel','PM');
        echo $this->PM->delete_cart();
    }
    function pay_now()
    {
        $this->load->model('ProductModel','PM');
        echo $this->PM->pay_now();
    }
    function place_order()
    {
        $this->load->model('ProductModel','PM');
        echo $this->PM->place_order();
    }
    function confirm_order()
    {
        $this->load->model('ProductModel','PM');
        echo $this->PM->confirm_order();
    }
    function view_orders()
    {
        $this->load->model('ProductModel','PM');
        echo $this->PM->view_orders();
    }
    function view_single_order()
    {
        $this->load->model('ProductModel','PM');
        echo $this->PM->view_single_order();
    }
    function cancel_order()
    {
        $this->load->model('ProductModel','PM');
        echo $this->PM->cancel_order();
    }
    function get_bookable_products()
    {
        $this->load->model('ProductModel','PM');
        echo $this->PM->get_bookable_products();
    }
    function book_now()
    {
        $this->load->model('ProductModel','PM');
        echo $this->PM->book_now();
    }
    function confirm_booking()
    {
        $this->load->model('ProductModel','PM');
        echo $this->PM->confirm_booking();
    }
    function view_bookings()
    {
        $this->load->model('ProductModel','PM');
        echo $this->PM->view_bookings();
    }
    function cancel_confirm()
    {
        $this->load->model('ProductModel','PM');
        echo $this->PM->cancel_confirm();
    }
    function cancel_booking()
    {
        $this->load->model('ProductModel','PM');
        echo $this->PM->cancel_booking();
    }
    function test()
    {
        $this->load->helper("custom");
        echo cover_me("ZUZqVFFxTjBReVVZNmdrTHhaUFB4UT09","d");
    }
}
