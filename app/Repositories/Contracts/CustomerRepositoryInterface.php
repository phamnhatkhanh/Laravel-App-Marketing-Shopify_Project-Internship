<?php

namespace App\Repositories\Contracts;
use Illuminate\Http\Request;
interface CustomerRepositoryInterface
{
    public function syncCutomerFromShopify();
    public function index(Request $request);
    public function getCustomer();
    public function exportCustomerCSV(Request $request);
}


