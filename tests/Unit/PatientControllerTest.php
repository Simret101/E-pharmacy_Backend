<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PatientControllerTest extends TestCase
{
    use RefreshDatabase;

   
    public function test_get_all_patients_returns_patients()
    {
        // Arrange: Create an admin user and authenticate.
        $admin = User::factory()->create(['is_role' => 0]); // Admin role
        $this->actingAs($admin);

        // Arrange: Create patients and other users.
        User::factory()->count(3)->create(['is_role' => 1]); // Patients
        User::factory()->count(2)->create(['is_role' => 2]); // Pharmacists

        // Act: Call the getAllPatients method.
        $response = $this->getJson('/api/admin/patients');

        // Assert: Check the response.
        $response->assertStatus(200);
        $responseData = $response->json();

        $this->assertEquals('success', $responseData['status']);
        $this->assertCount(3, $responseData['data']); // Only patients should be returned.
    }

    /**
     * Test index method with search parameter.
     */
   
}