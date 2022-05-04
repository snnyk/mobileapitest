<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Models\Device;
use Exception;
use Illuminate\Support\Facades\Cache;

class AuthController extends Controller
{    
    /**
     * auth
     *
     * @param  mixed $request
     * @return void
     */
    public function auth(AuthRequest $request)
    {
        try{

            $device = Device::updateOrCreate(
                [
                    'uid' => $request->uid,
                    'app_id' => $request->app_id
                ],
                [
                    'language' => $request->language,
                    'operating_system' => $request->operating_system
                ]
            );

            //cache token 
            $token = Cache::remember("token_{$device->id}", 3600*24 , function() use ($device) {
                return $device->createToken('subscription')->plainTextToken;
            });

            return ['token' => $token];
        
        }catch(Exception $e){
            //return the error for mobile client
            abort(422 , 'An error occurred');
            //handle the catched error with custom server, sentry, etc.
            // $e->getMessage();
        }
    }
}
