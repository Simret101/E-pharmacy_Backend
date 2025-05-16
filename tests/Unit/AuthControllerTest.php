<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user registration.
     */
    public function test_register_user_successfully()
    {
        // Arrange: Prepare registration data.
        $data = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'Password@123', // Updated password to meet format requirements
            'password_confirmation' => 'Password@123', // Add password confirmation
            'phone' => '1234567890', // Add phone number
            'is_role' => 1, // Patient role
        ];

        // Act: Call the register endpoint.
        $response = $this->postJson('/api/auth/register', $data);

        // Assert: Check the response.
        $response->assertStatus(201);
        $responseData = $response->json();

        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('User registered successfully. Please verify your email.', $responseData['message']);

        // Assert that the user was created in the database.
        $this->assertDatabaseHas('users', [
            'email' => 'johndoe@example.com',
            'is_role' => 1,
        ]);
    }

    /**
     * Test login with valid credentials.
     */
    public function test_login_user_successfully()
    {
        // Arrange: Create a user.
        $user = User::factory()->create([
            'email' => 'johndoe@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Act: Call the login endpoint.
        $response = $this->postJson('/api/auth/login', [
            'email' => 'johndoe@example.com',
            'password' => 'password123',
        ]);

        // Assert: Check the response.
        $response->assertStatus(200);
        $responseData = $response->json();

        $this->assertEquals('success', $responseData['status']);
        $this->assertEquals('Login successful', $responseData['message']);
        $this->assertArrayHasKey('access_token', $responseData['data']);
        $this->assertArrayHasKey('refresh_token', $responseData['data']);
    }

    /**
     * Test login with invalid credentials.
     */
    public function test_login_user_with_invalid_credentials()
    {
        // Arrange: Create a user.
        $user = User::factory()->create([
            'email' => 'johndoe@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Act: Call the login endpoint with incorrect password.
        $response = $this->postJson('/api/auth/login', [
            'email' => 'johndoe@example.com',
            'password' => 'wrongpassword',
        ]);

        // Assert: Check the response.
        $response->assertStatus(401);
        $responseData = $response->json();

        $this->assertEquals('failed', $responseData['status']);
        $this->assertEquals('Invalid credentials', $responseData['message']);
    }

 
   
}