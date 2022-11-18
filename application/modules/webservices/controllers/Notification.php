<?php defined('BASEPATH') OR exit('No direct script access allowed');
//require APPPATH . '/libraries/REST_Controller.php';

/* * ***************Studio.php**********************************
 * @product name    : Signal Health Group Inc.
 * @type            : Class
 * @class name      : Notification
 * @description     : Send notification.
 * @author          : Consagous Team
 * @url             : https://www.consagous.com/
 * @support         : aamir.shaikh@consagous.com
 * @copyright       : Consagous Team
 * ********************************************************** */
class Notification extends MX_Controller {
	private $userCollection = [];
	public function __construct() {
		header('Content-Type: application/json');
		parent::__construct();
		$this->load->model('dynamic_model');
		$this->load->helper('web_common_helper');
		$this->load->helper('notification_helper');
		$time_zone =  'UTC';
        date_default_timezone_set($time_zone);
		
		// $timezone    = $this->input->get_request_header('timeZone', true);
	    // $isValidTimezone= isValidTimezoneId($timezone);
		// if($isValidTimezone == true){
		//     date_default_timezone_set($timezone);
	    // }else{
	   	//     date_default_timezone_set('Asia/Calcutta');
	    // }
		// $language = $this->input->get_request_header('language');
		// if($language == "en")
		// {
		// 	$this->lang->load("web_message","english");
		// }
		// else
		// {
		// 	$this->lang->load("web_message","english");
		// }
	}

	public function index() {
		$userQuery = 'SELECT id, role_id, singup_for, name, lastname, mobile, email, profile_img, date_of_birth, gender, device_token, device_type FROM user WHERE notification_status = "0" AND role_id != "1" AND email_verified = 1 AND mobile_verified = 1 AND status = "Active" ';
		$resultQuery = $this->db->query($userQuery)->result_array();
		if (!empty($resultQuery)) {
			$this->userCollection = $resultQuery;
			$this->class_start();
			$this->appointment();
		}
	}

	private function setTimeZone () {
		$time_zone =  $this->input->get_request_header('current_time_zone', true);
		$time_zone =  $time_zone ? $time_zone : 'UTC';
		date_default_timezone_set($time_zone);
		return true;
	}

    // Class notification send 
    public function class_start() {

		$userInfo = $this->userCollection;
		$arr = array_map (function($value){
			return $value['id'];
		} , $userInfo);

		$user_str = implode(",",$arr);
		$time = time();
		$query = "SELECT (CURRENT_TIMESTAMP) as schedule, cst.scheduled_date, ua.user_id, u.device_token, cst.id, cst.business_id, cst.class_id, cst.location_id, cst.day_id, cst.from_time, cst.to_time, cst.instructor_id FROM user_attendance as ua JOIN class_scheduling_time as cst on (cst.id = ua.schedule_id) JOIN business_class as bc on (bc.id = cst.class_id) JOIN user as u on u.id = ua.user_id WHERE ua.service_type = 1 AND (ua.status = 'singup' OR ua.status = 'checkin') AND cst.status = 'Active' AND ua.user_id IN ($user_str)";

		$query1 = $query." AND cst.from_time = UNIX_TIMESTAMP( NOW() - INTERVAL 24 HOUR )";
		$query2 = $query." AND cst.from_time = UNIX_TIMESTAMP( NOW() - INTERVAL 2 HOUR )";
		$query3 = $query." AND cst.from_time = UNIX_TIMESTAMP( NOW() - INTERVAL 15 MINUTE )";

		$response_tweenty_four_hour = $this->db->query($query1)->result_array();
		$response_two_four_hour = $this->db->query($query2)->result_array();
		$response_tweenty_fifteen_minute = $this->db->query($query3)->result_array();
		$notificationArray = array();
		$notification_setting = '';
		if (!empty($response_tweenty_four_hour)) {
			foreach ($response_tweenty_four_hour as $res) {
				$schedule_id 	= $res['id'];
				$business_id 	= $res['business_id'];
				$class_id 		= $res['class_id'];
				$select_dt      = strtotime($res['scheduled_date']);
				$device_token 	= $res['device_token'];
				
				if (!empty($device_token)) {
					$send_data = array(
						'title' => TITLE_CLASS_SCHEDULING, 
						'message' => MESSAGES_TWEENTY_FOUR_CLASS, 
						'token' => $device_token, 
						'notification_setting' => $notification_setting
					);
					helpTestNotification($send_data);
				}
				array_push($notificationArray, array(
					'sender_id'		=>	0,
					'recepient_id'	=>	$res['user_id'],
					'title'			=>	TITLE_CLASS_SCHEDULING,
					'message'		=>	MESSAGES_TWEENTY_FOUR_CLASS,
					'is_read'		=>	0,
					'types'			=>	1,
					'is_deleted'	=>	0,
					'content_object'=>	json_encode(array('business_id' => $business_id, 'class_id' => $class_id, 'schedule_id' => $schedule_id, 'select_dt' => $select_dt)),
					'content_category' => 1,
					'create_dt'			=> $time
				));
			}
		}

		if (!empty($response_two_four_hour)) {
			foreach ($response_two_four_hour as $res) {
				$device_token 	= $res['device_token'];
				$title 			= TITLE_CLASS_SCHEDULING;
				$message 		= MESSAGES_TWO_FOUR_CLASS;
				$schedule_id 	= $res['id'];
				$business_id 	= $res['business_id'];
				$class_id 		= $res['class_id'];

				if (!empty($device_token)) {
					$send_data = array(
						'title' => $title, 
						'message' => $message, 
						'token' => $device_token, 
						'notification_setting' => $notification_setting
					);
					helpTestNotification($send_data);
				}
				array_push($notificationArray, array(
					'sender_id'		=>	0,
					'recepient_id'	=>	$res['user_id'],
					'title'			=>	$title,
					'message'		=>	$message,
					'is_read'		=>	0,
					'types'			=>	1,
					'is_deleted'	=>	0,
					'content_object'=>	json_encode(array('business_id' => $business_id, 'class_id' => $class_id, 'schedule_id' => $schedule_id, 'select_dt' => $select_dt)),
					'content_category' => 1,
					'create_dt'			=> $time
				));
			}
		}

		if (!empty($response_tweenty_fifteen_minute)) {
			foreach ($response_tweenty_fifteen_minute as $res) {
				$device_token 	= $res['device_token'];
				$title 			= TITLE_CLASS_SCHEDULING;
				$message 		= MESSAGES_FIFTEEN_MINUTE_CLASS;
				$schedule_id 	= $res['id'];
				$business_id 	= $res['business_id'];
				$class_id 		= $res['class_id'];

				if (!empty($device_token)) {
					$send_data = array(
						'title' => $title, 
						'message' => $message, 
						'token' => $device_token, 
						'notification_setting' => $notification_setting
					);
					helpTestNotification($send_data);
				}
				array_push($notificationArray, array(
					'sender_id'		=>	0,
					'recepient_id'	=>	$res['user_id'],
					'title'			=>	$title,
					'message'		=>	$message,
					'is_read'		=>	0,
					'types'			=>	1,
					'is_deleted'	=>	0,
					'content_object'=>	json_encode(array('business_id' => $business_id, 'class_id' => $class_id, 'schedule_id' => $schedule_id, 'select_dt' => $select_dt)),
					'content_category' => 1,
					'create_dt'			=> $time
				));
			}
		}

		if (!empty($notificationArray)) {
			$this->db->insert_batch('notification', $notificationArray);
		}
		return true;
    }

