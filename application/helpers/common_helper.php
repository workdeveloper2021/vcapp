<?php
defined('BASEPATH') or exit('No direct script access allowed');
$this->lang->load("message", "english");
$this->load->library('plivo');
//admin helper function  start

function dateDiffInDays($date1, $date2)
{
	$diff = strtotime($date2) - strtotime($date1);
	return abs(round($diff / 86400));
}

if (!function_exists('loginCheck')) {
	function loginCheck()
	{

		$CI = get_instance();
		if (loggedId()) {
			return TRUE;
		} else {
			//this code is user for clear cache
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			$CI->messages->setMessage('Please login first.', 'pageerror');
			redirect('login');
		}
	}
}
function generatePassword($len)
{
	$lower = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z');
	$upper = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
	$specials = array('!', '#', '$', '%', '@');
	$digits = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
	$all = array($lower, $upper, $specials, $digits);

	$pwd = $lower[array_rand($lower, 1)];
	$pwd = $pwd . $upper[array_rand($upper, 1)];
	$pwd = $pwd . $specials[array_rand($specials, 1)];
	$pwd = $pwd . $digits[array_rand($digits, 1)];

	for ($i = strlen($pwd); $i < max(8, $len); $i++) {
		$temp = $all[array_rand($all, 1)];
		$pwd = $pwd . $temp[array_rand($temp, 1)];
	}
	return str_shuffle($pwd);
}
function isValidTimezoneId($timezoneId)
{
	$savedZone = date_default_timezone_get(); # save current zone
	$res = $savedZone == $timezoneId; # it's TRUE if param matches current zone
	if (!$res) { # 0r...
		@date_default_timezone_set($timezoneId); # try to set new timezone
		$res = date_default_timezone_get() == $timezoneId; # it's true if new timezone set matches param string.
	} else {
		return false;
	}
	date_default_timezone_set($savedZone); # restore back old timezone
	return $res; # set result
}

/*
	 * @descript: This function is used to Upload an image
	 * @return config setting
	 */

function set_upload_options($dirPath, $allowedTypes, $fileName = "")
{
	//  upload an image and document options
	$config = array();
	$config['upload_path'] = $dirPath;
	$config['allowed_types'] = $allowedTypes;
	$config['overwrite'] = FALSE;

	if ($fileName != "") {
		$config['file_name'] = $fileName;
	}

	return $config;
}




if (!function_exists('check_expiry_year')) {
	function check_expiry_year($expiry_year)
	{
		$cur_year = date('Y');
		if ($expiry_year >= $cur_year) {
			return true;
		} else {
			return false;
		}
	}
}

if (!function_exists('check_expiry_month_year')) {
	function check_expiry_month_year($expiry_month, $expiry_year)
	{
		$cur_year  = date('Y');
		$cur_month = date('m');
		if ($expiry_year >= $cur_year) {
			if ($expiry_year == $cur_year) {
				if ($expiry_month >= $cur_month)
					return true;
				else
					return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	}
}
if (!function_exists('check_expiry_day_month_year')) {
	function check_expiry_day_month_year($date = '')
	{
		$cur_date  = date('Y-m-d');

		if ($date >= $cur_date) {
			return true;
		} else {
			return false;
		}
	}
}
if (!function_exists('get_str_to_time')) {
	function get_str_to_time($time)
	{
		$date = date('Y-m-d');
		$combinedDT = date('Y-m-d H:i:s', strtotime($time));
		$combinedDT = strtotime($combinedDT);
		return $combinedDT;
	}
}
if (!function_exists('check_authorization')) {
	//Check Auth for customer or merchant
	function check_authorization($logout = NULL)
	{
		$ci = &get_instance();
		$ci->load->model('dynamic_model');
		$ci->lang->load("message", "english");

		$auth_token = $ci->input->get_request_header('Authorization');
		$user_token = json_decode(base64_decode($auth_token));
		if (!empty($user_token)) {
			$usid     =  $user_token->userid;
			$auth_key =  $user_token->token;
			if ($usid != '' && $auth_key != '') {
				$condition = array(
					'user_id' => $usid,
					'token' => $auth_key
				);
				$loguser = $ci->dynamic_model->getdatafromtable('users', $condition);
				//echo $ci->db->last_query();die;
				//print_r($loguser);die;
				if ($loguser) {
					//if($usid == $loguser[0]['id'] && $auth_key == $loguser[0]['token']) {
					if ($usid == $loguser[0]['user_id'] && $auth_key == $loguser[0]['token'] && $loguser[0]['status'] == 'Active') {

						if (!empty($logout)) {
							$data2 = array(
								'token' => '',
								'device_id'   => '',
								'device_type' => ''
								//'Is_LoggedIn' => '0'
							);
							$wheres = array("user_id" => $usid);
							$result = $ci->dynamic_model->updateRowWhere("users", $wheres, $data2);

							return $ci->lang->line('logout_success');
						} else {
							return true;
						}
					} else {
						return $ci->lang->line('session_expire');
					}
				} else {
					return $ci->lang->line('varify_token_userid');
				}
			} else {
				return $ci->lang->line('header_required');
			}
		} else {
			return $ci->lang->line('header_required');
		}
	}
}
if (!function_exists('getuniquenumber')) {
	function getuniquenumber()
	{
		//////////////////GENERATE TRX #
		$a1 = date("ymd", time());
		$a2 = rand(100, 999);
		$u = substr(uniqid(), 7);
		$c = chr(rand(97, 122));
		$c2 = chr(rand(97, 122));
		$c3 = chr(rand(97, 122));
		$ok = "$c$u$c2$a2$c3";
		$txn_id = strtoupper($ok);
		return $txn_id;
		//////////////////GENERATE TRX #
	}
}
/* function used for encrypt password with sha512  */
if (!function_exists('encrypt_password')) {
	function encrypt_password($password)
	{
		$ci     = &get_instance();
		$key    = $ci->config->item('encryption_key');
		$salt1  = hash('sha512', $key . $password);
		$salt2  = hash('sha512', $password . $key);
		$hashed_password = hash('sha512', $salt1 . $password . $salt2);
		return $hashed_password;
	}
}

function image_check($image, $url, $flag = '')
{
	$CI = &get_instance();
	$filename = "$url$image";

	if (!empty($image)) {
		if (@getimagesize($filename)) {
			return $filename;
		} else {
			return ($flag == '') ? $url . 'userdefault.png' : false;
		}
	} else {
		return ($flag == '') ? $url . 'userdefault.png' : false;
	}
}
/* * ********Encrypt******* */
function hash_password($password)
{
	return password_hash($password, PASSWORD_BCRYPT);
}
/* * *******Compare******** */
function verify_password_hash($password, $hash)
{
	return password_verify($password, $hash) ? "verified" : "invalid";
}

function update_data($table = null, $data = array(), $where = array())
{
	$ci = &get_instance();
	$ci->db->update($table, $data, $where);
	if ($ci->db->affected_rows() > 0)
		return true;
	else
		return false;
}
///** * create a encoded id for sequrity pupose  */
if (!function_exists('encode_id')) {
	function encode_id($id, $salt)
	{
		$ci = &get_instance();
		$id = $ci->encrypt->encode($id . $salt);
		$id = str_replace("=", "~", $id);
		$id = str_replace("+", "_", $id);
		$id = str_replace("/", "-", $id);
		return $id;
	}
}
/** * decode the id which made by encode_id() */
if (!function_exists('decode_id')) {
	function decode_id($id, $salt)
	{
		$ci = &get_instance();
		$id = str_replace("_", "+", $id);
		$id = str_replace("~", "=", $id);
		$id = str_replace("-", "/", $id);
		$id = $ci->encrypt->decode($id);
		if ($id && strpos($id, $salt) !== false) {
			return str_replace($salt, "", $id);
		}
	}
}



function makeslug($slugdata)
{
	$title = $slugdata;
	$title = trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-zA-Z0-9_.]/', ' ', $title)));
	return strtolower(str_replace(' ', '_', $title));
}

// Get table data
function getdatafromtable($tbnm, $condition = array(), $data = '*', $limit = '', $offset = '')
{
	$CI = get_instance();
	$CI->load->model('dynamic_model');
	$result = $CI->dynamic_model->getdatafromtable($tbnm, $condition, $data, $limit, $offset);
	return $result;
}
function get_options($value)
{
	$CI = get_instance();
	$CI->load->model('dynamic_model');
	$condition = array('option_name' => $value);
	$result = $CI->dynamic_model->getoptions($condition);
	return $result[0]['option_value'];
}
// Get Table record count
function getdatacount($tbnm, $condition = array())
{
	$CI = get_instance();
	$CI->load->model('dynamic_model');
	$result = $CI->dynamic_model->countdata($tbnm, $condition);
	return $result[0]['counting'];
}
/* * ********** Email Function  ************* */
if (!function_exists('email_function')) {
	function email_function($to, $subject, $msg, $cc = '', $attachemt = '')
	{
		$CI = get_instance();
		$CI->load->library('email');
		$CI->email->from('prathak.godawat@consagous.com', 'Kohdy');
		$CI->email->to($to);
		$CI->email->subject($subject);
		$CI->email->message($msg);
		$CI->email->set_mailtype('html');
		if ($attachemt != '') {
			$CI->email->attach($attachemt);
		}
		if ($CI->email->send()) {
			$result = "1";
		} else {
			$result = "0";
		}
		return $result;
	}
}

// Get user role Name using Role ID
if (!function_exists('get_role_name')) {
	function get_role_name($roleid)
	{
		$CI = get_instance();
		$CI->load->model('dynamic_model');
		$condition = array('role_id' => $roleid);
		$result = $CI->dynamic_model->getdatafromtable('manage_roles', $condition, 'role_name');
		return $result[0]['role_name'];
	}
}

// Get slug info

if (!function_exists('get_slug_name')) {
	function get_slug_name($slug_name = '')
	{
		$CI = get_instance();
		$CI->load->model('dynamic_model');
		$condition = array('slug' => $slug_name);
		$result = $CI->dynamic_model->getdatafromtable('static_page', $condition, 'slug');
		if (!empty($result)) {
			return false;
		} else {
			return true;
		}
	}
}


// Get Limited Words
if (!function_exists('limit_words')) {
	function limit_words($string, $word_limit)
	{
		$words = explode(" ", $string);
		return implode(" ", array_splice($words, 0, $word_limit));
	}
}

// Get Date with right format
if (!function_exists('get_formated_date')) {
	function get_formated_date($timestramdate, $type = '')
	{
		if ($type == '1') {
			$formated_date = date("d M-Y - h:i A", strtotime($timestramdate));
		} elseif ($type == '2') {
			$formated_date = date('d M-Y', $timestramdate);
		} else {
			$formated_date = $timestramdate; //date("d M-Y - h:i A",$timestramdate);
		}
		return $formated_date;
	}
}

// Get Date with right format
if (!function_exists('get_date')) {
	function get_date($timestramdate, $type = '')
	{
		if ($type == '1') {
			$formated_date = date("d M-y ", strtotime($timestramdate));
		} else {
			$formated_date = date("d M-y", $timestramdate);
		}
		return $formated_date;
	}
}

// Version Check API

function version_check_helper()
{
	$arg = array();
	$CI = get_instance();
	$version_code =  $CI->input->get_request_header('version', true);

	if (!empty($version_code)) {
		$api_version =  config_item('api_version');
		$api_forcefully =  config_item('api_forcefully');
		if ($version_code >= $api_version) {
			$arg['status'] = 1;
			$arg['code'] = '465';
			$arg['message'] = 'App is Uptodate';
		} else {
			if ($api_forcefully) {
				$arg['status'] = 0;
				$arg['code'] = '467';
				$arg['message'] = 'Major Update Available';
			} else {
				$arg['status'] = 0;
				$arg['code'] = '466';
				$arg['message'] = 'Minor Update Available';
			}
		}
	} else {
		$arg['status'] = 0;
		$arg['message'] = 'Please Send App Version';
	}
	return $arg;
}
function version_check_helper1()
{
	$arg = array();
	$CI = get_instance();
	$version_code =  $CI->input->get_request_header('version', true);

	if (!empty($version_code)) {
		$api_version =  config_item('api_version');
		$api_forcefully =  config_item('api_forcefully');
		if ($version_code >= $api_version) {
			$arg['status'] = 1;
			$arg['error_code'] = '465';
			$arg['error_line'] = __line__;
			$arg['message'] = 'App is Up to date';
			$arg['data']  = json_decode('{}');
		} else {
			if ($api_forcefully) {
				$arg['status'] = 0;
				$arg['error_code'] = '467';
				$arg['error_line'] = __line__;
				$arg['message'] = 'Major Update Available';
				$arg['data']  = json_decode('{}');
			} else {
				$arg['status'] = 0;
				$arg['error_code'] = '466';
				$arg['error_line'] = __line__;
				$arg['message'] = 'Minor Update Available';
				$arg['data']  = json_decode('{}');
			}
		}
	} else {
		$arg['status'] = 0;
		$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
		$arg['error_line'] = __line__;
		$arg['message'] = 'Please Send App Version';
		$arg['data']  = json_decode('{}');
	}
	return $arg;
}




// Get single Validation Error for API
function get_form_error($error)
{
	if (count($error) > 0) {
		foreach ($error as $key => $value) {
			return $value;
			break;
		}
	}
}

// Get ip_info
if (!function_exists('ip_info')) {
	function ip_info($ip = NULL, $purpose = "location", $deep_detect = TRUE)
	{
		$output = NULL;
		if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
			$ip = $_SERVER["REMOTE_ADDR"];
			$ip = $_SERVER["HTTP_HOST"];
			if ($deep_detect) {
				if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
					$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
				if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
					$ip = $_SERVER['HTTP_CLIENT_IP'];
			}
		}
		$purpose    = str_replace(array("name", "\n", "\t", " ", "-", "_"), NULL, strtolower(trim($purpose)));
		$support    = array("country", "countrycode", "state", "region", "city", "location", "address");
		$continents = array(
			"AF" => "Africa",
			"AN" => "Antarctica",
			"AS" => "Asia",
			"EU" => "Europe",
			"OC" => "Australia (Oceania)",
			"NA" => "North America",
			"SA" => "South America"
		);
		if (filter_var($ip, FILTER_VALIDATE_IP) && in_array($purpose, $support)) {
			$ipdat = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=" . $ip));
			if (@strlen(trim($ipdat->geoplugin_countryCode)) == 2) {
				switch ($purpose) {
					case "location":
						$output = array(
							"city"           => @$ipdat->geoplugin_city,
							"state"          => @$ipdat->geoplugin_regionName,
							"country"        => @$ipdat->geoplugin_countryName,
							"country_code"   => @$ipdat->geoplugin_countryCode,
							"continent"      => @$continents[strtoupper($ipdat->geoplugin_continentCode)],
							"continent_code" => @$ipdat->geoplugin_continentCode
						);
						break;
					case "address":
						$address = array($ipdat->geoplugin_countryName);
						if (@strlen($ipdat->geoplugin_regionName) >= 1)
							$address[] = $ipdat->geoplugin_regionName;
						if (@strlen($ipdat->geoplugin_city) >= 1)
							$address[] = $ipdat->geoplugin_city;
						$output = implode(", ", array_reverse($address));
						break;
					case "city":
						$output = @$ipdat->geoplugin_city;
						break;
					case "state":
						$output = @$ipdat->geoplugin_regionName;
						break;
					case "region":
						$output = @$ipdat->geoplugin_regionName;
						break;
					case "country":
						$output = @$ipdat->geoplugin_countryName;
						break;
					case "countrycode":
						$output = @$ipdat->geoplugin_countryCode;
						break;
				}
			}
		}
		return $output;
	}
}

// Get OS
if (!function_exists('getOS')) {
	function getOS()
	{
		global $user_agent;
		$os_platform = "Unknown OS Platform";

		$os_array    = array(
			'/windows nt 10/i'     =>  'Windows 10',
			'/windows nt 6.3/i'     =>  'Windows 8.1',
			'/windows nt 6.2/i'     =>  'Windows 8',
			'/windows nt 6.1/i'     =>  'Windows 7',
			'/windows nt 6.0/i'     =>  'Windows Vista',
			'/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
			'/windows nt 5.1/i'     =>  'Windows XP',
			'/windows xp/i'         =>  'Windows XP',
			'/windows nt 5.0/i'     =>  'Windows 2000',
			'/windows me/i'         =>  'Windows ME',
			'/win98/i'              =>  'Windows 98',
			'/win95/i'              =>  'Windows 95',
			'/win16/i'              =>  'Windows 3.11',
			'/macintosh|mac os x/i' =>  'Mac OS X',
			'/mac_powerpc/i'        =>  'Mac OS 9',
			'/linux/i'              =>  'Linux',
			'/ubuntu/i'             =>  'Ubuntu',
			'/iphone/i'             =>  'iPhone',
			'/ipod/i'               =>  'iPod',
			'/ipad/i'               =>  'iPad',
			'/android/i'            =>  'Android',
			'/blackberry/i'         =>  'BlackBerry',
			'/webos/i'              =>  'Mobile'
		);

		foreach ($os_array as $regex => $value) {
			if (preg_match($regex, $user_agent)) {
				$os_platform = $value;
			}
		}
		return $os_platform;
	}
}

if (!function_exists('getBrowser')) {
	function getBrowser()
	{
		$user_agent     = $_SERVER['HTTP_USER_AGENT'];
		$browser        = "Unknown Browser";
		$browser_array  = array(
			'/msie/i'       =>  'Internet Explorer',
			'/firefox/i'    =>  'Firefox',
			'/safari/i'     =>  'Safari',
			'/chrome/i'     =>  'Chrome',
			'/edge/i'       =>  'Edge',
			'/opera/i'      =>  'Opera',
			'/netscape/i'   =>  'Netscape',
			'/maxthon/i'    =>  'Maxthon',
			'/konqueror/i'  =>  'Konqueror',
			'/mobile/i'     =>  'Handheld Browser'
		);

		foreach ($browser_array as $regex => $value) {
			if (preg_match($regex, $user_agent)) {
				$browser = $value;
			}
		}
		return $browser;
	}
}

/****************Function checkuserid**********************
 * @type            : Function
 * @Author          : Aamir
 * @function name   : checkuserid
 * @description     : Check user id in header.
 * @param           : null
 * @return          : null
 * ********************************************************** */

function checkuserid($datatype = '')
{
	//$datatype 1 means array default object for zero status case
	if ($datatype == '') {
		$datatype = array(); //json_decode('{}');
	} elseif ($datatype == '1') {
		$datatype = array();
	} else {
		$datatype = '';
	}
	$arg = array();
	$CI = get_instance();
	$userid =  $CI->input->get_request_header('userid', true);
	$parentId =  $CI->input->get_request_header('parentId', true);
	$role =  $CI->input->get_request_header('role', true);

	if (!empty($userid)) {
		$data = getuserdetail($userid, $role, $parentId);
		if ($data) {
			$email_verified = $data['email_verified'];
			$mobile_verified = $data['mobile_verified'];
			$status = $data['status'];

			/*if ($email_verified != 1) {
        $arg['status']     = 0;
		$arg['error_code']  = EMAILNOTVERIFED;
		$arg['error_line']= __line__;
		$arg['message']    = $CI->lang->line('email_not_varify');
		$arg['data']     = $data;
		return $arg;
		exit();
        }

        if ($mobile_verified != 1) {
        $arg['status']     = 0;
		$arg['error_code']  = MOBILENOTVERIFIED;
		$arg['error_line']= __line__;
		$arg['message']    = $CI->lang->line('otp_not_verify');
		$arg['data']     = $data;
		return $arg;
		exit();
        }

		if ($status == 'Deactive') {
		$arg['status']    = 0;
		$arg['message']   = $CI->lang->line('user_deactive');
		$arg['error_code'] = ERROR_AUTHORIZATION_CODE;
		$arg['error_line']= __line__;
		$arg['data']      =  $datatype;
		return $arg;
		exit();
		}*/

			$arg['status'] = 1;
			$arg['error_code'] = REST_Controller::HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['message'] = '';
			$arg['data']  = $data;
		} else {
			$arg['status']    = 0;
			$arg['error_code'] = ERROR_AUTHORIZATION_CODE;
			$arg['error_line'] = __line__;
			$arg['message']   = $CI->lang->line('invalid_detail');
			$arg['data']      =  $datatype;
		}
	} else {
		$arg['status'] = 0;
		$arg['error_code'] = ERROR_AUTHORIZATION_CODE;
		$arg['error_line'] = __line__;
		$arg['message'] = 'Please Send Userid';
		$arg['data']  =  $datatype;
	}
	return $arg;
}






