<?php


namespace App\Services;


use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AccessToChangeRoleService
{
    public static ?User $user = null;

    public static function isAbleFor($roleId, $modelInstance, $userId , $cacheUser = true): bool
    {
        if (self::$user && $cacheUser)
            $user = self::$user;
        else{
            $user = User::query()->find($userId);
            self::$user = $user;
        }
        $permissions = Role::query()->find($roleId)->permissions;
        $result = true;
        foreach ($permissions as $permission)
        {
            $key = $permission->key;
            $result = $user->canWithConditions($key,$modelInstance);
            if (!$result) break;
        }
        return $result;
    }
}
