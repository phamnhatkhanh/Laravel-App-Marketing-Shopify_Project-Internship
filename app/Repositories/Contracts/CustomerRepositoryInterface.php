<?php

namespace App\Repositories\Contracts;
use Illuminate\Http\Request;
interface CustomerRepositoryInterface
{
    public function syncCutomerFromShopify();
    public function index();
    public function searchFilterCustomer(Request $request);
    public function exportCustomerCSV();
}


