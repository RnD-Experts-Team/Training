<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;

class MoveItemsRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:checklist_items,id'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
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
