<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use App\Models\Store;
use DateTime;
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
        if (isset($params['orders_from']) && isset($params['orders_to'])) {

            $orders_from = trim($params['orders_from']);
            $orders_to = trim($params['orders_to']);
            $query->where("orders_count", ">=", $orders_from)
                ->where("orders_count", "<=", $orders_to);
        }

        if (isset($params['orders_from']) && trim($params['orders_from'])) {
            $query->where("orders_count", "<=", $params['orders_from']);
        }

        if (isset($params['orders_to']) && trim($params['orders_to'])) {
            $query->where("orders_count", ">=", $params['orders_to']);
        }

        return $query;
    }

    public function scopeTotalSpent($query, $params)
    {
        if (isset($params['spent_from']) && isset($params['spent_to'])) {
            info("from-to");

            $spent_from = trim($params['spent_from']);
            $spent_to = trim($params['spent_to']);
            $query->where("total_spent", ">=", $spent_from)
                ->where("total_spent", "<=", $spent_to);
        }

        if (isset($params['spent_from']) && trim($params['spent_from'])) {
            $query->whereNotBetween('total_spent', [0, $params['spent_from']]);
        }

        if (isset($params['spent_to']) && trim($params['spent_to'])) {
            $query->whereBetween('total_spent', [0, $params['spent_to']])
                ->where("total_spent", "=", $params['spent_to']);
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
        $now = date('Y-m-d H:i:s');
        if (isset($params['date_from']) && isset($params['date_to'])) {
            $date_from = trim($params['date_from']);
            $date_to = trim($params['date_to']);
            $query->whereDate("created_at", ">=",  $date_from)
                ->whereDate("created_at", "<=", $date_to);
        }

        if (isset($params['date_from']) && trim($params['date_from'])) {
            $query->whereDate("created_at", "<=",  $params['date_from']);
        }

        if (isset($params['date_to']) && trim($params['date_to'])) {
            $query->whereDate("created_at", ">=", $params['date_to'])
                ->whereDate("created_at", "<=", $now);

            return $query;
        }
    }
}
