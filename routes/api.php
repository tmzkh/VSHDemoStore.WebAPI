<?php

use App\Http\Controllers\Api\RolesController;
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
        return response()->json(Auth::user()->getAppUser());;
    });

    Route::get('/roles', [RolesController::class, 'index']);

    Route::post('/roles/assign-admin/{userId}', [RolesController::class, 'assignUserAdminRole']);

    Route::post('/roles/remove-admin/{userId}', [RolesController::class, 'removeUserAdminRole']);
});
