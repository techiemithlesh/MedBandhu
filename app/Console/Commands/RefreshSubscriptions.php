<?php

namespace App\Console\Commands;

use App\Support\SubscriptionService;
use Illuminate\Console\Command;

class RefreshSubscriptions extends Command
{
    protected $signature = 'hms:refresh-subscriptions';

    protected $description = 'Move lapsed trials/periods to past_due and re-derive each hospital\'s access flag. Run daily.';

    public function handle(SubscriptionService $subs): int
    {
        $n = $subs->refreshAll();
        $this->info("Refreshed subscriptions — {$n} changed.");

        return self::SUCCESS;
    }
}
