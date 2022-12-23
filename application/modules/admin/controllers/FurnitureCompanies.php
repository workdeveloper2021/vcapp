<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class FurnitureCompanies extends My_Controller {
    private $login_user_id = null;
    public function __construct(){      
        parent::__construct();      
        $this->load->model('dynamic_model');
        $this->load->model('admin_model');
        $this->load->model('users_model');
        $this->load->model('Furniture_model');
        $this->lang->load("admin_message", "english");
        if($this->session->userdata('logged_in')){
            $currentuser = getuserdetails();
            $this->login_user_id = $currentuser['id'];
        }
    }
            
    public function index() {
        // check_permission(VIEW,"user_list",1);
        $header['title'] = $this->lang->line('company_list'); 
        $data['userdata']=$this->dynamic_model->getdatafromtable(TABLE_COMPANIES);       
        $this->admintemplates('furniturecompanies/companies_list',$data, $header);
    }

    public function companyAjaxlist(){
        $start         =  $this->input->get('start'); // get promo code Id
        $length        =  $this->input->get('length'); // get promo code Id
        $draw          =  $this->input->get('draw'); // get promo code Id
        $order   =  $this->input->get('order');
        if(!empty($order)){ 

            if($order[0]['column']==3){
                $column_name = 'company_name';
            }else if($order[0]['column']==5){
                $column_name = 'location';               
            }else if($order[0]['column']==6){
                $column_name = 'info';               
            }else if($order[0]['column']==7){
                $column_name = 'visits';               
            }else if($order[0]['column']==8){
                $column_name = 'showroom_visits';               
            }else if($order[0]['column']==9){
                $column_name = 'status';               
            }else{
                $column_name = 'id';
            }
        }
        $totalRecord      = $this->Furniture_model->companyAjaxlist(true);
        $getRecordListing = $this->Furniture_model->companyAjaxlist(false,$start,$length, $column_name, $order[0]['dir']);
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
                    $profile_url = base_url('admin/FurnitureCompanies/companyprofile/').$login_user_id;
                    $profile_delete = base_url('admin/FurnitureCompanies/companydelete/').$login_user_id;                    
                    $companyshowrooms = base_url('admin/FurnitureShowrooms/companyshowrooms/').$login_user_id;                    
                    $companyproducts = base_url('admin/products/companyProducts/').$login_user_id;                    
                    $companyreatilers = base_url('admin/retailers/companyRetailers/').$login_user_id;                    
                    
                    $recordListing[$i][0]=   '<input type="checkbox" name="checkAll[]" value="'.($recordData->id).'" class="cb-element" ">';
                    $recordListing[$i][1]=  $srNumber+1;
                    if(!empty($recordData->thumbnail)){
                        $user_pic = base_url('uploads/company_media/').$recordData->thumbnail;
                     }else{
                         $user_pic = base_url('uploads/company_media/userdefault.png');
                     }
                    $recordListing[$i][2]= '<img src="'.$user_pic.'" width="40" height="40">';
                    $recordListing[$i][3]= $recordData->company_name;


                     if(!empty($recordData->video_url)){
                        $user_pic = base_url('uploads/company_media/').$recordData->video_url;
                     }else{
                         $user_pic = base_url('uploads/company_media/userdefault.png');
                     }
                    $actionContent = '';
                    $actionContent .='<u><b><a href="'.$user_pic.'" title="Company Video" target="_blank">Company Video</a></b></u>'; 
                     // }
                    $recordListing[$i][4]= $actionContent;


                    $recordListing[$i][5]= $recordData->company_location;
                    $recordListing[$i][6]= substr($recordData->info,0,500).'...';

                    $where = "company_id ='".$recordData->id."'";
                    $userdata=$this->dynamic_model->getdatafromtable('company_enter_count',$where); 

                    $recordListing[$i][7]=  !empty($userdata)?count($userdata):0;

                    $where = "company_id ='".$recordData->id."'";
                    $userdata=$this->dynamic_model->getdatafromtable('companys_showroom_portal_tapped',$where); 

                    $recordListing[$i][8]= !empty($userdata)?count($userdata):0;

                   
                    $table = 'manage_company_furniture';
                    $field = 'status';
                    $urls  =  base_url('admin/companies/updateStatus'); 
                    $actionContent='';
                        
                            if($recordData->status == "Deactive"){
                                $user_status = "Active";
                                $actionContent .='<a class="btn btn-danger waves-effect btn-width"  href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('inactive').'">'.$this->lang->line('inactive').'</a>';
                            }else{ 
                                $user_status = "Deactive";
                                $actionContent .='<a class="btn btn-active waves-effect btn-width"  href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('active').'">'.$this->lang->line('active').'</a>';
                            }

                    $recordListing[$i][9]= $actionContent; 
                    // $recordListing[$i][7]= $actionContent; 
                    //blank for edit button
                    $actionContent = '<div class="row">';
                    // if(check_permission(EDIT,"user_list")==1){ 
                    $actionContent .='<a href="'.$profile_url.'" title="Edit" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5"><i class="fa fa-edit"></i></a> '; 
                    $actionContent .='<a href="'.$profile_delete.'" title="Delete" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5"><i class="fa fa-trash" aria-hidden="true"></i></a>'; 
                    $actionContent .='&nbsp; <a style="margin-top:5px"  href="'.$companyshowrooms.'" title="Companys Showrooms" class="btn btn-info">Manage Showrooms</a> '; 
                    // $actionContent .='&nbsp; <a href="'.$companyproducts.'" title="Companys Products" class="btn btn-info">Manage Products</a> '; 
                    $actionContent .='&nbsp; <a style="margin-top:5px"  href="'.$companyreatilers.'" title="Companys Products" class="btn btn-info">Retailers</a> '; 
                    $actionContent .='</div> '; 
                     // }
                    $recordListing[$i][10]= $actionContent;
                   
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
    public function companyprofile($user_id=''){
        // check_permission(EDIT,"user_list",1);
        $uid =  decode($user_id);
        if(!empty($user_id) && !empty($uid)){

                $where = "id ='".$uid."'";
                $userdata=$this->dynamic_model->getdatafromtable('manage_company_furniture',$where); 
                $where = "status ='Active'";
                $locations=$this->dynamic_model->getdatafromtable('manage_comapny_location',$where); 

            $loguserinfo['companyinfo'] = $userdata;
            $loguserinfo['locations'] = $locations;
            $header['title'] = $this->lang->line('btn_update_details');
            $this->admintemplates('furniturecompanies/company-update', $loguserinfo, $header);
        } else{
            redirect(base_url('admin/companyprofile'));
        }
    }


    public function companydelete($id){
        $uid =  decode($id);
        $this->dynamic_model->deletedata('manage_company_furniture',array('id'=> $uid));
        $this->session->set_flashdata('updateclass', 'success');
        $this->session->set_flashdata('updateerror', 'Company Delete Successfully');
         redirect($_SERVER["HTTP_REFERER"]);       
    }

    public function addCompany(){

                $where = "status ='Active'";
                $locations=$this->dynamic_model->getdatafromtable('manage_comapny_location',$where); 

            $loguserinfo['locations'] = $locations;
            $header['title'] = $this->lang->line('title_add_company');
            $this->admintemplates('furniturecompanies/company-add', $loguserinfo, $header);
    }

    /* User Profile update by admin */
    public function companyUpdate(){
        // check_permission(EDIT,"user_list",1);
        extract($this->input->post());
        $allowedExts = array("JPG","JPEG","PNG","png","jpeg","jpg");
        $allowedVidExts = array("MP4","AVI","3GP","3GPP","mp4","avi","3gp","3gpp");

        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('updatename', 'update company name', 'required');
            $this->form_validation->set_rules('updatelocation', 'update company location', 'required');
            $this->form_validation->set_rules('updateinfo', 'update company info', 'required');
            // $this->form_validation->set_rules('updatelastname', 'updatelastnm', 'required', array( 'required' => $this->lang->line('last_name')));
            
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/FurnitureCompanies/companyprofile/'.encode($updateid));
            } else {
                $updatedata = array();
                $userid = $updateid;
                if ($_FILES['updateuserpic']['size'] >0) {
                   
                    $tmpFilePath2 = $_FILES['updateuserpic']['tmp_name'];

                    $image_file_type2 = pathinfo($_FILES["updateuserpic"]["name"],PATHINFO_EXTENSION);
                     $newFilePath2 = 'image360'.time().rand('0000','9999').'.'.$image_file_type2;
                    if(move_uploaded_file($tmpFilePath2, 'uploads/company_media/'.$newFilePath2)) {
                        $updatedata['thumbnail'] = $newFilePath2;
                    }
                } 

                $file_ext = pathinfo($_FILES["updatevideo"]["name"], PATHINFO_EXTENSION);
                if (!empty($_FILES['updatevideo']['name'])) {
                    // check for valid file to upload 
                    $file_ext=strtolower($file_ext);
                    if(!in_array($file_ext, $allowedVidExts)){
                        $this->session->set_flashdata('updateclass', 'danger');
                        $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                        redirect(site_url().'admin/FurnitureCompanies/companyprofile/'.encode($updateid)); 
                     
                    }
                    $vid_name = $this->dynamic_model->fileupload('updatevideo', 'uploads/company_media', 'Video');
                    if (empty($vid_name)) {
                        $this->session->set_flashdata('updateclass', 'danger');
                        $this->session->set_flashdata('updateerror',  'Error in uploading video');
                        redirect(site_url().'admin/FurnitureCompanies/companyprofile/'.encode($updateid)); 
                    }
                } else {
                    $vid_name = $this->input->post('oldvid');
                }

                $updatedata['company_name'] = $updatename;
                $updatedata['location'] = $updatelocation;
                $updatedata['info'] = $updateinfo;
                $updatedata['video_url'] = $vid_name;
                $this->dynamic_model->updatedata('manage_company_furniture', $updatedata, $userid); 
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('company_update'));
                redirect(site_url().'admin/FurnitureCompanies/companyprofile/'.encode($updateid));  
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProble in Server. Please Try Again');
            redirect(site_url().'admin/FurnitureCompanies/companyprofile/'.encode($updateid));                    
        }     
    }



    /* User Profile update by admin */
    public function companyAddSubmit(){
        // check_permission(EDIT,"user_list",1);
        extract($this->input->post());
        $allowedExts = array("JPG","JPEG","PNG","png","jpeg","jpg");
        $allowedVidExts = array("MP4","AVI","3GP","3GPP","mp4","avi","3gp","3gpp");

        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('updatename', 'update company name', 'required');
            $this->form_validation->set_rules('updatelocation', 'update company location', 'required');
            $this->form_validation->set_rules('updateinfo', 'update company info', 'required');
            // $this->form_validation->set_rules('updatelastname', 'updatelastnm', 'required', array( 'required' => $this->lang->line('last_name')));
            
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/FurnitureCompanies/addCompany');
            } else {
                $updatedata = array();
                if ($_FILES['updateuserpic']['size'] >0) {
                   
                    $tmpFilePath2 = $_FILES['updateuserpic']['tmp_name'];

                    $image_file_type2 = pathinfo($_FILES["updateuserpic"]["name"],PATHINFO_EXTENSION);
                     $newFilePath2 = 'image360'.time().rand('0000','9999').'.'.$image_file_type2;
                    if(move_uploaded_file($tmpFilePath2, 'uploads/company_media/'.$newFilePath2)) {
                        $img_name = $newFilePath2;
                    }
                } else {
                    $img_name = 'userdefault.png';
                }

                $file_ext = pathinfo($_FILES["updatevideo"]["name"], PATHINFO_EXTENSION);
                if (!empty($_FILES['updatevideo']['name'])) {
                    // check for valid file to upload 
                    $file_ext=strtolower($file_ext);
                    if(!in_array($file_ext, $allowedVidExts)){
                        $this->session->set_flashdata('updateclass', 'danger');
                        $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                        redirect(site_url().'admin/FurnitureCompanies/addCompany'); 
                     
                    }
                    $vid_name = $this->dynamic_model->fileupload('updatevideo', 'uploads/company_media', 'Video');
                } else {
                    $vid_name = '';
                }

                $updatedata['company_name'] = $updatename;
                $updatedata['location'] = $updatelocation;
                $updatedata['info'] = $updateinfo;
                $updatedata['thumbnail'] = $img_name;
                $updatedata['video_url'] = $vid_name;
                $company_id = $this->dynamic_model->insertdata('manage_company_furniture', $updatedata); 


                if(!empty($retail_name) && count($retail_name)>0){
                    foreach ($retail_name as $key => $value) {
                        $updatedata = array();
                        $updatedata['name'] = trim($retail_name[$key]);
                        $updatedata['country'] = trim($retailer_country[$key]);
                        $updatedata['city'] = trim($retail_city[$key]);
                        $updatedata['email'] = trim($retail_email[$key]);
                        $updatedata['company_id'] = $company_id;
                        $updatedata['created_date'] = time();
                        $this->dynamic_model->insertdata('company_retailers', $updatedata); 

                    }
                }


                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('company_add'));
                redirect(site_url().'admin/FurnitureCompanies/addCompany');  
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProble in Server. Please Try Again');
            redirect(site_url().'admin/companies/addCompany');                    
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
    public function block_company(){
        // check_permission(STATUS,"user_list",1);
        extract($this->input->post());
            $this->form_validation->set_rules('ids', 'ids', 'required', array( 'required' => 'Please select company'));
         
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/companies');
            } else {
                $ids = $this->input->post('ids'); 
                $where = 'id IN ('.$ids.')';
                $userdata=$this->dynamic_model->getdatafromtable('manage_company_list',$where); 
                if(!empty($userdata)){
                    foreach($userdata as $value){
                            $updatedata['status'] = 'Active';
                            $condition= array('id'=>$value['id']);
                            $this->dynamic_model->updateRowWhere('manage_company_list',$condition,$updatedata);
                    } 
                    $this->session->set_flashdata('updateclass', 'success');
                    $this->session->set_flashdata('updateerror', $this->lang->line('company_active'));
                     redirect(site_url().'admin/companies'); 
                }else{
                    $this->session->set_flashdata('updateclass', 'danger');
                    $this->session->set_flashdata('updateerror', $this->lang->line('record_not_found'));
                     redirect(site_url().'admin/companies'); 

                }
              
            }             
    }
   /*
    *  @access: public
    *  @Description: This method is used for profile
    *  @auther: 
    *  @return: void
    */ 
         
    public function unblock_company(){
           // check_permission(STATUS,"user_list",1);
           extract($this->input->post());
            $this->form_validation->set_rules('ids', 'ids', 'required', array( 'required' => 'Please select company'));
         
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/companies');
            } else {
                $ids = $this->input->post('ids'); 
                $where = 'id IN ('.$ids.')';
                $updatedata['status'] = 'Deactive';
                $this->dynamic_model->updateRowWhere('manage_company_list', $where ,$updatedata);
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('company_deactive'));
                 redirect(site_url().'admin/companies');  
            }           
    
    }


}
