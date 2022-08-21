<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObserveModel extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $table = 'observe_models';
    protected $fillable = ['database', 'table','id_row','action'];
}
