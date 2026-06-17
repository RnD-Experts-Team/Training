<?php

namespace App\Models;

use Database\Factories\SectionFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string|null $icon
 * @property int $order
 * @property string|null $pie_content_review
 * @property string|null $screen_to_shoulder
 * @property string|null $hands_on_shifts
 * @property-read Collection<int, Category> $categories
 */
class Section extends Model
{
    /** @use HasFactory<SectionFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'title', 'description', 'icon', 'order',
        'pie_content_review', 'screen_to_shoulder', 'hands_on_shifts',
    ];

    /**
     * @return HasMany<Category, $this>
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class)->orderBy('order');
    }

    /**
     * All checklist items in this section (across its categories), for counts.
     *
     * @return HasManyThrough<ChecklistItem, Category, $this>
     */
    public function checklistItems(): HasManyThrough
    {
        return $this->hasManyThrough(ChecklistItem::class, Category::class);
    }

    /**
     * @param  Builder<Section>  $query
     * @return Builder<Section>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order');
    }
}
