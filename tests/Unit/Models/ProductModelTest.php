<?php

namespace Tests\Unit\Models;

use App\Enums\ProductAssetType;
use App\Models\Product;
use App\Models\ProductAsset;
use App\Models\Taxon;
use Database\Seeders\TestCategorySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductModelTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(TestCategorySeeder::class);
    }

    /** @test */
    public function itCanCreateAndRetrieveProducts()
    {
        $product = Product::create([
            'name' => 'Dell Latitude E7240 Laptop',
            'sku'  => 'DLL-74237'
        ]);

        $this->assertDatabaseHas((new Product)->getTable(), [
            'name' => 'Dell Latitude E7240 Laptop',
            'sku'  => 'DLL-74237'
        ]);

        $productFromDb = Product::find($product->id);

        $this->assertEquals('Dell Latitude E7240 Laptop', $productFromDb->name);
        $this->assertEquals('DLL-74237', $productFromDb->sku);
    }

    /** @test */
    public function itCanAssignTaxonToProduct()
    {
        $product = Product::create([
            'name' => 'Black T-shirt',
            'sku'  => 'ts-01'
        ]);

        $taxon = Taxon::roots()
            ->whereSlug('men')
            ->first()
            ->children()
            ->whereSlug('t-shirts')
            ->first();

        $product->addTaxon($taxon);

        $this->assertEquals(
            'Clothes',
            $product->taxons()->first()->taxonomy->name
        );
    }

    /** @test */
    public function itCanQueryProductsFromTaxon()
    {
        $product = Product::create([
            'name' => 'Black T-shirt',
            'sku'  => 'ts-01'
        ]);

        $taxon = Taxon::roots()
            ->whereSlug('men')
            ->first()
            ->children()
            ->whereSlug('t-shirts')
            ->first();

        $product->addTaxon($taxon);

        $this->assertEquals(
            'Black T-shirt',
            $taxon->products()->first()->name
        );
    }

    /** @test */
    public function productCanHaveAssets()
    {
        $product = Product::create([
            'name' => 'Black T-shirt',
            'sku'  => 'ts-01'
        ]);

        $image = ProductAsset::create([
            'product_id' => $product->id,
            'path' => 'image.jpeg',
            'type' => ProductAssetType::IMAGE
        ]);

        $this->assertTrue($product->assets->contains($image));
    }
}
