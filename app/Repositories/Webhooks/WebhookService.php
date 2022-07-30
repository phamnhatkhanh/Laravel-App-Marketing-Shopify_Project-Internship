<?php

namespace App\Repositories\Webhooks;

use GuzzleHttp\Client;

class WebhookService
{
    public static function getAccessToken(string $code, string $domain)
    {
        $client2 = new Client();
        $response = $client2->post(
            "https://" . $domain . "/admin/oauth/access_token",
            [
                'form_params' => [
                    'client_id' => env('SHOPIFY_API_KEY'),
                    'client_secret' => env('SHOPIFY_SECRET_KEY'),
                    'code' => $code,
                ]
            ]
        );

        return json_decode($response->getBody()->getContents());
    }

    //Lấy thông tin đăng nhập
    public static function getDataLogin($shop, $access_token){
        $url = 'https://' . $shop . '/admin/api/2022-07/shop.json?';
        $client = new Client();
        $dataAuthen = $client->request('GET', $url, [
            'headers' => [
                'X-Shopify-Access-Token' => $access_token,
            ]
        ]);
        $getDataStore = (array)json_decode($dataAuthen->getBody());

        return $getDataStore;
    }

    //Lấy thông tin khách hàng từ Shopify về
    public static function createDataCustomer($shop, $access_token)
    {
        $client = new Client();
        $url = 'https://' . $shop . '/admin/api/2022-07/customers.json';
        $resProduct = $client->request('get', $url, [
            'headers' => [
                'X-Shopify-Access-Token' => $access_token,
            ]
        ]);

        return (array)json_decode($resProduct->getBody()->getContents());
    }
}
