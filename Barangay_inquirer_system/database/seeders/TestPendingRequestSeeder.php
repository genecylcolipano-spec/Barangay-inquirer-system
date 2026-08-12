<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DocumentRequest;

class TestPendingRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DocumentRequest::create([
            'user_id' => 1,
            'full_name' => 'Test Resident',
            'address' => '123 Test Street, Barangay Test',
            'document_type' => 'barangay_clearance',
            'details' => 'For testing dashboard display',
            'status' => 'pending'
        ]);

        echo "✓ Created pending request for dashboard testing\n";
    }
}
