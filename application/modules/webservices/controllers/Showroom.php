<?php defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
// include_once APPPATH.'third_party/phpseclib/Crypt/RSA.php';
ini_set('max_execution_time', 0);
/* * ***************Api.php**********************************
 * @product name    : Signal Health Group Inc.
 * @type            : Class
 * @class name      : Api
 * @description     : Class for all the methods , public methods calling from mobile apps.
 * @author          : Consagous Team
 * @url             : https://www.consagous.com/
 * @support         : aamir.shaikh@consagous.com
 * @copyright       : Consagous Team
 * ********************************************************** */


class Showroom extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		header("Access-Control-Allow-Origin: *");
		header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
		header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers,Authorization,X-API-KEY,Origin,X-Requested-With,userid,token,timeZone,timeZoneOffset,language,version,deviceId,deviceType,lat,lang,role");
		$method = $_SERVER['REQUEST_METHOD'];
		if ($method == "OPTIONS") {
			die();
		}
		$this->load->library('form_validation');
		$this->load->library('session');
		$this->load->model('dynamic_model');
		$this->load->model('api_model');
		$this->load->model('instructor_model');
		$this->load->library('Bomborapay');
		
	}
  
  public function get_furniture_companies()
	{
		$arg = array();
		$data = $this->db->where('status','Active')->get('manage_company_furniture')->result_array();
        if(!empty($data)){
        	foreach ($data as $key => $value) {
        		$data[$key]['showrooms'] =$this->db->where('company_id',$value['id'])->where('status','Active')->get('manage_showroom_furiture')->result_array();
        		$data[$key]['retailers'] =$this->db->where('company_id',$value['id'])->get('company_retailers')->result_array();
        		$data[$key]['products'] =$this->db->select('p.*')->from('furiture_product as p')->join('manage_showroom_furiture as m','m.id = p.showroom_id')->where('m.company_id',$value['id'])->where('p.favourite_status','yes')->get()->result_array();


        	}
        }
     if(!$data){
			$arg['status'] = 0;
			$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
			$arg['error_line'] = __line__;
			$arg['message'] = $this->lang->line('record_not_found');
			$arg['data'] = array();
		}else{
			$arg['status'] = 1;
			$arg['error_code'] = REST_Controller::HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['data'] = $data;
			$arg['message'] = "images listed successfully.";
		}

		echo json_encode($arg);
	}

	public function get_companies()
	{
		$arg = array();
		$data = $this->db->where('status','Active')->get('manage_company_list')->result_array();
        if(!empty($data)){
        	foreach ($data as $key => $value) {
        		$data[$key]['showrooms'] =$this->db->where('company_id',$value['id'])->where('status','Active')->get('manage_showroom_list')->result_array();
        			$data[$key]['products'] =$this->db->select('p.*')->from('product as p')->join('manage_showroom_list as m','m.id = p.showroom_id')->where('m.company_id',$value['id'])->where('p.favourite_status','yes')->get()->result_array();
        	}
        }

      	if(!$data){
			$arg['status'] = 0;
			$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
			$arg['error_line'] = __line__;
			$arg['message'] = $this->lang->line('record_not_found');
			$arg['data'] = array();
		}else{
			$arg['status'] = 1;
			$arg['error_code'] = REST_Controller::HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['data'] = $data;
			$arg['message'] = "images listed successfully.";
		}

		echo json_encode($arg);
	}

	public function get_furniture_companies_byid($id)
	{
		$arg = array();
		$data = $this->db->where('id',$id)->get('manage_company_list')->row_array();
        $data['showrooms'] =$this->db->where('company_id',$data['id'])->get('manage_showroom_furiture')->row_array();
         $data['retailers'] =$this->db->where('company_id',$data['id'])->get('company_retailers')->row_array();
       	if(!$data){
			$arg['status'] = 0;
			$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
			$arg['error_line'] = __line__;
			$arg['message'] = $this->lang->line('record_not_found');
			$arg['data'] = array();
		}else{
			$arg['status'] = 1;
			$arg['error_code'] = REST_Controller::HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['data'] = $data;
			$arg['message'] = "images listed successfully.";
		}

		echo json_encode($arg);
	}

	public function get_companies_byid($id)
	{
		$arg = array();
		$data = $this->db->where('id',$id)->get('manage_company_list')->row_array();
        $data['showrooms'] =$this->db->where('company_id',$data['id'])->get('manage_showroom_list')->row_array();
       	if(!$data){
			$arg['status'] = 0;
			$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
			$arg['error_line'] = __line__;
			$arg['message'] = $this->lang->line('record_not_found');
			$arg['data'] = array();
		}else{
			$arg['status'] = 1;
			$arg['error_code'] = REST_Controller::HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['data'] = $data;
			$arg['message'] = "images listed successfully.";
		}

		echo json_encode($arg);
	}

	 
	public function get_360image($id)
	{
		$arg = array();
		$data = $this->db->where('showroom_id',$id)->get('showroom_360_image')->result_array();
        if(!empty($data)){
        	foreach ($data as $key => $value) {
        		$image = $this->db->where('image360_id',$value['id'])->get('product')->result_array();
        		if(!empty($image)){
        			foreach ($image as $kee => $img) {
        					$image[$kee]['models'] = $this->db->where('img360_id',$img['id'])->get('showroom_3d_models')->result_array();
        			}
        		}
        		$data[$key]['coordinates'] =$image;
        	}
        }

		if(!$data){
			$arg['status'] = 0;
			$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
			$arg['error_line'] = __line__;
			$arg['message'] = $this->lang->line('record_not_found');
			$arg['data'] = array();
		}else{
			$arg['status'] = 1;
			$arg['error_code'] = REST_Controller::HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['data'] = $data;
			$arg['message'] = "images listed successfully.";
		}

		echo json_encode($arg);
	}

	public function get_furniture_360image($id)
	{
		$arg = array();
		$data = $this->db->where('showroom_id',$id)->get('showroom_furniuture360_image')->result_array();
        if(!empty($data)){
        	foreach ($data as $key => $value) {
        		$image = $this->db->where('image360_id',$value['id'])->get('furiture_product')->result_array();
        		if(!empty($image)){
        			foreach ($image as $kee => $img) {
        					$image[$kee]['models'] = $this->db->where('img360_id',$img['id'])->get('furitureshowroom_3d_models')->result_array();
        			}
        		}
        		$data[$key]['coordinates'] =$image;
        	}
        }
		if(!$data){
			$arg['status'] = 0;
			$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
			$arg['error_line'] = __line__;
			$arg['message'] = $this->lang->line('record_not_found');
			$arg['data'] = array();
		}else{
			$arg['status'] = 1;
			$arg['error_code'] = REST_Controller::HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['data'] = $data;
			$arg['message'] = "images listed successfully.";
		}

		echo json_encode($arg);
	}

	public function get_furniture_showroom()
	{
		$arg = array();
		$data = $this->db->get('manage_showroom_furiture')->result_array();
        if(!empty($data)){
        	foreach ($data as $key => $value) {
        		$image  =$this->db->where('showroom_id',$value['id'])->get('furiture_product')->result_array();
        		if(!empty($image)){
        			foreach ($image as $kee => $img) {
        					$image[$kee]['models'] = $this->db->where('img360_id',$img['id'])->get('furitureshowroom_3d_models')->result_array();
        			}
        		}
        		$data[$key]['coordinates'] =$image;

        	}
        }


		if(!$data){
			$arg['status'] = 0;
			$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
			$arg['error_line'] = __line__;
			$arg['message'] = $this->lang->line('record_not_found');
			$arg['data'] = array();
		}else{
			$arg['status'] = 1;
			$arg['error_code'] = REST_Controller::HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['data'] = $data;
			$arg['message'] = "Showrooms listed successfully.";
		}

		echo json_encode($arg);
	}

	public function get_showroom()
	{
		$arg = array();
		$data = $this->db->get('manage_showroom_list')->result_array();
        if(!empty($data)){
        	foreach ($data as $key => $value) {
        		$image =$this->db->where('showroom_id',$value['id'])->get('product')->result_array();
        		if(!empty($image)){
        			foreach ($image as $kee => $img) {
        					$image[$kee]['models'] = $this->db->where('img360_id',$img['id'])->get('showroom_3d_models')->result_array();
        			}
        		}
        		$data[$key]['coordinates'] =$image;

        	}
        }

		if(!$data){
			$arg['status'] = 0;
			$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
			$arg['error_line'] = __line__;
			$arg['message'] = $this->lang->line('record_not_found');
			$arg['data'] = array();
		}else{
			$arg['status'] = 1;
			$arg['error_code'] = REST_Controller::HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['data'] = $data;
			$arg['message'] = "Showrooms listed successfully.";
		}

		echo json_encode($arg);
	}


	public function get_furniture_showroom_byid($id)
	{
		$arg = array();
		$data = $this->db->where('id',$id)->get('manage_showroom_furiture')->row_array();
    $data['coordinates'] =$this->db->where('image360_id',$data['id'])->get('furiture_product')->result_array();
    if(!empty($data['coordinates'] )){
				foreach ($data['coordinates']  as $kee => $img) {
						$data['coordinates'][$kee]['models'] = $this->db->where('img360_id',$img['id'])->get('furitureshowroom_3d_models')->result_array();
				}
			}

		if(!$data){
			$arg['status'] = 0;
			$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
			$arg['error_line'] = __line__;
			$arg['message'] = $this->lang->line('record_not_found');
			$arg['data'] = array();
		}else{
			$arg['status'] = 1;
			$arg['error_code'] = REST_Controller::HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['data'] = $data;
			$arg['message'] = "Showrooms listed successfully.";
		}

		echo json_encode($arg);
	}


	public function get_showroom_byid($id)
	{
		$arg = array();
		$data = $this->db->where('id',$id)->get('manage_showroom_list')->row_array();
    $data['coordinates'] =$this->db->where('image360_id',$data['id'])->get('product')->result_array();
    if(!empty($data['coordinates'] )){
				foreach ($data['coordinates']  as $kee => $img) {
						$data['coordinates'][$kee]['models'] = $this->db->where('img360_id',$img['id'])->get('showroom_3d_models')->result_array();
				}
			}
		if(!$data){
			$arg['status'] = 0;
			$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
			$arg['error_line'] = __line__;
			$arg['message'] = $this->lang->line('record_not_found');
			$arg['data'] = array();
		}else{
			$arg['status'] = 1;
			$arg['error_code'] = REST_Controller::HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['data'] = $data;
			$arg['message'] = "Showrooms listed successfully.";
		}

		echo json_encode($arg);
	}

	//Update favourite status from product table
	 public function Addfavourite(){
    
	 	$input = $this->input->post();
	  if ($input['id'] !== '' && $input['favourite_status'] !== '') {
	 
      $chkid = $this->db->select('id')->where(array('id'=>$input['id']))->get('product')->row_array();
      if (!empty($chkid)) {
    
      $result = $this->db->where(array('id'=>$input['id']))->update('product',array('favourite_status'=>$input['favourite_status']));

      if ($result) {
      	echo 'Updated Successfully';
      	
      }else{
      	echo 'Not Updated';
      }
  	}else{
  		echo 'Wrong Id';
  	}
  }else{
  	echo 'Both field are required';
  }
    }


   public function Addfavourite_furniture(){

	 	$input = $this->input->post();
	  if ($input['id'] !== '' && $input['favourite_status'] !== '') {
	 
      $chkid = $this->db->select('id')->where(array('id'=>$input['id']))->get('furiture_product')->row_array();
      if (!empty($chkid)) {
    
      $result = $this->db->where(array('id'=>$input['id']))->update('furiture_product',array('favourite_status'=>$input['favourite_status']));

      if ($result) {
      	echo 'Updated Successfully';
      	
      }else{
      	echo 'Not Updated';
      }
  	}else{
  		echo 'Wrong Id';
  	}
  }else{
  	echo 'Both field are required';
  }
    }

    //Get all favourite status from product table


   public function Fetchfavourite(){
    $arg = array();
	  $result = $this->db->select('*')->where('favourite_status !=','no')->get('product')->result_array();
    if(!empty($result)){
    	foreach ($result as $key => $value) {
    		// print_r($value['image360_id']); die;
    		 $result[$key]['image360_details'] = $this->db->select('*')->where('id',$value['image360_id'])->get('showroom_360_image')->row();

    		  // $result[$key]['models'] =  $this->db->where('img360_id',$value['id'])->get('showroom_3d_models')->result_array();

    	}
    }

    if(!$result){
			$arg['status'] = 0;
			$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
			$arg['error_line'] = __line__;
			$arg['message'] = $this->lang->line('record_not_found');
			$arg['data'] = array();
		}else{
			$arg['status'] = 1;
			$arg['error_code'] = REST_Controller::HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['data'] = $result;
			$arg['message'] = "Showrooms listed successfully.";
		}

      echo json_encode($arg);
    }

   public function Fetchfavourite_furniture(){
    $arg = array();
	
	  $result = $this->db->select('*')->where('favourite_status !=','no')->get('furiture_product')->result_array();
    if(!empty($result)){
    	foreach ($result as $key => $value) {
    		// print_r($value['image360_id']); die;
    		 $showroom = $this->db->select('*')->where('id',$value['showroom_id'])->get('manage_showroom_furiture')->row();
    		 $result[$key]['company'] = $this->db->select('*')->where('id',$showroom->company_id)->get('manage_company_furniture')->row();

          $result[$key]['retailers'] = $this->db->select('*')->where('id',$showroom->company_id)->get('company_retailers')->result_array();

    		  // $result[$key]['models'] =  $this->db->where('img360_id',$value['id'])->get('showroom_3d_models')->result_array();

    	}
    }
     if(!$result){
			$arg['status'] = 0;
			$arg['error_code'] = REST_Controller::HTTP_NOT_FOUND;
			$arg['error_line'] = __line__;
			$arg['message'] = $this->lang->line('record_not_found');
			$arg['data'] = array();
		}else{
			$arg['status'] = 1;
			$arg['error_code'] = REST_Controller::HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['data'] = $result;
			$arg['message'] = "Showrooms listed successfully.";
		}
      echo json_encode($arg);
    }


}
