<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\API\RegisterRequest;
use App\Http\Requests\API\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class AuthController extends BaseController
{

    /**
     * Register api
     *
     * @return \Illuminate\Http\Response
     */
    public function register(RegisterRequest $request)
    { 
        try {
        $request->merge([
            'password' => Hash::make($request->password),
        ]);
        $user = User::create($request->all());
        $userResource = new UserResource($user);
        $token = $user->createToken('access_token')->plainTextToken;
        return response()->json([
            'user' => $userResource,
            'token' => $token,
        ], JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to register user'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Login api
     *
     * @return \Illuminate\Http\Response
     */
    public function login(LoginRequest $request)
    {
        try {
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $user = Auth::user();
                $userResource = new UserResource($user);
                $token = $user->createToken('access_token')->plainTextToken;
                return response()->json([
                    'user' => $userResource,
                    'token' => $token,
                ], JsonResponse::HTTP_OK);
            } else {
                return response()->json(['message' => 'Unauthorised'], JsonResponse::HTTP_UNAUTHORIZED);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to log in'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
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
    public function logout(Request $request)
    {
        try {
            if ($request->user()) {
                $request->user()->currentAccessToken()->delete();
                return response()->json(['message' => 'Logged out successfully'], JsonResponse::HTTP_OK);
            } else {
                return response()->json(['message' => 'User is not authenticated'], JsonResponse::HTTP_UNAUTHORIZED);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while logging out'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }        
    }
}