<?php


	namespace App\Http\Controllers;
    use App\Administration;
    use App\User;
    use App\School;
    use Carbon\Carbon;
    use Validator;
    use Exception;
    use GuzzleHttp\Client;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Auth;
    use Laravel\Passport\Client as OClient;
    use App\Http\Controllers\EmailController as Email;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Str;

	class AuthController
	{
        public $successStatus = 200;

        public function register(Request $request) {
//            $validator = Validator::make($request->all(), [
//                'email' => 'required|email|unique:users'
//                , 'phone_number'
//                , 'firstname' => 'required'
//                , 'lastname' => 'required'
//                , 'middlename'
//                , 'school_name' => 'required'
//                , 'city' => 'required'
//                , 'region' => 'required'
//            ]);

            $request->validate([
                'email' => 'required|email|unique:users'
                , 'password'
            ]);

            $user = new User();
            $user->email = $request->email;
            $user->password = bcrypt(1);
            if($user->save()) {
                return response()->json([
                    'message' => 'User created'
                    , 'status_code' => 201
                ], 201);
            } else {
                return response()->json([
                    'message' => 'Some error occurred'
                    , 'status_code' => 500
                ], 500);
            }
//            try {
//                $password = Str::random(8);
//                $input['email'] = $request->email;
//                $input['password'] = bcrypt($password);
//                $user = User::create($input);
//
//                //$oClient = OClient::where('password_client', 1)->first();
//                //$this->getTokenAndRefreshToken($oClient, $user->email, $password);
//                $school = new School;
//                $school->sch_name = $request->school_name;
//                $school->sch_city = $request->city;
//                $school->sch_region = $request->region;
//                $school->save();
//                $admin = new Administration;
//                $admin->adm_firstname = $request->firstname;
//                $admin->adm_lastname = $request->lastname;
//                $admin->adm_middlename = $request->middlename;
//                $admin->adm_phone_number = $request->phone_number;
//                $admin->adm_email = $request->email;
//                $admin->adm_user_id = $user->id;
//                $admin->adm_type_id = '1';
//                $admin->adm_school_id = $school->id;
//                $admin->save();
//                $email = new Email;
//                $email->sendEmail($request->email, $password);
//            } catch (Exception $e) {
//                return response()->json(false);
//            }
//            return response()->json(true);
        }

        public function login(Request $request)
        {
            if (!Auth::attempt(['email' => $request->email_login, 'password' => $request->password])) {
                return response()->json([
                   'message' => 'Unauthorized'
                    , 'status_code' => '401'
                ], 401);
            }

            $user = $request->user();
            if($user->role == 'admin') {
                $tokenData = $user->createToken('Personal Access Token', ['do_anything']);
            } else {
                $tokenData = $user->createToken('Personal Access Token', ['can_create']);
            }

            $token = $tokenData->token;
            if($request->remember_me) {
                $token->expires_at = Carbon::now()->addWeeks(1);
            }
            if($token->save()) {
                return response()->json([
                    'user' => $user
                    , 'access_token' => $tokenData->accessToken
                    , 'access_type' => 'Bearer'
                    , 'token_scope' => $tokenData->token->scopes[0]
                    , 'expires_at' => Carbon::parse($tokenData->token->expires_at)->toDateTimeString()
                    , 'status_code' => 200
                ], 200);
            } else {
                return response()->json([
                    'message' => 'Some error occurred'
                    , 'status_code' => 500
                ], 500);
            }
        }

        public function logout (Request $request) {
            $request->user()->token()->revoke();
            return response()->json([
                'message' => 'Logout successfully'
                , 'status_code' => 200
            ], 200);
        }
	}
