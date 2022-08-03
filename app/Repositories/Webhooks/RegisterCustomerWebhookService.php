<?php

namespace App\Repositories\Webhooks;

use GuzzleHttp\Client;

class RegisterCustomerWebhookService
{
    //Register Webhook Add, Edit, Delete, Uninstall
    public static function registerCustomerWebhookService($shop, $access_token)
    {
        $topic_access = [
            'customers/create',
            'customers/update',
            'customers/delete',
            'app/uninstalled',
        ];
        $client = new Client();
        $url = 'https://' . $shop . '/admin/api/2022-07/webhooks.json';
        foreach ($topic_access as $topic){
            $resShop = $client->request('post', $url, [
                'headers' => [
                    'X-Shopify-Access-Token' => $access_token,
                ],
                'form_params' => [
                    'webhook' => [
                        'topic' => $topic,
                        'format' => 'json',
                        'address' => config('shopify.ngrok').'/api/shopify/webhook',
                    ],
                ]
            ]);
        }
    }
}
