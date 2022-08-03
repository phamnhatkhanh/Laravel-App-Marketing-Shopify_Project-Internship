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
use Throwable;
use Illuminate\Support\ServiceProvider;
use PDOException;
use Illuminate\Database\QueryException;
use App\Helpers\JsonRespone\formatJson;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{

    protected $productRepository;
    protected $product;

    public function __construct(ProductRepository $productRepository){
        $this->productRepository= $productRepository;
    }

    public function update(ProductRequest $request, $product_id)
    {
        $product = $this->productRepository->update($request, $product_id);
        return response(formatJson::format(Response::HTTP_NO_CONTENT,"mess",$products,"err"),
                Response::HTTP_NO_CONTENT);
    }

    public function index()
    {
        $products = $this->productRepository->all();
        return response(formatJson::format(Response::HTTP_OK,"mess",$products,"err"),
                Response::HTTP_OK);
    }

    public function show( $product_id)
    {
        $product = $this->productRepository->find($product_id);
        return response(formatJson::format(Response::HTTP_OK,"mess",$product,"err"),
                Response::HTTP_NO_CONTENT);
    }

     public function store(ProductRequest $request)
    {
        $product = $this->productRepository->store($request);
        return response(formatJson::format(Response::HTTP_OK,"mess",$product,"err"),
                Response::HTTP_CREATED);
    }

    public function destroy( $product_id)
    {
        $product = $this->productRepository->destroy( $product_id);
        return response("delete product sucess",Response::HTTP_OK);
    }

}
