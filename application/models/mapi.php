<?php
error_reporting(E_ERROR | E_PARSE);
class Mapi extends CI_Model
{
	public function __construct()
	{
		parent::__construct();
	}

	public function login($data)
	{
		$query = $this->db->get_where('tbl_user', array("email" => $data->email, "type" => "app_user"));

		if ($query->num_rows() == 0) {
			return array("response" => array("success" => 0, "status_code" => 404, "message" => "No account is associated with this email."), "code" => 404);
		} else {
			$query = $this->db->get_where('tbl_user', array("email" => $data->email, "password" => sha1($data->specific_code), "type" => "app_user"));
			if ($query->num_rows() == 0) {
				return array("response" => array("success" => 0, "status_code" => 401, "message" => "Invalid Password."), "code" => 401);
			} else {
				$query = $this->db->get_where('tbl_user', array("email" => $data->email, "password" => sha1($data->specific_code), "phone" => $data->phone, "type" => "app_user"));

				if ($query->num_rows() == 0) {
					return array("response" => array("success" => 0, "status_code" => 401, "message" => "Invalid first name, last name or phone."), "code" => 401);
				} else {
					$user = $query->first_row();
					unset($user->password);
					unset($user->user_type);
					$user->profile_image =  base_url() . 'assets/img/' . $user->profile_image;
					$this->db->where('id', $user->id);
					$this->db->update('tbl_user', array("device_id" => $data->device_id, "device_token" => $data->device_token));
					return array("response" => array("success" => 1, "status_code" => 200, "message" => "Logged in successfully.", "data" => $user), "code" => 200);
				}
			}
		}
	}

	public function staffLogin($data)
	{

		$query = $this->db->get_where('tbl_user', array("email" => $data->email, "type" => $data->type));

		if ($query->num_rows() == 0) {
			return array("response" => array("success" => 0, "status_code" => 404, "message" => "No account is associated with this email."), "code" => 404);
		} else {
			$query = $this->db->get_where('tbl_user', array("email" => $data->email, "password" => sha1($data->password), "type" => $data->type));
			if ($query->num_rows() == 0) {
				return array("response" => array("success" => 0, "status_code" => 401, "message" => "Invalid Password."), "code" => 401);
			} else {
				$user = $query->first_row();
				unset($user->password);
				unset($user->user_type);
				$user->profile_image =  base_url() . 'assets/img/' . $user->profile_image;
				if ($data->device_id && $data->device_token && $data->device_type != "") {
					$this->db->where('id', $user->id);
					$this->db->update('tbl_user', array("device_id" => $data->device_id, "device_token" => $data->device_token, "device_type" => $data->device_type));
				}
				return array("response" => array("success" => 1, "status_code" => 200, "message" => "Logged in successfully.", "data" => $user), "code" => 200);
			}
		}
	}

	public function staffLogOut($data)
	{
		$update_data = array("device_type" => "", "device_id" => "", "device_token" => "");

		$this->db->where('id', $data->user_id);
		$this->db->update('tbl_user', $update_data);
		return array("response" => array("success" => 1, "status_code" => 200, "message" => "Success."), "code" => 200);
	}

	function signUp($data)
	{
		$data->password = sha1($data->password);

		// $this->db->group_start();
		// $this->db->where('email', $data['email']);
		// $this->db->or_where('phone', $data['phone']); 	
		// $this->db->group_end();  

		// $this->db->where("type",$data['type']);

		$this->db->where('email', $data->email);
		$this->db->or_where('phone', $data->phone);

		$query = $this->db->get('tbl_user');
		if ($query->num_rows() === 0) {
			$this->db->insert('tbl_user', $data);
			return array("response" => array("success" => 1, "status_code" => 200, "message" => "Signed up successfully."), "code" => 200);
		} else {
			return array("response" => array("success" => 0, "status_code" => 400, "message" => "Either email or phone is already registered."), "code" => 400);
		}
	}

	function addJobDetail($data)
	{
		$this->db->insert('tbl_jobs', $data);
		return array("response" => array("success" => 1, "status_code" => 200, "message" => "Job added successfully."), "code" => 200);
	}

