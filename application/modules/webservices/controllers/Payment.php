<?php defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

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


class Payment extends REST_Controller
{

  public function __construct()
  {
    parent::__construct();
    // header('Content-Type: application/json');
    //       header('Access-Control-Allow-Origin: *');
    //       header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,version,lang,userid,token");
    //       header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers,Authorization,X-API-KEY,Origin,X-Requested-With,userid,token,timeZone,timeZoneOffset,language,version,deviceId,deviceType,lat,lang,role");
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == "OPTIONS") {
      die();
    }

    $this->load->library('form_validation');
    $this->load->library('session');
    $this->load->library('Bomborapay');
    $this->load->model('dynamic_model');
    $this->load->model('api_model');
    $language = $this->input->get_request_header('language');
    if ($language == "en") {
      $this->lang->load("message", "english");
    } else if ($language == "ar") {
      $this->lang->load("message", "arabic");
    } else {
      $this->lang->load("message", "english");
    }
  }

  // App Version Check
  public function payment_checkout_post()
  {
    // $merchant_id = '300211950';
    //    $api_key = '1cAc137274064e6598A4f4E412D33A98'; 
    //    echo $this->input->get_request_header('Authorization', true);;die;

    $_POST = json_decode(file_get_contents("php://input"), true);
    $name = $this->input->post('name');
    $card_number = $this->input->post('card_number');
    $cvv_no = $this->input->post('cvv_no');
    $expiry_month = $this->input->post('expiry_month');
    $expiry_year = $this->input->post('expiry_year');
    $save_card_check = $this->input->post('save_card_check');
    $amount = $this->input->post('amount');
    $amount = number_format($amount, 2, ',', '.');
    $order_number = time();
    $payment_data = array(
      'order_number' => $order_number,
      'amount' => $amount,
      'payment_method' => 'card',
      'card' => array(
        'name' => $name,
        'number' => $card_number,
        'expiry_month' => $expiry_month,
        'expiry_year' => $expiry_year,
        'cvd' => $cvv_no,
        'complete' => true // false for pre-auth
      )
    );

    $result = $this->bomborapay->makePayment($payment_data);
    print_r($result);
  }
  public function tokenPayment_post()
  {
    $_POST = json_decode(file_get_contents("php://input"), true);
    $name = $this->input->post('name');
    $card_number = $this->input->post('card_number');
    $cvv_no = $this->input->post('cvv_no');
    $expiry_month = $this->input->post('expiry_month');
    $expiry_year = $this->input->post('expiry_year');
    $save_card_check = $this->input->post('save_card_check');
    $amount = $this->input->post('amount');
    $amount = number_format($amount, 2, ',', '.');
    $order_number = time();
    //       $payment_data = array(
    //        'order_number' => $order_number,
    //        'amount' => $amount,
    //        'name' => 'Mrs. Legato Testerson'	
    // );
    $amount = $this->input->post('amount');
    $token = $this->input->post('token');
    $amount = number_format($amount, 2, ',', '.');
    $order_number = time();
    $payment_data = array(
      'order_number' => $order_number,
      'amount' => $amount,
      'payment_method' => 'token',
      'token' => array(
        'name' => $name,
        'code' => $token,
        'complete' => true
      )
    );

    $url = 'https://api.na.bambora.com/v1/payments ';
    $res = $this->bomborapay->payment_checkout('POST', $url, $payment_data);
    print_r($res);
    die;

    //      $merchant_id = '300211950';
    //      $api_key = '1cAc137274064e6598A4f4E412D33A98';
    //   $result=$this->bomborapay->makePayment($merchant_id,$api_key,$payment_data);
    // print_r($result);
  }
  public function getToken_post()
  {
    $_POST = json_decode(file_get_contents("php://input"), true);
    $url = 'https://api.na.bambora.com/scripts/tokenization/tokens';
    $legato_token_data = array(
      'number' => '4030000010001234',
      'expiry_month' => '07',
      'expiry_year' => '22',
      'cvd' => '123'
    );
    //$auth='Passcode '.$encode_auth;
    $res = $this->bomborapay->payment_checkout('POST', $url, $legato_token_data);
    print_r($res);
    die;

    $name = $this->input->post('name');
    $card_number = $this->input->post('card_number');
    $cvv_no = $this->input->post('cvv_no');
    $expiry_month = $this->input->post('expiry_month');
    $expiry_year = $this->input->post('expiry_year');
    $save_card_check = $this->input->post('save_card_check');
    $amount = $this->input->post('amount');
    $amount = number_format($amount, 2, ',', '.');
    $order_number = time();
    //       $payment_data = array(
    //        'order_number' => $order_number,
    //        'amount' => $amount,
    //        'name' => 'Mrs. Legato Testerson'	
    // );
    $amount = $this->input->post('amount');
    $amount = number_format($amount, 2, ',', '.');
    $order_number = time();

    $result = $this->bomborapay->getTokenTest($legato_token_data);
    print_r($result);
  }
  public function clover_card_save_post()
  {

    $arg           = array();
    $_POST         = json_decode(file_get_contents("php://input"), true);

    $usid   = decode($this->input->post('user_id'));
    $savecard      = 1;
    $customer_name = $this->input->post('name');
    $number        = $this->input->post('number');
    $expiry_month  = $this->input->post('expiry_month');
    $expiry_year   = $this->input->post('expiry_year');
    $cvd           = $this->input->post('cvv');
    $country_code  = $this->input->post('country_code');
    $business_id   = $this->input->post('business_id');
    $token         = $this->input->post('card_token');

    $response = clover_api_card_profile_check($usid, $savecard, $customer_name, $number, $expiry_month, $expiry_year, $cvd, $country_code, $business_id, $token);

    // print_r($response); die;
    if ($response['marchant_id'] != '') {
      $arg['status'] = 1;
      $arg['error_code'] = REST_Controller::HTTP_OK;
      $arg['error_line'] = __line__;
      $arg['message'] = 'Card Info Successfully Added';
    } else {
      $arg['status'] = 0;
      $arg['error_code'] = 0;
      $arg['error_line'] = __line__;
      $arg['message'] = 'Card Not Added';
    }
    echo json_encode($arg);
  }

  public function cardSave_post()
  {

    $arg   = array();
    $_POST = json_decode(file_get_contents("php://input"), true);
    $userid = decode($this->input->post('userid'));
    $comments = $this->input->post('comments');
    $name = $this->input->post('name');
    $code = $this->input->post('code');

    $url = 'https://api.na.bambora.com/v1/profiles';
    $legato_token_data = array(
      'language' => 'en',
      'comments' => $comments,
      'token' => array(
        'name' => $name,
        'code' => $code
      )
    );

    $res = $this->bomborapay->profile_create('POST', $url, $legato_token_data);
    if ($res['code'] == '1') {
      $transaction_data = array(
        'user_id' => $userid,
        'card_id' => $res['customer_code'],
      );
      $transaction_id = $this->dynamic_model->insertdata('user_card_save', $transaction_data);

      $arg['status']    = 1;
      $arg['error_code'] = REST_Controller::HTTP_OK;
      $arg['error_line'] = __line__;
      $arg['message']   = $res['message'];
      // $arg['data']      = $data_val;
    } else {
      $arg['status']  = 0;
      $arg['error_code'] = 0;
      $arg['error_line'] = __line__;
      $arg['message'] = $res['message'];
    }

    echo json_encode($arg);
  }



  public function appoiment_cancel_post()
  {
    $arg    = array();
    $version_result = version_check_helper1();
    if ($version_result['status'] != 1) {
      $arg = $version_result;
    } else {
      $userdata = checkuserid();
      if ($userdata['status'] != 1) {
        $arg = $userdata;
      } else {
        $_POST = json_decode(file_get_contents("php://input"), true);
        $this->form_validation->set_rules('appointment_id', 'appointment id', 'required|numeric');

        if ($this->form_validation->run() == FALSE) {
          $arg['status']  = 0;
          $arg['error_code'] = 0;
          $arg['error_line'] = __line__;
          $arg['message'] = get_form_error($this->form_validation->error_array());
        } else {
          $response = array();
          $time = time();
          $appointment_id = $this->input->post('appointment_id');

          $query = 'SELECT * FROM business_appointment_book as ba WHERE ba.id = ' . $appointment_id;
          $collection = $this->dynamic_model->getQueryRowArray($query);
          if (!empty($collection)) {

            $slot_available_status = $collection['slot_available_status'];
            if ($slot_available_status == '3') {
              $arg['status']  = 0;
              $arg['error_code'] = 0;
              $arg['error_line'] = __line__;
              $arg['message'] = 'Appointment already cancel.';
            } else if ($slot_available_status == '2') {
              $arg['status']  = 0;
              $arg['error_code'] = 0;
              $arg['error_line'] = __line__;
              $arg['message'] = 'Appointment not cancel.';
            } else {
              //update slote status
              $where = array('id' => $appointment_id);
              $updateData = array('slot_available_status' => '3');
              $this->dynamic_model->updateRowWhere('business_appointment_book', $where, $updateData);
              $query = 'SELECT * FROM business_appointment_book as ba WHERE ba.id = ' . $appointment_id;
              $collection = $this->dynamic_model->getQueryRowArray($query);

              $arg['status']    = 1;
              $arg['error_code'] = REST_Controller::HTTP_OK;
              $arg['error_line'] = __line__;
              $arg['message']    = 'Appointment successfully cancel';
              $arg['data'] = $collection;
            }
          } else {
            $arg['status']  = 0;
            $arg['error_code'] = 0;
            $arg['error_line'] = __line__;
            $arg['message'] = 'no appointment found';
          }
        }
      }
    }
    echo json_encode($arg);
  }


  public function cardGet_post()
  {
    $arg   = array();
    $_POST = json_decode(file_get_contents("php://input"), true);
    $userid = $this->input->post('userid');

    $types = $this->input->post('types');
    if ($types == 'studio') {
      $userid = decode($this->input->post('userid'));
    }
    $data = array();
    $card_data = $this->dynamic_model->getdatafromtable('user_card_save', array('user_id' => $userid, 'id_deleted' => '0'));
    if ($card_data) {
      foreach ($card_data as $key => $value) {
        $data[] = array(
          'customer_code' => $value['profile_id'],
          'user_id' => $value['user_id'],
          'card_id' => $value['id'],
          'business_id' => $value['business_id'],
          'business_name' => '',
          'card_token' => $value['card_token'],
          'function' => '',
          'name' => $value['customer_name'],
          'number' => $value['card_no'],
          'expiry_month' => $value['expiry_month'],
          'expiry_year' => $value['expiry_year'],
          'card_type' => $value['card_type'],
        );
      }
      $arg['status']    = 1;
      $arg['error_code'] = REST_Controller::HTTP_OK;
      $arg['error_line'] = __line__;
      $arg['message']   = 'Card Loaded successfully';
      $arg['data']      = $data;
    } else {
      $arg['status']  = 0;
      $arg['error_code'] = 0;
      $arg['error_line'] = __line__;
      $arg['message'] = 'no card found';
    }
  }


  public function cardDelete_post()
  {
    $arg   = array();
    $_POST = json_decode(file_get_contents("php://input"), true);
    $profile_id = $this->input->post('id');
    $card_id = $this->input->post('card_id');
    $userid = decode($this->input->post('userid'));

    $this->form_validation->set_rules('id', 'profile id', 'required');
    $this->form_validation->set_rules('card_id', 'card id', 'required');
    $this->form_validation->set_rules('userid', 'user id', 'required');

    if ($this->form_validation->run() == FALSE) {
      $arg['status']  = 0;
      $arg['error_code'] = 0;
      $arg['error_line'] = __line__;
      $arg['message'] = get_form_error($this->form_validation->error_array());
    } else {

      $this->dynamic_model->updateRowWhere('user_card_save', array('card_id' => $card_id, 'profile_id' => $profile_id, 'user_id' => $userid), array('id_deleted' => 1));


      $arg['status']    = 1;
      $arg['error_code'] = REST_Controller::HTTP_OK;
      $arg['error_line'] = __line__;
      $arg['message']   = 'Card Successfully Removed';
      $arg['data']      = array();
    }
    echo json_encode($arg);
  }
}
