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

    // protected $store;
    // public function __construct(){
    //     $this->customer = getConnectDatabaseActived(new Customer());
    //     $this->store = getConnectDatabaseActived(new Store());

    // }

    protected $fillable = [
        'id',
        'store_id',
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

        if (isset($params['sort']) && isset($params['key_sort']) && trim($params['key_sort'] !== '' && trim($params['sort'] !== ''))) {
            $query->orderBy($params['key_sort'], $params['sort']);
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
            $arr = explode(',', $params['status']);
            if (count($arr) > 0) {
                $query->whereIn('status',  $arr);
            }
        }
        return $query;
    }

    public function scopeName($query, $params)
    {
        if (!empty($params['keywords']) && trim($params['keywords']) !== '') {
            $keywords = trim($params['keywords']);
            $query->where('name', 'LIKE', "%$keywords%");
        }
        return $query;
    }
}
