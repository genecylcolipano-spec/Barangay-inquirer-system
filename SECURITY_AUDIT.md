**Production Security Audit - Barangay Inquirer System**

**Audit Date:** `{{ date('Y-m-d H:i') }}`

**CRITICAL (Must Fix)**

1. **File Upload RCE (A03 Injection)**
```
app/Http/Controllers/SuperAdmin/SystemController.php: file_exists → unlink → $file->move() no mime/type validation
public/uploads/profiles/ writable
```
**Fix:** Add ` $request->validate(['photo' => 'image|max:2048'])`, unique/random filename, scan antivirus

2. **Mass Assignment (A01 Broken Access)**
```
User.php missing $fillable - role/name/email mass-assignable
```
**Fix:** `$fillable = ['name', 'email']; $guarded = ['role'];`

3. **Hardcoded DB in scripts**
```
alter_announcements.php, insert_announcement_test.php: $db = 'inquiry_system', $user = 'root'
```
**Delete production. Use artisan tinker**

**HIGH (Fix Before Launch)**

4. **Public uploads/ directory listing**
```
public/uploads/profiles/.gitkeep
```
**Fix:** .htaccess deny from all + move to storage/app/public

5. **Debug in prod files**
```
routes/web.php has test code like $activities, system-health
```
**Remove**

6. **No rate limiting**
**Fix:** bootstrap/app.php →rateLimit()

**MEDIUM**

7. **User role enum missing**
**Fix:** User model role enum ['resident', 'admin', 'super_admin']

**LOW**

8. **Config OK** (env usage good, no APP_KEY leaks)

**✅ PASS**
- Blade XSS safe
- No SQL injection patterns
- CSRF present
- Role middleware secure w/logs

**Immediate Actions:**
1. Delete top-level test PHP files
2. Fix uploads validation
3. Mass assignment guards
4. `chmod 755 public/uploads` → `storage:link`

Repo hardened for production.

