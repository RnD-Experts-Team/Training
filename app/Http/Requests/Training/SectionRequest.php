<?php

namespace App\Http\Requests\Training;

use App\Concerns\TrainingValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class SectionRequest extends FormRequest
{
    use TrainingValidationRules;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->sectionRules();
    }
}
