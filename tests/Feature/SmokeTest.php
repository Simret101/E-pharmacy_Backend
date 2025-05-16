<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase; // Import the trait
use Tests\TestCase;

class SmokeTest extends TestCase
{
    use RefreshDatabase; // Use the trait to reset the database between tests

    /**
     * Test the home page loads successfully.
     */
    public function test_home_page_loads()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test the login page loads successfully.
     */
    public function test_login_api()
    {
        // Arrange: Create a test user
        $user = \App\Models\User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('Password@123'),
            'is_role' => 1,
            'phone' => '1234567890',
            'address' => '123 Main St',
        ]);

        // Act: Call the login API
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'Password@123',
        ]);

        // Assert: Check the response
        $response->assertStatus(200)
                 ->assertJson(['status' => 'success']);
    }

    /**
     * Test the API health check endpoint.
     */
    public function test_api_health_check()
    {
        $response = $this->getJson('/api/health-check');

        $response->assertStatus(200)
                 ->assertJson(['status' => 'ok']);
    }

    public function test_registration_api()
    {
        // Act: Call the registration API
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'joh@example.com', // Ensure this matches the assertion
            'password' => 'Password@123',
            'password_confirmation' => 'Password@123',
            'is_role' => 1, // Patient role
            'phone' => '1234567890',
            'address' => '123 Main St',
        ]);

        // Assert: Check the response
        $response->assertStatus(201)
                 ->assertJson(['status' => 'success']);

        // Assert: Check the database
        $this->assertDatabaseHas('users', [
            'email' => 'joh@example.com', // Corrected email
            'is_role' => 1,
        ]);
    }

    public function test_email_verification_success_page_loads()
    {
        $response = $this->get('/email/verified');

        $response->assertStatus(200);
    }
}
