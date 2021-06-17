<?php

use Illuminate\Support\Facades\Route;

//Route::get('/{any}', function(){
//    return view('welcome');
//})->where('any', '.*');
    Route::group(['middleware' => 'web'], function () {
        Auth::routes();


            Route::get('/', 'RootController@index')->where('any', '.*')->name('welcome');
            Route::get('/journal', 'MyClassController@index')->where('any', '.*')->name('journal');
            Route::get('/my_class', 'MyClassController@index')->where('any', '.*')->name('my_class');
//            Route::get('/', 'ParentController@index')->where('any', '.*')->name('parent');

//        Route::get('/{any}', 'BaseController@index')->where('any', '.*')->name('base');
    });
//
//Route::get('/home', 'HomeController@index')->name('home');
