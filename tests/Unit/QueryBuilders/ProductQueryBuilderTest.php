<?php

namespace Tests\Unit\QueryBuilders;

use App\Models\Product;
use App\Models\Taxon;
use App\Models\Taxonomy;
use App\QueryBuilders\ProductQueryBuilder;
use Database\Seeders\CategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductQueryBuilderTest extends TestCase
{
    use RefreshDatabase;

    /** @var \App\Models\Product */
    private $product1,
        $product2,
        $product3,
        $product4,
        $product5;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CategorySeeder::class);

        $this->setUpProducts();
    }

    public function setUpProducts()
    {
        $menTaxonRoot = Taxon::roots()->whereSlug('men')->first();
        $womenTaxonRoot = Taxon::roots()->whereSlug('women')->first();

        $this->product1 = Product::create(['name' => 'Black T-shirt', 'sku'  => 'ts-01']);
        $this->product1->addTaxon($menTaxonRoot->children()->whereSlug('t-shirts')->first());

        $this->product2 = Product::create(['name' => 'Blue Shoe', 'sku'  => 'ts-02']);
        $this->product2->addTaxon($menTaxonRoot->children()->whereSlug('shoes')->first());

        $this->product3 = Product::create(['name' => 'Red T-shirt', 'sku'  => 'ts-03']);
        $this->product3->addTaxon($menTaxonRoot->children()->whereSlug('t-shirts')->first());

        $this->product4 = Product::create(['name' => 'W Blue T-shirt','sku'  => 'ts-04']);
        $this->product4->addTaxon($womenTaxonRoot->children()->whereSlug('t-shirts')->first());

        $this->product5 = Product::create(['name' => 'W Red Shoe', 'sku'  => 'ts-05']);
        $this->product5->addTaxon($womenTaxonRoot->children()->whereSlug('shoes')->first());
    }

    /** @test */
    public function itCanQueryProductsByFirstTaxonLevel()
    {
        $products = (new ProductQueryBuilder())->build([
            'women'
        ])->get();

        $this->assertCount(2, $products);
        $this->assertTrue($products->contains($this->product4));
        $this->assertTrue($products->contains($this->product5));
    }

    /** @test */
    public function itCanQueryProductsBySecondTaxonLevel()
    {
        $products = (new ProductQueryBuilder())->build([
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
        $products = (new ProductQueryBuilder())->build([
            'men', 't-shirts'
        ])->get();

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

        $products = (new ProductQueryBuilder())->build([], $taxonomy->slug)->get();

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
        ], $taxonomy->slug)->get();

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

        $products = (new ProductQueryBuilder())->build([
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

        $products = (new ProductQueryBuilder())->build([
            'men', 't-shirts'
        ], $taxonomy->slug)->get();

        $this->assertCount(2, $products);
        $this->assertTrue($products->contains($this->product1));
        $this->assertTrue($products->contains($this->product3));
    }
}
