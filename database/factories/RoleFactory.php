<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition()
    {
        $roles = [
            Role::SUPER_ADMIN,
            Role::Ministry_ADMIN,
            Role::Institution_ADMIN,
            Role::Ministry_USER,
            Role::Institution_USER,
            Role::Ministry_GUEST,
            Role::Institution_GUEST,
            Role::Student,
        ];

        return [
            'name' => $this->faker->randomElement($roles),
        ];
    }
}
