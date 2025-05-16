<?php

namespace Tests\Unit;

use App\Http\Controllers\Api\DrugController;
use App\Models\Drug;
use Illuminate\Http\Request;
use Illuminate\Testing\TestResponse;
use Illuminate\Http\UploadedFile; // Add this import
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase; // Add this

class DrugControllerTest extends TestCase
{
    use RefreshDatabase; // Use this trait to reset the database

    /**
     * Test the index method to retrieve a list of drugs.
     */
    public function test_index_returns_drugs_successfully()
    {
        // Arrange: Create a user and fake drugs in the database.
        $user = \App\Models\User::factory()->create(); // Create a user
        Drug::factory()->count(3)->create([
            'created_by' => $user->id, // Use the user's ID
            'category' => 'Test Category', // Match the filter
            'price' => 50, // Ensure the price is within the range
        ]);

        // Debug: Check if drugs exist in the database.
        $this->assertEquals(3, Drug::count());

        // Create a mock request with filters.
        $request = Request::create('/api/drugs', 'GET', [
            'category' => 'Test Category',
            'min_price' => 10,
            'max_price' => 100,
        ]);

        // Act: Call the index method.
        $controller = new DrugController(app('App\Customs\Services\CloudinaryService'));
        $response = $controller->index($request);

        // Wrap the response in a TestResponse object.
        $testResponse = TestResponse::fromBaseResponse($response);

        // Assert: Check the response.
        $testResponse->assertStatus(200);
        $responseData = $testResponse->json();

        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('Drugs retrieved successfully', $responseData['message']);
        $this->assertCount(3, $responseData['data']); // Ensure 3 drugs are returned.
    }

    /**
     * Test the show method to retrieve a single drug.
     */
    public function test_show_returns_drug_successfully()
    {
        // Arrange: Create a user and a drug.
        $user = \App\Models\User::factory()->create();
        $drug = \App\Models\Drug::factory()->create(['created_by' => $user->id]);

        // Act: Call the show method.
        $controller = new DrugController(app('App\Customs\Services\CloudinaryService'));
        $response = $controller->show($drug->id);

        // Wrap the response in a TestResponse object.
        $testResponse = TestResponse::fromBaseResponse($response);

        // Assert: Check the response.
        $testResponse->assertStatus(200);
        $responseData = $testResponse->json();

        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('Drug retrieved successfully', $responseData['message']);
        $this->assertEquals($drug->id, $responseData['data']['id']);
    }

    /**
     * Test the store method to create a new drug.
     */
    public function test_store_creates_drug_successfully()
    {
        // Arrange: Create a pharmacist user and authenticate.
        $pharmacist = \App\Models\User::factory()->create(['is_role' => 2]);
        $this->actingAs($pharmacist);

        // Mock the CloudinaryService.
        $cloudinaryService = $this->mock(\App\Customs\Services\CloudinaryService::class, function ($mock) {
            $mock->shouldReceive('uploadImage')->andReturn([
                'secure_url' => 'https://example.com/test_image.jpg',
                'public_id' => 'test_public_id',
            ]);
        });

        // Prepare drug data.
        $drugData = [
            'name' => 'Test Drug',
            'description' => 'Test Description',
            'brand' => 'Test Brand',
            'price' => 100,
            'category' => 'Test Category',
            'dosage' => 'Test Dosage',
            'stock' => 50,
            'image' => UploadedFile::fake()->image('test_image.jpg'), // Use UploadedFile
        ];

        // Act: Call the store method.
        $response = $this->postJson('/api/drugs', $drugData);

        // Assert: Check the response.
        $response->assertStatus(201);
        $response->assertJson([
            'status' => 'success',
            'message' => 'Drug created successfully',
        ]);
    }
}