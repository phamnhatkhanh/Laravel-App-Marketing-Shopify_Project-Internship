<?php

namespace App\Services\Shopify;

use GuzzleHttp\Client;

class ShopifyService
{
    /**
     * Get access_token from the Shopify
     *
     * @param string $code
     * @param string $domain
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function getAccessToken(string $code, string $domain)
    {
        info("ShopifyRepository getAccessToken: get token");
        $client = new Client();
        $request = $client->post(
            "https://" . $domain . "/admin/oauth/access_token",
            [
                'form_params' => [
                    'client_id' => env('SHOPIFY_API_KEY'),
                    'client_secret' => env('SHOPIFY_SECRET_KEY'),
                    'code' => $code,
                ]
            ]
        );
        $response = json_decode($request->getBody()->getContents());

        return $response;
    }

    /**
     * Retrieves a count of existing webhook subscriptions
     *
     * @param $shop
     * @param $access_token
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
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

    /**
     * Create a new webhook subscription
     *
     * @param $shop
     * @param $access_token
     * @param $getWebhook
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
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

    /**
     * Retrieve a count of Customers
     *
     * @param $shop
     * @param $access_token
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public static function countDataCustomer($shop, $access_token)
    {
        $client = new Client();
        $url = 'https://' . $shop . '/admin/api/2022-07/customers/count.json';
        $request = $client->request('get', $url, [
            'headers' => [
                'X-Shopify-Access-Token' => $access_token,
            ]
        ]);
        $countCustomer = (array)json_decode($request->getBody());

        return $countCustomer;
    }

    /**
     * If quantity Customer exceed one save will automatically press rel="next" to go through the page and continue save
     *
     * @param array $headers
     * @param $params
     * @return array|mixed
     */
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
