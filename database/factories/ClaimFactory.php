<?php

namespace Database\Factories;

use App\Models\Allocation;
use App\Models\Claim;
use App\Models\Institution;
use App\Models\Program;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClaimFactory extends Factory
{
    protected $model = Claim::class;

    public function definition()
    {
        // Generate a 32-character GUID by removing dashes from a UUID.
        $guid = str_replace('-', '', (string) Str::uuid());

        return [
            'guid'                    => $guid,
            'institution_guid'        => function () {
                return Institution::factory()->create()->guid;
            },
            'allocation_guid'        => function () {
                return Allocation::factory()->create()->guid;
            },
            'student_guid'        => function () {
                return Student::factory()->create()->guid;
            },
            'program_guid'        => function () {
                return Program::factory()->create()->guid;
            },
            'sin'                     => $this->faker->optional()->numberBetween(100000000, 999999999),
            'first_name'              => $this->faker->firstName,
            'last_name'               => $this->faker->lastName,
            'dob'                     => $this->faker->date('Y-m-d'),
            'email'                   => $this->faker->optional()->safeEmail,
            'city'                    => $this->faker->optional()->city,
            'zip_code'                => $this->faker->optional()->postcode,
            'claim_type'              => 'Program', // default as defined in migration
            'course_name'             => $this->faker->optional()->word,
            'claim_status'            => $this->faker->optional()->randomElement(['Pending', 'Approved', 'Rejected']),
            'outcome_status'          => $this->faker->optional()->randomElement(['Completed', 'InProgress']),
            'registration_fee'        => $this->faker->randomFloat(2, 0, 1000),
            'materials_fee'           => $this->faker->randomFloat(2, 0, 1000),
            'program_fee'             => $this->faker->randomFloat(2, 0, 1000),
            'claim_percent'           => $this->faker->randomFloat(2, 0, 100),
            'estimated_hold_amount'   => $this->faker->randomFloat(2, 0, 1000),
            'total_claim_amount'      => $this->faker->randomFloat(2, 0, 1000),
            'stable_enrolment_date'   => $this->faker->optional()->date('Y-m-d'),
            'expected_stable_enrolment_date' => $this->faker->optional()->date('Y-m-d'),
            'expiry_date'             => $this->faker->optional()->date('Y-m-d'),
            'psi_claim_request_date'  => $this->faker->optional()->date('Y-m-d'),
            'reporting_completed_date'=> $this->faker->optional()->date('Y-m-d'),
            'claimed_date'            => $this->faker->optional()->date('Y-m-d'),
            'expected_completion_date'=> $this->faker->optional()->date('Y-m-d'),
            'outcome_effective_date'  => $this->faker->optional()->date('Y-m-d'),
            'fifty_two_week_affirmation' => $this->faker->boolean,
            'agreement_confirmed'     => $this->faker->boolean,
            'registration_confirmed'  => $this->faker->boolean,
            'claimed_by_user_guid'    => $this->faker->optional()->uuid,
            'student_excel_guid'      => $this->faker->optional()->uuid,
            'program_excel_guid'      => $this->faker->optional()->uuid,
            'claim_excel_guid'        => $this->faker->optional()->uuid,
            'process_feedback'        => $this->faker->optional()->paragraph,
            'correction_amount'       => $this->faker->randomFloat(2, 0, 1000),
            'correction_comment'      => $this->faker->optional()->sentence,
        ];
    }
}
