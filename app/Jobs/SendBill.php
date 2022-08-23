<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendBill implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $store;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($store)
    {
        $this->store = $store;
    }

    /**
     * Send bill payment to shop owner
     *
     * @return void
     */
    public function handle()
    {
        $store = $this->store;

        Mail::send('mail.billEmail', compact('store'), function ($email) use ($store) {
            $email->from('huskadian@huska.husky.russian')
                ->subject('Thank you payment')
                ->to($store->email);
        });
    }
}
