# File Storage Policies - Quick Reference Guide

A developer-friendly quick start guide for using the file storage access control system.

## In This Document

- [Quick Start](#quick-start)
- [Using Policies](#using-policies)
- [Using Service](#using-service)
- [Common Patterns](#common-patterns)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)

---

## Quick Start

### 1. Check Authorization in Controller

```php
use App\Models\DocumentRequest;

class RequestController extends Controller
{
    public function downloadAttachment($id)
    {
        $request = DocumentRequest::findOrFail($id);
        
        // This is all you need for authorization!
        $this->authorize('download', $request);
        
        // Download file
        return Storage::disk('local')->download($request->attachment);
    }
}
```

### 2. Protect Routes

```php
// routes/web.php
Route::get('/requests/{id}/download', [RequestController::class, 'download'])
    ->middleware('auth', 'enforce_file_policy')
    ->name('requests.download');
```

### 3. Get Allowed Upload Directory

```php
use App\Services\FileStoragePolicy;

class RequestController extends Controller
{
    public function __construct(protected FileStoragePolicy $filePolicy) {}
    
    public function store(Request $request)
    {
        $directory = $this->filePolicy->getAllowedUploadDirectory($request->user());
        // outputs: "requests/1" (for user ID 1)
        
        $path = $request->file('attachment')->store($directory, 'local');
    }
}
```

---

## Using Policies

### Authorization with $this->authorize()

```php
// Check single policy action
$this->authorize('download', $documentRequest);

// If not authorized, throws AuthorizationException (403 error)
```

### Authorization with can() method

```php
if ($request->user()->can('download', $documentRequest)) {
    // Proceed with download
    return Storage::download($documentRequest->attachment);
} else {
    abort(403, 'Unauthorized');
}
```

### Available Policy Methods

```php
// DocumentRequestPolicy methods

// View request
abort_if($user->cannot('view', $documentRequest), 403);

// Download attachment
abort_if($user->cannot('download', $documentRequest), 403);

// Create new request (no model needed)
abort_if($user->cannot('create', DocumentRequest::class), 403);

// Update request (admin only)
abort_if($user->cannot('update', $documentRequest), 403);

// Delete request (owner or admin only)
abort_if($user->cannot('delete', $documentRequest), 403);

// Add notes (admin only)
abort_if($user->cannot('addNotes', $documentRequest), 403);

// Approve request (admin only)
abort_if($user->cannot('approve', $documentRequest), 403);

// Reject request (admin only)
abort_if($user->cannot('reject', $documentRequest), 403);
```

---

## Using Service

### Inject FileStoragePolicy

```php
use App\Services\FileStoragePolicy;

class YourController extends Controller
{
    public function __construct(protected FileStoragePolicy $filePolicy) {}
}
```

### Check Download Authorization

```php
// Check authorization before download
if (!$this->filePolicy->canDownloadFile($user, $filePath, 'local')) {
    abort(403, 'Cannot download this file');
}

// Proceed with download
return Storage::disk('local')->download($filePath);
```

### Verify File Ownership

```php
// Is this file owned by this user?
if ($this->filePolicy->isFileOwnedByUser($user, 'requests/1/document.pdf')) {
    echo "User 1 owns this file";
} else {
    echo "User 1 does not own this file";
}
```

### Get User's Upload Directory

```php
// Get where user should upload files
$uploadDir = $this->filePolicy->getAllowedUploadDirectory($user);
// outputs: "requests/1" for user ID 1

// Store file in that directory
$path = $file->store($uploadDir, 'local');
```

### Validate File Paths

```php
// Prevent directory traversal attacks
if (!$this->filePolicy->isValidFilePath($userProvidedPath)) {
    abort(400, 'Invalid file path');
}

// Safe to use path now
return Storage::download($userProvidedPath);
```

### Central Enforcement Point

```php
// Use this to verify authorization before any file operation
if (!$this->filePolicy->enforceFileAccessPolicy($user, $filePath)) {
    abort(403, 'Unauthorized file access');
}

// File access is now safe
```

### List Accessible Files

```php
// Get all files the user can access
$files = $this->filePolicy->getAccessibleFiles($user, 'local');

foreach ($files as $filePath) {
    echo $filePath; // e.g., "requests/1/document.pdf"
}
```

### Check Delete Authorization

```php
// Can user delete this file?
if ($this->filePolicy->canDeleteFile($user, $filePath)) {
    Storage::disk('local')->delete($filePath);
    DocumentRequest::where('attachment', $filePath)->delete();
} else {
    abort(403, 'Cannot delete this file');
}
```

---

## Common Patterns

### Pattern 1: Resident Download Own File

```php
class RequestController extends Controller
{
    public function downloadAttachment(Request $request, $id)
    {
        // 1. Get the document request
        $documentRequest = DocumentRequest::findOrFail($id);

        // 2. Check authorization (handles role checks)
        $this->authorize('download', $documentRequest);

        // 3. Download file
        return Storage::disk('local')->download($documentRequest->attachment);
    }
}
```

### Pattern 2: Admin Download Any File

```php
// Same code as Pattern 1!
// The policy automatically allows admins to download any file

$this->authorize('download', $documentRequest);
// Returns true for admins
// Returns true for residents if they own it
// Throws 403 for any other resident
```

### Pattern 3: Resident Upload File

```php
class RequestController extends Controller
{
    public function __construct(protected FileStoragePolicy $filePolicy) {}

    public function store(Request $request)
    {
        // 1. Validate request
        $validated = $request->validate([
            'attachment' => 'required|file|mimes:pdf,doc,docx|max:5120',
        ]);

        // 2. Get allowed directory
        $directory = $this->filePolicy->getAllowedUploadDirectory($request->user());

        // 3. Store file
        $path = $validated['attachment']->store($directory, 'local');

        // 4. Create document request record
        $docRequest = DocumentRequest::create([
            'user_id' => $request->user()->id,
            'attachment' => $path,
            // ... other fields
        ]);

        return redirect()->route('requests.show', $docRequest);
    }
}
```

### Pattern 4: Validate Path Before Use

```php
class DocumentController extends Controller
{
    public function __construct(protected FileStoragePolicy $filePolicy) {}

    public function viewDocument(Request $request)
    {
        $filePath = $request->query('file');

        // 1. Validate path (prevent directory traversal)
        if (!$this->filePolicy->isValidFilePath($filePath)) {
            abort(400, 'Invalid file path');
        }

        // 2. Check authorization
        if (!$this->filePolicy->canDownloadFile($request->user(), $filePath, 'local')) {
            abort(403, 'Unauthorized');
        }

        // 3. Use path safely
        return Storage::disk('local')->download($filePath);
    }
}
```

### Pattern 5: List User's Own Documents

```php
class RequestController extends Controller
{
    public function index(Request $request)
    {
        // Residents see only their requests
        // Admins see all requests
        
        if ($request->user()->role === 'resident') {
            $requests = DocumentRequest::where('user_id', $request->user()->id)
                ->get();
        } else {
            $requests = DocumentRequest::all();
        }

        return view('requests.index', compact('requests'));
    }
}
```

---

## Testing

### Running Tests

```bash
# Run all file storage policy tests
php artisan test tests/Unit/FileStoragePolicyTest.php
php artisan test tests/Feature/FileStoragePolicyFeatureTest.php

# Run specific test
php artisan test tests/Unit/FileStoragePolicyTest.php --filter test_user_ownership_of_own_file

# Run with verbose output
php artisan test tests/Unit/FileStoragePolicyTest.php --verbose
```

### Example Test

```php
use Tests\TestCase;
use App\Models\User;
use App\Models\DocumentRequest;
use App\Services\FileStoragePolicy;

class FileStorageTest extends TestCase
{
    public function test_resident_cannot_download_other_file()
    {
        $resident1 = User::factory()->create(['role' => 'resident']);
        $resident2 = User::factory()->create(['role' => 'resident']);

        $docRequest = DocumentRequest::factory()->create([
            'user_id' => $resident1->id,
            'attachment' => 'requests/1/document.pdf',
        ]);

        $filePolicy = app(FileStoragePolicy::class);

        // resident2 should NOT be able to download resident1's file
        $result = $filePolicy->canDownloadFile(
            $resident2,
            'requests/1/document.pdf',
            'local'
        );

        $this->assertFalse($result);
    }
}
```

---

## Troubleshooting

### "Unauthorized file access" Error

**Check**:
1. Is user authenticated? `auth()->check()`
2. Is file owner's ID in path? File path: `requests/1/file.pdf`, User ID: 1
3. Is user role correct? `$user->role` should be resident/admin/super_admin

**Solution**:
```php
// Debug: Check authorization
$user = auth()->user();
$documentRequest = DocumentRequest::find($id);

// These should tell you what's wrong
dd([
    'user_id' => $user->id,
    'user_role' => $user->role,
    'request_owner' => $documentRequest->user_id,
    'attachment' => $documentRequest->attachment,
    'can_download' => $user->can('download', $documentRequest),
]);
```

### "Invalid file path" Error

**Check**:
1. Does path contain `../`? Not allowed
2. Does path start with `/`? Only relative paths
3. Does path match pattern `requests/{user_id}/`? Required format

**Solution**:
```php
// Debug: Validate path
$filePolicy = app(FileStoragePolicy::class);
$filePath = 'requests/1/document.pdf';

if ($filePolicy->isValidFilePath($filePath)) {
    echo "Path is valid";
} else {
    echo "Path is invalid";
    // Check what's wrong
    dd($filePath);
}
```

### Unauthorized Access Not Logged

**Check**:
1. Is logging configured? Check `.env` -> `LOG_CHANNEL`
2. Is middleware applied? Check routes.php
3. Is attempt being made? Use browser DevTools

**Solution**:
```bash
# Check logs
tail -f storage/logs/laravel.log | grep "Unauthorized file access"

# Manually test unauthorized access
curl -H "Authorization: Bearer $TOKEN" http://localhost:8000/requests/999/download
```

### Tests Failing

**Check**:
1. Are routes defined? Tests need routes
2. Is middleware applied? Tests need to test middleware
3. Is database migrated? Tests use RefreshDatabase

**Solution**:
```bash
# Run test with full output
php artisan test --verbose

# Check specific failure
php artisan test --filter test_name --verbose

# Refresh database
php artisan migrate:fresh
```

### File Not Found After Upload

**Check**:
1. Was file stored correctly? Check `storage/app/requests/`
2. Is attachment path saved? Check database
3. Is disk correct? Check route - using `local` disk?

**Solution**:
```php
// Debug: Check file storage
$docRequest = DocumentRequest::find($id);

// File path in database
dd($docRequest->attachment);
// Output should look like: requests/1/filename.pdf

// Check file exists
dd(Storage::disk('local')->exists($docRequest->attachment));
// Should be: true
```

---

## Cheat Sheet

```php
// Authorization with policy
$this->authorize('download', $documentRequest);
$this->authorize('delete', $documentRequest);
$this->authorize('create', DocumentRequest::class);

// Authorization with service
$filePolicy->canDownloadFile($user, $path, 'local');
$filePolicy->isFileOwnedByUser($user, $path);
$filePolicy->isValidFilePath($path);

// File operations
$directory = $filePolicy->getAllowedUploadDirectory($user);
$files = $filePolicy->getAccessibleFiles($user, 'local');
$filePolicy->enforceFileAccessPolicy($user, $path);

// Route protection
->middleware('auth', 'enforce_file_policy')

// Testing
php artisan test tests/Unit/FileStoragePolicyTest.php
php artisan test tests/Feature/FileStoragePolicyFeatureTest.php
```

---

## See Also

- Full Documentation: [FILE_STORAGE_POLICIES.md](/FILE_STORAGE_POLICIES.md)
- Implementation Status: [FILE_STORAGE_POLICIES_CHECKLIST.md](/FILE_STORAGE_POLICIES_CHECKLIST.md)
- Completion Summary: [FILE_STORAGE_POLICIES_SUMMARY.md](/FILE_STORAGE_POLICIES_SUMMARY.md)

---

**Created**: Today (2024)  
**Purpose**: Quick reference for developers  
**Scope**: File storage access control policies
