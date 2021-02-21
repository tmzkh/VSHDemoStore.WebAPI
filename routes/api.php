<?php

use App\Http\Controllers\Api\AvatarController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\RolesController;
use App\Http\Controllers\Api\TaxonController;
use App\Http\Controllers\Api\TaxonomyController;
use App\Http\Controllers\Api\UserInfoController;
use Illuminate\Support\Facades\Route;

/*
|------------------------------------------------------------------------------
| API Routes
|------------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group([
    'as' => 'api.',
    'middleware' => ['force-json']
], function() {

    /*
    |------------------------------------------------------------------------------
    | Authenticated API routes
    |------------------------------------------------------------------------------
    */
    Route::group([
        'middleware' => ['auth:auth0']
    ], function() {

        /*
        |--------------------------------------------------------------------------
        | Userinfo management routes
        |--------------------------------------------------------------------------
        */
        Route::group([
            'as' => 'userinfo.',
            'prefix' => 'userinfo',
        ], function () {
            Route::get('/', [UserInfoController::class, 'index'])->name('get');

            Route::put('/', [UserInfoController::class, 'update'])->name('update');;

            Route::get('/avatar', [AvatarController::class, 'index'])->name('avatar.get');

            Route::post('/avatar', [AvatarController::class, 'store'])->name('avatar.update');
        });

        /*
        |--------------------------------------------------------------------------
        | Admin routes
        |--------------------------------------------------------------------------
        |
        | These routes require admin role.
        |
        */
        Route::group([
            'middleware' => ['check-role:admin'],
        ], function () {

            /*
            |----------------------------------------------------------------------
            | Role management routes
            |----------------------------------------------------------------------
            */
            Route::group([
                'as' => 'roles.',
                'prefix' => 'roles'
            ], function() {
                Route::get('/', [RolesController::class, 'index'])->name('index');

                Route::post('/assign-admin/{userId}', [
                    RolesController::class,
                    'assignUserAdminRole'
                ])->name('assign-admin');

                Route::post('/remove-admin/{userId}', [
                    RolesController::class,
                    'removeUserAdminRole'
                ])->name('remove-admin');
            });

            /*
            |----------------------------------------------------------------------
            | Product management routes
            |----------------------------------------------------------------------
            */
            Route::group([
                'as' => 'products.',
            ], function () {
                Route::resource('products', ProductController::class)
                    ->only([
                        'store',
                        'update',
                        'destroy'
                    ])->names([
                        'store' => 'store',
                        'update' => 'update',
                        'destroy' => 'destroy'
                    ]);
            });
        });
    });

    /*
    |------------------------------------------------------------------------------
    | Public API routes
    |------------------------------------------------------------------------------
    */
    Route::group([
        'as' => 'public.',
    ], function() {

        /*
        |--------------------------------------------------------------------------
        | Public product routes
        |--------------------------------------------------------------------------
        */
        Route::group([
            'as' => 'prodcuts.',
            'prefix' => 'products',
        ], function() {
            Route::get('/{categorySlug}', [ProductController::class, 'index'])->name('index');
        });

        /*
        |--------------------------------------------------------------------------
        | Public taxonomy routes
        |--------------------------------------------------------------------------
        */
        Route::group([
            'as' => 'taxonomies.',
            'prefix' => 'taxonomies',
        ], function() {
            Route::get('/', [TaxonomyController::class, 'index'])->name('index');
        });

        /*
        |--------------------------------------------------------------------------
        | Public taxon routes
        |--------------------------------------------------------------------------
        */
        Route::group([
            'as' => 'taxons.',
            'prefix' => 'taxons',
        ], function() {
            Route::get('/', [TaxonController::class, 'index'])->name('index');
        });
    });
});
