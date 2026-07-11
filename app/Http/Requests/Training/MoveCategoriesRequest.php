<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;

class MoveCategoriesRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:categories,id'],
            'section_id' => ['required', 'integer', 'exists:sections,id'],
        ];
    }

    /**
     * @return array<int, int>
     */
    public function ids(): array
    {
        return array_map('intval', $this->validated()['ids']);
    }
}
