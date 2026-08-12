<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Notifications\AdminCreatedNotification;
use App\Notifications\SystemBackupCompletedNotification;
use App\Notifications\HighTrafficAlertNotification;

class SendTestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:test';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Send test notifications to all super admins';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $superAdmins = User::where('role', 'super_admin')->get();

        if ($superAdmins->isEmpty()) {
            $this->error('No super admins found in system');
            return;
        }

        $this->info("Sending test notifications to {$superAdmins->count()} super admin(s)...");

        foreach ($superAdmins as $superAdmin) {
            // Test Admin Created Notification
            $testAdmin = User::create([
                'name' => 'Test Admin ' . now()->timestamp,
                'email' => 'test-admin-' . now()->timestamp . '@example.com',
                'password' => bcrypt(env('TEST_USER_PASSWORD', 'password123')),
                'role' => 'admin',
            ]);
            
            $superAdmin->notify(new AdminCreatedNotification($testAdmin));
            $this->info("✓ Admin Created notification sent");

            // Test System Backup Notification
            $superAdmin->notify(new SystemBackupCompletedNotification('success', 'Database backup completed at ' . now()->format('Y-m-d H:i:s')));
            $this->info("✓ System Backup notification sent");

            // Test High Traffic Alert
            $superAdmin->notify(new HighTrafficAlertNotification([
                'requests_per_minute' => rand(100, 500),
                'connected_users' => rand(10, 100),
            ]));
            $this->info("✓ High Traffic Alert notification sent");
        }

        $this->info('All test notifications sent successfully!');
    }
}
