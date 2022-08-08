<?php

namespace App\Repositories\Contracts;

interface CustomerRepositoryInterface
{
    // public function all();
    // public function find(Model $product);
    // public function store(Request $request);
    // public function update(Request $request, Model $product);
    // public function destroy(Model $product);
    public function exportCustomerCSV();
}


