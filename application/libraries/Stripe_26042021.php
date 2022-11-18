<?php
// Codeigniter access check, remove it for direct use
if( !defined( 'BASEPATH' ) ) exit( 'No direct script access allowed' );
// Set the server api endpoint and http methods as constants


include('vendor/autoload.php');

class Stripe {
    public function __construct(){
        $this->CI =& get_instance();
        $this->CI->load->config('stripe');
        $strip = array(
            'secret_key' => $this->CI->config->item('secret_key'),
            'public_key' => $this->CI->config->item('public_key')
        );
        \Stripe\Stripe::setApiKey($strip['secret_key']);
    }
	
	public function getaccount($key = ''){
        $msg = '';
        try{
         
            \Stripe\Stripe::setApiKey("sk_test_51GwxuAA1R4jClJNnRTmeYgDjDpOGI7oySYBvrGRWOK4mNotiXJfSj9c2VcYSEGfPrLA8OKVGznysDB2owj5reoap00vHdcr0bO");
            $charge = \Stripe\Account::all(array("limit" => 3));
            // print_r($charge); die;
            $msg = $charge;
        } catch(Exception $e){
            $msg = $e->getMessage();
        }
        return $msg;
    }


    public function getcard($key = ''){
        $msg = '';
        try{
            $charge = \Stripe\Customer::allsources($key, array('object' => 'card'));
            $msg = $charge;
        } catch(Exception $e){
            $msg = $e->getMessage();
        }
        return $msg;
    }


    public function getbank($key = ''){
        $msg = '';
        try{
            $charge = \Stripe\Customer::allsources($key, array('object' => 'bank_account'));
            $msg = $charge;
        } catch(Exception $e){
            $msg = $e->getMessage();
        }
        return $msg;
    }

    public function getbanknew($key = ''){
        $msg = '';
        try{
            $charge = \Stripe\Customer::allsources($key, array('object' => 'bank_account'));
            $msg = $charge;
             $status=1;
        } catch(Exception $e){
             $status=0;
            $msg = $e->getMessage();
        }
        $return= array('status'=> $status,'data'=>$msg);
        return $return;
    }

     public function getbalance(){
        $msg = '';
        try{
            $charge = \Stripe\Balance::retrieve();
            $msg = $charge;
             $status=1;
        } catch(Exception $e){
             $status=0;
            $msg = $e->getMessage();
        }
        $return= array('status'=> $status,'data'=>$msg);
        return $return;
    }

     public function payouttobank($amount,$destination,$currency='usd'){
        $msg = '';
        try{
            $charge = \Stripe\Payout::create(array('amount'=>$amount,'currency'=>$currency,'destination'=>$destination,'method'=>'standard','source_type'=>'card'));
            $msg = $charge;
             $status=1;
        } catch(Exception $e){
             $status=0;
            $msg = $e->getMessage();
        }
        $return= array('status'=> $status,'data'=>$msg);
        return $return;
    }
	
	
	public function addcard($data, $usid){
        $msg = '';
        try{         
        $charge = \Stripe\Customer::createSource($usid, $data); 
            $msg = $charge;
        } catch(Exception $e){
            $msg = $e->getMessage();
        }
        return $msg;
    }

    public function addbank($data, $usid){
        $msg = '';
        try{         
        $charge = \Stripe\Customer::createSource($usid, $data); 
            $msg = $charge;
        } catch(Exception $e){
            $msg = $e->getMessage();
        }
        return $msg;
    }

    public function removecard($cardid, $usid){
        $msg = '';
        try{
            $charge = \Stripe\Customer::deleteSource($usid, $cardid);	
            $msg = $charge;
        } catch(Exception $e){
            $msg = $e->getMessage();
        }
        return $msg;
    }


    public function getToken($data, $usid = ''){
        $msg = '';
        try{
            $charge = \Stripe\Token::create(array(
                "card" => array(
                    "number" => $data['card_number'],
                    "exp_month" => $data['exp_month'],
                    "exp_year" => $data['exp_year'],
                    "cvc" => $data['cvv']
                )
            )); 
            $msg = $charge;
        } catch(Exception $e){
            $msg = $e->getMessage();
        }
        return $msg;
    }
    
	
	public function createcard($token){
        $msg = '';
        try{        
    		$customer = \Stripe\Customer::create(array(
              "description" => "Customer for tradex@gmail.com",
              "source" => $token // obtained with Stripe.js
            ));
    		//$customer->id;
    				//$customer = \Stripe\Customer::retrieve("cus_D8DRzjpd6tN1m9");
    				//$customer->sources->create(array("source" => "tok_amex"));
    		$charge = $customer->sources->data[0];
    		//$charge = \Stripe\Token::retrieve($token);
    		//print_r($charge);
            $msg = $charge;
        } catch(Exception $e){
            $msg = $e->getMessage();
        }
        return $msg;
    }
	
    public function checkoutcustomer($data, $usid){
        $msg = '';
        try{
         
            $charge = \Stripe\Charge::create(array(
                'customer' => $data['token'],
                'amount' => $data['amount'],
                'currency' => 'usd',                
                'description' => 'test message ',
                'metadata' => array('userid'=> $usid),
            ));
            $msg = $charge;
        } catch(Exception $e){
            $msg = $e->getMessage();
        }
        return $msg;
    }

    public function create_customer($data){
        $msg = '';
        try{
            $customer = \Stripe\Customer::create(array(
                "name" => $data['name'],
                "description" => "Customer for ".$data['email'],
                "source" => $data['token'] // obtained with Stripe.js
            ));
            $msg = $customer->id != '' ? $customer->id : "";
        } catch(Exception $e){
            $msg = $e->getMessage();
        }
        return $msg;
    }

