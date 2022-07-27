<?php

namespace App\Repositories\Eloquents;

use App\Models\Shopify;

class WebhookRepository
{
    //Lưu thông tin Shopify
    public static function saveDataLogin($res, $access_token)
    {
        $saveData = $res['shop'];

        $findCreateAT = array('T', '+07:00');
        $replaceCreateAT = array(' ', '');
        $findUpdateAT = array('T', '+07:00');
        $replaceUpdateAT = array(' ', '');

        $created_at = str_replace($findCreateAT, $replaceCreateAT, $saveData->created_at);
        $updated_at = str_replace($findUpdateAT, $replaceUpdateAT, $saveData->updated_at);

        $dataPost = [
            'id' => $saveData->id,
            'name_merchant' => $saveData->name,
            'domain' => $saveData->domain,
            'myshopify_domain' => $saveData->domain,
            'email' => $saveData->email,
            'phone' => $saveData->phone,
            'access_token' => $access_token,
            'plan_name' => $saveData->plan_name,
            'address' => $saveData->address1,
            'zip' => $saveData->zip,
            'city' => $saveData->city,
            'country_name' => $saveData->country_name,
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ];
        Shopify::create($dataPost);

        return $dataPost;
    }
}
