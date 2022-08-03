<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Campaign;
class CampaignButton extends Model
{
    use HasFactory;
    protected $connection = 'mysql_campaigns';
    protected $table = 'campaign_email_customize_button';

    protected $fillable = [
        'campaign_id',
        'button_label',
        'button_radius',
        'button_background_color',
        'button_text_color',
    ];
    public function campaign()
    {
    	return $this->belongsTo(Campaign::class);
    }
}
