<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubscriptionController;
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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/auth' , [AuthController::class , 'auth']);

Route::group(['prefix' => '/subscription' , 'middleware' => 'auth:sanctum'] , function(){
    Route::post('/purchase' , [SubscriptionController::class , 'purchase']);
    Route::post('/check' , [SubscriptionController::class , 'check']);
});
