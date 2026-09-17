<?php

namespace App\Models;

use Database\Factories\QuizQuestionFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $quiz_id
 * @property string $prompt
 * @property int $order
 * @property-read Quiz $quiz
 * @property-read Collection<int, QuizQuestionOption> $options
 */
class QuizQuestion extends Model
{
    /** @use HasFactory<QuizQuestionFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['quiz_id', 'prompt', 'order'];

    /**
     * @return BelongsTo<Quiz, $this>
     */
    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * @return HasMany<QuizQuestionOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(QuizQuestionOption::class)->orderBy('order');
    }
}
