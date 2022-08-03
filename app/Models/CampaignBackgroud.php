<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Campaign;
class CampaignBackgroud extends Model
{
    use HasFactory;
    protected $connection = 'mysql_campaigns';
    protected $table = 'campaign_email_customize_background';

    protected $fillable = [
        'campaign_id',
        'background_banner',
        'background_color',
        'background_radius',
    ];
    public function campaign()
    {
    	return $this->belongsTo(Campaign::class);
    }
}
