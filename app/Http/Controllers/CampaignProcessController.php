<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\Database\CreatedModel;
use App\Events\Database\UpdatedModel;
use App\Events\Database\DeletedModel;
use Carbon\Carbon;

use Illuminate\Support\Facades\Schema;
use App\Models\CampaignProcess;


class CampaignProcessController extends Controller
{
    protected $campaignProcess;

    public function __construct(){
        $this->campaignProcess = getConnectDatabaseActived(new CampaignProcess());
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return $this->campaignProcess->get();
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $request['created_at'] = Carbon::now()->format('Y-m-d H:i:s');;
        $request['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');;

        Schema::connection($this->campaignProcess->getConnection()->getName())->disableForeignKeyConstraints();
            $campaignProcess = $this->campaignProcess->create($request->all());
        Schema::connection($this->campaignProcess->getConnection()->getName())->enableForeignKeyConstraints();

            // dd($campaignProcess);
        // $campaignProcess = $this->campaignProcess->where('id', $request['id'])->first();
        $connect = ($this->campaignProcess->getConnection()->getName());
        event(new CreatedModel($connect,$campaignProcess));
        return response([
            'data' => $campaignProcess
        ],201);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // dd("yktjyuom");

         $this->campaignProcess->where('id',$id)->update($request->all());
         $campaignProcess  = ($this->campaignProcess->where('id',$id)->first());
        //  dd($campaignProcess);
        $connect = ($this->campaignProcess->getConnection()->getName());
        // dd($connect);
        event(new UpdatedModel($connect,$campaignProcess));

        return response([
            'data' => $campaignProcess
        ],201);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // dd("t4673829wijs");
        $campaignProcess = $this->campaignProcess->where('id',$id)->first();
        if(!empty($campaignProcess)){
            $connect = ($this->campaignProcess->getConnection()->getName());
            event(new DeletedModel($connect,$campaignProcess));
            // $campaignProcess->delete();
            // return $campaignProcess;
            return response([
                'data' => $campaignProcess,
                'mess' => "dleete campaignProcess done"
            ],201);
        }
        return response([
                'mess' => "can not fin campaigns"
            ],201);

    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

}
