<?php

namespace App\Repositories\Eloquents;

use App\Jobs\SendEmailPreview;
use App\Jobs\SendTestPreview;
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
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use IvoPetkov\HTML5DOMDocument;
use Throwable;

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
        $campaignProcess = $this->campaignProcess->get();

        return $campaignProcess;
    }

    public function saveCampaign(Request $request)
    {
        // dd("saveCampaign");
        //save campaign
        $campaign = $this->campaign->create($request->all());

        $request['campaign_id'] = $campaign->id;

        //create campaign process default
        // $data_campaignProcess =  [
        //     "process" => "0",
        //     "status" => "running",
        //     "campaign_id" => 1,
        //     "name" => $campaign->name,
        //     "total_customers" => $this->customer->count(),
        // ];
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
        info('previewEmail: inside Fisrt');
        $imageName = $this->imageNameTemp;
        if (empty($imageName) && $request->hasFile('background_banner')){
            $request->validate(
                [
                    'background_banner' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
                ]
            );

            $name = time() . '.' . $request->background_banner->extension();
            $request->background_banner->move(public_path('uploads'), $name);
            $this->imageNameTemp = $name;
        }
        $image = $this->imageNameTemp;

        $bodyPreviewEmail = $request->preview_email;
        $cutBodyPreview = str_replace(array("\\",), '', $bodyPreviewEmail);
        $domBody = new HTML5DOMDocument();
        $domBody->loadHTML($cutBodyPreview);

        $querySelectorSubject = $domBody->querySelectorAll('.tiptap_variant');
        for ($i = 0; $i < count( $querySelectorSubject ); $i++){
            $nameVariant = $querySelectorSubject[$i]->attributes[2]->value;
            foreach ($array as $arr) {
                if ($nameVariant == $arr['variant']){
                    $querySelectorSubject[$i]->textContent = $arr['value'];
                    $querySelectorSubject[$i]->attributes[0]->value = "color: rgb(40, 41, 61); font-weight: 600; margin: 0px 3px;";
                }
            }
        }
        info('previewEmail: handle Body');

        if (!empty($image)) {
            $img = $domBody->getElementsByTagName('img')[0];
            $img->setAttribute('src', asset('uploads/' . $image));
        }
        info('previewEmail: Handle Image');

        $bodyEmail = $domBody->saveHTML();
        info('previewEmail: save body');

        return $bodyEmail;
    }

    public function previewEmail1($request, $array)
    {
        info('previewEmail: inside Fisrt');
        $imageName = $this->imageNameTemp;
        if (empty($imageName) && $request->hasFile('background_banner')){
            $request->validate(
                [
                    'background_banner' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
                ]
            );

            $name = time() . '.' . $request->background_banner->extension();
            $request->background_banner->move(public_path('uploads'), $name);
            $this->imageNameTemp = $name;
        }
        $image = $this->imageNameTemp;

        info('previewEmail: Handle Image');
        $bodyPreviewEmail = $request->preview_email;
        $cutBodyPreview = str_replace(array("\\",), '', $bodyPreviewEmail);
        $domBody = new HTML5DOMDocument();
        $domBody->loadHTML($cutBodyPreview);
        $querySelectorSubject = $domBody->querySelectorAll('.tiptap_variant');
        for ($i = 0; $i < count( $querySelectorSubject ); $i++){
           $nameVariant = $querySelectorSubject[$i]->attributes[2]->value;
            foreach ($array as $arr) {
                if ($nameVariant == $arr['variant']){
                    $querySelectorSubject[$i]->textContent = $arr['value'];
                    $querySelectorSubject[$i]->attributes[0]->value = "color: rgb(40, 41, 61); font-weight: 600; margin: 0px 3px;";
                }
            }
        }
        info('previewEmail: handle Body');

        if (!empty($image)) {
            $img = $domBody->getElementsByTagName('img')[0];
            $img->setAttribute('src', asset('uploads/' . $image));
        }

        $bodyEmail = $domBody->saveHTML();
        info('previewEmail: save body');

        return $bodyEmail;
    }

    public function subject($request, $array)
    {
        $domSubject = new HTML5DOMDocument();
        $domSubject->loadHTML($request);
        $querySelectorSubject = $domSubject->querySelector('p')->childNodes;

        $arraySubject = [];
        foreach ($querySelectorSubject as $item) {
            if ($item->nodeName == '#text') {
                array_push($arraySubject, $item->data);
            } else {
                $aa = $item->childNodes[0]->data;
                array_push($arraySubject, $aa);
            }
        }
        $arrayJoinElements = implode(' ', $arraySubject);

        foreach ($array as $arr) {
            $arrayJoinElements = str_replace($arr['variant'], $arr['value'], $arrayJoinElements);
        }
        return $arrayJoinElements;
    }

    public function SendEmail(Request $request)
    {
        $store = Store::latest()->first();
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

        dispatch(new SendTestPreview($bodyEmail, $subject, $imageName, $store, $sendEmail));

        return [
            'message' => 'Send Test Success',
            'status' => true,
        ];
    }

    public function sendEmailPreview(Request $request, $campaignProcess)
    {

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

            $store = Store::where('id',1)->first();
            foreach ($listCustomers as  $value) {
                info("inside sendEmailPreview");
                // dd($request->list_mail_customers);

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
        $totalpage = 0;
        $params = $request->except('_token');
        $data = $this->campaignProcess->searchcampaign($params)
            ->sort($params)
            ->name($params)
            ->status($params)
            ->simplePaginate(15);

        $total = $this->campaignProcess->searchcampaign($params)->count();
        $totalpage = (int)ceil($total / 15);
        return response([
            'data' => $data,
            "totalPage" => $totalpage ? $totalpage : 0,
            "total_campaignProcess" => $this->campaignProcess->count(),
            'status' => true,
        ], 200);
    }

    public function getCampaign()
    {
        return $this->campaign->get();
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
        // dd("dleete function ".$campaign_id);
        $campaign = $this->campaign->where('id', $campaign_id)->first();
        if (!empty($campaign)) {
            // $campaign->delete();
            $connect = ($this->campaign->getConnection()->getName());
            event(new DeletedModel($connect, $campaign));
            return $campaign;
        }
    }

    public function show($id)
    {
    }
}
