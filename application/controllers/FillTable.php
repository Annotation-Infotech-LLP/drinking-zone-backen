<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class FillTable extends ANNOT_Controller
{
	var $table = '',$column_order = "",$column_search = "",$order = array('Main.Id' => 'DESC'),$select="",$join=array(),$where=""; // default order 
	
	function __construct()
    {
        parent::__construct();
        if(!$this->input->is_ajax_request())
            exit('No direct script access allowed');
    }

	 
    private function getDataTables()
	{
	   $this->getDataTableQuery();
		
		if($_POST['length'] != -1)  // commented for working
			$this->db->limit($_POST['length'], $_POST['start']); // commented for working
		
		
		$query = $this->db->get();
		return $query->result();
	}
 
    private function getDataTableQuery()
	{
		if(!empty($this->select))
			$this->db->select($this->select);
		$this->db->from($this->table);
		if(!empty($this->where))
			$this->db->where($this->where);
		foreach($this->join as $table=>$cond)
			$this->db->join($table,$cond);
		$i = 0;
	
		foreach ($this->column_search as $item) // loop column 
		{
		 if(isset($_POST['search']) && $_POST['search']['value']!="" )
		 {
			if($_POST['search']['value']) // if datatable send POST for search
			{
				
				if($i===0) // first loop
				{
					//$this->db->group_start(); // open bracket. query Where with OR clause better with bracket. because maybe can combine with other WHERE with AND. this is not working
					$this->db->like($item, $_POST['search']['value']);
				}
				else
				{
					$this->db->or_like($item, $_POST['search']['value']);
				}

				if(count($this->column_search) - 1 == $i) //last loop
					{
						//$this->db->group_end(); //close bracket. this is not working
					}
			}
		 } // isset ending	
			$i++;
		}
		
		if(isset($_POST['order'])) // here order processing
		{
			$this->db->order_by($this->column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		} 
		if(isset($this->order))
		{
			$order = $this->order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}
 
 
    private function countFiltered()
	{
		$this->getDataTableQuery();
		$query = $this->db->get();
		return $query->num_rows();
	}

	private function countAll()
	{
		$this->db->from($this->table);
		if(!empty($this->where))
		    $this->db->where($this->where);
		return $this->db->count_all_results();
	}


    public function users()
    {
        $this->table    =   "users AS Main";
        $this->column_order=array('FirstName,LastName','Email','Mobile','Status','');
        $this->column_search=array('FirstName','LastName','Email','Mobile','Status');
        $this->select	="Id,CONCAT(FirstName,' ',LastName) AS Name,Email,Mobile,Status";
        $result = $this->getDataTables();

        $data = array();

        foreach($result as $row) {
            $rowArray=array();
            
            $class="btn-success";
            if($row->Status == "Pending")
                $class = "btn-warning";
            else if($row->Status == "Inactive")
                $class  = "btn-danger";
            $rowArray[] = $row->Name;
            $rowArray[] = $row->Email;
            $rowArray[] = $row->Mobile;
            $rowArray[] = "<label class=\"label $class\">$row->Status</label>";

            $action     = "<button class=\"btn btn-info\" onClick=\"showUser($row->Id);\"><i class=\"fa fa-eye\"></i> View</button>";
                           // <button class=\"btn btn-danger\" onClick=\"deleteData('$row->Id');\"><i class=\"fa fa-trash\"></i> Delete</button>";
            $rowArray[] = $action;
            $data[] = $rowArray;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->countAll(),
            "recordsFiltered" => $this->countFiltered(),
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function products()
    {
        $this->table    =   "products AS Main";
		$this->join		=	array("category AS CT"=>"CT.Id = Main.CategoryId");
		$this->select   =	"Main.Id,Main.Name,MinQuantity,MaxQuantity,CT.Name AS Category,FilePath,Main.Status";
        $this->column_order=array('','Main.Name','CT.Name','MinQuantity','MaxQuantity','','Main.Status','');
        $this->column_search=array('Main.Name','CT.Name','MinQuantity','MaxQuantity','Main.Status');
        $result = $this->getDataTables();

        $data = array();



		foreach($result as $row){
			$class="btn-success";
			if($row->Status == "Inactive")
				$class = "btn-danger";
            $rowArray=array();
            
            $rowArray[] = "<a href=\"".base_url($row->FilePath)."\" target=\"_blank\"><img src=\"".base_url($row->FilePath)."\" class=\"small-ic\"/></a>";
            $rowArray[] = $row->Name;
            $rowArray[] = $row->Category;
            $rowArray[] = $row->MinQuantity;
            $rowArray[] = $row->MaxQuantity;
            $rowArray[] = "<a href=\"".base_url($this->config->item("AdminController")."/product_prices/$row->Id")."\" class=\"btn btn-primary\"><i class=\"fa fa-eye\"></i> View</a>";
            $rowArray[] = "<label class=\"label $class\">$row->Status</label>";

            $action     = "<button class=\"btn btn-info\" onClick=\"editData('$row->Id');\"><i class=\"fa fa-edit\"></i> Edit</button>
			<button class=\"btn btn-danger\" onClick=\"deleteData('$row->Id');\"><i class=\"fa fa-trash\"></i> Delete</button>";
            $rowArray[] = $action;
            $data[] = $rowArray;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->countAll(),
            "recordsFiltered" => $this->countFiltered(),
            "data" => $data,
        );
        echo json_encode($output);
    }
    public function monthly()
    {
        $this->table    =   "monthly_products AS Main";
		$this->join		=	array("category AS CT"=>"CT.Id = Main.CategoryId","products AS PD"=>"PD.Id = Main.ProductId");
		$this->select   =	"Main.Id,PD.Name,CT.Name AS Category,FilePath,Main.Status";
        $this->column_order=array('','PD.Name','CT.Name','Main.Status','');
        $this->column_search=array('Main.Name','CT.Name','Main.Status');
        $result = $this->getDataTables();

        $data = array();



		foreach($result as $row){
			$class="btn-success";
			if($row->Status == "Inactive")
				$class = "btn-danger";
            $rowArray=array();
            
            $rowArray[] = "<a href=\"".base_url($row->FilePath)."\" target=\"_blank\"><img src=\"".base_url($row->FilePath)."\" class=\"small-ic\"/></a>";
            $rowArray[] = $row->Name;
            $rowArray[] = $row->Category;
            $rowArray[] = "<label class=\"label $class\">$row->Status</label>";

            $action     = "<button class=\"btn btn-info\" onClick=\"editData('$row->Id');\"><i class=\"fa fa-edit\"></i> Edit</button>
			<button class=\"btn btn-danger\" onClick=\"deleteData('$row->Id');\"><i class=\"fa fa-trash\"></i> Delete</button>";
            $rowArray[] = $action;
            $data[] = $rowArray;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->countAll(),
            "recordsFiltered" => $this->countFiltered(),
            "data" => $data,
        );
        echo json_encode($output);
    }

    public function orders()
    {
        $this->table    =   "orders AS Main";
		$this->join		=	array('users AS US'=>'US.Id = Main.UserId');
		$this->select   =	"Main.Id,Main.Status,DATE_FORMAT(DeliveryTime,'%D %b %y, %r') AS DeliveryTime,TotalAmount,PaymentStatus,US.Mobile,CONCAT(US.FirstName,' ',US.LastName) AS Name,Main.IsQuick,Main.OrderNumber";
		$this->where    =   "Main.Confirmed = 'Yes'";
        $this->column_order=array('Main.Id','Name','DeliveryTime','TotalAmount','PaymentStatus','Main.Status','');
        $this->column_search=array('US.FirstName','US.LastName','US.Mobile','TotalAmount','DeliveryTime','Main.Status');
        $result = $this->getDataTables();  
        $data = array();

		foreach($result as $row){
			$class="btn-default";
			if($row->Status == "Ordered")
				$class = "btn-warning";
			else if($row->Status == "Delivered")
				$class = "btn-success";
			else if($row->Status == "Cancelled")
				$class = "btn-danger";
			else if($row->Status == "Packed")
				$class = "btn-info";
			else if($row->Status == "Shipped")
				$class = "btn-primary";
			$quick=$row->IsQuick=="Yes"?"<label class=\"label btn-primary\">Quick</label>":"";
			$rowArray	= array();
            $rowArray[] = $row->OrderNumber;
            $rowArray[] = "$row->Name<br/><small>$row->Mobile</small>";
            $rowArray[] = !empty($quick)?$quick:$row->DeliveryTime??"<label class=\"label btn-default\">Normal</label>";
            $rowArray[] = $row->TotalAmount;
            $rowArray[] = $row->PaymentStatus;
            $rowArray[] = "<label class=\"label $class\">$row->Status</label>";

            $action     = "<a class=\"btn btn-primary\" href=\"".base_url($this->config->item("AdminController")."/view_order/$row->Id")."\"><i class=\"fa fa-eye\"></i> View Items</a>";
			if( !($row->Status == "Cancelled" || $row->Status == "Delivered") )
				$action .= "
				<button onClick=\"updateOrder($row->Id)\" class=\"btn btn-success\" ><i class=\"fa fa-arrow-up\"></i> Upgrade</button>";
            $rowArray[] = $action;
            $data[] = $rowArray;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->countAll(),
            "recordsFiltered" => $this->countFiltered(),
            "data" => $data,
        );
        echo json_encode($output);
    }
    public function bookings()
    {
        $this->table    =   "bookings AS Main";
		$this->join		=	array('users AS US'=>'US.Id = Main.UserId','products AS PD'=>'PD.Id = Main.ProductId');
        $this->where    =   "Main.Confirmed = 'Yes'";
		$this->select   =	"Main.Id,Main.Status,TotalAmount,PaymentStatus,US.Mobile,CONCAT(US.FirstName,' ',US.LastName) AS Name,PD.Name AS ProductName,FilePath,Quantity,StartDate,EndDate,PaymentStatus";
        $this->column_order=array('Name','DeliveryTime','TotalAmount','PaymentStatus','Main.Status','');
        $this->column_search=array('US.FirstName','US.LastName','US.Mobile','TotalAmount','DeliveryTime','Main.Status');
        $result = $this->getDataTables();  
        $data = array();

		foreach($result as $row){
			$class="btn-default";
			if($row->Status == "Booked")
				$class = "btn-warning";
			else if($row->Status == "Cancelled")
				$class = "btn-danger";
			$rowArray	= array();
            $rowArray[] = "<a href=\"".base_url($row->FilePath)."\" target=\"_blank\"><img src=\"".base_url($row->FilePath)."\" class=\"small-ic\"/></a>";
            $rowArray[] = $row->ProductName;
            $rowArray[] = $row->Quantity;
            $rowArray[] = "$row->Name<br/><small>$row->Mobile</small><br/>";
            $rowArray[] = $row->StartDate;
            $rowArray[] = $row->EndDate;
            $rowArray[] = $row->TotalAmount;
            $rowArray[] = $row->PaymentStatus;
            $rowArray[] = "<label class=\"label $class\">$row->Status</label>";

            $action     = "<button class=\"btn btn-info\" onClick=\"showAddress($row->Id)\"><i class=\"fa fa-eye\"></i> Address</button>";
			if( !($row->Status == "Cancelled" || $row->Status == "Approved") )
				$action .= "
				<button onClick=\"acceptBooking($row->Id)\" class=\"btn btn-success\" ><i class=\"fa fa-check\"></i> Accept</button>";
            $rowArray[] = $action;
            $data[] = $rowArray;
        }

        $output = array(
            "draw" => $_POST['draw'],
            "recordsTotal" => $this->countAll(),
            "recordsFiltered" => $this->countFiltered(),
            "data" => $data,
        );
        echo json_encode($output);
    }
}