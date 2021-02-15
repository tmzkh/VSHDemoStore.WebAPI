<?php

namespace Tests\Feature\UserInfo;

use App\Models\AuthUser;
use App\Models\User;
use Database\Seeders\AppACLSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GetUserInfoTest extends TestCase
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
    public function unauthenticatedUserCannotAccessUserInfo()
    {
        $this->json('GET', 'api/userinfo')
            ->assertStatus(401);
    }

    /** @test */
    public function authenticatedUserCanAccessOwnUserInfo()
    {
        $user = User::factory()->create([
            'sub' => 'auth|subsub',
            'name' => 'First User',
            'email' => 'first@email.com'
        ]);

        $user->assignRole(['Admin']);

        $authUser = new AuthUser($user->getAttributes(), $user);

        $this->actingAs($authUser, 'auth0');

        $this->json('GET', 'api/userinfo')
            ->assertStatus(200)
            ->assertJson([
                'name' => 'First User',
                'sub' => 'auth|subsub',
                'email' => 'first@email.com',
                'roles' => [
                    'Admin',
                ],
            ]);
    }

    /** @test */
    public function forNewUserItReturnsIsNewUserAttributeAsTrue()
    {
        Carbon::setTestNow('2021-02-15T13:45:00+00:00');

        $user = User::factory()->create([
            'sub' => 'auth|subsub',
            'name' => 'First User',
            'email' => 'first@email.com'
        ]);

        $user->assignRole(['Admin']);

        $authUser = new AuthUser($user->getAttributes(), $user);

        $this->actingAs($authUser, 'auth0');

        $this->json('GET', 'api/userinfo')
            ->assertStatus(200)
            ->assertJson([
                'is_new_user' => 'true',
            ]);
    }

    /** @test */
    public function itReturnsFalseAsIsNewUserAfterUserIsUpdated()
    {
        Carbon::setTestNow('2021-02-15T13:45:00+00:00');

        $user = User::factory()->create([
            'sub' => 'auth|subsub',
            'name' => 'First User',
            'email' => 'first@email.com'
        ]);

        $user->assignRole(['Admin']);

        $authUser = new AuthUser($user->getAttributes(), $user);

        $this->actingAs($authUser, 'auth0');

        Carbon::setTestNow('2021-02-15T14:45:00+00:00');

        $user->update([
            'name' => 'First User modified',
        ]);

        $user->save();

        $this->json('GET', 'api/userinfo')
            ->assertStatus(200)
            ->assertJson([
                'is_new_user' => 'false',
            ]);
    }
}
