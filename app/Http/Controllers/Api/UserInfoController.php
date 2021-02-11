<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
        return response()
            ->json(UserInfoResource::make(Auth::user()->getAppUser()));
    }
}
