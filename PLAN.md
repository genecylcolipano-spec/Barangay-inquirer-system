# Duplicate RoleMiddleware Fix

1. **Status:** Nested project = canonical. Top-level = junk.

2. **Action:**
   - Overwrite nested RoleMiddleware.php w/ secure version
   - rm -rf app/ bootstrap/ (cwd pollution)
   - Update bootstrap/app.php alias to 'role' => RoleMiddleware::class (add)

3. **Routes:** Have Route::middleware(['auth', 'role']) → will work post-fix.

4. **Test:** php artisan route:list (no Target class error)

Run these:
cd Barangay_inquirer_system
composer dump-autoload
php artisan route:clear optimize:clear
php artisan serve
