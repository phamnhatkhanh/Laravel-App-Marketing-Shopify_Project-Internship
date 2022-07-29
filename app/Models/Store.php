<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Store extends Authenticatable  implements JWTSubject
{
    use HasFactory;

    protected $table = 'stores';

    protected $fillable = [
        'id',
        'name_merchant',
        'email',
        'password',
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
    protected $guarded = [];

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
