<?php

namespace Database\Factories;

use App\Models\ProgramYear;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProgramYearFactory extends Factory
{
    protected $model = ProgramYear::class;

    public function definition()
    {
        // Generate a 32-character GUID by removing dashes from a UUID.
        $guid = str_replace('-', '', (string) Str::uuid());

        // Generate a random start date in the past 2 years.
        $startDate = $this->faker->dateTimeBetween('-2 years', 'now');
        // Ensure the end date is after the start date (within 2 years from start).
        $endDate = $this->faker->dateTimeBetween($startDate, '+2 years');

        return [
            'guid'           => $guid,
            'start_date'     => $startDate->format('Y-m-d'),
            'end_date'       => $endDate->format('Y-m-d'),
            'claim_percent'  => $this->faker->randomFloat(2, 0, 100),
            'status'         => 'active', // Default status as defined; optionally randomize: e.g. randomElement(['active','completed','cancelled'])
            'comment'        => $this->faker->optional()->paragraph,
            'last_touch_by_user_guid' => $this->faker->optional()->randomElement([str_replace('-', '', (string)Str::uuid()), null]),
        ];
    }
}
