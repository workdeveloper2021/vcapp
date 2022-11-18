<?php defined('BASEPATH') OR exit('No direct script access allowed');
//require APPPATH . '/libraries/REST_Controller.php';

/* * ***************Studio.php**********************************
 * @product name    : Signal Health Group Inc
 * @type            : Class
 * @class name      : Api
 * @description     : Class for all the methods , public methods calling from mobile apps.  
 * @author          : Consagous Team 	
 * @url             : https://www.consagous.com/      
 * @support         : aamir.shaikh@consagous.com	
 * @copyright       : Consagous Team	 	
 * ********************************************************** */
class Product extends MX_Controller {
 
	public function __construct() {
		parent::__construct();
		header('Content-Type: application/json');
       
		$this->load->library('form_validation');
		$this->load->library('session');
		$this->load->model('dynamic_model');
		$this->load->model('product_model');
	    $this->load->helper('web_common_helper');
		$language = $this->input->get_request_header('language');
		if($language == "en")
		{
			$this->lang->load("web_message","english");
		}
		else
		{
			$this->lang->load("web_message","english");
		}
	}

    /****************Function Add Products **********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : products_add
     * @description     : Business Products add
     * @param           : null 
     * @return          : null 
     * ********************************************************** */
	public function products_add()
	{
	   $arg   = array();
	   $userdata = web_checkuserid(); 
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{		
		    $this->form_validation->set_rules('product_name','Product name', 'required|trim', array( 'required' => $this->lang->line('product_name_required')));
		    $this->form_validation->set_rules('product_price','product price','required|trim',array( 'required' => $this->lang->line('product_price_required')));
		    $this->form_validation->set_rules('product_desc','Product description','required|trim',array( 'required' => $this->lang->line('product_desc_required')));
		    $this->form_validation->set_rules('quantity','Quantity','required');
		    //$this->form_validation->set_rules('product_details','Product details','required|trim',array( 'required' => $this->lang->line('product_details_required')));
		    $this->form_validation->set_rules('tax1','Tax 1','required',array( 'required' => $this->lang->line('tax1_required')));
		    $this->form_validation->set_rules('tax2','Tax 2','required',array( 'required' => $this->lang->line('tax2_required'))); 
		    if(empty($_FILES['product_images']['name'])){
		    $this->form_validation->set_rules('product_images','Product Images', 'required|trim', array( 'required' => $this->lang->line('product_images_required')));
		    }
			if ($this->form_validation->run() == FALSE)
			{
			  	$arg['status']  = 0;
			  	$arg['error_code'] = 0;
				$arg['error_line']= __line__;
			 	$arg['message'] = get_form_error($this->form_validation->error_array());
			}
			else
			{   
				$userdata = web_checkuserid(); 
				$usid =decode($userdata['data']['id']);
				$time=time();$success = 0;
				$product_name     = $this->input->post('product_name');
				$product_price    = $this->input->post('product_price');
				$product_desc     = $this->input->post('product_desc');
				$quantity     	  = $this->input->post('quantity');
				$product_details  = $this->input->post('product_details');
				$tax1             = $this->input->post('tax1');
				$tax2             = $this->input->post('tax2');
				$tax1_rate        = ($this->input->post('tax1_rate'))?$this->input->post('tax1_rate'):0;
				$tax2_rate        = ($this->input->post('tax2_rate'))?$this->input->post('tax2_rate'):0;
				  //echo is_valid_type("product_images");die;
				if(fileUploadingError("product_images")==false){
				 	$arg['status']     = 0;
		            $arg['error_code']  = HTTP_NOT_FOUND;
					$arg['error_line']= __line__;
					$arg['data']       = array();
					$arg['message']    = $this->lang->line('file_not_valid');
					echo json_encode($arg);exit;
				 }
				if(fileUploadingError("product_images","size")==false){
				 	$arg['status']     = 0;
		            $arg['error_code']  = HTTP_NOT_FOUND;
					$arg['error_line']= __line__;
					$arg['data']       = array();
					$arg['message']    = $this->lang->line('file_size_allow'). $this->lang->line('10_mb');
					echo json_encode($arg);exit;
				 }
                $img_name = $this->dynamic_model->multiple_fileupload("product_images",'uploads/products','Picture');
				//get business Id
				$where = array('status' => 'Active','user_id'=>$usid);
		        $business_data = $this->dynamic_model->getdatafromtable('business',$where,'id');
		        $business_id=(!empty($business_data[0]['id'])) ? $business_data[0]['id'] : 0;
				$productData =   array(
					                'business_id'   =>$business_id,
									'user_id'  		=>$usid,
									'product_name'  =>$product_name,
									'product_id'    =>$time,
									'price'  		=>$product_price,
									'description'   =>$product_desc,
									'quantity'		=>$quantity,
									'details'       =>($product_details)?$product_details :'',
									'tax1'          =>$tax1,
									'tax2'          =>$tax2,
									'tax1_rate'          =>($tax1_rate) ? $tax1_rate:0,
									'tax2_rate'          =>($tax2_rate) ?$tax2_rate:0 ,
									'status'   	    =>"Active",
									'create_dt'   	=>$time,
									'update_dt'   	=>$time
				                   );
				$product_id= $this->dynamic_model->insertdata('business_product',$productData);
				//Insert multiple message in product image
				if(!empty($img_name)){
                $success = 1;
                foreach($img_name as $img_name1){
                    $original_url = $img_name1['original_url'];
                    $product_img = array(
                        'product_id' => $product_id,
                        'image_name' => $original_url
                    );
                    $product_img_id = $this->dynamic_model->insertdata('business_product_images',$product_img);
                 }
              } 
				if($success && $product_id)
		        {
					$arg['status']    = 1;
					$arg['error_code'] = HTTP_OK;
					$arg['error_line']= __line__;
				 	$arg['message']   = $this->lang->line('product_add_succ');
				 	$arg['data']      = [];
		        }else{
		        	$arg['status']     = 0;
		            $arg['error_code']  = HTTP_NOT_FOUND;
					$arg['error_line']= __line__;
					$arg['data']       = array();
					$arg['message']    = $this->lang->line('server_problem');
		        }
		  }	
	    }  
	 echo json_encode($arg);	
	}
	/****************Function Edit Products **********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : products_edit
     * @description     : Business Products edit
     * @param           : null 
     * @return          : null 
     * ********************************************************** */

