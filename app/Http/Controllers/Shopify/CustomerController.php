<?php

namespace App\Http\Controllers\Shopify;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;


class CustomerController extends Controller
{
    public function showCustomer()
    {
        $customers = Customer::get();
        return response([
            'data' => $customers,
            'status' => true,
        ], 201);
    }

    public function searchCustomer(Request $request)
    {
        $search = Customer::query()
            ->firstName($request)
            ->lastName($request)
            ->email($request)
            ->phone($request)
            ->get();

        return response([
            'data' => $search,
            'status' => true,
        ], 201);
    }

    public function createDate(Request $request)
    {
        $createdDate = Customer::query()
            ->createAt($request)
            ->get();

        return response([
            'data' => $createdDate,
            'status' => true,
        ], 201);
    }

    public function totalSpent(Request $request)
    {
        $totalSpent = Customer::query()
            ->totalSpent($request)
            ->get();

        return response([
            'data' => $totalSpent,
            'status' => true,
        ], 201);
    }

    public function totalOrder(Request $request)
    {
        $totalOrder = Customer::query()
            ->totalOrder($request)
            ->get();

        return response([
            'data' => $totalOrder,
            'status' => true,
        ], 201);
    }
}
