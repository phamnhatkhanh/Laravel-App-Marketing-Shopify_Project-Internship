<?php
// app/Repositories/Eloquents/ProductRepository.php

namespace App\Repositories\Eloquents;

use App\Models\Product;
use App\Models\Review;
use App\Repositories\Contracts\ReviewRepositoryInterface;

use App\Http\Resources\Review\ReviewResource;
use App\Http\Resources\Review\ReviewCollectionResource;
use \Illuminate\Http\Request;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ReviewRepository implements ReviewRepositoryInterface
{
    public function all($product)
    {
        info("access Review repository");
        // ReviewResource::collection();
        return ReviewResource::collection($product->reviews);

    }

    public function find( $review)
    {
        return new ReviewResource($review);
    }

    public function store($request,$product){
        $review = new Review($request->all());
        $product->reviews()->save($review);
        return $review;
    }

    public function update($request, $product,$review){
        $review->update($request->all());
        return $review;

    }

    public function destroy( $product,$review){
        $review->delete();
    }
}

