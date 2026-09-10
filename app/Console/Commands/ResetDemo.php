<?php

namespace App\Console\Commands;

use App\Support\DemoResetService;
use Illuminate\Console\Command;

class ResetDemo extends Command
{
    protected $signature = 'hms:reset-demo';

    protected $description = 'Wipe the public demo hospital and rebuild it from the Demo* seeders. Run nightly.';

    public function handle(DemoResetService $service): int
    {
        if (! config('hms.demo.enabled')) {
            $this->warn('Demo mode is disabled (hms.demo.enabled) — nothing to do.');

            return self::SUCCESS;
        }

        $this->info('Resetting demo hospital…');
        $service->reset();
        $this->info('Demo hospital rebuilt.');

        return self::SUCCESS;
    }
}
