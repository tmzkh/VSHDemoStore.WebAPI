<?php

namespace Tests\Feature\UserInfo;

use App\Enums\Gender;
use App\Models\AuthUser;
use App\Models\User;
use Database\Seeders\AppACLSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class UpdateUserInfoTest extends TestCase
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
    public function unauthenticatedUserCannotUpdateUserInfo()
    {
        $this->json('PUT', 'api/userinfo')
            ->assertStatus(401);
    }

    /** @test */
    public function itValidatesNameWhenUpdatingUserInfo()
    {
        $user = User::factory()->create([
            'sub' => 'auth|subsub',
            'name' => 'First User',
            'email' => 'first@email.com',
            'gender' => Gender::defaultValue(),
        ]);

        $authUser = new AuthUser($user->getAttributes(), $user);

        $this->actingAs($authUser, 'auth0');

        $this->json('PUT', 'api/userinfo', [])
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => [
                        'The name field is required.'
                    ],
                ],
            ]);
    }

    /** @test */
    public function itValidatesGenderWhenUpdatingUserInfo()
    {
        $user = User::factory()->create([
            'sub' => 'auth|subsub',
            'name' => 'First User',
            'email' => 'first@email.com',
            'gender' => Gender::defaultValue(),
        ]);

        $authUser = new AuthUser($user->getAttributes(), $user);

        $this->actingAs($authUser, 'auth0');

        $this->json('PUT', 'api/userinfo', [
            'name' => 'First User modified',
            'gender' => 'mies'
        ])
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'gender' => [
                        'The selected gender is invalid.'
                    ],
                ],
            ]);

        $this->assertDatabaseMissing((new User)->getTable(), [
            'name' => 'First User modified',
            'gender' => 'mies'
        ]);
    }

    /** @test */
    public function authenticatedUserCanUpdateOwnUserInfo()
    {
        $user = User::factory()->create([
            'sub' => 'auth|subsub',
            'name' => 'First User',
            'email' => 'first@email.com',
            'gender' => Gender::defaultValue(),
        ]);

        $authUser = new AuthUser($user->getAttributes(), $user);

        $this->actingAs($authUser, 'auth0');

        $this->json('PUT', 'api/userinfo',
            [
                'name' => 'First User modified',
                'gender' => 'woman',
            ]
        )
            ->assertStatus(200)
            ->assertJson([
                'name' => 'First User modified',
                'gender' => 'woman',
            ]);

        $this->assertDatabaseHas((new User)->getTable(), [
            'name' => 'First User modified',
            'gender' => 'woman',
        ]);
    }
}
