<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
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
