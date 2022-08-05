<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface WebHookRepositoryInterface
{
    function webhook(Request $request);
    public function registerCustomerWebhookService($shop, $access_token);
    public function createFromShopify($payload, $myshopify_domain);
    public function updateFromShopify($payload);
    public function deleteFromShopify($payload);
}


