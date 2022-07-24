<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Review;
class Product extends Model
{
    use HasFactory;
    // protected $connection = 'mysql_products';
    protected $fillable = ['title', 'desc','price','stock','discount','user_id'];

    public function reviews()
    {
    	return $this->hasMany(Review::class);
    }
}

