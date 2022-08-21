<?php

namespace App\Repositories\Contracts;

use Illuminate\Http\Request;

interface ShopifyRepositoryInterface
{
    // public function index(Request $request);
    /**
     * If hmac already exists, then login into Store. If don't have hmac download the app from Shopify
     *
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request);

    /**
     * Receive information from login to do other things
     *
     * @param Request $request
     * @return mixed
     */
    public function authen(Request $request);

//    public function getAccessToken(string $code, string $domain);
//    public static function registerCustomerWebhookService($shop, $access_token);
//    public function getDataLogin($shop, $access_token);
//     public function countDataCustomer($shop, $access_token);
//     public function createDataCustomer($shop, $access_token,$store_id);
    //  public function createDataCustomer($shop, $access_token);
//     public function setParam(array $headers, $params);
//     public function saveDataStore($res, $access_token);
//     public function saveDataCustomer($getCustomer);
}


