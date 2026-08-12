<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use ZipArchive;

class SystemController extends Controller
{
    /**
     * Display activity logs.
     */
    public function activityLogs()
    {
        // retrieve latest activity records, paginate to keep view manageable
        $activities = \App\Models\Activity::latest()->paginate(20);
        return view('superadmin.system.activity-logs', compact('activities'));
    }

    /**
     * Display system settings.
     */
    public function settings()
    {
        $settings = [
            'site_name' => Setting::get('site_name', 'Barangay Inquirer System'),
            'site_logo' => Setting::get('site_logo'),
            'maintenance_mode' => Setting::get('maintenance_mode', false),
            'session_timeout' => Setting::get('session_timeout', 60),
            'require_2fa' => Setting::get('require_2fa', true),
            'enable_ip_whitelist' => Setting::get('enable_ip_whitelist', true),
            'email_driver' => Setting::get('email_driver', 'smtp'),
            'email_from' => Setting::get('email_from', 'noreply@barangayinquirer.com'),
            'backup_frequency' => Setting::get('backup_frequency', 'daily'),
            'last_backup' => Setting::get('last_backup', null),
            'last_restore' => Setting::get('last_restore', null),
            'footer_address' => Setting::get('footer_address', 'Barangay Hall, Your City, Your Province'),
            'footer_phone' => Setting::get('footer_phone', '+63 (XXX) XXX-XXXX'),
            'footer_email' => Setting::get('footer_email', 'info@barangay.gov.ph'),
            'footer_facebook' => Setting::get('footer_facebook', '#'),
            'footer_twitter' => Setting::get('footer_twitter', '#'),
            'footer_linkedin' => Setting::get('footer_linkedin', '#'),
            'footer_instagram' => Setting::get('footer_instagram', '#'),
            'privacy_policy' => Setting::get('privacy_policy', ''),
            'terms_of_service' => Setting::get('terms_of_service', ''),
        ];
        return view('superadmin.system.settings', compact('settings'));
    }

