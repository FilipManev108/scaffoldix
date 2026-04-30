<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->jobTitle();

        return [
            'workspace_id' => Workspace::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'level' => fake()->numberBetween(10, 90),
            'is_system' => false,
            'description' => fake()->optional()->sentence(),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Admin',
            'slug' => 'admin',
            'level' => 100,
            'is_system' => true,
        ]);
    }

    public function teamLead(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Team Lead',
            'slug' => 'team-lead',
            'level' => 80,
            'is_system' => true,
        ]);
    }

    public function senior(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Senior',
            'slug' => 'senior',
            'level' => 60,
            'is_system' => true,
        ]);
    }

    public function mid(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Mid',
            'slug' => 'mid',
            'level' => 40,
            'is_system' => true,
        ]);
    }

    public function junior(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Junior',
            'slug' => 'junior',
            'level' => 20,
            'is_system' => true,
        ]);
    }

    public function viewer(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Viewer',
            'slug' => 'viewer',
            'level' => 10,
            'is_system' => true,
        ]);
    }
}
