<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
error_reporting(E_ERROR | E_PARSE);
class Api extends REST_Controller
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('mapi');
	}

	/*Login API*/
	function login_post()
	{
		$data = $this->input->post('login_details');
		$data = json_decode($data);
		if (!$data->first_name && !$data->last_name && !$data->email && !$data->phone && !$data->specific_code) {
			$message = array("status_code" => 400, "success" => 0, "message" => "Missing parameters.");
			$this->response($message, 400);
		} else {
			$result = $this->mapi->login($data);
			$this->response($result['response'], $result['code']);
		}
	}

	public function staffLogin_post()
	{
		$data = $this->input->post('login_details');
		$data = json_decode($data);
		if (!$data->email || !$data->password || !$data->type) {
			$message = array("status_code" => 400, "success" => 0, "message" => "Missing parameters.");
			$this->response($message, 400);
		} else {
			$result = $this->mapi->staffLogin($data);
			$this->response($result['response'], $result['code']);
		}
	}

	public function staffLogout_post()
	{
		$data = $this->input->post('logout_details');
		$data = json_decode($data);
		if (!$data->user_id) {
			$message = array("status_code" => 400, "success" => 0, "message" => "Missing parameters.");
			$this->response($message, 400);
		} else {
			$result = $this->mapi->staffLogOut($data);
			$this->response($result['response'], $result['code']);
		}
	}

	public function signUp_post()
	{
		$data = json_decode($this->input->post('signup_data'));

		if (!$data->first_name || !$data->last_name || !$data->email || !$data->phone || !$data->type || !$data->password) {
			$message = array("status_code" => 400, "success" => 0, "message" => "Missing parameters.");
			$this->response($message, 400);
		} else {
			$result = $this->mapi->signUp($data);
			$this->response($result['response'], $result['code']);
		}
	}

	public function addJobDetail_post()
	{
		$data = json_decode((file_get_contents("php://input")));

		if (!$data->user_id || !$data->sales_man_id || !$data->installer_id || !$data->roof_material || !$data->inverter_type || !$data->story || !$data->city || !$data->state || !$data->system_size || !$data->design || !$data->price) {
			$message = array("status_code" => 400, "success" => 0, "message" => "Missing parameters.");
			$this->response($message, 400);
		} else {
			$result = $this->mapi->addJobDetail($data);
			$this->response($result['response'], $result['code']);
		}
	}

	function addLead_post()
	{
		$data = $this->input->post('add_lead_details');
		$data = json_decode($data);
		//print_r($data); die;
		if (!$data->first_name || !$data->last_name || !$data->phone || !$data->email || !$data->sales_id) {
			$message = array("status_code" => 400, "success" => 0, "message" => "Missing parameters.");
			$this->response($message, 400);
		} else {

			$user_data = new stdClass();

			$user_data->user_type = "opportunity";
			$user_data->type = "referral";
			$user_data->referred_by = $data->sales_id;
			$user_data->contactSalesman = $data->sales_id;
			$user_data->first_name = $data->first_name;
			$user_data->last_name = $data->last_name;
			$user_data->phone = $data->phone;
			$user_data->email = $data->email;

			unset($data->first_name);
			unset($data->last_name);
			unset($data->phone);
			unset($data->email);
			unset($data->sales_id);

			$job_data = new stdClass();

			$job_data = $data;

			$result = $this->mapi->addLead($user_data, $job_data);
			$this->response($result['response'], $result['code']);
		}
	}

	/*User's Update Profile API*/
	function update_profile_post()
	{
		$config['upload_path'] = './assets/img/';
		$config['allowed_types'] = 'gif|jpg|png|jpeg';
		$this->upload->initialize($config);

		$data = $this->input->post('update_profile_details');
		$data = json_decode($data);

		if (!empty($_FILES['profile_image'])) {
			if ($this->upload->do_upload('profile_image')) {
				$d['image'] = $this->upload->data();
				$file = $d['image']['file_name'];
				$result = $this->mapi->updateProfile($data, $file);
				$this->response($result['response'], $result['code']);
			} else {
				//$this->upload->display_errors();
			}
		} else {
			$result = $this->mapi->updateProfile($data, '');
			$this->response($result['response'], $result['code']);
		}
	}

	/*Add referral API*/
	function add_referral_post()
	{
		/*print_r($this->input->post('add_referral_details'));
		exit;*/
		$data = $this->input->post('add_referral_details');
		$data = json_decode($data);

		if (!$data->first_name || !$data->last_name || !$data->phone || !$data->referred_by) {
			$message = array("status_code" => 400, "success" => 0, "message" => "Missing parameters.");
			$this->response($message, 400);
		} else {
			$result = $this->mapi->add_referral($data);
			$this->response($result['response'], $result['code']);
		}
	}

	//Add Referral form web
	function addWebReferal_post()
	{
		$data = json_decode(json_encode($this->input->post()));

		if (!$data->first_name || !$data->last_name || !$data->phone || !$data->referred_by || !$data->email) {
			$message = array("status_code" => 400, "success" => 0, "message" => "Missing parameters.");
			$this->response($message, 400);
		} else {
			$result = $this->mapi->add_referral($data);

			$d['message'] = "Referral added successfully.";
			$this->load->view('success', $d);
		}
	}
	/*Dashboard API*/
	function getDashboard_data_post()
	{
		$data = $this->input->post('get_dashboard_data_details');
		$data = json_decode($data);

		if (!$data->user_id) {
			$message = array("status_code" => 400, "success" => 0, "message" => "Missing parameters.");
			$this->response($message, 400);
		} else {
			$result = $this->mapi->getDashboard_data($data->user_id);
			$this->response($result['response'], $result['code']);
		}
	}

	function getReferred_users_post()
	{
		$data = $this->input->post('referred_user_details');
		$data = json_decode($data);
		if (!$data->user_id || !$data->list_type) {
			$message = array("status_code" => 400, "success" => 0, "message" => "Missing parameters.");
			$this->response($message, 400);
		} else {
			$result = $this->mapi->getReferred_users($data, $data->limit, $data->page);
			$this->response($result['response'], $result['code']);
		}
	}

	public function getLeads_post()
	{
		$data = $this->input->post('get_lead_details');
		$data = json_decode($data);
		//print_r($data); die;
		if (isset($data->sales_id) && $data->sales_id != "") {
			if (empty($data->lead_type)) {
				$message = array("status_code" => 400, "success" => 0, "message" => "Missing parameters.");
				$this->response($message, 400);
			} else {
				$res = $this->mapi->getOpportunity($data, $data->limit, $data->page);
				$this->response($res['response'], $res['code']);
			}
		} else {
			if (!$data->list_type || !$data->limit || !$data->page) {
				$message = array("status_code" => 400, "success" => 0, "message" => "Missing parameters.");
				$this->response($message, 400);
			} else {
				if ($data->list_type = "installer") 
				{
					if(!$data->user_id)
					{
                      	$message = array("status_code" => 400, "success" => 0, "message" => "Missing parameters.");
					    $this->response($message, 400);
					}
				}
				
				$result = $this->mapi->getLeads($data, $data->limit, $data->page);
				$this->response($result['response'], $result['code']);
			}
		}
	}
	public function  getJobById_post()
	{
		$data = $this->input->post('get_jobs_details');
		$data = json_decode($data);
		if (!$data->id) {
			$message = array("status_code" => 400, "success" => 0, "message" => "Missing parameters.");
			$this->response($message, 400);
		} else {
			$result = $this->mapi->getJob($data);
			$this->response($result['response'], $result['code']);
		}
		if (isset($_FILES['file']['image'])) {
			$uploaddir = 'upload/document/';
			$uploadfile = $uploaddir . basename($_FILES['file']['name']);

			if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
				//echo "File is valid, and was successfully uploaded.\n";
			} else {
				//echo "Possible file upload attack!\n";
			}
		}
	}
	public function  editJob_post()
	{

		$data = $this->input->post('edit_jobs_details');
		$data = json_decode($data);
		$doc = $data->doc_name;
		unset($data->doc_name);

		if (!empty($_FILES)) { {
				$extension = end(explode(".", $_FILES["files"]["name"]));
				$serverFileName = 'doc_' . date("dmY") . "." . $extension;
				$_FILES['files']['name'] = $serverFileName;

				$uploadPath = 'upload/document/';
				$config['upload_path'] = $uploadPath;
				$config['allowed_types'] = '*';

				$this->upload->initialize($config);

				if ($this->upload->do_upload('files')) {

					$fileData = $this->upload->data();
					$uploadData['file_name'] = $fileData['file_name'];
					$uploadData['uploaded_on'] = date("Y-m-d H:i:s");
				}
			}

			if (!empty($uploadData)) {
				$file = $uploadData;
				if (empty($doc)) {
					$file['doc_name'] = $serverFileName;
				} else {
					$file['doc_name'] = $doc;
				}
				$result = $this->mapi->editJobs($data, $file);
				$this->response($result['response'], $result['code']);
			} else {
				$message = array("status_code" => 400, "success" => 0, "message" => "Something went wrong.");
				$this->response($message, 400);
			}
		} else {
			$result = $this->mapi->editJobs($data);
			$this->response($result['response'], $result['code']);
		}
	}

	public function acceptOrRejectLead_post()
	{
		$data = $this->input->post('lead_data');
		$data = json_decode($data);
		$data->flag = 0;

		if (!$data->user_id || !$data->type) {
			$message = array("status_code" => 400, "success" => 0, "message" => "Missing parameters.");

			$this->response($message, 400);
		} else {
			if ($data->flag == 0) {
				$result = $this->mapi->acceptLead($data);
				$this->response($result['response'], $result['code']);
			} else if ($data->flag == 1) {
				$result = $this->mapi->rejectLead($data);
				$this->response($result['response'], $result['code']);
			} else
				$message = array("status_code" => 400, "success" => 0, "message" => "Invalid Flag.");
			$this->response($message, 400);
		}
	}

	public function addNotes_post()
	{
		$data = $this->input->post('add_notes');
		$data = json_decode($data);
		if (!$data->user_id || !$data->description) {
			$message = array("status_code" => 400, "success" => 0, "message" => "Missing parameters.");
			$this->response($message, 400);
		} else {
			$result = $this->mapi->addNotes($data);
			$this->response($result['response'], $result['code']);
		}
	}
	public function addBid_post()
	{
		$data = $this->input->post('add_bid');
		$data = json_decode($data);
		if (!$data->user_id || !$data->job_id || !$data->amount) {
			$message = array("status_code" => 400, "success" => 0, "message" => "Missing parameters.");
			$this->response($message, 400);
		} else {
			$result = $this->mapi->addBid($data);
			$this->response($result['response'], $result['code']);
		}
	}

	public function  getNotes_post()
	{
		$data = $this->input->post('get_notes');
		$data = json_decode($data);

		if (!$data->user_id || !$data->limit || !$data->page) {
			$message = array("status_code" => 400, "success" => 0, "message" => "Missing parameters.");
			$this->response($message, 400);
		} else {
			$result = $this->mapi->getNotes($data, $data->limit, $data->page);
			$this->response($result['response'], $result['code']);
		}
	}
	public function editNotes_post()
	{
		$data = $this->input->post('edit_notes');
		$data = json_decode($data);
		if (!$data->note_id || !$data->description) {
			$message = array("status_code" => 400, "success" => 0, "message" => "Missing parameters.");
			$this->response($message, 400);
		} else {
			$result = $this->mapi->editNotes($data);
			$this->response($result['response'], $result['code']);
		}
	}

	public function  getWonBid_post()
	{
		$data = $this->input->post('get_won_bid');
		$data = json_decode($data);

		if (!$data->user_id) {
			$message = array("status_code" => 400, "success" => 0, "message" => "Missing parameters.");
			$this->response($message, 400);
		} else {
			$result = $this->mapi->getWonBid($data);
			$this->response($result['response'], $result['code']);
		}
	}

	public function  getCompletedJobs_post()
	{
		$data = $this->input->post('job_data');
		$data = json_decode($data);
		
		if(!$data->limit)
		{
			$limit = 10;
		}
		else
		{
			$limit = $data->limit;
		}
		if(!$data->page)
		{
		   $page  = 1;
		}
		else
		{
			$page = $data->page;
		}
        

		if (!$data->installer_id) {
			$message = array("status_code" => 400, "success" => 0, "message" => "Missing parameters.");
			$this->response($message, 400);
		} else {
			$result = $this->mapi->getCompletedJobs($data ,$limit, $page);
			$this->response($result['response'], $result['code']);
		}
	}

	
	/*Get Notification List*/
	function get_notificationList_post()
	{
		$data = $this->input->post('get_notification_details');
		$data = json_decode($data);

		if (!$data->user_id) {
			$message = array("status_code" => 400, "success" => 0, "message" => "Missing parameters.");
			$this->response($message, 400);
		} else {
			$result = $this->mapi->get_notificationList($data->user_id, $data->limit, $data->page);
			$this->response($result['response'], $result['code']);
		}
	}

	function termsCondtion_get()
	{
		$this->load->view('terms_of_use');
	}

	function privacy_policy_get()
	{
		$this->load->view('privacy_policy');
	}

	function refer_get()
	{
		$data['referred_by'] = $_GET["userid"];
		$this->load->view('referUser', $data);
	}
}
