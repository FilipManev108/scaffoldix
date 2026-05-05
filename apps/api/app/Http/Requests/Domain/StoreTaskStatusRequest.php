<?php

namespace App\Http\Requests\Domain;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskStatusRequest extends FormRequest
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
        $project = Project::find($this->route('project'));

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash:ascii',
                Rule::unique('task_statuses', 'slug')
                    ->where(fn ($query) => $query
                        ->where('workspace_id', $project?->workspace_id)
                        ->where('project_id', $this->route('project'))),
            ],
            'color' => ['nullable', 'string', 'max:255'],
            'position' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }
}
