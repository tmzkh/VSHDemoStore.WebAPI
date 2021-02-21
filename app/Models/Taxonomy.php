<?php

namespace App\Models;

use Vanilo\Category\Models\Taxonomy as BaseTaxonomy;

/**
 * App\Models\Taxonomy
 *
 * @property int $id
 * @property string $name
 * @property string|null $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Taxon[] $taxa
 * @property-read int|null $taxa_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Taxon[] $taxons
 * @property-read int|null $taxons_count
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy query()
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy withUniqueSlugConstraints(\Illuminate\Database\Eloquent\Model $model, string $attribute, array $config, string $slug)
 * @mixin \Eloquent
 */
class Taxonomy extends BaseTaxonomy
{

}
