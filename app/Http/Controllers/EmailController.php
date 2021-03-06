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
    public $email = "";
    public function sendEmail($email = "filonenkoandriysarmat@gmail.com", $text = "AAA")
    {
        $data['title'] = "Ваш пароль: ";
        $data['text'] = $text;
        $this->email = $email;
        Mail::send('emails.email', $data, function($message) {

            $message->to($this->email, 'Реєстрація')

                ->subject('Вітаємо з реєстрацією в системі "Моя Школа" ');
        });

        if (Mail::failures()) {
            return response()->Fail('Sorry! Please try again latter');
        }else{
            return response()->json('Great! Successfully send in your mail');
        }
    }
}
