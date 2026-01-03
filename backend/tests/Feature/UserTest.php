<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['auth.guards.api.driver' => 'sanctum']);
    }

    protected function loginAndGetToken($email, $password = 'password')
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => $email,
            'password' => $password
        ]);

        return $response->json('access_token');
    }

    public function test_admin_can_list_all_users()
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);
        
        $token = $this->loginAndGetToken('admin@example.com');

        // Create some test users
        User::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'users' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ]);

        $users = $response->json('users');
        $this->assertCount(4, $users); // 3 created + 1 admin
    }

    public function test_regular_user_cannot_list_users()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);
        
        $token = $this->loginAndGetToken('user@example.com');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users');

        $response->assertStatus(403)
            ->assertJson(['message' => 'Unauthorized access']);
    }

    public function test_admin_can_view_single_user()
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);
        
        $token = $this->loginAndGetToken('admin@example.com');

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com'
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'role' => 'user'
                ]
            ]);
    }

    public function test_regular_user_cannot_view_other_users()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);
        
        $token = $this->loginAndGetToken('user@example.com');

        $otherUser = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/users/{$otherUser->id}");

        $response->assertStatus(403)
            ->assertJson(['message' => 'Unauthorized access']);
    }

    public function test_regular_user_can_view_their_own_profile()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);
        
        $token = $this->loginAndGetToken('user@example.com');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'email' => 'user@example.com',
                ]
            ]);
    }

    public function test_admin_can_update_user()
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);
        
        $token = $this->loginAndGetToken('admin@example.com');

        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com'
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'user'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'name' => 'Updated Name',
                    'email' => 'updated@example.com',
                    'role' => 'user'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
    }

    public function test_admin_can_update_user_role_to_admin()
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);
        
        $token = $this->loginAndGetToken('admin@example.com');

        $user = User::factory()->create();

        $updateData = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'admin'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'role' => 'admin'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => 'admin'
        ]);
    }

    public function test_regular_user_cannot_update_other_users()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);
        
        $token = $this->loginAndGetToken('user@example.com');

        $otherUser = User::factory()->create();

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/users/{$otherUser->id}", $updateData);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Unauthorized access']);
    }

    public function test_regular_user_can_update_their_own_profile()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'name' => 'Original Name'
        ]);
        
        $token = $this->loginAndGetToken('user@example.com');

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'user@example.com',
            'role' => 'user'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id' => $user->id,
                    'name' => 'Updated Name',
                    'email' => 'user@example.com'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name'
        ]);
    }

    public function test_regular_user_cannot_change_their_own_role()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);
        
        $token = $this->loginAndGetToken('user@example.com');

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'user@example.com',
            'role' => 'admin' // Tentando se promover a admin
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200);

        // Verifica que o role não foi alterado apesar do usuário ter tentado
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'role' => 'user' // Permanece como user
        ]);
    }

    public function test_update_user_validates_required_fields()
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);
        
        $token = $this->loginAndGetToken('admin@example.com');

        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/users/{$user->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'role']);
    }

    public function test_update_user_validates_email_format()
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);
        
        $token = $this->loginAndGetToken('admin@example.com');

        $user = User::factory()->create();

        $updateData = [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'role' => 'user'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_update_user_validates_unique_email()
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);
        
        $token = $this->loginAndGetToken('admin@example.com');

        $user1 = User::factory()->create(['email' => 'existing@example.com']);
        $user2 = User::factory()->create();

        $updateData = [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'role' => 'user'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/users/{$user2->id}", $updateData);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_admin_can_delete_user()
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);
        
        $token = $this->loginAndGetToken('admin@example.com');

        $user = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'User deleted successfully']);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_regular_user_cannot_delete_users()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);
        
        $token = $this->loginAndGetToken('user@example.com');

        $otherUser = User::factory()->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/users/{$otherUser->id}");

        $response->assertStatus(403)
            ->assertJson(['message' => 'Unauthorized access']);

        $this->assertDatabaseHas('users', ['id' => $otherUser->id]);
    }

    public function test_regular_user_cannot_delete_their_own_account()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);
        
        $token = $this->loginAndGetToken('user@example.com');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(403)
            ->assertJson(['message' => 'Unauthorized access']);

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_delete_their_own_account()
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);
        
        $token = $this->loginAndGetToken('admin@example.com');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->deleteJson("/api/users/{$admin->id}");

        $response->assertStatus(422)
            ->assertJson(['message' => 'Cannot delete your own account']);

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_unauthenticated_user_cannot_access_user_management_routes()
    {
        $user = User::factory()->create();

        $routes = [
            ['method' => 'GET', 'url' => '/api/users'],
            ['method' => 'GET', 'url' => "/api/users/{$user->id}"],
            ['method' => 'PUT', 'url' => "/api/users/{$user->id}"],
            ['method' => 'DELETE', 'url' => "/api/users/{$user->id}"],
        ];

        foreach ($routes as $route) {
            $response = $this->json($route['method'], $route['url']);
            $response->assertStatus(401);
        }
    }

    public function test_admin_can_filter_users_by_role()
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);
        
        $token = $this->loginAndGetToken('admin@example.com');

        // Create 2 regular users
        User::factory()->count(2)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users?role=user');

        $response->assertStatus(200);

        $users = $response->json('users');
        $this->assertCount(2, $users);
        
        foreach ($users as $user) {
            $this->assertEquals('user', $user['role']);
        }
    }

    public function test_admin_can_search_users_by_name_or_email()
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);
        
        $token = $this->loginAndGetToken('admin@example.com');

        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/users?search=john');

        $response->assertStatus(200);

        $users = $response->json('users');
        $this->assertCount(1, $users);
        $this->assertEquals('John Doe', $users[0]['name']);
    }

    public function test_admin_can_create_new_user()
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);
        
        $token = $this->loginAndGetToken('admin@example.com');

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/users', $userData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => [
                    'id',
                    'name',
                    'email',
                    'role'
                ]
            ])
            ->assertJson([
                'user' => [
                    'name' => 'New User',
                    'email' => 'newuser@example.com',
                    'role' => 'user'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => 'user'
        ]);
    }

    public function test_regular_user_cannot_create_new_user()
    {
        $user = User::factory()->create([
            'email' => 'user@example.com',
        ]);
        
        $token = $this->loginAndGetToken('user@example.com');

        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/users', $userData);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Unauthorized access']);
    }
}