<?php defined('BASEPATH') OR exit('No direct script access allowed');
//require APPPATH . '/libraries/REST_Controller.php';

/* * ***************Api.php**********************************
 * @product name    : Signal Health Group Inc
 * @type            : Class
 * @class name      : Api
 * @description     : Class for all the methods , public methods calling from mobile apps.  
 * @author          : Consagous Team 	
 * @url             : https://www.consagous.com/      
 * @support         : aamir.shaikh@consagous.com	
 * @copyright       : Consagous Team	 	
 * ********************************************************** */

class Transaction extends MX_Controller {

	public function __construct() {
		parent::__construct();
		header('Content-Type: application/json');
		$this->load->library('form_validation');
		$this->load->library('session');
		$this->load->model('dynamic_model');
		$this->load->model('studio_model');
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

	// App Version Check
	public function version_check_get()
	{
		$arg = array();
		$version_result = version_check_helper1();
		echo json_encode($version_result);
	}

	 /****************Function Get Plans List*********************
     * @type            : Function
     * @Author          : Aamir
     * @function name   : get_plan_list
     * @description     : get plan listing  
     * @param           : null
     * @return          : null 
     * ********************************************************** */
		
	public function plans_list()
	{
	    $arg = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
            $condition=array('status'=>'Active');
			$subscribe_data = $this->dynamic_model->getdatafromtable("subscribe_plan",$condition);
	       if(!empty($subscribe_data))
	       {
	            foreach ($subscribe_data as $value) 
	            {
	            	$subdata['plan_id']      = encode($value['id']);
	            	$subdata['plan_name']   = $value['plan_name'];
	            	$subdata['amount']       = $value['amount']; 
	            	$subdata['max_users']   = $value['max_users'];
	            	if($value['type']==1){
	            		$type= 'Monthly';
	            	}elseif($value['type']==2){ 
	                    $type= 'Half Yearly';
	                }else{
                       $type= 'Yearly';
	                }  
	            	$subdata['plan_type']   = $type;
	            	$finaldata[]	        = $subdata;
	            }
	            $arg['status']     = 1;
				$arg['error_code']  = HTTP_OK;
				$arg['error_line']= __line__;
				$arg['data']       = $finaldata;
				$arg['message']    = $this->lang->line('record_found');    
	       }
	       else
	       {   
			$arg['status']     = 0;
            $arg['error_code']  = HTTP_NOT_FOUND;
			$arg['error_line']= __line__;
			$arg['data']       = array();
			$arg['message']    = $this->lang->line('record_not_found');
              
	       }
	        
        }
      echo json_encode($arg);
    } 
    //Used function for payment checkout
	public function subcription_plan_purchase()
	{
		$arg    = array();
		$version_result = version_check_helper1();
		if($version_result['status'] != 1 )
		{
			$arg = $version_result;
		}
		else
		{
		   	
			$_POST = json_decode(file_get_contents("php://input"), true); 
			if($_POST)
			{
		        $this->form_validation->set_rules('user_id','User Id', 'required|trim', array( 'required' => $this->lang->line('user_id_required')));
		        $this->form_validation->set_rules('name','Name', 'required|trim', array( 'required' => $this->lang->line('first_name')));
				//$this->form_validation->set_rules('lastname','Last Name', 'required|trim', array( 'required' => $this->lang->line('last_name')));
	        	$this->form_validation->set_rules('plan_id', 'Plan Id','trim|required',array(
						'required'   => $this->lang->line('plan_id_required')
					  ));
		        $this->form_validation->set_rules('amount', 'Amount','required|greater_than[0]',array(
						'required'   => $this->lang->line('amount_required'),
						'numeric'    => $this->lang->line('amount_valid')
					  ));

			        if($this->input->post('save_card_check')==1)
			        {
		          		$this->form_validation->set_rules('card_number', 'Card Number', 'required|numeric|min_length[16]|max_length[16]',array(
							'required'   => $this->lang->line('card_required'),
							'min_length' => $this->lang->line('card_min_length'),
							'max_length' => $this->lang->line('card_max_length'),
							'numeric'    => $this->lang->line('card_numeric')
						));
				        $this->form_validation->set_rules('cvv_no', 'Cvv no', 'required|numeric|min_length[3]|max_length[3]',array(
								'required'   => $this->lang->line('cvv_no_required'),
								'min_length' => $this->lang->line('cvv_no_min_length'),
								'max_length' => $this->lang->line('cvv_no_max_length'),
								'numeric'    => $this->lang->line('cvv_no_numeric')
							));
			        	$this->form_validation->set_rules('expiry_month', 'Expiry Month', 'required|numeric|less_than_equal_to[12]|greater_than[0]|min_length[2]',array('required'=> $this->lang->line('expiry_month_required'),
							'min_length' => $this->lang->line('expiry_month_min_length'),
							'less_than_equal_to' => $this->lang->line('expiry_month_less_than_equal_to'),
							'greater_than' => $this->lang->line('expiry_month_greater_than'),
							'numeric' => $this->lang->line('expiry_month_numeric')
						));
			        	$this->form_validation->set_rules('expiry_year', 'Expiry Year', 'required|numeric|min_length[4]|max_length[4]',array(
							'required'   => $this->lang->line('expiry_year_required'),
							'min_length' => $this->lang->line('expiry_year_min_length'),
							'max_length' => $this->lang->line('expiry_year_max_length'),
							'numeric'    => $this->lang->line('expiry_year_numeric')
						)); 
			        }
					if($this->form_validation->run() == FALSE)
					{
						$arg['status']  = 0;
						$arg['message'] = get_form_error($this->form_validation->error_array());
					}
					else
					{
						$usid  = decode($this->input->post('user_id'));
						$where1  = array("id" => $usid);
						$loguser = $this->dynamic_model->getdatafromtable("user",$where1);
						if($loguser[0]['email_verified']!=='1')
						{	
						$arg['status']    = 0;
						$arg['error_code'] = HTTP_OK;
					 	$arg['error_line']= __line__;
					 	$arg['message']   = $this->lang->line('email_not_varify');
					 	$arg['data']      = array();
					 	 echo json_encode($arg);exit;	
					    }
                        $time = time();
                        $name            = $this->input->post('name');
                        $lastname        = $this->input->post('lastname');
                        $plan_id          = decode($this->input->post('plan_id'));
					    $amount           = $this->input->post('amount');  
			           	$cvv_no           = $this->input->post('cvv_no');
			            $card_number      = $this->input->post('card_number');
			            $card_type        = $this->input->post('card_type');
			            $expiry_month     = $this->input->post('expiry_month');
			            $expiry_year      = $this->input->post('expiry_year');
			            $save_card_check  = $this->input->post('save_card_check');
			            $token            = $this->input->post('token');
			            $country_code     = '1';//$this->input->post('country_code');

			            if($this->input->post('save_card_check')=="" || $this->input->post('save_card_check')==0)
			            {
			            	$savecard = 0;
			            }
			            else
			            {
			            	$savecard = 1;
			            }
			            

			            $card_res=$card_data=$card_Exist=array();
                        $card_data = $this->dynamic_model->getdatafromtable('saved_card_details',array('user_id'=> $usid),'id,card_details');
                        if(!empty($card_data)){
                        	foreach($card_data as $value){
                             $card_arr=json_decode(decode($value['card_details']));
                             $card_id=$value['id'];
                             $card_bank_no=$card_arr->card_bank_no;
                             if($card_number==$card_bank_no){
                               $card_Exist[]=array("id"=>$card_id,"card_bank_no"=>$card_bank_no);

                             }
                         }
                        }

			            if($savecard==1)
			            {
	                        $FirstFourNumber = substr($card_number, 0, 4); // get first 4
		                    $LastFourNumber  = substr($card_number, 12, 4); // get last 4
		                    $newCardNumber   = $FirstFourNumber.' XXXX XXXX '.$LastFourNumber;

	                        
	                        // check year is valid
					        if(check_expiry_year($expiry_year) == false)
					        {
					        	$arg['status']  = 0;
					        	$arg['error_code'] =  ERROR_FAILED_CODE;
								$arg['error_line']= __line__;
		                        $arg['message'] = $this->lang->line('invalid_expiry_year');
					            echo json_encode($arg);exit;
					        }
					        // check year is valid
					        if(check_expiry_month_year($expiry_month,$expiry_year) == false)
					        {
					        	$arg['status']  = 0;
					        	$arg['error_code'] =  ERROR_FAILED_CODE;
								$arg['error_line']= __line__;
		                        $arg['message'] = $this->lang->line('invalid_expiry_year_month');
					            echo json_encode($arg);exit;
					        }
					    }
	                        if(empty($card_Exist))
					        {
		                        // Use for faster checkout
		                        if(@$save_card_check == 1)
		                            $del_status = 0;
		                        else
		                            $del_status = 1;
			                        // Firstly insert data into saved_card_details table
			                       $card_detail= array(
					                                   
					                                    'acc_holder_name'  =>$name.' '.$lastname,
					                                    'card_bank_no'     =>$card_number,
					                                    'expiry_month'     =>$expiry_month,
					                                    'expiry_year'      =>$expiry_year
			                            			);
			                        $json_card_data=encode(json_encode($card_detail));
			                        $saved_card_details = array(
					                                    'user_id'          =>$usid,
					                                    'is_debit_card'    => 0 ,
					                                    'is_credit_card'    =>1,
					                                    'is_deleted'       =>$del_status,
					                                    'created_by'       =>$usid,
					                                    'updated_by'       =>$usid,
					                                    'create_dt'        =>$time,
					                                    'update_dt'        =>$time,
					                                    'card_details'     =>$json_card_data,
					                                    'card_token'       =>$token
			                            			);
			                       $pay_id = $this->dynamic_model->insertdata('saved_card_details', $saved_card_details);
			                }else{
		                       $pay_id = @$card_Exist[0]['id'];
		                    } 
		                

                        
						    $ref_num  = getuniquenumber();
						    $payment_id   = $this->input->post('payment_id');
			              //logic implement for purachase plan mothly haif yearly and yearly
                            $wh  = array("id" =>$plan_id);
			                $subscription_plan = $this->dynamic_model->getdatafromtable("subscribe_plan",$wh);
                            $subplan_type=$subscription_plan[0]['type']; 
				            $curr_date =$time;
                             $plan_data = $this->studio_model->plan_check($plan_id,$usid);
                             if(!empty($plan_data)){
                              $sub_end= $plan_data['sub_end'];
                              if($curr_date > $sub_end){
                              	    $plan_status = "Active";
									$start_date  = $time;
									$sdate=date('d M Y',$start_date);
									if($subplan_type==1){
					            		$end_date   = strtotime(date("Y-m-d h:i:s")." +30 days");
			                            $edate      = date('d M Y',$end_date);
					            	}elseif($subplan_type==2){
					                    $end_date   = strtotime(date("Y-m-d h:i:s")." +6 month");
					                    $edate      = date('d M Y',$end_date);
					                }else{
			                           $end_date    = strtotime(date("Y-m-d h:i:s")." +1 year");
			                           $edate       = date('d M Y',$end_date);
					                }   
                                    $where1 = array(
											'sub_user_id' => $usid,
											'sub_plan_id' => $plan_id
									);
                                  	$subdata = array(
										'plan_status' => "Expire"
									);
									$this->dynamic_model->updateRowWhere('subscription',$where1,$subdata);	
									$msg = $this->lang->line('subscription_succ');
                              }else{
                                  $plan_status= "Upcoming";  
                                  $sub_end    = date('Y-m-d h:i:s',$plan_data['sub_end']);
                                  $startdate  = date('Y-m-d h:i:s',strtotime($sub_end."+1 days"));
                                  $start_date = strtotime($startdate);
                                  $sdate=date('d M Y',$start_date);
                                  if($subplan_type==1){
					            		 $end_date   = strtotime(date('Y-m-d h:i:s',strtotime($startdate."+30 days")));
					            		 $edate      = date('d M Y',$end_date);
					            	}elseif($subplan_type==2){
					                     $end_date   = strtotime(date('Y-m-d h:i:s',strtotime($startdate."+6 month")));
					                    $edate       = date('d M Y',$end_date);
					                }else{
			                            $end_date   = strtotime(date('Y-m-d h:i:s',strtotime($startdate."+1 year")));
			                            $edate      = date('d M Y',$end_date);
					                } 
					                $msg =$this->lang->line('subscription_upcoming');     
                                 } 	
							}else{	
								 $plan_status = "Active";
								 $start_date  = $time;
								 $sdate=date('d M Y',$start_date);
									if($subplan_type==1){
					            		$end_date   = strtotime(date("Y-m-d h:i:s")." +30 days");
			                            $edate      = date('d M Y',$end_date);
					            	}elseif($subplan_type==2){
					                    $end_date   = strtotime(date("Y-m-d h:i:s")." +6 month");
					                    $edate      = date('d M Y',$end_date);
					                }else{
			                           $end_date    = strtotime(date("Y-m-d h:i:s")." +1 year");
			                           $edate       = date('d M Y',$end_date);
					                } 
					                $msg=$this->lang->line('subscription_succ');
							}
							
                             //End of logic implement for purachase plan mothly haif yearly and yearly
							 //Insert data in transaction table 
		                 	$transaction_data = array(
				                                   'user_id'                =>$usid,
				                                   'amount'                 =>$amount,
				                                    'trx_id'       		    =>(!empty($payment_id)) ? $payment_id : $ref_num,
				                                    'transaction_type'      =>1,
				                                    'payment_status'        =>"Success",
				                                    'saved_card_id'         =>$pay_id,
				                                    'create_dt'        		=>$time,
				                                    'update_dt'        		=>$time
		                            			);
		                $trx_id=$this->dynamic_model->insertdata('transactions',$transaction_data);
						$subscription_data = array(
				                                    'sub_user_id'           =>$usid,
				                                    'sub_plan_id'     		=>$plan_id,
				                                    'sub_start'       		=>$start_date,
													'sub_end'       		=>$end_date,
													'max_users_count'		=>$subscription_plan[0]['max_users'],
				                                    'create_dt'        		=>$time,
				                                    'plan_status'        	=>$plan_status,
				                                    'transaction_id'        =>$trx_id,
				                                    'update_dt'        		=>$time
		                            			);
		                 $sub_id=$this->dynamic_model->insertdata('subscription',$subscription_data);  
		                

		                
						$card_id       = '';
						$customer_name = '';
						$number        = '';
						$expiry_month  = '';
						$expiry_year   = '';
						$cvd           = '';
						$business_id   = '';

						$res_data = clover_api_card_profile_check($usid,$savecard,$customer_name,$number,$expiry_month,$expiry_year,$cvd,$country_code,$business_id,$token);
						$customer_code= $res_data['customer_code'];	



			           // Activate plan add in users table
			            $where2 = array(
										'id' => $usid,
								);
                              	$plandata = array(
									'name' => $name,
									'lastname' => $lastname,
									'plan_id' => $plan_id
								);
						$this->dynamic_model->updateRowWhere('user',$where2,$plandata);
			             //Get active plan data
			             $whe  = array("id" => $plan_id);
					     $activeplan_data = $this->dynamic_model->getdatafromtable("subscribe_plan",$whe); 
                         $plantype=(!empty($activeplan_data[0]['type'])) ? $activeplan_data[0]['type'] : '';
			             if($subplan_type==1){
			            		$plantype = 'Monthly';
			            	}elseif($subplan_type==2){
			                    $plantype = 'Haif Yearly';
			                }else{
	                            $plantype= 'Yearly';
			                } 
			             //End of active plan data
			             $response  = array('plan_name'=>$activeplan_data[0]['plan_name'],'plan_status'=> (string)$plan_status,'amount' => number_format((float)$activeplan_data[0]['amount'], 2, '.', ''),'transaction_date'=>date('d M Y'),'validity_from'=>$sdate,'validity_to'=>$edate,'plan_type'=>$plantype);
			             if($sub_id)
			             {
		                	$arg['status']    = 1;
		                	$arg['error_code'] = HTTP_OK;
							$arg['error_line']= __line__;
							$arg['message']   = $msg;
							$arg['data']      = $response;

						}
						else
						{
							$arg['status']    = 0;
			                $arg['error_code'] = HTTP_NOT_FOUND;
							$arg['error_line']= __line__;
							$arg['message']   = $this->lang->line('subscription_failed'); 
							$arg['data']      = array();
						}
						  
				   }   
			}	
		} 
	    echo json_encode($arg);
	}


//--------------------------*************End of Api*************---------------------------//

}
