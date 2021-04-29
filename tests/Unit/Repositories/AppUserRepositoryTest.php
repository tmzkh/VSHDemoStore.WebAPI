<?php

namespace Tests\Unit\Repositories;

use App\Models\User;
use App\Enums\Gender;
use App\Repositories\AppUserRepository;
use Database\Seeders\AppACLSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App as FacadesApp;
use Mockery;
use Tests\TestCase;

class AppUserRepositoryTest extends TestCase
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

        $this->withHeaders([
            'Authorization' => 'Bearer secure.bearer.token',
            'Accept' => 'application/json'
        ]);
    }

    /**
     * Mock fetching user info from Auth0 API.
     *
     * @return AppUserRepository
     */
    private function mockFetchesUserInfoFromAuth0()
    {
        $mock = Mockery::mock(AppUserRepository::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $mock
            ->shouldReceive('fetchUserInfo')
            ->andReturn([
                'sub' => 'auth|subsub',
                'email' => 'test@email.com',
                'name' => 'Name McNameFace',
            ]);

        return $mock;
    }

    /**
     * Mock up request instance to return some bearerToken.
     *
     * AppUserRepository@getUserByDecodedJWT uses request() -helper so we must mock up it.
     *
     * @return void
     */
    public function mockAppsRequestInstance()
    {
        $requestMock = Mockery::mock(Request::class)->makePartial();

        $requestMock
            ->shouldReceive('bearerToken')
            ->andReturn('Bearer secure.bearer.token');

        $this->app->instance('request', $requestMock);
    }

    /** @test */
    public function itCreatesNewUserFromRequestIfExistingNotFound()
    {
        $this->assertDatabaseCount((new User)->getTable(), 0);

        $this->mockAppsRequestInstance();

        $appUserRepository = $this->mockFetchesUserInfoFromAuth0();

        $appUserRepository->getUserByDecodedJWT([]);

        $this->assertDatabaseCount((new User)->getTable(), 1);

        $this->assertDatabaseHas((new User)->getTable(), [
            'sub' => 'auth|subsub',
            'email' => 'test@email.com',
            'name' => 'Name McNameFace',
            'gender' => 'other',
        ]);
    }

    /** @test */
    public function itCreatesNewUserWithCustomerRoleIfUserNotFound()
    {
        $this->mockAppsRequestInstance();

        $appUserRepository = $this->mockFetchesUserInfoFromAuth0();

        $authUser = $appUserRepository->getUserByDecodedJWT([]);

        $appUser = $authUser->getAppUser();

        $this->assertEquals(['Customer'], $appUser->roles->pluck('name')->toArray());
    }

    /** @test */
    public function itReturnsExistingUserIfFound()
    {
        $this->mockAppsRequestInstance();

        User::factory()->create([
            'sub' => 'auth|subsub',
            'email' => 'test@email.com',
            'name' => 'Name McNameFace',
            'gender' => 'woman',
        ]);

        $appUserRepository = $this->mockFetchesUserInfoFromAuth0();

        $authUser = $appUserRepository->getUserByDecodedJWT([]);

        $appUser = $authUser->getAppUser();

        $this->assertDatabaseCount((new User)->getTable(), 1);

        $this->assertEquals('Name McNameFace', $appUser->name);
        $this->assertEquals('test@email.com', $appUser->email);
        $this->assertEquals('auth|subsub', $appUser->sub);
        $this->assertEquals(Gender::WOMAN, $appUser->gender->value());
    }
}
