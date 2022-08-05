<?php

namespace App\Jobs;

use App\Mail\AttachmentMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $store, $fileName, $request;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($store, $fileName, $request)
    {
        $this->store = $store;
        $this->fileName = $fileName;
        $this->request = $request;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $store = $this->store;
        $fileName = $this->fileName;
        $request = $this->request;

        Mail::send('mail.attachment', compact('store' ), function ($email) use ($store, $fileName, $request) {
            $email->subject('Test2221.');
            $email->to($request);
            $email->attach('storage/app/'.$fileName);

        });
    }
}
