<?php

namespace App\Providers;

use App\Models\Product;
use App\Models\Taxon;
use App\Models\Taxonomy;
use Illuminate\Support\ServiceProvider;
use Vanilo\Product\Contracts\Product as ProductContract;
use Vanilo\Category\Contracts\Taxon as TaxonContract;
use Vanilo\Category\Contracts\Taxonomy as TaxonomyContract;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \Auth0\Login\Contract\Auth0UserRepository::class,
            \App\Repositories\AppUserRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerExtendedVaniloModels();
    }

    /**
     * Register App\Models that extend Vanilo package models.
     *
     * @return void
     */
    private function registerExtendedVaniloModels()
    {
        $this->app->concord->registerModel(
            ProductContract::class, Product::class
        );

        $this->app->concord->registerModel(
            TaxonomyContract::class, Taxonomy::class
        );

        $this->app->concord->registerModel(
            TaxonContract::class, Taxon::class
        );
    }
}
