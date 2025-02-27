<?php

namespace Database\Factories;

use App\Models\Institution;
use App\Models\InstitutionStaff;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class InstitutionStaffFactory extends Factory
{
    protected $model = InstitutionStaff::class;

    public function definition()
    {
        // Generate a 32-character string by removing dashes from a UUID.
        $generateGuid = function () {
            return str_replace('-', '', (string) Str::uuid());
        };

        return [
            'guid'                    => $generateGuid(),
            'user_guid'        => function () {
                return User::factory()->create()->guid;
            },
            'institution_guid'        => function () {
                return Institution::factory()->create()->guid;
            },
            'bceid_business_guid'     => $this->faker->regexify('[A-Za-z0-9]{32}'),
            'bceid_user_guid'         => $this->faker->regexify('[A-Za-z0-9]{32}'),
            'bceid_user_id'           => $this->faker->regexify('[A-Za-z0-9]{8}'),
            'bceid_user_name'         => $this->faker->optional()->userName,
            'bceid_user_email'        => $this->faker->optional()->safeEmail,
//            'status'                  => $this->faker->randomElement(['pending', 'active', 'inactive']),
            'status'                  => 'active',
            'last_touch_by_user_guid' => $this->faker->optional()->randomElement([str_replace('-', '', (string)Str::uuid()), null]),
        ];
    }
}
