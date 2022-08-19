<?php

namespace App\Http\Controllers\Shopify;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use phpDocumentor\Reflection\Types\This;

use App\Http\Controllers\Controller;
use App\Http\Controllers\LoginController;
use App\Repositories\Shopify\ShopifyRepository;

use App\Models\Customer;
use App\Models\Store;

class ShopifyController extends Controller
{

    protected $shopifyRepository;

    public function __construct(ShopifyRepository $shopifyRepository)
    {
        $this->shopifyRepository = $shopifyRepository;
    }
    public function login(Request $request)
    {
        // info("shopify controller login");
        return $this->shopifyRepository->login($request);
    }


    //Get access_token and Login Shop
    public function authen(Request $request)
    {
        return $this->shopifyRepository->authen($request);
    }

     public function getStore()
    {
        return $this->shopifyRepository->getStore();
    }

    public function store(Request $request)
    {
        $store = $this->shopifyRepository->store($request);
        return response([
            'data' => $store
        ],201);
    }

    public function update(Request $request, $id)
    {
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
