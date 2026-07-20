<?php

namespace App\Http\Requests\Training;

use App\Enums\Role;
use App\Models\Trainee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignManagersRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $trainee = $this->route('trainee');
        $storeId = $trainee instanceof Trainee ? $trainee->store_id : null;

        $managerRules = [
            'integer',
            Rule::exists('users', 'id')->where('role', Role::Manager->value),
        ];

        // The picker only offers managers from the trainee's store; enforce that
        // server-side too so a crafted request can't grant cross-store access.
        if ($storeId !== null) {
            $managerRules[] = Rule::exists('manager_store', 'user_id')
                ->where('store_id', $storeId);
        }

        return [
            'manager_ids' => ['present', 'array'],
            'manager_ids.*' => $managerRules,
        ];
    }

    /**
     * @return array<int, int>
     */
    public function managerIds(): array
    {
        return array_map('intval', $this->validated()['manager_ids']);
    }
}
