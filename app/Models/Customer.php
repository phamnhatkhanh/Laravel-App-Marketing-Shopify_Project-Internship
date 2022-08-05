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

    public function scopeFilter($query, $params)
    {
        if (isset($params['keywords']) && trim($params['keywords'] !== '')) {
            $query->where('first_name', 'LIKE', trim($params['keywords']) . '%')
            ->Orwhere('last_name', 'LIKE', trim($params['keywords']) . '%')
            ->Orwhere('email', 'LIKE', trim($params['keywords']) . '%')
            ->Orwhere('phone', 'LIKE', trim($params['keywords']) . '%');
        }

        if (isset($params['total_spent']) && trim($params['total_spent'] !== '')) {
            $arr = explode('-', $params['total_spent']);
            if (count($arr) > 1) {
                $query
                    ->where("total_spent", ">", $arr[0])
                    ->where("total_spent", "<=", $arr[1]);
            }
        }

        if (isset($params['orders_count']) && trim($params['orders_count'] !== '')) {
            $arr = explode('-', $params['orders_count']);
            if (count($arr) > 1) {
                $query
                    ->where("orders_count", ">", $arr[0])
                    ->where("orders_count", "<=", $arr[1]);
            }
        }

        if (isset($params['created_at']) && trim($params['created_at'] !== '')) {
            $arr = explode('/', $params['created_at']);

            if (count($arr) > 1) {
                $query
                    ->whereDate("created_at", ">=", $arr[0])
                    ->whereDate("created_at", "<=", $arr[1]);
            }
        }

        if (isset($params['sort']) && trim($params['sort'] !== '')) {
            $query->orderBy('created_at', $params['sort']);
        }

        return $query;
    }
}
