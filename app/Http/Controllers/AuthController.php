<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use JWTAuth,Auth;use App\Models\User;
use Tymon\JWTAuth\Exceptions\JWTException;


class AuthController extends Controller {
    /*
      |--------------------------------------------------------------------------
      | Home Controller
      |--------------------------------------------------------------------------
      |
      | This controller renders your application's "dashboard" for users that
      | are authenticated. Of course, you are free to change or remove the
      | controller as you wish. It is just here to get your app started!
      |
     */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
      
        $this->middleware('jwt.auth', ['except' => ['signin','signin2','logout']]);
    }

    public function certifier() {
        return array('statuts' =>'success');
    }

    public function user_data(Request $request) {
        $user = JWTAuth::toUser($request->token);
        $user->profil_user;
        $user->agent_user;
        if(isset($user->agent_user))
        $user->agent_user->structure;
        return $user;
    }

    public function user_data_by_token($token) {
        $user = JWTAuth::toUser($token);
        return $user;
    }
    //authentifie un utilisateur
    public function signin(Request $request) {

        $credentials = $request->only('email', 'password');
        //dd(env('DB_HOST'));
        try {
            // verify the credentials and create a token for the user
            if (!  $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {

          \Log::error($e->getMessage());
            // something went wrong
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        $user=User::with("roles.permissions","agent.uniteAdmin",'userprestation.prestation')->where("id",Auth::id())->first();
        
        return response()->json(compact(['token','user']));

        // if no errors are encountered we can return a JWT
        

    }

    
    public function signin2(Request $request)
    {
        $check=User::whereAccessToken($request->code)->first();
        
        $credentials = ["email"=>$check->email,"password"=>"123"];
        try {
            // verify the credentials and create a token for the user
            if (!  $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {

          \Log::error($e->getMessage());
            // something went wrong
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
      //  $check->update(['access_token'=>null]);
        $user=User::with("roles.permissions","agent.uniteAdmin",'userprestation.prestation')->where("id",Auth::id())->first();
        
        return response()->json(compact(['token','user']));

        // if no errors are encountered we can return a JWT
        

    }


    public function logout()
    {
        Auth::logout();
        return response()->json([],200);

    }

    public function changePassword(Request $request){
        $user=User::find(Auth::id());
        if ($request->confirm_password != $request->new_password  ) {
            return response()->json([
                "success"=>true,
                "message"=>"Mots de passe non identique",
                "data"=>[]
            ],500);
        }elseif (!Hash::check($request->old_password,$user->password)) {
            return response()->json([
                "success"=>true,
                "message"=>"Ancien mot de passe incorrect",
                "data"=>[]
            ],500);
        }else{
            $user->update(['password'=>Hash::make($request->new_password)]);
            return response()->json([
                "success"=>true,
                "message"=>"Mot de passe changé avec succès",
                "data"=>[]
            ],200);

        }
        
    }
  }
