<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Campaign;
class CampaignProcess extends Model
{
    use HasFactory;
    protected $connection = 'mysql_campaigns_processes';
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
    public function campaign()
    {
    	return $this->belongsTo(Campaign::class);
    }
}
