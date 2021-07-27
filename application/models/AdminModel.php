<?php

defined('BASEPATH') OR exit('No direct script access allowed');
class AdminModel extends ANNOT_Model
{
    function __construct()
    {
        parent::__construct();
    }
    
    function admin_login_check()
    {
        $this->load->helper("custom");
        $username=$this->input->post('Username');
        $password=cryptPass($this->input->post('Password'));
        $res=$this->db->get_where("admin","UserName = '$username' AND Password = '$password'")->row_array();
        if( count($res) > 0 )
        {
            $_SESSION['ADMIN']=$res['Name'];
            $_SESSION['ADMIN_ID']=$res['Id'];
            return json_encode(array('status'=>TRUE));
        }
        return json_encode(array('status'=>FALSE));
    }
    
    function update_product()
    {
        $id=$this->input->post('hidId');
        $data=array(
            'Name'          => trim($this->input->post('Name')),
            'CategoryId'    => trim($this->input->post('Category')),
            'Deposit'       => trim($this->input->post('Deposit')),
            'MinQuantity'   => trim($this->input->post('MinQuantity')),
            'MaxQuantity'   => trim($this->input->post('MaxQuantity')),
            'Status'        => $this->input->post('Status'),
            'ModifiedOn'    => date("Y-m-d H:i:s"),
            'ModifiedBy'    => $this->input->ip_address()
        );
        if(!empty($_FILES['File']['name']))
        {
            $resUpload=$this->uploadFile('File',"uploads/products/",'jpeg|jpg|png');
            if($resUpload==FALSE)
               return json_encode(array('status'=>FALSE,'msg'=>'Upload Error!!! Please Select Valid Files Only and Try again...'));
            $data['FilePath']=$resUpload;
            $this->resizeImage($resUpload, 200, 200, 100);
            if($id > 0)
            {
                $file=$this->db->select("FilePath")->get_where('products',"Id = '$id'")->row("FilePath");
                if(file_exists($file))
                    unlink($file);
            }
        }
        if($id > 0)
        {
            $this->db->update('products',$data,"Id = '$id'");
            return json_encode(array('status'=>TRUE));
        }
        $data['CreatedBy'] = $this->input->ip_address();
        $data['CreatedOn'] = date("Y-m-d H:i:s");
        $this->db->insert('products',$data);
        return json_encode(array('status'=>TRUE));
    }
    function update_product_price($productId)
    {
        $id=$this->input->post('hidId');
        $data=array(
            'Price'         => trim($this->input->post('Price')),
            'UptoQuantity'  => trim($this->input->post('UptoQuantity')),
            'ProductId'     => $productId,
            'ModifiedOn'=> date("Y-m-d H:i:s"),
            'ModifiedBy'=> $this->input->ip_address()
        );
        if($id > 0)
        {
            $this->db->update('product_prices',$data,"Id = '$id'");
            return json_encode(array('status'=>TRUE));
        }
        $data['CreatedBy'] = $this->input->ip_address();
        $data['CreatedOn'] = date("Y-m-d H:i:s");
        $this->db->insert('product_prices',$data);
        return json_encode(array('status'=>TRUE));
    }
    function delete_product_price($pId,$id)
    {
        if( $this->db->where("ProductId",$pId)->count_all_results("product_prices") < 2 )
            return json_encode(array('status'=>FALSE,'msg'=>'Sorry!!! You need to keep atleast one price.'));
        $this->db->delete("product_prices","Id = '$id' AND ProductId = '$pId'");
            return json_encode(array('status'=>TRUE));
    }
    function change_order_status($id)
    {
        $values =   array("Cancelled","Ordered","Approved","Packed","Shipped","Delivered");
        $curr   =   $this->db->select("Status,UserId,OrderNumber")->get_where("orders","Id = '$id'")->row();
        $val    =   array_search($curr->Status,$values);
        if(empty($val) || $val > 4)
            return json_encode(array('status'=>FALSE,'msg'=>'Sorry!!! Nothing to Change.'));
        $val++;
        $data   =   array(
            "Status"        => $values[$val],
            "ModifiedOn"    => date("Y-m-d H:i:s"),
            "ModifiedBy"    => $this->input->ip_address()
        );
        $this->db->update("orders",$data,"Id = '$id'");
        if($this->db->affected_rows() < 1)
            return json_encode(array('status'=>FALSE,'msg'=>'Sorry!!! No Change Made.'));
        if($val>4)
        {
            $user   =   $this->db->select("CONCAT(FirstName,' ',LastName) AS Name,Mobile")->get_where("users","Id = '$curr->UserId'")->row();
            $this->sendSMS($user->Mobile,"Hello $user->Name\nYour Order $curr->OrderNumber is Delivered Succesfully.Time : ".date("d-m-Y h:i:s a"));
        }
        return json_encode(array('status'=>TRUE,'msg'=>'Success.'));
    }
    function update_monthly()
    {
        $id=$this->input->post('hidId');
        $data=array(
            'CategoryId'    => trim($this->input->post('Category')),
            'ProductId'     => trim($this->input->post('Product')),
            'Status'        => $this->input->post('Status'),
            'ModifiedOn'    => date("Y-m-d H:i:s"),
            'ModifiedBy'    => $this->input->ip_address()
        );
        if($id > 0)
        {
            $this->db->update('monthly_products',$data,"Id = '$id'");
            return json_encode(array('status'=>TRUE));
        }
        $data['CreatedBy'] = $this->input->ip_address();
        $data['CreatedOn'] = date("Y-m-d H:i:s");
        $this->db->insert('monthly_products',$data);
        return json_encode(array('status'=>TRUE));
    }
    function accept_booking($id)
    {
        $this->db->update("bookings",array("Status"=>"Approved"),"Id = '$id'");
        return json_encode(array("status"=>TRUE));
    }
}