<?php

namespace App\Http\Controllers\Shopify;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    function webhook(Request $request)
    {
        $topic = $request->header('X-Shopify-Topic');
        $payload = $request->all();

        switch ($topic) {
            case 'customers/update':
                //Update data Product
                ShopifyController::updateFromShopify($payload);
                break;

            case 'customers/create':
                //Create data Product
                ShopifyController::createFromShopify($payload);
                break;

            case 'customers/delete':
                //Delete data Product
                ShopifyController::deleteFromShopify($payload);
                break;
        }

    }
}
