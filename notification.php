<?php

function sendNotification($type, $sendInfo) {
        
        $message    = $sendInfo['message'];
        $token      = $sendInfo['token'];
        $title      = $sendInfo['title'];
        
        $notification_setting = $sendInfo['notification_setting'];

        
        if ($type == 1) {
                $apiKey = 'AAAA5LuRAlM:APA91bGW0LTvc9tHLX7XmfJCh5pJM9brxt1NSfKVp8mzes4lqSpt26qp3ssMNZiCLHXLRtdNOiU_XePZxAMqO2PWnHoyb2m11gVYwhYeL6By1n5NG9wlL6Qu0r5hl37yyFoCHxFRxmRg';
        } else {
            $apiKey = 'AAAA5LuRAlM:APA91bGW0LTvc9tHLX7XmfJCh5pJM9brxt1NSfKVp8mzes4lqSpt26qp3ssMNZiCLHXLRtdNOiU_XePZxAMqO2PWnHoyb2m11gVYwhYeL6By1n5NG9wlL6Qu0r5hl37yyFoCHxFRxmRg';
        }

        $icon = 'https://static.pexels.com/photos/4825/red-love-romantic-flowers.jpg';

        $msg = array(
            'body' => $message,	
            'notification_setting' => $notification_setting,	
            'icon' => 'icon',	
            'sound' => 'default',	
            'click_action' => "FCM_PLUGIN_ACTIVITY",	
        );

        $fields = array(
            'to' => $token,
            'notification' => $msg,
            'data' => $msg,
            'content_available' => true,
            'priority' => 'high',
        );

        $headers = array(
            'Authorization: key=' . $apiKey,
            'Content-Type: application/json',
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $response = curl_exec($ch);
        echo '<pre />'; print_r($response);
        curl_close($ch);
        return $response;

    }

        // sendNotification(1, array(
        //     'title'     => 'Rahul', 
        //     'message'   => 'Content', 
        //     'token'     => 'cjB4A0d_QvGDQfoWXfaJpR:APA91bHZ8Q1GWHSDvhiLfSs-hh1-ZgrLHGBQ8nDz-pk15UV65D18zZBI7O1bDGhjPMjOE-fDkQ3fh1Fd5cMj6D0bZWsLGNh6B6jkMtxm6Nk8nj7-gZLEySHtiZe578ocyvJJR-cH2Y7I', 
        //     'notification_setting' => ''
        // ));
        sendNotification(1, array(
            'title'     => 'Rahul2', 
            'message'   => 'Content', 
            'token'     => 'fVSeMOA7STqJYCEK1fcaC-:APA91bEuk-pxhmV0ctcmdaJ-M8Pk3iKKJPFuzAs8ZHKHyOuk6U7A8fYWWDH1o-NZdVDNAfq5DbpXccpsWtKwcxP3OgIgy4V--oDss74CvIuIYfJ3Z1dqGACRiSTozQOc_2yrVGYVi1GN', 
            'notification_setting' => ''
        ));



        echo '65sfsdf'; die;

?>