<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Products extends My_Controller {
    private $login_user_id = null;
    public function __construct(){      
        parent::__construct();      
        $this->load->model('dynamic_model');
        $this->load->model('admin_model');
        $this->load->model('users_model');
        $this->load->model('company_model');
        $this->load->model('showrooms_model');
        $this->load->model('products_model');
        $this->lang->load("admin_message", "english");
        if($this->session->userdata('logged_in')){
            $currentuser = getuserdetails();
            $this->login_user_id = $currentuser['id'];
        }
    }
            
    public function companyProducts($user_id=''){
        // check_permission(EDIT,"user_list",1); 
        $uid =  decode($user_id)?decode($user_id):decode(encode($user_id));
        if(!empty($user_id) && !empty($uid)){

            $where = "status ='Active'";
            $userdata=$this->dynamic_model->getdatafromtable('manage_product_categories',$where); 
            $loguserinfo['catList'] = $userdata;
            $loguserinfo['cid'] = $uid;
            $header['title'] = $this->lang->line('title_product_list');
            $this->admintemplates('products/products_list', $loguserinfo, $header);
        } else{
            redirect(base_url('admin/companies'));
        }
    }


    public function productAjaxlist($cid,$catId=""){
        $start         =  $this->input->get('start'); // get promo code Id
        $length        =  $this->input->get('length'); // get promo code Id
        $draw          =  $this->input->get('draw'); // get promo code Id
        $order   =  $this->input->get('order');
        if(!empty($order)){ 
            if($order[0]['column']==2){
                $column_name='category';
            }else if($order[0]['column']==3){
                $column_name='product';               
            }else if($order[0]['column']==4){
                $column_name='product_unit';               
            }else if($order[0]['column']==5){
                $column_name='detail1';               
            }else if($order[0]['column']==6){
                $column_name='detail2';               
            }else if($order[0]['column']==7){
                $column_name='detail3';               
            }else if($order[0]['column']==17){
                $column_name='status';               
            }else{
                $column_name='id';
            }
        }
        $totalRecord      = $this->products_model->productAjaxlist(true,0,0,'','desc',$cid,$catId);
        $getRecordListing = $this->products_model->productAjaxlist(false,$start,$length, $column_name, $order[0]['dir'],$cid,$catId);
        //echo $this->db->last_query(); die();
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
                    $delete_url = base_url('admin/companies/deleteproducts/').$login_user_id;                    
                    
                    $recordListing[$i][0]=   '<input type="checkbox" name="checkAll[]" value="'.($recordData->id).'" class="cb-element" ">';
                    $recordListing[$i][1]=  $srNumber+1;
                    // if(!empty($recordData->thumbnail)){
                    //     $user_pic = base_url('uploads/showroom_media/').$recordData->thumbnail;
                    //  }else{
                    //      $user_pic = base_url('uploads/showroom_media/userdefault.png');
                    //  }
                    // $recordListing[$i][2]= '<img src="'.$user_pic.'" width="40" height="40">';
                    $recordListing[$i][2]= $recordData->category_name;
                    $recordListing[$i][3]= $recordData->product_name;

                    $recordListing[$i][4]= $recordData->product_unit;
                    $recordListing[$i][5]= $recordData->details1;
                    $recordListing[$i][6]= $recordData->details2;

                    $recordListing[$i][7]= $recordData->details3;

                    // $recordListing[$i][5]= $recordData->company_location;
                    // $recordListing[$i][6]= $recordData->info;


                    $showImages = base_url('admin/products/showImages/').$login_user_id."/".encode($cid);                    
                    $showVideos = base_url('admin/products/showVideos/').$login_user_id."/".encode($cid);                    
                    $show360 = base_url('admin/products/show360/').$login_user_id."/".encode($cid);  

                    $showColours = base_url('admin/products/showColours/').$login_user_id."/".encode($cid); 

                    $show3dRenderedImages = base_url('admin/products/show3dRenderedImages/').$login_user_id."/".encode($cid);

                    $actionContent = '';
                    // if(check_permission(EDIT,"user_list")==1){
                    $actionContent .='<a href="'.$showImages.'" title="Product Images" class="btn btn-link">View</a> '; 
                    $recordListing[$i][8]= $actionContent; 

                    $actionContent = '';
                    // if(check_permission(EDIT,"user_list")==1){
                    $actionContent .='<a href="'.$show3dRenderedImages.'" title="Product 3D Rendered Image" class="btn btn-link">View</a> ';                     
                    $recordListing[$i][9]= $actionContent; 



                    $where = "product_list_id ='".$recordData->id."' and type='glb' and status='Active'";
                    $product_images=$this->dynamic_model->getdatafromtable('product_media',$where); 
                    if(!empty($product_images) && count($product_images)>0){
                        $recordListing[$i][10]= '<a href="'.base_url('uploads/company_media/').$product_images[0]['media'].'" title="Product Images" target="_blank" class="btn btn-link">View</a> '; 
                    }else{
                        $recordListing[$i][10]= ''; 
                    }


                    $where = "product_list_id ='".$recordData->id."' and type='usdz' and status='Active'";
                    $product_images=$this->dynamic_model->getdatafromtable('product_media',$where); 
                    if(!empty($product_images) && count($product_images)>0){
                        $recordListing[$i][11]= '<a href="'.base_url('uploads/company_media/').$product_images[0]['media'].'" title="Product Images" target="_blank" class="btn btn-link">View</a> '; 
                    }else{
                        $recordListing[$i][11]= ''; 
                    }


                    $actionContent = '';
                    // if(check_permission(EDIT,"user_list")==1){
                    $actionContent .='<a href="'.$show360.'" title="Product 360 Image" class="btn btn-link">View</a> '; 
                    $recordListing[$i][12]= $actionContent; 


                    $actionContent = '';
                    // if(check_permission(EDIT,"user_list")==1){
                    $actionContent .='<a href="'.$showVideos.'" title="Product Videos" class="btn btn-link">View</a> '; 
                    $recordListing[$i][13]= $actionContent; 


                    $actionContent = '';
                    // if(check_permission(EDIT,"user_list")==1){
                    $actionContent .='<a href="'.$showColours.'" title="Product Colours" class="btn btn-link">View</a> '; 
                    $recordListing[$i][14]= $actionContent; 


                    $where = "product_list_id ='".$recordData->id."' and is_deleted='0'";
                    $product_count=$this->dynamic_model->getdatafromtable('product_enter_count',$where); 

                    $recordListing[$i][15]= !empty($product_count)?count($product_count):0; 

                    $where = "product_list_id ='".$recordData->id."' and is_deleted='0'";
                    $product_count=$this->dynamic_model->getdatafromtable('product_render_icon_taps_count',$where); 

                    $recordListing[$i][16]= !empty($product_count)?count($product_count):0; 


                    $where = "product_list_id ='".$recordData->id."' and is_deleted='0'";
                    $product_count=$this->dynamic_model->getdatafromtable('three_d_modal_count',$where); 

                    $recordListing[$i][17]= !empty($product_count)?count($product_count):0; 

                   
                    $table = 'product_list';
                    $field = 'status';
                    $urls  =  base_url('admin/products/updateStatus'); 
                    $actionContent='';
                        
                            if($recordData->status == "Deactive"){
                                $user_status = "Active";
                                $actionContent .='<a class="btn btn-danger waves-effect btn-width"  href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('inactive').'">'.$this->lang->line('inactive').'</a>';
                            }else{ 
                                $user_status = "Deactive";
                                $actionContent .='<a class="btn btn-active waves-effect btn-width"  href="javascript:void(0);" onclick="check_and_status_change('.$recordData->id.', \''.$user_status.'\', \''.$urls.'\' , \''.$table.'\', \''.$field.'\');" title="'.$this->lang->line('active').'">'.$this->lang->line('active').'</a>';
                            }
                    $recordListing[$i][18]= $actionContent; 

                    //blank for edit button


                    $profile_url = base_url('admin/products/productUpdate/').$login_user_id."/".encode($cid);                    
                    $delete_url = base_url('admin/products/productDelete/').$login_user_id."/".encode($cid);   

                    $actionContent = '';
                    // if(check_permission(EDIT,"user_list")==1){
                    $actionContent .='<a href="'.$profile_url.'" title="Edit" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5"><i class="fa fa-edit"></i></a>'; 
                    $actionContent .='<a  href="javascript:void(0)" id="'.$delete_url.'" title="Delete" class="btn btn-icon waves-effect waves-light fa-new-grey m-b-5 deleteUser"><i class="fa fa-trash"></i></a>'; 
                     // }
                    $recordListing[$i][19]= $actionContent;
                   
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
    public function updateStatusOld() {
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

            if ($userId) {
                $data11 = $this->dynamic_model->getdatafromtable('product_list',  array('id' => $userId )  );
                if ($data11) {
                    $this->users_model->updateDataFromTabel('manage_products', array('status' => $userStatus , ) ,array('id'=>$data11[0]['product_id']));
                }
            }

            $this->users_model->updateDataFromTabel($table, $updateData, $upWhere);
            //echo $this->db->last_query();die;
            $returnData = array('isSuccess' => true);
        } else {
            $returnData = array('isSuccess' => false);
        }
        echo json_encode($returnData);
    }

    /* Show Profile info */


    /* Show Profile info */
    public function showImages($user_id='',$cid){
        // check_permission(EDIT,"user_list",1);
        $uid =  decode($user_id);
        if(!empty($user_id) && !empty($uid)){

                $where = "product_list_id ='".$uid."' and type='image' and status='Active'";
                $product_images=$this->dynamic_model->getdatafromtable('product_media',$where); 
                $loguserinfo['product_images'] = $product_images;
                $loguserinfo['cid'] = $cid;
                $header['title'] = $this->lang->line('product_images');
                $this->admintemplates('products/product-images', $loguserinfo, $header);
        } else{
            redirect(base_url('admin/products/companyProducts/').$cid);
        }
    }    

    public function show3dRenderedImages($user_id='',$cid){
        // check_permission(EDIT,"user_list",1);
        $uid =  decode($user_id);
        if(!empty($user_id) && !empty($uid)){

                $where = "product_list_id ='".$uid."' and status='Active'";
                $product_images=$this->dynamic_model->getdatafromtable('3d_rendered_product_image',$where); 
                $loguserinfo['product_images'] = $product_images;
                $loguserinfo['cid'] = $cid;
                $header['title'] = $this->lang->line('product_3d_rendered_images');
                $this->admintemplates('products/product-3dRenderImages', $loguserinfo, $header);
        } else{
            redirect(base_url('admin/products/companyProducts/').$cid);
        }
    }    


    public function show360($user_id='',$cid){
        // check_permission(EDIT,"user_list",1);
        $uid =  decode($user_id);
        if(!empty($user_id) && !empty($uid)){

                $where = "product_list_id ='".$uid."' and type='360image' and status='Active'";
                $product_360_image=$this->dynamic_model->getdatafromtable('product_media',$where); 
                $loguserinfo['product_360_image'] = $product_360_image;
                $loguserinfo['cid'] = $cid;
                $header['title'] = $this->lang->line('product_360_image');
                $this->admintemplates('products/product-360', $loguserinfo, $header);
        } else{
            redirect(base_url('admin/products/companyProducts/').$cid);
        }
    }  


    public function showVideos($user_id='',$cid){
        // check_permission(EDIT,"user_list",1);
        $uid =  decode($user_id);
        if(!empty($user_id) && !empty($uid)){

                $where = "product_list_id ='".$uid."' and type='video' and status='Active'";
                $product_videos=$this->dynamic_model->getdatafromtable('product_media',$where); 
                $loguserinfo['product_videos'] = $product_videos;
                $loguserinfo['cid'] = $cid;
                $header['title'] = $this->lang->line('product_video');
                $this->admintemplates('products/product-videos', $loguserinfo, $header);
        } else{
            redirect(base_url('admin/products/companyProducts/').$cid);
        }
    }  




    public function deleteCordinates($user_id=''){
        $uid =  decode($user_id);
        if(!empty($user_id) && !empty($uid)){
            $where2 = "id ='".$uid."'";
            $userdata2=$this->dynamic_model->deletedata('img_360_coordinates',$where2); 
            echo $userdata2;
        }
        echo "";
    }  



    public function showColours($user_id='',$cid){
        // check_permission(EDIT,"user_list",1);
        $uid =  decode($user_id);
        if(!empty($user_id) && !empty($uid)){

                $where = "product_list_id ='".$uid."'  and status='Active'";
                $colour_code=$this->dynamic_model->getdatafromtable('product_colour_varities',$where); 
                $loguserinfo['product_colour_varities'] = $colour_code;
                $loguserinfo['cid'] = $cid;
                $header['title'] = $this->lang->line('product_colours');
                $this->admintemplates('products/product-colours', $loguserinfo, $header);
        } else{
            redirect(base_url('admin/products/companyProducts/').$cid);
        }
    }    


    public function addProduct($cid){

            $where = "status ='Active'";
            $userdata=$this->dynamic_model->getdatafromtable('manage_product_categories',$where); 
            $loguserinfo['catList'] = $userdata;

            $loguserinfo['cid'] = $cid;
            $header['title'] = $this->lang->line('title_add_products');
            $this->admintemplates('products/product-add', $loguserinfo, $header);
    }



    public function productUpdate($user_id='',$cid=''){
        // check_permission(EDIT,"user_list",1);
        $uid =  decode($user_id);
        if(!empty($user_id) && !empty($uid)){

                $where = "id ='".$uid."'";
                $userdata=$this->dynamic_model->getdatafromtable('product_list',$where); 
                $loguserinfo['prodListInfo'] = $userdata;

                if(!empty($userdata) && !empty($userdata[0])){
                    $where = "id ='".$userdata[0]["product_id"]."'";
                    $userdata=$this->dynamic_model->getdatafromtable('manage_products',$where); 
                    $loguserinfo['prodInfo'] = $userdata;
                }else{
                    $loguserinfo['prodInfo'] = array();
                }

                $where = "status ='Active'";
                $userdata=$this->dynamic_model->getdatafromtable('manage_product_categories',$where); 
                $loguserinfo['catList'] = $userdata;

                $where = "product_list_id ='".$uid."'";
                $userdata=$this->dynamic_model->getdatafromtable('product_colour_varities',$where); 
                $loguserinfo['colours'] = $userdata;

                $where = "product_list_id ='".$uid."'";
                $userdata=$this->dynamic_model->getdatafromtable('3d_rendered_product_image',$where); 
                $loguserinfo['threed_rendered_product_image'] = $userdata;

                $where = "product_list_id ='".$uid."' and type='image'";
                $userdata=$this->dynamic_model->getdatafromtable('product_media',$where); 
                $loguserinfo['images'] = $userdata;

                $where = "product_list_id ='".$uid."' and type='video'";
                $userdata=$this->dynamic_model->getdatafromtable('product_media',$where); 
                $loguserinfo['videos'] = $userdata;

                $where = "product_list_id ='".$uid."' and type='usdz'";
                $userdata=$this->dynamic_model->getdatafromtable('product_media',$where); 
                $loguserinfo['usdz'] = $userdata;

                $where = "product_list_id ='".$uid."' and type='glb'";
                $userdata=$this->dynamic_model->getdatafromtable('product_media',$where); 
                $loguserinfo['glb'] = $userdata;

                $where = "product_list_id ='".$uid."'";
                $userdata=$this->dynamic_model->getdatafromtable('img_360_coordinates',$where); 
                $loguserinfo['img_360_coordinates'] = $userdata;

                $loguserinfo['cid'] = decode($cid);
                $loguserinfo['product_list_id'] = $uid;
                $header['title'] = $this->lang->line('btn_update_details');
            $this->admintemplates('products/product-update', $loguserinfo, $header);
        } else{
            redirect(base_url('admin/products/companyProducts/').$cid);
        }
    }

    /* Show Profile info */
    public function productDelete($user_id='',$cid){
        // check_permission(EDIT,"user_list",1);
        $uid =  decode($user_id);
        if(!empty($user_id) && !empty($uid)){
            // $cid =  decode($cid); 
            $loguserinfo['userinfo'] = $this->dynamic_model->deletedata('product_list',array('id'=>$uid));
            // $header['title'] = $this->lang->line('btn_update_details');
            redirect(base_url('admin/products/companyProducts/').$cid);
            // $this->admintemplates('users/profile-update', $loguserinfo, $header);
        } else{
            redirect(base_url('admin/products/companyProducts/').$cid);
        }
    }


    // public function productDelete($user_id='',$cid=''){
    //     // check_permission(EDIT,"user_list",1);
    //     $uid =  decode($user_id);
    //     if(!empty($user_id) && !empty($uid)){

    //             $where = "id ='".$uid."'";
    //             $userdata=$this->dynamic_model->getdatafromtable('product_list',$where); 
    //             $loguserinfo['prodListInfo'] = $userdata;

    //             if(!empty($userdata) && !empty($userdata[0])){
    //                 $where = "id ='".$userdata[0]["product_id"]."'";
    //                 $userdata=$this->dynamic_model->getdatafromtable('manage_products',$where); 
    //                 $loguserinfo['prodInfo'] = $userdata;
    //             }else{
    //                 $loguserinfo['prodInfo'] = array();
    //             }

    //             $where = "status ='Active'";
    //             $userdata=$this->dynamic_model->getdatafromtable('manage_product_categories',$where); 
    //             $loguserinfo['catList'] = $userdata;

    //             $where = "product_list_id ='".$uid."'";
    //             $userdata=$this->dynamic_model->getdatafromtable('product_colour_varities',$where); 
    //             $loguserinfo['colours'] = $userdata;

    //             $where = "product_list_id ='".$uid."'";
    //             $userdata=$this->dynamic_model->getdatafromtable('3d_rendered_product_image',$where); 
    //             $loguserinfo['threed_rendered_product_image'] = $userdata;

    //             $where = "product_list_id ='".$uid."' and type='image'";
    //             $userdata=$this->dynamic_model->getdatafromtable('product_media',$where); 
    //             $loguserinfo['images'] = $userdata;

    //             $where = "product_list_id ='".$uid."' and type='video'";
    //             $userdata=$this->dynamic_model->getdatafromtable('product_media',$where); 
    //             $loguserinfo['videos'] = $userdata;

    //             $where = "product_list_id ='".$uid."' and type='usdz'";
    //             $userdata=$this->dynamic_model->getdatafromtable('product_media',$where); 
    //             $loguserinfo['usdz'] = $userdata;

    //             $where = "product_list_id ='".$uid."' and type='glb'";
    //             $userdata=$this->dynamic_model->getdatafromtable('product_media',$where); 
    //             $loguserinfo['glb'] = $userdata;

    //             $where = "product_list_id ='".$uid."'";
    //             $userdata=$this->dynamic_model->getdatafromtable('img_360_coordinates',$where); 
    //             $loguserinfo['img_360_coordinates'] = $userdata;

    //             $loguserinfo['cid'] = decode($cid);
    //             $loguserinfo['product_list_id'] = $uid;
    //             $header['title'] = $this->lang->line('btn_update_details');
    //         $this->admintemplates('products/product-update', $loguserinfo, $header);
    //     } else{
    //         redirect(base_url('admin/products/companyProducts/').$cid);
    //     }
    // }


    public function removeData($type,$cid){
            if($type=="media"){
                $table = "product_media";
            }else if($type=="colour"){
                $table = "product_colour_varities";

                $table2 = "product_media";
                $where2 = "product_colour_varity_id ='".$cid."'";
                $userdata2=$this->dynamic_model->deletedata($table2,$where2); 

            }else if($type=="3d"){
                $table = "3d_rendered_product_image";
            }else{
                $table = "";
            }
            $where = "id ='".$cid."'";
            $userdata=$this->dynamic_model->deletedata($table,$where); 
            echo $userdata;
    }




    public function colourStatus($type,$cid){
            if($type=="active"){
                $status = "Active";
            }else{
                $status = "Deactive";
            }


            $updatedata= array('status'=>$status);

            $condition= array('id'=>$cid);
            $result = $this->dynamic_model->updateRowWhere('product_colour_varities',$condition,$updatedata);

            $condition= array('product_colour_varity_id'=>$cid);
            $result = $this->dynamic_model->updateRowWhere('product_media',$condition,$updatedata);


            echo $result;
    }




    /* User Profile update by admin */
    public function productUpdateSubmit($cid){

        $comid = decode($cid);

        if(!$comid){
            $comid = $cid;
        }

        // check_permission(EDIT,"user_list",1);
        extract($this->input->post());
        $allowedExts = array("JPG","JPEG","PNG","png","jpeg","jpg");
        $allowedVidExts = array("MP4","AVI","3GP","3GPP","mp4","avi","3gp","3gpp");
        $glbModelExts = array("glb","GLB");
        $usdzModelExts = array("usdz","USDZ");

        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('category_id', 'Product Category Id', 'required');
            $this->form_validation->set_rules('product_name', 'Product Name', 'required');
            $this->form_validation->set_rules('product_unit', 'Product Unit', 'required');
            // $this->form_validation->set_rules('updateuserpic', 'update showroom thumbnail', 'required');
            // $this->form_validation->set_rules('updatevideo', 'update showroom video', 'required');
            // $this->form_validation->set_rules('update360pic', 'update showroom 360 image', 'required');
            
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/products/companyProducts/'.$cid);
            } else {
                $updatedata = array();

                // $updatedata['product_name'] = $product_name;
                // $prodId = $this->dynamic_model->insertdata('manage_products', $updatedata); 
                // $updatedata = array();

                $where = "product_name ='".$product_name."'";
                $userdata=$this->dynamic_model->getdatafromtable('manage_products',$where); 

                if(!empty($userdata) && count($userdata)>0){
                    $prodId = $userdata[0]["id"];
                }else{
                    $updatedata['product_name'] = $product_name;
                    $prodId = $this->dynamic_model->insertdata('manage_products', $updatedata); 
                    $updatedata = array();
                }

                $company_id = decode($company_id);

                $updatedata['category_id'] = $category_id;
                $updatedata['product_unit'] = $product_unit;
                $updatedata['product_id'] = $prodId;
                $updatedata['details1'] = $product_details1;
                $updatedata['details2'] = $product_details2;
                $updatedata['details3'] = $product_details3;



                // $updatedata['status'] = 'Active';
                $condition= array('id'=>$product_list_id);
                $this->dynamic_model->updateRowWhere('product_list',$condition,$updatedata);
                $updatedata = array();


                $dataId = $product_list_id;

                // $updateuserpicExists=false;
                // if(!empty($_FILES['updateuserpic']['name']) && count($_FILES["updateuserpic"])>0){

                //     foreach($_FILES['updateuserpic']['name'] as $key => $value){

                //         $file_ext = pathinfo($_FILES["updateuserpic"]["name"][$key], PATHINFO_EXTENSION);
                //         if (!empty($_FILES['updateuserpic']['name'][$key])) {
                //             // check for valid file to upload 
                //             $file_ext=strtolower($file_ext);
                //             if(!in_array($file_ext, $allowedExts)){
                //                 $this->session->set_flashdata('updateclass', 'danger');
                //                 $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                //                 redirect(site_url().'admin/products/productUpdate/'.encode($product_list_id)."/".encode($company_id)); 
                //             }else{
                //                 $updateuserpicExists=true;
                //             }
                //             // $updateuserpic = $this->dynamic_model->fileupload('updateuserpic', 'uploads/showroom_media', 'Picture');
                //         } else {
                //             // $updateuserpic = 'userdefault.png';
                //         }

                //     }
                //     if($updateuserpicExists){

                //         $updateuserpic = $this->dynamic_model->multiple_fileupload('updateuserpic', 'uploads/company_media', 'Picture');

                //         foreach ($updateuserpic as $key => $value) {
                //             // $value[""]
                //             $updatedata = array();
                //             $updatedata['media'] = $value["original_url"];
                //             $updatedata['product_list_id'] = $dataId;
                //             $updatedata['type'] = 'image';
                //             // $updatedata['created_at'] = time();
                //             $this->dynamic_model->insertdata('product_media', $updatedata); 
                //             $updatedata = array();
                //         }

                //     }

                // }

                





                $updateuserrenderpicExists=false;

                if(!empty($_FILES['updateuserrenderpic']['name']) && count($_FILES["updateuserrenderpic"])>0){

                    foreach($_FILES['updateuserrenderpic']['name'] as $key => $value){

                        $file_ext = pathinfo($_FILES["updateuserrenderpic"]["name"][$key], PATHINFO_EXTENSION);
                        if (!empty($_FILES['updateuserrenderpic']['name'][$key])) {
                            // check for valid file to upload 
                            $file_ext=strtolower($file_ext);
                            if(!in_array($file_ext, $allowedExts)){
                                $this->session->set_flashdata('updateclass', 'danger');
                                $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                                redirect(site_url().'admin/products/companyProducts/'.$cid); 
                            }else{
                                $updateuserrenderpicExists=true;
                            }
                            // $updateuserrenderpic = $this->dynamic_model->fileupload('updateuserrenderpic', 'uploads/showroom_media', 'Picture');
                        } else {
                            // $updateuserrenderpic = 'userdefault.png';
                        }

                    }

                    if($updateuserrenderpicExists){

                        $updateuserrenderpic = $this->dynamic_model->multiple_fileupload('updateuserrenderpic', 'uploads/company_media', 'Picture');

                        $imageDetails = $this->input->post('renderPicInfo');

                        foreach ($updateuserrenderpic as $key => $value) {
                            // $value[""]
                            $updatedata = array();
                            $updatedata['image_name'] = $value["original_url"];
                            $updatedata['product_list_id'] = $dataId;
                            $updatedata['image_info'] = $imageDetails[$key];
                            $updatedata['created_on'] = time();
                            $this->dynamic_model->insertdata('3d_rendered_product_image', $updatedata); 
                            $updatedata = array();
                        }

                    }

                }

                




                //$file_ext = pathinfo($_FILES["update360img"]["name"], PATHINFO_EXTENSION);
                if (!empty($_FILES['update360img']['name'])) {
                    $file_ext = pathinfo($_FILES["update360img"]["name"], PATHINFO_EXTENSION);
                    // check for valid file to upload 
                    $file_ext=strtolower($file_ext);
                    if(!in_array($file_ext, $allowedExts)){
                        $this->session->set_flashdata('updateclass', 'danger');
                        $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                        redirect(site_url().'admin/products/companyProducts/'.$cid); 
                     
                    }
                    $img360 = $this->dynamic_model->fileupload('update360img', 'uploads/company_media', 'Picture');

                    $where = "product_list_id ='".$dataId."' and type ='360image'";
                    $this->dynamic_model->deletedata('product_media',$where); 


                    $updatedata = array();
                    $updatedata['media'] = $img360;
                    $updatedata['product_list_id'] = $dataId;
                    $updatedata['type'] = '360image';
                    // $updatedata['created_at'] = time();
                    $this->dynamic_model->insertdata('product_media', $updatedata); 
                    $updatedata = array();

                } else {
                    $img360 = '';
                }




                $where = "product_list_id ='".$dataId."'";
                $this->dynamic_model->deletedata('img_360_coordinates',$where); 

                if(!empty($xval) && count($xval)>0){
                    foreach ($xval as $key => $xvalue) { 
                        if(!empty($xval[$key]) && !empty($yval[$key]) && !empty($zval[$key])){

                            $updatedata = array();
                            $updatedata['product_list_id'] = $dataId;
                            $updatedata['xval'] = $xval[$key];
                            $updatedata['yval'] = $yval[$key];
                            $updatedata['zval'] = $zval[$key];
                            $updatedata['info'] = $coordinate_360_info[$key];
                            $updatedata['created_at'] = time();
                            $colorId = $this->dynamic_model->insertdata('img_360_coordinates', $updatedata); 
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
                        redirect(site_url().'admin/products/companyProducts/'.$cid); 
                    }
                    $vid_name = $this->dynamic_model->fileupload('updatevideo', 'uploads/company_media', 'Video');

                    $where = "product_list_id ='".$dataId."' and type ='video'";
                    $this->dynamic_model->deletedata('product_media',$where); 
                    
                    $vid_thumb_name = $this->dynamic_model->videoupload('updatevideo', 'uploads/company_media');


                    $updatedata = array();
                    $updatedata['media'] = $vid_name;
                    $updatedata['product_list_id'] = $dataId;
                    $updatedata['media_thumbnail'] = $vid_thumb_name['thumb_url'];
                    $updatedata['type'] = 'video';
                    // $updatedata['created_at'] = time();
                    $this->dynamic_model->insertdata('product_media', $updatedata); 
                    $updatedata = array();

                } else {
                    $vid_name = '';
                }







                // $file_ext = pathinfo($_FILES["usdz3dmodel"]["name"], PATHINFO_EXTENSION);
                // if (!empty($_FILES['usdz3dmodel']['name'])) {
                //     // check for valid file to upload 
                //     $file_ext=strtolower($file_ext);
                //     if(!in_array($file_ext, $usdzModelExts)){

                //         // $where = "id ='".$dataId."'";
                //         // $userdata=$this->dynamic_model->deletedata('product_list',$where); 

                //         $this->session->set_flashdata('updateclass', 'danger');
                //         $this->session->set_flashdata('updateerror',  $this->lang->line('file_usdz_required'));
                //         redirect(site_url().'admin/products/productUpdate/'.encode($product_list_id)."/".$cid); 

                //     }

                //     $where = "product_list_id ='".$dataId."' and type ='usdz'";
                //     $this->dynamic_model->deletedata('product_media',$where); 


                //     $usdz3dmodel = $this->dynamic_model->fileupload('usdz3dmodel', 'uploads/company_media', 'Model');

                //     $updatedata = array();
                //     $updatedata['media'] = $usdz3dmodel;
                //     $updatedata['product_list_id'] = $dataId;
                //     $updatedata['type'] = 'usdz';
                //     // $updatedata['created_at'] = time();
                //     $this->dynamic_model->insertdata('product_media', $updatedata); 
                //     $updatedata = array();

                // } else {
                //     $usdz3dmodel = '';
                // }



                // $file_ext = pathinfo($_FILES["glb3dmodel"]["name"], PATHINFO_EXTENSION);
                // if (!empty($_FILES['glb3dmodel']['name'])) {
                //     // check for valid file to upload 
                //     $file_ext=strtolower($file_ext);
                //     if(!in_array($file_ext, $glbModelExts)){

                //         // $where = "id ='".$dataId."'";
                //         // $userdata=$this->dynamic_model->deletedata('product_list',$where); 

                //         $this->session->set_flashdata('updateclass', 'danger');
                //         $this->session->set_flashdata('updateerror',  $this->lang->line('file_glb_required'));
                //         redirect(site_url().'admin/products/productUpdate/'.encode($product_list_id)."/".$cid); 
                     
                //     }

                //     $where = "product_list_id ='".$dataId."' and type ='glb'";
                //     $this->dynamic_model->deletedata('product_media',$where); 


                //     $glb3dmodel = $this->dynamic_model->fileupload('glb3dmodel', 'uploads/company_media', 'Model');

                //     $updatedata = array();
                //     $updatedata['media'] = $glb3dmodel;
                //     $updatedata['product_list_id'] = $dataId;
                //     $updatedata['type'] = 'glb';
                //     // $updatedata['created_at'] = time();
                //     $this->dynamic_model->insertdata('product_media', $updatedata); 
                //     $updatedata = array();

                // } else {
                //     $glb3dmodel = '';
                // }











                // if(!empty($color) && count($color)>0 ){
                //     foreach ($color as $key => $value) {

                //         $updatedata = array();
                //         $updatedata['product_list_id'] = $dataId;
                //         $updatedata['colour_code'] = $value;
                //         // $updatedata['created_at'] = time();
                //         $this->dynamic_model->insertdata('product_colour_varities', $updatedata); 
                //         $updatedata = array();

                        
                //     }
                // }






                if(!empty($existsColor) && count($existsColor)>0 ){
                    foreach ($existsColor as $key => $value) {


                        $where = "product_list_id ='".$dataId."' and colour_code = '".$value."' ";
                        $userdata=$this->dynamic_model->getdatafromtable('product_colour_varities',$where); 

                        if(!empty($userdata) && count($userdata)>0){
                            $colorId = $userdata[0]["id"];
                        }else{
                            $colorId = 0;
                        }



                            $updatedata = array();


                            $updateuserpicExists=false;
                            if(!empty($_FILES[$value]['name']) && count($_FILES[$value])>0){

                                foreach($_FILES[$value]['name'] as $key2 => $value2){

                                    $file_ext = pathinfo($_FILES[$value]["name"][$key2], PATHINFO_EXTENSION);
                                    if (!empty($_FILES[$value]['name'][$key2])) {
                                        // check for valid file to upload 
                                        $file_ext=strtolower($file_ext);
                                        if(!in_array($file_ext, $allowedExts)){
                                            $this->session->set_flashdata('updateclass', 'danger');
                                            $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                                            redirect(site_url().'admin/products/companyProducts/'.$cid); 
                                        }else{
                                            $updateuserpicExists = true;
                                        }
                                        // $updateuserpic = $this->dynamic_model->fileupload('updateuserpic', 'uploads/showroom_media', 'Picture');
                                    } else {
                                        // $updateuserpic = 'userdefault.png';
                                    }

                                }

                                if($updateuserpicExists){

                                    $updateuserpic = $this->dynamic_model->multiple_fileupload($value, 'uploads/company_media', 'Picture');

                                    foreach ($updateuserpic as $key3 => $val) {
                                        // $val[""]
                                        $updatedata = array();
                                        $updatedata['media'] = $val["original_url"];
                                        $updatedata['product_colour_varity_id'] = $colorId;
                                        $updatedata['product_list_id'] = $dataId;
                                        $updatedata['type'] = 'image';
                                        // $updatedata['created_at'] = time();
                                        $this->dynamic_model->insertdata('product_media', $updatedata); 
                                        $updatedata = array();
                                    }

                                }

                            }

                
 








                            $file_ext = pathinfo($_FILES[$value."_usdz"]["name"], PATHINFO_EXTENSION);
                            if (!empty($_FILES[$value."_usdz"]['name'])) {
                                // check for valid file to upload 
                                $file_ext=strtolower($file_ext);
                                // if(!in_array($file_ext, $usdzModelExts)){
                                if(false){

                                    $this->session->set_flashdata('updateclass', 'danger');
                                    $this->session->set_flashdata('updateerror',  $this->lang->line('file_usdz_required'));
                                    redirect(site_url().'admin/products/companyProducts/'.$cid); 

                                }
                                $usdz3dmodel = $this->dynamic_model->fileupload($value."_usdz", 'uploads/company_media', 'Model');

                                $updatedata = array();
                                $updatedata['media'] = $usdz3dmodel;
                                $updatedata['product_colour_varity_id'] = $colorId;
                                $updatedata['product_list_id'] = $dataId;
                                $updatedata['type'] = 'usdz';
                                // $updatedata['created_at'] = time();
                                $this->dynamic_model->insertdata('product_media', $updatedata); 
                                $updatedata = array();

                            } else {
                                $usdz3dmodel = '';
                            }



                            $file_ext = pathinfo($_FILES[$value."_glb"]["name"], PATHINFO_EXTENSION);
                            if (!empty($_FILES[$value."_glb"]['name'])) {
                                // check for valid file to upload 
                                $file_ext=strtolower($file_ext);
                                // if(!in_array($file_ext, $glbModelExts)){
                                if(false){

                                    $this->session->set_flashdata('updateclass', 'danger');
                                    $this->session->set_flashdata('updateerror',  $this->lang->line('file_glb_required'));
                                    redirect(site_url().'admin/products/companyProducts/'.$cid); 
                                 
                                }
                                $glb3dmodel = $this->dynamic_model->fileupload($value."_glb", 'uploads/company_media', 'Model');

                                $updatedata = array();
                                $updatedata['media'] = $glb3dmodel;
                                $updatedata['product_colour_varity_id'] = $colorId;
                                $updatedata['product_list_id'] = $dataId;
                                $updatedata['type'] = 'glb';
                                // $updatedata['created_at'] = time();
                                $this->dynamic_model->insertdata('product_media', $updatedata); 
                                $updatedata = array();

                            } else {
                                $glb3dmodel = '';
                            }









                        $updatedata = array();

                        
                    }
                }








                if(!empty($color) && count($color)>0 ){
                    foreach ($color as $key => $value) {

                        $updatedata = array();
                        $updatedata['product_list_id'] = $dataId;
                        $updatedata['colour_code'] = $value;
                        // $updatedata['created_at'] = time();
                        $colorId = $this->dynamic_model->insertdata('product_colour_varities', $updatedata); 




                            $updatedata = array();


                            $updateuserpicExists=false;
                            if(!empty($_FILES[$value]['name']) && count($_FILES[$value])>0){

                                foreach($_FILES[$value]['name'] as $key2 => $value2){

                                    $file_ext = pathinfo($_FILES[$value]["name"][$key2], PATHINFO_EXTENSION);
                                    if (!empty($_FILES[$value]['name'][$key2])) {
                                        // check for valid file to upload 
                                        $file_ext=strtolower($file_ext);
                                        if(!in_array($file_ext, $allowedExts)){
                                            $this->session->set_flashdata('updateclass', 'danger');
                                            $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                                            redirect(site_url().'admin/products/companyProducts/'.$cid); 
                                        }else{
                                            $updateuserpicExists = true;
                                        }
                                        // $updateuserpic = $this->dynamic_model->fileupload('updateuserpic', 'uploads/showroom_media', 'Picture');
                                    } else {
                                        // $updateuserpic = 'userdefault.png';
                                    }

                                }

                                if($updateuserpicExists){

                                    $updateuserpic = $this->dynamic_model->multiple_fileupload($value, 'uploads/company_media', 'Picture');

                                    foreach ($updateuserpic as $key3 => $val) {
                                        // $val[""]
                                        $updatedata = array();
                                        $updatedata['media'] = $val["original_url"];
                                        $updatedata['product_colour_varity_id'] = $colorId;
                                        $updatedata['product_list_id'] = $dataId;
                                        $updatedata['type'] = 'image';
                                        // $updatedata['created_at'] = time();
                                        $this->dynamic_model->insertdata('product_media', $updatedata); 
                                        $updatedata = array();
                                    }

                                }

                            }

                
 








                            $file_ext = pathinfo($_FILES[$value."_usdz"]["name"], PATHINFO_EXTENSION);
                            if (!empty($_FILES[$value."_usdz"]['name'])) {
                                // check for valid file to upload 
                                $file_ext=strtolower($file_ext);
                                // if(!in_array($file_ext, $usdzModelExts)){
                                if(false){

                                    $this->session->set_flashdata('updateclass', 'danger');
                                    $this->session->set_flashdata('updateerror',  $this->lang->line('file_usdz_required'));
                                    redirect(site_url().'admin/products/companyProducts/'.$cid); 

                                }
                                $usdz3dmodel = $this->dynamic_model->fileupload($value."_usdz", 'uploads/company_media', 'Model');

                                $updatedata = array();
                                $updatedata['media'] = $usdz3dmodel;
                                $updatedata['product_colour_varity_id'] = $colorId;
                                $updatedata['product_list_id'] = $dataId;
                                $updatedata['type'] = 'usdz';
                                // $updatedata['created_at'] = time();
                                $this->dynamic_model->insertdata('product_media', $updatedata); 
                                $updatedata = array();

                            } else {
                                $usdz3dmodel = '';
                            }



                            $file_ext = pathinfo($_FILES[$value."_glb"]["name"], PATHINFO_EXTENSION);
                            if (!empty($_FILES[$value."_glb"]['name'])) {
                                // check for valid file to upload 
                                $file_ext=strtolower($file_ext);
                                // if(!in_array($file_ext, $glbModelExts)){
                                if(false){

                                    $this->session->set_flashdata('updateclass', 'danger');
                                    $this->session->set_flashdata('updateerror',  $this->lang->line('file_glb_required'));
                                    redirect(site_url().'admin/products/companyProducts/'.$cid); 
                                 
                                }
                                $glb3dmodel = $this->dynamic_model->fileupload($value."_glb", 'uploads/company_media', 'Model');

                                $updatedata = array();
                                $updatedata['media'] = $glb3dmodel;
                                $updatedata['product_colour_varity_id'] = $colorId;
                                $updatedata['product_list_id'] = $dataId;
                                $updatedata['type'] = 'glb';
                                // $updatedata['created_at'] = time();
                                $this->dynamic_model->insertdata('product_media', $updatedata); 
                                $updatedata = array();

                            } else {
                                $glb3dmodel = '';
                            }









                        $updatedata = array();

                        
                    }
                }










                // $updatedata['company_id'] = $comid;
                // $updatedata['showroom_name'] = $updatename;
                // $updatedata['thumbnail'] = $updateuserpic;
                // $updatedata['img_360'] = $update360pic;
                // $updatedata['video_url'] = $vid_name;
                // $updatedata['play_video_url'] = $vid_play;
                // $this->dynamic_model->insertdata('manage_showroom_list', $updatedata); 
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('product_update'));
                redirect(site_url().'admin/products/companyProducts/'.$cid);  
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProble in Server. Please Try Again');
            redirect(site_url().'admin/products/companyProducts/'.$cid);                    
        }     
    }





    /* User Profile update by admin */
    public function productAddSubmit($cid){



        $comid = decode($cid);
        // check_permission(EDIT,"user_list",1);
        extract($this->input->post());
        $allowedExts = array("JPG","JPEG","PNG","png","jpeg","jpg");
        $glbModelExts = array("glb","GLB");
        $usdzModelExts = array("usdz","USDZ");

        $allowedVidExts = array("MP4","AVI","3GP","3GPP","mp4","avi","3gp","3gpp");

        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('category_id', 'Product Category Id', 'required');
            $this->form_validation->set_rules('product_name', 'Product Name', 'required');
            $this->form_validation->set_rules('product_unit', 'Product Unit', 'required|trim');
            // $this->form_validation->set_rules('updateuserpic', 'update showroom thumbnail', 'required');
            // $this->form_validation->set_rules('updatevideo', 'update showroom video', 'required');
            // $this->form_validation->set_rules('update360pic', 'update showroom 360 image', 'required');
            
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/products/addProduct/'.$cid);
            } else {
                $updatedata = array();

                // $updatedata['product_name'] = $product_name;
                // $prodId = $this->dynamic_model->insertdata('manage_products', $updatedata); 
                // $updatedata = array();

                $where = "product_name ='".$product_name."'";
                $userdata=$this->dynamic_model->getdatafromtable('manage_products',$where); 

                if(!empty($userdata) && count($userdata)>0){
                    $prodId = $userdata[0]["id"];
                }else{

                    $updatedata['product_name'] = $product_name;
                    $prodId = $this->dynamic_model->insertdata('manage_products', $updatedata); 
                    $updatedata = array();

                }

                $company_id = decode($company_id);

                $updatedata['category_id'] = $category_id;
                $updatedata['company_id'] = $company_id;
                $updatedata['product_id'] = $prodId;
                $updatedata['product_unit'] = $product_unit;
                $updatedata['details1'] = $product_details1;
                $updatedata['details2'] = $product_details2;
                $updatedata['details3'] = $product_details3;
                $updatedata['created_at'] = time();
                $dataId = $this->dynamic_model->insertdata('product_list', $updatedata);  
                $updatedata = array();





                $updateuserrenderpicExists=false;

                if(!empty($_FILES['updateuserrenderpic']['name']) && count($_FILES["updateuserrenderpic"])>0){

                    foreach($_FILES['updateuserrenderpic']['name'] as $key => $value){

                        $file_ext = pathinfo($_FILES["updateuserrenderpic"]["name"][$key], PATHINFO_EXTENSION);
                        if (!empty($_FILES['updateuserrenderpic']['name'][$key])) {
                            // check for valid file to upload 
                            $file_ext=strtolower($file_ext);
                            if(!in_array($file_ext, $allowedExts)){
                                $this->session->set_flashdata('updateclass', 'danger');
                                $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                                redirect(site_url().'admin/products/addProduct/'.$cid); 
                            }else{
                                $updateuserrenderpicExists=true;
                            }
                            // $updateuserrenderpic = $this->dynamic_model->fileupload('updateuserrenderpic', 'uploads/showroom_media', 'Picture');
                        } else {
                            // $updateuserrenderpic = 'userdefault.png';
                        }

                    }

                    if($updateuserrenderpicExists){

                        $updateuserrenderpic = $this->dynamic_model->multiple_fileupload('updateuserrenderpic', 'uploads/company_media', 'Picture');

                        $imageDetails = $this->input->post('renderPicInfo');

                        foreach ($updateuserrenderpic as $key => $value) {
                            // $value[""]
                            $updatedata = array();
                            $updatedata['image_name'] = $value["original_url"];
                            $updatedata['product_list_id'] = $dataId;
                            $updatedata['image_info'] = $imageDetails[$key];
                            $updatedata['created_on'] = time();
                            $this->dynamic_model->insertdata('3d_rendered_product_image', $updatedata); 
                            $updatedata = array();
                        }

                    }

                }

                


                $file_ext = pathinfo($_FILES["update360img"]["name"], PATHINFO_EXTENSION);
                if (!empty($_FILES['update360img']['name'])) {
                    // check for valid file to upload 
                    $file_ext=strtolower($file_ext);
                    if(!in_array($file_ext, $allowedExts)){
                        $this->session->set_flashdata('updateclass', 'danger');
                        $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                        redirect(site_url().'admin/products/addProduct/'.$cid); 
                     
                    }
                    $img360 = $this->dynamic_model->fileupload('update360img', 'uploads/company_media', 'Picture');

                    $updatedata = array();
                    $updatedata['media'] = $img360;
                    $updatedata['product_list_id'] = $dataId;
                    $updatedata['type'] = '360image';
                    // $updatedata['created_at'] = time();
                    $this->dynamic_model->insertdata('product_media', $updatedata); 
                    $updatedata = array();


                    if(!empty($xval) && count($xval)>0){
                        foreach ($xval as $key => $xvalue) { 
                            
                            $updatedata = array();
                            $updatedata['product_list_id'] = $dataId;
                            $updatedata['xval'] = $xval[$key];
                            $updatedata['yval'] = $yval[$key];
                            $updatedata['zval'] = $zval[$key];
                            $updatedata['info'] = $coordinate_360_info[$key];
                            $updatedata['created_at'] = time();
                            $colorId = $this->dynamic_model->insertdata('img_360_coordinates', $updatedata); 
                            $updatedata = array();
                        }
                    }


                } else {
                    $img360 = '';
                }



                $file_ext = pathinfo($_FILES["updatevideo"]["name"], PATHINFO_EXTENSION);
                if (!empty($_FILES['updatevideo']['name'])) {
                    // check for valid file to upload 
                    $file_ext=strtolower($file_ext);
                    if(!in_array($file_ext, $allowedVidExts)){
                        $this->session->set_flashdata('updateclass', 'danger');
                        $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                        redirect(site_url().'admin/products/addProduct/'.$cid); 
                     
                    }
                    $vid_name = $this->dynamic_model->fileupload('updatevideo', 'uploads/company_media', 'Video');

                    $vid_thumb_name = $this->dynamic_model->videoupload('updatevideo', 'uploads/company_media');
                    

                    $updatedata = array();
                    $updatedata['media'] = $vid_name;
                    $updatedata['media_thumbnail'] = $vid_thumb_name['thumb_url'];
                    $updatedata['product_list_id'] = $dataId;
                    $updatedata['type'] = 'video';
                    // $updatedata['created_at'] = time();
                    $this->dynamic_model->insertdata('product_media', $updatedata); 
                    $updatedata = array();

                } else {
                    $vid_name = '';
                }



                // $file_ext = pathinfo($_FILES["usdz3dmodel"]["name"], PATHINFO_EXTENSION);
                // if (!empty($_FILES['usdz3dmodel']['name'])) {
                //     // check for valid file to upload 
                //     $file_ext=strtolower($file_ext);
                //     if(!in_array($file_ext, $usdzModelExts)){
                //         // $where = "id ='".$dataId."'";
                //         // $userdata=$this->dynamic_model->deletedata('product_list',$where); 

                //         $this->session->set_flashdata('updateclass', 'danger');
                //         $this->session->set_flashdata('updateerror',  $this->lang->line('file_usdz_required'));
                //         redirect(site_url().'admin/products/addProduct/'.$cid); 

                //     }
                //     $usdz3dmodel = $this->dynamic_model->fileupload('usdz3dmodel', 'uploads/company_media', 'Model');

                //     $updatedata = array();
                //     $updatedata['media'] = $usdz3dmodel;
                //     $updatedata['product_list_id'] = $dataId;
                //     $updatedata['type'] = 'usdz';
                //     // $updatedata['created_at'] = time();
                //     $this->dynamic_model->insertdata('product_media', $updatedata); 
                //     $updatedata = array();

                // } else {
                //     $usdz3dmodel = '';
                // }



                // $file_ext = pathinfo($_FILES["glb3dmodel"]["name"], PATHINFO_EXTENSION);
                // if (!empty($_FILES['glb3dmodel']['name'])) {
                //     // check for valid file to upload 
                //     $file_ext=strtolower($file_ext);
                //     if(!in_array($file_ext, $glbModelExts)){

                //         // $where = "id ='".$dataId."'";
                //         // $userdata=$this->dynamic_model->deletedata('product_list',$where); 

                //         $this->session->set_flashdata('updateclass', 'danger');
                //         $this->session->set_flashdata('updateerror',  $this->lang->line('file_glb_required'));
                //         redirect(site_url().'admin/products/addProduct/'.$cid); 
                     
                //     }
                //     $glb3dmodel = $this->dynamic_model->fileupload('glb3dmodel', 'uploads/company_media', 'Model');

                //     $updatedata = array();
                //     $updatedata['media'] = $glb3dmodel;
                //     $updatedata['product_list_id'] = $dataId;
                //     $updatedata['type'] = 'glb';
                //     // $updatedata['created_at'] = time();
                //     $this->dynamic_model->insertdata('product_media', $updatedata); 
                //     $updatedata = array();

                // } else {
                //     $glb3dmodel = '';
                // }




                if(!empty($color) && count($color)>0 ){
                    foreach ($color as $key => $value) {

                        $updatedata = array();
                        $updatedata['product_list_id'] = $dataId;
                        $updatedata['colour_code'] = $value;
                        // $updatedata['created_at'] = time();
                        $colorId = $this->dynamic_model->insertdata('product_colour_varities', $updatedata); 




                            $updatedata = array();


                            $updateuserpicExists=false;
                            if(!empty($_FILES[$value]['name']) && count($_FILES[$value])>0){

                                foreach($_FILES[$value]['name'] as $key2 => $value2){

                                    $file_ext = pathinfo($_FILES[$value]["name"][$key2], PATHINFO_EXTENSION);
                                    if (!empty($_FILES[$value]['name'][$key2])) {
                                        // check for valid file to upload 
                                        $file_ext=strtolower($file_ext);
                                        if(!in_array($file_ext, $allowedExts)){
                                            $this->session->set_flashdata('updateclass', 'danger');
                                            $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                                            redirect(site_url().'admin/products/addProduct/'.$cid); 
                                        }else{
                                            $updateuserpicExists = true;
                                        }
                                        // $updateuserpic = $this->dynamic_model->fileupload('updateuserpic', 'uploads/showroom_media', 'Picture');
                                    } else {
                                        // $updateuserpic = 'userdefault.png';
                                    }

                                }

                                if($updateuserpicExists){

                                    $updateuserpic = $this->dynamic_model->multiple_fileupload($value, 'uploads/company_media', 'Picture');

                                    foreach ($updateuserpic as $key3 => $val) {
                                        // $val[""]
                                        $updatedata = array();
                                        $updatedata['media'] = $val["original_url"];
                                        $updatedata['product_colour_varity_id'] = $colorId;
                                        $updatedata['product_list_id'] = $dataId;
                                        $updatedata['type'] = 'image';
                                        // $updatedata['created_at'] = time();
                                        $this->dynamic_model->insertdata('product_media', $updatedata); 
                                        $updatedata = array();
                                    }

                                }

                            }

                









                            $file_ext = pathinfo($_FILES[$value."_usdz"]["name"], PATHINFO_EXTENSION);
                            if (!empty($_FILES[$value."_usdz"]['name'])) {
                                // check for valid file to upload 
                                $file_ext=strtolower($file_ext);
                                // if(!in_array($file_ext, $usdzModelExts)){
                                if(false){

                                    $this->session->set_flashdata('updateclass', 'danger');
                                    $this->session->set_flashdata('updateerror',  $this->lang->line('file_usdz_required'));
                                    redirect(site_url().'admin/products/addProduct/'.$cid); 

                                }
                                $usdz3dmodel = $this->dynamic_model->fileupload($value."_usdz", 'uploads/company_media', 'Model');

                                $updatedata = array();
                                $updatedata['media'] = $usdz3dmodel;
                                $updatedata['product_colour_varity_id'] = $colorId;
                                $updatedata['product_list_id'] = $dataId;
                                $updatedata['type'] = 'usdz';
                                // $updatedata['created_at'] = time();
                                $this->dynamic_model->insertdata('product_media', $updatedata); 
                                $updatedata = array();

                            } else {
                                $usdz3dmodel = '';
                            }



                            $file_ext = pathinfo($_FILES[$value."_glb"]["name"], PATHINFO_EXTENSION);
                            if (!empty($_FILES[$value."_glb"]['name'])) {
                                // check for valid file to upload 
                                $file_ext=strtolower($file_ext);
                                // if(!in_array($file_ext, $glbModelExts)){
                                if(false){

                                    $this->session->set_flashdata('updateclass', 'danger');
                                    $this->session->set_flashdata('updateerror',  $this->lang->line('file_glb_required'));
                                    redirect(site_url().'admin/products/addProduct/'.$cid); 
                                 
                                }
                                $glb3dmodel = $this->dynamic_model->fileupload($value."_glb", 'uploads/company_media', 'Model');

                                $updatedata = array();
                                $updatedata['media'] = $glb3dmodel;
                                $updatedata['product_colour_varity_id'] = $colorId;
                                $updatedata['product_list_id'] = $dataId;
                                $updatedata['type'] = 'glb';
                                // $updatedata['created_at'] = time();
                                $this->dynamic_model->insertdata('product_media', $updatedata); 
                                $updatedata = array();

                            } else {
                                $glb3dmodel = '';
                            }













                        $updatedata = array();

                        
                    }
                }




                // $updatedata['company_id'] = $comid;
                // $updatedata['showroom_name'] = $updatename;
                // $updatedata['thumbnail'] = $updateuserpic;
                // $updatedata['img_360'] = $update360pic;
                // $updatedata['video_url'] = $vid_name;
                // $updatedata['play_video_url'] = $vid_play;
                // $this->dynamic_model->insertdata('manage_showroom_list', $updatedata); 
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('product_add'));
                redirect(site_url().'admin/products/addProduct/'.$cid);  
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProble in Server. Please Try Again');
            redirect(site_url().'admin/products/addProduct/'.$cid);                    
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
