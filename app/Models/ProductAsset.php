<?php

namespace App\Models;

use App\Enums\ProductAssetType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
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
        'path',
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
     * @param \Illuminate\Database\Query\Builder $query
     * @param integer $productId
     * @return \Illuminate\Database\Query\Builder|ProductAsset
     */
    public function scopeByProductId(Builder $query, int $productId)
    {
        return $query->where('product_id', '=', $productId);
    }

    /**
     * Scope assets by asset type.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $assetType
     * @return \Illuminate\Database\Query\Builder|ProductAsset
     */
    public function scopeByType(Builder $query, string $assetType)
    {
        return $query->where('type', '=', $assetType);
    }
}
