<?php

namespace App\Repositories\Contracts;
use Illuminate\Http\Request;
interface CampaignRepositoryInterface
{
    /**
     * Search Campaign by Store
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request);

    /**
     * Receive request from FrontEnd. Send mail for selected customers and use Pusher the display mail number of successes, failures
     *
     * @param Request $request
     * @return mixed
     */
    public function saveCampaign(Request $request);

    /**
     * Receive request from FrontEnd put in Job and send mail to the person receiving the request
     *
     * @param Request $request
     * @return mixed
     */
    public function SendEmail(Request $request);

    /**
     * Receive request from saveCampaign put in Job. Send mail for selected customers and use Pusher the display mail number of successes, failures
     *
     * @param Request $request
     * @param Illuminate\Database\Eloquent\Model $campaignProcess
     * @return mixed
     */
    public function sendEmailPreview(Request $request, $campaignProcess);
}


