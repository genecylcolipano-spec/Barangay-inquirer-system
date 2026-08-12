# File Storage Access Policies Documentation

## Overview

This document describes the comprehensive file storage access control system implemented in the Barangay Inquirer System. The system ensures that users can only access files they have uploaded, while administrators have access to all files. It prevents unauthorized file access, directory traversal attacks, and provides security through multiple layers of authorization.

## Architecture

The file storage policy system is built on a **two-layer authorization architecture**:

### Layer 1: Laravel Native Authorization Policies
- **Component**: `app/Policies/DocumentRequestPolicy.php`
- **Purpose**: Provides native Laravel authorization policies for the `DocumentRequest` model
- **Methods**:
  - `view()` - Residents can view only their own requests
  - `download()` - Residents can download only their own attachments
  - `create()` - Only residents can create document requests
  - `update()` - Only admins can update requests
  - `delete()` - Only the owner of pending requests can delete
  - `approve()` - Only admins can approve requests
  - `reject()` - Only admins can reject requests
  - `addNotes()` - Only admins can add notes

### Layer 2: Custom File Storage Service
- **Component**: `app/Services/FileStoragePolicy.php`
- **Purpose**: Provides fine-grained file storage access control and validation
- **Key Methods**:
  - `canDownloadFile()` - Validates download authorization
  - `canViewFile()` - Validates view authorization
  - `isFileOwnedByUser()` - Verifies file ownership
  - `getAllowedUploadDirectory()` - Gets user's upload directory
  - `isValidFilePath()` - Prevents directory traversal attacks
  - `enforceFileAccessPolicy()` - Central enforcement point
  - `getAccessibleFiles()` - Lists accessible files for user
  - `canDeleteFile()` - Validates delete authorization

### Layer 3: Middleware Protection
- **Component**: `app/Http/Middleware/EnforceFileStoragePolicy.php`
- **Purpose**: Intercepts file access requests and enforces policies
- **Functions**:
  - Detects file access requests
  - Extracts file path from request
  - Validates user authorization
  - Logs unauthorized attempts
  - Returns 403 error for unauthorized access

## User Roles and Access

### Resident Users
- **Can perform**:
  - Create their own document requests
  - View their own requests and attachments
  - Download their own attachments
  - Delete their own pending requests; cannot delete approved/rejected requests
- **Cannot perform**:
  - View other residents' requests
  - Download other residents' files
  - Approve or reject requests

### Admin Users
- **Can perform**:
  - View all document requests
  - Download all attachments
  - Approve or reject requests
  - Add notes to requests
  - Upload responses on behalf of residents
  - Access any file in the storage system
- **Cannot perform**:
  - Create resident document requests (only residents can create)

### Super Admin Users
- **Can perform**: Everything admins can do, plus:
  - Manage user roles and permissions
  - Full system access without restrictions

## File Storage Structure

Files are stored in a user-organized directory structure:

```
storage/app/requests/
├── 1/                          # User ID 1
│   ├── barangay_clearance.pdf
│   ├── certificate_of_residency.docx
│   └── ...
├── 2/                          # User ID 2
│   ├── request_document.pdf
│   └── ...
└── N/                          # User ID N
    └── ...
```

**Key Points**:
- Each user's files are stored in their own directory: `requests/{user_id}/`
- File paths always start with `requests/{user_id}/`
- Files outside this structure cannot be accessed
- Directory traversal attempts (e.g., `../../../etc/passwd`) are blocked

## Implementation Details

### 1. Authorization Policy Registration

**File**: `app/Providers/AuthServiceProvider.php`

The `DocumentRequestPolicy` is registered with the `DocumentRequest` model:

```php
protected $policies = [
    DocumentRequest::class => DocumentRequestPolicy::class,
];
```

This allows Laravel's native authorization gates to work throughout the application:

```php
$this->authorize('download', $documentRequest);
```

### 2. Service Provider Setup

**File**: `bootstrap/providers.php`

The `AuthServiceProvider` must be registered:

```php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,          // File storage policies
    App\Providers\RedirectValidationServiceProvider::class,
];
```

### 3. Middleware Configuration

**File**: `bootstrap/app.php`

The file storage policy middleware is registered in the aliases:

```php
'enforce_file_policy' => \App\Http\Middleware\EnforceFileStoragePolicy::class,
```

### 4. Route Protection

Apply the middleware to file access routes:

```php
Route::get('/requests/{id}/download', [RequestController::class, 'download'])
    ->middleware('enforce_file_policy')
    ->name('requests.download');
```

## Security Features

### 1. Directory Traversal Prevention

