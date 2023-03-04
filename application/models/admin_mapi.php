<?php
/**
* 
*/
class Admin_mapi extends CI_Model
{
	var $primary_key = 'id';
	var $table  = 'tbl_user';
	var $column = array('first_name','last_name','email','phone');
	var $order = array('created' => 'desc');

	function __construct()
	{
		parent::__construct();		
		$this->load->model('genral_model');
	}
	/*Admin Login*/
	function login($data){
		$query = $this->db->get_where('tbl_admin',array("email"=>$data->email,"password"=>$data->password));
		if($query->num_rows() === 0){
			return array("response"=>array("status_code"=>400,"message"=>"Invalid Credentials."),"code"=>400);		
		}else{
			$row = $query->first_row();
			$sess_array = array(
                   'id' => $row->admin_id,                                   
                   'username' => $row->email                                                     
            );                 
			/*print_r($sess_array);
			exit;*/
            if(!empty($sess_array))
            {  
                $this->session->set_userdata('logged_in', $sess_array);
            }
            return array("response"=>array("status_code"=>200,"data"=>"Logged in successfully"),"code"=>200);             
		}
	}

	//New Function for dattabble
	private function _get_users_query($data=array(), $type="")
    {
      //$search= isset($data["search"]) ? $data["search"] : "";
      $this->db->select("id,CONCAT(first_name,' ',last_name ) AS name ,email,status,created,phone");     
      $this->db->from($this->table);
      $this->db->where("type",$type); 
	  $i = 0;
	  if($_POST['search']['value']){
		$this->db->group_start();
		  foreach ($this->column as $item){
			if($_POST['search']['value'])
			{
			//$this->db->group_start();
			  if($i===0){
				  $this->db->like($item, $_POST['search']['value']);
			  }elseif($item == "email"){ 
				  $this->db->or_like($item, $_POST['search']['value']);
			  }else{
				$this->db->or_like($item, $_POST['search']['value']);
			  } 
			}
			$column[$i] = $item;
			$i++;
		} 
		$this->db->group_end();  
	  }

      /*if(isset($_POST['order']))
      {
        $this->db->order_by($column[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
      }
      else if(isset($this->order))
      {
      }*/
      $order = $this->order;
	  $this->db->order_by(key($order), $order[key($order)]);
	}
	
    public function getAppUsers($data){
        $this->_get_users_query($data, 'app_user');
        if($data['length'] != -1)
        $this->db->limit($data['length'],$data['start']);
        $query = $this->db->get();
        return $query->result();
	}
	
	public function getSalesMen($data){
        $this->_get_users_query($data, 'sales_man');
        if($data['length'] != -1)
        $this->db->limit($data['length'],$data['start']);
        $query = $this->db->get();
        return $query->result();
	}
	
	public function getInstallers($data){
        $this->_get_users_query($data, 'installer');
        if($data['length'] != -1)
        $this->db->limit($data['length'],$data['start']);
        $query = $this->db->get();
		return $query->result();
		
		//print_r($this->db->last_query());die;

    }

    function count_filtered($type="")
    {
        $this->_get_users_query(null, $type);
        $query = $this->db->get();
        return $query->num_rows();
    }
 
    public function count_all()
    {
        $this->db->from("tbl_user");
        return $this->db->count_all_results();
    }

