<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

interface WebHookRepositoryInterface
{
    public function all();
    public function find(Model $product);
    public function store(Request $request);
    public function update(Request $request, Model $product);
    public function destroy(Model $product);
}


