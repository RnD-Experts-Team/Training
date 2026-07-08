<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EvaluationRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        // A manager cannot mark an item complete without scoring and noting it.
        $requiredWhenCompleted = Rule::requiredIf(fn (): bool => $this->boolean('completed'));

        return [
            'completed' => ['required', 'boolean'],
            'rating' => ['nullable', 'integer', 'min:0', 'max:100', $requiredWhenCompleted],
            'notes' => ['nullable', 'string', 'max:2000', $requiredWhenCompleted],
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

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rating.required' => __('Add a score before marking this step complete.'),
            'notes.required' => __('Add a note before marking this step complete.'),
        ];
    }
}
