<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Instructor_model extends CI_Model {
	public function __construct(){
		parent::__construct();
	}
	public function get_my_studios($user_id='',$limit='',$offset='',$latitude='',$longitude=''){
			$this->db->select('business.*');

			if (!empty($latitude) && !empty($longitude)) {
			$this->db->select('( 3959 * acos( cos( radians("' . $latitude . '") ) * cos( radians(lat) ) * cos( radians(longitude) - radians("' . $longitude . '") ) + sin( radians("' . $latitude . '") ) * sin( radians(lat)))) AS distance', false);
			}else{
				$this->db->select("'0' as distance");
			}

			$this->db->from('business');
			$this->db->join('business_trainer_relationship','business_trainer_relationship.business_id = business.id');
			$this->db->where('business_trainer_relationship.user_id',$user_id);
			$this->db->where('business_trainer_relationship.status','Approve');
			if($limit != ''){
				$this->db->limit($limit, $offset);
			}
			$this->db->order_by("create_dt","DESC");
			$query = $this->db->get();
			//echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				return  $query->result_array(); 
			}else{				
				return false;
			}	
	}
	public function get_my_classes($business_id='',$upcoming_date='',$limit='',$offset='',$status='',$user_id=''){

		$todaydate = date("Y-m-d");
		$this->db->select('*');
		$this->db->from('business_class');
		if(!empty($upcoming_date)){
			$date = date("Y-m-d",$upcoming_date);
            $where="business_id=".$business_id." AND instructor_id=".$user_id." AND status='Active' AND DATE(FROM_UNIXTIME(start_date))='".$date."'";
		}else{
           $where="business_id=".$business_id." AND instructor_id=".$user_id." AND DATE(FROM_UNIXTIME(end_date))>='".$todaydate."' AND status='Active'"; 
		}
		$this->db->where($where);
		if($limit != ''){
			$this->db->limit($limit, $offset);
		}
		$this->db->order_by("create_dt","DESC");
		$query = $this->db->get();
		//echo $this->db->last_query();die;
		if($query->num_rows() > 0) {
			return  $query->result_array(); 
		}else{				
			return false;
		}	
	}
	public function get_my_workshops($business_id='',$upcoming_date='',$limit='',$offset='',$status='',$user_id=''){

		$todaydate = date("Y-m-d");
		$this->db->select('*');
		$this->db->from('business_workshop');
		if(!empty($upcoming_date)){
			$date = date("Y-m-d",$upcoming_date);
            $where="business_id=".$business_id." AND instructor_id=".$user_id." AND status='Active' AND DATE(FROM_UNIXTIME(start_date))='".$date."'";
		}else{
           $where="business_id=".$business_id." AND instructor_id=".$user_id." AND DATE(FROM_UNIXTIME(end_date))>='".$todaydate."' AND status='Active'"; 
		}
		$this->db->where($where);
		if($limit != ''){
			$this->db->limit($limit, $offset);
		}
		$this->db->order_by("create_dt","DESC");
		$query = $this->db->get();
		//echo $this->db->last_query();die;
		if($query->num_rows() > 0) {
			return  $query->result_array(); 
		}else{				
			return false;
		}	
	}
	public function get_all_signed_classes($business_id='',$class_id='',$date='',$customer_type='',$checkedin_type='',$usid='',$limit='',$offset='', $schedule_id = '', $instructor = ''){

		$date = date("Y-m-d",$date);
		
		$this->db->select('user.id as userid,user.name as username,user.lastname,user.gender,user.date_of_birth,business_class.*,user_attendance.pass_id as user_pass_id, user_attendance.status as attendance_status,user_attendance.user_id,user_attendance.id, user_attendance.class_end_status');

		$path = base_url().'uploads/user/';
		$this->db->select("CONCAT('".$path."', user.profile_img) as profile_img");

		$this->db->from('business_class');
		$this->db->join('user_attendance','user_attendance.service_id = business_class.id');
		$this->db->join('user','user.id = user_attendance.user_id');
        $where="business_class.business_id='".$business_id."' AND business_class.id='".$class_id."' AND user_attendance.signup_status='1' AND  business_class.status='Active' AND  business_class.instructor_id != '".$usid."'";

       // echo $usid; die;
		$this->db->where($where);
		if($customer_type){
		  $this->db->where('user.gender',$customer_type);
		}
		if (!empty($schedule_id)) {
			$this->db->where('user_attendance.schedule_id',$schedule_id);
		}
		if($checkedin_type=='1') {
			if (!empty($instructor)) {
				$wh = "user_attendance.status='checkin' AND user_attendance.checkin_dt='".$date."'";
			} else {
				$wh = "(user_attendance.status='checkin' || user_attendance.status='singup') AND user_attendance.checkin_dt='".$date."'";
			}
		 
	     $this->db->where($wh);
		}elseif($checkedin_type=='2'){
			$wh1="user_attendance.status !='checkout' AND user_attendance.status !='checkin' ";
			$this->db->where($wh1);
		}
		$this->db->group_by('user_attendance.user_id');
		if($limit != ''){
			$this->db->limit($limit, $offset);
		}
		$this->db->order_by("user_attendance.create_dt","DESC");
		
		$query = $this->db->get();
		//echo $this->db->last_query();die;
		if($query->num_rows() > 0) {
			$result = $query->result_array(); 
			foreach ($result as $key => $value) {
               $username = $value['username'];
				$covid_info = getUserQuestionnaire($value['userid'],$class_id,$business_id);
                if(!empty($covid_info)){
                    $covid_status = $covid_info['covid_status'];
                    $covid_info = $covid_info['covid_info'];
                }else{
                    $covid_info = 0;
                    $covid_status = 0;
                }

                	////Signed Up Checked In
                if($value['attendance_status'] == 'singup'){
                	$attendance_status = 'Signed Up';
                }else if($value['attendance_status'] == 'checkin'){
                	$attendance_status = 'Checked In';
                }else{
                	$attendance_status = $value['attendance_status'];
                }
				$data[] = array(
					'covid_info' => $covid_info,
                    'covid_status' => $covid_status,
                    'user_pass_id' => $value['user_pass_id'],
					'userid' => $value['userid'],
					'name' => $username,
					'lastname' => $value['lastname'],
					'gender' => $value['gender'],
					'date_of_birth' => $value['date_of_birth'],
					'id' => $value['userid'],
					'business_id' => $value['business_id'],
					'instructor_id' => $value['instructor_id'],
					'class_name' => $value['class_name'],
					'day_id' => $value['day_id'],
					'start_date' => $value['start_date'],
					'end_date' => $value['end_date'],
					'from_time' => $value['from_time'],
					'to_time' => $value['to_time'],
					'class_type' => $value['class_type'],
					'duration' => $value['duration'],
					'class_repeat_times' => $value['class_repeat_times'],
					'class_days_prior_signup' => $value['class_days_prior_signup'],
					'class_waitlist_overflow' => $value['class_waitlist_overflow'],
					'class_status' => $value['class_status'],
					'capacity' => $value['capacity'],
					'description' => $value['description'],
					'about' => $value['about'],
					'location' => $value['location'],
					'status' => $value['status'],
					'create_dt' => $value['create_dt'],
					'update_dt' => $value['update_dt'],
					'is_cancel' => $value['is_cancel'],
					'attendance_status' => $attendance_status,
					'profile_img' => $value['profile_img'],
					'class_end_status'=> $value['class_end_status']
				);
			}
			
			return $data;
		}else{				
			return false;
		}	
	}

	public function get_all_signed_classes_by_schedule_date($business_id='',$class_id='',$date='',$customer_type='',$checkedin_type='',$usid='',$limit='',$offset=''){
		$this->db->select('user.name,user.lastname,user.gender,user.date_of_birth,business_class.*,user_attendance.status as attendance_status,user_attendance.user_id,user_attendance.id, user_attendance.class_end_status');

		$path = base_url().'uploads/user/';
		$this->db->select("CONCAT('".$path."', user.profile_img) as profile_img");


		$this->db->from('business_class');
		$this->db->join('user_attendance','user_attendance.service_id = business_class.id');
		$this->db->join('user','user.id = user_attendance.user_id');
        $where="business_class.business_id='".$business_id."' AND business_class.id='".$class_id."' AND user_attendance.service_type='1' AND user_attendance.signup_status='1' AND  business_class.status='Active' AND  business_class.instructor_id != '".$usid."'";
		$this->db->where($where);
		if($customer_type){
		  $this->db->where('user.gender',$customer_type);
		}
		if($checkedin_type=='1'){
		 $wh="(user_attendance.status='checkin' || user_attendance.status='singup') AND checkin_dt ='".$date."'";
	     $this->db->where($wh);
		}elseif($checkedin_type=='2'){
			 $wh1="user_attendance.status !='checkout' AND user_attendance.status !='checkin' ";
	     $this->db->where($wh1);
		}
		$this->db->group_by('user_attendance.user_id');
		if($limit != ''){
			$this->db->limit($limit, $offset);
		}
		$this->db->order_by("user_attendance.create_dt","DESC");
		
		$query = $this->db->get();
		//echo $this->db->last_query();die;
		if($query->num_rows() > 0) {
			return  $query->result_array(); 
		}else{				
			return false;
		}	
	}

	public function get_all_signed_workshops($business_id='',$workshop_id='',$date='',$customer_type='',$checkedin_type='',$usid='',$limit='',$offset=''){
		$this->db->select('user.name,user.lastname,user.profile_img,user.gender,user.date_of_birth,business_workshop.*,user_attendance.status as attendance_status,user_attendance.user_id,user_attendance.id');
		$this->db->from('business_workshop');
		$this->db->join('user_attendance','user_attendance.service_id = business_workshop.id');
		$this->db->join('user','user.id = user_attendance.user_id');
       
        $where="business_workshop.business_id='".$business_id."' AND business_workshop.id='".$workshop_id."' AND user_attendance.service_type='2' AND user_attendance.signup_status='1' AND  business_workshop.status='Active' AND  business_workshop.instructor_id != '".$usid."'";
		$this->db->where($where);
		if($customer_type){
		  $this->db->where('user.gender',$customer_type);
		}
		if($checkedin_type=='1'){
		 $wh="user_attendance.status='checkin' AND DATE(FROM_UNIXTIME(user_attendance.create_dt))='".$date."'";
	     $this->db->where($wh);
		}elseif($checkedin_type=='2'){
			 $wh1="user_attendance.status !='checkout' AND user_attendance.status !='checkin' ";
	     $this->db->where($wh1);
		}
		$this->db->group_by('user_attendance.user_id');
		if($limit != ''){
			$this->db->limit($limit, $offset);
		}
		$this->db->order_by("user_attendance.create_dt","DESC");
		$query = $this->db->get();
		//echo $this->db->last_query();die;
		if($query->num_rows() > 0) {
			return  $query->result_array(); 
		}else{				
			return false;
		}	
	}
	public function business_time_slote($business_id=''){
		$this->db->select('business_time_slote.*,manage_week_days.week_name');
		$this->db->from('business_time_slote');
		$this->db->join('manage_week_days','manage_week_days.id = business_time_slote.day_id');
		$this->db->where('business_id',$business_id);
		$this->db->group_by('business_time_slote.day_id');
		$query = $this->db->get();
		
		if($query->num_rows() > 0) {
			return  $query->result_array(); 
		}else{				
			return false;
		}	
	}





 
}

