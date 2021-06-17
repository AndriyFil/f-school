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
    use Illuminate\Support\Facades\DB;
	class AuthController
	{
        public $successStatus = 200;
        private $role_admin = "admin";
        private $role_teacher = "teacher";
        private $role_parent = "parent";
        private $role_schoolboy = "schoolboy";
        private $photo_path =  "img/user/elon-musk.png";
        public function register(Request $request) {
            $request->validate([
                'email' => 'required|email|unique:users'
                , 'phone_number'
                , 'firstname' => 'required'
                , 'lastname' => 'required'
                , 'middlename'
                , 'school_name' => 'required'
                , 'city' => 'required'
                , 'region' => 'required'
            ]);

            DB::beginTransaction();
            try {
                $school = new School;
                $school->sch_name = $request->school_name;
                $school->sch_city = $request->city;
                $school->sch_region = $request->region;
                $school->save();
                $user = new User();
                $password = Str::random(8);
                $user->email = $request->email;
                $user->phone_number = $request->phone_number;
                $user->firstname = $request->firstname;
                $user->lastname = $request->lastname;
                $user->middlename = $request->middlename;
                $user->photo = $this->photo_path;
                $user->school_id = $school->sch_id;
                $user->password = bcrypt($password);
                $user->save();
                $admin = new Administration;
                $admin->adm_user_id = $user->id;
                $admin->adm_type_id = '1';
                $admin->save();
                $email = new Email;
                $email->sendEmail($request->email, $password);
                DB::commit();
            } catch (Exception $e) {
                DB::rollback();
                return response()->json([
                    'message' => 'Some error occurred'
                    , 'status_code' => 500
                ], 500);
            }
            return response()->json([
                'message' => 'User created'
                , 'status_code' => 201
            ], 201);
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

        public function profile () {
            $user = Auth::user();
            if($user) {
                return response()->json($user, 200);
            }

            return response()->json([
                'message' => 'Not logged in'
                , 'status_code' => 500
            ], 500);
        }
	}
