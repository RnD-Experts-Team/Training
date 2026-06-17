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
            // Only honored for super admins; managers always use their own store.
            'store_id' => ['nullable', 'integer', 'exists:stores,id'],
        ];
    }
}
