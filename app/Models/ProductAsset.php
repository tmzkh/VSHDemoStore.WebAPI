<?php

namespace App\Models;

use App\Enums\ProductAssetType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Konekt\Enum\Eloquent\CastsEnums;

/**
 * App\Models\ProductAsset
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAsset byProductId(int $productId)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAsset byType(string $assetType)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAsset newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAsset newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAsset query()
 * @mixin \Eloquent
 * @property int $id
 * @property int $product_id
 * @property string $type
 * @property string $path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAsset images()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAsset models()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAsset whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAsset whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAsset wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAsset whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAsset whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAsset whereUpdatedAt($value)
 * @property int|null $model_id
 * @property string|null $url
 * @method static Builder|ProductAsset whereModelId($value)
 * @method static Builder|ProductAsset whereUrl($value)
 */
class ProductAsset extends Model
{
    use CastsEnums;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id',
        'model_id',
        'path',
        'url',
        'type',
    ];

    /**
     * The attributes that should be cast to \Konekt\Enum\Enum.
     *
     * @var array
     */
    protected $enums = [
        'type' => ProductAssetType::class,
    ];

    /**
     * Scope assets by product id.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param integer $productId
     * @return \Illuminate\Database\Eloquent\Builder|ProductAsset
     */
    public function scopeByProductId(Builder $query, int $productId)
    {
        return $query->where('product_id', '=', $productId);
    }

    /**
     * Scope assets by asset type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $assetType
     * @return \Illuminate\Database\Eloquent\Builder|ProductAsset
     */
    public function scopeByType(Builder $query, string $assetType)
    {
        return $query->where('type', '=', $assetType);
    }

    /**
     * Scope only images.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder|ProductAsset[]
     */
    public function scopeImages(Builder $query)
    {
        return $query->whereType(ProductAssetType::IMAGE);
    }

    /**
     * Scope only 3d-models.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder|ProductAsset[]
     */
    public function scopeModels(Builder $query)
    {
        return $query->whereType(ProductAssetType::MODEL);
    }

    /**
     * Scope images and 3d-models.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder|ProductAsset[]
     */
    public function scopeImagesAndModels(Builder $query)
    {
        return $query
            ->whereType(ProductAssetType::IMAGE)
            ->orWhere('type', '=', ProductAssetType::MODEL);
    }

    /**
     * 3D model has many materials.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function materials()
    {
        return $this->hasMany(ProductAsset::class, 'model_id', 'id');
    }
}
