<?php

namespace App\Jobs;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;
class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $fileName, $store;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($fileName, $store)
    {
        $this->fileName = $fileName;
        $this->store = $store;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $fileName = $this->fileName;
        $store = $this->store;

        Mail::send('mail.attachment', compact('store' ), function ($email) use ($fileName, $store) {
            $email->from($store->email);
            $email->subject('Backup data CSV From: '.$store->name_merchant);
            $email->to($store->email);
            $email->attach($fileName);
        });
    }
    public function failed(Throwable $exception)
    {
        info("job failed: ");
        // Mail::failures()
    }
}
