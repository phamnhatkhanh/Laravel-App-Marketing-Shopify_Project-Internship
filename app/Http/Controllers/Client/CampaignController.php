<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquents\CampaignRepository;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use App\Models\Campaign;
use App\Jobs\SendMail;
use App\Events\MailSent;

class CampaignController extends Controller
{
    protected $campaignRepository;
    protected $campaign;

    public function __construct(CampaignRepository $campaignRepository){
        $this->campaignRepository= $campaignRepository;
    }
     // nhan list user va gui sau hien tai fix cung.
    public function sendEmailCampaign(){
        $batch = Bus::batch([])
        ->then(function (Batch $batch) {
            event(new MailSent($batch->id));
        })->dispatch();

        $batch_id = $batch->id;
        info($batch_id);
        $users = [
            'manhitc@gmail.com'
            // 'khanhhcm4@gmail.com','nguyenducmanh123@gmail.com','phamgiakinh345@gmail.com','tranvangnhia57@gmail.com','khanhpham5301@gmail.com',
            // 'khanhhcm4@gmail.com','nguyenducmanh123@gmail.com','phamgiakinh345@gmail.com','tranvangnhia57@gmail.com','khanhpham5301@gmail.com',
        ];
        foreach ($users as  $user) {
            $batch->add(new SendMail($batch_id, $user));
        }
        return $batch;
    }
   
    public function searchCampaign(Request $request)
    {
        $search = Campaign::query()
            ->Campaign($request)
            ->get();

        return response([
            'data' => $search,
            'status' => true,
        ], 200);
    }

    public function sortCampaign(Request $request)
    {
        $sortCreated_at = Campaign::query()
            ->SortCampaingnDate($request)
            ->get();

        return response([
            'data' => $sortCreated_at,
            'status' => true,
        ], 201);
    }
}
