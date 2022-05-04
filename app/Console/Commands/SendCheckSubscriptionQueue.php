<?php

namespace App\Console\Commands;

use App\Jobs\CheckSubscriptionJob;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendCheckSubscriptionQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for expired subscriptions';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $count = 0;

        Subscription::whereDate('expire_date', '<=', Carbon::today())->whereStatus(1)->each(function($subscription) use(&$count){
            CheckSubscriptionJob::dispatch($subscription);
            $count++;
        });

        $this->info("$count subscription queued for check" . Carbon::today());

    }
}
