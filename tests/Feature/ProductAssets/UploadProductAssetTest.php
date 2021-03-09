<?php

namespace Tests\Feature\ProductAssets;

use App\Enums\ProductAssetType;
use App\Models\ProductAsset;
use Database\Seeders\AppACLSeeder;
use Database\Seeders\TestCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Helpers\Traits\CreatesProduct;
use Tests\Helpers\Traits\SetsUpUser;
use Tests\TestCase;

class UploadProductAssetTest extends TestCase
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
    public function unauthenticatedUserCannotAccessAvatars()
    {
        $this->json('POST', 'api/products/'.$this->product->slug.'/assets')
            ->assertStatus(401);
    }

    /** @test */
    public function customerUserCannotAccessAvatars()
    {
        $this->setUpUser([], 'Customer');

        $this->json('POST', 'api/products/'.$this->product->slug.'/assets')
            ->assertStatus(401);
    }

    /** @test */
    public function itValidatesMissingTypeAttribute()
    {
        $this->setUpUser();

        $this->json('POST', 'api/products/'.$this->product->slug.'/assets', [])
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'type' => [
                        'The type field is required.'
                    ]
                ]
            ]);
    }

    /** @test */
    public function itValidatesMissingAssetFile()
    {
        $this->setUpUser();

        $this->json('POST', 'api/products/'.$this->product->slug.'/assets', [])
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'file' => [
                        'The file field is required.'
                    ]
                ]
            ]);
    }

    /** @test */
    public function itValidatesType()
    {
        $this->setUpUser();

        $this->json('POST', 'api/products/'.$this->product->slug.'/assets', [
            'type' => 'not-existing-type'
        ])
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'type' => [
                        'The selected type is invalid.'
                    ]
                ]
            ]);
    }

    /** @test */
    public function itValidatesImageFileMimeType()
    {
        $this->setUpUser();

        $this->json('POST', 'api/products/'.$this->product->slug.'/assets', [
            'type' => ProductAssetType::IMAGE,
            'file' => UploadedFile::fake()->create('avatar.obj', 1000, 'text/plain')
        ])
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'file' => [
                        'The file type is invalid.'
                    ]
                ]
            ]);
    }

    /** @test */
    public function itValidates3DModelFileMimeType()
    {
        $this->setUpUser();

        $this->json('POST', 'api/products/'.$this->product->slug.'/assets', [
            'type' => ProductAssetType::MODEL,
            'file' => UploadedFile::fake()->image('thumbnail.jpeg')
        ])
        ->assertStatus(422)
        ->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'file' => [
                    'The file type is invalid.'
                ]
            ]
        ]);
    }

    /** @test */
    public function itValidatesMaterialFileMimeType()
    {
        $this->setUpUser();

        $this->json('POST', 'api/products/'.$this->product->slug.'/assets', [
            'type' => ProductAssetType::MATERIAL,
            'file' => UploadedFile::fake()->image('thumbnail.jpeg')
        ])
        ->assertStatus(422)
        ->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'file' => [
                    'The file type is invalid.'
                ]
            ]
        ]);
    }

    /** @test */
    public function itValidatesModelIdWhenUploadingMaterial()
    {
        $this->setUpUser();

        $this->json('POST', 'api/products/'.$this->product->slug.'/assets', [
            'type' => ProductAssetType::MATERIAL,
            'file' => UploadedFile::fake()->create('material.mtl', 1000, 'text/plain')
        ])
        ->assertStatus(422)
        ->assertJson([
            'message' => 'The given data was invalid.',
            'errors' => [
                'model_id' => [
                    'The model id field is required.'
                ]
            ]
        ]);
    }

    /** @test */
    public function itValidatesThatMaterialWillBeAttachedToModel()
    {
        $this->setUpUser();

        $model = ProductAsset::create([
            'product_id' => $this->product->id,
            'type' => ProductAssetType::IMAGE,
            'path' => '',
            'url' => ''
        ]);

        $this->json('POST', 'api/products/'.$this->product->slug.'/assets', [
            'type' => ProductAssetType::MATERIAL,
            'file' => UploadedFile::fake()->create('material.mtl', 1000, 'text/plain'),
            'model_id' => $model->id
        ])
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'model_id' => [
                        'The Model id is required and model must be type of model.'
                    ]
                ]
            ]);
    }

    /** @test */
    public function adminUserCanUploadImageAsProductAsset()
    {
        $this->setUpUser();

        $this->mockAssetsDisk();

        $this->json('POST', 'api/products/'.$this->product->slug.'/assets', [
            'type' => ProductAssetType::IMAGE,
            'file' => UploadedFile::fake()->image('thumbnail.jpeg')
        ])
            ->assertStatus(201)
            ->assertJson([
                'product_id' => $this->product->id,
                'type' => ProductAssetType::IMAGE,
            ]);

        $this->assertDatabaseHas((new ProductAsset)->getTable(), [
            'product_id' => $this->product->id,
            'type' => ProductAssetType::IMAGE,
        ]);

        $uploadedAsset = ProductAsset::first();

        Storage::disk('azure-assets')->assertExists($uploadedAsset->path);
    }

    /** @test */
    public function adminUserCanUpload3DModelAsProductAsset()
    {
        $this->setUpUser();

        $this->mockAssetsDisk();

        $this->json('POST', 'api/products/'.$this->product->slug.'/assets', [
            'type' => ProductAssetType::MODEL,
            'file' => UploadedFile::fake()->create('thumbnail.obj', 1000, 'text/plain')
        ])
            ->assertStatus(201)
            ->assertJson([
                'product_id' => $this->product->id,
                'type' => ProductAssetType::MODEL,
            ]);

        $this->assertDatabaseHas((new ProductAsset)->getTable(), [
            'product_id' => $this->product->id,
            'type' => ProductAssetType::MODEL,
        ]);

        $uploadedAsset = ProductAsset::first();

        Storage::disk('azure-assets')->assertExists($uploadedAsset->path);
    }

    /** @test */
    public function adminUserCanUploadMaterialAsProductAsset()
    {
        $this->setUpUser();

        $this->mockAssetsDisk();

        $model = ProductAsset::create([
            'product_id' => $this->product->id,
            'type' => ProductAssetType::MODEL,
            'path' => '',
            'url' => ''
        ]);

        $this->json('POST', 'api/products/'.$this->product->slug.'/assets', [
            'type' => ProductAssetType::MATERIAL,
            'file' => UploadedFile::fake()->create('material.mtl', 1000, 'text/plain'),
            'model_id' => $model->id
        ])
            ->assertStatus(201)
            ->assertJson([
                'product_id' => $this->product->id,
                'model_id' => $model->id,
                'type' => ProductAssetType::MATERIAL,
            ]);

        $this->assertDatabaseHas((new ProductAsset)->getTable(), [
            'product_id' => $this->product->id,
            'model_id' => $model->id,
            'type' => ProductAssetType::MATERIAL,
        ]);

        $uploadedAsset = ProductAsset::whereModelId($model->id)->first();

        Storage::disk('azure-assets')->assertExists($uploadedAsset->path);
    }
}
