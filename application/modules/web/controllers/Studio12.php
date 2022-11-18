<?php defined('BASEPATH') OR exit('No direct script access allowed');
//require APPPATH . '/libraries/REST_Controller.php';
include_once APPPATH.'third_party/phpseclib/Crypt/RSA.php';
ini_set('max_execution_time', 0);
/* * ***************Studio.php**********************************
 * @product name    : Signal Health Group Inc.
 * @type            : Class
 * @class name      : Api
 * @description     : Class for all the methods , public methods calling from mobile apps.
 * @author          : Consagous Team
 * @url             : https://www.consagous.com/
 * @support         : aamir.shaikh@consagous.com
 * @copyright       : Consagous Team
 * ********************************************************** */
class Studio extends MX_Controller {

	public function __construct() {
		header('Content-Type: application/json');
		// header('Access-Control-Allow-Origin: *');
		// header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, version, language");
        parent::__construct();
		$this->load->library('form_validation');
		//$this->load->library('session');
		$this->load->model('dynamic_model');
		$this->load->model('studio_model');
		$this->load->helper(array(
			'web_common_helper',
			'notification_helper'
		));
		$this->load->library('Bomborapay');
	    $timezone    = $this->input->get_request_header('timeZone', true);
	    $isValidTimezone= isValidTimezoneId($timezone);
		if($isValidTimezone == true){
		 date_default_timezone_set($timezone);
	   }else{
	   	date_default_timezone_set('Asia/Calcutta');
	   }
		$language = $this->input->get_request_header('language');
		if($language == "en")
		{
			$this->lang->load("web_message","english");
		}
		else
		{
			$this->lang->load("web_message","english");
		}

	}

	private function setTimeZone () {
		$time_zone =  $this->input->get_request_header('current_time_zone', true);
		$time_zone =  $time_zone ? $time_zone : 'UTC';
		date_default_timezone_set($time_zone);
		return true;
	}

   public function test(){
   	$data = array('phone' => '919981462821','message' => 'testing sms');
	send_sms($data);
   }
	// App Version Check
	public function version_check()
	{
		$arg = array();
		$version_result = version_check_helper1();
		echo json_encode($version_result);
	}
	 //Used function to get countries details
	public function get_countries()
	{
		$arg    = array();
		$countryData = $this->studio_model->get_country();
		if(!empty($countryData))
		{
		 	foreach($countryData as $key => $value){
                $data[] = array
                    (
                    'id' => $value['id'],
                    'name' => ucwords($value['name']),
                    'code' => ucwords($value['code'])
                  );
                }
		 	$arg['status']     = 1;
			$arg['error_code']  = REST_Controller::HTTP_OK;
			$arg['error_line']= __line__;
			$arg['data']       = $data;
			$arg['message']    = $this->lang->line('country_list');
		}
		else
		{
			//$arg['error']   = ERROR_FAILED_CODE;
			$arg['status']     = 0;
			$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
        	$arg['error_line']= __line__;
        	$arg['message']    = $this->lang->line('record_not_found');
        	$arg['data']       = array();
		}

		echo json_encode($arg);
	}

     //Used function to get countries details
	public function get_weekdays()
	{
		$arg    = array();
		$response = $this->dynamic_model->getdatafromtable('manage_week_days');
		if(!empty($response))
		{
		 	$arg['status']     = 1;
			$arg['error_code']  = REST_Controller::HTTP_OK;
			$arg['error_line']= __line__;
			$arg['data']       = $response;
			$arg['message']    = $this->lang->line('record_found');
		}
		else
		{
			$arg['status']     = 0;
			$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
        	$arg['error_line']= __line__;
        	$arg['message']    = $this->lang->line('record_not_found');
        	$arg['data']       = array();
		}

		echo json_encode($arg);
	}
	/****************Function register**********************************
     * @type            : Function
     * @Author          : Aamir
     * @function name   : register
     * @description     : Registeration for new user,
     					  send email verificication link.
     * @param           : null
     * @return          : null
     * ********************************************************** */

