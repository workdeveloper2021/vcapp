<?php defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

class Cronjobs extends REST_Controller {

	public function __construct() {
		parent::__construct();
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers,Authorization,X-API-KEY,Origin,X-Requested-With,userid,token,timeZone,timeZoneOffset,language,version,deviceId,deviceType,lat,lang,role");
         $method = $_SERVER['REQUEST_METHOD'];

        $time_zone =  'UTC';
        date_default_timezone_set($time_zone);
		
        $this->load->library('form_validation');
		$this->load->library('session');
		$this->load->model('dynamic_model');
        
		$this->load->model('api_model');
		$this->lang->load("message","english");

        $fetch_class = $this->router->fetch_class();
        $fetch_method = $this->router->fetch_method();

        $post = file_get_contents('php://input');
        $post = json_decode($post, TRUE);
        $mt = array('message' => serialize($post),
            'fetch_class' => $fetch_class,
            'fetch_method' => $fetch_method,
        );
        $this->dynamic_model->insertdata('json_save', $mt);
	}

    function test_get(){
        echo date('Y-m-d h:i:s A');
    }
    public function sms_check_get() {
        $uniquemobile = '9834';
        $country_code = '+91';
        $mobile = '9806112711';

        $message = "Your ".SITE_NAME." one time verificatiob code is ".$uniquemobile;
        $smsarray = array('phone'=>$country_code.$mobile,'message'=>$message);
        $st = send_sms($smsarray);
        print_r($st);
    }

    public function mail_check_get() {
        $newuserid = '225';
        $time = time();
        $where = array('id' => $newuserid);
        $findresult = $this->dynamic_model->getdatafromtable('user', $where);
        $name= ucwords($findresult[0]['name']);
        $email= $findresult[0]['email'];
        
        //Send Email Code
        $enc_user = encode($newuserid);
        $enc_role = encode($time);
        $url = site_url().'webservices/api/verify_user?encid='.$enc_user.'&batch='.$enc_role;
        $link='<a href="'.$url.'"> Click here </a>';

        $where1 = array('slug' => 'sucessfully_registration');
        $template_data = $this->dynamic_model->getdatafromtable('manage_notification_mail', $where1);
        $desc= str_replace('{USERNAME}',$name,$template_data[0]['description']);
        $desc_data= str_replace('{URL}',$link, $desc);
        $desc_send= str_replace('{SITE_TITLE}',SITE_TITLE, $desc_data);
        $subject = str_replace('{SITE_TITLE}',SITE_TITLE, $template_data[0]['subject']);
        $emailsubject = 'Thank you for registering with '.SITE_TITLE;
        $data['subject']     = $subject;
        $data['description'] = $desc_send;
        $data['body'] = "";
        $msg = $this->load->view('emailtemplate', $data, true);
       // print_r($email);die;
        $st =sendEmailCI("$email", SITE_TITLE ,$emailsubject, $msg);
        print_r($st);
    }

    public function checkout_class_get() {
        $arg = array(); 
        $query = 'SELECT GROUP_CONCAT(a.id) as u_id,s.to_time FROM user_attendance as a JOIN class_scheduling_time as s on (s.id = a.schedule_id) WHERE s.scheduled_date = date(now()) AND a.class_end_status = 0 ';
        $info = $this->dynamic_model->getQueryRowArray($query);
        $time = time();
        if (!empty($info)) {
            $ids = $info['u_id'];
            $to_time = $info['to_time'];
            if ($ids != null) {
                if($to_time < $time){
                $update = 'update user_attendance set class_end_status = "1" WHERE id IN ('.$ids.')';
                $this->db->query($update);
                }
            }
        }
        echo json_encode($arg);
    }

