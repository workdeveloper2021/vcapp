<?php defined('BASEPATH') OR exit('No direct script access allowed');
//require APPPATH . '/libraries/REST_Controller.php';

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
class Rstudio extends MX_Controller {

	public function __construct() { 
		header('Content-Type: application/json');
		// header('Access-Control-Allow-Origin: *');
		// header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, version, language");
        parent::__construct();
		$this->load->library('form_validation');
		//$this->load->library('session');
		$this->load->model('dynamic_model');
		$this->load->model('studio_model');
	    $this->load->helper('web_common_helper');
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
            $current_date = date('Y-m-d');
            $subscription_period = date('Y-m-d',strtotime($current_date. ' + 14 days'));
            $userdata = array('email'=>$email,'password'=>$hashed_password,'profile_img'=>$image,'email_verified'=>'0','mobile_verified'=>'0','mobile_otp'=>$uniquemobile,'mobile_otp_date'=>$time,'subscription_period'=>strtotime($subscription_period),'subscription_startdate'=>strtotime(date('Y-m-d')),'create_dt'=>$time,'update_dt'=>$time);
                $newuserid = $this->dynamic_model->insertdata('user',$userdata);
                if($newuserid){
                 $roledata = array(
                    'user_id'=>$newuserid,
                    'role_id'=>$role,
                    'create_dt'=>$time,
                    'update_dt'=>$time
                );
                $roleid = $this->dynamic_model->insertdata('user_role',$roledata);
               
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
                    $arg['message']   = $this->lang->line('thank_msg1');
                    $arg['data']      = $data_val;
                    }else{
                    $arg['status']    = 0;
                    $arg['error_code'] = HTTP_OK;
                    $arg['error_line']= __line__;
                    $arg['message']   = $this->lang->line('server_problem');
                    $arg['data']      = $data_val;
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
           $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[business.primary_email]' , array('required' => $this->lang->line('email_required'),'valid_email' => $this->lang->line('email_valid'),'is_unique' => $this->lang->line('email_unique')));
           $this->form_validation->set_rules('mobile', 'Mobile', 'required|min_length[8]|max_length[20]|numeric|is_unique[business.business_phone]', array(
                    'required' => $this->lang->line('mobile_required'),
                    'min_length' => $this->lang->line('mobile_min_length'),
                    'max_length' => $this->lang->line('mobile_max_length'),
                    'numeric' => $this->lang->line('mobile_numeric'),'is_unique' => $this->lang->line('mobile_unique')
                ));
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
                    echo json_encode($arg);exit();
                }
                $business_name   = $this->input->post('business_name');
                 $service_type_id = $this->input->post('service_type_id');
                 $services        = multiple_decode_ids($service_type_id);
                // $category_id     = $this->input->post('category_id');
                // $category_ids    = multiple_decode_ids($category_id);
                 $business_type    = decode($this->input->post('business_type_id'));
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
                                    'service_type'   =>$services,
                                    'business_type'  =>$business_type,
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
                    $business_id = $this->dynamic_model->insertdata('business',$businessData);
                    if($business_id)
                    {                        
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
            $this->form_validation->set_rules('other_address', 'Business Address', 'required',array('required' => $this->lang->line('business_address_required')));  		
			$this->form_validation->set_rules('country','Country', 'required', array( 'required' => $this->lang->line('country_required')));
			$this->form_validation->set_rules('state','State', 'required', array( 'required' => $this->lang->line('state_required')));
			$this->form_validation->set_rules('city','City', 'required', array( 'required' => $this->lang->line('city_required')));
			$this->form_validation->set_rules('lat', 'Latitude','required',array('required' => $this->lang->line('lat')));
			$this->form_validation->set_rules('lang', 'Longitude','required',array( 'required' => $this->lang->line('lng')));
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
				$business_id      = decode($this->input->post('business_id'));
				//$business_id      = 4;
				$business_address = $this->input->post('other_address');
				//$location_name    = $this->input->post('location_name');
				$latitude         = $this->input->post('lat');
				$longitude        = $this->input->post('lang');
				$country          = $this->input->post('country');
				$state            = $this->input->post('state');
				$city             = $this->input->post('city');
				$zipcode          = $this->input->post('zipcode');
				//$capacity         = $this->input->post('capacity');
				$location_info    = $this->input->post('location_info');
				//print_r($location_info);die;
				$businessData =   array(
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
					$business_update = $this->dynamic_model->updateRowWhere('business',$condition,$businessData);
					if($business_update)
			        {
			        	$condition1=array("business_id"=>$business_id);
			        	$business_delete = $this->dynamic_model->deletedata('business_location',$condition1);
			        	
						foreach($location_info as $value){
							$locationData =   array('business_id'=>$business_id,
									'location_name'  =>$value['location_name'],
									'capacity'		 =>$value['capacity'],
									'create_dt'		  =>$time,
									'update_dt'		  =>$time
				               );
						$this->dynamic_model->insertdata('business_location',$locationData);
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
		        foreach ($business_location_data as $value) {
	            	$roomsdata['location_name'] = $value['location_name'];
	            	$roomsdata['capacity']  = $value['capacity'];
	            	$business_room_array[]     = $roomsdata;
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
		        					'address'=>  $business_data[0]['other_address'],
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
			$pass_type_data = $this->dynamic_model->getdatafromtable('manage_pass_type',$condition);
			if(!empty($pass_type_data)){
			     foreach($pass_type_data as $value) 
	            {
	            	$passtypedata['pass_type_id']= encode($value['id']);
	            	$passtypedata['pass_type']   = $value['pass_type'];
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
					$is_recurring_billing      = $this->input->post('is_recurring_billing');
					$billing_start_from      = ($this->input->post('billing_start_from'))?$this->input->post('billing_start_from'):'';
					$age_restriction      = $this->input->post('age_restriction');
					$notes      = $this->input->post('notes');
					$tax1_rate      = ($this->input->post('tax1_rate'))? $this->input->post('tax1_rate') :0;
					$tax1_rate = ($amount*$tax1_rate)/100;

					$tax2_rate      = ($this->input->post('tax2_rate'))?$this->input->post('tax2_rate') :0 ;
					$tax2_rate = ($amount*$tax2_rate)/100;
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
					$passData =   array(
						                'business_id'   =>$business_id,
										'user_id'  		=>$usid,
										'pass_name'     =>$pass_name,
										'pass_id'   	=>$time,
										'pass_validity' =>$passexpiry,
										'purchase_date' =>$purchase_date,
										//'pass_end_date' =>$expire_date,
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
				$limit    = config_item('page_data_limit'); 
				//$limit    = 2; 
				$offset = $limit * $page_no;
				$where=array("business_id"=>decode($userdata['data']['business_id']),"status"=>"Active");
				$pass_data = $this->dynamic_model->getdatafromtable('business_passes',$where,"*",$limit, $offset,'create_dt');
				if(!empty($pass_data)){
				    foreach($pass_data as $value) 
		            {
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
										'status'   	    =>"Deactive",
										'create_dt'   	=>$time,
										'update_dt'   	=>$time
					                   );
					$business_class= $this->dynamic_model->insertdata('business_class',$classData);
					if($business_class)
			        {
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

                    //$where="business_id=".$business_id." AND status='Active' AND DATE(FROM_UNIXTIME(start_date_utc))>='".$todaydate."'";
                    // $schedule_data = $this->dynamic_model->getWhereInData('instructor_schedule','user_id',$instructor_ids,$where);
                     $where="business_id=".$business_id." AND day_id=".$day_id."";
                    //$schedule_data = $this->dynamic_model->getWhereInData('instructor_time_slot','user_id',$instructor_ids,$where);
                    $schedule_data= $this->dynamic_model->getdatafromtable('instructor_time_slot',$where);

                    
                   
                     $class_from_time = strtotime($class_from_time);
							$class_to_time = strtotime($class_to_time);
  					if(!empty($schedule_data)){
  						
						foreach($schedule_data as $key => $value){
						  $time_slot_data= $this->dynamic_model->getdatafromtable('business_time_slote',array("business_id"=>$business_id,"day_id"=>$value['day_id'],"id"=>$value['time_slot_id']));
						 
				            $instuctor_from_time = strtotime($time_slot_data[0]['time_slote_from']);
				             				            //$instuctor_from_time = '1593140400';
				            $instuctor_to_time = strtotime($time_slot_data[0]['time_slote_to']);

				           




				            //$instuctor_to_time = '1593154800';
                             // echo $instuctor_from_time .'=='. $class_from_time .'=='.$instuctor_to_time .'=='. $class_to_time;   
					         // if($instuctor_from_time <= $class_from_time && $instuctor_to_time <= $class_to_time){
				            // print_r($instuctor_from_time); echo "<br>";//09:00 1593574200
				            //  print_r($instuctor_to_time); echo "<br>";//12:00 1593585000
				            //   print_r($class_from_time); echo "<br>";//08:00 1593570600
				            //    print_r($class_to_time); echo "<br>";//01:30 1593547200
				            if($class_from_time >= $instuctor_from_time && $class_to_time <= $instuctor_to_time){
				             $userid[]= $value['user_id'];
			                }
						}
						

						
							if(!empty($userid)){
				                $user_data = $this->studio_model->get_instructor_data($userid);
					            
					            if(!empty($user_data)){
				                foreach ($user_data as $key => $value1) {
				                $response[]=array(
				                 	'id'=>encode($value1['id']),
				                 	'name'=>$value1['name'].' '.$value1['lastname'],
				                 	'profile_img' =>base_url().'/uploads/user/'. $value1['profile_img']);
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
			  if($service_type==1){
		        $business_data= $this->dynamic_model->getdatafromtable('business_class',$condition); 

		        $class_type=!empty($business_data) ? $business_data[0]['class_type']:'';
		        $where=array("business_id"=>decode($userdata['data']['business_id']),"class_type"=>$class_type,"service_type"=>1);
				}elseif($service_type==2){
		        $business_data= $this->dynamic_model->getdatafromtable('business_workshop',$condition);
		        $class_type=!empty($business_data) ? $business_data[0]['workshop_type']:'';
		        $where=array("business_id"=>decode($userdata['data']['business_id']),"class_type"=>$class_type,"service_id"=>'0',"service_type"=>2);
				}	

                //get passes according to class or workshop category	
				$pass_data = $this->dynamic_model->getdatafromtable('business_passes',$where);
// print_r($this->db->last_query()); die;
				//print_r($pass_data);die;
				if(!empty($pass_data)){
				    foreach($pass_data as $value) 
		            {
		            	$passesdata['pass_id']      = encode($value['id']);
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
										'status'   		=>$class_status,
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
					        								"class_id"=>$class_id
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
											$instructor_id = decode($value['instructor_id']);
											
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
											echo json_encode($time_slot_array); exit;
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
		            	$classesdata['capacity']     = $value['capacity'];
		            	$classesdata['location']     = $value['location_name'];
		            	$classesdata['from_time']    =  ($from_time)? $from_time:'';
		            	$classesdata['to_time']      =  ($to_time) ? $to_time:''; 
		            	$classesdata['from_time_utc']= $from_time;
		            	$classesdata['to_time_utc']  = $to_time;
		            	$classesdata['class_type']   = get_categories($value['class_type']);	            	
                        
                       
		            	$classesdata['start_date']    =  ($start_date !=='') ? date("M d Y",strtotime($start_date)) :'';
		            	$classesdata['end_date']    =  ($end_date !=='') ? date("M d Y",strtotime($end_date)) :'';
		            	$classesdata['start_date_utc']= $start_date;
		            	$classesdata['scheduled_date']= $value['scheduled_date'];
		            	$classesdata['end_date_utc']= $end_date;
		            	$classesdata['create_dt']    = date("M d Y",$value['create_dt']);
		            	$classesdata['create_dt_utc'] = $value['create_dt'];
		            	$classesdata['class_status'] = $value['status'];
		            	$classesdata['class_repeat_times'] = $value['class_repeat_times'];
		            	$attendence = $this->studio_model->get_class_attendence_count($business_id,$value['id'],$value['scheduled_date']);
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
				$page_no= (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";	
				$page_no= $page_no-1;
				$limit    = config_item('page_data_limit'); 
				$offset = $limit * $page_no;
				$business_id= decode($userdata['data']['business_id']);

				$where="business_id=".$business_id;
				$class_data = $this->dynamic_model->getdatafromtable('business_class',$where,"*",$limit,$offset,'create_dt');

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
		            	$classesdata['capacity']     = $value['capacity'];
		            	$classesdata['location']     = $value['location'];
		            	$classesdata['from_time']    =  ($from_time !=='') ? $from_time :'';
		            	$classesdata['to_time']      =  ($to_time !=='') ? $to_time :'';
		            	$classesdata['from_time_utc']= strtotime($from_time);
		            	$classesdata['to_time_utc']  = strtotime($to_time);
		            	$classesdata['class_type']   = get_categories($value['class_type']);	            	
                        
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
					
                    $where=array("id"=>$class_id,"business_id"=>$business_id,"status"=>"Active");
					$class_data = $this->studio_model->get_scheduled_class_detail($business_id,$class_id,$scheduled_date);
					//print_r($class_data);die;
					if(!empty($class_data)){
					    $classesdata['business_id']  = $business_id;
		            	$classesdata['class_id']     = $class_data[0]['id'];
		            	$classesdata['class_name']   = ucwords($class_data[0]['class_name']);
		            	$classesdata['from_time']    = $class_data[0]['from_time'];
		            	$classesdata['to_time']      = $class_data[0]['to_time'];
		            	$classesdata['from_time_utc'] =$class_data[0]['from_time'];
		            	$classesdata['to_time_utc'] = $class_data[0]['to_time'];
		            	$classesdata['duration']     = $class_data[0]['duration'].' minutes';
		            	$classesdata['scheduled_date']     = $class_data[0]['scheduled_date'];
                        $classesdata['total_capacity']    = $class_data[0]['capacity'];
                        $attendence = $this->studio_model->get_class_attendence_count($business_id,$class_data[0]['id'],$class_data[0]['scheduled_date']);
                        $classesdata['attendence']     = ($attendence)?count($attendence):0;
                        $classesdata['timeframe']     = get_daywise_instructor_data($class_data[0]['id'],1,$business_id);
		            	// $capicty_used                = get_checkin_class_or_workshop_count($class_data[0]['id'],1,$time);
			            // $classesdata['capacity']     = $capicty_used.'/'.$class_data[0]['capacity'];
                        $classesdata['location']     = $class_data[0]['location_name'];
		            	$classesdata['description']     = $class_data[0]['description'];
		            	$classesdata['class_type']   = get_categories($class_data[0]['class_type']);
                        $classesdata['start_date']    = date("M d Y",strtotime($class_data[0]['start_date']));
                            $classesdata['end_date']    = date("M d Y",strtotime($class_data[0]['end_date']));
                            
                            $classesdata['start_date_utc']=  strtotime($class_data[0]['start_date']);
                            $classesdata['end_date_utc']=  strtotime($class_data[0]['end_date']);
				        $classesdata['instructor_name'] = $class_data[0]['name'].' '.$class_data[0]['lastname'];
				        $classesdata['instructor_image'] = $class_data[0]['profile_image'];
		            	$where=array("business_id"=>$business_id,"service_id"=>$class_id,"service_type"=>"1","status"=>"Active");
					   	$passes_data = $this->dynamic_model->getdatafromtable('business_passes',$where,"*");
						if(!empty($passes_data)){
						    foreach($passes_data as $value) 
				            {
				                $passesdata=studiopassesdetails($value['id']);
				            	$pass_arr[]	  = $passesdata;
				            }
				        }
				        $classesdata['passes_details']    = $pass_arr;
		            	$classesdata['create_dt']    = date("d M Y ",$class_data[0]['create_dt']);
		            	$classesdata['create_dt_utc']  = $class_data[0]['create_dt'];
		            	$bookedUsers = $this->studio_model->get_booked_customer($class_data[0]['id'],$class_data[0]['scheduled_date']);
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
		            		$userArray[] = array(
		            						'user_id' =>$row['user_id'],
		            						'service_type' =>$row['service_type'],
		            						'service_id' =>$row['service_id'],
		            						'status' =>$row['status'],
		            						'checkin_dt' =>$row['checkin_dt'],
		            						'name' =>$row['name'],
		            						'lastname' =>$row['lastname'],
		            						'profile_image' =>$row['profile_image'],
		            						'covid_info' => $covid_info,
                    						'covid_status' => $covid_status
		            					);

		            	}
		            	$classesdata['booked_users']  = $userArray;
		            	$classesdata['day_name']  = $class_data[0]['week_name'];
		            	
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
	      
		   
			 
				$response=array();
				$time=time();
				$business_id= decode($userdata['data']['business_id']);

				$date = date('Y-m-d');
				
				$class_data = $this->studio_model->get_scheduled_class_list($business_id,'0','','','','');

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
		            	$classesdata['end_date_utc']= $end_date;
		            	$classesdata['create_dt']    = date("M d Y",$value['create_dt']);
		            	$classesdata['create_dt_utc'] = $value['create_dt'];
		            	$classesdata['class_status'] = $value['status'];
		            	$classesdata['class_repeat_times'] = $value['class_repeat_times'];
		            	$attendence = $this->studio_model->get_class_attendence_count($business_id,$value['id'],$value['scheduled_date']);
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
					$arg['data']       = array();
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
				
				$query = 'SELECT ser.id, ser.service_name, ser.service_type as service_category_id, ms.name as service_category, ser.start_date_time as start_date, ser.end_date_time  as end_date, ser.duration, ser.amount, ser.tax1, ser.tax1_rate,  ser.tax2, ser.tax2_rate, ser.cancel_policy, ser.tip_option, ser.pay_rate, ser.create_dt, ser.create_dt as create_dt_utc FROM service ser JOIN manage_skills ms on (ms.id = ser.service_type) where ser.business_id = '.$business_id.' AND ser.status = "Active" LIMIT '.$limit.' OFFSET '.$offset;

				$service_data = $this->dynamic_model->getQueryResultArray($query);
				
				if(!empty($service_data)){

					array_walk ( $service_data, function (&$key) {
						$key["service_id"] = encode($key['id']);
						unset($key['id']);
					});
					
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
                $where=array("business_id"=>$business_id);	
				$workshop_data = $this->dynamic_model->getdatafromtable('business_workshop',$where,"*",$limit,$offset,'create_dt'); 
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
				}
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
	public function workshop_scheduling()
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
				$workshop_data = $this->dynamic_model->getdatafromtable('business_workshop',$where,"*",$limit,$offset,'create_dt');
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
				$this->form_validation->set_rules('sin_no','sin no', 'required', array( 'required' => $this->lang->line('sin_no_required')));
				

				// $this->form_validation->set_rules('singup_for','Personal Account / For family member', 'required', array( 'required' => $this->lang->line('signupfor_required')));
				// $this->form_validation->set_rules('gender','Select gender', 'required', array( 'required' => $this->lang->line('gender_required')));
				// $this->form_validation->set_rules('city','City', 'required', array( 'required' => $this->lang->line('city_required')));
				// $this->form_validation->set_rules('state','State', 'required', array( 'required' => $this->lang->line('state_required')));
				// $this->form_validation->set_rules('country','Country', 'required', array( 'required' => $this->lang->line('country_required')));
				// $this->form_validation->set_rules('address','Address', 'required', array( 'required' => $this->lang->line('dob_required')));
				// $this->form_validation->set_rules('date_of_birth','DOB', 'required', array( 'required' => $this->lang->line('address_required')));
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
				    //$role  = $this->input->post('role');
				    $role  = 4;
				    //$singup_for  = $this->input->post('singup_for');
                    $name            = $this->input->post('name');
					$lastname        = $this->input->post('lastname');
					$email           = $this->input->post('email');
					// $mobile       = $this->input->post('mobile');
					// $gender       = $this->input->post('gender');
					// $date_of_birth       = $this->input->post('date_of_birth');
					// $address       = $this->input->post('address');
					// $city       = $this->input->post('city');
					// $state       = $this->input->post('state');
					// $country       = $this->input->post('country');
					// $country_code = $this->input->post('country_code');
					// $lat       = $this->input->post('lat');
					// $lang       = $this->input->post('lang');
					// $lat =  $this->input->get_request_header('lat', true);
					// $lang =  $this->input->get_request_header('lang', true);
					// $zipcode       = $this->input->post('zipcode');
					// $referred_by       = $this->input->post('referred_by');
					// $street       = $this->input->post('street');
					// $about       = $this->input->post('about');
					$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890123456789!@#$&*!@#$&*!@#$&*";
    				$password = substr(str_shuffle( $chars ),0,14);
					$hashed_password = encrypt_password($password);
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
				    if(!empty($_FILES['image']['name'])){
					$image = $this->dynamic_model->fileupload('image', 'uploads/user');
					}
					$notification = '{"app_notification":"1","alerts":"1","email":"1","sms":"1","phonecall":"1"}';
					$uniquemail   = getuniquenumber();
					$uniquemobile   = rand(0001,9999);
					$userdata = array(
						        'name'=>$name,
						        'lastname'=>$lastname,
						        'email'=>$email,
						        'password'=>$hashed_password,
						        'singup_for'=>"Me",
						        'profile_img'=>$image,
						        'email_verified'=>'1',
						        'mobile_verified'=>'0',
						        'mobile_otp'=>$uniquemobile,
						        'mobile_otp_date'=>$time,
						        'status'=>$status,
						        'create_dt'=>$time,
						        'update_dt'=>$time,
						        'notification'=>$notification,
						        //'location'=>$street,
						        //'country_code'=>$country_code
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
						$instructor_data = array(
							           'user_id'=>$newuserid,
							           'skill' =>$skills,
							           'total_experience'=>$total_exp,
							           'appointment_fees_type'=>$appointment_fees_type,
							           'sin_no'=>$sin_no,
							           'start_date'=>$start_date,
							           'substitute_instructor_name'=>$subsitute_name,
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
							
							//Send Email Code
							$enc_user = encode($newuserid);
							$enc_role = encode($time);
							$url = site_url().'webservices/api/verify_user?encid='.$enc_user.'&batch='.$enc_role;

                            $where1 = array('slug' => 'instructor_registration_by_owner');
                            $template_data = $this->dynamic_model->getdatafromtable('manage_notification_mail', $where1);
                            $desc= str_replace('{USERNAME}',$name,$template_data[0]['description']);
                            $desc_data= str_replace('{PASSWORD}',$password, $desc);
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
				// $this->form_validation->set_rules('gender','Select gender', 'required', array( 'required' => $this->lang->line('gender_required')));
				// $this->form_validation->set_rules('city','City', 'required', array( 'required' => $this->lang->line('city_required')));
				// $this->form_validation->set_rules('state','State', 'required', array( 'required' => $this->lang->line('state_required')));
				// $this->form_validation->set_rules('country','Country', 'required', array( 'required' => $this->lang->line('country_required')));
				// $this->form_validation->set_rules('address','Address', 'required', array( 'required' => $this->lang->line('dob_required')));
				// $this->form_validation->set_rules('date_of_birth','DOB', 'required', array( 'required' => $this->lang->line('address_required')));
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
				    //$role  = $this->input->post('role');
				    $role  = 4;
				    //$singup_for  = $this->input->post('singup_for');
                    $name            = $this->input->post('name');
					$lastname        = $this->input->post('lastname');
					
					// $mobile       = $this->input->post('mobile');
					// $gender       = $this->input->post('gender');
					// $date_of_birth       = $this->input->post('date_of_birth');
					// $address       = $this->input->post('address');
					// $city       = $this->input->post('city');
					// $state       = $this->input->post('state');
					// $country       = $this->input->post('country');
					// $country_code = $this->input->post('country_code');
					// $lat       = $this->input->post('lat');
					// $lang       = $this->input->post('lang');
					// $lat =  $this->input->get_request_header('lat', true);
					// $lang =  $this->input->get_request_header('lang', true);
					// $zipcode       = $this->input->post('zipcode');
					// $referred_by       = $this->input->post('referred_by');
					// $street       = $this->input->post('street');
					// $about       = $this->input->post('about');
					
					$skills      = multiple_decode_ids($this->input->post('skills'));	
					$total_exp  = $this->input->post('experience');
					$sin_no     = $this->input->post('sin_no');	
					$appointment_fees_type  = $this->input->post('appointment_fees_type');
					$appointment_fees = $this->input->post('appointment_fees');
					$start_date      = $this->input->post('start_date');
					$subsitute_name  = $this->input->post('subsitute_instructor_name');
					$status  = $this->input->post('status');
					$employee_contractor= $this->input->post('employee_contractor');
					$employee_id= $this->input->post('employee_id');
					
				    $image = 'userdefault.png';
				    if(!empty($_FILES['image']['name'])){
					$image = $this->dynamic_model->fileupload('image', 'uploads/user');
					}
					$notification = '{"app_notification":"1","alerts":"1","email":"1","sms":"1","phonecall":"1"}';
					
					
					$userdata = array(
						        'name'=>$name,
						        'lastname'=>$lastname,
						        'singup_for'=>"Me",
						        'profile_img'=>$image,
						        'email_verified'=>'1',
						        'mobile_verified'=>'1',
						        
						        'mobile_otp_date'=>$time,
						        'status'=>$status,
						        'create_dt'=>$time,
						        'update_dt'=>$time,
						        'notification'=>$notification,
						        //'location'=>$street,
						        //'country_code'=>$country_code
						    );

						$where = array('id'=>$usid);
						$newuserid = $this->dynamic_model->updateRowWhere('user',$where,$userdata);
						
						$instructor_data = array(
							           'skill' =>$skills,
							           'total_experience'=>$total_exp,
							           'appointment_fees_type'=>$appointment_fees_type,
							           'sin_no'=>$sin_no,
							           'start_date'=>$start_date,
							           'substitute_instructor_name'=>$subsitute_name,
							           'employee_id'=>(!empty($employee_id))? $employee_id : '',
							           'employee_contractor'=>$employee_contractor,
							           'create_dt'=>$time,
							           'created_by'=>$usid,
							           'update_dt'=>$time
								      );
							$where1 = array('user_id'=>$usid);
						    $this->dynamic_model->updateRowWhere('instructor_details',$where1,$instructor_data);
						    	
							
							//code
                            $data_val  = get_user_details($usid,$role);
                            
							$arg['status']    = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line']= __line__;
						 	$arg['message']   = $this->lang->line('profile_updated_success');
						 	$arg['data']      = $data_val;
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
				$instructor_info =  $this->studio_model->get_all_instructors($business_id,$search_val,$limit,$offset);
				$url = base_url().'uploads/user/';
				
				if($instructor_info){
					foreach($instructor_info as $value){
					$instructordata['id']     = encode($value['id']);
	            	$instructordata['name']   = ucwords($value['name']);
	            	$instructordata['lastname']= ucwords($value['lastname']);
	            	$instructordata['about']    = $value['about'];
	            	$instructordata['profile_img'] = $url.$value['profile_img'];
	            	$instructordata['availability_status']= $value['availability_status'];
	            	
	            	$instructordata['skill'] = (!empty($value['skill'])) ? get_categories($value['skill']) : "";
	            	$category=$value['skill'];
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
        			if(!empty($shedule_data)){
        				
	            			foreach ($shedule_data as $value1) {
	            				$timeline_data = $this->dynamic_model->getdatafromtable('business_class',array('id'=>$value1['class_id'],'status'=>'Active'));
	            				if($timeline_data){
	            					
						            $startTime = date('H:i:s',$value1['from_time']);
						            $endTime = date('H:i:s',$value1['to_time']);
						            $startDateTime= date('Y,m,d,H,i', strtotime($value1['scheduled_date'].' '.$startTime));
						            $endDateTime= date('Y,m,d,H,i', strtotime($value1['scheduled_date'].' '.$endTime));
									$singned_customer = $this->studio_model->get_booked_customer($value1['class_id'],$value1['scheduled_date']);
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
	            $this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|max_length[20]|regex', array( 
	                'required' => $this->lang->line('password_required'),
	                'min_length' => $this->lang->line('password_minlength'),
	                'max_length' => $this->lang->line('password_maxlenght'),
	                'regex' => $this->lang->line('reg_check')
	            ));
	            // $this->form_validation->set_rules('role','Role', 'required', array( 'required' => $this->lang->line('role_required')));
	            $this->form_validation->set_rules('singup_for','Personal Account / For family member', 'required', array( 'required' => $this->lang->line('signupfor_required')));
	            $this->form_validation->set_rules('gender','Select gender', 'required', array( 'required' => $this->lang->line('gender_required')));
	            $this->form_validation->set_rules('city','City', 'required', array( 'required' => $this->lang->line('city_required')));
	            $this->form_validation->set_rules('state','State', 'required', array( 'required' => $this->lang->line('state_required')));
	            $this->form_validation->set_rules('country','Country', 'required', array( 'required' => $this->lang->line('country_required')));
	            $this->form_validation->set_rules('address','Address', 'required', array( 'required' => $this->lang->line('address_required')));
	            $this->form_validation->set_rules('date_of_birth','DOB', 'required', array( 'required' => $this->lang->line('dob_required')));
	          
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
	                $singup_for  = $this->input->post('singup_for');
	                $name            = $this->input->post('name');
	                $lastname        = $this->input->post('lastname');
	                $email           = $this->input->post('email');
	                $mobile       = $this->input->post('mobile');
	                $gender       = $this->input->post('gender');
	                $date_of_birth       = $this->input->post('date_of_birth');
	                $address       = $this->input->post('address');
	                $city       = $this->input->post('city');
	                $state       = $this->input->post('state');
	                $country       = $this->input->post('country');
	                $country_code = $this->input->post('country_code');
	                $lat       = $this->input->post('lat');
	                $lang       = $this->input->post('lang');
	                $zipcode       = $this->input->post('zipcode');
	                $referred_by   = $this->input->post('referred_by');
	                $street       = $this->input->post('street');
	                $street       = (!empty($street)) ? $street :'';
	                $discount     = $this->input->post('discount');
	                $consent_signed= $this->input->post('consent_signed');
	                $hashed_password = encrypt_password($this->input->post('password'));

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
	                $userdata = array('name'=>$name,'lastname'=>$lastname,'password'=>$hashed_password,'email'=>$email,'mobile'=>$mobile,'profile_img'=>$image,'status'=>'Deactive','gender'=>$gender,'date_of_birth'=>$date_of_birth,'address'=>$address,'city'=>$city,'state'=>$state,'country'=>$country,'lat'=>$lat,'lang'=>$lang,'zipcode'=>$zipcode,'singup_for'=>$singup_for,'referred_by'=>$referred_by,'email_verified'=>'0','mobile_verified'=>'0','mobile_otp'=>$uniquemobile,'mobile_otp_date'=>$time,'create_dt'=>$time,'update_dt'=>$time,'notification'=>$notification,'location'=>$street,'country_code'=>$country_code,'discount'=>$discount,'consent_signed'=>$consent_signed,'created_by'=>$usid);
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
	                        $where = array('id' => $newuserid);
	                        $findresult = $this->dynamic_model->getdatafromtable('user', $where);
	                        $name= ucwords($findresult[0]['name']);
	                        
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
		        $client_data = $this->dynamic_model->getTwoTableData('user.*,user_role.role_id','user','user_role',$on,$where);
				//print_r($instructor_info);die;
				if($client_data){
					foreach($client_data as $value){
					$clientdata['id']     = $value['id'];
					//$clientdata['type']   = $value['title_name'];
	            	$clientdata['name']   = ucwords($value['name'].' '.$value['lastname']);
	            	$clientdata['email']  = $value['email'];
	            	$clientdata['profile_img']  = base_url().'uploads/user/'.$value['profile_img'];
	            	$clientdata['country_code'] = $value['country_code'];
	            	$clientdata['mobile'] = $value['mobile'];
	            	$clientdata['date_of_birth'] = $value['date_of_birth'];
	            	$clientdata['gender'] = $value['gender'];
	            	$clientdata['role'] =$value['role_id'];//client;
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
				$response=$imgarr=array();
				$passes_book_id=decode($this->input->post('passes_book_id'));
				$business_id=decode($userdata['data']['business_id']);
				$where=array('id'=>$passes_book_id,"business_id"=>$business_id,"service_type"=>1);
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
				$user_id=$business_data[0]['user_id'];
				$statusData =   array(
									'status'  =>$status,
									'update_dt'=>$time
				                   );
				 $condition=array("user_id"=>$id);
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
						$arg['message']    = $this->lang->line('server_problem');
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
                $business_time_slote= $this->dynamic_model->getdatafromtable('business_time_slote',array("business_id"=>$business_id));
                //print_r($business_time_slote);die;
                if(!empty($slot_info)){
                if(empty($business_time_slote)){
                    foreach($slot_info as $key=>$value) 
                    {
                        $day_id   = $value['day_id'];
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
						$arg['message']    = $this->lang->line('server_problem');
			        }
			     }else{
			     	  $arg['status']     = 0;
                        $arg['error_code']  = REST_Controller::HTTP_OK;
                        $arg['error_line']= __line__;
                       $arg['data']      = json_decode('{}');
                        $arg['message']    = $this->lang->line('already_availability'); 
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
                   	 	$service_detail = $this->dynamic_model->getdatafromtable('business_passes',array("id"=>$value['service_id'],"business_id"=>$value['business_id']));
                   	 	$service_name = $service_detail[0]['pass_name'];
                   	 	$service_type = 'Pass';
                   	 }

                   	 if($value['service_type']=='2'){
                   	 	$service_detail = $this->dynamic_model->getdatafromtable('service',array("id"=>$value['service_id'],"business_id"=>$value['business_id']));
                   	 	$service_name = $service_detail[0]['service_name'];
                   	 	$service_type = 'Service';
                   	 }

                   	 if($value['service_type']=='3'){
                   	 	$service_detail = $this->dynamic_model->getdatafromtable('business_product',array("id"=>$value['service_id'],"business_id"=>$value['business_id']));
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
	                   					'passes_start_date'=>($passes_start_date)?$passes_start_date:'',
	                   					'passes_end_date'=>($passes_end_date)?$passes_end_date:'',
	                   					'status'=>'Success'
	                   				);
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
    	$date = date('Y-m-d');
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
    	}

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
						$usid    = $this->input->post('user_id');
						//service_type=> 1 class 2 workshop 3 trainer 
						$service_type    = $this->input->post('service_type');
						// passes_status=> checkin checkout cancel
						$passes_status    = $this->input->post('passes_status');
                        $today_date = date("Y-m-d");
                       

						$where = array('id'=>$service_id,'status'=>"Active");
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
				        }
				        
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
                    $business_id  = decode($this->input->post('business_id'));
                    $user_id = $this->input->post('user_id');
                    $role_id = $this->input->post('role_id');
                    $data_val  = get_user_details($user_id,$role_id);
                   	
                    if($data_val){  
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

                    $usid =$userdata['data']['id'];
                    $pass_id = decode($this->input->post('pass_id'));
					$data_val  = studiopassesdetails($pass_id);
                   	
                    if($data_val){  
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
   
}
