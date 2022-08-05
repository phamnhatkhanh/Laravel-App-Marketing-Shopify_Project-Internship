<?php

namespace App\Jobs;

use Throwable;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

use App\Mail\WelcomeMail;
use App\Events\SendingMail;

class SendMail implements ShouldQueue
{
    // use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $MailCustomer;
    public $batchId;
    public $campaignProcessId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($batchId,$MailCustomer,$campaignProcessId)
    {
        $this->MailCustomer   = $MailCustomer;
        $this->batchId = $batchId;
        $this->campaignProcessId = $campaignProcessId;
    }

    public function handle()
    {
        info('Excuting mail: '.$this->MailCustomer .' batch ' . $this->batchId. ' campaign_id ' .$this->campaignProcessId);

        Mail::to($this->MailCustomer)->send(new WelcomeMail());
        event(new SendingMail($this->batchId,$this->campaignProcessId));
        if (Mail::failures() != 0) {
            // return "Email has been sent successfully.";
        }
        // info ('please check server');

// php artisan queue:work --queue=event
// php artisan queue:work --queue=default
    }


    public function failed(Throwable $exception)
    {
        info("job failed: ");
    }
}