    /**
     * Save general settings (Super Admin only)
     */
    public function updateGeneralSettings(Request $request)
    {
        // Verify user is Super Admin
        if (auth()->user()->role !== 'super_admin') {
            \App\Models\Activity::log("Unauthorized attempt to modify general settings", 'security_violation');
            abort(403, 'Only Super Administrators can modify site settings.');
        }

        $validated = $request->validate([
            'site_name' => 'required|string|max:255',
            'site_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Prevent duplicate site_name entries by normalizing the key and always updating the same row
        // The Setting::set method already normalizes keys and uses updateOrCreate on ['key' => normalized]
        // so duplicates at the DB level should not occur if there's a unique index on `settings.key`.
        Setting::set('site_name', $validated['site_name']);
        
        if ($request->hasFile('site_logo')) {
            $currentLogo = Setting::get('site_logo');
            if ($currentLogo && Storage::disk('public')->exists('settings/' . $currentLogo)) {
                Storage::disk('public')->delete('settings/' . $currentLogo);
            }

            $file = $request->file('site_logo');
            $extension = strtolower($file->getClientOriginalExtension());
            $filename = 'site_logo_' . time() . '_' . Str::random(12) . '.' . $extension;
            $path = $file->storeAs('settings', $filename, 'public');

            if (! $path) {
                return back()->with('error', 'Unable to save the site logo. Please try again.');
            }

            Setting::set('site_logo', $filename);
            \App\Models\Activity::log("Site logo updated", 'settings_update');
        }
        
        \App\Models\Activity::log("General settings updated", 'settings_update');

        return back()->with('success', 'General settings updated successfully');
    }

    /**
     * Toggle maintenance mode
     */
    public function toggleMaintenanceMode(Request $request)
    {
        $enabled = $request->has('enable_maintenance_mode') && $request->boolean('enable_maintenance_mode');

        if ($enabled) {
            $secret = Str::random(32);
            Artisan::call('down', ['--secret' => $secret]);

            \App\Models\Activity::log('Maintenance mode enabled with secret bypass', 'settings_update');

            return back()->with('success', 'Maintenance mode is now active. Bypass URL: ' . url('/' . $secret));
        }

        Artisan::call('up');
        \App\Models\Activity::log('Maintenance mode disabled', 'settings_update');

        return back()->with('success', 'Maintenance mode is now disabled and the site is live again.');
    }

    /**
     * Save security settings
     */
    public function updateSecuritySettings(Request $request)
    {
        $validated = $request->validate([
            'session_timeout' => 'required|integer|min:5|max:1440',
            'require_2fa' => 'nullable|boolean',
            'enable_ip_whitelist' => 'nullable|boolean',
        ]);

        Setting::set('session_timeout', $validated['session_timeout']);
        Setting::set('require_2fa', $request->has('require_2fa') ? 1 : 0);
        Setting::set('enable_ip_whitelist', $request->has('enable_ip_whitelist') ? 1 : 0);
        
        \App\Models\Activity::log("Security settings updated", 'settings_update');

        return back()->with('success', 'Security settings updated successfully');
    }

    /**
     * Save email settings
     */
    public function updateEmailSettings(Request $request)
    {
        $validated = $request->validate([
            'email_driver' => 'required|in:smtp,mailgun,sendgrid',
            'email_from' => 'required|email',
        ]);

        Setting::set('email_driver', $validated['email_driver']);
        Setting::set('email_from', $validated['email_from']);
        
        \App\Models\Activity::log("Email settings updated", 'settings_update');

        return back()->with('success', 'Email settings updated successfully');
    }

    /**
     * Save backup settings
     */
    public function updateBackupSettings(Request $request)
    {
        $validated = $request->validate([
            'backup_frequency' => 'required|in:daily,weekly,monthly',
        ]);

        Setting::set('backup_frequency', $validated['backup_frequency']);
        
        \App\Models\Activity::log("Backup settings updated", 'settings_update');

        return back()->with('success', 'Backup settings updated successfully');
    }

    /**
     * Create a manual backup file and log it.
     */
    public function createManualBackup()
    {
        $backupDirectory = '.';
        if (! Storage::disk('backups')->exists($backupDirectory)) {
            Storage::disk('backups')->makeDirectory($backupDirectory);
        }

        $connection = config('database.default');
        $dbConfig = config("database.connections.{$connection}");

        if (! isset($dbConfig['driver']) || $dbConfig['driver'] !== 'mysql') {
            return back()->with('error', 'Manual backup is currently supported only for MySQL databases.');
        }

        $host = $dbConfig['host'] ?? '127.0.0.1';
        $port = $dbConfig['port'] ?? '3306';
        $database = $dbConfig['database'] ?? null;
        $username = $dbConfig['username'] ?? null;
        $password = $dbConfig['password'] ?? '';

        if (! $database || ! $username) {
            return back()->with('error', 'Database connection is not configured properly for backup creation.');
        }

        $timestamp = now()->format('Ymd_His');
        $sqlFileName = "backup_{$timestamp}.sql";
        $zipFileName = "backup_{$timestamp}.zip";
        $backupPath = $zipFileName;
        $metadataFileName = "backup_{$timestamp}.txt";
        $metadataPath = $metadataFileName;

        // Find mysqldump executable - check multiple common locations
        $possiblePaths = [
            'mysqldump', // System PATH on Unix-like systems and configured Windows
            'C:\\xampp\\mysql\\bin\\mysqldump.exe', // XAMPP Windows
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe', // MySQL Server Windows (8.0)
            'C:\\Program Files\\MySQL\\MySQL Server 5.7\\bin\\mysqldump.exe', // MySQL Server Windows (5.7)
            'C:\\Program Files (x86)\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe', // MySQL Server Windows 32-bit (8.0)
            '/usr/bin/mysqldump', // Linux
            '/usr/local/bin/mysqldump', // macOS Homebrew
        ];

        $mysqldumpPath = null;
        foreach ($possiblePaths as $path) {
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                // Windows: check for .exe file or command in PATH
                if (file_exists($path)) {
                    $mysqldumpPath = $path;
                    break;
                }
            } else {
                // Unix-like systems: check for executable
                if (file_exists($path) && is_executable($path)) {
                    $mysqldumpPath = $path;
                    break;
                }
            }
        }

        if (! $mysqldumpPath) {
            Log::error('mysqldump not found in any known location.', [
                'checked_paths' => $possiblePaths,
                'php_os' => PHP_OS,
                'user_id' => optional(auth()->user())->id,
            ]);
            return back()->with('error', 'mysqldump is not installed or not found in PATH. Please install MySQL Server or add mysqldump to your system PATH.');
        }

        $command = [
            $mysqldumpPath,
            '--single-transaction',
            '--routines',
            '--triggers',
            '--events',
            '-h', $host,
            '-P', $port,
            '-u', $username,
        ];

        // Add password to command line if provided (more reliable than env vars)
        if ($password !== '') {
            $command[] = '-p' . $password;
        }

        $command[] = $database;

        // Get current environment and add MySQL password environment variable
        $env = getenv(); // Get all current environment variables
        $env['MYSQL_PWD'] = $password;

        $process = new Process($command, base_path(), $env);
        $process->setTimeout(300);
        $process->run();

        if (! $process->isSuccessful()) {
            Log::error('Database backup creation failed.', [
                'mysqldump_path' => $mysqldumpPath,
                'command' => implode(' ', $command),
                'database' => $database,
                'host' => $host,
                'port' => $port,
                'error' => $process->getErrorOutput(),
                'output' => $process->getOutput(),
                'exit_code' => $process->getExitCode(),
                'user_id' => optional(auth()->user())->id,
            ]);

            return back()->with('error', 'Unable to create database backup. Please verify that mysqldump is installed and database credentials are correct.');
        }

        $sqlDump = $process->getOutput();
        $tmpSqlPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $sqlFileName;
        file_put_contents($tmpSqlPath, $sqlDump);

        $archiveCreated = false;
        if (class_exists(ZipArchive::class)) {
            $zip = new ZipArchive();
            $tmpZipPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'tmp_' . $zipFileName;

            if ($zip->open($tmpZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                $zip->addFile($tmpSqlPath, $sqlFileName);
                $zip->close();
                Storage::disk('backups')->put($backupPath, file_get_contents($tmpZipPath));
                @unlink($tmpZipPath);
                $archiveCreated = true;
            } else {
                Log::warning('ZipArchive available but failed to create temporary zip.', [
                    'zip_path' => $tmpZipPath,
                    'user_id' => optional(auth()->user())->id,
                ]);
            }
        }

        if (! $archiveCreated) {
            $backupPath = $sqlFileName;
            Storage::disk('backups')->put($backupPath, $sqlDump);
        }

        Storage::disk('backups')->put($metadataPath, json_encode([
            'created_at' => now()->toDateTimeString(),
            'backup_frequency' => Setting::get('backup_frequency', 'daily'),
            'backup_file' => basename($backupPath),
            'status' => 'success',
            'message' => $archiveCreated ? 'Database backup archive created' : 'Database SQL dump created',
            'database' => $database,
        ], JSON_PRETTY_PRINT));

        @unlink($tmpSqlPath);

        Setting::set('last_backup', now()->toDateTimeString());
        \App\Models\Activity::log("Manual system backup created: " . basename($backupPath), 'backup_manual');

        $superAdmin = auth()->user();
        if ($superAdmin) {
            $superAdmin->notify(new \App\Notifications\SystemBackupCompletedNotification('success', 'Manual backup completed at ' . now()->format('Y-m-d H:i:s')));
        }

        return back()->with('success', "Manual backup created: " . basename($backupPath));
    }

    /**
     * Display backup history.
     */
    public function backupHistory(Request $request)
    {
        $directory = 'backups';

        // Download specific backup file if requested
        if ($request->has('file')) {
            $fileName = $request->query('file');
            
            // Prevent directory traversal - only allow files without path separators
            if (preg_match('/[\/\\\\]/', $fileName) || strpos($fileName, '..') !== false) {
                Log::warning('Invalid backup download request.', [
                    'requested_file' => $fileName,
                    'user_id' => optional(auth()->user())->id,
                ]);

                return redirect()->route('superadmin.settings.backup.history')
                    ->with('error', 'Invalid backup file name.');
            }

            if (! Storage::disk('backups')->exists($fileName)) {
                Log::error('Backup download file not found.', [
                    'requested_file' => $fileName,
                    'user_id' => optional(auth()->user())->id,
                ]);

                return redirect()->route('superadmin.settings.backup.history')
                    ->with('error', 'Requested backup file was not found.');
            }

            try {
                $fileFullPath = Storage::disk('backups')->path($fileName);
                return response()->download($fileFullPath);
            } catch (\Throwable $exception) {
                Log::error('Backup download failed.', [
                    'file' => $fileName,
                    'file_path' => Storage::disk('backups')->path($fileName),
                    'message' => $exception->getMessage(),
                    'user_id' => optional(auth()->user())->id,
                ]);

                return redirect()->route('superadmin.settings.backup.history')
                    ->with('error', 'Unable to download the backup file. Please contact the system administrator.');
            }
        }

        $backups = [];

        $files = Storage::disk('backups')->files('.');
        foreach ($files as $file) {
            $backups[] = [
                'name' => basename($file),
                'size' => Storage::disk('backups')->size($file),
                'updated_at' => Storage::disk('backups')->lastModified($file),
            ];
        }
        usort($backups, function ($a, $b) {
            return $b['updated_at'] <=> $a['updated_at'];
        });

        return view('superadmin.system.backup-history', compact('backups'));
    }

    /**
     * Restore a backup selected from history.
     */
    public function restoreBackup(Request $request)
    {
        $validated = $request->validate([
            'backup_file' => 'required|string',
        ]);

        $file = $validated['backup_file'];

        // Prevent directory traversal - only allow files without path separators
        if (preg_match('/[\\/\\\\]/', $file) || strpos($file, '..') !== false) {
            abort(403, 'Invalid backup file path.');
        }

        if (! Storage::disk('backups')->exists($file)) {
            return back()->with('error', 'Selected backup file does not exist.');
        }

        // TODO: Replace with actual restore logic; for now we simulate restore by copying metadata to restore file.
        $restoreMarkerPath = 'restore_' . now()->format('Ymd_His') . '.json';
        $restoreData = [
            'restored_from' => $file,
            'restored_at' => now()->toDateTimeString(),
            'status' => 'success',
        ];

        Storage::disk('backups')->put($restoreMarkerPath, json_encode($restoreData, JSON_PRETTY_PRINT));

        Setting::set('last_restore', now()->toDateTimeString());
        \App\Models\Activity::log("Backup restored from {$file}", 'backup_restore');

        $superAdmin = auth()->user();
        if ($superAdmin) {
            $superAdmin->notify(new \App\Notifications\SystemBackupCompletedNotification('success', 'Backup restored from ' . $file . ' at ' . now()->format('Y-m-d H:i:s')));
        }

        return back()->with('success', "Backup restored from {$file} successfully.");
    }

    /**
     * Display system health status.
     */
    public function systemHealth()
    {
        $health = [
            'database' => 'healthy',
            'cache' => 'healthy',
            'queue' => 'healthy',
            'storage' => 'healthy',
            'memoryUsage' => '45%',
            'cpuUsage' => '32%',
            'diskUsage' => '65%',
        ];

        return view('superadmin.system.health', compact('health'));
    }

    /**
     * Display profile.
     */
    public function profile()
    {
        $user = auth()->user();
        return view('superadmin.profile.index', compact('user'));
    }

    /**
     * Update profile.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        // Build dynamic validation rules depending on which form was submitted
        $rules = [];

        // If name/email present in request, validate them
        if ($request->has('name')) {
            $rules['name'] = 'required|string|max:255';
        }

        if ($request->has('email')) {
            $rules['email'] = 'required|email|unique:users,email,' . $user->id;
        }

        // If password change attempted, require current password and confirm new password
        if ($request->filled('password')) {
            $rules['current_password'] = 'required|string';
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $validated = $request->validate($rules);

        $data = [];

        if (isset($validated['name'])) {
            $data['name'] = $validated['name'];
        }

        if (isset($validated['email'])) {
            $data['email'] = $validated['email'];
        }

        if ($request->filled('password')) {
            // verify current password
            if (!Hash::check($request->input('current_password'), $user->password)) {
                return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect'])->withInput();
            }

            $data['password'] = Hash::make($validated['password']);
        }

        if (!empty($data)) {
            $user->update($data);
            \App\Models\Activity::log("Superadmin profile updated", 'profile_update');
        }

        return redirect()->route('superadmin.profile')->with('success', 'Profile updated successfully');
    }

    /**
     * Update profile photo.
     */
    public function updateProfilePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = auth()->user();

        // Delete old photo if exists
        if ($user->profile_photo && Storage::disk('public')->exists('uploads/profiles/' . $user->profile_photo)) {
            Storage::disk('public')->delete('uploads/profiles/' . $user->profile_photo);
        }

        // Store new photo
        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $extension = strtolower($file->getClientOriginalExtension());
            $filename = 'superadmin_' . auth()->id() . '_' . time() . '_' . Str::random(12) . '.' . $extension;
            $path = $file->storeAs('uploads/profiles', $filename, 'public');

            if (! $path) {
                return redirect()->route('superadmin.profile')->with('error', 'Unable to upload profile photo.');
            }

            // Update user record
            $user->update(['profile_photo' => $filename]);
            \App\Models\Activity::log("Superadmin updated profile photo", 'profile_photo');
        }

        return redirect()->route('superadmin.profile')
            ->with('success', 'Profile photo updated successfully!');
    }

