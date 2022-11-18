<?php

defined('BASEPATH') OR exit('No direct script access allowed');
// Get Session User details
function getuserdetails(){
	$CI = get_instance();
	$id = $CI->session->userdata['logged_in']['session_userid'];
	$CI->load->model('dynamic_model');
	$return = $CI->dynamic_model->get_user($id);
	return $return;
}
function get_business_category($business_category_ids=''){
	$business_categories='';
	$CI = get_instance();
	$CI->load->model('dynamic_model');
    $business_category= $CI->dynamic_model->getdatafromtable(TABLE_BUSINESS_CATEGORY,"id IN ($business_category_ids)",'GROUP_CONCAT(category_name) AS category_name');
    if(!empty($business_category)){
    	$business_categories= $business_category[0]['category_name'];
    }
   return $business_categories;
}
   // // Get services type Name  
   //  function get_services_type_name($service_type_id=''){
   //      $CI = get_instance();
   //      $getservice = '';
   //      if($service_type_id){
   //      $sql = "SELECT GROUP_CONCAT(service_name) AS service_name FROM manage_services_type
   //      WHERE id IN ($service_type_id)";
   //       $getservice= $CI->dynamic_model->get_query_result($sql);   
   //       $getservice = (!empty($getservice[0]->service_name)) ? $getservice[0]->service_name : '';
   //      }
   //      return $getservice;

   //  }
   //   // Get business type Name  
   //  function get_business_type_name($business_type_id=''){
   //      $CI = get_instance();
   //      $condition = array('id' => $business_type_id);
   //      $result = $CI->dynamic_model->getdatafromtable('manage_business_type',$condition);
   //      return (!empty($result[0]['business_type'])) ? $result[0]['business_type']: '';
   //  }
function get_operation_by_module($module_id=''){
	$CI = get_instance();
	$CI->load->model('dynamic_model');
    $operation= $CI->dynamic_model->getdatafromtable(TABLE_OPERATIONS,array("permission_module_id"=>$module_id));
    return $operation;
}
function get_permission_by_operation($user_id='',$operation_id=''){
	$CI = get_instance();
	$CI->load->model('dynamic_model');
    $permission= $CI->dynamic_model->getdatafromtable(TABLE_PERMISSION,array("operation_id"=>$operation_id,"user_id"=>$user_id));
    return $permission;
}
if (!function_exists('check_permission')){ 
    function check_permission($action='',$operation_slug='',$purpose=''){ 
        $ci = & get_instance();
        if($ci->session->userdata && $ci->session->userdata['logged_in']){

        }else{
               redirect(base_url());
               return FALSE;
        }
        $user_id=$ci->session->userdata['logged_in']['session_userid'];
        $role_id=$ci->session->userdata['logged_in']['session_userrole'];
        if($role_id==1){
            return TRUE;
        }
        $ci->db->select('P.*');
        $ci->db->from(TABLE_PERMISSION.' AS P');
        $ci->db->join(TABLE_OPERATIONS.' AS O', 'O.id = P.operation_id');
        $ci->db->join(TABLE_PERMISSION_MODULE.' AS M', 'M.id = O.permission_module_id');
        $ci->db->where('P.user_id',$user_id );
        $ci->db->where('O.operation_slug',$operation_slug );
        $results = $ci->db->get()->result_array();
            if(!empty($results)){
            switch($action){
                case "0":
                    $permission_check= $results[0]['is_add'];
                    break;
                case "1":
                     $permission_check=$results[0]['is_edit'];
                    break;
                case "2":
                    $permission_check=$results[0]['is_view'];
                    break;
                case "3":
                    $permission_check=$results[0]['is_delete'];
                case "4":
                    $permission_check=$results[0]['is_status'];
                    break;    
                 default:
                  $permission_check=0;
            }
            if ($permission_check ==1){
                 return TRUE;
             }else{
             	if($purpose ==1){
	              redirect(base_url());
             	}else{
                 return FALSE;
             	}
             }   
         }else{
         	if($purpose ==1){
               redirect(base_url());
         	}else{
             return FALSE;
         	}
        }   
    }
}

if (!function_exists('clean_string')) {
    function clean_string($string) {
       $CI = get_instance();
       $CI->load->model('dynamic_model');
       $string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.

       return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }
  }


