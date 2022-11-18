<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class StudioOwner extends My_Controller{
    private $login_user_id = null;
    public function __construct(){      
        parent::__construct();      
        $this->load->model('dynamic_model');
        $this->load->model('admin_model');
        $this->load->model('studio_model');
        $this->lang->load("admin_message", "english");
        if($this->session->userdata('logged_in')){
            $currentuser = getuserdetails();
            $this->login_user_id = $currentuser['id'];
        }
    }
            
    public function index() {
        check_permission(VIEW,"owner_list",1);
        $header['title'] = $this->lang->line('owner_list'); 
        $data['userdata']=$this->dynamic_model->getdatafromtable(TABLE_USERS,array("role_id "=>2),'id');       
        $this->admintemplates('studio_owner/owner_list',$data, $header);
    }

    public function ownerAjaxlist(){
        $start         =  $this->input->get('start'); // get promo code Id
        $length        =  $this->input->get('length'); // get promo code Id
        $draw          =  $this->input->get('draw'); // get promo code Id
        $order   =  $this->input->get('order');
        if(!empty($order)){ 
            if($order[0]['column']==3){
                $column_name='name';
            }else if($order[0]['column']==4){
                $column_name='email';
            }else if($order[0]['column']==5){
                $column_name='mobile';                
            }else{
                $column_name='id';
            }
        }
        $totalRecord      = $this->studio_model->ownerAjaxlist(true);
        $getRecordListing = $this->studio_model->ownerAjaxlist(false,$start,$length, $column_name, $order[0]['dir']);
        // echo'<pre>';
        // print_r($getRecordListing);
        // die;
        $recordListing = array();
        $content='[';
        $i=0;       
        $srNumber=$start;       
        if(!empty($getRecordListing)){
            $actionContent = '';
            foreach($getRecordListing as $recordData) {
                    $login_user_id = encode($recordData->id);
                    $profile_url = base_url('admin/StudioOwner/userprofile/').$login_user_id;
                    $recordListing[$i][0]=   '<input type="checkbox" name="checkAll[]" value="'.($recordData->id).'" class="cb-element" ">';
                    $recordListing[$i][1]=  $srNumber+1;
                    if(!empty($recordData->profile_img)){
                    $user_pic = base_url('uploads/user/').'/'.$recordData->profile_img;
                     }else{
                         $user_pic = base_url('uploads/user/userdefault.png');
                     }
                    $recordListing[$i][2]= '<img src="'.$user_pic.'" width="40" height="40">';
                    $recordListing[$i][3]= $recordData->name.' '.$recordData->lastname;
                    $recordListing[$i][4]= $recordData->email;
                    // $recordListing[$i][5]= $recordData->marchant_id;
                    // $recordListing[$i][6]= $recordData->clover_key;
                    // $recordListing[$i][7]= $recordData->access_token;
                    // $recordListing[$i][5]= $recordData->mobile;
                    // $recordListing[$i][6]= (!empty($recordData->country) ? $recordData->country:'');
                    // $recordListing[$i][7]= (!empty($recordData->state) ? $recordData->state:'');
                    // $recordListing[$i][8]= (!empty($recordData->city) ? $recordData->city:'');
                    // $recordListing[$i][9]= (!empty($recordData->address) ? $recordData->address:'');
                    // $recordListing[$i][6]= (!empty($recordData->gender) ? ucfirst($recordData->gender) :'');
                    // $recordListing[$i][7]= (!empty($recordData->date_of_birth) ? $recordData->date_of_birth:'');
                    $recordListing[$i][5]= get_formated_date($recordData->create_dt, 2);
                   
                    $table = TABLE_USERS;
                    $field = 'status';
                    $urls  =  base_url('admin/StudioOwner/updateStatus');
                    $actionContent='';
                    if(check_permission(STATUS,"owner_list")==1){
                      if($recordData->email_verified == "0" ){
                          $verify_status='1';
                        }else{
                            $verify_status='0';
                        }
                        if($recordData->email_verified == "0"){
                           $user_status = "Deactive";
                           $id='';
                            $actionContent .='<a class="btn btn-danger waves-effect btn-width"  href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\',\''.$id.'\',\''.$verify_status.'\');" title="'.$this->lang->line('inactive').'">'.$this->lang->line('inactive').'</a>';

                        }else{
                            if($recordData->status == "Deactive"){
                                $user_status = "Active";
                                $actionContent .='<a class="btn btn-danger waves-effect btn-width" href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('inactive').'">'.$this->lang->line('inactive').'</a>';
                            }else{ 
                                $user_status = "Deactive";
                                $actionContent .='<a class="btn btn-active waves-effect btn-width" href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('active').'">'.$this->lang->line('active').'</a>';
                            }
                        }
                  }
                    $recordListing[$i][6]= $actionContent; 
                    //blank for edit button
                    $actionContent = '';
                     if(check_permission(EDIT,"owner_list")==1){
                     $actionContent .='<a href="'.$profile_url.'" title="Edit" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5"><i class="fa fa-edit"></i></a> '; 
                     }
                    $recordListing[$i][7]= $actionContent;
                    $i++;
                    $srNumber++;
                }
          
            $content .= ']';
            $final_data = json_encode($recordListing);
        } else {
            $final_data = '[]';
        }   
                
        echo '{"draw":'.$draw.',"recordsTotal":'.$totalRecord.',"recordsFiltered":'.$totalRecord.',"data":'.$final_data.'}';
    }


    /* Block and approved user by admin */
    public function updateStatus(){
        $returnData = false;
        $userId = $this->input->post('ids');
        $IdField = $this->input->post('idField') ? $this->input->post('idField') : "id";
        $userStatus = $this->input->post('status');
        $table = $this->input->post('table');
        $field = $this->input->post('field');

        if ((!empty($userId)) && (!empty($table))) {
            $upWhere = array($IdField => $userId);
            $updateData = array($field => $userStatus);
            $this->studio_model->updateDataFromTabel($table, $updateData, $upWhere);
            $returnData = array('isSuccess' => true);
        } else {
            $returnData = array('isSuccess' => false);
        }
        echo json_encode($returnData);
    }

    /* Show Profile info */
    public function userprofile($user_id=''){
        check_permission(EDIT,"owner_list",1);
        $uid =  decode($user_id);
        if(!empty($user_id) && !empty($uid)){
            $loguserinfo['userinfo'] = $this->dynamic_model->get_user($uid);
            $header['title'] = $this->lang->line('btn_update_profile');
            $this->admintemplates('studio_owner/owner-profile-update', $loguserinfo, $header);
        } else{
            redirect(base_url('admin/StudioOwner'));
        }
    }
    /* User Profile update by admin */
    public function userProfileUpdate(){
        check_permission(EDIT,"owner_list",1);
        extract($this->input->post());
        $allowedExts = array("JPG","JPEG","PNG","png","jpeg","jpg");
        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('updatename', 'updatefullname', 'required', array( 'required' => $this->lang->line('tb_full_name')));
            $this->form_validation->set_rules('updatelastname', 'updatelastnm', 'required', array( 'required' => $this->lang->line('last_name')));
            
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/StudioOwner/userProfile/'.encode($updateid));
            } else {
                $updatedata = array();
                $userid = $updateid;
                $updatedata = array();
                $file_ext = pathinfo($_FILES["updateuserpic"]["name"], PATHINFO_EXTENSION);
                //print_r($_FILES);die;
                if (!empty($_FILES['updateuserpic']['name'])) {
                    // check for valid file to upload 
                    if(!in_array($file_ext, $allowedExts)){
                        $this->session->set_flashdata('updateclass', 'danger');
                        $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                        redirect(site_url().'admin/StudioOwner/userProfile/'.encode($updateid));     
                    }
                    $img_name = $this->dynamic_model->fileupload('updateuserpic', 'uploads/user', 'Picture');
                } else {
                    $img_name = $this->input->post('oldpic');
                }
                $updatedata['name'] = $updatename;
                $updatedata['lastname'] = $updatelastname;
                $updatedata['profile_img'] = $img_name;
                $updatedata['marchant_id'] = $marchant_id;
                $updatedata['clover_key'] = $clover_key;
                $updatedata['access_token'] = $access_token;
                //$updatedata['cad_marchant_id'] = $cad_marchant_id;
                $updatedata['marchant_id_type'] = $merchant_type;
                 if($email_verified==1){
                 $updatedata['email_verified'] = 1;
                }

                $this->dynamic_model->updatedata(TABLE_USERS, $updatedata, $userid);
                //echo $this->db->last_query(); exit();
                 
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('profile_update'));
                redirect(site_url().'admin/StudioOwner/userProfile/'.encode($updateid));  
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProble in Server. Please Try Again');
            redirect(site_url().'admin/StudioOwner/userProfile/'.encode($updateid));                    
        }
        $this->admintemplates('profile');
    }


    function exportCsvBusiness(){ 

        $file      =  'Business-List';
        $filename = $file."-".date("Y-m-d",time());

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'.csv"');

        $userdata= $this->session->userdata('search_data');

        $header  = array(
                'S. No.',
                'Full Name',
                'Business Name',
                'Email',
                'Phone',
                'Country',
                'State',
                'City',
                'Address',
                'Business Area (Sq.)',
                'Service Type',
                'Business Type',
                'Created Date',
                'Status'
        );    

        $fp = fopen('php://output', 'wb');
        fputcsv($fp, $header);

        foreach ($userdata as $key => $value) {
            $array = array($key+1);

            array_push($array, $value["name"]." ".$value["lastname"]);
            array_push($array, $value["business_name"]);
            array_push($array, $value["primary_email"]);
            array_push($array, $value["business_phone"]);
            array_push($array, (!empty($value["country"]) ? $value["country"]:''));
            array_push($array, (!empty($value["state"]) ? $value["state"]:''));
            array_push($array, (!empty($value["city"]) ? $value["city"]:''));
            array_push($array, (!empty($value["address"]) ? $value["address"]:''));
            array_push($array, (!empty($value["area"]) ? $value["area"]:''));
            array_push($array, (!empty($value["service_type"]) ? get_services_type_name($value["service_type"]):''));
            array_push($array, (!empty($value["business_type"]) ? get_business_type_name($value["business_type"]):''));
            array_push($array, get_formated_date($value["create_dt"]));
            array_push($array, ($value["status"]=="Deactive" ? $value["status"]:'Active'));

            fputcsv($fp, $array);
        }

        fclose($fp);

    }


    function exportCsvOwners(){

        $file      =  'Owners-List';
        $filename = $file."-".date("Y-m-d",time());

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'.csv"');

        $userdata= $this->session->userdata('search_data');

        $header  = array(
                'S. No.',
                'Full Name',
                'Email',
                'Created Date',
                'Status'
        );    

        $fp = fopen('php://output', 'wb');
        fputcsv($fp, $header);

        foreach ($userdata as $key => $value) {
            $array = array($key+1);

            array_push($array, $value["name"]." ".$value["lastname"]);
            array_push($array, $value["email"]);
            array_push($array, get_formated_date($value["create_dt"]));
            array_push($array, ($value["status"]=="Deactive" ? $value["status"]:'Active'));

            fputcsv($fp, $array);
        }

        fclose($fp);

    }


     public function exportUsercsv($file_type=''){
        $search_data= $this->session->userdata('search_data');
        $getRecordListing = json_decode(json_encode($search_data),true);
        //Code for CSV output
        $csvOutput = "";
        $file      =  'User-List';
        // $csvOutput .=  'User List';
        // $csvOutput .= "\n";
        $header  = array(
        'Full Name',
        'Email',
        'Country Code',
        'Phone',
        'Created Date',
        'Last Login',
        'Status'
       );    
        //Code for make header of CSV file
        for($head=0; $head<count($header); $head++)
        {
            $csvOutput .= $header[$head].",";
        }
        
        $csvOutput .= "\n";

        //Code for make rows of CSV file
        foreach($getRecordListing as $key => $recordData){
            $country_code= (!empty($recordData['country_code']) ? $recordData['country_code'] :'');
           if($recordData['admin_status']=="Approve"){
                $admin_status="Approved";
            }elseif($recordData['admin_status']=="Block"){
                $admin_status="Blocked";
            }else{
                $admin_status="Unapproved";
            }
            $csvOutput .= ucfirst($recordData['name'].''.$recordData['lastname']).",";   
            $csvOutput .= $recordData['email'].",";     
            $csvOutput .= $country_code.",";
            $csvOutput .= $recordData['mobile'].",";   
            $csvOutput .= get_formated_date($recordData['created_on']).",";
            $csvOutput .= get_formated_date($recordData['created_on']).",";
            $csvOutput .= $admin_status.",";
            $csvOutput .= "\n";
        }

        $filename = $file."-".date("Y-m-d",time());

        // header('Content-Type: application/csv');
        // header('Content-Disposition: attachment; filename="filename.csv"');

        header('Content-Type: text/csv; charset=utf-8');
        header("Content-type: application/vnd.ms-excel");
        header("Content-disposition: csv" . date("Y-m-d") . ".".$file_type);
        header("Content-disposition: filename=".$filename.".".$file_type);
        print chr(255) . chr(254).mb_convert_encoding($csvOutput, 'UTF-16LE', 'UTF-8');
        //print $csvOutput;
        exit;
    }
   /*
    *  @access: public
    *  @Description: This method is used for profile
    *  @auther: 
    *  @return: void
    */ 
    public function block_user(){
        check_permission(STATUS,"owner_list",1);
        extract($this->input->post());
            $this->form_validation->set_rules('ids', 'ids', 'required', array( 'required' => 'Please select user'));
         
            if($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/StudioOwner');
            }else{
                $ids = $this->input->post('ids'); 
                $where = 'id IN ('.$ids.')';
                $userdata=$this->dynamic_model->getdatafromtable(TABLE_USERS,$where); 
                if(!empty($userdata)){
                    foreach($userdata as $value){
                        if($value['email_verified'] == "0"){
                        $updatedata['status'] = 'Deactive';
                        }else{
                            $updatedata['status'] = 'Active';
                        } 
                        $condition= array('id'=>$value['id']);
                        $this->dynamic_model->updateRowWhere(TABLE_USERS,$condition,$updatedata);
                    } 
                    $this->session->set_flashdata('updateclass', 'success');
                    $this->session->set_flashdata('updateerror', $this->lang->line('user_active'));
                     redirect(site_url().'admin/StudioOwner'); 
                }else{
                    $this->session->set_flashdata('updateclass', 'danger');
                    $this->session->set_flashdata('updateerror', $this->lang->line('record_not_found'));
                     redirect(site_url().'admin/StudioOwner'); 
                }  
            }           
        }
  /*
    *  @access: public
    *  @Description: This method is used for profile
    *  @auther: 
    *  @return: void
    */ 
         
    public function unblock_user(){
           check_permission(STATUS,"owner_list",1);
           extract($this->input->post());
            $this->form_validation->set_rules('ids', 'ids', 'required', array( 'required' => 'Please select user'));
         
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/StudioOwner');
            } else {
                $ids = $this->input->post('ids'); 
                $where = 'id IN ('.$ids.')';
                $updatedata['status'] = 'Deactive';
                $this->dynamic_model->updateRowWhere(TABLE_USERS, $where ,$updatedata);
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('user_deactive'));
                 redirect(site_url().'admin/StudioOwner');  
            }           
    }

    public function business_trainers_list($bid){
        check_permission(VIEW,"instructor_list",1);
        $header['title'] = $this->lang->line('tb_assigned_instructor'); 
        $data['userdata']=$this->studio_model->get_business_trainers_ids($bid); 
        $this->session->set_userdata("bid",decode($bid));     
        $this->admintemplates('studio_owner/business_trainers_list',$data, $header);
    }

    public function business_classes_list($bid){ 
        // check_permission(VIEW,"instructor_list",1);
        $header['title'] = $this->lang->line('tb_business_classes'); 
        // $data['userdata']=$this->studio_model->get_business_trainers_ids($bid); 
        $data['userdata']=array(); 
        $this->session->set_userdata("bid",decode($bid));     
        $this->admintemplates('studio_owner/business_classes_list',$data, $header);
    }

    public function business_classes_detail($cid){ 
        // check_permission(VIEW,"instructor_list",1);
        $header['title'] = $this->lang->line('tb_class_scheduling_time'); 
        // $data['userdata']=$this->studio_model->get_business_trainers_ids($bid); 
        $data['userdata']=array(); 
        $this->session->set_userdata("cid",decode($cid));     
        $this->admintemplates('studio_owner/business_classes_detail',$data, $header);
    }

    public function business_classes_user_attendee($sid){ 
        // check_permission(VIEW,"instructor_list",1);
        $header['title'] = $this->lang->line('tb_class_scheduling_time'); 
        // $data['userdata']=$this->studio_model->get_business_trainers_ids($bid); 
        $data['userdata']=array(); 
        $this->session->set_userdata("sid",decode($sid));     
        $this->admintemplates('studio_owner/business_classes_user_attendee',$data, $header);
    }


    function exportCsvBusinessClasses(){

        $file      =  'Business-Class-List';
        $filename = $file."-".date("Y-m-d",time());

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'.csv"');

        $userdata= $this->session->userdata('search_data');

        $header  = array(
                'S. No.',
                'Class Name',
                'Start Date',
                'End Date',
                'Form Time',
                'To Time',
                'Class Category',
                'Class Duration (in minutes)',
                'Class Capacity',
                'Class Description',
                'Class Location',
                'Status'
        );    

        $fp = fopen('php://output', 'wb');
        fputcsv($fp, $header);


        foreach ($userdata as $key => $value) {
            $array = array($key+1);

            array_push($array, $value["class_name"]); 
            array_push($array, ($value["start_date"]!="" && $value["start_date"]!=0) ? get_formated_date($value["start_date"]) :'');
            array_push($array, ($value["end_date"]!="" && $value["end_date"]!=0) ? get_formated_date($value["end_date"]) :''); 
            array_push($array, ($value["from_time"]!="" && $value["from_time"]!=0) ? explode("-", get_formated_date($value["from_time"]) )[2]:'');
            array_push($array, ($value["to_time"]!="" && $value["to_time"]!=0) ? explode("-", get_formated_date($value["to_time"]) )[2]:'');
            array_push($array, (!empty($value["class_category"]) ? $value["class_category"]:''));
            array_push($array, (!empty($value["duration"]) ? $value["duration"]:''));
            array_push($array, (!empty($value["capacity"]) ? $value["capacity"]:''));
            array_push($array, (!empty($value["description"]) ? $value["description"]:''));
            array_push($array, (!empty($value["location"]) ? $value["location"]:''));
            array_push($array, ($value["status"]=="Deactive" ? $value["status"]:'Active'));

            fputcsv($fp, $array);
        }

        fclose($fp);

    }


    
    public function business_workshops_list($bid){
        // check_permission(VIEW,"instructor_list",1);
        $header['title'] = $this->lang->line('tb_business_workshops'); 
        // $data['userdata']=$this->studio_model->get_business_trainers_ids($bid); 
        $data['userdata']=array(); 
        $this->session->set_userdata("bid",decode($bid));     
        $this->admintemplates('studio_owner/business_workshops_list',$data, $header);
    }
 

    function exportCsvBusinessWorkshops(){

        $file      =  'Business-Workshop-List';
        $filename = $file."-".date("Y-m-d",time());

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'.csv"');

        $userdata= $this->session->userdata('search_data');

        $header  = array(
                'S. No.',
                'Workshop Name',
                'Workshop Id',
                'Workshop Category',
                'Start Date',
                'End Date',
                'Form Time',
                'To Time',
                'Workshop Duration (in minutes)',
                'Workshop No. Of Days',
                'Workshop Capacity',
                'Workshop Description',
                'Workshop Location',
                'Status'
        );    

        $fp = fopen('php://output', 'wb');
        fputcsv($fp, $header);


        foreach ($userdata as $key => $value) {
            $array = array($key+1);

            array_push($array, $value["workshop_name"]); 
            array_push($array, $value["workshop_id"]); 
            array_push($array, $value["workshop_category"]); 
            array_push($array, ($value["start_date"]!="" && $value["start_date"]!=0) ? get_formated_date($value["start_date"]) :'');
            array_push($array, ($value["end_date"]!="" && $value["end_date"]!=0) ? get_formated_date($value["end_date"]) :''); 
            array_push($array, ($value["from_time"]!="" && $value["from_time"]!=0) ? explode("-", get_formated_date($value["from_time"]) )[2]:'');
            array_push($array, ($value["to_time"]!="" && $value["to_time"]!=0) ? explode("-", get_formated_date($value["to_time"]) )[2]:'');
            array_push($array, (!empty($value["duration"]) ? $value["duration"]:''));
            array_push($array, (!empty($value["no_of_days"]) ? $value["no_of_days"]:''));
            array_push($array, (!empty($value["capacity"]) ? $value["capacity"]:''));
            array_push($array, (!empty($value["description"]) ? $value["description"]:''));
            array_push($array, (!empty($value["location"]) ? $value["location"]:''));
            array_push($array, ($value["status"]=="Deactive" ? $value["status"]:'Active'));

            fputcsv($fp, $array);
        }

        fclose($fp);

    }


    
    public function business_list(){
        check_permission(VIEW,"business_list",1);
        $header['title'] = $this->lang->line('business_list');     
        $this->admintemplates('studio_owner/business_list',[], $header);
    }    
    public function businessAjaxlist(){
        $start         =  $this->input->get('start'); // get promo code Id
        $length        =  $this->input->get('length'); // get promo code Id
        $draw          =  $this->input->get('draw'); // get promo code Id
        $order   =  $this->input->get('order');
        if(!empty($order)){ 
            if($order[0]['column']==2){
                $column_name='name';
            }
            else if($order[0]['column']==3){
                $column_name='business_name';
            }else if($order[0]['column']==5){
                $column_name='primary_email';
            }else if($order[0]['column']==6){
                $column_name='business_phone';                
            }
            else if($order[0]['column']==7){
                $column_name='country';                
            }else if($order[0]['column']==8){
                $column_name='state';                
            }else if($order[0]['column']==9){
                $column_name='city';                
            }
            else{
                $column_name='id';
            }
        }
        
        $totalRecord      = $this->studio_model->businessAjaxlist(true);
        $getRecordListing = $this->studio_model->businessAjaxlist(false,$start,$length,$column_name,$order[0]['dir']);
        // echo'<pre>';
        // print_r($getRecordListing);
        // die;
        $recordListing = array();
        $content='[';
        $actionContent = '';
        $i=0;       
        $srNumber=$start;       
        if(!empty($getRecordListing)){
            foreach($getRecordListing as $recordData) {
                    $login_user_id = encode($recordData->id);
                    $profile_url = base_url('admin/StudioOwner/userprofile/').$login_user_id;                    
                    $recordListing[$i][0]=   '<input type="checkbox" name="checkAll[]" value="'.($recordData->id).'" class="cb-element" ">';
                    $recordListing[$i][1]=  $srNumber+1;
                  
                    $recordListing[$i][2]= $recordData->name.' '.$recordData->lastname;
                    $recordListing[$i][3]= $recordData->business_name;
                    $businesss_pic = !empty($recordData->business_image) ? $recordData->business_image : "building.png";
                    $recordListing[$i][4]= '<img src="'.base_url('uploads/business/'.$businesss_pic).'" width="40" height="40">';
                    
                    $recordListing[$i][5]= $recordData->primary_email;
                    $recordListing[$i][6]= $recordData->business_phone;
                    $recordListing[$i][7]= (!empty($recordData->country) ? $recordData->country:'');
                    $recordListing[$i][8]= (!empty($recordData->state) ? $recordData->state:'');
                    $recordListing[$i][9]= (!empty($recordData->city) ? $recordData->city:'');
                    $recordListing[$i][10]= (!empty($recordData->address) ? $recordData->address:'');
                    // $recordListing[$i][11]=  get_business_category($recordData->category);
                    $recordListing[$i][11]= (!empty($recordData->area) ? $recordData->area:'');
                    $recordListing[$i][12]= (!empty($recordData->service_type)) ? get_services_type_name($recordData->service_type) :'';
                    $recordListing[$i][13]= (!empty($recordData->business_type)) ? get_business_type_name($recordData->business_type) :'';
                    $recordListing[$i][14]= get_formated_date($recordData->create_dt, 2);
                    $viewurls  =  base_url('admin/StudioOwner/business_trainers_list/'.encode($recordData->id));
                    $recordListing[$i][15]= '<a class="btn btn-active waves-effect btn-width" href="'.$viewurls.'">Show Instructors</a>';
                    $viewurls  =  base_url('admin/StudioOwner/business_classes_list/'.encode($recordData->id));
                    $recordListing[$i][16]= '<a class="btn btn-active waves-effect btn-width" href="'.$viewurls.'">Show Business Classes</a>';
                    $viewurls  =  base_url('admin/StudioOwner/business_workshops_list/'.encode($recordData->id));
                    // $recordListing[$i][17]= '<a class="btn btn-active waves-effect btn-width" href="'.$viewurls.'">Show Workshops</a>';
                    $table = TABLE_BUSINESS;
                    $field = 'status';
                    $urls  =  base_url('admin/StudioOwner/updateStatus');
                    $actionContent='';
                    if(check_permission(STATUS,"business_list")==1){
                    if($recordData->status == "Deactive"){
                        $status = "Active";
                        $actionContent .='<a class="btn btn-danger waves-effect btn-width" href="javascript:void(0);" onclick="changestatus('.$recordData->id.', \''.$status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('inactive').'">'.$this->lang->line('inactive').'</a>';
                    }else { 
                        $status = "Deactive";
                        $actionContent .='<a class="btn btn-active waves-effect btn-width" href="javascript:void(0);" onclick="changestatus('.$recordData->id.', \''.$status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('active').'">'.$this->lang->line('active').'</a>';
                    }
                }
                    $recordListing[$i][17]= $actionContent;
                    //blank for edit button
                    $actionContent = '';
                    $viewurls  =  base_url('admin/StudioOwner/view_business_details/'.encode($recordData->id));
                    $actionContent .='<a href="'.$viewurls.'" title="View" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5"><i class="fa fa-lg fa-eye"></i></a> '; 
                    $recordListing[$i][18]= $actionContent; 
                   
                    $i++;
                    $srNumber++;
                }
          
            $content .= ']';
            $final_data = json_encode($recordListing);
        } else {
            $final_data = '[]';
        }   
                
        echo '{"draw":'.$draw.',"recordsTotal":'.$totalRecord.',"recordsFiltered":'.$totalRecord.',"data":'.$final_data.'}';
    }
    public function view_business_details($business_id=''){
        check_permission(VIEW,"business_list",1);
        $header['title'] = $this->lang->line('business_deatils'); 
        $data['business_data']=$this->studio_model->get_business_details($business_id);      
        $this->admintemplates('studio_owner/view_business_details',$data, $header);
    }
    /*
    *  @access: public
    *  @Description: This method is business status change
    *  @auther: 
    *  @return: void
    */ 
    public function business_status_change($status=''){
        check_permission(STATUS,"business_list",1);
        extract($this->input->post());
            $this->form_validation->set_rules('ids', 'ids', 'required', array( 'required' => 'Please select business'));
         
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/StudioOwner/business_list');
            } else {
                $ids = $this->input->post('ids'); 
                $where = 'id IN ('.$ids.')';
                if(!empty(decode($status)==1)){
                $updatedata['status'] = 'Active';
                $msg= $this->lang->line('business_active');
                }else{
                $updatedata['status'] = 'Deactive';
                 $msg= $this->lang->line('business_deactive');
                }
                
                $this->dynamic_model->updateRowWhere(TABLE_BUSINESS, $where ,$updatedata);
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror',$msg);
                redirect(site_url().'admin/StudioOwner/business_list');  
            }                                                       
        }


        public function business_class_attendence_users($bid,$cid){
            // check_permission(VIEW,"instructor_list",1);
            $header['title'] = $this->lang->line('tb_business_class_users'); 
            // $data['userdata']=$this->studio_model->get_business_trainers_ids($bid); 
            $data['userdata']=array(); 
            $arr = array(
                "bid" => decode($bid),
                "cid" => decode($cid)
            );
            $this->session->set_userdata("attendence_param",$arr);     
            $this->admintemplates('studio_owner/business_class_users_list',$data, $header);
        }



    function exportCsvBusinessClassUsers(){

        $file      =  'Workshop-Users-List';
        $filename = $file."-".date("Y-m-d",time());

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'.csv"');

        $userdata= $this->session->userdata('search_data');

        $header  = array(
                'S. No.',
                "User's Full Name",
                'Class Name',
                'Form Time',
                'To Time',
                "User's Attendence Status"
        );    

        $fp = fopen('php://output', 'wb');
        fputcsv($fp, $header);


        foreach ($userdata as $key => $value) {
            $array = array($key+1);

            array_push($array, $value["name"]." ".$value["lastname"]); 
            array_push($array, $value["class_name"]); 
            array_push($array, ($value["from_time"]!="" && $value["from_time"]!=0) ? explode("-", get_formated_date($value["from_time"]) )[2]:'');
            array_push($array, ($value["to_time"]!="" && $value["to_time"]!=0) ? explode("-", get_formated_date($value["to_time"]) )[2]:'');
            array_push($array, ($value["attendance_status"]=="Deactive" ? $value["attendance_status"]:'Active'));

            fputcsv($fp, $array);
        }

        fclose($fp);

    }




        public function business_workshop_attendence_users($bid,$cid){
            // check_permission(VIEW,"instructor_list",1);
            $header['title'] = $this->lang->line('tb_business_workshop_users'); 
            // $data['userdata']=$this->studio_model->get_business_trainers_ids($bid); 
            $data['userdata']=array(); 
            $arr = array(
                "bid" => decode($bid),
                "cid" => decode($cid)
            );
            $this->session->set_userdata("attendence_param",$arr);     
            $this->admintemplates('studio_owner/business_workshop_users_list',$data, $header);
        } 



    function exportCsvBusinessWorkshopUsers(){

        $file      =  'Workshop-Users-List';
        $filename = $file."-".date("Y-m-d",time());

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'.csv"');

        $userdata= $this->session->userdata('search_data');

        $header  = array(
                'S. No.',
                "User's Full Name",
                'Workshop Name',
                'Form Time',
                'To Time',
                "User's Attendence Status"
        );    

        $fp = fopen('php://output', 'wb');
        fputcsv($fp, $header);


        foreach ($userdata as $key => $value) {
            $array = array($key+1);

            array_push($array, $value["name"]." ".$value["lastname"]); 
            array_push($array, $value["workshop_name"]); 
            array_push($array, ($value["from_time"]!="" && $value["from_time"]!=0) ? explode("-", get_formated_date($value["from_time"]) )[2]:'');
            array_push($array, ($value["to_time"]!="" && $value["to_time"]!=0) ? explode("-", get_formated_date($value["to_time"]) )[2]:'');
            array_push($array, ($value["attendance_status"]=="Deactive" ? $value["attendance_status"]:'Active'));

            fputcsv($fp, $array);
        }

        fclose($fp);

    }





}
