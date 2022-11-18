<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CategoryMaster extends My_Controller {
    private $login_user_id = null;
    public function __construct(){      
        parent::__construct();      
        $this->load->model('dynamic_model');
        $this->load->model('admin_model');
        $this->load->model('users_model');
        $this->load->model('category_model');
        $this->lang->load("admin_message", "english");
        if($this->session->userdata('logged_in')){
            $currentuser = getuserdetails();
            $this->login_user_id = $currentuser['id'];
        }
    }
            
    public function index() {
        check_permission(VIEW,"category_list",1);
        $header['title'] = $this->lang->line('category_list_title');
        $data['catdata']=$this->dynamic_model->getdatafromtable(TABLE_BUSINESS_CATEGORY,array("category_parent "=>0));       
        $this->admintemplates('category/category_list',$data, $header);
    }

    public function categoryAjaxlist(){
        $start         =  $this->input->get('start'); // get promo code Id
        $length        =  $this->input->get('length'); // get promo code Id
        $draw          =  $this->input->get('draw'); // get promo code Id
        $order   =  $this->input->get('order');
        if(!empty($order)){ 
            if($order[0]['column']==2){
                $column_name='category_name';
            }else if($order[0]['column']==3){
                $column_name='category_type';
            }else if($order[0]['column']==4){
                $column_name='price';                
            }else if($order[0]['column']==5){
                $column_name='no_of_days';                
            }else if($order[0]['column']==6){
                $column_name='status';                
            }else{
                $column_name='id';
            }
        }
        $totalRecord      = $this->category_model->categoryAjaxlist(true);
        $getRecordListing = $this->category_model->categoryAjaxlist(false,$start,$length, $column_name, $order[0]['dir']);
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
                    $cat_id = encode($recordData->id);
                    $cat_url = base_url('admin/categoryMaster/editCat/').$cat_id;                   
                    $subcat_url = base_url('admin/categoryMaster/getSubCat/').$cat_id;                   
                
                    $recordListing[$i][0]=   '<input type="checkbox" name="checkAll[]" value="'.($recordData->id).'" class="cb-element" ">';
                    $recordListing[$i][1]=  $srNumber+1;
                    $recordListing[$i][2]= $recordData->category_name;

                    $CatType = $recordData->category_type=='1'?$this->lang->line('category_type_1'):($recordData->category_type=='2'?$this->lang->line('category_type_2'):($recordData->category_type=='3'?$this->lang->line('category_type_3'):''));

                    $recordListing[$i][3]= $CatType;
                    $recordListing[$i][4]= $recordData->price;
                    $recordListing[$i][5]= $recordData->no_of_days;
                   
                    $table = TABLE_BUSINESS_CATEGORY;
                    $field = 'status';
                    $urls  =  base_url('admin/categoryMaster/updateStatus');
                    $actionContent='';
                    // if(check_permission(STATUS,"user_list")==1){ 
                    //     if($recordData->email_verified == "0" ){
                    //       $verify_status='1';//email not verify
                    //     }elseif($recordData->mobile_verified == "0" ){
                    //       $verify_status='2';//mobile not verify
                    //     }else{
                    //          $verify_status='0';//verified
                    //     }
                        // if($recordData->email_verified == "0" || $recordData->mobile_verified == "0"){
                        //    $id='';
                        //      if($recordData->status == "Deactive"){
                        //         $user_status = "Active";
                        //          $actionContent .='<a class="btn btn-danger waves-effect btn-width"  href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\',\''.$id.'\',\''.$verify_status.'\');" title="'.$this->lang->line('inactive').'">'.$this->lang->line('inactive').'</a>';
                        //     }else{ 
                        //         $user_status = "Deactive";
                        //          $actionContent .='<a class="btn btn-active waves-effect btn-width"  href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\',\''.$id.'\',\''.$verify_status.'\');" title="'.$this->lang->line('active').'">'.$this->lang->line('active').'</a>';
                        //     }
                        // }else{
                            if($recordData->status == "Deactive"){
                                $user_status = "Active";
                                $actionContent .='<a class="btn btn-danger waves-effect btn-width"  href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('inactive').'">'.$this->lang->line('inactive').'</a>';
                            }else{ 
                                $user_status = "Deactive";
                                $actionContent .='<a class="btn btn-active waves-effect btn-width"  href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('active').'">'.$this->lang->line('active').'</a>';
                            }
                        // }
                    // }
                    $recordListing[$i][6]= $actionContent; 
                    //blank for edit button
                    $actionContent = '';
                    // if(check_permission(EDIT,"user_list")==1){
                    // $actionContent .='<a href="'.$profile_url.'" title="Edit" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5"><i class="fa fa-edit"></i></a> '; 
                    //  }

                    $actionContent .='<a href="'.$cat_url.'" title="Edit" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5"><i class="fa fa-edit"></i></a> '; 
                    
                    $actionContent .='<a href="'.$subcat_url.'" title="View Sub Category" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5"><i class="fa fa-eye"></i></a> '; 
                    
                    
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

    /* Show Profile info */
    public function editCat($cat_id=''){
        // check_permission(EDIT,"user_list",1);
        $uid =  decode($cat_id);
        if(!empty($cat_id) && !empty($uid)){
            $loguserinfo['catinfo'] = $this->dynamic_model->get_category_by_id($uid);
            $header['title'] = $this->lang->line('btn_update_details');
            $this->admintemplates('category/category_update', $loguserinfo, $header);
        } else{
            redirect(base_url('admin/categoryMaster'));
        }
    }

    /* Show Profile info */
    public function getSubCat($cat_id=''){
        // check_permission(EDIT,"user_list",1);
        $uid =  decode($cat_id);
        if(!empty($cat_id) && !empty($uid)){
            $loguserinfo['catid'] = $cat_id;
            $header['title'] = $this->lang->line('sub_category_list');
            $this->admintemplates('category/sub_category_list', $loguserinfo, $header);
        } else{
            redirect(base_url('admin/categoryMaster'));
        }
    }




    public function subcategoryAjaxlist($categ_id){
        $start         =  $this->input->get('start'); // get promo code Id
        $length        =  $this->input->get('length'); // get promo code Id
        $draw          =  $this->input->get('draw'); // get promo code Id
        $order   =  $this->input->get('order');
        if(!empty($order)){ 
            if($order[0]['column']==2){
                $column_name='category_name';
            }else if($order[0]['column']==3){
                $column_name='category_type';
            }else if($order[0]['column']==4){
                $column_name='price';                
            }else if($order[0]['column']==5){
                $column_name='no_of_days';                
            }else if($order[0]['column']==6){
                $column_name='status';                
            }else{
                $column_name='id';
            }
        }
        $totalRecord      = $this->category_model->subcategoryAjaxlist(true,0,0,'','desc',decode($categ_id));
        $getRecordListing = $this->category_model->subcategoryAjaxlist(false,$start,$length, $column_name, $order[0]['dir'],decode($categ_id));
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
                    $cat_id = encode($recordData->id);
                    $cat_url = base_url('admin/categoryMaster/editSubCat/').$categ_id.'/'.$cat_id;                    
                    // $subcat_url = base_url('admin/categoryMaster/getSubCat/').$cat_id;                   
                
                    $recordListing[$i][0]=   '<input type="checkbox" name="checkAll[]" value="'.($recordData->id).'" class="cb-element" ">';
                    $recordListing[$i][1]=  $srNumber+1;
                    $recordListing[$i][2]= $recordData->category_name;

                    $CatType = $recordData->category_type=='1'?$this->lang->line('category_type_1'):($recordData->category_type=='2'?$this->lang->line('category_type_2'):($recordData->category_type=='3'?$this->lang->line('category_type_3'):''));

                    $recordListing[$i][3]= $CatType;
                    $recordListing[$i][4]= $recordData->price;
                    $recordListing[$i][5]= $recordData->no_of_days;
                   
                    $table = TABLE_BUSINESS_CATEGORY;
                    $field = 'status';
                    $urls  =  base_url('admin/categoryMaster/updateStatus');
                    $actionContent='';
                    // if(check_permission(STATUS,"user_list")==1){ 
                    //     if($recordData->email_verified == "0" ){
                    //       $verify_status='1';//email not verify
                    //     }elseif($recordData->mobile_verified == "0" ){
                    //       $verify_status='2';//mobile not verify
                    //     }else{
                    //          $verify_status='0';//verified
                    //     }
                        // if($recordData->email_verified == "0" || $recordData->mobile_verified == "0"){
                        //    $id='';
                        //      if($recordData->status == "Deactive"){
                        //         $user_status = "Active";
                        //          $actionContent .='<a class="btn btn-danger waves-effect btn-width"  href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\',\''.$id.'\',\''.$verify_status.'\');" title="'.$this->lang->line('inactive').'">'.$this->lang->line('inactive').'</a>';
                        //     }else{ 
                        //         $user_status = "Deactive";
                        //          $actionContent .='<a class="btn btn-active waves-effect btn-width"  href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\',\''.$id.'\',\''.$verify_status.'\');" title="'.$this->lang->line('active').'">'.$this->lang->line('active').'</a>';
                        //     }
                        // }else{
                            if($recordData->status == "Deactive"){
                                $user_status = "Active";
                                $actionContent .='<a class="btn btn-danger waves-effect btn-width"  href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('inactive').'">'.$this->lang->line('inactive').'</a>';
                            }else{ 
                                $user_status = "Deactive";
                                $actionContent .='<a class="btn btn-active waves-effect btn-width"  href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('active').'">'.$this->lang->line('active').'</a>';
                            }
                        // }
                    // }
                    $recordListing[$i][6]= $actionContent; 
                    //blank for edit button
                    $actionContent = '';
                    // if(check_permission(EDIT,"user_list")==1){
                    // $actionContent .='<a href="'.$profile_url.'" title="Edit" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5"><i class="fa fa-edit"></i></a> '; 
                    //  }

                    $actionContent .='<a href="'.$cat_url.'" title="Edit" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5"><i class="fa fa-edit"></i></a> '; 
                    
                    // $actionContent .='<a href="'.$subcat_url.'" title="View Sub Category" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5"><i class="fa fa-edit"></i></a> '; 
                    
                    
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




    

    /* User Profile update by admin */
    public function categoryUpdate(){
        // check_permission(EDIT,"user_list",1);
        extract($this->input->post());
        // $allowedExts = array("JPG","JPEG","PNG","png","jpeg","jpg");
        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('category_name', 'Category Name', 'required');
            $this->form_validation->set_rules('category_type', 'Category Type', 'required');
            $this->form_validation->set_rules('price', 'Price', 'required', array( 'required' => 'Valid price is required.'));
            $this->form_validation->set_rules('no_of_days', 'No. of days', 'required', array( 'required' => 'Valid no. of days is required.'));
            
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/categoryMaster/editCat/'.encode($updateid));
            } else {
                $updatedata = array();
                // $userid = $updateid;
                // $file_ext = pathinfo($_FILES["updateuserpic"]["name"], PATHINFO_EXTENSION);
                // if (!empty($_FILES['updateuserpic']['name'])) {
                //     // check for valid file to upload 
                //     $file_ext=strtolower($file_ext);
                //     if(!in_array($file_ext, $allowedExts)){
                //         $this->session->set_flashdata('updateclass', 'danger');
                //         $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                //         redirect(site_url().'admin/users/userProfile/'.encode($updateid)); 
                     
                //     }
                //     $img_name = $this->dynamic_model->fileupload('updateuserpic', 'uploads/user', 'Picture');
                // } else {
                //     $img_name = $this->input->post('oldpic');
                // }
                $updatedata['category_name'] = $category_name;
                $updatedata['category_type'] = $category_type;
                $updatedata['price'] = $price;
                $updatedata['no_of_days'] = $no_of_days;

                $this->dynamic_model->updatedata(TABLE_BUSINESS_CATEGORY, $updatedata, $updateid); 
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('category_update_success'));
                redirect(site_url().'admin/categoryMaster/editCat/'.encode($updateid));  
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProblem in Server. Please Try Again');
            redirect(site_url().'admin/categoryMaster/editCat/'.encode($updateid));                    
        }     
    }

    /* Show Profile info */
    public function editSubCat($catid,$subcatid=''){
        // check_permission(EDIT,"user_list",1);
        $uid =  decode($subcatid);
        if(!empty($subcatid) && !empty($uid)){
            $loguserinfo['catid'] = $catid;
            $loguserinfo['catinfo'] = $this->dynamic_model->get_category_by_id($uid);
            $header['title'] = $this->lang->line('btn_update_details');
            $this->admintemplates('category/sub_category_update', $loguserinfo, $header);
        } else{
            redirect(base_url('admin/categoryMaster'));
        }
    }


    

    /* User Profile update by admin */
    public function subCategoryUpdate(){
        // check_permission(EDIT,"user_list",1);
        extract($this->input->post());
        // $allowedExts = array("JPG","JPEG","PNG","png","jpeg","jpg");
        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('category_name', 'Category Name', 'required');
            $this->form_validation->set_rules('category_type', 'Category Type', 'required');
            $this->form_validation->set_rules('price', 'Price', 'required', array( 'required' => 'Valid price is required.'));
            $this->form_validation->set_rules('no_of_days', 'No. of days', 'required', array( 'required' => 'Valid no. of days is required.'));
            
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/categoryMaster/editSubCat/'.$catid.'/'.encode($updateid));
            } else {
                $updatedata = array();
                // $userid = $updateid;
                // $file_ext = pathinfo($_FILES["updateuserpic"]["name"], PATHINFO_EXTENSION);
                // if (!empty($_FILES['updateuserpic']['name'])) {
                //     // check for valid file to upload 
                //     $file_ext=strtolower($file_ext);
                //     if(!in_array($file_ext, $allowedExts)){
                //         $this->session->set_flashdata('updateclass', 'danger');
                //         $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                //         redirect(site_url().'admin/users/userProfile/'.encode($updateid)); 
                     
                //     }
                //     $img_name = $this->dynamic_model->fileupload('updateuserpic', 'uploads/user', 'Picture');
                // } else {
                //     $img_name = $this->input->post('oldpic');
                // }
                $updatedata['category_name'] = $category_name;
                $updatedata['category_type'] = $category_type;
                $updatedata['price'] = $price;
                $updatedata['no_of_days'] = $no_of_days;

                $this->dynamic_model->updatedata(TABLE_BUSINESS_CATEGORY, $updatedata, $updateid); 
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('sub_category_update_success'));
                redirect(site_url().'admin/categoryMaster/editSubCat/'.$catid.'/'.encode($updateid));  
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProblem in Server. Please Try Again');
            redirect(site_url().'admin/categoryMaster/editSubCat/'.$catid.'/'.encode($updateid));                    
        }     
    }



    public function addSubCat($catid){
        $header['title'] = $this->lang->line('add_sub_category');
        $data['catid'] = $catid;
        $this->admintemplates('category/sub_category_add', $data, $header);
    }



    /* User Profile update by admin */
    public function subCategoryAdd(){
        // check_permission(EDIT,"user_list",1);
        extract($this->input->post());
        // $allowedExts = array("JPG","JPEG","PNG","png","jpeg","jpg");
        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('category_name', 'Category Name', 'required');
            $this->form_validation->set_rules('category_type', 'Category Type', 'required');
            $this->form_validation->set_rules('price', 'Price', 'required', array( 'required' => 'Valid price is required.'));
            $this->form_validation->set_rules('catid', 'Category Id', 'required');
            $this->form_validation->set_rules('no_of_days', 'No. of days', 'required', array( 'required' => 'Valid no. of days is required.'));
            
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/categoryMaster/getSubCat/'.$catid);
            } else {
                $updatedata = array();
                // $userid = $updateid;
                // $file_ext = pathinfo($_FILES["updateuserpic"]["name"], PATHINFO_EXTENSION);
                // if (!empty($_FILES['updateuserpic']['name'])) {
                //     // check for valid file to upload 
                //     $file_ext=strtolower($file_ext);
                //     if(!in_array($file_ext, $allowedExts)){
                //         $this->session->set_flashdata('updateclass', 'danger');
                //         $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                //         redirect(site_url().'admin/users/userProfile/'.encode($updateid)); 
                     
                //     }
                //     $img_name = $this->dynamic_model->fileupload('updateuserpic', 'uploads/user', 'Picture');
                // } else {
                //     $img_name = $this->input->post('oldpic'); 
                // }
                $updatedata['category_name'] = $category_name;
                $updatedata['category_type'] = $category_type;
                $updatedata['price'] = $price;
                $updatedata['no_of_days'] = $no_of_days;
                $updatedata['category_parent'] = decode($catid);
                $updatedata['status'] = 'Active';
 
                $this->dynamic_model->insertdata(TABLE_BUSINESS_CATEGORY, $updatedata); 
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('sub_category_add_success'));
                redirect(site_url().'admin/categoryMaster/getSubCat/'.$catid);  
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProblem in Server. Please Try Again');
            redirect(site_url().'admin/categoryMaster/getSubCat/'.$catid);                    
        }     
    }







    /* Show Profile info */
    public function addCat(){
        $header['title'] = $this->lang->line('add_category');
        $this->admintemplates('category/category_add', array(), $header);
    }



    /* User Profile update by admin */
    public function categoryAdd(){
        // check_permission(EDIT,"user_list",1);
        extract($this->input->post());
        // $allowedExts = array("JPG","JPEG","PNG","png","jpeg","jpg");
        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('category_name', 'Category Name', 'required');
            $this->form_validation->set_rules('category_type', 'Category Type', 'required');
            $this->form_validation->set_rules('price', 'Price', 'required', array( 'required' => 'Valid price is required.'));
            $this->form_validation->set_rules('no_of_days', 'No. of days', 'required', array( 'required' => 'Valid no. of days is required.'));
            
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/categoryMaster');
            } else {
                $updatedata = array();
                // $userid = $updateid;
                // $file_ext = pathinfo($_FILES["updateuserpic"]["name"], PATHINFO_EXTENSION);
                // if (!empty($_FILES['updateuserpic']['name'])) {
                //     // check for valid file to upload 
                //     $file_ext=strtolower($file_ext);
                //     if(!in_array($file_ext, $allowedExts)){
                //         $this->session->set_flashdata('updateclass', 'danger');
                //         $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                //         redirect(site_url().'admin/users/userProfile/'.encode($updateid)); 
                     
                //     }
                //     $img_name = $this->dynamic_model->fileupload('updateuserpic', 'uploads/user', 'Picture');
                // } else {
                //     $img_name = $this->input->post('oldpic'); 
                // }
                $updatedata['category_name'] = $category_name;
                $updatedata['category_type'] = $category_type;
                $updatedata['price'] = $price;
                $updatedata['no_of_days'] = $no_of_days;
                $updatedata['category_parent'] = 0;
                $updatedata['status'] = 'Active';
 
                $this->dynamic_model->insertdata(TABLE_BUSINESS_CATEGORY, $updatedata); 
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('category_add_success'));
                redirect(site_url().'admin/categoryMaster');  
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProblem in Server. Please Try Again');
            redirect(site_url().'admin/categoryMaster/editCat/'.encode($updateid));                    
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
    public function block_categories(){
        // check_permission(STATUS,"user_list",1);
        extract($this->input->post());
            $this->form_validation->set_rules('ids', 'ids', 'required', array( 'required' => 'Please select category'));
         
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/categoryMaster');
            } else {
                $ids = $this->input->post('ids'); 
                $where = 'id IN ('.$ids.')';
                $userdata=$this->dynamic_model->getdatafromtable(TABLE_BUSINESS_CATEGORY,$where); 
                if(!empty($userdata)){
                    foreach($userdata as $value){
                            $updatedata['status'] = 'Active';
                            $condition= array('id'=>$value['id']);
                            $this->dynamic_model->updateRowWhere(TABLE_BUSINESS_CATEGORY,$condition,$updatedata);
                    } 
                    $this->session->set_flashdata('updateclass', 'success');
                    $this->session->set_flashdata('updateerror', $this->lang->line('category_active'));
                     redirect(site_url().'admin/categoryMaster'); 
                }else{
                    $this->session->set_flashdata('updateclass', 'danger');
                    $this->session->set_flashdata('updateerror', $this->lang->line('record_not_found'));
                     redirect(site_url().'admin/categoryMaster'); 

                }
              
            }             
    }
   /*
    *  @access: public
    *  @Description: This method is used for profile
    *  @auther: 
    *  @return: void
    */ 
         
    public function unblock_categories(){
        //    check_permission(STATUS,"user_list",1);
           extract($this->input->post());
            $this->form_validation->set_rules('ids', 'ids', 'required', array( 'required' => 'Please select category'));
         
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/categoryMaster');
            } else {
                $ids = $this->input->post('ids'); 
                $where = 'id IN ('.$ids.')';
                $updatedata['status'] = 'Deactive';
                $this->dynamic_model->updateRowWhere(TABLE_BUSINESS_CATEGORY, $where ,$updatedata);
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('category_deactive'));
                 redirect(site_url().'admin/categoryMaster');  
            }           
    
    }

    public function block_subcategories(){
        // check_permission(STATUS,"user_list",1);
        extract($this->input->post());
            $this->form_validation->set_rules('ids', 'ids', 'required', array( 'required' => 'Please select sub category'));
            $this->form_validation->set_rules('blocksubcat', 'Category Id', 'required');
            
         
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/categoryMaster/getSubCat/'.$this->input->post('blocksubcat'));
            } else {
                $ids = $this->input->post('ids'); 
                $where = 'id IN ('.$ids.')';
                $userdata=$this->dynamic_model->getdatafromtable(TABLE_BUSINESS_CATEGORY,$where); 
                if(!empty($userdata)){
                    foreach($userdata as $value){
                            $updatedata['status'] = 'Active';
                            $condition= array('id'=>$value['id']);
                            $this->dynamic_model->updateRowWhere(TABLE_BUSINESS_CATEGORY,$condition,$updatedata);
                    } 
                    $this->session->set_flashdata('updateclass', 'success');
                    $this->session->set_flashdata('updateerror', $this->lang->line('sub_category_active'));
                     redirect(site_url().'admin/categoryMaster/getSubCat/'.$this->input->post('blocksubcat')); 
                }else{
                    $this->session->set_flashdata('updateclass', 'danger');
                    $this->session->set_flashdata('updateerror', $this->lang->line('record_not_found'));
                     redirect(site_url().'admin/categoryMaster/getSubCat/'.$this->input->post('blocksubcat')); 

                }
              
            }             
    }
   /*category_active
sub_category_active
    *  @access: public
    *  @Description: This method is used for profile
    *  @auther: 
    *  @return: void
    */ 
         
    public function unblock_subcategories(){
        //    check_permission(STATUS,"user_list",1);
           extract($this->input->post());
            $this->form_validation->set_rules('ids', 'ids', 'required', array( 'required' => 'Please select category'));

            $this->form_validation->set_rules('unblocksubcat', 'Category Id', 'required');
         
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/categoryMaster/getSubCat/'.$this->input->post('unblocksubcat'));
            } else {
                $ids = $this->input->post('ids'); 
                $where = 'id IN ('.$ids.')';
                $updatedata['status'] = 'Deactive';
                $this->dynamic_model->updateRowWhere(TABLE_BUSINESS_CATEGORY, $where ,$updatedata);
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('sub_category_deactive'));
                 redirect(site_url().'admin/categoryMaster/getSubCat/'.$this->input->post('unblocksubcat'));  
            }           
    
    }


}
