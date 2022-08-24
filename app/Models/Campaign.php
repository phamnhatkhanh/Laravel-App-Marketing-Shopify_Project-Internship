<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Store;
use App\Models\CampaignVariant;
class Campaign extends Model
{
    use HasFactory;

    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection = 'mysql_campaigns';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'campaigns';

    protected $fillable = [
        'store_id',
        'name',
        'subject',
        'content',
        'footer',
        'background_banner',
        'background_color',
        'background_radius',
        'button_label',
        'button_radius',
        'button_background_color',
        'button_text_color'
    ];

    public function background()
    {
    	return $this->hasOne(CampaignVariant::class);
    }
    public function button()
    {
    	return $this->hasOne(CampaignButton::class);
    }
    public function variants()
    {
    	return $this->hasMany(CampaignVariant::class);
    }
    public function store()
    {
    	return $this->belongsTo(Store::class);
    }
}
