<?php

namespace App\Services\Shopify;

use GuzzleHttp\Client;

class ShopifyService
{
    public static function getAccessToken(string $code, string $domain)
    {
        info("ShopifyRepository getAccessToken: get token");
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

    public static function getTopicWebhook($shop, $access_token)
    {
        info('Get all Topic Webhook Register');
        $client = new Client();
        $url = 'https://' . $shop . '/admin/api/2022-07/webhooks.json';
        $request = $client->request('get', $url, [
            'headers' => [
                'X-Shopify-Access-Token' => $access_token
            ]
        ]);
        $response = (array)json_decode($request->getBody(), true);

        return $response;
    }

    public static function registerCustomerWebhookService($shop, $access_token, $getWebhook)
    {

        info("ShopifyRepository registerCustomerWebhookService: access persmission");
        $topic_access = [
            'customers/create',
            'customers/update',
            'customers/delete',
            'app/uninstalled',
        ];

        foreach ($topic_access as $topic) {
            $client = new Client();
            $url = 'https://' . $shop . '/admin/api/2022-07/webhooks.json';
            $request = $client->request('post', $url, [
                'headers' => [
                    'X-Shopify-Access-Token' => $access_token,
                ],
                'form_params' => [
                    'webhook' => [
                        'topic' => $topic,
                        'format' => 'json',
                        'address' => config('shopify.ngrok') . '/api/shopify/webhook',
                    ],
                ]
            ]);
        }
    }

    public static function countDataCustomer($shop, $access_token)
    {
        $client = new Client();
        $url = 'https://' . $shop . '/admin/api/2022-07/customers/count.json';
        $resCustomer = $client->request('get', $url, [
            'headers' => [
                'X-Shopify-Access-Token' => $access_token,
            ]
        ]);
        $countCustomer = (array)json_decode($resCustomer->getBody());

        return $countCustomer;
    }

    public static function setParam(array $headers, $params)
    {
        $links = explode(',', @$headers['Link'][0]);
        $nextPage = $prevPage = null;
        foreach ($links as $link) {
            if (strpos($link, 'rel="next"')) {
                $nextPage = $link;
            }
            if (strpos($link, 'rel="previous"')) {
                $prevPage = $link;
            }
        }

        $params = [];

        if ($nextPage) {
            preg_match('~<(.*?)>~', $nextPage, $next);
            $url_components = parse_url($next[1]);
            parse_str($url_components['query'], $parseStr);
            $params = $parseStr;
            $params['next_cursor'] = $parseStr['page_info'];
        }

        if ($prevPage) {
            preg_match('~<(.*?)>~', $prevPage, $next);
            $url_components = parse_url($next[1]);
            parse_str($url_components['query'], $parseStr);
            $params = !empty($params) ? $params : $parseStr;
            $params['prev_cursor'] = $parseStr['page_info'];
        }

        return $params;
    }
}
