<?php

namespace App\Actions;

use App\Models\Product;
use App\Models\ProductAsset;
use Illuminate\Support\Facades\Storage;

class ClearProductAssetsAction
{
    /**
     * Execute action to clear product's assets.
     *
     * @param \App\Models\Product $user
     * @return boolean
     */
    public function execute(Product $product) : bool
    {
        try {
            if (! $product->relationLoaded('images')) {
                $product->load('images');
            }

            $product->images->each(function (ProductAsset $image) {
                Storage::disk('azure-assets')->delete($image->path);

                $image->delete();
            });

            if (! $product->relationLoaded('models')) {
                $product->load(['models', 'models.materials']);
            }

            $product->models->each(function (ProductAsset $asset) {
                $asset->materials->each(function (ProductAsset $material) {
                    Storage::disk('azure-assets')->delete($material->path);

                    $material->delete();
                });

                Storage::disk('azure-assets')->delete($asset->path);

                $asset->delete();
            });

        } catch (\Throwable $th) {
            return false;
        }

        return true;
    }
}
