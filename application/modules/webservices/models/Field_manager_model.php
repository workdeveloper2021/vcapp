<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Field_manager_model extends CI_Model {
	public function __construct(){
		parent::__construct();
	}
	public function manager_plan_check($plan_id='',$user_id='')
	{
		$this->db->select('*');
		$this->db->from('subscription');
		$this->db->where('sub_plan_id',$plan_id);
		$this->db->where('sub_user_id',$user_id);
		$this->db->order_by("sub_id","DESC");
        $this->db->limit(1); 
		$query = $this->db->get();
		return $query->row_array();
	}


}