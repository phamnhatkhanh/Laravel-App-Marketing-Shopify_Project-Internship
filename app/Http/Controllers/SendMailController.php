<?php

namespace App\Http\Controllers;


use App\Jobs\SendEmail;
use App\Mail\AttachmentMail;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SendMailController extends Controller
{

    public function email(){
        $user = Customer::first();

//        dispatch(new SendEmail($user));

        return ;
    }
}
