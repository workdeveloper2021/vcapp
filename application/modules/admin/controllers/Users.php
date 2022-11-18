<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Users extends My_Controller {
    private $login_user_id = null;
    public function __construct(){      
        parent::__construct();      
        $this->load->model('dynamic_model');
        $this->load->model('admin_model');
        $this->load->model('users_model');
        $this->lang->load("admin_message", "english");
        if($this->session->userdata('logged_in')){
            $currentuser = getuserdetails();
            $this->login_user_id = $currentuser['id'];
        }
    }
            
    public function index() {
        check_permission(VIEW,"user_list",1);
        $header['title'] = $this->lang->line('user_list'); 
        $data['userdata']=$this->dynamic_model->getdatafromtable(TABLE_USERS,array("role_id "=>3),'id');       
        $this->admintemplates('users/users_list',$data, $header);
    }

    public function usersAjaxlist(){
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
                $column_name='create_dt';               
            }else if($order[0]['column']==6){
                $column_name='status';               
            }else{
                $column_name='id';
            }
        }
        $totalRecord      = $this->users_model->usersAjaxlist(true);
        $getRecordListing = $this->users_model->usersAjaxlist(false,$start,$length, $column_name, $order[0]['dir']);
        // print_r($getRecordListing);die();
        // echo'<pre>';
        // print_r($getRecordListing);
        // die;
        $recordListing = array();
        $content='[';
        $i=0;       
        $srNumber=$start;       
        if(!empty($getRecordListing)) {
            $actionContent = '';
            foreach($getRecordListing as $recordData) {
                    $login_user_id = encode($recordData->id);
                    $profile_url = base_url('admin/users/userprofile/').$login_user_id;                    
                    $delete_url = base_url('admin/users/userdelete/').$login_user_id;                    
                    
                    $recordListing[$i][0]=   '<input type="checkbox" name="checkAll[]" value="'.($recordData->id).'" class="cb-element" ">';
                    $recordListing[$i][1]=  $srNumber+1;
                    if(!empty($recordData->profile_img)){
                    $user_pic = base_url('uploads/user/').'/'.$recordData->profile_img;
                     }else{
                         $user_pic = base_url('uploads/user/userdefault.png');
                     }
                    $recordListing[$i][2]= '<img src="'.$user_pic.'" width="40" height="40">';
                    $recordListing[$i][3]= $recordData->name;
                    $recordListing[$i][4]= $recordData->email;
                    $recordListing[$i][5]= get_formated_date($recordData->create_dt, 2);
                   
                    $table = TABLE_USERS;
                    $field = 'status';
                    $urls  =  base_url('admin/users/updateStatus'); 
                    $actionContent='';
                    if(check_permission(STATUS,"user_list")==1){
                        if($recordData->email_verified == "0" ){
                          $verify_status='1';//email not verify
                        }else{
                             $verify_status='0';//verified
                        }
                        if($recordData->email_verified == "0"){
                           $id='';
                             if($recordData->status == "Deactive"){
                                $user_status = "Active";
                                 $actionContent .='<a class="btn btn-danger waves-effect btn-width"  href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\',\''.$id.'\',\''.$verify_status.'\');" title="'.$this->lang->line('inactive').'">'.$this->lang->line('inactive').'</a>';
                            }else{ 
                                $user_status = "Deactive";
                                 $actionContent .='<a class="btn btn-active waves-effect btn-width"  href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\',\''.$id.'\',\''.$verify_status.'\');" title="'.$this->lang->line('active').'">'.$this->lang->line('active').'</a>';
                            }
                        }else{
                            if($recordData->status == "Deactive"){
                                $user_status = "Active";
                                $actionContent .='<a class="btn btn-danger waves-effect btn-width"  href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('inactive').'">'.$this->lang->line('inactive').'</a>';
                            }else{ 
                                $user_status = "Deactive";
                                $actionContent .='<a class="btn btn-active waves-effect btn-width"  href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('active').'">'.$this->lang->line('active').'</a>';
                            }
                        }
                    }
                    $recordListing[$i][6]= $actionContent; 
                    // $recordListing[$i][7]= $actionContent; 
                    //blank for edit button
                    $actionContent = '';
                    // if(check_permission(EDIT,"user_list")==1){
                    $actionContent .='<a href="'.$profile_url.'" title="Edit" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5"><i class="fa fa-edit"></i></a> '; 
                    $actionContent .='<a href="javascript:void(0)" id="'.$delete_url.'" title="Delete" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5 deleteUser"><i class="fa fa-trash"></i></a> '; 

                     // }
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
    public function updateStatus() {
        $returnData = false;
        $userId = $this->input->post('ids');
        $IdField = $this->input->post('idField') ? $this->input->post('idField') : "id";
        $userStatus = $this->input->post('status');
        $table = $this->input->post('table');
        $field = $this->input->post('field');

        if ((!empty($userId)) && (!empty($table))) {
            $upWhere = array($IdField => $userId);
            $updateData = array($field => $userStatus);
            // $this->dynamic_model->updatedata($table,$upWhere, $updateData);
            $this->users_model->updateDataFromTabel($table, $updateData, $upWhere);
            //echo $this->db->last_query();die;
            $returnData = array('isSuccess' => true);
        } else {
            $returnData = array('isSuccess' => false);
        }
        echo json_encode($returnData);
    }


    public function addUser(){

            $header['title'] = $this->lang->line('title_add_user');
            $this->admintemplates('users/add-user', array(), $header);
    }


    /* Show Profile info */
    public function userprofile($user_id=''){
        // check_permission(EDIT,"user_list",1);
        $uid =  decode($user_id);
        if(!empty($user_id) && !empty($uid)){
            $loguserinfo['userinfo'] = $this->dynamic_model->get_user($uid);
            $header['title'] = $this->lang->line('btn_update_details');
            $this->admintemplates('users/profile-update', $loguserinfo, $header);
        } else{
            redirect(base_url('admin/users'));
        }
    }

    /* Show Profile info */
    public function userdelete($user_id=''){
        // check_permission(EDIT,"user_list",1);
        $uid =  decode($user_id);
        if(!empty($user_id) && !empty($uid)){
            $loguserinfo['userinfo'] = $this->dynamic_model->deletedata('user',array('id'=>$uid));
            // $header['title'] = $this->lang->line('btn_update_details');
            redirect(base_url('admin/users'));
            // $this->admintemplates('users/profile-update', $loguserinfo, $header);
        } else{
            redirect(base_url('admin/users'));
        }
    }


    public function userAddSubmit(){
        // check_permission(EDIT,"user_list",1);
        extract($this->input->post());
        $allowedExts = array("JPG","JPEG","PNG","png","jpeg","jpg");
        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('updatename', 'updatefullname', 'required', array( 'required' => $this->lang->line('tb_full_name')));
            // $this->form_validation->set_rules('updatelastname', 'updatelastnm', 'required', array( 'required' => $this->lang->line('last_name')));
            
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/users/addUser');
            } else {
                $updatedata = array();
                $file_ext = pathinfo($_FILES["updateuserpic"]["name"], PATHINFO_EXTENSION);
                if (!empty($_FILES['updateuserpic']['name'])) {
                    // check for valid file to upload 
                    $file_ext=strtolower($file_ext);
                    if(!in_array($file_ext, $allowedExts)){
                        $this->session->set_flashdata('updateclass', 'danger');
                        $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                        redirect(site_url().'admin/users/addUser'); 
                     
                    }
                    $img_name = $this->dynamic_model->fileupload('updateuserpic', 'uploads/user', 'Picture');
                } else {
                    $img_name = 'userdefault.png';
                }
                $updatedata['name'] = $updatename;
                $updatedata['email'] = $updateemail;
                $updatedata['profile_img'] = $img_name;
                $updatedata['role_id'] = 3;
                $updatedata['create_dt'] = time();
                if($email_verified==1){
                 $updatedata['email_verified'] = 1;
                 $updatedata['status'] = 'Active';

                }
                $this->dynamic_model->insertdata(TABLE_USERS, $updatedata); 
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('user_added'));
                redirect(site_url().'admin/users/addUser');  
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProble in Server. Please Try Again');
            redirect(site_url().'admin/users/addUser');                    
        }     
    }




    public function userProfileUpdate(){
        // check_permission(EDIT,"user_list",1);
        extract($this->input->post());
        $allowedExts = array("JPG","JPEG","PNG","png","jpeg","jpg");
        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('updatename', 'updatefullname', 'required', array( 'required' => $this->lang->line('tb_full_name')));
            // $this->form_validation->set_rules('updatelastname', 'updatelastnm', 'required', array( 'required' => $this->lang->line('last_name')));
            
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/users/userProfile/'.encode($updateid));
            } else {
                $updatedata = array();
                $userid = $updateid;
                $file_ext = pathinfo($_FILES["updateuserpic"]["name"], PATHINFO_EXTENSION);
                if (!empty($_FILES['updateuserpic']['name'])) {
                    // check for valid file to upload 
                    $file_ext=strtolower($file_ext);
                    if(!in_array($file_ext, $allowedExts)){
                        $this->session->set_flashdata('updateclass', 'danger');
                        $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                        redirect(site_url().'admin/users/userProfile/'.encode($updateid)); 
                     
                    }
                    $img_name = $this->dynamic_model->fileupload('updateuserpic', 'uploads/user', 'Picture');
                } else {
                    $img_name = $this->input->post('oldpic');
                }
                $updatedata['name'] = $updatename;
                $updatedata['profile_img'] = $img_name;
                if($email_verified==1){
                 $updatedata['email_verified'] = 1;
                }
                $this->dynamic_model->updatedata(TABLE_USERS, $updatedata, $userid); 
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('profile_update'));
                redirect(site_url().'admin/users/userProfile/'.encode($updateid));  
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProble in Server. Please Try Again');
            redirect(site_url().'admin/users/userProfile/'.encode($updateid));                    
        }     
    }





    /* exportUsercsv */
    function exportCsvUsers(){

        $file      =  'User-List';
        $filename = $file."-".date("Y-m-d",time());

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'.csv"');

        $userdata= $this->session->userdata('search_data');

        // $userdata      = $this->users_model->usersCsvList();
        // print_r($userdata); die;


        $header  = array(
                'S. No.',
                'Full Name',
                'Email',
                'Phone',
                'Country',
                'State',
                'City',
                'Address',
                'Gender',
                'DOB',
                'Created Date',
                'Status'
        );    

        $fp = fopen('php://output', 'wb');
        fputcsv($fp, $header);

        foreach ($userdata as $key => $value) {
            $array = array($key+1);

            array_push($array, $value["name"]." ".$value["lastname"]);
            array_push($array, $value["email"]);
            array_push($array, $value["mobile"]);
            array_push($array, (!empty($value["country"]) ? $value["country"]:''));
            array_push($array, (!empty($value["state"]) ? $value["state"]:''));
            array_push($array, (!empty($value["city"]) ? $value["city"]:''));
            array_push($array, (!empty($value["address"]) ? $value["address"]:''));
            array_push($array, (!empty($value["gender"]) ? ucfirst($value["gender"]):''));
            array_push($array, (!empty($value["date_of_birth"]) ? $value["date_of_birth"]:''));
            array_push($array, get_formated_date($value["create_dt"]));
            array_push($array, ($value["status"]=="Deactive" ? $value["status"]:'Active'));

            fputcsv($fp, $array);
        }

        fclose($fp);

    }




    
    /* exportUsercsv */
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
        check_permission(STATUS,"user_list",1);
        extract($this->input->post());
            $this->form_validation->set_rules('ids', 'ids', 'required', array( 'required' => 'Please select user'));
         
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/users');
            } else {
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
                     redirect(site_url().'admin/users'); 
                }else{
                    $this->session->set_flashdata('updateclass', 'danger');
                    $this->session->set_flashdata('updateerror', $this->lang->line('record_not_found'));
                     redirect(site_url().'admin/users'); 

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
           check_permission(STATUS,"user_list",1);
           extract($this->input->post());
            $this->form_validation->set_rules('ids', 'ids', 'required', array( 'required' => 'Please select user'));
         
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/users');
            } else {
                $ids = $this->input->post('ids'); 
                $where = 'id IN ('.$ids.')';
                $updatedata['status'] = 'Deactive';
                $this->dynamic_model->updateRowWhere(TABLE_USERS, $where ,$updatedata);
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('user_deactive'));
                 redirect(site_url().'admin/users');  
            }           
    
    }


}
