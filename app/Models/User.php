<?php

namespace App\Models;

use App\Http\Controllers\ResolvePermissionController;
use App\Http\Traits\FilterRecords;
use App\Http\Traits\HasPermissions;
use App\Http\Traits\MainPropertyGetter;
use App\Http\Traits\MainPropertySetter;
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
    use HasApiTokens, HasFactory, Notifiable, FilterRecords, SoftDeletes, MainPropertySetter, MainPropertyGetter;
    use HasPermissions;

    protected $primaryKey = 'user_id';
    public array $filters = [
        'first_name', 'last_name', 'phone', 'email', 'username', 'status'
    ];
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name', 'last_name', 'phone', 'username', 'email', 'password',
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
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'role_user',
            'user_ref_id',
            'role_ref_id'
        )->withPivot(['rolable_type','rolable_id']);
    }

    // participating entities defined here

    /**
     * @return MorphToMany
     */
    public function tasksJoined(): MorphToMany
    {
        return $this->morphedByMany(
          Task::class ,
          'memberable' ,
          'members' ,
          'user_ref_id' ,
          'memberable_id'
        );
    }

    /**
     * @return MorphToMany
     */
    public function teamsJoined(): MorphToMany
    {
        return $this->morphedByMany(
            Team::class ,
            'memberable' ,
            'members' ,
            'user_ref_id' ,
            'memberable_id'
        );
    }

    /**
     * @return MorphToMany
     */
    public function projectsJoined(): MorphToMany
    {
        return $this->morphedByMany(
            Project::class ,
            'memberable' ,
            'members' ,
            'user_ref_id' ,
            'memberable_id'
        );
    }

    /**
     * @return MorphToMany
     */
    public function companiesJoined(): MorphToMany
    {
        return $this->morphedByMany(
            Company::class ,
            'memberable' ,
            'members' ,
            'user_ref_id' ,
            'memberable_id'
        );
    }

    // watching entities are listed here below

    /**
     * @return MorphToMany
     */
    public function watchingCompanies(): MorphToMany
    {
        return $this->morphedByMany(
            Company::class ,
            'watchable' ,
            'watchers' ,
            'user_ref_id' ,
            'watchable_id'
        );
    }

    /**
     * @return MorphToMany
     */
    public function watchingProjects(): MorphToMany
    {
        return $this->morphedByMany(
            Project::class ,
            'watchable',
            'watchers' ,
            'user_ref_id' ,
            'watchable_id'
        );
    }

    /**
     * @return MorphToMany
     */
    public function watchingTeams(): MorphToMany
    {
        return $this->morphedByMany(
            Team::class ,
            'watchable',
            'watchers' ,
            'user_ref_id' ,
            'watchable_id'
        );
    }

    /**
     * @return MorphToMany
     */
    public function watchingTasks(): MorphToMany
    {
        return $this->morphedByMany(
            Task::class ,
            'watchable',
            'watchers' ,
            'user_ref_id' ,
            'watchable_id'
        );
    }

    /**
     * @return HasMany
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, 'user_ref_id');
    }

    /**
     * @return HasMany
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'user_ref_id');
    }

    /**
     * @return HasMany
     */
    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class, 'user_ref_id');
    }

    public function preferences(): HasMany
    {
        return $this->hasMany(Preference::class,'user_ref_id');
    }


    /**
     * @return Attribute
     */
    protected function password(): Attribute
    {
        return Attribute::set(fn($value) => Hash::make($value));
    }


    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
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
