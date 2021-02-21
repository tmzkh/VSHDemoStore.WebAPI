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
use Tests\Helpers\Traits\SetsUpProductListing;
use Tests\Helpers\Traits\SetsUpUser;
use Tests\TestCase;

class DestroyProductsTest extends TestCase
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
    public function unauthenticatedUserCannotDestroyProduct()
    {
        $this->json('DELETE', '/api/products/' . $this->product->id)
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);

        $this->assertDatabaseHas($this->product->getTable(), [
            'id' => $this->product->id,
            'name' => 'New T-shirt',
            'sku' => 'ts_01',
        ]);
    }

    /** @test */
    public function nonAdminUserCannotDestroyProduct()
    {
        $this->setUpUser([], 'Customer');

        $this->json('DELETE', '/api/products/' . $this->product->id)
            ->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthorized.'
            ]);

        $this->assertDatabaseHas($this->product->getTable(), [
            'id' => $this->product->id,
            'name' => 'New T-shirt',
            'sku' => 'ts_01',
        ]);
    }

    /** @test */
    public function adminUserCanDestroyProduct()
    {
        $this->setUpAdminUser();

        $this->json('DELETE', '/api/products/' . $this->product->id)
            ->assertStatus(204);
    }
}
