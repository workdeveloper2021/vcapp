<?php
if( !defined( 'BASEPATH' ) ) exit( 'No direct script access allowed' );
include('bombora/src/Beanstream/Gateway.php');

class Bomborapay {
    
    //Warrior Payments CAD
    private $merchant_id = '377010000';
    private $api_key = '358F70BB935d44e8b44bC855BFeE6451';

    //Warrior Payments USA
    private $merchant_id_usa = '377030000';
    private $api_key_usa = 'feee05dB05F44D54838F20422f170360';

  


   //1cAc137274064e6598A4f4E412D33A98
   private  $api_version = 'v1'; //default
   private $platform = 'api'; //default (or use 'tls12-api' for the TLS 1.2-Only endpoint)
   
    public function __construct(){
        $this->CI =& get_instance();     
    }
    //make a credit card payment
    public function makePayment($payment_data){
     //init new Beanstream Gateway object
     $beanstream = new \Beanstream\Gateway($this->merchantId,$this->apiKey,$this->platform,$this->api_version);
    //example payment function test vars
    $transaction_id = ''; //enter a transaction id to use in below functions
    $complete = TRUE;
        try { 
            $result = $beanstream->payments()->makePayment($payment_data);
            $transaction_id = $result['id']; 
            //display result
            is_null($result)?:print_r($result);
        }catch(\Beanstream\Exception $e){
            /*
             * Handle transaction error, $e->code can be checked for a
             * specific error, e.g. 211 corresponds to transaction being
             * DECLINED, 314 - to missing or invalid payment information
             * etc.
             */
            //return $response=$e->getMessage();
            print_r($e);          
        }

    }
    //make a credit card payment
	public function makeCardPayment($payment_data,$complete=''){
     $beanstream = new \Beanstream\Gateway($this->merchant_id,$this->api_key,$this->platform,$this->api_version);
    //example payment function test vars
    $transaction_id = ''; //enter a transaction id to use in below functions
    $complete = TRUE;
        try { 
            $result = $beanstream->payments()->makeCardPayment($payment_data, $complete);
            $transaction_id = $result['id']; 
            //display result
            is_null($result)?:print_r($result);
        }catch(\Beanstream\Exception $e){
            /*
             * Handle transaction error, $e->code can be checked for a
             * specific error, e.g. 211 corresponds to transaction being
             * DECLINED, 314 - to missing or invalid payment information
             * etc.
             */
             
             print_r($e);
             
        }

    }
    //make a credit card payment
    public function makeTokenPayment($payment_data,$complete=''){
     $beanstream = new \Beanstream\Gateway($this->merchant_id,$this->api_key,$this->platform,$this->api_version);
    //example payment function test vars
    $transaction_id = ''; //enter a transaction id to use in below functions
    $complete = TRUE;
        try { 
            $result = $beanstream->payments()->makeLegatoTokenPayment($token, $legato_payment_data, $complete);
            $transaction_id = $result['id']; 
            //display result
            is_null($result)?:print_r($result);
        }catch(\Beanstream\Exception $e){
            /*
             * Handle transaction error, $e->code can be checked for a
             * specific error, e.g. 211 corresponds to transaction being
             * DECLINED, 314 - to missing or invalid payment information
             * etc.
             */
             
             print_r($e);
             
        }

    }
 //make a credit card payment
    public function getTokenTest($legato_token_data){
     $beanstream = new \Beanstream\Gateway($this->merchant_id,$this->api_key,$this->platform,$this->api_version);

        try { 
            //simulate legato token payment (SHOULD NEVER BE CALLED IN PRODUCTION)
           return $token = $beanstream->payments()->getTokenTest($legato_token_data);
            
        }catch(\Beanstream\Exception $e){
            /*
             * Handle transaction error, $e->code can be checked for a
             * specific error, e.g. 211 corresponds to transaction being
             * DECLINED, 314 - to missing or invalid payment information
             * etc.
             */
             
             print_r($e);
             
        }

    }

