<?php

namespace App\Models;

use Database\Factories\EvaluationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $trainee_id
 * @property int $checklist_item_id
 * @property bool $completed
 * @property int|null $rating
 * @property string|null $notes
 * @property int|null $evaluated_by
 * @property Carbon|null $completed_at
 * @property-read Trainee $trainee
 * @property-read ChecklistItem $checklistItem
 * @property-read User|null $evaluator
 */
class Evaluation extends Model
{
    /** @use HasFactory<EvaluationFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'trainee_id', 'checklist_item_id', 'completed', 'rating', 'notes', 'evaluated_by', 'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Trainee, $this>
     */
    public function trainee(): BelongsTo
    {
        return $this->belongsTo(Trainee::class);
    }

    /**
     * @return BelongsTo<ChecklistItem, $this>
     */
    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(ChecklistItem::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}
