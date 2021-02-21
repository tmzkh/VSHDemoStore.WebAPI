<?php

namespace Tests\Helpers\Traits;

use App\Models\Product;
use App\Models\Taxon;

trait SetsUpProductListing
{
    /** @var \App\Models\Product */
    private $product1,
        $product2,
        $product3,
        $product4,
        $product5;

    /** @var \App\Models\Taxon */
    private
        $menTaxonRoot,
        $womenTaxonRoot;

    /**
     * Set up list of products.
     *
     * @return void
     */
    private function setUpProducts()
    {
        $this->menTaxonRoot = Taxon::roots()->whereSlug('men')->first();
        $this->womenTaxonRoot = Taxon::roots()->whereSlug('women')->first();

        $this->product1 = Product::create(['name' => 'Black T-shirt', 'sku'  => 'ts-01']);
        $this->product1->addTaxon($this->menTaxonRoot->children()->whereSlug('t-shirts')->first());

        $this->product2 = Product::create(['name' => 'Blue Shoe', 'sku'  => 'ts-02']);
        $this->product2->addTaxon($this->menTaxonRoot->children()->whereSlug('shoes')->first());

        $this->product3 = Product::create(['name' => 'Red T-shirt', 'sku'  => 'ts-03']);
        $this->product3->addTaxon($this->menTaxonRoot->children()->whereSlug('t-shirts')->first());

        $this->product4 = Product::create(['name' => 'W Blue T-shirt','sku'  => 'ts-04']);
        $this->product4->addTaxon($this->womenTaxonRoot->children()->whereSlug('t-shirts')->first());

        $this->product5 = Product::create(['name' => 'W Red Shoe', 'sku'  => 'ts-05']);
        $this->product5->addTaxon($this->womenTaxonRoot->children()->whereSlug('shoes')->first());
    }
}
