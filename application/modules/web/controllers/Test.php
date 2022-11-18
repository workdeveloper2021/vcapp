<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Test extends MX_Controller {
	public function __construct()
	{
		parent::__construct();
		header('Content-Type: application/json');
	    $this->load->library('form_validation');
		//$this->load->library('session');
		$this->load->model('dynamic_model');
		$this->load->model('studio_model');
	    $this->load->helper('web_common_helper');
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
    public function get_business_categories()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$condition = array('status' => "Active",'category_type' => "1");
			$category_list = $this->dynamic_model->getdatafromtable('business_category',$condition,'id,category_name');
			if(!empty($category_list)){
				foreach($category_list as $value) 
	            {
	            	$categorydata['category_id']= encode($value['id']);
	            	$categorydata['category_name']   = $value['category_name'];
	            	$response[]	        = $categorydata;
	            }	
				$arg['status']     = 1;
				$arg['error_code']  = 200;//REST_Controller::HTTP_OK;
				$arg['error_line']= __line__;
				$arg['data']       = $response;
				$arg['message']    = $this->lang->line('record_found');
			} else {
				$arg['status']     = 0;
				$arg['error_code']  = 200;//REST_Controller::HTTP_OK;
				$arg['error_line']= __line__;
				$arg['data']       = array();
			 	$arg['message']    = $this->lang->line('record_not_found');	
			}	
		}		
		echo json_encode($arg);
	}
    public function test_demo(){
        //$where = array('email' => $email);
        $result = $this->dynamic_model->getdatafromtable('user');
        if(!empty($result))
        {
         foreach($result as $key => $value){ 
                     $userdata = array(
                        'user_id'=>$value['id'],
                        'role_id'=>$value['role_id'],
                        'create_dt'=>$value['create_dt'],
                        'update_dt'=>$value['update_dt']
                    );
                        $newuserid = $this->dynamic_model->insertdata('user_role',$userdata);
                }

        }

    }
	public function register()
    {
        $arg   = array();
        if($_POST)
        {
            $version_result = version_check_helper1();
            if($version_result['status'] != 1 )
            {
                $arg = $version_result;
            }
            else
            {
                $this->form_validation->set_rules('name','Name', 'required|trim', array( 'required' => $this->lang->line('first_name')));
                $this->form_validation->set_rules('lastname','Last Name', 'required|trim', array( 'required' => $this->lang->line('last_name')));
                $this->form_validation->set_rules('email', 'Email', 'required|valid_email' , array('required' => $this->lang->line('email_required'),'valid_email' => $this->lang->line('email_valid')
                 ));
                $this->form_validation->set_rules('mobile', 'Mobile', 'required|min_length[8]|max_length[20]|numeric|is_unique[user.mobile]', array(
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
                
                $this->form_validation->set_rules('role','Role', 'required', array( 'required' => $this->lang->line('role_required')));
                $this->form_validation->set_rules('singup_for','Personal Account / For family member', 'required', array( 'required' => $this->lang->line('signupfor_required')));
                $this->form_validation->set_rules('gender','Select gender', 'required', array( 'required' => $this->lang->line('gender_required')));
                $this->form_validation->set_rules('city','City', 'required', array( 'required' => $this->lang->line('city_required')));
                $this->form_validation->set_rules('state','State', 'required', array( 'required' => $this->lang->line('state_required')));
                $this->form_validation->set_rules('country','Country', 'required', array( 'required' => $this->lang->line('country_required')));
                $this->form_validation->set_rules('address','Address', 'required', array( 'required' => $this->lang->line('dob_required')));
                $this->form_validation->set_rules('date_of_birth','DOB', 'required', array( 'required' => $this->lang->line('address_required')));
                if($this->input->post('role')==3){
                $this->form_validation->set_rules('emergency_contact_person','Emergency person', 'required', array( 'required' => $this->lang->line('emer_person_required')));
                $this->form_validation->set_rules('emergency_contact_no','Emergency person', 'required', array( 'required' => $this->lang->line('emer_contact_required')));
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
                    $role  = $this->input->post('role');
                    $role2=($role==3) ? 4 : 3;
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
                    // $lat =  $this->input->get_request_header('lat', true);
                    // $lang =  $this->input->get_request_header('lang', true);
                    $zipcode       = $this->input->post('zipcode');
                    $referred_by       = $this->input->post('referred_by');
                    $street       = $this->input->post('street');
                    $about       = $this->input->post('about');
                    $emergency_contact_person       = $this->input->post('emergency_contact_person');
                    $emergency_contact_no       = $this->input->post('emergency_contact_no');
                    $emergency_country_code       = $this->input->post('emergency_country_code');
                    $hashed_password = encrypt_password($this->input->post('password'));

                    $where = array('email' => $email);
                    $result = $this->dynamic_model->check_user_role($email,$role,1,$role2);
                    //print_r($result);die;

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
                    $time=time();
                    $uniquemail   = getuniquenumber();
                    $uniquemobile   = rand(0001,9999);
                    $userdata = array('name'=>$name,'lastname'=>$lastname,'password'=>$hashed_password,'email'=>$email,'mobile'=>$mobile,'profile_img'=>$image,'status'=>'Deactive','gender'=>$gender,'date_of_birth'=>$date_of_birth,'address'=>$address,'city'=>$city,'state'=>$state,'country'=>$country,'lat'=>$lat,'lang'=>$lang,'zipcode'=>$zipcode,'singup_for'=>$singup_for,'referred_by'=>$referred_by,'emergency_contact_person'=>$emergency_contact_person,'emergency_contact_no'=>$emergency_contact_no,'email_verified'=>'0','mobile_verified'=>'0','mobile_otp'=>$uniquemobile,'mobile_otp_date'=>$time,'create_dt'=>$time,'update_dt'=>$time,'notification'=>$notification,'location'=>$street,'country_code'=>$country_code,'emergency_country_code'=>$emergency_country_code);
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
                            //if role instructor than userId also insert in instructor_details table
                            if($role==4){
                             $instructor_data = array('user_id'=>$newuserid,'about'=>$about,'create_dt'=>$time,'update_dt'=>$time);
                             $this->dynamic_model->insertdata('instructor_details',$instructor_data);
                            }   
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
                $role_id= $this->input->post('role');
                $this->form_validation->set_rules('email', 'Email', 'required',array('required' => $this->lang->line('email_required')
                ));
                $this->form_validation->set_rules('password', '', 'required|min_length[8]|max_length[20]|regex', array(
                        'required' => $this->lang->line('password_required'),
                        'min_length' => $this->lang->line('password_minlength'),
                        'max_length' => $this->lang->line('password_maxlenght'),
                        'regex' => $this->lang->line('reg_check')
                    ));
                $this->form_validation->set_rules('device_token', 'Device Token', 'required',array('required' => $this->lang->line('device_token_required')
                ));
                $this->form_validation->set_rules('device_type', 'Device Type', 'required',array('required' => $this->lang->line('device_type_required')
                ));
                if($role_id ==3){
                $this->form_validation->set_rules('role', 'Role ', 'required',array('required' => $this->lang->line('role_required')
                ));
                }
                if ($this->form_validation->run() == FALSE)
                {
                    $arg['status']  = 0;
                    $arg['message'] = get_form_error($this->form_validation->error_array());
                }
                else
                {
                    $role2=($role_id==3) ? 4 : 3;
                    $email= $this->input->post('email');
                    $time=time();
                    $where = array('email' => $email);
                    $data = $this->dynamic_model->check_user_role($email,$role_id,1,$role2);
                    if(!empty($data))
                    {
                        $userid = $data[0]['id'];
                          //Insert data in user role table
                        if(count($data)!==2){
                        if($role_id!==$data[0]['role_id']){
                        $role=($data[0]['role_id']==3) ? 4 : 3;
                        $roledata = array(
                            'user_id'=>$userid,
                            'role_id'=>$role,
                            'create_dt'=>$time,
                            'update_dt'=>$time
                        );
                        $roleid = $this->dynamic_model->insertdata('user_role',$roledata);
                          }
                        }
                        $userdata = getuserdetail($userid,$role_id);
                        $hashed_password = encrypt_password($this->input->post('password'));
                        if($hashed_password == $data[0]['password'])
                        {
                            $emailid  = $data[0]['email'];
                            $token    = uniqid();
                            if ($userdata) {
                            if ($userdata['email_verified'] != 1) {
                            $arg['status']     = 0;
                            $arg['error_code']  = EMAILNOTVERIFED;
                            $arg['error_line']= __line__;
                            $arg['message']    = $this->lang->line('email_not_varify');
                            $arg['data']     = $userdata;
                            echo json_encode($arg);
                            exit();
                            }
                            if ($userdata['mobile_verified'] != 1) {
                            $arg['status']     = 0;
                            $arg['error_code']  = MOBILENOTVERIFIED;
                            $arg['error_line']= __line__;
                            $arg['message']    = $this->lang->line('otp_not_verify');
                            $arg['data']     = $userdata;
                            echo json_encode($arg);
                            exit();
                            }
                            if ($userdata['status'] != 'Active') {
                            $arg['status']    = 0;
                            $arg['message']   = $this->lang->line('user_deactive');
                            $arg['error_code'] = REST_Controller::HTTP_OK;
                            $arg['error_line']= __line__;
                            $arg['data']      = json_decode('{}');
                            echo json_encode($arg);
                            exit();
                            }
                          
                            $device_id   = $this->input->post('device_token');
                            $device_type = $this->input->post('device_type');

                            $where = array('email' => $emailid);
                            $tokenupdate = array('device_token'=>$device_id,'device_type'=>$device_type);
                            $varify = $this->dynamic_model->updateRowWhere('user',$where,$tokenupdate);
                            // $tokendata = array('userid'=>$userid,'token'=>$token);
                            // $user_token = base64_encode(json_encode($tokendata)); 
                            $arg['status']     = 1;
                            $arg['error_code']  = REST_Controller::HTTP_OK;
                            $arg['error_line']= __line__;
                            $arg['message']    = $this->lang->line('login_success');
                            $arg['data']     = $userdata;
                            
                            } else {
                            $arg['status']    = 0;
                            $arg['message']   = $this->lang->line('invalid_detail');
                            $arg['error_code'] = REST_Controller::HTTP_OK;
                            $arg['error_line']= __line__;
                            $arg['data']      = json_decode('{}'); 
                            
                            }   
                            
                        }
                        else
                        {
                            $arg['status']    = 0;
                            $arg['message']   = $this->lang->line('password_notmatch');
                            $arg['error_code'] = REST_Controller::HTTP_OK;
                            $arg['error_line']= __line__;
                            $arg['data']      = json_decode('{}');
                        }
                    }
                    else
                    {
                        $arg['status']    = 0;
                        $arg['message']   = $this->lang->line('register_first');
                        $arg['error_code'] = REST_Controller::HTTP_OK;
                        $arg['error_line']= __line__;
                        $arg['data']      = json_decode('{}');
                    }
                }
            }
            echo json_encode($arg);
        }
    }

}

