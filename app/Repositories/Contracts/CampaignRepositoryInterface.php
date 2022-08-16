<?php

namespace App\Repositories\Contracts;
use Illuminate\Http\Request;
interface CampaignRepositoryInterface
{
    public function index(Request $request);
    public function saveCampaign(Request $request);
    public function searchFilterCampaign(Request $request);
    public function SendEmail(Request $request);
    public function sendEmailPreview(Request $request, $campaignProcess);
}


