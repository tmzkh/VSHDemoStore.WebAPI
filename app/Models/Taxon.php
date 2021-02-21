<?php

namespace App\Models;

use App\Traits\HasProducts;
use Vanilo\Category\Models\Taxon as BaseTaxon;

/**
 * App\Models\Taxon
 *
 * @property int $id
 * @property int $taxonomy_id
 * @property int|null $parent_id
 * @property int|null $priority
 * @property string $name
 * @property string|null $slug
 * @property string|null $ext_title
 * @property string|null $meta_keywords
 * @property string|null $meta_description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Taxon[] $children
 * @property-read int|null $children_count
 * @property-read int $level
 * @property-read \Illuminate\Support\Collection $parents
 * @property-read \Illuminate\Database\Eloquent\Collection|Taxon[] $neighbours
 * @property-read int|null $neighbours_count
 * @property-read Taxon|null $parent
 * @property-read \App\Models\Taxonomy $taxonomy
 * @method static \Illuminate\Database\Eloquent\Builder|Taxon byTaxonomy($taxonomy)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxon except(\Vanilo\Category\Contracts\Taxon $taxon)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxon findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Taxon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Taxon query()
 * @method static \Illuminate\Database\Eloquent\Builder|Taxon roots()
 * @method static \Illuminate\Database\Eloquent\Builder|Taxon sort()
 * @method static \Illuminate\Database\Eloquent\Builder|Taxon sortReverse()
 * @method static \Illuminate\Database\Eloquent\Builder|Taxon whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxon whereExtTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxon whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxon whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxon whereMetaKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxon whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxon whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxon wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxon whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxon whereTaxonomyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxon whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxon withUniqueSlugConstraints(\Illuminate\Database\Eloquent\Model $model, string $attribute, array $config, string $slug)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Product[] $products
 * @property-read int|null $products_count
 */
class Taxon extends BaseTaxon
{
    use HasProducts;
}
