<?php

namespace Tests\Feature\Products;

use App\Enums\ProductAssetType;
use App\Models\Product;
use App\Models\ProductAsset;
use App\Models\Taxon;
use App\Models\Taxonomy;
use Database\Seeders\TestCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\Helpers\Traits\SetsUpProductListing;
use Tests\TestCase;

class GetProductsTest extends TestCase
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

    private function setUpAssetsForAllProducts()
    {
        $products = collect([
            $this->product1,
            $this->product2,
            $this->product3,
            $this->product4,
            $this->product5
        ]);

        $products->each(function (Product $product) {
            ProductAsset::create([
                'product_id' => $product->id,
                'path' => Str::random(5) . '.jpeg',
                'type' => ProductAssetType::IMAGE
            ]);
        });
    }

    /** @test */
    public function itReturnsListOfProductsWithStructure()
    {
        $this->setUpAssetsForAllProducts();

        $this->json('GET', '/api/products/clothes')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                        'slug',
                        'sku',
                        'taxons' => [
                            [
                                'id',
                                'parent_id',
                                'name',
                                'slug',
                                'parent' => [
                                    'id',
                                    'parent_id',
                                    'name',
                                    'slug',
                                ]
                            ]
                        ],
                        'assets' => [
                            [
                                'product_id',
                                'type',
                                'path'
                            ]
                        ]
                    ]
                ],
                'links' => [
                    'first',
                    'last',
                    'prev',
                    'next'
                ],
            ]);
    }

    /** @test */
    public function itCanFilterListingByTaxonomy()
    {
        // should not be fetched
        $product6 = Product::create(['name' => 'Fake product', 'sku'  => 'ts-05']);

        $fakeTaxonomy = Taxonomy::create(['name' => 'Fake product type']);

        $fakeTaxon = Taxon::create([
            'name' => 'men',
            'taxonomy_id' => $fakeTaxonomy->id
        ]);

        $fakeSecondTaxon = Taxon::create([
            'name' => 'T-shirts',
            'taxonomy_id' => $fakeTaxonomy->id,
            'parent_id' => $fakeTaxon->id
        ]);

        $product6->addTaxon($fakeSecondTaxon);

        $this->json('GET', '/api/products/clothes')
            ->assertJsonCount(5, 'data')
            ->assertJsonMissing([
                'data' => [
                    ['id' => $product6->id]
                ],
            ]);
    }

    /** @test */
    public function itCanFilterListingByFirstLevelTaxons()
    {
        $query = '?rootTaxons[]=men';

        $this->json('GET', '/api/products/clothes' . $query)
            ->assertJsonCount(3, 'data')
            ->assertJson([
                'data' => [
                    ['id' => $this->product1->id],
                    ['id' => $this->product2->id],
                    ['id' => $this->product3->id]
                ]
            ])
            ->assertJsonMissing([
                'data' => [
                    ['id' => $this->product4->id],
                    ['id' => $this->product5->id]
                ]
            ]);
    }

    /** @test */
    public function itCanFilterListingBySecondLevelTaxons()
    {
        $query = '?taxons[]=t-shirts';

        $this->json('GET', '/api/products/clothes' . $query)
            ->assertJsonCount(3, 'data')
            ->assertJson([
                'data' => [
                    ['id' => $this->product1->id],
                    ['id' => $this->product3->id],
                    ['id' => $this->product4->id]
                ]
            ])
            ->assertJsonMissing([
                'data' => [
                    ['id' => $this->product2->id],
                    ['id' => $this->product5->id]
                ]
            ]);
    }

    /** @test */
    public function itCanFilterListingByBothTaxonLevels()
    {
        $query = '?taxons[]=t-shirts&rootTaxons[]=men';

        $this->json('GET', '/api/products/clothes' . $query)
            ->assertJsonCount(2, 'data')
            ->assertJson([
                'data' => [
                    ['id' => $this->product1->id],
                    ['id' => $this->product3->id],
                ]
            ])
            ->assertJsonMissing([
                'data' => [
                    ['id' => $this->product2->id],
                    ['id' => $this->product4->id],
                    ['id' => $this->product5->id]
                ]
            ]);
    }
}
