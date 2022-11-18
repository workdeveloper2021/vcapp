<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_video extends My_Controller {

	public function __construct(){
        parent::__construct();
        $this->load->model('dynamic_model');
        $this->load->model('app_model');
        $this->load->model('video_model');
        $this->lang->load("admin_message", "english");
        if($this->session->userdata('logged_in')){
            $currentuser = getuserdetails();
            $this->login_user_id = $currentuser['id'];
        }
    }

    public function index(){
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> '. $this->lang->line('dashboard_breadcrumb') , 'admin'); 
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> '. $this->lang->line('appcontent_breadcrumb') , 'admin/category'); 
        $data=array();
        $header['title'] = 'Video List'; //$this->lang->line('app_videos_list');        
        $this->admintemplates('manage_video/videos_list',$data, $header);
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
            // echo $this->db->last_query();die;
            $returnData = array('isSuccess' => true);
        } else {
            $returnData = array('isSuccess' => false);
        }
        echo json_encode($returnData);
    }

    public function videosAjaxlist(){
        $start         =  $this->input->get('start'); // get promo code Id
        $length        =  $this->input->get('length'); // get promo code Id
        $draw          =  $this->input->get('draw'); // get promo code Id
        $order   =  $this->input->get('order');
        if(!empty($order)){ 
            if($order[0]['column']==1){
                $column_name='category';
            }else if($order[0]['column']==2){
                $column_name='subcategory';
            }else if($order[0]['column']==5){
                $column_name='name';                
            }else if($order[0]['column']==6){
                $column_name='description';                
            }else if($order[0]['column']==7){
                $column_name='duration';                
            }else if($order[0]['column']==8){
                $column_name='is_vimeo';                
            }else if($order[0]['column']==10){
                $column_name='status';                
            }else{
                $column_name='id';
            }
        }
        $this->login_user_id=$this->login_user_id?$this->login_user_id:'';
        $totalRecord      = $this->video_model->videosAjaxlist(true,0,0,'','desc',$this->login_user_id);
        $getRecordListing = $this->video_model->videosAjaxlist(false,$start,$length, $column_name, $order[0]['dir'],$this->login_user_id);
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
                    $recordListing[$i][1]= $recordData->cat;
                    $recordListing[$i][2]= $recordData->subcat;
                    $recordListing[$i][3]= '<img width="100" src='.$recordData->thumbnail.' >';
                    $recordListing[$i][4]= '<a href="'.$recordData->url.'" target="_blank"><b><u>PLAY VIDEO</u></b></a>';
                    $recordListing[$i][5]= $recordData->name;
                    $recordListing[$i][6]= $recordData->description;
                    $recordListing[$i][7]= $recordData->duration.' sec.';
                    $recordListing[$i][8]= $recordData->is_vimeo=='1'?'Yes':'No';
                    $recordListing[$i][9]= get_formated_date($recordData->created_at, 1);
                   
                    $table = TABLE_MANAGE_VIDEOS;
                    $field = 'status';
                    $urls  =  base_url('admin/manage_video/updateStatus');
                    $actionContent='';
                    // if(check_permission(STATUS,"app_videos_list")==1){
                    if($recordData->status == "Deactive"){
                        $user_status = "Active";
                        $actionContent .='<a class="btn btn-danger waves-effect" style="width: 90%;" href="javascript:void(0);" onclick="changestatus('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('inactive').'">'.$this->lang->line('inactive').'</a>';
                    }else{ 
                        $user_status = "Deactive";
                        $actionContent .='<a class="btn btn-active waves-effect" style="width: 90%;" href="javascript:void(0);" onclick="changestatus('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('active').'">'.$this->lang->line('active').'</a>';
                    }
                    // }
                    $recordListing[$i][10]= $actionContent; 




                                       
                    $table = TABLE_MANAGE_VIDEOS;
                    $field = 'is_delete';
                    $urls  =  base_url('admin/manage_video/updateStatus');
                    $actionContent='';
                    // if(check_permission(STATUS,"app_videos_list")==1){
                    if($recordData->is_delete == "0"){
                        $user_status = "1";
                        $actionContent .='<a  class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5" href="javascript:void(0);" onclick="deleteTheRecord('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="Delete"><i class="fa fa-trash" aria-hidden="true"></i></a>';
                    }
                    // }
                    $recordListing[$i][11]= $actionContent; 






                    //blank for edit button
                    // $actionContent = '';
                    // if(check_permission(EDIT,"app_videos_list")==1){
                    // $actionContent .='<a href="'.$plan_url.'" title="Edit" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5"><i class="fa fa-edit"></i></a> '; 
                    // }
                    // $recordListing[$i][6]= $actionContent;  
                    // $recordListing[$i][7]= $actionContent;  
                    // $recordListing[$i][8]= $actionContent;  
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
        
            check_permission(EDIT,"app_category_list",1);
            $data['parent_data']=$this->dynamic_model->getdatafromtable(TABLE_BUSINESS_CATEGORY,array("category_parent"=>0)); 
        
            $header['title'] = $this->lang->line('add_video'); 
            
            $this->admintemplates('manage_video/videos_update',$data, $header);
    }

    public function getSubCategory($catid){
        $data=$this->dynamic_model->getdatafromtable(TABLE_BUSINESS_CATEGORY,array("category_parent"=>$catid)); 
        echo(json_encode($data));
    }

    public function getVimeoVideo($page=1){

        include_once APPPATH.'vendor/autoload.php';
        $client = new Vimeo\Vimeo(VIMEO_CLIENT_ID, VIMEO_CLIENT_SECRET, VIMEO_ACCESS_TOKEN);
        $response = $client->request('/me/videos', ['per_page' => 25,'page'=>$page] , 'GET');
        $resBody = $response["body"]["data"];
        $mainArr = [];
        foreach ($resBody as $key => $value) {
            $obj = array(
                "name" => $value["name"],
                "description" => $value["description"],
                "url" => $value["link"],
                "thumbnail" => $value["pictures"]["sizes"][0]['link'],
                "duration" => $value["duration"]
            );
            array_push($mainArr,$obj);
        }
        echo(json_encode($mainArr));
        
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
    



     public function postVideos() {

        $this->form_validation->set_rules('category_parent', 'Parent category', 'required');
        $this->form_validation->set_rules('category_child', 'Child category', 'required');
        $this->form_validation->set_rules('videos[]', 'Video', 'required|min_length[1]');

        if ($this->form_validation->run() == FALSE){
            $this->session->set_flashdata('updateclass', 'danger');
            $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
            redirect(site_url().'admin/manage_video');
        } else {
            $category = $this->input->post('category_parent');
            $subcategory = $this->input->post('category_child');
            $videos = $this->input->post('videos[]');
            foreach ($videos as $key => $value) {
                $values = explode("/$/$",$value);
                $updatedata = array(
                    "category_id" => $category,
                    "sub_category_id" => $subcategory,
                    "url" => $values[0],
                    "name" => $values[1],
                    "description" => $values[2],
                    "thumbnail" => $values[3],
                    "duration" => $values[4],                
                    "is_vimeo" => '1',
                    "created_at" => time(),
                    "updated_at" => time(),
                    "created_by" => getuserdetails()['id']
                );
                $updateid = $this->dynamic_model->insertdata(TABLE_MANAGE_VIDEOS, $updatedata);
                
            }           
            
            $msg = $this->lang->line('videos_add_success');
            $this->session->set_flashdata('updateclass', 'success');
            $this->session->set_flashdata('updateerror', $msg);
            redirect(site_url().'admin/manage_video');              
        }
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
            $this->video_model->updateDataFromTabel($table, $updateData, $upWhere);
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
