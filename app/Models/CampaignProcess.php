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

    public function scopeSearchCampaign($query, $params)
    {
        if (!empty($params['keywords']) && trim($params['keywords']) !== '') {
            $keywords = trim($params['keywords']);
            $query->where('name', 'LIKE', "%$keywords%");
        }
        return $query;
    }

    public function scopeSort($query, $params)
    {
        if (isset($params['sort']) && trim($params['sort'] !== '')) {
            $query->orderBy('created_at', trim($params['sort']));
        }
        return $query;
    }

    public function scopeStatus($query, $params)
    {
        if (isset($params['status']) && trim($params['status'] !== '')) {
            $query->where("status", $params['status']);
        }
        return $query;
    }
}
