<?php

namespace Tests\Traits;

use App\Models\Institution;
use App\Models\InstitutionStaff;
use App\Models\ProgramYear;
use App\Models\Allocation;
use App\Models\User;
use App\Models\Role;

trait CreatesInstitutionUser
{
    /**
     * Create an institution user with the specified role.
     *
     * @param  string  $roleName  The role to assign (e.g. Role::Institution_USER or Role::Institution_ADMIN)
     * @return \App\Models\User
     */
    protected function createInstitutionUser(string $roleName = Role::Institution_USER): User
    {
        $user = User::factory()->create();

        $institution = Institution::factory()->create();
        InstitutionStaff::factory()->create([
            'institution_guid' => $institution->guid,
            'user_guid'        => $user->guid,
        ]);

        $programYear = ProgramYear::factory()->create();
        Allocation::factory()->create([
            'institution_guid'  => $institution->guid,
            'program_year_guid' => $programYear->guid,
        ]);

        $role = Role::firstOrCreate(['name' => $roleName]);
        $user->roles()->attach($role->id);

        return $user;
    }
}
