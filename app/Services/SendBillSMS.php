<?php

namespace App\Services;

use Twilio\Rest\Client;

class SendBillSMS
{
    public static function sendSMS()
    {
        $account_sid = env('TWILIO_SID');
        $auth_token = env('TWILIO_AUTH_TOKEN');
        $twilio_phone_number = env('TWILIO_NUMBER');
        // $twilio_phone_number = env('TWILIO_NUMBER');
        $phone = $store->phone;
        $client = new Client($account_sid, $auth_token);
    }

    public static function sendBillSMS(){
        $client->messages->create(
            $phone,
            [
                "from" => $twilio_phone_number,
                "body" => "Thank you payment '.$store->name_merchant.' \n
                            "
            ]
        );
    }
}