The system blocks attempts to escape the allowed directory:

```
BLOCKED: ../../etc/passwd
BLOCKED: ..\\..\\windows\\system32
BLOCKED: requests/../../../sensitive.env
ALLOWED: requests/1/document.pdf
```

### 2. Ownership Verification

Every file access is verified against the user's ID:

```php
// File path: requests/1/document.pdf
// User ID: 2
// Result: DENIED (user ID 2 doesn't match file owner ID 1)
```

### 3. Role-Based Access Control

Different roles have different access levels:

- **Resident**: Only own files
- **Admin**: All files in `requests/` directory
- **Super Admin**: All files with no restrictions

### 4. Unauthorized Access Logging

All unauthorized access attempts are logged:

```php
Log::warning('Unauthorized file access blocked', [
    'user_id' => $request->user()?->id,
    'ip' => $request->ip(),
    'method' => $request->method(),
    'path' => $request->path(),
]);
```

### 5. Path Validation

Each file path is validated before access:

```php
// Invalid paths rejected:
- Empty paths ("")
- Root paths ("/")
- Non-existent directory patterns
- Paths with suspicious sequences

// Valid paths accepted:
- requests/1/document.pdf
- requests/123/nested/path/file.txt
- requests/999/file-with-dashes.docx
```

## Usage Examples

### Checking Download Authorization

```php
use App\Services\FileStoragePolicy;

$filePolicy = app(FileStoragePolicy::class);

// Check if user can download a file
if ($filePolicy->canDownloadFile($user, $filePath, 'local')) {
    // Proceed with download
} else {
    // Deny access
}
```

### Using Native Authorization Policies

```php
// In controller
$documentRequest = DocumentRequest::find($id);

// Check authorization
$this->authorize('download', $documentRequest);

// Or use can()
if ($request->user()->can('download', $documentRequest)) {
    // Proceed with download
}
```

### Getting Accessible Files

```php
$filePolicy = app(FileStoragePolicy::class);

// Get all files accessible by the user
$files = $filePolicy->getAccessibleFiles($user, 'local');

foreach ($files as $filePath) {
    echo $filePath; // outputs: requests/1/document.pdf
}
```

### Enforcing Policy Centrally

```php
$filePolicy = app(FileStoragePolicy::class);

// Central enforcement point
if (!$filePolicy->enforceFileAccessPolicy($user, $filePath)) {
    abort(403, 'Unauthorized file access');
}
```

## Integration with Controllers

### Example: RequestController

```php
use App\Services\FileStoragePolicy;

class RequestController extends Controller
{
    public function __construct(protected FileStoragePolicy $filePolicy) {}

    // Download attachment with authorization
    public function downloadAttachment($id)
    {
        $request = DocumentRequest::findOrFail($id);

        // Use policy authorization
        $this->authorize('download', $request);

        // Additional file path validation
        if (!$this->filePolicy->isValidFilePath($request->attachment)) {
            abort(400, 'Invalid file path');
        }

        // Proceed with download
        return Storage::disk('local')->download($request->attachment);
    }

    // Upload file with authorization
    public function store(Request $request)
    {
        // Check authorization
        $this->authorize('create', DocumentRequest::class);

        $validated = $request->validate([
            'document_type' => 'required|string',
            'attachment' => 'required|file|mimes:pdf,doc,docx|max:5120',
        ]);

        // Get allowed upload directory
        $directory = $this->filePolicy->getAllowedUploadDirectory($request->user());

        // Store file in user's directory
        $path = $validated['attachment']->store($directory, 'local');

        // Create document request record
        $docRequest = DocumentRequest::create([
            'user_id' => $request->user()->id,
            'document_type' => $validated['document_type'],
            'attachment' => $path,
        ]);

        return response()->json($docRequest);
    }
}
```

## Testing

### Unit Tests

**File**: `tests/Unit/FileStoragePolicyTest.php`

Tests the `FileStoragePolicy` service:

```bash
php artisan test tests/Unit/FileStoragePolicyTest.php
```

**Test Coverage**:
- File ownership verification
- Path validation
- Directory traversal prevention
- Download authorization
- File deletion authorization
- Accessible files listing

### Feature Tests

**File**: `tests/Feature/FileStoragePolicyFeatureTest.php`

Tests end-to-end authorization scenarios:

```bash
php artisan test tests/Feature/FileStoragePolicyFeatureTest.php
```

**Test Coverage**:
- Residents downloading own files
- Residents blocked from downloading others' files
- Admins accessing any file
- Document request authorization
- Middleware blocking unauthorized access
- Unauthorized access logging

### Running All Tests

