<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_that_it_has_correct_fillable_attributes()
    {
        $user = new User();

        $this->assertEquals([
            'name',
            'email',
            'password',
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

    public function test_that_it_can_create_a_user()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
    }

    public function test_that_it_can_check_email_verification_status()
    {
        $verifiedUser = User::factory()->create(['email_verified_at' => now()]);
        $unverifiedUser = User::factory()->create(['email_verified_at' => null]);

        $this->assertTrue($verifiedUser->hasVerifiedEmail());
        $this->assertFalse($unverifiedUser->hasVerifiedEmail());
    }

    public function test_that_it_automatically_hashes_passwords()
    {
        $user = User::factory()->create([
            'password' => 'plain-password'
        ]);

        $this->assertNotEquals('plain-password', $user->password);
        $this->assertTrue(password_verify('plain-password', $user->password));
    }

    public function test_that_it_can_generate_api_tokens()
    {
        $user = User::factory()->create();

        $token = $user->createToken('test-token')->plainTextToken;

        $this->assertNotNull($token);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
            'name' => 'test-token',
        ]);
    }

    public function test_that_it_can_retrieve_user_by_email()
    {
        $user = User::factory()->create([
            'email' => 'unique@example.com'
        ]);

        $foundUser = User::where('email', 'unique@example.com')->first();

        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals($user->id, $foundUser->id);
    }
}