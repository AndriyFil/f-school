<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
    Route::get('/get_regions', 'WelcomeController@getRegions');
    Route::get('/get_cities/{id}', 'WelcomeController@getCities');


    Route::get('send_email', 'EmailController@sendEmail');


    //first try with authorization
    Route::post('login', 'UserController@login');
    Route::post('register', 'UserController@register');
    Route::post('refreshtoken', 'UserController@refreshToken');
    Route::get('/unauthorized', 'UserController@unauthorized');

    Route::group(['middleware' => ['CheckClientCredentials','auth:api']], function() {
        Route::post('logout', 'UserController@logout');
        Route::post('details', 'UserController@details');
    });

    Route::group(['prefix' => 'auth'], function(){
        Route::post('login', 'AuthController@login');
        Route::post('register', 'AuthController@register');

        Route::group(['middleware' => 'auth:api'], function() {
            Route::post('logout', 'AuthController@logout');
        });
    });

    Route::group(['prefix' => 'user'], function() {
        Route::group(['middleware' => 'auth:api'], function() {
            Route::post('edit_category', function() {
                return response()->json([
                    'message' => 'Admin success'
                    , 'status_code' => 200
                ], 200);
            })->middleware('scope:do_anything');
            Route::post('create-category', function() {
                return response()->json([
                    'message' => 'Everyone success'
                    , 'status_code' => 200
                ], 200);
            })->middleware('scope:do_anything, can_create');
         });
   });

