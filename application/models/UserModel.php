<?php

defined('BASEPATH') or exit('No direct script access allowed');
class UserModel extends ANNOT_Model
{
    function __construct()
    {
        parent::__construct();
    }
    function pre_register_user()
    {
        $mobile = trim($this->input->post('Mobile'));
        $email  = trim($this->input->post('Email'));
        if (!preg_match("/^[6-9]\d{9}$/", $mobile))
            return json_encode(array('status' => FALSE, 'msg' => "Invalid Mobile Number"));
        if (!empty($email) && !preg_match("/^([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})$/", $email))
            return json_encode(array('status' => FALSE, 'msg' => "Invalid Email Address"));
        if ($this->db->where("Mobile = '$mobile'")->count_all_results("users") > 0)
            return json_encode(array('status' => FALSE, 'msg' => "This Mobile Number Already Registered"));
        if ($this->db->where("(Email = '$email' AND Email != '' AND Email IS NOT NULL)")->count_all_results("users") > 0)
            return json_encode(array('status' => FALSE, 'msg' => "This Email Id Already Registered"));
        $this->load->helper('custom');
        $referralCode   =   generateRandomString(8);
        while ($this->db->where("ReferralCode", $referralCode)->count_all_results("users") > 0)
            $referralCode = generateRandomString(8);
        $data   = array(
            'FirstName'     => trim($this->input->post("FirstName")),
            'LastName'      => trim($this->input->post("LastName")),
            'ReferredBy'    => trim($this->input->post("ReferredBy")),
            'Password'      => cryptPass(trim($this->input->post('Password'))),
            'ReferralCode'  => $referralCode,
            'Mobile'        => $mobile,
            'Email'         => $email,
            'CreatedOn'     => date("Y-m-d H:i:s"),
            'CreatedBy'     => $this->input->ip_address(),
            'ModifiedOn'    => date("Y-m-d H:i:s"),
            'ModifiedBy'    => $this->input->ip_address()
        );
        $otp = $this->sendOTP($mobile, $data['FirstName'] . " " . $data['LastName']);
        if (empty($otp))
            return json_encode(array("status" => FALSE, 'msg' => "Oops!! Can't Send OTP..."));
        $this->db->delete("temp_data", ["Mobile" => $mobile]);
        $this->db->insert("temp_data", array(
            'Otp'           => $otp,
            'Mobile'        => $mobile,
            'Data'          => json_encode($data),
            'CreatedOn'     => date("Y-m-d H:i:s"),
            'CreatedBy'     => $this->input->ip_address(),
        ));
        return json_encode(array('status' => TRUE, 'msg' => 'Succesfully Sent OTP', 'OTP' => $otp));
    }
    function verify_otp()
    {
        $mobile = $this->input->post('Mobile');
        if (!preg_match("/^[6-9]\d{9}$/", $mobile))
            return json_encode(array('status' => FALSE, 'msg' => "Invalid Mobile Number"));
        $otp = $this->input->post('OTP');
        $stored = $this->db->get_where("temp_data", "Mobile = '$mobile' AND Otp = '$otp'");
        if ($stored->num_rows() < 1)
            return json_encode(array('status' => FALSE, 'msg' => "OTP Mismatch"));
        $this->db->insert('users', json_decode($stored->row()->Data));
        $userId=$this->db->insert_id();
        $this->db->delete("temp_data", "Mobile = '$mobile'");
        $res = $this->db->select("Id,FirstName,LastName,Mobile,Email,ReferralCode,Status")->get_where("users", "Id = '$userId'")->row();
        return json_encode(array('status' => TRUE, 'msg' => 'Registration Completed Successfully','data' => $res));
    }
    function user_login_check()
    {
        $this->load->helper("custom");
        $email = $this->input->post('Email');
        $password = $this->input->post('Password');
        if (empty($password))
            return json_encode(array('status' => FALSE, 'msg' => 'Sorry!!! Please Enter a Password.'));
        $password = cryptPass($password);
        $res = $this->db->select("Id,FirstName,LastName,Mobile,Email,ReferralCode,Status")->get_where("users", "((Email = '$email' AND Email != '' AND Email IS NOT NULL) OR Mobile = '$email') AND Password = '$password' AND Status = 'Active'");
        if ($res->num_rows() > 0) {
            $this->db->update("users", ["DeviceId" => NULL], ["DeviceId" => $this->input->post("DeviceId")]);
            $this->db->update("users", ["LastLogin" => date("Y-m-d H:i:s"), "LastLoginIP" => $this->input->ip_address(), "DeviceId" => $this->input->post("DeviceId")], ["Id" => $res->row("Id")]);
            return json_encode(array('status' => TRUE, 'msg' => 'Login Success', 'data' => $res->row()));
        }
        return json_encode(array('status' => FALSE, 'msg' => 'Sorry!!! Invalid Credentials OR Inactive Account'));
    }
    function update_address()
    {
        $id         =   $this->input->post("Id");
        $mobile = trim($this->input->post('Mobile'));
        if (!preg_match("/^[6-9]\d{9}$/", $mobile))
            return json_encode(array('status' => FALSE, 'msg' => "Invalid Mobile Number"));
        if (!in_array(trim($this->input->post("Pin")), $this->config->item("PinCodes")))
            return json_encode(array("status" => FALSE, "msg" => "Sorry!!! Delivery not available on this pin code"));
        $data   =   array(
            'Mobile'        =>  $mobile,
            'UserId'        =>  trim($this->input->post("UserId")),
            'Name'          =>  trim($this->input->post("Name")),
            'Line1'         =>  trim($this->input->post("Line1")),
            'Line2'         =>  trim($this->input->post("Line2")),
            'LandMark'      =>  trim($this->input->post("Landmark")),
            'PIN'           =>  trim($this->input->post("Pin")),
            'AddressType'   =>  trim($this->input->post("AddressType")),
            'StateId'       =>  trim($this->input->post("StateId")),
            'DistrictId'    =>  trim($this->input->post("DistrictId")),
            'ModifiedBy'    =>  $this->input->ip_address(),
            'ModifiedOn'    =>  date("Y-m-d H:i:s")
        );
        if (empty($id)) {
            $data['CreatedOn']    =  date("Y-m-d H:i:s");
            $data['CreatedBy']    =  $this->input->ip_address();
            $this->db->insert("address", $data);
            if ($this->db->insert_id() > 0)
                return json_encode(array('status' => TRUE, 'msg' => 'Successfully Added new Address'));
            else
                return json_encode(array('status' => FALSE, 'msg' => 'Cant Add Please Check Valodity of User Id', "Inputs" => $data));
        }
        $this->db->update("address", $data, "Id = '$id'");
        if ($this->db->affected_rows() > 0)
            return json_encode(array('status' => TRUE, 'msg' => "Successfully Edited the Address with id : $id"));
        else
            return json_encode(array('status' => FALSE, 'msg' => "Cant Edit Please Check Data provided Id : $id", "Inputs" => $data));
    }
    function get_address()
    {
        $id = $this->input->post('Id');
        if (!empty($id))
            $this->db->where('AD.Id', $id);
        $userId =   $this->input->post("UserId");
        $res    =   $this->db
            ->select("AD.Id,AD.Name,AD.Line1,AD.Line2,AD.LandMark AS Landmark,AD.Mobile,AD.PIN AS Pin,AD.AddressType,ST.Name AS State,AD.StateId,DT.Name AS District,AD.DistrictId,AD.Status")
            ->join('states AS ST', 'AD.StateId = ST.Id')
            ->join('districts AS DT', 'AD.DistrictId = DT.Id')
            ->get_where('address AS AD', "AD.UserId = '$userId'")
            ->result_array();
        return json_encode(array('status' => count($res) > 0 ? TRUE : FALSE, 'data' => $res));
    }
    function delete_address()
    {
        $id = $this->input->post('Id');
        $userId = $this->input->post('UserId');
        if ($this->db->get_where("address", "Id = '$id' AND UserId = '$userId'")->num_rows() < 1)
            return json_encode(array("status" => FALSE, "msg" => "Sorry!!! UserId not Matching."));
        $this->db->delete("address", "Id = '$id'");
        return json_encode(array('status' => TRUE, 'msg' => "Succesfully Deleted."));
    }
    function forgot_password()
    {
        $mobile = $this->input->post('Mobile');
        if (!preg_match("/^[6-9]\d{9}$/", $mobile))
            return json_encode(array('status' => FALSE, 'msg' => "Invalid Mobile Number"));
        $res    = $this->db->get_where("users", "Mobile = '$mobile'");
        if ($res->num_rows() < 1)
            return json_encode(array('status' => FALSE, 'msg' => "This Mobile didn't Match any Accounts"));
        $this->load->helper('custom');
        $otp = $this->sendOTP($mobile, $res->row('FirstName') . " " . $res->row('LastName'), TRUE);
        $this->db->delete("temp_data", ["Mobile" => $mobile]);
        $this->db->insert("temp_data", array(
            'Otp'           => cryptPass($otp),
            'Mobile'        => $mobile,
            'CreatedOn'     => date("Y-m-d H:i:s"),
            'CreatedBy'     => $this->input->ip_address(),
        ));
        return json_encode(array('status' => TRUE, 'msg' => "Succesfully Sent OTP."));
    }
    function reset_password()
    {
        $mobile =   $this->input->post('Mobile');
        if (!preg_match("/^[6-9]\d{9}$/", $mobile))
            return json_encode(array('status' => FALSE, 'msg' => "Invalid Mobile Number"));
        $res    = $this->db->get_where("users", "Mobile = '$mobile'");
        if ($res->num_rows() < 1)
            return json_encode(array('status' => FALSE, 'msg' => "This Mobile didn't Match any Accounts"));

        $this->load->helper('custom');
        $otp    =   cryptPass($this->input->post('OTP'));
        if ($this->db->where("Mobile = '$mobile' AND Otp = '$otp'")->count_all_results("temp_data") < 1)
            return json_encode(array('status' => FALSE, 'msg' => 'Oops!!! OTP Mismatch...'));
        $password   = $this->input->post('Password');
        if (empty($password))
            return json_encode(array('status' => FALSE, 'msg' => 'Sorry!!! Please Enter a Password.'));
        $this->db->update("users", array('Password' => cryptPass($password)), "Mobile = '$mobile'");
        $this->db->delete("temp_data", "Mobile = '$mobile'");
        return json_encode(array('status' => TRUE, 'msg' => 'Password Changed Succesfully'));
    }
    function change_password()
    {
        $id = $this->input->post('UserId');
        $this->load->helper("custom");
        $password   = cryptPass($this->input->post("CurrentPassword"));
        if ($this->db->where("Id = '$id' AND Password = '$password'")->count_all_results("users") < 1)
            return json_encode(array("status" => FALSE, "msg" => "Oops!!! Incorrect Current Password."));
        $password   = $this->input->post("NewPassword");
        if (empty($password))
            return json_encode(array('status' => FALSE, 'msg' => 'Sorry!!! Please Enter a Password.'));
        $this->db->update("users", array("Password" => cryptPass($password)), "Id = '$id'");
        return  json_encode(array("status" => TRUE, "msg" => "Password Changed Succesfully."));
    }
    function get_my_wallet()
    {
        $userId =   $this->input->post("UserId");
        $res    =   $this->db
            ->order_by("CreatedOn", "DESC")
            ->select("Title,Type,Amount,ClosingBalance,CreatedOn AS Time")
            ->get_where("transaction_history", "UserId = '$userId'")
            ->result_array();
        $history = array();
        $this->load->helper("custom");
        foreach ($res as $row) {
            $row["ClosingBalance"] = cover_me($row["ClosingBalance"], "d");
            $history[] = $row;
        }
        $balance = empty($res[0]["ClosingBalance"]) ? "0" : cover_me($res[0]["ClosingBalance"], "d");
        return json_encode(array("status" => count($history) > 0 ? TRUE : FALSE, "balance" => $balance, "history" => $history));
    }
    function get_states()
    {
        return json_encode(
            array(
                "status" => TRUE,
                "msg"   => "success",
                "data"  => $this->db
                    ->select("Id,Name")
                    ->order_by("Name")
                    ->get_where("states", "Status = 'Active'")
                    ->result_array()
            )
        );
    }
    function get_districts()
    {
        $stateId    =   $this->input->post("StateId");
        return json_encode(
            array(
                "status" =>  TRUE,
                "msg"   =>  "success",
                "data"  =>  $this->db
                    ->select("Id,Name")
                    ->order_by("Name")
                    ->get_where("districts", "StateId = '$stateId' AND Status = 'Active'")
                    ->result_array()
            )
        );
    }
    function check_update()
    {
        $res    =   $this->db->select("IsForceUpdate,UpdateTitle,UpdateNotes,VersionCode,VersionName")->get("app_update");
        return  json_encode(["status" => $res->num_rows() > 0, "data" => $res->num_rows() > 0 ? [$res->last_row()] : []]);
    }
    function update_profile()
    {
        $id = $this->input->post('UserId');
        $data = [
            "FirstName" =>  $this->input->post("FirstName"),
            "LastName"  =>  $this->input->post("LastName")
        ];
        $this->db->update("users", $data, "Id = '$id'");
        return  json_encode(array("status" => TRUE, "msg" => "Profile Updated."));
    }
}
