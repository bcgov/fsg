<?php

namespace Database\Factories;

use App\Models\Institution;
use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProgramFactory extends Factory
{
    protected $model = Program::class;

    public function definition()
    {
        // Generate a 32-character GUID by removing dashes.
        $guid = str_replace('-', '', (string) Str::uuid());

        // Define valid options for specific fields.
        $deliveryMethods = ['In-person', 'Hybrid or Blended', 'Online'];
        $onlineDeliveryTypes = ['Synchronous', 'Asynchronous'];
        $credentialTypes = ['Micro-Credential', 'Short-Certificate', 'Other'];
        $microCredentialTypes = ['No Applicable', 'Provincially Approved', 'Not Provincially Approved'];

        return [
            'guid'                              => $guid,
            'institution_guid'        => function () {
                return Institution::factory()->create()->guid;
            },
            'program_name'                      => $this->faker->sentence(3),
            'program_type'                      => $this->faker->word,
            'program_number'                    => $this->faker->optional()->bothify('??-###'),
            'delivery_method'                   => $this->faker->optional()->randomElement($deliveryMethods),
            'online_delivery_type'              => $this->faker->optional()->randomElement($onlineDeliveryTypes),
            'credential_type'                   => $this->faker->optional()->randomElement($credentialTypes),
            'micro_credential_type'             => $this->faker->optional()->randomElement($microCredentialTypes),
            'high_priority_industry'            => $this->faker->optional()->word,
            'total_duration_hrs'                => $this->faker->randomFloat(2, 0, 1000),
            'creditable'                        => $this->faker->boolean,
            'full_time'                         => $this->faker->boolean,
            'prov_funded_micro_cred'            => $this->faker->boolean,
            'indigenous_related_learning'       => $this->faker->boolean,
            'diversity_inclusion_related_learning'=> $this->faker->boolean,
            'active_status'                     => $this->faker->boolean,
            'last_touch_by_user_guid' => $this->faker->optional()->randomElement([str_replace('-', '', (string)Str::uuid()), null]),
            'excel_guid'                        => $this->faker->optional()->uuid,
            'start_date'                        => $this->faker->optional()->date('Y-m-d'),
            'end_date'                          => $this->faker->optional()->date('Y-m-d'),
        ];
    }
}
