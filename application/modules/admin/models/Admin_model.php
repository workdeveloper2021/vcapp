<?php

defined('BASEPATH') OR exit('No direct script access allowed'); 

class Admin_model extends CI_Model {
	public function __construct(){
		parent::__construct();
	}

	public function login($logdata){
		$condition = "email =" . "'" . $logdata['username'] . "'";
		$this->db->select('*');
		$this->db->from(TABLE_USERS);
		$this->db->where($condition);
		$this->db->limit(1);
		$query = $this->db->get();
		if ($query->num_rows() == 1) {
			return $query->row_array();
		} else {
			return false;
		}
	}

}