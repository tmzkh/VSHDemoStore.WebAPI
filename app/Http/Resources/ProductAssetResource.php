<?php

namespace App\Http\Resources;

use App\Enums\ProductAssetType;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductAssetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $attributes = [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'model_id' => $this->model_id,
            'url' => $this->url,
            'type' => $this->type->value(),
        ];

        if ($attributes['type'] == ProductAssetType::MODEL) {
            $attributes['materials'] = ProductAssetResource::collection($this->materials);
        }

        return $attributes;
    }
}
