<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Redirect,Response,DB,Config;
use Mail;
class EmailController extends Controller
{
    public function sendEmail($email, $text)
    {
        $data['title'] = "Ваш пароль: ";
        $data['password'] = $text;

        Mail::send('emails.email', $data, function($message) {

            $message->to('filonenkoandriysarmat@gmail.com', 'Receiver Name')

                ->subject('Вітаємо з реєстрацією в My School');
        });

        if (Mail::failures()) {
            return response()->Fail('Sorry! Please try again latter');
        }else{
            return response()->json('Great! Successfully send in your mail');
        }
    }
}
