<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('strhlp_create_customer')) {
        function strhlp_create_customer($data)
        {
                $CI = &get_instance();
                $CI->load->model('dynamic_model');

                $checkCustomer = $CI->dynamic_model->getdatafromtable(
                        'user',
                        array(
                                'id' => $data['user_id']
                        ),
                        'customer_id'
                );

                $customerId = $checkCustomer[0]['customer_id'];

                if (empty($customerId)) {

                        $CI->load->library('Stripe');

                        $info = array(
                                "token" => $data['token'],
                                "name"  => $data['name'],
                                "email" => $data['email']
                        );

                        $userInfo = $CI->stripe->create_customer($info);

                        if (!empty($userInfo)) {

                                $CI->dynamic_model->updatedata(
                                        'user',
                                        array(
                                                'customer_id'   =>      $userInfo
                                        ),
                                        $data['user_id']
                                );

                                return $userInfo;
                        } else {
                                return false;
                        }
                } else {
                        return $customerId;
                }
        }
}

if (!function_exists('strhlp_add_card')) {
        function strhlp_add_card($data)
        {
                $CI = &get_instance();
                $CI->load->library('Stripe');

                $requestArray = array(
                        'source' => $data['token']
                );
                $cardinfo = $CI->stripe->addcard($requestArray, $data['customer_id']);
                if (!empty($cardinfo)) {
                        return $cardinfo;
                } else {
                        return false;
                }
        }
}


if (!function_exists('strhlp_get_token')) {
        function strhlp_get_token($collection)
        {
                $CI = &get_instance();
                $CI->load->library('Stripe');

                $data = array(
                        'card_number' => $collection['card_number'],
                        'exp_month' => $collection['expiry_month'],
                        'exp_year' => $collection['expiry_year'],
                        'cvv' => $collection['cvv_no']
                );
                $token = $CI->stripe->getToken($data);
                return $token;
        }
}

if (!function_exists('strhlp_get_card_list')) {
        function strhlp_get_card_list($customer_id)
        {
                $CI = &get_instance();
                $CI->load->library('Stripe');

                $card_data = $CI->stripe->getcard($customer_id);
                if (!empty($card_data)) {
                        $data = $card_data['data'];
                        $resp = array();
                        foreach ($data as $d) {
                                if($d['name']==null)
                                {
                                    $name = '';    
                                }
                                else
                                {
                                   $name = $d['name'];     
                                }
                                array_push(
                                        $resp,
                                        array(
                                                'card_id'               =>      $d['id'],
                                                'brand'                 =>      $d['brand'],
                                                'exp_month'             =>      $d['exp_month'],
                                                'exp_year'             =>       $d['exp_year'],
                                                'card_number'           =>      'XXXXXXXXXXXX'.$d['last4'],
                                                'name'                  =>      $name,
                                        )

                                );
                        }
                        return $resp;
                } else {
                        return false;
                }
        }
}

if (!function_exists('strhlp_delete_card')) {
        function strhlp_delete_card($card_id, $customer_id)
        {
                $CI = &get_instance();
                $CI->load->library('Stripe');
                $cardinfo = $CI->stripe->removecard($card_id, $customer_id);
                if (!empty($cardinfo)) {
                        return true;
                } else {
                        return false;
                }
        }
}

if (!function_exists('strhlp_checkout')) {
        function strhlp_checkout($data, $metadata = array(), $type)
        {
                $CI = &get_instance();
                $CI->load->library('Stripe');
                $cardinfo = $CI->stripe->checkout($data, $metadata, $type);
                if (!empty($cardinfo)) {
                        return $cardinfo;
                } else {
                        return false;
                }
        }
}