function checkusertoken($datatype = '')
{
	//$datatype 1 means array default object for zero status case
	if ($datatype == '') {
		$datatype = array(); //json_decode('{}');
	} elseif ($datatype == '1') {
		$datatype = array();
	} else {
		$datatype = '';
	}
	$arg = array();
	$CI = get_instance();
	$userid =  $CI->input->get_request_header('authorization', true);

	// print_r($CI->input->get_request_header('authorization')); die;

	if (!empty($userid)) {

		if(!empty(base64_decode($userid)) && !empty(json_decode(base64_decode($userid)))){
			$skipped_registration = !empty(json_decode(base64_decode($userid))->skipped_registration)?json_decode(base64_decode($userid))->skipped_registration:false;
			$userid = json_decode(base64_decode($userid))->userid;
		}else{
			$userid = NULL;
		}

		$data = getuserdetail($userid);
		if (!empty($userid) && $data) {
			// $email_verified = $data['email_verified'];
			// $mobile_verified = $data['mobile_verified'];
			// $status = $data['status'];

			/*if ($email_verified != 1) {
        $arg['status']     = 0;
		$arg['error_code']  = EMAILNOTVERIFED;
		$arg['error_line']= __line__;
		$arg['message']    = $CI->lang->line('email_not_varify');
		$arg['data']     = $data;
		return $arg;
		exit();
        }

        if ($mobile_verified != 1) {
        $arg['status']     = 0;
		$arg['error_code']  = MOBILENOTVERIFIED;
		$arg['error_line']= __line__;
		$arg['message']    = $CI->lang->line('otp_not_verify');
		$arg['data']     = $data;
		return $arg;
		exit();
        }

		if ($status == 'Deactive') {
		$arg['status']    = 0;
		$arg['message']   = $CI->lang->line('user_deactive');
		$arg['error_code'] = ERROR_AUTHORIZATION_CODE;
		$arg['error_line']= __line__;
		$arg['data']      =  $datatype;
		return $arg;
		exit();
		}*/

			$data['skipped_registration'] = $skipped_registration;

			$arg['status'] = 1;
			$arg['error_code'] = REST_Controller::HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['message'] = '';
			$arg['data']  = $data;
		} else {
			$arg['status']    = 0;
			$arg['error_code'] = ERROR_AUTHORIZATION_CODE;
			$arg['error_line'] = __line__;
			// $arg['message']   = $CI->lang->line('invalid_detail');
			$arg['message']   = 'Invalid token details';
			$arg['data']      =  $datatype;
		}
	} else {
		$arg['status'] = 0;
		$arg['error_code'] = ERROR_AUTHORIZATION_CODE;
		$arg['error_line'] = __line__;
		$arg['message'] = 'Please Send Auth Token In Headers';
		$arg['data']  =  $datatype;
	}
	return $arg;
}






function checkuserprofile($datatype = '')
{
	//$datatype 1 means array default object for zero status case
	if ($datatype == '') {
		$datatype = array(); //json_decode('{}');
	} elseif ($datatype == '1') {
		$datatype = array();
	} else {
		$datatype = '';
	}
	$arg = array();
	$CI = get_instance();
	$userid =  $CI->input->get_request_header('userid', true);
	$parentId =  $CI->input->get_request_header('parentId', true);
	$role =  $CI->input->get_request_header('role', true);

	if (!empty($userid)) {
		$data = getuserdetail($userid, $role, $parentId);
		if ($data) {
			$email_verified = $data['email_verified'];
			$mobile_verified = $data['mobile_verified'];
			$status = $data['status'];

			$arg['status'] = 1;
			$arg['error_code'] = REST_Controller::HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['message'] = '';
			$arg['data']  = $data;
		} else {
			$arg['status']    = 0;
			$arg['error_code'] = ERROR_AUTHORIZATION_CODE;
			$arg['error_line'] = __line__;
			$arg['message']   = $CI->lang->line('invalid_detail');
			$arg['data']      =  $datatype;
		}
	} else {
		$arg['status'] = 0;
		$arg['error_code'] = ERROR_AUTHORIZATION_CODE;
		$arg['error_line'] = __line__;
		$arg['message'] = 'Please Send Userid';
		$arg['data']  =  $datatype;
	}
	return $arg;
}


function parentuserprofile($datatype = '')
{
	//$datatype 1 means array default object for zero status case
	if ($datatype == '') {
		$datatype = array(); //json_decode('{}');
	} elseif ($datatype == '1') {
		$datatype = array();
	} else {
		$datatype = '';
	}
	$arg = array();
	$CI = get_instance();
	$userid =  $CI->input->get_request_header('userid', true);
	$parentId =  $CI->input->get_request_header('parentId', true);
	$role =  $CI->input->get_request_header('role', true);

	if (!empty($userid)) {
		$data = getuserdetail($parentId, $role, $userid);
		if ($data) {
			$email_verified = $data['email_verified'];
			$mobile_verified = $data['mobile_verified'];
			$status = $data['status'];

			$arg['status'] = 1;
			$arg['error_code'] = REST_Controller::HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['message'] = '';
			$arg['data']  = $data;
		} else {
			$arg['status']    = 0;
			$arg['error_code'] = ERROR_AUTHORIZATION_CODE;
			$arg['error_line'] = __line__;
			$arg['message']   = $CI->lang->line('invalid_detail');
			$arg['data']      =  $datatype;
		}
	} else {
		$arg['status'] = 0;
		$arg['error_code'] = ERROR_AUTHORIZATION_CODE;
		$arg['error_line'] = __line__;
		$arg['message'] = 'Please Send Userid';
		$arg['data']  =  $datatype;
	}
	return $arg;
}


/****************Function getuserdetail*******************
 * @type            : Function
 * @Author          : Aamir
 * @function name   : getuserdetail
 * @description     : get all user details.
 * @param           : null
 * @return          : null
 * ********************************************************** */

function getuserdetail_old($id)
{

	$CI = &get_instance();
	$CI->db->select('*');
	$CI->db->from('user');
	$CI->db->where('id', $id);
	$datauser = $CI->db->get()->result_array();
	$datauser1 = @$datauser[0];
	if ($datauser1['id']) {
		$full_name = $datauser1['name'];
		$lastname = $datauser1['lastname'];
		$user_id = $datauser1['id'];
		$email = $datauser1['email'];
		$email_verified = $datauser1['email_verified'];
		$mobile_verified = $datauser1['mobile_verified'];
		$status = $datauser1['status'];
		$password = $datauser1['password'];
		$zipcode = $datauser1['zipcode'];
		$country = $datauser1['country'];
		$country_code = (!empty($datauser1['country_code'])) ? $datauser1['country_code'] : "";
		$emer_country_code = (!empty($datauser1['emergency_country_code'])) ? $datauser1['emergency_country_code'] : "";
		$state = $datauser1['state'];
		$city = $datauser1['city'];
		$address = $datauser1['address'];
		$location = (!empty($datauser1['location'])) ? $datauser1['location'] : '';
		$lat = (!empty($datauser1['lat'])) ? $datauser1['lat'] : '';
		$lang = (!empty($datauser1['lang'])) ? $datauser1['lang'] : '';
		$gender = $datauser1['gender'];
		$emergency_contact_person = (!empty($datauser1['emergency_contact_person'])) ? $datauser1['emergency_contact_person'] : '';
		$emergency_contact_no = (!empty($datauser1['emergency_contact_no'])) ? $datauser1['emergency_contact_no'] : '';
		$notification = !empty($datauser1['notification']) ? json_decode($datauser1['notification']) : "";
		$age = !empty($datauser1['date_of_birth']) ? date('Y') - date('Y', strtotime($datauser1['date_of_birth'])) : "";

		//image detail
		if ($datauser1['profile_img']) {
			$img = site_url() . 'uploads/user/' . $datauser1['profile_img'];
			$imgname = pathinfo($img, PATHINFO_FILENAME);
			$ext = pathinfo($img, PATHINFO_EXTENSION);
			$thumb = site_url() . 'uploads/user/' . $imgname . '_thumb.' . $ext;
		}
		$thumburl = !empty($thumb) ? $thumb : site_url() . 'uploads/userdefault.png';
		$imgurl = !empty($img) ? $img : site_url() . 'uploads/userdefault.png';
		//for instructor
		if ($datauser1['role_id'] == 4) {
			$other_info = getdatafromtable("instructor_details", array("user_id" => $datauser1['id']));
			$skill = (!empty($other_info[0]['skill'])) ? get_categories($other_info[0]['skill']) : "";
			$experience =  (!empty($other_info[0]['total_experience'])) ? $other_info[0]['total_experience'] : "";
			$appointment_fees_type =   (!empty($other_info[0]['appointment_fees_type'])) ? $other_info[0]['appointment_fees_type'] : "";
			$appointment_fees =   (!empty($other_info[0]['appointment_fees'])) ? $other_info[0]['appointment_fees'] : "";
			$about =   (!empty($other_info[0]['about'])) ? $other_info[0]['about'] : "";
			return array("id" => $user_id, "name" => $full_name, "lastname" => $lastname, "email" => $email, "password" => $password, "mobile" => $datauser1['mobile'], "role_id" => $datauser1['role_id'], "date_of_birth" => $datauser1['date_of_birth'], 'age' => $age, 'profile_img' => $imgurl, 'thumb' => $thumburl, 'email_verified' => $email_verified, 'mobile_verified' => $mobile_verified, 'status' => $status, 'notification' => $notification, 'zipcode' => $zipcode, 'country' => $country, 'country_code' => $country_code, 'state' => $state, 'city' => $city, 'address' => $address, 'lat' => $lat, 'lang' => $lang, 'emergency_contact_person' => $emergency_contact_person, 'emergency_contact_no' => $emergency_contact_no, 'emergency_country_code' => $emer_country_code, 'address' => $address, 'skill' => $skill, 'experience' => $experience, 'appointment_fees_type' => $appointment_fees_type, 'appointment_fees' => $appointment_fees, 'street' => $location, 'gender' => $gender, 'about' => $about);
		} else {
			//for users
			return array("id" => $user_id, "name" => $full_name, "lastname" => $lastname, "email" => $email, "password" => $password, "mobile" => $datauser1['mobile'], "role_id" => $datauser1['role_id'], "date_of_birth" => $datauser1['date_of_birth'], 'age' => $age, 'profile_img' => $imgurl, 'thumb' => $thumburl, 'email_verified' => $email_verified, 'mobile_verified' => $mobile_verified, 'status' => $status, 'notification' => $notification, 'zipcode' => $zipcode, 'country' => $country, 'country_code' => $country_code, 'state' => $state, 'city' => $city, 'address' => $address, 'lat' => $lat, 'lang' => $lang, 'emergency_contact_person' => $emergency_contact_person, 'emergency_contact_no' => $emergency_contact_no, 'emergency_country_code' => $emer_country_code, 'street' => $location, 'gender' => $gender);
		}
	} else {
		return array();
	}
}
function getuserdetail($id = '', $role = '', $parentId = '')
{
	$ci = &get_instance();
	$ci->load->model('dynamic_model');
	if (empty($role)) {
		$condition = array('user.id' => $id);
	} else {
		$condition = array('user.id' => $id, 'user_role.role_id' => $role);
	}
	$on = 'user_role.user_id = user.id';
	$datauser = $ci->dynamic_model->getTwoTableData('user.*,user_role.role_id', 'user', 'user_role', $on, $condition);
	$datauser1 = @$datauser[0];
	if (@$datauser1['id']) {
		$id = $datauser1['id'];
		$full_name = $datauser1['name'];
		$created_by = $datauser1['created_by'];
		$user_id = $datauser1['id'];
		$email = $datauser1['email'];
		$created_by = $datauser1['created_by'];
		//echo $mobile; die;
		$email_verified = $datauser1['email_verified'];
		$status = $datauser1['status'];
		$password = $datauser1['password'];
		$notification = !empty($datauser1['notification']) ? json_decode($datauser1['notification']) : "";

		//image detail
		if ($datauser1['profile_img']) {
			$img = site_url() . 'uploads/user/' . $datauser1['profile_img'];
			$imgname = pathinfo($img, PATHINFO_FILENAME);
			$ext = pathinfo($img, PATHINFO_EXTENSION);
			$thumb = site_url() . 'uploads/user/' . $imgname . '_thumb.' . $ext;
		}

		$default_img = $full_name ? $full_name : 'u';
		$default_img = strtolower(substr($default_img, 0, 1));
		$image = $default_img . '.png';

		$thumburl = !empty($thumb) ? $thumb : site_url() . 'uploads/user/' . $image;
		$imgurl = !empty($img) ? $img : site_url() . 'uploads/user/' . $image;

			//for users
			$gfm = get_family_member($parentId, $user_id);
			return array("id" => $user_id, "created_by" => $created_by, "name" => $full_name, "email" => $email, "password" => $password, "role_id" => $datauser1['role_id'], 'profile_img' => $imgurl, 'thumb' => $thumburl, 'email_verified' => $email_verified, 'status' => $status, 'notification' => $notification);
	} else {
		return array();
	}
}

function get_family_member($parentId, $user_id)
{

	if (empty($parentId)) {
		$parentIdUser = $user_id;
	} else {
		$parentIdUser = $parentId;
	}

	$CI = &get_instance();
	$condition = array("created_by" => $parentIdUser, "is_deleted" => '0');
	$member_data = $CI->dynamic_model->getdatafromtable('user', $condition, '*');
	$response = array();
	if (!empty($member_data)) {
		foreach ($member_data as $value) {
			$response[] = array(
				'memeber_id' => $value['id'],
				'id' => $value['id'],
				'member_name' => ucwords($value['name']),
				'image' => base_url() . 'uploads/user/' . $value['profile_img'],
				'relation' => get_family_name($value['relation_id']),
				'relative_id' => $value['created_by'],
				'email' => $value['email'],
				'dob' => $value['date_of_birth'],
				'gender' => $value['gender'],
				'create_dt' => date("d M Y ", $value['create_dt']),

			);
		}
	}

	/*if(!empty($parentId)){
				$condition=array("id"=>$parentId);
				$member_data= $CI->dynamic_model->getdatafromtable('user',$condition,'*');
				if(!empty($member_data)){
				    foreach($member_data as $value)
				    {
				    	$response[] = array('memeber_id' => $value['id'],
				    		'id' => $value['id'],
				    		'member_name' => ucwords($value['name']),
				    		'image' => base_url().'uploads/user/'.$value['profile_img'],
				    		'relation' => get_family_name($value['relation_id']),
				    		'relative_id' => $value['created_by'],
				    		'email' => $value['email'],
				    		'dob' => $value['date_of_birth'],
				    		'gender' => $value['gender'],
				    		'create_dt' => date("d M Y ",$value['create_dt']),

				    	 );
				    }
				}
			}*/
	return $response;
}
/****************Function sendEmailCI*******************
 * @type            : Function
 * @Author          : Aamir
 * @function name   : sendEmailCI
 * @description     : Common function to send email.
 * @param           : null
 * @return          : null
 * ********************************************************** */

if (!function_exists('sendEmailCI')) {
	function sendEmailCI($to, $from, $subject = '', $body = '', $attachments = array(), $filePath = '', $cc = '')
	{
		$CI = &get_instance();
		$config = array();
		$config['useragent'] = "CodeIgniter";
		$config['mailpath'] = "/usr/bin/sendmail"; // or "/usr/sbin/sendmail"
		$config['protocol'] = PROTOCOL;
		$config['smtp_host'] = SMTP_HOST;
		$config['smtp_port'] = SMTP_PORT;
		$config['smtp_user'] = SMTP_USER;
		$config['smtp_pass'] = SMTP_PASS;
		// $config['smtp_crypto'] = 'tls';
		$config['smtp_crypto'] = SMTP_CRYPTO;
		$config['mailtype'] = 'html';
		$config['charset'] = 'utf-8';
		$config['newline'] = "\r\n";
		$config['wordwrap'] = TRUE;

		$CI->load->library('email');
		$CI->email->initialize($config);

		$site_email = SITE_EMAIL;
		$site_name = SITE_NAME;

		$CI->email->from($site_email, $site_name);
		$CI->email->to($to);
		if (!empty($cc)) {
			$CI->email->cc($cc);
		}
		$CI->email->subject($subject);
		$CI->email->message($body);

		if (!empty($attachments)) {
			foreach ($attachments as $attachment) {
				$file_path = $filePath ? $filePath : config_item('root_url');
				$CI->email->attach($file_path . $attachment);
			}
		}
		$result = $CI->email->send();

		if ($result) {
			return $result;
		} else {
			// echo $CI->email->print_debugger();
			return false;
		}
	}
}


/****************Function getbusinessdetails*******************
 * @type            : Function
 * @Author          : Aamir
 * @function name   : getbusinessdetails
 * @description     : get all business details.
 * @param           : null
 * @return          : null
 * ********************************************************** */

