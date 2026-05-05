<?php

namespace App\Http\Requests\Domain;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeamRequest extends FormRequest
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

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash:ascii',
                Rule::unique('teams', 'slug')
                    ->where(fn ($query) => $query->where('workspace_id', $workspaceId)),
            ],
            'description' => ['nullable', 'string'],
        ];
    }
}
