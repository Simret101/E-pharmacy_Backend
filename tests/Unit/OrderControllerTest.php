<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\OrderController;
use App\Models\Drug;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the store method to create a new order.
     */
    public function test_store_creates_order_successfully()
    {
        // Arrange: Create a user and a drug.
        $user = User::factory()->create(); // Create a user
        $drug = Drug::factory()->create(['stock' => 50, 'price' => 100]); // Create a drug with stock and price

        // Authenticate the user.
        $this->actingAs($user);

        // Mock the CloudinaryService.
        $cloudinaryService = $this->mock(\App\Customs\Services\CloudinaryService::class, function ($mock) {
            $mock->shouldReceive('uploadImage')->andReturn([
                'secure_url' => 'https://example.com/test_prescription.jpg',
                'public_id' => 'test_public_id',
            ]);
        });

        // Prepare order data.
        $orderData = [
            'drug_id' => $drug->id,
            'quantity' => 2,
            'prescription_image' => UploadedFile::fake()->image('prescription.jpg'),
        ];

        // Act: Call the store method.
        $response = $this->postJson('/api/orders', $orderData);

        // Assert: Check the response.
        $response->assertStatus(201);
        $responseData = $response->json();

        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('Order created successfully', $responseData['message']);
        $this->assertEquals($drug->id, $responseData['data']['drug_id']);
        $this->assertEquals(2, $responseData['data']['quantity']);
        $this->assertEquals(200, $responseData['data']['total_amount']); // 2 * 100 = 200

        // Assert that the drug stock was reduced.
        $this->assertEquals(48, $drug->fresh()->stock); // Original stock (50) - quantity (2)
    }
}