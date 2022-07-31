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

    public $user;
    public $batch_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($batch_id,$user)
    {
        $this->user   = $user;
        $this->batch_id = $batch_id;
    }

    public function handle()
    {
        info('Excuting mail: '.$this->user .'  ' . $this->batch_id);

        Mail::to($this->user)->send(new WelcomeMail());
        if (Mail::failures() != 0) {
            event(new SendingMail($this->batch_id));
            // return "Email has been sent successfully.";
        }
        // info ('please check server');


    }


    public function failed(Throwable $exception)
    {
        info("job failed: ");
    }
}