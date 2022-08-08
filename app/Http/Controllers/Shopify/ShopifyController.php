<?php

namespace App\Http\Controllers\Shopify;

use App\Http\Controllers\Controller;
use App\Http\Controllers\JwtAuthController;
use App\Models\Customer;
use App\Models\Store;
use App\Repositories\Shopify\ShopifyRepository;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    public function login(Request $request)
    {
        return $this->shopifyRepository->login($request);
    }
    
    //Get access_token and Login Shop
    public function authen(Request $request)
    {
        return $this->shopifyRepository->authen($request);
    }
}
