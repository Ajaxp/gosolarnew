<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if( !function_exists('check_login'))
{
    function check_login()
    {
        $ci =& get_instance();
        if(!$ci->session->userdata('logged_in'))
        {           
            //If no session, redirect to login page
            redirect('user/login', 'refresh');
        }        
    }
}

if( !function_exists('has_permission'))
{
    function has_permission()
    {
        $ci =& get_instance();
        if($ci->session->userdata('logged_in'))
        {
            $session_data = get_session_data();
            $url_user_type = $ci->uri->segment(1, 0);
            if(ucfirst($url_user_type) != $session_data['user_type'])
            {
                redirect_to_dashboard();
            }            
        }
    }
}

if( !function_exists('check_status'))
{
    function check_status()
    {
        $ci =& get_instance();
        if($ci->session->userdata('logged_in'))
        {
            $user_detail = $ci->musers->get_detail(get_logged_in_user_id());
            if(!$user_detail)
            {
                $ci->session->set_flashdata('error_message', 'You are deleted by admin');
                $ci->session->unset_userdata('logged_in');
                redirect('user/login', 'refresh');
            }
            else if($user_detail['user_status'] == 0)
            {
                $ci->session->set_flashdata('error_message', 'Your account is inactivated by admin');
                $ci->session->unset_userdata('logged_in');
                redirect('user/login', 'refresh');
            }                        
        }
    }
}

if( !function_exists('redirect_to_dashboard'))
{
    function redirect_to_dashboard()
    {
        $ci =& get_instance();
        if($ci->session->userdata('logged_in'))
        {           
            $session_data = $ci->session->userdata('logged_in');            
            if($session_data['user_type'] == 'Admin'){
              redirect('admin/dashboard', 'refresh');
            }elseif($session_data['user_type'] == 'Vendor'){
              redirect('vendor/dashboard', 'refresh');
            }            
        }
        exit;
    }
}

if( !function_exists('get_session_data'))
{
    function get_session_data()
    {
        $ci =& get_instance();
        $session_data = $ci->session->userdata('logged_in');
        return $session_data;
    }
}

if( !function_exists('get_language_session_data'))
{
    function get_language_session_data()
    {
        $ci =& get_instance();
        $session_data = $ci->session->userdata('site_lang');
        return $session_data;
    }
}

if( !function_exists('get_template_dir_session_data'))
{
    function get_template_dir_session_data()
    {
        $ci =& get_instance();
        $session_data = $ci->session->userdata('temp_dir');
        return $session_data;
    }
}

if( !function_exists('get_logged_in_user_id'))
{
    function get_logged_in_user_id()
    {
    	$ci =& get_instance();
    	$session_data = $ci->session->userdata('logged_in');
    	return $session_data['id'];
    }
}

if( !function_exists('is_logged_in'))
{
    function is_logged_in()
    {
      $ci =& get_instance();
      if($ci->session->userdata('logged_in')){
        return true;  
      } else {
        return false;
      }      
    }
}

if( !function_exists('pr'))
{
    function pr($array)
    {    	
    	echo '<pre>'; print_r($array); echo '</pre>';    	
    }
}

if ( !function_exists('get_random_password'))
{
    /**
     * Generate a random password. 
     * 
     * get_random_password() will return a random password with length 6-8 of lowercase letters only.
     *
     * @access    public
     * @param    $chars_min the minimum length of password (optional, default 6)
     * @param    $chars_max the maximum length of password (optional, default 8)
     * @param    $use_upper_case boolean use upper case for letters, means stronger password (optional, default false)
     * @param    $include_numbers boolean include numbers, means stronger password (optional, default false)
     * @param    $include_special_chars include special characters, means stronger password (optional, default false)
     *
     * @return    string containing a random password 
     */    
    function get_random_password($chars_min=8, $chars_max=8, $use_upper_case=false, $include_numbers=false, $include_special_chars=true)
    {
        $length = rand(8, 8);
        $selection = 'aeuoyibcdfghjklmnpqrstvwxz';
        if($include_numbers) {
            $selection .= "1234567890";
        }
        if($include_special_chars) {
            $selection .= "!@\"#$%&[]{}?|";
        }
        $password = "";
        for($i=0; $i<$length; $i++) {
            $current_letter = $use_upper_case ? (rand(0,1) ? strtoupper($selection[(rand() % strlen($selection))]) : $selection[(rand() % strlen($selection))]) : $selection[(rand() % strlen($selection))];
            $password .=  $current_letter;
        }
        return $password;
    }

}

