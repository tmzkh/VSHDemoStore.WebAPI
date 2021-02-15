<?php

namespace App\Actions;

use App\Models\Avatar;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class ClearUsersAvatarsAction
{
    /**
     * Execute action to clear user's avatars.
     *
     * @param User $user
     * @return boolean
     */
    public function execute(User $user) : bool
    {
        try {
            if (! $user->relationLoaded('avatars')) {
                $user->load('avatars');
            }

            $user->avatars->each(function (Avatar $avatar) {
                Storage::disk('azure-avatars')->delete($avatar->path);

                $avatar->delete();
            });
        } catch (\Throwable $th) {
            return false;
        }

        return true;
    }
}
