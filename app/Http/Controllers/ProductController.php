<?php

namespace App\Http\Controllers;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Repositories\Eloquents\ProductRepository;
// use App\Repositories\Redis\RedisProductRepository;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Product\ProductCollection ;
use App\Http\Requests\ProductRequest;
use Illuminate\Database\Connection;
use DB;
use Exception;
use Throwable;
use Illuminate\Support\ServiceProvider;
use PDOException;
class ProductController extends Controller
{
    protected $productRepository;
    // public function __construct(RedisProductRepository $productRepository){
    //     $this->productRepository= $productRepository;

    // }
    public function __construct(ProductRepository $productRepository){
        $this->productRepository= $productRepository;
        // $this->middleware('auth:api')->except('index','show');
    }

    public function index(){
        $products = $this->productRepository->all();
        return $products;
    }
    public function show(Product $product){
        // $product = $this->productRepository->find($product);
        return $product;
    }
     public function store(ProductRequest $request)
    {
        // $product = $this->productRepository->store($request);
        return response([
            // 'data' => new ProductResource($product)
        ],201);
    }
    public function update(ProductRequest $request, Product $product)
    {

        // try{

            // $product = $this->productRepository->update($request, $product);
        // }catch{
        //     throw nghia("vfbsjnfsjfdngf");
        // }
        // $this->ProductUserCheck($product);




        return response([
            'data' => new ProductResource($product)
        ],201);
    }
    public function destroy(Product $product)
    {

        // $product = $this->productRepository->destroy( $product);
        return response("delete product sucess",213);
    }
}