	public function checkout($data, $metadata = array(), $type){
        $msg = '';
        try{
            $amt = $data['amount'];
            $name = $data['name'] != '' ? $data['name']  : "";
            $description = $data['description'];

    		if($type == 1){
                $charge = \Stripe\Charge::create(array(
                    'card' => $data['card_id'],
                    'customer' => $data['customer_id'],
                    'amount' => $amt*100,
                    'currency' => 'usd',                
                    'description' => $description,
                    'metadata' => $metadata,
                ));	 
    		} else {
                $stripeToken = $data['token'];
                $customer = \Stripe\Customer::create(array(
                    "name" => $name,
                    "description" => $description,
                    "source" => $stripeToken // obtained with Stripe.js
                ));

                /*$card_info = array(
                    'source' => $stripeToken
                );
                $this->addcard($card_info, $customer->id);*/

                $charge = \Stripe\Charge::create(array(
                    //'source' => $data['token'],
                    'customer' => $customer->id,
                    'amount' => $amt*100,
                    'currency' => 'usd',                
                    'description' => $description,
                    'metadata' => $metadata,
                ));	 
    		}		 
            $res=array('status'=>1,'response'=>$charge,'msg'=>'');

        } catch(Exception $e){
            $msg = $e->getMessage();
            $res=array('status'=>0,'response'=>'','meassge'=>$msg);
        }
        return $res;
    }

	 public function delete($cardid, $usid){
        $msg = '';
        try{         
			$customer = \Stripe\Customer::retrieve($usid);
			$charge = 	$customer->sources->retrieve($cardid)->delete();			
            $msg = $charge;
        } catch(Exception $e){
            $msg = $e->getMessage();
        }
        return $msg;
    }
    public function refund($charge_id='',$amount=''){
        $msg = '';
        try{         
            $refund = \Stripe\Refund::create([
                'charge' => $charge_id,
                'amount' => $amount*100,
            ]);            
            $res=array('status'=>1,'response'=>$refund,'msg'=>'');
        } catch(Exception $e){
            $msg = $e->getMessage();
            $res=array('status'=>0,'response'=>'','meassge'=>$msg);
        }
        return $res;
    }


    public function createStripeAccount($data){
        $msg = '';
        try{
        
            $stripeAccount = \Stripe\Account::create([
              "type" => "custom",
              "country" =>$data['country'],
              "email" => $data['email'],   
              "business_type" => "individual",
               "individual" => [
              //     'address' => [
              //         'city' => 'London',
              //         'line1' => '16a, Little London, Milton Keynes, MK19 6HT ',
              //         'postal_code' => 'MK19 6HT',            
              //     ],
              //     'dob'=>[
              //         "day" => '25',
              //         "month" => '02',
              //         "year" => '1994'
              //     ],
                  "email" => $data['email'],
                  "first_name" => $data['first_name'],
                  "last_name" => $data['last_name'],
                  //"gender" => $data['gender'],
                 // "phone"=> ""
               ],
              'capabilities' => [
                'card_payments' => ['requested' => true],
                'transfers' => ['requested' => true],
              ],

          ]);
        $msg =  $stripeAccount;
        $status = 1;
        } catch(Exception $e){
            $msg = $e->getMessage();
            $status = 0;
        }
         $return= array('status'=> $status,'data'=>$msg);
        return $return;
    }

    public function accountTosAccept($stripeAccount_id){
        $msg = '';
        try{
           $update = \Stripe\Account::update(
              $stripeAccount_id,
                [
                  'tos_acceptance' => [
                    'date' => time(),
                    'ip' => $_SERVER['REMOTE_ADDR'], // Assumes you're not using a proxy
                  ],
                ]
            );
            $msg =  $update;
           } catch(Exception $e){
            $msg = $e->getMessage();
        }
        return $msg;
    }

    public function createExternalAccount($stripeAccount_id, $bank_id){
        $msg = '';
        try{
           $bankAccount = \Stripe\Account::createExternalAccount(
              $stripeAccount_id,['external_account' => $bank_id]
            );
            $msg =  $bankAccount;
            $status=1;
           } catch(Exception $e){
            $msg = $e->getMessage();
            $status=0;
        }
         $return= array('status'=> $status,'data'=>$msg);
        return $return;
    }

    public function createBankToken($data){
        $msg = '';
        try{
        
        $customer = \Stripe\Token::create(
             array("bank_account" =>  array(
                   "country" => $data['country'],
                   "currency" => $data['currency'],
                   "account_holder_name" => $data['account_holder_name'],
                   "account_holder_type" => $data['account_holder_type'],
                   "routing_number" => $data['routing_number'],
                   "account_holder_type" => $data['account_holder_type'],
                   "account_number" => $data['account_number'],
                 )
            )
         );
            $msg = $customer;
            $status=1;
        } catch(Exception $e){
            $msg = $e->getMessage();
            $status=0;
        }
        $return= array('status'=> $status,'data'=>$msg);
        return $return;
    }

     public function checkDuplicateCard($token){
        $msg = '';
        try{         
            $customer = \Stripe\Token::retrieve($token);       
            $msg = $customer;
             $status=1;
        } catch(Exception $e){
            $msg = $e->getMessage();
             $status=0;
        }
        $return= array('status'=> $status,'data'=>$msg);
        return $return;
    }




}