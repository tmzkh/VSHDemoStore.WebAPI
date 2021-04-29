<?php

namespace Tests\Feature\Products;

use App\Enums\Gender;
use App\Models\AuthUser;
use App\Models\Product;
use App\Models\Taxon;
use App\Models\Taxonomy;
use App\Models\User;
use Database\Seeders\AppACLSeeder;
use Database\Seeders\TestCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Helpers\Traits\SetsUpAdminUser;
use Tests\Helpers\Traits\SetsUpUser;
use Tests\TestCase;

class CreateProductsTest extends TestCase
{
    use RefreshDatabase, SetsUpAdminUser, SetsUpUser;

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
    }

    /** @test */
    public function unauthenticatedCannotCreateProduct()
    {
        $this->json('POST', '/api/products', [
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
    public function nonAdminUserCannotCreateProduct()
    {
        $this->setUpUser([], 'Customer');

        $this->json('POST', '/api/products', [
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

        $this->json('POST', '/api/products', [])
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

        $this->json('POST', '/api/products', [
            'name' => 'ab'
        ])
            ->assertJson([
                'errors' => [
                    'name' => [
                        'The name must be at least 3 characters.'
                    ]
                ]
            ]);

        $this->json('POST', '/api/products', [
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
    public function adminUserCanCreateProduct()
    {
        $this->setUpAdminUser();

        $rootTaxon = Taxon::findBySlug('men');
        $taxon = Taxon::findBySlug('t-shirts');

        $this->json('POST', '/api/products', [
            'name' => 'New product',
            'sku' => 'np-01',
            'taxon_id' => $taxon->id
        ])
            ->assertStatus(201)
            ->assertJson([
                'name' => 'New product',
                'sku' => 'np-01',
                'slug' => 'new-product',
                'taxons' => [
                    [
                        'id' => $taxon->id,
                        'parent' => [
                            'id' => $rootTaxon->id,
                        ],
                    ],
                ]
            ]);

        $this->assertDatabaseHas((new Product)->getTable(), [
            'name' => 'New product',
            'sku' => 'np-01',
            'slug' => 'new-product',
        ]);
    }
}
