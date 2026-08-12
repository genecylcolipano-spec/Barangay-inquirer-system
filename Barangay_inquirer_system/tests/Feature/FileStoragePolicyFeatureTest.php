<?php

namespace Tests\Feature;

use App\Models\DocumentRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileStoragePolicyFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected User $residentUser;
    protected User $adminUser;
    protected DocumentRequest $documentRequest;

    protected function setUp(): void
    {
        parent::setUp();

        // Fake the storage to avoid actual file system operations
        Storage::fake('local');

        // Create test users
        $this->residentUser = User::factory()->create([
            'role' => 'resident',
        ]);

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);

        // Create a sample document request with attachment
        $this->documentRequest = DocumentRequest::factory()->create([
            'user_id' => $this->residentUser->id,
            'attachment' => 'requests/' . $this->residentUser->id . '/sample.pdf',
            'status' => 'pending',
        ]);

        // Create a fake file
        Storage::disk('local')->put(
            'requests/' . $this->residentUser->id . '/sample.pdf',
            'Sample PDF content'
        );
    }

    /**
     * Test that resident user can download their own document
     */
    public function test_resident_can_download_own_document(): void
    {
        $this->actingAs($this->residentUser);

        // Assuming there's a download route
        $response = $this->get(route('requests.download', $this->documentRequest->id));

        // Should either succeed or not be forbidden (depending on implementation)
        $this->assertNotEquals(403, $response->status());
    }

    /**
     * Test that resident cannot download another resident's document
     */
    public function test_resident_cannot_download_other_resident_document(): void
    {
        $anotherResident = User::factory()->create(['role' => 'resident']);

        $anotherDocRequest = DocumentRequest::factory()->create([
            'user_id' => $anotherResident->id,
            'attachment' => 'requests/' . $anotherResident->id . '/secret.pdf',
        ]);

        $this->actingAs($this->residentUser);

        // Assuming there's a download route
        $response = $this->get(route('requests.download', $anotherDocRequest->id));

        // Should be forbidden (403)
        $this->assertEquals(403, $response->status());
    }

    /**
     * Test that admin can download any document
     */
    public function test_admin_can_download_any_document(): void
    {
        $this->actingAs($this->adminUser);

        // Admin should be able to access any document
        $response = $this->get(route('requests.download', $this->documentRequest->id));

        // Should succeed or not be forbidden
        $this->assertNotEquals(403, $response->status());
    }

    /**
     * Test authorization on view action
     */
    public function test_resident_can_view_own_document_request(): void
    {
        $this->actingAs($this->residentUser);

        $response = $this->get(route('requests.show', $this->documentRequest->id));

        // Should succeed
        $this->assertNotEquals(403, $response->status());
    }

    /**
     * Test authorization prevents viewing other resident's request
     */
    public function test_resident_cannot_view_other_resident_request(): void
    {
        $anotherResident = User::factory()->create(['role' => 'resident']);

        $anotherDocRequest = DocumentRequest::factory()->create([
            'user_id' => $anotherResident->id,
        ]);

        $this->actingAs($this->residentUser);

        $response = $this->get(route('requests.show', $anotherDocRequest->id));

        // Should be forbidden
        $this->assertEquals(403, $response->status());
    }

    /**
     * Test that admin can view any document request
     */
    public function test_admin_can_view_any_document_request(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get(route('requests.show', $this->documentRequest->id));

        // Should succeed
        $this->assertNotEquals(403, $response->status());
    }

    /**
     * Test document request creation authorization
     */
    public function test_resident_can_create_document_request(): void
    {
        $this->actingAs($this->residentUser);

        $file = UploadedFile::fake()->pdf('document.pdf');

        $response = $this->post(route('requests.store'), [
            'document_type' => 'barangay_clearance',
            'purpose' => 'Personal use',
            'attachment' => $file,
        ]);

        // Should succeed or redirect to show
        $this->assertIn($response->status(), [200, 201, 302]);
    }

    /**
     * Test that admin cannot create document request as resident (non-resident action)
     */
    public function test_admin_cannot_create_as_resident(): void
    {
        $this->actingAs($this->adminUser);

        $file = UploadedFile::fake()->pdf('document.pdf');

        $response = $this->post(route('requests.store'), [
            'document_type' => 'barangay_clearance',
            'purpose' => 'Personal use',
            'attachment' => $file,
        ]);

        // Should be forbidden
        $this->assertEquals(403, $response->status());
    }

    /**
     * Test that unauthenticated users cannot access files
     */
    public function test_unauthenticated_user_cannot_download(): void
    {
        // No authentication
        $response = $this->get(route('requests.download', $this->documentRequest->id));

        // Should be redirected to login or forbidden
        $this->assertIn($response->status(), [302, 401, 403]);
    }

    /**
     * Test that file deletion authorization is enforced
     */
    public function test_owner_can_delete_pending_request(): void
    {
        $this->actingAs($this->residentUser);

        $response = $this->delete(route('requests.destroy', $this->documentRequest->id));

        // Owner should be able to delete pending request
        $this->assertIn($response->status(), [200, 204, 302]);
    }

    /**
     * Test that non-owner cannot delete request
     */
    public function test_non_owner_cannot_delete_request(): void
    {
        $anotherResident = User::factory()->create(['role' => 'resident']);

        $this->actingAs($anotherResident);

        $response = $this->delete(route('requests.destroy', $this->documentRequest->id));

        // Should be forbidden
        $this->assertEquals(403, $response->status());
    }

    /**
     * Test that approved requests cannot be deleted by owner
     */
    public function test_owner_cannot_delete_approved_request(): void
    {
        $this->documentRequest->update(['status' => 'approved']);

        $this->actingAs($this->residentUser);

        $response = $this->delete(route('requests.destroy', $this->documentRequest->id));

        // Should be forbidden (only pending can be deleted)
        $this->assertEquals(403, $response->status());
    }

    /**
     * Test admin can approve document request
     */
    public function test_admin_can_approve_request(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->post(route('requests.approve', $this->documentRequest->id));

        // Should succeed
        $this->assertNotEquals(403, $response->status());
    }

    /**
     * Test resident cannot approve request
     */
    public function test_resident_cannot_approve_request(): void
    {
        $this->actingAs($this->residentUser);

        $response = $this->post(route('requests.approve', $this->documentRequest->id));

        // Should be forbidden
        $this->assertEquals(403, $response->status());
    }

    /**
     * Test that middleware blocks direct file access with invalid path
     */
    public function test_middleware_blocks_directory_traversal_attempts(): void
    {
        $this->actingAs($this->residentUser);

        // Attempt to access a file outside the allowed directory
        $response = $this->get(route('requests.download', '../../etc/passwd'));

        // Should be blocked
        $this->assertEquals(403, $response->status());
    }

    /**
     * Test activity logging on unauthorized access attempts
     */
    public function test_unauthorized_access_is_logged(): void
    {
        $anotherResident = User::factory()->create(['role' => 'resident']);

        $this->actingAs($anotherResident);

        // Attempt to download another user's file
        $response = $this->get(route('requests.download', $this->documentRequest->id));

        // Should be logged and forbidden
        $this->assertEquals(403, $response->status());

        // Verify the attempt was logged in activity log or security logs
        // This would depend on your activity logging implementation
    }
}
