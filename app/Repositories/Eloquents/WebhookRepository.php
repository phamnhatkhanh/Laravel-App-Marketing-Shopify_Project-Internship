<?php

namespace App\Repositories\Eloquents;

use App\Models\Shopify;
use App\Models\Store;
use Session;
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
        Session::put('store_id',$saveData->id);
        $dataPost = [
            'id' => $saveData->id,
            'name_merchant' => $saveData->name,
            'email' => $saveData->email,
            'password' => '123qwe',
            'phone' => $saveData->phone,
            'myshopify_domain' => $saveData->domain,
            'domain' => $saveData->domain,
            'access_token' => $access_token,
            'address' => $saveData->address1,
            'province' => 'New York',
            'city' => $saveData->city,
            'zip' => $saveData->zip,
            'country_name' => $saveData->country_name,
            'created_at' => $created_at,
            'updated_at' => $updated_at,
        ];
        Store::create($dataPost);

        return $dataPost;
    }
}
