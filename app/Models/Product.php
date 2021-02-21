<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Vanilo\Category\Traits\HasTaxons;
use Vanilo\Product\Models\Product as BaseProduct;

/**
 * App\Models\Product
 *
 * @property-read bool $is_active
 * @property-read string $title
 * @method static \Illuminate\Database\Eloquent\Builder|Product actives()
 * @method static \Illuminate\Database\Eloquent\Builder|Product findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereSlug(string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|Product withUniqueSlugConstraints(\Illuminate\Database\Eloquent\Model $model, string $attribute, array $config, string $slug)
 * @mixin \Eloquent
 * @property int $id
 * @property string $name
 * @property string|null $slug
 * @property string $sku
 * @property string|null $price
 * @property string|null $excerpt
 * @property string|null $description
 * @property string $state
 * @property string|null $ext_title
 * @property string|null $meta_keywords
 * @property string|null $meta_description
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $stock
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereExcerpt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereExtTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereMetaKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUpdatedAt($value)
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Taxon[] $taxons
 * @property-read int|null $taxons_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProductAsset[] $assets
 * @property-read int|null $assets_count
 */
class Product extends BaseProduct
{
    use HasTaxons;

    /**
     * Product has many assets.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assets(): HasMany
    {
        return $this->hasMany(ProductAsset::class, 'product_id');
    }
}
