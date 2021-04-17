<?php

namespace Tests\Feature\Products;

use App\Enums\Gender;
use App\Enums\ProductAssetType;
use App\Models\AuthUser;
use App\Models\Product;
use App\Models\ProductAsset;
use App\Models\Taxon;
use App\Models\User;
use Database\Seeders\AppACLSeeder;
use Database\Seeders\TestCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    /**
     * Mock up product assets.
     *
     * @return void
     */
    private function mockProductAssets()
    {
        Storage::fake('azure-assets');

        $path = Storage::disk('azure-assets')->putFileAs(
            '',
            UploadedFile::fake()->create('3d-model.obj', 10000, 'text/plain'),
            'product_'.$this->product->id.'_asset_101010.obj'
        );

        $model = ProductAsset::create([
            'product_id' => $this->product->id,
            'type' => ProductAssetType::MODEL,
            'path' => $path
        ]);

        $path = Storage::disk('azure-assets')->putFileAs(
            '',
            UploadedFile::fake()->create('material.obj', 10000, 'text/plain'),
            'product_'.$this->product->id.'_asset_101012.obj'
        );

        ProductAsset::create([
            'product_id' => $this->product->id,
            'model_id' => $model->id,
            'type' => ProductAssetType::MATERIAL,
            'path' => $path
        ]);

        $path = Storage::disk('azure-assets')->putFileAs(
            '',
            UploadedFile::fake()->create('img.jpeg', 10000, 'image/jpeg'),
            'product_'.$this->product->id.'_asset_101011.jpeg'
        );

        ProductAsset::create([
            'product_id' => $this->product->id,
            'type' => ProductAssetType::IMAGE,
            'path' => $path
        ]);
    }

    /** @test */
    public function unauthenticatedUserCannotDestroyProduct()
    {
        $this->json('DELETE', '/api/products/' . $this->product->slug)
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

        $this->json('DELETE', '/api/products/' . $this->product->slug)
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

        $this->json('DELETE', '/api/products/' . $this->product->slug)
            ->assertStatus(204);
    }

    /** @test */
    public function productAssetsAreDestroyedWithProduct()
    {
        $this->mockProductAssets();

        $this->setUpAdminUser();

        $file1Name = 'product_'.$this->product->id.'_asset_101010.obj';

        $file2Name = 'product_'.$this->product->id.'_asset_101011.jpeg';

        $file3Name = 'product_'.$this->product->id.'_asset_101012.obj';

        $this->json('DELETE', '/api/products/' . $this->product->slug)
            ->assertStatus(204);

        $this->assertDatabaseCount((new ProductAsset())->getTable(), 0);

        Storage::disk('azure-assets')
            ->assertMissing($file1Name)
            ->assertMissing($file2Name)
            ->assertMissing($file3Name);
    }
}
