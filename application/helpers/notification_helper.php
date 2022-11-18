<?php defined('BASEPATH') OR exit('No direct script access allowed');

$this->lang->load("message","english");

if(! function_exists('sendNotification')) {

    function sendNotification($type, $sendInfo) {
        
        $message    = $sendInfo['message'];
        $token      = $sendInfo['token'];
        $title      = $sendInfo['title'];

        $notification_setting = $sendInfo['notification_setting'];

        $CI = &get_instance();

        if ($type == 1) {
            $apiKey = $CI->config->item('android_server_key');
        } else {
            $apiKey = $CI->config->item('ios_server_key');
        }

        $icon = 'https://static.pexels.com/photos/4825/red-love-romantic-flowers.jpg';

        $msg = array(
            'body' => $message,	
            'notification_setting' => $notification_setting,	
            'icon' => 'icon',	
            'sound' => 'default',	
            'click_action' => "FCM_PLUGIN_ACTIVITY",	
        );

        $fields = array(
            'to' => $token,
            'notification' => $msg,
            'data' => $msg,
            'content_available' => true,
            'priority' => 'high',
        );

        $headers = array(
            'Authorization: key=' . $apiKey,
            'Content-Type: application/json',
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
        return $response;

    }

}

if(! function_exists('helpTestNotification')) {

    function helpTestNotification($collection) {

        $token = 'edc6mieXPMo:APA91bGQqZ_q8bdbhxiAGaQQ1TrtNRH0ham30ySQ6cKVqPPPzPdkm35kfavvxQwcifVDiLwrsTcFeX7BbTzXP-iChHonftqoDpmcLwS_7URim0f5q2pO3a-9qfKK1WqpcwTNC1V4f1Gp';

        $notification_setting = '';

        $send_data = array('title' => 'Test', 'message' => 'test data', 'token' => $token, 'notification_setting' => $notification_setting);

        return sendNotification(1, $collection);

    }

}

if(! function_exists('cancelClassSceduling')) {

    function cancelClassSceduling($schedule_id) {
        $time_zone =  'UTC';
        date_default_timezone_set($time_zone);
        $CI = &get_instance();
        $CI->load->model('dynamic_model');
        
        $notification_setting = '';
        $notificationArray = array();
        $getSchedule = $CI->dynamic_model->getdatafromtable('class_scheduling_time', array('id' => $schedule_id));
        if (!empty($getSchedule)) {
            $business_id = $getSchedule[0]['business_id'];
            $class_id = $getSchedule[0]['class_id'];
            $schedule_id = $getSchedule[0]['id'];
            $select_dt = strtotime($getSchedule[0]['scheduled_date']);

            $condition = " (user_attendance.status = 'singup' OR user_attendance.status = 'checkin' OR user_attendance.status = 'waiting') AND user_attendance.schedule_id='" . $schedule_id . "' AND user_attendance.service_id='" . $class_id . "' AND user_booking.service_type='1'";
            $on = 'user_attendance.user_id = user.id';
            $data = "user.id, user.device_token";
            $userInfo = $CI->dynamic_model->getTwoTableData($data, 'user_attendance', 'user', $on, $condition);
            if (!empty($userInfo)) {

                foreach($userInfo as $usr) {
                    $device_token = $usr['device_token'];
                    if (!empty($device_token)) {
                     
                        $send_data = array(
                            'title' => TITLE_CLASS_SCHEDULING, 
                            'message' => MESSAGES_CANCEL_CLASS, 
                            'token' => $device_token, 
                            'notification_setting' => $notification_setting
                        );
                        helpTestNotification($send_data);

                    }

                    array_push($notificationArray, array(
                        'sender_id'		=>	0,
                        'recepient_id'	=>	$usr['id'],
                        'title'			=>	TITLE_CLASS_SCHEDULING,
                        'message'		=>	MESSAGES_CANCEL_CLASS,
                        'is_read'		=>	0,
                        'types'			=>	1,
                        'is_deleted'	=>	0,
                        'content_object'=>	json_encode(array('business_id' => $business_id, 'class_id' => $class_id, 'schedule_id' => $schedule_id, 'select_dt' => $select_dt)),
                        'content_category' => 1
                    ));

                }
                if (!empty($notificationArray)) {
                    $CI->db->insert_batch('notification', $notificationArray);
                }
            }
        }
    }
}

if(! function_exists('waitListingClass')) {

    function waitListingClass($business_id, $class_id, $schedule_id, $user_id, $schedule_date) {

        $time_zone =  'UTC';
        date_default_timezone_set($time_zone);
        $CI = &get_instance();
        $CI->load->model('dynamic_model');
        
        $notification_setting = '';
        $notificationArray = array();
        $userInfo = $CI->dynamic_model->getdatafromtable('user', array('id' => $user_id));

        $device_token = $userInfo[0]['device_token'];
        if (!empty($device_token)) {
            
            $send_data = array(
                'title' => TITLE_CLASS_SCHEDULING, 
                'message' => MESSAGES_WAIT_LIST_CHANGE_CLASS, 
                'token' => $device_token, 
                'notification_setting' => $notification_setting
            );
            helpTestNotification($send_data);

        }

        array_push($notificationArray, array(
            'sender_id'		=>	0,
            'recepient_id'	=>	$user_id,
            'title'			=>	TITLE_CLASS_SCHEDULING,
            'message'		=>	MESSAGES_WAIT_LIST_CHANGE_CLASS,
            'is_read'		=>	0,
            'types'			=>	1,
            'is_deleted'	=>	0,
            'content_object'=>	json_encode(array('business_id' => $business_id, 'class_id' => $class_id, 'schedule_id' => $schedule_id, 'select_dt' => strtotime($schedule_date))),
            'content_category' => 1
        ));

        if (!empty($notificationArray)) {
			$CI->db->insert_batch('notification', $notificationArray);
		}
    }
}

if(! function_exists('cancelUserAppointment')) {

    function cancelUserAppointment($user_id, $business_id, $service_id, $schedule_id) {

        $time_zone =  'UTC';
        date_default_timezone_set($time_zone);
        $CI = &get_instance();

        $CI->load->model('dynamic_model');

        $userInfo = $CI->dynamic_model->getdatafromtable('user', array('id' => $user_id));
        
        $notification_setting = '';
        $notificationArray = array();
        $time = time();
        $collection = $userInfo[0];

        if (!empty($collection['device_token'])) {
            $send_data = array(
                'title' => TITLE_SERVICE_APPOINTMENT, 
                'message' => APPOINTMENT_CANCEL_SERVICE, 
                'token' => $collection['device_token'], 
                'notification_setting' => $notification_setting
            );
            helpTestNotification($send_data);
        }
        array_push($notificationArray, array(
            'sender_id'		=>	0,
            'recepient_id'	=>	$user_id,
            'title'			=>	TITLE_SERVICE_APPOINTMENT,
            'message'		=>	APPOINTMENT_CANCEL_SERVICE,
            'is_read'		=>	0,
            'types'			=>	1,
            'is_deleted'	=>	0,
            'content_object'=>	json_encode(array('business_id' => $business_id, 'service_id' => $service_id, 'schedule_id'   => $schedule_id)),
            'content_category' => 2,
            'create_dt'			=> $time
        ));

        if (!empty($notificationArray)) {
			$CI->db->insert_batch('notification', $notificationArray);
		}
    }
}

if(! function_exists('cancelAppointment')) {

    function cancelAppointment($service_id, $schedule_id) {

        $time_zone =  'UTC';
        date_default_timezone_set($time_zone);
        $CI = &get_instance();

        $CI->load->model('dynamic_model');

        $condition = " user_booking.status = 'Confirm' AND user_booking.service_id = ".$service_id." AND user_booking.shift_schedule_id = ".$schedule_id." AND user_booking.service_type='2'";
        $on = 'user_booking.user_id = user.id';
        $data = "user.id, user.device_token, user_booking.business_id";
        $userInfo = $CI->dynamic_model->getTwoTableData($data, 'user_booking', 'user', $on, $condition);

        $notification_setting = '';
        $notificationArray = array();
        $time = time();

        if (!empty($userInfo)) {
            foreach ($userInfo as $usr) {
                $device_token = $usr['device_token'];
                $user_id = $usr['id'];
                $business_id = $usr['business_id'];

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
					'recepient_id'	=>	$user_id,
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
			$CI->db->insert_batch('notification', $notificationArray);
		}
  
    }
}
