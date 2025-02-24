<?php

namespace Database\Factories;

use App\Models\Allocation;
use App\Models\Institution;
use App\Models\ProgramYear;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AllocationFactory extends Factory
{
    protected $model = Allocation::class;

    public function definition()
    {
        // Generate a 32-character GUID by removing dashes from a UUID.
        $generateGuid = function () {
            return str_replace('-', '', (string) Str::uuid());
        };

        // Generate total_amount and used_amount. For testing, we can have used_amount be less than or equal to total_amount.
        $totalAmount = $this->faker->randomFloat(2, 1000, 10000);
        $usedAmount = $this->faker->randomFloat(2, 0, $totalAmount);
        $overageWarningPercent = $this->faker->randomFloat(2, 0, 100);

        return [
            'guid'                    => $generateGuid(),
            'institution_guid'        => function () {
                return Institution::factory()->create()->guid;
            },
            'program_year_guid'       => function () {
                return ProgramYear::factory()->create()->guid;
            },
            'used_amount'             => $usedAmount,
            'total_amount'            => $totalAmount,
            'overage_warning_percent'             => 0,
//            'status'                  => $this->faker->randomElement(['active', 'completed', 'cancelled']),
            'status'                  => 'active',
        ];
    }
}
