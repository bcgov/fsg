<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    // Specify the model that this factory is for.
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

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
            'guid'                     => $guid,
            'first_name'               => $this->faker->firstName,
            'last_name'                => $this->faker->lastName,
            // Optionally, you can concatenate first and last names, or use Faker's name method.
            'name'                     => $this->faker->name,
            'disabled'                 => false,
            'email'                    => $this->faker->unique()->safeEmail,
            'password'                 => bcrypt('password'), // Default password for testing
            'idir_username'            => $this->faker->userName,
            'bcsc_username'            => $this->faker->userName,
            'bceid_username'           => $this->faker->userName,
            'idir_user_guid'           => $this->faker->uuid,
            'bcsc_user_guid'           => $this->faker->uuid,
            'bceid_user_guid'          => $this->faker->uuid,
            'bceid_business_guid'      => $this->faker->uuid,
            'last_touch_by_user_guid' => $this->faker->optional()->randomElement([str_replace('-', '', (string)Str::uuid()), null]),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
