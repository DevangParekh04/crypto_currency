<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use GuzzleHttp\Client;
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

Route::post('/login',[AuthenticationController::class,'login']);
Route::post('/register',[AuthenticationController::class,'register']);
Route::post('/send-otp',[AuthenticationController::class,'sendOtp']);
Route::post('/verify-email',[AuthenticationController::class,'verifyEmail']);
Route::post('/reset-password', [AuthenticationController::class, 'resetPassword']);
Route::get('/get_cripto_currency', [AuthenticationController::class, 'getCriptoCurrency']);

Route::post('/notification',[UserController::class,'notification']);
Route::middleware('auth:api')->group(function (){
    Route::get('/get-user-holdings',[UserController::class,'getUserHolding']);
    Route::get('/get-user-transactions',[UserController::class,'getUserTransaction']);
    Route::post('/add-buy',[UserController::class,'addBuy']);
    Route::get('/get-currency',[UserController::class,'getCurrency']);
    Route::get('/get-currency',[UserController::class,'getCurrency']);


});
