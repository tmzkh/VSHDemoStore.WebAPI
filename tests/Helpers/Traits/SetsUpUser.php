<?php

namespace Tests\Helpers\Traits;

use App\Enums\Gender;
use App\Models\AuthUser;
use App\Models\User;

trait SetsUpUser
{
    /** @var \App\Models\User */
    protected $user;

    /**
     * Set up user with role and act like it.
     *
     * Default: ['sub' => 'auth|subsub', 'name' => 'First User', 'email' => 'first@email.com', 'gender' => Gender::defaultValue()], role: 'Admin'
     *
     * @param array $userAttributes
     * @param string $role
     * @return void
     */
    protected function setUpUser($userAttributes = [], $role = 'Admin')
    {
        $this->user = User::factory()->create(array_merge([
            'sub' => 'auth|subsub',
            'name' => 'First User',
            'email' => 'first@email.com',
            'gender' => Gender::defaultValue(),
        ], $userAttributes));

        $this->user->assignRole([$role]);

        $authUser = new AuthUser($this->user->getAttributes(), $this->user);

        $this->actingAs($authUser, 'auth0');
    }
}
