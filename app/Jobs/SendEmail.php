<?php

namespace App\Jobs;

use Throwable;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

use App\Models\Customer;


class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Get path and name File CSV Customers when export
     *
     * @var object
     */
    private $fileName;

    /**
     * Get Shop owner information have token
     *
     * @var array
     */
    private $store;

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
     * Send File Information Customer to shop owner
     *
     * @return void
     */
    public function handle()
    {
        $fileName = $this->fileName;
        $store = $this->store;

        Mail::send('mail.attachment', compact('store'), function ($email) use ($fileName, $store) {
            $email->from($store->email);
            $email->subject('Backup data CSV From: ' . $store->name_merchant);
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
