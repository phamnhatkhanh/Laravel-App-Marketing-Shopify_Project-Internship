<?php

namespace App\Console\Commands;

use App\Jobs\SendBill;
use App\Models\Store;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class BillPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bill:payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $storeID = GetStoreID();
        $store = Store::where('id', $storeID)->first();
        dispatch(new SendBill($store));

        $account_sid = env('TWILIO_SID');
        $auth_token = env('TWILIO_AUTH_TOKEN');
        $twilio_phone_number = env('TWILIO_NUMBER');
        // $twilio_phone_number = env('TWILIO_NUMBER');
        $phone = $store->phone;
        $client = new Client($account_sid, $auth_token);

        $client->messages->create(
            $phone,
            [
                "from" => $twilio_phone_number,
                "body" => "Thank you payment '.$store->name_merchant.' \n
                            "
            ]
        );

        return;
    }
}
