<?php

namespace App\Http\Controllers;

use App\Administration;
use App\User;
use App\School;
use Validator;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Client as OClient;
use App\Http\Controllers\EmailController as Email;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public $successStatus = 200;

    public function login(Request $request) {
        if (Auth::attempt(['email' => request('email'), 'password' => request('password')])) {
            $oClient = OClient::where('password_client', 1)->first();
            $guzzle = new GuzzleHttp\Client;

            return $this->getTokenAndRefreshToken($oClient, request('email'), request('password'));

        } else {
            return response()->json(['error'=>'Unauthorised'], 401);
        }
    }

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users'
            , 'phone_number'
            , 'firstname' => 'required'
            , 'lastname' => 'required'
            , 'middlename'
            , 'school_name' => 'required'
            , 'city' => 'required'
            , 'region' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }

        try {
            $password = Str::random(8);
            $input['email'] = $request->email;
            $input['password'] = bcrypt($password);
            $user = User::create($input);
            $oClient = OClient::where('password_client', 1)->first();
            $this->getTokenAndRefreshToken($oClient, $user->email, $password);
            $school = new School;
            $school->sch_name = $request->school_name;
            $school->sch_city = $request->city;
            $school->sch_region = $request->region;
            $school->save();
            $admin = new Administration;
            $admin->adm_firstname = $request->firstname;
            $admin->adm_lastname = $request->lastname;
            $admin->adm_middlename = $request->middlename;
            $admin->adm_phone_number = $request->phone_number;
            $admin->adm_email = $request->email;
            $admin->adm_user_id = $user->id;
            $admin->adm_type_id = '1';
            $admin->adm_school_id = $school->id;
            $admin->save();
            $email = new Email;
            $email->sendEmail($request->email, $password);
        } catch (Exception $e) {
            return response()->json(false);
        }
        return response()->json(true);
    }

    public function getTokenAndRefreshToken(OClient $oClient, $email, $password) {
        $oClient = OClient::where('password_client', 1)->first();

        $http = new Client;
        $response = $http->request('POST', 'http://192.168.1.6:8079/oauth/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $oClient->id,
                'client_secret' => $oClient->secret,
                'username' => $email,
                'password' => $password,
                'scope' => '*',
            ],
        ]);

        $result = json_decode((string) $response->getBody(), true);
        return response()->json($result, $this->successStatus);
    }

    public function details() {
        $user = Auth::user();
        return response()->json($user, $this->successStatus);
    }

    public function logout(Request $request) {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    public function unauthorized() {
        return response()->json("unauthorized", 401);
    }


    //TODO
    public function refreshToken(Request $request) {
        $refresh_token = $request->header('Refreshtoken');
        $oClient = OClient::where('password_client', 1)->first();
        $http = new Client;

        try {
            $response = $http->request('POST', 'http://192.168.1.6:8079/oauth/token', [
                'form_params' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => $refresh_token,
                    'client_id' => $oClient->id,
                    'client_secret' => $oClient->secret,
                    'scope' => '*',
                ],
            ]);
            return json_decode((string) $response->getBody(), true);
        } catch (Exception $e) {
            return response()->json("unauthorized", 401);
        }
    }
}
