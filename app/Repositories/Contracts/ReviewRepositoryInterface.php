<?php
// app/Repositories/Contracts/ProductRepositoryInterface.php

namespace App\Repositories\Contracts;
// use App\Repositories\Contracts\ProductRepositoryInterface;
interface ReviewRepositoryInterface
// extends ProductRepositoryInterface
{
    public function all(Model $product);
    public function find(Model $review);
    public function store(Request $request, Model $review);
    public function update(Request $request, Product $product,Review $review);
    public function destroy(Request $request, Product $product);
}
// truyen thieu hoac du doi so laf loi ngay
// nay dua  class ke thua

//crr ddang muon lam eloquent chung het

