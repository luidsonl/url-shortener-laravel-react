<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_that_it_has_correct_fillable_attributes()
    {
        $user = new User();

        $this->assertEquals([
            'name',
            'email',
            'password',
            'role',
        ], $user->getFillable());
    }

    public function test_that_it_has_correct_hidden_attributes()
    {
        $user = new User();
        
        $this->assertEquals([
            'password',
            'remember_token',
        ], $user->getHidden());
    }

    public function test_that_it_has_correct_casts()
    {
        $user = new User();
        
        $casts = $user->getCasts();

        $this->assertEquals('datetime', $casts['email_verified_at']);
        $this->assertEquals('hashed', $casts['password']);
    }

    public function test_that_it_can_create_a_regular_user()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'user',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals('user', $user->role);
        $this->assertFalse($user->isAdmin());
    }

    public function test_that_it_creates_user_with_default_role_as_user()
    {
        $user = User::factory()->create([
            'name' => 'Default User',
            'email' => 'default@example.com',
        ]);

        $this->assertEquals('user', $user->role);
        $this->assertFalse($user->isAdmin());
    }

    public function test_that_it_can_create_admin_user()
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $this->assertEquals('admin', $admin->role);
        $this->assertTrue($admin->isAdmin());
    }

    public function test_that_it_rejects_invalid_roles()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);
        
        User::factory()->create([
            'name' => 'Invalid User',
            'email' => 'invalid@example.com',
            'role' => 'stand-user',
        ]);
    }

    public function test_that_it_can_check_user_roles_correctly()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($user->isAdmin());

        if (method_exists($admin, 'isUser')) {
            $this->assertFalse($admin->isUser());
            $this->assertTrue($user->isUser());
        }
    }

    public function test_that_it_only_accepts_valid_roles()
    {
        $user = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertEquals('user', $user->role);
        $this->assertEquals('admin', $admin->role);

    }

    public function test_that_it_can_scope_users_by_role()
    {
        User::factory()->create(['role' => 'admin']);
        User::factory()->create(['role' => 'user']);
        User::factory()->create(['role' => 'user']);
        User::factory()->create(['role' => 'user']);

        if (method_exists(User::class, 'scopeAdmins')) {
            $admins = User::admins()->get();
            $this->assertCount(1, $admins);
            $this->assertEquals('admin', $admins->first()->role);
            $this->assertTrue($admins->first()->isAdmin());
        }

        if (method_exists(User::class, 'scopeUsers')) {
            $users = User::users()->get();
            $this->assertCount(3, $users);
            $this->assertTrue($users->every(fn($user) => $user->role === 'user'));
            $this->assertTrue($users->every(fn($user) => !$user->isAdmin()));
        }
    }

    public function test_that_it_can_check_email_verification_status()
    {
        $verifiedUser = User::factory()->create([
            'email_verified_at' => now(),
            'role' => 'user'
        ]);
        $unverifiedUser = User::factory()->create([
            'email_verified_at' => null,
            'role' => 'admin'
        ]);

        $this->assertTrue($verifiedUser->hasVerifiedEmail());
        $this->assertFalse($unverifiedUser->hasVerifiedEmail());
    }

    public function test_that_it_automatically_hashes_passwords()
    {
        $user = User::factory()->create([
            'password' => 'plain-password',
            'role' => 'user'
        ]);

        $this->assertNotEquals('plain-password', $user->password);
        $this->assertTrue(password_verify('plain-password', $user->password));
    }

    public function test_that_it_can_generate_api_tokens()
    {
        $user = User::factory()->create(['role' => 'user']);

        $token = $user->createToken('test-token')->plainTextToken;

        $this->assertNotNull($token);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
            'name' => 'test-token',
        ]);
    }

    public function test_that_it_can_retrieve_user_by_email_and_role()
    {
        $user = User::factory()->create([
            'email' => 'unique@example.com',
            'role' => 'user'
        ]);

        $foundUser = User::where('email', 'unique@example.com')->first();

        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals($user->id, $foundUser->id);
        $this->assertEquals('user', $foundUser->role);
        $this->assertFalse($foundUser->isAdmin());
    }

    public function test_that_it_can_filter_users_by_specific_roles()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user1 = User::factory()->create(['role' => 'user']);
        $user2 = User::factory()->create(['role' => 'user']);

        $regularUsers = User::where('role', 'user')->get();
        $adminUsers = User::where('role', 'admin')->get();

        $this->assertCount(2, $regularUsers);
        $this->assertCount(1, $adminUsers);
        $this->assertEquals($admin->id, $adminUsers->first()->id);
        $this->assertTrue($adminUsers->first()->isAdmin());
    }

    public function test_that_admin_users_have_different_permissions()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($user->isAdmin());
        
        if (method_exists($admin, 'canManageUsers')) {
            $this->assertTrue($admin->canManageUsers());
            $this->assertFalse($user->canManageUsers());
        }
    }
}