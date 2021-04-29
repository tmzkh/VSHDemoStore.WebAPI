<?php

namespace App\Http\Controllers\Api;

use App\Actions\ClearUsersAvatarsAction;
use App\Actions\StoreAvatarAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UploadAvatarFileRequest;
use App\Models\Avatar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;


class AvatarController extends Controller
{
    /**
     * Download authenticated user's avatar.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (Auth::user()->getAppUser()->avatars->count() < 1) {
            return response()->json(null, 404);
        }

        return Storage::disk('azure-avatars')->download(
            Auth::user()->getAppUser()->avatars->first()->path);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\Api\UploadAvatarFileRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(
        UploadAvatarFileRequest $request,
        StoreAvatarAction $storeAvatarAction
    ) {
        return $storeAvatarAction->execute($request, Auth::user()->getAppUser())
            ? response()->json(null, 201)
            : response()->json(null, 204);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Avatar  $avatar
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Avatar $avatar)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Avatar  $avatar
     * @return \Illuminate\Http\Response
     */
    public function destroy(ClearUsersAvatarsAction $clearAvatarsAction)
    {
        $clearAvatarsAction->execute(Auth::user()->getAppUser());

        return response()->json(null, 204);
    }
}
