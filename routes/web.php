<?php

use Illuminate\Support\Facades\Route;

//Route::get('/{any}', function(){
//    return view('welcome');
//})->where('any', '.*');
Route::get('/{any}', 'WelcomeController@index')->where('any', '.*')->name('welcome');

//Auth::routes();
//
//Route::get('/home', 'HomeController@index')->name('home');