function getbusinessdetails_old($id = '', $usid = '', $dist = '', $flag = '')
{

	$CI = &get_instance();
	$CI->db->select('*');
	$CI->db->from('business');
	$CI->db->where('id', $id);
	$CI->db->where('status', 'Active');
	$databusiness = $CI->db->get()->result_array();
	// $con=array('business.id'=>$id,'business.status'=>'Active','user.status'=>'Active');
	// $on='business.user_id = user.id';
	// $databusiness = $CI->dynamic_model->getTwoTableData('user.status as user_status,business.*','business','user',$on,$con);
	if (isset($databusiness[0]['id'])) {
		$databusiness = $databusiness[0];

		$getcat = '';
		if ($databusiness['category']) {
			$ids = $databusiness['category'];
			$sql = "SELECT GROUP_CONCAT(category_name) AS category_name FROM manage_category
            WHERE id IN ($ids)";
			$getcat = $CI->dynamic_model->get_query_result($sql);
			$getcat = $getcat[0]->category_name;
		}

		$img = site_url() . 'uploads/business/' . $databusiness['logo'];
		$imgname = pathinfo($img, PATHINFO_FILENAME);
		$ext = pathinfo($img, PATHINFO_EXTENSION);
		$thumb = site_url() . 'uploads/business/' . $imgname . '_thumb.' . $ext;

		$busi_img = site_url() . 'uploads/business/' . $databusiness['business_image'];
		$imgnamebusi = pathinfo($busi_img, PATHINFO_FILENAME);
		$extbusi = pathinfo($busi_img, PATHINFO_EXTENSION);
		$thumb_img = site_url() . 'uploads/business/' . $imgnamebusi . '_thumb.' . $extbusi;

		$distance = (!empty($dist)) ? $dist . ' Km' : '0 Km';
		//Check my favourite status
		//service_type 1 for business
		$where = array("user_id" => $usid, "service_id" => $id, "service_type" => 1);
		$user_favourite = $CI->dynamic_model->getdatafromtable("user_business_favourite", $where);
		$favourite = (!empty($user_favourite)) ? '1' : '0';

		//latest product
		//flag==1 means no product data show
		$product_data = ($flag == 1) ? [] : get_product_list($id);
		return array("business_id" => $id, "business_name" => $databusiness['business_name'], "email" => $databusiness['primary_email'], "address" => $databusiness['address'], "city" => $databusiness['city'], "state" => $databusiness['state'], "country" => $databusiness['country'], "business_phone" => $databusiness['business_phone'], "logo" => $img, "thumb" => $thumb, "business_img" => $busi_img, "business_thumb" => $thumb_img, "skills" => $getcat, "class_categories" => $getcat, "workshop_categories" => $getcat, "instructor_categories" => $getcat, "services_categories" => $getcat, "distance" => $distance, "favourite" => $favourite, "latitude" => $databusiness['lat'], "longitude" => $databusiness['longitude'], "product_details" => $product_data);
	} else {
		return array();
	}
}
function getbusinessdetails($id = '', $usid = '', $dist = '', $flag = '', $pass_details = '', $pass_display_con = '')
{

	$CI = &get_instance();
	$con = array('business.id' => $id, 'business.status' => 'Active', 'user.status' => 'Active');
	$on = 'business.user_id = user.id';
	$databusiness = $CI->dynamic_model->getTwoTableData('user.status as user_status,business.*', 'business', 'user', $on, $con);
	if (isset($databusiness[0]['id'])) {
		$databusiness = $databusiness[0];
		$getcat = '';
		$where = array('type' => 1, 'parent_id !=' => 0);
		$findresult = $CI->dynamic_model->getdatafromtable('business_category', $where, 'category');
		if (!empty($findresult)) {
			$ids = array_column($findresult, 'category');
			$condition = array('status' => 'Active', 'category_type' => 2, 'category_parent !=' => 0);
			$getcat = $CI->dynamic_model->getWhereInData('manage_category', 'id', $ids, $condition, 'GROUP_CONCAT(category_name) AS category_name');
			if (!empty($getcat)) {
				$getcat = $getcat[0]['category_name'];
			}

			$query = "SELECT GROUP_CONCAT(m.category_name) AS category_name FROM `business_category` as b join manage_category as m on b.category = m.id where b.business_id = '" . $id . "'";
			$getSkill = $CI->db->query($query)->result_array();
			if (!empty($getSkill)) {
				$getcat = $getSkill[0]['category_name'];
			}
		}
		$img = site_url() . 'uploads/business/' . $databusiness['logo'];
		$imgname = pathinfo($img, PATHINFO_FILENAME);
		$ext = pathinfo($img, PATHINFO_EXTENSION);
		$thumb = site_url() . 'uploads/business/' . $imgname . '_thumb.' . $ext;

		$busi_img = site_url() . 'uploads/business/' . $databusiness['business_image'];
		$imgnamebusi = pathinfo($busi_img, PATHINFO_FILENAME);
		$extbusi = pathinfo($busi_img, PATHINFO_EXTENSION);
		$thumb_img = site_url() . 'uploads/business/' . $imgnamebusi . '_thumb.' . $extbusi;

		$distance = (!empty($dist)) ? $dist . ' Km' : '0 Km';
		//Check my favourite status
		//service_type 1 for business
		$where = array("user_id" => $usid, "service_id" => $id, "service_type" => 1);
		$user_favourite = $CI->dynamic_model->getdatafromtable("user_business_favourite", $where);
		$favourite = (!empty($user_favourite)) ? '1' : '0';


		$where = array("user_id" => $usid, "business_id" => $id);
		$user_booking = $CI->dynamic_model->getdatafromtable("user_booking", $where);
		$user_booking = (!empty($user_booking)) ? '1' : '0';



		//latest product
		//flag==1 means no product data show
		$product_data = ($flag == 1) ? [] : get_product_list($id);

		if (!empty($pass_details)) {
			$all_pass = get_all_pass($id, $usid, $pass_display_con);
			$k = 1;
		} else {
			$all_pass = array();
			$k = 2;
		}
		return array("user_booking" => $user_booking, "business_id" => $id, "business_name" => $databusiness['business_name'], "email" => $databusiness['primary_email'], "address" => $databusiness['address'], "city" => $databusiness['city'], "state" => $databusiness['state'], "country" => $databusiness['country'], "business_phone" => $databusiness['business_phone'], "logo" => $img, "thumb" => $thumb, "business_img" => $busi_img, "business_thumb" => $thumb_img, "skills" => $getcat, "class_categories" => $getcat, "workshop_categories" => $getcat, "video_categories" => $getcat, "instructor_categories" => $getcat, "services_categories" => $getcat, "distance" => $distance, "favourite" => $favourite, "latitude" => $databusiness['lat'], "longitude" => $databusiness['longitude'], "product_details" => $product_data, 'pass_details' => $all_pass, 'k' => $k);
	} else {
		return array();
	}
}
/****************Function getpassesdetails*******************
 * @type            : Function
 * @Author          : Aamir
 * @function name   : getpassesdetails
 * @description     : get all passes details.
 * @param           : null
 * @return          : null
 * ********************************************************** */
function getpassesdetails($service_id = '', $user_id = '', $user_booking_id = '')
{
	$CI = &get_instance();
	$CI->db->select('*');
	$CI->db->from('business_passes');
	$CI->db->where('id', $service_id);
	$CI->db->where('status', "Active");
	$passes_data = $CI->db->get()->row_array();
	if (!empty($passes_data)) {

		$pass_for = $passes_data['pass_for'];
		if ($pass_for == '0') {
			$pass_for_label = 'Class Pass';
		} else if ($pass_for == '1') {
			$pass_for_label = 'Workshop Pass';
		}
		$passesdata['pass_for']  = ''; //$pass_for_label;

		$passId = $passes_data['pass_type'];
		$where = array("id" => $passId);
		$manage_pass_data = $CI->dynamic_model->getdatafromtable("manage_pass_type", $where);
		$pass_type_name = '';
		if (!empty($manage_pass_data)) {
			$pass_type_name = $manage_pass_data[0]['pass_type'];
		}


		$pass_type_subcat = $passes_data['pass_type_subcat'];
		$where = array("id" => $pass_type_subcat);
		$manage_pass_res = $CI->dynamic_model->getdatafromtable("manage_pass_type", $where);
		$pass_days = '';
		if (!empty($manage_pass_res)) {
			$pass_days = $manage_pass_res[0]['pass_days'];
		}

		if ($passId == '1') {
			// class
			if ($pass_days > '1') {
				$pass_validity = $pass_days . ' Classes';
			} else if ($pass_days == '1') {
				$pass_validity = $pass_days . ' Class';
			} else {
				$pass_validity = $pass_days;
			}
		} else {
			if ($pass_days > '1') {
				$pass_validity = $pass_days . ' Days';
			} else if ($pass_days == '1') {
				$pass_validity = $pass_days . ' Day';
			} else {
				$pass_validity = $pass_days;
			}
		}



		$passesdata['pass_type_name']  = $pass_type_name;
		$passesdata['pass_id']  = $passes_data['id'];
		$passesdata['description']  = $passes_data['description'];
		$passesdata['notes']  = $passes_data['notes'];
		$passesdata['pass_name'] = ucwords($passes_data['pass_name']);
		// $passes_data['service_type'] 1 class 2 workshop
		if ($passes_data['service_type'] == 1) {
			$classes_data = $CI->dynamic_model->getdatafromtable('business_class', array("id" => $passes_data['service_id']));
			$passesdata['class_name'] = (!empty($classes_data)) ? ucwords($classes_data[0]['class_name']) : "";
		} else {
			$classes_data = $CI->dynamic_model->getdatafromtable('business_workshop', array("id" => $passes_data['service_id']));
			$passesdata['class_name'] = (!empty($classes_data)) ? ucwords($classes_data[0]['workshop_name']) : "";
		}

		//Check my favourite status
		//service_type 1 for business ,2 passes
		$whe = array("user_id" => $user_id, "service_id" => $passes_data['id'], "service_type" => 2);
		$user_favourite = $CI->dynamic_model->getdatafromtable("user_business_favourite", $whe);
		$favourite = (!empty($user_favourite)) ? '1' : '0';
		$passesdata['booking_pass_id'] = $passes_data['pass_id'];
		$passType  = (!empty($passes_data['pass_type'])) ? $passes_data['pass_type'] : '';

		$pass_type_subcat  = (!empty($passes_data['pass_type_subcat'])) ? $passes_data['pass_type_subcat'] : '';
		//echo $passType.'------'.$pass_type_subcat;
		$pass_type = get_passes_type_name($passType, $pass_type_subcat);

		$pass_recring = get_passes_recring($passType, $pass_type_subcat);


		$passesdata['is_recring']  = $pass_recring;
		$passesdata['passes_id']  = $passes_data['id'];
		$passesdata['pass_type_subcat']  = $passes_data['pass_type_subcat'];
		$passesdata['purchase_date']  = $passes_data['purchase_date'];
		$amount = $passes_data['amount'];

		if ($passes_data['pass_type_subcat'] == '36') {
			$pass_type = $pass_type . ' ($' . $amount . ')';
		}
		$passesdata['pass_type'] = $pass_type;

		$pass_type_category = $passes_data['pass_type'];
		if ($pass_type_category == '1') {
			$pass_type_category = 'PunchCard';
		} else if ($pass_type_category == '10') {
			$pass_type_category = 'TimeFrame';
		} else {
			$pass_type_category = 'Recurring Membership';
		}

		$passesdata['pass_type_category'] = $pass_type_category;
		$passesdata['start_date'] = date("d M Y ", $passes_data['purchase_date']);
		$passesdata['end_date']  = date("d M Y ", $passes_data['pass_end_date']);
		$passesdata['start_date_utc'] = $passes_data['purchase_date'];
		$passesdata['end_date_utc']  = $passes_data['pass_end_date'];

		//$passesdata['amount'] = number_format($passes_data['amount'],2);
		$passesdata['tax'] = $passes_data['tax1_rate'] + $passes_data['tax2_rate'];


		if ($passes_data['pass_type_subcat'] == '36') {
			$today_dt = date('d');
			$a_date = date("Y-m-d");
			$lastmonth_dt = date("t", strtotime($a_date));
			$diff_dt = $lastmonth_dt - $today_dt;
			$diff_dt = $diff_dt + 1;
			$rt = date("Y-m-t", strtotime($a_date));
			$recurring_date = $rt;
			$pass_end_date = strtotime($rt);
			$passes_remaining_count = $diff_dt;
			$per_day_amt = $amount / $lastmonth_dt;
			$per_day_amt = round($per_day_amt, 2);
			$amount = $per_day_amt * $diff_dt;

			$passesdata['amount'] = number_format($amount, 2);
		} else {
			$passesdata['amount'] = number_format($amount, 2);
		}


		$workshop_price = $amount;
		$workshop_tax_price = 0;
		$tax1_rate_val = 0;
		$tax2_rate_val = 0;
		$workshop_total_price = $workshop_price;
		if (strtolower($passes_data['tax1']) == 'yes') {
			$tax1_rate = floatVal($passes_data['tax1_rate']);
			$tax1_rate_val = (($workshop_price * $tax1_rate) / 100);
			$workshop_tax_price = $tax1_rate_val;
			$workshop_total_price = $workshop_price + $tax1_rate_val;
		}
		if (strtolower($passes_data['tax2']) == 'yes') {
			$tax2_rate = floatVal($passes_data['tax2_rate']);
			$tax2_rate_val = (($workshop_price * $tax2_rate) / 100);
			$workshop_tax_price = $tax1_rate_val + $tax2_rate_val;
			$workshop_total_price = $workshop_total_price + $tax2_rate_val;
		}

		$passesdata['tax1_rate'] = number_format($tax1_rate_val, 2);
		$passesdata['tax2_rate'] = number_format($tax2_rate_val, 2);
		$passesdata['pass_tax_price'] = number_format($workshop_tax_price, 2);
		$passesdata['pass_total_price'] = number_format($workshop_total_price, 2);


		$passesdata['pass_validity'] = $pass_validity;
		$is_one_time_purchase = $passes_data['is_one_time_purchase'];
		$passesdata['business_id'] = $passes_data['business_id'];
		$passesdata['age_restriction'] = $passes_data['age_restriction'];
		$passesdata['age_over_under'] = $passes_data['age_over_under'];
		$passesdata['favourite'] = $favourite;
		//Check my passes purchase status


		if (!empty($user_booking_id)) {
			$condition = array("id" => $user_booking_id, "user_id" => $user_id, "service_id" => $passes_data['id'], "service_type" => '1', "passes_status" => "1", "status" => "Success");
		} else {
			$condition = array("user_id" => $user_id, "service_id" => $passes_data['id'], "service_type" => '1', "passes_status" => "1", "status" => "Success");
		}

		$purchase_data = $CI->dynamic_model->getdatafromtable("user_booking", $condition);

		if (empty($purchase_data) && !empty($user_booking_id)) {
			$condition = array("id" => $user_booking_id);
			$purchase_data = $CI->dynamic_model->getdatafromtable("user_booking", $condition);
		}
		// $purchase_data= (!empty($purchase_data)) ? '1' : '0';

		// active 0 expire 1
		$is_purchase = 0;
		$passes_total_count = 0;
		$pass_current_status = 1;

		$is_one_time_purchase_val = 0;
		if ($is_one_time_purchase == 'yes') {
			$condition = array("user_id" => $user_id, "service_id" => $passes_data['id'], "service_type" => '1', 'status' => 'success');
			//,"status"=>"Success", "passes_status" => "1"
			$purchase_result = $CI->dynamic_model->getdatafromtable("user_booking", $condition);
			if (!empty($purchase_result)) {
				$is_one_time_purchase_val = 1;
			}
		}

		$passes_remaining_count = 0;

		if (!empty($purchase_data)) {
			$pass_current_status = 0;

			$passesdata['start_date'] = date("d M Y ", $purchase_data[0]['passes_start_date']);
			$passesdata['end_date']  = date("d M Y ", $purchase_data[0]['passes_end_date']);
			$passesdata['start_date_utc'] = $purchase_data[0]['passes_start_date'];
			$passesdata['end_date_utc']  = $purchase_data[0]['passes_end_date'];

			//$passes_remaining_count = $purchase_data[0]['passes_remaining_count'];
			$passes_total_count = $purchase_data[0]['passes_total_count'];
			/*if($passes_remaining_count == '0'){
            		$is_purchase = 1;
            		$pass_current_status = 1;
            	}*/

			$passes_remaining_count = $purchase_data[0]['passes_remaining_count'];
			$passes_status = $purchase_data[0]['passes_status'];
			if ($passes_status == '0' || $passes_remaining_count == '0') {
				$is_purchase = 0;
				$pass_current_status = 1;
			}
		}




		$purchase_data = $is_purchase;

		$passesdata['total_count'] = $passes_total_count;
		$passesdata['remaining_count'] = $passes_remaining_count;
		$passesdata['pass_current_status'] = $pass_current_status;
		$passesdata['is_one_time_purchase'] = $is_one_time_purchase_val;
		//print_r($purchase_data); die;
		$passesdata['is_purchase'] = $purchase_data;
		//Check cart added or not in your cart bucket
		$condition1 = array("user_id" => $user_id, "service_id" => $passes_data['id'], "service_type" => 1, "status" => "Pending");
		$cart_data = $CI->dynamic_model->getdatafromtable("user_booking", $condition1);
		$cartdata = (!empty($cart_data)) ? '1' : '0';
		$passesdata['is_cart'] = $cartdata;
		/*$pass_counter = $CI->db->get_where('user_booking', array("user_id"=>$user_id,"service_id"=>$passes_data['id'],"service_type"=>1,"status"=>"Success"))->row_array();
			$remaining_count = 0;
			if(!empty($pass_counter)){
				$remaining_count = $pass_counter['passes_remaining_count'];
			}*/
		//$passesdata['remaining_count'] = $remaining_count;
		return $passesdata;
	} else {
		return array();
	}
}
function studiopassesdetails($service_id = '')
{
	$CI = &get_instance();
	$CI->db->select('*');
	$CI->db->from('business_passes');
	$CI->db->where('id', $service_id);
	$CI->db->where('status', "Active");
	$passes_data = $CI->db->get()->row_array();
	if (!empty($passes_data)) {
		$passesdata['pass_id']  = $passes_data['id'];
		$passesdata['pass_name'] = ucwords($passes_data['pass_name']);
		// $passes_data['service_type'] 1 class 2 workshop
		if ($passes_data['service_type'] == 1) {
			$classes_data = $CI->dynamic_model->getdatafromtable('business_class', array("id" => $passes_data['service_id']));
			$passesdata['class_name'] = (!empty($classes_data)) ? ucwords($classes_data[0]['class_name']) : "";
		} else {
			$classes_data = $CI->dynamic_model->getdatafromtable('business_workshop', array("id" => $passes_data['service_id']));
			$passesdata['class_name'] = (!empty($classes_data)) ? ucwords($classes_data[0]['workshop_name']) : "";
		}

		//Check my favourite status
		//service_type 1 for business ,2 passes
		$whe = array("service_id" => $passes_data['id'], "service_type" => 2);
		$user_favourite = $CI->dynamic_model->getdatafromtable("user_business_favourite", $whe);
		$favourite = (!empty($user_favourite)) ? '1' : '0';
		$passesdata['booking_pass_id'] = $passes_data['pass_id'];
		$passType  = (!empty($passes_data['pass_type'])) ? $passes_data['pass_type'] : '';

		$pass_type_subcat  = (!empty($passes_data['pass_type_subcat'])) ? $passes_data['pass_type_subcat'] : '';
		// $pass_type=get_passes_type_name($passType,$pass_type_subcat);
		$pass_type = get_passes_type_name($passType);
		$pass_sub_type = get_passes_type_name($passType, $pass_type_subcat);

		$passesdata['pass_type_id'] = encode($passType);
		$passesdata['pass_type'] = $pass_type;
		$passesdata['pass_sub_type_id'] = encode($pass_type_subcat);
		$passesdata['pass_sub_type'] = $pass_sub_type;
		$passesdata['start_date'] = date("d M Y ", $passes_data['purchase_date']);
		$passesdata['end_date']  = date("d M Y ", $passes_data['pass_end_date']);
		$passesdata['start_date_utc'] = $passes_data['purchase_date'];
		$passesdata['end_date_utc']  = $passes_data['pass_end_date'];
		$passesdata['amount'] = $passes_data['amount'];
		$passesdata['passes_id'] = $passes_data['pass_id'];
		$passesdata['purchase_date'] =  date("d M Y ", $passes_data['purchase_date']);
		$passesdata['tax'] = $passes_data['tax1_rate'] + $passes_data['tax2_rate'];
		$passesdata['pass_validity'] = $passes_data['pass_validity'];
		$passesdata['age_restriction'] = $passes_data['age_restriction'];
		$passesdata['age_over_under'] = $passes_data['age_over_under'];
		$passesdata['favourite'] = $favourite;
		$passesdata['tax1'] = $passes_data['tax1'];
		$passesdata['tax2'] = $passes_data['tax2'];
		$passesdata['tax1_rate'] = $passes_data['tax1_rate'];
		$passesdata['tax2_rate'] = $passes_data['tax2_rate'];
		$passesdata['is_client_visible'] = $passes_data['is_client_visible'];
		$passesdata['description'] = $passes_data['description'];
		$passesdata['notes'] = $passes_data['notes'];
		$passesdata['is_recurring_billing'] = $passes_data['is_recurring_billing'];
		$passesdata['billing_start_from'] = $passes_data['billing_start_from'];
		//Check my passes purchase status
		$condition = array("service_id" => $passes_data['id'], "service_type" => 1, "status" => "Success");
		$purchase_data = $CI->dynamic_model->getdatafromtable("user_booking", $condition);
		$purchase_data = (!empty($purchase_data)) ? '1' : '0';
		$passesdata['is_purchase'] = $purchase_data;
		//Check cart added or not in your cart bucket
		$condition1 = array("service_id" => $passes_data['id'], "service_type" => 1, "status" => "Pending");
		$cart_data = $CI->dynamic_model->getdatafromtable("user_booking", $condition1);
		$cartdata = (!empty($cart_data)) ? '1' : '0';
		$passesdata['is_cart'] = $cartdata;



		return $passesdata;
	} else {
		return array();
	}
}

