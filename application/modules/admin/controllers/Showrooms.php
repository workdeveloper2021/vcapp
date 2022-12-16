<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Showrooms extends My_Controller {
    private $login_user_id = null;
    public function __construct(){      
        parent::__construct();      
        $this->load->model('dynamic_model');
        $this->load->model('admin_model');
        $this->load->model('users_model');
        $this->load->model('company_model');
        $this->load->model('showrooms_model');
        $this->load->model('Images_model');
        $this->lang->load("admin_message", "english");
        if($this->session->userdata('logged_in')){
            $currentuser = getuserdetails();
            $this->login_user_id = $currentuser['id'];
        }
    } 
            
    public function companyshowrooms($user_id=''){
        // check_permission(EDIT,"user_list",1);
        $uid =  decode($user_id); 
        if(!empty($user_id) && !empty($uid)){
            $loguserinfo['cid'] = $uid;
            $header['title'] = $this->lang->line('title_showroom_list');
            $this->admintemplates('showrooms/showrooms_list', $loguserinfo, $header);
        } else{
            redirect(base_url('admin/companies'));
        }
    }


    public function showroomAjaxlist($cid){
        $start         =  $this->input->get('start'); // get promo code Id
        $length        =  $this->input->get('length'); // get promo code Id
        $draw          =  $this->input->get('draw'); // get promo code Id
        $order   =  $this->input->get('order');
        if(!empty($order)){ 
            if($order[0]['column']==3){
                $column_name='showroom_name';
            }else if($order[0]['column']==9){
                $column_name='status';               
            }else{
                $column_name='id';
            }
        }
        $totalRecord      = $this->showrooms_model->showroomAjaxlist(true,0,0,'','desc',$cid);
        $getRecordListing = $this->showrooms_model->showroomAjaxlist(false,$start,$length, $column_name, $order[0]['dir'],$cid);
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
                    $profile_url = base_url('admin/companies/companyprofile/').$login_user_id;                    
                    
                    $recordListing[$i][0]=   '<input type="checkbox" name="checkAll[]" value="'.($recordData->id).'" class="cb-element" ">';
                    $recordListing[$i][1]=  $srNumber+1;
                    if(!empty($recordData->thumbnail)){
                        $user_pic = base_url('uploads/showroom_media/').$recordData->thumbnail;
                     }else{
                         $user_pic = base_url('uploads/showroom_media/userdefault.png');
                     }
                    $recordListing[$i][2]= '<img src="'.$user_pic.'" width="40" height="40">';
                    $recordListing[$i][3]= $recordData->showroom_name;


                     if(!empty($recordData->video_url)){
                        $user_pic = base_url('uploads/showroom_media/').$recordData->video_url;
                     }else{
                         $user_pic = '';
                     }
                    $actionContent = '';
                    $actionContent .='<u><b><a href="'.$user_pic.'" title="Showroom Background Video" target="_blank">Showroom Background Video</a></b></u>'; 
                     // }
                    $recordListing[$i][4]= $actionContent;


                     if(!empty($recordData->play_video_url)){
                        $user_pic = base_url('uploads/showroom_media/').$recordData->play_video_url;
                     }else{
                         $user_pic = '';
                     }
                    $actionContent = '';
                    $actionContent .='<u><b><a href="'.$user_pic.'" title="Showroom Play Video" target="_blank">Showroom Play Video</a></b></u>'; 
                     // }
                    $recordListing[$i][5]= $actionContent;



                   
                    // $recordListing[$i][5]= $recordData->company_location;
                    // $recordListing[$i][6]= $recordData->info;

                   
                    $table = 'manage_showroom_list';
                    $field = 'status';
                    $urls  =  base_url('admin/showrooms/updateStatus'); 
                    $actionContent='';
                        
                            if($recordData->status == "Deactive"){
                                $user_status = "Active";
                                $actionContent .='<a class="btn btn-danger waves-effect btn-width"  href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('inactive').'">'.$this->lang->line('inactive').'</a>';
                            }else{ 
                                $user_status = "Deactive";
                                $actionContent .='<a class="btn btn-active waves-effect btn-width"  href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('active').'">'.$this->lang->line('active').'</a>';
                            }

                    
                    $where = "showroom_id ='".$recordData->id."'";
                    $userdata=$this->dynamic_model->getdatafromtable('companys_showroom_entered_count',$where); 

                    $recordListing[$i][6]= !empty($userdata)?count($userdata):0; 

                    $where = "showroom_id ='".$recordData->id."'";
                    $userdata=$this->dynamic_model->getdatafromtable('view_showroom_video_count',$where); 
                    $recordListing[$i][7]= !empty($userdata)?count($userdata):0; 


                    $recordListing[$i][8]= $actionContent; 
                    // $recordListing[$i][7]= $actionContent; 
                    //blank for edit button


                    $profile_url = base_url('admin/showrooms/showroomprofile/').$login_user_id;
                    $image360 = base_url('admin/showrooms/image360list/').$login_user_id;                   
                    $actionContent = '';
                    // if(check_permission(EDIT,"user_list")==1){
                    $actionContent .='<a href="'.$profile_url.'" title="Edit" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5"><i class="fa fa-edit"></i></a> '; 
                    $actionContent .='<a href="'. $image360 .'" title="Edit" class="btn btn-info">Manage 360images</a> '; 
                     // }
                    $recordListing[$i][9]= $actionContent;
                   
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
    public function showroomprofile($user_id=''){
        // check_permission(EDIT,"user_list",1);
        $uid =  decode($user_id);
        if(!empty($user_id) && !empty($uid)){

                $where = "id ='".$uid."'";
                $userdata=$this->dynamic_model->getdatafromtable('manage_showroom_list',$where); 
                $loguserinfo['showroominfo'] = $userdata;

                $where = "showroom_id ='".$uid."' && is_showrooms_coordinates='1'";
                $userdata2=$this->dynamic_model->getdatafromtable('product',$where); 
                $loguserinfo['product'] = $userdata2;


            $header['title'] = $this->lang->line('btn_update_details');
            $this->admintemplates('showrooms/showroom-update', $loguserinfo, $header);
        } else{
            redirect(base_url('admin/companies'));
        }
    }

    public function addShowrooms($cid){

                // $where = "status ='Active'";
                // $locations=$this->dynamic_model->getdatafromtable('manage_comapny_location',$where); 
        // manage_showroom_list

            $loguserinfo['cid'] = $cid;
            $header['title'] = $this->lang->line('title_add_showroom');
            $this->admintemplates('showrooms/showroom-add', $loguserinfo, $header);
    }

    
    public function deleteCordinates($user_id=''){
        $uid =  decode($user_id);
        if(!empty($user_id) && !empty($uid)){
            $where2 = "id ='".$uid."'";
            $userdata2=$this->dynamic_model->deletedata('product',$where2); 
            echo $userdata2;
        }
        echo "";
    }  


    /* User Profile update by admin */
    public function showroomUpdate(){
        // check_permission(EDIT,"user_list",1);
        extract($this->input->post());
        $allowedExts = array("JPG","JPEG","PNG","png","jpeg","jpg");
        $allowedVidExts = array("MP4","AVI","3GP","3GPP","mp4","avi","3gp","3gpp");

        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('updatename', 'update company name', 'required');
            
            // $this->form_validation->set_rules('updateuserpic', 'update thumbnail', 'required');
            // $this->form_validation->set_rules('update360pic', 'update 360 image', 'required');
            // $this->form_validation->set_rules('updatevideo', 'update background video', 'required');
            // $this->form_validation->set_rules('updateplayvideo', 'update play video', 'required');
            
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/showrooms/companyshowrooms/'.encode($company_id));
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
                        redirect(site_url().'admin/showrooms/companyshowrooms/'.encode($company_id)); 
                    }
                    $img_name = $this->dynamic_model->fileupload('updateuserpic', 'uploads/showroom_media', 'Picture');
                } else {
                    $img_name = $this->input->post('oldpic');
                }


                
                $file_ext = pathinfo($_FILES["update360pic"]["name"], PATHINFO_EXTENSION);
                if (!empty($_FILES['update360pic']['name'])) {
                    // check for valid file to upload 
                    $file_ext=strtolower($file_ext);
                    if(!in_array($file_ext, $allowedExts)){
                        $this->session->set_flashdata('updateclass', 'danger');
                        $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                        redirect(site_url().'admin/showrooms/companyshowrooms/'.encode($company_id)); 
                    }
                    $img_360_name = $this->dynamic_model->fileupload('update360pic', 'uploads/showroom_media', 'Picture');
                } else {
                    $img_360_name = $this->input->post('old360pic');
                }

               // echo $userid ; exit();

                $where = "showroom_id ='".$userid."'";
                $this->dynamic_model->deletedata('product',$where); 

                if(!empty($xval) && count($xval)>0){
                    foreach ($xval as $key => $xvalue) { 
                        if(!empty($xval[$key]) && !empty($yval[$key]) && !empty($zval[$key])){

                            $updatedata = array();
                            $updatedata['showroom_id'] = $userid;
                            $updatedata['is_showrooms_coordinates'] = '1';
                            $updatedata['xval'] = $xval[$key];
                            $updatedata['yval'] = $yval[$key];
                            $updatedata['zval'] = $zval[$key];
                            $updatedata['info'] = $coordinate_360_info[$key];
                            $updatedata['created_at'] = time();
                            $colorId = $this->dynamic_model->insertdata('product', $updatedata); 
                            $updatedata = array();

                        }
                    }
                }

                $file_ext = pathinfo($_FILES["updatevideo"]["name"], PATHINFO_EXTENSION);
                if (!empty($_FILES['updatevideo']['name'])) {
                    // check for valid file to upload 
                    $file_ext=strtolower($file_ext);
                    if(!in_array($file_ext, $allowedVidExts)){
                        $this->session->set_flashdata('updateclass', 'danger');
                        $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                        redirect(site_url().'admin/showrooms/companyshowrooms/'.encode($company_id)); 
                     
                    }
                    $vid_name = $this->dynamic_model->fileupload('updatevideo', 'uploads/showroom_media', 'Video');
                } else {
                    $vid_name = $this->input->post('oldvid');
                }




                $file_ext = pathinfo($_FILES["updateplayvideo"]["name"], PATHINFO_EXTENSION);
                if (!empty($_FILES['updateplayvideo']['name'])) {
                    // check for valid file to upload 
                    $file_ext=strtolower($file_ext);
                    if(!in_array($file_ext, $allowedVidExts)){
                        $this->session->set_flashdata('updateclass', 'danger');
                        $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                        redirect(site_url().'admin/showrooms/companyshowrooms/'.encode($company_id)); 
                     
                    }
                    $vid_play_name = $this->dynamic_model->fileupload('updateplayvideo', 'uploads/showroom_media', 'Video');
                } else {
                    $vid_play_name = $this->input->post('oldplayvid');
                }

                $updatedata['showroom_name'] = $updatename;
                $updatedata['information'] = $information;
                $updatedata['thumbnail'] = $img_name;
                $updatedata['img_360'] = $img_360_name;
                $updatedata['video_url'] = $vid_name;
                $updatedata['play_video_url'] = $vid_play_name;
                $this->dynamic_model->updatedata('manage_showroom_list', $updatedata, $userid); 
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('showroom_update'));
                redirect(site_url().'admin/showrooms/companyshowrooms/'.encode($company_id));  
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProble in Server. Please Try Again');
            redirect(site_url().'admin/showrooms/companyshowrooms/'.encode($company_id));                    
        }     
    }



    /* User Profile update by admin */
    public function showroomAddSubmit($cid){
       
        $comid = decode($cid);
        // check_permission(EDIT,"user_list",1);
        extract($this->input->post());
        $allowedExts = array("JPG","JPEG","PNG","png","jpeg","jpg");
        $allowedVidExts = array("MP4","AVI","3GP","3GPP","mp4","avi","3gp","3gpp");

        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('updatename', 'update showroom name', 'required');
            // $this->form_validation->set_rules('updateuserpic', 'update showroom thumbnail', 'required');
            // $this->form_validation->set_rules('updatevideo', 'update showroom video', 'required');
            // $this->form_validation->set_rules('updateplayvideo', 'update showroom play video', 'required');
            // $this->form_validation->set_rules('update360pic', 'update showroom 360 image', 'required');
            
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/showrooms/addShowrooms/'.$cid);
            } else {
                $updatedata = array();
 
                
                $file_ext = pathinfo($_FILES["updateuserpic"]["name"], PATHINFO_EXTENSION);
                if (!empty($_FILES['updateuserpic']['name'])) {
                    // check for valid file to upload 
                    $file_ext=strtolower($file_ext);
                    if(!in_array($file_ext, $allowedExts)){
                        $this->session->set_flashdata('updateclass', 'danger');
                        $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                        redirect(site_url().'admin/showrooms/addShowrooms/'.$cid); 
                    }
                    $updateuserpic = $this->dynamic_model->fileupload('updateuserpic', 'uploads/showroom_media', 'Picture');
                } else {
                    $updateuserpic = 'userdefault.png';
                }
 
               
                $file_ext = pathinfo($_FILES["updatevideo"]["name"], PATHINFO_EXTENSION);
                if (!empty($_FILES['updatevideo']['name'])) {
                    // check for valid file to upload 
                    $file_ext=strtolower($file_ext);
                    if(!in_array($file_ext, $allowedVidExts)){
                        $this->session->set_flashdata('updateclass', 'danger');
                        $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                        redirect(site_url().'admin/showrooms/addShowrooms/'.$cid); 
                     
                    }
                    $vid_name = $this->dynamic_model->fileupload('updatevideo', 'uploads/showroom_media', 'Video');
                } else {
                    $vid_name = '';
                }



                $file_ext = pathinfo($_FILES["updateplayvideo"]["name"], PATHINFO_EXTENSION);
                if (!empty($_FILES['updateplayvideo']['name'])) {
                    // check for valid file to upload 
                    $file_ext=strtolower($file_ext);
                    if(!in_array($file_ext, $allowedVidExts)){
                        $this->session->set_flashdata('updateclass', 'danger');
                        $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                        redirect(site_url().'admin/showrooms/addShowrooms/'.$cid); 
                     
                    }
                    $vid_play = $this->dynamic_model->fileupload('updateplayvideo', 'uploads/showroom_media', 'Video');
                } else {
                    $vid_play = '';
                }


                $updatedata['company_id'] = $comid;
                $updatedata['showroom_name'] = $updatename;
                $updatedata['information'] = $information;
                $updatedata['thumbnail'] = $updateuserpic;
                // $updatedata['img_360'] = $update360pic;
                $updatedata['video_url'] = $vid_name;
                $updatedata['play_video_url'] = $vid_play;

                $showroomId = $this->dynamic_model->insertdata('manage_showroom_list', $updatedata);
               
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('showroom_add'));
                redirect(site_url().'admin/showrooms/addShowrooms/'.$cid);  
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProble in Server. Please Try Again');
            redirect(site_url().'admin/showrooms/addShowrooms/'.$cid);                    
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

    // 360image

    public function image360list($user_id=''){
        // check_permission(EDIT,"user_list",1);
        $uid =  decode($user_id); 
        if(!empty($user_id) && !empty($uid)){
            $loguserinfo['cid'] = $uid;
            $header['title'] = $this->lang->line('images_360_list');
            $this->admintemplates('showrooms/images-list', $loguserinfo, $header);
        } else{
            redirect(base_url('admin/companies'));
        }
    }

    public function add360image($cid){

                // $where = "status ='Active'";
                // $locations=$this->dynamic_model->getdatafromtable('manage_comapny_location',$where); 
        // manage_showroom_list

            $loguserinfo['cid'] = $cid;
            $header['title'] = $this->lang->line('title_add_360image');
            $this->admintemplates('showrooms/images-add', $loguserinfo, $header);
    }


     public function imageAddSubmit($cid){
        // echo '<pre>';
        // print_r($_POST); die;
        $comid = decode($cid);
        // check_permission(EDIT,"user_list",1);
        extract($this->input->post());
        $allowedExts = array("JPG","JPEG","PNG","png","jpeg","jpg");
        $allowedVidExts = array("MP4","AVI","3GP","3GPP","mp4","avi","3gp","3gpp");

        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('description', 'description', 'required');
            // $this->form_validation->set_rules('updateuserpic', 'update showroom thumbnail', 'required');
            // $this->form_validation->set_rules('updatevideo', 'update showroom video', 'required');
            // $this->form_validation->set_rules('updateplayvideo', 'update showroom play video', 'required');
            // $this->form_validation->set_rules('update360pic', 'update showroom 360 image', 'required');
            
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/showrooms/addShowrooms/'.$cid);
            } else {
                $updatedata = array();

                if (!empty($_FILES['update360pic']['name'])) {
                   $tmpFilePath2 = $_FILES['update360pic']['tmp_name'];

                    $image_file_type2 = pathinfo($_FILES["update360pic"]["name"],PATHINFO_EXTENSION);
                     $newFilePath2 = 'image360'.time().rand('0000','9999').'.'.$image_file_type2;
                    if(move_uploaded_file($tmpFilePath2, 'uploads/showroom_media/'.$newFilePath2)) {
                        $updateuserpic = $newFilePath2;
                    }    
                    // $updateuserpic = $this->dynamic_model->fileupload('update360pic', 'uploads/showroom_media', 'Model');
                } else {
                    $updateuserpic = 'userdefault.png';
                }


                 if (!empty($_FILES['retailer2']['name'])) {
                   $tmpFilePath2 = $_FILES['retailer2']['tmp_name'];

                    $image_file_type2 = pathinfo($_FILES["retailer2"]["name"],PATHINFO_EXTENSION);
                     $newFilePath2 = 'retailerimg'.time().rand('0000','9999').'.'.$image_file_type2;
                    if(move_uploaded_file($tmpFilePath2, 'uploads/showroom_media/'.$newFilePath2)) {
                        $retailer2 = $newFilePath2;
                    }    
                    // $updateuserpic = $this->dynamic_model->fileupload('update360pic', 'uploads/showroom_media', 'Model');
                } else {
                    $retailer2 = 'userdefault.png';
                }


                if(!empty($_FILES['thumbnail']['name'])) {
                   $tmpFilePath2 = $_FILES['thumbnail']['tmp_name'];

                    $image_file_type2 = pathinfo($_FILES["thumbnail"]["name"],PATHINFO_EXTENSION);
                     $newFilePath2 = 'thumbnail'.time().rand('0000','9999').'.'.$image_file_type2;
                    if(move_uploaded_file($tmpFilePath2, 'uploads/showroom_media/'.$newFilePath2)) {
                        $thumbnail = $newFilePath2;
                    }    
                    // $updateuserpic = $this->dynamic_model->fileupload('update360pic', 'uploads/showroom_media', 'Model');
                } else {
                    $thumbnail = 'userdefault.png';
                }

               
               


                $updatedata['showroom_id'] = $comid;
                $updatedata['description'] = $description;
                $updatedata['retaileremail'] = $retaileremail;
                $updatedata['retailer1'] = $retailer;
                $updatedata['reatilerimage'] = $retailer2;
                $updatedata['image360'] = $updateuserpic;
                $updatedata['thumbnail'] = $thumbnail;
                $imgid = $this->dynamic_model->insertdata('showroom_360_image', $updatedata);
                 if(!empty($nos360)){
                        foreach ($nos360 as $key => $xvalue) {
                        $ddt = $_POST['codeno'.$xvalue];
                        //image
                          if(!empty($_FILES['image'.$xvalue]['name'])){
                            for ($j=0; $j <count($_FILES['image'.$xvalue]['name']) ; $j++) {
                                $tmpFilePath1 = $_FILES['image'.$xvalue]['tmp_name'][$j];
                                $image_file_type1 = pathinfo($_FILES['image'.$xvalue]["name"][$j],PATHINFO_EXTENSION);
                                $newFilePath1 = 'image'.time().rand('0000','9999').'.'.$image_file_type1;
                                if(move_uploaded_file($tmpFilePath1, 'uploads/showroom_media/'.$newFilePath1)) {
                                    $images[] = $newFilePath1;
                                }    
                            }
                          }
                           
                       

                          $img12 = '';
                          if(!empty($images)){
                             $img12 = implode(',', $images);   
                          }
                         
                            $updatedata = array();
                            $updatedata['showroom_id'] = $comid;
                            $updatedata['is_showrooms_coordinates'] = '1';
                            $updatedata['image360_id'] =  $imgid;
                            $updatedata['xval'] = $_POST['xval'.$xvalue];
                            $updatedata['yval'] = $_POST['yval'.$xvalue];
                            $updatedata['zval'] = $_POST['zval'.$xvalue];
                            $updatedata['info'] = $_POST['coordinate_360_info'.$xvalue];
                            $updatedata['product_name'] = $_POST['product_name'.$xvalue];
                            $updatedata['image'] = $img12;
                            $updatedata['created_at'] = time();
                           
                            $colorId = $this->dynamic_model->insertdata('product', $updatedata); 
                            
                            $updatedata = array();
                           
                            if(!empty($_POST['modals_color'.$xvalue])){
                                foreach ($_POST['modals_color'.$xvalue] as $key => $modelcolor) {
                                     //3d modals
                      
                                    $modals ='';
                                      if(!empty($_FILES['3dmodals'.$xvalue]['name'][$key])){
                                       
                                            $tmpFilePath2 = $_FILES['3dmodals'.$xvalue]['tmp_name'][$key];

                                            $image_file_type2 = pathinfo($_FILES["3dmodals".$xvalue]["name"][$key],PATHINFO_EXTENSION);
                                             $newFilePath2 = '3dmodals'.time().rand('0000','9999').'.'.$image_file_type2;
                                            if(move_uploaded_file($tmpFilePath2, 'uploads/showroom_media/'.$newFilePath2)) {
                                                $modals = $newFilePath2;
                                            }    
                                        
                                      }
                                   $this->dynamic_model->insertdata('showroom_3d_models', array('modals3d'=>$modals,'color'=>$_POST['modals_color'.$xvalue][$key],'img360_id' =>$colorId)); 
                                           
                                }
                            }
                        }
                    }  
                
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('showroom_add'));
                redirect(site_url().'admin/showrooms/image360list/'.$cid);  
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProble in Server. Please Try Again');
            redirect(site_url().'admin/showrooms/image360list/'.$cid);                    
        }     
    }


    public function imageUpdateSubmit($cid){
        // echo '<pre>';
        // print_r($_POST); die;
        $comid = decode($cid);
        // check_permission(EDIT,"user_list",1);
        extract($this->input->post());
        $allowedExts = array("JPG","JPEG","PNG","png","jpeg","jpg");
        $allowedVidExts = array("MP4","AVI","3GP","3GPP","mp4","avi","3gp","3gpp");

        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('description', 'description', 'required');
            // $this->form_validation->set_rules('updateuserpic', 'update showroom thumbnail', 'required');
            // $this->form_validation->set_rules('updatevideo', 'update showroom video', 'required');
            // $this->form_validation->set_rules('updateplayvideo', 'update showroom play video', 'required');
            // $this->form_validation->set_rules('update360pic', 'update showroom 360 image', 'required');
            
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/showrooms/addShowrooms/'.$cid);
            } else {
                $updatedata = array();

                if ($_FILES['update360pic']['size']>0) {
                   $tmpFilePath2 = $_FILES['update360pic']['tmp_name'];

                    $image_file_type2 = pathinfo($_FILES["update360pic"]["name"],PATHINFO_EXTENSION);
                     $newFilePath2 = 'image360'.time().rand('0000','9999').'.'.$image_file_type2;
                    if(move_uploaded_file($tmpFilePath2, 'uploads/showroom_media/'.$newFilePath2)) {
                        $updatedata['image360'] = $newFilePath2;
                    }    
                    // $updateuserpic = $this->dynamic_model->fileupload('update360pic', 'uploads/showroom_media', 'Model');
                } 


                 if ($_FILES['retailer2']['size']>0) {
                   $tmpFilePath2 = $_FILES['retailer2']['tmp_name'];

                    $image_file_type2 = pathinfo($_FILES["retailer2"]["name"],PATHINFO_EXTENSION);
                     $newFilePath2 = 'retailerimg'.time().rand('0000','9999').'.'.$image_file_type2;
                    if(move_uploaded_file($tmpFilePath2, 'uploads/showroom_media/'.$newFilePath2)) {
                         $updatedata['reatilerimage'] = $newFilePath2;
                    }    
                    // $updateuserpic = $this->dynamic_model->fileupload('update360pic', 'uploads/showroom_media', 'Model');
                }


                if($_FILES['thumbnail']['size']>0) {
                   $tmpFilePath2 = $_FILES['thumbnail']['tmp_name'];

                    $image_file_type2 = pathinfo($_FILES["thumbnail"]["name"],PATHINFO_EXTENSION);
                     $newFilePath2 = 'thumbnail'.time().rand('0000','9999').'.'.$image_file_type2;
                    if(move_uploaded_file($tmpFilePath2, 'uploads/showroom_media/'.$newFilePath2)) {
                         $updatedata['thumbnail'] = $newFilePath2;
                    }    
                    // $updateuserpic = $this->dynamic_model->fileupload('update360pic', 'uploads/showroom_media', 'Model');
                }

               
                


                $updatedata['showroom_id'] = $showroom_id;
                $updatedata['description'] = $description;
                $updatedata['retaileremail'] = $retaileremail;
                $updatedata['retailer1'] = $retailer; 
                $this->dynamic_model->updatedata('showroom_360_image', $updatedata,$comid);
                 if(!empty($nos360)){
                        foreach ($nos360 as $key => $xvalue) {
                        $ddt = $_POST['codeno'.$xvalue];
                        //image
                          if(!empty($_FILES['image'.$xvalue]['name'])){
                            for ($j=0; $j <count($_FILES['image'.$xvalue]['name']) ; $j++) {
                                $tmpFilePath1 = $_FILES['image'.$xvalue]['tmp_name'][$j];
                                $image_file_type1 = pathinfo($_FILES['image'.$xvalue]["name"][$j],PATHINFO_EXTENSION);
                                $newFilePath1 = 'image'.time().rand('0000','9999').'.'.$image_file_type1;
                                if(move_uploaded_file($tmpFilePath1, 'uploads/showroom_media/'.$newFilePath1)) {
                                    $images[] = $newFilePath1;
                                }    
                            }
                          }
                           
                       

                          $img12 = '';
                          if(!empty($images)){
                             $img12 = implode(',', $images);   
                          }
                         
                            $updatedata = array();
                            $updatedata['showroom_id'] = $showroom_id;
                            $updatedata['is_showrooms_coordinates'] = '1';
                            $updatedata['image360_id'] =  $comid;
                            $updatedata['xval'] = $_POST['xval'.$xvalue];
                            $updatedata['yval'] = $_POST['yval'.$xvalue];
                            $updatedata['zval'] = $_POST['zval'.$xvalue];
                            $updatedata['info'] = $_POST['coordinate_360_info'.$xvalue];
                            $updatedata['product_name'] = $_POST['product_name'.$xvalue];
                            $updatedata['image'] = $img12;
                            $updatedata['created_at'] = time();
                            $this->dynamic_model->deletedata('product', array('image360_id'=>$comid)); 
                            
                            $colorId = $this->dynamic_model->insertdata('product', $updatedata); 
                            
                            $updatedata = array();
                           
                            if(!empty($_POST['modals_color'.$xvalue])){
                                foreach ($_POST['modals_color'.$xvalue] as $key => $modelcolor) {
                                     //3d modals
                      
                                    $modals ='';
                                      if(!empty($_FILES['3dmodals'.$xvalue]['name'][$key])){
                                       
                                            $tmpFilePath2 = $_FILES['3dmodals'.$xvalue]['tmp_name'][$key];

                                            $image_file_type2 = pathinfo($_FILES["3dmodals".$xvalue]["name"][$key],PATHINFO_EXTENSION);
                                             $newFilePath2 = '3dmodals'.time().rand('0000','9999').'.'.$image_file_type2;
                                            if(move_uploaded_file($tmpFilePath2, 'uploads/showroom_media/'.$newFilePath2)) {
                                                $modals = $newFilePath2;
                                            }    
                                        
                                      }
                                   $this->dynamic_model->insertdata('showroom_3d_models', array('modals3d'=>$modals,'color'=>$_POST['modals_color'.$xvalue][$key],'img360_id' =>$colorId)); 
                                           
                                }
                            }
                        }
                    }  
                
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('showroom_add'));
                redirect($_SERVER['HTTP_REFERER']);  
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProble in Server. Please Try Again');
            redirect($_SERVER['HTTP_REFERER']);                    
        }     
    }



    public function imageAjaxlist($cid){
        $start         =  $this->input->get('start'); // get promo code Id
        $length        =  $this->input->get('length'); // get promo code Id
        $draw          =  $this->input->get('draw'); // get promo code Id
        $order   =  $this->input->get('order');
        if(!empty($order)){ 
            if($order[0]['column']==3){
                $column_name='showroom_name';
            }else if($order[0]['column']==9){
                $column_name='status';               
            }else{
                $column_name='id';
            }
        }
        $totalRecord      = $this->Images_model->showroomAjaxlist(true,0,0,'','desc',$cid);
        $getRecordListing = $this->Images_model->showroomAjaxlist(false,$start,$length, $column_name, $order[0]['dir'],$cid);
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
                    $profile_url = base_url('admin/companies/companyprofile/').$login_user_id;                    
                    
                    $recordListing[$i][0]=   '<input type="checkbox" name="checkAll[]" value="'.($recordData->id).'" class="cb-element" ">';
                    $recordListing[$i][1]= $recordData->description;
                    if(!empty($recordData->image360)){
                        $user_pic = base_url('uploads/showroom_media/').$recordData->image360;
                     }else{
                        $user_pic = '';
                     }
                    $recordListing[$i][2]= '<img src="'.$user_pic.'" width="40" height="40">';
                     
                   
                   
                    $table = 'manage_showroom_list';
                    $field = 'status';
                    $urls  =  base_url('admin/showrooms/updateStatus'); 
                    $actionContent='';
                        
                            if($recordData->status == "Deactive"){
                                $user_status = "Active";
                                $actionContent .='<a class="btn btn-danger waves-effect btn-width"  href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('inactive').'">'.$this->lang->line('inactive').'</a>';
                            }else{ 
                                $user_status = "Deactive";
                                $actionContent .='<a class="btn btn-active waves-effect btn-width"  href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('active').'">'.$this->lang->line('active').'</a>';
                            }

                    
                    $where = "showroom_id ='".$recordData->id."'";
                    $userdata=$this->dynamic_model->getdatafromtable('companys_showroom_entered_count',$where); 

                  
                    $profile_url = base_url('admin/showrooms/deleteImage/').$login_user_id;

                    $edit_url = base_url('admin/showrooms/editimage360/').$login_user_id;
                                       
                    $actionContent = '';
                    // if(check_permission(EDIT,"user_list")==1){
                    $actionContent .='<a href="'.$profile_url.'" title="Delete" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5"><i class="fa fa-trash"></i></a> ';

                     $actionContent .='<a href="'.$edit_url.'" title="Delete" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5"><i class="fa fa-edit"></i></a> ';  
                  
                     // }
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


    public function deleteImage($user_id=''){
        $uid =  decode($user_id);
        if(!empty($user_id) && !empty($uid)){
            $where2 = "id ='".$uid."'";
            $userdata2=$this->dynamic_model->deletedata('showroom_360_image',$where2); 
            echo $userdata2;
        }
       redirect($_SERVER["HTTP_REFERER"]);
    }  

        public function editimage360($user_id=''){
             $uid =  decode($user_id);
            if(!empty($user_id) && !empty($uid)){
                $where2 = "id ='".$uid."'";
            $loguserinfo['cid'] = $user_id;

               $header['title'] = $this->lang->line('title_add_360image');

               $data['data'] = $this->db->select('*')->where($where2)->get('showroom_360_image')->row_array();

               $this->admintemplates('showrooms/images-edit',$data,$loguserinfo, $header);
            }
        }
   
    public function updateimage360($cid){
        // echo '<pre>';
        // print_r($_POST); die;
        $comid = decode($cid);
        // check_permission(EDIT,"user_list",1);
        extract($this->input->post());
        $allowedExts = array("JPG","JPEG","PNG","png","jpeg","jpg");
        $allowedVidExts = array("MP4","AVI","3GP","3GPP","mp4","avi","3gp","3gpp");

        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('description', 'description', 'required');
            // $this->form_validation->set_rules('updateuserpic', 'update showroom thumbnail', 'required');
            // $this->form_validation->set_rules('updatevideo', 'update showroom video', 'required');
            // $this->form_validation->set_rules('updateplayvideo', 'update showroom play video', 'required');
            // $this->form_validation->set_rules('update360pic', 'update showroom 360 image', 'required');
            
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/showrooms/addShowrooms/'.$cid);
            } else {
                $updatedata = array();

                if ($_FILES['update360pic']['size']>0) {
                   $tmpFilePath2 = $_FILES['update360pic']['tmp_name'];

                    $image_file_type2 = pathinfo($_FILES["update360pic"]["name"],PATHINFO_EXTENSION);
                     $newFilePath2 = 'image360'.time().rand('0000','9999').'.'.$image_file_type2;
                    if(move_uploaded_file($tmpFilePath2, 'uploads/showroom_media/'.$newFilePath2)) {
                        $updateuserpic = $newFilePath2;
                    }    
                    // $updateuserpic = $this->dynamic_model->fileupload('update360pic', 'uploads/showroom_media', 'Model');
                } else {
                    $updateuserpic = 'userdefault.png';
                }

                 if ($_FILES['retailer2']['size'] > 0) {
                   $tmpFilePath2 = $_FILES['retailer2']['tmp_name'];

                    $image_file_type2 = pathinfo($_FILES["retailer2"]["name"],PATHINFO_EXTENSION);
                     $newFilePath2 = 'retailerimg'.time().rand('0000','9999').'.'.$image_file_type2;
                    if(move_uploaded_file($tmpFilePath2, 'uploads/showroom_media/'.$newFilePath2)) {
                        $retailer2 = $newFilePath2;
                    }    
                    // $updateuserpic = $this->dynamic_model->fileupload('update360pic', 'uploads/showroom_media', 'Model');
                } else {
                    $retailer2 = 'userdefault.png';
                }

             

                $updatedata['showroom_id'] = $comid;
                $updatedata['description'] = $description;
                $updatedata['retaileremail'] = $retaileremail;
                $updatedata['retailer1'] = $retailer;
                $updatedata['reatilerimage'] = $retailer2;
                $updatedata['image360'] = $updateuserpic;
                $imgid = $this->dynamic_model->insertdata('showroom_360_image', $updatedata);
                 if(!empty($nos360)){
                        foreach ($nos360 as $key => $xvalue) {
                        $ddt = $_POST['codeno'.$xvalue];
                        //image
                          if(!empty($_FILES['image'.$xvalue]['name'])){
                            for ($j=0; $j <count($_FILES['image'.$xvalue]['name']) ; $j++) {
                                $tmpFilePath1 = $_FILES['image'.$xvalue]['tmp_name'][$j];
                                $image_file_type1 = pathinfo($_FILES['image'.$xvalue]["name"][$j],PATHINFO_EXTENSION);
                                $newFilePath1 = 'image'.time().rand('0000','9999').'.'.$image_file_type1;
                                if(move_uploaded_file($tmpFilePath1, 'uploads/showroom_media/'.$newFilePath1)) {
                                    $images[] = $newFilePath1;
                                }    
                            }
                          }
                           
                       

                          $img12 = '';
                          if(!empty($images)){
                             $img12 = implode(',', $images);   
                          }
                         
                            $updatedata = array();
                            $updatedata['showroom_id'] = $comid;
                            $updatedata['is_showrooms_coordinates'] = '1';
                            $updatedata['image360_id'] =  $imgid;
                            $updatedata['xval'] = $_POST['xval'.$xvalue];
                            $updatedata['yval'] = $_POST['yval'.$xvalue];
                            $updatedata['zval'] = $_POST['zval'.$xvalue];
                            $updatedata['info'] = $_POST['coordinate_360_info'.$xvalue];
                            $updatedata['product_name'] = $_POST['product_name'.$xvalue];
                            $updatedata['image'] = $img12;
                            $updatedata['created_at'] = time();
                           
                            $colorId = $this->dynamic_model->insertdata('product', $updatedata); 
                            
                            $updatedata = array();
                           
                            if(!empty($_POST['modals_color'.$xvalue])){
                                foreach ($_POST['modals_color'.$xvalue] as $key => $modelcolor) {
                                     //3d modals
                      
                                    $modals ='';
                                      if(!empty($_FILES['3dmodals'.$xvalue]['name'][$key])){
                                       
                                            $tmpFilePath2 = $_FILES['3dmodals'.$xvalue]['tmp_name'][$key];

                                            $image_file_type2 = pathinfo($_FILES["3dmodals".$xvalue]["name"][$key],PATHINFO_EXTENSION);
                                             $newFilePath2 = '3dmodals'.time().rand('0000','9999').'.'.$image_file_type2;
                                            if(move_uploaded_file($tmpFilePath2, 'uploads/showroom_media/'.$newFilePath2)) {
                                                $modals = $newFilePath2;
                                            }    
                                        
                                      }
                                   $this->dynamic_model->insertdata('showroom_3d_models', array('modals3d'=>$modals,'color'=>$_POST['modals_color'.$xvalue][$key],'img360_id' =>$colorId)); 
                                           
                                }
                            }
                        }
                    }  
                
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('showroom_add'));
                redirect(site_url().'admin/showrooms/image360list/'.$cid);  
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProble in Server. Please Try Again');
            redirect(site_url().'admin/showrooms/image360list/'.$cid);                    
        }     
    }


}
