<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquents\CampaignRepository;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use App\Models\Campaign;
use App\Models\CampaignProcess;
use App\Models\CampaignBackgroud;
use App\Models\CampaignButton;
use App\Models\CampaignVariant;

use App\Jobs\SendMail;
use App\Events\MailSent;

class CampaignController extends Controller
{
    protected $campaignRepository;
    protected $campaign;

    public function __construct(CampaignRepository $campaignRepository){
        $this->campaignRepository= $campaignRepository;
    }

    public function saveCampaign(Request $request){
        //save campaign
        $campaign = Campaign::create($request->all());
        $request['campaign_id']=$campaign->id;
        $campaign_backgroud = CampaignBackgroud::create($request->all());
        $campaign_button = CampaignButton::create($request->all());
        foreach ($request->variant_name as $name) {
            $campaign_variant = CampaignVariant::create([
                "campaign_id" => $request->campaign_id,
                "name" => $name
            ]);
        }

        //create campaign process default
        $campaignProcess = CampaignProcess::create([
            "campaign_id" => $campaign->id,
            "name" => $campaign->name,
            "total_customers"=>1000,
        ]);

        // return $campaignProcess->id;
        $this->sendEmailCampaign($request['list_mail_customers'],$campaignProcess);


        return [$campaign,$campaign_backgroud,$campaign_button,$campaign_variant];

        // init campagin_process with data default.
        // create batch -> realtime.
        // "list_mail_customers":["khanhhcm4@gmail.com","nguyenducmanh123@gmail.com","phamgiakinh345@gmail.com","tranvangnhia57@gmail.com","khanhpham5301@gmail.com"]
    }


    // nhan list user va gui sau hien tai fix cung.
    public function sendEmailCampaign($listMailCustomers,$campaignProcess){
        // dd($campaignProcessId);
        $batch = Bus::batch([])
        ->then(function (Batch $batch) use ($campaignProcess) {
            info("in fucntion send emial  ".$campaignProcess->id);
            event(new MailSent($batch->id,$campaignProcess->id));
            info([
                'process' =>$batch->progress(),
                'send_email_done' =>$batch->processedJobs(),
                'send_email_fail' =>$batch->failedJobs,
                ]
            );
           $campaignProcess->update([
                'status' =>'completed',
                'process' =>$batch->progress(),
                'send_email_done' =>$batch->processedJobs(),
                'send_email_fail' =>$batch->failedJobs,
            ]);
            // info($campaignProcess);
        })->dispatch();

        $batchId = $batch->id;

        // $users = [
        //     // 'manhitc@gmail.com',
        //     'khanhhcm4@gmail.com','nguyenducmanh123@gmail.com','phamgiakinh345@gmail.com','tranvangnhia57@gmail.com','khanhpham5301@gmail.com',
        //     'khanhhcm4@gmail.com','nguyenducmanh123@gmail.com','phamgiakinh345@gmail.com','tranvangnhia57@gmail.com','khanhpham5301@gmail.com',
        // ];
        foreach ($listMailCustomers as  $MailCustomer) {
            $batch->add(new SendMail($batchId, $MailCustomer,$campaignProcess->id));
        }

        // return $batch;
    }
   
    public function searchFilterCampaign(Request $request)
    {
        $params = $request->except('_token');

        $data = CampaignProcess::filter($params)->get();

        return response([
            'data' =>  $data,
            'status' => true,
        ], 200);
    }

}
