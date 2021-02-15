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

class GetAvatarTest extends TestCase
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

    /**
     * Mock up exsisting avatar.
     *
     * @param int $userId
     * @return void
     */
    private function mockExistingAvatar($userId)
    {
        $this->mockAvatarsDisk();

        $path = Storage::disk('azure-avatars')->putFileAs(
            $filePath ?? '',
            UploadedFile::fake()->create('avatar.obj', 10000, 'text/plain'),
            'avatar_101010.obj'
        );

        Avatar::create([
            'user_id' => $userId,
            'path' => $path
        ]);
    }

    /** @test */
    public function unauthenticatedUserCannotAccessAvatars()
    {
        $this->GET('api/userinfo/avatar')
            ->assertStatus(401);
    }

    /** @test */
    public function itReturnsNotFoundIfUserHasNotAvatar()
    {
        $user = User::factory()->create([
            'sub' => 'auth|subsub',
            'name' => 'First User',
            'email' => 'first@email.com',
            'gender' => Gender::defaultValue(),
        ]);

        $authUser = new AuthUser($user->getAttributes(), $user);

        $this->actingAs($authUser, 'auth0');

        $this->get('api/userinfo/avatar')
            ->assertStatus(404);
    }

    /** @test */
    public function itReturnsAvatarIfUserHasOne()
    {
        $user = User::factory()->create([
            'sub' => 'auth|subsub',
            'name' => 'First User',
            'email' => 'first@email.com',
            'gender' => Gender::defaultValue(),
        ]);

        $authUser = new AuthUser($user->getAttributes(), $user);

        $this->actingAs($authUser, 'auth0');

        $this->mockExistingAvatar($user->id);

        $this->get('api/userinfo/avatar')
            ->assertStatus(200)
            ->assertHeader('Content-Type', 'model/obj')
            ->assertHeader('Content-Disposition',
                'attachment; filename=avatar_101010.obj');
    }
}