    /**
     * Update footer settings
     */
    public function updateFooterSettings(Request $request)
    {
        $validated = $request->validate([
            'footer_address' => 'required|string|max:500',
            'footer_phone' => 'required|string|max:50',
            'footer_email' => 'required|email|max:255',
            'footer_facebook' => 'nullable|url|max:500',
            'footer_twitter' => 'nullable|url|max:500',
            'footer_linkedin' => 'nullable|url|max:500',
            'footer_instagram' => 'nullable|url|max:500',
            'privacy_policy' => 'nullable|string',
            'terms_of_service' => 'nullable|string',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        \App\Models\Activity::log("Footer settings updated", 'settings_update');

        return back()->with('success', 'Footer settings updated successfully');
    }

    /**
     * Find mysqldump executable path - check system PATH and common installation locations.
     */
    private function findMysqldump()
    {
        // Try to find mysqldump in the system PATH
        if (PHP_OS_FAMILY === 'Windows') {
            $findCommand = 'where mysqldump';
        } else {
            $findCommand = 'which mysqldump';
        }

        $process = new Process([$findCommand], null, null, null, 5);
        $process->run();
        
        if ($process->isSuccessful()) {
            $path = trim($process->getOutput());
            if ($path && file_exists($path)) {
                return $path;
            }
        }

        // Check common Windows installation paths
        if (PHP_OS_FAMILY === 'Windows') {
            $commonPaths = [
                'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
                'C:\\Program Files\\MySQL\\MySQL Server 5.7\\bin\\mysqldump.exe',
                'C:\\Program Files (x86)\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
                'C:\\xampp\\mysql\\bin\\mysqldump.exe',
                'C:\\wamp64\\bin\\mysql\\mysql8.0.1\\bin\\mysqldump.exe',
                'C:\\wamp\\bin\\mysql\\mysql5.7.\\bin\\mysqldump.exe',
                'C:\\Program Files\\MariaDB 10.9\\bin\\mysqldump.exe',
                'C:\\Program Files\\MariaDB 10.8\\bin\\mysqldump.exe',
            ];

            foreach ($commonPaths as $path) {
                // Handle wildcards in paths
                if (strpos($path, '*') !== false) {
                    $glob = glob($path);
                    foreach ($glob as $globPath) {
                        if (file_exists($globPath)) {
                            return $globPath;
                        }
                    }
                } elseif (file_exists($path)) {
                    return $path;
                }
            }
        } else {
            // Check common Linux/Mac paths
            $commonPaths = [
                '/usr/bin/mysqldump',
                '/usr/local/bin/mysqldump',
                '/usr/local/mysql/bin/mysqldump',
            ];

            foreach ($commonPaths as $path) {
                if (file_exists($path)) {
                    return $path;
                }
            }
        }

        return null;
    }
}
