<?php

namespace App\Http\Controllers\Client;

use App\Jobs\SendEmailPreview;
use App\Mail\SendMailPreview;
use App\Models\Store;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use IvoPetkov\HTML5DOMDocument;
use IvoPetkov\HTML5DOMElement;
use IvoPetkov\HTML5DOMNodeList;
use phpDocumentor\Reflection\Types\Boolean;
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

    public function __construct(CampaignRepository $campaignRepository)
    {
        $this->campaignRepository = $campaignRepository;
    }

    public function getCampaignProceess()
    {

        $campaignProcess = $this->campaignRepository->getCampaignProceess();
        return response(
        // "data"=>$campaignProcess,
            formatJsonRepsone(Response::HTTP_OK,"mess",$campaignProcess,"err"),
            Response::HTTP_OK);
    }

    public function saveCampaign(Request $request){

        return $this->campaignRepository->saveCampaign($request);
    }

    public function sendEmailPreview(Request $request)
    {
        return $this->campaignRepository->sendEmailPreview($request);
    }

    public function searchFilterCampaign(Request $request)
    {

        return $this->campaignRepository->searchFilterCampaign($request);
    }

    public function getCampaign()
    {
        // dd("get campaignRepository");
        return $this->campaignRepository->getCampaign();
    }

    public function update(Request $request, $id)
    {

        // dd("upate proe");
        $campaign = $this->campaignRepository->update($request, $id);

        return response([
            'data' => $campaign
        ],201);
    }

    public function store(Request $request)
    {
        // dd("store prodcut");
        $campaign = $this->campaignRepository->store($request);
        return response([
            'data' => $campaign
            // 'data' => new campaignResource($campaign)
        ],201);
    }

    public function destroy($id)
    {
        // dd("dlete campaign resource");
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
