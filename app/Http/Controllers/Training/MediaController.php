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
            $file = $request->file('file');
            $path = $file->store("training/media/{$checklistItem->id}", 'public');

            // The public disk is configured with `throw => false`, so a failed
            // write returns false instead of raising — don't persist a broken row.
            if (! is_string($path) || $path === '') {
                Inertia::flash('toast', [
                    'type' => 'error',
                    'message' => __('The file could not be saved. Please try again.'),
                ]);

                return back();
            }

            $attributes['path'] = $path;

            // Stored files get a hashed name, so keep the original filename as the
            // label when none was given — otherwise attachments render as "Attachment".
            $attributes['label'] = $attributes['label'] ?: $file->getClientOriginalName();
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
