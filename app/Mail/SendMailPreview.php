<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use IvoPetkov\HTML5DOMDocument;

class SendMailPreview extends Mailable
{
    use Queueable, SerializesModels;

    private $body, $sub, $imageName, $store, $sendEmail;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($body, $sub, $imageName, $store, $sendEmail)
    {
        $this->body = $body;
        $this->sub = $sub;
        $this->imageName = $imageName;
        $this->store = $store;
        $this->sendEmail = $sendEmail;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $dom = new HTML5DOMDocument();
        $dom->loadHTML($this->body);
        if (!empty($this->imageName)) {
            $img = $dom->getElementsByTagName('img')[0];
            $img->setAttribute('src', asset('uploads/' . $this->imageName));
        }

        $body = $dom->saveHTML();
        $body = str_replace('Customer_Full_name',  $this->store->name_merchant ?? '', $body);
        $body = str_replace('Customer_First_name', $this->store->name_merchant ?? '', $body);
        $body = str_replace('Customer_Last_name', $this->store->name_merchant ?? '', $body);
        $body = str_replace('Shop_name', $this->store->name_merchant ?? '', $body);
        $this->body = $body;

        $subject = $this->subject;
        $sendEmail = $this->sendEmail;

        Mail::send('mail.emailPreview', compact('body' ), function ($email) use ($subject, $sendEmail) {
            $email->subject($subject);
            $email->to($sendEmail);
        });

        //        Mail::to($request->send_email)->send(new SendMailPreview($body, $request->subject, $imageName, $store, $request->send_email));

        //        return $this->subject($this->subject)->view('mail.emailPreview', compact('body'));

    }
}
