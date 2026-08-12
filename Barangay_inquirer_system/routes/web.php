<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Resident\DashboardController;
use App\Http\Controllers\Resident\ProfileController;
use App\Http\Controllers\Resident\DocumentController;
use App\Http\Controllers\Resident\AnnouncementController as ResidentAnnouncementController;
use App\Http\Controllers\Resident\SettingsController;
use App\Http\Controllers\Resident\NotificationController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\RequestController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Resident\RequestController as ResidentRequestController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\SettingsController as AdminSettingsController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\AdminController as SuperAdminAdminController;
use App\Http\Controllers\SuperAdmin\UserController as SuperAdminUserController;
use App\Http\Controllers\SuperAdmin\RequestController as SuperAdminRequestController;
use App\Http\Controllers\SuperAdmin\AnnouncementController as SuperAdminAnnouncementController;
use App\Http\Controllers\SuperAdmin\SystemController;
use App\Http\Controllers\SuperAdmin\NotificationController as SuperAdminNotificationController;
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;

// Home page
Route::get('/', [PageController::class, 'home'])->name('home');

// Language switching
Route::get('/language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ceb'])) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
        \Log::info("Language switched to: " . $locale);
    }
    return redirect('/');
})->name('language.switch')->middleware('throttle:10,1');

// Contact form submission (1-3 req/min for guests, 3-5 for residents, 5-10 for admins, 10-20 for super admins)
Route::post('/contact', [PageController::class, 'submitContact'])->name('contact.submit')->middleware('throttle_contact');

// This displays the page
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');

// This handles the form submission (POST)
Route::post('/register', [AuthController::class, 'register'])->name('register.submit')->middleware('throttle_auth');

// Clerk Authentication Routes
Route::get('/clerk/login', [App\Http\Controllers\ClerkAuthController::class, 'showLogin'])->name('clerk.login');
Route::post('/clerk/callback', [App\Http\Controllers\ClerkAuthController::class, 'callback'])->name('clerk.callback');
Route::post('/clerk/logout', [App\Http\Controllers\ClerkAuthController::class, 'logout'])->name('clerk.logout');

// Authentication pages
Route::get('/login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
// Handles login form submission
Route::post('/login', [App\Http\Controllers\Auth\LoginController::class, 'login'])->name('login.submit')->middleware(['check_login_attempts', 'throttle_auth']);

// Password reset (forgot password) pages
Route::get('/password/reset', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/password/email', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email')->middleware('throttle_password_reset');
Route::get('/password/reset/{token}', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'reset'])->name('password.update')->middleware('throttle_auth');

// Password Strength API
Route::post('/api/password/check', [App\Http\Controllers\PasswordStrengthController::class, 'check'])->name('password.check');
Route::get('/api/password/requirements', [App\Http\Controllers\PasswordStrengthController::class, 'requirements'])->name('password.requirements');

// Logout (POST) - supports both traditional and Clerk auth
Route::post('/logout', function (Illuminate\Http\Request $request) {
    // Check if using Clerk auth
    if ($request->session()->has('clerk_token')) {
        return app(App\Http\Controllers\ClerkAuthController::class)->logout($request);
    }
    
    // Traditional Laravel logout
    Illuminate\Support\Facades\Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('home');
})->name('logout');
Route::get('/signup', [PageController::class, 'signup'])->name('signup');

// Dashboard (example for after login)
Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');

// Services routes

Route::prefix('services')->group(function () {

    Route::get('/barangay-clearance', [ServiceController::class, 'barangay'])
        ->name('services.barangay');

    Route::get('/cedula', [ServiceController::class, 'cedula'])
        ->name('services.cedula');

    Route::get('/indigency', [ServiceController::class, 'indigency'])
        ->name('services.indigency');

    Route::get('/purok-clearance', [ServiceController::class, 'purok'])
        ->name('services.purok');

    Route::get('/others', [ServiceController::class, 'others'])
        ->name('services.others');
});
Route::middleware(['auth', 'throttle_user_data'])->prefix('services')->group(function () {
    Route::get('/clearance/create', [ServiceController::class, 'clearanceCreate'])
        ->name('clearance.create');
    
    Route::get('/cedula/create', [ServiceController::class, 'cedulaCreate'])
        ->name('cedula.create');
    
    Route::get('/indigency/create', [ServiceController::class, 'indigencyCreate'])
        ->name('indigency.create');
    
    Route::get('/purok/create', [ServiceController::class, 'purokCreate'])
        ->name('purok.create');
    
    Route::get('/others/create', [ServiceController::class, 'othersCreate'])
        ->name('others.create');
});


// for navbar page ni

// Public Pages
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/services', [PageController::class, 'services'])->name('services')->middleware('throttle_public');
Route::get('/announcements', [PageController::class, 'announcements'])->name('announcements.index')->middleware('throttle_public');
// Backward compatibility: old route for legacy templates
Route::get('/announcements/list', [PageController::class, 'announcementsIndex'])->name('announcements.list')->middleware('throttle_public');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/privacy-policy', [PageController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/terms-of-service', [PageController::class, 'termsOfService'])->name('terms-of-service');


