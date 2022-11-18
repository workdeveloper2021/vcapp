<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class My_Controller extends MX_Controller {
	public function __construct(){
		parent::__construct();
		$this->load->library('session');
		$this->load->helper('admin_common_helper');
		date_default_timezone_set('Asia/Kolkata');
	}
	public function index(){
		
	}
	public function frontendtemplates($view, $data = array(), $headerdata = array()){
		$footerdata = array();
		$this->load->view('templates/front_header', $headerdata);
		$this->load->view('web/'.$view,$data);
		$this->load->view('templates/front_footer',$footerdata);
	}
	public function admintemplates($view, $data = array(), $headerdata = array()){
		 $role_id_arr=array();
		if($this->session->userdata('logged_in')){
			$userid = $this->session->userdata['logged_in']['session_userid'];
			$session_userrole=$this->session->userdata['logged_in']['session_userrole'];
            $loguserinfo = $this->dynamic_model->get_user($userid);
			if($loguserinfo['status']=="Active" || $session_userrole==1){	
			if($this->session->userdata['logged_in']['session_lockscreen'] == "0"){
				$this->load->view('templates/header', $headerdata);
				$this->load->view('admin/'.$view,$data);
				$this->load->view('templates/footer');
			}else{
                   $this->load->view('lockscreen',$loguserinfo['userinfo'] );
                }				
			}else{
		  		$this->session->set_flashdata('loginclass', 'danger');
                $this->session->set_flashdata('login', $this->lang->line('deactive_account')); 
                $this->logout_user($userid);
			}		  	
		}else{
		  $this->load->view('login');
		}	
	}

	public function restauranttemplates($view, $data = array(), $headerdata = array()){
		if($this->session->userdata('logged_in')){
			if($this->session->userdata['logged_in']['session_lockscreen'] == "0"){
				if($this->session->userdata['logged_in']['session_userrole'] == "2"){
					$this->load->view('templates/header', $headerdata);
					$this->load->view('restaurant/'.$view,$data);
					$this->load->view('templates/footer');
				} else {
					redirect(site_url());
				}				
			} else {
				$userid = $this->session->userdata['logged_in']['session_userid'];
            	$loguserinfo['userinfo'] = $this->dynamic_model->get_user($userid);
		  		$this->load->view('lockscreen', $loguserinfo);
			}		  	
		} else {
		  $this->load->view('login');
		}	
	} 
	public function logout_user($login_user_id=''){
        $this->session->unset_userdata('logged_in'); 
        $data2 = array(
            'is_loggedin' => '0'
        );
        $wheres = array("id" => $login_user_id);
        $result = $this->dynamic_model->updateRowWhere(TABLE_USERS, $wheres, $data2);
        $this->load->view('login');
        redirect(base_url('admin'));
    }

	 

}