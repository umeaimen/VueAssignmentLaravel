<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\User\ProfileRequest;
use App\Http\Requests\User\ChangePasswordRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function updateProfile(ProfileRequest $request)
    {
        try {
            $user = Auth::user();
            $user->update($request->all());
            $userResource = new UserResource($user);
            return response()->json(['message' => 'User profile updated successfully', 'user' => $userResource], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update user profile', 'error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function updatePassword(ChangePasswordRequest $request)
    {
        try {
            $user = Auth::user();
            $request->merge([
                'password' => Hash::make($request->password),
            ]);
            $user->update($request->all());
            $userResource = new UserResource($user);
            return response()->json(['message' => 'User password updated successfully', 'user' => $userResource], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update user password', 'error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
