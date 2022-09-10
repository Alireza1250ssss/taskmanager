<?php


namespace App\Http\Traits;


use App\Http\Controllers\ResolvePermissionController;
use App\Models\Role;
use App\Models\RoleUser;
use App\Services\ConditionService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;

trait HasPermissions
{
    public function canDo($keyPermission,$modelItem,$userId): bool
    {
        $rolesHavingPermission = Role::query()->whereHas('permissions', function (Builder $builder) use ($keyPermission) {
            $builder->where('key', $keyPermission);
        })->get();

        $rolePermissionRecordForUser = RoleUser::query()->where('user_ref_id', $userId)
            ->whereIn('role_ref_id', $rolesHavingPermission->pluck('role_id')->toArray())
            ->whereNotNull('rolable_type')->get();
        if ($rolePermissionRecordForUser->isEmpty())
            return false;

        foreach ($rolePermissionRecordForUser as $rolePermission) {

            $parentItem = ResolvePermissionController::$models[$rolePermission->rolable_type]['class']::find($rolePermission->rolable_id);
            if (empty($parentItem)) continue;

            if ($parentItem->isParentOf($modelItem)) {
                return true;
            }
        }
        return false;
    }


    public function authorizeFor($keyPermission , $modelItem)
    {
        $userId = $this->user_id;

        if (!$this->canDo($keyPermission,$modelItem,$userId))
            throw new AuthorizationException();
    }
}
