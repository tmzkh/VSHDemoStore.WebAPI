<?php

namespace Tests\Feature\ProductAssets;

use App\Enums\ProductAssetType;
use App\Models\Product;
use App\Models\ProductAsset;
use Database\Seeders\AppACLSeeder;
use Database\Seeders\TestCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Helpers\Traits\CreatesProduct;
use Tests\Helpers\Traits\SetsUpUser;
use Tests\TestCase;

class GetProductAssetsTest extends TestCase
{
    use RefreshDatabase, CreatesProduct, SetsUpUser;

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
     * Mock azure-assets disk in storage.
     *
     * @return void
     */
    private function mockAssetsDisk()
    {
        Storage::fake('azure-assets');
    }

    /** @test */
    public function guestCanGetOnlyImageAssets()
    {
        $this->createProdut();

        ProductAsset::create([
            'product_id' => $this->product->id,
            'path' => 'obj.obj',
            'type' => ProductAssetType::MODEL
        ]);

        ProductAsset::create([
            'product_id' => $this->product->id,
            'path' => 'image.jpeg',
            'type' => ProductAssetType::IMAGE
        ]);

        $this->json('GET', '/api/products/'.$this->product->slug.'/assets')
            ->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJson([
                'data' => [
                    [
                        'product_id' => $this->product->id,
                        'model_id' => null,
                        'url' => null,
                        'type' => ProductAssetType::IMAGE,
                    ]
                ]
            ]);
    }

    /** @test */
    public function authenticatedUserCanGetAllAssets()
    {
        $this->setUpUser([], 'Customer');

        $this->createProdut();

        $model = ProductAsset::create([
            'product_id' => $this->product->id,
            'path' => 'obj.obj',
            'type' => ProductAssetType::MODEL
        ]);

        ProductAsset::create([
            'product_id' => $this->product->id,
            'path' => 'image.jpeg',
            'type' => ProductAssetType::IMAGE
        ]);

        ProductAsset::create([
            'product_id' => $this->product->id,
            'model_id' => $model->id,
            'path' => 'material.mtl',
            'type' => ProductAssetType::MATERIAL
        ]);

        $this->json('GET', '/api/products/'.$this->product->slug.'/assets')
            ->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJson([
                'data' => [
                    [
                        'product_id' => $this->product->id,
                        'type' => ProductAssetType::MODEL,
                        'materials' => [
                            [
                                'product_id' => $this->product->id,
                                'model_id' => $model->id,
                                'type' => ProductAssetType::MATERIAL,
                            ]
                        ]
                    ],
                    [
                        'product_id' => $this->product->id,
                        'type' => ProductAssetType::IMAGE,
                    ]
                ]
            ]);
    }
}
