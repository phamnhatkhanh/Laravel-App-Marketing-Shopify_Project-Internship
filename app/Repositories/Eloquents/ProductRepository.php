<?php
// app/Repositories/Eloquents/ProductRepository.php

namespace App\Repositories\Eloquents;

use App\Models\Product;
use App\Models\User;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Product\ProductCollectionResource;
use \Illuminate\Http\Request;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ProductRepository implements ProductRepositoryInterface
{

    public function all()
    {
        return ProductCollectionResource::collection(Product::paginate(20));
    }

    public function find( $id)
    {
        $product =Product::find($id);
        return new ProductResource($product);
    }

    public function store($request){

        $product = new Product;
        $product->user_id = 2;
        $product->title = $request->title;
        $product->desc = $request->desc;
        $product->stock = $request->stock;
        $product->price = $request->price;
        $product->discount = $request->discount;
        $product->save();
        return $product;
    }
    public function update($request, $product){
        $product->update($request->all());
        return $product;
    }
    public function destroy( $product){
        $product->delete();
    }
}

