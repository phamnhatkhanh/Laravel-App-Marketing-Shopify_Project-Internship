<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Store;

class Customer extends Model
{
    use HasFactory;
    protected $connection = 'mysql_customers';
    protected $table = 'customers';

    protected $fillable = [
        'id',
        'store_id',
        'email',
        'first_name',
        'last_name',
        'phone',
        'country',
        'orders_count',
        'total_spent',
        'created_at',
        'updated_at',
    ];


    public function store()
    {
    	return $this->belongsTo(Store::class);
    }
    public $timestamps = false;

    public function scopeFirstName($query, $request)
    {
        if ($request->has('first_name')) {
            $query->where('first_name', 'LIKE', '%' .$request->first_name. '%');
        }

        return $query;
    }

    public function scopeLastName($query, $request)
    {
        if ($request->has('last_name')) {
            $query->where('last_name', 'LIKE', '%' . $request->last_name . '%');
        }

        return $query;
    }

    public function scopeEmail($query, $request){
        if ($request->has('email')){
            $query->where('email',$request->email);
        }

        return $query;
    }

    public function scopePhone($query, $request){
        if ($request->has('phone')){
            $query->where('phone', $request->phone);
        }

        return $query;
    }

    public function scopeCreateAt($query, $request){
        if ($request->has('created_at')){
            $query->whereDate('created_at', $request->created_at);
        }

        return $query;
    }

    public function scopeTotalSpent($query, $request){
        $min_total_spent = 0;
        $max_total_spent = 5000000;
        if ($request->has('total_spent')){
            $query->whereBetween('total_spent',[$min_total_spent, $max_total_spent]);
        }

        return $query;
    }

    public function scopeTotalOrder($query, $request){
        $min_orders_count = 0;
        $max_orders_count = 5000000;
        if ($request->has('orders_count')){
            $query->whereBetween('orders_count',[$min_orders_count, $max_orders_count]);
        }

        return $query;
    }
}
