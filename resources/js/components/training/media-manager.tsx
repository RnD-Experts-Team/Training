import { router, useForm } from '@inertiajs/react';
import {
    ExternalLink,
    FileText,
    ImageIcon,
    LinkIcon,
    Trash2,
    Video,
} from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { destroy, store } from '@/routes/training/media';
import type { ChecklistItem, MediaType } from '@/types/training';

const TYPE_ICON = {
    link: LinkIcon,
    image: ImageIcon,
    video: Video,
    file: FileText,
} as const;

export function MediaManager({ item }: { item: ChecklistItem }) {
    const [adding, setAdding] = useState(false);
    const form = useForm<{
        type: MediaType;
        url: string;
        label: string;
        file: File | null;
    }>({
        type: 'link',
        url: '',
        label: '',
        file: null,
    });

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post(store(item.id).url, {
            preserveScroll: true,
            forceFormData: true,
            onSuccess: () => {
                form.reset();
                setAdding(false);
            },
        });
    }

    const media = item.media ?? [];

    return (
        <div className="space-y-2">
            {media.length > 0 && (
                <ul className="flex flex-wrap gap-2">
                    {media.map((m) => {
                        const Icon = TYPE_ICON[m.type];

                        return (
                            <li
                                key={m.id}
                                className="flex items-center gap-2 rounded-md border bg-muted/40 py-1 pr-1 pl-2 text-xs"
                            >
                                <Icon className="size-3.5 text-muted-foreground" />
                                <a
                                    href={m.display_url ?? '#'}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="max-w-44 truncate hover:underline"
                                >
                                    {m.label || m.url || m.type}
                                </a>
                                <ExternalLink className="size-3 text-muted-foreground" />
                                <button
                                    type="button"
                                    onClick={() =>
                                        router.delete(destroy(m.id).url, {
                                            preserveScroll: true,
                                        })
                                    }
                                    className="rounded p-0.5 text-muted-foreground hover:bg-destructive/10 hover:text-destructive"
                                    aria-label="Remove media"
                                >
                                    <Trash2 className="size-3.5" />
                                </button>
                            </li>
                        );
                    })}
                </ul>
            )}

            {adding ? (
                <form
                    onSubmit={submit}
                    className="flex flex-wrap items-start gap-2 rounded-md border p-2"
                >
                    <Select
                        value={form.data.type}
                        onValueChange={(value) => {
                            form.setData('type', value as MediaType);
                            form.setData('file', null);
                            form.setData('url', '');
                        }}
                    >
                        <SelectTrigger size="sm" className="w-28">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="link">Link</SelectItem>
                            <SelectItem value="image">Image</SelectItem>
                            <SelectItem value="video">Video</SelectItem>
                            <SelectItem value="file">File</SelectItem>
                        </SelectContent>
                    </Select>

                    {form.data.type === 'link' ? (
                        <div className="flex-1">
                            <Input
                                value={form.data.url}
                                onChange={(e) =>
                                    form.setData('url', e.target.value)
                                }
                                placeholder="https://…"
                                className="h-8"
                            />
                            <InputError message={form.errors.url} />
                        </div>
                    ) : (
                        <div className="flex-1">
                            <Input
                                type="file"
                                onChange={(e) =>
                                    form.setData(
                                        'file',
                                        e.target.files?.[0] ?? null,
                                    )
                                }
                                className="h-8"
                            />
                            <InputError message={form.errors.file} />
                        </div>
                    )}

                    <Input
                        value={form.data.label}
                        onChange={(e) => form.setData('label', e.target.value)}
                        placeholder="Label (optional)"
                        className="h-8 w-40"
                    />

                    <Button type="submit" size="sm" disabled={form.processing}>
                        Add
                    </Button>
                    <Button
                        type="button"
                        size="sm"
                        variant="ghost"
                        onClick={() => setAdding(false)}
                    >
                        Cancel
                    </Button>
                </form>
            ) : (
                <Button
                    type="button"
                    size="sm"
                    variant="outline"
                    onClick={() => setAdding(true)}
                >
                    <LinkIcon className="size-3.5" /> Add media
                </Button>
            )}
        </div>
    );
}
