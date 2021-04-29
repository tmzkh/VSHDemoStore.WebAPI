<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Array of normalized role names.
     *
     * @var array
     */
    private static $roles = [
        'admin' => 'Admin'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        $role = static::$roles[$role] ?? 'Admin';

        if (! Auth::user()->getAppUser()->hasRole($role)) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }
}
