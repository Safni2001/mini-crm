<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $companyName = fake()->company();
        
        return [
            'name' => $companyName,
            'email' => fake()->companyEmail(),
            'website' => fake()->optional(0.8)->url(),
            'logo' => fake()->optional(0.3)->randomElement([
                'logos/company-1.jpg',
                'logos/company-2.jpg',
                'logos/company-3.png',
                null
            ]),
        ];
    }

    /**
     * Indicate that the company has no logo.
     */
    public function withoutLogo(): static
    {
        return $this->state(fn (array $attributes) => [
            'logo' => null,
        ]);
    }

    /**
     * Indicate that the company has a logo.
     */
    public function withLogo(): static
    {
        return $this->state(fn (array $attributes) => [
            'logo' => 'logos/test-' . fake()->uuid() . '.jpg',
        ]);
    }

    /**
     * Indicate that the company has no website.
     */
    public function withoutWebsite(): static
    {
        return $this->state(fn (array $attributes) => [
            'website' => null,
        ]);
    }
}
