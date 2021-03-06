<?php

namespace App\Http\Controllers\Api;

use App\Enums\Gender;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UpdateUserInfoRequest;
use App\Http\Resources\UserInfoResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserInfoController extends Controller
{
    /**
     * Get user info of authenticated user.
     *
     * @param int $userId
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user()->getAppUser();

        $user->load(['avatars']);

        return response()->json(UserInfoResource::make($user));
    }

    /**
     * Update authenticated user's user info.
     *
     * @param App\Http\Requests\Api\UpdateUserInfoRequest $request
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateUserInfoRequest $request)
    {
        $user = Auth::user()->getAppUser();

        $values = $request->validated();

        if (! empty($values['gender']) && ! Gender::has($values['gender'])) {
            unset($values['gender']);
        }

        $user->update($values);

        return response()
            ->json(UserInfoResource::make($user));
    }
}
