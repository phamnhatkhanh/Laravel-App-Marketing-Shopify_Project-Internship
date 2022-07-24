<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\ReviewRequest;
use App\Http\Resources\Review\ReviewResource;
use App\Http\Resources\Review\ReviewCollection;
use App\Models\Review;
use App\Models\Product;
use Symfony\Component\HttpFoundation\Response;

use App\Repositories\Eloquents\ReviewRepository;

class ReviewController extends Controller
{
    protected $reviewRepository;

     public function __construct(ReviewRepository $reviewRepository){
        $this->reviewRepository= $reviewRepository;
        // $this->middleware('auth:api')->except('index','show');
    }

    public function index(Product $product){

        $reviews = $this->reviewRepository->all($product);
        return $reviews;
        // return $product->reviews;
    }

    public function store(ReviewRequest $request,Product $product)
    {
        $review = $this->reviewRepository->store($request, $product);

        return response([
            'data' => new ReviewResource($review)
        ],Response::HTTP_CREATED);
    }

     public function update(ReviewRequest $request,Product $product, Review $review)
    {
        $review =  $this->reviewRepository->update($request, $product,$review);

        return response([
            'data'=> new ReviewResource($review)
        ],404);
        // return $review;
    }

    public function destroy(Product $product, Review $review)
    {
        
        $this->reviewRepository->destroy($product,$review);
        // info("delete product");
        // return response()->json([
        //             'errors' => 'delete data success'
        //         ],404);

        return response()->json(['data'=>'delete review of product:'. $product->id ."and review id: " .$review->id],200);
    }
}
