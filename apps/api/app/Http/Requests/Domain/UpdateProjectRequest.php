<?php

namespace App\Http\Requests\Domain;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $workspaceId = $this->route('workspace');
        $projectId = $this->route('project');

        return [
            'team_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('teams', 'id')
                    ->where(fn ($query) => $query
                        ->where('workspace_id', $workspaceId)
                        ->whereNull('deleted_at')),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'alpha_dash:ascii',
                Rule::unique('projects', 'slug')
                    ->where(fn ($query) => $query->where('workspace_id', $workspaceId))
                    ->ignore($projectId),
            ],
            'description' => ['nullable', 'string'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ];
    }
}
