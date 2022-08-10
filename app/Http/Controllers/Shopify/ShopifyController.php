<?php

namespace App\Http\Controllers\Shopify;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use phpDocumentor\Reflection\Types\This;

use App\Http\Controllers\Controller;
use App\Http\Controllers\JwtAuthController;
use App\Repositories\Shopify\ShopifyRepository;

use App\Models\Customer;
use App\Models\Store;

class ShopifyController extends Controller
{

    protected $shopifyRepository;

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

     public function getStore()
    {

        // dd("skfbsjfhds");
        return $this->shopifyRepository->getStore();
    }



    public function store(Request $request)
    {
        // dd("store prodcut");
        $store = $this->shopifyRepository->store($request);
        return response([
            'data' => $store
            // 'data' => new customerResource($store)
        ],201);
    }

    public function update(Request $request, $id)
    {

        // dd("upate proe");
        $store = $this->shopifyRepository->update($request, $id);

        return response([
            'data' => $store
        ],201);
    }
    public function destroy($id)
    {

        $store = $this->shopifyRepository->destroy( $id);
        return response([
            'data' => $store,
            'mess' => "dleete customer done"
        ],201);

    }

    public function show($id)
    {

    }
}
