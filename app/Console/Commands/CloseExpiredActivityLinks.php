<?php

namespace App\Console\Commands;

use App\Models\ActivityLink;
use Illuminate\Console\Command;

class CloseExpiredActivityLinks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'activity-links:close-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close activity links that have passed their expiration date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = ActivityLink::whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->where('is_open', true)
            ->update(['is_open' => false]);

        $this->info("Closed {$count} expired activity links.");

        logger()->info("Closed {$count} expired activity links.");

        return Command::SUCCESS;
    }
}
