<?php

namespace App\Repositories\Eloquents;

use App\Models\Shopify;
use App\Models\Store;
use Illuminate\Support\Facades\Session;

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

        $password = Session::get('password');

        $dataPost = [
            'id' => $saveData->id,
            'name_merchant' => $saveData->name,
            'domain' => $saveData->domain,
            'myshopify_domain' => $saveData->myshopify_domain,
            'email' => $saveData->email,
            'phone' => $saveData->phone,
            'password' => $password['password'],
            'access_token' => $access_token,
            'province' => $saveData->province,
            'address' => $saveData->address1,
            'zip' => $saveData->zip,
            'city' => $saveData->city,
            'country_name' => $saveData->country_name,
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ];
        Store::create($dataPost);

        return $dataPost;
    }
}
