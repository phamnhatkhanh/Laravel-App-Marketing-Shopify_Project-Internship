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

    /**
     * If hmac already exists, then login into Store. If don't have hmac download the app from Shopify
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string|void
     */
    public function login(Request $request)
    {
        return $this->shopifyRepository->login($request);
    }

    /**
     * Receive information from login to do other things
     *
     * @param Request $request
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function authen(Request $request)
    {
        return $this->shopifyRepository->authen($request);
    }

    /**
     * Get all Customer display the interface
     *
     * @return resource
     */
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
