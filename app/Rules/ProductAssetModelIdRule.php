<?php

namespace App\Rules;

use App\Enums\ProductAssetType;
use App\Models\ProductAsset;
use Illuminate\Contracts\Validation\Rule;

class ProductAssetModelIdRule implements Rule
{
    /** @var string */
    protected $type;

    /**
     * Create a new rule instance.
     *
     * @param string $productAssetType
     * @return void
     */
    public function __construct(string $productAssetType)
    {
        $this->type = $productAssetType;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if ($this->type != ProductAssetType::MATERIAL) {
            return true;
        }

        $model = ProductAsset::find($value);

        return $model && $model->type->value() == ProductAssetType::MODEL;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The Model id is required and model must be type of model.';
    }
}
