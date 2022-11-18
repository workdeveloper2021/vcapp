<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Permission extends My_Controller {
    private $login_user_id = null;
    public function __construct(){      
        parent::__construct();      
        $this->load->model('dynamic_model');
        $this->load->model('admin_model');
        $this->load->model('permission_model');
        $this->lang->load("admin_message", "english");
        if($this->session->userdata('logged_in')){
            $currentuser = getuserdetails();
            $this->login_user_id = $currentuser['id'];
        }
    }          
    /* Staff List*/
    public function index(){
        check_permission(VIEW,"",1);
        $header['title'] = $this->lang->line('staff_list'); 
        $data['userdata']=$this->dynamic_model->getdatafromtable(TABLE_USERS,"role_id not in(1,2,3,4)",'id');       
        $this->admintemplates('permission/staff_list',$data, $header);
    }
     /* Staff List Ajax*/
    public function staffAjaxlist(){
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
        $totalRecord      = $this->permission_model->staffAjaxlist(true);
        $getRecordListing = $this->permission_model->staffAjaxlist(false,$start,$length, $column_name, $order[0]['dir']);
        // echo'<pre>';
        // print_r($getRecordListing);
        // die;
        $recordListing = array();
        $actionContent = '';
        $content='[';
        $i=0;       
        $srNumber=$start;       
        if(!empty($getRecordListing)) {
            foreach($getRecordListing as $recordData) {
                    $user_id = encode($recordData->id);
                    $profile_url = base_url('admin/permission/staff_profile/').$user_id;                 
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
                    $recordListing[$i][5]= $recordData->mobile;
                    $recordListing[$i][6]= get_formated_date($recordData->create_dt, 2);
                   
                    $table = TABLE_USERS;
                    $field = 'status';
                    $actionContent = '';
                    $urls  =  base_url('admin/permission/updateStatus');
                    if($recordData->status == "Deactive"){
                        $user_status = "Active";
                        $actionContent .='<a class="btn btn-danger waves-effect" style="width: 90%;" href="javascript:void(0);" onclick="changestatus('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('inactive').'">'.$this->lang->line('inactive').'</a>';
                    }else { 
                        $user_status = "Deactive";
                        $actionContent .='<a class="btn btn-active waves-effect" style="width: 90%;" href="javascript:void(0);" onclick="changestatus('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('active').'">'.$this->lang->line('active').'</a>';
                    }
                    $recordListing[$i][7]= $actionContent; 
                    //blank for edit button
                    $actionContent = '';
                    $permission_url= base_url('admin/permission/permission_setting/').$user_id;
                    $actionContent .='<a href="'.$profile_url.'" title="Edit" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5"><i class="fa fa-edit"></i></a> '; 
                    $actionContent .='<a href="'.$permission_url.'" title="Set Permission" class="btn btn-primary"><i class="fa fa-folder-open"></i> Set Permission</a> '; 
                    $recordListing[$i][8]= $actionContent;
                   
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

        if ((!empty($userId)) && (!empty($table))){
            $upWhere = array($IdField => $userId);
            $updateData = array($field => $userStatus);
            $this->dynamic_model->updateRowWhere($table,$upWhere,$updateData);
            //echo $this->db->last_query();die;
            $returnData = array('isSuccess' => true);
        } else {
            $returnData = array('isSuccess' => false);
        }
        echo json_encode($returnData);
    }

    /* Add and Update Profile info */
    public function staff_profile($user_id=''){
        check_permission(VIEW,"",1);
        $header['title'] =  (!empty($user_id)) ? $this->lang->line('btn_update_profile') : $this->lang->line('btn_add_profile');
        $uid =  decode($user_id);
        $data['userinfo'] = $this->dynamic_model->get_user($uid);
        $data['roledata']=$this->dynamic_model->getdatafromtable(TABLE_MANAGE_ROLES,"id not in(1,2,3,4)");   
        $this->admintemplates('permission/staff_profile',$data,$header);  
    }
    /* User Profile update by admin */
    public function userProfileSubmit(){
        check_permission(VIEW,"",1);
        extract($this->input->post());
        $allowedExts = array("gif","jpg","png");
        $is_submit = $this->input->post('is_submit');
        $userid = decode($updateid);
        $userData= $this->dynamic_model->get_user($userid);
        $redirect_url=(empty($updateid)) ? site_url().'admin/permission/staff_profile' : site_url().'admin/permission/staff_profile/'.$updateid; 
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('name', 'fullname', 'required', array( 'required' => $this->lang->line('tb_full_name')));
            $this->form_validation->set_rules('lastname', 'lastnm', 'required', array( 'required' => $this->lang->line('last_name')));
            if(empty($userData)){
            $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[user.email]');
            $this->form_validation->set_rules('phone', 'Phone', 'required|regex_match[/^[1-9]{1}[0-9]+/]|min_length[8]|max_length[12]|numeric|is_unique[user.mobile]');
            }
            if($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect($redirect_url);
            }else{
                $updatedata = $insertdata=array();
                $file_ext = pathinfo($_FILES["userpic"]["name"], PATHINFO_EXTENSION);
                if(!empty($_FILES['userpic']['name'])){
                    // check for valid file to upload 
                    $file_ext=strtolower($file_ext);
                    if(!in_array($file_ext, $allowedExts)){
                        $this->session->set_flashdata('updateclass', 'danger');
                        $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                        redirect($redirect_url);  
                    }
                    $img_name = $this->dynamic_model->fileupload('userpic','uploads/user','Picture');
                } else {
                    $img_name = $this->input->post('oldpic');
                }
                $time=time();
                if(empty($userData)){
                    $insertdata['name'] = $name;
                    $insertdata['lastname'] = $lastname;
                    $insertdata['password'] = encrypt_password($password);
                    $insertdata['mobile'] = $phone;
                    $insertdata['email'] = $email;
                    $insertdata['role_id'] = $roles;
                    $insertdata['profile_img'] = $img_name;
                    $insertdata['create_dt'] = $time;
                    $insertdata['update_dt'] = $time;
                    $this->dynamic_model->insertdata(TABLE_USERS,$insertdata);
                    $this->session->set_flashdata('updateclass', 'success');
                    $this->session->set_flashdata('updateerror', $this->lang->line('profile_add'));
                    redirect($redirect_url);  
                }else{
                    $updatedata['name'] = $name;
                    $updatedata['lastname'] = $lastname;
                    $updatedata['role_id'] = $roles;
                    $updatedata['profile_img'] = $img_name;
                    $updatedata['update_dt'] = $time;
                    $this->dynamic_model->updateRowWhere(TABLE_USERS,array("id"=>$userid),$updatedata);
                    $this->session->set_flashdata('updateclass', 'success');
                    $this->session->set_flashdata('updateerror', $this->lang->line('profile_update'));
                    redirect($redirect_url);  
                }      
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProble in Server. Please Try Again');
            redirect($redirect_url);                    
        }  
    }
   /*
    *  @access: public
    *  @Description: This method is used for profile
    *  @auther: 
    *  @return: void
    */ 
    public function block_user(){
        extract($this->input->post());
            $this->form_validation->set_rules('ids', 'ids', 'required', array( 'required' => 'Please select user'));
         
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/permission');
            } else {
                $ids = $this->input->post('ids'); 
                $where = 'id IN ('.$ids.')';
                $userdata=$this->dynamic_model->getdatafromtable(TABLE_USERS,$where); 
                if(!empty($userdata)){
                    $updatedata['status'] = 'Active';
                    $this->dynamic_model->updateRowWhere(TABLE_USERS,$where,$updatedata);  
                    $this->session->set_flashdata('updateclass', 'success');
                    $this->session->set_flashdata('updateerror', $this->lang->line('user_active'));
                     redirect(site_url().'admin/permission'); 
                }else{
                    $this->session->set_flashdata('updateclass', 'danger');
                    $this->session->set_flashdata('updateerror', $this->lang->line('record_not_found'));
                     redirect(site_url().'admin/permission'); 
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
           extract($this->input->post());
            $this->form_validation->set_rules('ids', 'ids', 'required', array( 'required' => 'Please select user'));
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/permission');
            } else {
                $ids = $this->input->post('ids'); 
                $where = 'id IN ('.$ids.')';
                $updatedata['status'] = 'Deactive';
                $this->dynamic_model->updateRowWhere(TABLE_USERS, $where ,$updatedata);
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('user_deactive'));
                 redirect(site_url().'admin/permission');  
            }           
    
    }
    /* Roles List*/
    public function role_list(){
        check_permission(VIEW,"",1);
        $data=$header=array();
        $header['title'] = $this->lang->line('role_list');      
        $this->admintemplates('permission/role_list',$data, $header);
    }
    /* Roles List Ajax */
    public function rolesAjaxlist(){
        $start         =  $this->input->get('start'); // get promo code Id
        $length        =  $this->input->get('length'); // get promo code Id
        $draw          =  $this->input->get('draw'); // get promo code Id
        $order   =  $this->input->get('order');
        if(!empty($order)){ 
            if($order[0]['column']==1){
                $column_name='role_name';
            }else{
                $column_name='id';
            }
        }
        $totalRecord      = $this->permission_model->rolesAjaxlist(true);
        $getRecordListing = $this->permission_model->rolesAjaxlist(false,$start,$length, $column_name,$order[0]['dir']);
        // echo'<pre>';
        // print_r($getRecordListing);
        // die;
        $recordListing = array();
        $content='[';
        $i=0;       
        $srNumber=$start;       
        if(!empty($getRecordListing)) {
            $actionContent = '';
            foreach($getRecordListing as $recordData){
                    $role_id = encode($recordData->id);
                    $url = base_url('admin/permission/roles/').$role_id;
                    $actionContent = ''; // set default empty
                    $content .='[';                    
                    $recordListing[$i][0]=  $srNumber+1;
                    $recordListing[$i][1]= $recordData->role_name;
                    $recordListing[$i][2]= get_formated_date($recordData->create_dt, 2);
                    //blank for edit button
                    $actionContent = '';
                    $actionContent .='<a href="'.$url.'" title="Edit" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5"><i class="fa fa-edit"></i></a> '; 
                    $recordListing[$i][3]= $actionContent;
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
    /* Show Profile info */
    public function roles($role_id=''){
         check_permission(VIEW,"",1);
        $roleid =  decode($role_id);
        $header['title'] =  (!empty($roleid)) ? $this->lang->line('btn_update_role') : $this->lang->line('btn_add_role');
        $data['rolesdata']=$this->dynamic_model->getdatafromtable(TABLE_MANAGE_ROLES,array("id"=>$roleid));       
        $this->admintemplates('permission/add_role',$data,$header);
    }
    /* User Profile update by admin */
    public function roles_submit(){
        check_permission(VIEW,"",1);
        extract($this->input->post());
        $is_submit = $this->input->post('is_submit');
        $redirect_url=(empty($updateid)) ? site_url().'admin/permission/roles' : site_url().'admin/permission/roles/'.$updateid; 
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('role_name', 'Role Name','required',array('required' => $this->lang->line('tb_role_name')));   
            if($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect($redirect_url);
            }else{
                $updatedata = $insertdata=array();
                $time=time();
                $role_id = decode($updateid); 
                if(!empty($role_id)){
                  $updatedata['role_name'] = $role_name;
                  $updatedata['updated_by'] =$this->login_user_id;
                  $updatedata['update_dt'] = $time;
                  $this->dynamic_model->updateRowWhere(TABLE_MANAGE_ROLES,array("id"=>$role_id),$updatedata); 
                  $this->session->set_flashdata('updateclass', 'success');
                  $this->session->set_flashdata('updateerror', $this->lang->line('role_update'));
                  redirect($redirect_url);   
              }else{
                    $insertdata['role_name'] = $role_name;
                    $insertdata['created_by'] =$this->login_user_id;
                    $insertdata['updated_by'] =$this->login_user_id;
                    $insertdata['create_dt'] = $time;
                    $insertdata['update_dt'] = $time;
                    $this->dynamic_model->insertdata(TABLE_MANAGE_ROLES,$insertdata);
                    $this->session->set_flashdata('updateclass', 'success');
                    $this->session->set_flashdata('updateerror', $this->lang->line('role_added'));
                    redirect($redirect_url);   
               }
                
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProble in Server. Please Try Again');
             redirect($redirect_url);                    
        }
        $this->admintemplates('permission/roles');
    }
    /* Add and Update Profile info */
    public function permission_setting($user_id=''){
         check_permission(VIEW,"",1);
        $data=$header=array();
        $header['title'] = $this->lang->line('role_permission');  
        $data['modules']=$this->dynamic_model->getdatafromtable(TABLE_PERMISSION_MODULE);    
        $data['user_id']=$user_id;
        $this->admintemplates('permission/permission_setting',$data, $header);
    }
      /*****************Function index**********************************
    * @type            : Function
    * @function name   : index
    * @description     : Load "permission setting" user interface                 
    *                    and setting user role permission    
    * @param           : null
    * @return          : null 
    * ********************************************************** */
    public function permission_action(){
        check_permission(VIEW,"",1);
        extract($this->input->post());
        $is_submit = $this->input->post('is_submit');
        $updateid = $this->input->post('updateid');
        $redirect_url=(empty($updateid)) ? site_url().'admin/permission/permission_setting' : site_url().'admin/permission/permission_setting/'.$updateid; 
        $user_id = decode($updateid);
        $userData= $this->dynamic_model->get_user($user_id);
        if(isset($is_submit) && $is_submit == 1){

          // echo "<pre>";print_r($_POST);die;
            $this->form_validation->set_rules('operation[]', 'Operation Name','required',array('required' => $this->lang->line('tb_role_name')));   
            if($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect($redirect_url);
            }else{
                //echo count($this->input->post('operation'));die;
                 foreach($this->input->post('operation') AS $key => $value)
                 {
                      $inserdata =$updatedata=array();$time=time();
                      $exist=$this->dynamic_model->getdatafromtable(TABLE_PERMISSION, array('user_id'=>$user_id,'operation_id'=>$key));  
                      if($exist){
                          $updatedata['is_add']          = isset($is_add[$key]) && !empty($is_add[$key]) ? $is_add[$key] : 0;
                          $updatedata['is_edit']         = isset($is_edit[$key]) && !empty($is_edit[$key]) ? $is_edit[$key] : 0;
                          $updatedata['is_status']       = isset($is_status[$key]) && !empty($is_status[$key]) ? $is_status[$key] : 0;
                          $updatedata['is_view']         = isset($is_view[$key]) && !empty($is_view[$key]) ? $is_view[$key] : 0;
                          $updatedata['update_dt']      = $time;
                          $this->dynamic_model->updateRowWhere(TABLE_PERMISSION,array('user_id' =>$user_id,'operation_id'=>$key),$updatedata);  
                          $msg= $this->lang->line('permission_update');    
                      }else{
                          $inserdata['user_id']      = $user_id;
                          $inserdata['operation_id'] = $key;
                          $inserdata['is_add']          = isset($is_add[$key]) && !empty($is_add[$key]) ? $is_add[$key] : 0;
                          $inserdata['is_edit']         = isset($is_edit[$key]) && !empty($is_edit[$key]) ? $is_edit[$key] : 0;
                          $inserdata['is_status']       = isset($is_status[$key]) && !empty($is_status[$key]) ? $is_status[$key] : 0;
                          $inserdata['is_view']         = isset($is_view[$key]) && !empty($is_view[$key]) ? $is_view[$key] : 0;
                          $inserdata['status']          = "Active";
                          $inserdata['create_dt']      = $time;
                          $inserdata['update_dt']      = $time;
                          $this->dynamic_model->insertdata(TABLE_PERMISSION,$inserdata);
                          $msg= $this->lang->line('permission_add');                 
                      }                  
                 }   
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror',$msg);
                redirect($redirect_url);  
                
            }           
        }else{
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProble in Server. Please Try Again');
             redirect($redirect_url);                    
        }
        $this->admintemplates('permission/roles');
    }

}
