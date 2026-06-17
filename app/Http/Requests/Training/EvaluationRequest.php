<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;

class EvaluationRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'completed' => ['required', 'boolean'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    /**
     * @return array{completed: bool, rating: int|null, notes: string|null}
     */
    public function evaluationData(): array
    {
        return [
            'completed' => $this->boolean('completed'),
            'rating' => $this->input('rating') !== null ? (int) $this->input('rating') : null,
            'notes' => $this->input('notes'),
        ];
    }
}
