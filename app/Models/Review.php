<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
class Review extends Model
{
    use HasFactory;
    // protected $connection = 'mysql_reviews';
     protected $fillable = ['customer', 'review','star','product_id'];
     public function product()
    {
    	return $this->belongsTo(Product::class);
    }
}
