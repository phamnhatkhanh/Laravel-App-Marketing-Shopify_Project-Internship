<?php

namespace App\Repositories\Eloquents;

use App\Jobs\SendEmailPreview;
use App\Models\Store;
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
use IvoPetkov\HTML5DOMDocument;
use Throwable;

class CampaignRepository implements CampaignRepositoryInterface
{

    protected $customer;
    protected $campaign;
    protected $campaignProcess;

    public function __construct(){
        $this->campaignProcess = getConnectDatabaseActived(new CampaignProcess());
        $this->customer = getConnectDatabaseActived(new Customer());
        $this->campaign = getConnectDatabaseActived(new Campaign());

    }

    public function getCampaignProceess(){
        $campaignProcess = $this->campaignProcess->all();

        return $campaignProcess;
    }

    public function saveCampaign(Request $request){

        //save campaign
        $campaign = $this->campaign->create($request->all());
        $request['campaign_id']=$campaign->id;

        // info(json_encode($request->all()));

        //create campaign process default
        $campaignProcess = $this->campaignProcess->create([
            'process' =>"0",
            "campaign_id" => $campaign->id,
            "name" => $campaign->name,
            "total_customers"=>$this->customer->count(),
        ]);

        $this->sendEmailCampaign($request['list_mail_customers'],$campaignProcess);

//        dispatch(new SendEmailPreview($subject, $sendEmail));
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

    public function sendEmailPreview(Request $request)
    {
        if ($request->hasFile('fileImage')) {
            if ($request->file('fileImage')->isValid()) {
                $request->validate(
                    [
                        'fileImage' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
                    ]
                );

                $imageName = time() . '.' . $request->fileImage->extension();
                $request->fileImage->move(public_path('uploads'), $imageName);
            }
        } else {
            $imageName = '';
        }

        $body = $request->preview_email;
        $store = Store::latest()->first();

        $domBody = new HTML5DOMDocument();
        $domBody->loadHTML($body);

        $findFooter = array('<p style="text-align: center">', '</p>');
        $replaceFooter = array('', '');
        $footer = str_replace($findFooter, $replaceFooter, $request->footer);

        if (!empty($imageName)) {
            $img = $domBody->getElementsByTagName('img')[0];
            $img->setAttribute('src', asset('uploads/' . $imageName));
        }

        $body = $domBody->saveHTML();
        $body = str_replace('Customer_Full_name', $store->name_merchant ?? '', $body);
        $body = str_replace('Customer_First_name', $store->name_merchant ?? '', $body);
        $body = str_replace('Customer_Last_name', $store->name_merchant ?? '', $body);
        $body = str_replace('Shop_name', $store->name_merchant ?? '', $body);

        $subject = $request->subject;
        $findSubject = array('<p>', '<span>', '</span></p>');
        $replaceSubject = array('', '', '');
        $subject = str_replace($findSubject, $replaceSubject, $subject);

        $subject = str_replace('Customer_Full_name', $store->name_merchant ?? '', $subject);
        $subject = str_replace('Customer_First_name', $store->name_merchant ?? '', $subject);
        $subject = str_replace('Customer_Last_name', $store->name_merchant ?? '', $subject);
        $subject = str_replace('Shop_name', $store->name_merchant ?? '', $subject);

        dispatch(new SendEmailPreview($body, $subject, $imageName, $store, $request->send_email));

        return response([
            'message' => 'Send Email Test Success',
            'status' => 204,
        ], 204);
    }

    public function searchFilterCampaign(Request $request)
    {
        $params = $request->except('_token');
        $data = $this->campaignProcess->searchcampaign($params)
        ->sort($params)
        ->status($params)
        ->simplePaginate(15);

        return response([
            'data' =>  $data,
            'status' => true,
        ], 200);
    }


}
