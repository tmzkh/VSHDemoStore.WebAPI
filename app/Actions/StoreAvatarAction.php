<?php

namespace App\Actions;

use App\Http\Requests\Api\UploadAvatarFileRequest;
use App\Models\Avatar;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreAvatarAction
{
    /**
     * Action to clear previous avatars.
     *
     * @var \App\Actions\ClearUsersAvatarsAction
     */
    private $clearAvatarsAction;

    public function __construct(ClearUsersAvatarsAction $clearAvatarsAction)
    {
        $this->clearAvatarsAction = $clearAvatarsAction;
    }

    /**
     * Uploads avatar file to default disk. If user has previously saved avatars, they will be cleared first.
     *
     * @param UploadAvatarFileRequest $request
     * @param User $user
     * @param string|null $fileName
     * @param string|null $filePath
     * @return boolean Indicates if file upload is successful or not.
     */
    public function execute(
        UploadAvatarFileRequest $request,
        User $user,
        ?string $fileName = null,
        ?string $filePath = null
    ) : bool
    {
        if (! $this->clearUsersAvatars($user)) {
            return false;
        }

        $path = Storage::disk('azure-avatars')->putFileAs(
            $filePath ?? '',
            $request->file('avatar'),
            $fileName
                ? $fileName
                : 'avatar_' . $user->id . Str::random(15) . '.obj'
        );

        if (! $path) {
            return false;
        }

        Avatar::create([
            'user_id' => $user->id,
            'path' => $path
        ]);

        return true;
    }

    /**
     * Executes ClearUsersAvatarsAction.
     *
     * @param User $user
     * @return boolean Indicates if action was successful.
     */
    public function clearUsersAvatars(User $user) : bool
    {
        if ($user->avatars()->count() > 0) {
            return $this->clearAvatarsAction->execute($user);
        }

        return true;
    }
}
