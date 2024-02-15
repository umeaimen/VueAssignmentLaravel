<?php

use App\Http\Controllers\API\AuthController;
use  App\Http\Controllers\API\User\UserController;
use App\Http\Controllers\API\feedback\FeedBackController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::controller(AuthController::class)->group(function(){
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::get('feedback/index', [FeedbackController::class, 'index']);
});
Route::group(['middleware' => 'auth:sanctum'], function() {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user', [AuthController::class, 'user']);
    Route::post('user/update-profile', [UserController::class, 'updateProfile']);
    Route::post('user/update-password', [UserController::class, 'updatePassword']);;
    Route::get('/user/feedback', [FeedbackController::class, 'userFeedback']);
    Route::resource('feedback', FeedbackController::class);;
  });

