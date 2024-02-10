<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
class AuthController extends BaseController
{
  
    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
   
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')->plainTextToken;
        $success['name'] =  $user->name;
   
        return $this->sendResponse($success, 'User register successfully.');
    }
   
    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $user = Auth::user(); 
            $success['token'] =  $user->createToken('MyApp')->plainTextToken; 
            $success['name'] =  $user->name;
   
            return $this->sendResponse($success, 'User login successfully.');
        } 
        else{ 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        } 
    }

        public function user(Request $request)
    {
        return response()->json($request->user());
    }
    /**
     * Forget Password api
     *
     * @return \Illuminate\Http\Response
     */

     public function forgotPassword(Request $request)
    {  
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        $success['token'] =  $user->createToken('MyApp')->plainTextToken; 
        $success['email'] =  $user->email; 
        return $this->sendResponse($success, 'Reset your Password ');
    }
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            // 'token' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    // 'remember_token' => Str::random(60), // Optional: Generate a new remember token
                ])->save();
            }
        );
        //   if($status != Password::PASSWORD_RESET){
        //      throw ValidationException::withMessages([
        //         'email' => [__($status)],
        //      ]);
        //   }
        return $status == Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }
        public function logout(Request $request)
        { 
            
            if ($request->user()) {
             $request->user()->tokens()->delete();
            return response()->json(['message' => 'Logged out successfully']);
            } else {
                return response()->json(['message' => 'User is not authenticated'], 401);
            }
        }
}
