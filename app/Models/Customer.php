<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Store;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Claims\Custom;

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
            $query->where('first_name', 'LIKE', '%' . $request->first_name . '%');
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

    public function scopeEmail($query, $request)
    {
        if ($request->has('email')) {
            $query->where('email', $request->email);
        }

        return $query;
    }

    public function scopePhone($query, $request)
    {
        if ($request->has('phone')) {
            $query->where('phone', $request->phone);
        }

        return $query;
    }

    public function scopeCreateAt($query, $request)
    {
        if ($request->has('created_at')) {
            $query->whereDate('created_at', $request->created_at);
        }

        return $query;
    }

    public function scopeTotalSpent($query, $request)
    {
        $from_total_spent = $request->from_total_spent;
        $to_total_spent = $request->to_total_spent;
        if ($request->has('total_spent')) {
            $query->whereBetween('total_spent', [$from_total_spent, $to_total_spent]);
        }

        return $query;
    }

    public function scopeTotalOrder($query, $request)
    {
        $from_orders_count = $request->from_orders_count;
        $to_orders_count =  $request->to_orders_count;
        if ($request->has('orders_count')) {
            $query->whereBetween('orders_count', [$from_orders_count, $to_orders_count]);
        }

        return $query;
    }

    public function scopeSort($query, $request)
    {
        $sortCreated_at = $request->created_at;
        $queryCustomer = Customer::orderBy('created_at', $sortCreated_at ? $sortCreated_at : 'ASC');

        return  $queryCustomer;
    }
}
