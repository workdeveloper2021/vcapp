<?php 
    include('phpseclib-php5-master/phpseclib/Crypt/RSA.php');
    //echo 'hi'; die;
    $merchant_order_data_marchantid = 'RKMDWMMA611F1'; 
    $access_token    = '24a0bbef-8ef3-657b-9449-4b01c158d928'; 
    $clover_base_url = 'https://apisandbox.dev.clover.com/';
    $user_cc_no      = 4111111111111111;
    $user_cc_mo      = 03;
    $user_cc_yr      = 2021;
    $user_cc_cvv     = 123;
    $user_zip        = 94041;
    $amount          = 400;
    $taxAmount       = 0 ;
    $currency        = 'USD';



    /***********************************************************************************************/
    $url_create_order = $clover_base_url.'v3/merchants/'.$merchant_order_data_marchantid.'/orders';    
    $curlOrderPost = json_encode(array("state"=>"open"));
    $ch = curl_init();      
    curl_setopt($ch, CURLOPT_URL, $url_create_order);        
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);        
    curl_setopt($ch, CURLOPT_POST, 1);      
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($curlOrderPost),'Authorization: Bearer '. $access_token));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlOrderPost);    
    $pay_order_res = curl_exec($ch);  
    curl_close($ch); 
    //print_r($pay_order_res);die;
    $pay_order_data = json_decode($pay_order_res);
    $orderId = $pay_order_data->id;
    


   /***********************************************************************************************/
    $url_key = $clover_base_url.'v2/merchant/'.$merchant_order_data_marchantid.'/pay/key';
    $ch = curl_init();      
    curl_setopt($ch, CURLOPT_URL, $url_key);        
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);        
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");       
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: Bearer '. $access_token));   
    $pay_key_res = curl_exec($ch);  
    curl_close($ch); 
    //  print_r($pay_key_res);die;
    $pay_key_data = json_decode($pay_key_res);
    // print_r($pay_key_data); die;
   
    /***********************************************************************************************/


    $rsa = new Crypt_RSA() ;
   //echo "<pre>";print_r($rsa);
    //1. GET to /v2/merchant/{mId}/pay/key To get the encryption information you’ll need for the pay endpoint.
    //2. Encrypt the card information 
    $prefix = $pay_key_data->prefix;
    $modulus = $pay_key_data->modulus;
    $exponent = $pay_key_data->exponent;
    //echo "<hr>";
    
    $first_6 = substr($user_cc_no, 0, 6);
    $last_4 = substr($user_cc_no, -4);
    //2.1. Prepend the card number with the prefix from GET /v2/merchant/{mId}/pay/key.
   
    //2.2. Generate an RSA public key using the modulus and exponent provided byGET /v2/merchant/{mId}/pay/key.
    $m = new Math_BigInteger($modulus);
    $e = new Math_BigInteger($exponent);
  
    $card = $prefix.$user_cc_no; 
    
    $rsa->setHash('sha1');
    $rsa->setMGFHash('sha1');
    $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_OAEP);
    $rsa->loadKey(array('n' => $m, 'e' => $e));

    // $rsa->setPublicKey();
    // $mypublickey = $rsa->getPublicKey();
    //echo "<hr>";
    //2.3. Encrypt the card number and prefix from step 1 with the public key.
    //$stingToEncPublickey = $stingToEnc.$mypublickey; 

    //$ciphertext =   $rsa->encrypt($card);
    //echo "<hr>";
    //3. Base64 encode the resulting encrypted data into a string which you will send to Clover in the “cardEncrypted” field.
    //$stingBase64Encpted = base64_encode($ciphertext);
    /***********************************************************************************************/

      $ciphertext =   $rsa->encrypt($card);
     
      //echo "<hr>";
      //3. Base64 encode the resulting encrypted data into a string which you will send to Clover in the “cardEncrypted” field.
      $stingBase64Encpted = base64_encode($ciphertext);
      /***********************************************************************************************/
     

    /****************** POST DATA TO PAYMENT API ******************/
    $url = $clover_base_url.'v2/merchant/'.$merchant_order_data_marchantid.'/pay';

    $payload = array('orderId'=>$orderId,
                      'taxAmount'=>$taxAmount,
                      //'zip'=>$user_zip,
                      'expMonth'=>$user_cc_mo,
                      //'cvv'=>$user_cc_cvv,
                      'amount'=>$amount,
                      'currency'=>$currency,
                      'last4'=>$last_4,
                      'expYear'=>$user_cc_yr,
                      'first6'=>$first_6,
                      'cardEncrypted'=>$stingBase64Encpted
                    );
    //$curlPost = '{"orderId":"HXRXQJ6T5YHMJ","taxAmount":0,"zip":"","expMonth":"05","cvv":"123","amount":"100.00","currency":"USD","last4":"1111","expYear":"2030","first6":"411111","cardEncrypted":"pJ9QXs4rmBphUHw8gkQGCT9Tz11aasYp8RS2rHiDTXdkA+u0Pew3KVQ9x8Y6071IAI2o2ukdWlU09KNrXnLTDeL\/\/u6GmzH3UmaFMplaNDnj0ciR+b1g6ObwneB7YCo31IImiHL6mNnbLzsq75LQDjq3iahC\/wVnUhTYgbUjGJ1+Z3xSsfyGeWuDBdPPu3o3G6O9CnxG4N\/Oy6R0A0Jx0fLHHSTM8qyfsd5yAEz0Es3NH2WH4zrnXUsSo6fETIND5+V1xi1AQ2ejwSC9lJhoPipUkpy9Is5vQtqnyXVJyj1Tb\/qandt0P1+6dE\/1kv6tn74P2SgU4yW6IHgne3gNdA=="}';


    /* $curlPost = json_encode($payload);

    $ch = curl_init();      
    curl_setopt($ch, CURLOPT_URL, $url);        
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);        
    curl_setopt($ch, CURLOPT_POST, 1);      
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($curlPost),'Authorization: Bearer '. $access_token));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);    
    $data = curl_exec($ch);  
    curl_close($ch); 
    print_r($data);//die;*/

    /****************** POST DATA TO PAYMENT API ******************/


   $url2 = 'https://scl-sandbox.dev.clover.com/v1/charges/';

    $curlPost = '{"amount":1800,"currency":"usd","source":"clv_1TSTSFwm1CHQ6yEGvmAT4LP2"}';

    $ch = curl_init();      
    curl_setopt($ch, CURLOPT_URL, $url2);        
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);        
    curl_setopt($ch, CURLOPT_POST, 1);      
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($curlPost),'Authorization: Bearer '. $access_token));//'idempotency-key:af2bbe3c4b4dd3682793cc09155a9a7a'
    curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);    
    $data = curl_exec($ch);  
    curl_close($ch); 
    print_r($data);die;


/*curl --request POST \
  --url 'https://scl-sandbox.dev.clover.com/v1/charges' \
  --header 'accept: application/json' \
  --header 'authorization: Bearer {access_token}' \
  --header 'idempotency-key {uuid4_key}' \
  --header 'content-type: application/json' \
  --data '{"amount":1800,
  "currency":"usd",
  "source":"{token}"}'*/


  /*

  //https://docs.clover.com/docs/ecommerce-accepting-payments
  
{ "id" : "R48Q7GPMY2FQY", "amount" : 1800, "amount_refunded" : 0, "currency" : "usd", "created" : 1616661109846, "captured" : true, "ref_num" : "108400500020", "auth_code" : "OK2809", "outcome" : { "network_status" : "approved_by_network", "type" : "authorized" }, "paid" : true, "status" : "succeeded", "source" : { "id" : "clv_1TSTS1iWemARMj4AXHxHikLV", "brand" : "AMEX", "cvc_check" : "unchecked", "exp_month" : "11", "exp_year" : "2025", "first6" : "378282", "last4" : "0005" } }

{"message":"400 Bad Request","error":{"code":"token_already_used","message":"You cannot use a clover token more than once unless it is marked as multipay."}}
  */