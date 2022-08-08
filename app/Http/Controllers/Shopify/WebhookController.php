<?php

namespace App\Http\Controllers\Shopify;

use App\Http\Controllers\Controller;
use App\Jobs\CreateCustomer;
use App\Jobs\DeleteCustomer;
use App\Jobs\UpdateCustomer;
use App\Repositories\Shopify\WebhookRepository;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    protected $webHookRepository;
    // protected $product;

    public function __construct(WebhookRepository $webHookRepository){
        $this->webHookRepository= $webHookRepository;
    }

    function webhook(Request $request)
    {
        $this->webHookRepository->webhook($request);
    }

    //Đưa vào Queue để lưu những khách hàng đã được tạo trên Shopify vào DB
    public function createFromShopify($payload,$myshopify_domain)
    {
        $this->webHookRepository->createFromShopify($payload, $myshopify_domain);
    }

    //Đưa vào Queue để tự động lưu những khách hàng đã được sửa trên Shopify vào DB
    public function updateFromShopify($payload)
    {
        $this->webHookRepository->updateFromShopify($payload);
    }

    //Đưa vào Queue để tự động xóa khách hàng đã xóa trên Shopify trong DB
    public function deleteFromShopify($payload)
    {
        $this->webHookRepository->deleteFromShopify($payload);
    }

}

