<?php defined('BASEPATH') or exit('No direct script access allowed');
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

class Transaction_new extends MX_Controller
{

	public function __construct()
	{
		parent::__construct();
		header('Content-Type: application/json');
		$this->load->library('form_validation');
		$this->load->library('session');
		$this->load->model('dynamic_model');
		$this->load->model('studio_model');
		$this->load->helper('stripe_helper');
		$language = $this->input->get_request_header('language');
		if ($language == "en") {
			$this->lang->load("web_message", "english");
		} else {
			$this->lang->load("web_message", "english");
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
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {
			$condition = array('status' => 'Active');
			$subscribe_data = $this->dynamic_model->getdatafromtable("subscribe_plan", $condition);
			if (!empty($subscribe_data)) {
				foreach ($subscribe_data as $value) {
					$subdata['plan_id']      = encode($value['id']);
					$subdata['plan_name']   = $value['plan_name'];
					$subdata['amount']       = $value['amount'];
					$subdata['max_users']   = $value['max_users'];
					if ($value['type'] == 1) {
						$type = 'Monthly';
					} elseif ($value['type'] == 2) {
						$type = 'Half Yearly';
					} else {
						$type = 'Yearly';
					}
					$subdata['plan_type']   = $type;
					$finaldata[]	        = $subdata;
				}
				$arg['status']     = 1;
				$arg['error_code']  = HTTP_OK;
				$arg['error_line'] = __line__;
				$arg['data']       = $finaldata;
				$arg['message']    = $this->lang->line('record_found');
			} else {
				$arg['status']     = 0;
				$arg['error_code']  = HTTP_NOT_FOUND;
				$arg['error_line'] = __line__;
				$arg['data']       = array();
				$arg['message']    = $this->lang->line('record_not_found');
			}
		}
		echo json_encode($arg);
	}


	public function subcription_plan_purchase_bk()
	{
		$arg    = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {

			$_POST = json_decode(file_get_contents("php://input"), true);
			if ($_POST) {
				$this->form_validation->set_rules('user_id', 'User Id', 'required|trim', array('required' => $this->lang->line('user_id_required')));
				$this->form_validation->set_rules('name', 'Name', 'required|trim', array('required' => $this->lang->line('first_name')));
				//$this->form_validation->set_rules('lastname','Last Name', 'required|trim', array( 'required' => $this->lang->line('last_name')));
				$this->form_validation->set_rules('plan_id', 'Plan Id', 'trim|required', array(
					'required'   => $this->lang->line('plan_id_required')
				));
				$this->form_validation->set_rules('amount', 'Amount', 'required|greater_than[0]', array(
					'required'   => $this->lang->line('amount_required'),
					'numeric'    => $this->lang->line('amount_valid')
				));

				if ($this->input->post('save_card_check') == 1) {
					$this->form_validation->set_rules('card_number', 'Card Number', 'required|numeric|min_length[16]|max_length[16]', array(
						'required'   => $this->lang->line('card_required'),
						'min_length' => $this->lang->line('card_min_length'),
						'max_length' => $this->lang->line('card_max_length'),
						'numeric'    => $this->lang->line('card_numeric')
					));
					$this->form_validation->set_rules('cvv_no', 'Cvv no', 'required|numeric|min_length[3]|max_length[3]', array(
						'required'   => $this->lang->line('cvv_no_required'),
						'min_length' => $this->lang->line('cvv_no_min_length'),
						'max_length' => $this->lang->line('cvv_no_max_length'),
						'numeric'    => $this->lang->line('cvv_no_numeric')
					));
					$this->form_validation->set_rules('expiry_month', 'Expiry Month', 'required|numeric|less_than_equal_to[12]|greater_than[0]|min_length[2]', array(
						'required' => $this->lang->line('expiry_month_required'),
						'min_length' => $this->lang->line('expiry_month_min_length'),
						'less_than_equal_to' => $this->lang->line('expiry_month_less_than_equal_to'),
						'greater_than' => $this->lang->line('expiry_month_greater_than'),
						'numeric' => $this->lang->line('expiry_month_numeric')
					));
					$this->form_validation->set_rules('expiry_year', 'Expiry Year', 'required|numeric|min_length[4]|max_length[4]', array(
						'required'   => $this->lang->line('expiry_year_required'),
						'min_length' => $this->lang->line('expiry_year_min_length'),
						'max_length' => $this->lang->line('expiry_year_max_length'),
						'numeric'    => $this->lang->line('expiry_year_numeric')
					));
				}
				if ($this->form_validation->run() == FALSE) {
					$arg['status']  = 0;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {
					$usid  = decode($this->input->post('user_id'));
					$where1  = array("id" => $usid);
					$loguser = $this->dynamic_model->getdatafromtable("user", $where1);
					if ($loguser[0]['email_verified'] !== '1') {
						$arg['status']    = 0;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['message']   = $this->lang->line('email_not_varify');
						$arg['data']      = array();
						echo json_encode($arg);
						exit;
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
					$country_code     = '1'; //$this->input->post('country_code');

					if ($this->input->post('save_card_check') == "" || $this->input->post('save_card_check') == 0) {
						$savecard = 0;
					} else {
						$savecard = 1;
					}


					$card_res = $card_data = $card_Exist = array();
					$card_data = $this->dynamic_model->getdatafromtable('saved_card_details', array('user_id' => $usid), 'id,card_details');
					if (!empty($card_data)) {
						foreach ($card_data as $value) {
							$card_arr = json_decode(decode($value['card_details']));
							$card_id = $value['id'];
							$card_bank_no = $card_arr->card_bank_no;
							if ($card_number == $card_bank_no) {
								$card_Exist[] = array("id" => $card_id, "card_bank_no" => $card_bank_no);
							}
						}
					}

					if ($savecard == 1) {
						$FirstFourNumber = substr($card_number, 0, 4); // get first 4
						$LastFourNumber  = substr($card_number, 12, 4); // get last 4
						$newCardNumber   = $FirstFourNumber . ' XXXX XXXX ' . $LastFourNumber;


						// check year is valid
						if (check_expiry_year($expiry_year) == false) {
							$arg['status']  = 0;
							$arg['error_code'] =  ERROR_FAILED_CODE;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('invalid_expiry_year');
							echo json_encode($arg);
							exit;
						}
						// check year is valid
						if (check_expiry_month_year($expiry_month, $expiry_year) == false) {
							$arg['status']  = 0;
							$arg['error_code'] =  ERROR_FAILED_CODE;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('invalid_expiry_year_month');
							echo json_encode($arg);
							exit;
						}
					}
					if (empty($card_Exist)) {
						// Use for faster checkout
						if (@$save_card_check == 1)
							$del_status = 0;
						else
							$del_status = 1;
						// Firstly insert data into saved_card_details table
						$card_detail = array(

							'acc_holder_name'  => $name . ' ' . $lastname,
							'card_bank_no'     => $card_number,
							'expiry_month'     => $expiry_month,
							'expiry_year'      => $expiry_year
						);
						$json_card_data = encode(json_encode($card_detail));
						$saved_card_details = array(
							'user_id'          => $usid,
							'is_debit_card'    => 0,
							'is_credit_card'    => 1,
							'is_deleted'       => $del_status,
							'created_by'       => $usid,
							'updated_by'       => $usid,
							'create_dt'        => $time,
							'update_dt'        => $time,
							'card_details'     => $json_card_data,
							'card_token'       => $token
						);
						$pay_id = $this->dynamic_model->insertdata('saved_card_details', $saved_card_details);
					} else {
						$pay_id = @$card_Exist[0]['id'];
					}



					$ref_num  = getuniquenumber();
					$payment_id   = $this->input->post('payment_id');
					//logic implement for purachase plan mothly haif yearly and yearly
					$wh  = array("id" => $plan_id);
					$subscription_plan = $this->dynamic_model->getdatafromtable("subscribe_plan", $wh);
					$subplan_type = $subscription_plan[0]['type'];
					$curr_date = $time;
					$plan_data = $this->studio_model->plan_check($plan_id, $usid);
					$msg = $this->lang->line('subscription_succ');
					if (!empty($plan_data)) {
						$sub_end = $plan_data['sub_end'];
						if ($curr_date > $sub_end) {
							$plan_status = "Active";
							$start_date  = $time;
							$sdate = date('d M Y', $start_date);
							if ($subplan_type == 1) {
								$end_date   = strtotime(date("Y-m-d h:i:s") . " +30 days");
								$edate      = date('d M Y', $end_date);
							} elseif ($subplan_type == 2) {
								$end_date   = strtotime(date("Y-m-d h:i:s") . " +6 month");
								$edate      = date('d M Y', $end_date);
							} else {
								$end_date    = strtotime(date("Y-m-d h:i:s") . " +1 year");
								$edate       = date('d M Y', $end_date);
							}
							$where1 = array(
								'sub_user_id' => $usid,
								'sub_plan_id' => $plan_id
							);
							$subdata = array(
								'plan_status' => "Expire"
							);
							$this->dynamic_model->updateRowWhere('subscription', $where1, $subdata);
							$msg = $this->lang->line('subscription_succ');
						} else {
							$plan_status = "Upcoming";
							$sub_end    = date('Y-m-d h:i:s', $plan_data['sub_end']);
							$startdate  = date('Y-m-d h:i:s', strtotime($sub_end . "+1 days"));
							$start_date = strtotime($startdate);
							$sdate = date('d M Y', $start_date);
							if ($subplan_type == 1) {
								$end_date   = strtotime(date('Y-m-d h:i:s', strtotime($startdate . "+30 days")));
								$edate      = date('d M Y', $end_date);
							} elseif ($subplan_type == 2) {
								$end_date   = strtotime(date('Y-m-d h:i:s', strtotime($startdate . "+6 month")));
								$edate       = date('d M Y', $end_date);
							} else {
								$end_date   = strtotime(date('Y-m-d h:i:s', strtotime($startdate . "+1 year")));
								$edate      = date('d M Y', $end_date);
							}
							$msg = $this->lang->line('subscription_upcoming');
						}
					} else {
						$plan_status = "Active";
						$start_date  = $time;
						$sdate = date('d M Y', $start_date);
						if ($subplan_type == 1) {
							$end_date   = strtotime(date("Y-m-d h:i:s") . " +30 days");
							$edate      = date('d M Y', $end_date);
						} elseif ($subplan_type == 2) {
							$end_date   = strtotime(date("Y-m-d h:i:s") . " +6 month");
							$edate      = date('d M Y', $end_date);
						} else {
							$end_date    = strtotime(date("Y-m-d h:i:s") . " +1 year");
							$edate       = date('d M Y', $end_date);
						}
						$msg = $this->lang->line('subscription_succ');
					}

					//End of logic implement for purachase plan mothly haif yearly and yearly
					//Insert data in transaction table
					$transaction_data = array(
						'user_id'                => $usid,
						'amount'                 => $amount,
						'trx_id'       		    => (!empty($payment_id)) ? $payment_id : $ref_num,
						'transaction_type'      => 1,
						'payment_status'        => "Success",
						'saved_card_id'         => $pay_id,
						'create_dt'        		=> $time,
						'update_dt'        		=> $time
					);
					$trx_id = $this->dynamic_model->insertdata('transactions', $transaction_data);
					$subscription_data = array(
						'sub_user_id'           => $usid,
						'sub_plan_id'     		=> $plan_id,
						'sub_start'       		=> $start_date,
						'sub_end'       		=> $end_date,
						'max_users_count'		=> $subscription_plan[0]['max_users'],
						'create_dt'        		=> $time,
						'plan_status'        	=> $plan_status,
						'transaction_id'        => $trx_id,
						'update_dt'        		=> $time
					);
					$sub_id = $this->dynamic_model->insertdata('subscription', $subscription_data);



					$card_id       = '';
					$customer_name = '';
					$number        = '';
					$expiry_month  = '';
					$expiry_year   = '';
					$cvd           = '';
					$business_id   = '';

					$res_data = clover_api_card_profile_check($usid, $savecard, $customer_name, $number, $expiry_month, $expiry_year, $cvd, $country_code, $business_id, $token);
					$customer_code = $res_data['customer_code'];



					// Activate plan add in users table
					$where2 = array(
						'id' => $usid,
					);
					$plandata = array(
						'name' => $name,
						'lastname' => $lastname,
						'plan_id' => $plan_id
					);
					$this->dynamic_model->updateRowWhere('user', $where2, $plandata);
					//Get active plan data
					$whe  = array("id" => $plan_id);
					$activeplan_data = $this->dynamic_model->getdatafromtable("subscribe_plan", $whe);
					$plantype = (!empty($activeplan_data[0]['type'])) ? $activeplan_data[0]['type'] : '';
					if ($subplan_type == 1) {
						$plantype = 'Monthly';
					} elseif ($subplan_type == 2) {
						$plantype = 'Haif Yearly';
					} else {
						$plantype = 'Yearly';
					}
					//End of active plan data
					$response  = array('plan_name' => $activeplan_data[0]['plan_name'], 'plan_status' => (string)$plan_status, 'amount' => number_format((float)$activeplan_data[0]['amount'], 2, '.', ''), 'transaction_date' => date('d M Y'), 'validity_from' => $sdate, 'validity_to' => $edate, 'plan_type' => $plantype);
					if ($sub_id) {
						$arg['status']    = 1;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['message']   = $msg;
						$arg['data']      = $response;
					} else {
						$arg['status']    = 0;
						$arg['error_code'] = HTTP_NOT_FOUND;
						$arg['error_line'] = __line__;
						$arg['message']   = $this->lang->line('subscription_failed');
						$arg['data']      = array();
					}
				}
			}
		}
		echo json_encode($arg);
	}


	//Used function for payment checkout
	public function subcription_plan_purchase_bk2()
	{
		$arg    = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {

			$_POST = json_decode(file_get_contents("php://input"), true);
			if ($_POST) {
				$this->form_validation->set_rules('user_id', 'User Id', 'required|trim', array('required' => $this->lang->line('user_id_required')));
				$this->form_validation->set_rules('name', 'Name', 'required|trim', array('required' => $this->lang->line('first_name')));
				//$this->form_validation->set_rules('lastname','Last Name', 'required|trim', array( 'required' => $this->lang->line('last_name')));
				$this->form_validation->set_rules('plan_id', 'Plan Id', 'trim|required', array(
					'required'   => $this->lang->line('plan_id_required')
				));
				$this->form_validation->set_rules('amount', 'Amount', 'required|greater_than[0]', array(
					'required'   => $this->lang->line('amount_required'),
					'numeric'    => $this->lang->line('amount_valid')
				));

				if ($this->input->post('save_card_check') == 1) {
					$this->form_validation->set_rules('card_number', 'Card Number', 'required|numeric|min_length[16]|max_length[16]', array(
						'required'   => $this->lang->line('card_required'),
						'min_length' => $this->lang->line('card_min_length'),
						'max_length' => $this->lang->line('card_max_length'),
						'numeric'    => $this->lang->line('card_numeric')
					));
					$this->form_validation->set_rules('cvv_no', 'Cvv no', 'required|numeric|min_length[3]|max_length[3]', array(
						'required'   => $this->lang->line('cvv_no_required'),
						'min_length' => $this->lang->line('cvv_no_min_length'),
						'max_length' => $this->lang->line('cvv_no_max_length'),
						'numeric'    => $this->lang->line('cvv_no_numeric')
					));
					$this->form_validation->set_rules('expiry_month', 'Expiry Month', 'required|numeric|less_than_equal_to[12]|greater_than[0]|min_length[2]', array(
						'required' => $this->lang->line('expiry_month_required'),
						'min_length' => $this->lang->line('expiry_month_min_length'),
						'less_than_equal_to' => $this->lang->line('expiry_month_less_than_equal_to'),
						'greater_than' => $this->lang->line('expiry_month_greater_than'),
						'numeric' => $this->lang->line('expiry_month_numeric')
					));
					$this->form_validation->set_rules('expiry_year', 'Expiry Year', 'required|numeric|min_length[4]|max_length[4]', array(
						'required'   => $this->lang->line('expiry_year_required'),
						'min_length' => $this->lang->line('expiry_year_min_length'),
						'max_length' => $this->lang->line('expiry_year_max_length'),
						'numeric'    => $this->lang->line('expiry_year_numeric')
					));
				}
				if ($this->form_validation->run() == FALSE) {
					$arg['status']  = 0;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {
					$usid  = decode($this->input->post('user_id'));
					$where1  = array("id" => $usid);
					$loguser = $this->dynamic_model->getdatafromtable("user", $where1);
					if ($loguser[0]['email_verified'] !== '1') {
						$arg['status']    = 0;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['message']   = $this->lang->line('email_not_varify');
						$arg['data']      = array();
						echo json_encode($arg);
						exit;
					}
					$time = strtotime(date("Y-m-d H:i:s")); //time();
					$name            = $this->input->post('name');
					$lastname        = $this->input->post('lastname');
					$pre_plan_id      = decode($this->input->post('pre_plan_id'));
					$plan_id          = decode($this->input->post('plan_id'));
					$amount           = $this->input->post('amount');
					$cvv_no           = $this->input->post('cvv_no');
					$card_number      = $this->input->post('card_number');
					$card_type        = $this->input->post('card_type');
					$expiry_month     = $this->input->post('expiry_month');
					$expiry_year      = $this->input->post('expiry_year');
					$save_card_check  = $this->input->post('save_card_check');
					$token            = $this->input->post('token');
					$country_code     = $this->input->post('country_code');
					$new_amount = $amount;
					/*if($this->input->post('save_card_check')=="" || $this->input->post('save_card_check')==0)
			            {
			            	$savecard = 0;
			            }
			            else
			            {
			            	$savecard = 1;
			            }*/

					$savecard = $save_card_check;

					$card_res = $card_data = $card_Exist = array();
					$card_data = $this->dynamic_model->getdatafromtable('saved_card_details', array('user_id' => $usid), 'id,card_details');
					if (!empty($card_data)) {
						foreach ($card_data as $value) {
							$card_arr = json_decode(decode($value['card_details']));
							$card_id = $value['id'];
							$card_bank_no = $card_arr->card_bank_no;
							if ($card_number == $card_bank_no) {
								$card_Exist[] = array("id" => $card_id, "card_bank_no" => $card_bank_no);
							}
						}
					}

					if ($savecard == 1) {
						$FirstFourNumber = substr($card_number, 0, 4); // get first 4
						$LastFourNumber  = substr($card_number, 12, 4); // get last 4
						$newCardNumber   = $FirstFourNumber . ' XXXX XXXX ' . $LastFourNumber;


						// check year is valid
						if (check_expiry_year($expiry_year) == false) {
							$arg['status']  = 0;
							$arg['error_code'] =  ERROR_FAILED_CODE;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('invalid_expiry_year');
							echo json_encode($arg);
							exit;
						}
						// check year is valid
						if (check_expiry_month_year($expiry_month, $expiry_year) == false) {
							$arg['status']  = 0;
							$arg['error_code'] =  ERROR_FAILED_CODE;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('invalid_expiry_year_month');
							echo json_encode($arg);
							exit;
						}
					}
					if (empty($card_Exist)) {
						// Use for faster checkout
						if (@$save_card_check == 1)
							$del_status = 0;
						else
							$del_status = 1;
						// Firstly insert data into saved_card_details table
						$card_detail = array(

							'acc_holder_name'  => $name . ' ' . $lastname,
							'card_bank_no'     => $card_number,
							'expiry_month'     => $expiry_month,
							'expiry_year'      => $expiry_year
						);
						$json_card_data = encode(json_encode($card_detail));
						$saved_card_details = array(
							'user_id'          => $usid,
							'is_debit_card'    => 0,
							'is_credit_card'    => 1,
							'is_deleted'       => $del_status,
							'created_by'       => $usid,
							'updated_by'       => $usid,
							'create_dt'        => $time,
							'update_dt'        => $time,
							'card_details'     => $json_card_data,
							'card_token'       => $token
						);
						$pay_id = $this->dynamic_model->insertdata('saved_card_details', $saved_card_details);
					} else {
						$pay_id = @$card_Exist[0]['id'];
					}



					$ref_num  = getuniquenumber();
					$payment_id   = $this->input->post('payment_id');
					//logic implement for purachase plan mothly haif yearly and yearly
					$wh  = array("id" => $pre_plan_id);
					$subscription_plan = $this->dynamic_model->getdatafromtable("subscribe_plan", $wh);
					if (!empty($subscription_plan)) {
						$pre_subplan_type = $subscription_plan[0]['type'];
						$previous_amount = $subscription_plan[0]['amount'];

						if ($pre_subplan_type == 1) {
							$pre_one_day_amount = ($previous_amount / 30);
						} else if ($pre_subplan_type == 2) {
							$pre_one_day_amount = ($previous_amount / 180);
						} else {
							$pre_one_day_amount = ($previous_amount / 356);
						}
					} else {
						$pre_one_day_amount = 0;
					}


					$wh1  = array("id" => $plan_id);
					$subscription_plan1 = $this->dynamic_model->getdatafromtable("subscribe_plan", $wh1);
					$subplan_type = $subscription_plan1[0]['type'];
					$newplan_amount = $subscription_plan1[0]['amount'];


					$curr_date = $time;
					$plan_data = $this->studio_model->plan_check($pre_plan_id, $usid);
					if (!empty($plan_data)) {
						$sub_end = $plan_data['sub_end'];
						if ($curr_date > $sub_end) {
							$plan_status = "Active";
							$start_date  = strtotime(date("Y-m-d 00:00:00") . " +14 days");
							$sdate = date('d M Y', $start_date);
							if ($subplan_type == 1) {
								$end_date   = strtotime(date("Y-m-d 23:59:59") . " +43 days"); //30 + Free 14 days trial
								$edate      = date('d M Y', $end_date);
							} elseif ($subplan_type == 2) {
								$end_date   = strtotime(date("Y-m-d 23:59:59") . " +180 days"); //6 month + Free 14 days trial
								$edate      = date('d M Y', $end_date);
							} else {
								$end_date    = strtotime(date("Y-m-d 23:59:59") . " +365 days"); // 1 year +  Free 14 days trial
								$edate       = date('d M Y', $end_date);
							}
							/*$where1 = array(
											'sub_user_id' => $usid,
											'sub_plan_id' => $pre_plan_id
									);
                                  	$subdata = array(
										'plan_status' => "Expire"
									);
									$this->dynamic_model->updateRowWhere('subscription',$where1,$subdata);	*/
							$msg = $this->lang->line('subscription_succ');
						} else {

							$days = 0;
							$now = $time; //time(); // or your date as well
							//$start_your_date = $plan_data['sub_start'];
							if ($now > $sub_end) {
								$datediff = $now - $sub_end;
								$days = @round($datediff / (60 * 60 * 24));
								//used_days

								$plan_status = "Active";
								//$sub_end    = date('Y-m-d 23:59:59',$plan_data['sub_end']);
								//$startdate  = date('Y-m-d 00:00:00',strtotime($sub_end."+1 days"));

								$startdate  = date('Y-m-d 00:00:00');
								$start_date = strtotime($startdate . "+1 days");
								$sdate = date('d M Y', $start_date);
								if ($subplan_type == 1) {
									$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($startdate . "+" . (30 - $days) . " days")));
									$edate      = date('d M Y', $end_date);
								} elseif ($subplan_type == 2) {
									$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($startdate . "+" . (180 - $days) . " days")));
									$edate       = date('d M Y', $end_date);
								} else {
									$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($startdate . "+" . (365 - $days) . " days")));
									$edate      = date('d M Y', $end_date);
								}
							} else {
								$datediff = $sub_end - $now;
								$days = @round($datediff / (60 * 60 * 24));
								//remaining_days

								$plan_status = "Active";
								//$sub_end    = date('Y-m-d 23:59:59',$plan_data['sub_end']);
								//$startdate  = date('Y-m-d 00:00:00',strtotime($sub_end."+1 days"));

								$startdate  = date('Y-m-d 00:00:00');
								$start_date = strtotime($startdate . "+1 days");
								$sdate2 = date('Y-m-d 00:00:00', $start_date);
								$sdate = date('d M Y', $start_date);
								$start_date = strtotime($sdate2);
								if ($subplan_type == 1) {

									//$pre_one_day_amount = ($previous_amount/30);
									$remaining_days_amount = $days * $pre_one_day_amount;
									$new_amount = $amount - $remaining_days_amount;

									$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($sdate2 . "+30 days")));
									$edate      = date('d M Y', $end_date);
								} elseif ($subplan_type == 2) {

									//$pre_one_day_amount = ($previous_amount/180);
									$remaining_days_amount = $days * $pre_one_day_amount;
									$new_amount = $amount - $remaining_days_amount;

									$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($sdate2 . "+180 days")));
									$edate       = date('d M Y', $end_date);
								} else {

									//$pre_one_day_amount = ($previous_amount/365);
									$remaining_days_amount = $days * $pre_one_day_amount;
									$new_amount = $amount - $remaining_days_amount;

									$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($sdate2 . "+365 days")));
									$edate      = date('d M Y', $end_date);
								}
							}


							$msg = $this->lang->line('subscription_upcoming');
						}
					} else {
						$plan_status = "Active";
						$start_date  = strtotime(date("Y-m-d 00:00:00") . " +14 days");
						$sdate = date('d M Y', $start_date);
						if ($subplan_type == 1) {
							$end_date   = strtotime(date("Y-m-d 23:59:59") . " +43 days"); //30 + Free 14 days trial
							$edate      = date('d M Y', $end_date);
						} elseif ($subplan_type == 2) {
							$end_date   = strtotime(date("Y-m-d 23:59:59") . " +180 days"); //6 month + Free 14 days trial
							$edate      = date('d M Y', $end_date);
						} else {
							$end_date    = strtotime(date("Y-m-d 23:59:59") . " +365 days"); // 1 year +  Free 14 days trial
							$edate       = date('d M Y', $end_date);
						}
						$msg = $this->lang->line('subscription_succ');
					}

					$where1 = array(
						'sub_user_id' => $usid,
						'sub_plan_id' => $pre_plan_id
					);
					$subdata = array(
						'plan_status' => "Expire"
					);
					$this->dynamic_model->updateRowWhere('subscription', $where1, $subdata);

					//End of logic implement for purachase plan mothly haif yearly and yearly
					//Insert data in transaction table
					$transaction_data = array(
						'user_id'                => $usid,
						'amount'                 => $amount,
						'new_amount'				=> $new_amount,
						'trx_id'       		    => (!empty($payment_id)) ? $payment_id : $ref_num,
						'transaction_type'      => 1,
						'payment_status'        => "Success",
						'saved_card_id'         => $pay_id,
						'create_dt'        		=> $time,
						'update_dt'        		=> $time
					);
					$trx_id = $this->dynamic_model->insertdata('transactions', $transaction_data);
					$subscription_data = array(
						'sub_user_id'           => $usid,
						'sub_plan_id'     		=> $plan_id,
						'sub_start'       		=> $start_date,
						'sub_end'       		=> $end_date,
						'max_users_count'		=> $subscription_plan1[0]['max_users'],
						'create_dt'        		=> $time,
						'plan_status'        	=> $plan_status,
						'transaction_id'        => $trx_id,
						'update_dt'        		=> $time
					);
					$sub_id = $this->dynamic_model->insertdata('subscription', $subscription_data);

					$where1 = array('user_id' => $usid, 'status' => 'Active');
					$business_data = $this->dynamic_model->getdatafromtable('business', $where1);
					if (!empty($business_data)) {
						$business_id   = $business_data[0]['id'];
					} else {
						$business_id = 0;
					}

					$card_id       = '';
					$customer_name = '';
					$number        = '';
					$expiry_month  = '';
					$expiry_year   = '';
					$cvd           = '';


					$res_data = clover_api_card_profile_check($usid, $savecard, $customer_name, $number, $expiry_month, $expiry_year, $cvd, $country_code, $business_id, $token);
					$customer_code = $res_data['customer_code'];



					// Activate plan add in users table
					$where2 = array(
						'id' => $usid,
					);
					$plandata = array(
						'name' => $name,
						'lastname' => $lastname,
						'plan_id' => $plan_id
					);
					$this->dynamic_model->updateRowWhere('user', $where2, $plandata);
					//Get active plan data
					$whe  = array("id" => $plan_id);
					$activeplan_data = $this->dynamic_model->getdatafromtable("subscribe_plan", $whe);
					$plantype = (!empty($activeplan_data[0]['type'])) ? $activeplan_data[0]['type'] : '';
					if ($subplan_type == 1) {
						$plantype = 'Monthly';
					} elseif ($subplan_type == 2) {
						$plantype = 'Half Yearly';
					} else {
						$plantype = 'Yearly';
					}
					//End of active plan data
					$response  = array('plan_name' => $activeplan_data[0]['plan_name'], 'plan_status' => (string)$plan_status, 'amount' => number_format((float)$activeplan_data[0]['amount'], 2, '.', ''), 'transaction_date' => date('d M Y'), 'validity_from' => $sdate, 'validity_to' => $edate, 'plan_type' => $plantype);
					if ($sub_id) {
						$arg['status']    = 1;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['message']   = $msg;
						$arg['data']      = $response;
					} else {
						$arg['status']    = 0;
						$arg['error_code'] = HTTP_NOT_FOUND;
						$arg['error_line'] = __line__;
						$arg['message']   = $this->lang->line('subscription_failed');
						$arg['data']      = array();
					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function fetch_clover_payment_token($business_id, $country_code, $number, $expiry_month, $expiry_year, $cvd)
	{
		$clover_key = '';
		if ($business_id != "") {
			$mid          = getUserMarchantId($business_id);
			$marchant_id  = $mid['marchant_id'];
			$country_code = $mid['marchant_id_type'];
			$clover_key   = $mid['clover_key'];
			$access_token = $mid['access_token'];


			if (empty($marchant_id)) {
				$arg['status'] = 0;
				$arg['error_code'] = ERROR_FAILED_CODE;
				$arg['error_line'] = __line__;
				$arg['message'] = 'Merchant id is empty';
				$arg['data'] = json_decode('{}');
				echo json_encode($arg);
				die;
			} else if (empty($country_code)) {
				$arg['status'] = 0;
				$arg['error_code'] = ERROR_FAILED_CODE;
				$arg['error_line'] = __line__;
				$arg['message'] = 'Merchant country code is empty';
				$arg['data'] = json_decode('{}');
				echo json_encode($arg);
				die;
			} else if (empty($clover_key)) {
				$arg['status'] = 0;
				$arg['error_code'] = ERROR_FAILED_CODE;
				$arg['error_line'] = __line__;
				$arg['message'] = 'Merchant clover key is empty';
				$arg['data'] = json_decode('{}');
				echo json_encode($arg);
				die;
			} else if (empty($access_token)) {
				$arg['status'] = 0;
				$arg['error_code'] = ERROR_FAILED_CODE;
				$arg['error_line'] = __line__;
				$arg['message'] = 'Merchant clover access token is empty';
				$arg['data'] = json_decode('{}');
				echo json_encode($arg);
				die;
			}
		} else {
			if ($country_code == 1) // For USA
			{
				$marchant_id  = MERCHANT_ID_USA;
				$country_code = $country_code;
				$clover_key   = CLOVER_KEY_USA;
				$access_token = ACCESS_TOKEN_USA;
			} else if ($country_code == 2) // For CAD
			{
				$marchant_id  = MERCHANT_ID_CAD;
				$country_code = $country_code;
				$clover_key   = CLOVER_KEY_CAD;
				$access_token = ACCESS_TOKEN_CAD;
			}
		}
		if (empty($clover_key)) {
			$arg['status'] = 0;
			$arg['error_code'] = ERROR_FAILED_CODE;
			$arg['error_line'] = __line__;
			$arg['message'] = 'Please enter valid data';
			$arg['data'] = json_decode('{}');
			echo json_encode($arg);
			exit;
		}
		$token = getCloverToken($number, $expiry_month, $expiry_year, $cvd, $clover_key);
		if ($token) {
			$res = array('token' => $token);
			$arg['status'] = 1;
			$arg['error_code'] = HTTP_OK;
			$arg['error_line'] = __line__;
			$arg['message'] = 'Clover Payment token';
			$arg['data'] = $res;
		} else {
			$arg['status'] = 0;
			$arg['error_code'] = ERROR_FAILED_CODE;
			$arg['error_line'] = __line__;
			$arg['message'] = 'Invalid Details';
			$arg['data'] = json_decode('{}');
			echo json_encode($arg);
		}
		return json_encode($arg);
	}

	public function subcription_plan_purchase_dp()
	{
		$arg    = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {

			$_POST = json_decode(file_get_contents("php://input"), true);
			if ($_POST) {
				$this->form_validation->set_rules('user_id', 'User Id', 'required|trim', array('required' => $this->lang->line('user_id_required')));
				$this->form_validation->set_rules('name', 'Name', 'required|trim', array('required' => $this->lang->line('first_name')));
				//$this->form_validation->set_rules('lastname','Last Name', 'required|trim', array( 'required' => $this->lang->line('last_name')));
				$this->form_validation->set_rules('plan_id', 'Plan Id', 'trim|required', array(
					'required'   => $this->lang->line('plan_id_required')
				));
				$this->form_validation->set_rules('amount', 'Amount', 'required|greater_than[0]', array(
					'required'   => $this->lang->line('amount_required'),
					'numeric'    => $this->lang->line('amount_valid')
				));

				if ($this->input->post('save_card_check') == 1) {
					$this->form_validation->set_rules('card_number', 'Card Number', 'required|numeric|min_length[16]|max_length[16]', array(
						'required'   => $this->lang->line('card_required'),
						'min_length' => $this->lang->line('card_min_length'),
						'max_length' => $this->lang->line('card_max_length'),
						'numeric'    => $this->lang->line('card_numeric')
					));
					$this->form_validation->set_rules('cvv_no', 'Cvv no', 'required|numeric|min_length[3]|max_length[3]', array(
						'required'   => $this->lang->line('cvv_no_required'),
						'min_length' => $this->lang->line('cvv_no_min_length'),
						'max_length' => $this->lang->line('cvv_no_max_length'),
						'numeric'    => $this->lang->line('cvv_no_numeric')
					));
					$this->form_validation->set_rules('expiry_month', 'Expiry Month', 'required|numeric|less_than_equal_to[12]|greater_than[0]|min_length[2]', array(
						'required' => $this->lang->line('expiry_month_required'),
						'min_length' => $this->lang->line('expiry_month_min_length'),
						'less_than_equal_to' => $this->lang->line('expiry_month_less_than_equal_to'),
						'greater_than' => $this->lang->line('expiry_month_greater_than'),
						'numeric' => $this->lang->line('expiry_month_numeric')
					));
					$this->form_validation->set_rules('expiry_year', 'Expiry Year', 'required|numeric|min_length[4]|max_length[4]', array(
						'required'   => $this->lang->line('expiry_year_required'),
						'min_length' => $this->lang->line('expiry_year_min_length'),
						'max_length' => $this->lang->line('expiry_year_max_length'),
						'numeric'    => $this->lang->line('expiry_year_numeric')
					));
				}
				if ($this->form_validation->run() == FALSE) {

					$arg['status']  = 0;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {

					$usid  = decode($this->input->post('user_id'));

					$time = strtotime(date("Y-m-d H:i:s")); //time();
					$name            = $this->input->post('name');
					$lastname        = $this->input->post('lastname');
					$pre_plan_id      = decode($this->input->post('pre_plan_id'));
					$plan_id          = decode($this->input->post('plan_id'));
					$amount           = $this->input->post('amount');
					$cvv_no           = $this->input->post('cvv_no');
					$card_number      = $this->input->post('card_number');
					$card_type        = $this->input->post('card_type');
					$expiry_month     = $this->input->post('expiry_month');
					$expiry_year      = $this->input->post('expiry_year');
					$save_card_check  = $this->input->post('save_card_check');
					$token  = $this->input->post('token');
					$savecard = $save_card_check;
					$new_amount = $amount;
					// check year is valid
					if (check_expiry_year($expiry_year) == false) {
						$arg['status']  = 0;
						$arg['error_code'] =  ERROR_FAILED_CODE;
						$arg['error_line'] = __line__;
						$arg['message'] = $this->lang->line('invalid_expiry_year');
						echo json_encode($arg);
						exit;
					}

					// check year is valid
					if (check_expiry_month_year($expiry_month, $expiry_year) == false) {
						$arg['status']  = 0;
						$arg['error_code'] =  ERROR_FAILED_CODE;
						$arg['error_line'] = __line__;
						$arg['message'] = $this->lang->line('invalid_expiry_year_month');
						echo json_encode($arg);
						exit;
					}

					$where1  = array("id" => $usid);
					$loguser = $this->dynamic_model->getdatafromtable("user", $where1);
					if ($loguser[0]['email_verified'] !== '1') {
						$arg['status']    = 0;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['message']   = $this->lang->line('email_not_varify');
						$arg['data']      = array();
						echo json_encode($arg);
						exit;
					}

					$where1 = array('user_id' => $usid, 'status' => 'Active');
					$business_data = $this->dynamic_model->getdatafromtable('business', $where1);
					if (!empty($business_data)) {
						$business_id   = $business_data[0]['id'];
					} else {
						$business_id = 0;
					}

					$getCustomerId = strhlp_create_customer(
						array(
							'token'	=> $token,
							'name'	=> $name . ' ' . $lastname,
							'email'	=> $loguser[0]['email'],
							'user_id' => $usid
						)
					);

					if (!$getCustomerId) {
						$arg['status'] = 0;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['message']   = "Invalid token";
						$arg['data']      = array();
					} else {
						$card_res = $card_data = $card_Exist = array();
						$card_data = $this->dynamic_model->getdatafromtable('saved_card_details', array('user_id' => $usid), 'id,card_details');
						if (!empty($card_data)) {
							foreach ($card_data as $value) {
								$card_arr = json_decode(decode($value['card_details']));
								$card_id = $value['id'];
								$card_bank_no = $card_arr->card_bank_no;
								if ($card_number == $card_bank_no) {
									$card_Exist[] = array("id" => $card_id, "card_bank_no" => $card_bank_no);
								}
							}
						}

						if ($savecard == 1) {
							$cardStatus = strhlp_add_card(
								array(
									'token'			=>	$token,
									'customer_id'	=>	$getCustomerId
								)
							);

							if ($cardStatus == 'You cannot use a Stripe token more than once: ' . $token . '.') {

								$cardToken = strhlp_get_token(
									array(
										'card_number'	=>	$card_number,
										'expiry_month'		=>	$expiry_month,
										'cvv_no'			=>	$cvv_no,
										'expiry_year'		=>	$expiry_year
									)
								);

								$cardStatus = strhlp_add_card(
									array(
										'token'			=>	$cardToken,
										'customer_id'	=>	$getCustomerId
									)
								);
							}
						} else {
							$cardStatus = '';
						}

						$cardToken = strhlp_get_token(
							array(
								'card_number'	=>	$card_number,
								'expiry_month'		=>	$expiry_month,
								'cvv_no'			=>	$cvv_no,
								'expiry_year'		=>	$expiry_year
							)
						);

						strhlp_checkout(
							array(
								'amount'	=>	$amount,
								'name'		=>	$name . ' ' . $lastname,
								'email'		=>	$loguser[0]['email'],
								'description' => 'Studio Registration',
								'token'	=>	$cardToken
							),
							array(
								'user_id'	=>	$usid
							),
							2
						);

						if (empty($card_Exist)) {

							// Use for faster checkout
							if (@$save_card_check == 1)
								$del_status = 0;
							else
								$del_status = 1;
							// Firstly insert data into saved_card_details table
							$card_detail = array(

								'acc_holder_name'  => $name . ' ' . $lastname,
								'card_bank_no'     => $card_number,
								'expiry_month'     => $expiry_month,
								'expiry_year'      => $expiry_year
							);
							$json_card_data = encode(json_encode($card_detail));
							$token = 'token';
							$saved_card_details = array(
								'user_id'          => $usid,
								'is_debit_card'    => 0,
								'is_credit_card'    => 1,
								'is_deleted'       => $del_status,
								'created_by'       => $usid,
								'updated_by'       => $usid,
								'create_dt'        => $time,
								'update_dt'        => $time,
								'card_details'     => $json_card_data,
								'card_token'       => $token
							);
							$pay_id = $this->dynamic_model->insertdata('saved_card_details', $saved_card_details);
						} else {
							$pay_id = @$card_Exist[0]['id'];
						}

						$ref_num  = getuniquenumber();
						$payment_id   = $this->input->post('payment_id');

						//logic implement for purachase plan mothly haif yearly and yearly
						$wh  = array("id" => $pre_plan_id);
						$subscription_plan = $this->dynamic_model->getdatafromtable("subscribe_plan", $wh);
						if (!empty($subscription_plan)) {
							$pre_subplan_type = $subscription_plan[0]['type'];
							$previous_amount = $subscription_plan[0]['amount'];

							if ($pre_subplan_type == 1) {
								$pre_one_day_amount = ($previous_amount / 30);
							} else if ($pre_subplan_type == 2) {
								$pre_one_day_amount = ($previous_amount / 180);
							} else {
								$pre_one_day_amount = ($previous_amount / 356);
							}
						} else {
							$pre_one_day_amount = 0;
						}

						$wh1  = array("id" => $plan_id);
						$subscription_plan1 = $this->dynamic_model->getdatafromtable("subscribe_plan", $wh1);
						$subplan_type = $subscription_plan1[0]['type'];
						$newplan_amount = $subscription_plan1[0]['amount'];

						$curr_date = $time;
						$plan_data = $this->studio_model->plan_check($pre_plan_id, $usid);
						if (!empty($plan_data)) {
							$sub_end = $plan_data['sub_end'];
							if ($curr_date > $sub_end) {
								$plan_status = "Active";
								$start_date  = strtotime(date("Y-m-d 00:00:00") . " +14 days");
								$sdate = date('d M Y', $start_date);
								if ($subplan_type == 1) {
									$end_date   = strtotime(date("Y-m-d 23:59:59") . " +43 days"); //30 + Free 14 days trial
									$edate      = date('d M Y', $end_date);
								} elseif ($subplan_type == 2) {
									$end_date   = strtotime(date("Y-m-d 23:59:59") . " +180 days"); //6 month + Free 14 days trial
									$edate      = date('d M Y', $end_date);
								} else {
									$end_date    = strtotime(date("Y-m-d 23:59:59") . " +365 days"); // 1 year +  Free 14 days trial
									$edate       = date('d M Y', $end_date);
								}
								/*$where1 = array(
													'sub_user_id' => $usid,
													'sub_plan_id' => $pre_plan_id
											);
											$subdata = array(
												'plan_status' => "Expire"
											);
											$this->dynamic_model->updateRowWhere('subscription',$where1,$subdata);	*/
								$msg = $this->lang->line('subscription_succ');
							} else {

								$days = 0;
								$now = $time; //time(); // or your date as well
								//$start_your_date = $plan_data['sub_start'];
								if ($now > $sub_end) {
									$datediff = $now - $sub_end;
									$days = @round($datediff / (60 * 60 * 24));
									//used_days

									$plan_status = "Active";
									//$sub_end    = date('Y-m-d 23:59:59',$plan_data['sub_end']);
									//$startdate  = date('Y-m-d 00:00:00',strtotime($sub_end."+1 days"));

									$startdate  = date('Y-m-d 00:00:00');
									$start_date = strtotime($startdate . "+1 days");
									$sdate = date('d M Y', $start_date);
									if ($subplan_type == 1) {
										$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($startdate . "+" . (30 - $days) . " days")));
										$edate      = date('d M Y', $end_date);
									} elseif ($subplan_type == 2) {
										$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($startdate . "+" . (180 - $days) . " days")));
										$edate       = date('d M Y', $end_date);
									} else {
										$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($startdate . "+" . (365 - $days) . " days")));
										$edate      = date('d M Y', $end_date);
									}
								} else {
									$datediff = $sub_end - $now;
									$days = @round($datediff / (60 * 60 * 24));
									//remaining_days

									$plan_status = "Active";
									//$sub_end    = date('Y-m-d 23:59:59',$plan_data['sub_end']);
									//$startdate  = date('Y-m-d 00:00:00',strtotime($sub_end."+1 days"));

									$startdate  = date('Y-m-d 00:00:00');
									$start_date = strtotime($startdate . "+1 days");
									$sdate2 = date('Y-m-d 00:00:00', $start_date);
									$sdate = date('d M Y', $start_date);
									$start_date = strtotime($sdate2);
									if ($subplan_type == 1) {

										//$pre_one_day_amount = ($previous_amount/30);
										$remaining_days_amount = $days * $pre_one_day_amount;
										$new_amount = $amount - $remaining_days_amount;

										$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($sdate2 . "+30 days")));
										$edate      = date('d M Y', $end_date);
									} elseif ($subplan_type == 2) {

										//$pre_one_day_amount = ($previous_amount/180);
										$remaining_days_amount = $days * $pre_one_day_amount;
										$new_amount = $amount - $remaining_days_amount;

										$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($sdate2 . "+180 days")));
										$edate       = date('d M Y', $end_date);
									} else {

										//$pre_one_day_amount = ($previous_amount/365);
										$remaining_days_amount = $days * $pre_one_day_amount;
										$new_amount = $amount - $remaining_days_amount;

										$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($sdate2 . "+365 days")));
										$edate      = date('d M Y', $end_date);
									}
								}


								$msg = $this->lang->line('subscription_upcoming');
							}
						} else {
							$plan_status = "Active";
							$start_date  = strtotime(date("Y-m-d 00:00:00") . " +14 days");
							$sdate = date('d M Y', $start_date);
							if ($subplan_type == 1) {
								$end_date   = strtotime(date("Y-m-d 23:59:59") . " +43 days"); //30 + Free 14 days trial
								$edate      = date('d M Y', $end_date);
							} elseif ($subplan_type == 2) {
								$end_date   = strtotime(date("Y-m-d 23:59:59") . " +180 days"); //6 month + Free 14 days trial
								$edate      = date('d M Y', $end_date);
							} else {
								$end_date    = strtotime(date("Y-m-d 23:59:59") . " +365 days"); // 1 year +  Free 14 days trial
								$edate       = date('d M Y', $end_date);
							}
							$msg = $this->lang->line('subscription_succ');
						}

						$where1 = array(
							'sub_user_id' => $usid,
							'sub_plan_id' => $pre_plan_id
						);
						$subdata = array(
							'plan_status' => "Expire"
						);
						$this->dynamic_model->updateRowWhere('subscription', $where1, $subdata);

						//End of logic implement for purachase plan mothly haif yearly and yearly
						//Insert data in transaction table
						$transaction_data = array(
							'user_id'                => $usid,
							'amount'                 => $amount,
							'new_amount'				=> $new_amount,
							'trx_id'       		    => (!empty($payment_id)) ? $payment_id : $ref_num,
							'transaction_type'      => 1,
							'payment_status'        => "Success",
							'saved_card_id'         => $pay_id,
							'create_dt'        		=> $time,
							'update_dt'        		=> $time
						);

						$trx_id = $this->dynamic_model->insertdata('transactions', $transaction_data);
						$subscription_data = array(
							'sub_user_id'           => $usid,
							'sub_plan_id'     		=> $plan_id,
							'sub_start'       		=> $start_date,
							'sub_end'       		=> $end_date,
							'max_users_count'		=> $subscription_plan1[0]['max_users'],
							'create_dt'        		=> $time,
							'plan_status'        	=> $plan_status,
							'transaction_id'        => $trx_id,
							'update_dt'        		=> $time
						);
						$sub_id = $this->dynamic_model->insertdata('subscription', $subscription_data);

						$card_id       = '';
						$customer_name = '';
						$number        = '';
						$expiry_month  = '';
						$expiry_year   = '';
						$cvd           = '';
						$country_code = 0;
						//$res_data = clover_api_card_profile_check($usid, $savecard, $customer_name, $number, $expiry_month, $expiry_year, $cvd, $country_code, $business_id, $token);
						// $customer_code = $res_data['customer_code'];

						// Activate plan add in users table
						$where2 = array(
							'id' => $usid,
						);
						$plandata = array(
							'name' => $name,
							'lastname' => $lastname,
							'plan_id' => $plan_id
						);

						$this->dynamic_model->updateRowWhere('user', $where2, $plandata);
						//Get active plan data
						$whe  = array("id" => $plan_id);
						$activeplan_data = $this->dynamic_model->getdatafromtable("subscribe_plan", $whe);
						$plantype = (!empty($activeplan_data[0]['type'])) ? $activeplan_data[0]['type'] : '';
						if ($subplan_type == 1) {
							$plantype = 'Monthly';
						} elseif ($subplan_type == 2) {
							$plantype = 'Half Yearly';
						} else {
							$plantype = 'Yearly';
						}
						//End of active plan data
						$response  = array('plan_name' => $activeplan_data[0]['plan_name'], 'plan_status' => (string)$plan_status, 'amount' => number_format((float)$activeplan_data[0]['amount'], 2, '.', ''), 'transaction_date' => date('d M Y'), 'validity_from' => $sdate, 'validity_to' => $edate, 'plan_type' => $plantype);
						if ($sub_id) {
							$arg['status']    = 1;
							$arg['error_code'] = HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['message']   = $msg;
							$arg['data']      = $response;
						} else {
							$arg['status']    = 0;
							$arg['error_code'] = HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['message']   = $this->lang->line('subscription_failed');
							$arg['data']      = array();
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function subcription_plan_purchase_25042021()
	{
		$arg    = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {

			$_POST = json_decode(file_get_contents("php://input"), true);
			if ($_POST) {
				$this->form_validation->set_rules('user_id', 'User Id', 'required|trim', array('required' => $this->lang->line('user_id_required')));
				$this->form_validation->set_rules('name', 'Name', 'required|trim', array('required' => $this->lang->line('first_name')));
				//$this->form_validation->set_rules('lastname','Last Name', 'required|trim', array( 'required' => $this->lang->line('last_name')));
				$this->form_validation->set_rules('plan_id', 'Plan Id', 'trim|required', array(
					'required'   => $this->lang->line('plan_id_required')
				));
				$this->form_validation->set_rules('amount', 'Amount', 'required|greater_than[0]', array(
					'required'   => $this->lang->line('amount_required'),
					'numeric'    => $this->lang->line('amount_valid')
				));

				if ($this->input->post('save_card_check') == 1) {
					$this->form_validation->set_rules('card_number', 'Card Number', 'required|numeric|min_length[16]|max_length[16]', array(
						'required'   => $this->lang->line('card_required'),
						'min_length' => $this->lang->line('card_min_length'),
						'max_length' => $this->lang->line('card_max_length'),
						'numeric'    => $this->lang->line('card_numeric')
					));
					$this->form_validation->set_rules('cvv_no', 'Cvv no', 'required|numeric|min_length[3]|max_length[3]', array(
						'required'   => $this->lang->line('cvv_no_required'),
						'min_length' => $this->lang->line('cvv_no_min_length'),
						'max_length' => $this->lang->line('cvv_no_max_length'),
						'numeric'    => $this->lang->line('cvv_no_numeric')
					));
					$this->form_validation->set_rules('expiry_month', 'Expiry Month', 'required|numeric|less_than_equal_to[12]|greater_than[0]|min_length[2]', array(
						'required' => $this->lang->line('expiry_month_required'),
						'min_length' => $this->lang->line('expiry_month_min_length'),
						'less_than_equal_to' => $this->lang->line('expiry_month_less_than_equal_to'),
						'greater_than' => $this->lang->line('expiry_month_greater_than'),
						'numeric' => $this->lang->line('expiry_month_numeric')
					));
					$this->form_validation->set_rules('expiry_year', 'Expiry Year', 'required|numeric|min_length[4]|max_length[4]', array(
						'required'   => $this->lang->line('expiry_year_required'),
						'min_length' => $this->lang->line('expiry_year_min_length'),
						'max_length' => $this->lang->line('expiry_year_max_length'),
						'numeric'    => $this->lang->line('expiry_year_numeric')
					));
				}
				if ($this->form_validation->run() == FALSE) {
					$arg['status']  = 0;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {
					$usid  = decode($this->input->post('user_id'));

					$where1 = array('user_id' => $usid, 'status' => 'Active');
					$business_data = $this->dynamic_model->getdatafromtable('business', $where1);
					if (!empty($business_data)) {
						$business_id   = $business_data[0]['id'];
					} else {
						$business_id = 0;
					}

					if ($this->input->post('expiry_month')) {
						$resp = $this->fetch_clover_payment_token($business_id, $this->input->post('country_code'), $this->input->post('card_number'), $this->input->post('expiry_month'), $this->input->post('expiry_year'), $this->input->post('cvv_no'));

						$resp = json_decode($resp);
						if ($resp->status == 0) {
							$arg['status'] = 0;
							$arg['error_code'] = ERROR_FAILED_CODE;
							$arg['error_line'] = __line__;
							$arg['message'] = 'Invalid Details';
							$arg['data'] = json_decode('{}');
							echo json_encode($arg);
							exit;
						}
					}



					$where1  = array("id" => $usid);
					$loguser = $this->dynamic_model->getdatafromtable("user", $where1);
					if ($loguser[0]['email_verified'] !== '1') {
						$arg['status']    = 0;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['message']   = $this->lang->line('email_not_varify');
						$arg['data']      = array();
						echo json_encode($arg);
						exit;
					}
					$time = strtotime(date("Y-m-d H:i:s")); //time();
					$name            = $this->input->post('name');
					$lastname        = $this->input->post('lastname');
					$pre_plan_id      = decode($this->input->post('pre_plan_id'));
					$plan_id          = decode($this->input->post('plan_id'));
					$amount           = $this->input->post('amount');
					$cvv_no           = $this->input->post('cvv_no');
					$card_number      = $this->input->post('card_number');
					$card_type        = $this->input->post('card_type');
					$expiry_month     = $this->input->post('expiry_month');
					$expiry_year      = $this->input->post('expiry_year');
					$save_card_check  = $this->input->post('save_card_check');
					//$token            = $this->input->post('token');
					if ($this->input->post('token')) {
						$token = $this->input->post('token');
					} else {
						$dat = $resp->data;
						$token = $dat->token;
					}

					$country_code     = $this->input->post('country_code');
					$new_amount = $amount;
					/*if($this->input->post('save_card_check')=="" || $this->input->post('save_card_check')==0)
			            {
			            	$savecard = 0;
			            }
			            else
			            {
			            	$savecard = 1;
			            }*/

					$savecard = $save_card_check;

					$card_res = $card_data = $card_Exist = array();
					$card_data = $this->dynamic_model->getdatafromtable('saved_card_details', array('user_id' => $usid), 'id,card_details');
					if (!empty($card_data)) {
						foreach ($card_data as $value) {
							$card_arr = json_decode(decode($value['card_details']));
							$card_id = $value['id'];
							$card_bank_no = $card_arr->card_bank_no;
							if ($card_number == $card_bank_no) {
								$card_Exist[] = array("id" => $card_id, "card_bank_no" => $card_bank_no);
							}
						}
					}

					if ($savecard == 1) {
						$FirstFourNumber = substr($card_number, 0, 4); // get first 4
						$LastFourNumber  = substr($card_number, 12, 4); // get last 4
						$newCardNumber   = $FirstFourNumber . ' XXXX XXXX ' . $LastFourNumber;


						// check year is valid
						if (check_expiry_year($expiry_year) == false) {
							$arg['status']  = 0;
							$arg['error_code'] =  ERROR_FAILED_CODE;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('invalid_expiry_year');
							echo json_encode($arg);
							exit;
						}
						// check year is valid
						if (check_expiry_month_year($expiry_month, $expiry_year) == false) {
							$arg['status']  = 0;
							$arg['error_code'] =  ERROR_FAILED_CODE;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('invalid_expiry_year_month');
							echo json_encode($arg);
							exit;
						}
					}
					if (empty($card_Exist)) {
						// Use for faster checkout
						if (@$save_card_check == 1)
							$del_status = 0;
						else
							$del_status = 1;
						// Firstly insert data into saved_card_details table
						$card_detail = array(

							'acc_holder_name'  => $name . ' ' . $lastname,
							'card_bank_no'     => $card_number,
							'expiry_month'     => $expiry_month,
							'expiry_year'      => $expiry_year
						);
						$json_card_data = encode(json_encode($card_detail));
						$saved_card_details = array(
							'user_id'          => $usid,
							'is_debit_card'    => 0,
							'is_credit_card'    => 1,
							'is_deleted'       => $del_status,
							'created_by'       => $usid,
							'updated_by'       => $usid,
							'create_dt'        => $time,
							'update_dt'        => $time,
							'card_details'     => $json_card_data,
							'card_token'       => $token
						);
						$pay_id = $this->dynamic_model->insertdata('saved_card_details', $saved_card_details);
					} else {
						$pay_id = @$card_Exist[0]['id'];
					}



					$ref_num  = getuniquenumber();
					$payment_id   = $this->input->post('payment_id');
					//logic implement for purachase plan mothly haif yearly and yearly
					$wh  = array("id" => $pre_plan_id);
					$subscription_plan = $this->dynamic_model->getdatafromtable("subscribe_plan", $wh);
					if (!empty($subscription_plan)) {
						$pre_subplan_type = $subscription_plan[0]['type'];
						$previous_amount = $subscription_plan[0]['amount'];

						if ($pre_subplan_type == 1) {
							$pre_one_day_amount = ($previous_amount / 30);
						} else if ($pre_subplan_type == 2) {
							$pre_one_day_amount = ($previous_amount / 180);
						} else {
							$pre_one_day_amount = ($previous_amount / 356);
						}
					} else {
						$pre_one_day_amount = 0;
					}


					$wh1  = array("id" => $plan_id);
					$subscription_plan1 = $this->dynamic_model->getdatafromtable("subscribe_plan", $wh1);
					$subplan_type = $subscription_plan1[0]['type'];
					$newplan_amount = $subscription_plan1[0]['amount'];


					$curr_date = $time;
					$plan_data = $this->studio_model->plan_check($pre_plan_id, $usid);
					if (!empty($plan_data)) {
						$sub_end = $plan_data['sub_end'];
						if ($curr_date > $sub_end) {
							$plan_status = "Active";
							$start_date  = strtotime(date("Y-m-d 00:00:00") . " +14 days");
							$sdate = date('d M Y', $start_date);
							if ($subplan_type == 1) {
								$end_date   = strtotime(date("Y-m-d 23:59:59") . " +43 days"); //30 + Free 14 days trial
								$edate      = date('d M Y', $end_date);
							} elseif ($subplan_type == 2) {
								$end_date   = strtotime(date("Y-m-d 23:59:59") . " +180 days"); //6 month + Free 14 days trial
								$edate      = date('d M Y', $end_date);
							} else {
								$end_date    = strtotime(date("Y-m-d 23:59:59") . " +365 days"); // 1 year +  Free 14 days trial
								$edate       = date('d M Y', $end_date);
							}
							/*$where1 = array(
											'sub_user_id' => $usid,
											'sub_plan_id' => $pre_plan_id
									);
                                  	$subdata = array(
										'plan_status' => "Expire"
									);
									$this->dynamic_model->updateRowWhere('subscription',$where1,$subdata);	*/
							$msg = $this->lang->line('subscription_succ');
						} else {

							$days = 0;
							$now = $time; //time(); // or your date as well
							//$start_your_date = $plan_data['sub_start'];
							if ($now > $sub_end) {
								$datediff = $now - $sub_end;
								$days = @round($datediff / (60 * 60 * 24));
								//used_days

								$plan_status = "Active";
								//$sub_end    = date('Y-m-d 23:59:59',$plan_data['sub_end']);
								//$startdate  = date('Y-m-d 00:00:00',strtotime($sub_end."+1 days"));

								$startdate  = date('Y-m-d 00:00:00');
								$start_date = strtotime($startdate . "+1 days");
								$sdate = date('d M Y', $start_date);
								if ($subplan_type == 1) {
									$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($startdate . "+" . (30 - $days) . " days")));
									$edate      = date('d M Y', $end_date);
								} elseif ($subplan_type == 2) {
									$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($startdate . "+" . (180 - $days) . " days")));
									$edate       = date('d M Y', $end_date);
								} else {
									$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($startdate . "+" . (365 - $days) . " days")));
									$edate      = date('d M Y', $end_date);
								}
							} else {
								$datediff = $sub_end - $now;
								$days = @round($datediff / (60 * 60 * 24));
								//remaining_days

								$plan_status = "Active";
								//$sub_end    = date('Y-m-d 23:59:59',$plan_data['sub_end']);
								//$startdate  = date('Y-m-d 00:00:00',strtotime($sub_end."+1 days"));

								$startdate  = date('Y-m-d 00:00:00');
								$start_date = strtotime($startdate . "+1 days");
								$sdate2 = date('Y-m-d 00:00:00', $start_date);
								$sdate = date('d M Y', $start_date);
								$start_date = strtotime($sdate2);
								if ($subplan_type == 1) {

									//$pre_one_day_amount = ($previous_amount/30);
									$remaining_days_amount = $days * $pre_one_day_amount;
									$new_amount = $amount - $remaining_days_amount;

									$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($sdate2 . "+30 days")));
									$edate      = date('d M Y', $end_date);
								} elseif ($subplan_type == 2) {

									//$pre_one_day_amount = ($previous_amount/180);
									$remaining_days_amount = $days * $pre_one_day_amount;
									$new_amount = $amount - $remaining_days_amount;

									$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($sdate2 . "+180 days")));
									$edate       = date('d M Y', $end_date);
								} else {

									//$pre_one_day_amount = ($previous_amount/365);
									$remaining_days_amount = $days * $pre_one_day_amount;
									$new_amount = $amount - $remaining_days_amount;

									$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($sdate2 . "+365 days")));
									$edate      = date('d M Y', $end_date);
								}
							}


							$msg = $this->lang->line('subscription_upcoming');
						}
					} else {
						$plan_status = "Active";
						$start_date  = strtotime(date("Y-m-d 00:00:00") . " +14 days");
						$sdate = date('d M Y', $start_date);
						if ($subplan_type == 1) {
							$end_date   = strtotime(date("Y-m-d 23:59:59") . " +43 days"); //30 + Free 14 days trial
							$edate      = date('d M Y', $end_date);
						} elseif ($subplan_type == 2) {
							$end_date   = strtotime(date("Y-m-d 23:59:59") . " +180 days"); //6 month + Free 14 days trial
							$edate      = date('d M Y', $end_date);
						} else {
							$end_date    = strtotime(date("Y-m-d 23:59:59") . " +365 days"); // 1 year +  Free 14 days trial
							$edate       = date('d M Y', $end_date);
						}
						$msg = $this->lang->line('subscription_succ');
					}

					$where1 = array(
						'sub_user_id' => $usid,
						'sub_plan_id' => $pre_plan_id
					);
					$subdata = array(
						'plan_status' => "Expire"
					);
					$this->dynamic_model->updateRowWhere('subscription', $where1, $subdata);

					//End of logic implement for purachase plan mothly haif yearly and yearly
					//Insert data in transaction table
					$transaction_data = array(
						'user_id'                => $usid,
						'amount'                 => $amount,
						'new_amount'				=> $new_amount,
						'trx_id'       		    => (!empty($payment_id)) ? $payment_id : $ref_num,
						'transaction_type'      => 1,
						'payment_status'        => "Success",
						'saved_card_id'         => $pay_id,
						'create_dt'        		=> $time,
						'update_dt'        		=> $time
					);
					$trx_id = $this->dynamic_model->insertdata('transactions', $transaction_data);
					$subscription_data = array(
						'sub_user_id'           => $usid,
						'sub_plan_id'     		=> $plan_id,
						'sub_start'       		=> $start_date,
						'sub_end'       		=> $end_date,
						'max_users_count'		=> $subscription_plan1[0]['max_users'],
						'create_dt'        		=> $time,
						'plan_status'        	=> $plan_status,
						'transaction_id'        => $trx_id,
						'update_dt'        		=> $time
					);
					$sub_id = $this->dynamic_model->insertdata('subscription', $subscription_data);

					////////////////////////////

					$card_id       = '';
					$customer_name = '';
					$number        = '';
					$expiry_month  = '';
					$expiry_year   = '';
					$cvd           = '';


					$res_data = clover_api_card_profile_check($usid, $savecard, $customer_name, $number, $expiry_month, $expiry_year, $cvd, $country_code, $business_id, $token);
					$customer_code = $res_data['customer_code'];



					// Activate plan add in users table
					$where2 = array(
						'id' => $usid,
					);
					$plandata = array(
						'name' => $name,
						'lastname' => $lastname,
						'plan_id' => $plan_id
					);
					$this->dynamic_model->updateRowWhere('user', $where2, $plandata);
					//Get active plan data
					$whe  = array("id" => $plan_id);
					$activeplan_data = $this->dynamic_model->getdatafromtable("subscribe_plan", $whe);
					$plantype = (!empty($activeplan_data[0]['type'])) ? $activeplan_data[0]['type'] : '';
					if ($subplan_type == 1) {
						$plantype = 'Monthly';
					} elseif ($subplan_type == 2) {
						$plantype = 'Half Yearly';
					} else {
						$plantype = 'Yearly';
					}
					//End of active plan data
					$response  = array('plan_name' => $activeplan_data[0]['plan_name'], 'plan_status' => (string)$plan_status, 'amount' => number_format((float)$activeplan_data[0]['amount'], 2, '.', ''), 'transaction_date' => date('d M Y'), 'validity_from' => $sdate, 'validity_to' => $edate, 'plan_type' => $plantype);
					if ($sub_id) {
						$arg['status']    = 1;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['message']   = $msg;
						$arg['data']      = $response;
					} else {
						$arg['status']    = 0;
						$arg['error_code'] = HTTP_NOT_FOUND;
						$arg['error_line'] = __line__;
						$arg['message']   = $this->lang->line('subscription_failed');
						$arg['data']      = array();
					}
				}
			}
		}
		echo json_encode($arg);
	}
	public function subcription_plan_purchase_old()
	{
		$arg    = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {

			$_POST = json_decode(file_get_contents("php://input"), true);
			if ($_POST) {
				$this->form_validation->set_rules('user_id', 'User Id', 'required|trim', array('required' => $this->lang->line('user_id_required')));
				$this->form_validation->set_rules('name', 'Name', 'required|trim', array('required' => $this->lang->line('first_name')));
				//$this->form_validation->set_rules('lastname','Last Name', 'required|trim', array( 'required' => $this->lang->line('last_name')));
				$this->form_validation->set_rules('plan_id', 'Plan Id', 'trim|required', array(
					'required'   => $this->lang->line('plan_id_required')
				));
				$this->form_validation->set_rules('amount', 'Amount', 'required|greater_than[0]', array(
					'required'   => $this->lang->line('amount_required'),
					'numeric'    => $this->lang->line('amount_valid')
				));

				if ($this->input->post('save_card_check') == 1) {
					$this->form_validation->set_rules('card_number', 'Card Number', 'required|numeric|min_length[16]|max_length[16]', array(
						'required'   => $this->lang->line('card_required'),
						'min_length' => $this->lang->line('card_min_length'),
						'max_length' => $this->lang->line('card_max_length'),
						'numeric'    => $this->lang->line('card_numeric')
					));
					$this->form_validation->set_rules('cvv_no', 'Cvv no', 'required|numeric|min_length[3]|max_length[3]', array(
						'required'   => $this->lang->line('cvv_no_required'),
						'min_length' => $this->lang->line('cvv_no_min_length'),
						'max_length' => $this->lang->line('cvv_no_max_length'),
						'numeric'    => $this->lang->line('cvv_no_numeric')
					));
					$this->form_validation->set_rules('expiry_month', 'Expiry Month', 'required|numeric|less_than_equal_to[12]|greater_than[0]|min_length[2]', array(
						'required' => $this->lang->line('expiry_month_required'),
						'min_length' => $this->lang->line('expiry_month_min_length'),
						'less_than_equal_to' => $this->lang->line('expiry_month_less_than_equal_to'),
						'greater_than' => $this->lang->line('expiry_month_greater_than'),
						'numeric' => $this->lang->line('expiry_month_numeric')
					));
					$this->form_validation->set_rules('expiry_year', 'Expiry Year', 'required|numeric|min_length[4]|max_length[4]', array(
						'required'   => $this->lang->line('expiry_year_required'),
						'min_length' => $this->lang->line('expiry_year_min_length'),
						'max_length' => $this->lang->line('expiry_year_max_length'),
						'numeric'    => $this->lang->line('expiry_year_numeric')
					));
				}
				if ($this->form_validation->run() == FALSE) {
					$arg['status']  = 0;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {
					$usid  = decode($this->input->post('user_id'));

					$where1 = array('user_id' => $usid, 'status' => 'Active');
					$business_data = $this->dynamic_model->getdatafromtable('business', $where1);
					if (!empty($business_data)) {
						$business_id   = $business_data[0]['id'];
					} else {
						$business_id = 0;
					}

					// if ($this->input->post('expiry_month')) {
					// 	$resp = $this->fetch_clover_payment_token($business_id, $this->input->post('country_code'), $this->input->post('card_number'), $this->input->post('expiry_month'), $this->input->post('expiry_year'), $this->input->post('cvv_no'));
					//
					// 	$resp = json_decode($resp);
					// 	if ($resp->status == 0) {
					// 		$arg['status'] = 0;
					// 		$arg['error_code'] = ERROR_FAILED_CODE;
					// 		$arg['error_line'] = __line__;
					// 		$arg['message'] = 'Invalid Details';
					// 		$arg['data'] = json_decode('{}');
					// 		echo json_encode($arg); exit;
					// 	}
					// }



					$where1  = array("id" => $usid);
					$loguser = $this->dynamic_model->getdatafromtable("user", $where1);
					if ($loguser[0]['email_verified'] !== '1') {
						$arg['status']    = 0;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['message']   = $this->lang->line('email_not_varify');
						$arg['data']      = array();
						echo json_encode($arg);
						exit;
					}
					$time = strtotime(date("Y-m-d H:i:s")); //time();
					$name            = $this->input->post('name');
					$lastname        = $this->input->post('lastname');
					$pre_plan_id      = decode($this->input->post('pre_plan_id'));
					$plan_id          = decode($this->input->post('plan_id'));
					$amount           = $this->input->post('amount');
					$cvv_no           = $this->input->post('cvv_no');
					$card_number      = $this->input->post('card_number');
					$card_type        = $this->input->post('card_type');
					$expiry_month     = $this->input->post('expiry_month');
					$expiry_year      = $this->input->post('expiry_year');
					$save_card_check  = $this->input->post('save_card_check');
					//$token            = $this->input->post('token');
					if ($this->input->post('token')) {
						$token = $this->input->post('token');
					} else {
						$dat = $resp->data;
						$token = $dat->token;
					}

					$country_code     = $this->input->post('country_code');
					$new_amount = $amount;
					/*if($this->input->post('save_card_check')=="" || $this->input->post('save_card_check')==0)
			            {
			            	$savecard = 0;
			            }
			            else
			            {
			            	$savecard = 1;
			            }*/

					$savecard = $save_card_check;

					$card_res = $card_data = $card_Exist = array();
					$card_data = $this->dynamic_model->getdatafromtable('saved_card_details', array('user_id' => $usid), 'id,card_details');
					if (!empty($card_data)) {
						foreach ($card_data as $value) {
							$card_arr = json_decode(decode($value['card_details']));
							$card_id = $value['id'];
							$card_bank_no = $card_arr->card_bank_no;
							if ($card_number == $card_bank_no) {
								$card_Exist[] = array("id" => $card_id, "card_bank_no" => $card_bank_no);
							}
						}
					}

					if ($savecard == 1) {
						$FirstFourNumber = substr($card_number, 0, 4); // get first 4
						$LastFourNumber  = substr($card_number, 12, 4); // get last 4
						$newCardNumber   = $FirstFourNumber . ' XXXX XXXX ' . $LastFourNumber;


						// check year is valid
						if (check_expiry_year($expiry_year) == false) {
							$arg['status']  = 0;
							$arg['error_code'] =  ERROR_FAILED_CODE;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('invalid_expiry_year');
							echo json_encode($arg);
							exit;
						}
						// check year is valid
						if (check_expiry_month_year($expiry_month, $expiry_year) == false) {
							$arg['status']  = 0;
							$arg['error_code'] =  ERROR_FAILED_CODE;
							$arg['error_line'] = __line__;
							$arg['message'] = $this->lang->line('invalid_expiry_year_month');
							echo json_encode($arg);
							exit;
						}
					}
					if (empty($card_Exist)) {
						// Use for faster checkout
						if (@$save_card_check == 1)
							$del_status = 0;
						else
							$del_status = 1;
						// Firstly insert data into saved_card_details table
						$card_detail = array(

							'acc_holder_name'  => $name . ' ' . $lastname,
							'card_bank_no'     => $card_number,
							'expiry_month'     => $expiry_month,
							'expiry_year'      => $expiry_year
						);
						$json_card_data = encode(json_encode($card_detail));
						$saved_card_details = array(
							'user_id'          => $usid,
							'is_debit_card'    => 0,
							'is_credit_card'    => 1,
							'is_deleted'       => $del_status,
							'created_by'       => $usid,
							'updated_by'       => $usid,
							'create_dt'        => $time,
							'update_dt'        => $time,
							'card_details'     => $json_card_data,
							'card_token'       => $token
						);
						$pay_id = $this->dynamic_model->insertdata('saved_card_details', $saved_card_details);
					} else {
						$pay_id = @$card_Exist[0]['id'];
					}



					$ref_num  = getuniquenumber();
					$payment_id   = $this->input->post('payment_id');
					//logic implement for purachase plan mothly haif yearly and yearly
					$wh  = array("id" => $pre_plan_id);
					$subscription_plan = $this->dynamic_model->getdatafromtable("subscribe_plan", $wh);
					if (!empty($subscription_plan)) {
						$pre_subplan_type = $subscription_plan[0]['type'];
						$previous_amount = $subscription_plan[0]['amount'];

						if ($pre_subplan_type == 1) {
							$pre_one_day_amount = ($previous_amount / 30);
						} else if ($pre_subplan_type == 2) {
							$pre_one_day_amount = ($previous_amount / 180);
						} else {
							$pre_one_day_amount = ($previous_amount / 356);
						}
					} else {
						$pre_one_day_amount = 0;
					}


					$wh1  = array("id" => $plan_id);
					$subscription_plan1 = $this->dynamic_model->getdatafromtable("subscribe_plan", $wh1);
					$subplan_type = $subscription_plan1[0]['type'];
					$newplan_amount = $subscription_plan1[0]['amount'];


					$curr_date = $time;
					$plan_data = $this->studio_model->plan_check($pre_plan_id, $usid);
					if (!empty($plan_data)) {
						$sub_end = $plan_data['sub_end'];
						if ($curr_date > $sub_end) {
							$plan_status = "Active";
							$start_date  = strtotime(date("Y-m-d 00:00:00") . " +14 days");
							$sdate = date('d M Y', $start_date);
							if ($subplan_type == 1) {
								$end_date   = strtotime(date("Y-m-d 23:59:59") . " +43 days"); //30 + Free 14 days trial
								$edate      = date('d M Y', $end_date);
							} elseif ($subplan_type == 2) {
								$end_date   = strtotime(date("Y-m-d 23:59:59") . " +180 days"); //6 month + Free 14 days trial
								$edate      = date('d M Y', $end_date);
							} else {
								$end_date    = strtotime(date("Y-m-d 23:59:59") . " +365 days"); // 1 year +  Free 14 days trial
								$edate       = date('d M Y', $end_date);
							}
							/*$where1 = array(
											'sub_user_id' => $usid,
											'sub_plan_id' => $pre_plan_id
									);
                                  	$subdata = array(
										'plan_status' => "Expire"
									);
									$this->dynamic_model->updateRowWhere('subscription',$where1,$subdata);	*/
							$msg = $this->lang->line('subscription_succ');
						} else {

							$days = 0;
							$now = $time; //time(); // or your date as well
							//$start_your_date = $plan_data['sub_start'];
							if ($now > $sub_end) {
								$datediff = $now - $sub_end;
								$days = @round($datediff / (60 * 60 * 24));
								//used_days

								$plan_status = "Active";
								//$sub_end    = date('Y-m-d 23:59:59',$plan_data['sub_end']);
								//$startdate  = date('Y-m-d 00:00:00',strtotime($sub_end."+1 days"));

								$startdate  = date('Y-m-d 00:00:00');
								$start_date = strtotime($startdate . "+1 days");
								$sdate = date('d M Y', $start_date);
								if ($subplan_type == 1) {
									$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($startdate . "+" . (30 - $days) . " days")));
									$edate      = date('d M Y', $end_date);
								} elseif ($subplan_type == 2) {
									$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($startdate . "+" . (180 - $days) . " days")));
									$edate       = date('d M Y', $end_date);
								} else {
									$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($startdate . "+" . (365 - $days) . " days")));
									$edate      = date('d M Y', $end_date);
								}
							} else {
								$datediff = $sub_end - $now;
								$days = @round($datediff / (60 * 60 * 24));
								//remaining_days

								$plan_status = "Active";
								//$sub_end    = date('Y-m-d 23:59:59',$plan_data['sub_end']);
								//$startdate  = date('Y-m-d 00:00:00',strtotime($sub_end."+1 days"));

								$startdate  = date('Y-m-d 00:00:00');
								$start_date = strtotime($startdate . "+1 days");
								$sdate2 = date('Y-m-d 00:00:00', $start_date);
								$sdate = date('d M Y', $start_date);
								$start_date = strtotime($sdate2);
								if ($subplan_type == 1) {

									//$pre_one_day_amount = ($previous_amount/30);
									$remaining_days_amount = $days * $pre_one_day_amount;
									$new_amount = $amount - $remaining_days_amount;

									$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($sdate2 . "+30 days")));
									$edate      = date('d M Y', $end_date);
								} elseif ($subplan_type == 2) {

									//$pre_one_day_amount = ($previous_amount/180);
									$remaining_days_amount = $days * $pre_one_day_amount;
									$new_amount = $amount - $remaining_days_amount;

									$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($sdate2 . "+180 days")));
									$edate       = date('d M Y', $end_date);
								} else {

									//$pre_one_day_amount = ($previous_amount/365);
									$remaining_days_amount = $days * $pre_one_day_amount;
									$new_amount = $amount - $remaining_days_amount;

									$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($sdate2 . "+365 days")));
									$edate      = date('d M Y', $end_date);
								}
							}


							$msg = $this->lang->line('subscription_upcoming');
						}
					} else {
						$plan_status = "Active";
						$start_date  = strtotime(date("Y-m-d 00:00:00") . " +14 days");
						$sdate = date('d M Y', $start_date);
						if ($subplan_type == 1) {
							$end_date   = strtotime(date("Y-m-d 23:59:59") . " +43 days"); //30 + Free 14 days trial
							$edate      = date('d M Y', $end_date);
						} elseif ($subplan_type == 2) {
							$end_date   = strtotime(date("Y-m-d 23:59:59") . " +180 days"); //6 month + Free 14 days trial
							$edate      = date('d M Y', $end_date);
						} else {
							$end_date    = strtotime(date("Y-m-d 23:59:59") . " +365 days"); // 1 year +  Free 14 days trial
							$edate       = date('d M Y', $end_date);
						}
						$msg = $this->lang->line('subscription_succ');
					}

					$where1 = array(
						'sub_user_id' => $usid,
						'sub_plan_id' => $pre_plan_id
					);
					$subdata = array(
						'plan_status' => "Expire"
					);
					$this->dynamic_model->updateRowWhere('subscription', $where1, $subdata);

					//End of logic implement for purachase plan mothly haif yearly and yearly
					//Insert data in transaction table
					$transaction_data = array(
						'user_id'                => $usid,
						'amount'                 => $amount,
						'new_amount'				=> $new_amount,
						'trx_id'       		    => (!empty($payment_id)) ? $payment_id : $ref_num,
						'transaction_type'      => 1,
						'payment_status'        => "Success",
						'saved_card_id'         => $pay_id,
						'create_dt'        		=> $time,
						'update_dt'        		=> $time
					);
					$trx_id = $this->dynamic_model->insertdata('transactions', $transaction_data);
					$subscription_data = array(
						'sub_user_id'           => $usid,
						'sub_plan_id'     		=> $plan_id,
						'sub_start'       		=> $start_date,
						'sub_end'       		=> $end_date,
						'max_users_count'		=> $subscription_plan1[0]['max_users'],
						'create_dt'        		=> $time,
						'plan_status'        	=> $plan_status,
						'transaction_id'        => $trx_id,
						'update_dt'        		=> $time
					);
					$sub_id = $this->dynamic_model->insertdata('subscription', $subscription_data);

					////////////////////////////

					$card_id       = '';
					$customer_name = '';
					$number        = '';
					$expiry_month  = '';
					$expiry_year   = '';
					$cvd           = '';


					// $res_data = clover_api_card_profile_check($usid,$savecard,$customer_name,$number,$expiry_month,$expiry_year,$cvd,$country_code,$business_id,$token);
					$customer_code = time(); //$res_data['customer_code'];



					// Activate plan add in users table
					$where2 = array(
						'id' => $usid,
					);
					$plandata = array(
						'name' => $name,
						'lastname' => $lastname,
						'plan_id' => $plan_id
					);
					$this->dynamic_model->updateRowWhere('user', $where2, $plandata);
					//Get active plan data
					$whe  = array("id" => $plan_id);
					$activeplan_data = $this->dynamic_model->getdatafromtable("subscribe_plan", $whe);
					$plantype = (!empty($activeplan_data[0]['type'])) ? $activeplan_data[0]['type'] : '';
					if ($subplan_type == 1) {
						$plantype = 'Monthly';
					} elseif ($subplan_type == 2) {
						$plantype = 'Half Yearly';
					} else {
						$plantype = 'Yearly';
					}
					//End of active plan data
					$response  = array('plan_name' => $activeplan_data[0]['plan_name'], 'plan_status' => (string)$plan_status, 'amount' => number_format((float)$activeplan_data[0]['amount'], 2, '.', ''), 'transaction_date' => date('d M Y'), 'validity_from' => $sdate, 'validity_to' => $edate, 'plan_type' => $plantype);
					if ($sub_id) {
						$arg['status']    = 1;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['message']   = $msg;
						$arg['data']      = $response;
					} else {
						$arg['status']    = 0;
						$arg['error_code'] = HTTP_NOT_FOUND;
						$arg['error_line'] = __line__;
						$arg['message']   = $this->lang->line('subscription_failed');
						$arg['data']      = array();
					}
				}
			}
		}
		echo json_encode($arg);
	}

	public function subcription_plan_purchase()
	{
		$arg    = array();
		$version_result = version_check_helper1();
		if ($version_result['status'] != 1) {
			$arg = $version_result;
		} else {

			$_POST = json_decode(file_get_contents("php://input"), true);
			if ($_POST) {
				$this->form_validation->set_rules('user_id', 'User Id', 'required|trim', array('required' => $this->lang->line('user_id_required')));
				$this->form_validation->set_rules('name', 'Name', 'required|trim', array('required' => $this->lang->line('first_name')));
				//$this->form_validation->set_rules('lastname','Last Name', 'required|trim', array( 'required' => $this->lang->line('last_name')));
				$this->form_validation->set_rules('plan_id', 'Plan Id', 'trim|required', array(
					'required'   => $this->lang->line('plan_id_required')
				));
				$this->form_validation->set_rules('amount', 'Amount', 'required|greater_than[0]', array(
					'required'   => $this->lang->line('amount_required'),
					'numeric'    => $this->lang->line('amount_valid')
				));

				if ($this->input->post('save_card_check') == 1) {
					$this->form_validation->set_rules('card_number', 'Card Number', 'required|numeric|min_length[16]|max_length[16]', array(
						'required'   => $this->lang->line('card_required'),
						'min_length' => $this->lang->line('card_min_length'),
						'max_length' => $this->lang->line('card_max_length'),
						'numeric'    => $this->lang->line('card_numeric')
					));
					$this->form_validation->set_rules('cvv_no', 'Cvv no', 'required|numeric|min_length[3]|max_length[3]', array(
						'required'   => $this->lang->line('cvv_no_required'),
						'min_length' => $this->lang->line('cvv_no_min_length'),
						'max_length' => $this->lang->line('cvv_no_max_length'),
						'numeric'    => $this->lang->line('cvv_no_numeric')
					));
					$this->form_validation->set_rules('expiry_month', 'Expiry Month', 'required|numeric|less_than_equal_to[12]|greater_than[0]|min_length[2]', array(
						'required' => $this->lang->line('expiry_month_required'),
						'min_length' => $this->lang->line('expiry_month_min_length'),
						'less_than_equal_to' => $this->lang->line('expiry_month_less_than_equal_to'),
						'greater_than' => $this->lang->line('expiry_month_greater_than'),
						'numeric' => $this->lang->line('expiry_month_numeric')
					));
					$this->form_validation->set_rules('expiry_year', 'Expiry Year', 'required|numeric|min_length[4]|max_length[4]', array(
						'required'   => $this->lang->line('expiry_year_required'),
						'min_length' => $this->lang->line('expiry_year_min_length'),
						'max_length' => $this->lang->line('expiry_year_max_length'),
						'numeric'    => $this->lang->line('expiry_year_numeric')
					));
				}
				if ($this->form_validation->run() == FALSE) {

					$arg['status']  = 0;
					$arg['message'] = get_form_error($this->form_validation->error_array());
				} else {

					$usid  = decode($this->input->post('user_id'));

					$time = strtotime(date("Y-m-d H:i:s")); //time();
					$name            = $this->input->post('name');
					$lastname        = $this->input->post('lastname');
					$pre_plan_id      = decode($this->input->post('pre_plan_id'));
					$plan_id          = decode($this->input->post('plan_id'));
					$amount           = $this->input->post('amount');
					$cvv_no           = $this->input->post('cvv_no');
					$card_number      = $this->input->post('card_number');
					$card_type        = $this->input->post('card_type');
					$expiry_month     = $this->input->post('expiry_month');
					$expiry_year      = $this->input->post('expiry_year');
					$save_card_check  = $this->input->post('save_card_check');
					$token  = $this->input->post('token');
					$savecard = $save_card_check;
					$new_amount = $amount;
					// check year is valid
					if (check_expiry_year($expiry_year) == false) {
						$arg['status']  = 0;
						$arg['error_code'] =  ERROR_FAILED_CODE;
						$arg['error_line'] = __line__;
						$arg['message'] = $this->lang->line('invalid_expiry_year');
						echo json_encode($arg);
						exit;
					}

					// check year is valid
					if (check_expiry_month_year($expiry_month, $expiry_year) == false) {
						$arg['status']  = 0;
						$arg['error_code'] =  ERROR_FAILED_CODE;
						$arg['error_line'] = __line__;
						$arg['message'] = $this->lang->line('invalid_expiry_year_month');
						echo json_encode($arg);
						exit;
					}

					$where1  = array("id" => $usid);
					$loguser = $this->dynamic_model->getdatafromtable("user", $where1);
					if ($loguser[0]['email_verified'] !== '1') {
						$arg['status']    = 0;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['message']   = $this->lang->line('email_not_varify');
						$arg['data']      = array();
						echo json_encode($arg);
						exit;
					}

					$where1 = array('user_id' => $usid, 'status' => 'Active');
					$business_data = $this->dynamic_model->getdatafromtable('business', $where1);
					if (!empty($business_data)) {
						$business_id   = $business_data[0]['id'];
					} else {
						$business_id = 0;
					}

					$getCustomerId = strhlp_create_customer(
						array(
							'token'	=> $token,
							'name'	=> $name . ' ' . $lastname,
							'email'	=> $loguser[0]['email'],
							'user_id' => $usid
						)
					);

					if (!$getCustomerId) {
						$arg['status'] = 0;
						$arg['error_code'] = HTTP_OK;
						$arg['error_line'] = __line__;
						$arg['message']   = "Invalid token";
						$arg['data']      = array();
					} else {
						$card_res = $card_data = $card_Exist = array();
						$card_data = $this->dynamic_model->getdatafromtable('saved_card_details', array('user_id' => $usid), 'id,card_details');
						if (!empty($card_data)) {
							foreach ($card_data as $value) {
								$card_arr = json_decode(decode($value['card_details']));
								$card_id = $value['id'];
								$card_bank_no = $card_arr->card_bank_no;
								if ($card_number == $card_bank_no) {
									$card_Exist[] = array("id" => $card_id, "card_bank_no" => $card_bank_no);
								}
							}
						}

						if ($savecard == 1) {
							$cardStatus = strhlp_add_card(
								array(
									'token'			=>	$token,
									'customer_id'	=>	$getCustomerId
								)
							);

							if ($cardStatus == 'You cannot use a Stripe token more than once: ' . $token . '.') {

								$token = strhlp_get_token(
									array(
										'card_number'	=>	$card_number,
										'expiry_month'		=>	$expiry_month,
										'cvv_no'			=>	$cvv_no,
										'expiry_year'		=>	$expiry_year
									)
								);

								$cardStatus = strhlp_add_card(
									array(
										'token'			=>	$token,
										'customer_id'	=>	$getCustomerId
									)
								);
							}

							/****************************/
							$card_data = array('user_id' => $usid,
												'business_id' =>$business_id,
												'card_id' =>rand(99,99999999),
												'profile_id'=>$getCustomerId,
												'customer_name'=>$name,
												'card_no'=>$card_number,
												'expiry_year'=>$expiry_year,
												'expiry_month'=>$expiry_month,
												'card_token'=>$token,
												'card_type'=>''
											);
							$this->dynamic_model->insertdata('user_card_save', $card_data);
							/****************************/
						} else {
							$cardStatus = '';
						}

						$cardToken = strhlp_get_token(
							array(
								'card_number'	=>	$card_number,
								'expiry_month'		=>	$expiry_month,
								'cvv_no'			=>	$cvv_no,
								'expiry_year'		=>	$expiry_year
							)
						);

						/*strhlp_checkout(
							array(
								'amount'	=>	$amount,
								'name'		=>	$name . ' ' . $lastname,
								'email'		=>	$loguser[0]['email'],
								'description' => 'Studio Registration',
								'token'	=>	$cardToken
							),
							array(
								'user_id'	=>	$usid
							),
							2
						);*/

						if (empty($card_Exist)) {

							// Use for faster checkout
							if (@$save_card_check == 1)
								$del_status = 0;
							else
								$del_status = 1;
							// Firstly insert data into saved_card_details table
							$card_detail = array(

								'acc_holder_name'  => $name . ' ' . $lastname,
								'card_bank_no'     => $card_number,
								'expiry_month'     => $expiry_month,
								'expiry_year'      => $expiry_year
							);
							$json_card_data = encode(json_encode($card_detail));
							$token = 'token';
							$saved_card_details = array(
								'user_id'          => $usid,
								'is_debit_card'    => 0,
								'is_credit_card'    => 1,
								'is_deleted'       => $del_status,
								'created_by'       => $usid,
								'updated_by'       => $usid,
								'create_dt'        => $time,
								'update_dt'        => $time,
								'card_details'     => $json_card_data,
								'card_token'       => $token
							);
							$pay_id = $this->dynamic_model->insertdata('saved_card_details', $saved_card_details);
						} else {
							$pay_id = @$card_Exist[0]['id'];
						}

						$ref_num  = getuniquenumber();
						$payment_id   = $this->input->post('payment_id');

						//logic implement for purachase plan mothly haif yearly and yearly
						$wh  = array("id" => $pre_plan_id);
						$subscription_plan = $this->dynamic_model->getdatafromtable("subscribe_plan", $wh);
						if (!empty($subscription_plan)) {
							$pre_subplan_type = $subscription_plan[0]['type'];
							$previous_amount = $subscription_plan[0]['amount'];

							if ($pre_subplan_type == 1) {
								$pre_one_day_amount = ($previous_amount / 30);
							} else if ($pre_subplan_type == 2) {
								$pre_one_day_amount = ($previous_amount / 180);
							} else {
								$pre_one_day_amount = ($previous_amount / 356);
							}
						} else {
							$pre_one_day_amount = 0;
						}

						$wh1  = array("id" => $plan_id);
						$subscription_plan1 = $this->dynamic_model->getdatafromtable("subscribe_plan", $wh1);
						$subplan_type = $subscription_plan1[0]['type'];
						$newplan_amount = $subscription_plan1[0]['amount'];

						$curr_date = $time;
						$plan_data = $this->studio_model->plan_check($pre_plan_id, $usid);
						if (!empty($plan_data)) {
							$sub_end = $plan_data['sub_end'];
							if ($curr_date > $sub_end) {
								$plan_status = "Active";
								$start_date  = strtotime(date("Y-m-d 00:00:00") . " +14 days");
								$sdate = date('d M Y', $start_date);
								if ($subplan_type == 1) {
									$end_date   = strtotime(date("Y-m-d 23:59:59") . " +43 days"); //30 + Free 14 days trial
									$edate      = date('d M Y', $end_date);
								} elseif ($subplan_type == 2) {
									$end_date   = strtotime(date("Y-m-d 23:59:59") . " +180 days"); //6 month + Free 14 days trial
									$edate      = date('d M Y', $end_date);
								} else {
									$end_date    = strtotime(date("Y-m-d 23:59:59") . " +365 days"); // 1 year +  Free 14 days trial
									$edate       = date('d M Y', $end_date);
								}
								/*$where1 = array(
													'sub_user_id' => $usid,
													'sub_plan_id' => $pre_plan_id
											);
											$subdata = array(
												'plan_status' => "Expire"
											);
											$this->dynamic_model->updateRowWhere('subscription',$where1,$subdata);	*/
								$msg = $this->lang->line('subscription_succ');
							} else {

								$days = 0;
								$now = $time; //time(); // or your date as well
								//$start_your_date = $plan_data['sub_start'];
								if ($now > $sub_end) {
									$datediff = $now - $sub_end;
									$days = @round($datediff / (60 * 60 * 24));
									//used_days

									$plan_status = "Active";
									//$sub_end    = date('Y-m-d 23:59:59',$plan_data['sub_end']);
									//$startdate  = date('Y-m-d 00:00:00',strtotime($sub_end."+1 days"));

									$startdate  = date('Y-m-d 00:00:00');
									$start_date = strtotime($startdate . "+1 days");
									$sdate = date('d M Y', $start_date);
									if ($subplan_type == 1) {
										$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($startdate . "+" . (30 - $days) . " days")));
										$edate      = date('d M Y', $end_date);
									} elseif ($subplan_type == 2) {
										$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($startdate . "+" . (180 - $days) . " days")));
										$edate       = date('d M Y', $end_date);
									} else {
										$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($startdate . "+" . (365 - $days) . " days")));
										$edate      = date('d M Y', $end_date);
									}
								} else {
									$datediff = $sub_end - $now;
									$days = @round($datediff / (60 * 60 * 24));
									//remaining_days

									$plan_status = "Active";
									//$sub_end    = date('Y-m-d 23:59:59',$plan_data['sub_end']);
									//$startdate  = date('Y-m-d 00:00:00',strtotime($sub_end."+1 days"));

									$startdate  = date('Y-m-d 00:00:00');
									$start_date = strtotime($startdate . "+1 days");
									$sdate2 = date('Y-m-d 00:00:00', $start_date);
									$sdate = date('d M Y', $start_date);
									$start_date = strtotime($sdate2);
									if ($subplan_type == 1) {

										//$pre_one_day_amount = ($previous_amount/30);
										$remaining_days_amount = $days * $pre_one_day_amount;
										$new_amount = $amount - $remaining_days_amount;

										$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($sdate2 . "+30 days")));
										$edate      = date('d M Y', $end_date);
									} elseif ($subplan_type == 2) {

										//$pre_one_day_amount = ($previous_amount/180);
										$remaining_days_amount = $days * $pre_one_day_amount;
										$new_amount = $amount - $remaining_days_amount;

										$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($sdate2 . "+180 days")));
										$edate       = date('d M Y', $end_date);
									} else {

										//$pre_one_day_amount = ($previous_amount/365);
										$remaining_days_amount = $days * $pre_one_day_amount;
										$new_amount = $amount - $remaining_days_amount;

										$end_date   = strtotime(date('Y-m-d 23:59:59', strtotime($sdate2 . "+365 days")));
										$edate      = date('d M Y', $end_date);
									}
								}


								$msg = $this->lang->line('subscription_upcoming');
							}
						} else {
							$plan_status = "Active";
							$start_date  = strtotime(date("Y-m-d 00:00:00") . " +14 days");
							$sdate = date('d M Y', $start_date);
							if ($subplan_type == 1) {
								$end_date   = strtotime(date("Y-m-d 23:59:59") . " +43 days"); //30 + Free 14 days trial
								$edate      = date('d M Y', $end_date);
							} elseif ($subplan_type == 2) {
								$end_date   = strtotime(date("Y-m-d 23:59:59") . " +180 days"); //6 month + Free 14 days trial
								$edate      = date('d M Y', $end_date);
							} else {
								$end_date    = strtotime(date("Y-m-d 23:59:59") . " +365 days"); // 1 year +  Free 14 days trial
								$edate       = date('d M Y', $end_date);
							}
							$msg = $this->lang->line('subscription_succ');
						}

						$where1 = array(
							'sub_user_id' => $usid,
							'sub_plan_id' => $pre_plan_id
						);
						$subdata = array(
							'plan_status' => "Expire"
						);
						$this->dynamic_model->updateRowWhere('subscription', $where1, $subdata);

						//End of logic implement for purachase plan mothly haif yearly and yearly
						//Insert data in transaction table
						$transaction_data = array(
							'user_id'                => $usid,
							'amount'                 => $amount,
							'new_amount'				=> $new_amount,
							'trx_id'       		    => (!empty($payment_id)) ? $payment_id : $ref_num,
							'transaction_type'      => 1,
							'payment_status'        => "Success",
							'saved_card_id'         => $pay_id,
							'create_dt'        		=> $time,
							'update_dt'        		=> $time
						);

						$trx_id = $this->dynamic_model->insertdata('transactions', $transaction_data);
						$subscription_data = array(
							'sub_user_id'           => $usid,
							'sub_plan_id'     		=> $plan_id,
							'sub_start'       		=> $start_date,
							'sub_end'       		=> $end_date,
							'max_users_count'		=> $subscription_plan1[0]['max_users'],
							'create_dt'        		=> $time,
							'plan_status'        	=> $plan_status,
							'transaction_id'        => $trx_id,
							'update_dt'        		=> $time
						);
						$sub_id = $this->dynamic_model->insertdata('subscription', $subscription_data);

						$card_id       = '';
						$customer_name = '';
						$number        = '';
						$expiry_month  = '';
						$expiry_year   = '';
						$cvd           = '';
						$country_code = 0;
						//$res_data = clover_api_card_profile_check($usid, $savecard, $customer_name, $number, $expiry_month, $expiry_year, $cvd, $country_code, $business_id, $token);
						// $customer_code = $res_data['customer_code'];

						// Activate plan add in users table
						$where2 = array(
							'id' => $usid,
						);
						$plandata = array(
							'name' => $name,
							'lastname' => $lastname,
							'plan_id' => $plan_id
						);

						$this->dynamic_model->updateRowWhere('user', $where2, $plandata);
						//Get active plan data
						$whe  = array("id" => $plan_id);
						$activeplan_data = $this->dynamic_model->getdatafromtable("subscribe_plan", $whe);
						$plantype = (!empty($activeplan_data[0]['type'])) ? $activeplan_data[0]['type'] : '';
						if ($subplan_type == 1) {
							$plantype = 'Monthly';
						} elseif ($subplan_type == 2) {
							$plantype = 'Half Yearly';
						} else {
							$plantype = 'Yearly';
						}
						//End of active plan data
						$response  = array('plan_name' => $activeplan_data[0]['plan_name'], 'plan_status' => (string)$plan_status, 'amount' => number_format((float)$activeplan_data[0]['amount'], 2, '.', ''), 'transaction_date' => date('d M Y'), 'validity_from' => $sdate, 'validity_to' => $edate, 'plan_type' => $plantype);
						if ($sub_id) {
							$arg['status']    = 1;
							$arg['error_code'] = HTTP_OK;
							$arg['error_line'] = __line__;
							$arg['message']   = $msg;
							$arg['data']      = $response;
						} else {
							$arg['status']    = 0;
							$arg['error_code'] = HTTP_NOT_FOUND;
							$arg['error_line'] = __line__;
							$arg['message']   = $this->lang->line('subscription_failed');
							$arg['data']      = array();
						}
					}
				}
			}
		}
		echo json_encode($arg);
	}
	

	public function subscription_plan_recurring()
	{
		$current_date = strtotime(date('Y-m-d 00:00:00'));

		$subscription_user_data = $this->dynamic_model->customQuery("select * from subscription where plan_status='Active' and is_recurring='0' and sub_start = '" . $current_date . "' order by sub_id desc ");
		if (count($subscription_user_data) > 0) {
			foreach ($subscription_user_data as $key => $val_data) {
				$time = strtotime(date("Y-m-d H:i:s"));
				$usid = $val_data->sub_user_id;
				$plan_id = $val_data->sub_plan_id;
				$max_users_count = $val_data->max_users_count;
				$sub_id = $val_data->sub_id;
				$sub_end = $val_data->sub_end;


				$wh1  = array("id" => $plan_id);
				$subscription_plan1 = $this->dynamic_model->getdatafromtable("subscribe_plan", $wh1);
				$subplan_type = $subscription_plan1[0]['type'];

				$start_date = strtotime(date("Y-m-d 00:00:00", $sub_end) . "+1 days");

				if ($subplan_type == 1) {
					$end_date   = strtotime(date("Y-m-d 23:59:59", $start_date) . " +30 days"); //30 days

				} elseif ($subplan_type == 2) {
					$end_date   = strtotime(date("Y-m-d 23:59:59", $start_date) . " +180 days"); //6 month

				} else {
					$end_date    = strtotime(date("Y-m-d 23:59:59", $start_date) . " +365 days"); // 1 year

				}

				$transactions_data = $this->dynamic_model->customQuery("select * from transactions where id='" . $val_data->transaction_id . "' and user_id='" . $usid . "' ");
				if (count($transactions_data) > 0) {
					$amount  = $transactions_data[0]->amount;
				} else {
					$amount  = 0;
				}

				$user_card_save = $this->dynamic_model->customQuery("select * from user_card_save where user_id='" . $usid . "' order by id desc");
				if (count($user_card_save) > 0) {
					$token  = $user_card_save[0]->card_token;
					$business_id = $user_card_save[0]->business_id;
				} else {
					$token  = 0;
					$business_id = 0;
				}



				/**********************************/
				$savecard      = 1;
				$card_id       = '';
				$customer_name = '';
				$number        = '';
				$expiry_month  = '';
				$expiry_year   = '';
				$cvd           = '';
				$country_code  = '';

				// $res_data = clover_api_card_profile_check($usid,$savecard,$customer_name,$number,$expiry_month,$expiry_year,$cvd,$country_code,$business_id,$token);
				// $customer_code= $res_data['customer_code'];
				// $marchant_id  = $res_data['marchant_id'];
				// $country_code = $res_data['country_code'];
				// $clover_key   = $res_data['clover_key'];
				// $access_token = $res_data['access_token'];
				// $currency     = $res_data['currency'];


				$user_cc_no   = $number;
				$user_cc_mo   = $expiry_month;
				$user_cc_yr   = $expiry_year;
				$user_cc_cvv  = $cvd;
				$user_zip     = '';
				$amount       = $amount;
				$taxAmount    = 0;

				// $res  = clover_api_payment_checkout($user_cc_no,$user_cc_mo,$user_cc_yr,$user_cc_cvv,$user_zip,$amount,$taxAmount,$marchant_id,$clover_key,$access_token,$currency,$token);

				//{ "id" : "R48Q7GPMY2FQY", "amount" : 1800, "amount_refunded" : 0, "currency" : "usd", "created" : 1616661109846, "captured" : true, "ref_num" : "108400500020", "auth_code" : "OK2809", "outcome" : { "network_status" : "approved_by_network", "type" : "authorized" }, "paid" : true, "status" : "succeeded", "source" : { "id" : "clv_1TSTS1iWemARMj4AXHxHikLV", "brand" : "AMEX", "cvc_check" : "unchecked", "exp_month" : "11", "exp_year" : "2025", "first6" : "378282", "last4" : "0005" } }

				//{"message":"400 Bad Request","error":{"code":"token_already_used","message":"You cannot use a clover token more than once unless it is marked as multipay."}}


				//{"paymentId":"30PFKD66YXNCP","result":"APPROVED","authCode":"OK7823","token":"DGY73XR6DDDF0","vaultedCard":{"first6":"411111","last4":"1111","expirationDate":"0321","token":"1894469479681111"}}

				//echo $res['message'];die;
				// if(@$res->status == 'succeeded')
				if (true) {


					$where2 = array(
						'sub_id' => $sub_id,
					);
					$plandata = array(
						'is_recurring' => 1
					);
					$this->dynamic_model->updateRowWhere('subscription', $where2, $plandata);


					/*$where = array('user_id' => $usid,
						'business_id' => $business_id,
					);
					$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);*/

					$ref_num    = getuniquenumber();
					$payment_id = time(); // !empty($res->id) ? $res->id : $ref_num;
					$authorizing_merchant_id = time(); //$res->source->id;
					$payment_type   = 'Card';
					$payment_method = 'Online';
					$amount         = $amount;

					//End of logic implement for purachase plan mothly haif yearly and yearly
					//Insert data in transaction table
					$transaction_data = array(
						'authorizing_merchant_id' => $authorizing_merchant_id,
						'payment_type' => $payment_type,
						'payment_method' => $payment_method,
						'responce_all' => '', // json_encode($res),
						'user_id' => $usid,
						'amount' => $amount,
						'trx_id' => $payment_id,
						'order_number' => $time,
						'transaction_type' => 1,
						'payment_status' => "Success",
						'saved_card_id' => 0,
						'create_dt' => $time,
						'update_dt' => $time,
					);
					$trx_id = $this->dynamic_model->insertdata('transactions', $transaction_data);


					$subscription_data = array(
						'sub_user_id'           => $usid,
						'sub_plan_id'     		=> $plan_id,
						'sub_start'       		=> $start_date,
						'sub_end'       		=> $end_date,
						'max_users_count'		=> $max_users_count,
						'create_dt'        		=> $time,
						'plan_status'        	=> 'Upcoming',
						'transaction_id'        => $trx_id,
						'update_dt'        		=> $time
					);
					$subid = $this->dynamic_model->insertdata('subscription', $subscription_data);
				}
				/***********************************/
			}
		}
	}

	//https://staging.warriorsms.com/superadmin/web/transaction_new/pass_booking_recurring
	public function pass_booking_recurring()
	{
		$recurring_date = date('Y-m-d');

		$user_booking_data = $this->dynamic_model->customQuery("select * from user_booking where is_recurring_stop=0 and recurring_date = '" . $recurring_date . "' and recurring > 0 order by id desc ");
		if (count($user_booking_data) > 0) {
			foreach ($user_booking_data as $key => $val_data) {
				$time = strtotime(date("Y-m-d H:i:s"));
				$usid           = $val_data->user_id;
				$business_id    = $val_data->business_id;
				//$amount         = $val_data->amount;
				$recurring_date = $val_data->recurring_date;
				$recurring      = $val_data->recurring;
				$passes_total_count = $val_data->passes_total_count;
				$transaction_id     = $val_data->transaction_id;
				$user_booking_id    = $val_data->id;
				$passes_start_date  = $val_data->passes_start_date;
				$passes_end_date    = $val_data->passes_end_date;

				if ($passes_total_count == 365) {
					$pass_recurring = 'Yearly'; //time Duration for recurring

					$new_recurring_date = date("Y-m-t", strtotime(date('Y-m-d')));
					$new_recurring_count = $recurring - 1;
				} else if ($passes_total_count == 30) {
					$pass_recurring = 'Monthly'; //Time Duration for recurring

					$new_recurring_date = date("Y-m-t", strtotime('next month'));  //date("Y-m-t", strtotime(date('Y-m-d')));
					$new_recurring_count = 1;

					if (date('m') == 12) {
						$passes_start_date  = strtotime((date('Y') + 1) . '-01-01');
					} else {
						$passes_start_date  = strtotime(date('Y') . '-' . (date('m') + 1) . '-01');
					}

					$passes_end_date  = strtotime(date("Y-m-t", strtotime('next month')));
				} else if ($passes_total_count == 180) {
					$pass_recurring = '6 Monthly'; //Time Duration for recurring

					$new_recurring_date = date("Y-m-t", strtotime(date('Y-m-d')));
					$new_recurring_count = $recurring - 1;
				} else if ($passes_total_count == 90) {
					$pass_recurring = '3 Mothly'; //Time Duration for recurring

					$new_recurring_date = date("Y-m-t", strtotime(date('Y-m-d')));
					$new_recurring_count = $recurring - 1;
				}



				$transactions_data = $this->dynamic_model->customQuery("select * from transactions where id='" . $transaction_id . "' and user_id='" . $usid . "' ");
				if (count($transactions_data) > 0) {
					$amount  = $transactions_data[0]->amount;
				} else {
					$amount  = 0;
				}

				$user_card_save = $this->dynamic_model->customQuery("select * from user_card_save where user_id='" . $usid . "' and id_deleted=0 order by id desc");
				if (count($user_card_save) > 0) {
					$token  = $user_card_save[0]->card_token;
					$business_id = $user_card_save[0]->business_id;
				} else {
					$token  = 0;
					$business_id = 0;
				}



				/**********************************/
				$savecard      = 1;
				$card_id       = '';
				$customer_name = '';
				$number        = '';
				$expiry_month  = '';
				$expiry_year   = '';
				$cvd           = '';
				$country_code  = '';

				$res_data = clover_api_card_profile_check($usid, $savecard, $customer_name, $number, $expiry_month, $expiry_year, $cvd, $country_code, $business_id, $token);
				$customer_code = $res_data['customer_code'];
				$marchant_id  = $res_data['marchant_id'];
				$country_code = $res_data['country_code'];
				$clover_key   = $res_data['clover_key'];
				$access_token = $res_data['access_token'];
				$currency     = $res_data['currency'];


				$user_cc_no   = $number;
				$user_cc_mo   = $expiry_month;
				$user_cc_yr   = $expiry_year;
				$user_cc_cvv  = $cvd;
				$user_zip     = '';
				$amount       = $amount;
				$taxAmount    = 0;

				$res  = clover_api_payment_checkout($user_cc_no, $user_cc_mo, $user_cc_yr, $user_cc_cvv, $user_zip, $amount, $taxAmount, $marchant_id, $clover_key, $access_token, $currency, $token);
				//var_dump($res); die;
				//{ "id" : "R48Q7GPMY2FQY", "amount" : 1800, "amount_refunded" : 0, "currency" : "usd", "created" : 1616661109846, "captured" : true, "ref_num" : "108400500020", "auth_code" : "OK2809", "outcome" : { "network_status" : "approved_by_network", "type" : "authorized" }, "paid" : true, "status" : "succeeded", "source" : { "id" : "clv_1TSTS1iWemARMj4AXHxHikLV", "brand" : "AMEX", "cvc_check" : "unchecked", "exp_month" : "11", "exp_year" : "2025", "first6" : "378282", "last4" : "0005" } }

				//{"message":"400 Bad Request","error":{"code":"token_already_used","message":"You cannot use a clover token more than once unless it is marked as multipay."}}


				//{"paymentId":"30PFKD66YXNCP","result":"APPROVED","authCode":"OK7823","token":"DGY73XR6DDDF0","vaultedCard":{"first6":"411111","last4":"1111","expirationDate":"0321","token":"1894469479681111"}}

				//echo $res['message'];die;
				if (@$res->status == 'succeeded') {
					/*$where = array('user_id' => $usid,
						'business_id' => $business_id,
					);
					$result_card = $this->dynamic_model->getdatafromtable('user_card_save', $where);*/

					$ref_num    = getuniquenumber();
					$payment_id = !empty($res->id) ? $res->id : $ref_num;
					$authorizing_merchant_id = $res->source->id;
					$payment_type   = 'Card';
					$payment_method = 'Recurring';
					$amount         = $amount;

					//End of logic implement for purachase plan mothly haif yearly and yearly
					//Insert data in transaction table
					$transaction_data = array(
						'authorizing_merchant_id' => $authorizing_merchant_id,
						'payment_type' => $payment_type,
						'payment_method' => $payment_method,
						'responce_all' => json_encode($res),
						'user_id' => $usid,
						'amount' => $amount,
						'trx_id' => $payment_id,
						'order_number' => $time,
						'transaction_type' => 1,
						'payment_status' => "Success",
						'saved_card_id' => 0,
						'create_dt' => $time,
						'update_dt' => $time,
					);
					$trx_id = $this->dynamic_model->insertdata('transactions', $transaction_data);


					$this->dynamic_model->updateRowWhere('user_booking', array('id' => $user_booking_id), array('recurring' => $new_recurring_count, 'recurring_date' => date('Y-m-d', strtotime($new_recurring_date)), 'passes_start_date' => $passes_start_date, 'passes_end_date' => $passes_end_date));

					$pass_recurring_data = array(
						'user_booking_id'       => $user_booking_id,
						'user_id'               => $usid,
						'business_id'     		=> $business_id,
						'transaction_id'        => $trx_id,
						'amount'       		    => $amount,
						'payment_mode'			=> $payment_method,
						'passes_total_count'	=> $passes_total_count,
						'create_by'        		=> $usid,
						'create_dt'        		=> $time,
						'recurring'        		=> $new_recurring_count,
						'recurring_date'		=> date('Y-m-d', strtotime($new_recurring_date)),
						'passes_start_date'     => $passes_start_date,
						'passes_end_date'		=> $passes_end_date
					);
					$sub_id = $this->dynamic_model->insertdata('pass_recurring_data', $pass_recurring_data);
				} else {

					$ref_num    = getuniquenumber();
					$payment_type   = 'Card';
					$payment_method = 'Online';
					$amount         = $amount;

					$transaction_data = array(
						'authorizing_merchant_id' => $ref_num,
						'payment_type' => $payment_type,
						'payment_method' => $payment_method,
						'responce_all' => json_encode($res),
						'user_id' => $usid,
						'amount' => $amount,
						'trx_id' => $ref_num,
						'order_number' => $time,
						'transaction_type' => 1,
						'payment_status' => "Failure",
						'saved_card_id' => 0,
						'create_dt' => $time,
						'update_dt' => $time,
					);
					$trx_id = $this->dynamic_model->insertdata('transactions', $transaction_data);
				}
				/***********************************/
			}
		}
	}

	//--------------------------*************End of Api*************---------------------------//

}
