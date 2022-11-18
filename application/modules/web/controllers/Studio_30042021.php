<?php defined('BASEPATH') OR exit('No direct script access allowed');
//require APPPATH . '/libraries/REST_Controller.php';
// include_once APPPATH.'third_party/phpseclib/Crypt/RSA.php';
ini_set('max_execution_time', 0);
/* * ***************Studio.php**********************************
 * @product name    : Signal Health Group Inc
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

            $userdata = array(
				'notification'	=>	$notification,
				'email'	=>	$email,
				'password'		=>	$hashed_password,
				'profile_img'	=>	$image,
				'email_verified'=>	'0',
				'mobile_verified'	=>	'1',
				'mobile_otp'	=>	$uniquemobile,
				'mobile_otp_date'	=>	$time,
				'subscription_period'	=>	strtotime($subscription_period),'subscription_startdate'=>strtotime(date('Y-m-d')),
				'create_dt'	=>$time,
				'marchant_id_type'	=>	1,
				'marchant_id'	=>	'RKMDWMMA611F1',
				'clover_key'	=>	'af2bbe3c4b4dd3682793cc09155a9a7a',
				'access_token'	=>	'24a0bbef-8ef3-657b-9449-4b01c158d928',
				'update_dt'	=>	$time,'status' => 'Active'
			);
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
                $website          = $this->input->post('website');
                $owner_details    = $this->input->post('owner_details');
                $zipcode          = $this->input->post('zipcode');
                $location_name    = $this->input->post('location_name');
                if(!empty($_FILES['business_logo']['name'])) {
                    $img_name = $this->dynamic_model->fileupload('business_logo','uploads/business','Picture');
                }else{
                    $img_name ="building.png";
                }
                $businessData =   array('user_id'    =>$user_id,
                                    'business_name'  =>$business_name,
                                    'address'        =>$business_address,
                                    //'service_type'   =>$services,
                                    //'business_type'  =>$business_type,
                                    //'category'       =>$category_ids,
                                    'primary_email'  =>$email,
                                    'business_phone' =>$mobile,
                                    'website'        =>$website,
                                    'lat'            =>$latitude,
                                    'longitude'      =>$longitude,
                                    'country'        =>$country,
                                    'state'          =>$state,
                                    'city'           =>$city,
                                    'zipcode'        =>$zipcode,
                                    'location_name'  =>$location_name,
                                    'logo'            =>$img_name,
                                    'business_image'  =>$img_name,
                                    'owner_details'   =>$owner_details,
                                    'create_dt'       =>$time,
                                    'update_dt'       =>$time
							   );
					if ($services != 0) {
						$businessData['service_type'] = $services;
					}
					if ($business_type != 0) {
						$businessData['business_type'] = $business_type;
					}
                    $business_id = $this->dynamic_model->insertdata('business',$businessData);
                    if($business_id)
                    {

                    	$time = time();
                    	$skills = '3,2';
                    	$total_experience = '1';
                    $instructor_data = array('user_id'=>$user_id,'about'=>$business_name,'create_dt'=>$time,'update_dt'=>$time,'skill'=>$skills,'total_experience'=>$total_experience);
$this->dynamic_model->insertdata('instructor_details',$instructor_data);

$insert_data = array('user_id' => $user_id,
					'business_id' => $business_id,
					'status' => "Approve",
					'create_dt' => $time,
					'update_dt' => $time
								);
$this->dynamic_model->insertdata('business_trainer_relationship',$insert_data);

$where2 = array('id' => $user_id);
$updateData = array('lat' => $latitude,
	'availability_status' => 'Available',
                      'lang' => $longitude,
                      'country' => $country,
                      'state' => $state,
                      'city' => $city,
                      'address'=> $location_name,
	                   );
$this->dynamic_model->updateRowWhere('user',$where2,$updateData);



$this->dynamic_model->updateRowWhere('user_card_save',array('user_id' => $user_id,'business_id'=>0),array('business_id'=>$business_id));



                        if(!empty($category_info)){
                         foreach($category_info as $value){
                           $category=decode($value->category);
                            $catData = array(
                              'business_id'    =>$business_id,
                              'category'       =>$category,
                              'parent_id'      =>0,
                              'type'           =>1,
                              'create_dt'      =>$time,
                              'update_dt'      =>$time
                               );
                          $subcat_id = $this->dynamic_model->insertdata('business_category',$catData);
                             foreach($value->subcategory_info as $value1){
                              $subcategory=decode($value1->subcategory);
                              $subData = array(
                              'business_id'    =>$business_id,
                              'category'       =>$subcategory,
                              'parent_id'      =>$category,
                              'type'           =>1,
                              'create_dt'      =>$time,
                              'update_dt'      =>$time
                               );
                            $this->dynamic_model->insertdata('business_category',$subData);
                           }

                         }
                        }
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


	public function get_business_profile() {
    	$arg = array();
    	//check version is updated or not
		$version_result = version_check_helper1();
		//echo '--'.$version_result; die;
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = web_checkuserid();
		    if($userdata['status'] != 1){
				$arg = $userdata;
			}
			else
			{

				$user_id 		= decode($userdata['data']['id']);
				$business_id 	= decode($userdata['data']['business_id']);
				$where1 = array('id' => $business_id, 'user_id'=>$user_id);
				$business_data = $this->dynamic_model->getdatafromtable('business',$where1,'*');

				if (!$business_data) {

					$arg['status']     = 0;
                        $arg['error_code']  = HTTP_NOT_FOUND;
                        $arg['error_line']= __line__;
                        $arg['message']    = 'No Data Found';

				} else {

					$info = $business_data[0];
					$query = 'SELECT category as id FROM `business_category` WHERE business_category.parent_id = 0 and business_category.business_id = '.$business_id;
					$category = $this->dynamic_model->getQueryResultArray($query);

					$categoryArray = array();
					if (!empty($category)) {
						for($i = 0; $i < count($category); $i++) {
							$rowInfo = $category[$i];
							$categoryId = $rowInfo['id'];
							$subQuery = 'SELECT GROUP_CONCAT(id) as id FROM `business_category` WHERE business_category.parent_id = '.$categoryId.' and business_category.business_id = '.$business_id;
							array_push($categoryArray, array('parent_id' => $categoryId, 'subcategory' => $this->dynamic_model->getQueryRowArray($subQuery)));
						}
					}
					$url = site_url() . 'uploads/business/';

					if (!empty($info['logo'])) {
						$info['logo'] = $url.$info['logo'];
					} else {
						$info['logo'] = site_url().'uploads/logo.png';
					}
					$info['category'] = $categoryArray;

					$arg['status']    = 1;
					$arg['error_code'] = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['message']   = $this->lang->line('business_register');
					$arg['data']      = $info;
				}

			}
		}
		echo json_encode($arg);
    }

	public function business_location_update()
	{
		$arg   = array();

		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
			// $this->form_validation->set_rules('user_id','User Id', 'required|trim', array( 'required' => $this->lang->line('user_id_required')));
			$this->form_validation->set_rules('business_id','Business Id', 'required|trim', array( 'required' => $this->lang->line('business_id_required')));
            /* $this->form_validation->set_rules('other_address', 'Business Address', 'required',array('required' => $this->lang->line('business_address_required')));
			$this->form_validation->set_rules('country','Country', 'required', array( 'required' => $this->lang->line('country_required')));
			$this->form_validation->set_rules('state','State', 'required', array( 'required' => $this->lang->line('state_required')));
			$this->form_validation->set_rules('city','City', 'required', array( 'required' => $this->lang->line('city_required')));
			$this->form_validation->set_rules('lat', 'Latitude','required',array('required' => $this->lang->line('lat')));
			$this->form_validation->set_rules('lang', 'Longitude','required',array( 'required' => $this->lang->line('lng'))); */
			// $this->form_validation->set_rules('location_name', 'location_name','required',array( 'required' => $this->lang->line('location_name_req')));

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
				//echo encode(6);die;
				$time=time();
				$uid          = $this->input->post('user_id');
				if(!empty($uid)){
				  $user_id = decode($this->input->post('user_id'));
				}else{
					$userdata = web_checkuserid();
					$user_id  =  decode($userdata['data']['id']);
				}

				// echo json_encode($user_id); exit;
				//Check Subscription plan purchase or not
				$where = array('id'=>$user_id);
		        $user_data = $this->dynamic_model->getdatafromtable('user',$where,'id,plan_id, status');
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


				$status = $user_data[0]['status'];
				if ($status != 'Active') {
					$this->dynamic_model->updateRowWhere('user', $where, array('status' => 'Active'));
				}

				$business_id      = decode($this->input->post('business_id'));
				//$business_id      = 4;
				/* $business_address = $this->input->post('other_address');
				//$location_name    = $this->input->post('location_name');
				$latitude         = $this->input->post('lat');
				$longitude        = $this->input->post('lang');
				$country          = $this->input->post('country');
				$state            = $this->input->post('state');
				$city             = $this->input->post('city');
				$zipcode          = $this->input->post('zipcode'); */
				//$capacity         = $this->input->post('capacity');
				$location_info    = $this->input->post('location_info');

				/* $businessData =   array(
									//'location_name'  =>$location_name,
									'other_address'  =>$business_address,
									'other_lat'      =>$latitude,
									'other_longitude'=>$longitude,
									'other_country'  =>$country,
									'other_state'    =>$state,
									'other_city'	 =>$city,
									'zipcode'		 =>$zipcode,
									'location_detail'=>$business_address,
									'update_dt'		  =>$time
				               );
				    $condition=array("id"=>$business_id);
					$business_update = $this->dynamic_model->updateRowWhere('business',$condition,$businessData); */
					if(true)
			        {
						$condition1 = array("business_id"=>$business_id);
						$businessInfo = $this->dynamic_model->getdatafromtable('business', array('id' => $business_id), '*');

						$businessLatitude = $businessInfo[0]['lat'];
						$businessLongitude = $businessInfo[0]['longitude'];
						$businessAddress = $businessInfo[0]['address'];

			        	// $business_delete = $this->dynamic_model->deletedata('business_location',$condition1);

						$insert_array = array();
						$update_array = array();
						foreach($location_info as $value){
							$location_id = $value['location_id'];
							$is_address_same = ucfirst($value['is_address_same']);
							$address = $value['address'];

							if ($is_address_same == 'Yes' || $is_address_same == 'No') {
								if ($location_id != null) {
									$update = array(
										'id' => decode($value['location_id']),
										'business_id' => $business_id,
										'is_address_same' => $is_address_same,
										'address' => $address,
										'location_name'  =>$value['location_name'],
										'capacity'		 =>$value['capacity'],
										'update_dt'		  =>$time
									);
									if (array_key_exists('location_url', $value)) {
										$update['location_url'] = $value['location_url'];
									}

									if ($is_address_same == 'No') {
										$update['address'] = $value['address'];
										$update['country'] = $value['country'];
										$update['state'] = $value['state'];
										$update['city'] = $value['city'];
										$update['lat'] = $value['lat'];
										$update['longitude'] = $value['longitude'];
										$update['zipcode'] = $value['zipcode'];
									}

									$local_latitude = $businessLatitude;
									$local_longitude = $businessLongitude;
									if ( $update['is_address_same'] == 'No') {

										$local_latitude = $value['lat'];

										$local_longitude = $value['longitude'];

										$businessAddress = $value['address'];

									}

									$update['map_url'] = 'http://maps.google.com/maps?q='.$local_latitude.','.$local_longitude;

									array_push($update_array, $update);

								} else {
									$insert = array(
										'business_id' => $business_id,
										'location_name'  =>$value['location_name'],
										'is_address_same' => $is_address_same,
										'address' => $address,
										'capacity'		 =>$value['capacity'],
										'location_url'		 =>$value['location_url'],
										'create_dt'		  =>$time,
										'update_dt'		  =>$time
									);
									if (array_key_exists('location_url', $value)) {
										$insert['location_url'] = $value['location_url'];
									}

									$local_latitude = $businessLatitude;
									$local_longitude = $businessLongitude;

									if ( $insert['is_address_same'] == 'No') {

										$insert['address'] = $value['address'];
										$insert['country'] = $value['country'];
										$insert['state'] = $value['state'];
										$insert['city'] = $value['city'];
										$insert['lat'] = $value['lat'];
										$insert['longitude'] = $value['longitude'];
										$insert['zipcode'] = $value['zipcode'];

										$local_latitude = $value['lat'];

										$local_longitude = $value['longitude'];

										$businessAddress = $value['address'];

									} else {
										$insert['address'] = '';
										$insert['country'] = '';
										$insert['state'] = '';
										$insert['city'] = '';
										$insert['lat'] = 0;
										$insert['longitude'] = 0;
										$insert['zipcode'] = 0;
									}

									$insert['map_url'] = 'http://maps.google.com/maps?q='.$local_latitude.','.$local_longitude;

									array_push($insert_array, $insert);
								}

							} else {
								$arg['status']     = 0;
								$arg['error_code']  = HTTP_NOT_FOUND;
								$arg['error_line']= __line__;
								$arg['data']       = array();
								$arg['message']    = 'Invalid Address';
							}

						}

						if (!empty($insert_array)) {
							$this->db->insert_batch('business_location', $insert_array);
						}

						if (!empty($update_array)) {
							$this->db->update_batch('business_location', $update_array, 'id');
						}

						$arg['status']    = 1;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line']= __line__;
					 	$arg['message']   = $this->lang->line('business_location_succ');
					 	$arg['data']      = [];
			        }else{
			        	$arg['status']     = 0;
			            $arg['error_code']  = HTTP_NOT_FOUND;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('server_problem');
			        }


			}
		 }
		}
		echo json_encode($arg);
	}

	public function get_business_location_detail(){
		$arg   = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{

			$this->form_validation->set_rules('business_id','Business Id', 'required|trim', array( 'required' => $this->lang->line('business_id_required')));


			if ($this->form_validation->run() == FALSE)
			{
			  	$arg['status']  = 0;
			  	$arg['error_code'] = 0;
				$arg['error_line']= __line__;
			 	$arg['message'] = get_form_error($this->form_validation->error_array());
			}
			else
			{
				$business_id = decode($this->input->post('business_id'));
				$where = array('id'=>$business_id);
		        $business_data = $this->dynamic_model->getdatafromtable('business',$where);
		        $where1 = array('business_id'=>$business_id);
		        $business_location_data = $this->dynamic_model->getdatafromtable('business_location',$where1);

		        $business_room_array= array();
		        if (!empty($business_location_data)) {
		        	foreach ($business_location_data as $value) {
				    	$roomsdata['location_id'] = encode($value['id']);
				    	$roomsdata['location_name'] = $value['location_name'];
				    	$roomsdata['is_address_same']  = $value['is_address_same'];
				    	$roomsdata['address']  = ($value['address'] == null) ? '' : $value['address'];
				    	$roomsdata['capacity']  = $value['capacity'];
						$roomsdata['location_url']  = $value['location_url'];
						$roomsdata['country']  = $value['country'];
						$roomsdata['state']  = $value['state'];
						$roomsdata['city']  = $value['city'];
						$roomsdata['lat']  = $value['lat'];
						$roomsdata['longitude']  = $value['longitude'];
						$roomsdata['zipcode']  = $value['zipcode'];
				    	$business_room_array[]     = $roomsdata;
					}
		        } else {
		        	$business_room_array[] = array('location_id' => '', 'location_name' => '', 'is_address_same' => 'yes', 'address' => '', 'capacity' => 15);
		        }

		        // $business_room_array = array(
		       	// 						'location_name'=>  $business_location_data[0]['location_name'],
		        // 						'capacity'=>  $business_location_data[0]['capacity']
		       	// 					);
		        $business_array = array(
		        					'user_id'=>  encode($business_data[0]['user_id']),
		        					'business_id'=>  encode($business_data[0]['id']),
		        					'location_name'=>  $business_data[0]['location_name'],
		        					'city'=>  $business_data[0]['city'],
		        					'country'=>  $business_data[0]['country'],
		        					'state'=>  $business_data[0]['state'],
		        					'zipcode'=>  $business_data[0]['zipcode'],
		        					'latitude'=>  $business_data[0]['lat'],
		        					'longitude'=>  $business_data[0]['longitude'],
		        					'address'=>  $business_data[0]['address'],
		        					'other_address'=>  $business_data[0]['other_address'],
		        					'location_info'=>  $business_room_array
		        				);

		        if(!empty($business_data)){
		        	$arg['status']    = 1;
					$arg['error_code'] = HTTP_OK;
					$arg['error_line']= __line__;
				 	$arg['message']   = $this->lang->line('business_location_succ');
				 	$arg['data']      = $business_array;
				}else{
					$arg['status']     = 0;
		            $arg['error_code']  = HTTP_NOT_FOUND;
					$arg['error_line']= __line__;
					$arg['data']       = array();
					$arg['message']    = 'No record found';
		        }
			}
		 }
		}
		echo json_encode($arg);
	}
	/****************Function get_room_locationDetails**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : get_room_location
     * @description     : get room location
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function get_room_location()
	{
		$arg   = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
			$this->form_validation->set_rules('business_id','Business Id', 'required|trim', array( 'required' => $this->lang->line('business_id_required')));
			if($this->form_validation->run() == FALSE)
			{
			  	$arg['status']  = 0;
			  	$arg['error_code'] = 0;
				$arg['error_line']= __line__;
			 	$arg['message'] = get_form_error($this->form_validation->error_array());
			}
			else
			{
				//echo encode("4");die;
				$time=time();
				$business_id          = decode($this->input->post('business_id'));
				//print_r($business_id); die;
				$where = array('business_id'=>$business_id);
		        $roomData = $this->dynamic_model->getdatafromtable('business_location',$where);
		        if(!empty($roomData)){
		        	foreach($roomData as $value)
		            {
		            	$roomsdata['location_id']   = encode($value['id']);
		            	$roomsdata['location_name'] = $value['location_name'];
		            	$roomsdata['capacity']  = $value['capacity'];
		            	$response[]     = $roomsdata;

		            }
						$arg['status']    = 1;
						$arg['error_code']= HTTP_OK;
						$arg['error_line']= __line__;
					 	$arg['message']   = $this->lang->line('record_found');
					 	$arg['data']      =$response;
			        }else{
			        	$arg['status']     = 0;
			            $arg['error_code']  = HTTP_NOT_FOUND;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('record_not_found');
			        }
			}
		 }
		}
		echo json_encode($arg);
	}
	/****************Function Merchants Details**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : merachant_details
     * @description     : Update merchant details for business owner
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function complete_merchant_profile()
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
		    $this->form_validation->set_rules('year_start', 'Year Start', 'required|numeric|min_length[4]|max_length[4]',array(
				'required'   => $this->lang->line('start_year_required'),
				'min_length' => $this->lang->line('start_year_min_length'),
				'max_length' => $this->lang->line('start_year_max_length'),
				'numeric'    => $this->lang->line('start_year_numeric')
			));
		    $this->form_validation->set_rules('month_start', 'Month Start', 'required|numeric|less_than_equal_to[12]|greater_than[0]|min_length[2]',array('required'=> $this->lang->line('start_month_required'),
								'min_length' => $this->lang->line('start_month_min_length'),
								'less_than_equal_to' => $this->lang->line('start_month_less_than_equal_to'),
								'greater_than' => $this->lang->line('start_month_greater_than'),
								'numeric' => $this->lang->line('start_month_numeric')
							));

			if(empty($_FILES['cheque_image']['name'])){
				$this->form_validation->set_rules('cheque_image','Cheque Image','required',array('required' => $this->lang->line('cheque_image_req')));
			}
			$this->form_validation->set_rules('bank_account_type','Bank Account Type','required',array('required' => $this->lang->line('bank_account_type_req')));
			if ($this->form_validation->run() == FALSE)
			{
			  	$arg['status']  = 0;
			  	$arg['error_code'] = 0;
				$arg['error_line']= __line__;
			 	$arg['message'] = get_form_error($this->form_validation->error_array());
			}
			else
			{
				$time=time();
				$uid          = $this->input->post('user_id');
				if(!empty($uid)){
				  $user_id = decode($this->input->post('user_id'));
				}else{
					$userdata = web_checkuserid();
					$user_id  =  decode($userdata['data']['id']);
				}
				$year_start     = $this->input->post('year_start');
				$month_start    = $this->input->post('month_start');
				$bank_account_type     = $this->input->post('bank_account_type');
                if(!empty($_FILES['cheque_image']['name'])){
                    $cheque_image = $this->dynamic_model->fileupload('cheque_image','uploads/bank_img','Picture');
                }
				$merchantData =   array('year_start'    =>$year_start,
									'month_start'  		=>$month_start,
									'bank_account_type' =>$bank_account_type,
									'cheque_image'   	=>$cheque_image
				                   );
				$merchantDetails=array("merchant_details"=>json_encode($merchantData));
				$merchant= $this->dynamic_model->updateRowWhere('user', array('id' =>$user_id),$merchantDetails);
				if($merchant)
		        {
					$arg['status']    = 1;
					$arg['error_code'] = HTTP_OK;
					$arg['error_line']= __line__;
				 	$arg['message']   = $this->lang->line('meachant_save_succ');
				 	$arg['data']      = [];
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
	/****************Function Get pass type**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : get_pass_type
     * @description     : get pass type for create business passes
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function get_pass_type()
	{
		$arg = array();
		$_POST = json_decode(file_get_contents("php://input"), true);
		if($_POST)
		{
			$pass_subcat=decode($this->input->post("pass_subcat"));
			if(!empty($pass_subcat)){
				$condition = array("parent_id"=>$pass_subcat,'status' => "Active");
			}else{
			$condition = array("parent_id"=>0,'status' => "Active");
		    }
			$pass_type_data = $this->dynamic_model->getdatafromtable('manage_pass_type',$condition, '*', '', '', 'pass_days', 'ASC');
			if(!empty($pass_type_data)){
			     foreach($pass_type_data as $value)
	            {
					$passtypedata['pass_type_id']= encode($value['id']);
					if(!empty($pass_subcat)){

						if($value['parent_id'] == '1'){
							$passtypedata['pass_type'] = $value['pass_days'];
						}else if($value['parent_id'] == '10'){
							$passtypedata['pass_type'] = $value['pass_days'];
						}else{
							$passtypedata['pass_type'] = $value['pass_type'];
						}
						//$passtypedata['pass_type']   = ($value['parent_id'] == '10') ? $value['pass_days'] : $value['pass_type'];
					} else {
						$passtypedata['pass_type']   = $value['pass_type'];
					}

	            	$response[]	        = $passtypedata;
	            }
				$arg['status']     = 1;
				$arg['error_code']  = HTTP_OK;
				$arg['error_line']= __line__;
				$arg['data']       = $response;
				$arg['message']    = $this->lang->line('record_found');
			}else {
				$arg['status']     = 0;
				$arg['error_code']  = HTTP_OK;
				$arg['error_line']= __line__;
				$arg['data']       = array();
			 	$arg['message']    = $this->lang->line('record_not_found');
			}
	    }

		echo json_encode($arg);
	}
	/****************Function Add Pass passes**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : add_passes
     * @description     : add business passes
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function add_passes()
	{
	   $arg   = array();
	   $userdata = web_checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
                $this->form_validation->set_rules('pass_name','Pass name', 'required|trim', array( 'required' => $this->lang->line('pass_name_required')));
			    $this->form_validation->set_rules('purchase_date','Purchase Date', 'required|trim', array( 'required'=>$this->lang->line('purchase_date_required')));
			    //$this->form_validation->set_rules('expire_date','Expire Date', 'required|trim', array( 'required'=>$this->lang->line('expire_date_required')));
			    $this->form_validation->set_rules('amount','Amount', 'required|trim', array('required'=>$this->lang->line('amount_required')));
			    $this->form_validation->set_rules('service_type','Service Type','required|trim', array( 'required' => $this->lang->line('service_type_required')));
 			  // $this->form_validation->set_rules('service_id','Service Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
			    $this->form_validation->set_rules('passexpiry','Pass Expiry', 'required|trim');
			    $this->form_validation->set_rules('pass_type','Pass Type', 'required|trim', array('required'=>$this->lang->line('pass_type_required')));
			    $this->form_validation->set_rules('pass_sub_type','Pass Sub Type', 'required|trim', array('required'=>$this->lang->line('pass_subtype_required')));
			    $this->form_validation->set_rules('tax1','Tax 1','required',array( 'required' => $this->lang->line('tax1_required')));
		        $this->form_validation->set_rules('tax2','Tax 2','required',array( 'required' => $this->lang->line('tax2_required')));
		        $this->form_validation->set_rules('is_client_visible','Is client visible','required',array( 'required' => $this->lang->line('is_client_visible_required')));
		        $this->form_validation->set_rules('is_one_time_purchase','Is one time purchase','required',array( 'required' => $this->lang->line('is_one_time_purchase_required')));
		        $this->form_validation->set_rules('description','Description','required',array( 'required' => $this->lang->line('description_required')));
		        $this->form_validation->set_rules('notes','Notes','required',array( 'required' => $this->lang->line('notes_required')));
		         $this->form_validation->set_rules('is_recurring_billing','Is recurring billing','required',array( 'required' => $this->lang->line('is_recurring_billing_required')));
		         //$this->form_validation->set_rules('billing_start_from','Billing start from','required',array( 'required' => $this->lang->line('billing_start_from_required')));
		         $this->form_validation->set_rules('age_restriction','Age restriction','required',array( 'required' => $this->lang->line('age_restriction_required')));
		         //$this->form_validation->set_rules('age_over_under','Age over under','required',array( 'required' => $this->lang->line('age_over_under_required')));
				if ($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$userdata = web_checkuserid();
					$usid =decode($userdata['data']['id']);

					$time=time();
                    $pass_name   = $this->input->post('pass_name');
					//$service_id  = decode($this->input->post('service_id'));
					//service_type 1 classes 2 workshop
					$service_type = $this->input->post('service_type');
					$purchase_date    = $this->input->post('purchase_date');
					 $purchase_date_format    = date('Y-m-d',$this->input->post('purchase_date'));
					//$expire_date      = $this->input->post('expire_date');
					//$expire_date_format    = date('Y-m-d',$this->input->post('expire_date'));
					$passexpiry           = $this->input->post('passexpiry');

					$amount           = $this->input->post('amount');
					$class_type       = !empty($this->input->post('class_type'))? decode($this->input->post('class_type')) :'';
					$pass_type        = decode($this->input->post('pass_type'));
					$pass_sub_type        = decode($this->input->post('pass_sub_type'));
					$tax1      = $this->input->post('tax1');
					$tax2      = $this->input->post('tax2');
					$is_client_visible      = $this->input->post('is_client_visible');
					$is_one_time_purchase      = $this->input->post('is_one_time_purchase');
					$description      = $this->input->post('description');
					$age_over_under      = $this->input->post('age_over_under');
					if (is_null($age_over_under)) {
						$age_over_under = 'None';
					}
					$is_recurring_billing      = $this->input->post('is_recurring_billing');
					$billing_start_from      = ($this->input->post('billing_start_from'))?$this->input->post('billing_start_from'):'';
					$age_restriction      = $this->input->post('age_restriction');
					$notes      = $this->input->post('notes');
					$tax1_rate      = ($this->input->post('tax1_rate'))? $this->input->post('tax1_rate') :0;
					// $tax1_rate = ($amount*$tax1_rate) / 100;

					$tax2_rate      = ($this->input->post('tax2_rate'))?$this->input->post('tax2_rate') :0 ;
					// $tax2_rate = ($amount*$tax2_rate)/100;
					//get business Id
					$where = array('status' => 'Active','user_id'=>$usid);
			        $business_data = $this->dynamic_model->getdatafromtable('business',$where,'id');
			        $business_id=(!empty($business_data[0]['id'])) ? $business_data[0]['id'] : 0;
                    //pass_validity
			  //        $datetime1 = date_create($purchase_date_format);
					// $datetime2 = date_create($expire_date_format);
					// $interval = date_diff($datetime1, $datetime2);
					// //print_r($interval);die;
					// if($interval->format('%m')>0){
					// $month_text=($interval->format('%m')>1) ? "Months" :"Month";
					// if($interval->format('%d')>0){
	    //             $day_text=($interval->format('%d')>1) ? "Days" :"Day";
					// $pass_validity=$interval->format('%m').' '.$month_text.' '.$interval->format('%d').' '.$day_text;
     //                 }else{
					//   $pass_validity=$interval->format('%m').' '.$month_text;
     //                 }
					// }else{
					// 	$pass_validity=($interval->format('%d')>1) ? "Days" :"Day";
					// 	$pass_validity=$interval->format('%d').' '.$pass_validity;
					// }
					$expire_date = ($passexpiry * 24 * 60 * 60) + $purchase_date;
					$passData =   array(
						                'business_id'   =>$business_id,
										'user_id'  		=>$usid,
										'pass_name'     =>$pass_name,
										'pass_id'   	=>$time,
										'pass_validity' =>$passexpiry,
										'purchase_date' =>$purchase_date,
										'pass_end_date' =>$expire_date,
										'amount'   	    =>$amount,
										//'service_id'   	=>$service_id,
										'service_type'  =>$service_type,
										'class_type'   	=>$class_type,
										'pass_type'   	=>$pass_type,
										'pass_type_subcat'=>$pass_sub_type,
										'status'        =>"Active",
										'tax1'          =>$tax1,
										'tax2'          =>$tax2,
										'tax1_rate'	=>($tax1_rate)?$tax1_rate:0,
										'tax2_rate'	=>($tax2_rate)?$tax2_rate:0,
										'tax1_rate_percentage'=>($tax2_rate)?$tax2_rate:0,
										'tax2_rate_percentage'=>($tax2_rate)?$tax2_rate:0,
										'is_client_visible'          =>$is_client_visible,
										'is_one_time_purchase'          =>$is_one_time_purchase,
										'description'          =>$description,
										'notes'          =>$notes,
										'is_recurring_billing'          =>$is_recurring_billing,
										'billing_start_from'          =>$billing_start_from,
										'age_restriction'          =>$age_restriction,
										'age_over_under'          =>$age_over_under,
										'create_dt'   	=>$time,
										'update_dt'   	=>$time
					                   );
					//print_r($passData);die;
					$business_passes= $this->dynamic_model->insertdata('business_passes',$passData);
					if($business_passes)
			        {
						$insertClassPass = array();
						$classIds = $this->input->post('class_details');

						if (is_array($classIds) && count($classIds) > 0) {
							foreach($classIds as $cl) {
								array_push($insertClassPass, array(
									'user_id'		=>	$usid,
									'business_id'	=> $business_id,
									'class_id'		=>	decode($cl),
									'pass_id'		=>	$business_passes,
									'create_dt'		=>	$time,
									'update_dt'		=>	$time,
								));
							}
							if (!empty($insertClassPass)) {
								$this->db->insert_batch('business_passes_associates', $insertClassPass);
							}
						}
						$arg['status']    = 1;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line']= __line__;
						$arg['message']   = $this->lang->line('pass_save_succ');
					 	$arg['data']      = [];
			        }else{
			        	$arg['status']     = 0;
			            $arg['error_code']  = HTTP_NOT_FOUND;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('server_problem');
			        }

				}
			}
	    }

	 echo json_encode($arg);
    }
    /****************Function Get passes list**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : passes_list
     * @description     : list of passes for class
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function passes_list()
	{
		$arg = array();
		$userdata = web_checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
	      $_POST = json_decode(file_get_contents("php://input"), true);
		  if($_POST)
		  {
		    $this->form_validation->set_rules('page_no', 'Page No', 'required|numeric',array(
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
				$page_no= (!empty($this->input->post('page_no'))) ? $this->input->post('page_no') : "1";
				$page_no= $page_no-1;
				$limit    = 200; //config_item('page_data_limit');
				//$limit    = 2;
				$offset = $limit * $page_no;
				/*$where=array("business_id"=>decode($userdata['data']['business_id']),"status"=>"Active");*/

				$business_id = decode($userdata['data']['business_id']);
				$data = "business_passes.*, manage_pass_type.pass_days";
			    $condition = "business_passes.business_id='".$business_id."' AND business_passes.status='Active'";
                $on = 'manage_pass_type.id = business_passes.pass_type_subcat';
			    $pass_data = $this->dynamic_model->getTwoTableData($data, 'business_passes', 'manage_pass_type', $on, $condition, $limit, $offset, "business_passes.create_dt","DESC");

				/*$pass_data = $this->dynamic_model->getdatafromtable('business_passes',$where,"*",$limit, $offset,'create_dt');*/
				if(!empty($pass_data)){
				    foreach($pass_data as $value)
		            {
		            	$passesdata['pass_id']      = encode($value['id']);
                        $passesdata['pass_for']    = $value['pass_for'];
		            	$passesdata['pass_name']    = $value['pass_name'];
		            	$passesdata['pass_days']    = $value['pass_days'];
		            	$passesdata['pass_status']    = $value['status'];

		            	$passesdata['passes_id']    = $value['pass_id'];
		            	$passesdata['pass_validity']= $value['pass_validity'];
		            	$passesdata['purchase_date']=  date("d M Y ",$value['purchase_date']);
		            	$passesdata['pass_end_date']=  date("d M Y ",$value['pass_end_date']);
		            	$passesdata['purchase_date_utc']= $value['purchase_date'];
		            	$passesdata['pass_end_date_utc']= $value['pass_end_date'];
		            	$passesdata['class_type']   =  get_categories($value['class_type']);

		            	$passType  = (!empty($value['pass_type'])) ? $value['pass_type'] : '';
						$pass_type_subcat  = (!empty($value['pass_type_subcat'])) ? $value['pass_type_subcat'] : '';
						$pass_type=get_passes_type_name($passType,$pass_type_subcat);

						$passesdata['pass_type']=get_passes_type_name($passType);
						$passesdata['pass_sub_type']=$pass_type;
						$passesdata['amount']=$value['amount'];
						$passesdata['tax1']=$value['tax1'];
						$passesdata['tax2']=$value['tax2'];
						$passesdata['tax1_rate']=$value['tax1_rate'];
						$passesdata['tax2_rate']=$value['tax2_rate'];
						$passesdata['is_client_visible']=$value['is_client_visible'];
						$passesdata['is_one_time_purchase']=$value['is_one_time_purchase'];
						$passesdata['description']=$value['description'];
						$passesdata['notes']=$value['notes'];
						$passesdata['is_recurring_billing']=$value['is_recurring_billing'];
						$passesdata['billing_start_from']=$value['billing_start_from'];
						$passesdata['age_restriction']=$value['age_restriction'];
						$passesdata['age_over_under']=$value['age_over_under'];

		            	$passesdata['create_dt']    =  date("d M Y ",$value['create_dt']);
		            	$passesdata['create_dt_utc'] = $value['create_dt'];
		            	$response[]	                = $passesdata;
		            }
					$arg['status']     = 1;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = $response;
					$arg['message']    = $this->lang->line('record_found');
				}else{
					$arg['status']     = 0;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = array();
				 	$arg['message']    = $this->lang->line('record_not_found');
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
	public function instructor_list_details($business_id='',$service_type='',$service_id='',$search_val='',$limit = "1",$offset= "0",$search_skill=""){
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
		}
		//$category='1,2,3,4,5,6,7,8';
		//if services skill search
		$where='';
	    if(!empty($search_skill) && $service_type==3){

	    	$search_skills = explode(',', $search_skill);
				foreach($search_skills as $keyids) {
				$close = '';
				$start = '';
				$operator = 'OR';
				$operatorstart = '';
				if($keyids == end($search_skills)){
				$close = ')';
				$operator = '';
				}
				if($keyids == reset($search_skills)){
				$start = '(';
				$operatorstart = '';
				}
				$where .= " $operatorstart$start FIND_IN_SET('$keyids', skill) $operator$close";
				}
		}else{
			//skill according to service type
				$where='';
				if($category){
				$catids = explode(',', $category);
				foreach($catids as $keyids) {
				$close = '';
				$start = '';
				$operator = 'OR';
				$operatorstart = '';
				if($keyids == end($catids)){
				$close = ')';
				$operator = '';
				}
				if($keyids == reset($catids)){
				$start = '(';
				$operatorstart = '';
				}
				$where .= " $operatorstart$start FIND_IN_SET('$keyids', skill) $operator$close";
				}
				}
	        }
        if($where){
        $condition1="$where AND status='Active'";
        }else{
    	$condition1="status='Active'";
        }
        //echo $where;die;
        $instructor_info =  $this->studio_model->get_instructor_details($business_id,$instructor_ids,$condition1,$search_val,$limit,$offset);

		if($instructor_info){
			foreach($instructor_info as $value){
					$instructordata['id']     = encode($value['id']);
	            	$instructordata['name']   = ucwords($value['name']);
	            	$instructordata['lastname']= ucwords($value['lastname']);
	            	$instructordata['about']    = $value['about'];
	            	$instructordata['profile_img'] = $url.$value['profile_img'];
	            	$instructordata['availability_status']= $value['availability_status'];

	            	$instructordata['skill'] = (!empty($value['skill'])) ? get_categories($value['skill']) : "";
	            	$instructordata['services'] =  "Zumba,Yoga,Gym,Fitness";
	            	$instructordata['experience'] =  (!empty($value['total_experience'])) ? $value['total_experience'] : "";
	            	$instructordata['appointment_fees_type'] =   (!empty($value['appointment_fees_type'])) ? $value['appointment_fees_type'] : "";
	            	$instructordata['appointment_fees'] =   (!empty($value['appointment_fees'])) ? $value['appointment_fees'] : "";
	            	$response[]	                 = $instructordata;
			}
		}
		return $response;
	}
    /****************Function Add business classes**********
     * @type            : Function
     * @Author          : Arpit
     * @function name   : add_classes
     * @description     : add  class like yoga
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function add_class()
	{
	   $arg   = array();
	   $userdata = web_checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
			    $this->form_validation->set_rules('class_name','Calss name', 'required|trim', array( 'required' => $this->lang->line('class_name_required')));
			     $this->form_validation->set_rules('class_type','Class Type', 'required|trim', array( 'required' => $this->lang->line('class_type_required')));
               	if ($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$from_time=$to_time=$duration='';
					$userdata = web_checkuserid();
					$usid =decode($userdata['data']['id']);
					$time=time();
					$class_name       = $this->input->post('class_name');
				    $class_type       = decode($this->input->post('class_type'));
					$duration         = $this->input->post('duration');
					$description      = $this->input->post('description');
					//get business Id
					$where = array('status' => 'Active','user_id'=>$usid);
			        $business_data = $this->dynamic_model->getdatafromtable('business',$where,'id');
			        $business_id=(!empty($business_data[0]['id'])) ? $business_data[0]['id'] : 0;
					$classData =   array(
						'business_id'   =>$business_id,
						'user_id'  		=>$usid,
						'class_name'    =>$class_name,
						'duration'      =>$duration,
						'description'   =>$description,
						'class_type'   	=>$class_type,
						'status'   	    =>"Active",
						'create_dt'   	=>$time,
						'update_dt'   	=>$time
					);
					$business_class= $this->dynamic_model->insertdata('business_class',$classData);
					if($business_class)
			        {
						/* Pass Add */

						$insertClassPass = array();

						$passIds = $this->input->post('pass_id');

						if (is_array($passIds) && count($passIds) > 0) {
							foreach($passIds as $cl) {
								array_push($insertClassPass, array(
									'user_id'		=>	$usid,
									'business_id'	=>  $business_id,
									'class_id'		=>	$business_class,
									'pass_id'		=>	decode($cl),
									'create_dt'		=>	$time,
									'update_dt'		=>	$time,
								));
							}
							if (!empty($insertClassPass)) {
								$this->db->insert_batch('business_passes_associates', $insertClassPass);
							}
						}

						$arg['status']    = 1;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line']= __line__;
					 	$arg['message']   = $this->lang->line('class_save_succ');
					 	$arg['data']      = [];
			        }else{
			        	$arg['status']     = 0;
			            $arg['error_code']  = HTTP_NOT_FOUND;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('server_problem');
			        }

				}
			}
	    }
	 echo json_encode($arg);
    }
    /****************Function Add business classes**********
     * @type            : Function
     * @Author          : Arpit
     * @function name   : update_classes
     * @description     : update class like yoga
     * @param           : null
     * @return          : null
     * ********************************************************** */
    public function update_class()
    {
        $arg   = array();
        $userdata = web_checkuserid();
        if($userdata['status'] != 1){
            $arg = $userdata;
        } else {
            $_POST = json_decode(file_get_contents("php://input"), true);
            if($_POST)
            {
                $this->form_validation->set_rules('class_id','Class Id', 'required|trim', array( 'required' => $this->lang->line('class_id_required')));
                $this->form_validation->set_rules('description','Description','required',array( 'required' => $this->lang->line('description_required')));
                if ($this->form_validation->run() == FALSE)
                {
                    $arg['status']  = 0;
                    $arg['error_code'] = 0;
                    $arg['error_line']= __line__;
                    $arg['message'] = get_form_error($this->form_validation->error_array());
                } else {
                    $userdata = web_checkuserid();
                    $usid =decode($userdata['data']['id']);
                    $time=time();
                    $where = array('status' => 'Active','user_id'=>$usid);
                    $business_data = $this->dynamic_model->getdatafromtable('business',$where,'id');
                    $business_id=(!empty($business_data[0]['id'])) ? $business_data[0]['id'] : 0;
                    $class_id = decode($this->input->post('class_id'));
                    $description = trim($this->input->post('description'));
                    $where = array('id' => $class_id, 'business_id' => $business_id, 'user_id' => $usid);
                    $update = array('description' => $description);
                    $status = $this->dynamic_model->updateRowWhere('business_class', $where, $update);
					$arg['status']    = 1;
					$arg['error_code'] = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['message']   = 'Class update successfully.';
					$arg['data']      = array();
					/* if ($status) {
                        $arg['status']    = 1;
                        $arg['error_code'] = HTTP_OK;
                        $arg['error_line']= __line__;
                        $arg['message']   = 'Class update successfully.';
                        $arg['data']      = array();
                    } else {
                        $arg['status']     = 0;
                        $arg['error_code']  = HTTP_NOT_FOUND;
                        $arg['error_line']= __line__;
                        $arg['data']       = array();
                        $arg['message']    = $this->lang->line('server_problem');
                    } */
                }
            }
        }
        echo json_encode($arg);
    }
      /****************Function Avalible Instructor**********
     * @type            : Function
     * @Author          : Arpit
     * @function name   : avalible_instructor
     * @description     : avalible instructor
     * @param           : null
     * @return          : null
     * ********************************************************** */
    public function avalible_instructor_old()
	{
	   $arg   = array();
	   $userdata = web_checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
			    $this->form_validation->set_rules('service_id','Service Id','required|trim', array( 'required' => $this->lang->line('service_id_required')));
			    $this->form_validation->set_rules('service_type','Service Type','required|trim', array( 'required' => $this->lang->line('service_type_required')));
			    $this->form_validation->set_rules('date','Date','required|trim', array( 'required' => $this->lang->line('date_required')));
			   if($_POST['service_type'] !='3'){
			    $this->form_validation->set_rules('from_time','From time','required|trim', array( 'required' => $this->lang->line('from_time_required')));
			    $this->form_validation->set_rules('to_time','To time','required|trim', array( 'required' => $this->lang->line('to_time_required')));
			    }else{
			    	$this->form_validation->set_rules('slot_id','Slot Id','required|trim', array( 'required' => $this->lang->line('slot_id_required')));
			    }
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
					$response=array();
					$usid =decode($userdata['data']['id']);
					$business_id =decode($userdata['data']['business_id']);
					$service_type=$this->input->post('service_type');
					$service_id=decode($this->input->post('service_id'));
					$service_date=$this->input->post('date');
					$class_from_time=$this->input->post('from_time');
					$class_to_time=$this->input->post('to_time');
					$slot_id=$this->input->post('slot_id');
					$service_date = date("Y-m-d",$service_date);
					$todaydate = date("Y-m-d",$time);

                    $instructor_ids = $this->studio_model->get_instructor_ids($business_id);
                    $where="business_id=".$business_id." AND status='Active' AND DATE(FROM_UNIXTIME(start_date_utc))>='".$todaydate."'";
                    $schedule_data = $this->dynamic_model->getWhereInData('instructor_schedule','user_id',$instructor_ids,$where);
                    //$business_data = $this->dynamic_model->getdatafromtable('instructor_schedule',$where);
                    //print_r($business_data);die;
                    //find class detail
                    if($service_type==1){
                    	$where1 = array('id'=>$service_id,'business_id'=>$business_id);
				        $service_data = $this->dynamic_model->getdatafromtable('business_class',$where1);
                    }elseif($service_type==2){
                    	$where1 = array('id'=>$service_id,'business_id'=>$business_id);
				        $service_data = $this->dynamic_model->getdatafromtable('business_workshop',$where1);
                    }else{
                         $where1 = array('id'=>$service_id,'business_id'=>$business_id);
				         $service_data = $this->dynamic_model->getdatafromtable('service',$where1);
                    }

				    //print_r($business_data);die;
					if(!empty($schedule_data)){
						foreach($schedule_data as $key => $value){
				            $instuctor_start_date = $value['start_date_utc'];
				            $instuctor_from_time  = $value['from_time_utc'];
				            $instuctor_to_time    = $value['to_time_utc'];
                             // echo $value['from_time'].'=='.$value['to_time'].'=='.$slot_data[0]['slot_time_from'].'=='.$slot_data[0]['slot_time_to'];die;
					       // if($instuctor_start_date ==$class_date){
					       // if($instuctor_from_time <= $class_from_time && $instuctor_to_time >= $class_to_time){
			                $where2 = array('id'=>$value['user_id'],'status'=>'Active');
				            $user_data = $this->dynamic_model->getdatafromtable('user',$where2);
			                $response[]=array(
			                 	'id'=>encode($user_data[0]['id']),
			                 	'name'=>$user_data[0]['name'].' '.$user_data[0]['lastname'],
			                 	'profile_img' =>base_url().'/uploads/user/'. $user_data[0]['profile_img']);
			                 //}
			              //}
						}
					}
					if($response)
			        {
						$arg['status']    = 1;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line']= __line__;
					 	$arg['data']      = $response;
					 	$arg['message']   = $this->lang->line('record_found');
			        }else{
			        	$arg['status']     = 0;
			            $arg['error_code']  = HTTP_NOT_FOUND;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('instructor_not_avalible');
			        }
				}
			}
	    }
	 echo json_encode($arg);
    }
    public function avalible_instructor()
	{

	   $arg   = array();
	   $userdata = web_checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{

			    $this->form_validation->set_rules('service_id','Service Id','required|trim', array( 'required' => $this->lang->line('service_id_required')));
			    $this->form_validation->set_rules('service_type','Service Type','required|trim', array( 'required' => $this->lang->line('service_type_required')));
			    //$this->form_validation->set_rules('date','Date','required|trim', array( 'required' => $this->lang->line('date_required')));
			   if($_POST['service_type'] !='3'){
			    $this->form_validation->set_rules('from_time','From time','required|trim', array( 'required' => $this->lang->line('from_time_required')));
			    $this->form_validation->set_rules('to_time','To time','required|trim', array( 'required' => $this->lang->line('to_time_required')));
			    $this->form_validation->set_rules('day_id','Day Id','required', array( 'required' => $this->lang->line('day_id_required')));
			    }else{
			    	$this->form_validation->set_rules('slot_id','Slot Id','required|trim', array( 'required' => $this->lang->line('slot_id_required')));
			    }
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
					$response=$userid=array();
					$usid =decode($userdata['data']['id']);
					$business_id =decode($userdata['data']['business_id']);
					$service_type=$this->input->post('service_type');
					$service_id=decode($this->input->post('service_id'));
					//$service_date=$this->input->post('date');
					$class_from_time=$this->input->post('from_time');
					$class_to_time=$this->input->post('to_time');
					$day_id=$this->input->post('day_id');
					$slot_id=$this->input->post('slot_id');
					//$service_date = date("Y-m-d",$service_date);
					$todaydate = date("Y-m-d",$time);

                    $instructor_ids = $this->studio_model->get_instructor_ids($business_id);
                   // print_r($instructor_ids); die;

					//$where="business_id=".$business_id." AND status='Active' AND DATE(FROM_UNIXTIME(start_date_utc))>='".$todaydate."'";
                    // $schedule_data = $this->dynamic_model->getWhereInData('instructor_schedule','user_id',$instructor_ids,$where);
                     $where="day_status = 1 AND time_slot_status = 1 AND business_id=".$business_id." AND day_id=".$day_id."";
                    //$schedule_data = $this->dynamic_model->getWhereInData('instructor_time_slot','user_id',$instructor_ids,$where);
                    $schedule_data= $this->dynamic_model->getdatafromtable('instructor_time_slot',$where);
					//
                    if ($this->input->post('demo')) {
						if(!empty($schedule_data)){
							$arg['class_from_time'] = $class_from_time;
							$arg['class_to_time'] = $class_to_time;
							$arg['schedule_data'] = $schedule_data;
							echo json_encode($arg); exit;
						}

					}
					$class_from_time = strtotime($class_from_time);
					$class_to_time = strtotime($class_to_time);
					if(!empty($schedule_data)){

						foreach($schedule_data as $key => $value){

							$time_slot_data= $this->dynamic_model->getdatafromtable('business_time_slote',array("business_id"=>$business_id,"day_id"=>$value['day_id'],"id"=>$value['time_slot_id']));
						    $instuctor_from_time = strtotime($time_slot_data[0]['time_slote_from']);
				            $instuctor_to_time = strtotime($time_slot_data[0]['time_slote_to']);
				            //if($class_from_time >= $instuctor_from_time && $class_to_time <= $instuctor_to_time){
								if ($value['day_status'] == "1" && $value['time_slot_status'] == "1") {
									$userid[]= $value['user_id'];
								}
							//}
						}



							if(!empty($userid)){

				                $user_data = $this->studio_model->get_instructor_data($userid);

					            if(!empty($user_data)){
				                    foreach ($user_data as $key => $value1) {
										if(!empty($instructor_ids)) {
											if (in_array($value1['id'], $instructor_ids)) {
												$response[]=array(
													'id'=>encode($value1['id']),
													'name'=>$value1['name'].' '.$value1['lastname'],
													'profile_img' =>base_url().'/uploads/user/'. $value1['profile_img']
											   	);
											}

										}
				                    }
				                }
			               }
					}
					if($response)
			        {
						$arg['status']    = 1;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line']= __line__;
					 	$arg['data']      = $response;
					 	$arg['message']   = $this->lang->line('record_found');
			        }else{
			        	$arg['status']     = 0;
			            $arg['error_code']  = HTTP_NOT_FOUND;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('instructor_not_avalible');
			        }
				}
			}
	    }
	 echo json_encode($arg);
    }

    /****************Function Get passes according to category*********
     * @type            : Function
     * @Author          : Arpit
     * @function name   : passes_list
     * @description     : list of passes for class
     * @param           : null
     * @return          : null
     * ********************************************************** */
    public function get_workshop_passes() {
        $arg = array();
        $userdata = web_checkuserid();
        if($userdata['status'] != 1){
            $arg = $userdata;
        }
        else
        {
            $usid =decode($userdata['data']['id']);
            $business_id =decode($userdata['data']['business_id']);
            $pass_data = $this->dynamic_model->getdatafromtable('business_passes',
                array(
                    'business_id'   =>   $business_id,
                    'service_type'  =>   2,
                    'status'        =>  'Active'
                )
            );
            if(!empty($pass_data)){
                foreach($pass_data as $value)
                {
                    $is_added   = 0;
                    /*if (!empty($attachedPass)) {
                        if (in_array($value['id'], $attachedPass)) {
                            $is_added   = 1;
                        }
                    }*/
                    $passesdata['pass_id']      = encode($value['id']);
                    $passesdata['is_added']     = $is_added;
                    $passesdata['pass_name']    = $value['pass_name'];
                    $passesdata['amount']    = $value['amount'];
                    $passesdata['pass_id']      = encode($value['id']);
                    $passesdata['pass_name']    = $value['pass_name'];
                    $passesdata['passes_id']    = $value['pass_id'];
                    $passesdata['pass_validity']= $value['pass_validity'];
                    $passesdata['purchase_date']=  date("d M Y ",$value['purchase_date']);
                    $passesdata['pass_end_date']=  date("d M Y ",$value['pass_end_date']);
                    $passesdata['purchase_date_utc']= $value['purchase_date'];
                    $passesdata['pass_end_date_utc']= $value['pass_end_date'];
                    $passesdata['class_type']   =  get_categories($value['class_type']);

                    $passType  = (!empty($value['pass_type'])) ? $value['pass_type'] : '';
                    $pass_type_subcat  = (!empty($value['pass_type_subcat'])) ? $value['pass_type_subcat'] : '';
                    $pass_type=get_passes_type_name($passType,$pass_type_subcat);

                    $passesdata['pass_type']=get_passes_type_name($passType);
                    $passesdata['pass_sub_type']=$pass_type;
                    $passesdata['tax1']=$value['tax1'];
                    $passesdata['tax2']=$value['tax2'];
                    $passesdata['create_dt']    =  date("d M Y ",$value['create_dt']);
                    $passesdata['create_dt_utc'] = $value['create_dt'];
                    $response[]	                = $passesdata;

                }
                $arg['status']     = 1;
                $arg['error_code']  = HTTP_OK;
                $arg['error_line']= __line__;
                $arg['data']       = $response;
                $arg['message']    = $this->lang->line('record_found');
            }else{
                $arg['status']     = 0;
                $arg['error_code']  = HTTP_OK;
                $arg['error_line']= __line__;
                $arg['data']       = array();
                $arg['message']    = $this->lang->line('record_not_found');
            }
        }
        echo json_encode($arg);
    }
	public function get_passes_according_to_category()
	{
		$arg = array();
		$userdata = web_checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
	      $_POST = json_decode(file_get_contents("php://input"), true);
		  if($_POST)
		  {
			$this->form_validation->set_rules('service_id','Service Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
		    $this->form_validation->set_rules('service_type','Service Type','required|trim', array( 'required' => $this->lang->line('service_type_required')));
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
			   $service_id= decode($this->input->post('service_id'));
			   $service_type= $this->input->post('service_type');
               //get class type
               	$condition = array('id'=>$service_id);
			  	if($service_type==1) {

		        	$business_data= $this->dynamic_model->getdatafromtable('business_class',$condition);
		        	$class_type=!empty($business_data) ? $business_data[0]['class_type']:'';
                 	$where = array(
                 		"business_passes.business_id"	=>	decode($userdata['data']['business_id']),
                 		"business_passes.status"	=>	'Active'
                 	);

				} elseif($service_type==2) {

		        	$business_data= $this->dynamic_model->getdatafromtable('business_workshop',$condition);
		        	$class_type=!empty($business_data) ? $business_data[0]['workshop_type']:'';
		        	$where=array("business_passes.business_id"=>decode($userdata['data']['business_id']),"business_passes.class_type"=>$class_type, "business_passes.service_id"=>'0',"business_passes.service_type"=>2);

				}

				$attachedPass = array();
				if($service_type == 1) {
					$added_passes   = $this->db->query("SELECT GROUP_CONCAT(DISTINCT pass_id SEPARATOR ',') as pass_id FROM `business_passes_associates` where business_id = ".decode($userdata['data']['business_id'])." and class_id = ".$service_id." GROUP BY class_id")->row_array();
					if (!empty($added_passes)) {
						$attachedPass = explode(',', $added_passes['pass_id']);
					}
				}
                //get passes according to class or workshop category

                $data = "business_passes.*, manage_pass_type.pass_days";
			    // $condition = "business_passes.business_id='".$business_id."' AND business_passes.status='Active'";
                $on = 'manage_pass_type.id = business_passes.pass_type_subcat';
			    $pass_data = $this->dynamic_model->getTwoTableData($data, 'business_passes', 'manage_pass_type', $on, $where, '', '', "business_passes.create_dt","DESC");

				// $pass_data = $this->dynamic_model->getdatafromtable('business_passes', $where);
// print_r($this->db->last_query()); die;
				//print_r($pass_data);die;
				if(!empty($pass_data)){
				    foreach($pass_data as $value)
		            {
						$is_added   = 0;
						if (!empty($attachedPass)) {
							if (in_array($value['id'], $attachedPass)) {
								$is_added   = 1;
							}
						}
						$passesdata['pass_id']      = encode($value['id']);
						$passesdata['is_added']     = $is_added;
		            	$passesdata['pass_name']    = $value['pass_name'];
		            	$passesdata['pass_days']    = $value['pass_days'];
		            	$passesdata['amount']    = $value['amount'];
		            	$passesdata['pass_id']      = encode($value['id']);
		            	$passesdata['pass_name']    = $value['pass_name'];
		            	$passesdata['passes_id']    = $value['pass_id'];
		            	$passesdata['pass_validity']= $value['pass_validity'];
		            	$passesdata['purchase_date']=  date("d M Y ",$value['purchase_date']);
		            	$passesdata['pass_end_date']=  date("d M Y ",$value['pass_end_date']);
		            	$passesdata['purchase_date_utc']= $value['purchase_date'];
		            	$passesdata['pass_end_date_utc']= $value['pass_end_date'];
		            	$passesdata['class_type']   =  get_categories($value['class_type']);

		            	$passType  = (!empty($value['pass_type'])) ? $value['pass_type'] : '';
						$pass_type_subcat  = (!empty($value['pass_type_subcat'])) ? $value['pass_type_subcat'] : '';
						$pass_type=get_passes_type_name($passType,$pass_type_subcat);

						$passesdata['pass_type']=get_passes_type_name($passType);
						$passesdata['pass_sub_type']=$pass_type;
						$passesdata['tax1']=$value['tax1'];
						$passesdata['tax2']=$value['tax2'];
		            	$passesdata['create_dt']    =  date("d M Y ",$value['create_dt']);
		            	$passesdata['create_dt_utc'] = $value['create_dt'];
		            	$response[]	                = $passesdata;

		            }
					$arg['status']     = 1;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = $response;
					$arg['message']    = $this->lang->line('record_found');
				}else{
					$arg['status']     = 0;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = array();
				 	$arg['message']    = $this->lang->line('record_not_found');
				}
		    }
		  }
		}

	   echo json_encode($arg);
	}
    /****************Function class scheduling**********
     * @type            : Function
     * @Author          : Arpit
     * @function name   : class_scheduling
     * @description     : class scheduling
     * @param           : null
     * @return          : null
     * ********************************************************** */

	public function class_scheduling_old()
	{

	   $arg   = array();
	   $userdata = web_checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
				//print_r($_POST); die;
			    $this->form_validation->set_rules('class_id','Class Id', 'required|trim', array( 'required' => $this->lang->line('class_id_required')));
			    // $this->form_validation->set_rules('user_id','User Id', 'required|trim', array( 'required' => $this->lang->line('user_id_required')));
			     $this->form_validation->set_rules('start_date','Start date','required|trim', array( 'required' => $this->lang->line('start_date_required')));
			     //$this->form_validation->set_rules('end_date','End date','required|trim', array( 'required' => $this->lang->line('end_date_required')));
			    //$this->form_validation->set_rules('from_time','From time','required|trim', array( 'required' => $this->lang->line('from_time_required')));
			    //$this->form_validation->set_rules('to_time','To time','required|trim', array( 'required' => $this->lang->line('to_time_required')));
                $this->form_validation->set_rules('class_location','Pass Validity', 'required|trim', array('required' => $this->lang->line('class_location_required')));
                $this->form_validation->set_rules('passes_id','Passes Id', 'required|trim', array('required' => $this->lang->line('passesid_required')));
                //$this->form_validation->set_rules('time_slot','time slot', 'required');
                //$this->form_validation->set_rules('day_id','Day Id', 'required|trim', array('required' => $this->lang->line('day_id_required')));
                $this->form_validation->set_rules('class_status','Class status', 'required|trim', array('required' => $this->lang->line('class_status_required')));
                $this->form_validation->set_rules('class_repeat_times','Class repeat times', 'required|trim', array('required' => $this->lang->line('class_repeat_times_required')));
                $this->form_validation->set_rules('class_days_prior_signup','Class days prior signup', 'required|trim', array('required' => $this->lang->line('class_days_prior_signup_required')));
                $this->form_validation->set_rules('class_waitlist_overflow','Class waitlist overflow', 'required|trim', array('required' => $this->lang->line('class_waitlist_overflow_required')));
				if ($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{

					$from_time=$to_time=$duration='';
					$userdata = web_checkuserid();
					$usid =decode($userdata['data']['id']);
					$business_id =decode($userdata['data']['business_id']);
					//echo encode($this->input->post('class_id'))."--";
					//echo encode($this->input->post('class_location'))."--";
					//echo encode($this->input->post('passes_id')); die;
					$time=time();
					$class_id    = decode($this->input->post('class_id'));
					//$user_id     = $this->input->post('user_id');
					//$user_id     = !empty($user_id) ? decode($user_id):'0';

					$location    = decode($this->input->post('class_location'));
					$class_date  = $this->input->post('class_date');
					$time_slot  = $this->input->post('time_slot');
					//$from_time   = $this->input->post('from_time');
					//$to_time     = $this->input->post('to_time');
					$start_date  = date("Y-m-d",$this->input->post('start_date'));
					//$end_date    = date("Y-m-d",$this->input->post('end_date'));
					//$day_id      = $this->input->post('day_id');
					$class_status      = $this->input->post('class_status');
					$class_repeat_times      = $this->input->post('class_repeat_times');
					$class_days_prior_signup      = $this->input->post('class_days_prior_signup');
					$class_waitlist_overflow      = $this->input->post('class_waitlist_overflow');
					$passes_id   = multiple_decode_ids($this->input->post('passes_id'),1);

					 //print_r($passes_id);
					//get class data
					$where = array('id'=>$class_id,'business_id'=>$business_id);
				    $class_data = $this->dynamic_model->getdatafromtable('business_class',$where);
				    if(!empty($class_data)){
				    $duration=!empty($class_data[0]['duration']) ? $class_data[0]['duration']:'';
                    //get locations
				    $where1 = array('business_id'=>$business_id,'id'=>$location);
				    $room_location = $this->dynamic_model->getdatafromtable('business_location',$where1);
				    $location_name=!empty($room_location[0]['location_name']) ? $room_location[0]['location_name']:'';
				    $capacity=!empty($room_location[0]['capacity']) ? $room_location[0]['capacity']:'';
					//$classdate=date("Y-m-d",$start_date);
					$cal_send  =  round($class_repeat_times/count($time_slot));
					$cal_end  =  fmod($class_repeat_times,count($time_slot));
					$total_days = ($cal_send * 7) + $cal_end;
					$end_date  = date('Y-m-d', strtotime($start_date. ' + '.$total_days.' days'));
					$classData =   array(
										//'from_time'   	=>$from_time,
										//'to_time'       =>$to_time,
										//'instructor_id' =>$user_id,
										'capacity'   	=>$capacity,
										'location'   	=>$location_name,
										//'status'   	    =>"Active",
										'start_date'   	=>$start_date,
										'end_date'   	=>$end_date,
										//'day_id'   		=>$day_id,
										'status'   		=> 'Active',
										'class_repeat_times'   		=>$class_repeat_times,
										'class_days_prior_signup'   		=>$class_days_prior_signup,
										'class_waitlist_overflow'   		=>$class_waitlist_overflow,
										//'end_date'   	=>$end_date,
										'update_dt'   	=>$time
					                   );
							$business_class= $this->dynamic_model->updateRowWhere('business_class',$where,$classData);
							if($business_class){
								if(!empty($time_slot)){
					        		foreach ($time_slot as $value) {
					        			$time_slot_array = array(
					        								"business_id"=>$business_id,
					        								"instructor_id"=>decode($value['instructor_id']),
					        								"day_id"=>$value['day_id'],
					        								"from_time"=>($value['from_time'])?$value['from_time']:'',
					        								"to_time"=>($value['to_time'])?$value['to_time']:'',
					        								"class_id"=>$class_id,
                                                            "status" => ($class_status == 'Active') ? 'Active' : 'Deactive',
					        								);
					        			$this->dynamic_model->insertdata('class_scheduling_time',$time_slot_array);
					        		}

					        		$where2=array("business_id"=>$business_id);
									$business_passes= $this->dynamic_model->getWhereInData('business_passes','id',$passes_id,$where2);
									if(!empty($business_passes)){
										foreach ($business_passes as $value){
			                            $where3=array("id"=>$value['id'],"business_id"=>$business_id);
			                            $updateData= array(
											'service_id'   	=>$class_id,
											'service_type'  =>1,
											'update_dt'   	=>$time
						                   );
									   $this->dynamic_model->updateRowWhere('business_passes',$where3,$updateData);
										}
									}
					        		$arg['status']     = 1;
									$arg['error_code']  = HTTP_OK;
									$arg['error_line']= __line__;
									$arg['message']    = 'Class scheduled successfully.';

					        	}
							}
			        }else{
			        	$arg['status']     = 0;
			            $arg['error_code']  = HTTP_NOT_FOUND;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('record_not_found');
			        }
				}
			}
	    }
	 echo json_encode($arg);
	}
	public function class_scheduling()
	{
		$arg   = array();
		$userdata = web_checkuserid();
	   	if($userdata['status'] != 1){
			$arg = $userdata;
		} else {
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST) {

				$this->form_validation->set_rules('class_id','Class Id', 'required|trim', array( 'required' => $this->lang->line('class_id_required')));
			    $this->form_validation->set_rules('start_date','Start date','required|trim', array( 'required' => $this->lang->line('start_date_required')));
			    $this->form_validation->set_rules('class_location','Location','required|trim', array( 'required' => $this->lang->line('location_name_req')));
				$this->form_validation->set_rules('passes_id','Passes Id', 'required|trim', array('required' => $this->lang->line('passesid_required')));
				$this->form_validation->set_rules('class_status','Class status', 'required|trim', array('required' => $this->lang->line('class_status_required')));
                $this->form_validation->set_rules('class_repeat_times','Class repeat times', 'required|trim', array('required' => $this->lang->line('class_repeat_times_required')));
                $this->form_validation->set_rules('class_days_prior_signup','Class days prior signup', 'required|trim', array('required' => $this->lang->line('class_days_prior_signup_required')));
                $this->form_validation->set_rules('class_waitlist_overflow','Class waitlist overflow', 'required|trim', array('required' => $this->lang->line('class_waitlist_overflow_required')));

				if ($this->input->post('class_waitlist_overflow') && $this->input->post('class_waitlist_overflow') == 'yes') {
					$this->form_validation->set_rules('class_overflow_count','Class waitlist overflow count', 'required|trim', array('required' => 'Overflow Count is required'));
				}

				$this->form_validation->set_rules('start_time','Start Time', 'required|trim', array('required' => $this->lang->line('start_date_time_required')));
				$this->form_validation->set_rules('end_time','End Time', 'required|trim', array('required' => $this->lang->line('end_date_time_required')));
				$this->form_validation->set_rules('day_id','Day Id', 'required|trim', array('required' => $this->lang->line('day_id_required')));
				$this->form_validation->set_rules('instructor_id','Instructor Id', 'required|trim', array('required' => $this->lang->line('instructor_id_required')));

				if ($this->form_validation->run() == FALSE)
				{
					$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {
					$time_zone =  $this->input->get_request_header('Timezone', true);
					$time_zone =  $time_zone ? $time_zone : 'UTC';
					date_default_timezone_set($time_zone);

					$userdata = web_checkuserid();
					$usid =decode($userdata['data']['id']);
					$business_id =decode($userdata['data']['business_id']);
					$time=time();

					$class_id = decode($this->input->post('class_id'));
					$start_date = $this->input->post('start_date');
					$class_location = decode($this->input->post('class_location'));
					$passes_id = decode($this->input->post('passes_id'));
					$class_status = $this->input->post('class_status');
					$class_repeat_times = $this->input->post('class_repeat_times');
					$class_days_prior_signup = $this->input->post('class_days_prior_signup');
					$class_waitlist_overflow = $this->input->post('class_waitlist_overflow');
					$class_overflow_count = $this->input->post('class_overflow_count');
					$start_time = $this->input->post('start_time');
					$end_time = $this->input->post('end_time');
					$day_id = $this->input->post('day_id');
					$instructor_id = decode($this->input->post('instructor_id'));


					$passes_id   = multiple_decode_ids($this->input->post('passes_id'),1);

					$where = array('id'=>$class_id,'business_id'=>$business_id);
					$class_data = $this->dynamic_model->getdatafromtable('business_class',$where);

					if(!empty($class_data)){

						$duration=!empty($class_data[0]['duration']) ? $class_data[0]['duration']:'';
						$room_location = $this->dynamic_model->getdatafromtable('business_location', array('id' => $class_location, 'business_id'=>$business_id));
						$location_name=!empty($room_location[0]['location_name']) ? $room_location[0]['location_name']:'';
						$capacity=!empty($room_location[0]['capacity']) ? $room_location[0]['capacity']:'';

						$classData =   array(
							//'from_time'   	=>$from_time,
							//'to_time'       =>$to_time,
							//'instructor_id' =>$user_id,
							'capacity'   	=>	$capacity,
							'location'   	=>	$location_name,
							//'status'   	    =>"Active",
							'start_date'   	=> date("Y-m-d",$start_date),
							//'end_date'   	=>$end_date,
							//'day_id'   		=>$day_id,
							// 'status'   		=>$class_status,
							'class_repeat_times'   		=>$class_repeat_times,
							'class_days_prior_signup'   		=>$class_days_prior_signup,
							'class_waitlist_overflow'   		=>$class_waitlist_overflow,
							'class_waitlist_count'   		=>($class_overflow_count)?$class_overflow_count :0,
							//'end_date'   	=>$end_date,
							'update_dt'   	=>$time
						);

						$business_class = $this->dynamic_model->updateRowWhere('business_class',$where,$classData);

						if(true) {

							$total_days = ($class_repeat_times * 7) + 6;
							$end_date   = strtotime('+'.$total_days.' day', $start_date);

							$begin = new DateTime(date('Y-m-d', $start_date));
							$end   = new DateTime(date('Y-m-d', $end_date));

							$insertSlot = array();

							$dayOfWeek = date("l", $start_date);
							$counter = 1;
							for($i = $begin; $i <= $end; $i->modify('+1 day')){
								$current_date = $i->format("Y-m-d");
								$st = date('h:i:s A', $start_time);
								$rt = $current_date . ' ' . $st;
								$start_time = strtotime($rt);

								$en = date('h:i:s A', $end_time);
								$end_append = $current_date . ' ' . $en;
								$end_time = strtotime($end_append);

								$current_day = date('D', strtotime($current_date));

								if(strpos("Date ".$dayOfWeek, $current_day)) {
									if ($class_repeat_times >= $counter ) {
										array_push($insertSlot, array(
											'business_id'	=>	$business_id,
											'instructor_id' =>  $instructor_id,
											'day_id'        =>  $day_id,
											'location_id'	=>	$class_location,
											'scheduled_date'=>  $current_date,
											'from_time'		=>	$start_time,
											'to_time'		=>	$end_time,
											'class_id'		=>	$class_id,
											'status'		=>	($class_status == 'Active') ? 'Active' : 'Deactive'
										));
									}
									$counter++;
								}
							}

							/* $arg['status'] = 0;
							$arg['data'] = $insertSlot;
							echo json_encode($arg); exit; */

							if (!empty($insertSlot)) {

								$where2=array("business_id"=>$business_id);
								$business_passes= $this->dynamic_model->getWhereInData('business_passes','id',$passes_id,$where2);

								if(!empty($business_passes)) {

									$this->db->insert_batch('class_scheduling_time', $insertSlot);
									$insert_array = array();

									foreach ($business_passes as $value){
										array_push($insert_array, array(
											'user_id'       =>  $usid,
											'business_id'   =>  $business_id,
											'class_id'      =>  $class_id,
											'pass_id'       =>  $value['id'],
											'create_dt'     =>  $time,
											'update_dt'     =>  $time
										));

										$where3=array("id"=>$value['id'],"business_id"=>$business_id);
										$updateData= array(
											'service_id'   	=>$class_id,
											'service_type'  =>1,
											'update_dt'   	=>$time
										);
										$this->dynamic_model->updateRowWhere('business_passes',$where3,$updateData);
									}

									if (!empty($insert_array)) {
										$this->db->insert_batch('business_passes_associates', $insert_array);
									}

									$arg['status']     = 1;
									$arg['error_code']  = HTTP_OK;
									$arg['error_line']= __line__;
									$arg['error_line']=  $insertSlot;
									$arg['message']    = 'Class scheduled successfully.';

								} else {

									$arg['status']     = 0;
									$arg['error_code']  = HTTP_NOT_FOUND;
									$arg['error_line']= __line__;
									$arg['data']       = array();
									$arg['message']    = $this->lang->line('record_not_found');

								}

							} else {

								$arg['status']     = 0;
								$arg['error_code']  = HTTP_NOT_FOUND;
								$arg['error_line']= __line__;
								$arg['data']       = array();
								$arg['message']    = $this->lang->line('record_not_found');

							}

						}

					} else {
						$arg['status']     = 0;
			            $arg['error_code']  = HTTP_NOT_FOUND;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('record_not_found');
					}

				}


			} else {
				$arg['status']     = 0;
				$arg['error_code']  = HTTP_NOT_FOUND;
				$arg['error_line']= __line__;
				$arg['data']       = array();
				$arg['message']    = $this->lang->line('record_not_found');
			}
		}

		echo json_encode($arg);
	}

    public function class_scheduling_01092020()
	{

	   $arg   = array();
	   $userdata = web_checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{

				//print_r($_POST); die;
			    $this->form_validation->set_rules('class_id','Class Id', 'required|trim', array( 'required' => $this->lang->line('class_id_required')));
			    // $this->form_validation->set_rules('user_id','User Id', 'required|trim', array( 'required' => $this->lang->line('user_id_required')));
			     $this->form_validation->set_rules('start_date','Start date','required|trim', array( 'required' => $this->lang->line('start_date_required')));
			     //$this->form_validation->set_rules('end_date','End date','required|trim', array( 'required' => $this->lang->line('end_date_required')));
			    //$this->form_validation->set_rules('from_time','From time','required|trim', array( 'required' => $this->lang->line('from_time_required')));
			    //$this->form_validation->set_rules('to_time','To time','required|trim', array( 'required' => $this->lang->line('to_time_required')));
               // $this->form_validation->set_rules('class_location','Pass Validity', 'required|trim', array('required' => $this->lang->line('class_location_required')));
                $this->form_validation->set_rules('passes_id','Passes Id', 'required|trim', array('required' => $this->lang->line('passesid_required')));
                //$this->form_validation->set_rules('time_slot','time slot', 'required');
                //$this->form_validation->set_rules('day_id','Day Id', 'required|trim', array('required' => $this->lang->line('day_id_required')));
                $this->form_validation->set_rules('class_status','Class status', 'required|trim', array('required' => $this->lang->line('class_status_required')));
                $this->form_validation->set_rules('class_repeat_times','Class repeat times', 'required|trim', array('required' => $this->lang->line('class_repeat_times_required')));
                $this->form_validation->set_rules('class_days_prior_signup','Class days prior signup', 'required|trim', array('required' => $this->lang->line('class_days_prior_signup_required')));
                $this->form_validation->set_rules('class_waitlist_overflow','Class waitlist overflow', 'required|trim', array('required' => $this->lang->line('class_waitlist_overflow_required')));
				if ($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
                    $time_slot = $this->input->post('time_slot');

                    if(empty($time_slot)) {
                        $arg['status']     = 0;
                        $arg['error_code']  = HTTP_NOT_FOUND;
                        $arg['error_line']= __line__;
                        $arg['message']    =  'Time slots is missing';
                        echo json_encode($arg); exit;
                    }
                    $ins_status = false;
                    $loc_status = false;
                    foreach($time_slot as $ts) {
                        $from_time 	= $ts['from_time'];
                        $to_time 	= $ts['to_time'];
                        if (!array_key_exists('instructor_id', $ts)) {
                            $ins_status = true;
                        }
                        if (!array_key_exists('class_location', $ts)) {
                            $loc_status = true;
                        }
                    }

                    if ($ins_status) {
                        $arg['status']     = 0;
                        $arg['error_code']  = HTTP_NOT_FOUND;
                        $arg['error_line']= __line__;
                        $arg['message']    =  'Instructor is missing';
                        echo json_encode($arg); exit;
                    }

                    if ($loc_status) {
                        $arg['status']     = 0;
                        $arg['error_code']  = HTTP_NOT_FOUND;
                        $arg['error_line']= __line__;
                        $arg['message']    =  'Location is missing';
                        echo json_encode($arg); exit;
					}

					$from_time=$to_time=$duration='';
					$userdata = web_checkuserid();
					$usid =decode($userdata['data']['id']);
					$business_id =decode($userdata['data']['business_id']);
					//echo encode($this->input->post('class_id'))."--";
					//echo encode($this->input->post('class_location'))."--";
					//echo encode($this->input->post('passes_id')); die;
					$time=time();
					$class_id    = decode($this->input->post('class_id'));
					//$user_id     = $this->input->post('user_id');
					//$user_id     = !empty($user_id) ? decode($user_id):'0';

					//$location    = decode($this->input->post('class_location'));
					$class_date  = $this->input->post('class_date');
					$time_slot  = $this->input->post('time_slot');
					//$from_time   = $this->input->post('from_time');
					//$to_time     = $this->input->post('to_time');
					$start_date  = date("Y-m-d",$this->input->post('start_date'));
					//$end_date    = date("Y-m-d",$this->input->post('end_date'));
					//$day_id      = $this->input->post('day_id');
					$class_status      = $this->input->post('class_status');
					$class_repeat_times      = $this->input->post('class_repeat_times');
					$class_days_prior_signup      = $this->input->post('class_days_prior_signup');
					$class_waitlist_overflow      = $this->input->post('class_waitlist_overflow');
					$class_waitlist_count      = $this->input->post('class_overflow_count');

					$passes_id   = multiple_decode_ids($this->input->post('passes_id'),1);

					 //print_r($passes_id);
					//get class data
					$where = array('id'=>$class_id,'business_id'=>$business_id);
				    $class_data = $this->dynamic_model->getdatafromtable('business_class',$where);
				    if(!empty($class_data)){
				    $duration=!empty($class_data[0]['duration']) ? $class_data[0]['duration']:'';
                    //get locations
                    //'id'=>$location
				    $where1 = array('business_id'=>$business_id);
				    $room_location = $this->dynamic_model->getdatafromtable('business_location',$where1);
				    $location_name=!empty($room_location[0]['location_name']) ? $room_location[0]['location_name']:'';
				    $capacity=!empty($room_location[0]['capacity']) ? $room_location[0]['capacity']:'';
					//$classdate=date("Y-m-d",$start_date);
					//$cal_send  =  round($class_repeat_times/count($time_slot));
					//$cal_end  =  fmod($class_repeat_times,count($time_slot));
					//$total_days = ($cal_send * 7) + $cal_end;
					//$end_date  = date('Y-m-d', strtotime($start_date. ' + '.$total_days.' days'));
					$classData =   array(
						//'from_time'   	=>$from_time,
						//'to_time'       =>$to_time,
						//'instructor_id' =>$user_id,
						'capacity'   	=>$capacity,
						'location'   	=>$location_name,
						//'status'   	    =>"Active",
						'start_date'   	=>$start_date,
						//'end_date'   	=>$end_date,
						//'day_id'   		=>$day_id,
						'status'   		=>$class_status,
						'class_repeat_times'   		=>$class_repeat_times,
						'class_days_prior_signup'   		=>$class_days_prior_signup,
						'class_waitlist_overflow'   		=>$class_waitlist_overflow,
						'class_waitlist_count'   		=>($class_waitlist_count)?$class_waitlist_count :0,
						//'end_date'   	=>$end_date,
						'update_dt'   	=>$time
					);
					// Check Instructor and location avaliablity
					if ($this->input->post('demo')) {
						$arg['post'] = $_POST;
						$arg['classData'] = $classData;
						$arg['endDateData'] =  $this->studio_model->getServiceEndDate('class_scheduling_time',$class_id,$business_id);
						echo json_encode($arg); exit;
					}
							$business_class= $this->dynamic_model->updateRowWhere('business_class',$where,$classData);
							if($business_class){

								if(!empty($time_slot)){
									$date = $start_date;
									$unixTimestamp = strtotime($date);
									$dayOfWeek = date("l", $unixTimestamp);

									if($time_slot[0]['day_id']=='1'){
										$weekU = 'Monday';
										$weekL = 'monday';
									}
									if($time_slot[0]['day_id']=='2'){
										$weekU = 'Tuesday';
										$weekL = 'tuesday';
									}
									if($time_slot[0]['day_id']=='3'){
										$weekU = 'Wednesday';
										$weekL = 'wednesday';
									}
									if($time_slot[0]['day_id']=='4'){
										$weekU = 'Thursday';
										$weekL = 'thursday';
									}
									if($time_slot[0]['day_id']=='5'){
										$weekU = 'Friday';
										$weekL = 'friday';
									}
									if($time_slot[0]['day_id']=='6'){
										$weekU = 'Saturday';
										$weekL = 'saturday';
									}
									if($time_slot[0]['day_id']=='7'){
										$weekU = 'Sunday';
										$weekL = 'sunday';
									}

									// if($dayOfWeek == $weekU){

									// 	$date = new DateTime($date);
									// 	$date->modify('next '.$weekL);
									// 	$date = $date->format('Y-m-d');


									// }
									// else{
									// 	$date = new DateTime($date);
									// 	$date->modify('next '.$weekL);
									// 	$date =$date->format('Y-m-d');

									// }

								for ($i=0; $i < $class_repeat_times ; $i++) {
									$j=0;

									foreach ($time_slot as  $key=> $value) {
			        					if($value['day_id']=='1'){
											$weekL = 'monday';
											}
											if($value['day_id']=='2'){
												$weekL = 'tuesday';
											}
											if($value['day_id']=='3'){
												$weekL = 'wednesday';
											}
											if($value['day_id']=='4'){
												$weekL = 'thursday';
											}
											if($value['day_id']=='5'){
												$weekL = 'friday';
											}
											if($value['day_id']=='6'){
												$weekL = 'saturday';
											}
											if($value['day_id']=='7'){
												$weekL = 'sunday';
											}
										if($dayOfWeek != $weekU){

										$date = new DateTime($date);
										$date->modify('next '.$weekL);
										$date = $date->format('Y-m-d');


										}else{

											if($j==0 && $i==0){
												$date = new DateTime($date);
												$date =$date->format('Y-m-d');

												$where2 = array('id'=>$class_id,'business_id'=>$business_id);
												$endDateArray = array('start_date'=> $date);
												$business_class= $this->dynamic_model->updateRowWhere('business_class',$where2,$endDateArray);
											}else{
												$date = new DateTime($date);
												$date->modify('next '.$weekL);
												$date =$date->format('Y-m-d');
											}

										}

										$instructor_id 	= 	decode($value['instructor_id']);
										$start_time 	=	$value['from_time'];
										$end_time 	=	$value['to_time'];



						        			$time_slot_array = array(
												"business_id"=>$business_id,
												"instructor_id"=>decode($value['instructor_id']),
												"day_id"=>$value['day_id'],
												"location_id"=>decode($value['class_location']),
												"scheduled_date"=>$date,
												"from_time"=>($value['from_time'])?$value['from_time']:'',
												"to_time"=>($value['to_time'])?$value['to_time']:'',
												"class_id"=>$class_id
						        			);
						        			if($business_id !='' && $value['day_id'] !='' && $value['class_location'] !='' && $class_id!='' && $date !=''){
							        			$this->dynamic_model->insertdata('class_scheduling_time',$time_slot_array);
						        			}
							        		$j++;}


										}

									$endDateData= $this->studio_model->getServiceEndDate('class_scheduling_time',$class_id,$business_id);

									$where2 = array('id'=>$class_id,'business_id'=>$business_id);
									$endDateArray = array('end_date'=> $endDateData[0]['scheduled_date']);
									$business_class= $this->dynamic_model->updateRowWhere('business_class',$where2,$endDateArray);


					        		$where2=array("business_id"=>$business_id);
									$business_passes= $this->dynamic_model->getWhereInData('business_passes','id',$passes_id,$where2);
									if(!empty($business_passes)){
                                        $insert_array = array();

										foreach ($business_passes as $value){
                                            array_push($insert_array, array(
                                                'user_id'       =>  $usid,
                                                'business_id'   =>  $business_id,
                                                'class_id'      =>  $class_id,
                                                'pass_id'       =>  $value['id'],
                                                'create_dt'     =>  $time,
                                                'update_dt'     =>  $time
                                            ));

                                            $where3=array("id"=>$value['id'],"business_id"=>$business_id);
			                                $updateData= array(
											    'service_id'   	=>$class_id,
											    'service_type'  =>1,
											    'update_dt'   	=>$time
						                    );
									        $this->dynamic_model->updateRowWhere('business_passes',$where3,$updateData);
                                        }

                                        if (!empty($insert_array)) {
                                            $this->db->insert_batch('business_passes_associates', $insert_array);
                                        }
									}
					        		$arg['status']     = 1;
									$arg['error_code']  = HTTP_OK;
									$arg['error_line']= __line__;
									$arg['message']    = 'Class scheduled successfully.';

					        	}
							}
			        }else{
			        	$arg['status']     = 0;
			            $arg['error_code']  = HTTP_NOT_FOUND;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('record_not_found');
			        }
				}
			}
	    }
	 echo json_encode($arg);
    }
     /****************Function class_scheduled_list*******************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : class_scheduled_list
     * @description     : list of class scheduled
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function class_scheduled_list_old()
	{
		$arg = array();
		$userdata = web_checkuserid();
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
				//$limit    = 1;
				$offset = $limit * $page_no;
				$business_id= decode($userdata['data']['business_id']);
                //scheduled_type 0 today classes 1 upcoming classes
                $scheduled_type=  $this->input->post('scheduled_type');
				//$where="business_id=".$business_id." AND instructor_id !=''";
				$where="business_id=".$business_id." AND status='Active'";
				$class_data = $this->dynamic_model->getdatafromtable('business_class',$where,"*",$limit,$offset,'create_dt');
				if(!empty($class_data)){
				    foreach($class_data as $value)
		            {
		            	$from_time=(!empty($value['from_time']))? $value['from_time'] : "";
		            	$to_time=(!empty($value['to_time']))? $value['to_time'] : "";
		            	$start_date=(!empty($value['start_date']))? $value['start_date'] : "";
						$end_date=(!empty($value['end_date']))? $value['end_date'] : "";
		            	$classesdata['class_id']     = encode($value['id']);
		            	$classesdata['class_name']   = $value['class_name'];
		            	$classesdata['duration']     = $value['duration'];
		            	$classesdata['capacity']     = $value['capacity'];

		            	$where1 = array('business_id'=>$business_id,'id'=>$value['location']);
					$room_location = $this->dynamic_model->getdatafromtable('business_location',$where1);
		            	$classesdata['location']     = !empty($room_location[0]['location_name']) ? $room_location[0]['location_name']:'';
		            	$classesdata['from_time']    =  ($from_time)? $from_time:'';
		            	$classesdata['to_time']      =  ($to_time) ? $to_time:'';
		            	$classesdata['from_time_utc']= strtotime($from_time);
		            	$classesdata['to_time_utc']  = strtotime($to_time);
		            	$classesdata['class_type']   = get_categories($value['class_type']);

                        $singned_customer= $this->studio_model->get_all_signed_classes($business_id,$value['id'],5,0);
                        $total_singned_customer= $this->studio_model->get_all_signed_classes($business_id,$value['id']);
                        $total_customer=(!empty($total_singned_customer)) ? count($total_singned_customer) : '0';
			            $client_details=array("client_details"=>$singned_customer,"total_count"=>"$total_customer");
			            $classesdata['client_info']= $client_details;
		            	$classesdata['start_date']    =  ($start_date !=='') ? date("M d Y",strtotime($start_date)) :'';
		            	$classesdata['end_date']    =  ($end_date !=='') ? date("M d Y",strtotime($start_date)) :'';
		            	$classesdata['start_date_utc']= $start_date;
		            	$classesdata['end_date_utc']= $end_date;
		            	$classesdata['create_dt']    = date("M d Y",$value['create_dt']);
		            	$classesdata['create_dt_utc'] = $value['create_dt'];
		            	$classesdata['status'] =($scheduled_type==0) ? 'Today': 'upcoming';
		            	$classesdata['class_status'] = $value['status'];
		            	$classesdata['class_repeat_times'] = $value['class_repeat_times'];
		            	$classesdata['class_days_prior_signup'] = $value['class_days_prior_signup'];
		            	$classesdata['class_waitlist_overflow'] = $value['class_waitlist_overflow'];
		            	$response[]	                 = $classesdata;
		            }
					$arg['status']     = 1;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = $response;
					$arg['message']    = $this->lang->line('record_found');
				}else{
					$arg['status']     = 0;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = array();
				 	$arg['message']    = $this->lang->line('record_not_found');
				}
		    }
		  }
		}

	   echo json_encode($arg);
	}

	public function class_scheduled_list()
	{
		$arg = array();
		$userdata = web_checkuserid();
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
				//$limit    = 1;
				$offset = $limit * $page_no;
				$business_id= decode($userdata['data']['business_id']);
                //scheduled_type 0 today classes 1 upcoming classes
                $scheduled_type=  $this->input->post('scheduled_type');
				$from_date=  $this->input->post('from_date');
				$to_date=  $this->input->post('to_date');
				//$where="business_id=".$business_id." AND instructor_id !=''";
				$class_data = $this->studio_model->get_scheduled_class_list($business_id,$scheduled_type,$from_date,$to_date,$limit,$offset);

				if(!empty($class_data)){
				    foreach($class_data as $value)
		            {
		            	$from_time=(!empty($value['from_time']))? $value['from_time'] : "";
		            	$to_time=(!empty($value['to_time']))? $value['to_time'] : "";
		            	$start_date=(!empty($value['start_date']))? $value['start_date'] : "";
									$end_date=(!empty($value['end_date']))? $value['end_date'] : "";
		            	$classesdata['class_id']     = encode($value['id']);
		            	$classesdata['class_name']   = $value['class_name'];
		            	$classesdata['duration']     = $value['duration'];
		            	$classesdata['capacity']     = $value['location_capacity'];
		            	$classesdata['location']     = $value['location_name'];
		            	$classesdata['location_url']     = $value['location_url'];
		            	$classesdata['from_time']    =  ($from_time)? $from_time:'';
		            	$classesdata['to_time']      =  ($to_time) ? $to_time:'';
		            	$classesdata['from_time_utc']= $from_time;
		            	$classesdata['to_time_utc']  = $to_time;
		            	$classesdata['class_type']   = get_categories($value['class_type']);


		            	$classesdata['start_date']    =  ($start_date !=='') ? date("M d Y",strtotime($start_date)) :'';
		            	$classesdata['end_date']    =  ($end_date !=='') ? date("M d Y",strtotime($end_date)) :'';
		            	$classesdata['start_date_utc']= $start_date;
		            	$classesdata['scheduled_date']= $value['scheduled_date'];
		            	$classesdata['schedule_id']= encode($value['schedule_id']);
		            	$classesdata['end_date_utc']= $end_date;
		            	$classesdata['create_dt']    = date("M d Y",$value['create_dt']);
		            	$classesdata['create_dt_utc'] = $value['create_dt'];
						$classesdata['class_status'] = $value['status'];
						$classesdata['scheduling_status'] = $value['scheduling_status'];
		            	$classesdata['class_repeat_times'] = $value['class_repeat_times'];
		            	$attendence = $this->studio_model->get_class_attendence_count($business_id,$value['id'],$value['scheduled_date'],$value['schedule_id']);
		            	//print_r($attendence); die;
		            	$classesdata['attendence']   = count($attendence);
		            	$classesdata['class_days_prior_signup'] = $value['class_days_prior_signup'];
		            	$classesdata['instructor_name'] = $value['name'].' '.$value['lastname'];
		            	$classesdata['class_waitlist_overflow'] = $value['class_waitlist_overflow'];
		            	$response[]	                 = $classesdata;
		            }
					$arg['status']     = 1;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = $response;
					$arg['message']    = $this->lang->line('record_found');
				}else{
					$arg['status']     = 0;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = array();
				 	$arg['message']    = $this->lang->line('record_not_found');
				}
		    }
		  }
		}

	   echo json_encode($arg);
	}
    /****************Function Get passes list**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : passes_list
     * @description     : list of passes for class
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function class_list()
	{
		$arg = array();
		$userdata = web_checkuserid();
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
		   // $this->form_validation->set_rules('class_type','Class Type', 'required|trim', array( 'required' => $this->lang->line('class_type_required')));
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
				$page = $this->input->post('pageid');
				$page_no= (!empty($page)) ? $page : "1";
				$page_no= $page_no-1;
				$limit    = config_item('page_data_limit');
				$offset = $limit * $page_no;
				$business_id= decode($userdata['data']['business_id']);

				$where="business_id=".$business_id;
				$class_data = $this->dynamic_model->getdatafromtable('business_class',$where,"*",$limit,$offset,'class_name'); // create_dt

				if(!empty($class_data)){
				    foreach($class_data as $value)
		            {
		            	$from_time=(!empty($value['from_time']))? $value['from_time'] : "";
		            	$to_time=(!empty($value['to_time']))? $value['to_time'] : "";
		            	$start_date=(!empty($value['start_date']))? $value['start_date'] : "";
						$end_date = (!empty($value['end_date']))? $value['end_date'] : "";
		            	$classesdata['class_id']     = encode($value['id']);
		            	$classesdata['class_name']   = $value['class_name'];
		            	$classesdata['duration']     = $value['duration'];
                        $classesdata['description']     = $value['description'];

		            	$classesdata['capacity']     = $value['capacity'];
		            	$classesdata['location']     = $value['location'];
		            	$classesdata['from_time']    =  ($from_time !=='') ? $from_time :'';
		            	$classesdata['to_time']      =  ($to_time !=='') ? $to_time :'';
		            	$classesdata['from_time_utc']= strtotime($from_time);
		            	$classesdata['to_time_utc']  = strtotime($to_time);
		            	$classesdata['class_type']   = $this->db->get_where('manage_category', array('id' => $value['class_type']))->row_array()['category_name'];

                        $instructor_data             = $this->instructor_list_details($business_id,1,$value['id']);
			            $classesdata['instructor_details']    = !empty($instructor_data[0]) ? $instructor_data[0] : json_decode('{}');
		            	$classesdata['start_date']    =  ($start_date !=='') ? date("d M Y ",strtotime($start_date)) :'';
		            	$classesdata['end_date']    =  ($end_date !=='') ? date("d M Y ",strtotime($end_date)) :'';
		            	$classesdata['start_date_utc']= strtotime($start_date);
		            	$classesdata['end_date_utc']= strtotime($end_date);
		            	$classesdata['create_dt']    = date("d M Y ",$value['create_dt']);
		            	$classesdata['create_dt_utc'] = $value['create_dt'];
		            	$classesdata['status'] = $value['status'];
		            	$response[]	                 = $classesdata;
		            }
					$arg['status']     = 1;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = $response;
					$arg['message']    = $this->lang->line('record_found');
				}else{
					$arg['status']     = 0;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = array();
				 	$arg['message']    = $this->lang->line('record_not_found');
				}
		    }
		  }
		}

	   echo json_encode($arg);
	}

	public function class_details()
	{
		$arg = array();
		$userdata = web_checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{

		      $_POST = json_decode(file_get_contents("php://input"), true);
			  if($_POST)
			  {

			    $this->form_validation->set_rules('class_id','Class Id', 'required|trim', array( 'required' => $this->lang->line('class_id_required')));
			    $this->form_validation->set_rules('business_id', 'Business Id', 'required',array(
						'required' => $this->lang->line('business_id_req')
					));
				$this->form_validation->set_rules('schedule_id','Schedule Id', 'required|trim');
			    $this->form_validation->set_rules('scheduled_date','Scheduled Date', 'required|trim');
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
					$response=$pass_arr=array();

					$class_id=  decode($this->input->post('class_id'));
					$business_id=  decode($this->input->post('business_id'));
					$scheduled_date=  $this->input->post('scheduled_date');
					$schedule_id=  decode($this->input->post('schedule_id'));

                    $where=array("id"=>$class_id,"business_id"=>$business_id,"status"=>"Active");
					$class_data = $this->studio_model->get_scheduled_class_detail($business_id,$class_id,$scheduled_date, $schedule_id);
					//print_r($class_data);die;
					if(!empty($class_data)){
					    $classesdata['business_id']  = $business_id;
		            	$classesdata['class_id']     = $class_data[0]['id'];
		            	$classesdata['schedule_id']     = $schedule_id;
		            	$classesdata['class_name']   = ucwords($class_data[0]['class_name']);
		            	$classesdata['from_time']    = $class_data[0]['from_time'];
		            	$classesdata['to_time']      = $class_data[0]['to_time'];
		            	$classesdata['from_time_utc'] =$class_data[0]['from_time'];
		            	$classesdata['to_time_utc'] = $class_data[0]['to_time'];
		            	$classesdata['duration']     = $class_data[0]['duration'].' minutes';
		            	$classesdata['scheduled_date']     = $class_data[0]['scheduled_date'];
						$classesdata['total_capacity']    = $class_data[0]['capacity'];
						$attendence = $this->studio_model->get_class_attendence_count($business_id,$class_data[0]['id'],$class_data[0]['scheduled_date'], $schedule_id);
						$classesdata['attendence']     = ($attendence)?count($attendence):0;
						$classesdata['timeframe']     = get_daywise_instructor_data($class_data[0]['id'],1,$business_id, $schedule_id);
		            	// $capicty_used                = get_checkin_class_or_workshop_count($class_data[0]['id'],1,$time);
			            // $classesdata['capacity']     = $capicty_used.'/'.$class_data[0]['capacity'];
						$classesdata['location']     = $class_data[0]['location_name'];
						$classesdata['location_url']     = $class_data[0]['location_url'];
						$classesdata['web_link']     = $class_data[0]['web_link'];
		            	$classesdata['description']     = $class_data[0]['description'];
		            	$classesdata['class_type']   = get_categories($class_data[0]['class_type']);
                        $classesdata['start_date']    = date("M d Y",strtotime($class_data[0]['start_date']));
                        $classesdata['end_date']    = date("M d Y",strtotime($class_data[0]['end_date']));

                        $classesdata['start_date_utc']=  strtotime($class_data[0]['start_date']);
                        $classesdata['end_date_utc']=  strtotime($class_data[0]['end_date']);
				        $classesdata['instructor_name'] = $class_data[0]['name'].' '.$class_data[0]['lastname'];
				        $classesdata['instructor_image'] = $class_data[0]['profile_image'];
						$classesdata['scheduling_status'] = $class_data[0]['scheduling_status'];
		            	//$where=array("business_id"=>$business_id,"service_id"=>$class_id,"service_type"=>"1","status"=>"Active");
		            	$this->db->select('b.*');
		            	$this->db->select('mpt.pass_days');
                        $this->db->from('business_passes_associates as bpa');
                        $this->db->join('business_passes b', 'b.id = bpa.pass_id');
                        $this->db->join('manage_pass_type mpt', 'mpt.id = b.pass_type_subcat');
                        $this->db->where('bpa.business_id',$business_id);
                        $this->db->where('bpa.class_id',$class_id);
                        $this->db->where('b.status',"Active");
                        $this->db->group_by('b.id');
                        $passes_data = $this->db->get()->result_array();
						// $passes_data = $this->dynamic_model->getdatafromtable('business_passes',$where,"*");
						if(!empty($passes_data)){
						    foreach($passes_data as $value)
				            {
				                $passesdata=studiopassesdetails($value['id']);
				                $passesdata['pass_days'] = $value['pass_days'];
				            	$pass_arr[]	  = $passesdata;
				            }
				        }
						$classesdata['passes_details']    = $pass_arr;
						$classesdata['create_dt']    = date("d M Y ",$class_data[0]['create_dt']);
		            	$classesdata['create_dt_utc']  = $class_data[0]['create_dt'];
		            	$bookedUsers = $this->studio_model->get_booked_customer($class_data[0]['id'],$class_data[0]['scheduled_date'], $schedule_id);
		            	$userArray= array();
		            	foreach ($bookedUsers as $row) {
		            		$covid_info = getUserQuestionnaire($row['user_id'],$row['service_id'],$business_id);
		            		if(!empty($covid_info)){
			                    $covid_status = $covid_info['covid_status'];
			                    $covid_info = $covid_info['covid_info'];
			                }else{
			                    $covid_info = 0;
			                    $covid_status = 0;
							}

							$sql = "SELECT * FROM user_attendance WHERE user_id = '".$row['user_id']."' && service_id = '".$row['service_id']."' && schedule_id = '".$schedule_id."'";

							$attendance_data = $this->dynamic_model->getQueryResultArray($sql);
							$attendance_id = '';
							if(!empty($attendance_data)){
								$attendance_id = $attendance_data[0]['id'];
							}
		            		$userArray[] = array(
											'user_id' =>$row['user_id'],
											'attendance_id' => $attendance_id,
		            						'service_type' =>$row['service_type'],
		            						'service_id' =>$row['service_id'],
		            						'status' =>$row['status'],
		            						'checkin_dt' =>$row['checkin_dt'],
		            						'name' =>$row['name'],
		            						'lastname' =>$row['lastname'],
		            						'profile_image' =>$row['profile_image'],
		            						'covid_info' => $covid_info,
											'covid_status' => $covid_status,
											'created_by' => ($row['checked_by'] == 0) ? 'Self' : ( ($row['checked_by'] == $row['user_id']) ? 'Self' : 'Studio')
		            					);

		            	}
		            	$classesdata['booked_users']  = $userArray;
		            	$classesdata['day_name']  = $class_data[0]['week_name'];
						$setLocationId = $class_data[0]['location_id'];
						$selectFromDae = $class_data[0]['from_time'];
						$selectToDae = $class_data[0]['to_time'];
						$currentDate = date('Y-m-d');
						$upcommingSql = "SELECT id, scheduled_date FROM class_scheduling_time WHERE status = 'Active' AND scheduled_date >= '".$currentDate."' AND business_id = ".$business_id." AND class_id = ".$class_id." AND DATE_FORMAT(FROM_UNIXTIME(from_time), '%H:%i:%s') = DATE_FORMAT(FROM_UNIXTIME(".$selectFromDae."), '%H:%i:%s') AND DATE_FORMAT(FROM_UNIXTIME(to_time), '%H:%i:%s') = DATE_FORMAT(FROM_UNIXTIME(".$selectToDae."), '%H:%i:%s') AND location_id = ".$setLocationId;
						$classesdata['upcomming'] = $this->dynamic_model->getQueryResultArray($upcommingSql);
		            	// $classesdata['checkedin_customer']  = $this->studio_model->get_checkedin_customer($class_data[0]['id'],$class_data[0]['scheduled_date']);
		            	// $classesdata['waiting_customer']  = $this->studio_model->get_waiting_customer($class_data[0]['id'],$class_data[0]['scheduled_date']);
		            	// $classesdata['passes_status'] = get_passes_checkin_status($usid,$class_data[0]['id'],1,$date);

                        //get passes purchase status
            //             $check_purchase= get_passes_status($usid,$business_id,$class_id,1);
				        // if($check_purchase=='Pending' || $check_purchase ==''){
            //             	 $classes_status='0';//pass purchase
            //             }elseif(empty($signup_check) || $current_status=='cancel'){
				        // 	$classes_status='1';//signup

				        // }elseif(!empty($signup_check)){
				        // 	if(empty($checkin_data) || $current_status=='singup' ){
				        // 		$classes_status='2';//check in
				        // 	}
            //                 // elseif($current_status=='absence' ){
            //                 //     $classes_status='5';//check in
            //                 // }
            //                 else{
            //                      //3 checked in, 4 waiting
				        // 	     $classes_status= (!empty($signup_check[0]['waitng_list_no'])) ? '4' : '3';
				        // 	}
				        // }
            //            $classesdata['class_status']   = $classes_status;
            //            $classesdata['waitng_list_no'] =(!empty($signup_check[0]['waitng_list_no'])) ? $signup_check[0]['waitng_list_no'] : '';
		            	$response	                  = $classesdata;

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
	   echo json_encode($arg);
	}

	public function today_class_list()
	{
		$arg = array();
		$userdata = web_checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{

			$time_zone =  $this->input->get_request_header('Timezone', true);
			$time_zone =  $time_zone ? $time_zone : 'UTC';
			date_default_timezone_set($time_zone);
			$response=array();
			$time=time();
			$business_id= decode($userdata['data']['business_id']);
			$date = date('Y-m-d');
			$_POST = json_decode(file_get_contents("php://input"), true);
			if ($_POST) {
				$date = $this->input->post('scheduled_date');
				$class_data = $this->studio_model->get_scheduled_class_list($business_id, 1, $date, '', '', '');
			} else {
				$class_data = $this->studio_model->get_scheduled_class_list($business_id,'0','','','','');
			}


			if(!empty($class_data)){
				foreach($class_data as $value)
				{
					$from_time=(!empty($value['from_time']))? $value['from_time'] : "";
					$to_time=(!empty($value['to_time']))? $value['to_time'] : "";
					$start_date=(!empty($value['start_date']))? $value['start_date'] : "";
					$end_date=(!empty($value['end_date']))? $value['end_date'] : "";
					$classesdata['class_id']     = encode($value['id']);
					$classesdata['class_name']   = $value['class_name'];
					$classesdata['duration']     = $value['duration'];
					$classesdata['capacity']     = $value['capacity'];
					$classesdata['location']     = $value['location_name'];
					$classesdata['location_url']     = $value['location_url'];
					$classesdata['from_time']    =  ($from_time)? $from_time:'';
					$classesdata['to_time']      =  ($to_time) ? $to_time:'';
					$classesdata['from_time_utc']= $from_time;
					$classesdata['to_time_utc']  = $to_time;
					$classesdata['class_type']   = get_categories($value['class_type']);
					$skillIds = $this->studio_model->get_instructor_skills($value['instructor_id']);
					$classesdata['skills'] = ($skillIds)?get_categories($skillIds[0]['skill']):'';
					$classesdata['start_date']    =  ($start_date !=='') ? date("M d Y",strtotime($start_date)) :'';
					$classesdata['end_date']    =  ($end_date !=='') ? date("M d Y",strtotime($end_date)) :'';
					$classesdata['start_date_utc']= $start_date;
					$classesdata['scheduled_date']= $value['scheduled_date'];
					$classesdata['schedule_id']= encode($value['schedule_id']);
					$classesdata['end_date_utc']= $end_date;
					$classesdata['create_dt']    = date("M d Y",$value['create_dt']);
					$classesdata['create_dt_utc'] = $value['create_dt'];
					$classesdata['class_status'] = $value['status'];
					$classesdata['class_repeat_times'] = $value['class_repeat_times'];
					$attendence = $this->studio_model->get_class_attendence_count($business_id,$value['id'],$value['scheduled_date'],$value['schedule_id']);
					$classesdata['attendence']   = count($attendence);
					$classesdata['class_days_prior_signup'] = $value['class_days_prior_signup'];
					$classesdata['instructor_name'] = $value['name'].' '.$value['lastname'];
					$classesdata['instructor_image'] = $value['profile_image'];
					$classesdata['class_waitlist_overflow'] = $value['class_waitlist_overflow'];
					$response[]	                 = $classesdata;
				}
				$arg['status']     = 1;
				$arg['error_code']  = HTTP_OK;
				$arg['error_line']= __line__;
				$arg['data']       = $response;
				$arg['message']    = $this->lang->line('record_found');
			}else{
				$arg['status']     = 0;
				$arg['error_code']  = HTTP_OK;
				$arg['error_line']= __line__;
				$arg['data']       = $this->db->last_query();
				$arg['message']    = $this->lang->line('record_not_found');
			}


		}

	   echo json_encode($arg);
	}
    /****************Function Add Services **********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : add_services
     * @description     : add  services like Massage/Therapy sessions
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function add_services()
	{
	   $arg   = array();
	   $userdata = web_checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{

			$_POST = json_decode(file_get_contents("php://input"), true);

			if($_POST)
			{
				$this->form_validation->set_rules('service_name','Service name', 'required|trim', array( 'required' => $this->lang->line('service_name_required')));
            	$this->form_validation->set_rules('duration','Duration', 'required|trim', array( 'required' => $this->lang->line('workshop_duration_req')));
				// $this->form_validation->set_rules('service_type_id','Service Type', 'required|trim', array( 'required' => $this->lang->line('service_type_required')));
				// $this->form_validation->set_rules('skills', 'Skill', 'required|trim', array( 'required' => 'Skill is required'));
                $this->form_validation->set_rules('amount','Amount', 'required|trim', array('required'=>$this->lang->line('amount_required')));
				$this->form_validation->set_rules('is_client_visible', 'required|trim');
				$this->form_validation->set_rules('tax1','Tax 1','required|alpha',array( 'required' => $this->lang->line('tax1_required'), 'alpha' => $this->lang->line('tax1_alpha')));
				$this->form_validation->set_rules('tax2','Tax 2','required|alpha',array( 'required' => $this->lang->line('tax2_required'), 'alpha' => $this->lang->line('tax2_alpha')));
				if ($this->input->post('tax1') && (ucfirst($this->input->post('tax1')) == 'Yes')) {
					$this->form_validation->set_rules('tax1_rate','Tax 1 rate','required|numeric',array( 'required' => $this->lang->line('tax1_rate_required'),'numeric' => $this->lang->line('tax1_rate_numeric')));
				}
				if ($this->input->post('tax2') && (ucfirst($this->input->post('tax2')) == 'Yes')) {
					$this->form_validation->set_rules('tax2_rate','Tax 2 rate','required|numeric',array( 'required' => $this->lang->line('tax2_rate_required'),'numeric' => $this->lang->line('tax2_rate_numeric')));
				}

				// $this->form_validation->set_rules('start_date','Start Date', 'required|trim', array( 'required' => $this->lang->line('start_date_time_required')));
				// $this->form_validation->set_rules('end_date','End Date', 'required|trim', array( 'required' => $this->lang->line('end_date_time_required')));
				$this->form_validation->set_rules('cancel_policy','Cancel Policy', 'required|trim|max_length[140]', array( 'required' => $this->lang->line('cancel_policy_required'),'max_length' => $this->lang->line('cancel_policy_max_length')));
				$this->form_validation->set_rules('description','Description', 'required|trim', array( 'required' => 'description is required'));
				$this->form_validation->set_rules('instructor[]','Instructor', 'required', array( 'required' => 'Instructor is required'));
				if ($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$userdata 	= 	web_checkuserid();
					$usid 		=	decode($userdata['data']['id']);
					$business_id= decode($userdata['data']['business_id']);
					$time		=	time();
					$start_date = date('Y-m-d'); // $this->input->post('start_date');
					$end_date 	= date('Y-m-d'); // $this->input->post('end_date');

					/* $date1	=	date_create(date('Y-m-d', $start_date));
					$date2	=	date_create(date('Y-m-d', $end_date));
					$diff	=	date_diff($date1,$date2);
					$days 	=  $diff->format("%R%a");

					$current = date_create(date('Y-m-d')); */
					/*if($days < $time && $current < $date1) {
						$arg['status']  = 0;
						$arg['error_code'] = 0;
						$arg['error_line']= __line__;
						$arg['message'] = 'Invalid date';
						echo json_encode($arg);
						exit();
					}*/

					$service_name 		= $this->input->post('service_name');
					$duration   		= $this->input->post('duration');
					// $service_type   	= $this->input->post('service_type_id');
					// $skills   			= $this->input->post('skills');
					$amount     		= $this->input->post('amount');
					$is_client_visible  = $this->input->post('is_client_visible');

					$tax1       	= $this->input->post('tax1');
					$tax2       	= $this->input->post('tax2');
					$tax1_rate      = $this->input->post('tax1_rate');
					$tax1_rate = $tax1_rate ? $tax1_rate : 0;
					$tax2_rate      = $this->input->post('tax2_rate');
					$tax2_rate = $tax2_rate ? $tax2_rate : 0;
					$policy      	= $this->input->post('cancel_policy');


					$tip_option     = $this->input->post('tip_option');
					$description     = $this->input->post('description');
					$time_needed     = $this->input->post('time_needed');
					// $service_type     = $this->input->post('service_type');
					$instructor = $this->input->post('instructor');

					$serviceData =   array(
						'service_name'		=>	$service_name,
						'user_id'			=>	$usid,
						'business_id'		=>	$business_id,
						'start_date_time'	=>	$start_date,
						'end_date_time'		=>	$end_date,
						'amount'			=>	$amount,
						// 'service_type'  	=>  $service_type,
						'tip_option'		=>	$tip_option,
						'description'		=>	$description,
						'time_needed'     	=>	$time_needed,
						'duration'     		=>	$duration,
						'tax1'         		=>	(ucfirst($tax1) == 'Yes') ? 'Yes' : 'No',
						'tax2'         		=>	(ucfirst($tax2) == 'Yes') ? 'Yes' : 'No',
						'tax1_rate'     	=>	$tax1_rate,
						'tax2_rate'     	=>	$tax2_rate,
						'cancel_policy'		=>	$policy,
						'is_client_visible' =>  (ucfirst($is_client_visible) == 'Yes') ? 'Yes' : 'No',
						// 'skills'				=>	$skills,
						'status'   	    	=>	"Active",
						'create_dt'   		=>	$time,
						'update_dt'   		=>	$time
					);

					if ($this->input->post('tax1_label')) {
						$serviceData['tax1_label'] = $this->input->post('tax1_label');
					} else {
						$serviceData['tax1_label'] = 'Tax1';
					}

					if ($this->input->post('tax2_label')) {
						$serviceData['tax2_label'] = $this->input->post('tax2_label');
					} else {
						$serviceData['tax2_label'] = 'Tax2';
					}

					$business_sevice= $this->dynamic_model->insertdata('service',$serviceData);
					if($business_sevice)
			        {
						$serviceId = $this->db->insert_id();
						$length = count($instructor);
						$insertData = array();
						for($i = 0; $i < $length; $i++) {
							array_push($insertData, array(
								'service_id' => $serviceId,
								'instructor_id' => decode($instructor[$i]),
								'create_dt' => $time
							));
						}
						$this->db->insert_batch('service_instructor', $insertData);
						$arg['status']    = 1;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line']= __line__;
					 	$arg['message']   = $this->lang->line('service_save_succ');
					 	$arg['data']      = [];
			        }else{
			        	$arg['status']     = 0;
			            $arg['error_code']  = HTTP_NOT_FOUND;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('server_problem');
			        }
				}
			}
	    }

	 	echo json_encode($arg);
    }
    public function add_services_02102020()
	{
	   $arg   = array();
	   $userdata = web_checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
			$_POST = json_decode(file_get_contents("php://input"), true);

			if($_POST)
			{
				$this->form_validation->set_rules('service_name','Service name', 'required|trim', array( 'required' => $this->lang->line('service_name_required')));
			    $this->form_validation->set_rules('start_date','Start Date', 'required|trim', array( 'required' => $this->lang->line('start_date_time_required')));
				$this->form_validation->set_rules('end_date','End Date', 'required|trim', array( 'required' => $this->lang->line('end_date_time_required')));
            	$this->form_validation->set_rules('duration','Duration', 'required|trim', array( 'required' => $this->lang->line('workshop_duration_req')));
				$this->form_validation->set_rules('service_type_id','Service Type', 'required|trim', array( 'required' => $this->lang->line('service_type_required')));
				$this->form_validation->set_rules('cancel_policy','Cancel Policy', 'required|trim|max_length[140]', array( 'required' => $this->lang->line('cancel_policy_required'),'max_length' => $this->lang->line('cancel_policy_max_length')));
                $this->form_validation->set_rules('amount','Amount', 'required|trim', array('required'=>$this->lang->line('amount_required')));
				$this->form_validation->set_rules('tax1','Tax 1','required|alpha',array( 'required' => $this->lang->line('tax1_required'), 'alpha' => $this->lang->line('tax1_alpha')));
				$this->form_validation->set_rules('tax2','Tax 2','required|alpha',array( 'required' => $this->lang->line('tax2_required'), 'alpha' => $this->lang->line('tax2_alpha')));
				$this->form_validation->set_rules('tip_option','Tip Option','required|alpha',array('required' => $this->lang->line('tip_option_required'),'alpha' => $this->lang->line('tip_option_alpha')));
				$this->form_validation->set_rules('pay_rate','Pay Rate','required|numeric',array('required' => $this->lang->line('pay_rate_required'),'numeric' => $this->lang->line('pay_rate_numeric')));


				if ($this->input->post('tax1') && (ucfirst($this->input->post('tax1')) == 'Yes')) {
					$this->form_validation->set_rules('tax1_rate','Tax 1 rate','required|numeric',array( 'required' => $this->lang->line('tax1_rate_required'),'numeric' => $this->lang->line('tax1_rate_numeric')));
				}

				if ($this->input->post('tax2') && (ucfirst($this->input->post('tax2')) == 'Yes')) {
					$this->form_validation->set_rules('tax2_rate','Tax 2 rate','required|numeric',array( 'required' => $this->lang->line('tax2_rate_required'),'numeric' => $this->lang->line('tax2_rate_numeric')));
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
					$userdata 	= 	web_checkuserid();
					$usid 		=	decode($userdata['data']['id']);
					$business_id= decode($userdata['data']['business_id']);
					$time		=	time();
					$start_date = $this->input->post('start_date');
					$end_date 	= $this->input->post('end_date');

					$date1	=	date_create(date('Y-m-d', $start_date));
					$date2	=	date_create(date('Y-m-d', $end_date));
					$diff	=	date_diff($date1,$date2);
					$days 	=  $diff->format("%R%a");

					$current = date_create(date('Y-m-d'));
					if($days < $time && $current < $date1) {
						$arg['status']  = 0;
						$arg['error_code'] = 0;
						$arg['error_line']= __line__;
						$arg['message'] = 'Invalid date';
						echo json_encode($arg);
						exit();
					}

					$service_name 	= $this->input->post('service_name');
					$service_type   = $this->input->post('service_type_id');
					$amount     	= $this->input->post('amount');
					$duration   	= $this->input->post('duration');
					$tax1       	= $this->input->post('tax1');
					$tax2       	= $this->input->post('tax2');
					$tax1_rate      = $this->input->post('tax1_rate');
					$tax2_rate      = $this->input->post('tax2_rate');
					$policy      	= $this->input->post('cancel_policy');
					$tip_option		= $this->input->post('tip_option');
					$pay_rate		= $this->input->post('pay_rate');

					$serviceData =   array(
						'service_name'		=>	$service_name,
						'user_id'			=>	$usid,
						'business_id'		=>	$business_id,
						'start_date_time'	=>	$start_date,
						'end_date_time'		=>	$end_date,
						'amount'			=>	$amount,
						'service_type'  	=>  $service_type,
						'duration'     		=>	$duration,
						'tax1'         		=>	(ucfirst($tax1) == 'Yes') ? 'Yes' : 'No',
						'tax2'         		=>	(ucfirst($tax2) == 'Yes') ? 'Yes' : 'No',
						'tax1_rate'     	=>	($tax1_rate)? $tax1_rate:'',
						'tax2_rate'     	=>	($tax2_rate)? $tax2_rate:'',
						'cancel_policy'		=>	$policy,
						'tip_option'		=>	(ucfirst($tip_option) == 'Yes') ? 'Yes' : 'No',
						'pay_rate'			=>	$pay_rate,
						'status'   	    	=>	"Active",
						'create_dt'   		=>	$time,
						'update_dt'   		=>	$time
					);

					$business_sevice= $this->dynamic_model->insertdata('service',$serviceData);
					if($business_sevice)
			        {
						$arg['status']    = 1;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line']= __line__;
					 	$arg['message']   = $this->lang->line('service_save_succ');
					 	$arg['data']      = [];
			        }else{
			        	$arg['status']     = 0;
			            $arg['error_code']  = HTTP_NOT_FOUND;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('server_problem');
			        }
				}
			}
	    }

	 echo json_encode($arg);
    }
	public function add_services_bkt()
	{
	   $arg   = array();
	   $userdata = web_checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
			    $this->form_validation->set_rules('service_name','Service name', 'required|trim', array( 'required' => $this->lang->line('service_name_required')));
                $this->form_validation->set_rules('duration','Duration', 'required|trim', array( 'required' => $this->lang->line('workshop_duration_req')));
                $this->form_validation->set_rules('amount','Amount', 'required|trim', array('required'=>$this->lang->line('amount_required')));
			    $this->form_validation->set_rules('tax1','Tax 1','required',array( 'required' => $this->lang->line('tax1_required')));
		        $this->form_validation->set_rules('tax2','Tax 2','required',array( 'required' => $this->lang->line('tax2_required')));
			    // $this->form_validation->set_rules('start_date_time','Start Date Time','required|trim',array( 'required' => $this->lang->line('start_date_time_required')));
			    //  $this->form_validation->set_rules('end_date_time','End Date Time','required|trim',array( 'required' => $this->lang->line('end_date_time_required')));
			    $this->form_validation->set_rules('service_type_id','Service Type', 'required|trim', array( 'required' => $this->lang->line('service_type_required')));
			    // $this->form_validation->set_rules('service_location','Service location', 'required|trim', array( 'required' => $this->lang->line('service_location_required')));
				if ($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$userdata = web_checkuserid();
					$usid =decode($userdata['data']['id']);
					$time=time();
					$service_name = $this->input->post('service_name');
					$amount     = $this->input->post('amount');
					$duration   = $this->input->post('duration');
					$tax1       = $this->input->post('tax1');
					$tax2       = $this->input->post('tax2');
					$tax1_rate       = $this->input->post('tax1_rate');
					$tax2_rate      = $this->input->post('tax2_rate');
					// $start_date_time    = $this->input->post('start_date_time');
					// $end_date_time      = $this->input->post('end_date_time');
					$service_type       = decode($this->input->post('service_type_id'));
					// $service_location   = $this->input->post('service_location');

					//get business Id
					$business_id= decode($userdata['data']['business_id']);
					$serviceData =   array(
						                'business_id'  =>$business_id,
										'user_id'  	   =>$usid,
										'service_name' =>$service_name,
										'duration'     =>$duration,
										'amount'       =>$amount,
										'tax1'         =>$tax1,
										'tax2'         =>$tax2,
										'tax1_rate'         =>($tax1_rate)? $tax1_rate:'',
										'tax2_rate'         =>($tax2_rate)? $tax2_rate:'',
										// 'start_date_time'  =>strtotime($start_date_time),
										// 'end_date_time'    =>strtotime($end_date_time),
										 'service_type'     =>$service_type,
										// 'service_location' =>$service_location,
										'status'   	    =>"Active",
										'create_dt'   	=>$time,
										'update_dt'   	=>$time
					                   );
					$business_sevice= $this->dynamic_model->insertdata('service',$serviceData);
					if($business_sevice)
			        {
						$arg['status']    = 1;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line']= __line__;
					 	$arg['message']   = $this->lang->line('service_save_succ');
					 	$arg['data']      = [];
			        }else{
			        	$arg['status']     = 0;
			            $arg['error_code']  = HTTP_NOT_FOUND;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('server_problem');
			        }

				}
			}
	    }

	 echo json_encode($arg);
    }

    /****************Function Get services list**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : services_list
     * @description     : list of services
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function services_list()
	{
		$arg = array();
		$userdata = web_checkuserid('1');
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
                $business_id= decode($userdata['data']['business_id']);
				/* $where=array("business_id"=>$business_id,"status"=>"Active");
				$service_data = $this->dynamic_model->getdatafromtable('service',$where,"*",$limit,$offset,'create_dt','DESC'); */

				$query = 'SELECT ser.id, ser.business_id, ser.service_name, ser.skills as service_category_id, ser.service_type, ms.name as service_category, ser.start_date_time as start_date, ser.end_date_time  as end_date, ser.is_client_visible, ser.duration, ser.amount, ser.tax1, ser.tax1_rate,  ser.tax2, ser.tax1_label, ser.tax2_label, ser.tax2_rate, ser.tip_option, ser.time_needed, ser.description, ser.cancel_policy, ser.create_dt, ser.create_dt as create_dt_utc FROM service ser LEFT JOIN manage_skills ms on (ms.id = ser.skills) where ser.business_id = '.$business_id.' AND ser.status = "Active" LIMIT '.$limit.' OFFSET '.$offset;

				$service_data = $this->dynamic_model->getQueryResultArray($query);

				if(!empty($service_data)){
					$total_count = $this->db->query('SELECT ser.* FROM service ser where ser.business_id = '.$business_id.' AND ser.status = "Active"')->num_rows();

					array_walk ( $service_data, function (&$key) {

						$serviceId = $key['id'];
						$key["service_id"] = encode($serviceId);
						$imgePath = base_url().'uploads/user/';
						$collection = $this->dynamic_model->getQueryResultArray("SELECT user.id, user.name, user.lastname, concat('".$imgePath."', user.profile_img) as profile, user.profile_img, user.availability_status, instructor_details.total_experience as experience, instructor_details.appointment_fees, instructor_details.appointment_fees_type, instructor_details.skill FROM service_instructor JOIN user on (user.id = service_instructor.instructor_id) JOIN instructor_details on (instructor_details.user_id = user.id) where service_instructor.status = 'Active' AND service_instructor.service_id = ".$serviceId." GROUP BY service_instructor.instructor_id");
						array_walk ( $collection, function (&$keys) {
							$keys["instructor_ids"] = encode($keys['id']);
						});
						$key["instructor"] = $collection;
						/* $key["service_id"] = encode($key['id']);
						$skillId = intval($key['service_category_id']);
						$ser_business_id = $key['business_id'];
						$imgePath = base_url().'uploads/user/';
						$key["instructor"] = $this->dynamic_model->getQueryResultArray("SELECT user.id, user.name, user.lastname, concat('".$imgePath."', user.profile_img) as profile, user.profile_img, instructor_details.skill FROM `business_trainer_relationship` JOIN user on (user.id = business_trainer_relationship.user_id) JOIN instructor_details on (instructor_details.user_id = user.id) WHERE business_trainer_relationship.business_id = ".$ser_business_id." AND business_trainer_relationship.is_verified = 'Active' and business_trainer_relationship.status = 'Approve' AND FIND_IN_SET(".$skillId.", instructor_details.skill)");
						$query = ""; */
					});

					$arg['status']     = 1;
					$arg['error_code']  = REST_Controller::HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = $service_data;
					$arg['total'] = $total_count;
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

	   echo json_encode($arg);
	}

	public function services_list_bk()
	{
		$arg = array();
		$userdata = web_checkuserid('1');
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
                $business_id= decode($userdata['data']['business_id']);
				$where=array("business_id"=>$business_id,"status"=>"Active");
				$service_data = $this->dynamic_model->getdatafromtable('service',$where,"*",$limit,$offset,'create_dt','DESC');
				//print_r($class_data);die;
				if(!empty($service_data)){
				    foreach($service_data as $value)
		            {
		            	$servicedata['service_id']     = encode($value['id']);
		            	$servicedata['service_name']   = ucwords($value['service_name']);
		            	$service_type=!empty($value['service_type']) ? get_categories($value['service_type'],2) :'';
		            	$servicedata['service_category'] = $service_type;
		            	$servicedata['amount']   = $value['amount'];
		            	$servicedata['tax1']   = $value['tax1'];
		            	$servicedata['tax2']   = $value['tax2'];
		            	$servicedata['create_dt']    = date("d M Y ",$value['create_dt']);
		            	$servicedata['create_dt_utc'] = $value['create_dt'];
		            	$response[]	                 = $servicedata;
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

	   echo json_encode($arg);
	}

	public function service_comment() {
		$arg   = array();
	  	 $userdata = web_checkuserid();
	   	if($userdata['status'] != 1){
			$arg = $userdata;
		} else {
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
				$this->form_validation->set_rules('id','Service', 'required|trim', array( 'required' => 'Service is required'));
				$this->form_validation->set_rules('schedule','Schedule', 'required|trim', array( 'required' => 'Schedule is required'));
				$this->form_validation->set_rules('comment','Comment', 'required|trim', array( 'required' => 'Comment is required'));
				if ($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {
					$userdata 	= 	web_checkuserid();
					$usid 		=	decode($userdata['data']['id']);
					$business_id= decode($userdata['data']['business_id']);
					$time		=	time();
					$this->dynamic_model->insertdata('business_shift_instructor_comments',
						array(
							'shift_id'	=>	$this->input->post('id'),
							'shift_schedule_id' => $this->input->post('schedule'),
							'comment' => $this->input->post('comment'),
							'create_dt'	=>	$time,
							'type'	=> 2,
							'owner_id'	=>	$usid
						)
					);

					$response = getShift(3, $business_id, $this->input->post('schedule'), '', '', 'shift_date_str', 'ASC', '');
					$arg['status']     = 1;
					$arg['error_code']  = REST_Controller::HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = $response;
					$arg['message']    = $this->lang->line('record_found');
				}
			}
		}
		echo json_encode($arg);
	}

	 /****************Function service_scheduling scheduling**********
     * @type            : Function
     * @Author          : Arpit
     * @function name   : service_scheduling
     * @description     : service scheduling
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function service_scheduling()
	{
	   $arg   = array();
	   $userdata = web_checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
			    $this->form_validation->set_rules('service_id','Service Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
			    // $this->form_validation->set_rules('user_id','User Id', 'required|trim', array( 'required' => $this->lang->line('user_id_required')));
			     $this->form_validation->set_rules('slot_date','Service date','required|trim', array( 'required' => $this->lang->line('date_required')));
			    $this->form_validation->set_rules('slot_id','Slot Id','required|trim', array( 'required' => $this->lang->line('slot_id_required')));
                // $this->form_validation->set_rules('service_location','Service location', 'required|trim', array('required' => $this->lang->line('service_location_required')));
				if ($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					//echo encode('1');exit;
					$from_time=$to_time=$duration='';
					$userdata = web_checkuserid();
					$usid =decode($userdata['data']['id']);
					$business_id =decode($userdata['data']['business_id']);
					$time=time();
					$service_id    = decode($this->input->post('service_id'));
					$user_id     = decode($this->input->post('user_id'));
					//$location    = $this->input->post('service_location');
					$slot_date  = $this->input->post('slot_date');
					$slot_id   = $this->input->post('slot_id');
					//$duration  = $this->input->post('duration');
					//$status    = $this->input->post('status');

					// $start_end_time   = $this->input->post('start_end_time');
					// $class_time=explode('to',$start_end_time);
					// if(!empty($class_time)){
					//  $from_time=@strtotime($class_time[0]);
					//  $to_time=@strtotime($class_time[1]);
					//  $duration =round(abs($to_time -$from_time)/60,2);//in minutes
					// }
					//echo date("d M Y H:i:s",$from_time);die;
				$where="business_id=".$business_id." AND service_id=".$service_id." AND slot_id=".$slot_id." AND slot_date='".$slot_date."'";
				$appointment_data = $this->dynamic_model->getdatafromtable('business_appointment_book',$where);
				if(empty($appointment_data)){
					$serviceData =   array(
										'business_id'  =>$business_id,
										//'user_id'       =>$user_id,
										'slot_id'       =>$slot_id,
										'slot_date'     =>$slot_date,
										'service_id'   	=>$service_id,
										'service_type'  =>1,
										'slot_available_status'=>0,
										'create_dt'   	=>$time,
										'update_dt'   	=>$time
					                   );
					$business_appointment= $this->dynamic_model->insertdata('business_appointment_book',$serviceData);
				}else{
					$serviceData = array(
										'user_id'       =>$user_id,
										'slot_id'       =>$slot_id,
										'slot_date'     =>$slot_date,
										'slot_available_status'=>0,
										'update_dt'   	=>$time
					                   );
					// $where = array('id'=>$class_id,'business_id'=>$business_id);
					$business_appointment= $this->dynamic_model->updateRowWhere('business_appointment_book',$where,$serviceData);
				}
					if($business_appointment)
			        {
						$arg['status']    = 1;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line']= __line__;
					 	$arg['message']   = $this->lang->line('service_scheduled_succ');
					 	$arg['data']      = [];
			        }else{
			        	$arg['status']     = 0;
			            $arg['error_code']  = HTTP_NOT_FOUND;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('server_problem');
			        }

				}
			}
	    }
	 echo json_encode($arg);
    }
     /****************Function service_scheduled_list*******************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : service_scheduled_list
     * @description     : list of service scheduled
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function service_scheduled_list()
	{
		$arg = array();
		$userdata = web_checkuserid();
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
				//$limit    = 1;
				$offset = $limit * $page_no;
				$business_id= decode($userdata['data']['business_id']);
                //scheduled_type 0 today classes 1 upcoming classes
                $scheduled_type=  $this->input->post('scheduled_type');
				// $where="business_id=".$business_id." AND status='Active'";
				// $service_data = $this->dynamic_model->getdatafromtable('service',$where,"*",$limit,$offset,'create_dt');
				$data="service.*,business_appointment_book.slot_id,business_appointment_book.slot_date,business_appointment_book.slot_available_status,business_appointment_book.user_id as instructor_id";
			    $condition="service.business_id='".$business_id."' AND service.status='Active'";
                $on='service.id = business_appointment_book.service_id';
			    $service_data= $this->dynamic_model->getTwoTableData($data,'service','business_appointment_book',$on,$condition,$limit,$offset,"service.create_dt","DESC");
				if(!empty($service_data)){
				    foreach($service_data as $value)
		            {
		            	$servicedata['id']     = encode($value['id']);
		            	$servicedata['service_name']   = $value['service_name'];
				        $slot_info = $this->dynamic_model->getdatafromtable('business_slots',array('slot_id'=>$value['slot_id']));
		            	$servicedata['slot_id']   = $value['slot_id'];
		            	$servicedata['user_id']   =!empty($value['instructor_id']) ?  encode($value['instructor_id']) : '';
		            	//get instructor data
		            	$where2 = array('id'=>$value['instructor_id']);
				        $user_data = $this->dynamic_model->getdatafromtable('user',$where2);

		            	$servicedata['name'] = $user_data[0]['name'];
		            	$servicedata['profile_img'] = base_url().'uploads/user/'. $user_data[0]['profile_img'];
		            	$servicedata['slot_time_from'] = $slot_info[0]['slot_time_from'];
		            	$servicedata['slot_time_to'] = $slot_info[0]['slot_time_to'];
		            	$servicedata['slot_date']    = date("d M Y ",$value['slot_date']) ;
		            	$servicedata['slot_date_utc']= $value['slot_date'];
		            	$servicedata['create_dt']    = date("d M Y ",$value['create_dt']);
		            	$servicedata['create_dt_utc'] = $value['create_dt'];
		            	$servicedata['status'] =($scheduled_type==0) ? 'Today': 'upcoming';
		            	$response[]	                 = $servicedata;
		            }
					$arg['status']     = 1;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = $response;
					$arg['message']    = $this->lang->line('record_found');
				}else{
					$arg['status']     = 0;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = array();
				 	$arg['message']    = $this->lang->line('record_not_found');
				}
		    }
		  }
		}

	   echo json_encode($arg);
	}
    /****************Function register workshop **********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : register_workshop
     * @description     : register workshop like Massage/Therapy sessions
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function add_workshop()
	{
	   $arg   = array();
	   $userdata = web_checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
				$this->form_validation->set_rules('workshop_name','Workshop Name', 'required|trim', array( 'required' => $this->lang->line('workshop_name_reqired')));
				$this->form_validation->set_rules('workshop_type','Workshop Type', 'required|trim', array( 'required' => $this->lang->line('workshop_type_reqired')));
				$this->form_validation->set_rules('duration','Duration', 'required|trim', array( 'required' => $this->lang->line('service_duration_req')));
				if ($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$usid =decode($userdata['data']['id']);
					$time=time();
			        $business_id= decode($userdata['data']['business_id']);
					$workShopData = array(
									"business_id"=> $business_id,
									"user_id"=> $usid,
									"workshop_id"=> $time,
									"workshop_name"=> $this->input->post('workshop_name'),
									"duration"=> $this->input->post('duration'),
									"no_of_days"=> $this->input->post('no_of_days'),
									"workshop_type"=> decode($this->input->post('workshop_type')),
									"status"=>"Deactive",
									"description"=> $this->input->post('description'),
									"create_dt"=> $time,
									"update_dt"=> $time
					);
					$workshop= $this->dynamic_model->insertdata('business_workshop',$workShopData);
					if($workshop)
			        {
						$arg['status']    = 1;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line']= __line__;
					 	$arg['message']   = $this->lang->line('workshop_save_succ');
					 	$arg['data']      = [];
			        }else{
			        	$arg['status']     = 0;
			            $arg['error_code']  = HTTP_NOT_FOUND;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('server_problem');
			        }
				}
			}
	    }

	  echo json_encode($arg);
    }
    /****************Function Get workshop list**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : business_workshop_list
     * @description     : list of classes
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function workshop_list()
	{
	   $arg = array();
	   $userdata = web_checkuserid();
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
				$time=time();
				$usid =$userdata['data']['id'];
				$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
				$page_no= $page_no-1;
				$limit    = config_item('page_data_limit');
				$offset = $limit * $page_no;
				$business_id= decode($userdata['data']['business_id']);
                $where = array("business_id"=>$business_id);

                $response = get_all_workshop($business_id);
                if ($this->input->post('workshop_id')) {
                    $workshop_id = $this->input->post('workshop_id');
                    $collection = $this->db->get_where('business_workshop_master', array('id' => $workshop_id))->row_array(); // get_all_workshop($business_id, $workshop_id);

                    if (!empty($collection)) {

                        $schedule = $this->db->get_where('business_workshop_schdule', array('workshop_id' => $collection['id']))->result_array();
                        $resp = array();
                        if (!empty($schedule)) {
                            foreach ($schedule as $key => $value) {
                                $scheduleId = $value['id'];
                                $value['location'] = ($value['location'] == 0) ? 0 : encode($value['location']);
                                $instructor = $this->db->get_where('business_workshop_schdule_instructor', array('schedule_id' => $scheduleId))->result_array();
                                $instructor_array = array();
                                foreach ($instructor as $val) {
                                    array_push($instructor_array, encode($val['user_id']));
                                }
                                $value['instructor'] = $instructor_array;
                                array_push($resp, $value);

                            }
                        }

                        $collection['schedule'] = $resp;

                        $arg['status']     = 1;
                        $arg['error_code']  = REST_Controller::HTTP_OK;
                        $arg['error_line']= __line__;
                        $arg['data']       = $collection;
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
                /*if (!empty($response)) {
				    $workshop_data = $this->dynamic_model->getdatafromtable('business_workshop',$where,"*",$limit,$offset,'create_dt');
				    foreach($workshop_data as $value)
		            {
		            	$workshopdata['workshop_id']  = encode($value['id']);
		            	$workshopdata['workshop_name']= ucwords($value['workshop_name']);
		            	$workshopdata['from_time']    = $value['from_time'];
		            	$workshopdata['to_time']      = $value['to_time'];
		            	$workshopdata['from_time_utc'] = strtotime($value['from_time']);
		            	$workshopdata['to_time_utc']  = strtotime($value['to_time']);
		            	$workshopdata['duration']     = $value['duration'];
		            	$capicty_used                 = get_checkin_class_or_workshop_count($value['id'],2,$time);
		            	$workshopdata['capacity']     = $capicty_used.'/'.$value['capacity'];
		            	$workshopdata['location']     = $value['location'];
		            	$workshopdata['workshop_type']= get_categories($value['workshop_type']);
		                $instructor_data[]             =  $this->instructor_list_details($business_id,2,$value['id']);
		            	$workshopdata['instructor_details']    =  !empty($instructor_data[0]) ? $instructor_data[0] : json_decode('{}') ;
		            	$workshopdata['create_dt']    = date("d M Y ",$value['create_dt']);
		            	$workshopdata['start_date']    = date("d M Y ",strtotime($value['start_date']));
		            	$workshopdata['end_date']    = date("d M Y ",strtotime($value['end_date']));
		            	$workshopdata['create_dt_utc'] = $value['create_dt'];
		            	$workshopdata['start_date_utc']= strtotime($value['start_date']);
		            	$workshopdata['end_date_utc']= strtotime($value['end_date']);
		            	$workshopdata['status'] = $value['status'];
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
				}*/
		    }
		  }
		}

	   echo json_encode($arg);
	}

	public function today_workshop_list()
	{
	   $arg = array();
	   $userdata = web_checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{




				$response=array();
				$time=time();
				$usid =$userdata['data']['id'];

				$business_id= decode($userdata['data']['business_id']);
				$date=date('Y-m-d');
                $where=array("business_id"=>$business_id,'start_date'=>$date);
				$workshop_data = $this->dynamic_model->getdatafromtable('business_workshop',$where);
				//print_r($workshop_data);die;
				if(!empty($workshop_data)){
				    foreach($workshop_data as $value)
		            {
		            	$workshopdata['workshop_id']  = encode($value['id']);
		            	$workshopdata['workshop_name']= ucwords($value['workshop_name']);
		            	$workshopdata['from_time']    = $value['from_time'];
		            	$workshopdata['to_time']      = $value['to_time'];
		            	$workshopdata['from_time_utc'] = strtotime($value['from_time']);
		            	$workshopdata['to_time_utc']  = strtotime($value['to_time']);
		            	$workshopdata['duration']     = $value['duration'];
		            	$capicty_used                 = get_checkin_class_or_workshop_count($value['id'],2,$time);
		            	$workshopdata['total_capacity']     = $value['capacity'];
		            	$workshopdata['capacity_used']     = $capicty_used;

		            	$workshopdata['location']     = $value['location'];
		            	$workshopdata['workshop_type']= get_categories($value['workshop_type']);
		                $instructor_data             =  $this->instructor_list_details($business_id,2,$value['id']);
		            	$workshopdata['instructor_details']    = !empty($instructor_data[0]) ? $instructor_data[0] : json_decode('{}') ;;
		            	$workshopdata['create_dt']    = date("d M Y ",$value['create_dt']);
		            	$workshopdata['start_date']    = date("d M Y ",strtotime($value['start_date']));
		            	$workshopdata['end_date']    = date("d M Y ",strtotime($value['end_date']));
		            	$workshopdata['create_dt_utc'] = $value['create_dt'];
		            	$workshopdata['start_date_utc']= strtotime($value['start_date']);
		            	$workshopdata['end_date_utc']= strtotime($value['end_date']);
		            	$workshopdata['status'] = $value['status'];
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

	   echo json_encode($arg);
	}
	    /****************Function workshop scheduling**********
     * @type            : Function
     * @Author          : Arpit
     * @function name   : workshop_scheduling
     * @description     : workshop scheduling
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function workshop_scheduling() {
		$arg   = array();
	   	$userdata = web_checkuserid();
	   	if($userdata['status'] != 1){
			$arg = $userdata;
		} else {

			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
				$this->form_validation->set_rules('workshopName','Name', 'required|trim|max_length[200]', array( 'required' => 'Name is required'));
                $this->form_validation->set_rules('workShopCapacity', 'Workshop Capacity', 'required|trim|numeric', array(
                    'required' => 'Workshop capacity is required',
                    'numeric' => 'Workshop capacity is required',
                ));
				$this->form_validation->set_rules('workshopClientVisibility','Visibility', 'required|trim', array( 'required' => 'Visibility is required'));
				$this->form_validation->set_rules('workshopDescription','Description', 'required|trim', array( 'required' => 'Description is required'));
				$this->form_validation->set_rules('workshopPrice','Price', 'required|trim', array( 'required' => 'Price is required'));
				$this->form_validation->set_rules('workshopTax1','Tax1', 'required|trim', array( 'required' => 'Tax1 is required'));
				$this->form_validation->set_rules('tax1_rate','Tax1 Rate', 'required|trim', array( 'required' => 'Tax1 Rate is required'));
				$this->form_validation->set_rules('workshopTax2','Tax2', 'required|trim', array( 'required' => 'Tax2 is required'));
				$this->form_validation->set_rules('tax2_rate','Tax2 Rate', 'required|trim', array( 'required' => 'Tax2 Rate is required'));
				// $this->form_validation->set_rules('slot[]','Slots', 'required', array( 'required' => 'Slots is required'));

				if ($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
					$arg['message'] = get_form_error($this->form_validation->error_array());

				} else {

					$userdata = web_checkuserid();
					$usid =decode($userdata['data']['id']);
					$business_id =decode($userdata['data']['business_id']);
					$time = time();

                    $workshop_status = $this->input->post('workshopStatus');
					/*
                    $waiting_status = $this->input->post('workshopWaiting');
                    $waiting_count = $this->input->post('waitCount');*/

					$workshop = array(
						'business_id'	    =>	$business_id,
						'user_id'		    =>	$usid,
						'name'     		    =>  $this->input->post('workshopName'),
                        'workshop_capacity' =>  $this->input->post('workShopCapacity'),
						'visibility'        =>  $this->input->post('workshopClientVisibility'),
						'description'       =>  $this->input->post('workshopDescription'),
						'price'   		    =>  $this->input->post('workshopPrice'),
						'tax1'   		    =>  $this->input->post('workshopTax1'),
						'tax1_rate'   	    =>  $this->input->post('tax1_rate'),
						'tax2'   		    =>  $this->input->post('workshopTax2'),
						'tax2_rate'   	    =>  $this->input->post('tax2_rate'),
						'create_dt'         =>  $time,
						'update_dt'         =>  $time
					);

                    $workShopId = $this->dynamic_model->insertdata('business_workshop_master', $workshop);
                    /*$passesInfo = $this->input->post('passes');
                    $pass_assign = array();
                    for ($i = 0; $i < count($passesInfo); $i++) {
                        array_push($pass_assign, array(
                            'user_id'	    =>	$usid,
                            'business_id'	=>  $business_id,
                            'class_id'	    =>	$workShopId,
                            'pass_id'	    =>	decode($passesInfo[$i]),
                            'pass_type'     =>  1,
                            'create_dt'	    =>	$time,
                            'update_dt'	    =>	$time,
                        ));
                    }

                    $this->db->insert_batch('business_passes_associates', $pass_assign);*/
                    $slots = $this->input->post('slot');

					$arrayInfo = array();
					for ($i = 0; $i < count($slots); $i++) {
					    $rowInfo = $slots[$i];
						$insertSchdule = array(
							'workshop_id'	=>	$workShopId,
							'capacity'		=>	$rowInfo['capacity'],
							'schedule_date'	=>	$rowInfo['service'],
							'schedule_dates'=>	date('Y-m-d', $rowInfo['service']),
							'location'		=>	($rowInfo['location'] == '0') ? 0 : decode($rowInfo['location']),
							'start'			=>	$rowInfo['start'],
							'end'			=>	$rowInfo['end'],
							'address'		=>	$rowInfo['address'],
							// 'waiting_status'=>  $waiting_status,
							// 'waiting_count' =>  $waiting_count,
							'status'        =>  ($workshop_status === 'Active') ? 'Active' : 'Deactive',
							'create_dt'     => 	$time,
							'update_dt'     => 	$time
						);

						$workShopScheduleId = $this->dynamic_model->insertdata('business_workshop_schdule', $insertSchdule);
                        $instructor = $rowInfo['instructor'];
						$insertInstructor = array();
						for ($j = 0; $j < count($instructor); $j++) {
						    $rowObject = $instructor[$j];
						    array_push($insertInstructor, array(
                                'schedule_id'	=>	$workShopScheduleId,
                                'user_id'		=>	decode($rowObject),
                                'create_dt'     => 	$time,
                                'update_dt'     => 	$time
                            ));
                        }
						$this->db->insert_batch('business_workshop_schdule_instructor', $insertInstructor);
                    }

                    $arg['status']     = 1;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['message']    = 'Workshop scheduled successfully.';


				}

			}

		}
		echo json_encode($arg);
	}

    public function workshop_scheduling_update() {
        $arg   = array();
        $userdata = web_checkuserid();
        if($userdata['status'] != 1){
            $arg = $userdata;
        } else {

            $_POST = json_decode(file_get_contents("php://input"), true);
            if($_POST)
            {
                $this->form_validation->set_rules('workshop_id','Workshop', 'required|trim|numeric', array(
                    'required' => 'Workshop is required',
                    'numeric' => 'Workshop is required',
                ));
                $this->form_validation->set_rules('workShopCapacity', 'Workshop Capacity', 'required|trim|numeric', array(
                    'required' => 'Workshop capacity is required',
                    'numeric' => 'Workshop capacity is required',
                ));

                $this->form_validation->set_rules('workshopName','Name', 'required|trim|max_length[200]', array( 'required' => 'Name is required'));
                $this->form_validation->set_rules('workshopClientVisibility','Visibility', 'required|trim', array( 'required' => 'Visibility is required'));
                $this->form_validation->set_rules('workshopDescription','Description', 'required|trim', array( 'required' => 'Description is required'));
                $this->form_validation->set_rules('workshopPrice','Price', 'required|trim', array( 'required' => 'Price is required'));
                $this->form_validation->set_rules('workshopTax1','Tax1', 'required|trim', array( 'required' => 'Tax1 is required'));
                $this->form_validation->set_rules('tax1_rate','Tax1 Rate', 'required|trim', array( 'required' => 'Tax1 Rate is required'));
                $this->form_validation->set_rules('workshopTax2','Tax2', 'required|trim', array( 'required' => 'Tax2 is required'));
                $this->form_validation->set_rules('tax2_rate','Tax2 Rate', 'required|trim', array( 'required' => 'Tax2 Rate is required'));
                // $this->form_validation->set_rules('slot[]','Slots', 'required', array( 'required' => 'Slots is required'));

                if ($this->form_validation->run() == FALSE)
                {
                    $arg['status']  = 0;
                    $arg['error_code'] = 0;
                    $arg['error_line']= __line__;
                    $arg['message'] = get_form_error($this->form_validation->error_array());

                } else {

                    $userdata = web_checkuserid();
                    $usid =decode($userdata['data']['id']);
                    $business_id =decode($userdata['data']['business_id']);
                    $time = time();
                    $workShopId = $this->input->post('workshop_id');
                    $workshop_status = $this->input->post('workshopStatus');
                    /*
                    $waiting_status = $this->input->post('workshopWaiting');
                    $waiting_count = $this->input->post('waitCount');*/

                    $workshop = array(
                        'name'     		    =>  $this->input->post('workshopName'),
                        'workshop_capacity' =>  $this->input->post('workShopCapacity'),
                        'visibility'        =>  $this->input->post('workshopClientVisibility'),
                        'description'       =>  $this->input->post('workshopDescription'),
                        'price'   		    =>  $this->input->post('workshopPrice'),
                        'tax1'   		    =>  $this->input->post('workshopTax1'),
                        'tax1_rate'   	    =>  $this->input->post('tax1_rate'),
                        'tax2'   		    =>  $this->input->post('workshopTax2'),
                        'tax2_rate'   	    =>  $this->input->post('tax2_rate'),
                        'update_dt'         => $time
                    );

                    $this->dynamic_model->updateRowWhere('business_workshop_master', array('id' => $workShopId), $workshop);
                    $slots = $this->input->post('slot');

                    $arrayInfo = array();
                    for ($i = 0; $i < count($slots); $i++) {
                        $rowInfo = $slots[$i];
                        $slotId = $rowInfo['updateId'];
                        $updateSchedule = array(
                            'capacity'		=>	$rowInfo['capacity'],
                            'schedule_date'	=>	$rowInfo['service'],
                            'schedule_dates'=>	date('Y-m-d', $rowInfo['service']),
                            'location'		=>	($rowInfo['location'] == '0') ? 0 : decode($rowInfo['location']),
                            'start'			=>	$rowInfo['start'],
                            'end'			=>	$rowInfo['end'],
                            'address'		=>	$rowInfo['address'],
                            'status'        =>  ($workshop_status === 'Active') ? 'Active' : 'Deactive',
                            'update_dt'     => 	$time
                        );
                        if ($slotId == 0) {
                            $updateSchedule['workshop_id']	=	$workShopId;
                            $updateSchedule['create_dt']    =   $time;
                            $workShopScheduleId = $this->dynamic_model->insertdata('business_workshop_schdule', $updateSchedule);
                        } else {
                            $workShopScheduleId = $this->dynamic_model->updateRowWhere('business_workshop_schdule', array('id' => $slotId), $updateSchedule);
                        }

                        $instructor = $rowInfo['instructor'];
                        $insertInstructor = array();
                        $this->dynamic_model->deletedata('business_workshop_schdule_instructor', array('schedule_id' => $workShopScheduleId));
                        for ($j = 0; $j < count($instructor); $j++) {
                            $rowObject = $instructor[$j];
                            array_push($insertInstructor, array(
                                'schedule_id'	=>	$workShopScheduleId,
                                'user_id'		=>	decode($rowObject),
                                'create_dt'     => 	$time,
                                'update_dt'     => 	$time
                            ));
                        }
                        $this->db->insert_batch('business_workshop_schdule_instructor', $insertInstructor);
                    }

                    $arg['status']     = 1;
                    $arg['error_code']  = HTTP_OK;
                    $arg['error_line']= __line__;
                    $arg['message']    = 'Workshop scheduled successfully.';


                }

            }

        }
        echo json_encode($arg);
    }

	public function workshop_scheduling_old()
	{
	   $arg   = array();
	   $userdata = web_checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{

			    $this->form_validation->set_rules('workshop_id','Class Id', 'required|trim', array( 'required' => $this->lang->line('workshop_id_required')));
			    // $this->form_validation->set_rules('user_id','User Id', 'required|trim', array( 'required' => $this->lang->line('user_id_required')));
			     $this->form_validation->set_rules('start_date','Start date','required|trim', array( 'required' => $this->lang->line('start_date_required')));
			     //$this->form_validation->set_rules('end_date','End date','required|trim', array( 'required' => $this->lang->line('end_date_required')));
			    // $this->form_validation->set_rules('workshop_date','Workshop date','required|trim', array( 'required' => $this->lang->line('workshop_date_required')));
			    //$this->form_validation->set_rules('from_time','From time','required|trim', array( 'required' => $this->lang->line('from_time_required')));
			    //$this->form_validation->set_rules('to_time','To time','required|trim', array( 'required' => $this->lang->line('to_time_required')));
			    //$this->form_validation->set_rules('day_id','Day id','required|trim', array( 'required' => $this->lang->line('day_id_required')));
                $this->form_validation->set_rules('workshop_location','Work shop location', 'required|trim', array('required' => $this->lang->line('workshop_location_required')));
			    // $this->form_validation->set_rules('capacity','Capacity', 'required|trim', array( 'required' => $this->lang->line('capacity_reqired')));
			    $this->form_validation->set_rules('passes_id','Passes Id', 'required|trim', array('required' => $this->lang->line('passesid_required')));
				$this->form_validation->set_rules('workshop_status','Work shop status', 'required|trim', array('required' => $this->lang->line('workshop_status_required')));
				$this->form_validation->set_rules('workshop_repeat_times','Work shop repeat times', 'required|trim', array('required' => $this->lang->line('workshop_repeat_times_required')));
				$this->form_validation->set_rules('workshop_days_prior_signup','Work shop days prior signup', 'required|trim', array('required' => $this->lang->line('workshop_days_prior_signup_required')));
				$this->form_validation->set_rules('workshop_waitlist_overflow','Work shop wait list overflow', 'required|trim', array('required' => $this->lang->line('workshop_waitlist_overflow_required')));

				if ($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					//echo encode('1');exit;
					$from_time=$to_time=$duration='';
					$userdata = web_checkuserid();
					$usid =decode($userdata['data']['id']);
					$business_id =decode($userdata['data']['business_id']);
					$time=time();
					$workshop_id = decode($this->input->post('workshop_id'));
				   // $workshop_id =7;
					$user_id     = $this->input->post('user_id');
					$user_id     = !empty($user_id) ? decode($user_id):'0';
					$location    = decode($this->input->post('workshop_location'));
					//$workshop_date= $this->input->post('workshop_date');
					//$from_time   = $this->input->post('from_time');
					//$to_time     = $this->input->post('to_time');
					//$capacity    = $this->input->post('capacity');
					$start_date  = date("Y-m-d",$this->input->post('start_date'));
					//$end_date    = date("Y-m-d",$this->input->post('end_date'));
					$time_slot      = $this->input->post('time_slot');
					$passes_id   = multiple_decode_ids($this->input->post('passes_id'),1);
					$workshop_status      = $this->input->post('workshop_status');
					$workshop_repeat_times      = $this->input->post('workshop_repeat_times');
					$workshop_days_prior_signup      = $this->input->post('workshop_days_prior_signup');
					$workshop_waitlist_overflow      = $this->input->post('workshop_waitlist_overflow');
					//get locations
					$where1 = array('business_id'=>$business_id,'id'=>$location);
					$room_location = $this->dynamic_model->getdatafromtable('business_location',$where1);
					$location_name=!empty($room_location[0]['location_name']) ? $room_location[0]['location_name']:'';
					$capacity=!empty($room_location[0]['capacity']) ? $room_location[0]['capacity']:'';

					$workshopdate=date("Y-m-d",strtotime($start_date));
					//$end_date=date("Y-m-d",strtotime($end_date));
					$cal_send  =  round($workshop_repeat_times/count($time_slot));
					$cal_end  =  fmod($workshop_repeat_times,count($time_slot));
					$total_days = ($cal_send * 7) + $cal_end;
					$end_date  = date('Y-m-d', strtotime($start_date. ' + '.$total_days.' days'));
					$classData =   array(
										//'from_time'   	=>$from_time,
										//'to_time'       =>$to_time,
										//'instructor_id' =>$user_id,
										//'duration'      =>$duration,
										'capacity'   	=>$capacity,
										'location'   	=>$location_name,
										'status'   	    =>"Active",
										'start_date'   	=>$workshopdate,
										'end_date'   	=>$end_date,
										//'day_id'		=> $day_id,
										'status'		=> $workshop_status,
										'workshop_repeat_times'		=> $workshop_repeat_times,
										'workshop_days_prior_signup'		=> $workshop_days_prior_signup,
										'workshop_waitlist_overflow'		=> $workshop_waitlist_overflow,
										'update_dt'   	=>$time
					                   );
						$where = array('id'=>$workshop_id,'business_id'=>$business_id);
						$business_workshop= $this->dynamic_model->updateRowWhere('business_workshop',$where,$classData);
						if($business_workshop) {
							if(!empty($time_slot)){
									$date = $start_date;
											$unixTimestamp = strtotime($date);
											$dayOfWeek = date("l", $unixTimestamp);

											if($time_slot[0]['day_id']=='1'){
												$weekU = 'Monday';
												$weekL = 'monday';
											}
											if($time_slot[0]['day_id']=='2'){
												$weekU = 'Tuesday';
												$weekL = 'tuesday';
											}
											if($time_slot[0]['day_id']=='3'){
												$weekU = 'Wednesday';
												$weekL = 'wednesday';
											}
											if($time_slot[0]['day_id']=='4'){
												$weekU = 'Thursaday';
												$weekL = 'thursaday';
											}
											if($time_slot[0]['day_id']=='5'){
												$weekU = 'Friday';
												$weekL = 'friday';
											}
											if($time_slot[0]['day_id']=='6'){
												$weekU = 'Saturday';
												$weekL = 'saturday';
											}
											if($time_slot[0]['day_id']=='7'){
												$weekU = 'Sunday';
												$weekL = 'sunday';
											}

							// if($dayOfWeek != $weekU){

							// 	$date = new DateTime($date);

							// 	$date->modify('next '.$weekL);
							// 	$date = $date->format('Y-m-d');


							// }else{
							// 	$date = new DateTime($date);
							// 	$date->modify('next '.$weekL);
							// 	$date =$date->format('Y-m-d');

							// }
							for ($i=0; $i < $class_repeat_times ; $i++) {
								$j=0;
								foreach ($time_slot as $key => $value) {



									if($dayOfWeek != $weekU){

										$date = new DateTime($date);
										$date->modify('next '.$weekL);
										$date = $date->format('Y-m-d');


									}else{
											// $date = new DateTime($date);
											// $date->modify('next '.$weekL);
											// $date =$date->format('Y-m-d');

										if($value['day_id']=='1'){
										$weekL = 'monday';
										}
										if($value['day_id']=='2'){
											$weekL = 'tuesday';
										}
										if($value['day_id']=='3'){
											$weekL = 'wednesday';
										}
										if($value['day_id']=='4'){
											$weekL = 'thursaday';
										}
										if($value['day_id']=='5'){
											$weekL = 'friday';
										}
										if($value['day_id']=='6'){
											$weekL = 'saturday';
										}
										if($value['day_id']=='7'){
											$weekL = 'sunday';
										}
										if($j==0 && $i==0){
											$date = new DateTime($date);
											$date =$date->format('Y-m-d');

											$where2 = array('id'=>$workshop_id,'business_id'=>$business_id);
											$startDateArray = array('start_date'=> $date);
											$business_class= $this->dynamic_model->updateRowWhere('business_workshop',$where2,$startDateArray);
										}else{
											$date = new DateTime($date);
											$date->modify('next '.$weekL);
											$date =$date->format('Y-m-d');
										}

									}

				        			$workshopSlotArray = array(
													"workshop_id"=> $workshop_id,
													"business_id"=> $business_id,
													"from_time"=> $value['from_time'],
										            "to_time"=> $value['to_time'],
										            "day_id"=> $value['day_id'],
										            "instructor_id"=> decode($value['instructor_id']),
										            "scheduled_date"=>$date,
														);
								$business_sevice= $this->dynamic_model->insertdata('workshop_scheduling_time',$workshopSlotArray);
				        		$j++;}


							}

							$endDateData= $this->studio_model->getServiceEndDate('workshop_scheduling_time',$workshop_id,$business_id);

							$where2 = array('id'=>$workshop_id,'business_id'=>$business_id);
							$endDateArray = array('end_date'=> $endDateData[0]['scheduled_date']);
							$business_class= $this->dynamic_model->updateRowWhere('business_workshop',$where2,$endDateArray);

							$arg['status']     = 1;
							$arg['error_code']  = HTTP_OK;
							$arg['error_line']= __line__;
							$arg['message']    = 'Workshop scheduled successfully.';

			        	}


						$where2=array("business_id"=>$business_id);
						$business_passes= $this->dynamic_model->getWhereInData('business_passes','id',$passes_id,$where2);
						if(!empty($business_passes)){
							foreach ($business_passes as $value){
                            $where3=array("id"=>$value['id'],"business_id"=>$business_id);
                            $updateData= array(
								'service_id'   	=>$workshop_id,
								'service_type'  =>2,
								'update_dt'   	=>$time
			                   );
						   $this->dynamic_model->updateRowWhere('business_passes',$where3,$updateData);
							}
						}
						$arg['status']    = 1;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line']= __line__;
					 	$arg['message']   = $this->lang->line('workshop_scheduled_succ');
					 	$arg['data']      = [];
			        }else{
			        	$arg['status']     = 0;
			            $arg['error_code']  = HTTP_NOT_FOUND;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('server_problem');
			        }
			}
	    }
	 }
	echo json_encode($arg);
}
       /****************Function workshop_scheduled_list*******************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : worshop_scheduled_list
     * @description     : list of workshop scheduled
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function workshop_scheduled_list()
	{
		$arg = array();
		$userdata = web_checkuserid();
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
              $this->form_validation->set_rules('scheduleDate', 'Schedule Date', 'required|numeric',array(
                  'required' => 'Schedule date is required',
                  'numeric' => 'Schedule date is required',
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
				$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
				$page_no= $page_no-1;
				$limit    = config_item('page_data_limit');
				//$limit    = 1;
				$offset = $limit * $page_no;
				$business_id= decode($userdata['data']['business_id']);
                //scheduled_type 0 today classes 1 upcoming classes
                $scheduled_type=  $this->input->post('scheduled_type');
				//$where="business_id=".$business_id." AND instructor_id !=''";
				$where = "business_workshop_master.business_id=".$business_id;
				$scheduleDate = $this->input->post('scheduleDate');

                $where .=  ' AND business_workshop_schdule.schedule_dates = "'.date('Y-m-d', $scheduleDate).'"';

                $query = "SELECT business_workshop_schdule.id, business_workshop_master.description, business_workshop_master.tax1, business_workshop_master.tax2, business_workshop_master.tax1_rate, business_workshop_master.tax2_rate, business_workshop_master.id as workshop_id, business_workshop_schdule.waiting_status, business_workshop_schdule.waiting_count, business_workshop_master.name, business_workshop_master.visibility, business_workshop_master.price, business_workshop_master.workshop_capacity as capacity, business_workshop_schdule.schedule_date, business_workshop_schdule.location, business_workshop_schdule.status as status, business_workshop_schdule.start, business_workshop_schdule.end, CASE WHEN business_workshop_schdule.location = 0 THEN 'Other' ELSE business_location.location_name END location_name, business_workshop_schdule.address  FROM `business_workshop_schdule` JOIN business_workshop_master on (business_workshop_master.id = business_workshop_schdule.workshop_id) LEFT JOIN business_location on (business_location.id = business_workshop_schdule.location)  WHERE ".$where." ORDER BY business_workshop_schdule.schedule_dates DESC";

				$collection = $this->db->query($query)->result_array();
                $response = array();
				if (!empty($collection)) {

				    foreach($collection as $value) {
                        $imageUrl = site_url() . 'uploads/user/';
                        $scheduleId = $value['id'];
                        $workshopId = $value['workshop_id'];
                        $query = 'SELECT user.id, user.name, user.lastname, concat("'.$imageUrl.'", user.profile_img) as profile_img FROM `business_workshop_schdule_instructor` JOIN user on (user.id = business_workshop_schdule_instructor.user_id) where business_workshop_schdule_instructor.schedule_id = '.$scheduleId;
                        $used = $this->db->get_where('user_booking', array(
                            //'class_id' => $scheduleId,
                            'service_id' => $workshopId,
                            'service_type' => '4',
                            'status' => 'Success'
                        ))->num_rows();
                        $value['used'] = $used;
                        $value['customer_detail'] = $this->db->query("SELECT user.name, concat('".$imageUrl."', user.profile_img) as profile_img, user.lastname, user.gender, DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(), user.date_of_birth)), '%Y')+0 AS age, user_booking.status FROM `user_booking` JOIN user on (user.id = user_booking.user_id) WHERE user_booking.service_type = 4 ANd user_booking.business_id = ".$business_id." AND user_booking.service_id = ".$workshopId)->result_array();
                        $value['instructorDetails'] = $this->db->query($query)->result_array();
                        array_push($response, $value);
                    }
                }

				if (!empty($response)) {
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

				/* $workshop_data = $this->dynamic_model->getdatafromtable('business_workshop',$where,"*",$limit,$offset,'create_dt');
				if(!empty($workshop_data)){
				    foreach($workshop_data as $value)
		            {
		            	$from_time=(!empty($value['from_time']))? $value['from_time'] : "";
		            	$to_time=(!empty($value['to_time']))? $value['to_time'] : "";
		            	$start_date=(!empty($value['start_date']))? $value['start_date'] : "";
						$end_date=(!empty($value['end_date']))? $value['end_date'] : "";

		            	$workshopdata['workshop_id']     = encode($value['id']);
		            	$workshopdata['workshop_name']   = $value['workshop_name'];
		            	$workshopdata['duration']     = $value['duration'];
		            	$workshopdata['capacity']     = $value['capacity'];
		            	$workshopdata['location']     = $value['location'];
		            	$workshopdata['from_time']    =  ($from_time !=='') ? $from_time :'';
		            	$workshopdata['to_time']      =  ($to_time !=='') ? $to_time :'';
		            	$workshopdata['from_time_utc']= strtotime($from_time);
		            	$workshopdata['to_time_utc']  = strtotime($to_time);
		            	$workshopdata['workshop_type']   = get_categories($value['workshop_type']);
		            	$workshopdata['workshop_status']		= $value['status'];
		            	$workshopdata['workshop_repeat_times']		= $value['workshop_repeat_times'];
						$workshopdata['workshop_days_prior_signup']		= $value['workshop_days_prior_signup'];
						$workshopdata['workshop_waitlist_overflow']		= $value['workshop_waitlist_overflow'];

                        $singned_customer= $this->studio_model->get_all_signed_workshops($business_id,$value['id'],5,0);
                        $total_singned_customer= $this->studio_model->get_all_signed_workshops($business_id,$value['id']);
                        $total_customer=(!empty($total_singned_customer)) ? count($total_singned_customer) : '0';
			            $client_details=array("client_details"=>$singned_customer,"total_count"=>"$total_customer");
			            $workshopdata['client_info']= $client_details;
		            	$workshopdata['start_date']    =  ($start_date !=='') ? date("d M Y ",strtotime($start_date)) :'';
		            	$workshopdata['end_date']    =  ($end_date !=='') ? date("d M Y ",strtotime($end_date)) :'';
		            	$workshopdata['start_date_utc']= strtotime($start_date);
		            	$workshopdata['end_date_utc']= strtotime($start_date);
		            	$workshopdata['create_dt']    = date("d M Y ",$value['create_dt']);
		            	$workshopdata['create_dt_utc'] = $value['create_dt'];
		            	$workshopdata['status'] =($scheduled_type==0) ? 'Today': 'upcoming';
		            	$response[]	                 = $workshopdata;
		            }
					$arg['status']     = 1;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = $response;
					$arg['message']    = $this->lang->line('record_found');
				}else{
					$arg['status']     = 0;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = array();
				 	$arg['message']    = $this->lang->line('record_not_found');
				} */
		    }
		  }
		}

	   echo json_encode($arg);
	}
    /****************Function Cancel workshop **********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : search_customer_for_workshop
     * @description     : get customer list according to workshop
     * @param           : pageid, search_val, workshop_id
     * @return          : null
     * ********************************************************** */
    public function search_customer_for_workshop() {
        $arg = array();
        $userdata = web_checkuserid();
        if($userdata['status'] != 1){
            $arg = $userdata;
        } else {
            $_POST = json_decode(file_get_contents("php://input"), true);
            if ($_POST) {
                $this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
                    'required' => $this->lang->line('page_no'),
                    'numeric' => $this->lang->line('page_no_numeric'),
                ));
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
                } else {
                    $time_zone =  $this->input->get_request_header('Timezone', true);
                    $time_zone =  $time_zone ? $time_zone : 'UTC';
                    date_default_timezone_set($time_zone);
                    $response=array();
                    $business_id    = decode($userdata['data']['business_id']);
                    $userId         = decode($userdata['data']['id']);
                    $page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
                    $page_no= $page_no-1;
                    $limit    = config_item('page_data_limit');
                    $offset = $limit * $page_no;
                    $search_val=  $this->input->post('search_val');
                    $workshop_id=$this->input->post('workshop_id');

                    $getExistCustomer = "SELECT GROUP_CONCAT(DISTINCT  user_booking.user_id) as user_id  FROM `user_booking` WHERE `service_type` = 4 AND service_id = ".$workshop_id." AND status = 'Success'";
                    $customerCheck = $this->db->query($getExistCustomer);

                    $where= ' mobile_verified = 1 AND email_verified = 1 AND (user.name LIKE "%'.$search_val.'%" OR user.lastname LIKE "%'.$search_val.'%" OR user.mobile LIKE "%'.$search_val.'%" OR user.email LIKE "%'.$search_val.'%")';
                    if ($customerCheck->num_rows()) {
                        $customer = $customerCheck->row_array()['user_id'];
                        if ($customer !== null) {
                            $where .= ' AND id NOT IN ('.$customer.')';
                        }

                    }
                    $client_data = $this->dynamic_model->getdatafromtable('user',$where, '*', $limit, $offset);
                    if($client_data) {
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

    public function buy_now_workshop_cash() {
        $arg = array();
        $userdata = web_checkuserid();
        if($userdata['status'] != 1){
            $arg = $userdata;
        } else {
            $_POST = json_decode(file_get_contents("php://input"), true);
            if ($_POST) {
                $this->form_validation->set_rules('workshop_id','Workshop Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
                $this->form_validation->set_rules('schedule_id','Schedule Id', 'required|trim', array( 'required' => 'Schedule id is required'));
                $this->form_validation->set_rules('customer_id','Customer Id', 'required|trim', array( 'required' => $this->lang->line('customer_id_required')));
                $this->form_validation->set_rules('transaction_id','Transaction Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
                $this->form_validation->set_rules('payment_transaction_id','Transaction Note', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
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

                    $amount = $this->input->post('amount');
                    $customer_id = $this->input->post('customer_id');
                    $workshop_id = $this->input->post('workshop_id');
                    $transaction_id = $this->input->post('transaction_id');
                    $payment_transaction_id =$this->input->post('payment_transaction_id');

                    $business_id    = decode($userdata['data']['business_id']);
                    $userId         = decode($userdata['data']['id']);

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
						'class_id' => $this->input->post('schedule_id'),
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
                    $imageUrl = site_url() . 'uploads/user/';
                    $arg['data']      = $this->db->query("SELECT user.name, concat('".$imageUrl."', user.profile_img) as profile_img, user.lastname, user.gender, DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(), user.date_of_birth)), '%Y')+0 AS age, user_booking.status FROM `user_booking` JOIN user on (user.id = user_booking.user_id) WHERE user_booking.service_type = 4 ANd user_booking.business_id = ".$business_id." AND user_booking.service_id = ".$workshop_id)->result_array();;
                }
            } else {
                $arg['status']    = 0;
                $arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
                $arg['error_line']= __line__;
                $arg['message']   = '';
                $arg['data']      =json_decode('{}');
            }
        }
        echo json_encode($arg);
    }

    public function clover_buy_now_workshop() {
        $arg = array();
        $userdata = web_checkuserid();
        if($userdata['status'] != 1){
            $arg = $userdata;
        } else {
            $_POST = json_decode(file_get_contents("php://input"), true);
            if ($_POST) {
                $this->form_validation->set_rules('workshop_id','Workshop Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
                $this->form_validation->set_rules('schedule_id','Schedule Id', 'required|trim', array( 'required' => 'Schedule id is required'));
                $this->form_validation->set_rules('customer_id','Customer Id', 'required|trim', array( 'required' => $this->lang->line('customer_id_required')));
                //$this->form_validation->set_rules('transaction_id','Transaction Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
                //$this->form_validation->set_rules('payment_transaction_id','Transaction Note', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
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

                    $amount = $this->input->post('amount');
                    $customer_id = $this->input->post('customer_id');
                    $workshop_id = $this->input->post('workshop_id');
                   // $transaction_id = $this->input->post('transaction_id');
                    //$payment_transaction_id =$this->input->post('payment_transaction_id');

                    $business_id    = decode($userdata['data']['business_id']);
                    $userId         = decode($userdata['data']['id']);

                    $where = array('id' => $workshop_id);
                    $product_data = $this->dynamic_model->getdatafromtable('business_workshop_master',$where);

                    $Amt = 0;
                    $usid = $userdata['data']['id'];
                    $name = $userdata['data']['name'];
                    $lastname = $userdata['data']['lastname'];
                    $time = time();
                    $pass_start_date = $pass_end_date = $pass_status='';


                    $token = $this->input->post('token');
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
					$amount       = $amount;
					$taxAmount    = 0;
					// $res  = clover_api_payment_checkout($user_cc_no,$user_cc_mo,$user_cc_yr,$user_cc_cvv,$user_zip,$amount,$taxAmount,$marchant_id,$clover_key,$access_token,$currency,$token);
					//var_dump($res);die;


					/*object(stdClass)#29 (2) {
					 ["message"]=>
					 string(20) "402 Payment Required"
					 ["error"]=>
					 object(stdClass)#28 (4) {
					   ["code"]=>
					   string(13) "card_declined"
					   ["message"]=>
					   string(29) "DECLINED: No reason provided."
					   ["charge"]=>
					   string(13) "1X3D7FTB3WZ88"
					   ["declineCode"]=>
					   string(15) "issuer_declined"
					 }
					}*/

					//{ "id" : "R48Q7GPMY2FQY", "amount" : 1800, "amount_refunded" : 0, "currency" : "usd", "created" : 1616661109846, "captured" : true, "ref_num" : "108400500020", "auth_code" : "OK2809", "outcome" : { "network_status" : "approved_by_network", "type" : "authorized" }, "paid" : true, "status" : "succeeded", "source" : { "id" : "clv_1TSTS1iWemARMj4AXHxHikLV", "brand" : "AMEX", "cvc_check" : "unchecked", "exp_month" : "11", "exp_year" : "2025", "first6" : "378282", "last4" : "0005" } }

					//{"message":"400 Bad Request","error":{"code":"token_already_used","message":"You cannot use a clover token more than once unless it is marked as multipay."}}


					//{"paymentId":"30PFKD66YXNCP","result":"APPROVED","authCode":"OK7823","token":"DGY73XR6DDDF0","vaultedCard":{"first6":"411111","last4":"1111","expirationDate":"0321","token":"1894469479681111"}}

					//echo $res['message'];die;
				// if(@$res->status == 'succeeded')
				if(true)
				{
						$where = array('user_id' => $usid,
							'business_id' => $business_id,
						);
						$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);

						$ref_num    = getuniquenumber();
						$payment_id = time(); //!empty($res->id) ? $res->id : $ref_num;
						$authorizing_merchant_id = time(); //$res->source->id;
						$payment_type   = 'Card';
						$payment_method = 'Online';
						$amount         = $amount;

					$transaction_data = array(
						'user_id'           =>	$usid,
						'amount'            =>	$amount,
						'trx_id'           =>	$payment_id,
						'order_number'     =>	$time,
						'transaction_type' =>	4,
						'payment_status'   =>	"Success",
						'saved_card_id'    =>	0,
						'create_dt'        =>	$time,
						'update_dt'        =>	$time,
						'authorizing_merchant_id' => $authorizing_merchant_id,
                        'payment_type' => $payment_type,
                        'payment_method' => $payment_method,
                        'responce_all'=> '', //json_encode($res),
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
						'class_id' => $this->input->post('schedule_id'),
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
                    $imageUrl = site_url() . 'uploads/user/';
                    $arg['data']      = $this->db->query("SELECT user.name, concat('".$imageUrl."', user.profile_img) as profile_img, user.lastname, user.gender, DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(), user.date_of_birth)), '%Y')+0 AS age, user_booking.status FROM `user_booking` JOIN user on (user.id = user_booking.user_id) WHERE user_booking.service_type = 4 ANd user_booking.business_id = ".$business_id." AND user_booking.service_id = ".$workshop_id)->result_array();;
                   }
                   else
                   {
                   	$arg['status']    = 0;
	                $arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
	                $arg['error_line']= __line__;
	                $arg['message']   = @$res->error->message;
	                $arg['data']      =json_decode('{}');
                   }
                }
            } else {
                $arg['status']    = 0;
                $arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
                $arg['error_line']= __line__;
                $arg['message']   = '';
                $arg['data']      =json_decode('{}');
            }
        }
        echo json_encode($arg);
    }

    /****************Function Cancel workshop **********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : workshop_scheduling_cancel
     * @description     : add business class like yoga
     * @param           : null
     * @return          : null
     * ********************************************************** */
    public function workshop_scheduling_cancel() {
        $arg = array();
        $userdata = web_checkuserid();
        if($userdata['status'] != 1){
            $arg = $userdata;
        } else {
            $_POST = json_decode(file_get_contents("php://input"), true);
            if($_POST)
            {
                $this->form_validation->set_rules('shceduleId', 'Schedule Id', 'required|numeric',array(
                    'required' => 'Schedule id is required',
                    'numeric' => 'Schedule id is required',
                ));
                $this->form_validation->set_rules('workShopId', 'WorkShop Id', 'required|numeric',array(
                    'required' => 'WorkShop id is required',
                    'numeric' => 'WorkShop id is required',
                ));
                $this->form_validation->set_rules('status', 'Status', 'required',array(
                    'required' => 'Status is required'
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
                    $response=array();
                    $business_id    = decode($userdata['data']['business_id']);
                    $userId         = decode($userdata['data']['id']);
                    $workShopId     = $this->input->post('workShopId');
                    $scheduleId     = $this->input->post('shceduleId');
                    $status         = $this->input->post('status');
                    $checkStatus    =   $this->db->get_where('business_workshop_schdule', array(
                        'id'        =>  $scheduleId
                    ))->num_rows();
                    if ($checkStatus) {

						$this->dynamic_model->updateRowWhere('business_workshop_schdule', array('id' => $scheduleId), array('status' => $status));

						/* if ($status = 'Cancel') {
							$this->dynamic_model->updateRowWhere(
								'user_booking',
								array(
									'service_id' 	=> 	$workShopId,
									'class_id'		=>	$scheduleId,
									'business_id' 	=>	$business_id,
									'service_type'	=>	'4'
								),
								array(
									'status' => 'Cancel',
									'system_comment'=> 'Workshop cancel by studio admin'
								)
							);
						} */

                        $arg['status']     = 1;
                        $arg['error_code']  = HTTP_OK;
                        $arg['error_line']= __line__;
                        $arg['message']    = 'Workshop status changed successfully';
                    } else {
                        $arg['status']     = 0;
                        $arg['error_code']  = HTTP_OK;
                        $arg['error_line']= __line__;
                        $arg['data']       = array();
                        $arg['message']    = $this->lang->line('record_not_found');
                    }
                }
            }
        }

        echo json_encode($arg);
    }
	/****************Function Add business classes**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : add_business_classes
     * @description     : add business class like yoga
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function book_appoinment()
	{
		$arg   = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
		   $userdata = web_checkuserid();
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
				$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
				{
				    $this->form_validation->set_rules('business_id','Business Id','required|trim', array( 'required' => $this->lang->line('business_id_required')));
				    $this->form_validation->set_rules('user_id','User Id','required|trim', array( 'required' => $this->lang->line('user_id_required')));
				    $this->form_validation->set_rules('service_id','Service Id','required|trim', array( 'required' => $this->lang->line('service_id_required')));
				    $this->form_validation->set_rules('service_type','Service type','required|trim', array('required'=>$this->lang->line('service_type_required')));
				    $this->form_validation->set_rules('slot_time_id','Slot Time Id','required|trim',array('required' =>$this->lang->line('slot_time_id_required')));
					if ($this->form_validation->run() == FALSE)
					{
					  	$arg['status']  = 0;
					  	$arg['error_code'] = 0;
						$arg['error_line']= __line__;
					 	$arg['message'] = get_form_error($this->form_validation->error_array());
					}
					else
					{
						$userdata = web_checkuserid();
						$usid =decode($userdata['data']['id']);
						$time=time();
						$business_id   = $this->input->post('business_id');
						$user_id   = $this->input->post('user_id');
						$service_id    = $this->input->post('service_id');
					    $service_type  = $this->input->post('service_type');
					    $slot_date     = $this->input->post('slot_date');
					    $slot_time_id  = $this->input->post('slot_time_id');
				 	    $schedule_data = array(
										   	'user_id'   	 =>  $user_id,
										    'business_id'    =>  $business_id,
										    'slot_id'        =>  $slot_time_id,
										    'slot_date'      =>  $slot_date,
										    'service_id'   	 =>  $service_id,
										    'service_type'   =>  $service_type,
										    'slot_available_status' => 0,
										    'create_dt'     => $time,
										    'update_dt'     => $time
									);
							$appointment_book = $this->dynamic_model->insertdata('business_appointment_book',$schedule_data);
							if($appointment_book)
					        {
								$arg['status']    = 1;
								$arg['error_code'] = HTTP_OK;
								$arg['error_line']= __line__;
							 	$arg['message']   = $this->lang->line('book_appointment_msg');
							 	$arg['data']      = [];
					        }else{
					        	$arg['status']     = 0;
					            $arg['error_code']  = HTTP_NOT_FOUND;
								$arg['error_line']= __line__;
								$arg['data']       = array();
								$arg['message']    = $this->lang->line('server_problem');
					        }

					}
				}
		    }
        }
	 echo json_encode($arg);
    }
    public function avalible_instructor_service()
	{
		$arg   = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
		   $userdata = web_checkuserid();
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
				$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
				{
				    $this->form_validation->set_rules('business_id','Business Id','required|trim', array( 'required' => $this->lang->line('business_id_required')));
				    $this->form_validation->set_rules('slot_date','Slot Date','required|trim', array( 'required' => $this->lang->line('slot_date_required')));
				    $this->form_validation->set_rules('slot_id','Slot Id','required|trim', array('required'=>$this->lang->line('slot_id_required')));
					if ($this->form_validation->run() == FALSE)
					{
					  	$arg['status']  = 0;
					  	$arg['error_code'] = 0;
						$arg['error_line']= __line__;
					 	$arg['message'] = get_form_error($this->form_validation->error_array());
					}
					else
					{
						$userdata = web_checkuserid();
						$usid =decode($userdata['data']['id']);
						$response=array();
						$time=time();
						$business_id=$this->input->post('business_id');
						$slot_date=$this->input->post('slot_date');
						$slot_id=$this->input->post('slot_id');
				    	//$where = array('status'=>'Active','business_id'=>$business_id);
						$date = date("Y-m-d",$time);
	                    $where="business_id=".$business_id." AND status='Active' AND DATE(FROM_UNIXTIME(start_date_utc))>='".$date."'";
	                    $business_data = $this->dynamic_model->getdatafromtable('instructor_schedule',$where);
	                    //print_r($business_data);die;

						$where1 = array('slot_id'=>$slot_id,'slot_status'=>'Active');
						$slot_data = $this->dynamic_model->getdatafromtable('business_slots',$where1);
						if(!empty($business_data)){
							foreach($business_data as $key => $value){
					            $instuctor_date= $value['start_date'];
					            $instuctor_from_time= strtotime($value['from_time']);
					            $instuctor_to_time= strtotime($value['to_time']);
					            $from_time= strtotime($slot_data[0]['slot_time_from']);
					            $to_time= strtotime($slot_data[0]['slot_time_to']);

					           // echo $value['from_time'].'=='.$value['to_time'].'=='.$slot_data[0]['slot_time_from'].'=='.$slot_data[0]['slot_time_to'];die;
					            // if($instuctor_date ==$slot_date){
					            // 	if($instuctor_from_time <= $from_time && $instuctor_to_time >= $to_time){
					                 $user_ids[]=$value['user_id'];
					                 $where2 = array('id'=>$value['user_id'],'status'=>'Active');
						             $user_data = $this->dynamic_model->getdatafromtable('user',$where2);
					                 $response[]=array(
					                 	'id'=>$user_data[0]['id'],
					                 	'name'=>$user_data[0]['name'].' '.$user_data[0]['lastname'],
					                 	'profile_img' =>base_url().'/uploads/user/'. $value['profile_img']);
					            // 	}
					            // }
							}
						}
						if($response)
				        {
							$arg['status']    = 1;
							$arg['error_code'] = HTTP_OK;
							$arg['error_line']= __line__;
						 	$arg['data']      = $response;
						 	$arg['message']   = $this->lang->line('record_found');
				        }else{
				        	$arg['status']     = 0;
				            $arg['error_code']  = HTTP_NOT_FOUND;
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
    /****************add_instructor**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : add instructor
     * @description     : add instructor.
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function add_instructor()
	{
		$arg   = array();
		if($_POST)
		{
			$userdata = web_checkuserid();
		    if($userdata['status'] != 1){
			  $arg = $userdata;
			}
			else
			{

				$this->form_validation->set_rules('name','Name', 'required|trim', array( 'required' => $this->lang->line('first_name')));
				$this->form_validation->set_rules('lastname','Last Name', 'required|trim', array( 'required' => $this->lang->line('last_name')));
				 $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[user.email]' , array('required' => $this->lang->line('email_required'),'valid_email' => $this->lang->line('email_valid'),'is_unique' => $this->lang->line('email_unique')
				 ));
				// $this->form_validation->set_rules('mobile', 'Mobile', 'required|min_length[8]|max_length[20]|numeric|is_unique[user.mobile]', array(
				// 		'required' => $this->lang->line('mobile_required'),
				// 		'min_length' => $this->lang->line('mobile_min_length'),
				// 		'max_length' => $this->lang->line('mobile_max_length'),
				// 		'numeric' => $this->lang->line('mobile_numeric')
				// 	));
				// $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|max_length[20]|regex', array(
				// 	'required' => $this->lang->line('password_required'),
				// 	'min_length' => $this->lang->line('password_minlength'),
				// 	'max_length' => $this->lang->line('password_maxlenght'),
				// 	'regex' => $this->lang->line('reg_check')
				// ));

				// $this->form_validation->set_rules('role','Role', 'required', array( 'required' => $this->lang->line('role_required')));
				$this->form_validation->set_rules('skills','Skills', 'required', array( 'required' => $this->lang->line('skills_required')));
				$this->form_validation->set_rules('experience','Experience', 'required', array( 'required' => $this->lang->line('experience_required')));
				$this->form_validation->set_rules('appointment_fees','Appointment fees ', 'required', array( 'required' => $this->lang->line('appointment_fees_required')));
				$this->form_validation->set_rules('appointment_fees_type','Appointment fees type', 'required', array( 'required' => $this->lang->line('appointment_fees_type_required')));
				// $this->form_validation->set_rules('sin_no','sin no', 'required', array( 'required' => $this->lang->line('sin_no_required')));


				// $this->form_validation->set_rules('singup_for','Personal Account / For family member', 'required', array( 'required' => $this->lang->line('signupfor_required')));
				$this->form_validation->set_rules('gender','Select gender', 'required', array( 'required' => $this->lang->line('gender_required')));
				$this->form_validation->set_rules('city','City', 'required', array( 'required' => $this->lang->line('city_required')));
				$this->form_validation->set_rules('state','State', 'required', array( 'required' => $this->lang->line('state_required')));
				$this->form_validation->set_rules('country','Country', 'required', array( 'required' => $this->lang->line('country_required')));
				$this->form_validation->set_rules('address','Address', 'required', array( 'required' => $this->lang->line('dob_required')));
				$this->form_validation->set_rules('dob','DOB', 'required', array( 'required' => $this->lang->line('address_required')));
				if ($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
				    $time=time();
					$usid =decode($userdata['data']['id']);
					$bid = decode($userdata['data']['business_id']);

					$condition = array(
						'subscription.sub_user_id'	=>	$usid,
						'subscription.plan_status'	=>	'Active',
						'subscribe_plan.status'	=>	'Active',
					);

					$on = 'subscribe_plan.id = subscription.sub_plan_id';
					$maxuser = $this->dynamic_model->getTwoTableData('subscribe_plan.max_users','subscription','subscribe_plan', $on, $condition);
					if (!empty($maxuser)) {
						$maxCount = $maxuser[0]['max_users'];
						$getAcceptInstructor = $this->dynamic_model->countdata('business_trainer_relationship', array(
							'is_verified' 	=> 'Active',
							'status' 		=>	'Approve',
							'business_id'   =>  $bid
						));
						if (!empty($getAcceptInstructor)) {
							$activeUser = $getAcceptInstructor[0]['counting'] + 1;
							if ($activeUser > $maxCount) {
								$arg['status']   = 0;
								$arg['error_code']  = HTTP_NOT_FOUND;
								$arg['error_line']= __line__;
								$arg['purchase'] = 1;
								$arg['message']    = 'Plan limit is exceeds, Please upgrade your plan';
								echo json_encode($arg); exit;
							}
						}
					}
				    //$role  = $this->input->post('role');
				    $role  = 4;
				    //$singup_for  = $this->input->post('singup_for');
                    $name            = $this->input->post('name');
					$lastname        = $this->input->post('lastname');
					$email           = $this->input->post('email');
					$mobile       = $this->input->post('mobile');
					$gender       = $this->input->post('gender');
					$date_of_birth       = date('Y-m-d', $this->input->post('dob'));
					$address       = $this->input->post('address');
					$city       = $this->input->post('city');
					$state       = $this->input->post('state');
					$country       = $this->input->post('country');
					$country_code = $this->input->post('country_code');
					$lat       = $this->input->post('lattitude');
					$lang       = $this->input->post('longitude');
					// $lat =  $this->input->get_request_header('lat', true);
					// $lang =  $this->input->get_request_header('lang', true);
					$zipcode       = $this->input->post('zipcode');
					// $referred_by       = $this->input->post('referred_by');
					$street       = $this->input->post('street');
					$about       = $this->input->post('about');
					$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890123456789!@#$&*!@#$&*!@#$&*";
    				$password = substr(str_shuffle( $chars ),0,14);
					$hashed_password = encrypt_password($password);
					$skills      = $this->input->post('skills');
					$total_exp  = $this->input->post('experience');

					$appointment_fees_type  = $this->input->post('appointment_fees_type');
					$appointment_fees = $this->input->post('appointment_fees');
					$start_date      = $this->input->post('start_date');
					$subsitute_name  = $this->input->post('subsitute_instructor_name');
					$status  = $this->input->post('status');
					$employee_contractor= $this->input->post('employee_contractor');
					$employee_id= $this->input->post('employee_id');
					$where = array('email' => $email);
					$result = $this->dynamic_model->getdatafromtable('user',$where);
					if(!empty($result))
					{
						$arg['status']    = 0;
						$arg['error_code'] = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['message']   = $this->lang->line('already_register');
						$arg['data']      = array();
				    }
				    else
				    {
				    	$image = 'userdefault.png';
				    	$pro_img = !empty($name) ? $name : $email;
				    	$default_img = $pro_img ? $pro_img :'u';
                    	$default_img = strtolower(substr($default_img,0,1));
                     	$image = $default_img.'.png';

				    	if(!empty($_FILES['image']['name'])){
							$image = $this->dynamic_model->fileupload('image', 'uploads/user');
						}
						$notification = '{"app_notification":"1","alerts":"1","email":"1","sms":"1","phonecall":"1"}';
						$uniquemail   = getuniquenumber();
						$uniquemobile   = rand(0001,9999);
						$userdata = array(
							'name'=>$name,
							'lastname'=>$lastname,
							'mobile'=>$mobile,
							'date_of_birth' => $date_of_birth,
							'gender'	=> strtolower($gender),
							'email'=>$email,
							'city' => $city,
							'state' => $state,
							'country' => $country,
							'address' => $address,
							'zipcode' => $zipcode,
							'lat' => $lat,
							'lang' => $lang,
							'password'=>$hashed_password,
							'singup_for'=>"Me",
							'profile_img'=>$image,
							'email_verified'=>'1',
							'mobile_verified'=>'1',
							'mobile_otp'=>$uniquemobile,
							'mobile_otp_date'=>$time,
							'status'=>$status,
							'create_dt'=>$time,
							'update_dt'=>$time,
							'notification'=>$notification,
							'location'=>$street,
							'availability_status' => 'Available',
							'created_by'=>$usid,
							'country_code'=>$country_code
						);
						$newuserid = $this->dynamic_model->insertdata('user',$userdata);
						if($newuserid)
				        {
				         	$roledata = array(
		                    	'user_id'=>$newuserid,
		                    	'role_id'=>$role,
		                    	'create_dt'=>$time,
		                    	'update_dt'=>$time
		                	);
		                	$roleid = $this->dynamic_model->insertdata('user_role',$roledata);


							$where2 = array('user_id'=>$usid);
							$business_data = $this->dynamic_model->getdatafromtable('business',$where2);
							$business_id=!empty($business_data[0]['id']) ? $business_data[0]['id'] :'0';
							if(!empty($business_id)){
								$roledata = array(
									'business_id'=>$business_id,
									'user_id'=>$newuserid,
									'is_verified'=>'Active',
									'status'=>'Approve',
									'create_dt'=>$time,
									'update_dt'=>$time
								);
								$this->dynamic_model->insertdata('business_trainer_relationship',$roledata);
							}

							$instructor_data = array(
								'user_id'=>$newuserid,
								'skill' =>$skills,
								'total_experience'=>$total_exp,
								'about' => $about,
								'registration' => $this->input->post('registration'),
								'appointment_fees_type'=>$appointment_fees_type,
								'appointment_fees'=> $appointment_fees,
								'sin_no'=> ($this->input->post('sin_no')) ? $this->input->post('sin_no') : 0,
								'start_date'=>$start_date,
								'substitute_instructor_name'=> ($subsitute_name == null) ? '' : $subsitute_name,
								'employee_id'=>(!empty($employee_id))? $employee_id : '',
								'employee_contractor'=>$employee_contractor,
								'create_dt'=>$time,
								'created_by'=>$usid,
								'update_dt'=>$time
							);
						    $this->dynamic_model->insertdata('instructor_details',$instructor_data);

							$where = array('id' => $newuserid);
							$findresult = $this->dynamic_model->getdatafromtable('user', $where);
							$name= ucwords($findresult[0]['name']);

							/* Instructor time slot setup */
							$getTimeSlot = $this->dynamic_model->getdatafromtable('business_time_slote', array('business_id' => $business_id));
							if ($getTimeSlot) {
								$counter = count($getTimeSlot);
								$slot_insert_array = array();
								for ($i = 0; $i < $counter; $i++) {
									$rowObject = $getTimeSlot[$i];
									$day_id = $rowObject['day_id'];
									$slot_id = $rowObject['id'];
									array_push($slot_insert_array, array(
										'user_id' 			=> 	$newuserid,
										'business_id' 		=> 	$business_id,
										'day_id' 			=> 	$day_id,
										'time_slot_id' 		=> 	$slot_id,
										'day_status'		=>	1,
										'time_slot_status' 	=> 	1,
										'create_dt'			=>	$time,
										'update_dt'			=>	$time
									));
								}
								$this->db->insert_batch('instructor_time_slot', $slot_insert_array);
							}

							//Send Email Code
							$enc_user = encode($newuserid);
							$enc_role = encode($time);
							$url = site_url().'webservices/api/verify_user?encid='.$enc_user.'&batch='.$enc_role;

                            $where1 = array('slug' => 'instructor_registration_by_owner');
                            $template_data = $this->dynamic_model->getdatafromtable('manage_notification_mail', $where1);

							$site_url = site_url();
							$site_url = str_replace('/superadmin', '/staff-signin', $site_url);
							$weblink = '<a href="'.$site_url.'"> Click here </a>';

							$desc= str_replace('{USERNAME}',$name,$template_data[0]['description']);
                            $desc_data= str_replace('{PASSWORD}',$password, $desc);
							$desc_data= str_replace('{URL}',$weblink, $desc_data);
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
							$arg['error_code'] = REST_Controller::HTTP_OK;
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

    public function edit_instructor()
	{
		$arg   = array();
		if($_POST)
		{
			$userdata = web_checkuserid();
		    if($userdata['status'] != 1){
			  $arg = $userdata;
			}
			else
			{
				$this->form_validation->set_rules('instructor_id','Instructor Id', 'required|trim');
				$this->form_validation->set_rules('name','Name', 'required|trim', array( 'required' => $this->lang->line('first_name')));
				$this->form_validation->set_rules('lastname','Last Name', 'required|trim', array( 'required' => $this->lang->line('last_name')));

				// $this->form_validation->set_rules('mobile', 'Mobile', 'required|min_length[8]|max_length[20]|numeric|is_unique[user.mobile]', array(
				// 		'required' => $this->lang->line('mobile_required'),
				// 		'min_length' => $this->lang->line('mobile_min_length'),
				// 		'max_length' => $this->lang->line('mobile_max_length'),
				// 		'numeric' => $this->lang->line('mobile_numeric')
				// 	));
				// $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|max_length[20]|regex', array(
				// 	'required' => $this->lang->line('password_required'),
				// 	'min_length' => $this->lang->line('password_minlength'),
				// 	'max_length' => $this->lang->line('password_maxlenght'),
				// 	'regex' => $this->lang->line('reg_check')
				// ));

				// $this->form_validation->set_rules('role','Role', 'required', array( 'required' => $this->lang->line('role_required')));
				$this->form_validation->set_rules('skills','Skills', 'required', array( 'required' => $this->lang->line('skills_required')));
				$this->form_validation->set_rules('experience','Experience', 'required', array( 'required' => $this->lang->line('experience_required')));
				$this->form_validation->set_rules('appointment_fees','Appointment fees ', 'required', array( 'required' => $this->lang->line('appointment_fees_required')));
				$this->form_validation->set_rules('appointment_fees_type','Appointment fees type', 'required', array( 'required' => $this->lang->line('appointment_fees_type_required')));
				$this->form_validation->set_rules('sin_no','sin no', 'required', array( 'required' => $this->lang->line('sin_no_required')));


				// $this->form_validation->set_rules('singup_for','Personal Account / For family member', 'required', array( 'required' => $this->lang->line('signupfor_required')));
				$this->form_validation->set_rules('gender','Select gender', 'required', array( 'required' => $this->lang->line('gender_required')));
				$this->form_validation->set_rules('city','City', 'required', array( 'required' => $this->lang->line('city_required')));
				$this->form_validation->set_rules('state','State', 'required', array( 'required' => $this->lang->line('state_required')));
				$this->form_validation->set_rules('country','Country', 'required', array( 'required' => $this->lang->line('country_required')));
				$this->form_validation->set_rules('address','Address', 'required', array( 'required' => $this->lang->line('address_required')));
				$this->form_validation->set_rules('dob','DOB', 'required', array( 'required' => $this->lang->line('dob_required')));
				if ($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$time=time();
					$usid =decode($userdata['data']['id']);
					$instructor_id = decode($this->input->post('instructor_id'));
				    //$role  = $this->input->post('role');
				    $role  = 4;
				    //$singup_for  = $this->input->post('singup_for');
                    $name            = $this->input->post('name');
					$lastname        = $this->input->post('lastname');
					$email           = $this->input->post('email');
					// $mobile       = $this->input->post('mobile');
					// $mobile       = $this->input->post('mobile');
					$gender       = $this->input->post('gender');
					$date_of_birth       = date('Y-m-d', $this->input->post('dob'));
					$address       = $this->input->post('address');
					$city       = $this->input->post('city');
					$state       = $this->input->post('state');
					$country       = $this->input->post('country');
					$country_code = $this->input->post('country_code');
					$lat       = $this->input->post('lat');
					$lang       = $this->input->post('lang');
					$lat =  $this->input->get_request_header('lat', true);
					$lang =  $this->input->get_request_header('lang', true);
					$zipcode       = $this->input->post('zipcode');
					$referred_by       = $this->input->post('referred_by');
					$street       = $this->input->post('street');
					$about       = $this->input->post('about');

					$skills      = $this->input->post('skills');
					$total_exp  = $this->input->post('experience');
					$sin_no     = $this->input->post('sin_no');
					$appointment_fees_type  = $this->input->post('appointment_fees_type');
					$appointment_fees = $this->input->post('appointment_fees');
					$start_date      = $this->input->post('start_date');
					$subsitute_name  = $this->input->post('subsitute_instructor_name');
					$status  = $this->input->post('status');
					$employee_contractor= $this->input->post('employee_contractor');
					$employee_id= $this->input->post('employee_id');

					$where = array('email !=' => $email, 'id' => $instructor_id);
					$result = $this->dynamic_model->getdatafromtable('user',$where);

					if(!empty($result))
					{
						$arg['status']    = 0;
						$arg['error_code'] = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['message']   = $this->lang->line('already_register');
						$arg['data']      = array();
				    }

					/* $where = array('mobile !=' => $mobile, 'id' => $instructor_id);
					$check = $this->dynamic_model->getdatafromtable('user',$where);

					if(!empty($check))
					{
						$arg['status']    = 0;
						$arg['error_code'] = REST_Controller::HTTP_OK;
						$arg['error_line']= __line__;
						$arg['message']   = $this->lang->line('already_register');
						$arg['data']      = array();
					} */

					$image = '';
				    if(!empty($_FILES['image']['name'])){
					$image = $this->dynamic_model->fileupload('image', 'uploads/user');
					}
					$notification = '{"app_notification":"1","alerts":"1","email":"1","sms":"1","phonecall":"1"}';
					$userdata = array(
						'name'=>$name,
						'lastname'=>$lastname,
						// 'mobile'=>$mobile,
						'date_of_birth' => $date_of_birth,
						'gender'	=> strtolower($gender),
						//'email'=>$email,
						'city' => $city,
						'state' => $state,
						'country' => $country,
						'address' => $address,
						'zipcode' => $zipcode,
						'lat' => $lat,
						'lang' => $lang,
						'status'=>$status,
						'update_dt'=>$time,
						'location'=>$street,
						'created_by'=>$usid,
						'country_code'=>$country_code
					);

					if (!empty($image)) {
						$userdata['profile_img'] = $image;
					}

					$skill_array = explode(',', $skills);
					$skills = array_unique($skill_array);
					$skills = implode(',', $skills);
					$instructor_data = array(
						'skill' =>$skills,
						'total_experience'=>$total_exp,
						'about' => $about,
						'registration' => $this->input->post('registration'),
						'appointment_fees' => $appointment_fees,
						'appointment_fees_type'=>$appointment_fees_type,
						'sin_no'=> ($this->input->post('sin_no')) ? $this->input->post('sin_no') : 0,
						'start_date'=>$start_date,
						'substitute_instructor_name'=> ($subsitute_name == null) ? '' : $subsitute_name,
						'employee_id'=>(!empty($employee_id))? $employee_id : '',
						'employee_contractor'=>$employee_contractor,
						'created_by'=>$usid,
						'update_dt'=>$time
					);

					$this->dynamic_model->updateRowWhere('user', array('id' => $instructor_id), $userdata);
					$this->dynamic_model->updateRowWhere('instructor_details', array('user_id' => $instructor_id), $instructor_data);

					$where = array('id'=>$instructor_id);
				   	$userData = $this->dynamic_model->getdatafromtable('user',$where);
				   	$where1 = array('user_id'=>$instructor_id);
					$instructorData = $this->dynamic_model->getdatafromtable('instructor_details',$where1);
					$url = site_url() . 'uploads/user/';
					$collection = array(
						'id' => encode($instructor_id),
						'name'=>$userData[0]['name'],
						'lastname'=>$userData[0]['name'],
						'email'=>$userData[0]['email'],
						'date_of_birth' => $userData[0]['date_of_birth'],
						'singup_for'=>$userData[0]['singup_for'],
						'profile_img'=> $url.$userData[0]['profile_img'],
						'email_verified'=>$userData[0]['email_verified'],
						'mobile_verified'=>$userData[0]['mobile_verified'],
						'mobile_otp_date'=>$userData[0]['mobile_otp_date'],
						'status'=>$userData[0]['status'],
						'create_dt'=>$userData[0]['create_dt'],
						'update_dt'=>$userData[0]['update_dt'],
						'notification'=>$userData[0]['notification'],
						'city' => $userData[0]['city'],
						'state' => $userData[0]['state'],
						'country' => $userData[0]['country'],
						'address' => $userData[0]['address'],
						'street'=> $userData[0]['location'],
						'zipcode' => $userData[0]['zipcode'],
						'lat' => $userData[0]['lat'],
						'lang' => $userData[0]['lang'],
						'gender' => $userData[0]['gender'],
						'skill' =>$instructorData[0]['skill'],
						'total_experience'=>$instructorData[0]['total_experience'],
						'appointment_fees'=> $instructorData[0]['appointment_fees'],
						'appointment_fees_type'=>$instructorData[0]['appointment_fees_type'],
						'sin_no'=>$instructorData[0]['sin_no'],
						'start_date'=>$instructorData[0]['start_date'],
						'substitute_instructor_name'=>$instructorData[0]['substitute_instructor_name'],
						'employee_id'=>$instructorData[0]['employee_id'],
						'employee_contractor'=>$instructorData[0]['employee_contractor'],
						'create_dt'=>$instructorData[0]['create_dt'],
						'created_by'=>$instructorData[0]['created_by'],
						'update_dt'=>$instructorData[0]['update_dt'],
						'about' => $instructorData[0]['about']
					);
					$arg['status']    = 1;
					$arg['error_code'] = REST_Controller::HTTP_OK;
					$arg['error_line']= __line__;
					$arg['message']   = 'Instructor updated successfully.';
				 	$arg['data']      = $collection;
				}



			}

		echo json_encode($arg);
	  }
    }

    public function instructor_details()
	{
		$arg   = array();


			$userdata = web_checkuserid();
		    if($userdata['status'] != 1){
			  $arg = $userdata;
			}
			else
			{
				$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
				{
				$this->form_validation->set_rules('instructor_id','Instructor id', 'required|trim');
				if ($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
				    $time=time();
				    $url = site_url() . 'uploads/user/';
				    $instructor_id  = $this->input->post('instructor_id');
				    $instructorid =decode($instructor_id);
				    $where = array('id'=>$instructorid);
				   	$userData = $this->dynamic_model->getdatafromtable('user',$where);
				   	$where1 = array('user_id'=>$instructorid);
					$instructorData = $this->dynamic_model->getdatafromtable('instructor_details',$where1);

					$userdata = array(
								'id' => $instructor_id,
						        'name'=>$userData[0]['name'],
						        'lastname'=>$userData[0]['lastname'],
						        'email'=>$userData[0]['email'],
								'date_of_birth' => $userData[0]['date_of_birth'],
						        'singup_for'=>$userData[0]['singup_for'],
						        'profile_img'=> $url.$userData[0]['profile_img'],
						        'email_verified'=>$userData[0]['email_verified'],
						        'mobile_verified'=>$userData[0]['mobile_verified'],
						        'mobile_otp_date'=>$userData[0]['mobile_otp_date'],
						        'status'=>$userData[0]['status'],
						        'create_dt'=>$userData[0]['create_dt'],
						        'update_dt'=>$userData[0]['update_dt'],
						        'notification'=>$userData[0]['notification'],
						        'city' => $userData[0]['city'],
								'state' => $userData[0]['state'],
								'country' => $userData[0]['country'],
								'address' => $userData[0]['address'],
								'street'=> $userData[0]['location'],
								'zipcode' => $userData[0]['zipcode'],
								'lat' => $userData[0]['lat'],
								'lang' => $userData[0]['lang'],
								'gender' => $userData[0]['gender'],
								'skill' =>$instructorData[0]['skill'],
								'registration' => $instructorData[0]['registration'],
							   'total_experience'=>$instructorData[0]['total_experience'],
							   'appointment_fees'=> $instructorData[0]['appointment_fees'],
					           'appointment_fees_type'=>$instructorData[0]['appointment_fees_type'],
					           'sin_no'=>$instructorData[0]['sin_no'],
					           'start_date'=>$instructorData[0]['start_date'],
					           'substitute_instructor_name'=>$instructorData[0]['substitute_instructor_name'],
					           'employee_id'=>$instructorData[0]['employee_id'],
					           'employee_contractor'=>$instructorData[0]['employee_contractor'],
					           'create_dt'=>$instructorData[0]['create_dt'],
					           'created_by'=>$instructorData[0]['created_by'],
					           'update_dt'=>$instructorData[0]['update_dt'],
					           'about' => $instructorData[0]['about'],
					           'appointment_fees' => $instructorData[0]['appointment_fees']

						    );



							$arg['status']    = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
						 	$arg['message']   = $this->lang->line('profile_updated_success');
						 	$arg['data']      = $userdata;
				}



			}

		echo json_encode($arg);
	  }
    }

    public function instructor_details_15092020()
	{
		$arg   = array();


			$userdata = web_checkuserid();
		    if($userdata['status'] != 1){
			  $arg = $userdata;
			}
			else
			{
				$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
				{
				$this->form_validation->set_rules('instructor_id','Instructor id', 'required|trim');
				if ($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
				    $time=time();

				    $instructor_id  = $this->input->post('instructor_id');
				    $instructorid =decode($instructor_id);
				    $where = array('id'=>$instructorid);
				   	$userData = $this->dynamic_model->getdatafromtable('user',$where);
				   	$where1 = array('user_id'=>$instructorid);
					$instructorData = $this->dynamic_model->getdatafromtable('instructor_details',$where1);

					$userdata = array(
						        'name'=>$userData[0]['name'],
						        'lastname'=>$userData[0]['name'],
						        'email'=>$userData[0]['email'],
						        'singup_for'=>$userData[0]['singup_for'],
						        'profile_img'=>$userData[0]['profile_img'],
						        'email_verified'=>$userData[0]['email_verified'],
						        'mobile_verified'=>$userData[0]['mobile_verified'],
						        'mobile_otp_date'=>$userData[0]['mobile_otp_date'],
						        'status'=>$userData[0]['status'],
						        'create_dt'=>$userData[0]['create_dt'],
						        'update_dt'=>$userData[0]['update_dt'],
						        'notification'=>$userData[0]['notification'],
						        'skill' =>$instructorData[0]['skill'],
					           'total_experience'=>$instructorData[0]['total_experience'],
					           'appointment_fees_type'=>$instructorData[0]['appointment_fees_type'],
					           'sin_no'=>$instructorData[0]['sin_no'],
					           'start_date'=>$instructorData[0]['start_date'],
					           'substitute_instructor_name'=>$instructorData[0]['substitute_instructor_name'],
					           'employee_id'=>$instructorData[0]['employee_id'],
					           'employee_contractor'=>$instructorData[0]['employee_contractor'],
					           'create_dt'=>$instructorData[0]['create_dt'],
					           'created_by'=>$instructorData[0]['created_by'],
					           'update_dt'=>$instructorData[0]['update_dt']

						    );



							$arg['status']    = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
						 	$arg['message']   = $this->lang->line('profile_updated_success');
						 	$arg['data']      = $userdata;
				}



			}

		echo json_encode($arg);
	  }
    }
     public function get_skills()
    {
        $arg    = array();
		$skills = $this->dynamic_model->getdatafromtable('manage_skills');
		$this->db->select('*')->from('manage_skills')->order_by('name', 'asc');
		$skills = $this->db->get()->result_array();
		// $skills = $this->db->get_where('manage_skills', array('status' => 'Ative'));
		if(!empty($skills))
        {

            $arg['status']     = 1;
            $arg['error_code']  = REST_Controller::HTTP_OK;
            $arg['error_line']= __line__;
            $arg['data']       = $skills;
            $arg['message']    = $this->lang->line('skills_list');
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
    /****************Function Get Instructor list**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : instructor_list
     * @description     : list of classes
     * @param           : null
     * @return          : null
     * ********************************************************** */
    public function instructor_list()
	{
	   $arg = array();
	   $userdata = web_checkuserid();
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
				$business_id=  decode($userdata['data']['business_id']);
				$search_val=  $this->input->post('search_val');
				$instructor_info =  $this->studio_model->get_all_instructors($business_id,$search_val,$limit,$offset, 1);
				$url = base_url().'uploads/user/';

				/* Businesss Information */
				$user_id = decode($userdata['data']['id']);
				$totalStaff = 0;
				$amount = 0;
				$current = 0;
				$condition = array(
					'subscription.sub_user_id'	=>	$user_id,
					'subscription.plan_status'	=>	'Active',
					'subscribe_plan.status'	=>	'Active',

				);

				$on = 'subscribe_plan.id = subscription.sub_plan_id';
				$maxuser = $this->dynamic_model->getTwoTableData('subscribe_plan.max_users, subscribe_plan.amount','subscription','subscribe_plan', $on, $condition);
				if (!empty($maxuser)) {
					$maxCount = $maxuser[0]['max_users'];
					$totalStaff = $maxCount;
					$amount = $maxuser[0]['amount'];
					$getAcceptInstructor = $this->dynamic_model->countdata('business_trainer_relationship', array(
						'is_verified' 	=> 'Active',
						'status' 		=>	'Approve',
						'business_id'   =>  $business_id
					));
					if (!empty($getAcceptInstructor)) {
						$current = $getAcceptInstructor[0]['counting'];
					}
				}

				$businessInfo = array(
					'package' 	=> $totalStaff,
					'amount' 	=> $amount,
					'current' 	=> $current
				);

				if($instructor_info){
					foreach($instructor_info as $value){
						$instructordata['id']     = encode($value['id']);
						$instructordata['name']   = ucwords($value['name']);
						$instructordata['lastname']= ucwords($value['lastname']);
						$instructordata['about']    = $value['about'];
						$instructordata['registration']    = $value['registration'];
						$instructordata['country_code']    = $value['country_code'];
						$instructordata['mobile']    = $value['mobile'];
						$instructordata['profile_img'] = $url.$value['profile_img'];
						$instructordata['availability_status']= $value['availability_status'];
						$skill = $value['skill'];
						if (!empty($skill)) {
							$instructordata['skill'] = (!empty($value['skill'])) ? get_categories($value['skill']) : "";
						} else {
							$instructordata['skill']=$value['skill'];
						}
						$instructordata['services'] =  "Zumba,Yoga,Gym,Fitness";
						$instructordata['experience'] =  (!empty($value['total_experience'])) ? $value['total_experience'] : "";
						$instructordata['appointment_fees_type'] =   (!empty($value['appointment_fees_type'])) ? $value['appointment_fees_type'] : "";
						$instructordata['appointment_fees'] =   (!empty($value['appointment_fees'])) ? $value['appointment_fees'] : "";
						$instructordata['sin_no'] =   (!empty($value['sin_no'])) ? $value['sin_no'] : "";
						$instructordata['start_date'] =   (!empty($value['start_date'])) ? $value['start_date'] : "";
						$instructordata['employee_id'] =   (!empty($value['employee_id'])) ? $value['employee_id'] : "";
						$instructordata['role'] = $value['role_id'];
						$response[]	                 = $instructordata;
					}
				    $arg['status']    = 1;
					$arg['error_code'] = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']      = $response;
					$arg['businessInfo']      = $businessInfo;
					// $arg['info'] = $this->studio_model->get_instructor_ids($business_id);
					$arg['message']   = $this->lang->line('record_found');
				}
				else{
					$arg['status']     = 0;
					$arg['error_code']  = REST_Controller::HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = array();
					$arg['businessInfo']       = $businessInfo;
				 	$arg['message']    = $this->lang->line('record_not_found');
				}
		    }
		  }
		}
	   echo json_encode($arg);
	}

	public function instructor_stat_list()
	{
	   $arg = array();
	   $userdata = web_checkuserid();
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
				$business_id=  decode($userdata['data']['business_id']);
				$instructor_info =  $this->studio_model->get_all_instructors($business_id,'',$limit,$offset);

				$url = base_url().'uploads/user/';

				if($instructor_info){
					foreach($instructor_info as $value){
					$instructordata['Id']     = encode($value['id']);
	            	$instructordata['name']   = ucwords($value['name']);
	            	$instructordata['lastname']= ucwords($value['lastname']);
	            	$instructordata['about']    = $value['about'];
	            	$instructordata['profile_img'] = $url.$value['profile_img'];
	            	$instructordata['availability_status']= $value['availability_status'];
	            	$instructordata['Text'] = ucwords($value['name']).' '.ucwords($value['lastname']);
	            	$instructordata['skill'] = (!empty($value['skill'])) ? get_categories($value['skill']) : "";
	            	$where1= array('instructor_id'=>$value['id']);
	            	$class_data = $this->dynamic_model->getdatafromtable('business_class',$where1);
	            	$booking_class_count=0;
	            	if(!empty($class_data)){
	            		$i=1;

		            	foreach ($class_data as $value) {
		            		$where= array('class_id'=>$value['id']);
	 	            		$booking_data = $this->dynamic_model->getdatafromtable('user_booking',$where);
	 	            		if(!empty($booking_data)){
	 	            			$booking_class_count = $i++;
	 	            		}

		            	}
	            	}

	            	$workshop_data = $this->dynamic_model->getdatafromtable('business_workshop',$where1);
	            	$booking_workshop_count=0;
	            	if(!empty($workshop_data)){
	            		$j=1;

		            	foreach ($workshop_data as $value) {
		            		$where= array('workshop_id'=>$value['id']);
	 	            		$booking_data = $this->dynamic_model->getdatafromtable('user_booking',$where);
	 	            		if(!empty($booking_data)){
	 	            			$booking_workshop_count = $j++;
	 	            		}
		            	}
	            	}


 	            	$instructordata['booking_count']= $booking_class_count + $booking_workshop_count;

	            	$response[]	                 = $instructordata;
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

	public function room_list()
	{
	   $arg = array();
	   $userdata = web_checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{

	      $_POST = json_decode(file_get_contents("php://input"), true);
		  if($_POST)
		  {
			$this->form_validation->set_rules('search_type', 'Search Type', 'required|numeric');
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
				$business_id =  decode($userdata['data']['business_id']);
				$search_type =  $this->input->post('search_type');
				if($search_type==1){
					$instructor_info =  $this->studio_model->get_class_room_list($business_id);
				}

				if($search_type==2){
					$instructor_info =  $this->studio_model->get_workshop_room_list($business_id);
				}


				if($instructor_info){
					foreach($instructor_info as $value){
						$instructordata['Id']     = encode($value['location_id']);
	            		$instructordata['Text'] = ucwords($value['location_name']);

	            		$response[]	                 = $instructordata;
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
	public function timeline_calender_old()
	{
	   $arg = array();
	   $userdata = web_checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
	      $_POST = json_decode(file_get_contents("php://input"), true);
		  if($_POST)
		  {

			$response=array();
			$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";

			$business_id=  decode($userdata['data']['business_id']);
			$instructor_info =  $this->studio_model->get_all_instructors($business_id,'','','');
			$url = base_url().'uploads/user/';
			$search_type = $this->input->post('search_type');
			$search_date = $this->input->post('search_date');
			//$day_id = $this->input->post('day_id');

			if($instructor_info){
				foreach($instructor_info as $value){
				$instructordata['id']     = encode($value['id']);
            	$instructordata['name']   = ucwords($value['name']);
            	$instructordata['lastname']= ucwords($value['lastname']);
            	$instructordata['about']    = $value['about'];
            	$instructordata['profile_img'] = $url.$value['profile_img'];
            	$instructordata['availability_status']= $value['availability_status'];
            	$instructordata['skill'] = (!empty($value['skill'])) ? get_categories($value['skill']) : "";
            	//$where= 'business_id="'. $business_id.'" AND status="Active"';

            	$filterData =array();
            	if($search_type=='1'){

        			if(isset($search_date) && !empty($search_date)){
        				$search_array = array('business_id'=>$business_id,'scheduled_date'=>$search_date);
        			}else{
        				$search_array = array('business_id'=>$business_id);
        			}
        			$shedule_data = $this->dynamic_model->getdatafromtable('class_scheduling_time',$search_array);
        			//print_r($this->db->last_query()); die;
        			if(!empty($shedule_data)){

	            			foreach ($shedule_data as $value1) {
	            				$timeline_data = $this->dynamic_model->getdatafromtable('business_class',array('id'=>$value1['class_id'],'status'=>'Active'));
	            				if($timeline_data){
	            					$singned_customer= $this->studio_model->get_all_signed_classes($business_id,$value1['class_id'],5,0);
			                        $total_singned_customer= $this->studio_model->get_all_signed_classes($business_id,$value1['class_id']);
			                        $total_customer=(!empty($total_singned_customer)) ? count($total_singned_customer) : '0';

		            				$filterData[] = array(
		            							"Id"=> $timeline_data[0]['id'],
							                    "Subject"=> $timeline_data[0]['class_name'],
							                    "scheduled_date"=>$value1['scheduled_date'],
							                    "StartTime"=>date('Y,m,d, H,i',$value1['from_time']),
							                    "EndTime"=>date('Y,m,d, H,i',$value1['to_time']),
							                    "EmployeeId"=> $instructordata['id'],
									            "client_details"=>$singned_customer,
									            "total_count"=>$total_customer,
									            "instructor_data" => $this->instructor_list_details($business_id,1,$value1['class_id']),
									            "room"=>$timeline_data[0]['location']
		            						);
		            			}

	            			}



            		}
            	}else if($search_type=='2'){
            		if(isset($search_date) && !empty($search_date)){
        				$search_array = array('business_id'=>$business_id,'scheduled_date'=>$search_date);
        			}else{
        				$search_array = array('business_id'=>$business_id);
        			}
        			$shedule_data = $this->dynamic_model->getdatafromtable('workshop_scheduling_time',$search_array);
        			//print_r($this->db->last_query()); die;
        			if(!empty($shedule_data)){
	            			foreach ($shedule_data as $value1) {
	            				$timeline_data = $this->dynamic_model->getdatafromtable('business_workshop',array('id'=>$value1['workshop_id'],'status'=>'Active'));
	            				if($timeline_data){
		            				$filterData[] = array(
		            							"Id"=> $timeline_data[0]['id'],
							                    "Subject"=> $timeline_data[0]['workshop_name'],
							                    "scheduled_date"=>$value1['scheduled_date'],
							                    "StartTime"=>date('Y,m,d, H,i',$value1['from_time']),
							                    "EndTime"=>date('Y,m,d, H,i',$value1['to_time']),
							                    "EmployeeId"=> $instructordata['id']
		            						);
		            			}

	            			}



            		}
            	}else{
            		if(isset($search_date) && !empty($search_date)){
        				$search_array = array('business_id'=>$business_id,'scheduled_date'=>$search_date);
        			}else{
        				$search_array = array('business_id'=>$business_id);
        			}
        			$shedule_data = $this->dynamic_model->getdatafromtable('class_scheduling_time',$search_array);
        			//print_r($this->db->last_query()); die;
        			if(!empty($shedule_data)){
	            			foreach ($shedule_data as $value1) {
	            				$timeline_data = $this->dynamic_model->getdatafromtable('business_class',array('id'=>$value1['class_id'],'status'=>'Active'));
	            				if($timeline_data){
		            				$filterData[] = array(
		            							"Id"=> $timeline_data[0]['id'],
							                    "Subject"=> $timeline_data[0]['class_name'],
							                    "scheduled_date"=>$value1['scheduled_date'],
							                    "StartTime"=>date('Y,m,d, H,i',$value1['from_time']),
							                    "EndTime"=>date('Y,m,d, H,i',$value1['to_time']),
							                    "EmployeeId"=> $instructordata['id']
		            						);
		            			}

	            			}



            		}
            	}


            	$instructordata['class_list']= ($filterData)? $filterData:array();


            	$response[]	                 = $instructordata;
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
	   echo json_encode($arg);
	}

	public function timeline_calender()
	{
	   $arg = array();
	   $userdata = web_checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
	      $_POST = json_decode(file_get_contents("php://input"), true);
		  if($_POST)
		  {

			$response=array();
			$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";

			$business_id=  decode($userdata['data']['business_id']);

			$url = base_url().'uploads/user/';
			$search_type = $this->input->post('search_type');
			$search_date = $this->input->post('search_date');


            	$filterData =array();
            	if($search_type=='1'){

        			if(isset($search_date) && !empty($search_date)){
        				$search_array = array('business_id'=>$business_id,'scheduled_date'=>$search_date);
        			}else{
        				$search_array = array('business_id'=>$business_id);
        			}
        			$shedule_data = $this->dynamic_model->getdatafromtable('class_scheduling_time',$search_array);
        			//print_r($this->db->last_query()); die;
        			if(!empty($shedule_data)) {

	            			foreach ($shedule_data as $value1) {
	            				$timeline_data = $this->dynamic_model->getdatafromtable('business_class',array('id'=>$value1['class_id'],'status'=>'Active'));
	            				if($timeline_data){

						            $startTime = date('H:i:s',$value1['from_time']);
						            $endTime = date('H:i:s',$value1['to_time']);
						            $startDateTime= date('Y,m,d,H,i', strtotime($value1['scheduled_date'].' '.$startTime));
						            $endDateTime= date('Y,m,d,H,i', strtotime($value1['scheduled_date'].' '.$endTime));
									$singned_customer = $this->studio_model->get_booked_customer($value1['class_id'],$value1['scheduled_date'], $value1['id']);
									$response=array();
									if (!empty($singned_customer)) {
										foreach($singned_customer as $cust) {
											$covid_info = getUserQuestionnaire($cust['user_id'], $value1['class_id'],$business_id);
											if(!empty($covid_info)){
												$cust['covid_status'] = $covid_info['covid_status'];
												$cust['covid_info'] = $covid_info['covid_info'];
											}else {
												$cust['covid_info'] = 0;
												$cust['covid_status'] = 0;
											}
											$cust['schedule_id']  = $value1['id'];
											$response[] = $cust;
										}
									}
		            				$filterData[] = array(
		            							"Id"=> $timeline_data[0]['id'],
		            							 "business_id"=>$business_id,
							                    "Subject"=> $timeline_data[0]['class_name'],
							                    "scheduled_date"=>$value1['scheduled_date'],
							                    "StartTime"=>$startDateTime,
							                    "EndTime"=>$endDateTime,

							                    "StartTimeUtc"=>$value1['from_time'],
							                    "EndTimeUtc"=>$value1['to_time'],

							                    "EmployeeId"=> encode($value1['location_id']),
									            "duration"=>$timeline_data[0]['duration'],
									            "description"=>$timeline_data[0]['description'],
									            "capacity"=>$timeline_data[0]['capacity'],
									            "client_details"=>$response,
									            "total_count"=>count($singned_customer),
									            "instructor_data" => $this->studio_model->studio_instructor_list_details($value1['instructor_id']),
									            "room"=>$timeline_data[0]['location']
		            						);
		            			}

	            			}



            		}
            	}else if($search_type=='2'){
            		if(isset($search_date) && !empty($search_date)){
        				$search_array = array('business_id'=>$business_id,'scheduled_date'=>$search_date);
        			}else{
        				$search_array = array('business_id'=>$business_id);
        			}
        			$shedule_data = $this->dynamic_model->getdatafromtable('workshop_scheduling_time',$search_array);
        			//print_r($this->db->last_query()); die;
        			if(!empty($shedule_data)){

	            			foreach ($shedule_data as $value1) {
	            				$timeline_data = $this->dynamic_model->getdatafromtable('business_workshop',array('id'=>$value1['workshop_id'],'status'=>'Active'));
	            				if($timeline_data){
	            					$singned_customer= $this->studio_model->get_all_signed_classes($business_id,$value1['workshop_id'],5,0);
			                        $total_singned_customer= $this->studio_model->get_all_signed_classes($business_id,$value1['workshop_id']);
			                        $total_customer=(!empty($total_singned_customer)) ? count($total_singned_customer) : '0';
						            $startTime = date('H:i:s',$value1['from_time']);
						            $endTime = date('H:i:s',$value1['to_time']);
						            $startDateTime= date('Y,m,d,H,i', strtotime($value1['scheduled_date'].' '.$startTime));
						            $endDateTime= date('Y,m,d,H,i', strtotime($value1['scheduled_date'].' '.$endTime));
		            				$filterData[] = array(
		            							"Id"=> $timeline_data[0]['id'],
							                    "Subject"=> $timeline_data[0]['workshop_name'],
							                    "scheduled_date"=>$value1['scheduled_date'],
							                    "StartTime"=>$startDateTime,
							                    "EndTime"=>$endDateTime,
							                    "EmployeeId"=> encode($value1['location_id']),
									            "client_details"=>$singned_customer,
									            "total_count"=>$total_customer,
									            "instructor_data" => $this->studio_model->studio_instructor_list_details($value1['instructor_id']),
									            "room"=>$timeline_data[0]['location']
		            						);
		            			}

	            			}



            		}
            	}else{
            		if(isset($search_date) && !empty($search_date)){
        				$search_array = array('business_id'=>$business_id,'scheduled_date'=>$search_date);
        			}else{
        				$search_array = array('business_id'=>$business_id);
        			}
        			$shedule_data = $this->dynamic_model->getdatafromtable('class_scheduling_time',$search_array);
        			//print_r($this->db->last_query()); die;
        			if(!empty($shedule_data)){

	            			foreach ($shedule_data as $value1) {
	            				$timeline_data = $this->dynamic_model->getdatafromtable('business_class',array('id'=>$value1['class_id'],'status'=>'Active'));
	            				if($timeline_data){

						            $startTime = date('H:i:s',$value1['from_time']);
						            $endTime = date('H:i:s',$value1['to_time']);
						            $startDateTime= date('Y,m,d,H,i', strtotime($value1['scheduled_date'].' '.$startTime));
						            $endDateTime= date('Y,m,d,H,i', strtotime($value1['scheduled_date'].' '.$endTime));
						            $singned_customer = $this->studio_model->get_signedup_customer($value1['class_id'],$value1['scheduled_date']);
		            				$filterData[] = array(
		            							"Id"=> $timeline_data[0]['id'],
							                    "Subject"=> $timeline_data[0]['class_name'],
							                    "scheduled_date"=>$value1['scheduled_date'],
							                    "StartTime"=>$startDateTime,
							                    "EndTime"=>$endDateTime,
							                    "EmployeeId"=> encode($value1['location_id']),
									            "client_details"=>$singned_customer,
									            "total_count"=>count($singned_customer),
									            "instructor_data" => $this->studio_model->studio_instructor_list_details($value1['instructor_id']),
									            "room"=>$timeline_data[0]['location']
		            						);
		            			}

	            			}



            		}
            	}


			    $arg['status']    = 1;
				$arg['error_code'] = HTTP_OK;
				$arg['error_line']= __line__;
			 	$arg['data']      = $filterData;
			 	$arg['message']   = $this->lang->line('record_found');


		  }
		}
	   echo json_encode($arg);
	}
     /****************add_client**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : add client
     * @description     : add client.
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function add_client()
    {
        $arg   = array();
        if($_POST)
        {
           $userdata = web_checkuserid();
		    if($userdata['status'] != 1){
			  $arg = $userdata;
			}
			else
			{
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
	            /*$this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|max_length[20]|regex', array(
	                'required' => $this->lang->line('password_required'),
	                'min_length' => $this->lang->line('password_minlength'),
	                'max_length' => $this->lang->line('password_maxlenght'),
	                'regex' => $this->lang->line('reg_check')
	            ));*/
	            // $this->form_validation->set_rules('role','Role', 'required', array( 'required' => $this->lang->line('role_required')));
	            // $this->form_validation->set_rules('singup_for','Personal Account / For family member', 'required', array( 'required' => $this->lang->line('signupfor_required')));
	            //$this->form_validation->set_rules('gender','Select gender', 'required', array( 'required' => $this->lang->line('gender_required')));
	            //$this->form_validation->set_rules('city','City', 'required', array( 'required' => $this->lang->line('city_required')));
	            //$this->form_validation->set_rules('state','State', 'required', array( 'required' => $this->lang->line('state_required')));
	            //$this->form_validation->set_rules('country','Country', 'required', array( 'required' => $this->lang->line('country_required')));
	            //$this->form_validation->set_rules('address','Address', 'required', array( 'required' => $this->lang->line('address_required')));
	            //$this->form_validation->set_rules('date_of_birth','DOB', 'required', array( 'required' => $this->lang->line('dob_required')));

	            if ($this->form_validation->run() == FALSE)
	            {
	                $arg['status']  = 0;
	                $arg['error_code'] = 0;
	                $arg['error_line']= __line__;
	                $arg['message'] = get_form_error($this->form_validation->error_array());
	            }
	            else
	            {
	                $role  = 3;//client
	                $role2= 4;//instructor
	                $usid =decode($userdata['data']['id']);
	                //$singup_for  = $this->input->post('singup_for');
	                $name            = $this->input->post('name');
	                $lastname        = $this->input->post('lastname');
	                $email           = $this->input->post('email');
	                $mobile       = $this->input->post('mobile');
	                $gender       = $this->input->post('gender');
	                $date_of_birth       = $this->input->post('date_of_birth');
	                //$address       = $this->input->post('address');
	                //$city       = $this->input->post('city');
	                //$state       = $this->input->post('state');
	                //$country       = $this->input->post('country');
	                $country_code = $this->input->post('country_code');
	                //$lat       = $this->input->post('lat');
	                //$lang       = $this->input->post('lang');
	                //$zipcode       = $this->input->post('zipcode');
	                $referred_by   = $this->input->post('referred_by');
	                //$street       = $this->input->post('street');
	                //$street       = (!empty($street)) ? $street :'';
	                $discount     = $this->input->post('discount');
	                //$consent_signed= $this->input->post('consent_signed');
	                //$hashed_password = encrypt_password($this->input->post('password'));

	                $where = array('email' => $email);
	                $result = $this->dynamic_model->check_user_role($email,$role,1,$role2);
	                //print_r($result);die;

	                if(!empty($result))
	                {
	                $arg['status']    = 0;
	                $arg['error_code'] = HTTP_OK;
	                $arg['error_line']= __line__;
	                $arg['message']   = $this->lang->line('already_register');
	                $arg['data'] = json_decode('{}');
	                }
	                else
	                {
	                $image = 'userdefault.png';
	                if(!empty($_FILES['image']['name'])){
	                    $image = $this->dynamic_model->fileupload('image', 'uploads/user');
	                }

	                $notification = '{"app_notification":"1","alerts":"1","email":"1","sms":"1","phonecall":"1"}';
	                $time=time();
	                $uniquemail   = getuniquenumber();
					$uniquemobile   = rand(0001,9999);
					$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890123456789!@#$&*!@#$&*!@#$&*";
					$password = randomPassword();
	                $hashed_password = encrypt_password($password);
	                $userdata = array(
	                    'name'=>$name,
                        'lastname'=>$lastname,
                        'password'=> $hashed_password,
                        'email'=>$email,
                        'mobile'=>$mobile,
                        'profile_img'=>$image,
                        'status'=>'Deactive',
                        'gender'=>$gender,
                        'date_of_birth'=>$date_of_birth,
                        //'address'=>$address,
                        //'city'=>$city,
                        //'state'=>$state,
                        //'country'=>$country,
                        //'lat'=>$lat,
                        //'lang'=>$lang,
                        //'zipcode'=>$zipcode,
                        'singup_for'=> 'me', // $singup_for,
                        'referred_by'=>$referred_by,
                        'email_verified'=>'0',
                        'mobile_verified'=>'1',
                        'mobile_otp'=>$uniquemobile,
                        'mobile_otp_date'=>$time,
                        'create_dt'=>$time,
                        'update_dt'=>$time,
                        'notification'=>$notification,
                        //'location'=>$street,
                        'country_code'=>$country_code,
                        'discount'=>$discount,
                        //'consent_signed'=>$consent_signed,
                        'created_by'=>$usid);
	                    $newuserid = $this->dynamic_model->insertdata('user',$userdata);
	                    if($newuserid)
	                    {
	                        $roledata = array(
	                            'user_id'=>$newuserid,
	                            'role_id'=>$role,
	                            'create_dt'=>$time,
	                            'update_dt'=>$time
	                        );
	                        $roleid = $this->dynamic_model->insertdata('user_role',$roledata);

	                        $where = array('user_id' => $usid);
	                        $findresult = $this->dynamic_model->getdatafromtable('business', $where);
	                        $business_name= ucwords($findresult[0]['business_name']);

	                        $where = array('id' => $newuserid);
	                        $findresult = $this->dynamic_model->getdatafromtable('user', $where);
	                        $name= ucwords($findresult[0]['name']);

	                        //Send Email Code
	                        $enc_user = encode($newuserid);
	                        $enc_role = encode($time);
	                        $url = site_url().'webservices/api/verify_user?encid='.$enc_user.'&batch='.$enc_role;
							$link='<a href="'.$url.'"> Click here </a>';
							$site_url = site_url();
							$site_url = str_replace('/superadmin', '/signin', $site_url);
							$weblink = '<a href="'.$site_url.'"> Click here </a>';

	                        // $where1 = array('slug' => 'sucessfully_registration');
	                        $where1 = array('slug' => 'new_client_registration');
	                        $template_data = $this->dynamic_model->getdatafromtable('manage_notification_mail', $where1);

							$desc= str_replace('{USERNAME}',$name,$template_data[0]['description']);
							$desc_data= str_replace('{PASSWORD}',$password, $desc);
							$desc_data= str_replace('{URL}',$link, $desc_data);
							$desc_data= str_replace('{WEBURL}',$weblink, $desc_data);

							$desc_data= str_replace('{STUDIO_NAME}',$business_name, $desc_data);

							//$desc_data= str_replace('{URL}',$link, $desc);
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

	                        $data_val  = getuserdetail($newuserid,$role);

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
    /****************Function Get Instructor list**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : client_list
     * @description     : client list
     * @param           : null
     * @return          : null
     * ********************************************************** */
    public function client_list()
	{
	   $arg = array();
	   $userdata = web_checkuserid();
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
				$usid   = decode($userdata['data']['id']);
				$role=3;//client
				$response=array();
				$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
				$page_no= $page_no-1;
				$limit    = config_item('page_data_limit');
				$offset = $limit * $page_no;
				$search_val=  $this->input->post('search_val');

				//$where = array('created_by' => $usid);
				if($search_val){
					$where= 'user.created_by="'. $usid.'" AND user_role.role_id="'. $role.'" AND user.name LIKE "%'.$search_val.'%"';
				}else{
                    $where= 'user.created_by="'. $usid.'" AND user_role.role_id="'. $role.'"';
				}

				if ($this->input->post('client_id')) {

					$where 		= 	'user.created_by="'. $usid.'" AND user.id="'. $this->input->post('client_id').'" AND user_role.role_id="'. $role.'"';
				}

				// $condition=array('user.email'=>$email,'user_role.role_id'=>$role);
		        $on='user_role.user_id = user.id';
		        $client_data = $this->dynamic_model->getTwoTableData('user.*,user_role.role_id','user','user_role',$on,$where, '', '', 'user.name');
				//print_r($instructor_info);die;
				if($client_data){
					foreach($client_data as $value){
                        $clientdata['id']     = $value['id'];
                        //$clientdata['type']   = $value['title_name'];
                        $clientdata['first_name']   = ucwords($value['name']);
                        $clientdata['last_name']   = ucwords($value['lastname']);
                        $clientdata['email']  = $value['email'];
                        $clientdata['profile_img']  = base_url().'uploads/user/'.$value['profile_img'];
                        $clientdata['country_code'] = $value['country_code'];
                        $clientdata['mobile'] = $value['mobile'];
                        $clientdata['date_of_birth'] = $value['date_of_birth'];
                        $clientdata['gender'] = $value['gender'];
                        $clientdata['status'] = ($value['status'] == 'Active') ? 1 : 0;
                        $clientdata['role'] =$value['role_id'];//client;
                        if ($this->input->post('client_id')) {
                            $clientdata['address'] =$value['address'];
                            $clientdata['city'] =$value['city'];
                            $clientdata['state'] =$value['state'];
                            $clientdata['country'] =$value['country'];
                            $clientdata['lat'] =$value['lat'];
                            $clientdata['lang'] =$value['lang'];
                            $clientdata['zipcode'] =$value['zipcode'];
                            $clientdata['singup_for'] =$value['singup_for'];
                            $clientdata['referred_by'] =$value['referred_by'];
                            $clientdata['discount'] =$value['discount'];
                            $clientdata['consent_signed'] =$value['consent_signed'];


                        }
                        $clientdata['create_dt'] = $value['create_dt'];
                        $response[]	          = $clientdata;
					}
				    $arg['status']    = 1;
					$arg['error_code'] = HTTP_OK;
					$arg['error_line']= __line__;

					if ($this->input->post('client_id')) {
						$arg['data']      = $response[0];
					} else {
						$arg['data']      = $response;
					}

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

    /****************Function Get Instructor list**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : client_update
     * @description     : client list
     * @param           : null
     * @return          : null
     * ********************************************************** */
    public function update_client()
	{
	   $arg = array();
	   $userdata = web_checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
	      $_POST = json_decode(file_get_contents("php://input"), true);
		  if($_POST)
		  {
		    $this->form_validation->set_rules('client_id', 'Client Id', 'required|numeric',array(
				'required' => 'Class id is required',
				'numeric' => 'Class id is required',
			));

			$this->form_validation->set_rules('first_name', 'First Name', 'required|trim',array(
				'required' => $this->lang->line('first_name')
			));

			$this->form_validation->set_rules('last_name', 'Last Name', 'required|trim',array(
				'required' => $this->lang->line('last_name')
			));



			if($this->form_validation->run() == FALSE)
			{
			  	$arg['status']  = 0;
			  	$arg['error_code'] = 0;
				$arg['error_line']= __line__;
			 	$arg['message'] = get_form_error($this->form_validation->error_array());
			}
			 else {

                    $role  = 3;//client
                    $role2= 4;//instructor
                    $usid =decode($userdata['data']['id']);
                    $name            = $this->input->post('first_name');
                    $lastname        = $this->input->post('last_name');
                    $country_code = $this->input->post('country_code');
                    $referred_by   = $this->input->post('referred_by');
                    $discount     = $this->input->post('discount');


                    $time=time();
                    $userdata = array(
						'name'          =>  $name,
						'lastname'      =>  $lastname,
						'referred_by'   =>  $referred_by,
						'country_code'  =>  $country_code,
						'discount'      =>  $discount,
						'update_dt'     =>  $time
                    );
					if ($this->input->post('gender')) {
						$userdata['gender'] = $this->input->post('gender');
					}
					if ($this->input->post('date_of_birth')) {
						$userdata['date_of_birth'] = $this->input->post('date_of_birth');
					}
                    if ($this->input->post('password') && !empty($this->input->post('password'))) {
                        $hashed_password = encrypt_password($this->input->post('password'));
                        $userdata['password'] = $hashed_password;
                    }
                    $updateUserid = $this->dynamic_model->updateRowWhere('user', array('id' => $this->input->post('client_id')),$userdata);
                    if($updateUserid)
                    {
                        $arg['status']    = 1;
                        $arg['error_code'] = HTTP_OK;
                        $arg['error_line']= __line__;
                        $arg['message']   = 'Client Successfully Updated';

                    } else {
                        $arg['status']    = 0;
                        $arg['error_code'] = HTTP_OK;
                        $arg['error_line']= __line__;
                        $arg['message']   = 'Please try again';
                    }
                }
		  }
		}
	   echo json_encode($arg);
	}
	 /****************Function add_tax ********************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : add_tax
     * @description     : add tax
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function add_tax()
	{
	   $arg   = array();
	   $userdata = web_checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
				 $this->form_validation->set_rules('tax1_name','Tax 1 name', 'required|trim', array( 'required' => $this->lang->line('tax1_required')));
				$this->form_validation->set_rules('amount_per_tax1','Tax 1 amount(%)', 'required|trim', array( 'required' => $this->lang->line('tax1_amt')));
				$this->form_validation->set_rules('tax2_name','Tax 2 name', 'required|trim', array( 'required' => $this->lang->line('tax2_required')));
				$this->form_validation->set_rules('amount_per_tax2','Tax 2 amount(%)', 'required|trim', array( 'required' => $this->lang->line('tax2_amt')));


				if ($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$usid =decode($userdata['data']['id']);
					$time=time();
			        $business_id= decode($userdata['data']['business_id']);
			        $tax1_name=$this->input->post('tax1_name');
			        $tax2_name=$this->input->post('tax2_name');
			        $amount_per_tax1=$this->input->post('amount_per_tax1');
			        $amount_per_tax2=$this->input->post('amount_per_tax2');
			        $where=array("business_id"=>$business_id);
				    $tax_result = $this->dynamic_model->getdatafromtable('business_tax',$where);
				    if(empty($tax_result)){
					$taxData = array(
									"business_id"=> $business_id,
									"tax1_name"=> $tax1_name,
									"tax1_rate"=> $amount_per_tax1,
									"tax2_name"=> $tax2_name,
									"tax2_rate"=> $amount_per_tax2,
									"create_dt"=> $time,
									"update_dt"=> $time
					);
					$taxId= $this->dynamic_model->insertdata('business_tax',$taxData);
				   }else{
				 	$taxData = array(
									"tax1_name"=> $tax1_name,
									"tax1_rate"=> $amount_per_tax1,
									"tax2_name"=> $tax2_name,
									"tax2_rate"=> $amount_per_tax2,
									"update_dt"=> $time
					);
				 	$taxId= $this->dynamic_model->updateRowWhere('business_tax',$where,$taxData);

				    }
					if($taxId)
			        {
						$arg['status']    = 1;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line']= __line__;
					 	$arg['message']   = $this->lang->line('tax_succ');
					 	$arg['data']      = [];
			        }else{
			        	$arg['status']     = 0;
			            $arg['error_code']  = HTTP_NOT_FOUND;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('server_problem');
			        }
				}
			}
	    }

	  echo json_encode($arg);
    }
      /****************Function tax_details***********************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : tax details
     * @description     : tax details
     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function tax_details()
	{
		$arg = array();
		$userdata = web_checkuserid('1');
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
			$response=array();
			$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
			$page_no= $page_no-1;
			$limit    = config_item('page_data_limit');
			$offset = $limit * $page_no;
            $business_id= decode($userdata['data']['business_id']);
			$where=array("business_id"=>$business_id,"status"=>"Active");
			$tax_data = $this->dynamic_model->getdatafromtable('business_tax',$where,"*",$limit,$offset,'create_dt');
			//print_r($class_data);die;
			if(!empty($tax_data)){
				$arg['status']     = 1;
				$arg['error_code']  = REST_Controller::HTTP_OK;
				$arg['error_line']= __line__;
				$arg['data']       = $tax_data;
				$arg['message']    = $this->lang->line('record_found');
			}else{
				$arg['status']     = 0;
				$arg['error_code']  = REST_Controller::HTTP_OK;
				$arg['error_line']= __line__;
				$arg['data']       = array();
			 	$arg['message']    = $this->lang->line('record_not_found');
			}
		}

	   echo json_encode($arg);
	}
	  /*********Function Get passes purchase customer list*****
     * @type            : Function
     * @Author          : Arpit
     * @function name   : passes_list
     * @description     : passes list
                           purpose,

     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function customer_passes_list()
	{
	   $arg = array();
	   $userdata = web_checkuserid();
	  // print_r($userdata);die;
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
				$response=$imgarr=array();
				$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
				$page_no= $page_no-1;
				$limit    = config_item('page_data_limit');
				//$limit    =1;
				$offset = $limit * $page_no;
				// $user_token='UzdNeXRsSXl0RFJYc2dZQQ==';
				// echo $userid = decode(base64_decode($user_token));die;
				$business_id=decode($userdata['data']['business_id']);
				$where=array("business_id"=>$business_id,"service_type"=>1);
				$pass_purchase_data = $this->dynamic_model->getdatafromtable('user_booking',$where,'*',$limit,$offset,'create_dt','DESC');
				if(!empty($pass_purchase_data))
				{
				    foreach($pass_purchase_data as $value){
				     // get products details
	                $where2 = array('id'=>$value['service_id'],'status' => 'Active');
		            $pass_data = $this->dynamic_model->getdatafromtable('business_passes',$where2);
		            $service_id=(!empty($pass_data[0]['id'])) ? $pass_data[0]['id'] : '';
		            $pass_name=(!empty($pass_data[0]['pass_name'])) ? $pass_data[0]['pass_name'] : 0;
		            // get transaction id
		            $where3 = array('id'=>$value['transaction_id']);
		            $trx_data = $this->dynamic_model->getdatafromtable('transactions',$where3);
		            // get users information
		            $where4 = array('id'=>$value['user_id']);
		            $user_data = $this->dynamic_model->getdatafromtable('user',$where4);
	            	$passData['id'] = encode($value['id']);
	            	$passData['pass_id']=encode($value['service_id']);
	            	$passData['pass_name'] = $pass_name;
	            	$passData['passes_id'] = $pass_data[0]['pass_id'];
	            	$passData['pass_validity']= $pass_data[0]['pass_validity'];
	            	$passData['purchase_date']=  date("d M Y ",$pass_data[0]['purchase_date']);
	            	$passData['pass_end_date']=  date("d M Y ",$pass_data[0]['pass_end_date']);
	            	$passData['purchase_date_utc']= $pass_data[0]['purchase_date'];
	            	$passData['pass_end_date_utc']= $pass_data[0]['pass_end_date'];
	            	$passData['class_type']   =  get_categories($pass_data[0]['class_type']);

	            	$passType  = (!empty($pass_data[0]['pass_type'])) ? $pass_data[0]['pass_type'] : '';
					$pass_type_subcat  = (!empty($pass_data[0]['pass_type_subcat'])) ? $pass_data[0]['pass_type_subcat'] : '';
					$pass_type=get_passes_type_name($passType,$pass_type_subcat);
					$passData['pass_type']=get_passes_type_name($passType);
					$passData['pass_sub_type']=$pass_type;
	            	$passData['order_id']= !empty($trx_data[0]['trx_id']) ? $trx_data[0]['trx_id'] :'';
	            	$passData['amount']  = $value['amount'];
	            	$passData['sub_total']  = $value['sub_total'];
	            	$passData['quantity']  =$value['quantity'];
	            	$passData['status']  =$value['status'];
	            	$passData['customer_name']   = ucwords($user_data[0]['name'].' '.$user_data[0]['lastname']);
	            	$passData['email']  = $user_data[0]['email'];
	            	$passData['profile_img']  = base_url().'uploads/user/'.$user_data[0]['profile_img'];
	            	$passData['country_code'] = $user_data[0]['country_code'];
	            	$passData['mobile'] = $user_data[0]['mobile'];
	            	$passData['date_of_birth'] = $user_data[0]['date_of_birth'];
	            	$passData['gender'] = $user_data[0]['gender'];
	            	$response[]	  = $passData;
                   }
					$arg['status']     = 1;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = $response;
					$arg['message']    = $this->lang->line('record_found');
				}else{
					$arg['status']     = 0;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = array();
				 	$arg['message']    = $this->lang->line('record_not_found');
				}
		    }
		  }
		}

	   echo json_encode($arg);
	}
	 /*********Function Get passes purchase customer details*****
     * @type            : Function
     * @Author          : Arpit
     * @function name   : customer_passes_details
     * @description     : customer passes details
                           purpose,

     * @param           : null
     * @return          : null
     * ********************************************************** */
	public function customer_passes_details()
	{
	   $arg = array();
	   $userdata = web_checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
	      $_POST = json_decode(file_get_contents("php://input"), true);
		  if($_POST)
		  {
		    $this->form_validation->set_rules('passes_book_id', 'Passes book id', 'required',array(
					'required' => $this->lang->line('pass_book_id_required')
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
				$passes_book_id=decode($this->input->post('passes_book_id'));
				$business_id=decode($userdata['data']['business_id']);
				$where=array('id'=> $passes_book_id,"business_id"=>$business_id,"service_type"=>1);

				/* if ($this->input->post('demo')) {
					$arg['status'] = $where;
					$arg['userdata'] = $userdata;
					echo json_encode($arg); exit;
				} */
				$pass_purchase = $this->dynamic_model->getdatafromtable('user_booking',$where);
				if(!empty($pass_purchase))
				{
				     // get passes details
	                $where2 = array('id'=>$pass_purchase[0]['service_id'],'status' => 'Active');
		            $pass_data = $this->dynamic_model->getdatafromtable('business_passes',$where2);
		            $service_id=(!empty($pass_data[0]['id'])) ? $pass_data[0]['id'] : '';
		            $pass_name=(!empty($pass_data[0]['pass_name'])) ? $pass_data[0]['pass_name'] : 0;
		            // get transaction id
		            $where3 = array('id'=>$pass_purchase[0]['transaction_id']);
		            $trx_data = $this->dynamic_model->getdatafromtable('transactions',$where3);
		            // get users information
		            $where4 = array('id'=>$pass_purchase[0]['user_id']);
		            $user_data = $this->dynamic_model->getdatafromtable('user',$where4);
	            	$passData['id'] = encode($pass_purchase[0]['id']);
	            	$passData['pass_id'] =encode($pass_purchase[0]['service_id']);
	            	$passData['pass_name'] = $pass_name;
	            		$passData['passes_id'] = $pass_data[0]['pass_id'];
	            	$passData['pass_validity']= $pass_data[0]['pass_validity'];
	            	$passData['purchase_date']=  date("d M Y ",$pass_data[0]['purchase_date']);
	            	$passData['pass_end_date']=  date("d M Y ",$pass_data[0]['pass_end_date']);
	            	$passData['purchase_date_utc']= $pass_data[0]['purchase_date'];
	            	$passData['pass_end_date_utc']= $pass_data[0]['pass_end_date'];

					$manage_category = $this->db->get_where('manage_category', array('id' => $pass_data[0]['class_type']));
					$class_type = '';
					if ($manage_category->num_rows() > 0) {
						$class_type = $manage_category->row_array()['category_name'];
					}
	            	$passData['class_type']   =  $class_type;

	            	$passType  = (!empty($pass_data[0]['pass_type'])) ? $pass_data[0]['pass_type'] : '';
					$pass_type_subcat  = (!empty($pass_data[0]['pass_type_subcat'])) ? $pass_data[0]['pass_type_subcat'] : '';
					$pass_type=get_passes_type_name($passType,$pass_type_subcat);
					$passData['pass_type']=get_passes_type_name($passType);
					$passData['pass_sub_type']=$pass_type;
	            	$passData['order_id']= !empty($trx_data[0]['trx_id']) ? $trx_data[0]['trx_id'] :'';
	            	$passData['amount']  = $pass_purchase[0]['amount'];
	            	$passData['sub_total']  = $pass_purchase[0]['sub_total'];
	            	$passData['quantity']  =$pass_purchase[0]['quantity'];
	            	$passData['status']  =$pass_purchase[0]['status'];
	            	$passData['customer_name']   = ucwords($user_data[0]['name'].' '.$user_data[0]['lastname']);
	            	$passData['email']  = $user_data[0]['email'];
	            	$passData['profile_img']  = base_url().'uploads/user/'.$user_data[0]['profile_img'];
	            	$passData['country_code'] = $user_data[0]['country_code'];
	            	$passData['mobile'] = $user_data[0]['mobile'];
	            	$passData['date_of_birth'] = $user_data[0]['date_of_birth'];
	            	$passData['gender'] = $user_data[0]['gender'];
	            	$passData['payment_mode'] = $pass_purchase[0]['payment_mode'];
	            	$passData['transaction_id'] = $pass_purchase[0]['reference_payment_id'];
					$passData['transaction_date'] = $pass_purchase[0]['create_dt'];
					$passData['card_type'] = '';

	            	$response	  = $passData;
					$arg['status']     = 1;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = $response;
					$arg['message']    = $this->lang->line('record_found');
				}else{
					$arg['status']     = 0;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = array();
				 	$arg['message']    = $this->lang->line('record_not_found');
				}
		    }
		  }
		}

	   echo json_encode($arg);
	}

	/*****Function customer class list**********
     * @type            : Function
     * @Author          : Arpit
     * @function name   : class_scheduled_list
     * @description     : list of class scheduled
     * @param           : null
     * @return          : null
     * ***********************************************************/
	public function customer_class_list()
	{
		$arg = array();
		$userdata = web_checkuserid();
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
		    $this->form_validation->set_rules('class_id','Class Id','required|trim', array( 'required' => $this->lang->line('class_id_required')));
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
				$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
				$page_no= $page_no-1;
				$limit    = config_item('page_data_limit');
				//$limit    = 1;
				$offset = $limit * $page_no;
				$business_id= decode($userdata['data']['business_id']);
                //checkedin_type 1 cheked in  2 not cheked in
                $checkedin_type=  $this->input->post('checkedin_type');
                $class_id=  decode($this->input->post('class_id'));
				$classes_data= $this->studio_model->get_all_signed_classes($business_id,$class_id,$limit,$offset,$checkedin_type,$date);
				//print_r($classes_data);die;
				if(!empty($classes_data)){
				    foreach($classes_data as $value)
		            {

                       $where = array('id'=>$value['service_id']);
				       $class_data = $this->dynamic_model->getdatafromtable('business_class',$where);
		            	$from_time=(!empty($class_data[0]['from_time']))? $class_data[0]['from_time'] : "";
		            	$to_time=(!empty($class_data[0]['to_time']))? $class_data[0]['to_time'] : "";
		            	$start_date=(!empty($class_data[0]['start_date']))? $class_data[0]['start_date'] : "";
		            	$classesdata['id']           = encode($value['id']);
		            	$classesdata['class_id']     = encode($class_data[0]['id']);
		            	$classesdata['business_id']  = encode($class_data[0]['business_id']);
		            	$classesdata['class_name']   = $class_data[0]['class_name'];
		            	$classesdata['duration']     = $class_data[0]['duration'];
		            	$classesdata['capacity']     = $class_data[0]['capacity'];
		            	$classesdata['location']     = $class_data[0]['location'];
		            	$classesdata['from_time']    =  ($from_time !=='') ? $from_time :'';
		            	$classesdata['to_time']      =  ($to_time !=='') ? $to_time :'';
		            	$classesdata['from_time_utc']= strtotime($from_time);
		            	$classesdata['to_time_utc']  = strtotime($to_time);
		            	$classesdata['class_type']   = get_categories($class_data[0]['class_type']);
		            	// get users information
		               $where4 = array('id'=>$value['user_id']);
		               $user_data = $this->dynamic_model->getdatafromtable('user',$where4);
		            	$classesdata['user_id'] =encode($user_data[0]['id']);
		            	$classesdata['name']   = ucwords($user_data[0]['name']);
		            	$classesdata['lastname']= ucwords($user_data[0]['lastname']);
		            	$classesdata['email']  = $user_data[0]['email'];
		            	$classesdata['profile_img']  = base_url().'uploads/user/'.$user_data[0]['profile_img'];
		            	$classesdata['country_code'] = $user_data[0]['country_code'];
		            	$classesdata['mobile'] = $user_data[0]['mobile'];
		            	$classesdata['date_of_birth'] = $user_data[0]['date_of_birth'];
		            	$classesdata['gender'] = $user_data[0]['gender'];
		            	$response[]	                 = $classesdata;
		            }
					$arg['status']     = 1;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = $response;
					$arg['message']    = $this->lang->line('record_found');
				}else{
					$arg['status']     = 0;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = array();
				 	$arg['message']    = $this->lang->line('record_not_found');
				}
		    }
		  }
		}

	   echo json_encode($arg);
	}

	/*****Function customer Wrokshop list**********
     * @type            : Function
     * @Author          : Arpit
     * @function name   : class_scheduled_list
     * @description     : list of class scheduled
     * @param           : null
     * @return          : null
     * ***********************************************************/
	public function customer_workshop_list()
	{
		$arg = array();
		$userdata = web_checkuserid();
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
		    $this->form_validation->set_rules('workshop_id','Workshop Id','required', array( 'required' => $this->lang->line('workshop_id_required')));
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
				$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
				$page_no= $page_no-1;
				$limit    = config_item('page_data_limit');
				//$limit    = 1;
				$offset = $limit * $page_no;
				$business_id= decode($userdata['data']['business_id']);
                //checkedin_type 1 cheked in  2 not cheked in
                $checkedin_type=  $this->input->post('checkedin_type');
                $workshop_id=  decode($this->input->post('workshop_id'));
				$workshop_info= $this->studio_model->get_all_signed_workshops($business_id,$workshop_id,$limit,$offset,$checkedin_type,$date);
				//print_r($workshop_info);die;
				if(!empty($workshop_info)){
				    foreach($workshop_info as $value)
		            {

                       $where = array('id'=>$value['service_id']);
				       $workshop_data = $this->dynamic_model->getdatafromtable('business_workshop',$where);
		            	$from_time=(!empty($workshop_data[0]['from_time']))? $workshop_data[0]['from_time'] : "";
		            	$to_time=(!empty($workshop_data[0]['to_time']))? $workshop_data[0]['to_time'] : "";
		            	$start_date=(!empty($workshop_data[0]['start_date']))? $workshop_data[0]['start_date'] : "";
		            	$workshopdata['id']           = encode($value['id']);
		            	$workshopdata['workshop_id']     = encode($workshop_data[0]['id']);
		            	$workshopdata['business_id']  = encode($workshop_data[0]['business_id']);
		            	$workshopdata['workshop_name']   = $workshop_data[0]['workshop_name'];
		            	$workshopdata['duration']     = $workshop_data[0]['duration'];
		            	$workshopdata['capacity']     = $workshop_data[0]['capacity'];
		            	$workshopdata['location']     = $workshop_data[0]['location'];
		            	$workshopdata['from_time']    =  ($from_time !=='') ? date("h:i A ",$from_time) :'';
		            	$workshopdata['to_time']      =  ($to_time !=='') ? date("h:i A  ",$to_time) :'';
		            	$workshopdata['from_time_utc']= $from_time;
		            	$workshopdata['to_time_utc']  = $to_time;
		            	$workshopdata['workshop_type']   = get_categories($workshop_data[0]['workshop_type']);
		            	// get users information
		               $where4 = array('id'=>$value['user_id']);
		               $user_data = $this->dynamic_model->getdatafromtable('user',$where4);
		            	$workshopdata['user_id'] =encode($user_data[0]['id']);
		            	$workshopdata['name']   = ucwords($user_data[0]['name']);
		            	$workshopdata['lastname']= ucwords($user_data[0]['lastname']);
		            	$workshopdata['email']  = $user_data[0]['email'];
		            	$workshopdata['profile_img']  = base_url().'uploads/user/'.$user_data[0]['profile_img'];
		            	$workshopdata['country_code'] = $user_data[0]['country_code'];
		            	$workshopdata['mobile'] = $user_data[0]['mobile'];
		            	$workshopdata['date_of_birth'] = $user_data[0]['date_of_birth'];
		            	$workshopdata['gender'] = $user_data[0]['gender'];
		            	$response[]	                 = $workshopdata;
		            }
					$arg['status']     = 1;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = $response;
					$arg['message']    = $this->lang->line('record_found');
				}else{
					$arg['status']     = 0;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = array();
				 	$arg['message']    = $this->lang->line('record_not_found');
				}
		    }
		  }
		}

	   echo json_encode($arg);
	}
		/*****Function register_instructor_list**********
     * @type            : Function
     * @Author          : Arpit
     * @function name   : register_instructor_list
     * @description     : register_instructor_list
     * @param           : null
     * @return          : null
     * ***********************************************************/
	public function register_instructor_list()
	{
		$arg = array();
		$userdata = web_checkuserid();
		
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
				//$limit    = 1;
				$offset = $limit * $page_no;
				$business_id= decode($userdata['data']['business_id']);
				$business_data = $this->dynamic_model->getdatafromtable('business_trainer_relationship',array('business_id'=>$business_id));
				//print_r($classes_data);die;
				if(!empty($business_data)){
				    foreach($business_data as $value)
		            {

                       $where4 = array('id'=>$value['user_id']);
		               $user_data = $this->dynamic_model->getdatafromtable('user',$where4);
		            	$bdata['id'] =encode($value['id']);
		            	$bdata['user_id'] =encode($user_data[0]['id']);
		            	$bdata['name']   = ucwords($user_data[0]['name']);
		            	$bdata['lastname']= ucwords($user_data[0]['lastname']);
		            	 $diff = (date('Y') - date('Y',strtotime($user_data[0]['date_of_birth'])));
		            	$bdata['age']= $diff;
		            	$bdata['gender']= ucwords($user_data[0]['gender']);
		 				$bdata['instructor_id']= $value['user_id'];

		            	$bdata['profile_img']  = base_url().'uploads/user/'.$user_data[0]['profile_img'];
		            	$bdata['status']  =$value['status'];
		            	$response[]	 = $bdata;
		            }
					$arg['status']     = 1;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = $response;
					$arg['message']    = $this->lang->line('record_found');
				}else{
					$arg['status']     = 0;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = array();
				 	$arg['message']    = $this->lang->line('record_not_found');
				}
		    }
		  }
		}

	   echo json_encode($arg);
	}
	public function change_register_instructor_status()
	{
		$arg   = array();
		$userdata = web_checkuserid();
	    if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
			$this->form_validation->set_rules('id','Register Id', 'required|trim', array( 'required' => $this->lang->line('register_id_required')));
            $this->form_validation->set_rules('status', 'Status ', 'required',array('required' => $this->lang->line('status_required')));
			if ($this->form_validation->run() == FALSE)
			{
			  	$arg['status']  = 0;
			  	$arg['error_code'] = 0;
				$arg['error_line']= __line__;
			 	$arg['message'] = get_form_error($this->form_validation->error_array());
			}
			else
			{
				//echo encode(6);die;
				$time=time();
				$id      = decode($this->input->post('id'));

				$status  = $this->input->post('status');
				$business_id= decode($userdata['data']['business_id']);

				$business_data = $this->dynamic_model->getdatafromtable('business_trainer_relationship',array('business_id'=>$business_id));

				if(!empty($business_data)){
					$user_id = $business_data[0]['user_id'];
					if ($status == 'Approve') {
						// Check User
						$condition = array(
							'subscription.sub_user_id'	=>	$user_id,
							'subscription.plan_status'	=>	'Active',
							'subscribe_plan.status'	=>	'Active',
						);
						$on = 'subscribe_plan.id = subscription.sub_plan_id';
						$maxuser = $this->dynamic_model->getTwoTableData('subscribe_plan.max_users','subscription','subscribe_plan', $on, $condition);
						if (!empty($maxuser)) {
							$maxCount = $maxuser[0]['max_users'];
							$getAcceptInstructor = $this->dynamic_model->countdata('business_trainer_relationship', array(
								'is_verified' 	=> 'Active',
								'status' 		=>	'Approve',
								'business_id'   =>  $business_id
							));
							if (!empty($getAcceptInstructor)) {
								$activeUser = $getAcceptInstructor[0]['counting'] + 1;
								if ($activeUser > $maxCount) {
									$arg['status']   = 0;
									$arg['error_code']  = HTTP_NOT_FOUND;
									$arg['error_line']= __line__;
									$arg['purchase'] = 1;
									$arg['message']    = 'Plan limit is exceeds, Please upgrade your plan';
									echo json_encode($arg); exit;
								}
							}
						}
					}

					$statusData =   array(
						'status'  =>$status,
						'update_dt'=>$time
					);
					$condition=array("user_id"=>$id, 'business_id' => $business_id);
					$register_data = $this->dynamic_model->updateRowWhere('business_trainer_relationship',$condition,$statusData);

					if($register_data)
			        {
                      //send push notification to instructor
			          if($status=='Approve'){
                        $notification_title ='Your request has been approved successfully';
                        $notification_type=1;
			          }else{
                        $notification_title ='Your request has been rejected';
                         $notification_type=2;
			          }
                       $push_notification= pushNotification($notification_title,$notification_type,'',$user_id);
                       //if($push_notification==true){
                       	$notification_data= array(
				        	                    'recepient_id'=>$user_id,
				        	                    'message'=>$notification_title,
				        	                    'create_dt'=>$time
				        	                  );
                        $this->dynamic_model->insertdata('notification',$notification_data);
                       //}
						$msg=($status=='Approve') ? $this->lang->line('business_register_approve') : $this->lang->line('business_register_reject');
						$arg['status']    = 1;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line']= __line__;
					 	$arg['message']   = $msg;
					 	$arg['data']      = [];
			        }else{
			        	$arg['status']     = 0;
			            $arg['error_code']  = HTTP_NOT_FOUND;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = 'Action already performed.';
			        }
			    }else{
					$arg['status']     = 0;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = array();
				 	$arg['message']    = $this->lang->line('record_not_found');
				}


			}
		 }
		}
		echo json_encode($arg);
	}

	function manage_week_days($week_name) {
	$arr = array('1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday', '7' => 'Sunday');
		foreach ($arr as $key => $value) {
			if (strtolower($value) == strtolower($week_name)) {
				return $key;
			}
		}
	}

    public function business_opening_closing_time()
	{
		$arg   = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{

			$this->form_validation->set_rules('business_id','Business Id', 'required|trim', array( 'required' => $this->lang->line('business_id_required')));
			if ($this->form_validation->run() == FALSE)
			{
			  	$arg['status']  = 0;
			  	$arg['error_code'] = 0;
				$arg['error_line']= __line__;
			 	$arg['message'] = get_form_error($this->form_validation->error_array());
			}
			else
			{
				//echo encode(6);die;
				$time=time();
				$business_id      = decode($this->input->post('business_id'));
				$slot_info= $this->input->post('slot_info');


				$checkfav = array('business_id' => $business_id);
				$this->dynamic_model->deletedata('business_time_slote',$checkfav);

                $business_time_slote= $this->dynamic_model->getdatafromtable('business_time_slote',array("business_id"=>$business_id));
                if(!empty($slot_info)){
                	if(empty($business_time_slote)){
                    	foreach($slot_info as $key=>$value)
                    	{
                        	$day_id   = @$value['day_id'];

                        	if(empty($day_id)){
                        		$day_id = $this->manage_week_days($value['name']);
                        	}
                        	foreach($value['slot_time'] as $value1)
                        	{
                            	$time_slote_from = $value1['time_slote_from'];
                            	$time_slote_to   = $value1['time_slote_to'];
                            	$data=array(
                                    'business_id'=>$business_id,
                                    'day_id'=>$day_id,
                                    'time_slote_from'=>$time_slote_from,
                                    'time_slote_to'=>$time_slote_to,
                                    'create_dt'=>$time,
                                    'update_dt'=>$time
                                );

	                            if($day_id !='' && $time_slote_from !='' && $time_slote_to !=''){
	                         		$business_time_slote =  $this->dynamic_model->insertdata('business_time_slote',$data);
	                         	}
                        	}
                    	}
						if($business_time_slote)
			        	{
							$arg['status']    = 1;
							$arg['error_code'] = HTTP_OK;
							$arg['error_line']= __line__;
					 		$arg['message']   = $this->lang->line('business_time_succ');
					 		$arg['data']      = [];
			        	}else{
			        		$arg['status']     = 0;
			            	$arg['error_code']  = HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['data']       = array();
							$arg['message']    = 'Please enter business hours of operation.';
			        	}
			     	}else{

			     		$updateArray = array();
			     		foreach($slot_info as $key=>$value) {
			     			$day_id   = $value['day_id'];
			     			foreach($value['slot_time'] as $value1)
                        	{
                        		$time_slote_from = $value1['time_slote_from'];
                            	$time_slote_to   = $value1['time_slote_to'];
                            	$time_slot_id    = $value1['time_slot_id'];
                            	$updateData = array(
                            		'id'				=>	$time_slot_id,
                            		'time_slote_from'	=>	$time_slote_from,
                            		'time_slote_to'		=>	$time_slote_to,
                            		'update_dt'			=>	time(),
                            	);
                            	array_push($updateArray, $updateData);
                        	}
			     		}
			     		if (!empty($updateArray)) {
                    		$this->db->update_batch('business_time_slote', $updateArray, 'id');
                    		$arg['status']    = 1;
							$arg['error_code'] = HTTP_OK;
							$arg['error_line']= __line__;
					 		$arg['message']   = $this->lang->line('business_time_succ');
					 		$arg['data']      = [];

                    	} else {
                    		$arg['status']     = 0;
			            	$arg['error_code']  = HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['data']       = array();
							$arg['message']    = $this->lang->line('server_problem');
                    	}
			     		/*$arg['status']     = 0;
                        $arg['error_code']  = REST_Controller::HTTP_OK;
                        $arg['error_line']= __line__;
                       	$arg['data']      = json_decode('{}');
                        $arg['message']    = $this->lang->line('already_availability'); */
			        }
			   }
		    }
		}
		echo json_encode($arg);
	}

	}


	public function business_class_time()
	{
		$arg   = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{

			$this->form_validation->set_rules('service_type','Service Type', 'required|trim');

			$this->form_validation->set_rules('class_id','Class Id', 'required|trim', array( 'required' => $this->lang->line('class_id_required')));

			$this->form_validation->set_rules('business_id','Business Id', 'required|trim', array( 'required' => $this->lang->line('business_id_required')));
			if ($this->form_validation->run() == FALSE)
			{
			  	$arg['status']  = 0;
			  	$arg['error_code'] = 0;
				$arg['error_line']= __line__;
			 	$arg['message'] = get_form_error($this->form_validation->error_array());
			}
			else
			{
				//echo encode(6);die;
				$time=time();
				$business_id      = decode($this->input->post('business_id'));
				$service_type      = $this->input->post('service_type');
				$class_id      = decode($this->input->post('class_id'));
				if($service_type == 1){
					$service_data= $this->dynamic_model->getdatafromtable('business_class',array("business_id"=>$business_id,"id"=>$class_id));
				}

				if($service_type == 2){
					$service_data= $this->dynamic_model->getdatafromtable('business_workshop',array("business_id"=>$business_id,"id"=>$class_id));
				}


				if(!empty($service_data)){
					$week_data= $this->dynamic_model->business_time_slote($business_id);
                    //$week_data= $this->dynamic_model->getdatafromtable('manage_week_days');

                    if(!empty($week_data)){
                    	$weekData['business_id']   = $week_data[0]['business_id'];
                        foreach($week_data as $key=>$value)
                        {
                           $i=0;

                            $weekData['id']   = $value['day_id'];
                            $weekData['name'] = $value['week_name'];
                            $time_slot_data= $this->dynamic_model->getdatafromtable('business_time_slote',array("business_id"=>$business_id,"day_id"=>$value['day_id']));
                            $slotarr= array();
                            foreach($time_slot_data as $key1=> $value1)
                            {

                            	$interval  = abs($value1['time_slote_from'] - $value1['time_slote_to']);

								$minutes   = round($interval / 60);

								//if($class_data[0]['duration'] < $minutes){

									$time_slot_data[$key1]['time_slot_id'] = $value1['id'];
	                               	$time_slot_data[$key1]['time_slote_from'] = $value1['time_slote_from'];
	                                $time_slot_data[$key1]['time_slote_to'] = $value1['time_slote_to'];
	                                unset($time_slot_data[$key1]['id']);
	                                unset($time_slot_data[$key1]['day_id']);
	                                unset($time_slot_data[$key1]['create_dt']);
	                                unset($time_slot_data[$key1]['update_dt']);
								//}
                            	if($service_data[0]['duration'] <= $minutes){
                               	 $slotarr        = $time_slot_data;
                            	}
                            }
                            $weekData['slot_time'] = $slotarr;
                            // $user_data= $this->dynamic_model->getdatafromtable('user',array('id'=>$usid));
                            $response[]        = $weekData;
                        }



                    }
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
                        $arg['message']    = $this->lang->line('record_not_found');
                    }
				}



		    }
		}
		echo json_encode($arg);
	}

	}

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
           $userdata = web_checkuserid();
           if($userdata['status'] != 1){
             $arg = $userdata;
            }
            else
            {
	           $_POST = json_decode(file_get_contents("php://input"), true);

			  if($_POST)
			  {
			   $this->form_validation->set_rules('business_id', 'Business Id', 'required',array(
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
                    $response=array();
                    $time=time();

                    $usid =$userdata['data']['id'];
                    $business_id  = decode($this->input->post('business_id'));
                    $week_data= $this->dynamic_model->business_time_slote($business_id);
                    //$week_data= $this->dynamic_model->getdatafromtable('manage_week_days');

                    if(!empty($week_data)){
                    	$weekData['business_id']   = $week_data[0]['business_id'];
                        foreach($week_data as $key=>$value)
                        {
                           $i=0;

                            $weekData['id']   = $value['day_id'];
                            $weekData['name'] = $value['week_name'];
                            $time_slot_data= $this->dynamic_model->getdatafromtable('business_time_slote',array("business_id"=>$business_id,"day_id"=>$value['day_id']));
                            foreach($time_slot_data as $key1=> $value1)
                            {
                            	$time_slot_data[$key1]['time_slot_id'] = $value1['id'];
                               $time_slot_data[$key1]['time_slote_from'] = $value1['time_slote_from'];
                                $time_slot_data[$key1]['time_slote_to'] = $value1['time_slote_to'];
                                unset($time_slot_data[$key1]['id']);
                                unset($time_slot_data[$key1]['day_id']);
                                unset($time_slot_data[$key1]['create_dt']);
                                unset($time_slot_data[$key1]['update_dt']);
                               // $slotarr[$i++]        = $$time_slot_data;
                            }
                            $weekData['slot_time'] = $time_slot_data;
                            // $user_data= $this->dynamic_model->getdatafromtable('user',array('id'=>$usid));
                            $response[]        = $weekData;
                        }



                    }
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
                        $arg['message']    = $this->lang->line('record_not_found');
                    }
                }
               }
           }
       }
       echo json_encode($arg);
    }

    public function get_customer_payment_requests()
    {
        $arg = array();
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
        }
        else
        {
           $userdata = web_checkuserid();
           if($userdata['status'] != 1){
             $arg = $userdata;
            }
            else
            {
	           $_POST = json_decode(file_get_contents("php://input"), true);

			  if($_POST)
			  {
			   $this->form_validation->set_rules('business_id', 'Business Id', 'required',array(
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
                    $response=array();
                    $time=time();

                    $usid =$userdata['data']['id'];
                    $business_id  = decode($this->input->post('business_id'));

                   	$response = $this->studio_model->user_payment_requests($business_id);
                   $requestArray= array();
                   foreach ($response as  $value) {

                   	 if($value['service_type']=='1'){
                   	 	$service_detail = $this->dynamic_model->getdatafromtable('business_passes',array("id"=>$value['service_id'],"business_id"=>$value['business_id']), '*', '', '', 'create_dt', 'desc');
                   	 	$service_name = $service_detail[0]['pass_name'];
                   	 	$service_type = 'Pass';
                   	 }

                   	 if($value['service_type']=='2'){
                   	 	$service_detail = $this->dynamic_model->getdatafromtable('service',array("id"=>$value['service_id'],"business_id"=>$value['business_id']), '*', '', '', 'create_dt', 'desc');
                   	 	$service_name = $service_detail[0]['service_name'];
                   	 	$service_type = 'Service';
                   	 }

                   	 if($value['service_type']=='3'){
                   	 	$service_detail = $this->dynamic_model->getdatafromtable('business_product',array("id"=>$value['service_id'],"business_id"=>$value['business_id']), '*', '', '', 'create_dt', 'desc');
                   	 	$service_name = $service_detail[0]['product_name'];
                   	 	$service_type = 'Product';
                   	 }

                   	 $requestArray[] = array(
                   	 					'full_name'=>$value['name'].' '.$value['lastname'],
	                   					'profile_img'=>$value['profile_img'],
	                   					'service_id'=>$value['service_id'],
	                   					'service_type_id'=>$value['service_type'],
	                   					'amount'=>$value['amount'],
	                   					'total_amount'=> $value['amount'] + $value['tax'],
	                   					'id'=>$value['id'],
	                   					'user_id'=>$value['user_id'],
	                   					'business_id'=>$value['business_id'],
	                   					'reference_payment_id'=>$value['reference_payment_id'],
	                   					'service_name'=>$service_name,
	                   					'service_type_name' => $service_type,
	                   					'status'=>$value['status']
                   	 				);
                   }
                    if($response){
                        $arg['status']     = 1;
                        $arg['error_code']  = REST_Controller::HTTP_OK;
                        $arg['error_line']= __line__;
                        $arg['data']       = $requestArray;
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

    public function get_customer_payment_requests_details()
    {
        $arg = array();
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
        }
        else
        {
           $userdata = web_checkuserid();
           if($userdata['status'] != 1){
             $arg = $userdata;
            }
            else
            {
	           $_POST = json_decode(file_get_contents("php://input"), true);

			  if($_POST)
			  {
			   $this->form_validation->set_rules('business_id', 'Business Id', 'required',array(
						'required' => $this->lang->line('business_id_req')
					));
			   $this->form_validation->set_rules('customer_id', 'Customer Id', 'required');
			   $this->form_validation->set_rules('service_id', 'Service Id', 'required');
			   $this->form_validation->set_rules('service_type', 'Service type', 'required');
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

                   	$request_id  = $this->input->post('request_id');
                    $business_id  = decode($this->input->post('business_id'));
                    $customer_id  = $this->input->post('customer_id');
                    $service_id  = $this->input->post('service_id');
                    $service_type  = $this->input->post('service_type');

                   	$response = $this->studio_model->user_payment_requests_details($business_id,$customer_id,$request_id);

                   	if(!empty($response)){
                   		$dob=$response[0]['date_of_birth'];
	    				$diff = (date('Y') - date('Y',strtotime($dob)));

	    				if($service_type=='1'){
	                   	 	$service_detail = $this->studio_model->getpassesDetails($business_id,$service_id);
	                   	 	$service_type = 'Pass';

	                   	 	$dataArray = array(
	                   					'full_name'=>$response[0]['name'].' '.$response[0]['lastname'],
	                   					'profile_img'=>$response[0]['profile_img'],
	                   					'age'=>$diff,
	                   					'gender'=>$response[0]['gender'],
	                   					'service_id'=>$response[0]['service_id'],
	                   					'service_type_id'=>$response[0]['service_type'],
	                   					'amount'=>$response[0]['amount'],
	                   					'total_amount'=> $response[0]['amount'] + $response[0]['tax'],
	                   					'user_id'=>$response[0]['user_id'],
	                   					'business_id'=>$response[0]['business_id'],
	                   					'reference_payment_id'=>$response[0]['reference_payment_id'],
	                   					'service_name'=>$service_detail[0]['pass_name'],
	                   					'service_type_name' => $service_type,
	                   					'is_one_time_purchase'=>$service_detail[0]['is_one_time_purchase'],
	                   					'description'=>$service_detail[0]['description'],
	                   					'tax'=>$response[0]['tax'],
	                   					'pass_start_date'=>$service_detail[0]['purchase_date'],
	                   					'pass_end_date'=>$service_detail[0]['pass_end_date'],
	                   					'pass_type'=>$service_detail[0]['pass_type'],
	                   					'family_user_id'=>$response[0]['family_user_id'],
	                   					'status'=>$response[0]['status'],
	                   					'payment_mode' =>$response[0]['ub_payment_mode'],
	                   					'payment_note' =>$response[0]['ub_payment_note'],
	                   					'transaction_id' =>$response[0]['ub_transaction_id']
	                   				);
	                   	 }

	                   	 if($service_type=='2'){
	                   	 	$service_detail = $this->studio_model->getserviceDetails($business_id,$service_id);

	                   	 	$service_type = 'Service';

	                   	 	$dataArray = array(
	                   					'full_name'=>$response[0]['name'].' '.$response[0]['lastname'],
	                   					'profile_img'=>$response[0]['profile_img'],
	                   					'age'=>$diff,
	                   					'gender'=>$response[0]['gender'],
	                   					'service_id'=>$response[0]['service_id'],
	                   					'service_type_id'=>$response[0]['service_type'],
	                   					'amount'=>$response[0]['amount'],
	                   					'total_amount'=> $response[0]['amount'] + $response[0]['tax'],
	                   					'user_id'=>$response[0]['user_id'],
	                   					'business_id'=>$response[0]['business_id'],
	                   					'service_name'=>$service_detail[0]['service_name'],
	                   					'service_type_name' => $service_type,
	                   					'service_category'=>$service_detail[0]['category_name'],
	                   					'from_time'=>$service_detail[0]['from_time'],
	                   					'to_time'=>$service_detail[0]['to_time'],
	                   					'instructor'=>$service_detail[0]['name'].' '.$service_detail[0]['lastname'],
	                   					'slot_date'=>$response[0]['slot_date'],
	                   					'family_user_id'=>$response[0]['family_user_id'],
	                   					'tax'=>$response[0]['tax'],
	                   					'status'=>$response[0]['status'],
	                   					'payment_mode' =>$response[0]['ub_payment_mode'],
	                   					'payment_note' =>$response[0]['ub_payment_note'],
	                   					'transaction_id' =>$response[0]['ub_transaction_id']
	                   				);
	                   	 }

	                   	 if($service_type=='3'){
	                   	 	$service_detail = $this->studio_model->getproductDetails($response[0]['service_id'],$response[0]['business_id']);
	                   	 	//print_r($service_detail); die;
	                   	 	$service_type = 'Product';
	                   	 	$dataArray = array(
	                   					'full_name'=>$response[0]['name'].' '.$response[0]['lastname'],
	                   					'profile_img'=>$response[0]['profile_img'],
	                   					'age'=>$diff,
	                   					'gender'=>$response[0]['gender'],
	                   					'service_id'=>$response[0]['service_id'],
	                   					'service_type_id'=>$response[0]['service_type'],
	                   					'amount'=>$response[0]['amount'],
	                   					'total_amount'=> $response[0]['amount'] + $response[0]['tax'],
	                   					'user_id'=>$response[0]['user_id'],
	                   					'business_id'=>$response[0]['business_id'],
	                   					'reference_payment_id'=>$response[0]['reference_payment_id'],
	                   					'tax'=>$response[0]['tax'],
	                   					'service_name'=>$service_detail[0]['product_name'],
	                   					'service_type_name' => $service_type,
	                   					'description'=>$service_detail[0]['description'],
	                   					'image'=>$service_detail[0]['product_image'],
	                   					'quantity'=>$service_detail[0]['quantity'],
	                   					'product_id'=>$service_detail[0]['product_id'],
	                   					'family_user_id'=>$response[0]['family_user_id'],
	                   					'status'=>$response[0]['status'],
	                   					'payment_mode' =>$response[0]['ub_payment_mode'],
	                   					'payment_note' =>$response[0]['ub_payment_note'],
	                   					'transaction_id' =>$response[0]['ub_transaction_id']
	                   				);

	                   	 }
                   	}



                   // echo $this->db->last_query(); die;
                    if($dataArray){
                        $arg['status']     = 1;
                        $arg['error_code']  = REST_Controller::HTTP_OK;
                        $arg['error_line']= __line__;
                        $arg['data']       = $dataArray;
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


    public function update_user_payment_request()
    {
        $arg = array();
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
        }
        else
        {
           $userdata = web_checkuserid();
           if($userdata['status'] != 1){
             $arg = $userdata;
            }
            else
            {
	           $_POST = json_decode(file_get_contents("php://input"), true);

			  if($_POST)
			  {
			   $this->form_validation->set_rules('business_id', 'Business Id', 'required',array(
						'required' => $this->lang->line('business_id_req')
					));
			   $this->form_validation->set_rules('customer_id', 'Customer Id', 'required');
			   $this->form_validation->set_rules('service_id', 'Service Id', 'required');
			   $this->form_validation->set_rules('service_type', 'Service type', 'required');
			   $this->form_validation->set_rules('transaction_id', 'transaction Id', 'required');
			   $this->form_validation->set_rules('amount', 'Amount', 'required');
			   $this->form_validation->set_rules('tax_amount', 'Tax', 'required');
			   $this->form_validation->set_rules('payment_mode', 'Payment mode', 'required');
			   $this->form_validation->set_rules('payment_note', 'Payment note', 'required');
			   $this->form_validation->set_rules('reference_payment_id', 'Reference payment Id', 'required');


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


                    $business_id  = decode($this->input->post('business_id'));
                    $customer_id  = $this->input->post('customer_id');
                    $service_id  = $this->input->post('service_id');
                    $service_type  = $this->input->post('service_type');
                    $transaction_id  = $this->input->post('transaction_id');
                    $amount  = $this->input->post('amount');
                    $tax_amount  = $this->input->post('tax_amount');
                    $payment_mode  = $this->input->post('payment_mode');
                    $reference_payment_id  = $this->input->post('reference_payment_id');
                    $payment_note  = $this->input->post('payment_note');
                    $quantity  = $this->input->post('quantity');
                    $service_slot_id  = $this->input->post('service_slot_id');
                    $passes_start_date  = $this->input->post('passes_start_date');
                    $passes_end_date  = $this->input->post('passes_end_date');


                   	if($service_type==1){
                   		$where = array('id' => $service_id);
                   		$passData = $this->dynamic_model->getdatafromtable('business_passes',$where);
                   		$class_id  = ($passData)?$passData[0]['service_id']:0;

                   	}
                   	if($service_type==2){
                   		$class_id=0;
                   	}
                   	if($service_type==3){
                   		$class_id=0;
                   	}

					$passes_total_count = 0;
					$passes_remaining_count = 0;
					$passes_start_date = 0;
					$passes_end_date = 0;

					if($service_type==1)
					{
						$where1 = array('id'=>$service_id,'status' => 'Active');
						$business_pass= $this->dynamic_model->getdatafromtable('business_passes',$where1);

						$passes_start_date = $time;
						$validity = (!empty($business_pass[0]['pass_validity'])) ? $business_pass[0]['pass_validity'] : 0;
						$getEndDate = ($validity * 24 * 60 * 60) + $time;
						$passes_end_date = ($validity == 0) ? $passes_start_date : $getEndDate;


						$pass_type_subcat=$business_pass[0]['pass_type_subcat'];
						$where = array('id'=>$pass_type_subcat);
						$manage_pass_type = $this->dynamic_model->getdatafromtable('manage_pass_type',$where);
						$pass_days = $manage_pass_type[0]['pass_days'];

						$passes_total_count = $pass_days;
						$passes_remaining_count = $pass_days;
					}



                   	$dataArray = array(
	                   					'service_id'=>$service_id,
	                   					'class_id'=>$class_id,
	                   					'service_type'=>$service_type,
	                   					'amount'=>($amount)?$amount:0.00,
	                   					'sub_total'=>($amount)?$amount:0.00,
	                   					'user_id'=>$customer_id,
	                   					'business_id'=>$business_id,
	                   					'transaction_id'=>$transaction_id,
	                   					'reference_payment_id'=>$reference_payment_id,
	                   					'payment_note'=>$payment_note,
	                   					'payment_mode' => $payment_mode,
	                   					'quantity'=>($quantity)?$quantity:0,
	                   					'service_slot_id'=>($service_slot_id)?$service_slot_id:0,
	                   					'tax_amount'=>($tax_amount)?$tax_amount :0.00,
	                   					'status'=>'Success',
	                   					'create_dt'=> $time
									   );
					if ($service_type == 1) {
						$dataArray['passes_status'] = '1';
						$dataArray['passes_start_date'] = $passes_start_date;
						$dataArray['passes_end_date'] = $passes_end_date;
						$dataArray['passes_total_count'] = $passes_total_count;
						$dataArray['passes_remaining_count'] = $passes_remaining_count;
					}
                   	$bookingData= $this->dynamic_model->insertdata('user_booking',$dataArray);

                   	if($bookingData){
                   		$data = array('status' => 'Success');
						$where1 = array('reference_payment_id' => $reference_payment_id);
						$varify = $this->dynamic_model->updateRowWhere('user_payment_requests', $where1, $data);
                   	}
                   	$transaction_data = array(
                                   'user_id'                =>$customer_id,
                                   'amount'                 =>($amount)?$amount:0.00,
                                    'trx_id'                =>$transaction_id,
                                    'order_number'          =>time(),
                                    'transaction_type'      =>($service_type==2) ? 3 : $service_type,
                                    'payment_status'        =>"Success",
                                    'saved_card_id'         =>0,
                                    'create_dt'        		=>time(),
                                    'update_dt'        		=>time()
		                            );
		            $transaction_id=$this->dynamic_model->insertdata('transactions',$transaction_data);

                   // echo $this->db->last_query(); die;
                    if($dataArray){
                        $arg['status']     = 1;
                        $arg['error_code']  = REST_Controller::HTTP_OK;
                        $arg['error_line']= __line__;
                        $arg['message']    = 'Payment request updated successfully';
                        $arg['data']    = $dataArray;

                    }else{
                        $arg['status']     = 0;
                        $arg['error_code']  = REST_Controller::HTTP_OK;
                        $arg['error_line']= __line__;
                        $arg['message']    = 'Payment request updation failed';
                        $arg['data']    = json_decode('{}');
                    }
                }
               }
           }
       }
       echo json_encode($arg);
    }



    public function class_scheduling_inactive(){
    	/* $date = date('Y-m-d');
    	$where = array('status'=>'Active');
    	$classData = $this->dynamic_model->getdatafromtable('business_class',$where);
    	if(!empty($classData)){
    		foreach ($classData as  $value) {
    			if($value['end_date'] < $date ){
    				$data = array('status' => 'Deactive');
					$where1 = array('id' => $value['id']);
					$varify = $this->dynamic_model->updateRowWhere('business_class', $where1, $data);

    			}
    		}
    	} */

    	$workshopData = $this->dynamic_model->getdatafromtable('business_workshop',$where);
    	if(!empty($workshopData)){
    		foreach ($workshopData as  $value) {
    			if($value['end_date'] < $date ){
    				$data = array('status' => 'Deactive');
					$where1 = array('id' => $value['id']);
					$varify = $this->dynamic_model->updateRowWhere('business_workshop', $where1, $data);

    			}
    		}
    	}
    }

    public function passes_status_change()
	{
		$arg = array();
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
        }
        else
        {
		   $userdata = web_checkuserid();
           if($userdata['status'] != 1){
             $arg = $userdata;
            }
            else
			{
				$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
				{
				    $this->form_validation->set_rules('service_type','Service Type','required|trim', array( 'required' => $this->lang->line('service_type_required')));
				     $this->form_validation->set_rules('user_id','User Id','required|trim');
				    $this->form_validation->set_rules('service_id','Service Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('passes_status','Service Id', 'required|trim', array( 'required' => $this->lang->line('passes_status_required')));
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

                		$lat = $this->input->get_request_header('lat');
						$lang = $this->input->get_request_header('lang');
						$room_capacity=$waitng_list_no='';
						$usid =$userdata['data']['id'];
						$updateData=$response=array();
						$time=time();
						$date = date("Y-m-d",$time);
						$service_id    = $this->input->post('service_id');
						$user_id    = $this->input->post('user_id');
						//service_type=> 1 class 2 workshop 3 trainer
						$service_type    = $this->input->post('service_type');
						// passes_status=> checkin checkout cancel
						$passes_status    = $this->input->post('passes_status');
						$schedule_id 	  = $this->input->post('schedule_id');
						$attendance_id 	  = $this->input->post('attendance_id');
						$today_date = date("Y-m-d");

						$day_update = 1;
						if(!empty($attendance_id)){
                            $whe="id = '".$attendance_id."'";
                            $passes_data = $this->dynamic_model->getdatafromtable('user_attendance',$whe);
                            if(!empty($passes_data)){
                                $pass_id = $passes_data[0]['pass_id'];
                            }

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
                    	}

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

						if($passes_status == 'cancel') {
							$where = array('id'=>$service_id,'status'=>"Active");
							$business_class= $this->dynamic_model->getdatafromtable('business_class',$where);
                            $business_id =  $business_class[0]['business_id'];

                            $whe="user_id = '".$user_id."' AND service_id = '".$service_id."' AND schedule_id = '".$schedule_id."'";
                            $user_attendance_check = $this->dynamic_model->getdatafromtable('user_attendance',$whe);
                            $pass_id = $user_attendance_check[0]['pass_id'];
							$attendance_id 	  = $this->input->post('attendance_id');

							//$whe="user_id = '".$user_id."' AND business_id = '".$business_id."' AND passes_remaining_count != '0' AND passes_status = '1' AND status = 'Success' AND service_id = '".$pass_id."'";
							$current_time = time();
                            $whe="user_id = '".$user_id."' AND business_id = '".$business_id."' AND service_id = '".$pass_id."' AND status = 'Success' AND passes_end_date >= '".$current_time."'";
							//die;
                            $pass_status_check = $this->dynamic_model->getdatafromtable('user_booking',$whe);

                            if (!empty($pass_status_check)) {

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
								// $this->dynamic_model->updateRowWhere('user_booking', array('id' => $user_booking_id), $updateData);
								if(!empty($day_update)){
									$this->dynamic_model->updateRowWhere('user_booking', array('id' => $user_booking_id), $updateData);
								}


                                /* $user_booking_id = $pass_status_check[0]['id'];
                                $passes_remaining_count = ($pass_status_check[0]['passes_remaining_count'] + 1);

                            	$updateData =   array(
                                    'passes_remaining_count' =>  $passes_remaining_count
                                );
                            	$this->dynamic_model->updateRowWhere('user_booking', array('id' => $user_booking_id), $updateData); */
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

							$this->dynamic_model->updateRowWhere('user_attendance', array('service_type' => 1, 'user_id' => $user_id, 'service_id' => $service_id, 'schedule_id' => $schedule_id), array('status' => $status));

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


						/* $where = array('id'=>$service_id,'status'=>"Active");
						//find capcity class or workshop
						if($service_type==1){
							$business_class= $this->dynamic_model->getdatafromtable('business_class',$where);
							$business_id=(!empty($business_class[0]['business_id'])) ? $business_class[0]['business_id'] : 0;
							$room_capacity=(!empty($business_class[0]['capacity'])) ? $business_class[0]['capacity'] : 0;
							$class_days_prior_signup = $business_class[0]['class_days_prior_signup'] ? $business_class[0]['class_days_prior_signup'] : 1;
							$start_date = $business_class[0]['start_date'];
							$duration = $business_class[0]['duration'];

							if($passes_status == 'singup')
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
										echo json_encode($arg);exit;
									}

								}
							}

						}elseif($service_type==2){
							$business_workshop= $this->dynamic_model->getdatafromtable('business_workshop',$where);
							$business_id=(!empty($business_workshop[0]['business_id'])) ? $business_workshop[0]['business_id'] : 0;
							$room_capacity=(!empty($business_workshop[0]['capacity'])) ? $business_workshop[0]['capacity'] : 0;

							$duration = $business_workshop[0]['duration'];

							$class_days_prior_signup = $business_workshop[0]['workshop_days_prior_signup'] ? $business_workshop[0]['workshop_days_prior_signup'] : 1;
							$start_date = $business_workshop[0]['start_date'];
                          	if($passes_status == 'singup')
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

							}
                    	}
						//signup process
						$condition="user_id=".$usid." AND service_id=".$service_id;
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
											'user_id'  		=>$usid,
											'status'  		=>$passes_status,
											'service_id'    =>$service_id,
											'service_type'  =>$service_type,
											'checkin_time'  =>0,
											'checkout_time' =>0,
											'signup_status' =>1,
											'create_dt'   	=>$time,
											'update_dt'   	=>$time,
                                            'checkin_dt'=>date('Y-m-d',$time)
						                   );

				        	$checkId= $this->dynamic_model->insertdata('user_attendance',$insertData);

				        }else{

							//find data today wise
							$whe="user_id=".$usid." AND service_id=".$service_id." AND service_type=".$service_type." AND DATE(FROM_UNIXTIME(create_dt))='".$date."'";
							$check_pass= $this->dynamic_model->getdatafromtable('user_attendance',$whe);

							//check room capacity
							$where1="service_id=".$service_id." AND service_type=".$service_type." AND status='checkin' AND DATE(FROM_UNIXTIME(create_dt))='".$date."'";
							$check_in_count= getdatacount('user_attendance',$where1);
							$getTime=strtotime(date('H:i:s',$time));

							$checkout_time = $time + ((int)$duration*60);

							if(empty($check_pass)){
							$insertData =   array(
												'user_id'  		=>$usid,
												'status'  		=>$passes_status,
												'service_id'    =>$service_id,
												'service_type'  =>$service_type,
												'checkin_time'  =>$time,
												'checkout_time' =>$checkout_time,
												'signup_status' =>1,
												'create_dt'   	=>$time,
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
									$where3="user_id=".$usid." AND service_id=".$service_id." AND service_type=".$service_type." AND status='waiting' AND DATE(FROM_UNIXTIME(create_dt))='".$date."'";
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
							}elseif($passes_status=='singup'){

								$updateData['status']=$passes_status;
							}

							$checkout_time = $time + ((int)$duration*60);
							$updateData['checkout_time']=$checkout_time;

							$updateData['update_dt']=$time;
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
				        } */

					}
				}
		    }
        }
	  echo json_encode($arg);
    }




    public function subscription_inactive(){
    	$date = strtotime(date('Y-m-d'));
    	$where = "subscription_period < ".$date." AND status='Active'";
    	$userData = $this->dynamic_model->getdatafromtable('user',$where);
    	if(!empty($userData)){
    		foreach ($userData as  $value) {
				$data = array('status' => 'Deactive');
				$where1 = array('id' => $value['id']);
				$varify = $this->dynamic_model->updateRowWhere('user', $where1, $data);
    		}
    	}
    }

    public function subscription_notification(){
    	$current_date = date('Y-m-d');
    	$nextDate = date('Y-m-d',strtotime($current_date. ' + 7 days'));
    	$where = "subscription_period < ".$nextDate." AND status='Active'";
    	$userData = $this->dynamic_model->getdatafromtable('user',$where);
    	if(!empty($userData)){
    		foreach ($userData as  $value) {
				// send notifications
    		}
    	}

    }

    public function get_user_details()
    {
        $arg = array();
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
        }
        else
        {
           $userdata = web_checkuserid();
           if($userdata['status'] != 1){
             $arg = $userdata;
            }
            else
            {
	           $_POST = json_decode(file_get_contents("php://input"), true);

			  if($_POST)
			  {
			   $this->form_validation->set_rules('user_id', 'User Id', 'required');
			   $this->form_validation->set_rules('role_id', 'Role Id', 'required');
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

					$business_id = decode($userdata['data']['business_id']);
					if ($this->input->post('business_id')) {
						$business_id  = decode($this->input->post('business_id'));
					}
					$user_id = decode($this->input->post('user_id'));
                    $role_id = $this->input->post('role_id');
                    $arg['user_id'] = $user_id;
                    $arg['role_id'] = $role_id;
                    // echo json_encode($arg); exit;
                    $data_val  = get_user_details($user_id,$role_id);

					$passes = array();
					$query = "SELECT business_passes.id, business_passes.pass_name, business_passes.pass_validity, business_passes.pass_type, business_passes.pass_type_subcat, user_booking.passes_start_date as start_date, user_booking.passes_end_date as end_date, user_booking.sub_total FROM user_booking  JOIN business_passes on (business_passes.id = user_booking.service_id) WHERE user_booking.business_id = ".$business_id." and user_booking.service_type = 1 and user_booking.user_id =  ".$user_id." and passes_status = 1 AND user_booking.status != 'Pending'";

					$purchase_passes_data = $this->dynamic_model->getQueryResultArray($query);
					if (!empty($purchase_passes_data)) {
						foreach($purchase_passes_data as $pass) {
							array_push($passes_ids, $pass['id']);
							$collection['pass_id'] = $pass['id'];
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

					if($data_val){
						$data_val['purchase_passes'] = $purchase_passes_data;
                        $arg['status']     = 1;
                        $arg['error_code']  = REST_Controller::HTTP_OK;
                        $arg['error_line']= __line__;
                        $arg['data']       = $data_val;
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


    public function get_pass_details()
    {
        $arg = array();
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
        }
        else
        {
           $userdata = web_checkuserid();
           if($userdata['status'] != 1){
             $arg = $userdata;
            }
            else
            {
	           $_POST = json_decode(file_get_contents("php://input"), true);

			  if($_POST)
			  {
			   $this->form_validation->set_rules('pass_id', 'Pass Id', 'required');
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

                    $usid =decode($userdata['data']['id']);
					$business_id = decode($userdata['data']['business_id']);
                    $pass_id = decode($this->input->post('pass_id'));
					$data_val  = studiopassesdetails($pass_id);

                    if($data_val){
						$data_val['pass_type'] = $data_val['pass_type_id'];
						$data_val['pass_sub_type'] = $data_val['pass_sub_type_id'];

						$data_val['class_id'] = $this->db->query('SELECT DISTINCT class_id AS class_id, business_class.class_name FROM business_passes_associates as bpa JOIN business_class on (business_class.id = bpa.class_id) WHERE bpa.business_id = '.$business_id.' AND bpa.pass_type = 0 AND bpa.pass_id = '.$data_val['pass_id'])->result_array();

						$data_val['class'] = $this->db->query('SELECT bc.id, bc.class_name FROM business_class as bc WHERE bc.status = "Active" AND bc.business_id = '.$business_id.' AND bc.id NOT IN (SELECT bpa.class_id FROM business_passes_associates as bpa WHERE bpa.pass_id = '.$data_val['pass_id'].' AND bpa.business_id = '.$business_id.')')->result_array();

						$arg['status']     = 1;
                        $arg['error_code']  = REST_Controller::HTTP_OK;
                        $arg['error_line']= __line__;
						$arg['data']       = $data_val;
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

	public function search_customer_list() {
		$arg = array();
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
		} else
        {
			$userdata = web_checkuserid();

			if($userdata['status'] != 1){
				$arg = $userdata;
			} else {

				$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
			  	{
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
					$this->form_validation->set_rules('search_val', 'search value', 'required');
					if($this->form_validation->run() == FALSE)
					{
						$arg['status']  = 0;
						$arg['error_code'] = 0;
						$arg['error_line']= __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());

					} else {
						$response=array();
                    	$time=time();

						$usid 		= decode($userdata['data']['id']);
						$business_id= decode($userdata['data']['business_id']);
						$page_no	= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no	= $page_no-1;
						$limit    	= config_item('page_data_limit');
						$offset 	= $limit * $page_no;
						$search_val	= $this->input->post('search_val');

						// availability_status = "Available" AND
						$where= 'mobile_verified = 1 AND id != '.$usid.' AND email_verified = 1 AND (user.name LIKE "%'.$search_val.'%" OR user.lastname LIKE "%'.$search_val.'%" OR user.mobile LIKE "%'.$search_val.'%" OR user.email LIKE "%'.$search_val.'%")';

						$client_data = $this->dynamic_model->getdatafromtable('user',$where, '*', $limit, $offset, 'name');
						$client_total = $this->dynamic_model->getdatafromtable('user',$where, '*');
						if($client_data) {
							foreach($client_data as $value) {
								if ( !empty($value['name']) && !empty($value['name']) && !empty($value['name']) ) {
									$clientdata['id']     = encode($value['id']);
									$clientdata['name']   = ucwords($value['name'].' '.$value['lastname']);
									$clientdata['email']  = $value['email'];
									$clientdata['profile_img']  = base_url().'uploads/user/'.$value['profile_img'];
									$clientdata['country_code'] = !empty($value['country_code']) ? $value['country_code'] : '';
									$clientdata['mobile'] = $value['mobile'];
									$clientdata['date_of_birth'] =  !empty($value['date_of_birth']) ? $value['date_of_birth'] : '';
									$clientdata['gender'] = $value['gender'];
									$response[] = $clientdata;
								}

							}


							$arg['status']    = 1;
							$arg['error_code'] = HTTP_OK;
							$arg['error_line']= __line__;
							$arg['data']      = $response;
							$arg['limit']      = $limit;
							$arg['total']      = count($client_total);
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
		}

		echo json_encode($arg);
	}

	public function search_customer_details() {
		$arg = array();
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
		} else
        {
			$userdata = web_checkuserid();

			if($userdata['status'] != 1){
				$arg = $userdata;
			} else {

				$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
			  	{
					$this->form_validation->set_rules('client_id', 'Client Id', 'required',array(
						'required' => 'Client is required',
						'numeric' => 'Client is required',
					));

					/*$this->form_validation->set_rules('class_id', 'Class Id', 'required',array(
						'required' => 'Class is required',
						'numeric' => 'Class is required',
					));*/

					if($this->form_validation->run() == FALSE)
					{
						$arg['status']  = 0;
						$arg['error_code'] = 0;
						$arg['error_line']= __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());

					} else {
						$response=array();
                    	$time=time();

                    	$class_id = decode($this->input->post('class_id'));
						$usid 		= decode($userdata['data']['id']);
						$business_id= decode($userdata['data']['business_id']);
						$user_id = decode($this->input->post('client_id'));
						$couter = $this->studio_model->get_rows('user', array('id' => $user_id));
						$business_info = $this->dynamic_model->getQueryRowArray('SELECT * FROM business where id = '.$business_id);
						if ($couter) {
							$where = ' id = '.$user_id;
							$customerDetails = getuserdetail($user_id, 3, $user_id);
							$client_details = $this->dynamic_model->getdatafromtable('user',$where, 'id, name, lastname, email, profile_img, country_code, mobile, date_of_birth, gender, referred_by, address, emergency_contact_person, emergency_contact_no, zipcode, country, state, city, lat, lang');
							$product_ids = array();
							$passes_ids = array();
							$response  = array();
							foreach($client_details as $value) {
								$isWaiver = $this->db->query('select * from business_waiver where user_id = '.$user_id.' AND business_id = '.$business_id);
								$waiver = 0;
								if ($isWaiver->num_rows() > 0) {
									$row = $isWaiver->row_array();
									$waiver = $row['isWaiver'];
								}
								$clientdata['id'] = $value['id'];
								// $clientdata['customerDetails'] = $customerDetails;
								$clientdata['name']   = ucwords($value['name'].' '.$value['lastname']);
								$clientdata['email']   = $value['email'];
								$clientdata['mobile']   = $value['mobile'];
								$clientdata['referred_by']   = $value['referred_by'];
								$clientdata['address']   = $value['address'];
								$clientdata['zipcode']   = $value['zipcode'];
								$clientdata['country']   = $value['country'];
								$clientdata['state']   = $value['state'];
								$clientdata['city']   = $value['city'];
								$clientdata['lat']   = $value['lat'];
								$clientdata['lang']   = $value['lang'];
								$clientdata['contact_person']   = $value['emergency_contact_person'];
								$clientdata['contact_no']   = $value['emergency_contact_no'];
								$clientdata['waiver']   = $waiver;
								$clientdata["profile_img"] = base_url().'uploads/user/'.$value['profile_img'];
								$clientdata['country_code'] = !empty($value['country_code']) ? $value['country_code'] : '';
								$clientdata['date_of_birth'] =  !empty($value['date_of_birth']) ? $value['date_of_birth'] : '';
								$clientdata['family_member'] = get_family_member($value['id'], $value['id']);

								$time_zone = $this->input->get_request_header('Timezone', true);
								$time_zone = $time_zone ? $time_zone : 'UTC';
								date_default_timezone_set($time_zone);

								$customerId = $value['id'];
								$clientdata['attendance'] = array(
									'class' 	=> $this->attendanceCount($business_id, $customerId, 1),
									'workshop'	=> $this->attendanceCount($business_id, $customerId, 2),
									'appointment'	=> $this->attendanceCount($business_id, $customerId, 3),
								);
								$where = ' business_id = "'.$business_id.'" AND service_type = "1" AND passes_status IN (1, 3) AND user_id = "'.$user_id.'"';
								$purchase_product_data = $this->dynamic_model->getQueryResultArray('SELECT DISTINCT business_product.id, business_product.product_name, business_product.description, business_product.product_id, business_product.price, business_product_images.image_name as pro_image FROM user_booking JOIN business_product ON (business_product.id = user_booking.service_id) JOIN business_product_images on (business_product_images.product_id = business_product.id) WHERE user_booking.business_id = '.$business_id.' AND user_booking.service_type = 3 AND user_booking.status != "Pending" AND user_booking.user_id = '.$user_id);

								$product = array();
								if (!empty($purchase_product_data)) {

									foreach($purchase_product_data as $pro) {
										$info['product_name'] = $pro['product_name'];
										$info['product_id'] = $pro['product_id'];
										$info['product_description'] = $pro['description'];
										$info['price'] = $pro['price'];
										$info['pro_image'] = base_url().'uploads/products/'.$pro['pro_image'];
										$info['studio_logo'] = base_url().'uploads/business/'.$business_info['logo'];
										array_push($product_ids, $pro['id']);
										$product[] = $info;
									}
								}
								$clientdata['purchase_product_data'] = $product;


								$purchase_passes_data = $this->dynamic_model->getQueryResultArray('SELECT user_booking.*, business_passes.id, business_passes.pass_name, business_passes.pass_validity,business_passes.is_recurring_stop, business_passes.pass_type, business_passes.pass_type_subcat, user_booking.passes_start_date as start_date, user_booking.passes_end_date as end_date, user_booking.sub_total FROM `user_booking` JOIN business_passes on (business_passes.id = user_booking.service_id) WHERE user_booking.business_id = '.$business_id.' and user_booking.service_type = 1 and user_booking.user_id = '.$user_id.' and passes_status = 1 AND user_booking.status != "Pending"');

								$passes = array();
								if (!empty($purchase_passes_data)) {
									foreach($purchase_passes_data as $valuess) {
										array_push($passes_ids, $valuess['id']);
										$passesdata = getpassesdetails($valuess['service_id'],$user_id);

										$business_ids = $valuess['business_id'];
										$business_info = $this->dynamic_model->getQueryRowArray('SELECT *  FROM business WHERE id = '.$business_ids);
										$passesdata['business_logo'] =  empty($business_info['business_image']) ? '' : site_url().'uploads/business/'.$business_info['business_image'];

										$passesdata['start_date'] = date('d M Y ',$valuess['passes_start_date']);
                                		$passesdata['end_date'] = date('d M Y ',$valuess['passes_end_date']);

                                		$passesdata['start_date_utc'] = $valuess['passes_start_date'];
                                		$passesdata['end_date_utc'] = $valuess['passes_end_date'];
                                		$passesdata['is_recurring_stop'] = $valuess['is_recurring_stop'];

                                		$passesdata['status'] = $valuess['status'];

										$passes[]   = $passesdata;

										/*$collection['pass_id'] = $pass['id'];
										$collection['pass_name'] = $pass['pass_name'];
										$collection['pass_type'] = get_passes_type_name($pass['pass_type']);
										$collection['pass_type_subcat'] = get_passes_type_name($pass['pass_type'], $pass['pass_type_subcat']);
										$collection['pass_validity'] = $pass['pass_validity'];
										$collection['start_date'] = $pass['start_date'];
										$collection['end_date'] = $pass['end_date'];
										$collection['sub_total'] = $pass['sub_total'];
										$collection['studio_logo'] = base_url().'uploads/business/'.$business_info['logo'];
										$passes[] = $collection;*/
									}
								}


								$clientdata['purchase_passes_data'] = $passes;

								$queryPro = "SELECT (SELECT id FROM user_booking as u where u.service_id = business_product.id AND u.status = 'Pending' AND u.user_id = '".$user_id."' and u.service_type = '3') as added_incart, business_product.id, business_product.product_name, business_product.price, business_product_images.image_name as pro_image, business_product.product_id, business_product.quantity, business_product.description, business_product.tax1, business_product.tax2, business_product.tax1_rate, business_product.tax2_rate  FROM business_product JOIN business_product_images on (business_product_images.product_id = business_product.id) WHERE business_product.status = 'Active' AND business_product.business_id = '".$business_id."' GROUP by business_product.id";
								if (!empty($product_ids)) {
									// $queryPro .= ' AND business_product.id NOT IN ('.implode(',', $product_ids).')';
								}

								$avl_product = array();
								$avaliableProduct = $this->dynamic_model->getQueryResultArray($queryPro);
								if (!empty($avaliableProduct)) {
									foreach($avaliableProduct as $pro) {
										$avaliable_collection['id'] = $pro['id'];
										$avaliable_collection['product_name'] = $pro['product_name'];
										$avaliable_collection['price'] = $pro['price'];
										$avaliable_collection['pro_image'] = base_url().'uploads/products/'.$pro['pro_image'];
										$avaliable_collection['studio_logo'] = base_url().'uploads/business/'.$business_info['logo'];
										$avaliable_collection['product_id'] = $pro['product_id'];
										$avaliable_collection['quantity'] = $pro['quantity'];
										$avaliable_collection['description'] = $pro['description'];
										$avaliable_collection['tax1'] = $pro['tax1'];
										$avaliable_collection['tax2'] = $pro['tax2'];
										$avaliable_collection['tax1_rate'] = ($pro['tax1'] == 'Yes') ? $pro['tax1_rate'] : '0';
										$avaliable_collection['tax2_rate'] = ($pro['tax2'] == 'Yes') ? $pro['tax2_rate'] : '0';
										$avaliable_collection['added_incart'] = $pro['added_incart'] ? 1 : 0;
										$avl_product[] = $avaliable_collection;
									}
								}

								$clientdata['avaliable_product_data']    =  $avl_product;

								if(!empty($class_id)){
									$queryPasses = "SELECT mp.pass_days, (SELECT id FROM user_booking as u where u.service_id = buss.id AND u.status = 'Pending' AND u.user_id = '".$user_id."' and u.service_type = '1') as added_incart, buss.id, buss.pass_name, buss.pass_type, buss.pass_type_subcat, buss.purchase_date, buss.pass_validity, buss.amount, buss.pass_id, buss.tax1, buss.tax2, buss.tax1_rate, buss.tax2_rate from business_passes_associates AS pass JOIN business_passes as buss on (buss.id = pass.pass_id) JOIN manage_pass_type as mp on (mp.id = buss.pass_type_subcat) WHERE pass.business_id = ".$business_id." AND pass.class_id = ".$class_id." AND buss.status = 'Active' AND buss.is_client_visible = 'Yes' GROUP by pass.pass_id";
								}else{
									$queryPasses = "SELECT mp.pass_days, (SELECT id FROM user_booking as u where u.service_id = buss.id AND u.status = 'Pending' AND u.user_id = '".$user_id."' and u.service_type = '1') as added_incart, buss.id, buss.pass_name, buss.pass_type, buss.pass_type_subcat, buss.purchase_date, buss.pass_validity, buss.amount, buss.pass_id, buss.tax1, buss.tax2, buss.tax1_rate, buss.tax2_rate from business_passes_associates AS pass JOIN business_passes as buss on (buss.id = pass.pass_id) JOIN manage_pass_type as mp on (mp.id = buss.pass_type_subcat) WHERE pass.business_id = ".$business_id." AND buss.status = 'Active' GROUP by pass.pass_id";
								} //  AND buss.is_client_visible = 'Yes'


								// AND CURRENT_DATE BETWEEN DATE_FORMAT(FROM_UNIXTIME(buss.purchase_date), '%Y-%m-%d') AND DATE_ADD(DATE_FORMAT(FROM_UNIXTIME(buss.purchase_date), '%Y-%m-%d'), INTERVAL buss.pass_validity day)

								//echo $queryPasses; die;

								if (!empty($passes_ids)) {
									// $queryPasses .= ' AND buss.id NOT IN ('.implode(',', $passes_ids).')';
								}

								$avaliable_pass = $this->dynamic_model->getQueryResultArray($queryPasses);
								if ($this->input->post('demo')) {
									echo json_encode($this->db->last_query()); exit;
								}
								$avl_pass = array();
								if (!empty($avaliable_pass)) {
									foreach($avaliable_pass as $avpass) {
										$pass_avaliable['id'] = $avpass['id'];
										$pass_avaliable['pass_name'] = $avpass['pass_name'];
										$pass_avaliable['pass_days'] = $avpass['pass_days'];
										$pass_avaliable['pass_type'] = get_passes_type_name($avpass['pass_type']);
										$pass_avaliable['pass_type_subcat'] = get_passes_type_name($avpass['pass_type'], $avpass['pass_type_subcat']);
										$pass_avaliable['pass_validity'] = $avpass['pass_validity'];
										$pass_avaliable['amount'] = $avpass['amount'];
										$pass_avaliable['pass_id'] = $avpass['pass_id'];
										$pass_avaliable['tax1'] = $avpass['tax1'];
										$pass_avaliable['tax2'] = $avpass['tax2'];
										$pass_avaliable['tax1_rate'] = ($avpass['tax1'] == 'yes') ? $avpass['tax1_rate'] : '0';
										$pass_avaliable['tax2_rate'] = ($avpass['tax2'] == 'yes') ? $avpass['tax2_rate'] : '0';
										$pass_avaliable['purchase_start_date'] = $avpass['purchase_date'];
										$pass_avaliable['purchase_end_date'] = (string)(($avpass['pass_validity'] * 24 * 60 * 60) + $avpass['purchase_date']);
										$pass_avaliable['studio_logo'] = base_url().'uploads/business/'.$business_info['logo'];
										$pass_avaliable['added_incart'] = $avpass['added_incart'] ? 1 : 0;
										$avl_pass[] = $pass_avaliable;
									}

								}

								$clientdata['avaliable_passes_data']    = $avl_pass;

								$response[] = $clientdata;
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
		}

		echo json_encode($arg);
	}

	public function add_cart() {
		$arg   = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
		   	$userdata = web_checkuserid();// checkuserid();
		   	if($userdata['status'] != 1){
				$arg = $userdata;
			}
			else
			{
				$response=array();
				$time=time();

				$usid 		= decode($userdata['data']['id']);
				$business_id= decode($userdata['data']['business_id']);

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
								$arg['data']      =  json_decode('{}');
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
							if($pass_amount!== $amount){
					        	$arg['status']     = 0;
					            $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line']= __line__;
								$arg['data']      =  json_decode('{}');
								$arg['message']    = $this->lang->line('amount_incorrect');
								echo json_encode($arg);exit;
							}

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
								$arg['data']      =  json_decode('{}');
								$arg['message']    = $this->lang->line('product_quantity_limit');
								echo json_encode($arg);exit;
							}

							//check amount
							if($product_amount!== $amount){
								$arg['status']     = 0;
								$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line']= __line__;
								$arg['data']      =  json_decode('{}');
								$arg['message']    = $this->lang->line('amount_incorrect');
								echo json_encode($arg);exit;
						   	}
						} else {
							$arg['status']     = 0;
							$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['data']      =  json_decode('{}');
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
							$arg['data']       = json_decode('{}');
							$arg['message']    = $this->lang->line('server_problem');
				        }
					}
				}
			}
		}

		echo json_encode($arg);
	}

	public function get_cart_list_info($usid='',$limit='',$offset='', $business_id = '')
    {
        $response=array();
		$item_name=$service_id=$class_name=$booking_pass_id=$pass_type=$purchase_date=$pass_end_date=$purchase_date_utc=$pass_end_date_utc=$desc=$product_image=$favourite='';
		$discount = 0;
		$total_discount = 0;
		$total_amount = 0;
        $total_tax = 0;
		$total_tax1 = 0;
		$total_tax2 = 0;
        $business_data = $this->studio_model->get_cart_business($usid,$limit,$offset, $business_id);
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
					$discount = 0;
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

                        $pass_validity= (!empty($business_pass)) ? "Valid for ".$business_pass[0]['pass_validity'] : "";
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
                    $cartData['amount']  = floatVal($value1['amount']);
                    $cartData['sub_total']  = floatVal($value1['sub_total']);
                    $cartData['quantity']  = floatVal($value1['quantity']);
					/* if($value1['service_type']==1){
						$tax_cal = $value1['tax_amount'];
					} else {
						$tax_cal = ($value1['tax_amount'] / 100) * $value1['sub_total'];
					} */

					if ($value1['tax1_rate'] == 0) {
						$cartData['tax1_rate'] = 0;
					} else {
						$cartData['tax1_rate'] = ($value1['tax1_rate'] / 100) * $value1['amount'];
						$cartData['tax1_rate'] = $cartData['tax1_rate'] * $cartData['quantity'];
					}

					if ($value1['tax2_rate'] == 0) {
						$cartData['tax2_rate'] = 0;
					} else {
						$cartData['tax2_rate'] = ($value1['tax2_rate'] / 100) * $value1['amount'];
						$cartData['tax2_rate'] = $cartData['tax2_rate'] * $cartData['quantity'];
					}
					// $cartData['tax1_rate'] = ($value1['tax1_rate'] == 0) ? 0 : (($value1['tax1_rate'] / 100) * $value1['amount']));
					// $cartData['tax2_rate'] = ($value1['tax2_rate'] == 0) ? 0 : (($value1['tax2_rate'] / 100) * $value1['amount']));

					$tax = ($value1['tax_amount'] / 100) * $value1['amount'];
					$tax_cal = $tax * $value1['quantity'];

					$cartData['tax']  = floatVal($tax_cal);

					$total_amount += $value1['sub_total'];
          			$total_tax += floatVal($tax_cal);
					$total_tax1 += floatVal($cartData['tax1_rate']);
					$total_tax2 += floatVal($cartData['tax2_rate']);
					$cartData['discount'] = number_format($value1['discount'], 2);
					$cart_response[$i++]      = $cartData;
					if ($value1['discount'] > 0) {
						$total_discount += $value1['discount'];
					}
                }

                $businessData['cart_details']  = $cart_response;
                $response[]   = $businessData;
            }
            $whe=array("user_id"=>$usid,"business_id"=>$business_id,"status"=>"Pending");
            $total_item=getdatacount('user_booking',$whe);
            $tax= gettotalTax($usid,$business_id);
            $total_amt  = check_cart_value($usid,$business_id);

			$grand_total = ($total_amount - $total_discount ) + $total_tax;
			return  $result	=	array(
				"business_details"	=>	$response,
				"total_item"		=> $total_item,
				"total_amount"		=> number_format($total_amount,2),
				"total_discount"	=> number_format($total_discount, 2), // floatVal(round($total_discount,2)),
				"tax"				=> number_format($total_tax, 2),
				"tax1_rate" 		=> number_format($total_tax1, 2),
				"tax2_rate" 		=> number_format($total_tax2, 2),
				"grand_total"		=> number_format($grand_total,2)
			);

        }else{
           return false;
        }
	}

	public function cart_list()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = web_checkuserid();
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
					$business_id= decode($userdata['data']['business_id']);

					$business_data = $this->get_cart_list_info($usid,$limit,$offset,$business_id);

					if(!empty($business_data)){

						$response = array();
						$response ['total_item'] =  $business_data['total_item'];
						$response ['total_amount'] =  $business_data['total_amount'];
						$response ['tax'] =  $business_data['tax'];
						$response ['tax1_rate'] =  $business_data['tax1_rate'];
						$response ['tax2_rate'] =  $business_data['tax2_rate'];
						$response ['total_discount'] =  $business_data['total_discount'];
						$response ['grand_total'] =  $business_data['grand_total'];
						$response ['business_details'] =  $business_data['business_details'][0];

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

	public function update_cart()
	{
		$arg   = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
		   $userdata = web_checkuserid();
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
						$business_id   = decode($this->input->post('business_id'));
						$discount      = $this->input->post('discount');
                        if(empty($product_id)){
                            $where2 = array('id'=>$product_id);
                            $product_data = $this->dynamic_model->getdatafromtable('business_product',$where2);
                            $amount=(!empty($product_data[0]['price'])) ? $product_data[0]['price'] : '';

                            $total_amt  = $quantity*$amount;
                            $tax = '0';
                            $grand_total=$total_amt+$tax;
                            $result=array("business_details"=>[],"total_item"=>"$quantity","total_amount"=>"$total_amt","tax"=>"$tax","grand_total"=>"$grand_total");
                            $arg['status']    = 1;
                            $arg['error_code'] = REST_Controller::HTTP_OK;
                            $arg['error_line']= __line__;
                            $arg['data']       = $result;
                            $arg['message']   = $this->lang->line('cart_update_succ');
                        }else{
							$product_quantity= get_product_quantity($business_id,$product_id);
							if($product_quantity < $quantity){
								$arg['status']     = 0;
								$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line']= __line__;
								$arg['data']      =  json_decode('{}');
								$arg['message']    = $this->lang->line('product_quantity_limit');
								echo json_encode($arg);exit;
							}

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
								}

                        		$sub_total=$check_cart[0]['amount']*$total_quantity;
                            	$updateData =   array(
									'quantity' =>	$total_quantity,
									'discount' => 	$discount,
									'sub_total' =>	$sub_total,
									'update_dt'=>	$time
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

	public function pay_at_desk() {
		$arg    = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = web_checkuserid();
			if($userdata['status'] != 1){
				$arg = $userdata;
			}
			else
			{
				$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST) {
					$this->form_validation->set_rules('client_id', 'Client Id', 'required|numeric');
					$this->form_validation->set_rules('reference_id', 'Reference Id', 'required');
					$this->form_validation->set_rules('payment_mode', 'Payment Mode', 'required');
					$this->form_validation->set_rules('payment_note', 'Payment Note', 'required');
					$this->form_validation->set_rules('data[]', 'Item Information', 'required');

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

						$business_id= decode($userdata['data']['business_id']);
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

							if(!empty($cart_data)) {

								$this->db->insert_batch('user_payment_requests',$insert_data);
								$transaction_id = $this->dynamic_model->insertdata('transactions',$transaction_data);

								foreach($cart_data as $value) {

									$service_type = $value['service_type'];
									$service_id = $value['service_id'];

									if($value['service_type']=='1') {
										$where1 = array('id'=>$value['service_id'],'service_type'=>'1','status' => 'Active');
										$business_pass= $this->dynamic_model->getdatafromtable('business_passes',$where1);
										$pass_start_date    =   $time;
										$validity = (!empty($business_pass[0]['pass_validity'])) ? $business_pass[0]['pass_validity'] : 0;
										$getEndDate = ($validity * 24 * 60 * 60) + $time;
										$pass_end_date= ($validity == 0) ? $pass_start_date : $getEndDate;
										$pass_status = 1;
										$card_id = $value['id'];
										$where2 = array("user_id"=>$client_id, 'id'=>$card_id, "status"=>"Pending","service_type"=>'1');

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
					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function pay_at_desk_28082020(){

        $arg    = array();
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
        }
        else
        {
           $userdata = web_checkuserid();
           if($userdata['status'] != 1){
             $arg = $userdata;
            }
            else
            {
				$_POST = json_decode(file_get_contents("php://input"), true);

				if($_POST)
			  	{

					$this->form_validation->set_rules('client_id', 'Client Id', 'required|numeric');
					$this->form_validation->set_rules('reference_id', 'Reference Id', 'required');
					$this->form_validation->set_rules('payment_mode', 'Payment Mode', 'required');
					$this->form_validation->set_rules('payment_note', 'Payment Note', 'required');
					$this->form_validation->set_rules('data[]', 'Item Information', 'required');

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

							$payment_mode = $this->input->post('payment_mode');
							$payment_note = $this->input->post('payment_note');
							$reference_id = $this->input->post('reference_id');

							$insert_transaction = array();
							$business_id= decode($userdata['data']['business_id']);

							for($i = 0; $i < count($data); $i++) {
								$row = $data[$i];
								if (array_key_exists('service_id', $row) && array_key_exists('service_type', $row) && array_key_exists('business_id', $row) && array_key_exists('quantity', $row) && array_key_exists('amount', $row) && array_key_exists('tax', $row) && array_key_exists('discount', $row)) {

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
										'reference_payment_id' => $reference_id, // getuniquenumber(),
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

	public function remove_cart()
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
		   $userdata = web_checkuserid();
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
					$business_id= decode($userdata['data']['business_id']);

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

	public function payment_checkout() {
		$arg    = array();
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
        } else {
        	$_POST = json_decode(file_get_contents("php://input"), true);
			if ($_POST) {

				$this->form_validation->set_rules('client_id', 'Client Id', 'required|numeric');
				$this->form_validation->set_rules('data[]', 'Item Information', 'required');

				if($this->form_validation->run() == FALSE)
				{
					$arg['status']  = 0;
					$arg['error_code'] = 0;
					$arg['error_line']= __line__;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {

					$business_id = 0;

					$usid =	$this->input->post('client_id');
					$client_id = $this->input->post('client_id');
					$time = time();
					$savecard= $this->input->post('savecard');
					$card_id = $this->input->post('card_id');
					$data = $this->input->post('data');
					$loop_status = true;

					$amount = 0;
					if (count($data) > 0) {
						for($i = 0; $i < count($data); $i++) {
							$row = $data[$i];
							if (array_key_exists('service_id', $row) && array_key_exists('service_type', $row) && array_key_exists('business_id', $row) && array_key_exists('quantity', $row) && array_key_exists('amount', $row) && array_key_exists('tax', $row) && array_key_exists('discount', $row)) {

								$total_amount = ($row['amount'] + $row['tax']) - $row['discount'];
								$amount = $amount + $total_amount;
								$business_id = $row['business_id'];

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

							$amount = number_format((float)$amount, 2, '.', '');
							$token  = $this->input->post('token');

							if (empty($token) && empty($card_id)) {

								$arg['status']  = 0;
								$arg['error_code'] = 0;
								$arg['error_line']= __line__;
								$arg['message'] = 'Failed payment request';
								echo json_encode($arg);exit;
							}

							if(!empty($token)) {

								$payment_data = array(
									'order_number' 		=>	$time,
									'amount' 			=>	$amount,
									'payment_method' 	=> 'token',
									'token' 			=> array(
										'name'	=>	'Test Card',
										'code'	=> 	$token,
										'complete' =>	true
									)
								);

							} else if(!empty($card_id)) {

								$where 			=	array('user_id' => $usid);
								$result_card 	=	$this->dynamic_model->getdatafromtable('user_card_save', $where);
								$customer_code 	=	$result_card[0]['card_id'];

								$payment_data = array(
									'order_number' 		=>	$time,
									'amount'			=>	$amount,
									'payment_method' 	=>	'payment_profile',
									'payment_profile'	=> 	array(
										'customer_code' =>$customer_code,
										'card_id' => $card_id,
										'complete' =>true
									)
								);
							}
						}

						$where = array('user_id' => $usid);
						$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
						// echo json_encode($result_card);exit;
						if(empty($result_card) && ($savecard == '1')){
							$legato_token_data = array(
									'language' => 'en',
									'comments' => SITE_NAME,
									'token' => array('name' => 'Test Card',
									'code' => $token)
								);
							$apiurl='https://api.na.bambora.com/v1/profiles';
							$responce = $this->bomborapay->profile_create('POST',$apiurl, $legato_token_data);
							if($responce['code'] == '1'){
								$transaction_data = array(
									'user_id'=>$usid,
									'card_id'=>$responce['customer_code']
								);
								$this->dynamic_model->insertdata('user_card_save',	$transaction_data);
								$customer_code = $responce['customer_code'];
							}
						} elseif(!empty($result_card) && ($savecard == '1')){
							$customer_code = $result_card[0]['card_id'];
							$apiurl="https://api.na.bambora.com/v1/profiles/$customer_code/cards";
							$legato_token_data = array(
									'token' => array('name' => 'Test Card',
									'code' => $token)
								);
							$responce = $this->bomborapay->profile_create('POST',$apiurl, $legato_token_data);
							if($responce['code'] == '1'){
								$customer_code = $responce['customer_code'];
							}
						}

						if($savecard == '1'){
							$payment_data = array(
								'order_number' => $time,
								'amount' => $amount,
								'payment_method' => 'payment_profile',
								'payment_profile' => array(
									'customer_code' =>$customer_code,
									'card_id' => $card_id,
									'complete' =>true
								)
							);
						}

						$payUrl='https://api.na.bambora.com/v1/payments ';

						//$mid = '377010002';
						$business_id = getBusinessId($usid);
                        $mid = getUserMarchantId($business_id);
                        $marchant_id = $mid['marchant_id'];
                        $marchant_id_type = $mid['marchant_id_type'];

						$res = $this->bomborapay->payment_checkout('POST',$payUrl,$payment_data,$marchant_id,$marchant_id_type);

						if(@$res['approved']=='1') {

							$ref_num  = getuniquenumber();
							$payment_id =	!empty(@$res['id']) ? $res['id'] : $ref_num;

							$authorizing_merchant_id =!empty(@$res['authorizing_merchant_id']) ? $res['authorizing_merchant_id'] : '';
                        	$payment_type =!empty(@$res['type']) ? $res['type'] : '';
                        	$payment_method =!empty(@$res['payment_method']) ? $res['payment_method'] : '';
                        	$amount =!empty(@$res['amount']) ? $res['amount'] : '';
							$transaction_data = array(
								'user_id'           =>	$usid,
								'amount'            =>	$amount,
								'trx_id'           =>	$payment_id,
								'order_number'     =>	$time,
								'transaction_type' =>	2,
								'payment_status'   =>	"Success",
								'saved_card_id'    =>	0,
								'create_dt'        =>	$time,
								'update_dt'        =>	$time,
								'authorizing_merchant_id' => $authorizing_merchant_id,
                                'payment_type' => $payment_type,
                                'payment_method' => $payment_method,
                                'responce_all'=>json_encode($res),
							);

							$transaction_id=$this->dynamic_model->insertdata('transactions',$transaction_data);

							$where=array("user_id"=>$usid, "business_id" => $business_id, "status"=>"Pending");
							$cart_data = $this->dynamic_model->getdatafromtable('user_booking',$where);

							if(!empty($cart_data)) {

								foreach($cart_data as $value) {

									$service_type = $value['service_type'];
									$service_id = $value['service_id'];

									if($value['service_type']=='1') {
										$where1 = array('id'=>$value['service_id'],'service_type'=>'1','status' => 'Active');
										$business_pass= $this->dynamic_model->getdatafromtable('business_passes',$where1);
										$pass_start_date    =   $time;


			$pass_validity = (!empty($business_pass[0]['pass_validity'])) ? $business_pass[0]['pass_validity'] : 0;

			if(!empty($business_pass)){
               $pass_type_subcat=$business_pass[0]['pass_type_subcat'];
                if(!empty($pass_type_subcat)){
                $where2 = array('id'=>$pass_type_subcat);
                $manage_pass= $this->dynamic_model->getdatafromtable('manage_pass_type',$where2);
                    if(!empty($manage_pass)){
                        $validity=$manage_pass[0]['pass_days'];
                    }
                }
            }

										$getEndDate = ($pass_validity * 24 * 60 * 60) + $time;
										$pass_end_date= ($pass_validity == 0) ? $pass_start_date : $getEndDate;
										$pass_status = 1;
										$where2 = array("user_id"=>$usid,"status"=>"Pending","service_type"=>'1');
										$booking_data =   array(
											'transaction_id'        => $transaction_id,
											'status'                => "Success",
											'passes_start_date'     => $pass_start_date,
											'passes_end_date'       => $pass_end_date,
											'passes_status'         => $pass_status,
											'passes_total_count'    =>  $validity,
											'passes_remaining_count'  =>  $validity,
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
										$where2 = array("user_id"=>$usid,"status"=>"Pending","service_type!="=>'1');
										$booking_data =   array(
											'transaction_id'  => $transaction_id,
											'status'          => "Success",
											'update_dt'       => $time
										);
										$booking_id = $this->dynamic_model->updateRowWhere('user_booking',$where2,$booking_data);
									}
								}
							}

							$response  = array('amount' =>number_format((float)$amount, 2, '.', ''),'transaction_date'=>date('d M Y'));
							if($transaction_id) {
								$arg['status']    = 1;
								$arg['error_code'] = HTTP_OK;
								$arg['error_line']= __line__;
								$arg['message']   =$this->lang->line('payment_succ');
								$arg['data']      = $response;
							} else {
								$arg['status']    = 0;
								$arg['error_code'] = HTTP_NOT_FOUND;
								$arg['error_line']= __line__;
								$arg['message']   = $this->lang->line('payment_fail');
							}

						} else {
							$arg['status']    = 0;
							$arg['error_code'] = HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['message']   = $this->lang->line('payment_fail');
						}

					} else {
						$arg['status']  = 0;
						$arg['error_code'] = 0;
						$arg['error_line']= __line__;
						$arg['message'] = 'Invalid payment request';
					}
				}
			} else {
				$arg['status']  = 0;
				$arg['error_code'] =  ERROR_FAILED_CODE;
				$arg['error_line']= __line__;
				$arg['message'] = 'Invalid request';
				$arg['data']      =json_decode('{}');
			}
		}
		echo json_encode($arg);
	}

	public function clover_pay_checkout() {
		$arg    = array();
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
        } else {
        	$_POST = json_decode(file_get_contents("php://input"), true);
			if ($_POST) {

				$this->form_validation->set_rules('client_id', 'Client Id', 'required|numeric');
				$this->form_validation->set_rules('data[]', 'Item Information', 'required');

				if($this->form_validation->run() == FALSE)
				{
					$arg['status']  = 0;
					$arg['error_code'] = 0;
					$arg['error_line']= __line__;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {

					$business_id = 0;

					$usid =	$this->input->post('client_id');
					$client_id = $this->input->post('client_id');
					$time = time();
					//$savecard= $this->input->post('savecard');
					//$card_id = $this->input->post('card_id');
					$data = $this->input->post('data');
					$loop_status = true;

					$amount = 0;
					if (count($data) > 0) {
						for($i = 0; $i < count($data); $i++) {
							$row = $data[$i];
							if (array_key_exists('service_id', $row) && array_key_exists('service_type', $row) && array_key_exists('business_id', $row) && array_key_exists('quantity', $row) && array_key_exists('amount', $row) && array_key_exists('tax', $row) && array_key_exists('discount', $row)) {

								$total_amount = ($row['amount'] + $row['tax']) - $row['discount'];
								$amount = $amount + $total_amount;
								$business_id = $row['business_id'];

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

							$amount = number_format((float)$amount, 2, '.', '');
							$token  = $this->input->post('token');

							//if (empty($token) && empty($card_id)) {
							if (empty($token)) {
								$arg['status']  = 0;
								$arg['error_code'] = 0;
								$arg['error_line']= __line__;
								$arg['message'] = 'Failed payment request';
								echo json_encode($arg);exit;
							}

							/*if(!empty($token)) {

								$payment_data = array(
									'order_number' 		=>	$time,
									'amount' 			=>	$amount,
									'payment_method' 	=> 'token',
									'token' 			=> array(
										'name'	=>	'Test Card',
										'code'	=> 	$token,
										'complete' =>	true
									)
								);

							} else if(!empty($card_id)) {

								$where 			=	array('user_id' => $usid);
								$result_card 	=	$this->dynamic_model->getdatafromtable('user_card_save', $where);
								$customer_code 	=	$result_card[0]['card_id'];

								$payment_data = array(
									'order_number' 		=>	$time,
									'amount'			=>	$amount,
									'payment_method' 	=>	'payment_profile',
									'payment_profile'	=> 	array(
										'customer_code' =>$customer_code,
										'card_id' => $card_id,
										'complete' =>true
									)
								);
							}*/
						}

						/*$where = array('user_id' => $usid);
						$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
						// echo json_encode($result_card);exit;
						if(empty($result_card) && ($savecard == '1')){
							$legato_token_data = array(
									'language' => 'en',
									'comments' => SITE_NAME,
									'token' => array('name' => 'Test Card',
									'code' => $token)
								);
							$apiurl='https://api.na.bambora.com/v1/profiles';
							$responce = $this->bomborapay->profile_create('POST',$apiurl, $legato_token_data);
							if($responce['code'] == '1'){
								$transaction_data = array(
									'user_id'=>$usid,
									'card_id'=>$responce['customer_code']
								);
								$this->dynamic_model->insertdata('user_card_save',	$transaction_data);
								$customer_code = $responce['customer_code'];
							}
						} elseif(!empty($result_card) && ($savecard == '1')){
							$customer_code = $result_card[0]['card_id'];
							$apiurl="https://api.na.bambora.com/v1/profiles/$customer_code/cards";
							$legato_token_data = array(
									'token' => array('name' => 'Test Card',
									'code' => $token)
								);
							$responce = $this->bomborapay->profile_create('POST',$apiurl, $legato_token_data);
							if($responce['code'] == '1'){
								$customer_code = $responce['customer_code'];
							}
						}

						if($savecard == '1'){
							$payment_data = array(
								'order_number' => $time,
								'amount' => $amount,
								'payment_method' => 'payment_profile',
								'payment_profile' => array(
									'customer_code' =>$customer_code,
									'card_id' => $card_id,
									'complete' =>true
								)
							);
						}

						$payUrl='https://api.na.bambora.com/v1/payments ';

						//$mid = '377010002';
						$business_id = getBusinessId($usid);
                        $mid = getUserMarchantId($business_id);
                        $marchant_id = $mid['marchant_id'];
                        $marchant_id_type = $mid['marchant_id_type'];

						$res = $this->bomborapay->payment_checkout('POST',$payUrl,$payment_data,$marchant_id,$marchant_id_type);

						if(@$res['approved']=='1') {

							$ref_num  = getuniquenumber();
							$payment_id =	!empty(@$res['id']) ? $res['id'] : $ref_num;

							$authorizing_merchant_id =!empty(@$res['authorizing_merchant_id']) ? $res['authorizing_merchant_id'] : '';
                        	$payment_type =!empty(@$res['type']) ? $res['type'] : '';
                        	$payment_method =!empty(@$res['payment_method']) ? $res['payment_method'] : '';
                        	$amount =!empty(@$res['amount']) ? $res['amount'] : '';*/


                        	//$business_id = $this->input->post('business_id');
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
							$amount       = $amount;
							$taxAmount    = 0;
							// $res  = clover_api_payment_checkout($user_cc_no,$user_cc_mo,$user_cc_yr,$user_cc_cvv,$user_zip,$amount,$taxAmount,$marchant_id,$clover_key,$access_token,$currency,$token);

							//{ "id" : "R48Q7GPMY2FQY", "amount" : 1800, "amount_refunded" : 0, "currency" : "usd", "created" : 1616661109846, "captured" : true, "ref_num" : "108400500020", "auth_code" : "OK2809", "outcome" : { "network_status" : "approved_by_network", "type" : "authorized" }, "paid" : true, "status" : "succeeded", "source" : { "id" : "clv_1TSTS1iWemARMj4AXHxHikLV", "brand" : "AMEX", "cvc_check" : "unchecked", "exp_month" : "11", "exp_year" : "2025", "first6" : "378282", "last4" : "0005" } }

							//{"message":"400 Bad Request","error":{"code":"token_already_used","message":"You cannot use a clover token more than once unless it is marked as multipay."}}


							//{"paymentId":"30PFKD66YXNCP","result":"APPROVED","authCode":"OK7823","token":"DGY73XR6DDDF0","vaultedCard":{"first6":"411111","last4":"1111","expirationDate":"0321","token":"1894469479681111"}}

						//echo $res['message'];die;
						// if(@$res->status == 'succeeded')
						if(true)
						{
								$where = array('user_id' => $usid,
									'business_id' => $business_id,
								);
								$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);

								$ref_num    = getuniquenumber();
								$payment_id = true; // !empty($res->id) ? $res->id : $ref_num;
								$authorizing_merchant_id = true; // $res->source->id;
								$payment_type   = 'Card';
								$payment_method = 'Online';
								$amount         = $amount;

								$transaction_data = array(
									'user_id'           =>	$usid,
									'amount'            =>	$amount,
									'trx_id'           =>	$payment_id,
									'order_number'     =>	$time,
									'transaction_type' =>	2,
									'payment_status'   =>	"Success",
									'saved_card_id'    =>	0,
									'create_dt'        =>	$time,
									'update_dt'        =>	$time,
									'authorizing_merchant_id' => $authorizing_merchant_id,
	                                'payment_type' => $payment_type,
	                                'payment_method' => $payment_method,
	                                'responce_all'=> '' //json_encode($res),
								);

								$transaction_id=$this->dynamic_model->insertdata('transactions',$transaction_data);

								$where=array("user_id"=>$usid, "business_id" => $business_id, "status"=>"Pending");
								$cart_data = $this->dynamic_model->getdatafromtable('user_booking',$where);

								if(!empty($cart_data)) {

									foreach($cart_data as $value) {

										$service_type = $value['service_type'];
										$service_id = $value['service_id'];

										if($value['service_type']=='1') {
											$where1 = array('id'=>$value['service_id'],'service_type'=>'1','status' => 'Active');
											$business_pass= $this->dynamic_model->getdatafromtable('business_passes',$where1);
											$pass_start_date    =   $time;


										$pass_validity = (!empty($business_pass[0]['pass_validity'])) ? $business_pass[0]['pass_validity'] : 0;

										if(!empty($business_pass)){
							               $pass_type_subcat=$business_pass[0]['pass_type_subcat'];
							                if(!empty($pass_type_subcat)){
							                $where2 = array('id'=>$pass_type_subcat);
							                $manage_pass= $this->dynamic_model->getdatafromtable('manage_pass_type',$where2);
							                    if(!empty($manage_pass)){
							                        $validity=$manage_pass[0]['pass_days'];
							                    }
							                }
							            }

											$getEndDate = ($pass_validity * 24 * 60 * 60) + $time;
											$pass_end_date= ($pass_validity == 0) ? $pass_start_date : $getEndDate;
											$pass_status = 1;
											$where2 = array("user_id"=>$usid,"status"=>"Pending","service_type"=>'1');
											$booking_data =   array(
												'transaction_id'        => $transaction_id,
												'status'                => "Success",
												'passes_start_date'     => $pass_start_date,
												'passes_end_date'       => $pass_end_date,
												'passes_status'         => $pass_status,
												'passes_total_count'    =>  $validity,
												'passes_remaining_count'  =>  $validity,
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
											$where2 = array("user_id"=>$usid,"status"=>"Pending","service_type!="=>'1');
											$booking_data =   array(
												'transaction_id'  => $transaction_id,
												'status'          => "Success",
												'update_dt'       => $time
											);
											$booking_id = $this->dynamic_model->updateRowWhere('user_booking',$where2,$booking_data);
										}
									}
								}

								$response  = array('amount' =>number_format((float)$amount, 2, '.', ''),'transaction_date'=>date('d M Y'));
								if($transaction_id) {
									$arg['status']    = 1;
									$arg['error_code'] = HTTP_OK;
									$arg['error_line']= __line__;
									$arg['message']   =$this->lang->line('payment_succ');
									$arg['data']      = $response;
								} else {
									$arg['status']    = 0;
									$arg['error_code'] = HTTP_NOT_FOUND;
									$arg['error_line']= __line__;
									$arg['message']   = $this->lang->line('payment_fail');
								}

						} else {
							$arg['status']    = 0;
							$arg['error_code'] = HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['message']   = ''; //@$res->error->message;//$this->lang->line('payment_fail');
						}

					} else {
						$arg['status']  = 0;
						$arg['error_code'] = 0;
						$arg['error_line']= __line__;
						$arg['message'] = 'Invalid payment request';
					}
				}
			} else {
				$arg['status']  = 0;
				$arg['error_code'] =  ERROR_FAILED_CODE;
				$arg['error_line']= __line__;
				$arg['message'] = 'Invalid request';
				$arg['data']      =json_decode('{}');
			}
		}
		echo json_encode($arg);
	}

	public function search_customer() {
		$arg   = array();
        $arrayName = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
		   $userdata = web_checkuserid();
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
					} else {
						$usid   = decode($userdata['data']['id']);
						$response=array();
						$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no= $page_no-1;
						$limit    = config_item('page_data_limit');
						$offset = $limit * $page_no;
						$search_val=  $this->input->post('search_val');
						$business_id= decode($this->input->post('business_id'));
						$class_id=decode($this->input->post('class_id'));

						if($search_val){
							$where= ' mobile_verified = 1 AND email_verified = 1 AND id != '.$usid.' AND (user.name LIKE "%'.$search_val.'%" OR user.lastname LIKE "%'.$search_val.'%" OR user.mobile LIKE "%'.$search_val.'%" OR user.email LIKE "%'.$search_val.'%")';
						}

						// $client_data = $this->dynamic_model->getdatafromtable('user',$where);
						$client_data = $this->dynamic_model->getdatafromtable('user',$where, '*', $limit, $offset);
						if($client_data){
							foreach($client_data as $value){
								if (!empty($value['name']) && !empty($value['lastname']) && !empty($value['mobile'])) {
									$user_id = $value['id'];
									$where= ' business_id = "'.$business_id.'" AND class_id = "'.$class_id.'" AND service_type = "1" AND passes_status = "1" AND user_id = "'.$user_id.'"';
									$pass_data = $this->dynamic_model->getdatafromtable('user_booking',$where);
									$pass_id = "";
									// echo json_encode($this->db->last_query()); exit;
									if(!empty($pass_data)){
										$pass_id = $pass_data[0]['service_id'];
									}

									$clientdata['pass_id']     = $pass_id;
									$usid = $value['id'];
									$clientdata['id']     = encode($value['id']);
									$clientdata['client_id']     = $value['id'];
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
									if(!empty($passes_data)){
										foreach($passes_data as $values)
										{
											$pass_id_array[] = $values['id'];
										}
									}

									if (!empty($pass_id_array)) {
										$pass_id_array = implode(",",$pass_id_array);
									 	$sql = "SELECT * FROM user_booking WHERE status = 'Success' AND passes_remaining_count != 0 AND  user_id = '".$value['id']."' AND passes_status = '1' AND service_id IN ($pass_id_array)";
										$my_passes_data = $this->dynamic_model->getQueryResultArray($sql);
										$pass_arr = array();
										if(!empty($my_passes_data)){
											foreach($my_passes_data as $valuess)
											{
												$passesdata=getpassesdetails($valuess['service_id'],$usid);
												$business_ids = $valuess['business_id'];
												$business_info = $this->dynamic_model->getQueryRowArray('SELECT *  FROM business WHERE id = '.$business_ids);
												$passesdata['business_logo'] =  empty($business_info['business_image']) ? '' : site_url().'uploads/business/'.$business_info['business_image'];

												$passesdata['start_date'] = date('d M Y ',$valuess['passes_start_date']);
	                                    		$passesdata['end_date'] = date('d M Y ',$valuess['passes_end_date']);

	                                    		$passesdata['start_date_utc'] = $valuess['passes_start_date'];
	                                    		$passesdata['end_date_utc'] = $valuess['passes_end_date'];
	                                    		$passesdata['status'] = $valuess['status'];

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
		}
		echo json_encode($arg);
	}

	public function new_client_signup() {
		$arg   = array();
        $arrayName = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
		    $userdata = web_checkuserid();
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
					// $this->form_validation->set_rules('instractor_id', 'Instractor id', 'required');
					$this->form_validation->set_rules('pass_id', 'Pass id', 'required');


					if($this->form_validation->run() == FALSE)
					{
						$arg['status']  = 0;
						$arg['error_code'] = 0;
						$arg['error_line']= __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$usid   = decode($userdata['data']['id']);
						$response=array();

						$user_id=  decode($this->input->post('user_id'));
						$business_id= decode($this->input->post('business_id'));
						$class_id= decode($this->input->post('class_id'));
						$schedule_id=$this->input->post('schedule_id');
						// $instractor_id=$this->input->post('instractor_id');
						$pass_id=$this->input->post('pass_id');
						$service_type = 1;
						$time = time();
						$status = 'singup';
						$scheduled_date = date('Y-m-d');

						$where= ' id = "'.$schedule_id.'"';
						$class_data = $this->dynamic_model->getdatafromtable('class_scheduling_time',$where);
						if(!empty($class_data)){
							$scheduled_date = $class_data[0]['scheduled_date'];
						}

						$where= ' user_id = "'.$user_id.'" AND business_id = "'.$business_id.'" AND service_id = "'.$pass_id.'" AND status = "Success" AND passes_status = "1" AND passes_remaining_count != "0" ';
						$pass_data = $this->dynamic_model->getdatafromtable('user_booking',$where);

						if (empty($pass_data)) {
							$arg['status']     = 0;
                            $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
                            $arg['error_line']= __line__;
                            $arg['data']       = json_decode('{}');
                            $arg['message']    = 'Please purchase pass then you can singup';
                            echo json_encode($arg);exit;
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

						if($pass_data){

							/* Check days login */
							$where_days = ' id = "'.$schedule_id.'"';
							$class_data_days = $this->dynamic_model->getdatafromtable('class_scheduling_time',$where_days);
							if(!empty($class_data_days)){
							    $scheduled_date_days = $class_data[0]['scheduled_date'];
							    $class_id_days = $class_data[0]['class_id'];
							    $getClassDataDays = $this->dynamic_model->getQueryRowArray('SELECT * FROM business_class where id = '. $class_id_days);
							    if(!empty($getClassDataDays)){
							      $class_days_prior_signup = $getClassDataDays['class_days_prior_signup'];
							      $start_date = strtotime($scheduled_date_days);
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
							/* Check days login */
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
													'checked_by'    =>$usid
												);



							$check_user_entry = $this->db->get_where('user_attendance', array(
								'user_id' => $user_id,
								'service_id' => $class_id,
								'schedule_id' => $schedule_id,
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
		}
		echo json_encode($arg);
	}

	public function get_save_cards()
    {

    	$arg = array();
    	//check version is updated or not
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = web_checkuserid();
		    if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
				$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
		  		{
					$this->form_validation->set_rules('client_id', 'Client Id', 'required|numeric');
					if($this->form_validation->run() == FALSE)
					{

						$arg['status']  = 0;
						$arg['error_code'] = 0;
						$arg['error_line']= __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());

					} else {
						$client_id = $this->input->post('client_id');

						$card_detail = $this->dynamic_model->getdatafromtable('saved_card_details',array('user_id'=> $client_id,'is_deleted'=>0));
						//print_r($card_detail);die;
						if($card_detail)
						{
							$imagePath   = site_url()."assets/images/card.jpeg";
							$user_data   = array();
							$card_array  = array();
							foreach ($card_detail as $card)
							{

								$card_arr=json_decode(decode($card['card_details']));
								$card_bank_no=$card_arr->card_bank_no;
								$expiry_month=$card_arr->expiry_month;
								$expiry_year=$card_arr->expiry_year;
								// check year is valid
								if(check_expiry_month_year($expiry_month,$expiry_year) == true)
								{
								$user_data["card_id"]        = $card['id'];
								$user_data["userid"]         = $card['user_id'];
								$last_digit = substr($card_bank_no,-4);
								$user_data["card_number"]    = 'XXXX-XXXX-XXXX-'.$last_digit;
								$user_data["full_card_number"] =$card_bank_no;
								$user_data["expiry_month"]   = $expiry_month;
								$user_data["expiry_year"]    = $expiry_year;
								if($card['is_debit_card'] == 1)
									$user_data["card_type"] = "Debit Card";
								if($card['is_credit_card'] == 1)
									$user_data["card_type"] = "Credit Card";
								$card_array[]                = $user_data;
								}
							}
							if(!empty($card_array)){
								$arg['status']     = 1;
								$arg['error_code']  = REST_Controller::HTTP_OK;
								$arg['error_line']= __line__;
								$arg['data']       = $card_array;
								$arg['message']    = $this->lang->line('saved_card_details');
							}else{
								$arg['status']     = 0;
								$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line']= __line__;
								$arg['data']       = array();
								$arg['message']    = $this->lang->line('saved_card_not_found');
							}
						}
						else
						{
							$arg['status']     = 0;
							$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['data']       = array();
							$arg['message']    = $this->lang->line('saved_card_not_found');
						}
					}
				}


			}
		}
	    echo json_encode($arg);
	}

	public function get_cards()
    {

    	$arg = array();
    	//check version is updated or not
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = web_checkuserid();
		    if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{
				$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
		  		{
					$this->form_validation->set_rules('client_id', 'Client Id', 'required|numeric');
					if($this->form_validation->run() == FALSE)
					{

						$arg['status']  = 0;
						$arg['error_code'] = 0;
						$arg['error_line']= __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());

					} else {
						$client_id = $this->input->post('client_id');
						$where = array('user_id' => $client_id);

       					$result = $this->dynamic_model->getdatafromtable('user_card_save', $where);
						if(!empty($result)) {
							foreach ($result as $key => $value) {
								$card_id = $value['card_id'];
								$url="https://api.na.bambora.com/v1/profiles/$card_id/cards";
								$res=$this->bomborapay->profile_create('GET',$url);
								$data[$key] = $res;
							}
							$arg['status']    = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
							$arg['message']   = $this->lang->line('thank_msg1');
							$arg['data']      = $data;
						} else {
							$arg['status']  = 0;
							$arg['error_code'] = 0;
							$arg['error_line']= __line__;
							$arg['message'] = 'no card found';
						}

					}
				}


			}
		}
	    echo json_encode($arg);
	}

	public function update_business_status() {
		$arg = array();
    	//check version is updated or not
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = web_checkuserid();
		    if($userdata['status'] != 1){
				$arg = $userdata;
			}
			else
			{
				$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
		  		{
					$this->form_validation->set_rules('class_id', 'Class Id', 'required');
					$this->form_validation->set_rules('schedule_id', 'Schedule Id', 'required');
					if($this->form_validation->run() == FALSE)
					{

						$arg['status']  = 0;
						$arg['error_code'] = 0;
						$arg['error_line']= __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());

					} else {
						$usid   		= 	decode($userdata['data']['id']);
						$business_id 	=	decode($userdata['data']['business_id']);
						$class_id 		=	$this->input->post('class_id'); //decode($this->input->post('class_id'));
						$schedule_id 	=	$this->input->post('schedule_id'); //decode($this->input->post('schedule_id'));

						$where = array('business_id' => $business_id, 'id' => $schedule_id, 'class_id' => $class_id);
						$result = $this->dynamic_model->getdatafromtable('class_scheduling_time', $where);
						if (false) {
							$arg['status']  = 0;
							$arg['error_code'] = 0;
							$arg['error_line']= __line__;
							$arg['message']= 'Invalid Details';
						} else {

							$info = $result[0];
							$status = ($info['status'] == 'Active') ? array('status' => 'Deactive') : array('status' => 'Active');
							// $whereArray = array('id' => $class_id);
							$this->dynamic_model->updateRowWhere('class_scheduling_time', $where, $status);
							$arg['status']    = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
							$arg['message']   = 'Class status changed';
						}
					}
				}
			}
		}

		echo json_encode($arg);
	}

	public function update_business_profile() {
		$arg = array();
    	//check version is updated or not
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = web_checkuserid();
		    if($userdata['status'] != 1){
				$arg = $userdata;
			}
			else
			{

				//$this->form_validation->set_rules('category_info', 'Category info', 'required',array('required' => $this->lang->line('category_required')));
				$this->form_validation->set_rules('business_address', 'Business Address', 'required',array('required' => $this->lang->line('business_address_required')));
				$this->form_validation->set_rules('country','Country', 'required', array( 'required' => $this->lang->line('country_required')));
				$this->form_validation->set_rules('state','State', 'required', array( 'required' => $this->lang->line('state_required')));
				$this->form_validation->set_rules('city','City', 'required', array( 'required' => $this->lang->line('city_required')));
				$this->form_validation->set_rules('lat', 'Latitude','required',array('required' => $this->lang->line('lat')));
				$this->form_validation->set_rules('lang', 'Longitude','required',array( 'required' => $this->lang->line('lng')));
				$this->form_validation->set_rules('email', 'Email', 'required|valid_email' , array('required' => $this->lang->line('email_required'),'valid_email' => $this->lang->line('email_valid')));
				$this->form_validation->set_rules('mobile', 'Mobile', 'required|min_length[8]|max_length[20]|numeric', array(
					'required' => $this->lang->line('mobile_required'),
					'min_length' => $this->lang->line('mobile_min_length'),
					'max_length' => $this->lang->line('mobile_max_length'),
					'numeric' => $this->lang->line('mobile_numeric')
				));
				$this->form_validation->set_rules('website','Website','required',array('required' => $this->lang->line('website_required')));
				if ($this->form_validation->run() == FALSE)
				{
					$arg['status']  = 0;
					$arg['error_code'] = 0;
					$arg['error_line']= __line__;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$usid   		= 	decode($userdata['data']['id']);
					$business_id 	=	decode($userdata['data']['business_id']);
					$services='';
					$time=time();
					//Check Subscription plan purchase or not
					$where = array('id'=>$usid);
					$user_data = $this->dynamic_model->getdatafromtable('user',$where,'id,plan_id');
					if(!empty($user_data)){
						$plan_id=(!empty($user_data[0]['plan_id'])) ? $user_data[0]['plan_id'] : "";
						$plan_data = $this->studio_model->plan_check($plan_id,$usid);
						if(empty($plan_data)){
							$arg['status']     = 0;
							$arg['error_code']  = HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['data']       = array();
							$arg['message']    = $this->lang->line('plan_not_purchase');
							echo json_encode($arg);exit();
						}
					}

					// Check email
					$business_email = $this->dynamic_model->getdatafromtable('business', array('primary_email' => $this->input->post('email'), 'id !=' => $business_id), '*');
					if(!empty($business_email)) {
						$arg['status']      = 0;
						$arg['error_code']  = HTTP_NOT_FOUND;
						$arg['error_line']  = __line__;
						$arg['data']        = array();
						$arg['message']     = $this->lang->line('email_unique');
						echo json_encode($arg);exit();
					}

					$business_mobile = $this->dynamic_model->getdatafromtable('business', array('business_phone' => $this->input->post('mobile'), 'id !=' => $business_id), '*');
					if(!empty($business_mobile)) {
						$arg['status']     = 0;
						$arg['error_code']  = HTTP_NOT_FOUND;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('mobile_unique');
						echo json_encode($arg);exit();
					}

					$business_name   = $this->input->post('business_name');
					$services = 0;
					if ($this->input->post('service_type_id')) {
						$service_type_id = $this->input->post('service_type_id');
						$services        = multiple_decode_ids($service_type_id);
					}

					$business_type = 0;
					if ($this->input->post('business_type_id')) {
						$business_type    = decode($this->input->post('business_type_id'));
					}

					// $category_info    = json_decode($this->input->post('category_info'));
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
					$website          = $this->input->post('website');
					$owner_details    = $this->input->post('owner_details');
					$zipcode          = $this->input->post('zipcode');
					$location_name    = $this->input->post('location_name');
					$img_name = '';
					if(!empty($_FILES['business_logo']['name'])) {
						$img_name = $this->dynamic_model->fileupload('business_logo','uploads/business','Picture');
					}

					$businessData =   array(
						// 'user_id'    =>$usid,
						'business_name'  =>$business_name,
						'address'        =>$business_address,
						//'service_type'   =>$services,
						//'business_type'  =>$business_type,
						//'category'       =>$category_ids,
						'primary_email'  =>$email,
						'business_phone' =>$mobile,
						'website'        =>$website,
						'lat'            =>$latitude,
						'longitude'      =>$longitude,
						'country'        =>$country,
						'state'          =>$state,
						'city'           =>$city,
						'zipcode'        =>$zipcode,
						'location_name'  =>$location_name,
						'owner_details'   =>$owner_details,
						'update_dt'       =>$time
					);

					if (!empty($img_name)) {
						$businessData['logo'] = $img_name;
						$businessData['business_image'] = $img_name;
					}

					if ($services != 0) {
						$businessData['service_type'] = $services;
					}
					if ($business_type != 0) {
						$businessData['business_type'] = $business_type;
					}
					$this->db->where('id', $business_id);
					$this->db->update('business', $businessData);

					$where1 = array('id' => $business_id, 'user_id'=>$usid);
					$business_data = $this->dynamic_model->getdatafromtable('business',$where1,'*');

					$info = $business_data[0];
					$query = 'SELECT category as id FROM `business_category` WHERE business_category.parent_id = 0 and business_category.business_id = '.$business_id;
					$category = $this->dynamic_model->getQueryResultArray($query);

					$categoryArray = array();
					if (!empty($category)) {
						for($i = 0; $i < count($category); $i++) {
							$rowInfo = $category[$i];
							$categoryId = $rowInfo['id'];
							$subQuery = 'SELECT GROUP_CONCAT(id) as id FROM `business_category` WHERE business_category.parent_id = '.$categoryId.' and business_category.business_id = '.$business_id;
							array_push($categoryArray, array('parent_id' => $categoryId, 'subcategory' => $this->dynamic_model->getQueryRowArray($subQuery)));
						}
					}
					$url = site_url() . 'uploads/business/';
					$info['logo'] = $url.$info['logo'];
					$info['category'] = $categoryArray;

					$arg['status']    = 1;
					$arg['error_code'] = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['message']   = $this->lang->line('business_update');
					$arg['data']      = $info;
				}

			}
		}

		echo json_encode($arg);
	}

	public function passes_details() {
		$arg = array();
		$userdata = web_checkuserid();
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{
	      $_POST = json_decode(file_get_contents("php://input"), true);
		  if($_POST)
		  {
		    $this->form_validation->set_rules('pass_id', 'Pass Id', 'required');
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
				$pass_id = decode($this->input->post('pass_id'));
				$where=array("id" => $pass_id, "business_id"=>decode($userdata['data']['business_id']),"status"=>"Active");
				$pass_data = $this->dynamic_model->getdatafromtable('business_passes',$where,"*");
				if(!empty($pass_data)){
				    foreach($pass_data as $value)
		            {
		            	$passesdata['pass_id']      = encode($value['id']);
						$passesdata['business_id']    = $value['business_id'];
						$passesdata['user_id']    = $value['user_id'];
						$passesdata['pass_name']    = $value['pass_name'];
		            	$passesdata['pass_validity']= $value['pass_validity'];
						$passesdata['purchase_date']=  date("d M Y ",$value['purchase_date']);
						$passesdata['amount']=$value['amount'];
						$passesdata['service_type']=$value['service_type'];
		            	$passesdata['class_type']   =  $value['class_type'];
						$passesdata['pass_type'] = $value['pass_type'];
						$passesdata['pass_sub_type']=$value['pass_type_subcat'];
						$passesdata['status'] = $value['status'];
		            	$passesdata['passes_id']    = $value['pass_id'];
		            	$passesdata['pass_end_date']=  date("d M Y ",$value['pass_end_date']);
		            	$passesdata['purchase_date_utc']= $value['purchase_date'];
		            	$passesdata['pass_end_date_utc']= $value['pass_end_date'];
						$passesdata['tax1']=$value['tax1'];
						$passesdata['tax2']=$value['tax2'];
						$passesdata['tax1_rate']=$value['tax1_rate'];
						$passesdata['tax2_rate']=$value['tax2_rate'];
						$passesdata['is_client_visible']=$value['is_client_visible'];
						$passesdata['is_one_time_purchase']=$value['is_one_time_purchase'];
						$passesdata['description']=$value['description'];
						$passesdata['notes']=$value['notes'];
						$passesdata['is_recurring_billing']=$value['is_recurring_billing'];
						$passesdata['billing_start_from']=$value['billing_start_from'];
						$passesdata['age_restriction']=$value['age_restriction'];
						$passesdata['age_over_under']=$value['age_over_under'];

		            	$passesdata['create_dt']    =  date("d M Y ",$value['create_dt']);
		            	$passesdata['create_dt_utc'] = $value['create_dt'];
		            	$response[]	                = $passesdata;
		            }
					$arg['status']     = 1;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = $response[0];
					$arg['message']    = $this->lang->line('record_found');
				}else{
					$arg['status']     = 0;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = array();
				 	$arg['message']    = $this->lang->line('record_not_found');
				}
		    }
		  }
		}

	   echo json_encode($arg);
	}

	public function update_passes() {
		$arg   = array();
	   	$userdata = web_checkuserid();
	   	if($userdata['status'] != 1){
			$arg = $userdata;
		}
		else
		{
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
				$this->form_validation->set_rules('pass_id', 'Pass Id', 'required');
				$this->form_validation->set_rules('pass_name','Pass name', 'required|trim', array( 'required' => $this->lang->line('pass_name_required')));
			    $this->form_validation->set_rules('tax1','Tax 1','required',array( 'required' => $this->lang->line('tax1_required')));
		        $this->form_validation->set_rules('tax2','Tax 2','required',array( 'required' => $this->lang->line('tax2_required')));
		        $this->form_validation->set_rules('is_client_visible','Is client visible','required',array( 'required' => $this->lang->line('is_client_visible_required')));
		        $this->form_validation->set_rules('description','Description','required',array( 'required' => $this->lang->line('description_required')));
		        $this->form_validation->set_rules('notes','Notes','required',array( 'required' => $this->lang->line('notes_required')));
		        if ($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {
					$userdata = web_checkuserid();
					$usid 			=	decode($userdata['data']['id']);
					$business_id 	=	decode($userdata['data']['business_id']);
					$time=time();
					$pass_id = $this->input->post('pass_id');
					$pass_name   = $this->input->post('pass_name');
					$tax1      = $this->input->post('tax1');
					$tax2      = $this->input->post('tax2');
					$is_client_visible      = $this->input->post('is_client_visible');
					$description      = $this->input->post('description');
					$notes      = $this->input->post('notes');

					$info = $this->dynamic_model->getQueryRowArray('SELECT * FROM business_passes WHERE id = '.$pass_id);

					if (!empty($info)) {

						$amount = $info['amount'];
						$tax1_rate      = ($this->input->post('tax1_rate'))? $this->input->post('tax1_rate') :0;
						$tax1_rate = ($amount*$tax1_rate)/100;

						$tax2_rate      = ($this->input->post('tax2_rate'))?$this->input->post('tax2_rate') :0 ;
						$tax2_rate = ($amount*$tax2_rate)/100;

						$passData =   array(
							'pass_name'     		=>	$pass_name,
							'tax1'          		=>	$tax1,
							'tax2'          		=>	$tax2,
							'tax1_rate'				=>	($tax1_rate)?$tax1_rate:0,
							'tax2_rate'				=>	($tax2_rate)?$tax2_rate:0,
							'tax1_rate_percentage'	=>	($tax2_rate)?$tax2_rate:0,
							'tax2_rate_percentage'	=>	($tax2_rate)?$tax2_rate:0,
							'is_client_visible'     =>	$is_client_visible,
							'description'          	=>	$description,
							'notes'          		=>	$notes,
							'update_dt'   			=>	$time
						);
						$this->dynamic_model->updateRowWhere('business_passes', array('id' => $pass_id), $passData);

						$classIds = $this->input->post('class_details');

						if (is_array($classIds) && count($classIds) > 0) {
							$insertClassPass = array();
							foreach($classIds as $cl) {
								array_push($insertClassPass, array(
									'user_id'		=>	$usid,
									'business_id'	=> $business_id,
									'class_id'		=>	$cl,
									'pass_id'		=>	$pass_id,
									'create_dt'		=>	$time,
									'update_dt'		=>	$time,
								));
							}
							if (!empty($insertClassPass)) {
								$this->db->insert_batch('business_passes_associates', $insertClassPass);
							}
						}

						$arg['status']    = 1;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line']= __line__;
					 	$arg['message']   = 'Passes update successfully';
					 	$arg['data']      = [];

					} else {
						$arg['status']  = 0;
						$arg['error_code'] = 0;
						$arg['error_line']= __line__;
						$arg['message'] = 'Invalid passess';
					}

				}
			}
		}
		echo json_encode($arg);
	}

	public function shift_update() {
		$arg   = array();
		$userdata = web_checkuserid();
   	if($userdata['status'] != 1){
			$arg = $userdata;
		} else {
			$_POST = json_decode(file_get_contents("php://input"), true);
		}
		echo json_encode($arg);
	}
	public function add_shift() {
		$arg   = array();
		$userdata = web_checkuserid();
	   	if($userdata['status'] != 1){
			$arg = $userdata;
		}
		else
		{
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
				$this->form_validation->set_rules('day_id','Day Id', 'required|trim', array( 'required' => 'Day Ids is required'));
				$this->form_validation->set_rules('repeat','Repeat ', 'required|trim|numeric',
					array( 'required' => 'Repeat week is required', 'numeric' => 'Repeat week is required')
				);
				$this->form_validation->set_rules('start_date','Start Date', 'required|trim|numeric',
					array( 'required' => 'Start Date is required', 'numeric' => 'Start Date is required')
				);
				$this->form_validation->set_rules('start_time','Start Time', 'required|trim|numeric',
					array( 'required' => 'Start Time is required', 'numeric' => 'Start Time is required')
				);
				$this->form_validation->set_rules('end_time','End Time', 'required|trim|numeric',
					array( 'required' => 'End Time is required', 'numeric' => 'End Time is required')
				);
				$this->form_validation->set_rules('duration','Duration', 'required|trim|numeric',
					array( 'required' => 'Duration is required', 'numeric' => 'Duration is required')
				);
				$this->form_validation->set_rules('instructor','Instructor', 'required|trim',
					array( 'required' => 'Instructor is required')
				);
				$this->form_validation->set_rules('location','Location', 'required|trim',
					array( 'required' => 'Location is required')
				);
				$this->form_validation->set_rules('pay_type','Pay Type', 'required|trim',
					array( 'required' => 'Pay Type is required')
				);
				$this->form_validation->set_rules('pay_rate','Pay Rate', 'required|trim',
					array( 'required' => 'Pay Rate is required')
				);
				$this->form_validation->set_rules('description','Description', 'required|trim',
					array( 'required' => 'Description is required')
				);
				if ($this->input->post('shift_name')) {
					$this->form_validation->set_rules('shift_name','Shift Name', 'max_length[200]',
						array( 'max_length' => 'Shift Name is to long')
					);
				}
				if ($this->form_validation->run() == FALSE)
				{
					$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {
					$userdata 		=	web_checkuserid();
					$user_id 		=	decode($userdata['data']['id']);
					$business_id 	=	decode($userdata['data']['business_id']);
					$time			=	time();

					$time_zone =  $this->input->get_request_header('Timezone', true);
          $time_zone =  $time_zone ? $time_zone : 'UTC';
					date_default_timezone_set($time_zone);

					$start_date = $this->input->post('start_date');
					// $start_date = strtotime(date('Y-m-d'));
					$location 	= decode($this->input->post('location'));
					$repeat = $this->input->post('repeat');
					$start_time = $this->input->post('start_time');
					$end_time = $this->input->post('end_time');
					$day_id = $this->input->post('day_id');
					$instructor = decode($this->input->post('instructor'));
					$duration = $this->input->post('duration');
					$pay_type = $this->input->post('pay_type');
					$pay_rate = $this->input->post('pay_rate');
					$description = $this->input->post('description');

					$total_days = ($repeat * 7) + 6;
					$end_date   = strtotime('+'.$total_days.' day', $start_date);
					$dayOfWeek = date("l", $start_date);
					$begin = new DateTime(date('Y-m-d', $start_date));
					$end   = new DateTime(date('Y-m-d', $end_date));
					$counter = 1;
					$this->db->select('*')->from('manage_week_days')->where_in('id', explode(',', $day_id));
					$info = $this->db->get()->result_array();
					$dayId = array();
					foreach ($info as $in) {
						array_push($dayId, $in['id']);
					}
					$insertMaster = array(
						'business_id' => $business_id,
						'user_id'     => $user_id,
						'location_id' => $location,
						'start_date'  => $start_date,
						'end_date'    => $end_date,
						'week_repeat' => $repeat,
						'duration'    => $duration,
						'pay_type'    => $pay_type,
						'pay_rate'    => $pay_rate,
						'description' => $description,
						'created_by'  => $user_id,
						'updated_by'  => $user_id,
						'create_dt'   => $time,
						'update_dt'   => $time
					);
					if ($this->input->post('shift_name')) {
						$insertMaster['shift_name'] = $this->input->post('shift_name');
					}

					$insertSchedling = array();
					$shiftId = 0;
					$shifdIds = array();
					for($i = $begin; $i <= $end; $i->modify('+1 day')) {
						$current_date = $i->format("Y-m-d");
						$current_day = date('N', strtotime($current_date));
						if (in_array($current_day, $dayId)) {
							$convert_start_date = $current_date . ' ' .date('h:i A', $start_time);
							$convert_end_date = $current_date . ' ' .date('h:i A', $end_time);
							array_push($insertSchedling,
								array(
									'shift_id'   			=> $shiftId,
									'shift_date' 			=> strtotime($current_date),
									'shift_date_str' 	=> $current_date,
									'day_id' 	 				=> $current_day,
									'start_time' 			=> strtotime($convert_start_date),
									'end_time'	 			=> strtotime($convert_end_date),
									'create_dt'  			=> $time,
									'update_dt'  			=> $time
								)
							);

							$getCurrentDateData = $this->db->query('SELECT * FROM `business_shift_scheduling` WHERE shift_date_str = "'.$current_date.'"');

							if ($getCurrentDateData->num_rows() > 0) {
								$collection = $getCurrentDateData->result_array();
								foreach ($collection as $value) {

									$tbl_start_date = $current_date . ' ' .date('h:i A', $value['start_time']);
									$tbl_end_date = $current_date . ' ' .date('h:i A', $value['end_time']);

									$current_start_date = $current_date . ' ' .date('h:i A', $start_time);
									$current_end_date = $current_date . ' ' .date('h:i A', $end_time);

									$convert_tbl_start_date = new DateTime($tbl_start_date);
									$convert_tbl_tbl_end_date = new DateTime($tbl_end_date);
									/* Enter date */
									$convert_current_start_date = new DateTime($current_start_date);
									$convert_current_end_date = new DateTime($current_end_date);

    								if ( ($convert_current_start_date->getTimestamp() >= $convert_tbl_start_date->getTimestamp() && $convert_current_start_date->getTimestamp() <= $convert_tbl_tbl_end_date->getTimestamp()) || ($convert_current_end_date->getTimestamp() <= $convert_tbl_start_date->getTimestamp() && $convert_current_end_date->getTimestamp() >= $convert_tbl_tbl_end_date->getTimestamp())) {
    									array_push($shifdIds, $value['shift_id']);
    								}

								}

								$location_info = '';
								if (!empty($shifdIds)) {
									$shifdIds = array_unique($shifdIds);
									$query = "SELECT GROUP_CONCAT(location_id SEPARATOR ',') as location_id FROM `business_shift` WHERE id in (".implode(',', $shifdIds).")";
									$location_info = $this->db->query($query)->row_array()['location_id'];
								}
								if (!empty($location_info)) {
									$locationArray = explode(',', $location_info);
									if (in_array($location, $locationArray)) {
										$arg['status']     = 0;
										$arg['error_code']  = HTTP_OK;
										$arg['error_line']= __line__;
										$arg['message']    = 'Already have same time shift at this Room Location . Please Select different Room Location or Date and time for creating the new shift';
										echo json_encode($arg); exit;
									}
								}
							}

						}
					} /* Schedule section end */

					$insertInstructor = array();
					// $instructor = explode(',', $instructor);

					$instrucot_info = '';
					if (!empty($shifdIds)) {
						$query = "SELECT GROUP_CONCAT(instructor SEPARATOR ',') as instructor FROM `business_shift_instructor` WHERE shift_id in (".implode(',', $shifdIds).")";
						$instrucot_info = $this->db->query($query)->row_array()['instructor'];
					}

					if (!empty($instrucot_info)) {
						$instructorArray = explode(',', $instrucot_info);
						if (in_array($instructor, $instructorArray)) {
							$arg['status']     = 0;
							$arg['error_code']  = HTTP_OK;
							$arg['error_line']= __line__;
							$arg['message']    = 'Instructor already have same time shift. Please Create different Date or time shift';
							echo json_encode($arg); exit;
						}
					}


					$shiftId = $this->dynamic_model->insertdata('business_shift',$insertMaster);

					$updateSchedule = array();
					$instructor_collection = array();
					if (!empty($insertSchedling)) {
						foreach($insertSchedling as $val) {
							$val['shift_id'] = $shiftId;
							array_push($updateSchedule, $val);
							// $scheduleId = $this->dynamic_model->insertdata('business_shift_scheduling',$val);
							/*array_push($instructor_collection, array(
								'shift_id'		=>	$shiftId,
								'schedule_id'	=>	$scheduleId,
								'instructor' 	=> 	$instructor,
								'create_dt' 	=> 	$time
							));*/
						}
						$this->db->insert_batch('business_shift_scheduling', $updateSchedule);
						// New Code
						// $this->db->insert_batch('business_shift_instructor', $instructor_collection);
					}

					$shiftId = $this->dynamic_model->insertdata('business_shift_instructor',array(
						'shift_id'	=>	$shiftId,
						'instructor' => $instructor,
						'create_dt' => $time
					));

					$arg['status']    = 1;
					$arg['error_code'] = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['message']   = 'Shift created successfully';
					$arg['data']      = array();
				}
			}
		}

		echo json_encode($arg);
	}

	public function shift_all_schedule() {
		$arg = array();
		$userdata = web_checkuserid();
	   	if($userdata['status'] != 1){
			$arg = $userdata;
		} else {
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
				$this->form_validation->set_rules('id', 'Id', 'required|numeric',array(
					'required' => 'Id is required',
					'numeric' => 'Id is required',
				));
				if($this->form_validation->run() == FALSE)
				{
					$arg['status']  = 0;
					$arg['error_code'] = 0;
					$arg['error_line']= __line__;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {
					$userdata 		=	web_checkuserid();
					$user_id 		=	decode($userdata['data']['id']);
					$business_id 	=	decode($userdata['data']['business_id']);
					$shiftId = $this->input->post('id');

					$response	=	array();
					$getRecord = $this->dynamic_model->getdatafromtable('business_shift', array('id' => $shiftId));
					if ($getRecord) {
						$getSchedule = $this->dynamic_model->getdatafromtable('business_shift_scheduling', array('shift_id' => $getRecord[0]['id']));
						$responce = $getRecord[0];
						$responce['schedule'] = $getSchedule;
						$arg['status']     	= 1;
					  $arg['error_code']  = HTTP_OK;
					  $arg['error_line']	= __line__;
					  $arg['data']       	= $responce;
					  $arg['message']    	= $this->lang->line('record_found');
					} else {
						$arg['status']     = 0;
					  $arg['error_code']  = HTTP_OK;
					  $arg['error_line']= __line__;
					  $arg['data']       = array();
					  $arg['message']    = $this->lang->line('record_not_found');
					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function list_shift() {
		$arg = array();
		$userdata = web_checkuserid();
	   	if($userdata['status'] != 1){
			$arg = $userdata;
		} else {
			$_POST = json_decode(file_get_contents("php://input"), true);
		  	if($_POST)
		  	{
				$this->form_validation->set_rules('page_no', 'Page No', 'required|numeric',array(
					'required' => $this->lang->line('page_no'),
					'numeric' => $this->lang->line('page_no_numeric'),
				));
				$this->form_validation->set_rules('shift_status', 'Shift Status', 'required|numeric',array(
					'required' 	=> 'Status is required',
					'numeric' 	=> 'Status is required',
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
					$userdata 		=	web_checkuserid();
					$user_id 		=	decode($userdata['data']['id']);
					$business_id 	=	decode($userdata['data']['business_id']);

					$response	=	array();
					$page_no	= 	(!empty($this->input->post('page_no'))) ? $this->input->post('page_no') : "1";
					$page_no	= 	$page_no-1;
					$limit    = 	config_item('page_data_limit');
					$offset 	=   $limit * $page_no;
					$status		=	$this->input->post('shift_status');

					$time_zone =  $this->input->get_request_header('Timezone', true);
          			$time_zone =  $time_zone ? $time_zone : 'UTC';
					date_default_timezone_set($time_zone);

					if ($this->input->post('start_date') && $this->input->post('end_date')) {
						$start_date = $this->input->post('start_date');
						$end_date = $this->input->post('end_date');
						$collection = getShift(1, $business_id, $user_id, $limit, $offset, 'shift_date_str', 'ASC', '', 0, $status, $start_date, $end_date);
					} else {
						$current_date = date('Y-m-d');
						$collection = getShift(1, $business_id, $user_id, $limit, $offset, 'shift_date_str', 'ASC', $current_date, 0, $status);
					}



					if (!empty($collection)) {
						array_walk ( $collection, function (&$key) {
							$key['location_id'] = encode($key['location_id']);
						});
						$arg['status']     = 1;
						$arg['error_code']  = HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = $collection;
						$query = 'SELECT COUNT(*) as total_record FROM `business_shift_scheduling` JOIN business_shift on (business_shift.id = business_shift_scheduling.shift_id) WHERE business_shift.business_id = '.$business_id;
						$arg['total_record'] = $this->db->query($query)->row_array()['total_record'];
						$arg['message']    = $this->lang->line('record_found');
					} else {
						$arg['status']     = 0;
						$arg['error_code']  = HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('record_not_found');
					}

				}
			}
		}
		echo json_encode($arg);
	}

	public function transaction_list() {
		$arg = array();
		$userdata = web_checkuserid();
	   	if($userdata['status'] != 1){
			$arg = $userdata;
		} else {
			$_POST = json_decode(file_get_contents("php://input"), true);
		  	if($_POST)
		  	{
				$this->form_validation->set_rules('page_no', 'Page No', 'required|numeric',array(
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
					$userdata 		=	web_checkuserid();
					$user_id 		=	decode($userdata['data']['id']);
					$business_id 	=	decode($userdata['data']['business_id']);

					$response	=	array();
					$page_no	= 	(!empty($this->input->post('page_no'))) ? $this->input->post('page_no') : "1";
					$page_no	= 	$page_no-1;
					$limit    	= 	config_item('page_data_limit');
					$offset 	=   $limit * $page_no;

					$query = "SELECT user.name, user.lastname, user.email, user.mobile, user.profile_img, user.gender, user.date_of_birth, user.country_code, user.country, user.state, user.city, user.zipcode, user.address, user.location, user_booking.id, user_booking.class_id,  user_booking.service_id,  user_booking.transaction_id,  user_booking.trainer_user_id,  user_booking.amount, user_booking.sub_total, user_booking.discount, user_booking.quantity,  user_booking.tax_amount,  user_booking.payment_mode,  user_booking.payment_note,  user_booking.reference_payment_id,  user_booking.passes_start_date,  user_booking.passes_end_date, user_booking.passes_status,  user_booking.passes_total_count,  user_booking.passes_remaining_count FROM `user_booking` JOIN user on (user.id = user_booking.user_id)
					WHERE user_booking.business_id = ".$business_id." AND user_booking.status = 'success' ORDER BY user_booking.create_dt LIMIT ".$limit." OFFSET ".$offset;

					$collection = $this->db->query($query)->result_array();
					if(!empty($collection)) {
						array_walk ( $collection, function (&$key) {
							$key['profile_img'] = site_url() . 'uploads/user/'.$key['profile_img'];
							$transactionLength = strlen($key['transaction_id']);
							if ($transactionLength > 0) {
								$key['payment_mode'] = 'Online';
								$currentTransaction = $this->db->get_where('transactions', array('id' => $key['transaction_id']))->row_array();
								$key['trx_id'] = $currentTransaction['trx_id'];
								$key['amount'] = $currentTransaction['amount'];
								$key['order_number'] = $currentTransaction['order_number'];
							} else {
								$key['payment_mode'] = $key['payment_mode'];
								$key['trx_id'] = '';
								$key['order_number'] = '';
							}
							unset($key['transaction_id']);
						});
					}
					// $collection = getShift(1, $business_id, $user_id, $limit, $offset, 'create_dt', 'ASC');

					if (!empty($collection)) {
						$arg['status']     = 1;
						$arg['error_code']  = HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = $collection;
						$arg['message']    = $this->lang->line('record_found');
					} else {
						$arg['status']     = 0;
						$arg['error_code']  = HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('record_not_found');
					}

				}
			}
		}
		echo json_encode($arg);
	}

	public function update_class_passes() {
		$arg = array();
		$userdata = web_checkuserid();
	   	if($userdata['status'] != 1){
			$arg = $userdata;
		} else {
			$_POST = json_decode(file_get_contents("php://input"), true);
		  	if($_POST)
		  	{
				$this->form_validation->set_rules('class_id', 'Class Id', 'required',array(
					'required' => $this->lang->line('class_id_required')
				));
				$this->form_validation->set_rules('pass_id[]', 'Pass Id', 'required', array(
					'required' => 'Pass id required'
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
					$userdata 		=	web_checkuserid();
					$class_id		=	decode($this->input->post('class_id'));
					$pass_id        =   $this->input->post('pass_id');
					$user_id 		=	decode($userdata['data']['id']);
					$business_id 	=	decode($userdata['data']['business_id']);
					$time			= 	time();
					$type           =   1;
					if ($this->input->post('action_type')) {
						$type           =   $this->input->post('action_type');
					}

					if (is_array($pass_id)) {
						$decodeArray = array();
						for($i = 0; $i < count($pass_id); $i++) {
							array_push($decodeArray, decode($pass_id[$i]));
						}

						$this->db->select('*');
						$this->db->from('business_passes_associates');
						$this->db->where('user_id', $user_id);
						$this->db->where('class_id', $class_id);
						$this->db->where('business_id', $business_id);
						$this->db->where_in('pass_id', $decodeArray);
						$info = $this->db->get();
						if ($type === 1) {
							if ($info->num_rows() === 0 ) {
								$insertData = array();
								for($i = 0; $i < count($decodeArray); $i++) {
									array_push($insertData, array(
										'user_id'	=>	$user_id,
										'business_id'	=> $business_id,
										'class_id'	=>	$class_id,
										'pass_id'	=>	$decodeArray[$i],
										'create_dt'	=>	$time,
										'update_dt'	=>	$time,
									));
								}
								$this->db->insert_batch('business_passes_associates', $insertData);
								$arg['status']     = 1;
								$arg['error_code']  = HTTP_OK;
								$arg['error_line']= __line__;
								$arg['data']       = array();
								$arg['message']    = 'Class passes updated successfully.';
							} else {
								$arg['status']     = 0;
								$arg['error_code']  = HTTP_OK;
								$arg['error_line']= __line__;
								$arg['data']       = array();
								$arg['message']    = 'Pass already exists';
							}
						} else {
							if ($info->num_rows() === 0 ) {
								$arg['status']     = 0;
								$arg['error_code']  = HTTP_OK;
								$arg['error_line']= __line__;
								$arg['data']       = array();
								$arg['message']    = 'Pass not exists';
							} else {

								$this->db->where('class_id', $class_id);
								$this->db->where('business_id', $business_id);
								$this->db->where_in('pass_id', $decodeArray);
								$this->db->delete('business_passes_associates');

								$arg['status']     = 1;
								$arg['error_code']  = HTTP_OK;
								$arg['error_line']= __line__;
								$arg['data']       = array();
								$arg['message']    = 'Class passes deleted successfully.';
							}
						}

					} else {
						$arg['status']     = 0;
						$arg['error_code']  = HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = 'Invalid Passes data';
					}


				}
			}
		}
		echo json_encode($arg);
	}

	public function update_class_schedule() {
		$arg = array();
		$userdata = web_checkuserid();
	   	if($userdata['status'] != 1){
			$arg = $userdata;
		} else {
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
		  	{
				$this->form_validation->set_rules('class_id', 'Class Id', 'required',array(
					'required' => $this->lang->line('class_id_required')
				));
				$this->form_validation->set_rules('schedule_id', 'Schedule Id', 'required',array(
					'required' => 'Schedule id is required'
				));
				if($this->form_validation->run() == FALSE)
				{
					$arg['status']  = 0;
					$arg['error_code'] = 0;
					$arg['error_line']= __line__;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {
					$userdata 		=	web_checkuserid();
					$class_id		=	decode($this->input->post('class_id'));
					$schedule_id	=	$this->input->post('schedule_id');
					$business_id 	=	decode($userdata['data']['business_id']);
					$time			= 	time();

					if ($this->input->post('capacity')) {
						$this->dynamic_model->updateRowWhere('business_class',
							array('id' => $class_id),
							array('capacity' => $this->input->post('capacity'))
						);
						$arg['status']     = 1;
						$arg['error_code']  = HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = 'Class capacity updated successfully.';
					} else if ($this->input->post('instructor_id')) {

						$instractor_id = decode($this->input->post('instructor_id'));

						$type = '1';
						if ($this->input->post('type')) {
							$type = $this->input->post('type');
						}

						if ($type === '1' || $type === '2') {

							if ($type === '1') {

								$this->dynamic_model->updateRowWhere('class_scheduling_time',
									array('id' => $schedule_id, 'business_id' => $business_id, 'class_id' => $class_id),
									array('instructor_id' => $instractor_id)
								);
								$arg['status']      = 1;
								$arg['error_code']  = HTTP_OK;
								$arg['error_line']  = __line__;
								$arg['data']       = array();
								$arg['message']    = 'Class instructor updated successfully.';

							} else {

								$this->dynamic_model->updateRowWhere('class_scheduling_time',
									array('business_id' => $business_id, 'class_id' => $class_id),
									array('instructor_id' => $instractor_id)
								);
								$arg['status']      = 1;
								$arg['error_code']  = HTTP_OK;
								$arg['error_line']  = __line__;
								$arg['data']       = array();
								$arg['message']    = 'Class instructor updated successfully.';
							}

						} else {
							$arg['status']     = 0;
							$arg['error_code']  = HTTP_OK;
							$arg['error_line']= __line__;
							$arg['data']       = array();
							$arg['message']    = 'Invalid Request';
						}

					} else {
						$arg['status']     = 0;
						$arg['error_code']  = HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = 'Invalid Request';
					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function service_appointment_list() {
	   $arg = array();
	   $userdata = web_checkuserid();
	   if($userdata['status'] != 1){
	        $arg = $userdata;
	   } else {
	      $_POST = json_decode(file_get_contents("php://input"), true);
	      if($_POST)
	      {
	        $this->form_validation->set_rules('pageid', 'Page No', 'required|numeric',array(
                                        'required' => $this->lang->line('page_no'),
                                        'numeric' => $this->lang->line('page_no_numeric'),
                ));
                if($this->form_validation->run() == FALSE) {
        	        $arg['status']  = 0;
					$arg['error_code'] = 0;
					$arg['error_line']= __line__;
					$arg['message'] = get_form_error($this->form_validation->error_array());
                } else {
                    $userdata	=	web_checkuserid();
					$user_id 	=	decode($userdata['data']['id']);
					$business_id 	=	decode($userdata['data']['business_id']);
					$response   =   array();
					$page_no    =   (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
					$transaction_id = (!empty($this->input->post('transaction_id'))) ? $this->input->post('transaction_id') : "";

					$start_dt = (!empty($this->input->post('search_dt'))) ? $this->input->post('search_dt') : "";
					$end_dt = $start_dt;

					$page_no    =   $page_no-1;
                    $limit      =   config_item('page_data_limit');
                    $offset     =   $limit * $page_no;
					$imgePath = base_url().'uploads/user/';

					$query = "SELECT t.create_dt, IFNULL(concat(uf.name, '', uf.lastname),'') as family_member_name, IFNULL(uf.gender,'') as family_gender, IFNULL(uf.date_of_birth,'') as family_dob,(CASE WHEN uf.profile_img != '' THEN CONCAT('".$imgePath."',uf.profile_img) ELSE '' END ) as family_profile_img, b.family_user_id,s.id as service_id, t.id as transaction_id,t.user_id,t.amount,t.discount,t.trx_id,t.order_number,t.create_dt as payment_date,t.payment_type,t.payment_method,t.responce_all,u.name,u.lastname, u.gender, u.date_of_birth,concat('".$imgePath."', u.profile_img) as profile_img, s.service_name,s.duration,s.cancel_policy,s.description, s.tax1, s.tax2, s.tax1_rate, s.tax2_rate,bs.business_name,bs.address,bs.location_detail,uu.name as instructor_name,uu.lastname as instructor_lastname,concat('".$imgePath."', uu.profile_img) as instructor_profile_img, b.status as booking_status,b.passes_start_date as start_time, b.passes_end_date as end_time, b.shift_date, b.tip_comment, bl.location_name,bl.address as location_address, s.tip_option  FROM transactions AS t join user_booking as b on t.id = b.transaction_id JOIN user as u on u.id = b.user_id join service as s on s.id = b.service_id JOIN business as bs on bs.id = b.business_id JOIN user as uu on uu.id = b.shift_instructor LEFT Join business_shift as bshift on bshift.id = b.shift_id left join business_location as bl on bl.id = bshift.location_id left join user as uf on uf.id = b.family_user_id WHERE b.business_id = ".$business_id." AND b.service_type = '2' ";

					if(!empty($transaction_id)){
						$query .= "AND t.id = '".$transaction_id."'";
					}
					if(!empty($start_dt)){
						$end_dt = $end_dt + 60*60*24;
						$query .= " AND t.create_dt >= '".$start_dt."' AND t.create_dt < '".$end_dt."'  ";
					}
					$total  = $query;
					// $query .= " ORDER BY b.create_dt desc LIMIT ".$limit.' OFFSET '.$offset;
					$query .= " ORDER BY b.create_dt desc";
                    $collection = $this->db->query($query)->result_array();
                    if (!empty($collection)) {
                        $arg['status']     = 1;
                        $arg['error_code']  = HTTP_OK;
                        $arg['error_line']= __line__;
						$arg['data']       = $collection;
						$arg['total'] = $this->db->query($total)->num_rows();
                        $arg['message']    = $this->lang->line('record_found');
                    } else {
                        $arg['status']     = 0;
                        $arg['error_code']  = HTTP_OK;
                        $arg['error_line']= __line__;
                        $arg['data']       = array();
                        $arg['message']    = $this->lang->line('record_not_found');
                    }
                }
	      }
	   }
	   echo json_encode($arg);
	}

	public function service_scheduling_time_slot() {
	   $arg = array();
	   $userdata = web_checkuserid();
	   if($userdata['status'] != 1){
	        $arg = $userdata;
	   } else {
	      $_POST = json_decode(file_get_contents("php://input"), true);
	      if($_POST)
	      {
	        $this->form_validation->set_rules('service_id', 'service id', 'required|numeric');
                $this->form_validation->set_rules('instructor_id', 'instructor id', 'required|numeric');
                $this->form_validation->set_rules('service_date', 'service date', 'required');
                if($this->form_validation->run() == FALSE) {
        	        $arg['status']  = 0;
			$arg['error_code'] = 0;
			$arg['error_line']= __line__;
			$arg['message'] = get_form_error($this->form_validation->error_array());
                } else {
                	$time_zone =  $this->input->get_request_header('Timezone', true);
                    $time_zone =  $time_zone ? $time_zone : 'UTC';
					date_default_timezone_set($time_zone);
                	$userdata	=	web_checkuserid();
					$user_id 	=	decode($userdata['data']['id']);
					$business_id 	=	decode($userdata['data']['business_id']);
					$data = array();
					$response = array();
					$time = time();
					$service_id= $this->input->post('service_id');
					$instructor_id=$this->input->post('instructor_id');
					$service_date = $this->input->post('service_date');
					$service_date = date('Y-m-d',$service_date);
					$query = "SELECT s.*,l.location_name,l.address as location_address,l.address,l.capacity FROM business_shift_instructor as si join business_shift as s on si.shift_id = s.id join business_location as l on l.id = s.location_id where si.instructor = '".$instructor_id."' AND s.business_id = '".$business_id."'";
                    $collection = $this->dynamic_model->getQueryResultArray($query);
					
                    $sql = "SELECT * FROM service as ss WHERE ss.id = '".$service_id."'";
                    $services_collection = $this->dynamic_model->getQueryRowArray($sql);

					$service_schedule = "SELECT bss.*, bsi.instructor, concat(user.name, ' ', user.lastname) as instructor_name, bl.location_name FROM business_shift_scheduling as bss JOIN business_shift as bs on (bs.id = bss.shift_id) JOIN business_shift_instructor as bsi on (bsi.shift_id = bss.shift_id) JOIN user on (user.id = bsi.instructor) left join business_location as bl on bl.id = bs.location_id WHERE bss.status = 1 AND bs.business_id = ".$business_id." AND bss.shift_date_str BETWEEN '".date('Y-m-d')."' AND '".date('Y-m-d', strtotime('+2 months'))."' AND bsi.instructor =  ".$instructor_id;
					
					// $service_schedule = "SELECT DISTINCT business_shift_scheduling.shift_date_str FROM `service` JOIN service_instructor on (service_instructor.service_id = service.id) JOIN business_shift_instructor on (business_shift_instructor.instructor = service_instructor.instructor_id) JOIN business_shift_scheduling on (business_shift_scheduling.shift_id = business_shift_instructor.shift_id) WHERE business_shift_scheduling.status = 1 AND service.id = ".$service_id." AND service.business_id = '".$business_id."' AND service_instructor.instructor_id = ".$instructor_id." AND  business_shift_scheduling.shift_date_str BETWEEN '".date('Y-m-d')."' AND '".date('Y-m-d', strtotime('+2 months'))."'";

					if (!empty($collection)) {
						
                        foreach ($collection as  $value){

                            $shift_id = $value['id'];
                            $duration = $services_collection['duration'];
                            $business_id = $value['business_id'];
                            $location_name = $value['location_name'];
                            $address = $value['address'];
                            $capacity = $value['capacity'];

							$sql = "SELECT * FROM business_shift_scheduling as ss WHERE ss.status =1 AND ss.shift_id = '".$shift_id."' AND ss.shift_date_str = '".$service_date."'";
                            $scheduling_collection = $this->dynamic_model->getQueryResultArray($sql);

							if (!empty($scheduling_collection)) {
                                foreach ($scheduling_collection as  $key){
									$start_time = $key['start_time'];
                                	$end_time = $key['end_time'];
                                	$shift_date = $key['shift_date'];
                                	$slot = $this->getShiftTimeSlote($start_time,$end_time,$shift_date,$duration);
                               		$data[]= array('shift_id'=>$shift_id,
                                    	'business_id'=>$business_id,
                                     	'location_name'=>$location_name,
                                    	'location_address'=>$address,
                                    	'address'=>$address,
                                    	'capacity'=>$capacity,
                                     	'duration'=>$duration,
										'services_collection'=>$services_collection,
										'shift_schedule_id' => $key['id'],
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
							$arg['schedule'] = $this->dynamic_model->getQueryResultArray($service_schedule);
							$arg['message']    = $this->lang->line('record_found');
						} else {
							$arg['status']     = 0;
							$arg['error_code']  = 0;
							$arg['error_line']= __line__;
							$arg['schedule'] = $this->dynamic_model->getQueryResultArray($service_schedule);
							$arg['message']    = 'no appointment found';
						}
					} else {
                        $arg['status']  = 0;
                        $arg['error_code'] = 0;
                        $arg['error_line']= __line__;
						$arg['schedule'] = $this->dynamic_model->getQueryResultArray($service_schedule);
                        $arg['message'] = 'no appointment found';
                    }
                }
            }
        }
        echo json_encode($arg);
    }

	function getShiftTimeSlote($start_time,$end_time,$shift_date,$interval=10){

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
   	function getShiftTimeSlote_old($start_time,$end_time,$shift_date,$interval=10){
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

            $sql = "SELECT * FROM user_booking as b WHERE b.passes_start_date = '".$endTime."' AND b.passes_end_date = '".$newEndTime."' AND b.shift_date = '".$shift_date."' AND service_type = '2'";
            $result = $this->dynamic_model->getQueryResultArray($sql);
            $is_available = $result ? 1 : 0;
           // $is_available = 0;

            if( $newEndTime <= $end_time ){

				$GivenDate = $shift_date;
				$CurrentDate = strtotime(date('Y-m-d'));

				if($GivenDate == $CurrentDate){
					$star_time_slot = date('Y-m-d h:i A');
					$end_time_slot  = date('Y-m-d h:i A', $endTime);
					$start = new DateTime($star_time_slot);
					$end = new DateTime($end_time_slot);

					if($start->getTimestamp() > $end->getTimestamp()) {
						$is_available = 1;
					}
				} else if ($CurrentDate > $GivenDate) {
					$is_available = 1;
				}

				$slote[] = array('slot' => $st .'-'. $newst,
                        'start_time_unix' => $endTime,
                        'end_time_unix' => $newEndTime,
                        'start_time' => date('Y-m-d h:i A', $endTime),
                        'end_time' => date('Y-m-d h:i A', $newEndTime),
                        'shift_date' => $shift_date,
						'is_available' => $is_available,
                       /*  'sql' => $sql */
                       );
            }
            $k=15;
        }
        return $slote;
    }

    public function get_customer_list() {
    	$arg = array();
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
		} else {
			$userdata = web_checkuserid();
			if($userdata['status'] != 1){
				$arg = $userdata;
			} else {
				$response=array();
            	$time=time();

				$usid 		= decode($userdata['data']['id']);
				$business_id= decode($userdata['data']['business_id']);
				$where= 'mobile_verified = 1 AND id != '.$usid.' AND email_verified = 1';
				$client_data = $this->dynamic_model->getdatafromtable('user',$where, '*');
				if($client_data) {
					foreach($client_data as $value) {
						if ( !empty($value['name']) && !empty($value['name']) && !empty($value['name']) ) {
							$clientdata['id']     = $value['id'];
							$clientdata['name']   = ucwords($value['name'].' '.$value['lastname']);
							$clientdata['email']  = $value['email'];
							$clientdata['gender'] = $value['gender'];
							$clientdata['profile_img']  = base_url().'uploads/user/'.$value['profile_img'];
							$clientdata['mobile'] = $value['mobile'];
							$clientdata['date_of_birth'] =  !empty($value['date_of_birth']) ? $value['date_of_birth'] : '';
							$response[] = $clientdata;
						}
					}
					$arg['status']    = 1;
					$arg['error_code'] = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']      = $response;
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
    	echo json_encode($arg);
    }

	public function get_member_list()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = web_checkuserid();
		   	if($userdata['status'] != 1){
				$arg = $userdata;
			}
			else
			{
		      $_POST = json_decode(file_get_contents("php://input"), true);
			  if($_POST)
			  {
			    $this->form_validation->set_rules('customer_id', 'Customer', 'required|numeric',array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no'),
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
					$usid =	$this->input->post('customer_id');
					$condition=array("user_id"=>$usid,"is_deleted"=>'0');
					$member_data= $this->dynamic_model->getdatafromtable('user_family_details',$condition,'*',"","","create_dt","DESC");
					if(!empty($member_data)){
					    foreach($member_data as $value)
			            {
			            	$memberdata['memeber_id']   = $value['id'];
			            	$memberdata['member_name'] = ucwords($value['member_name']);
			            	$memberdata['image']        = base_url().'uploads/user/'.$value['photo'];
			            	$memberdata['relation']     = get_family_name($value['relative_id']);
			            	$memberdata['relative_id']     = $value['relative_id'];
			            	$memberdata['dob']     = $value['dob'];
                            $memberdata['gender']     = $value['gender'];
			            	$memberdata['create_dt']    = date("d M Y ",$value['create_dt']);
			            	$response[]	                = $memberdata;
			            }
			        }
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

	public function book_services()
	{
	    $arg    = array();
	    $version_result = version_check_helper1();
	    if($version_result['status'] != 1 )
	    {
	        $arg = $version_result;
	    }
	    else
	    {
	        $userdata = web_checkuserid();
	        if($userdata['status'] != 1){
	         $arg = $userdata;
	        }
	        else
	        {
	        	$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
				{
					$this->form_validation->set_rules('customer','Customer', 'required|trim', array( 'required' => 'Customer is required'));
					$this->form_validation->set_rules('service_type','Service type', 'required|trim', array( 'required' => 'Service type'));
					$this->form_validation->set_rules('service_id','Service Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('quantity','Quantity', 'required', array( 'required' => $this->lang->line('quantity_required')));
					$this->form_validation->set_rules('grand_total','grand total','required',array(
							'required'   => $this->lang->line('amount_required'),
							'numeric'    => $this->lang->line('amount_valid')
						));
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
						$schedule_id=$this->input->post('schedule_id');
						$family_user_id=$this->input->post('family_user_id');
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
							'shift_instructor' => $shift_instructor,
							'shift_id' => $shift_id,
							'shift_schedule_id' => $schedule_id,
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
							$arg['message']   = 'Successful Booked the Appointment';//$this->lang->line('payment_succ');
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

	public function service_status_change()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = web_checkuserid();
		    if($userdata['status'] != 1){
			    $arg = $userdata;
			}
			else
			{
				$_POST = json_decode(file_get_contents("php://input"), true);
			    if($_POST)
			    {
					$userdata	=	web_checkuserid();
					$user_id 	=	decode($userdata['data']['id']);
					$business_id 	=	decode($userdata['data']['business_id']);

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

                        if($booking_status)
				        {
							$arg['status']      = 1;
							$arg['error_code']  = REST_Controller::HTTP_OK;
							$arg['error_line']  = __line__;
							$arg['pay_status']  = $status;
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

    public function update_services_instructor() {
    	$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = web_checkuserid();
		    if($userdata['status'] != 1){
			    $arg = $userdata;
			}
			else
			{
		        $_POST = json_decode(file_get_contents("php://input"), true);
			    if($_POST)
			    {
			        $this->form_validation->set_rules('id','Service Id', 'required|trim', array( 'required' => 'Service id is required'));
			        $this->form_validation->set_rules('instructor[]', 'Instructor Id', 'required',array(
						'required' => 'Instructor is required'
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
                        $service_id 	=  $this->input->post('id');
                        $instructor_ids =  $this->input->post('instructor');

                        $insert_data = true;
						$times =	time();
						/*$query = "SELECT GROUP_CONCAT(instructor_id SEPARATOR ',') as instructor_id FROM service_instructor WHERE service_id = ".$service_id;
						$findresult = $this->dynamic_model->getQueryRowArray($query)['instructor_id'];
						$arrResult = explode(',', $findresult);*/
                        for($i = 0; $i < count($instructor_ids); $i++) {
							$instructorId = decode($instructor_ids[$i]);
							$status = $this->db->get_where('service_instructor', array('service_id' => $service_id, 'instructor_id' => $instructorId))->num_rows();
							if ($status) {
                                $this->db->where('service_id', $service_id);
                                $this->db->where('instructor_id', $instructorId);
							    $this->db->update('service_instructor', array(
                                    'status' => 'Active'
                                ));
                            } else {
							    $this->db->insert('service_instructor', array(
                                    'service_id' => $service_id,
                                    'instructor_id' => $instructorId,
                                    'create_dt'	=> $times
                                ));
                            }
                            /*if (!in_array($instructorId, $arrResult)) {
								array_push($insert_data, array(
									'service_id' => $service_id,
									'instructor_id' => $instructorId,
									'create_dt'	=> $times
								));
							}*/
						}
						if(!empty($insert_data))
				        {
							// $this->db->insert_batch('service_instructor', $insert_data);
							$imgePath = base_url().'uploads/user/';
							$collection = $this->dynamic_model->getQueryResultArray("SELECT user.id, user.name, user.lastname, concat('".$imgePath."', user.profile_img) as profile, user.profile_img, user.availability_status, instructor_details.total_experience as experience, instructor_details.appointment_fees, instructor_details.appointment_fees_type, instructor_details.skill FROM service_instructor JOIN user on (user.id = service_instructor.instructor_id) JOIN instructor_details on (instructor_details.user_id = user.id) where service_instructor.service_id = ".$service_id);
							array_walk ( $collection, function (&$keys) {
								$keys["instructor_ids"] = encode($keys['id']);
							});
							$arg['status']      = 1;
							$arg['error_code']  = REST_Controller::HTTP_OK;
							$arg['error_line']  = __line__;
							$arg['data'] = $collection;
							$arg['message']     = 'Instructor update successfully';
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

	public function buy_now_services_cash()
	{
		$arg    = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}else
		{
			$userdata = web_checkuserid();
			if($userdata['status'] != 1){
			$arg = $userdata;
			}
			else
			{
			$_POST = json_decode(file_get_contents("php://input"), true);
				if($_POST)
				{
					$userdata	=	web_checkuserid();
					$user_id 	=	decode($userdata['data']['id']);
					$business_id 	=	decode($userdata['data']['business_id']);

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
						/* $usid =$userdata['data']['id'];
						$name =$userdata['data']['name'];
						$lastname =$userdata['data']['lastname']; */
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

	public function shift_cancel() {
    	$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = web_checkuserid();
		    if($userdata['status'] != 1){
			    $arg = $userdata;
			}
			else
			{
		        $_POST = json_decode(file_get_contents("php://input"), true);
			    if($_POST)
			    {
			        $this->form_validation->set_rules('schedule_id','Schedule Id', 'required|numeric', array(
						'required' => 'Schedule id is required',
						'numeric' => 'Schedule id is required',
					));
					$this->form_validation->set_rules('shift_id','Shift Id', 'required|numeric', array(
						'required' => 'Shift id is required',
						'numeric' => 'Shift id is required',
					));
					$this->form_validation->set_rules('type','Type', 'required|numeric', array(
						'required' => 'Type is required',
						'numeric' => 'Type is required',
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

                        $schedule_id 	=  $this->input->post('schedule_id');
                        $shift_id 		=  $this->input->post('shift_id');
                        $type 			=  $this->input->post('type');

						$query = 'select * from business_shift_scheduling where status = 1 AND id = '.$schedule_id. ' AND shift_id = '.$shift_id;
						$collection  = $this->dynamic_model->getQueryRowArray($query);

						if ($type == 1 || $type == 2) {

							if (!empty($collection)) {
								if ($type == 1) {
									$this->dynamic_model->updateRowWhere('business_shift_scheduling',
										array('id' => $schedule_id, 'shift_id' => $shift_id),
										array('status' => 3)
									);
								} else {
									$this->dynamic_model->updateRowWhere('business_shift_scheduling',
										array('shift_date_str >=' => $collection['shift_date_str'], 'shift_id' => $shift_id),
										array('status' => 3)
									);
								}

								$deactivateBookingQuery = "SELECT GROUP_CONCAT(id SEPARATOR ',') as schedule_id FROM `business_shift_scheduling` WHERE shift_id = ".$shift_id." AND shift_date_str >= '".$collection['shift_date_str']."'";

								$deactivateBookingQueryStatus = $this->db->query($deactivateBookingQuery);
								if ($deactivateBookingQueryStatus->num_rows()) {
									$rowData = $deactivateBookingQueryStatus->row_array()['schedule_id'];
									// $rowData = explode(',', $rowData);
									$query = 'UPDATE user_booking SET status = "Cancel" WHERE status = "Confirm" AND shift_id = '.$shift_id.' AND shift_schedule_id IN ('.$rowData.')';
									$this->db->query($query);
								}

								$arg['status']      = 1;
								$arg['error_code']  = REST_Controller::HTTP_OK;
								$arg['error_line']  = __line__;
								$arg['message']     = 'Status update successfully';

							} else {
								$arg['status']     	= 0;
								$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line']	= __line__;
								$arg['message']    	= 'Invalid Shift';
							}
						} else {
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

	public function buy_now_services()
	{
		$arg    = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = web_checkuserid();
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
				$this->form_validation->set_rules('user_id','Customer Id', 'required|trim', array( 'required' => 'Customer is required'));
				if($this->form_validation->run() == FALSE)
				{
					$arg['status']  = 0;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$service_id = $this->input->post('service_id');
					$transaction_id = $this->input->post('transaction_id');
					$where = array('id'=>$service_id,'status' => 'Active');
					$product_data = $this->dynamic_model->getdatafromtable('service',$where);
					$Amt=0;
					$usid = $this->input->post('user_id');
					$time = time();
					$pass_start_date=$pass_end_date=$pass_status='';
					//service_type => 1 passes 2 services 3 product

					$token            = $this->input->post('token');

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
							'create_dt'             =>$time,
							'transaction_date' => date('Y-m-d'),
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

	public function clover_buy_now_services()
	{
		$arg    = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = web_checkuserid();
			if($userdata['status'] != 1){
				//var_dump($userdata);die;
			$arg = $userdata;
			}
			else
			{
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
				$this->form_validation->set_rules('service_id','Service Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
				$this->form_validation->set_rules('transaction_id','Transaction Id', 'required|trim', array( 'required' => $this->lang->line('service_id_required')));
				$this->form_validation->set_rules('user_id','Customer Id', 'required|trim', array( 'required' => 'Customer is required'));
				if($this->form_validation->run() == FALSE)
				{
					$arg['status']  = 0;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$service_id = $this->input->post('service_id');
					$transaction_id = $this->input->post('transaction_id');
					$where = array('id'=>$service_id,'status' => 'Active');
					$product_data = $this->dynamic_model->getdatafromtable('service',$where);
					$Amt=0;
					$usid = $this->input->post('user_id');
					$time = time();
					$pass_start_date=$pass_end_date=$pass_status='';
					//service_type => 1 passes 2 services 3 product

					$token            = $this->input->post('token');

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


					       //$business_id = $this->input->post('business_id');
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
							// $res  = clover_api_payment_checkout($user_cc_no,$user_cc_mo,$user_cc_yr,$user_cc_cvv,$user_zip,$amount,$taxAmount,$marchant_id,$clover_key,$access_token,$currency,$token);
							//var_dump($res); die;
							//{ "id" : "R48Q7GPMY2FQY", "amount" : 1800, "amount_refunded" : 0, "currency" : "usd", "created" : 1616661109846, "captured" : true, "ref_num" : "108400500020", "auth_code" : "OK2809", "outcome" : { "network_status" : "approved_by_network", "type" : "authorized" }, "paid" : true, "status" : "succeeded", "source" : { "id" : "clv_1TSTS1iWemARMj4AXHxHikLV", "brand" : "AMEX", "cvc_check" : "unchecked", "exp_month" : "11", "exp_year" : "2025", "first6" : "378282", "last4" : "0005" } }

							//{"message":"400 Bad Request","error":{"code":"token_already_used","message":"You cannot use a clover token more than once unless it is marked as multipay."}}


							//{"paymentId":"30PFKD66YXNCP","result":"APPROVED","authCode":"OK7823","token":"DGY73XR6DDDF0","vaultedCard":{"first6":"411111","last4":"1111","expirationDate":"0321","token":"1894469479681111"}}

							//echo $res['message'];die;
							// if(@$res->status == 'succeeded')
							if(true)
							{
								$where = array('user_id' => $usid,
									'business_id' => $business_id,
								);
								$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);

								$ref_num    = getuniquenumber();
								$payment_id = time(); // !empty($res->id) ? $res->id : $ref_num;
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
									'create_dt'             =>$time,
									'transaction_date' => date('Y-m-d'),
									'update_dt'             =>$time,
									'responce_all'=> '', //json_encode($res)
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
								$arg['message']   = ''; //@$res->error->message;
								$arg['data']      =json_decode('{}');
							}
						}
					}
				}
			}
		echo json_encode($arg);
	}

    public function remove_services_instructor() {
        $arg = array();
        $version_result = version_check_helper1();
        if($version_result['status'] != 1 )
        {
            $arg = $version_result;
        }
        else
        {
            $userdata = web_checkuserid();
            if($userdata['status'] != 1){
                $arg = $userdata;
            }
            else
            {
                $_POST = json_decode(file_get_contents("php://input"), true);
                if($_POST)
                {
                    $this->form_validation->set_rules('instructor_id','Instructor Id', 'required|numeric', array(
                        'required' => 'Instructor is required',
                        'numeric' => 'Instructor is required',
                    ));
                    $this->form_validation->set_rules('service_id','Service Id', 'required|numeric', array(
                        'required' => 'Service is required',
                        'numeric' => 'Service is required',
                    ));

                    if($this->form_validation->run() == FALSE)
                    {
                        $arg['status']  = 0;
                        $arg['error_code'] = 0;
                        $arg['error_line']= __line__;
                        $arg['message'] = get_form_error($this->form_validation->error_array());
                    } else {
                        $user_id        =   $userdata['data']['id'];
                        $business_id    =  $this->input->post('business_id');
                        $instructor_id  =  $this->input->post('instructor_id');
                        $service_id     =  $this->input->post('service_id');

                        $checkStatus = $this->dynamic_model->updateRowWhere('service_instructor',
                            array(
                                'service_id'    =>  $service_id,
                                'instructor_id' =>  $instructor_id
                            ),
                            array(
                                'status'    =>  'Deactive'
                            )
                        );
                        if ($checkStatus) {
                            $imgePath = base_url().'uploads/user/';
                            $collection = $this->dynamic_model->getQueryResultArray("SELECT user.id, user.name, user.lastname, concat('".$imgePath."', user.profile_img) as profile, user.profile_img, user.availability_status, instructor_details.total_experience as experience, instructor_details.appointment_fees, instructor_details.appointment_fees_type, instructor_details.skill FROM service_instructor JOIN user on (user.id = service_instructor.instructor_id) JOIN instructor_details on (instructor_details.user_id = user.id) where service_instructor.status = 'Active' AND service_instructor.service_id = ".$service_id);
                            array_walk ( $collection, function (&$keys) {
                                $keys["instructor_ids"] = encode($keys['id']);
                            });

                            $arg['status']      =   1;
                            $arg['error_code']  =   REST_Controller::HTTP_OK;
                            $arg['error_line']  =   __line__;
                            $arg['message']     =   'Instructor remove successfully';
                            $arg['collection']  =   $collection;

                        } else {
                            $arg['status']     	= 0;
                            $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
                            $arg['error_line']	= __line__;
                            $arg['message']    	= 'Invalid Request';
                        }
                    }
                }
            }
        }
        echo json_encode($arg);
    }

	/****************Function class schedule calendar **********************************
     * @type            : Function
     * @Author          : Aamir
     * @function name   : calendar
     * @description     : class schedule information
     * @param           : start date, end date
     * @return          : array data
     * ********************************************************** */

	public function get_class_schedule_calendar() {
		$arg = array();
		$userdata = web_checkuserid();
		if($userdata['status'] != 1){
			$arg = $userdata;
		} else {
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
				$this->form_validation->set_rules('start', 'Start Date', 'required',array(
					'required' => 'Start Date is required'
				));
				$this->form_validation->set_rules('end', 'End Date', 'required',array(
					'required' => 'End Date is required'
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
					$response=array();
					$page_no= (!empty($this->input->post('page'))) ? $this->input->post('page') : "1";
					$page_no= $page_no-1;
					$limit    = config_item('page_data_limit');
					$offset = $limit * $page_no;
					$business_id= decode($userdata['data']['business_id']);
					$from_date=  $this->input->post('start');
					$to_date=  $this->input->post('end');

					$class = array();
					if ($this->input->post('class')) {
						$class = $this->input->post('class');
					}

					$room = array();
					if ($this->input->post('location')) {
						$room = $this->input->post('location');
					}

					$instructor = array();
					if ($this->input->post('instructor')) {
						$instructor = $this->input->post('instructor');
					}

					$class_data = $this->studio_model->get_calendar_scheduled_class_list($business_id, $from_date, $to_date, $class, $room, $instructor);

					if(!empty($class_data)){
						foreach($class_data as $value)
						{
							$from_time=(!empty($value['from_time']))? $value['from_time'] : "";
							$to_time=(!empty($value['to_time']))? $value['to_time'] : "";
							$start_date=(!empty($value['start_date']))? $value['start_date'] : "";
							$end_date=(!empty($value['end_date']))? $value['end_date'] : "";
							$classesdata['class_id']     = encode($value['id']);
							$classesdata['class_name']   = $value['class_name'];
							$classesdata['duration']     = $value['duration'];
							$classesdata['capacity']     = $value['location_capacity'];
							$classesdata['location']     = $value['location_name'];
							$classesdata['location_url']     = $value['location_url'];
							$classesdata['from_time']    =  ($from_time)? $from_time:'';
							$classesdata['to_time']      =  ($to_time) ? $to_time:'';
							$classesdata['from_time_utc']= $from_time;
							$classesdata['to_time_utc']  = $to_time;
							$classesdata['class_type']   = get_categories($value['class_type']);


							$classesdata['start_date']    =  ($start_date !=='') ? date("M d Y",strtotime($start_date)) :'';
							$classesdata['end_date']    =  ($end_date !=='') ? date("M d Y",strtotime($end_date)) :'';
							$classesdata['start_date_utc']= $start_date;
							$classesdata['scheduled_date']= $value['scheduled_date'];
							$classesdata['schedule_id']= encode($value['schedule_id']);
							$classesdata['end_date_utc']= $end_date;
							$classesdata['create_dt']    = date("M d Y",$value['create_dt']);
							$classesdata['create_dt_utc'] = $value['create_dt'];
							$classesdata['class_status'] = $value['status'];
							$classesdata['scheduling_status'] = $value['scheduling_status'];
							$classesdata['class_repeat_times'] = $value['class_repeat_times'];
							$attendence = $this->studio_model->get_class_attendence_count($business_id,$value['id'],$value['scheduled_date'],$value['schedule_id']);
							//print_r($attendence); die;
							$classesdata['attendence']   = count($attendence);
							$classesdata['class_days_prior_signup'] = $value['class_days_prior_signup'];
							$classesdata['instructor_name'] = $value['name'].' '.$value['lastname'];
							$classesdata['class_waitlist_overflow'] = $value['class_waitlist_overflow'];
							$response[]	                 = $classesdata;
						}
						$arg['status']     = 1;
						$arg['error_code']  = HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = $response;
						if ($this->input->post('location')) {
							$const = $this->input->post('location');
							$arg['location']       = decode($const[0]);
						}
						$arg['message']    = $this->lang->line('record_found');
					}else{
						$arg['status']     = 0;
						$arg['error_code']  = HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('record_not_found');
					}
				}
			}
		}
		echo json_encode($arg);
	}

	/****************Function workshop schedule calendar **********************************
     * @type            : Function
     * @Author          : Aamir
     * @function name   : calendar
     * @description     : workshop schedule information
     * @param           : start date, end date
     * @return          : array data
     * ********************************************************** */

	public function get_workshop_schedule_calendar() {
		$arg = array();
		$userdata = web_checkuserid();
		if($userdata['status'] != 1){
			$arg = $userdata;
		} else {
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
				$this->form_validation->set_rules('start', 'Start Date', 'required',array(
					'required' => 'Start Date is required'
				));
				$this->form_validation->set_rules('end', 'End Date', 'required',array(
					'required' => 'End Date is required'
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
					$response=array();
					$business_id= decode($userdata['data']['business_id']);
					$start_date = $this->input->post('start');
					$end_date = $this->input->post('end');

					$where = 'business_workshop_master.business_id='.$business_id.' AND business_workshop_schdule.schedule_dates BETWEEN "'.$start_date.'" AND "'.$end_date.'" ';

					$query = "SELECT business_workshop_schdule.id, business_workshop_master.description, business_workshop_master.tax1, business_workshop_master.tax2, business_workshop_master.tax1_rate, business_workshop_master.tax2_rate, business_workshop_master.id as workshop_id, business_workshop_schdule.waiting_status, business_workshop_schdule.waiting_count, business_workshop_master.name, business_workshop_master.visibility, business_workshop_master.price, business_workshop_master.workshop_capacity as capacity, business_workshop_schdule.schedule_date, business_workshop_schdule.location, business_workshop_schdule.status as status, business_workshop_schdule.start, business_workshop_schdule.end, CASE WHEN business_workshop_schdule.location = 0 THEN 'Other' ELSE business_location.location_name END location_name, business_workshop_schdule.address, (CASE WHEN business_location.map_url IS NULL THEN '' Else business_location.map_url END) as location_url, CASE WHEN business_location.location_url IS NULL THEN '' Else business_location.location_url END as web_link FROM `business_workshop_schdule` JOIN business_workshop_master on (business_workshop_master.id = business_workshop_schdule.workshop_id) LEFT JOIN business_location on (business_location.id = business_workshop_schdule.location)  WHERE ".$where." ORDER BY business_workshop_schdule.schedule_dates DESC";

					$collection = $this->db->query($query)->result_array();

					$response = array();
					if (!empty($collection)) {

						foreach($collection as $value) {
							$imageUrl = site_url() . 'uploads/user/';
							$scheduleId = $value['id'];
							$workshopId = $value['workshop_id'];
							$query = 'SELECT user.id, user.name, user.lastname, concat("'.$imageUrl.'", user.profile_img) as profile_img FROM `business_workshop_schdule_instructor` JOIN user on (user.id = business_workshop_schdule_instructor.user_id) where business_workshop_schdule_instructor.schedule_id = '.$scheduleId;
							$used = $this->db->get_where('user_booking', array(
								//'class_id' => $scheduleId,
								'service_id' => $workshopId,
								'service_type' => '4',
								'status' => 'Success'
							))->num_rows();
							$value['used'] = $used;
							$value['customer_detail'] = $this->db->query("SELECT user.name, concat('".$imageUrl."', user.profile_img) as profile_img, user.lastname, user.gender, DATE_FORMAT(FROM_DAYS(DATEDIFF(CURDATE(), user.date_of_birth)), '%Y')+0 AS age, user_booking.status FROM `user_booking` JOIN user on (user.id = user_booking.user_id) WHERE user_booking.service_type = 4 ANd user_booking.business_id = ".$business_id." AND user_booking.service_id = ".$workshopId)->result_array();
							$value['instructorDetails'] = $this->db->query($query)->result_array();
							array_push($response, $value);
						}
					}

					if (!empty($response)) {

						array_walk ( $response, function (&$keys) {

							$resp = $keys['instructorDetails'];

							if (!empty($resp)) {
								$firstInstructor = $resp[0];
								$keys['instructor_name'] = $firstInstructor['name'].' '.$firstInstructor['lastname'];

								$keys['instructor_lastname'] = '';
								$keys['instructor_image'] = $firstInstructor['profile_img'];
							}

						});

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
			}
		}
		echo json_encode($arg);
	}

	/****************Function service schedule calendar **********************************
     * @type            : Function
     * @Author          : Aamir
     * @function name   : calendar
     * @description     : service schedule information
     * @param           : start date, end date
     * @return          : array data
     * ********************************************************** */
	public function get_service_schedule_calendar() {
		$arg = array();
		$userdata = web_checkuserid();
		if($userdata['status'] != 1){
			$arg = $userdata;
		} else {
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
				$this->form_validation->set_rules('start', 'Start Date', 'required',array(
					'required' => 'Start Date is required'
				));
				$this->form_validation->set_rules('end', 'End Date', 'required',array(
					'required' => 'End Date is required'
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

					$response=array();
					$business_id= decode($userdata['data']['business_id']);
					$start_date = $this->input->post('start');
					$end_date = $this->input->post('end');

					$imgePath = base_url().'uploads/user/';

					$query = "SELECT t.create_dt, IFNULL(concat(uf.name, '', uf.lastname),'') as family_member_name, IFNULL(uf.gender,'') as family_gender, IFNULL(uf.date_of_birth,'') as family_dob,(CASE WHEN uf.profile_img != '' THEN CONCAT('".$imgePath."',uf.profile_img) ELSE '' END ) as family_profile_img, b.family_user_id,s.id as service_id, t.id as transaction_id,t.user_id,t.amount,t.discount,t.trx_id,t.order_number,t.create_dt as payment_date,t.payment_type,t.payment_method,t.responce_all,u.name,u.lastname, u.gender, u.date_of_birth,concat('".$imgePath."', u.profile_img) as profile_img, s.service_name,s.duration,s.cancel_policy,s.description, s.tax1, s.tax2, s.tax1_rate, s.tax2_rate,bs.business_name,bs.address,bs.location_detail,uu.name as instructor_name,uu.lastname as instructor_lastname,concat('".$imgePath."', uu.profile_img) as instructor_profile_img, b.status as booking_status,b.passes_start_date as start_time, b.passes_end_date as end_time, b.shift_date, b.tip_comment, bl.location_name, (CASE WHEN bl.is_address_same = 'Yes' THEN (SELECT business.address FROM business WHERE business.id = bl.business_id) ELSE bl.address END) AS location_address, (CASE WHEN bl.map_url IS NULL THEN '' Else bl.map_url END) as location_url, CASE WHEN bl.location_url IS NULL THEN '' Else bl.location_url END as web_link, s.tip_option  FROM transactions AS t join user_booking as b on t.id = b.transaction_id JOIN user as u on u.id = b.user_id join service as s on s.id = b.service_id JOIN business as bs on bs.id = b.business_id JOIN user as uu on uu.id = b.shift_instructor LEFT Join business_shift as bshift on bshift.id = b.shift_id left join business_location as bl on bl.id = bshift.location_id left join user as uf on uf.id = b.family_user_id WHERE b.business_id = ".$business_id." AND b.service_type = '2' AND from_unixtime(b.passes_start_date, '%Y-%m-%d') BETWEEN '".$start_date."' AND '".$end_date."'";

					if ($this->input->post('instructor')) {
						$instructor = $this->input->post('instructor');
						$query .= " AND b.shift_instructor = ".decode($instructor[0]);
					}

					if ($this->input->post('service')) {
						$service = $this->input->post('service');
						$query .= " AND s.id = ".$service[0];
					}

					$query .= " ORDER BY b.create_dt desc";
                    $collection = $this->db->query($query)->result_array();

					if (!empty($collection)) {

						array_walk ( $collection, function (&$keys) {
							$keys['instructor_name'] = $keys['instructor_name'].' '.$keys['instructor_lastname'];

							$keys['instructor_lastname'] = '';
							$keys['instructor_image'] = $keys['instructor_profile_img'];

						});

						$arg['status']     = 1;
						$arg['error_code']  = HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = $collection;
						$arg['message']    = $this->lang->line('record_found');
					} else {
						$arg['status']     = 0;
						$arg['error_code']  = HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('record_not_found');
					}

				}
			}
		}

		echo json_encode($arg);
	}

	public function service_appointment_cancel() {
		$arg = array();
		$userdata = web_checkuserid();
		if($userdata['status'] != 1){
			$arg = $userdata;
		} else {
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
				$this->form_validation->set_rules('transaction_id', 'Transaction Id', 'required',array(
					'required' => 'Transaction Id is required'
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

					$response=array();
					$business_id= decode($userdata['data']['business_id']);
					$transaction_id = $this->input->post('transaction_id');

					$booking_status = $this->dynamic_model->updateRowWhere('user_booking', array('transaction_id' => $transaction_id), array('status' => 'Cancel'));

					if($booking_status)
					{
						$getRecord = $this->dynamic_model->getdatafromtable('user_booking', array('transaction_id' => $transaction_id), 'service_id, shift_schedule_id, user_id');
						cancelUserAppointment($getRecord[0]['user_id'], $business_id, $getRecord[0]['service_id'], $getRecord[0]['shift_schedule_id']);
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

		echo json_encode($arg);
	}

	public function send_appointment_mail() {
		$arg = array();
		$userdata = web_checkuserid();
		if($userdata['status'] != 1){
			$arg = $userdata;
		} else {
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
				$this->form_validation->set_rules('transaction_id', 'Transaction Id', 'required',array(
					'required' => 'Transaction Id is required'
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

					/* $to = 'rahul.rao@consagous.co';
					$cc = 'rahul.rao@consagous.co'; */
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

		echo json_encode($arg);
	}

	/****************Function shift schedule calendar **********************************
     * @type            : Function
     * @Author          : Aamir
     * @function name   : calendar
     * @description     : shift schedule information
     * @param           : start date, end date, instructor
     * @return          : array data
     * ********************************************************** */
	public function get_shift_schedule_calendar() {
		$arg = array();
		$userdata = web_checkuserid();
		if($userdata['status'] != 1){
			$arg = $userdata;
		} else {
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
				$this->form_validation->set_rules('start', 'Start Date', 'required',array(
					'required' => 'Start Date is required'
				));
				$this->form_validation->set_rules('end', 'End Date', 'required',array(
					'required' => 'End Date is required'
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

					$response=array();
					$business_id= decode($userdata['data']['business_id']);
					$start_date = $this->input->post('start');
					$end_date = $this->input->post('end');

					$query = "SELECT bss.*, bsi.instructor, concat(user.name, ' ', user.lastname) as instructor_name, bl.location_name FROM business_shift_scheduling as bss JOIN business_shift as bs on (bs.id = bss.shift_id) JOIN business_shift_instructor as bsi on (bsi.shift_id = bss.shift_id) JOIN user on (user.id = bsi.instructor) left join business_location as bl on bl.id = bs.location_id WHERE bss.status = 1 AND bs.business_id = ".$business_id." AND bss.shift_date_str BETWEEN '".$start_date."' AND '".$end_date."' ";

					if ($this->input->post('instructor')) {
						$instructor = $this->input->post('instructor');
						$query .= " AND bsi.instructor = ".decode($instructor[0]);
					}

					if ($this->input->post('location')) {
						$location = $this->input->post('location');
						$query .= " AND bs.location_id = ".decode($location[0]);
					}

					$query .= " ORDER BY bss.shift_date_str asc";
                    $collection = $this->db->query($query)->result_array();

					if (!empty($collection)) {

						$arg['status']     = 1;
						$arg['error_code']  = HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = $collection;
						$arg['message']    = $this->lang->line('record_found');
					} else {
						$arg['status']     = 0;
						$arg['error_code']  = HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('record_not_found');
					}

				}
			}
		}

		echo json_encode($arg);
	}

	/* waiver update */
	public function waiver_customer() {
		$arg = array();
		$userdata = web_checkuserid();
		if($userdata['status'] != 1){
			$arg = $userdata;
		} else {
			$_POST = json_decode(file_get_contents("php://input"), true);
			if($_POST)
			{
				$this->form_validation->set_rules('cust_id', 'Customer Id', 'required',array(
					'required' => 'Customer id is required'
				));
				$this->form_validation->set_rules('cust_status', 'Waiver Status', 'required',array(
					'required' => 'Waiver is required'
				));
				if($this->form_validation->run() == FALSE)
				{
					$arg['status']  = 0;
					$arg['error_code'] = 0;
					$arg['error_line']= __line__;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {

					$response=array();
					$time = time();
					$business_id= decode($userdata['data']['business_id']);
					$cust_id = $this->input->post('cust_id');
					$cust_status = $this->input->post('cust_status');

					$where = array('business_id' => $business_id, 'user_id' => $cust_id);
					$data = array(
						'business_id' 	=> $business_id,
						'user_id' 		=> $cust_id,
						'isWaiver' 		=> $cust_status,
						'update_dt'		=>	$time
					);
					$row = $this->db->get_where('business_waiver', $where);
					if ($row->num_rows() > 0) {

						$this->db->update('business_waiver', $data, $where);

					} else {
						// Insert
						$data['create_dt'] = $time;
						$this->db->insert('business_waiver', $data);
					}

					$arg['status']     = 1;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['message']    = 'Waiver update successfully.';

					// if (!empty($collection)) {

					// 	$arg['status']     = 1;
					// 	$arg['error_code']  = HTTP_OK;
					// 	$arg['error_line']= __line__;
					// 	$arg['data']       = $collection;
					// 	$arg['message']    = $this->lang->line('record_found');
					// } else {
					// 	$arg['status']     = 0;
					// 	$arg['error_code']  = HTTP_OK;
					// 	$arg['error_line']= __line__;
					// 	$arg['data']       = array();
					// 	$arg['message']    = $this->lang->line('record_not_found');
					// }

				}
			}
		}

		echo json_encode($arg);
	}

	public function get_relations() {
		$arg = array();
		$userdata = web_checkuserid();
		if($userdata['status'] != 1){
			$arg = $userdata;
		} else {
			$response = array();
			$relations_data = $this->dynamic_model->getdatafromtable("manage_relations", array("status" => "Active"));
			if (!empty($relations_data)) {
				foreach ($relations_data as $value) {
					$relation_data['relative_id'] = $value['id'];
					$relation_data['realtion_name'] = ucwords($value['name']);
					$response[] = $relation_data;
				}
				$arg['status'] = 1;
				$arg['error_code'] = REST_Controller::HTTP_OK;
				$arg['error_line'] = __line__;
				$arg['data'] = $response;
				$arg['message'] = $this->lang->line('record_found');
			} else {
				$arg['status'] = 0;
				$arg['error_code'] = REST_Controller::HTTP_OK;
				$arg['error_line'] = __line__;
				$arg['data'] = array();
				$arg['message'] = $this->lang->line('record_not_found');
			}
		}

		echo json_encode($arg);
	}
	// Add family member
	public function add_member() {
		$arg = array();
		$userdata = web_checkuserid();

		if($userdata['status'] != 1){
			$arg = $userdata;
		} else {

			$response = array();

			if ($_POST) {
				$version_result = version_check_helper1();
				if ($version_result['status'] != 1) {
					$arg = $version_result;
				} else {
					$this->form_validation->set_rules('fullname', 'Full Name', 'required|trim', array('required' => $this->lang->line('full_name')));
					$this->form_validation->set_rules('gender', 'Gender', 'required|trim');
					$this->form_validation->set_rules('dob', 'Date of birth', 'required|trim', array('required' => $this->lang->line('dob_required')));
					$this->form_validation->set_rules('relative_id', 'Relative Id', 'required|trim', array('required' => $this->lang->line('relative_id_req')));
					$this->form_validation->set_rules('user_id', 'Customer', 'required|trim', array('required' => 'Customer is required'));

					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$time = time();
						$role = 3;
						$usid = decode($this->input->post('user_id'));
						$data = getuserdetail($usid,$role,$usid);

						$country = $userdata['data']['country'];
						$country_code = $userdata['data']['country_code'];
						$state = $userdata['data']['state'];
						$city = $userdata['data']['city'];
						$zipcode = $userdata['data']['zipcode'];
						$address = $userdata['data']['address'];
						$lat = $userdata['data']['lat'];
						$lang = $userdata['data']['lang'];

						$email = ''; //$this->input->post('email');
						$fullname = $this->input->post('fullname');
						$dob = $this->input->post('dob');
						$gender = $this->input->post('gender');
						$relative_id = $this->input->post('relative_id');
						$relation_id = $this->input->post('relation_id');

						$default_img = $fullname ? $fullname : 'u';
						$default_img = strtolower(substr($default_img, 0, 1));
						$image = $default_img . '.png';

						if (!empty($_FILES['image']['name'])) {
							$image = $this->dynamic_model->fileupload('image', 'uploads/user');
						}

						$this->load->library('user_agent');
						$mobile_otp = '0';
						$email_verified = '0';
						if ($this->agent->is_browser()) {
							$mobile_otp = '1';
						} else {
							$email_verified = '1';
						}

						$notification = '{"app_notification":"1","alerts":"1","email":"1","sms":"1","phonecall":"1"}';
						$userdata = array(
							"relation_id" => $relative_id,
							"name" => $fullname,
							"profile_img" => $image,
							"date_of_birth" => $dob,
							"email" => $email,
							"gender" => $gender,
							"created_by" => $usid,
							'notification' => $notification,
							'status' => 'Deactive',
							'email_verified' => $email_verified,
							'mobile_verified' => $mobile_otp,
							"singup_for" => "family",
							"create_dt" => $time,
							"update_dt" => $time,
							'zipcode' => $zipcode,
							'country' => $country,
							'country_code' => $country_code,
							'state' => $state,
							'city' => $city,
							'address' => $address,
							'lat' => $lat,
							'lang' => $lang,
						);
						$newuserid = $this->dynamic_model->insertdata('user', $userdata);

						if ($newuserid) {
							$roledata = array(
								'user_id' => $newuserid,
								'role_id' => $role,
								'create_dt' => $time,
								'update_dt' => $time,
							);
							$roleid = $this->dynamic_model->insertdata('user_role', $roledata);

							$user_id = $this->input->get_request_header('userid', true);
							$parentId = $this->input->get_request_header('parentId', true);
							$gfm = get_family_member($parentId, $user_id);
							//$st = array('family_member'=>$gfm);

							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('member_add');
							$arg['data'] = $gfm;
						} else {
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('server_problem');
							$arg['data'] = json_decode('{}');
						}

					}
				}
			}
		}

		echo json_encode($arg);
	}

	public function edit_member() {
		$arg = array();
		$userdata = web_checkuserid();

		if($userdata['status'] != 1){
			$arg = $userdata;
		} else {

			$response = array();

			if ($_POST) {
				$version_result = version_check_helper1();
				if ($version_result['status'] != 1) {
					$arg = $version_result;
				} else {
						$this->form_validation->set_rules('user_id', 'Customer', 'required|trim', array('required' => 'Customer is required'));
						$this->form_validation->set_rules('member_id', 'Member Id', 'required', array('required' => $this->lang->line('member_id_req')));
						$this->form_validation->set_rules('fullname', 'Full Name', 'required|trim', array('required' => $this->lang->line('full_name')));
						$this->form_validation->set_rules('relative_id', 'Relative Id', 'required|trim', array('required' => $this->lang->line('relative_id_req')));
						if ($this->form_validation->run() == FALSE) {
							$arg['status'] = 0;
							$arg['error_code'] = 0;
							$arg['error_line'] = __line__;
							$arg['message'] = get_form_error($this->form_validation->error_array());
						} else {
							$time = time();
							$member_id = $this->input->post('member_id');
							$fullname = $this->input->post('fullname');
							$dob = $this->input->post('dob');
							$relative_id = $this->input->post('relative_id');
							$image = 'userdefault.png';

							$default_img = $fullname ? $fullname : 'u';
							$default_img = strtolower(substr($default_img, 0, 1));
							$image = $default_img . '.png';

							if (!empty($_FILES['image']['name'])) {
								$image = $this->dynamic_model->fileupload('image', 'uploads/user');
								$updatedata['profile_img'] = $image;
							}
							if (!empty($relative_id)) {
								$updatedata['relation_id'] = $relative_id;
							}
							if (!empty($fullname)) {
								$updatedata['name'] = $fullname;
							}
							if (!empty($dob)) {
								$updatedata['date_of_birth'] = $dob;
							}
							$where = array("id" => $member_id);
							$updatedata['update_dt'] = $time;

							$relative_id = $this->dynamic_model->updateRowWhere('user', $where, $updatedata);

							if ($relative_id) {
								$arg['status'] = 1;
								$arg['error_code'] = REST_Controller::HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('member_update');
								$arg['data'] = json_decode('{}');
							} else {
								$arg['status'] = 1;
								$arg['error_code'] = REST_Controller::HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('server_problem');
								$arg['data'] = json_decode('{}');
							}

						}
				}

			}
		}
		echo json_encode($arg);
	}

	public function remove_member() {
		$arg = array();
		$userdata = web_checkuserid();

		if($userdata['status'] != 1){
			$arg = $userdata;
		} else {

			$response = array();
			$_POST = json_decode(file_get_contents("php://input"), true);
			if ($_POST) {
				$version_result = version_check_helper1();
				if ($version_result['status'] != 1) {
					$arg = $version_result;
				} else {
					$userdata = checkuserid();
					if ($userdata['status'] != 1) {
						$arg = $userdata;
					} else {
						// $this->form_validation->set_rules('user_id', 'Customer', 'required|trim', array('required' => 'Customer is required'));
						$this->form_validation->set_rules('member_id', 'Member Id', 'required', array('required' => $this->lang->line('member_id_req')));

						if ($this->form_validation->run() == FALSE) {
							$arg['status'] = 0;
							$arg['error_code'] = 0;
							$arg['error_line'] = __line__;
							$arg['message'] = get_form_error($this->form_validation->error_array());
						} else {
							$time = time();
							//$usid =$userdata['data']['id'];
							$member_id = $this->input->post('member_id');
							$where = array("id" => $member_id);
							$updatedata['is_deleted'] = '1';
							$updatedata['update_dt'] = $time;

							$relative_id = $this->dynamic_model->updateRowWhere('user_family_details', $where, $updatedata);
							if ($relative_id) {
								$arg['status'] = 1;
								$arg['error_code'] = REST_Controller::HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('member_remove');
								$arg['data'] = json_decode('{}');
							} else {
								$arg['status'] = 1;
								$arg['error_code'] = REST_Controller::HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('server_problem');
								$arg['data'] = json_decode('{}');
							}

						}

					}
				}

			}
		}
		echo json_encode($arg);
	}

	private function attendanceCount($business_id, $user_id, $type = 1) {
		// 1 - Class 2 -Workshop, 3 - Appointment
		$currentStartDate = date('Y-m-d', strtotime('today - 30 days'));
		$currentEndDate = date('Y-m-d');
		$previousStartDate = date('Y-m-d', strtotime('today - 60 days'));

		if ($type == 1) {
			$query_both = "SELECT count(*) as total_count FROM user_attendance as ua JOIN business_class as bc on (bc.id = ua.service_id) WHERE  (ua.status = 'checkin' OR ua.status = 'singup') AND bc.business_id = " . $business_id . " AND ua.user_id = " . $user_id . " AND ua.checkin_dt BETWEEN ";

			$query_checkin = "SELECT count(*) as total_count FROM user_attendance as ua JOIN business_class as bc on (bc.id = ua.service_id) WHERE  ua.status = 'checkin' AND bc.business_id = " . $business_id . " AND ua.user_id = " . $user_id . " AND ua.checkin_dt BETWEEN ";

			$current_month_merge = $query_both . " '" . $currentStartDate . "' AND '" . $currentEndDate . "' UNION " . $query_checkin . " '" . $currentStartDate . "' AND '" . $currentEndDate . "' ";

			$previous_month_merge = $query_both . " '" . $previousStartDate . "' AND '" . $currentStartDate . "' UNION " . $query_checkin . " '" . $previousStartDate . "' AND '" . $currentStartDate . "' ";

			$getCurrentCount = $this->dynamic_model->getQueryResultArray($current_month_merge);
			$getPreviousCount = $this->dynamic_model->getQueryResultArray($previous_month_merge);

			$counterCurrent = count($getCurrentCount);
			if ($counterCurrent == 0) {
				$current_total_count = 0;
				$current_avl_count = 0;
			} else if ($counterCurrent == 1) {
				$current_total_count = $getCurrentCount[0]['total_count'];
				$current_avl_count = 0;
			} else {
				$current_total_count = $getCurrentCount[0]['total_count'];
				$current_avl_count = $getCurrentCount[1]['total_count'];
			}

			$counterPrevious = count($getPreviousCount);
			if ($counterPrevious == 0) {
				$previous_total_count = 0;
				$previous_avl_count = 0;
			} else if ($counterPrevious == 1) {
				$previous_total_count = $getPreviousCount[0]['total_count'];
				$previous_avl_count = 0;
			} else {
				$previous_total_count = $getPreviousCount[0]['total_count'];
				$previous_avl_count = $getPreviousCount[1]['total_count'];
			}

			$current_month_percentage = ($current_avl_count == 0) ? 0 : (($current_avl_count / $current_total_count) * 100);
			$previous_month_percentage = ($previous_avl_count == 0) ? 0 : (($previous_avl_count / $previous_total_count) * 100);

			$attendance_status = 1;

			if ($previous_month_percentage == 0 && $current_month_percentage == 0) {

				$percentage = 0;

			} else if ($previous_month_percentage == 0) {

				$percentage = $current_month_percentage;

			} else if ($current_month_percentage == 0) {

				$percentage = $previous_month_percentage;
				$attendance_status = 0;

			} else if ($current_month_percentage > $previous_month_percentage) {

				$percentage = $current_month_percentage - $previous_month_percentage;

			} else if ($previous_month_percentage > $current_month_percentage) {

				$percentage = $previous_month_percentage - $current_month_percentage;
				$attendance_status = 0;

			}
		} else {
			$attendance_status = 0;
			$percentage = 0;
			$current_avl_count = 0;
			$current_total_count = 0;
			$previous_avl_count = 0;
			$previous_total_count = 0;
		}

		return array(
			'description' => ($attendance_status) ? '+' . number_format($percentage, 2) . '%' : '-' . number_format($percentage, 2) . '%',
			'attendance_status' => $attendance_status,
			'current_month' => $current_avl_count . ' / ' . $current_total_count . ' Classes (' . date_format(date_create($currentStartDate), 'M d Y') . ' - ' . date_format(date_create($currentEndDate), 'M d Y') . ')',
			'previous_month' => $previous_avl_count . ' / ' . $previous_total_count . ' Classes (' . date_format(date_create($previousStartDate), 'M d Y') . ' - ' . date_format(date_create($currentStartDate), 'M d Y') . ')',
		);
	}

	public function get_customer_profile() {
		$arg = array();
		$userdata = web_checkuserid();

		$userdata = version_check_helper1();
		if ($userdata['status'] != 1) {
			$arg = $userdata;
		} else {
			$_POST = json_decode(file_get_contents("php://input"), true);
			if ($_POST) {
				$version_result = version_check_helper1();
				if ($version_result['status'] != 1) {
					$arg = $version_result;
				} else {
					$this->form_validation->set_rules('user_id', 'Customer Id', 'required', array('required' => 'Customer id is required'));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$userid = $this->input->post('user_id');
						$role = 3;
						$userdata = $instructordata = array();
						$data = getuserdetail($userid, $role, $userid);

						$arg['status'] = 1;
						$arg['error_code'] = REST_Controller::HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['data'] = $data;
						$arg['message'] = $this->lang->line('profile_details');
					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function customer_profile_update() {
		$arg = array();
		$userdata = web_checkuserid();
		if ($userdata['status'] != 1) {
			$arg = $userdata;
		} else {
			if ($_POST) {
				$version_result = version_check_helper1();
				if ($version_result['status'] != 1) {
					$arg = $version_result;
				} else {
					$this->form_validation->set_rules('user_id', 'Customer Id', 'required', array('required' => 'Customer id is required'));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$userid = $this->input->post('user_id');
						$role_id = 3;
						$userdata = $instructordata = array();

						if (!empty($this->input->post('name'))) {
							$userdata['name'] = $this->input->post('name');
						}

						if (!empty($this->input->post('lastname'))) {
							$userdata['lastname'] = $this->input->post('lastname');
						}

						if ($this->input->post('date_of_birth')) {
							$userdata['date_of_birth'] = $this->input->post('date_of_birth');
						}

						if (!empty($this->input->post('address'))) {
							$userdata['address'] = $this->input->post('address');
						}

						if (!empty($this->input->post('city'))) {
							$userdata['city'] = $this->input->post('city');
						}

						if (!empty($this->input->post('state'))) {
							$userdata['state'] = $this->input->post('state');
						}

						if (!empty($this->input->post('country'))) {
							$userdata['country'] = $this->input->post('country');
						}
						if (!empty($this->input->post('country_code'))) {
							$userdata['country_code'] = $this->input->post('country_code');
						}
						if (!empty($this->input->post('lang'))) {
							$userdata['lat'] = $this->input->post('lat');
						}

						if (!empty($this->input->post('lang'))) {
							$userdata['lang'] = $this->input->post('lang');
						}

						if (!empty($this->input->post('gender'))) {
							$userdata['gender'] = $this->input->post('gender');
						}
						if (!empty($this->input->post('street'))) {
							$userdata['location'] = $this->input->post('street');
						}
						if (!empty($this->input->post('emergency_contact_person'))) {
							$userdata['emergency_contact_person'] = $this->input->post('emergency_contact_person');
						}

						if (!empty($this->input->post('emergency_contact_no'))) {
							$userdata['emergency_contact_no'] = $this->input->post('emergency_contact_no');
						}
						if (!empty($this->input->post('emergency_country_code'))) {
							$userdata['emergency_country_code'] = $this->input->post('emergency_country_code');
						}

						if (!empty($_FILES['image']['name'])) {
							$profile_image = $this->dynamic_model->fileupload('image', 'uploads/user');
							$userdata['profile_img'] = $profile_image;
						}

						$userdata['update_dt'] = time();
						$where = array('id' => $userid);
						$updatedata = $this->dynamic_model->updateRowWhere("user", $where, $userdata);

						if ($updatedata) {
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('profile_update');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_MODIFIED;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('profile_notupdate');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}

	/* 11-04-2021 */
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

	public function clover_pay_checkout_single() {
		$arg    = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		} else {
			$_POST = json_decode(file_get_contents("php://input"), true);
			if ($_POST) {

				$this->form_validation->set_rules('client_id', 'Client Id', 'required|numeric');
				$this->form_validation->set_rules('data[]', 'Item Information', 'required');

				if($this->form_validation->run() == FALSE)
				{
					$arg['status']  = 0;
					$arg['error_code'] = 0;
					$arg['error_line']= __line__;
					$arg['message'] = get_form_error($this->form_validation->error_array());

				} else {

					$business_id = 0;

					$usid =	$this->input->post('client_id');
					$client_id = $this->input->post('client_id');
					$time = time();
					//$savecard= $this->input->post('savecard');
					//$card_id = $this->input->post('card_id');
					$data = $this->input->post('data');
					$loop_status = true;

					$amount = 0;
					if (count($data) > 0) {
						for($i = 0; $i < count($data); $i++) {
							$row = $data[$i];
							if (array_key_exists('service_id', $row) && array_key_exists('service_type', $row) && array_key_exists('business_id', $row) && array_key_exists('quantity', $row) && array_key_exists('amount', $row) && array_key_exists('tax', $row) && array_key_exists('discount', $row)) {

								$total_amount = ($row['amount'] + $row['tax']) - $row['discount'];
								$amount = $amount + $total_amount;
								$business_id = $row['business_id'];

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

							$amount = number_format((float)$amount, 2, '.', '');

                            $authorization    = $this->input->get_request_header('Authorization', true);
							if ($this->input->get_request_header('Authorization', true)) {
								$userdata = web_checkuserid();
								if ($userdata['status'] == 1) {
									$business_id 	= decode($userdata['data']['business_id']);
								} else {
									$arg['status']  = 0;
									$arg['error_code'] = 0;
									$arg['error_line']= __line__;
									$arg['message'] = 'Invalid user';
									echo json_encode($arg); exit;
								}
							}

							// if ($this->input->post('expiry_month')) {
							// 	$resp = $this->fetch_clover_payment_token($business_id, $this->input->post('country_code'), $this->input->post('number'), $this->input->post('expiry_month'), $this->input->post('expiry_year'), $this->input->post('cvd'));
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

							$token  = $this->input->post('token');
							if ($this->input->post('token')) {
								$token = $this->input->post('token');
							} else {
								$dat = $resp->data;
								$token = $dat->token;
							}

							//if (empty($token) && empty($card_id)) {
							if (empty($token)) {
								$arg['status']  = 0;
								$arg['error_code'] = 0;
								$arg['error_line']= __line__;
								$arg['message'] = 'Failed payment request';
								echo json_encode($arg);exit;
							}

						}

						//$business_id = $this->input->post('business_id');
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
						$amount       = $amount;
						$taxAmount    = 0;
						// $res  = clover_api_payment_checkout($user_cc_no,$user_cc_mo,$user_cc_yr,$user_cc_cvv,$user_zip,$amount,$taxAmount,$marchant_id,$clover_key,$access_token,$currency,$token);

						//echo $res['message'];die;
						// if(@$res->status == 'succeeded')
						if(true)
						{
								$where = array('user_id' => $usid,
									'business_id' => $business_id,
								);
								$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);

								$ref_num    = getuniquenumber();
								$payment_id = time(); //!empty($res->id) ? $res->id : $ref_num;
								$authorizing_merchant_id = time(); //$res->source->id;
								$payment_type   = 'Card';
								$payment_method = 'Online';
								$amount         = $amount;

								$transaction_data = array(
									'user_id'           =>	$usid,
									'amount'            =>	$amount,
									'trx_id'           =>	$payment_id,
									'order_number'     =>	$time,
									'transaction_type' =>	2,
									'payment_status'   =>	"Success",
									'saved_card_id'    =>	0,
									'create_dt'        =>	$time,
									'update_dt'        =>	$time,
									'authorizing_merchant_id' => $authorizing_merchant_id,
									'payment_type' => $payment_type,
									'payment_method' => $payment_method,
									'responce_all'=> '' //json_encode($res),
								);

								$transaction_id=$this->dynamic_model->insertdata('transactions',$transaction_data);

								$where=array("user_id"=>$usid, "business_id" => $business_id, "status"=>"Pending");
								$cart_data = $this->dynamic_model->getdatafromtable('user_booking',$where);

								if(!empty($cart_data)) {

									foreach($cart_data as $value) {

										$service_type = $value['service_type'];
										$service_id = $value['service_id'];

										if($value['service_type']=='1') {
											$where1 = array('id'=>$value['service_id'],'service_type'=>'1','status' => 'Active');
											$business_pass= $this->dynamic_model->getdatafromtable('business_passes',$where1);
											$pass_start_date    =   $time;


										$pass_validity = (!empty($business_pass[0]['pass_validity'])) ? $business_pass[0]['pass_validity'] : 0;

										if(!empty($business_pass)){
											$pass_type_subcat=$business_pass[0]['pass_type_subcat'];
											if(!empty($pass_type_subcat)){
											$where2 = array('id'=>$pass_type_subcat);
											$manage_pass= $this->dynamic_model->getdatafromtable('manage_pass_type',$where2);
												if(!empty($manage_pass)){
													$validity=$manage_pass[0]['pass_days'];
												}
											}
										}

											$getEndDate = ($pass_validity * 24 * 60 * 60) + $time;
											$pass_end_date= ($pass_validity == 0) ? $pass_start_date : $getEndDate;
											$pass_status = 1;
											$where2 = array("user_id"=>$usid,"status"=>"Pending","service_type"=>'1');
											$booking_data =   array(
												'transaction_id'        => $transaction_id,
												'status'                => "Success",
												'passes_start_date'     => $pass_start_date,
												'passes_end_date'       => $pass_end_date,
												'passes_status'         => $pass_status,
												'passes_total_count'    =>  $validity,
												'passes_remaining_count'  =>  $validity,
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
											$where2 = array("user_id"=>$usid,"status"=>"Pending","service_type!="=>'1');
											$booking_data =   array(
												'transaction_id'  => $transaction_id,
												'status'          => "Success",
												'update_dt'       => $time
											);
											$booking_id = $this->dynamic_model->updateRowWhere('user_booking',$where2,$booking_data);
										}
									}
								}

								$response  = array('amount' =>number_format((float)$amount, 2, '.', ''),'transaction_date'=>date('d M Y'));
								if($transaction_id) {
									$arg['status']    = 1;
									$arg['error_code'] = HTTP_OK;
									$arg['error_line']= __line__;
									$arg['message']   =$this->lang->line('payment_succ');
									$arg['data']      = $response;
								} else {
									$arg['status']    = 0;
									$arg['error_code'] = HTTP_NOT_FOUND;
									$arg['error_line']= __line__;
									$arg['message']   = $this->lang->line('payment_fail');
								}

						} else {
							$arg['status']    = 0;
							$arg['error_code'] = HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['message']   = '';// @$res->error->message;//$this->lang->line('payment_fail');
						}

					} else {
						$arg['status']  = 0;
						$arg['error_code'] = 0;
						$arg['error_line']= __line__;
						$arg['message'] = 'Invalid payment request';
					}
				}
			} else {
				$arg['status']  = 0;
				$arg['error_code'] =  ERROR_FAILED_CODE;
				$arg['error_line']= __line__;
				$arg['message'] = 'Invalid request';
				$arg['data']      =json_decode('{}');
			}
		}
		echo json_encode($arg);
	}


	public function is_recurring()
	{
		$arg    = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$_POST = json_decode(file_get_contents("php://input"), true);
			if ($_POST)
			{

				$this->form_validation->set_rules('user_id', 'User Id', 'required|numeric');
				$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric');
				$this->form_validation->set_rules('id', 'Id', 'required|numeric');
				$this->form_validation->set_rules('pass_id', 'Pass Id', 'required|numeric');
				$this->form_validation->set_rules('is_recurring', 'is recurring', 'required|numeric');
				//$is_recurring = 0 for start and 1 for stop recurring
				if($this->form_validation->run() == FALSE)
				{
					$arg['status']  = 0;
					$arg['error_code'] = 0;
					$arg['error_line']= __line__;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$business_id = $this->input->post('business_id');
					$user_id =	$this->input->post('user_id');
					$id = $this->input->post('id');
					$pass_id = $this->input->post('pass_id');
					$is_recurring = $this->input->post('is_recurring');

                    $authorization    = $this->input->get_request_header('Authorization', true);
					if ($this->input->get_request_header('Authorization', true)) {
						$userdata = web_checkuserid();
						if ($userdata['status'] == 1) {
							$business_id 	= decode($userdata['data']['business_id']);
						} else {
							$arg['status']  = 0;
							$arg['error_code'] = 0;
							$arg['error_line']= __line__;
							$arg['message'] = 'Invalid user';
							echo json_encode($arg); exit;
						}
					}

					$user_pass_booking_data =$this->dynamic_model->customQuery("select * from business_passes where id='".$id ."' and business_id='".$business_id."' and pass_id='".$pass_id."' ");


			        if(count($user_pass_booking_data)>0)
			        {
						if($is_recurring==0)//For Start Recurring
						{
							$recurring_data = array('is_recurring_stop'=>$is_recurring);
						}
						else if($is_recurring==1)//For Stop Recurring
						{
							$recurring_data = array('is_recurring_stop'=>$is_recurring);
						}

						$where = array("id"=>$id,"business_id"=>$business_id,"pass_id"=>$pass_id);

						$pass_recurring_data = $this->dynamic_model->updateRowWhere('business_passes',$where,$recurring_data);

						$arg['status']  = 1;
						$arg['error_code'] =  ERROR_FAILED_CODE;
						$arg['error_line']= __line__;
						$arg['message'] = 'Recurring status successfully updated';
						$arg['data']      =json_decode('{}');
					}
					else
					{
						$arg['status']  = 0;
						$arg['error_code'] =  ERROR_FAILED_CODE;
						$arg['error_line']= __line__;
						$arg['message'] = 'No Record Found';
						$arg['data']      =json_decode('{}');
					}

				}
			}
			else
			{
				$arg['status']  = 0;
				$arg['error_code'] =  ERROR_FAILED_CODE;
				$arg['error_line']= __line__;
				$arg['message'] = 'Invalid request';
				$arg['data']      =json_decode('{}');
			}
		}
		echo json_encode($arg);
	}



	public function get_video_list() {
		$arg    = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			
				
				$data_array_video = get_video_data();

				$arg['status']     = 1;
				$arg['error_code'] = REST_Controller::HTTP_OK;
				$arg['error_line'] = __line__;
				$arg['data']       = $data_array_video;
				$arg['message']    = $this->lang->line('record_found');
				
			
		}
		echo json_encode($arg);
	}




}
