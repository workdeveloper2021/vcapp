<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Web extends My_Controller {
    private $login_user_id = null;

	public function __construct(){        
        parent::__construct();
        $this->load->model('dynamic_model');
        $this->load->model('admin_model');
        if($this->session->userdata('logged_in')){
            $currentuser = getuserdetails();
            $this->login_user_id = $currentuser['id'];
        }
    }
        
    public function index() {
        $header['title'] = $this->lang->line('dashboard');
        $header['menu'] = "front_page";
        $this->frontendtemplates('frontpage','', $header);
    }

    public function contact() {
        $header['title'] = $this->lang->line('dashboard');
        $this->frontendtemplates('contact','', $header);
    }
    public function about() {
        $header['title'] = $this->lang->line('dashboard');
        $this->frontendtemplates('about','', $header);
    }
    public function gallery() {
        $header['title'] = $this->lang->line('dashboard');
        $this->frontendtemplates('gallery','', $header);
    }    
    public function faq() {
        $header['title'] = $this->lang->line('dashboard');
        $this->frontendtemplates('faq','', $header);
    }    
    public function privacy() {
        $header['title'] = $this->lang->line('dashboard');
        $this->frontendtemplates('privacy','', $header);
    }
    public function terms_condition() {
        $header['title'] = $this->lang->line('dashboard');
        $this->frontendtemplates('terms_condition','', $header);
    }
}