function get_product_list($business_id = '', $limit = '5', $offset = '0', $search_val = '', $sort_price = '')
{
	$CI = &get_instance();
	$response = array();
	$where = array("business_id" => $business_id);
	$search_val = trim($search_val);
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
	$product_data = $CI->dynamic_model->getdatafromtable("business_product", $where, "*", $limit, $offset, $order_name, $order_by);
	//echo $CI->db->last_query();die;
	if (!empty($product_data)) {
		foreach ($product_data as $value) {
			$productdata['product_id']    = $value['id'];
			$productdata['product_name']   = $value['product_name'];
			$productdata['product_price']   = $value['price'];
			$productdata['product_status']   = $value['status'];
			$productdata['product_description'] = $value['description'];
			$productdata['tax1'] = $value['tax1'];
			$productdata['tax2'] = $value['tax2'];
			$productdata['tax1_rate'] = $value['tax1_rate'];
			$productdata['tax2_rate'] = $value['tax2_rate'];
			$image_datas = get_product_images($value['id']);
			$productdata['product_images'] = $image_datas;

			$workshop_price = $value['price'];
			$workshop_tax_price = 0;
			$tax1_rate_val = 0;
			$tax2_rate_val = 0;
			$workshop_total_price = $workshop_price;
			if (strtolower($value['tax1']) == 'yes') {
				$tax1_rate = floatVal($value['tax1_rate']);
				$tax1_rate_val = (($workshop_price * $tax1_rate) / 100);
				$workshop_tax_price = $tax1_rate_val;
				$workshop_total_price = $workshop_price + $tax1_rate_val;
			}
			if (strtolower($value['tax2']) == 'yes') {
				$tax2_rate = floatVal($value['tax2_rate']);
				$tax2_rate_val = (($workshop_price * $tax2_rate) / 100);
				$workshop_tax_price = $tax1_rate_val + $tax2_rate_val;
				$workshop_total_price = $workshop_total_price + $tax2_rate_val;
			}

			$productdata['tax1_rate'] = number_format($tax1_rate_val, 2);
			$productdata['tax2_rate'] = number_format($tax2_rate_val, 2);
			$productdata['product_tax_price'] = number_format($workshop_tax_price, 2);
			$productdata['product_total_price'] = number_format($workshop_total_price, 2);

			$response[]	        = $productdata;
		}
	}
	return $response;
}
function get_product_images($product_id = '')
{
	$CI = &get_instance();
	$imgarr = array();
	$image_data = $CI->dynamic_model->getdatafromtable("business_product_images", array("product_id" => $product_id));
	if (!empty($image_data)) {
		foreach ($image_data as $value1) {
			$imgdata['product_image_id'] = $value1['id'];
			$imgdata['image_name'] = base_url() . 'uploads/products/' . $value1['image_name'];
			$imgarr[]	        = $imgdata;
		}
	}
	return $imgarr;
}
// Get Category Name using category ID
function get_categories($category_id = '', $type = '')
{
	$CI = get_instance();
	$getcat = '';
	if ((!empty($category_id)) && ($type == '' || $type == '1')) {
		$sql = "SELECT GROUP_CONCAT(name) AS category_name FROM manage_skills
        WHERE id IN ($category_id) AND status='Active'";
		$getcat = $CI->dynamic_model->get_query_result($sql);
		$getcat = (!empty($getcat[0]->category_name)) ? $getcat[0]->category_name : '';
	} else {
		$ids = $category_id;
		$condition = array('status' => 'Active', 'category_type' => 2, 'category_parent !=' => 0);
		//print_r($category_id); die;
		$getcat = $CI->dynamic_model->getWhereInData('manage_category', 'id', $ids, $condition, 'GROUP_CONCAT(category_name) AS category_name');
		if (!empty($getcat)) {
			$getcat = $getcat[0]['category_name'];
		}
	}
	return $getcat;
}
// Get Category data
function get_categories_data($category_id = '', $type = '')
{
	$CI = get_instance();
	$getcat = [];
	if ($category_id && $type == '' || $type == '1') {
		$sql = "SELECT  id, GROUP_CONCAT(name) AS category_name FROM manage_skills
        WHERE id IN ($category_id) AND status='Active'";
		$getcat = $CI->dynamic_model->get_query_result($sql);
	} else {
		$ids = $category_id;
		$condition = array('status' => 'Active', 'category_type' => 2, 'category_parent !=' => 0);
		$getcat = $CI->dynamic_model->getWhereInData('manage_category', 'id', $ids, $condition, 'id,category_name');
	}
	return $getcat;
}
// Get business type Name
function get_business_type_name($business_type_id = '')
{
	$CI = get_instance();
	$condition = array('id' => $business_type_id);
	$result = $CI->dynamic_model->getdatafromtable('manage_business_type', $condition);
	return (!empty($result[0]['business_type'])) ? $result[0]['business_type'] : '';
}
// Get Family Name
function get_family_name($relative_id = '')
{
	$CI = get_instance();
	$condition = array('id' => $relative_id);
	$result = $CI->dynamic_model->getdatafromtable('manage_relations', $condition);
	return (!empty($result[0]['name'])) ? $result[0]['name'] : '';
}
// Get passes Name
function get_passes_type_name($pass_type_id = '', $pass_type_subcat = '')
{
	$CI = get_instance();
	if (!empty($pass_type_id) && !empty($pass_type_subcat)) {
		$condition = array('id' => $pass_type_subcat, 'parent_id' => $pass_type_id, 'status' => "Active");
	} else {
		$condition = array('id' => $pass_type_id, 'parent_id' => 0, 'status' => "Active");
	}
	$result = $CI->dynamic_model->getdatafromtable('manage_pass_type', $condition);
	return (!empty($result[0]['pass_type'])) ? $result[0]['pass_type'] : '';
}

function get_passes_recring($pass_type_id = '', $pass_type_subcat = '')
{
	$CI = get_instance();
	if (!empty($pass_type_id) && !empty($pass_type_subcat)) {
		$condition = array('id' => $pass_type_subcat, 'parent_id' => $pass_type_id, 'status' => "Active");
	} else {
		$condition = array('id' => $pass_type_id, 'parent_id' => 0, 'status' => "Active");
	}
	$result = $CI->dynamic_model->getdatafromtable('manage_pass_type', $condition);
	return $result[0]['is_recring'] ? $result[0]['is_recring'] : 0;
}
// Get services type Name
function get_services_type_name($service_type_id = '')
{
	$CI = get_instance();
	$getservice = '';
	if ($service_type_id) {
		$sql = "SELECT GROUP_CONCAT(service_name) AS service_name FROM manage_services_type
        WHERE id IN ($service_type_id)";
		$getservice = $CI->dynamic_model->get_query_result($sql);
		$getservice = (!empty($getservice[0]->service_name)) ? $getservice[0]->service_name : '';
	}
	return $getservice;
}
// Get business type Name
function get_product_quantity($business_id = '', $product_id = '')
{
	$CI = get_instance();
	$condition = array('id' => $product_id, 'business_id' => $business_id);
	$result = $CI->dynamic_model->getdatafromtable('business_product', $condition);
	return (!empty($result[0]['quantity'])) ? $result[0]['quantity'] : '';
}

// Get business type Name
function get_service_tax($business_id = '', $service_id = '', $service_type = '1')
{
	$CI = get_instance();
	$condition = array('id' => $service_id, 'business_id' => $business_id);
	if ($service_type == '1') {
		$table = 'business_passes';
	} elseif ($service_type == '2') {
		$table = 'services';
	} elseif ($service_type == '3') {
		$table = 'business_product';
	}
	$result = $CI->dynamic_model->getdatafromtable($table, $condition);
	//print_r($result); die;
	$tax1_rate = $result[0]['tax1_rate'] ? $result[0]['tax1_rate'] : 0;
	$tax2_rate = $result[0]['tax2_rate'] ? $result[0]['tax2_rate'] : 0;

	$tax =  floatVal($tax1_rate) + floatVal($tax2_rate);
	return $tax;
}
// Get check cart and return total amount
function check_cart($user_id = '')
{
	$CI = get_instance();
	$condition = array('user_id' => $user_id, 'status' => "Pending");
	$result = $CI->dynamic_model->getdatafromtable('user_booking', $condition, '', '', '', 'id', 'DESC');

	if (!empty($result)) {

		$find_sub_total    = $result[0]['sub_total'];
		// $total_amt  = array_sum($find_sub_total) ;
		return number_format((float)$find_sub_total, 2, '.', '');
	} else {
		return false;
	}
}

function check_cart_value($user_id = '', $business_id = '')
{
	$CI = get_instance();

	if (!empty($business_id)) {
		$condition = array('user_id' => $user_id, 'business_id' => $business_id, 'status' => "Pending");
	} else {
		$condition = array('user_id' => $user_id, 'status' => "Pending");
	}

	$result = $CI->dynamic_model->getdatafromtable('user_booking', $condition, '', '', '', 'id', 'DESC');

	if (!empty($result)) {
		$find_sub_total = 0;
		foreach ($result as $key => $value) {
			$find_sub = $value['sub_total'];
			$find_sub_total = $find_sub_total + $find_sub;
		}
		// $total_amt  = array_sum($find_sub_total) ;
		return number_format((float)$find_sub_total, 2, '.', '');
	} else {
		return false;
	}
}

function check_cart_with_tax($user_id = '')
{
	$CI = get_instance();
	$condition = array('user_id' => $user_id, 'status' => "Pending");
	$result = $CI->dynamic_model->getdatafromtable('user_booking', $condition, '', '', '', 'id', 'DESC');

	if (!empty($result)) {
		$total_amt = 0;
		foreach ($result as $key => $value) {
			$find_sub_total    = $value['sub_total'];
			$find_tax_amount    = $value['tax_amount'];
			$total_amt = $total_amt + $find_sub_total + $find_tax_amount;
		}


		//calculate total amount
		/* $find_sub_total    = $result[0]['sub_total'];
            $find_tax_amount    = $result[0]['tax_amount'];
            $total_amt = $find_sub_total + $find_tax_amount;*/
		// $total_amt  = array_sum($find_sub_total) ;
		return number_format((float)$total_amt, 2, '.', '');
	} else {
		return false;
	}
}
function gettotalTax($user_id = '', $business_id = '')
{
	$CI = get_instance();
	if (!empty($business_id)) {
		$condition = array('user_id' => $user_id, 'business_id' => $business_id, 'status' => "Pending");
	} else {
		$condition = array('user_id' => $user_id, 'status' => "Pending");
	}

	$result = $CI->dynamic_model->getdatafromtable('user_booking', $condition, '', '', '', 'id', 'DESC');
	if (!empty($result)) {
		$tax = 0;

		foreach ($result as  $value) {
			$tax1 = ($value['tax_amount'] / 100) * $value['amount'];
			$tax_cal = $tax1 * $value['quantity'];
			$tax += $tax_cal;
			// $tax += $value['tax_amount'];
		}
		return $tax;
	} else {
		return false;
	}
}
// Get calculate tax for cart
function calculate_tax_for_cart($user_id = '')
{
	$CI = get_instance();
	$condition = array('user_id' => $user_id, 'status' => "Pending");
	$result = $CI->dynamic_model->getdatafromtable('user_booking', $condition);
	if (!empty($result)) {
		//calculate total amount
		$find_sub_total    = array_column($result, 'sub_total');
		$total_amt  = array_sum($find_sub_total);
		return number_format((float)$total_amt, 2, '.', '');
	} else {
		return false;
	}
}
// Get get passes status
function get_passes_checkin_status($user_id = '', $service_id = '', $service_type = '', $date = '')
{
	$CI = get_instance();
	$condition = "user_id=" . $user_id . " AND service_id=" . $service_id . " AND service_type=" . $service_type . " AND DATE(FROM_UNIXTIME(create_dt))='" . $date . "'";
	$user_attendance = $CI->dynamic_model->getdatafromtable('user_attendance', $condition);
	if (!empty($user_attendance)) {
		$passes_status = (!empty($user_attendance[0]['status'])) ? $user_attendance[0]['status'] : '';
		return $passes_status;
	} else {
		return '';
	}
}

function get_passes_checkin_status_by_schdule($user_id = '', $service_id = '', $service_type = '', $date = '', $schedule_id = '')
{
	$CI = get_instance();
	$condition = "user_id=" . $user_id . " AND service_id=" . $service_id . " AND service_type=" . $service_type . " AND checkin_dt='" . $date . "' AND schedule_id =" . $schedule_id;
	$user_attendance = $CI->dynamic_model->getdatafromtable('user_attendance', $condition);
	if (!empty($user_attendance)) {
		$passes_status = (!empty($user_attendance[0]['status'])) ? $user_attendance[0]['status'] : '';
		return $passes_status;
	} else {
		return '';
	}
}
// check purchase passses status
function get_passes_status($user_id = '', $business_id = '', $service_id = '', $service_type = '')
{
	$CI = get_instance();
	//service_type 1= classes=1 & 2=workshop
	if ($service_type == '1') {
		$where = array(
			"user_id" => $user_id, "business_id" => $business_id, "service_type" => '1',
			'status' => 'Success', 'passes_remaining_count' != '0'
		);
	} else {
		$where = array(
			"user_id" => $user_id, "business_id" => $business_id, "service_type" => '1',
			'status' => 'Success', 'passes_remaining_count' != '0'
		);
		// $where=array("user_id"=>$user_id,"business_id"=>$business_id,"service_type"=>'1');
	}
	$passes_data = $CI->dynamic_model->getdatafromtable('user_booking', $where);
	if (!empty($passes_data)) {
		$passes_status = (!empty($passes_data[0]['status'])) ? $passes_data[0]['status'] : '';
		return $passes_status;
	} else {
		return '';
	}
}


// get_signed_class_or_workshop_count
function get_checkin_class_or_workshop_count($service_id = '', $service_type = '', $date = '')
{
	$CI = get_instance();
	$date = date("Y-m-d", $date);
	$condition = " service_id=" . $service_id . " AND service_type=" . $service_type . " AND (status ='checkin' ||  status ='singup') AND DATE(FROM_UNIXTIME(create_dt))='" . $date . "'";
	$user_attendance = $CI->dynamic_model->getdatafromtable('user_attendance', $condition, 'count(status) as status');
	if (!empty($user_attendance)) {
		$count_data = (!empty($user_attendance[0]['status'])) ? $user_attendance[0]['status'] : '0';
		return $count_data;
	} else {
		return '0';
	}
}

function get_checkin_class_or_workshop_daily_count($service_id = '', $service_type = '', $date = '', $schedule_id = '')
{
	$CI = get_instance();
	// $date = date("Y-m-d",$date);
	$condition = "service_id=" . $service_id . " AND service_type=" . $service_type . " AND (status ='checkin' ||  status ='singup') AND checkin_dt='" . $date . "'";
	if (!empty($schedule_id)) {
		$condition .= " AND schedule_id = " . $schedule_id;
	}
	$user_attendance = $CI->dynamic_model->getdatafromtable('user_attendance', $condition, 'count(status) as status');
	if (!empty($user_attendance)) {
		$count_data = (!empty($user_attendance[0]['status'])) ? $user_attendance[0]['status'] : '0';
		return $count_data;
	} else {
		return '0';
	}
}

function get_daywise_instructor_data($service_id = '', $service_type = '', $business_id = '', $schedule_id = '')
{

	$CI = &get_instance();
	$url = site_url() . 'uploads/user/';
	if ($service_type == '1') {
		$CI->db->select('class_scheduling_time.*, business_location.location_name, business_location.capacity, user.name,user.lastname,CONCAT("' . $url . '", profile_img) as profile_img,manage_week_days.week_name');
		$CI->db->join('user', 'user.id=class_scheduling_time.instructor_id', 'LEFT');
		$CI->db->join('manage_week_days', 'manage_week_days.id=class_scheduling_time.day_id', 'LEFT');
		$CI->db->join('business_location', 'business_location.id = class_scheduling_time.location_id', 'LEFT');
		$CI->db->from('class_scheduling_time');
		$CI->db->where('class_scheduling_time.class_id', $service_id);
		$CI->db->where('class_scheduling_time.business_id', $business_id);
		if (!empty($schedule_id)) {
			$CI->db->where('class_scheduling_time.id', $schedule_id);
		}
		$data = $CI->db->get()->result_array();
	}
	if ($service_type == '2') {
		$CI->db->select('workshop_scheduling_time.*, business_location.location_name, business_location.capacity, user.name,user.lastname,CONCAT("' . $url . '", profile_img) as profile_img,manage_week_days.week_name');
		$CI->db->join('user', 'user.id=workshop_scheduling_time.instructor_id', 'LEFT');

		$CI->db->join('manage_week_days', 'manage_week_days.id=workshop_scheduling_time.day_id', 'LEFT');
		$CI->db->join('business_location', 'business_location.id = workshop_scheduling_time.location_id', 'LEFT');
		$CI->db->from('workshop_scheduling_time');
		$CI->db->where('workshop_scheduling_time.workshop_id', $service_id);
		$CI->db->where('workshop_scheduling_time.business_id', $business_id);
		$data = $CI->db->get()->result_array();
	}


	if (!empty($data)) {
		return $data;
	} else {
		return array();
	}
}
function date_of_birth($dateOfBirth = '')
{
	$today = date("Y-m-d");
	$diff = date_diff(date_create($dateOfBirth), date_create($today));
	return $diff->format('%y');
}
// Get slots name
function get_slots_time($slot_id = '')
{
	$CI = get_instance();
	$condition = array('slot_id' => $slot_id);
	$result = $CI->dynamic_model->getdatafromtable('business_slots', $condition);
	return (!empty($result[0]['slot_time_from'])) ? $result[0]['slot_time_from'] . "-" . $result[0]['slot_time_to'] : '';
}
/****************Function get_instrucotor_business_details*******************
 * @type            : Function
 * @Author          : Aamir
 * @function name   : get_instrucotor_business_details
 * @description     : get all business details.
 * @param           : null
 * @return          : null
 * ********************************************************** */

