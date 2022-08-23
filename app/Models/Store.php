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

    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'mysql_stores';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'stores';

    public $timestamps = false;
    protected $guarded = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'name_merchant',
        'email',
        'password',
        'phone',
        'status',
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

    /**
     * Get the stores customer list
     *
     * @return Illuminate\Database\Eloquent\Collection;
     */
    public function customers()
    {
    	return $this->hasMany(Customer::class);
    }

    /**
     * Get the store campaigns list
     *
     * @return Illuminate\Database\Eloquent\Collection;
     */
    public function campaigns()
    {
    	return $this->hasMany(Campaign::class);
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
