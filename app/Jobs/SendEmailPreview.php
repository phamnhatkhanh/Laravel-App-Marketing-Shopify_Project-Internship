<?php

namespace App\Jobs;


use Throwable;
use App\Mail\WelcomeMail;
use App\Events\SendingMail;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use IvoPetkov\HTML5DOMDocument;

class SendEmailPreview implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Get body Email after handle with dom
     *
     * @var object
     */
    public $body;

    /**
     * Get subject Email after handle with dom
     *
     * @var object
     */
    private $subject;

    /**
     * Get name image
     *
     * @var object
     */
    private $imageName;

    /**
     * Get Shop owner information have token
     *
     * @var array
     */
    private $store;

    /**
     *
     * @var object
     */
    private $sendEmail;
    public $batchId;
    private $campaignProcess;


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($sendEmail, $batchId, $campaignProcess, $body, $subject, $imageName, $store)
    {
        $this->connection = 'database';

        $this->body = $body;
        $this->subject = $subject;
        $this->imageName = $imageName;
        $this->store = $store;
        $this->sendEmail = $sendEmail;
        $this->batchId = $batchId;
        $this->campaignProcess = $campaignProcess;
    }

    /**
     * Send Campaign to Customers
     *
     * @return void
     */
    public function handle()
    {
        // try {
        info("SendEmailPreview: campaignProcess id" . $this->campaignProcess->id);
        info("SendEmailPreview: batch id" . $this->batchId);
        info("SendEmailPreview: send mail " . $this->sendEmail);

        $bodyEmail = $this->body;
        $subject = $this->subject;
        $store = $this->store;
        $sendEmail = $this->sendEmail;
        info("SendEmailPreview: send mail......");
        // info("body ".$this->batchId ."  processed". $this->campaignProcess->id);
        Mail::send('mail.emailPreview', compact('bodyEmail'), function ($email) use ($subject, $store, $sendEmail) {
            $email->from($store->email);
            $email->to($sendEmail)->subject($subject);
        });
        info("SendEmailPreview: call event");
        event(new SendingMail($this->batchId, $this->campaignProcess));
    }

    public function failed(Throwable $exception)
    {
        info("job failed: ");
    }
}
