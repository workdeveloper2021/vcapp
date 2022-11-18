<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api_model extends CI_Model {
	public function __construct(){
		parent::__construct();
	}

	public function question_options($question_id)
	{
		$this->db->select('*');
		$this->db->from('manage_questionnaire_ans');
		$this->db->where_in('question_id',$question_id);
		$this->db->order_by('id',"ASC");
		$query = $this->db->get();
		$result =  $query->result_array();
		if(!empty($result)){
			$i=1;
			foreach ($result as $key) {
	            # code...
	            $id = $i;
	            $i++;
	            $data[] = array('id' => $id,
	            'question_id' => $key['question_id'],
	            'question_ans' => $key['question_ans']);
	        }
	        return $data;
    	}else{
    		return false;
    	}
	}
	public function get_country()
	{
		$this->db->select('*');
		$this->db->from('manage_countries');
		$this->db->order_by('id',"ASC");
		$query = $this->db->get();
		return $query->result_array();
	}
	public function get_instructor_info($business_id='',$search_val='',$limit = "5",$offset= "0")
	{
		$search_val=trim($search_val);
		$like= '(name LIKE "%'.$search_val.'%" OR lastname LIKE "%'.$search_val.'%" )';
		$response=array();
		$where=array("business_id"=>$business_id,"is_verified"=>"Active","status"=>"Active");
		$business_trainer = $this->dynamic_model->getdatafromtable("business_trainer_relationship",$where,'user_id');
		if(!empty($business_trainer)){
		$instuctor_ids = array_column($business_trainer,'user_id');
		$condition1=array("status"=>"Active");
		$url = site_url() . 'uploads/user/';
		$instructor_info = $this->dynamic_model->getWhereInData("user","id",$instuctor_ids,$condition1,"id,name,lastname, CONCAT('" . $url . "', profile_img) as profile_img,availability_status",$limit,$offset,"create_dt","DESC",$like);
		if(!empty($instructor_info)){
		   $response=$instructor_info ;
		  }
       }
       return $response;
	}
	public function get_instructor_ids($business_id='')
	{
		$response=array();
		$where=array("business_id"=>$business_id,"status"=>"Approve");
		$business_trainer = $this->dynamic_model->getdatafromtable("business_trainer_relationship",$where,'user_id');
		if(!empty($business_trainer)){
		$instuctor_ids = array_column($business_trainer,'user_id');
		$response=$instuctor_ids;
       }
       return $response;
	}


	// public function get_companies($location='',$product='',$limit='',$offset=''){

	// 		$this->db->select('manage_company_list.id, manage_company_list.company_name, manage_company_list.thumbnail, manage_company_list.video_url, manage_comapny_location.location, manage_products.product_name, manage_products.product_title');
	// 		$this->db->from('manage_company_list');
	// 		// $this->db->join('company_list','company_list.company_id = manage_companies.id');
	// 		$this->db->join('manage_comapny_location',"manage_comapny_location.id = manage_company_list.location and manage_comapny_location.status='Active'");
	// 		$this->db->join('manage_products',"manage_products.id = manage_company_list.product and manage_products.status='Active'");

 //            if(!empty($location)){
	// 		 $this->db->where("manage_company_list.location='".$location."'");
	// 	    }

 //            if(!empty($product)){
	// 		 $this->db->where("manage_company_list.product='".$product."'");
	// 	    }

	// 		if($limit != ''){
	// 			$this->db->limit($limit, $offset);
	// 		}
	// 		//$this->db->group_by("manage_skills.name");
	// 		$this->db->order_by("manage_company_list.id","DESC");
	// 		$query = $this->db->get();
	// 		// echo $this->db->last_query();die;
	// 		if ($query->num_rows() > 0) {
	// 			  return  $query->result_array();
				 
	// 		} else {				
	// 			return false;
	// 		}
	// }


	public function get_companies($location='',$product='',$limit='',$offset=''){

			$this->db->select('manage_company_list.id, manage_company_list.info, manage_company_list.company_name, manage_company_list.thumbnail, manage_company_list.video_url, count(types_company_list.id) as types_count, GROUP_CONCAT(types_company.type_name) as type_name, manage_comapny_location.location as company_location');

			$this->db->from('manage_company_list');
			// $this->db->join('company_list','company_list.company_id = manage_companies.id');

			$this->db->join('types_company_list',"types_company_list.company_id = manage_company_list.id and types_company_list.status='Active'","left");

			$this->db->join('types_company',"types_company.id = types_company_list.type_id and types_company.status='Active'","left");

            // if(!empty($location)){
				$this->db->join('manage_comapny_location',"manage_comapny_location.id = manage_company_list.location and manage_comapny_location.status='Active'");
			// }
            if(!empty($product)){
				$this->db->join('product_list',"product_list.company_id = manage_company_list.id and product_list.status='Active'");
			}


            if(!empty($location)){
			 	$this->db->where("manage_company_list.location='".$location."'");
		    }

            if(!empty($product)){
			 	$this->db->where("product_list.product_id='".$product."'");
			 	$this->db->where("product_list.status='Active' ");
		    }
			$this->db->where("manage_company_list.status='Active'");

			if($limit != ''){
				$this->db->limit($limit, $offset);
			}
			// $this->db->group_by("types_company.company_id");
			$this->db->group_by("types_company_list.company_id");
			$this->db->group_by("manage_company_list.id");
			$this->db->order_by("manage_company_list.id","DESC");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
			} else {				
				return false;
			}
	}





	public function search_companies($search=''){

			$this->db->select('manage_company_list.id, manage_company_list.info, manage_company_list.company_name, manage_company_list.thumbnail, manage_company_list.video_url, count(types_company_list.id) as types_count, GROUP_CONCAT(types_company.type_name) as type_name, manage_comapny_location.location as company_location');

			$this->db->from('manage_company_list');
			// $this->db->join('company_list','company_list.company_id = manage_companies.id');

			$this->db->join('types_company_list',"types_company_list.company_id = manage_company_list.id and types_company_list.status='Active'","left");

			$this->db->join('types_company',"types_company.id = types_company_list.type_id and types_company.status='Active'","left");

            // if(!empty($location)){
				$this->db->join('manage_comapny_location',"manage_comapny_location.id = manage_company_list.location and manage_comapny_location.status='Active'");
			// }
            if(!empty($product)){
				$this->db->join('product_list',"product_list.company_id = manage_company_list.id and product_list.status='Active'");
			}


            if(!empty($location)){
			 	$this->db->where("manage_company_list.location='".$location."'");
		    }

            if(!empty($product)){
			 	$this->db->where("product_list.product_id='".$product."'");
			 	$this->db->where("product_list.status='Active' ");
		    }

			$this->db->where("manage_company_list.status='Active'");
			// if($search!=''){
				$this->db->where("manage_company_list.company_name like '%".$search."%'");
			// }

			// if($limit != ''){
			// 	$this->db->limit($limit, $offset);
			// }
			// $this->db->group_by("types_company.company_id");
			$this->db->group_by("types_company_list.company_id");
			$this->db->group_by("manage_company_list.id");
			$this->db->order_by("manage_company_list.id","DESC");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
			} else {				
				return false;
			}
	}





	public function get_company_product_categories($company_id='',$limit='',$offset=''){

			$this->db->select('manage_product_categories.id, manage_product_categories.category_name,"'.base_url().'uploads/company_media/userdefault.png'.'" as category_thumbnail');
			$this->db->from('manage_product_categories');
			// $this->db->join('company_list','company_list.company_id = manage_companies.id');
			
			$this->db->join('product_list',"product_list.category_id = manage_product_categories.id and product_list.status='Active'");

			// $this->db->join('manage_comapny_location',"manage_comapny_location.id = manage_company_list.location and manage_comapny_location.status='Active'");

			// $this->db->join('manage_showroom_list',"manage_showroom_list.company_id = manage_company_list.id and manage_showroom_list.status='Active'");


            // if(!empty($showroom)){
			 $this->db->where("product_list.company_id='".$company_id."'");
		    // }

    //         if(!empty($product)){
			 // $this->db->where("product_list.product_id='".$product."'");
		  //   }

			if($limit != ''){
				$this->db->limit($limit, $offset);
			}
			$this->db->group_by("product_list.category_id");
			$this->db->order_by("manage_product_categories.id","DESC");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}





	public function get_overall_product_categories($limit='',$offset=''){

			$this->db->select('manage_product_categories.id, manage_product_categories.category_name,


			    CASE WHEN (manage_product_categories.category_thumbnail AND manage_product_categories.category_thumbnail!="") THEN CONCAT("'.base_url().'uploads/category/'.'",manage_product_categories.category_thumbnail) ELSE "'.base_url().'uploads/company_media/userdefault.png'.'" END AS category_thumbnail'        


			);
			$this->db->from('manage_product_categories'); 
			// $this->db->join('company_list','company_list.company_id = manage_companies.id');
			
			$this->db->join('product_list',"product_list.category_id = manage_product_categories.id and product_list.status='Active'","left");

			// $this->db->join('manage_comapny_location',"manage_comapny_location.id = manage_company_list.location and manage_comapny_location.status='Active'");

			// $this->db->join('manage_showroom_list',"manage_showroom_list.company_id = manage_company_list.id and manage_showroom_list.status='Active'");


            // if(!empty($showroom)){
			 // $this->db->where("product_list.company_id='".$company_id."'");
		    // }

    //         if(!empty($product)){
			 // $this->db->where("product_list.product_id='".$product."'");
		  //   }

			if($limit != ''){
				$this->db->limit($limit, $offset);
			}
			$this->db->group_by("product_list.category_id");
			$this->db->order_by("manage_product_categories.id","DESC");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}





	public function get_all_product_categories_post($limit='',$offset=''){

			$this->db->select('manage_product_categories.id, manage_product_categories.category_name,"'.base_url().'uploads/company_media/userdefault.png'.'" as category_thumbnail');
			$this->db->from('manage_product_categories');
			// $this->db->join('company_list','company_list.company_id = manage_companies.id');
			
			// $this->db->join('product_list',"product_list.category_id = manage_product_categories.id and product_list.status='Active'");

			// $this->db->join('manage_comapny_location',"manage_comapny_location.id = manage_company_list.location and manage_comapny_location.status='Active'");

			// $this->db->join('manage_showroom_list',"manage_showroom_list.company_id = manage_company_list.id and manage_showroom_list.status='Active'");


			 $this->db->where("manage_product_categories.status='Active'");

            // if(!empty($showroom)){
			 // $this->db->where("product_list.company_id='".$company_id."'");
		    // }

    //         if(!empty($product)){
			 // $this->db->where("product_list.product_id='".$product."'");
		  //   }

			if($limit != ''){
				$this->db->limit($limit, $offset);
			}
			// $this->db->group_by("product_list.category_id");
			$this->db->order_by("manage_product_categories.id","DESC");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}






	public function get_company_products($company_id='',$category='',$limit='',$offset='',$user_id){

			$this->db->select('manage_products.id, manage_products.product_name, manage_products.product_thumbnail, product_list.id as product_list_id, CASE WHEN (favourite_product_list.favourite_status AND favourite_product_list.favourite_status=1) THEN 1 ELSE 0 END AS favourite_status');

			$this->db->from('manage_products');
			
			$this->db->join('product_list',"product_list.product_id = manage_products.id and product_list.status='Active'");

			$this->db->join('favourite_product_list',"favourite_product_list.product_list_id = product_list.id and product_list.status='Active' and favourite_product_list.user_id='".$user_id."'","left");


            if(!empty($company_id)){
			 $this->db->where("product_list.company_id='".$company_id."' and product_list.category_id='".$category."'");
		    }

    //         if(!empty($product)){
			 // $this->db->where("product_list.product_id='".$product."'");
		  //   }

			if($limit != ''){
				$this->db->limit($limit, $offset);
			}
			$this->db->group_by("product_list.product_id");
			$this->db->order_by("manage_products.id","DESC");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}
 





	public function product_same_category($company_id='',$category='',$product_list_id='',$user_id){

			$this->db->select('manage_products.product_name, product_list.id as product_list_id, CASE WHEN (favourite_product_list.favourite_status AND favourite_product_list.favourite_status=1) THEN 1 ELSE 0 END AS favourite_status, ');

			$this->db->from('manage_products');
			
			$this->db->join('product_list',"product_list.product_id = manage_products.id and product_list.status='Active'");

			$this->db->join('favourite_product_list',"favourite_product_list.product_list_id = product_list.id and product_list.status='Active' and favourite_product_list.user_id='".$user_id."'","left");


            if(!empty($company_id)){
			 $this->db->where("product_list.company_id='".$company_id."' and product_list.category_id='".$category."' and product_list.id!='".$product_list_id."'");
		    }

    //         if(!empty($product)){
			 // $this->db->where("product_list.product_id='".$product."'");
		  //   }

			// if($limit != ''){
				$this->db->limit(3, 0);
			// }
			$this->db->group_by("product_list.product_id");
			$this->db->order_by("manage_products.id","DESC");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}




	public function get_product_details($product_list_id='',$user_id){


			$baseUrl = base_url().'uploads/company_media/';
			$photo = base_url().'uploads/company_media/userdefault.png';

			$this->db->select('manage_products.id, manage_products.product_name, product_list.details1, product_list.details2, product_list.details3,   

			    CASE WHEN (product_media.media AND product_media.media!="") THEN CONCAT("'.$baseUrl.'",product_media.media) ELSE "'.base_url().'uploads/company_media/userdefault.mp4'.'" END AS product_video,        

			    CASE WHEN (product_media.media_thumbnail AND product_media.media_thumbnail!="") THEN CONCAT("'.$baseUrl.'",product_media.media_thumbnail) ELSE "'.base_url().'uploads/company_media/userdefault.png'.'" END AS product_video_thumbnail,        

				product_list.id as product_list_id, CASE WHEN (favourite_product_list.favourite_status AND favourite_product_list.favourite_status=1) THEN 1 ELSE 0 END AS favourite_status');

			$this->db->from('manage_products');
			
			$this->db->join('product_list',"product_list.product_id = manage_products.id and product_list.status='Active'");

			$this->db->join('favourite_product_list',"favourite_product_list.product_list_id = product_list.id and product_list.status='Active' and favourite_product_list.user_id='".$user_id."'","left");

			$this->db->join('product_media',"product_media.product_list_id = product_list.id and product_media.status='Active' and product_media.type='video'","left");


            if(!empty($product_list_id)){
			 $this->db->where("product_list.id='".$product_list_id."'");
		    }


			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}




	public function get_company_products_color($product_list_id=''){


									// $condition = array('product_list_id'=>$val["product_list_id"],'status' => 'Active');
									// $setting = $this->dynamic_model->getdatafromtable("product_colour_varities", $condition);

			$this->db->select('product_colour_varities.id, product_colour_varities.colour_code');
			$this->db->from('product_colour_varities');
			// $this->db->join('company_list','company_list.company_id = manage_companies.id');
			
			// $this->db->join('manage_colour_codes',"manage_colour_codes.id = product_colour_varities.colour_code_id and manage_colour_codes.status='Active' and product_colour_varities.status='Active'");

			 $this->db->where("product_colour_varities.product_list_id='".$product_list_id."' and product_colour_varities.status='Active'");


			// $this->db->group_by("product_list.product_id");
			// $this->db->order_by("manage_products.id","DESC");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}


	public function get_company_products_image($product_list_id=''){


									// $condition = array('product_list_id'=>$val["product_list_id"],'status' => 'Active');
									// $setting = $this->dynamic_model->getdatafromtable("product_colour_varities", $condition);

			$this->db->select('product_media.media');
			$this->db->from('product_media');
			// $this->db->join('company_list','company_list.company_id = manage_companies.id');
			
			// $this->db->join('manage_colour_codes',"manage_colour_codes.id = product_colour_varities.colour_code_id and manage_colour_codes.status='Active' and product_colour_varities.status='Active'");

			 $this->db->where("product_media.product_list_id='".$product_list_id."' and product_media.status='Active' and product_media.type='image'");


			// $this->db->group_by("product_list.product_id");
			// $this->db->order_by("manage_products.id","DESC");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}


	public function get_company_products_image360($product_list_id=''){


									// $condition = array('product_list_id'=>$val["product_list_id"],'status' => 'Active');
									// $setting = $this->dynamic_model->getdatafromtable("product_colour_varities", $condition);

			$this->db->select('product_media.media');
			$this->db->from('product_media');
			// $this->db->join('company_list','company_list.company_id = manage_companies.id');
			
			// $this->db->join('manage_colour_codes',"manage_colour_codes.id = product_colour_varities.colour_code_id and manage_colour_codes.status='Active' and product_colour_varities.status='Active'");

			 $this->db->where("product_media.product_list_id='".$product_list_id."' and product_media.status='Active' and product_media.type='360image'");


			// $this->db->group_by("product_list.product_id");
			// $this->db->order_by("manage_products.id","DESC");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}


	public function get_company_products_3dModel($product_list_id=''){


									// $condition = array('product_list_id'=>$val["product_list_id"],'status' => 'Active');
									// $setting = $this->dynamic_model->getdatafromtable("product_colour_varities", $condition);

			$this->db->select('product_media.media');
			$this->db->from('product_media');
			// $this->db->join('company_list','company_list.company_id = manage_companies.id');
			
			// $this->db->join('manage_colour_codes',"manage_colour_codes.id = product_colour_varities.colour_code_id and manage_colour_codes.status='Active' and product_colour_varities.status='Active'");

			 $this->db->where("product_media.product_list_id='".$product_list_id."' and product_media.status='Active' and product_media.type='image'");


			// $this->db->group_by("product_list.product_id");
			// $this->db->order_by("manage_products.id","DESC");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}



	public function get_products_image($product_list_id='',$color_id){ 


									// $condition = array('product_list_id'=>$val["product_list_id"],'status' => 'Active');
									// $setting = $this->dynamic_model->getdatafromtable("product_colour_varities", $condition);

			$baseUrl = base_url().'uploads/company_media/';
			$photo = base_url().'uploads/company_media/userdefault.png';

			$this->db->select("CASE WHEN (product_media.media AND product_media.media!='') THEN CONCAT('".$baseUrl."',product_media.media) ELSE '".base_url().'uploads/company_media/userdefault.png'."' END AS product_image, product_media.id");
			$this->db->from('product_media');
			// $this->db->join('company_list','company_list.company_id = manage_companies.id');
			
			// $this->db->join('manage_colour_codes',"manage_colour_codes.id = product_colour_varities.colour_code_id and manage_colour_codes.status='Active' and product_colour_varities.status='Active'");

			 $this->db->where("product_media.product_list_id='".$product_list_id."' and product_media.product_colour_varity_id='".$color_id."' and product_media.status='Active' and product_media.type='image'");


			// $this->db->group_by("product_list.product_id");
			// $this->db->order_by("manage_products.id","DESC");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}



	public function products_3d_model($product_list_id='',$type=''){ 


									// $condition = array('product_list_id'=>$val["product_list_id"],'status' => 'Active');
									// $setting = $this->dynamic_model->getdatafromtable("product_colour_varities", $condition);

			$baseUrl = base_url().'uploads/company_media/';
			$photo = base_url().'uploads/company_media/userdefault.png';

			$this->db->select("CASE WHEN (product_media.media AND product_media.media!='') THEN CONCAT('".$baseUrl."',product_media.media) ELSE '".base_url().'uploads/company_media/userdefault.png'."' END AS products_3d_model, product_media.id");
			$this->db->from('product_media');
			// $this->db->join('company_list','company_list.company_id = manage_companies.id');
			
			// $this->db->join('manage_colour_codes',"manage_colour_codes.id = product_colour_varities.colour_code_id and manage_colour_codes.status='Active' and product_colour_varities.status='Active'");

			 $this->db->where("product_media.product_list_id='".$product_list_id."' and product_media.status='Active' and product_media.type='".$type."'");


			// $this->db->group_by("product_list.product_id");
			// $this->db->order_by("manage_products.id","DESC");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0 && $type!='') {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}


	public function products_3d_model_color($product_list_id='',$color_id='',$type=''){ 


									// $condition = array('product_list_id'=>$val["product_list_id"],'status' => 'Active');
									// $setting = $this->dynamic_model->getdatafromtable("product_colour_varities", $condition);

			$baseUrl = base_url().'uploads/company_media/';
			$photo = base_url().'uploads/company_media/userdefault.png';

			$this->db->select("CASE WHEN (product_media.media AND product_media.media!='') THEN CONCAT('".$baseUrl."',product_media.media) ELSE '".base_url().'uploads/company_media/userdefault.png'."' END AS products_3d_model, product_media.id");
			$this->db->from('product_media');
			// $this->db->join('company_list','company_list.company_id = manage_companies.id');
			
			// $this->db->join('manage_colour_codes',"manage_colour_codes.id = product_colour_varities.colour_code_id and manage_colour_codes.status='Active' and product_colour_varities.status='Active'");

			 $this->db->where("product_media.product_list_id='".$product_list_id."' and product_media.product_colour_varity_id='".$color_id."' and product_media.status='Active' and product_media.type='".$type."'");


			// $this->db->group_by("product_list.product_id");
			// $this->db->order_by("manage_products.id","DESC");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0 && $type!='') {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}




	public function product_3d_rendered_images($product_list_id=''){ 



			$baseUrl = base_url().'uploads/company_media/';
			$photo = base_url().'uploads/company_media/userdefault.png';

			$this->db->select("CASE WHEN (3d_rendered_product_image.image_name AND 3d_rendered_product_image.image_name!='') THEN CONCAT('".$baseUrl."',3d_rendered_product_image.image_name) ELSE '".base_url().'uploads/company_media/userdefault.png'."' END AS three_d_rendered_product_image, 3d_rendered_product_image.id, 3d_rendered_product_image.image_info");
			$this->db->from('3d_rendered_product_image');
			// $this->db->join('company_list','company_list.company_id = manage_companies.id');
			
			// $this->db->join('manage_colour_codes',"manage_colour_codes.id = product_colour_varities.colour_code_id and manage_colour_codes.status='Active' and product_colour_varities.status='Active'");

			 $this->db->where("3d_rendered_product_image.product_list_id='".$product_list_id."' and 3d_rendered_product_image.status='Active' and 3d_rendered_product_image.is_deleted='0'");


			// $this->db->group_by("product_list.product_id");
			// $this->db->order_by("manage_products.id","DESC");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}




	public function make_product_favourite($product_list_id='',$favourite_status='0',$user_id=''){


				$where=array("user_id"=>$user_id,"product_list_id"=>$product_list_id);
				$business_trainer = $this->dynamic_model->getdatafromtable("favourite_product_list",$where);
				if(!empty($business_trainer)){
					$fav = $this->dynamic_model->updateRowWhere("favourite_product_list",$where,array(
						"favourite_status" => $favourite_status,
						"updated_at" => time()
					));
			    }else{

					$fav = $this->dynamic_model->insertdata("favourite_product_list",array(

						"product_list_id" => $product_list_id,
						"favourite_status" => $favourite_status,
						"user_id" => $user_id,
						"created_at" => time(),
						"updated_at" => time()

					));

			    }

				if ($fav) {
					  return  true;
					 
				} else {				
					return false;
				}
	}


	public function get_showrooms($company_id,$limit='',$offset=''){

			$shwrmurl = base_url('uploads/showroom_media/');

			$this->db->select('manage_showroom_list.id, manage_showroom_list.showroom_name, CONCAT("'.$shwrmurl.'", thumbnail) as thumbnail, CONCAT("'.$shwrmurl.'", video_url) as video_url, CONCAT("'.$shwrmurl.'", play_video_url) as play_video_url, CONCAT("'.$shwrmurl.'", img_360) as img_360');
			$this->db->from('manage_showroom_list');
			// $this->db->join('company_list','company_list.company_id = manage_companies.id');

            if(!empty($company_id)){
			 $this->db->where("manage_showroom_list.company_id='".$company_id."' and manage_showroom_list.status='Active'");
		    }


			if($limit != ''){
				$this->db->limit($limit, $offset);
			}
			//$this->db->group_by("manage_skills.name");
			$this->db->order_by("manage_showroom_list.id","DESC");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}




	public function get_showrooms_cordinates($showroom_id){


			$this->db->select('id, xval, yval, zval, info');
			$this->db->from('img_360_coordinates');

            if(!empty($showroom_id)){
			 $this->db->where("showroom_id='".$showroom_id."' and is_showrooms_coordinates='1' and status='Active'");
		    }

			$this->db->order_by("id","DESC");

			$query = $this->db->get();
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}



	public function get_location_list_old(){

			$this->db->select('manage_comapny_location.id, manage_comapny_location.location');
			$this->db->from('manage_comapny_location');
			//$this->db->group_by("manage_skills.name");
			$this->db->order_by("manage_comapny_location.id","DESC");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}


	public function get_location_list(){

		$this->db->select('manage_comapny_location.*');
        $this->db->from('manage_comapny_location');
        $this->db->join('manage_company_list','manage_comapny_location.id = manage_company_list.location'); 

			$this->db->order_by("manage_comapny_location.id","DESC");
			$this->db->group_by("manage_comapny_location.id");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}



	public function get_product_list(){

			$this->db->select('manage_products.id, manage_products.product_name, manage_products.product_title');
			$this->db->from('manage_products');
			$this->db->where('status','Active');
			//$this->db->group_by("manage_skills.name");
			$this->db->order_by("manage_products.id","DESC");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}




	

	public function get_instructor_details($business_id='',$instructor_ids='',$condition='',$search_val='',$limit='',$offset=''){
		//$instuctor_ids= $this->get_instructor_ids($business_id);

		if(!empty($instructor_ids)){
			$search_info = trim($search_val);
			$like= '(name LIKE "%'.$search_val.'%" OR lastname LIKE "%'.$search_val.'%" )';

			$this->db->select('user.*,instructor_details.skill,instructor_details.total_experience,instructor_details.appointment_fees,instructor_details.appointment_fees_type,instructor_details.shifts_instructor,instructor_details.about');
			$this->db->from('user');
			$this->db->join('instructor_details','instructor_details.user_id = user.id');
			$this->db->where_in('user.id',$instructor_ids);
			$this->db->group_by('user.id');
            if(!empty($condition)){
			 $this->db->where($condition);
		    }
		    if(!empty($search_info)){ 
		    $this->db->where($like,NUll);
	     	}
			if($limit != ''){
				$this->db->limit($limit, $offset);
			}
			$this->db->order_by("create_dt","DESC");
			$query = $this->db->get();
			
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	        
		}else{
			return false;
		}
	}
	public function get_my_studios($user_id='',$limit='',$offset='',$latitude='',$longitude='',$created_by=''){
			$this->db->select('business.*');
			if (!empty($latitude) && !empty($longitude)) {
			$this->db->select('( 3959 * acos( cos( radians("' . $latitude . '") ) * cos( radians(lat) ) * cos( radians(longitude) - radians("' . $longitude . '") ) + sin( radians("' . $latitude . '") ) * sin( radians(lat)))) AS distance', false);
			}else{
				$this->db->select("'0' as distance");
			}
			$this->db->from('business');
			$this->db->join('user_booking','user_booking.business_id = business.id');
			$this->db->where('user_booking.user_id',$user_id);
			$this->db->group_by('user_booking.business_id');
			if($limit != ''){
				$this->db->limit($limit, $offset);
			}
			$this->db->order_by("create_dt","DESC");
			$query = $this->db->get();
			//echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				return  $query->result_array(); 
			}else{
				if (empty($created_by)) {
					return false;
				}

				/* start */
				$this->db->select('business.*');
				if (!empty($latitude) && !empty($longitude)) {
				$this->db->select('( 3959 * acos( cos( radians("' . $latitude . '") ) * cos( radians(lat) ) * cos( radians(longitude) - radians("' . $longitude . '") ) + sin( radians("' . $latitude . '") ) * sin( radians(lat)))) AS distance', false);
				}else{
					$this->db->select("'0' as distance");
				}
				$this->db->from('business');
				$this->db->where('business.user_id',$created_by);
				$query = $this->db->get();
				if ($query->num_rows() > 0) {
					return  $query->result_array(); 
				}else{
					return false;
				}	
				/* end*/			
				//return false;
			}	
	}
	public function get_signed_classes($business_id='',$upcoming_date='',$limit='',$offset='',$status='',$user_id=''){
		$this->db->select('business_class.*,user_attendance.status as attendance_status,user_attendance.user_id,user_attendance.checkin_dt');
		$this->db->from('business_class');
		$this->db->join('user_attendance','user_attendance.service_id = business_class.id');
		if(!empty($upcoming_date)){
			$date = date("Y-m-d",$upcoming_date);
            $where="business_class.business_id=".$business_id." AND user_attendance.user_id=".$user_id." AND business_class.status='Active' AND user_attendance.service_type='1' AND user_attendance.checkin_dt ='".$date."'";

            //DATE(FROM_UNIXTIME(start_date))='".$date."'
		}else{
			//if status not empty its chk wating status
		  if(empty($status)){
           $where="business_class.business_id=".$business_id." AND user_attendance.user_id=".$user_id." AND user_attendance.service_type='1' AND business_class.status='Active'";
          }else{
          	 $where="business_class.business_id=".$business_id." AND user_attendance.user_id=".$user_id." AND user_attendance.service_type='1' AND business_class.status='Active' AND user_attendance.status='waiting'";
          }
          
		}
		$this->db->where($where);
		$this->db->group_by('user_attendance.service_id');
		if($limit != ''){
			$this->db->limit($limit, $offset);
		}
		$this->db->order_by("create_dt","DESC");
		$query = $this->db->get();
		//echo $this->db->last_query();die;
		if($query->num_rows() > 0) {
			return  $query->result_array(); 
		}else{				
			return false;
		}	
	}

	public function get_signed_workshop($business_id='',$upcoming_date='',$limit='',$offset='',$status='',$user_id=''){
		$this->db->select('business_workshop.*,user_attendance.status as attendance_status,user_attendance.user_id');
		$this->db->from('business_workshop');
		$this->db->join('user_attendance','user_attendance.service_id = business_workshop.id');
		$this->db->group_by('user_attendance.service_id');
		if(!empty($upcoming_date)){
			$date = date("Y-m-d",$upcoming_date);
            $where="business_workshop.business_id=".$business_id." AND user_attendance.user_id=".$user_id." AND business_workshop.status='Active' AND user_attendance.service_type='2'  AND DATE(FROM_UNIXTIME(start_date))='".$date."'";
		}else{
           $where="business_workshop.business_id=".$business_id." AND user_attendance.user_id=".$user_id." AND user_attendance.service_type='2' AND business_workshop.status='Active'";
		}
		$this->db->where($where);
		if($limit != ''){
			$this->db->limit($limit, $offset);
		}
		$this->db->order_by("create_dt","DESC");
		$query = $this->db->get();
		//echo $this->db->last_query();die;
		if($query->num_rows() > 0) {
			return  $query->result_array(); 
		}else{				
			return false;
		}	
	}

    public function my_classes_attandance($business_id='',$limit='',$offset='',$user_id=''){
		$this->db->select('business_class.*,user_attendance.status as attendance_status,user_attendance.user_id, user_attendance.schedule_id,user_attendance.checkin_dt');
		$this->db->from('business_class');
		$this->db->join('user_attendance','user_attendance.service_id = business_class.id');
		$where="business_class.business_id=".$business_id." AND user_attendance.user_id=".$user_id." AND user_attendance.service_type='1' AND business_class.status='Active'  ORDER BY user_attendance.checkin_dt DESC";
		//  AND user_attendance.status ='checkin'
		$this->db->where($where);
		//$this->db->group_by('user_attendance.service_id');
		if($limit != ''){
			$this->db->limit($limit, $offset);
		}
		//$this->db->order_by("create_dt","DESC");
		$query = $this->db->get();
		//echo $this->db->last_query();die;
		if($query->num_rows() > 0) {
			return  $query->result_array(); 
		}else{				
			return false;
		}	
	}

	public function get_cart_business($user_id='',$limit='',$offset='', $business_id = ''){

			$this->db->select('*');
			$this->db->from('user_booking');
			$this->db->where('user_id',$user_id);
			$this->db->where('status',"Pending");
			if (!empty($business_id)) {
				$this->db->where('business_id', $business_id);
			}
			$this->db->group_by('business_id');
			$this->db->order_by("create_dt","DESC");
			if($limit != ''){
				$this->db->limit($limit, $offset);
			}
			$query = $this->db->get();
			//echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				return  $query->result_array(); 
			}else{				
				return false;
			}	
	}
	public function search_business_old($business_ids='',$lat='',$lang='',$distance='',$search_text='',$limit='',$offset=''){
		if(empty($lat && $lang)){
          $select='*';
          $order_by='id';
          $having='';
		}else{
		   //3959 miles 6371 km
		   $select="*,(
					    6371 * acos (
					      cos ( radians($lat) )
					      * cos( radians( lat	 ) )
					      * cos( radians( longitude ) - radians($lang) )
					      + sin ( radians($lat) )
					      * sin( radians( lat	 ) )
					    )
					  ) AS distance";
		    $order_by="distance";
		    $having=" HAVING distance < $distance";
		}
		if(!empty($business_ids)){
			$sql = "SELECT $select FROM business
            WHERE id IN($business_ids) AND status='Active' $having
            ORDER BY $order_by LIMIT $limit OFFSET $offset";
			
		}elseif($search_text){
            $sql = 'SELECT '.$select.' FROM business
            WHERE status="Active" AND business_name LIKE  "%'.$search_text.'%"
             '.$having.' ORDER BY '.$order_by.' LIMIT '.$limit.' OFFSET '.$offset;
		}else{
			 $sql = 'SELECT '.$select.' FROM business
            WHERE status="Active" ORDER BY '.$order_by.' LIMIT '.$limit.' OFFSET '.$offset;
		}
		$getbusiness = $this->dynamic_model->get_query_result($sql);
		//echo $this->db->last_query();die();
		return $getbusiness;
	}
	public function search_business($business_ids='',$lat='',$lang='',$distance='',$search_text='',$limit='',$offset=''){
         $having='';
		if(empty($lat && $lang)){
          $select='business.*';
          $order_by='business.create_dt';
		}else{
		   //3959 miles 6371 km
		   $select="business.*,(
					    6371 * acos (
					      cos ( radians($lat) )
					      * cos( radians(business.lat) )
					      * cos( radians(business.longitude ) - radians($lang) )
					      + sin ( radians($lat) )
					      * sin( radians(business.lat) )
					    )
					  ) AS distance";
		    $order_by="distance";
		    if(!empty($distance)){
		    $having=" HAVING distance < $distance";
		    }
		}
		if(!empty($business_ids)){
            $sql = "SELECT $select FROM business JOIN user ON user.id=business.user_id WHERE business.id IN($business_ids) AND user.status='Active' AND business.status='Active' $having
			 ORDER BY $order_by LIMIT $limit";
			if (!empty($offset)) {
				$sql .= ' OFFSET '.$offset;
			} 
			
		}

//.' OFFSET '.$offset
		if(!empty($business_ids)){
			$cat='';
			if(!empty($business_ids)){
				$cat = 'business.id IN('.$business_ids.') AND';
			}
			$sql = 'SELECT '.$select.' FROM business JOIN user ON user.id=business.user_id WHERE  '.$cat.' business.status="Active" AND user.status="Active" '.$having.' ORDER BY '.$order_by.' LIMIT '.$limit;
			if (!empty($offset)) {
				$sql .= ' OFFSET '.$offset;
			}
		}else if(!empty($search_text)){
				$sql = 'SELECT '.$select.' FROM business JOIN user ON user.id=business.user_id WHERE  business.status="Active" AND user.status="Active" AND business_name LIKE  "%'.$search_text.'%" '.$having.' ORDER BY '.$order_by.' LIMIT '.$limit;
				if (!empty($offset)) {
					$sql .= ' OFFSET '.$offset;
				}
		}else{
			 $sql = 'SELECT '.$select.' FROM business JOIN user ON user.id=business.user_id WHERE business.status="Active" AND user.status="Active" ORDER BY '.$order_by.' LIMIT '.$limit;
			 if (!empty($offset)) {
				$sql .= ' OFFSET '.$offset;
			}
		}
		$getbusiness = $this->dynamic_model->get_query_result($sql);
		//echo $this->db->last_query();die();
		return $getbusiness;
	}
	public function get_business_according_to_distance_old($catid='',$lat='',$lang='',$distance=''){
		if(empty($lat && $lang)){
          $select='*';
          $order_by='create_dt';
          $having='';
		}else{
		   //3959 miles 6371 km
		   $select="*,(
					    6371 * acos (
					      cos ( radians($lat) )
					      * cos( radians( lat	 ) )
					      * cos( radians( longitude ) - radians($lang) )
					      + sin ( radians($lat) )
					      * sin( radians( lat	 ) )
					    )
					  ) AS distance";
		    $order_by="distance";
		    $having=" HAVING distance < $distance";
		}
		$sql = "SELECT $select FROM business
	            WHERE status='Active' AND FIND_IN_SET('$catid', category) $having
	            ORDER BY $order_by LIMIT 5";

	     //$sql = "SELECT b.* FROM business as b JOIN `business_category`as c on b.id = c.business_id where c.category IN ('$catid') GROUP BY b.id ORDER BY b.name LIMIT 25";
	    // echo $sql; die;
		$getbusiness = $this->dynamic_model->get_query_result($sql);
		//echo $this->db->last_query();die();
		return $getbusiness;
	}
	public function get_business_according_to_distance($catid='',$lat='',$lang='',$distance='',$limit=5){
		$business_ids=$getbusiness='';
		if(!empty($catid)){
		$condition=array("category"=>$catid,"parent_id !="=>0,"type"=>1);
        $getsubcat = $this->dynamic_model->getdatafromtable('business_category',$condition); 
        if(!empty($getsubcat)){
        	$subcat=array_column($getsubcat,'business_id');
        	$business_ids=implode(',',$subcat);
          }
        }
		if(empty($lat && $lang)){
          $select='business.*';
          $order_by='business.create_dt';
          $having='';
		}else{
		   //3959 miles 6371 km
		   $select="business.*,(
					    6371 * acos (
					      cos ( radians($lat) )
					      * cos( radians( business.lat	 ) )
					      * cos( radians( business.longitude ) - radians($lang) )
					      + sin ( radians($lat) )
					      * sin( radians( business.lat	 ) )
					    )
					  ) AS distance";
		    $order_by="distance";
		    $having=" HAVING distance < $distance";
		}
		 if(!empty($business_ids)){
		 	 $sql = "SELECT $select FROM business JOIN user ON user.id=business.user_id WHERE business.id IN($business_ids) AND user.status='Active' AND business.status='Active' $having
            ORDER BY $order_by LIMIT $limit";
            //echo $sql; die;
            $getbusiness = $this->dynamic_model->get_query_result($sql);	
		}
		//echo $this->db->last_query();die();
		//print_r($getbusiness);die;
		return $getbusiness;
	}
	public function user_location_checked_in_studio($business_id='',$lat='',$lang='',$distance='0.1'){
		   $getbusiness='';
		   //3959 miles 6371 km
		   if(!empty($lat && $lang)){
		   $select="*,(
					    6371 * acos (
					      cos ( radians($lat) )
					      * cos( radians( lat	 ) )
					      * cos( radians( longitude ) - radians($lang) )
					      + sin ( radians($lat) )
					      * sin( radians( lat	 ) )
					    )
					  ) AS distance";
		    $order_by="distance";
		    $having=" HAVING distance < $distance";
		
		$sql = "SELECT $select FROM business
	            WHERE id=$business_id AND status='Active' $having";
		$getbusiness = $this->dynamic_model->get_query_result($sql);
	    }
		//echo $this->db->last_query();die();
		return $getbusiness;
	}





	public function get_all_productsOld($filterArr=array(),$limit='',$offset='',$user_id){

			$this->db->select('manage_products.id, manage_products.product_name, manage_company_list.company_name, manage_products.product_thumbnail, product_list.id as product_list_id, CASE WHEN (favourite_product_list.favourite_status AND favourite_product_list.favourite_status=1) THEN 1 ELSE 0 END AS favourite_status');

			$this->db->from('manage_products');
			
			$this->db->join('product_list',"product_list.product_id = manage_products.id and product_list.status='Active'");
			
			$this->db->join('manage_company_list',"product_list.company_id = manage_company_list.id and manage_company_list.status='Active'");
			
			$this->db->join('manage_product_categories',"product_list.category_id = manage_product_categories.id and manage_product_categories.status='Active'");

			$this->db->join('favourite_product_list',"favourite_product_list.product_list_id = product_list.id and product_list.status='Active' and favourite_product_list.user_id='".$user_id."'","left");


            if(!empty($filterArr['company_id'])){
			    $this->db->where("product_list.company_id='".$filterArr['company_id']."'");
		    }
		    if(!empty($filterArr['category_id'])){
		    	$this->db->where("product_list.category_id='".$filterArr['category_id']."'");
		    }
		    if(!empty($filterArr['product_name'])){
		    	$this->db->where("manage_products.product_name like '%".$filterArr['product_name']."%'");
		    }

    			//         if(!empty($product)){
				 // $this->db->where("product_list.product_id='".$product."'");
		  		//   }

			if($limit != ''){
				$this->db->limit($limit, $offset);
			}
			$this->db->group_by("product_list.product_id");
			$this->db->order_by("manage_products.id","DESC");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}
 




	public function get_favourite_productsold($category='',$limit='',$offset='',$user_id){

			$this->db->select('manage_company_list.id as comp_id');

			$this->db->from('favourite_product_list');
			
			$this->db->join('product_list',"product_list.id = favourite_product_list.product_list_id and product_list.status='Active'");

			// $this->db->join('manage_products',"manage_products.id = product_list.product_id and manage_products.status='Active'");
			
			// $this->db->join('manage_product_categories',"product_list.category_id = manage_product_categories.id and manage_product_categories.status='Active'");




			// $this->db->join('manage_company_list',"manage_company_list.id = product_list.company_id and manage_company_list.status='Active'");

			$this->db->join('manage_company_list',"manage_company_list.id = product_list.company_id");





			// $this->db->join('favourite_product_list',"favourite_product_list.product_list_id = product_list.id and product_list.status='Active' and favourite_product_list.user_id='".$user_id."'","left");

			$this->db->where("favourite_product_list.user_id='".$user_id."'");

    //         if(!empty($category)){
			 // $this->db->where("product_list.category_id='".$category."'");
		  //   }

    //         if(!empty($product)){
			 // $this->db->where("product_list.product_id='".$product."'");
		  //   }

			if($limit != ''){
				$this->db->limit($limit, $offset);
			}
			$this->db->group_by("favourite_product_list.id");
			$this->db->group_by("product_list.id");
			$this->db->group_by("product_list.company_id");
			// $this->db->order_by("manage_products.id","DESC");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}
 



	public function get_favourite_products_companies($category='',$limit='',$offset='',$user_id){

			$this->db->select('manage_company_list.id as company_id, manage_company_list.company_name');

			$this->db->from('manage_company_list');
			
			$this->db->join('product_list',"product_list.company_id = manage_company_list.id and product_list.status='Active'");

			$this->db->join('favourite_product_list',"favourite_product_list.product_list_id = product_list.id");

			$this->db->where("favourite_product_list.user_id='".$user_id."' and favourite_product_list.favourite_status='1'");

			$this->db->where("manage_company_list.status='Active'");
			
			if($category=='0' || $category==0){
				$category='';
			}
			if($category!=''){
				$this->db->where("product_list.category_id='".$category."'");
			}

			if($limit != ''){
				$this->db->limit($limit, $offset);
			}
			$this->db->group_by("manage_company_list.id");
			$this->db->order_by("manage_company_list.id","ASC");
			$query = $this->db->get();
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}
 



	public function get_company_favourite_products($company_id='',$category='',$limit='',$offset='',$user_id){

			$this->db->select('manage_products.id, manage_products.product_name, manage_products.product_thumbnail, product_list.id as product_list_id, product_list.product_unit');

			$this->db->from('manage_products');
			
			$this->db->join('product_list',"product_list.product_id = manage_products.id and product_list.status='Active'");

			$this->db->join('favourite_product_list',"favourite_product_list.product_list_id = product_list.id and product_list.status='Active' and favourite_product_list.user_id='".$user_id."' and favourite_product_list.favourite_status='1'");


            if(!empty($company_id)){
			 $this->db->where("product_list.company_id='".$company_id."'");
		    }


            if(!empty($category)){
			 $this->db->where("product_list.category_id='".$category."'");
		    }

			// if($limit != ''){
			// 	$this->db->limit($limit, $offset);
			// }
			$this->db->group_by("product_list.product_id");
			// $this->db->group_by("favourite_product_list.product_list_id");
			$this->db->order_by("favourite_product_list.id","DESC");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}
 




	public function get_companies_retailers_countries($company_id=''){

			$this->db->select('manage_comapny_location.id as country_id, manage_comapny_location.location as country_name');

			$this->db->from('company_retailers');
			
			$this->db->join('manage_comapny_location',"manage_comapny_location.id = company_retailers.country and manage_comapny_location.status='Active'");

			// $this->db->join('favourite_product_list',"favourite_product_list.product_list_id = product_list.id");

			// $this->db->where("favourite_product_list.user_id='".$user_id."' and favourite_product_list.favourite_status='1'");

			$this->db->where("company_retailers.status='Active' and company_retailers.company_id='".$company_id."' ");
			
			// if($category!=''){
			// 	$this->db->where("product_list.category_id='".$category."'");
			// }

			// if($limit != ''){
			// 	$this->db->limit($limit, $offset);
			// }
			$this->db->group_by("manage_comapny_location.id");
			// $this->db->order_by("manage_company_list.id","ASC");
			$query = $this->db->get();
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}
 

	public function get_companies_retailers_cities($company_id,$country_id=''){

			$this->db->select('company_retailers.city');

			$this->db->from('company_retailers');
			

			$this->db->where("company_retailers.country='".$country_id."' and company_retailers.company_id='".$company_id."'");
			
			$this->db->group_by("company_retailers.city");
			// $this->db->order_by("manage_company_list.id","ASC");
			$query = $this->db->get();
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}
 

	public function get_companies_retailers_users($company_id,$city=''){

			$this->db->select('company_retailers.id, company_retailers.name, company_retailers.email');

			$this->db->from('company_retailers');
			

			$this->db->where("company_retailers.city='".$city."' and company_retailers.company_id='".$company_id."'");
			
			// $this->db->group_by("company_retailers.city");
			// $this->db->order_by("manage_company_list.id","ASC");
			$query = $this->db->get();
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}
 







	public function get_category_countries($category_id=''){

			$this->db->select('manage_comapny_location.id as country_id, manage_comapny_location.location as country_name');

			$this->db->from('product_list');
			
			$this->db->join('manage_company_list',"manage_company_list.id = product_list.company_id and manage_company_list.status='Active'");

			$this->db->join('manage_comapny_location',"manage_comapny_location.id = manage_company_list.location and manage_comapny_location.status='Active'");

			// $this->db->join('favourite_product_list',"favourite_product_list.product_list_id = product_list.id");

			// $this->db->where("favourite_product_list.user_id='".$user_id."' and favourite_product_list.favourite_status='1'");

			if($category_id!=''){
				$this->db->where("product_list.category_id='".$category_id."' ");
			}
			
			// if($category!=''){
			// 	$this->db->where("product_list.category_id='".$category."'");
			// }

			// if($limit != ''){
			// 	$this->db->limit($limit, $offset);
			// }
			$this->db->group_by("manage_comapny_location.id");
			// $this->db->order_by("manage_company_list.id","ASC");
			$query = $this->db->get();
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}
 






	public function get_all_products($filterArr=array(),$limit='',$offset='',$user_id){

			$this->db->select('manage_products.id, manage_products.product_name, manage_company_list.company_name, manage_company_list.location as country_id, manage_products.product_thumbnail, product_list.id as product_list_id, product_list.category_id, CASE WHEN (favourite_product_list.favourite_status AND favourite_product_list.favourite_status=1) THEN 1 ELSE 0 END AS favourite_status');

			$this->db->from('manage_products');
			
			$this->db->join('product_list',"product_list.product_id = manage_products.id and product_list.status='Active'");
			
			$this->db->join('manage_company_list',"product_list.company_id = manage_company_list.id and manage_company_list.status='Active'");

			$this->db->join('manage_comapny_location',"manage_comapny_location.id = manage_company_list.location and manage_comapny_location.status='Active'");

			$this->db->join('manage_product_categories',"product_list.category_id = manage_product_categories.id and manage_product_categories.status='Active'");

			$this->db->join('favourite_product_list',"favourite_product_list.product_list_id = product_list.id and product_list.status='Active' and favourite_product_list.user_id='".$user_id."'","left");


            if(!empty($filterArr['company_id'])){
			    $this->db->where("product_list.company_id='".$filterArr['company_id']."'");
		    }
            if(!empty($filterArr['country_id'])){
			    $this->db->where("manage_company_list.location='".$filterArr['country_id']."'");
		    }
		    if(!empty($filterArr['category_id'])){
		    	$this->db->where("product_list.category_id='".$filterArr['category_id']."'");
		    }
		    if(!empty($filterArr['product_name'])){
		    	$this->db->where("manage_products.product_name like '%".$filterArr['product_name']."%'");
		    }

    			//         if(!empty($product)){
				 // $this->db->where("product_list.product_id='".$product."'");
		  		//   }

			if($limit != ''){
				$this->db->limit($limit, $offset);
			}
			$this->db->group_by("product_list.product_id");
			$this->db->order_by("manage_products.id","DESC");
			$query = $this->db->get();
			// echo $this->db->last_query();die;
			if ($query->num_rows() > 0) {
				  return  $query->result_array();
				 
			} else {				
				return false;
			}
	}
 






}

