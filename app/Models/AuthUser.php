<?php

namespace App\Models;

use Auth0\Login\Auth0JWTUser;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuthUser extends Auth0JWTUser
{
    protected $guard_name = 'api';

    /**
     * User model
     *
     * @var \App\Models\User
     */
    protected $appUser;

    /**
     * Auth0JWTUser constructor.
     *
     * @param array $userInfo
     */
    public function __construct(array $userInfo, User $appUser)
    {
        parent::__construct($userInfo);

        $this->appUser = $appUser;
    }

    /**
     * Get app user.
     *
     * @return \App\Models\User
     */
    public function getAppUser(): User
    {
        return $this->appUser;
    }
}
