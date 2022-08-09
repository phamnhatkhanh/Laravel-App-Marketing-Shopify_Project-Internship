<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
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

    public function scopeSearchCustomer($query, $params)
    {
        if (!empty($params['keywords']) && trim($params['keywords']) !== '') {
            $keywords = trim($params['keywords']);
            $query->where('first_name', 'LIKE', "%$keywords%")
                ->Orwhere('last_name', 'LIKE', "%$keywords%")
                ->Orwhere('country', 'LIKE', "%$keywords%")
                ->Orwhere('email', 'LIKE', "%$keywords%")
                ->Orwhere('phone', 'LIKE', "%$keywords%");
        }

        return $query;
    }

    public function scopeOrder($query, $params)
    {
        if (isset($params['orders_count']) && trim($params['orders_count'] !== '')) {
            $arr = explode('-', $params['orders_count']);

            if (count($arr) > 1) {
                $query
                    ->where("orders_count", ">", (int)$arr[0])
                    ->where("orders_count", "<=", (int)$arr[1]);
            }
        }

        return $query;
    }

    public function scopeTotalSpant($query, $params)
    {
        if (isset($params['total_spent']) && trim($params['total_spent']) !== '') {
            $arr = explode('-', $params['total_spent']);

            if (count($arr) > 1) {
                $query->where("total_spent", ">", (int)$arr[0])
                    ->where("total_spent", "<=", (int)$arr[1]);
            }
        }

        return $query;
    }

    public function scopeSort($query, $params)
    {
        if (isset($params['sort']) && trim($params['sort'] !== '')) {
            $query->orderBy('created_at', trim($params['sort']));
        }

        return $query;
    }

    public function scopeDate($query, $params)
    {
        if (isset($params['from_date']) && isset($params['to_date'])) {
            $from_date = trim($params['from_date']);
            $to_date = trim($params['to_date']);
            $query->whereDate("created_at", ">=",  $from_date)
                ->whereDate("created_at", "<=", $to_date);
        }

        if (isset($params['from_date']) && trim($params['from_date'])) {
            $query->whereDate("created_at", "<=",  $params['from_date']);
        }

        if (isset($params['to_date']) && trim($params['to_date'])) {
            $now = date('Y-m-d H:i:s');
            $query->whereDate("created_at", ">=", $params['to_date'])
                ->whereDate("created_at", "<=", $now);
        }

        return $query;
    }

}
