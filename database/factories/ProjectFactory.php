<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $images = [];
        $imgCount = rand(2, 4);
        for ($i = 0; $i < $imgCount; $i++) {
            $images[] = 'https://picsum.photos/seed/' . rand(1000, 9999) . '/800/600';
        }

        return [
            'id' => $this->faker->uuid(),
            'name' => ucwords($this->faker->words(4, true)),
            'style' => $this->faker->randomElement(['Traditional', 'Modern', 'Minimalist', 'Futuristic', 'Industrial']),
            'description' => $this->faker->paragraphs(2, true),
            'images' => $images,
            'layout_images' => $images,
            'highlight_features' => $this->faker->sentence(5),
            'estimated_cost' => 'Rp ' . $this->faker->numberBetween(200, 900) . 'jt - ' . $this->faker->numberBetween(1, 5) . 'M',
            'area' => $this->faker->randomElement(['80 m2 (8x10m)', '120 m2 (10x12m)', '150 m2 (12x12m)', '200 m2 (10x20m)']),
            'status' => 'approved',
            'likes_count' => $this->faker->numberBetween(0, 500),
        ];
    }
}
