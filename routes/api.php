<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'as' => 'api.',
    'middleware' => ['force-json', 'auth:auth0']
], function() {

    Route::get('/user', function (Request $request) {
        return response()->json(['user' => Auth::user()->getUserInfo()]);;
    });
});

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     $user = $request->user()->toArray();

//     return response()->json([
//         'user' => $user,
//     ]);
// });
