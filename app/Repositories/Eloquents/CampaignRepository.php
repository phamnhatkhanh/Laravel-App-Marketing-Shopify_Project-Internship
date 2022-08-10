<?php

namespace App\Repositories\Eloquents;

use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;

use App\Models\Campaign;
use App\Models\CampaignProcess;
use App\Models\CampaignBackgroud;
use App\Models\CampaignButton;
use App\Models\CampaignVariant;
use App\Models\Customer;

use App\Jobs\SendMail;
use App\Events\MailSent;

use App\Repositories\Contracts\CampaignRepositoryInterface;
use Illuminate\Http\Request;
use Throwable;

class CampaignRepository implements CampaignRepositoryInterface
{

    protected $campaignProcess;
    public function __construct(){
        $this->campaignProcess = new CampaignProcess();

    }

    public function getCampaignProceess(){
        $campaignProcess = CampaignProcess::all();

        return $campaignProcess;
    }

    public function saveCampaign(Request $request){

        //save campaign
        $campaign = Campaign::create($request->all());
        $request['campaign_id']=$campaign->id;

        info(json_encode($request->all()));

        //create campaign process default
        $campaignProcess = CampaignProcess::create([
            'process' =>"0",
            "campaign_id" => $campaign->id,
            "name" => $campaign->name,
            "total_customers"=>Customer::count(),
        ]);

        $this->sendEmailCampaign($request['list_mail_customers'],$campaignProcess);

        return [$campaign];
    }

    // nhan list user va gui sau hien tai fix cung.
    private function sendEmailCampaign($listMailCustomers,$campaignProcess){

        $batch = Bus::batch([])
        ->then(function (Batch $batch) {

        })
        ->finally(function (Batch $batch) use ($campaignProcess) {

            event(new MailSent($batch->id,$campaignProcess->id));

           $campaignProcess->update([
                'status' =>'completed',
                'process' => intval($batch->progress()),
                'send_email_done' =>$batch->processedJobs(),
                'send_email_fail' =>$batch->failedJobs,
            ]);

        })->onQueue('jobs')->dispatch();

        $batchId = $batch->id;
        foreach ($listMailCustomers as  $MailCustomer) {
            $batch->add(new SendMail($batchId, $MailCustomer,$campaignProcess->id));
        }
    }

    public function searchFilterCampaign(Request $request)
    {
        $params = $request->except('_token');
        $data = CampaignProcess::searchcampaign($params)
        ->sort($params)
        ->status($params)
        ->simplePaginate(15);

        return response([
            'data' =>  $data,
            'status' => true,
        ], 200);
    }


}
