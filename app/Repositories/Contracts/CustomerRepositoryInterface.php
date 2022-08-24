<?php

namespace App\Repositories\Contracts;

use Illuminate\Http\Request;

interface CustomerRepositoryInterface
{
    /**
     * Get Store Information
     *
     * @param Request $request
     * @return mixed
     */
    public function syncCutomerFromShopify(Request $request);

    /**
     * Search Customer by Store
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request);


    /**
     * Receive request from FontEnd send all Customer or select Customer and Create File to send Mail
     *
     * @param Request $request
     * @return mixed
     */
    public function exportCustomerCSV(Request $request);
}


