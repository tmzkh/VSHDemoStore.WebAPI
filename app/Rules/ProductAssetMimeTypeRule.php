<?php

namespace App\Rules;

use App\Enums\ProductAssetType;
use Illuminate\Contracts\Validation\Rule;

class ProductAssetMimeTypeRule implements Rule
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
        $mimeType = $value->getMimeType();

        $extension = $value->getClientOriginalExtension();

        switch ($this->type) {
            case ProductAssetType::IMAGE:
                return $this->handleImageTypeValidation($mimeType);

            case ProductAssetType::MODEL:
                return $this->handle3DModelFileMimeTypeValidation($mimeType, $extension);

            case ProductAssetType::MATERIAL:
                return $this->handleMaterialFileMimeTypeValidation($mimeType, $extension);

            default:
                return false;
        }
    }

    /**
     * Handle image file mimetype validation.
     *
     * @param string $mimeType
     * @param void $fail
     * @return void
     */
    private function handleImageTypeValidation($mimeType)
    {
        return in_array(
            $mimeType,
            ['image/jpg', 'image/jpeg','image/bmp','image/png']
        );
    }

    /**
     * Handle 3d-model file mime type validation
     *
     * @param string $mimeType
     * @param string $extension
     * @return bool
     */
    private function handle3DModelFileMimeTypeValidation($mimeType, $extension)
    {
        return ($mimeType == 'text/plain') && ($extension == 'obj');
    }

    /**
     * Handle material file mime type validation
     *
     * @param string $mimeType
     * @param string $extension
     * @return bool
     */
    private function handleMaterialFileMimeTypeValidation($mimeType, $extension)
    {
        return ($mimeType == 'text/plain') && ($extension == 'mtl');
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The file type is invalid.';
    }
}
