<?php

namespace App\Actions\Training;

use App\Models\ChecklistItem;
use App\Models\Evaluation;
use App\Models\Trainee;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RecordEvaluation
{
    /**
     * Record a manager's evaluation of a single checklist item for a trainee,
     * cascading completion down to sub-items and recomputing the parent.
     *
     * @param  array{completed: bool, rating?: int|null, notes?: string|null}  $data
     */
    public function handle(Trainee $trainee, ChecklistItem $item, array $data, User $evaluator): void
    {
        DB::transaction(function () use ($trainee, $item, $data, $evaluator): void {
            $completed = (bool) $data['completed'];
            $completedAt = $completed ? now() : null;

            // 1. The item itself (carries rating + notes).
            $this->upsert($trainee, $item->id, [
                'completed' => $completed,
                'rating' => $data['rating'] ?? null,
                'notes' => $data['notes'] ?? null,
                'evaluated_by' => $evaluator->id,
                'completed_at' => $completedAt,
            ]);

            // 2. Down-cascade completion to sub-items (preserve their rating/notes).
            $childIds = ChecklistItem::where('parent_id', $item->id)->pluck('id');
            foreach ($childIds as $childId) {
                $this->upsert($trainee, (int) $childId, [
                    'completed' => $completed,
                    'evaluated_by' => $evaluator->id,
                    'completed_at' => $completedAt,
                ]);
            }

            // 3. Up-cascade: a parent is complete only when all of its children are.
            if ($item->parent_id !== null) {
                $this->recomputeParent($trainee, (int) $item->parent_id, $evaluator);
            }
        });
    }

    private function recomputeParent(Trainee $trainee, int $parentId, User $evaluator): void
    {
        $childIds = ChecklistItem::where('parent_id', $parentId)->pluck('id');

        $completedChildren = Evaluation::where('trainee_id', $trainee->id)
            ->whereIn('checklist_item_id', $childIds)
            ->where('completed', true)
            ->count();

        $allComplete = $childIds->isNotEmpty() && $completedChildren === $childIds->count();

        $this->upsert($trainee, $parentId, [
            'completed' => $allComplete,
            'evaluated_by' => $evaluator->id,
            'completed_at' => $allComplete ? now() : null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function upsert(Trainee $trainee, int $checklistItemId, array $attributes): void
    {
        Evaluation::updateOrCreate(
            ['trainee_id' => $trainee->id, 'checklist_item_id' => $checklistItemId],
            $attributes,
        );
    }
}
