<?php

namespace Database\Factories;

use App\Models\Catalog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Catalog>
 */
class CatalogFactory extends Factory
{
    protected $model = Catalog::class;

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

        $highlights = [];
        $hlCount = rand(3, 5);
        for ($i = 0; $i < $hlCount; $i++) {
            $highlights[] = $this->faker->sentence(3);
        }

        $roomsOptions = ['2 Bedrooms', '3 Bedrooms + 1 Office', 'Studio', '4 Bedrooms, 2 Bathrooms', '1 Bedroom'];

        return [
            'id' => $this->faker->uuid(),
            'title' => ucwords($this->faker->words(4, true)),
            'style' => $this->faker->randomElement(['Traditional', 'Modern', 'Minimalist', 'Futuristic', 'Industrial']),
            'description' => $this->faker->paragraphs(2, true),
            'images' => $images,
            'interior_highlights' => $highlights,
            'layout_image' => 'https://picsum.photos/seed/' . rand(1000, 9999) . '/400/300',
            'rooms' => $this->faker->randomElement($roomsOptions),
            'estimated_cost' => $this->faker->numberBetween(200_000_000, 2_000_000_000),
            'area' => $this->faker->randomElement(["80 m2 (8x10m)", "120 m2 (10x12m)", "150 m2 (12x12m)", "200 m2 (10x20m)"]),
            'status' => 'approved',
            'rating' => $this->faker->randomFloat(1, 3.5, 5.0),
            'likes_count' => $this->faker->numberBetween(0, 500),
        ];
    }
}
