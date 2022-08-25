<?php

namespace App\Repositories\Contracts;

use App\Http\Requests\UpdateCampaignRequest;
use Illuminate\Http\Request;

interface CampaignRepositoryInterface
{
    /**
     * Search Campaign by Store
     *
     * @param UpdateCampaignRequest $request
     * @return mixed
     */
    public function index(Request $request);

    /**
     * Receive request from FrontEnd. Send mail for selected customers and use Pusher the display mail number of successes, failures
     *
     * @param Request $request
     * @return mixed
     */
    public function saveCampaign(UpdateCampaignRequest $request);

    /**
     * Receive request from FrontEnd put in Job and send mail to the person receiving the request
     *
     * @param UpdateCampaignRequest $request
     * @return mixed
     */
    public function SendEmail(UpdateCampaignRequest $request);

}


