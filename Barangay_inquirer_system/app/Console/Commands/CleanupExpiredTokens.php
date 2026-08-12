<?php

namespace App\Console\Commands;

use App\Services\JwtService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupExpiredTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jwt:cleanup {--force : Run cleanup without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired JWT refresh tokens from database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Confirm with user unless --force flag is used
        if (!$this->option('force')) {
            if (!$this->confirm('This will delete all expired refresh tokens. Continue?')) {
                $this->info('Cancelled.');
                return Command::FAILURE;
            }
        }

        $this->info('Cleaning up expired refresh tokens...');

        $deleted = JwtService::cleanupExpiredTokens();

        $this->info("Deleted {$deleted} expired refresh tokens.");
        $this->line('Cleanup completed successfully.');

        return Command::SUCCESS;
    }
}
