<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Campaign_Process;
class Campaign extends Model
{
    use HasFactory;

    protected $connection = 'mysql_campaigns';
    protected $table = 'campaigns';
    
    protected $fillable = [
        'store_id',
        'name',
        'subject',
        'content',
        'footer'
    ];
    public function store()
    {
    	return $this->belongsTo(Campaign_Process::class);
    }

    public function scopeCampaign($query, $request)
    {
        if ($request->has('name')) {
            $query->where('name', 'LIKE', '%' . $request->name . '%');
        }

        return $query;
    }

    public function scopeSortCampaingnDate($query, $request)
    {
        $sortCreated_at = $request->created_at;
        $queryCustomer = Campaign::orderBy('created_at', $sortCreated_at ? $sortCreated_at : 'ASC');

        return  $queryCustomer;
    }
}
