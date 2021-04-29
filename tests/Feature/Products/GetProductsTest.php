<?php

namespace Tests\Feature\Products;

use App\Enums\ProductAssetType;
use App\Models\Product;
use App\Models\ProductAsset;
use App\Models\Taxon;
use App\Models\Taxonomy;
use Database\Seeders\AppACLSeeder;
use Database\Seeders\TestCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Tests\Helpers\Traits\SetsUpProductListing;
use Tests\Helpers\Traits\SetsUpUser;
use Tests\TestCase;

class GetProductsTest extends TestCase
{
    use RefreshDatabase, SetsUpProductListing, SetsUpUser;

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

    /**
     * Set up assets of type image for each product
     *
     * @return void
     */
    private function setUpImageAssetsForAllProducts()
    {
        $this->productCollection->each(function (Product $product) {
            ProductAsset::create([
                'product_id' => $product->id,
                'path' => Str::random(5) . '.jpeg',
                'type' => ProductAssetType::IMAGE
            ]);
        });
    }

    /**
     * Set up assets of type model for prodcuts by ids.
     *
     * @param array $ids product ids
     * @param bool $withMaterial
     * @return void
     */
    private function setUpModelAssetsForProducts(array $ids = [], bool $withMaterial = false)
    {
        if (empty($ids)) {
            return;
        }

        $this->productCollection->each(function (Product $product) use ($ids, $withMaterial) {
            if (in_array($product->id, $ids)) {
                $model = ProductAsset::create([
                    'product_id' => $product->id,
                    'path' => Str::random(5) . '.obj',
                    'type' => ProductAssetType::MODEL
                ]);

                if ($withMaterial) {
                    ProductAsset::create([
                        'product_id' => $product->id,
                        'path' => Str::random(5) . '.mtl',
                        'type' => ProductAssetType::MATERIAL,
                        'model_id' => $model->id
                    ]);
                }
            }
        });
    }

    /** @test */
    public function itReturnsListOfProductsWithStructure()
    {
        $this->setUpImageAssetsForAllProducts();

        $this->json('GET', '/api/products/clothes')
            ->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    [
                        'id',
                        'name',
                        'slug',
                        'sku',
                        'is_fittable',
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

    /** @test */
    public function itReturnsOnlyImageAssetsForGuests()
    {
        $this->setUpImageAssetsForAllProducts();

        $this->setUpModelAssetsForProducts([
            $this->product1->id,
        ]);

        $query = '?taxons[]=t-shirts&rootTaxons[]=men&withImagesAndModels=true';

        $this->json('GET', '/api/products/clothes' . $query)
            ->assertJsonCount(1, 'data.0.assets')
            ->assertJson([
                'data' => [
                    [
                        'id' => $this->product1->id,
                        'assets' => [
                            [
                                'type' => ProductAssetType::IMAGE,
                                'product_id' => $this->product1->id,
                            ],
                        ],
                        'is_fittable' => true,
                    ],
                ]
            ])
            ->assertJsonMissing([
                [
                    'id' => $this->product1->id,
                    'assets' => [
                        [
                            'type' => ProductAssetType::MODEL,
                            'product_id' => $this->product1->id,
                        ],
                    ]
                ],
            ]);
    }

    /** @test */
    public function itReturnsImageAndModelAssetsForAuthenticatedUser()
    {
        $this->seed(AppACLSeeder::class);

        $this->setUpUser([], 'Customer');

        $this->setUpImageAssetsForAllProducts();

        $this->setUpModelAssetsForProducts([
            $this->product1->id,
        ]);

        $query = '?taxons[]=t-shirts&rootTaxons[]=men&withImagesAndModels=true';

        $this->json('GET', '/api/products/clothes' . $query)
            ->assertJsonCount(2, 'data.0.assets')
            ->assertJson([
                'data' => [
                    [
                        'id' => $this->product1->id,
                        'assets' => [
                            [
                                'type' => ProductAssetType::IMAGE,
                                'product_id' => $this->product1->id,
                            ],
                            [
                                'type' => ProductAssetType::MODEL,
                                'product_id' => $this->product1->id,
                            ],
                        ],
                        'is_fittable' => true,
                    ],
                ]
            ]);
    }

    /** @test */
    public function itReturnsMaterialAssetsWithModelAssetsForAuthenticatedUser()
    {
        $this->seed(AppACLSeeder::class);

        $this->setUpUser([], 'Customer');

        $this->setUpModelAssetsForProducts([
            $this->product1->id,
        ], true);

        $query = '?taxons[]=t-shirts&rootTaxons[]=men&withImagesAndModels=true';

        $this->json('GET', '/api/products/clothes' . $query)
            ->assertJsonCount(1, 'data.0.assets.0.materials')
            ->assertJson([
                'data' => [
                    [
                        'id' => $this->product1->id,
                        'assets' => [
                            [
                                'type' => ProductAssetType::MODEL,
                                'product_id' => $this->product1->id,
                                'materials' => [
                                    [
                                        'type' => ProductAssetType::MATERIAL,
                                        'product_id' => $this->product1->id,
                                    ]
                                ]
                            ],
                        ]
                    ],
                ]
            ]);
    }

    /** @test */
    public function itCanScopeFittable()
    {
        $this->setUpModelAssetsForProducts([
            $this->product1->id
        ]);

        $this->json('GET', '/api/products/clothes?onlyFittable=true')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    [
                        'id' => $this->product1->id,
                    ]
                ]
            ]);
    }

    /** @test */
    public function itCanReturnOneProductBySlug()
    {
        $this->setUpImageAssetsForAllProducts();

        $this->setUpModelAssetsForProducts([
            $this->product1->id
        ]);

        $this->json('GET', '/api/product/'.$this->product1->slug)
            ->assertStatus(200)
            ->assertJsonCount(1, 'assets')
            ->assertJsonCount(1, 'taxons')
            ->assertJson([
                'id' => $this->product1->id,
                'name' => $this->product1->name,
                'slug' => $this->product1->slug,
                'sku' => $this->product1->sku,
                'is_fittable' => true,
                'assets' => [
                    [
                        'type' => ProductAssetType::IMAGE,
                        'product_id' => $this->product1->id,
                    ]
                ],
                'taxons' => [
                    [
                        'name' => 'T-shirts',
                        'parent' => [
                            'name' => 'men',
                        ]
                    ]
                ],
            ]);
    }

    /** @test */
    public function authenticatedUserCanGetProductBySlugWithImageAndModelAssets()
    {
        $this->seed(AppACLSeeder::class);

        $this->setUpUser([], 'Customer');

        $this->setUpImageAssetsForAllProducts();

        $this->setUpModelAssetsForProducts([
            $this->product1->id
        ], true);

        $this->json('GET', '/api/product/'.$this->product1->slug.'?withImagesAndModels=true')
            ->assertStatus(200)
            ->assertJsonCount(2, 'assets')
            ->assertJson([
                'id' => $this->product1->id,
                'name' => $this->product1->name,
                'slug' => $this->product1->slug,
                'sku' => $this->product1->sku,
                'is_fittable' => true,
                'assets' => [
                    [
                        'type' => ProductAssetType::IMAGE,
                        'product_id' => $this->product1->id,
                    ],
                    [
                        'type' => ProductAssetType::MODEL,
                        'product_id' => $this->product1->id,
                        'materials' => [
                            [
                                'type' => ProductAssetType::MATERIAL,
                                'product_id' => $this->product1->id,
                            ]
                        ]
                    ]
                ],
            ]);
    }
}
