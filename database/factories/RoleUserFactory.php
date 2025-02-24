<?php

namespace Database\Factories;

use App\Models\RoleUser;
use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoleUserFactory extends Factory
{
    protected $model = RoleUser::class;

    public function definition()
    {
        return [
            // Create a new User if one isn't provided
            'user_id' => User::factory(),
            // Create a new Role if one isn't provided
            'role_id' => Role::factory(),
        ];
    }
}