if( !function_exists('admin_theme_url'))
{
    function admin_theme_url()
    {               
        return site_url() . 'assets/backend/';
    }
}

if( !function_exists('admin_url'))
{
    function admin_url()
    {               
        return site_url() . 'admin/';
    }
}

if( !function_exists('vendor_url'))
{
    function vendor_url()
    {               
        return site_url() . 'vendor/';
    }
}

if( !function_exists('get_home_url'))
{
    function get_home_url()
    {    
        if(is_logged_in()){
            $session_data = get_session_data();            
            if($session_data['user_type'] == 'Admin'){
                $home_url = admin_url();
            } else if($session_data['user_type'] == 'Vendor'){
                $home_url = vendor_url();
            }            
        } else {
            $home_url = site_url();
        }           
        return $home_url;
    }
}

if( !function_exists('load_admin_js_files'))
{
    function load_admin_js_files( $js_array=array() )
    {
        $return_js_files = '';
        foreach($js_array as $key=>$value){
          $return_js_files .= '<script src="'.admin_theme_url().'js/admin/'.$value.'.js"></script><br>';
        }
        return $return_js_files;
    }
}

if( !function_exists('load_vendor_js_files'))
{
    function load_vendor_js_files( $js_array=array() )
    {
        $return_js_files = '';
        foreach($js_array as $key=>$value){
          $return_js_files .= '<script src="'.admin_theme_url().'js/vendor/'.$value.'.js"></script><br>';
        }
        return $return_js_files;
    }
}

/***Function to replace NULL value BLANK value ***/
if( !function_exists('replacer'))
{
    function replacer(& $item, $key)
    {
        if ($item === null) {
            $item = '';
        }
    }
}

/*That will be 32 alphanumeric characters long and unique.*/
if( !function_exists('get_activation_key'))
{
    function get_activation_key()
    {
        $random_hash = md5(uniqid(rand(), true));
        return $random_hash;
    }
}

/*Email send*/
if( !function_exists('send_email'))
{
    function send_email($to='', $subject='', $template='', $data=array())
    {
        $ci =& get_instance();

        $from_email = $ci->config->item('from_email');
        $from_name  = $ci->config->item('from_name');
        $message    = $ci->load->view($template, $data, TRUE); // this will return you html data as message        

        $ci->email->from($from_email, $from_name);
        $ci->email->to($to); //recipient email address
        $ci->email->subject($subject);
        $ci->email->message($message);
        $sent = $ci->email->send();
        // var_dump($sent);
        
        if(!$sent)
        {            
            // Generate error
            return false;
        }
        else
        {   
            // Generate success
            return true;
        }

    }
}

/*
*Delete file if exists
*e.g. $DelFilePath = "/apache/htdocs/myfile.pdf"
*/
if( !function_exists('delete_file'))
{
    function delete_file($DelFilePath)
    {            
        if(file_exists($DelFilePath))
        {
            unlink($DelFilePath);
        }
    }
}

/*
*Get hours difference between two dates
*WHERE, date1 = '2015-10-27 11:00:00'
*       date2 = '2015-10-29 11:00:20'
*/
if( !function_exists('get_hours_difference_btw_two_dates'))
{
    function get_hours_difference_btw_two_dates($date1, $date2)
    {
        //echo 'date1:'.$date1.'------'; echo 'date2:'.$date2; exit;        

        $diff = strtotime($date2) - strtotime($date1);
        
        $diff_in_hrs = $diff/3600;

        return $diff_in_hrs;
    }
}

