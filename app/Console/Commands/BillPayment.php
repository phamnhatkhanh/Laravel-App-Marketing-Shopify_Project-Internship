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

        $this->line('Start process send mail and sms bill for merchant.');

        $listStore = $storeModelBuilder->get();
        foreach ($listStore as $item){
            if ($item->status == 'installed'){
                dispatch(new SendBill($item));

                SendBillSMS::sendBillSMS($item);
            }
        }

        $this->line('Done nofication bill for merchant.');

    }
}