    public function search_user($data){
    	
    	$query = $this->db->query("SELECT  `id` , CONCAT( first_name,  ' ', last_name ) AS name,  `email` ,  `phone` 
			FROM  `tbl_user` 
			WHERE  `type` =  'app_user' AND (`first_name` LIKE '%".$data['name']."%' OR  `last_name` LIKE '%".$data['name']."%')");
    	
    	$result = $query->result_array();
    	return array("response"=>array("results"=>$result),"code"=>400);
    }


	/*Add app user manualy by admin*/
	function addUser($data){
		$data->type = "app_user";
		$password = $data->specific_code;		
		$email_data = array(
				"first_name"=>$data->first_name,
				"last_name"=>$data->last_name,
				"email"=>$data->email,
				"phone"=>$data->phone,
				"specific_code"=>$password,
			);
		$data->password = sha1($data->specific_code);
		unset($data->specific_code);
		
		$this->db->where('email', $data->email);
		$this->db->or_where('phone', $data->phone); 		
		$query = $this->db->get('tbl_user');
		if($query->num_rows() === 0){
			$this->db->insert('tbl_user', $data);
			$sent = send_email($data->email,'Go Solar|App Credentials','template.php' ,$email_data);
			return array("response"=>array("success"=>1,"status_code"=>200,"message"=>"User added successfully."),"code"=>200);			
		}else{
			return array("response"=>array("success"=>0,"status_code"=>400,"message"=>"Either email or phone is already registered."),"code"=>400);
		}
	}

	/*Update APIkey and Site id*/
	function updateUser($data){

		//Check if email is unique
		$this->db->where('email', $data->email);
		$this->db->where('id !=', $data->id);
		$CheckEmail = $this->db->get('tbl_user');
		if($CheckEmail->num_rows() > 0){
			return array("response"=>array("success"=>0,"status_code"=>400,"message"=>"Email is already registered."),"code"=>400);	
		}

		//Check if Phone is unique
		$this->db->where('phone', $data->phone);
		$this->db->where('id !=', $data->id);
		$CheckEmail = $this->db->get('tbl_user');
		if($CheckEmail->num_rows() > 0){
			return array("response"=>array("success"=>0,"status_code"=>400,"message"=>"Phone is already registered."),"code"=>400);	
		}
		//Update user
		$this->db->where("id",$data->id);
		$query = $this->db->update('tbl_user',$data);
		return array("response"=>array("success"=>1,"status_code"=>200,"message"=>"User updated successfully."),"code"=>200);
	}
	/*Get users referred by go solar app users*/
	function getReferrals($data){
		/*print_r($data);	
		exit;*/
		$limit = $data['length'];
        $offset = $data['start'];     
        $count_query = $this->db->query("SELECT u.id,CONCAT( u.first_name,  ' ', u.last_name ) AS name, u.email, u.phone,u.address,u.city,u.state,u.user_type,u.created, CONCAT( t.first_name,  ' ', t.last_name ) AS referrar
			FROM tbl_user u
			INNER JOIN tbl_user t ON u.referred_by = t.id
			WHERE u.type =  'referral' AND u.user_type != 'sales'");
        $count = $count_query->num_rows();
		$result = $this->db->query("SELECT u.id, CONCAT( u.first_name,  ' ', u.last_name ) AS name,u.email, u.phone,u.address,u.city,u.state,u.user_type,u.created, CONCAT( t.first_name,  ' ', t.last_name ) AS referrar
			FROM tbl_user u
			INNER JOIN tbl_user t ON u.referred_by = t.id
			WHERE u.type =  'referral' AND u.user_type != 'sales' order by u.updated DESC LIMIT ".$offset.",".$limit)->result_array();		
				
		$res = array(
			"draw"=> $data['draw'],
            "recordsTotal"=> $count,
            "recordsFiltered"=> $count,
            "data"=> $result
			);
		return array("response"=>$res,"code"=>200);			
	}

	/*Check if email and mobile number is uniuqe*/
	function checkField($data){
		$field = $data->field;
		$value = $data->value;

		$where = array($field=>$value);
		if(isset($data->id)){
			$where['id !='] = $data->id;
		}
		
		$query = $this->db->get_where('tbl_user',$where);
		
		if($query->num_rows() === 0){
			return array("response"=>array("status_code"=>200,"message"=>"Field is unique."),"code"=>200);
		}else{
			return array("response"=>array("status_code"=>400,"message"=>"Field is already exist."),"code"=>400);
		}
	}

	function viewUser($id){
		$query = $this->db->get_where('tbl_user',array("id"=>$id));
		$result = $query->first_row();	
		
		return array("response"=>array("status_code"=>200,"data"=>$result),"code"=>200);
	}
	
	function getReferral($id){
		$result  = $this->db->query("SELECT u.id,u.first_name, u.last_name, u.email, u.phone,u.address,u.city,u.state,u.user_type,u.created, CONCAT( t.first_name,  ' ', t.last_name ) AS referrar
			FROM tbl_user u
			INNER JOIN tbl_user t ON u.referred_by = t.id
			WHERE u.id=".$id)->first_row();
		return array("response"=>array("status_code"=>200,"data"=>$result),"code"=>200);
	}	

	function getPower($data){

		if($data->site_url == '2'){
			$endpoint = 'https://api.enphaseenergy.com/api/v2/systems/'.$data->site_id.'/summary';
			$ch = @curl_init();
					
			@curl_setopt($ch, CURLOPT_URL, $endpoint);
			@curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			@curl_setopt($ch, CURLOPT_HEADER, 0);
			@curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
			@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$response = @curl_exec($ch);
			$status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$curl_errors = curl_error($ch);
			@curl_close($ch);
		}
		else{
			$endpoint = 'https://monitoringapi.solaredge.com/site/'.$data->site_id.'/overview?api_key='.$data->api_key;
			$ch = @curl_init();
					
			@curl_setopt($ch, CURLOPT_URL, $endpoint);
			@curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			@curl_setopt($ch, CURLOPT_HEADER, 0);
			@curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, false);
			@curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$response = @curl_exec($ch);
			$status_code = @curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$curl_errors = curl_error($ch);
			@curl_close($ch);
		}

		return array("response"=>array("status_code"=>200,"data"=>json_decode($response)),"code"=>200);
		
	}

	function changeUserStatus($data){
		
		$this->db->where('id',$data->id);
		$this->db->update('tbl_user',array("status"=>$data->status));	
		return array("response"=>array("success"=>1,"status_code"=>200,"message"=>"Status updated successfully."),"code"=>200);
	}

	/*Change user's status from Referral to Opportunity*/
	function change_status($data){
		

		$currentUser = $this->db->get_where('tbl_user',array("id"=>$data->data->id))->first_row();
		$parent_id = $currentUser->referred_by;
		
		//Get Parent user				
		$parent = $this->db->select('id,device_type,device_token,money,paypal_id,referred_by')->get_where('tbl_user',array("id"=>$parent_id))->first_row();
		$creditAmount = $data->data->amount;
		$credit = $parent->money + $creditAmount;							
		
		
		//Transfer Money to paypal Account
		// $result = $this->genral_model->payMoney(array("email"=>$parent->paypal_id,"amount"=>10));
		// if($result == true){
			$this->db->where('id',$data->data->id);
			$this->db->update('tbl_user',array("user_type"=>$data->status));	

			//Update money to parent and send notification
			$this->db->where('id',$parent_id);
			$this->db->update('tbl_user',array("money"=>$credit,"updated"=>date('Y-m-d H:i:s')));
			//Send Notification to Parent
			$msg = "You earned $".$creditAmount;
			$result = $this->Notify($parent->id,$msg,$parent->device_token,$parent->device_type, $creditAmount);
			return array("response"=>array("success"=>1,"status_code"=>200,"message"=>"Status updated successfully."),"code"=>200);
		// }else{
		// 	return array("response"=>array("success"=>1,"status_code"=>400,"message"=>"Paymanet failed, Please try again after sometime"),"code"=>400);
		// }
	}

	/*Make referral user as sales---
	|Transfer amount to parent users|
	-------------------------------*/
	function make_sold($data){
		//Mark Last level user as sales
		$this->db->where('id',$data->id);
		$this->db->update('tbl_user',array("user_type"=>"sales","updated"=>date('Y-m-d H:i:s')));
		/*------------------------------------------------------*/
		
		//Get total Levals
		$this->db->order_by("order", "desc"); 
		$Levels = $this->db->get('tbl_network')->result_array();
		// print_r($Levels);
		$this->db->select('id,referred_by');
		$currentUser = $this->db->get_where('tbl_user',array("id"=>$data->id))->first_row();
		$parent_id = $currentUser->referred_by;
		//Add Money to parent users
		for($i=0;$i<count($Levels);$i++){			
			if($parent_id != 0){
				//Get Parent user				
				$parent = $this->db->select('id,device_type,device_token,money,referred_by')->get_where('tbl_user',array("id"=>$parent_id))->first_row();
				$creditAmount = ($data->amount * $Levels[$i]['network_percentage'])/100;
				$credit = $parent->money + $creditAmount;			
				
				//Update money to parent and send notification
				$this->db->where('id',$parent_id);
				$this->db->update('tbl_user',array("money"=>$credit,"updated"=>date('Y-m-d H:i:s')));
				
				//Send Notification to Parent
				$msg = "You earned $".$creditAmount;
				$this->Notify($parent->id,$msg,$parent->device_token,$parent->device_type, $creditAmount);
				
				//Set paretn of parent
				$parent_id = $parent->referred_by;
			}
		}
		return array("response"=>array("success"=>1,"status_code"=>200,"message"=>"User successfully marked as sales."),"code"=>200);
	}
	//Get Users with status sales
	function getSales ($data){
		$limit = $data['length'];
        $offset = $data['start'];

		$this->db->select("id, CONCAT(first_name,' ',last_name ) AS name, phone,type, updated");      
        $this->db->from('tbl_user');
        $this->db->where("user_type","sales");
        $query = $this->db->get();
        $count = $query->num_rows();
        $this->db->limit($limit);	
        $this->db->offset($offset);	
		$result = $query->result();
		
		$res = array(
			"draw"=> $data['draw'],
            "recordsTotal"=> $count,
            "recordsFiltered"=> $count,
            "data"=> $result
			);
		return array("response"=>$res,"code"=>200);
	}

	//Add sales user to APP
	function add_app_user($data){
		if(isset($data->email) || !empty($data->email)){
			$Equery = $this->db->get_where('tbl_user',array("email"=>$data->email,"id !="=>$data->id));	
			if($Equery->num_rows() > 0){
				return array("response"=>array("success"=>0,"status_code"=>400,"message"=>"Email is already exist."),"code"=>200);							
			}
		}
		$data->type = "app_user";
		$password = $data->specific_code;		
		$email_data = array(
				"first_name"=>$data->first_name,
				"last_name"=>$data->last_name,
				"email"=>$data->email,
				"phone"=>$data->phone,
				"specific_code"=>$password,
			);
		$data->password = sha1($data->specific_code);
		$id = $data->id;
		unset($data->specific_code);
		unset($data->id);
		$this->db->where("id",$id);
		$this->db->update('tbl_user', $data);
		// print_r($email_data);
		// exit;
		$sent = send_email($data->email,'Go Solar|Credentials','template.php' ,$email_data);
		return array("response"=>array("success"=>1,"status_code"=>200,"message"=>"User added successfully."),"code"=>200);
	}


	function getSettings(){
		$result = $this->db->select('network_name,network_percentage')->get('tbl_network')->result_array();
		return array("response"=>array("status_code"=>200,"data"=>$result),"code"=>200);
	}

	function save_settings($data){		
		$this->db->empty_table('tbl_network'); 
		
		for($i=0;$i< count($data);$i++){
				// print_r($data[$i]);
			// $this->db->insert('tbl_network',$data[$i]);
			if(!empty((array) $data[$i])){				
				$data[$i]->order = $i+1;
				$data[$i]->id = $i+1;
				$this->db->insert('tbl_network',$data[$i]);
			}
		}
		return array("response"=>array("status_code"=>200,"message"=>"success"),"code"=>200);
	}

	function getEmailSettings(){
		$result = $this->db->select('id,email')->get('tbl_mail_setting')->result_array();
		return array("response"=>array("status_code"=>200,"data"=>$result),"code"=>200);
	}

	function save_email_settings($data){		
		$this->db->empty_table('tbl_mail_setting'); 
		$this->db->insert_batch('tbl_mail_setting', $data); 
		return array("response"=>array("status_code"=>200,"message"=>"success"),"code"=>200);
	}

	function getDashboardData(){
		$appUsers = $this->db->get_where('tbl_user',array("type"=>"app_user"))->num_rows();
		$ReferralUsers = $this->db->get_where('tbl_user',array("user_type"=>"referral","type"=>"referral"))->num_rows();
		$OpportunityUsers = $this->db->get_where('tbl_user',array("user_type"=>"opportunity"))->num_rows();
		$SalesUsers = $this->db->get_where('tbl_user',array("user_type"=>"sales"))->num_rows();
		
		$this->db->select_sum('amount');
		$ReferralAmount = $this->db->get('tbl_notification')->first_row();
		if($ReferralAmount->amount){
			$amount = $ReferralAmount->amount;
		}else{
			$amount = 0;
		}
		$data = array(
			"appUsers"=>$appUsers,
			"ReferralUsers"=>$ReferralUsers,
			"OpportunityUsers"=>$OpportunityUsers,
			"SalesUsers"=>$SalesUsers,
			"ReferralAmount"=>$amount
			);

		return array("response"=>array("status_code"=>200,"data"=>$data),"code"=>200);
	}

	function testPush($id){
		$result = $this->db->get_where('tbl_user',array("id"=>$id))->first_row();
		$this->Notify($id, 'Test FCM push',$result->device_token,0,0);
		return array("response"=>array("status_code"=>200,"data"=>"Called"),"code"=>200);
	}

	function testPaypal($data){
		$result = $this->genral_model->payMoney($data);
		return array("response"=>array("status_code"=>200,"data"=>$result),"code"=>200);
	}

	/*Send Notification*/
	function Notify($user_id, $msg, $registration_id, $device_type, $amount){
		if($device_type == 0){			
			$pushArray = array(							
					"message"=>$msg,
					"registration_id"=>$registration_id,							
					"user_id"=> $user_id,
					"amount"=>$amount
				);					
			$result = $this->genral_model->sendPush($pushArray);
			return $result;				
		}else{
			//Send IOS PUSH
		}	
	}

	function getUsersToExport(){
		$query = $this->db->select("u.id,u.first_name,u.last_name,u.phone,u.email,u.city,u.state,u.address,CONCAT(user.first_name,' ',user.last_name) as referred_by")	
						  ->from("tbl_user as u")
						  ->join("tbl_user as user","u.referred_by=u.id",'left')
						  ->where("u.type","app_user")
						  ->order_by("u.created","desc")
						  ->get();
						  
	    return array("fields"=>$query->list_fields(),"users"=>$query->result());						  
	}	


	public function getJobListing($data){
        $this->_get_jobs_query($data);
        if($data['length'] != -1)
        $this->db->limit($data['length'],$data['start']);
        $query = $this->db->get();
        return $query->result();
	}
	
	function jobs_count_filtered()
    {
        $this->_get_jobs_query(null);
        $query = $this->db->get();
        return $query->num_rows();
    }
 
    public function jobs_count_all()
    {
        $this->db->from("tbl_jobs");
        return $this->db->count_all_results();
	}
	
	//get bid listing
	public function get_bid_listing($job_id){
		$this->db->select('b.job_id,b.id,b.amount,CONCAT(u.first_name,  " ", u.last_name ) AS user_name,b.is_won');
		$this->db->from('tbl_bids as b');
		$this->db->join('tbl_user as u','b.user_id = u.id');
		$this->db->where('b.job_id',$job_id);
		$query = $this->db->get();

		if($query->num_rows() === 0){
			return array("response"=>array("success"=>1,"status_code"=>200,"message"=>"Job not found!","data"=>$query->result()),"code"=>200);
		}else{
			return array("response"=>array("success"=>1,"status_code"=>200,"message"=>"Success!","data"=>$query->result()),"code"=>200);
		}
		// return $data->result();
	}

	//check bid already accepted with same job id
	public function checkDuplicateJobs($data){
		$this->db->select('*');
		$this->db->from('tbl_bids');
		$this->db->where('job_id',$data->job_id);
		$this->db->where('is_won','1');
		$data = $this->db->get();
		return $data;
	}

	//accept bids 
	public function acceptbids($data){
		$this->db->where("id",$data->id);
		$query = $this->db->update('tbl_bids',$data);
		return array("response"=>array("success"=>1,"status_code"=>200,"message"=>"Job updated successfully."),"code"=>200);
	}

	 //New Function for dattabble
	 private function _get_jobs_query($data=array())
	 {
		 $column =  array('j.id','user.first_name','user.last_name','j.created');
		 $this->db->select('j.*,CONCAT( user.first_name,  " ", user.last_name ) AS user_name,CONCAT( s.first_name,  " ", s.last_name ) AS sales_man,CONCAT( i.first_name,  " ", i.last_name ) AS installer');
		 $this->db->from('tbl_jobs'.' as j');
		 $this->db->join('tbl_user as user','user.id=j.user_id','left');
		 $this->db->join('tbl_user as s','s.id=j.sales_man_id','left');
		 $this->db->join('tbl_user as i','i.id=j.installer_id','left');
	   $i = 0;
	   if($_POST['search']['value']){
		 $this->db->group_start();
		   foreach ($column as $item){
			 if($_POST['search']['value'])
			 {
			 //$this->db->group_start();
			   if($i===0){
				   $this->db->like($item, $_POST['search']['value']);
			   }elseif($item == "user.first_name"){ 
				   $this->db->or_like($item, $_POST['search']['value']);
			   }else{
				 $this->db->or_like($item, $_POST['search']['value']);
			   } 
			 }
			 $column[$i] = $item;
			 $i++;
		 } 
		 $this->db->group_end();  
	   }
 
	   $order = array('j.created' => 'desc');
	   $this->db->order_by(key($order), $order[key($order)]);
	 }
	 				
}	

?>
