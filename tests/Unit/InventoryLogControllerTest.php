<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\InventoryLog;
use App\Models\Drug;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InventoryLogControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test index method for pharmacists.
     */
    public function test_index_returns_inventory_logs_for_pharmacist()
    {
        // Arrange: Create a pharmacist user and authenticate.
        $pharmacist = User::factory()->create(['is_role' => 2]);
        $this->actingAs($pharmacist);

        // Create inventory logs with associated drugs.
        $drug = Drug::factory()->create();
        InventoryLog::factory()->count(5)->create(['drug_id' => $drug->id]);

        // Act: Call the index method.
        $response = $this->getJson('/api/inventory/logs');

        // Assert: Check the response.
        $response->assertStatus(200);
        $responseData = $response->json();

        $this->assertCount(5, $responseData['data']['data']); // Ensure 5 logs are returned.
        $this->assertEquals($drug->id, $responseData['data']['data'][0]['drug']['id']); // Check associated drug.
    }

    /**
     * Test index method for non-pharmacists.
     */
    public function test_index_returns_403_for_non_pharmacist()
    {
        // Arrange: Create a non-pharmacist user and authenticate.
        $user = User::factory()->create(['is_role' => 1]); // Patient role
        $this->actingAs($user);

        // Act: Call the index method.
        $response = $this->getJson('/api/inventory/logs');

        // Assert: Check the response.
        $response->assertStatus(403);
        $responseData = $response->json();

        $this->assertEquals('Unauthorized. Only pharmacists can access this resource.', $responseData['message']);
    }
}