function get_instrucotor_business_details_old($id = '', $usid = '', $dist = '')
{

	$CI = &get_instance();
	$CI->db->select('*');
	$CI->db->from('business');
	$CI->db->where('id', $id);
	$CI->db->where('status', 'Active');
	$databusiness = $CI->db->get()->result_array();
	//$databusiness = @$databusiness[0];
	if (isset($databusiness[0]['id'])) {
		$databusiness = $databusiness[0];

		$getcat = '';
		$where = array('type' => 1, 'parent_id !=' => 0);
		$findresult = $CI->dynamic_model->getdatafromtable('business_category', $where, 'category');
		if (!empty($findresult)) {
			$ids = array_column($findresult, 'category');
			$condition = array('status' => 'Active', 'category_type' => 2, 'category_parent !=' => 0);
			$getcat = $CI->dynamic_model->getWhereInData('manage_category', 'id', $ids, $condition, 'GROUP_CONCAT(category_name) AS category_name');
			if (!empty($getcat)) {
				$getcat = $getcat[0]['category_name'];
			}
		}

		$img = site_url() . 'uploads/business/' . $databusiness['logo'];
		$imgname = pathinfo($img, PATHINFO_FILENAME);
		$ext = pathinfo($img, PATHINFO_EXTENSION);
		$thumb = site_url() . 'uploads/business/' . $imgname . '_thumb.' . $ext;

		$busi_img = site_url() . 'uploads/business/' . $databusiness['business_image'];
		$imgnamebusi = pathinfo($busi_img, PATHINFO_FILENAME);
		$extbusi = pathinfo($busi_img, PATHINFO_EXTENSION);
		$thumb_img = site_url() . 'uploads/business/' . $imgnamebusi . '_thumb.' . $extbusi;

		$distance = (!empty($dist)) ? $dist . ' Km' : '0 Km';
		//Check my favourite status
		//service_type 1 for business
		$where = array("user_id" => $usid, "service_id" => $id, "service_type" => 1);
		//check favourite status
		$user_favourite = $CI->dynamic_model->getdatafromtable("user_business_favourite", $where);
		$favourite = (!empty($user_favourite)) ? '1' : '0';
		//check register with studio status
		$where1 = array("business_id" => $id, "user_id" => $usid, "is_verified" => "Active", "status" => "Active");
		$user_business_trainer = $CI->dynamic_model->getdatafromtable("business_trainer_relationship", $where1);
		$is_register = (!empty($user_business_trainer)) ? '1' : '0';

		return array("business_id" => $id, "business_name" => $databusiness['business_name'], "email" => $databusiness['primary_email'], "address" => $databusiness['address'], "city" => $databusiness['city'], "state" => $databusiness['state'], "country" => $databusiness['country'], "business_phone" => $databusiness['business_phone'], "logo" => $img, "thumb" => $thumb, "business_img" => $busi_img, "business_thumb" => $thumb_img, "skills" => $getcat, "class_categories" => $getcat, "workshop_categories" => $getcat, "services_categories" => $getcat, "distance" => $distance, "favourite" => $favourite, "latitude" => $databusiness['lat'], "longitude" => $databusiness['longitude'], "is_register" => $is_register);
	} else {
		return array();
	}
}
function get_instrucotor_business_details($id = '', $usid = '', $dist = '')
{

	$CI = &get_instance();
	$con = array('business.id' => $id, 'business.status' => 'Active', 'user.status' => 'Active');
	$on = 'business.user_id = user.id';
	$databusiness = $CI->dynamic_model->getTwoTableData('user.status as user_status,business.*', 'business', 'user', $on, $con);
	if (isset($databusiness[0]['id'])) {
		$databusiness = $databusiness[0];

		$getcat = '';
		$where = array('type' => 1, 'parent_id !=' => 0);
		$findresult = $CI->dynamic_model->getdatafromtable('business_category', $where, 'category');
		if (!empty($findresult)) {
			$ids = array_column($findresult, 'category');
			$condition = array('status' => 'Active', 'category_type' => 2, 'category_parent !=' => 0);
			$getcat = $CI->dynamic_model->getWhereInData('manage_category', 'id', $ids, $condition, 'GROUP_CONCAT(category_name) AS category_name');
			if (!empty($getcat)) {
				$getcat = $getcat[0]['category_name'];
			}
		}

		$img = site_url() . 'uploads/business/' . $databusiness['logo'];
		$imgname = pathinfo($img, PATHINFO_FILENAME);
		$ext = pathinfo($img, PATHINFO_EXTENSION);
		$thumb = site_url() . 'uploads/business/' . $imgname . '_thumb.' . $ext;

		$busi_img = site_url() . 'uploads/business/' . $databusiness['business_image'];
		$imgnamebusi = pathinfo($busi_img, PATHINFO_FILENAME);
		$extbusi = pathinfo($busi_img, PATHINFO_EXTENSION);
		$thumb_img = site_url() . 'uploads/business/' . $imgnamebusi . '_thumb.' . $extbusi;

		$distance = (!empty($dist)) ? $dist . ' Km' : '0 Km';
		//Check my favourite status
		//service_type 1 for business
		$where = array("user_id" => $usid, "service_id" => $id, "service_type" => 1);
		//check favourite status
		$user_favourite = $CI->dynamic_model->getdatafromtable("user_business_favourite", $where);
		$favourite = (!empty($user_favourite)) ? '1' : '0';
		//check register with studio status
		$where1 = array("business_id" => $id, "user_id" => $usid);
		$business_trainer = $CI->dynamic_model->getdatafromtable("business_trainer_relationship", $where1);
		if (!empty($business_trainer) && $business_trainer[0]['status'] == 'Approve') {
			$is_register = '1';
		} elseif (!empty($business_trainer) && $business_trainer[0]['status'] == 'Reject') {
			$is_register = '2';
		} elseif (!empty($business_trainer) && $business_trainer[0]['status'] == 'Pending') {
			$is_register = '3';
		} else {
			$is_register = '0';
		}
		return array("business_id" => $id, "business_name" => $databusiness['business_name'], "email" => $databusiness['primary_email'], "address" => $databusiness['address'], "city" => $databusiness['city'], "state" => $databusiness['state'], "country" => $databusiness['country'], "business_phone" => $databusiness['business_phone'], "logo" => $img, "thumb" => $thumb, "business_img" => $busi_img, "business_thumb" => $thumb_img, "skills" => $getcat, "class_categories" => $getcat, "video_categories" => $getcat, "workshop_categories" => $getcat, "services_categories" => $getcat, "distance" => $distance, "favourite" => $favourite, "latitude" => $databusiness['lat'], "longitude" => $databusiness['longitude'], "is_register" => $is_register);
	} else {
		return array();
	}
}
if (!function_exists('pushNotification')) {
	function pushNotification($title = '', $types = '', $sender_user_id = '', $receiver_user_id = '')
	{
		$ci     = &get_instance();
		$notification_setting = '';
		$deviceInfo = getdatafromtable("user", array("id" => $receiver_user_id), 'name,lastname,device_token,device_type,notification');
		$senderInfo = getdatafromtable("user", array("id" => $sender_user_id), 'name,lastname');
		$sender_name = (!empty($senderInfo[0]['name'])) ? $senderInfo[0]['name'] . ' ' . $senderInfo[0]['lastname'] :  "";
		$token = $deviceInfo[0]['device_token'];
		// $token='f1FK79raZ6w:APA91bFTdPvN6QpIdjR4CIcYCAMKUWtBHku-kr9Osxc5xACEyl6GX3D6KKCwY3vAmY_xfCvv99vM_vpMsGLYw49AJSQ4sv_0RUzEJHaG45khZUNpn6VIjMRffy23Yz_ck71as6q-ka65';
		$device_type = $deviceInfo[0]['device_type'];
		$title = str_replace('*USERNAME*', $sender_name, $title);
		$getnotification = json_decode($deviceInfo[0]['notification']);
		$appNotification = $getnotification->app_notification;
		if ($appNotification == 1) {

			$apiKey = $ci->config->item('android_server_key');
			$badge = "1";
			if ($receiver_user_id != '') {
				$badge = getdatacount('notification', array('recepient_id' => $receiver_user_id, 'is_read' => 0));
			} else {
				$badge = "1";
			}
			$msg = array(
				'body' => $title,
				'title' => $title,
				'type' => $types,
				'notification_setting' => $notification_setting,
				'icon' => 'icon',
				"sound" => "push_short_duration.caf",
				"badge" => $badge,
				"user_id" => encode($receiver_user_id),
				"click_action" => "FCM_PLUGIN_ACTIVITY"
			);

			if ($device_type == 'android') {
				$fields = array(
					'to' => $token,
					'data' => $msg,
					'content_available' => true,
					'priority' => 'high',
					'sound' => 'push_short_duration.caf'
				);
			} else {
				$fields = array(
					'to' => $token,
					'notification' => $msg,
					'data' => $msg,
					'content_available' => true,
					'priority' => 'high',
					'sound' => 'push_short_duration.caf'
				);
			}

			$headers = array(
				'Authorization: key=' . $apiKey,
				'Content-Type: application/json'
			);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
			$response = curl_exec($ch);
			curl_close($ch);
			return true;
		} else {
			return false;
		}
	}
}
if (!function_exists('getbamboraToken')) {
	function getbamboraToken($card_number, $expiry_date, $expiry_year, $cvd)
	{
		$headers = array(
			'Content-Type: application/json'
		);

		$fields = array(
			'number' => $card_number,
			'expiry_month' => $expiry_date,
			'expiry_year' => $expiry_year,
			'cvd' => $cvd
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://api.na.bambora.com/scripts/tokenization/tokens');
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		$response = curl_exec($ch);
		if ($response) {
			$res = json_decode($response);
			return $res->token;
		}
		curl_close($ch);
		return true;
	}
}


if (!function_exists('getCloverToken')) {
	function getCloverToken($card_number, $expiry_date, $expiry_year, $cvd, $clover_key)
	{
		$headers = array(
			'Content-Type: application/json',
			'apikey:' . $clover_key
		);

		/*
		{
			"card":{
		      "number":"4111111111111111",
	          "exp_month":"03",
	          "exp_year":"2021",
	          "cvv":"123"
	          }
		}
		*/

		$fields = array(
			'card' => array(
				'number' => $card_number,
				'exp_month' => $expiry_date,
				'exp_year' => $expiry_year,
				'cvv' => $cvd
			)
		);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, CLOVER_TOKEN_URL);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		$response = curl_exec($ch);
		if ($response) {
			$res = json_decode($response);
			if (@$res->id) {
				return $res->id;
			} else {
				if ($res->message && $res->message == '400 Bad Request') {
					$error = $res->error;
					$arg['status'] = 0;
					$arg['error_code'] = ERROR_FAILED_CODE;
					$arg['error_line'] = __line__;
					$arg['message'] = $error->message;
					$arg['data'] = json_decode('{}');
					echo json_encode($arg);
					exit;
				}
			}
			/* if ($res->message && $res->message == '400 Bad Request') {
				$error = $res->error;
				$arg['status'] = 0;
				$arg['error_code'] = ERROR_FAILED_CODE;
				$arg['error_line'] = __line__;
				$arg['message'] = $error->message;
				$arg['data'] = json_decode('{}');
				echo json_encode($arg); exit;
			} */
		}
		curl_close($ch);
		return true;
	}
}

function clover_create_customer_profile($marchant_id, $clover_key, $access_token, $currency, $business_id, $user_id, $token)
{
	$CI = &get_instance();
	//echo 'hi'; die;
	$merchant_order_data_marchantid = $marchant_id;
	$access_token    = $access_token;
	$clover_base_url = CLOVER_BASE_URL;

	$where = array('id' => $user_id, 'status' => 'Active');
	$user_data = $CI->dynamic_model->getdatafromtable('user', $where);

	$where1 = array('id' => $business_id, 'status' => 'Active');
	$business_data = $CI->dynamic_model->getdatafromtable('business', $where1);
	//print_r($user_data );die;

	$merchant_id   = $marchant_id;
	$emailAddress  = $user_data[0]['email'];
	$phoneNumber   = $user_data[0]['mobile'];
	$first6        = '';
	$last4         = '';
	$token         = $token;
	$businessName  = $business_data[0]['business_name'];
	$firstName     = $user_data[0]['name'];
	$lastName      = $user_data[0]['lastname'];

	$data = '{"merchant":{"id":"' . $merchant_id . '"},"emailAddresses":[{"customer":{},"emailAddress":"' . $emailAddress . '","primaryEmail":true}],"phoneNumbers":[{"customer":{},"phoneNumber":"' . $phoneNumber . '"}],"cards":[{"customer":{},"first6":"' . $first6 . '","last4":"' . $last4 . '","token":"' . $token . '","tokenType":"MULTIPAY"}],"metadata":{"customer":{"id":"' . $emailAddress . '"},"businessName":"' . $businessName . '"},"firstName":"' . $firstName . '","lastName":"' . $lastName . '","marketingAllowed":true}';
	/***********************************************************************************************/
	$url_create_order = $clover_base_url . 'v3/merchants/' . $merchant_order_data_marchantid . '/customers?expand=addresses,emailAddresses,phoneNumbers,cards,metadata&access_token=' . $access_token;
	$curlOrderPost = $data;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url_create_order);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($curlOrderPost), 'Authorization: Bearer ' . $access_token));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $curlOrderPost);
	$pay_order_res = curl_exec($ch);
	curl_close($ch);
	//print_r($pay_order_res);die;
	return $pay_order_data = json_decode($pay_order_res);
}

function clover_existing_customer_profile_card_add($marchant_id, $clover_key, $access_token, $currency, $business_id, $user_id, $token)
{
	//echo 'hi'; die;
	$CI = &get_instance();
	$merchant_order_data_marchantid = $marchant_id;
	$access_token    = $access_token;
	$clover_base_url = CLOVER_BASE_URL;

	$where = array('id' => $user_id, 'status' => 'Active');
	$user_data = $CI->dynamic_model->getdatafromtable('user', $where);

	$where1 = array('id' => $business_id, 'status' => 'Active');
	$business_data = $CI->dynamic_model->getdatafromtable('business', $where1);
	//print_r($user_data );die;

	$merchant_id   = $marchant_id;
	$emailAddress  = $user_data[0]['email'];
	$phoneNumber   = $user_data[0]['mobile'];
	$first6        = '';
	$last4         = '';
	$token         = $token;
	$businessName  = $business_data[0]['business_name'];
	$firstName     = $user_data[0]['name'];
	$lastName      = $user_data[0]['lastname'];
	$clover_customer_profile_id      = $user_data[0]['clover_customer_profile_id'];

	$data = '{"first6":"","last4":"","firstName":"' . $firstName . '","lastName":"' . $lastName . '","expirationDate":"","token":"' . $token . '","tokenType":"MULTIPAY"}';
	/***********************************************************************************************/
	$url_create_order = $clover_base_url . 'v3/merchants/' . $merchant_order_data_marchantid . '/customers/' . $clover_customer_profile_id . '/cards?&access_token=' . $access_token;
	$curlOrderPost = $data;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url_create_order);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($curlOrderPost), 'Authorization: Bearer ' . $access_token));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $curlOrderPost);
	$pay_order_res = curl_exec($ch);
	curl_close($ch);
	//print_r($pay_order_res);die;
	return $pay_order_data = json_decode($pay_order_res);
}

function clover_payment_checkout($user_cc_no, $user_cc_mo, $user_cc_yr, $user_cc_cvv, $user_zip, $amount, $taxAmount, $marchant_id, $clover_key, $access_token, $currency)
{
	$CI = &get_instance();
	$merchant_order_data_marchantid = $marchant_id;
	$access_token    = $access_token;
	$clover_base_url = CLOVER_BASE_URL;

	/*
	    	$user_cc_no      = 4111111111111111;
		    $user_cc_mo      = 03;
		    $user_cc_yr      = 2021;
		    $user_cc_cvv     = 123;
		    $user_zip        = 94041;
		    $amount          = 400;
		    $taxAmount       = 0 ;
		    $currency        = 'USD';
		*/

	/***********************************************************************************************/
	$url_create_order = $clover_base_url . 'v3/merchants/' . $merchant_order_data_marchantid . '/orders';
	$curlOrderPost = json_encode(array("state" => "open"));
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url_create_order);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($curlOrderPost), 'Authorization: Bearer ' . $access_token));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $curlOrderPost);
	$pay_order_res = curl_exec($ch);
	curl_close($ch);
	//print_r($pay_order_res);die;
	$pay_order_data = json_decode($pay_order_res);
	$orderId = $pay_order_data->id;


	/***********************************************************************************************/
	$url_key = $clover_base_url . 'v2/merchant/' . $merchant_order_data_marchantid . '/pay/key';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url_key);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token));
	$pay_key_res = curl_exec($ch);
	curl_close($ch);
	//  print_r($pay_key_res);die;
	$pay_key_data = json_decode($pay_key_res);
	// print_r($pay_key_data); die;

	/***********************************************************************************************/


	$rsa = new Crypt_RSA();
	//print_r($rsa);
	//1. GET to /v2/merchant/{mId}/pay/key To get the encryption information you’ll need for the pay endpoint.
	//2. Encrypt the card information
	$prefix = $pay_key_data->prefix;
	$modulus = $pay_key_data->modulus;
	$exponent = $pay_key_data->exponent;
	//echo "<hr>";

	$first_6 = substr($user_cc_no, 0, 6);
	$last_4 = substr($user_cc_no, -4);
	//2.1. Prepend the card number with the prefix from GET /v2/merchant/{mId}/pay/key.

	//2.2. Generate an RSA public key using the modulus and exponent provided byGET /v2/merchant/{mId}/pay/key.
	$m = new Math_BigInteger($modulus);
	$e = new Math_BigInteger($exponent);

	$card = $prefix . $user_cc_no;

	$rsa->setHash('sha1');
	$rsa->setMGFHash('sha1');
	$rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_OAEP);
	$rsa->loadKey(array('n' => $m, 'e' => $e));

	/*************************************/
	// $rsa->setPublicKey();
	// $mypublickey = $rsa->getPublicKey();
	//echo "<hr>";
	//2.3. Encrypt the card number and prefix from step 1 with the public key.
	//$stingToEncPublickey = $stingToEnc.$mypublickey;

	$ciphertext =   $rsa->encrypt($card);


	//echo "<hr>";
	//3. Base64 encode the resulting encrypted data into a string which you will send to Clover in the “cardEncrypted” field.
	$stingBase64Encpted = base64_encode($ciphertext);
	/***********************************************************************************************/



	/****************** POST DATA TO PAYMENT API ******************/
	$url = $clover_base_url . 'v2/merchant/' . $merchant_order_data_marchantid . '/pay';

	$payload = array(
		'orderId' => $orderId,
		'taxAmount' => $taxAmount,
		'zip' => $user_zip,
		'expMonth' => $user_cc_mo,
		'cvv' => $user_cc_cvv,
		'amount' => round($amount * 100),
		'currency' => $currency,
		'last4' => $last_4,
		'expYear' => $user_cc_yr,
		'first6' => $first_6,
		'cardEncrypted' => $stingBase64Encpted
	);
	$curlPost = json_encode($payload);

	/* {"orderId":"RAYDD270VF6VT","taxAmount":"0","zip":"","expMonth":"05","cvv":"123","amount":"100.00","currency":"USD","last4":"1111","expYear":"2030","first6":"411111","cardEncrypted":"GEP+VJYPQ+zRKdpXDWeApr8E9XNn9k8HKRiytL47MlYz+XfAyJ3UD6oJXxKaLY6FCfwsW+FaPDOa\/rP56pK\/ZmUaQUPDCc4gyhYfdvPbizzSYaRt\/URuqGQFyOc3b+EKvcL9u5ee5mEIQFP2KdtKqy+zwWBxrEHqgcWJlbPtjh7w13t8HtEe+vxcFwEtgjl6nC9WYne2bvwpACcQI9k0WbUNV8ALZw3X+zhJSECccHuDG+X10JCamFa3pGGKRBlutK\/hEpz4rBQ34w9gAUkbJUp7b5Paufm+Cygf21WPRud976wMey0dwOs9bb7FGTcBEE9FK3bnjuOU5AfGmpsVrA=="}

	   {"orderId":"YJNWR5C9YGK90","taxAmount":0,"zip":"","expMonth":"05","cvv":"123","amount":"100.00","currency":"USD","last4":"1111","expYear":"2030","first6":"411111","cardEncrypted":"YlYhrGugGRGoWC7I83jiUGBgwA2\/6nXNvYhVMRJzIcGMqsyzs5z3\/3yCTqHBrzZ\/UCcfdloENow\/S3D3y1Yqhb+9WHPOMdlrXucxLzKYssrZtcPJC7V6zSxWR1Me\/qStzerxRsCgtspEN0Tqk6HdMeA2qy9tAWDBKutC0TeXV9jc+156bIe7O46N9jgT4FAQvCJhxnRWA9juEp71+x8lz0COhe4aZZaRIKrxhpGG0aXLfUmgUCf\/BJw4mN7ksPUg8djNtEMj1Ob2EhUmaWCCMEKf8r78C1UeHL28uxR\/F\/3OSMJtfsP45YvGE7iXeHe4SHcCeNqhEkfRR2glhF6IsQ=="}*/

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($curlPost), 'Authorization: Bearer ' . $access_token));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
	$data = curl_exec($ch);
	curl_close($ch);
	//print_r($data);die;
	$array_data = json_decode($data);
	return $array_data;
	/****************** POST DATA TO PAYMENT API ******************/
}

function clover_card_profile_check($usid, $savecard, $customer_name, $number, $expiry_month, $expiry_year, $cvd, $country_code, $business_id, $token)
{
	$CI = &get_instance();

	/*$usid          = $this->input->post('user_id');
		$savecard      = 1;
		$customer_name = $this->input->post('name');
		$number        = $this->input->post('number');
		$expiry_month  = $this->input->post('expiry_month');
		$expiry_year   = $this->input->post('expiry_year');
		$cvd           = $this->input->post('cvv');
		$country_code  = $this->input->post('country_code');
		$business_id   = $this->input->post('business_id');
		$token         = $this->input->post('card_token');*/

	if ($business_id != "") {
		$mid = getUserMarchantId($business_id);

		//var_dump($mid); die;
		$marchant_id  = $mid['marchant_id'];
		$country_code = $mid['marchant_id_type'];
		$clover_key   = $mid['clover_key'];
		$access_token = $mid['access_token'];
		if ($country_code == 1) {
			$currency = CURRENCY_CODE_USA;
		} else if ($country_code == 2) {
			$currency = CURRENCY_CODE_CAD;
		}
	} else {
		if ($country_code == 1) //For USA
		{
			$marchant_id  = MERCHANT_ID_USA;
			$country_code = $country_code;
			$clover_key   = CLOVER_KEY_USA;
			$access_token = ACCESS_TOKEN_USA;
			$currency     = CURRENCY_CODE_USA;
		} else if ($country_code == 2) //For CAD
		{
			$marchant_id  = MERCHANT_ID_CAD;
			$country_code = $country_code;
			$clover_key   = CLOVER_KEY_CAD;
			$access_token = ACCESS_TOKEN_CAD;
			$currency     = CURRENCY_CODE_CAD;
		}
	}

	$where = array(
		'user_id' => $usid,
		'business_id' => $business_id,
	);
	$result_card = $CI->dynamic_model->getdatafromtable('user_card_save', $where);
	if (empty($result_card) && ($savecard == '1')) {

		//For Card Not exist
		$response = clover_create_customer_profile($marchant_id, $clover_key, $access_token, $currency, $business_id, $usid, $token);
		//print_r($response);die;

		if ($response->id != '') {
			$transaction_data = array(
				'user_id' => $usid,
				'business_id' => $business_id,
				'card_id' => $response->cards->elements[0]->id,
				'profile_id' => $response->id,
				'customer_name' => $customer_name,
				'card_no' => encode($number),
				'expiry_year' => encode($expiry_year),
				'expiry_month' => encode($expiry_month),
				'card_token' => $response->cards->elements[0]->token,
				'card_type' => '' //$response->cards->elements[0]->cardType
			);
			$CI->dynamic_model->insertdata('user_card_save', $transaction_data);
			$customer_code = $response->id;

			$CI->dynamic_model->updateRowWhere('user', array('id' => $usid), array('clover_customer_profile_id' => $customer_code));
		}
	} elseif (!empty($result_card) && ($savecard == '1')) {

		//For Card Already Exist
		$response = clover_existing_customer_profile_card_add($marchant_id, $clover_key, $access_token, $currency, $business_id, $usid, $token);
		//print_r($response);die;
		if ($response->id != '') {
			$transaction_data = array(
				'user_id' => $usid,
				'business_id' => $business_id,
				'card_id' => $response->id,
				'profile_id' => $response->customer->id,
				'customer_name' => $customer_name,
				'card_no' => encode($number),
				'expiry_year' => encode($expiry_year),
				'expiry_month' => encode($expiry_month),
				'card_token' => $response->token,
				'card_type' => ''
			);
			$CI->dynamic_model->insertdata('user_card_save', $transaction_data);
			$customer_code = $response->customer->id;
		}
	} else if ($savecard == '0') {
		//Card Not save
		$customer_code = '';
	}
	//die;

	return array('customer_code' => $customer_code, 'marchant_id' => $marchant_id, 'country_code' => $country_code, 'clover_key' => $clover_key, 'access_token' => $access_token, 'currency' => $currency);
}



