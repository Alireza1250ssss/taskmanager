<?php

namespace App\Models;

use App\Http\Traits\FilterRecords;
use App\Models\Permissions\Permission;
use App\Models\Permissions\Role;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable,FilterRecords,SoftDeletes;

    protected $primaryKey = 'user_id';
    public array $filters = [
        'first_name' , 'last_name' , 'phone' , 'email' , 'username' , 'status'
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name', 'last_name' , 'phone', 'username', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'verify_code',
    ];



    /**
     * @return HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class,'user_ref_id');
    }

    /**
     * @return HasMany
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class , 'user_ref_id');
    }

    /**
     * @return HasMany
     */
    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class , 'user_ref_id');
    }

    /*
     * --------- polymorphic permissions defined here with MorphedByMany return type -------------------
     */

    /**
     * @return MorphToMany
     */
    public function fields(): MorphToMany
    {
        return $this->morphedByMany(
            Field::class ,
            'permissible' ,
            'permissibles' ,
            'user_ref_id'
        );
    }

    /**
     * @return MorphToMany
     */
    public function entities(): MorphToMany
    {
        return $this->morphedByMany(Entity::class , 'permissible');
    }



    /**
     * @return Attribute
     */
    protected function password() : Attribute
    {
        return Attribute::set( fn($value) => Hash::make($value));
    }

    /**
     * @inheritDoc
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * @inheritDoc
     */
    public function getJWTCustomClaims()
    {
        return [
          'phone' => $this->phone
        ];
    }
}
