<?php defined('BASEPATH') OR exit('No direct script access allowed');
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


class Questionnaire extends REST_Controller {

	public function __construct() {
		parent::__construct();
		// header('Content-Type: application/json');
  //       header('Access-Control-Allow-Origin: *');
  //       header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,version,lang,userid,token");
  //       header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE,OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers,Authorization,X-API-KEY,Origin,X-Requested-With,userid,token,timeZone,timeZoneOffset,language,version,deviceId,deviceType,lat,lang,role");
         $method = $_SERVER['REQUEST_METHOD'];
        if($method == "OPTIONS") {
            die();
        }
		
        $this->load->library('form_validation');
		$this->load->library('session');
		$this->load->library('Bomborapay');
		$this->load->model('dynamic_model');
		$this->load->model('api_model');	
		$language = $this->input->get_request_header('language');
		if($language == "en")
		{
			$this->lang->load("message","english");
		}
		else if($language == "ar")
		{
			$this->lang->load("message","arabic");
		}
		else
		{
			$this->lang->load("message","english");
		}
	}


  public function questionnaire_get()
  {
     $arg   = array();
     $where = array('status' => 'Active');
       $result = $this->dynamic_model->getdatafromtable('manage_questionnaire', $where);
       if(!empty($result)){      

        $arg['status']    = 1;
        $arg['error_code'] = REST_Controller::HTTP_OK;
        $arg['error_line']= __line__;
        $arg['message']   = '';//$this->lang->line('thank_msg1');
       $arg['data']      = $result;

       }else{
      
        $arg['status']  = 0;
        $arg['error_code'] = 0;
        $arg['error_line']= __line__;
        $arg['message'] = 'no data found';
       }
       
       echo json_encode($arg);
    
  }


  public function submitQuestionnaire_post()
  {
      $arg   = array();
      $_POST = json_decode(file_get_contents("php://input"), true);
      $userid = $_POST['userid']; 
      $answer_array = $_POST['answer_array']; 
      //$array = array(array('q_id'=>'1', 'q_ans'=>'Yes'),array('q_id'=>'2', 'q_ans'=>'No'),array('q_id'=>'3', 'q_ans'=>'Yes')); 
      // [{"q_id":"1","q_ans":"Yes"},{"q_id":"2","q_ans":"No"},{"q_id":"3","q_ans":"Yes"}]     

      if(!empty($answer_array)){ 
            $transaction_data = array('user_id'=>$userid,
                                   'question_data'=>json_encode( $answer_array),
                                   'created_at'=>time(),
                                    );
            $transaction_id=$this->dynamic_model->insertdata('user_questionnaire',$transaction_data);


         $arg['status']    = 1;
         $arg['error_code'] = REST_Controller::HTTP_OK;
         $arg['error_line']= __line__;
         $arg['message']   = 'Successfully';//$this->lang->line('thank_msg1');
         $arg['data']      = '';

       }else{
      
         $arg['status']  = 0;
         $arg['error_code'] = 0;
         $arg['error_line']= __line__;
         $arg['message'] = 'Error';
       }
       
      echo json_encode($arg);
    
  }

  public function getUserQuestionnaire_post()
  {
      $arg   = array();
      $_POST = json_decode(file_get_contents("php://input"), true);
      $userid = $_POST['userid'];  
      $where = array('user_id' => $userid);
       $result = $this->dynamic_model->getdatafromtable('user_questionnaire', $where);

      if(!empty($result)){ 
         foreach ($result as $key ) {
              # code...
            $question_data_decode= json_decode( $key['question_data'], true);
            foreach ($question_data_decode as $question) {
              $where_question = array('id' => $question['q_id']);
               $result_question = $this->dynamic_model->getdatafromtable('manage_questionnaire', $where_question);
               $single_question[]= array(
                              'question_id'=> $question['q_id'] ,
                              'question_text'=> $result_question[0]['question_title'] ,
                              'question_answer'=> $question['q_ans'] ,
                              'created'=> $key['created_at'] ,
                              );
                 $res['question_array'] = $single_question;
            }
            
         }


         $arg['status']    = 1;
         $arg['error_code'] = REST_Controller::HTTP_OK;
         $arg['error_line']= __line__;
         $arg['message']   = 'Successfully';//$this->lang->line('thank_msg1');
         $arg['data']      = $res;

       }else{
      
         $arg['status']  = 0;
         $arg['error_code'] = 0;
         $arg['error_line']= __line__;
         $arg['message'] = 'Error';
       }
       
      echo json_encode($arg);
    
  }




}