    //Method curl for bombora paymnets
     function payment_checkout($http_method = 'POST',$url, $data = NULL,$submerchant_id='',$marchant_id_type='')
    {
       // echo $submerchant_id.'--'.$marchant_id_type; die;
    if($marchant_id_type == '1'){
        $auth=base64_encode($this->merchant_id_usa.':'.$this->api_key_usa);
    }else{
       $auth=base64_encode($this->merchant_id.':'.$this->api_key); 
    }
   



        //check to see if we have curl installed on the server 
        if ( ! extension_loaded('curl')) {
            //no curl
            throw new ConnectorException('The cURL extension is required', 0);
        }
        //init the curl request
        //via endpoint to curl
        $req = curl_init($url); 
        //set request headers with encoded auth
        curl_setopt($req, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Passcode '.$auth,
            'Sub-Merchant-Id: '.$submerchant_id,
        ));
        //set other curl options        
        curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($req, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($req, CURLOPT_TIMEOUT, 30); 
        //set http method
        //default to GET if data is null
        //default to POST if data is not null
        if (is_null($http_method)) {
            if (is_null($data)) {
                $http_method = 'GET';
            } else {
                $http_method = 'POST';
            }
        }
        //set http method in curl
        curl_setopt($req, CURLOPT_CUSTOMREQUEST, $http_method);
        
        //make sure incoming payload is good to go, set it
        if ( ! is_null($data)) {
            curl_setopt($req, CURLOPT_POSTFIELDS, json_encode($data));
        }        
        //execute curl request
        $raw = curl_exec($req);
        if (false === $raw) { //make sure we got something back
            throw new ConnectorException(curl_error($req), -curl_errno($req));
        }
        //decode the result
        $res = json_decode($raw, true);
       /* print_r($res);

         echo '<pre/>';
    print_r($data);
    echo $marchant_id_type;
    echo $submerchant_id;
     die;*/
        return $res;
    }


     function profile_create($http_method = 'POST', $url, $data = NULL,  $submerchant_id='377010002',$marchant_id_type='')
    {
        //$auth='Mzc3MDEwMDAwOkM5MDEyNTg1MDkwNjQ2QzI5REIwNjJCODZEMkQ5M0Mz';
       
       if($marchant_id_type == '1'){
            $mid = '377030000';
            $passcode = '6ACAA5EB4D9B487B9A85DBF0700D2659';
            $auth = base64_encode($mid.':'.$passcode);
        }else{
            $mid = '377010000';
            $passcode = 'C9012585090646C29DB062B86D2D93C3';
            $auth = base64_encode($mid.':'.$passcode);
        }   

        $req = curl_init($url); 
        curl_setopt($req, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Passcode '.$auth,
            'Sub-Merchant-Id: '.$submerchant_id,
        ));
        curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($req, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($req, CURLOPT_TIMEOUT, 30); 
        if (is_null($http_method)) {
            if (is_null($data)) {
                $http_method = $http_method ? $http_method : 'GET';
            } else {
                $http_method = $http_method ? $http_method : 'POST';
            }
        }
        curl_setopt($req, CURLOPT_CUSTOMREQUEST, $http_method);
        if ( ! is_null($data)) {
            curl_setopt($req, CURLOPT_POSTFIELDS, json_encode($data));
        }        
        //execute curl request
        $raw = curl_exec($req);
        if (false === $raw) { //make sure we got something back
            throw new ConnectorException(curl_error($req), -curl_errno($req));
        }
        //decode the result
        $res = json_decode($raw, true);
        return $res;
    }

    function profile_create_test($http_method = 'POST',$url, $data = NULL,$submerchant_id='')
    {
       $auth='MzAwMjExOTUwOjNEMzg4RUVlODYyOTRmMkJiRjViREY1M0U2NDUzMDI0';
      // $auth='Mzc3MDMwMDAwOjZBQ0FBNUVCNEQ5QjQ4N0I5QTg1REJGMDcwMEQyNjU5';
        $req = curl_init($url); 
        curl_setopt($req, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Passcode '.$auth,
        ));
        curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($req, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($req, CURLOPT_TIMEOUT, 30); 
        if (is_null($http_method)) {
            if (is_null($data)) {
                $http_method = $http_method ? $http_method : 'GET';
            } else {
                $http_method = $http_method ? $http_method : 'POST';
            }
        }
        curl_setopt($req, CURLOPT_CUSTOMREQUEST, $http_method);
        if ( ! is_null($data)) {
            curl_setopt($req, CURLOPT_POSTFIELDS, json_encode($data));
        }        
        //execute curl request
        $raw = curl_exec($req);
        if (false === $raw) { //make sure we got something back
            throw new ConnectorException(curl_error($req), -curl_errno($req));
        }
        //decode the result
        $res = json_decode($raw, true);
        return $res;
    }

    
	

}