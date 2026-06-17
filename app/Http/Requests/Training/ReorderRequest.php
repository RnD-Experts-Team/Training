<?php

namespace App\Http\Requests\Training;

use App\Concerns\TrainingValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class ReorderRequest extends FormRequest
{
    use TrainingValidationRules;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->reorderRules();
    }

    /**
     * @return array<int, array{id: int, order: int}>
     */
    public function orderedItems(): array
    {
        /** @var array<int, array{id: int, order: int}> */
        return $this->validated()['items'];
    }
}
