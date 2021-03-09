<?php

namespace App\Http\Controllers\Api;

use App\Actions\StoreProductAssetAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\UploadProductAssetFileRequest;
use App\Http\Resources\ProductAssetResource;
use App\Models\Product;
use Auth;
use Illuminate\Http\Request;

class ProductAssetController extends Controller
{
    /**
     * List product's assets.
     *
     * @param Product $product
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Product $product)
    {
        if (! Auth::check()) {
            $assets = $product->images;
        } else {
            $assets = $product->imagesAndModels;
        }

        return ProductAssetResource::collection($assets);
    }

    /**
     * Upload new asset for product.
     *
     * @param UploadProductAssetFileRequest $request
     * @param Product $product
     * @return \App\Http\Resources\ProductAssetResource|\Illuminate\Http\Response
     */
    public function store(
        UploadProductAssetFileRequest $request,
        Product $product,
        StoreProductAssetAction $storeProductAssetAction
    ) {
        $productAsset = $storeProductAssetAction->execute($request, $product);

        if (! $productAsset) {
            return response()->json(['message' => 'Error uploading file'], 500);
        }

        return response()->json(ProductAssetResource::make($productAsset), 201);
    }
}