	public function addLead($user_data, $job_data)
	{

		$query = $this->db->get_where('tbl_user', array("phone" => $user_data->phone));
		if ($query->num_rows() == 0) {

			if (isset($user_data->email) || !empty($user_data->email)) {
				$Equery = $this->db->get_where('tbl_user', array("email" => $user_data->email));
				if ($Equery->num_rows() > 0) {
					return array("response" => array("success" => 0, "status_code" => 400, "message" => "Email already exists."), "code" => 400);
				}
			}

			$user_data->updated = date('Y-m-d H:i:s');
			$this->db->insert('tbl_user', $user_data);
			$insert_id = $this->db->insert_id();

			$job_data->user_id = $insert_id;
			$job_data->sales_man_id	= $user_data->referred_by;
			$job_data->updated = $user_data->updated;
			$this->db->insert('tbl_jobs', $job_data);
			return array("response" => array("success" => 1, "status_code" => 200, "message" => "Lead added successfully."), "code" => 200);
		} else {
			return array("response" => array("success" => 0, "status_code" => 400, "message" => "Phone already exists."), "code" => 200);
		}
	}

	public function updateProfile($data, $file)
	{
		/*print_r($data);
		exit;*/
		if ($data->phone || !empty($data->phone)) {
			$query = $this->db->get_where('tbl_user', array("phone" => $data->phone, "id!=" => $data->id));
			if ($query->num_rows() > 0) {
				return array("response" => array("success" => 0, "status_code" => 400, "message" => "Phone is already exist."), "code" => 200);
				die();
			}
		}

		if ($data->email || !empty($data->email)) {
			$query = $this->db->get_where('tbl_user', array("email" => $data->email, "id!=" => $data->id));
			if ($query->num_rows() > 0) {
				return array("response" => array("success" => 0, "status_code" => 400, "message" => "Email is already exist."), "code" => 200);
				die();
			}
		}
		if ($file) {
			$data->profile_image = $file;
			$this->db->where('id', $data->id);
			$this->db->update('tbl_user', $data);
			$query = $this->db->get_where('tbl_user', array("id" => $data->id));
			$user = $query->first_row();
			unset($user->password);
			unset($user->user_type);
			$user->profile_image =  base_url() . 'assets/img/' . $user->profile_image;
			return array("response" => array("success" => 1, "status_code" => 200, "message" => "Profile updated successfully.", "data" => $user), "code" => 200);
		} else {
			$this->db->where('id', $data->id);
			$this->db->update('tbl_user', $data);
			$query = $this->db->get_where('tbl_user', array("id" => $data->id));
			$user = $query->first_row();
			$user->profile_image = base_url() . 'assets/img/' . $user->profile_image;
			unset($user->password);
			unset($user->user_type);
			return array("response" => array("success" => 1, "status_code" => 200, "message" => "Profile updated successfully.", "data" => $user), "code" => 200);
		}
	}

