<?php

namespace App\Http\Requests\Training;

use App\Enums\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignManagersRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'manager_ids' => ['present', 'array'],
            'manager_ids.*' => [
                'integer',
                Rule::exists('users', 'id')->where('role', Role::Manager->value),
            ],
        ];
    }

    /**
     * @return array<int, int>
     */
    public function managerIds(): array
    {
        return array_map('intval', $this->validated()['manager_ids']);
    }
}
