<?php

namespace App\Http\Resources;

use App\Enums\ProductAssetType;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $attributes = parent::toArray($request);

        $attributes['is_fittable'] = ! empty($attributes['models_count'])
            && $attributes['models_count'] > 0;

        unset($attributes['models_count']);

        return $attributes;
    }
}
