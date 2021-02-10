<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    /**
     * Return app role listing.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (! Auth::user()->getAppUser()->hasRole('Admin')) {
            return response('Unauthorized', 401);
        }

        return response()->json(Role::all(['id', 'name']));
    }

    /**
     * Assing user admin role by user id.
     *
     * @param int $userId
     * @return \Illuminate\Http\Response
     */
    public function assignUserAdminRole($userId)
    {
        if (! Auth::user()->getAppUser()->hasRole('Admin')) {
            return response('Unauthorized', 401);
        }

        $user = User::findOrFail($userId);

        if ($user->hasRole('Customer')) {
            $user->removeRole('Customer');
        }

        if (! $user->hasRole('Admin')) {
            $user->assignRole('Admin');
        }

        return response('', 200);
    }

    /**
     * Remove admin role from user by user id.
     *
     * @param int $userId
     * @return \Illuminate\Http\Response
     */
    public function removeUserAdminRole($userId)
    {
        if (! Auth::user()->getAppUser()->hasRole('Admin')) {
            return response('Unauthorized', 401);
        }

        $user = User::findOrFail($userId);

        if ($user->hasRole('Admin')) {
            $user->removeRole('Admin');
        }

        return response('', 200);
    }
}
