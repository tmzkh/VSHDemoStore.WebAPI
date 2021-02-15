<?php

namespace Tests\Feature\Avatar;

use App\Enums\Gender;
use App\Models\AuthUser;
use App\Models\Avatar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class UploadAvatarTest extends TestCase
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
    }

    /**
     * Mock azure-avatars disk in storage.
     *
     * @return void
     */
    private function mockAvatarsDisk()
    {
        Storage::fake('azure-avatars');
    }

    /** @test */
    public function unauthenticatedUserCannotAccessAvatars()
    {
        $this->json('POST', 'api/userinfo/avatar')
            ->assertStatus(401);
    }

    /** @test */
    public function itValidatesMissingAvatar()
    {
        $user = User::factory()->create([
            'sub' => 'auth|subsub',
            'name' => 'First User',
            'email' => 'first@email.com',
            'gender' => Gender::defaultValue(),
        ]);

        $authUser = new AuthUser($user->getAttributes(), $user);

        $this->actingAs($authUser, 'auth0');

        $this->json('POST', 'api/userinfo/avatar')
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'avatar' => [
                        'The avatar field is required.'
                    ]
                ]
            ]);

        $this->assertDatabaseCount((new Avatar)->getTable(), 0);
    }

    /** @test */
    public function itValidatesAvatarMIMEType()
    {
        $user = User::factory()->create([
            'sub' => 'auth|subsub',
            'name' => 'First User',
            'email' => 'first@email.com',
            'gender' => Gender::defaultValue(),
        ]);

        $authUser = new AuthUser($user->getAttributes(), $user);

        $this->actingAs($authUser, 'auth0');

        $this->json('POST', 'api/userinfo/avatar', [
            'avatar' => UploadedFile::fake()->image('avatar.jpeg')
        ])
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'avatar' => [
                        'The avatar must be a file of type: text/plain.'
                    ]
                ]
            ]);

        $this->assertDatabaseCount((new Avatar)->getTable(), 0);
    }

    /** @test */
    public function authenticatedUserCanUploadAnAvatar()
    {
        $user = User::factory()->create([
            'sub' => 'auth|subsub',
            'name' => 'First User',
            'email' => 'first@email.com',
            'gender' => Gender::defaultValue(),
        ]);

        $authUser = new AuthUser($user->getAttributes(), $user);

        $this->actingAs($authUser, 'auth0');

        $this->mockAvatarsDisk();

        $this->json('POST', 'api/userinfo/avatar', [
            'avatar' => UploadedFile::fake()->create('avatar.obj', 10000, 'text/plain'),
        ])
            ->assertStatus(201);

        $this->assertDatabaseCount((new Avatar)->getTable(), 1);

        $this->assertDatabaseHas((new Avatar)->getTable(), [
            'user_id' => $user->id,
        ]);

        $uploadedAvatar = Avatar::first();

        Storage::disk('azure-avatars')->assertExists($uploadedAvatar->path);
    }
}
