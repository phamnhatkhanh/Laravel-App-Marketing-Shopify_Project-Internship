<?php

namespace App\Repositories\Shopify;

use App\Jobs\Shopify\UninstallApp;
use GuzzleHttp\Client;
use Illuminate\Http\Request;

use Session;

use App\Jobs\Shopify\CreateCustomer;
use App\Jobs\Shopify\DeleteCustomer;
use App\Jobs\Shopify\UpdateCustomer;


use App\Models\Store;

class WebhookRepository
{

    protected $store;
    public function __construct(){
        $this->store = new Store();
    }

    /**
     * Receive Webhook was shot back from Shopify
     *
     * @param Request $request
     * @return mixed
     */
    function webhook(Request $request){
        $topic = $request->header('X-Shopify-Topic');
        $myshopify_domain = $request->header('X-Shopify-Shop-Domain');
        $payload = $request->all();

        switch ($topic) {
            case 'customers/update':
                //Update data Product
                $this->updateFromShopify($payload);
                break;

            case 'customers/create':
                //Create data Product
                $this->createFromShopify($payload, $myshopify_domain);
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
     * @param string $myshopify_domain
     * @return void
     */
    public function createFromShopify($payload, $myshopify_domain){
       dispatch(new CreateCustomer($payload, $myshopify_domain));
    }

    /**
     * Receive Edit Customer Webhook from Shopify put in Job
     *
     * @param string $payload
     * @return void
     */
    public function updateFromShopify($payload){
        info("WebhookRepository: update customer from shopify");
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
