<?php

namespace App\Http\Controllers;


use App\Jobs\SendEmail;
use App\Mail\AttachmentMail;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class SendMailController extends Controller
{

     protected $customer;

    public function __construct(){
        $this->customer = getConnectDatabaseActived(new Customer());

    }
    public function email(){
        $user = $this->customer->first();

//        dispatch(new SendEmail($user));

        return ;
    }
}
