<?php

namespace Database\Factories;

use App\Models\Util;
use Illuminate\Database\Eloquent\Factories\Factory;

class UtilFactory extends Factory
{
    protected $fillable = ['field_name', 'field_type', 'field_description', 'active_flag'];
    protected $model = Util::class;

    public function definition()
    {
        return [
            'field_name'                      => $this->faker->sentence(3),
            'field_type'                      => $this->faker->word,
            'field_description'                      => $this->faker->word,
            'active_flag'                          => true,
        ];
    }
}