	public function register()
    {
        $arg   = array();
        $_POST = json_decode(file_get_contents("php://input"), true);
        if($_POST)
        {
         $this->form_validation->set_rules('email', 'Email', 'required|valid_email' , array('required' => $this->lang->line('email_required'),'valid_email' => $this->lang->line('email_valid')
         ));
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|max_length[20]|regex', array(
            'required' => $this->lang->line('password_required'),
            'min_length' => $this->lang->line('password_minlength'),
            'max_length' => $this->lang->line('password_maxlenght'),
            'regex' => $this->lang->line('reg_check')
        ));
        if ($this->form_validation->run() == FALSE)
        {
            $arg['status']  = 0;
            $arg['error_code'] = 0;
            $arg['error_line']= __line__;
            $arg['message'] = get_form_error($this->form_validation->error_array());
        }
        else
        {
            $role=2;
            $time=time();
            $email = $this->input->post('email');
            $role_check=$this->dynamic_model->check_user_role($email,$role);
            if($role_check){
                $arg['status']    = 0;
                $arg['error_code'] = HTTP_OK;
                $arg['error_line']= __line__;
                $arg['message']   = $this->lang->line('already_register');
                $arg['data']      = array();
            }else{
            $hashed_password = encrypt_password($this->input->post('password'));
            $uniquemail   = getuniquenumber();
            $uniquemobile   = rand(0001,9999);
            $image = 'userdefault.png';
            
            $default_img = $email ? $email :'u';
        	$default_img = strtolower(substr($default_img,0,1));
         	$image = $default_img.'.png';

            $current_date = date('Y-m-d');
            $subscription_period = date('Y-m-d',strtotime($current_date. ' + 14 days'));

            $notification = '{"app_notification":"1","alerts":"1","email":"1","sms":"1","phonecall":"1"}';

            $userdata = array('notification'=>$notification,'email'=>$email,'password'=>$hashed_password,'profile_img'=>$image,'email_verified'=>'0','mobile_verified'=>'1','mobile_otp'=>$uniquemobile,'mobile_otp_date'=>$time,'subscription_period'=>strtotime($subscription_period),'subscription_startdate'=>strtotime(date('Y-m-d')),'create_dt'=>$time,'update_dt'=>$time,'status' => 'Active');
                $newuserid = $this->dynamic_model->insertdata('user',$userdata);
                if($newuserid){
                 $roledata = array(
                    'user_id'=>$newuserid,
                    'role_id'=>$role,
                    'create_dt'=>$time,
                    'update_dt'=>$time
                );
                $roleid = $this->dynamic_model->insertdata('user_role',$roledata);

                $roledata = array(
                    'user_id'=>$newuserid,
                    'role_id'=>'4',
                    'create_dt'=>$time,
                    'update_dt'=>$time
                );
                $this->dynamic_model->insertdata('user_role',$roledata);


                    $where = array('id' => $newuserid);
                    $findresult = $this->dynamic_model->getdatafromtable('user', $where);
                    $name= $findresult[0]['email'];

                    //Send Email Code
                    $enc_user = encode($newuserid);
                    $enc_role = encode($time);
                    $url = site_url().'web/studio/verify_user?encid='.$enc_user.'&batch='.$enc_role;
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
                    //$this->sendmail->sendmailto($email,$emailsubject,"$msg");
                    sendEmailCI("$email", SITE_TITLE ,$emailsubject, $msg);
                    //Send Email Code
                    //send otp thirdparty
                    //code
                    $data_val  = get_user_details($newuserid,$role);
                    $arg['status']    = 1;
                    $arg['error_code'] = HTTP_OK;
                    $arg['error_line']= __line__;
                    $arg['message']   = 'Please go to your inbox and look for an email from Signal Health Group Inc. Click on the Verify Your Email.'; //$this->lang->line('thank_msg1');
                    $arg['data']      = $data_val;
                    }else{
                    $arg['status']    = 0;
                    $arg['error_code'] = HTTP_OK;
                    $arg['error_line']= __line__;
                    $arg['message']   = $this->lang->line('server_problem');
                    $arg['data']      = array();
                   }
                }
            }
            echo json_encode($arg);
        }
    }
	public function register_old()
	{
		$arg   = array();
		$_POST = json_decode(file_get_contents("php://input"), true);
		if($_POST)
		{
			$version_result = version_check_helper1();
			if($version_result['status'] != 1 )
			{
				$arg = $version_result;
			}
			else
			{
				$this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[user.email]' , array('required' => $this->lang->line('email_required'),'valid_email' => $this->lang->line('email_valid'),'is_unique' => $this->lang->line('email_unique')
				));
				$this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|max_length[20]|regex', array(
					'required' => $this->lang->line('password_required'),
					'min_length' => $this->lang->line('password_minlength'),
					'max_length' => $this->lang->line('password_maxlenght'),
					'regex' => $this->lang->line('reg_check')
				));

				if ($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$email           = $this->input->post('email');
					$hashed_password = encrypt_password($this->input->post('password'));

					$where = array('email' => $email);
					$result = $this->dynamic_model->getdatafromtable('user',$where);
					if(!empty($result))
					{

					$arg['status']    = 0;
					$arg['error_code'] = HTTP_OK;
				 	$arg['error_line']= __line__;
				 	$arg['message']   = $this->lang->line('already_register');
				 	$arg['data']      = array();

				    }
				    else
				    {

					$time=time();
					$uniquemail   = getuniquenumber();
					$uniquemobile   = rand(0001,9999);
					$image = 'userdefault.png';
					$userdata = array('email'=>$email,'password'=>$hashed_password,'profile_img'=>$image,'email_verified'=>'0','mobile_verified'=>'0','mobile_otp'=>$uniquemobile,'mobile_otp_date'=>$time,'create_dt'=>$time,'update_dt'=>$time,'role_id'=>2);
						$newuserid = $this->dynamic_model->insertdata('user',$userdata);
						if($newuserid)
				        {
							$where = array('id' => $newuserid);
							$findresult = $this->dynamic_model->getdatafromtable('user', $where);
							$name= $findresult[0]['email'];

							//Send Email Code
							$enc_user = encode($newuserid);
							$enc_role = encode($time);
							$url = site_url().'web/studio/verify_user?encid='.$enc_user.'&batch='.$enc_role;
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
							//$this->sendmail->sendmailto($email,$emailsubject,"$msg");
							sendEmailCI("$email", SITE_TITLE ,$emailsubject, $msg);
							//Send Email Code

							//send otp thirdparty
							//code

                            $data_val  = get_user_details($newuserid);

							$arg['status']    = 1;
							$arg['error_code'] = HTTP_OK;
							$arg['error_line']= __line__;
						 	$arg['message']   = $this->lang->line('thank_msg1');
						 	$arg['data']      = $data_val;
				        }

				    }
				}
			}
			echo json_encode($arg);
		}
	}
    /****************Function verify**********************************
     * @type            : Function
     * @Author          : Aamir
     * @function name   : verify_user
     * @description     : Verify email.
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function verify_user()
	{
		$enc = $_GET['encid'];
		$role = decode($_GET['batch']);
		$userid = decode($enc);
		$where = array('id' => $userid);
		$findresult = $this->dynamic_model->getdatafromtable('user', $where);
		if($findresult)
		{
			$email_verified = $findresult[0]['email_verified'];
			$create_dt = $findresult[0]['create_dt'];
			header("Content-Type: text/html");
			if($role == $create_dt){
			if($email_verified == 1){
			$data['email_verified']='Already verified';
			$data['email_status']='1';
			$this->load->view('content/verify',$data);
			} else {
			$where1 = array('email' => $findresult[0]['email']);
			$data = array('email_verified' => "1");
			$varify = $this->dynamic_model->updateRowWhere('user', $where1, $data);

			$data['email_verified']='Verify successfully';
			$data['email_status']='1';
			$this->load->view('content/verify',$data);
			}
			} else {
			$data['email_verified']='Not Verify Please Try again Later';
			$data['email_status']='';
			$this->load->view('content/verify',$data);
			}
		}
		else
		{
			$data['email_verified']='Not Verify Please Try again Later';
			$data['email_status']='';
			$this->load->view('content/verify',$data);
		}
	}

	 /****************Function login**********************************
     * @type            : Function
     * @Author          : Aamir
     * @function name   : login
     * @description     : login for user,
     					  check all verification.
     * @param           : null
     * @return          : null
     * ********************************************************** */

	public function login()
	{
		$arg = array();
		$_POST = json_decode(file_get_contents("php://input"), true);
		if($_POST)
		{
			$version_result = version_check_helper1();
			if($version_result['status'] != 1 )
			{
				$arg = $version_result;
			}
			else
			{
				$this->form_validation->set_rules('email', 'Email', 'required',array('required' => $this->lang->line('email_required')
				));
				$this->form_validation->set_rules('password', '', 'required|min_length[8]|max_length[20]|regex', array(
						'required' => $this->lang->line('password_required'),
						'min_length' => $this->lang->line('password_minlength'),
						'max_length' => $this->lang->line('password_maxlenght'),
						'regex' => $this->lang->line('reg_check')
					));
				if ($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$role=2;
					$email= $this->input->post('email');
					$where = array('email' => $email);
					$data = $this->dynamic_model->check_user_role($email,$role);
					//$data = $this->dynamic_model->getdatafromtable('user',$where);
					if(!empty($data))
					{
						$hashed_password = encrypt_password($this->input->post('password'));
                        $userid = $data[0]['id'];
						$userdata = get_user_details($userid,$role);
						if($hashed_password == $userdata['password'])
						{
							$emailid  = $userdata['email'];
						    if($userdata){
						    if($userdata['email_verified'] == 1 && $userdata['business_status']=='Active' && $userdata['payment_status']=='Active'){

	                           if ($userdata['status'] != 'Active'){
								$arg['status']    = 0;
								$arg['message']   = $this->lang->line('user_deactive');
								$arg['error_code'] = HTTP_OK;
								$arg['error_line']= __line__;
								$arg['data']      = array();
								 echo json_encode($arg);exit();
								}
								$current_status = $userdata['first_login'];
                            $updatedata = array(
										'first_login' => "1"
									);
                            $where1 = array(
											'id' => $userid
									     );
							$this->dynamic_model->updateRowWhere('user',$where1,$updatedata);
							$userdata = get_user_details($userid,$role);
							if ($current_status == $userdata['first_login']) {
								$userdata['first_login'] = 0;
							}
							$arg['status']     = 1;
							$arg['error_code']  = HTTP_OK;
							$arg['error_line']= __line__;
							$arg['message']    = $this->lang->line('login_success');
							$arg['data']     = $userdata;
					        }else{
					        	 $msg='';
					        	 //echo "hi";die;
					        	 if($userdata['redirect_to_verify'] == 1){
					        	 	$msg=$this->lang->line('email_not_varify');
					             }elseif($userdata['redirect_to_verify'] == 2){
					             	$msg=$this->lang->line('plan_not_purchased');
					             }elseif($userdata['redirect_to_verify'] == 3){
					             	$msg=$this->lang->line('business_not_registred');
					             }elseif($userdata['redirect_to_verify'] == 4){
					             	$msg=$this->lang->line('business_not_activated');
					             }
                            $arg['status']     = 1;
							$arg['error_code']  = HTTP_OK;
							$arg['error_line']= __line__;
							$arg['message']    = $msg;
							$arg['data']       = $userdata;
					        }
							}else{
							$arg['status']    = 0;
			  				$arg['message']   = $this->lang->line('invalid_detail');
			  				$arg['error_code'] = HTTP_OK;
			  				$arg['error_line']= __line__;
			  				$arg['data']      = array();

						    }
						}
						else
						{
							$arg['status']    = 0;
			  				$arg['message']   = $this->lang->line('password_notmatch');
			  				$arg['error_code'] = HTTP_OK;
			  				$arg['error_line']= __line__;
			  				$arg['data']      = array();
						}
					}
					else
					{
						$arg['status']    = 0;
						$arg['message']   = $this->lang->line('register_first');
						$arg['error_code'] = HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']      = array();
					}
				}
			}
			echo json_encode($arg);
		}
	}

	/****************Function logout**********************************
     * @type            : Function
     * @Author          : Aamir
     * @function name   : logout
     * @description     : Clear all session of user.
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function logout()
	{
		$arg = array();
		$arg['status']     = 1;
		$arg['error_code']  = HTTP_OK;
		$arg['error_line']= __line__;
		$arg['message']    = check_authorization('logout');
		$arg['data']       = array();
		echo json_encode($arg);
	}

	/****************Function changepassword**********************************
     * @type            : Function
     * @function name   : changepassword
     * @description     : check the old password and replace it with new one.
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function changepassword()
	{
		$arg    = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 ){
		$arg = $version_result;
		}else{
				$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST){
					$this->form_validation->set_rules('old_password', 'Old Password', 'trim|required|regex', array(
						'required' => $this->lang->line('old_password')
					));
					$this->form_validation->set_rules('new_password', 'New Password', 'trim|required|min_length[8]|max_length[20]', array(
						'required' => $this->lang->line('new_password'),
						'min_length' => $this->lang->line('password_minlength'),
						'max_length' => $this->lang->line('password_maxlenght'),
						'regex' => $this->lang->line('reg_check')
					));
					if ($this->form_validation->run() == FALSE){
						$arg['status']  = 0;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					}else{

						$userdata = web_checkuserid();
						if($userdata['status'] == 0){
						$arg['status']    = 0;
				        $arg['error_code'] = HTTP_NOT_MODIFIED;
						$arg['error_line']= __line__;
						$arg['message']   = $userdata['message'];
						$arg['data']      = array();
						echo json_encode($arg);
						exit();
						}
						$userid = decode($userdata['data']['id']);
						if($userdata['status'] == 1){

						$hashed_password = encrypt_password($this->input->post('old_password'));
						if($hashed_password == $userdata['data']['password'])
						{
							$data1 = array('password' => encrypt_password($this->input->post('new_password')));
							$where  = array("id" => $userid);
			                $keyUpdate = $this->dynamic_model->updateRowWhere("user", $where, $data1);
			                if($keyUpdate) {
			                	$arg['status']    = 1;
			                	$arg['error_code'] = HTTP_OK;
								$arg['error_line']= __line__;
								$arg['message']   = $this->lang->line('password_change_success');
								$arg['data']      = $userdata['data'];
			                }
			                else
			                {
			                	$arg['status']    = 0;
				                $arg['error_code'] = HTTP_NOT_MODIFIED;
								$arg['error_line']= __line__;
								$arg['message']   = $this->lang->line('password_not_update');
								$arg['data']      = array();
			                }
						}
						else
						{
							$arg['status']    = 0;
			                $arg['error_code'] = HTTP_NOT_MODIFIED;
							$arg['error_line']= __line__;
							$arg['message']   = $this->lang->line('old_password_not');
							$arg['data']      = array();
						}
					} else {
					$arg = $data_val;
					}
					}
				}

		}
		echo json_encode($arg);
	}
	/****************Function check_verify_email**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : check_verify_email
     * @description     : check verify email
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function check_verify_email()
	{
		$arg = array();
		$_POST = json_decode(file_get_contents("php://input"),true);
		if($_POST){
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email' , array('required' => $this->lang->line('email_required'),'valid_email' => $this->lang->line('email_valid')
             ));
            if ($this->form_validation->run() == FALSE)
            {
                $arg['status']  = 0;
                $arg['error_code'] = 0;
                $arg['error_line']= __line__;
                $arg['message'] = get_form_error($this->form_validation->error_array());
            }
            else
            {
				$role=2;
				$email = $this->input->post('email');
		        $condition=array('user.email'=>$email,'user_role.role_id'=>$role);
		        $on='user_role.user_id = user.id';
		        $datauser = $this->dynamic_model->getTwoTableData('user.*,user_role.role_id','user','user_role',$on,$condition);
				if(!empty($datauser)){
				    if($datauser[0]['email_verified']=='0'){
				    $is_verified='0';
				    $msg=$this->lang->line('email_not_verify');
				    }elseif($datauser[0]['status']=='Deactive'){
				     $is_verified='0';
				     $msg=$this->lang->line('user_deactive');
				    }else{
				    $is_verified='1';
				    $msg=$this->lang->line('record_found');
				    }
					$arg['status']     = 1;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']['is_verified']=$is_verified;
					$arg['message']    = $msg;
				}else {
					$arg['status']     = 0;
					$arg['error_code']  =HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = array();
				 	$arg['message']    = $this->lang->line('record_not_found');
				}
	        }
	  }

	 echo json_encode($arg);
	}

	/****************Function getprofile**********************************
     * @type            : Function
     * @Author          : Aamir
     * @function name   : getprofile
     * @description     : Get all details of user.
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function get_profile()
	{
		$arg = array();
		$userdata = web_checkuserid();
		if($userdata['status'] == 1){

			$arg['status']     = 1;
			$arg['error_code']  = HTTP_OK;
			$arg['error_line']= __line__;
			$arg['data']       = $userdata['data'];
			$arg['message']    = $this->lang->line('profile_details');
		} else {
			$arg = $userdata;
		}

		echo json_encode($arg);
	}
	/****************Function updateprofile***********************
     * @type            : Function
     * @Author          : Aamir
     * @function name   : profile_update
     * @description     : User can change there profile details.
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function profile_update()
	{
		$arg    = array();
		$userdata = web_checkuserid();
		if($userdata['status'] != 1){
			$arg = $userdata;
		}else{

			if(empty($this->input->post())){
	  		$arg['status']    = 0;
			$arg['error_code'] = HTTP_NOT_MODIFIED;
	  		$arg['error_line']= __line__;
	  		$arg['message']   = $this->lang->line('profile_notupdate');
	  		$arg['data']      = array();
			}else{
				$userid = decode($userdata['data']['id']);
				$role_id = $userdata['data']['role_id'];
				$userdata =$instructordata=array();

				if(!empty($this->input->post('skill'))){
				$instructordata['skill'] = $this->input->post('skills');
				}
				if(!empty($this->input->post('experience'))){
				$instructordata['total_experience'] = $this->input->post('experience');
				}
				if(!empty($this->input->post('appointment_fees'))){
				$instructordata['appointment_fees'] = $this->input->post('appointment_fees');
				}
				if(!empty($this->input->post('appointment_fees_type'))){
				$instructordata['appointment_fees_type'] = $this->input->post('appointment_fees_type');
				}
				if(!empty($this->input->post('start_date'))){
				$instructordata['start_date'] = $this->input->post('start_date');
				}
				if(!empty($this->input->post('employee_id'))){
				$instructordata['employee_id'] = $this->input->post('employee_id');
				}
				if(!empty($this->input->post('employee_contractor'))){
				$instructordata['employee_contractor'] = $this->input->post('employee_contractor');
				}

				if(!empty($this->input->post('about'))){
				$instructordata['about'] = $this->input->post('about');
				}

				$instructordata['update_dt']      = time();
				$where = array('user_id' => $userid);
				$this->dynamic_model->updateRowWhere("instructor_details",$where,$instructordata);


				if(!empty($this->input->post('name'))){
				$userdata['name']      = $this->input->post('name');
				}

				if(!empty($this->input->post('lastname'))){
				$userdata['lastname']      = $this->input->post('lastname');
				}

				if(!empty($this->input->post('address'))){
				$userdata['address']      = $this->input->post('address');
				}

				if(!empty($this->input->post('city'))){
				$userdata['city']      = $this->input->post('city');
				}
				if(!empty($this->input->post('state'))){
				$userdata['state']      = $this->input->post('state');
				}
				if(!empty($this->input->post('country'))){
				$userdata['country']      = $this->input->post('country');
				}
				if(!empty($this->input->post('country_code'))){
				$userdata['country_code']      = $this->input->post('country_code');
				}
				if(!empty($this->input->post('lang'))){
				$userdata['lat']      = $this->input->post('lat');
				}
				if(!empty($this->input->post('lang'))){
				$userdata['lang']      = $this->input->post('lang');
				}
				if(!empty($this->input->post('zipcode'))){
				$userdata['zipcode']      = $this->input->post('zipcode');
				}
				if(!empty($this->input->post('gender'))){
				$userdata['gender']      = $this->input->post('gender');
				}
				if(!empty($this->input->post('mobile'))){
				$userdata['mobile']      = $this->input->post('mobile');
				}
				if(!empty($this->input->post('street'))){
				$userdata['location']      = $this->input->post('street');
				}

				if(!empty($this->input->post('dob'))){
				$userdata['date_of_birth'] = date('Y-m-d',$this->input->post('dob'));
				}

				if(!empty($this->input->post('status'))){
				//$userdata['status'] = $this->input->post('status');
				}
				if(!empty($_FILES['image']['name'])){
				$profile_image = $this->dynamic_model->fileupload('image', 'uploads/user');
				$userdata['profile_img'] = $profile_image;
				}
				$userdata['update_dt']      = time();
				$where = array('id' => $userid);
				$updatedata = $this->dynamic_model->updateRowWhere("user",$where,$userdata);

				if($updatedata)
				{
					$userdata1 = get_user_details($userid,$role_id);
					$arg['status']    = 1;
					$arg['error_code'] = HTTP_OK;
			  		$arg['error_line']= __line__;
			  		$arg['message']   = $this->lang->line('profile_update');
			  		$arg['data']      = $userdata1;
			  	}else{
			  		$arg['status']    = 0;
					$arg['error_code'] = HTTP_NOT_MODIFIED;
			  		$arg['error_line']= __line__;
			  		$arg['message']   = $this->lang->line('profile_notupdate');
			  		$arg['data']      = json_decode('{}');
			  	}
			}
		}
		echo json_encode($arg);
	}
    /****************Function forgotpassword***********************
     * @type            : Function
     * @Author          : Aamir
     * @function name   : forgot_password
     * @description     : User will get temporary password to set new password.
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function forgot_password()
	{
		$arg   = array();
		$_POST = json_decode(file_get_contents("php://input"), true);
		if($_POST)
		{
			$version_result = version_check_helper1();
			if($version_result['status'] != 1 )
			{
				$arg = $version_result;
			}
			else
			{
				 $this->form_validation->set_rules('email', 'Email', 'required|valid_email' , array('required' => $this->lang->line('email_required'),'valid_email' => $this->lang->line('email_valid')
					));

				if ($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code']   = ERROR_FAILED_CODE;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
				     $roleId=$this->input->post('role');
				     $time=time();
				     $email=$this->input->post('email');
				     $condition = array('email' =>$email);
				     $forcheck = $this->dynamic_model->getdatafromtable('user',$condition);
					if($forcheck)
					{
						$roleExist = $this->dynamic_model->check_user_role($email,$roleId);
						if(empty($roleExist)){
					        $arg['status']     = 0;
							$arg['error_code']  = 0;
							$arg['error_line']= __line__;
							$arg['message']    = $this->lang->line('forgot_msg_role_error');
							$arg['data']     =  json_decode('{}');

					    }else{
					    $role_id=$roleExist[0]['role_id'];
						$email=$roleExist[0]['email'];
						$userid=$roleExist[0]['id'];
						$full_name=ucwords($roleExist[0]['name'].' '.$roleExist[0]['lastname']);
						$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890123456789!@#$&*!@#$&*!@#$&*";

    					$otpnumber = substr(str_shuffle( $chars ),0, 14 );
    					//$otpnumber = "Qquerty@123";
						//$val = getuniquenumber();
						//$otpnumber   = substr($val, 0, 6);
						$where1 = array('slug' => 'forget_password');
						$template_data = $this->dynamic_model->getdatafromtable('manage_notification_mail', $where1);
                        $desc= str_replace('{USERNAME}',$full_name,$template_data[0]['description']);
                        $desc_data= str_replace('{OTP}',$otpnumber, $desc);
						$desc_send= str_replace('{SITE_TITLE}',SITE_TITLE, $desc_data);
                        $subject = str_replace('{SITE_TITLE}',SITE_TITLE, $template_data[0]['subject']);
                        $emailsubject = 'Forgot password '.SITE_TITLE;
						$data['subject']     = $subject;

						$data['description'] = $desc_send;
						$data['body'] = "";
						$msg = $this->load->view('emailtemplate', $data, true);
						//$mailsent=$this->sendmail->sendmailto($email,$subject, "$msg");
						$mailsent = sendEmailCI("$email", SITE_TITLE ,$emailsubject, $msg);
                        if($mailsent==1){
                         $update_data = array('password' =>encrypt_password($otpnumber));
		                 $wheres = array("id" => $userid);
		                 $updatedata = $this->dynamic_model->updateRowWhere("user",$wheres,$update_data);

				        	$data_val        = array('userid'=>"$userid",'password'=>"$otpnumber");
				        	$arg['status']     = 1;
				        	$arg['error_code']  = HTTP_OK;
						 	$arg['error_line']= __line__;
						 	$arg['data']       = $data_val;
							$arg['message']    = $this->lang->line('forgot_send');
						}else{
							$arg['data']       = array();
							$arg['error_code']  = HTTP_NOT_MODIFIED;
							$arg['error_line']= __line__;
							$arg['status']     = 0;
						 	$arg['message']    = $this->lang->line('forgot_otp_not_send');
						   }
						}
					}
					else
					{
						$arg['data']       = array();
						$arg['error_code']  = HTTP_NOT_MODIFIED;
						$arg['error_line']= __line__;
						$arg['status']     = 0;
					 	$arg['message']    = $this->lang->line('email_not_exist');
					}
				}
			}
		}
			echo json_encode($arg);
	}

	/****************Function logout**********************************
     * @type            : Function
     * @Author          : Aamir
     * @function name   : logout
     * @description     : Clear all session of user.
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function get_services_type()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$condition = array('status' => "Active");
			$service_list = $this->dynamic_model->getdatafromtable('manage_services_type',$condition,'id,service_name');

			if(!empty($service_list)){
				foreach($service_list as $value)
	            {
	            	$servicedata['service_type_id']= encode($value['id']);
	            	$servicedata['service_name']   = $value['service_name'];
	            	$response[]	        = $servicedata;
	            }
				$arg['status']     = 1;
				$arg['error_code']  = HTTP_OK;
				$arg['error_line']= __line__;
				$arg['data']       = $response;
				$arg['message']    = $this->lang->line('record_found');
			} else {
				$arg['status']     = 0;
				$arg['error_code']  = HTTP_OK;
				$arg['error_line']= __line__;
				$arg['data']       = array();
			 	$arg['message']    = $this->lang->line('record_not_found');
			}
		}

		echo json_encode($arg);
	}
	/****************Function slot_list **********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : slot_list
     * @description     : slot list
     * @param           : null
     * @return          : array
     * ********************************************************** */
	public function slot_list()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$condition = array('slot_status' => "Active");
			$slots_data = $this->dynamic_model->getdatafromtable('business_slots',$condition);
			if(!empty($slots_data)){
				$arg['status']     = 1;
				$arg['error_code']  = HTTP_OK;
				$arg['error_line']= __line__;
				$arg['data']       = $slots_data;
				$arg['message']    = $this->lang->line('record_found');
			} else {
				$arg['status']     = 0;
				$arg['error_code']  = HTTP_OK;
				$arg['error_line']= __line__;
				$arg['data']       = array();
			 	$arg['message']    = $this->lang->line('record_not_found');
			}
		}

		echo json_encode($arg);
	}
	// public function get_business_categories12()
	// {
	// 	$arg = array();
	// 	$version_result = version_check_helper1();
	// 	if($version_result['status'] != 1 )
	// 	{
	// 		$arg = $version_result;
	// 	}
	// 	else
	// 	{
	// 		$condition = array('status' => "Active",'category_type' => "1");
	// 		$category_list = $this->dynamic_model->getdatafromtable('manage_category',$condition,'id,category_name');
	// 		if(!empty($category_list)){
	// 			foreach($category_list as $value)
	//             {
	//             	$categorydata['category_id']= encode($value['id']);
	//             	$categorydata['category_name']   = $value['category_name'];
	//             	$response[]	        = $categorydata;
	//             }
	// 			$arg['status']     = 1;
	// 			$arg['error_code']  = HTTP_OK;
	// 			$arg['error_line']= __line__;
	// 			$arg['data']       = $response;
	// 			$arg['message']    = $this->lang->line('record_found');
	// 		} else {
	// 			$arg['status']     = 0;
	// 			$arg['error_code']  =HTTP_OK;
	// 			$arg['error_line']= __line__;
	// 			$arg['data']       = array();
	// 		 	$arg['message']    = $this->lang->line('record_not_found');
	// 		}
	// 	}
	// 	echo json_encode($arg);
	// }
	public function get_business_categories()
	{
		$arg = array();
		$_POST = json_decode(file_get_contents("php://input"),true);
		if($_POST){
		$type = !empty($this->input->post('type')) ? $this->input->post('type') : '1';

		if($this->input->post('subcat')=='All'){
           $subcat = $this->input->post('subcat');
		}elseif(!empty($this->input->post('subcat'))){
          $subcat = decode($this->input->post('subcat'));
		}else{
			 $subcat ='';
		}

		//subcat all  for all services
		if($subcat=="All"){
           $condition = array('status' => "Active",'category_type' => "$type",'category_parent !=' => "0");
		}else{
			$condition = array('status' => "Active",'category_type' => "$type",'category_parent' => "$subcat");
		}
		$category_list = $this->dynamic_model->getdatafromtable('manage_category',$condition,'id,category_name');
		if(!empty($category_list)){
			foreach($category_list as $value){
            	$categorydata['category_id']= encode($value['id']);
            	$categorydata['category_name']   = $value['category_name'];
            	$response[]	        = $categorydata;
            }
			$arg['status']     = 1;
			$arg['error_code']  = HTTP_OK;
			$arg['error_line']= __line__;
			$arg['data']       = $response;
			$arg['message']    = $this->lang->line('record_found');
		}else {
			$arg['status']     = 0;
			$arg['error_code']  =HTTP_OK;
			$arg['error_line']= __line__;
			$arg['data']       = array();
		 	$arg['message']    = $this->lang->line('record_not_found');
		}
	  }

	 echo json_encode($arg);
	}
	public function subcat_detail($type='',$id='')
	{
	    $subcategory=[];
	    $condition1 = array('status' => "Active",'category_type' => "$type",'category_parent' => $id);
	    $subcategory_data = $this->dynamic_model->getdatafromtable('manage_category',$condition1,"id,category_name");
	    if(!empty($subcategory_data)){
		        foreach($subcategory_data as $value1){
            	$scategory['id']= encode($id);
            	$scategory['category_id']= encode($value1['id']);
            	$scategory['category_name']   = $value1['category_name'];
            	 $subcategory[]=$scategory;
               }
            }
            return $subcategory;
    }
	public function get_business_multiple_categories()
	{
		$arg = array();
		$_POST = json_decode(file_get_contents("php://input"),true);
		if($_POST){
		$type = !empty($this->input->post('type')) ? $this->input->post('type') : '2';
		$condition = array('status' => "Active",'category_type' => "$type",'category_parent' => "0");
		$category_list = $this->dynamic_model->getdatafromtable('manage_category',$condition,'id,category_name');

		if(!empty($category_list)){
			foreach($category_list as $key=>$value){
            	$categorydata['category_id']= encode($value['id']);
            	$categorydata['category_name']   = $value['category_name'];
            	$subcategory=$this->subcat_detail($type,$value['id']);
		        $categorydata['subcategory']   = $subcategory;
            	$response[]	        = $categorydata;
            }
			$arg['status']     = 1;
			$arg['error_code']  = HTTP_OK;
			$arg['error_line']= __line__;
			$arg['data']       = $response;
			$arg['message']    = $this->lang->line('record_found');
		}else {
			$arg['status']     = 0;
			$arg['error_code']  =HTTP_OK;
			$arg['error_line']= __line__;
			$arg['data']       = array();
		 	$arg['message']    = $this->lang->line('record_not_found');
		}
	  }

	 echo json_encode($arg);
	}
	/****************Function Get business type**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : get_business_type
     * @description     : get business type for create business
                           purpose,

     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function get_business_type()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$condition = array('status' => "Active");
			$business_type_data = $this->dynamic_model->getdatafromtable('manage_business_type',$condition,'id,business_type');

			if(!empty($business_type_data)){
			     foreach($business_type_data as $value)
	            {
	            	$businesstypedata['business_type_id']= encode($value['id']);
	            	$businesstypedata['business_type']   = $value['business_type'];
	            	$response[]	        = $businesstypedata;
	            }
				$arg['status']     = 1;
				$arg['error_code']  = HTTP_OK;
				$arg['error_line']= __line__;
				$arg['data']       = $response;
				$arg['message']    = $this->lang->line('record_found');
			} else {
				$arg['status']     = 0;
				$arg['error_code']  = HTTP_OK;
				$arg['error_line']= __line__;
				$arg['data']       = array();
			 	$arg['message']    = $this->lang->line('record_not_found');
			}
		}

		echo json_encode($arg);
	}
   /****************Function register business profile**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : business_profile
     * @description     : Create business_profile for studio owner
     * @param           : null
     * @return          : null
     * ********************************************************** */

	public function register_business_profile_old()
	{
		$arg   = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$this->form_validation->set_rules('user_id','User Id', 'required|trim', array( 'required' => $this->lang->line('user_id_required')));
			$this->form_validation->set_rules('service_type_id', 'Services', 'required',array('required' => $this->lang->line('service_type_required')));
			 $this->form_validation->set_rules('category_id', 'Category Id', 'required',array('required' => $this->lang->line('category_required')));
            $this->form_validation->set_rules('business_type_id', 'Business Type ', 'required',array('required' => $this->lang->line('business_type_required')));
            $this->form_validation->set_rules('business_address', 'Business Address', 'required',array('required' => $this->lang->line('business_address_required')));
			$this->form_validation->set_rules('country','Country', 'required', array( 'required' => $this->lang->line('country_required')));
			$this->form_validation->set_rules('state','State', 'required', array( 'required' => $this->lang->line('state_required')));
			$this->form_validation->set_rules('city','City', 'required', array( 'required' => $this->lang->line('city_required')));
			$this->form_validation->set_rules('lat', 'Latitude','required',array('required' => $this->lang->line('lat')));
			$this->form_validation->set_rules('lang', 'Longitude','required',array( 'required' => $this->lang->line('lng')));
           $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[business.primary_email]' , array('required' => $this->lang->line('email_required'),'valid_email' => $this->lang->line('email_valid'),'is_unique' => $this->lang->line('email_unique')));
		   $this->form_validation->set_rules('mobile', 'Mobile', 'required|min_length[8]|max_length[20]|numeric|is_unique[business.business_phone]', array(
					'required' => $this->lang->line('mobile_required'),
					'min_length' => $this->lang->line('mobile_min_length'),
					'max_length' => $this->lang->line('mobile_max_length'),
					'numeric' => $this->lang->line('mobile_numeric')
				));
			$this->form_validation->set_rules('website','Website','required',array('required' => $this->lang->line('website_required')));
			if(empty($_FILES['business_logo']['name'])){
				$this->form_validation->set_rules('business_logo','Business Logo','required',array('required' => $this->lang->line('business_logo_required')));
			}
			// $this->form_validation->set_rules('area','area','required',array('required' => $this->lang->line('area_required')));
			// $this->form_validation->set_rules('no_of_floor','no_of_floor','required',array('required' => $this->lang->line('no_of_floor_required')));
			// $this->form_validation->set_rules('business_located_floor','location_detail','required',array('required' => $this->lang->line('business_located_floor_required')));
			// $this->form_validation->set_rules('no_of_employee','no_of_employee','required',array('required' => $this->lang->line('no_of_employee_required')));
			// $this->form_validation->set_rules('is_seasonal','is_seasonal','required',array('required' => $this->lang->line('is_seasonal_required')));
			// $this->form_validation->set_rules('services_offered','services_offered','required',array('required' => $this->lang->line('services_offered_required')));

			if ($this->form_validation->run() == FALSE)
			{
			  	$arg['status']  = 0;
			  	$arg['error_code'] = 0;
				$arg['error_line']= __line__;
			 	$arg['message'] = get_form_error($this->form_validation->error_array());
			}
			else
			{
				$services='';
				$time=time();
				$uid          = $this->input->post('user_id');
				if(!empty($uid)){
				  $user_id = decode($this->input->post('user_id'));
				}else{
					$userdata = web_checkuserid();
					$user_id  =  decode($userdata['data']['id']);
				}
				//Check Subscription plan purchase or not
				$where = array('id'=>$user_id);
		        $user_data = $this->dynamic_model->getdatafromtable('user',$where,'id,plan_id');
		        if(!empty($user_data)){
		        	$plan_id=(!empty($user_data[0]['plan_id'])) ? $user_data[0]['plan_id'] : "";
		        	$plan_data = $this->studio_model->plan_check($plan_id,$user_id);
		        	if(empty($plan_data)){
		        	$arg['status']     = 0;
		            $arg['error_code']  = HTTP_NOT_FOUND;
					$arg['error_line']= __line__;
					$arg['data']       = array();
					$arg['message']    = $this->lang->line('plan_not_purchase');
					echo json_encode($arg);exit();
				  }
		        }
				//Check business already registered or not
				$where1 = array('user_id'=>$user_id);
		        $business_data = $this->dynamic_model->getdatafromtable('business',$where1,'id');
		        if(!empty($business_data)){
		        	$arg['status']     = 0;
		            $arg['error_code']  = HTTP_NOT_FOUND;
					$arg['error_line']= __line__;
					$arg['data']       = array();
					$arg['message']    = $this->lang->line('business_register_already');
					echo json_encode($arg);exit();
		        }
				$business_name   = $this->input->post('business_name');
				$service_type_id = $this->input->post('service_type_id');
				$services        = multiple_decode_ids($service_type_id);
				$category_id     = $this->input->post('category_id');
				$category_ids    = multiple_decode_ids($category_id);
				$business_type    = decode($this->input->post('business_type_id'));
				$business_address = $this->input->post('business_address');
				$email            = $this->input->post('email');
				$mobile           = $this->input->post('mobile');
				$latitude         = $this->input->post('lat');
				$longitude        = $this->input->post('lang');
				$country          = $this->input->post('country');
				$state            = $this->input->post('state');
				$city             = $this->input->post('city');
				$website          = $this->input->post('website');
				$owner_details    = $this->input->post('owner_details');
				$zipcode          = $this->input->post('zipcode');
				$location_name    = $this->input->post('location_name');
				// $area             = $this->input->post('area');
				// $no_of_floor      = $this->input->post('no_of_floor');
				// $business_located_floor  = $this->input->post('business_located_floor');
				// $no_of_employee     = $this->input->post('no_of_employee');
				// $is_seasonal        = $this->input->post('is_seasonal');
				// $services_offered   = $this->input->post('services_offered');
                if(!empty($_FILES['business_logo']['name'])) {
                    $img_name = $this->dynamic_model->fileupload('business_logo','uploads/business','Picture');
                }else{
                	$img_name ="building.png";
                }
				$businessData =   array('user_id'    =>$user_id,
									'business_name'  =>$business_name,
									'address'        =>$business_address,
									'service_type'   =>$services,
									'business_type'  =>$business_type,
									'category'		 =>$category_ids,
									'primary_email'  =>$email,
									'business_phone' =>$mobile,
									'website'        =>$website,
									'lat'            =>$latitude,
									'longitude'		 =>$longitude,
									'country'		 =>$country,
									'state'		     =>$state,
									'city'		     =>$city,
									'zipcode'		 =>$zipcode,
									'location_name'  =>$location_name,
									// 'area'			 => $area,
									// 'number_of_floor'=> $no_of_floor,
									// 'location_detail'=>$business_address,
									// 'business_located_floor'=>$business_located_floor,
									// 'number_of_employee'=>$no_of_employee,
									// 'is_seasonal'	  =>$is_seasonal,
									// 'services_offered'=>$services_offered,
									'logo'			  =>$img_name,
									'business_image'  =>$img_name,
									'owner_details'   =>$owner_details,
									'create_dt'		  =>$time,
									'update_dt'		  =>$time
				               );
					$business_id = $this->dynamic_model->insertdata('business',$businessData);
					if($business_id)
			        {
						$business_data=get_business_details($business_id);
						$arg['status']    = 1;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line']= __line__;
					 	$arg['message']   = $this->lang->line('business_register');
					 	$arg['data']      = $business_data;
			        }else{
			        	$arg['status']     = 0;
			            $arg['error_code']  = HTTP_NOT_FOUND;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('server_problem');
			        }


			}
		}
		echo json_encode($arg);
	}
	public function register_business_profile()
    {
        $arg   = array();
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
        }
        else
        {
            $this->form_validation->set_rules('user_id','User Id', 'required|trim', array( 'required' => $this->lang->line('user_id_required')));
            // $this->form_validation->set_rules('service_type_id', 'Services', 'required',array('required' => $this->lang->line('service_type_required')));
            $this->form_validation->set_rules('category_info', 'Category info', 'required',array('required' => $this->lang->line('category_required')));
            // $this->form_validation->set_rules('business_type_id', 'Business Type ', 'required',array('required' => $this->lang->line('business_type_required')));
            $this->form_validation->set_rules('business_address', 'Business Address', 'required',array('required' => $this->lang->line('business_address_required')));
            $this->form_validation->set_rules('country','Country', 'required', array( 'required' => $this->lang->line('country_required')));
            $this->form_validation->set_rules('state','State', 'required', array( 'required' => $this->lang->line('state_required')));
            $this->form_validation->set_rules('city','City', 'required', array( 'required' => $this->lang->line('city_required')));
            $this->form_validation->set_rules('lat', 'Latitude','required',array('required' => $this->lang->line('lat')));
            $this->form_validation->set_rules('lang', 'Longitude','required',array( 'required' => $this->lang->line('lng')));

           //$this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[business.primary_email]' , array('required' => $this->lang->line('email_required'),'valid_email' => $this->lang->line('email_valid'),'is_unique' => $this->lang->line('email_unique')));

           /*$this->form_validation->set_rules('mobile', 'Mobile', 'required|min_length[8]|max_length[20]|numeric|is_unique[business.business_phone]', array(
                    'required' => $this->lang->line('mobile_required'),
                    'min_length' => $this->lang->line('mobile_min_length'),
                    'max_length' => $this->lang->line('mobile_max_length'),
                    'numeric' => $this->lang->line('mobile_numeric'),'is_unique' => $this->lang->line('mobile_unique')
                ));*/

            $this->form_validation->set_rules('website','Website','required',array('required' => $this->lang->line('website_required')));
            if(empty($_FILES['business_logo']['name'])){
                $this->form_validation->set_rules('business_logo','Business Logo','required',array('required' => $this->lang->line('business_logo_required')));
            }
            if ($this->form_validation->run() == FALSE)
            {
                $arg['status']  = 0;
                $arg['error_code'] = 0;
                $arg['error_line']= __line__;
                $arg['message'] = get_form_error($this->form_validation->error_array());
            }
            else
            {
      //           $category_info='[{
		    //         "category": "S7MytFKyULIGAA==",
		    //         "subcategory_info": [
		    //             {
		    //                 "subcategory": "S7MyslIyNFSyBgA="
		    //             },
		    //             {
		    //                 "subcategory": "S7MyslIyNFKyBgA="
		    //             },
		    //             {
		    //                 "subcategory": "S7MyslIysVCyBgA="
		    //             }
		    //         ]
		    //     },
		    //     {
		    //         "category": "S7MytFKyVLIGAA==",
		    //         "subcategory_info": [
		    //             {
		    //                 "subcategory": "S7MyslIyNFayBgA="
		    //             },
		    //             {
		    //                 "subcategory": "S7MyslIyNFGyBgA="
		    //             },
		    //             {
		    //                 "subcategory": "S7MyslIyNFWyBgA="
		    //             },
		    //             {
		    //                 "subcategory": "S7MyslIyNFOyBgA="
		    //             },
		    //             {
		    //                 "subcategory": "S7MyslIyNFeyBgA="
		    //             },
		    //             {
		    //                 "subcategory": "S7MyslIytFCyBgA="
		    //             },
		    //             {
		    //                 "subcategory": "S7MyslIytFSyBgA="
		    //             }
		    //         ]
		    //     },
		    //     {
		    //         "category": "S7MyslIyNFCyBgA=",
		    //         "subcategory_info": [
		    //             {
		    //                 "subcategory": "S7MyslIyMlCyBgA="
		    //             },
		    //             {
		    //                 "subcategory": "S7MyslIyMlSyBgA="
		    //             },
		    //             {
		    //                 "subcategory": "S7MyslIyMlKyBgA="
		    //             },
		    //             {
		    //                 "subcategory": "S7MyslIyMlayBgA="
		    //             },
		    //             {
		    //                 "subcategory": "S7MyslIyMlGyBgA="
		    //             }
		    //         ]
		    //     }
		    // ]';
                $services='';
                $time=time();
                $uid          = $this->input->post('user_id');
                if(!empty($uid)){
                  $user_id = decode($this->input->post('user_id'));
                }else{
                    $userdata = web_checkuserid();
                    $user_id  =  decode($userdata['data']['id']);
                }
                //Check Subscription plan purchase or not
                $where = array('id'=>$user_id);
                $user_data = $this->dynamic_model->getdatafromtable('user',$where,'id,plan_id');
                if(!empty($user_data)){
                    $plan_id=(!empty($user_data[0]['plan_id'])) ? $user_data[0]['plan_id'] : "";
                    $plan_data = $this->studio_model->plan_check($plan_id,$user_id);
                    if(empty($plan_data)){
                    $arg['status']     = 0;
                    $arg['error_code']  = HTTP_NOT_FOUND;
                    $arg['error_line']= __line__;
                    $arg['data']       = array();
                    $arg['message']    = $this->lang->line('plan_not_purchase');
                    echo json_encode($arg);exit();
                  }
                }
                //Check business already registered or not
                $where1 = array('user_id'=>$user_id);
                $business_data = $this->dynamic_model->getdatafromtable('business',$where1,'id');
                if(!empty($business_data)){
                 $arg['status']     = 0;
                    $arg['error_code']  = HTTP_NOT_FOUND;
                    $arg['error_line']= __line__;
                    $arg['data']       = array();
                    $arg['message']    = $this->lang->line('business_register_already');
                   // echo json_encode($arg);exit();
$this->dynamic_model->deletedata('business',$where1);

                }
				 $business_name   = $this->input->post('business_name');
				 $services = 0;
				 if ($this->input->post('service_type_id')) {
					$service_type_id = $this->input->post('service_type_id');
                 	$services        = multiple_decode_ids($service_type_id);
				 }

                // $category_id     = $this->input->post('category_id');
                // $category_ids    = multiple_decode_ids($category_id);

				$business_type = 0;
				if ($this->input->post('business_type_id')) {
					$business_type    = decode($this->input->post('business_type_id'));
				}
                $category_info    = json_decode($this->input->post('category_info'));
                //$category_info    = json_decode($category_info);
                //print_r($category_info);die;
                $business_address = $this->input->post('business_address');
                $email            = $this->input->post('email');
                $mobile           = $this->input->post('mobile');
                $latitude         = $this->input->post('lat');
                $longitude        = $this->input->post('lang');
                $country          = $this->input->post('country');
                $state            = $this->input->post('state');
                $city             = $this->input->post('city');
                $website          = $this->input->post('websit