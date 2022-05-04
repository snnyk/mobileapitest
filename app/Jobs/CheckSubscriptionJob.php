<?php

namespace App\Jobs;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class CheckSubscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected $subscription;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $this->mockHttp();

        try{
            //get app credentials
            $credentials = config("subscription.{$this->subscription->device->operating_system}");
            
            //check subscription status
            $response = Http::withBasicAuth($credentials['username'], $credentials['password'] )
                ->post($credentials['api_url']);

            if($response->successful()){
                $body = json_decode($response->body());
                $this->subscription->update(['status' => $body->status]);
            }else{
                //log failed attemps
            }
        }catch(\Exception $e){
            //handle the caught error with custom server, sentry, etc.
            // $e->getMessage();
        }
    }


    protected function mockHttp()
    {
        Http::fake(function () {
            
            //generate random status
            $last_char = substr( rand(1,99) , -1);
            $status = ($last_char % 2 === 0) ? 1 : 0;

            return Http::response([
                'status' => $status
            ], 200);
        });
    }
}