	public function add_referral($data)
	{
		$data->user_type = "referral";
		$data->type = "referral";
		$query = $this->db->get_where('tbl_user', array("phone" => $data->phone));
		if ($query->num_rows() == 0) {

			if (isset($data->email) || !empty($data->email)) {
				$Equery = $this->db->get_where('tbl_user', array("email" => $data->email));
				if ($Equery->num_rows() > 0) {
					return array("response" => array("success" => 0, "status_code" => 400, "message" => "Email is already exist."), "code" => 200);
				}
			}

			$data->updated = date('Y-m-d H:i:s');
			$this->db->insert('tbl_user', $data);

			//Get refferad user name
			$referrar = $this->db->select('first_name,last_name')->get_where('tbl_user', array("id" => $data->referred_by))->first_row();

			$email_data = array(
				"user" => $data->first_name . ' ' . $data->last_name,
				"referrar" => $referrar->first_name . ' ' . $referrar->last_name,
				"mobile" => $data->phone,
				"city" => isset($data->city) ? $data->city : "N/A",
				"state" => isset($data->state) ? $data->state : "N/A",
				"email" => isset($data->email) ? $data->email : "N/A",
				"address" => isset($data->address) ? $data->address : "N/A"
			);

			// Add New Email ( 14-03-2019 )
			//$sent = send_email('alyce@gosolarpower.com','Go Solar|New Referral','refferalMailtemp.php' ,$email_data);
			//$sent = send_email('john@gosolarpower.com','Go Solar|New Referral','refferalMailtemp.php' ,$email_data);
			//$sent = send_email('david@gosolarpower.com','Go Solar|New Referral','refferalMailtemp.php' ,$email_data);
			// Add New Email ( 14-03-2019 )				
			// $sent = send_email('catherine@gosolarprogram.com','Go Solar|New Referral','refferalMailtemp.php' ,$email_data);


			// start : dynamic admin emails implemented

			$result = $this->db->select('id,email')->get('tbl_mail_setting')->result_array();				
			if(!empty($result)){
				$adminEmails = array_column($result,'email');				
				$adminEmailStr = implode(',',$adminEmails);
				$sent = send_email($adminEmailStr,'Go Solar|New Referral','refferalMailtemp.php' ,$email_data);
			}				
			//end : dynamic admin emails implemented


			// $sent = send_email('thomasl@gosolarpower.com', 'Go Solar|New Referral', 'refferalMailtemp.php', $email_data);
			// $sent = send_email('court@gosolarpower.com', 'Go Solar|New Referral', 'refferalMailtemp.php', $email_data);
			// $sent = send_email('harold@gosolarprogram.com','Go Solar|New Referral','refferalMailtemp.php' ,$email_data);					

			return array("response" => array("success" => 1, "status_code" => 200, "message" => "Invitation sent successfully."), "code" => 200);
		} else {
			return array("response" => array("success" => 0, "status_code" => 400, "message" => "Phone is already exist."), "code" => 200);
		}
	}

	public function getDashboard_data($id)
	{
		$get_referrals_count = $this->db->get_where('tbl_user', array("referred_by" => $id, "user_type" => "referral"));
		$referrals = $get_referrals_count->num_rows();

		$get_opportunity_count = $this->db->get_where('tbl_user', array("referred_by" => $id, "user_type" => "opportunity"));
		$opportunity = $get_opportunity_count->num_rows();

		$get_sales_count = $this->db->get_where('tbl_user', array("referred_by" => $id, "user_type" => "sales"));
		$sales = $get_sales_count->num_rows();
		$UserMoney = $this->db->get_where('tbl_user', array("id" => $id))->first_row();
		$count = 0;
		$this->db->select('id');
		$users = $this->db->get_where('tbl_user', array("referred_by" => $id));
		$result = $users->result();
		for ($i = 0; $i < count($result); $i++) {
			$indirect_sales = $this->db->get_where('tbl_user', array("referred_by" => $result[$i]->id, "user_type" => "sales"));
			$count = $count + $indirect_sales->num_rows();
		}

		$data = array(
			"referrals" => $referrals,
			"opportunity" => $opportunity,
			"sales" => $sales,
			"indirect_sales" => $count,
			"money" => $UserMoney->money
		);
		return array("response" => array("success" => 1, "status_code" => 200, "data" => $data), "code" => 200);
	}

