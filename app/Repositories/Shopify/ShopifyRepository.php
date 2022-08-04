<?php
// app/Repositories/Eloquents/ProductRepository.php

namespace App\Repositories\Shopify;

use App\Repositories\Contracts\ShopifyRepositoryInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use App\Models\Customer;
use App\Models\Store;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Session;

class ShopifyRepository implements ShopifyRepositoryInterface
{
    public function index(Request $request){

    }

    public function login(Request $request){

    }

    public function authen(Request $request){

    }

    public function getAccessToken(string $code, string $domain){

    }

    public function getDataLogin($shop, $access_token){

    }

    public function countDataCustomer($shop, $access_token){

    }

    public function createDataCustomer($shop, $access_token){

    }

//    public function setParam(array $headers, $params);

    public function saveDataLogin($res, $access_token){

    }

    public function saveDataCustomer($getCustomer){

    }
}
