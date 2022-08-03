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
use App\Models\Customer;
use App\Jobs\SendMail;
use App\Events\MailSent;
use App\Helpers\JsonRespone\formatJson;
use Symfony\Component\HttpFoundation\Response;

class CampaignController extends Controller
{
    protected $campaignRepository;
    protected $campaign;

    public function __construct(CampaignRepository $campaignRepository){
        $this->campaignRepository= $campaignRepository;
    }

    public function getCampaignProceess(){
        $campaignProcess = CampaignProcess::all();
        return response(formatJson::format(Response::HTTP_OK,"mess",$campaignProcess,"err"),
                Response::HTTP_OK);
    }

    public function saveCampaign(Request $request){
        //save campaign
        $campaign = Campaign::create($request->all());
        $request['campaign_id']=$campaign->id;
        // dd($request->all());
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
            'process' =>"0",
            "campaign_id" => $campaign->id,
            "name" => $campaign->name,
            "total_customers"=>Customer::count(),
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

            event(new MailSent($batch->id,$campaignProcess->id));
            info(gettype($batch->progress()).' :  '.$batch->progress() .' campaign processed: ').$campaignProcess->process;


           $campaignProcess->update([
                'status' =>'completed',
                'process' => intval($batch->progress()),
                'send_email_done' =>$batch->processedJobs(),
                'send_email_fail' =>$batch->failedJobs,
            ]);

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
