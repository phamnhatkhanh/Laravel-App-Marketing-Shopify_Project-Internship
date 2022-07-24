<?php
// app/Repositories/Redis/RedisProductRepository.php

namespace App\Repositories\Redis;
use App\Repositories\Contracts\ProductRepositoryInterface;
class RedisProductRepository implements ProductRepositoryInterface
{
    public function all()
    {
        info("access redis");
        return 'Get all product from Redis';
    }

    public function find($id)
    {
        return 'Get single product by id: ' . $id;
    }
}
