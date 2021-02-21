<?php

namespace Tests\Unit\QueryBuilders;

use App\Models\Product;
use App\Models\Taxon;
use App\Models\Taxonomy;
use App\QueryBuilders\ProductQueryBuilder;
use Database\Seeders\TestCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Helpers\Traits\SetsUpProductListing;
use Tests\TestCase;

class ProductQueryBuilderTest extends TestCase
{
    use RefreshDatabase, SetsUpProductListing;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TestCategorySeeder::class);

        $this->setUpProducts();
    }

    /** @test */
    public function itCanQueryProductsByFirstTaxonLevel()
    {
        $products = (new ProductQueryBuilder())->build([
            'women'
        ], [], null)->get();

        $this->assertCount(2, $products);
        $this->assertTrue($products->contains($this->product4));
        $this->assertTrue($products->contains($this->product5));
    }

    /** @test */
    public function itCanQueryProductsBySecondTaxonLevel()
    {
        $products = (new ProductQueryBuilder())->build([], [
            't-shirts'
        ])->get();

        $this->assertCount(3, $products);
        $this->assertTrue($products->contains($this->product1));
        $this->assertTrue($products->contains($this->product3));
        $this->assertTrue($products->contains($this->product4));
    }

    /** @test */
    public function itCanQueryProductsByBothTaxonLevels()
    {
        $products = (new ProductQueryBuilder())->build(
            ['men'],
            ['t-shirts']
        )->get();

        $this->assertCount(2, $products);
        $this->assertTrue($products->contains($this->product1));
        $this->assertTrue($products->contains($this->product3));
    }

    /** @test */
    public function itCanQueryProductsByTaxonomy()
    {
        $taxonomy = Taxonomy::first();

        // should not be fetched
        $product6 = Product::create(['name' => 'W Red Shoe', 'sku'  => 'ts-05']);

        $fakeTaxonomy = Taxonomy::create(['name' => 'Fake product type']);

        $fakeTaxon = Taxon::create([
            'name' => 'fake taxonomy',
            'taxonomy_id' => $fakeTaxonomy->id
        ]);

        $product6->addTaxon($fakeTaxon);

        $products = (new ProductQueryBuilder())->build([], [], $taxonomy->slug)->get();

        $this->assertCount(5, $products);
        $this->assertTrue($products->contains($this->product1));
        $this->assertTrue($products->contains($this->product2));
        $this->assertTrue($products->contains($this->product3));
        $this->assertTrue($products->contains($this->product4));
        $this->assertTrue($products->contains($this->product5));
    }

    /** @test */
    public function itCanQueryProductsByTaxonomyAndFirstLevelTaxon()
    {
        $taxonomy = Taxonomy::first();

        // should not be fetched
        $product6 = Product::create(['name' => 'Fake product', 'sku'  => 'ts-05']);

        $fakeTaxonomy = Taxonomy::create(['name' => 'Fake product type']);

        $fakeTaxon = Taxon::create([
            'name' => 'women',
            'taxonomy_id' => $fakeTaxonomy->id
        ]);

        $fakeSecondTaxon = Taxon::create([
            'name' => 'accessories',
            'taxonomy_id' => $fakeTaxonomy->id,
            'parent_id' => $fakeTaxon->id
        ]);

        $product6->addTaxon($fakeSecondTaxon);

        $products = (new ProductQueryBuilder())->build([
            'women'
        ], [], $taxonomy->slug)->get();

        $this->assertCount(2, $products);
        $this->assertTrue($products->contains($this->product4));
        $this->assertTrue($products->contains($this->product5));
    }

    /** @test */
    public function itCanQueryProductsByTaxonomyAndSecondLevelTaxon()
    {
        $taxonomy = Taxonomy::first();

        // should not be fetched
        $product6 = Product::create(['name' => 'Fake product', 'sku'  => 'ts-05']);

        $fakeTaxonomy = Taxonomy::create(['name' => 'Fake product type']);

        $fakeTaxon = Taxon::create([
            'name' => 'women',
            'taxonomy_id' => $fakeTaxonomy->id
        ]);

        $fakeSecondTaxon = Taxon::create([
            'name' => 't-shirts',
            'taxonomy_id' => $fakeTaxonomy->id,
            'parent_id' => $fakeTaxon->id
        ]);

        $product6->addTaxon($fakeSecondTaxon);

        $products = (new ProductQueryBuilder())->build([], [
            't-shirts'
        ], $taxonomy->slug)->get();

        $this->assertCount(3, $products);
        $this->assertTrue($products->contains($this->product1));
        $this->assertTrue($products->contains($this->product3));
        $this->assertTrue($products->contains($this->product4));
    }

    /** @test */
    public function itCanQueryProductsByTaxonomyAndBothTaxonLevels()
    {
        $taxonomy = Taxonomy::first();

        // should not be fetched
        $product6 = Product::create(['name' => 'Fake product', 'sku'  => 'ts-05']);

        $fakeTaxonomy = Taxonomy::create(['name' => 'Fake product type']);

        $fakeTaxon = Taxon::create([
            'name' => 'women',
            'taxonomy_id' => $fakeTaxonomy->id
        ]);

        $fakeSecondTaxon = Taxon::create([
            'name' => 't-shirts',
            'taxonomy_id' => $fakeTaxonomy->id,
            'parent_id' => $fakeTaxon->id
        ]);

        $product6->addTaxon($fakeSecondTaxon);

        $products = (new ProductQueryBuilder())->build(
            ['men'],
            ['t-shirts'],
            $taxonomy->slug
        )->get();

        $this->assertCount(2, $products);
        $this->assertTrue($products->contains($this->product1));
        $this->assertTrue($products->contains($this->product3));
    }
}
