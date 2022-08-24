<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\Campaign;

class CampaignProcess extends Model
{
    use HasFactory;
    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'mysql_campaigns_processes';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'campaign_processes';


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    /**
     * Get campaign belongs to this campaign process.
     *
     * @return Illuminate\Database\Eloquent;
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get list campagin process if exist keyword in columns.
     *
     * @param object $query
     * @param object $params
     *
     * @return Illuminate\Database\Eloquent\Collection;
     */
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

    /**
     * Get list campagin process has by sort date create.
     *
     * @param object $query
     * @param object $params
     *
     * @return Illuminate\Database\Eloquent\Collection;
     */
    public function scopeSort($query, $params)
    {
        if (isset($params['sort']) && trim($params['sort'] !== '')) {
            $query->orderBy('created_at', trim($params['sort']));
        }
        return $query;
    }

     /**
     * Get list campagin process has by sort their campaign process status.
     *
     * @param object $query
     * @param object $params
     *
     * @return Illuminate\Database\Eloquent\Collection;
     */
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

    /**
     * Get list campagin process if exist keyword in name column.
     *
     * @param object $query
     * @param object $params
     *
     * @return Illuminate\Database\Eloquent\Collection;
     */
    public function scopeName($query, $params)
    {
        if (!empty($params['keywords']) && trim($params['keywords']) !== '') {
            $keywords = trim($params['keywords']);
            $query->where('name', 'LIKE', "%$keywords%");
        }
        return $query;
    }
}
