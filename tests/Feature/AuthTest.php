<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Src\Auth\Infrastructure\Models\UserEloquentModel;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = UserEloquentModel::factory()->vendedor()->create([
            'password' => 'secret-pass',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'secret-pass',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_password_is_rejected(): void
    {
        $user = UserEloquentModel::factory()->vendedor()->create();

        $this->from('/login')
            ->post('/login', [
                'email' => $user->email,
                'password' => 'incorrecta',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = UserEloquentModel::factory()->vendedor()->inactive()->create([
            'password' => 'secret-pass',
        ]);

        $this->from('/login')
            ->post('/login', [
                'email' => $user->email,
                'password' => 'secret-pass',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_can_logout(): void
    {
        $user = UserEloquentModel::factory()->vendedor()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }
}
