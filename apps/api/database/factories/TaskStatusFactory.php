<?php

namespace Database\Factories;

use App\Models\TaskStatus;
use App\Models\Project;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<TaskStatus>
 */
class TaskStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement(['Backlog', 'Todo', 'In Progress', 'Review', 'Done']).' '.fake()->unique()->numberBetween(1000, 9999);

        return [
            'workspace_id' => Workspace::factory(),
            'project_id' => fn (array $attributes) => fake()->boolean()
                ? Project::factory()->create(['workspace_id' => $attributes['workspace_id']])->id
                : null,
            'name' => $name,
            'slug' => Str::slug($name),
            'color' => fake()->optional()->hexColor(),
            'position' => fake()->numberBetween(0, 10),
            'is_default' => false,
        ];
    }

    public function default(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
            'position' => 0,
        ]);
    }
}
