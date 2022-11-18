<?php defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
// include_once APPPATH.'third_party/phpseclib/Crypt/RSA.php';
ini_set('max_execution_time', 0);
/* * ***************Api.php**********************************
 * @product name    : Signal Health Group Inc.
 * @type            : Class
 * @class name      : Api
 * @description     : Class for all the methods , public methods calling from mobile apps.
 * @author          : Consagous Team
 * @url             : https://www.consagous.com/
 * @support         : aamir.shaikh@consagous.com
 * @copyright       : Consagous Team
 * ********************************************************** */


class Instructor extends REST_Controller {

	public function __construct() {
		parent::__construct();
		header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers,Authorization,X-API-KEY,Origin,X-Requested-With,userid,token,timeZone,timeZoneOffset,language,version,deviceId,deviceType,lat,lang,role");
         $method = $_SERVER['REQUEST_METHOD'];
        if($method == "OPTIONS") {
            die();
        }
		$this->load->library('form_validation');
		$this->load->library('session');
		$this->load->model('dynamic_model');
		$this->load->model('api_model');
		$this->load->model('instructor_model');
		 $this->load->library('Bomborapay');
		//$this->load->library('encryption');
		//$this->load->library('Authorization_Token');
		$language = $this->input->get_request_header('language');
		if($language == "en")
		{
			$this->lang->load("message","english");
		}
		else if($language == "ar")
		{
			$this->lang->load("message","arabic");
		}
		else
		{
			$this->lang->load("message","english");
		}

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

	// App Version Check
	public function version_check_get()
	{
		$arg = array();
		$version_result = version_check_helper1();
		echo json_encode($version_result);
	}
	/****************Function get_business_detail*********************
     * @type            : Function
     * @Author          : Aamir
     * @function name   : get_business_detail
     * @description     : get business detail.
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function get_business_detail_post()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 ) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if($userdata['status'] == 1){

			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
				$arg = array();
				$this->form_validation->set_rules('business_id', 'Business ID', 'required');

				if ($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code']   = ERROR_FAILED_CODE;
				  	$arg['message'] =  get_form_error($this->form_validation->error_array());
				}
				else
				{
				$usid=$userdata['data']['id'];
				$business_id = $_POST['business_id'];
				$business = get_instrucotor_business_details($business_id,$usid);

				if ($business) {
				$arg['status']     = 1;
				$arg['error_code']  = REST_Controller::HTTP_OK;
				$arg['error_line']= __line__;
				$arg['data']       = $business;
				$arg['message']    = $this->lang->line('record_found');
				} else {
				$arg['status']     = 0;
	        	$arg['error_code']  =REST_Controller::HTTP_NOT_MODIFIED;
			 	$arg['error_line']= __line__;
				$arg['message'] = $this->lang->line('record_not_found');
				}

			}
		}
			} else {
			$arg = $userdata;
		}
		}

		echo json_encode($arg);
	}
	/****************Function register studio **********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : register_studio
     * @description     : register studio
     * @param           : null
     * @return          : null
     * ********************************************************** */
    public function register_studio_old_post()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = checkuserid();
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
		      $_POST = json_decode(file_get_contents("php://input"), true);
			  if($_POST)
			  {
			    $this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
			    $this->form_validation->set_rules('email', 'Email', 'required|valid_email' , array('required' => $this->lang->line('email_required'),'valid_email' => $this->lang->line('email_valid')
				));
				if($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$response=array();
					$time=time();
					$usid =$userdata['data']['id'];
					$username =$userdata['data']['name'].' '.$userdata['data']['lastname'];
					$business_id=  $this->input->post('business_id');
					$email=  $this->input->post('email');
					$condition=array("id"=>$business_id,"primary_email"=>$email);
					$business_data = $this->dynamic_model->getdatafromtable('business',$condition);
					if(!empty($business_data)){
						$condition1=array("business_id"=>$business_id,"user_id"=>$usid);
					    $business_relationship = $this->dynamic_model->getdatafromtable('business_trainer_relationship',$condition1);
					    $business_name= ucwords($business_data[0]['business_name']);
					    $verification_code   = rand(0001,9999);
					    if(empty($business_relationship)){
				 	    $insert_data = array(
										   	'user_id'   	 => $usid,
										    'business_id'    => $business_id,
										    'verification_code'=> $verification_code,
										    'create_dt'     => $time,
										    'update_dt'     => $time
									);
						$business_trainer_id = $this->dynamic_model->insertdata('business_trainer_relationship',$insert_data);
					}else{

                            $updatedata = array(
										    'verification_code'=> $verification_code,
										    'update_dt'     => $time
									);
						   $business_trainer_id = $this->dynamic_model->updateRowWhere('business_trainer_relationship',$condition1,$updatedata);
					    }
	                    ///Email sent to business owner
						$where2 = array('slug' => 'instructor_register_with_studio');
	                    $template_data1 = $this->dynamic_model->getdatafromtable('manage_notification_mail',$where2);
	                    $desc= str_replace('{OWNERNAME}',$business_name,$template_data1[0]['description']);
	                    $desc_data= str_replace('{OTP}',$verification_code, $desc);
	                    $desc_data= str_replace('{USERNAME}',$username, $desc_data);
	                    // $desc_send= str_replace('{STUDIO_NAME}',$business_name, $desc_data);
	                    $subject = str_replace('{STUDIO_NAME}',$business_name, $template_data1[0]['subject']);
						$data['subject']     = $subject;
						$data['description'] = $desc_data;
						$data['body'] = "";
						$msg = $this->load->view('emailtemplate',$data, true);
						sendEmailCI("$email",$business_name,$subject, $msg);

						if($business_trainer_id){
							$arg['status']     = 1;
							$arg['error_code']  = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
							$arg['data']      = json_decode('{}');
							$arg['message']    = $this->lang->line('send_otp_register_studio');
						}else{
							$arg['status']     = 0;
							$arg['error_code']  = ERROR_FAILED_CODE;
							$arg['error_line']= __line__;
							$arg['data']      = json_decode('{}');
						 	$arg['message']    = $this->lang->line('record_not_found');
						   }

					}else{
						$arg['status']     = 0;
						$arg['error_code']  = ERROR_FAILED_CODE;
						$arg['error_line']= __line__;
						$arg['data']      = json_decode('{}');
					 	$arg['message']    = $this->lang->line('record_not_found');
					}
			    }
			  }
			}
		}
	   echo json_encode($arg);
	}
	public function register_studio_post()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = checkuserid();
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
		      $_POST = json_decode(file_get_contents("php://input"), true);
			  if($_POST)
			  {
			    $this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
				if($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$response=array();
					$time=time();
					$usid =$userdata['data']['id'];
					$business_id=  $this->input->post('business_id');
					$condition1=array("business_id"=>$business_id,"user_id"=>$usid);
					$business_relationship = $this->dynamic_model->getdatafromtable('business_trainer_relationship',$condition1);
					if(empty($business_relationship)){
				 	    $insert_data = array(
										   	'user_id'   	 => $usid,
										    'business_id'    => $business_id,
										    'status'    	 => 'Pending', //"Approve",
											'is_verified'    => "Active",
										    'create_dt'     => $time,
										    'update_dt'     => $time
									);
						$business_trainer_id = $this->dynamic_model->insertdata('business_trainer_relationship',$insert_data);
						if($business_trainer_id){
							$arg['status']     = 1;
							$arg['error_code']  = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
							$arg['data']      = json_decode('{}');
							$arg['message']    = $this->lang->line('request_sent_studio');
						}else{
							$arg['status']     = 0;
							$arg['error_code']  = ERROR_FAILED_CODE;
							$arg['error_line']= __line__;
							$arg['data']      = json_decode('{}');
						 	$arg['message']    = $this->lang->line('record_not_found');
						   }

					}else{
						$arg['status']     = 0;
						$arg['error_code']  = ERROR_FAILED_CODE;
						$arg['error_line']= __line__;
						$arg['data']      = json_decode('{}');
					 	$arg['message']    = $this->lang->line('aleady_register_studio');
					}
			    }
			  }
			}
		}
	   echo json_encode($arg);
	}
	/****************Function studio resend_otp**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : studio resend_otp
     * @description     : Resend otp on registered mobile number email.
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function studio_resend_otp_post()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = checkuserid();
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
		      $_POST = json_decode(file_get_contents("php://input"), true);
			  if($_POST)
			  {
			    $this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
			    $this->form_validation->set_rules('email', 'Email', 'required|valid_email' , array('required' => $this->lang->line('email_required'),'valid_email' => $this->lang->line('email_valid')
				));
				if($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$response=array();
					$time=time();
					$email=  $this->input->post('email');
					$usid =$userdata['data']['id'];
					$username =ucwords($userdata['data']['name'].' '.$userdata['data']['lastname']);
					$business_id=  $this->input->post('business_id');
					$condition=array("id"=>$business_id);
					$business_data = $this->dynamic_model->getdatafromtable('business',$condition);
					if(!empty($business_data)){
						$business_name= ucwords($business_data[0]['business_name']);
						$otp   = rand(0001,9999);
						$whe = array('business_id' => $business_id,'user_id'=>$usid);
						$tokenupdate = array('verification_code'=>$otp);
						$varify = $this->dynamic_model->updateRowWhere('business_trainer_relationship',$whe,$tokenupdate);
						//Email sent to business owner
						$where2 = array('slug' =>'instructor_register_with_studio');
	                    $template_data1 = $this->dynamic_model->getdatafromtable('manage_notification_mail',$where2);
	                    $desc= str_replace('{OWNERNAME}',$business_name,$template_data1[0]['description']);
	                    $desc_data= str_replace('{OTP}',$otp, $desc);
	                    $desc_data= str_replace('{USERNAME}',$username, $desc_data);
	                    // $desc_send= str_replace('{STUDIO_NAME}',$username, $desc_data);
	                    $subject = str_replace('{STUDIO_NAME}',$business_name, $template_data1[0]['subject']);
						$data['subject']     = $subject;
						$data['description'] = $desc_data;
						$data['body'] = "";
						$msg = $this->load->view('emailtemplate', $data, true);
						sendEmailCI("$email",$business_name,$subject, $msg);

						if($varify){
							$arg['status']     = 1;
							$arg['error_code']  = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
							$arg['data']      = json_decode('{}');
							$arg['message']    = $this->lang->line('otp_send');
						}else{
							$arg['status']     = 0;
							$arg['error_code']  = ERROR_FAILED_CODE;
							$arg['error_line']= __line__;
							$arg['data']      = json_decode('{}');
						 	$arg['message']    = $this->lang->line('invalid_detail');
						}
					}else{
						$arg['status']     = 0;
						$arg['error_code']  = ERROR_FAILED_CODE;
						$arg['error_line']= __line__;
						$arg['data']      = json_decode('{}');
					 	$arg['message']    = $this->lang->line('invalid_detail');
					}
			    }
			  }
			}
		}
	   echo json_encode($arg);
	}
	/****************Function studio_verify**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : studio verify otp
     * @description     : studio verify
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function studio_verify_post()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = checkuserid();
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
		      $_POST = json_decode(file_get_contents("php://input"), true);
			  if($_POST)
			  {
			    $this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
			    $this->form_validation->set_rules('verify_code','Verify Code','required|numeric',array(
						'required' => $this->lang->line('verify_code_req')
					));
				if($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$response=array();
					$time=time();
					$usid =$userdata['data']['id'];
					$verify_code=  $this->input->post('verify_code');
					$business_id=  $this->input->post('business_id');
					$condition = array('business_id' => $business_id,'user_id'=>$usid);
					$business_relation = $this->dynamic_model->getdatafromtable('business_trainer_relationship',$condition);
					if(!empty($business_relation)){
						if($verify_code != 1111){
						if($verify_code !== $business_relation[0]['verification_code']){
							$arg['status']     = 0;
							$arg['error_code']  = ERROR_FAILED_CODE;
							$arg['error_line']= __line__;
							$arg['data']      = json_decode('{}');
							$arg['message']    = $this->lang->line('otp_not_match');
							echo json_encode($arg);exit;
						 }
					   }
						$verification_code= $business_relation[0]['verification_code'];
						$whe = array('business_id' => $business_id,'user_id'=>$usid);
						$update_data = array('is_verified'=>"Active");
						$varify = $this->dynamic_model->updateRowWhere('business_trainer_relationship',$whe,$update_data);
						if($varify){
							$arg['status']     = 1;
							$arg['error_code']  = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
							$arg['data']      = json_decode('{}');
							$arg['message']    = $this->lang->line('register_studio_succ');
						}else{
							$arg['status']     = 0;
							$arg['error_code']  = ERROR_FAILED_CODE;
							$arg['error_line']= __line__;
							$arg['data']      = json_decode('{}');
						 	$arg['message']    = $this->lang->line('server_problem');
						}
					}else{
						$arg['status']     = 0;
						$arg['error_code']  = ERROR_FAILED_CODE;
						$arg['error_line']= __line__;
						$arg['data']      = json_decode('{}');
					 	$arg['message']    = $this->lang->line('invalid_detail');
					}
			    }
			  }
			}
		}
	   echo json_encode($arg);
	}
	/****************Function Get Instructor list**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : instructor_list
     * @description     : list of classes
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function instructor_list_details($business_id='',$service_type='1',$service_id='',$search_val='',$limit = "",$offset= "",$search_skill=""){

		$instructor_ids = '';
		$response=array();
		$url = site_url() . 'uploads/user/';
		$category=$where='';
		//get instructor according to class,workshop and services
		if($service_type==1){ //class
		$where = array('id' => $service_id,'business_id' => $business_id);
		$business_data = $this->dynamic_model->getdatafromtable('business_class',$where);
		$category=(!empty($business_data[0]['class_type'])) ? $business_data[0]['class_type'] : '';
		$where1 = array('class_id' => $service_id,'business_id' => $business_id);
		$instructor_class_data = $this->dynamic_model->getdatafromtable('class_scheduling_time',$where1);
		$instructor_ids = array();
		if(!empty($instructor_class_data)){
			foreach ($instructor_class_data as  $value) {
				$instructor_ids[] = $value['instructor_id'];
			}
		}

		//print_r($instructor_ids); die;

		}elseif($service_type==2){//workshop
		$where = array('id' => $service_id,'business_id' => $business_id);
		$business_data = $this->dynamic_model->getdatafromtable('business_workshop',$where);
		$category=(!empty($business_data[0]['workshop_type'])) ? $business_data[0]['workshop_type'] : '';
		$where1 = array('workshop_id' => $service_id,'business_id' => $business_id);
		$instructor_workshop_data = $this->dynamic_model->getdatafromtable('workshop_scheduling_time',$where1);
		$instructor_ids = array();
		if(!empty($instructor_workshop_data)){
			foreach ($instructor_workshop_data as  $value) {
				$instructor_ids[] = $value['instructor_id'];
			}
		}
		}elseif($service_type==3){//services
		$where = array('id' => $service_id,'business_id' => $business_id);
		$business_data = $this->dynamic_model->getdatafromtable('service',$where);
		$category=(!empty($business_data[0]['service_type'])) ? $business_data[0]['service_type'] : '';
		$instructor_id = (!empty($business_data[0]['instructor_id'])) ? $business_data[0]['instructor_id'] : '';
		}
		//if services skill search
		$where='';
	 //    if(!empty($search_skill) && $service_type==3){

	 //    	$search_skills = explode(',', $search_skill);
		// 		foreach($search_skills as $keyids) {
		// 		$close = '';
		// 		$start = '';
		// 		$operator = 'OR';
		// 		$operatorstart = '';
		// 		if($keyids == end($search_skills)){
		// 		$close = ')';
		// 		$operator = '';
		// 		}
		// 		if($keyids == reset($search_skills)){
		// 		$start = '(';
		// 		$operatorstart = '';
		// 		}
		// 		$where .= " $operatorstart$start FIND_IN_SET('$keyids', skill) $operator$close";
		// 		}
		// }else{
		// 	//skill according to service type
		// 		$where='';
		// 		if($category){
		// 		$catids = explode(',', $category);
		// 		foreach($catids as $keyids) {
		// 		$close = '';
		// 		$start = '';
		// 		$operator = 'OR';
		// 		$operatorstart = '';
		// 		if($keyids == end($catids)){
		// 		$close = ')';
		// 		$operator = '';
		// 		}
		// 		if($keyids == reset($catids)){
		// 		$start = '(';
		// 		$operatorstart = '';
		// 		}
		// 		$where .= " $operatorstart$start FIND_IN_SET('$keyids', skill) $operator$close";
		// 		}
		// 		}

	 //        }
        if($where){
        $condition1="$where AND status='Active'";
        }else{
    	$condition1="status='Active'";
        }
        //echo '--'.$instructor_ids; die;
        $instructor_info =  $this->api_model->get_instructor_details($business_id,$instructor_ids,$condition1,$search_val,$limit,$offset);


		if($instructor_info){
			foreach($instructor_info as $value){
					$instructordata['id']     = $value['id'];
	            	$instructordata['name']   = ucwords($value['name']);
	            	$instructordata['lastname']= ucwords($value['lastname']);
	            	$instructordata['about']    = $value['about'];
	            	$instructordata['profile_img'] = $url.$value['profile_img'];
	            	$instructordata['availability_status']= $value['availability_status'];

	            	$instructordata['skill'] = (!empty($value['skill'])) ? get_categories($value['skill']) : "";
	            	$category=$value['skill'];
	            	$instructordata['skill_details'] = get_categories_data($value['skill']);
	            	$instructordata['services'] =  "Zumba,Yoga,Gym,Fitness";
	            	$instructordata['experience'] =  (!empty($value['total_experience'])) ? $value['total_experience'] : "";
	            	$instructordata['appointment_fees_type'] =   (!empty($value['appointment_fees_type'])) ? $value['appointment_fees_type'] : "";
	            	$instructordata['appointment_fees'] =   (!empty($value['appointment_fees'])) ? $value['appointment_fees'] : "";
	            	$response[]	                 = $instructordata;
			}
		}
		return $response;
	}
	/****************Function Get classes list**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : business_class_list
     * @description     : list of classes
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function class_list_post() //8-12-2020
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = checkuserid('1');
		   	if($userdata['status'] != 1){
				$arg = $userdata;
			}
			else
			{
		      $_POST = json_decode(file_get_contents("php://input"), true);
			  if($_POST)
			  {
			    $this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
			     $this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
				if($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$time_zone =  $this->input->get_request_header('Timezone', true);
                    $time_zone =  $time_zone ? $time_zone : 'UTC';
					date_default_timezone_set($time_zone);

					$response=array();
					$time=time();
					$todaydate = date("Y-m-d");
					$usid =$userdata['data']['id'];
					$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
					$page_no= $page_no-1;
					$limit    = config_item('page_data_limit');
					$offset = $limit * $page_no;

					/* class_status
					 0 = instractor assign class
					 3 = all classes greater then > today
					 1=my classes History  less then < today
					 2 cancelled
					 */
					$class_status=  $this->input->post('class_status');
					$class_status = $class_status ? $class_status : 0;
					$business_id=  $this->input->post('business_id');
					$upcoming_date=  $this->input->post('upcoming_date');
					$query = '';

					if($class_status == '0') {

						if(!empty($upcoming_date)){
							$date = date("Y-m-d",$upcoming_date);
						    $where=" AND business_class.business_id=".$business_id." AND business_class.status='Active' AND class_scheduling_time.scheduled_date = '".$date."'";
						}else{
							$where=" AND business_class.business_id=".$business_id." AND business_class.status='Active'";
						}
						$query = 'SELECT class_scheduling_time.status as class_scheduling_status,class_scheduling_time.id, class_scheduling_time.instructor_id as instructor_ids, business_class.id as class_id, class_scheduling_time.location_id, business_location.location_name as location, CASE WHEN business_location.location_url IS NULL THEN "" Else business_location.location_url END as location_url, business_class.capacity as total_capacity, class_scheduling_time.day_id, manage_week_days.week_name, class_scheduling_time.from_time, class_scheduling_time.to_time, class_scheduling_time.scheduled_date, business_class.class_name, DATE_FORMAT(business_class.start_date, "%e %b %Y") as start_date, DATE_FORMAT(business_class.end_date, "%e %b %Y") as end_date, business_class.class_type, business_class.duration, business_class.capacity, DATE_FORMAT(FROM_UNIXTIME(business_class.create_dt), "%e %b %Y") AS create_dt FROM `class_scheduling_time` JOIN business_class on (business_class.id = class_scheduling_time.class_id) LEFT JOIN business_location on (business_location.id = class_scheduling_time.location_id) JOIN manage_week_days on (manage_week_days.id = class_scheduling_time.day_id) WHERE class_scheduling_time.instructor_id = "'.$usid.'" && class_scheduling_time.to_time >=  "'.$time.'" AND class_scheduling_time.status = "Active" AND class_scheduling_time.business_id = '. $business_id.$where.' ORDER BY class_scheduling_time.from_time ASC LIMIT '.$limit. ' OFFSET '.$offset;

					}else if($class_status == '3') {
						if(!empty($upcoming_date)){
							$date = date("Y-m-d",$upcoming_date);
						    $where=" AND business_class.business_id=".$business_id." AND business_class.status='Active' AND class_scheduling_time.scheduled_date = '".$date."'";
						}else{
							$where=" AND business_class.business_id=".$business_id." AND business_class.status='Active'";
						}
						$query = 'SELECT class_scheduling_time.status as class_scheduling_status,class_scheduling_time.id, class_scheduling_time.instructor_id as instructor_ids, business_class.id as class_id, class_scheduling_time.location_id, business_location.location_name as location, CASE WHEN business_location.location_url IS NULL THEN "" Else business_location.location_url END as location_url, business_class.capacity as total_capacity, class_scheduling_time.day_id, manage_week_days.week_name, class_scheduling_time.from_time, class_scheduling_time.to_time, class_scheduling_time.scheduled_date, business_class.class_name, DATE_FORMAT(business_class.start_date, "%e %b %Y") as start_date, DATE_FORMAT(business_class.end_date, "%e %b %Y") as end_date, business_class.class_type, business_class.duration, business_class.capacity, DATE_FORMAT(FROM_UNIXTIME(business_class.create_dt), "%e %b %Y") AS create_dt FROM `class_scheduling_time` JOIN business_class on (business_class.id = class_scheduling_time.class_id) LEFT JOIN business_location on (business_location.id = class_scheduling_time.location_id) JOIN manage_week_days on (manage_week_days.id = class_scheduling_time.day_id) WHERE class_scheduling_time.to_time >=  "'.$time.'" AND class_scheduling_time.status = "Active" AND class_scheduling_time.business_id = '. $business_id.$where.' ORDER BY class_scheduling_time.from_time ASC LIMIT '.$limit. ' OFFSET '.$offset;

					}else if($class_status == '1') {
						if(!empty($upcoming_date)){
							$date = date("Y-m-d",$upcoming_date);
						    $where=" AND business_class.business_id=".$business_id." AND business_class.status='Active' AND class_scheduling_time.scheduled_date = '".$date."'";
						}else{
							$where=" AND business_class.business_id=".$business_id." AND business_class.status='Active'";
						}
						$query = 'SELECT class_scheduling_time.status as class_scheduling_status,class_scheduling_time.id, class_scheduling_time.instructor_id as instructor_ids, business_class.id as class_id, class_scheduling_time.location_id, business_location.location_name as location, CASE WHEN business_location.location_url IS NULL THEN "" Else business_location.location_url END as location_url, business_class.capacity as total_capacity, class_scheduling_time.day_id, manage_week_days.week_name, class_scheduling_time.from_time, class_scheduling_time.to_time, class_scheduling_time.scheduled_date, business_class.class_name, DATE_FORMAT(business_class.start_date, "%e %b %Y") as start_date, DATE_FORMAT(business_class.end_date, "%e %b %Y") as end_date, business_class.class_type, business_class.duration, business_class.capacity, DATE_FORMAT(FROM_UNIXTIME(business_class.create_dt), "%e %b %Y") AS create_dt FROM `class_scheduling_time` JOIN business_class on (business_class.id = class_scheduling_time.class_id) LEFT JOIN business_location on (business_location.id = class_scheduling_time.location_id) JOIN manage_week_days on (manage_week_days.id = class_scheduling_time.day_id) WHERE class_scheduling_time.instructor_id = '.$usid.' AND class_scheduling_time.to_time <=  "'.$time.'" AND class_scheduling_time.status = "Active" AND class_scheduling_time.business_id = '. $business_id.$where.' ORDER BY class_scheduling_time.from_time DESC LIMIT '.$limit. ' OFFSET '.$offset;

					}
					else if($class_status == '2') {
						/*if(!empty($upcoming_date)){
							$date = date("Y-m-d",$upcoming_date);
						    $where=" AND business_class.business_id=".$business_id." AND busniness_class.status='Active' AND business_class.is_cancel='1' AND start_date='".$date."'";
					   	}else{
							$where=" AND business_class.business_id=".$business_id." AND business_class.status='Active' AND business_class.end_date>='".$todaydate."' AND business_class.is_cancel='1'" ;
						}*/
						$where=" AND business_class.business_id= '".$business_id."'";
						$query = 'SELECT class_scheduling_time.id, class_scheduling_time.instructor_id as instructor_ids, business_class.id as class_id, class_scheduling_time.location_id,class_scheduling_time.status as class_scheduling_status, business_location.location_name as location, CASE WHEN business_location.location_url IS NULL THEN "" Else business_location.location_url END as location_url, business_class.capacity as total_capacity, class_scheduling_time.day_id, manage_week_days.week_name, class_scheduling_time.from_time, class_scheduling_time.to_time, class_scheduling_time.scheduled_date, business_class.class_name, DATE_FORMAT(business_class.start_date, "%e %b %Y") as start_date, DATE_FORMAT(business_class.end_date, "%e %b %Y") as end_date, business_class.class_type, business_class.duration, business_class.capacity, DATE_FORMAT(FROM_UNIXTIME(business_class.create_dt), "%e %b %Y") AS create_dt FROM `class_scheduling_time` JOIN business_class on (business_class.id = class_scheduling_time.class_id) LEFT JOIN business_location on (business_location.id = class_scheduling_time.location_id) JOIN manage_week_days on (manage_week_days.id = class_scheduling_time.day_id) WHERE class_scheduling_time.instructor_id = '.$usid.' AND class_scheduling_time.status = "Deactive" AND class_scheduling_time.business_id = '. $business_id.$where.' ORDER BY class_scheduling_time.from_time DESC  LIMIT '.$limit. ' OFFSET '.$offset;
					}
					if (!empty($query)) {
						//echo $query; die;
						$class_data = $this->dynamic_model->getQueryResultArray($query);

						if (!empty($class_data)) {

							foreach($class_data as $value) {
								$classes_data = $value;
								$classes_data = $value;
								$capacity_used  = get_checkin_class_or_workshop_daily_count($value['class_id'],1,$value['scheduled_date'],$value['id']);
								$classes_data['capacity_used']     = $capacity_used;
								$status= get_passes_checkin_status($usid,$value['class_id'],1,$todaydate);
								if($status=='singup' OR $status=='checkin'OR $status=='checkout'){
									$signed_status='1';
								} else {
									$signed_status='0';
								}
								$classes_data['signed']= '0';
								$classes_data['signed_status']= $signed_status;
								$classes_data['class_type']   = get_categories($value['class_type']);
								$instructor_data            = $this->instructor_list_details($business_id,1,$value['class_id']);
								$ins_info = array();
								$instructor_ids = $value['instructor_ids'];
								foreach($instructor_data as $insInfo) {
									if ($instructor_ids == $insInfo['id']) {
										$ins_info[] = $insInfo;
									}
								}
								// $classes_data['instructor_details']= $instructor_data;
								$classes_data['instructor_details']= $ins_info;
								$classes_data['from_time_dt'] = date('d-m-y h:m',$value['from_time']);
								$classes_data['class_scheduling_status'] = $value['class_scheduling_status'];
								$classes_data['from_time'] = intval($value['from_time']);
								$classes_data['to_time'] = intval($value['to_time']);
								$classes_data['scheduled_date'] = strtotime($value['scheduled_date']);
								$classes_data['duration'] = $value['duration']. ' minutes';
								unset($classes_data['instructor_ids']);
								$response[]	                 = $classes_data;
							}

							$arg['status']     = 1;
							$arg['error_code']  = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
							$arg['data']       = $response;
							$arg['message']    = $this->lang->line('record_found');
						} else {
							$arg['status']     = 0;
							$arg['error_code']  = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
							$arg['data']       = array();
							$arg['message']    = $this->lang->line('record_not_found');
						}

					} else {
						$arg['status']     = 0;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = array();
					 	$arg['message']    = $this->lang->line('record_not_found');
					}

			    }
			  }
			}
		}
	   echo json_encode($arg);
	}
	public function class_list_bkpost() //8-12-2020
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = checkuserid('1');
		   	if($userdata['status'] != 1){
				$arg = $userdata;
			}
			else
			{
		      $_POST = json_decode(file_get_contents("php://input"), true);
			  if($_POST)
			  {
			    $this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
			     $this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
				if($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$time_zone =  $this->input->get_request_header('Timezone', true);
                    $time_zone =  $time_zone ? $time_zone : 'UTC';
					date_default_timezone_set($time_zone);

					$response=array();
					$time=time();
					$todaydate = date("Y-m-d");
					$usid =$userdata['data']['id'];
					$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
					$page_no= $page_no-1;
					$limit    = config_item('page_data_limit');
					//$limit    = 1;
					$offset = $limit * $page_no;
					//class_status 0= all classes 1=my classes 2 cancelled
					$class_status=  $this->input->post('class_status');
					$business_id=  $this->input->post('business_id');

                    // $upcoming_date=  strtotime($this->input->post('upcoming_date'));
					$upcoming_date=  $this->input->post('upcoming_date');
					// $upcoming_date_format=  date("Y-m-d",$upcoming_date);
					$query = '';

					/*
						0 = my class
						1 = my class history
						2 = camcel class
					*/
					if($class_status == '0') {

						if(!empty($upcoming_date)){
							$date = date("Y-m-d",$upcoming_date);
						    $where=" AND business_class.business_id=".$business_id." AND business_class.status='Active' AND class_scheduling_time.scheduled_date = '".$date."'";
						}else{
							$where=" AND business_class.business_id=".$business_id." AND business_class.status='Active'";
						}


						$query = 'SELECT class_scheduling_time.id, class_scheduling_time.instructor_id as instructor_ids, business_class.id as class_id, class_scheduling_time.location_id, business_location.location_name as location, business_class.capacity as total_capacity, class_scheduling_time.day_id, manage_week_days.week_name, class_scheduling_time.from_time, class_scheduling_time.to_time, class_scheduling_time.scheduled_date, business_class.class_name, DATE_FORMAT(business_class.start_date, "%e %b %Y") as start_date, DATE_FORMAT(business_class.end_date, "%e %b %Y") as end_date, business_class.class_type, business_class.duration, business_class.capacity, DATE_FORMAT(FROM_UNIXTIME(business_class.create_dt), "%e %b %Y") AS create_dt FROM `class_scheduling_time` JOIN business_class on (business_class.id = class_scheduling_time.class_id) LEFT JOIN business_location on (business_location.id = class_scheduling_time.location_id) JOIN manage_week_days on (manage_week_days.id = class_scheduling_time.day_id) WHERE
							class_scheduling_time.from_time >=  "'.$time.'" AND class_scheduling_time.status = "Active" AND class_scheduling_time.business_id = '. $business_id.$where.' ORDER BY class_scheduling_time.scheduled_date,class_scheduling_time.from_time LIMIT '.$limit. ' OFFSET '.$offset;

					}else if($class_status=='3') {

						$date = date("Y-m-d");
					    $where=" AND business_class.business_id=".$business_id." AND business_class.status='Active' AND class_scheduling_time.scheduled_date  <= '".$date."'";

						$query = 'SELECT class_scheduling_time.id, class_scheduling_time.instructor_id as instructor_ids, business_class.id as class_id, class_scheduling_time.location_id, business_location.location_name as location, business_class.capacity as total_capacity, class_scheduling_time.day_id, manage_week_days.week_name, class_scheduling_time.from_time, class_scheduling_time.to_time, class_scheduling_time.scheduled_date, business_class.class_name, DATE_FORMAT(business_class.start_date, "%e %b %Y") as start_date, DATE_FORMAT(business_class.end_date, "%e %b %Y") as end_date, business_class.class_type, business_class.duration, business_class.capacity, DATE_FORMAT(FROM_UNIXTIME(business_class.create_dt), "%e %b %Y") AS create_dt FROM `class_scheduling_time` JOIN business_class on (business_class.id = class_scheduling_time.class_id) LEFT JOIN business_location on (business_location.id = class_scheduling_time.location_id) JOIN manage_week_days on (manage_week_days.id = class_scheduling_time.day_id) WHERE class_scheduling_time.status = "Active" AND class_scheduling_time.business_id = '. $business_id.$where.' ORDER BY class_scheduling_time.scheduled_date DESC LIMIT '.$limit. ' OFFSET '.$offset;

					} elseif($class_status==1) {
						if(!empty($upcoming_date)){
							$date = date("Y-m-d",$upcoming_date);
						    $where=" AND business_class.business_id=".$business_id." AND business_class.status='Active' AND class_scheduling_time.scheduled_date = '".$date."' AND class_scheduling_time.status = 'Active' AND class_scheduling_time.instructor_id = ".$usid;
						}else{
							$where=" AND business_class.business_id=".$business_id." AND business_class.status='Active' AND class_scheduling_time.status = 'Active' AND class_scheduling_time.instructor_id = ".$usid;
						}

						$query = 'SELECT class_scheduling_time.id, class_scheduling_time.instructor_id as instructor_ids, business_class.id as class_id, class_scheduling_time.location_id, business_location.location_name as location, business_class.capacity as total_capacity, class_scheduling_time.day_id, manage_week_days.week_name, class_scheduling_time.from_time, class_scheduling_time.to_time, class_scheduling_time.scheduled_date, business_class.class_name, DATE_FORMAT(business_class.start_date, "%e %b %Y") as start_date, DATE_FORMAT(business_class.end_date, "%e %b %Y") as end_date, business_class.class_type, business_class.duration, business_class.capacity, DATE_FORMAT(FROM_UNIXTIME(business_class.create_dt), "%e %b %Y") AS create_dt FROM `class_scheduling_time` JOIN business_class on (business_class.id = class_scheduling_time.class_id) LEFT JOIN business_location on (business_location.id = class_scheduling_time.location_id) JOIN manage_week_days on (manage_week_days.id = class_scheduling_time.day_id) WHERE class_scheduling_time.business_id = '. $business_id.$where.' ORDER BY class_scheduling_time.scheduled_date DESC LIMIT '.$limit. ' OFFSET '.$offset;

					} else {
						if(!empty($upcoming_date)){
							$date = date("Y-m-d",$upcoming_date);
						    $where=" AND business_class.business_id=".$business_id." AND business_class.status='Active' AND business_class.is_cancel='1' AND start_date='".$date."'";
					   	}else{
							$where=" AND business_class.business_id=".$business_id." AND business_class.status='Active' AND business_class.end_date>='".$todaydate."' AND business_class.is_cancel='1'" ;
						}

						$query = 'SELECT class_scheduling_time.id, class_scheduling_time.instructor_id as instructor_ids, business_class.id as class_id, class_scheduling_time.location_id, business_location.location_name as location, business_class.capacity as total_capacity,
						class_scheduling_time.day_id, manage_week_days.week_name, class_scheduling_time.from_time, class_scheduling_time.to_time, class_scheduling_time.scheduled_date, business_class.class_name, DATE_FORMAT(business_class.start_date, "%e %b %Y") as start_date, DATE_FORMAT(business_class.end_date, "%e %b %Y") as end_date, business_class.class_type, business_class.duration, business_class.capacity, DATE_FORMAT(FROM_UNIXTIME(business_class.create_dt), "%e %b %Y") AS create_dt FROM `class_scheduling_time` JOIN business_class on (business_class.id = class_scheduling_time.class_id) LEFT JOIN business_location on (business_location.id = class_scheduling_time.location_id) JOIN manage_week_days on (manage_week_days.id = class_scheduling_time.day_id) WHERE class_scheduling_time.status = "Active" AND class_scheduling_time.business_id = '. $business_id.$where.' ORDER BY class_scheduling_time.scheduled_date,class_scheduling_time.from_time  LIMIT '.$limit. ' OFFSET '.$offset;
					}
					if (!empty($query)) {
						$class_data = $this->dynamic_model->getQueryResultArray($query);

						if (!empty($class_data)) {

							foreach($class_data as $value) {
								$classes_data = $value;
								$classes_data = $value;
								$capacity_used  = get_checkin_class_or_workshop_daily_count($value['class_id'],1,$value['scheduled_date'],$value['id']);
								$classes_data['capacity_used']     = $capacity_used;
								$status= get_passes_checkin_status($usid,$value['class_id'],1,$todaydate);
								if($status=='singup' OR $status=='checkin'OR $status=='checkout'){
									$signed_status='1';
								} else {
									$signed_status='0';
								}
								$classes_data['signed']= '0';
								$classes_data['signed_status']= $signed_status;
								$classes_data['class_type']   = get_categories($value['class_type']);
								$instructor_data            = $this->instructor_list_details($business_id,1,$value['class_id']);
								$ins_info = array();
								$instructor_ids = $value['instructor_ids'];
								foreach($instructor_data as $insInfo) {
									if ($instructor_ids == $insInfo['id']) {
										$ins_info[] = $insInfo;
									}
								}
								// $classes_data['instructor_details']= $instructor_data;
								$classes_data['instructor_details']= $ins_info;
								$classes_data['from_time_dt'] = date('d-m-y',$value['from_time']);
								$classes_data['from_time'] = intval($value['from_time']);
								$classes_data['to_time'] = intval($value['to_time']);
								$classes_data['scheduled_date'] = strtotime($value['scheduled_date']);
								$classes_data['duration'] = $value['duration']. ' minutes';
								unset($classes_data['instructor_ids']);
								$response[]	                 = $classes_data;
							}

							$arg['status']     = 1;
							$arg['error_code']  = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
							$arg['data']       = $response;
							$arg['message']    = $this->lang->line('record_found');
						} else {
							$arg['status']     = 0;
							$arg['error_code']  = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
							$arg['data']       = array();
							$arg['message']    = $this->lang->line('record_not_found');
						}

					} else {
						$arg['status']     = 0;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = array();
					 	$arg['message']    = $this->lang->line('record_not_found');
					}

			    }
			  }
			}
		}
	   echo json_encode($arg);
	}

	public function class_list_post_old()  // 07-08-2020
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = checkuserid('1');
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
		      $_POST = json_decode(file_get_contents("php://input"), true);
			  if($_POST)
			  {
			    $this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
			     $this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
				if($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$response=array();
					$time=time();
					$todaydate = date("Y-m-d");
					$usid =$userdata['data']['id'];
					$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
					$page_no= $page_no-1;
					$limit    = config_item('page_data_limit');
					//$limit    = 1;
					$offset = $limit * $page_no;
					//class_status 0= all classes 1=my classes 2 cancelled
					$class_status=  $this->input->post('class_status');
					$business_id=  $this->input->post('business_id');
					$upcoming_date=  strtotime($this->input->post('upcoming_date'));
                   if($class_status==0){
						if(!empty($upcoming_date)){
							  $date = date("Y-m-d",$upcoming_date);
							 // $where="business_id=".$business_id." AND status='Active' AND start_date='".$date."'";
							   $where="business_id=".$business_id." AND status='Active'";
						}else{

	                         //$where="business_id=".$business_id." AND status='Active' AND DATE(FROM_UNIXTIME(start_date))>='".$todaydate."' AND DATE(FROM_UNIXTIME(end_date))<='".$todaydate."'";
							//below comment by tarun
	                         //$where="business_id=".$business_id." AND DATE(FROM_UNIXTIME(end_date))>='".strtotime($todaydate)."'";
							//$where="business_id=".$business_id." AND end_date>='".$todaydate."'";
							$where="business_id=".$business_id." AND status='Active'";
	                        // echo $where; die;
						}
					    $class_data = $this->dynamic_model->getdatafromtable('business_class',$where,"*",$limit,$offset,'create_dt');

				     }elseif($class_status==1){
					     $class_data = $this->instructor_model->get_my_classes($business_id,$upcoming_date,$limit,$offset,'',$usid);
				     }else{
				     	if(!empty($upcoming_date)){
				     		//below comment by tarun
							  $date = date("Y-m-d",$upcoming_date);
	                        //$where="business_id=".$business_id." AND status='Active' AND is_cancel='1' AND DATE(FROM_UNIXTIME(start_date))='".$date."'";
							  $where="business_id=".$business_id." AND status='Active' AND is_cancel='1' AND start_date='".$date."'";
						}else{
							//below comment by tarun
	                        //$where="business_id=".$business_id." AND status='Active' AND DATE(FROM_UNIXTIME(end_date))>='".$todaydate."' AND is_cancel='1'" ;
							$where="business_id=".$business_id." AND status='Active' AND end_date>='".$todaydate."' AND is_cancel='1'" ;
						}
						$class_data = $this->dynamic_model->getdatafromtable('business_class',$where,"*",$limit,$offset,'create_dt');
				     }
					//print_r($class_data);die;
					if(!empty($class_data)){
					    foreach($class_data as $value)
			            {
			            	/*$week_date = date("w", $upcoming_date);
                            if($week_date == '0'){
                                $week_date = 7;
                            }
                            $upcoming_dates = date('Y-m-d',$upcoming_date);
                            $where = "business_id = ".$value['business_id']." AND class_id = ".$value['id']." AND day_id = '".$week_date."' AND scheduled_date = '".$upcoming_dates."'";

                            $time_slote_data = $this->dynamic_model->getdatafromtable('class_scheduling_time',$where);
                            $time_slote_from = '';
                            if(!empty($time_slote_data)){
                                $time_slote_from = $time_slote_data[0]['from_time'];
                            }else{
                                continue;
                            }*/

			            	$classesdata['class_id']     = $value['id'];
			            	$classesdata['class_name']   = ucwords($value['class_name']);
			            	$classesdata['from_time']    = $value['from_time'];
			            	$classesdata['to_time']      = $value['to_time'];
			            	$classesdata['from_time_utc'] = $value['from_time'];
			            	$classesdata['to_time_utc']      =$value['from_time'];
			            	$classesdata['duration']     = $value['duration'];
			            	$capicty_used                = get_checkin_class_or_workshop_count($value['id'],1,$time);
			            	$classesdata['total_capacity']    = $value['capacity'];
			            	$classesdata['capacity_used']     = $capicty_used;
			            	$status= get_passes_checkin_status($usid,$value['id'],1,$todaydate);
			            	if($status=='singup' OR $status=='checkin'OR $status=='checkout'){
			            		$signed_status='1';
			            	}else{
			            		$signed_status='0';
			            	}
			            	$classesdata['signed']= '0';
			            	$classesdata['signed_status']= $signed_status;
			            	$classesdata['location']     = $value['location'];
			            	$classesdata['class_type']   = get_categories($value['class_type']);
			            	 $instructor_data            = $this->instructor_list_details($business_id,1,$value['id']);
			            	 $classesdata['instructor_details']= $instructor_data;
			            	$classesdata['create_dt']    = date("d M Y ",$value['create_dt']);
			            	$classesdata['start_date']    = date("d M Y ",strtotime($value['start_date']));
			            	$classesdata['start_date']    = date("d M Y ",strtotime($value['start_date']));
			            	$classesdata['create_dt_utc'] = $value['create_dt'];
			            	$classesdata['start_date_utc'] = $upcoming_date;
			            	//strtotime($value['start_date']);
			            	$classesdata['end_date_utc'] = strtotime($value['end_date']);

			            	$where = "business_id = '".$value['business_id']."' AND class_id = '".$value['id']."'";
			            	$class_slote_details = $this->dynamic_model->getdatafromtable('class_scheduling_time',$where);
                        $st = array();
                        $classesdata['class_slote_details'] = $class_slote_details ? $class_slote_details :$st;


			            	$response[]	                 = $classesdata;
			            }
						$arg['status']     = 1;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = $response;
						$arg['message']    = $this->lang->line('record_found');
					}else{
						$arg['status']     = 0;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = array();
					 	$arg['message']    = $this->lang->line('record_not_found');
					}
			    }
			  }
			}
		}
	   echo json_encode($arg);
	}
	/****************Function Get classes details************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : class_details
     * @description     : Calsses details
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function class_details_post()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = checkuserid();
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
		      $_POST = json_decode(file_get_contents("php://input"), true);
			  if($_POST)
			  {
			    $this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
			    $this->form_validation->set_rules('class_id','Class Id', 'required|trim', array( 'required' => $this->lang->line('class_id_required')));
			    $this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
					'required' => $this->lang->line('business_id_req'),
					'numeric' => $this->lang->line('business_id_numeric'),
				));
				$this->form_validation->set_rules('select_dt','Date', 'required|trim', array( 'required' => 'Please select date'));
				$this->form_validation->set_rules('schedule_id','Schedule Id', 'required|trim', array( 'required' => 'Schedule id is required'));

				if($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$usid =$userdata['data']['id'];
					$time=time();
					$date = date("Y-m-d",$time);
					$select_date = $this->input->post('select_dt');
					$response=$customerData=array();
					$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
					$page_no= $page_no-1;
					$limit    = config_item('page_data_limit');
					$offset = $limit * $page_no;

					$class_id=  $this->input->post('class_id');
					$business_id=  $this->input->post('business_id');
					$customer_type=  $this->input->post('customer_type');
					$checkedin_type=  $this->input->post('checkedin_type');

					//status class complete or cancel
					$status=  $this->input->post('status');

                    $time_zone =  $this->input->get_request_header('Timezone', true);
                    $time_zone =  $time_zone ? $time_zone : 'UTC';
                    date_default_timezone_set($time_zone);

                    $upcoming_date = $this->input->post('select_dt');

                    $where=array("id"=>$class_id,"business_id"=>$business_id,"status"=>"Active");
					$class_data = $this->dynamic_model->getdatafromtable('business_class',$where,"*",'','','create_dt');
					$scheduleId = $this->input->post('schedule_id');

					if(!empty($class_data)) {

						$week_date = date("w", $upcoming_date);
						if($week_date == '0'){
							$week_date = 7;
						}

						$upcoming_dates = date('Y-m-d',$upcoming_date);
						// $where = "business_id = ".$class_data[0]['business_id']." AND class_id = ".$class_data[0]['id']." AND day_id = '".$week_date."' AND scheduled_date = '".$upcoming_dates."'";

						$where = "business_id = ".$class_data[0]['business_id']." AND class_id = ".$class_data[0]['id']." AND id = ".$scheduleId." AND day_id = '".$week_date."' AND scheduled_date = '".$upcoming_dates."'";

						$time_slote_data = $this->dynamic_model->getdatafromtable('class_scheduling_time',$where);

						$time_slote_from = '';
						$location = '';
						$location_url = '';
						$map_url = '';
						$class_end_status = 0;
						$add_customer_status = 0;
                        if(!empty($time_slote_data)){
                        	$class_scheduling_status = $time_slote_data[0]['status'];
							$time_slote_from = $time_slote_data[0]['from_time'];

							if($time_slote_from < time()){
								$add_customer_status = 1;
							}

							if($class_scheduling_status == 'Deactive'){
								$add_customer_status = 1;
							}
							$locationId = $time_slote_data[0]['location_id'];
							$class_end_status = $time_slote_data[0]['class_end_status'];

							if ($locationId != null) {
								$locationInfo = $this->db->get_where('business_location', array('id' => $locationId))->row_array();
								if (!empty($locationInfo)) {
									$location = $locationInfo['location_name'];
									$location_url = $locationInfo['location_url'];
									$map_url = $locationInfo['map_url'];
								}
							}

                        }else{
                            $arg['status']     = 0;
                            $arg['error_code']  = REST_Controller::HTTP_OK;
                            $arg['error_line']= __line__;
                            $arg['data']       = json_decode('{}');
                            $arg['message']    = $this->lang->line('record_not_found');
                            echo json_encode($arg); die;
						}



						$classesdata['class_end_status']  = $class_end_status;
						$classesdata['class_scheduling_status'] = $class_scheduling_status;
						$classesdata['add_customer_status'] = $add_customer_status;
						$classesdata['schedule_id']      = $time_slote_data[0]['id'];
						$classesdata['class_id']     = $class_data[0]['id'];
		            	$classesdata['class_name']   = ucwords($class_data[0]['class_name']);
		            	$classesdata['from_time']    = $class_data[0]['from_time'];
		            	$classesdata['from_time']    = $time_slote_from;
		            	$classesdata['from_time_dt']    = date('d-m-y h:m',$time_slote_from);
		            	$classesdata['current_time']    = date('d-m-y h:m',$time);
						//$classesdata['to_time']      = $class_data[0]['to_time'];
						$classesdata['to_time']      = $time_slote_data[0]['to_time'];
						$classesdata['scheduled_date']      = strtotime($time_slote_data[0]['scheduled_date']);
						$classesdata['location']      = $location;

						$classesdata['web_link']      	= $location_url;
						$classesdata['location_url']    = $map_url;

						$classesdata['from_time_utc'] = $time_slote_from;
						$classesdata['to_time_utc']      =$class_data[0]['to_time'];

		            	//$classesdata['from_time_utc']    = $class_data[0]['from_time'];
		            	$classesdata['duration']     = $class_data[0]['duration'].' minutes';

		            	$classesdata['description']      =$class_data[0]['description'];
		            	$classesdata['start_date']      =date("d M Y ",strtotime($class_data[0]['start_date']));
						$classesdata['end_date']      =date("d M Y ",strtotime($class_data[0]['end_date']));

		            	$classesdata['start_date_utc']      =strtotime($class_data[0]['start_date']);
		            	$classesdata['end_date_utc']      =strtotime($class_data[0]['end_date']);
		            	// $capicty_used                 	= get_checkin_class_or_workshop_count($class_data[0]['id'],1,$time);
		            	$capicty_used                 	= get_checkin_class_or_workshop_daily_count($class_data[0]['id'],1,$time_slote_data[0]['scheduled_date'], $scheduleId);
			            $classesdata['total_capacity']    = $class_data[0]['capacity'];
			            $classesdata['capacity_used']     = $capicty_used;

						$timeframe = get_daywise_instructor_data($class_data[0]['id'],1,$business_id, $scheduleId);
						array_walk ( $timeframe, function (&$key) {
							$key["scheduled_date"] = strtotime($key['scheduled_date']);
						});
						$classesdata['timeframe']     = $timeframe;

		            	// $classesdata['location']     = $class_data[0]['location'];
		            	$classesdata['class_type']   = get_categories($class_data[0]['class_type']);
		            	if($class_data[0]['end_date']>=$time){
		            	  $classesdata['status']="Complete";
		            	}else{
		            		$classesdata['status']="Inprogress";
		            	}
		            	$classesdata['create_dt']    = date("d M Y ",$class_data[0]['create_dt']);
						$classesdata['create_dt_utc']    = $class_data[0]['create_dt'];

					 // select_date
					   // $customer_details=$this->instructor_model->get_all_signed_classes($business_id,$class_data[0]['id'],$date,$customer_type,$checkedin_type,$usid,$limit,$offset);
					  	if ($checkedin_type == '1') {
							$customer_details=$this->instructor_model->get_all_signed_classes($business_id,$class_data[0]['id'],$select_date,$customer_type,$checkedin_type,$usid,$limit,$offset, $scheduleId, 1);
					   	} else {
							$customer_details=$this->instructor_model->get_all_signed_classes($business_id,$class_data[0]['id'],$select_date,$customer_type,$checkedin_type,$usid,$limit,$offset, $scheduleId);
						}

					  // print_r($customer_details); die;


                       	/* if(!empty($customer_details)){
							foreach($customer_details as $value){
								$customerdata['id']     = $value['user_id'];
								$customerdata['name']   = ucwords($value['name']);
								$customerdata['lastname'] = ucwords($value['lastname']);
								$customerdata['gender'] = $value['gender'];
								$customerdata['date_of_birth'] =!empty($value['date_of_birth']) ? $value['date_of_birth'] : '';
								$customerdata['profile_img']= $value['profile_img'];
								$customerdata['business_id']=$value['business_id'];
								$customerdata['class_end_status']=$value['class_end_status'];
								//get pass Id
								$con=array("service_id"=>$class_data[0]['id'],"service_type"=>1);
								$pass_data = $this->dynamic_model->getdatafromtable('business_passes',$con);
								$pass_id=!empty($pass_data[0]['pass_id']) ? $pass_data[0]['pass_id'] :'';
								$customerdata['pass_purchase_id']="$pass_id";

								// $con1="service_id='".$class_id."' AND service_type='1' AND user_id='".$value['user_id']."' AND DATE(FROM_UNIXTIME(user_attendance.create_dt))='".$date."'";
								$con1="service_id='".$class_id."' AND service_type='1' AND user_id='".$value['user_id']."' AND checkin_dt='".$select_date."'";
								$user_attendance = $this->dynamic_model->getdatafromtable('user_attendance',$con1,'','1','0','update_dt','DESC');

								if (!empty($user_attendance)) {
									$current_status=(!empty($user_attendance[0]['status'])) ? $user_attendance[0]['status'] : '';
									$classes_status = '0';
									if($current_status == 'singup') {
										$classes_status = '1';
									} elseif($current_status == 'checkin') {
										$classes_status = '2';
									} elseif($current_status == 'cancel') {
										$classes_status = '5';
									} elseif($current_status == 'absence') {
										$classes_status = '6';
									} elseif($current_status == 'waiting') {
										$classes_status = '4';
									} elseif($current_status == 'checkout') {
										$classes_status = '3';
									}

									$customerdata['class_status']   = $classes_status;
									$covid_info = getUserQuestionnaire($value['user_id'],$class_data[0]['id'],$business_id);
									if(!empty($covid_info)){
										$customerdata['covid_status'] = $covid_info['covid_status'];
										$customerdata['covid_info'] = $covid_info['covid_info'];
								   	}else {
										$customerdata['covid_info'] = 0;
										$customerdata['covid_status'] = 0;
								   	}

									$customerdata['waitng_list_no'] =(!empty($user_attendance[0]['waitng_list_no'])) ? $user_attendance[0]['waitng_list_no'] : '';

									if (!empty($classes_status)) {
										$customerData[] =$customerdata;
									}
								}
							}
                       	} */
						   $customerData = array();
                       	if(!empty($customer_details)){
							foreach($customer_details as $value){
								//print_r( $value); die;

								$this->db->select('b.*');
		                        $this->db->from('business_passes_associates as bpa');
		                        $this->db->join('business_passes b', 'b.id = bpa.pass_id');
		                        $this->db->where('bpa.business_id',$business_id);
		                        $this->db->where('bpa.class_id',$class_id);
		                        $this->db->where('b.status',"Active");
		                        $passes_data = $this->db->get()->result_array();
		                        $pass_id_array = array();
								if(!empty($passes_data)){
								    foreach($passes_data as $values)
						            {
						            	$pass_id_array[] = $values['id'];
						            }
						        }

						        if (!empty($pass_id_array)) {
									$pass_id_array = implode(",", array_unique($pass_id_array));
									$sql = "SELECT * FROM user_booking WHERE user_id = '".$value['id']."' && service_type = 1 && passes_status = '1' && service_id IN ($pass_id_array)";
		                            $my_passes_data = $this->dynamic_model->getQueryResultArray($sql);
									$pass_arr = array();
		                            if(!empty($my_passes_data)){
		                                foreach($my_passes_data as $valuess)
		                                {
		                                    $passesdata=getpassesdetails($valuess['service_id'],$valuess['user_id']); // $usid
		                                    $business_ids = $valuess['business_id'];
		                                    $business_info = $this->dynamic_model->getQueryRowArray('SELECT *  FROM business WHERE id = '.$business_ids);
		                                    $passesdata['business_logo'] =  empty($business_info['business_image']) ? '' : site_url().'uploads/business/'.$business_info['business_image'];
		                                    $pass_arr[]   = $passesdata;
		                                }
		                            }
	                            	$customerdata['my_passes_details'] = $pass_arr;
		                        }else{
		                            $customerdata['my_passes_details'] = array();
								}

								$sql = "SELECT * FROM user_attendance WHERE user_id = '".$value['id']."' && service_id = '".$class_id."' && schedule_id = '".$scheduleId."'";
		                        $attendance_data = $this->dynamic_model->getQueryResultArray($sql);
		                       	$attendance_id = '';
		                        if(!empty($attendance_data)){
		                        	$attendance_id = $attendance_data[0]['id'];
		                        }


								$customerdata['attendance_id']     = $attendance_id;
								$customerdata['id']     = $value['id'];
								$customerdata['name']   = ucwords($value['name']);
								$customerdata['lastname'] = ucwords($value['lastname']);
								$customerdata['gender'] = $value['gender'];
								$customerdata['date_of_birth'] =!empty($value['date_of_birth']) ? $value['date_of_birth'] : '';
								$customerdata['profile_img']= $value['profile_img'];
								$customerdata['business_id']=$value['business_id'];
								$customerdata['class_end_status']=$value['class_end_status'];
								$customerdata['covid_info']=$value['covid_info'];
								$customerdata['covid_status']=$value['covid_status'];
								$con=array("id"=>$value['user_pass_id']);
								$pass_data = $this->dynamic_model->getdatafromtable('business_passes',$con);
								$pass_id=!empty($pass_data[0]['pass_id']) ? $pass_data[0]['pass_id'] :'';
								$customerdata['pass_purchase_id']= $value['user_pass_id'] .''.$pass_id;

								$con1="service_id='".$class_id."' AND service_type='1' AND schedule_id = ".$scheduleId." AND user_id='".$value['id']."' AND checkin_dt='".date('Y-m-d', $select_date)."'";
								$user_attendance = $this->dynamic_model->getdatafromtable('user_attendance',$con1,'','1','0','update_dt','DESC');
								if (!empty($user_attendance)) {
									$current_status=(!empty($user_attendance[0]['status'])) ? $user_attendance[0]['status'] : '';
									$classes_status = '0';
									if($current_status == 'singup') {
										$classes_status = '1';
									} elseif($current_status == 'checkin') {
										$classes_status = '2';
									} elseif($current_status == 'cancel') {
										$classes_status = '5';
									} elseif($current_status == 'absence') {
										$classes_status = '6';
									} elseif($current_status == 'waiting') {
										$classes_status = '4';
									} elseif($current_status == 'checkout') {
										$classes_status = '3';
									}

									$customerdata['class_status']   = $classes_status;
									$customerdata['waitng_list_no'] =(!empty($user_attendance[0]['waitng_list_no'])) ? $user_attendance[0]['waitng_list_no'] : '';
								} else {
									$customerdata['class_status']   = '1';
									$customerdata['waitng_list_no'] = '';

								}
								$customerData[] =$customerdata;
							}
                       	}
						$classesdata['customer_details'] =$customerData;
		            	$response= $classesdata;

						$arg['status']     = 1;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = $response;
						$arg['message']    = $this->lang->line('record_found');
					}else{
						$arg['status']     = 0;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = json_decode('{}');
					 	$arg['message']    = $this->lang->line('record_not_found');
					}
			    }
			  }
			}
		}
	   echo json_encode($arg);
	}

	public function passes_status_change_post()
	{
		$arg   = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
		   $userdata = checkuserid();
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
				$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
				{
				    $this->form_validation->set_rules('user_id','User Id','required|trim', array( 'user_id_required' => $this->lang->line('service_type_required')));
				    $this->form_validation->set_rules('service_type','Service Type','required|trim', array( 'required' => $this->lang->line('service_type_required')));
				    $this->form_validation->set_rules('service_id','Service Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('schedule_id','Schedule Id', 'required|trim', array( 'required' => 'Schedule id is required'));

					$this->form_validation->set_rules('passes_status','Service Id', 'required|trim', array( 'required' => $this->lang->line('passes_status_required')));

					if($this->form_validation->run() == FALSE)
					{
					  	$arg['status']  = 0;
					  	$arg['error_code'] = 0;
						$arg['error_line']= __line__;
					 	$arg['message'] = get_form_error($this->form_validation->error_array());
					}
					else
					{
						$lat = $this->input->get_request_header('lat');
						$lang = $this->input->get_request_header('lang');
						$room_capacity=$waitng_list_no='';
						$usid =$userdata['data']['id'];
						$updateData=$response=array();
						$time=time();
						$date = date("Y-m-d",$time);
						$user_id    = $this->input->post('user_id');
						$service_id    = $this->input->post('service_id');
						//service_type=> 1 class 2 workshop 3 trainer
						$service_type    = $this->input->post('service_type');
						// passes_status=> checkin checkout cancel
						$passes_status    = $this->input->post('passes_status');
						$schedule_id 	  = $this->input->post('schedule_id');
						$pass_id 	  = $this->input->post('pass_id');
						$attendance_id 	  = $this->input->post('attendance_id');
						//Check same user not eligble to change checkin status

						if(!empty($attendance_id) && empty($pass_id)){
                            $whe="id = '".$attendance_id."'";
                            $passes_data = $this->dynamic_model->getdatafromtable('user_attendance',$whe);
                            if(!empty($passes_data)){
                                $pass_id = $passes_data[0]['pass_id'];
                            }
                        }

						$day_update = 1;
                        if(!empty($pass_id)){
                            $whe="id = '".$pass_id."'";
                            $passes_data = $this->dynamic_model->getdatafromtable('business_passes',$whe);
                            if(!empty($passes_data)){
                                $pass_type = $passes_data[0]['pass_type'];
                                if($pass_type == '10' || $pass_type == '37'){
                                    $day_update = 0;
                                }
                            }
                        }

                        if($usid ==$user_id){
				        	$arg['status']     = 0;
				            $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['data']       = json_decode('{}');
							$arg['message']    = 'Same user not eligble to change status';//$this->lang->line('something_wrong');
							$arg['start_time'] = 0;
							$arg['start_time_message'] = '';
							echo json_encode($arg);exit;
						}

						$getSchedule = $this->dynamic_model->getQueryRowArray('SELECT * FROM class_scheduling_time where id = '. $schedule_id);
                        $class_scheduling_status = $getSchedule['status'];
                        $class_scheduling_date = $getSchedule['scheduled_date'];
						if($class_scheduling_status == 'Deactive'){
							$arg['status']     = 0;
							$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['data']       = json_decode('{}');
							$arg['message']    = 'You can not change status.';
							echo json_encode($arg);exit;
						}

						if($passes_status == 'notcheckin')
						{
							$where = array('id'=>$service_id,'status'=>"Active");
							$business_class= $this->dynamic_model->getdatafromtable('business_class',$where);
                            $class_days_prior_signup = $business_class[0]['class_days_prior_signup'] ? $business_class[0]['class_days_prior_signup'] : 1;

                            $start_date = strtotime($class_scheduling_date);
							$unixTimestamp = $start_date - ((int)$class_days_prior_signup*24*60*60);
							$today = time();
							if($today >= $unixTimestamp){
							}else{
							$unixTimestamp = date('Y-m-d',$unixTimestamp);
							$arg['status']     = 0;
							$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['data']       = json_decode('{}');
							$arg['message']    = $class_days_prior_signup.' day prior, You will be signup this class.';
							echo json_encode($arg);exit;
							}
						}


						if($passes_status == 'checkin'){
							$where = array('id'=>$service_id,'status'=>"Active");
							$business_class= $this->dynamic_model->getdatafromtable('business_class',$where);
                            $business_id =  $business_class[0]['business_id'];

                            $getSchedule = $this->dynamic_model->getQueryRowArray('SELECT * FROM class_scheduling_time where id = '. $schedule_id);
                            $date = $getSchedule['scheduled_date'];

							$where = array('scheduled_date'=>$date,
									'class_id'=>$service_id,
									'business_id'=> $business_id,
								);
							$class_data = $this->dynamic_model->getdatafromtable('class_scheduling_time',$where);
							if(empty($class_data)){
								$arg['status']     = 0;
								$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line']= __line__;
								$arg['data']       = json_decode('{}');
								$arg['message']    = 'Class not available today.';
								echo json_encode($arg);exit;
							}else{
								$class_scheduling_status = $class_data[0]['status'];
								if($class_scheduling_status == 'Deactive'){
									$arg['status']     = 0;
									$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line']= __line__;
									$arg['data']       = json_decode('{}');
									$arg['message']    = 'You can not change status.';
									echo json_encode($arg);exit;
								}
								$from_time = $class_data[0]['from_time'];
                                $scheduled_end_time = $class_data[0]['to_time'];
                                $scheduled_date = $class_data[0]['scheduled_date'];
								$from_time = $from_time - 15*60;
								$time = time();

                                $sed = date('h:i:s A',$scheduled_end_time);
                                $rt = $scheduled_date . ' ' . $sed;
                                $scheduled_end_date_time = strtotime($rt);

                                $sed = date('h:i:s A',$from_time);
                                $rt = $scheduled_date . ' ' . $sed;
                                $scheduled_start_date_time = strtotime($rt);

								if($from_time > $time){
									$unixTimestamp = date('h:i:s A',$from_time);
									$arg['status']     = 0;
									$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line']= __line__;
									$arg['data']       = json_decode('{}');
									$arg['message']    = 'System will allow you to check In before 15 min of class start time.';
									echo json_encode($arg);exit;
								}/*else if($scheduled_end_date_time < $time){
                                    $unixTimestamp = date('h:i:s A',$from_time);
                                    $arg['status']     = 0;
                                    $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
                                    $arg['error_line']= __line__;
                                    $arg['data']       = json_decode('{}');
                                    $arg['message']    = 'Class time is over.';
                                    echo json_encode($arg);exit;
                                }*/
							}

						}

						if ($passes_status == 'notcheckin') {
							$where = array('id'=>$service_id,'status'=>"Active");
							$business_class= $this->dynamic_model->getdatafromtable('business_class',$where);
                            $business_id =  $business_class[0]['business_id'];

                            $whe="user_id = '".$user_id."' AND service_id = '".$service_id."' AND schedule_id = '".$schedule_id."'";
                            $user_attendance_check = $this->dynamic_model->getdatafromtable('user_attendance',$whe);
                            $pass_id = $user_attendance_check[0]['pass_id'];

							$whe="user_id = '".$user_id."' AND business_id = '".$business_id."' AND passes_remaining_count != '0' AND passes_status = '1' AND status = 'Success' AND service_id = '".$pass_id."'";
                            $pass_status_check = $this->dynamic_model->getdatafromtable('user_booking',$whe);
                            if(!empty($pass_status_check)){
                            	 $user_booking_id = $pass_status_check[0]['id'];
                            	$passes_remaining_count = ($pass_status_check[0]['passes_remaining_count'] - 1);
                        		$updateData =   array(
                                'passes_remaining_count' =>  $passes_remaining_count
                            	);
                            	if(!empty($day_update)){
	                    			$this->dynamic_model->updateRowWhere('user_booking', array('id' => $user_booking_id), $updateData);
	                    		}
                            }
					    }

						if($passes_status == 'cancel'){
							$where = array('id'=>$service_id,'status'=>"Active");
							$business_class= $this->dynamic_model->getdatafromtable('business_class',$where);
                            $business_id =  $business_class[0]['business_id'];

                            $whe="user_id = '".$user_id."' AND service_id = '".$service_id."' AND schedule_id = '".$schedule_id."'";
                            $user_attendance_check = $this->dynamic_model->getdatafromtable('user_attendance',$whe);
                            $pass_id = $user_attendance_check[0]['pass_id'];

                            $current_time = time();
							$whe="user_id = '".$user_id."' AND business_id = '".$business_id."' AND status = 'Success' AND passes_end_date >= '".$current_time."' AND service_id = '".$pass_id."'";
							//passes_remaining_count != '0' AND passes_status = '1' AND
                            $pass_status_check = $this->dynamic_model->getdatafromtable('user_booking',$whe);

                            if (!empty($pass_status_check)) {
                            	//print_r($pass_status_check); die;
                                $user_booking_id = $pass_status_check[0]['id'];
                          		$passes_total_count = $pass_status_check[0]['passes_total_count'];
                                $passes_remaining_count = ($pass_status_check[0]['passes_remaining_count'] + 1);
                                if($passes_total_count < $passes_remaining_count){
                                	$passes_remaining_count = $pass_status_check[0]['passes_remaining_count'];
                                }

                            	$updateData =   array(
                                        'passes_remaining_count' =>  $passes_remaining_count,
                                        'passes_status' =>  1
                                    );
                            	if(!empty($day_update)){
                            		$this->dynamic_model->updateRowWhere('user_booking', array('id' => $user_booking_id), $updateData);
                            	}
                        	}
						}

						if ($passes_status == 'checkin' || $passes_status == 'notcheckin' || $passes_status == 'noshow' || $passes_status == 'cancel') {

							if ($passes_status == 'noshow') {
								$status = 'absence';
							} elseif ($passes_status == 'notcheckin') {
								$status = 'singup';
							} else {
								$status = $passes_status;
							}

							if(!empty($pass_id)){
								$update = array('status' => $status,
									'pass_id' => $pass_id
								);
							}else{
								$update = array('status' => $status);
							}



							$this->dynamic_model->updateRowWhere('user_attendance', array('service_type' => 1, 'user_id' => $user_id, 'service_id' => $service_id, 'schedule_id' => $schedule_id), $update);



							$msg = '';
							if ($passes_status == 'checkin') {
								$msg = $this->lang->line('check_in_succ');
							} elseif ($passes_status == 'noshow') {
								$msg= $this->lang->line('attendance_absent_succ');
							} elseif ($passes_status == 'cancel') {
								$msg= $this->lang->line('attendance_cancel_succ');
							} else {
								$msg= $this->lang->line('check_signup_succ');
							}

							$arg['status']    = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
							$arg['message']   = $msg;

						} else {
							$arg['status']     = 0;
				            $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['message']    = $this->lang->line('something_wrong');
							echo json_encode($arg);exit;
						}

						// singup, checkin, checkout, cancel, waiting, 'absence'
						/* if ($passes_status == 'checkin' || $passes_status == 'noshow') {

							$business_class_id = 0;
							$room_capacity_count = 0;
							$waitlist_capacity = 0;
							$schedule_id = $this->input->post('schedule_id');

							$where = array('id'=>$service_id ,'status'=>"Active");
							if($service_type==1){

								$business_class= $this->dynamic_model->getdatafromtable('business_class',$where);
								$business_id=(!empty($business_class[0]['business_id'])) ? $business_class[0]['business_id'] : 0;
								$room_capacity=(!empty($business_class[0]['capacity'])) ? $business_class[0]['capacity'] : 0;
								$class_days_prior_signup = $business_class[0]['class_days_prior_signup'] ? $business_class[0]['class_days_prior_signup'] : 1;
								$start_date = $business_class[0]['start_date'];
								$duration = $business_class[0]['duration'];
								$room_capacity_count = $room_capacity;
								$waitlist_capacity=(!empty($business_class[0]['class_waitlist_count'])) ? $business_class[0]['class_waitlist_count'] : 0;
								$total_count = $room_capacity + $waitlist_capacity;
								$business_class_id = (!empty($business_class[0]['id'])) ? $business_class[0]['id'] : 0;
								$getSchedule = $this->dynamic_model->getQueryRowArray('SELECT * FROM class_scheduling_time where id = '. $schedule_id);
								$getScheduleInfo = get_checkin_class_or_workshop_daily_count($business_class[0]['id'], 1, $getSchedule['scheduled_date']);

								$date = $getSchedule['scheduled_date'];
								$scheduled_end_time = $getSchedule['to_time'];

								if($date == date('Y-m-d')){
									$st = date('h:i:s A',$scheduled_end_time);
									$rt = $date . ' ' . $st;
									$scheduled_end_date_time = strtotime($rt);

									if($scheduled_end_date_time < $time){
										$arg['status']     = 0;
										$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
										$arg['error_line']= __line__;
										$arg['data']       = json_decode('{}');
										$arg['message']    = 'Class time passed..';
										echo json_encode($arg);exit;
									}
								}


								$condition_waiting_count = 'status = "waiting" AND service_id = '.$service_id.' AND checkin_dt = "'.$date.'" AND schedule_id = '.$schedule_id;
								$wait_count = $this->db->get_where('user_attendance', $condition_waiting_count)->num_rows();

								// Check same user signup request
								if($passes_status == 'singup') {
									//$whe="user_id = '".$usid."' AND service_id = '".$service_id."' AND checkin_dt = '".$date."' AND status = 'singup'";
									$whe="user_id = '".$user_id."' AND service_id = '".$service_id."' AND checkin_dt = '".$date."'";
									$attendance = $this->dynamic_model->getdatafromtable('user_attendance',$whe);
									if(!empty($attendance)){
										$arg['status']     = 0;
										$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
										$arg['error_line']= __line__;
										$arg['data']       = json_decode('{}');
										$arg['message']    = 'You have already signed up';
										echo json_encode($arg);exit;
									}
								}

								// Check new user signup request

								if (($wait_count + $getScheduleInfo) >= $total_count && $passes_status == 'singup') {
									$arg['status']     = 0;
									$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line'] = __line__;
									$arg['data']       = json_decode('{}');
									$arg['message']    = 'Class capacity is already full.';
									echo json_encode($arg); exit();
								}

								if($passes_status == 'singup')
								{

									$start_date = strtotime($start_date);
									$unixTimestamp = $start_date - ((int)$class_days_prior_signup*24*60*60);

									$today = time();
									if($today >= $unixTimestamp){

									}else{
										$open_time = $unixTimestamp;
										$unixTimestamp = date('Y-m-d',$unixTimestamp);
										$arg['status']     = 0;
										$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
										$arg['error_line']= __line__;
										$arg['data']       = json_decode('{}');
										$arg['message']    = 'Sing up is open '.$unixTimestamp;
										$arg['start_time'] = $open_time;
										$arg['start_time_message'] = 'Sing up is open ';
										echo json_encode($arg);exit;
									}

								}

								if($passes_status == 'checkin'){
									//$today_date = date('Y-m-d');

									$where = array('scheduled_date'=>$date,
										'class_id'=>$service_id,
										'business_id'=> $business_id,
									);
									$class_data = $this->dynamic_model->getdatafromtable('class_scheduling_time',$where);
									if(empty($class_data)){
										$arg['status']     = 0;
										$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
										$arg['error_line']= __line__;
										$arg['data']       = json_decode('{}');
										$arg['message']    = 'Class not available today.';
										$arg['start_time'] = 0;
										$arg['start_time_message'] = '';
										echo json_encode($arg);exit;
									}else{
										$from_time = $class_data[0]['from_time'];
										$from_time = $from_time - 15*60;
										$time = time();
										if($from_time > $time){
											$unixTimestamp = date('h:i:s A',$from_time);
											$arg['status']     = 0;
											$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
											$arg['error_line']= __line__;
											$arg['data']       = json_decode('{}');
											$arg['message']    = 'Cheken allow after '.$unixTimestamp;
											$arg['start_time'] = $from_time;
											$arg['start_time_message'] = 'Cheken allow after ';
											echo json_encode($arg);exit;
										}

									}
								}

								if($passes_status == 'noshow') {
									$where = array('scheduled_date'=>$date,
										'class_id'=>$service_id,
										'business_id'=> $business_id,
									);
									$class_data = $this->dynamic_model->getdatafromtable('class_scheduling_time',$where);
									if(empty($class_data)){
										$arg['status']     = 0;
										$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
										$arg['error_line']= __line__;
										$arg['data']       = json_decode('{}');
										$arg['message']    = 'Class not available today.';
										$arg['start_time'] = 0;
										$arg['start_time_message'] = '';
										echo json_encode($arg);exit;
									} else {
										$from_time = $class_data[0]['from_time'];
										$from_time = $from_time - 15*60;

										$to_time = $class_data[0]['to_time'];
										$time = time();

										if($from_time > $time){
											$unixTimestamp = date('h:i:s A',$from_time);
											$arg['status']     = 0;
											$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
											$arg['error_line']= __line__;
											$arg['data']       = json_decode('{}');
											$arg['message']    = 'Cheken allow after '.$unixTimestamp;
											$arg['start_time'] = $from_time;
											$arg['start_time_message'] = 'Cheken allow after ';
											echo json_encode($arg);exit;
										}

										if($time> $to_time) {
											$arg['status']     = 0;
											$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
											$arg['error_line']= __line__;
											$arg['data']       = json_decode('{}');
											$arg['message']    = 'Time is over';
											echo json_encode($arg);exit;
										}
									}
								}


							} elseif ($service_type == 2) {
								// workshop
							} else {
								// Service
							}

							$message_waiting = '';
							$condition="user_id=".$user_id." AND service_id=".$service_id.' AND checkin_dt = "'.$date.'" AND schedule_id = '.$schedule_id;
							$signup_check = $this->dynamic_model->getdatafromtable('user_attendance',$condition);

							if(empty($signup_check)) {

								// Freash user record

								if($passes_status !=='singup'){
									$arg['status']     = 0;
									$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line']= __line__;
									$arg['data']       = json_decode('{}');
									$arg['message']    = $this->lang->line('passes_status_error');
									 echo json_encode($arg);exit;
								}

								// Check current user signup
								$getCurrentStatus = get_checkin_class_or_workshop_daily_count($business_class_id, 1, $date); // Signup and checkin user
								$couter = $getCurrentStatus + 1;

								// User signup
								$insertData =   array(
									'user_id'  		=>	$user_id,
									'status'  		=>	$passes_status,
									'service_id'    =>	$service_id,
									'service_type'  =>	$service_type,
									'checkin_time'  =>	0,
									'checkout_time' =>	0,
									'signup_status' =>	1,
									'create_dt'   	=>	$time,
									'update_dt'   	=>	$time,
									'checkin_dt'	=>	$date,
									'schedule_id' 	=> 	$schedule_id
								);

								$current_Waiting_number = 0;
								if ($couter > $room_capacity_count) {

									$condition_waiting_count = 'status = "waiting" AND service_id = '.$service_id.' AND checkin_dt = "'.$date.'" AND schedule_id = '.$schedule_id;
									$wait_count = $this->db->get_where('user_attendance', $condition_waiting_count)->num_rows();
									$current_Waiting_number = $wait_count + 1;
									$insertData['status'] = 'waiting';
									$insertData['waitng_list_no'] = $current_Waiting_number;
									$passes_status = 'waiting';
								}

								$checkId= $this->dynamic_model->insertdata('user_attendance',$insertData);
								if ($current_Waiting_number > 0) {
									$message_waiting = 'Current waiting list number is : '.$current_Waiting_number;
								}

							} else {
								// Pass status only checkin or cancel
								$getSchedule = $this->dynamic_model->getQueryRowArray('SELECT * FROM class_scheduling_time where id = '. $schedule_id);

								if ($passes_status=='checkin') {

									if ($signup_check[0]['status'] == 'singup') {
										$updateData =   array(
											'status'  		=>	$passes_status,
											'checkin_time'  =>	$getSchedule['from_time'],
											'checkout_time' =>	$getSchedule['to_time'],
											'signup_status' =>	1,
											'update_dt'   	=>	$time,
										);
										$checkId = $this->dynamic_model->updateRowWhere('user_attendance', array('id' => $signup_check[0]['id']), $updateData);
										$msg1= $this->lang->line('check_in_succ');
									} else {
										$arg['status']     = 0;
										$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
										$arg['error_line']= __line__;
										$arg['data']       = json_decode('{}');
										$arg['message']    = 'Request not allowed';
										echo json_encode($arg); exit();
									}

								} elseif($passes_status=='cancel') {

									if ($signup_check[0]['status'] == 'checkin') {
										$arg['status']     = 0;
										$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
										$arg['error_line']= __line__;
										$arg['data']       = json_decode('{}');
										$arg['message']    = 'Cancellation is not possible.';
										echo json_encode($arg); exit();

									} else {

										$current_status = $signup_check[0]['status'];

										if ($current_status == 'singup') {

											$current_waiting_condition="status = 'waiting' AND service_id=".$service_id.' AND checkin_dt = "'.$date.'" AND schedule_id = '.$schedule_id;
											$current_waiting = $this->dynamic_model->getdatafromtable('user_attendance',$current_waiting_condition);

											$updateArray = array();
											if(!empty($current_waiting)) {
												foreach($current_waiting as $cw) {
													$set_status = 'waiting';
													$waitng_list_no = $cw['waitng_list_no'];
													if ($waitng_list_no == 1) {
														$set_status = 'singup';
													}
													$waitng_list_no =  $waitng_list_no - 1;
													array_push($updateArray, array('id' => $cw['id'], 'status' => $set_status, 'waitng_list_no' => $waitng_list_no));
												}
											}

											$updateData =   array(
												'user_id'  		=>	$usid,
												'status'  		=>	$passes_status,
												'update_dt'   	=>	$time,
											);
											$checkId= $this->dynamic_model->updateRowWhere('user_attendance', array('id' => $signup_check[0]['id']), $updateData);

											if (!empty($updateArray)) {
												$this->db->update_batch('user_attendance',$updateArray, 'id');
											}

										} elseif($current_status == 'waiting') {

											$current_wait_list_number = $signup_check[0]['waitng_list_no'];

											$current_waiting_condition="status = 'waiting' AND waitng_list_no > ".$current_wait_list_number." AND service_id=".$service_id.' AND checkin_dt = "'.$date.'" AND schedule_id = '.$schedule_id;
											$current_waiting = $this->dynamic_model->getdatafromtable('user_attendance',$current_waiting_condition);

											$updateArray = array();
											if(!empty($current_waiting)) {
												foreach($current_waiting as $cw) {
													$waitng_list_no = $cw['waitng_list_no'];
													$waitng_list_no =  $waitng_list_no - 1;
													array_push($updateArray, array('id' => $cw['id'], 'waitng_list_no' => $waitng_list_no));
												}
											}

											$updateData =   array(
												'user_id'  		=>	$usid,
												'status'  		=>	$passes_status,
												'waitng_list_no'=>	0,
												'update_dt'   	=>	$time,
											);
											$checkId= $this->dynamic_model->updateRowWhere('user_attendance', array('id' => $signup_check[0]['id']), $updateData);

											if (!empty($updateArray)) {
												$this->db->update_batch('user_attendance',$updateArray, 'id');
											}

										}

									}

								} elseif ($passes_status=='noshow') {
									$id = $signup_check[0]['id'];
									$this->db->where('id', $id);
									$checkId= $this->db->update('user_attendance', array('status' => 'absence'));
								}

							}

							if($passes_status=='singup'){
								$msg= $this->lang->line('check_signup_succ');
							} elseif($passes_status=='checkin'){
									$msg= $msg1;
							} elseif($passes_status=='checkout'){
								$msg= $this->lang->line('check_out_succ');

							}elseif($passes_status=='cancel'){
								if ($message_waiting == '0') {
									$msg= 'Not allowed';
								} else {
									$msg= $this->lang->line('attendance_cancel_succ');
								}

							} elseif($passes_status=='waiting') {
								$msg= $message_waiting;
							} elseif($passes_status == 'noshow') {
								$msg= $this->lang->line('attendance_absent_succ');
							}

							if($checkId)
							{
								$response= array("wating_list_no"=>"$waitng_list_no");
								$arg['status']    = 1;
								$arg['error_code'] = REST_Controller::HTTP_OK;
								$arg['error_line']= __line__;
								$arg['message']   = $msg;
								$arg['data']      = $response;
							}else{
								$arg['status']     = 0;
								$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line']= __line__;
								$arg['data']       = json_decode('{}');
								$arg['message']    = $this->lang->line('server_problem');
							}

						} else {
							$arg['status']     = 0;
				            $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['data']       = json_decode('{}');
							$arg['message']    = $this->lang->line('something_wrong');
							$arg['start_time'] = 0;
							$arg['start_time_message'] = '';
							echo json_encode($arg);exit;
						} */
					}
				}
		    }
        }
	  echo json_encode($arg);
	}

	public function passes_status_change_14082020_post()
	{
		$arg   = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
		   $userdata = checkuserid();
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
				$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
				{
				    $this->form_validation->set_rules('user_id','User Id','required|trim', array( 'user_id_required' => $this->lang->line('service_type_required')));
				    $this->form_validation->set_rules('service_type','Service Type','required|trim', array( 'required' => $this->lang->line('service_type_required')));
				    $this->form_validation->set_rules('service_id','Service Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('schedule_id','Schedule Id', 'required|trim', array( 'required' => 'Schedule id is required'));

					$this->form_validation->set_rules('passes_status','Service Id', 'required|trim', array( 'required' => $this->lang->line('passes_status_required')));

					if($this->form_validation->run() == FALSE)
					{
					  	$arg['status']  = 0;
					  	$arg['error_code'] = 0;
						$arg['error_line']= __line__;
					 	$arg['message'] = get_form_error($this->form_validation->error_array());
					}
					else
					{
						$lat = $this->input->get_request_header('lat');
						$lang = $this->input->get_request_header('lang');
						$room_capacity=$waitng_list_no='';
						$usid =$userdata['data']['id'];
						$updateData=$response=array();
						$time=time();
						$date = date("Y-m-d",$time);
						$user_id    = $this->input->post('user_id');
						$service_id    = $this->input->post('service_id');
						//service_type=> 1 class 2 workshop 3 trainer
						$service_type    = $this->input->post('service_type');
						// passes_status=> checkin checkout cancel
						$passes_status    = $this->input->post('passes_status');

						// $where = array('id'=>$service_id,'user_id'=>$usid,'status'=>"Active");
						$where = array('id'=>$service_id ,'status'=>"Active");
						//find capcity class or workshop
						if($service_type==1){
				        	$business_data= $this->dynamic_model->getdatafromtable('business_class',$where);
				        	$business_id=(!empty($business_data[0]['business_id'])) ? $business_data[0]['business_id'] : 0;
				        	$room_capacity=(!empty($business_data[0]['capacity'])) ? $business_data[0]['capacity'] : 0;
							$class_days_prior_signup = $business_data[0]['class_days_prior_signup'] ? $business_data[0]['class_days_prior_signup'] : 1;
							$start_date = $business_data[0]['start_date'];
							$duration = $business_data[0]['duration'];
							$waitlist_capacity=(!empty($business_class[0]['class_waitlist_count'])) ? $business_class[0]['class_waitlist_count'] : 0;
							$total_count = $room_capacity + $waitlist_capacity;

							$schedule_id = $this->input->post('schedule_id');
							$getSchedule = $this->dynamic_model->getQueryRowArray('SELECT * FROM class_scheduling_time where id = '. $this->input->post('schedule_id'));

							$getScheduleInfo = get_checkin_class_or_workshop_daily_count($business_data[0]['id'], 1, $getSchedule['scheduled_date']);
							$scheduled_date = $getSchedule['scheduled_date'];

							if($passes_status == 'singup')
							{

								$start_date = strtotime($start_date);
								$unixTimestamp = $start_date - ((int)$class_days_prior_signup*24*60*60);

								$today = time();
								if($today >= $unixTimestamp){

								}else{
									$open_time = $unixTimestamp;
									$unixTimestamp = date('Y-m-d',$unixTimestamp);
									$arg['status']     = 0;
									$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line']= __line__;
									$arg['data']       = json_decode('{}');
									$arg['message']    = 'Sing up is open '.$unixTimestamp;
									$arg['start_time'] = $open_time;
									$arg['start_time_message'] = 'Sing up is open ';
									echo json_encode($arg);exit;
								}

							}

							if($passes_status == 'checkin'){
								//$today_date = date('Y-m-d');

								$where = array('scheduled_date'=>$date,
									'class_id'=>$service_id,
									'business_id'=> $business_id,
								);
								$class_data = $this->dynamic_model->getdatafromtable('class_scheduling_time',$where);
								if(empty($class_data)){
									$arg['status']     = 0;
									$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line']= __line__;
									$arg['data']       = json_decode('{}');
									$arg['message']    = 'Class not available today.';
									$arg['start_time'] = 0;
									$arg['start_time_message'] = '';
									echo json_encode($arg);exit;
								}else{
									$from_time = $class_data[0]['from_time'];
									$from_time = $from_time - 15*60;
									$time = time();
									if($from_time > $time){
										$unixTimestamp = date('h:i:s A',$from_time);
										$arg['status']     = 0;
										$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
										$arg['error_line']= __line__;
										$arg['data']       = json_decode('{}');
										$arg['message']    = 'Cheken allow after '.$unixTimestamp;
										$arg['start_time'] = $from_time;
										$arg['start_time_message'] = 'Cheken allow after ';
										echo json_encode($arg);exit;
									}

								}
							}

							if($passes_status == 'noshow') {
								$where = array('scheduled_date'=>$date,
									'class_id'=>$service_id,
									'business_id'=> $business_id,
								);
								$class_data = $this->dynamic_model->getdatafromtable('class_scheduling_time',$where);
								if(empty($class_data)){
									$arg['status']     = 0;
									$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line']= __line__;
									$arg['data']       = json_decode('{}');
									$arg['message']    = 'Class not available today.';
									$arg['start_time'] = 0;
									$arg['start_time_message'] = '';
									echo json_encode($arg);exit;
								} else {
									$from_time = $class_data[0]['from_time'];
									$from_time = $from_time - 15*60;

									$to_time = $class_data[0]['to_time'];
									$time = time();

									if($from_time > $time){
										$unixTimestamp = date('h:i:s A',$from_time);
										$arg['status']     = 0;
										$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
										$arg['error_line']= __line__;
										$arg['data']       = json_decode('{}');
										$arg['message']    = 'Cheken allow after '.$unixTimestamp;
										$arg['start_time'] = $from_time;
										$arg['start_time_message'] = 'Cheken allow after ';
										echo json_encode($arg);exit;
									}

									if($time> $to_time) {
										$arg['status']     = 0;
										$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
										$arg['error_line']= __line__;
										$arg['data']       = json_decode('{}');
										$arg['message']    = 'Time is over';
										echo json_encode($arg);exit;
									}
								}
							}

						}elseif($service_type==2){
				        $business_data= $this->dynamic_model->getdatafromtable('business_workshop',$where);
				        $business_id=(!empty($business_data[0]['business_id'])) ? $business_data[0]['business_id'] : 0;
				        $room_capacity=(!empty($business_data[0]['capacity'])) ? $business_data[0]['capacity'] : 0;
						}
						 //Not able to Access Scheduled classes or workshop
       //                  if(empty($business_data)){
				   //      	$arg['status']     = 0;
				   //          $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
							// $arg['error_line']= __line__;
							// $arg['data']       = json_decode('{}');
							// $arg['message']    = $this->lang->line('pass_status_not_change');
							//  echo json_encode($arg);exit;
       //                  }
						//location cheked
						// $location_check=$this->api_model->user_location_checked_in_studio($business_id,$lat,$lang);
						// if(empty($location_check)){
				  //       	$arg['status']     = 0;
				  //           $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
						// 	$arg['error_line']= __line__;
						// 	$arg['data']       = array();
						// 	$arg['message']    = $this->lang->line('not_eligible_class');
						// 	 echo json_encode($arg);exit;
						//     }
                        //Check same user not eligble to change checkin status
                        if($usid ==$user_id){
				        	$arg['status']     = 0;
				            $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['data']       = json_decode('{}');
							$arg['message']    = $this->lang->line('something_wrong');
							$arg['start_time'] = 0;
							$arg['start_time_message'] = '';
							 echo json_encode($arg);exit;
                        }
						//signup process
						$condition="user_id=".$user_id." AND service_id=".$service_id;
				        $signup_check= $this->dynamic_model->getdatafromtable('user_attendance',$condition);
				        if(empty($signup_check)){
				        	if($passes_status !=='singup'){
				        		$arg['status']     = 0;
				            	$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line']= __line__;
								$arg['data']       = json_decode('{}');
								$arg['message']    = $this->lang->line('passes_status_error');
								$arg['start_time'] = 0;
								$arg['start_time_message'] = '';
							 	echo json_encode($arg);exit;
						    }

				        	$insertData =   array(
											'user_id'  		=>$user_id,
											'status'  		=>$passes_status,
											'service_id'    =>$service_id,
											'service_type'  =>$service_type,
											'checkin_time'  =>0,
											'checkout_time' =>0,
											'signup_status' =>1,
											'checked_by'   	=>$usid,
											'create_dt'   	=>$time,
											'update_dt'   	=>$time,
											'checkin_dt'=>date('Y-m-d',$time)
						                   );

				        	$checkId= $this->dynamic_model->insertdata('user_attendance',$insertData);

				        }else{

                        //find data today wise
						$whe="user_id=".$user_id." AND service_id=".$service_id." AND service_type=".$service_type." AND DATE(FROM_UNIXTIME(create_dt))='".$date."'";
				        $check_pass= $this->dynamic_model->getdatafromtable('user_attendance',$whe);

						//check room capacity
						$where1="service_id=".$service_id." AND service_type=".$service_type." AND status='checkin' AND DATE(FROM_UNIXTIME(create_dt))='".$date."'";
				        $check_in_count= getdatacount('user_attendance',$where1);
						$getTime=strtotime(date('H:i:s',$time));
						$checkout_time = $time + ((int)$duration*60);

				        if(empty($check_pass)){
				       //$passes_status=($passes_status=='noshow') ? 'absence' : $passes_status;
						$insertData =   array(
											'user_id'  		=>$user_id,
											'status'  		=>$passes_status,
											'service_id'    =>$service_id,
											'service_type'  =>$service_type,
											'checkin_time'  =>$time,
											'checkout_time' =>$checkout_time,
											'signup_status' =>1,
											'create_dt'   	=>$time,
											'checked_by'   	=>$usid,
											'update_dt'   	=>$time,
											'checkin_dt'=>date('Y-m-d',$time)
						                   );

						$checkId= $this->dynamic_model->insertdata('user_attendance',$insertData);

							$msg1= $this->lang->line('check_in_succ');
						}elseif(!empty($check_pass ))
						{
	                       if($passes_status=='checkin'){
			                   if($room_capacity ==$check_in_count)
						       {
						       	$where2="service_id=".$service_id." AND service_type=".$service_type." AND status='waiting' AND checkin_dt='".$scheduled_date."'";
						       	$wating_data= $this->dynamic_model->getdatafromtable('user_attendance',$where2);
                                //if already waiting list
						       	$where3="user_id=".$user_id." AND service_id=".$service_id." AND service_type=".$service_type." AND status='waiting' AND checkin_dt='".$scheduled_date."'";
						       	$already_wating_data= $this->dynamic_model->getdatafromtable('user_attendance',$where3);
						       //	print_r($wating_data);die;
						       	 $already_waiting_no= (!empty($already_wating_data[0]['waitng_list_no'])) ? $already_wating_data[0]['waitng_list_no'] : 0;
						       	 $waiting_no= (!empty($wating_data[0]['waitng_list_no'])) ? $wating_data[0]['waitng_list_no'] : 0;
						       	if(empty($waiting_no)){
						       		$waitng_list_no=1;
						       	}elseif(!empty($already_wating_data)){
						       		$waitng_list_no=$already_waiting_no;
						       	}
						       	else{
						       		$waitng_list_no=$waiting_no+1;
						       	}
						       //echo $waitng_list_no;die;
						       	$updateData['status']='waiting';
						        $updateData['waitng_list_no']=$waitng_list_no;

						       	$msg1= $this->lang->line('waiting_msg');
		                       }else{
		                       	$updateData['status']=$passes_status;
		                       	$updateData['checkin_time']=$getTime;
		                       	$msg1= $this->lang->line('check_in_succ');
		                       }
	                       }elseif($passes_status=='checkout'){
	                       	$updateData['status']=$passes_status;
	                        $updateData['checkout_time']=$getTime;
	                       }elseif($passes_status=='cancel'){

	                       	$updateData['status']=$passes_status;
	                       }elseif($passes_status=='singup'|| $passes_status=='notcheckin'){
	                       		$updateData['status']='singup';
						   } elseif($passes_status=='noshow') { // part of upper
								$updateData['status']='absence';
						   }

						   $checkout_time = $time + ((int)$duration*60);
                           $updateData['checkout_time']=$checkout_time;
	                       $updateData['update_dt']=$time;
	                       $updateData['checked_by']=$usid;
						   $where3 =  array('id'=>$check_pass[0]['id']);
						   $checkId= $this->dynamic_model->updateRowWhere('user_attendance',$where3,$updateData);
						  // echo $this->db->last_query();die;
						}
					}
						if($passes_status=='singup'){
                          $msg= $this->lang->line('check_signup_succ');
						}elseif($passes_status=='checkin'){
	                      $msg= $msg1;
						}elseif($passes_status=='checkout'){
							$msg= $this->lang->line('check_out_succ');

						}elseif($passes_status=='cancel'){
							$msg= $this->lang->line('attendance_cancel_succ');
						}elseif($passes_status=='noshow'){
							$msg= $this->lang->line('attendance_absent_succ');
						}else{
							$msg= $this->lang->line('attendance_succ');
						}
						if($checkId)
				        {
							// $passes_status=get_passes_checkin_status($usid,$service_id,$service_type,$date);
							$response= array("wating_list_no"=>"$waitng_list_no");
							$arg['status']    = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
						 	$arg['message']   = $msg;
						 	$arg['data']      = $response;
				        }else{
				        	$arg['status']     = 0;
				            $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['data']       = json_decode('{}');
							$arg['message']    = $this->lang->line('server_problem');
							$arg['start_time'] = 0;
							$arg['start_time_message'] = '';
				        }

					}
				}
		    }
        }
	  echo json_encode($arg);
	}

	public function passes_status_change_demo_post()
	{
		$arg   = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
		   $userdata = checkuserid();
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
				$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
				{
				    $this->form_validation->set_rules('user_id','User Id','required|trim', array( 'user_id_required' => $this->lang->line('service_type_required')));
				    $this->form_validation->set_rules('service_type','Service Type','required|trim', array( 'required' => $this->lang->line('service_type_required')));
				    $this->form_validation->set_rules('service_id','Service Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
				    $this->form_validation->set_rules('passes_status','Service Id', 'required|trim', array( 'required' => $this->lang->line('passes_status_required')));
					if($this->form_validation->run() == FALSE)
					{
					  	$arg['status']  = 0;
					  	$arg['error_code'] = 0;
						$arg['error_line']= __line__;
					 	$arg['message'] = get_form_error($this->form_validation->error_array());
					}
					else
					{
						$lat = $this->input->get_request_header('lat');
						$lang = $this->input->get_request_header('lang');
						$room_capacity=$waitng_list_no='';
						$usid =$userdata['data']['id'];   // Instructor Id
						$updateData=$response=array();
						$time=time();
						$date = date("Y-m-d",$time);
						$user_id    = $this->input->post('user_id');
						$service_id    = $this->input->post('service_id');
						//service_type=> 1 class 2 workshop 3 trainer
						$service_type    = $this->input->post('service_type');
						// passes_status=> checkin checkout cancel
						$passes_status    = $this->input->post('passes_status');



						// $where = array('id'=>$service_id,'user_id'=>$usid,'status'=>"Active");
						$where = array('id'=>$service_id, 'status'=>"Active");
						//find capcity class or workshop

						if($service_type==1){
							$business_data= $this->dynamic_model->getdatafromtable('business_class',$where);
							$business_id = $business_data[0]['business_id'];

				        	$business_id=(!empty($business_id)) ? $business_id : 0;
				        	$room_capacity=(!empty($business_data[0]['capacity'])) ? $business_data[0]['capacity'] : 0;
							$class_days_prior_signup = $business_data[0]['class_days_prior_signup'] ? $business_data[0]['class_days_prior_signup'] : 1;
							$start_date = $business_data[0]['start_date'];
							$duration = $business_data[0]['duration'];

							/* if($passes_status == 'singup')
							{

								$start_date = strtotime($start_date);
								$unixTimestamp = $start_date - ((int)$class_days_prior_signup*24*60*60);

								$today = time();
								if($today >= $unixTimestamp){

								}else{
									$unixTimestamp = date('Y-m-d',$unixTimestamp);
								$arg['status']     = 0;
									$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line']= __line__;
									$arg['data']       = json_decode('{}');
									$arg['message']    = 'Sing up is open '.$unixTimestamp;
									echo json_encode($arg);exit;
								}

							} */


							if($passes_status == 'checkin'){
								//$today_date = date('Y-m-d');
								$where = array(
									'scheduled_date'=>$date,
									'class_id'=>$service_id,
									'business_id'=> $business_id,
								);

								$class_data = $this->dynamic_model->getdatafromtable('class_scheduling_time',$where);

								if(empty($class_data)){
									$arg['status']     = 0;
									$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line']= __line__;
									$arg['data']       = json_decode('{}');
									$arg['message']    = 'Class not available today.';

								}else{
									$from_time = $class_data[0]['from_time'];
									$from_time = $from_time - 15*60;
									$time = time();
									if($from_time > $time){
										$unixTimestamp = date('h:i:s A',$from_time);
										$arg['status']     = 0;
										$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
										$arg['error_line']= __line__;
										$arg['data']       = json_decode('{}');
										$arg['message']    = 'Cheken allow after '.$unixTimestamp;
										echo json_encode($arg);exit;
									}

								}

							}

							echo json_encode($arg); exit();

						}elseif($service_type==2){
				        $business_data= $this->dynamic_model->getdatafromtable('business_workshop',$where);
				        $business_id=(!empty($business_data[0]['business_id'])) ? $business_data[0]['business_id'] : 0;
				        $room_capacity=(!empty($business_data[0]['capacity'])) ? $business_data[0]['capacity'] : 0;
						}
						 //Not able to Access Scheduled classes or workshop
       //                  if(empty($business_data)){
				   //      	$arg['status']     = 0;
				   //          $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
							// $arg['error_line']= __line__;
							// $arg['data']       = json_decode('{}');
							// $arg['message']    = $this->lang->line('pass_status_not_change');
							//  echo json_encode($arg);exit;
       //                  }
						//location cheked
						// $location_check=$this->api_model->user_location_checked_in_studio($business_id,$lat,$lang);
						// if(empty($location_check)){
				  //       	$arg['status']     = 0;
				  //           $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
						// 	$arg['error_line']= __line__;
						// 	$arg['data']       = array();
						// 	$arg['message']    = $this->lang->line('not_eligible_class');
						// 	 echo json_encode($arg);exit;
						//     }
                        //Check same user not eligble to change checkin status
                        if($usid ==$user_id){
				        	$arg['status']     = 0;
				            $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['data']       = json_decode('{}');
							$arg['message']    = $this->lang->line('something_wrong');
							 echo json_encode($arg);exit;
                        }
						//signup process
						$condition="user_id=".$user_id." AND service_id=".$service_id;
				        $signup_check= $this->dynamic_model->getdatafromtable('user_attendance',$condition);
				        if(empty($signup_check)){
				        	if($passes_status !=='singup'){
				        		$arg['status']     = 0;
				            	$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line']= __line__;
								$arg['data']       = json_decode('{}');
								$arg['message']    = $this->lang->line('passes_status_error');
							 	echo json_encode($arg);exit;
						    }

				        	$insertData =   array(
											'user_id'  		=>$user_id,
											'status'  		=>$passes_status,
											'service_id'    =>$service_id,
											'service_type'  =>$service_type,
											'checkin_time'  =>0,
											'checkout_time' =>0,
											'signup_status' =>1,
											'checked_by'   	=>$usid,
											'create_dt'   	=>$time,
											'update_dt'   	=>$time,
											'checkin_dt'=>date('Y-m-d',$time)
						                   );

				        	$checkId= $this->dynamic_model->insertdata('user_attendance',$insertData);

				        }else{

                        //find data today wise
						$whe="user_id=".$user_id." AND service_id=".$service_id." AND service_type=".$service_type." AND DATE(FROM_UNIXTIME(create_dt))='".$date."'";
				        $check_pass= $this->dynamic_model->getdatafromtable('user_attendance',$whe);

						//check room capacity
						$where1="service_id=".$service_id." AND service_type=".$service_type." AND status='checkin' AND DATE(FROM_UNIXTIME(create_dt))='".$date."'";
				        $check_in_count= getdatacount('user_attendance',$where1);
						$getTime=strtotime(date('H:i:s',$time));
						$checkout_time = $time + ((int)$duration*60);

				        if(empty($check_pass)){
				       //$passes_status=($passes_status=='noshow') ? 'absence' : $passes_status;
						$insertData =   array(
											'user_id'  		=>$user_id,
											'status'  		=>$passes_status,
											'service_id'    =>$service_id,
											'service_type'  =>$service_type,
											'checkin_time'  =>$time,
											'checkout_time' =>$checkout_time,
											'signup_status' =>1,
											'create_dt'   	=>$time,
											'checked_by'   	=>$usid,
											'update_dt'   	=>$time,
											'checkin_dt'=>date('Y-m-d',$time)
						                   );

						$checkId= $this->dynamic_model->insertdata('user_attendance',$insertData);

							$msg1= $this->lang->line('check_in_succ');
						}elseif(!empty($check_pass ))
						{
	                       if($passes_status=='checkin'){
			                   if($room_capacity ==$check_in_count)
						       {
						       	$where2="service_id=".$service_id." AND service_type=".$service_type." AND status='waiting' AND DATE(FROM_UNIXTIME(create_dt))='".$date."'";
						       	$wating_data= $this->dynamic_model->getdatafromtable('user_attendance',$where2);
                                //if already waiting list
						       	$where3="user_id=".$user_id." AND service_id=".$service_id." AND service_type=".$service_type." AND status='waiting' AND DATE(FROM_UNIXTIME(create_dt))='".$date."'";
						       	$already_wating_data= $this->dynamic_model->getdatafromtable('user_attendance',$where3);
						       //	print_r($wating_data);die;
						       	 $already_waiting_no= (!empty($already_wating_data[0]['waitng_list_no'])) ? $already_wating_data[0]['waitng_list_no'] : 0;
						       	 $waiting_no= (!empty($wating_data[0]['waitng_list_no'])) ? $wating_data[0]['waitng_list_no'] : 0;
						       	if(empty($waiting_no)){
						       		$waitng_list_no=1;
						       	}elseif(!empty($already_wating_data)){
						       		$waitng_list_no=$already_waiting_no;
						       	}
						       	else{
						       		$waitng_list_no=$waiting_no+1;
						       	}
						       //echo $waitng_list_no;die;
						       	$updateData['status']='waiting';
						        $updateData['waitng_list_no']=$waitng_list_no;

						       	$msg1= $this->lang->line('waiting_msg');
		                       }else{
		                       	$updateData['status']=$passes_status;
		                       	$updateData['checkin_time']=$getTime;
		                       	$msg1= $this->lang->line('check_in_succ');
		                       }
	                       }elseif($passes_status=='checkout'){
	                       	$updateData['status']=$passes_status;
	                        $updateData['checkout_time']=$getTime;
	                       }elseif($passes_status=='cancel'){

	                       	$updateData['status']=$passes_status;
	                       }elseif($passes_status=='singup'|| $passes_status=='notcheckin'){
	                       		$updateData['status']='singup';
						   } elseif($passes_status=='noshow') { // part of upper
								$updateData['status']='absence';
						   }

						   $checkout_time = $time + ((int)$duration*60);
                           $updateData['checkout_time']=$checkout_time;
	                       $updateData['update_dt']=$time;
	                       $updateData['checked_by']=$usid;
						   $where3 =  array('id'=>$check_pass[0]['id']);
						   $checkId= $this->dynamic_model->updateRowWhere('user_attendance',$where3,$updateData);
						  // echo $this->db->last_query();die;
						}
					}
						if($passes_status=='singup'){
                          $msg= $this->lang->line('check_signup_succ');
						}elseif($passes_status=='checkin'){
	                      $msg= $msg1;
						}elseif($passes_status=='checkout'){
							$msg= $this->lang->line('check_out_succ');

						}elseif($passes_status=='cancel'){
							$msg= $this->lang->line('attendance_cancel_succ');
						}elseif($passes_status=='noshow'){
							$msg= $this->lang->line('attendance_absent_succ');
						}else{
							$msg= $this->lang->line('attendance_succ');
						}
						if($checkId)
				        {
							// $passes_status=get_passes_checkin_status($usid,$service_id,$service_type,$date);
							$response= array("wating_list_no"=>"$waitng_list_no");
							$arg['status']    = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
						 	$arg['message']   = $msg;
						 	$arg['data']      = $response;
				        }else{
				        	$arg['status']     = 0;
				            $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['data']       = json_decode('{}');
							$arg['message']    = $this->lang->line('server_problem');
				        }

					}
				}
		    }
        }
	  echo json_encode($arg);
    }
	/****************Function Get workshop list**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : workshop_list
     * @description     : list of workshop
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function workshop_list_old_post()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = checkuserid();
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
		      $_POST = json_decode(file_get_contents("php://input"), true);
			  if($_POST)
			  {
			    $this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
			     $this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
				if($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$response=array();
					$time=time();
					$todaydate = date("Y-m-d",$time);
					$usid =$userdata['data']['id'];
					$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
					$page_no= $page_no-1;
					$limit    = config_item('page_data_limit');
					$offset = $limit * $page_no;
					$business_id=  $this->input->post('business_id');
					$upcoming_date=  strtotime($this->input->post('upcoming_date'));
					// 0=all workshop 1=singed workshop
					$workshop_status=  $this->input->post('workshop_status');
					  if($workshop_status==0){
						if(!empty($upcoming_date)){
							  $date = date("Y-m-d",$upcoming_date);
	                       //comment by tarun
	                       // $where="business_id=".$business_id." AND status='Active' AND DATE(FROM_UNIXTIME(start_date))='".$date."'";
							  $where="business_id=".$business_id." AND status='Active' AND start_date='".$date."'";
						}else{

	                         //$where="business_id=".$business_id." AND status='Active' AND DATE(FROM_UNIXTIME(start_date))>='".$todaydate."' AND DATE(FROM_UNIXTIME(end_date))<='".$todaydate."'";
							//comment by tarun
	                        // $where="business_id=".$business_id." AND DATE(FROM_UNIXTIME(end_date))>='".$todaydate."'";
	                        $where="business_id=".$business_id." AND end_date>='".$todaydate."'";
						}
					    $workshop_data = $this->dynamic_model->getdatafromtable('business_workshop',$where,"*",$limit,$offset,'create_dt');
				     }elseif($workshop_status==1){
					     $workshop_data = $this->instructor_model->get_my_workshops($business_id,$upcoming_date,$limit,$offset,'',$usid);
				     }else{
				     	if(!empty($upcoming_date)){
							  $date = date("Y-m-d",$upcoming_date);
							  //comment by tarun
	                        //$where="business_id=".$business_id." AND status='Active' AND is_cancel='1' AND DATE(FROM_UNIXTIME(start_date))='".$date."'";
							  $where="business_id=".$business_id." AND status='Active' AND is_cancel='1' AND start_date='".$date."'";
						}else{
							//comment by tarun

	                        //$where="business_id=".$business_id." AND status='Active' AND DATE(FROM_UNIXTIME(end_date))>='".$todaydate."' AND is_cancel='1'" ;
	                        $where="business_id=".$business_id." AND status='Active' AND end_date>='".$todaydate."' AND is_cancel='1'" ;
						}
					   $workshop_data = $this->dynamic_model->getdatafromtable('business_workshop',$where,"*",$limit,$offset,'create_dt');
				     }
					//print_r($workshop_data);die;
					if(!empty($workshop_data)){
					    foreach($workshop_data as $value)
			            {
			            	$workshopdata['workshop_id']  = $value['id'];
			            	$workshopdata['workshop_name']= ucwords($value['workshop_name']);
			            	$workshopdata['from_time']    = $value['from_time'];
			            	$workshopdata['to_time']      = $value['to_time'];
			            	$workshopdata['from_time_utc'] = $value['from_time'];
			            	$workshopdata['to_time_utc']  = $value['to_time'];
			            	$workshopdata['duration']     = $value['duration'];
			            	$capicty_used                 = get_checkin_class_or_workshop_count($value['id'],2,$time);
			            	$workshopdata['total_capacity']    = $value['capacity'];
			            	$workshopdata['capacity_used']     = $capicty_used;
			            	$status= get_passes_checkin_status($usid,$value['id'],2,$todaydate);
			            	if($status=='singup' OR $status=='checkin'OR $status=='checkout'){
			            		$signed_status='1';
			            	}else{
			            		$signed_status='0';
			            	}
			            	$workshopdata['signed']= '0';
			            	$workshopdata['signed_status']= $signed_status;
			            	$workshopdata['location']     = $value['location'];
			            	$workshopdata['workshop_type']= get_categories($value['workshop_type']);
			            	$instructor_data             = $this->instructor_list_details($business_id,2,$value['id']);
			            	 $workshopdata['instructor_details']= !empty($instructor_data[0]) ? $instructor_data[0] : json_decode('{}');
			            	$workshopdata['create_dt']    = date("d M Y ",$value['create_dt']);
			            	$workshopdata['start_date']    = date("d M Y ",strtotime($value['start_date']));
			            	$workshopdata['end_date']    = date("d M Y ",strtotime($value['start_date']));
			            	$workshopdata['create_dt_utc'] = $value['create_dt'];
			            	$workshopdata['start_date_utc']= strtotime($value['start_date']);
			            	$workshopdata['end_date_utc']= strtotime($value['end_date']);
			            	$response[]	                  = $workshopdata;
			            }
						$arg['status']     = 1;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = $response;
						$arg['message']    = $this->lang->line('record_found');
					}else{
						$arg['status']     = 0;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = array();
					 	$arg['message']    = $this->lang->line('record_not_found');
					}
			    }
			  }
			}
		}
	   echo json_encode($arg);
	}

	public function workshop_list_post()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = checkuserid('1');
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
		      $_POST = json_decode(file_get_contents("php://input"), true);
			  if($_POST)
			  {
			    $this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
			     $this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
				if($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$time_zone =  $this->input->get_request_header('Timezone', true);
                    $time_zone =  $time_zone ? $time_zone : 'UTC';
					date_default_timezone_set($time_zone);

					$response=array();
					$time=time();
					$usid =$userdata['data']['id'];
					$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
					$page_no= $page_no-1;
					$limit    = config_item('page_data_limit');
					$offset = $limit * $page_no;
					$business_id=  $this->input->post('business_id');
					$upcoming_date=  intval($this->input->post('upcoming_date'));
					if ($upcoming_date <= 0 ) {
						$upcoming_date = '';
					}

					/*
					 0 = instractor future workshop
					 1 = my classes History  less then < today
					 2 Cancelled
					 3 = all workshop
					 */



					$workshop_status=  $this->input->post('workshop_status');
                    $imgPath = base_url().'uploads/user/';

                    if($workshop_status == '0'){
                        $query = "SELECT business_workshop_master.id as workshop_id, business_workshop_schdule.id as schedule_id, business_workshop_master.name as workshop_name, business_workshop_master.workshop_capacity, business_workshop_master.price as workshop_price, business_workshop_schdule.*, CASE WHEN business_location.location_name IS NULL THEN '' Else business_location.location_name END as location, CASE WHEN business_location.location_url IS NULL THEN '' Else business_location.location_url END as location_url FROM `business_workshop_schdule` JOIN business_workshop_master on (business_workshop_master.id = business_workshop_schdule.workshop_id) JOIN business_workshop_schdule_instructor si ON si.schedule_id = business_workshop_schdule.id  LEFT JOIN business_location on (business_location.id = business_workshop_schdule.location) WHERE business_workshop_master.business_id = ".$business_id." AND business_workshop_schdule.status = 'Active'";

						if(!empty($upcoming_date)){
							$searchDate = date("Y-m-d", $upcoming_date);
							$query .= " AND business_workshop_schdule.schedule_dates = '".$searchDate."' ";
						}
						$searchDate = time();
						$query .= " AND si.user_id = '".$usid."' AND business_workshop_schdule.end >= '".$searchDate."' ";
                        $query .= "  ORDER BY start ASC limit $offset,$limit";
					    $workshop_data = $this->db->query($query)->result_array();
				    }else if($workshop_status == '3'){
                        $query = "SELECT business_workshop_master.id as workshop_id, business_workshop_schdule.id as schedule_id, business_workshop_master.name as workshop_name, business_workshop_master.workshop_capacity, business_workshop_master.price as workshop_price, business_workshop_schdule.*, CASE WHEN business_location.location_name IS NULL THEN '' Else business_location.location_name END as location, CASE WHEN business_location.location_url IS NULL THEN '' Else business_location.location_url END as location_url FROM `business_workshop_schdule` JOIN business_workshop_master on (business_workshop_master.id = business_workshop_schdule.workshop_id) LEFT JOIN business_location on (business_location.id = business_workshop_schdule.location) WHERE business_workshop_master.business_id = ".$business_id." AND business_workshop_schdule.status = 'Active'";

						if(!empty($upcoming_date)){
							$searchDate = date("Y-m-d", $upcoming_date);
							$query .= " AND business_workshop_schdule.schedule_dates = '".$searchDate."' ";
						}
						$searchDate = time();
						$query .= " AND business_workshop_schdule.end >= '".$searchDate."' ";
                        $query .= "  ORDER BY start ASC limit $offset,$limit";
					    $workshop_data = $this->db->query($query)->result_array();
				    }else if ($workshop_status == '1') {
                        $query = "SELECT business_workshop_master.id as workshop_id, business_workshop_schdule.id as schedule_id, business_workshop_master.name as workshop_name, business_workshop_master.workshop_capacity, business_workshop_master.price as workshop_price, business_workshop_schdule.*, CASE WHEN business_location.location_name IS NULL THEN '' Else business_location.location_name END as location, CASE WHEN business_location.location_url IS NULL THEN '' Else business_location.location_url END as location_url FROM `business_workshop_schdule` JOIN business_workshop_master on (business_workshop_master.id = business_workshop_schdule.workshop_id) LEFT JOIN business_location on (business_location.id = business_workshop_schdule.location) WHERE business_workshop_master.business_id = ".$business_id." AND business_workshop_schdule.status = 'Active'";
						$customer_date = date('Y-m-d');
						if (!empty($upcoming_date)) {
							$customer_date = date('Y-m-d', $upcoming_date);
							$query .= " AND business_workshop_schdule.schedule_dates = '".$customer_date."' ";
						} else {
							$query .= " AND business_workshop_schdule.schedule_dates < '".$customer_date."' ";
						}

                        $query .= "  ORDER BY schedule_date DESC limit $offset,$limit";
                        $workshop_data = $this->db->query($query)->result_array();

                    } else if($workshop_status == '2'){
                        $query = "SELECT business_workshop_master.id as workshop_id, business_workshop_schdule.id as schedule_id, business_workshop_master.name as workshop_name, business_workshop_master.workshop_capacity, business_workshop_master.price as workshop_price, business_workshop_schdule.*, CASE WHEN business_location.location_name IS NULL THEN '' Else business_location.location_name END as location, CASE WHEN business_location.location_url IS NULL THEN '' Else business_location.location_url END as location_url FROM `business_workshop_schdule` JOIN business_workshop_master on (business_workshop_master.id = business_workshop_schdule.workshop_id) LEFT JOIN business_location on (business_location.id = business_workshop_schdule.location) WHERE business_workshop_master.business_id = ".$business_id." AND business_workshop_schdule.status = 'Cancel'";

						if(!empty($upcoming_date)){
							$searchDate = date("Y-m-d", $upcoming_date);
							$query .= " AND business_workshop_schdule.schedule_dates = '".$searchDate."' ";
						}
                        $query .= "  ORDER BY schedule_date DESC limit $offset,$limit";
					    $workshop_data = $this->db->query($query)->result_array();
				    } else {
					    $workshop_data = '';
					}

					if(!empty($workshop_data)){
					    foreach($workshop_data as $value)
			            {
                            $workshopdata = $value;
                            $workshopdata['total_capacity'] = $value['workshop_capacity'];
                            unset($workshopdata['capacity']);
                            $workshopdata['capacity_used']     = $this->db->get_where('user_booking', array(
                               'service_type' => '4',
                                'service_id' => $value['workshop_id'],
                                'status' => 'Success'
                            ))->num_rows();

                            $workshopdata['instructor_details'] = [];
			            	$response[]	= $workshopdata;
			            }
						$arg['status']     = 1;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = $response;
						$arg['message']    = $this->lang->line('record_found');
					}else{
						$arg['status']     = 0;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = array();
					 	$arg['message']    = $this->lang->line('record_not_found');
					}
			    }
			  }
			}
		}
	   echo json_encode($arg);
	}

	 public function business_workshop_details_post()
    {
        $arg = array();
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
        }
        else
        {
            $userdata = checkuserid();
            if($userdata['status'] != 1){
                $arg = $userdata;
            }
            else
            {
                $_POST = json_decode(file_get_contents("php://input"), true);
                if($_POST)
                {
                    $this->form_validation->set_rules('business_id','Business_id Id', 'required|trim', array( 'required' => 'Business_id id is required'));
                    if($this->form_validation->run() == FALSE)
                    {
                        $arg['status']  = 0;
                        $arg['error_code'] = 0;
                        $arg['error_line']= __line__;
                        $arg['message'] = get_form_error($this->form_validation->error_array());
                    }
                    else
                    {
                        $user_id = $userdata['data']['id'];
                        $business_id =  $this->input->post('business_id');
                        $workshop_id = $this->input->post('workshop_id');

                        if ($this->input->post('schedule_id')) {
                            $schedule_id = $this->input->post('schedule_id');
                            $workshop_result = get_all_workshop_schedule($business_id,$workshop_id, $schedule_id, $user_id);

                        } else {
                            $workshop_result = get_all_workshop($business_id,$workshop_id);
                        }

                        if($workshop_result)
                        {
                            $arg['status']      = 1;
                            $arg['error_code']  = REST_Controller::HTTP_OK;
                            $arg['error_line']  = __line__;
                            $arg['message']     = '';
                            $arg['data']      = $workshop_result;
                        }else{
                            $arg['status']     = 0;
                            $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
                            $arg['error_line']= __line__;
                            $arg['message']    = 'Not found workshop.';
                        }

                    }
                }
            }
        }
        echo json_encode($arg);
    }

	/****************Function Get workshop details************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : workshop_details
     * @description     : Wokshop details
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function workshop_details_post()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = checkuserid();
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
		      $_POST = json_decode(file_get_contents("php://input"), true);
			  if($_POST)
			  {
			    $this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
			    $this->form_validation->set_rules('workshop_id','Workshop Id', 'required|trim', array( 'required' => $this->lang->line('workshop_id_required')));
			    $this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
				if($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$time=time();
					$usid =$userdata['data']['id'];
					$date = date("Y-m-d",$time);
					$response=$customerData=array();
					$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
					$page_no= $page_no-1;
					$limit    = config_item('page_data_limit');
					$offset = $limit * $page_no;

					$workshop_id=  $this->input->post('workshop_id');
					$business_id=  $this->input->post('business_id');
					$customer_type=  $this->input->post('customer_type');
					$checkedin_type=  $this->input->post('checkedin_type');
					//status class complete or cancel
					$status=  $this->input->post('status');

                    $where=array("id"=>$workshop_id,"business_id"=>$business_id,"status"=>"Active");
					$workshop_data = $this->dynamic_model->getdatafromtable('business_workshop',$where,"*",'','','create_dt');
					if(!empty($workshop_data)){

		            	$workshopdata['workshop_id']     = $workshop_data[0]['id'];
		            	$workshopdata['workshop_name']   = ucwords($workshop_data[0]['workshop_name']);
		            	$workshopdata['from_time']    = $workshop_data[0]['from_time'];
		            	$workshopdata['to_time']      = $workshop_data[0]['to_time'];
		            	$workshopdata['duration']     = $workshop_data[0]['duration'].' minutes';
		            	$workshopdata['from_time_utc']    = $workshop_data[0]['from_time'];
		            	$workshopdata['to_time_utc']      =$workshop_data[0]['to_time'];
		            	$workshopdata['start_date']      =date("d M Y ",strtotime($workshop_data[0]['start_date']));
		            	$workshopdata['end_date']      =date("d M Y ",strtotime($workshop_data[0]['end_date']));
		            	$workshopdata['start_date_utc']      =strtotime($workshop_data[0]['start_date']);
		            	$workshopdata['end_date_utc']      =strtotime($workshop_data[0]['end_date']);
		            	$capicty_used                 = get_checkin_class_or_workshop_count($workshop_data[0]['id'],2,$time);
			            	$workshopdata['total_capacity']    = $workshop_data[0]['capacity'];
			            	$workshopdata['capacity_used']     = $capicty_used;
		            	//$capicty_used = get_checkin_class_or_workshop_count($workshop_data[0]['id'],2,$time);
			            $workshopdata['capacity']     = $capicty_used.'/'.$workshop_data[0]['capacity'];
			            $workshopdata['timeframe']     = get_daywise_instructor_data($workshop_data[0]['id'],2,$business_id);
		            	$workshopdata['location']     = $workshop_data[0]['location'];
		            	$workshopdata['workshop_type']   = get_categories($workshop_data[0]['workshop_type']);
		            	if($workshop_data[0]['end_date']>=$time){
		            	  $workshopdata['status']="Complete";
		            	}else{
		            		$workshopdata['status']="Inprogress";
		            	}
		            	$workshopdata['create_dt']    = date("d M Y ",$workshop_data[0]['create_dt']);
		            	$workshopdata['create_dt_utc']    = $workshop_data[0]['create_dt'];
                       $customer_details=$this->instructor_model->get_all_signed_workshops($business_id,$workshop_data[0]['id'],$date,$customer_type,$checkedin_type,$usid,$limit,$offset);
                       //print_r($customer_details);die;
                       if(!empty($customer_details)){
                       	foreach($customer_details as $value){
                       	$customerdata['id']     = $value['user_id'];
		            	$customerdata['name']   = ucwords($value['name']);
		            	$customerdata['lastname'] = ucwords($value['lastname']);
		            	$customerdata['gender'] = $value['gender'];
		            	$customerdata['date_of_birth'] =!empty($value['date_of_birth']) ? $value['date_of_birth'] : '';
		            	$customerdata['profile_img']= base_url().'uploads/user/'.$value['profile_img'];
		            	$customerdata['business_id']=$value['business_id'];
		            	//get pass Id
		            	$con=array("service_id"=>$workshop_data[0]['id'],"service_type"=>2);
				        $pass_data = $this->dynamic_model->getdatafromtable('business_passes',$con);
                        $pass_id=!empty($pass_data[0]['pass_id']) ? $pass_data[0]['pass_id'] :'';
		            	$customerdata['pass_purchase_id']="$pass_id";

                        $con1="service_id='".$workshop_id."' AND service_type='2' AND user_id='".$value['user_id']."' AND DATE(FROM_UNIXTIME(user_attendance.create_dt))='".$date."'";
				        $user_attendance = $this->dynamic_model->getdatafromtable('user_attendance',$con1,'','1','0','update_dt','DESC');
		                //signup condition check
			        	if($user_attendance[0]['status']=='checkin'){
                             //2 checked in
			        	     $workshop_status= '2';
			        	}
			        	elseif($user_attendance[0]['status']=='checkout'){
                         //3 checkout
                         $workshop_status= '3';
			        	}
			           elseif(!empty($user_attendance[0]['waitng_list_no'])){
                         //4 waiting
                         $workshop_status= '4';
			        	}elseif($user_attendance[0]['status']=='cancel'){
                         //5 cancel
                         $workshop_status= '5';
			        	}else{
			        		//check in 1
			        		$workshop_status='1';//check in
			        	}
                        $customerdata['workshop_status']= $workshop_status;
		            	$customerData[] =$customerdata;
                       	}
                       }
                       $workshopdata['customer_details'] =$customerData;
		            	$response= $workshopdata;

						$arg['status']     = 1;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = $response;
						$arg['message']    = $this->lang->line('record_found');
					}else{
						$arg['status']     = 0;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = json_decode('{}');
					 	$arg['message']    = $this->lang->line('record_not_found');
					}
			    }
			  }
			}
		}
	   echo json_encode($arg);
	}
	/****************Function instructor schedule*****************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : instructor_schedule
     * @description     : instructor schedule
     * @param           : null
     * @return          : null
     * ********************************************************** */
    public function instructor_schedule_post()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = checkuserid();
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
		      $_POST = json_decode(file_get_contents("php://input"), true);
			  if($_POST)
			  {
			    $this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
			    $this->form_validation->set_rules('type', 'Type', 'required|numeric',array(
						'required' => $this->lang->line('type_req'),
						'numeric' => $this->lang->line('type_numeric'),
					));
				if($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$response=array();
					$time=time();
					$usid =$userdata['data']['id'];
					$business_id=  $this->input->post('business_id');
					$type=  $this->input->post('type');
					$schedule_info=  $this->input->post('schedule_info');
					//echo "<pre>";print_r($schedule_info);exit;
					if(!empty($schedule_info)){
					 foreach($schedule_info as $key => $schedule_value){

                       $schedule_data = $this->dynamic_model->getdatafromtable('instructor_schedule');
					 	echo $schedule_data[0]['start_date'].'=='.$schedule_value['schedule_date'];
					 	if($schedule_data[0]['start_date']==$schedule_value['schedule_date']){
					 		echo "hi";

					 	}
				 	   $schedule_data = array(
										   	'user_id'   	 => $usid,
										    'business_id'    => $business_id,
										    'type'           => $type,
										    'status'         => "Active",
										    'start_date'     =>  $schedule_value['schedule_date'],
										    'from_time'   	 =>  $schedule_value['from_time'],
										    'to_time'   	 =>  $schedule_value['to_time'],
										    'start_date_utc' =>  strtotime($schedule_value['schedule_date']),
										    'from_time_utc'  =>  strtotime($schedule_value['from_time']),
										    'to_time_utc'    =>  strtotime($schedule_value['to_time']),
										    'created_at'     => $time,
										    'updated_at'     => $time
									);
							// $instructor_schedule = $this->dynamic_model->insertdata('instructor_schedule',$schedule_data);
							$instructor_schedule =1;
					 }
						if($instructor_schedule){
							$arg['status']     = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data']       = json_decode('{}');
							$arg['message']    = $this->lang->line('schedule_info_succ');
						}else{
							$arg['status']     = 0;
							$arg['error_code'] = ERROR_FAILED_CODE;
							$arg['error_line'] = __line__;
							$arg['data']       = json_decode('{}');
						 	$arg['message']    = $this->lang->line('server_problem');
						}
				    }else{
						$arg['status']     = 0;
						$arg['error_code'] = ERROR_FAILED_CODE;
						$arg['error_line'] = __line__;
						$arg['data']       = json_decode('{}');
					 	$arg['message']    = $this->lang->line('schedule_info_req');
					}
			    }
			  }
			}
		}
	   echo json_encode($arg);
	}
	/****************Function my_studio_list*********************
     * @type            : Function
     * @Author          : Aamir
     * @function name   : my_studio_list
     * @description     : My studio List
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function my_studio_list_post()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = checkuserid();
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
		      $_POST = json_decode(file_get_contents("php://input"), true);
			  if($_POST)
			  {
			    $this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
				if($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$response=array();
					$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
					$page_no= $page_no-1;
					$limit    = config_item('page_data_limit');
					$offset = $limit * $page_no;

					$usid =$userdata['data']['id'];
					$lat=$userdata['data']['lat'];
		    		$lang=$userdata['data']['lang'];

					$lat = !empty($_POST['lat']) ? $_POST['lat'] : $lat;
					$lang = !empty($_POST['lang']) ? $_POST['lang'] : $lang;

					$studio_data = $this->instructor_model->get_my_studios($usid,$limit,$offset,$lat,$lang);
					if(!empty($studio_data)){
					    foreach($studio_data as $value)
			            {

			            	$studiodata['business_id']  = $value['id'];
			            	$studiodata['business_name']= ucwords($value['business_name']);
			            	$studiodata['email']= $value['primary_email'];
			            	$studiodata['address']= $value['address'];
			            	$studiodata['city']= $value['city'];
			            	$studiodata['state']= $value['state'];
			            	$studiodata['country']= $value['country'];
			            	$studiodata['business_phone']= $value['business_phone'];
			            	$studiodata['skills']     = get_categories($value['category']);
			            	$img = site_url().'uploads/business/'.$value['logo'];
							$imgname = pathinfo($img, PATHINFO_FILENAME);
							$ext = pathinfo($img, PATHINFO_EXTENSION);
							$thumb = site_url().'uploads/business/'.$imgname.'_thumb.'.$ext;

							$busi_img = site_url().'uploads/business/'.$value['business_image'];
							$imgnamebusi = pathinfo($busi_img, PATHINFO_FILENAME);
							$extbusi = pathinfo($busi_img, PATHINFO_EXTENSION);
							$thumb_img = site_url().'uploads/business/'.$imgnamebusi.'_thumb.'.$extbusi;
			            	$studiodata['logo']     = $img;
			            	$studiodata['thumb']     = $thumb;
			            	$studiodata['business_thumb']= $thumb_img;

			            	$business_lat  = $value['lat'];
			            	$business_long = $value['longitude'];

			            	$distance= $value['distance'] ? $value['distance']: '0';
			            	$distance = $distance * 1.609;
							$distance = round($distance, 2);
							$distance = $distance.' Km';

				            //Check my favourite status
				            //service_type 1 for business
				            $where=array("user_id"=>$usid,"service_id"=>$value['id'],"service_type"=>1);
							$user_favourite= $this->dynamic_model->getdatafromtable("user_business_favourite",$where);
						    $favourite= (!empty($user_favourite)) ? '1' : '0';
			            	$studiodata['distance'] = $distance;
			            	$studiodata['favourite']= $favourite;
			            	$studiodata['latitude']= $value['lat'];
			            	$studiodata['longitude']= $value['longitude'];
			            	$response[]	            = $studiodata;
			            }
						$arg['status']     = 1;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = $response;
						$arg['message']    = $this->lang->line('record_found');
					}else{
						$arg['status']     = 0;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = array();
					 	$arg['message']    = $this->lang->line('record_not_found');
					}
			    }
			  }
			}
		}
	   echo json_encode($arg);
	}
	public function my_studio_list_old_post()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = checkuserid();
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
		      $_POST = json_decode(file_get_contents("php://input"), true);
			  if($_POST)
			  {
			    $this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
				if($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$response=array();
					$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
					$page_no= $page_no-1;
					$limit    = config_item('page_data_limit');
					$offset = $limit * $page_no;
					$usid =$userdata['data']['id'];
					$studio_data = $this->instructor_model->get_my_studios($usid,$limit,$offset);
					if(!empty($studio_data)){
					    foreach($studio_data as $value)
			            {

			            	$studiodata['business_id']  = $value['id'];
			            	$studiodata['business_name']= ucwords($value['business_name']);
			            	$studiodata['email']= $value['primary_email'];
			            	$studiodata['address']= $value['address'];
			            	$studiodata['city']= $value['city'];
			            	$studiodata['state']= $value['state'];
			            	$studiodata['country']= $value['country'];
			            	$studiodata['business_phone']= $value['business_phone'];
			            	$studiodata['skills']     = get_categories($value['category']);
			            	$img = site_url().'uploads/business/'.$value['logo'];
							$imgname = pathinfo($img, PATHINFO_FILENAME);
							$ext = pathinfo($img, PATHINFO_EXTENSION);
							$thumb = site_url().'uploads/business/'.$imgname.'_thumb.'.$ext;

							$busi_img = site_url().'uploads/business/'.$value['business_image'];
							$imgnamebusi = pathinfo($busi_img, PATHINFO_FILENAME);
							$extbusi = pathinfo($busi_img, PATHINFO_EXTENSION);
							$thumb_img = site_url().'uploads/business/'.$imgnamebusi.'_thumb.'.$extbusi;
			            	$studiodata['logo']     = $img;
			            	$studiodata['thumb']     = $thumb;
			            	$studiodata['business_thumb']= $thumb_img;
			            	$distance= '0 Km';
				            //Check my favourite status
				            //service_type 1 for business
				            $where=array("user_id"=>$usid,"service_id"=>$value['id'],"service_type"=>1);
							$user_favourite= $this->dynamic_model->getdatafromtable("user_business_favourite",$where);
						    $favourite= (!empty($user_favourite)) ? '1' : '0';
			            	$studiodata['distance'] = $distance;
			            	$studiodata['favourite']= $favourite;
			            	$studiodata['latitude']= $value['lat'];
			            	$studiodata['longitude']= $value['longitude'];
			            	$response[]	            = $studiodata;
			            }
						$arg['status']     = 1;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = $response;
						$arg['message']    = $this->lang->line('record_found');
					}else{
						$arg['status']     = 0;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = array();
					 	$arg['message']    = $this->lang->line('record_not_found');
					}
			    }
			  }
			}
		}
	   echo json_encode($arg);
	}
	/****************Function service appointment details**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : service_appointment_list
     * @description     : service appointment details
     * @param           : null
     * @return          : null
     * ********************************************************** */
    public function appointment_list_post_old()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = checkuserid('1');
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
		      $_POST = json_decode(file_get_contents("php://input"), true);
			  if($_POST)
			  {
			    $this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
			     $this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
				if($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$response=array();
					$time=time();
					$date = date("Y-m-d",$time);
					$usid =$userdata['data']['id'];
					$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
					$page_no= $page_no-1;
					//$limit    = config_item('page_data_limit');
					$limit    = 5;
					$offset = $limit * $page_no;
					//appointment_status 0= all appointment 1=my appointment 2 = cancelled appointment
					$appointment_status=  $this->input->post('appointment_status');
					$business_id=  $this->input->post('business_id');
					$upcoming_date=  strtotime($this->input->post('upcoming_date'));

					$user_data = $this->dynamic_model->getdatafromtable('user',array("role_id"=>3,"status"=>"Active"),"*",$limit,$offset,'create_dt');

					//print_r($class_data);die;
					if(!empty($user_data)){
					    foreach($user_data as $value)
			            {
					        $udata['id']     = $value['id'];
			            	$udata['name']   = ucwords($value['name']);
			            	$udata['lastname']= ucwords($value['lastname']);
			            	$udata['profile_img'] = base_url()."uploads/user/".$value['profile_img'];
			            	$udata['gender'] =$value['gender'];
			            	$udata['date_of_birth'] =$value['date_of_birth'];
			            	$udata['skill']    = "Spa Parlour";
			            	 $instructor_data             = $this->instructor_list_details($business_id,3,$value['id']);
			            	 $udata['instructor_details']= !empty($instructor_data[0]) ? $instructor_data[0] : json_decode('{}');
			            	$udata['appointment_date'] = "2020-04-28";
			            	$udata['appointment_from_time'] = "02:00 PM";
			            	$udata['appointment_to_time'] = "04:00 PM";
			            	$udata['appointment_date_utc'] = "1588070681";
			            	$udata['appointment_from_time_utc'] = "1582293600";
			            	$udata['appointment_to_time_utc'] = "1582128000";

			            	$response[]	                 = $udata;
			            }
						$arg['status']     = 1;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = $response;
						$arg['message']    = $this->lang->line('record_found');
					}else{
						$arg['status']     = 0;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = array();
					 	$arg['message']    = $this->lang->line('record_not_found');
					}
			    }
			  }
			}
		}
	   echo json_encode($arg);
	}

	 /****************Function Add Client**********************************
     * @type            : Function
     * @Author          : Aamir
     * @function name   : register
     * @description     : Registeration for new user,
     					  send email verificication link and
     					  otp on register mobile number.
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function add_client_post()
    {
        $arg   = array();
         $_POST = json_decode(file_get_contents("php://input"), true);
        if($_POST){
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 ) {
            $arg = $version_result;
        }else{
		$userdata = checkuserid('2');
		if($userdata['status'] != 1){
			$arg = $userdata;
		}else{
                $this->form_validation->set_rules('name','Name', 'required|trim', array( 'required' => $this->lang->line('first_name')));
                $this->form_validation->set_rules('lastname','Last Name', 'required|trim', array( 'required' => $this->lang->line('last_name')));
                $this->form_validation->set_rules('email', 'Email', 'required|valid_email' , array('required' => $this->lang->line('email_required'),'valid_email' => $this->lang->line('email_valid')
				 ));
				$this->form_validation->set_rules('mobile', 'Mobile', 'required|min_length[8]|max_length[20]|numeric', array(
						'required' => $this->lang->line('mobile_required'),
						'min_length' => $this->lang->line('mobile_min_length'),
						'max_length' => $this->lang->line('mobile_max_length'),
						'numeric' => $this->lang->line('mobile_numeric')
					));
				//$this->form_validation->set_rules('date_of_birth','DOB', 'required', array( 'required' => $this->lang->line('dob_required')));
				// $this->form_validation->set_rules('gender','Select gender', 'required', array( 'required' => $this->lang->line('gender_required')));
                if($this->form_validation->run() == FALSE){
                    $arg['status']  = 0;
                    $arg['error_code'] = 0;
                    $arg['error_line']= __line__;
                    $arg['message'] = get_form_error($this->form_validation->error_array());
                }else{
                    $usid   = $userdata['data']['id'];
                    $gender = $this->input->post('gender');
                    //$type   = $this->input->post('type');
                    $name   = $this->input->post('name');
                    $lastname = $this->input->post('lastname');
                    $email  = $this->input->post('email');
                    // $date_of_birth = $this->input->post('date_of_birth');
                    $country_code = $this->input->post('country_code');
                    $mobile = $this->input->post('mobile');

					$where = array('email' => $email);
					//$result = $this->dynamic_model->getdatafromtable('user',$where);
					//role 3 custumer or 4 instructor
					$result = $this->dynamic_model->check_user_role($email,3,1,4);
                    //print_r($result);die;
                    if(!empty($result)){
                    $arg['status']    = 0;
                    $arg['error_code'] = REST_Controller::HTTP_OK;
                    $arg['error_line']= __line__;
                    $arg['message']   = $this->lang->line('already_register');
                    $arg['data']      = '';
                    }else{
                    $notification = '{"app_notification":"1","alerts":"1","email":"1","sms":"1","phonecall":"1"}';
                    $time=time();
                    $uniquemail   = getuniquenumber();
                    $uniquemobile   = rand(0001,9999);
                    $image = 'userdefault.png';
                    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890123456789!@#$&*!@#$&*!@#$&*";
    				$password = substr(str_shuffle( $chars ),0,14);
					$hashed_password = encrypt_password($password);
					$referred_by   = $this->input->post('referred_by');
					$discount     = $this->input->post('discount');
					$mobile_otp = '0';
					$this->load->library('user_agent');
					if ($this->agent->is_browser())
					{
						$mobile_otp = '1';
					}
                    $userdata = array(
						'name'=>$name,
						'lastname'=>$lastname,
						'password'=>$hashed_password,
						'email'=>$email,
						'mobile'=>$mobile,
						'profile_img'=>$image,
						'status'=>'Deactive',
						//'gender'=>$gender,
						//'date_of_birth'=>$date_of_birth,
						'singup_for'=>'me',
						'email_verified'=>'0',
						'mobile_verified'=>$mobile_otp,
						'mobile_otp'=>$uniquemobile,
						'mobile_otp_date'=>$time,
						'country_code'=>$country_code,
						'referred_by'=> $referred_by,
						'discount'=>$discount,
						'create_dt'=>$time,
						'update_dt'=>$time,
						'notification'=>$notification,
						'created_by'=>$usid);
                        $newuserid = $this->dynamic_model->insertdata('user',$userdata);
                        if($newuserid) {
                        	 $roledata = array(
                                'user_id'=>$newuserid,
                                'role_id'=>3,
                                'create_dt'=>$time,
                                'update_dt'=>$time
                            );
                            $roleid = $this->dynamic_model->insertdata('user_role',$roledata);
                            $where = array('id' => $newuserid);
							$findresult = $this->dynamic_model->getdatafromtable('user', $where);
							$name= ucwords($findresult[0]['name']);

							//Send Email Code
							$enc_user = encode($newuserid);
							$enc_role = encode($time);
							 $url = site_url().'webservices/api/verify_user?encid='.$enc_user.'&batch='.$enc_role;
                            $link='<a href="'.$url.'"> Click here </a>';
							$weburl = site_url();
							$weburl = str_replace('/superadmin', '/signin', $weburl);
                            $weblink='<a href="'.$weburl.'"> Click here </a>';

                            $where1 = array('slug' => 'new_client_registration');
                            $template_data = $this->dynamic_model->getdatafromtable('manage_notification_mail', $where1);
                            $desc= str_replace('{USERNAME}',$name,$template_data[0]['description']);
                            $desc_data= str_replace('{PASSWORD}',$password, $desc);
                            $desc_data= str_replace('{URL}',$link, $desc_data);
                            $desc_data= str_replace('{WEBURL}',$weblink, $desc_data);
                            $desc_send= str_replace('{SITE_TITLE}',SITE_TITLE, $desc_data);
                            $subject = str_replace('{SITE_TITLE}',SITE_TITLE, $template_data[0]['subject']);
                            $emailsubject = 'Thank you for registering with '.SITE_TITLE;
							$data['subject']     = $subject;
							$data['description'] = $desc_send;
							$data['body'] = "";
							$msg = $this->load->view('emailtemplate', $data, true);
							sendEmailCI("$email", SITE_TITLE ,$emailsubject, $msg);
							//Send Email Code
                            $arg['status']    = 1;
                            $arg['error_code'] = REST_Controller::HTTP_OK;
                            $arg['error_line']= __line__;
                            $arg['message']   = $this->lang->line('thank_msg_client');
                            $arg['data']      = '';
                        }else{
		                    $arg['status']    = 0;
		                    $arg['error_code'] = REST_Controller::HTTP_OK;
		                    $arg['error_line']= __line__;
		                    $arg['message']   = $this->lang->line('record_not_found');
		                    $arg['data']      = '';
                        }
                    }
                }
            }
         }
            echo json_encode($arg);
       }
    }
    /****************Function client_list********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : client_list
     * @description     : list of client
     * @param           : null
     * @return          : null
     * ********************************************************** */
    public function client_list_post()
	{
	   $arg = array();
	   $userdata = checkuserid('1');
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
	      $_POST = json_decode(file_get_contents("php://input"), true);
		  if($_POST)
		  {
		    $this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
					'required' => $this->lang->line('page_no'),
					'numeric' => $this->lang->line('page_no_numeric'),
				));
		    $this->form_validation->set_rules('business_id','Business Id','required',array(
					'required' => $this->lang->line('business_id_req')
				));
			if($this->form_validation->run() == FALSE)
			{
			  	$arg['status']  = 0;
			  	$arg['error_code'] = 0;
				$arg['error_line']= __line__;
			 	$arg['message'] = get_form_error($this->form_validation->error_array());
			}
			else
			{
				$usid   = $userdata['data']['id'];
				$response=array();
				$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
				$page_no= $page_no-1;
				$limit    = config_item('page_data_limit');
				$offset = $limit * $page_no;
				$search_val=  $this->input->post('search_val');
				$business_id=$this->input->post('business_id');
				if($search_val){
					//$where= 'user.created_by="'.$usid.'" AND (user.name LIKE "%'.$search_val.'%" OR user.lastname LIKE "%'.$search_val.'%") AND business_trainer_relationship.business_id="'.$business_id.'"';
					$where= '(user.name LIKE "%'.$search_val.'%" OR user.lastname LIKE "%'.$search_val.'%") AND user_booking.business_id="'.$business_id.'"';
				}else{
                    // $where= 'user.created_by="'.$usid.'" AND business_trainer_relationship.business_id="'.$business_id.'"';

                      $where= 'user_booking.business_id="'.$business_id.'"';
				}

				/*$data="user.*,business_trainer_relationship.business_id";
                $on='user.created_by = business_trainer_relationship.user_id';
			    $client_data= $this->dynamic_model->getTwoTableData($data,'user','business_trainer_relationship',$on,$where,$limit,$offset,"user.create_dt","DESC");*/



			    $data="user.*,user_booking.business_id";
                $on='user.id = user_booking.user_id';
			    $client_data= $this->dynamic_model->getTwoTableData($data,'user','user_booking',$on,$where,$limit,$offset,"user.name","ASC",'','user.id');

				if($client_data){
					foreach($client_data as $value){
					$clientdata['id']     = $value['id'];
					//$clientdata['type']   = $value['title_name'];
	            	$clientdata['name']   = ucwords($value['name'].' '.$value['lastname']);
	            	$clientdata['email']  = $value['email'];
	            	$clientdata['profile_img']  = base_url().'uploads/user/'.$value['profile_img'];
	            	$clientdata['country_code'] = !empty($value['country_code']) ? $value['country_code'] : '';
	            	$clientdata['mobile'] = $value['mobile'];
	            	$clientdata['date_of_birth'] =  !empty($value['date_of_birth']) ? $value['date_of_birth'] : '';
	            	$clientdata['gender'] = $value['gender'];
	            	$response[]	          = $clientdata;
					}
				    $arg['status']    = 1;
					$arg['error_code'] = HTTP_OK;
					$arg['error_line']= __line__;
				 	$arg['data']      = $response;
				 	$arg['message']   = $this->lang->line('record_found');
				}
				else{
					$arg['status']     = 0;
					$arg['error_code']  = REST_Controller::HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = array();
				 	$arg['message']    = $this->lang->line('record_not_found');
				}
		    }
		  }
		}
	   echo json_encode($arg);
	}
	/****************Function client details****************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : client_details
     * @description     : client details
     * @param           : null
     * @return          : null
     * ********************************************************** */
    public function client_details_post()
	{
	   $arg = array();
	   $userdata = checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
	      $_POST = json_decode(file_get_contents("php://input"), true);
		  if($_POST)
		  {
		    $this->form_validation->set_rules('id', 'User Id', 'required|numeric',array(
					'required' => $this->lang->line('user_id'),
				));
			if($this->form_validation->run() == FALSE)
			{
			  	$arg['status']  = 0;
			  	$arg['error_code'] = 0;
				$arg['error_line']= __line__;
			 	$arg['message'] = get_form_error($this->form_validation->error_array());
			}
			else
			{
				$usid   = $userdata['data']['id'];
				$response=array();
				$id=  $this->input->post('id');
				$client_data = $this->dynamic_model->getdatafromtable('user',array('id'=>$id));
				if($client_data){
					$clientdata['id']     = $client_data[0]['id'];
	            	$clientdata['name']   = ucwords($client_data[0]['name'].' '.$client_data[0]['lastname']);
	            	$clientdata['email']  = $client_data[0]['email'];
	            	$clientdata['profile_img']  = base_url().'uploads/user/'.$client_data[0]['profile_img'];
	            	$clientdata['country_code'] = $client_data[0]['country_code'];
	            	$clientdata['mobile'] = $client_data[0]['mobile'];
	            	$clientdata['address'] = $client_data[0]['address'];
	            	$clientdata['country'] = $client_data[0]['country'];
	            	$clientdata['state'] = $client_data[0]['state'];
	            	$clientdata['city'] = $client_data[0]['city'];
	            	$clientdata['lat'] = $client_data[0]['lat'];
	            	$clientdata['lang'] = $client_data[0]['lang'];
	            	$clientdata['zipcode'] = $client_data[0]['zipcode'];
	            	$clientdata['street'] = $client_data[0]['location'];
	            	$clientdata['date_of_birth'] = $client_data[0]['date_of_birth'];
	            	$clientdata['gender'] = $client_data[0]['gender'];
	            	$clientdata['create_dt'] = date("d M Y ",$client_data[0]['create_dt']);
			        $clientdata['create_dt_utc'] = $client_data[0]['create_dt'];
                   $condition=array("user_id"=>$client_data[0]['id'],"is_deleted"=>'0');
			        $member_data= $this->dynamic_model->getdatafromtable('user_family_details',$condition);
			       $clientdata['memeber_id']   = !empty($member_data[0]['id']) ? $member_data[0]['id'] : '';
			       $clientdata['member_name']   = !empty($member_data[0]['member_name']) ? ucwords($member_data[0]['member_name']) : '';
	            	$response	          = $clientdata;
				    $arg['status']    = 1;
					$arg['error_code'] = HTTP_OK;
					$arg['error_line']= __line__;
				 	$arg['data']      = $response;
				 	$arg['message']   = $this->lang->line('record_found');
				}
				else{
					$arg['status']     = 0;
					$arg['error_code']  = REST_Controller::HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = array();
				 	$arg['message']    = $this->lang->line('record_not_found');
				}
		    }
		  }
		}
	   echo json_encode($arg);
	}
    // For cron job 1
    public function class_time_update_get(){
        $curr_date=date('Y-m-d');
        $user_attendance = $this->dynamic_model->getdatafromtable('user_attendance',array('status'=>'checkin'));
        if(!empty($user_attendance)){
            foreach($user_attendance as $value){
	            if($value['service_type']==1){
	            	$where=array("id"=>$value['service_id']);
                   $business_class = $this->dynamic_model->getdatafromtable('business_class');
	            }else{
	            	$where=array("id"=>$value['service_id']);
                   $business_class = $this->dynamic_model->getdatafromtable('business_workshop');
	            }
	            $to_time =$business_class[0]['to_time'];
	            $end_time = date('h:i',$to_time);
	            $checkout_time = date('Y-m-d h:i',strtotime($end_time));
	            $checkin_date=date('Y-m-d',$value['checkin_time']);
	            if($curr_date >= $checkin_date){
	               // echo $value['id'];
                $wh = array('user_id' =>$value['user_id'],'status'=>'checkin');
				$update_data = array(
					'status' =>'checkout',
					'checkout_time' =>strtotime($checkout_time)
				);
				$this->dynamic_model->updateRowWhere('user_attendance',$wh,$update_data);

               }
            }
        }
    }
    /****************Function Get week days and time slots********
     * @type            : Function
     * @Author          : Arpit
     * @function name   : get_member_list
     * @description     : list of member
     * @param           : null
     * @return          : null
     * ********************************************************** */
    public function get_weekdays_timeslot_post()
    {
        $arg = array();
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
        }
        else
        {
           $userdata = checkuserid('1');
           if($userdata['status'] != 1){
             $arg = $userdata;
            }
            else
            {
	           $_POST = json_decode(file_get_contents("php://input"), true);
			  if($_POST)
			  {
			   $this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric')
					));
				if($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
                    $response=array();
                    $time=time();

                    $usid =$userdata['data']['id'];
                    $business_id  = $this->input->post('business_id');
                    $week_data= $this->instructor_model->business_time_slote($business_id);
                    //$week_data= $this->dynamic_model->getdatafromtable('manage_week_days');

                    if(!empty($week_data)){
                        foreach($week_data as $key=>$value)
                        {
                           $i=0;
                           $day_slot= $this->dynamic_model->getdatafromtable('instructor_time_slot',array("business_id"=>$business_id,"user_id"=>$usid,"day_id"=>$value['day_id']));

                            $weekData['id']   = $value['day_id'];
                            $weekData['week_name'] = $value['week_name'];
                            $weekData['day_status']   = !empty($day_slot[0]['day_status']) ? $day_slot[0]['day_status'] :"0";
                            $time_slot_data= $this->dynamic_model->getdatafromtable('business_time_slote',array("business_id"=>$business_id,"day_id"=>$value['day_id']));
                            foreach($time_slot_data as $key1=> $value1)
                            {

                                $time_slot= $this->dynamic_model->getdatafromtable('instructor_time_slot',array("business_id"=>$business_id,"user_id"=>$usid,"day_id"=>$value['day_id'],"time_slot_id"=>$value1['id']));
                                $time_slot_data[$key1]['id']   = $value1['id'];
                                $time_slot_data[$key1]['time_slote_from'] = $value1['time_slote_from'];
                                $time_slot_data[$key1]['time_slote_to'] = $value1['time_slote_to'];
                                  $time_slot_data[$key1]['time_slot_status']   = !empty($time_slot[0]['time_slot_status']) ? $time_slot[0]['time_slot_status'] :"0";


                                unset($time_slot_data[$key1]['day_id']);
                                unset($time_slot_data[$key1]['create_dt']);
                                unset($time_slot_data[$key1]['update_dt']);
                                //$slotarr[$i++]        = $slotData;
                            }
                            $weekData['time_slot'] = $time_slot_data;
                            $user_data= $this->dynamic_model->getdatafromtable('user',array('id'=>$usid));
                            $response[]        = $weekData;
                        }
                        $availability_status=!empty($user_data[0]['availability_status']) ? $user_data[0]['availability_status'] : '';

                        $result=array('week_data'=>$response,'availability_status'=>$availability_status);
                    }
                    if($response){
                        $arg['status']     = 1;
                        $arg['error_code']  = REST_Controller::HTTP_OK;
                        $arg['error_line']= __line__;
                        $arg['data']       = $result;
                        $arg['message']    = $this->lang->line('record_found');
                    }else{
                        $arg['status']     = 0;
                        $arg['error_code']  = REST_Controller::HTTP_OK;
                        $arg['error_line']= __line__;
                        $arg['message']    = $this->lang->line('record_not_found');
                    }
                }
               }
           }
       }
       echo json_encode($arg);
    }
      /****************Function Get week days and time slots********
     * @type            : Function
     * @Author          : Arpit
     * @function name   : get_member_list
     * @description     : list of member
     * @param           : null
     * @return          : null
     * ********************************************************** */
    public function instructor_availability_post()
    {
        $arg = array();
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
        }
        else
        {
            $userdata = checkuserid('2');
           if($userdata['status'] != 1){
             $arg = $userdata;
            }
            else
            {
              $_POST = json_decode(file_get_contents("php://input"), true);
              if($_POST)
              {
                $this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric')
					));
			     //            $this->form_validation->set_rules('day_id', 'Day Id', 'required',array(
								// 	'required' => $this->lang->line('day_id_req')
								// ));
			     //             $this->form_validation->set_rules('time_slot_id', 'Time slot Id', 'required',array(
								// 	'required' => $this->lang->line('time_slot_id_req')
								// ));
                if($this->form_validation->run() == FALSE)
                {
                    $arg['status']  = 0;
                    $arg['error_code'] = 0;
                    $arg['error_line']= __line__;
                    $arg['message'] = get_form_error($this->form_validation->error_array());
                }
                else
                {
                    $time=time();
                    $usid =$userdata['data']['id'];
                    $business_id= $this->input->post('business_id');
                    $day_id= $this->input->post('day_id');
                    $day_status= $this->input->post('day_status');
                    $slot_info= $this->input->post('slot_time');
                    $availability_status= $this->input->post('availability_status');
                    if(!empty($availability_status)){
                    //update availability status of instructor
                     $userdata=array(
                                        'availability_status'=>$availability_status,
                                        'update_dt'=>$time
                                      );
                     $condition=array("id"=>$usid);
                    $this->dynamic_model->updateRowWhere('user',$condition,$userdata);
                    //End of update availability status of instructor
                      $arg['status']     = 1;
	                    $arg['error_code']  = REST_Controller::HTTP_OK;
	                    $arg['error_line']= __line__;
	                    $arg['data']      ='';
	                    $arg['message']    = $this->lang->line('instructor_availability_succ');
                    }


                    if(!empty($slot_info)){
                    	$instructor_time_slot= $this->dynamic_model->getdatafromtable('instructor_time_slot',array("business_id"=>$business_id,"user_id"=>$usid,"day_id"=>$day_id,"time_slot_id"=>$slot_info[0]['time_slot_id']));
                    if(empty($instructor_time_slot)){
                        foreach($slot_info as $value)
                            {
                                $time_slot_id   = $value['time_slot_id'];
                                $slot_status   =!empty($day_status) ? $value['time_slot_status'] :'0';
                                $data=array(
                                          'user_id'=>$usid,
                                          'business_id'=>$business_id,
                                          'time_slot_id'=>$time_slot_id,
                                          'day_id'=>$day_id,
                                          'time_slot_status'=>$slot_status,
                                          'day_status'=>$day_status,
                                          'create_dt'=>$time,
                                          'update_dt'=>$time
                                      );
                                 $this->dynamic_model->insertdata('instructor_time_slot',$data);
                            }
                            $arg['status']     = 1;
		                    $arg['error_code']  = REST_Controller::HTTP_OK;
		                    $arg['error_line']= __line__;
		                    $arg['data']      ='';
		                    $arg['message']    = $this->lang->line('instructor_availability_succ');
                        // $arg['status']     = 1;
                        // $arg['error_code']  = REST_Controller::HTTP_OK;
                        // $arg['error_line']= __line__;
                        // $arg['data']      = '';
                        // $arg['message']    = $this->lang->line('instructor_availability_succ');
                    }else{
                    	 foreach($slot_info as $value)
                         {
	                        $time_slot_id   = $value['time_slot_id'];
	                        $slot_status   =!empty($day_status) ? $value['time_slot_status'] :'0';
	                       $where= array("business_id"=>$business_id,"user_id"=>$usid,"day_id"=>$day_id,"time_slot_id"=>$time_slot_id);

	                        $data=array(
	                                  'time_slot_status'=>$slot_status,
	                                  'day_status'=>$day_status,
	                                  'update_dt'=>$time
	                              );

	                       $this->dynamic_model->updateRowWhere('instructor_time_slot',$where,$data);

                         }
                         $arg['status']     = 1;
                        $arg['error_code']  = REST_Controller::HTTP_OK;
                        $arg['error_line']= __line__;
                        $arg['data']      = '';
                        $arg['message']    = $this->lang->line('instructor_availability_succ');

                    }



                }
                // else{

                //         $arg['status']     = 0;
                //         $arg['error_code']  = REST_Controller::HTTP_OK;
                //         $arg['error_line']= __line__;
                //         $arg['data']      = [];
                //         $arg['message']    = $this->lang->line('time_slot_req');
                //     }
                }
              }
            }
        }
       echo json_encode($arg);
    }


     public function instructor_availabilityn_post()
    {
        $arg = array();
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
        }
        else
        {
            $userdata = checkuserid('2');
           if($userdata['status'] != 1){
             $arg = $userdata;
            }
            else
            {
              $_POST = json_decode(file_get_contents("php://input"), true);
              if($_POST)
              {

                $this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric')
					));

                if($this->form_validation->run() == FALSE)
                {
                    $arg['status']  = 0;
                    $arg['error_code'] = 0;
                    $arg['error_line']= __line__;
                    $arg['message'] = get_form_error($this->form_validation->error_array());
                }
                else
                {
                    $time=time();
                    $usid =$userdata['data']['id'];
                    $business_id= $this->input->post('business_id');
                    $days= $this->input->post('days');
                   // $day_status= $this->input->post('day_status');
                    //$slot_info= $this->input->post('slot_time');
                    $availability_status= $this->input->post('availability_status');

                    if(!empty($availability_status)){
                    //update availability status of instructor
                     $userdata=array(
                                        'availability_status'=>$availability_status,
                                        'update_dt'=>$time
                                      );
                     $condition=array("id"=>$usid);
                    $this->dynamic_model->updateRowWhere('user',$condition,$userdata);
                    //End of update availability status of instructor
                      $arg['status']     = 1;
	                    $arg['error_code']  = REST_Controller::HTTP_OK;
	                    $arg['error_line']= __line__;
	                    $arg['data']      ='';
	                    $arg['message']    = $this->lang->line('instructor_availability_succ');
                    }


                    if(!empty($days)){
                    	foreach($days as $d){
	                        foreach($d['slot_time'] as $value){
	                        	 $instructor_time_slot= $this->dynamic_model->getdatafromtable('instructor_time_slot',array("business_id"=>$business_id,"user_id"=>$usid,"day_id"=>$d['day_id'],"time_slot_id"=>$value['time_slot_id']));

	                        	if(empty($instructor_time_slot)){
	                        		$time_slot_id   = $value['time_slot_id'];
	                                $slot_status   =!empty($d['day_status']) ? $value['time_slot_status'] :'0';
	                                $data=array(
	                                          'user_id'=>$usid,
	                                          'business_id'=>$business_id,
	                                          'time_slot_id'=>$time_slot_id,
	                                          'day_id'=>$d['day_id'],
	                                          'time_slot_status'=>$slot_status,
	                                          'day_status'=>$d['day_status'],
	                                          'create_dt'=>$time,
	                                          'update_dt'=>$time
	                                      );
	                                 $this->dynamic_model->insertdata('instructor_time_slot',$data);
	                        	}else{
	                        		$time_slot_id   = $value['time_slot_id'];
			                        $slot_status   =!empty($d['day_status']) ? $value['time_slot_status'] :'0';
			                       $where= array("business_id"=>$business_id,"user_id"=>$usid,"day_id"=>$d['day_id'],"time_slot_id"=>$time_slot_id);

			                        $data=array(
			                                  'time_slot_status'=>$slot_status,
			                                  'day_status'=>$d['day_status'],
			                                  'update_dt'=>$time
			                              );

	                       			$this->dynamic_model->updateRowWhere('instructor_time_slot',$where,$data);
	                        	}

	                        }

                            $arg['status']     = 1;
		                    $arg['error_code']  = REST_Controller::HTTP_OK;
		                    $arg['error_line']= __line__;
		                    $arg['data']      ='';
		                    $arg['message']    = $this->lang->line('instructor_availability_succ');
		                }

                	}

                }
              }
            }
        }
       echo json_encode($arg);
    }

    	/****************Function service appointment details**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : service_appointment_list
     * @description     : service appointment details
     * @param           : null
     * @return          : null
     * ********************************************************** */
    public function appointment_list_post()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = checkuserid('1');
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
		      $_POST = json_decode(file_get_contents("php://input"), true);
			  if($_POST)
			  {
			    $this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
			     $this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
				if($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$response=array();
                    $time_zone =  $this->input->get_request_header('Timezone', true);
                    $time_zone =  $time_zone ? $time_zone : 'UTC';
                    date_default_timezone_set($time_zone);
					$time = time();
					$date = date("Y-m-d",$time);
					$usid = $userdata['data']['id'];
					$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
					$page_no= $page_no-1;
					$limit    = 5;
					$offset = $limit * $page_no;
					$business_id =  $this->input->post('business_id');

					$query = "SELECT user.id, user.name, user.lastname, user.profile_img, user.gender, user.date_of_birth, business_appointment_book.user_id as instructor_id, business_appointment_book.family_user_id, business_appointment_book.slot_date as appointment_date, service.duration as appointment_duration, business_appointment_book.service_id, business_appointment_book.slot_id, service_scheduling_time_slot.start_time as appointment_from_time, service_scheduling_time_slot.end_time as appointment_to_time, business_appointment_book.create_dt  FROM `business_appointment_book` JOIN user_booking on (user_booking.id = business_appointment_book.booking_id) JOIN user on (user.id = user_booking.user_id) JOIN service_scheduling_time_slot on (service_scheduling_time_slot.id = business_appointment_book.slot_id) JOIN  service on (service.id = business_appointment_book.service_id) where business_appointment_book.slot_available_status != 3 and business_appointment_book.user_id = ".$usid;

					if ($this->input->post('customer_type') && (strtolower($this->input->post('customer_type')) == 'male' || strtolower($this->input->post('customer_type')) == 'female')) {
						$gender = strtolower($this->input->post('customer_type'));
						$query .= ' AND user.gender = "'.$gender.'"';

					}

					$collection = $this->dynamic_model->getQueryResultArray($query);

					if(!empty($collection)) {
						$query_info = 'SELECT user.id, user.name, user.lastname, user.profile_img, user.availability_status, instructor_details.total_experience as experience, instructor_details.appointment_fees, instructor_details.appointment_fees_type, instructor_details.skill FROM user join instructor_details on (instructor_details.user_id = user.id) where user.id = '.$usid;
						$instructorData = $this->dynamic_model->getQueryRowArray($query_info);

						if(!empty($instructorData)) {
							$url = site_url() . 'uploads/user/'.$instructorData['profile_img'];
							$instructorData['profile_img'] = $url;
							$instructorData['appointment_fees'] = floatVal($instructorData['appointment_fees']);
							$skills = $instructorData['skill'];
							$instructorData['skill'] = $this->dynamic_model->getQueryRowArray('SELECT GROUP_CONCAT(name) as skill from manage_skills where id in ('.$skills.')')['skill'];
							$instructorData['skill_details'] = $this->dynamic_model->getQueryResultArray('SELECT id, name as category_name from manage_skills where id in ('.$skills.')');
						}

						$response = array();
						foreach($collection as $col){
							$temp['id']    	= $col['id'];
							$temp['name']    		= $col['name'];
							$temp['lastname']    	= $col['lastname'];
							$temp['profile_img']    = site_url() . 'uploads/user/'.$col['profile_img'];
							$temp['gender']    		= $col['gender'];
							$temp['date_of_birth']    = strtotime($col['date_of_birth']);
							$temp['family_user_id']    = $col['family_user_id'];
							$temp['appointment_date']    = $col['appointment_date'];
							$temp['appointment_from_time']    = $col['appointment_from_time'];
							$temp['appointment_to_time']    = $col['appointment_to_time'];
							$temp['appointment_duration']    = $col['appointment_duration'];

							if ($col['family_user_id'] > 0) {
								$memberInfo = 'SELECT user_family_details.member_name, user_family_details.photo, user_family_details.dob, manage_relations.name as relations  FROM `user_family_details` JOIN manage_relations on (manage_relations.id = user_family_details.relative_id) WHERE user_family_details.id = '.$col['family_user_id'];
								$memberData = $this->dynamic_model->getQueryRowArray($memberInfo);
								$temp['member_detail']    = $col['appointment_to_time'];
							} else {
								$temp['member_detail']    = array();
							}
							$temp['instructor_details'] = $instructorData;
							$temp['create_dt']    = $col['create_dt'];
							$response[] = $temp;
						}

						$arg['status']     = 1;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = $response;
						$arg['message']    = $this->lang->line('record_found');
					} else {
						$arg['status']     = 0;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = array();
					 	$arg['message']    = $this->lang->line('record_not_found');
					}


					/* $user_data = $this->dynamic_model->getdatafromtable('user',array("role_id"=>3,"status"=>"Active"),"*",$limit,$offset,'create_dt');

					if(!empty($user_data)){
					    foreach($user_data as $value)
			            {
					        $udata['id']     = $value['id'];
			            	$udata['name']   = ucwords($value['name']);
			            	$udata['lastname']= ucwords($value['lastname']);
			            	$udata['profile_img'] = base_url()."uploads/user/".$value['profile_img'];
			            	$udata['gender'] =$value['gender'];
			            	$udata['date_of_birth'] =$value['date_of_birth'];
			            	$udata['skill']    = "Spa Parlour";
			            	// $instructor_data             = $this->instructor_list_details($business_id,3,$value['id']);
			            	// $udata['instructor_details']= !empty($instructor_data[0]) ? $instructor_data[0] : json_decode('{}');
			            	$udata['appointment_date'] = "2020-04-28";
			            	$udata['appointment_from_time'] = "02:00 PM";
			            	$udata['appointment_to_time'] = "04:00 PM";
			            	$udata['appointment_date_utc'] = "1588070681";
			            	$udata['appointment_from_time_utc'] = "1582293600";
			            	$udata['appointment_to_time_utc'] = "1582128000";

			            	$response[]	                 = $udata;
			            }
						$arg['status']     = 1;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = $response;
						$arg['message']    = $this->lang->line('record_found');
					}else{
						$arg['status']     = 0;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = array();
					 	$arg['message']    = $this->lang->line('record_not_found');
					} */
			    }
			  }
			}
		}
	   echo json_encode($arg);
	}

	public function service_list_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
		   	if($userdata['status'] != 1){
			 $arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
			    	/* $this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					)); */

					if($this->form_validation->run() == FALSE)
            		{
						$arg['status']  = 0;
						$arg['error_code'] = 0;
						$arg['error_line']= __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());

					} else {

						$response=array();
						$time=time();
						$usid =$userdata['data']['id'];
						$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no= $page_no-1;
						$limit    = config_item('page_data_limit');
						$offset = $limit * $page_no;
						// $business_id=  $this->input->post('business_id');

						$businessPath =	base_url().'uploads/business/';
						$query = "SELECT business.business_name, concat('".$businessPath."', business.logo) as logo, service.id, service.service_name, service.business_id, service.amount, service.service_type, (CASE when service.service_type = 1 THEN 'Service' ELSE 'Non Service' END) as service_type_name, service.duration, service.tax1, service.tax2, service.tax1_label, service.tax2_label, service.tax1_rate, service.tax2_rate, service.cancel_policy, service.tip_option, service.description, service.pay_rate_type, service.pay_rate, service.is_client_visible, service.time_needed FROM service JOIN business on (business.id = service.business_id) WHERE service.status = 'Active' AND service.skills in (SELECT DISTINCT instructor_details.skill FROM instructor_details JOIN user on (user.id = instructor_details.user_id) where user.id = ".$usid.") ";

						if ($this->input->post('business_id')) {
							$business_id =  $this->input->post('business_id');
							$query .= " AND service.business_id = ".$business_id;
						}
						$query .= " LIMIT ".$limit." OFFSET ".$offset;
						$collection = $this->dynamic_model->getQueryResultArray($query);
						/* $counter = "SELECT service.id as service_id, concat(UCASE(LEFT(service.service_name, 1)), SUBSTRING(service.service_name, 2)) as service_name, concat(UCASE(LEFT(manage_skills.name, 1)), SUBSTRING(manage_skills.name, 2)) as service_category, DATE_FORMAT(FROM_UNIXTIME(service.create_dt), '%e %b %Y') AS create_dt FROM `service_scheduling_time` JOIN service_scheduling on (service_scheduling.id = service_scheduling_time.scheduled_id) JOIN service on (service.id = service_scheduling.service_id) JOIN manage_skills on (manage_skills.id = service.service_type) where service_scheduling_time.instructor_id IN (".$usid.") AND service.business_id = ".$business_id." GROUP BY service.id LIMIT ".$limit." OFFSET ".$offset;

						$parentData = $this->dynamic_model->getQueryResultArray($counter);

						$query = "SELECT service.id as service_id, concat(UCASE(LEFT(service.service_name, 1)), SUBSTRING(service.service_name, 2)) as service_name, concat(UCASE(LEFT(manage_skills.name, 1)), SUBSTRING(manage_skills.name, 2)) as service_category, manage_skills.id as category_id, DATE_FORMAT(FROM_UNIXTIME(service.create_dt), '%e %b %Y') AS create_dt, business_location.location_name, business_location.capacity FROM `service_scheduling_time` JOIN service_scheduling on (service_scheduling.id = service_scheduling_time.scheduled_id) JOIN service on (service.id = service_scheduling.service_id) JOIN manage_skills on (manage_skills.id = service.service_type) JOIN business_location on (business_location.id = service_scheduling.location) where service_scheduling_time.instructor_id IN (".$usid.") AND service.business_id = ".$business_id;

						$collection_data = $this->dynamic_model->getQueryResultArray($query);

						$collection = array();
						for ($j = 0; $j < count($parentData); $j++) {
							$parent_array = $parentData[$j];
							$temp_array = array();
							for ($i = 0; $i < count($collection_data); $i++) {
								$info = $collection_data[$i];
								$service_id = $info['service_id'];
								if ($service_id == $parent_array['service_id']) {
									array_push($temp_array, array('location_name' => $info['location_name'], 'capacity' => $info['capacity']));
								}
							}
							$temp_array = array_unique($temp_array, SORT_REGULAR);
							$temp_array = array_values($temp_array);
							array_push($collection, array(
									'service_id' 		=> 	$parent_array['service_id'],
									'service_name' 		=> 	$parent_array['service_name'],
									'service_category' 	=> 	$parent_array['service_category'],
									'category'			=>	array($parent_array['service_name']),
									'details'			=> 	$temp_array[0],
									'create_dt' 		=> 	$parent_array['create_dt']
								)
							);
						} */

						/* $query = 'SELECT service.id AS service_id, service_name, concat(UCASE(LEFT(manage_skills.name, 1)), SUBSTRING(manage_skills.name, 2)) as service_category, service.create_dt FROM `business_appointment_book` JOIN service ON (service.id = business_appointment_book.service_id) JOIN manage_skills on (manage_skills.id = service.service_type) WHERE business_appointment_book.business_id = 4 AND business_appointment_book.user_id = 34 GROUP BY business_appointment_book.service_id LIMIT 10 OFFSET 0';

						$collection = $this->dynamic_model->getQueryResultArray($query); */

						if (!empty($collection)) {
							$arg['status']  = 1;
							$arg['error_code'] = 0;
							$arg['error_line']= __line__;
							$arg['data'] = $collection;
							// $arg['collection_data'] = $collection_data;
						} else {
							$arg['status']     = 0;
							$arg['error_code']  = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
							$arg['message']    = $this->lang->line('record_not_found');
						}

					}
				}
			}
		}

		echo json_encode($arg);
	}

	public function service_list__old_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
		   	if($userdata['status'] != 1){
			 $arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
			    	$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));

					if($this->form_validation->run() == FALSE)
            		{
						$arg['status']  = 0;
						$arg['error_code'] = 0;
						$arg['error_line']= __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());

					} else {

						$response=array();
						$time=time();
						$usid =$userdata['data']['id'];
						$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no= $page_no-1;
						$limit    = config_item('page_data_limit');
						$offset = $limit * $page_no;
						$business_id=  $this->input->post('business_id');

						$counter = "SELECT service.id as service_id, concat(UCASE(LEFT(service.service_name, 1)), SUBSTRING(service.service_name, 2)) as service_name, concat(UCASE(LEFT(manage_skills.name, 1)), SUBSTRING(manage_skills.name, 2)) as service_category, DATE_FORMAT(FROM_UNIXTIME(service.create_dt), '%e %b %Y') AS create_dt FROM `service_scheduling_time` JOIN service_scheduling on (service_scheduling.id = service_scheduling_time.scheduled_id) JOIN service on (service.id = service_scheduling.service_id) JOIN manage_skills on (manage_skills.id = service.service_type) where service_scheduling_time.instructor_id IN (".$usid.") AND service.business_id = ".$business_id." GROUP BY service.id LIMIT ".$limit." OFFSET ".$offset;

						$parentData = $this->dynamic_model->getQueryResultArray($counter);

						$query = "SELECT service.id as service_id, concat(UCASE(LEFT(service.service_name, 1)), SUBSTRING(service.service_name, 2)) as service_name, concat(UCASE(LEFT(manage_skills.name, 1)), SUBSTRING(manage_skills.name, 2)) as service_category, DATE_FORMAT(FROM_UNIXTIME(service.create_dt), '%e %b %Y') AS create_dt, business_location.location_name, business_location.capacity FROM `service_scheduling_time` JOIN service_scheduling on (service_scheduling.id = service_scheduling_time.scheduled_id) JOIN service on (service.id = service_scheduling.service_id) JOIN manage_skills on (manage_skills.id = service.service_type) JOIN business_location on (business_location.id = service_scheduling.location) where service_scheduling_time.instructor_id IN (".$usid.") AND service.business_id = ".$business_id;

						$collection_data = $this->dynamic_model->getQueryResultArray($query);

						/* $query = 'SELECT service.id AS service_id, service_name, concat(UCASE(LEFT(manage_skills.name, 1)), SUBSTRING(manage_skills.name, 2)) as service_category, service.create_dt FROM `business_appointment_book` JOIN service ON (service.id = business_appointment_book.service_id) JOIN manage_skills on (manage_skills.id = service.service_type) WHERE business_appointment_book.business_id = 4 AND business_appointment_book.user_id = 34 GROUP BY business_appointment_book.service_id LIMIT 10 OFFSET 0';

						$collection = $this->dynamic_model->getQueryResultArray($query); */

						if (!empty($collection)) {
							/* array_walk ( $collection, function (&$key) {
								$key["create_dt"] = date("d M Y ",$key['create_dt']);
							}); */
							$arg['status']  = 1;
							$arg['error_code'] = 0;
							$arg['error_line']= __line__;
							$arg['data'] = $collection;
						} else {
							$arg['status']     = 0;
							$arg['error_code']  = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
							$arg['data']       = json_decode('{}');
							$arg['message']    = $this->lang->line('record_not_found');
						}

					}
				}
			}
		}

		echo json_encode($arg);
	}

	public function view_my_schedule_post() {

		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
		   	if($userdata['status'] != 1){
			 $arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));

					$this->form_validation->set_rules('service_type', 'Service Type', 'required|numeric',array(
						'required' => 'Sevice Type is required',
						'numeric' => 'Sevice Type is numeric',
					));

					if ($this->input->post('service_type') && $this->input->post('service_type') == 3) {
						$this->form_validation->set_rules('service_id', 'Service Id', 'required|numeric',array(
							'required' => 'Sevice Id is required',
							'numeric' => 'Sevice Id is numeric',
						));
					}

					if($this->form_validation->run() == FALSE)
            		{
						$arg['status']  = 0;
						$arg['error_code'] = 0;
						$arg['error_line']= __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());

					} else {

						$response=array();
						$time=time();
						$usid =$userdata['data']['id'];
						$business_id=  $this->input->post('business_id');
						$servie_type =  $this->input->post('service_type'); // 1 - Class, 2 - Workshop, 3 - Service
						$collection = array();
						if ($servie_type == 1) {

						} else if ($servie_type == 2) {

						} else if ($servie_type == 3) {
							$service_id = $this->input->post('service_id');
							$query = 'SELECT service_scheduling_time.service_date, manage_week_days.id as day_id, manage_week_days.week_name, service_scheduling_time_slot.start_time, service_scheduling_time_slot.end_time FROM `service_scheduling_time_slot` JOIN service_scheduling_time on (service_scheduling_time.id = service_scheduling_time_slot.service_scheduling_time_id) JOIN manage_week_days on (manage_week_days.id = service_scheduling_time.day_id) JOIN service_scheduling on (service_scheduling.id = service_scheduling_time.scheduled_id) WHERE service_scheduling.business_id = '.$business_id.' and service_scheduling_time.instructor_id IN ('.$usid.') AND service_scheduling_time.service_date BETWEEN date(now()) AND (date_add(date_add(date(now()), interval -WEEKDAY(date(now()))-1 day), interval 7 day)) AND service_scheduling.service_id = '.$service_id.' ORDER BY service_scheduling_time.service_date asc';
							$array = $this->dynamic_model->getQueryResultArray($query);
							$filter_data = array();
							for ($i = 0; $i < count($array); $i++) {
								$info = $array[$i];
								array_push($filter_data, array('service_date' => $info['service_date'], 'week_name' => $info['week_name']));
							}

							$filter_data = array_unique($filter_data, SORT_REGULAR);
							$filter_data = array_values($filter_data);

							for ($j = 0; $j < count($filter_data); $j++) {
								$parent_array = $filter_data[$j];
								$temp_array = array();
								for ($i = 0; $i < count($array); $i++) {
									$info = $array[$i];
									$service_date = $info['service_date'];
									if ($service_date == $parent_array['service_date']) {
										array_push($temp_array, array('start_time' => $info['start_time'], 'end_time' => $info['end_time']));
									}
								}
								$temp_array = array_unique($temp_array, SORT_REGULAR);
								$temp_array = array_values($temp_array);

								array_push($collection, array(
										'service_date' 		=> 	$parent_array['service_date'],
										'week_name' 		=> 	$parent_array['week_name'],
										'details'			=> 	$temp_array,
									)
								);
							}
						}

						if($collection){
							$arg['status']     = 1;
							$arg['error_code']  = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
							$arg['data']      = $collection;
							$arg['message']    = $this->lang->line('record_found');
						}else{
							$arg['status']     = 0;
							$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['message']    = $this->lang->line('record_not_found');
							$arg['data']       = array();
						}


					}
				}
			}
		}

		echo json_encode($arg);
	}

	public function service_appointment_details_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if($userdata['status'] != 1){
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					$this->form_validation->set_rules('service_id', 'Service Id', 'required|numeric',array(
						'required' => $this->lang->line('service_id_required'),
						'numeric' => $this->lang->line('service_id_required'),
					));
					$this->form_validation->set_rules('select_date', 'Select Date', 'required|numeric',array(
						'required' => $this->lang->line('select_date_required'),
						'numeric' => $this->lang->line('select_date_required'),
					));
					/* $this->form_validation->set_rules('client_id', 'Client', 'required|numeric',array(
						'required' => 'Client Id is required',
						'numeric' => 'Client Id is required',
					)); */

					if($this->form_validation->run() == FALSE)
            		{
						$arg['status']  = 0;
						$arg['error_code'] = 0;
						$arg['error_line']= __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$user_id      	=   $userdata['data']['id'];
						$response  		=   array();

						$service_id		=  $this->input->post('service_id');
						$business_id	=  $this->input->post('business_id');
						$select_date	=  date('Y-m-d', $this->input->post('select_date'));
						$client_id 		=  $this->input->post('client_id');

						$scheduledInfo = "SELECT service_scheduling_time.id, service.duration, service_scheduling_time.from_time, service_scheduling_time.to_time, service_scheduling_time.instructor_id FROM `service_scheduling` JOIN service_scheduling_time on (service_scheduling_time.scheduled_id = service_scheduling.id) JOIN service on (service.id = service_scheduling.service_id) WHERE service.business_id = ".$business_id." AND service.id = ".$service_id." AND service_scheduling_time.service_date = '".$select_date."' AND service_scheduling_time.instructor_id LIKE '%".$user_id."%'";

						$collection = $this->dynamic_model->getQueryResultArray($scheduledInfo);

						if(!empty($collection)){

							$obj_array = array();
							for ($i = 0; $i < count($collection); $i++) {
								$object = $collection[$i];
								$response = $this->dynamic_model->getQueryResultArray('SELECT id, start_time, end_time FROM service_scheduling_time_slot WHERE status = 0 AND service_scheduling_time_id = '.$object['id']);
								array_push($obj_array, $object['id']);
								if (empty($response)) {
									// date_default_timezone_set("Asia/Kolkata");
									$start_date = date('h:i A', $object['from_time']);
									$end_date   = date('h:i A', $object['to_time']);
									$insertArray = array();
									$range  =   range(strtotime($start_date),strtotime($end_date), $object['duration']*60);
									foreach($range as $time){
										$timestamp = $time + $object['duration']*60;
										$temp_array = array(
											'service_scheduling_time_id' => $object['id'],
											'start_time'    => get_str_to_time(date("h:i A",$time)),
											'end_time'      => get_str_to_time(date("h:i A",$timestamp)),
											'status'        => 0
										);
										array_push($insertArray, $temp_array);
									}   array_pop($insertArray);

									$this->db->insert_batch('service_scheduling_time_slot', $insertArray);
								}
							}
							$sId = implode(',', $obj_array);
							$response = $this->dynamic_model->getQueryResultArray('SELECT id, start_time, end_time FROM service_scheduling_time_slot WHERE status = 0 AND service_scheduling_time_id IN ('.$sId.')');

							$query = 'SELECT service.id as service_id, service.service_name, service.start_date_time, service.end_date_time, service.duration, service.amount as service_charge, service.tax1, service.tax2, service.tax1_label as tax_name, service.tax2_label as tax2_name, service.tax1_rate, service.tax2_rate, service.cancel_policy, service.tip_option, service.create_dt, manage_skills.id as skill_id, manage_skills.name, (SELECT business_location.location_name FROM `service_scheduling` JOIN business_location on (business_location.id = service_scheduling.location) WHERE service_id = service.id GROUP BY service_id) as location FROM service JOIN manage_skills on (manage_skills.id = service.service_type) WHERE service.id = '.$service_id.' AND service.business_id = '.$business_id;

							$service_data = $this->dynamic_model->getQueryRowArray($query);

							if (!empty($service_data)) {
								$temp = array();
								$temp['service_id']         = $service_data['service_id'];
								$temp['service_name']       = $service_data['service_name'];
								$temp['start_date_time']    = $service_data['start_date_time'];
								$temp['end_date_time']      = $service_data['end_date_time'];
								$temp['duration']           = $service_data['duration'];
								$temp['service_charge']     = floatVal($service_data['service_charge']);
								$temp['tax1']               = $service_data['tax1'];
								$temp['tax2']               = $service_data['tax2'];
								$temp['tax_name']           = $service_data['tax_name'];
								$temp['tax2_name']          = $service_data['tax2_name'];
								$temp['tax1_rate']          = ($service_data['tax1'] == 'Yes') ? floatVal($service_data['tax1_rate']) : 0;
								$temp['tax2_rate']          = ($service_data['tax2'] == 'Yes') ? floatVal($service_data['tax2_rate']) : 0;
								$temp['cancel_policy']      = $service_data['cancel_policy'];
								$temp['tip_option']         = $service_data['tip_option'];
								$temp['create_dt']          = date("d M Y ",$service_data['create_dt']);
								$temp['location']           = $service_data['location'];
								$temp['service_category'] = array(
									'id'            =>  $service_data['skill_id'],
									'category_name' =>  $service_data['name']
								);
								$service_data = $temp;
							}

							$query_info = 'SELECT user.id, user.name, user.lastname, user.profile_img, user.availability_status, instructor_details.total_experience as experience, instructor_details.appointment_fees, instructor_details.appointment_fees_type, instructor_details.skill FROM user join instructor_details on (instructor_details.user_id = user.id) where user.id = '.$user_id;

							$instructor = $this->dynamic_model->getQueryRowArray($query_info);
							$url = site_url() . 'uploads/user/'.$instructor['profile_img'];
							$instructor['profile_img'] = $url;
							$instructor['appointment_fees'] = floatVal($instructor['appointment_fees']);
							$skills = $instructor['skill'];
							$instructor['skill'] = $this->dynamic_model->getQueryRowArray('SELECT GROUP_CONCAT(name) as skill from manage_skills where id in ('.$skills.')')['skill'];
							$instructor['skill_details'] = $this->dynamic_model->getQueryResultArray('SELECT id, name as category_name from manage_skills where id in ('.$skills.')');

							$customer_details = 'SELECT name, lastname, profile_img, date_of_birth as dob, gender FROM user where id = '.$client_id;
							$userInfo = $this->dynamic_model->getQueryRowArray($customer_details);
							if (!empty($userInfo)) {
								$userInfo['profile_img'] = site_url() . 'uploads/user/'.$userInfo['profile_img'];
							}

							$arg['status']    = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
							$arg['data']       = array('slot_data' => $response, 'service_data' => $service_data, 'instructor_data' => $instructor, 'client_data' => $userInfo);
							$arg['message']    = $this->lang->line('record_found');


						} else {
							$arg['status']     = 0;
							$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['message']    = $this->lang->line('record_not_found');
							$arg['data']       = array();
						}

					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function confirm_appointment_details_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if($userdata['status'] != 1){
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {

					// 1 - class, 2 - workshop, 3 - service

					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					$this->form_validation->set_rules('select_date', 'Select Date', 'required|numeric',array(
						'required' => $this->lang->line('select_date_required'),
						'numeric' => $this->lang->line('select_date_required'),
					));

					$this->form_validation->set_rules('request_type', 'Request', 'required|numeric',array(
						'required' => 'Request type is required',
						'numeric' => 'Request type is required',
					));

					if ($this->input->post('request_type') == 3) {
						$this->form_validation->set_rules('service_id', 'Service Id', 'required|numeric',array(
							'required' => $this->lang->line('service_id_required'),
							'numeric' => $this->lang->line('service_id_required'),
						));

						$this->form_validation->set_rules('client_id', 'Client', 'required|numeric',array(
							'required' => 'Client Id is required',
							'numeric' => 'Client Id is required',
						));
					}

					if($this->form_validation->run() == FALSE)
            		{
						$arg['status']  = 0;
						$arg['error_code'] = 0;
						$arg['error_line']= __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$user_id      	=   $userdata['data']['id'];
						$response  		=   array();

						$business_id	=  $this->input->post('business_id');
						$select_date	=  date('Y-m-d', $this->input->post('select_date'));
						$client_id 		=  $this->input->post('client_id');
						$page_no 		= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no 		= $page_no-1;
						$limit    		= config_item('page_data_limit');
						$offset 		= $limit * $page_no;
						$request_type   =  $this->input->post('request_type');
						if ($request_type == 1) {

							$query = 'SELECT class_scheduling_time.id, class_scheduling_time.class_id, business_class.class_name, business_class.class_name, business_class.start_date, business_class.end_date, business_class.capacity,  business_class.class_type, class_scheduling_time.location_id, business_location.location_name, class_scheduling_time.day_id, manage_week_days.week_name, class_scheduling_time.from_time, class_scheduling_time.to_time FROM class_scheduling_time JOIN business_class on (business_class.id = class_scheduling_time.class_id) JOIN business_location on (business_location.id = class_scheduling_time.location_id) JOIN manage_week_days on (manage_week_days.id = class_scheduling_time.day_id) WHERE class_scheduling_time.scheduled_date = "'.date('Y-m-d', strtotime($select_date)).'" AND class_scheduling_time.business_id = '.$business_id.' AND class_scheduling_time.instructor_id = '.$user_id;

							$collection = $this->dynamic_model->getQueryResultArray($query);

							if (!empty($collection)) {

								array_walk ( $collection, function (&$key) {
									$time=time();
									$date = date("Y-m-d",$time);
									$key['class_type']   = get_categories($key['class_type']);
									if($key['end_date']>=$time){
					            	  	$key['status']="Complete";
					            	}else{
					            		$key['status']="Inprogress";
					            	}
									$capicty_used = get_checkin_class_or_workshop_count($key['class_id'],1,$time);
									$key['total_capacity']    = $key['capacity'];
			            			$key['capacity_used']     = $capicty_used;
			            			$page_no 		= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
									$page_no 		= $page_no-1;
									$limit    		= config_item('page_data_limit');
									$offset 		= $limit * $page_no;
			            		});

								$query_info = 'SELECT user.id, user.name, user.lastname, user.profile_img, user.availability_status, instructor_details.total_experience as experience, instructor_details.appointment_fees, instructor_details.appointment_fees_type, instructor_details.skill FROM user join instructor_details on (instructor_details.user_id = user.id) where user.id = '.$user_id;

								$instructor = $this->dynamic_model->getQueryRowArray($query_info);
								$url = site_url() . 'uploads/user/'.$instructor['profile_img'];
								$instructor['profile_img'] = $url;
								$instructor['appointment_fees'] = floatVal($instructor['appointment_fees']);
								$skills = $instructor['skill'];
								$instructor['skill'] = $this->dynamic_model->getQueryRowArray('SELECT GROUP_CONCAT(name) as skill from manage_skills where id in ('.$skills.')')['skill'];
								$instructor['skill_details'] = $this->dynamic_model->getQueryResultArray('SELECT id, name as category_name from manage_skills where id in ('.$skills.')');

								$arg['status']    = 1;
								$arg['error_code'] = REST_Controller::HTTP_OK;
								$arg['error_line']= __line__;
								// $arg['data']       = array('slot_data' => $response, 'collection' => $service_data, 'instructor_data' => $instructor, 'client_data' => $userInfo);
								$arg['data']       = array('slot_data' => $collection, 'instructor_data' => $instructor);
								$arg['message']    = $this->lang->line('record_found');

							} else {
								$arg['status']     = 0;
								$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line']= __line__;
								$arg['message']    = $this->lang->line('record_not_found');
								$arg['data']       = array();
							}

						} else if ($request_type == 2) {

						} else if ($request_type == 3) {
							$service_id		=  $this->input->post('service_id');
							$scheduledInfo = "SELECT service_scheduling_time.id, service.duration, service_scheduling_time.from_time, service_scheduling_time.to_time, service_scheduling_time.instructor_id FROM `service_scheduling` JOIN service_scheduling_time on (service_scheduling_time.scheduled_id = service_scheduling.id) JOIN service on (service.id = service_scheduling.service_id) WHERE service.business_id = ".$business_id." AND service.id = ".$service_id." AND service_scheduling_time.service_date = '".$select_date."' AND service_scheduling_time.instructor_id LIKE '%".$user_id."%'";

							$collection = $this->dynamic_model->getQueryResultArray($scheduledInfo);

							if(!empty($collection)){

								$obj_array = array();
								for ($i = 0; $i < count($collection); $i++) {
									$object = $collection[$i];
									$response = $this->dynamic_model->getQueryResultArray('SELECT id, start_time, end_time FROM service_scheduling_time_slot WHERE status = 0 AND service_scheduling_time_id = '.$object['id']);
									array_push($obj_array, $object['id']);
									if (empty($response)) {
										// date_default_timezone_set("Asia/Kolkata");
										$start_date = date('h:i A', $object['from_time']);
										$end_date   = date('h:i A', $object['to_time']);
										$insertArray = array();
										$range  =   range(strtotime($start_date),strtotime($end_date), $object['duration']*60);
										foreach($range as $time){
											$timestamp = $time + $object['duration']*60;
											$temp_array = array(
												'service_scheduling_time_id' => $object['id'],
												'start_time'    => get_str_to_time(date("h:i A",$time)),
												'end_time'      => get_str_to_time(date("h:i A",$timestamp)),
												'status'        => 0
											);
											array_push($insertArray, $temp_array);
										}   array_pop($insertArray);

										$this->db->insert_batch('service_scheduling_time_slot', $insertArray);
									}
								}

								$sId = implode(',', $obj_array);
								$response = $this->dynamic_model->getQueryResultArray('SELECT id, start_time, end_time FROM service_scheduling_time_slot WHERE status = 0 AND service_scheduling_time_id IN ('.$sId.')');

								$query = 'SELECT service.id as service_id, service.service_name, service.start_date_time, service.end_date_time, service.duration, service.amount as service_charge, service.tax1, service.tax2, service.tax1_label as tax_name, service.tax2_label as tax2_name, service.tax1_rate, service.tax2_rate, service.cancel_policy, service.tip_option, service.create_dt, manage_skills.id as skill_id, manage_skills.name, (SELECT business_location.location_name FROM `service_scheduling` JOIN business_location on (business_location.id = service_scheduling.location) WHERE service_id = service.id GROUP BY service_id) as location FROM service JOIN manage_skills on (manage_skills.id = service.service_type) WHERE service.id = '.$service_id.' AND service.business_id = '.$business_id;

								$service_data = $this->dynamic_model->getQueryRowArray($query);

								if (!empty($service_data)) {
									$temp = array();
									$temp['service_id']         = $service_data['service_id'];
									$temp['service_name']       = $service_data['service_name'];
									$temp['start_date_time']    = $service_data['start_date_time'];
									$temp['end_date_time']      = $service_data['end_date_time'];
									$temp['duration']           = $service_data['duration'];
									$temp['service_charge']     = floatVal($service_data['service_charge']);
									$temp['tax1']               = $service_data['tax1'];
									$temp['tax2']               = $service_data['tax2'];
									$temp['tax_name']           = $service_data['tax_name'];
									$temp['tax2_name']          = $service_data['tax2_name'];
									$temp['tax1_rate']          = ($service_data['tax1'] == 'Yes') ? floatVal($service_data['tax1_rate']) : 0;
									$temp['tax2_rate']          = ($service_data['tax2'] == 'Yes') ? floatVal($service_data['tax2_rate']) : 0;
									$temp['cancel_policy']      = $service_data['cancel_policy'];
									$temp['tip_option']         = $service_data['tip_option'];
									$temp['create_dt']          = date("d M Y ",$service_data['create_dt']);
									$temp['location']           = $service_data['location'];
									$temp['service_category'] = array(
										'id'            =>  $service_data['skill_id'],
										'category_name' =>  $service_data['name']
									);
									$service_data = $temp;
								}

								$query_info = 'SELECT user.id, user.name, user.lastname, user.profile_img, user.availability_status, instructor_details.total_experience as experience, instructor_details.appointment_fees, instructor_details.appointment_fees_type, instructor_details.skill FROM user join instructor_details on (instructor_details.user_id = user.id) where user.id = '.$user_id;

								$instructor = $this->dynamic_model->getQueryRowArray($query_info);
								$url = site_url() . 'uploads/user/'.$instructor['profile_img'];
								$instructor['profile_img'] = $url;
								$instructor['appointment_fees'] = floatVal($instructor['appointment_fees']);
								$skills = $instructor['skill'];
								$instructor['skill'] = $this->dynamic_model->getQueryRowArray('SELECT GROUP_CONCAT(name) as skill from manage_skills where id in ('.$skills.')')['skill'];
								$instructor['skill_details'] = $this->dynamic_model->getQueryResultArray('SELECT id, name as category_name from manage_skills where id in ('.$skills.')');

								$customer_details = 'SELECT name, lastname, profile_img, date_of_birth as dob, gender FROM user where id = '.$client_id;
								$userInfo = $this->dynamic_model->getQueryRowArray($customer_details);
								if (!empty($userInfo)) {
									$userInfo['profile_img'] = site_url() . 'uploads/user/'.$userInfo['profile_img'];
								}

								$arg['status']    = 1;
								$arg['error_code'] = REST_Controller::HTTP_OK;
								$arg['error_line']= __line__;
								$arg['data']       = array('slot_data' => $response, 'collection' => $service_data, 'instructor_data' => $instructor, 'client_data' => $userInfo);
								$arg['message']    = $this->lang->line('record_found');

							} else {
								$arg['status']     = 0;
								$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line']= __line__;
								$arg['message']    = $this->lang->line('record_not_found');
								$arg['data']       = array();
							}

						} else {
							$arg['status']     = 0;
							$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['message']    = $this->lang->line('record_not_found');
							$arg['data']       = array();
						}

					}
				}
			}
		}
		echo json_encode($arg);
	}


// customer search
 public function search_customer_list_post()
	{
	   $arg = array();
	   $userdata = checkuserid('1');
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
	      $_POST = json_decode(file_get_contents("php://input"), true);
		  if($_POST)
		  {
		    $this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
					'required' => $this->lang->line('page_no'),
					'numeric' => $this->lang->line('page_no_numeric'),
				));
		    $this->form_validation->set_rules('business_id','Business Id','required',array(
					'required' => $this->lang->line('business_id_req')
				));
		     $this->form_validation->set_rules('class_id','Class Id','required',array(
					'required' => $this->lang->line('class_id_req')
				));
		    $this->form_validation->set_rules('search_val', 'search value', 'required');
			if($this->form_validation->run() == FALSE)
			{
			  	$arg['status']  = 0;
			  	$arg['error_code'] = 0;
				$arg['error_line']= __line__;
			 	$arg['message'] = get_form_error($this->form_validation->error_array());
			}
			else
			{
				$usid   = $userdata['data']['id'];
				$response=array();
				$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
				$page_no= $page_no-1;
				$limit    = config_item('page_data_limit');
				$offset = $limit * $page_no;
				$search_val=  $this->input->post('search_val');
				$business_id=$this->input->post('business_id');
				$class_id=$this->input->post('class_id');

				if($search_val){
					$where= ' mobile_verified = 1 AND email_verified = 1 AND id != '.$usid.' AND (user.name LIKE "%'.$search_val.'%" OR user.lastname LIKE "%'.$search_val.'%" OR user.mobile LIKE "%'.$search_val.'%" OR user.email LIKE "%'.$search_val.'%")';
				}

				// $client_data = $this->dynamic_model->getdatafromtable('user',$where);
				$client_data = $this->dynamic_model->getdatafromtable('user',$where, '*', $limit, $offset);
				if($client_data){
					foreach($client_data as $value){
						if (!empty($value['name']) && !empty($value['lastname']) && !empty($value['mobile'])) {
							$user_id = $value['id'];

							$purchase_passes_data = $this->dynamic_model->getQueryResultArray('SELECT business_passes.id,business_passes.pass_id, business_passes.pass_name, business_passes.pass_validity, business_passes.pass_type, business_passes.pass_type_subcat, user_booking.passes_start_date as start_date, user_booking.passes_end_date as end_date, user_booking.sub_total FROM `user_booking` JOIN business_passes on (business_passes.id = user_booking.service_id) WHERE user_booking.business_id = '.$business_id.' and user_booking.service_type = 1 and user_booking.user_id = '.$user_id.' and passes_status = 1 and user_booking.status != "Pending"');

					 		//$where= ' business_id = "'.$business_id.'" AND class_id = "'.$class_id.'" AND service_type = "1" AND passes_status = "1" AND user_id = "'.$user_id.'"';
							//$pass_data = $this->dynamic_model->getdatafromtable('user_booking',$where);
							$pass_id = "";

							/* if(!empty($purchase_passes_data)){
								$pass_id = $pass_data[0]['service_id'];
							} */

							$clientdata['pass_id']     = $pass_id;
							$clientdata['id']     = $value['id'];
							$usid = $value['id'];
							$clientdata['name']   = ucwords($value['name'].' '.$value['lastname']);
							$clientdata['email']  = $value['email'];
							$clientdata['profile_img']  = base_url().'uploads/user/'.$value['profile_img'];
							$clientdata['country_code'] = !empty($value['country_code']) ? $value['country_code'] : '';
							$clientdata['mobile'] = $value['mobile'];
							$clientdata['date_of_birth'] =  !empty($value['date_of_birth']) ? $value['date_of_birth'] : '';
							$clientdata['gender'] = $value['gender'];

							$this->db->select('b.*');
	                        $this->db->from('business_passes_associates as bpa');
	                        $this->db->join('business_passes b', 'b.id = bpa.pass_id');
	                        $this->db->where('bpa.business_id',$business_id);
	                        $this->db->where('bpa.class_id',$class_id);
	                        $this->db->where('b.status',"Active");
	                        $passes_data = $this->db->get()->result_array();
	                        $st = $this->db->last_query();
	                       // echo $st; die;
	                        $pass_id_array = array();
							/* if(!empty($passes_data)){
							    foreach($passes_data as $values)
					            {
					            	$pass_id_array[] = $values['id'];
					            }
							} */

							if(!empty($purchase_passes_data)){
							    foreach($purchase_passes_data as $values)
					            {
					            	$pass_id_array[] = $values['id'];
					            }
							}



					        if (!empty($pass_id_array)) {
	                            $pass_id_array = implode(",",$pass_id_array);
	                            $sql = "SELECT * FROM user_booking WHERE user_id = '".$value['id']."' && passes_status = '1' && service_id IN ($pass_id_array)";
	                            $my_passes_data = $this->dynamic_model->getQueryResultArray($sql);
                                $pass_arr = array();
	                            if(!empty($my_passes_data)){
	                                foreach($my_passes_data as $valuess)
	                                {
	                                    $passesdata=getpassesdetails($valuess['service_id'],$usid);

	                                    $passesdata['start_date'] = date('d M Y ',$valuess['passes_start_date']);
	                                    $passesdata['end_date'] = date('d M Y ',$valuess['passes_end_date']);

	                                    $passesdata['start_date_utc'] = $valuess['passes_start_date'];
	                                    $passesdata['end_date_utc'] = $valuess['passes_end_date'];
	                                    $business_ids = $valuess['business_id'];
	                                    $business_info = $this->dynamic_model->getQueryRowArray('SELECT *  FROM business WHERE id = '.$business_ids);
	                                    $passesdata['business_logo'] =  empty($business_info['business_image']) ? '' : site_url().'uploads/business/'.$business_info['business_image'];
	                                    $pass_arr[]   = $passesdata;
	                                }
	                            }
                            	$clientdata['my_passes_details'] = $pass_arr;
	                        }else{
	                            $clientdata['my_passes_details'] = array();
	                        }


							$response[]	          = $clientdata;


						}


					}
				    $arg['status']    = 1;
					$arg['error_code'] = HTTP_OK;
					$arg['error_line']= __line__;
				 	$arg['data']      = $response;
				 	$arg['message']   = $this->lang->line('record_found');
				}
				else{
					$arg['status']     = 0;
					$arg['error_code']  = REST_Controller::HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = array();
				 	$arg['message']    = $this->lang->line('record_not_found');
				}
		    }
		  }
		}
	   echo json_encode($arg);
	}

 public function search_customer_for_workshop_post()
	{
	   $arg = array();
	   $userdata = checkuserid('1');
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
	      $_POST = json_decode(file_get_contents("php://input"), true);
		  if($_POST)
		  {
		    $this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
					'required' => $this->lang->line('page_no'),
					'numeric' => $this->lang->line('page_no_numeric'),
				));
		    /*$this->form_validation->set_rules('business_id','Business Id','required',array(
					'required' => $this->lang->line('business_id_req')
				));*/
		    $this->form_validation->set_rules('workshop_id','Class Id','required',array(
					'required' => $this->lang->line('class_id_req')
				));
		    $this->form_validation->set_rules('search_val', 'search value', 'required');
			if($this->form_validation->run() == FALSE)
			{
			  	$arg['status']  = 0;
			  	$arg['error_code'] = 0;
				$arg['error_line']= __line__;
			 	$arg['message'] = get_form_error($this->form_validation->error_array());
			}
			else
			{
				$usid   = $userdata['data']['id'];
				$response=array();
				$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
				$page_no= $page_no-1;
				$limit    = config_item('page_data_limit');
				$offset = $limit * $page_no;
				$search_val=  $this->input->post('search_val');
				$business_id=$this->input->post('business_id');
				$workshop_id=$this->input->post('workshop_id');

				if($search_val){
					$where= ' mobile_verified = 1 AND email_verified = 1 AND id != '.$usid.' AND (user.name LIKE "%'.$search_val.'%" OR user.lastname LIKE "%'.$search_val.'%" OR user.mobile LIKE "%'.$search_val.'%" OR user.email LIKE "%'.$search_val.'%")';
				}

				$client_data = $this->dynamic_model->getdatafromtable('user',$where, '*', $limit, $offset);
				if($client_data){
					foreach($client_data as $value){
						if (!empty($value['name']) && !empty($value['lastname']) && !empty($value['mobile'])) {
							$user_id = $value['id'];

							$purchase_data = $this->dynamic_model->getQueryResultArray('SELECT bwm.* FROM `user_booking` as ub JOIN business_workshop_master as bwm on (bwm.id = ub.service_id) WHERE ub.business_id = '.$business_id.' and ub.service_id = '.$workshop_id.' and ub.user_id = '.$user_id.' and ub.passes_status = 1 and ub.status != "Pending"');


					 		$is_purchase = 0;
					 		if(!empty($purchase_data)){
					 			$is_purchase = 1;
					 		}

							$clientdata['workshop_id']     = $workshop_id;
							$clientdata['is_purchase']     = $is_purchase;
							$clientdata['id']     = $value['id'];
							$clientdata['name']   = ucwords($value['name'].' '.$value['lastname']);
							$clientdata['email']  = $value['email'];
							$clientdata['profile_img']  = base_url().'uploads/user/'.$value['profile_img'];
							$clientdata['country_code'] = !empty($value['country_code']) ? $value['country_code'] : '';
							$clientdata['mobile'] = $value['mobile'];
							$clientdata['date_of_birth'] =  !empty($value['date_of_birth']) ? $value['date_of_birth'] : '';
							$clientdata['gender'] = $value['gender'];
							$response[]	          = $clientdata;
						}
					}
				    $arg['status']    = 1;
					$arg['error_code'] = HTTP_OK;
					$arg['error_line']= __line__;
				 	$arg['data']      = $response;
				 	$arg['message']   = $this->lang->line('record_found');
				}
				else{
					$arg['status']     = 0;
					$arg['error_code']  = REST_Controller::HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = array();
				 	$arg['message']    = $this->lang->line('record_not_found');
				}
		    }
		  }
		}
	   echo json_encode($arg);
	}

// search customer for searvuces
public function search_customer_for_services_post()
{
   $arg = array();
   $userdata = checkuserid('1');
   if($userdata['status'] != 1){
	 $arg = $userdata;
	}
	else
	{
      $_POST = json_decode(file_get_contents("php://input"), true);
	  if($_POST)
	  {
	    $this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
				'required' => $this->lang->line('page_no'),
				'numeric' => $this->lang->line('page_no_numeric'),
			));
	    $this->form_validation->set_rules('business_id','Business Id','required',array(
				'required' => $this->lang->line('business_id_req')
			));
	    $this->form_validation->set_rules('search_val', 'search value', 'required');
		if($this->form_validation->run() == FALSE)
		{
		  	$arg['status']  = 0;
		  	$arg['error_code'] = 0;
			$arg['error_line']= __line__;
		 	$arg['message'] = get_form_error($this->form_validation->error_array());
		}
		else
		{
			$usid   = $userdata['data']['id'];
			$response=array();
			$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
			$page_no= $page_no-1;
			$limit    = config_item('page_data_limit');
			$offset = $limit * $page_no;
			$search_val=  $this->input->post('search_val');
			$business_id=$this->input->post('business_id');

			if($search_val){
				$where= ' mobile_verified = 1 AND email_verified = 1 AND id != '.$usid.' AND (user.name LIKE "%'.$search_val.'%" OR user.lastname LIKE "%'.$search_val.'%" OR user.mobile LIKE "%'.$search_val.'%" OR user.email LIKE "%'.$search_val.'%")';
			}

			$client_data = $this->dynamic_model->getdatafromtable('user',$where, '*', $limit, $offset);
			if($client_data){
				foreach($client_data as $value){
					if (!empty($value['name']) && !empty($value['lastname']) && !empty($value['mobile'])) {
						$user_id = $value['id'];
						$clientdata['id']     = $value['id'];
						$clientdata['name']   = ucwords($value['name'].' '.$value['lastname']);
						$clientdata['email']  = $value['email'];
						$clientdata['profile_img']  = base_url().'uploads/user/'.$value['profile_img'];
						$clientdata['country_code'] = !empty($value['country_code']) ? $value['country_code'] : '';
						$clientdata['mobile'] = $value['mobile'];
						$clientdata['date_of_birth'] =  !empty($value['date_of_birth']) ? $value['date_of_birth'] : '';
						$clientdata['gender'] = $value['gender'];
						$response[]	          = $clientdata;
					}
				}
			    $arg['status']    = 1;
				$arg['error_code'] = HTTP_OK;
				$arg['error_line']= __line__;
			 	$arg['data']      = $response;
			 	$arg['message']   = $this->lang->line('record_found');
			}
			else{
				$arg['status']     = 0;
				$arg['error_code']  = REST_Controller::HTTP_OK;
				$arg['error_line']= __line__;
				$arg['data']       = array();
			 	$arg['message']    = $this->lang->line('record_not_found');
			}
	    }
	  }
	}
   echo json_encode($arg);
}
	// -------
	// new client signup
 public function new_client_signup_post()
	{
	   $arg = array();
	   $userdata = checkuserid('1');
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
	      $_POST = json_decode(file_get_contents("php://input"), true);
		  if($_POST)
		  {
		    $this->form_validation->set_rules('user_id', 'User id', 'required');
		    $this->form_validation->set_rules('business_id','Business Id','required');
		    $this->form_validation->set_rules('class_id','Class Id','required');
		    $this->form_validation->set_rules('schedule_id', 'Schedule id', 'required');
		    $this->form_validation->set_rules('instractor_id', 'Instractor id', 'required');
		    $this->form_validation->set_rules('pass_id', 'Pass id', 'required');
			if($this->form_validation->run() == FALSE)
			{
			  	$arg['status']  = 0;
			  	$arg['error_code'] = 0;
				$arg['error_line']= __line__;
			 	$arg['message'] = get_form_error($this->form_validation->error_array());
			}
			else
			{
				$usid   = $userdata['data']['id'];
				$response=array();

				$user_id=  $this->input->post('user_id');
				$business_id=$this->input->post('business_id');
				$class_id=$this->input->post('class_id');
				$schedule_id=$this->input->post('schedule_id');
				$instractor_id=$this->input->post('instractor_id');
				$pass_id=$this->input->post('pass_id');
				$service_type = 1;
				$time = time();
				$status = 'singup';
				$scheduled_date = date('Y-m-d');

		        $day_update = 1;
                if(!empty($pass_id)){
                    $whe="id = '".$pass_id."'";
                    $passes_data = $this->dynamic_model->getdatafromtable('business_passes',$whe);
                    if(!empty($passes_data)){
                        $pass_type = $passes_data[0]['pass_type'];
                        if($pass_type == '10' || $pass_type == '37'){
                            $day_update = 0;
                        }
                    }
                }




				$where= ' id = "'.$schedule_id.'"';
				$class_data = $this->dynamic_model->getdatafromtable('class_scheduling_time',$where);
				if(!empty($class_data)){
					$scheduled_date = $class_data[0]['scheduled_date'];

					    $getClassData = $this->dynamic_model->getQueryRowArray('SELECT * FROM business_class where id = '. $class_id);
						if(!empty($getClassData)){
							$class_days_prior_signup = $getClassData['class_days_prior_signup'];

							$start_date = strtotime($scheduled_date);
							$unixTimestamp = $start_date - ((int)$class_days_prior_signup*24*60*60);
							$today = time();
							if($today >= $unixTimestamp){

							}else{
							$unixTimestamp = date('Y-m-d',$unixTimestamp);
							$arg['status']     = 0;
							$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['message']    = $class_days_prior_signup.' day prior, You will be signup this class.';
							echo json_encode($arg);exit;
							}
						}

				}


				$where= ' user_id = "'.$user_id.'" AND business_id = "'.$business_id.'" AND service_id = "'.$pass_id.'" AND status = "Success" AND passes_status = "1" AND passes_remaining_count != "0"';
				$pass_data = $this->dynamic_model->getdatafromtable('user_booking',$where);

				if (empty($pass_data)) {
							$arg['status']     = 0;
                            $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
                            $arg['error_line']= __line__;
                            $arg['data']       = json_decode('{}');
                            $arg['message']    = 'Please purchase pass then you can singup';
                            echo json_encode($arg);exit;
                        }


				if($pass_data){


					 $whe="user_id = '".$user_id."' AND schedule_id = '".$schedule_id."' AND service_id = '".$class_id."' AND status = 'cancel'";
                        $data_check = $this->dynamic_model->getdatafromtable('user_attendance',$whe);
                        if (!empty($data_check)) {
                            # code...
                            $where1 = array('id'=>$data_check[0]['id']);
                            $deleteCart= $this->dynamic_model->deletedata('user_attendance',$where1);
                        }







                        	$passes_remaining_count='';
                            $user_booking_id = $pass_data[0]['id'];
                            $passes_remaining_count = ($pass_data[0]['passes_remaining_count'] - 1);
                            $updateData =   array(
                                        'passes_remaining_count' =>  $passes_remaining_count
                                    );
                            if(!empty($day_update)){
                            	$this->dynamic_model->updateRowWhere('user_booking', array('id' => $user_booking_id), $updateData);
                        	}

					$insertData =   array(
											'user_id'  		=>$user_id,
											'service_id'    =>$class_id,
											'schedule_id'   =>$schedule_id,
											'service_type'  =>$service_type,
											'status'  		=>$status,
											'pass_id'  		=>$pass_id,
											'checkin_dt'    => $scheduled_date,
											'checkin_time'  =>0,
											'checkout_time' =>0,
											'signup_status' =>1,
											'create_dt'   	=>$time,
											'update_dt'   	=>$time,
											'signup_status' =>1,
											'checked_by'    =>$instractor_id
										   );


				$getSchedule = $this->dynamic_model->getQueryRowArray('SELECT * FROM class_scheduling_time where id = '. $schedule_id);
				//echo '--',$schedule_id; die;
				if(!empty($getSchedule)){
					$date = @$getSchedule['scheduled_date'];
				}else{
					$time=time();
					$date = date("Y-m-d",$time);
				}

					$check_user_entry = $this->db->get_where('user_attendance', array(
						'user_id' => $user_id,
						'service_id' => $class_id,
						'schedule_id' => $schedule_id,
						'checkin_dt' => $date
					))->num_rows();

					if ($check_user_entry > 0) {
						$arg['status']    = 0;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line']= __line__;
						$arg['message']   = 'The client already attends this class.';
					} else {


						$this->dynamic_model->insertdata('user_attendance',$insertData);
						$arg['status']    = 1;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line']= __line__;
						$arg['message']   = $this->lang->line('check_signup_succ');
					}

				}
				else{
					$arg['status']     = 0;
					$arg['error_code']  = REST_Controller::HTTP_OK;
					$arg['error_line']= __line__;
			 		$arg['message']    = 'please purchase pass';
				}
		    }
		  }
		}
	   echo json_encode($arg);
	}

	public function search_customer_details_post()
	{
	   $arg = array();
	   $userdata = checkuserid('1');
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
	    	$_POST = json_decode(file_get_contents("php://input"), true);
		  	if($_POST)
		  	{
				$this->form_validation->set_rules('business_id', 'Business Id', 'required',array(
					'required' => 'Business Id is required',
					'numeric' => 'Business Id is required',
				));

				$this->form_validation->set_rules('client_id', 'Client Id', 'required',array(
					'required' => 'Client Id is required',
					'numeric' => 'Client Id is required',
				));

				if($this->form_validation->run() == FALSE)
				{
					$arg['status']  = 0;
					$arg['error_code'] = 0;
					$arg['error_line']= __line__;
					$arg['message'] = get_form_error($this->form_validation->error_array());

				} else {
					$response	=	array();

						$time 		=	time();

						$business_id	= $this->input->post('business_id');
						$class_id	= $this->input->post('class_id');
						$user_id 		= $this->input->post('client_id');
						$business_info = $this->dynamic_model->getQueryRowArray('SELECT * FROM business where id = '.$business_id);
						$where 			= ' id = '.$user_id;
						$client_details = $this->dynamic_model->getdatafromtable('user',$where, 'id, name, lastname, email, profile_img, country_code, mobile, date_of_birth, gender');

						$couter = $this->db->get_where('user', array('id' => $user_id))->num_rows();

						if ($couter) {
							$product_ids = array();
							$passes_ids = array();
							$response  = array();
							$whe=array("user_id"=>$user_id, "business_id"=>$business_id,"status"=>"Pending");
            				$total_item=getdatacount('user_booking',$whe);
            				$search_user_id='';
							foreach($client_details as $value) {
								if (!empty($value['name']) && !empty($value['lastname']) && !empty($value['mobile'])) {
									$search_user_id=$value['id'];
									$clientdata['id'] = $value['id'];
									$clientdata['name']   = ucwords($value['name'].' '.$value['lastname']);
									$clientdata['email']   = $value['email'];
									$clientdata['mobile']   = $value['mobile'];
									$clientdata["profile_img"] = base_url().'uploads/user/'.$value['profile_img'];
									$clientdata['country_code'] = !empty($value['country_code']) ? $value['country_code'] : '';
									$clientdata['date_of_birth'] =  !empty($value['date_of_birth']) ? $value['date_of_birth'] : '';

									$clientdata['total_item']    = $total_item;

									$where = ' business_id = "'.$business_id.'" AND service_type = "1" AND passes_status IN (1, 3) AND user_id = "'.$user_id.'"';
									$purchase_product_data = $this->dynamic_model->getQueryResultArray('SELECT DISTINCT business_product.id, user_booking.id as user_booking_id,business_product.product_name, business_product.description, business_product.product_id, business_product.price, business_product.tax1, business_product.tax2, business_product.tax1_rate, business_product.tax2_rate, business_product_images.image_name as pro_image FROM user_booking JOIN business_product ON (business_product.id = user_booking.service_id) JOIN business_product_images on (business_product_images.product_id = business_product.id) WHERE user_booking.business_id = '.$business_id.' AND user_booking.service_type = 3 AND user_booking.user_id = "'.$user_id.'" AND user_booking.status != "Pending"');

									$product = array();
									if (!empty($purchase_product_data)) {

										foreach($purchase_product_data as $pro) {
											$info['product_name'] = $pro['product_name'];
											$info['product_id'] = $pro['product_id'];
											$info['product_description'] = $pro['description'];
											$info['price'] = $pro['price'];
											$info['tax1'] = $pro['tax1'];
											$info['tax1_rate'] = ($pro['tax1'] == 'Yes') ? $pro['tax1_rate'] : '0';
											$info['tax2'] = $pro['tax2'];
											$info['tax2_rate'] = ($pro['tax2'] == 'Yes') ? $pro['tax2_rate'] : '0';
											$info['pro_image'] = base_url().'uploads/products/'.$pro['pro_image'];
											$info['studio_logo'] = base_url().'uploads/business/'.$business_info['logo'];
											array_push($product_ids, $pro['id']);
											$product[] = $info;
										}
									}
									$clientdata['purchase_product_data'] = $product;

									$purchase_passes_data = $this->dynamic_model->getQueryResultArray('SELECT user_booking.id as user_booking_id,business_passes.id,business_passes.pass_id, business_passes.pass_name, business_passes.pass_validity, business_passes.pass_type, business_passes.pass_type_subcat, user_booking.passes_start_date as start_date, user_booking.passes_end_date as end_date, user_booking.sub_total FROM `user_booking` JOIN business_passes on (business_passes.id = user_booking.service_id) WHERE user_booking.business_id = '.$business_id.' and user_booking.service_type = 1 and user_booking.user_id = '.$user_id.' and passes_status = 1 and user_booking.status != "Pending"');


									$passes = array();
									if (!empty($purchase_passes_data)) {
										foreach($purchase_passes_data as $pass) {
											array_push($passes_ids, $pass['id']);
											$collection['user_booking_id'] = $pass['user_booking_id'];
											$collection['id'] = $pass['id'];
											$collection['pass_id'] = $pass['pass_id'];
											$collection['pass_name'] = $pass['pass_name'];
											$collection['pass_type'] = get_passes_type_name($pass['pass_type']);
											$collection['pass_type_subcat'] = get_passes_type_name($pass['pass_type'], $pass['pass_type_subcat']);
											$collection['pass_validity'] = $pass['pass_validity'];
											$collection['start_date'] = $pass['start_date'];
											$collection['end_date'] = $pass['end_date'];


											$collection['sub_total'] = $pass['sub_total'];
											$collection['studio_logo'] = base_url().'uploads/business/'.$business_info['logo'];
											$passes[] = $collection;
										}
									}

									$clientdata['purchase_passes_data'] = $passes;

									$queryPro = "SELECT (SELECT id FROM user_booking as u where u.service_id = business_product.id AND u.status = 'Pending' AND u.user_id = '".$user_id."' and u.service_type = '3') as added_incart, business_product.id, business_product.product_name, business_product.price, business_product_images.image_name as pro_image, business_product.product_id, business_product.quantity, business_product.description, business_product.tax1, business_product.tax2, business_product.tax1_rate, business_product.tax2_rate  FROM `business_product` JOIN business_product_images on (business_product_images.product_id = business_product.id) WHERE business_product.status = 'Active' AND business_product.business_id ='".$business_id."' GROUP by business_product.id";
									if (!empty($product_ids)) {
										// $queryPro .= ' AND business_product.id NOT IN ('.implode(',', $product_ids).')';
									}

									$avl_product = array();
									$avaliableProduct = $this->dynamic_model->getQueryResultArray($queryPro);
									if (!empty($avaliableProduct)) {
										foreach($avaliableProduct as $pro) {
											$avaliable_product['id'] = $pro['id'];
											$avaliable_product['added_incart'] = $pro['added_incart'] ? 1 : 0;
											$avaliable_product['product_name'] = $pro['product_name'];
											$avaliable_product['price'] = $pro['price'];
											$avaliable_product['pro_image'] = base_url().'uploads/products/'.$pro['pro_image'];
											$avaliable_product['studio_logo'] = base_url().'uploads/business/'.$business_info['logo'];
											$avaliable_product['product_id'] = $pro['product_id'];
											$avaliable_product['quantity'] = $pro['quantity'];
											$avaliable_product['description'] = $pro['description'];
											$avaliable_product['tax1'] = $pro['tax1'];
											$avaliable_product['tax2'] = $pro['tax2'];
											$avaliable_product['tax1_rate'] = ($pro['tax1'] == 'Yes') ? $pro['tax1_rate'] : '0';
											$avaliable_product['tax2_rate'] = ($pro['tax2'] == 'Yes') ? $pro['tax2_rate'] : '0';
											$avl_product[] = $avaliable_product;
										}
									}

									$clientdata['avaliable_product_data']    =  $avl_product;

									$sql = "SELECT * FROM user_booking WHERE service_type = 1 AND status = 'Success' AND passes_status = '1' AND user_id = $search_user_id";
									$query = $this->db->query($sql)->result_array();
									$pass_id = '';
									if (!empty($query)) {
										foreach ($query as $key => $value) {
											$pass_id .= $value['service_id'].',';
											$pass_id_array[] = $value['service_id'];
										}
										$pass_id = rtrim($pass_id,",");
									}


									if (!empty($pass_id)) {
										if(!empty($class_id)){
											$queryPasses = "SELECT (SELECT id FROM user_booking as u where u.service_id = buss.id AND u.status = 'Pending' AND u.user_id = '".$user_id."' and u.service_type = '1') as added_incart, (SELECT u.id FROM user_booking as u where u.service_id = pass.pass_id AND u.status = 'Success' AND u.passes_status = '1' AND u.service_type = '1' AND u.business_id = '".$business_id."' AND u.user_id = '".$user_id."' ) as user_booking_id,buss.id, buss.pass_name, buss.pass_type, buss.pass_type_subcat, buss.purchase_date, buss.pass_validity, buss.amount, buss.pass_id, buss.tax1, buss.tax2, buss.tax1_rate, buss.tax2_rate from business_passes_associates AS pass JOIN business_passes as buss on (buss.id = pass.pass_id) WHERE pass.business_id = ".$business_id." AND pass.class_id = ".$class_id." AND buss.status = 'Active' AND buss.id NOT IN (".$pass_id.") GROUP by pass.pass_id";
										}else{
											$queryPasses = "SELECT (SELECT id FROM user_booking as u where u.service_id = buss.id AND u.status = 'Pending' AND u.user_id = '".$user_id."' and u.service_type = '1') as added_incart, (SELECT u.id FROM user_booking as u where u.service_id = pass.pass_id AND u.status = 'Success' AND u.passes_status = '1' AND u.service_type = '1' AND u.business_id = '".$business_id."' AND u.user_id = '".$user_id."' ) as user_booking_id,buss.id, buss.pass_name, buss.pass_type, buss.pass_type_subcat, buss.purchase_date, buss.pass_validity, buss.amount, buss.pass_id, buss.tax1, buss.tax2, buss.tax1_rate, buss.tax2_rate from business_passes_associates AS pass JOIN business_passes as buss on (buss.id = pass.pass_id) WHERE pass.business_id = ".$business_id." AND buss.status = 'Active' AND buss.id NOT IN (".$pass_id.")  GROUP by pass.pass_id";
										}
									}
									else
									{
										if(!empty($class_id)){
											$queryPasses = "SELECT (SELECT id FROM user_booking as u where u.service_id = buss.id AND u.status = 'Pending' AND u.user_id = '".$user_id."' and u.service_type = '1') as added_incart, (SELECT u.id FROM user_booking as u where u.service_id = pass.pass_id AND u.status = 'Success' AND u.passes_status = '1' AND u.service_type = '1' AND u.business_id = '".$business_id."' AND u.user_id = '".$user_id."' ) as user_booking_id,buss.id, buss.pass_name, buss.pass_type, buss.pass_type_subcat, buss.purchase_date, buss.pass_validity, buss.amount, buss.pass_id, buss.tax1, buss.tax2, buss.tax1_rate, buss.tax2_rate from business_passes_associates AS pass JOIN business_passes as buss on (buss.id = pass.pass_id) WHERE pass.business_id = ".$business_id." AND pass.class_id = ".$class_id." AND buss.status = 'Active' GROUP by pass.pass_id";
										}else{
											$queryPasses = "SELECT (SELECT id FROM user_booking as u where u.service_id = buss.id AND u.status = 'Pending' AND u.user_id = '".$user_id."' and u.service_type = '1') as added_incart, (SELECT u.id FROM user_booking as u where u.service_id = pass.pass_id AND u.status = 'Success' AND u.passes_status = '1' AND u.service_type = '1' AND u.business_id = '".$business_id."' AND u.user_id = '".$user_id."' ) as user_booking_id,buss.id, buss.pass_name, buss.pass_type, buss.pass_type_subcat, buss.purchase_date, buss.pass_validity, buss.amount, buss.pass_id, buss.tax1, buss.tax2, buss.tax1_rate, buss.tax2_rate from business_passes_associates AS pass JOIN business_passes as buss on (buss.id = pass.pass_id) WHERE pass.business_id = ".$business_id." AND buss.status = 'Active'  GROUP by pass.pass_id";
										}
									}


									$clientdata['passes_ids']    =  $passes_ids;

									// '.implode(',', $passes_ids).'
									if (!empty($passes_ids)) {
										// $queryPasses .= ' AND buss.id NOT IN ('.implode(',', $passes_ids).')';
									}

									if (!empty($pass_id)) {
										$avaliable_pass = $this->dynamic_model->getQueryResultArray($queryPasses);
									} else {
										//$avaliable_pass = array();
										$avaliable_pass = $this->dynamic_model->getQueryResultArray($queryPasses);
									}

									$avl_pass = array();
									if (!empty($avaliable_pass)) {

										foreach($avaliable_pass as $avpass) {
											//$pass_collection['query'] = $queryPasses;
											$pass_collection['user_booking_id'] = $avpass['user_booking_id'];
											$pass_collection['id'] = $avpass['id'];
											$pass_collection['added_incart'] = $avpass['added_incart'] ? 1 : 0;
											$pass_collection['pass_name'] = $avpass['pass_name'];

											$pass_type = get_passes_type_name($avpass['pass_type']);
											$amount = $avpass['amount'];

											if($avpass['pass_type_subcat'] == '36'){
												$pass_type = $pass_type;
											}

											$pass_collection['pass_type_name'] = $pass_type;
											$pass_collection['pass_type'] = $pass_type;
											$pass_collection['pass_type_subcat'] = get_passes_type_name($avpass['pass_type'], $avpass['pass_type_subcat']);

											if($avpass['pass_validity'] > '1'){
												$pass_validity = $avpass['pass_validity']. ' Days';
											}else if($avpass['pass_validity'] == '1'){
												$pass_validity = $avpass['pass_validity']. ' Day';
											}else{
												$pass_validity = $avpass['pass_validity'];
											}

											$pass_collection['pass_validity'] = $pass_validity;

											$pass_type_subcat = $avpass['pass_type_subcat'];

											if($pass_type_subcat == '36'){
												$today_dt = date('d');
												$a_date = date("Y-m-d");
												$lastmonth_dt = date("t", strtotime($a_date));
												$diff_dt = $lastmonth_dt - $today_dt;
												$diff_dt = $diff_dt + 1;

												$rt = date("Y-m-t", strtotime($a_date));
												$recurring_date = $rt;
												$pass_end_date = strtotime($rt);
												$passes_remaining_count = $diff_dt;

												$per_day_amt = $amount/$lastmonth_dt;

												$per_day_amt = round($per_day_amt,2);
												$Amt = $per_day_amt * $diff_dt;

												$amount = number_format($Amt, 2);
											}

											$pass_collection['amount'] = $amount;
											$pass_collection['pass_id'] = $avpass['pass_id'];
											$pass_collection['tax1'] = $avpass['tax1'];
											$pass_collection['tax2'] = $avpass['tax2'];
											$pass_collection['tax1_rate'] = ($avpass['tax1'] == 'yes') ? $avpass['tax1_rate'] : '0';
											$pass_collection['tax2_rate'] = ($avpass['tax2'] == 'yes') ? $avpass['tax2_rate'] : '0';
											$pass_collection['purchase_start_date'] = $avpass['purchase_date'];
											$pass_collection['purchase_end_date'] = (string)(($avpass['pass_validity'] * 24 * 60 * 60) + $avpass['purchase_date']);
											$pass_collection['studio_logo'] = base_url().'uploads/business/'.$business_info['logo'];
											$avl_pass[] = $pass_collection;
										}

									}

									$clientdata['avaliable_passes_data']    = $avl_pass;

									$response[] = $clientdata;

								}

							}


							$arg['status']    = 1;
							$arg['error_code'] = HTTP_OK;
							$arg['error_line']= __line__;
							$arg['data']      = $response[0];
							$arg['message']   = $this->lang->line('record_found');
						} else {
							$arg['status']     = 0;
							$arg['error_code']  = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
							$arg['data']       = array();
							$arg['message']    = $this->lang->line('record_not_found');
						}

				}
			}
		}

		echo json_encode($arg);
	}

	public function get_user_profile_post()
	{
		$_POST = json_decode(file_get_contents("php://input"), true);
		if($_POST)
		{
			$arg = array();
			$this->form_validation->set_rules('customer_id', 'customer id', 'required');
	        if ($this->form_validation->run() == FALSE)
			{
			 	$arg['status']  = 0;
	            $arg['error']   = ERROR_FAILED_CODE;
	            $arg['message'] = get_form_error($this->form_validation->error_array());
			}else{
				$customer_id=$this->input->post('customer_id');
				$userdata = getuserdetail($customer_id);
				if(!empty($userdata)){
					$arg['status']     = 1;
					$arg['error_code']  = REST_Controller::HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = $userdata;
					$arg['message']    = $this->lang->line('profile_details');
				}else{
					$arg['status']     = 0;
					$arg['error_code']  = REST_Controller::HTTP_NOT_MODIFIED;
					$arg['error_line']= __line__;
					$arg['data']       = json_decode('{}');
				 	$arg['message']    = $this->lang->line('record_not_found');
				}
			}
		}else{
			$arg['status']  = 0;
            $arg['error_code'] =  ERROR_FAILED_CODE;
            $arg['error_line']= __line__;
            $arg['message'] = 'Invalid Details';
            $arg['data']      =json_decode('{}');
		}
		echo json_encode($arg);
	}

	public function add_cart_post() {
		$arg   = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
		   	$userdata = checkuserid();
		   	if($userdata['status'] != 1){
				$arg = $userdata;
			}
			else
			{
				$response=array();
				$time=time();

				$usid 		= decode($userdata['data']['id']);

				$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
				{

					$this->form_validation->set_rules('service_type','Service Type','required|trim', array( 'required' => $this->lang->line('service_type_required')));
				    $this->form_validation->set_rules('service_id','Service Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
				    $this->form_validation->set_rules('amount','Amount', 'required', array( 'required' => $this->lang->line('amount_required')));
					$this->form_validation->set_rules('quantity','Quantity', 'required', array( 'required' => $this->lang->line('quantity_required')));
					$this->form_validation->set_rules('client_id', 'Client Id', 'required',array(
						'required' => 'Client is required',
						'numeric' => 'Client is required',
					));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required',array(
						'required' => 'Business Id is required',
						'numeric' => 'Business Id is required',
					));

					if($this->form_validation->run() == FALSE)
					{
					  	$arg['status']  = 0;
					  	$arg['error_code'] = 0;
						$arg['error_line']= __line__;
					 	$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						//service_type => 1 passes 2 services 3 product
						$time			=	time();
						$service_id   	= 	$this->input->post('service_id');
						$client_id   	= 	$this->input->post('client_id');
						$service_type 	= 	$this->input->post('service_type');
						$quantity     	= 	$this->input->post('quantity');
						$amount       	= 	$this->input->post('amount');
						$amount       	= 	number_format((float)$amount, 2, '.', '');
						$business_id	= $this->input->post('business_id');
						if($service_type==1) {

							$pass_check = $this->dynamic_model->getdatafromtable('user_booking', array('user_id'=>$client_id,'service_id'=>$service_id,'service_type' => '1', 'status' => 'Pending'));

							if (!empty($pass_check)) {
								$arg['status']     = 0;
								$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line']= __line__;
								$arg['data']      =  json_decode('{}');
								$arg['message']    = 'Already added in cart';
								echo json_encode($arg);exit;
							}

							$whe = array('user_id'=>$client_id,'service_id'=>$service_id,'service_type' => '1', 'passes_status'=>'1');
							$chk_pass_booking= $this->dynamic_model->getdatafromtable('user_booking',$whe);
							if(!empty($chk_pass_booking)){
								$arg['status']     = 0;
								$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line']= __line__;
								// $arg['data']      =  json_decode('{}');
								$arg['message']    = $this->lang->line('pass_already_msg');
								echo json_encode($arg);exit;
							}
							$where = array('id'=>$service_id,'status' => 'Active');
							$business_pass= $this->dynamic_model->getdatafromtable('business_passes',$where);
							$business_id=(!empty($business_pass[0]['business_id'])) ? $business_pass[0]['business_id'] : 0;
				            if($business_pass[0]['service_type']==1){
                              $class_id=$business_pass[0]['service_id'];
				            }else{
                              $workshop_id=$business_pass[0]['service_id'];
							}
							$pass_amount=(!empty($business_pass[0]['amount'])) ? $business_pass[0]['amount'] : 0;
							/*if($pass_amount!== $amount){
					        	$arg['status']     = 0;
					            $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line']= __line__;
								// $arg['data']      =  json_decode('{}');
								$arg['message']    = $this->lang->line('amount_incorrect');
								echo json_encode($arg);exit;
							}*/

						} elseif($service_type==2) {
							$business_id=0;
						} elseif($service_type==3) {
							$where = array('id'=>$service_id,'status' => 'Active');
				            $product_data = $this->dynamic_model->getdatafromtable('business_product',$where);
				            $business_id=(!empty($product_data[0]['business_id'])) ? $product_data[0]['business_id'] : 0;
				            $product_amount=(!empty($product_data[0]['price'])) ? $product_data[0]['price'] : 0;
                            //check product stock limit
							$product_quantity= get_product_quantity($business_id,$service_id);
							if($product_quantity < $quantity){
								$arg['status']     = 0;
								$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line']= __line__;
								// $arg['data']      =  json_decode('{}');
								$arg['message']    = $this->lang->line('product_quantity_limit');
								echo json_encode($arg);exit;
							}

							//check amount
							if($product_amount!== $amount){
								$arg['status']     = 0;
								$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line']= __line__;
								//$arg['data']      =  json_decode('{}');
								$arg['message']    = $this->lang->line('amount_incorrect');
								echo json_encode($arg);exit;
						   	}
						} else {
							$arg['status']     = 0;
							$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							// $arg['data']      =  json_decode('{}');
							$arg['message']    = 'Incorrect service type';
							echo json_encode($arg);exit;
						}

						$service_tax= get_service_tax($business_id,$service_id,$service_type);
						$condition = array('service_id'=>$service_id,'service_type' =>$service_type,'user_id' =>$client_id,"status"=>"Pending");
						$cart_data = $this->dynamic_model->getdatafromtable('user_booking',$condition);
						$tax1_rate = 0;
						$tax2_rate = 0;
						if ($service_type == 1) {
							$row = $this->db->get_where('business_passes', array('id' => $service_id, 'business_id' => $business_id))->row_array();
							$tax1_rate = $row['tax1_rate'] ? $row['tax1_rate'] : 0;
        					$tax2_rate = $row['tax2_rate'] ? $row['tax2_rate'] : 0;
						} else {
							$row = $this->db->get_where('business_product', array('id' => $service_id, 'business_id' => $business_id))->row_array();
							$tax1_rate = $row['tax1_rate'] ? $row['tax1_rate'] : 0;
        					$tax2_rate = $row['tax2_rate'] ? $row['tax2_rate'] : 0;
						}
						if(!empty($cart_data)) {
							$get_quantity=$cart_data[0]['quantity'];
				        	$total_quantity=$get_quantity+$quantity;
                            $updateData =   array(
											'amount'  		=>$amount,
											'quantity'  	=>$total_quantity,
											'sub_total'  	=>$total_quantity*$amount,
											'tax_amount'	=>$service_tax,
											'tax1_rate'		=>	$tax1_rate,
											'tax2_rate'		=>	$tax2_rate,
											'update_dt'   	=>$time
						                   );

							$booking_id= $this->dynamic_model->updateRowWhere('user_booking',$condition,$updateData);
						} else {
							$insertData = array(
								'business_id'   =>$business_id,
								'user_id'  		=>$client_id,
								'amount'  		=>$amount,
								'service_type'  =>$service_type,
								'service_id'  	=>$service_id,
								'class_id'  	=>(!empty($class_id))? $class_id :'',
								'workshop_id'  	=>(!empty($workshop_id))? $workshop_id :'',
								'quantity'  	=>$quantity,
								'tax_amount'	=>$service_tax,
								'tax1_rate'		=>	$tax1_rate,
								'tax2_rate'		=>	$tax2_rate,
								'sub_total'  	=>$amount*$quantity,
								'status'  		=>"Pending",
								'create_dt'   	=>$time,
								'update_dt'   	=>$time
					        );
						    $booking_id= $this->dynamic_model->insertdata('user_booking',$insertData);
						}

						if($booking_id)
				        {
							$arg['status']    = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
						 	$arg['message']   = $this->lang->line('cart_add_succ');
						 	$arg['data']      =  json_decode('{}');
				        }else{
				        	$arg['status']     = 0;
				            $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							//$arg['data']       = json_decode('{}');
							$arg['message']    = $this->lang->line('server_problem');
				        }
					}
				}
			}
		}

		echo json_encode($arg);
	}

	public function get_cart_list_info($usid='',$limit='',$offset='', $business_id)
    {
        $response=array();
		$item_name=$service_id=$class_name=$booking_pass_id=$pass_type=$purchase_date=$pass_end_date=$purchase_date_utc=$pass_end_date_utc=$desc=$product_image=$favourite='';
		$discount = 0;
		$total_discount = 0;
		$total_tax1 = 0;
		$total_tax2 = 0;
        $business_data = $this->api_model->get_cart_business($usid,$limit,$offset,$business_id);
        if(!empty($business_data)){
            foreach($business_data as $value)
            {
               	$business_id  = $value['business_id'];
            	$businessData['business_id']  = $business_id;
                $where1=array("id"=>$business_id,"status"=>"Active");
                $busidata = $this->dynamic_model->getdatafromtable('business',$where1);
                $businessData['business_name']  = $busidata[0]['business_name'];
                $img = site_url().'uploads/business/'.$busidata[0]['logo'];
                $businessData['logo']  = $img;

                $where=array("user_id"=>$usid,"business_id"=>$business_id,"status"=>"Pending","service_type !="=>2);
                $cart_data = $this->dynamic_model->getdatafromtable('user_booking',$where);
                $i=0;

                foreach($cart_data as $value1)
               {
                    //1 passes 2 services 3 productdata
                    if($value1['service_type']==1){
                        $where2 = array('business_id'=>$value1['business_id'],'id'=>$value1['service_id'],'status' => 'Active');
                        $business_pass= $this->dynamic_model->getdatafromtable('business_passes',$where2);

                        $item_name=(!empty($business_pass[0]['pass_name'])) ? $business_pass[0]['pass_name'] : '';
                        $service_id=(!empty($business_pass[0]['service_id'])) ? $business_pass[0]['service_id'] : '';
                        //echo $business_pass[0]['service_type']; die;
                        if($business_pass[0]['service_type']=='1'){


                        $classes_data = $this->dynamic_model->getdatafromtable('business_class',array("id"=>$service_id));

                        $class_name= (!empty($classes_data)) ? ucwords($classes_data[0]['class_name']) : "";

                        }else{
                        $workshop_data = $this->dynamic_model->getdatafromtable('business_workshop',array("id"=>$service_id));
                        $class_name= (!empty($workshop_data)) ? ucwords($workshop_data[0]['workshop_name']) : "";

                        }
                        $booking_pass_id= (!empty($business_pass)) ? ucwords($business_pass[0]['pass_id']) : "";
                        $passType  = (!empty($business_pass[0]['pass_type'])) ? $business_pass[0]['pass_type'] : '';

                        $pass_type_subcat  = (!empty($business_pass[0]['pass_type_subcat'])) ? $business_pass[0]['pass_type_subcat'] : '';
                        $pass_type=get_passes_type_name($passType,$pass_type_subcat);

                        $pass_validity= (!empty($business_pass)) ? $business_pass[0]['pass_validity']." Days" : "";
                        $purchase_date= (!empty($business_pass)) ?  date("d M Y ",$business_pass[0]['purchase_date']) : "";
                        $pass_end_date= (!empty($business_pass)) ?  date("d M Y ",$business_pass[0]['pass_end_date']) : "";
                        $purchase_date_utc= (!empty($business_pass)) ?  $business_pass[0]['purchase_date'] : "";
                        $pass_end_date_utc= (!empty($business_pass)) ? $business_pass[0]['pass_end_date'] : "";

                       //Check my favourite status
                        $wh=array("user_id"=>$usid,"service_id"=>$value1['service_id'],"service_type"=>2);
                        $user_favourite= $this->dynamic_model->getdatafromtable("user_business_favourite",$wh);
                        $favourite= (!empty($user_favourite)) ? '1' : '0';
                         //if passes data then blank other data
                        $desc=$product_image='';

                    }elseif($value1['service_type']==2){
                           $item_name='';
                            $service_id=0;
                    }elseif($value1['service_type']==3){
                        $where2 = array('id'=>$value1['service_id'],'status' => 'Active');
                        $product_data = $this->dynamic_model->getdatafromtable('business_product',$where2);
                        $service_id=(!empty($product_data[0]['id'])) ? $product_data[0]['id'] : '';
                        $item_name=(!empty($product_data[0]['product_name'])) ? $product_data[0]['product_name'] : 0;
                        $desc=(!empty($product_data[0]['details'])) ? $product_data[0]['details'] : '';
                        $product_img= get_product_images($service_id);
                        $product_image=(!empty($product_img[0]['image_name'])) ? $product_img[0]['image_name'] : '';
                        //if product data then blank other data
                        $class_name=$booking_pass_id=$pass_type=$purchase_date=$pass_end_date=$pass_validity=$favourite='';
					}

                    $cartData['cart_id']  = $value1['id'];
                    $cartData['item_name']  = $item_name;
                    $cartData['item_decription']  = $desc;
                    $cartData['service_type']  = $value1['service_type'];
                    $cartData['service_id']  = $value1['service_id'];
                    $cartData['class_name']= $class_name;
                    $cartData['booking_pass_id']= $booking_pass_id;
                    $cartData['pass_type']    = $pass_type;
                    $cartData['pass_validity']= $pass_validity;
                    $cartData['favourite']    = $favourite;
                    $cartData['start_date']   = $purchase_date;
                    $cartData['end_date']     = $pass_end_date;
                    $cartData['start_date_utc']= $purchase_date_utc;
                    $cartData['end_date_utc']= $pass_end_date_utc;
                    $cartData['item_image']  = $product_image;
                    $cartData['amount']  = number_format($value1['amount'],2);
                    $cartData['sub_total']  = number_format($value1['sub_total'],2);
                    $cartData['quantity']  = floatVal($value1['quantity']);
					$cartData['tax']  = number_format((float)$value1['tax_amount'], 2, '.', ''); // floatVal($value1['tax_amount']);
					$temp_tax1 = 0;
					$temp_tax2 = 0;
					if ($value1['tax1_rate'] == 0) {
						$temp_tax1 = 0;
					} else {
						$temp_tax1 = ($value1['tax1_rate'] / 100) * $value1['amount'];
						$temp_tax1 = $temp_tax1 * $cartData['quantity'];
					}

					if ($value1['tax2_rate'] == 0) {
						$temp_tax2 = 0;
					} else {
						$temp_tax2 = ($value1['tax2_rate'] / 100) * $value1['amount'];
						$temp_tax2 = $temp_tax2 * $cartData['quantity'];
					}
					$tax = ($value1['tax_amount'] / 100) * $value1['amount'];
					$tax_cal = $tax * $value1['quantity'];
					$cartData['tax']  = number_format($tax_cal, 2);

					$cartData['tax1_rate'] = number_format($temp_tax1, 2);
					$cartData['tax2_rate'] = number_format($temp_tax2, 2);
					$total_tax1 += floatVal($cartData['tax1_rate']);
					$total_tax2 += floatVal($cartData['tax2_rate']);

					$cartData['discount'] = $value1['discount'];
					$cart_response[$i++]      = $cartData;
					if ($value1['discount'] > 0) {
						$total_discount += $value1['discount'];
					}

                }

                $businessData['cart_details']  = $cart_response;
                $response[]   = $businessData;
            }
            $whe=array("user_id"=>$usid, "business_id"=>$business_id,"status"=>"Pending");
            $total_item=getdatacount('user_booking',$whe);
            $tax= gettotalTax($usid,$business_id);
            $total_amt  = check_cart_value($usid,$business_id);


			/* $tax = ($total_amt - $total_discount ) / $tax;
			$grand_total = ($total_amt - $total_discount ) + $tax; */
			$workshop_price = ($total_amt - $total_discount );
			$tax = $total_tax1 + $total_tax2; // 31-12-2020
			// $tax = (($workshop_price * $tax) / 100);
			$grand_total = ($workshop_price + $tax );

			//$grand_total = ($total_amt - $total_discount ) + $tax;

            return  $result	=	array(
				"total_item"		=> $total_item,
				"total_amount"		=> number_format($total_amt,2),
				"tax"				=> number_format($total_tax1 + $total_tax2, 2), // floatVal(round($tax,2)),
				"total_discount"	=> number_format($total_discount,2),
				"tax1_rate" 		=> number_format($total_tax1, 2),
				"tax2_rate" 		=> number_format($total_tax2, 2),
				"grand_total"		=> number_format($grand_total,2),
				"business_details"	=>	$response,
			);

        }else{
           return false;
        }
	}

	public function cart_list_post()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = checkuserid();
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
		      $_POST = json_decode(file_get_contents("php://input"), true);
			  if($_POST)
			  {
			    $this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
					'required' => $this->lang->line('page_no'),
					'numeric' => $this->lang->line('page_no_numeric'),
				));

				$this->form_validation->set_rules('client_id', 'Client Id', 'required|numeric',array(
					'required' => 'client id is required',
					'numeric' => 'client id is required',
				));

				$this->form_validation->set_rules('business_id', 'Business Id', 'required',array(
					'required' => 'Business Id is required',
					'numeric' => 'Business Id is required',
				));

				if($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$response 	= array();
					$page_no 	= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
					$page_no 	= $page_no-1;
					$limit    	= config_item('page_data_limit');
					$offset 	= $limit * $page_no;
					$usid 		=	$this->input->post('client_id');
					$business_id	= $this->input->post('business_id');
                    $business_data = $this->get_cart_list_info($usid,$limit,$offset,$business_id);

					if(!empty($business_data)){

						$arg['status']     = 1;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = $business_data;
						$arg['message']    = $this->lang->line('record_found');
					}else{
						$arg['status']     = 0;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = json_decode('{}');
					 	$arg['message']    = $this->lang->line('record_not_found');
					}
			    }
			  }
			}
		}
	   echo json_encode($arg);
	}

	public function update_cart_post()
	{
		$arg   = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
		   $userdata = checkuserid();
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
				$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
				{
				    $product_id   = $this->input->post('product_id');
                    if(empty($product_id)){
                    	$this->form_validation->set_rules('cart_id','Cart Id', 'required|trim',array( 'required' => $this->lang->line('cart_id_required')));
                    }
					$this->form_validation->set_rules('quantity','Quantity', 'required', array( 'required' => $this->lang->line('quantity_required')));

					$this->form_validation->set_rules('client_id','Client Id', 'required', array( 'required' => 'Client id required'));

					$this->form_validation->set_rules('business_id', 'Business Id', 'required',array(
						'required' => 'Business Id is required',
						'numeric' => 'Business Id is required',
					));

					$this->form_validation->set_rules('discount','Discount', 'required', array( 'required' => 'Discount is required'));

					if($this->form_validation->run() == FALSE)
					{
					  	$arg['status']  = 0;
					  	$arg['error_code'] = 0;
						$arg['error_line']= __line__;
					 	$arg['message'] = get_form_error($this->form_validation->error_array());
					}
					else
					{

                        $page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
                        $page_no= $page_no-1;
                        $limit    = config_item('page_data_limit');
                        $offset = $limit * $page_no;
                        $usid = $this->input->post('client_id');
						$time=time();
						$cart_id   = $this->input->post('cart_id');
                        $quantity  = $this->input->post('quantity');
                        $product_id   = $this->input->post('product_id');
						$business_id   = $this->input->post('business_id');
						$discount      = $this->input->post('discount');
                        if(empty($product_id)){
                            $where2 = array('id'=>$product_id);
                            $product_data = $this->dynamic_model->getdatafromtable('business_product',$where2);
                            $amount=(!empty($product_data[0]['price'])) ? $product_data[0]['price'] : '';

                            // $total_amt  = $quantity*$amount;
							$total_amt  = intVal($quantity) * floatVal($amount);
                            $tax = '0';
                            $grand_total=$total_amt+$tax;
                            $result=array("business_details"=>[],"total_item"=>"$quantity","total_amount"=>"$total_amt","tax"=>"$tax","grand_total"=>"$grand_total");
                            $arg['status']    = 1;
                            $arg['error_code'] = REST_Controller::HTTP_OK;
                            $arg['error_line']= __line__;
                            $arg['data']       = $result;
                            $arg['message']   = $this->lang->line('cart_update_succ');
                        }else{
							$where = array('id'=>$cart_id,'user_id'=>$usid,'status'=>'Pending');
				        	$check_cart= $this->dynamic_model->getdatafromtable('user_booking',$where);
							if(!empty($check_cart))
							{
				        		$where2 = array('id'=>$product_id);
                            	$product_data = $this->dynamic_model->getdatafromtable('business_product',$where2);
                            	$Quantity=!empty($check_cart[0]['quantity']) ? $check_cart[0]['quantity'] :'0';
                            	$total_quantity=$quantity;

								if($quantity > $product_data[0]['quantity']){
									$arg['status']     = 0;
									$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line']= __line__;
									$arg['data']       = json_decode('{}');
									$arg['message']    ='Product Quantity exceeds.';
									 echo json_encode($arg);
                                die;
								}

                        		$sub_total=$check_cart[0]['amount']*$total_quantity;
                            	$updateData =   array(
									'quantity' =>$total_quantity,
									'sub_total' =>$sub_total,
									'discount' => 	$discount,
									'update_dt'=>$time
							    );
								$updateCart= $this->dynamic_model->updateRowWhere('user_booking',$where,$updateData);

								if($updateCart)
					        	{

                                	$result = $this->get_cart_list_info($usid,$limit,$offset, $business_id);

									$arg['status']    = 1;
									$arg['error_code'] = REST_Controller::HTTP_OK;
									$arg['error_line']= __line__;
									$arg['data']       = $result;
									$arg['message']   = $this->lang->line('cart_update_succ');
					        	}else{
									$arg['status']     = 0;
									$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line']= __line__;
									$arg['data']       = json_decode('{}');
									$arg['message']    = $this->lang->line('server_problem');
					        	}
					       	}else{
					        	$arg['status']     = 0;
					            $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line']= __line__;
								$arg['data']       = json_decode('{}');
								$arg['message']    = $this->lang->line('something_wrong');
					        }
                        }

					}
				}
		    }
        }
	  echo json_encode($arg);
	}

	public function pay_at_desk_post(){

        $arg    = array();
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
        }
        else
        {
           $userdata = checkuserid();
           if($userdata['status'] != 1){
             $arg = $userdata;
            }
            else
            {
				$_POST = json_decode(file_get_contents("php://input"), true);

				if($_POST)
			  	{

					$this->form_validation->set_rules('client_id', 'Client Id', 'required|numeric');
					$this->form_validation->set_rules('data[]', 'Item Information', 'required');
					$this->form_validation->set_rules('reference_id', 'Reference Id', 'required');
					$this->form_validation->set_rules('payment_mode', 'Payment Mode', 'required');
					$this->form_validation->set_rules('payment_note', 'Payment Note', 'required');

					if($this->form_validation->run() == FALSE)
					{

						$arg['status']  = 0;
						$arg['error_code'] = 0;
						$arg['error_line']= __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());

					} else {

						$client_id = $this->input->post('client_id');
						$data = $this->input->post('data');
						$loop_status = true;

						$business_id =  0;
						$transaction_id = 0;
						$transaction_amount = 0;
						$transaction_discount = 0;
						$time = time();
						$prod_ids = array();
						$pass_ids = array();
						$insert_data = array();
						$payment_mode = $this->input->post('payment_mode');
						$payment_note = $this->input->post('payment_note');
						$reference_id = $this->input->post('reference_id');

						if (count($data) > 0) {
							for($i = 0; $i < count($data); $i++) {
								$row = $data[$i];
								if (array_key_exists('service_id', $row) && array_key_exists('service_type', $row) && array_key_exists('business_id', $row) && array_key_exists('quantity', $row) && array_key_exists('amount', $row) && array_key_exists('tax', $row) && array_key_exists('discount', $row)) {

									if ($row['service_type'] == '1') {
										array_push($pass_ids, $row['service_id']);
									}

									if ($row['service_type'] == '3') {
										array_push($prod_ids, $row['service_id']);
									}
									$transaction_amount     = $transaction_amount + $row['amount'];
									$transaction_discount   = $transaction_discount + $row['discount'];
									$business_id = $row['business_id'];
									array_push($insert_data, array(
										'user_id'		=>	$client_id,
										'service_id'	=>  $row['service_id'],
										'service_type'	=>  $row['service_type'],
										'business_id'	=>  $row['business_id'],
										'amount'		=>  $row['amount'],
										'tax'			=>  $row['tax'],
										'discount'		=>	$row['discount'],
										'slot_date'		=> 	'',
										'slot_time_id'	=> 	'',
										'instructor_id'	=> 	'',
										'reference_payment_id' => $reference_id,
										'status'	=>	'Success',
										'created_dt' => date('Y-m-d'),
										'quantity'   => $row['quantity']
									));

								} else {
									$loop_status = false;
								}
							}

							if (!$loop_status) {
								$arg['status']  = 0;
								$arg['error_code'] = 0;
								$arg['error_line']= __line__;
								$arg['message'] = 'Failed to create payment request';

							} else {

								// Transaction Entry
								$transaction_data = array(
									'user_id'           =>	$client_id,
									'amount'            =>	$transaction_amount,
									'discount'          =>	$transaction_discount,
									'trx_id'           =>	$reference_id,
									'order_number'     =>	$time,
									'transaction_type' =>	2,
									'payment_status'   =>	"Success",
									'saved_card_id'    =>	0,
									'create_dt'        =>	$time,
									'update_dt'        =>	$time
								);

								$where=array("user_id"=>$client_id, "business_id" => $business_id, "status"=>"Pending");
								$cart_data = $this->dynamic_model->getdatafromtable('user_booking',$where);
								$transaction_id = 0;
								if(!empty($cart_data)) {

									$this->db->insert_batch('user_payment_requests',$insert_data);
									$transaction_id = $this->dynamic_model->insertdata('transactions',$transaction_data);

									foreach($cart_data as $value) {

										$service_type = $value['service_type'];
										$service_id = $value['service_id'];
										$card_id = $value['id'];

										if($value['service_type']=='1') {
											$where1 = array('id'=>$value['service_id'],'service_type'=>'1','status' => 'Active');
											$business_pass= $this->dynamic_model->getdatafromtable('business_passes',$where1);
											$pass_start_date    =   $time;
											$validity = (!empty($business_pass[0]['pass_validity'])) ? $business_pass[0]['pass_validity'] : 0;
											$getEndDate = ($validity * 24 * 60 * 60) + $time;
											$pass_end_date= ($validity == 0) ? $pass_start_date : $getEndDate;
											$pass_status = 1;
											$where2 = array("user_id"=>$client_id,"status"=>"Pending","service_type"=>'1', 'id'=>$card_id );

											$passes_total_count = 0;
											$passes_remaining_count = 0;

											$pass_type_subcat = $business_pass[0]['pass_type_subcat'];
											$where = array('id'=>$pass_type_subcat);
											$manage_pass_type = $this->dynamic_model->getdatafromtable('manage_pass_type',$where);
											$pass_days = $manage_pass_type[0]['pass_days'];

											$booking_data =   array(
												'transaction_id'        => $transaction_id,
												'status'                => "Success",
												'passes_start_date'     => $pass_start_date,
												'passes_end_date'       => $pass_end_date,
												'passes_status'         => $pass_status,
												'passes_total_count'    =>  $pass_days,
												'passes_remaining_count'  =>  $pass_days,
												'payment_mode'	=>	$payment_mode,
												'payment_note'	=>	$payment_note,
												'reference_payment_id'	=>	 $reference_id,
												'update_dt'             => $time
											);

											$booking_id = $this->dynamic_model->updateRowWhere('user_booking',$where2,$booking_data);

										} else {

											$cart_quantity = $value['quantity'];
											$result_product = $this->dynamic_model->getdatafromtable('business_product', array('id'=>$value['service_id']));
											$total_quantity = $result_product[0]['quantity']-$cart_quantity;

											$product_id = $this->dynamic_model->updateRowWhere('business_product',
												array(
													'id'	=>	$value['service_id']
												),
												array(
													'quantity'=> $total_quantity)
												);
											$where2 = array("user_id"=>$client_id,"status"=>"Pending","service_type!="=>'1');
											$booking_data =   array(
												'transaction_id'  => $transaction_id,
												'payment_mode'	=>	$payment_mode,
												'payment_note'	=>	 $payment_note,
												'reference_payment_id'	=>	 $reference_id,
												'status'          => "Success",
												'update_dt'       => $time
											);
											$booking_id = $this->dynamic_model->updateRowWhere('user_booking',$where2,$booking_data);
										}
									}
								}

								if($transaction_id) {
									$arg['status']    = 1;
									$arg['error_code'] = HTTP_OK;
									$arg['error_line']= __line__;
									$arg['message']   =$this->lang->line('payment_succ');
								} else {
									$arg['status']    = 0;
									$arg['error_code'] = HTTP_NOT_FOUND;
									$arg['error_line']= __line__;
									$arg['message']   = $this->lang->line('payment_fail');
								}

							}

						} else {
							$arg['status']  = 0;
							$arg['error_code'] = 0;
							$arg['error_line']= __line__;
							$arg['message'] = 'Failed to create payment request';
						}
					}
				}




            }
        }

        echo json_encode($arg);



	}

	public function pay_at_desk_290820202_post(){

        $arg    = array();
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
        }
        else
        {
           $userdata = checkuserid();
           if($userdata['status'] != 1){
             $arg = $userdata;
            }
            else
            {
				$_POST = json_decode(file_get_contents("php://input"), true);

				if($_POST)
			  	{

					/* $this->form_validation->set_rules('service_id', 'Service Id', 'required|numeric');
					$this->form_validation->set_rules('service_type', 'Service Type', 'required|numeric');
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric');
					$this->form_validation->set_rules('quantity', 'Quantity', 'required|numeric');
					$this->form_validation->set_rules('amount', 'Amount', 'required|numeric'); */
					$this->form_validation->set_rules('client_id', 'Client Id', 'required|numeric');
					$this->form_validation->set_rules('data[]', 'Item Information', 'required');
					$this->form_validation->set_rules('reference_id', 'Reference Id', 'required');
					$this->form_validation->set_rules('payment_mode', 'Payment Mode', 'required');
					$this->form_validation->set_rules('payment_note', 'Payment Note', 'required');

					if($this->form_validation->run() == FALSE)
					{

						$arg['status']  = 0;
						$arg['error_code'] = 0;
						$arg['error_line']= __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());

					} else {

						$client_id = $this->input->post('client_id');
						$data = $this->input->post('data');
						$loop_status = true;
						if (count($data) > 0) {
							$insert_data = array();
							$pass_ids = array();
							$prod_ids = array();
							$insert_transaction = array();

							$payment_mode = $this->input->post('payment_mode');
							$payment_note = $this->input->post('payment_note');
							$reference_id = $this->input->post('reference_id');

							$business_id= '0';
							for($i = 0; $i < count($data); $i++) {
								$row = $data[$i];
								if (array_key_exists('service_id', $row) && array_key_exists('service_type', $row) && array_key_exists('business_id', $row) && array_key_exists('quantity', $row) && array_key_exists('amount', $row) && array_key_exists('tax', $row) && array_key_exists('discount', $row)) {
									$business_id = $row['business_id'];

									if ($row['service_type'] == '1') {
										array_push($pass_ids, $row['service_id']);
									}

									if ($row['service_type'] == '3') {
										array_push($prod_ids, $row['service_id']);
									}

									array_push($insert_data, array(
										'user_id'		=>	$client_id,
										'service_id'	=>  $row['service_id'],
										'service_type'	=>  $row['service_type'],
										'business_id'	=>  $row['business_id'],
										'amount'		=>  $row['amount'],
										'tax'			=>  $row['tax'],
										'discount'		=>	$row['discount'],
										'slot_date'		=> 	'',
										'slot_time_id'	=> 	'',
										'instructor_id'	=> 	'',
										'reference_payment_id' => $reference_id, //getuniquenumber(),
										'status'	=>	'Success',
										'created_dt' => date('Y-m-d'),
										'quantity'   => $row['quantity']
									));
								} else {
									$loop_status = false;
								}
							}

							if (!$loop_status) {
								$arg['status']  = 0;
								$arg['error_code'] = 0;
								$arg['error_line']= __line__;
								$arg['message'] = 'Failed to create payment request';
							} else {

								$passes_start_date = time();
								if (!empty($pass_ids)) {
									$this->db->where_in('id', $pass_ids);
									$passes = $this->db->get('business_passes')->result_array();

									foreach($passes as $pass) {
										$validity = (!empty($pass['pass_validity'])) ? $pass['pass_validity'] : 0;
										$getEndDate = ($validity * 24 * 60 * 60) + $passes_start_date;
										$passes_end_date = ($validity == 0) ? $pass_start_date : $getEndDate;
										$passes_total_count = 0;
										$passes_remaining_count = 0;

										$pass_type_subcat=$pass['pass_type_subcat'];
										$where = array('id'=>$pass_type_subcat);
										$manage_pass_type = $this->dynamic_model->getdatafromtable('manage_pass_type',$where);
										$pass_days = $manage_pass_type[0]['pass_days'];

										$passes_total_count = $pass_days;
										$passes_remaining_count = $pass_days;

										$where_array = array('user_id' => $client_id, '	business_id' => $business_id, 'service_id' => $pass['id'], '	service_type' => 1, 'status' => 'Pending');
										$update_record = $this->db->get_where('user_booking', $where_array)->row_array();

										$this->db->where($where_array);
										$this->db->update('user_booking', array(
											'status' => 'Success',
											'update_dt' => $passes_start_date,
											'passes_status' => '1',
											'passes_start_date' => $passes_start_date,
											'passes_end_date' => $passes_end_date,
											'passes_total_count' => $passes_total_count,
											'passes_remaining_count' => $passes_remaining_count,
											'payment_mode'	=>	$payment_mode,
											'payment_note'	=>	 $payment_note,
											'reference_payment_id'	=>	 $reference_id
										));
										if (!empty($update_record)) {
											array_push($insert_transaction, $update_record['id']);
										}
									}
								}

								if (!empty($prod_ids)) {
									for ($i = 0; $i < count($prod_ids); $i++) {
										$where_array = array('user_id' => $client_id, '	business_id' => $business_id, 'service_id' => $prod_ids[$i], '	service_type' => 2, 'status' => 'Pending');
										$update_record_pro = $this->db->get_where('user_booking', $where_array)->row_array();
										$this->db->update('user_booking', array(
											'status' => 'Success',
											'payment_mode'	=>	$payment_mode,
											'payment_note'	=>	 $payment_note,
											'reference_payment_id'	=>	 $reference_id,
											'update_dt' => $passes_start_date
										));
										if (!empty($update_record_pro)) {
											array_push($insert_transaction, $update_record_pro['id']);
										}
									}
								}

								if (!empty($insert_transaction)) {
									$this->db->where_in('id', $insert_transaction);
									$info = $this->db->get('user_booking')->result_array();
									$tran_data = array();
									if (!empty($info)) {
										foreach($info as $booking) {
											array_push($tran_data, array(
												'user_id'                => $booking['user_id'],
												'amount'                 => ($booking['amount']) ? $booking['amount']: 0.00,
												'trx_id'                =>  $booking['transaction_id'],
												'discount'				=>	$booking['discount'],
												'order_number'          =>  time(),
												'transaction_type'      =>  ($booking['service_type']==2) ? 3 : $booking['service_type'],
												'payment_status'        =>	"Success",
												'saved_card_id'         =>	0,
												'create_dt'        		=>	time(),
												'update_dt'        		=>	time()
											));
										}
										if(!empty($tran_data)) {
											$this->db->insert_batch('transactions' ,$tran_data);
										}
									}
								}

								$requestData = $this->db->insert_batch('user_payment_requests',$insert_data);

								if ($requestData) {
									$arg['status']    = 1;
									$arg['error_code'] = REST_Controller::HTTP_OK;
									$arg['error_line']= __line__;
									$arg['message']    = 'Payment request successfully';

								} else {
									$arg['status']  = 0;
									$arg['error_code'] = 0;
									$arg['error_line']= __line__;
									$arg['message'] = 'Failed to create payment request';
								}

							}

						} else {
							$arg['status']  = 0;
							$arg['error_code'] = 0;
							$arg['error_line']= __line__;
							$arg['message'] = 'Failed to create payment request';
						}
					}
				}




            }
        }

        echo json_encode($arg);



	}

	public function remove_cart_post()
	{
		$arg   = array();
        $arrayName = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
		   $userdata = checkuserid();
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
				$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
				{
				    $remove_cart_type   = $this->input->post('remove_cart_type');
				    $this->form_validation->set_rules('remove_cart_type','Remove Cart Type','required|trim', array( 'required' => $this->lang->line('remove_cart_type_required')));
				    if($remove_cart_type !=1){
				    	$this->form_validation->set_rules('cart_id','Cart Id', 'required|trim',array( 'required' => $this->lang->line('cart_id_required')));
					}

					$this->form_validation->set_rules('client_id','Client Id', 'required|trim',array( 'required' => 'Client Id is required'));
					$this->form_validation->set_rules('business_id','Business Id', 'required|trim',array( 'required' => 'Business Id is required'));

					if($this->form_validation->run() == FALSE)
					{
					  	$arg['status']  = 0;
					  	$arg['error_code'] = 0;
						$arg['error_line']= __line__;
					 	$arg['message'] = get_form_error($this->form_validation->error_array());
					}
					else
					{
						$business_id= $this->input->post('business_id');
						$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
                        $page_no= $page_no-1;
                        $limit    = config_item('page_data_limit');
                        $offset = $limit * $page_no;
                        $usid =$userdata['data']['id'];
						$time=time();
						$cart_id   	= $this->input->post('cart_id');
						$usid 		= $this->input->post('client_id');
						//remove_cart_type 0 single 1 all
						if($remove_cart_type !=1){
							$where = array('id'=>$cart_id,'user_id'=>$usid,'status'=>'Pending');
						}else{
							$where = array('user_id'=>$usid,'status'=>'Pending');
						}
				        $check_cart = $this->dynamic_model->getdatafromtable('user_booking',$where);
				        if(!empty($check_cart))
				        {
					        if($remove_cart_type == 1){
	                            $where1 = array('user_id'=>$usid,'status'=>'Pending');
							    $deleteCart= $this->dynamic_model->deletedata('user_booking',$where1);
					        }else{
	                            $where1 = array('id' =>$cart_id,'user_id'=>$usid,'status'=>'Pending');
                                $deleteCart= $this->dynamic_model->deletedata('user_booking',$where1);
					        }
                            $business_data = $this->get_cart_list_info($usid,$limit,$offset, $business_id);
                            $business_data = $business_data ? $business_data : json_decode('{}');
						    $arg['status']    = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
                            $arg['data']       = $business_data;
						 	$arg['message']   = $this->lang->line('remove_cart');
					    }else{
					        	$arg['status']     = 0;
					            $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line']= __line__;
                                $arg['data']       = json_decode('{}');
								$arg['message']    = $this->lang->line('something_wrong');
					        }

					}
				}
		    }
        }
	  echo json_encode($arg);
	}

	public function shift_list_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = checkuserid();
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
				$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
			  	{
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
					$this->form_validation->set_rules('business_id', 'Business Id', 'numeric',array(
						'numeric' => $this->lang->line('business_id_numeric'),
					));

					$this->form_validation->set_rules('upcoming_date', 'Upcoming Date', 'numeric',array(
						'numeric' => 'Upcoming Date is required',
					));

					if($this->form_validation->run() == FALSE)
					{
						$arg['status']  = 0;
						$arg['error_code'] = 0;
						$arg['error_line']= __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$time_zone =  $this->input->get_request_header('Timezone', true);
						$time_zone =  $time_zone ? $time_zone : 'UTC';
						date_default_timezone_set($time_zone);

						//workshop_status 0 = instructor future workshop, 1 = my workshop History, 2 = cancelled, 3 = All workshop
						/*
							0 = all
							1 = my features
							2 = cancel
							3 = compleated

						*/
						$shift_status = $this->input->post('shift_status');
						$business_id = $this->input->post('business_id');
						$user_id     = $userdata['data']['id'];
						$searchDate = '';

						$upcoming_date = $this->input->post('upcoming_date');
						$today_dt = date("Y-m-d");
						if(($shift_status == '0' ||  $shift_status == '1') && !empty($upcoming_date)){
							$searchDate = date("Y-m-d", $this->input->post('upcoming_date'));
							if($today_dt > $searchDate){
								$arg['status']  = 0;
								$arg['error_code'] = 0;
								$arg['error_line']= __line__;
								$arg['message'] = 'Select future date.';
								echo json_encode($arg);	die;
							}
						}else if(($shift_status == '3') && !empty($upcoming_date)){
							$searchDate = date("Y-m-d", $this->input->post('upcoming_date'));
							if($searchDate > $today_dt){
								$arg['status']  = 0;
								$arg['error_code'] = 0;
								$arg['error_line']= __line__;
								$arg['message'] = 'Select past date.';
								echo json_encode($arg);	die;
							}
						}
						if ($this->input->post('upcoming_date')) {
							$searchDate = date("Y-m-d", $this->input->post('upcoming_date'));
						} else {
							//$searchDate = date("Y-m-d");
						}

						$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no= $page_no-1;
						$limit    = config_item('page_data_limit');
						//$limit    = 1;
						$offset = $limit * $page_no;

						$collection = getShift(2, $business_id, $user_id, $limit, $offset, 'shift_date_str', 'DESC', $searchDate,'',$shift_status);

						if (!empty($collection)) {
							$arg['status']     = 1;
							$arg['error_code']  = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
							$arg['data']       = $collection;
							$arg['business_path'] = base_url('uploads/business/');
							$arg['message']    = $this->lang->line('record_found');
						} else {
							$arg['status']     = 0;
							$arg['error_code']  =REST_Controller::HTTP_NOT_MODIFIED;
							$arg['error_line']= __line__;
							$arg['message'] = $this->lang->line('record_not_found');
						}

					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function shift_details_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = checkuserid();
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
				$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
			  	{
					$this->form_validation->set_rules('business_id', 'Business Id', 'numeric',array(
						'numeric' => $this->lang->line('business_id_numeric'),
					));

					$this->form_validation->set_rules('schedule_id', 'Schedule Id', 'required|numeric',array(
						'schedule_id' => 'Shift is required',
						'schedule_id' => 'Shift is required',
					));

					if($this->form_validation->run() == FALSE)
					{
						$arg['status']  = 0;
						$arg['error_code'] = 0;
						$arg['error_line']= __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$time_zone =  $this->input->get_request_header('Timezone', true);
						$time_zone =  $time_zone ? $time_zone : 'UTC';
						date_default_timezone_set($time_zone);

						$business_id = $this->input->post('business_id');
						$user_id     = $userdata['data']['id'];
						$scheduleId    = $this->input->post('schedule_id');

						$collection = getShift(3, $business_id, $scheduleId, '', '', 'shift_date_str', 'DESC');

						if (!empty($collection)) {
							$arg['status']     = 1;
							$arg['error_code']  = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
							$arg['data']       = $collection;
							$arg['business_path'] = base_url('uploads/business/');
							$arg['message']    = $this->lang->line('record_found');
						} else {
							$arg['status']     = 0;
							$arg['error_code']  =REST_Controller::HTTP_NOT_MODIFIED;
							$arg['error_line']= __line__;
							$arg['message'] = $this->lang->line('record_not_found');
						}

					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function shift_cancel_request_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = checkuserid();
			if($userdata['status'] != 1){
				$arg = $userdata;
			}
			else
			{
				$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
				{
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
						'required' => $this->lang->line('business_id_required'),
						'numeric' => $this->lang->line('business_id_required'),
					));

					$this->form_validation->set_rules('shift_id', 'Shift Id', 'required|numeric',array(
						'required' => 'Shift id is required',
						'numeric' => 'Shift id is required',
					));
					$this->form_validation->set_rules('shift_schedule_id', 'Shift Schedule Id', 'required|numeric',array(
						'required' => 'Shift schedule id is required',
						'numeric' => 'Shift schedule id is required',
					));
					$this->form_validation->set_rules('reason', 'Reason', 'required',array(
						'required' => 'Reason is required'
					));
					if($this->form_validation->run() == FALSE)
					{
						$arg['status']  = 0;
						$arg['error_code'] = 0;
						$arg['error_line']= __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());

					} else {

						$business_id = $this->input->post('business_id');
						$user_id     = $userdata['data']['id'];
						$shift_id    = $this->input->post('shift_id');
						$shift_schedule_id = $this->input->post('shift_schedule_id');
						$reason    	 = $this->input->post('reason');

						$info = $this->dynamic_model->getQueryRowArray('SELECT * FROM business_shift_instructor where status = 1 AND shift_id = '.$shift_id.' AND instructor = '.$user_id);

						if (!empty($info)) {

							$insertData = array('instructor' => $user_id,
								'shift_id' => $shift_id,
								'shift_schedule_id'=> $shift_schedule_id,
					            'comment' => $reason,
					            'create_dt' => time(),
					        );

							$this->dynamic_model->insertdata('business_shift_instructor_comments',$insertData);

							$arg['status']    = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
						 	$arg['message']   = 'Request send successfully';

						} else {
							$arg['status']     = 0;
							$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['message']    = 'Request under process';
						}

					}
				}
			}
		}
		echo json_encode($arg);
	}

	/* Get service appointment details */
	public function my_book_services_list_post()
    {
    	date_default_timezone_set('UTC');
        $arg = array();
        $from_time_utc = date('Y-m-d');
        $time = strtotime($from_time_utc);
        $time = time() - 24*60*60 ;
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
        }
        else
        {
            $userdata = checkuserid('1');
            if($userdata['status'] != 1){
                $arg = $userdata;
            }
            else
            {
                $userid      = $userdata['data']['id'];
                $_POST = json_decode(file_get_contents("php://input"), true);
                if($_POST)
                {
                    $this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
                        'required' => $this->lang->line('page_no'),
                        'numeric' => $this->lang->line('page_no_numeric'),
                    ));

                    if($this->form_validation->run() == FALSE)
                    {
                        $arg['status']  = 0;
                        $arg['error_code'] = 0;
                        $arg['error_line']= __line__;
                        $arg['message'] = get_form_error($this->form_validation->error_array());
                    }
                    else
                    {
						$response   =   array();
                        $page_no    =   (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
                        $transaction_id = (!empty($this->input->post('transaction_id'))) ? $this->input->post('transaction_id') : "";
                        $start_dt = (!empty($this->input->post('search_dt'))) ? $this->input->post('search_dt') : "";
                        $end_dt = $start_dt;
                        $transaction_status = (!empty($this->input->post('transaction_status'))) ? $this->input->post('transaction_status') : "";

						$business_id = ($this->input->post('business_id')) ? $this->input->post('business_id') : 0;

                        $page_no    =   $page_no-1;
                        $limit      =   config_item('page_data_limit');
                        $offset     =   $limit * $page_no;
                        $imgePath = base_url().'uploads/user/';
                        $query = "SELECT bshift.start_date,b.status, IFNULL(concat(uf.name, '', uf.lastname),'') as family_member_name, IFNULL(uf.gender,'') as family_gender, IFNULL(uf.date_of_birth,'') as family_dob,(CASE WHEN uf.profile_img != '' THEN CONCAT('".$imgePath."',uf.profile_img) ELSE '' END ) as family_profile_img, b.family_user_id, s.id as service_id, t.id as transaction_id,t.user_id,t.amount,t.transactions_tax,t.discount,t.trx_id,t.order_number,t.create_dt as payment_date,t.payment_type,t.payment_method,t.responce_all,u.name,u.lastname, u.gender, u.date_of_birth,concat('".$imgePath."', u.profile_img) as profile_img, s.amount as service_amount, s.tip_option,s.service_name,s.duration,s.cancel_policy,s.description, s.tax1, s.tax2, s.tax1_rate, s.tax2_rate,bs.business_name,bs.address,bs.location_detail,uu.name as instructor_name,uu.lastname as instructor_lastname,concat('".$imgePath."', uu.profile_img) as instructor_profile_img, b.passes_start_date as start_time, b.passes_end_date as end_time, b.shift_date, CASE WHEN bl.location_name IS NULL THEN '' Else bl.location_name END as location_name, CASE WHEN bl.address IS NULL THEN '' Else bl.address END as location_address, CASE WHEN bl.map_url IS NULL THEN '' Else bl.map_url END as location_url, CASE WHEN bl.location_url IS NULL THEN '' Else bl.location_url END as web_link, b.status as booking_status, b.tip_comment FROM transactions AS t join user_booking as b on t.id = b.transaction_id JOIN user as u on u.id = b.user_id join service as s on s.id = b.service_id JOIN business as bs on bs.id = b.business_id JOIN user as uu on uu.id = b.shift_instructor LEFT Join business_shift as bshift on bshift.id = b.shift_id left join business_location as bl on bl.id = bshift.location_id left join user as uf on uf.id = b.family_user_id WHERE b.service_type = '2' ";

						if ($business_id != 0) {
							$query .= " AND b.business_id = ".$business_id;
						}

                        $transaction_status = trim(strtolower($transaction_status));
                        if($transaction_status == 'all'){
                        	$query .= " AND b.shift_date >= '".$time."' AND b.status = 'Confirm'";
                        }else{
                        	 $query .= " AND b.shift_instructor = '".$userid."'";
                        }

                        if(!empty($transaction_id)){
                           $query .= " AND t.id = '".$transaction_id."'";
                        }
                        if(!empty($start_dt)){
                            $start_dt = date('Y-m-d',$start_dt);
                            $query .= " AND b.shift_search_date = '".$start_dt."'";
                        }

                        if (!$this->input->post('transaction_id')) {
                            if(!empty($transaction_status)){
                                $transaction_status = trim(strtolower($transaction_status));
                                if($transaction_status == 'my'){

                                }else if($transaction_status == 'cancel'){
                                    $query .= "AND b.status = 'Cancel'";
                                }else if($transaction_status == 'pending_payment'){
                                    $query .= "AND b.status = 'Completed'";
                                } else if ($transaction_status == 'completed_appointment') {
                                    $query .= "AND b.status = 'Success'";
                                }else if ($transaction_status == 'upcoming') {
                                    $query .= " AND b.shift_date >= '".$time."' AND b.status != 'Completed' AND b.status != 'Success'";
                                }
                            }else{
                                $query .= "AND b.status = 'Confirm'";
                            }
                        }

						if ($transaction_status == 'completed_appointment') {
							$query .= " ORDER BY b.passes_start_date DESC LIMIT ".$limit.' OFFSET '.$offset;
						}else if ($transaction_status == 'upcoming') {
							$query .= " AND b.status != 'Cancel' ORDER BY b.passes_start_date asc LIMIT ".$limit.' OFFSET '.$offset;
                        }else{
                        	$query .= " ORDER BY b.passes_start_date ASC LIMIT ".$limit.' OFFSET '.$offset;
                        }


						//echo $query; die;
                       	// $query .= " ORDER BY b.create_dt desc";
                        $collection = $this->db->query($query)->result_array();
                        if(!empty($collection)){
                         array_walk ( $collection, function (&$key) {

							if ($key['family_user_id'] != '0') {

								$family = explode(" ", $key['family_member_name']);
								if (count($family) > 1) {
									$key['name'] = $family[0];
									$key['lastname'] = $family[1];
								} else {
									$key['name'] = $key['family_member_name'];
									$key['lastname'] = '';
								}
								$key['gender'] = $key['family_gender'];
								$key['date_of_birth'] = $key['family_dob'];
								$key['profile_img'] = $key['family_profile_img'];
							}

                         	$key['b_start_date'] = date('d-m-y',$key['start_date']);
                            $workshop_price = $key['service_amount'];
                            $transactions_tax = $key['transactions_tax'];
                            $amount = $workshop_price - $transactions_tax;
                            //$key['amount'] = number_format($amount,2);
                            $key['amount'] = number_format($key['service_amount'],2);
                            $total = 0;
							if ($key['tax1'] == 'Yes') {
								$tax1_rate = ($key['tax1_rate'] / 100) * $key['service_amount'];
								$total += $tax1_rate;
								$key['tax1_rate'] = number_format($tax1_rate, 2);
							}
							if ($key['tax2'] == 'Yes') {
								$tax2_rate = ($key['tax2_rate'] / 100) * $key['service_amount'];
								$total += $tax2_rate;
								$key['tax2_rate'] = number_format($tax2_rate, 2);
							}
							$key['service_tax_price'] = number_format($total, 2); // number_format($transactions_tax,2);
							$key['service_total_price'] = number_format($workshop_price + $total,2);
                        });
                    }

                        if (!empty($collection)) {
                            $arg['status']     = 1;
                            $arg['error_code']  = HTTP_OK;
                            $arg['error_line']= __line__;
                           // $arg['query']       = $query;
                            $arg['data']       = $collection;
                            $arg['message']    = $this->lang->line('record_found');
                        } else {
                            $arg['status']     = 0;
                            $arg['error_code']  = HTTP_OK;
                            $arg['error_line']= __line__;
                            $arg['data']       = array();
                           // $arg['data']       = $query;
                            $arg['message']    = $this->lang->line('record_not_found');
                        }
                    }
                }
            }
        }
        echo json_encode($arg);
	}

	public function service_status_change_post()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = checkuserid();
		    if($userdata['status'] != 1){
			    $arg = $userdata;
			}
			else
			{
		        $_POST = json_decode(file_get_contents("php://input"), true);
			    if($_POST)
			    {
			        $this->form_validation->set_rules('transaction_id','Transaction Id', 'required|trim', array( 'required' => 'Transaction id is required'));
			        $this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
                    if($this->form_validation->run() == FALSE)
                    {
                        $arg['status']  = 0;
                        $arg['error_code'] = 0;
                        $arg['error_line']= __line__;
                        $arg['message'] = get_form_error($this->form_validation->error_array());
                    }
                    else
                    {
                        $user_id        =   $userdata['data']['id'];
                        $business_id    =  $this->input->post('business_id');
                        $transaction_id =  $this->input->post('transaction_id');

                        $searchArray = array('transaction_id' => $transaction_id, 'business_id' => $business_id);
                        $getAmount = $this->db->get_where('user_booking', $searchArray)->row_array();
                        $amount = floatval($getAmount['amount']);
                        $status = 'Success';
                        if ($amount > 0) {
                            $status = 'Completed';
                            $booking_status = $this->dynamic_model->updateRowWhere('user_booking', $searchArray, array('status' => $status));
                        } else {
                            $booking_status = $this->dynamic_model->updateRowWhere('user_booking', $searchArray, array('status' => $status));
                        }

                        // $booking_status = $this->dynamic_model->updateRowWhere('user_booking', array('transaction_id' => $transaction_id, 'business_id' => $business_id), array('status' => 'Completed'));

                        if($booking_status)
				        {
							$arg['status']      = 1;
							$arg['error_code']  = REST_Controller::HTTP_OK;
							$arg['error_line']  = __line__;
						 	$arg['message']     = 'Status changed successfully';
						}else{
				        	$arg['status']     = 0;
				            $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['message']    = $this->lang->line('server_problem');
				        }

                    }
                }
            }
        }
        echo json_encode($arg);
    }

public function buy_now_services_post()
{
    $arg    = array();
    $version_result = version_check_helper1();
    if($version_result['status'] != 1 )
    {
        $arg = $version_result;
    }
    else
    {
        $userdata = checkuserid();
        if($userdata['status'] != 1){
         $arg = $userdata;
        }
        else
        {
        $_POST = json_decode(file_get_contents("php://input"), true);
        if($_POST)
        {
            $this->form_validation->set_rules('service_id','Service Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
            $this->form_validation->set_rules('transaction_id','Transaction Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
            if($this->form_validation->run() == FALSE)
            {
                $arg['status']  = 0;
                $arg['message'] = get_form_error($this->form_validation->error_array());
            }
            else
            {
                $tip_amount = $this->input->post('tip_amount');
                $service_id = $this->input->post('service_id');
                $transaction_id = $this->input->post('transaction_id');
                $where = array('id'=>$service_id,'status' => 'Active');
                $product_data = $this->dynamic_model->getdatafromtable('service',$where);
                $Amt=0;
                $usid =$userdata['data']['id'];
                $name =$userdata['data']['name'];
                $lastname =$userdata['data']['lastname'];
                $time = time();
                $pass_start_date=$pass_end_date=$pass_status='';
                //service_type => 1 passes 2 services 3 product

                $token = $this->input->post('token');
                $where = array('transaction_id'=>$transaction_id);
                $booking_data = $this->dynamic_model->getdatafromtable('user_booking',$where);
                if(empty($booking_data)){
                   $arg['status']    = 0;
                    $arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
                    $arg['error_line']= __line__;
                    $arg['message']   = @$res['message'];
                    $arg['data']      =json_decode('{}');
                    echo json_encode($arg); die;
                }

                $quantity = $booking_data[0]['quantity'];
                $grand_total = $booking_data[0]['amount'];
                $grand_total = number_format((float)$grand_total, 2, '.', '');
                $savecard= $this->input->post('savecard');
                $card_id = $this->input->post('card_id');
                $passes_total_count     = 0;
                $passes_remaining_count  = 0;

                $pass_start_date = 0;
                $pass_end_date = 0;
                $service = $this->db->get_where('service', array('id' => $service_id))->row_array();
                $business_id = $service['business_id'];

                $mid = getUserMarchantId($business_id);
                $marchant_id = $mid['marchant_id'];
                $marchant_id_type = $mid['marchant_id_type'];

                if(!empty($token)){
                    $payment_data = array(
                        'order_number' => $time,
                        'amount' => $grand_total,
                        'payment_method' => 'token',
                        'token' => array(
                        'name' =>'Test Card',
                        'code' => $token,
                        'complete' =>true
                               )
                     );
                }else if(!empty($card_id)){

                $where = array('user_id' => $usid, 'business_id' => $business_id);
                $result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
                $customer_code = $result_card[0]['card_id'];

                $payment_data = array(
                    'order_number' => $time,
                    'amount' => $grand_total,
                    'payment_method' => 'payment_profile',
                    'payment_profile' => array(
                    'customer_code' =>$customer_code,
                    'card_id' => $card_id,
                    'complete' =>true)
                    );
                }

                /* start */
                $where = array('user_id' => $usid, 'business_id' => $business_id);
                $result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
                if(empty($result_card) && ($savecard == '1')){
                    $legato_token_data = array(
                            'language' => 'en',
                            'comments' => SITE_NAME,
                            'token' => array('name' => 'Test Card',
                            'code' => $token)
                        );
                    $apiurl='https://api.na.bambora.com/v1/profiles';
                    $responce = $this->bomborapay->profile_create('POST',$apiurl, $legato_token_data, $marchant_id);
                    //echo $marchant_id;
                    //print_r($responce); die;
                    if($responce['code'] == '1'){
                        $transaction_data = array('user_id'=>$usid,
                            'business_id' => $business_id,
                            'card_id'=>$responce['customer_code']);

                        $this->dynamic_model->insertdata('user_card_save',$transaction_data);
                        $customer_code = $responce['customer_code'];
                    }
                }elseif(!empty($result_card) && ($savecard == '1')){
                    $customer_code = $result_card[0]['card_id'];
                    $apiurl="https://api.na.bambora.com/v1/profiles/$customer_code/cards";
                    $legato_token_data = array(
                            'token' => array('name' => 'Test Card',
                            'code' => $token)
                        );
                    $responce = $this->bomborapay->profile_create('POST',$apiurl, $legato_token_data,$marchant_id);
                    // echo $marchant_id;
                    //print_r($responce); die;
                    if($responce['code'] == '1'){
                        $customer_code = $responce['customer_code'];
                    }
                }

                if($savecard == '1'){
                    $payment_data = array(
                        'order_number' => $time,
                        'amount' => $grand_total,
                        'payment_method' => 'payment_profile',
                        'payment_profile' => array(
                        'customer_code' =>$customer_code,
                        'card_id' => $card_id,
                        'complete' =>true)
                    );
                }
                /* end */
                $payUrl='https://api.na.bambora.com/v1/payments';
                $res=$this->bomborapay->payment_checkout('POST',$payUrl,$payment_data,$marchant_id,$marchant_id_type);
                if(@$res['approved']==1)
                {
                    $where = array('user_id' => $usid,'business_id' => $business_id);
                    $result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
                    $ref_num  = getuniquenumber();
                    $payment_id =!empty(@$res['id']) ? $res['id'] : $ref_num;
                    $authorizing_merchant_id =!empty(@$res['authorizing_merchant_id']) ? $res['authorizing_merchant_id'] : '';
                    $payment_type =!empty(@$res['type']) ? $res['type'] : '';
                    $payment_method =!empty(@$res['payment_method']) ? $res['payment_method'] : '';
                    $amount =!empty(@$res['amount']) ? $res['amount'] : '';
                    //Insert data in transaction table
                    $transaction_data = array(
                        'authorizing_merchant_id' => $authorizing_merchant_id,
                        'payment_type' => $payment_type,
                        'payment_method' => $payment_method,
                        'amount'                 =>$amount,
                        'trx_id'                =>$payment_id,
                        'order_number'          =>$time,
                        'transaction_type'      =>3,
                        'payment_status'        =>"Success",
                        'transaction_date' => date('Y-m-d'),
                        'create_dt'             =>$time,
                        'update_dt'             =>$time,
                        'responce_all'=>json_encode($res)
                            );
                    $where1 = array('id' => $transaction_id);
                    $this->dynamic_model->updateRowWhere('transactions', $where1, $transaction_data);

                        //after that insert into user booking table
                    $sub_total=$amount*$quantity;
                    $passData =   array(
                        'amount'        =>$amount,
                        'sub_total'     =>$sub_total,
                        'status'        =>"Success",
                        'create_dt'     =>$time,
                        'update_dt'     =>$time,
                    );

                    if ($this->input->post('tip_comment')) {
                        $passData['tip_comment'] = $this->input->post('tip_comment');
                    }

                    $where1 = array('transaction_id' => $transaction_id);
                    $this->dynamic_model->updateRowWhere('user_booking', $where1, $passData);


                    $response  = array('amount' =>number_format((float)$sub_total, 2, '.', ''),'transaction_date'=>date('d M Y'));
                    if($transaction_id)
                    {
                        $arg['status']    = 1;
                        $arg['error_code'] = HTTP_OK;
                        $arg['error_line']= __line__;
                        $arg['message']   =$this->lang->line('payment_succ');
                        $arg['transaction_id']   = $transaction_id;
                        $arg['data']      = $response;
                    }
                    else
                    {
                        $arg['status']    = 0;
                        $arg['error_code'] = HTTP_NOT_FOUND;
                        $arg['error_line']= __line__;
                        $arg['message']   = $this->lang->line('payment_fail');
                        $arg['data']      =json_decode('{}');
                    }
                    }else{
                        $arg['status']    = 0;
                        $arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
                        $arg['error_line']= __line__;
                        $arg['message']   = @$res['message'];
                        $arg['data']      =json_decode('{}');
                    }
                    }
                }
            }
        }
      echo json_encode($arg);
}

public function clover_buy_now_services_post()
{
    $arg    = array();
    $version_result = version_check_helper1();
    if($version_result['status'] != 1 )
    {
        $arg = $version_result;
    }
    else
    {
        $userdata = checkuserid();
        if($userdata['status'] != 1){
         $arg = $userdata;
        }
        else
        {
        $_POST = json_decode(file_get_contents("php://input"), true);
        if($_POST)
        {
            $this->form_validation->set_rules('service_id','Service Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
            $this->form_validation->set_rules('transaction_id','Transaction Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
            if($this->form_validation->run() == FALSE)
            {
                $arg['status']  = 0;
                $arg['message'] = get_form_error($this->form_validation->error_array());
            }
            else
            {
                $tip_amount = $this->input->post('tip_amount');
                $service_id = $this->input->post('service_id');
                $transaction_id = $this->input->post('transaction_id');
                $where = array('id'=>$service_id,'status' => 'Active');
                $product_data = $this->dynamic_model->getdatafromtable('service',$where);
                $Amt=0;
                $usid =$userdata['data']['id'];
                $name =$userdata['data']['name'];
                $lastname =$userdata['data']['lastname'];
                $time = time();
                $pass_start_date=$pass_end_date=$pass_status='';
                //service_type => 1 passes 2 services 3 product

                $token = $this->input->post('token');
                $where = array('transaction_id'=>$transaction_id);
                $booking_data = $this->dynamic_model->getdatafromtable('user_booking',$where);
                if(empty($booking_data)){
                   $arg['status']    = 0;
                    $arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
                    $arg['error_line']= __line__;
                    $arg['message']   = 'No record found';
                    $arg['data']      =json_decode('{}');
                    echo json_encode($arg); die;
                }

                $quantity = $booking_data[0]['quantity'];
                $grand_total = $booking_data[0]['amount'];
                $grand_total = number_format((float)$grand_total, 2, '.', '');
                //$savecard= $this->input->post('savecard');
                //$card_id = $this->input->post('card_id');
                $passes_total_count     = 0;
                $passes_remaining_count  = 0;

                $pass_start_date = 0;
                $pass_end_date = 0;
                $service = $this->db->get_where('service', array('id' => $service_id))->row_array();
                $business_id = $service['business_id'];

                /*$mid = getUserMarchantId($business_id);
                $marchant_id = $mid['marchant_id'];
                $marchant_id_type = $mid['marchant_id_type'];

                if(!empty($token)){
                    $payment_data = array(
                        'order_number' => $time,
                        'amount' => $grand_total,
                        'payment_method' => 'token',
                        'token' => array(
                        'name' =>'Test Card',
                        'code' => $token,
                        'complete' =>true
                               )
                     );
                }else if(!empty($card_id)){

                $where = array('user_id' => $usid, 'business_id' => $business_id);
                $result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
                $customer_code = $result_card[0]['card_id'];

                $payment_data = array(
                    'order_number' => $time,
                    'amount' => $grand_total,
                    'payment_method' => 'payment_profile',
                    'payment_profile' => array(
                    'customer_code' =>$customer_code,
                    'card_id' => $card_id,
                    'complete' =>true)
                    );
                }

                $where = array('user_id' => $usid, 'business_id' => $business_id);
                $result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
                if(empty($result_card) && ($savecard == '1')){
                    $legato_token_data = array(
                            'language' => 'en',
                            'comments' => SITE_NAME,
                            'token' => array('name' => 'Test Card',
                            'code' => $token)
                        );
                    $apiurl='https://api.na.bambora.com/v1/profiles';
                    $responce = $this->bomborapay->profile_create('POST',$apiurl, $legato_token_data, $marchant_id);
                    //echo $marchant_id;
                    //print_r($responce); die;
                    if($responce['code'] == '1'){
                        $transaction_data = array('user_id'=>$usid,
                            'business_id' => $business_id,
                            'card_id'=>$responce['customer_code']);

                        $this->dynamic_model->insertdata('user_card_save',$transaction_data);
                        $customer_code = $responce['customer_code'];
                    }
                }elseif(!empty($result_card) && ($savecard == '1')){
                    $customer_code = $result_card[0]['card_id'];
                    $apiurl="https://api.na.bambora.com/v1/profiles/$customer_code/cards";
                    $legato_token_data = array(
                            'token' => array('name' => 'Test Card',
                            'code' => $token)
                        );
                    $responce = $this->bomborapay->profile_create('POST',$apiurl, $legato_token_data,$marchant_id);
                    // echo $marchant_id;
                    //print_r($responce); die;
                    if($responce['code'] == '1'){
                        $customer_code = $responce['customer_code'];
                    }
                }

                if($savecard == '1'){
                    $payment_data = array(
                        'order_number' => $time,
                        'amount' => $grand_total,
                        'payment_method' => 'payment_profile',
                        'payment_profile' => array(
                        'customer_code' =>$customer_code,
                        'card_id' => $card_id,
                        'complete' =>true)
                    );
                }

                $payUrl='https://api.na.bambora.com/v1/payments';
                $res=$this->bomborapay->payment_checkout('POST',$payUrl,$payment_data,$marchant_id,$marchant_id_type);
                if(@$res['approved']==1)
                {
                    $where = array('user_id' => $usid,'business_id' => $business_id);
                    $result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
                    $ref_num  = getuniquenumber();
                    $payment_id =!empty(@$res['id']) ? $res['id'] : $ref_num;
                    $authorizing_merchant_id =!empty(@$res['authorizing_merchant_id']) ? $res['authorizing_merchant_id'] : '';
                    $payment_type =!empty(@$res['type']) ? $res['type'] : '';
                    $payment_method =!empty(@$res['payment_method']) ? $res['payment_method'] : '';
                    $amount =!empty(@$res['amount']) ? $res['amount'] : '';*/

                			$savecard      = $this->input->post('savecard');
							$card_id       = $this->input->post('card_id');
							$customer_name = $this->input->post('customer_name');
							$number        = $this->input->post('number');
							$expiry_month  = $this->input->post('expiry_month');
							$expiry_year   = $this->input->post('expiry_year');
							$cvd           = $this->input->post('cvd');
							$country_code  = $this->input->post('country_code');

							$res_data = clover_api_card_profile_check($usid,$savecard,$customer_name,$number,$expiry_month,$expiry_year,$cvd,$country_code,$business_id,$token);
							$customer_code= $res_data['customer_code'];
							$marchant_id  = $res_data['marchant_id'];
							$country_code = $res_data['country_code'];
							$clover_key   = $res_data['clover_key'];
							$access_token = $res_data['access_token'];
							$currency     = $res_data['currency'];


							$user_cc_no   = $number;
							$user_cc_mo   = $expiry_month;
							$user_cc_yr   = $expiry_year;
							$user_cc_cvv  = $cvd;
							$user_zip     = '';
							$amount       = $grand_total;
							$taxAmount    = 0;
							$res  = clover_api_payment_checkout($user_cc_no,$user_cc_mo,$user_cc_yr,$user_cc_cvv,$user_zip,$amount,$taxAmount,$marchant_id,$clover_key,$access_token,$currency,$token);

							//{ "id" : "R48Q7GPMY2FQY", "amount" : 1800, "amount_refunded" : 0, "currency" : "usd", "created" : 1616661109846, "captured" : true, "ref_num" : "108400500020", "auth_code" : "OK2809", "outcome" : { "network_status" : "approved_by_network", "type" : "authorized" }, "paid" : true, "status" : "succeeded", "source" : { "id" : "clv_1TSTS1iWemARMj4AXHxHikLV", "brand" : "AMEX", "cvc_check" : "unchecked", "exp_month" : "11", "exp_year" : "2025", "first6" : "378282", "last4" : "0005" } }

						//{"message":"400 Bad Request","error":{"code":"token_already_used","message":"You cannot use a clover token more than once unless it is marked as multipay."}}


						//{"paymentId":"30PFKD66YXNCP","result":"APPROVED","authCode":"OK7823","token":"DGY73XR6DDDF0","vaultedCard":{"first6":"411111","last4":"1111","expirationDate":"0321","token":"1894469479681111"}}

						//echo $res['message'];die;
						//echo $res['message'];die;
						if(@$res->status == 'succeeded')
						{
							$where = array('user_id' => $usid,
								'business_id' => $business_id,
							);
							$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);

							$ref_num    = getuniquenumber();
							$payment_id = !empty($res->id) ? $res->id : $ref_num;
							$authorizing_merchant_id = $res->source->id;
							$payment_type   = 'Card';
							$payment_method = 'Online';
							$amount         = $amount;

                    //Insert data in transaction table
                    $transaction_data = array(
                        'authorizing_merchant_id' => $authorizing_merchant_id,
                        'payment_type' => $payment_type,
                        'payment_method' => $payment_method,
                        'amount'                 =>$amount,
                        'trx_id'                =>$payment_id,
                        'order_number'          =>$time,
                        'transaction_type'      =>3,
                        'payment_status'        =>"Success",
                        'transaction_date' => date('Y-m-d'),
                        'create_dt'             =>$time,
                        'update_dt'             =>$time,
                        'responce_all'=>json_encode($res)
                            );
                    $where1 = array('id' => $transaction_id);
                    $this->dynamic_model->updateRowWhere('transactions', $where1, $transaction_data);

                        //after that insert into user booking table
                    $sub_total=$amount*$quantity;
                    $passData =   array(
                        'amount'        =>$amount,
                        'sub_total'     =>$sub_total,
                        'status'        =>"Success",
                        'create_dt'     =>$time,
                        'update_dt'     =>$time,
                    );

                    if ($this->input->post('tip_comment')) {
                        $passData['tip_comment'] = $this->input->post('tip_comment');
                    }

                    $where1 = array('transaction_id' => $transaction_id);
                    $this->dynamic_model->updateRowWhere('user_booking', $where1, $passData);


                    $response  = array('amount' =>number_format((float)$sub_total, 2, '.', ''),'transaction_date'=>date('d M Y'));
                    if($transaction_id)
                    {
                        $arg['status']    = 1;
                        $arg['error_code']= HTTP_OK;
                        $arg['error_line']= __line__;
                        $arg['message']   = $this->lang->line('payment_succ');
                        $arg['transaction_id']   = $transaction_id;
                        $arg['data']      = $response;
                    }
                    else
                    {
                        $arg['status']    = 0;
                        $arg['error_code'] = HTTP_NOT_FOUND;
                        $arg['error_line']= __line__;
                        $arg['message']   = $this->lang->line('payment_fail');
                        $arg['data']      =json_decode('{}');
                    }
                    }else{
                        $arg['status']    = 0;
                        $arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
                        $arg['error_line']= __line__;
                        $arg['message']   = @$res->error->message;
                        $arg['data']      =json_decode('{}');
                    }
                    }
                }
            }
        }
      echo json_encode($arg);
}

	public function buy_now_services_cash_post()
	{
		$arg    = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}else
		{
			$userdata = checkuserid();
			if($userdata['status'] != 1){
			$arg = $userdata;
			}
			else
			{
			$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
				{
					$this->form_validation->set_rules('service_id','Service Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('transaction_id','Transaction Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('payment_transaction_id','Transaction Note', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
					if($this->form_validation->run() == FALSE)
					{
						$arg['status']  = 0;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					}else
					{
						$service_id = $this->input->post('service_id');
						$transaction_id = $this->input->post('transaction_id');
						$payment_transaction_id = $this->input->post('payment_transaction_id');
						$comment = $this->input->post('comment');
						$where = array('id'=>$service_id,'status' => 'Active');
						$product_data = $this->dynamic_model->getdatafromtable('service',$where);
						$Amt=0;
						$usid =$userdata['data']['id'];
						$name =$userdata['data']['name'];
						$lastname =$userdata['data']['lastname'];
						$time = time();
						$pass_start_date=$pass_end_date=$pass_status='';
						$where = array('transaction_id'=>$transaction_id);
						$booking_data = $this->dynamic_model->getdatafromtable('user_booking',$where);
						if(empty($booking_data)){
						$arg['status']    = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['message']   = @$res['message'];
							$arg['data']      =json_decode('{}');
							echo json_encode($arg); die;
						}
						$quantity = $booking_data[0]['quantity'];
						$grand_total = $booking_data[0]['amount'];
						$grand_total = number_format((float)$grand_total, 2, '.', '');
						$passes_total_count     = 0;
						$passes_remaining_count  = 0;
						$pass_start_date = 0;
						$pass_end_date = 0;
						$service = $this->db->get_where('service', array('id' => $service_id))->row_array();
						$business_id = $service['business_id'];

						$mid = getUserMarchantId($business_id);
						$marchant_id = $mid['marchant_id'];
						$marchant_id_type = $mid['marchant_id_type'];
						$ref_num  = getuniquenumber();
						$payment_id =$ref_num;
						$authorizing_merchant_id = '';
						$payment_type = 'Cash';
						$payment_method = 'Cash Online';
						$amount = $grand_total;
						$transaction_data = array(
						'authorizing_merchant_id' => $authorizing_merchant_id,
						'payment_type' => $payment_type,
						'payment_method' => $payment_method,
						'amount'                 =>$amount,
						'trx_id'                =>$payment_id,
						'order_number'          =>$time,
						'transaction_type'      =>3,
						'payment_status'        =>"Success",
						'transaction_date' => date('Y-m-d'),
						'create_dt'             =>$time,
						'update_dt'             =>$time
							);
						$where1 = array('id' => $transaction_id);
						$this->dynamic_model->updateRowWhere('transactions', $where1, $transaction_data);
						$sub_total=$amount*$quantity;
						$passData =   array(
						'amount'        =>$amount,
						'sub_total'     =>$sub_total,
						'status'        =>"Success",
						'create_dt'     =>$time,
						'update_dt'     =>$time,
						);
                        if ($this->input->post('tip_comment')) {
                            $passData['tip_comment'] = $this->input->post('tip_comment');
                        }
						$where1 = array('transaction_id' => $transaction_id);
						$this->dynamic_model->updateRowWhere('user_booking', $where1, $passData);
						$response  = array('amount' =>number_format((float)$sub_total, 2, '.', ''),'transaction_date'=>date('d M Y'));
						if($transaction_id)
						{
							$arg['status']    = 1;
							$arg['error_code'] = HTTP_OK;
							$arg['error_line']= __line__;
							$arg['message']   =$this->lang->line('payment_succ');
							$arg['transaction_id']   = $transaction_id;
							$arg['data']      = $response;
						}
						else
						{
							$arg['status']    = 0;
							$arg['error_code'] = HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['message']   = $this->lang->line('payment_fail');
							$arg['data']      =json_decode('{}');
						}


					}
				}else{
					$arg['status']    = 0;
					$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
					$arg['error_line']= __line__;
					$arg['message']   = @$res['message'];
					$arg['data']      =json_decode('{}');
				}
			}
		}
		echo json_encode($arg);
	}

	public function services_list_post()
    {
        $arg = array();
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
        }
        else
        {
            $userdata = checkuserid('1');
           if($userdata['status'] != 1){
             $arg = $userdata;
            }
            else
            {
              $_POST = json_decode(file_get_contents("php://input"), true);
              if($_POST)
              {
                $this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
                        'required' => $this->lang->line('page_no'),
                        'numeric' => $this->lang->line('page_no_numeric'),
                    ));
                 $this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
                        'required' => $this->lang->line('business_id_req'),
                        'numeric' => $this->lang->line('business_id_numeric'),
                    ));
                if($this->form_validation->run() == FALSE)
                {
                    $arg['status']  = 0;
                    $arg['error_code'] = 0;
                    $arg['error_line']= __line__;
                    $arg['message'] = get_form_error($this->form_validation->error_array());
                }
                else
                {
                    $response=array();
                    $page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
                    $page_no= $page_no-1;
                    $limit    = config_item('page_data_limit');
					$offset = $limit * $page_no;
					$instructorId 		=	$userdata['data']['id'];
					$business_id=  $this->input->post('business_id');

					$query = 'SELECT ser.id, ser.business_id, ser.service_name, ser.skills as service_category_id, ser.service_type, ms.name as service_category, ser.start_date_time as start_date, ser.end_date_time  as end_date, ser.is_client_visible, ser.duration, ser.amount, ser.tax1, ser.tax1_rate,  ser.tax2, ser.tax1_label, ser.tax2_label, ser.tax2_rate, ser.tip_option, ser.time_needed, ser.description, ser.cancel_policy, ser.create_dt, ser.create_dt as create_dt_utc FROM service ser LEFT JOIN manage_skills ms on (ms.id = ser.skills) JOIN service_instructor si on (si.service_id = ser.id) where ser.business_id = '.$business_id.' AND ser.status = "Active" AND ser.is_client_visible = "yes" AND si.instructor_id = '.$instructorId.' LIMIT '.$limit.' OFFSET '.$offset;

					$service_data = $this->dynamic_model->getQueryResultArray($query);
					 if (!empty($service_data)) {
                        array_walk ( $service_data, function (&$key) {
                            $workshop_price = $key['amount'];
                            $workshop_tax_price = 0;
                            $tax1_rate_val = 0;
                            $tax2_rate_val = 0;
                            $workshop_total_price = $workshop_price;
                            if(strtolower($key['tax1']) == 'yes'){
                                $tax1_rate = floatVal($key['tax1_rate']);
                                $tax1_rate_val = (($workshop_price * $tax1_rate) / 100);
                                $workshop_tax_price = $tax1_rate_val;
                                $workshop_total_price = $workshop_price + $tax1_rate_val;

                            }
                            if(strtolower($key['tax2']) == 'yes'){
                                $tax2_rate = floatVal($key['tax2_rate']);
                                $tax2_rate_val = (($workshop_price * $tax2_rate) / 100);
                                $workshop_tax_price = $tax1_rate_val + $tax2_rate_val;
                                $workshop_total_price = $workshop_total_price + $tax2_rate_val;
                            }

                            $key['tax1_rate'] = number_format($tax1_rate_val,2);
                            $key['tax2_rate'] = number_format($tax2_rate_val,2);
                            $key['service_tax_price'] = number_format($workshop_tax_price,2);
                            $key['service_total_price'] = number_format($workshop_total_price,2);
                        });
                    }


					if(!empty($service_data)){
                    	$arg['status']     = 1;
                        $arg['error_code']  = REST_Controller::HTTP_OK;
                        $arg['error_line']= __line__;
                        $arg['data']       = $service_data;
                        $arg['message']    = $this->lang->line('record_found');
                    }else{
                        $arg['status']     = 0;
                        $arg['error_code']  = REST_Controller::HTTP_OK;
                        $arg['error_line']= __line__;
                        $arg['data']       = array();
                        $arg['message']    = $this->lang->line('record_not_found');
                    }
                }
              }
            }
        }
       echo json_encode($arg);
    }

	public function services_details_post() {
		$arg    = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}else
		{
			$userdata = checkuserid();
			if($userdata['status'] != 1){
			$arg = $userdata;
			}
			else
			{
				$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST) {

					$this->form_validation->set_rules('service_id','Service Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric',array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));

					if($this->form_validation->run() == FALSE)
					{
						$arg['status']  = 0;
						$arg['error_code'] = 0;
						$arg['error_line']= __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$instructorId 		=	$userdata['data']['id'];
						$service_id			=  $this->input->post('service_id');
						$business_id 		=  $this->input->post('business_id');

						$service_data = $this->dynamic_model->getdatafromtable('service',
							array('is_client_visible' => 'yes', 'status' => 'Active', 'id' => $service_id, 'business_id' => $business_id),
							'id as service_id, service_name, create_dt, duration, amount as service_charge, tax1, tax2, tax1_rate, tax2_rate, cancel_policy, tip_option, description, time_needed'
						);

						if(!empty($service_data)){
							$resp = array();
							foreach($service_data as $val) {
								$temp = $val;
								$serviceId = $temp['service_id'];

								$imgePath = base_url().'uploads/user/';
								$instructor = $this->dynamic_model->getQueryResultArray("SELECT user.id, user.name, user.lastname, concat('".$imgePath."', user.profile_img) as profile_img, user.availability_status, instructor_details.total_experience as experience, instructor_details.appointment_fees, instructor_details.appointment_fees_type, instructor_details.skill FROM service_instructor JOIN user on (user.id = service_instructor.instructor_id) JOIN instructor_details on (instructor_details.user_id = user.id) where service_instructor.service_id = '".$serviceId."'  GROUP BY user.id");
								//AND service_instructor.instructor_id = ".$instructorId."
								array_walk ( $instructor, function (&$keys) {
									$keys['appointment_fees'] = floatVal($keys['appointment_fees']);
									$skills = $keys['skill'];
								});
								$temp["instructor"] = $instructor;
								$temp["create_dt"] = date("d M Y ",$temp['create_dt']);
								$temp["service_id"] = encode($serviceId);
								array_push($resp, $temp);
							}

							$arg['status']     = 1;
							$arg['error_code']  = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
							$arg['data']       = $resp;
							$arg['message']    = $this->lang->line('record_found');

						} else {
							$arg['status']     = 0;
							$arg['error_code']  = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
							$arg['data']       = json_decode('{}');
							$arg['message']    = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function service_scheduling_time_slot_post() {
		$arg    = array();
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
        }
        else
        {
           $userdata = checkuserid();
           if($userdata['status'] != 1){
             $arg = $userdata;
            }
            else
            {
                $_POST = json_decode(file_get_contents("php://input"), true);

                $this->form_validation->set_rules('business_id', 'business id', 'required|numeric');
                $this->form_validation->set_rules('service_id', 'service id', 'required|numeric');
                $this->form_validation->set_rules('service_date', 'service date', 'required');
                if($this->form_validation->run() == FALSE)
                {
                    $arg['status']  = 0;
                    $arg['error_code'] = 0;
                    $arg['error_line']= __line__;
                    $arg['message'] = get_form_error($this->form_validation->error_array());
                } else {

					$time_zone =  $this->input->get_request_header('Timezone', true);
                    $time_zone =  $time_zone ? $time_zone : 'UTC';
					date_default_timezone_set($time_zone);

                    $data = array();
                    $response = array();
                    $time = time();
                    $business_id	= 	$this->input->post('business_id');
                    $service_id		= 	$this->input->post('service_id');
                    //$instructor_id	=	$userdata['data']['id'];;
                    $instructor_id		= 	$this->input->post('instructor_id');
                    $instructor_id = $instructor_id ? $instructor_id : $userdata['data']['id'];
                    $service_date 	= 	$this->input->post('service_date');
                    $service_date 	= 	date('Y-m-d',$service_date);

                    $query = "SELECT s.*,l.location_name,l.address as location_address,l.address,l.capacity FROM business_shift_instructor as si join business_shift as s on si.shift_id = s.id join business_location as l on l.id = s.location_id where si.instructor = '".$instructor_id."' AND s.business_id = '".$business_id."'";
                    $collection = $this->dynamic_model->getQueryResultArray($query);

                    $sql = "SELECT * FROM service as ss WHERE ss.id = '".$service_id."'";
                    $services_collection = $this->dynamic_model->getQueryResultArray($sql);

                    //getQueryRowArray
                    //getQueryResultArray
                        if(!empty($services_collection)){
                         array_walk ( $services_collection, function (&$key) {
                            $workshop_price = floatVal($key['amount']);
                            $workshop_tax_price = 0;
                            $tax1_rate_val = 0;
                            $tax2_rate_val = 0;
                            $workshop_total_price = $workshop_price;
                            if(strtolower($key['tax1']) == 'yes'){
                                $tax1_rate = floatVal($key['tax1_rate']);
                                $tax1_rate_val = (($workshop_price * $tax1_rate) / 100);
                                $workshop_tax_price = $tax1_rate_val;
                                $workshop_total_price = $workshop_price + $tax1_rate_val;

                            }
                            if(strtolower($key['tax2']) == 'yes'){
                                $tax2_rate = floatVal($key['tax2_rate']);
                                $tax2_rate_val = (($workshop_price * $tax2_rate) / 100);
                                $workshop_tax_price = $tax1_rate_val + $tax2_rate_val;
                                $workshop_total_price = $workshop_total_price + $tax2_rate_val;
                            }

                            $key['tax1_rate'] = number_format($tax1_rate_val,2);
                            $key['tax2_rate'] = number_format($tax2_rate_val,2);
                            $key['service_tax_price'] = number_format($workshop_tax_price,2);
                            $key['service_total_price'] = number_format($workshop_total_price,2);
                        });
                    }


                    if (!empty($collection)) {
                        foreach ($collection as  $value){

                            $shift_id = $value['id'];
                            $duration = $services_collection[0]['duration'];
														$time_needed = $services_collection[0]['time_needed'];
                            $business_id = $value['business_id'];
                            $location_name = $value['location_name'];
                            $address = $value['address'];
                            $capacity = $value['capacity'];

                            //
                            $sql = "SELECT * FROM business_shift_scheduling as ss WHERE ss.shift_id = '".$shift_id."' AND ss.shift_date_str = '".$service_date."' AND ss.status = '1'";
                            $scheduling_collection = $this->dynamic_model->getQueryResultArray($sql);

                            if (!empty($scheduling_collection)) {
                                foreach ($scheduling_collection as  $key){
                                $shift_scheduling_id = $key['id'];
                                $start_time = $key['start_time'];
                                $end_time = $key['end_time'];
                                $shift_date = $key['shift_date'];
                                $slot = $this->getShiftTimeSlote($start_time,$end_time,$shift_date,$duration, $time_needed);
                               	$data[]= array('shift_id'=>$shift_id,
                                    'shift_scheduling_id'=>$shift_scheduling_id,
                                    'business_id'=>$business_id,
                                     'location_name'=>$location_name,
                                    'location_address'=>$address,
                                    'address'=>$address,
                                    'capacity'=>$capacity,
                                     'duration'=>$duration,
                                    'services_collection'=>$services_collection[0],
                                    /* '$sql'=>$sql, */
                                    'slot'=>$slot
                                    );
                                }
                            }
						}
						if (!empty($data)) {
							$arg['status']     = 1;
							$arg['error_code']  = HTTP_OK;
							$arg['error_line']= __line__;
							$arg['data']       = $data;
							$arg['message']    = $this->lang->line('record_found');
						} else {
							$arg['status']     = 0;
							$arg['error_code']  = 0;
							$arg['error_line']= __line__;
							$arg['message']    = 'no appointment found';
						}

					} else {
                        $arg['status']  = 0;
                        $arg['error_code'] = 0;
                        $arg['error_line']= __line__;
                        $arg['message'] = 'no appointment found';
                    }
                }

            }
        }
        echo json_encode($arg);
	}

	public function get_member_list_post()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = checkuserid('1');
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
		      $_POST = json_decode(file_get_contents("php://input"), true);
			  if($_POST)
			  {
			    $this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
					'required' => $this->lang->line('page_no'),
					'numeric' => $this->lang->line('page_no_numeric'),
				));
				$this->form_validation->set_rules('customer_id', 'Customer Id', 'required|numeric',array(
					'required' => 'Customer id is required',
					'numeric'  => 'Customer id is required',
				));
				if($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$response=array();
					$time=time();
					$usid = $this->input->post('customer_id');
					$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
					$page_no= $page_no-1;
					$limit    = config_item('page_data_limit');
					$offset = $limit * $page_no;

					$response = get_family_member($usid, $usid);



					// $condition=array("user_id"=>$usid,"is_deleted"=>'0');
					// $member_data= $this->dynamic_model->getdatafromtable('user_family_details',$condition,'*',$limit,$offset,"create_dt","DESC");
					// if(!empty($member_data)){
					//     foreach($member_data as $value)
			        //     {
			        //     	$memberdata['memeber_id']   = $value['id'];
			        //     	$memberdata['member_name'] = ucwords($value['member_name']);
			        //     	$memberdata['image']        = base_url().'uploads/user/'.$value['photo'];
			        //     	$memberdata['relation']     = get_family_name($value['relative_id']);
			        //     	$memberdata['relative_id']     = $value['relative_id'];
			        //     	$memberdata['dob']     = $value['dob'];
                    //         $memberdata['gender']     = $value['gender'];
			        //     	$memberdata['create_dt']    = date("d M Y ",$value['create_dt']);
			        //     	$response[]	                = $memberdata;
			        //     }
			        // }
					if($response){
						$arg['status']     = 1;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = $response;
						$arg['message']    = $this->lang->line('record_found');
					}else{
						$arg['status']     = 0;
						$arg['error_code']  = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = array();
					 	$arg['message']    = $this->lang->line('record_not_found');
					}
			    }
			  }
			}
		}
	   echo json_encode($arg);
	}

	public function book_services_post()
	{
		$arg    = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = checkuserid();
			if($userdata['status'] != 1){
			$arg = $userdata;
			}
			else
			{
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
				$this->form_validation->set_rules('customer','Customer', 'required|trim', array( 'required' => 'Customer is required'));
				$this->form_validation->set_rules('service_id','Service Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
				$this->form_validation->set_rules('quantity','Quantity', 'required', array( 'required' => $this->lang->line('quantity_required')));
				/*$this->form_validation->set_rules('grand_total','grand total','required|greater_than[0]',array(
						'required'   => $this->lang->line('amount_required'),
						'numeric'    => $this->lang->line('amount_valid')
					));
				*/
					$this->form_validation->set_rules('slot_date','Slot Date', 'required', array( 'required' => $this->lang->line('date_required')));
				$this->form_validation->set_rules('instructor_id','Instructor', 'required', array( 'required' => $this->lang->line('instructor_id_required')));
				if($this->form_validation->run() == FALSE)
				{
					$arg['status']  = 0;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$service_id   = $this->input->post('service_id');
					$where = array('id'=>$service_id,'status' => 'Active');
					$product_data = $this->dynamic_model->getdatafromtable('service',$where);
					$Amt=0;
					$usid = $this->input->post('customer');
					// $usid =$userdata['data']['id'];
					/* $name =$userdata['data']['name'];
					$lastname =$userdata['data']['lastname']; */
					$time = time();
					$pass_start_date=$pass_end_date=$pass_status='';
				//service_type => 1 passes 2 services 3 product

				$shift_schedule_id = $this->input->post('shift_scheduling_id');
				$shift_schedule_id = $shift_schedule_id ? $shift_schedule_id : '0';

				$start_time_unix = $this->input->post('start_time_unix');
				$end_time_unix = $this->input->post('end_time_unix');
				$shift_date = $this->input->post('shift_date');

				$service_type     = $this->input->post('service_type');
				$quantity         = $this->input->post('quantity');
				$token            = $this->input->post('token');
				$grand_total      = $this->input->post('grand_total');
				$grand_total           = number_format((float)$grand_total, 2, '.', '');
				$slot_date=  $this->input->post('slot_date');
				$slot_time_id=  $this->input->post('slot_time_id');
				$savecard= $this->input->post('savecard');
				$shift_instructor=$this->input->post('instructor_id');
				$shift_id=$this->input->post('shift_id');
				$family_user_id=$this->input->post('family_user_id');
				$family_user_id = $family_user_id ? $family_user_id : 0;
				$passes_total_count     = 0;
				$passes_remaining_count  = 0;

				$pass_start_date = 0;
				$pass_end_date = 0;
				$service = $this->db->get_where('service', array('id' => $service_id))->row_array();
				$business_id = $service['business_id'];

				$where = array('user_id' => $usid);
				$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
				$ref_num  = getuniquenumber();
				$payment_id = '';
				$authorizing_merchant_id = '';
				$payment_type = '';
				$payment_method = '';
				$amount =$grand_total;

				//Insert data in transaction table
				$transaction_data = array(
					'authorizing_merchant_id' => $authorizing_merchant_id,
					'payment_type' => $payment_type,
					'payment_method' => $payment_method,
					'user_id'                =>$usid,
					'amount'                 =>$amount,
					'trx_id'                =>$payment_id,
					'order_number'          =>$time,
					'transaction_type'      =>($service_type==2) ? 3 : $service_type,
					'payment_status'        =>"Confirm",
					'saved_card_id'         =>0,
					'create_dt'             =>$time,
					'transaction_date' => date('Y-m-d'),
					'update_dt'             =>$time,
						);
				$transaction_id=$this->dynamic_model->insertdata('transactions',$transaction_data);
							//after that insert into user booking table
				//echo $amount.'--'.$quantity; die;
						$sub_total=$amount*$quantity;
						$passData =   array(
						'business_id'   =>$business_id,
						'user_id'       =>$usid,
						'transaction_id'=>$transaction_id,
						'amount'        =>$amount,
						'service_type'  =>$service_type,
						'service_id'    =>$service_id,
						'quantity'      =>$quantity,
						'sub_total'     =>$sub_total,
						'status'        =>"Confirm",
						'create_dt'     =>$time,
						'update_dt'     =>$time,
						'passes_start_date' => $start_time_unix,
						'passes_end_date' => $end_time_unix,
						'shift_date' => $shift_date,
						'shift_search_date' => date('Y-m-d',$shift_date),
						'shift_instructor' => $shift_instructor,
						'shift_id' => $shift_id,
						'shift_schedule_id' => $shift_schedule_id,
						'family_user_id' => $family_user_id
						);
				//$passData['service_slot_id'] = $slot_time_id;
					$booking_id= $this->dynamic_model->insertdata('user_booking',$passData);
					if($service_type    ==  2){
						$insert_data    =   array(
						"business_id"           =>  $business_id,
						'booking_id'            =>  $booking_id,
						"user_id"               =>  $this->input->post('instructor_id'),
						"slot_id"               =>  $slot_time_id,
						"service_id"            =>  $service_id,
						"service_type"          =>  1,
						"slot_available_status" =>  "1",
						"slot_date"             =>  $slot_date,
						'create_dt'     =>$time,
						'update_dt'     =>$time
						);
					}
			$response  = array('amount' =>number_format((float)$sub_total, 2, '.', ''),'transaction_date'=>date('d M Y'));
			if($transaction_id)
				{
					$arg['status']    = 1;
					$arg['error_code'] = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['message']   =$this->lang->line('book_services');
					if($service_type    ==  2){
						$arg['booking_id']   = $booking_id;
					}
					$arg['transaction_id']   = $transaction_id;
					$arg['data']      = $response;
				}
				else
				{
					$arg['status']    = 0;
					$arg['error_code'] = HTTP_NOT_FOUND;
					$arg['error_line']= __line__;
					$arg['message']   = $this->lang->line('payment_fail');
					$arg['data']      =json_decode('{}');
				}


				}
			}
			}
		}
		echo json_encode($arg);
	}

	function getShiftTimeSlote($start_time, $end_time, $shift_date, $interval = 10, $time_needed = 15) {

		$dotay1 = date('Y-m-d', $shift_date);
		$st = date('h:i:s A', $start_time);
		$rt = $dotay1 . ' ' . $st;
		$start_time = strtotime($rt);

		$st = date('h:i:s A', $end_time);
		$rt = $dotay1 . ' ' . $st;
		$end_time = strtotime($rt);

		$slote = array();
		$unixTimeIntervel = (3600 + ($interval * 60));
		$hourdiff = ((($end_time) - ($start_time)) / 60);

		$j = 0;
		$intervalNew = 0;
		$increment_interval = $interval;
		$k = 0;
		for ($i = 0; $i <= $hourdiff; $i += $interval) {
			if ($j == 0) {
				$intervalNew = 0;
			} else {
				$intervalNew = $j;
			}
			$intervalNew = $intervalNew + $k;
			$endTime = strtotime("+" . $intervalNew . " minutes", $start_time);
			$st = date('h:i A', $endTime);
			$j += $increment_interval;

			$j = $j + $k;

			$newEndTime = strtotime("+" . $j . " minutes", $start_time);
			$newst = date('h:i A', $newEndTime);

			$d1 = $shift_date ? $shift_date : $d1;
			$rt = $d1 . ' ' . $st;
			$mt = strtotime($rt);
			$unixTimeIntervel = $mt;

			$sql = "SELECT * FROM user_booking as b WHERE b.passes_start_date = '" . $endTime . "' AND b.passes_end_date = '" . $newEndTime . "' AND b.shift_date = '" . $shift_date . "' AND service_type = '2' AND    status != 'Cancel'";
			$result = $this->dynamic_model->getQueryResultArray($sql);
			$is_available = 0;
			if (!empty($result)) {
				$is_available = 1;
			}

			if ($newEndTime <= $end_time) {
				$GivenDate = $shift_date;
				$CurrentDate = strtotime(date('Y-m-d'));
				$time = time();
				if ($GivenDate == $CurrentDate) {
					$star_time_slot = date('Y-m-d h:i A');
					$end_time_slot = date('Y-m-d h:i A', $endTime);
					$start = new DateTime($star_time_slot);
					$end = new DateTime($end_time_slot);

					if ($endTime < $time) {
						$is_available = 1;
					}
				}

				$slote[] = array('slot' => $st . '-' . $newst,
					'start_time_unix' => $endTime,
					'end_time_unix' => $newEndTime,
					'start_time' => date('Y-m-d h:i A', $endTime),
					'end_time' => date('Y-m-d h:i A', $newEndTime),
					'shift_date' => $shift_date,
					'is_available' => $is_available,
					'result' => date('Y-m-d h:i A', $time),
					'time_needed' => $time_needed,
				);
			}
			$k = $time_needed;
		}
		return $slote;
	}

	function getShiftTimeSlote_21042021($start_time,$end_time,$shift_date,$interval=10){

        $dotay1 = date('Y-m-d',$shift_date);
        $st = date('h:i:s A',$start_time);
        $rt = $dotay1 . ' ' . $st;
        $start_time = strtotime($rt);

        $st = date('h:i:s A',$end_time);
        $rt = $dotay1 . ' ' . $st;
        $end_time = strtotime($rt);

        $slote = array();
        $unixTimeIntervel = (3600 + ($interval * 60));
        $hourdiff = ((($end_time) - ($start_time)) / 60);

        $j = 0;
        $intervalNew = 0;
        $increment_interval = $interval;
        $k=0;
        for ($i = 0; $i <= $hourdiff; $i += $interval) {
            if ($j == 0) {
                $intervalNew = 0;
            }else{
                $intervalNew = $j;
            }
            $intervalNew = $intervalNew + $k;
            $endTime = strtotime("+" . $intervalNew . " minutes", $start_time);
            $st = date('h:i A', $endTime);
            $j += $increment_interval;

            $j = $j + $k;

            $newEndTime = strtotime("+" . $j . " minutes", $start_time);
             $newst = date('h:i A', $newEndTime);

            $d1 = $shift_date ? $shift_date : $d1;
            $rt = $d1 . ' ' . $st;
            $mt = strtotime($rt);
            $unixTimeIntervel = $mt;

            $sql = "SELECT * FROM user_booking as b WHERE b.passes_start_date = '".$endTime."' AND b.passes_end_date = '".$newEndTime."' AND b.shift_date = '".$shift_date."' AND service_type = '2' AND    status != 'Cancel'";
            $result = $this->dynamic_model->getQueryResultArray($sql);
            $is_available = 0;
            if(!empty($result)){
                $is_available = 1;
            }

            if( $newEndTime <= $end_time ){
				$GivenDate = $shift_date;
				$CurrentDate = strtotime(date('Y-m-d'));
                $time = time();
				if($GivenDate == $CurrentDate){
					$star_time_slot = date('Y-m-d h:i A');
					$end_time_slot  = date('Y-m-d h:i A', $endTime);
					$start = new DateTime($star_time_slot);
					$end = new DateTime($end_time_slot);

					if($endTime < $time){
                        $is_available = 1;
                    }
				}

				$slote[] = array('slot' => $st .'-'. $newst,
                        'start_time_unix' => $endTime,
                        'end_time_unix' => $newEndTime,
                        'start_time' => date('Y-m-d h:i A', $endTime),
                        'end_time' => date('Y-m-d h:i A', $newEndTime),
                        'shift_date' => $shift_date,
						'is_available' => $is_available,
                        'result' => date('Y-m-d h:i A', $time),
                );
            }
            $k=15;
        }
        return $slote;
    }

	public function getInstructorShiftDate_post() {
        $arg    = array();
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
        }
        else
        {
           $userdata = checkuserid();
           if($userdata['status'] != 1){
             $arg = $userdata;
            }
            else
            {
                $_POST = json_decode(file_get_contents("php://input"), true);

                $this->form_validation->set_rules('business_id', 'business id', 'required|numeric');
                $this->form_validation->set_rules('service_id', 'service id', 'required|numeric');
               //  $this->form_validation->set_rules('instructor_id', 'instructor id', 'required|numeric');
                if($this->form_validation->run() == FALSE)
                {
                    $arg['status']  = 0;
                    $arg['error_code'] = 0;
                    $arg['error_line']= __line__;
                    $arg['message'] = get_form_error($this->form_validation->error_array());
                } else {

                    $data = array();
                    $response = array();
                    $time = time();
                    $business_id= $this->input->post('business_id');
                    $service_id= $this->input->post('service_id');
                    // $instructor_id = $this->input->post('instructor_id');
					$instructor_id =$userdata['data']['id'];

                    $query = "SELECT s.*,l.location_name,l.address,l.capacity FROM business_shift_instructor as si join business_shift as s on si.shift_id = s.id join business_location as l on l.id = s.location_id where si.instructor = '".$instructor_id."' AND s.business_id = '".$business_id."'";
                    $collection = $this->dynamic_model->getQueryResultArray($query);
                    if (!empty($collection)) {
                        foreach ($collection as  $value){
                            $shift_id = $value['id'];
                            $duration = $value['duration'];
                            $business_id = $value['business_id'];
                            $location_name = $value['location_name'];
                            $address = $value['address'];
                            $capacity = $value['capacity'];
                            $sql = "SELECT * FROM business_shift_scheduling as ss WHERE ss.shift_id = '".$shift_id."' GROUP by ss.shift_date ORDER BY ss.shift_date ASC";
                            $scheduling_collection = $this->dynamic_model->getQueryResultArray($sql);
                            if (!empty($scheduling_collection)) {
                                foreach ($scheduling_collection as  $key){
                                $start_time = $key['start_time'];
                                $end_time = $key['end_time'];
                                $shift_date = $key['shift_date'];
                                $shift_date_str = $key['shift_date_str'];
                               $data[]= array('shift_id'=>$shift_id,
                                    'business_id'=>$business_id,
                                     'location_name'=>$location_name,
                                    'address'=>$address,
                                    'capacity'=>$capacity,
                                    'shift_date'=>$shift_date,
                                    'shift_date_str'=>$shift_date_str,
                                    );
                                }
                            }
                        }
                        $arg['status']     = 1;
                            $arg['error_code']  = HTTP_OK;
                            $arg['error_line']= __line__;
                            $arg['data']       = $data;
                            $arg['message']    = $this->lang->line('record_found');
                    } else {
                        $arg['status']  = 0;
                        $arg['error_code'] = 0;
                        $arg['error_line']= __line__;
                        $arg['message'] = 'no appointment found';
                    }
                }

            }
        }
        echo json_encode($arg);
    }

	public function buy_now_workshop_cash_post()
	{
		$arg    = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}else
		{
			$userdata = checkuserid();
			if($userdata['status'] != 1){
			$arg = $userdata;
			}
			else
			{
			$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
				{
					$this->form_validation->set_rules('business_id','Business Id', 'required|trim', array( 'required' => $this->lang->line('business_id_required')));
					$this->form_validation->set_rules('workshop_id','Workshop Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('customer_id','Customer Id', 'required|trim', array( 'required' => $this->lang->line('customer_id_required')));
					$this->form_validation->set_rules('transaction_id','Transaction Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('payment_transaction_id','Transaction Note', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
					if($this->form_validation->run() == FALSE)
					{
						$arg['status']  = 0;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					}else
					{
						$amount = $this->input->post('amount');
						$customer_id = $this->input->post('customer_id');
						$business_id = $this->input->post('business_id');
						$workshop_id = $this->input->post('workshop_id');
						$transaction_id = $this->input->post('transaction_id');
						$payment_transaction_id =$this->input->post('payment_transaction_id');
						$comment = $this->input->post('comment');

						$where = array('id' => $workshop_id);
						$product_data = $this->dynamic_model->getdatafromtable('business_workshop_master',$where);


						$Amt = 0;
						$usid = $userdata['data']['id'];
						$name = $userdata['data']['name'];
						$lastname = $userdata['data']['lastname'];
						$time = time();
						$pass_start_date = $pass_end_date = $pass_status='';

						$payment_type ='Cash';
						$payment_method ='Cash Online';
						 $transaction_data = array(
                        'payment_type' => $payment_type,
                        'payment_method' => $payment_method,
                        'user_id'                =>$customer_id,
                        'amount'                 =>$amount,
                        'trx_id'                =>$transaction_id,
                        'order_number'          =>$time,
                        'transaction_type'      => 4,
                        'payment_status'        =>"Success",
                        'saved_card_id'         =>0,
                        'create_dt'             =>$time,
                        'update_dt'             =>$time,
                            );
                    	$transactionId=$this->dynamic_model->insertdata('transactions',$transaction_data);
                    	$passData =   array(
		                    'business_id' => $business_id,
		                    'user_id' => $customer_id,
		                    'create_by' => $usid,
		                    'transaction_id'=>$transactionId,
		                    'amount' => $amount,
		                    'service_type' => '4',
		                    'service_id' => $workshop_id,
		                    'sub_total' => $amount,
		                    'passes_status' => '1',
		                    'status' => "Success",
		                    'create_dt' => $time,
		                    'update_dt' => $time,
		                );

	                    $booking_id= $this->dynamic_model->insertdata('user_booking',$passData);
	            		$arg['status']    = 1;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line']= __line__;
						$arg['message']   =$this->lang->line('payment_succ');
						$arg['transaction_id']   = $transaction_id;
						$arg['data']      = $product_data;
					}
				}else{
					$arg['status']    = 0;
					$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
					$arg['error_line']= __line__;
					$arg['message']   = '';
					$arg['data']      =json_decode('{}');
				}
			}
		}
		echo json_encode($arg);
	}

	public function buy_now_workshop_post()
	{
	    $arg    = array();
	    $version_result = version_check_helper1();
	    if($version_result['status'] != 1 )
	    {
	        $arg = $version_result;
	    }
	    else
	    {
        $userdata = checkuserid();
        if($userdata['status'] != 1){
         $arg = $userdata;
        }
        else
        {
        $_POST = json_decode(file_get_contents("php://input"), true);
        if($_POST)
        {
            $this->form_validation->set_rules('business_id','Business_id Id', 'required|trim', array( 'required' => 'Business_id id is required'));
            $this->form_validation->set_rules('workshop_id','Workshop Id', 'required|trim', array( 'required' => 'Workshop id is required.'));
            $this->form_validation->set_rules('workshop_schdule_id','Workshop Schdule Id', 'required|trim', array( 'required' => 'Workshop Schdule id is required.'));
            $this->form_validation->set_rules('customer_id','Customer Id', 'required|trim', array( 'required' => $this->lang->line('customer_id_required')));
            //$this->form_validation->set_rules('quantity','Quantity', 'required', array( 'required' => $this->lang->line('quantity_required')));
            $this->form_validation->set_rules('grand_total','grand total','required|greater_than[0]',array(
                    'required' => $this->lang->line('amount_required'),
                    'numeric' => $this->lang->line('amount_valid')
                  ));
            //$this->form_validation->set_rules('slot_date','Slot Date', 'required', array( 'required' => $this->lang->line('date_required')));
            $this->form_validation->set_rules('token','Token', 'required', array( 'required' => $this->lang->line('token_required')));
             if($this->form_validation->run() == FALSE)
            {
                $arg['status']  = 0;
                $arg['message'] = get_form_error($this->form_validation->error_array());
            }
            else
            {
                $business_id = $this->input->post('business_id');
                $workshop_id = $this->input->post('workshop_id');
                $customer_id = $this->input->post('customer_id');
                $workshop_schdule_id = $this->input->post('workshop_schdule_id');
                $where = array('id' => $workshop_id);
                $product_data = $this->dynamic_model->getdatafromtable('business_workshop_master',$where);

                $where = array('id' => $workshop_schdule_id);
                $schdule_data = $this->dynamic_model->getdatafromtable('business_workshop_schdule',$where);
                $start_time_unix = $schdule_data[0]['start'];
                $end_time_unix = $schdule_data[0]['end'];

                $Amt=0;
                $usid = $userdata['data']['id'];
                $name = $userdata['data']['name'];
                $lastname =$userdata['data']['lastname'];
                $time = time();
                $pass_start_date=$pass_end_date=$pass_status='';
                //service_type => 1 passes 2 services 3 product
                $service_type     = 4;
                $quantity         = 1;
                $token            = $this->input->post('token');
                $grand_total      = $this->input->post('grand_total');
                $grand_total           = number_format((float)$grand_total, 2, '.', '');

                $savecard= $this->input->post('savecard');
                $passes_total_count     = 0;
                $passes_remaining_count  = 0;

                $pass_start_date = 0;
                $pass_end_date = 0;

                $savecard= $this->input->post('savecard');
                $card_id = $this->input->post('card_id');

                $mid = getUserMarchantId($business_id);
                $marchant_id = $mid['marchant_id'];
                $marchant_id_type = $mid['marchant_id_type'];

                if(!empty($token)){
                    $payment_data = array(
                        'order_number' => $time,
                        'amount' => $grand_total,
                        'payment_method' => 'token',
                        'token' => array(
                        'name' =>'Test Card',
                        'code' => $token,
                        'complete' =>true
                               )
                     );
                }else if(!empty($card_id)){

                $where = array('user_id' => $usid, 'business_id' => $business_id);
                $result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
                $customer_code = $result_card[0]['card_id'];

                $payment_data = array(
                    'order_number' => $time,
                    'amount' => $grand_total,
                    'payment_method' => 'payment_profile',
                    'payment_profile' => array(
                    'customer_code' =>$customer_code,
                    'card_id' => $card_id,
                    'complete' =>true)
                    );
                }

                /* start */
                $where = array('user_id' => $usid);
                $result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
                if(empty($result_card) && ($savecard == '1')){
                    $legato_token_data = array(
                            'language' => 'en',
                            'comments' => SITE_NAME,
                            'token' => array('name' => 'Test Card',
                            'code' => $token)
                        );
                    $apiurl='https://api.na.bambora.com/v1/profiles';
                    $responce = $this->bomborapay->profile_create('POST',$apiurl, $legato_token_data, $marchant_id);
                    //echo $marchant_id;
                    //print_r($responce); die;
                    if($responce['code'] == '1'){
                        $transaction_data = array('user_id'=>$usid,
                                              'card_id'=>$responce['customer_code']);
                        $this->dynamic_model->insertdata('user_card_save',$transaction_data);
                        $customer_code = $responce['customer_code'];
                    }
                }elseif(!empty($result_card) && ($savecard == '1')){
                    $customer_code = $result_card[0]['card_id'];
                    $apiurl="https://api.na.bambora.com/v1/profiles/$customer_code/cards";
                    $legato_token_data = array(
                            'token' => array('name' => 'Test Card',
                            'code' => $token)
                        );
                    $responce = $this->bomborapay->profile_create('POST',$apiurl, $legato_token_data,$marchant_id);
                    // echo $marchant_id;
                    //print_r($responce); die;
                    if($responce['code'] == '1'){
                        $customer_code = $responce['customer_code'];
                    }
                }

                if($savecard == '1'){
                    $payment_data = array(
                        'order_number' => $time,
                        'amount' => $grand_total,
                        'payment_method' => 'payment_profile',
                        'payment_profile' => array(
                        'customer_code' =>$customer_code,
                        'card_id' => $card_id,
                        'complete' =>true)
                    );
                }
                /* end */
                $payUrl='https://api.na.bambora.com/v1/payments';
                $res=$this->bomborapay->payment_checkout('POST',$payUrl,$payment_data,$marchant_id,$marchant_id_type);
                if(@$res['approved']==1)
                {
                    $where = array('user_id' => $customer_id);
                    $result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
                    $ref_num  = getuniquenumber();
                    $payment_id =!empty(@$res['id']) ? $res['id'] : $ref_num;
                    $authorizing_merchant_id =!empty(@$res['authorizing_merchant_id']) ? $res['authorizing_merchant_id'] : '';
                    $payment_type =!empty(@$res['type']) ? $res['type'] : '';
                    $payment_method =!empty(@$res['payment_method']) ? $res['payment_method'] : '';
                    $amount =!empty(@$res['amount']) ? $res['amount'] : '';
                    //Insert data in transaction table
                    $transaction_data = array(
                        'authorizing_merchant_id' => $authorizing_merchant_id,
                        'payment_type' => $payment_type,
                        'payment_method' => $payment_method,
                        'user_id'                =>$customer_id,
                        'amount'                 =>$amount,
                        'trx_id'                =>$payment_id,
                        'order_number'          =>$time,
                        'transaction_type'      => 4,
                        'payment_status'        =>"Success",
                        'saved_card_id'         =>0,
                        'create_dt'             =>$time,
                        'update_dt'             =>$time,
                        'responce_all'=>json_encode($res)
                            );
                    $transaction_id=$this->dynamic_model->insertdata('transactions',$transaction_data);
                        //after that insert into user booking table
                    $sub_total=$amount*$quantity;
                    $passData =   array(
                    'business_id'   =>$business_id,
                    'user_id'       =>$customer_id,
                    'create_by'       =>$usid,
                    'transaction_id'=>$transaction_id,
                    'amount'        =>$amount,
                    'service_type'  =>$service_type,
                    'service_id'    =>$workshop_id,
                    'class_id'      =>$workshop_schdule_id,
                    'quantity'      =>$quantity,
                    'sub_total'     =>$sub_total,
                    'status'        =>"Success",
                    'create_dt'     =>$time,
                    'update_dt'     =>$time,
                    'passes_start_date' => $start_time_unix,
                    'passes_end_date' => $end_time_unix,
                    );

                    $booking_id= $this->dynamic_model->insertdata('user_booking',$passData);

                    $response  = array('amount' =>number_format((float)$sub_total, 2, '.', ''),'transaction_date'=>date('d M Y'));
                    if($transaction_id)
                    {
                        $arg['status']    = 1;
                        $arg['error_code'] = HTTP_OK;
                        $arg['error_line']= __line__;
                        $arg['message']   =$this->lang->line('payment_succ');
                        $arg['transaction_id']   = $transaction_id;
                        $arg['data']      = $response;
                    }
                    else
                    {
                        $arg['status']    = 0;
                        $arg['error_code'] = HTTP_NOT_FOUND;
                        $arg['error_line']= __line__;
                        $arg['message']   = $this->lang->line('payment_fail');
                        $arg['data']      =json_decode('{}');
                    }
                    }else{
                        $arg['status']    = 0;
                        $arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
                        $arg['error_line']= __line__;
                        $arg['message']   = @$res['message'];
                        $arg['data']      =json_decode('{}');
                    }
                    }
                }
            }
        }
      	echo json_encode($arg);
    }

    public function clover_buy_now_workshop_post()
	{
	    $arg    = array();
	    $version_result = version_check_helper1();
	    if($version_result['status'] != 1 )
	    {
	        $arg = $version_result;
	    }
	    else
	    {
        $userdata = checkuserid();
        if($userdata['status'] != 1){
         $arg = $userdata;
        }
        else
        {
        $_POST = json_decode(file_get_contents("php://input"), true);
        if($_POST)
        {
            $this->form_validation->set_rules('business_id','Business_id Id', 'required|trim', array( 'required' => 'Business_id id is required'));
            $this->form_validation->set_rules('workshop_id','Workshop Id', 'required|trim', array( 'required' => 'Workshop id is required.'));
            $this->form_validation->set_rules('workshop_schdule_id','Workshop Schdule Id', 'required|trim', array( 'required' => 'Workshop Schdule id is required.'));
            $this->form_validation->set_rules('customer_id','Customer Id', 'required|trim', array( 'required' => $this->lang->line('customer_id_required')));
            //$this->form_validation->set_rules('quantity','Quantity', 'required', array( 'required' => $this->lang->line('quantity_required')));
            $this->form_validation->set_rules('grand_total','grand total','required|greater_than[0]',array(
                    'required' => $this->lang->line('amount_required'),
                    'numeric' => $this->lang->line('amount_valid')
                  ));
            //$this->form_validation->set_rules('slot_date','Slot Date', 'required', array( 'required' => $this->lang->line('date_required')));
            $this->form_validation->set_rules('token','Token', 'required', array( 'required' => $this->lang->line('token_required')));
             if($this->form_validation->run() == FALSE)
            {
                $arg['status']  = 0;
                $arg['message'] = get_form_error($this->form_validation->error_array());
            }
            else
            {
                $business_id = $this->input->post('business_id');
                $workshop_id = $this->input->post('workshop_id');
                $customer_id = $this->input->post('customer_id');
                $workshop_schdule_id = $this->input->post('workshop_schdule_id');
                $where = array('id' => $workshop_id);
                $product_data = $this->dynamic_model->getdatafromtable('business_workshop_master',$where);

                $where = array('id' => $workshop_schdule_id);
                $schdule_data = $this->dynamic_model->getdatafromtable('business_workshop_schdule',$where);
                $start_time_unix = $schdule_data[0]['start'];
                $end_time_unix = $schdule_data[0]['end'];

                $Amt=0;
                $usid = $userdata['data']['id'];
                $name = $userdata['data']['name'];
                $lastname =$userdata['data']['lastname'];
                $time = time();
                $pass_start_date=$pass_end_date=$pass_status='';
                //service_type => 1 passes 2 services 3 product
                $service_type     = 4;
                $quantity         = 1;
                $token            = $this->input->post('token');
                $grand_total      = $this->input->post('grand_total');
                $grand_total           = number_format((float)$grand_total, 2, '.', '');

                //$savecard= $this->input->post('savecard');
                $passes_total_count     = 0;
                $passes_remaining_count  = 0;

                $pass_start_date = 0;
                $pass_end_date = 0;

                /*$savecard= $this->input->post('savecard');
                $card_id = $this->input->post('card_id');

                $mid = getUserMarchantId($business_id);
                $marchant_id = $mid['marchant_id'];
                $marchant_id_type = $mid['marchant_id_type'];

                if(!empty($token)){
                    $payment_data = array(
                        'order_number' => $time,
                        'amount' => $grand_total,
                        'payment_method' => 'token',
                        'token' => array(
                        'name' =>'Test Card',
                        'code' => $token,
                        'complete' =>true
                               )
                     );
                }else if(!empty($card_id)){

                $where = array('user_id' => $usid, 'business_id' => $business_id);
                $result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
                $customer_code = $result_card[0]['card_id'];

                $payment_data = array(
                    'order_number' => $time,
                    'amount' => $grand_total,
                    'payment_method' => 'payment_profile',
                    'payment_profile' => array(
                    'customer_code' =>$customer_code,
                    'card_id' => $card_id,
                    'complete' =>true)
                    );
                }

                $where = array('user_id' => $usid);
                $result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
                if(empty($result_card) && ($savecard == '1')){
                    $legato_token_data = array(
                            'language' => 'en',
                            'comments' => SITE_NAME,
                            'token' => array('name' => 'Test Card',
                            'code' => $token)
                        );
                    $apiurl='https://api.na.bambora.com/v1/profiles';
                    $responce = $this->bomborapay->profile_create('POST',$apiurl, $legato_token_data, $marchant_id);
                    //echo $marchant_id;
                    //print_r($responce); die;
                    if($responce['code'] == '1'){
                        $transaction_data = array('user_id'=>$usid,
                                              'card_id'=>$responce['customer_code']);
                        $this->dynamic_model->insertdata('user_card_save',$transaction_data);
                        $customer_code = $responce['customer_code'];
                    }
                }elseif(!empty($result_card) && ($savecard == '1')){
                    $customer_code = $result_card[0]['card_id'];
                    $apiurl="https://api.na.bambora.com/v1/profiles/$customer_code/cards";
                    $legato_token_data = array(
                            'token' => array('name' => 'Test Card',
                            'code' => $token)
                        );
                    $responce = $this->bomborapay->profile_create('POST',$apiurl, $legato_token_data,$marchant_id);
                    // echo $marchant_id;
                    //print_r($responce); die;
                    if($responce['code'] == '1'){
                        $customer_code = $responce['customer_code'];
                    }
                }

                if($savecard == '1'){
                    $payment_data = array(
                        'order_number' => $time,
                        'amount' => $grand_total,
                        'payment_method' => 'payment_profile',
                        'payment_profile' => array(
                        'customer_code' =>$customer_code,
                        'card_id' => $card_id,
                        'complete' =>true)
                    );
                }

                $payUrl='https://api.na.bambora.com/v1/payments';
                $res=$this->bomborapay->payment_checkout('POST',$payUrl,$payment_data,$marchant_id,$marchant_id_type);
                if(@$res['approved']==1)
                {
                    $where = array('user_id' => $customer_id);
                    $result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
                    $ref_num  = getuniquenumber();
                    $payment_id =!empty(@$res['id']) ? $res['id'] : $ref_num;
                    $authorizing_merchant_id =!empty(@$res['authorizing_merchant_id']) ? $res['authorizing_merchant_id'] : '';
                    $payment_type =!empty(@$res['type']) ? $res['type'] : '';
                    $payment_method =!empty(@$res['payment_method']) ? $res['payment_method'] : '';
                    $amount =!empty(@$res['amount']) ? $res['amount'] : '';*/


                    $savecard      = $this->input->post('savecard');
					$card_id       = $this->input->post('card_id');
					$customer_name = $this->input->post('customer_name');
					$number        = $this->input->post('number');
					$expiry_month  = $this->input->post('expiry_month');
					$expiry_year   = $this->input->post('expiry_year');
					$cvd           = $this->input->post('cvd');
					$country_code  = $this->input->post('country_code');

					$res_data = clover_api_card_profile_check($usid,$savecard,$customer_name,$number,$expiry_month,$expiry_year,$cvd,$country_code,$business_id,$token);
					$customer_code= $res_data['customer_code'];
					$marchant_id  = $res_data['marchant_id'];
					$country_code = $res_data['country_code'];
					$clover_key   = $res_data['clover_key'];
					$access_token = $res_data['access_token'];
					$currency     = $res_data['currency'];


					$user_cc_no   = $number;
					$user_cc_mo   = $expiry_month;
					$user_cc_yr   = $expiry_year;
					$user_cc_cvv  = $cvd;
					$user_zip     = '';
					$amount       = $grand_total;
					$taxAmount    = 0;
					$res  = clover_api_payment_checkout($user_cc_no,$user_cc_mo,$user_cc_yr,$user_cc_cvv,$user_zip,$amount,$taxAmount,$marchant_id,$clover_key,$access_token,$currency,$token);

					//{ "id" : "R48Q7GPMY2FQY", "amount" : 1800, "amount_refunded" : 0, "currency" : "usd", "created" : 1616661109846, "captured" : true, "ref_num" : "108400500020", "auth_code" : "OK2809", "outcome" : { "network_status" : "approved_by_network", "type" : "authorized" }, "paid" : true, "status" : "succeeded", "source" : { "id" : "clv_1TSTS1iWemARMj4AXHxHikLV", "brand" : "AMEX", "cvc_check" : "unchecked", "exp_month" : "11", "exp_year" : "2025", "first6" : "378282", "last4" : "0005" } }

						//{"message":"400 Bad Request","error":{"code":"token_already_used","message":"You cannot use a clover token more than once unless it is marked as multipay."}}


						//{"paymentId":"30PFKD66YXNCP","result":"APPROVED","authCode":"OK7823","token":"DGY73XR6DDDF0","vaultedCard":{"first6":"411111","last4":"1111","expirationDate":"0321","token":"1894469479681111"}}

						//echo $res['message'];die;
						//echo $res['message'];die;
						if(@$res->status == 'succeeded')
						{
							$where = array('user_id' => $usid,
								'business_id' => $business_id,
							);
							$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);

							$ref_num    = getuniquenumber();
							$payment_id = !empty($res->id) ? $res->id : $ref_num;
							$authorizing_merchant_id = $res->source->id;
							$payment_type   = 'Card';
							$payment_method = 'Online';
							$amount         = $amount;


                    //Insert data in transaction table
                    $transaction_data = array(
                        'authorizing_merchant_id' => $authorizing_merchant_id,
                        'payment_type' => $payment_type,
                        'payment_method' => $payment_method,
                        'user_id'                =>$customer_id,
                        'amount'                 =>$amount,
                        'trx_id'                =>$payment_id,
                        'order_number'          =>$time,
                        'transaction_type'      => 4,
                        'payment_status'        =>"Success",
                        'saved_card_id'         =>0,
                        'create_dt'             =>$time,
                        'update_dt'             =>$time,
                        'responce_all'=>json_encode($res)
                            );
                    $transaction_id=$this->dynamic_model->insertdata('transactions',$transaction_data);
                        //after that insert into user booking table
                    $sub_total=$amount*$quantity;
                    $passData =   array(
                    'business_id'   =>$business_id,
                    'user_id'       =>$customer_id,
                    'create_by'       =>$usid,
                    'transaction_id'=>$transaction_id,
                    'amount'        =>$amount,
                    'service_type'  =>$service_type,
                    'service_id'    =>$workshop_id,
                    'class_id'      =>$workshop_schdule_id,
                    'quantity'      =>$quantity,
                    'sub_total'     =>$sub_total,
                    'status'        =>"Success",
                    'create_dt'     =>$time,
                    'update_dt'     =>$time,
                    'passes_start_date' => $start_time_unix,
                    'passes_end_date' => $end_time_unix,
                    );

                    $booking_id= $this->dynamic_model->insertdata('user_booking',$passData);

                    $response  = array('amount' =>number_format((float)$sub_total, 2, '.', ''),'transaction_date'=>date('d M Y'));
                    if($transaction_id)
                    {
                        $arg['status']    = 1;
                        $arg['error_code'] = HTTP_OK;
                        $arg['error_line']= __line__;
                        $arg['message']   =$this->lang->line('payment_succ');
                        $arg['transaction_id']   = $transaction_id;
                        $arg['data']      = $response;
                    }
                    else
                    {
                        $arg['status']    = 0;
                        $arg['error_code'] = HTTP_NOT_FOUND;
                        $arg['error_line']= __line__;
                        $arg['message']   = $this->lang->line('payment_fail');
                        $arg['data']      =json_decode('{}');
                    }
                    }else{
                        $arg['status']    = 0;
                        $arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
                        $arg['error_line']= __line__;
                        $arg['message']   = @$res->error->message;
                        $arg['data']      =json_decode('{}');
                    }
                    }
                }
            }
        }
      	echo json_encode($arg);
    }

	public function service_appointment_cancel_post()
    {
        $arg = array();
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
        }
        else
        {
            $userdata = checkuserid();
            if($userdata['status'] != 1){
                $arg = $userdata;
            }
            else
            {
                $_POST = json_decode(file_get_contents("php://input"), true);
                if($_POST)
                {
                    $this->form_validation->set_rules('transaction_id','Transaction Id', 'required|trim', array( 'required' => 'Transaction id is required'));
                    if($this->form_validation->run() == FALSE)
                    {
                        $arg['status']  = 0;
                        $arg['error_code'] = 0;
                        $arg['error_line']= __line__;
                        $arg['message'] = get_form_error($this->form_validation->error_array());
                    }
                    else
                    {
                        $user_id        =   $userdata['data']['id'];
                       // $business_id    =  $this->input->post('business_id');
                        $transaction_id =  $this->input->post('transaction_id');

                        $booking_status = $this->dynamic_model->updateRowWhere('user_booking', array('transaction_id' => $transaction_id), array('status' => 'Cancel'));

                        if($booking_status)
                        {
                            $arg['status']      = 1;
                            $arg['error_code']  = REST_Controller::HTTP_OK;
                            $arg['error_line']  = __line__;
                            $arg['message']     = 'Appointment cancel successfully';
                        }else{
                            $arg['status']     = 0;
                            $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
                            $arg['error_line']= __line__;
                            $arg['message']    = $this->lang->line('server_problem');
                        }

                    }
                }
            }
        }
        echo json_encode($arg);
    }

	public function send_appointment_mail_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
        {
            $arg = $version_result;
        }
        else
        {
			$userdata = checkuserid('1');
            if($userdata['status'] != 1){
                $arg = $userdata;
            }
            else
            {
				$userid      = $userdata['data']['id'];
                $_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
                {
					$this->form_validation->set_rules('transaction_id', 'Transaction Id', 'required|numeric',array(
                        'required' => 'Transaction Id is required',
                        'numeric' => 'Transaction Id is required',
                    ));
					if($this->form_validation->run() == FALSE)
                    {
                        $arg['status']  = 0;
                        $arg['error_code'] = 0;
                        $arg['error_line']= __line__;
                        $arg['message'] = get_form_error($this->form_validation->error_array());
                    } else {
                    	$time_zone =  $this->input->get_request_header('Timezone', true);
	                    $time_zone =  $time_zone ? $time_zone : 'UTC';
						date_default_timezone_set($time_zone);
                    	$transaction_id = $this->input->post('transaction_id');

						$data = helpAppointmentSendEmail($transaction_id);

						$msg = $this->load->view('appointment', $data, true);
						$to = $data['customer_email'];
                        $cc = $data['instructor'];
						$html = $msg;

						sendEmailCI($to, SITE_NAME , 'Appointment Billing Details', $msg, array(), '', $cc);

						$arg['status']     = 1;
						$arg['error_code']  = HTTP_OK;
						$arg['error_line']= __line__;
						// $arg['data'] = $data;
						// $arg['preview'] = $this->load->view('appointment', $data);;
						$arg['message']    = 'Email send successfully.';
					}
				}
			}
		}
		echo json_encode($arg);
	}

	/* 09-04-2021 */
	public function fetch_clover_payment_token($business_id, $country_code, $number, $expiry_month, $expiry_year, $cvd) {
		$clover_key = '';
		if($business_id!="")
		{
			$mid          = getUserMarchantId($business_id);
			$marchant_id  = $mid['marchant_id'];
			$country_code = $mid['marchant_id_type'];
			$clover_key   = $mid['clover_key'];
			$access_token = $mid['access_token'];


			if(empty($marchant_id))
			{
				$arg['status'] = 0;
				$arg['error_code'] = ERROR_FAILED_CODE;
				$arg['error_line'] = __line__;
				$arg['message'] = 'Merchant id is empty';
				$arg['data'] = json_decode('{}');
				echo json_encode($arg);
				die;
			}
			else if(empty($country_code))
			{
				$arg['status'] = 0;
				$arg['error_code'] = ERROR_FAILED_CODE;
				$arg['error_line'] = __line__;
				$arg['message'] = 'Merchant country code is empty';
				$arg['data'] = json_decode('{}');
				echo json_encode($arg);
				die;
			}
			else if(empty($clover_key))
			{
				$arg['status'] = 0;
				$arg['error_code'] = ERROR_FAILED_CODE;
				$arg['error_line'] = __line__;
				$arg['message'] = 'Merchant clover key is empty';
				$arg['data'] = json_decode('{}');
				echo json_encode($arg);
				die;
			}
			else if(empty($access_token))
			{
				$arg['status'] = 0;
				$arg['error_code'] = ERROR_FAILED_CODE;
				$arg['error_line'] = __line__;
				$arg['message'] = 'Merchant clover access token is empty';
				$arg['data'] = json_decode('{}');
				echo json_encode($arg);
				die;
			}
		}
		else
		{
			if($country_code==1)// For USA
			{
				$marchant_id  = MERCHANT_ID_USA;
				$country_code = $country_code;
				$clover_key   = CLOVER_KEY_USA;
				$access_token = ACCESS_TOKEN_USA;
			}
			else if($country_code==2)// For CAD
			{
				$marchant_id  = MERCHANT_ID_CAD;
				$country_code = $country_code;
				$clover_key   = CLOVER_KEY_CAD;
				$access_token = ACCESS_TOKEN_CAD;
			}
		}
		if (empty($clover_key)) {
			$arg['status'] = 0;
			$arg['error_code'] = ERROR_FAILED_CODE;
			$arg['error_line'] = __line__;
			$arg['message'] = 'Please enter valid data';
			$arg['data'] = json_decode('{}');
			echo json_encode($arg); exit;
		}
		$token = getCloverToken($number, $expiry_month, $expiry_year, $cvd,$clover_key);
		if ($token) {
			$res = array('token' => $token);
			$arg['status'] = 1;
			$arg['error_code'] = HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['message'] = 'Clover Payment token';
			$arg['data'] = $res;
		} else {
			$arg['status'] = 0;
			$arg['error_code'] = ERROR_FAILED_CODE;
			$arg['error_line'] = __line__;
			$arg['message'] = 'Invalid Details';
			$arg['data'] = json_decode('{}');
			echo json_encode($arg);
		}
		return json_encode($arg);
	}

	public function clover_buy_now_workshop_single_post()
	{
	    $arg    = array();
	    $version_result = version_check_helper1();
	    if($version_result['status'] != 1 )
	    {
	        $arg = $version_result;
	    }
	    else
	    {
        $userdata = checkuserid();
        if($userdata['status'] != 1){
         $arg = $userdata;
        }
        else
        {
        $_POST = json_decode(file_get_contents("php://input"), true);
        if($_POST)
        {
			// if ($this->input->post('expiry_month')) {
			// 	$resp = $this->fetch_clover_payment_token($this->input->post('business_id'), $this->input->post('country_code'), $this->input->post('number'), $this->input->post('expiry_month'), $this->input->post('expiry_year'), $this->input->post('cvd'));
			//
			// 	$resp = json_decode($resp);
			// 	if ($resp->status == 0) {
			// 		$arg['status'] = 0;
			// 		$arg['error_code'] = ERROR_FAILED_CODE;
			// 		$arg['error_line'] = __line__;
			// 		$arg['message'] = 'Invalid Details';
			// 		$arg['data'] = json_decode('{}');
			// 		echo json_encode($arg); exit;
			// 	}
			// }

            $this->form_validation->set_rules('business_id','Business_id Id', 'required|trim', array( 'required' => 'Business_id id is required'));
            $this->form_validation->set_rules('workshop_id','Workshop Id', 'required|trim', array( 'required' => 'Workshop id is required.'));
            $this->form_validation->set_rules('workshop_schdule_id','Workshop Schdule Id', 'required|trim', array( 'required' => 'Workshop Schdule id is required.'));
            $this->form_validation->set_rules('customer_id','Customer Id', 'required|trim', array( 'required' => $this->lang->line('customer_id_required')));
            //$this->form_validation->set_rules('quantity','Quantity', 'required', array( 'required' => $this->lang->line('quantity_required')));
            $this->form_validation->set_rules('grand_total','grand total','required|greater_than[0]',array(
                    'required' => $this->lang->line('amount_required'),
                    'numeric' => $this->lang->line('amount_valid')
                  ));
            //$this->form_validation->set_rules('slot_date','Slot Date', 'required', array( 'required' => $this->lang->line('date_required')));
            // $this->form_validation->set_rules('token','Token', 'required', array( 'required' => $this->lang->line('token_required')));
             if($this->form_validation->run() == FALSE)
            {
                $arg['status']  = 0;
                $arg['message'] = get_form_error($this->form_validation->error_array());
            }
            else
            {
                $business_id = $this->input->post('business_id');
                $workshop_id = $this->input->post('workshop_id');
                $customer_id = $this->input->post('customer_id');
                $workshop_schdule_id = $this->input->post('workshop_schdule_id');
                $where = array('id' => $workshop_id);
                $product_data = $this->dynamic_model->getdatafromtable('business_workshop_master',$where);

                $where = array('id' => $workshop_schdule_id);
                $schdule_data = $this->dynamic_model->getdatafromtable('business_workshop_schdule',$where);
                $start_time_unix = $schdule_data[0]['start'];
                $end_time_unix = $schdule_data[0]['end'];

                $Amt=0;
                $usid = $userdata['data']['id'];
                $name = $userdata['data']['name'];
                $lastname =$userdata['data']['lastname'];
                $time = time();
                $pass_start_date=$pass_end_date=$pass_status='';
                //service_type => 1 passes 2 services 3 product
                $service_type     = 4;
                $quantity         = 1;
				/*if ($this->input->post('token')) {
					$token = $this->input->post('token');
				} else {
					$dat = $resp->data;
					$token = $dat->token;
				}*/

                $token            = $this->input->post('token');
                $grand_total      = $this->input->post('grand_total');
                $grand_total           = number_format((float)$grand_total, 2, '.', '');

                //$savecard= $this->input->post('savecard');
                $passes_total_count     = 0;
                $passes_remaining_count  = 0;

                $pass_start_date = 0;
                $pass_end_date = 0;




                    $savecard      = $this->input->post('savecard');
					$card_id       = $this->input->post('card_id');
					$customer_name = $this->input->post('customer_name');
					$number        = $this->input->post('number');
					$expiry_month  = $this->input->post('expiry_month');
					$expiry_year   = $this->input->post('expiry_year');
					$cvd           = $this->input->post('cvd');
					$country_code  = $this->input->post('country_code');

					// $res_data = clover_api_card_profile_check($usid,$savecard,$customer_name,$number,$expiry_month,$expiry_year,$cvd,$country_code,$business_id,$token);
					// $customer_code= $res_data['customer_code'];
					// $marchant_id  = $res_data['marchant_id'];
					// $country_code = $res_data['country_code'];
					// $clover_key   = $res_data['clover_key'];
					// $access_token = $res_data['access_token'];
					// $currency     = $res_data['currency'];


					$user_cc_no   = $number;
					$user_cc_mo   = $expiry_month;
					$user_cc_yr   = $expiry_year;
					$user_cc_cvv  = $cvd;
					$user_zip     = '';
					$amount       = $grand_total;
					$taxAmount    = 0;
					

					if (check_expiry_year($expiry_year) == false) {
						$arg['status']  = 0;
						$arg['error_code'] =  ERROR_FAILED_CODE;
						$arg['error_line'] = __line__;
						$arg['message'] = $this->lang->line('invalid_expiry_year');
						echo json_encode($arg);
						exit;
					}

					// check year is valid
					if (check_expiry_month_year($expiry_month, $expiry_year) == false) {
						$arg['status']  = 0;
						$arg['error_code'] =  ERROR_FAILED_CODE;
						$arg['error_line'] = __line__;
						$arg['message'] = $this->lang->line('invalid_expiry_year_month');
						echo json_encode($arg);
						exit;
					}

					$getCustomerId = strhlp_create_customer(
						array(
							'token'	=> $token,
							'name'	=> $name . ' ' . $lastname,
							'email'	=> $userdata['data']['email'],
							'user_id' => $usid
						)
					);

					if (!$getCustomerId) {
						$arg['status'] = 0;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['message']   = "Invalid token";
						$arg['data']      = array();
					} else {
						if ($savecard == 1) {
							$cardStatus = strhlp_add_card(
								array(
									'token'			=>	$token,
									'customer_id'	=>	$getCustomerId
								)
							);

							if ($cardStatus == 'You cannot use a Stripe token more than once: ' . $token . '.') {

								$token = strhlp_get_token(
									array(
										'card_number'	=>	$number,
										'expiry_month'		=>	$expiry_month,
										'cvv_no'			=>	$cvd,
										'expiry_year'		=>	$expiry_year
									)
								);

								$cardStatus = strhlp_add_card(
									array(
										'token'			=>	$token,
										'customer_id'	=>	$getCustomerId
									)
								);
							}

							/****************************/
							$card_data = array('user_id' => $usid,
												'business_id' =>$business_id,
												'card_id' =>rand(99,99999999),
												'profile_id'=>$getCustomerId,
												//'customer_name'=>$customer_name,
												'card_no'=>$number,
												'expiry_year'=>$expiry_year,
												'expiry_month'=>$expiry_month,
												'card_token'=>$token,
												'card_type'=>''
											);
							$this->dynamic_model->insertdata('user_card_save', $card_data);
							/****************************/
						}

						$token = strhlp_get_token(
							array(
								'card_number'	=>	$number,
								'expiry_month'		=>	$expiry_month,
								'cvv_no'			=>	$cvd,
								'expiry_year'		=>	$expiry_year
							)
						);

						
					}

					$res = strhlp_checkout(
							array(
								'amount'	=>	$amount,
								'name'		=>	$name . ' ' . $lastname,
								'email'		=>	$userdata['data']['email'],
								'description' => $amount.' successfully paid!',
								'token'	=>	$token
							),
							array(
								'user_id'	=>	$usid
							),
							2
						);

					//Succeeded 
					//print_r($res);
					$payment_status = $res['response']->status;
					$transaction_id = $res['response']->balance_transaction;
					

					// 25/04/2021
					//$res  = clover_api_payment_checkout($user_cc_no,$user_cc_mo,$user_cc_yr,$user_cc_cvv,$user_zip,$amount,$taxAmount,$marchant_id,$clover_key,$access_token,$currency,$token);

					//{ "id" : "R48Q7GPMY2FQY", "amount" : 1800, "amount_refunded" : 0, "currency" : "usd", "created" : 1616661109846, "captured" : true, "ref_num" : "108400500020", "auth_code" : "OK2809", "outcome" : { "network_status" : "approved_by_network", "type" : "authorized" }, "paid" : true, "status" : "succeeded", "source" : { "id" : "clv_1TSTS1iWemARMj4AXHxHikLV", "brand" : "AMEX", "cvc_check" : "unchecked", "exp_month" : "11", "exp_year" : "2025", "first6" : "378282", "last4" : "0005" } }

					//{"message":"400 Bad Request","error":{"code":"token_already_used","message":"You cannot use a clover token more than once unless it is marked as multipay."}}


					//{"paymentId":"30PFKD66YXNCP","result":"APPROVED","authCode":"OK7823","token":"DGY73XR6DDDF0","vaultedCard":{"first6":"411111","last4":"1111","expirationDate":"0321","token":"1894469479681111"}}

					//echo $res['message'];die;
					// if(@$res->status == 'succeeded')
					if(strtolower($payment_status)=='succeeded')
					{
						$where = array('user_id' => $usid,
							'business_id' => $business_id,
						);
						$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);

						$ref_num    = getuniquenumber();
						$payment_id = $transaction_id ;//time(); // !empty($res->id) ? $res->id : $ref_num;
						$authorizing_merchant_id = time(); //$res->source->id;
						$payment_type   = 'Card';
						$payment_method = 'Online';
						$amount         = $amount;


                    //Insert data in transaction table
                    $transaction_data = array(
                        'authorizing_merchant_id' => $authorizing_merchant_id,
                        'payment_type' => $payment_type,
                        'payment_method' => $payment_method,
                        'user_id'                =>$customer_id,
                        'amount'                 =>$amount,
                        'trx_id'                =>$payment_id,
                        'order_number'          =>$time,
                        'transaction_type'      => 4,
                        'payment_status'        =>"Success",
                        'saved_card_id'         =>0,
                        'create_dt'             =>$time,
                        'update_dt'             =>$time,
                        'responce_all'=> '' // json_encode($res)
                            );
                    $transaction_id=$this->dynamic_model->insertdata('transactions',$transaction_data);
                        //after that insert into user booking table
                    $sub_total=$amount*$quantity;
                    $passData =   array(
                    'business_id'   =>$business_id,
                    'user_id'       =>$customer_id,
                    'create_by'       =>$usid,
                    'transaction_id'=>$transaction_id,
                    'amount'        =>$amount,
                    'service_type'  =>$service_type,
                    'service_id'    =>$workshop_id,
                    'class_id'      =>$workshop_schdule_id,
                    'quantity'      =>$quantity,
                    'sub_total'     =>$sub_total,
                    'status'        =>"Success",
                    'create_dt'     =>$time,
                    'update_dt'     =>$time,
                    'passes_start_date' => $start_time_unix,
                    'passes_end_date' => $end_time_unix,
                    );

                    $booking_id= $this->dynamic_model->insertdata('user_booking',$passData);

                    $response  = array('amount' =>number_format((float)$sub_total, 2, '.', ''),'transaction_date'=>date('d M Y'));
                    if($transaction_id)
                    {
                        $arg['status']    = 1;
                        $arg['error_code'] = HTTP_OK;
                        $arg['error_line']= __line__;
                        $arg['message']   =$this->lang->line('payment_succ');
                        $arg['transaction_id']   = $transaction_id;
                        $arg['data']      = $response;
                    }
                    else
                    {
                        $arg['status']    = 0;
                        $arg['error_code'] = HTTP_NOT_FOUND;
                        $arg['error_line']= __line__;
                        $arg['message']   = $this->lang->line('payment_fail');
                        $arg['data']      =json_decode('{}');
                    }
                    }else{
                        $arg['status']    = 0;
                        $arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
                        $arg['error_line']= __line__;
                        $arg['message']   = ''; //@$res->error->message;
                        $arg['data']      =json_decode('{}');
                    }
                    }
                }
            }
        }
      	echo json_encode($arg);
    }

	public function clover_buy_now_services_single_post()
	{
		$arg    = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = checkuserid();
			if($userdata['status'] != 1){
				$arg = $userdata;
			}
        	else
        	{
        		$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
				{
					// if ($this->input->post('expiry_month')) {
					// 	$resp = $this->fetch_clover_payment_token($this->input->post('business_id'), $this->input->post('country_code'), $this->input->post('number'), $this->input->post('expiry_month'), $this->input->post('expiry_year'), $this->input->post('cvd'));
					//
					// 	$resp = json_decode($resp);
					// 	if ($resp->status == 0) {
					// 		$arg['status'] = 0;
					// 		$arg['error_code'] = ERROR_FAILED_CODE;
					// 		$arg['error_line'] = __line__;
					// 		$arg['message'] = 'Invalid Details';
					// 		$arg['data'] = json_decode('{}');
					// 		echo json_encode($arg); exit;
					// 	}
					// }
					$this->form_validation->set_rules('service_id','Service Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('transaction_id','Transaction Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
					if($this->form_validation->run() == FALSE)
					{
						$arg['status']  = 0;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					}
					else
					{
						$tip_amount = $this->input->post('tip_amount');
						$service_id = $this->input->post('service_id');
						$transaction_id = $this->input->post('transaction_id');
						$where = array('id'=>$service_id,'status' => 'Active');
						$product_data = $this->dynamic_model->getdatafromtable('service',$where);
						$Amt=0;
						$usid =$userdata['data']['id'];
						$name =$userdata['data']['name'];
						$lastname =$userdata['data']['lastname'];
						$time = time();
						$pass_start_date=$pass_end_date=$pass_status='';
                		//service_type => 1 passes 2 services 3 product

						// $token = $this->input->post('token');
						if ($this->input->post('token')) {
							$token = $this->input->post('token');
						} else {
							$dat = $resp->data;
							$token = $dat->token;
						}

						$where = array('transaction_id'=>$transaction_id);
						$booking_data = $this->dynamic_model->getdatafromtable('user_booking',$where);
						if(empty($booking_data)){
						$arg['status']    = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['message']   = 'No record found';
							$arg['data']      =json_decode('{}');
							echo json_encode($arg); die;
						}

						$quantity = $booking_data[0]['quantity'];
						$grand_total = $booking_data[0]['amount'];
						$grand_total = number_format((float)$grand_total, 2, '.', '');
						//$savecard= $this->input->post('savecard');
						//$card_id = $this->input->post('card_id');
						$passes_total_count     = 0;
						$passes_remaining_count  = 0;

						$pass_start_date = 0;
						$pass_end_date = 0;
						$service = $this->db->get_where('service', array('id' => $service_id))->row_array();
						$business_id = $service['business_id'];



						$savecard      = $this->input->post('savecard');
						$card_id       = $this->input->post('card_id');
						$customer_name = $this->input->post('customer_name');
						$number        = $this->input->post('number');
						$expiry_month  = $this->input->post('expiry_month');
						$expiry_year   = $this->input->post('expiry_year');
						$cvd           = $this->input->post('cvd');
						$country_code  = $this->input->post('country_code');

						// $res_data = clover_api_card_profile_check($usid,$savecard,$customer_name,$number,$expiry_month,$expiry_year,$cvd,$country_code,$business_id,$token);
						// $customer_code= $res_data['customer_code'];
						// $marchant_id  = $res_data['marchant_id'];
						// $country_code = $res_data['country_code'];
						// $clover_key   = $res_data['clover_key'];
						// $access_token = $res_data['access_token'];
						// $currency     = $res_data['currency'];


						$user_cc_no   = $number;
						$user_cc_mo   = $expiry_month;
						$user_cc_yr   = $expiry_year;
						$user_cc_cvv  = $cvd;
						$user_zip     = '';
						$amount       = $grand_total;
						$taxAmount    = 0;
						

						if (check_expiry_year($expiry_year) == false) {
							$arg['status']  = 0;
							$arg['error_code'] =  ERROR_FAILED_CODE;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('invalid_expiry_year');
							echo json_encode($arg);
							exit;
						}

						// check year is valid
						if (check_expiry_month_year($expiry_month, $expiry_year) == false) {
							$arg['status']  = 0;
							$arg['error_code'] =  ERROR_FAILED_CODE;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('invalid_expiry_year_month');
							echo json_encode($arg);
							exit;
						}

						$getCustomerId = strhlp_create_customer(
							array(
								'token'	=> $token,
								'name'	=> $name . ' ' . $lastname,
								'email'	=> $userdata['data']['email'],
								'user_id' => $usid
							)
						);

						if (!$getCustomerId) {
							$arg['status'] = 0;
							$arg['error_code'] = HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['message']   = "Invalid token";
							$arg['data']      = array();
						} else {
							if ($savecard == 1) {
								$cardStatus = strhlp_add_card(
									array(
										'token'			=>	$token,
										'customer_id'	=>	$getCustomerId
									)
								);

								if ($cardStatus == 'You cannot use a Stripe token more than once: ' . $token . '.') {

									$token = strhlp_get_token(
										array(
											'card_number'	=>	$number,
											'expiry_month'		=>	$expiry_month,
											'cvv_no'			=>	$cvd,
											'expiry_year'		=>	$expiry_year
										)
									);

									$cardStatus = strhlp_add_card(
										array(
											'token'			=>	$token,
											'customer_id'	=>	$getCustomerId
										)
									);
								}

								/****************************/
								$card_data = array('user_id' => $usid,
													'business_id' =>$business_id,
													'card_id' =>rand(99,99999999),
													'profile_id'=>$getCustomerId,
													//'customer_name'=>$customer_name,
													'card_no'=>$number,
													'expiry_year'=>$expiry_year,
													'expiry_month'=>$expiry_month,
													'card_token'=>$token,
													'card_type'=>''
												);
								$this->dynamic_model->insertdata('user_card_save', $card_data);
								/****************************/
							}

							$token = strhlp_get_token(
								array(
									'card_number'	=>	$number,
									'expiry_month'		=>	$expiry_month,
									'cvv_no'			=>	$cvd,
									'expiry_year'		=>	$expiry_year
								)
							);

							
						}

						$res = strhlp_checkout(
								array(
									'amount'	=>	$amount,
									'name'		=>	$name . ' ' . $lastname,
									'email'		=>	$userdata['data']['email'],
									'description' => $amount.' successfully paid!',
									'token'	=>	$token
								),
								array(
									'user_id'	=>	$usid
								),
								2
							);

						//Succeeded 
						//print_r($res);
						$payment_status = $res['response']->status;
						$transaction_id = $res['response']->balance_transaction;
						

						// 25/04/2021
						//$res  = clover_api_payment_checkout($user_cc_no,$user_cc_mo,$user_cc_yr,$user_cc_cvv,$user_zip,$amount,$taxAmount,$marchant_id,$clover_key,$access_token,$currency,$token);

						//{ "id" : "R48Q7GPMY2FQY", "amount" : 1800, "amount_refunded" : 0, "currency" : "usd", "created" : 1616661109846, "captured" : true, "ref_num" : "108400500020", "auth_code" : "OK2809", "outcome" : { "network_status" : "approved_by_network", "type" : "authorized" }, "paid" : true, "status" : "succeeded", "source" : { "id" : "clv_1TSTS1iWemARMj4AXHxHikLV", "brand" : "AMEX", "cvc_check" : "unchecked", "exp_month" : "11", "exp_year" : "2025", "first6" : "378282", "last4" : "0005" } }

						//{"message":"400 Bad Request","error":{"code":"token_already_used","message":"You cannot use a clover token more than once unless it is marked as multipay."}}


						//{"paymentId":"30PFKD66YXNCP","result":"APPROVED","authCode":"OK7823","token":"DGY73XR6DDDF0","vaultedCard":{"first6":"411111","last4":"1111","expirationDate":"0321","token":"1894469479681111"}}

						//echo $res['message'];die;
						// if(@$res->status == 'succeeded')
						if(strtolower($payment_status)=='succeeded')
						{
							$where = array('user_id' => $usid,
								'business_id' => $business_id,
							);
							$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);

							$ref_num    = getuniquenumber();
							$payment_id = $transaction_id ;//time(); // !empty($res->id) ? $res->id : $ref_num;
							$authorizing_merchant_id = time(); //$res->source->id;
							$payment_type   = 'Card';
							$payment_method = 'Online';
							$amount         = $amount;

                    		//Insert data in transaction table
                    		$transaction_data = array(
								'authorizing_merchant_id' => $authorizing_merchant_id,
								'payment_type' => $payment_type,
								'payment_method' => $payment_method,
								'amount'                 =>$amount,
								'trx_id'                =>$payment_id,
								'order_number'          =>$time,
								'transaction_type'      =>3,
								'payment_status'        =>"Success",
								'transaction_date' => date('Y-m-d'),
								'create_dt'             =>$time,
								'update_dt'             =>$time,
								'responce_all'=> '' // json_encode($res)
                            );

							$where1 = array('id' => $transaction_id);

							$this->dynamic_model->updateRowWhere('transactions', $where1, $transaction_data);


							//after that insert into user booking table

							$sub_total=$amount*$quantity;

							$passData =   array(

								'amount'        =>$amount,

								'sub_total'     =>$sub_total,

								'status'        =>"Success",

								'create_dt'     =>$time,

								'update_dt'     =>$time,
                    		);

							if ($this->input->post('tip_comment')) {
								$passData['tip_comment'] = $this->input->post('tip_comment');
							}

							$where1 = array('transaction_id' => $transaction_id);
							$this->dynamic_model->updateRowWhere('user_booking', $where1, $passData);


                    		$response  = array('amount' =>number_format((float)$sub_total, 2, '.', ''),'transaction_date'=>date('d M Y'));
							if($transaction_id)
							{
								$arg['status']    = 1;
								$arg['error_code']= HTTP_OK;
								$arg['error_line']= __line__;
								$arg['message']   = $this->lang->line('payment_succ');
								$arg['transaction_id']   = $transaction_id;
								$arg['data']      = $response;
							}
							else
							{
								$arg['status']    = 0;
								$arg['error_code'] = HTTP_NOT_FOUND;
								$arg['error_line']= __line__;
								$arg['message']   = $this->lang->line('payment_fail');
								$arg['data']      =json_decode('{}');
							}
						} else {
							$arg['status']    = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['message']   = ''; // @$res->error->message;
							$arg['data']      =json_decode('{}');
						}
                    }
                }
            }
        }
      echo json_encode($arg);
	}




}
