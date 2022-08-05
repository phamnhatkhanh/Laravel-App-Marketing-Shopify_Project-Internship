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
        'process',
        'status',
        'send_email_done',
        'send_email_fail',
        'total_customers',
        'created_at'
    ];
    public function campaign()
    {
    	return $this->belongsTo(Campaign::class);
    }

    public function scopeFilter($query, $params)
    {
        if (isset($params['name']) && trim($params['name'] !== '')) {
            $query->where('name', 'LIKE', trim($params['name']) . '%');
        }

        if (isset($params['sortDate']) && trim($params['sortDate'] !== '')) {
            $query->orderBy('created_at', $params['sortDate']);
        }

        if (isset($params['status']) && trim($params['status'] !== '')) {
            $query->where("status", $params['status']);
        }
        return $query;
    }
}
