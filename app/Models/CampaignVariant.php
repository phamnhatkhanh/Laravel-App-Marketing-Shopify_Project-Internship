<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Campaign;
class CampaignVariant extends Model
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
    protected $table = 'campaign_email_content_variant';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'campaign_id',
        'name',

    ];


    /**
     * Get campaign belongs to this campaign variant.
     *
     * @return Illuminate\Database\Eloquent;
     */
    public function campaign()
    {
    	return $this->belongsTo(Campaign::class);
    }
}
