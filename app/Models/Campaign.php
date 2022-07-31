<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Campaign_Process;
class Campaign extends Model
{
    use HasFactory;

    protected $connection = 'mysql_campaigns';
    protected $table = 'campaigns';
    
    protected $fillable = [
        'store_id',
        'name',
        'subject',
        'content',
        'footer'
    ];
    public function store()
    {
    	return $this->belongsTo(Campaign_Process::class);
    }
}
