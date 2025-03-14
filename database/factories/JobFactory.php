<?php

namespace Database\Factories;

use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Job>
 */
class JobFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' =>fake()->randomElement(User::all()->pluck('id')->toArray()),
            'title' => fake()->jobTitle,
            'description' => fake()->paragraphs(3,true),
            'location' => fake()->city,
            'category' =>fake()->randomElement(Job::$categories),
            'salary' => fake()->numberBetween(5_000,150_000),
            'experience' => fake()->randomElement(Job::$experiences),
            'type'=>fake()->randomElement(['full_time','part_time','contract','internship']),
            'application_deadline' => fake()->date(),
        ];
    }
}
