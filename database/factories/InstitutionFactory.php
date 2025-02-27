<?php

namespace Database\Factories;

use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Institution>
 */
class InstitutionFactory extends Factory
{
    // Specify the model this factory is for.
    protected $model = Institution::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a 32-character GUID by removing dashes.
        $guid = str_replace('-', '', (string) Str::uuid());

        return [
            'guid'                   => $guid,
            // Generate a random 32-character string for bceid_business_guid.
            'bceid_business_guid'    => $this->faker->regexify('[A-Za-z0-9]{32}'),
            'dli'                    => $this->faker->optional()->word,
            'name'                   => $this->faker->company,
            'name_code'              => strtoupper($this->faker->lexify(str_repeat('?', 10))), // generates a 10-character uppercase string
            'size'                   => $this->faker->optional()->randomElement(['small', 'medium', 'large']),
            'category'               => $this->faker->optional()->word,
            'economic_region'        => $this->faker->optional()->word,
            'legal_name'             => $this->faker->optional()->company,
            'address1'               => $this->faker->optional()->streetAddress,
            'address2'               => $this->faker->optional()->secondaryAddress,
            'primary_contact'        => $this->faker->optional()->name,
            'primary_email'          => $this->faker->optional()->safeEmail,
            'city'                   => $this->faker->optional()->city,
            'postal_code'            => $this->faker->optional()->postcode,
            'province'               => $this->faker->optional()->stateAbbr, // 2-letter abbreviation; adjust if needed
            'active_status'          => true,
            'last_touch_by_user_guid' => $this->faker->optional()->randomElement([str_replace('-', '', (string)Str::uuid()), null]),
        ];
    }
}
