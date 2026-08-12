<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\DocumentRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Notifications\AdminNotesUpdated;

class AdminNotesNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_notes_update_sends_notification()
    {
        // Create a resident user
        $resident = User::factory()->create();

        // Create a document request for the resident
        $documentRequest = DocumentRequest::create([
            'user_id' => $resident->id,
            'document_type' => 'Barangay Clearance',
            'purpose' => 'Testing notifications',
            'status' => 'Pending',
        ]);

        // Create an admin user
        $admin = User::factory()->create(['role' => 'admin']);

        // Mock the notification
        Notification::fake();

        // Act as admin and update notes
        $response = $this->actingAs($admin)
            ->post(route('admin.requests.update-notes', $documentRequest->id), [
                'notes' => 'Test notes from admin'
            ]);

        // Assert the response
        $response->assertRedirect()
            ->assertSessionHas('success', 'Notes updated successfully. Resident has been notified.');

        // Assert the database was updated
        $documentRequest->refresh();
        $this->assertEquals('Test notes from admin', $documentRequest->notes);

        // Assert notification was sent
        Notification::assertSentTo(
            $resident,
            AdminNotesUpdated::class,
            function ($notification, $channels) use ($documentRequest) {
                return $notification->documentRequest->id === $documentRequest->id
                    && $notification->oldNotes === null
                    && $notification->newNotes === 'Test notes from admin';
            }
        );
    }

    public function test_resident_receives_notification_in_dropdown()
    {
        // Create a resident user
        $resident = User::factory()->create();

        // Create a document request
        $documentRequest = DocumentRequest::create([
            'user_id' => $resident->id,
            'document_type' => 'Barangay Clearance',
            'purpose' => 'Testing dropdown',
            'status' => 'Pending',
        ]);

        // Send notification manually
        $resident->notify(new AdminNotesUpdated($documentRequest, null, 'Test notes'));

        // Act as resident and check recent notifications
        $response = $this->actingAs($resident)
            ->get(route('resident.notifications.recent'));

        $response->assertStatus(200);

        $notifications = $response->json();

        $this->assertCount(1, $notifications);
        $this->assertEquals('Request Notes Updated', $notifications[0]['title']);
        $this->assertStringContains('Admin has added notes', $notifications[0]['message']);
        $this->assertFalse($notifications[0]['read']);
    }

    public function test_unread_count_updates_correctly()
    {
        // Create a resident user
        $resident = User::factory()->create();

        // Create a document request
        $documentRequest = DocumentRequest::create([
            'user_id' => $resident->id,
            'document_type' => 'Barangay Clearance',
            'purpose' => 'Testing unread count',
            'status' => 'Pending',
        ]);

        // Send notification
        $resident->notify(new AdminNotesUpdated($documentRequest, null, 'Test notes'));

        // Check unread count
        $response = $this->actingAs($resident)
            ->get(route('resident.notifications.check-unread'));

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals(1, $data['unread_count']);

        // Mark as read
        $notification = $resident->unreadNotifications()->first();
        $this->actingAs($resident)
            ->post(route('resident.notifications.read', $notification->id));

        // Check unread count again
        $response = $this->actingAs($resident)
            ->get(route('resident.notifications.check-unread'));

        $data = $response->json();
        $this->assertEquals(0, $data['unread_count']);
    }
}