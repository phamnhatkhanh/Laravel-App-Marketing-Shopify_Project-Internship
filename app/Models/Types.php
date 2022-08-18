<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Types extends Model
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
}
