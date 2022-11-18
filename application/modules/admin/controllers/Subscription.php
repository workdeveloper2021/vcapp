<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Subscription extends My_Controller{
    private $login_user_id = null;
    public function __construct(){      
        parent::__construct();      
        $this->load->model('dynamic_model');
        $this->load->model('admin_model');
        $this->load->model('subscription_model');
        $this->lang->load("admin_message", "english");
        if($this->session->userdata('logged_in')){
            $currentuser = getuserdetails();
            $this->login_user_id = $currentuser['id'];
        }
    }
            
    public function plan_list(){
        check_permission(VIEW,"subcription_plans_list",1);
        $data=array();
        $header['title'] = $this->lang->line('subcription_plan_list');        
        $this->admintemplates('subscription/plan_list',$data, $header);
    } 
    public function planAjaxlist(){
        $start         =  $this->input->get('start'); // get promo code Id
        $length        =  $this->input->get('length'); // get promo code Id
        $draw          =  $this->input->get('draw'); // get promo code Id
        $order   =  $this->input->get('order');
        if(!empty($order)){ 
            if($order[0]['column']==1){
                $column_name='plan_name';
            }else if($order[0]['column']==2){
                $column_name='amount';
            }else if($order[0]['column']==3){
                $column_name='max_users';                
            }else if($order[0]['column']==4){
                $column_name='create_dt';                
            }else{
                $column_name='id';
            }
        }
        $totalRecord      = $this->subscription_model->planAjaxlist(true);
        $getRecordListing = $this->subscription_model->planAjaxlist(false,$start,$length, $column_name, $order[0]['dir']);
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
                    $plan_url = base_url('admin/subscription/plan_update/').$plan_id;
                    $actionContent = ''; // set default empty
                    $content .='[';                     
                    // $recordListing[$i][0]=   '<input type="checkbox" name="checkAll[]" value="'.($recordData->id).'" class="cb-element" ">';
                    $recordListing[$i][0]=  $srNumber+1;
                    $recordListing[$i][1]= $recordData->plan_name;
                    $recordListing[$i][2]= CURRENCY.' '.$recordData->amount;
                    $recordListing[$i][3]= (!empty($recordData->max_users) ? $recordData->max_users:'0');
                    $recordListing[$i][4]= get_formated_date($recordData->create_dt, 2);
                   
                    $table = TABLE_SUBSCRIBE_PLAN;
                    $field = 'status';
                    $urls  =  base_url('admin/subscription/updateStatus');
                    $actionContent='';
                    if(check_permission(STATUS,"subcription_plans_list")==1){
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
                    if(check_permission(EDIT,"subcription_plans_list")==1){
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





            
    public function users_list(){
        check_permission(VIEW,"subcription_plans_list",1);
        $data=array();
        $header['title'] = $this->lang->line('subcription_users_list');        
        $this->admintemplates('subscription/users_list',$data, $header);
    } 
    public function usersAjaxlist(){
        $start         =  $this->input->get('start'); // get promo code Id
        $length        =  $this->input->get('length'); // get promo code Id
        $draw          =  $this->input->get('draw'); // get promo code Id
        $order   =  $this->input->get('order');
        if(!empty($order)){ 
            if($order[0]['column']==1){
                $column_name='name';
            }else if($order[0]['column']==2){
                $column_name='plan_name';
            }else if($order[0]['column']==3){
                $column_name='sub_start';                
            }else if($order[0]['column']==4){
                $column_name='sub_end';                
            }else if($order[0]['column']==5){
                $column_name='trx_id';                
            }else if($order[0]['column']==6){
                $column_name='payment_status';                
            }else{
                $column_name='sub_id';
            }
        }else{
                $column_name='sub_id';
        }

        if($order[0] && $order[0]['dir']){
            $getRecordListing = $this->subscription_model->usersAjaxlist(false,$start,$length, $column_name, $order[0]['dir']);
        }else{
            $getRecordListing = $this->subscription_model->usersAjaxlist(false,$start,$length, $column_name);
        }

        $totalRecord      = $this->subscription_model->usersAjaxlist(true);
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
                    // $plan_id = encode($recordData->sub_id);
                    // $plan_url = base_url('admin/subscription/plan_update/').$plan_id;
                    $actionContent = ''; // set default empty
                    $content .='[';                     
                    // $recordListing[$i][0]=   '<input type="checkbox" name="checkAll[]" value="'.($recordData->id).'" class="cb-element" ">';
                    $recordListing[$i][0]=  $srNumber+1;
                    $recordListing[$i][1]= $recordData->name." ".$recordData->lastname;
                    $recordListing[$i][2]= $recordData->plan_name;
                    $recordListing[$i][3]= get_formated_date($recordData->sub_start, 2);
                    $recordListing[$i][4]= get_formated_date($recordData->sub_end, 2);
                    $recordListing[$i][5]= $recordData->trx_id;
                    $recordListing[$i][6]= $recordData->payment_status;

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

    function exportCsvSubscrUsers(){

        $file      =  'Subscription-Users-List';
        $filename = $file."-".date("Y-m-d",time());

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="'.$filename.'.csv"');

        $userdata= $this->session->userdata('search_data');

        $header  = array(
                'S. No.',
                'Full Name',
                'Plan Name',
                'Start Date',
                'End Date',
                'Transaction Id',
                'Payment Status'
        );    

        $fp = fopen('php://output', 'wb');
        fputcsv($fp, $header);

        foreach ($userdata as $key => $value) {
            $array = array($key+1);

            array_push($array, $value["name"]." ".$value["lastname"]);
            array_push($array, $value["plan_name"]);
            array_push($array, get_formated_date($value["sub_start"]));
            array_push($array, get_formated_date($value["sub_end"]));
            array_push($array, $value["trx_id"]);
            array_push($array, $value["payment_status"]);

            fputcsv($fp, $array);
        }

        fclose($fp);

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
            $this->subscription_model->updateDataFromTabel($table, $updateData, $upWhere);
            //echo $this->db->last_query();die;
            $returnData = array('isSuccess' => true);
        } else {
            $returnData = array('isSuccess' => false);
        }
        echo json_encode($returnData);
    }

    /* Show Profile info */
    public function plan_update($plan_id=''){
        check_permission(EDIT,"subcription_plans_list",1);
        $pid =  decode($plan_id);
        if(!empty($plan_id) && !empty($pid)){
            $header['title'] = $this->lang->line('subcription_plan_update');
            $data['plandata']=$this->dynamic_model->getdatafromtable(TABLE_SUBSCRIBE_PLAN,array("id"=>$pid)); 
            $this->admintemplates('subscription/plan_update',$data, $header);
        } else{
            redirect(base_url('admin/subscription'));
        }
    }
    /* User Profile update by admin */
    public function plan_update_submit(){
        check_permission(EDIT,"subcription_plans_list",1);
        extract($this->input->post());
        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('plan_name', 'plan_name', 'required', array( 'required' => $this->lang->line('tb_plan_name')));
            $this->form_validation->set_rules('amount', 'amount', 'required', array( 'required' => $this->lang->line('tb_plan_price')));
            
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/subscription/userProfile/'.encode($updateid));
            } else {
                $updatedata = array();
                $updatedata['plan_name'] = $plan_name;
                $updatedata['amount'] = $amount;
                //if(!empty($max_users)){
                $updatedata['max_users'] = $max_users;
                //}
                $this->dynamic_model->updatedata(TABLE_SUBSCRIBE_PLAN, $updatedata,$updateid);
                 
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('subscription_update'));
                redirect(site_url().'admin/subscription/plan_update/'.encode($updateid));  
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProble in Server. Please Try Again');
            redirect(site_url().'admin/subscription/plan_update/'.encode($updateid));                    
        }
        $this->admintemplates('admin/subscription/plan_list');
    }

    public function plan_list_month(){
        check_permission(VIEW,"subcription_plans_list",1);
        $data=array();
        $header['title'] = $this->lang->line('subcription_plan_list_month');        
        $this->admintemplates('subscription/month_plan_list',$data, $header);
    } 
    public function planAjaxlistMonth(){
        $start         =  $this->input->get('start'); // get promo code Id
        $length        =  $this->input->get('length'); // get promo code Id
        $draw          =  $this->input->get('draw'); // get promo code Id
        $order   =  $this->input->get('order');
        if(!empty($order)){ 
            if($order[0]['column']==1){
                $column_name='plan_name';
            }else if($order[0]['column']==2){
                $column_name='amount';
            }else if($order[0]['column']==3){
                $column_name='max_users';                
            }else if($order[0]['column']==4){
                $column_name='create_dt';                
            }else{
                $column_name='id';
            }
        }
        $totalRecord      = $this->subscription_model->planAjaxlistMonth(true);
        $getRecordListing = $this->subscription_model->planAjaxlistMonth(false,$start,$length, $column_name, $order[0]['dir']);
       // echo $this->db->last_query();
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
                    $plan_url = base_url('admin/subscription/plan_update_month/').$plan_id;
                    $actionContent = ''; // set default empty
                    $content .='[';                     
                    // $recordListing[$i][0]=   '<input type="checkbox" name="checkAll[]" value="'.($recordData->id).'" class="cb-element" ">';
                    $recordListing[$i][0]=  $srNumber+1;
                    $recordListing[$i][1]= $recordData->plan_name;
                    $recordListing[$i][2]= CURRENCY.' '.$recordData->amount;
                    $recordListing[$i][3]= (!empty($recordData->max_users) ? $recordData->max_users:'0');
                    $recordListing[$i][4]= get_formated_date($recordData->create_dt, 2);
                   
                    $table = TABLE_SUBSCRIBE_PLAN;
                    $field = 'status';
                    $urls  =  base_url('admin/subscription/updateStatus');
                    $actionContent='';
                    if(check_permission(STATUS,"subcription_plans_list")==1){
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
                    if(check_permission(EDIT,"subcription_plans_list")==1){
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
    public function plan_update_month($plan_id=''){
        check_permission(EDIT,"subcription_plans_list",1);
        $pid =  decode($plan_id);
        if(!empty($plan_id) && !empty($pid)){
            $header['title'] = $this->lang->line('subcription_plan_update');
            $data['plandata']=$this->dynamic_model->getdatafromtable(TABLE_SUBSCRIBE_PLAN,array("id"=>$pid)); 
            $this->admintemplates('subscription/plan_update_month',$data, $header);
        } else{
            redirect(base_url('admin/subscription'));
        }
    }
    /* User Profile update by admin */
    public function plan_update_submit_month(){
        check_permission(EDIT,"subcription_plans_list",1);
        extract($this->input->post());
        $is_submit = $this->input->post('is_submit');
        if(isset($is_submit) && $is_submit == 1){
            $this->form_validation->set_rules('plan_name', 'plan_name', 'required', array( 'required' => $this->lang->line('tb_plan_name')));
            $this->form_validation->set_rules('amount', 'amount', 'required', array( 'required' => $this->lang->line('tb_plan_price')));
            
            if ($this->form_validation->run() == FALSE){
                $this->session->set_flashdata('updateclass', 'danger');
                $this->session->set_flashdata('updateerror', get_form_error($this->form_validation->error_array()));
                redirect(site_url().'admin/subscription/userProfile/'.encode($updateid));
            } else {
                $updatedata = array();
                $updatedata['plan_name'] = $plan_name;
                $updatedata['amount'] = $amount;
                //if(!empty($max_users)){
                $updatedata['max_users'] = $max_users;
                //}
                $this->dynamic_model->updatedata(TABLE_SUBSCRIBE_PLAN, $updatedata,$updateid);
                 
                $this->session->set_flashdata('updateclass', 'success');
                $this->session->set_flashdata('updateerror', $this->lang->line('subscription_update'));
                redirect(site_url().'admin/subscription/plan_update_month/'.encode($updateid));  
            }           
        } else {
             $this->session->set_flashdata('updateclass', 'danger');
             $this->session->set_flashdata('updateerror', 'SomeProble in Server. Please Try Again');
            redirect(site_url().'admin/subscription/plan_update_month/'.encode($updateid));                    
        }
        $this->admintemplates('admin/subscription/plan_list_month');
    }


}
