<?php

namespace App\Models;

use App\Enums\Gender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Konekt\Enum\Eloquent\CastsEnums;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $sub
 * @property \Illuminate\Support\Collection $avatars
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, CastsEnums;

    protected $guard_name = 'api';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'sub',
        'gender',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        //
    ];

    /**
     * The attributes that should be cast to \Konekt\Enum\Enum.
     *
     * @var array
     */
    protected $enums = [
        'gender' => Gender::class,
    ];

    /**
     * User has many avatars.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function avatars()
    {
        return $this->hasMany(Avatar::class, 'user_id', 'id');
    }
}
