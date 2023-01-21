<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AdsJobsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->jobTitle(),
            'description' => $this->faker->paragraph(2),
            'requirement' => $this->faker->paragraph(1),
            'yearsOfExperiences' => rand(5, 12),
            'workTime' =>  rand(10, 20),
            'isSpecial' => rand(0, 1),
            'user_id' => rand(13, 35)
        ];
    }
}
