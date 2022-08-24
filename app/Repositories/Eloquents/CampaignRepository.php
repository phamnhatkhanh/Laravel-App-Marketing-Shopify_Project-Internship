<?php

namespace App\Repositories\Eloquents;

use Throwable;

use Illuminate\Bus\Batch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Schema;

use App\Models\Store;
use App\Models\Campaign;
use App\Models\CampaignProcess;
use App\Models\Customer;

use App\Events\MailSent;
use App\Events\Database\CreatedModel;
use App\Events\Database\UpdatedModel;
use App\Events\Database\DeletedModel;

use App\Jobs\SendMail;
use App\Jobs\SendEmailPreview;
use App\Jobs\SendTestPreview;

use App\Services\Campaigns\CampaignService;
use App\Repositories\Contracts\CampaignRepositoryInterface;

// use Tymon\JWTAuth\Exceptions\JWTException;
// use Tymon\JWTAuth\Exceptions\TokenExpiredException;
// use Tymon\JWTAuth\Exceptions\TokenInvalidException;
// use Tymon\JWTAuth\Facades\JWTAuth;

class CampaignRepository implements CampaignRepositoryInterface
{
    protected $store;
    protected $customer;
    protected $campaign;
    protected $campaignProcess;
    protected $imageNameTemp = null;


    public function __construct()
    {
        $this->store = setConnectDatabaseActived(new Store());
        $this->customer = setConnectDatabaseActived(new Customer());
        $this->campaign = setConnectDatabaseActived(new Campaign());
        $this->campaignProcess = setConnectDatabaseActived(new CampaignProcess());
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
        // $storeID = "60157821137";
        $storeID = getStoreID();
        $request['store_id'] = $storeID;

        try {
            $campaign = $this->campaign->create($request->all());
            $request['campaign_id'] = $campaign->id;
            $connect = ($this->campaign->getConnection()->getName());
            event(new CreatedModel($connect, $campaign));
            // dd( $this->campaignProcess->getModels());
            if ($request->has("list_mail_customers")) {
                $listCustomersId = json_decode($request->list_mail_customers, true);
                $total_customers = count($listCustomersId);
            } elseif ($request->has("list_mail_customers_except")) {
                $listCustomersId = json_decode($request->list_mail_customers_except, true);
                $total_customers = count($this->customer->whereNotIn('id', $listCustomersId)->get());
            } elseif ($request->has("all_customer")) {
                info("SendMail: send mail all_customer in store");
                $listCustomersId = $this->customer->where('store_id', $storeID)->get();
                $total_customers = count($listCustomersId);
            } else {
                $total_customers = 0;
            }
            Schema::connection($this->campaignProcess->getConnection()->getName())->disableForeignKeyConstraints();
            $campaignProcess = $this->campaignProcess->create([
                "process" => "0",
                "status" => "running",
                "store_id" => $storeID,
                "campaign_id" => $campaign->id,
                "name" => $campaign->name,
                "total_customers" => $total_customers,
            ]);
            $connect = ($this->campaignProcess->getConnection()->getName());
            event(new CreatedModel($connect, $campaignProcess));
            Schema::connection($this->campaignProcess->getConnection()->getName())->disableForeignKeyConstraints();


            $this->sendEmailPreview($request, $campaignProcess);
            return response([
                "status" => true,
                "message" => "Save success campaign"
            ], 200);
        } catch (Throwable $e) {

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
        $storeID = getStoreID();
        $store = $this->store->where('id', $storeID)->first();
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
        info('Ready Job : ' . $store);
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
        try {
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
                    event(new MailSent($batch->id, $campaignProcess));
                    event(new UpdatedModel($connect, $campaignProcess));
                })->onQueue('jobs')->dispatch();
            $batchId = $batch->id;

            info("inside sendEmailPreview: handel templete mail " . $batchId);


            if ($request->has("list_mail_customers")) {
                info("SendMail: list mail");
                info("SendMail: list mail");
                $listCustomersId =  json_decode($request->list_mail_customers, true);
                $listCustomers =  $this->customer->whereIn('id', $listCustomersId)->get();
            } elseif ($request->has("list_mail_customers_except")) {
                info("SendMail: exception mail");
                $listCustomersId =  $request->list_mail_customers_except;
                $listCustomersId =  json_decode($request->list_mail_customers_except, true);
                $listCustomers =  $this->customer->whereNotIn('id', $listCustomersId)->get();
            } elseif ($request->has("all_customer")) {
                info("SendMail: send all email in store");
                $listCustomers =  $this->customer->get();
            } else {
                $listCustomers = [];
            }

            info("inside sendEmailPreview: list customer send mail " . json_encode($listCustomers, true));
            $storeID = $campaignProcess->store_id;

            $store = $this->store->where('id', $storeID)->first();
            foreach ($listCustomers as  $value) {
                info("inside sendEmailPreview");

                $array = ([
                    [
                        "variant" => 'Customer_Full_name',
                        "value" =>  $value->first_name . ' ' . $value->last_name
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

                $batch->add(new SendEmailPreview($value->email, $batchId, $campaignProcess, $bodyEmail, $subject, $imageName, $store));
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
        $storeID = getStoreID();
        if (isset($storeID)) {
            $totalPage = 0;
            $params = $request->except('_token');
            $data = $this->campaignProcess
                ->where("store_id", $storeID)
                ->searchcampaign($params)
                ->sort($params)
                ->name($params)
                ->status($params)
                ->orderBy('created_at', 'desc')
                ->simplePaginate(15);

            $total = $this->campaignProcess
                ->where("store_id", $storeID)
                ->searchcampaign($params)->count();
            info("total" . $total);

            $totalPage = (int)ceil($total / 15);
            info("totalPage" . $totalPage);
        }

        return response([
            'data' => $data,
            "totalPage" => $totalPage ? $totalPage : 0,
            "total_campaignProcess" => $this->campaignProcess->count(),
            'status' => true,
        ], 200);
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
        // event(new CreatedModel($connect, $campaign));
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
