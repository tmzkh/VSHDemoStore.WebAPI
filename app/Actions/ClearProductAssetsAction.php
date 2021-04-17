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
            if (! $product->relationLoaded('assets')) {
                $product->load('assets');
            }

            $product->assets->each(function (ProductAsset $asset) {
                Storage::disk('azure-assets')->delete($asset->path);

                $asset->delete();
            });
        } catch (\Throwable $th) {
            return false;
        }

        return true;
    }
}
