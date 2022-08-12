<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use IvoPetkov\HTML5DOMDocument;

class SendEmailPreview
//    implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $body, $subject, $imageName, $store, $sendEmail;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($body, $subject, $imageName, $store, $sendEmail)
    {
        $this->body = $body;
        $this->subject = $subject;
        $this->imageName = $imageName;
        $this->store = $store;
        $this->sendEmail = $sendEmail;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $body = $this->body;
        $subject = $this->subject;
        $store = $this->store;
        $sendEmail = $this->sendEmail;

        Mail::send('mail.emailPreview', compact('body' ), function ($email) use ($subject, $store,$sendEmail) {
            $email->from($store->email);
            $email->to($sendEmail)->subject($subject);
        });

    }
}
