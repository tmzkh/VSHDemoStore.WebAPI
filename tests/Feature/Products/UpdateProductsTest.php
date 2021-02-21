<?php

namespace Tests\Feature\Products;

use App\Enums\Gender;
use App\Models\AuthUser;
use App\Models\Product;
use App\Models\Taxon;
use App\Models\User;
use Database\Seeders\AppACLSeeder;
use Database\Seeders\TestCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Helpers\Traits\CreatesProduct;
use Tests\Helpers\Traits\SetsUpAdminUser;
use Tests\Helpers\Traits\SetsUpUser;
use Tests\TestCase;

class UpdateProductsTest extends TestCase
{
    use RefreshDatabase, SetsUpAdminUser, CreatesProduct, SetsUpUser;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AppACLSeeder::class);
        $this->seed(TestCategorySeeder::class);

        $this->createProdut();
    }

    /** @test */
    public function unauthenticatedCannotUpdateProduct()
    {
        $this->json('PUT', '/api/products/' . $this->product->id, [
            'name' => 'New product',
            'sku' => 'np-01',
            'taxon_id' => 1
        ])
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);

        $this->assertDatabaseMissing((new Product)->getTable(), [
            'name' => 'New product',
            'sku' => 'np-01',
        ]);
    }

    /** @test */
    public function nonAdminUserCannotUpdateProduct()
    {
        $this->setUpUser([], 'Customer');

        $this->json('PUT', '/api/products/' . $this->product->id, [
            'name' => 'New product',
            'sku' => 'np-01',
            'taxon_id' => 1
        ])
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthorized.'
            ]);

        $this->assertDatabaseMissing((new Product)->getTable(), [
            'name' => 'New product',
            'sku' => 'np-01',
        ]);
    }

    /** @test */
    public function itValidatesRequest()
    {
        $this->setUpAdminUser();

        $this->json('PUT', '/api/products/' . $this->product->id, [])
            ->assertJson([
                'errors' => [
                    'name' => [
                        'The name field is required.'
                    ],
                    'sku' => [
                        'The sku field is required.'
                    ],
                    'taxon_id' => [
                        'The taxon id field is required.'
                    ],
                ]
            ]);

        $this->json('PUT', '/api/products/' . $this->product->id, [
            'name' => 'ab'
        ])
            ->assertJson([
                'errors' => [
                    'name' => [
                        'The name must be at least 3 characters.'
                    ]
                ]
            ]);

        $this->json('PUT', '/api/products/' . $this->product->id, [
            'taxon_id' => '25' // non existing
        ])
            ->assertJson([
                'errors' => [
                    'taxon_id' => [
                        'The selected taxon id is invalid.'
                    ],
                ]
            ]);
    }

    /** @test */
    public function adminUserCanUpdateProduct()
    {
        $this->setUpAdminUser();

        $taxon = Taxon::whereSlug('t-shirts')
            ->whereHas('parent', function($query) {
                $query->whereSlug('women');
            }
        )->first();

        $this->json('PUT', '/api/products/' . $this->product->id, [
            'name' => 'Updated product',
            'sku' => 'up-01',
            'taxon_id' => $taxon->id
        ])
            ->assertStatus(200)
            ->assertJson([
                'name' => 'Updated product',
                'sku' => 'up-01',
                'slug' => 'updated-product',
                'taxons' => [
                    [
                        'id' => $taxon->id,
                        'parent' => [
                            'slug' => 'women',
                        ],
                    ],
                ]
            ]);

        $this->assertDatabaseHas((new Product)->getTable(), [
            'name' => 'Updated product',
            'sku' => 'up-01',
            'slug' => 'updated-product',
        ]);
    }

    /** @test */
    public function adminUserCanUpdateProductsTaxon()
    {
        $this->setUpAdminUser();

        $taxon = Taxon::whereSlug('shoes')
            ->whereHas('parent', function($query) {
                $query->whereSlug('men');
            }
        )->first();

        $this->json('PUT', '/api/products/' . $this->product->id, [
            'name' => 'Updated product',
            'sku' => 'up-01',
            'taxon_id' => $taxon->id
        ])
            ->assertStatus(200)
            ->assertJson([
                'name' => 'Updated product',
                'sku' => 'up-01',
                'slug' => 'updated-product',
                'taxons' => [
                    [
                        'id' => $taxon->id,
                        'parent' => [
                            'slug' => 'men',
                        ],
                    ],
                ]
            ]);

        $this->assertDatabaseHas((new Product)->getTable(), [
            'name' => 'Updated product',
            'sku' => 'up-01',
            'slug' => 'updated-product',
        ]);

        $this->assertEquals($taxon->id, $this->product->taxons()->first()->id);
    }
}
