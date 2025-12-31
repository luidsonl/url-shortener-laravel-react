<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;
use Tests\TestCase;

class WelcomeEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function loginAndGetToken($email, $password = 'password')
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => $email,
            'password' => $password
        ]);

        return $response->json('access_token');
    }

    public function test_that_welcome_email_is_sent_after_registration()
    {
        Mail::fake();

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201);

        Mail::assertSent(WelcomeEmail::class, function ($mail) use ($userData) {

            return $mail->hasTo($userData['email']);
        });
    }

    public function test_that_welcome_email_has_correct_content_after_registration()
    {
        Mail::fake();

        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(201);

        Mail::assertSent(WelcomeEmail::class, function ($mail) use ($userData) {
            return $mail->hasTo($userData['email'])
                && str_contains($mail->envelope()->subject, 'Welcome')
                && str_contains($mail->render(), $userData['name']);
        });
    }

    public function test_that_no_email_is_sent_when_registration_fails()
    {
        Mail::fake();

        $userData = [
            'name' => 'John Doe',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/auth/register', $userData);

        $response->assertStatus(422);

        Mail::assertNotSent(WelcomeEmail::class);
    }



    public function test_welcome_email_is_sent_when_user_is_created()
    {
        Mail::fake();

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

        $response->assertStatus(201);

        Mail::assertSent(WelcomeEmail::class, function ($mail) use ($userData) {
            return $mail->hasTo($userData['email']);
        });
    }


    public function test_welcome_email_is_not_sent_on_user_update()
    {
        Mail::fake();

        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);

        $token = $this->loginAndGetToken('admin@example.com');

        $user = User::factory()->create();

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'user'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->putJson("/api/users/{$user->id}", $updateData);

        $response->assertStatus(200);

        // Verifica que NENHUM WelcomeEmail foi enviado durante atualização
        Mail::assertNotSent(WelcomeEmail::class);
    }

    public function test_no_email_is_sent_when_user_creation_fails()
    {
        Mail::fake();

        $admin = User::factory()->admin()->create([
            'email' => 'admin@example.com',
        ]);

        $token = $this->loginAndGetToken('admin@example.com');

        $existingUser = User::factory()->create(['email' => 'existing@example.com']);

        $userData = [
            'name' => 'New User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'user'
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/users', $userData);

        $response->assertStatus(422);

        Mail::assertNothingSent();
    }
}