```bash
php artisan test                                    # Run all tests
php artisan test --filter FileStoragePolicy        # Run storage policy tests only
php artisan test --filter FileStoragePolicyTest    # Run unit tests
php artisan test --filter FileStoragePolicyFeature # Run feature tests
```

## Configuration

### Environment Variables

Add to `.env` if needed:

```env
# File storage disk (default: local)
FILESYSTEM_DISK=local

# Log file access attempts (optional)
LOG_FILE_ACCESS=true
```

### Customization

To customize the upload directory pattern, edit `getAllowedUploadDirectory()` in `FileStoragePolicy`:

```php
public function getAllowedUploadDirectory(User $user): string
{
    // Current: requests/{user_id}/
    // Custom: uploads/{user_id}/{year}/{month}/
    return "uploads/{$user->id}/" . date('Y/m');
}
```

## Security Considerations

### 1. Always Validate File Paths

Before accessing any file, validate the path:

```php
if (!$filePolicy->isValidFilePath($filePath)) {
    abort(400, 'Invalid file path');
}
```

### 2. Check Authorization Before Download

Use the native policy or the service:

```php
// Method 1: Native policy (recommended)
$this->authorize('download', $documentRequest);

// Method 2: Service
if (!$filePolicy->canDownloadFile($user, $filePath, 'local')) {
    abort(403);
}
```

### 3. Log Suspicious Activity

All unauthorized attempts are logged. Monitor logs for patterns:

```bash
tail -f storage/logs/laravel.log | grep "Unauthorized file access"
```

### 4. Disable Direct URL Access (if applicable)

If using public disk, serve files through controller to enforce authorization:

```php
// DON'T: Let users access files directly via URL
// public/storage/requests/1/file.pdf

// DO: Serve through authenticated controller
Route::get('/download/{id}', [FileController::class, 'download'])->middleware('auth');
```

### 5. Validate File Types

Add additional validation in your controller:

```php
$validated = $request->validate([
    'attachment' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:5120',
]);
```

## Troubleshooting

### "Unauthorized file access" Error

**Cause**: User doesn't own the file or path is invalid

**Solution**:
1. Verify file is in `requests/{owner_id}/` directory
2. Check user is authenticated
3. Confirm file is attached to a `DocumentRequest` for that user

### "Invalid file path" Error

**Cause**: File path contains directory traversal sequences

**Solution**:
1. Never allow user input directly as file paths
2. Store file paths in database, not as URL parameters
3. Use ID-based lookups: `/download/123` instead of `/download/path/to/file.pdf`

### Files Not Accessible After Upload

**Cause**: File stored in wrong directory or policy not registered

**Solution**:
1. Verify `AuthServiceProvider` is in `bootstrap/providers.php`
2. Check file is stored in `requests/{user_id}/` format
3. Confirm middleware is applied to download routes

### Permission Denied for Admin

**Cause**: Admin role not recognized

**Solution**:
1. Verify admin user record has `role = 'admin'` or `'super_admin'`
2. Check policy checks for correct role values
3. Review database for user role

## Future Enhancements

Potential improvements to the file storage system:

1. **File Expiration**: Automatically delete old files
   ```php
   public function deleteExpiredFiles($daysOld = 90)
   ```

2. **File Versioning**: Keep track of document versions
   ```php
   storage/requests/1/document_v1_2024-01-15.pdf
   storage/requests/1/document_v2_2024-02-20.pdf
   ```

3. **Quota Management**: Limit storage per user
   ```php
   public function getUserStorageQuota(User $user)
   public function checkQuotaExceeded(User $user, int $fileSize)
   ```

4. **Encryption at Rest**: Encrypt sensitive files
   ```php
   public function encryptFile($filePath)
   public function decryptFile($filePath)
   ```

5. **Audit Trail**: Log all file accesses
   ```php
   FileAccessLog::create(['user_id' => $user->id, 'file' => $path])
   ```

6. **Virus Scanning**: Scan uploads for malware
   ```php
   public function scanFileForViruses($filePath)
   ```

## Summary

The file storage access policy system provides:

- ✅ **Multi-layer authorization**: Policies + Service + Middleware
- ✅ **Role-based access control**: Different permissions per role
- ✅ **Directory traversal prevention**: Blocks malicious paths
- ✅ **Ownership verification**: Users access only their files
- ✅ **Comprehensive logging**: Tracks unauthorized attempts
- ✅ **Native Laravel integration**: Uses built-in authorization
- ✅ **Extensive testing**: Unit and feature test coverage

This ensures the Barangay Inquirer System maintains file security while providing a seamless user experience.
