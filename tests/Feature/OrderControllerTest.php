<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\User;
use App\Models\Drug;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Notification;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_order_fakely_passes()
    {
        // Fake storage to prevent actual file operations
        Storage::fake('public');

        // Create a user and authenticate
        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a drug with enough stock
        $drug = Drug::factory()->create([
            'stock' => 100,
            'price' => 50,
            'user_id' => $user->id, // pharmacist
        ]);

        // Fake the notification system
        Notification::fake();

        // Mock the CloudinaryService
        $this->mock(\App\Customs\Services\CloudinaryService::class, function ($mock) {
            $mock->shouldReceive('uploadImage')->andReturn([
                'secure_url' => 'https://fake-cloudinary-url.com/fake.jpg'
            ]);
        });

        // Send POST request to the order store endpoint
        $response = $this->postJson(route('orders.store'), [
            'drug_id' => $drug->id,
            'quantity' => 2,
            'prescription_image' => UploadedFile::fake()->image('prescription.jpg'),
        ]);

        // Assert successful response
        $response->assertStatus(201);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Order created successfully',
        ]);
    }
}
