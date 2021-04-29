<?php

namespace App\Repositories;

use App\Models\AuthUser;
use App\Models\User as User;
use Auth0\Login\Auth0User;
use Auth0\Login\Repository\Auth0UserRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Traits\FetchesUserInfoFromAuth0;

class AppUserRepository extends Auth0UserRepository
{
    use FetchesUserInfoFromAuth0;

    /**
     * Get or create user.
     *
     * @param array $profile
     * @return User
     */
    protected function upsertUser($profile): User {
        $existingUser = User::whereSub($profile['sub'])->first();

        if ($existingUser) {
            return $existingUser;
        }

        $user = User::create([
            'sub' => $profile['sub'],
            'email' => $profile['email'] ?? '',
            'name' => $profile['name'] ?? '',
        ]);

        $user->assignRole('Customer');

        return $user;
    }

    /**
     * @param array $decodedJwt
     * @return AuthUser
     */
    public function getUserByDecodedJWT(array $decodedJwt) : Authenticatable
    {
        $user = $this->fetchUserInfo(request()->bearerToken());

        $user = $this->upsertUser($user);

        $userInfo = $user->getAttributes();

        $userInfo = [
            'id' => $userInfo['id'] ?? '',
            'name' => $userInfo['name'] ?? '',
            'email' => $userInfo['email'] ?? '',
            'sub' => $userInfo['sub'] ?? '',
            'gender' => $userInfo['gender'] ?? '',
            'created_at' => $userInfo['created_at'] ?? '',
            'updated_at' => $userInfo['updated_at'] ?? '',
        ];

        return new AuthUser($userInfo, $user);
    }

    public function getUserByUserInfo(array $userinfo) : Authenticatable
    {
        $user = $this->upsertUser($userinfo['profile']);
        return new Auth0User($user->getAttributes(), $userinfo['accessToken']);
    }
}
