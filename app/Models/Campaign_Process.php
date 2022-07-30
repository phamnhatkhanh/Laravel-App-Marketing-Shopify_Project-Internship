<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign_Process extends Model
{
    use HasFactory;
    protected $table = 'campaign_processes';
    protected $fillable = [
        'id',
        'campaign_id',
        'name',
        'status',
        'send_email_done',
        'send_email_fail',
        'total_customers',
    ];
}
