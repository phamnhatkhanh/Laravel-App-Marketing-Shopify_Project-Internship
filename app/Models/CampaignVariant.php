<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Campaign;
class CampaignVariant extends Model
{
    use HasFactory;
    protected $connection = 'mysql_campaigns';
    protected $table = 'campaign_email_content_variant';

    protected $fillable = [
        'campaign_id',
        'name',

    ];
    public function campaign()
    {
    	return $this->belongsTo(Campaign::class);
    }
}
