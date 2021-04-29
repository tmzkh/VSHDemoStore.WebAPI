<?php

namespace App\Traits;

use App\Models\Product;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Vanilo\Product\Models\ProductProxy;

/**
 * Trait to add 'has products' functionality to taxon (product categories). Not implemented in individual module, copied implementation from Vanilo/Framework: https://github.com/vanilophp/framework/blob/master/src/Models/Taxon.php
 */
trait HasProducts
{
    public function products(): MorphToMany
    {
        return $this->morphedByMany(
            ProductProxy::modelClass(),
            'model',
            'model_taxons',
            'taxon_id',
            'model_id'
        );
    }

    public function addProduct(Product $product): void
    {
        $this->products()->attach($product);
    }

    public function addProducts(iterable $products)
    {
        foreach ($products as $product) {
            if (! $product instanceof Product) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Every element passed to addProduct must be a Product object. Given `%s`.',
                        is_object($product) ? get_class($product) : gettype($product)
                    )
                );
            }
        }

        return $this->products()->saveMany($products);
    }

    public function removeProduct(Product $product)
    {
        return $this->products()->detach($product);
    }
}