/*
*Get mins difference between two dates
*WHERE, date1 = '2015-10-27 11:00:00'
*       date2 = '2015-10-29 11:25:20'
*/
if( !function_exists('get_hours_mins_difference_btw_two_dates'))
{
    function get_hours_mins_difference_btw_two_dates($date1, $date2)
    {        
        $datetime1 = strtotime($date2);
        $datetime2 = strtotime($date1);
        $interval  = abs($datetime2 - $datetime1);
        $minutes   = round($interval / 60);
        return $minutes;        
    }
}

/*
*Get location address lat long array
*WHERE, location_address = 'Saudi Ceramic Factory, Riyadh, Riyadh Province, Saudi Arabia'
*/
if( !function_exists('get_lat_long_array_from_location_address'))
{
    function get_lat_long_array_from_location_address($location_address)
    {
        $address = urlencode($location_address);
        $url = "http://maps.google.com/maps/api/geocode/json?address=$address&sensor=false";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $response = curl_exec($ch);
        curl_close($ch);
        $response_a = json_decode($response);

        $lat_long_array = array();
        if( isset($response_a->results[0]->geometry->location->lat) && isset($response_a->results[0]->geometry->location->lng) )
        {
            $lat_long_array = array(
                'latitude' => $response_a->results[0]->geometry->location->lat,
                'longitude' => $response_a->results[0]->geometry->location->lng
            );
        }        
        return $lat_long_array;        
    }
}

/*
*Display date like Mar 10, 2001, 5:16 pm
*WHERE, date = 'Y-m-d H:i:s'
*/
if( !function_exists('display_date1'))
{
    function display_date1($date)
    {
        return date('M j, Y, g:i a', strtotime($date) );
    }
}

/*
*Display date like Mar 10, 2001, 5:16 pm
*WHERE, date = 'Y-m-d H:i:s'
*/
if( !function_exists('display_date2'))
{
    function display_date2($date)
    {
        return date('M j, Y @ g:ia', strtotime($date) );
    }
}

if( !function_exists('send_ios_pushnotification'))
{
    function send_ios_pushnotification($device_token, $arr_message, $user_id = NULL)
    {
        $ci =& get_instance();

        if($device_token)
        {            
            $ci->load->library('apn');
            $ci->apn->payloadMethod = 'enhance'; // включите этот метод для отладки
            $ci->apn->connectToPush();

            // adding custom variables to the notification
            $ci->apn->setData($arr_message);

            // sample push notification
            // $send_result = $ci->apn->sendMessage(
            //          $device_token, 
            //          'Тестовое уведомление #1 (TIME:'.date('H:i:s').')', 
            //          /*badge*/ 2, 
            //          /*sound*/ 'default'
            // );

            // send push notification
            $send_result = $ci->apn->sendMessage(
                $device_token,
                $arr_message['alert'],
                $arr_message['badge'],
                $arr_message['sound']
            );

            $ci->load->database();
            $data = array(
                'user_id' => $user_id,                
                'device_id' => $device_token,
                'device_type' => 'ios',
                'notification_message' => $arr_message['alert'],
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s')
            );

            if($send_result)
            {
                $data['status'] = 'delivered';
                $push_result = 'Success';
            }
            else
            {
                $data['status'] = 'failed';
                $data['status_message'] = $ci->apn->error;
                $push_result = $ci->apn->error;
                //log_message('error',$ci->apn->error);
            }

            // inserting data in push notification table
            $ci->db->insert('easycar_push_notifications_history', $data);

            $ci->apn->disconnectPush();
            return $push_result;
        }
    }
}

// Example loading an extension based on OS
if (!extension_loaded('openssl')) {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        dl('php_openssl.dll');
    } else {
        dl('php_openssl.so');
    }
}