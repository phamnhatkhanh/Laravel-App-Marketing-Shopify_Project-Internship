<?php

namespace App\Repositories\Contracts;

use Illuminate\Http\Request;

interface ShopifyRepositoryInterface
{
    
    /**
     * If hmac already exists, then login into Store. If don't have hmac download the app from Shopify
     *
     * @param Request $request
     * @return mixed
     */
    public function login(Request $request);

    /**
     * Receive information from login to do other things
     *
     * @param Request $request
     * @return mixed
     */
    public function authen(Request $request);


}


