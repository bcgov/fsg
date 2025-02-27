<?php

namespace App\Policies;

use App\Models\Claim;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClaimPolicy
{
    use HandlesAuthorization;


    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Claim $model): bool
    {
        $rolesToCheck = [Role::Ministry_USER, Role::Institution_ADMIN, Role::SUPER_ADMIN, Role::Student];
        $can = $user->roles()->pluck('name')->intersect($rolesToCheck)->isNotEmpty() && $user->disabled === false;

        return $can && ($user->bceid_business_guid === $model->bceid_business_guid);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $rolesToCheck = [Role::Student];

        return $user->roles()->pluck('name')->intersect($rolesToCheck)->isNotEmpty() && $user->disabled === false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Claim $model): bool
    {
        $rolesToCheck = [Role::Ministry_ADMIN, Role::Ministry_USER, Role::Institution_ADMIN, Role::Institution_USER,
            Role::SUPER_ADMIN, Role::Student];

        return $user->roles()->pluck('name')->intersect($rolesToCheck)->isNotEmpty() && $user->disabled === false;
    }

}
