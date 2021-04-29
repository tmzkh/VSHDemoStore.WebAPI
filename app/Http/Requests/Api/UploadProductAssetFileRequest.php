<?php

namespace App\Http\Requests\Api;

use App\Enums\ProductAssetType;
use App\Rules\ProductAssetMimeTypeRule;
use App\Rules\ProductAssetModelIdRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadProductAssetFileRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'type' => [
                'required',
                'in:' . implode(',', ProductAssetType::values())
            ],
            'file' => [
                'required',
                'file',
                new ProductAssetMimeTypeRule($this->type ?? '')
            ],
            'model_id' => [
                Rule::requiredIf(function() {
                    return $this->type == ProductAssetType::MATERIAL;
                }),
                new ProductAssetModelIdRule($this->type ?? ''),
            ]
        ];
    }
}
