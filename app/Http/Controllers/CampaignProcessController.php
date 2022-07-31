<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Bus;
use Illuminate\Bus\Batch;
use App\Http\Requests\StoreCampaign_ProcessRequest;
use App\Http\Requests\UpdateCampaign_ProcessRequest;
use App\Models\Campaign_Process;
use App\Jobs\SendMail;
use App\Events\MailSent;
class CampaignProcessController extends Controller
{

     // nhan list user va gui sau hien tai fix cung.

    public function sendEmailCampaign(){
        // dd("sfknskf");
        $batch = Bus::batch([])
        ->then(function (Batch $batch) {
            event(new MailSent($batch->id));
        })->dispatch();

        $batch_id = $batch->id;
        info($batch_id);
        $users = [
            'khanhhcm4@gmail.com'
            // 'khanhhcm4@gmail.com','nguyenducmanh123@gmail.com','phamgiakinh345@gmail.com','tranvangnhia57@gmail.com','khanhpham5301@gmail.com',
            // 'khanhhcm4@gmail.com','nguyenducmanh123@gmail.com','phamgiakinh345@gmail.com','tranvangnhia57@gmail.com','khanhpham5301@gmail.com',
        ];
        // dd("sfknskf");
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
     * @param  \App\Http\Requests\StoreCampaign_ProcessRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCampaign_ProcessRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Campaign_Process  $campaign_Process
     * @return \Illuminate\Http\Response
     */
    public function show(Campaign_Process $campaign_Process)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Campaign_Process  $campaign_Process
     * @return \Illuminate\Http\Response
     */
    public function edit(Campaign_Process $campaign_Process)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateCampaign_ProcessRequest  $request
     * @param  \App\Models\Campaign_Process  $campaign_Process
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCampaign_ProcessRequest $request, Campaign_Process $campaign_Process)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Campaign_Process  $campaign_Process
     * @return \Illuminate\Http\Response
     */
    public function destroy(Campaign_Process $campaign_Process)
    {
        //
    }

}
