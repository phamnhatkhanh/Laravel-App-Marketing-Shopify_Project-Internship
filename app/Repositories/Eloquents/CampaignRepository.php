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
use Carbon\Carbon;
use App\Jobs\SendMail;
use App\Events\MailSent;

use App\Events\Database\CreatedModel;
use App\Events\Database\UpdatedModel;
use App\Events\Database\DeletedModel;

use App\Repositories\Contracts\CampaignRepositoryInterface;
use DOMDocument;
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
        $campaignProcess = $this->campaignProcess->get();

        return $campaignProcess;
    }

    public function saveCampaign(Request $request){
        //save campaign
        $campaign = $this->campaign->create($request->all());
        $request['campaign_id']=$campaign->id;

        //create campaign process default
        $campaignProcess = $this->campaignProcess->create([
            "process" =>"0",
            "status" => "running",
            "campaign_id" => $campaign->id,
            "name" => $campaign->name,
            "total_customers"=>$this->customer->count(),
        ]);

        $connect = ($this->campaignProcess->getConnection()->getName());
        event(new CreatedModel($connect,$campaignProcess));
        // dd($request['list_mail_customers']);
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
            $campaignProcess->update([
                'status' =>'completed',
                'process' => 100,
                'send_email_done' =>$batch->processedJobs(),
                'send_email_fail' =>$batch->failedJobs,
            ]);

            $connect = ($campaignProcess->getConnection()->getName());
            event(new UpdatedModel($connect,$campaignProcess));
            event(new MailSent($batch->id,$campaignProcess));
        })->onQueue('jobs')->dispatch();
        $batchId = $batch->id;

        foreach ($listMailCustomers as  $key => $MailCustomer) {
            if($key >1 && $key < 5){
                $MailCustomer =1;
                // info("key: ".  $key. "  value: ".$MailCustomer);
            }
            $batch->add(new SendMail($batchId, $MailCustomer,$campaignProcess));
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

        $domBody = new DOMDocument();
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
        ->name($params)
        ->status($params)
        ->get();

        return response([
            'data' =>  $data,
            'status' => true,
        ], 200);
    }

    public function getCampaign()
    {
        // dd("skfbsjfhds");
        return $this->campaign->get();

    }

    public function store($request){

        // dd("repo: sotre");

        // $request['created_at'] = Carbon::now()->format('Y-m-d H:i:s');;
        // $request['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');;

        $campaign = $this->campaign->create($request->all());
        // dd($campaign);
        // $campaign = $this->campaign->where('id', $request['id'])->first();
        $connect = ($this->campaign->getConnection()->getName());
        event(new CreatedModel($connect,$campaign));
        return $campaign;
    }

    public function update( $request, $campaign_id){
        $this->campaign->where('id',$campaign_id)->update($request->all());
        $campaign  = ($this->campaign->where('id',$campaign_id)->first());
        $connect = ($this->campaign->getConnection()->getName());
        event(new UpdatedModel($connect,$campaign));

        return $campaign;
    }
    public function destroy( $campaign_id){
        // dd("dleete function ".$campaign_id);
        $campaign = $this->campaign->where('id',$campaign_id)->first();
        if(!empty($campaign)){
            $campaign->delete();
            $connect = ($this->campaign->getConnection()->getName());
            event(new DeletedModel($connect,$campaign));
            return $campaign;
        }
    }
    public function show($id)
    {

    }

}
