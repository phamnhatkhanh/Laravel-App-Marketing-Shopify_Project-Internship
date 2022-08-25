<?php

namespace App\Repositories\Shopify;

use Session;
use GuzzleHttp\Client;
use Illuminate\Http\Request;


use App\Jobs\Shopify\CreateCustomer;
use App\Jobs\Shopify\DeleteCustomer;
use App\Jobs\Shopify\UpdateCustomer;
use App\Jobs\Shopify\UninstallApp;



class WebhookRepository
{
    public function __construct(){

    }

    /**
     * Receive Webhook was shot back from Shopify
     *
     * @param Request $request
     * @return mixed
     */
    function webhook(Request $request){
        $topic = $request->header('X-Shopify-Topic');
        $myShopifyDomain = $request->header('X-Shopify-Shop-Domain');
        $payload = $request->all();

        switch ($topic) {
            case 'customers/update':
                //Update data Product
                $this->updateFromShopify($payload);
                break;

            case 'customers/create':
                //Create data Product
                $this->createFromShopify($payload, $myShopifyDomain);
                break;

            case 'customers/delete':
                //Delete data Product
                $this->deleteFromShopify($payload);

            case 'app/uninstalled':
                //Unistall App
                $this->uninstallAppFromShopify($payload);
        }
    }

    /**
     * Receive Add Customer Webhook from Shopify put in Job
     *
     * @param string $payload
     * @param string $myShopifyDomain
     * @return void
     */
    public function createFromShopify($payload, $myShopifyDomain){
       dispatch(new CreateCustomer($payload, $myShopifyDomain));
    }

    /**
     * Receive Edit Customer Webhook from Shopify put in Job
     *
     * @param string $payload
     * @return void
     */
    public function updateFromShopify($payload){
        dispatch(new UpdateCustomer($payload));
    }

    /**
     * Receive Delete Customer Webhook from Shopify put in Job
     *
     * @param string $payload
     * @return void
     */
    public function deleteFromShopify($payload){
        dispatch(new DeleteCustomer($payload));
    }

    /**
     * Receive uninstall App Webhook from Shopify put in Job
     *
     * @param string $payload
     * @return void
     */
    public function uninstallAppFromShopify($payload){
        dispatch(new UninstallApp($payload));
    }
}
