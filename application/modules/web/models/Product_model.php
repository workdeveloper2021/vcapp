<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Product_model extends CI_Model {
	public function __construct(){
		parent::__construct();
		//$this->load->model('dynamic_model');
	}
	public function get_remove_images($remove_image_ids='',$product_id=''){
		if(!empty($remove_image_ids)){
			$this->db->select('*');
			$this->db->from('business_product_images');
			$this->db->where_in('id',$remove_image_ids);
	        $query=$this->db->get(); 
	        $returnData = $query->result_array(); 
	        return $returnData;	
		}else{
			return false;
		}
	}
	public function get_product_images($product_id=''){
		$imgarr=array();
		$image_data = $this->db->get_where('business_product_images',array("product_id"=>$product_id))->result_array();
    	if(!empty($image_data)){
      	foreach($image_data as $value1){
      	    // if(image_check($value1['image_name'],base_url().'uploads/product_img/',1)!==false){	
      	      $imgdata['product_image_id']=encode($value1['id']);
      	      $imgdata['image_name']=base_url().'uploads/products/'.$value1['image_name'] ;   
       	      $imgarr[]	        = $imgdata;
       	    // }
       	  } 	  
       	}
       	return $imgarr;
	}
   
}