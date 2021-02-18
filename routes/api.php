<?php

use App\Http\Controllers\Api\RolesController;
use App\Http\Controllers\Api\UserInfoController;
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

    Route::get('/userinfo', [UserInfoController::class, 'index']);

    Route::put('/userinfo', [UserInfoController::class, 'update']);

    Route::get('/roles', [RolesController::class, 'index']);

    Route::post('/roles/assign-admin/{userId}', [RolesController::class, 'assignUserAdminRole']);

    Route::post('/roles/remove-admin/{userId}', [RolesController::class, 'removeUserAdminRole']);
});
