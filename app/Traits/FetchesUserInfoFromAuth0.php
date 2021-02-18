<?php

namespace App\Traits;

use GuzzleHttp\Client as ApiClient;

trait FetchesUserInfoFromAuth0
{
    protected function fetchUserInfo(string $accessToken) : array
    {
        $client = new ApiClient(['base_uri' => 'https://' . env('AUTH0_DOMAIN')]);

        $userInfo = $client->request('GET', 'userinfo', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Accept' => 'application/json',
            ]
        ]);

        $user = json_decode($userInfo->getBody()->getContents(), true);

        return $user;
    }
}
