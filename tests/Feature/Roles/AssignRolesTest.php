<?php

namespace Tests\Feature\Roles;

use App\Models\AuthUser;
use App\Models\User;
use Database\Seeders\AppACLSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AssignRolesTest extends TestCase
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
    public function adminUserCanAssignOtherUserAdminRole()
    {
        $user = User::factory()->create();

        $user->assignRole(['Admin']);

        $authUser = new AuthUser($user->getAttributes(), $user);

        $otherUser = User::factory()->create();

        $this->actingAs($authUser, 'auth0');

        $this->json('POST', 'api/roles/assign-admin/' . $otherUser->id)
            ->assertStatus(200);

        $this->assertTrue($otherUser->hasRole('Admin'));
    }

    /** @test */
    public function adminUserCanRemoveOtherUserAdminRole()
    {
        $user = User::factory()->create();

        $user->assignRole(['Admin']);

        $authUser = new AuthUser($user->getAttributes(), $user);

        $otherUser = User::factory()->create();

        $this->actingAs($authUser, 'auth0');

        $this->json('POST', 'api/roles/remove-admin/' . $otherUser->id)
            ->assertStatus(200);

        $this->assertFalse($otherUser->hasRole('Admin'));
    }
}
