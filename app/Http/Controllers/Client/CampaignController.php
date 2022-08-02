<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Repositories\Eloquents\CampaignRepository;
use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;

use App\Models\Campaign;
use App\Jobs\SendMail;
use App\Events\MailSent;

class CampaignController extends Controller
{
    protected $campaignRepository;
    protected $campaign;

    public function __construct(CampaignRepository $campaignRepository){
        $this->campaignRepository= $campaignRepository;
    }
     // nhan list user va gui sau hien tai fix cung.
    public function sendEmailCampaign(){
        $batch = Bus::batch([])
        ->then(function (Batch $batch) {
            event(new MailSent($batch->id));
        })->dispatch();

        $batch_id = $batch->id;
        info($batch_id);
        $users = [
            'manhitc@gmail.com'
            // 'khanhhcm4@gmail.com','nguyenducmanh123@gmail.com','phamgiakinh345@gmail.com','tranvangnhia57@gmail.com','khanhpham5301@gmail.com',
            // 'khanhhcm4@gmail.com','nguyenducmanh123@gmail.com','phamgiakinh345@gmail.com','tranvangnhia57@gmail.com','khanhpham5301@gmail.com',
        ];
        foreach ($users as  $user) {
            $batch->add(new SendMail($batch_id, $user));
        }
        return $batch;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreCampaignRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCampaignRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Campaign  $campaign
     * @return \Illuminate\Http\Response
     */
    public function show(Campaign $campaign)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Campaign  $campaign
     * @return \Illuminate\Http\Response
     */
    public function edit(Campaign $campaign)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCampaignRequest  $request
     * @param  \App\Models\Campaign  $campaign
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCampaignRequest $request, Campaign $campaign)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Campaign  $campaign
     * @return \Illuminate\Http\Response
     */
    public function destroy(Campaign $campaign)
    {
        //
    }
}
