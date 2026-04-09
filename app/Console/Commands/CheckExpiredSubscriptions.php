<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Subscription;
use Carbon\Carbon;

class CheckExpiredSubscriptions extends Command
{
    protected $signature = 'subscriptions:check-expired';
    protected $description = 'Mark expired subscriptions as expired';

    public function handle()
    {
        $expired = Subscription::where('status', 'active')
            ->where('ends_at', '<', Carbon::now())
            ->update(['status' => 'expired']);

        $this->info("Expired {$expired} subscriptions.");
    }
}