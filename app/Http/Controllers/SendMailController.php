<?php

namespace App\Http\Controllers;

use App\Mail\AttachmentMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SendMailController extends Controller
{
    public function email(){
        Mail::to('giakinh451@gmail.com')->send(new AttachmentMail());
    }
}