	public function getReferred_users($data, $limit, $page)
	{
		$offset = $page == 1 ? 0 : ($page - 1) * $limit;

		switch ($data->list_type) {
			case 'referral':
				$this->db->select('id, first_name, last_name, updated');
				$this->db->order_by("updated", "desc");
				$query = $this->db->get_where('tbl_user', array("referred_by" => $data->user_id, "user_type" => "referral"), $limit, $offset);

				$result = $query->result();
				return array("response" => array("success" => 1, "status_code" => 200, "data" => $result), "code" => 200);
				break;
			case 'opportunity':
				$this->db->select('id, first_name, last_name,updated');
				$this->db->order_by("updated", "desc");
				$query = $this->db->get_where('tbl_user', array("referred_by" => $data->user_id, "user_type" => "opportunity"), $limit, $offset);

				$result = $query->result();
				return array("response" => array("success" => 1, "status_code" => 200, "data" => $result), "code" => 200);
				break;

			case 'sales':
				$this->db->select('id, first_name, last_name,updated');
				$this->db->order_by("updated", "desc");
				$query = $this->db->get_where('tbl_user', array("referred_by" => $data->user_id, "user_type" => "sales"), $limit, $offset);

				$result = $query->result();
				return array("response" => array("success" => 1, "status_code" => 200, "data" => $result), "code" => 200);
				break;
			case 'indirect_sales':
				$res = array();

				$this->db->select('id, first_name,last_name ');
				$this->db->order_by("updated", "desc");
				$users = $this->db->get_where('tbl_user', array("referred_by" => $data->user_id));
				$result = $users->result();
				// print_r(count($result));
				for ($i = 0; $i < count($result); $i++) {
					// print_r($result[$i]->id);
					$this->db->select('id,first_name ,last_name,updated');
					$indirect_sales = $this->db->get_where('tbl_user', array("referred_by" => $result[$i]->id, "user_type" => "sales"));
					$referred_to = $indirect_sales->result();
					if (count($referred_to) > 0) {
						$res[] = array(
							"referred_by" => $result[$i],
							"referred_to" => $referred_to
						);
					}
				}
				return array("response" => array("success" => 1, "status_code" => 200, "data" => $res), "code" => 200);
				break;
			default:
				return array("response" => array("success" => 0, "status_code" => 400, "message" => "Invalid List type."), "code" => 200);
				break;
		}
	}

	public function getLeads($data, $limit, $page)
	{
		$offset = $page == 1 ? 0 : ($page - 1) * $limit;

		if ($data->list_type == "sales_man") {
			$user_type = "referral";
		} else if ($data->list_type == "installer") {
			$user_type = "sales";
		} else {
			return array("response" => array("success" => 0, "status_code" => 400, "message" => "Invalid List type."), "code" => 400);
		}

		$this->db->join('tbl_jobs', 'u.id = tbl_jobs.user_id', 'left');
		if ($data->list_type == "installer") {
			$this->db->select('u.id,u.first_name,u.last_name,u.email,u.user_type,u.phone,tbl_jobs.state,tbl_jobs.street,IFNULL(tbl_bids.amount,"") AS amount,CONCAT( t.first_name,  " ", t.last_name) AS referrar,u.contactInstaller,u.type,u.updated,tbl_jobs.id AS job_id,tbl_jobs.city,tbl_jobs.system_size');
			$this->db->join('tbl_bids', 'tbl_jobs.id = tbl_bids.job_id AND tbl_bids.user_id = ' . $data->user_id, 'left');
		} else {
			$this->db->select('u.id,u.first_name,u.last_name,u.email,u.user_type,u.phone,tbl_jobs.state,u.contactInstaller ,CONCAT( t.first_name,  " ", t.last_name) AS referrar,u.contactInstaller,u.type,u.updated,tbl_jobs.id AS job_id,tbl_jobs.city,tbl_jobs.system_size');
		}
		$this->db->from('tbl_user as u');
		$this->db->order_by("u.created", "desc");
		//if ($data->list_type == "installer") {
		//	$this->db->join('tbl_bids', 'u.id = tbl_bids.user_id', 'left');
		//}
		$this->db->join('tbl_user as t', 'u.referred_by=t.id', 'inner');

		$this->db->where_not_in("u.type", array("sales_man", "installer"));
		$this->db->where(array("u.user_type" => $user_type));
		$this->db->limit($limit, $offset);
		$query = $this->db->get();
		// print_r($this->db->last_query());
		// die;
		$results = $query->result_array();
		$total = count($results);
		for ($i = 0; $i < $total; $i++) {
			$u_type = $results[$i]['user_type'];
			$contact_sales = $results[$i]['contactInstaller'];
			if ($u_type == "completed" || $contact_sales > 0) {
				$results[$i]['is_installed'] = true;
			} else {
				$results[$i]['is_installed'] = false;
			}
		}

		return array("response" => array("success" => 1, "status_code" => 200, "data" => $results), "code" => 200);
	}

