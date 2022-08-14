<?php

namespace App\Repositories\Contracts;
use Illuminate\Http\Request;
interface CampaignRepositoryInterface
{
    public function getCampaignProceess();
    public function saveCampaign(Request $request);
    public function searchFilterCampaign(Request $request);
    public function sendEmailPreview(Request $request, $campaignProcess);
}


