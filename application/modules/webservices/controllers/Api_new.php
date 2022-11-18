
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

class Api_new extends REST_Controller {

	public function __construct() {
		parent::__construct();
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers,Authorization,X-API-KEY,Origin,X-Requested-With,userid,token,timeZone,timeZoneOffset,language,version,deviceId,deviceType,lat,lang,role");
		$method = $_SERVER['REQUEST_METHOD'];
		if ($method == "OPTIONS") {
			die();
		}

		$this->load->library('form_validation');
		$this->load->library('session');
		$this->load->model('dynamic_model');
		$this->load->model('instructor_model');
		$this->load->helper('stripe_helper');
		$this->load->model('api_model');
		$this->load->library('Bomborapay');
		$language = $this->input->get_request_header('language');
		if ($language == "en") {
			$this->lang->load("message", "english");
		} else if ($language == "ar") {
			$this->lang->load("message", "arabic");
		} else {
			$this->lang->load("message", "english");
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
	public function version_check_get() {
		$arg = array();
		$version_result = version_check_helper1();
		echo json_encode($version_result);
	}

	public function test_get() {
		$where = "";
		$responce = get_all_workshop('13');
		echo '<pre/>';
		print_r($responce);
		/*$_POST = json_decode(file_get_contents("php://input"), true);
			        $token = $this->input->post('token');
			        $marchant_id = $this->input->post('marchant_id');

			        $legato_token_data = array(
			                            'language' => 'en',
			                            'comments' => SITE_NAME,
			                            'token' => array('name' => 'Test Card',
			                            'code' => $token)
			                        );
			        $apiurl='https://api.na.bambora.com/v1/profiles';
			        $responce = $this->bomborapay->profile_create('POST',$apiurl, $legato_token_data, $marchant_id);
			        echo '--'.$marchant_id;
			        echo '<pre/>';
		*/
	}
	/****************Function check user mobile/email*****************************
		     * @type            : Function
		     * @Author          : Aamir
		     * @function name   : register
		     * @description     : Registeration for new user,
		     					  send email verificication link and
		     					  otp on register mobile number.
		     * @param           : null
		     * @return          : null
	*/

	public function validate_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$_POST = json_decode(file_get_contents("php://input"), true);
			if ($_POST) {
				$this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[user.email]', array('required' => $this->lang->line('email_required'), 'valid_email' => $this->lang->line('email_valid'), 'is_unique' => $this->lang->line('email_unique'),
				));
				$this->form_validation->set_rules('mobile', 'Mobile', 'required|min_length[8]|max_length[20]|numeric|is_unique[user.mobile]', array(
					'required' => $this->lang->line('mobile_required'),
					'min_length' => $this->lang->line('mobile_min_length'),
					'max_length' => $this->lang->line('mobile_max_length'),
					'numeric' => $this->lang->line('mobile_numeric'),
				));
				if ($this->form_validation->run() == FALSE) {
					$arg['status'] = 0;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {

					$arg['status'] = 1;
					$arg['error_code'] = REST_Controller::HTTP_OK;
					$arg['error_line'] = __line__;
					$arg['message'] = $this->lang->line('email_mobile_required');
					$arg['data'] = '';
				}
			}

		}
		echo json_encode($arg);
	}
	//Used function to get countries details
	public function get_countries_get() {
		$arg = array();
		$countryData = $this->api_model->get_country();
		if (!empty($countryData)) {
			foreach ($countryData as $key => $value) {
				$data[] = array
					(
					'id' => $value['id'],
					'name' => ucwords($value['name']),
					'code' => ucwords($value['code']),
				);
			}
			$arg['status'] = 1;
			$arg['error_code'] = REST_Controller::HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['data'] = $data;
			$arg['message'] = $this->lang->line('country_list');
		} else {
			//$arg['error']   = ERROR_FAILED_CODE;
			$arg['status'] = 0;
			$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
			$arg['error_line'] = __line__;
			$arg['message'] = $this->lang->line('record_not_found');
			$arg['data'] = array();
		}

		echo json_encode($arg);
	}

	public function cancel_policy_get() {
		$arg = array();
		$condition = array('slug' => 'cancel_policy');
		$result = $this->dynamic_model->getdatafromtable('manage_static_page', $condition);
		if (!empty($result)) {
			$arg['status'] = 1;
			$arg['error_code'] = REST_Controller::HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['data'] = $result;
			$arg['message'] = '';
		} else {
			//$arg['error']   = ERROR_FAILED_CODE;
			$arg['status'] = 0;
			$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
			$arg['error_line'] = __line__;
			$arg['message'] = $this->lang->line('record_not_found');
			$arg['data'] = array();
		}

		echo json_encode($arg);
	}

	//Used function to get countries details
	public function get_skills_get() {
		$arg = array();

		//getdatafromtable($tbnm, $condition = array(), $data = "*", $limit = "", $offset= "", $orderby = "", $ordertype = "ASC")

		$skills = $this->dynamic_model->getdatafromtable('manage_skills', $condition = array(), '', '', '', 'name', 'ASC');
		if (!empty($skills)) {

			$arg['status'] = 1;
			$arg['error_code'] = REST_Controller::HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['data'] = $skills;
			$arg['message'] = $this->lang->line('skills_list');
		} else {
			//$arg['error']   = ERROR_FAILED_CODE;
			$arg['status'] = 0;
			$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
			$arg['error_line'] = __line__;
			$arg['message'] = $this->lang->line('record_not_found');
			$arg['data'] = array();
		}

		echo json_encode($arg);
	}
	/****************Function register**********************************
		     * @type            : Function
		     * @Author          : Aamir
		     * @function name   : register
		     * @description     : Registeration for new user,
		     					  send email verificication link and
		     					  otp on register mobile number.
		     * @param           : null
		     * @return          : null
	*/
	public function register_post() {
		$arg = array();
		if ($_POST) {

			$version_result = version_check_helper1();
			if ($version_result['status'] != 1) {
				$arg = $version_result;
			} else {
				$this->form_validation->set_rules('name', 'Name', 'required|trim', array('required' => $this->lang->line('first_name')));
				$this->form_validation->set_rules('lastname', 'Last Name', 'required|trim', array('required' => $this->lang->line('last_name')));
				$this->form_validation->set_rules('email', 'Email', 'required|valid_email', array('required' => $this->lang->line('email_required'), 'valid_email' => $this->lang->line('email_valid'),
				));
				$this->form_validation->set_rules('mobile', 'Mobile', 'required|min_length[8]|max_length[20]|numeric', array(
					'required' => $this->lang->line('mobile_required'),
					'min_length' => $this->lang->line('mobile_min_length'),
					'max_length' => $this->lang->line('mobile_max_length'),
					'numeric' => $this->lang->line('mobile_numeric'),
				));
				$this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|max_length[20]|regex', array(
					'required' => $this->lang->line('password_required'),
					'min_length' => $this->lang->line('password_minlength'),
					'max_length' => $this->lang->line('password_maxlenght'),
					'regex' => $this->lang->line('reg_check'),
				));

				$this->form_validation->set_rules('role', 'Role', 'required', array('required' => $this->lang->line('role_required')));
				$this->form_validation->set_rules('singup_for', 'Personal Account / For family member', 'required', array('required' => $this->lang->line('signupfor_required')));
				$this->form_validation->set_rules('gender', 'Select gender', 'required', array('required' => $this->lang->line('gender_required')));
				// $this->form_validation->set_rules('city','City', 'required', array( 'required' => $this->lang->line('city_required')));
				//$this->form_validation->set_rules('state','State', 'required', array( 'required' => $this->lang->line('state_required')));
				// $this->form_validation->set_rules('country','Country', 'required', array( 'required' => $this->lang->line('country_required')));
				//$this->form_validation->set_rules('address','Address', 'required', array( 'required' => $this->lang->line('address_required')));
				//$this->form_validation->set_rules('date_of_birth','DOB', 'required', array( 'required' => $this->lang->line('dob_required')));
				if ($this->input->post('role') == 3) {
					// $this->form_validation->set_rules('emergency_contact_person','Emergency person', 'required', array( 'required' => $this->lang->line('emer_person_required')));
					//$this->form_validation->set_rules('emergency_contact_no','Emergency person', 'required', array( 'required' => $this->lang->line('emer_contact_required')));

				}
				if ($this->form_validation->run() == FALSE) {
					$arg['status'] = 0;
					$arg['error_code'] = 0;
					$arg['error_line'] = __line__;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {
					$role = $this->input->post('role');
					$role2 = ($role == 3) ? 4 : 3;
					$singup_for = $this->input->post('singup_for');
					$name = $this->input->post('name');
					$lastname = $this->input->post('lastname');
					$email = $this->input->post('email');
					$mobile = $this->input->post('mobile');
					$gender = $this->input->post('gender');
					$date_of_birth = $this->input->post('date_of_birth');
					$date_of_birth = $date_of_birth ? $date_of_birth : '2001-1-1';
					$address = $this->input->post('address');
					$address = $address ? $address : '';
					$city = $this->input->post('city');
					$city = $city ? $city : '';
					$state = $this->input->post('state');
					$state = $state ? $state : '';
					$country = $this->input->post('country');
					$country = $country ? $country : '';
					$country_code = $this->input->post('country_code');
					$country_code = $country_code ? $country_code : '';
					$lat = $this->input->post('lat');
					$lat = $lat ? $lat : '';
					$lang = $this->input->post('lang');
					$lang = $lang ? $lang : '';
					// $lat =  $this->input->get_request_header('lat', true);
					// $lang =  $this->input->get_request_header('lang', true);
					$zipcode = $this->input->post('zipcode');
					$zipcode = $zipcode ? $zipcode : '';
					$referred_by = $this->input->post('referred_by');
					$street = $this->input->post('street');
					$street = (!empty($street)) ? $street : '';

					$about = $this->input->post('about');
					$emergency_contact_person = $this->input->post('emergency_contact_person');
					$emergency_contact_person = $emergency_contact_person ? $emergency_contact_person : '';

					$emergency_contact_no = $this->input->post('emergency_contact_no');
					$emergency_contact_no = $emergency_contact_no ? $emergency_contact_no : '';

					$emergency_country_code = $this->input->post('emergency_country_code');
					$emergency_country_code = $emergency_country_code ? $emergency_country_code : '';

					$skills = $this->input->post('skills');
					$total_experience = $this->input->post('experience');
					$hashed_password = encrypt_password($this->input->post('password'));

					$where = array('email' => $email);
					$result = $this->dynamic_model->check_user_role($email, $role, 1, $role2);
					//print_r($result);die;

					if (!empty($result)) {
						$arg['status'] = 0;
						$arg['error_code'] = REST_Controller::HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['message'] = $this->lang->line('already_register');
						$arg['data'] = json_decode('{}');
					} else {
						$image = 'userdefault.png';
						$default_img = $name ? $name : 'u';
						$default_img = strtolower(substr($default_img, 0, 1));
						$image = $default_img . '.png';

						if (!empty($_FILES['image']['name'])) {
							$image = $this->dynamic_model->fileupload('image', 'uploads/user');
						}

						$notification = '{"app_notification":"1","alerts":"1","email":"1","sms":"1","phonecall":"1"}';
						$time = time();
						$uniquemail = getuniquenumber();
						$uniquemobile = rand(0001, 9999);

						$this->load->library('user_agent');
						$mobile_otp = '0';
						$email_verified = '0';
						if ($this->agent->is_browser()) {
							$mobile_otp = '1';
						} else {
							$email_verified = '1';
						}
						$userdata = array('name' => $name, 'lastname' => $lastname, 'password' => $hashed_password, 'email' => $email, 'mobile' => $mobile, 'profile_img' => $image, 'status' => 'Deactive', 'gender' => $gender, 'date_of_birth' => $date_of_birth, 'address' => $address, 'city' => $city, 'state' => $state, 'country' => $country, 'lat' => $lat, 'lang' => $lang, 'zipcode' => $zipcode, 'singup_for' => $singup_for, 'referred_by' => $referred_by, 'emergency_contact_person' => $emergency_contact_person, 'emergency_contact_no' => $emergency_contact_no, 'email_verified' => $email_verified, 'mobile_verified' => $mobile_otp, 'mobile_otp' => $uniquemobile, 'mobile_otp_date' => $time, 'create_dt' => $time, 'update_dt' => $time, 'notification' => $notification, 'location' => $street, 'country_code' => $country_code, 'emergency_country_code' => $emergency_country_code);
						$newuserid = $this->dynamic_model->insertdata('user', $userdata);
						if ($newuserid) {
							$roledata = array(
								'user_id' => $newuserid,
								'role_id' => $role,
								'create_dt' => $time,
								'update_dt' => $time,
							);
							$roleid = $this->dynamic_model->insertdata('user_role', $roledata);
							//if role instructor than userId also insert in instructor_details table
							if ($role == 4) {
								$instructor_data = array('user_id' => $newuserid, 'about' => $about, 'create_dt' => $time, 'update_dt' => $time, 'skill' => $skills, 'total_experience' => $total_experience);
								$this->dynamic_model->insertdata('instructor_details', $instructor_data);
							}
							$where = array('id' => $newuserid);
							$findresult = $this->dynamic_model->getdatafromtable('user', $where);
							$name = ucwords($findresult[0]['name']);

							//Send Email Code
							$enc_user = encode($newuserid);
							$enc_role = encode($time);
							$url = site_url() . 'webservices/api/verify_user?encid=' . $enc_user . '&batch=' . $enc_role;
							$link = '<a href="' . $url . '"> Click here </a>, Mobile Verification Code: ' . $uniquemobile;

							$where1 = array('slug' => 'sucessfully_registration');
							$template_data = $this->dynamic_model->getdatafromtable('manage_notification_mail', $where1);
							$desc = str_replace('{USERNAME}', $name, $template_data[0]['description']);
							$desc_data = str_replace('{URL}', $link, $desc);
							$desc_send = str_replace('{SITE_TITLE}', SITE_TITLE, $desc_data);
							$subject = str_replace('{SITE_TITLE}', SITE_TITLE, $template_data[0]['subject']);
							$emailsubject = 'Thank you for registering with ' . SITE_TITLE;
							$data['subject'] = $subject;
							$data['description'] = $desc_send;
							$data['body'] = "";
							$msg = $this->load->view('emailtemplate', $data, true);
							//$this->sendmail->sendmailto($email,$emailsubject,"$msg");
							sendEmailCI("$email", SITE_TITLE, $emailsubject, $msg);
							//Send Email Code

							//send otp thirdparty
							$message = "Your ".SITE_NAME." one time verificatiob code is " . $uniquemobile;
							$smsarray = array('phone' => $country_code . $mobile, 'message' => $message);

							send_sms($smsarray);

							//code

							$data_val = getuserdetail($newuserid, $role);

							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('thank_msg1');
							$arg['data'] = $data_val;
						}

					}
				}
			}
			echo json_encode($arg);
		}
	}
	public function register_old_post() {
		$arg = array();
		if ($_POST) {
			$version_result = version_check_helper1();
			if ($version_result['status'] != 1) {
				$arg = $version_result;
			} else {
				$this->form_validation->set_rules('name', 'Name', 'required|trim', array('required' => $this->lang->line('first_name')));
				$this->form_validation->set_rules('lastname', 'Last Name', 'required|trim', array('required' => $this->lang->line('last_name')));
				$this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[user.email]', array('required' => $this->lang->line('email_required'), 'valid_email' => $this->lang->line('email_valid'), 'is_unique' => $this->lang->line('email_unique'),
				));
				$this->form_validation->set_rules('mobile', 'Mobile', 'required|min_length[8]|max_length[20]|numeric|is_unique[user.mobile]', array(
					'required' => $this->lang->line('mobile_required'),
					'min_length' => $this->lang->line('mobile_min_length'),
					'max_length' => $this->lang->line('mobile_max_length'),
					'numeric' => $this->lang->line('mobile_numeric'),
				));
				$this->form_validation->set_rules('password', 'Password', 'required|min_length[8]|max_length[20]|regex', array(
					'required' => $this->lang->line('password_required'),
					'min_length' => $this->lang->line('password_minlength'),
					'max_length' => $this->lang->line('password_maxlenght'),
					'regex' => $this->lang->line('reg_check'),
				));

				$this->form_validation->set_rules('role', 'Role', 'required', array('required' => $this->lang->line('role_required')));
				$this->form_validation->set_rules('singup_for', 'Personal Account / For family member', 'required', array('required' => $this->lang->line('signupfor_required')));
				$this->form_validation->set_rules('gender', 'Select gender', 'required', array('required' => $this->lang->line('gender_required')));
				$this->form_validation->set_rules('city', 'City', 'required', array('required' => $this->lang->line('city_required')));
				$this->form_validation->set_rules('state', 'State', 'required', array('required' => $this->lang->line('state_required')));
				$this->form_validation->set_rules('country', 'Country', 'required', array('required' => $this->lang->line('country_required')));
				$this->form_validation->set_rules('address', 'Address', 'required', array('required' => $this->lang->line('address_required')));
				$this->form_validation->set_rules('date_of_birth', 'DOB', 'required', array('required' => $this->lang->line('dob_required')));
				if ($this->input->post('role') == 3) {
					$this->form_validation->set_rules('emergency_contact_person', 'Emergency person', 'required', array('required' => $this->lang->line('emer_person_required')));
					$this->form_validation->set_rules('emergency_contact_no', 'Emergency person', 'required', array('required' => $this->lang->line('emer_contact_required')));
				}
				if ($this->form_validation->run() == FALSE) {
					$arg['status'] = 0;
					$arg['error_code'] = 0;
					$arg['error_line'] = __line__;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {
					$role = $this->input->post('role');
					$singup_for = $this->input->post('singup_for');
					$name = $this->input->post('name');
					$lastname = $this->input->post('lastname');
					$email = $this->input->post('email');
					$mobile = $this->input->post('mobile');
					$gender = $this->input->post('gender');
					$date_of_birth = $this->input->post('date_of_birth');
					$address = $this->input->post('address');
					$city = $this->input->post('city');
					$state = $this->input->post('state');
					$country = $this->input->post('country');
					$country_code = $this->input->post('country_code');
					$lat = $this->input->post('lat');
					$lang = $this->input->post('lang');
					// $lat =  $this->input->get_request_header('lat', true);
					// $lang =  $this->input->get_request_header('lang', true);
					$zipcode = $this->input->post('zipcode');
					$referred_by = $this->input->post('referred_by');
					$street = $this->input->post('street');
					$about = $this->input->post('about');
					$emergency_contact_person = $this->input->post('emergency_contact_person');
					$emergency_contact_no = $this->input->post('emergency_contact_no');
					$emergency_country_code = $this->input->post('emergency_country_code');
					$hashed_password = encrypt_password($this->input->post('password'));

					$where = array('email' => $email);
					$result = $this->dynamic_model->getdatafromtable('user', $where);
					if (!empty($result)) {

						$arg['status'] = 0;
						$arg['error_code'] = REST_Controller::HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['message'] = $this->lang->line('already_register');
						$arg['data'] = array();

					} else {

						$image = 'userdefault.png';
						if (!empty($_FILES['image']['name'])) {
							$image = $this->dynamic_model->fileupload('image', 'uploads/user');
						}

						$notification = '{"app_notification":"1","alerts":"1","email":"1","sms":"1","phonecall":"1"}';
						$time = time();
						$uniquemail = getuniquenumber();
						$uniquemobile = rand(0001, 9999);
						$userdata = array('name' => $name, 'lastname' => $lastname, 'password' => $hashed_password, 'email' => $email, 'mobile' => $mobile, 'role_id' => $role, 'profile_img' => $image, 'status' => 'Deactive', 'gender' => $gender, 'date_of_birth' => $date_of_birth, 'address' => $address, 'city' => $city, 'state' => $state, 'country' => $country, 'lat' => $lat, 'lang' => $lang, 'zipcode' => $zipcode, 'singup_for' => $singup_for, 'referred_by' => $referred_by, 'emergency_contact_person' => $emergency_contact_person, 'emergency_contact_no' => $emergency_contact_no, 'email_verified' => '0', 'mobile_verified' => '0', 'mobile_otp' => $uniquemobile, 'mobile_otp_date' => $time, 'create_dt' => $time, 'update_dt' => $time, 'notification' => $notification, 'location' => $street, 'country_code' => $country_code, 'emergency_country_code' => $emergency_country_code);
						$newuserid = $this->dynamic_model->insertdata('user', $userdata);
						if ($newuserid) {
							//if role instructor than userId also insert in instructor_details table
							if ($role == 4) {
								$instructor_data = array('user_id' => $newuserid, 'about' => $about, 'create_dt' => $time, 'update_dt' => $time);
								$this->dynamic_model->insertdata('instructor_details', $instructor_data);
							}
							$where = array('id' => $newuserid);
							$findresult = $this->dynamic_model->getdatafromtable('user', $where);
							$name = ucwords($findresult[0]['name']);

							//Send Email Code
							$enc_user = encode($newuserid);
							$enc_role = encode($time);
							$url = site_url() . 'webservices/api/verify_user?encid=' . $enc_user . '&batch=' . $enc_role;
							$link = '<a href="' . $url . '"> Click here </a>';

							$where1 = array('slug' => 'sucessfully_registration');
							$template_data = $this->dynamic_model->getdatafromtable('manage_notification_mail', $where1);
							$desc = str_replace('{USERNAME}', $name, $template_data[0]['description']);
							$desc_data = str_replace('{URL}', $link, $desc);
							$desc_send = str_replace('{SITE_TITLE}', SITE_TITLE, $desc_data);
							$subject = str_replace('{SITE_TITLE}', SITE_TITLE, $template_data[0]['subject']);
							$emailsubject = 'Thank you for registering with ' . SITE_TITLE;
							$data['subject'] = $subject;
							$data['description'] = $desc_send;
							$data['body'] = "";
							$msg = $this->load->view('emailtemplate', $data, true);
							//$this->sendmail->sendmailto($email,$emailsubject,"$msg");
							sendEmailCI("$email", SITE_TITLE, $emailsubject, $msg);
							//Send Email Code

							//send otp thirdparty
							//code

							$data_val = getuserdetail($newuserid);

							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('thank_msg1');
							$arg['data'] = $data_val;
						}

					}
				}
			}
			echo json_encode($arg);
		}
	}
	/****************Function resend_otp**********************************
		     * @type            : Function
		     * @Author          : Aamir
		     * @function name   : resend_otp
		     * @description     : Resend otp on registered mobile number email.
		     * @param           : null
		     * @return          : null
	*/
	public function resend_otp_get() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userid = $this->input->get_request_header('userid', true);
			$userdata = getuserdetail($userid);
			if ($userdata) {
				$uniquemobile = rand(0001, 9999);
				$where = array('id' => $userdata['id']);
				$tokenupdate = array('mobile_otp' => $uniquemobile);
				$varify = $this->dynamic_model->updateRowWhere('user', $where, $tokenupdate);

				// user otp code thirdparty

				$arg['status'] = 1;
				$arg['error_code'] = REST_Controller::HTTP_OK;
				$arg['error_line'] = __line__;
				$arg['data'] = $userdata;
				$arg['message'] = $this->lang->line('otp_send');
			} else {
				$arg['status'] = 0;
				$arg['error_code'] = REST_Controller::HTTP_OK;
				$arg['error_line'] = __line__;
				$arg['message'] = $this->lang->line('invalid_detail');
				$arg['data'] = json_decode('{}');
			}
		}

		echo json_encode($arg);
	}

	/****************Function verify**********************************
		     * @type            : Function
		     * @Author          : Aamir
		     * @function name   : verify_user
		     * @description     : Verify email.
		     * @param           : null
		     * @return          : null
	*/
	public function verify_user_get() {
		$enc = $_GET['encid'];
		$role = decode($_GET['batch']);
		$userid = decode($enc);
		$where = array('id' => $userid);
		$findresult = $this->dynamic_model->getdatafromtable('user', $where);
		if (!empty($findresult)) {
			$email_verified = $findresult[0]['email_verified'];
			$create_dt = $findresult[0]['create_dt'];
			header("Content-Type: text/html");
			// echo $role .'=='. $create_dt; die;

			//if($role == $create_dt){
			if ($email_verified == 1) {
				$data['email_verified'] = 'Already verified';
				$data['email_status'] = '1';
				$this->load->view('content/verify', $data);
			} else {
				$where1 = array('email' => $findresult[0]['email']);

				//mobile no already verified then status active
				$mobile_verified = $findresult[0]['mobile_verified'];
				if ($mobile_verified == '1') {
					$data = array('email_verified' => "1", 'status' => "Active");
				} else {
					$data = array('email_verified' => "1");
				}
				$varify = $this->dynamic_model->updateRowWhere('user', $where1, $data);

				$data['email_verified'] = 'Verify successfully';
				$data['email_status'] = '1';
				$site_url = site_url();
				$site_url = str_replace('/superadmin', '/signin', $site_url);
				header('Location: ' . $site_url);
				exit;

				//$this->load->view('content/verify',$data);
			}
			/*} else {
				$data['email_verified']='Not Verify Please Try again Later';
				$data['email_status']='';
				$this->load->view('content/verify',$data);
			*/
		} else {
			$data['email_verified'] = 'Not Verify Please Try again Later';
			$data['email_status'] = '';
			$this->load->view('content/verify', $data);
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
	*/
	public function login_post() {
		$arg = array();
		$_POST = json_decode(file_get_contents("php://input"), true);
		if ($_POST) {
			$version_result = version_check_helper1();
			if ($version_result['status'] != 1) {
				$arg = $version_result;
			} else {
				$role_id = $this->input->post('role');
				$this->form_validation->set_rules('email', 'Email', 'required', array('required' => $this->lang->line('email_required'),
				));
				$this->form_validation->set_rules('password', '', 'required|min_length[8]|max_length[20]|regex', array(
					'required' => $this->lang->line('password_required'),
					'min_length' => $this->lang->line('password_minlength'),
					'max_length' => $this->lang->line('password_maxlenght'),
					'regex' => $this->lang->line('reg_check'),
				));
				if ($_POST['device_type'] !== 'Browser') {
					$this->form_validation->set_rules('device_token', 'Device Token', 'required', array('required' => $this->lang->line('device_token_required'),
					));
				}
				$this->form_validation->set_rules('device_type', 'Device Type', 'required', array('required' => $this->lang->line('device_type_required'),
				));
				if ($role_id == 3) {
					$this->form_validation->set_rules('role', 'Role ', 'required', array('required' => $this->lang->line('role_required'),
					));
				}
				if ($this->form_validation->run() == FALSE) {
					$arg['status'] = 0;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {
					$role_id = ($role_id !== '') ? $role_id : 3;
					$role2 = ($role_id == 3) ? 4 : 3;
					$email = $this->input->post('email');
					$time = time();
					$where = array('email' => $email);
					$data = $this->dynamic_model->check_user_role($email, $role_id, 1, $role2);

					if (!empty($data)) {
						$userid = $data[0]['id'];
						//Insert data in user role table
						if (count($data) !== 2) {
							if ($role_id !== $data[0]['role_id']) {
								$role = ($data[0]['role_id'] == 3) ? 4 : 3;
								$roledata = array(
									'user_id' => $userid,
									'role_id' => $role,
									'create_dt' => $time,
									'update_dt' => $time,
								);
								$roleid = $this->dynamic_model->insertdata('user_role', $roledata);
							}
						}
						$userdata = getuserdetail($userid, $role_id);

						$hashed_password = encrypt_password($this->input->post('password'));
						if ($hashed_password == $data[0]['password']) {
							$emailid = $data[0]['email'];
							$name = $data[0]['name'];
							$token = uniqid();
							if ($userdata) {
								if ($userdata['email_verified'] != 1) {

									$where = array('email' => $emailid);
									$tokenupdate = array('create_dt' => $time);
									$this->dynamic_model->updateRowWhere('user', $where, $tokenupdate);

									//Send Email Code
									$enc_user = encode($userid);
									$enc_role = encode($time);
									$url = site_url() . 'webservices/api/verify_user?encid=' . $enc_user . '&batch=' . $enc_role;
									$link = '<a href="' . $url . '"> Click here </a>';

									$where1 = array('slug' => 'verify_email');
									$template_data = $this->dynamic_model->getdatafromtable('manage_notification_mail', $where1);
									$desc = str_replace('{USERNAME}', $name, $template_data[0]['description']);
									$desc_data = str_replace('{URL}', $link, $desc);
									$desc_send = str_replace('{SITE_TITLE}', SITE_TITLE, $desc_data);
									$subject = str_replace('{SITE_TITLE}', SITE_TITLE, $template_data[0]['subject']);
									$emailsubject = 'Verify Email ' . SITE_TITLE;
									$data['subject'] = $subject;
									$data['description'] = $desc_send;
									$data['body'] = "";
									$msg = $this->load->view('emailtemplate', $data, true);
									//$this->sendmail->sendmailto($email,$emailsubject,"$msg");
									sendEmailCI("$email", SITE_TITLE, $emailsubject, $msg);
									//Send Email Code

									$arg['status'] = 0;
									$arg['error_code'] = EMAILNOTVERIFED;
									$arg['error_line'] = __line__;
									$arg['message'] = $this->lang->line('email_not_varify');
									$arg['data'] = $userdata;
									echo json_encode($arg);
									exit();
								}
								if ($userdata['mobile_verified'] != 1) {
									$arg['status'] = 0;
									$arg['error_code'] = MOBILENOTVERIFIED;
									$arg['error_line'] = __line__;
									$arg['message'] = $this->lang->line('otp_not_verify');
									$arg['data'] = $userdata;
									echo json_encode($arg);
									exit();
								}
								if ($userdata['status'] != 'Active') {
									$arg['status'] = 0;
									$arg['message'] = $this->lang->line('user_deactive');
									$arg['error_code'] = REST_Controller::HTTP_OK;
									$arg['error_line'] = __line__;
									$arg['data'] = json_decode('{}');
									echo json_encode($arg);
									exit();
								}

								$device_id = $this->input->post('device_token');
								$device_type = $this->input->post('device_type');

								$where = array('email' => $emailid);
								$tokenupdate = array('device_token' => $device_id, 'device_type' => $device_type);
								$varify = $this->dynamic_model->updateRowWhere('user', $where, $tokenupdate);
								// $tokendata = array('userid'=>$userid,'token'=>$token);
								// $user_token = base64_encode(json_encode($tokendata));
								$arg['status'] = 1;
								$arg['error_code'] = REST_Controller::HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('login_success');
								$arg['data'] = $userdata;

							} else {
								$arg['status'] = 0;
								$arg['message'] = $this->lang->line('invalid_detail');
								$arg['error_code'] = REST_Controller::HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['data'] = json_decode('{}');

							}

						} else {
							$arg['status'] = 0;
							$arg['message'] = $this->lang->line('password_notmatch');
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = json_decode('{}');
						}
					} else {
						$arg['status'] = 0;
						$arg['message'] = $this->lang->line('register_first');
						$arg['error_code'] = REST_Controller::HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['data'] = json_decode('{}');
					}
				}
			}
			echo json_encode($arg);
		}
	}
	public function clover_card_save_post() {

		$arg           = array();
		$_POST         = json_decode(file_get_contents("php://input"), true);
		$usid          = $this->input->post('user_id');
		$savecard      = 1;
		$customer_name = $this->input->post('name');
		$number        = $this->input->post('number');
		$expiry_month  = $this->input->post('expiry_month');
		$expiry_year   = $this->input->post('expiry_year');
		$cvd           = $this->input->post('cvv');
		$country_code  = $this->input->post('country_code');
		$business_id   = $this->input->post('business_id');
		$token         = $this->input->post('card_token');

		$response = clover_api_card_profile_check($usid,$savecard,$customer_name,$number,$expiry_month,$expiry_year,$cvd,$country_code,$business_id,$token);

		// print_r($res); die;
		if ($response['marchant_id'] != '')
		{
			$arg['status'] = 1;
			$arg['error_code'] = REST_Controller::HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['message'] = 'Card Info Successfully Added';
		} else {
			$arg['status'] = 0;
			$arg['error_code'] = 0;
			$arg['error_line'] = __line__;
			$arg['message'] = 'Card Not Added';
		}
		echo json_encode($arg);
	}

	public function cardSave_post() {

		$arg = array();
		$_POST = json_decode(file_get_contents("php://input"), true);
		$userid = $this->input->post('userid');
		$comments = $this->input->post('comments');
		$name = $this->input->post('name');
		$code = $this->input->post('code');
		$id = $this->input->post('id');

		$comments = $comments;
		$name = $name;
		$code = $code;

		$where = array('user_id' => $userid);
		$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
		if (!empty($result_card)) {
			$id = $result_card[0]['card_id'];
		}

		if (!empty($id)) {
			$url = "https://api.na.bambora.com/v1/profiles/$id/cards";
			$legato_token_data = array(
				'token' => array('name' => $name,
					'code' => $code),
			);
		} else {
			$url = 'https://api.na.bambora.com/v1/profiles';
			$legato_token_data = array(
				'language' => 'en',
				'comments' => $comments,
				'token' => array('name' => $name,
					'code' => $code),
			);
		}

		$res = $this->bomborapay->profile_create('POST', $url, $legato_token_data);
		// print_r($res); die;
		if (($res['code'] == '1') && (empty($id))) {
			$transaction_data = array('user_id' => $userid,
				'card_id' => $res['customer_code'],
			);
			$transaction_id = $this->dynamic_model->insertdata('user_card_save', $transaction_data);

			$arg['status'] = 1;
			$arg['error_code'] = REST_Controller::HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['message'] = $res['message'];
		} else if ($res['code'] == '1') {
			$arg['status'] = 1;
			$arg['error_code'] = REST_Controller::HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['message'] = $res['message'];
		} else {
			$arg['status'] = 0;
			$arg['error_code'] = 0;
			$arg['error_line'] = __line__;
			$arg['message'] = $res['message'];
		}
		echo json_encode($arg);
	}

	public function login_old_post() {
		$arg = array();
		$_POST = json_decode(file_get_contents("php://input"), true);
		if ($_POST) {
			$version_result = version_check_helper1();
			if ($version_result['status'] != 1) {
				$arg = $version_result;
			} else {
				$role_id = $this->input->post('role');
				$this->form_validation->set_rules('email', 'Email', 'required', array('required' => $this->lang->line('email_required'),
				));
				$this->form_validation->set_rules('password', '', 'required|min_length[8]|max_length[20]|regex', array(
					'required' => $this->lang->line('password_required'),
					'min_length' => $this->lang->line('password_minlength'),
					'max_length' => $this->lang->line('password_maxlenght'),
					'regex' => $this->lang->line('reg_check'),
				));
				$this->form_validation->set_rules('device_token', 'Device Token', 'required', array('required' => $this->lang->line('device_token_required'),
				));
				$this->form_validation->set_rules('device_type', 'Device Type', 'required', array('required' => $this->lang->line('device_type_required'),
				));
				if ($role_id == 3) {
					$this->form_validation->set_rules('role', 'Role ', 'required', array('required' => $this->lang->line('role_required'),
					));
				}
				if ($this->form_validation->run() == FALSE) {
					$arg['status'] = 0;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {
					$email = $this->input->post('email');
					$where = array('email' => $email);
					$data = $this->dynamic_model->getdatafromtable('user', $where);
					if (!empty($data)) {
						// if($role_id==3 || $role_id==4){
						// 	if($data[0]['role_id'] !==$role_id){
						//         $arg['status']     = 0;
						// 		$arg['error_code']  = 0;
						// 		$arg['error_line']= __line__;
						// 		$arg['message']    = $this->lang->line('role_error');
						// 		$arg['data']     =  json_decode('{}');
						// 		echo json_encode($arg);
						// 		exit();
						//         }
						//        }
						$hashed_password = encrypt_password($this->input->post('password'));
						if ($hashed_password == $data[0]['password']) {
							$userid = $data[0]['id'];
							$emailid = $data[0]['email'];
							$token = uniqid();
							$userdata = getuserdetail($userid);

							if ($userdata) {

								if ($userdata['email_verified'] != 1) {
									$arg['status'] = 0;
									$arg['error_code'] = EMAILNOTVERIFED;
									$arg['error_line'] = __line__;
									$arg['message'] = $this->lang->line('email_not_varify');
									$arg['data'] = $userdata;
									echo json_encode($arg);
									exit();
								}
								if ($userdata['mobile_verified'] != 1) {
									$arg['status'] = 0;
									$arg['error_code'] = MOBILENOTVERIFIED;
									$arg['error_line'] = __line__;
									$arg['message'] = $this->lang->line('otp_not_verify');
									$arg['data'] = $userdata;
									echo json_encode($arg);
									exit();
								}
								if ($userdata['status'] != 'Active') {
									$arg['status'] = 0;
									$arg['message'] = $this->lang->line('user_deactive');
									$arg['error_code'] = REST_Controller::HTTP_OK;
									$arg['error_line'] = __line__;
									$arg['data'] = json_decode('{}');
									echo json_encode($arg);
									exit();
								}
								$device_id = $this->input->post('device_token');
								$device_type = $this->input->post('device_type');

								$where = array('email' => $emailid);
								$tokenupdate = array('device_token' => $device_id, 'device_type' => $device_type);
								$varify = $this->dynamic_model->updateRowWhere('user', $where, $tokenupdate);

								$time = time();
								$tokendata = array('userid' => $userid, 'token' => $token, 'status' => $data[0]['status']);
								$user_token = base64_encode(json_encode($tokendata));

								$arg['status'] = 1;
								$arg['error_code'] = REST_Controller::HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('login_success');
								$arg['data'] = $userdata;

							} else {
								$arg['status'] = 0;
								$arg['message'] = $this->lang->line('invalid_detail');
								$arg['error_code'] = REST_Controller::HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['data'] = json_decode('{}');

							}

						} else {
							$arg['status'] = 0;
							$arg['message'] = $this->lang->line('password_notmatch');
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = json_decode('{}');
						}
					} else {
						$arg['status'] = 0;
						$arg['message'] = $this->lang->line('register_first');
						$arg['error_code'] = REST_Controller::HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['data'] = json_decode('{}');
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
	*/
	public function logout_get() {
		$arg = array();
		$arg['status'] = 1;
		$arg['error_code'] = REST_Controller::HTTP_OK;
		$arg['error_line'] = __line__;
		$arg['message'] = check_authorization('logout');
		$arg['data'] = array();
		echo json_encode($arg);
	}

	/****************Function changepassword**********************************
		     * @type            : Function
		     * @function name   : changepassword
		     * @description     : check the old password and replace it with new one.
		     * @param           : null
		     * @return          : null
	*/
	public function changepassword_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$_POST = json_decode(file_get_contents("php://input"), true);
			if ($_POST) {
				$this->form_validation->set_rules('old_password', 'Old Password', 'trim|required|regex', array(
					'required' => $this->lang->line('old_password'),
				));
				$this->form_validation->set_rules('new_password', 'New Password', 'trim|required|min_length[8]|max_length[20]', array(
					'required' => $this->lang->line('new_password'),
					'min_length' => $this->lang->line('password_minlength'),
					'max_length' => $this->lang->line('password_maxlenght'),
					'regex' => $this->lang->line('reg_check'),
				));
				if ($this->form_validation->run() == FALSE) {
					$arg['status'] = 0;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {

					$userdata = checkuserid();
					if ($userdata['status'] == 0) {
						$arg['status'] = 0;
						$arg['error_code'] = REST_Controller::HTTP_NOT_MODIFIED;
						$arg['error_line'] = __line__;
						$arg['message'] = $userdata['message'];
						$arg['data'] = json_decode('{}');
						echo json_encode($arg);
						exit();
					}
					$userid = $userdata['data']['id'];
					if ($userdata['status'] == 1) {
						$hashed_password = encrypt_password($this->input->post('old_password'));
						if ($hashed_password == $userdata['data']['password']) {
							$data1 = array('password' => encrypt_password($this->input->post('new_password')));
							$where = array("id" => $userid);
							$keyUpdate = $this->dynamic_model->updateRowWhere("user", $where, $data1);
							if ($keyUpdate) {
								$arg['status'] = 1;
								$arg['error_code'] = REST_Controller::HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('password_change_success');
								$arg['data'] = $userdata['data'];
							} else {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_MODIFIED;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('password_not_update');
								$arg['data'] = json_decode('{}');
							}
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_MODIFIED;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('old_password_not');
							$arg['data'] = json_decode('{}');
						}
					} else {
						$arg = $data_val;
					}
				}
			}

		}
		echo json_encode($arg);
	}

	public function demo_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$_POST = json_decode(file_get_contents("php://input"), true);
			if ($_POST) {
				$this->form_validation->set_rules('first_name', 'First Name', 'trim|required', array(
					'required' => $this->lang->line('first_name'),
				));
				$this->form_validation->set_rules('last_name', 'Last Name', 'trim|required', array(
					'required' => $this->lang->line('last_name'),
				));
				$this->form_validation->set_rules('email', 'Email', 'required|valid_email', array('required' => $this->lang->line('email_required'), 'valid_email' => $this->lang->line('email_valid'),
				));
				$this->form_validation->set_rules('business_name', 'Business Name', 'trim|required', array(
					'required' => $this->lang->line('business_name'),
				));
				$this->form_validation->set_rules('phone', 'Phone Number', 'trim|required', array(
					'required' => $this->lang->line('phone'),
				));
				$this->form_validation->set_rules('comment', 'Comment', 'trim|required', array(
					'required' => $this->lang->line('comment'),
				));

				if ($this->form_validation->run() == FALSE) {
					$arg['status'] = 0;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {

					$first_name = $this->input->post('first_name');
					$last_name = $this->input->post('last_name');
					$email = $this->input->post('email');
					$business_name = $this->input->post('business_name');
					$phone = $this->input->post('phone');
					$comment = $this->input->post('comment');

					$requestData = array(
						'first_name' => $first_name,
						'last_name' => $last_name,
						'business_name' => $business_name,
						'email' => $email,
						'phone' => $phone,
						'comment' => $comment,
						'created_on' => time(),
					);

					$insertId = $this->dynamic_model->insertdata('contact_requests', $requestData);
					if ($insertId) {

						$bodydata = "
                            <tr>
                                <td>First Name: </td>
                                <td>$first_name</td>
                            </tr>
                            <tr>
                                <td>Last Name :</td>
                                <td>$last_name</td>
                            </tr>
                            <tr>
                                <td>Business Name :</td>
                                <td>$business_name</td>
                            </tr>
                            <tr>
                                <td>Email Address :</td>
                                <td>$email</td>
                            </tr>
                            <tr>
                                <td>Phone Number :</td>
                                <td>$phone</td>
                            </tr>
                            <tr>
                                <td>Comment : </td>
                                <td>$comment</td>
                            </tr>
                            ";
						$emailsubject = 'Contact Form Of ' . SITE_TITLE;
						$data['subject'] = $emailsubject;
						$data['description'] = "";
						$data['body'] = $bodydata;
						$msg = $this->load->view('emailtemplate', $data, true);
						sendEmailCI("help@signalhg.com", SITE_TITLE, $emailsubject, $msg);

						$arg['status'] = 1;
						$arg['error_code'] = REST_Controller::HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['message'] = 'Thank you your request has been received.';
					} else {
						$arg['status'] = 0;
						$arg['error_code'] = REST_Controller::HTTP_NOT_MODIFIED;
						$arg['error_line'] = __line__;
						$arg['message'] = $this->lang->line('something_wrong');
					}

				}
			}

		}
		echo json_encode($arg);
	}

	public function help_contact_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$_POST = json_decode(file_get_contents("php://input"), true);
			if ($_POST) {
				$this->form_validation->set_rules('name', 'First Name', 'trim|required', array(
					'required' => $this->lang->line('name_req'),
				));
				$this->form_validation->set_rules('email', 'Email', 'required|valid_email', array('required' => $this->lang->line('email_required'), 'valid_email' => $this->lang->line('email_valid'),
				));
				$this->form_validation->set_rules('phone', 'Phone Number', 'trim|required', array(
					'required' => $this->lang->line('phone'),
				));
				$this->form_validation->set_rules('message', 'Comment', 'trim|required', array(
					'required' => $this->lang->line('msg_required'),
				));

				if ($this->form_validation->run() == FALSE) {
					$arg['status'] = 0;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {

					$first_name = $this->input->post('name');
					$email = $this->input->post('email');
					$phone = $this->input->post('phone');
					$message = $this->input->post('message');

					$requestData = array(
						'name' => $first_name,
						'email' => $email,
						'phone' => $phone,
						'message' => $message,
						'created_on' => time(),
					);

					$insertId = $this->dynamic_model->insertdata('help_and_contact', $requestData);
					if ($insertId) {

						$bodydata = "
                            <tr>
                                <td>Name: </td>
                                <td>$first_name</td>
                            </tr>
                            <tr>
                                <td>Email Address :</td>
                                <td>$email</td>
                            </tr>
                            <tr>
                                <td>Phone Number :</td>
                                <td>$phone</td>
                            </tr>
                            <tr>
                                <td>Message : </td>
                                <td>$message</td>
                            </tr>
                            ";
						$emailsubject = 'Contact Form Of ' . SITE_TITLE;
						$data['subject'] = $emailsubject;
						$data['description'] = "";
						$data['body'] = $bodydata;
						$msg = $this->load->view('emailtemplate', $data, true);
						sendEmailCI("help@signalhg.com", SITE_TITLE, $emailsubject, $msg);

						$arg['status'] = 1;
						$arg['error_code'] = REST_Controller::HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['message'] = 'Thank you for contacting us.';
					} else {
						$arg['status'] = 0;
						$arg['error_code'] = REST_Controller::HTTP_NOT_MODIFIED;
						$arg['error_line'] = __line__;
						$arg['message'] = $this->lang->line('something_wrong');
					}

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
	*/
	public function get_profile_get() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {

			$userdata = checkuserid();

			if ($userdata['status'] == 1) {

				$info = $userdata['data'];

				$roleId = $info['role_id'];

				if ($roleId == 4) {
					if ($info['skill_id'] == null) {
						$info['skill_id'] = '';
					}

					if ($info['skill'] == null) {
						$info['skill'] = '';
					}

					if ($info['about'] == null) {
						$info['about'] = '';
					}

					if ($info['experience'] == null) {
						$info['experience'] = '';
					}
				}

				$arg['status'] = 1;
				$arg['error_code'] = REST_Controller::HTTP_OK;
				$arg['error_line'] = __line__;
				$arg['data'] = $info;
				$arg['message'] = $this->lang->line('profile_details');
			} else {
				$arg = $userdata;
			}
		}

		echo json_encode($arg);
	}

	public function get_switch_user_profile_get() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserprofile();
			if ($userdata['status'] == 1) {
				$info = $userdata['data'];
				$roleId = $info['role_id'];
				if ($roleId == 4) {
					if ($info['skill_id'] == null) {
						$info['skill_id'] = '';
					}
					if ($info['skill'] == null) {
						$info['skill'] = '';
					}
					if ($info['about'] == null) {
						$info['about'] = '';
					}
					if ($info['experience'] == null) {
						$info['experience'] = '';
					}
				}
				$arg['status'] = 1;
				$arg['error_code'] = REST_Controller::HTTP_OK;
				$arg['error_line'] = __line__;
				$arg['data'] = $info;
				$arg['message'] = $this->lang->line('profile_details');
			} else {
				$arg = $userdata;
			}
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
	*/
	public function profile_update_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {

				if (empty($this->input->post())) {
					$arg['status'] = 0;
					$arg['error_code'] = REST_Controller::HTTP_NOT_MODIFIED;
					$arg['error_line'] = __line__;
					$arg['message'] = $this->lang->line('profile_notupdate');
					$arg['data'] = array();
				} else {
					$userid = $userdata['data']['id'];
					$role_id = $userdata['data']['role_id'];
					$userdata = $instructordata = array();

					if (!empty($this->input->post('role_id'))) {
						$role_id = $this->input->post('role_id');
					}

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
					if ($role_id == 4) {

						$check_user = $this->db->get_where('instructor_details', array('user_id' => $userid))->num_rows();

						if (!empty($this->input->post('skills'))) {
							if ($this->input->post('skills') != 'undefined') {
								$instructordata['skill'] = $this->input->post('skills');
							}
						}

						if (!empty($this->input->post('experience'))) {
							$instructordata['total_experience'] = $this->input->post('experience');
						}
						if (!empty($this->input->post('sin'))) {
							$instructordata['sin_no'] = $this->input->post('sin');
						}
						if (!empty($this->input->post('appointment_fees_type'))) {
							$instructordata['appointment_fees_type'] = $this->input->post('appointment_fees_type');
						}
						if (!empty($this->input->post('appointment_fees'))) {
							$instructordata['appointment_fees'] = $this->input->post('appointment_fees');
						}
						if (!empty($this->input->post('about'))) {
							$instructordata['about'] = $this->input->post('about');
						}

						if (isset($_POST['registration'])) {
							$instructordata['registration'] = $this->input->post('registration');
						}

						if ($check_user > 0) {

							$instructorupdate['update_dt'] = time();
							// Instructor details
							$where1 = array('user_id' => $userid);
							$instructorupdate = $this->dynamic_model->updateRowWhere("instructor_details", $where1, $instructordata);

						} else {

							$instructordata['user_id'] = $userid;

							$instructordata['instructor_available_appointment'] = 'weekdays';

							$instructordata['shifts_instructor'] = 'class';

							$instructordata['start_date'] = time();

							$instructordata['substitute_instructor_name'] = '';
							$instructordata['employee_id'] = '';
							$instructordata['employee_contractor'] = '';
							$instructordata['create_dt'] = time();
							$instructordata['created_by'] = $userid;

							$this->db->insert('instructor_details', $instructordata);

						}

					}
					if (!empty($_FILES['image']['name'])) {
						$profile_image = $this->dynamic_model->fileupload('image', 'uploads/user');
						$userdata['profile_img'] = $profile_image;
					}

					$userdata['update_dt'] = time();
					$where = array('id' => $userid);
					$updatedata = $this->dynamic_model->updateRowWhere("user", $where, $userdata);

					if ($updatedata || $instructorupdate) {
						$userdata1 = getuserdetail($userid, $role_id);

						$arg['status'] = 1;
						$arg['error_code'] = REST_Controller::HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['message'] = $this->lang->line('profile_update');
						$arg['data'] = $userdata1;
					} else {
						$arg['status'] = 0;
						$arg['error_code'] = REST_Controller::HTTP_NOT_MODIFIED;
						$arg['error_line'] = __line__;
						$arg['message'] = $this->lang->line('profile_notupdate');
						$arg['data'] = json_decode('{}');
					}
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
	*/
	public function forgot_password_post() {
		$arg = array();
		$_POST = json_decode(file_get_contents("php://input"), true);
		if ($_POST) {
			$version_result = version_check_helper1();
			if ($version_result['status'] != 1) {
				$arg = $version_result;
			} else {
				$role_id = $this->input->post('role');
				$this->form_validation->set_rules('email', 'Email', 'required|valid_email', array('required' => $this->lang->line('email_required'), 'valid_email' => $this->lang->line('email_valid'),
				));
				if ($role_id == 4) {
					$this->form_validation->set_rules('role', 'Role ', 'required', array('required' => $this->lang->line('role_required'),
					));
				}

				if ($this->form_validation->run() == FALSE) {
					$arg['status'] = 0;
					$arg['error_code'] = ERROR_FAILED_CODE;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {
					$time = time();
					$email = $this->input->post('email');
					$condition = array('email' => $email);
					$forcheck = $this->dynamic_model->getdatafromtable('user', $condition);
					if ($forcheck) {
						$role2 = ($role_id == 3) ? 4 : 3;
						$roleExist = $this->dynamic_model->check_user_role($email, $role_id, 1, $role2);
						if (empty($roleExist)) {
							$arg['status'] = 0;
							$arg['error_code'] = 0;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('forgot_msg_role_error');
							$arg['data'] = json_decode('{}');
						} else {
							$email = $roleExist[0]['email'];
							$userid = $roleExist[0]['id'];
							$roleId = $roleExist[0]['role_id'];
							$full_name = ucwords($roleExist[0]['name'] . ' ' . $roleExist[0]['lastname']);
							$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ01234567890123456789!@#$&*!@#$&*!@#$&*";
							$otpnumber = randomPassword(); // substr(str_shuffle( $chars ),0, 14 );
							//$val = getuniquenumber();
							//$otpnumber   = substr($val, 0, 6);
							$where1 = array('slug' => 'forget_password');
							$template_data = $this->dynamic_model->getdatafromtable('manage_notification_mail', $where1);
							$desc = str_replace('{USERNAME}', $full_name, $template_data[0]['description']);
							$desc_data = str_replace('{OTP}', $otpnumber, $desc);
							$desc_send = str_replace('{SITE_TITLE}', SITE_TITLE, $desc_data);
							$subject = str_replace('{SITE_TITLE}', SITE_TITLE, $template_data[0]['subject']);
							$emailsubject = 'Forgot password ' . SITE_TITLE;
							$data['subject'] = $subject;

							$data['description'] = $desc_send;
							$data['body'] = "";
							$msg = $this->load->view('emailtemplate', $data, true);
							//$mailsent=$this->sendmail->sendmailto($email,$subject, "$msg");
							$mailsent = sendEmailCI("$email", SITE_TITLE, $emailsubject, $msg);
							if ($mailsent == 1) {
								$update_data = array('password' => encrypt_password($otpnumber));
								$wheres = array("id" => $userid);
								$updatedata = $this->dynamic_model->updateRowWhere("user", $wheres, $update_data);

								$data_val = array('userid' => "$userid", 'password' => "$otpnumber");
								$arg['status'] = 1;
								$arg['error_code'] = REST_Controller::HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['data'] = $data_val;
								$arg['message'] = $this->lang->line('forgot_send');
							} else {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_MODIFIED;
								$arg['error_line'] = __line__;
								$arg['data'] = json_decode('{}');
								$arg['message'] = $this->lang->line('forgot_otp_not_send');
							}
						}
					} else {
						$arg['status'] = 0;
						$arg['error_code'] = REST_Controller::HTTP_NOT_MODIFIED;
						$arg['error_line'] = __line__;
						$arg['data'] = json_decode('{}');
						$arg['message'] = $this->lang->line('email_not_exist');
					}
				}
			}
		}
		echo json_encode($arg);
	}

	/****************Function verify_otp**************************
		     * @type            : Function
		     * @Author          : Aamir
		     * @function name   : verify_otp
		     * @description     : Verify otp that user get on there register mobile number.
		     * @param           : null
		     * @return          : null
	*/
	public function verify_otp_post() {
		$_POST = json_decode(file_get_contents("php://input"), true);
		if ($_POST) {
			$arg = array();
			$this->form_validation->set_rules('userid', 'User ID', 'required');
			$this->form_validation->set_rules('otp', 'OTP', 'required|max_length[6]');

			if ($this->form_validation->run() == FALSE) {
				$arg['status'] = 0;
				$arg['error_code'] = ERROR_FAILED_CODE;
				$arg['message'] = get_form_error($this->form_validation->error_array());
			} else {
				$usid = $this->input->post('userid');
				$user_otp = $this->input->post('otp');

				$condition = array('id' => $usid);
				$result = getdatafromtable('user', $condition);

				if ($result) {
					$id = $result[0]['id'];
					$mobile_verified = $result[0]['mobile_verified'];
					$mobile_otp_date = $result[0]['mobile_otp_date'];
					$mobile_otp = $result[0]['mobile_otp'];

					if ($mobile_verified == 1) {
						$arg['status'] = 0;
						$arg['error_code'] = ERROR_FAILED_CODE;
						$arg['error_line'] = __line__;
						$arg['message'] = $this->lang->line('already_verify');
						echo json_encode($arg);
						exit();
					}

					if ($user_otp != 1111) {
						if ($mobile_otp != $user_otp) {
							$arg['status'] = 0;
							$arg['error_code'] = ERROR_FAILED_CODE;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('otp_not_match');
							echo json_encode($arg);
							exit();
						}

						$newdate = $mobile_otp_date + 3600;
						if ($newdate < time()) {
							$arg['status'] = 0;
							$arg['error_code'] = ERROR_FAILED_CODE;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('otp_expire');
							echo json_encode($arg);
							exit();
						}
					}
					//email already verified then status active
					$email_verified = $result[0]['email_verified'];
					if ($email_verified == '1') {
						$update_data = array('mobile_verified' => "1", 'status' => "Active");
					} else {
						$update_data = array('mobile_verified' => "1");
					}
					$updatedata = $this->dynamic_model->updateRowWhere("user", $condition, $update_data);
					$data_val = getuserdetail($usid);

					$arg['status'] = 1;
					$arg['error_code'] = REST_Controller::HTTP_OK;
					$arg['error_line'] = __line__;
					$arg['data'] = $data_val;
					$arg['message'] = $this->lang->line('otp_verify');

				} else {
					$arg['status'] = 0;
					$arg['error_code'] = ERROR_FAILED_CODE;
					$arg['error_line'] = __line__;
					$arg['message'] = $this->lang->line('invalid_detail');
				}
			}
			echo json_encode($arg);
		}
	}

	/****************Function notification_on_off****************
		     * @type            : Function
		     * @Author          : Aamir
		     * @function name   : notification_on_off
		     * @description     : notification setting.
		     * @param           : {
									"app_notification":"1",
									"alerts":"1",
									"email":"1",
									"sms":"1",
									"phonecall":"1"
								   }
		     * @return          : null
	*/
	public function notification_on_off12_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$usid = $userdata['data']['id'];
					$notification = json_encode($this->input->post());

					$app_notification = $this->input->post('app_notification');
					$alerts = $this->input->post('alerts');
					$email = $this->input->post('email');
					$sms = $this->input->post('sms');
					$phonecall = $this->input->post('phonecall');

					$where = array("id" => $usid);
					$noti_data = $this->dynamic_model->getdatafromtable("user", $where, 'id,notification');

					$update_data = array('notification' => $notification);
					$updatedata = $this->dynamic_model->updateRowWhere("user", $where, $update_data);

					if ($updatedata) {
						$userdata = checkuserid();
						$arg['status'] = 1;
						$arg['error_code'] = REST_Controller::HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['data'] = $userdata['data'];
						$arg['message'] = $this->lang->line('notification_change_success');
					} else {
						$arg['status'] = 0;
						$arg['error_code'] = REST_Controller::HTTP_NOT_MODIFIED;
						$arg['error_line'] = __line__;
						$arg['message'] = $this->lang->line('not_updated');
					}

				}
			}
		}
		echo json_encode($arg);
	}
	public function notification_on_off_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {

					$msg = '';
					$usid = $userdata['data']['id'];
					$notification = json_encode($this->input->post());

					$app_notification = $this->input->post('app_notification');
					$alerts = $this->input->post('alerts');
					$email = $this->input->post('email');
					$sms = $this->input->post('sms');
					$phonecall = $this->input->post('phonecall');

					$where = array("id" => $usid);
					$noti_data = $this->dynamic_model->getdatafromtable("user", $where, 'id,notification');
					if (!empty($noti_data)) {
						$getnotification = json_decode($noti_data[0]['notification']);
						$appNotification = $getnotification->app_notification;
						$alert = $getnotification->alerts;
						$mail = $getnotification->email;
						$smss = $getnotification->sms;
						$phonecalls = $getnotification->phonecall;
						if ($app_notification !== $appNotification) {
							$msg = $this->lang->line('notification_app_success');

						} elseif ($alerts !== $alert) {
							$msg = $this->lang->line('notification_alerts_success');
						} elseif ($email !== $mail) {
							$msg = $this->lang->line('notification_mail_success');
						} elseif ($sms !== $smss) {
							$msg = $this->lang->line('notification_sms_success');
						} elseif ($phonecall !== $phonecalls) {
							$msg = $this->lang->line('notification_phonecall_success');
						} else {
							$msg = $this->lang->line('notification_change_success');
						}
					}

					$update_data = array('notification' => $notification);
					$updatedata = $this->dynamic_model->updateRowWhere("user", $where, $update_data);

					if ($updatedata) {
						$userdata = checkuserid();
						$arg['status'] = 1;
						$arg['error_code'] = REST_Controller::HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['data'] = $userdata['data'];
						$arg['message'] = $msg;
					} else {
						$arg['status'] = 0;
						$arg['error_code'] = REST_Controller::HTTP_NOT_MODIFIED;
						$arg['error_line'] = __line__;
						$arg['message'] = $this->lang->line('not_updated');
					}
				}
			}
		}
		echo json_encode($arg);
	}
	//Function used for Get Notification List
	public function get_notification_list_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {

					/* if ($this->input->post('status')) {
						$this->load->helper('notification_helper');
						$resp = helpTestNotification();
						$arg['test'] = 'Not working';
						$arg['d'] = $resp;
						echo json_encode($arg); exit;
					} */

					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array('required' => $this->lang->line('page_no'), 'numeric' => $this->lang->line('page_no_numeric')));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error'] = ERROR_FAILED_CODE;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$userid = $userdata['data']['id'];
						$loguser = $this->dynamic_model->get_user_by_id($userid);
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;
						// $last30days= date('Y-m-d',strtotime("-30 days"));

						$condition = array("recepient_id" => $userid);
						$notification_data = $this->dynamic_model->getdatafromtable('notification', $condition, '*', $limit, $offset, 'create_dt', 'DESC');
						$cond = array("is_deleted" => '0', "is_read" => '0', "recepient_id" => $userid);
						$notification_count = $this->dynamic_model->getdatafromtable('notification', $cond, '*');
						if (!empty($notification_data)) {
							$user_data = array();
							$request_data = array();
							foreach ($notification_data as $details) {
								$user_data["id"] = $details['id'];
								$user_data["title"] = $details['title'];
								$user_data["message"] = $details['message'];
								$user_data["name"] = (!empty($loguser['name'])) ? ucwords($loguser['name']) : '';
								$user_data["lastname"] = (!empty($loguser['lastname'])) ? ucwords($loguser['lastname']) : '';
								$user_data["mobile"] = (!empty($loguser['mobile'])) ? $loguser['mobile'] : '';
								$user_data["country_code"] = (!empty($loguser['country_code'])) ? $loguser['country_code'] : '';
								$user_data["types"] = $details['types'];
								$user_data["read"] = $details['is_read'];
								$user_data['created_at'] = date('d M Y', $details['create_dt']);
								$user_data['create_dt_utc'] = $details['create_dt'];
								$notification_array[] = $user_data;
							}
							if ($notification_count) {
								$tot_count = count($notification_count);
							} else {
								$tot_count = "0";
							}

							$notification_data = array('unread_count' => "$tot_count",
								'notification' => $notification_array,
							);
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $notification_data;
							$arg['unread_count'] = "$tot_count";
							$arg['message'] = $this->lang->line('record_found');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							// $arg['data'] = [];
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}
	//Function used for Read Notification Status
	public function read_notification_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('notification_id', 'Notification Id', 'required|numeric', array('required' => $this->lang->line('notification_id_required'), 'numeric' => $this->lang->line('notification_id_numeric')));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error'] = ERROR_FAILED_CODE;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$usid = $userdata['data']['id'];
						$loguser = $this->dynamic_model->get_user_by_id($usid);
						if ($loguser) {
							$notification_id = $this->input->post('notification_id');
							$data1 = array(
								'is_read' => 1,
							);
							$where = array("id" => $notification_id, "recepient_id" => $usid);
							$keyUpdate = $this->dynamic_model->updateRowWhere("notification", $where, $data1);
							// echo $this->db->last_query();die;
							if ($keyUpdate) {
								//"is_read" =>'0'
								$cond = array("recepient_id" => $usid);
								$notification_count = $this->dynamic_model->getdatafromtable('notification', $cond, '*');
								if ($notification_count) {
									$unread_count = count($notification_count);
								} else {
									$unread_count = "0";
								}

								$notification_data = array("id" => $notification_id, "is_read" => 1, "unread_count" => (string) $unread_count);

								$arg['status'] = 1;
								$arg['error_code'] = REST_Controller::HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['data'] = $notification_data;
							} else {

								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_MODIFIED;
								$arg['error_line'] = __line__;
								$arg['data'] = json_decode('{}');
								$arg['message'] = $this->lang->line('not_updated');
							}
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_MODIFIED;
							$arg['error_line'] = __line__;
							$arg['data'] = json_decode('{}');
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}

		echo json_encode($arg);
	}
	/****************Function get_about**************************
		     * @type            : Function
		     * @Author          : Aamir
		     * @function name   : get_about
		     * @description     : Content page for about us.
		     * @param           : term-and-condition/privacy-policies/about-us
		     * @return          : null
	*/

	public function getcontent_post() {
		$_POST = json_decode(file_get_contents("php://input"), true);
		if ($_POST) {
			$arg = array();
			$this->form_validation->set_rules('page_title', 'Title', 'required');

			if ($this->form_validation->run() == FALSE) {
				$arg['status'] = 0;
				$arg['error_code'] = ERROR_FAILED_CODE;
				$arg['message'] = get_form_error($this->form_validation->error_array());
			} else {
				$page_title = $this->input->post('page_title');
				$arg['status'] = 1;
				$arg['error_code'] = REST_Controller::HTTP_OK;
				$arg['error_line'] = __line__;
				$arg['message'] = "";
				$arg['data'] = array('url' => site_url() . 'Welcome/content/' . $page_title);

			}
			echo json_encode($arg);
		}
	}

//--------------------------*************End of Onboard*************---------------------------//

	/****************Function get_categories*********************
		     * @type            : Function
		     * @Author          : Aamir
		     * @function name   : get_categories
		     * @description     : get categories of trainer and business.
		     * @param           : null
		     * @return          : null
	*/
	public function get_categories_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				$type = !empty($this->input->post('type')) ? $this->input->post('type') : '1';
				$subcat = !empty($this->input->post('subcat')) ? $this->input->post('subcat') : '0';
				$business_id = !empty($this->input->post('business_id')) ? $this->input->post('business_id') : '';
				$where = array('id' => $business_id);
				$business_data = $this->dynamic_model->getdatafromtable('business', $where);
				$business_categry = (!empty($business_data[0]['category'])) ? $business_data[0]['category'] : '';

				if (empty($business_id)) {
					$sql = "SELECT id,category_name FROM manage_category
	            WHERE status='Active' AND category_type='$type' AND category_parent='$subcat'
	            ORDER BY id ASC";
				} else {
					$sql = "SELECT id,category_name FROM manage_category
			        WHERE id IN ($business_categry) AND category_type=$type  AND status='Active' AND category_parent='$subcat'";
				}
				$findresult = $this->dynamic_model->get_query_result($sql);
				if ($findresult) {
					$arg['status'] = 1;
					$arg['error_code'] = REST_Controller::HTTP_OK;
					$arg['error_line'] = __line__;
					$arg['data'] = $findresult;
					$arg['message'] = $this->lang->line('record_found');
				} else {
					$arg['status'] = 0;
					$arg['error_code'] = REST_Controller::HTTP_NOT_MODIFIED;
					$arg['error_line'] = __line__;
					$arg['data'] = array();
					$arg['message'] = $this->lang->line('record_not_found');
				}
			}
		}
		echo json_encode($arg);
	}
	/****************Function get_business*********************
		     * @type            : Function
		     * @Author          : Aamir
		     * @function name   : get_business
		     * @description     : get business listing with pagination.
		     * @param           : null
		     * @return          : null
	*/
	public function get_business_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				$page_id = !empty($_POST['pageid']) ? $_POST['pageid'] : '1';
				$search_text = !empty($_POST['search_text']) ? $_POST['search_text'] : '';
				$category = !empty($_POST['category']) ? $_POST['category'] : '0';
				$subcategory = !empty($_POST['subcategory']) ? $_POST['subcategory'] : '';
				//$distance = !empty($_POST['distance']) ? $_POST['distance'] : '50';
				$distance = $_POST['distance'];
				$business_id = !empty($_POST['business_id']) ? $_POST['business_id'] : '';

				$lat = $userdata['data']['lat'];
				$lang = $userdata['data']['lang'];
				$usid = $userdata['data']['id'];
				$lat = !empty($_POST['lat']) ? $_POST['lat'] : $lat;
				$lang = !empty($_POST['lang']) ? $_POST['lang'] : $lang;

				$business_array = array();
				$limit = 10;
				$offset = 10 * $page_id;
				if ($page_id == 1) {
					$offset = 0;
				}

				$business_ids = '';
				if (!empty($category)) {

					if (!empty($subcategory)) {
						$subcatids = explode(',', $subcategory);
						$condition = array("parent_id" => $category, "type" => 1);
					} else {
						$subcatids = '';
						$condition = array("category" => $category, "type" => 1);
					}

					// print_r($subcatids);
					// print_r($condition);
					//die;
					//category
					$getsubcat = $this->dynamic_model->getWhereInData('business_category', 'category', $subcatids, $condition);

					if (!empty($getsubcat)) {
						$subcat = array_column($getsubcat, 'business_id');
						$business_ids = implode(',', $subcat);
					}
				}

				// print_r($business_ids);
				//die;

				$getbusiness = $this->api_model->search_business($business_ids, $lat, $lang, $distance, $search_text, $limit, $offset);
				$distance = '';
				//print_r($this->db->last_query());die;
				if ($getbusiness) {
					foreach ($getbusiness as $keybusiness) {
						$id = $keybusiness->id;
						if (!empty($lat && $lang)) {
							$distance = (@$keybusiness->distance == 0) ? 0 : number_format((float) $keybusiness->distance, 2, '.', '');
						}
						$business_array[] = getbusinessdetails($id, $usid, $distance, $flag = 1);
					}
					$total_count = count($getbusiness);
					$arg['status'] = 1;
					$arg['error_code'] = REST_Controller::HTTP_OK;
					$arg['error_line'] = __line__;
					$arg['data'] = $business_array;
					$arg['total_count'] = "$total_count";
					$arg['message'] = $this->lang->line('record_found');
				} else {
					$arg['status'] = 0;
					$arg['error_code'] = REST_Controller::HTTP_NOT_MODIFIED;
					$arg['error_line'] = __line__;
					$arg['data'] = array();
					$arg['message'] = $this->lang->line('record_not_found');
				}
			}
		}
		echo json_encode($arg);
	}
	public function get_business_search_list_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				$search_text = !empty($_POST['search_text']) ? $_POST['search_text'] : '';
				$usid = $userdata['data']['id'];
				$business_array = array();

				$sql = 'SELECT * FROM business
            WHERE status="Active" AND business_name LIKE "%' . $search_text . '%"
            ORDER BY id ';
				$getbusiness = $this->dynamic_model->get_query_result($sql);
				if ($getbusiness) {
					foreach ($getbusiness as $keybusiness) {
						$business_array[] = array("id" => $keybusiness->id, "business_name" => $keybusiness->business_name);
					}

					$arg['status'] = 1;
					$arg['error_code'] = REST_Controller::HTTP_OK;
					$arg['error_line'] = __line__;
					$arg['data'] = $business_array;
					$arg['message'] = $this->lang->line('record_found');
				} else {
					$arg['status'] = 0;
					$arg['error_code'] = REST_Controller::HTTP_NOT_MODIFIED;
					$arg['error_line'] = __line__;
					$arg['data'] = array();
					$arg['message'] = $this->lang->line('record_not_found');
				}
			}
		}
		echo json_encode($arg);
	}
	/****************Function get_business_detail*********************
		     * @type            : Function
		     * @Author          : Aamir
		     * @function name   : get_business_detail
		     * @description     : get business detail.
		     * @param           : null
		     * @return          : null
	*/
	public function get_business_detail_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] == 1) {

				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$arg = array();
					$this->form_validation->set_rules('business_id', 'Business ID', 'required');

					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = ERROR_FAILED_CODE;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$usid = $userdata['data']['id'];
						$business_id = $_POST['business_id'];
						$business = getbusinessdetails($business_id, $usid, '', '', 'pass_details', 'without_purchase');

						if ($business) {
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $business;
							$arg['message'] = $this->lang->line('record_found');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_MODIFIED;
							$arg['error_line'] = __line__;
							$arg['data'] = json_decode('{}');
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
	/****************Function user_dashboard*********************
		     * @type            : Function
		     * @Author          : Aamir
		     * @function name   : user_dashboard
		     * @description     : get dashboard categories and its business.
		     * @param           : null
		     * @return          : null
	*/
	public function user_dashboard_old_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {

				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page no.', 'required');

					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = ERROR_FAILED_CODE;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$usid = $userdata['data']['id'];
						$lat = $userdata['data']['lat'];
						$lang = $userdata['data']['lang'];
						$page_id = !empty($_POST['pageid']) ? $_POST['pageid'] : '1';
						$lat = !empty($_POST['lat']) ? $_POST['lat'] : $lat;
						$lang = !empty($_POST['lang']) ? $_POST['lang'] : $lang;
						$limit = 10;
						$offset = 10 * $page_id;
						if ($page_id == 1) {
							$offset = 0;
						}

						$where = array('status' => 'Active', 'category_type' => 1);
						$findresult = $this->dynamic_model->getdatafromtable('manage_category', $where, 'id,category_name', $limit, $offset);

						$catarr = array();
						if ($findresult) {
							foreach ($findresult as $keycategory) {
								$catid = $keycategory['id'];
								$category_name = $keycategory['category_name'];
								$getbusiness = $this->api_model->get_business_according_to_distance($catid, $lat, $lang, 50);
								$getbusinessdetail = array();
								$distance = '';
								if ($getbusiness) {
									foreach ($getbusiness as $keybusiness) {
										if (!empty($lat && $lang)) {
											$distance = (@$keybusiness->distance == 0) ? 0 : number_format((float) $keybusiness->distance, 2, '.', '');
										}
										$getbusinessdetail[] = getbusinessdetails($keybusiness->id, $usid, $distance, $flag = 1);
									}
									$catarr[] = array("cat_id" => $catid, "category_name" => $category_name, 'business' => $getbusinessdetail);
								}
							}
							if (!empty($getbusinessdetail)) {
								$arg['status'] = 1;
								$arg['error_code'] = REST_Controller::HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['data'] = $catarr;
								$arg['message'] = $this->lang->line('record_found');
							} else {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_MODIFIED;
								$arg['error_line'] = __line__;
								$arg['data'] = array();
								$arg['message'] = $this->lang->line('record_not_found');
							}
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_MODIFIED;
							$arg['error_line'] = __line__;
							$arg['data'] = array();
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function all_business_details($getbusiness = [], $usid = '', $lat = '', $lang = '') {
		$getbusinessdetail = [];
		$distance = '';
		if ($getbusiness) {
			foreach ($getbusiness as $keybusiness) {
				if (!empty($lat && $lang)) {
					$distance = (@$keybusiness->distance == 0) ? 0 : number_format((float) $keybusiness->distance, 2, '.', '');
				}
				$getbusinessdetail[] = getbusinessdetails($keybusiness->id, $usid, $distance, $flag = 1);
			}
		}
		return $getbusinessdetail;
	}
	public function user_dashboard_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {

				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page no.', 'required');

					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = ERROR_FAILED_CODE;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$usid = $userdata['data']['id'];
						$lat = $userdata['data']['lat'];
						$lang = $userdata['data']['lang'];
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$lat = !empty($_POST['lat']) ? $_POST['lat'] : $lat;
						$lang = !empty($_POST['lang']) ? $_POST['lang'] : $lang;

						$page_no = $page_no - 1;
						//$limit    = config_item('page_data_limit');
						$limit = 10;
						$offset = $limit * $page_no;
						$where = array('status' => 'Active', 'category_type' => 2, 'category_parent !=' => 0);
						$findresult = $this->dynamic_model->getdatafromtable('manage_category', $where, 'id,category_name', $limit, $offset);

						$catarr = $getbusinessdetail = array();
						if ($findresult) {

							foreach ($findresult as $keycategory) {
								$catid = $keycategory['id'];
								$category_name = $keycategory['category_name'];
								$getbusiness = $this->api_model->get_business_according_to_distance($catid, $lat, $lang, 50, 50);
								//print_r($getbusiness); die;
								$getbusinessdetail = $this->all_business_details($getbusiness, $usid, $lat, $lang);

								if (!empty($getbusinessdetail)) {
									$catarr[] = array("cat_id" => $catid, "category_name" => $category_name, 'business' => $getbusinessdetail);
								}
							}

							if (!empty($catarr)) {
								$arg['status'] = 1;
								$arg['error_code'] = REST_Controller::HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['data'] = $catarr;
								$arg['message'] = $this->lang->line('record_found');
							} else {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_MODIFIED;
								$arg['error_line'] = __line__;
								$arg['data'] = array();
								$arg['message'] = $this->lang->line('record_not_found');
							}
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_MODIFIED;
							$arg['error_line'] = __line__;
							$arg['data'] = array();
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}
	/****************Function favourite*********************
		     * @type            : Function
		     * @Author          : Aamir
		     * @function name   : favourite
		     * @description     : favourite business and passes
		     * @param           : null
		     * @return          : null
	*/
	public function favourite_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('2');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {

				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$userid = $this->input->get_request_header('userid', true);
					$this->form_validation->set_rules('service_type', 'Service Type', 'required|trim', array('required' => $this->lang->line('service_type_required')));
					$this->form_validation->set_rules('service_id', 'Service Id', 'required|trim', array('required' => $this->lang->line('service_id_required')));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = ERROR_FAILED_CODE;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$service_id = $this->input->post('service_id');
						$status = $this->input->post('status');
						//1 business 2 passes
						$service_type = $this->input->post('service_type');
						if ($service_type == 1) {
							$where = array('id' => $service_id);
							$findresult = $this->dynamic_model->getdatafromtable('business', $where, 'id');
						} else {
							$where = array('id' => $service_id);
							$findresult = $this->dynamic_model->getdatafromtable('business_passes', $where, 'id');
						}
						if ($findresult) {
							$checkfav = array('service_id' => $service_id, 'user_id' => $userid, 'service_type' => $service_type);
							$favresult = $this->dynamic_model->getdatafromtable('user_business_favourite', $checkfav);

							if ($favresult && ($status == 1)) {
								$newuserid = $this->dynamic_model->deletedata('user_business_favourite', $checkfav);
								$arg['status'] = 1;
								$arg['error_code'] = REST_Controller::HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['data'] = $status;
								$arg['message'] = $this->lang->line('unfavourite');
							} else {
								if ($status) {
									$newuserid = $this->dynamic_model->insertdata('user_business_favourite', $checkfav);
									$arg['status'] = 1;
									$arg['error_code'] = REST_Controller::HTTP_OK;
									$arg['error_line'] = __line__;
									$arg['data'] = $status;
									$arg['message'] = $this->lang->line('favourite_added');
								} else {
									$newuserid = $this->dynamic_model->deletedata('user_business_favourite', $checkfav);
									$arg['status'] = 1;
									$arg['error_code'] = REST_Controller::HTTP_OK;
									$arg['error_line'] = __line__;
									$arg['data'] = $status;
									$arg['message'] = $this->lang->line('unfavourite');
								}
							}
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_MODIFIED;
							$arg['error_line'] = __line__;
							$arg['data'] = '';
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}
	public function favouritelist_post() {
		$arg = array();
		$role = $this->input->get_request_header('role', true);
		$role = $role ? $role : 3;
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$userid = $this->input->get_request_header('userid', true);
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page no.', 'required');
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = ERROR_FAILED_CODE;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$business_array = $pass_arr = array();
						$business_name = $address = $business_id = $skills = $img = $pass_id = $pass_name = $pass_type = $pass_end_date = $purchase_date = $pass_validity = '';
						$page_id = !empty($_POST['pageid']) ? $_POST['pageid'] : '1';
						$limit = '10';
						$offset = $limit * (int) $page_id;
						if ($page_id == 1) {
							$offset = 0;
						}
						$where = array('user_id' => $userid);
						$findresult = $this->dynamic_model->getdatafromtable('user_business_favourite', $where, '*', $limit, $offset);
						//print_r($findresult);die;
						if (!empty($findresult)) {
							foreach ($findresult as $keycategory) {
								//service_type 1 business 2 passes
								$service_id = $keycategory['service_id'];
								$service_type = $keycategory['service_type'];
								if ($service_type == 1) {
									//get business details
									$where1 = array("id" => $service_id, "status" => "Active");
									$busidata = $this->dynamic_model->getdatafromtable('business', $where1);
									$business_name = $busidata[0]['business_name'];
									$business_id = (!empty($busidata[0]['id'])) ? $busidata[0]['id'] : '';
									$business_name = (!empty($busidata[0]['business_name'])) ? $busidata[0]['business_name'] : '';
									$address = (!empty($busidata[0]['address'])) ? $busidata[0]['address'] : '';
									$skills = (!empty($busidata[0]['category'])) ? get_categories($busidata[0]['category']) : '';
									$logo = (!empty($busidata[0]['logo'])) ? $busidata[0]['logo'] : '';
									$img = site_url() . 'uploads/business/' . $logo;
									//if get business data then passes data empty
									$pass_id = $pass_name = $pass_type = $pass_end_date = $purchase_date = $pass_validity = '';
								} elseif ($service_type == 2) {
									//get passes details
									$where2 = array("id" => $service_id, "status" => "Active");
									$passes_data = $this->dynamic_model->getdatafromtable('business_passes', $where2);

									$pass_id = (!empty($passes_data[0]['id'])) ? $passes_data[0]['id'] : '';

									$pass_name = (!empty($passes_data[0]['pass_name'])) ? $passes_data[0]['pass_name'] : '';
									$passType = (!empty($passes_data[0]['pass_type'])) ? $passes_data[0]['pass_type'] : '';
									$pass_type_subcat = (!empty($passes_data[0]['pass_type_subcat'])) ? $passes_data[0]['pass_type_subcat'] : '';
									$pass_type = get_passes_type_name($passType, $pass_type_subcat);

									$purchase_date = (!empty($passes_data[0]['purchase_date'])) ? date("d M Y ", $passes_data[0]['purchase_date']) : '';
									$pass_end_date = (!empty($passes_data[0]['pass_end_date'])) ? date("d M Y ", $passes_data[0]['pass_end_date']) : '';
									$pass_validity = (!empty($passes_data[0]['pass_validity'])) ? $passes_data[0]['pass_validity'] . ' ' . "Month" : '';

									//if get passes data then business data empty
									$business_name = $address = $business_id = $skills = $img = '';
								}
								$allData['service_type'] = $service_type;
								$allData['business_id'] = $business_id;
								$allData['business_name'] = $business_name;
								$allData['business_address'] = $address;
								$allData['skills'] = $skills;
								$allData['logo'] = $img;
								if ($role == '3') {
									$allData['pass_id'] = $pass_id;
									$allData['pass_name'] = $pass_name;
									$allData['pass_type'] = $pass_type;
									$allData['start_date'] = $purchase_date;
									$allData['end_date'] = $pass_end_date;
									$allData['pass_validity'] = $pass_validity;
								}
								$allData['favourite'] = "1";
								$response[] = $allData;
							}
							$arg['status'] = 1;
							$arg['role'] = $role;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $response;
							$arg['message'] = $this->lang->line('record_found');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_MODIFIED;
							$arg['error_line'] = __line__;
							$arg['data'] = array();
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}
	/****************Function Get classes list**********************************
		     * @type            : Function
		     * @Author          : Arpit
		     * @function name   : business_class_list
		     * @description     : list of classes
		     * @param           : null
		     * @return          : null
	*/
	public function class_list_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$response = array();
						$time = time();
						$date = date("Y-m-d", $time);
						$usid = $userdata['data']['id'];
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;
						//0= all classes 1=singned class
						$class_status = $this->input->post('class_status');
						$business_id = $this->input->post('business_id');
						$upcoming_date = $this->input->post('upcoming_date');

						$time_zone = $this->input->get_request_header('Timezone', true);
						$time_zone = $time_zone ? $time_zone : 'UTC';
						date_default_timezone_set($time_zone);
						$upcoming_date_format = date('Y-m-d', $upcoming_date);
						if ($class_status == 0) {

							$query = 'SELECT class_scheduling_time.id, class_scheduling_time.instructor_id as instructor_ids, business_class.id as class_id, class_scheduling_time.location_id, business_location.location_name as location, CASE WHEN business_location.map_url IS NULL THEN "" Else business_location.map_url END as map_url, CASE WHEN business_location.location_url IS NULL THEN "" Else business_location.location_url END as location_url, business_class.class_type, business_class.capacity as total_capacity, class_scheduling_time.day_id, manage_week_days.week_name, class_scheduling_time.from_time, class_scheduling_time.to_time, class_scheduling_time.scheduled_date, business_class.class_name, DATE_FORMAT(business_class.start_date, "%e %b %Y") as start_date, DATE_FORMAT(business_class.end_date, "%e %b %Y") as end_date, business_class.class_type, business_class.duration, business_class.capacity, business_class.create_dt FROM `class_scheduling_time` JOIN business_class on (business_class.id = class_scheduling_time.class_id) LEFT JOIN business_location on (business_location.id = class_scheduling_time.location_id) JOIN manage_week_days on (manage_week_days.id = class_scheduling_time.day_id)  WHERE class_scheduling_time.status = "Active" AND class_scheduling_time.business_id = ' . $business_id . ' AND business_class.business_id= ' . $business_id . ' AND business_class.status="Active"  AND class_scheduling_time.scheduled_date = "' . $upcoming_date_format . '" ORDER BY class_scheduling_time.scheduled_date ASC LIMIT ' . $limit . ' OFFSET ' . $offset;
							//from_time
							$class_data = $this->dynamic_model->getQueryResultArray($query);

						} else {

							$query = 'SELECT class_scheduling_time.id, class_scheduling_time.instructor_id as instructor_ids, business_class.id as class_id, class_scheduling_time.location_id, business_location.location_name as location, CASE WHEN business_location.map_url IS NULL THEN "" Else business_location.map_url END as map_url, CASE WHEN business_location.location_url IS NULL THEN "" Else business_location.location_url END as location_url, business_class.class_type, business_class.capacity as total_capacity, class_scheduling_time.day_id, manage_week_days.week_name, class_scheduling_time.from_time, class_scheduling_time.to_time, class_scheduling_time.scheduled_date, business_class.class_name, DATE_FORMAT(business_class.start_date, "%e %b %Y") as start_date, DATE_FORMAT(business_class.end_date, "%e %b %Y") as end_date, business_class.class_type, business_class.duration, business_class.capacity, business_class.create_dt FROM `user_attendance` JOIN business_class ON (business_class.id = user_attendance.service_id) JOIN class_scheduling_time ON (class_scheduling_time.id = user_attendance.schedule_id) JOIN business_location on (business_location.id = class_scheduling_time.location_id) JOIN manage_week_days on (manage_week_days.id = class_scheduling_time.day_id) where user_attendance.checkin_dt = "' . $upcoming_date_format . '" AND user_attendance.user_id = ' . $usid . ' AND business_class.business_id = ' . $business_id . ' AND class_scheduling_time.status = "Active" AND class_scheduling_time.business_id = ' . $business_id . ' AND business_class.status="Active"  AND class_scheduling_time.scheduled_date = "' . $upcoming_date_format . '" ORDER BY class_scheduling_time.scheduled_date ASC LIMIT ' . $limit . ' OFFSET ' . $offset;
							//from_time
							$class_data = $this->dynamic_model->getQueryResultArray($query);
						}
						//print_r($class_data);die;
						if (!empty($class_data)) {
							foreach ($class_data as $value) {
								$week_date = date("w", $upcoming_date);
								if ($week_date == '0') {
									$week_date = 7;
								}

								$time_slote_from = $value['from_time'];
								$to_time = $value['to_time'];
								$scheduled_date = $value['scheduled_date'];

								$classesdata['schedule_id'] = $value['id'];
								$classesdata['class_id'] = $value['class_id'];
								$classesdata['class_name'] = ucwords($value['class_name']);
								$classesdata['from_time'] = $value['from_time'];
								$classesdata['to_time'] = $value['to_time'];
								$classesdata['from_time_utc'] = $time_slote_from;
								$classesdata['to_time_utc'] = $to_time;
								$classesdata['start_date_utc'] = strtotime($scheduled_date);
								$classesdata['end_date_utc'] = strtotime($scheduled_date);
								$classesdata['duration'] = $value['duration'] . ' minutes';
								$capicty_used = get_checkin_class_or_workshop_daily_count($value['class_id'], 1, date('Y-m-d', $upcoming_date), $value['id']);
								//$value['id']
								//  echo $value['class_id'].'--'.$upcoming_date; die;

								$classesdata['total_capacity'] = $value['total_capacity'];
								$classesdata['capacity_used'] = $capicty_used;
								$status = get_passes_checkin_status($usid, $value['class_id'], 1, $date, $value['id']);
								if ($status == 'singup' OR $status == 'checkin' OR $status == 'checkout') {
									$signed_status = '1';
								} else {
									$signed_status = '0';
								}
								$classesdata['signed_status'] = $signed_status;
								$classesdata['signed'] = '0';
								$classesdata['location'] = $value['location'];
								$classesdata['web_link'] = $value['location_url'];
								$classesdata['location_url'] = $value['map_url'];
								$classesdata['class_type'] = get_categories($value['class_type']);
								$instructor_data = $this->instructor_details_get($business_id, $value['class_id'], $value['instructor_ids']);
								$classesdata['instructor_details'] = $instructor_data;
								$classesdata['create_dt'] = date("d M Y ", $value['create_dt']);
								$classesdata['start_date'] = $value['start_date'];
								$classesdata['end_date'] = $value['end_date'];
								$classesdata['create_dt_utc'] = $value['create_dt'];

								$response[] = $classesdata;

							}

							$arg['status'] = $response ? 1 : 0;
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
				}
			}
		}
		echo json_encode($arg);
	}

	public function class_list_21082020_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$response = array();
						$time = time();
						$date = date("Y-m-d", $time);
						$usid = $userdata['data']['id'];
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;
						//0= all classes 1=singned class
						$class_status = $this->input->post('class_status');
						$business_id = $this->input->post('business_id');
						$upcoming_date = strtotime($this->input->post('upcoming_date'));

						/* $upcoming_dates = $upcoming_date ? $upcoming_date : date('Y-m-d');
							                    $unixTimestamp = strtotime($upcoming_dates);
							                    $week_date = date("w", $unixTimestamp);
							                    if($week_date == '0'){
							                        $week_date = 7;
							                    }

							                    $where = "business_id = ".$business_id." AND day_id = '".$week_date."'";
							                    $time_slote_data = $this->dynamic_model->getdatafromtable('class_scheduling_time',$where);
							                    if(empty($time_slote_data)){
							                        $arg['status']     = 0;
							                        $arg['error_code']  = REST_Controller::HTTP_OK;
							                        $arg['error_line']= __line__;
							                        $arg['data']       = array();
							                        $arg['message']    = $this->lang->line('record_not_found');
							                        echo json_encode($arg); die;
							                    }
						*/
						if ($class_status == 0) {
							if (!empty($upcoming_date)) {
								$date = date("Y-m-d", $upcoming_date);
								//$where="business_id=".$business_id." AND status='Active' AND start_date='".$date."'";
								$where = "business_id=" . $business_id . " AND status='Active'";
							} else {
								// $todaydate = date("Y-m-d",$time);
								// $where="business_id=".$business_id." AND status='Active' AND DATE(FROM_UNIXTIME(start_date))>='".$todaydate."'";
								$where = "business_id=" . $business_id . " AND status='Active'";
							}
							//die($where);
							$class_data = $this->dynamic_model->getdatafromtable('business_class', $where, "*", $limit, $offset, 'create_dt');

						} else {
							$class_data = $this->api_model->get_signed_classes($business_id, $upcoming_date, $limit, $offset, '', $usid);
						}
						//print_r($class_data);die;
						if (!empty($class_data)) {
							foreach ($class_data as $value) {
								//echo $upcoming_date = $upcoming_date ? $upcoming_date : date('Y-m-d');
								//$unixTimestamp = strtotime($upcoming_date);
								$week_date = date("w", $upcoming_date);
								if ($week_date == '0') {
									$week_date = 7;
								}
								$upcoming_dates = date('Y-m-d', $upcoming_date);
								$where = "business_id = " . $value['business_id'] . " AND class_id = " . $value['id'] . " AND day_id = '" . $week_date . "' AND scheduled_date = '" . $upcoming_dates . "'";

								$time_slote_data = $this->dynamic_model->getdatafromtable('class_scheduling_time', $where);
								$time_slote_from = '';
								$location_name = '';
								if (!empty($time_slote_data)) {
									// print_r($time_slote_data); die;
									$time_slote_from = $time_slote_data[0]['from_time'];
									$to_time = $time_slote_data[0]['to_time'];
									$scheduled_date = $time_slote_data[0]['scheduled_date'];
									$instructor_id_sel = $time_slote_data[0]['instructor_id'];
									$location_id = $time_slote_data[0]['location_id'];

									$where = "id = '" . $location_id . "'";

									$location_data = $this->dynamic_model->getdatafromtable('business_location', $where);
									if (!empty($location_data)) {
										$location_name = $location_data[0]['location_name'];
									}

								} else {
									continue;
								}

								$classesdata['class_id'] = $value['id'];
								$classesdata['class_name'] = ucwords($value['class_name']);
								$classesdata['from_time'] = $value['from_time'];
								$classesdata['to_time'] = $value['to_time'];
								$classesdata['from_time_utc'] = $time_slote_from;
								$classesdata['to_time_utc'] = $to_time;
								//$value['to_time'];
								$classesdata['start_date_utc'] = strtotime($scheduled_date);
								$classesdata['end_date_utc'] = strtotime($scheduled_date);

								$classesdata['duration'] = $value['duration'] . ' minutes';
								// $capicty_used                = get_checkin_class_or_workshop_count($value['id'],1,$upcoming_date);

//echo $upcoming_dates; die;
								$capicty_used = get_checkin_class_or_workshop_daily_count($value['id'], 1, $upcoming_dates);

								$classesdata['total_capacity'] = $value['capacity'];
								$classesdata['capacity_used'] = $capicty_used;
								// $capicty_used                = get_checkin_class_or_workshop_count($value['id'],1,$time);
								// $classesdata['capacity']     = $capicty_used.'/'.$value['capacity'];
								$status = get_passes_checkin_status($usid, $value['id'], 1, $date);
								if ($status == 'singup' OR $status == 'checkin' OR $status == 'checkout') {
									$signed_status = '1';
								} else {
									$signed_status = '0';
								}
								$classesdata['signed_status'] = $signed_status;
								$classesdata['signed'] = '0';
								$classesdata['location'] = $location_name;
								//$value['location'];
								$classesdata['class_type'] = get_categories($value['class_type']);
								// $instructor_data            = $this->instructor_list_details($business_id,1,$value['id']);
								$instructor_data = $this->instructor_details_get($business_id, $value['id'], $instructor_id_sel);

								$classesdata['instructor_details'] = $instructor_data;
								$classesdata['create_dt'] = date("d M Y ", $value['create_dt']);
								$classesdata['start_date'] = date("d M Y ", strtotime($value['start_date']));
								$classesdata['end_date'] = date("d M Y ", strtotime($value['end_date']));
								$classesdata['create_dt_utc'] = $value['create_dt'];
								//$classesdata['start_date_utc']=  $upcoming_date;
								//strtotime($value['start_date']);
								//$classesdata['end_date_utc']=  strtotime($value['end_date']);
								$response[] = $classesdata;
							}
							$arg['status'] = $response ? 1 : 0;
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
				}
			}
		}
		echo json_encode($arg);
	}
	/****************Function Get classes details**********************************
		     * @type            : Function
		     * @Author          : Arpit
		     * @function name   : class_details
		     * @description     : Calsses details
		     * @param           : null
		     * @return          : null
	*/
	public function class_details_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
					$this->form_validation->set_rules('class_id', 'Class Id', 'required|trim', array('required' => $this->lang->line('class_id_required')));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					$this->form_validation->set_rules('select_dt', 'Date', 'required|trim', array('required' => 'Please select date'));
					$this->form_validation->set_rules('schedule_id', 'Schedule Id', 'required|trim|numeric',
						array(
							'required' => 'Schedule Id is required',
							'numeric' => 'Schedule Id is required',
						)
					);
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$usid = $userdata['data']['id'];
						$time = time();
						$date = date("Y-m-d", $time);
						$response = $pass_arr = array();
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;

						$class_id = $this->input->post('class_id');
						$business_id = $this->input->post('business_id');

						$time_zone = $this->input->get_request_header('Timezone', true);
						$time_zone = $time_zone ? $time_zone : 'UTC';
						date_default_timezone_set($time_zone);

						//$upcoming_date = strtotime($this->input->post('select_dt'));
						$upcoming_date = $this->input->post('select_dt');
						$schedule_id = $this->input->post('schedule_id');
						$where = array("id" => $class_id, "business_id" => $business_id, "status" => "Active");
						$class_data = $this->dynamic_model->getdatafromtable('business_class', $where, "*", $limit, $offset, 'create_dt');
						//print_r($class_data);die;
						if (!empty($class_data)) {

							$week_date = date("w", $upcoming_date);
							if ($week_date == '0') {
								$week_date = 7;
							}
							$upcoming_dates = date('Y-m-d', $upcoming_date);
							$where_schdule = "business_id = " . $class_data[0]['business_id'] . " AND id = " . $schedule_id . " AND class_id = " . $class_data[0]['id'] . " AND day_id = '" . $week_date . "' AND scheduled_date = '" . $upcoming_dates . "'";

							$time_slote_data = $this->dynamic_model->getdatafromtable('class_scheduling_time', $where_schdule);
							$time_slote_from = '';
							$location = ''; /* rahul 10-08-2020 22:22 */
							$location_url = '';
							$map_url = '';
							$class_end_status = 0;
							if (!empty($time_slote_data)) {
								$time_slote_from = $time_slote_data[0]['from_time'];
								$to_time = $time_slote_data[0]['to_time'];
								$scheduled_date = $time_slote_data[0]['scheduled_date'];
								$instructor_id_sel = $time_slote_data[0]['instructor_id'];
								$classesdata['schedule_id'] = $time_slote_data[0]['id'];
								$class_end_status = $time_slote_data[0]['class_end_status'];
								/* rahul 10-08-2020 22:22 */
								$locationId = $time_slote_data[0]['location_id'];
								if ($locationId != null) {
									$locationInfo = $this->db->get_where('business_location', array('id' => $locationId))->row_array();
									if (!empty($locationInfo)) {
										$location = $locationInfo['location_name'];
										$location_url = empty($locationInfo['location_url']) ? '' : $locationInfo['location_url'];
										$map_url = empty($locationInfo['map_url']) ? '' : $locationInfo['map_url'];
									}
								}
							} else {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['data'] = json_decode('{}');
								$arg['message'] = $this->lang->line('record_not_found');
								echo json_encode($arg);die;
							}

							$classesdata['class_id'] = $class_data[0]['id'];
							$classesdata['class_name'] = $class_data[0]['class_name'];
							$classesdata['from_time'] = $class_data[0]['from_time'];
							$classesdata['to_time'] = $class_data[0]['to_time'];
							$classesdata['from_time_utc'] = $time_slote_from;
							//$class_data[0]['from_time'];
							$classesdata['to_time_utc'] = $to_time;
							//$class_data[0]['to_time'];

							$classesdata['start_date_utc'] = strtotime($scheduled_date);
							$classesdata['end_date_utc'] = strtotime($scheduled_date);

							$classesdata['duration'] = $class_data[0]['duration'] . ' minutes';
							// $capicty_used                = get_checkin_class_or_workshop_count($class_data[0]['id'],1,$upcoming_date);

//echo $class_data[0]['id'].'--'.$upcoming_dates; die;
							$capicty_used = get_checkin_class_or_workshop_daily_count($class_data[0]['id'], 1, $upcoming_dates, $schedule_id);
							$classesdata['total_capacity'] = $class_data[0]['capacity'];
							$classesdata['capacity_used'] = $capicty_used;
							$classesdata['timeframe'] = get_daywise_instructor_data($class_data[0]['id'], 1, $business_id);
							// $capicty_used                = get_checkin_class_or_workshop_count($class_data[0]['id'],1,$time);
							// $classesdata['capacity']     = $capicty_used.'/'.$class_data[0]['capacity'];
							$classesdata['location'] = $class_data[0]['location'];
							$classesdata['location'] = $location; /* rahul 10-08-2020 22:22 */
							$classesdata['web_link'] = $location_url;
							$classesdata['location_url'] = $map_url;
							$classesdata['description'] = $class_data[0]['description'];
							$classesdata['class_type'] = get_categories($class_data[0]['class_type']);
							$classesdata['start_date'] = date("d M Y ", strtotime($class_data[0]['start_date']));
							$classesdata['end_date'] = date("d M Y ", strtotime($class_data[0]['end_date']));

							// $instructor_data             =  $this->instructor_list_details($business_id,1,$class_data[0]['id']);
							$instructor_data = $this->instructor_details_get($business_id, $class_data[0]['id'], $instructor_id_sel);
							$classesdata['instructor_details'] = $instructor_data;
							$where = array("business_id" => $business_id, "service_id" => $class_id, "service_type" => "1", "status" => "Active");
							// $passes_data = $this->dynamic_model->getdatafromtable('business_passes',$where,"*",$limit,$offset,'create_dt');

							$sql = "SELECT * FROM user_booking WHERE service_type = 1 AND status = 'Success' AND passes_status = '1' AND user_id = $usid";
							$query = $this->db->query($sql)->result_array();
							$pass_id = '';
							$pass_id_array = array();
							if (!empty($query)) {
								foreach ($query as $key => $value) {
									$pass_id .= $value['service_id'] . ',';
									$pass_id_array[] = $value['service_id'];
								}
								$pass_id = rtrim($pass_id, ",");
							}

							$this->db->select('b.*,(SELECT u.id FROM user_booking as u where u.service_id = bpa.pass_id AND u.status = "Success" AND u.passes_status = "1" AND u.service_type = "1" AND u.business_id = "' . $business_id . '" AND u.user_id = "' . $usid . '" ) as user_booking_id');
							$this->db->from('business_passes_associates as bpa');
							$this->db->join('business_passes b', 'b.id = bpa.pass_id');
							$this->db->where('bpa.business_id', $business_id);
							$this->db->where('bpa.class_id', $class_id);
							$this->db->where('bpa.pass_type', "0");
							$this->db->where('b.status', "Active");
							$this->db->where('b.purchase_date <=', $time);
							$blockidsss = "b.id NOT IN ('" . $pass_id . "')";
							$this->db->where($blockidsss);

							$this->db->group_by('bpa.pass_id');
							$passes_data = $this->db->get()->result_array();

							//$pass_id_array;
							if (!empty($passes_data)) {
								foreach ($passes_data as $value) {
									$purchase_date = $value['purchase_date'];
									$user_booking_id = $value['user_booking_id'] ? $value['user_booking_id'] : '';
									if (!empty($user_booking_id)) {
										$where = array("id" => $user_booking_id);
										$u_data = $this->dynamic_model->getdatafromtable("user_booking", $where);
										if (!empty($u_data)) {
											$purchase_date = $u_data[0]['passes_start_date'];
										}
									}

									$time = time();
									if ($purchase_date <= $time) {
										$passesdata = getpassesdetails($value['id'], $usid, $user_booking_id);
										//$pass_id_array[] = $passesdata['pass_id'];
										if ($value['is_client_visible'] == 'no') {
											continue;
										}

										// $pass_id_array .= "'".$passesdata['pass_id']."',";

										$business_ids = $passesdata['business_id'];
										$business_info = $this->dynamic_model->getQueryRowArray('SELECT *  FROM business WHERE id = ' . $business_ids);
										$passesdata['business_logo'] = empty($business_info['business_image']) ? '' : site_url() . 'uploads/business/' . $business_info['business_image'];
										$pass_arr[] = $passesdata;
									}
								}
							}

							$classesdata['passes_details'] = $pass_arr;

							if (!empty($pass_id_array)) {
								$pass_id_array = implode(",", $pass_id_array);
								$sql = "SELECT * FROM user_booking WHERE user_id = '" . $usid . "' && passes_status = '1' && service_type = '1' && status = 'Success' && service_id IN ($pass_id_array)";
								$my_passes_data = $this->dynamic_model->getQueryResultArray($sql);
								$pass_arr = array();
								if (!empty($my_passes_data)) {
									foreach ($my_passes_data as $value) {
										$passesdata = getpassesdetails($value['service_id'], $usid, $value['id']);
										$business_ids = $value['business_id'];
										$business_info = $this->dynamic_model->getQueryRowArray('SELECT *  FROM business WHERE id = ' . $business_ids);
										$passesdata['business_logo'] = empty($business_info['business_image']) ? '' : site_url() . 'uploads/business/' . $business_info['business_image'];

										$passesdata['user_booking_id'] = $value['id'];
										$passesdata['start_date_utc'] = $value['passes_start_date'];
										$passesdata['end_date_utc'] = $value['passes_end_date'];
										$passesdata['remaining_count'] = $value['passes_remaining_count'];
										if ($business_ids == $business_id)
										$pass_arr[] = $passesdata;
									}
								}
								$classesdata['my_passes_details'] = $pass_arr;
							} else {
								$classesdata['my_passes_details'] = array();
							}

							$classesdata['create_dt'] = date("d M Y ", $class_data[0]['create_dt']);
							$classesdata['create_dt_utc'] = $class_data[0]['create_dt'];
							// $classesdata['passes_status'] = get_passes_checkin_status($usid,$class_data[0]['id'],1,$date);

							//signup condition check
							//$condition="user_id=".$usid." AND service_type=1 AND service_id=".$class_id;
							$condition = "user_id = '" . $usid . "' AND service_type = '1' AND checkin_dt = '" . $upcoming_dates . "' AND service_id = '" . $class_id . "' AND schedule_id = '" . $schedule_id . "'";
							$signup_check = $this->dynamic_model->getdatafromtable('user_attendance', $condition);
							//checked-In condition check
							//$whe="user_id=".$usid." AND service_id=".$class_id." AND service_type=1 AND DATE(FROM_UNIXTIME(create_dt))='".$date."'";

							$whe = "user_id=" . $usid . " AND service_id=" . $class_id . "  AND schedule_id = " . $schedule_id . " AND service_type=1 AND checkin_dt='" . $upcoming_dates . "'";

							$checkin_data = $this->dynamic_model->getdatafromtable('user_attendance', $whe);
							$current_status = (!empty($checkin_data[0]['status'])) ? $checkin_data[0]['status'] : '';

							//get passes purchase status

							$whe = "user_id=" . $usid . " AND service_id=" . $class_id . " AND schedule_id = '" . $schedule_id . "' AND checkin_dt='" . $upcoming_dates . "'";
							$check_purchase = $this->dynamic_model->getdatafromtable('user_attendance', $whe);
							$check_purchase = (!empty($check_purchase[0]['status'])) ? $check_purchase[0]['status'] : '';
							$classesdata['class_end_status'] = $class_end_status;
							// $class_end_status= $check_purchase[0]['signup_status'];
							//$classesdata['class_end_status']   = $class_end_status;

							// $check_purchase= get_passes_status($usid,$business_id,$class_id,1);
							if ($check_purchase == 'Pending' || $check_purchase == '') {
								$classes_status = '0'; //pass purchase
							} elseif (empty($signup_check) || $current_status == 'cancel') {
								$classes_status = '1'; //signup

							} elseif (!empty($signup_check)) {

								if (empty($checkin_data) || $current_status == 'singup') {
									$classes_status = '2'; //check in
								} else {
									//3 checked in, 4 waiting
									$classes_status = (!empty($signup_check[0]['waitng_list_no'])) ? '4' : '3';
								}

								if (!empty($checkin_data)) {
									$checkout_time = $checkin_data[0]['checkout_time'];
									$time = time();
									if ($checkout_time < $time) {
										$classes_status = '2';
									}
								}
							}

							/* not puspace 0
								                         pass paschase 1
								                         singup 2
								                         chekin 3
								                         wating 4
								                         cancel 1

							*/
							// add new condition
							if (!empty($signup_check)) {
								$waitng_list_no = $signup_check[0]['waitng_list_no'];
								if (!empty($waitng_list_no)) {
									$classes_status = 4;
								} else if ($current_status == 'checkin') {
									$classes_status = 3;
								} else if ($current_status == 'absence') {
									$classes_status = 6;
								}
								$classesdata['attendance_id'] = $signup_check[0]['id'];
							} else {
								$classesdata['attendance_id'] = 0;
							}

							//0 without wating,1 wating , 2 wating put
							$classesdata['waiting_status'] = 0;

							$query = "SELECT count(ua.id) as user_counter, bc.class_waitlist_count, (select status from user_attendance where user_attendance.user_id = ".$usid." AND schedule_id = ".$schedule_id."  AND service_id = ".$class_id.") as current_status, bc.class_waitlist_overflow FROM user_attendance as ua JOIN business_class as bc on (bc.id = ua.service_id)  WHERE ua.service_type = 1 AND ua.schedule_id = ".$schedule_id."  AND ua.service_id = ".$class_id." AND ua.status = 'singup' OR 'checkin'";

							$waiting_counter = $this->db->query($query);

							// if ($waiting_counter->num_rows() > 0) {
							// 	$rowData = $waiting_counter->row_array();
							// 	if ($rowData['current_status'] == 'singup' || $rowData['current_status'] == 'checkin') {
							// 		$classesdata['waiting_status'] = 0;
							// 	} else {
							// 		if ($rowData['class_waitlist_overflow'] == 'no' && ($classesdata['total_capacity'] >= $rowData['user_counter'])) {

							// 			$classesdata['waiting_status'] = 2;

							// 		} else {

							// 			$waiting_number = $rowData['class_waitlist_count'];

							// 			// Overflow Condition
							// 			$waiting_status = $this->db->get_where('user_attendance', array(
							// 				'service_type' 	=> 1,
							// 				'schedule_id'	=> $schedule_id,
							// 				'service_id'	=> $class_id,
							// 				'status'		=> 'waiting',
							// 			))->num_rows();

							// 			if ($classesdata['total_capacity'] > $rowData['user_counter']) {
							// 				$classesdata['waiting_status'] = 0;
							// 			} else {
							// 				if ($waiting_number > $waiting_status) {
							// 					$classesdata['waiting_status'] = 1;
							// 				} else {
							// 					$classesdata['waiting_status'] = 2;
							// 				}
							// 			}
							// 		}
							// 	}
							// }

							$classesdata['class_status'] = $classes_status;
							$classesdata['waitng_list_no'] = (!empty($signup_check[0]['waitng_list_no'])) ? $signup_check[0]['waitng_list_no'] : '';

//echo $business_id.'--'.$class_data[0]['id'].'--'.$date.'--'.$customer_type.'--'.$checkedin_type.'--'.$usid.'--'.$limit.'--'.$offset; die;

							$customer_type = '';
							$checkedin_type = '1';

							//echo "string".$upcoming_dates.'---'.$customer_type.'---'.$checkedin_type;  die();

							$customer_details = $this->instructor_model->get_all_signed_classes($business_id, $class_data[0]['id'], $this->input->post('select_dt'), $customer_type, $checkedin_type, $usid, $limit, $offset, $schedule_id);
							//$customer_details=$this->instructor_model->get_all_signed_classes_by_schedule_date($business_id,$class_data[0]['id'],$upcoming_dates,$customer_type,$checkedin_type,$usid,$limit,$offset);
							$st = array();
							$classesdata['customer_details'] = $customer_details ? $customer_details : $st;

							$covid_info = getUserQuestionnaire($usid, $class_data[0]['id'], $business_id);
							// print_r($covid_info); die;
							if (!empty($covid_info)) {
								$covid_status = $covid_info['covid_status'];
								$covid_info = $covid_info['covid_info'];
							} else {
								$covid_info = 0;
								$covid_status = 0;
							}
							$classesdata['covid_info'] = $covid_info;
							$classesdata['covid_status'] = $covid_status;

							$response = $classesdata;

							$arg['status'] = $time_slote_from ? 1 : 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $time_slote_from ? $response : json_decode('{}');
							$arg['message'] = $this->lang->line('record_found');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = json_decode('{}');
							$arg['message'] = $this->lang->line('record_not_found');
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
		     * @function name   : business_workshop_list
		     * @description     : list of classes
		     * @param           : null
		     * @return          : null
	*/
	public function workshop_list_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$time_zone = $this->input->get_request_header('Timezone', true);
						$time_zone = $time_zone ? $time_zone : 'UTC';
						date_default_timezone_set($time_zone);

						$response = array();
						$time = time();
						$usid = $userdata['data']['id'];
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;
						$business_id = $this->input->post('business_id');
						$upcoming_date = $this->input->post('upcoming_date');
						// 0=all workshop 1=singed workshop
						$workshop_status = $this->input->post('workshop_status');
						$imgPath = base_url() . 'uploads/user/';
						if ($workshop_status == 0) {

							$query = "SELECT business_workshop_master.id as workshop_id, business_workshop_schdule.id as schedule_id, business_workshop_master.name as workshop_name,business_workshop_master.workshop_capacity, business_workshop_master.price as workshop_price, business_workshop_schdule.*, CASE WHEN business_location.location_name IS NULL THEN '' Else business_location.location_name END as location, (CASE WHEN business_location.map_url IS NULL THEN '' Else business_location.map_url END) as location_url, (CASE WHEN business_location.location_url IS NULL THEN '' Else business_location.location_url END) as web_link FROM `business_workshop_schdule` JOIN business_workshop_master on (business_workshop_master.id = business_workshop_schdule.workshop_id) LEFT JOIN business_location on (business_location.id = business_workshop_schdule.location) WHERE business_workshop_master.business_id = " . $business_id . " AND business_workshop_schdule.status = 'Active' AND business_workshop_master.workshop_capacity != '0' ";

							if (!empty($upcoming_date)) {
								$searchDate = date("Y-m-d", $upcoming_date);
								$query .= " AND business_workshop_schdule.schedule_dates = '" . $searchDate . "' ";
							}
							$query .= "  ORDER BY business_workshop_schdule.start ASC";

							// $query .= " LIKE ".$limit." OFFSET ".$offset;
							//echo $query;
							$workshop_data = $this->db->query($query)->result_array(); // dynamic_model->getdatafromtable('business_workshop',$where,"*",$limit,$offset,'create_dt');
						} else {
							$workshop_data = '';
							$current_date = date("Y-m-d", $upcoming_date);
							$query = "SELECT bws.id as schedule_id, bws.id as id, bws.start as start_time, bws.end as end_time, bws.start, bws.end, bws.schedule_date, bws.schedule_dates, CASE WHEN bws.address IS NULL THEN '' Else bws.address END as address_id, t.create_dt, b.family_user_id,t.id as transaction_id,t.user_id,t.amount,t.discount,t.trx_id,t.order_number,t.create_dt as payment_date,t.payment_type,t.payment_method,t.responce_all,u.name,u.lastname, u.gender, u.date_of_birth, bs.business_name,bs.address,bs.location_detail, b.status as booking_status, b.tip_comment, bwm.name as workshop_name, bwm.description as workshop_description, bwm.price as workshop_price, bwm.tax1, bwm.tax1_rate, bwm.tax2, bwm.tax2_rate,bwm.workshop_capacity as capacity, bwm.workshop_capacity as workshop_capacity, bwm.id as workshop_id, bl.location_name as location, (CASE WHEN bl.map_url IS NULL THEN '' Else bl.map_url END) as location_url, (CASE WHEN bl.location_url IS NULL THEN '' Else bl.location_url END) as web_link  FROM business_workshop_schdule as bws JOIN business_workshop_master as bwm on (bwm.id = bws.workshop_id) JOIN user_booking as b on (b.service_id = bwm.id) LEFT JOIN transactions as t on (t.id = b.transaction_id) LEFT JOIN user as u on (u.id = b.user_id) LEFT JOIN business_location as bl on (bl.id = bws.location) JOIN business as bs on (bs.id = b.business_id) WHERE b.service_type = 4 AND b.status = 'Success'  AND bws.schedule_dates = '$current_date' AND b.business_id = ".$business_id." AND b.user_id = $usid ORDER BY bws.schedule_dates asc";
							$workshop_data = $this->db->query($query)->result_array();
							// $this->api_model->get_signed_workshop($business_id,$upcoming_date,$limit,$offset,'',$usid)
						}

						//print_r($workshop_data);die;
						if (!empty($workshop_data)) {
							foreach ($workshop_data as $value) {
								// $instructorId = $value['user_id'];
								$workshopdata = $value;
								/*$workshopdata['workshop_id']  = $value['id'];
									            	$workshopdata['workshop_name']= ucwords($value['workshop_name']);
									            	$workshopdata['from_time']    = $value['from_time'];
									            	$workshopdata['to_time']      = $value['to_time'];
									            	$workshopdata['from_time_utc'] = $value['from_time'];
									            	$workshopdata['to_time_utc']  =  $value['to_time'];
								*/

								///$capicty_used                 = get_checkin_class_or_workshop_count($value['id'],2,$time);
								// $workshopdata['total_capacity']    = $value['capacity'];
								$workshopdata['total_capacity'] = $value['workshop_capacity'];
								unset($workshopdata['capacity']);
								$workshopdata['capacity_used'] = $this->db->get_where('user_booking', array(
									'service_id' => $value['workshop_id'],
									'service_type' => '4',
									'status' => 'Success',
								))->num_rows();

								/*$this->db->select('user.id, user.name, user.lastname, instructor_details.about, concat("'.$imgPath.'", user.profile_img) as profile_img, user.availability_status, instructor_details.skill,instructor_details.total_experience as experience,instructor_details.appointment_fees,instructor_details.appointment_fees_type,instructor_details.shifts_instructor,');
									                            $this->db->from('user');
									                            $this->db->join('instructor_details','instructor_details.user_id = user.id');
								*/
								$workshopdata['instructor_details'] = [];

								// $workshopdata['capacity']     = $capicty_used.'/'.$value['capacity'];
								// $workshopdata['location']     = $value['location'];
								//$workshopdata['workshop_type']= get_categories($value['workshop_type']);
								// $instructor_data             =  $this->instructor_list_details($business_id,2,$value['id']);

								/*$workshopdata['instructor_details']    = $instructor_data;
									            	$workshopdata['create_dt']    = date("d M Y ",$value['create_dt']);
									            	$workshopdata['start_date']    = date("d M Y ",strtotime($value['start_date']));
									            	$workshopdata['end_date']    = date("d M Y ",strtotime($value['end_date']));
									            	$workshopdata['create_dt_utc'] = $value['create_dt'];
									            	$workshopdata['start_date_utc']= strtotime($value['start_date']);
								*/
								$response[] = $workshopdata;
							}
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $response;
							// $arg['query'] = $query;
							$arg['message'] = $this->lang->line('record_found');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = array();
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}
	/****************Function Get classes details**********************************
		     * @type            : Function
		     * @Author          : Arpit
		     * @function name   : class_details
		     * @description     : Calsses details
		     * @param           : null
		     * @return          : null
	*/
	public function workshop_details_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
					$this->form_validation->set_rules('workshop_id', 'Workshop Id', 'required|trim', array('required' => $this->lang->line('workshop_id_required')));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$usid = $userdata['data']['id'];
						$time = time();
						$date = date("Y-m-d", $time);
						$response = $pass_arr = array();
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;

						$workshop_id = $this->input->post('workshop_id');
						$business_id = $this->input->post('business_id');

						$where = array("id" => $workshop_id, "business_id" => $business_id, "status" => "Active");
						$workshop_data = $this->dynamic_model->getdatafromtable('business_workshop', $where, "*", $limit, $offset, 'create_dt');
						//print_r($workshop_data);die;
						if (!empty($workshop_data)) {

							$workshopsdata['workshop_id'] = $workshop_data[0]['id'];
							$workshopsdata['workshop_name'] = ucwords($workshop_data[0]['workshop_name']);
							$workshopsdata['from_time'] = $workshop_data[0]['from_time'];
							$workshopsdata['to_time'] = $workshop_data[0]['to_time'];

							$workshopsdata['from_time_utc'] = $workshop_data[0]['from_time'];
							$workshopsdata['to_time_utc'] = $workshop_data[0]['to_time'];
							$workshopsdata['duration'] = $workshop_data[0]['duration'] . ' minutes';
							$capicty_used = get_checkin_class_or_workshop_count($workshop_data[0]['id'], 2, $time);
							$workshopsdata['total_capacity'] = $workshop_data[0]['capacity'];
							$workshopsdata['capacity_used'] = $capicty_used;
							$workshopsdata['timeframe'] = get_daywise_instructor_data($workshop_data[0]['id'], 2, $business_id);
							// $workshopsdata['capacity']     = $capicty_used.'/'.$workshop_data[0]['capacity'];
							$workshopsdata['location'] = $workshop_data[0]['location'];
							$workshopsdata['description'] = $workshop_data[0]['description'];
							$workshopsdata['workshop_type'] = get_categories($workshop_data[0]['workshop_type']);
							$instructor_data = $this->instructor_list_details($business_id, 2, $workshop_data[0]['id']);

							$workshopsdata['instructor_details'] = $instructor_data;
							$where = array("business_id" => $business_id, "service_id" => $workshop_id, "service_type" => "2", "status" => "Active");
							$passes_data = $this->dynamic_model->getdatafromtable('business_passes', $where, "*", $limit, $offset, 'create_dt');
							if (!empty($passes_data)) {
								foreach ($passes_data as $value) {
									$passesdata = getpassesdetails($value['id'], $usid);
									$pass_arr[] = $passesdata;
								}
							}
							$workshopsdata['passes_details'] = $pass_arr;
							$workshopsdata['create_dt'] = date("d M Y ", $workshop_data[0]['create_dt']);
							$workshopsdata['start_date'] = date("d M Y ", strtotime($workshop_data[0]['start_date']));
							$workshopsdata['end_date'] = date("d M Y ", strtotime($workshop_data[0]['end_date']));
							$workshopsdata['start_date_utc'] = strtotime($workshop_data[0]['start_date']);
							$workshopsdata['end_date_utc'] = strtotime($workshop_data[0]['end_date']);
							$workshopsdata['create_dt_utc'] = $workshop_data[0]['create_dt'];
							// $classesdata['passes_status'] = get_passes_checkin_status($usid,$class_data[0]['id'],1,$date);

							//signup condition check
							$condition = "user_id=" . $usid . " AND service_type=2 AND service_id=" . $workshop_id;
							$signup_check = $this->dynamic_model->getdatafromtable('user_attendance', $condition);
							//checked-In condition check
							$whe = "user_id=" . $usid . " AND service_id=" . $workshop_id . " AND service_type=2 AND DATE(FROM_UNIXTIME(create_dt))='" . $date . "'";
							$checkin_data = $this->dynamic_model->getdatafromtable('user_attendance', $whe);
							$current_status = (!empty($checkin_data[0]['status'])) ? $checkin_data[0]['status'] : '';

							//get passes purchase status
							$check_purchase = get_passes_status($usid, $business_id, $workshop_id, 2);
							if ($check_purchase == 'Pending' || $check_purchase == '') {
								$workshop_status = '0'; //pass purchase
							} elseif (empty($signup_check) || $current_status == 'cancel') {
								$workshop_status = '1'; //signup

							} elseif (!empty($signup_check)) {
								if (empty($checkin_data) || $current_status == 'singup') {
									$workshop_status = '2'; //check in
								} else {
									//3 checked in, 4 waiting
									$workshop_status = (!empty($signup_check[0]['waitng_list_no'])) ? '4' : '3';
								}
							}
							$workshopsdata['workshop_status'] = $workshop_status;
							$workshopsdata['waitng_list_no'] = (!empty($signup_check[0]['waitng_list_no'])) ? $signup_check[0]['waitng_list_no'] : '';
							$response = $workshopsdata;

							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $response;
							$arg['message'] = $this->lang->line('record_found');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = json_decode('{}');
							$arg['message'] = $this->lang->line('record_not_found');
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
	*/
	public function instructor_list_details($business_id = '', $service_type = '1', $service_id = '', $search_val = '', $limit = "", $offset = "", $search_skill = "") {
		$response = array();
		$url = site_url() . 'uploads/user/';
		$category = $where = '';
		//get instructor according to class,workshop and services
		if ($service_type == 1) {
			//class
			$where = array('id' => $service_id, 'business_id' => $business_id);
			$business_data = $this->dynamic_model->getdatafromtable('business_class', $where);
			$category = (!empty($business_data[0]['class_type'])) ? $business_data[0]['class_type'] : '';
			//$instructor_id = (!empty($business_data[0]['instructor_id'])) ? $business_data[0]['instructor_id'] : '';
			$where1 = array('class_id' => $service_id, 'business_id' => $business_id);
			$instructor_class_data = $this->dynamic_model->getdatafromtable('class_scheduling_time', $where1);
			$instructor_ids = array();
			if (!empty($instructor_class_data)) {
				foreach ($instructor_class_data as $value) {
					$instructor_ids[] = $value['instructor_id'];
				}
			}
		} elseif ($service_type == 2) {
//workshop
			$where = array('id' => $service_id, 'business_id' => $business_id);
			$business_data = $this->dynamic_model->getdatafromtable('business_workshop', $where);
			$category = (!empty($business_data[0]['workshop_type'])) ? $business_data[0]['workshop_type'] : '';
			$where2 = array('workshop_id' => $service_id, 'business_id' => $business_id);
			$instructor_workshop_data = $this->dynamic_model->getdatafromtable('workshop_scheduling_time', $where2);
			$instructor_ids = array();
			if (!empty($instructor_workshop_data)) {
				foreach ($instructor_workshop_data as $value) {
					$instructor_ids[] = $value['instructor_id'];
				}
			}
			//$instructor_id = (!empty($business_data[0]['instructor_id'])) ? $business_data[0]['instructor_id'] : '';
		} elseif ($service_type == 3) {
//services
			$where = array('id' => $service_id, 'business_id' => $business_id);
			$business_data = $this->dynamic_model->getdatafromtable('service', $where);
			$category = (!empty($business_data[0]['service_type'])) ? $business_data[0]['service_type'] : '';
			$instructor_id = (!empty($business_data[0]['instructor_id'])) ? $business_data[0]['instructor_id'] : '';
		}
		//if services skill search
		$where = '';
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
		if ($where) {
			$condition1 = "$where AND status='Active'";
		} else {
			$condition1 = "status='Active'";
		}
		//echo $where;die;
		$instructor_info = $this->api_model->get_instructor_details($business_id, $instructor_ids, $condition1, $search_val, $limit, $offset);
		//print_r($instructor_info);die;
		if ($instructor_info) {
			foreach ($instructor_info as $value) {
				$instructordata['id'] = $value['id'];
				$instructordata['name'] = ucwords($value['name']);
				$instructordata['lastname'] = ucwords($value['lastname']);
				$instructordata['about'] = $value['about'];
				$instructordata['profile_img'] = $url . $value['profile_img'];
				$instructordata['availability_status'] = $value['availability_status'];

				$instructordata['skill'] = (!empty($value['skill'])) ? get_categories($value['skill']) : "";
				$category = $value['skill'];
				$instructordata['skill_details'] = (!empty($value['skill'])) ? get_categories_data($value['skill']) : array();
				$instructordata['services'] = "Zumba,Yoga,Gym,Fitness";
				$instructordata['experience'] = (!empty($value['total_experience'])) ? $value['total_experience'] : "";
				$instructordata['appointment_fees_type'] = (!empty($value['appointment_fees_type'])) ? $value['appointment_fees_type'] : "";
				$instructordata['appointment_fees'] = (!empty($value['appointment_fees'])) ? $value['appointment_fees'] : "";
				$instructordata['appointment_fees'] = "12pm to 2pm";
				$instructordata['duration_of_service'] = "1 hour";
				$response[] = $instructordata;
			}
		}
		return $response;
	}

	public function instructor_details_get($business_id = '', $class_id = '', $instructor_id = '') {
		$response = array();
		$url = site_url() . 'uploads/user/';
		$category = $where = '';

		$instructor_info = $this->api_model->get_instructor_details($business_id, $instructor_id);
		//print_r($instructor_info);die;
		if ($instructor_info) {
			foreach ($instructor_info as $value) {
				$instructordata['id'] = $value['id'];
				$instructordata['name'] = ucwords($value['name']);
				$instructordata['lastname'] = ucwords($value['lastname']);
				$instructordata['about'] = $value['about'];
				$instructordata['profile_img'] = $url . $value['profile_img'];
				$instructordata['availability_status'] = $value['availability_status'];

				$instructordata['skill'] = (!empty($value['skill'])) ? get_categories($value['skill']) : "";
				$category = $value['skill'];
				$instructordata['skill_details'] = (!empty($value['skill'])) ? get_categories_data($value['skill']) : array();
				$instructordata['services'] = "Zumba,Yoga,Gym,Fitness";
				$instructordata['experience'] = (!empty($value['total_experience'])) ? $value['total_experience'] : "";
				$instructordata['appointment_fees_type'] = (!empty($value['appointment_fees_type'])) ? $value['appointment_fees_type'] : "";
				$instructordata['appointment_fees'] = (!empty($value['appointment_fees'])) ? $value['appointment_fees'] : "";
				$instructordata['appointment_fees'] = "12pm to 2pm";
				$instructordata['duration_of_service'] = "1 hour";
				$response[] = $instructordata;
			}
		}
		return $response;
	}

	public function instructor_list_post() {

		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					//service type 1 classs 2 workshop 3 services 4 all instructors
					//$service_type=  $this->input->post('service_type');
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));

					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$response = array();
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;
						$business_id = $this->input->post('business_id');
						//$service_id=  $this->input->post('service_id');
						$search_val = $this->input->post('search_val');
						//$search_skill=  $this->input->post('search_skill');
						$instructor_data = $this->api_model->get_all_instructors($business_id, $search_val, $limit, $offset);
						//$instructor_data =$this->instructor_list_details($business_id,$service_type,$service_id,$search_val,$limit,$offset,$search_skill);
						//get instructor count
						//$instructor_count =$this->instructor_list_details($business_id,$service_type,$service_id,$search_val);

						$instructor_array_data = array();
						if (!empty($instructor_data)) {
							foreach ($instructor_data as $value) {

								$instructor_array = array(
									"id" => $value['id'],
									"role_id" => $value['role_id'],
									"singup_for" => $value['singup_for'],
									"availability_status" => $value['availability_status'],
									"name" => $value['name'],
									"lastname" => $value['lastname'],
									"mobile" => $value['mobile'],
									"email" => $value['email'],
									"password" => $value['password'],
									"temp_password" => $value['temp_password'],
									"profile_img" => $value['profile_img'],
									"date_of_birth" => $value['date_of_birth'],
									"gender" => $value['gender'],
									"about" => $value['about'],
									"country" => $value['country'],
									"country_code" => $value['country_code'],
									"state" => $value['state'],
									"city" => $value['city'],
									"zipcode" => $value['zipcode'],
									"address" => $value['address'],
									"location" => $value['location'],
									"lat" => $value['lat'],
									"lang" => $value['lang'],
									"device_token" => $value['device_token'],
									"device_type" => $value['device_type'],
									"login_count" => $value['login_count'],
									"email_verified" => $value['email_verified'],
									"email_otp" => $value['email_otp'],
									"email_otp_date" => $value['email_otp_date'],
									"mobile_verified" => $value['mobile_verified'],
									"mobile_otp" => $value['mobile_otp'],
									"mobile_otp_date" => $value['mobile_otp_date'],
									"referral_code" => $value['referral_code'],
									"referred_by" => $value['referred_by'],
									"emergency_contact_person" => $value['emergency_contact_person'],
									"emergency_contact_no" => $value['emergency_contact_no'],
									"notification" => json_decode($value['notification']),
									"status" => $value['status'],
									"profile_status" => $value['profile_status'],
									"is_loggedin" => $value['is_loggedin'],
									"create_dt" => $value['create_dt'],
									"update_dt" => $value['update_dt'],
									"plan_id" => $value['plan_id'],
									"merchant_details" => $value['merchant_details'],
									"emergency_country_code" => $value['emergency_country_code'],
									"created_by" => $value['created_by'],
									"discount" => $value['discount'],
									"consent_signed" => $value['consent_signed'],
									"first_login" => $value['first_login'],
									"skill" => get_categories($value['skill'], 1), //get_categories
									"total_experience" => $value['total_experience'],
									"appointment_fees" => $value['appointment_fees'],
									"appointment_fees_type" => $value['appointment_fees_type'],
									"shifts_instructor" => $value['shifts_instructor'],
									"start_date" => $value['start_date'],
									"sin_no" => $value['sin_no'],
									"employee_id" => $value['employee_id'],
								);
								$instructor_array_data[] = $instructor_array;

							}
						}

						$instructor_count = $this->api_model->get_all_instructors($business_id, $search_val, $limit, $offset);
						if (!empty($instructor_data)) {
							$total_count = count($instructor_count);
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $instructor_array_data;
							$arg['total_count'] = "$total_count";
							$arg['message'] = $this->lang->line('record_found');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = array();
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function instructor_listn_post() {

		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					//service type 1 classs 2 workshop 3 services 4 all instructors
					$service_type = $this->input->post('service_type');
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					$this->form_validation->set_rules('service_type', 'Service Type', 'required|trim', array('required' => $this->lang->line('service_type_required')));
					if ($service_type !== '4') {
						$this->form_validation->set_rules('service_id', 'Service Id', 'required|trim', array('required' => $this->lang->line('service_id_required')));
					}
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$response = array();
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;
						$business_id = $this->input->post('business_id');
						$service_id = $this->input->post('service_id');
						$search_val = $this->input->post('search_val');
						$search_skill = $this->input->post('search_skill');
						$instructor_data = $this->instructor_list_details($business_id, $service_type, $service_id, $search_val, $limit, $offset, $search_skill);
						//get instructor count
						$instructor_count = $this->instructor_list_details($business_id, $service_type, $service_id, $search_val);
						if (!empty($instructor_data)) {
							$total_count = count($instructor_count);
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $instructor_data;
							$arg['total_count'] = "$total_count";
							$arg['message'] = $this->lang->line('record_found');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = array();
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}
	/****************Function getprofile**********************************
		     * @type            : Function
		     * @Author          : arpit
		     * @function name   : other_instructor_details
		     * @description     : Get all details of user.
		     * @param           : null
		     * @return          : null
	*/
	public function instructor_details_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('user_id', 'User Id', 'required|numeric', array(
						'required' => $this->lang->line('user_id'),
						'numeric' => $this->lang->line('user_id_numeric'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$response = array();
						$user_id = $this->input->post("user_id");
						$user_data = getuserdetail($user_id);
						if (!empty($user_data)) {

							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $user_data;
							$arg['message'] = $this->lang->line('profile_details');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = json_decode('{}');
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}
	/****************Function Passes list**********************************
		     * @type            : Function
		     * @Author          : Arpit
		     * @function name   : business_workshop_list
		     * @description     : list of classes
		     * @param           : null
		     * @return          : null
	*/
	public function passes_list_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
					$this->form_validation->set_rules('class_id', 'Class Id', 'required|trim', array('required' => $this->lang->line('class_id_required')));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$response = array();
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;
						$business_id = $this->input->post('business_id');
						$class_id = $this->input->post('class_id');
						$where = array("business_id" => $business_id, "service_id" => $class_id, "service_type" => "1", "status" => "Active");
						$passes_data = $this->dynamic_model->getdatafromtable('business_passes', $where, "*", $limit, $offset, 'create_dt');
						if (!empty($passes_data)) {
							foreach ($passes_data as $value) {
								$passesdata['pass_id'] = $value['id'];
								$passesdata['pass_name'] = ucwords($value['pass_name']);
								$classes_data = $this->dynamic_model->getdatafromtable('business_class', array("id" => $class_id));
								$passesdata['class_name'] = (!empty($classes_data)) ? ucwords($classes_data[0]['class_name']) : "";
								$passType = (!empty($value['pass_type'])) ? $value['pass_type'] : '';
								$pass_type_subcat = (!empty($value['pass_type_subcat'])) ? $value['pass_type_subcat'] : '';
								$pass_type = get_passes_type_name($passType, $pass_type_subcat);

								$passesdata['pass_type'] = $pass_type;
								$passesdata['start_date'] = date("d M Y ", $value['purchase_date']);
								$passesdata['end_date'] = date("d M Y ", $value['pass_end_date']);
								$passesdata['pass_validity'] = $value['pass_validity'] . ' ' . "Month";
								$response[] = $passesdata;
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
				}
			}
		}
		echo json_encode($arg);
	}
	/****************Function Passes details**********************************
		     * @type            : Function
		     * @Author          : Arpit
		     * @function name   : passes_details
		     * @description     : passes details
		     * @param           : null
		     * @return          : null
	*/
	public function passes_details_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pass_id', 'Pass Id', 'required|trim', array('required' => $this->lang->line('pass_id_required')));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$response = array();
						$usid = $userdata['data']['id'];
						$pass_id = $this->input->post('pass_id');
						$user_booking_id = $this->input->post('user_booking_id');
						$passes_data = getpassesdetails($pass_id, $usid, $user_booking_id);
						if (!empty($passes_data)) {
							$business_info = $this->dynamic_model->getQueryRowArray('SELECT *  FROM business WHERE id = ' . $passes_data['business_id']);
							$passes_data['business_logo'] = empty($business_info['business_image']) ? '' : site_url() . 'uploads/business/' . $business_info['business_image'];

							$pass_type_subcat = $passes_data['pass_type_subcat'];
							$amount = $passes_data['amount'];
							/* if($pass_type_subcat == '36'){
								                             $today_dt = date('d');
								                                $a_date = date("Y-m-d");
								                               $lastmonth_dt = date("t", strtotime($a_date));
								                               $diff_dt = $lastmonth_dt - $today_dt;
								                               $diff_dt = $diff_dt + 1;
								                               // echo '--'.$amount;
								                                $rt = date("Y-m-t", strtotime($a_date));
								                                $recurring_date = $rt;
								                                $pass_end_date = strtotime($rt);
								                                $passes_remaining_count = $diff_dt;

								                               $per_day_amt = $amount/$lastmonth_dt;

								                                $per_day_amt = round($per_day_amt,2);
								                                $Amt = $per_day_amt * $diff_dt;

								                                $passes_data['amount'] = number_format($Amt,2);
								                        }else{
								                           $passes_data['amount'] = number_format($passes_data['amount'],2);
							*/
							$passes_data['amount'] = number_format($passes_data['amount'], 2);
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $passes_data;
							$arg['message'] = $this->lang->line('record_found');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = json_decode('{}');
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}
	/****************Function passes status change **********************************
		     * @type            : Function
		     * @Author          : Arpit
		     * @function name   : passes_status_change
		     * @description     : passes status change
		     * @param           : null
		     * @return          : null
	*/
	public function passes_status_change12_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('service_type', 'Service Type', 'required|trim', array('required' => $this->lang->line('service_type_required')));
					$this->form_validation->set_rules('service_id', 'Service Id', 'required|trim', array('required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('passes_status', 'Service Id', 'required|trim', array('required' => $this->lang->line('passes_status_required')));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$waitng_list_no = '';
						$usid = $userdata['data']['id'];
						$updateData = $response = array();
						$time = time();
						$date = date("Y-m-d", $time);
						$service_id = $this->input->post('service_id');
						//service_type=> 1 class 2 workshop 3 trainer
						$service_type = $this->input->post('service_type');
						// passes_status=> checkin checkout cancel
						$passes_status = $this->input->post('passes_status');
						$condition = "user_id=" . $usid . " AND service_id=" . $service_id;
						$signup_check = $this->dynamic_model->getdatafromtable('user_attendance', $condition);
						if (empty($signup_check)) {
							if ($passes_status !== 'singup') {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['data'] = array();
								$arg['message'] = $this->lang->line('passes_status_error');
								echo json_encode($arg);exit;
							}

							$insertData = array(
								'user_id' => $usid,
								'status' => $passes_status,
								'service_id' => $service_id,
								'service_type' => $service_type,
								'checkin_time' => 0,
								'checkout_time' => 0,
								'signup_status' => 1,
								'create_dt' => $time,
								'update_dt' => $time,
							);

							$checkId = $this->dynamic_model->insertdata('user_attendance', $insertData);

						} else {

							//find data today wise
							$whe = "user_id=" . $usid . " AND service_id=" . $service_id . " AND service_type=" . $service_type . " AND DATE(FROM_UNIXTIME(create_dt))='" . $date . "'";
							$check_pass = $this->dynamic_model->getdatafromtable('user_attendance', $whe);
							$room_capacity = $waitng_list_no = '';
							$where = array('id' => $service_id, 'status' => "Active");
							//find capcity class or workshop
							if ($service_type == 1) {
								$business_class = $this->dynamic_model->getdatafromtable('business_class', $where);
								$room_capacity = (!empty($business_class[0]['capacity'])) ? $business_class[0]['capacity'] : 0;

							} elseif ($service_type == 2) {
								$business_workshop = $this->dynamic_model->getdatafromtable('business_workshop', $where);
								$room_capacity = (!empty($business_workshop[0]['capacity'])) ? $business_workshop[0]['capacity'] : 0;
							}

							// $where1="service_id=".$service_id." AND service_type=".$service_type." AND status='checkin'";

							//check room capacity
							$where1 = "service_id=" . $service_id . " AND service_type=" . $service_type . " AND status='checkin' AND DATE(FROM_UNIXTIME(create_dt))='" . $date . "'";
							$check_in_count = getdatacount('user_attendance', $where1);
							$getTime = strtotime(date('H:i:s', $time));
							if (empty($check_pass)) {
								$insertData = array(
									'user_id' => $usid,
									'status' => $passes_status,
									'service_id' => $service_id,
									'service_type' => $service_type,
									'checkin_time' => $time,
									'checkout_time' => 0,
									'signup_status' => 1,
									'create_dt' => $time,
									'update_dt' => $time,
								);

								$checkId = $this->dynamic_model->insertdata('user_attendance', $insertData);

								$msg1 = $this->lang->line('check_in_succ');
							} elseif (!empty($check_pass)) {
								if ($passes_status == 'checkin') {
									if ($room_capacity == $check_in_count) {
										$where2 = "service_id=" . $service_id . " AND service_type=" . $service_type . " AND status='waiting' AND DATE(FROM_UNIXTIME(create_dt))='" . $date . "'";
										$wating_data = $this->dynamic_model->getdatafromtable('user_attendance', $where2);
										//if already waiting list
										$where3 = "user_id=" . $usid . " AND service_id=" . $service_id . " AND service_type=" . $service_type . " AND status='waiting' AND DATE(FROM_UNIXTIME(create_dt))='" . $date . "'";
										$already_wating_data = $this->dynamic_model->getdatafromtable('user_attendance', $where3);
										//	print_r($wating_data);die;
										$already_waiting_no = (!empty($already_wating_data[0]['waitng_list_no'])) ? $already_wating_data[0]['waitng_list_no'] : 0;
										$waiting_no = (!empty($wating_data[0]['waitng_list_no'])) ? $wating_data[0]['waitng_list_no'] : 0;
										if (empty($waiting_no)) {
											$waitng_list_no = 1;
										} elseif (!empty($already_wating_data)) {
											$waitng_list_no = $already_waiting_no;
										} else {
											$waitng_list_no = $waiting_no + 1;
										}
										//echo $waitng_list_no;die;
										$updateData['status'] = 'waiting';
										$updateData['waitng_list_no'] = $waitng_list_no;

										$msg1 = $this->lang->line('waiting_msg');
									} else {
										$updateData['status'] = $passes_status;
										$updateData['checkin_time'] = $getTime;
										$msg1 = $this->lang->line('check_in_succ');
									}
								} elseif ($passes_status == 'checkout') {
									$updateData['status'] = $passes_status;
									$updateData['checkout_time'] = $getTime;
								} elseif ($passes_status == 'cancel') {

									$updateData['status'] = $passes_status;
								} elseif ($passes_status == 'singup') {

									$updateData['status'] = $passes_status;
								}
								$updateData['update_dt'] = $time;
								$where3 = array('id' => $check_pass[0]['id']);
								$checkId = $this->dynamic_model->updateRowWhere('user_attendance', $where3, $updateData);
								// echo $this->db->last_query();die;
							}
						}
						if ($passes_status == 'singup') {
							$msg = $this->lang->line('check_signup_succ');
						} elseif ($passes_status == 'checkin') {
							$msg = $msg1;
						} elseif ($passes_status == 'checkout') {
							$msg = $this->lang->line('check_out_succ');

						} elseif ($passes_status == 'cancel') {
							$msg = $this->lang->line('attendance_cancel_succ');
						}
						if ($checkId) {
							// $passes_status=get_passes_checkin_status($usid,$service_id,$service_type,$date);
							$response = array("wating_list_no" => "$waitng_list_no");
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['message'] = $msg;
							$arg['data'] = $response;
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['data'] = json_decode('{}');
							$arg['message'] = $this->lang->line('server_problem');
						}

					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function passes_status_change_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('service_type', 'Service Type', 'required|trim', array('required' => $this->lang->line('service_type_required')));
					$this->form_validation->set_rules('service_id', 'Service Id', 'required|trim', array('required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('passes_status', 'Service Id', 'required|trim', array('required' => $this->lang->line('passes_status_required')));
					$this->form_validation->set_rules('schedule_id', 'Schedule Id', 'required|trim', array('required' => 'Schedule id is required'));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {

						$lat = $this->input->get_request_header('lat');
						$lang = $this->input->get_request_header('lang');
						$room_capacity = $waitng_list_no = '';
						$usid = $userdata['data']['id'];
						$updateData = $response = array();
						$time = time();
						$date = date("Y-m-d", $time);
						$service_id = $this->input->post('service_id');
						$class_id = $this->input->post('service_id');
						$service_type = $this->input->post('service_type');
						$passes_status = $this->input->post('passes_status');
						$today_date = date("Y-m-d");
						$where = array('id' => $service_id, 'status' => "Active");
						$business_class_id = 0;
						$room_capacity_count = 0;
						$waitlist_capacity = 0;
						$schedule_id = $this->input->post('schedule_id');
						$pass_id = $this->input->post('pass_id');
						$attendance_id = $this->input->post('attendance_id');

						if (!empty($attendance_id) && empty($pass_id)) {
							$whe = "id = '" . $attendance_id . "'";
							$passes_data = $this->dynamic_model->getdatafromtable('user_attendance', $whe);
							if (!empty($passes_data)) {
								$pass_id = $passes_data[0]['pass_id'];
							}
						}

						$day_update = 1;
						if (!empty($pass_id)) {
							$whe = "id = '" . $pass_id . "'";
							$passes_data = $this->dynamic_model->getdatafromtable('business_passes', $whe);
							if (!empty($passes_data)) {
								$pass_type = $passes_data[0]['pass_type'];
								if ($pass_type == '10' || $pass_type == '37') {
									$day_update = 0;
								}
							}
						}

						//echo '---'.$day_update; die;

						if ($passes_status == 'singup') {
							if (empty($pass_id)) {
								$arg['status'] = 0;
								$arg['error_code'] = 0;
								$arg['error_line'] = __line__;
								$arg['message'] = 'Please choose pass.';
								echo json_encode($arg);exit;
							}

							$whe = "user_id = '" . $usid . "' AND schedule_id = '" . $schedule_id . "' AND service_id = '" . $service_id . "' AND status = 'cancel'";
							$data_check = $this->dynamic_model->getdatafromtable('user_attendance', $whe);
							if (!empty($data_check)) {
								# code...
								$where1 = array('id' => $data_check[0]['id']);
								$deleteCart = $this->dynamic_model->deletedata('user_attendance', $where1);
							}

						} else {
							if (empty($attendance_id)) {
								$arg['status'] = 0;
								$arg['error_code'] = 0;
								$arg['error_line'] = __line__;
								$arg['message'] = 'Please send attendance id.';
								echo json_encode($arg);exit;
							}
						}

						if ($service_type == 1) {
							$business_class = $this->dynamic_model->getdatafromtable('business_class', $where);

							$business_id = $business_class[0]['business_id'];
							$room_capacity = (!empty($business_class[0]['capacity'])) ? $business_class[0]['capacity'] : 0;
							$class_days_prior_signup = $business_class[0]['class_days_prior_signup'] ? $business_class[0]['class_days_prior_signup'] : 1;
							$start_date = $business_class[0]['start_date'];
							$duration = $business_class[0]['duration'];
							$room_capacity_count = $room_capacity;
							$waitlist_capacity = (!empty($business_class[0]['class_waitlist_count'])) ? $business_class[0]['class_waitlist_count'] : 0;
							$total_count = $room_capacity + $waitlist_capacity;
							$business_class_id = (!empty($business_class[0]['id'])) ? $business_class[0]['id'] : 0;
							$getSchedule = $this->dynamic_model->getQueryRowArray('SELECT * FROM class_scheduling_time where id = ' . $schedule_id);

							$getScheduleInfo = get_checkin_class_or_workshop_daily_count($business_class[0]['id'], 1, $getSchedule['scheduled_date'], $schedule_id);
							//  print_r($getScheduleInfo); die;

							$date = $getSchedule['scheduled_date'];
							$scheduled_start_time = $getSchedule['from_time'];
							$scheduled_end_time = $getSchedule['to_time'];
							// $class_id = $getSchedule['class_id'];

							if ($date == date('Y-m-d')) {
								$st = date('h:i:s A', $scheduled_end_time);
								$rt = $date . ' ' . $st;
								$scheduled_end_date_time = strtotime($rt);

								/*if($scheduled_end_date_time < $time){
									                                $arg['status']     = 0;
									                                $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
									                                $arg['error_line']= __line__;
									                                $arg['data']       = json_decode('{}');
									                                $arg['message']    = 'Class time passed..';
									                                echo json_encode($arg);exit;
								*/
							}

							if ($passes_status != 'singup') {
								$whe = "id = '" . $attendance_id . "'";
								$pass_status_check = $this->dynamic_model->getdatafromtable('user_attendance', $whe);
								if (!empty($pass_status_check)) {
									$pass_id = $pass_status_check[0]['pass_id'];
								}
							}

							$condition_waiting_count = "status = 'waiting' AND id = '" . $attendance_id . "'";
							$wait_count = $this->db->get_where('user_attendance', $condition_waiting_count)->num_rows();

							$condition_waiting_count = "status = 'waiting' AND service_type = '1' AND schedule_id = '" . $schedule_id . "'";
							$wait_count_check = $this->db->get_where('user_attendance', $condition_waiting_count)->num_rows();

							//echo '--'.$passes_status.'---'.$wait_count.'==='.$waitlist_capacity;
							//print_r($wait_count); die;
							if ($passes_status == 'singup' && !empty($wait_count_check)) {
								if ($wait_count_check >= $waitlist_capacity) {
									$arg['status'] = 0;
									$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line'] = __line__;
									$arg['data'] = json_decode('{}');
									$arg['message'] = 'The Class is full and no more clients can sign up for the class unless someone cancels or there is a no show..';
									echo json_encode($arg);exit;
								}
							}

							$whe = "user_id = '" . $usid . "' AND business_id = '" . $business_id . "' AND passes_remaining_count != '0' AND passes_status = '1' AND status = 'Success' AND service_id = '" . $pass_id . "'";
							$pass_status_check = $this->dynamic_model->getdatafromtable('user_booking', $whe);

							$user_booking_id = '';
							$passes_remaining_count = 0;

							if (!empty($pass_status_check)) {
								$user_booking_id = $pass_status_check[0]['id'];
								$passes_remaining_count = ($pass_status_check[0]['passes_remaining_count'] - 1);
							}

							// Check same user signup request
							if ($passes_status == 'singup') {
								if (empty($pass_status_check)) {
									$arg['status'] = 0;
									$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line'] = __line__;
									$arg['data'] = json_decode('{}');
									$arg['message'] = 'Please purchase pass then you can singup';
									echo json_encode($arg);exit;
								}

								//$whe="user_id = '".$usid."' AND service_id = '".$service_id."' AND checkin_dt = '".$date."' AND status = 'singup'";
								$whe = "user_id = '" . $usid . "' AND service_id = '" . $service_id . "' AND schedule_id = '" . $schedule_id . "' AND checkin_dt = '" . $date . "' AND status != 'cancel'";
								$attendance = $this->dynamic_model->getdatafromtable('user_attendance', $whe);
								if (!empty($attendance)) {
									$arg['status'] = 0;
									$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line'] = __line__;
									$arg['data'] = json_decode('{}');
									$arg['message'] = 'You have already signed up';
									echo json_encode($arg);exit;
								}
							}

							// Check new user signup request

							if (($wait_count + $getScheduleInfo) >= $total_count && $passes_status == 'singup') {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['data'] = json_decode('{}');
								$arg['message'] = 'Class capacity is already full.';
								echo json_encode($arg);exit();
							}

							if ($passes_status == 'singup') {

								//$start_date = strtotime($start_date);
								$start_date = strtotime($date);
								$unixTimestamp = $start_date - ((int) $class_days_prior_signup * 24 * 60 * 60);

								$today = time();
								if ($today >= $unixTimestamp) {

								} else {
									$unixTimestamp = date('Y-m-d', $unixTimestamp);
									$arg['status'] = 0;
									$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line'] = __line__;
									$arg['data'] = json_decode('{}');
									$arg['message'] = $class_days_prior_signup . ' day prior, You will be signup this class.';
									echo json_encode($arg);exit;
								}

							}

							if ($passes_status == 'checkin') {
								//$today_date = date('Y-m-d');

								$where = array('scheduled_date' => $date,
									'class_id' => $service_id,
									'business_id' => $business_id,
								);
								$class_data = $this->dynamic_model->getdatafromtable('class_scheduling_time', $where);
								if (empty($class_data)) {
									$arg['status'] = 0;
									$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line'] = __line__;
									$arg['data'] = json_decode('{}');
									$arg['message'] = 'Class not available today.';
									echo json_encode($arg);exit;
								} else {
									$from_time = $class_data[0]['from_time'];
									$scheduled_end_time = $class_data[0]['to_time'];
									$scheduled_date = $class_data[0]['scheduled_date'];

									$from_time = $from_time - 15 * 60;
									$time = time();

									$sed = date('h:i:s A', $scheduled_end_time);
									$rt = $scheduled_date . ' ' . $sed;
									$scheduled_end_date_time = strtotime($rt);

									$sed = date('h:i:s A', $from_time);
									$rt = $scheduled_date . ' ' . $sed;
									$scheduled_start_date_time = strtotime($rt);

									if ($from_time > $time) {
										$unixTimestamp = date('h:i:s A', $from_time);
										$arg['status'] = 0;
										$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
										$arg['error_line'] = __line__;
										$arg['data'] = json_decode('{}');
										$arg['message'] = 'System will allow you to check In before 15 min of class start time.';
										echo json_encode($arg);exit;
									}

									/*else if($scheduled_end_date_time < $time){
										                                        $unixTimestamp = date('h:i:s A',$from_time);
										                                        $arg['status']     = 0;
										                                        $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
										                                        $arg['error_line']= __line__;
										                                        $arg['data']       = json_decode('{}');
										                                        $arg['message']    = 'Class time is over.';
										                                        echo json_encode($arg);exit;
									*/

									$st = date('h:i:s A', $scheduled_end_time);
									$rt = $date . ' ' . $st;
									$scheduled_end_date_time = strtotime($rt);

								}
							}

						} elseif ($service_type == 2) {
							$business_workshop = $this->dynamic_model->getdatafromtable('business_workshop', $where);
							$business_id = (!empty($business_workshop[0]['business_id'])) ? $business_workshop[0]['business_id'] : 0;
							$room_capacity = (!empty($business_workshop[0]['capacity'])) ? $business_workshop[0]['capacity'] : 0;

							$duration = $business_workshop[0]['duration'];

							$class_days_prior_signup = $business_workshop[0]['workshop_days_prior_signup'] ? $business_workshop[0]['workshop_days_prior_signup'] : 1;
							$start_date = $business_workshop[0]['start_date'];
							if ($passes_status == 'singup') {

								$start_date = strtotime($start_date);
								$unixTimestamp = $start_date - ((int) $class_days_prior_signup * 24 * 60 * 60);

								$today = time();
								if ($today >= $unixTimestamp) {

								} else {
									$unixTimestamp = date('Y-m-d', $unixTimestamp);
									$arg['status'] = 0;
									$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line'] = __line__;
									$arg['data'] = json_decode('{}');
									$arg['message'] = 'Sing up is open ' . $unixTimestamp;
									echo json_encode($arg);exit;
								}
							}
						}

						$message_waiting = '';
						$condition = "user_id=" . $usid . " AND service_id=" . $service_id . ' AND checkin_dt = "' . $date . '" AND schedule_id = ' . $schedule_id;
						$signup_check = $this->dynamic_model->getdatafromtable('user_attendance', $condition);

						if (empty($signup_check)) {

							/* Freash user record */

							if ($passes_status !== 'singup') {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['data'] = json_decode('{}');
								$arg['message'] = $this->lang->line('passes_status_error');
								echo json_encode($arg);exit;
							}

							// Check current user signup
							$getCurrentStatus = get_checkin_class_or_workshop_daily_count($business_class_id, 1, $date, $schedule_id); // Signup and checkin user
							$couter = $getCurrentStatus + 1;

							$updateData = array(
								'passes_remaining_count' => $passes_remaining_count,
							);
							if (!empty($day_update)) {
								$this->dynamic_model->updateRowWhere('user_booking', array('id' => $user_booking_id), $updateData);
							}

							// User signup
							$insertData = array(
								'user_id' => $usid,
								'status' => $passes_status,
								'service_id' => $service_id,
								'service_type' => $service_type,
								'checkin_time' => 0,
								'checkout_time' => 0,
								'signup_status' => 1,
								'create_dt' => $time,
								'update_dt' => $time,
								'checkin_dt' => $date,
								'schedule_id' => $schedule_id,
								'pass_id' => $pass_id,
							);

							$current_Waiting_number = 0;
							if ($couter > $room_capacity_count) {

								$condition_waiting_count = 'status = "waiting" AND service_id = ' . $service_id . ' AND checkin_dt = "' . $date . '" AND schedule_id = ' . $schedule_id;
								$wait_count = $this->db->get_where('user_attendance', $condition_waiting_count)->num_rows();
								$current_Waiting_number = $wait_count + 1;
								$insertData['status'] = 'waiting';
								$insertData['waitng_list_no'] = $current_Waiting_number;
								$passes_status = 'waiting';
							}

							$checkId = $this->dynamic_model->insertdata('user_attendance', $insertData);
							if ($current_Waiting_number > 0) {
								$message_waiting = 'Current waiting list number is : ' . $current_Waiting_number;
							}

						} else {

							// Pass status only checkin or cancel
							$getSchedule = $this->dynamic_model->getQueryRowArray('SELECT * FROM class_scheduling_time where id = ' . $schedule_id);

							//echo $passes_status.'--'.$signup_check[0]['status']; die;
							if ($passes_status == 'checkin') {

								if ($signup_check[0]['status'] == 'singup') {
									if (empty($pass_status_check)) {
										$arg['status'] = 0;
										$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
										$arg['error_line'] = __line__;
										$arg['data'] = json_decode('{}');
										$arg['message'] = 'Please purchase pass then you can singup';
										echo json_encode($arg);exit;
									}

									$updateData = array(
										'passes_remaining_count' => $passes_remaining_count,
									);
									// print_r($updateData); die;
									//$this->dynamic_model->updateRowWhere('user_booking', array('id' => $user_booking_id), $updateData);

									$updateData = array(
										'status' => $passes_status,
										'checkin_time' => $getSchedule['from_time'],
										'checkout_time' => $getSchedule['to_time'],
										'signup_status' => 1,
										'update_dt' => $time,
										'pass_id' => $pass_id,
									);
									// print_r($updateData); die;
									$checkId = $this->dynamic_model->updateRowWhere('user_attendance', array('id' => $signup_check[0]['id']), $updateData);
									$msg1 = $this->lang->line('check_in_succ');
								} else {
									$arg['status'] = 0;
									$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line'] = __line__;
									$arg['data'] = json_decode('{}');
									$arg['message'] = 'Request not allowed';
									echo json_encode($arg);exit();
								}

							} elseif ($passes_status == 'cancel') {
								// Cancel user slot
								if ($signup_check[0]['status'] == 'checkin') {
									$arg['status'] = 0;
									$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line'] = __line__;
									$arg['data'] = json_decode('{}');
									$arg['message'] = 'Cancellation is not possible.';
									echo json_encode($arg);exit();

								} else {

									$current_status = $signup_check[0]['status'];

									if ($current_status == 'singup') {

										$current_waiting_condition = "status = 'waiting' AND service_id=" . $service_id . ' AND checkin_dt = "' . $date . '" AND schedule_id = ' . $schedule_id;
										$current_waiting = $this->dynamic_model->getdatafromtable('user_attendance', $current_waiting_condition);

										$updateArray = array();
										if (!empty($current_waiting)) {
											foreach ($current_waiting as $cw) {
												$set_status = 'waiting';
												$waitng_list_no = $cw['waitng_list_no'];
												if ($waitng_list_no == 1) {
													$set_status = 'singup';
												}
												$waitng_list_no = $waitng_list_no - 1;
												array_push($updateArray, array('id' => $cw['id'], 'status' => $set_status, 'waitng_list_no' => $waitng_list_no));
											}
										}

										if ($current_status == 'singup') {

											$current_time = time();
											$whe = "user_id = '" . $usid . "' AND business_id = '" . $business_id . "' AND service_id = '" . $pass_id . "' AND status = 'Success' AND passes_end_date >= '" . $current_time . "'";
											$pass_status_check = $this->dynamic_model->getdatafromtable('user_booking', $whe);
											if (!empty($pass_status_check)) {
												$user_booking_id = $pass_status_check[0]['id'];
												$passes_remaining_count = $pass_status_check[0]['passes_remaining_count'];
											}

											$passes_remaining_count = $passes_remaining_count + 1;
											$updateData = array(
												'passes_remaining_count' => $passes_remaining_count,
												'passes_status' => 1,
											);

											if (!empty($day_update)) {
												$this->dynamic_model->updateRowWhere('user_booking', array('id' => $user_booking_id), $updateData);
											}
										}

										$updateData = array(
											'user_id' => $usid,
											'status' => $passes_status,
											'update_dt' => $time,
										);
										$checkId = $this->dynamic_model->updateRowWhere('user_attendance', array('id' => $signup_check[0]['id']), $updateData);

										if (!empty($updateArray)) {
											$this->db->update_batch('user_attendance', $updateArray, 'id');
										}

									} elseif ($current_status == 'waiting') {

										$current_wait_list_number = $signup_check[0]['waitng_list_no'];

										$current_waiting_condition = "status = 'waiting' AND waitng_list_no > " . $current_wait_list_number . " AND service_id=" . $service_id . ' AND checkin_dt = "' . $date . '" AND schedule_id = ' . $schedule_id;
										$current_waiting = $this->dynamic_model->getdatafromtable('user_attendance', $current_waiting_condition);

										$updateArray = array();
										if (!empty($current_waiting)) {
											foreach ($current_waiting as $cw) {
												$waitng_list_no = $cw['waitng_list_no'];
												$waitng_list_no = $waitng_list_no - 1;
												array_push($updateArray, array('id' => $cw['id'], 'waitng_list_no' => $waitng_list_no));
											}
										}

										/*$updateData =   array(
											                                        'passes_remaining_count' =>  $passes_remaining_count
										*/
										//$this->dynamic_model->updateRowWhere('user_booking', array('id' => $user_booking_id), $updateData);

										$updateData = array(
											'user_id' => $usid,
											'status' => $passes_status,
											'waitng_list_no' => 0,
											'update_dt' => $time,
										);
										$checkId = $this->dynamic_model->updateRowWhere('user_attendance', array('id' => $signup_check[0]['id']), $updateData);

										if (!empty($updateArray)) {
											$this->db->update_batch('user_attendance', $updateArray, 'id');
										}

									}

								}
							}
						}

						if ($passes_status == 'singup') {
							$msg = $this->lang->line('check_signup_succ');
						} elseif ($passes_status == 'checkin') {
							$msg = $msg1;
						} elseif ($passes_status == 'checkout') {
							$msg = $this->lang->line('check_out_succ');

						} elseif ($passes_status == 'cancel') {
							if ($message_waiting == '0') {
								$msg = 'Not allowed';
							} else {
								$msg = $this->lang->line('attendance_cancel_succ');
							}

						} elseif ($passes_status == 'waiting') {
							$msg = $message_waiting;
						}

						if ($checkId) {
							$response = array("wating_list_no" => "$waitng_list_no");
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['message'] = $msg;
							$arg['data'] = $response;
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['data'] = json_decode('{}');
							$arg['message'] = $this->lang->line('server_problem');
						}

					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function passes_status_change_post_old() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('service_type', 'Service Type', 'required|trim', array('required' => $this->lang->line('service_type_required')));
					$this->form_validation->set_rules('service_id', 'Service Id', 'required|trim', array('required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('passes_status', 'Service Id', 'required|trim', array('required' => $this->lang->line('passes_status_required')));
					$this->form_validation->set_rules('schedule_id', 'Schedule Id', 'required|trim', array('required' => 'Schedule id is required'));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {

						$lat = $this->input->get_request_header('lat');
						$lang = $this->input->get_request_header('lang');
						$room_capacity = $waitng_list_no = '';
						$usid = $userdata['data']['id'];
						$updateData = $response = array();
						$time = time();
						$date = date("Y-m-d", $time);
						$service_id = $this->input->post('service_id');
						//service_type=> 1 class 2 workshop 3 trainer
						$service_type = $this->input->post('service_type');
						// passes_status=> checkin checkout cancel
						$passes_status = $this->input->post('passes_status');
						$today_date = date("Y-m-d");

						$where = array('id' => $service_id, 'status' => "Active");
						//find capcity class or workshop
						if ($service_type == 1) {
							$business_class = $this->dynamic_model->getdatafromtable('business_class', $where);
							$business_id = (!empty($business_class[0]['business_id'])) ? $business_class[0]['business_id'] : 0;
							$room_capacity = (!empty($business_class[0]['capacity'])) ? $business_class[0]['capacity'] : 0;
							$class_days_prior_signup = $business_class[0]['class_days_prior_signup'] ? $business_class[0]['class_days_prior_signup'] : 1;
							$start_date = $business_class[0]['start_date'];
							$duration = $business_class[0]['duration'];

							$waitlist_capacity = (!empty($business_class[0]['class_waitlist_count'])) ? $business_class[0]['class_waitlist_count'] : 0;
							$total_count = $room_capacity + $waitlist_capacity;

							$schedule_id = $this->input->post('schedule_id');
							$getSchedule = $this->dynamic_model->getQueryRowArray('SELECT * FROM class_scheduling_time where id = ' . $this->input->post('schedule_id'));

							$getScheduleInfo = get_checkin_class_or_workshop_daily_count($business_class[0]['id'], 1, $getSchedule['scheduled_date']);
							if ($getScheduleInfo > $total_count) {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								// $arg['data']       = json_decode('{}');
								$arg['message'] = 'Class seat are full';
							}

							$date = $getSchedule['scheduled_date'];

							if ($passes_status == 'singup') {
								$whe = "user_id = '" . $usid . "' AND service_id = '" . $service_id . "' AND checkin_dt = '" . $date . "' AND status = 'singup'";
								$attendance = $this->dynamic_model->getdatafromtable('user_attendance', $whe);
								if (!empty($attendance)) {
									$arg['status'] = 0;
									$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line'] = __line__;
									// $arg['data']       = json_decode('{}');
									$arg['message'] = 'You have already signed up';
									echo json_encode($arg);exit;

								}
							}

							if ($passes_status == 'singup') {

								$start_date = strtotime($start_date);
								$unixTimestamp = $start_date - ((int) $class_days_prior_signup * 24 * 60 * 60);

								$today = time();
								if ($today >= $unixTimestamp) {

								} else {
									$unixTimestamp = date('Y-m-d', $unixTimestamp);
									$arg['status'] = 0;
									$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line'] = __line__;
									// $arg['data']       = json_decode('{}');
									$arg['message'] = 'Sing up is open ' . $unixTimestamp;
									echo json_encode($arg);exit;
								}

							}

							if ($passes_status == 'checkin') {
								//$today_date = date('Y-m-d');

								$where = array('scheduled_date' => $date,
									'class_id' => $service_id,
									'business_id' => $business_id,
								);
								$class_data = $this->dynamic_model->getdatafromtable('class_scheduling_time', $where);
								if (empty($class_data)) {
									$arg['status'] = 0;
									$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line'] = __line__;
									// $arg['data']       = json_decode('{}');
									$arg['message'] = 'Class not available today.';
									echo json_encode($arg);exit;
								} else {
									$from_time = $class_data[0]['from_time'];
									$from_time = $from_time - 15 * 60;
									$time = time();
									if ($from_time > $time) {
										$unixTimestamp = date('h:i:s A', $from_time);
										$arg['status'] = 0;
										$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
										$arg['error_line'] = __line__;
										// $arg['data']       = json_decode('{}');
										$arg['message'] = 'Chekin allow after ' . $unixTimestamp;
										echo json_encode($arg);exit;
									}

								}
							}

						} elseif ($service_type == 2) {
							$business_workshop = $this->dynamic_model->getdatafromtable('business_workshop', $where);
							$business_id = (!empty($business_workshop[0]['business_id'])) ? $business_workshop[0]['business_id'] : 0;
							$room_capacity = (!empty($business_workshop[0]['capacity'])) ? $business_workshop[0]['capacity'] : 0;

							$duration = $business_workshop[0]['duration'];

							$class_days_prior_signup = $business_workshop[0]['workshop_days_prior_signup'] ? $business_workshop[0]['workshop_days_prior_signup'] : 1;
							$start_date = $business_workshop[0]['start_date'];
							if ($passes_status == 'singup') {

								$start_date = strtotime($start_date);
								$unixTimestamp = $start_date - ((int) $class_days_prior_signup * 24 * 60 * 60);

								$today = time();
								if ($today >= $unixTimestamp) {

								} else {
									$unixTimestamp = date('Y-m-d', $unixTimestamp);
									$arg['status'] = 0;
									$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line'] = __line__;
									// $arg['data']       = json_decode('{}');
									$arg['message'] = 'Sing up is open ' . $unixTimestamp;
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
						$condition = "user_id=" . $usid . " AND service_id=" . $service_id;
						$signup_check = $this->dynamic_model->getdatafromtable('user_attendance', $condition);
						if (empty($signup_check)) {
							if ($passes_status !== 'singup') {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								// $arg['data']       = json_decode('{}');
								$arg['message'] = $this->lang->line('passes_status_error');
								echo json_encode($arg);exit;
							}

							$insertData = array(
								'user_id' => $usid,
								'status' => $passes_status,
								'service_id' => $service_id,
								'service_type' => $service_type,
								'checkin_time' => 0,
								'checkout_time' => 0,
								'signup_status' => 1,
								'create_dt' => $time,
								'update_dt' => $time,
								'checkin_dt' => $date,
								'schedule_id' => $schedule_id,
							);

							$checkId = $this->dynamic_model->insertdata('user_attendance', $insertData);

						} else {

							//find data today wise
							$whe = "user_id=" . $usid . " AND service_id=" . $service_id . " AND service_type=" . $service_type . " AND DATE(FROM_UNIXTIME(create_dt))='" . $date . "'";
							$check_pass = $this->dynamic_model->getdatafromtable('user_attendance', $whe);

							//check room capacity
							$where1 = "service_id=" . $service_id . " AND service_type=" . $service_type . " AND status='checkin' AND DATE(FROM_UNIXTIME(create_dt))='" . $date . "'";
							$check_in_count = getdatacount('user_attendance', $where1);
							$getTime = strtotime(date('H:i:s', $time));

							$checkout_time = $time + ((int) $duration * 60);

							if (empty($check_pass)) {
								$insertData = array(
									'user_id' => $usid,
									'status' => $passes_status,
									'service_id' => $service_id,
									'service_type' => $service_type,
									'checkin_time' => $time,
									'checkout_time' => $checkout_time,
									'signup_status' => 1,
									'create_dt' => $time,
									'update_dt' => $time,
									'checkin_dt' => $date,
									'schedule_id' => $schedule_id,
								);

								$checkId = $this->dynamic_model->insertdata('user_attendance', $insertData);

								$msg1 = $this->lang->line('check_in_succ');
							} elseif (!empty($check_pass)) {
								if ($passes_status == 'checkin') {
									if ($room_capacity == $check_in_count) {
										$where2 = "service_id=" . $service_id . " AND service_type=" . $service_type . " AND status='waiting' AND DATE(FROM_UNIXTIME(create_dt))='" . $date . "'";
										$wating_data = $this->dynamic_model->getdatafromtable('user_attendance', $where2);
										//if already waiting list
										$where3 = "user_id=" . $usid . " AND service_id=" . $service_id . " AND service_type=" . $service_type . " AND status='waiting' AND DATE(FROM_UNIXTIME(create_dt))='" . $date . "'";
										$already_wating_data = $this->dynamic_model->getdatafromtable('user_attendance', $where3);
										//	print_r($wating_data);die;
										$already_waiting_no = (!empty($already_wating_data[0]['waitng_list_no'])) ? $already_wating_data[0]['waitng_list_no'] : 0;
										$waiting_no = (!empty($wating_data[0]['waitng_list_no'])) ? $wating_data[0]['waitng_list_no'] : 0;
										if (empty($waiting_no)) {
											$waitng_list_no = 1;
										} elseif (!empty($already_wating_data)) {
											$waitng_list_no = $already_waiting_no;
										} else {
											$waitng_list_no = $waiting_no + 1;
										}
										//echo $waitng_list_no;die;
										$updateData['status'] = 'waiting';
										$updateData['waitng_list_no'] = $waitng_list_no;

										$msg1 = $this->lang->line('waiting_msg');
									} else {
										$updateData['status'] = $passes_status;
										$updateData['checkin_time'] = $getTime;
										$msg1 = $this->lang->line('check_in_succ');
									}
								} elseif ($passes_status == 'checkout') {
									$updateData['status'] = $passes_status;
									$updateData['checkout_time'] = $getTime;
								} elseif ($passes_status == 'cancel') {

									$updateData['status'] = $passes_status;
								} elseif ($passes_status == 'singup') {

									$updateData['status'] = $passes_status;
								}

								$checkout_time = $time + ((int) $duration * 60);
								$updateData['checkout_time'] = $checkout_time;

								$updateData['update_dt'] = $time;
								$where3 = array('id' => $check_pass[0]['id']);
								$checkId = $this->dynamic_model->updateRowWhere('user_attendance', $where3, $updateData);
								// echo $this->db->last_query();die;
							}
						}
						if ($passes_status == 'singup') {
							$msg = $this->lang->line('check_signup_succ');
						} elseif ($passes_status == 'checkin') {
							$msg = $msg1;
						} elseif ($passes_status == 'checkout') {
							$msg = $this->lang->line('check_out_succ');

						} elseif ($passes_status == 'cancel') {
							$msg = $this->lang->line('attendance_cancel_succ');
						}
						if ($checkId) {
							// $passes_status=get_passes_checkin_status($usid,$service_id,$service_type,$date);
							$response = array("wating_list_no" => "$waitng_list_no");
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['message'] = $msg;
							$arg['data'] = $response;
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							// $arg['data']       = json_decode('{}');
							$arg['message'] = $this->lang->line('server_problem');
						}

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
	*/
	public function services_list_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$response = array();
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;
						$business_id = $this->input->post('business_id');

						$query = 'SELECT ser.id, ser.business_id, ser.service_name, ser.skills as service_category_id, ser.service_type, ms.name as service_category, ser.start_date_time as start_date, ser.end_date_time  as end_date, ser.is_client_visible, ser.duration, ser.amount, ser.tax1, ser.tax1_rate,  ser.tax2, ser.tax1_label, ser.tax2_label, ser.tax2_rate, ser.tip_option, ser.time_needed, ser.description, ser.cancel_policy, ser.create_dt, ser.create_dt as create_dt_utc FROM service ser LEFT JOIN manage_skills ms on (ms.id = ser.skills) where ser.business_id = ' . $business_id . ' AND ser.status = "Active" AND ser.is_client_visible = "yes" LIMIT ' . $limit . ' OFFSET ' . $offset;

						$service_data = $this->dynamic_model->getQueryResultArray($query);

						if (!empty($service_data)) {
							array_walk($service_data, function (&$key) {
								$workshop_price = $key['amount'];
								$workshop_tax_price = 0;
								$tax1_rate_val = 0;
								$tax2_rate_val = 0;
								$workshop_total_price = $workshop_price;
								if (strtolower($key['tax1']) == 'yes') {
									$tax1_rate = floatVal($key['tax1_rate']);
									$tax1_rate_val = (($workshop_price * $tax1_rate) / 100);
									$workshop_tax_price = $tax1_rate_val;
									$workshop_total_price = $workshop_price + $tax1_rate_val;

								}
								if (strtolower($key['tax2']) == 'yes') {
									$tax2_rate = floatVal($key['tax2_rate']);
									$tax2_rate_val = (($workshop_price * $tax2_rate) / 100);
									$workshop_tax_price = $tax1_rate_val + $tax2_rate_val;
									$workshop_total_price = $workshop_total_price + $tax2_rate_val;
								}

								$key['tax1_rate'] = number_format($tax1_rate_val, 2);
								$key['tax2_rate'] = number_format($tax2_rate_val, 2);
								$key['service_tax_price'] = number_format($workshop_tax_price, 2);
								$key['service_total_price'] = number_format($workshop_total_price, 2);
							});
						}

						if (!empty($service_data)) {
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $service_data;
							$arg['message'] = $this->lang->line('record_found');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = array();
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}
	public function services_list_old_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$response = array();
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;
						$business_id = $this->input->post('business_id');

						$where = array("business_id" => $business_id, "status" => "Active");
						$service_data = $this->dynamic_model->getdatafromtable('service', $where, "*", $limit, $offset, 'create_dt');
						//print_r($class_data);die;
						if (!empty($service_data)) {
							foreach ($service_data as $value) {
								$servicedata['service_id'] = $value['id'];
								$servicedata['service_name'] = ucwords($value['service_name']);
								$servicedata['service_category'] = get_categories($value['service_type'], 2);
								$servicedata['create_dt'] = date("d M Y ", $value['create_dt']);
								$response[] = $servicedata;
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
				}
			}
		}
		echo json_encode($arg);
	}
	/****************Function Get services details**********************************
		     * @type            : Function
		     * @Author          : Arpit
		     * @function name   : services_details
		     * @description     : Services details
		     * @param           : null
		     * @return          : null
	*/
	public function services_details_post_old() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
					$this->form_validation->set_rules('service_id', 'Service Id', 'required|trim', array('required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$usid = $userdata['data']['id'];
						$getcat = array();
						$time = time();
						$date = date("Y-m-d", $time);
						$response = $pass_arr = array();
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;

						$service_id = $this->input->post('service_id');
						$business_id = $this->input->post('business_id');

						$where = array("id" => $service_id, "business_id" => $business_id, "status" => "Active");
						$service_data = $this->dynamic_model->getdatafromtable('service', $where, "*", $limit, $offset, 'create_dt');
						//print_r($class_data);die;
						if (!empty($service_data)) {

							$servicedata['service_id'] = $service_data[0]['id'];
							$servicedata['service_name'] = ucwords($service_data[0]['service_name']);
							$servicedata['start_date_time'] = date("d M Y h:i ", $service_data[0]['start_date_time']);
							$servicedata['end_date_time'] = date("d M Y h:i ", $service_data[0]['end_date_time']);
							$servicedata['start_date_time_utc'] = $service_data[0]['start_date_time'];
							$servicedata['end_date_time_utc'] = $service_data[0]['end_date_time'];
							$servicedata['location'] = $service_data[0]['service_location'];
							$category = !empty($service_data[0]['service_type']) ? $service_data[0]['service_type'] : '';
							$servicedata['service_category'] = get_categories_data($category, 2);
							$instructor_data = $this->instructor_list_details($business_id, 3, $service_data[0]['id']);
							$servicedata['instructor_details'] = $instructor_data;
							$servicedata['create_dt'] = date("d M Y ", $service_data[0]['create_dt']);
							$response = $servicedata;

							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $response;
							$arg['message'] = $this->lang->line('record_found');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = json_decode('{}');
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}
	/****************Function add_cart **********************************
		     * @type            : Function
		     * @Author          : Arpit
		     * @function name   : add_cart
		     * @description     : Add Cart
		     * @param           : null
		     * @return          : null
	*/
	public function add_cart_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('service_type', 'Service Type', 'required|trim', array('required' => $this->lang->line('service_type_required')));
					$this->form_validation->set_rules('service_id', 'Service Id', 'required|trim', array('required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('amount', 'Amount', 'required', array('required' => $this->lang->line('amount_required')));
					$this->form_validation->set_rules('quantity', 'Quantity', 'required', array('required' => $this->lang->line('quantity_required')));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$usid = $userdata['data']['id'];
						$time = time();
						$service_id = $this->input->post('service_id');
						//service_type => 1 passes 2 services 3 product
						$service_type = $this->input->post('service_type');
						$quantity = $this->input->post('quantity');
						$amount = $this->input->post('amount');
						$amount = number_format((float) $amount, 2, '.', '');

						if ($service_type == 1) {
							//Check pass already added or not
							$whe = array('user_id' => $usid, 'service_id' => $service_id, 'service_type' => '1', 'passes_status' => '1');
							$chk_pass_booking = $this->dynamic_model->getdatafromtable('user_booking', $whe);
							if (!empty($chk_pass_booking)) {

								$passes_remaining_count = $chk_pass_booking[0]['passes_remaining_count'];
								if ($passes_remaining_count == '0') {
									$arg['status'] = 0;
									$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line'] = __line__;
									$arg['data'] = json_decode('{}');
									$arg['message'] = $this->lang->line('pass_already_msg');
									echo json_encode($arg);exit;
								}
							}
							$where = array('id' => $service_id, 'status' => 'Active');
							$business_pass = $this->dynamic_model->getdatafromtable('business_passes', $where);
							$business_id = (!empty($business_pass[0]['business_id'])) ? $business_pass[0]['business_id'] : 0;
							if ($business_pass[0]['service_type'] == 1) {
								$class_id = $business_pass[0]['service_id'];
							} else {
								$workshop_id = $business_pass[0]['service_id'];
							}
							$pass_amount = (!empty($business_pass[0]['amount'])) ? $business_pass[0]['amount'] : 0;
							/* if($pass_amount!== $amount){
								        	$arg['status']     = 0;
								            $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
											$arg['error_line']= __line__;
											$arg['data']      =  json_decode('{}');
											$arg['message']    = $this->lang->line('amount_incorrect');
											echo json_encode($arg);exit;
							*/
						} elseif ($service_type == 2) {
							$business_id = 0;
						} elseif ($service_type == 3) {
							$where = array('id' => $service_id, 'status' => 'Active');
							$product_data = $this->dynamic_model->getdatafromtable('business_product', $where);
							$business_id = (!empty($product_data[0]['business_id'])) ? $product_data[0]['business_id'] : 0;
							$product_amount = (!empty($product_data[0]['price'])) ? $product_data[0]['price'] : 0;
							//check product stock limit
							$product_quantity = get_product_quantity($business_id, $service_id);

							if ($product_quantity < $quantity) {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['data'] = json_decode('{}');
								$arg['message'] = $this->lang->line('product_quantity_limit');
								echo json_encode($arg);exit;
							}
							//check amount
							if ($product_amount !== $amount) {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['data'] = json_decode('{}');
								$arg['message'] = $this->lang->line('amount_incorrect');
								echo json_encode($arg);exit;
							}
						}
						//condition check when cart already added then only update quantity & amount

						$service_tax = get_service_tax($business_id, $service_id, $service_type);
						$condition = array('service_id' => $service_id, 'service_type' => $service_type, 'user_id' => $usid, "status" => "Pending");
						$cart_data = $this->dynamic_model->getdatafromtable('user_booking', $condition);
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
						if (!empty($cart_data)) {
							$get_quantity = $cart_data[0]['quantity'];
							$total_quantity = $get_quantity + $quantity;
							$updateData = array(
								'amount' => $amount,
								'quantity' => $total_quantity,
								'sub_total' => $total_quantity * $amount,
								'tax_amount' => $service_tax,
								'tax1_rate' => $tax1_rate,
								'tax2_rate' => $tax2_rate,
								'update_dt' => $time,
							);

							$booking_id = $this->dynamic_model->updateRowWhere('user_booking', $condition, $updateData);
						} else {
							$insertData = array(
								'business_id' => $business_id,
								'user_id' => $usid,
								'amount' => $amount,
								'service_type' => $service_type,
								'service_id' => $service_id,
								'class_id' => (!empty($class_id)) ? $class_id : '',
								'workshop_id' => (!empty($workshop_id)) ? $workshop_id : '',
								'quantity' => $quantity,
								'tax_amount' => $service_tax,
								'tax1_rate' => $tax1_rate,
								'tax2_rate' => $tax2_rate,
								'sub_total' => $amount * $quantity,
								'status' => "Pending",
								'create_dt' => $time,
								'update_dt' => $time,
							);
							$booking_id = $this->dynamic_model->insertdata('user_booking', $insertData);
						}
						if ($booking_id) {
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('cart_add_succ');
							$arg['data'] = json_decode('{}');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['data'] = json_decode('{}');
							$arg['message'] = $this->lang->line('server_problem');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}
	/****************Function Cart list list**********************************
		     * @type            : Function
		     * @Author          : Arpit
		     * @function name   : cart_list
		     * @description     : Cart List
		     * @param           : null
		     * @return          : null
	*/
	public function get_cart_list_info($usid = '', $limit = '', $offset = '') {
		$response = array();
		$discount = 0;
		$total_discount = 0;
		$total_tax1 = 0;
		$total_tax2 = 0;
		$item_name = $service_id = $class_name = $booking_pass_id = $pass_type = $purchase_date = $pass_end_date = $purchase_date_utc = $pass_end_date_utc = $desc = $product_image = $favourite = '';
		$business_data = $this->api_model->get_cart_business($usid, $limit, $offset);
		//print_r($business_data);die;
		if (!empty($business_data)) {
			foreach ($business_data as $value) {

				//$cartData['cart_id']  = $value['id'];
				$business_id = $value['business_id'];
				$businessData['business_id'] = $business_id;
				$where1 = array("id" => $business_id, "status" => "Active");
				$busidata = $this->dynamic_model->getdatafromtable('business', $where1);
				$businessData['business_name'] = $busidata[0]['business_name'];
				$img = site_url() . 'uploads/business/' . $busidata[0]['logo'];
				$businessData['logo'] = $img;

				$where = array("user_id" => $usid, "business_id" => $business_id, "status" => "Pending", "service_type !=" => 2);
				$cart_data = $this->dynamic_model->getdatafromtable('user_booking', $where);
				$i = 0;
				if (!empty($cart_data)) {
					foreach ($cart_data as $value1) {
						//1 passes 2 services 3 productdata
						if ($value1['service_type'] == 1) {
							$where2 = array('business_id' => $value1['business_id'], 'id' => $value1['service_id'], 'status' => 'Active');
							$business_pass = $this->dynamic_model->getdatafromtable('business_passes', $where2);

							$item_name = (!empty($business_pass[0]['pass_name'])) ? $business_pass[0]['pass_name'] : '';
							$service_id = (!empty($business_pass[0]['service_id'])) ? $business_pass[0]['service_id'] : '';
							//echo $business_pass[0]['service_type']; die;
							if ($business_pass[0]['service_type'] == '1') {

								$classes_data = $this->dynamic_model->getdatafromtable('business_class', array("id" => $service_id));

								$class_name = (!empty($classes_data)) ? ucwords($classes_data[0]['class_name']) : "";

							} else {
								$workshop_data = $this->dynamic_model->getdatafromtable('business_workshop', array("id" => $service_id));
								$class_name = (!empty($workshop_data)) ? ucwords($workshop_data[0]['workshop_name']) : "";

							}
							$booking_pass_id = (!empty($business_pass)) ? ucwords($business_pass[0]['pass_id']) : "";
							$passType = (!empty($business_pass[0]['pass_type'])) ? $business_pass[0]['pass_type'] : '';

							$pass_type_subcat = (!empty($business_pass[0]['pass_type_subcat'])) ? $business_pass[0]['pass_type_subcat'] : '';
							$pass_type = get_passes_type_name($passType, $pass_type_subcat);

							$pass_validity = (!empty($business_pass)) ? $business_pass[0]['pass_validity'] . " Days" : "";
							$purchase_date = (!empty($business_pass)) ? date("d M Y ", $business_pass[0]['purchase_date']) : "";
							$pass_end_date = (!empty($business_pass)) ? date("d M Y ", $business_pass[0]['pass_end_date']) : "";
							$purchase_date_utc = (!empty($business_pass)) ? $business_pass[0]['purchase_date'] : "";
							$pass_end_date_utc = (!empty($business_pass)) ? $business_pass[0]['pass_end_date'] : "";

							//Check my favourite status
							$wh = array("user_id" => $usid, "service_id" => $value1['service_id'], "service_type" => 2);
							$user_favourite = $this->dynamic_model->getdatafromtable("user_business_favourite", $wh);
							$favourite = (!empty($user_favourite)) ? '1' : '0';
							//if passes data then blank other data
							$desc = $product_image = '';

						} elseif ($value1['service_type'] == 2) {
							$item_name = '';
							$service_id = 0;
						} elseif ($value1['service_type'] == 3) {
							$where2 = array('id' => $value1['service_id'], 'status' => 'Active');
							$product_data = $this->dynamic_model->getdatafromtable('business_product', $where2);
							$service_id = (!empty($product_data[0]['id'])) ? $product_data[0]['id'] : '';
							$item_name = (!empty($product_data[0]['product_name'])) ? $product_data[0]['product_name'] : 0;
							$desc = (!empty($product_data[0]['details'])) ? $product_data[0]['details'] : '';
							$product_img = get_product_images($service_id);
							$product_image = (!empty($product_img[0]['image_name'])) ? $product_img[0]['image_name'] : '';
							//if product data then blank other data
							$class_name = $booking_pass_id = $pass_type = $purchase_date = $pass_end_date = $pass_validity = $favourite = '';
						}
						$cartData['cart_id'] = $value1['id'];
						$cartData['item_name'] = $item_name;
						$cartData['item_decription'] = $desc;
						$cartData['service_type'] = $value1['service_type'];
						$cartData['service_id'] = $value1['service_id'];
						$cartData['class_name'] = $class_name;
						$cartData['booking_pass_id'] = $booking_pass_id;
						$cartData['pass_type'] = $pass_type;
						$cartData['pass_validity'] = $pass_validity;
						$cartData['favourite'] = $favourite;
						$cartData['start_date'] = $purchase_date;
						$cartData['end_date'] = $pass_end_date;
						$cartData['start_date_utc'] = $purchase_date_utc;
						$cartData['end_date_utc'] = $pass_end_date_utc;
						$cartData['item_image'] = $product_image;
						$cartData['amount'] = number_format($value1['amount'], 2);
						$cartData['sub_total'] = number_format($value1['sub_total'], 2);
						$cartData['quantity'] = floatVal($value1['quantity']);
						$cartData['tax'] = number_format((float) $value1['tax_amount'], 2, '.', ''); // floatVal($value1['tax_amount']);

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
						$cartData['tax'] = number_format($tax_cal, 2);

						$cartData['tax1_rate'] = number_format($temp_tax1, 2);
						$cartData['tax2_rate'] = number_format($temp_tax2, 2);
						$cartData['discount'] = $value1['discount'];
						$total_tax1 += floatVal($cartData['tax1_rate']);
						$total_tax2 += floatVal($cartData['tax2_rate']);
						$cart_response[$i++] = $cartData;
						if ($value1['discount'] > 0) {
							$total_discount += $value1['discount'];
						}
					}
				}

				$businessData['cart_details'] = $cart_response;
				$response[] = $businessData;
			}
			$whe = array("user_id" => $usid, "status" => "Pending");
			$total_item = getdatacount('user_booking', $whe);
			$tax = gettotalTax($usid);
			$total_amt = check_cart_value($usid);

			/* $tax = ($total_amt - $total_discount ) / $tax;
			$grand_total = ($total_amt - $total_discount ) + $tax; */
			//$grand_total=$total_amt+$tax;

			$workshop_price = ($total_amt - $total_discount);
			//$tax = (($workshop_price * $tax) / 100);
			$grand_total = ($workshop_price + $tax);

			return $result = array(
				"business_details" => $response,
				"total_item" => $total_item,
				"total_amount" => number_format($total_amt, 2),
				"tax" => number_format($tax, 2), //floatVal(round($tax,2)),
				"tax1_rate" => number_format($total_tax1, 2),
				"tax2_rate" => number_format($total_tax2, 2),
				"grand_total" => number_format($grand_total, 2),
			);

		} else {
			return false;
		}

	}
	public function cart_list_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$response = array();
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;
						$usid = $userdata['data']['id'];
						$business_data = $this->get_cart_list_info($usid, $limit, $offset);

						if (!empty($business_data)) {
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $business_data;
							$arg['message'] = $this->lang->line('record_found');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = json_decode('{}');
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}
	/****************Function update cart **********************************
		     * @type            : Function
		     * @Author          : Arpit
		     * @function name   : update_cart
		     * @description     : Update cart
		     * @param           : null
		     * @return          : null
	*/
	public function update_cart_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$product_id = $this->input->post('product_id');
					if (empty($product_id)) {
						$this->form_validation->set_rules('cart_id', 'Cart Id', 'required|trim', array('required' => $this->lang->line('cart_id_required')));
					}
					$this->form_validation->set_rules('quantity', 'Quantity', 'required', array('required' => $this->lang->line('quantity_required')));

					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
						echo json_encode($arg);
						die;
					} else {

						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;
						$usid = $userdata['data']['id'];
						$time = time();
						$cart_id = $this->input->post('cart_id');
						$quantity = $this->input->post('quantity');
						$product_id = $this->input->post('product_id');
						if (!empty($product_id)) {
							$where2 = array('id' => $product_id);
							$product_data = $this->dynamic_model->getdatafromtable('business_product', $where2);
							$amount = (!empty($product_data[0]['price'])) ? $product_data[0]['price'] : '';

							$total_amt = $quantity * $amount;
							$workshop_price = $total_amt;
							$workshop_tax_price = 0;
							$tax1_rate_val = 0;
							$tax2_rate_val = 0;
							$workshop_total_price = $workshop_price;
							if (strtolower($product_data[0]['tax1']) == 'yes') {
								$tax1_rate = floatVal($product_data[0]['tax1_rate']);
								$tax1_rate_val = (($workshop_price * $tax1_rate) / 100);
								$workshop_tax_price = $tax1_rate_val;
								$workshop_total_price = $workshop_price + $tax1_rate_val;

							}
							if (strtolower($product_data[0]['tax2']) == 'yes') {
								$tax2_rate = floatVal($product_data[0]['tax2_rate']);
								$tax2_rate_val = (($workshop_price * $tax2_rate) / 100);
								$workshop_tax_price = $tax1_rate_val + $tax2_rate_val;
								$workshop_total_price = $workshop_total_price + $tax2_rate_val;
							}

							$tax1_rate = number_format($tax1_rate_val, 2);
							$tax2_rate = number_format($tax2_rate_val, 2);
							$product_tax_price = number_format($workshop_tax_price, 2);
							$product_total_price = number_format($workshop_total_price, 2);

							//$tax = '0';
							//$grand_total=$total_amt+$tax;

							$grand_total = $product_total_price;
							$result = array(
								"business_details" => [],
								"total_item" => "$quantity",
								"total_amount" => "$total_amt",
								"tax" => "$product_tax_price",
								"tax1_rate" =>  number_format($tax1_rate_val, 2),
								"tax2_rate" =>  number_format($tax2_rate_val, 2),
								"grand_total" => "$grand_total");
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $result;
							$arg['message'] = $this->lang->line('cart_update_succ');
							echo json_encode($arg);
							die;
						} else {
							$where = array('id' => $cart_id, 'user_id' => $usid, 'status' => 'Pending');
							$check_cart = $this->dynamic_model->getdatafromtable('user_booking', $where);
							if (!empty($check_cart)) {
								$product_id = $check_cart[0]['service_id'];
								//echo $product_id; die;

								$where2 = array('id' => $product_id);
								$product_data = $this->dynamic_model->getdatafromtable('business_product', $where2);
								$Quantity = !empty($check_cart[0]['quantity']) ? $check_cart[0]['quantity'] : '0';
								$total_quantity = $quantity;

								// echo $total_quantity.'==='. $product_data[0]['quantity']; die;

								if ($total_quantity >= $product_data[0]['quantity']) {
									$arg['status'] = 0;
									$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line'] = __line__;
									$arg['data'] = json_decode('{}');
									$arg['message'] = 'Product Quantity exceeds.';
									echo json_encode($arg);
									die;
								}

								$sub_total = $check_cart[0]['amount'] * $total_quantity;
								$updateData = array(
									'quantity' => $total_quantity,
									'sub_total' => $sub_total,
									'update_dt' => $time,
								);
								$updateCart = $this->dynamic_model->updateRowWhere('user_booking', $where, $updateData);

								if ($updateCart) {

									$result = $this->get_cart_list_info($usid, $limit, $offset);

									$arg['status'] = 1;
									$arg['error_code'] = REST_Controller::HTTP_OK;
									$arg['error_line'] = __line__;
									$arg['data'] = $result;
									$arg['message'] = $this->lang->line('cart_update_succ');
								} else {
									$arg['status'] = 0;
									$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
									$arg['error_line'] = __line__;
									$arg['data'] = json_decode('{}');
									$arg['message'] = $this->lang->line('server_problem');
								}
							} else {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['data'] = json_decode('{}');
								$arg['message'] = $this->lang->line('something_wrong');
							}
						}

					}
				}
			}
		}
		echo json_encode($arg);
	}
	/****************Function Remove Cart **********************************
		     * @type            : Function
		     * @Author          : Arpit
		     * @function name   : remove_cart
		     * @description     : Remove cart
		     * @param           : null
		     * @return          : null
	*/
	public function remove_cart_post() {
		$arg = array();
		$arrayName = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$remove_cart_type = $this->input->post('remove_cart_type');
					$this->form_validation->set_rules('remove_cart_type', 'Remove Cart Type', 'required|trim', array('required' => $this->lang->line('remove_cart_type_required')));
					if ($remove_cart_type != 1) {
						$this->form_validation->set_rules('cart_id', 'Cart Id', 'required|trim', array('required' => $this->lang->line('cart_id_required')));
					}
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;
						$usid = $userdata['data']['id'];
						$time = time();
						$cart_id = $this->input->post('cart_id');
						//remove_cart_type 0 single 1 all
						if ($remove_cart_type != 1) {
							$where = array('id' => $cart_id, 'user_id' => $usid, 'status' => 'Pending');
						} else {
							$where = array('user_id' => $usid, 'status' => 'Pending');
						}
						$check_cart = $this->dynamic_model->getdatafromtable('user_booking', $where);
						if (!empty($check_cart)) {
							if ($remove_cart_type == 1) {
								$where1 = array('user_id' => $usid, 'status' => 'Pending');
								$deleteCart = $this->dynamic_model->deletedata('user_booking', $where1);
							} else {
								$where1 = array('id' => $cart_id, 'user_id' => $usid, 'status' => 'Pending');
								$deleteCart = $this->dynamic_model->deletedata('user_booking', $where1);
							}
							$business_data = $this->get_cart_list_info($usid, $limit, $offset);
							$business_data = $business_data ? $business_data : json_decode('{}');
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $business_data;
							$arg['message'] = $this->lang->line('remove_cart');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['data'] = json_decode('{}');
							$arg['message'] = $this->lang->line('something_wrong');
						}

					}
				}
			}
		}
		echo json_encode($arg);
	}
	/****************Function Get products list**********************************
		     * @type            : Function
		     * @Author          : Arpit
		     * @function name   : product_list
		     * @description     : product list
		     * @param           : null
		     * @return          : null
	*/
	public function all_products_list_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$usid = $userdata['data']['id'];
						$response = $imgarr = array();
						$business_id = $this->input->post('business_id');
						$search_text = $this->input->post('search_text');
						$sort_price = $this->input->post('sort_price');
						$search_category = $this->input->post('search_category');
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;
						//$product_data = get_product_list($business_id,$limit,$offset,$search_text,$sort_price);
						$search_skills = [];
						if (!empty($search_category)) {
							$search_skills = explode(',', $search_category);
						}
						$where = array("business_id" => $business_id);
						$search_val = trim($search_text);
						if (!empty($search_val)) {
							$where = 'business_id= "' . $business_id . '" AND (product_name LIKE "%' . $search_val . '%")';
						} else {
							$where = 'business_id= "' . $business_id . '"';
						}
						if (!empty($sort_price)) {
							$order_by = ($sort_price == 'low') ? 'Asc' : 'DESC';
							$order_name = 'price';
						} else {
							$order_by = 'DESC';
							$order_name = 'create_dt';
						}
						$product_data = $this->dynamic_model->getWhereInData("business_product", "category_id", $search_skills, $where, "*", $limit, $offset, $order_name, $order_by);

						if (!empty($product_data)) {
							foreach ($product_data as $value) {
								$productdata['product_id'] = $value['id'];
								$productdata['product_name'] = $value['product_name'];
								$productdata['product_price'] = $value['price'];
								$productdata['quantity'] = $value['quantity'];
								$productdata['product_status'] = $value['status'];
								$productdata['product_description'] = $value['description'];
								$image_datas = get_product_images($value['id']);
								$productdata['product_images'] = $image_datas;
								//$productdata['product_categories']=get_categories_data('',3);
								//Check cart added or not in your cart bucket
								$condition1 = array("user_id" => $usid, "service_id" => $value['id'], "service_type" => 3, "status" => "Pending");
								$cart_data = $this->dynamic_model->getdatafromtable("user_booking", $condition1);
								$cartdata = (!empty($cart_data)) ? '1' : '0';
								$productdata['is_cart'] = $cartdata;
								$productdatas[] = $productdata;
							}
							$response = $productdatas;
							$total_count = count($response);
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $response;
							$arg['total_count'] = "$total_count";
							$arg['message'] = $this->lang->line('record_found');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = array();
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}
	public function products_details_postbk() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('product_id', 'Product Id', 'required|trim', array('required' => $this->lang->line('product_id_required')));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$usid = $userdata['data']['id'];
						$response = $imgarr = $similarproducts = array();
						$product_id = $this->input->post('product_id');
						$page_no = (!empty($this->input->post('page_no'))) ? $this->input->post('page_no') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;
						$where = array("id" => $product_id);
						$product_data = $this->dynamic_model->getdatafromtable("business_product", $where);
						if (!empty($product_data)) {
							$productdata['product_id'] = $product_data[0]['id'];
							$productdata['product_name'] = $product_data[0]['product_name'];
							$productdata['product_price'] = $product_data[0]['price'];
							$productdata['product_quantity'] = (!empty($product_data[0]['quantity'])) ? $product_data[0]['quantity'] : '';
							$productdata['product_status'] = $product_data[0]['status'];
							$productdata['product_description'] = $product_data[0]['description'];
							//get tax
							$where1 = array("business_id" => $product_data[0]['business_id']);
							$business_tax = $this->dynamic_model->getdatafromtable("business_tax", $where1);
							if ($product_data[0]['tax1'] == 'Yes') {
								$productdata['tax_name'] = (!empty($business_tax[0]['tax1_name'])) ? $business_tax[0]['tax1_name'] : '';
								//$productdata['tax'] = (!empty($business_tax[0]['tax1_rate'])) ? $business_tax[0]['tax1_rate'] : '';
								$productdata['tax'] = '0';

							} else {
								$productdata['tax_name'] = '';
								$productdata['tax'] = '';
							}
							if ($product_data[0]['tax2'] == 'Yes') {
								$productdata['tax2_name'] = (!empty($business_tax[0]['tax2_name'])) ? $business_tax[0]['tax2_name'] : '';
								// $productdata['tax2'] = (!empty($business_tax[0]['tax2_rate'])) ? $business_tax[0]['tax2_rate'] : '';
								$productdata['tax2'] = '0';
							} else {
								$productdata['tax2_name'] = '';
								$productdata['tax2'] = '';
							}
							$image_datas = get_product_images($product_data[0]['id']);
							$productdata['product_images'] = $image_datas;
							//Check cart added or not in your cart bucket
							$condition1 = array("user_id" => $usid, "service_id" => $product_data[0]['id'], "service_type" => 3, "status" => "Pending");
							$cart_data = $this->dynamic_model->getdatafromtable("user_booking", $condition1);
							$cartdata = (!empty($cart_data)) ? '1' : '0';
							$productdata['is_cart'] = $cartdata;
							//get data for similars products
							$business_id = $product_data[0]['business_id'];
							$product_id = $product_data[0]['id'];
							$category_id = (!empty($product_data[0]['category_id'])) ? $product_data[0]['category_id'] : '';
							$search_val = trim($product_data[0]['product_name']);
							// $where= 'business_id= "'.$business_id.'" AND id!= "'.$product_id.'" AND (product_name LIKE "%'.$search_val.'%")';
							$where = 'business_id= "' . $business_id . '" AND id!= "' . $product_id . '" AND category_id= "' . $category_id . '"';

							$similar_products = $this->dynamic_model->getdatafromtable("business_product", $where, "*", $limit, $offset, "create_dt", "DESC");
							if (!empty($similar_products)) {
								foreach ($similar_products as $value) {
									$similardata['product_id'] = $value['id'];
									$similardata['product_name'] = $value['product_name'];
									$similardata['product_price'] = $value['price'];
									$similardata['product_status'] = $value['status'];
									$similardata['product_description'] = $value['details'];
									$image_datas = get_product_images($value['id']);
									$similardata['product_images'] = $image_datas;
									//Check cart added or not in your cart bucket
									$condition2 = array("user_id" => $usid, "service_id" => $value['id'], "service_type" => 3, "status" => "Pending");
									$cart_data = $this->dynamic_model->getdatafromtable("user_booking", $condition2);
									$cartdata = (!empty($cart_data)) ? '1' : '0';
									$similardata['is_cart'] = $cartdata;
									//get tax
									$whe1 = array("business_id" => $value['business_id']);
									$business_tax_similar = $this->dynamic_model->getdatafromtable("business_tax", $whe1);
									if ($value['tax1'] == 'Yes') {
										$similardata['tax_name'] = (!empty($business_tax_similar[0]['tax1_name'])) ? $business_tax_similar[0]['tax1_name'] : '';
										//$productdata['tax'] = (!empty($business_tax[0]['tax1_rate'])) ? $business_tax[0]['tax1_rate'] : '';
										$similardata['tax'] = '0';

									} else {
										$similardata['tax_name'] = '';
										$similardata['tax'] = '';
									}
									if ($value['tax2'] == 'Yes') {
										$similardata['tax2_name'] = (!empty($business_tax_similar[0]['tax2_name'])) ? $business_tax_similar[0]['tax2_name'] : '';
										// $productdata['tax2'] = (!empty($business_tax[0]['tax2_rate'])) ? $business_tax[0]['tax2_rate'] : '';
										$similardata['tax2'] = '0';
									} else {
										$similardata['tax2_name'] = '';
										$similardata['tax2'] = '';
									}
									$similarproducts[] = $similardata;
								}
							}

							$productdata['similar_products'] = $similarproducts;
							$response = $productdata;
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $response;
							$arg['message'] = $this->lang->line('record_found');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = json_decode('{}');
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}
	public function products_details_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('product_id', 'Product Id', 'required|trim', array('required' => $this->lang->line('product_id_required')));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$usid = $userdata['data']['id'];
						$response = $imgarr = $similarproducts = array();
						$product_id = $this->input->post('product_id');
						$page_no = (!empty($this->input->post('page_no'))) ? $this->input->post('page_no') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;
						$where = array("id" => $product_id);
						$product_data = $this->dynamic_model->getdatafromtable("business_product", $where);
						if (!empty($product_data)) {
							$productdata['product_id'] = $product_data[0]['id'];
							$productdata['product_name'] = $product_data[0]['product_name'];
							$productdata['product_price'] = $product_data[0]['price'];
							$productdata['product_quantity'] = (!empty($product_data[0]['quantity'])) ? $product_data[0]['quantity'] : 0;
							$productdata['product_status'] = $product_data[0]['status'];
							$productdata['product_description'] = $product_data[0]['description'];
							//get tax
							$where1 = array("business_id" => $product_data[0]['business_id']);
							$business_tax = $this->dynamic_model->getdatafromtable("business_tax", $where1);
							if ($product_data[0]['tax1'] == 'Yes') {
								//$productdata['tax_name'] = (!empty($business_tax[0]['tax1_name'])) ? $business_tax[0]['tax1_name'] : '';
								//$productdata['tax'] = (!empty($business_tax[0]['tax1_rate'])) ? $business_tax[0]['tax1_rate'] : '';
								$productdata['tax1_rate'] = (!empty($business_tax[0]['tax1_rate'])) ? $business_tax[0]['tax1_name'] : 0;
								$productdata['tax1'] = 'Yes';

							} else {
								$productdata['tax1_rate'] = 0;
								$productdata['tax1'] = 'No';
							}
							if ($product_data[0]['tax2'] == 'Yes') {
								//$productdata['tax2_name'] = (!empty($business_tax[0]['tax2_name'])) ? $business_tax[0]['tax2_name'] : '';
								$productdata['tax2_rate'] = (!empty($business_tax[0]['tax2_rate'])) ? $business_tax[0]['tax2_rate'] : 0;
								// $productdata['tax2'] = (!empty($business_tax[0]['tax2_rate'])) ? $business_tax[0]['tax2_rate'] : '';
								$productdata['tax2'] = 'Yes';
							} else {
								$productdata['tax2_rate'] = 0;
								$productdata['tax2'] = 'No';
							}

							$workshop_price = $product_data[0]['price'];
							$workshop_tax_price = 0;
							$tax1_rate_val = 0;
							$tax2_rate_val = 0;
							$workshop_total_price = $workshop_price;
							if (strtolower($product_data[0]['tax1']) == 'yes') {
								$tax1_rate = floatVal($product_data[0]['tax1_rate']);
								$tax1_rate_val = (($workshop_price * $tax1_rate) / 100);
								$workshop_tax_price = $tax1_rate_val;
								$workshop_total_price = $workshop_price + $tax1_rate_val;

							}
							if (strtolower($product_data[0]['tax2']) == 'yes') {
								$tax2_rate = floatVal($product_data[0]['tax2_rate']);
								$tax2_rate_val = (($workshop_price * $tax2_rate) / 100);
								$workshop_tax_price = $tax1_rate_val + $tax2_rate_val;
								$workshop_total_price = $workshop_total_price + $tax2_rate_val;
							}

							$productdata['tax1_rate'] = number_format($tax1_rate_val, 2);
							$productdata['tax2_rate'] = number_format($tax2_rate_val, 2);
							$productdata['product_tax_price'] = number_format($workshop_tax_price, 2);
							$productdata['product_total_price'] = number_format($workshop_total_price, 2);

							$image_datas = get_product_images($product_data[0]['id']);
							$productdata['product_images'] = $image_datas;
							//Check cart added or not in your cart bucket
							$condition1 = array("user_id" => $usid, "service_id" => $product_data[0]['id'], "service_type" => 3, "status" => "Pending");
							$cart_data = $this->dynamic_model->getdatafromtable("user_booking", $condition1);
							$cartdata = (!empty($cart_data)) ? '1' : '0';
							$productdata['is_cart'] = $cartdata;
							//get data for similars products
							$business_id = $product_data[0]['business_id'];
							$product_id = $product_data[0]['id'];
							$category_id = (!empty($product_data[0]['category_id'])) ? $product_data[0]['category_id'] : '';
							$search_val = trim($product_data[0]['product_name']);
							// $where= 'business_id= "'.$business_id.'" AND id!= "'.$product_id.'" AND (product_name LIKE "%'.$search_val.'%")';
							$where = 'business_id= "' . $business_id . '" AND id!= "' . $product_id . '" AND category_id= "' . $category_id . '"';

							$similar_products = $this->dynamic_model->getdatafromtable("business_product", $where, "*", $limit, $offset, "create_dt", "DESC");
							if (!empty($similar_products)) {
								foreach ($similar_products as $value) {
									$similardata['product_id'] = $value['id'];
									$similardata['product_name'] = $value['product_name'];
									$similardata['product_price'] = $value['price'];
									$similardata['quantity'] = $value['quantity'];
									$similardata['product_status'] = $value['status'];
									$similardata['product_description'] = $value['description'];
									$image_datas = get_product_images($value['id']);
									$similardata['product_images'] = $image_datas;
									//Check cart added or not in your cart bucket
									$condition2 = array("user_id" => $usid, "service_id" => $value['id'], "service_type" => 3, "status" => "Pending");
									$cart_data = $this->dynamic_model->getdatafromtable("user_booking", $condition2);
									$cartdata = (!empty($cart_data)) ? '1' : '0';
									$similardata['is_cart'] = $cartdata;
									//get tax
									$whe1 = array("business_id" => $value['business_id']);
									$business_tax_similar = $this->dynamic_model->getdatafromtable("business_tax", $whe1);
									if ($value['tax1'] == 'Yes') {
										// $similardata['tax_name'] = (!empty($business_tax_similar[0]['tax1_name'])) ? $business_tax_similar[0]['tax1_name'] : '';
										$similardata['tax1_rate'] = (!empty($business_tax_similar[0]['tax1_rate'])) ? $business_tax_similar[0]['tax1_rate'] : 0;
										//$productdata['tax'] = (!empty($business_tax[0]['tax1_rate'])) ? $business_tax[0]['tax1_rate'] : '';
										$similardata['tax1'] = 'Yes';

									} else {
										$similardata['tax1_rate'] = 0;
										$similardata['tax1'] = 'No';
									}
									if ($value['tax2'] == 'Yes') {
										$similardata['tax2_rate'] = (!empty($business_tax_similar[0]['tax2_rate'])) ? $business_tax_similar[0]['tax2_rate'] : 0;
										// $productdata['tax2'] = (!empty($business_tax[0]['tax2_rate'])) ? $business_tax[0]['tax2_rate'] : '';
										$similardata['tax2'] = 'Yes';
									} else {
										$similardata['tax2_rate'] = 0;
										$similardata['tax2'] = 'No';
									}
									$similarproducts[] = $similardata;
								}
							}

							$productdata['similar_products'] = $similarproducts;
							$response = $productdata;
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $response;
							$arg['message'] = $this->lang->line('record_found');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = json_decode('{}');
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}
	//Function used for Get Card Details List
	public function getCardDetails_get() {
		$arg = array();
		//check version is updated or not
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$usid = $userdata['data']['id'];
				$card_detail = $this->dynamic_model->getdatafromtable('saved_card_details', array('user_id' => $usid, 'is_deleted' => 0));
				//print_r($card_detail);die;
				if ($card_detail) {
					$imagePath = site_url() . "assets/images/card.jpeg";
					$user_data = array();
					$card_array = array();
					foreach ($card_detail as $card) {

						$card_arr = json_decode(decode($card['card_details']));
						$card_bank_no = $card_arr->card_bank_no;
						$expiry_month = $card_arr->expiry_month;
						$expiry_year = $card_arr->expiry_year;
						// check year is valid
						if (check_expiry_month_year($expiry_month, $expiry_year) == true) {
							$user_data["card_id"] = $card['id'];
							$user_data["userid"] = $card['user_id'];
							$last_digit = substr($card_bank_no, -4);
							$user_data["card_number"] = 'XXXX-XXXX-XXXX-' . $last_digit;
							$user_data["full_card_number"] = $card_bank_no;
							$user_data["expiry_month"] = $expiry_month;
							$user_data["expiry_year"] = $expiry_year;
							if ($card['is_debit_card'] == 1) {
								$user_data["card_type"] = "Debit Card";
							}

							if ($card['is_credit_card'] == 1) {
								$user_data["card_type"] = "Credit Card";
							}

							$card_array[] = $user_data;
						}
					}
					if (!empty($card_array)) {
						$arg['status'] = 1;
						$arg['error_code'] = REST_Controller::HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['data'] = $card_array;
						$arg['message'] = $this->lang->line('saved_card_details');
					} else {
						$arg['status'] = 0;
						$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
						$arg['error_line'] = __line__;
						$arg['data'] = array();
						$arg['message'] = $this->lang->line('saved_card_not_found');
					}
				} else {
					$arg['status'] = 0;
					$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
					$arg['error_line'] = __line__;
					$arg['data'] = array();
					$arg['message'] = $this->lang->line('saved_card_not_found');
				}
			}
		}
		echo json_encode($arg);
	}
	//Function used for Delete Card Detail
	public function deleteBankCardDetail_post() {
		$arg = array();
		//check version is updated or not
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$arg = array();
					$this->form_validation->set_rules('card_id', 'card Id', 'required|numeric', array('required' => $this->lang->line('card_id_required'), 'numeric' => $this->lang->line('card_id_numeric')));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = ERROR_FAILED_CODE;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$usid = $userdata['data']['id'];
						$cardId = $this->input->post('card_id');
						$card_Exist = $this->dynamic_model->getdatafromtable('saved_card_details', array('user_id' => $usid, 'id' => $cardId, 'is_deleted' => 0));
						if ($card_Exist) {
							$data1 = array(
								'is_deleted' => 1,
								'updated_by' => $usid,
								'update_dt' => time(),
							);
							$where = array("id" => $cardId, "user_id" => $usid);
							$cardUpdate = $this->dynamic_model->updateRowWhere('saved_card_details', $where, $data1);

							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('card_delete_success');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = ERROR_FAILED_CODE;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}
	//bambora payment token genrate
	public function getbamboraPaymentToken_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {

			$_POST = json_decode(file_get_contents("php://input"), true);

			if ($_POST) {

				$this->form_validation->set_rules('number', 'Card Number', 'required', array('required' => $this->lang->line('number_required')));
				$this->form_validation->set_rules('expiry_month', 'Expiry Month', 'required', array('required' => $this->lang->line('expiry_month_required')));
				$this->form_validation->set_rules('expiry_year', 'Expiry year', 'required', array('required' => $this->lang->line('expiry_year_required')));
				$this->form_validation->set_rules('cvd', 'Cvd', 'required', array('required' => $this->lang->line('cvd_required')));
				if ($this->form_validation->run() == FALSE) {
					$arg['status'] = 0;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {

					$number = $this->input->post('number');
					$expiry_month = $this->input->post('expiry_month');
					$expiry_year = $this->input->post('expiry_year');
					$cvd = $this->input->post('cvd');

					$token = getbamboraToken($number, $expiry_month, $expiry_year, $cvd);
					if ($token) {
						$res = array('token' => $token);
						$arg['status'] = 1;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['message'] = 'Payment token';
						$arg['data'] = $res;
					} else {
						$arg['status'] = 0;
						$arg['error_code'] = ERROR_FAILED_CODE;
						$arg['error_line'] = __line__;
						$arg['message'] = 'Invalid Details';
						$arg['data'] = json_decode('{}');
						echo json_encode($arg);
					}

				}
			}

		}
		echo json_encode($arg);
	}
	//Used function for payment checkout
	public function payment_checkout_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('amount', 'Amount', 'required|greater_than[0]', array(
						'required' => $this->lang->line('amount_required'),
						'numeric' => $this->lang->line('amount_valid'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$usid = $userdata['data']['id'];
						$name = $userdata['data']['name'];
						$lastname = $userdata['data']['lastname'];
						$time = time();
						$amount = $this->input->post('amount');
						$savecard = $this->input->post('savecard');
						$card_id = $this->input->post('card_id');
						$amount = number_format((float) $amount, 2, '.', '');
						$token = $this->input->post('token');
						$card_res = $card_data = $card_Exist = array();
						$cart_check = check_cart_with_tax($usid);
						if ($cart_check == false) {
							$arg['status'] = 0;
							$arg['error_code'] = ERROR_FAILED_CODE;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('check_cart_msg');
							$arg['data'] = json_decode('{}');
							echo json_encode($arg);exit;
						}
						if ($cart_check !== $amount) {
							$arg['status'] = 0;
							$arg['error_code'] = ERROR_FAILED_CODE;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('amount_incorrect');
							$arg['data'] = json_decode('{}');
							echo json_encode($arg);exit;
						}
						if (!empty($token)) {
							$payment_data = array(
								'order_number' => $time,
								'amount' => $amount,
								'payment_method' => 'token',
								'token' => array(
									'name' => 'Test Card',
									'code' => $token,
									'complete' => true,
								),
							);
						} else if (!empty($card_id)) {
							$where = array('user_id' => $usid);
							$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
							$customer_code = $result_card[0]['card_id'];

							$payment_data = array(
								'order_number' => $time,
								'amount' => $amount,
								'payment_method' => 'payment_profile',
								'payment_profile' => array(
									'customer_code' => $customer_code,
									'card_id' => $card_id,
									'complete' => true,
								),
							);
						}
						$where = array('user_id' => $usid);
						$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
						if (empty($result_card) && ($savecard == '1')) {
							$legato_token_data = array(
								'language' => 'en',
								'comments' => SITE_NAME,
								'token' => array('name' => 'Test Card',
									'code' => $token),
							);
							$apiurl = 'https://api.na.bambora.com/v1/profiles';
							$responce = $this->bomborapay->profile_create('POST', $apiurl, $legato_token_data);
							if ($responce['code'] == '1') {
								$transaction_data = array('user_id' => $usid,
									'card_id' => $responce['customer_code']);
								$this->dynamic_model->insertdata('user_card_save', $transaction_data);
								$customer_code = $responce['customer_code'];
							}
						} elseif (!empty($result_card) && ($savecard == '1')) {
							$customer_code = $result_card[0]['card_id'];
							$apiurl = "https://api.na.bambora.com/v1/profiles/$customer_code/cards";
							$legato_token_data = array(
								'token' => array('name' => 'Test Card',
									'code' => $token),
							);
							$responce = $this->bomborapay->profile_create('POST', $apiurl, $legato_token_data);
							if ($responce['code'] == '1') {
								$customer_code = $responce['customer_code'];
							}
						}

						if ($savecard == '1') {
							$payment_data = array(
								'order_number' => $time,
								'amount' => $amount,
								'payment_method' => 'payment_profile',
								'payment_profile' => array(
									'customer_code' => $customer_code,
									'card_id' => $card_id,
									'complete' => true,
								),
							);
						}

						// print_r($payment_data);die;
						$business_id = getBusinessId($usid);
						$mid = getUserMarchantId($business_id);
						$marchant_id = $mid['marchant_id'];
						$marchant_id_type = $mid['marchant_id_type'];

						//$mid = '377010002';
						$payUrl = 'https://api.na.bambora.com/v1/payments ';
						$res = $this->bomborapay->payment_checkout('POST', $payUrl, $payment_data, $marchant_id, $marchant_id_type);

						//$res=$this->bomborapay->payment_checkout('POST',$payUrl,$payment_data,$mid);
						// print_r($res); die;

						//echo $res['message'];die;
						if (@$res['approved'] == '1') {
							$ref_num = getuniquenumber();
							$payment_id = !empty(@$res['id']) ? $res['id'] : $ref_num;

							$authorizing_merchant_id = !empty(@$res['authorizing_merchant_id']) ? $res['authorizing_merchant_id'] : '';
							$payment_type = !empty(@$res['type']) ? $res['type'] : '';
							$payment_method = !empty(@$res['payment_method']) ? $res['payment_method'] : '';
							$amount = !empty(@$res['amount']) ? $res['amount'] : '';

							//End of logic implement for purachase plan mothly haif yearly and yearly
							//Insert data in transaction table
							$transaction_data = array(
								'authorizing_merchant_id' => $authorizing_merchant_id,
								'payment_type' => $payment_type,
								'payment_method' => $payment_method,
								'responce_all' => json_encode($res),
								'user_id' => $usid,
								'amount' => $amount,
								'trx_id' => $payment_id,
								'order_number' => $time,
								'transaction_type' => 2,
								'payment_status' => "Success",
								'saved_card_id' => 0,
								'create_dt' => $time,
								'update_dt' => $time,
							);
							$transaction_id = $this->dynamic_model->insertdata('transactions', $transaction_data);
							$where = array("user_id" => $usid, "status" => "Pending");
							$cart_data = $this->dynamic_model->getdatafromtable('user_booking', $where);
							if (!empty($cart_data)) {
								foreach ($cart_data as $value) {
									//service_type 1 then update passes status and expiry
									$recurring = 0;
									if ($value['service_type'] == '1') {
										$where1 = array('id' => $value['service_id'], 'service_type' => '1', 'status' => 'Active');
										$business_pass = $this->dynamic_model->getdatafromtable('business_passes', $where1);
										// $pass_start_date=(!empty($business_pass[0]['purchase_date'])) ? $business_pass[0]['purchase_date'] : 0;
										// $pass_end_date=(!empty($business_pass[0]['pass_end_date'])) ? $business_pass[0]['pass_end_date'] : 0;

										$pass_validity = (!empty($business_pass[0]['pass_validity'])) ? $business_pass[0]['pass_validity'] : 0;
										$pass_start_date = $time;
										$getEndDate = ($pass_validity * 24 * 60 * 60) + $time;
										$pass_end_date = ($pass_validity == 0) ? $pass_start_date : $getEndDate;

										if (!empty($business_pass)) {
											$pass_type_subcat = $business_pass[0]['pass_type_subcat'];
											if (!empty($pass_type_subcat)) {
												$where2 = array('id' => $pass_type_subcat);
												$manage_pass = $this->dynamic_model->getdatafromtable('manage_pass_type', $where2);
												if (!empty($manage_pass)) {
													$validity = $manage_pass[0]['pass_days'];
												}
											}

											if ($pass_type_subcat == '37') {
												$today_dt = date('d');
												$a_date = date("Y-m-d");
												$lastmonth_dt = date("t", strtotime($a_date));
												$diff_dt = $lastmonth_dt - $today_dt;
												$diff_dt = $diff_dt + 1;

												$rt = date("Y-m-t", strtotime($a_date));
												$pass_end_date = strtotime($rt);
												$passes_remaining_count = $diff_dt;

												$per_day_amt = $Amt / $lastmonth_dt;

												$per_day_amt = round($per_day_amt, 2);
												$Amt = $per_day_amt * $diff_dt;

												$grand_total = number_format((float) $Amt, 2, '.', '');
												$Amt = $grand_total;
												$recurring = 1;
											}
										}

										$pass_status = 1;
										$where2 = array("user_id" => $usid, "status" => "Pending", "service_type" => '1');
										$booking_data = array(
											'transaction_id' => $transaction_id,
											'status' => "Success",
											'passes_start_date' => $pass_start_date,
											'passes_end_date' => $pass_end_date,
											'passes_status' => $pass_status,
											'passes_total_count' => $validity,
											'passes_remaining_count' => $validity,
											'update_dt' => $time,
											'recurring' => $recurring,
										);
										$booking_id = $this->dynamic_model->updateRowWhere('user_booking', $where2, $booking_data);
									} else {

										$cart_quantity = $value['quantity'];
										$result_product = $this->dynamic_model->getdatafromtable('business_product', array('id' => $value['service_id']));
										$total_quantity = $result_product[0]['quantity'] - $cart_quantity;

										$product_id = $this->dynamic_model->updateRowWhere('business_product', array('id' => $value['service_id']), array('quantity' => $total_quantity));
										$where2 = array("user_id" => $usid, "status" => "Pending", "service_type!=" => '1');
										$booking_data = array(
											'transaction_id' => $transaction_id,
											'status' => "Success",
											'update_dt' => $time,
										);
										$booking_id = $this->dynamic_model->updateRowWhere('user_booking', $where2, $booking_data);
									}
								}
							}
							$response = array('amount' => number_format((float) $amount, 2, '.', ''), 'transaction_date' => date('d M Y'));
							if ($transaction_id) {
								$arg['status'] = 1;
								$arg['error_code'] = HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_succ');
								$arg['data'] = $response;
							} else {
								$arg['status'] = 0;
								$arg['error_code'] = HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_fail');
								$arg['data'] = json_decode('{}');
							}
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['message'] = @$res['message'];
							$arg['data'] = json_decode('{}');
						}

					}
				}
			}
		}
		echo json_encode($arg);
	}

	//Used function for pay checkout
	public function clover_pay_checkout_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('amount', 'Amount', 'required|greater_than[0]', array(
						'required' => $this->lang->line('amount_required'),
						'numeric' => $this->lang->line('amount_valid'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$usid = $userdata['data']['id'];
						$name = $userdata['data']['name'];
						$lastname = $userdata['data']['lastname'];
						$time = time();
						$amount = $this->input->post('amount');
						//$savecard = $this->input->post('savecard');
						//$card_id = $this->input->post('card_id');
						$amount = number_format((float) $amount, 2, '.', '');
						$token = $this->input->post('token');
						$card_res = $card_data = $card_Exist = array();
						/*$cart_check = check_cart_with_tax($usid);
						if ($cart_check == false) {
							$arg['status'] = 0;
							$arg['error_code'] = ERROR_FAILED_CODE;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('check_cart_msg');
							$arg['data'] = json_decode('{}');
							echo json_encode($arg);exit;
						}
						echo "<li>Amount : ".$amount;
						echo "<li>cart_check : ".$cart_check;die;
						if ($cart_check !== $amount) {
							$arg['status'] = 0;
							$arg['error_code'] = ERROR_FAILED_CODE;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('amount_incorrect');
							$arg['data'] = json_decode('{}');
							echo json_encode($arg);exit;
						}*/


						/*if (!empty($token)) {
							$payment_data = array(
								'order_number' => $time,
								'amount' => $amount,
								'payment_method' => 'token',
								'token' => array(
									'name' => 'Test Card',
									'code' => $token,
									'complete' => true,
								),
							);
						} else if (!empty($card_id)) {
							$where = array('user_id' => $usid);
							$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
							$customer_code = $result_card[0]['card_id'];

							$payment_data = array(
								'order_number' => $time,
								'amount' => $amount,
								'payment_method' => 'payment_profile',
								'payment_profile' => array(
									'customer_code' => $customer_code,
									'card_id' => $card_id,
									'complete' => true,
								),
							);
						}
						$where = array('user_id' => $usid);
						$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
						if (empty($result_card) && ($savecard == '1')) {
							$legato_token_data = array(
								'language' => 'en',
								'comments' => SITE_NAME,
								'token' => array('name' => 'Test Card',
									'code' => $token),
							);
							$apiurl = 'https://api.na.bambora.com/v1/profiles';
							$responce = $this->bomborapay->profile_create('POST', $apiurl, $legato_token_data);
							if ($responce['code'] == '1') {
								$transaction_data = array('user_id' => $usid,
									'card_id' => $responce['customer_code']);
								$this->dynamic_model->insertdata('user_card_save', $transaction_data);
								$customer_code = $responce['customer_code'];
							}
						} elseif (!empty($result_card) && ($savecard == '1')) {
							$customer_code = $result_card[0]['card_id'];
							$apiurl = "https://api.na.bambora.com/v1/profiles/$customer_code/cards";
							$legato_token_data = array(
								'token' => array('name' => 'Test Card',
									'code' => $token),
							);
							$responce = $this->bomborapay->profile_create('POST', $apiurl, $legato_token_data);
							if ($responce['code'] == '1') {
								$customer_code = $responce['customer_code'];
							}
						}

						if ($savecard == '1') {
							$payment_data = array(
								'order_number' => $time,
								'amount' => $amount,
								'payment_method' => 'payment_profile',
								'payment_profile' => array(
									'customer_code' => $customer_code,
									'card_id' => $card_id,
									'complete' => true,
								),
							);
						}

						// print_r($payment_data);die;
						$business_id = getBusinessId($usid);
						$mid = getUserMarchantId($business_id);
						$marchant_id = $mid['marchant_id'];
						$marchant_id_type = $mid['marchant_id_type'];

						//$mid = '377010002';
						$payUrl = 'https://api.na.bambora.com/v1/payments ';
						$res = $this->bomborapay->payment_checkout('POST', $payUrl, $payment_data, $marchant_id, $marchant_id_type);

						//$res=$this->bomborapay->payment_checkout('POST',$payUrl,$payment_data,$mid);
						// print_r($res); die;

						//echo $res['message'];die;
						if (@$res['approved'] == '1') {
							$ref_num = getuniquenumber();
							$payment_id = !empty(@$res['id']) ? $res['id'] : $ref_num;

							$authorizing_merchant_id = !empty(@$res['authorizing_merchant_id']) ? $res['authorizing_merchant_id'] : '';
							$payment_type = !empty(@$res['type']) ? $res['type'] : '';
							$payment_method = !empty(@$res['payment_method']) ? $res['payment_method'] : '';
							$amount = !empty(@$res['amount']) ? $res['amount'] : '';*/

							$business_id = $this->input->post('business_id');
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
							$amount       = $amount;
							$taxAmount    = 0;

							$res  = clover_api_payment_checkout($user_cc_no,$user_cc_mo,$user_cc_yr,$user_cc_cvv,$user_zip,$amount,$taxAmount,$marchant_id,$clover_key,$access_token,$currency,$token);

							//{ "id" : "R48Q7GPMY2FQY", "amount" : 1800, "amount_refunded" : 0, "currency" : "usd", "created" : 1616661109846, "captured" : true, "ref_num" : "108400500020", "auth_code" : "OK2809", "outcome" : { "network_status" : "approved_by_network", "type" : "authorized" }, "paid" : true, "status" : "succeeded", "source" : { "id" : "clv_1TSTS1iWemARMj4AXHxHikLV", "brand" : "AMEX", "cvc_check" : "unchecked", "exp_month" : "11", "exp_year" : "2025", "first6" : "378282", "last4" : "0005" } }

							//{"message":"400 Bad Request","error":{"code":"token_already_used","message":"You cannot use a clover token more than once unless it is marked as multipay."}}


							//{"paymentId":"30PFKD66YXNCP","result":"APPROVED","authCode":"OK7823","token":"DGY73XR6DDDF0","vaultedCard":{"first6":"411111","last4":"1111","expirationDate":"0321","token":"1894469479681111"}}

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

							//End of logic implement for purachase plan mothly haif yearly and yearly
							//Insert data in transaction table
							$transaction_data = array(
								'authorizing_merchant_id' => $authorizing_merchant_id,
								'payment_type' => $payment_type,
								'payment_method' => $payment_method,
								'responce_all' => json_encode($res),
								'user_id' => $usid,
								'amount' => $amount,
								'trx_id' => $payment_id,
								'order_number' => $time,
								'transaction_type' => 2,
								'payment_status' => "Success",
								'saved_card_id' => 0,
								'create_dt' => $time,
								'update_dt' => $time,
							);
							$transaction_id = $this->dynamic_model->insertdata('transactions', $transaction_data);
							$where = array("user_id" => $usid, "status" => "Pending");
							$cart_data = $this->dynamic_model->getdatafromtable('user_booking', $where);
							if (!empty($cart_data)) {
								foreach ($cart_data as $value) {
									//service_type 1 then update passes status and expiry
									$recurring = 0;
									if ($value['service_type'] == '1') {
										$where1 = array('id' => $value['service_id'], 'service_type' => '1', 'status' => 'Active');
										$business_pass = $this->dynamic_model->getdatafromtable('business_passes', $where1);
										// $pass_start_date=(!empty($business_pass[0]['purchase_date'])) ? $business_pass[0]['purchase_date'] : 0;
										// $pass_end_date=(!empty($business_pass[0]['pass_end_date'])) ? $business_pass[0]['pass_end_date'] : 0;

										$pass_validity = (!empty($business_pass[0]['pass_validity'])) ? $business_pass[0]['pass_validity'] : 0;
										$pass_start_date = $time;
										$getEndDate = ($pass_validity * 24 * 60 * 60) + $time;
										$pass_end_date = ($pass_validity == 0) ? $pass_start_date : $getEndDate;

										if (!empty($business_pass)) {
											$pass_type_subcat = $business_pass[0]['pass_type_subcat'];
											if (!empty($pass_type_subcat)) {
												$where2 = array('id' => $pass_type_subcat);
												$manage_pass = $this->dynamic_model->getdatafromtable('manage_pass_type', $where2);
												if (!empty($manage_pass)) {
													$validity = $manage_pass[0]['pass_days'];
												}
											}

											if ($pass_type_subcat == '37') {
												$today_dt = date('d');
												$a_date = date("Y-m-d");
												$lastmonth_dt = date("t", strtotime($a_date));
												$diff_dt = $lastmonth_dt - $today_dt;
												$diff_dt = $diff_dt + 1;

												$rt = date("Y-m-t", strtotime($a_date));
												$pass_end_date = strtotime($rt);
												$passes_remaining_count = $diff_dt;

												$per_day_amt = $Amt / $lastmonth_dt;

												$per_day_amt = round($per_day_amt, 2);
												$Amt = $per_day_amt * $diff_dt;

												$grand_total = number_format((float) $Amt, 2, '.', '');
												$Amt = $grand_total;
												$recurring = 1;
											}
										}

										$pass_status = 1;
										$where2 = array("user_id" => $usid, "status" => "Pending", "service_type" => '1');
										$booking_data = array(
											'transaction_id' => $transaction_id,
											'status' => "Success",
											'passes_start_date' => $pass_start_date,
											'passes_end_date' => $pass_end_date,
											'passes_status' => $pass_status,
											'passes_total_count' => $validity,
											'passes_remaining_count' => $validity,
											'update_dt' => $time,
											'recurring' => $recurring,
										);
										$booking_id = $this->dynamic_model->updateRowWhere('user_booking', $where2, $booking_data);
									} else {

										$cart_quantity = $value['quantity'];
										$result_product = $this->dynamic_model->getdatafromtable('business_product', array('id' => $value['service_id']));
										$total_quantity = $result_product[0]['quantity'] - $cart_quantity;

										$product_id = $this->dynamic_model->updateRowWhere('business_product', array('id' => $value['service_id']), array('quantity' => $total_quantity));
										$where2 = array("user_id" => $usid, "status" => "Pending", "service_type!=" => '1');
										$booking_data = array(
											'transaction_id' => $transaction_id,
											'status' => "Success",
											'update_dt' => $time,
										);
										$booking_id = $this->dynamic_model->updateRowWhere('user_booking', $where2, $booking_data);
									}
								}
							}
							$response = array('amount' => number_format((float) $amount, 2, '.', ''), 'transaction_date' => date('d M Y'));
							if ($transaction_id) {
								$arg['status'] = 1;
								$arg['error_code'] = HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_succ');
								$arg['data'] = $response;
							} else {
								$arg['status'] = 0;
								$arg['error_code'] = HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_fail');
								$arg['data'] = json_decode('{}');
							}
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['message'] = @$res->error->message;
							$arg['data'] = json_decode('{}');
						}

					}
				}
			}
		}
		echo json_encode($arg);
	}

	//Clover payment token genrate
	public function get_clover_payment_token_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {

			$_POST = json_decode(file_get_contents("php://input"), true);

			if ($_POST) {

				$this->form_validation->set_rules('number', 'Card Number', 'required', array('required' => $this->lang->line('number_required')));
				$this->form_validation->set_rules('expiry_month', 'Expiry Month', 'required', array('required' => $this->lang->line('expiry_month_required')));
				$this->form_validation->set_rules('expiry_year', 'Expiry year', 'required', array('required' => $this->lang->line('expiry_year_required')));
				$this->form_validation->set_rules('cvd', 'Cvv', 'required', array('required' => $this->lang->line('cvd_required')));
				if ($this->form_validation->run() == FALSE) {
					$arg['status'] = 0;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {

					$authorization =  $this->input->get_request_header('Authorization', true);
					if($authorization!="")
					{
					    $userid = decode(base64_decode($authorization));
					    $databusiness = getdatafromtable('business',array('user_id'=>$userid));
						$business_id= $databusiness[0]['id'];
					}
					else
					{
						$business_id = $this->input->post('business_id');
					}


					$number = $this->input->post('number');
					$expiry_month = $this->input->post('expiry_month');
					$expiry_year = $this->input->post('expiry_year');
					$cvd = $this->input->post('cvd');

					$country_code = $this->input->post('country_code');

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

				}
			}

		}
		echo json_encode($arg);
	}

	//Used function for clover buy now new
	public function clover_buy_now_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {

					$this->form_validation->set_rules('service_type', 'Service Type', 'required|trim', array('required' => $this->lang->line('service_type_required')));
					$this->form_validation->set_rules('service_id', 'Service Id', 'required|trim', array('required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('quantity', 'Quantity', 'required', array('required' => $this->lang->line('quantity_required')));

					// $this->form_validation->set_rules('number', 'Card No', 'required', array('required' => $this->lang->line('quantity_required')));
					// $this->form_validation->set_rules('expiry_month', 'Expiry Month', 'required', array('required' => $this->lang->line('quantity_required')));
					// $this->form_validation->set_rules('expiry_year', 'Expiry Year', 'required', array('required' => $this->lang->line('quantity_required')));

					// $this->form_validation->set_rules('amount','Amount', 'required', array( 'required' => $this->lang->line('amount_required')));
					$this->form_validation->set_rules('grand_total', 'grand total', 'required|greater_than[0]', array(
						'required' => $this->lang->line('amount_required'),
						'numeric' => $this->lang->line('amount_valid'),
					));

					if ($this->input->post('service_type') == 2) {
						$this->form_validation->set_rules('slot_date', 'Slot Date', 'required', array('required' => $this->lang->line('date_required')));
						$this->form_validation->set_rules('slot_time_id', 'Slot Id', 'required', array('required' => $this->lang->line('slot_id_required')));
						$this->form_validation->set_rules('token', 'Token', 'required', array('required' => $this->lang->line('token_required')));
						$this->form_validation->set_rules('instructor_id', 'Instructor', 'required', array('required' => $this->lang->line('instructor_id_required')));
					}

					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$recurring = 0;
						$service_id = $this->input->post('service_id');
						$where = array('id' => $service_id, 'status' => 'Active');
						$product_data = $this->dynamic_model->getdatafromtable('business_product', $where);

						$Amt = 0;
						$usid = $userdata['data']['id'];
						$name = $userdata['data']['name'];
						$lastname = $userdata['data']['lastname'];
						$time = time();
						$pass_start_date = $pass_end_date = $pass_status = '';

						//service_type => 1 passes 2 services 3 product
						$service_type = $this->input->post('service_type');
						$quantity = $this->input->post('quantity');
						$token = $this->input->post('token');
						// $amount           = $this->input->post('amount');
						// $amount           = number_format((float)$amount, 2, '.', '');
						$grand_total = $this->input->post('grand_total');
						$grand_total = number_format((float) $grand_total, 2, '.', '');
						//$grand_total           = '10.00';;
						$slot_date = $this->input->post('slot_date');
						$slot_time_id = $this->input->post('slot_time_id');
						$savecard = $this->input->post('savecard');
						if ($service_type == 1) {
							//Check pass already added or not
							$whe = array('user_id' => $usid, 'service_id' => $service_id, 'service_type' => '1', '   passes_status' => '1');
							$chk_pass_booking = $this->dynamic_model->getdatafromtable('user_booking', $whe);
							if (!empty($chk_pass_booking)) {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('pass_already_msg');
								$arg['data'] = json_decode('{}');
								echo json_encode($arg);exit;
							}
						}

						//get data according to service type passes,services,products
						$passes_total_count = 0;
						$passes_remaining_count = 0;
						$recurring_date = '';
						if ($service_type == 1) {
							$where = array('id' => $service_id, 'status' => 'Active');
							$business_pass = $this->dynamic_model->getdatafromtable('business_passes', $where);
							$business_id = (!empty($business_pass[0]['business_id'])) ? $business_pass[0]['business_id'] : 0;
							if ($business_pass[0]['service_type'] == 1) {
								$class_id = $business_pass[0]['service_id'];
							} else {
								$workshop_id = $business_pass[0]['service_id'];
							}
							$pass_start_date = (!empty($business_pass[0]['purchase_date'])) ? $business_pass[0]['purchase_date'] : 0;
							$pass_end_date = (!empty($business_pass[0]['pass_end_date'])) ? $business_pass[0]['pass_end_date'] : 0;

							$pass_start_date = time();
							$validity = (!empty($business_pass[0]['pass_validity'])) ? $business_pass[0]['pass_validity'] : 0;
							$getEndDate = ($validity * 24 * 60 * 60) + $time;
							$pass_end_date = ($validity == 0) ? $pass_start_date : $getEndDate;

							$pass_type_subcat = $business_pass[0]['pass_type_subcat'];
							$where = array('id' => $pass_type_subcat);
							$manage_pass_type = $this->dynamic_model->getdatafromtable('manage_pass_type', $where);

							$pass_days = $manage_pass_type[0]['pass_days'];

							$passes_total_count = $pass_days;
							$passes_remaining_count = $pass_days;
							$Amt = (!empty($business_pass[0]['amount'])) ? $business_pass[0]['amount'] : 0;

							if ($pass_type_subcat == '36') {
								$today_dt = date('d');
								$a_date = date("Y-m-d");
								$lastmonth_dt = date("t", strtotime($a_date));
								$diff_dt = $lastmonth_dt - $today_dt;
								$diff_dt = $diff_dt + 1;

								$rt = date("Y-m-t", strtotime($a_date));
								$recurring_date = $rt;
								$pass_end_date = strtotime($rt);
								$passes_remaining_count = $diff_dt;

								$per_day_amt = $Amt / $lastmonth_dt;

								$per_day_amt = round($per_day_amt, 2);
								$Amt = $per_day_amt * $diff_dt;

								$grand_total = number_format((float) $Amt, 2, '.', '');
								$Amt = $grand_total;
								$recurring = 1;
							} else if (($pass_type_subcat == '33')) {
								// 3 month
								$recurring = 2;
								$recurring_date = date('Y-m-d', strtotime('next month'));
								$rt = date('d-M-y', strtotime('next month'));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-Y', strtotime("$rt +1 month"));
								$pass_end_date = strtotime($rt);
								$date1 = date('d-M-Y');
								$pass_days = dateDiffInDays($date1, $rt);

								$passes_total_count = $pass_days;
								$passes_remaining_count = $pass_days;

							} else if (($pass_type_subcat == '34')) {
								// 6 month
								$recurring = 5;
								$recurring_date = date('Y-m-d', strtotime('next month'));
								$rt = date('d-M-y', strtotime('next month'));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-Y', strtotime("$rt +1 month"));
								$pass_end_date = strtotime($rt);
								$date1 = date('d-M-Y');
								$pass_days = dateDiffInDays($date1, $rt);

								$passes_total_count = $pass_days;
								$passes_remaining_count = $pass_days;
							} else if (($pass_type_subcat == '35')) {
								// 12 month
								$recurring = 11;
								$recurring_date = date('Y-m-d', strtotime('next month'));
								$rt = date('d-M-y', strtotime('next month'));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-Y', strtotime("$rt +1 month"));
								$pass_end_date = strtotime($rt);
								$date1 = date('d-M-Y');
								$pass_days = dateDiffInDays($date1, $rt);
								$passes_total_count = $pass_days;
								$passes_remaining_count = $pass_days;
							}
							// echo  $grand_total; die;
							$pass_status = 1;

						} elseif ($service_type == 2) {
							/* $where = array('id'=>$service_id,'status' => 'Active');
											            	$business_service= $this->dynamic_model->getdatafromtable('service',$where);
											            	$business_id=(!empty($business_service[0]['business_id'])) ? $business_service[0]['business_id'] : 0;
															$Amt=(!empty($business_service[0]['amount'])) ? $business_service[0]['amount'] : 0;
															$con=array("service_id"=>$service_id,"service_type"=>1,"business_id"=>$business_id,"slot_available_status"=>"0","slot_date"=>$slot_date,"id"=>$slot_time_id);
															$appointment_data = $this->dynamic_model->getdatafromtable('business_appointment_book',$con);
															if(empty($appointment_data)){
																$arg['status']     = 0;
													            $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
																$arg['error_line']= __line__;
																$arg['message']    = $this->lang->line('services_already_book');
								                                $arg['data']      =json_decode('{}');
																echo json_encode($arg);exit;
							*/
							$where = array(
								'service_id' => $service_id,
								'service_type' => '2',
								'service_slot_id' => $slot_time_id,
								'status' => 'Success',
							);
							$chk_pass_booking = $this->dynamic_model->getdatafromtable('user_booking', $where);
							$chk_service_schedule = $this->dynamic_model->getdatafromtable('service_scheduling_time_slot', array('status' => 0));
							$chk_appointment_booking = $this->dynamic_model->getdatafromtable('business_appointment_book', array('slot_id' => $this->input->post('slot_time_id')));

							if (!empty($chk_pass_booking)) {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('slot_already_booked');
								$arg['data'] = json_decode('{}');
								echo json_encode($arg);exit();
							} else if (empty($chk_service_schedule)) {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('slot_id_not_found');
								$arg['data'] = json_decode('{}');
								echo json_encode($arg);exit();
							} else if (!empty($chk_appointment_booking)) {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('slot_already_booked');
								$arg['data'] = json_decode('{}');
								echo json_encode($arg);exit();
							}

							$pass_start_date = 0;
							$pass_end_date = 0;
							$service = $this->db->get_where('service', array('id' => $service_id))->row_array();
							$business_id = $service['business_id'];

						} elseif ($service_type == 3) {
							$where = array('id' => $service_id, 'status' => 'Active');
							$product_data = $this->dynamic_model->getdatafromtable('business_product', $where);
							$business_id = (!empty($product_data[0]['business_id'])) ? $product_data[0]['business_id'] : 0;
							$Amt = (!empty($product_data[0]['price'])) ? $product_data[0]['price'] : 0;
							//check product stock limit
							$product_quantity = get_product_quantity($business_id, $service_id);
							if ($product_quantity < $quantity) {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('product_quantity_limit');
								$arg['data'] = json_decode('{}');
								echo json_encode($arg);exit;
							}
						}

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
								'user_id' => $usid,
								'amount' => $amount,
								'trx_id' => $payment_id,
								'order_number' => $time,
								'transaction_type' => ($service_type == 2) ? 3 : $service_type,
								'payment_status' => "Success",
								'saved_card_id' => 0,
								'create_dt' => $time,
								'update_dt' => $time,
								'responce_all' => json_encode($res),
							);
							$transaction_id = $this->dynamic_model->insertdata('transactions', $transaction_data);
							//after that insert into user booking table
							$sub_total = $Amt * $quantity;
							$passData = array(
								'business_id' => $business_id,
								'user_id' => $usid,
								'transaction_id' => $transaction_id,
								'amount' => $Amt,
								'service_type' => $service_type,
								'service_id' => $service_id,
								'class_id' => (!empty($class_id)) ? $class_id : '',
								'workshop_id' => (!empty($workshop_id)) ? $workshop_id : '',
								'quantity' => $quantity,
								'sub_total' => $sub_total,
								'status' => "Success",
								'passes_start_date' => $pass_start_date,
								'passes_end_date' => $pass_end_date,
								'passes_status' => $pass_status,
								'create_dt' => $time,
								'update_dt' => $time,
								'recurring' => $recurring,
								'recurring_date' => $recurring_date,
							);
							if ($service_type == 1) {
								$passData['passes_total_count'] = $passes_total_count;
								$passData['passes_remaining_count'] = $passes_remaining_count;
							}
							if ($service_type == 2) {
								$passData['service_slot_id'] = $slot_time_id;
							}
							$booking_id = $this->dynamic_model->insertdata('user_booking', $passData);
							if ($service_type == 2) {
								$insert_data = array(
									"business_id" => $business_id,
									'booking_id' => $booking_id,
									"user_id" => $this->input->post('instructor_id'),
									"slot_id" => $slot_time_id,
									"service_id" => $service_id,
									"service_type" => 1,
									"slot_available_status" => "1",
									"slot_date" => $slot_date,
									'create_dt' => $time,
									'update_dt' => $time,
								);
								if ($this->input->post('family_user_id')) {
									$insert_data['family_user_id'] = $this->input->post('family_user_id');
								}
								$booking_id = $this->dynamic_model->insertdata('business_appointment_book', $insert_data);
								$this->dynamic_model->updateRowWhere('service_scheduling_time_slot', array('id' => $slot_time_id), array('status' => 1));
							} else {
								$remain_quantity = $product_data[0]['quantity'] - $quantity;
								$update_data1 = array('quantity' => $remain_quantity);

								$cond1 = array("business_id" => $business_id, "id" => $product_data[0]['id']);
								$this->dynamic_model->updateRowWhere('business_product', $cond1, $update_data1);
							}

							/* if($service_type==2){
								$cond=array("service_id"=>$service_id,"service_type"=>1,"business_id"=>$business_id,"slot_available_status"=>"0","slot_date"=>$slot_date,"id"=>$slot_time_id);
								$update_data=array("slot_available_status"=>1);
								$booking_id= $this->dynamic_model->updateRowWhere('business_appointment_book',$cond,$update_data);
							*/

							$response = array('amount' => number_format((float) $sub_total, 2, '.', ''), 'transaction_date' => date('d M Y'));
							if ($transaction_id) {
								$arg['status'] = 1;
								$arg['error_code'] = HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_succ');
								if ($service_type == 2) {
									$arg['booking_id'] = $booking_id;
								}
								$arg['data'] = $response;
							} else {
								$arg['status'] = 0;
								$arg['error_code'] = HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_fail');
								$arg['data'] = json_decode('{}');
							}
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['message'] = @$res->error->message;
							$arg['data'] = json_decode('{}');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}

	//Used function for buy now
	public function buy_now_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('service_type', 'Service Type', 'required|trim', array('required' => $this->lang->line('service_type_required')));
					$this->form_validation->set_rules('service_id', 'Service Id', 'required|trim', array('required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('quantity', 'Quantity', 'required', array('required' => $this->lang->line('quantity_required')));
					// $this->form_validation->set_rules('amount','Amount', 'required', array( 'required' => $this->lang->line('amount_required')));
					$this->form_validation->set_rules('grand_total', 'grand total', 'required|greater_than[0]', array(
						'required' => $this->lang->line('amount_required'),
						'numeric' => $this->lang->line('amount_valid'),
					));

					if ($this->input->post('service_type') == 2) {
						$this->form_validation->set_rules('slot_date', 'Slot Date', 'required', array('required' => $this->lang->line('date_required')));
						$this->form_validation->set_rules('slot_time_id', 'Slot Id', 'required', array('required' => $this->lang->line('slot_id_required')));
						$this->form_validation->set_rules('token', 'Token', 'required', array('required' => $this->lang->line('token_required')));
						$this->form_validation->set_rules('instructor_id', 'Instructor', 'required', array('required' => $this->lang->line('instructor_id_required')));
					}

					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$recurring = 0;
						$service_id = $this->input->post('service_id');
						$where = array('id' => $service_id, 'status' => 'Active');
						$product_data = $this->dynamic_model->getdatafromtable('business_product', $where);

						$Amt = 0;
						$usid = $userdata['data']['id'];
						$name = $userdata['data']['name'];
						$lastname = $userdata['data']['lastname'];
						$time = time();
						$pass_start_date = $pass_end_date = $pass_status = '';

						//service_type => 1 passes 2 services 3 product
						$service_type = $this->input->post('service_type');
						$quantity = $this->input->post('quantity');
						$token = $this->input->post('token');
						// $amount           = $this->input->post('amount');
						// $amount           = number_format((float)$amount, 2, '.', '');
						$grand_total = $this->input->post('grand_total');
						$grand_total = number_format((float) $grand_total, 2, '.', '');
						//$grand_total           = '10.00';;
						$slot_date = $this->input->post('slot_date');
						$slot_time_id = $this->input->post('slot_time_id');
						$savecard = $this->input->post('savecard');
						if ($service_type == 1) {
							//Check pass already added or not
							$whe = array('user_id' => $usid, 'service_id' => $service_id, 'service_type' => '1', '   passes_status' => '1');
							$chk_pass_booking = $this->dynamic_model->getdatafromtable('user_booking', $whe);
							if (!empty($chk_pass_booking)) {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('pass_already_msg');
								$arg['data'] = json_decode('{}');
								echo json_encode($arg);exit;
							}
						}
						//get data according to service type passes,services,products
						$passes_total_count = 0;
						$passes_remaining_count = 0;
						$recurring_date = '';
						if ($service_type == 1) {
							$where = array('id' => $service_id, 'status' => 'Active');
							$business_pass = $this->dynamic_model->getdatafromtable('business_passes', $where);
							$business_id = (!empty($business_pass[0]['business_id'])) ? $business_pass[0]['business_id'] : 0;
							if ($business_pass[0]['service_type'] == 1) {
								$class_id = $business_pass[0]['service_id'];
							} else {
								$workshop_id = $business_pass[0]['service_id'];
							}
							$pass_start_date = (!empty($business_pass[0]['purchase_date'])) ? $business_pass[0]['purchase_date'] : 0;
							$pass_end_date = (!empty($business_pass[0]['pass_end_date'])) ? $business_pass[0]['pass_end_date'] : 0;

							$pass_start_date = time();
							$validity = (!empty($business_pass[0]['pass_validity'])) ? $business_pass[0]['pass_validity'] : 0;
							$getEndDate = ($validity * 24 * 60 * 60) + $time;
							$pass_end_date = ($validity == 0) ? $pass_start_date : $getEndDate;

							$pass_type_subcat = $business_pass[0]['pass_type_subcat'];
							$where = array('id' => $pass_type_subcat);
							$manage_pass_type = $this->dynamic_model->getdatafromtable('manage_pass_type', $where);

							$pass_days = $manage_pass_type[0]['pass_days'];

							$passes_total_count = $pass_days;
							$passes_remaining_count = $pass_days;
							$Amt = (!empty($business_pass[0]['amount'])) ? $business_pass[0]['amount'] : 0;

							if ($pass_type_subcat == '36') {
								$today_dt = date('d');
								$a_date = date("Y-m-d");
								$lastmonth_dt = date("t", strtotime($a_date));
								$diff_dt = $lastmonth_dt - $today_dt;
								$diff_dt = $diff_dt + 1;

								$rt = date("Y-m-t", strtotime($a_date));
								$recurring_date = $rt;
								$pass_end_date = strtotime($rt);
								$passes_remaining_count = $diff_dt;

								$per_day_amt = $Amt / $lastmonth_dt;

								$per_day_amt = round($per_day_amt, 2);
								$Amt = $per_day_amt * $diff_dt;

								$grand_total = number_format((float) $Amt, 2, '.', '');
								$Amt = $grand_total;
								$recurring = 1;
							} else if (($pass_type_subcat == '33')) {
								// 3 month
								$recurring = 2;
								$recurring_date = date('Y-m-d', strtotime('next month'));
								$rt = date('d-M-y', strtotime('next month'));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-Y', strtotime("$rt +1 month"));
								$pass_end_date = strtotime($rt);
								$date1 = date('d-M-Y');
								$pass_days = dateDiffInDays($date1, $rt);

								$passes_total_count = $pass_days;
								$passes_remaining_count = $pass_days;

							} else if (($pass_type_subcat == '34')) {
								// 6 month
								$recurring = 5;
								$recurring_date = date('Y-m-d', strtotime('next month'));
								$rt = date('d-M-y', strtotime('next month'));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-Y', strtotime("$rt +1 month"));
								$pass_end_date = strtotime($rt);
								$date1 = date('d-M-Y');
								$pass_days = dateDiffInDays($date1, $rt);

								$passes_total_count = $pass_days;
								$passes_remaining_count = $pass_days;
							} else if (($pass_type_subcat == '35')) {
								// 12 month
								$recurring = 11;
								$recurring_date = date('Y-m-d', strtotime('next month'));
								$rt = date('d-M-y', strtotime('next month'));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-Y', strtotime("$rt +1 month"));
								$pass_end_date = strtotime($rt);
								$date1 = date('d-M-Y');
								$pass_days = dateDiffInDays($date1, $rt);
								$passes_total_count = $pass_days;
								$passes_remaining_count = $pass_days;
							}
							// echo  $grand_total; die;
							$pass_status = 1;

						} elseif ($service_type == 2) {
							/* $where = array('id'=>$service_id,'status' => 'Active');
											            	$business_service= $this->dynamic_model->getdatafromtable('service',$where);
											            	$business_id=(!empty($business_service[0]['business_id'])) ? $business_service[0]['business_id'] : 0;
															$Amt=(!empty($business_service[0]['amount'])) ? $business_service[0]['amount'] : 0;
															$con=array("service_id"=>$service_id,"service_type"=>1,"business_id"=>$business_id,"slot_available_status"=>"0","slot_date"=>$slot_date,"id"=>$slot_time_id);
															$appointment_data = $this->dynamic_model->getdatafromtable('business_appointment_book',$con);
															if(empty($appointment_data)){
																$arg['status']     = 0;
													            $arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
																$arg['error_line']= __line__;
																$arg['message']    = $this->lang->line('services_already_book');
								                                $arg['data']      =json_decode('{}');
																echo json_encode($arg);exit;
							*/
							$where = array(
								'service_id' => $service_id,
								'service_type' => '2',
								'service_slot_id' => $slot_time_id,
								'status' => 'Success',
							);
							$chk_pass_booking = $this->dynamic_model->getdatafromtable('user_booking', $where);
							$chk_service_schedule = $this->dynamic_model->getdatafromtable('service_scheduling_time_slot', array('status' => 0));
							$chk_appointment_booking = $this->dynamic_model->getdatafromtable('business_appointment_book', array('slot_id' => $this->input->post('slot_time_id')));

							if (!empty($chk_pass_booking)) {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('slot_already_booked');
								$arg['data'] = json_decode('{}');
								echo json_encode($arg);exit();
							} else if (empty($chk_service_schedule)) {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('slot_id_not_found');
								$arg['data'] = json_decode('{}');
								echo json_encode($arg);exit();
							} else if (!empty($chk_appointment_booking)) {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('slot_already_booked');
								$arg['data'] = json_decode('{}');
								echo json_encode($arg);exit();
							}

							$pass_start_date = 0;
							$pass_end_date = 0;
							$service = $this->db->get_where('service', array('id' => $service_id))->row_array();
							$business_id = $service['business_id'];

						} elseif ($service_type == 3) {
							$where = array('id' => $service_id, 'status' => 'Active');
							$product_data = $this->dynamic_model->getdatafromtable('business_product', $where);
							$business_id = (!empty($product_data[0]['business_id'])) ? $product_data[0]['business_id'] : 0;
							$Amt = (!empty($product_data[0]['price'])) ? $product_data[0]['price'] : 0;
							//check product stock limit
							$product_quantity = get_product_quantity($business_id, $service_id);
							if ($product_quantity < $quantity) {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('product_quantity_limit');
								$arg['data'] = json_decode('{}');
								echo json_encode($arg);exit;
							}
						}

						$savecard = $this->input->post('savecard');
						$card_id = $this->input->post('card_id');
						$customer_code = '';
						if (!empty($token)) {
							$payment_data = array(
								'order_number' => $time,
								'amount' => $grand_total,
								'payment_method' => 'token',
								'token' => array(
									'name' => 'Test Card',
									'code' => $token,
									'complete' => true,
								),
							);
						} else if (!empty($card_id)) {

							$where = array('user_id' => $usid);
							$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
							$customer_code = $result_card[0]['card_id'];

							$payment_data = array(
								'order_number' => $time,
								'amount' => $grand_total,
								'payment_method' => 'payment_profile',
								'payment_profile' => array(
									'customer_code' => $customer_code,
									'card_id' => $card_id,
									'complete' => true,
								),
							);
						}

						/* start */
						$mid = getUserMarchantId($business_id);
						$marchant_id = $mid['marchant_id'];
						$marchant_id_type = $mid['marchant_id_type'];

						$where = array('user_id' => $usid,
							'business_id' => $business_id,
						);
						$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
						if (empty($result_card) && ($savecard == '1')) {
							$legato_token_data = array(
								'language' => 'en',
								'comments' => SITE_NAME,
								'token' => array('name' => 'Test Card',
									'code' => $token),
							);
							$apiurl = 'https://api.na.bambora.com/v1/profiles';
							$responce = $this->bomborapay->profile_create('POST', $apiurl, $legato_token_data, $marchant_id, $marchant_id_type);
							if ($responce['code'] == '1') {
								$transaction_data = array('user_id' => $usid,
									'business_id' => $business_id,
									'card_id' => $responce['customer_code'],
								);
								$this->dynamic_model->insertdata('user_card_save', $transaction_data);
								$customer_code = $responce['customer_code'];
							}
						} elseif (!empty($result_card) && ($savecard == '1')) {
							$customer_code = $result_card[0]['card_id'];
							$apiurl = "https://api.na.bambora.com/v1/profiles/$customer_code/cards";
							$legato_token_data = array(
								'token' => array('name' => 'Test Card',
									'code' => $token),
							);
							$responce = $this->bomborapay->profile_create('POST', $apiurl, $legato_token_data, $marchant_id, $marchant_id_type);
							if ($responce['code'] == '1') {
								$customer_code = $responce['customer_code'];
							}
						}

						if ($savecard == '1') {
							$payment_data = array(
								'order_number' => $time,
								'amount' => $grand_total,
								'payment_method' => 'payment_profile',
								'payment_profile' => array(
									'customer_code' => $customer_code,
									'card_id' => $card_id,
									'complete' => true,
								),
							);
						}
						/* end */
						$payUrl = 'https://api.na.bambora.com/v1/payments';

						$res = $this->bomborapay->payment_checkout('POST', $payUrl, $payment_data, $marchant_id, $marchant_id_type);

						//echo $res['message'];die;
						if (@$res['approved'] == 1) {
							$where = array('user_id' => $usid,
								'business_id' => $business_id,
							);
							$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);

							$ref_num = getuniquenumber();
							$payment_id = !empty(@$res['id']) ? $res['id'] : $ref_num;

							$authorizing_merchant_id = !empty(@$res['authorizing_merchant_id']) ? $res['authorizing_merchant_id'] : '';
							$payment_type = !empty(@$res['type']) ? $res['type'] : '';
							$payment_method = !empty(@$res['payment_method']) ? $res['payment_method'] : '';
							$amount = !empty(@$res['amount']) ? $res['amount'] : '';
							//Insert data in transaction table
							$transaction_data = array(
								'authorizing_merchant_id' => $authorizing_merchant_id,
								'payment_type' => $payment_type,
								'payment_method' => $payment_method,
								'user_id' => $usid,
								'amount' => $amount,
								'trx_id' => $payment_id,
								'order_number' => $time,
								'transaction_type' => ($service_type == 2) ? 3 : $service_type,
								'payment_status' => "Success",
								'saved_card_id' => 0,
								'create_dt' => $time,
								'update_dt' => $time,
								'responce_all' => json_encode($res),
							);
							$transaction_id = $this->dynamic_model->insertdata('transactions', $transaction_data);
							//after that insert into user booking table
							$sub_total = $Amt * $quantity;
							$passData = array(
								'business_id' => $business_id,
								'user_id' => $usid,
								'transaction_id' => $transaction_id,
								'amount' => $Amt,
								'service_type' => $service_type,
								'service_id' => $service_id,
								'class_id' => (!empty($class_id)) ? $class_id : '',
								'workshop_id' => (!empty($workshop_id)) ? $workshop_id : '',
								'quantity' => $quantity,
								'sub_total' => $sub_total,
								'status' => "Success",
								'passes_start_date' => $pass_start_date,
								'passes_end_date' => $pass_end_date,
								'passes_status' => $pass_status,
								'create_dt' => $time,
								'update_dt' => $time,
								'recurring' => $recurring,
								'recurring_date' => $recurring_date,
							);
							if ($service_type == 1) {
								$passData['passes_total_count'] = $passes_total_count;
								$passData['passes_remaining_count'] = $passes_remaining_count;
							}
							if ($service_type == 2) {
								$passData['service_slot_id'] = $slot_time_id;
							}
							$booking_id = $this->dynamic_model->insertdata('user_booking', $passData);
							if ($service_type == 2) {
								$insert_data = array(
									"business_id" => $business_id,
									'booking_id' => $booking_id,
									"user_id" => $this->input->post('instructor_id'),
									"slot_id" => $slot_time_id,
									"service_id" => $service_id,
									"service_type" => 1,
									"slot_available_status" => "1",
									"slot_date" => $slot_date,
									'create_dt' => $time,
									'update_dt' => $time,
								);
								if ($this->input->post('family_user_id')) {
									$insert_data['family_user_id'] = $this->input->post('family_user_id');
								}
								$booking_id = $this->dynamic_model->insertdata('business_appointment_book', $insert_data);
								$this->dynamic_model->updateRowWhere('service_scheduling_time_slot', array('id' => $slot_time_id), array('status' => 1));
							} else {
								$remain_quantity = $product_data[0]['quantity'] - $quantity;
								$update_data1 = array('quantity' => $remain_quantity);

								$cond1 = array("business_id" => $business_id, "id" => $product_data[0]['id']);
								$this->dynamic_model->updateRowWhere('business_product', $cond1, $update_data1);
							}

							/* if($service_type==2){
								$cond=array("service_id"=>$service_id,"service_type"=>1,"business_id"=>$business_id,"slot_available_status"=>"0","slot_date"=>$slot_date,"id"=>$slot_time_id);
								$update_data=array("slot_available_status"=>1);
								$booking_id= $this->dynamic_model->updateRowWhere('business_appointment_book',$cond,$update_data);
							*/

							$response = array('amount' => number_format((float) $sub_total, 2, '.', ''), 'transaction_date' => date('d M Y'));
							if ($transaction_id) {
								$arg['status'] = 1;
								$arg['error_code'] = HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_succ');
								if ($service_type == 2) {
									$arg['booking_id'] = $booking_id;
								}
								$arg['data'] = $response;
							} else {
								$arg['status'] = 0;
								$arg['error_code'] = HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_fail');
								$arg['data'] = json_decode('{}');
							}
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['message'] = @$res['message'];
							$arg['data'] = json_decode('{}');
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
	*/
	public function my_studio_list_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$response = array();
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;
						$usid = $userdata['data']['id'];
						$lat = $userdata['data']['lat'];
						$lang = $userdata['data']['lang'];
						$created_by = $userdata['data']['created_by'];

						$lat = !empty($_POST['lat']) ? $_POST['lat'] : $lat;
						$lang = !empty($_POST['lang']) ? $_POST['lang'] : $lang;

						$studio_data = $this->api_model->get_my_studios($usid, $limit, $offset, $lat, $lang, $created_by);
						if (!empty($studio_data)) {
							foreach ($studio_data as $value) {

								$studiodata['business_id'] = $value['id'];
								$studiodata['business_name'] = ucwords($value['business_name']);
								$studiodata['email'] = $value['primary_email'];
								$studiodata['address'] = $value['address'];
								$studiodata['city'] = $value['city'];
								$studiodata['state'] = $value['state'];
								$studiodata['country'] = $value['country'];
								$studiodata['business_phone'] = $value['business_phone'];
								$studiodata['skills'] = get_categories($value['category']);
								$img = site_url() . 'uploads/business/' . $value['logo'];
								$imgname = pathinfo($img, PATHINFO_FILENAME);
								$ext = pathinfo($img, PATHINFO_EXTENSION);
								$thumb = site_url() . 'uploads/business/' . $imgname . '_thumb.' . $ext;

								$busi_img = site_url() . 'uploads/business/' . $value['business_image'];
								$imgnamebusi = pathinfo($busi_img, PATHINFO_FILENAME);
								$extbusi = pathinfo($busi_img, PATHINFO_EXTENSION);
								$thumb_img = site_url() . 'uploads/business/' . $imgnamebusi . '_thumb.' . $extbusi;
								$studiodata['logo'] = $img;
								$studiodata['thumb'] = $thumb;
								$studiodata['business_thumb'] = $thumb_img;

								$distance = $value['distance'] ? $value['distance'] : '0';
								$distance = $distance * 1.609;
								$distance = round($distance, 2);
								$distance = $distance . ' Km';

								//Check my favourite status
								//service_type 1 for business
								$where = array("user_id" => $usid, "service_id" => $value['id'], "service_type" => 1);
								$user_favourite = $this->dynamic_model->getdatafromtable("user_business_favourite", $where);
								$favourite = (!empty($user_favourite)) ? '1' : '0';
								$studiodata['distance'] = $distance;
								$studiodata['favourite'] = $favourite;
								$studiodata['latitude'] = $value['lat'];
								$studiodata['longitude'] = $value['longitude'];
								$response[] = $studiodata;
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
				}
			}
		}
		echo json_encode($arg);
	}
	/****************Function Get my classes list**********************************
		     * @type            : Function
		     * @Author          : Arpit
		     * @function name   : my_class_list
		     * @description     : list of my classes,waiting list,attendance list
		     * @param           : null
		     * @return          : null
	*/
	public function my_class_list_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					$this->form_validation->set_rules('status', 'Status', 'required|numeric', array(
						'required' => $this->lang->line('status_req'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$response = array();
						$time = time();
						$usid = $userdata['data']['id'];
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;
						$business_id = $this->input->post('business_id');
						//status 0 for my class, 1 my waiting list
						$status = $this->input->post('status');
						$status = $status ? $status : 0;
						$time_zone = $this->input->get_request_header('Timezone', true);
						$time_zone = $time_zone ? $time_zone : 'UTC';
						date_default_timezone_set($time_zone);
						$current = date('Y-m-d');
						$current_time = time();
						/* $where = '';
							if(empty($status)){
								$where = ' Where user_attendance.service_type = 1 AND user_attendance.user_id = '.$usid.' AND business_class.business_id = '.$business_id;
							} else {
								$where = ' Where user_attendance.service_type = 1 AND user_attendance.status="waiting" AND user_attendance.user_id = '.$usid.' AND business_class.business_id = '.$business_id;
							}

						*/

						$where = '';
						if (empty($status)) {

							$query = 'SELECT class_scheduling_time.id, class_scheduling_time.instructor_id as instructor_ids, business_class.id as class_id, class_scheduling_time.location_id, business_location.location_name as location, CASE WHEN business_location.map_url IS NULL THEN "" Else business_location.map_url END as location_url, CASE WHEN business_location.location_url IS NULL THEN "" Else business_location.location_url END as web_link, business_class.class_type, business_class.capacity as total_capacity, class_scheduling_time.day_id, manage_week_days.week_name, class_scheduling_time.from_time, class_scheduling_time.to_time, class_scheduling_time.scheduled_date, business_class.class_name, DATE_FORMAT(business_class.start_date, "%e %b %Y") as start_date, DATE_FORMAT(business_class.end_date, "%e %b %Y") as end_date, business_class.class_type, business_class.duration, business_class.capacity, business_class.create_dt FROM `user_attendance` JOIN business_class ON (business_class.id = user_attendance.service_id) JOIN class_scheduling_time ON (class_scheduling_time.id = user_attendance.schedule_id) JOIN business_location on (business_location.id = class_scheduling_time.location_id) JOIN manage_week_days on (manage_week_days.id = class_scheduling_time.day_id) where  user_attendance.user_id = ' . $usid . ' AND business_class.business_id = ' . $business_id . ' AND class_scheduling_time.business_id = ' . $business_id . ' AND user_attendance.status != "checkout"  AND user_attendance.status != "cancel" AND business_class.status="Active"';

							//if ($this->input->post('class_status')) {
							$class_status = $this->input->post('class_status');
							$class_status = $class_status ? $class_status : 0;
							if ($class_status == '0') {
								//$query .= ' AND class_scheduling_time.scheduled_date >= "'.$current.'" AND class_scheduling_time.to_time >= "'.$current_time.'" ';
								$query .= ' AND class_scheduling_time.scheduled_date >= "' . $current . '"';
								$query .= ' ORDER BY class_scheduling_time.from_time ASC LIMIT ' . $limit . ' OFFSET ' . $offset;
							} else {
								//$query .= ' AND class_scheduling_time.scheduled_date < "'.$current.'" AND class_scheduling_time.to_time < "'.$current_time.'" ';
								$query .= ' AND class_scheduling_time.scheduled_date < "' . $current . '"';
								$query .= ' ORDER BY class_scheduling_time.from_time DESC LIMIT ' . $limit . ' OFFSET ' . $offset;
							}
							/*} else {
								$query .= ' ORDER BY class_scheduling_time.from_time ASC LIMIT '.$limit. ' OFFSET '.$offset;
							*/

							//
						} else {
							//echo '--'; die;
							$query = 'SELECT class_scheduling_time.id, class_scheduling_time.instructor_id as instructor_ids, business_class.id as class_id, class_scheduling_time.location_id, business_location.location_name as location, CASE WHEN business_location.location_url IS NULL THEN "" Else business_location.location_url END as location_url, business_class.class_type, business_class.capacity as total_capacity, class_scheduling_time.day_id, manage_week_days.week_name, class_scheduling_time.from_time, class_scheduling_time.to_time, class_scheduling_time.scheduled_date, business_class.class_name, DATE_FORMAT(business_class.start_date, "%e %b %Y") as start_date, DATE_FORMAT(business_class.end_date, "%e %b %Y") as end_date, business_class.class_type, business_class.duration, business_class.capacity, business_class.create_dt FROM `user_attendance` JOIN business_class ON (business_class.id = user_attendance.service_id) JOIN class_scheduling_time ON (class_scheduling_time.id = user_attendance.schedule_id) JOIN business_location on (business_location.id = class_scheduling_time.location_id) JOIN manage_week_days on (manage_week_days.id = class_scheduling_time.day_id) where class_scheduling_time.scheduled_date >= "' . $current . '" AND user_attendance.user_id = ' . $usid . ' AND business_class.business_id = ' . $business_id . ' AND class_scheduling_time.business_id = ' . $business_id . ' AND user_attendance.status="waiting" AND business_class.status="Active"';

							if ($this->input->post('class_status')) {
								$class_status = intval($this->input->post('class_status'));
								if ($class_status == 0) {
									$query .= ' AND class_scheduling_time.to_time >= "' . $current_time . '" ';
									$query .= ' ORDER BY class_scheduling_time.from_time ASC LIMIT ' . $limit . ' OFFSET ' . $offset;
								} else {
									$query .= ' AND class_scheduling_time.to_time < "' . $current_time . '" ';
									$query .= ' ORDER BY class_scheduling_time.from_time DESC LIMIT ' . $limit . ' OFFSET ' . $offset;
								}
							} else {
								$query .= ' ORDER BY class_scheduling_time.from_time ASC LIMIT ' . $limit . ' OFFSET ' . $offset;
							}

						}
						//echo $query;
						$class_data = $this->dynamic_model->getQueryResultArray($query);

						// $class_data = $this->api_model->get_signed_classes($business_id,'',$limit,$offset,$status,$usid);
						if (!empty($class_data)) {

							foreach ($class_data as $value) {

								$unixTimestamp = strtotime($value['scheduled_date']);
								$week_date = date("w", $unixTimestamp);

								$where = "id = " . $value['id'];

								$classesdata['test_from_time'] = date('d M Y h:i:s A', $value['from_time']);

								$time_slote_data = $this->dynamic_model->getdatafromtable('class_scheduling_time', $where);
								if (!empty($time_slote_data)) {
									$from_time = $time_slote_data[0]['from_time'];
									$to_time = $time_slote_data[0]['to_time'];
									$scheduled_date = $time_slote_data[0]['scheduled_date'];
									$instructor_id_sel = $time_slote_data[0]['instructor_id'];
								} else {
									continue;
								}

								$classesdata['scheduled_id'] = $value['id'];
								// $classesdata['scheduled_date'] = $value['scheduled_date'];
								$classesdata['schedule_id'] = $value['id'];
								$classesdata['from_time_utc'] = $from_time;
								$classesdata['from_time_utc'] = $from_time;
								$classesdata['to_time_utc'] = $to_time;
								$classesdata['start_date_utc'] = strtotime($scheduled_date);
								$classesdata['end_date_utc'] = strtotime($scheduled_date);
								$classesdata['class_id'] = $value['class_id'];
								$classesdata['class_name'] = ucwords($value['class_name']);
								$classesdata['from_time'] = $value['from_time'];
								$classesdata['to_time'] = $value['to_time'];
								$classesdata['start_date'] = date("d M Y ", strtotime($value['start_date']));
								$classesdata['end_date'] = date("d M Y ", strtotime($value['end_date']));
								$classesdata['duration'] = $value['duration'] . ' minutes';
								$capicty_used = get_checkin_class_or_workshop_daily_count($value['class_id'], 1, $scheduled_date, $value['id']);
								$classesdata['total_capacity'] = $value['capacity'];
								$classesdata['capacity_used'] = $capicty_used;
								$classesdata['location'] = $value['location'];
								$classesdata['location_url'] = $value['location_url'];

								//$classesdata['class_type']   = get_categories($value['class_type']);

								$classesdata['class_type'] = $this->dynamic_model->getSingleROwColumnValue('manage_category', array('id' => $value['class_type']), 'category_name')['category_name'];

								$instructor_data = $this->instructor_details_get($business_id, $value['id'], $instructor_id_sel);
								$classesdata['instructor_details'] = !empty($instructor_data) ? (array) $instructor_data : json_decode('{}');
								$classesdata['create_dt'] = date("d M Y ", $value['create_dt']);
								$classesdata['create_dt_utc'] = $value['create_dt'];
								$response[] = $classesdata;
							}
							if (!empty($classesdata)) {
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
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = array();
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function my_class_list_old_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					$this->form_validation->set_rules('status', 'Status', 'required|numeric', array(
						'required' => $this->lang->line('status_req'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$response = array();
						$time = time();
						$usid = $userdata['data']['id'];
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;
						$business_id = $this->input->post('business_id');
						//status 0 for my class, 1 my waiting list
						$status = $this->input->post('status');
						$class_data = $this->api_model->get_signed_classes($business_id, '', $limit, $offset, $status, $usid);
						if (!empty($class_data)) {
							foreach ($class_data as $value) {
								//print_r($class_data);die;

								$unixTimestamp = strtotime($value['checkin_dt']);
								$week_date = date("w", $unixTimestamp);

								$where = "business_id = " . $value['business_id'] . " AND class_id = " . $value['id'] . " AND day_id = '" . $week_date . "' AND scheduled_date = '" . $value['checkin_dt'] . "'";

								$time_slote_data = $this->dynamic_model->getdatafromtable('class_scheduling_time', $where);
								if (!empty($time_slote_data)) {
									$from_time = $time_slote_data[0]['from_time'];
									$to_time = $time_slote_data[0]['to_time'];
									$scheduled_date = $time_slote_data[0]['scheduled_date'];
									$instructor_id_sel = $time_slote_data[0]['instructor_id'];
								} else {
									continue;
								}

								$classesdata['from_time_utc'] = $from_time;
								$classesdata['to_time_utc'] = $to_time;
								$classesdata['start_date_utc'] = strtotime($scheduled_date);
								$classesdata['end_date_utc'] = strtotime($scheduled_date);

								$classesdata['class_id'] = $value['id'];
								$classesdata['class_name'] = ucwords($value['class_name']);
								$classesdata['from_time'] = $value['from_time'];
								$classesdata['to_time'] = $value['to_time'];
								//$classesdata['from_time_utc']=strtotime($value['from_time']);
								//$classesdata['start_date_utc']    = strtotime($value['start_date']);
								//$classesdata['end_date_utc']    = strtotime($value['end_date']);
								//$classesdata['to_time_utc'] = strtotime($value['to_time']);

								$classesdata['start_date'] = date("d M Y ", strtotime($value['start_date']));
								$classesdata['end_date'] = date("d M Y ", strtotime($value['end_date']));

								$classesdata['duration'] = $value['duration'] . ' minutes';
								$capicty_used = get_checkin_class_or_workshop_count($value['id'], 1, $time);
								$classesdata['total_capacity'] = $value['capacity'];
								$classesdata['capacity_used'] = $capicty_used;
								//             $capicty_used                = get_checkin_class_or_workshop_count($value['id'],1,$time);
								// $classesdata['capacity']     = $capicty_used.'/'.$value['capacity'];
								$classesdata['location'] = $value['location'];
								$classesdata['class_type'] = get_categories($value['class_type']);
								//$instructor_data             = $this->instructor_list_details($business_id,1,$value['id']);
								$instructor_data = $this->instructor_details_get($business_id, $value['id'], $instructor_id_sel);

								$classesdata['instructor_details'] = !empty($instructor_data) ? (array) $instructor_data : json_decode('{}');
								$classesdata['create_dt'] = date("d M Y ", $value['create_dt']);
								$classesdata['create_dt_utc'] = $value['create_dt'];
								$response[] = $classesdata;
							}
							if (!empty($classesdata)) {
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
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = array();
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}
	/****************Function Get my classes list**********************************
		     * @type            : Function
		     * @Author          : Arpit
		     * @function name   : my_class_list
		     * @description     : list of my classes,waiting list,attendance list
		     * @param           : null
		     * @return          : null
	*/
	public function my_classes_attandance_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$response = array();
						$time = time();
						$usid = $userdata['data']['id'];
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";

						if ($page_no > '1') {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = array();
							$arg['message'] = $this->lang->line('record_not_found');
							echo json_encode($arg);die;
						}
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;
						$attendance_for = $this->input->post('attendance_for');
						$attendance_for = $attendance_for ? $attendance_for : 0;
						$business_id = $this->input->post('business_id');

						if ($attendance_for == '0' || $attendance_for == '1') {
							$class_data = $this->api_model->my_classes_attandance($business_id, $limit, $offset, $usid);
							if (!empty($class_data)) {
								foreach ($class_data as $value) {
									$schedule_id = $value['schedule_id'];
									$where = "id = '" . $schedule_id . "'";
									$time_slote_data = $this->dynamic_model->getdatafromtable('class_scheduling_time', $where);
									$from_time = '';
									$to_time = '';
									$scheduled_date = '';
									$instructor_id_sel = '';

									if (!empty($time_slote_data)) {
										$from_time = $time_slote_data[0]['from_time'];
										$to_time = $time_slote_data[0]['to_time'];
										$scheduled_date = $time_slote_data[0]['scheduled_date'];
										$instructor_id_sel = $time_slote_data[0]['instructor_id'];

										$scheduled_date = strtotime($scheduled_date);

									}

									$classesdata['checkin_dt'] = strtotime($value['checkin_dt']);
									$classesdata['schedule_id'] = $schedule_id;
									$classesdata['attendance_for'] = 1;
									$classesdata['from_time_utc'] = $from_time;
									$classesdata['to_time_utc'] = $to_time;
									$classesdata['start_date_utc'] = $scheduled_date;
									$classesdata['end_date_utc'] = $scheduled_date;
									$classesdata['class_id'] = $value['id'];
									$classesdata['class_name'] = ucwords($value['class_name']);
									$classesdata['from_time'] = $value['from_time'];
									$classesdata['to_time'] = $value['to_time'];
									$classesdata['duration'] = $value['duration'] . ' minutes';

									// $capicty_used                = get_checkin_class_or_workshop_count($value['id'],1,$time);
									$capicty_used = get_checkin_class_or_workshop_daily_count($value['id'], 1, $scheduled_date, $schedule_id);
									$classesdata['total_capacity'] = $value['capacity'];
									$classesdata['capacity_used'] = $capicty_used;
									$classesdata['location'] = $value['location'];
									$status = $this->db->get_where('user_attendance', array('schedule_id' => $schedule_id, 'service_type' => 1, 'user_id' => $usid, 'service_id' => $value['id']))->row_array()['status'];
									$classesdata['status'] = (
										$status == 'absence') ? 'Absent' : (
										$status == 'cancel' ? 'Cancelled' : (
											$status == 'waiting' ? 'Waiting' : (
												$status == 'checkin' ? 'Checked-In' : (
													$status == 'singup' ? 'signed-up' : 'Checked-Out'
												)
											)
										)
									);
									$classesdata['class_type'] = get_categories($value['class_type']);
									$instructor_data = $this->instructor_list_details($business_id, 1, $value['id']);
									$classesdata['instructor_details'] = $instructor_data;
									$classesdata['create_dt'] = date("d M Y ", $value['create_dt']);
									$response[] = $classesdata;
								}
							}
						}

						if ($attendance_for == '0' || $attendance_for == '2') {
							// services data add shift
							$imgePath = base_url() . 'uploads/user/';
							$query = "SELECT t.create_dt, IFNULL(uf.member_name,'') as family_member_name, IFNULL(uf.gender,'') as family_gender, IFNULL(uf.dob,'') as family_dob,(CASE WHEN uf.photo != '' THEN CONCAT('" . $imgePath . "',uf.photo) ELSE '' END ) as family_profile_img, b.family_user_id,s.id as service_id, t.id as transaction_id,t.user_id,t.amount,t.discount,t.trx_id,t.order_number,t.create_dt as payment_date,t.payment_type,t.payment_method,t.responce_all,u.name,u.lastname, u.gender, u.date_of_birth,concat('" . $imgePath . "', u.profile_img) as profile_img, s.service_name,s.duration,s.cancel_policy,s.description, s.tax1, s.tax2, s.tax1_rate, s.tip_option, s.tax2_rate,bs.business_name,bs.address,bs.location_detail,uu.name as instructor_name,uu.lastname as instructor_lastname,concat('" . $imgePath . "', uu.profile_img) as instructor_profile_img, b.status as booking_status,b.passes_start_date as start_time, b.passes_end_date as end_time, b.shift_date, b.tip_comment, bl.location_name,bl.address as location_address, CASE WHEN bl.location_url IS NULL THEN '' Else bl.location_url END as location_url  FROM transactions AS t join user_booking as b on t.id = b.transaction_id JOIN user as u on u.id = b.user_id join service as s on s.id = b.service_id JOIN business as bs on bs.id = b.business_id JOIN user as uu on uu.id = b.shift_instructor LEFT Join business_shift as bshift on bshift.id = b.shift_id left join business_location as bl on bl.id = bshift.location_id left join user_family_details as uf on uf.id = b.family_user_id WHERE b.user_id = '" . $usid . "' AND b.business_id = '" . $business_id . "' AND b.service_type = '2' && b.status = 'Success'  ORDER BY b.shift_date DESC";
							//echo $query; die;
							$service_data = $this->dynamic_model->getQueryResultArray($query);
							if (!empty($service_data)) {
								array_walk($service_data, function (&$key) {
									$workshop_price = $key['amount'];
									$workshop_tax_price = 0;
									$tax1_rate_val = 0;
									$tax2_rate_val = 0;
									$workshop_total_price = $workshop_price;
									if (strtolower($key['tax1']) == 'yes') {
										$tax1_rate = floatVal($key['tax1_rate']);
										$tax1_rate_val = (($workshop_price * $tax1_rate) / 100);
										$workshop_tax_price = $tax1_rate_val;
										$workshop_total_price = $workshop_price + $tax1_rate_val;

									}
									if (strtolower($key['tax2']) == 'yes') {
										$tax2_rate = floatVal($key['tax2_rate']);
										$tax2_rate_val = (($workshop_price * $tax2_rate) / 100);
										$workshop_tax_price = $tax1_rate_val + $tax2_rate_val;
										$workshop_total_price = $workshop_total_price + $tax2_rate_val;
									}

									$key['attendance_for'] = 2;
									$key['tax1_rate'] = number_format($tax1_rate_val, 2);
									$key['tax2_rate'] = number_format($tax2_rate_val, 2);
									$key['service_tax_price'] = number_format($workshop_tax_price, 2);
									$key['service_total_price'] = number_format($workshop_total_price, 2);

									$serviceId = $key['service_id'];
									$imgePath = base_url() . 'uploads/user/';
								});
							}
						}

						//print_r($response); die;
						if ($attendance_for == '0') {
							$response = array_merge($response, $service_data);
							$result = array();
							foreach ($response as $key => $row) {
								$attendance_for = $row['attendance_for'];
								if ($attendance_for == '1') {
									$result['checkin_date'][$key] = $row['checkin_dt'];
								} else if ($attendance_for == '2') {
									$result['checkin_date'][$key] = $row['shift_date'];
								}
							}
//@array_multisort($result['checkin_dt'], SORT_DESC, $result['shift_date'], SORT_DESC,$response);
							@array_multisort($result['checkin_date'], SORT_DESC, $response);
//$response = (array) $response;

						} else if ($attendance_for == '2') {
							$response = $service_data;
						}

						// services end
						if (!empty($response)) {
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
				}
			}
		}
		echo json_encode($arg);
	}

	/****************Function Get my purchases list**********************************
		     * @type            : Function
		     * @Author          : Arpit
		     * @function name   : my_purchase_list
		     * @description     : list of my purchases
		     * @param           : null
		     * @return          : null
	*/
	public function my_purchase_list_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$response = array();
						$time = time();
						$usid = $userdata['data']['id'];
						$page = $this->input->post('pageid');
						$page_no = (!empty($page)) ? $page : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;
						$business_id = $this->input->post('business_id');
						$business_info = $this->dynamic_model->getQueryRowArray('SELECT *  FROM business WHERE id = ' . $business_id);
						$purchase_status = $this->input->post('purchase_status');
						//status 0 for services, 1 passes 2 products
						if ($this->input->post('purchase_status') == '0' || $this->input->post('purchase_status') == '1' || $this->input->post('purchase_status') == '2' || $this->input->post('purchase_status') == '3') {
							if ($purchase_status == 0) {
								$service_query = 'SELECT service.amount as service_amount, service.id as service_id, service.service_name, manage_skills.name, service.create_dt from business_appointment_book JOIN user_booking on (user_booking.id = business_appointment_book.booking_id) JOIN service ON (service.id = business_appointment_book.service_id) JOIN manage_skills on (manage_skills.id = service.service_type) WHERE business_appointment_book.business_id = ' . $business_id . ' AND user_booking.user_id = ' . $usid . ' GROUP BY business_appointment_book.service_id LIMIT ' . $limit . ' OFFSET ' . $offset;

								$response = $this->dynamic_model->getQueryResultArray($service_query);
								if (!empty($response)) {
									array_walk($response, function (&$key) {
										$key['service_name'] = ucwords($key['service_name']);
										$key['service_category'] = ucwords($key['name']);
										$key['create_dt'] = date("d M Y ", $key['create_dt']);
										unset($key['name']);
									});

								}
							} elseif ($purchase_status == '3') {
								$service_result = get_services_list($usid, $limit, $offset);

								$response = $service_result;

							} elseif ($purchase_status == 1) {
								$data = "business_passes.*,user_booking.id as user_booking_id, user_booking.passes_start_date as booking_passes_start_date, user_booking.passes_end_date as booking_passes_end_date, user_booking.passes_total_count, user_booking.passes_remaining_count,user_booking.passes_status";
								$condition = " user_booking.status = 'Success' AND business_passes.business_id='" . $business_id . "' AND user_booking.user_id='" . $usid . "' AND user_booking.service_type='1'";

								/* AND business_passes.status='Active' AND user_booking.passes_status='1'";*/
								$on = 'business_passes.id = user_booking.service_id';
								$service_data = $this->dynamic_model->getTwoTableData($data, 'business_passes', 'user_booking', $on, $condition, $limit, $offset, "user_booking.create_dt", "DESC");
								//print_r($service_data);die;
								if (!empty($service_data)) {
									foreach ($service_data as $value) {
										$passesdata['pass_id'] = $value['id'];
										$passesdata['user_booking_id'] = $value['user_booking_id'];

										$passId = $value['pass_type'];
										$where = array("id" => $passId);
										$manage_pass_data = $this->dynamic_model->getdatafromtable("manage_pass_type", $where);
										$pass_type_name = '';
										if (!empty($manage_pass_data)) {
											$pass_type_name = $manage_pass_data[0]['pass_type'];
										}
										$passesdata['pass_type_name'] = $pass_type_name;

										$passes_status = $value['passes_status'];
										if ($passes_status == '1') {
											$passes_status_label = 'Active';
										} else {
											$passes_status_label = 'Expired';
										}
										$passesdata['pass_status_text'] = $passes_status_label;
										$pass_for = $value['pass_for'];
										if ($pass_for == '0') {
											$pass_for_label = 'Class Pass';
										} else if ($pass_for == '1') {
											$pass_for_label = 'Workshop Pass';
										}
										$passesdata['pass_for'] = ''; //$pass_for_label;

										if ($value['pass_type_subcat'] == '36') {
											$passesdata['pass_mark'] = 'no expiration';
										} else {
											$passesdata['pass_mark'] = '';
										}

										if ($value['service_type'] == '1') {
											$classes_data = $this->dynamic_model->getdatafromtable('business_class', array("id" => $value['service_id']));
											$class_name = (!empty($classes_data)) ? ucwords($classes_data[0]['class_name']) : "";
										} else {
											$workshop_data = $this->dynamic_model->getdatafromtable('business_workshop', array("id" => $value['service_id']));
											$class_name = (!empty($workshop_data)) ? ucwords($workshop_data[0]['workshop_name']) : "";
										}

										$passesdata['class_name'] = ucwords($class_name);
										$passesdata['pass_name'] = ucwords($value['pass_name']);

										$pass_type_category = $value['pass_type'];
										if ($pass_type_category == '1') {
											$pass_type_category = 'PunchCard';
										} else if ($pass_type_category == '10') {
											$pass_type_category = 'TimeFrame';
										} else {
											$pass_type_category = 'Other';
										}

										$passesdata['pass_type_category'] = $pass_type_category;

										$passType = (!empty($value['pass_type'])) ? $value['pass_type'] : '';
										$pass_type_subcat = (!empty($value['pass_type_subcat'])) ? $value['pass_type_subcat'] : '';
										$pass_type = get_passes_type_name($passType, $pass_type_subcat);

										$passesdata['pass_type'] = get_passes_type_name($passType);
										$passesdata['pass_number_counts'] = get_passes_type_name($passType, $pass_type_subcat);
										$passesdata['start_date'] = $value['booking_passes_start_date']; //date("d M Y ",$value['purchase_date']);
										$passesdata['end_date'] = $value['booking_passes_end_date']; //date("d M Y ",$value['pass_end_date']);
										$passesdata['amount'] = $value['amount'];
										$passesdata['pass_validity'] = $value['pass_validity'] . ' ' . "Days";
										$passesdata['total_count'] = $value['passes_total_count'];
										$passesdata['remaining_count'] = $value['passes_remaining_count'];
										// $passesdata['total_count'] = $value['passes_remaining_count'];
										$passesdata['business_logo'] = empty($business_info['business_image']) ? '' : site_url() . 'uploads/business/' . $business_info['business_image'];
										$passesdata['booking_pass_id'] = $value['pass_id'];
										//service_type 2 for passes
										$where1 = array("user_id" => $usid, "service_id" => $value['id'], "service_type" => 2);
										$user_favourite = $this->dynamic_model->getdatafromtable("user_business_favourite", $where1);
										$favourite = (!empty($user_favourite)) ? '1' : '0';
										$passesdata['favourite'] = $favourite;
										$response[] = $passesdata;
									}
								}
							} else {
								//create_dt
								$data = "business_product.*,user_booking.quantity as purchase_quan,user_booking.create_dt as purchase_dt";
								$condition = "business_product.business_id='" . $business_id . "' AND user_booking.user_id='" . $usid . "' AND business_product.status='Active' AND user_booking.service_type='3' AND user_booking.status='Success' ";
								$on = 'business_product.id = user_booking.service_id';
								$service_data = $this->dynamic_model->getTwoTableData($data, 'business_product', 'user_booking', $on, $condition, $limit, $offset, "user_booking.create_dt", "DESC");
								//print_r($service_data);die;
								if (!empty($service_data)) {
									foreach ($service_data as $value) {
										$productdata['product_id'] = $value['id'];
										$productdata['product_name'] = $value['product_name'];
										$productdata['purchase_date'] = $value['purchase_dt'];
										$productdata['product_price'] = $value['price'];
										$productdata['product_status'] = $value['status'];
										$productdata['product_description'] = $value['description'];
										$productdata['quantity'] = $value['purchase_quan'];
										$image_datas = get_product_images($value['id']);
										$productdata['product_images'] = $image_datas;
										$response[] = $productdata;
									}
								}
							}
						} else {

							// Service listing
							$service_query = 'SELECT service.amount as service_amount, service.id as service_id, service.service_name, manage_skills.name, service.create_dt from business_appointment_book JOIN user_booking on (user_booking.id = business_appointment_book.booking_id) JOIN service ON (service.id = business_appointment_book.service_id) JOIN manage_skills on (manage_skills.id = service.service_type) WHERE business_appointment_book.business_id = ' . $business_id . ' AND user_booking.user_id = ' . $usid . ' GROUP BY business_appointment_book.service_id LIMIT ' . $limit . ' OFFSET ' . $offset;
							// echo $service_query; die;

							$response = $this->dynamic_model->getQueryResultArray($service_query);
							if (!empty($response)) {
								array_walk($response, function (&$key) {
									$key['service_name'] = ucwords($key['service_name']);
									$key['service_category'] = ucwords($key['name']);
									$key['create_dt'] = date("d M Y ", $key['create_dt']);
									unset($key['name']);
									$key['purchase_status'] = "0";
								});

							}

							$data = "business_passes.*, user_booking.id as user_booking_id, user_booking.passes_start_date as booking_passes_start_date, user_booking.passes_end_date as booking_passes_end_date, user_booking.passes_total_count, user_booking.passes_remaining_count,user_booking.passes_status";
							//user_booking.passes_status = 1 AND
							$condition = "business_passes.business_id='" . $business_id . "'AND user_booking.status <> 'Pending' AND user_booking.user_id='" . $usid . "' AND user_booking.service_type = '1'";
							/*AND business_passes.status='Active' AND user_booking.service_type='1'";*/
							$on = 'business_passes.id = user_booking.service_id';
							$service_data = $this->dynamic_model->getTwoTableData($data, 'user_booking', 'business_passes', $on, $condition, $limit, $offset, "user_booking.create_dt", "ASC");
							// echo $this->db->last_query(); die;
							//print_r($condition);die;
							if (!empty($service_data)) {
								foreach ($service_data as $value) {
									$passesdata['user_booking_id'] = $value['user_booking_id'];
									$passId = $value['pass_type'];
									$where = array("id" => $passId);
									$manage_pass_data = $this->dynamic_model->getdatafromtable("manage_pass_type", $where);
									$pass_type_name = '';
									if (!empty($manage_pass_data)) {
										$pass_type_name = $manage_pass_data[0]['pass_type'];
									}
									$passesdata['pass_type_name'] = $pass_type_name;

									$pass_for = $value['pass_for'];
									if ($pass_for == '0') {
										$pass_for_label = 'Class Pass';
									} else if ($pass_for == '1') {
										$pass_for_label = 'Workshop Pass';
									}
									$passesdata['pass_for'] = ''; //$pass_for_label;

									$passesdata['pass_id'] = $value['id'];
									if ($value['pass_type_subcat'] == '36') {
										$passesdata['pass_mark'] = 'no expiration';
									} else {
										$passesdata['pass_mark'] = '';
									}
									if ($value['service_type'] == '1') {
										$classes_data = $this->dynamic_model->getdatafromtable('business_class', array("id" => $value['service_id']));
										$class_name = (!empty($classes_data)) ? ucwords($classes_data[0]['class_name']) : "";
									} else {
										$workshop_data = $this->dynamic_model->getdatafromtable('business_workshop', array("id" => $value['service_id']));
										$class_name = (!empty($workshop_data)) ? ucwords($workshop_data[0]['workshop_name']) : "";
									}

									$passes_status = $value['passes_status'];
									if ($passes_status == '1') {
										$passes_status_label = 'Active';
									} else {
										$passes_status_label = 'Expired';
									}
									$passesdata['pass_status_text'] = $passes_status_label;

									$passesdata['class_name'] = ucwords($class_name);
									$passesdata['pass_name'] = ucwords($value['pass_name']);

									$passType = (!empty($value['pass_type'])) ? $value['pass_type'] : '';

									$pass_type_category = $passType;
									if ($pass_type_category == '1') {
										$pass_type_category = 'PunchCard';
									} else if ($pass_type_category == '10') {
										$pass_type_category = 'TimeFrame';
									} else {
										$pass_type_category = 'Other';
									}

									$passesdata['pass_type_category'] = $pass_type_category;

									$pass_type_subcat = (!empty($value['pass_type_subcat'])) ? $value['pass_type_subcat'] : '';
									$pass_type = get_passes_type_name($passType, $pass_type_subcat);

									$passesdata['pass_type'] = get_passes_type_name($passType);
									$passesdata['pass_number_counts'] = get_passes_type_name($passType, $pass_type_subcat);
									$passesdata['start_date'] = $value['booking_passes_start_date']; //date("d M Y ",$value['purchase_date']);
									$passesdata['end_date'] = $value['booking_passes_end_date']; //date("d M Y ",$value['pass_end_date']);
									$passesdata['amount'] = $value['amount'];
									$passesdata['pass_validity'] = $value['pass_validity'] . ' ' . "Days";
									$passesdata['total_count'] = $value['passes_total_count'];
									$passesdata['remaining_count'] = $value['passes_remaining_count'];
									$passesdata['business_logo'] = empty($business_info['business_image']) ? '' : site_url() . 'uploads/business/' . $business_info['business_image'];
									$passesdata['booking_pass_id'] = $value['pass_id'];
									//service_type 2 for passes
									$where1 = array("user_id" => $usid, "service_id" => $value['id'], "service_type" => 2);
									$user_favourite = $this->dynamic_model->getdatafromtable("user_business_favourite", $where1);
									$favourite = (!empty($user_favourite)) ? '1' : '0';
									$passesdata['favourite'] = $favourite;
									$passesdata['purchase_status'] = "1";
									$response[] = $passesdata;
								}
							}

							$service_result = get_services_list($usid, $limit, $offset);
							if (!empty($service_result)) {
								foreach ($service_result as $key => $value) {
									$response[] = $value;
								}
							}

							$data = "business_product.*,user_booking.quantity as purchase_quan,user_booking.create_dt as purchase_dt";
							$condition = "business_product.business_id='" . $business_id . "' AND user_booking.user_id='" . $usid . "' AND business_product.status='Active' AND user_booking.service_type='3' AND user_booking.status <> 'Pending'"; /*AND user_booking.status='Success'";*/
							$on = 'business_product.id = user_booking.service_id';
							$service_data = $this->dynamic_model->getTwoTableData($data, 'business_product', 'user_booking', $on, $condition, $limit, $offset, "user_booking.create_dt", "DESC");
							//print_r($service_data);die;
							if (!empty($service_data)) {
								foreach ($service_data as $value) {
									$productdata['product_id'] = $value['id'];
									$productdata['product_name'] = $value['product_name'];
									$productdata['purchase_date'] = $value['purchase_dt'];
									$productdata['product_price'] = $value['price'];
									$productdata['product_status'] = $value['status'];
									$productdata['product_description'] = $value['description'];
									$productdata['quantity'] = $value['purchase_quan'];
									$image_datas = get_product_images($value['id']);
									$productdata['product_images'] = $image_datas;
									$productdata['purchase_status'] = "2";
									$response[] = $productdata;
								}
							}

						}

						if ($response) {
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
				}
			}
		}
		echo json_encode($arg);
	}

	/****************Function Get my purchases Details**********************************
		     * @type            : Function
		     * @Author          : Arpit
		     * @function name   : my_purchase_detail
		     * @description     : dettails of my purchases
		     * @param           : null
		     * @return          : null
	*/
	public function my_purchase_detail_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					$this->form_validation->set_rules('service_id', 'Service Id', 'required|numeric', array(
						'required' => $this->lang->line('service_id_required'),
						'numeric' => $this->lang->line('service_id_required'),
					));

					$this->form_validation->set_rules('search_type', 'Serach Type', 'required|numeric|less_than_equal_to[2]|greater_than_equal_to[0]', array(
						'required' => 'Search type is required',
						'numeric' => 'Search type is required',
						'less_than' => 'Invalid Search Type',
					));

					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$response = array();
						$usid = $userdata['data']['id'];
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;
						$type = $this->input->post('search_type'); // 0 - All, 1 - Upcomming, 2 - Complete | Old Date
						$business_id = $this->input->post('business_id');
						$service_id = $this->input->post('service_id');

						$query = "SELECT business_appointment_book.id, service.service_name, manage_skills.name as service_type, service_scheduling_time.service_date, service.duration, service_scheduling_time_slot.start_time, service_scheduling_time_slot.end_time, user_booking.amount, user_booking.tax_amount, IF (business_appointment_book.family_user_id = 0, (SELECT concat(user.name, ',', IFNULL(user.lastname,''), ',', IFNULL(user.gender,''), ',', IFNULL(user.date_of_birth,'')) as details from user WHERE user.id = user_booking.user_id), (SELECT concat(user_family_details.member_name, ',' ,IFNULL(user_family_details.gender, ''), ',', IFNULL(user_family_details.dob, '')) FROM user_family_details WHERE id = business_appointment_book.family_user_id)) as user_details FROM `business_appointment_book` JOIN user_booking ON (user_booking.id = business_appointment_book.booking_id) JOIN service_scheduling_time_slot on (service_scheduling_time_slot.id = business_appointment_book.slot_id) JOIN service on (service.id = business_appointment_book.service_id) JOIN manage_skills on (manage_skills.id = service.service_type) JOIN service_scheduling_time on (service_scheduling_time.id = service_scheduling_time_slot.service_scheduling_time_id) WHERE business_appointment_book.business_id = " . $business_id . " AND business_appointment_book.service_id = " . $service_id . " AND user_booking.user_id = " . $usid;

						if ($type == 1) {
							$query .= ' AND service_scheduling_time.service_date >= DATE(NOW())';
						} else if ($type == 2) {
							$query .= ' AND service_scheduling_time.service_date <= DATE(NOW())';
						}

						$response = $this->dynamic_model->getQueryResultArray($query);

						if (!empty($response)) {

							array_walk($response, function (&$key) {
								$key['customer_detail'] = array('id' => 2);
								$key['appointment_detail'] = array('id' => 2);
								$key['service_detail'] = array('id' => 2);
								$key['payment_detail'] = array('id' => 2);
							});
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
				}
			}
		}
		echo json_encode($arg);
	}
	/****************Function get relations*********************
		     * @type            : Function
		     * @Author          : Aamir
		     * @function name   : get_relations
		     * @description     : get relations
		     * @param           : null
		     * @return          : null
	*/
	public function get_relations_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
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
		}
		echo json_encode($arg);
	}
	/****************Function add_member **********************************
		     * @type            : Function
		     * @Author          : Arpit
		     * @function name   : add_member
		     * @description     : Add Membrer
		     * @param           : null
		     * @return          : null
	*/
	public function add_member_post() {
		$arg = array();
		if ($_POST) {
			$version_result = version_check_helper1();
			if ($version_result['status'] != 1) {
				$arg = $version_result;
			} else {
				$userdata = parentuserprofile();
				if ($userdata['status'] != 1) {
					$arg = $userdata;
				} else {
					$this->form_validation->set_rules('fullname', 'Full Name', 'required|trim', array('required' => $this->lang->line('full_name')));
					$this->form_validation->set_rules('gender', 'Gender', 'required|trim');
					$this->form_validation->set_rules('dob', 'Date of birth', 'required|trim', array('required' => $this->lang->line('dob_required')));
					/* if(empty($_FILES['image']['name'])){
						$this->form_validation->set_rules('image','Image', 'required|trim', array( 'required' => $this->lang->line('image_req')));
					*/
					$this->form_validation->set_rules('relative_id', 'Relative Id', 'required|trim', array('required' => $this->lang->line('relative_id_req')));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {

						$time = time();
						$role = 3;
						$role2 = 4;
						$usid = $userdata['data']['id'];

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
						//$image = 'userdefault.png';

						$default_img = $fullname ? $fullname : 'u';
						$default_img = strtolower(substr($default_img, 0, 1));
						$image = $default_img . '.png';

						if (!empty($_FILES['image']['name'])) {
							$image = $this->dynamic_model->fileupload('image', 'uploads/user');
						}

						/* $where = array('email' => $email);
							                        $result = $this->dynamic_model->check_user_role($email,$role,1,$role2);
							                        if(!empty($result))
							                        {
							                        $arg['status']    = 0;
							                        $arg['error_code'] = REST_Controller::HTTP_OK;
							                        $arg['error_line']= __line__;
							                        $arg['message']   = $this->lang->line('family_member_already_register');
							                        echo json_encode($arg); die;
							                        }
						*/

						/*$userdata=array(
														            "relative_id"=>$relative_id,
														            "member_name"=>$fullname,
														            "photo"=>$image,
														            "dob"=>$dob,
							                                        "gender"=>$gender,
														            "user_id"=>$usid,
														            "status"=>"Active",
														            "create_dt"=>$time,
														            "update_dt"=>$time
						*/
						//$relative_id = $this->dynamic_model->insertdata('user_family_details',$userdata);
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
			echo json_encode($arg);
		}
	}
	/****************Function edit_member **********************************
		     * @type            : Function
		     * @Author          : Arpit
		     * @function name   : edit-member
		     * @description     : Edit Membrer
		     * @param           : null
		     * @return          : null
	*/
	public function edit_member_post() {
		$arg = array();
		if ($_POST) {
			$version_result = version_check_helper1();
			if ($version_result['status'] != 1) {
				$arg = $version_result;
			} else {
				$userdata = checkuserid();
				if ($userdata['status'] != 1) {
					$arg = $userdata;
				} else {

					$this->form_validation->set_rules('member_id', 'Member Id', 'required', array('required' => $this->lang->line('member_id_req')));
					$this->form_validation->set_rules('fullname', 'Full Name', 'required|trim', array('required' => $this->lang->line('full_name')));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$time = time();
						$usid = $userdata['data']['id'];
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
							$updatedata['photo'] = $image;
						}
						if (!empty($relative_id)) {
							$updatedata['relative_id'] = $relative_id;
						}
						if (!empty($fullname)) {
							$updatedata['member_name'] = $fullname;
						}
						if (!empty($dob)) {
							$updatedata['dob'] = $dob;
						}
						$where = array("id" => $member_id);
						$updatedata['update_dt'] = $time;

						$relative_id = $this->dynamic_model->updateRowWhere('user_family_details', $where, $updatedata);

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
			echo json_encode($arg);
		}
	}
	/****************Function remove_member **********************************
		     * @type            : Function
		     * @Author          : Arpit
		     * @function name   : remove_member
		     * @description     : Remove member
		     * @param           : null
		     * @return          : null
	*/
	public function remove_member_post() {
		$arg = array();
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
			echo json_encode($arg);
		}
	}
	/****************Function add_member **********************************
		     * @type            : Function
		     * @Author          : Arpit
		     * @function name   : add_member
		     * @description     : Add Membrer
		     * @param           : null
		     * @return          : null
	*/

	/****************Function Get member list**********************************
		     * @type            : Function
		     * @Author          : Arpit
		     * @function name   : get_member_list
		     * @description     : list of member
		     * @param           : null
		     * @return          : null
	*/
	public function get_member_list_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$response = array();
						$time = time();
						$usid = $userdata['data']['id'];
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;

						$user_id = $this->input->get_request_header('userid', true);
						$parentId = $this->input->get_request_header('parentId', true);

						$response = get_family_member($parentId, $user_id);
						/*$condition=array("created_by"=>$usid,"is_deleted"=>'0');
												$member_data= $this->dynamic_model->getdatafromtable('user',$condition,'*',$limit,$offset,"create_dt","DESC");
												if(!empty($member_data)){
												    foreach($member_data as $value)
										            {
										            	$memberdata['memeber_id']   = $value['id'];
							                            $memberdata['id']   = $value['id'];
										            	$memberdata['member_name'] = ucwords($value['name']);
										            	$memberdata['image']        = base_url().'uploads/user/'.$value['profile_img'];
										            	$memberdata['relation']     = get_family_name($value['relation_id']);
										            	$memberdata['relative_id']     = $value['created_by'];
							                            $memberdata['email']     = $value['email'];
										            	$memberdata['dob']     = $value['date_of_birth'];
							                            $memberdata['gender']     = $value['gender'];
										            	$memberdata['create_dt']    = date("d M Y ",$value['create_dt']);
										            	$response[]	                = $memberdata;
										            }
						*/
						if ($response) {
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
				}
			}
		}
		echo json_encode($arg);
	}
	/****************Function booking service details**************************
		     * @type            : Function
		     * @Author          : arpit
		     * @function name   : booking_service_details
		     * @description     :  booking service details.
		     * @param           : null
		     * @return          : null
	*/
	public function booking_service_details_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('user_id', 'User Id', 'required|numeric', array(
						'required' => $this->lang->line('user_id'),
						'numeric' => $this->lang->line('user_id_numeric'),
					));
					$this->form_validation->set_rules('service_id', 'Service Id', 'required|trim', array('required' => $this->lang->line('service_id_required')));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$response = array();
						$user_id = $this->input->post("user_id");
						$service_id = $this->input->post("service_id");
						$user_data = getuserdetail($user_id);
						$where = array('id' => $service_id);
						$service_data = $this->dynamic_model->getdatafromtable('service', $where);
						$business_id = (!empty($service_data[0]['business_id'])) ? $service_data[0]['business_id'] : '';
						$business_data = $this->dynamic_model->getdatafromtable('business', $where);
						$category = (!empty($business_data[0]['category'])) ? $business_data[0]['category'] : '';
						if (!empty($user_data)) {
							$response = array(
								"instructor_details" => $user_data,
								"skills" => get_categories($category),
								"experience" => "9 years",
								"appointment_time" => "12pm to 2pm",
								"fees" => '$19.99/hour',
								"duration_of_service" => '1 hour',

							);

							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $response;
							$arg['message'] = $this->lang->line('profile_details');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = json_decode('{}');
							$arg['message'] = $this->lang->line('record_not_found');
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
		     * @function name   : service_appointment_details
		     * @description     : service appointment details
		     * @param           : null
		     * @return          : null
	*/
	public function service_appointment_details_post_old() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('user_id', 'User Id', 'required|trim', array('required' => $this->lang->line('user_id')));
					$this->form_validation->set_rules('service_id', 'Service Id', 'required|trim', array('required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					$this->form_validation->set_rules('select_date', 'Select Date', 'required|trim', array('required' => $this->lang->line('select_date_required')));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$usid = $userdata['data']['id'];
						$response = array();
						$time = time();
						$currdate = date('Y-m-d');
						$user_id = $this->input->post('user_id');
						$service_id = $this->input->post('service_id');
						$business_id = $this->input->post('business_id');
						$select_date = $this->input->post('select_date');

						$where = array("service_id" => $service_id, "service_type" => 1, "business_id" => $business_id, "slot_available_status" => "0", "slot_date" => $select_date);

						$appointment_data = $this->dynamic_model->getdatafromtable('business_appointment_book', $where);
						//print_r($appointment_data);die;
						if (!empty($appointment_data)) {
							foreach ($appointment_data as $value) {
								$ptime_datas["slot_time_id"] = $value["id"];
								$slot_data = $this->dynamic_model->getdatafromtable('business_slots', array("slot_id" => $value['slot_id']));
								$ptime_datas["slot_from_time"] = $slot_data[0]['slot_time_from'];
								$ptime_datas["slot_to_time"] = $slot_data[0]['slot_time_to'];
								$ptime_datas["slot_date"] = $value["slot_date"];
								$ptime_datas["slot_id"] = $value["slot_id"];
								$ptime_datas["slot_available_status"] = $value["slot_available_status"];
								$response[] = $ptime_datas;
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
							$arg['data'] = json_decode('{}');
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}
	/****************Function service complete booking**********************************
		     * @type            : Function
		     * @Author          : Arpit
		     * @function name   : service_complete_booking
		     * @description     : service complete booking
		     * @param           : null
		     * @return          : null
	*/
	public function service_appointment_book_details_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('user_id', 'User Id', 'required|trim', array('required' => $this->lang->line('user_id')));
					$this->form_validation->set_rules('service_id', 'Service Id', 'required|trim', array('required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					$this->form_validation->set_rules('slot_date', 'Slot Date', 'required|trim', array('required' => $this->lang->line('select_date_required')));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$usid = $userdata['data']['id'];
						$response = array();
						$time = time();
						$currdate = date('Y-m-d');
						$user_id = $this->input->post('user_id');
						$service_id = $this->input->post('service_id');
						$business_id = $this->input->post('business_id');
						$slot_date = $this->input->post('slot_date');

						$where = array("service_id" => $service_id, "service_type" => 1, "business_id" => $business_id, "slot_available_status" => "0", "slot_date" => $slot_date);
						$appointment_data = $this->dynamic_model->getdatafromtable('business_appointment_book', $where);
						//print_r($appointment_data);die;
						if ($appointment_data) {
							$service_data = $this->dynamic_model->getdatafromtable('service', array("id" => $service_id));
							$amount = $service_data[0]['amount'];
							//               $insertData =   array(
							// 	                'business_id'   =>$business_id,
							// 					'user_id'  		=>$usid,
							// 					'amount'  		=>$amount,
							// 					'service_type'  =>2,
							// 					'service_id'  	=>$service_id,
							// 					'status'  		=>"Pending",
							// 					'create_dt'   	=>$time,
							// 					'update_dt'   	=>$time
							//                    );
							// $booking_id= $this->dynamic_model->insertdata('user_booking',$insertData);
							//                if($booking_id){
							$customer_details = array("full_name" => ucwords($userdata['data']['name'] . ' ' . $userdata['data']['lastname']), "gender" => $userdata['data']['gender'], "age" => date_of_birth($userdata['data']['date_of_birth']));

							//get instructor data
							$instructor_data = getuserdetail($user_id);
							$instructor_name = ucwords($instructor_data['name'] . ' ' . $instructor_data['lastname']);

							$appointment_details = array("service_time" => get_slots_time($appointment_data[0]['slot_id']), "service_date" => $appointment_data[0]['slot_date'], "instructor_name" => $instructor_name, "duration" => $service_data[0]['duration'] . ' hour', "skills" => get_categories($service_data[0]['service_type']));
							$service_details = array("type" => get_categories($service_data[0]['service_type']));
							$payment_details = array("appointment_fees" => $service_data[0]['amount'], "tax" => "0", "total_amount" => $service_data[0]['amount']);

							$response = array("customer_details" => $customer_details, "appointment_details" => $appointment_details, "service_details" => $service_details, "payment_details" => $payment_details);
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $response;
							$arg['message'] = $this->lang->line('record_found');

							//                }else{
							//                $arg['status']     = 0;
							// $arg['error_code']  = REST_Controller::HTTP_OK;
							// $arg['error_line']= __line__;
							// $arg['data']       = json_decode('{}');
							// $arg['message']    = $this->lang->line('record_not_found');
							//                }
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = json_decode('{}');
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function cardGet_post() {
		$arg = array();
		$_POST = json_decode(file_get_contents("php://input"), true);
		$userid = $this->input->post('userid');
		//$where = array('user_id' => $userid);
		// $result = $this->dynamic_model->getdatafromtable('user_card_save', $where);

		/* $sql = "SELECT ucs.*,b.business_name FROM user_card_save as ucs JOIN business AS b on ucs.business_id = b.id WHERE ucs.user_id = '" . $userid . "' and ucs.id_deleted='0' ORDER BY ucs.id DESC";
		$result = $this->dynamic_model->getQueryResultArray($sql);
		$data = array();
		if (!empty($result)) {
			foreach ($result as $key => $value) {*/


				//var_dump($value);
				// $card_id = $value['card_id'];
				 //$business_name = $value['business_name'];
				/*$url = "https://api.na.bambora.com/v1/profiles/$card_id/cards";
				$res = $this->bomborapay->profile_create('GET', $url);
				// echo '<pre/>';
				//print_r($res);
				if (!empty($res)) {
					if (!empty($res['card'])) {
						foreach ($res['card'] as $key => $value) {
							$data[] = array('customer_code' => $res['customer_code'],
								'card_id' => $value['card_id'],
								'business_name' => $business_name,
								'function' => $value['function'],
								'name' => $value['name'],
								'number' => $value['number'],
								'expiry_month' => $value['expiry_month'],
								'expiry_year' => $value['expiry_year'],
								'card_type' => $value['card_type'],
							);
						}
					}
				}*/

				$card_data = $this->dynamic_model->getdatafromtable('user_card_save', array('user_id' => $userid, 'id_deleted' => '0'));
				if($card_data)
				{

					$where1 = array('id' => $card_data[0]['business_id'], 'status' => 'Active');
					$business_data = $this->dynamic_model->getdatafromtable('business', $where1);
					//var_dump($business_data); die;
					$where = array('id' => $business_data[0]['user_id'], 'status' => 'Active');
					$user_data = $this->dynamic_model->getdatafromtable('user', $where);


					//var_dump($user_data);die;
					$marchant_id  = $user_data[0]['marchant_id'];
					$country_code = $user_data[0]['marchant_id_type'];
					$clover_key   = $user_data[0]['clover_key'];
					$access_token = $user_data[0]['access_token'];

					$where3 = array('id' => $userid, 'status' => 'Active');
					$user_data3 = $this->dynamic_model->getdatafromtable('user', $where3);

					$clover_customer_profile_id =  $user_data3[0]['clover_customer_profile_id']; //'D5TATZMAD6QQW';

					$firstName     = $user_data3[0]['name'];
		    		$lastName      = $user_data3[0]['lastname'];
		    		$customer_name = $firstName.' '.$lastName;
					if($country_code==1)
					{
						$currency = CURRENCY_CODE_USA;
					}
					else if($country_code==2)
					{
						$currency = CURRENCY_CODE_CAD;
					}

					$url = CLOVER_CARD_BASE_URL.'/merchants/'.$marchant_id.'/customers/'.$clover_customer_profile_id.'?expand=cards';
					$curlPost = array();
					$ch = curl_init();
				    curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
					curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: Bearer '. $access_token));//'idempotency-key:af2bbe3c4b4dd3682793cc09155a9a7a'
					//curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
				    $data1 = curl_exec($ch);
				    curl_close($ch);
				    //print_r($data1);die;
				    $array_data = json_decode($data1);

				   //print_r($array_data->cards->elements); die;

				    $card_array  = @$array_data->cards->elements;
				    if(!empty($card_array))
				    {
				    	foreach ($card_array as $key1 => $val)
				    	{

				    		//$sql = "SELECT ucs.*,b.business_name FROM user_card_save as ucs JOIN business AS b on ucs.business_id = b.id WHERE ucs.user_id = '" . $userid . "' and ucs.id_deleted='0' ORDER BY ucs.id DESC limit 0,1";
							//$result = $this->dynamic_model->getQueryResultArray($sql);

				    		$expiry_month        = substr($val->expirationDate, 0, 2);
		    				$expiry_year         = '20'.substr($val->expirationDate, -2);

				    		$data[] = array('customer_code' => $val->customer->id,
						            'user_id' => $userid,
									'card_id' => $val->id,
									'business_id' => '',//$result[0]['business_id'],
									'business_name' => '',//$result[0]['business_name'],
									'card_token' => $val->token,
									'function' => '',
									'name' => $customer_name,
									'number' => $val->first6.'-XXXX-'.$val->last4, //decode($value['card_no']),
									'expiry_month' => $expiry_month,//decode($value['expiry_month']),
									'expiry_year' => $expiry_year,//decode($value['expiry_year']),
									'card_type' => $val->cardType,//$value['card_type'],
								);
				    	}
				    }


				//}

					if (!empty($data)) {
						$arg['status'] = 1;
						$arg['error_code'] = REST_Controller::HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['message'] = 'successfully';
						$arg['data'] = $data;
					} else {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = 'no card found';
					}
				}
				else
				{
					$arg['status'] = 0;
					$arg['error_code'] = 0;
					$arg['error_line'] = __line__;
					$arg['message'] = 'no card found';
				}

		/*} else {

			$arg['status'] = 0;
			$arg['error_code'] = 0;
			$arg['error_line'] = __line__;
			$arg['message'] = 'no card found';
		}*/

		echo json_encode($arg);
	}

	public function services_details_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {

					$this->form_validation->set_rules('service_id', 'Service Id', 'required|trim', array('required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {

						$userId = $userdata['data']['id'];
						$service_id = $this->input->post('service_id');
						$business_id = $this->input->post('business_id');

						$service_data = $this->dynamic_model->getdatafromtable('service',
							array('is_client_visible' => 'yes', 'status' => 'Active', 'id' => $service_id, 'business_id' => $business_id),
							'id as service_id, service_name, create_dt, duration, amount as service_charge, tax1, tax2, tax1_rate, tax2_rate, cancel_policy, tip_option, description, time_needed'
						);

						if (!empty($service_data)) {

							array_walk($service_data, function (&$key) {

								$workshop_price = $key['service_charge'];
								$workshop_tax_price = 0;
								$tax1_rate_val = 0;
								$tax2_rate_val = 0;
								$workshop_total_price = $workshop_price;
								if (strtolower($key['tax1']) == 'yes') {
									$tax1_rate = floatVal($key['tax1_rate']);
									$tax1_rate_val = (($workshop_price * $tax1_rate) / 100);
									$workshop_tax_price = $tax1_rate_val;
									$workshop_total_price = $workshop_price + $tax1_rate_val;

								}
								if (strtolower($key['tax2']) == 'yes') {
									$tax2_rate = floatVal($key['tax2_rate']);
									$tax2_rate_val = (($workshop_price * $tax2_rate) / 100);
									$workshop_tax_price = $tax1_rate_val + $tax2_rate_val;
									$workshop_total_price = $workshop_total_price + $tax2_rate_val;
								}

								$key['tax1_rate'] = number_format($tax1_rate_val, 2);
								$key['tax2_rate'] = number_format($tax2_rate_val, 2);
								$key['service_tax_price'] = number_format($workshop_tax_price, 2);
								$key['service_total_price'] = number_format($workshop_total_price, 2);

								$serviceId = $key['service_id'];
								$key["service_id"] = encode($serviceId);
								$imgePath = base_url() . 'uploads/user/';
								$instructor = $this->dynamic_model->getQueryResultArray("SELECT user.id, user.name, user.lastname, concat('" . $imgePath . "', user.profile_img) as profile_img, user.availability_status, instructor_details.total_experience as experience, instructor_details.appointment_fees, instructor_details.appointment_fees_type, instructor_details.skill FROM service_instructor JOIN user on (user.id = service_instructor.instructor_id) JOIN instructor_details on (instructor_details.user_id = user.id) where service_instructor.service_id = '" . $serviceId . "' GROUP BY user.id");

								array_walk($instructor, function (&$keys) {
									$keys['appointment_fees'] = floatVal($keys['appointment_fees']);
									$skills = $keys['skill'];

									// $keys['skill'] = $this->dynamic_model->getQueryRowArray('SELECT GROUP_CONCAT(name) as skill from manage_skills where id IN ('.$skills.')');
									/* $keys['skill'] = $this->dynamic_model->getQueryRowArray('SELECT GROUP_CONCAT(name) as skill from manage_skills where id in ('.$skills.')')['skill']; */
									/*

										$keys['skill_details'] = $this->dynamic_model->getQueryResultArray('SELECT id, name as category_name from manage_skills where id in ('.$skills.')');
									*/
								});
								$key["instructor"] = $instructor;
								$key["create_dt"] = date("d M Y ", $key['create_dt']);
								unset($key["service_id"]);
							});

							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $service_data;
							$arg['message'] = $this->lang->line('record_found');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = json_decode('{}');
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function services_details_20102020_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('service_id', 'Service Id', 'required|trim', array('required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$usid = $userdata['data']['id'];
						$getcat = array();
						$time = time();
						$date = date("Y-m-d", $time);
						$response = $pass_arr = array();
						$service_id = $this->input->post('service_id');
						$business_id = $this->input->post('business_id');

						$query = 'SELECT service.id as service_id, service.service_name, service.create_dt, manage_skills.id as skill_id, manage_skills.name, service_scheduling.id as scheduled_id, service.duration, service.amount as service_charge, service.tax1, service.tax2,  service.tax1_label as tax_name, service.tax2_label as tax2_name, service.tax1_rate, service.tax2_rate, service.cancel_policy, service.tip_option FROM service_scheduling JOIN service on (service.id = service_scheduling.service_id) JOIN manage_skills on (manage_skills.id = service.service_type) WHERE service_scheduling.status = "Active" AND service_scheduling.service_id = ' . $service_id . ' AND service_scheduling.business_id = ' . $business_id;

						$service_data = $this->dynamic_model->getQueryResultArray($query);

						if (!empty($service_data)) {

							array_walk($service_data, function (&$key) {
								$key['service_category'] = array(
									'id' => $key['skill_id'],
									'category_name' => $key['name'],
								);

								$key['service_charge'] = floatVal($key['service_charge']);
								if ($key['tax1'] == 'Yes') {
									$key['tax1_rate'] = floatVal($key['tax1_rate']);
								} else {
									$key['tax1_rate'] = 0;
								}

								if ($key['tax2'] == 'Yes') {
									$key['tax2_rate'] = floatVal($key['tax2_rate']);
								} else {
									$key['tax2_rate'] = 0;
								}

								$getSchedule = 'SELECT GROUP_CONCAT(service_scheduling.id) schedule_id FROM `service_scheduling` WHERE business_id = ' . $this->input->post('business_id') . ' AND service_id = ' . $this->input->post('service_id');

								$getScheduleIds = $this->dynamic_model->getQueryRowArray($getSchedule);

								$query_ins = 'SELECT GROUP_CONCAT(service_scheduling_time.instructor_id) as instructor FROM `service_scheduling_time` WHERE scheduled_id IN (' . $getScheduleIds['schedule_id'] . ')';

								$instructor = $this->dynamic_model->getQueryRowArray($query_ins);
								$unique = explode(',', $instructor['instructor']);
								$unique_filter = array_unique($unique);

								$unique_ins = implode(',', $unique_filter);
								$query_info = 'SELECT user.id, user.name, user.lastname, user.profile_img, user.availability_status, instructor_details.total_experience as experience, instructor_details.appointment_fees, instructor_details.appointment_fees_type, instructor_details.skill FROM user join instructor_details on (instructor_details.user_id = user.id) where user.id in (' . $unique_ins . ')';
								$instructor = $this->dynamic_model->getQueryResultArray($query_info);
								array_walk($instructor, function (&$keys) {
									$url = site_url() . 'uploads/user/' . $keys['profile_img'];
									$keys['profile_img'] = $url;
									$keys['appointment_fees'] = floatVal($keys['appointment_fees']);
									$skills = $keys['skill'];

									$keys['skill'] = $this->dynamic_model->getQueryRowArray('SELECT GROUP_CONCAT(name) as skill from manage_skills where id in (' . $skills . ')')['skill'];
									$keys['skill_details'] = $this->dynamic_model->getQueryResultArray('SELECT id, name as category_name from manage_skills where id in (' . $skills . ')');

									$keys['appointment_time'] = $this->dynamic_model->getQueryResultArray('SELECT service_scheduling_time.id, service_scheduling_time.service_date, manage_week_days.week_name as week, business_location.location_name, service_scheduling_time.from_time, service_scheduling_time.to_time FROM `service_scheduling_time` JOIN manage_week_days on (manage_week_days.id = service_scheduling_time.day_id) left JOIN business_location on (business_location.id = service_scheduling_time.location_id) WHERE service_scheduling_time.service_date BETWEEN date(now()) AND (date_add(date_add(date(now()), interval -WEEKDAY(date(now()))-1 day), interval 7 day)) AND service_scheduling_time.scheduled_id IN (SELECT GROUP_CONCAT(id) as schdule FROM `service_scheduling` where service_scheduling.business_id = ' . $this->input->post('business_id') . ' AND service_scheduling.service_id = ' . $this->input->post('service_id') . ')');
								});

								$key['instructor_details'] = $instructor;
								$key["create_dt"] = date("d M Y ", $key['create_dt']);
								unset($key['name']);
								unset($key['skill_id']);
								unset($key['scheduled_id']);
								// unset($key['duration']);
							});

							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $service_data[0];
							$arg['message'] = $this->lang->line('record_found');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = json_decode('{}');
							$arg['message'] = $this->lang->line('record_not_found');
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
		     * @function name   : service_appointment_details
		     * @description     : service appointment details
		     * @param           : null
		     * @return          : null
	*/
	public function service_appointment_details_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					// $this->form_validation->set_rules('user_id','User Id','required|trim',array('required'=>$this->lang->line('user_id')));
					$this->form_validation->set_rules('service_id', 'Service Id', 'required|trim', array('required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					$this->form_validation->set_rules('instructor_id', 'Instructor', 'required|trim', array('required' => 'Instructor id is required'));
					$this->form_validation->set_rules('select_date', 'Select Date', 'required|trim', array('required' => $this->lang->line('select_date_required')));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$usid = $userdata['data']['id'];
						$response = array();
						$time = time();
						$currdate = date('Y-m-d');
						// $user_id=  $this->input->post('user_id');
						$service_id = $this->input->post('service_id');
						$business_id = $this->input->post('business_id');
						$select_date = date('Y-m-d', $this->input->post('select_date'));
						$instructor_id = $this->input->post('instructor_id');

						$scheduledInfo = "SELECT service_scheduling_time.id, service.duration, service_scheduling_time.from_time, service_scheduling_time.to_time, service_scheduling_time.instructor_id FROM `service_scheduling` JOIN service_scheduling_time on (service_scheduling_time.scheduled_id = service_scheduling.id) JOIN service on (service.id = service_scheduling.service_id) WHERE service.business_id = " . $business_id . " AND service.id = " . $service_id . " AND service_scheduling_time.service_date = '" . $select_date . "' AND service_scheduling_time.instructor_id LIKE '%" . $instructor_id . "%'";

						$collection = $this->dynamic_model->getQueryResultArray($scheduledInfo);

						if (!empty($collection)) {

							$obj_array = array();
							for ($i = 0; $i < count($collection); $i++) {
								$object = $collection[$i];
								$response = $this->dynamic_model->getQueryResultArray('SELECT id, start_time, end_time FROM service_scheduling_time_slot WHERE status = 0 AND service_scheduling_time_id = ' . $object['id']);
								array_push($obj_array, $object['id']);
								if (empty($response)) {
									// date_default_timezone_set("Asia/Kolkata");
									$start_date = date('h:i A', $object['from_time']);
									$end_date = date('h:i A', $object['to_time']);
									$insertArray = array();
									$range = range(strtotime($start_date), strtotime($end_date), $object['duration'] * 60);
									foreach ($range as $time) {
										$timestamp = $time + $object['duration'] * 60;
										$temp_array = array(
											'service_scheduling_time_id' => $object['id'],
											'start_time' => get_str_to_time(date("h:i A", $time)),
											'end_time' => get_str_to_time(date("h:i A", $timestamp)),
											'status' => 0,
										);
										array_push($insertArray, $temp_array);
									}array_pop($insertArray);

									$this->db->insert_batch('service_scheduling_time_slot', $insertArray);
								}
							}
							$sId = implode(',', $obj_array);
							$response = $this->dynamic_model->getQueryResultArray('SELECT id, start_time, end_time FROM service_scheduling_time_slot WHERE status = 0 AND service_scheduling_time_id IN (' . $sId . ')');

							$query_info = 'SELECT user.id, user.name, user.lastname, user.profile_img, user.availability_status, instructor_details.total_experience as experience, instructor_details.appointment_fees, instructor_details.appointment_fees_type, instructor_details.skill FROM user join instructor_details on (instructor_details.user_id = user.id) where user.id = ' . $this->input->post('instructor_id');
							$instructor = $this->dynamic_model->getQueryRowArray($query_info);
							$url = site_url() . 'uploads/user/' . $instructor['profile_img'];
							$instructor['profile_img'] = $url;
							$instructor['appointment_fees'] = floatVal($instructor['appointment_fees']);
							$skills = $instructor['skill'];
							$instructor['skill'] = $this->dynamic_model->getQueryRowArray('SELECT GROUP_CONCAT(name) as skill from manage_skills where id in (' . $skills . ')')['skill'];
							$instructor['skill_details'] = $this->dynamic_model->getQueryResultArray('SELECT id, name as category_name from manage_skills where id in (' . $skills . ')');

							$query = 'SELECT service.id as service_id, service.service_name, service.start_date_time, service.end_date_time, business_location.location_name as location, service.create_dt, manage_skills.id as skill_id, manage_skills.name, service_scheduling.id as scheduled_id, service.duration, service.amount as service_charge, service.tax1, service.tax2,  service.tax1_label as tax_name, service.tax2_label as tax2_name, service.tax1_rate, service.tax2_rate, service.cancel_policy, service.tip_option FROM service_scheduling JOIN service on (service.id = service_scheduling.service_id) JOIN business_location ON (business_location.id = service_scheduling.location)  JOIN manage_skills on (manage_skills.id = service.service_type) WHERE service_scheduling.status = "Active" AND service_scheduling.service_id = ' . $service_id . ' AND service_scheduling.business_id = ' . $business_id;

							$query = 'SELECT service.id as service_id, service.service_name, service.start_date_time, service.end_date_time, service.duration, service.amount as service_charge, service.tax1, service.tax2, service.tax1_label as tax_name, service.tax2_label as tax2_name, service.tax1_rate, service.tax2_rate, service.cancel_policy, service.tip_option, service.create_dt, manage_skills.id as skill_id, manage_skills.name, (SELECT business_location.location_name FROM `service_scheduling` JOIN business_location on (business_location.id = service_scheduling.location) WHERE service_id = service.id GROUP BY service_id) as location FROM service JOIN manage_skills on (manage_skills.id = service.service_type) WHERE service.id = ' . $service_id . ' AND service.business_id = ' . $business_id;

							$service_data = $this->dynamic_model->getQueryRowArray($query);

							if (!empty($service_data)) {
								$temp = array();
								$temp['service_id'] = $service_data['service_id'];
								$temp['service_name'] = $service_data['service_name'];
								$temp['start_date_time'] = $service_data['start_date_time'];
								$temp['end_date_time'] = $service_data['end_date_time'];
								$temp['duration'] = $service_data['duration'];
								$temp['service_charge'] = floatVal($service_data['service_charge']);
								$temp['tax1'] = $service_data['tax1'];
								$temp['tax2'] = $service_data['tax2'];
								$temp['tax_name'] = $service_data['tax_name'];
								$temp['tax2_name'] = $service_data['tax2_name'];
								$temp['tax1_rate'] = ($service_data['tax1'] == 'Yes') ? floatVal($service_data['tax1_rate']) : 0;
								$temp['tax2_rate'] = ($service_data['tax2'] == 'Yes') ? floatVal($service_data['tax2_rate']) : 0;
								$temp['cancel_policy'] = $service_data['cancel_policy'];
								$temp['tip_option'] = $service_data['tip_option'];
								$temp['create_dt'] = date("d M Y ", $service_data['create_dt']);
								$temp['location'] = $service_data['location'];
								$temp['service_category'] = array(
									'id' => $service_data['skill_id'],
									'category_name' => $service_data['name'],
								);
								$service_data = $temp;
							}
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = array('slot_data' => $response, 'instructor_data' => $instructor, 'service_data' => $service_data);
							$arg['message'] = $this->lang->line('record_found');
						} else {

							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = json_decode('{}');
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function appoiment_details_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
					'required' => $this->lang->line('page_no'),
					'numeric' => $this->lang->line('page_no_numeric'),
				));

				if ($this->input->post('booking_id')) {
					$this->form_validation->set_rules('booking_id', 'Booking Id', 'required|numeric');
				}

				if ($this->form_validation->run() == FALSE) {

					$arg['status'] = 0;
					$arg['error_code'] = 0;
					$arg['error_line'] = __line__;
					$arg['message'] = get_form_error($this->form_validation->error_array());

				} else {

					$response = array();
					$time = time();
					$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
					$page_no = $page_no - 1;
					$limit = config_item('page_data_limit');
					$offset = $limit * $page_no;
					$user_id = $userdata['data']['id'];

					$query = 'SELECT business_appointment.slot_available_status, business_appointment.id as appointment_id, business_appointment.business_id, user_booking.user_id as user_booking_id, user_booking.amount, user_booking.transaction_id, user_booking.sub_total, user_booking.tax_amount, business_appointment.user_id as instructor_id, user.name as instructor_name, user.lastname  as instructor_name,  business_appointment.slot_date, user_booking.user_id as user_id, business_appointment.family_user_id, service_scheduling_time_slot.start_time, service_scheduling_time_slot.end_time, service_scheduling_time.day_id, manage_week_days.week_name, service_scheduling.location, business_location.location_name, service.service_name, service.service_type, manage_skills.name as category_name FROM `business_appointment_book` as business_appointment JOIN user on (user.id = business_appointment.user_id) JOIN user_booking on (user_booking.id = business_appointment.booking_id) JOIN service_scheduling_time_slot on (service_scheduling_time_slot.id = business_appointment.slot_id) JOIN service_scheduling_time on (service_scheduling_time.id = service_scheduling_time_slot.service_scheduling_time_id) JOIN service_scheduling on (service_scheduling.id = service_scheduling_time.scheduled_id) JOIN service on (service.id = service_scheduling.service_id)   JOIN manage_week_days on (manage_week_days.id = service_scheduling_time.day_id) JOIN business_location on (business_location.id = service_scheduling.location)  JOIN manage_skills on (manage_skills.id = service.service_type) WHERE user_booking.user_id = ' . $user_id;

					if ($this->input->post('booking_id')) {
						$query .= ' AND business_appointment.id = ' . $this->input->post('booking_id');
					} else {
						$query .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
					}

					$collection = $this->dynamic_model->getQueryResultArray($query);

					if (!empty($collection)) {
						array_walk($collection, function (&$key) {
							$user_family_id = $key['family_user_id'];
							$get_user = '';
							if ($user_family_id != 0) {
								$get_user = 'SELECT user_family_details.member_name as name, user_family_details.photo as profile_img, user_family_details.gender, user_family_details.dob FROM user_family_details JOIN user on (user.id = user_family_details.user_id) WHERE user_family_details.id = ' . $user_family_id;
							} else {
								$get_user = 'SELECT name, lastname, profile_img, gender, date_of_birth as dob FROM user WHERE id = ' . $key['user_booking_id'];
							}
							$info = $this->dynamic_model->getQueryRowArray($get_user);
							if (!empty($info)) {
								if ($user_family_id != 0) {
									$family_profile = $info['profile_img'];
									$profile_family = site_url() . 'uploads/user/' . $family_profile;
									$info['profile_img'] = $profile_family;
									$member = explode(' ', $info['name']);
									$info['name'] = $member[0];
									$info['lastname'] = (count($member) > 1) ? $member[1] : '';

								} else {
									$user_profile = $info['profile_img'];
									$profile_user = site_url() . 'uploads/user/' . $user_profile;
									$info['profile_img'] = $profile_user;
								}
							}
							$status = ($key['slot_available_status'] == 3) ? 0 : 1;
							$key['status'] = $status;
							unset($key['slot_available_status']);
							$key['user_info'] = $info;
							unset($key['user_booking_id']);
						});
						$arg['status'] = 1;
						$arg['error_code'] = REST_Controller::HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['message'] = $this->lang->line('record_found');
						if ($this->input->post('booking_id')) {
							$arg['data'] = $collection[0];
						} else {
							$arg['data'] = $collection;
						}

					} else {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = 'no data found';
					}
				}

			}
		}

		echo json_encode($arg);

	}

	public function pay_at_desk_post() {

		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				$this->form_validation->set_rules('service_id', 'Service Id', 'required|numeric');
				$this->form_validation->set_rules('service_type', 'Service Type', 'required|numeric');
				$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric');
				$this->form_validation->set_rules('quantity', 'Quantity', 'required|numeric');
				$this->form_validation->set_rules('amount', 'Amount', 'required|numeric');

				if ($this->form_validation->run() == FALSE) {

					$arg['status'] = 0;
					$arg['error_code'] = 0;
					$arg['error_line'] = __line__;
					$arg['message'] = get_form_error($this->form_validation->error_array());

				} else {

					$user_id = $userdata['data']['id'];
					$service_id = $this->input->post('service_id');
					$service_type = $this->input->post('service_type');
					$business_id = $this->input->post('business_id');
					$amount = $this->input->post('amount');
					$tax = $this->input->post('tax');
					$slot_date = $this->input->post('slot_date');
					$slot_time_id = $this->input->post('slot_time_id');
					$instructor_id = $this->input->post('instructor_id');
					$quantity = $this->input->post('quantity');

					$preapreArray = array(
						'user_id' => $user_id,
						'service_id' => $service_id,
						'service_type' => $service_type,
						'business_id' => $business_id,
						'amount' => $amount,
						'tax' => ($tax) ? $tax : 0.00,
						'slot_date' => ($slot_date) ? $slot_date : '',
						'slot_time_id' => ($slot_time_id) ? $slot_time_id : 0,
						'instructor_id' => ($instructor_id) ? $instructor_id : 0,
						'reference_payment_id' => getuniquenumber(),
						'status' => 'Pending',
						'created_dt' => date('Y-m-d'),
						'quantity' => ($quantity) ? $quantity : 0,
					);
					$requestData = $this->dynamic_model->insertdata('user_payment_requests', $preapreArray);

					if ($requestData) {
						$arg['status'] = 1;
						$arg['error_code'] = REST_Controller::HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['message'] = 'Payment request successfully';

					} else {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = 'Failed to create payment request';
					}
				}

			}
		}

		echo json_encode($arg);

	}

	public function appoiment_cancel_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				$this->form_validation->set_rules('appointment_id', 'appointment id', 'required|numeric');

				if ($this->form_validation->run() == FALSE) {
					$arg['status'] = 0;
					$arg['error_code'] = 0;
					$arg['error_line'] = __line__;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {
					$response = array();
					$time = time();
					$appointment_id = $this->input->post('appointment_id');

					$query = 'SELECT * FROM business_appointment_book as ba WHERE ba.id = ' . $appointment_id;
					$collection = $this->dynamic_model->getQueryRowArray($query);
					if (!empty($collection)) {

						$slot_available_status = $collection['slot_available_status'];
						if ($slot_available_status == '3') {
							$arg['status'] = 0;
							$arg['error_code'] = 0;
							$arg['error_line'] = __line__;
							$arg['message'] = 'Appointment already cancel.';
						} else if ($slot_available_status == '2') {
							$arg['status'] = 0;
							$arg['error_code'] = 0;
							$arg['error_line'] = __line__;
							$arg['message'] = 'Appointment not cancel.';
						} else {
							//update slote status
							$where = array('id' => $appointment_id);
							$updateData = array('slot_available_status' => '3');
							$this->dynamic_model->updateRowWhere('business_appointment_book', $where, $updateData);
							$query = 'SELECT * FROM business_appointment_book as ba WHERE ba.id = ' . $appointment_id;
							$collection = $this->dynamic_model->getQueryRowArray($query);

							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['message'] = 'Appointment successfully cancel';
							$arg['data'] = $collection;
						}
					} else {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['data'] = array();
						$arg['message'] = 'no appointment found';
					}
				}

			}
		}
		echo json_encode($arg);
	}

	public function cardDelete_post() {
		$arg = array();
		$_POST = json_decode(file_get_contents("php://input"), true);
		$clover_customer_profile_id = $this->input->post('id');
		$card_id = $this->input->post('card_id');

		$this->form_validation->set_rules('id', 'profile id', 'required');
		$this->form_validation->set_rules('card_id', 'card id', 'required');

		if ($this->form_validation->run() == FALSE) {
			$arg['status'] = 0;
			$arg['error_code'] = 0;
			$arg['error_line'] = __line__;
			$arg['message'] = get_form_error($this->form_validation->error_array());
		} else {

		 //    $url = "https://api.na.bambora.com/v1/profiles/$id/cards/$card_id";
			// $res = $this->bomborapay->profile_create('DELETE', $url);


			$where = array('profile_id' => $clover_customer_profile_id, 'card_id' => $card_id);
			$card_data = $this->dynamic_model->getdatafromtable('user_card_save', $where);
			if($card_data)
			{
				$where1 = array('id' => $card_data[0]['business_id'], 'status' => 'Active');
				$business_data = $this->dynamic_model->getdatafromtable('business', $where1);
				//var_dump($business_data); die;
				$where2 = array('id' => $business_data[0]['user_id'], 'status' => 'Active');
				$user_data = $this->dynamic_model->getdatafromtable('user', $where2);
				$marchant_id  = $user_data[0]['marchant_id'];
				$country_code = $user_data[0]['marchant_id_type'];
				$clover_key   = $user_data[0]['clover_key'];
				$access_token = $user_data[0]['access_token'];

				$where3 = array('id' => $card_data[0]['user_id'], 'status' => 'Active');
				$user_data3 = $this->dynamic_model->getdatafromtable('user', $where3);

				$clover_customer_profile_id =  $user_data3[0]['clover_customer_profile_id']; //'D5TATZMAD6QQW';

				$firstName     = $user_data3[0]['name'];
	    		$lastName      = $user_data3[0]['lastname'];
	    		$customer_name = $firstName.' '.$lastName;
				if($country_code==1)
				{
					$currency = CURRENCY_CODE_USA;
				}
				else if($country_code==2)
				{
					$currency = CURRENCY_CODE_CAD;
				}

				$url = CLOVER_CARD_BASE_URL.'/merchants/'.$marchant_id.'/customers/'.$clover_customer_profile_id.'/cards/'.$card_id;
				$curlPost = array();
				$ch = curl_init();
			    curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: Bearer '. $access_token));//'idempotency-key:af2bbe3c4b4dd3682793cc09155a9a7a'
				//curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
			    $data1 = curl_exec($ch);
			    curl_close($ch);
			    //print_r($data);die;
			    $array_data = json_decode($data1);


				$this->dynamic_model->updateRowWhere('user_card_save', array('card_id'=>$card_id,'profile_id'=>$clover_customer_profile_id), array('id_deleted'=>1));

				// $data[$key] = $res;
				//$data =$res;

				/*if ($res['code'] == '1') {
					$status = 1;
					$message = $res['message'];
				} else {
					$status = 0;
					$message = $res['message'];
				}*/

				$arg['status'] = 1;
				$arg['error_code'] = REST_Controller::HTTP_OK;
				$arg['error_line'] = __line__;
				$arg['message'] = 'Card Successfully Removed';
				$arg['data'] = json_decode('{}');
			}
			else
			{
				$arg['status'] = 1;
				$arg['error_code'] = REST_Controller::HTTP_OK;
				$arg['error_line'] = __line__;
				$arg['message'] = 'No record found';
				$arg['data'] = json_decode('{}');
			}

		}
		echo json_encode($arg);
	}

	public function questionnaire_post() {
		$arg = array();
		$where = array('status' => 'Active');
		$result = $this->dynamic_model->getdatafromtable('manage_questionnaire', $where);
		if (!empty($result)) {
			$i = 1;
			foreach ($result as $key) {
				# code...
				$id = $key['id'];
				$i++;
				$question_options = $this->api_model->question_options($id);
				$data[] = array('question_id' => $id,
					'question_title' => $key['question_title'],
					'types' => $key['types'],
					'question_options' => $question_options);
			}
			$arg['status'] = 1;
			$arg['error_code'] = REST_Controller::HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['message'] = ''; //$this->lang->line('thank_msg1');
			$arg['data'] = $data;

		} else {

			$arg['status'] = 0;
			$arg['error_code'] = 0;
			$arg['error_line'] = __line__;
			$arg['message'] = 'no data found';
		}
		echo json_encode($arg);
	}

	public function submitQuestionnaire_post() {
		$arg = array();
		$_POST = json_decode(file_get_contents("php://input"), true);
		$userid = $_POST['userid'];
		$business_id = $_POST['business_id'];
		$class_id = $_POST['class_id'];
		$questionnaire = $_POST['questionnaire'];

		$this->form_validation->set_rules('userid', 'user id', 'required');
		$this->form_validation->set_rules('class_id', 'class id', 'required');
		$this->form_validation->set_rules('business_id', 'business id', 'required');

		if ($this->form_validation->run() == FALSE) {
			$arg['status'] = 0;
			$arg['error_code'] = 0;
			$arg['error_line'] = __line__;
			$arg['message'] = get_form_error($this->form_validation->error_array());
		} else {

			if (!empty($questionnaire)) {

				$checkfav = array('user_id' => $userid,
					'business_id' => $business_id,
					'class_id' => $class_id);
				$this->dynamic_model->deletedata('user_questionnaire', $checkfav);

				foreach ($questionnaire as $key) {
					$question_id = $key['question_id'];
					if ($question_id == '4') {
						$question_ct = count($key['question_answer']);
						if ($question_ct == '1') {
							$question_answer = @$key['question_answer'][0];
						} else {
							$question_answer = $key['question_answer'];
							$question_answer = implode(",", $question_answer);
						}

						$question_answer = $question_answer ? $question_answer : '';
					} else {
						$question_answer = $key['question_answer'];
						$question_answer = $question_answer ? $question_answer : '0';
					}

					$transaction_data = array('user_id' => $userid,
						'business_id' => $business_id,
						'class_id' => $class_id,
						'question_id' => $question_id,
						'question_ans' => $question_answer,
						'created_at' => date('Y-m-d'),
					);
					$transaction_id = $this->dynamic_model->insertdata('user_questionnaire', $transaction_data);
				}

				$arg['status'] = 1;
				$arg['error_code'] = REST_Controller::HTTP_OK;
				$arg['error_line'] = __line__;
				$arg['message'] = 'Completed Successfully'; //$this->lang->line('thank_msg1');
				$arg['data'] = '';

			} else {

				$arg['status'] = 0;
				$arg['error_code'] = 0;
				$arg['error_line'] = __line__;
				$arg['message'] = 'Error';
			}
		}

		echo json_encode($arg);

	}

	public function getUserQuestionnaire_post() {
		$arg = array();
		$_POST = json_decode(file_get_contents("php://input"), true);
		$userid = $_POST['userid'];
		$business_id = $_POST['business_id'];
		$class_id = $_POST['class_id'];

		$this->form_validation->set_rules('userid', 'user id', 'required');
		$this->form_validation->set_rules('class_id', 'class id', 'required');
		$this->form_validation->set_rules('business_id', 'business id', 'required');

		if ($this->form_validation->run() == FALSE) {
			$arg['status'] = 0;
			$arg['error_code'] = 0;
			$arg['error_line'] = __line__;
			$arg['message'] = get_form_error($this->form_validation->error_array());
		} else {

			$where = array('user_id' => $userid,
				'business_id' => $business_id,
				'class_id' => $class_id,
			);
			$result = $this->dynamic_model->getdatafromtable('user_questionnaire', $where);
			$danger_status = 0;
			if (!empty($result)) {
				foreach ($result as $key) {
					$where_question = array('id' => $key['question_id']);
					$result_question = $this->dynamic_model->getdatafromtable('manage_questionnaire', $where_question);
					if ($key['question_id'] == '4') {
						$question_answer = $key['question_ans'];
						$question_answer = @explode(',', $question_answer);
						if (!empty($question_answer)) {
							$danger_status = 1;
						}
					} else {
						$question_answer = $key['question_ans'];
						if ($question_answer == '1') {
							$danger_status = 1;
						}
					}
					$res[] = array('question_id' => $key['question_id'],
						'question_text' => $result_question[0]['question_title'],
						'question_answer' => $question_answer,
					);
				}
				$arg['status'] = 1;
				$arg['error_code'] = REST_Controller::HTTP_OK;
				$arg['error_line'] = __line__;
				$arg['message'] = 'Successfully'; //$this->lang->line('thank_msg1');
				$arg['danger_status'] = $danger_status;
				$arg['data'] = $res;
			} else {
				$arg['status'] = 0;
				$arg['error_code'] = 0;
				$arg['error_line'] = __line__;
				$arg['message'] = 'Error';
			}
		}
		echo json_encode($arg);
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
				if ($to_time < $time) {
					$update = 'update user_attendance set class_end_status = "1" WHERE id IN (' . $ids . ')';
					$this->db->query($update);
				}
			}
		}

		echo json_encode($arg);
	}

	public function pass_expire_get() {
		$query = "SELECT GROUP_CONCAT(id) as booking_id FROM `user_booking` WHERE CURRENT_DATE > DATE_FORMAT(FROM_UNIXTIME(passes_start_date), '%Y-%m-%d')";
		$info = $this->dynamic_model->getQueryRowArray($query);
		if (!empty($info)) {
			$ids = $info['booking_id'];
			if ($ids != null) {
				$update = 'update user_booking set passes_status = "0" WHERE id IN (' . $ids . ')';
				$this->db->query($update);
			}
		}
	}

	public function class_list_test_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$response = array();
						$time = time();
						$date = date("Y-m-d", $time);
						$usid = $userdata['data']['id'];
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;
						//0= all classes 1=singned class
						$class_status = $this->input->post('class_status');
						$business_id = $this->input->post('business_id');
						$upcoming_date = strtotime($this->input->post('upcoming_date'));

						/* $upcoming_dates = $upcoming_date ? $upcoming_date : date('Y-m-d');
							                    $unixTimestamp = strtotime($upcoming_dates);
							                    $week_date = date("w", $unixTimestamp);
							                    if($week_date == '0'){
							                        $week_date = 7;
							                    }

							                    $where = "business_id = ".$business_id." AND day_id = '".$week_date."'";
							                    $time_slote_data = $this->dynamic_model->getdatafromtable('class_scheduling_time',$where);
							                    if(empty($time_slote_data)){
							                        $arg['status']     = 0;
							                        $arg['error_code']  = REST_Controller::HTTP_OK;
							                        $arg['error_line']= __line__;
							                        $arg['data']       = array();
							                        $arg['message']    = $this->lang->line('record_not_found');
							                        echo json_encode($arg); die;
							                    }
						*/
						if ($class_status == 0) {
							if (!empty($upcoming_date)) {
								$date = date("Y-m-d", $upcoming_date);
								//$where="business_id=".$business_id." AND status='Active' AND start_date='".$date."'";
								$where = "business_id=" . $business_id . " AND status='Active'";
							} else {
								// $todaydate = date("Y-m-d",$time);
								// $where="business_id=".$business_id." AND status='Active' AND DATE(FROM_UNIXTIME(start_date))>='".$todaydate."'";
								$where = "business_id=" . $business_id . " AND status='Active'";
							}
							//die($where);
							$class_data = $this->dynamic_model->getdatafromtable('business_class', $where, "*", $limit, $offset, 'create_dt');

						} else {
							$class_data = $this->api_model->get_signed_classes($business_id, $upcoming_date, $limit, $offset, '', $usid);
						}
						//print_r($class_data);die;
						if (!empty($class_data)) {
							foreach ($class_data as $value) {
								//echo $upcoming_date = $upcoming_date ? $upcoming_date : date('Y-m-d');
								//$unixTimestamp = strtotime($upcoming_date);
								$week_date = date("w", $upcoming_date);
								if ($week_date == '0') {
									$week_date = 7;
								}
								$upcoming_dates = date('Y-m-d', $upcoming_date);
								$where = "business_id = " . $value['business_id'] . " AND class_id = " . $value['id'] . " AND day_id = '" . $week_date . "' AND scheduled_date = '" . $upcoming_dates . "'";

								$time_slote_data = $this->dynamic_model->getdatafromtable('class_scheduling_time', $where);
								$time_slote_from = '';
								$location_name = '';
								if (!empty($time_slote_data)) {
									// print_r($time_slote_data); die;
									$time_slote_from = $time_slote_data[0]['from_time'];
									$to_time = $time_slote_data[0]['to_time'];
									$scheduled_date = $time_slote_data[0]['scheduled_date'];
									$instructor_id_sel = $time_slote_data[0]['instructor_id'];
									$location_id = $time_slote_data[0]['location_id'];

									$where = "id = '" . $location_id . "'";

									$location_data = $this->dynamic_model->getdatafromtable('business_location', $where);
									if (!empty($location_data)) {
										$location_name = $location_data[0]['location_name'];
									}

								} else {
									continue;
								}

								$classesdata['class_id'] = $value['id'];
								$classesdata['class_name'] = ucwords($value['class_name']);
								$classesdata['from_time'] = $value['from_time'];
								$classesdata['to_time'] = $value['to_time'];
								$classesdata['from_time_utc'] = $time_slote_from;
								$classesdata['to_time_utc'] = $to_time;
								//$value['to_time'];
								$classesdata['start_date_utc'] = strtotime($scheduled_date);
								$classesdata['end_date_utc'] = strtotime($scheduled_date);

								$classesdata['duration'] = $value['duration'] . ' minutes';
								// $capicty_used                = get_checkin_class_or_workshop_count($value['id'],1,$upcoming_date);

//echo $upcoming_dates; die;
								$capicty_used = get_checkin_class_or_workshop_daily_count($value['id'], 1, $upcoming_dates);

								$classesdata['total_capacity'] = $value['capacity'];
								$classesdata['capacity_used'] = $capicty_used;
								// $capicty_used                = get_checkin_class_or_workshop_count($value['id'],1,$time);
								// $classesdata['capacity']     = $capicty_used.'/'.$value['capacity'];
								$status = get_passes_checkin_status($usid, $value['id'], 1, $date);
								if ($status == 'singup' OR $status == 'checkin' OR $status == 'checkout') {
									$signed_status = '1';
								} else {
									$signed_status = '0';
								}
								$classesdata['signed_status'] = $signed_status;
								$classesdata['signed'] = '0';
								$classesdata['location'] = $location_name;
								//$value['location'];
								$classesdata['class_type'] = get_categories($value['class_type']);
								// $instructor_data            = $this->instructor_list_details($business_id,1,$value['id']);
								$instructor_data = $this->instructor_details_get($business_id, $value['id'], $instructor_id_sel);

								$classesdata['instructor_details'] = $instructor_data;
								$classesdata['create_dt'] = date("d M Y ", $value['create_dt']);
								$classesdata['start_date'] = date("d M Y ", strtotime($value['start_date']));
								$classesdata['end_date'] = date("d M Y ", strtotime($value['end_date']));
								$classesdata['create_dt_utc'] = $value['create_dt'];
								//$classesdata['start_date_utc']=  $upcoming_date;
								//strtotime($value['start_date']);
								//$classesdata['end_date_utc']=  strtotime($value['end_date']);
								$response[] = $classesdata;
							}
							$arg['status'] = $response ? 1 : 0;
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
				}
			}
		}
		echo json_encode($arg);
	}

	public function faq_get() {

		$data = array(
			array(
				'id' => '1',
				'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut pretiu.',
				'answer' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut pretiu.',
			),
			array(
				'id' => '2',
				'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut pretiu.',
				'answer' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut pretiu.',
			),
			array(
				'id' => '3',
				'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut pretiu.',
				'answer' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut pretiu.',
			),
			array(
				'id' => '4',
				'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut pretiu.',
				'answer' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut pretiu.',
			),
			array(
				'id' => '5',
				'question' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut pretiu.',
				'answer' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut pretiu.',
			),
		);

		$arg['status'] = 1;
		$arg['error_code'] = REST_Controller::HTTP_OK;
		$arg['error_line'] = __line__;
		$arg['message'] = $this->lang->line('thank_msg1');
		$arg['data'] = $data;
		echo json_encode($arg);

	}

	public function transaction_list_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$userid = $userdata['data']['id'];
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));

					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$response = array();
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;

						$query = "SELECT user.name, user.lastname, user.email, user.mobile, user.profile_img, user.gender, user.date_of_birth, user.country_code, user.country, user.state, user.city, user.zipcode, user.address, user.location, user_booking.id, user_booking.class_id,  user_booking.service_id,  user_booking.transaction_id,  user_booking.trainer_user_id,  user_booking.amount, user_booking.sub_total, user_booking.discount, user_booking.quantity,  user_booking.tax_amount,  user_booking.payment_mode,  user_booking.payment_note,  user_booking.reference_payment_id,  user_booking.passes_start_date,  user_booking.passes_end_date, user_booking.passes_status,  user_booking.passes_total_count,  user_booking.passes_remaining_count, user_booking.create_dt as transaction_date FROM `user_booking` JOIN user on (user.id = user_booking.user_id) WHERE user_booking.user_id = " . $userid . " AND user_booking.status = 'success' ORDER BY user_booking.create_dt desc LIMIT " . $limit . " OFFSET " . $offset;
						//transaction_id
						$collection = $this->db->query($query)->result_array();
						if (!empty($collection)) {
							array_walk($collection, function (&$key) {
								$key['profile_img'] = site_url() . 'uploads/user/' . $key['profile_img'];
								if (!empty($key['transaction_id'])) {
									$key['payment_mode'] = $key['payment_mode'];
									$transaction_data = $this->db->get_where('transactions', array('id' => $key['transaction_id']))->row_array();
									if (!empty($transaction_data)) {
										$key['amount'] = $transaction_data['amount'];
										$key['trx_id'] = $transaction_data['trx_id'];
										$key['authorizing_merchant_id'] = $transaction_data['authorizing_merchant_id'];
										$key['payment_method'] = $transaction_data['payment_method'];
										$key['payment_date'] = $transaction_data['create_dt'];
									}

								} else {
									$key['payment_mode'] = 'Online';
									$key['trx_id'] = '';

								}
								unset($key['transaction_id']);
							});
						}

						if (!empty($collection)) {
							$arg['status'] = 1;
							$arg['error_code'] = HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $collection;
							$arg['message'] = $this->lang->line('record_found');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = array();
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function getInstructorShiftDate_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);

				$this->form_validation->set_rules('business_id', 'business id', 'required|numeric');
				$this->form_validation->set_rules('service_id', 'service id', 'required|numeric');
				$this->form_validation->set_rules('instructor_id', 'instructor id', 'required|numeric');
				if ($this->form_validation->run() == FALSE) {
					$arg['status'] = 0;
					$arg['error_code'] = 0;
					$arg['error_line'] = __line__;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {
					$time_zone = $this->input->get_request_header('Timezone', true);
					$time_zone = $time_zone ? $time_zone : 'UTC';
					date_default_timezone_set($time_zone);
					$currentDate = date('Y-m-d');
					$data = array();
					$response = array();
					$time = time();
					$business_id = $this->input->post('business_id');
					$service_id = $this->input->post('service_id');
					$instructor_id = $this->input->post('instructor_id');

					$query = "SELECT s.*,l.location_name,l.address,l.capacity FROM business_shift_instructor as si join business_shift as s on si.shift_id = s.id join business_location as l on l.id = s.location_id where si.instructor = '" . $instructor_id . "' AND s.business_id = '" . $business_id . "'";
					$collection = $this->dynamic_model->getQueryResultArray($query);
					if (!empty($collection)) {
						foreach ($collection as $value) {
							$shift_id = $value['id'];
							$duration = $value['duration'];
							$business_id = $value['business_id'];
							$location_name = $value['location_name'];
							$address = $value['address'];
							$capacity = $value['capacity'];
							$sql = "SELECT * FROM business_shift_scheduling as ss WHERE ss.shift_id = '" . $shift_id . "' AND ss.shift_date_str >= '".$currentDate."' GROUP by ss.shift_date ORDER BY ss.shift_date ASC";
							$scheduling_collection = $this->dynamic_model->getQueryResultArray($sql);
							if (!empty($scheduling_collection)) {
								foreach ($scheduling_collection as $key) {
									$start_time = $key['start_time'];
									$end_time = $key['end_time'];
									$shift_date = $key['shift_date'];
									$shift_date_str = $key['shift_date_str'];
									$data[] = array('shift_id' => $shift_id,
										'business_id' => $business_id,
										'location_name' => $location_name,
										'address' => $address,
										'capacity' => $capacity,
										'shift_date' => $shift_date,
										'shift_date_str' => $shift_date_str,
									);
								}
							}
						}
						$arg['status'] = 1;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['data'] = $data;
						$arg['message'] = $this->lang->line('record_found');
					} else {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = 'no appointment found';
					}
				}

			}
		}
		echo json_encode($arg);
	}
	public function service_scheduling_time_slot_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);

				$this->form_validation->set_rules('business_id', 'business id', 'required|numeric');
				$this->form_validation->set_rules('service_id', 'service id', 'required|numeric');
				$this->form_validation->set_rules('instructor_id', 'instructor id', 'required|numeric');
				$this->form_validation->set_rules('service_date', 'service date', 'required');
				if ($this->form_validation->run() == FALSE) {
					$arg['status'] = 0;
					$arg['error_code'] = 0;
					$arg['error_line'] = __line__;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {

					$time_zone = $this->input->get_request_header('Timezone', true);
					$time_zone = $time_zone ? $time_zone : 'UTC';
					date_default_timezone_set($time_zone);

					$data = array();
					$response = array();
					$time = time();
					$business_id = $this->input->post('business_id');
					$service_id = $this->input->post('service_id');
					$instructor_id = $this->input->post('instructor_id');
					$service_date = $this->input->post('service_date');
					$service_date = date('Y-m-d', $service_date);

					$query = "SELECT s.*, CASE WHEN l.location_url IS NULL THEN '' Else l.location_url END as web_link, l.location_name, CASE WHEN l.map_url IS NULL THEN '' Else l.map_url END as location_url, l.address as location_address,l.address,l.capacity FROM business_shift_instructor as si join business_shift as s on si.shift_id = s.id join business_location as l on l.id = s.location_id where si.instructor = '" . $instructor_id . "' AND s.business_id = '" . $business_id . "'";
					$collection = $this->dynamic_model->getQueryResultArray($query);

					$sql = "SELECT * FROM service as ss WHERE ss.id = '" . $service_id . "'";
					$services_collection = $this->dynamic_model->getQueryResultArray($sql);
					// print_r($services_collection); die; //getQueryRowArray

					if (!empty($services_collection)) {
						array_walk($services_collection, function (&$key) {
							//  print_r($key); die;
							$workshop_price = $key['amount'];
							$workshop_tax_price = 0;
							$tax1_rate_val = 0;
							$tax2_rate_val = 0;
							$workshop_total_price = $workshop_price;
							if (strtolower($key['tax1']) == 'yes') {
								$tax1_rate = floatVal($key['tax1_rate']);
								$tax1_rate_val = (($workshop_price * $tax1_rate) / 100);
								$workshop_tax_price = $tax1_rate_val;
								$workshop_total_price = $workshop_price + $tax1_rate_val;

							}
							if (strtolower($key['tax2']) == 'yes') {
								$tax2_rate = floatVal($key['tax2_rate']);
								$tax2_rate_val = (($workshop_price * $tax2_rate) / 100);
								$workshop_tax_price = $tax1_rate_val + $tax2_rate_val;
								$workshop_total_price = $workshop_total_price + $tax2_rate_val;
							}

							$key['tax1_rate'] = number_format($tax1_rate_val, 2);
							$key['tax2_rate'] = number_format($tax2_rate_val, 2);
							$key['service_tax_price'] = number_format($workshop_tax_price, 2);
							$key['service_total_price'] = number_format($workshop_total_price, 2);
						});
					}
					// print_r($services_collection); die;
					if (!empty($collection)) {
						foreach ($collection as $value) {

							$shift_id = $value['id'];
							$duration = $services_collection[0]['duration'];
							$time_needed = $services_collection[0]['time_needed'];
							$business_id = $value['business_id'];
							$location_name = $value['location_name'];
							$location_url = $value['location_url'];
							$address = $value['address'];
							$capacity = $value['capacity'];

							$sql = "SELECT * FROM business_shift_scheduling as ss WHERE ss.shift_id = '" . $shift_id . "' AND ss.shift_date_str = '" . $service_date . "' AND ss.status = '1'";
							$scheduling_collection = $this->dynamic_model->getQueryResultArray($sql);
							if (!empty($scheduling_collection)) {
								foreach ($scheduling_collection as $key) {
									$shift_scheduling_id = $key['id'];
									$start_time = $key['start_time'];
									$end_time = $key['end_time'];
									$shift_date = $key['shift_date'];
									$slot = $this->getShiftTimeSlote($start_time, $end_time, $shift_date, $duration, $time_needed);
									$data[] = array('shift_id' => $shift_id,
										'shift_scheduling_id' => $shift_scheduling_id,
										'business_id' => $business_id,
										'location_name' => $location_name,
										'location_url' => $location_url,
										'location_address' => $address,
										'address' => $address,
										'capacity' => $capacity,
										'duration' => $duration,
										'services_collection' => $services_collection[0],
										/* '$sql'=>$sql, */
										'slot' => $slot,
									);
								}
							}
						}
						if (!empty($data)) {
							$arg['status'] = 1;
							$arg['error_code'] = HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $data;
							$arg['message'] = $this->lang->line('record_found');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = 0;
							$arg['error_line'] = __line__;
							$arg['message'] = 'no appointment found';
						}

					} else {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = 'no appointment found';
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

/* services buy now*/
	public function buy_now_services_old_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('service_id', 'Service Id', 'required|trim', array('required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('quantity', 'Quantity', 'required', array('required' => $this->lang->line('quantity_required')));
					$this->form_validation->set_rules('grand_total', 'grand total', 'required|greater_than[0]', array(
						'required' => $this->lang->line('amount_required'),
						'numeric' => $this->lang->line('amount_valid'),
					));
					$this->form_validation->set_rules('slot_date', 'Slot Date', 'required', array('required' => $this->lang->line('date_required')));
					//$this->form_validation->set_rules('slot_time_id','Slot Id', 'required', array( 'required' => $this->lang->line('slot_id_required')));
					$this->form_validation->set_rules('token', 'Token', 'required', array('required' => $this->lang->line('token_required')));
					$this->form_validation->set_rules('instructor_id', 'Instructor', 'required', array('required' => $this->lang->line('instructor_id_required')));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$service_id = $this->input->post('service_id');
						$where = array('id' => $service_id, 'status' => 'Active');
						$product_data = $this->dynamic_model->getdatafromtable('service', $where);
						$Amt = 0;
						$usid = $userdata['data']['id'];
						$name = $userdata['data']['name'];
						$lastname = $userdata['data']['lastname'];
						$time = time();
						$pass_start_date = $pass_end_date = $pass_status = '';
						//service_type => 1 passes 2 services 3 product

						$start_time_unix = $this->input->post('start_time_unix');
						$end_time_unix = $this->input->post('end_time_unix');
						$shift_date = $this->input->post('shift_date');

						$service_type = $this->input->post('service_type');
						$quantity = $this->input->post('quantity');
						$token = $this->input->post('token');
						$grand_total = $this->input->post('grand_total');
						$grand_total = number_format((float) $grand_total, 2, '.', '');
						$slot_date = $this->input->post('slot_date');
						$slot_time_id = $this->input->post('slot_time_id');
						$savecard = $this->input->post('savecard');
						$shift_instructor = $this->input->post('instructor_id');
						$shift_id = $this->input->post('shift_id');
						$passes_total_count = 0;
						$passes_remaining_count = 0;

						$pass_start_date = 0;
						$pass_end_date = 0;
						$service = $this->db->get_where('service', array('id' => $service_id))->row_array();
						$business_id = $service['business_id'];

						$savecard = $this->input->post('savecard');
						$card_id = $this->input->post('card_id');

						$mid = getUserMarchantId($business_id);
						$marchant_id = $mid['marchant_id'];
						$marchant_id_type = $mid['marchant_id_type'];

						if (!empty($token)) {
							$payment_data = array(
								'order_number' => $time,
								'amount' => $grand_total,
								'payment_method' => 'token',
								'token' => array(
									'name' => 'Test Card',
									'code' => $token,
									'complete' => true,
								),
							);
						} else if (!empty($card_id)) {

							$where = array('user_id' => $usid, 'business_id' => $business_id);
							$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
							$customer_code = $result_card[0]['card_id'];

							$payment_data = array(
								'order_number' => $time,
								'amount' => $grand_total,
								'payment_method' => 'payment_profile',
								'payment_profile' => array(
									'customer_code' => $customer_code,
									'card_id' => $card_id,
									'complete' => true),
							);
						}

						/* start */
						$where = array('user_id' => $usid);
						$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
						if (empty($result_card) && ($savecard == '1')) {
							$legato_token_data = array(
								'language' => 'en',
								'comments' => SITE_NAME,
								'token' => array('name' => 'Test Card',
									'code' => $token),
							);
							$apiurl = 'https://api.na.bambora.com/v1/profiles';
							$responce = $this->bomborapay->profile_create('POST', $apiurl, $legato_token_data, $marchant_id);
							//echo $marchant_id;
							//print_r($responce); die;
							if ($responce['code'] == '1') {
								$transaction_data = array('user_id' => $usid,
									'card_id' => $responce['customer_code']);
								$this->dynamic_model->insertdata('user_card_save', $transaction_data);
								$customer_code = $responce['customer_code'];
							}
						} elseif (!empty($result_card) && ($savecard == '1')) {
							$customer_code = $result_card[0]['card_id'];
							$apiurl = "https://api.na.bambora.com/v1/profiles/$customer_code/cards";
							$legato_token_data = array(
								'token' => array('name' => 'Test Card',
									'code' => $token),
							);
							$responce = $this->bomborapay->profile_create('POST', $apiurl, $legato_token_data, $marchant_id);
							// echo $marchant_id;
							//print_r($responce); die;
							if ($responce['code'] == '1') {
								$customer_code = $responce['customer_code'];
							}
						}

						if ($savecard == '1') {
							$payment_data = array(
								'order_number' => $time,
								'amount' => $grand_total,
								'payment_method' => 'payment_profile',
								'payment_profile' => array(
									'customer_code' => $customer_code,
									'card_id' => $card_id,
									'complete' => true),
							);
						}
						/* end */
						$payUrl = 'https://api.na.bambora.com/v1/payments';
						//$mid = '377010002';
						/*$mid = getUserMarchantId($business_id);
	                $marchant_id = $mid['marchant_id'];
		*/
		$res = $this->bomborapay->payment_checkout('POST', $payUrl, $payment_data, $marchant_id, $marchant_id_type);
						if (@$res['approved'] == 1) {
							$where = array('user_id' => $usid);
							$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
							$ref_num = getuniquenumber();
							$payment_id = !empty(@$res['id']) ? $res['id'] : $ref_num;
							$authorizing_merchant_id = !empty(@$res['authorizing_merchant_id']) ? $res['authorizing_merchant_id'] : '';
							$payment_type = !empty(@$res['type']) ? $res['type'] : '';
							$payment_method = !empty(@$res['payment_method']) ? $res['payment_method'] : '';
							$amount = !empty(@$res['amount']) ? $res['amount'] : '';
							//Insert data in transaction table
							$transaction_data = array(
								'authorizing_merchant_id' => $authorizing_merchant_id,
								'payment_type' => $payment_type,
								'payment_method' => $payment_method,
								'user_id' => $usid,
								'amount' => $amount,
								'trx_id' => $payment_id,
								'order_number' => $time,
								'transaction_type' => ($service_type == 2) ? 3 : $service_type,
								'payment_status' => "Success",
								'saved_card_id' => 0,
								'create_dt' => $time,
								'update_dt' => $time,
								'responce_all' => json_encode($res),
							);
							$transaction_id = $this->dynamic_model->insertdata('transactions', $transaction_data);
							//after that insert into user booking table
							$sub_total = $amount * $quantity;
							$passData = array(
								'business_id' => $business_id,
								'user_id' => $usid,
								'transaction_id' => $transaction_id,
								'amount' => $amount,
								'service_type' => $service_type,
								'service_id' => $service_id,
								'quantity' => $quantity,
								'sub_total' => $sub_total,
								'status' => "Success",
								'create_dt' => $time,
								'update_dt' => $time,
								'passes_start_date' => $start_time_unix,
								'passes_end_date' => $end_time_unix,
								'shift_date' => $shift_date,
								'shift_search_date' => date('Y-m-d', $shift_date),
								'shift_instructor' => $shift_instructor,
								'shift_id' => $shift_id,
							);

							//$passData['service_slot_id'] = $slot_time_id;
							$booking_id = $this->dynamic_model->insertdata('user_booking', $passData);
							if ($service_type == 2) {
								$insert_data = array(
									"business_id" => $business_id,
									'booking_id' => $booking_id,
									"user_id" => $this->input->post('instructor_id'),
									"slot_id" => $slot_time_id,
									"service_id" => $service_id,
									"service_type" => 1,
									"slot_available_status" => "1",
									"slot_date" => $slot_date,
									'create_dt' => $time,
									'update_dt' => $time,
								);
							}

							$response = array('amount' => number_format((float) $sub_total, 2, '.', ''), 'transaction_date' => date('d M Y'));
							if ($transaction_id) {
								$arg['status'] = 1;
								$arg['error_code'] = HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_succ');
								if ($service_type == 2) {
									$arg['booking_id'] = $booking_id;
								}
								$arg['transaction_id'] = $transaction_id;
								$arg['data'] = $response;
							} else {
								$arg['status'] = 0;
								$arg['error_code'] = HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_fail');
								$arg['data'] = json_decode('{}');
							}
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['message'] = @$res['message'];
							$arg['data'] = json_decode('{}');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function buy_now_services_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('service_id', 'Service Id', 'required|trim', array('required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('transaction_id', 'Transaction Id', 'required|trim', array('required' => $this->lang->line('service_id_required')));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$service_id = $this->input->post('service_id');
						$transaction_id = $this->input->post('transaction_id');
						$where = array('id' => $service_id, 'status' => 'Active');
						$product_data = $this->dynamic_model->getdatafromtable('service', $where);
						$Amt = 0;
						$usid = $userdata['data']['id'];
						$name = $userdata['data']['name'];
						$lastname = $userdata['data']['lastname'];
						$time = time();
						$pass_start_date = $pass_end_date = $pass_status = '';
						//service_type => 1 passes 2 services 3 product

						$token = $this->input->post('token');

						$where = array('transaction_id' => $transaction_id);
						$booking_data = $this->dynamic_model->getdatafromtable('user_booking', $where);
						if (empty($booking_data)) {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['message'] = @$res['message'];
							$arg['data'] = json_decode('{}');
							echo json_encode($arg);die;
						}

						$quantity = $booking_data[0]['quantity'];
						$grand_total = $booking_data[0]['amount'];
						$grand_total = number_format((float) $grand_total, 2, '.', '');
						$savecard = $this->input->post('savecard');
						$card_id = $this->input->post('card_id');
						$passes_total_count = 0;
						$passes_remaining_count = 0;

						$pass_start_date = 0;
						$pass_end_date = 0;
						$service = $this->db->get_where('service', array('id' => $service_id))->row_array();
						$business_id = $service['business_id'];

						$mid = getUserMarchantId($business_id);
						$marchant_id = $mid['marchant_id'];
						$marchant_id_type = $mid['marchant_id_type'];

						if (!empty($token)) {
							$payment_data = array(
								'order_number' => $time,
								'amount' => $grand_total,
								'payment_method' => 'token',
								'token' => array(
									'name' => 'Test Card',
									'code' => $token,
									'complete' => true,
								),
							);
						} else if (!empty($card_id)) {

							$where = array('user_id' => $usid, 'business_id' => $business_id);
							$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
							$customer_code = $result_card[0]['card_id'];

							$payment_data = array(
								'order_number' => $time,
								'amount' => $grand_total,
								'payment_method' => 'payment_profile',
								'payment_profile' => array(
									'customer_code' => $customer_code,
									'card_id' => $card_id,
									'complete' => true),
							);
						}

						/* start */
						$where = array('user_id' => $usid, 'business_id' => $business_id);
						$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
						if (empty($result_card) && ($savecard == '1')) {
							$legato_token_data = array(
								'language' => 'en',
								'comments' => SITE_NAME,
								'token' => array('name' => 'Test Card',
									'code' => $token),
							);
							$apiurl = 'https://api.na.bambora.com/v1/profiles';
							$responce = $this->bomborapay->profile_create('POST', $apiurl, $legato_token_data, $marchant_id);
							//echo $marchant_id;
							//print_r($responce); die;
							if ($responce['code'] == '1') {
								$transaction_data = array('user_id' => $usid,
									'business_id' => $business_id,
									'card_id' => $responce['customer_code']);

								$this->dynamic_model->insertdata('user_card_save', $transaction_data);
								$customer_code = $responce['customer_code'];
							}
						} elseif (!empty($result_card) && ($savecard == '1')) {
							$customer_code = $result_card[0]['card_id'];
							$apiurl = "https://api.na.bambora.com/v1/profiles/$customer_code/cards";
							$legato_token_data = array(
								'token' => array('name' => 'Test Card',
									'code' => $token),
							);
							$responce = $this->bomborapay->profile_create('POST', $apiurl, $legato_token_data, $marchant_id);
							// echo $marchant_id;
							//print_r($responce); die;
							if ($responce['code'] == '1') {
								$customer_code = $responce['customer_code'];
							}
						}

						if ($savecard == '1') {
							$payment_data = array(
								'order_number' => $time,
								'amount' => $grand_total,
								'payment_method' => 'payment_profile',
								'payment_profile' => array(
									'customer_code' => $customer_code,
									'card_id' => $card_id,
									'complete' => true),
							);
						}
						/* end */
						$payUrl = 'https://api.na.bambora.com/v1/payments';
						$res = $this->bomborapay->payment_checkout('POST', $payUrl, $payment_data, $marchant_id, $marchant_id_type);
						if (@$res['approved'] == 1) {
							$where = array('user_id' => $usid, 'business_id' => $business_id);
							$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
							$ref_num = getuniquenumber();
							$payment_id = !empty(@$res['id']) ? $res['id'] : $ref_num;
							$authorizing_merchant_id = !empty(@$res['authorizing_merchant_id']) ? $res['authorizing_merchant_id'] : '';
							$payment_type = !empty(@$res['type']) ? $res['type'] : '';
							$payment_method = !empty(@$res['payment_method']) ? $res['payment_method'] : '';
							$amount = !empty(@$res['amount']) ? $res['amount'] : '';
							//Insert data in transaction table
							$transaction_data = array(
								'authorizing_merchant_id' => $authorizing_merchant_id,
								'payment_type' => $payment_type,
								'payment_method' => $payment_method,
								'amount' => $amount,
								'trx_id' => $payment_id,
								'order_number' => $time,
								'transaction_type' => 3,
								'payment_status' => "Success",
								'create_dt' => $time,
								'transaction_date' => date('Y-m-d'),
								'update_dt' => $time,
								'responce_all' => json_encode($res),
							);
							$where1 = array('id' => $transaction_id);
							$this->dynamic_model->updateRowWhere('transactions', $where1, $transaction_data);

							//after that insert into user booking table
							$sub_total = $amount * $quantity;
							$passData = array(
								'amount' => $amount,
								'sub_total' => $sub_total,
								'status' => "Success",
								'create_dt' => $time,
								'update_dt' => $time,
							);

							if ($this->input->post('tip_comment')) {
								$passData['tip_comment'] = $this->input->post('tip_comment');
							}

							$where1 = array('transaction_id' => $transaction_id);
							$this->dynamic_model->updateRowWhere('user_booking', $where1, $passData);

							$response = array('amount' => number_format((float) $sub_total, 2, '.', ''), 'transaction_date' => date('d M Y'));
							if ($transaction_id) {
								$arg['status'] = 1;
								$arg['error_code'] = HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_succ');
								$arg['transaction_id'] = $transaction_id;
								$arg['data'] = $response;
							} else {
								$arg['status'] = 0;
								$arg['error_code'] = HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_fail');
								$arg['data'] = json_decode('{}');
							}
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['message'] = @$res['message'];
							$arg['data'] = json_decode('{}');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function clover_buy_now_services_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('service_id', 'Service Id', 'required|trim', array('required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('transaction_id', 'Transaction Id', 'required|trim', array('required' => $this->lang->line('service_id_required')));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$service_id = $this->input->post('service_id');
						$transaction_id = $this->input->post('transaction_id');
						$where = array('id' => $service_id, 'status' => 'Active');
						$product_data = $this->dynamic_model->getdatafromtable('service', $where);
						$Amt = 0;
						$usid = $userdata['data']['id'];
						$name = $userdata['data']['name'];
						$lastname = $userdata['data']['lastname'];
						$time = time();
						$pass_start_date = $pass_end_date = $pass_status = '';
						//service_type => 1 passes 2 services 3 product

						$token = $this->input->post('token');

						$where = array('transaction_id' => $transaction_id);
						$booking_data = $this->dynamic_model->getdatafromtable('user_booking', $where);
						if (empty($booking_data)) {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['message'] = 'No record Found';
							$arg['data'] = json_decode('{}');
							echo json_encode($arg);die;
						}

						$quantity = $booking_data[0]['quantity'];
						$grand_total = $booking_data[0]['amount'];
						$grand_total = number_format((float) $grand_total, 2, '.', '');
						//$savecard = $this->input->post('savecard');
						//$card_id = $this->input->post('card_id');
						$passes_total_count = 0;
						$passes_remaining_count = 0;

						$pass_start_date = 0;
						$pass_end_date = 0;
						$service = $this->db->get_where('service', array('id' => $service_id))->row_array();
						$business_id = $service['business_id'];

						/*$mid = getUserMarchantId($business_id);
						$marchant_id = $mid['marchant_id'];
						$marchant_id_type = $mid['marchant_id_type'];

						if (!empty($token)) {
							$payment_data = array(
								'order_number' => $time,
								'amount' => $grand_total,
								'payment_method' => 'token',
								'token' => array(
									'name' => 'Test Card',
									'code' => $token,
									'complete' => true,
								),
							);
						} else if (!empty($card_id)) {

							$where = array('user_id' => $usid, 'business_id' => $business_id);
							$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
							$customer_code = $result_card[0]['card_id'];

							$payment_data = array(
								'order_number' => $time,
								'amount' => $grand_total,
								'payment_method' => 'payment_profile',
								'payment_profile' => array(
									'customer_code' => $customer_code,
									'card_id' => $card_id,
									'complete' => true),
							);
						}

						$where = array('user_id' => $usid, 'business_id' => $business_id);
						$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
						if (empty($result_card) && ($savecard == '1')) {
							$legato_token_data = array(
								'language' => 'en',
								'comments' => SITE_NAME,
								'token' => array('name' => 'Test Card',
									'code' => $token),
							);
							$apiurl = 'https://api.na.bambora.com/v1/profiles';
							$responce = $this->bomborapay->profile_create('POST', $apiurl, $legato_token_data, $marchant_id);
							//echo $marchant_id;
							//print_r($responce); die;
							if ($responce['code'] == '1') {
								$transaction_data = array('user_id' => $usid,
									'business_id' => $business_id,
									'card_id' => $responce['customer_code']);

								$this->dynamic_model->insertdata('user_card_save', $transaction_data);
								$customer_code = $responce['customer_code'];
							}
						} elseif (!empty($result_card) && ($savecard == '1')) {
							$customer_code = $result_card[0]['card_id'];
							$apiurl = "https://api.na.bambora.com/v1/profiles/$customer_code/cards";
							$legato_token_data = array(
								'token' => array('name' => 'Test Card',
									'code' => $token),
							);
							$responce = $this->bomborapay->profile_create('POST', $apiurl, $legato_token_data, $marchant_id);
							// echo $marchant_id;
							//print_r($responce); die;
							if ($responce['code'] == '1') {
								$customer_code = $responce['customer_code'];
							}
						}

						if ($savecard == '1') {
							$payment_data = array(
								'order_number' => $time,
								'amount' => $grand_total,
								'payment_method' => 'payment_profile',
								'payment_profile' => array(
									'customer_code' => $customer_code,
									'card_id' => $card_id,
									'complete' => true),
							);
						}

						$payUrl = 'https://api.na.bambora.com/v1/payments';
						$res = $this->bomborapay->payment_checkout('POST', $payUrl, $payment_data, $marchant_id, $marchant_id_type);
						if (@$res['approved'] == 1) {
							$where = array('user_id' => $usid, 'business_id' => $business_id);
							$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
							$ref_num = getuniquenumber();
							$payment_id = !empty(@$res['id']) ? $res['id'] : $ref_num;
							$authorizing_merchant_id = !empty(@$res['authorizing_merchant_id']) ? $res['authorizing_merchant_id'] : '';
							$payment_type = !empty(@$res['type']) ? $res['type'] : '';
							$payment_method = !empty(@$res['payment_method']) ? $res['payment_method'] : '';
							$amount = !empty(@$res['amount']) ? $res['amount'] : '';
						*/

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
								'amount' => $amount,
								'trx_id' => $payment_id,
								'order_number' => $time,
								'transaction_type' => 3,
								'payment_status' => "Success",
								'create_dt' => $time,
								'transaction_date' => date('Y-m-d'),
								'update_dt' => $time,
								'responce_all' => json_encode($res),
							);
							$where1 = array('id' => $transaction_id);
							$this->dynamic_model->updateRowWhere('transactions', $where1, $transaction_data);

							//after that insert into user booking table
							$sub_total = $amount * $quantity;
							$passData = array(
								'amount' => $amount,
								'sub_total' => $sub_total,
								'status' => "Success",
								'create_dt' => $time,
								'update_dt' => $time,
							);

							if ($this->input->post('tip_comment')) {
								$passData['tip_comment'] = $this->input->post('tip_comment');
							}

							$where1 = array('transaction_id' => $transaction_id);
							$this->dynamic_model->updateRowWhere('user_booking', $where1, $passData);

							$response = array('amount' => number_format((float) $sub_total, 2, '.', ''), 'transaction_date' => date('d M Y'));
							if ($transaction_id) {
								$arg['status'] = 1;
								$arg['error_code'] = HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_succ');
								$arg['transaction_id'] = $transaction_id;
								$arg['data'] = $response;
							} else {
								$arg['status'] = 0;
								$arg['error_code'] = HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_fail');
								$arg['data'] = json_decode('{}');
							}
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['message'] = @$res->error->message;
							$arg['data'] = json_decode('{}');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}

	/* book services with ou payment */

	public function my_book_services_list_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$userid = $userdata['data']['id'];
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));

					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$response = array();
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$transaction_id = (!empty($this->input->post('transaction_id'))) ? $this->input->post('transaction_id') : "";

						$business_id = ($this->input->post('business_id')) ? $this->input->post('business_id') : 0;

						$time_zone = $this->input->get_request_header('Timezone', true);
						$time_zone = $time_zone ? $time_zone : 'UTC';
						date_default_timezone_set($time_zone);
						$current = date('Y-m-d');
						$start_dt = (!empty($this->input->post('search_dt'))) ? $this->input->post('search_dt') : "";

						$end_dt = $start_dt;

						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;
						$imgePath = base_url() . 'uploads/user/';
						//tip
						$query = "SELECT t.create_dt, IFNULL(uf.member_name,'') as family_member_name, IFNULL(uf.gender,'') as family_gender, IFNULL(uf.dob,'') as family_dob,(CASE WHEN uf.photo != '' THEN CONCAT('" . $imgePath . "',uf.photo) ELSE '' END ) as family_profile_img, b.family_user_id,s.id as service_id, t.id as transaction_id,t.user_id,t.amount,t.discount,t.trx_id,t.order_number,t.create_dt as payment_date,t.payment_type,t.payment_method,t.responce_all,u.name,u.lastname, u.gender, u.date_of_birth,concat('" . $imgePath . "', u.profile_img) as profile_img, s.service_name,s.duration,s.cancel_policy,s.description, s.tax1, s.tax2, s.tax1_rate, s.amount as service_amount, s.tip_option, s.tax2_rate,bs.business_name,bs.address,bs.location_detail,uu.name as instructor_name,uu.lastname as instructor_lastname,concat('" . $imgePath . "', uu.profile_img) as instructor_profile_img, b.status as booking_status,b.passes_start_date as start_time, b.passes_end_date as end_time, b.shift_date, b.tip_comment, bl.location_name, CASE WHEN bl.map_url IS NULL THEN '' Else bl.map_url END as location_url, CASE WHEN bl.location_url IS NULL THEN '' Else bl.location_url END as web_link, bl.address as location_address  FROM transactions AS t join user_booking as b on t.id = b.transaction_id JOIN user as u on u.id = b.user_id join service as s on s.id = b.service_id JOIN business as bs on bs.id = b.business_id JOIN user as uu on uu.id = b.shift_instructor LEFT Join business_shift as bshift on bshift.id = b.shift_id left join business_location as bl on bl.id = bshift.location_id left join user_family_details as uf on uf.id = b.family_user_id WHERE b.user_id = " . $userid . " AND b.service_type = '2' ";

						if (!empty($transaction_id)) {
							$query .= " AND b.status = 'Success' AND t.id = '" . $transaction_id . "'";
						}

						if (!empty($business_id)) {
							$query .= " AND b.status != 'Success' AND b.business_id = '" . $business_id . "'";
						}

						if (!empty($start_dt)) {
							$start_dt = date('Y-m-d', $start_dt);
							$query .= " AND b.shift_search_date = '" . $start_dt . "'";
							$query .= " ORDER BY b.shift_date desc LIMIT " . $limit . ' OFFSET ' . $offset;

						} else if (isset($_POST['transaction_status'])) {
							$transaction_status = $this->input->post('transaction_status');
							if ($transaction_status == '0') {
								$query .= " AND b.shift_search_date >= '" . $current . "' ";
								$query .= " ORDER BY b.passes_start_date asc LIMIT " . $limit . ' OFFSET ' . $offset;
							} else {
								$query .= " AND b.shift_search_date < '" . $current . "' ";
								$query .= " ORDER BY b.passes_start_date desc LIMIT " . $limit . ' OFFSET ' . $offset;
								//b.shift_date
							}
						} else {
							$query .= " ORDER BY b.shift_date desc LIMIT " . $limit . ' OFFSET ' . $offset;
						}

						// $query .= " ORDER BY b.create_dt desc";
						$collection = $this->db->query($query)->result_array();
						// echo $this->db->last_query(); die;

						if (!empty($collection)) {
							array_walk($collection, function (&$key) {
								$workshop_price = $key['service_amount'];
								$workshop_tax_price = 0;
								$tax1_rate_val = 0;
								$tax2_rate_val = 0;
								$workshop_total_price = $workshop_price;
								if (strtolower($key['tax1']) == 'yes') {
									$tax1_rate = floatVal($key['tax1_rate']);
									$tax1_rate_val = (($workshop_price * $tax1_rate) / 100);
									$workshop_tax_price = $tax1_rate_val;
									$workshop_total_price = $workshop_price + $tax1_rate_val;

								}
								if (strtolower($key['tax2']) == 'yes') {
									$tax2_rate = floatVal($key['tax2_rate']);
									$tax2_rate_val = (($workshop_price * $tax2_rate) / 100);
									$workshop_tax_price = $tax1_rate_val + $tax2_rate_val;
									$workshop_total_price = $workshop_total_price + $tax2_rate_val;
								}

								$key['tax1_rate'] = number_format($tax1_rate_val, 2);
								$key['tax2_rate'] = number_format($tax2_rate_val, 2);
								$key['service_tax_price'] = number_format($workshop_tax_price, 2);
								$key['service_total_price'] = number_format($workshop_total_price, 2);
							});
						}

						if (!empty($collection)) {
							$arg['status'] = 1;
							$arg['error_code'] = HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = $collection;
							$arg['message'] = $this->lang->line('record_found');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = array();
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function book_services_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('service_id', 'Service Id', 'required|trim', array('required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('quantity', 'Quantity', 'required', array('required' => $this->lang->line('quantity_required')));
					$this->form_validation->set_rules('grand_total', 'grand total', 'required', array(
						'required' => $this->lang->line('amount_required'),
						'numeric' => $this->lang->line('amount_valid'),
					));
					$this->form_validation->set_rules('slot_date', 'Slot Date', 'required', array('required' => $this->lang->line('date_required')));
					$this->form_validation->set_rules('instructor_id', 'Instructor', 'required', array('required' => $this->lang->line('instructor_id_required')));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$service_id = $this->input->post('service_id');
						$where = array('id' => $service_id, 'status' => 'Active');
						$product_data = $this->dynamic_model->getdatafromtable('service', $where);
						$Amt = 0;
						$usid = $userdata['data']['id'];
						$name = $userdata['data']['name'];
						$lastname = $userdata['data']['lastname'];
						$time = time();
						$pass_start_date = $pass_end_date = $pass_status = '';
						//service_type => 1 passes 2 services 3 product

						$shift_schedule_id = $this->input->post('shift_scheduling_id');
						$shift_schedule_id = $shift_schedule_id ? $shift_schedule_id : '0';

						$start_time_unix = $this->input->post('start_time_unix');
						$end_time_unix = $this->input->post('end_time_unix');
						$shift_date = $this->input->post('shift_date');

						$service_type = $this->input->post('service_type');
						$quantity = $this->input->post('quantity');
						$token = $this->input->post('token');
						$transactions_tax = $this->input->post('tax');
						$transactions_tax = $transactions_tax ? $transactions_tax : 0;
						$grand_total = $this->input->post('grand_total');
						$grand_total = number_format((float) $grand_total, 2, '.', '');
						$slot_date = $this->input->post('slot_date');
						$slot_time_id = $this->input->post('slot_time_id');
						$savecard = $this->input->post('savecard');
						$shift_instructor = $this->input->post('instructor_id');
						$shift_id = $this->input->post('shift_id');
						$family_user_id = $this->input->post('family_user_id');
						$family_user_id = $family_user_id ? $family_user_id : 0;
						$passes_total_count = 0;
						$passes_remaining_count = 0;

						$pass_start_date = 0;
						$pass_end_date = 0;
						$service = $this->db->get_where('service', array('id' => $service_id))->row_array();
						$business_id = $service['business_id'];

						$where = array('user_id' => $usid);
						$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
						$ref_num = getuniquenumber();
						$payment_id = '';
						$authorizing_merchant_id = '';
						$payment_type = '';
						$payment_method = '';
						$amount = $grand_total;

						//Insert data in transaction table
						$transaction_data = array(
							'authorizing_merchant_id' => $authorizing_merchant_id,
							'payment_type' => $payment_type,
							'payment_method' => $payment_method,
							'user_id' => $usid,
							'amount' => $amount,
							'transactions_tax' => $transactions_tax,
							'trx_id' => $payment_id,
							'order_number' => $time,
							'transaction_type' => ($service_type == 2) ? 3 : $service_type,
							'payment_status' => "Confirm",
							'saved_card_id' => 0,
							'create_dt' => $time,
							'transaction_date' => date('Y-m-d'),
							'update_dt' => $time,
						);
						$transaction_id = $this->dynamic_model->insertdata('transactions', $transaction_data);
						//after that insert into user booking table
						//echo $amount.'--'.$quantity; die;
						$sub_total = $amount * $quantity;
						$passData = array(
							'business_id' => $business_id,
							'user_id' => $usid,
							'transaction_id' => $transaction_id,
							'amount' => $amount,
							'service_type' => $service_type,
							'service_id' => $service_id,
							'quantity' => $quantity,
							'sub_total' => $sub_total,
							'status' => "Confirm",
							'create_dt' => $time,
							'update_dt' => $time,
							'passes_start_date' => $start_time_unix,
							'passes_end_date' => $end_time_unix,
							'shift_date' => $shift_date,
							'shift_search_date' => date('Y-m-d', $shift_date),
							'shift_instructor' => $shift_instructor,
							'shift_id' => $shift_id,
							'shift_schedule_id' => $shift_schedule_id,
							'family_user_id' => $family_user_id,
						);
						//$passData['service_slot_id'] = $slot_time_id;
						$booking_id = $this->dynamic_model->insertdata('user_booking', $passData);
						if ($service_type == 2) {
							$insert_data = array(
								"business_id" => $business_id,
								'booking_id' => $booking_id,
								"user_id" => $this->input->post('instructor_id'),
								"slot_id" => $slot_time_id,
								"service_id" => $service_id,
								"service_type" => 1,
								"slot_available_status" => "1",
								"slot_date" => $slot_date,
								'create_dt' => $time,
								'update_dt' => $time,
							);
						}
						$response = array('amount' => number_format((float) $sub_total, 2, '.', ''), 'transaction_date' => date('d M Y'));
						if ($transaction_id) {
							$arg['status'] = 1;
							$arg['error_code'] = HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('book_services');
							if ($service_type == 2) {
								$arg['booking_id'] = $booking_id;
							}
							$arg['transaction_id'] = $transaction_id;
							$arg['data'] = $response;
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('payment_fail');
							$arg['data'] = json_decode('{}');
						}

					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function service_status_change_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('transaction_id', 'Transaction Id', 'required|trim', array('required' => 'Transaction id is required'));
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$user_id = $userdata['data']['id'];
						$business_id = $this->input->post('business_id');
						$transaction_id = $this->input->post('transaction_id');

						$booking_status = $this->dynamic_model->updateRowWhere('user_booking', array('transaction_id' => $transaction_id, 'business_id' => $business_id, 'user_id' => $user_id), array('status' => 'Completed'));

						if ($booking_status) {
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['message'] = 'Status changed successfully';
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('server_problem');
						}

					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function service_appointment_cancel_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('transaction_id', 'Transaction Id', 'required|trim', array('required' => 'Transaction id is required'));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$user_id = $userdata['data']['id'];
						// $business_id    =  $this->input->post('business_id');
						$transaction_id = $this->input->post('transaction_id');

						$booking_status = $this->dynamic_model->updateRowWhere('user_booking', array('transaction_id' => $transaction_id), array('status' => 'Cancel'));

						if ($booking_status) {
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['message'] = 'Appointment cancel successfully';
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('server_problem');
						}

					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function business_workshop_details_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('business_id', 'Business_id Id', 'required|trim', array('required' => 'Business_id id is required'));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$user_id = $userdata['data']['id'];
						$business_id = $this->input->post('business_id');
						$workshop_id = $this->input->post('workshop_id');

						if ($this->input->post('schedule_id')) {
							$schedule_id = $this->input->post('schedule_id');
							$workshop_result = get_all_workshop_schedule($business_id, $workshop_id, $schedule_id, $user_id);
						} else {
							$workshop_result = get_all_workshop($business_id, $workshop_id);
						}

						if ($workshop_result) {
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['message'] = '';
							$arg['data'] = $workshop_result;
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['message'] = 'Not found workshop.';
						}

					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function buy_now_workshop_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('business_id', 'Business_id Id', 'required|trim', array('required' => 'Business_id id is required'));
					$this->form_validation->set_rules('workshop_id', 'Workshop Id', 'required|trim', array('required' => 'Workshop id is required.'));
					$this->form_validation->set_rules('workshop_schdule_id', 'Workshop Schdule Id', 'required|trim', array('required' => 'Workshop Schdule id is required.'));
					//$this->form_validation->set_rules('quantity','Quantity', 'required', array( 'required' => $this->lang->line('quantity_required')));
					$this->form_validation->set_rules('grand_total', 'grand total', 'required|greater_than[0]', array(
						'required' => $this->lang->line('amount_required'),
						'numeric' => $this->lang->line('amount_valid'),
					));
					//$this->form_validation->set_rules('slot_date','Slot Date', 'required', array( 'required' => $this->lang->line('date_required')));
					$this->form_validation->set_rules('token', 'Token', 'required', array('required' => $this->lang->line('token_required')));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$business_id = $this->input->post('business_id');
						$transactions_tax = $this->input->post('tax');
						$transactions_tax = $transactions_tax ? $transactions_tax : 0;

						$workshop_id = $this->input->post('workshop_id');
						$workshop_schdule_id = $this->input->post('workshop_schdule_id');
						$where = array('id' => $workshop_id);
						$product_data = $this->dynamic_model->getdatafromtable('business_workshop_master', $where);
						$workshop_capacity = $product_data[0]['workshop_capacity'];

						$user_booking_data = $this->db->get_where('user_booking', array(
							'service_type' => '4',
							'service_id' => $workshop_id,
							'status' => 'Success',
						))->num_rows();
						if ($workshop_capacity <= $user_booking_data) {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['message'] = 'No capicty available.';
							echo json_encode($arg);die;
						}

						$where = array('id' => $workshop_schdule_id);
						$schdule_data = $this->dynamic_model->getdatafromtable('business_workshop_schdule', $where);
						$start_time_unix = $schdule_data[0]['start'];
						$end_time_unix = $schdule_data[0]['end'];

						$Amt = 0;
						$usid = $userdata['data']['id'];
						$name = $userdata['data']['name'];
						$lastname = $userdata['data']['lastname'];
						$time = time();
						$pass_start_date = $pass_end_date = $pass_status = '';
						//service_type => 1 passes 2 services 3 product
						$service_type = 4;
						$quantity = 1;
						$token = $this->input->post('token');
						$grand_total = $this->input->post('grand_total');
						$grand_total = number_format((float) $grand_total, 2, '.', '');

						$savecard = $this->input->post('savecard');
						$passes_total_count = 0;
						$passes_remaining_count = 0;

						$pass_start_date = 0;
						$pass_end_date = 0;

						$savecard = $this->input->post('savecard');
						$card_id = $this->input->post('card_id');

						$mid = getUserMarchantId($business_id);
						$marchant_id = $mid['marchant_id'];
						$marchant_id_type = $mid['marchant_id_type'];

						if (!empty($token)) {
							$payment_data = array(
								'order_number' => $time,
								'amount' => $grand_total,
								'payment_method' => 'token',
								'token' => array(
									'name' => 'Test Card',
									'code' => $token,
									'complete' => true,
								),
							);
						} else if (!empty($card_id)) {

							$where = array('user_id' => $usid, 'business_id' => $business_id);
							$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
							$customer_code = $result_card[0]['card_id'];

							$payment_data = array(
								'order_number' => $time,
								'amount' => $grand_total,
								'payment_method' => 'payment_profile',
								'payment_profile' => array(
									'customer_code' => $customer_code,
									'card_id' => $card_id,
									'complete' => true),
							);
						}

						/* start */
						$where = array('user_id' => $usid);
						$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
						if (empty($result_card) && ($savecard == '1')) {
							$legato_token_data = array(
								'language' => 'en',
								'comments' => SITE_NAME,
								'token' => array('name' => 'Test Card',
									'code' => $token),
							);
							$apiurl = 'https://api.na.bambora.com/v1/profiles';
							$responce = $this->bomborapay->profile_create('POST', $apiurl, $legato_token_data, $marchant_id);
							//echo $marchant_id;
							//print_r($responce); die;
							if ($responce['code'] == '1') {
								$transaction_data = array('user_id' => $usid,
									'card_id' => $responce['customer_code']);
								$this->dynamic_model->insertdata('user_card_save', $transaction_data);
								$customer_code = $responce['customer_code'];
							}
						} elseif (!empty($result_card) && ($savecard == '1')) {
							$customer_code = $result_card[0]['card_id'];
							$apiurl = "https://api.na.bambora.com/v1/profiles/$customer_code/cards";
							$legato_token_data = array(
								'token' => array('name' => 'Test Card',
									'code' => $token),
							);
							$responce = $this->bomborapay->profile_create('POST', $apiurl, $legato_token_data, $marchant_id);
							// echo $marchant_id;
							//print_r($responce); die;
							if ($responce['code'] == '1') {
								$customer_code = $responce['customer_code'];
							}
						}

						if ($savecard == '1') {
							$payment_data = array(
								'order_number' => $time,
								'amount' => $grand_total,
								'payment_method' => 'payment_profile',
								'payment_profile' => array(
									'customer_code' => $customer_code,
									'card_id' => $card_id,
									'complete' => true),
							);
						}
						/* end */
						$payUrl = 'https://api.na.bambora.com/v1/payments';
						$res = $this->bomborapay->payment_checkout('POST', $payUrl, $payment_data, $marchant_id, $marchant_id_type);
						if (@$res['approved'] == 1) {
							$where = array('user_id' => $usid);
							$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
							$ref_num = getuniquenumber();
							$payment_id = !empty(@$res['id']) ? $res['id'] : $ref_num;
							$authorizing_merchant_id = !empty(@$res['authorizing_merchant_id']) ? $res['authorizing_merchant_id'] : '';
							$payment_type = !empty(@$res['type']) ? $res['type'] : '';
							$payment_method = !empty(@$res['payment_method']) ? $res['payment_method'] : '';
							$amount = !empty(@$res['amount']) ? $res['amount'] : '';
							//Insert data in transaction table
							$transaction_data = array(
								'authorizing_merchant_id' => $authorizing_merchant_id,
								'payment_type' => $payment_type,
								'payment_method' => $payment_method,
								'user_id' => $usid,
								'amount' => $amount,
								'transactions_tax' => $transactions_tax,
								'trx_id' => $payment_id,
								'order_number' => $time,
								'transaction_type' => 4,
								'payment_status' => "Success",
								'saved_card_id' => 0,
								'create_dt' => $time,
								'update_dt' => $time,
								'responce_all' => json_encode($res),
							);
							$transaction_id = $this->dynamic_model->insertdata('transactions', $transaction_data);
							//after that insert into user booking table
							$sub_total = $amount * $quantity;
							$passData = array(
								'business_id' => $business_id,
								'user_id' => $usid,
								'transaction_id' => $transaction_id,
								'amount' => $amount,
								'service_type' => $service_type,
								'service_id' => $workshop_id,
								'class_id' => $workshop_schdule_id,
								'quantity' => $quantity,
								'sub_total' => $sub_total,
								'status' => "Success",
								'create_dt' => $time,
								'update_dt' => $time,
								'passes_start_date' => $start_time_unix,
								'passes_end_date' => $end_time_unix,
							);

							$booking_id = $this->dynamic_model->insertdata('user_booking', $passData);

							$response = array('amount' => number_format((float) $sub_total, 2, '.', ''), 'transaction_date' => date('d M Y'));
							if ($transaction_id) {
								$arg['status'] = 1;
								$arg['error_code'] = HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_succ');
								$arg['transaction_id'] = $transaction_id;
								$arg['data'] = $response;
							} else {
								$arg['status'] = 0;
								$arg['error_code'] = HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_fail');
								$arg['data'] = json_decode('{}');
							}
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['message'] = @$res['message'];
							$arg['data'] = json_decode('{}');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function clover_buy_now_workshop_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('business_id', 'Business_id Id', 'required|trim', array('required' => 'Business_id id is required'));
					$this->form_validation->set_rules('workshop_id', 'Workshop Id', 'required|trim', array('required' => 'Workshop id is required.'));
					$this->form_validation->set_rules('workshop_schdule_id', 'Workshop Schdule Id', 'required|trim', array('required' => 'Workshop Schdule id is required.'));
					//$this->form_validation->set_rules('quantity','Quantity', 'required', array( 'required' => $this->lang->line('quantity_required')));
					$this->form_validation->set_rules('grand_total', 'grand total', 'required|greater_than[0]', array(
						'required' => $this->lang->line('amount_required'),
						'numeric' => $this->lang->line('amount_valid'),
					));
					//$this->form_validation->set_rules('slot_date','Slot Date', 'required', array( 'required' => $this->lang->line('date_required')));
					$this->form_validation->set_rules('token', 'Token', 'required', array('required' => $this->lang->line('token_required')));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$business_id = $this->input->post('business_id');
						$transactions_tax = $this->input->post('tax');
						$transactions_tax = $transactions_tax ? $transactions_tax : 0;

						$workshop_id = $this->input->post('workshop_id');
						$workshop_schdule_id = $this->input->post('workshop_schdule_id');
						$where = array('id' => $workshop_id);
						$product_data = $this->dynamic_model->getdatafromtable('business_workshop_master', $where);
						$workshop_capacity = $product_data[0]['workshop_capacity'];

						$user_booking_data = $this->db->get_where('user_booking', array(
							'service_type' => '4',
							'service_id' => $workshop_id,
							'status' => 'Success',
						))->num_rows();
						if ($workshop_capacity <= $user_booking_data) {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['message'] = 'No capicty available.';
							echo json_encode($arg);die;
						}

						$where = array('id' => $workshop_schdule_id);
						$schdule_data = $this->dynamic_model->getdatafromtable('business_workshop_schdule', $where);
						$start_time_unix = $schdule_data[0]['start'];
						$end_time_unix = $schdule_data[0]['end'];

						$Amt = 0;
						$usid = $userdata['data']['id'];
						$name = $userdata['data']['name'];
						$lastname = $userdata['data']['lastname'];
						$time = time();
						$pass_start_date = $pass_end_date = $pass_status = '';
						//service_type => 1 passes 2 services 3 product
						$service_type = 4;
						$quantity = 1;
						$token = $this->input->post('token');
						$grand_total = $this->input->post('grand_total');
						$grand_total = number_format((float) $grand_total, 2, '.', '');

						//$savecard = $this->input->post('savecard');
						$passes_total_count = 0;
						$passes_remaining_count = 0;

						$pass_start_date = 0;
						$pass_end_date = 0;

						/*$savecard = $this->input->post('savecard');
						$card_id = $this->input->post('card_id');

						$mid = getUserMarchantId($business_id);
						$marchant_id = $mid['marchant_id'];
						$marchant_id_type = $mid['marchant_id_type'];

						if (!empty($token)) {
							$payment_data = array(
								'order_number' => $time,
								'amount' => $grand_total,
								'payment_method' => 'token',
								'token' => array(
									'name' => 'Test Card',
									'code' => $token,
									'complete' => true,
								),
							);
						} else if (!empty($card_id)) {

							$where = array('user_id' => $usid, 'business_id' => $business_id);
							$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
							$customer_code = $result_card[0]['card_id'];

							$payment_data = array(
								'order_number' => $time,
								'amount' => $grand_total,
								'payment_method' => 'payment_profile',
								'payment_profile' => array(
									'customer_code' => $customer_code,
									'card_id' => $card_id,
									'complete' => true),
							);
						}

						$where = array('user_id' => $usid);
						$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
						if (empty($result_card) && ($savecard == '1')) {
							$legato_token_data = array(
								'language' => 'en',
								'comments' => SITE_NAME,
								'token' => array('name' => 'Test Card',
									'code' => $token),
							);
							$apiurl = 'https://api.na.bambora.com/v1/profiles';
							$responce = $this->bomborapay->profile_create('POST', $apiurl, $legato_token_data, $marchant_id);
							//echo $marchant_id;
							//print_r($responce); die;
							if ($responce['code'] == '1') {
								$transaction_data = array('user_id' => $usid,
									'card_id' => $responce['customer_code']);
								$this->dynamic_model->insertdata('user_card_save', $transaction_data);
								$customer_code = $responce['customer_code'];
							}
						} elseif (!empty($result_card) && ($savecard == '1')) {
							$customer_code = $result_card[0]['card_id'];
							$apiurl = "https://api.na.bambora.com/v1/profiles/$customer_code/cards";
							$legato_token_data = array(
								'token' => array('name' => 'Test Card',
									'code' => $token),
							);
							$responce = $this->bomborapay->profile_create('POST', $apiurl, $legato_token_data, $marchant_id);
							// echo $marchant_id;
							//print_r($responce); die;
							if ($responce['code'] == '1') {
								$customer_code = $responce['customer_code'];
							}
						}

						if ($savecard == '1') {
							$payment_data = array(
								'order_number' => $time,
								'amount' => $grand_total,
								'payment_method' => 'payment_profile',
								'payment_profile' => array(
									'customer_code' => $customer_code,
									'card_id' => $card_id,
									'complete' => true),
							);
						}

						$payUrl = 'https://api.na.bambora.com/v1/payments';
						$res = $this->bomborapay->payment_checkout('POST', $payUrl, $payment_data, $marchant_id, $marchant_id_type);
						if (@$res['approved'] == 1) {
							$where = array('user_id' => $usid);
							$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
							$ref_num = getuniquenumber();
							$payment_id = !empty(@$res['id']) ? $res['id'] : $ref_num;
							$authorizing_merchant_id = !empty(@$res['authorizing_merchant_id']) ? $res['authorizing_merchant_id'] : '';
							$payment_type = !empty(@$res['type']) ? $res['type'] : '';
							$payment_method = !empty(@$res['payment_method']) ? $res['payment_method'] : '';
							$amount = !empty(@$res['amount']) ? $res['amount'] : '';
						*/
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
								'user_id' => $usid,
								'amount' => $amount,
								'transactions_tax' => $transactions_tax,
								'trx_id' => $payment_id,
								'order_number' => $time,
								'transaction_type' => 4,
								'payment_status' => "Success",
								'saved_card_id' => 0,
								'create_dt' => $time,
								'update_dt' => $time,
								'responce_all' => json_encode($res),
							);
							$transaction_id = $this->dynamic_model->insertdata('transactions', $transaction_data);
							//after that insert into user booking table
							$sub_total = $amount * $quantity;
							$passData = array(
								'business_id' => $business_id,
								'user_id' => $usid,
								'transaction_id' => $transaction_id,
								'amount' => $amount,
								'service_type' => $service_type,
								'service_id' => $workshop_id,
								'class_id' => $workshop_schdule_id,
								'quantity' => $quantity,
								'sub_total' => $sub_total,
								'status' => "Success",
								'create_dt' => $time,
								'update_dt' => $time,
								'passes_start_date' => $start_time_unix,
								'passes_end_date' => $end_time_unix,
							);

							$booking_id = $this->dynamic_model->insertdata('user_booking', $passData);

							$response = array('amount' => number_format((float) $sub_total, 2, '.', ''), 'transaction_date' => date('d M Y'));
							if ($transaction_id) {
								$arg['status'] = 1;
								$arg['error_code'] = HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_succ');
								$arg['transaction_id'] = $transaction_id;
								$arg['data'] = $response;
							} else {
								$arg['status'] = 0;
								$arg['error_code'] = HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_fail');
								$arg['data'] = json_decode('{}');
							}
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['message'] = @$res->error->message;
							$arg['data'] = json_decode('{}');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function workshop_appointment_cancel_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('transaction_id', 'Transaction Id', 'required|trim', array('required' => 'Transaction id is required'));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$user_id = $userdata['data']['id'];
						// $business_id    =  $this->input->post('business_id');
						$transaction_id = $this->input->post('transaction_id');

						$booking_status = $this->dynamic_model->updateRowWhere('user_booking', array('transaction_id' => $transaction_id), array('status' => 'Cancel'));

						if ($booking_status) {
							$arg['status'] = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['message'] = 'workshop cancel successfully';
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('server_problem');
						}

					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function my_book_workshop_list_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$userid = $userdata['data']['id'];
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('pageid', 'Page No', 'required|numeric', array(
						'required' => $this->lang->line('page_no'),
						'numeric' => $this->lang->line('page_no_numeric'),
					));

					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$response = array();
						$page_no = (!empty($this->input->post('pageid'))) ? $this->input->post('pageid') : "1";
						$transaction_id = (!empty($this->input->post('transaction_id'))) ? $this->input->post('transaction_id') : "";

						$start_dt = (!empty($this->input->post('search_dt'))) ? $this->input->post('search_dt') : "";

						$business_id = ($this->input->post('business_id')) ? $this->input->post('business_id') : 0;

						$end_dt = $start_dt;

						$page_no = $page_no - 1;
						$limit = config_item('page_data_limit');
						$offset = $limit * $page_no;
						$imgePath = base_url() . 'uploads/user/';
						$time_zone = $this->input->get_request_header('Timezone', true);
						$time_zone = $time_zone ? $time_zone : 'UTC';
						date_default_timezone_set($time_zone);
						$current = date('Y-m-d');

						/* $query = "SELECT (SELECT ws.id FROM business_workshop_schdule as ws WHERE ws.workshop_id = wm.id group by ws.workshop_id ) as id,(SELECT ws.schedule_date FROM business_workshop_schdule as ws WHERE ws.workshop_id = wm.id group by ws.workshop_id ) as schedule_date, (SELECT ws.schedule_dates FROM business_workshop_schdule as ws WHERE ws.workshop_id = wm.id group by ws.workshop_id ) as schedule_dates, (SELECT ws.start FROM business_workshop_schdule as ws WHERE ws.workshop_id = wm.id group by ws.workshop_id ) as start, (SELECT ws.end FROM business_workshop_schdule as ws WHERE ws.workshop_id = wm.id group by ws.workshop_id ) as end, CASE WHEN bws.address IS NULL THEN '' Else bws.address END as address_id, t.create_dt, b.family_user_id,t.id as transaction_id,t.user_id,t.amount,t.discount,t.trx_id,t.order_number,t.create_dt as payment_date,t.payment_type,t.payment_method,t.responce_all,u.name,u.lastname, u.gender, u.date_of_birth,concat('" . $imgePath . "', u.profile_img) as profile_img, bs.business_name,bs.address,bs.location_detail, b.status as booking_status,b.passes_start_date as start_time, b.passes_end_date as end_time, b.tip_comment, wm.name as workshop_name, wm.description as workshop_description, wm.price as workshop_price, wm.tax1, wm.tax1_rate, wm.tax2, wm.tax2_rate,wm.workshop_capacity as capacity, wm.workshop_capacity as total_capacity, wm.id as workshop_id, bl.location_name as location, (CASE WHEN bl.map_url IS NULL THEN '' Else bl.map_url END) as location_url, (CASE WHEN bl.location_url IS NULL THEN '' Else bl.location_url END) as web_link  FROM transactions AS t join user_booking as b on t.id = b.transaction_id JOIN user as u on u.id = b.user_id join business_workshop_master as wm on wm.id = b.service_id JOIN business_workshop_schdule as bws on bws.id = b.service_id LEFT JOIN business_location as bl on bl.id = bws.location  JOIN business as bs on bs.id = b.business_id  WHERE b.user_id = " . $userid . " AND b.service_type = '4' "; */

						$query = "SELECT bws.id, bws.start as start_time, bws.end as end_time, bws.start, bws.end, bws.schedule_date, bws.schedule_dates, CASE WHEN bws.address IS NULL THEN '' Else bws.address END as address_id, t.create_dt, b.family_user_id,t.id as transaction_id,t.user_id,t.amount,t.discount,t.trx_id,t.order_number,t.create_dt as payment_date,t.payment_type,t.payment_method,t.responce_all,u.name,u.lastname, u.gender, u.date_of_birth, concat('" . $imgePath . "', u.profile_img) as profile_img, bs.business_name,bs.address,bs.location_detail, b.status as booking_status, b.tip_comment, bwm.name as workshop_name, bwm.description as workshop_description, bwm.price as workshop_price, bwm.tax1, bwm.tax1_rate, bwm.tax2, bwm.tax2_rate,bwm.workshop_capacity as capacity, bwm.workshop_capacity as total_capacity, bwm.id as workshop_id, bl.location_name as location, (CASE WHEN bl.map_url IS NULL THEN '' Else bl.map_url END) as location_url, (CASE WHEN bl.location_url IS NULL THEN '' Else bl.location_url END) as web_link  FROM business_workshop_schdule as bws JOIN business_workshop_master as bwm on (bwm.id = bws.workshop_id) JOIN user_booking as b on (b.service_id = bwm.id) LEFT JOIN transactions as t on (t.id = b.transaction_id)
						LEFT JOIN user as u on (u.id = b.user_id) LEFT JOIN business_location as bl on (bl.id = bws.location) JOIN business as bs on (bs.id = b.business_id)
						WHERE b.service_type = 4 AND b.status = 'Success'  AND b.user_id = ".$userid;

						if (!empty($transaction_id)) {
							$query .= " AND t.id = '" . $transaction_id . "'";
						}

						if ($business_id != 0) {
							$query .= " AND b.business_id = '" . $business_id . "'";
						}

						if (!empty($start_dt)) {
							$start_dt = date('Y-m-d', $start_dt);
							$query .= " AND b.shift_search_date = '" . $start_dt . "'";
						}

						if (isset($_POST['workshop_status'])) {
							$class_status = $this->input->post('workshop_status');
							if ($class_status == '0') {
								$query .= " AND bws.schedule_dates >= '" . $current . "' ";
								$query .= " ORDER BY bws.schedule_dates asc LIMIT " . $limit . ' OFFSET ' . $offset;
							} else {
								$query .= " AND bws.schedule_dates < '" . $current . "' ";
								$query .= " ORDER BY bws.schedule_dates desc LIMIT " . $limit . ' OFFSET ' . $offset;
							}

						} else {
							$query .= " ORDER BY b.create_dt desc LIMIT " . $limit . ' OFFSET ' . $offset;
						}

						// $query .= " ORDER BY b.create_dt desc";
						$collection = $this->db->query($query)->result_array();

						if (!empty($collection)) {

							array_walk($collection, function (&$key) {
								//$key['capacity_used'] = 0;
								$key['address'] = $key['address_id'];
								$key['capacity_used'] = $this->db->get_where('user_booking', array(
									//'class_id' => $workshop_data['id'],
									'service_id' => $key['workshop_id'],
									'status' => 'Success',
								))->num_rows();

								unset($key['address_id']);

							});
							$resp = array();

							/* if (isset($_POST['workshop_status'])) {

								$work_status = $this->input->post('workshop_status');

								foreach($collection  as $col) {

									$schedule_dates = $col['schedule_dates'];

									if ($work_status == '0') {
										// Future date
										if ($schedule_dates >= $current) {
											array_push($resp, $col);
										}

									} else {

										if ($current > $schedule_dates) {

											array_push($resp, $col);

										}

									}

								}

							} else {
								$resp = $collection;
							} */


							$arg['status'] = 1;
							$arg['error_code'] = HTTP_OK;
							$arg['error_line'] = __line__;
							// $arg['query']       = $query;
							$arg['data'] = $collection;
							$arg['message'] = $this->lang->line('record_found');
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data'] = array();
							$arg['message'] = $this->lang->line('record_not_found');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function my_attendance_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					$this->form_validation->set_rules('business_id', 'Business Id', 'required|numeric', array(
						'required' => $this->lang->line('business_id_req'),
						'numeric' => $this->lang->line('business_id_numeric'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['error_code'] = 0;
						$arg['error_line'] = __line__;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$user_id = $userdata['data']['id'];
						$time_zone = $this->input->get_request_header('Timezone', true);
						$time_zone = $time_zone ? $time_zone : 'UTC';
						date_default_timezone_set($time_zone);

						$business_id = $this->input->post('business_id');
						$currentStartDate = date('Y-m-d', strtotime('today - 30 days'));
						$currentEndDate = date('Y-m-d');
						$previousStartDate = date('Y-m-d', strtotime('today - 60 days'));

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

						$arg['status'] = 1;
						$arg['error_code'] = REST_Controller::HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['data'] = array(
							'description' => ($attendance_status) ? '+' . number_format($percentage, 2) . '%' : '-' . number_format($percentage, 2) . '%',
							'attendance_status' => $attendance_status,
							'current_month' => $current_avl_count . ' / ' . $current_total_count . ' Classes (' . date_format(date_create($currentStartDate), 'M d Y') . ' - ' . date_format(date_create($currentEndDate), 'M d Y') . ')',
							'previous_month' => $previous_avl_count . ' / ' . $previous_total_count . ' Classes (' . date_format(date_create($previousStartDate), 'M d Y') . ' - ' . date_format(date_create($currentStartDate), 'M d Y') . ')',
						);

						$arg['message'] = $this->lang->line('record_found');
					}
				}
			}
		}
		echo json_encode($arg);
	}

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

	public function clover_buy_now_workshop_single_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {

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

					$this->form_validation->set_rules('business_id', 'Business_id Id', 'required|trim', array('required' => 'Business_id id is required'));
					$this->form_validation->set_rules('workshop_id', 'Workshop Id', 'required|trim', array('required' => 'Workshop id is required.'));
					$this->form_validation->set_rules('workshop_schdule_id', 'Workshop Schdule Id', 'required|trim', array('required' => 'Workshop Schdule id is required.'));
					//$this->form_validation->set_rules('quantity','Quantity', 'required', array( 'required' => $this->lang->line('quantity_required')));
					$this->form_validation->set_rules('grand_total', 'grand total', 'required|greater_than[0]', array(
						'required' => $this->lang->line('amount_required'),
						'numeric' => $this->lang->line('amount_valid'),
					));
					//$this->form_validation->set_rules('slot_date','Slot Date', 'required', array( 'required' => $this->lang->line('date_required')));
					// $this->form_validation->set_rules('token', 'Token', 'required', array('required' => $this->lang->line('token_required')));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$business_id = $this->input->post('business_id');
						$transactions_tax = $this->input->post('tax');
						$transactions_tax = $transactions_tax ? $transactions_tax : 0;

						$workshop_id = $this->input->post('workshop_id');
						$workshop_schdule_id = $this->input->post('workshop_schdule_id');
						$where = array('id' => $workshop_id);
						$product_data = $this->dynamic_model->getdatafromtable('business_workshop_master', $where);
						$workshop_capacity = $product_data[0]['workshop_capacity'];

						$user_booking_data = $this->db->get_where('user_booking', array(
							'service_type' => '4',
							'service_id' => $workshop_id,
							'status' => 'Success',
						))->num_rows();
						if ($workshop_capacity <= $user_booking_data) {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['message'] = 'No capicty available.';
							echo json_encode($arg);die;
						}

						$where = array('id' => $workshop_schdule_id);
						$schdule_data = $this->dynamic_model->getdatafromtable('business_workshop_schdule', $where);
						$start_time_unix = $schdule_data[0]['start'];
						$end_time_unix = $schdule_data[0]['end'];

						$Amt = 0;
						$usid = $userdata['data']['id'];
						$name = $userdata['data']['name'];
						$lastname = $userdata['data']['lastname'];
						$time = time();
						$pass_start_date = $pass_end_date = $pass_status = '';
						//service_type => 1 passes 2 services 3 product
						$service_type = 4;
						$quantity = 1;
						// $token = $this->input->post('token');
                        if ($this->input->post('token')) {
							$token = $this->input->post('token');
						} else {
							$dat = $resp->data;
							$token = $dat->token;
						}

						$grand_total = $this->input->post('grand_total');
						$grand_total = number_format((float) $grand_total, 2, '.', '');

						//$savecard = $this->input->post('savecard');
						$passes_total_count = 0;
						$passes_remaining_count = 0;

						$pass_start_date = 0;
						$pass_end_date = 0;

						/*$savecard = $this->input->post('savecard');
						$card_id = $this->input->post('card_id');

						$mid = getUserMarchantId($business_id);
						$marchant_id = $mid['marchant_id'];
						$marchant_id_type = $mid['marchant_id_type'];

						if (!empty($token)) {
							$payment_data = array(
								'order_number' => $time,
								'amount' => $grand_total,
								'payment_method' => 'token',
								'token' => array(
									'name' => 'Test Card',
									'code' => $token,
									'complete' => true,
								),
							);
						} else if (!empty($card_id)) {

							$where = array('user_id' => $usid, 'business_id' => $business_id);
							$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
							$customer_code = $result_card[0]['card_id'];

							$payment_data = array(
								'order_number' => $time,
								'amount' => $grand_total,
								'payment_method' => 'payment_profile',
								'payment_profile' => array(
									'customer_code' => $customer_code,
									'card_id' => $card_id,
									'complete' => true),
							);
						}

						$where = array('user_id' => $usid);
						$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
						if (empty($result_card) && ($savecard == '1')) {
							$legato_token_data = array(
								'language' => 'en',
								'comments' => SITE_NAME,
								'token' => array('name' => 'Test Card',
									'code' => $token),
							);
							$apiurl = 'https://api.na.bambora.com/v1/profiles';
							$responce = $this->bomborapay->profile_create('POST', $apiurl, $legato_token_data, $marchant_id);
							//echo $marchant_id;
							//print_r($responce); die;
							if ($responce['code'] == '1') {
								$transaction_data = array('user_id' => $usid,
									'card_id' => $responce['customer_code']);
								$this->dynamic_model->insertdata('user_card_save', $transaction_data);
								$customer_code = $responce['customer_code'];
							}
						} elseif (!empty($result_card) && ($savecard == '1')) {
							$customer_code = $result_card[0]['card_id'];
							$apiurl = "https://api.na.bambora.com/v1/profiles/$customer_code/cards";
							$legato_token_data = array(
								'token' => array('name' => 'Test Card',
									'code' => $token),
							);
							$responce = $this->bomborapay->profile_create('POST', $apiurl, $legato_token_data, $marchant_id);
							// echo $marchant_id;
							//print_r($responce); die;
							if ($responce['code'] == '1') {
								$customer_code = $responce['customer_code'];
							}
						}

						if ($savecard == '1') {
							$payment_data = array(
								'order_number' => $time,
								'amount' => $grand_total,
								'payment_method' => 'payment_profile',
								'payment_profile' => array(
									'customer_code' => $customer_code,
									'card_id' => $card_id,
									'complete' => true),
							);
						}

						$payUrl = 'https://api.na.bambora.com/v1/payments';
						$res = $this->bomborapay->payment_checkout('POST', $payUrl, $payment_data, $marchant_id, $marchant_id_type);
						if (@$res['approved'] == 1) {
							$where = array('user_id' => $usid);
							$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
							$ref_num = getuniquenumber();
							$payment_id = !empty(@$res['id']) ? $res['id'] : $ref_num;
							$authorizing_merchant_id = !empty(@$res['authorizing_merchant_id']) ? $res['authorizing_merchant_id'] : '';
							$payment_type = !empty(@$res['type']) ? $res['type'] : '';
							$payment_method = !empty(@$res['payment_method']) ? $res['payment_method'] : '';
							$amount = !empty(@$res['amount']) ? $res['amount'] : '';
						*/
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
								'user_id' => $usid,
								'amount' => $amount,
								'transactions_tax' => $transactions_tax,
								'trx_id' => $payment_id,
								'order_number' => $time,
								'transaction_type' => 4,
								'payment_status' => "Success",
								'saved_card_id' => 0,
								'create_dt' => $time,
								'update_dt' => $time,
								'responce_all' => '' // json_encode($res),
							);
							$transaction_id = $this->dynamic_model->insertdata('transactions', $transaction_data);
							//after that insert into user booking table
							$sub_total = $amount * $quantity;
							$passData = array(
								'business_id' => $business_id,
								'user_id' => $usid,
								'transaction_id' => $transaction_id,
								'amount' => $amount,
								'service_type' => $service_type,
								'service_id' => $workshop_id,
								'class_id' => $workshop_schdule_id,
								'quantity' => $quantity,
								'sub_total' => $sub_total,
								'status' => "Success",
								'create_dt' => $time,
								'update_dt' => $time,
								'passes_start_date' => $start_time_unix,
								'passes_end_date' => $end_time_unix,
							);

							$booking_id = $this->dynamic_model->insertdata('user_booking', $passData);

							$response = array('amount' => number_format((float) $sub_total, 2, '.', ''), 'transaction_date' => date('d M Y'));
							if ($transaction_id) {
								$arg['status'] = 1;
								$arg['error_code'] = HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_succ');
								$arg['transaction_id'] = $transaction_id;
								$arg['data'] = $response;
							} else {
								$arg['status'] = 0;
								$arg['error_code'] = HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_fail');
								$arg['data'] = json_decode('{}');
							}
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['message'] = ''; // 25/04/2021 @$res->error->message;
							$arg['data'] = json_decode('{}');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function clover_buy_now_services_single_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					/* 25/04/2021
					if ($this->input->post('expiry_month')) {
						$resp = $this->fetch_clover_payment_token($this->input->post('business_id'), $this->input->post('country_code'), $this->input->post('number'), $this->input->post('expiry_month'), $this->input->post('expiry_year'), $this->input->post('cvd'));

						$resp = json_decode($resp);
						if ($resp->status == 0) {
							$arg['status'] = 0;
							$arg['error_code'] = ERROR_FAILED_CODE;
							$arg['error_line'] = __line__;
							$arg['message'] = 'Invalid Details';
							$arg['data'] = json_decode('{}');
							echo json_encode($arg); exit;
						}
					} */

					$this->form_validation->set_rules('service_id', 'Service Id', 'required|trim', array('required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('transaction_id', 'Transaction Id', 'required|trim', array('required' => $this->lang->line('service_id_required')));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$service_id = $this->input->post('service_id');
						$transaction_id = $this->input->post('transaction_id');
						$where = array('id' => $service_id, 'status' => 'Active');
						$product_data = $this->dynamic_model->getdatafromtable('service', $where);
						$Amt = 0;
						$usid = $userdata['data']['id'];
						$name = $userdata['data']['name'];
						$lastname = $userdata['data']['lastname'];
						$time = time();
						$pass_start_date = $pass_end_date = $pass_status = '';
						//service_type => 1 passes 2 services 3 product

						if ($this->input->post('token')) {
							$token = $this->input->post('token');
						} else {
							$dat = $resp->data;
							$token = $dat->token;
						}
						// $token = $this->input->post('token');

						$where = array('transaction_id' => $transaction_id);
						$booking_data = $this->dynamic_model->getdatafromtable('user_booking', $where);
						if (empty($booking_data)) {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['message'] = 'No record Found';
							$arg['data'] = json_decode('{}');
							echo json_encode($arg);die;
						}

						$quantity = $booking_data[0]['quantity'];
						$grand_total = $booking_data[0]['amount'];
						$grand_total = number_format((float) $grand_total, 2, '.', '');
						//$savecard = $this->input->post('savecard');
						//$card_id = $this->input->post('card_id');
						$passes_total_count = 0;
						$passes_remaining_count = 0;

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
							/* 25/04/2021
							$res_data = clover_api_card_profile_check($usid,$savecard,$customer_name,$number,$expiry_month,$expiry_year,$cvd,$country_code,$business_id,$token);
							$customer_code= $res_data['customer_code'];
							$marchant_id  = $res_data['marchant_id'];
							$country_code = $res_data['country_code'];
							$clover_key   = $res_data['clover_key'];
							$access_token = $res_data['access_token'];
							$currency     = $res_data['currency']; */


							$user_cc_no   = $number;
							$user_cc_mo   = $expiry_month;
							$user_cc_yr   = $expiry_year;
							$user_cc_cvv  = $cvd;
							$user_zip     = '';
							$amount       = $grand_total;
							$taxAmount    = 0;
							// 25/04/2021
							// $res  = clover_api_payment_checkout($user_cc_no,$user_cc_mo,$user_cc_yr,$user_cc_cvv,$user_zip,$amount,$taxAmount,$marchant_id,$clover_key,$access_token,$currency,$token);

							//{ "id" : "R48Q7GPMY2FQY", "amount" : 1800, "amount_refunded" : 0, "currency" : "usd", "created" : 1616661109846, "captured" : true, "ref_num" : "108400500020", "auth_code" : "OK2809", "outcome" : { "network_status" : "approved_by_network", "type" : "authorized" }, "paid" : true, "status" : "succeeded", "source" : { "id" : "clv_1TSTS1iWemARMj4AXHxHikLV", "brand" : "AMEX", "cvc_check" : "unchecked", "exp_month" : "11", "exp_year" : "2025", "first6" : "378282", "last4" : "0005" } }

							//{"message":"400 Bad Request","error":{"code":"token_already_used","message":"You cannot use a clover token more than once unless it is marked as multipay."}}


							//{"paymentId":"30PFKD66YXNCP","result":"APPROVED","authCode":"OK7823","token":"DGY73XR6DDDF0","vaultedCard":{"first6":"411111","last4":"1111","expirationDate":"0321","token":"1894469479681111"}}

							//echo $res['message'];die;
							// 25/04/2021
							// if(@$res->status == 'succeeded')
							if(true)
							{
								$where = array('user_id' => $usid,
									'business_id' => $business_id,
								);
								$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);

								$ref_num    = getuniquenumber();
								$payment_id = time(); // !empty($res->id) ? $res->id : $ref_num;
								$authorizing_merchant_id = time(); // $res->source->id;
								$payment_type   = 'Card';
								$payment_method = 'Online';
								$amount         = $amount;



							//Insert data in transaction table
							$transaction_data = array(
								'authorizing_merchant_id' => $authorizing_merchant_id,
								'payment_type' => $payment_type,
								'payment_method' => $payment_method,
								'amount' => $amount,
								'trx_id' => $payment_id,
								'order_number' => $time,
								'transaction_type' => 3,
								'payment_status' => "Success",
								'create_dt' => $time,
								'transaction_date' => date('Y-m-d'),
								'update_dt' => $time,
								'responce_all' => '' // json_encode($res),
							);
							$where1 = array('id' => $transaction_id);
							$this->dynamic_model->updateRowWhere('transactions', $where1, $transaction_data);

							//after that insert into user booking table
							$sub_total = $amount * $quantity;
							$passData = array(
								'amount' => $amount,
								'sub_total' => $sub_total,
								'status' => "Success",
								'create_dt' => $time,
								'update_dt' => $time,
							);

							if ($this->input->post('tip_comment')) {
								$passData['tip_comment'] = $this->input->post('tip_comment');
							}

							$where1 = array('transaction_id' => $transaction_id);
							$this->dynamic_model->updateRowWhere('user_booking', $where1, $passData);

							$response = array('amount' => number_format((float) $sub_total, 2, '.', ''), 'transaction_date' => date('d M Y'));
							if ($transaction_id) {
								$arg['status'] = 1;
								$arg['error_code'] = HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_succ');
								$arg['transaction_id'] = $transaction_id;
								$arg['data'] = $response;
							} else {
								$arg['status'] = 0;
								$arg['error_code'] = HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_fail');
								$arg['data'] = json_decode('{}');
							}
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['message'] = ''; // 25/04/2021 @$res->error->message;
							$arg['data'] = json_decode('{}');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}

	//Used function for clover buy now new
	public function clover_buy_now_single_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					/* 25/04/2021
					if ($this->input->post('expiry_month')) {
						$resp = $this->fetch_clover_payment_token($this->input->post('business_id'), $this->input->post('country_code'), $this->input->post('number'), $this->input->post('expiry_month'), $this->input->post('expiry_year'), $this->input->post('cvd'));

						$resp = json_decode($resp);
						if ($resp->status == 0) {
							$arg['status'] = 0;
							$arg['error_code'] = ERROR_FAILED_CODE;
							$arg['error_line'] = __line__;
							$arg['message'] = 'Invalid Details';
							$arg['data'] = json_decode('{}');
							echo json_encode($arg); exit;
						}
					} */


					$this->form_validation->set_rules('service_type', 'Service Type', 'required|trim', array('required' => $this->lang->line('service_type_required')));
					$this->form_validation->set_rules('service_id', 'Service Id', 'required|trim', array('required' => $this->lang->line('service_id_required')));
					$this->form_validation->set_rules('quantity', 'Quantity', 'required', array('required' => $this->lang->line('quantity_required')));

					// $this->form_validation->set_rules('number', 'Card No', 'required', array('required' => $this->lang->line('quantity_required')));
					// $this->form_validation->set_rules('expiry_month', 'Expiry Month', 'required', array('required' => $this->lang->line('quantity_required')));
					// $this->form_validation->set_rules('expiry_year', 'Expiry Year', 'required', array('required' => $this->lang->line('quantity_required')));

					// $this->form_validation->set_rules('amount','Amount', 'required', array( 'required' => $this->lang->line('amount_required')));
					$this->form_validation->set_rules('grand_total', 'grand total', 'required|greater_than[0]', array(
						'required' => $this->lang->line('amount_required'),
						'numeric' => $this->lang->line('amount_valid'),
					));

					if ($this->input->post('service_type') == 2) {
						$this->form_validation->set_rules('slot_date', 'Slot Date', 'required', array('required' => $this->lang->line('date_required')));
						$this->form_validation->set_rules('slot_time_id', 'Slot Id', 'required', array('required' => $this->lang->line('slot_id_required')));
						// $this->form_validation->set_rules('token', 'Token', 'required', array('required' => $this->lang->line('token_required')));
						$this->form_validation->set_rules('instructor_id', 'Instructor', 'required', array('required' => $this->lang->line('instructor_id_required')));
					}

					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$recurring = 0;
						$service_id = $this->input->post('service_id');
						$where = array('id' => $service_id, 'status' => 'Active');
						$product_data = $this->dynamic_model->getdatafromtable('business_product', $where);

						$Amt = 0;
						$usid = $userdata['data']['id'];
						$name = $userdata['data']['name'];
						$lastname = $userdata['data']['lastname'];
						$time = time();
						$pass_start_date = $pass_end_date = $pass_status = '';

						//service_type => 1 passes 2 services 3 product
						$service_type = $this->input->post('service_type');
						$quantity = $this->input->post('quantity');
						if ($this->input->post('token')) {
							$token = $this->input->post('token');
						} else {
							$dat = $resp->data;
							$token = $dat->token;
						}

						// $amount           = $this->input->post('amount');
						// $amount           = number_format((float)$amount, 2, '.', '');
						$grand_total = $this->input->post('grand_total');
						$grand_total = number_format((float) $grand_total, 2, '.', '');
						//$grand_total           = '10.00';;
						$slot_date = $this->input->post('slot_date');
						$slot_time_id = $this->input->post('slot_time_id');
						$savecard = $this->input->post('savecard');
						if ($service_type == 1) {
							//Check pass already added or not
							$whe = array('user_id' => $usid, 'service_id' => $service_id, 'service_type' => '1', '   passes_status' => '1');
							$chk_pass_booking = $this->dynamic_model->getdatafromtable('user_booking', $whe);
							if (!empty($chk_pass_booking)) {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('pass_already_msg');
								$arg['data'] = json_decode('{}');
								echo json_encode($arg);exit;
							}
						}
						//get data according to service type passes,services,products
						$passes_total_count = 0;
						$passes_remaining_count = 0;
						$recurring_date = '';
						if ($service_type == 1) {
							$where = array('id' => $service_id, 'status' => 'Active');
							$business_pass = $this->dynamic_model->getdatafromtable('business_passes', $where);
							$business_id = (!empty($business_pass[0]['business_id'])) ? $business_pass[0]['business_id'] : 0;
							if ($business_pass[0]['service_type'] == 1) {
								$class_id = $business_pass[0]['service_id'];
							} else {
								$workshop_id = $business_pass[0]['service_id'];
							}
							$pass_start_date = (!empty($business_pass[0]['purchase_date'])) ? $business_pass[0]['purchase_date'] : 0;
							$pass_end_date = (!empty($business_pass[0]['pass_end_date'])) ? $business_pass[0]['pass_end_date'] : 0;

							$pass_start_date = time();
							$validity = (!empty($business_pass[0]['pass_validity'])) ? $business_pass[0]['pass_validity'] : 0;
							$getEndDate = ($validity * 24 * 60 * 60) + $time;
							$pass_end_date = ($validity == 0) ? $pass_start_date : $getEndDate;

							$pass_type_subcat = $business_pass[0]['pass_type_subcat'];
							$where = array('id' => $pass_type_subcat);
							$manage_pass_type = $this->dynamic_model->getdatafromtable('manage_pass_type', $where);

							$pass_days = $manage_pass_type[0]['pass_days'];

							$passes_total_count = $pass_days;
							$passes_remaining_count = $pass_days;
							$Amt = (!empty($business_pass[0]['amount'])) ? $business_pass[0]['amount'] : 0;

							if ($pass_type_subcat == '36') {
								$today_dt = date('d');
								$a_date = date("Y-m-d");
								$lastmonth_dt = date("t", strtotime($a_date));
								$diff_dt = $lastmonth_dt - $today_dt;
								$diff_dt = $diff_dt + 1;

								$rt = date("Y-m-t", strtotime($a_date));
								$recurring_date = $rt;
								$pass_end_date = strtotime($rt);
								$passes_remaining_count = $diff_dt;

								$per_day_amt = $Amt / $lastmonth_dt;

								$per_day_amt = round($per_day_amt, 2);
								$Amt = $per_day_amt * $diff_dt;

								$grand_total = number_format((float) $Amt, 2, '.', '');
								$Amt = $grand_total;
								$recurring = 1;
							} else if (($pass_type_subcat == '33')) {
								// 3 month
								$recurring = 2;
								$recurring_date = date('Y-m-d', strtotime('next month'));
								$rt = date('d-M-y', strtotime('next month'));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-Y', strtotime("$rt +1 month"));
								$pass_end_date = strtotime($rt);
								$date1 = date('d-M-Y');
								$pass_days = dateDiffInDays($date1, $rt);

								$passes_total_count = $pass_days;
								$passes_remaining_count = $pass_days;

							} else if (($pass_type_subcat == '34')) {
								// 6 month
								$recurring = 5;
								$recurring_date = date('Y-m-d', strtotime('next month'));
								$rt = date('d-M-y', strtotime('next month'));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-Y', strtotime("$rt +1 month"));
								$pass_end_date = strtotime($rt);
								$date1 = date('d-M-Y');
								$pass_days = dateDiffInDays($date1, $rt);

								$passes_total_count = $pass_days;
								$passes_remaining_count = $pass_days;
							} else if (($pass_type_subcat == '35')) {
								// 12 month
								$recurring = 11;
								$recurring_date = date('Y-m-d', strtotime('next month'));
								$rt = date('d-M-y', strtotime('next month'));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-y', strtotime("$rt +1 month"));
								$rt = date('d-M-Y', strtotime("$rt +1 month"));
								$pass_end_date = strtotime($rt);
								$date1 = date('d-M-Y');
								$pass_days = dateDiffInDays($date1, $rt);
								$passes_total_count = $pass_days;
								$passes_remaining_count = $pass_days;
							}
							// echo  $grand_total; die;
							$pass_status = 1;

						} elseif ($service_type == 2) {
							/* $where = array('id'=>$service_id,'status' => 'Active');
															$business_service= $this->dynamic_model->getdatafromtable('service',$where);
															$business_id=(!empty($business_service[0]['business_id'])) ? $business_service[0]['business_id'] : 0;
															$Amt=(!empty($business_service[0]['amount'])) ? $business_service[0]['amount'] : 0;
															$con=array("service_id"=>$service_id,"service_type"=>1,"business_id"=>$business_id,"slot_available_status"=>"0","slot_date"=>$slot_date,"id"=>$slot_time_id);
															$appointment_data = $this->dynamic_model->getdatafromtable('business_appointment_book',$con);
															if(empty($appointment_data)){
																$arg['status']     = 0;
																$arg['error_code']  = REST_Controller::HTTP_NOT_FOUND;
																$arg['error_line']= __line__;
																$arg['message']    = $this->lang->line('services_already_book');
																$arg['data']      =json_decode('{}');
																echo json_encode($arg);exit;
							*/
							$where = array(
								'service_id' => $service_id,
								'service_type' => '2',
								'service_slot_id' => $slot_time_id,
								'status' => 'Success',
							);
							$chk_pass_booking = $this->dynamic_model->getdatafromtable('user_booking', $where);
							$chk_service_schedule = $this->dynamic_model->getdatafromtable('service_scheduling_time_slot', array('status' => 0));
							$chk_appointment_booking = $this->dynamic_model->getdatafromtable('business_appointment_book', array('slot_id' => $this->input->post('slot_time_id')));

							if (!empty($chk_pass_booking)) {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('slot_already_booked');
								$arg['data'] = json_decode('{}');
								echo json_encode($arg);exit();
							} else if (empty($chk_service_schedule)) {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('slot_id_not_found');
								$arg['data'] = json_decode('{}');
								echo json_encode($arg);exit();
							} else if (!empty($chk_appointment_booking)) {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('slot_already_booked');
								$arg['data'] = json_decode('{}');
								echo json_encode($arg);exit();
							}

							$pass_start_date = 0;
							$pass_end_date = 0;
							$service = $this->db->get_where('service', array('id' => $service_id))->row_array();
							$business_id = $service['business_id'];

						} elseif ($service_type == 3) {
							$where = array('id' => $service_id, 'status' => 'Active');
							$product_data = $this->dynamic_model->getdatafromtable('business_product', $where);
							$business_id = (!empty($product_data[0]['business_id'])) ? $product_data[0]['business_id'] : 0;
							$Amt = (!empty($product_data[0]['price'])) ? $product_data[0]['price'] : 0;
							//check product stock limit
							$product_quantity = get_product_quantity($business_id, $service_id);
							if ($product_quantity < $quantity) {
								$arg['status'] = 0;
								$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('product_quantity_limit');
								$arg['data'] = json_decode('{}');
								echo json_encode($arg);exit;
							}
						}

						$savecard      = $this->input->post('savecard');
						$card_id       = $this->input->post('card_id');
						$customer_name = $this->input->post('customer_name');
						$number        = $this->input->post('number');
						$expiry_month  = $this->input->post('expiry_month');
						$expiry_year   = $this->input->post('expiry_year');
						$cvd           = $this->input->post('cvd');
						$country_code  = $this->input->post('country_code');
						/* 25/04/201
						$res_data = clover_api_card_profile_check($usid,$savecard,$customer_name,$number,$expiry_month,$expiry_year,$cvd,$country_code,$business_id,$token);
						$customer_code= $res_data['customer_code'];
						$marchant_id  = $res_data['marchant_id'];
						$country_code = $res_data['country_code'];
						$clover_key   = $res_data['clover_key'];
						$access_token = $res_data['access_token'];
						$currency     = $res_data['currency']; */


						$user_cc_no   = $number;
						$user_cc_mo   = $expiry_month;
						$user_cc_yr   = $expiry_year;
						$user_cc_cvv  = $cvd;
						$user_zip     = '';
						$amount       = $grand_total;
						$taxAmount    = 0;
						/* 25/04/201
						$res  = clover_api_payment_checkout($user_cc_no,$user_cc_mo,$user_cc_yr,$user_cc_cvv,$user_zip,$amount,$taxAmount,$marchant_id,$clover_key,$access_token,$currency,$token); */

						//{ "id" : "R48Q7GPMY2FQY", "amount" : 1800, "amount_refunded" : 0, "currency" : "usd", "created" : 1616661109846, "captured" : true, "ref_num" : "108400500020", "auth_code" : "OK2809", "outcome" : { "network_status" : "approved_by_network", "type" : "authorized" }, "paid" : true, "status" : "succeeded", "source" : { "id" : "clv_1TSTS1iWemARMj4AXHxHikLV", "brand" : "AMEX", "cvc_check" : "unchecked", "exp_month" : "11", "exp_year" : "2025", "first6" : "378282", "last4" : "0005" } }

						//{"message":"400 Bad Request","error":{"code":"token_already_used","message":"You cannot use a clover token more than once unless it is marked as multipay."}}


						//{"paymentId":"30PFKD66YXNCP","result":"APPROVED","authCode":"OK7823","token":"DGY73XR6DDDF0","vaultedCard":{"first6":"411111","last4":"1111","expirationDate":"0321","token":"1894469479681111"}}

						//echo $res['message'];die;
						//echo $res['message'];die;
						// 25/04/201
						// if(@$res->status == 'succeeded')
						if(true)
						{
							$where = array('user_id' => $usid,
								'business_id' => $business_id,
							);
							$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);

							$ref_num    = getuniquenumber();
							$payment_id = time(); //!empty($res->id) ? $res->id : $ref_num; 25/04/201
							$authorizing_merchant_id = time(); // $res->source->id; 25/04/201
							$payment_type   = 'Card';
							$payment_method = 'Online';
							$amount         = $amount;
							//Insert data in transaction table
							$transaction_data = array(
								'authorizing_merchant_id' => $authorizing_merchant_id,
								'payment_type' => $payment_type,
								'payment_method' => $payment_method,
								'user_id' => $usid,
								'amount' => $amount,
								'trx_id' => $payment_id,
								'order_number' => $time,
								'transaction_type' => ($service_type == 2) ? 3 : $service_type,
								'payment_status' => "Success",
								'saved_card_id' => 0,
								'create_dt' => $time,
								'update_dt' => $time,
								'responce_all' => '' // 25/04/201 json_encode($res),
							);
							$transaction_id = $this->dynamic_model->insertdata('transactions', $transaction_data);
							//after that insert into user booking table
							$sub_total = $Amt * $quantity;
							$passData = array(
								'business_id' => $business_id,
								'user_id' => $usid,
								'transaction_id' => $transaction_id,
								'amount' => $Amt,
								'service_type' => $service_type,
								'service_id' => $service_id,
								'class_id' => (!empty($class_id)) ? $class_id : '',
								'workshop_id' => (!empty($workshop_id)) ? $workshop_id : '',
								'quantity' => $quantity,
								'sub_total' => $sub_total,
								'status' => "Success",
								'passes_start_date' => $pass_start_date,
								'passes_end_date' => $pass_end_date,
								'passes_status' => $pass_status,
								'create_dt' => $time,
								'update_dt' => $time,
								'recurring' => $recurring,
								'recurring_date' => $recurring_date,
							);
							if ($service_type == 1) {
								$passData['passes_total_count'] = $passes_total_count;
								$passData['passes_remaining_count'] = $passes_remaining_count;
							}
							if ($service_type == 2) {
								$passData['service_slot_id'] = $slot_time_id;
							}
							$booking_id = $this->dynamic_model->insertdata('user_booking', $passData);
							if ($service_type == 2) {
								$insert_data = array(
									"business_id" => $business_id,
									'booking_id' => $booking_id,
									"user_id" => $this->input->post('instructor_id'),
									"slot_id" => $slot_time_id,
									"service_id" => $service_id,
									"service_type" => 1,
									"slot_available_status" => "1",
									"slot_date" => $slot_date,
									'create_dt' => $time,
									'update_dt' => $time,
								);
								if ($this->input->post('family_user_id')) {
									$insert_data['family_user_id'] = $this->input->post('family_user_id');
								}
								$booking_id = $this->dynamic_model->insertdata('business_appointment_book', $insert_data);
								$this->dynamic_model->updateRowWhere('service_scheduling_time_slot', array('id' => $slot_time_id), array('status' => 1));
							} else {
								$remain_quantity = $product_data[0]['quantity'] - $quantity;
								$update_data1 = array('quantity' => $remain_quantity);

								$cond1 = array("business_id" => $business_id, "id" => $product_data[0]['id']);
								$this->dynamic_model->updateRowWhere('business_product', $cond1, $update_data1);
							}

							/* if($service_type==2){
								$cond=array("service_id"=>$service_id,"service_type"=>1,"business_id"=>$business_id,"slot_available_status"=>"0","slot_date"=>$slot_date,"id"=>$slot_time_id);
								$update_data=array("slot_available_status"=>1);
								$booking_id= $this->dynamic_model->updateRowWhere('business_appointment_book',$cond,$update_data);
							*/

							$response = array('amount' => number_format((float) $sub_total, 2, '.', ''), 'transaction_date' => date('d M Y'));
							if ($transaction_id) {
								$arg['status'] = 1;
								$arg['error_code'] = HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_succ');
								if ($service_type == 2) {
									$arg['booking_id'] = $booking_id;
								}
								$arg['data'] = $response;
							} else {
								$arg['status'] = 0;
								$arg['error_code'] = HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_fail');
								$arg['data'] = json_decode('{}');
							}
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['message'] = ''; // 25/04/2021 @$res->error->message;
							$arg['data'] = json_decode('{}');
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function clover_pay_checkout_single_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					// 25/04/2021
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

					$this->form_validation->set_rules('amount', 'Amount', 'required|greater_than[0]', array(
						'required' => $this->lang->line('amount_required'),
						'numeric' => $this->lang->line('amount_valid'),
					));
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$usid = $userdata['data']['id'];
						$name = $userdata['data']['name'];
						$lastname = $userdata['data']['lastname'];
						$time = time();
						$amount = $this->input->post('amount');
						//$savecard = $this->input->post('savecard');
						//$card_id = $this->input->post('card_id');
						$amount = number_format((float) $amount, 2, '.', '');
						$token = $this->input->post('token');
						// if ($this->input->post('token')) {
							
						// } else {
						// 	$dat = $resp->data;
						// 	$token = $dat->token;
						// }
						// $token = $this->input->post('token');

						$card_res = $card_data = $card_Exist = array();
						/*$cart_check = check_cart_with_tax($usid);
						if ($cart_check == false) {
							$arg['status'] = 0;
							$arg['error_code'] = ERROR_FAILED_CODE;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('check_cart_msg');
							$arg['data'] = json_decode('{}');
							echo json_encode($arg);exit;
						}
						echo "<li>Amount : ".$amount;
						echo "<li>cart_check : ".$cart_check;die;
						if ($cart_check !== $amount) {
							$arg['status'] = 0;
							$arg['error_code'] = ERROR_FAILED_CODE;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('amount_incorrect');
							$arg['data'] = json_decode('{}');
							echo json_encode($arg);exit;
						}*/


						/*if (!empty($token)) {
							$payment_data = array(
								'order_number' => $time,
								'amount' => $amount,
								'payment_method' => 'token',
								'token' => array(
									'name' => 'Test Card',
									'code' => $token,
									'complete' => true,
								),
							);
						} else if (!empty($card_id)) {
							$where = array('user_id' => $usid);
							$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
							$customer_code = $result_card[0]['card_id'];

							$payment_data = array(
								'order_number' => $time,
								'amount' => $amount,
								'payment_method' => 'payment_profile',
								'payment_profile' => array(
									'customer_code' => $customer_code,
									'card_id' => $card_id,
									'complete' => true,
								),
							);
						}
						$where = array('user_id' => $usid);
						$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);
						if (empty($result_card) && ($savecard == '1')) {
							$legato_token_data = array(
								'language' => 'en',
								'comments' => SITE_NAME,
								'token' => array('name' => 'Test Card',
									'code' => $token),
							);
							$apiurl = 'https://api.na.bambora.com/v1/profiles';
							$responce = $this->bomborapay->profile_create('POST', $apiurl, $legato_token_data);
							if ($responce['code'] == '1') {
								$transaction_data = array('user_id' => $usid,
									'card_id' => $responce['customer_code']);
								$this->dynamic_model->insertdata('user_card_save', $transaction_data);
								$customer_code = $responce['customer_code'];
							}
						} elseif (!empty($result_card) && ($savecard == '1')) {
							$customer_code = $result_card[0]['card_id'];
							$apiurl = "https://api.na.bambora.com/v1/profiles/$customer_code/cards";
							$legato_token_data = array(
								'token' => array('name' => 'Test Card',
									'code' => $token),
							);
							$responce = $this->bomborapay->profile_create('POST', $apiurl, $legato_token_data);
							if ($responce['code'] == '1') {
								$customer_code = $responce['customer_code'];
							}
						}

						if ($savecard == '1') {
							$payment_data = array(
								'order_number' => $time,
								'amount' => $amount,
								'payment_method' => 'payment_profile',
								'payment_profile' => array(
									'customer_code' => $customer_code,
									'card_id' => $card_id,
									'complete' => true,
								),
							);
						}

						// print_r($payment_data);die;
						$business_id = getBusinessId($usid);
						$mid = getUserMarchantId($business_id);
						$marchant_id = $mid['marchant_id'];
						$marchant_id_type = $mid['marchant_id_type'];

						//$mid = '377010002';
						$payUrl = 'https://api.na.bambora.com/v1/payments ';
						$res = $this->bomborapay->payment_checkout('POST', $payUrl, $payment_data, $marchant_id, $marchant_id_type);

						//$res=$this->bomborapay->payment_checkout('POST',$payUrl,$payment_data,$mid);
						// print_r($res); die;

						//echo $res['message'];die;
						if (@$res['approved'] == '1') {
							$ref_num = getuniquenumber();
							$payment_id = !empty(@$res['id']) ? $res['id'] : $ref_num;

							$authorizing_merchant_id = !empty(@$res['authorizing_merchant_id']) ? $res['authorizing_merchant_id'] : '';
							$payment_type = !empty(@$res['type']) ? $res['type'] : '';
							$payment_method = !empty(@$res['payment_method']) ? $res['payment_method'] : '';
							$amount = !empty(@$res['amount']) ? $res['amount'] : '';*/

							$business_id = $this->input->post('business_id');
							$savecard      = $this->input->post('savecard');
							$card_id       = $this->input->post('card_id');
							$customer_name = $this->input->post('customer_name');
							$number        = $this->input->post('number');
							$expiry_month  = $this->input->post('expiry_month');
							$expiry_year   = $this->input->post('expiry_year');
							$cvd           = $this->input->post('cvd');
							$country_code  = $this->input->post('country_code');
							// 25/04/2021
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
		
										$cardToken = strhlp_get_token(
											array(
												'card_number'	=>	$number,
												'expiry_month'		=>	$expiry_month,
												'cvv_no'			=>	$cvd,
												'expiry_year'		=>	$expiry_year
											)
										);
		
										$cardStatus = strhlp_add_card(
											array(
												'token'			=>	$cardToken,
												'customer_id'	=>	$getCustomerId
											)
										);
									}
								}

								$token = strhlp_get_token(
									array(
										'card_number'	=>	$number,
										'expiry_month'		=>	$expiry_month,
										'cvv_no'			=>	$cvd,
										'expiry_year'		=>	$expiry_year
									)
								);

								strhlp_checkout(
									array(
										'amount'	=>	$amount,
										'name'		=>	$name . ' ' . $lastname,
										'email'		=>	$userdata['data']['email'],
										'description' => 'Studio Registration',
										'token'	=>	$token
									),
									array(
										'user_id'	=>	$usid
									),
									2
								);
							}

							// 25/04/2021
							//$res  = clover_api_payment_checkout($user_cc_no,$user_cc_mo,$user_cc_yr,$user_cc_cvv,$user_zip,$amount,$taxAmount,$marchant_id,$clover_key,$access_token,$currency,$token);

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

							//End of logic implement for purachase plan mothly haif yearly and yearly
							//Insert data in transaction table
							$transaction_data = array(
								'authorizing_merchant_id' => $authorizing_merchant_id,
								'payment_type' => $payment_type,
								'payment_method' => $payment_method,
								'responce_all' => '', // json_encode($res),
								'user_id' => $usid,
								'amount' => $amount,
								'trx_id' => $payment_id,
								'order_number' => $time,
								'transaction_type' => 2,
								'payment_status' => "Success",
								'saved_card_id' => 0,
								'create_dt' => $time,
								'update_dt' => $time,
							);
							$transaction_id = $this->dynamic_model->insertdata('transactions', $transaction_data);
							$where = array("user_id" => $usid, "status" => "Pending");
							$cart_data = $this->dynamic_model->getdatafromtable('user_booking', $where);
							if (!empty($cart_data)) {
								foreach ($cart_data as $value) {
									//service_type 1 then update passes status and expiry
									$recurring = 0;
									if ($value['service_type'] == '1') {
										$where1 = array('id' => $value['service_id'], 'service_type' => '1', 'status' => 'Active');
										$business_pass = $this->dynamic_model->getdatafromtable('business_passes', $where1);
										// $pass_start_date=(!empty($business_pass[0]['purchase_date'])) ? $business_pass[0]['purchase_date'] : 0;
										// $pass_end_date=(!empty($business_pass[0]['pass_end_date'])) ? $business_pass[0]['pass_end_date'] : 0;

										$pass_validity = (!empty($business_pass[0]['pass_validity'])) ? $business_pass[0]['pass_validity'] : 0;
										$pass_start_date = $time;
										$getEndDate = ($pass_validity * 24 * 60 * 60) + $time;
										$pass_end_date = ($pass_validity == 0) ? $pass_start_date : $getEndDate;

										if (!empty($business_pass)) {
											$pass_type_subcat = $business_pass[0]['pass_type_subcat'];
											if (!empty($pass_type_subcat)) {
												$where2 = array('id' => $pass_type_subcat);
												$manage_pass = $this->dynamic_model->getdatafromtable('manage_pass_type', $where2);
												if (!empty($manage_pass)) {
													$validity = $manage_pass[0]['pass_days'];
												}
											}

											if ($pass_type_subcat == '37') {
												$today_dt = date('d');
												$a_date = date("Y-m-d");
												$lastmonth_dt = date("t", strtotime($a_date));
												$diff_dt = $lastmonth_dt - $today_dt;
												$diff_dt = $diff_dt + 1;

												$rt = date("Y-m-t", strtotime($a_date));
												$pass_end_date = strtotime($rt);
												$passes_remaining_count = $diff_dt;

												$per_day_amt = $Amt / $lastmonth_dt;

												$per_day_amt = round($per_day_amt, 2);
												$Amt = $per_day_amt * $diff_dt;

												$grand_total = number_format((float) $Amt, 2, '.', '');
												$Amt = $grand_total;
												$recurring = 1;
											}
										}

										$pass_status = 1;
										$where2 = array("user_id" => $usid, "status" => "Pending", "service_type" => '1');
										$booking_data = array(
											'transaction_id' => $transaction_id,
											'status' => "Success",
											'passes_start_date' => $pass_start_date,
											'passes_end_date' => $pass_end_date,
											'passes_status' => $pass_status,
											'passes_total_count' => $validity,
											'passes_remaining_count' => $validity,
											'update_dt' => $time,
											'recurring' => $recurring,
										);
										$booking_id = $this->dynamic_model->updateRowWhere('user_booking', $where2, $booking_data);
									} else {

										$cart_quantity = $value['quantity'];
										$result_product = $this->dynamic_model->getdatafromtable('business_product', array('id' => $value['service_id']));
										$total_quantity = $result_product[0]['quantity'] - $cart_quantity;

										$product_id = $this->dynamic_model->updateRowWhere('business_product', array('id' => $value['service_id']), array('quantity' => $total_quantity));
										$where2 = array("user_id" => $usid, "status" => "Pending", "service_type!=" => '1');
										$booking_data = array(
											'transaction_id' => $transaction_id,
											'status' => "Success",
											'update_dt' => $time,
										);
										$booking_id = $this->dynamic_model->updateRowWhere('user_booking', $where2, $booking_data);
									}
								}
							}
							$response = array('amount' => number_format((float) $amount, 2, '.', ''), 'transaction_date' => date('d M Y'));
							if ($transaction_id) {
								$arg['status'] = 1;
								$arg['error_code'] = HTTP_OK;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_succ');
								$arg['data'] = $response;
							} else {
								$arg['status'] = 0;
								$arg['error_code'] = HTTP_NOT_FOUND;
								$arg['error_line'] = __line__;
								$arg['message'] = $this->lang->line('payment_fail');
								$arg['data'] = json_decode('{}');
							}
						} else {
							$arg['status'] = 0;
							$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['message'] = ''; // 25/04/2021 @$res->error->message;
							$arg['data'] = json_decode('{}');
						}

					}
				}
			}
		}
		echo json_encode($arg);
	}

//--------------------------*************End of Api*************---------------------------//


	public function get_video_list_get() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid('1');
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				
				$data_array_video = get_video_data();

				$arg['status']     = 1;
				$arg['error_code'] = REST_Controller::HTTP_OK;
				$arg['error_line'] = __line__;
				$arg['data']       = $data_array_video;
				$arg['message']    = $this->lang->line('record_found');
				
			}
		}
		echo json_encode($arg);
	}

	public function get_category_list_get() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			
				
				$findresult = $this->dynamic_model->get_query_result('select * from manage_category where status="Active" and category_parent=0');
		        //var_dump($findresult); die;
		        $data = array();
		        $data_array = array();
		        if(count($findresult)>0)
		        {
		          foreach ($findresult as $key => $value) 
		          {

		          	$sub_findresult = $this->dynamic_model->get_query_result('select * from manage_category where status="Active" and category_parent="'.$value->id.'"');
			        //var_dump($findresult); die;
			        $data1 = array();
			        $data_array1 = array();
			        if($sub_findresult)
			        {
			          foreach ($sub_findresult as $key1 => $val) 
			          {
			          	$data1['id']            = $val->id;
			            $data1['category_name'] = $val->category_name;
			            $data1['category_type'] = $val->category_type;
			            $data1['price']         = $val->price;
			            $data1['no_of_days']    = $val->no_of_days;
			            $data1['category_parent']   = $val->category_parent;

			             $data_array1[] = $data1;
			          }
			        }


		            $data['id']            = $value->id;
		            $data['category_name'] = $value->category_name;
		            $data['category_type'] = $value->category_type;
		            $data['price']         = $value->price;
		            $data['no_of_days']    = $value->no_of_days;
		            $data['category_parent']        = $value->category_parent;
		            $data['sub_category_child']    = $data_array1;
		            

		            $data_array[] = $data;
		          }

		        }

				$arg['status']     = 1;
				$arg['error_code'] = REST_Controller::HTTP_OK;
				$arg['error_line'] = __line__;
				$arg['data']       = $data_array;
				$arg['message']    = $this->lang->line('record_found');
				
			
		}
		echo json_encode($arg);
	}

	public function signal_registration_from_post() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				$_POST = json_decode(file_get_contents("php://input"), true);
				if ($_POST) {
					
					$this->form_validation->set_rules('step', 'Step', 'required|in_list[1,2,3,4,5,6,7]');
					$this->form_validation->set_rules('json_step_data', 'Step data json format', 'required');
					if ($this->form_validation->run() == FALSE) {
						$arg['status'] = 0;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					} else {
						$usid = $userdata['data']['id'];
						$name = $userdata['data']['name'];
						$lastname = $userdata['data']['lastname'];

						$fullfill_step = $this->input->post('step');
						$json_step_data = $this->input->post('json_step_data');

						if($fullfill_step==1)
						{
							$selectData = $this->dynamic_model->get_query_result('select * from user_registration where status="Active" and user_id="'.$usid.'" ');
							if($selectData)
							{
								$user_registration_update = $this->dynamic_model->updateRowWhere('user_registration',array('user_id'=>$usid),array('fullfill_step'=>$fullfill_step,'step_1_data'=>$json_step_data,'update_dt'=>strtotime(date('d-m-Y H:i:s'))));
							}
							else
							{
								$user_registration_id = $this->dynamic_model->insertdata('user_registration',array('user_id'=>$usid,'fullfill_step'=>$fullfill_step,'step_1_data'=>$json_step_data,'create_dt'=>strtotime(date('d-m-Y H:i:s'))));
							}

							$arg['status']     = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data']       = array();
							$arg['message']    = 'Step 1 successfully added';
						}
						else if($fullfill_step==2)
						{
							$user_registration_update = $this->dynamic_model->updateRowWhere('user_registration',array('user_id'=>$usid),array('fullfill_step'=>$fullfill_step,'step_2_data'=>$json_step_data,'update_dt'=>strtotime(date('d-m-Y H:i:s'))));

							$arg['status']     = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data']       = array();
							$arg['message']    = 'Step 2 successfully added';
						}
						else if($fullfill_step==3)
						{
							$user_registration_update = $this->dynamic_model->updateRowWhere('user_registration',array('user_id'=>$usid),array('fullfill_step'=>$fullfill_step,'step_3_data'=>$json_step_data,'update_dt'=>strtotime(date('d-m-Y H:i:s'))));

							$arg['status']     = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data']       = array();
							$arg['message']    = 'Step 3 successfully added';
						}
						else if($fullfill_step==4)
						{
							$user_registration_update = $this->dynamic_model->updateRowWhere('user_registration',array('user_id'=>$usid),array('fullfill_step'=>$fullfill_step,'step_4_data'=>$json_step_data,'update_dt'=>strtotime(date('d-m-Y H:i:s'))));

							$arg['status']     = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data']       = array();
							$arg['message']    = 'Step 4 successfully added';
						}
						else if($fullfill_step==5)
						{
							$user_registration_update = $this->dynamic_model->updateRowWhere('user_registration',array('user_id'=>$usid),array('fullfill_step'=>$fullfill_step,'step_5_data'=>$json_step_data,'update_dt'=>strtotime(date('d-m-Y H:i:s'))));

							$arg['status']     = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data']       = array();
							$arg['message']    = 'Step 5 successfully added';
						}
						else if($fullfill_step==6)
						{
							$user_registration_update = $this->dynamic_model->updateRowWhere('user_registration',array('user_id'=>$usid),array('fullfill_step'=>$fullfill_step,'step_6_data'=>$json_step_data,'update_dt'=>strtotime(date('d-m-Y H:i:s'))));

							$arg['status']     = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data']       = array();
							$arg['message']    = 'Step 6 successfully added';
						}
						else if($fullfill_step==7)
						{
							$user_registration_update = $this->dynamic_model->updateRowWhere('user_registration',array('user_id'=>$usid),array('fullfill_step'=>$fullfill_step,'step_7_data'=>$json_step_data,'update_dt'=>strtotime(date('d-m-Y H:i:s'))));

							$arg['status']     = 1;
							$arg['error_code'] = REST_Controller::HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['data']       = array();
							$arg['message']    = 'Step 7 successfully added';
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function get_user_registration_step_get() {
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			
				$userdata = checkuserid();
				if ($userdata['status'] != 1) {
					$arg = $userdata;
				} else {

					$usid = $userdata['data']['id'];
					$name = $userdata['data']['name'];
					$lastname = $userdata['data']['lastname'];


				$findresult = $this->dynamic_model->get_query_result('select * from user_registration where status="Active" and user_id="'.$usid.'" ');
		        //var_dump($findresult); die;
		        $data = array();
		        $data_array = array();
		        if(count($findresult)>0)
		        {
		          foreach ($findresult as $key => $value) 
		          {
		            $data['id']          = $value->id;
		            $data['user_id']     = $value->user_id;
		            $data['step_1_data'] = $value->step_1_data;
		            $data['step_2_data'] = $value->step_2_data;
		            $data['step_3_data'] = $value->step_3_data;
		            $data['step_4_data'] = $value->step_4_data;
		            $data['step_5_data'] = $value->step_5_data;
		            $data['step_6_data'] = $value->step_6_data;
		            $data['step_7_data'] = $value->step_7_data;
		            $data['check_out_data'] = $value->check_out_data;
		            $data['fullfill_step']  = $value->fullfill_step;


		            $data_array[] = $data;
		          }

		        }

				$arg['status']     = 1;
				$arg['error_code'] = REST_Controller::HTTP_OK;
				$arg['error_line'] = __line__;
				$arg['data']       = $data_array;
				$arg['message']    = $this->lang->line('record_found');
				
			}
		}
		echo json_encode($arg);
	}

	public function signature_image_upload_post() 
	{
		$arg = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$userdata = checkuserid();
			if ($userdata['status'] != 1) {
				$arg = $userdata;
			} else {
				

					if(isset($_FILES['signature_image']['name']) && $_FILES['signature_image']['name']!="")
					{
						if(is_uploaded_file($_FILES['signature_image']['tmp_name']))
						{
							$signature_image = $_FILES['signature_image']['name'];
							$path="uploads/signature_image/".$signature_image;			
							move_uploaded_file($_FILES['signature_image']['tmp_name'],$path);
						}

						//$userdata['signature_image']=$signature_image;

						$img = site_url().'uploads/signature_image/'.$signature_image;

						$arg['status']    = 1;
						$arg['error_code']= REST_Controller::HTTP_OK;
				  		$arg['error_line']= __line__;
				  		$arg['message']   = 'successfully Uploaded';
				  		$arg['data'] 	  = array('signature_image_url'=>$img);
					}
					else
					{
						$arg['status']    = 1;
						$arg['error_code']= REST_Controller::HTTP_OK;
				  		$arg['error_line']= __line__;
				  		$arg['message']   = 'Image Not Uploaded';
				  		$arg['data'] 	  = array();
					}
				
			}
	  	}
		echo json_encode($arg);
	}

}
