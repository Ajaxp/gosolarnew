<?php 
/**
 * 
 */
class Referral_model extends CI_Model
{
    var $primary_key = 'id';
	var $table  = 'tbl_user';
	var $column = array('u.id','u.first_name','u.last_name','t.first_name','u.email','u.phone','u.updated');
	var $order = array('u.created' => 'desc');

    function __construct()
    {
       parent::__construct();       
    }

    //New Function for dattabble
    private function _get_referrals_query($data=array()){
            if($data['type'] == "opportunity"){
                $_wherenotIn = array("sales","referral");
            }else if($data['type'] == "referral"){
                $_wherenotIn = array("sales","opportunity");
            }else{
                $_wherenotIn = array("referral","opportunity");
            }
            // print_r($_POST['user_id']);       
            //$search= isset($data["search"]) ? $data["search"] : "";
            $this->db->select('u.id,u.first_name,u.last_name,u.email,u.user_type,u.phone,CONCAT( t.first_name,  " ", t.last_name ) AS referrar,u.contactInstaller,u.type,u.updated');
            $this->db->from($this->table.' as u');
            // $this->db->where("u.type",'referral');                        
            $this->db->where_not_in("u.user_type",$_wherenotIn);
            if(isset($data['id'])){
               $this->db->where("u.referred_by",$data['id']);
            }  
            if(isset($data['sales_id'])){
               $this->db->where("u.contactSalesman",$data['sales_id']);
            }
            if(isset($data['installer_id'])){
               $this->db->where("u.contactInstaller",$data['installer_id']);
            }                         
            $this->db->join('tbl_user as t','u.referred_by=t.id','inner');
            $i = 0;
	        //$this->db->group_start();
            $_like = "";
            foreach ($this->column as $item){
            if($_POST['search']['value'])
            {
                if($i===0){
                    $_like = "(".$item." LIKE '%".$_POST['search']['value']."%' ESCAPE '!'";
                    // $this->db->like($item, $_POST['search']['value']);
                }else{
                    $_like.= " OR ".$item." LIKE '%".$_POST['search']['value']."%' ESCAPE '!'";
                    // $this->db->or_like($item, $_POST['search']['value']);
                } 
            }
            $column[$i] = $item;
            $i++;
            }
            $_like.=")";
        	//$this->db->group_end();

            if($_POST['search']['value']){
                $this->db->where($_like);
            } 
            if(isset($_POST['order']))
            { 
            $this->db->order_by($column[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
            }else{
                $order = $this->order;
                $this->db->order_by(key($order), $order[key($order)]);
            }      
        
    }

    public function getJobDetail($data){
        $this->db->select('j.*,CONCAT( s.first_name,  " ", s.last_name ) AS sales_man,CONCAT( i.first_name,  " ", i.last_name ) AS installer');
        $this->db->from('tbl_jobs'.' as j');
        $this->db->join('tbl_user as s','s.id=j.sales_man_id');
        $this->db->join('tbl_user as i','i.id=j.installer_id');
        $this->db->where('j.user_id', $data->user_id);
        $this->db->where('j.installer_id', $data->installer_id);
        $query = $this->db->get();

        if($query->num_rows() === 0){
		    return array("response"=>array("success"=>1,"status_code"=>200,"message"=>"Job not found!","data"=>$query->result()),"code"=>200);
		}else{
		    return array("response"=>array("success"=>1,"status_code"=>200,"message"=>"Success!","data"=>$query->result()),"code"=>200);
		}
    }

    function updateJobDetail($data){
		$this->db->where("id",$data->id);
		$query = $this->db->update('tbl_jobs',$data);
		return array("response"=>array("success"=>1,"status_code"=>200,"message"=>"Job updated successfully."),"code"=>200);
	}

    public function getReferrals($data){
        
        $this->_get_referrals_query($data);
        if($data['length'] != -1)
        $this->db->limit($data['length'],$data['start']);
        $query = $this->db->get();
        // print_r($this->db->last_query());
        // exit;
        return $query->result();
    }

    function count_filtered($data){
        $this->_get_referrals_query($data);
        $query = $this->db->get();
        return $query->num_rows();
    }

    public function count_all($data){
        if($data['type'] == "opportunity"){
            $_wherenotIn = array("sales","referral");
        }else if($data['type'] == "referral"){
            $_wherenotIn = array("sales","opportunity");
        }else{
            $_wherenotIn = array("referral","opportunity");
        }

        $this->db->select('u.id','u.first_name','u.last_name','u.email','u.phone','CONCAT( t.first_name,  " ", t.last_name ) AS referrar','u.type','u.updated');
        $this->db->from($this->table.' as u');
        // $this->db->where('u.type','referral');
        $this->db->where_not_in('u.user_type',$_wherenotIn);  
        if(isset($data['id'])){
               $this->db->where("u.referred_by",$data['id']);
        }
        $this->db->join('tbl_user as t','u.referred_by=t.id','inner');
        return $this->db->count_all_results();
    }  
}
?>