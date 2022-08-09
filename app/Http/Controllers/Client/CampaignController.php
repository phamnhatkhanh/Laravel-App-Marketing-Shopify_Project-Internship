<?php

namespace App\Http\Controllers\Client;

use Symfony\Component\HttpFoundation\Response;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquents\CampaignRepository;

use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
use Illuminate\Http\Request;

use App\Models\Campaign;
use App\Models\CampaignProcess;
use App\Models\CampaignBackgroud;
use App\Models\CampaignButton;
use App\Models\CampaignVariant;
use App\Models\Customer;

use App\Jobs\SendMail;
use App\Events\MailSent;


class CampaignController extends Controller
{
    protected $campaignRepository;
    protected $campaign;

    public function __construct(CampaignRepository $campaignRepository){
        $this->campaignRepository= $campaignRepository;
    }

    public function getCampaignProceess(){

        $campaignProcess = $this->campaignRepository->getCampaignProceess();
        return response(formatJsonRepsone(Response::HTTP_OK,"mess",$campaignProcess,"err"),
            Response::HTTP_OK);
    }

    public function saveCampaign(Request $request){

        return $this->campaignRepository->saveCampaign($request);
    }

    public function searchFilterCampaign(Request $request)
    {
       return $this->campaignRepository->searchFilterCampaign($request);
    }

}
