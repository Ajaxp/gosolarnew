<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH.'/libraries/REST_Controller.php';

/**
* 
*/
class Admin_api extends REST_Controller
{
	
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
       	$this->load->model('admin_mapi');
       	// $this->load->library('session');
	}

	public function login_post(){
		$data=json_decode((file_get_contents("php://input")));	
		if(!$data->email || !$data->password){
			$message = array("status_code"=>400,"message"=>"Missing parameters.");
            $this->response($message, 400);	
		}else{
			$result = $this->admin_mapi->login($data);
			$this->response($result['response'], $result['code']);
		}
	}

	public function checkLogin_get(){
		$user = $this->session->userdata('logged_in');
		exit;
        if($user){
            $response = array("status"=>1, "user"=>$user);
            $this->response($response, 200);
        }else{
            $response = array("status"=>0, "message"=>"No user logged in");
            $this->response($response, 400);
        }
	}

	public function logout_post(){
		$this->session->unset_userdata('logged_in');
		session_destroy();
		$this->response(array("status"=>0, "message"=>"Logout successfully"), 200);
	}

	public function getAppUsers_post(){		
		
		$response=array();
     	$list = $this->admin_mapi->getAppUsers($this->input->post());
        $data = array();
        $start = $this->input->post("start");
        foreach ($list as $users) {
            $start++;
            $row = array();
            $row["id"] = $users->id;
            $row["name"] = $users->name;
          	$row["email"] = $users->email;            
			$row["status"] = $users->status;               
			$row["phone"] = $users->phone;            
            $row["created"] = $users->created;            
            $data[]=$row;
        }
 
        $response = array(
            "draw"            => $this->input->post('draw'),
            "recordsTotal"    => $this->admin_mapi->count_all(),
            "recordsFiltered" => $this->admin_mapi->count_filtered('app_user'),
            "data"            => $data,
        );
     	$this->response($response);
	}

	public function getSalesMen_post(){		

		$response=array();
     	$list = $this->admin_mapi->getSalesMen($this->input->post());
        $data = array();
        $start = $this->input->post("start");
        foreach ($list as $users) {
            $start++;
            $row = array();
            $row["id"] = $users->id;
            $row["name"] = $users->name;
			$row["email"] = $users->email; 
			$row["status"] = $users->status;               
          	$row["phone"] = $users->phone;            
            $row["created"] = $users->created;            
            $data[]=$row;
        }
 
        $response = array(
            "draw"            => $this->input->post('draw'),
            "recordsTotal"    => $this->admin_mapi->count_all(),
            "recordsFiltered" => $this->admin_mapi->count_filtered('sales_man'),
            "data"            => $data,
        );
     	$this->response($response);
	}

	public function getInstallers_post(){		

		$response=array();
     	$list = $this->admin_mapi->getInstallers($this->input->post());
        $data = array();
        $start = $this->input->post("start");
        foreach ($list as $users) {
            $start++;
            $row = array();
            $row["id"] = $users->id;
            $row["name"] = $users->name;
          	$row["email"] = $users->email;            
			$row["status"] = $users->status;               
			$row["phone"] = $users->phone;            
            $row["created"] = $users->created;            
            $data[]=$row;
        }
 
        $response = array(
            "draw"            => $this->input->post('draw'),
            "recordsTotal"    => $this->admin_mapi->count_all(),
            "recordsFiltered" => $this->admin_mapi->count_filtered('installer'),
            "data"            => $data,
        );
     	$this->response($response);
	}

	public function changeUserStatus_post(){
		$data=json_decode((file_get_contents("php://input")));
		if($data->id == "" || $data->status == "" || $data->id == NULL || $data->status == NULL){
    		$message = array("status_code"=>400,"success"=>0,"message"=>"Missing parameters.");
            $this->response($message, 400);
    	}else{
			$result = $this->admin_mapi->changeUserStatus($data);
			$this->response($result['response'], $result['code']);
    	}
	}

	public function addUser_post(){
		$data=json_decode((file_get_contents("php://input")));
		$data->site_id = $data->site_url;
		unset($data->site_url);
		if(!$data->first_name && !$data->last_name && !$data->email && !$data->phone && !$data->specific_code){
    		$message = array("status_code"=>400,"success"=>0,"message"=>"Missing parameters.");
            $this->response($message, 400);
    	}else{
			$result = $this->admin_mapi->addUser($data);
			$this->response($result['response'], $result['code']);
    	}
	}	

	public function updateUser_post(){
		$data=json_decode((file_get_contents("php://input")));	
		$result = $this->admin_mapi->updateUser($data);
		$this->response($result['response'], $result['code']);
	}

	public function getReferrals_post(){		
		$result = $this->admin_mapi->getReferrals($this->input->post());
		$this->response($result['response'], $result['code']);
	}

	public function checkField_post(){
		$data=json_decode((file_get_contents("php://input")));		
		$result = $this->admin_mapi->checkField($data);		
		$this->response($result['response'], $result['code']);
	}

	public function checkPhone_post(){
		$data = json_decode(json_encode($this->input->post()));
		$data->field = 'phone';
		$data->value = $data->phone;
		$result = $this->admin_mapi->checkField($data);
		if($result['code'] == 200){
			echo 'true';
		}else{
			echo 'false';
		}		
		/*	print_r($result);
		exit;*/
	}

	public function checkEmail_post(){
		$data = json_decode(json_encode($this->input->post()));
		$data->field = 'email';
		$data->value = $data->email;
		$result = $this->admin_mapi->checkField($data);
		if($result['code'] == 200){
			echo 'true';
		}else{
			echo 'false';
		}		
		/*	print_r($result);
		exit;*/
	}

	public function viewUser_post(){
		$data=json_decode((file_get_contents("php://input")));
				
		$result = $this->admin_mapi->viewUser($data->id);
		$this->response($result['response'], $result['code']);
	}

	public function getReferral_post(){
		$data=json_decode((file_get_contents("php://input")));
				
		$result = $this->admin_mapi->getReferral($data->id);
		$this->response($result['response'], $result['code']);
	}
	
	public function getPower_post(){
		$data=json_decode((file_get_contents("php://input")));
		
		$result = $this->admin_mapi->getPower($data);
		$this->response($result['response'], $result['code']);
	}

	public function change_status_post(){
		$data=json_decode((file_get_contents("php://input")));
		$result = $this->admin_mapi->change_status($data);
		$this->response($result['response'], $result['code']);
	}

	public function make_sold_post(){
		$data=json_decode((file_get_contents("php://input")));
		// $data=$this->input->post();
		if(!$data->id || !$data->amount){
			$this->response(array("code"=>400,"message"=>"Missing parameters."),400);
		}else{			
			$result = $this->admin_mapi->make_sold($data);
			$this->response($result['response'], $result['code']);	
		}
	}

	public function getSales_post(){
		$result = $this->admin_mapi->getSales($this->input->post());
		$this->response($result['response'], $result['code']);
	}

	public function add_app_user_post(){
		$data=json_decode((file_get_contents("php://input")));
		
		$result = $this->admin_mapi->add_app_user($data);
		$this->response($result['response'], $result['code']);	
	}

	public function get_settings_get(){
		$result = $this->admin_mapi->getSettings();
		$this->response($result['response'], $result['code']);	
	}

	public function save_settings_post(){
		$data=json_decode((file_get_contents("php://input")));
		$result = $this->admin_mapi->save_settings($data);
		$this->response($result['response'], $result['code']);	
	}

	public function get_admin_email_settings_get(){
		$result = $this->admin_mapi->getEmailSettings();
		$this->response($result['response'], $result['code']);	
	}

	public function save_admin_email_settings_post(){
		$dataList=json_decode((file_get_contents("php://input")));
		$insertData = [];
		if(!empty($dataList)){
			$i = 0;
			foreach($dataList as $data){
				$insertData[$i]['email'] = $data->email;
				$i++;
			}
		}
		$result = $this->admin_mapi->save_email_settings($insertData);
		$this->response($result['response'], $result['code']);	
	}

	public function getDashboardData_get(){
		$result = $this->admin_mapi->getDashboardData();
		$this->response($result['response'], $result['code']);		
	}

	public function search_user_get(){		
		$result = $this->admin_mapi->search_user($this->input->get());
		$this->response($result['response']);	
	}

	public function testPush_post(){
		$result =$this->admin_mapi->testPush($this->input->post('id'));
		$this->response($result['response'], $result['code']);
	}

	public function testPaypal_post(){
		$result =$this->admin_mapi->testPaypal($this->input->post());
		$this->response($result['response'], $result['code']);
	}

	function add_referral_post(){
		$params=json_decode((file_get_contents("php://input")));
		$data = $params->add_referral_details;
		// print_r($data);
		// exit;
		$this->load->model('mapi');
		
		if(!$data->first_name || !$data->last_name || !$data->phone || !$data->referred_by){
			$message = array("status_code"=>400,"success"=>0,"message"=>"Missing parameters.");
            $this->response($message, 400);
		}else{
			$result = $this->mapi->add_referral($data);
			$this->response($result['response'], $result['code']);
		}
	}

	public function exportUsers_post(){
		//Get Users
		$userData = $this->admin_mapi->getUsersToExport();
		$result = $userData['users'];

		// Starting the PHPExcel library
        $this->load->library('excel');
 
       /* $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setTitle("export")->setDescription("none");*/
 
        $this->excel->setActiveSheetIndex(0);
 
        // Field names in the first row
        $fields = $userData['fields'];
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
        $filename = FCPATH."users.xls";
        $this->excel->save($filename);
        return $this->response(array("file"=>"users.xls"),200);
	}


	//get job listing
	public function getJobs_post(){
        $response=array();
        $list = $this->admin_mapi->getJobListing($this->input->post());
        $data = array();
        $start = $this->input->post("start");
       
        foreach ($list as $jobs) {
            $start++;
            $row = array();
            $row["id"] = $jobs->id;            
            $row["user_name"] = $jobs->user_name;
            $row["sales_man"] = $jobs->sales_man;
            $row["installer"] = $jobs->installer;
            $row["created"] = $jobs->created;
                                   
            $data[]=$row;
        }
        $response = array(
            "draw"            => $this->input->post('draw'),
            "recordsTotal"    => $this->admin_mapi->jobs_count_all($this->input->post()),
            "recordsFiltered" => $this->admin_mapi->jobs_count_filtered($this->input->post()),
            "data"            => $data
        );
        $this->response($response);
	}
	
	//Bid listing
	public function getbidlisting_post(){
        $data=json_decode((file_get_contents("php://input")));
        if($data->id == "" || $data->id == NULL){
    		$message = array("status_code"=>400,"success"=>0,"message"=>"Missing parameters.");
            $this->response($message, 400);
    	}else{
            $result = $this->admin_mapi->get_bid_listing($data->id);
			$this->response($result['response'], $result['code']);
    	}
	}
	
	//accept bids
	public function acceptbids_post(){
        $post =json_decode((file_get_contents("php://input")));
        if($post->id == "" || $post->id == NULL ){
    		$message = array("status_code"=>400,"success"=>0,"message"=>"Missing parameters.");
            $this->response($message, 400);
    	}else{
            $checkDuplicateJobs = $this->admin_mapi->checkDuplicateJobs($post);
            if($checkDuplicateJobs->num_rows() > 0){
                $message = array("status_code"=>400,"success"=>0,"message"=>"Already bid accepted for same job.");
                $this->response($message, 400);
            }
            $this->admin_mapi->acceptbids($post);
			$data = $this->db->get_where('tbl_bids',array('id'=> $post->id))->first_row();
			$data = $this->db->get_where('tbl_user',array('id'=> $data->user_id))->first_row();
			$user_id = $data->id;
			$msg="Congratulation You Won Bid";
			$registration_id = $data->device_token;
			$device_type = $data->device_type;
			$this->admin_mapi->Notify($user_id, $msg, $registration_id, $device_type,0);
            $result = $this->admin_mapi->get_bid_listing($post->job_id);
			$this->response($result['response'], $result['code']);
    	}
    }
}
