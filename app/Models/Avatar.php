<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Avatar
 *
 * @property string $path Path to file.
 * @property int $id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Avatar newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Avatar newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Avatar query()
 * @method static \Illuminate\Database\Eloquent\Builder|Avatar whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Avatar whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Avatar wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Avatar whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Avatar whereUserId($value)
 * @mixin \Eloquent
 */
class Avatar extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'path',
    ];
}
