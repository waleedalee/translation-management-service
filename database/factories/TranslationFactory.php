<?php

namespace Database\Factories;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translation>
 */
class TranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Translation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $locales = ['en', 'fr', 'de'];
        $tags = ['web', 'mobile', 'desktop'];
        
        $selectedTags = $this->faker->randomElements(
            $tags, 
            $this->faker->numberBetween(1, 3)
        );

        return [
            'key' => $this->faker->unique()->word() . '.' . $this->faker->word(),
            'locale' => $this->faker->randomElement($locales),
            'content' => $this->faker->sentence(),
            'tags' => $selectedTags,
        ];
    }
} 