	public function getJob($data)
	{

		$this->db->select('tbl_jobs.*,CONCAT( tbl_user.first_name,  " ", tbl_user.last_name) AS name');
		$this->db->from('tbl_jobs');
		$this->db->where(array("tbl_jobs.id" => $data->id));
		$this->db->join('tbl_user', 'tbl_user.id = tbl_jobs.user_id', 'left');
		$query = $this->db->get();
		$result = $query->first_row();
		$files = $this->db->get_where('tbl_job_documents', array("job_id" => $data->id));
		$files = $files->result_array();
		$total = count($files);
		for ($i = 0; $i < $total; $i++) {
			$files[$i]['document_url'] =  base_url() . 'upload/document/' . $files[$i]['document_url'];
		}
		$result->document = $files;
		return array("response" => array("success" => 1, "status_code" => 200, "data" => $result), "code" => 200);
	}

	public function editJobs($data, $file = array())
	{

		if ($file) {


			$total = count($file);

			$tmp = $file['file_name'];
			$this->db->insert('tbl_job_documents', array('job_id' => $data->id, "document_url" => $tmp, 'doc_name' => $file['doc_name']));

			$this->db->where('id', $data->id);
			$this->db->update('tbl_jobs', $data);
			$query = $this->db->get_where('tbl_jobs', array("id" => $data->id));
			$this->db->select('*');
			$files = $this->db->get_where('tbl_job_documents', array("job_id" => $data->id));
			$jobs = $query->first_row();
			$files = $files->result_array();
			$total = count($files);
			for ($i = 0; $i < $total; $i++) {
				$files[$i]["document_url"] =  base_url() . 'upload/document/' . $files[$i]['document_url'];
			}


			$jobs->document = $files;
			return array("response" => array("success" => 1, "status_code" => 200, "message" => "Jobs updated successfully.", "data" => $jobs), "code" => 200);
		} else {
			$this->db->where('id', $data->id);
			$this->db->update('tbl_jobs', $data);
			$query = $this->db->get_where('tbl_jobs', array("id" => $data->id));
			$files = $this->db->get_where('tbl_job_documents', array("job_id" => $data->id));
			$jobs = $query->first_row();
			$files = $files->result_array();
			$total = count($files);
			for ($i = 0; $i < $total; $i++) {
				$files[$i]["document_url"] =  base_url() . 'upload/document/' . $files[$i]['document_url'];
			}


			$jobs->document = $files;
			return array("response" => array("success" => 1, "status_code" => 200, "message" => "Jobs updated successfully.", "data" => $jobs), "code" => 200);
		}
	}




	public function getOpportunity($data, $limit, $page)
	{
		$offset = $page == 1 ? 0 : ($page - 1) * $limit;

		$this->db->select('u.id,u.first_name,u.last_name,u.email,u.user_type,u.phone,u.contactInstaller,u.type,u.updated,tbl_jobs.id AS job_id,tbl_jobs.city,tbl_jobs.system_size,tbl_jobs.state');
		$this->db->from('tbl_user as u');
		$this->db->where('u.contactSalesman', $data->sales_id);
		$this->db->join('tbl_jobs', 'u.id = tbl_jobs.user_id', 'left');
		$this->db->where('u.user_type', $data->lead_type);
		$this->db->limit($limit, $offset);

		$query = $this->db->get();
		$result = $query->result();
		return array("response" => array("success" => 1, "status_code" => 200, "data" => $result), "code" => 200);
	}

	public function acceptLead($data)
	{
		$user_data = $this->db->get_where('tbl_user', array("id" => $data->user_id))->first_row();

		if ($data->type == 'lead') {
			$update_data = array("contactSalesman" => $data->sales_id, "user_type" => "opportunity");
			if ($user_data->contactSalesman != 0) {
				return array("response" => array("success" => 0, "status_code" => 400, "message" => "Sorry! The lead was already accepted."), "code" => 200);
			}
		} else if ($data->type == 'opportunity') {
			$update_data = array("contactSalesman" => $data->sales_id, "user_type" => "sales");
		} else if ($data->type == 'sales') {
			if ($data->installer_id) {
				$update_data = array("contactInstaller" => $data->installer_id, "user_type" => "completed");
			} else {
				$update_data = array("contactSalesman" => $data->sales_id, "user_type" => "completed");
			}
		} else {
			return array("response" => array("success" => 0, "status_code" => 400, "message" => "Invalid type."), "code" => 400);
		}

		$this->db->where('id', $data->user_id);
		$this->db->update('tbl_user', $update_data);
		return array("response" => array("success" => 1, "status_code" => 200, "message" => "Success."), "code" => 200);
	}

