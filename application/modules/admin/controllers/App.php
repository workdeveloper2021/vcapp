<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class App extends My_Controller {
	public function __construct(){
        parent::__construct();
        $this->load->model('dynamic_model');
        $this->load->model('app_model');
        $this->lang->load("admin_message", "english");
        if($this->session->userdata('logged_in')){
            $currentuser = getuserdetails();
            $this->login_user_id = $currentuser['id'];
        }
    }
    public function index(){
        //check_permission(VIEW,"page_content",1);
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> '. $this->lang->line('dashboard_breadcrumb') , 'admin'); 
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> '. $this->lang->line('appcontent_breadcrumb') , 'admin/category'); 
       $header['title'] = $this->lang->line('app_content');
       $content_info['about_us'] =  $this->dynamic_model->getdatafromtable(TABLE_STATIC_PAGE,array("slug"=>"about-us"));
       $content_info['privacy_policy'] =  $this->dynamic_model->getdatafromtable(TABLE_STATIC_PAGE,array("slug"=>"privacy-policies"));
       $content_info['terms_condition'] =  $this->dynamic_model->getdatafromtable(TABLE_STATIC_PAGE,array("slug"=>"term-and-condition"));
        $this->admintemplates('app/app_content', $content_info, $header);
    }
    public function contact_us(){
        check_permission(VIEW,"contact_details",1);
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> '. $this->lang->line('dashboard_breadcrumb') , 'admin'); 
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> '. $this->lang->line('appcontent_breadcrumb') , 'admin/category'); 

        $contact_info['contactemail'] = get_options('contactemail');
        $contact_info['contactaddress'] = get_options('contactaddress');
        $contact_info['contactphone'] = get_options('contactphone');
        // $condition = array('phonecode!=' => '');
        // $country_code_arr = getdatafromtable(TABLE_COUNTRY, $condition, 'phonecode');
        
        // if(!empty($country_code_arr)){
        //     $contact_info['country_code'] = $country_code_arr; 
        // }else{
        //     $contact_info['country_code'] = array();
        // }
        $header['title'] = $this->lang->line('app_contact_title');
        $this->admintemplates('app/app_contact', $contact_info, $header);
    }
    public function contact_us_submit(){
        check_permission(EDIT,"contact_details",1);
        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('contact_email', 'Email', 'required');
            $this->form_validation->set_rules('contact_number', 'Number', 'required');
            //$this->form_validation->set_rules('code', 'Code', 'required');
            $this->form_validation->set_rules('contact_address', 'Address', 'required');
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('contactclass', 'danger');
                $this->session->set_flashdata('appcontacterror', validation_errors());
                redirect(site_url().'admin/app/contact_us');
            } else {

                $contact_email = $this->input->post('contact_email');  
                $contact_number = $this->input->post('contact_number');  
                $contact_address = $this->input->post('contact_address');
                // $code = $this->input->post('code');
                $contact_number = $contact_number;
                $result = $this->dynamic_model->updateRowWhere("options", array("option_name" => "contactemail"), array( 'option_value' => $contact_email ));
                $result1 = $this->dynamic_model->updateRowWhere("options", array("option_name" => "contactphone"), array( 'option_value' => $contact_number ));
                $result2 = $this->dynamic_model->updateRowWhere("options", array("option_name" => "contactaddress"), array( 'option_value' => $contact_address ));
                $this->session->set_flashdata('contactclass', 'success');
                $this->session->set_flashdata('appcontacterror', $this->lang->line('succefully_updated'));
                redirect(site_url().'admin/app/contact_us');
            }

        } else {
            $this->session->set_flashdata('contactclass', 'danger');
            $this->session->set_flashdata('appcontacterror', 'Wrong Method. Please Fill this Form');
            redirect(site_url().'admin/app/contact_us');
        }
    }
    public function page_content_submit(){
        check_permission(EDIT,"page_content",1);
        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            //echo TABLE_STATIC_PAGE;die;
                $form_type = $this->input->post('form_type'); 
                if($form_type == "about_us"){
                    $about_us = $this->input->post('about_app');
                    $this->dynamic_model->updateRowWhere(TABLE_STATIC_PAGE, array("slug" => "about-us"), array( 'discription' => $about_us ));
                }
                if($form_type == "app_privacy_policy"){
                    $app_privacy_policy = $this->input->post('app_privacy_policy');
                    $this->dynamic_model->updateRowWhere(TABLE_STATIC_PAGE, array("slug" => "privacy-policies"), array( 'discription' => $app_privacy_policy ));
                } 
                if($form_type == "app_term_us"){
                    $app_term_us = $this->input->post('app_term_us');
                    $this->dynamic_model->updateRowWhere(TABLE_STATIC_PAGE, array("slug" => "term-and-condition"), array( 'discription' => $app_term_us ));
                }
                $this->session->set_flashdata('appcontactclass', "success");
                $this->session->set_flashdata('appcontacterror', $this->lang->line('succefully_updated')); 
                redirect(site_url().'admin/app');
        } else {
            $this->session->set_flashdata('appcontacterror', 'Wrong Method. Please Fill this Form');
            redirect(site_url().'admin/app');
        }
	}
    public function app_settings(){
         check_permission(VIEW,"website_settings",1);
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> '. $this->lang->line('dashboard_breadcrumb') , 'admin'); 
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> '. $this->lang->line('appcontent_breadcrumb') , 'admin/category'); 
        $header['title'] = $this->lang->line('web_setting');
        $content_info['sitetitle'] = get_options("sitetitle");
        $content_info['sitelogo'] = get_options("sitelogo");
        $this->admintemplates('app/app_settings',$content_info,$header);
    }
    public function app_settings_submit(){
        check_permission(EDIT,"website_settings",1);
        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){        
         $site_title = $this->input->post('site_title');
        if(!empty($site_title)){
        $this->dynamic_model->updateRowWhere(TABLE_OPTIONS,array("option_name" => "sitetitle"), array( 'option_value' => $site_title ));
        //echo $this->db->last_query();die;
        }
        //print_r($_FILES);die;
       if(!empty($_FILES['site_logo']['name'])){
        $file_ext = pathinfo($_FILES["site_logo"]["name"], PATHINFO_EXTENSION);
        // check for valid file to upload 
        $allowedExts = array("gif","jpg","png");
        if(!in_array($file_ext, $allowedExts)){
            $this->session->set_flashdata('appsettingclass', 'danger');
            $this->session->set_flashdata('appsettingerror',  $this->lang->line('file_required'));
            redirect(site_url().'admin/app/app_settings/'); 
        }
            $img_name = $this->dynamic_model->fileupload('site_logo','uploads/appImg','Picture');
            $this->dynamic_model->updateRowWhere(TABLE_OPTIONS,array("option_name" => "sitelogo"), array('option_value'=>'uploads/appImg/'.$img_name ));
        } 
        $this->session->set_flashdata('appsettingclass', "success");
        $this->session->set_flashdata('appsettingerror', $this->lang->line('succefully_updated')); 
        redirect(site_url().'admin/app/app_settings');
        } else {
            $this->session->set_flashdata('appcontacterror', 'Wrong Method. Please Fill this Form');
            redirect(site_url().'admin/app/app_settings');
        }
    } 

    
    public function category_list(){
        check_permission(VIEW,"contact_details",1);
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> '. $this->lang->line('dashboard_breadcrumb') , 'admin'); 
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> '. $this->lang->line('appcontent_breadcrumb') , 'admin/category'); 
        $data=array();
        $header['title'] = $this->lang->line('app_category_list');        
        $this->admintemplates('app/category_list',$data, $header);
    } 
    public function categoryAjaxlist(){
        $start         =  $this->input->get('start'); // get promo code Id
        $length        =  $this->input->get('length'); // get promo code Id
        $draw          =  $this->input->get('draw'); // get promo code Id
        $order   =  $this->input->get('order');
        if(!empty($order)){ 
            if($order[0]['column']==1){
                $column_name='plan_name';
            }else if($order[0]['column']==2){
                $column_name='amount';
            }else if($order[0]['column']==3){
                $column_name='max_users';                
            }else if($order[0]['column']==4){
                $column_name='create_dt';                
            }else{
                $column_name='id';
            }
        }
        $totalRecord      = $this->app_model->categoryAjaxlist(true);
        $getRecordListing = $this->app_model->categoryAjaxlist(false,$start,$length, $column_name, $order[0]['dir']);
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
                    $plan_id = encode($recordData->id);
                    $plan_url = base_url('admin/app/category_update/').$plan_id;
                    $actionContent = ''; // set default empty
                    $content .='[';                     
                    // $recordListing[$i][0]=   '<input type="checkbox" name="checkAll[]" value="'.($recordData->id).'" class="cb-element" ">';
                    $recordListing[$i][0]=  $srNumber+1;
                    $recordListing[$i][1]= $recordData->category_name;
                    $parent_name = '';
                    if (!empty($recordData->category_parent)) {
                        $condition = array('id' => $recordData->category_parent);
                        $parent_details = getdatafromtable(TABLE_BUSINESS_CATEGORY, $condition, 'id, category_name');
                        $parent_name = $parent_details[0]['category_name'];
                    }
                   
                    $recordListing[$i][2]= $parent_name;
                    if ($recordData->category_type == '1') {
                        $category_type = 'Skills Category';
                    }
                    if ($recordData->category_type == '2') {
                        $category_type = 'Business Category';
                    }
                    if ($recordData->category_type == '3') {
                        $category_type = 'Products Category';
                    }
                    $recordListing[$i][3]= $category_type;
                    $recordListing[$i][4]= get_formated_date($recordData->create_dt, 2);
                   
                    $table = TABLE_BUSINESS_CATEGORY;
                    $field = 'status';
                    $urls  =  base_url('admin/app/updateStatus');
                    $actionContent='';
                    if(check_permission(STATUS,"app_category_list")==1){
                    if($recordData->status == "Deactive"){
                        $user_status = "Active";
                        $actionContent .='<a class="btn btn-danger waves-effect" style="width: 90%;" href="javascript:void(0);" onclick="changestatus('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('inactive').'">'.$this->lang->line('inactive').'</a>';
                    }else{ 
                        $user_status = "Deactive";
                        $actionContent .='<a class="btn btn-active waves-effect" style="width: 90%;" href="javascript:void(0);" onclick="changestatus('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('active').'">'.$this->lang->line('active').'</a>';
                    }
                }
                    $recordListing[$i][5]= $actionContent; 
                    //blank for edit button
                    $actionContent = '';
                    if(check_permission(EDIT,"app_category_list")==1){
                    $actionContent .='<a href="'.$plan_url.'" title="Edit" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5"><i class="fa fa-edit"></i></a> '; 
                    }
                    $recordListing[$i][6]= $actionContent;  
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
            $this->app_model->updateDataFromTabel($table, $updateData, $upWhere);
            //echo $this->db->last_query();die;
            $returnData = array('isSuccess' => true);
        } else {
            $returnData = array('isSuccess' => false);
        }
        echo json_encode($returnData);
    }

    /* Show Profile info */
    public function category_update($category_id=''){
        check_permission(EDIT,"app_category_list",1);
        $data['parent_data']=$this->dynamic_model->getdatafromtable(TABLE_BUSINESS_CATEGORY,array("category_parent"=>0)); 
        if(!empty($category_id) ) {
            $pid =  decode($category_id);
            $header['title'] = $this->lang->line('category_update');
            $data['category_data']=$this->dynamic_model->getdatafromtable(TABLE_BUSINESS_CATEGORY,array("id"=>$pid)); 
           //echo "<pre>"; print_r($data); echo "</pre>"; exit();
            $this->admintemplates('app/category_update',$data, $header);
        } else{
             $header['title'] = $this->lang->line('category_add');
            $this->admintemplates('app/category_update',$data, $header);
           // redirect(base_url('admin/category_list'));
        }
    }
    /* User Profile update by admin */

    public function category_update_submit(){
        check_permission(EDIT,"app_category_list",1);
        extract($this->input->post());
        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('category_name', 'category_name', 'required', array( 'required' => $this->lang->line('tb_category_name')));
            $this->form_validation->set_rules('category_type', 'category_type', 'required', array( 'required' => $this->lang->line('tb_category_type')));
            
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/app/category_list/');
            } else {
                $updatedata = array();
                $updatedata['category_name'] = $category_name;
                $updatedata['category_type'] = $category_type;
                if(!empty($category_parent)){
                    $updatedata['category_parent'] = $category_parent;
                }
                if(!empty($updateid)){
                    $this->dynamic_model->updatedata(TABLE_BUSINESS_CATEGORY, $updatedata,$updateid);
                    $msg = $this->lang->line('category_update_success');
                }
                else{
                    $updatedata['status'] = "Active";
                    $updatedata['create_dt'] = time();
                    $updateid = $this->dynamic_model->insertdata(TABLE_BUSINESS_CATEGORY, $updatedata);
                     $msg = $this->lang->line('category_add_success');
                }
               // echo $this->db->last_query(); exit();
                 
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $msg);
                redirect(site_url().'admin/app/category_update/'.encode($updateid));  
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProblem in Server. Please Try Again');
            redirect(site_url().'admin/app/category_update/'.encode($updateid));                    
        }
        $this->admintemplates('admin/app/category_list');
    }

    public function skills_list(){
        check_permission(VIEW,"contact_details",1);
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> '. $this->lang->line('dashboard_breadcrumb') , 'admin'); 
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> '. $this->lang->line('appcontent_breadcrumb') , 'admin/category'); 
        $data=array();
        $header['title'] = $this->lang->line('app_skills_list');        
        $this->admintemplates('app/skills_list',$data, $header);
    } 
    public function skillsAjaxlist(){
        $start         =  $this->input->get('start'); // get promo code Id
        $length        =  $this->input->get('length'); // get promo code Id
        $draw          =  $this->input->get('draw'); // get promo code Id
        $order   =  $this->input->get('order');
        if(!empty($order)){ 
            if($order[0]['column']==1){
                $column_name='plan_name';
            }else if($order[0]['column']==2){
                $column_name='amount';
            }else if($order[0]['column']==3){
                $column_name='max_users';                
            }else if($order[0]['column']==4){
                $column_name='create_dt';                
            }else{
                $column_name='id';
            }
        }
        $totalRecord      = $this->app_model->skillsAjaxlist(true);
        $getRecordListing = $this->app_model->skillsAjaxlist(false,$start,$length, $column_name, $order[0]['dir']);
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
                    $plan_id = encode($recordData->id);
                    $plan_url = base_url('admin/app/skills_update/').$plan_id;
                    $actionContent = ''; // set default empty
                    $content .='[';                     
                    // $recordListing[$i][0]=   '<input type="checkbox" name="checkAll[]" value="'.($recordData->id).'" class="cb-element" ">';
                    $recordListing[$i][0]=  $srNumber+1;
                    $recordListing[$i][1]= $recordData->name;
                    $recordListing[$i][2]= get_formated_date(strtotime($recordData->create_dt), 2);
                   
                    $table = TABLE_MANAGE_SKILLS;
                    $field = 'status';
                    $urls  =  base_url('admin/app/updateStatus');
                    $actionContent='';
                    if(check_permission(STATUS,"app_skills_list")==1){
                    if($recordData->status == "Inactive"){
                        $user_status = "Active";
                        $actionContent .='<a class="btn btn-danger waves-effect" style="width: 90%;" href="javascript:void(0);" onclick="changestatus('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('inactive').'">'.$this->lang->line('inactive').'</a>';
                    }else{ 
                        $user_status = "Inactive";
                        $actionContent .='<a class="btn btn-active waves-effect" style="width: 90%;" href="javascript:void(0);" onclick="changestatus('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('active').'">'.$this->lang->line('active').'</a>';
                    }
                }
                    $recordListing[$i][3]= $actionContent; 
                    //blank for edit button
                    $actionContent = '';
                    if(check_permission(EDIT,"app_skills_list")==1){
                    $actionContent .='<a href="'.$plan_url.'" title="Edit" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5"><i class="fa fa-edit"></i></a> '; 
                    }
                    $recordListing[$i][4]= $actionContent;  
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
    public function skills_update($skills_id=''){
        check_permission(EDIT,"app_skills_list",1);
        $data = array();
        if(!empty($skills_id) ) {
            $pid =  decode($skills_id);
            $header['title'] = $this->lang->line('skills_update');
            $data['skills_data']=$this->dynamic_model->getdatafromtable(TABLE_MANAGE_SKILLS,array("id"=>$pid)); 
            $this->admintemplates('app/skills_update',$data, $header);
        } else{
            $header['title'] = $this->lang->line('skills_add');
            $this->admintemplates('app/skills_update',$data, $header);
           // redirect(base_url('admin/category_list'));
        }
    }
    /* User Profile update by admin */

    public function skills_update_submit(){
        check_permission(EDIT,"app_skills_list",1);
        extract($this->input->post());
        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('name', 'name', 'required', array( 'required' => $this->lang->line('tb_skills_name')));
            
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/app/skills_list/');
            } else {
                $updatedata = array();
                $updatedata['name'] = $name;
                if(!empty($updateid)){
                    $this->dynamic_model->updatedata(TABLE_MANAGE_SKILLS, $updatedata,$updateid);
                    $msg = $this->lang->line('skills_update_success');
                }
                else{
                    $updatedata['status'] = "Active";
                    $updatedata['create_dt'] = date('Y-m-d H:i:s');
                    $updateid = $this->dynamic_model->insertdata(TABLE_MANAGE_SKILLS, $updatedata);
                     $msg = $this->lang->line('skills_add_success');
                }
               // echo $this->db->last_query(); exit();
                 
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $msg);
                redirect(site_url().'admin/app/skills_update/'.encode($updateid));  
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProblem in Server. Please Try Again');
            redirect(site_url().'admin/app/skills_update/'.encode($updateid));                    
        }
        $this->admintemplates('admin/app/skills_list');
    }


     /* Block and approved user by admin */
    public function updateStatusSkills() {
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
            $this->app_model->updateDataFromTabel($table, $updateData, $upWhere);
            //echo $this->db->last_query();die;
            $returnData = array('isSuccess' => true);
        } else {
            $returnData = array('isSuccess' => false);
        }
        echo json_encode($returnData);
    }


    /********************************************/


    public function videos_list(){
        check_permission(VIEW,"app_videos_list",1);
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> '. $this->lang->line('dashboard_breadcrumb') , 'admin'); 
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> '. $this->lang->line('appcontent_breadcrumb') , 'admin/category'); 
        $data=array();
        $header['title'] = 'Video List'; //$this->lang->line('app_videos_list');        
        $this->admintemplates('app/videos_list',$data, $header);
    } 
    public function videosAjaxlist(){
        $start         =  $this->input->get('start'); // get promo code Id
        $length        =  $this->input->get('length'); // get promo code Id
        $draw          =  $this->input->get('draw'); // get promo code Id
        $order   =  $this->input->get('order');
        if(!empty($order)){ 
            if($order[0]['column']==1){
                $column_name='title';
            }else if($order[0]['column']==2){
                $column_name='url';
            }else if($order[0]['column']==3){
                $column_name='description';                
            }else if($order[0]['column']==4){
                $column_name='create_dt';                
            }else{
                $column_name='id';
            }
        }
        $totalRecord      = $this->app_model->videosAjaxlist(true);
        $getRecordListing = $this->app_model->videosAjaxlist(false,$start,$length, $column_name, $order[0]['dir']);
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
                    $plan_id = encode($recordData->id);
                    $plan_url = base_url('admin/app/videos_update/').$plan_id;
                    $actionContent = ''; // set default empty
                    $content .='[';                     
                    // $recordListing[$i][0]=   '<input type="checkbox" name="checkAll[]" value="'.($recordData->id).'" class="cb-element" ">';
                    $recordListing[$i][0]=  $srNumber+1;
                    $recordListing[$i][1]= $recordData->title;
                    $recordListing[$i][2]= '<video width="320" height="240" controls><source src="'.base_url().'uploads/video/'.$recordData->url.'" type="video/mp4">Your browser does not support the video tag.</video>';
                    $recordListing[$i][3]= $recordData->description;
                    $recordListing[$i][4]= get_formated_date(strtotime($recordData->create_dt), 2);
                   
                    $table = TABLE_MANAGE_VIDEO;
                    $field = 'status';
                    $urls  =  base_url('admin/app/updateStatus');
                    $actionContent='';
                    if(check_permission(STATUS,"app_videos_list")==1){
                    if($recordData->status == "Deactive"){
                        $user_status = "Active";
                        $actionContent .='<a class="btn btn-danger waves-effect" style="width: 90%;" href="javascript:void(0);" onclick="changestatus('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('inactive').'">'.$this->lang->line('inactive').'</a>';
                    }else{ 
                        $user_status = "Deactive";
                        $actionContent .='<a class="btn btn-active waves-effect" style="width: 90%;" href="javascript:void(0);" onclick="changestatus('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('active').'">'.$this->lang->line('active').'</a>';
                    }
                }
                    $recordListing[$i][5]= $actionContent; 
                    //blank for edit button
                    $actionContent = '';
                    if(check_permission(EDIT,"app_videos_list")==1){
                    $actionContent .='<a href="'.$plan_url.'" title="Edit" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5"><i class="fa fa-edit"></i></a> '; 
                    }
                    $recordListing[$i][6]= $actionContent;  
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
    public function videos_update($videos_id=''){
        check_permission(EDIT,"app_videos_list",1);
        $data = array();
        if(!empty($videos_id) ) {
            $pid =  decode($videos_id);
            $header['title'] = 'Video Update'; // $this->lang->line('videos_update');
            $data['videos_data']=$this->dynamic_model->getdatafromtable(TABLE_MANAGE_VIDEO,array("id"=>$pid)); 
            $this->admintemplates('app/videos_update',$data, $header);
        } else{
            $header['title'] = 'Video Add'; //$this->lang->line('videos_add');
            $this->admintemplates('app/videos_update',$data, $header);
           // redirect(base_url('admin/category_list'));
        }
    }
    /* User Profile update by admin */

    public function videos_update_submit(){
        check_permission(EDIT,"app_videos_list",1);
        extract($this->input->post());
        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('title', 'title', 'required', array( 'required' => $this->lang->line('tb_videos_name')));
            
             $this->form_validation->set_rules('description', 'description', 'required', array( 'required' => $this->lang->line('tb_videos_desc')));

            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/app/videos_list/');
            } else {
                $updatedata = array();
                
                if(is_uploaded_file($_FILES['video_url']['tmp_name']))
                {
                    $exp_file=explode('.',$_FILES['video_url']['name']);
                    $filename=str_replace(" ","_",$exp_file[0]);
                    $filename=str_replace(" ","_",$filename);
                    $file_name=clean_string($filename).".".$exp_file[1];
                    $path="uploads/video/".$file_name;
                    $ext = pathinfo($_FILES['video_url']['name'], PATHINFO_EXTENSION);

                    if (in_array($ext, array('mp4','3gp'))) 
                    {
                        move_uploaded_file($_FILES['video_url']['tmp_name'],$path);

                        //For video upload
                        $unique_id = substr(md5(rand(0000,9999)), 0, 12);
                        $video_thumb_image = $unique_id.'.jpg';
                        $video_thumb_image_path = "uploads/video/";
                        $thumbnail = $video_thumb_image_path.$video_thumb_image;
                        

                        // shell command [highly simplified, please don't run it plain on your script!]
                        $output = shell_exec("ffmpeg -i $path -deinterlace -an -ss 1 -t 00:00:01 -r 1 -y -vcodec mjpeg -f mjpeg $thumbnail 2>&1");
                        //echo "<pre>$output</pre>";die;

                        $updatedata['url'] = $file_name;
                        //$updatedata['video_thumb_image'] = $video_thumb_image;
                        
                    }
                }


                
                $updatedata['title'] = $title;
                $updatedata['description'] = $description;
                if(!empty($updateid)){
                    $this->dynamic_model->updatedata(TABLE_MANAGE_VIDEO, $updatedata,$updateid);
                    $msg = $this->lang->line('videos_update_success');
                }
                else{
                    $updatedata['status'] = "Active";
                    $updatedata['create_dt'] = date('Y-m-d H:i:s');
                    $updateid = $this->dynamic_model->insertdata(TABLE_MANAGE_VIDEO, $updatedata);
                     $msg = $this->lang->line('videos_add_success');
                }
               // echo $this->db->last_query(); exit();
                 
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $msg);
                redirect(site_url().'admin/app/videos_update/'.encode($updateid));  
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProblem in Server. Please Try Again');
            redirect(site_url().'admin/app/videos_update/'.encode($updateid));                    
        }
        $this->admintemplates('admin/app/videos_list');
    }


     /* Block and approved user by admin */
    public function updateStatusVideos() {
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
            $this->app_model->updateDataFromTabel($table, $updateData, $upWhere);
            //echo $this->db->last_query();die;
            $returnData = array('isSuccess' => true);
        } else {
            $returnData = array('isSuccess' => false);
        }
        echo json_encode($returnData);
    }

    /******************************************/



    public function newsfeed_list(){
        check_permission(VIEW,"app_newsfeed_list",1);
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> '. $this->lang->line('dashboard_breadcrumb') , 'admin'); 
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> '. $this->lang->line('appcontent_breadcrumb') , 'admin/category'); 
        $data=array();
        $header['title'] = 'Newsfeed List'; //$this->lang->line('app_newsfeed_list');        
        $this->admintemplates('app/newsfeed_list',$data, $header);
    } 
    public function newsfeedAjaxlist(){
        $start         =  $this->input->get('start'); // get promo code Id
        $length        =  $this->input->get('length'); // get promo code Id
        $draw          =  $this->input->get('draw'); // get promo code Id
        $order   =  $this->input->get('order');
        if(!empty($order)){ 
            if($order[0]['column']==1){
                $column_name='title';
            }else if($order[0]['column']==2){
                $column_name='url';
            }else if($order[0]['column']==3){
                $column_name='description';                
            }else if($order[0]['column']==4){
                $column_name='create_dt';                
            }else{
                $column_name='id';
            }
        }
        $totalRecord      = $this->app_model->newsfeedAjaxlist(true);
        $getRecordListing = $this->app_model->newsfeedAjaxlist(false,$start,$length, $column_name, $order[0]['dir']);
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
                    $plan_id = encode($recordData->id);
                    $plan_url = base_url('admin/app/newsfeed_update/').$plan_id;
                    $actionContent = ''; // set default empty
                    $content .='[';                     
                    // $recordListing[$i][0]=   '<input type="checkbox" name="checkAll[]" value="'.($recordData->id).'" class="cb-element" ">';
                    $recordListing[$i][0]=  $srNumber+1;
                    $recordListing[$i][1]= $recordData->title;
                    $recordListing[$i][2]= '<img width="50%" src='.base_url().'uploads/newsfeed/'.$recordData->url.' >';

                    //'<video width="320" height="240" controls><source src="'.base_url().'uploads/video/'.$recordData->url.'" type="video/mp4">Your browser does not support the video tag.</video>';
                    $recordListing[$i][3]= $recordData->description;
                    $recordListing[$i][4]= get_formated_date($recordData->create_dt, 1);
                   
                    $table = TABLE_MANAGE_NEWSFEED;
                    $field = 'status';
                    $urls  =  base_url('admin/app/updateStatus');
                    $actionContent='';
                    if(check_permission(STATUS,"app_newsfeed_list")==1){
                    if($recordData->status == "Deactive"){
                        $user_status = "Active";
                        $actionContent .='<a class="btn btn-danger waves-effect" style="width: 90%;" href="javascript:void(0);" onclick="changestatus('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('inactive').'">'.$this->lang->line('inactive').'</a>';
                    }else{ 
                        $user_status = "Deactive";
                        $actionContent .='<a class="btn btn-active waves-effect" style="width: 90%;" href="javascript:void(0);" onclick="changestatus('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('active').'">'.$this->lang->line('active').'</a>';
                    }
                }
                    $recordListing[$i][5]= $actionContent; 
                    //blank for edit button
                    $actionContent = '';
                    if(check_permission(EDIT,"app_newsfeed_list")==1){
                    $actionContent .='<a href="'.$plan_url.'" title="Edit" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5"><i class="fa fa-edit"></i></a> '; 
                    }
                    $recordListing[$i][6]= $actionContent;  
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
    public function newsfeed_update($newsfeed_id=''){
        check_permission(EDIT,"app_newsfeed_list",1);
        $data = array();
        if(!empty($newsfeed_id) ) {
            $pid =  decode($newsfeed_id);
            $header['title'] = 'Newsfeed Update'; // $this->lang->line('newsfeed_update');
            $data['newsfeed_data']=$this->dynamic_model->getdatafromtable(TABLE_MANAGE_NEWSFEED,array("id"=>$pid)); 
            $this->admintemplates('app/newsfeed_update',$data, $header);
        } else{
            $header['title'] = 'Newsfeed Add'; //$this->lang->line('newsfeed_add');
            $this->admintemplates('app/newsfeed_update',$data, $header);
           // redirect(base_url('admin/category_list'));
        }
    }
    /* User Profile update by admin */

    public function newsfeed_update_submit(){
        check_permission(EDIT,"app_newsfeed_list",1);
        extract($this->input->post());
        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('title', 'title', 'required', array( 'required' => $this->lang->line('tb_newsfeed_name')));
            
             $this->form_validation->set_rules('description', 'description', 'required', array( 'required' => $this->lang->line('tb_newsfeed_desc')));

            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/app/newsfeed_list/');
            } else {
                $updatedata = array();
                
                if(is_uploaded_file($_FILES['newsfeed_url']['tmp_name']))
                {
                    $exp_file=explode('.',$_FILES['newsfeed_url']['name']);
                    $filename=str_replace(" ","_",$exp_file[0]);
                    $filename=str_replace(" ","_",$filename);
                    $file_name=clean_string($filename).".".$exp_file[1];
                    $path="uploads/newsfeed/".$file_name;
                    $ext = pathinfo($_FILES['newsfeed_url']['name'], PATHINFO_EXTENSION);

                    if (in_array($ext, array('jpeg','png','jpg'))) 
                    {
                        move_uploaded_file($_FILES['newsfeed_url']['tmp_name'],$path);

                        //For video upload
                        //$unique_id = substr(md5(rand(0000,9999)), 0, 12);
                        //$video_thumb_image = $unique_id.'.jpg';
                        //$video_thumb_image_path = "uploads/video/";
                        //$thumbnail = $video_thumb_image_path.$video_thumb_image;
                        

                        // shell command [highly simplified, please don't run it plain on your script!]
                        //$output = shell_exec("ffmpeg -i $path -deinterlace -an -ss 1 -t 00:00:01 -r 1 -y -vcodec mjpeg -f mjpeg $thumbnail 2>&1");
                        //echo "<pre>$output</pre>";die;

                        $updatedata['url'] = $file_name;
                        //$updatedata['video_thumb_image'] = $video_thumb_image;
                        
                    }
                }


                $time = time();
                $updatedata['title'] = $title;
                $updatedata['description'] = $description;
                $updatedata['update_dt'] = $time;
                if(!empty($updateid)){
                    $this->dynamic_model->updatedata(TABLE_MANAGE_NEWSFEED, $updatedata,$updateid);
                    $msg = $this->lang->line('newsfeed_update_success');
                }
                else{
                    
                    $updatedata['status'] = "Active";
                    $updatedata['create_dt'] = $time;
                    
                    $updateid = $this->dynamic_model->insertdata(TABLE_MANAGE_NEWSFEED, $updatedata);
                     $msg = $this->lang->line('newsfeed_add_success');
                }
               // echo $this->db->last_query(); exit();
                 
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $msg);
                redirect(site_url().'admin/app/newsfeed_update/'.encode($updateid));  
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProblem in Server. Please Try Again');
            redirect(site_url().'admin/app/newsfeed_update/'.encode($updateid));                    
        }
        $this->admintemplates('admin/app/newsfeed_list');
    }


     /* Block and approved user by admin */
    public function updateStatusNewsfeed() {
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
            $this->app_model->updateDataFromTabel($table, $updateData, $upWhere);
            //echo $this->db->last_query();die;
            $returnData = array('isSuccess' => true);
        } else {
            $returnData = array('isSuccess' => false);
        }
        echo json_encode($returnData);
    }
}
