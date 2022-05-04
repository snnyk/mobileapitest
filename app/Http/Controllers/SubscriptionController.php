<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscriptionPurchaseRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

class SubscriptionController extends Controller
{
    public function purchase(SubscriptionPurchaseRequest $request)
    {
        //mock http request
        $this->mockHttp($request->receipt);

        try{
            //get app credentials
            $credentials = config("subscription.{$request->user()->operating_system}");

            //try subscription purchase
            $response = Http::withBasicAuth($credentials['username'], $credentials['password'] )
                ->post($credentials['api_url']);

            if($response->successful()){

                $body = json_decode($response->body());

                $request->user()->subscription()->updateOrCreate(
                    ['receipt' => $request->receipt],
                    [
                        'status' => 1,
                        'expire_date' => $expire_date = Carbon::parse($body->expire_date)->format("Y-m-d H:i:s")
                    ]
                );

                return[
                    'status' => true,
                    'mesage' => 'Purchase confirmed',
                    'expire_date' => $expire_date
                ];
            }else{
                return [
                    'status' => false,
                    'message' => 'Purchase not confirmed'
                ];
            }

        }catch(Exception $e){
            //return the error for mobile client
            abort(422 , 'An error occurred');
            //handle the caught error with custom server, sentry, etc.
            // $e->getMessage();
        }
    }
    
    /**
     * mockHttp
     * mock http requests by last char of receipt value
     * @param  mixed $receipt
     * @return void
     */
    protected function mockHttp($receipt)
    {
        Http::fake(function () use($receipt) {
            
            $last_char = substr( $receipt, -1);

            if($last_char % 2 === 0){
                return Http::response('purchase error', 401);
            }else{
                return Http::response([
                    // UTC -6 
                    'expire_date' => Carbon::now()->addMonth()->subHour(6)
                ], 200);
            }
        });
    }

    
    /**
     * check
     * 
     * @param  mixed $request
     * @return array
     */
    public function check(Request $request)
    {
        //get bearer token
        $token = $request->bearerToken();

        //cache all response 
        return Cache::remember("subscription_check_{$token}", 3600*24 , function () use($request) {

            if($request->user()->subscription()->exists()){

                $subscription = $request->user()->subscription()->first();
                //returns expired subscription
                return [
                    'status' => !$subscription->isExpired(),
                    'mesage' => ($subscription->isExpired()) ? 'Subscription expired' : 'Subscription contunues',
                    'expire_date' => $subscription->expire_date
                ];
            }else{
                return [
                    'status' => false,
                    'message' => 'Subscription not found'
                ];
            }
            
        });
    }
}
