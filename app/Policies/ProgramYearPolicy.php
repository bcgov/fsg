<?php

namespace App\Policies;

use App\Models\ProgramYear;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProgramYearPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        $rolesToCheck = [Role::Ministry_ADMIN, Role::SUPER_ADMIN];

        return $user->roles()->pluck('name')->intersect($rolesToCheck)->isNotEmpty() && $user->disabled === false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProgramYear $model): bool
    {
        $rolesToCheck = [Role::Ministry_ADMIN, Role::SUPER_ADMIN];

        return $user->roles()->pluck('name')->intersect($rolesToCheck)->isNotEmpty() && $user->disabled === false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $rolesToCheck = [Role::Ministry_ADMIN];

        return $user->roles()->pluck('name')->intersect($rolesToCheck)->isNotEmpty() && $user->disabled === false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProgramYear $model): bool
    {
        $rolesToCheck = [Role::Ministry_ADMIN];

        return $user->roles()->pluck('name')->intersect($rolesToCheck)->isNotEmpty() && $user->disabled === false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProgramYear $model): bool
    {
        $rolesToCheck = [Role::SUPER_ADMIN];

        return $user->roles()->pluck('name')->intersect($rolesToCheck)->isNotEmpty() && $user->disabled === false;

    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProgramYear $model): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProgramYear $model): bool
    {
        //
    }
}
