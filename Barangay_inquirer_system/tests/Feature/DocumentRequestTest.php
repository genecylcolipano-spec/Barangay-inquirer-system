<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\DocumentRequest;

class DocumentRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_resident_request()
    {
        $response = $this->get(route('resident.request'));
        $response->assertRedirect(route('login'));
    }

    public function test_resident_sees_prefilled_form()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('resident.request'));

        $response->assertStatus(200);
        $response->assertSeeText('Request Document');
        $response->assertSee($user->name);
        $response->assertSee($user->email);
    }

    public function test_resident_can_submit_request()
    {
        $user = User::factory()->create();

        $payload = [
            'document_type' => 'Barangay Clearance',
            'purpose' => 'Testing submission',
        ];

        $response = $this->actingAs($user)->post(route('resident.request.store'), $payload);

        $response->assertRedirect();

        $this->assertDatabaseHas('document_requests', [
            'user_id' => $user->id,
            'document_type' => 'Barangay Clearance',
            'purpose' => 'Testing submission',
        ]);
    }

    public function test_dashboard_shows_recent_requests()
    {
        $user = User::factory()->create();

        DocumentRequest::create([
            'user_id' => $user->id,
            'document_type' => 'Cedula',
            'purpose' => 'Testing recent',
            'status' => 'Pending',
        ]);

        $response = $this->actingAs($user)->get(route('resident.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Recent Requests');
        $response->assertSee('Cedula');
        $response->assertSee('requestStatusChart');
        $response->assertSee('requestsSparkline');
    }

    public function test_resident_can_cancel_request()
    {
        $user = User::factory()->create();

        $req = DocumentRequest::create([
            'user_id' => $user->id,
            'document_type' => 'Barangay Clearance',
            'purpose' => 'To cancel',
            'status' => 'Pending',
        ]);

        $response = $this->actingAs($user)->post(route('resident.request.cancel', $req->id));
        $response->assertRedirect();

        $this->assertDatabaseHas('document_requests', [
            'id' => $req->id,
            'status' => 'Cancelled',
        ]);
    }

    public function test_ajax_cancel_request_returns_json()
    {
        $user = User::factory()->create();

        $req = DocumentRequest::create([
            'user_id' => $user->id,
            'document_type' => 'Barangay Clearance',
            'purpose' => 'AJAX cancel',
            'status' => 'Pending',
        ]);

        $response = $this->actingAs($user)->postJson(route('resident.request.cancel', $req->id));

        $response->assertStatus(200)->assertJson(['status' => 'ok']);

        $this->assertDatabaseHas('document_requests', [
            'id' => $req->id,
            'status' => 'Cancelled',
        ]);
    }

    public function test_resident_requests_listing_page()
    {
        $user = User::factory()->create();

        DocumentRequest::factory()->count(15)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('resident.requests.index'));
        $response->assertStatus(200);
        $response->assertSee('Your Requests');
    }
}
