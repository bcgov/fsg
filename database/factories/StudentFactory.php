<?php

namespace Database\Factories;

use App\Models\Role;
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
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => Role::Student]);
        $user->roles()->attach($role->id);

        return [
            'guid'                => $guid,
            'user_guid'           => $user->guid,
            'sin'                 => '254825474',
            'first_name'          => $user->first_name,
            'last_name'           => $user->last_name,
            'dob'                 => $this->faker->date('Y-m-d'),
            'gender'              => $this->faker->randomElement(['Male', 'Female', 'Other']),
            'email'               => $user->email,
            'city'                => $this->faker->city,
            'zip_code'            => 'V9V9V9',
            'citizenship'         => $this->faker->word,
            'grade12_or_over19'   => $this->faker->randomElement(['grade12', 'over19']),
            'bc_resident'         => true,
            'info_consent'        => true,
            'duplicative_funding' => true,
            'tax_implications'    => true,
            'lifetime_max'        => true,
            'fed_prov_benefits'   => true,
            'workbc_client'       => true,
            'additional_supports' => true,
            'total_grant'         => $this->faker->randomFloat(2, 0, 10000),
            'excel_guid'          => $this->faker->optional()->uuid,
        ];
    }
}
