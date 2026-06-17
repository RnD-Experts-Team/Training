<?php

namespace App\Concerns;

use App\Enums\Importance;
use App\Enums\MediaType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

trait TrainingValidationRules
{
    /**
     * @return array<string, array<int, ValidationRule|Enum|string>>
     */
    protected function sectionRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:255'],
            'pie_content_review' => ['nullable', 'string', 'max:255'],
            'screen_to_shoulder' => ['nullable', 'string', 'max:255'],
            'hands_on_shifts' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, array<int, ValidationRule|string>>
     */
    protected function categoryRules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    /**
     * @param  int  $categoryId  Constrains the parent item to the same category.
     * @return array<string, array<int, ValidationRule|Enum|string>>
     */
    protected function checklistItemRules(int $categoryId): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'importance' => ['required', new Enum(Importance::class)],
            'parent_id' => [
                'nullable',
                Rule::exists('checklist_items', 'id')->where('category_id', $categoryId),
            ],
        ];
    }

    /**
     * @return array<string, array<int, ValidationRule|Enum|string>>
     */
    protected function reorderRules(): array
    {
        return [
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'integer'],
            'items.*.order' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * Media validation switches on the submitted type.
     *
     * @return array<string, array<int, ValidationRule|Enum|string>>
     */
    protected function mediaRules(): array
    {
        $type = $this->input('type');

        $rules = [
            'type' => ['required', new Enum(MediaType::class)],
            'label' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'string'],
            'file' => ['nullable', 'file'],
        ];

        if ($type === MediaType::Link->value) {
            $rules['url'] = ['required', 'url', 'max:2048'];
        } elseif ($type === MediaType::Image->value) {
            $rules['file'] = ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'];
        } elseif ($type === MediaType::Video->value) {
            $rules['file'] = ['required', 'file', 'mimetypes:video/mp4,video/quicktime,video/webm', 'max:51200'];
        } elseif ($type === MediaType::File->value) {
            $rules['file'] = ['required', 'file', 'mimes:pdf,doc,docx,xlsx,csv,txt', 'max:10240'];
        }

        return $rules;
    }
}
