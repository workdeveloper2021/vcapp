<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api_model extends CI_Model {
	public function __construct(){
		parent::__construct();
	}

	public function question_options($question_id)
	{
		$this->db->select('*');
		$this->db->from('manage_questionnaire_ans');
		$this->db->where_in('question_id',$question_id);
		$this->db->order_by('id',"ASC");
		$query = $this->db->get();
		$result =  $query->result_array();
		if(!empty($result)){
			$i=1;
			foreach ($result as $key) {
	            # code...
	            $id = $i;
	            $i++;
	            $data[] = array('id' => $id,
	            'question_id' => $key['question_id'],
	            'question_ans' => $key['question_ans']);
	        }
	        return $data;
    	}else{
    		return false;
    	}
	}
	public function get_country()
	{
		$this->db->select('*');
		$this->db->from('manage_countries');
		$this->db->order_by('id',"ASC");
		$query = $this->db->get();
		return $query->result_array();
	}
	public function get_instructor_info($business_id='',$search_val='',$limit = "5",$offset= "0")
	{
		$search_val=trim($search_val);
		$like= '(name LIKE "%'.$search_val.'%" OR lastname LIKE "%'.$search_val.'%" )';
		$response=array();
		$where=array("business_id"=>$business_id,"is_verified"=>"Active","status"=>"Active");
		$business_trainer = $this->dynamic_model->getdatafromtable("business_trainer_relationship",$where,'user_id');
		if(!empty($business_trainer)){
		$instuctor_ids = array_column($business_trainer,'user_id');
		$condition1=array("status"=>"Active");
		$url = site_url() . 'uploads/user/';
		$instructor_info = $this->dynamic_model->getWhereInData("user","id",$instuctor_ids,$condition1,"id,name,lastname, CONCAT('" . $url . "', profile_img) as profile_img,availability_status",$limit,$offset,"create_dt","DESC",$like);
		if(!empty($instructor_info)){
		   $response=$instructor_info ;
		  }
       }
       return $response;
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
	public function get_all_instructors($business_id='',$search_val='',$limit='',$offset=''){

		$instuctor_ids=$this->get_instructor_ids($business_id);
		if($instuctor_ids){
			$search_info = trim($search_val);
			$like= '(user.name LIKE "%'.$search_val.'%" OR user.lastname LIKE "%'.$search_val.'%" )';
			$url = site_url() . 'uploads/user/';
			$this->db->select('user.*,CONCAT("'.$url.'",user.profile_img) as profile_img,instructor_details.skill,instructor_details.total_experience,instructor_details.appointment_fees,instructor_details.appointment_fees_type,instructor_details.shifts_instructor,instructor_details.about,instructor_details.start_date,instructor_details.sin_no,instructor_details.employee_id,instructor_details.about,');
			$this->db->from('user');
			$this->db->join('instructor_details','instructor_details.user_id = user.id');
			//$this->db->join('manage_skills','FIND_IN_SET(manage_skills.id, instructor_details.skill)','LEFT');
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
			//$this->db->group_by("manage_skills.name");
			$this->db->order_by("user.create_dt","DESC");
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
		//$instuctor_ids= $this->get_instructor_ids($business_id);

		if(!empty($instructor_ids)){
			$search_info = trim($search_val);
			$like= '(name LIKE "%'.$search_val.'%" OR lastname LIKE "%'.$search_val.'%" )';

			$this->db->select('user.*,instructor_details.skill,instructor_details.total_experience,instructor_details.appointment_fees,instructor_details.appointment_fees_type,instructor_details.shifts_instructor,instructor_details.about');
			$this->db->from('user');
			$this->db->join('instructor_details','instructor_details.user_id = user.id');
			$this->db->where_in('user.id',$instructor_ids);
			$this->db->group_by('user.id');
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
			
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	        
		}else{
			return false;
		}
	}
	public function get_my_studios($user_id='',$limit='',$offset='',$latitude='',$longitude='',$created_by=''){
			$this->db->select('business.*');
			if (!empty($latitude) && !empty($longitude)) {
			$this->db->select('( 3959 * acos( cos( radians("' . $latitude . '") ) * cos( radians(lat) ) * cos( radians(longitude) - radians("' . $longitude . '") ) + sin( radians("' . $latitude . '") ) * sin( radians(lat)))) AS distance', false);
			}else{
				$this->db->select("'0' as distance");
			}
			$this->db->from('business');
			$this->db->join('user_booking','user_booking.business_id = business.id');
			$this->db->where('user_booking.user_id',$user_id);
			$this->db->group_by('user_booking.business_id');
			if($limit != ''){
				$this->db->limit($limit, $offset);
			}
			$this->db->order_by("create_dt","DESC");
			$query = $this->db->get();
			//echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				return  $query->result_array(); 
			}else{
				if (empty($created_by)) {
					return false;
				}

				/* start */
				$this->db->select('business.*');
				if (!empty($latitude) && !empty($longitude)) {
				$this->db->select('( 3959 * acos( cos( radians("' . $latitude . '") ) * cos( radians(lat) ) * cos( radians(longitude) - radians("' . $longitude . '") ) + sin( radians("' . $latitude . '") ) * sin( radians(lat)))) AS distance', false);
				}else{
					$this->db->select("'0' as distance");
				}
				$this->db->from('business');
				$this->db->where('business.user_id',$created_by);
				$query = $this->db->get();
				if ($query->num_rows() > 0) {
					return  $query->result_array(); 
				}else{
					return false;
				}	
				/* end*/			
				//return false;
			}	
	}
	public function get_signed_classes($business_id='',$upcoming_date='',$limit='',$offset='',$status='',$user_id=''){
		$this->db->select('business_class.*,user_attendance.status as attendance_status,user_attendance.user_id,user_attendance.checkin_dt');
		$this->db->from('business_class');
		$this->db->join('user_attendance','user_attendance.service_id = business_class.id');
		if(!empty($upcoming_date)){
			$date = date("Y-m-d",$upcoming_date);
            $where="business_class.business_id=".$business_id." AND user_attendance.user_id=".$user_id." AND business_class.status='Active' AND user_attendance.service_type='1' AND user_attendance.checkin_dt ='".$date."'";

            //DATE(FROM_UNIXTIME(start_date))='".$date."'
		}else{
			//if status not empty its chk wating status
		  if(empty($status)){
           $where="business_class.business_id=".$business_id." AND user_attendance.user_id=".$user_id." AND user_attendance.service_type='1' AND business_class.status='Active'";
          }else{
          	 $where="business_class.business_id=".$business_id." AND user_attendance.user_id=".$user_id." AND user_attendance.service_type='1' AND business_class.status='Active' AND user_attendance.status='waiting'";
          }
          
		}
		$this->db->where($where);
		$this->db->group_by('user_attendance.service_id');
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

	public function get_signed_workshop($business_id='',$upcoming_date='',$limit='',$offset='',$status='',$user_id=''){
		$this->db->select('business_workshop.*,user_attendance.status as attendance_status,user_attendance.user_id');
		$this->db->from('business_workshop');
		$this->db->join('user_attendance','user_attendance.service_id = business_workshop.id');
		$this->db->group_by('user_attendance.service_id');
		if(!empty($upcoming_date)){
			$date = date("Y-m-d",$upcoming_date);
            $where="business_workshop.business_id=".$business_id." AND user_attendance.user_id=".$user_id." AND business_workshop.status='Active' AND user_attendance.service_type='2'  AND DATE(FROM_UNIXTIME(start_date))='".$date."'";
		}else{
           $where="business_workshop.business_id=".$business_id." AND user_attendance.user_id=".$user_id." AND user_attendance.service_type='2' AND business_workshop.status='Active'";
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

    public function my_classes_attandance($business_id='',$limit='',$offset='',$user_id=''){
		$this->db->select('business_class.*,user_attendance.status as attendance_status,user_attendance.user_id, user_attendance.schedule_id');
		$this->db->from('business_class');
		$this->db->join('user_attendance','user_attendance.service_id = business_class.id');
        $where="business_class.business_id=".$business_id." AND user_attendance.user_id=".$user_id." AND user_attendance.service_type='1' AND business_class.status='Active' AND user_attendance.status ='checkin'";
		$this->db->where($where);
		//$this->db->group_by('user_attendance.service_id');
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
	public function search_business_old($business_ids='',$lat='',$lang='',$distance='',$search_text='',$limit='',$offset=''){
		if(empty($lat && $lang)){
          $select='*';
          $order_by='id';
          $having='';
		}else{
		   //3959 miles 6371 km
		   $select="*,(
					    6371 * acos (
					      cos ( radians($lat) )
					      * cos( radians( lat	 ) )
					      * cos( radians( longitude ) - radians($lang) )
					      + sin ( radians($lat) )
					      * sin( radians( lat	 ) )
					    )
					  ) AS distance";
		    $order_by="distance";
		    $having=" HAVING distance < $distance";
		}
		if(!empty($business_ids)){
			$sql = "SELECT $select FROM business
            WHERE id IN($business_ids) AND status='Active' $having
            ORDER BY $order_by LIMIT $limit OFFSET $offset";
			
		}elseif($search_text){
            $sql = 'SELECT '.$select.' FROM business
            WHERE status="Active" AND business_name LIKE  "%'.$search_text.'%"
             '.$having.' ORDER BY '.$order_by.' LIMIT '.$limit.' OFFSET '.$offset;
		}else{
			 $sql = 'SELECT '.$select.' FROM business
            WHERE status="Active" ORDER BY '.$order_by.' LIMIT '.$limit.' OFFSET '.$offset;
		}
		$getbusiness = $this->dynamic_model->get_query_result($sql);
		//echo $this->db->last_query();die();
		return $getbusiness;
	}
	public function search_business($business_ids='',$lat='',$lang='',$distance='',$search_text='',$limit='',$offset=''){
         $having='';
		if(empty($lat && $lang)){
          $select='business.*';
          $order_by='business.create_dt';
		}else{
		   //3959 miles 6371 km
		   $select="business.*,(
					    6371 * acos (
					      cos ( radians($lat) )
					      * cos( radians(business.lat) )
					      * cos( radians(business.longitude ) - radians($lang) )
					      + sin ( radians($lat) )
					      * sin( radians(business.lat) )
					    )
					  ) AS distance";
		    $order_by="distance";
		    if(!empty($distance)){
		    $having=" HAVING distance < $distance";
		    }
		}
		if(!empty($business_ids)){
            $sql = "SELECT $select FROM business JOIN user ON user.id=business.user_id WHERE business.id IN($business_ids) AND user.status='Active' AND business.status='Active' $having
             ORDER BY $order_by LIMIT $limit";
			
		}

//.' OFFSET '.$offset
		if(!empty($business_ids)){
			$cat='';
			if(!empty($business_ids)){
				$cat = 'business.id IN('.$business_ids.') AND';
			}
            $sql = 'SELECT '.$select.' FROM business JOIN user ON user.id=business.user_id WHERE  '.$cat.' business.status="Active" AND user.status="Active" '.$having.' ORDER BY '.$order_by.' LIMIT '.$limit;
		}else if(!empty($search_text)){
				$sql = 'SELECT '.$select.' FROM business JOIN user ON user.id=business.user_id WHERE  business.status="Active" AND user.status="Active" AND business_name LIKE  "%'.$search_text.'%" '.$having.' ORDER BY '.$order_by.' LIMIT '.$limit;
		}else{
			 $sql = 'SELECT '.$select.' FROM business JOIN user ON user.id=business.user_id WHERE business.status="Active" AND user.status="Active" ORDER BY '.$order_by.' LIMIT '.$limit;
		}
		$getbusiness = $this->dynamic_model->get_query_result($sql);
		//echo $this->db->last_query();die();
		return $getbusiness;
	}
	public function get_business_according_to_distance_old($catid='',$lat='',$lang='',$distance=''){
		if(empty($lat && $lang)){
          $select='*';
          $order_by='create_dt';
          $having='';
		}else{
		   //3959 miles 6371 km
		   $select="*,(
					    6371 * acos (
					      cos ( radians($lat) )
					      * cos( radians( lat	 ) )
					      * cos( radians( longitude ) - radians($lang) )
					      + sin ( radians($lat) )
					      * sin( radians( lat	 ) )
					    )
					  ) AS distance";
		    $order_by="distance";
		    $having=" HAVING distance < $distance";
		}
		$sql = "SELECT $select FROM business
	            WHERE status='Active' AND FIND_IN_SET('$catid', category) $having
	            ORDER BY $order_by LIMIT 5";

	     //$sql = "SELECT b.* FROM business as b JOIN `business_category`as c on b.id = c.business_id where c.category IN ('$catid') GROUP BY b.id ORDER BY b.name LIMIT 25";
	    // echo $sql; die;
		$getbusiness = $this->dynamic_model->get_query_result($sql);
		//echo $this->db->last_query();die();
		return $getbusiness;
	}
	public function get_business_according_to_distance($catid='',$lat='',$lang='',$distance='',$limit=5){
		$business_ids=$getbusiness='';
		if(!empty($catid)){
		$condition=array("category"=>$catid,"parent_id !="=>0,"type"=>1);
        $getsubcat = $this->dynamic_model->getdatafromtable('business_category',$condition); 
        if(!empty($getsubcat)){
        	$subcat=array_column($getsubcat,'business_id');
        	$business_ids=implode(',',$subcat);
          }
        }
		if(empty($lat && $lang)){
          $select='business.*';
          $order_by='business.create_dt';
          $having='';
		}else{
		   //3959 miles 6371 km
		   $select="business.*,(
					    6371 * acos (
					      cos ( radians($lat) )
					      * cos( radians( business.lat	 ) )
					      * cos( radians( business.longitude ) - radians($lang) )
					      + sin ( radians($lat) )
					      * sin( radians( business.lat	 ) )
					    )
					  ) AS distance";
		    $order_by="distance";
		    $having=" HAVING distance < $distance";
		}
		 if(!empty($business_ids)){
		 	 $sql = "SELECT $select FROM business JOIN user ON user.id=business.user_id WHERE business.id IN($business_ids) AND user.status='Active' AND business.status='Active' $having
            ORDER BY $order_by LIMIT $limit";
            //echo $sql; die;
            $getbusiness = $this->dynamic_model->get_query_result($sql);	
		}
		//echo $this->db->last_query();die();
		//print_r($getbusiness);die;
		return $getbusiness;
	}
	public function user_location_checked_in_studio($business_id='',$lat='',$lang='',$distance='0.1'){
		   $getbusiness='';
		   //3959 miles 6371 km
		   if(!empty($lat && $lang)){
		   $select="*,(
					    6371 * acos (
					      cos ( radians($lat) )
					      * cos( radians( lat	 ) )
					      * cos( radians( longitude ) - radians($lang) )
					      + sin ( radians($lat) )
					      * sin( radians( lat	 ) )
					    )
					  ) AS distance";
		    $order_by="distance";
		    $having=" HAVING distance < $distance";
		
		$sql = "SELECT $select FROM business
	            WHERE id=$business_id AND status='Active' $having";
		$getbusiness = $this->dynamic_model->get_query_result($sql);
	    }
		//echo $this->db->last_query();die();
		return $getbusiness;
	}
 
}

