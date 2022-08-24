<?php

namespace App\Services;

use Twilio\Rest\Client;

class SendBillSMS
{
    /**
     * Setup value to send SMS give shop owner
     *
     * @param $store
     * @return void
     * @throws \Twilio\Exceptions\ConfigurationException
     * @throws \Twilio\Exceptions\TwilioException
     */
    public static function sendBillSMS($store){
        $accountSid = config('twilio.twilio_sid');
        $authToken = config('twilio.twilio_auth_token');
        $twilioPhoneNumber = config('twilio.twilio_number');
        $phone = $store->phone;
        $client = new Client($accountSid, $authToken);

        $client->messages->create(
            $phone,
            [
                "from" => $twilioPhoneNumber,
                "body" => "Thank you payment: $store->name_merchant
                           The amount paid: 50$"
            ]
        );
    }
}