/**********************************************Token Wise clover payment api Start********************************************************/


function clover_api_create_customer_profile($marchant_id, $clover_key, $access_token, $currency, $business_id, $user_id, $token, $customer_name, $number, $expiry_month, $expiry_year, $cvd)
{
	$CI = &get_instance();
	//echo 'hi'; die;
	$merchant_order_data_marchantid = $marchant_id;
	$access_token    = $access_token;
	//$clover_base_url = CLOVER_BASE_URL;

	$where = array('id' => $user_id, 'status' => 'Active');
	$user_data = $CI->dynamic_model->getdatafromtable('user', $where);

	$where1 = array('id' => $business_id, 'status' => 'Active');
	$business_data = $CI->dynamic_model->getdatafromtable('business', $where1);


	$merchant_id   = $marchant_id;
	$emailAddress  = $user_data[0]['email'];
	$phoneNumber   = $user_data[0]['mobile'];
	//$first6        = substr($number, 0, 6);
	//$last4         = substr($number, -4);
	//$expirationDate= $expiry_month.'/'.$expiry_year;
	$token         = $token;
	$businessName  = ''; // $business_data[0]['business_name'];
	$firstName     = $user_data[0]['name'];
	$lastName      = $user_data[0]['lastname'];

	//$data = '{"merchant":{"id":"'.$merchant_id.'"},"emailAddresses":[{"customer":{},"emailAddress":"'.$emailAddress.'","primaryEmail":true}],"phoneNumbers":[{"customer":{},"phoneNumber":"'.$phoneNumber.'"}],"cards":[{"customer":{},"first6":"'.$first6.'","last4":"'.$last4.'","expirationDate":"'.$expirationDate.'","token":"'.$token.'","tokenType":"MULTIPAY"}],"metadata":{"customer":{"id":"'.$emailAddress.'"},"businessName":"'.$businessName.'"},"firstName":"'.$firstName.'","lastName":"'.$lastName.'","marketingAllowed":true}';
	/***********************************************************************************************/
	//$url_create_order = $clover_base_url.'v3/merchants/'.$merchant_order_data_marchantid.'/customers?expand=addresses,emailAddresses,phoneNumbers,cards,metadata&access_token='.$access_token;

	/*
		 $curlOrderPost = $data;
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url_create_order);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($curlOrderPost),'Authorization: Bearer '. $access_token));
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlOrderPost);
	    $pay_order_res = curl_exec($ch);
	    curl_close($ch);
	    //print_r($pay_order_res);die;
	    return $pay_order_data = json_decode($pay_order_res);
	    */

	$url = CLOVER_BASE_URL_NEW . '/customers';

	$curlPost = '{"email":"' . $emailAddress . '","firstName":"' . $firstName . '","lastName":"' . $lastName . '",
		  "source":"' . $token . '","shipping":{"address":{"city":"","country":"",
		  "line1":"","postal_code":"","state":""}}}';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($curlPost), 'Authorization: Bearer ' . $access_token)); //'idempotency-key:af2bbe3c4b4dd3682793cc09155a9a7a'
	curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
	$data = curl_exec($ch);
	curl_close($ch);
	//print_r($data);die;

	//{ "id" : "D5TATZMAD6QQW", "object" : "customer", "created" : 1617037169310, "currency" : "USD", "email" : "john.doe@customer.com", "name" : "John Doe", "sources" : { "object" : "list", "data" : [ "0J1PNVSM4YBD6" ] }, "shipping" : { "name" : "John Doe", "address" : { "line1" : "415 N Mathilda Ave", "city" : "Sunnyvale", "state" : "CA", "postal_code" : "94085", "country" : "US" } } }

	return $data = json_decode($data);
}

function clover_api_existing_customer_profile_card_add($marchant_id, $clover_key, $access_token, $currency, $business_id, $user_id, $token, $customer_name, $number, $expiry_month, $expiry_year, $cvd)
{
	//echo 'hi'; die;
	$CI = &get_instance();
	$merchant_order_data_marchantid = $marchant_id;
	$access_token    = $access_token;
	//$clover_base_url = CLOVER_BASE_URL;

	$where = array('id' => $user_id, 'status' => 'Active');
	$user_data = $CI->dynamic_model->getdatafromtable('user', $where);

	$where1 = array('id' => $business_id, 'status' => 'Active');
	$business_data = $CI->dynamic_model->getdatafromtable('business', $where1);
	//print_r($user_data );die;

	$merchant_id   = $marchant_id;
	$emailAddress  = $user_data[0]['email'];
	$phoneNumber   = $user_data[0]['mobile'];
	//$first6        = substr($number, 0, 6);
	//$last4         = substr($number, -4);
	//$expirationDate= $expiry_month.'/'.$expiry_year;
	$token         = $token;
	$businessName  = $business_data[0]['business_name'];
	$firstName     = $user_data[0]['name'];
	$lastName      = $user_data[0]['lastname'];
	$clover_customer_profile_id      = $user_data[0]['clover_customer_profile_id'];

	//$data = '{"first6":"'.$first6.'","last4":"'.$last4.'","firstName":"'.$firstName.'","lastName":"'.$lastName.'","expirationDate":"'.$expirationDate.'","token":"'.$token.'","tokenType":"MULTIPAY"}';
	/***********************************************************************************************/
	//$url_create_order = $clover_base_url.'v3/merchants/'.$merchant_order_data_marchantid.'/customers/'.$clover_customer_profile_id.'/cards?&access_token='.$access_token;
	/*$curlOrderPost = $data;
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url_create_order);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($curlOrderPost),'Authorization: Bearer '. $access_token));
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlOrderPost);
	    $pay_order_res = curl_exec($ch);
	    curl_close($ch);
	    //print_r($pay_order_res);die;
	    return $pay_order_data = json_decode($pay_order_res);  */

	$url = CLOVER_BASE_URL_NEW . '/customers/' . $clover_customer_profile_id;

	$curlPost = '{"email":"' . $emailAddress . '","firstName":"' . $firstName . '","lastName":"' . $lastName . '",
		  "source":"' . $token . '","shipping":{"address":{"city":"","country":"",
		  "line1":"","postal_code":"","state":""}}}';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($curlPost), 'Authorization: Bearer ' . $access_token)); //'idempotency-key:af2bbe3c4b4dd3682793cc09155a9a7a'
	curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
	$data = curl_exec($ch);
	curl_close($ch);
	//print_r($data);die;

	//{ "id" : "D5TATZMAD6QQW", "object" : "customer", "created" : 1617037169310, "currency" : "USD", "email" : "john.doe@customer.com", "name" : "John Doe", "sources" : { "object" : "list", "data" : [ "0J1PNVSM4YBD6" ] }, "shipping" : { "name" : "John Doe", "address" : { "line1" : "415 N Mathilda Ave", "city" : "Sunnyvale", "state" : "CA", "postal_code" : "94085", "country" : "US" } } }

	return $data = json_decode($data);
}

function clover_api_payment_checkout($user_cc_no, $user_cc_mo, $user_cc_yr, $user_cc_cvv, $user_zip, $amount, $taxAmount, $marchant_id, $clover_key, $access_token, $currency, $token)
{
	$CI = &get_instance();
	$merchant_order_data_marchantid = $marchant_id;
	$access_token    = $access_token;
	//$clover_base_url = CLOVER_BASE_URL;

	/*
	    	$user_cc_no      = 4111111111111111;
		    $user_cc_mo      = 03;
		    $user_cc_yr      = 2021;
		    $user_cc_cvv     = 123;
		    $user_zip        = 94041;
		    $amount          = 400;
		    $taxAmount       = 0 ;
		    $currency        = 'USD';
		    $token           = 'clv_1TSTSFwm1CHQ6yEGvmAT4LP2';
		*/

	$url = CLOVER_BASE_URL_NEW . '/charges/';

	$curlPost = '{"amount":' . $amount . ',"currency":"' . $currency . '","source":"' . $token . '"}';

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($curlPost), 'Authorization: Bearer ' . $access_token)); //'idempotency-key:af2bbe3c4b4dd3682793cc09155a9a7a'
	curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
	$data = curl_exec($ch);
	curl_close($ch);
	//print_r($data);die;
	$array_data = json_decode($data);
	return $array_data;

	/*
	    New response -

		{ "id" : "R48Q7GPMY2FQY", "amount" : 1800, "amount_refunded" : 0, "currency" : "usd", "created" : 1616661109846, "captured" : true, "ref_num" : "108400500020", "auth_code" : "OK2809", "outcome" : { "network_status" : "approved_by_network", "type" : "authorized" }, "paid" : true, "status" : "succeeded", "source" : { "id" : "clv_1TSTS1iWemARMj4AXHxHikLV", "brand" : "AMEX", "cvc_check" : "unchecked", "exp_month" : "11", "exp_year" : "2025", "first6" : "378282", "last4" : "0005" } }

		Old response -

		{"paymentId":"30PFKD66YXNCP","result":"APPROVED","authCode":"OK7823","token":"DGY73XR6DDDF0","vaultedCard":{"first6":"411111","last4":"1111","expirationDate":"0321","token":"1894469479681111"}}

	    */
	/****************** POST DATA TO PAYMENT API ******************/
}

function clover_api_card_profile_check($usid, $savecard, $customer_name, $number, $expiry_month, $expiry_year, $cvd, $country_code, $business_id, $token)
{
	$CI = &get_instance();

	/*$usid          = $this->input->post('user_id');
		$savecard      = 1;
		$customer_name = $this->input->post('name');
		$number        = $this->input->post('number');
		$expiry_month  = $this->input->post('expiry_month');
		$expiry_year   = $this->input->post('expiry_year');
		$cvd           = $this->input->post('cvv');
		$country_code  = $this->input->post('country_code');
		$business_id   = $this->input->post('business_id');
		$token         = $this->input->post('card_token');*/
	if ($customer_name != "") {
		$customer_name = $customer_name;
	} else {
		$customer_name = '';
	}
	if ($business_id != "") {
		$mid = getUserMarchantId($business_id);

		//var_dump($mid); die;
		$marchant_id  = $mid['marchant_id'];
		$country_code = $mid['marchant_id_type'];
		$clover_key   = $mid['clover_key'];
		$access_token = $mid['access_token'];
		if ($country_code == 1) {
			$currency = CURRENCY_CODE_USA;
		} else if ($country_code == 2) {
			$currency = CURRENCY_CODE_CAD;
		}
	} else {
		if ($country_code == 1) //For USA
		{
			$marchant_id  = MERCHANT_ID_USA;
			$country_code = $country_code;
			$clover_key   = CLOVER_KEY_USA;
			$access_token = ACCESS_TOKEN_USA;
			$currency     = CURRENCY_CODE_USA;
		} else if ($country_code == 2) //For CAD
		{
			$marchant_id  = MERCHANT_ID_CAD;
			$country_code = $country_code;
			$clover_key   = CLOVER_KEY_CAD;
			$access_token = ACCESS_TOKEN_CAD;
			$currency     = CURRENCY_CODE_CAD;
		}
	}
	$customer_code = '';
	$where = array(
		'user_id' => $usid,
		'business_id' => $business_id,
	);
	$result_card = $CI->dynamic_model->getdatafromtable('user_card_save', $where);
	if (empty($result_card) && ($savecard == '1')) {

		//For Card Not exist
		$response = clover_api_create_customer_profile($marchant_id, $clover_key, $access_token, $currency, $business_id, $usid, $token, $customer_name, $number, $expiry_month, $expiry_year, $cvd);
		//print_r($response);die;

		//{ "id" : "D5TATZMAD6QQW", "object" : "customer", "created" : 1617093766780, "currency" : "USD", "email" : "john.doe@customer.com", "name" : "John Doe", "sources" : { "object" : "list", "data" : [ "S48T5RC0D1HXT" ] }, "shipping" : { "name" : "John Doe", "address" : { "line1" : "415 N Mathilda Ave", "city" : "Sunnyvale", "state" : "CA", "postal_code" : "94085", "country" : "US" } } }

		if (isset($response->id) && $response->id != '') {
			$transaction_data = array(
				'user_id' => $usid,
				'business_id'  => $business_id,
				'card_id'      => $response->sources->data[0],
				'profile_id'   => $response->id,
				'customer_name' => $customer_name,
				'card_token'   => $token
			);
			$CI->dynamic_model->insertdata('user_card_save', $transaction_data);
			$customer_code = $response->id;

			$CI->dynamic_model->updateRowWhere('user', array('id' => $usid), array('clover_customer_profile_id' => $customer_code));
		}
	} elseif (!empty($result_card) && ($savecard == '1')) {

		//For Card Already Exist
		$response = clover_api_existing_customer_profile_card_add($marchant_id, $clover_key, $access_token, $currency, $business_id, $usid, $token, $customer_name, $number, $expiry_month, $expiry_year, $cvd);
		//print_r($response);die;

		//{ "id" : "D5TATZMAD6QQW", "object" : "customer", "created" : 1617093766780, "currency" : "USD", "email" : "john.doe@customer.com", "name" : "John Doe", "sources" : { "object" : "list", "data" : [ "S48T5RC0D1HXT" ] }, "shipping" : { "name" : "John Doe", "address" : { "line1" : "415 N Mathilda Ave", "city" : "Sunnyvale", "state" : "CA", "postal_code" : "94085", "country" : "US" } } }


		if (isset($response->id) && $response->id != '') {
			$transaction_data = array(
				'user_id' => $usid,
				'business_id'  => $business_id,
				'card_id'      => $response->sources->data[0],
				'profile_id'   => $response->id,
				'customer_name' => $customer_name,
				'card_token'   => $token
			);
			$CI->dynamic_model->insertdata('user_card_save', $transaction_data);
			$customer_code = $response->id;
		}
	} else if ($savecard == '0') {
		//Card Not save
		$customer_code = '';
	}
	//die;

	return array('customer_code' => $customer_code, 'marchant_id' => $marchant_id, 'country_code' => $country_code, 'clover_key' => $clover_key, 'access_token' => $access_token, 'currency' => $currency);
}



function send_sms($data)
{
	$CI = &get_instance();
	$phone = $data['phone'];
	$message = $data['message'];
	//PLIVO_FROM_NUMBER +17863086353 +17863086353
	$sms_data = array(
		'src' => '+16473635664', //'+14163584361',
		'dst' => $phone,
		'text' => $message,
		'type' => 'sms',
		'url' => base_url() . 'index.php/plivo_test/receive_sms',
		'method' => 'POST',
	);

	$response_array = $CI->plivo->send_sms($sms_data);

	//if ($response_array[0] == '200') {
	if ($response_array[0] == '202') {
		$data["response"] = json_decode($response_array[1], TRUE);
		return true;
	} else {
		//return false;
		return $response_array;
	}
}

function getUserQuestionnaire($userid, $class_id, $business_id)
{
	$CI = &get_instance();
	$where = array(
		'user_id' => $userid,
		'business_id' => $business_id,
		'class_id' => $class_id
	);
	$result = $CI->dynamic_model->getdatafromtable('user_questionnaire', $where);
	if (!empty($result)) {
		$danger_status = 0;
		foreach ($result as $key) {
			if ($key['question_id'] == '4') {
				$question_answer = trim($key['question_ans']);
				if (!empty($question_answer)) {
					$question_answer = @explode(',', $question_answer);
					$danger_status = 1;
				}
			} else {
				$question_answer = $key['question_ans'];
				if ($question_answer == '1') {
					$danger_status = 1;
				}
			}
		}
		$arrayName = array(
			'covid_info' => 1,
			'covid_status' => $danger_status,
		);
		return $arrayName;
	} else {
		return 0;
	}
}

function randomPassword($len = 8)
{

	//enforce min length 8
	if ($len < 8)
		$len = 8;

	//define character libraries - remove ambiguous characters like iIl|1 0oO
	$sets = array();
	$sets[] = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
	$sets[] = 'abcdefghjkmnpqrstuvwxyz';
	$sets[] = '23456789';
	$sets[]  = '!@#$&*';

	$password = '';

	//append a character from each set - gets first 4 characters
	foreach ($sets as $set) {
		$password .= $set[array_rand(str_split($set))];
	}

	//use all characters to fill up to $len
	while (strlen($password) < $len) {
		//get a random set
		$randomSet = $sets[array_rand($sets)];

		//add a random char from the random set
		$password .= $randomSet[array_rand(str_split($randomSet))];
	}

	//shuffle the password string before returning!
	return str_shuffle($password);
}

