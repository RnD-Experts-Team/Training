<?php

namespace App\Http\Requests\Training;

use Illuminate\Foundation\Http\FormRequest;

class QuizQuestionRequest extends FormRequest
{
    /**
     * Fixed at exactly 4 choices (A–D), one marked correct by index — keeps
     * the authoring form simple for what's meant to be a quick check.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'max:500'],
            'options' => ['required', 'array', 'size:4'],
            'options.*' => ['required', 'string', 'max:255'],
            'correct_index' => ['required', 'integer', 'between:0,3'],
        ];
    }
}
