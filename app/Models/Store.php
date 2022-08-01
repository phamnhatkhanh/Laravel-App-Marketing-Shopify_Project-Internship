<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Models\Customer;
use App\Models\Campaign;
class Store extends Authenticatable  implements JWTSubject
{
    use HasFactory;

    protected $connection = 'mysql_stores';
    protected $table = 'stores';

    protected $fillable = [
        'id',
        'name_merchant',
        'email',
        'password',
        'phone',
        'password',
        'myshopify_domain',
        'domain',
        'access_token',
        'address',
        'province',
        'city',
        'zip',
        'country_name',
        'created_at',
        'updated_at',

    ];

    public $timestamps = false;
    protected $guarded = [];

    public function customers()
    {
    	return $this->hasMany(Customer::class);
    }
    public function campaigns()
    {
    	return $this->hasMany(Campaign::class);
    }

    public function getJWTIdentifier()
    {        return $this->getKey();
    }    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {        return [];
    }
}
