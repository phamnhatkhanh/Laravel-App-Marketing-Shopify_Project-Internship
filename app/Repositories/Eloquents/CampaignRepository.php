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
use App\Models\Customer;
use App\Jobs\SendMail;
use App\Events\MailSent;
use App\Events\Database\CreatedModel;
use App\Events\Database\UpdatedModel;
use App\Events\Database\DeletedModel;
use App\Repositories\Contracts\CampaignRepositoryInterface;
use Illuminate\Http\Request;
use Throwable;

use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Schema;
class CampaignRepository implements CampaignRepositoryInterface
{
    protected $customer;
    protected $campaign;
    protected $campaignProcess;
    protected $imageNameTemp = null;


    public function __construct()
    {
        $this->customer = getConnectDatabaseActived(new Customer());
        $this->campaign = getConnectDatabaseActived(new Campaign());
        $this->campaignProcess = getConnectDatabaseActived(new CampaignProcess());
    }

    /**
     * Get List Campaign Processes
     *
     * @return mixed
     */
    public function getCampaignProceess()
    {
        $campaignProcess = $this->campaignProcess->orderBy('created_at', 'desc')->get();

        return $campaignProcess;
    }

    /**
     * Receive request from FrontEnd. Send mail for selected customers and use Pusher the display mail number of successes, failures
     *
     * @param Request $request
     * @return array
     */
    public function saveCampaign(Request $request)
    {
        //save campaign
        // dd($this->campaign->getModels());

        try{
            $campaign = $this->campaign->create($request->all());
            $request['campaign_id'] = $campaign->id;
            $connect = ($this->campaign->getConnection()->getName());
            event(new CreatedModel($connect,$campaign));
            // dd( $this->campaignProcess->getModels());

            Schema::connection($this->campaignProcess->getConnection()->getName())->disableForeignKeyConstraints();
                $campaignProcess = $this->campaignProcess->create([
                    "process" => "0",
                    "status" => "running",
                    "campaign_id" => $campaign->id,
                    "name" => $campaign->name,
                    "total_customers" => $this->customer->count(),
                ]);
                $connect = ($this->campaignProcess->getConnection()->getName());
                event(new CreatedModel($connect,$campaignProcess));
            Schema::connection($this->campaignProcess->getConnection()->getName())->disableForeignKeyConstraints();


            $this->sendEmailPreview($request, $campaignProcess);
            return response([
            "status" => true,
            "message" => "Save success campaign"
        ], 200);
        }catch(Throwable $e){
            // dd($e);
        }

    }

    /**
     * Receive request, array from sendMail or SendMailPreview replace image, mailing content and put in dom
     *
     * @param $request
     * @param array $array
     * @return string
     */
    public function previewEmail($request, $array)
    {
        return CampaignService::previewEmail($request, $array);
    }

    /**
     * Receive request, array from SendMail or sendMailPreview put in dom and replace subject
     *
     * @param $request
     * @param array $array
     * @return string
     */
    public function subject($request, $array)
    {
        return CampaignService::subject($request, $array);
    }

    /**
     * Receive request from FrontEnd put in Job and send mail to the person receiving the request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function SendEmail(Request $request)
    {
        info('SendTestMail Success');
        $storeID = GetStoreID();
        $store = Store::where('id', $storeID)->first();
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

        return response()->json([
            'message' => 'Send Test Success',
            'status' => true,
        ], 204);
    }

    /**
     * Receive request from saveCampaign put in Job. Send mail for selected customers and use Pusher the display mail number of successes, failures
     *
     * @param Request $request
     * @param $campaignProcess
     * @return \Illuminate\Http\JsonResponse
     */
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
            }elseif($request->has("all_customer")){
                $listCustomers = Customer::get();
            }

            $storeID = GetStoreID();
            $store = Store::where('id', $storeID)->first();
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

        return response()->json([
            'message' => 'Prepare save campaign and send mail',
            'status' => true,
        ], 204);
    }

    /**
     * Search Campaign by Store
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|void
     */
    public function index(Request $request)
    {
        return $this->campaign->get();
        // dd("skfskfj");
        // $store_id = getStoreID();

        // $store = Store::where('id',$store_id)->first();

        // if(isset($store)){
        //     $totalpage = 0;
        //     $params = $request->except('_token');
        //     $data = $this->campaignProcess
        //     ->where("store_id", $store->id)
        //     ->searchcampaign($params)
        //         ->sort($params)
        //         ->name($params)
        //         ->status($params)
        //         ->simplePaginate(15);

        //     $total = $this->campaignProcess
        //     ->where("store_id", $store->id)
        //     ->searchcampaign($params)->count();

        //     $totalpage = (int)ceil($total / 15);
        //     return response([
        //         'data' => $data,
        //         "totalPage" => $totalpage ? $totalpage : 0,
        //         "total_campaignProcess" => $this->campaignProcess->count(),
        //         'status' => true,
        //     ], 200);
        // }

    }

    /**
     * Get list Campaign
     *
     * @return mixed
     */
    public function getCampaign()
    {
        return $this->campaign->get();
        // return $this->campaign->orderBy('created_at', 'desc')->get();
    }

    /**
     * Save Campaign
     *
     * @param $request
     * @return mixed
     */
    public function store($request)
    {
        $campaign = $this->campaign->create($request->all());
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