// Admin Routes - only users with the 'admin' role can access
Route::middleware(['auth', 'role:admin', 'throttle_user_data'])->prefix('admin')->as('admin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    // Administrative Profile Route
    Route::get('/profile', function () {
        return redirect()->route('admin.users.show', auth()->id());
    })->name('profile');

    // Announcements Management
    Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::get('/announcements/create', [AnnouncementController::class, 'create'])->name('announcements.create');
    Route::post('/announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::get('/announcements/{announcement}', [AnnouncementController::class, 'show'])->name('announcements.show');
    Route::get('/announcements/{announcement}/edit', [AnnouncementController::class, 'edit'])->name('announcements.edit');
    Route::put('/announcements/{announcement}', [AnnouncementController::class, 'update'])->name('announcements.update');
    Route::delete('/announcements/{announcement}', [AnnouncementController::class, 'destroy'])->name('announcements.destroy');
    
    // Document Requests Management
    Route::prefix('requests')->name('requests.')->group(function () {
        Route::get('/', [RequestController::class, 'index'])->name('index');
        Route::get('/{request}', [RequestController::class, 'show'])->name('show');
        Route::get('/{request}/view', [RequestController::class, 'viewAttachment'])->name('view');
        Route::get('/{request}/download', [RequestController::class, 'downloadAttachment'])->name('download');
        Route::post('/{request}/update-notes', [RequestController::class, 'updateNotes'])->name('update-notes');
        Route::post('/{request}/approve', [RequestController::class, 'approve'])->name('approve');
        Route::post('/{request}/reject', [RequestController::class, 'reject'])->name('reject');
        Route::post('/{request}/pending', [RequestController::class, 'pending'])->name('pending');
        Route::post('/{request}/processing', [RequestController::class, 'processing'])->name('processing');
    });
    
    // Users Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Settings Management
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [AdminSettingsController::class, 'index'])->name('index');
        Route::put('/profile', [AdminSettingsController::class, 'updateProfile'])->name('profile');
        Route::put('/password', [AdminSettingsController::class, 'updatePassword'])->name('password');
        Route::put('/photo', [AdminSettingsController::class, 'updatePhoto'])->name('photo');
        Route::put('/footer', [AdminSettingsController::class, 'updateFooterSettings'])->name('footer');
    });

    // Notifications (Admin)
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [AdminNotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [AdminNotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [AdminNotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::delete('/{id}', [AdminNotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/delete-all', [AdminNotificationController::class, 'deleteAll'])->name('delete-all');
    });
});

// Super Admin Routes - only users with the 'super_admin' role can access
Route::middleware(['auth', 'role:super_admin', 'throttle_user_data'])->prefix('superadmin')->as('superadmin.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
    
    // Profile
    Route::get('/profile', [SystemController::class, 'profile'])->name('profile');
    Route::post('/profile', [SystemController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/photo', [SystemController::class, 'updateProfilePhoto'])->name('profile.photo');
    
    // Admins Management
    Route::prefix('admins')->name('admins.')->group(function () {
        Route::get('/', [SuperAdminAdminController::class, 'index'])->name('index');
        Route::get('/create', [SuperAdminAdminController::class, 'create'])->name('create');
        Route::post('/', [SuperAdminAdminController::class, 'store'])->name('store');
        Route::get('/{admin}', [SuperAdminAdminController::class, 'show'])->name('show');
        Route::get('/{admin}/edit', [SuperAdminAdminController::class, 'edit'])->name('edit');
        Route::put('/{admin}', [SuperAdminAdminController::class, 'update'])->name('update');
        Route::delete('/{admin}', [SuperAdminAdminController::class, 'destroy'])->name('destroy');
    });
    
    // Users Management
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [SuperAdminUserController::class, 'index'])->name('index');
        Route::get('/{user}', [SuperAdminUserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [SuperAdminUserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [SuperAdminUserController::class, 'update'])->name('update');
        Route::delete('/{user}', [SuperAdminUserController::class, 'destroy'])->name('destroy');
    });
    
    // Document Requests Management
    Route::prefix('requests')->name('requests.')->group(function () {
        Route::get('/', [SuperAdminRequestController::class, 'index'])->name('index');
        Route::get('/{request}', [SuperAdminRequestController::class, 'show'])->name('show');
        Route::post('/{request}/update-notes', [SuperAdminRequestController::class, 'updateNotes'])->name('update-notes');
        Route::post('/{request}/approve', [SuperAdminRequestController::class, 'approve'])->name('approve');
        Route::post('/{request}/reject', [SuperAdminRequestController::class, 'reject'])->name('reject');
    });
    
    // Announcements Management
    Route::prefix('announcements')->name('announcements.')->group(function () {
        Route::get('/', [SuperAdminAnnouncementController::class, 'index'])->name('index');
        Route::get('/create', [SuperAdminAnnouncementController::class, 'create'])->name('create');
        Route::post('/', [SuperAdminAnnouncementController::class, 'store'])->name('store');
        Route::get('/{announcement}', [SuperAdminAnnouncementController::class, 'show'])->name('show');
        Route::get('/{announcement}/edit', [SuperAdminAnnouncementController::class, 'edit'])->name('edit');
        Route::put('/{announcement}', [SuperAdminAnnouncementController::class, 'update'])->name('update');
        Route::delete('/{announcement}', [SuperAdminAnnouncementController::class, 'destroy'])->name('destroy');
    });
    
    // System Management
    Route::get('/activity-logs', [SystemController::class, 'activityLogs'])->name('activity-logs');
    Route::get('/settings', [SystemController::class, 'settings'])->name('settings');
    Route::post('/settings/general', [SystemController::class, 'updateGeneralSettings'])->name('settings.general');
    Route::post('/settings/maintenance', [SystemController::class, 'toggleMaintenanceMode'])->name('settings.maintenance');
    Route::post('/settings/footer', [SystemController::class, 'updateFooterSettings'])->name('settings.footer');
    Route::post('/settings/security', [SystemController::class, 'updateSecuritySettings'])->name('settings.security');
    Route::post('/settings/email', [SystemController::class, 'updateEmailSettings'])->name('settings.email');
    Route::post('/settings/backup', [SystemController::class, 'updateBackupSettings'])->name('settings.backup');
    Route::post('/settings/backup/manual', [SystemController::class, 'createManualBackup'])->name('settings.backup.manual');
    Route::post('/settings/backup/restore', [SystemController::class, 'restoreBackup'])->name('settings.backup.restore');
    Route::get('/settings/backup/history', [SystemController::class, 'backupHistory'])->name('settings.backup.history');
    Route::get('/system-health', [SystemController::class, 'systemHealth'])->name('system-health');
    
    // Notifications Management
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [SuperAdminNotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [SuperAdminNotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [SuperAdminNotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::delete('/{id}', [SuperAdminNotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/delete-all', [SuperAdminNotificationController::class, 'deleteAll'])->name('delete-all');
    });
});




Route::middleware(['auth', 'throttle_user_data'])->prefix('resident')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('resident.dashboard');

    // Documents routes
    Route::get('/documents', [DocumentController::class, 'index'])
        ->name('resident.documents');

    Route::get('/documents/{document}', [DocumentController::class, 'show'])
        ->name('resident.document.show');

    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])
        ->name('resident.document.download');

    // Requests routes
    Route::get('/request/create', [ResidentRequestController::class, 'create'])
        ->name('resident.request.create');

    Route::post('/request/store', [ResidentRequestController::class, 'store'])
        ->name('resident.request.store');

    Route::get('/my-requests', [ResidentRequestController::class, 'index'])
        ->name('resident.requests');

    Route::get('/request/{request}', [ResidentRequestController::class, 'show'])
        ->name('resident.request.show');

    Route::get('/request/{request}/view', [ResidentRequestController::class, 'viewAttachment'])
        ->name('resident.request.view');

    Route::get('/request/{request}/download', [ResidentRequestController::class, 'downloadAttachment'])
        ->name('resident.request.download');

    Route::delete('/request/{request}', [ResidentRequestController::class, 'destroy'])
        ->name('resident.request.destroy');

    // Announcements routes
    Route::get('/announcements', [ResidentAnnouncementController::class, 'index'])
        ->name('resident.announcements');

    Route::get('/announcements/{announcement}', [ResidentAnnouncementController::class, 'show'])
        ->name('resident.announcement.show');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'index'])
        ->name('resident.profile');

    Route::get('/profile/edit', [ProfileController::class, 'edit'])
        ->name('resident.profile.edit');

    Route::post('/profile/update', [ProfileController::class, 'update'])
        ->name('resident.profile.update');

    // Settings routes
    Route::get('/settings', [SettingsController::class, 'index'])
        ->name('resident.settings');

    Route::post('/settings/profile', [SettingsController::class, 'updateProfile'])
        ->name('resident.settings.profile');

    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])
        ->name('resident.settings.password');

    Route::put('/settings/photo', [SettingsController::class, 'updatePhoto'])
        ->name('resident.settings.photo');

    // Notification routes
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('resident.notification.read');

    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('resident.notifications.read-all');

    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])
        ->name('resident.notification.destroy');

    Route::delete('/notifications/delete-all', [NotificationController::class, 'deleteAll'])
        ->name('resident.notifications.delete-all');

    Route::get('/notifications/check-unread', [NotificationController::class, 'checkUnread'])
        ->name('resident.notifications.check-unread');

    Route::get('/notifications/recent', [NotificationController::class, 'getRecent'])
        ->name('resident.notifications.recent');

    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('resident.notifications');

    Route::post('/logout', function () {
        Auth::logout();
        return redirect('/login');
    })->name('resident.logout');
});
