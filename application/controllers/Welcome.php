<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	public function __construct(){		
        parent::__construct();		
        $this->load->model('dynamic_model');  
    }
	
    public function content(){
        $slug = $this->uri->segment(3);
        $where = array('slug' =>$slug);
		$data['static_data'] = $this->dynamic_model->getdatafromtable('manage_static_page',$where);
        if($data['static_data'][0]['id']){
        $this->load->view('content/content',$data);
        } else {
        $this->load->view('error404',$data);    
        }
    }

    public function web_content(){
        $slug = $this->uri->segment(3);
        $where = array('slug' =>$slug);
        $data['static_data'] = $this->dynamic_model->getdatafromtable('manage_static_page',$where);
        if($data['static_data'][0]['id']){
           $response['content'] = $data['static_data'][0]['discription'];
           echo json_encode($response);
        }
    }
    public function check_verify(){
        $this->load->view('content/verify');   
    }
	
}
