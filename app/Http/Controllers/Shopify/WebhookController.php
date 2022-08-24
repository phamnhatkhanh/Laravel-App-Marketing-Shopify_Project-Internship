<?php

namespace App\Http\Controllers\Shopify;


use Illuminate\Http\Request;

use App\Http\Controllers\Controller;

use App\Repositories\Shopify\WebhookRepository;

class WebhookController extends Controller
{
    protected $webHookRepository;

    public function __construct(WebhookRepository $webHookRepository){
        $this->webHookRepository= $webHookRepository;
    }

    /**
     * Receive Webhook was shot back from Shopify
     *
     * @param Request $request
     * @return void
     */
    function webhook(Request $request)
    {
        $this->webHookRepository->webhook($request);
    }

    /**
     * Receive Add Customer Webhook from Shopify put in Job
     *
     * @param string $payload
     * @param string $myShopifyDomain
     * @return void
     */
    public function createFromShopify($payload,$myshopify_domain)
    {
        $this->webHookRepository->createFromShopify($payload, $myshopify_domain);
    }

    /**
     * Receive Edit Customer Webhook from Shopify put in Job
     *
     * @param string $payload
     * @return void
     */
    public function updateFromShopify($payload)
    {
        $this->webHookRepository->updateFromShopify($payload);
    }

    /**
     * Receive Delete Customer Webhook from Shopify put in Job
     *
     * @param string $payload
     * @return void
     */
    public function deleteFromShopify($payload)
    {
        $this->webHookRepository->deleteFromShopify($payload);
    }

}

