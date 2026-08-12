<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\DocumentRequest;

class DocumentRequestFactory extends Factory
{
    protected $model = DocumentRequest::class;

    public function definition()
    {
        $types = ['Barangay Clearance', 'Purok Clearance', 'Certificate of Indigency', 'Cedula'];
        $statuses = ['Pending', 'Approved', 'Rejected'];

        return [
            'user_id' => \App\Models\User::factory(),
            'document_type' => $this->faker->randomElement($types),
            'purpose' => $this->faker->sentence(6),
            'status' => $this->faker->randomElement($statuses),
        ];
    }
}
