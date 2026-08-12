# Security Hardening Plan

**Priority 1: Rate Limiting**
```
bootstrap/app.php → middleware()->throttle('api', 60, 1) 
middleware()->throttle('login', 5, 1)
```

**Priority 2: Fix upload dir**
Move public/uploads → storage/app/public/uploads
php artisan storage:link
.htaccess deny

**Priority 3: Delete prod test files**
rm alter_announcements.php insert_announcement_test.php

**Priority 4: User role enum**
app/Models/User.php add enum

**Priority 5: Route cleanup**
web.php remove test $activities = 

**Confirm plan, then execute.**
