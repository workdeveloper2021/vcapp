<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$this->lang->load("message","english");
//admin helper function  start

    /****************Function get_business_details*******************
     * @type            : Function
     * @Author          : Aamir
     * @function name   : getbusinessdetails
     * @description     : get all business details.   
     * @param           : null 
     * @return          : null 
     * ********************************************************** */
  function get_business_details($id=''){

      $CI = &get_instance();
      $CI->db->select('*');
      $CI->db->from('business');
      $CI->db->where('id',$id);
      $databusiness = $CI->db->get()->result_array();
      //$databusiness = @$databusiness[0];
      if(isset($databusiness[0]['id'])){
          $databusiness = $databusiness[0];
      
          $getcat = array();
          if($databusiness['category']){
          $ids = $databusiness['category'];
          $sql = "SELECT GROUP_CONCAT(category_name) AS category_name FROM manage_category
            WHERE id IN ($ids)";
          $getcat = $CI->dynamic_model->get_query_result($sql); 
          $getcat = $getcat[0]->category_name;
          }

          $img = site_url().'uploads/business/'.$databusiness['logo'];
          $imgname = pathinfo($img, PATHINFO_FILENAME);
          $ext = pathinfo($img, PATHINFO_EXTENSION);
          $thumb = site_url().'uploads/business/'.$imgname.'_thumb.'.$ext;

          $busi_img = site_url().'uploads/business/'.$databusiness['business_image'];
          $imgnamebusi = pathinfo($busi_img, PATHINFO_FILENAME);
          $extbusi = pathinfo($busi_img, PATHINFO_EXTENSION);
          $thumb_img = site_url().'uploads/business/'.$imgnamebusi.'_thumb.'.$extbusi;

          return array("business_id"=>encode($id),"business_name"=>$databusiness['business_name'],"email"=>$databusiness['primary_email'],"address"=>$databusiness['address'],"city"=>$databusiness['city'],"state"=>$databusiness['state'],"country"=>$databusiness['country'],"business_phone"=>$databusiness['business_phone'],"logo"=>$img,"thumb"=>$thumb,"business_img"=>$busi_img,"business_thumb"=>$thumb_img,"skills"=>$getcat,"latitude"=>$databusiness['lat'],"longitude"=>$databusiness['longitude'],"zipcode"=>$databusiness['zipcode'],"location_name"=>$databusiness['location_name']);
          } else {
          return array();
          }
    }
    /****************Function get_user_details*******************
     * @type            : Function
     * @Author          : Aamir
     * @function name   : get_user_details
     * @description     : get all user details.   
     * @param           : null 
     * @return          : null 
     * ********************************************************** */

        function get_user_details_old($id){ 
            $datauser = getdatafromtable('user',array('id'=>$id));
            if(!empty($datauser)){                   
            $full_name = $datauser[0]['name'];
            $lastname = $datauser[0]['lastname'];
            $user_id = $datauser[0]['id'];
            $auth = base64_encode(encode($user_id));
            $role_id = $datauser[0]['role_id'];
            $email = $datauser[0]['email'];
            $password = $datauser[0]['password'];
            $email_verified = $datauser[0]['email_verified'];
            $mobile = $datauser[0]['mobile'];
            $mobile_verified = $datauser[0]['mobile_verified'];
            $status = $datauser[0]['status'];
            $profile_status = $datauser[0]['profile_status'];
            $date_of_birth = $datauser[0]['date_of_birth'];
            $notification = !empty($datauser[0]['notification']) ? json_decode($datauser[0]['notification']) : "";
            $age = !empty($datauser[0]['date_of_birth']) ? date('Y') - date('Y',strtotime($datauser[0]['date_of_birth'])) : "";  
            //image detail
            if($datauser[0]['profile_img']){
                $img = site_url().'uploads/user/'.$datauser[0]['profile_img'];
                $imgname = pathinfo($img, PATHINFO_FILENAME);
                $ext = pathinfo($img, PATHINFO_EXTENSION);
                $thumb = site_url().'uploads/user/'.$imgname.'_thumb.'.$ext;
            }
            $thumburl = !empty($thumb) ? $thumb : site_url().'uploads/user/default.png';
            $imgurl = !empty($img) ? $img : site_url().'uploads/user/default.png';
            //check payment status
              if(!empty($datauser[0]['plan_id'])){
                $plan_id=$datauser[0]['plan_id'];
                $payment_data=plan_check($plan_id,$user_id);
                $payment_status = (!empty($payment_data)) ? $payment_data['plan_status']: '';
               }else{
                $payment_status = '';
               }
            //get business details
            $databusiness = getdatafromtable('business',array('user_id'=>$id));
            $business_status = (!empty($databusiness[0]['status'])) ? $databusiness[0]['status'] : '';
            $business_id = (!empty($databusiness[0]['id'])) ? $databusiness[0]['id'] : '';
            $business_name = (!empty($databusiness[0]['business_name'])) ? $databusiness[0]['business_name'] : '';

            $img = site_url().'uploads/business/'.$databusiness[0]['logo'];
            $imgname = pathinfo($img, PATHINFO_FILENAME);
            $ext = pathinfo($img, PATHINFO_EXTENSION);
            $thumb = site_url().'uploads/business/'.$imgname.'_thumb.'.$ext;

            $busi_img = site_url().'uploads/business/'.$databusiness[0]['business_image'];
            $imgnamebusi = pathinfo($busi_img, PATHINFO_FILENAME);
            $extbusi = pathinfo($busi_img, PATHINFO_EXTENSION);
            $thumb_img = site_url().'uploads/business/'.$imgnamebusi.'_thumb.'.$extbusi;

            $redirect_to_verify='0';
            if($datauser[0]['email_verified'] != 1){
                $redirect_to_verify='1'; // here redirect_to verify = 1 means email is not verified
             }elseif(empty($plan_id)){
                $redirect_to_verify='2'; // here redirect_to verify = 2 means subscription plan not purchased             
             }elseif(empty($databusiness)){
                $redirect_to_verify='3'; // here redirect_to verify = 3 means business not registered              
             }
             // elseif(!empty($databusiness &&  $databusiness[0]['status'] != 'Active')){
             //    $redirect_to_verify='4';// here redirect_to verify =  means business not activated 
             // }  
            return array("Authorization"=>$auth,"id"=>encode($user_id),"name"=>$full_name,"lastname"=>$lastname,"email"=>$email,"mobile"=>$mobile,"password"=>$password,"role_id"=>$role_id,"date_of_birth"=>$date_of_birth,'age'=>$age,'profile_img'=>$imgurl,'thumb'=>$thumburl,'email_verified'=>$email_verified,'status'=>$status,'profile_status'=>$profile_status,'notification'=>$notification,"redirect_to_verify"=>$redirect_to_verify,'business_id'=>encode($business_id),'business_status'=>$business_status,'payment_status'=>$payment_status,"business_name"=>$business_name,"logo"=>$img,"logothumb"=>$thumb,"business_img"=>$busi_img,"business_thumb"=>$thumb_img);
            } else {
            return array();
            }
        }

     function get_user_details($id='',$role=''){
           $ci = & get_instance();
           $ci->load->model('dynamic_model');
           $condition=array('user.id'=>$id,'user_role.role_id'=>$role);
           $on='user_role.user_id = user.id';
           $datauser = $ci->dynamic_model->getTwoTableData('user.*,user_role.role_id','user','user_role',$on,$condition); 
          
            //$datauser = getdatafromtable('user',array('id'=>$id));
            if(!empty($datauser)){                   
            $full_name = ($datauser[0]['name'] == 'undefined') ? '' : $datauser[0]['name'];
            $lastname = ($datauser[0]['lastname'] == 'undefined') ? '' : $datauser[0]['lastname'];
            $user_id = $datauser[0]['id'];
            $auth = base64_encode(encode($user_id));
            $role_id = $datauser[0]['role_id'];
            $email = $datauser[0]['email'];
            $password = $datauser[0]['password'];
            $email_verified = $datauser[0]['email_verified'];
            $mobile = ($datauser[0]['mobile'] == 'undefined') ? '' : $datauser[0]['mobile'];
            $mobile_verified = $datauser[0]['mobile_verified'];
            $status = $datauser[0]['status'];
            $zipcode = ($datauser[0]['zipcode'] == 'undefined') ? '' : $datauser[0]['zipcode'];
            $country = ($datauser[0]['country'] == 'undefined') ? '' : $datauser[0]['country'];
            $country_code = (!empty($datauser[0]['country_code'])) ? $datauser[0]['country_code'] : "" ;
            $state = ($datauser[0]['state'] == 'undefined') ? '' : $datauser[0]['state'];
            $city = ($datauser[0]['city'] == 'undefined') ? '' : $datauser[0]['city'];
            $address = ($datauser[0]['address'] == 'undefined') ? '' : $datauser[0]['address'];
            $first_login = $datauser[0]['first_login'];
            $location = ($datauser[0]['location'] == 'undefined') ? '' : ((!empty($datauser[0]['location'])) ? $datauser[0]['location'] : '');
            $lat = ($datauser[0]['lat'] == 'undefined') ? '' : ((!empty($datauser[0]['lat'])) ? $datauser[0]['lat'] : '');
            $lang = ($datauser[0]['lang'] == 'undefined') ? '' : ((!empty($datauser[0]['lang'])) ? $datauser[0]['lang'] : '');
            $gender = ($datauser[0]['gender'] == 'undefined') ? '' : $datauser[0]['gender'];
            $profile_status = $datauser[0]['profile_status'];
            $marchant_type = $datauser[0]['marchant_id_type'];
            $marchant_id = $datauser[0]['marchant_id'];
            $clover_key = $datauser[0]['clover_key'];
            $access_token = $datauser[0]['access_token'];
            $cad_marchant_id = $datauser[0]['cad_marchant_id'];

            

            $date_of_birth = $datauser[0]['date_of_birth'];
            $notification = !empty($datauser[0]['notification']) ? json_decode($datauser[0]['notification']) : "";
            $age = !empty($datauser[0]['date_of_birth']) ? date('Y') - date('Y',strtotime($datauser[0]['date_of_birth'])) : "";  
            //image detail
            if($datauser[0]['profile_img']){
                $img = site_url().'uploads/user/'.$datauser[0]['profile_img'];
                $imgname = pathinfo($img, PATHINFO_FILENAME);
                $ext = pathinfo($img, PATHINFO_EXTENSION);
                $thumb = site_url().'uploads/user/'.$imgname.'_thumb.'.$ext;
            }
            $thumburl = !empty($thumb) ? $thumb : site_url().'uploads/user/default.png';
            $imgurl = !empty($img) ? $img : site_url().'uploads/user/default.png';
            //check payment status
            
              if(!empty($datauser[0]['plan_id'])){
                $plan_id=$datauser[0]['plan_id'];
                $payment_data=plan_check($plan_id,$user_id);
                $payment_status = (!empty($payment_data)) ? $payment_data['plan_status']: '';
               }else{
                $payment_status = '';
               }
            //get business details
            $databusiness = getdatafromtable('business',array('user_id'=>$id));
            $business_status = (!empty($databusiness[0]['status'])) ? $databusiness[0]['status'] : '';
            $business_id = (!empty($databusiness[0]['id'])) ? $databusiness[0]['id'] : '';
            $business_name = (!empty($databusiness[0]['business_name'])) ? $databusiness[0]['business_name'] : '';
            $business_logo = (!empty($databusiness[0]['logo'])) ? $databusiness[0]['logo'] : '';
            $business_image = (!empty($databusiness[0]['business_image'])) ? $databusiness[0]['business_image'] : '';


            $img = site_url().'uploads/business/'.$business_logo;
            $imgname = pathinfo($img, PATHINFO_FILENAME);
            $ext = pathinfo($img, PATHINFO_EXTENSION);
            $thumb = site_url().'uploads/business/'.$imgname.'_thumb.'.$ext;

            
            if (!empty($business_image)) {
                $busi_img = site_url().'uploads/business/'.$business_image;
            } else {
                $busi_img = site_url().'uploads/logo.png';
            }
            $imgnamebusi = pathinfo($busi_img, PATHINFO_FILENAME);
            $extbusi = pathinfo($busi_img, PATHINFO_EXTENSION);
            $thumb_img = site_url().'uploads/business/'.$imgnamebusi.'_thumb.'.$extbusi;

            $redirect_to_verify='0';
            if($datauser[0]['email_verified'] != 1){
                $redirect_to_verify='1'; // here redirect_to verify = 1 means email is not verified
             }elseif(empty($plan_id)){
                $redirect_to_verify='2'; // here redirect_to verify = 2 means subscription plan not purchased             
             }elseif(empty($databusiness)){
                $redirect_to_verify='3'; // here redirect_to verify = 3 means business not registered              
             }
             // elseif(!empty($databusiness &&  $databusiness[0]['status'] != 'Active')){
             //    $redirect_to_verify='4';// here redirect_to verify =  means business not activated 
             // }  
                $collection = array("Authorization"=>$auth,"id"=>encode($user_id),"marchant_type" =>$marchant_type,"marchant_id" =>$marchant_id,"clover_key" =>$clover_key,"access_token" =>$access_token, "cad_marchant_id" =>$cad_marchant_id,"name"=>$full_name,"lastname"=>$lastname,"email"=>$email,"mobile"=>$mobile,"password"=>$password,"role_id"=>$role_id,"date_of_birth"=>$date_of_birth,'age'=>$age,'gender'=>$gender,'profile_img'=>$imgurl,'thumb'=>$thumburl,'email_verified'=>$email_verified,'status'=>$status,'profile_status'=>$profile_status,'zipcode'=>$zipcode,'country'=>$country,'country_code'=>$country_code,'state'=>$state,'city'=>$city,'address'=>$address,'lat'=>$lat,'lang'=>$lang,'street'=>$location,'notification'=>$notification,"redirect_to_verify"=>$redirect_to_verify,'business_id'=>encode($business_id),'business_status'=>$business_status,'payment_status'=>$payment_status,"business_name"=>$business_name,"logo"=>$img,"logothumb"=>$thumb,"business_img"=>$busi_img,"business_thumb"=>$thumb_img,"first_login"=>"$first_login");
              
                if (($role == '4') || ($role == '2')) {
                    /* $skills = $ci->dynamic_model->getQueryRowArray('SELECT GROUP_CONCAT(skill) as skill FROM instructor_details WHERE user_id = '.$user_id)['skill'];
                    $collection['skills'] = $ci->dynamic_model->getQueryRowArray('SELECT GROUP_CONCAT(name) as skills FROM `manage_skills` where id in ('.$skills.')')['skills']; */
                    $info = $ci->dynamic_model->getQueryRowArray('SELECT skill, total_experience, about,  employee_id,substitute_instructor_name,employee_contractor,start_date,  appointment_fees,appointment_fees_type FROM instructor_details WHERE user_id = '.$user_id);
                      if(!empty($info)){

                    $collection['skills'] = $ci->dynamic_model->getQueryRowArray('SELECT GROUP_CONCAT(name) as skills FROM `manage_skills` where id in ('.$info['skill'].')')['skills'];
                    $collection['experience'] = $info['total_experience'];
                     $collection['total_experience'] = $info['total_experience'];
                     $collection['skill'] = $info['skill'];
                    $collection['about'] = $info['about'];
                    $collection['employee_id'] = $info['employee_id'];
                    $collection['substitute_instructor_name'] = $info['substitute_instructor_name'];
                    $collection['employee_contractor'] = $info['employee_contractor'];
                    $collection['start_date'] = date('Y-m-d',$info['start_date']);
$collection['appointment_fees'] = $info['appointment_fees'];
$collection['appointment_fees_type'] = $info['appointment_fees_type'];
}
 
                    
                }
                return $collection;
            } else {
            return array();
            }
        }

    function web_checkuserid(){
    $arg = array();
    $CI = get_instance();
    $user_token =  $CI->input->get_request_header('Authorization',true);
    $user_role =  $CI->input->get_request_header('role',true);
    $user_role=(!empty($user_role)) ? $user_role : 2;
    $userid = decode(base64_decode($user_token));
    if(!empty($user_token)){ 
        $data = get_user_details($userid,$user_role);
        // print_r($data);die;
        if($data){
        $email_verified = $data['email_verified'];
        $status = $data['status'];
        
        if ($email_verified != 1) {
        $arg['status']     = 0;
        $arg['error_code']  = EMAILNOTVERIFED;
        $arg['error_line']= __line__;
        $arg['message']    = $CI->lang->line('email_not_varify');
        $arg['data']     = array();
        return $arg;
        exit();
        }

        if($status == 'Deactive'){
        $arg['status']    = 0;
        $arg['message']   = $CI->lang->line('user_deactive');
        $arg['error_code'] = REST_Controller::HTTP_OK;
        $arg['error_line']= __line__;
        $arg['data']      = array();
        return $arg;
        exit();
        }
        if($data['business_status'] == 'Deactive'){
        $arg['status']    = 0;
        $arg['message']   = $CI->lang->line('business_not_activated');
        $arg['error_code'] = REST_Controller::HTTP_OK;
        $arg['error_line']= __line__;
        $arg['data']      = array();
        return $arg;
        exit();
        } 
        if($data['payment_status'] != 'Active'){
        $arg['status']    = 0;
        $arg['message']   = $CI->lang->line('plan_not_purchased');
        $arg['error_code'] = REST_Controller::HTTP_OK;
        $arg['error_line']= __line__;
        $arg['data']      = array();
        return $arg;
        exit();
        }
        $arg['status'] = 1;
        $arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
        $arg['error_line']= __line__;
        $arg['message'] = '';
        $arg['data']  = $data;
        } else {
        $arg['status']    = 0;
        $arg['error_code'] = REST_Controller::HTTP_OK;
        $arg['error_line']= __line__;
        $arg['message']   = $CI->lang->line('invalid_detail');
        $arg['data']      = array();
        }
    } else {
        $arg['status'] = 0;
        $arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
        $arg['error_line']= __line__;
        $arg['message'] = 'Please Send Userid';
        $arg['data']  = array();
    }
    return $arg;
 }
    /*
    Get the size in bytes and error uploading
    Regarding size and Incorrect format for multiple images
    */
    function fileUploadingError($filenm='',$error_type=''){
       $res = array();
       $valid_types = array("image/jpg","image/jpeg","image/JPG","image/JPEG","image/png", "image/PNG","image/gif");
       $number_of_files = sizeof($_FILES["$filenm"]['name']);
       for($i=0;$i< $number_of_files;$i++){
        $bytes= $_FILES[$filenm]['size'][$i];
        //25 mb 26214400  //20mb  20971520 //10mb 10485760
        if($error_type=='size'){
            if ($bytes <= 10485760)
            {
                $allow = 1;
            }else{
                 $allow = 2;
            }
        }else{
            if(in_array($_FILES[$filenm]['type'][$i], $valid_types)) 
             {
               $allow= 1;
             }else{
                $allow= 2; 
             }
        }
           $res[] = $allow;
        } 
     if(! in_array(2,$res)){
         return true;
        }else{
            return false;
        }      
    }
     //get encode Ids and return string ids Or array
    function multiple_decode_ids($ids='',$flag='')
    {
        $data=array();
        if(!empty($ids )){
        $ids_arr= array_filter(explode(',',$ids));
         if(!empty($ids_arr)){
          foreach ($ids_arr as $value) {
              $data[]=decode($value);
          }  
           return ($flag==1) ? $data : implode(',',$data);
         }
        }
        return false;
    }
    //plan status check 
   function plan_check($plan_id='',$user_id='')
    {
        $CI = &get_instance();
        $CI->db->select('*');
        $CI->db->from('subscription');
        $CI->db->where('sub_plan_id',$plan_id);
        $CI->db->where('sub_user_id',$user_id);
        $CI->db->order_by('sub_id', 'desc');
        //$CI->db->where('plan_status','Active');
        $query = $CI->db->get();
        return $query->row_array();
    }

     // Get business type Name  
   //  function get_business_type_name($business_type_id=''){
   //      $CI = get_instance();
   //      $condition = array('id' => $business_type_id);
   //      $result = $CI->dynamic_model->getdatafromtable('manage_business_type',$condition);
   //      return (!empty($result[0]['business_type'])) ? $result[0]['business_type']: '';
   //  }
   //  // Get passes Name  
   //  function get_passes_type_name($pass_type_id=''){
   //      $CI = get_instance();
   //      $condition = array('id' => $pass_type_id);
   //      $result = $CI->dynamic_model->getdatafromtable('manage_pass_type',$condition);
   //      return (!empty($result[0]['pass_type'])) ? $result[0]['pass_type']: '';
   //  }
   //  // // Get Category Name using category ID
   //  function get_categories($category_id=''){
   //      $CI = get_instance();
   //      $getcat = '';
   //      if($category_id){
   //      $sql = "SELECT GROUP_CONCAT(category_name) AS category_name FROM business_category
   //      WHERE id IN ($category_id)";
   //       $getcat = $CI->dynamic_model->get_query_result($sql);   
   //       $getcat = (!empty($getcat[0]->category_name)) ? $getcat[0]->category_name : '';
   //      }
   //      return $getcat;
   //  }
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







