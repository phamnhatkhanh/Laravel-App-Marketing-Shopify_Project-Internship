<?php

namespace App\Console\Commands;

use Twilio\Rest\Client;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

use App\Models\Store;
use App\Jobs\SendBill;
use App\Services\SendBillSMS;


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
     * Send bill to shop owner by SMS and Email
     *
     * @return int
     */
    public function handle()
    {
        $storeModelBuilder = setConnectDatabaseActived(new Store());


        $this->line('Bắt đầu gửi mail và gửi sms thông báo bill');
        $storeID = getStoreID();
        $store = $storeModelBuilder->where('id', $storeID)->first();
        dispatch(new SendBill($store));

        SendBillSMS::sendBillSMS($store);

        $this->line('Kết thúc quá trình gửi sms và gửi mail');

        return;
    }
}
