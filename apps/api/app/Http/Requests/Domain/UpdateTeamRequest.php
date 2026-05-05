<?php

namespace App\Http\Requests\Domain;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamRequest extends FormRequest
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
        $teamId = $this->route('team');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                'alpha_dash:ascii',
                Rule::unique('teams', 'slug')
                    ->where(fn ($query) => $query->where('workspace_id', $workspaceId))
                    ->ignore($teamId),
            ],
            'description' => ['nullable', 'string'],
        ];
    }
}
