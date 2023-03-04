<?php
/**
 * 
 */
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'/libraries/REST_Controller.php';

class Referral extends Rest_Controller
{
    
    // function __construct()
    // {
    //     parent::__construct();
    //     $this->load->model('referral_model');        
    // }
    function __construct()
	{
		header('Access-Control-Allow-Origin: *');
    	header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
    	header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    	$method = $_SERVER['REQUEST_METHOD'];
    	if ($method == "OPTIONS") {
        	die();
    	}
		parent::__construct();
       	$this->load->model('referral_model');
       	// $this->load->library('session');
    }
    
    public function getReferrals_post(){
        $response=array();
        $list = $this->referral_model->getReferrals($this->input->post());
        $data = array();
        $start = $this->input->post("start");
       
        foreach ($list as $orders) {
            $start++;
            $row = array();
            $row["id"] = $orders->id;            
            $row["name"] = $orders->first_name.' '.$orders->last_name;
            // $row["last_name"] = $orders->last_name;
            $row["email"] = $orders->email;
            $row["phone"] = $orders->phone;
            $row["referrar"] = $orders->referrar;                        
            $row["type"] = $orders->type;          
            $row["contactInstaller"] = $orders->contactInstaller;
            $row["user_type"] = $orders->user_type;            
            $row["updated"] = $orders->updated;                                    
            $data[]=$row;
        }

        $response = array(
            "draw"            => $this->input->post('draw'),
            "recordsTotal"    => $this->referral_model->count_all($this->input->post()),
            "recordsFiltered" => $this->referral_model->count_filtered($this->input->post()),
            "data"            => $data
        );
        $this->response($response);
    }

    public function getJobDetail_post(){
        $data=json_decode((file_get_contents("php://input")));
        if($data->installer_id == "" || $data->user_id == NULL || $data->installer_id == NULL || $data->user_id == ""){
    		$message = array("status_code"=>400,"success"=>0,"message"=>"Missing parameters.");
            $this->response($message, 400);
    	}else{
            $result=$this->referral_model->getJobDetail($data);
			$this->response($result['response'], $result['code']);
    	}
    }

    public function updateJobDetail_post(){
		$data=json_decode((file_get_contents("php://input")));	
		$result = $this->referral_model->updateJobDetail($data);
		$this->response($result['response'], $result['code']);
	}

    public function getOrderDetail_get(){
        $response=array();
        $result=$this->morder->getdetail($this->input->get('id'));
        if($result){
            $this->response($result,200);
        } 
    }

    public function changeStatus_post(){
        $data=json_decode((file_get_contents("php://input")));
        $result = $this->morder->changeStatus($data);
        $this->response($result,200);
    }

    public function download_get()
    {
        $dir = FCPATH.$_GET['file'];
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename='.basename($dir));
        header('Content-Transfer-Encoding: binary');       
        header('Expires: 0');        
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($dir));
        ob_clean();
        flush();
        readfile($dir);
    }

    public function csvExport_get()
    {
        $sql = "SELECT u.id,u.first_name,u.last_name,u.phone,u.email,u.city,u.state,u.address,CONCAT(user.first_name,' ',user.last_name) as referred_by,u.created FROM tbl_user as u LEFT JOIN `tbl_user` as user ON `user`.`id`=`u`.`referred_by` WHERE (u.user_type != 'sales' AND u.type='referral') ORDER BY `u`.`created` ASC";

        $query = $this->db->query($sql);
        $result = $query->result();
        //$query = $this->db->get();

        //print_r($query);
        //exit;

        // Starting the PHPExcel library
        $this->load->library('excel');
 
       /* $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setTitle("export")->setDescription("none");*/
 
        $this->excel->setActiveSheetIndex(0);
 
        // Field names in the first row
        $fields = $query->list_fields();
        $col = 0;
        foreach ($fields as $field)
        {
            $this->excel->getActiveSheet()->setCellValueByColumnAndRow($col, 1, $field);
            $col++;
        }
 
        // Fetching the table data
        $row = 2;
        foreach($result as $data)
        {
            $col = 0;
            foreach ($fields as $field)
            {
                $this->excel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $data->$field);
                $col++;
            }
 
            $row++;
        }
 
        $this->excel->setActiveSheetIndex(0);
    

        //$objWriter = PHPExcel_IOFactory::createWriter($this->excel, 'Excel5');
 
        // Sending headers to force the user to download the file
        /*header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Products_'.date('dMy').'.xls"');
        header('Cache-Control: max-age=0');*/
        $filename = FCPATH."referrals.xls";
        $this->excel->save($filename);
        return $this->response(array("file"=>"referrals.xls"),200);
    }

}
?>