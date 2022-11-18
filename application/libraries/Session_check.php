<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
 * session libraries 
 * check the session
 * 
 */ 
class Session_check 
{
	function sessionCheck()
	{
		$this->CI =& get_instance();
		//this code is user for clear cache
	   header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
       header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
       header("Cache-Control: no-store, no-cache, must-revalidate"); 
       header("Cache-Control: post-check=0, pre-check=0", false);
       header("Pragma: no-cache");
	} 
	
	/*
	 * this funciton check the session if exist then go to home
	 * if not exist then redirect to index
	 * 
	 */ 
	function checkSession()
	{    
		
		if($this->CI->session->userdata('userRoleId')) 
		{   
			return true;
	    }
	    else
	    {
		   $this->CI->session->set_flashdata('globalmsg', 'Please Enter Email ID and Password');
		   redirect(base_url());
		}
	
	}
	
	
	/*
	 * This function check user already login 
	 * redirect to login page to home
	 * 
	 */ 
	function CheckUserLoginSession()
	{
		if($this->CI->session->userdata('userId')) 
		{   
	        redirect(base_url().'admin/dashboard');
	    }else{
			return false;
		}
	}

	
}

/* Location: ./application/libraries/session_check.php */
