<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class UserTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_that_user_can_register_through_api()
    {
        $userData = [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertCreated()
                ->assertJsonStructure([
                    'user' => [
                        'id',
                        'name',
                        'email',
                        'created_at',
                    ],
                    'access_token'
                ]);

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => $userData['email']
        ]);
    }

    public function test_that_user_can_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertOk()
                ->assertJsonStructure([
                    'user' => ['id', 'name', 'email'],
                    'access_token'
                ]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_that_user_cannot_login_with_incorrect_credentials()
    {
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized()
                ->assertJson(['message' => 'Invalid credentials']);

        $this->assertGuest();
    }

    public function test_that_authenticated_user_can_retrieve_their_profile_via_api()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                        ->getJson('/api/user');

        $response->assertOk()
                ->assertJson([
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]);
    }

    public function test_that_user_can_update_their_profile()
    {
        $user = User::factory()->create();
        $newEmail = $this->faker->unique()->safeEmail();

        $response = $this->actingAs($user, 'sanctum')
                        ->putJson('/api/user/profile', [
                            'name' => 'Updated Name',
                            'email' => $newEmail,
                        ]);

        $response->assertOk()
                ->assertJson(['message' => 'Profile updated successfully']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => $newEmail,
        ]);
    }

    public function test_that_user_can_logout()
    {
        $user = User::factory()->create();
        
        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $token = $loginResponse->json('access_token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        $response->assertOk()
                ->assertJson(['message' => 'Logged out successfully']);
    }

    public function test_that_unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/user');

        $response->assertUnauthorized();
    }

    public function test_that_user_can_change_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old_password')
        ]);

        $response = $this->actingAs($user, 'sanctum')
                        ->putJson('/api/user/password', [
                            'current_password' => 'old_password',
                            'password' => 'new_password123',
                            'password_confirmation' => 'new_password123',
                        ]);

        $response->assertOk()
                ->assertJson(['message' => 'Password updated successfully']);

        $this->assertTrue(Hash::check('new_password123', $user->fresh()->password));
    }

    public function test_that_registration_requires_valid_data()
    {
        $response = $this->postJson('/api/register', [
            'name' => '',
            'email' => 'invalid-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ]);

        $response->assertUnprocessable()
                ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_that_user_can_refresh_token()
    {
        $user = User::factory()->create();
        
        $loginResponse = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $oldToken = $loginResponse->json('access_token');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $oldToken,
        ])->postJson('/api/refresh');

        $response->assertOk()
                ->assertJsonStructure(['access_token']);
    }

    public function test_that_user_cannot_register_with_existing_email()
    {
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com'
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertUnprocessable()
                ->assertJsonValidationErrors(['email']);
    }

    public function test_that_user_can_delete_their_account()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
                        ->deleteJson('/api/user');

        $response->assertOk()
                ->assertJson(['message' => 'Account deleted successfully']);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id
        ]);
    }
}