	public function rejectLead($data)
	{
		$insert_data = array("user_id" => $data->user_id, "isRejectedBy_Id" => $data->sales_id);
		$user_data = $this->db->get_where('tbl_rejectedLeadData', $insert_data);

		if ($user_data->num_rows() === 0) {
			$this->db->insert('tbl_rejectedLeadData', $insert_data);
		}
		return array("response" => array("success" => 1, "status_code" => 200, "message" => "Lead Rejected successfully."), "code" => 200);
	}

	public function addNotes($data)
	{
		$this->db->insert('tbl_notes', $data);
		return array("response" => array("success" => 1, "status_code" => 200, "message" => "Notes added successfully."), "code" => 200);
	}

	public function addBid($data)
	{
		$this->db->insert('tbl_bids', $data);
		return array("response" => array("success" => 1, "status_code" => 200, "message" => "Bid added successfully."), "code" => 200);
	}
	public function getNotes($data, $limit, $page)
	{
		$offset = $page == 1 ? 0 : ($page - 1) * $limit;

		$this->db->select('*');
		$this->db->from('tbl_notes');
		$this->db->where(array("user_id" => $data->user_id));
		$this->db->order_by("id", "desc");
		$this->db->limit($limit, $offset);
		$query = $this->db->get();
		$result = $query->result();
		return array("response" => array("success" => 1, "status_code" => 200, "data" => $result), "code" => 200);
	}
	public function getWonBid($data)
	{
		$this->db->select('tbl_bids.*,tbl_jobs.*, CONCAT( tbl_user.first_name,  " ", tbl_user.last_name) AS name');
		$this->db->from('tbl_bids');
		$this->db->where(array("tbl_bids.user_id" => $data->user_id, "is_won" => 1));
		$this->db->join('tbl_jobs', 'tbl_bids.job_id = tbl_jobs.id', 'left');
		$this->db->join('tbl_user', 'tbl_user.id = tbl_jobs.user_id', 'left');
		$this->db->order_by("tbl_bids.id", "desc");
		$query = $this->db->get();
		$result = $query->result_array();

		return array("response" => array("success" => 1, "status_code" => 200, "data" => $result), "code" => 200);
	}

	public function editNotes($data)
	{
		$this->db->where("id", $data->note_id);
		$this->db->update('tbl_notes', array("description" => $data->description));
		return array("response" => array("success" => 1, "status_code" => 200, "message" => "Notes Updated successfully."), "code" => 200);
	}

	public function getCompletedJobs($data, $limit, $page)
	{
		$offset = $page == 1 ? 0 : ($page - 1) * $limit;

		$this->db->select('tbl_jobs.*,tbl_bids.amount, CONCAT( u.first_name,  " ", u.last_name) AS name');
		$this->db->from('tbl_user as u');
		$this->db->where(array("u.contactInstaller" => $data->installer_id, "u.user_type" => "completed", "tbl_bids.is_won" => 1));
		$this->db->join('tbl_jobs', 'u.id = tbl_jobs.user_id', 'left');
		$this->db->join('tbl_bids', 'tbl_bids.user_id = u.id', 'left');
		$this->db->order_by("tbl_bids.id", "desc");
		$this->db->limit($limit, $offset);
		$query = $this->db->get();
		$result = $query->result_array();
		return array("response" => array("success" => 1, "status_code" => 200, "data" => $result), "code" => 200);
	}
	public function get_notificationList($id, $limit, $page)
	{
		$offset = $page == 1 ? 0 : ($page - 1) * $limit;
		// echo $offset;        			
		$this->db->where('user_id', $id);
		$this->db->order_by("created", "desc");
		$query = $this->db->get('tbl_notification', $limit, $offset);
		$result = $query->result();
		return array("response" => array("success" => 1, "status_code" => 200, "data" => $result), "code" => 200);
	}
}