	public function update_product() {
		$arg   = array();
	   	$userdata = web_checkuserid(); 
	   	if($userdata['status'] != 1){
			$arg = $userdata;
		}
		else
		{
			$_POST = json_decode(file_get_contents("php://input"), true); 
			if ($_POST) {
				$this->form_validation->set_rules('product_id','Product Id', 'required|trim', array( 'required' => $this->lang->line('product_name_required')));
				// $this->form_validation->set_rules('product_name','Product name', 'required|trim', array( 'required' => $this->lang->line('product_name_required')));
				$this->form_validation->set_rules('product_price','product price','required|trim',array( 'required' => $this->lang->line('product_price_required')));
				// $this->form_validation->set_rules('product_desc','Product description','required|trim',array( 'required' => $this->lang->line('product_desc_required')));
				$this->form_validation->set_rules('quantity','Quantity','required');
				// $this->form_validation->set_rules('product_details','Product details','required|trim',array( 'required' => $this->lang->line('product_details_required')));
				// $this->form_validation->set_rules('tax1','Tax 1','required',array( 'required' => $this->lang->line('tax1_required')));
				// $this->form_validation->set_rules('tax2','Tax 2','required',array( 'required' => $this->lang->line('tax2_required'))); 
				// $this->form_validation->set_rules('status','Status','required',array( 'required' => $this->lang->line('status_req')));
				/* if(empty($_FILES['product_images']['name'])){
					$this->form_validation->set_rules('product_images','Product Images', 'required|trim', array( 'required' => $this->lang->line('product_images_required')));
				} */
				if ($this->form_validation->run() == FALSE)
				{
					$arg['status']  = 0;
					$arg['error_code'] = 0;
					$arg['error_line']= __line__;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{
					$userdata = web_checkuserid(); 
					$usid 				=	decode($userdata['data']['id']);
					$time 			  	= time();
					$success 			= 0;
					$product_id 		= decode($this->input->post('product_id'));
					$product_price    	= $this->input->post('product_price');
					$quantity     	  	= $this->input->post('quantity');
					// $status             = $this->input->post('status');
					// $status				= ($status == 'Active') ? $status : (($status == 'Deactive')  ? $status : (($status == 'Disable') ? $status : 'no'));

					$where = array('user_id'=>$usid);
					$business_data = $this->dynamic_model->getdatafromtable('business',$where,'id');
					$business_id=(!empty($business_data[0]['id'])) ? $business_data[0]['id'] : 0;
					$productInfo = $this->dynamic_model->getdatafromtable('business_product', array('id' => $product_id, 'business_id' => $business_id), '*');
					if (!$productInfo) {
						$arg['status']     = 0;
						$arg['error_code']  = HTTP_NOT_FOUND;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = 'Invalid Request';
						echo json_encode($arg);exit;
					} else {
						$productQuantity = $productInfo[0]['quantity'];
						if ($productQuantity > $quantity) {
							$arg['status']     = 0;
							$arg['error_code']  = HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['data']       = array();
							$arg['message']    = 'Invalid Quantity';
							echo json_encode($arg);exit;
						} else {
							$productData =   array(
								'price'  		=>$product_price,
								'quantity'		=>$quantity,
								'update_dt'   	=>$time
							);
							if ($this->input->post('status')) {
								$status             = $this->input->post('status');
								$status				= ($status == 'Active') ? $status : 'Deactive';
								$productData['status'] = $status;
							}
							$this->dynamic_model->updateRowWhere('business_product', array('id' => $product_id), $productData);
							$arg['status']    = 1;
							$arg['error_code'] = HTTP_OK;
							$arg['error_line']= __line__;
							$arg['message']   = $this->lang->line('product_update_succ');
							$arg['data']      = [];
						}
					}
				}
			}
			
		}
		echo json_encode($arg);
	}
    /****************Function Get products list**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : product_list
     * @description     : product list 
                           purpose, 
     					    
     * @param           : null 
     * @return          : null 
     * ********************************************************** */
	public function products_list()
	{
	   $arg = array();
	   $userdata = web_checkuserid(); 
	  // print_r($userdata);die;
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{		
	      $_POST = json_decode(file_get_contents("php://input"), true); 
		  if($_POST)
		  {
		    $this->form_validation->set_rules('page_no', 'Page No', 'required|numeric',array(
					'required' => $this->lang->line('page_no'),
					'numeric' => $this->lang->line('page_no_numeric'),
				));
			if($this->form_validation->run() == FALSE)
			{
			  	$arg['status']  = 0;
			  	$arg['error_code'] = 0;
				$arg['error_line']= __line__;
			 	$arg['message'] = get_form_error($this->form_validation->error_array());
			}
			else
			{ 
				$response=$imgarr=array();
				$page_no= (!empty($this->input->post('page_no'))) ? $this->input->post('page_no') : "1";	
				$page_no= $page_no-1;
				$limit    = config_item('page_data_limit'); 
				//$limit    =2; 
				$offset = $limit * $page_no;
				$where=array("business_id"=>decode($userdata['data']['business_id']),"status"=>"Active");
				$product_data = $this->dynamic_model->getdatafromtable('business_product',$where,"*",$limit, $offset,'create_dt');
				$i=0;$j=0;
				if(!empty($product_data)){
				    foreach($product_data as $value) 
		            {
		            	$productdata['product_id']    = encode($value['id']);
		            	$productdata['product_name']   = $value['product_name'];
		            	$productdata['productId']   = $value['product_id'];
		            	$productdata['product_price']   = $value['price'];
		            	$productdata['product_status']   = $value['status'];
		            	$productdata['product_description'] = $value['description'];
		            	$productdata['product_details'] = $value['details'];
		            	$productdata['tax1'] = $value['tax1'];
		            	$productdata['tax2'] = $value['tax2'];
		            	$productdata['tax1_rate'] = $value['tax1_rate'];
		            	$productdata['tax2_rate'] = $value['tax2_rate'];
		            	$image_datas =$this->product_model->get_product_images($value['id']);
		            	//$image_data = $this->dynamic_model->getdatafromtable('business_product_images',array("product_id"=>$value['id']));
		            	//echo $this->db->last_query();
		            	 
		            	// if(!empty($image_data)){
            //           	foreach($image_data as $value1){
            //           	    //if(image_check($value1['image_name'],base_url().'uploads/product_img/',1)!==false){	
            //           	      $imgdata['product_image_id']=encode($value1['id']);
            //           	      $imgdata['image_name']=base_url().'uploads/product_img/'.$value1['image_name'] ;   
            //            	      $imgarr[$j++]	        = $imgdata;
            //            	    // }
            //            	  }
            //            	}
                       	$productdata['product_images'] = $image_datas;
		            	$response[]	        = $productdata;
		            }
					$arg['status']     = 1;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = $response;
					$arg['message']    = $this->lang->line('record_found');
				}else{
					$arg['status']     = 0;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = array();
				 	$arg['message']    = $this->lang->line('record_not_found');	
				}
		    }
		  }
		}	
		
	   echo json_encode($arg);
	}
	/****************Function products details**********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : product_details
     * @description     : product details,      					    
     * @param           : null 
     * @return          : null 
     * ********************************************************** */
	public function products_details()
	{
		$arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
			$userdata = web_checkuserid(); 
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{		
		      $_POST = json_decode(file_get_contents("php://input"), true); 
			  if($_POST)
			  {
			    $this->form_validation->set_rules('product_id','Product name', 'required|trim',array('required'=>$this->lang->line('product_id_required')));
				if($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{ 
					$response=$imgarr=array();
					$product_id= decode($this->input->post('product_id'));
					$where=array("id"=>$product_id);
					$product_data = $this->dynamic_model->getdatafromtable('business_product',$where);
					//echo $this->db->last_query();die; 
					if(!empty($product_data)){
					    foreach($product_data as $value) 
			            {
			            	$productdata['product_id']    = encode($value['id']);
			            	$productdata['product_name']   = $value['product_name'];
			            	$productdata['product_price']   = $value['price'];
			            	$productdata['product_status']   = $value['status'];
							$productdata['product_description'] = $value['description'];
							$productdata['product_quantity'] = $value['quantity'];
			            	$image_data = $this->dynamic_model->getdatafromtable('business_product_images',array("product_id"=>$value['id']));
			            	if(!empty($image_data)){
			            	$i=0;
                          	foreach($image_data as $value1){
                          	    if(image_check($value1['image_name'],base_url().'uploads/products/',1)!==false){	
                          	      $imgdata['product_image_id']=encode($value1['id']);
                          	      $imgdata['image_name']=base_url().'uploads/products/'.$value1['image_name'] ;   
                           	      $imgarr[$i++]	        = $imgdata;
                           	    }
                           	  }
                           	}
                           	$productdata['product_images'] = $imgarr;
			            	$response[]	        = $productdata;
			            }	
						$arg['status']     = 1;
						$arg['error_code']  = HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = $response[0];
						$arg['message']    = $this->lang->line('record_found');
					}else{
						$arg['status']     = 0;
						$arg['error_code']  = HTTP_OK;
						$arg['error_line']= __line__;
						$arg['data']       = array();
					 	$arg['message']    = $this->lang->line('record_not_found');	
					}
			    }
			  }
			}	
		}		
	   echo json_encode($arg);
	}
    /****************Function update products **********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : products_update
     * @description     : Business Products update
     * @param           : null 
     * @return          : null 
     * ********************************************************** */
	public function products_update()
	{
		$arg   = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
		   $userdata = web_checkuserid(); 
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{		
			    $this->form_validation->set_rules('product_id','Product name', 'required|trim', array( 'required' => $this->lang->line('product_id_required')));
			    $this->form_validation->set_rules('product_name','Product name', 'required|trim', array( 'required' => $this->lang->line('product_name_required')));
			    $this->form_validation->set_rules('product_price','product price','required|trim',array( 'required' => $this->lang->line('product_price_required')));
			    $this->form_validation->set_rules('product_desc','Product description','required|trim',array( 'required' => $this->lang->line('product_desc_required')));
				if($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{   
					$userdata = web_checkuserid(); 
					$usid =decode($userdata['data']['id']);
					$time=time();
					$product_id       = decode($this->input->post('product_id'));
					$product_name     = $this->input->post('product_name');
					$product_price    = $this->input->post('product_price');
					$product_desc     = $this->input->post('product_desc');
					$remove_image_ids = $this->input->post('remove_image_ids');
                    //get product details
					$where = array('id'=>$product_id);
					$product_data = $this->dynamic_model->getdatafromtable('business_product',$where,'id');
			        if(!empty($product_data)){
                    if(!empty($_FILES['product_images']['name'])){        
						if(fileUploadingError("product_images")==false){
						 	$arg['status']     = 0;
				            $arg['error_code']  = HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['data']       = array();
							$arg['message']    = $this->lang->line('file_not_valid');
							echo json_encode($arg);exit;
						 }
						if(fileUploadingError("product_images","size")==false){
						 	$arg['status']     = 0;
				            $arg['error_code']  = HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['data']       = array();
							$arg['message']    = $this->lang->line('file_size_allow'). $this->lang->line('10_mb');
							echo json_encode($arg);exit;
						 }
	                     $img_name = $this->dynamic_model->multiple_fileupload("product_images",'uploads/products','Picture');
	                    if(!empty($img_name)){
	                    $success = 1;
	                    foreach($img_name as $img_name1){
	                        $original_url = $img_name1['original_url'];
	                        $product_img = array(
	                            'product_id' => $product_id,
	                            'image_name' => $original_url
	                        );
	                        $product_img_id = $this->dynamic_model->insertdata('business_product_images',$product_img);
	                      }
	                    } 
                    }
				 	    //Remove product images
						if(!empty($remove_image_ids)){
						  $remove_arr=multiple_decode_ids($remove_image_ids,1);
						  $remove_img_data=$this->product_model->get_remove_images($remove_arr,$product_id);
                          if(!empty($remove_img_data)){
                          	foreach ($remove_img_data as $value){
                          	  $this->dynamic_model->deletedata('business_product_images',array("id"=>$value['id'],"product_id"=>$value['product_id']));
						      unlink('./uploads/products/'.$value['image_name']);
                           	  }
                            }
					    }
				 	   //Update product data
						$productData =   array(
											'product_name'  =>$product_name,
											'price'  		=>$product_price,
											'details'       =>$product_desc,
											'update_dt'   	=>$time
						                   );
						$product_id= $this->dynamic_model->updateRowWhere('business_product',$where,$productData);
						if($product_id)
				        {
							$arg['status']    = 1;
							$arg['error_code'] = HTTP_OK;
							$arg['error_line']= __line__;
						 	$arg['message']   = $this->lang->line('product_update_succ');
						 	$arg['data']      = [];
				        }else{
				        	$arg['status']     = 0;
				            $arg['error_code']  = HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['data']       = array();
							$arg['message']    = $this->lang->line('server_problem');
				        }
			        }else{
			        	$arg['status']     = 0;
			            $arg['error_code']  = HTTP_NOT_FOUND;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('something_wrong');
			        }
			  }	
		    }  
       }
	 echo json_encode($arg);	
    }
    /****************Function Remove products **********************************
     * @type            : Function
     * @Author          : Arpit
     * @function name   : products_update
     * @description     : Business Products update
     * @param           : null 
     * @return          : null 
     * ********************************************************** */
	public function products_status_change()
	{
		$arg   = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
		   $userdata = web_checkuserid(); 
		   if($userdata['status'] != 1){
			 $arg = $userdata;
			}
			else
			{		
			    $_POST = json_decode(file_get_contents("php://input"), true); 
				if($_POST)
				{
			    $this->form_validation->set_rules('product_id','Product name', 'required|trim', array( 'required' => $this->lang->line('product_id_required')));
			    $this->form_validation->set_rules('product_status','Product status', 'required|trim', array( 'required' => $this->lang->line('product_status_required')));
				if($this->form_validation->run() == FALSE)
				{
				  	$arg['status']  = 0;
				  	$arg['error_code'] = 0;
					$arg['error_line']= __line__;
				 	$arg['message'] = get_form_error($this->form_validation->error_array());
				}
				else
				{   
					$userdata = web_checkuserid(); 
					$usid =decode($userdata['data']['id']);
					$time=time();
					$product_id       = decode($this->input->post('product_id'));
					$product_status   = $this->input->post('product_status');
                    //get business Id
					$where = array('id'=>$product_id);
					$product_data = $this->dynamic_model->getdatafromtable('business_product',$where,'id');
			        if(!empty($product_data)){ 
				 	   //Update product status
			        	if($product_status=='Active'){
			        		$status='Deactive';
			        		$msg=$this->lang->line('product_active_msg');
			        	}else{
			        		$status='Active';
			        		$msg=$this->lang->line('product_deactive_msg');
			        	}
						$productData =   array('status'=>$status);
						$product_update= $this->dynamic_model->updateRowWhere('business_product',$where,$productData);
						if($product_update)
				        {
							$arg['status']    = 1;
							$arg['error_code'] = HTTP_OK;
							$arg['error_line']= __line__;
						 	$arg['message']   = $msg;
						 	$arg['data']      = [];
				        }else{
				        	$arg['status']     = 0;
				            $arg['error_code']  = HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['data']       = array();
							$arg['message']    = $this->lang->line('server_problem');
				        }
			        }else{
			        	$arg['status']     = 0;
			            $arg['error_code']  = HTTP_NOT_FOUND;
						$arg['error_line']= __line__;
						$arg['data']       = array();
						$arg['message']    = $this->lang->line('something_wrong');
			        }
			    }
			   }	
		    }  
       }
	 echo json_encode($arg);	
    }
    /*********Function Get products purchase customer list*****
     * @type            : Function
     * @Author          : Arpit
     * @function name   : product_list
     * @description     : product list 
                           purpose, 
     					    
     * @param           : null 
     * @return          : null 
     * ********************************************************** */
	public function customer_product_list()
	{
	   $arg = array();
	   $userdata = web_checkuserid(); 
	  // print_r($userdata);die;
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{		
	      $_POST = json_decode(file_get_contents("php://input"), true); 
		  if($_POST)
		  {
		    $this->form_validation->set_rules('page_no', 'Page No', 'required|numeric',array(
					'required' => $this->lang->line('page_no'),
					'numeric' => $this->lang->line('page_no_numeric'),
				));
			if($this->form_validation->run() == FALSE)
			{
			  	$arg['status']  = 0;
			  	$arg['error_code'] = 0;
				$arg['error_line']= __line__;
			 	$arg['message'] = get_form_error($this->form_validation->error_array());
			}
			else
			{ 
				$response=$imgarr=array();
				$page_no= (!empty($this->input->post('page_no'))) ? $this->input->post('page_no') : "1";	
				$page_no= $page_no-1;
				$limit    = config_item('page_data_limit'); 
				//$limit    =1; 
				$offset = $limit * $page_no;
				// $user_token='UzdNeXRsSXl0RFJYc2dZQQ==';
				// echo $userid = decode(base64_decode($user_token));die;
				$business_id=decode($userdata['data']['business_id']);
				$where=array("business_id"=>$business_id,"service_type"=>3);
				$product_purchase_data = $this->dynamic_model->getdatafromtable('user_booking',$where,'*',$limit,$offset,'create_dt','DESC');
				if(!empty($product_purchase_data))
				{
				    foreach($product_purchase_data as $value){
				     // get products details
	                $where2 = array('id'=>$value['service_id'],'status' => 'Active');
		            $product_data = $this->dynamic_model->getdatafromtable('business_product',$where2);
		            $service_id=(!empty($product_data[0]['id'])) ? $product_data[0]['id'] : '';
		            $product_name=(!empty($product_data[0]['product_name'])) ? $product_data[0]['product_name'] : 0;
		            $desc=(!empty($product_data[0]['details'])) ? $product_data[0]['details'] : '';
		            $product_img =$this->product_model->get_product_images($value['service_id']);
		            // get transaction id
		            $where3 = array('id'=>$value['transaction_id']);
		            $trx_data = $this->dynamic_model->getdatafromtable('transactions',$where3);	
		            // get users information
		            $where4 = array('id'=>$value['user_id']);
		            $user_data = $this->dynamic_model->getdatafromtable('user',$where4);	
	            	$productData['id'] = encode($value['id']); 
	            	$productData['product_id']=encode($value['service_id']); 
	            	$productData['product_name'] = $product_name;
	            	$productData['decription']  = $desc;
	            	$productData['order_id']= !empty($trx_data[0]['trx_id']) ? $trx_data[0]['trx_id'] :'';
	            	$productData['product_images']  = $product_img;
	            	$productData['amount']  = $value['amount'];
	            	$productData['sub_total']  = $value['sub_total'];
	            	$productData['quantity']  =$value['quantity'];
	            	$productData['category']  ='';
	            	$productData['status']  =$value['status'];
	            	$productData['customer_name']   = ucwords($user_data[0]['name'].' '.$user_data[0]['lastname']);
	            	$productData['email']  = $user_data[0]['email'];
	            	$productData['profile_img']  = base_url().'uploads/user/'.$user_data[0]['profile_img'];
	            	$productData['country_code'] = $user_data[0]['country_code'];
	            	$productData['mobile'] = $user_data[0]['mobile'];
	            	$productData['date_of_birth'] = $user_data[0]['date_of_birth'];
	            	$productData['gender'] = $user_data[0]['gender'];
	            	$response[]	  = $productData;
                   }
					$arg['status']     = 1;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = $response;
					$arg['message']    = $this->lang->line('record_found');
				}else{
					$arg['status']     = 0;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = array();
				 	$arg['message']    = $this->lang->line('record_not_found');	
				}
		    }
		  }
		}	
		
	   echo json_encode($arg);
	}
	 /*********Function Get products purchase customer details*****
     * @type            : Function
     * @Author          : Arpit
     * @function name   : customer_product_details
     * @description     : customer product details  
                           purpose, 
     					    
     * @param           : null 
     * @return          : null 
     * ********************************************************** */
	public function customer_product_details()
	{
	   $arg = array();
	   $userdata = web_checkuserid(); 
	   if($userdata['status'] != 1){
		 $arg = $userdata;
		}
		else
		{		
	      $_POST = json_decode(file_get_contents("php://input"), true); 
		  if($_POST)
		  {
		    $this->form_validation->set_rules('product_book_id', 'product book id', 'required',array(
					'required' => $this->lang->line('product_book_id_required')
				));
			if($this->form_validation->run() == FALSE)
			{
			  	$arg['status']  = 0;
			  	$arg['error_code'] = 0;
				$arg['error_line']= __line__;
			 	$arg['message'] = get_form_error($this->form_validation->error_array());
			}
			else
			{ 
				$response  = $imgarr = array();
				$product_book_id  = decode($this->input->post('product_book_id'));
				$business_id = decode($userdata['data']['business_id']);
				$where = array('id'=>$product_book_id,"business_id"=>$business_id,"service_type"=>3);
				$product_purchase = $this->dynamic_model->getdatafromtable('user_booking',$where); 
				// echo json_encode($where); exit;

				if(!empty($product_purchase))
				{
				     // get products details
	                $where2 = array('id'=>$product_purchase[0]['service_id'],'status' => 'Active');
		            $product_data = $this->dynamic_model->getdatafromtable('business_product',$where2);
		            $service_id=(!empty($product_data[0]['id'])) ? $product_data[0]['id'] : '';
		            $product_name=(!empty($product_data[0]['product_name'])) ? $product_data[0]['product_name'] : 0;
		            $desc=(!empty($product_data[0]['details'])) ? $product_data[0]['details'] : '';
		            $product_img =$this->product_model->get_product_images($product_purchase[0]['service_id']);
		            // get transaction id
		            $where3 = array('id'=>$product_purchase[0]['transaction_id']);
		            $trx_data = $this->dynamic_model->getdatafromtable('transactions',$where3);	
		            // get users information
		            $where4 = array('id'=>$product_purchase[0]['user_id']);
		            $user_data = $this->dynamic_model->getdatafromtable('user',$where4);	
	            	$productData['id'] = encode($product_purchase[0]['id']); 
	            	$productData['product_id'] =encode($product_purchase[0]['service_id']); 
	            	$productData['product_name'] = $product_name;
	            	$productData['decription']  = $desc;
	            	$productData['order_id']= !empty($trx_data[0]['trx_id']) ? $trx_data[0]['trx_id'] :'';
	            	$productData['product_images']  = $product_img;
	            	$productData['amount']  = $product_purchase[0]['amount'];
	            	$productData['sub_total']  = $product_purchase[0]['sub_total'];
	            	$productData['quantity']  =$product_purchase[0]['quantity'];
	            	$productData['category']  ='';
	            	$productData['status']  =$product_purchase[0]['status'];
	            	$productData['customer_name']   = ucwords($user_data[0]['name'].' '.$user_data[0]['lastname']);
	            	$productData['email']  = $user_data[0]['email'];
	            	$productData['profile_img']  = base_url().'uploads/user/'.$user_data[0]['profile_img'];
	            	$productData['country_code'] = $user_data[0]['country_code'];
	            	$productData['mobile'] = $user_data[0]['mobile'];
	            	$productData['date_of_birth'] = $user_data[0]['date_of_birth'];
	            	$productData['gender'] = $user_data[0]['gender'];
	            	$response	  = $productData;
					$arg['status']     = 1;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = $response;
					$arg['message']    = $this->lang->line('record_found');
				}else{
					$arg['status']     = 0;
					$arg['error_code']  = HTTP_OK;
					$arg['error_line']= __line__;
					$arg['data']       = array();
				 	$arg['message']    = $this->lang->line('record_not_found');	
				}
		    }
		  }
		}	
		
	   echo json_encode($arg);
	}


}
