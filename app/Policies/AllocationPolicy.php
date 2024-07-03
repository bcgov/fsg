<?php

namespace App\Policies;

use App\Models\Allocation;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AllocationPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        $rolesToCheck = [Role::Ministry_ADMIN, Role::SUPER_ADMIN];

        return $user->roles()->pluck('name')->intersect($rolesToCheck)->isNotEmpty() && $user->disabled === false;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Allocation $model): bool
    {
        $rolesToCheck = [Role::Ministry_USER, Role::Institution_ADMIN];
        $can = $user->roles()->pluck('name')->intersect($rolesToCheck)->isNotEmpty() && $user->disabled === false;

        return $can && ($user->bceid_business_guid === $model->bceid_business_guid);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $rolesToCheck = [Role::Ministry_USER];

        return $user->roles()->pluck('name')->intersect($rolesToCheck)->isNotEmpty() && $user->disabled === false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Allocation $model): bool
    {
        $rolesToCheck = [Role::Ministry_USER, Role::Institution_ADMIN];

        return $user->roles()->pluck('name')->intersect($rolesToCheck)->isNotEmpty() && $user->disabled === false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Allocation $model): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Allocation $model): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Allocation $model): bool
    {
        //
    }
}
