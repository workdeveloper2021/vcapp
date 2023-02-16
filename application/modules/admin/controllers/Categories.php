<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
 
class Categories extends My_Controller {
    private $login_user_id = null;
    public function __construct(){      
        parent::__construct();      
        $this->load->model('dynamic_model');
        $this->load->model('admin_model');
        $this->load->model('users_model');
        $this->load->model('company_model');
        $this->load->model('categories_model');
        $this->load->model('location_model');
        $this->lang->load("admin_message", "english");
        if($this->session->userdata('logged_in')){
            $currentuser = getuserdetails();
            $this->login_user_id = $currentuser['id'];
        }
    }
            
    public function index() {
        // check_permission(VIEW,"user_list",1);
        $header['title'] = $this->lang->line('tb_product_categories'); 

        $where = "status ='Active'";
        $data['userdata']=$this->dynamic_model->getdatafromtable('manage_product_categories',$where); 

        // $resutl = sendEmailCI("praveen.singh926@gmail.com", SITE_TITLE, 'testing', 'testing');
        // print_r($resutl); die;
        $this->admintemplates('categories/category_list',$data, $header);
    }

    public function categoriesAjaxlist(){
        $start         =  $this->input->get('start'); // get promo code Id
        $length        =  $this->input->get('length'); // get promo code Id
        $draw          =  $this->input->get('draw'); // get promo code Id
        $order   =  $this->input->get('order');
        if(!empty($order)){ 
            if($order[0]['column']==2){
                $column_name='catName';
            }else if($order[0]['column']==4){
                $column_name='status';               
            }else{
                $column_name='id';
            }
        }
        $totalRecord      = $this->categories_model->categoriesAjaxlist(true);
        $getRecordListing = $this->categories_model->categoriesAjaxlist(false,$start,$length, $column_name, $order[0]['dir']);
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
                    $profile_url = base_url('admin/categories/categoriesdetails/').$login_user_id;                    
                    if(!empty($recordData->category_thumbnail)){
                        $user_pic = base_url('uploads/category/').$recordData->category_thumbnail;
                     }else{
                         $user_pic = base_url('uploads/category/1576842998User-icon.png');
                     }
                    $user_pic= '<img src="'.$user_pic.'" width="40" height="40">';

                    $recordListing[$i][0]=   '<input type="checkbox" name="checkAll[]" value="'.($recordData->id).'" class="cb-element" ">';
                    $recordListing[$i][1]=  $srNumber+1;

                    $recordListing[$i][2]= $recordData->category_name;

                    $recordListing[$i][3]= $user_pic; 
                   
                    $table = 'manage_product_categories';
                    $field = 'status';
                    $urls  =  base_url('admin/categories/updateStatus'); 
                    $actionContent='';
                        
                            if($recordData->status == "Deactive"){
                                $user_status = "Active";
                                $actionContent .='<a class="btn btn-danger waves-effect btn-width"  href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('inactive').'">'.$this->lang->line('inactive').'</a>';
                            }else{ 
                                $user_status = "Deactive";
                                $actionContent .='<a class="btn btn-active waves-effect btn-width"  href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('active').'">'.$this->lang->line('active').'</a>';
                            }

                    $recordListing[$i][4]= $actionContent; 

                    $actionContent = '';
                    // if(check_permission(EDIT,"user_list")==1){
                    $actionContent .='<a href="'.$profile_url.'" title="Edit" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5"><i class="fa fa-edit"></i></a> '; 
                     // }
                    $recordListing[$i][5]= $actionContent;
                   
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
    public function categoriesdetails($user_id=''){
        // check_permission(EDIT,"user_list",1);
        $uid =  decode($user_id);
        if(!empty($user_id) && !empty($uid)){

            $where = "id='".$uid."'";
            $categories=$this->dynamic_model->getdatafromtable('manage_product_categories',$where); 

            $loguserinfo['categories'] = $categories;
            $header['title'] = $this->lang->line('btn_update_details');

            $this->admintemplates('categories/categories-update', $loguserinfo, $header);
        } else{
            redirect(base_url('admin/categories'));
        }
    }


    public function addCategories(){

            $header['title'] = $this->lang->line('btn_categories_add');

            $this->admintemplates('categories/categories-add', array(), $header);
    }



    /* User Profile update by admin */
    public function categoriesUpdate(){ 
        // check_permission(EDIT,"user_list",1);
        extract($this->input->post());

        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('updatename', 'update category name', 'required');
            // $this->form_validation->set_rules('updatelastname', 'updatelastnm', 'required', array( 'required' => $this->lang->line('last_name')));
            
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/categories/categoriesdetails/'.encode($updateid));
            } else {
                $updatedata = array();
                $userid = $updateid;
                $updatedata['category_name'] = $updatename;


                $allowedExts = array("JPG","JPEG","PNG","png","jpeg","jpg");
                $file_ext = pathinfo($_FILES["thumbnail"]["name"], PATHINFO_EXTENSION);
   
                if (!empty($_FILES['thumbnail']['name'])) {
                    // check for valid file to upload 
                    $file_ext=strtolower($file_ext);
                    if(!in_array($file_ext, $allowedExts)){
                        $this->session->set_flashdata('updateclass', 'danger');
                        $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                        redirect(site_url().'admin/categories/categoriesdetails/'.encode($updateid));
                    }
                    $thumbnail = $this->dynamic_model->fileupload('thumbnail', 'uploads/category', 'Picture');

                    // $where = "product_list_id ='".$dataId."' and type ='360image'";
                    // $this->dynamic_model->deletedata('product_media',$where); 

                    // $updatedata = array();
                    $updatedata['category_thumbnail'] = $thumbnail;

                } else {
                    // $updatedata['category_thumbnail'] = '';
                }



                $this->dynamic_model->updatedata('manage_product_categories', $updatedata, $userid); 
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('category_update'));
                redirect(site_url().'admin/categories/categoriesdetails/'.encode($updateid));  
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProble in Server. Please Try Again');
            redirect(site_url().'admin/categories/categoriesdetails/'.encode($updateid));                    
        }     
    }



    /* User Profile update by admin */
    public function categoriesAddSubmit(){
        // check_permission(EDIT,"user_list",1);
        extract($this->input->post());

        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('updatename', 'add category name', 'required');
            // $this->form_validation->set_rules('updatelastname', 'updatelastnm', 'required', array( 'required' => $this->lang->line('last_name')));
            
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/categories/addCategories');
            } else {
                $updatedata = array();
                $updatedata['category_name'] = $updatename;

                $allowedExts = array("JPG","JPEG","PNG","png","jpeg","jpg");
                $file_ext = pathinfo($_FILES["thumbnail"]["name"], PATHINFO_EXTENSION);
   
                if (!empty($_FILES['thumbnail']['name'])) {
                    // check for valid file to upload 
                    $file_ext=strtolower($file_ext);
                    if(!in_array($file_ext, $allowedExts)){
                        $this->session->set_flashdata('updateclass', 'danger');
                        $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                        redirect(site_url().'admin/categories/addCategories/'); 
                     
                    }
                    $thumbnail = $this->dynamic_model->fileupload('thumbnail', 'uploads/category', 'Picture');

                    // $where = "product_list_id ='".$dataId."' and type ='360image'";
                    // $this->dynamic_model->deletedata('product_media',$where); 

                    // $updatedata = array();
                    $updatedata['category_thumbnail'] = $thumbnail;

                } else {
                    $updatedata['category_thumbnail'] = '';
                }




                $this->dynamic_model->insertdata('manage_product_categories', $updatedata); 
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('category_added'));
                redirect(site_url().'admin/categories');  
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProble in Server. Please Try Again');
            redirect(site_url().'admin/categories');                    
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
            $this->form_validation->set_rules('ids', 'ids', 'required', array( 'required' => 'Please select location'));
         
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/categories');
            } else {
                $ids = $this->input->post('ids'); 
                $where = 'id IN ('.$ids.')';
                $userdata=$this->dynamic_model->getdatafromtable('manage_product_categories',$where); 
                if(!empty($userdata)){
                    foreach($userdata as $value){
                            $updatedata['status'] = 'Active';
                            $condition= array('id'=>$value['id']);
                            $this->dynamic_model->updateRowWhere('manage_product_categories',$condition,$updatedata);
                    } 
                    $this->session->set_flashdata('updateclass', 'success');
                    $this->session->set_flashdata('updateerror', $this->lang->line('categories_active'));
                     redirect(site_url().'admin/categories'); 
                }else{
                    $this->session->set_flashdata('updateclass', 'danger');
                    $this->session->set_flashdata('updateerror', $this->lang->line('record_not_found'));
                     redirect(site_url().'admin/categories'); 

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
           // check_permission(STATUS,"user_list",1);
           extract($this->input->post());
            $this->form_validation->set_rules('ids', 'ids', 'required', array( 'required' => 'Please select location'));
         
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/categories');
            } else {
                $ids = $this->input->post('ids'); 
                $where = 'id IN ('.$ids.')';
                $updatedata['status'] = 'Deactive';
                $this->dynamic_model->updateRowWhere('manage_product_categories', $where ,$updatedata);
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('categories_deactive'));
                 redirect(site_url().'admin/categories');  
            }           
    
    }


}