function getShift($type = 1, $business_id, $user_or_type_id, $limit, $offset, $orderBy, $orderType = 'DESC', $shiftDate = '', $oldStatus = 1, $shift_status = 0, $start_date = '', $end_date = '')
{
	// Type 1 : List, Type 2 : According Instructor, Type 3 : Details
	$businessUrl = base_url('uploads/business/');
	$CI = &get_instance();

	$CI->db->select('business_shift_scheduling.id, business_shift_scheduling.shift_date, business_shift_scheduling.shift_date_str, business_shift_scheduling.day_id, business_shift_scheduling.start_time, business_shift_scheduling.end_time, business_shift_scheduling.status as shift_schedule_status, (CASE WHEN business_shift_scheduling.status = 1 THEN "Active" WHEN business_shift_scheduling.status = 2 THEN "In-Active" ELSE "Cancel" END) as shift_schedule_status_name');
	$CI->db->select('manage_week_days.week_name');
	$CI->db->select('business_shift.id as shift_id, business_shift.business_id, (CASE WHEN business_shift.shift_name = 1 THEN "Service Shift" ELSE "Non-Service  Shift" END) as shift_name, business_shift.location_id, business_shift.start_date, business_shift.end_date, business_shift.duration, business_shift.pay_type, business_shift.pay_rate, business_shift.description');
	$CI->db->select('business_location.location_name');
	$CI->db->select('CONCAT(user.name, " ", user.lastname) as instructor');
	$CI->db->select('business.business_name, concat("' . $businessUrl . '", business.business_image) as business_image');
	$CI->db->select('business_shift_instructor.status');
	$CI->db->select('(CASE WHEN business_shift_instructor.status = 1 THEN "Assign" WHEN business_shift_instructor.status = 2 THEN "Cancel Request Pending" ELSE "Instructor Change" END) as status_text');
	$CI->db->from('business_shift_scheduling');
	$CI->db->join('manage_week_days', 'manage_week_days.id = business_shift_scheduling.day_id');
	$CI->db->join('business_shift', 'business_shift.id = business_shift_scheduling.shift_id');
	$CI->db->join('business_location', 'business_location.id = business_shift.location_id');
	$CI->db->join('business_shift_instructor', 'business_shift_instructor.shift_id = business_shift.id');
	$CI->db->join('user', 'user.id = business_shift_instructor.instructor');
	$CI->db->join('business', 'business.id = business_shift.business_id');
	if (!empty($business_id)) {
		$CI->db->where('business_shift.business_id', $business_id);
	}
	if (!empty($shiftDate)) {
		$CI->db->where('business_shift_scheduling.shift_date_str', $shiftDate);
	}
	if (!$oldStatus) {
		//$CI->db->where('business_shift_scheduling.shift_date_str >=', date('Y-m-d'));
	}
	if ($type === 1) {
		$CI->db->where('business_shift.user_id', $user_or_type_id);
	} else if ($type === 2) {

		$CI->db->where('business_shift_instructor.instructor', $user_or_type_id);
	} else {
		$CI->db->where('business_shift_scheduling.id', $user_or_type_id);
	}

	/*
			0 = all d
			1 = my features d
			2 = cancel D
			3 = compleated
		*/
	if ($shift_status == '2') {
		$CI->db->where('business_shift_scheduling.status', '3');
	} else {
		$CI->db->where('business_shift_scheduling.status', '1');
	}

	if (!empty($start_date) && !empty($end_date)) {
		$CI->db->where('business_shift_scheduling.shift_date_str >=', $start_date);
		$CI->db->where('business_shift_scheduling.shift_date_str <=', $end_date);
	}

	$currentDate = strtotime(date('Y-m-d'));
	if ($shift_status == '0') {
		$CI->db->where('business_shift_scheduling.shift_date >=', $currentDate);
		$orderBy = 'business_shift_scheduling.start_time';
		$orderType = 'ASC';
	} else if ($shift_status == '1') {
		$CI->db->where('business_shift_scheduling.shift_date >=', $currentDate);
		$orderBy = 'business_shift_scheduling.start_time';
		$orderType = 'ASC';
	} else if ($shift_status == '2') {
		$CI->db->where('business_shift_scheduling.shift_date >=', $currentDate);
		$orderBy = 'business_shift_scheduling.start_time';
		$orderType = 'DESC';
	} else if ($shift_status == '3') {
		$CI->db->where('business_shift_scheduling.shift_date <', $currentDate);
		$orderBy = 'business_shift_scheduling.start_time';
		$orderType = 'DESC';
	}
	if ($limit != '') {
		$CI->db->limit($limit, $offset);
	}

	if ($orderBy != '') {
		$CI->db->order_by($orderBy, $orderType);
	}
	$query = $CI->db->get();
	//echo $CI->db->last_query(); die;
	if ($type === 1 || $type === 2) {
		if ($query->num_rows() > 0) {
			$collection = $query->result_array();
			array_walk($collection, function (&$key) {
				$CISUb = &get_instance();
				$shiftId = $key['shift_id'];
				$shiftScheduling = $key['id'];
				$imgPath = base_url() . 'uploads/user/';
				$query = 'select u.id, u.name, u.lastname, concat("' . $imgPath . '", u.profile_img) as profile,c.comment,c.create_dt,c.instructor,c.shift_id, c.type from business_shift_instructor_comments as c JOIN user as u on (u.id = c.instructor) where c.type = 1 AND c.shift_schedule_id = ' . $shiftScheduling . ' AND c.shift_id = ' . $shiftId . ' ORDER BY c.id asc';
				$comment = $CISUb->db->query($query)->result_array();
				// $key['comment'] = $CISUb->db->query($query)->result_array();

				$queryStudio = 'select u.id, u.name, u.lastname, concat("' . $imgPath . '", u.profile_img) as profile,c.comment,c.create_dt,c.instructor,c.shift_id, c.type from business_shift_instructor_comments as c JOIN user as u on (u.id = c.owner_id) where c.type = 2 AND c.shift_schedule_id = ' . $shiftScheduling . ' AND c.shift_id = ' . $shiftId . ' ORDER BY c.id asc';
				$owner_comment = $CISUb->db->query($queryStudio)->result_array();
				$common_array = array_merge($comment, $owner_comment);
				usort($common_array, function ($item1, $item2) {
					return $item1['create_dt'] <=> $item2['create_dt'];
				});
				$key['comment'] = $common_array;
				$key['owner_comment'] = $owner_comment;
			});

			return $collection;
		} else {
			return $query->result_array();
		}
		// return $query->result_array();
	} else {
		if ($query->num_rows() > 0) {

			$collection = $query->row_array();
			$CISUb = &get_instance();
			$shiftId = $collection['shift_id'];
			$shiftScheduling = $collection['id'];
			$imgPath = base_url() . 'uploads/user/';

			$query = 'select u.id, u.name, u.lastname, concat("' . $imgPath . '", u.profile_img) as profile,c.comment,c.create_dt,c.instructor,c.shift_id, c.type from business_shift_instructor_comments as c JOIN user as u on (u.id = c.instructor) where c.type = 1 AND c.shift_schedule_id = ' . $shiftScheduling . ' AND c.shift_id = ' . $shiftId . ' ORDER BY c.id asc';
			$comment = $CISUb->db->query($query)->result_array();

			$queryStudio = 'select u.id, u.name, u.lastname, concat("' . $imgPath . '", u.profile_img) as profile,c.comment,c.create_dt,c.instructor,c.shift_id, c.type from business_shift_instructor_comments as c JOIN user as u on (u.id = c.owner_id) where c.type = 2 AND c.shift_schedule_id = ' . $shiftScheduling . ' AND c.shift_id = ' . $shiftId . ' ORDER BY c.id asc';
			$owner_comment = $CISUb->db->query($queryStudio)->result_array();

			$common_array = array_merge($comment, $owner_comment);
			usort($common_array, function ($item1, $item2) {
				return $item1['create_dt'] <=> $item2['create_dt'];
			});
			$collection['comment'] = $common_array;
			$collection['owner_comment'] = $owner_comment;
			return $collection;
		} else {
			return $query->row_array();
		}
	}
}

function getUserMarchantId($business_id)
{
	$CI = &get_instance();
	$where = array('business_id' => $business_id);
	$sql = "select u.cad_marchant_id,u.marchant_id,u.marchant_id_type,u.clover_key,u.access_token from business as b join user as u on b.user_id = u.id where b.id = '" . $business_id . "'";
	$query = $CI->db->query($sql);
	if ($query->num_rows() > 0) {
		$result = $query->row();
		$marchant_id = $result->marchant_id;
		$cad_marchant_id = $result->cad_marchant_id;
		$marchant_id_type = $result->marchant_id_type;
		$clover_key = $result->clover_key;
		$access_token = $result->access_token;

		$arrayName = 0;
		if ($marchant_id_type == '1') {
			$arrayName = $marchant_id;
		} else if ($marchant_id_type == '2') {
			$arrayName = $marchant_id; //$cad_marchant_id;
		}
		$data = array(
			'marchant_id_type' => $marchant_id_type,
			'marchant_id' => trim($arrayName),
			'clover_key' => trim($clover_key),
			'access_token' => trim($access_token)
		);
		return $data;
	} else {
		return 0;
	}
}

function getBusinessId($user_id = '')
{
	$CI = get_instance();
	$condition = array('user_id' => $user_id, 'status' => "Pending");
	$result = $CI->dynamic_model->getdatafromtable('user_booking', $condition, '', '', '', 'id', 'DESC');
	if (!empty($result)) {
		$business_id = 0;
		foreach ($result as $key => $value) {
			$business_id = $value['business_id'];
		}
		return $business_id;
	} else {
		return false;
	}
}

function get_all_pass($business_id, $user_id, $pass_display_con = '')
{
	$CI = &get_instance();
	$where = array("business_id" => $business_id, "status" => "Active");
	$class_data = $CI->dynamic_model->getdatafromtable('business_class', $where);
	$pass_info = array();
	$time = time();
	if (!empty($class_data)) {

		if ($pass_display_con == 'without_purchase') {
			$sql = "SELECT * FROM user_booking WHERE service_type = 1 AND status = 'Success' AND passes_status = '1' AND user_id = $user_id AND business_id = $business_id";
			$query = $CI->db->query($sql)->result_array();
			$pass_id = '';
			if (!empty($query)) {
				foreach ($query as $key => $value) {
					$pass_id .= $value['service_id'] . ',';
				}
				$pass_id = rtrim($pass_id, ",");
			}
		}

		foreach ($class_data as $key => $value) {
			$class_id = $value['id'];
			$CI->db->select('b.*,b.pass_type as passId,mp.pass_type, (SELECT u.id FROM user_booking as u where u.service_id = bpa.pass_id AND u.status = "Success" AND u.passes_status = "1" AND u.service_type = "1" AND u.business_id = "' . $business_id . '" AND u.user_id = "' . $user_id . '" ) as user_booking_id');
			$CI->db->from('business_passes_associates as bpa');
			$CI->db->join('business_passes b', 'b.id = bpa.pass_id');
			$CI->db->join('manage_pass_type mp', 'mp.id = b.pass_type_subcat', 'LEFT');

			if ($pass_display_con == 'without_purchase') {
				$CI->db->where('b.purchase_date <=', $time);
				$blockidsss = "b.id NOT IN ('" . $pass_id . "')";
				$CI->db->where($blockidsss);
			} else {
				$CI->db->where('bpa.class_id', $class_id);
			}

			$CI->db->where('bpa.business_id', $business_id);
			$CI->db->where('b.is_client_visible', "yes");
			$CI->db->where('b.status', "Active");
			$CI->db->group_by('bpa.pass_id');
			$passes_data = $CI->db->get()->result_array();
			$arg['status'] = 0;
			$arg['last'] = $CI->db->last_query();
			//echo json_encode($arg); exit;
			//echo $CI->db->last_query(); die;
			if (!empty($passes_data)) {
				foreach ($passes_data as $key_1 => $value_1) {
					//echo '<pre/>'; print_r($value_1); die;
					$pass_id = $value_1['pass_id'];
					$passId = $value_1['passId'];
					$pass_type_subcat = $value_1['pass_type_subcat'];

					$where = array(
						"user_id" => $user_id,
						"service_id" => $value_1['id'],
						"service_type" => '2'
					);
					$user_favourite = $CI->dynamic_model->getdatafromtable("user_business_favourite", $where);
					$favourite = (!empty($user_favourite)) ? '1' : '0';

					$where = array("id" => $passId);
					$manage_pass_data = $CI->dynamic_model->getdatafromtable("manage_pass_type", $where);
					$pass_type_name = '';
					if (!empty($manage_pass_data)) {
						$pass_type_name = $manage_pass_data[0]['pass_type'];
					}

					$where = array("id" => $pass_type_subcat);
					$manage_pass_res = $CI->dynamic_model->getdatafromtable("manage_pass_type", $where);
					$pass_days = '';
					if (!empty($manage_pass_res)) {
						$pass_days = $manage_pass_res[0]['pass_days'];
					}



					$pass_for = $value_1['pass_for'];
					if ($pass_for == '0') {
						$pass_for_label = 'Class Pass';
					} else if ($pass_for == '1') {
						$pass_for_label = 'Workshop Pass';
					}

					/*if($value_1['pass_validity'] > '1'){
						$pass_validity = $value_1['pass_validity']. ' Days';
					}else if($value_1['pass_validity'] == '1'){
						$pass_validity = $value_1['pass_validity']. ' Day';
					}else{
						$pass_validity = $value_1['pass_validity'];
					}*/

					if ($passId == '1') {
						// class
						if ($pass_days > '1') {
							$pass_validity = $pass_days . ' Classes';
						} else if ($pass_days == '1') {
							$pass_validity = $pass_days . ' Class';
						} else {
							$pass_validity = $pass_days;
						}
					} else {
						if ($pass_days > '1') {
							$pass_validity = $pass_days . ' Days';
						} else if ($pass_days == '1') {
							$pass_validity = $pass_days . ' Day';
						} else {
							$pass_validity = $pass_days;
						}
					}





					$amount = $value_1['amount'];
					$pass_type = $value_1['pass_type'];
					if ($value_1['pass_type_subcat'] == '36') {
						$pass_type = $pass_type . ' ($' . $amount . ')';
					}

					if ($value_1['pass_type_subcat'] == '36') {
						$today_dt = date('d');
						$a_date = date("Y-m-d");
						$lastmonth_dt = date("t", strtotime($a_date));
						$diff_dt = $lastmonth_dt - $today_dt;
						$diff_dt = $diff_dt + 1;
						$rt = date("Y-m-t", strtotime($a_date));
						$recurring_date = $rt;
						$pass_end_date = strtotime($rt);
						$passes_remaining_count = $diff_dt;
						$per_day_amt = $amount / $lastmonth_dt;
						$per_day_amt = round($per_day_amt, 2);
						$amount = $per_day_amt * $diff_dt;

						$amount = number_format($amount, 2);
					} else {
						$amount = number_format($amount, 2);
					}

					$passes_start_date = $value_1['purchase_date'];
					$user_booking_id = $value_1['user_booking_id'] ? $value_1['user_booking_id'] : '';
					if (!empty($user_booking_id)) {
						$where = array("id" => $user_booking_id);
						$u_data = $CI->dynamic_model->getdatafromtable("user_booking", $where);
						if (!empty($u_data)) {
							$passes_start_date = $u_data[0]['passes_start_date'];
						}
					}

					$pass_info[] = array(
						'user_booking_id' => $user_booking_id,
						'id' => $value_1['id'],
						'business_id' => $value_1['business_id'],
						'pass_for' => '', //$pass_for_label,
						'pass_type' => $pass_type,
						'pass_type_subcat' => $value_1['pass_type_subcat'],
						'pass_id' => $value_1['pass_id'],
						'pass_name' => $value_1['pass_name'],
						'pass_validity' => $pass_validity,
						'amount' => $amount,
						'age_restriction' => $value_1['age_restriction'],
						'age_over_under' => $value_1['age_over_under'],
						'description' => $value_1['description'],
						'notes' => $value_1['notes'],
						'passes_start_date' => $passes_start_date,
						'favourite' => $favourite,
						'pass_type_name' => $pass_type_name
					);
				}
				return $pass_info;
			} else {
				return $pass_info;
			}
		}
	} else {
		return $pass_info;
	}
}

function get_all_workshop_schedule($business_id, $workshop_id = '', $schedule_id = '', $user_id = '')
{

	$CI = &get_instance();
	$business_logo = site_url() . 'uploads/business/';
	$CI->db->select('bwm.id as workshop_id, bwm.workshop_capacity as total_capacity, bwm.name as workshop_name, bwm.description as workshop_description, bwm.price as workshop_price, bwm.tax1, bwm.tax1_rate, bwm.tax2, bwm.tax2_rate');
	$CI->db->select('bws.id, bws.schedule_date, bws.schedule_dates, bws.start, bws.end, bws.address');
	$CI->db->select('b.business_name, concat("' . $business_logo . '", b.logo) as business_logo, concat("' . $business_logo . '", b.business_image) as business_image');
	// $CI->db->select('IF(bws.location = 0, "", bl.location_name) as location, IF(bws.location = 0, "", bl.location_url) as location_url');
	$CI->db->select('IF(bws.location = 0, "", bl.location_name) as location');
	$CI->db->select('IF(bws.location = 0, "", bl.map_url) as location_url');
	$CI->db->select('IF(bws.location = 0, "", bl.location_url) as web_link');

	$CI->db->from('business_workshop_schdule as bws');
	$CI->db->join('business_workshop_master as bwm', 'bwm.id = bws.workshop_id');
	$CI->db->join('business b', 'b.id = bwm.business_id');
	$CI->db->join('business_location bl', 'bl.id = bws.location', 'left');
	$CI->db->where('b.status', 'Active');
	// $CI->db->where('bws.status', 'Active');
	$CI->db->where('bwm.business_id', $business_id);

	if (!empty($workshop_id)) {
		$CI->db->where('bwm.id', $workshop_id);
	}

	if (!empty($schedule_id)) {
		$CI->db->where('bws.id', $schedule_id);
	}

	$workshop_data = $CI->db->get()->row_array();
	// echo $CI->db->last_query();
	// print_r($workshop_data); die;
	$workshop_info = array();

	if (!empty($schedule_id) && !empty($workshop_data)) {
		// Row Array Data
		$workshop_data['instructor_details'] = instructor_details_fun($workshop_data['id']);
		$workshop_data['is_purchase'] = '0';
		if (!empty($user_id)) {
			$workshop_data['is_purchase'] = $CI->db->get_where('user_booking', array(
				//'class_id'  =>  $workshop_data['id'],
				'service_id' =>  $workshop_data['workshop_id'],
				'user_id'   =>  $user_id,
				'status'    =>  'Success',
				'service_type'    =>  '4'
			))->num_rows();
		}

		$scheduleId = $workshop_data['id'];
		$workshop_id = $workshop_data['workshop_id'];
		$user_logo = site_url() . 'uploads/user/';
		$query = "SELECT user.name, concat('" . $user_logo . "', user.profile_img) as profile_img, user.lastname, user.gender, case when user.date_of_birth = '' then '' else DATE_FORMAT(FROM_DAYS(DATEDIFF(now(), user.date_of_birth)), '%Y')+0 end as Age, user_booking.status FROM user_booking JOIN user on (user.id = user_booking.user_id) where user_booking.status != 'Cancel' AND user_booking.service_id = " . $workshop_id . " and user_booking.service_type = 4";

		//DATE_FORMAT(FROM_DAYS(DATEDIFF(now(), user.date_of_birth)), '%Y')+0 AS Age,
		//IF(user.date_of_birth IS NOT NULL, DATE_FORMAT(FROM_DAYS(DATEDIFF(now(), user.date_of_birth)), '%Y')+0, '') as Age




		$workshop_data['customer_details'] = $CI->db->query($query)->result_array();
		$workshop_data['capacity_used'] = $CI->db->get_where('user_booking', array(
			//'class_id' => $workshop_data['id'],
			'service_id' => $workshop_data['workshop_id'],
			'service_type' => '4',
			'status' => 'Success'
		))->num_rows();
		$workshop_price = $workshop_data['workshop_price'];
		$workshop_tax_price = 0;
		$tax1_rate_val = 0;
		$tax2_rate_val = 0;
		$workshop_total_price = $workshop_price;
		if ($workshop_data['tax1'] == 'yes') {
			$tax1_rate = floatVal($workshop_data['tax1_rate']);
			// $tax1_rate_val = (($workshop_price * $tax1_rate) / 100);
			$tax1_rate_val = ($tax1_rate / 100) * $workshop_price;
			$workshop_tax_price = $tax1_rate_val;
			$workshop_total_price = $workshop_price + $tax1_rate_val;
		}
		if ($workshop_data['tax2'] == 'yes') {
			$tax2_rate = floatVal($workshop_data['tax2_rate']);
			// $tax2_rate_val = (($workshop_price * $tax2_rate) / 100);
			$tax2_rate_val = ($tax2_rate / 100) * $workshop_price;;
			$workshop_tax_price = $tax1_rate_val + $tax2_rate_val;
			$workshop_total_price = $workshop_total_price + $tax2_rate_val;
		}

		$workshop_data['tax1_rate'] = number_format($tax1_rate_val, 2);
		$workshop_data['tax2_rate'] = number_format($tax2_rate_val, 2);

		$workshop_data['workshop_tax_price'] = number_format($workshop_tax_price, 2);
		$workshop_data['workshop_total_price'] = number_format($workshop_total_price, 2);
		return $workshop_data;
	} else {
		// Row Id
	}
}