    /*
    0 - not open
    1 - open
    2 - close
    */
    public function class_schedule_change_get() {
        echo date('Y-m-d h:i:s A');
        echo '<br/>';
        $arg = array(); 
        $date = date('Y-m-d');
        echo $query = "SELECT * FROM class_scheduling_time as c WHERE c.scheduled_date = '".$date."' AND c.class_end_status != '2'";
       // echo $query = "SELECT * FROM class_scheduling_time as c WHERE c.scheduled_date = '".$date."' AND c.id = '184'";
        $variable = $this->dynamic_model->getQueryResultArray($query);
        $time = time();
        if (!empty($variable)) {
            foreach ($variable as $key => $info) {
                $ids = $info['id'];
                $class_end_status = $info['class_end_status'];
                $from_time = $info['from_time'];
                $to_time = $info['to_time'];
                $scheduled_date = $info['scheduled_date'];
                $time = time();
            echo '<br/>';
               echo $from_time.'--'. $st = date('h:i:s A',$from_time);
                $rt = $scheduled_date . ' ' . $st;
               echo  $from_time_utc = strtotime($rt);
            echo '<br/>';
                echo $to_time.'--'.$st = date('h:i:s A',$to_time);
                $rt = $scheduled_date . ' ' . $st;
                $to_time_utc = strtotime($rt);
            echo '<br/>';
                //echo $st = date('h:i:s A',$time);
           
                if($time >= $from_time && $time <= $to_time){
                   echo  $update ="update class_scheduling_time set class_end_status = '1' WHERE id = '".$ids."'";
                    $this->db->query($update);
                }

                if($to_time < $time){
                    echo '----'.$update ="update class_scheduling_time set class_end_status = '2' WHERE id = '".$ids."'";
                    $this->db->query($update);
                }
            } 
        }
        echo json_encode($arg);
    }

    // user pass deactive
    public function user_pass_deactive_get() {
        $arg = array(); 
        $time = time();
        $query = "SELECT * FROM user_booking as b WHERE b.service_type = '1' AND b.passes_status = '1' AND b.passes_end_date < '".$time."'";
        $variable = $this->dynamic_model->getQueryResultArray($query);
        if (!empty($variable)) {
            foreach ($variable as $key => $value) {
                $id = $value['id'];
                $update = "update user_booking set passes_status = '0' WHERE id = '".$id."'";
                $this->db->query($update);
            }
        }


        $query = "SELECT * FROM user_booking as b WHERE b.service_type = '1' AND b.passes_status = '1' AND b.passes_remaining_count = '0'";
        $variable = $this->dynamic_model->getQueryResultArray($query);
        if (!empty($variable)) {
            foreach ($variable as $key => $value) {
                $id = $value['id'];
                $update = "update user_booking set passes_status = '0' WHERE id = '".$id."'";
                $this->db->query($update);
            }
        }

        echo json_encode($arg);
    }
	
    function get_timeframe_pass_get(){
        $query = "SELECT b.* FROM user_booking as b join business_passes as p on b.service_id = p.id where b.service_type = '1' AND b.passes_status = '1' AND b.passes_remaining_count != '0' AND (p.pass_type = '10' || p.pass_type = '37' )";
         $variable = $this->dynamic_model->getQueryResultArray($query);
        if (!empty($variable)) {
            foreach ($variable as $key => $value) {
                $id = $value['id'];
                $passes_remaining_count = $value['passes_remaining_count'];
                if($passes_remaining_count > 0){
                    $passes_remaining_count = $passes_remaining_count -1;
                    $update = "update user_booking set passes_remaining_count = '".$passes_remaining_count."' WHERE id = '".$id."'";
                    $this->db->query($update);
                }
            }
        }
        return true;
    }


    function update_time_get(){
        $query = "SELECT * FROM business_shift_scheduling where u_status = '0' limit 1000";
         $variable = $this->dynamic_model->getQueryResultArray($query);
        if (!empty($variable)) {
            foreach ($variable as $key => $value) {
                $id = $value['id'];
                $from_time = $value['start_time'];
                $to_time = $value['end_time'];
                $current_date = $value['shift_date_str'];

                $st = date('h:i:s A',$from_time);
                $rt = $current_date . ' ' . $st;
                $new_from_time = strtotime($rt);

                $st = date('h:i:s A',$to_time);
                $rt = $current_date . ' ' . $st;
                $new_to_time = strtotime($rt);

                $update = "update business_shift_scheduling set start_time = '".$new_from_time."', end_time = '".$new_to_time."', u_status = '1' WHERE id = '".$id."'";
                $this->db->query($update);
            }
        }
        return true;
    }

   public function user_img_get() {
        $arg = array(); 
        $query = "SELECT * FROM user WHERE profile_img = 'userdefault.png'";
        $info = $this->dynamic_model->getQueryResultArray($query);
        $time = time();
        if (!empty($info)) {
            foreach ($info as $key => $value) {
                 $ids = $value['id']; 
                 $name = $value['name'];
                 $email = $value['email'];
                 $name = $name ? $name :$email;
           $name = strtolower(substr($name,0,1));
            if (!empty($name)) {
                $name = $name.'.png';
                echo $update = "update user set profile_img = '".$name."'  WHERE id = '".$ids."'";
                $this->db->query($update);
            }
            }
          
        }
        echo json_encode($arg);
    }

}
