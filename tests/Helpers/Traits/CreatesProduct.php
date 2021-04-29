<?php

namespace Tests\Helpers\Traits;

use App\Models\Product;
use App\Models\Taxon;

trait CreatesProduct
{
    /** @var \App\Models\Product */
    protected $product;

    /**
     * Creates product for testing.
     *
     * Default: ['name' => 'New T-shirt', 'sku' => 'ts_01'], taxon: ['slug' => 't-shirts', 'parent' => ['slug' => 'women'] ]
     *
     * @param array $productAttributes
     * @param string $taxonSlug
     * @param string $rootTaxonSlug
     * @return void
     */
    private function createProdut(
        $productAttributes = [],
        $taxonSlug = 't-shirts',
        $rootTaxonSlug = 'women'
    ) {
        $this->product = Product::create(array_merge([
            'name' => 'New T-shirt',
            'sku' => 'ts_01',
        ], $productAttributes));

        $this->product->addTaxon(
            Taxon::whereSlug($taxonSlug)
                ->whereHas('parent', function($query) use($rootTaxonSlug) {
                    $query->whereSlug($rootTaxonSlug);
                }
            )->first()
        );
    }
}
