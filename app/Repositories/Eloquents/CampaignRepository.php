<?php

namespace App\Repositories\Eloquents;

use App\Jobs\SendEmailPreview;
use App\Jobs\SendTestPreview;
use App\Models\Store;
use App\Services\Campaigns\CampaignService;
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
use Illuminate\Http\Request;
use IvoPetkov\HTML5DOMDocument;
use Throwable;

use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class CampaignRepository implements CampaignRepositoryInterface
{
    protected $customer;
    protected $campaign;
    protected $campaignProcess;
    protected $imageNameTemp = null;

    public function __construct()
    {
        $this->campaignProcess = getConnectDatabaseActived(new CampaignProcess());
        $this->customer = getConnectDatabaseActived(new Customer());
        $this->campaign = getConnectDatabaseActived(new Campaign());
    }

    public function getCampaignProceess()
    {
        $campaignProcess = $this->campaignProcess->orderBy('created_at', 'desc')->get();

        return $campaignProcess;
    }

    public function saveCampaign(Request $request)
    {
        //save campaign
        $campaign = $this->campaign->create($request->all());

        $request['campaign_id'] = $campaign->id;


        $campaignProcess = $this->campaignProcess->create([
            "process" => "0",
            "status" => "running",
            "campaign_id" => 1,
            "name" => $campaign->name,
            "total_customers" => $this->customer->count(),
        ]);

        $this->sendEmailPreview($request, $campaignProcess);


        //    $connect = ($this->campaignProcess->getConnection()->getName());
        // event(new CreatedModel($connect,$data_campaignProcess,$this->campaignProcess->getModel()->getTable()));
        // $connect = ($this->campaignProcess->getConnection()->getName());


        // event(new CreatedModel($connect, $campaignProcess));

        return [$campaign];
    }

    // nhan list user va gui sau hien tai fix cung.
    private function sendEmailCampaign($listMailCustomers, $campaignProcess)
    {

        $batch = Bus::batch([])
            ->then(function (Batch $batch) {
            })
            ->finally(function (Batch $batch) use ($campaignProcess) {
                $campaignProcess->update([
                    'status' => 'completed',
                    'process' => 100,
                    'send_email_done' => $batch->processedJobs(),
                    'send_email_fail' => $batch->failedJobs,
                ]);

                $connect = ($campaignProcess->getConnection()->getName());
                event(new UpdatedModel($connect, $campaignProcess));
                event(new MailSent($batch->id, $campaignProcess));
            })->onQueue('jobs')->dispatch();
        $batchId = $batch->id;

        foreach ($listMailCustomers as $key => $MailCustomer) {
            if ($key > 1 && $key < 5) {
                $MailCustomer = 1;
                // info("key: ".  $key. "  value: ".$MailCustomer);
            }
            $batch->add(new SendMail($batchId, $MailCustomer, $campaignProcess));
        }
    }

    public function previewEmail($request, $array)
    {
        return CampaignService::previewEmail($request, $array);
    }

    public function subject($request, $array)
    {
        return CampaignService::subject($request, $array);
    }

    public function SendEmail(Request $request)
    {
        info('SendTestMail Success');
        $store = Store::where('myshopify_domain', $request->myshopify_domain)->first();
        $array = ([
            [
                "variant" => 'Customer_Full_name',
                "value" => $store->name_merchant
            ],
            [
                "variant" => 'Customer_First_name',
                "value" => $store->city
            ],
            [
                "variant" => 'Customer_Last_name',
                "value" => $store->country_name
            ],
            [
                "variant" => 'Shop_name',
                "value" => $store->domain
            ],
        ]);

        $bodyEmail = $this->previewEmail($request, $array);
        $imageName = $this->imageNameTemp;


        $subject = $this->subject($request->subject, $array);

        $sendEmail = $request->send_email;
        info('Ready Job : '.$store);
        dispatch(new SendTestPreview($bodyEmail, $subject, $imageName, $store, $sendEmail));
        info('SendTestMail Success');
        return [
            'message' => 'Send Test Success',
            'status' => true,
        ];
    }

    public function sendEmailPreview(Request $request, $campaignProcess)
    {
        info($request->all());
        try{
            $batch = Bus::batch([])
                ->then(function (Batch $batch) {
                })
                ->finally(function (Batch $batch) use ($campaignProcess) {
                    $campaignProcess->update([
                        'status' => 'completed',
                        'process' => 100,
                        'send_email_done' => $batch->processedJobs(),
                        'send_email_fail' => $batch->failedJobs,
                    ]);

                    $connect = ($campaignProcess->getConnection()->getName());
                    event(new UpdatedModel($connect, $campaignProcess));
                    event(new MailSent($batch->id, $campaignProcess));
                })->onQueue('jobs')->dispatch();
            $batchId = $batch->id;

            info("inside sendEmailPreview: handel templete mail ". $batchId);
            info("inside sendEmailPreview: lsit customer ". $request->list_mail_customers);

            if($request->has("list_mail_customers")){
                $listCustomersId =  json_decode($request->list_mail_customers, true);
                $listCustomers = Customer::whereIn('id', $listCustomersId)->get();
            }elseif($request->has("except_customer")){
                $listCustomersId =  $request->list_mail_customers;
                $listCustomersId =  json_decode($request->list_mail_customers, true);
                $listCustomers = Customer::whereNotIn('id', $listCustomersId)->get();
            }else{
                $listCustomers = Customer::get();
            }

            $store = Store::where('myshopify_domain', $request->domain)->first();
            foreach ($listCustomers as  $value) {
                info("inside sendEmailPreview");

                $array = ([
                    [
                        "variant" => 'Customer_Full_name',
                        "value" =>  $value->first_name.' ' . $value->last_name
                    ],
                    [
                        "variant" => 'Customer_First_name',
                        "value" => $value->first_name
                    ],
                    [
                        "variant" => 'Customer_Last_name',
                        "value" => $value->last_name
                    ],
                    [
                        "variant" => 'Shop_name',
                        "value" => $store->domain
                    ],
                ]);


                $bodyEmail = $this->previewEmail($request, $array);
                $imageName = $this->imageNameTemp;

                $subject = $this->subject($request->subject, $array);

                $batch->add(new SendEmailPreview( $value->email, $batchId, $campaignProcess,$bodyEmail, $subject, $imageName, $store));
            }
            info("inside sendEmailPreview:group jobs");
        } catch (Throwable $e) {
            info($e);
        }

        return [
            'message' => 'Prepare save campaign and send mail',
            'status' => true,
        ];
    }

    public function index(Request $request)
    {
        $store_id = getStoreID();

        $store = Store::where('id',$store_id)->first();

        if(isset($store)){
            $totalpage = 0;
            $params = $request->except('_token');
            $data = $this->campaignProcess
            ->where("store_id", $store->id)
            ->searchcampaign($params)
                ->sort($params)
                ->name($params)
                ->status($params)
                ->simplePaginate(15);
    
            $total = $this->campaignProcess
            ->where("store_id", $store->id)
            ->searchcampaign($params)->count();

            $totalpage = (int)ceil($total / 15);
            return response([
                'data' => $data,
                "totalPage" => $totalpage ? $totalpage : 0,
                "total_campaignProcess" => $this->campaignProcess->count(),
                'status' => true,
            ], 200);
        }

    }

    public function getCampaign()
    {
        return $this->campaign->orderBy('created_at', 'desc')->get();
    }

    public function store($request)
    {

        // dd("repo: sotre");

        // $request['created_at'] = Carbon::now()->format('Y-m-d H:i:s');;
        // $request['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');;

        $campaign = $this->campaign->create($request->all());
        // dd($campaign);
        // $campaign = $this->campaign->where('id', $request['id'])->first();
        $connect = ($this->campaign->getConnection()->getName());
        event(new CreatedModel($connect, $campaign));
        return $campaign;
    }

    public function update($request, $campaign_id)
    {
        $campaign = ($this->campaign->where('id', $campaign_id)->first());
        if (!empty($campaign)) {

            $campaign->update($request->all());

            $connect = ($this->campaign->getConnection()->getName());
            event(new UpdatedModel($connect, $campaign));
        }

        return $campaign;
    }

    public function destroy($campaign_id)
    {
        $campaign = $this->campaign->where('id', $campaign_id)->first();
        if (!empty($campaign)) {
            $connect = ($this->campaign->getConnection()->getName());
            event(new DeletedModel($connect, $campaign));
            return $campaign;
        }
    }

    public function show($id)
    {
    }
}
