<?php

namespace Tests\Feature\Auth;

use App\Models\AuthUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function itAuthenticatesApi()
    {
        $this->json('GET', 'api/user')
            ->assertStatus(401);
    }

    /** @test */
    public function authenticatedUserCanAccessApi()
    {
        $user = User::factory()->create();

        $authUser = new AuthUser($user->getAttributes(), $user);

        $this->actingAs($authUser, 'auth0');

        $this->json('GET', 'api/user')
            ->assertStatus(200);
    }
}
