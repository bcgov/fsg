<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    // Specify the model that this factory is for.
    protected $model = Student::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a 32-character GUID by removing dashes from a UUID.
        $guid = str_replace('-', '', (string) Str::uuid());

        return [
            'guid'                => $guid,
            'user_guid'        => function () {
                return User::factory()->create()->guid;
            },
            'sin'                 => $this->faker->optional()->numberBetween(100000000, 999999999),
            'first_name'          => $this->faker->firstName,
            'last_name'           => $this->faker->lastName,
            'dob'                 => $this->faker->date('Y-m-d'),
            'gender'              => $this->faker->optional()->randomElement(['Male', 'Female', 'Other']),
            'email'               => $this->faker->optional()->safeEmail,
            'city'                => $this->faker->optional()->city,
            'zip_code'            => $this->faker->optional()->postcode,
            'citizenship'         => $this->faker->optional()->word,
            'grade12_or_over19'   => $this->faker->optional()->randomElement(['grade12', 'over19']),
            'bc_resident'         => $this->faker->boolean,
            'info_consent'        => $this->faker->boolean,
            'duplicative_funding' => $this->faker->boolean,
            'tax_implications'    => $this->faker->boolean,
            'lifetime_max'        => $this->faker->boolean,
            'fed_prov_benefits'   => $this->faker->boolean,
            'workbc_client'       => $this->faker->boolean,
            'additional_supports' => $this->faker->boolean,
            'total_grant'         => $this->faker->randomFloat(2, 0, 10000),
            'excel_guid'          => $this->faker->optional()->uuid,
        ];
    }
}
