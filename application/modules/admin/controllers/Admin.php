<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends My_Controller {
    private $login_user_id = null;

	public function __construct(){
        parent::__construct();
        $this->load->model('dynamic_model');
        $this->load->model('admin_model');
        $this->lang->load("admin_message", "english");
        if($this->session->userdata('logged_in')){
            $currentuser = getuserdetails();
            $this->login_user_id = $currentuser['id'];
        }
        $this->month_array = array('1'=>'January', '2'=> 'February', '3'=> 'March', '4'=> 'April', '5'=> 'May', '6'=> 'June', '7'=> 'July', '8'=> 'August', '9'=> 'September', '10'=> 'October', '11'=> 'November', '12'=> 'December');
    }

    public function index() {
       // echo "hi";die;
        $this->breadcrumbs->push('<i class="fa fa-dashboard"></i> '. $this->lang->line('dashboard_breadcrumb') , 'admin/dashboard');
        $header['title'] = $this->lang->line('dashboard');


         $dash_data['total_instructor_graph'] = array();

        $this->admintemplates('dashboard',$dash_data, $header);
	}
	public function login() {
        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $data = $this->dynamic_model->checkEmail($this->input->post('useremail'));
            if($data){
                $logdata = array(
                    'username' => $this->input->post('useremail')
                );
                $data = $this->admin_model->login($logdata);

                if($data){
                    $role_data=getdatafromtable(TABLE_MANAGE_ROLES,array("id"=>$data['role_id'],"created_by"=>1),'id');
                    $role_id_arr=array();

                    if(!empty($role_data) || $data['role_id'] == 1 ){
                        // echo $data['password'].'======'.encrypt_password($this->input->post('userpass'));die;
                        // if(encrypt_password($this->input->post('userpass')) == $data['password']){
                        //   echo 'dep';
                        // } die;
                        $hashed_password = encrypt_password($this->input->post('userpass'));
                        if($hashed_password == $data['password']){
                          
                          if($data['status']=="Active"){
                            $rememberMe = $this->input->post('keep_me');
                            if ($rememberMe == 'keep_me') {
                                $this->input->set_cookie('email', $email, time() + 3600 * 30);
                                $this->input->set_cookie('password', $password, time() + 3600 * 30);
                                $this->input->set_cookie('keep_me', $rememberMe, time() + 3600 * 30);
                            } else {
                                // Cookie expires when browser closes
                                if(isset($_COOKIE['email']) || isset($_COOKIE['password'])){
                                    delete_cookie("email");
                                    delete_cookie("password");
                                    delete_cookie("keep_me");
                                }
                            }

                            $where1 = array(
                                'id' => $data['id']
                            );
                            $info = array(
                                'is_loggedin' => "1",
                                //'last_login' => time()
                            );
                           $this->dynamic_model->updateRowWhere(TABLE_USERS, $where1, $info);
                           //data strored in session
                            $session_array['session_userid'] = $data['id'];
                            $session_array['session_userrole'] = $data['role_id'];
                            $session_array['session_lockscreen'] = "0";
                            $session_array['session_language'] = "english";
                            $this->session->set_userdata('logged_in', $session_array);
                            redirect(site_url().'admin');
                             //$this->session->set_flashdata('loginclass','success');
                            }else{
                            $this->session->set_flashdata('loginclass', 'danger');
                            $this->session->set_flashdata('login', $this->lang->line('deactive_account'));
                           }
                        }else{
                        $this->session->set_flashdata('loginclass', 'danger');
                        $this->session->set_flashdata('login', $this->lang->line('email_password_not_match'));
                       }
                    } else {
                        $this->session->set_flashdata('loginclass', 'danger');
                        $this->session->set_flashdata('login', $this->lang->line('not_authorized'));
                    }
                } else {
                    $this->session->set_flashdata('loginclass', 'danger');
                    $this->session->set_flashdata('login', $this->lang->line('email_not_in_record'));
                }
            } else {
                $this->session->set_flashdata('loginclass', 'danger');
                $this->session->set_flashdata('login', $this->lang->line('email_not_in_record'));
            }
            $this->load->view('login');
        } else {
            $this->load->view('login');
        }
    }
	public function logout(){
        $this->session->sess_destroy();
        $data2 = array(
            'is_loggedin' => '0'
        );
        $wheres = array("id" => $this->login_user_id);
        $result = $this->dynamic_model->updateRowWhere(TABLE_USERS, $wheres, $data2);

        $this->load->view('login');
        redirect(base_url('admin'));
    }

    public function lockscreen(){
        if($this->session->userdata('logged_in')){
            $userid = $this->session->userdata['logged_in']['session_userid'];
            $loguserinfo['userinfo'] = $this->dynamic_model->get_user($userid);

            $detailsData = $this->session->userdata['logged_in'];
            $detailsData['session_lockscreen'] = "1";
            $this->session->set_userdata('logged_in', $detailsData);

            $this->load->view('lockscreen', $loguserinfo);
        } else{
            $this->load->view('login');
        }
    }

    public function forgotpassword(){
        $this->load->view('forgot-password');
    }
    public function random_password(){
            $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
            $password = array();
            $alpha_length = strlen($alphabet) - 1;
            for ($i = 0; $i < 8; $i++)
            {
                $n = rand(0, $alpha_length);
                $password[] = $alphabet[$n];
            }
            return implode($password);
        }
    public function forgotpassword_submit(){
        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $condition = array(
                'email' => $this->input->post('forgot_email')
            );
            $userinfo = getdatafromtable(TABLE_USERS, $condition);
            if(empty($userinfo)){
                $this->session->set_flashdata('forgotpassword', "Please enter valid email address");
                redirect(site_url().'admin/forgotpassword');
            } else {
                $name = $userinfo[0]['name'].' '.$userinfo[0]['lastname'];
                $email = $userinfo[0]['email'];
                 $newpassword = $this->random_password();
                $where1 = array(
                    'email' => $email
                );
                $data = array(
                    'password' => encrypt_password($newpassword)
                );
                $update = $this->dynamic_model->updateRowWhere(TABLE_USERS, $where1, $data);
                //  echo $newpassword;die;
                $data['subject']     = 'FORGOT YOUR PASSWORD ?';
                $condition = array("slug" => 'admin_forget_password');
                $forgot_data = getdatafromtable(TABLE_EMAIL_TEMPLATE, $condition, "description");

                $data['description'] = str_replace("{USERNAME}","$name", $forgot_data[0]['description']);
                $data['body']  = "<tr><td>Email Address</td><td>$email</td></tr><tr><td>New Password</td><td>$newpassword</td></tr>";
                $msg = $this->load->view('emailtemplate', $data, true);
                sendEmailCI("$email", SITE_NAME ,'Forgot Password From '.SITE_NAME,$msg);

                $this->session->set_flashdata('forgotpassword', "We’ve sent an email with a new password.");
                redirect(site_url().'admin/forgotpassword');
            }
        } else {
            $this->session->set_flashdata('forgotpassword', 'SomeProble in Server. Please Try Again');
            redirect(site_url().'admin/forgotpassword');
        }
    }

    public function changeLanguage($language){
        $detailsData = $this->session->userdata['logged_in'];
        $detailsData['session_language'] = "$language";
        $this->session->set_userdata('logged_in', $detailsData);
        redirect(base_url('admin'));
    }

    public function lockopen(){
        if($this->session->userdata('logged_in')){

            $userid = $this->session->userdata['logged_in']['session_userid'];
            $loguserinfo = $this->dynamic_model->get_user($userid);

            if(encrypt_password($this->input->post('userpass')) == $loguserinfo['password']){
                $detailsData = $this->session->userdata('logged_in');
                $detailsData['session_lockscreen'] = "0";
                $this->session->set_userdata('logged_in', $detailsData);
                redirect(base_url('admin'));
            } else {

                $this->session->set_flashdata('lockclass', 'danger');
                $this->session->set_flashdata('lockerror', $this->lang->line('password_notmatch'));
                $loguserinfo1['userinfo'] = $loguserinfo;
                $this->load->view('lockscreen', $loguserinfo1);
            }

        } else{
            $this->load->view('login');
        }
	}

    public function profile(){
        if($this->session->userdata('logged_in')){
            $userid = $this->session->userdata['logged_in']['session_userid'];
            $loguserinfo['userinfo'] = $this->dynamic_model->get_user($userid);
            $header['title'] = $this->lang->line('profile');
            $this->admintemplates('profile', $loguserinfo, $header);
        } else{
            $this->load->view('login');
        }
    }

    /* public function profileupdate(){
        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('updatefirstnm', 'updatefirstnm', 'required', array( 'required' => $this->lang->line('first_name')));
            $this->form_validation->set_rules('updatelastnm', 'updatelastnm', 'required', array( 'required' => $this->lang->line('last_name')));
            $this->form_validation->set_rules('updateemail', 'updateemail', 'required', array( 'required' => $this->lang->line('email_required')));
            $this->form_validation->set_rules('updateoldpass', 'updateoldpass', 'required', array( 'required' => $this->lang->line('password_required')));
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
            } else {
                $updatedata = array();
                $userid = $this->login_user_id;
                $oldpass = $this->input->post('updateoldpass');
                $newpass = $this->input->post('updatenewnm');
                $newcomfirm = $this->input->post('updatecofpassnm');
                $userdata = $this->dynamic_model->get_user($userid);
                if(encrypt_password($oldpass) == $userdata['user_pass']){
                    $checkpass = 0;
                    $checkpasschange = 0;
                    if($newpass != '' && $newcomfirm != '' && $newpass == $newcomfirm){
                        $updatedata['id'] = $userid;
                        $updatedata['user_pass'] = hash_password($newpass);

                        $this->dynamic_model->updatedata('users', $updatedata, $userid);
                        $checkpass = 1;
                        $checkpasschange = 1;
                    } else if($newpass == '' && $newcomfirm == ''){
                        $checkpass = 1;
                    } else {
                        $this->session->set_flashdata('updateclass', 'danger');
                        $this->session->set_flashdata('updateerror', 'New Password Not Match');
                        redirect(site_url().'admin/profile');
                    }
                    if($checkpass == '1'){
                        $updatedata = array();
                         if (!empty($_FILES['updateuserpic']['name'])) {
                           $img_name = $this->dynamic_model->fileupload('updateuserpic', 'uploads/user');
                        } else {
                            $img_name = $this->input->post('oldpic');
                        }
                        $updatedata['full_name'] = $this->input->post('updatefirstnm').' '.$this->input->post('updatelastnm');
                        $updatedata['firstname'] = $this->input->post('updatefirstnm');
                        $updatedata['lastname'] = $this->input->post('updatelastnm');
                        $updatedata['user_pic'] = $img_name;

                        $this->dynamic_model->updatedata('users', $updatedata, $userid);
                    }
                    if($checkpasschange == 1){
                        $this->session->sess_destroy();
                        redirect(site_url('admin'));
                    } else {
                        $this->session->set_flashdata('updateclass', 'success');
                        $this->session->set_flashdata('updateerror', $this->lang->line('profile_update'));
                        redirect(site_url().'admin/profile');
                    }
                } else {
                    $this->session->set_flashdata('updateclass', 'danger');
                    $this->session->set_flashdata('updateerror', 'Old Password Not Match');
                    redirect(site_url().'admin/profile');
                }
            }
        } else {
            $$this->session->set_flashdata('updateclass', 'danger');
            $this->session->set_flashdata('updateerror', 'SomeProble in Server. Please Try Again');
            redirect(site_url().'admin/profile');
        }
        $this->admintemplates('profile');
    } */

    public function profileupdate(){
        $is_submit = $this->input->post('is_submit');
         $allowedExts = array("gif","jpg","png");
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('updatefirstnm', 'updatefirstnm', 'required', array( 'required' => $this->lang->line('first_name')));
            // $this->form_validation->set_rules('updatelastnm', 'updatelastnm', 'required', array( 'required' => $this->lang->line('last_name')));
            $this->form_validation->set_rules('updateemail', 'updateemail', 'required', array( 'required' => $this->lang->line('email_required')));
            $this->form_validation->set_rules('updateoldpass', 'updateoldpass', 'required', array( 'required' => $this->lang->line('password_required')));
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/profile');
            } else {
                $updatedata = array();
                $userid = $this->login_user_id;
                $oldpass = $this->input->post('updateoldpass');
                $newpass = $this->input->post('updatenewnm');
                $newcomfirm = $this->input->post('updatecofpassnm');
                $asset_type = $this->input->post('asset_type');
                $userdata = $this->dynamic_model->get_user($userid);
                if(encrypt_password($oldpass) == $userdata['password']){
                    $updatedata = array();
                    $file_ext = pathinfo($_FILES["updateuserpic"]["name"], PATHINFO_EXTENSION);
                    if (!empty($_FILES['updateuserpic']['name'])) {
                        // check for valid file to upload
                        $file_ext=strtolower($file_ext);
                        if(!in_array($file_ext, $allowedExts)){
                            $this->session->set_flashdata('updateclass', 'danger');
                            $this->session->set_flashdata('updateerror',  $this->lang->line('file_required'));
                            redirect(site_url().'admin/profile');
                        }
                        $img_name = $this->dynamic_model->fileupload('updateuserpic', 'uploads/user', 'Picture');
                    } else {
                        $img_name = $this->input->post('oldpic');
                    }
                    $updatedata['name'] = $this->input->post('updatefirstnm');
                    $updatedata['lastname'] = $this->input->post('updatelastnm');
                    $updatedata['profile_img'] = $img_name;

                    $this->dynamic_model->updatedata(TABLE_USERS, $updatedata, $userid);
                    $this->session->set_flashdata('updateclass', 'success');
                    $this->session->set_flashdata('updateerror', $this->lang->line('profile_update'));
                    redirect(site_url().'admin/profile');

                } else {
                    $this->session->set_flashdata('updateclass', 'danger');
                    $this->session->set_flashdata('updateerror', $this->lang->line('current_pass_incorrect'));
                    redirect(site_url().'admin/profile');
                }
            }
        } else {
            $this->session->set_flashdata('updateclass', 'danger');
            $this->session->set_flashdata('updateerror', 'SomeProble in Server. Please Try Again');
            redirect(site_url().'admin/profile');
        }
        $this->admintemplates('profile');
    }
    public function changepassword(){
        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('updateoldpass', 'updateoldpass', 'required' , array( 'required' => $this->lang->line('password_required')));
            $this->form_validation->set_rules('updatenewnm', 'updateoldpass', 'required|min_length[6]|max_length[16]', array( 'required' => $this->lang->line('new_password_required'),
                'min_length' => $this->lang->line('password_minlength'),
                'max_length' => $this->lang->line('password_maxlenght')
            ));
            $this->form_validation->set_rules('updatecofpassnm', 'updateoldpass', 'required|matches[updatenewnm]', array( 'required' => $this->lang->line('conf_password_required'), 'matches' => $this->lang->line('pass_confirm_not_match')));

            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('changepasswordclass', 'danger');
                $this->session->set_flashdata('passworderror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/profile');
            } else {

                $updatedata = array();
                $userid = $this->login_user_id;
                $oldpass = $this->input->post('updateoldpass');
                $newpass = $this->input->post('updatenewnm');
                $newcomfirm = $this->input->post('updatecofpassnm');
                $userdata = $this->dynamic_model->get_user($userid);
                if(encrypt_password($oldpass) == $userdata['password']){
                    $updatedata['password'] = encrypt_password($newpass);
                    $this->dynamic_model->updatedata(TABLE_USERS, $updatedata, $userid);

                    $this->session->set_flashdata('changepasswordclass', 'success');
                    $this->session->set_flashdata('passworderror', 'Password Change Successfully');
                    redirect(site_url().'admin/profile');
                } else {
                    $this->session->set_flashdata('changepasswordclass', 'danger');
                    $this->session->set_flashdata('passworderror', $this->lang->line('current_pass_incorrect'));
                    redirect(site_url().'admin/profile');
                }
            }
        } else {
            $this->session->set_flashdata('changepasswordclass', 'danger');
            $this->session->set_flashdata('passworderror', 'SomeProble in Server. Please Try Again');
            redirect(site_url().'admin/profile');
        }
        $this->admintemplates('profile');
    }




}
