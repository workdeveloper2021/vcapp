<?php

defined('BASEPATH') OR exit('No direct script access allowed');



class Users_model extends CI_Model {
	public function __construct(){
		parent::__construct();
	}

	public function delete_auth($userid) {
		$this->db->delete('auth_user', array('user_id' => $userid));
		return true;
	}

	public function get_country()
	{
		$this->db->select('*');
		$this->db->from('countries');
		$this->db->order_by('sortname',"ASC");
		$query = $this->db->get();
		return $query->result_array();
	}
	public function get_states($country_id='')
	{
		$this->db->select('*');
		$this->db->from('countries_states');
		$this->db->where('country_id',$country_id);
		$query = $this->db->get();
		return $query->result_array();
	}
	public function get_cities($state_id='')
	{
		$this->db->select('*');
		$this->db->from('countries_cities');
		$this->db->where('state_id',$state_id);
		$query = $this->db->get();
		return $query->result_array();
	}
  public function slot_check($slot_id='',$slot_date_id='')
	{
		if(!empty($slot_id)){
		$slottimeid= array_column($slot_id, 'slottimeid');
		$this->db->select('*');
		$this->db->from('pitch_time_slote');
		$this->db->where('slot_date_id',$slot_date_id);
		$this->db->where_in('id',$slottimeid);
		$this->db->where('slot_available_status',1);
		 $query = $this->db->get();
		 return $result= $query->result_array();
		}else{
			return false;
		}
	}
    public function slot_check_avaliblity($slot_id='',$slot_date_id='')
	{
		if(!empty($slot_id)){
		$slottimeid= array_column($slot_id, 'slottimeid');
		$this->db->select('*');
		$this->db->from('pitch_time_slote');
		$this->db->where('slot_date_id',$slot_date_id);
		$this->db->where_in('id',$slottimeid);
		$this->db->where('slot_available_status',0);
		 $query = $this->db->get();
		 return $result= $query->result_array();
		}else{
			return false;
		}
	}
	public function get_slot_date($slotdate_id='')
	{
		if(!empty($slotdate_id)){
		$this->db->select('*');
		$this->db->from('pitch_date_slote');
		$this->db->where('id',$slotdate_id);
		 $query = $this->db->get();
		 return $result= $query->result_array();
		}else{
			return false;
		}
	}
    public function get_upcoming_booking_data($userid='',$venueid='')
	{
		$arrdate=array();
		$this->db->select('*');
		$this->db->from('pitch_booking');
		$this->db->where('user_id',$userid);
		$this->db->where('venue_id',$venueid);
		$query = $this->db->get();
		$booking_data= $query->result_array();
		if(!empty($booking_data)){
             foreach($booking_data as $value){
             $wh=array('id'=>$value['time_slote_id']);
             $time_slot_data=$this->db->get_where('pitch_time_slote',$wh)->result_array();
	               if(!empty($time_slot_data)){
		               foreach ($time_slot_data as $value1) {
		                    $arrdate[]= $value1['slot_date'];
		               }
	              }
             }
             return $arrdate;
		}else{
			return false;
		}
	}
	public function slot_time_check($slot_id='',$trx_id='')
	{
		if(!empty($slot_id)){
		$slottimeid= array_column($slot_id, 'slottimeid');
		$this->db->select('*');
		$this->db->from('pitch_booking');
		$this->db->where_in('time_slote_id',$slottimeid);
		$this->db->where('transaction_id',$trx_id);
		 $query = $this->db->get();
		 return $result= $query->result_array();
		}else{
			return false;
		}
	}


}