<?php

namespace App\Actions;

use App\Enums\ProductAssetType;
use App\Http\Requests\Api\UploadProductAssetFileRequest;
use App\Models\Product;
use App\Models\ProductAsset;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreProductAssetAction
{
    public function execute(
        UploadProductAssetFileRequest $request,
        Product $product,
        ?string $fileName = null,
        ?string $filePath = null
    ) : ?ProductAsset
    {
        $path = Storage::disk('azure-assets')->putFileAs(
            $filePath ?? '',
            $request->file('file'),
            $fileName ?? 'product_' . $product->id . '_asset_' . Str::random(15) . '.' . $request->file('file')->getClientOriginalExtension()
        );

        if (! $path) {
            return null;
        }

        $attributes = [
            'product_id' => $product->id,
            'type' => $request->type,
            'path' => $path,
            'url' => Storage::disk('azure-assets')->url($path),
        ];

        if ($attributes['type'] == ProductAssetType::MATERIAL) {
            $attributes['model_id'] = $request->model_id;
        }

        return ProductAsset::create($attributes);
    }
}
