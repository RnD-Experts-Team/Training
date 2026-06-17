<?php

namespace App\Http\Requests\Training;

use App\Concerns\TrainingValidationRules;
use App\Models\Category;
use App\Models\ChecklistItem;
use Illuminate\Foundation\Http\FormRequest;

class ChecklistItemRequest extends FormRequest
{
    use TrainingValidationRules;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return $this->checklistItemRules($this->resolveCategoryId());
    }

    /**
     * The category is taken from the route on store and from the item on update.
     */
    private function resolveCategoryId(): int
    {
        $category = $this->route('category');
        if ($category instanceof Category) {
            return $category->id;
        }

        $item = $this->route('checklistItem');
        if ($item instanceof ChecklistItem) {
            return $item->category_id;
        }

        return 0;
    }
}
