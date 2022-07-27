<?php

namespace App\Repositories\Webhooks;

use GuzzleHttp\Client;

class RegisterCustomerWebhookService
{
    //Đăng kí Webhook
    public static function registerProductWebhookService($shop, $access_token){
        self::createCustomerWebhook($shop, $access_token);
        self::updateCustomerWebhook($shop, $access_token);
        self::deleteCustomerWebhook($shop, $access_token);
    }

    //Đăng kí Webhooks để lấy khách hàng đã được sửa trên Shopify về
    public static function createCustomerWebhook($shop, $access_token)
    {
        $client = new Client();
        $url = 'https://' . $shop . '/admin/api/2022-07/webhooks.json';
        $resShop = $client->request('post', $url, [
            'headers' => [
                'X-Shopify-Access-Token' => $access_token,
            ],
            'form_params' => [
                'webhook' => [
                    'topic' => 'customers/create',
                    'format' => 'json',
                    'address' => config('shopify.ngrok').'/api/shopify/webhook',
                ],
            ]
        ]);
    }

    //Đăng kí Webhooks để lấy những khách hàng đã được tạo trên Shopify về
    public static function updateCustomerWebhook($shop, $access_token)
    {
        $client = new Client();
        $url = 'https://' . $shop . '/admin/api/2022-07/webhooks.json';
        $resShop = $client->request('post', $url, [
            'headers' => [
                'X-Shopify-Access-Token' => $access_token,
            ],
            'form_params' => [
                'webhook' => [
                    'topic' => 'customers/update',
                    'format' => 'json',
                    'address' => config('shopify.ngrok').'/api/shopify/webhook',
                ],
            ]
        ]);
    }

    //Đăng kí Webhooks để xóa những khách hàng đã được tạo trên Shopify về
    public static function deleteCustomerWebhook($shop, $access_token)
    {
        $client = new Client();
        $url = 'https://' . $shop . '/admin/api/2022-07/webhooks.json';

        $client->request('POST', $url, [
            'headers' => [
                'X-Shopify-Access-Token' => $access_token,
            ],
            'form_params' => [
                'webhook' => [
                    'topic' => 'customers/delete',
                    'format' => 'json',
                    'address' => config('shopify.ngrok').'/api/shopify/webhook',
                ],
            ]
        ]);
    }
}
