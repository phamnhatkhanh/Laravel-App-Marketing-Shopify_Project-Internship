<?php

namespace App\Repositories\Contracts;

use Illuminate\Http\Request;

interface WebHookRepositoryInterface
{
    /**
     * Receive Webhook was shot back from Shopify
     *
     * @param Request $request
     * @return mixed
     */
    function webhook(Request $request);

}


