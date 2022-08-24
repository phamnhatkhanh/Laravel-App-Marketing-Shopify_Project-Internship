<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DbStatus extends Model
{
    use HasFactory;
    /**
     * The connection name for the model.
     *
     * @var string|null
     */
protected $connection = 'mysql';
    /**
     * The table associated with the model.
     *
     * @var string
     */
protected $table = 'db_statuses';
    protected $fillable = ['name','status','model_name'];
}
