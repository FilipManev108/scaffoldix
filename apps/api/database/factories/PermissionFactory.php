<?php

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Permission>
 */
class PermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $group = fake()->randomElement(['workspace', 'team', 'project', 'task']);
        $action = fake()->randomElement(['view', 'create', 'update', 'delete', 'manage']).'-'.fake()->unique()->numberBetween(1000, 9999);
        $name = $group.'.'.$action;

        return [
            'name' => $name,
            'group' => $group,
            'description' => Str::headline($name).' permission.',
        ];
    }
}
