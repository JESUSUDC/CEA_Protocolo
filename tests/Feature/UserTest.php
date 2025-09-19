<?php
declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class UserTest extends TestCase
{
    public function test_register_user(): void
    {
        $response = $this->postJson('/api/v1/users', [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => '123456'
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['id']);
    }

    public function test_login_user(): void
    {
        $response = $this->postJson('/api/v1/users/login', [
            'username_or_email' => 'testuser',
            'password' => '123456'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token']);
    }
}
