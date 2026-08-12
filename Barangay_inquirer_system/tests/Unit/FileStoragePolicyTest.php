<?php

namespace Tests\Unit;

use App\Models\DocumentRequest;
use App\Models\User;
use App\Services\FileStoragePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FileStoragePolicyTest extends TestCase
{
    use RefreshDatabase;

    protected FileStoragePolicy $filePolicy;
    protected User $residentUser;
    protected User $adminUser;
    protected DocumentRequest $documentRequest;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filePolicy = app(FileStoragePolicy::class);

        // Create test users
        $this->residentUser = User::factory()->create([
            'role' => 'resident',
        ]);

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);

        // Create a document request
        $this->documentRequest = DocumentRequest::factory()->create([
            'user_id' => $this->residentUser->id,
            'attachment' => 'requests/1/test-document.pdf',
        ]);
    }

    /**
     * Test that user can check ownership of their own file
     */
    public function test_user_ownership_of_own_file(): void
    {
        $result = $this->filePolicy->isFileOwnedByUser(
            $this->residentUser,
            'requests/1/test-document.pdf'
        );

        $this->assertTrue($result);
    }

    /**
     * Test that user cannot claim ownership of another user's file
     */
    public function test_user_cannot_claim_ownership_of_other_file(): void
    {
        $anotherUser = User::factory()->create(['role' => 'resident']);

        $result = $this->filePolicy->isFileOwnedByUser(
            $anotherUser,
            'requests/1/test-document.pdf'
        );

        $this->assertFalse($result);
    }

    /**
     * Test that valid file paths pass validation
     */
    public function test_valid_file_path_passes_validation(): void
    {
        $validPaths = [
            'requests/1/document.pdf',
            'requests/123/file-name-with-dashes.docx',
            'requests/999/file_with_underscores.xlsx',
            'requests/1/nested/path/file.txt',
        ];

        foreach ($validPaths as $path) {
            $result = $this->filePolicy->isValidFilePath($path);
            $this->assertTrue($result, "Path '{$path}' should be valid");
        }
    }

    /**
     * Test that directory traversal attempts are blocked
     */
    public function test_directory_traversal_attempts_blocked(): void
    {
        $maliciousPaths = [
            '../../../etc/passwd',
            '../../sensitive-file.txt',
            'requests/1/../../../../etc/passwd',
            '.../.../../file.txt',
            'requests/../../../admin/secrets.env',
        ];

        foreach ($maliciousPaths as $path) {
            $result = $this->filePolicy->isValidFilePath($path);
            $this->assertFalse($result, "Malicious path '{$path}' should be blocked");
        }
    }

    /**
     * Test that user can download their own file
     */
    public function test_user_can_download_own_file(): void
    {
        $result = $this->filePolicy->canDownloadFile(
            $this->residentUser,
            'requests/1/test-document.pdf',
            'local'
        );

        $this->assertTrue($result);
    }

    /**
     * Test that user cannot download another user's file
     */
    public function test_user_cannot_download_other_file(): void
    {
        $anotherUser = User::factory()->create(['role' => 'resident']);

        $result = $this->filePolicy->canDownloadFile(
            $anotherUser,
            'requests/1/test-document.pdf',
            'local'
        );

        $this->assertFalse($result);
    }

    /**
     * Test that admin can download any file
     */
    public function test_admin_can_download_any_file(): void
    {
        $result = $this->filePolicy->canDownloadFile(
            $this->adminUser,
            'requests/1/test-document.pdf',
            'local'
        );

        $this->assertTrue($result);
    }

    /**
     * Test that super_admin can download any file
     */
    public function test_super_admin_can_download_any_file(): void
    {
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        $result = $this->filePolicy->canDownloadFile(
            $superAdmin,
            'requests/1/test-document.pdf',
            'local'
        );

        $this->assertTrue($result);
    }

    /**
     * Test getting allowed upload directory for user
     */
    public function test_get_allowed_upload_directory(): void
    {
        $directory = $this->filePolicy->getAllowedUploadDirectory($this->residentUser);

        $this->assertStringContainsString($this->residentUser->id, $directory);
        $this->assertStringContainsString('requests', $directory);
    }

    /**
     * Test that invalid file paths are rejected
     */
    public function test_invalid_file_paths_rejected(): void
    {
        $invalidPaths = [
            '',
            '/',
            'just-a-filename.txt', // Missing directory structure
            'some/random/path',     // Doesn't match expected pattern
        ];

        foreach ($invalidPaths as $path) {
            $result = $this->filePolicy->isValidFilePath($path);
            $this->assertFalse($result, "Invalid path '{$path}' should be rejected");
        }
    }

    /**
     * Test that enforceFileAccessPolicy returns correct result
     */
    public function test_enforce_file_access_policy(): void
    {
        // User can access own file
        $userAccess = $this->filePolicy->enforceFileAccessPolicy(
            $this->residentUser,
            'requests/1/test-document.pdf'
        );
        $this->assertTrue($userAccess);

        // Admin can access any file
        $adminAccess = $this->filePolicy->enforceFileAccessPolicy(
            $this->adminUser,
            'requests/1/test-document.pdf'
        );
        $this->assertTrue($adminAccess);

        // Different user cannot access
        $anotherUser = User::factory()->create(['role' => 'resident']);
        $otherUserAccess = $this->filePolicy->enforceFileAccessPolicy(
            $anotherUser,
            'requests/1/test-document.pdf'
        );
        $this->assertFalse($otherUserAccess);
    }

    /**
     * Test getting accessible files for user
     */
    public function test_get_accessible_files_for_user(): void
    {
        // Create multiple document requests for the resident
        DocumentRequest::factory()->count(3)->create([
            'user_id' => $this->residentUser->id,
        ]);

        // Create documents for another user
        $anotherUser = User::factory()->create(['role' => 'resident']);
        DocumentRequest::factory()->count(2)->create([
            'user_id' => $anotherUser->id,
        ]);

        $accessibleFiles = $this->filePolicy->getAccessibleFiles($this->residentUser, 'local');

        // Resident should only see their own files
        $this->assertCount(4, $accessibleFiles);

        // All files should belong to the resident
        foreach ($accessibleFiles as $file) {
            $this->assertStringContainsString((string) $this->residentUser->id, $file);
        }
    }

    /**
     * Test that accessible files for admin includes all files
     */
    public function test_admin_sees_all_accessible_files(): void
    {
        // Create multiple document requests for different users
        DocumentRequest::factory()->count(3)->create([
            'user_id' => $this->residentUser->id,
        ]);

        $anotherUser = User::factory()->create(['role' => 'resident']);
        DocumentRequest::factory()->count(2)->create([
            'user_id' => $anotherUser->id,
        ]);

        $accessibleFiles = $this->filePolicy->getAccessibleFiles($this->adminUser, 'local');

        // Admin should see all files (at least the test files)
        $this->assertGreaterThanOrEqual(5, count($accessibleFiles));
    }

    /**
     * Test file deletion authorization for owner
     */
    public function test_owner_can_delete_own_file(): void
    {
        $result = $this->filePolicy->canDeleteFile(
            $this->residentUser,
            'requests/1/test-document.pdf'
        );

        $this->assertTrue($result);
    }

    /**
     * Test file deletion authorization blocking non-owner
     */
    public function test_non_owner_cannot_delete_file(): void
    {
        $anotherUser = User::factory()->create(['role' => 'resident']);

        $result = $this->filePolicy->canDeleteFile(
            $anotherUser,
            'requests/1/test-document.pdf'
        );

        $this->assertFalse($result);
    }

    /**
     * Test admin can delete any file
     */
    public function test_admin_can_delete_any_file(): void
    {
        $result = $this->filePolicy->canDeleteFile(
            $this->adminUser,
            'requests/1/test-document.pdf'
        );

        $this->assertTrue($result);
    }
}
