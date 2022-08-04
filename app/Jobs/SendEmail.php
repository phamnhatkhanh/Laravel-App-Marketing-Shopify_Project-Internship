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

    private $store;
    private $fileName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($store, $fileName)
    {
        $this->store = $store;
        $this->fileName = $fileName;

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

        Mail::send('mail.attachment', compact('store' ), function ($email) use ($store, $fileName) {
            $email->subject('Test2221.');
            $email->to('giakinh451@gmail.com');
            $email->attach('storage/app/'.$fileName);

        });
    }
}
