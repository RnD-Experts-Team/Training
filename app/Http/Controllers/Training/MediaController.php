<?php

namespace App\Http\Controllers\Training;

use App\Enums\MediaType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Training\StoreMediaRequest;
use App\Models\ChecklistItem;
use App\Models\MediaItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class MediaController extends Controller
{
    public function store(StoreMediaRequest $request, ChecklistItem $checklistItem): RedirectResponse
    {
        $type = MediaType::from($request->validated('type'));

        $attributes = [
            'type' => $type,
            'label' => $request->validated('label'),
            'order' => (int) $checklistItem->media()->max('order') + 1,
        ];

        if ($type === MediaType::Link) {
            $attributes['url'] = $request->validated('url');
        } else {
            $attributes['path'] = $request->file('file')->store("training/media/{$checklistItem->id}", 'public');
        }

        $checklistItem->media()->create($attributes);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Media added.')]);

        return back();
    }

    public function destroy(MediaItem $media): RedirectResponse
    {
        if ($media->path) {
            Storage::disk('public')->delete($media->path);
        }

        $media->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Media removed.')]);

        return back();
    }
}
