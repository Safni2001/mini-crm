<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->optional(0.9)->safeEmail(),
            'phone' => fake()->optional(0.7)->phoneNumber(),
            'company_id' => fake()->optional(0.8)->passthrough(\App\Models\Company::factory()),
        ];
    }

    /**
     * Indicate that the employee has no email.
     */
    public function withoutEmail(): static
    {
        return $this->state(fn (array $attributes) => [
            'email' => null,
        ]);
    }

    /**
     * Indicate that the employee has no phone.
     */
    public function withoutPhone(): static
    {
        return $this->state(fn (array $attributes) => [
            'phone' => null,
        ]);
    }

    /**
     * Indicate that the employee is not assigned to any company.
     */
    public function withoutCompany(): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => null,
        ]);
    }

    /**
     * Set a specific company for the employee.
     */
    public function forCompany($company): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => is_object($company) ? $company->id : $company,
        ]);
    }
}
