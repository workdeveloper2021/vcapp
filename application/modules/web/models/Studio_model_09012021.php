<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Studio_model extends CI_Model {
	public function __construct(){
		parent::__construct();
		//$this->load->model('dynamic_model');
	}
	public function get_country()
	{
		$this->db->select('*');
		$this->db->from('manage_countries');
		$this->db->order_by('id',"ASC");
		$query = $this->db->get();
		return $query->result_array();
	}
	public function plan_check($plan_id='',$user_id='')
	{
		$this->db->select('*');
		$this->db->from('subscription');
		$this->db->where('sub_plan_id',$plan_id);
		$this->db->where('sub_user_id',$user_id);
		$this->db->where('plan_status','Active');
		$query = $this->db->get();
		return $query->row_array();
	}
	public function get_instructor_ids($business_id='')
	{

		$response=array();
		$where=array("business_id"=>$business_id,"status"=>"Approve");
		$business_trainer = $this->dynamic_model->getdatafromtable("business_trainer_relationship",$where,'user_id');
		if(!empty($business_trainer)){
		$instuctor_ids = array_column($business_trainer,'user_id');
		$response=$instuctor_ids;
       }
       return $response;
	}
	public function get_all_instructors($business_id='',$search_val='',$limit='',$offset='', $specificStudio = ''){
		$instuctor_ids=$this->get_instructor_ids($business_id);
		if($instuctor_ids){
			$search_info = trim($search_val);
			$like= '(name LIKE "%'.$search_val.'%" OR lastname LIKE "%'.$search_val.'%" )';

			$this->db->select('user.*,instructor_details.skill,instructor_details.total_experience, instructor_details.registration, instructor_details.appointment_fees,instructor_details.appointment_fees_type,instructor_details.shifts_instructor,instructor_details.about,instructor_details.start_date,instructor_details.sin_no,instructor_details.employee_id,instructor_details.about,');
			$this->db->from('user');
			$this->db->join('instructor_details','instructor_details.user_id = user.id');
			if (!empty($specificStudio)) {
				$this->db->where_in('user.id',$instuctor_ids);
			}
			//$this->db->where_in('user.id',$instuctor_ids);


            if(!empty($condition)){
			 $this->db->where($condition);
		    }
		    if(!empty($search_info)){
		    $this->db->where($like,NUll);
	     	}
			if($limit != ''){
				$this->db->limit($limit, $offset);
			}
			// $this->db->order_by("create_dt","DESC");
			$this->db->order_by("user.name","asc");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				  return  $query->result_array();

			} else {
				return false;
			}

		}else{
			return false;
		}
	}
	public function get_instructor_details($business_id='',$instructor_ids='',$condition='',$search_val='',$limit='',$offset=''){
		//$instuctor_ids=$this->get_instructor_ids($business_id);
		if(!empty($instuctor_ids)){
		//if(1){
			$search_info = trim($search_val);
			$like= '(name LIKE "%'.$search_val.'%" OR lastname LIKE "%'.$search_val.'%" )';

			$this->db->select('user.*,instructor_details.skill,instructor_details.total_experience,instructor_details.appointment_fees,instructor_details.appointment_fees_type,instructor_details.shifts_instructor,instructor_details.about');
			$this->db->from('user');
			$this->db->join('instructor_details','instructor_details.user_id = user.id');
			$this->db->where_in('user.id',$instuctor_ids);

            if(!empty($condition)){
			 $this->db->where($condition);
		    }
		    if(!empty($search_info)){
		    $this->db->where($like,NUll);
	     	}
			if($limit != ''){
				$this->db->limit($limit, $offset);
			}
			$this->db->order_by("create_dt","DESC");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				  return  $query->result_array();

			} else {
				return false;
			}

		}else{
			return false;
		}
	}
	public function get_all_signed_classes($business_id='',$class_id='',$limit='',$offset='',$checkedin_type='',$date=''){
		$url=base_url().'uploads/user/';
        $this->db->select("user.name,user.lastname,CONCAT('" . $url . "', user.profile_img) as profile_img,user.gender,user.date_of_birth,business_class.*,user_attendance.status as attendance_status,user_attendance.user_id,user_attendance.service_id");
		$this->db->from('business_class');
		$this->db->join('user_attendance','user_attendance.service_id = business_class.id');
		$this->db->join('user','user.id = user_attendance.user_id');
        $where="business_class.business_id='".$business_id."' AND business_class.id='".$class_id."' AND user_attendance.service_type='1' AND user_attendance.signup_status='1' AND  business_class.status='Active'";
		$this->db->where($where);
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
		$this->db->order_by("create_dt","DESC");
		$query = $this->db->get();
		//echo $this->db->last_query();die;
		if($query->num_rows() > 0) {
			return  $query->result_array();
		}else{
			return false;
		}
	}
	public function get_all_signed_workshops($business_id='',$workshop_id='',$limit='',$offset='',$checkedin_type='',$date=''){
		$this->db->select('user.name,user.lastname,user.profile_img,user.gender,user.date_of_birth,business_workshop.*,user_attendance.status as attendance_status,user_attendance.user_id,user_attendance.service_id');
		$this->db->from('business_workshop');
		$this->db->join('user_attendance','user_attendance.service_id = business_workshop.id');
		$this->db->join('user','user.id = user_attendance.user_id');

        $where="business_workshop.business_id='".$business_id."' AND business_workshop.id='".$workshop_id."' AND user_attendance.service_type='2' AND user_attendance.signup_status='1' AND  business_workshop.status='Active'";
		$this->db->where($where);
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

	public function get_instructor_data($user_id='')
	{
		$this->db->select('*');
		$this->db->from('user');
		$this->db->where_in('id',$user_id);
		$this->db->where('status','Active');
		$this->db->group_by('id');
		$query = $this->db->get();
		//echo $this->db->last_query();die;
		return $query->result_array();
	}

	public function get_instructor_class_data($instuctor_id, $business_id,$search_date)
	{
		$this->db->select('business_class.*,class_scheduling_time.from_time,class_scheduling_time.to_time,class_scheduling_time.instructor_id');
		$this->db->from('class_scheduling_time');
		$this->db->join('business_class','business_class.id=class_scheduling_time.class_id');
		if(isset($search_date) && !empty($search_date)){
			$this->db->where('class_scheduling_time.scheduled_date',$search_date);
    	}
    	$this->db->where('class_scheduling_time.instructor_id',$instuctor_id);
    	$this->db->where('business_class.business_id',$business_id);
		//$this->db->where('class_scheduling_time.day_id',$day_id);
		$this->db->where('business_class.status','Active');
		//$this->db->group_by('class_scheduling_time.instructor_id');
		$query = $this->db->get();
		echo $this->db->last_query();die;
		return $query->result_array();
	}

	public function user_payment_requests($business_id){
		$url=base_url().'uploads/user/';
		$this->db->select("user_payment_requests.*,user.name,user.lastname,CONCAT('". $url ."', user.profile_img) as profile_img");
		$this->db->from('user_payment_requests');
		$this->db->join('user','user_payment_requests.user_id=user.id');
		$this->db->where('user_payment_requests.business_id',$business_id);
		$query = $this->db->get();

		return $query->result_array();
	}

	public function user_payment_requests_details($business_id,$customer_id,$request_id){
		$url=base_url().'uploads/user/';
		$this->db->select("user_payment_requests.*,user.name,user.lastname,CONCAT('". $url ."', user.profile_img) as profile_img,user.gender,user.date_of_birth, user_booking.transaction_id ub_transaction_id, user_booking.payment_mode as ub_payment_mode, user_booking.payment_note as ub_payment_note");
		$this->db->from('user_payment_requests');
		// if($service_type==1){
		// 	$this->db->join('user','user_payment_requests.user_id=user.id');
		// }elseif($service_type==2){
		// 	$this->db->join('user','user_payment_requests.user_id=user.id');
		// }elseif($service_type==3){
		// 	$this->db->join('user','user_payment_requests.user_id=user.id');
		// }

		$this->db->join('user','user_payment_requests.user_id=user.id');
		$this->db->join('user_booking','user_payment_requests.reference_payment_id = user_booking.reference_payment_id', 'left');
		$this->db->where('user_payment_requests.business_id',$business_id);
		$this->db->where('user_payment_requests.user_id',$customer_id);
		$this->db->where('user_payment_requests.id',$request_id);

		$query = $this->db->get();

		return $query->result_array();
	}

	public function getpassesDetails($business_id,$service_id){
		$this->db->select("business_passes.pass_name,business_passes.purchase_date,business_passes.pass_end_date,business_passes.is_one_time_purchase,business_passes.description,business_passes.pass_id,business_passes.amount,business_passes.tax1_rate,business_passes.tax2_rate,manage_pass_type.pass_type");
		$this->db->from('business_passes');
		$this->db->join('manage_pass_type','manage_pass_type.id=business_passes.pass_type');
		$this->db->where('business_passes.business_id',$business_id);
		$this->db->where('business_passes.id',$service_id);
		$this->db->where('manage_pass_type.status','Active');
		$query = $this->db->get();
		return $query->result_array();
	}

	public function getserviceDetails($business_id,$service_id){
		$this->db->select("service.service_name,manage_category.category_name,service_scheduling_time.from_time,service_scheduling_time.to_time,user.name,user.lastname");
		$this->db->from('service');
		$this->db->join('manage_category','manage_category.id=service.service_type');
		$this->db->join('user_payment_requests','user_payment_requests.service_id=service.id');
		$this->db->join('service_scheduling_time_slot','service_scheduling_time_slot.id=user_payment_requests.slot_time_id');
		$this->db->join('service_scheduling_time','service_scheduling_time.id=service_scheduling_time_slot.service_scheduling_time_id');
		$this->db->join('user','user.id=user_payment_requests.instructor_id');
		$this->db->where('service.business_id',$business_id);
		$this->db->where('service.id',$service_id);
		$query = $this->db->get();
		return $query->result_array();
	}

	public function getproductDetails($service_id,$business_id){
		$url=base_url().'uploads/products/';
		$this->db->select("business_product.*,CONCAT('". $url ."', business_product_images.image_name) as product_image");
		$this->db->from('business_product');
		$this->db->join('business_product_images','business_product_images.product_id=business_product.id');
		$this->db->where('business_product.business_id',$business_id);
		$this->db->where('business_product.id',$service_id);
		$query = $this->db->get();
		//echo $this->db->last_query();die;
		return $query->result_array();
	}

	public function getServiceEndDate($table,$service_id,$business_id){
		$this->db->select("scheduled_date");
		$this->db->from($table);
		$this->db->where('business_id',$business_id);
		$this->db->where('class_id',$service_id);
		$this->db->limit('1');
		$this->db->order_by('id',"DESC");
		$query = $this->db->get();
		//echo $this->db->last_query();die;
		return $query->result_array();
	}

	public function get_class_room_list($business_id=''){
		$this->db->select("class_scheduling_time.*,business_location.location_name");
		$this->db->from('class_scheduling_time');
		$this->db->join('business_location','business_location.id=class_scheduling_time.location_id','LEFT');
		$this->db->where('class_scheduling_time.business_id',$business_id);
		$this->db->group_by('class_scheduling_time.location_id');
		$query = $this->db->get();
		//echo $this->db->last_query();die;
		return $query->result_array();
	}
	public function get_workshop_room_list($business_id=''){
		$this->db->select("workshop_scheduling_time.*,business_location.location_name");
		$this->db->from('workshop_scheduling_time');
		$this->db->join('business_location','business_location.id=workshop_scheduling_time.location_id');
		$this->db->where('workshop_scheduling_time.business_id',$business_id);
		$this->db->group_by('workshop_scheduling_time.location_id');
		$query = $this->db->get();
		//echo $this->db->last_query();die;
		return $query->result_array();
	}

	public function studio_instructor_list_details($instructor_id=''){
		$url=base_url().'uploads/user/';
		$this->db->select("user.*,CONCAT('". $url ."', user.profile_img) as profile_image");
		$this->db->from('user');
		$this->db->where('id',$instructor_id);
		$query = $this->db->get();
		//echo $this->db->last_query();die;
		return $query->result_array();
	}

	public function get_scheduled_class_list($business_id='',$scheduled_type='',$from_date='',$to_date='',$limit='',$offset=''){

		$url=base_url().'uploads/user/';

		$this->db->select("business_class.id,business_class.class_type,business_class.class_name,business_class.duration,business_class.capacity,business_class.start_date,business_class.end_date,business_class.create_dt,business_class.status,business_class.class_repeat_times,business_class.class_days_prior_signup,business_class.class_waitlist_overflow, class_scheduling_time.id as schedule_id, class_scheduling_time.status as scheduling_status ,class_scheduling_time.from_time,class_scheduling_time.to_time,class_scheduling_time.location_id,class_scheduling_time.day_id,class_scheduling_time.instructor_id,class_scheduling_time.scheduled_date,class_scheduling_time.class_id,business_location.location_name, business_location.capacity as location_capacity, business_location.location_url,user.name,user.lastname,CONCAT('". $url ."', user.profile_img) as profile_image");
		$this->db->from('class_scheduling_time');
		$this->db->join('business_class','business_class.id=class_scheduling_time.class_id','LEFT');
		$this->db->join('user','user.id=class_scheduling_time.instructor_id','LEFT');
		$this->db->join('business_location','business_location.id=class_scheduling_time.location_id','LEFT');


		$this->db->where('class_scheduling_time.business_id',$business_id);
		$this->db->order_by('class_scheduling_time.scheduled_date','ASC');
		$this->db->order_by('class_scheduling_time.from_time','ASC');

		if($scheduled_type==0){
			$date= date('Y-m-d');
			$this->db->where('class_scheduling_time.scheduled_date',$date);

		}
		if($scheduled_type==1){
			$from_date= date('Y-m-d',$from_date);
			//$to_date= date('Y-m-d',$to_date);
			//$this->db->where("class_scheduling_time.scheduled_date BETWEEN '".$from_date."' AND '".$to_date."'");
			$this->db->where('class_scheduling_time.scheduled_date',$from_date);
			//$this->db->order_by('class_scheduling_time.scheduled_date','ASC');
			//$this->db->order_by('class_scheduling_time.from_time','ASC');

		}
		if($limit != ''){
			$this->db->limit($limit, $offset);
		}
		$query = $this->db->get();

		return $query->result_array();
	}

	public function get_scheduled_class_detail($business_id='',$class_id='',$scheduled_date='', $scheduled_id=''){

		$url=base_url().'uploads/user/';
		$this->db->select("business_class.*,class_scheduling_time.from_time,class_scheduling_time.to_time,class_scheduling_time.location_id,class_scheduling_time.day_id,class_scheduling_time.instructor_id,class_scheduling_time.scheduled_date,class_scheduling_time.class_id,business_location.location_name, business_location.location_url, business_location.capacity,user.name,user.lastname,CONCAT('". $url ."', user.profile_img) as profile_image,manage_week_days.week_name");
		$this->db->from('class_scheduling_time');
		$this->db->join('business_class','business_class.id=class_scheduling_time.class_id','LEFT');
		$this->db->join('manage_week_days','manage_week_days.id=class_scheduling_time.day_id','LEFT');
		$this->db->join('user','user.id=class_scheduling_time.instructor_id','LEFT');
		$this->db->join('business_location','business_location.id=class_scheduling_time.location_id','LEFT');
		$this->db->where('class_scheduling_time.business_id',$business_id);
		$this->db->where('class_scheduling_time.class_id',$class_id);
		$this->db->where('class_scheduling_time.scheduled_date',$scheduled_date);
		if (!empty($scheduled_id)) {
			$this->db->where('class_scheduling_time.id',$scheduled_id);
		}

		$query = $this->db->get();
		return $query->result_array();
	}

	public function get_class_attendence_count($business_id='',$class_id='',$date='', $scheduled_id = ''){
		$this->db->select("*");
		$this->db->from('user_attendance');
		$this->db->where('service_id',$class_id);
		$where = '(status="singup" or status = "checkin")';
		if (!empty($scheduled_id)) {
			$where = '(status="singup" or status = "checkin") AND schedule_id ='.$scheduled_id;
		}
		$this->db->where($where);
		$this->db->where('checkin_dt',$date);
		$query = $this->db->get();
		//echo $this->db->last_query();
		return $query->result_array();
	}

	public function get_booked_customer($class_id='',$date='', $scheduled_id = ''){
		$url=base_url().'uploads/user/';
		$this->db->select("user_attendance.user_id,user_attendance.service_type,user_attendance.service_id,user_attendance.status,user_attendance.checkin_dt,user.name,user.lastname, user.created_by, user_attendance.checked_by, CONCAT('". $url ."', user.profile_img) as profile_image");
		$this->db->from('user_attendance');
		$this->db->join('user','user.id=user_attendance.user_id');
		$this->db->where('user_attendance.service_id',$class_id);
		if (!empty($scheduled_id)) {
			$this->db->where('user_attendance.schedule_id',$scheduled_id);
		}

		// $this->db->where('user_attendance.status','singup');
		$this->db->where('user_attendance.checkin_dt',$date);
		$query = $this->db->get();
		return $query->result_array();
	}

	public function get_checkedin_customer($class_id='',$date=''){
		$url=base_url().'uploads/user/';
		$this->db->select("user_attendance.status,user_attendance.checkin_dt,user.name,user.lastname,CONCAT('". $url ."', user.profile_img) as profile_image");
		$this->db->from('user_attendance');
		$this->db->join('user','user.id=user_attendance.user_id');
		$this->db->where('user_attendance.service_id',$class_id);
		$this->db->where('user_attendance.status','checkin');
		$this->db->where('user_attendance.checkin_dt',$date);
		$query = $this->db->get();
		return $query->result_array();
	}

	public function get_waiting_customer($class_id='',$date=''){
		$url=base_url().'uploads/user/';
		$this->db->select("user_attendance.status,user_attendance.checkin_dt,user.name,user.lastname,CONCAT('". $url ."', user.profile_img) as profile_image");
		$this->db->from('user_attendance');
		$this->db->join('user','user.id=user_attendance.user_id');
		$this->db->where('user_attendance.service_id',$class_id);
		$this->db->where('user_attendance.status','waiting');
		$this->db->where('user_attendance.checkin_dt',$date);
		$query = $this->db->get();
		return $query->result_array();
	}

	public function get_instructor_skills($instructor_id=''){

		$this->db->select("instructor_details.skill");
		$this->db->from('instructor_details');
		$this->db->where('instructor_details.user_id',$instructor_id);
		$query = $this->db->get();
		return $query->result_array();
	}

	public function get_rows($table, $where) {
		return $this->db->get_where($table, $where)->num_rows();
	}

	public function get_cart_business($user_id='',$limit='',$offset='', $business_id = ''){

		$this->db->select('*');
		$this->db->from('user_booking');
		$this->db->where('user_id',$user_id);
		$this->db->where('status',"Pending");
		if (!empty($business_id)) {
			$this->db->where('business_id', $business_id);
		}
		$this->db->group_by('business_id');
		$this->db->order_by("create_dt","DESC");
		if($limit != ''){
			$this->db->limit($limit, $offset);
		}
		$query = $this->db->get();
		//echo $this->db->last_query();die;
		if ($query->num_rows() > 0) {
			return  $query->result_array();
		}else{
			return false;
		}
	}
}
