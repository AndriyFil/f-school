<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
class EmailController extends Controller
{
    public function sendEmail($email = "filonenkoandriysarmat@gmail.com", $text = "AAA")
    {
        $data['title'] = "Ваш пароль: ";
        $data['text'] = $text;

        Mail::send('emails.email', $data, function($message) {

            $message->to('filonenkoandriysarmat@gmail.com', 'AAAA')

                ->subject('Вітаємо з реєстрацією в School');
        });

        if (Mail::failures()) {
            return response()->Fail('Sorry! Please try again latter');
        }else{
            return response()->json('Great! Successfully send in your mail');
        }
    }
}
