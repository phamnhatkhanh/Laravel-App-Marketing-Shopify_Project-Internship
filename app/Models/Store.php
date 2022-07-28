<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $table = 'stores';
    protected $fillable = [
        'id',
        'name_merchant',
        'email',
        'phone',
        'domain',
        'myshopify_domain',
        'access_token',
        // 'plan_name',
        'address',
        'zip',
        'city',
        'country_name',
        // 'created_at',
        // 'updated_at',
    ];

    public $timestamps = false;
}
