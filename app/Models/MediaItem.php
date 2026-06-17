<?php

namespace App\Models;

use App\Enums\MediaType;
use Database\Factories\MediaItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $checklist_item_id
 * @property MediaType $type
 * @property string|null $url
 * @property string|null $path
 * @property string|null $label
 * @property int $order
 * @property-read string|null $display_url
 * @property-read ChecklistItem $checklistItem
 */
class MediaItem extends Model
{
    /** @use HasFactory<MediaItemFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['checklist_item_id', 'type', 'url', 'path', 'label', 'order'];

    /** @var list<string> */
    protected $appends = ['display_url'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => MediaType::class,
        ];
    }

    /**
     * @return BelongsTo<ChecklistItem, $this>
     */
    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(ChecklistItem::class);
    }

    /**
     * The resolved URL the frontend renders: the link itself, or the public
     * URL of the stored file.
     */
    public function getDisplayUrlAttribute(): ?string
    {
        if ($this->type === MediaType::Link) {
            return $this->url;
        }

        return $this->path ? Storage::disk('public')->url($this->path) : null;
    }
}