	public function appointment() {

		$userInfo = $this->userCollection;
		$arr = array_map (function($value){
			return $value['id'];
		} , $userInfo);

		$user_str = implode(",",$arr);
		$time = time();

		$query = "SELECT (CURRENT_TIMESTAMP) as schedule, ub.user_id, ub.business_id, ub.service_id, ub.shift_schedule_id, ub.passes_start_date as start_time, user.device_token FROM user_booking as ub join user on (user.id = ub.user_id) where ub.status = 'Confirm' AND ub.service_type = 2 AND ub.user_id IN ($user_str)";

		$query1 = $query." AND ub.passes_start_date = UNIX_TIMESTAMP( NOW() - INTERVAL 24 HOUR )";
		$query2 = $query." AND ub.passes_start_date = UNIX_TIMESTAMP( NOW() - INTERVAL 2 HOUR )";

		$response_tweenty_four_hour = $this->db->query($query1)->result_array();
		$response_two_four_hour = $this->db->query($query2)->result_array();
		$notificationArray = array();
		$notification_setting = '';
		if (!empty($response_tweenty_four_hour)) {
			foreach ($response_tweenty_four_hour as $res) {
				$schedule_id 	= $res['shift_schedule_id'];
				$business_id 	= $res['business_id'];
				$service_id 		= $res['service_id'];
				$device_token 	= $res['device_token'];
				
				if (!empty($device_token)) {
					$send_data = array(
						'title' => TITLE_SERVICE_APPOINTMENT, 
						'message' => APPOINTMENT_TWEENTY_FOUR, 
						'token' => $device_token, 
						'notification_setting' => $notification_setting
					);
					helpTestNotification($send_data);
				}
				array_push($notificationArray, array(
					'sender_id'		=>	0,
					'recepient_id'	=>	$res['user_id'],
					'title'			=>	TITLE_SERVICE_APPOINTMENT,
					'message'		=>	APPOINTMENT_TWEENTY_FOUR,
					'is_read'		=>	0,
					'types'			=>	1,
					'is_deleted'	=>	0,
					'content_object'=>	json_encode(array('business_id' => $business_id, 'service_id' => $service_id, 'schedule_id'   => $schedule_id)),
					'content_category' => 2,
					'create_dt'			=> $time
				));
			}
		}

		if (!empty($response_two_four_hour)) {
			foreach ($response_two_four_hour as $res) {
				$schedule_id 	= $res['shift_schedule_id'];
				$business_id 	= $res['business_id'];
				$service_id 		= $res['service_id'];
				$device_token 	= $res['device_token'];
				
				if (!empty($device_token)) {
					$send_data = array(
						'title' => TITLE_SERVICE_APPOINTMENT, 
						'message' => APPOINTMENT_TWEENTY_FOUR, 
						'token' => $device_token, 
						'notification_setting' => $notification_setting
					);
					helpTestNotification($send_data);
				}
				array_push($notificationArray, array(
					'sender_id'		=>	0,
					'recepient_id'	=>	$res['user_id'],
					'title'			=>	TITLE_SERVICE_APPOINTMENT,
					'message'		=>	APPOINTMENT_TWEENTY_FOUR,
					'is_read'		=>	0,
					'types'			=>	1,
					'is_deleted'	=>	0,
					'content_object'=>	json_encode(array('business_id' => $business_id, 'service_id' => $service_id, 'schedule_id'   => $schedule_id)),
					'content_category' => 2,
					'create_dt'			=> $time
				));
			}
		}

		if (!empty($notificationArray)) {
			$this->db->insert_batch('notification', $notificationArray);
		}
		return true;
    }
}


