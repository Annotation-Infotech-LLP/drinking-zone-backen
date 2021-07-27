<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class AjaxCalls extends ANNOT_Controller
{
    function __construct()
    {
        parent::__construct();
        if(!$this->input->is_ajax_request())
            exit('No direct script access allowed');
    }

	public function getDistricts()
	{
        $statedId = $this->input->post('StateId');
        $resarrray=$this->db->get_where('districts',"Status = 'Active' AND StateId = '$statedId'")->result_array();
        if(count($resarrray)<1)
        {
            echo "0";
            exit;
        }
        $res='<option value="">-Select District-</option>';
        foreach($resarrray as $row)
            $res.='<option value="'.$row['Id'].'">'.$row['Name'].'</option>';
        echo $res;
    }

	public function getProducts($categoryId)
	{
        $resarrray=$this->db->get_where('products',"Status = 'Active' AND CategoryId = '$categoryId'")->result_array();
        if(count($resarrray)<1)
        {
            echo '<option value="">-No Products-</option>';
            exit;
        }
        $res='<option value="">-Select Product-</option>';
        foreach($resarrray as $row)
            $res.='<option value="'.$row['Id'].'">'.$row['Name'].'</option>';
        echo $res;
    }
    
    function admin_login_check()
    {
        $this->load->model("AdminModel");
        echo $this->AdminModel->admin_login_check();
    }

    function get_edit($table,$id)
    {
        echo json_encode($this->db->get_where($table,"Id = '$id'")->row());
    }

    function delete_db_n_file($table,$id)
    {
        $file=$this->db->select('FilePath')->get_where($table,"Id = '$id'")->row('FilePath');
        $this->db->delete($table,"Id = '$id'");
        $rows=$this->db->affected_rows();
        if($rows>0)
        {
            @unlink($file);
            echo json_encode(array('status'=>TRUE));
        }
        else
            echo json_encode(array('status'=>FALSE));
    }
    
    function delete_db($table,$id)
    {
        if($this->db->delete($table,"Id = '$id'"))
            echo json_encode(array('status'=>TRUE));
        else
            echo json_encode(array('status'=>FALSE));
    }

    function update_product()
    {
        $this->load->model("AdminModel");
        echo $this->AdminModel->update_product();
    }
    function update_product_price($id)
    {
        $this->load->model("AdminModel");
        echo $this->AdminModel->update_product_price($id);
    }
    function delete_product_price($pId,$id)
    {
        $this->load->model("AdminModel");
        echo $this->AdminModel->delete_product_price($pId,$id);
    }
    function change_order_status($id)
    {
        $this->load->model('AdminModel');
        echo $this->AdminModel->change_order_status($id);
    }
    function update_monthly()
    {
        $this->load->model("AdminModel");
        echo $this->AdminModel->update_monthly();
    }
    function accept_booking($id)
    {
        $this->load->model("AdminModel");
        echo $this->AdminModel->accept_booking($id);
    }
}