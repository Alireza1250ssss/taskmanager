<?php


namespace App\Http\Traits;


use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasMembers
{
    /**
     * @return MorphToMany
     */
    public function members(): MorphToMany
    {
        return $this->morphToMany(
            User::class,
            'memberable',
            'members',
            'memberable_id',
            'user_ref_id'
        );
    }
}
