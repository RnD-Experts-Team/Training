<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;

class TraineeRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'hired_at' => ['nullable', 'date'],
            // The controller checks the chosen store is one the user may use
            // (any store for super admins, the manager's own stores otherwise).
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
        ];
    }
}
