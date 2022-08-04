<?php

namespace App\Http\Controllers\Shopify;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Store;
use App\Repositories\Shopify\ShopifyRepository;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use phpDocumentor\Reflection\Types\This;

class ShopifyController extends Controller
{
    protected $productRepository;

//     protected $product;

    public function __construct(ShopifyRepository $shopifyRepository)
    {
        $this->shopifyRepository = $shopifyRepository;
    }

    // Truyền ra ngoài view để nhập tên Shopify
    public function index(Request $request)
    {
        $name = $request->get('name');
        if (!empty($name)) {
            return response([
                'data' => $name,
                'status' => 201,
            ], 201);
        } else {
            return response();
        }
    }

    // Lấy link Shopify
    public function login(Request $request)
    {
        // Store::where()
        //
        // if("co store"){

        // }else {

        // }
        $apiKey = config('shopify.shopify_api_key');
        $scope = 'read_customers,write_customers';
        $shop = $request->shop;

        $redirect_uri = config('shopify.ngrok') . '/api/authen';
        $url = 'https://' . $shop . '/admin/oauth/authorize?client_id=' . $apiKey . '&scope=' . $scope . '&redirect_uri=' . $redirect_uri;
        return redirect($url);
    }

    //Get access_token and Login Shop
    public function authen(Request $request)
    {
        $code = $request->code;
        $shopName = $request->shop;

        //Lấy Access_token gọi về từ WebhookService
        $getAccess_token = $this->getAccessToken($code, $shopName);
        $access_token = $getAccess_token->access_token;

        //Lấy thông tin đăng nhập
        $getDataLogin = $this->getDataLogin($shopName, $access_token);

        $password = $getDataLogin['shop']->myshopify_domain;

        if ($password == "") {
            return false;
        }

        $storeData = array(
            // "id" => $getDataLogin['shop']->id,
            "password" => bcrypt($password),
        );
        Session::put('password', $storeData);

        // Lưu thông tin Shopify vào DB
        if (!Store::find($getDataLogin['shop']->id)) {
            $this->saveDataLogin($getDataLogin, $access_token);
        }
        Session::put('id', $getDataLogin['shop']->id);

        //Lưu thông tin khách hàng ở Shopify lấy về từ SaveDataWebhookService vào DB
        $createCustomer = $this->createDataCustomer($shopName, $access_token);

        foreach ($createCustomer['customers'] as $item) {

            if (!Customer::find($item->id)) {
                $this->saveDataCustomer($createCustomer);
            }
        }

        //Đăng kí CustomerWebhooks thêm, xóa, sửa
        $this->registerCustomerWebhookService($shopName, $access_token);

        return redirect('http://127.0.0.1:8000/api/dashboard');
    }

    public function getAccessToken(string $code, string $domain)
    {
       return $this->shopifyRepository->getAccessToken($code, $domain);
    }

    //Register Webhook Add, Edit, Delete, Uninstall
    public function registerCustomerWebhookService($shop, $access_token){
        $this->shopifyRepository->registerCustomerWebhookService($shop, $access_token);
    }

    //Lấy thông tin đăng nhập
    public function getDataLogin($shop, $access_token)
    {
        return $this->shopifyRepository->getDataLogin($shop, $access_token);
    }

    //Đếm số khách hàng lấy về
    public function countDataCustomer($shop, $access_token)
    {
        return $this->shopifyRepository->countDataCustomer($shop, $access_token);
    }

    //Lấy thông tin khách hàng từ Shopify về
    public function createDataCustomer($shop, $access_token)
    {
        $limit = 250;
        $countCustomer = $this->countDataCustomer($shop, $access_token);
        $ceilRequest = (int)ceil($countCustomer['count'] / $limit);
        $numberRequest = $countCustomer > $limit ? $ceilRequest : 1;

        $param = [
            'fields' => 'id, first_name, last_name, email, phone, country, order_count, total_spent, created_at, updated_at',
            'limit' => $limit,
        ];
        for ($i = 0; $i < $numberRequest; $i++) {
            $client = new Client();
            $url = 'https://' . $shop . '/admin/api/2022-07/customers.json?limit='.$param['limit'];
            $resCustomer = $client->request('get', $url, [
                'headers' => [
                    'X-Shopify-Access-Token' => $access_token,
                    'Content-Type' => 'application/json',
                ],
            ]);

            $headers = $resCustomer->getHeaders();
            $params = $this->setParam($headers, $param);

            $getDataCustomer = (array)json_decode($resCustomer->getBody());
        }

        return $getDataCustomer;
    }

    public function setParam(array $headers, $params)
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

    //Lưu thông tin Shopify
    public function saveDataLogin($res, $access_token)
    {
        return $this->shopifyRepository->saveDataLogin($res, $access_token);
    }

    //Lưu khách hàng vào DB
    public function saveDataCustomer($getCustomer)
    {
        return $this->shopifyRepository->saveDataCustomer($getCustomer);

    }


    // private function generatePasswordFromEmail($email)
    // {
    //     $parsedEmail = explode("@", $email);
    //     if (count($parsedEmail) > 1) {
    //         return $parsedEmail[0];
    //     }
    //     retu
}
