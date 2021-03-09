<?php

namespace Tests\Unit\Models;

use App\Enums\ProductAssetType;
use App\Models\Product;
use App\Models\ProductAsset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductAssetModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function itCastsTypeToEnum()
    {
        $productAsset = ProductAsset::create([
            'product_id' => Product::create(['name' => 'Name', 'sku' => 'p_01'])->id,
            'path' => 'image.jpeg',
            'type' => 'image'
        ]);

        $this->assertInstanceOf(ProductAssetType::class, $productAsset->type);
    }

    /** @test */
    public function modelAssetCanHasMaterials()
    {
        $product = Product::create(['name' => 'Name', 'sku' => 'p_01']);

        $model = ProductAsset::create([
            'product_id' => $product->id,
            'path' => 'image.jpeg',
            'type' => ProductAssetType::MODEL
        ]);

        $material = ProductAsset::create([
            'product_id' => $product->id,
            'model_id' => $model->id,
            'path' => 'image.jpeg',
            'type' => ProductAssetType::MODEL
        ]);

        $this->assertTrue($model->materials->contains($material));
    }
}
