<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquents\CampaignRepository;



class CampaignController extends Controller
{
    protected $campaignRepository;
    protected $campaign;

    public function __construct(CampaignRepository $campaignRepository)
    {
        $this->campaignRepository = $campaignRepository;
    }

    /**
     * Search Campaign by Store
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|void
     */
    public function index(Request $request)
    {
        return $this->campaignRepository->index($request);
    }

    /**
     * Get list Campaign Processes
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getCampaignProceess()
    {

        $campaignProcess = $this->campaignRepository->getCampaignProceess();
        return response(
            formatJsonRepsone(Response::HTTP_OK,"mess",$campaignProcess,"err"),
            Response::HTTP_OK);
    }

    /**
     * Receive request from FrontEnd. Send mail for selected customers and use Pusher the display mail number of successes, failures
     *
     * @param Request $request
     * @return array
     */
    public function saveCampaign(Request $request){

        return $this->campaignRepository->saveCampaign($request);
    }

    /**
     * Receive request from FrontEnd put in Job and send mail to the person receiving the request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function SendEmail(Request $request){
        return $this->campaignRepository->sendEmail($request);
    }

    /**
     * Receive request from saveCampaign put in Job. Send mail for selected customers and use Pusher the display mail number of successes, failures
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendEmailPreview(Request $request)
    {
        return $this->campaignRepository->sendEmailPreview($request);
    }

    /**
     * Get list Campaign
     *
     * @return mixed
     */
    public function getCampaign()
    {
        return $this->campaignRepository->getCampaign();
    }

    public function update(Request $request, $id)
    {
        $campaign = $this->campaignRepository->update($request, $id);

        return response([
            'data' => $campaign
        ],201);
    }

    /**
     * Save Campaign
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $campaign = $this->campaignRepository->store($request);
        return response([
            'data' => $campaign
        ],201);
    }

    public function destroy($id)
    {
        $campaign = $this->campaignRepository->destroy( $id);
        return response([
            'data' => $campaign,
            'mess' => "dleete campaign done"
        ],201);

    }

    public function show($id)
    {

    }

}
