<?php

namespace Tests\Feature\Roles;

use App\Models\AuthUser;
use App\Models\User;
use Database\Seeders\AppACLSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GetRolesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AppACLSeeder::class);
    }

    /** @test */
    public function unauthenticatedCannotAccessRoles()
    {
        $this->json('GET', 'api/roles')
            ->assertStatus(401);
    }

    /** @test */
    public function nonAdminUserCannotAccessRoles()
    {
        $user = User::factory()->create();

        $authUser = new AuthUser($user->getAttributes(), $user);

        $this->actingAs($authUser, 'auth0');

        $this->json('GET', 'api/roles')
            ->assertStatus(401);
    }

    /** @test */
    public function adminUserCanAccessRoles()
    {
        $user = User::factory()->create();

        $user->assignRole(['Admin']);

        $authUser = new AuthUser($user->getAttributes(), $user);

        $this->actingAs($authUser, 'auth0');

        $this->json('GET', 'api/roles')
            ->assertStatus(200)
            ->assertJson([
                [
                    'id' => 1,
                    'name' => 'Admin'
                ],
                [
                    'id' => 2,
                    'name' => 'Customer'
                ]
            ]);
    }
}
