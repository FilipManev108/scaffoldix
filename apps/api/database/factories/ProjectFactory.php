<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'workspace_id' => Workspace::factory(),
            'team_id' => fn (array $attributes) => Team::factory()->create([
                'workspace_id' => $attributes['workspace_id'],
            ])->id,
            'created_by' => User::factory(),
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1000, 9999),
            'description' => fake()->optional()->paragraph(),
            'starts_at' => fake()->optional()->dateTimeBetween('-1 month', '+1 month'),
            'ends_at' => fake()->optional()->dateTimeBetween('+1 month', '+6 months'),
        ];
    }
}
