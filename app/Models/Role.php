<?php

namespace App\Models;

use App\Http\Controllers\RoleController;
use App\Http\Traits\FilterRecords;
use App\Observers\BaseObserver;
use App\Observers\OwnerObserver;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory,FilterRecords;

    protected $primaryKey = 'role_id';
    protected $fillable = ['name','company_ref_id','category'];
    public array $filters = ['name'];

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
          User::class ,
          'role_user' ,
          'role_ref_id' ,
          'user_ref_id' ,
        );
    }

    /**
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'permission_role',
        'role_ref_id' ,
            'permission_ref_id',
        )->withPivot(['condition_params','access']);
    }

    /**
     * @return Attribute
     */
    protected function category(): Attribute
    {
        return Attribute::set(fn($value) => RoleController::LEVELS[$value]);
    }

    public function getCategoryAttribute($value): string
    {
        return array_search($value,RoleController::LEVELS);
    }

    public static function hasBaseRoleOn($model , $userId): bool
    {
        $type = (new OwnerObserver())->models[get_class($model)];
        $modelId = $model->{$model->getPrimaryKey()};
        $baseRole = static::query()->where('name','base-role')->first();
        if (!$baseRole) return false;

        return (bool)RoleUser::query()->where([
            'user_ref_id' => $userId,
            'role_ref_id' => $baseRole->role_id ,
            'rolable_type' => $type ,
            'rolable_id' => $modelId
        ])->first();
    }

    public static function hasAnyRoleOn($model , $userId): bool
    {
        $type = (new OwnerObserver())->models[get_class($model)];
        $modelId = $model->{$model->getPrimaryKey()};

        return (bool)RoleUser::query()->where([
            'rolable_type' => $type ,
            'rolable_id' => $modelId,
            'user_ref_id' => $userId
        ])->first();
    }

    public static function takeRolesOn($model,$userId)
    {
        $type = (new BaseObserver())->models[get_class($model)];
        $modelId = $model->{$model->getPrimaryKey()};
        $roleUserRecords = RoleUser::query()->where([
            'rolable_type' => $type,
            'rolable_id' => $modelId,
            'user_ref_id' => $userId
        ])->get();

        foreach ($roleUserRecords as $roleUserRecord)
        {
            RoleController::checkAccessOnRole($roleUserRecord->role_ref_id,$model);
            $roleUserRecord->delete();
        }
    }

}
