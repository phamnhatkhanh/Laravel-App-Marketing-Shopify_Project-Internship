<?php

namespace App\Repositories\Contracts;
use Illuminate\Http\Request;
interface CustomerRepositoryInterface
{
    public function syncCutomerFromShopify(Request $request);
    public function index(Request $request);
    public function getCustomer();
    public function exportCustomerCSV();
    public function exportSelectCustomerCSV(Request $request);
}