function get_all_workshop($business_id, $workshop_id = '', $schedule_id = '')
{
	$CI = &get_instance();

	$CI->db->select('bwm.*,b.business_name,b.logo,b.business_image');
	$CI->db->from('business_workshop_master as bwm');
	$CI->db->join('business b', 'b.id = bwm.business_id');
	$CI->db->where('bwm.business_id', $business_id);
	if (!empty($workshop_id)) {
		$CI->db->where('bwm.id', $workshop_id);
	}
	$CI->db->where('b.status', 'Active');
	$workshop_data = $CI->db->get()->result_array();
	$workshop_info = array();
	if (!empty($workshop_data)) {
		foreach ($workshop_data as $key => $value) {
			$workshop_id = $value['id'];
			$business_logo = site_url() . 'uploads/business/' . $value['logo'];
			$business_image = site_url() . 'uploads/business/' . $value['business_image'];

			// , CASE WHEN bl.location_url IS NULL THEN "" Else bl.location_url END as location_url
			$CI->db->select('bws.*,bl.location_name,bl.is_address_same,bl.address as business_location_address, (CASE WHEN bl.location_url IS NULL THEN "" ELSE bl.location_url END) as web_link, (CASE WHEN bl.map_url IS NULL THEN "" ELSE bl.map_url END) as location_url'); // ,

			// $CI->db->select('(CASE WHEN bl.map_url IS NULL THEN "" Else bl.map_url END) as location_url, CASE WHEN bl.location_url IS NULL THEN "" Else bl.location_url END as web_link');

			$CI->db->from('business_workshop_schdule as bws');
			$CI->db->join('business_location bl', 'bl.id = bws.location', 'left');
			$CI->db->where('bws.workshop_id', $workshop_id);
			if (!empty($schedule_id)) {
				$CI->db->where('bws.id', $workshop_id);
			}
			$CI->db->where('bws.status', "Active");
			$schdule_data = $CI->db->get()->result_array();
			if (!empty($schdule_data)) {
				foreach ($schdule_data as $key_1 => $value_1) {
					//echo '<pre/>'; print_r($value_1); die;
					$instructor_details = instructor_details_fun($value_1['id']);

					$schedule_id = $value_1['id'];
					$workshop_id = $value_1['workshop_id'];

					$user_logo = site_url() . 'uploads/user/';
					$query = "SELECT user.name, concat('" . $user_logo . "', user.profile_img) as profile_img, user.lastname, user.gender, DATE_FORMAT(FROM_DAYS(DATEDIFF(now(), user.date_of_birth)), '%Y')+0 AS Age, user_booking.status FROM user_booking JOIN user on (user.id = user_booking.user_id) where user_booking.class_id = " . $schedule_id . " AND user_booking.service_id = " . $workshop_id . " and user_booking.service_type = 4";

					$customer_details = $CI->db->query($query)->result_array();
					$is_purchase = 0;
					$capacity_used = 0;

					$price = $value['price'];
					$workshop_total_price = $price;
					$tax1_rate = $value['tax1_rate'];
					$workshop_tax_price = 0;
					$tax1_rate_val = 0;
					$tax2_rate_val = 0;
					if ($value['tax1'] == 'yes') {
						// $tax1_rate_val = (($price * $tax1_rate) / 100);
						$tax1_rate_val = ($tax1_rate / 100) * $price;
						$workshop_tax_price = $price + $tax1_rate_val;
						$workshop_total_price = $price + $tax1_rate_val;
					}
					if ($value['tax2'] == 'yes') {
						$tax2_rate = $value['tax2_rate'];
						// $tax2_rate_val = (($price * $tax2_rate) / 100);
						$tax2_rate_val = ($tax2_rate / 100) * $price;
						$workshop_tax_price = $price + $tax2_rate_val;
						$workshop_total_price = $workshop_total_price + $tax2_rate_val;
					}

					$workshop_info = array(
						'id' => $value_1['id'],
						'workshop_id' => $value_1['workshop_id'],
						'total_capacity' => $value_1['capacity'],
						'schedule_date' => $value_1['schedule_date'],
						'schedule_dates' => $value_1['schedule_dates'],
						'location' => $value_1['location_name'],
						'location_url' => $value_1['location_url'],
						'business_location' => $value_1['location_name'],
						'is_address_same' => $value_1['is_address_same'],
						'business_location_address' => $value_1['business_location_address'],
						'start' => $value_1['start'],
						'end' => $value_1['end'],
						'address' => $value_1['address'],
						'workshop_name' => $value['name'],
						'workshop_description' => $value['description'],
						'workshop_price' => $value['price'],
						'workshop_tax_price' => number_format($workshop_tax_price, 2),
						'workshop_total_price' => number_format($workshop_total_price, 2),
						'tax1' => $value['tax1'],
						'tax1_rate' => number_format($tax1_rate_val, 2),
						'tax2' => $value['tax2'],
						'tax2_rate' => number_format($tax2_rate_val, 2),
						'business_name' => $value['business_name'],
						'business_logo' => $business_logo,
						'business_image' => $business_image,
						'instructor_details' => $instructor_details,
						'is_purchase' => $is_purchase,
						'customer_details' => $customer_details,
						'capacity_used' => $capacity_used
					);
				}
			}
		}
	}
	return $workshop_info;
}

function instructor_details_fun($schedule_id)
{
	$CI = &get_instance();

	$CI->db->select('u.*');
	$CI->db->from('business_workshop_schdule_instructor as bwsi');
	$CI->db->join('user u', 'u.id = bwsi.user_id');
	$CI->db->where('bwsi.schedule_id', $schedule_id);
	$instructor_data = $CI->db->get()->result_array();
	$instructor_info = array();
	if (!empty($instructor_data)) {
		foreach ($instructor_data as $key => $value) {
			$profile_img = site_url() . 'uploads/user/' . $value['profile_img'];
			$instructor_info[] = array(
				'instructor_id' => $value['id'],
				'instructor_name' => $value['name'],
				'instructor_profile_img' => $profile_img,
			);
		}
	}
	return $instructor_info;
}

function get_services_list($userid, $limit = 25, $offset = 0)
{
	$CI = &get_instance();
	$imgePath = base_url() . 'uploads/user/';
	$query = "SELECT t.create_dt, IFNULL(concat(uf.name, '', uf.lastname),'') as family_member_name, IFNULL(uf.gender,'') as family_gender, IFNULL(uf.date_of_birth,'') as family_dob,(CASE WHEN uf.profile_img != '' THEN CONCAT('" . $imgePath . "',uf.profile_img) ELSE '' END ) as family_profile_img, b.family_user_id,s.id as service_id, t.id as transaction_id,t.user_id,t.amount,t.discount,t.trx_id,t.order_number,t.create_dt as payment_date,t.payment_type,t.payment_method,t.responce_all,u.name,u.lastname, u.gender, u.date_of_birth,concat('" . $imgePath . "', u.profile_img) as profile_img, s.service_name,s.duration,s.cancel_policy,s.description, s.tax1, s.tax2, s.tax1_rate, s.tax2_rate,bs.business_name,bs.address,bs.location_detail,uu.name as instructor_name,uu.lastname as instructor_lastname,concat('" . $imgePath . "', uu.profile_img) as instructor_profile_img, b.status as booking_status,b.passes_start_date as start_time, b.passes_end_date as end_time, b.shift_date, b.tip_comment, bl.location_name,bl.address as location_address,'3' as purchase_status  FROM transactions AS t join user_booking as b on t.id = b.transaction_id JOIN user as u on u.id = b.user_id join service as s on s.id = b.service_id JOIN business as bs on bs.id = b.business_id JOIN user as uu on uu.id = b.shift_instructor LEFT Join business_shift as bshift on bshift.id = b.shift_id left join business_location as bl on bl.id = bshift.location_id left join user as uf on uf.id = b.family_user_id WHERE b.user_id = " . $userid . " AND b.service_type = '2' ORDER BY b.passes_start_date DESC LIMIT $limit OFFSET $offset";
	/*  	if(!empty($transaction_id)){
        $query .= "AND t.id = '".$transaction_id."'";
    }
    if(!empty($start_dt)){
        $start_dt = date('Y-m-d',$start_dt);
        $query .= " AND b.shift_search_date = '".$start_dt."'";
	}
	$query .= " ORDER BY b.create_dt desc LIMIT ".$limit.' OFFSET '.$offset;
*/
	$collection = $CI->db->query($query)->result_array();

	if (!empty($collection)) {
		array_walk($collection, function (&$key) {

			$key['location_name'] = $key['location_name'] ? $key['location_name'] : '';
			$key['location_address'] = $key['location_address'] ? $key['location_address'] : '';
		});
	}
	//location_name
	return $collection;
}

if (!function_exists('helpAppointmentSendEmail')) {
	function helpAppointmentSendEmail($transaction_id)
	{

		$CI = get_instance();

		$imgePath = base_url() . 'uploads/user/';
		$query = "SELECT t.payment_status, u.name as customer_fname, u.lastname as customer_lname, u.address as customer_address, u.email as customer_email, uu.name as ins_first, uu.lastname as ins_surname, uu.address as ins_address, uu.email as instructor_email, uu.mobile as instructor_mobile, ins.registration, bshift.start_date,b.status, IFNULL(concat(uf.name, '', uf.lastname),'') as family_member_name, IFNULL(uf.gender,'') as family_gender, IFNULL(uf.date_of_birth,'') as family_dob,(CASE WHEN uf.profile_img != '' THEN CONCAT('" . $imgePath . "',uf.profile_img) ELSE '' END ) as family_profile_img, b.family_user_id, s.id as service_id, t.id as transaction_id,t.user_id,t.amount,t.transactions_tax,t.discount,t.trx_id,t.order_number,t.create_dt as payment_date,t.payment_type,t.payment_method,t.responce_all,u.name,u.lastname, u.gender, u.date_of_birth,concat('" . $imgePath . "', u.profile_img) as profile_img, s.service_name,s.duration,s.cancel_policy,s.description, s.tax1, s.tax2, s.tax1_rate, s.tax2_rate, bs.business_image, bs.business_phone, bs.primary_email as business_email, bs.business_name,bs.address,bs.location_detail,uu.name as instructor_name,uu.lastname as instructor_lastname,concat('" . $imgePath . "', uu.profile_img) as instructor_profile_img, b.passes_start_date as start_time, b.passes_end_date as end_time, b.shift_date, CASE WHEN bl.location_name IS NULL THEN '' Else bl.location_name END as location_name, CASE WHEN bl.address IS NULL THEN '' Else bl.address END as location_address, CASE WHEN bl.location_url IS NULL THEN '' Else bl.location_url END as location_url, CASE WHEN bl.map_url IS NULL THEN '' Else bl.map_url END as map_url, b.status as booking_status, b.tip_comment FROM transactions AS t join user_booking as b on t.id = b.transaction_id JOIN user as u on u.id = b.user_id join service as s on s.id = b.service_id JOIN business as bs on bs.id = b.business_id JOIN user as uu on uu.id = b.shift_instructor LEFT Join business_shift as bshift on bshift.id = b.shift_id left join business_location as bl on bl.id = bshift.location_id JOIN instructor_details as ins on (ins.user_id = uu.id) left join user as uf on uf.id = b.family_user_id WHERE b.service_type = '2' AND t.id = " . $transaction_id;
		$collection = $CI->db->query($query)->row_array();

		$collection['b_start_date'] = date('d-m-y', $collection['start_date']);
		$workshop_price = $collection['amount'];
		$transactions_tax = $collection['transactions_tax'];
		$amount = $workshop_price - $transactions_tax;
		$collection['amount'] = number_format($amount, 2);
		$total = 0;
		$tax1_rate = 0;
		$tax2_rate = 0;
		if ($collection['tax1'] == 'Yes') {
			$tax1_rate = ($collection['tax1_rate'] / 100) * $collection['amount'];
			$total += $tax1_rate;
			$collection['tax1_rate'] = number_format($tax1_rate, 2);
		}
		if ($collection['tax2'] == 'Yes') {
			$tax2_rate = ($collection['tax2_rate'] / 100) * $collection['amount'];
			$total += $tax2_rate;
			$collection['tax2_rate'] = number_format($tax2_rate, 2);
		}
		$collection['service_tax_price'] = number_format($total, 2); // number_format($transactions_tax,2);
		$collection['service_total_price'] = number_format($amount + $total, 2);


		$payment_status = ($collection['payment_status'] == 'Confirm') ? 'Mark as Complete' : (

			($collection['payment_status'] == 'Completed' ? 'Payment Pending' : ($collection['payment_status'] == 'Success' ? 'Paid' : $collection['payment_status'])));

		$data = array(
			'transaction_id'		=> $transaction_id,
			'registration'			=>	$collection['registration'],
			'business_image'		=>	site_url() . 'uploads/business/' . $collection['business_image'],
			'business_phone'		=>	$collection['business_phone'],
			'business_email'		=>	$collection['business_email'],
			'instructor_name'		=>	$collection['ins_first'] . ' ' . $collection['ins_surname'],
			'instructor'			=>	$collection['instructor_email'],
			'instructor_address'	=>	$collection['ins_address'],
			'tax1'					=>	$tax1_rate,
			'tax1_percentage'		=>	$collection['tax1_rate'],
			'tax2_percentage'		=>	$collection['tax2_rate'],
			'tax2'					=>	$tax2_rate,
			'customer_email'		=>	$collection['customer_email'],
			'service_provider' 		=> 	$collection['instructor_name'],
			'service_type'			=> 	$collection['service_name'],
			'business_name'			=> 	$collection['business_name'],
			'instructor_mobile'		=> 	$collection['instructor_mobile'],
			'address'				=> 	$collection['address'],
			'duration'				=> 	$collection['duration'],
			'shift_date'			=>  date('M d, Y', $collection['shift_date']),
			'amount'				=>	$collection['amount'],
			'grand'					=>	$collection['service_total_price'],
			'location_url'			=>	$collection['location_url'],
			'location_name'			=>	$collection['location_name'],
			'map_url'				=>	$collection['map_url'],
			'customer_address'		=>	$collection['customer_address'],
			'customer_name'			=>	$collection['customer_fname'] . ' ' . $collection['customer_lname'],
			'payment_status' 		=>  $payment_status
		);

		return $data;
	}
}


if (!function_exists('get_video_data')) {
	function get_video_data()
	{
		$CI = get_instance();
		$CI->load->model('dynamic_model');
		$findresult = $CI->dynamic_model->get_query_result('select * from video_content where status="Active" ');
		//var_dump($findresult); die;
		$data = array();
		$data_array = array();
		if (count($findresult) > 0) {
			foreach ($findresult as $key => $value) {
				$data['id']          = $value->id;
				$data['title']       = $value->title;
				$data['description'] = $value->description;
				$data['video_url']   = base_url() . 'uploads/video/' . $value->url;

				$data_array[] = $data;
			}
		}

		return $data_array;
	}
}



if (!function_exists('get_newsfeed_data')) {
	function get_newsfeed_data($id = '')
	{
		$CI = get_instance();
		$CI->load->model('dynamic_model');
		$Userid = $CI->input->get_request_header('Userid');

		$query = "SELECT nc.id, nc.title, nc.url, nc.description, nc.create_dt, (SELECT count(*) FROM newsfeed_favourite as nf WHERE nf.newsfeed_id = nc.id AND nf.is_like = 1) as total_like_count, (SELECT count(*) FROM newsfeed_favourite as nf WHERE nf.newsfeed_id = nc.id AND nf.is_like = 0) as total_unlike_count, (SELECT COUNT(*) FROM newsfeed_comments as comment WHERE comment.newsfeed_id = nc.id) as total_comment_count FROM newsfeed_content as nc WHERE nc.status = 'Active' ORDER BY nc.create_dt DESC";

		// $query = 'select * from newsfeed_content where status="Active"';
		if (!empty($id)) {
			$query .= ' AND nc.id = ' . $id;
		}

		$findresult = $CI->dynamic_model->get_query_result($query);
		$data = array();
		$data_array = array();
		if (count($findresult) > 0) {
			foreach ($findresult as $key => $value) {

				$data['id']          = $value->id;
				$data['title']       = $value->title;
				$data['description'] = $value->description;
				$data['newsfeed_img_url']   = base_url() . 'uploads/newsfeed/' . $value->url;
				$data['added_date']    = date("d M-Y h:i A", $value->create_dt);
				$data['total_like_count']  = $value->total_like_count;
				$data['total_unlike_count'] = $value->total_unlike_count;
				$data['total_comment_count'] = $value->total_comment_count;
				$like = strval($CI->db->get_where('newsfeed_favourite', array(
					'user_id' 		=> 	$Userid,
					'newsfeed_id' 	=>	$value->id,
					'is_like'		=>	1
				))->num_rows());
				$data['is_like']       = $like; // strval($is_like);
				//$data['comments_data'] = $comments_data;

				$data_array[] = $data;
			}
		}

		if (!empty($data_array) && !empty($id)) {
			return $data_array[0];
		} else {
			return $data_array;
		}
	}
}

if (!function_exists('get_workout_category')) {
	function get_workout_category()
	{
		$CI = get_instance();
		$CI->load->model('dynamic_model');
		$Userid = $CI->input->get_request_header('Userid');

		$collection = $CI->dynamic_model->getdatafromtable('manage_workout_category', array('status' => 'Active'), 'id, name, image_url, icon_class');

		if ($collection) {
			array_walk($collection, function (&$key) {
				$key['image_url'] = site_url().'uploads/workout/'.$key['image_url'];
			});
		}
		return $collection;
	}

}

if (!function_exists('cl_get_video_master')) {
	function cl_get_video_master($page = '', $category = '', $subcategory = '')
	{
		$CI = get_instance();
		$CI->load->model('dynamic_model');
		$Userid = $CI->input->get_request_header('Userid');

		$page_no = $page - 1;
		$limit = config_item('page_data_limit');
		$offset = $limit * $page_no;

		$on = 'manage_category.id = manage_videos.category_id';
		$on2 = 'sub.id = manage_videos.sub_category_id';
		$condition = " manage_videos.status = 'Active' ";
		
		if (!empty($category)) {
			$condition .= " AND manage_videos.category_id = ".$category;
		}

		if (!empty($subcategory)) {
			$condition .= " AND manage_videos.sub_category_id = ".$subcategory;
		}

		$collection = $CI->dynamic_model->getThreeTableData('manage_videos.id, manage_videos.name, manage_videos.description, manage_videos.category_id,manage_videos.sub_category_id,manage_videos.url,manage_videos.thumbnail, manage_videos.duration, manage_videos.duration, manage_category.category_name, sub.category_name as sub_category', 'manage_videos', 'manage_category', 'manage_category as sub', $on, $on2, $condition, $limit, $offset, "manage_videos.create_dt", "DESC");
		return $collection;
	}
}

if (!function_exists('get_business_video')) {
	function get_business_video($page = '', $business_id = '', $category = '', $subcategory = '')
	{
		$CI = get_instance();
		$CI->load->model('dynamic_model');
		$Userid = $CI->input->get_request_header('Userid');

		$page_no = $page - 1;
		$limit = config_item('page_data_limit');
		$offset = $limit * $page_no;

		$on = 'manage_category.id = manage_videos.category_id';
		$on2 = 'sub.id = manage_videos.sub_category_id';
		$condition = " manage_videos.status = 'Active' ";
		if (!empty($business_id)) {
			$condition .= " AND manage_videos.business_id = ".$business_id;
		}

		if (!empty($category)) {
			$condition .= " AND manage_videos.category_id = ".$category;
		}

		if (!empty($subcategory)) {
			$condition .= " AND manage_videos.sub_category_id = ".$subcategory;
		}

		$collection = $CI->dynamic_model->getThreeTableData('manage_videos.id, manage_videos.name, manage_videos.description, manage_videos.category_id,manage_videos.sub_category_id,manage_videos.url,manage_videos.thumbnail, manage_videos.duration, manage_videos.duration, manage_category.category_name, sub.category_name as sub_category', 'manage_videos', 'manage_category', 'manage_category as sub', $on, $on2, $condition, $limit, $offset, "manage_videos.create_dt", "DESC");
		return $collection;
	}
}

if (!function_exists('get_studio_business_category')) {
	function get_studio_business_category($business_id)
	{
		$CI = get_instance();
		$CI->load->model('dynamic_model');

		$on = 'manage_category.id = business_category.category';
		$condition = " business_category.parent_id = 0 AND business_category.business_id = ".$business_id;
		$collection = $CI->dynamic_model->getTwoTableData('manage_category.id, manage_category.category_name', 'business_category', 'manage_category', $on, $condition);

		$category = array();
		if ($collection) {

			foreach($collection as $col) {

				$category_id = $col['id'];
				$category_name = $col['category_name'];

				$on = 'manage_category.id = business_category.category';
				$condition = " business_category.parent_id = ".$category_id." AND business_category.business_id = ".$business_id;
				$subcollection = $CI->dynamic_model->getTwoTableData('manage_category.id, manage_category.category_name as name', 'business_category', 'manage_category', $on, $condition);

				array_push($category, array(
					'id'	=>	$category_id,
					'name'	=>	$category_name,
					'sub_category' => $subcollection
				));
			}
		}

		return $category;
	}
}
