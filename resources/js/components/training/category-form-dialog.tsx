import { useForm } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent, ReactNode } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { CATEGORY_COLORS, CATEGORY_COLOR_CLASSES } from '@/lib/category-colors';
import { cn } from '@/lib/utils';
import { store, update } from '@/routes/training/categories';
import type { Category } from '@/types/training';

export function CategoryFormDialog({
    sectionId,
    category,
    trigger,
}: {
    sectionId: number;
    category?: Category;
    trigger: ReactNode;
}) {
    const [open, setOpen] = useState(false);
    const form = useForm<{
        title: string;
        description: string;
        color: string | null;
    }>({
        title: category?.title ?? '',
        description: category?.description ?? '',
        color: category?.color ?? null,
    });

    function submit(event: FormEvent) {
        event.preventDefault();
        const options = {
            preserveScroll: true,
            onSuccess: () => {
                setOpen(false);

                if (!category) {
                    form.reset();
                }
            },
        };

        if (category) {
            form.put(update(category.id).url, options);
        } else {
            form.post(store(sectionId).url, options);
        }
    }

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {category ? 'Edit category' : 'New category'}
                    </DialogTitle>
                    <DialogDescription>
                        Categories group related checklist items within a
                        station.
                    </DialogDescription>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    <div className="grid gap-2">
                        <Label htmlFor="cat-title">Title</Label>
                        <Input
                            id="cat-title"
                            value={form.data.title}
                            onChange={(e) =>
                                form.setData('title', e.target.value)
                            }
                            autoFocus
                            required
                        />
                        <InputError message={form.errors.title} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="cat-description">Description</Label>
                        <Textarea
                            id="cat-description"
                            value={form.data.description}
                            onChange={(e) =>
                                form.setData('description', e.target.value)
                            }
                        />
                        <InputError message={form.errors.description} />
                    </div>
                    <div className="grid gap-2">
                        <Label>Color</Label>
                        <div className="flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                onClick={() => form.setData('color', null)}
                                className={cn(
                                    'flex size-7 items-center justify-center rounded-full border text-xs text-muted-foreground transition-transform hover:scale-110',
                                    form.data.color === null &&
                                        'ring-2 ring-ring ring-offset-2 ring-offset-background',
                                )}
                                aria-label="No color"
                                title="No color"
                            >
                                —
                            </button>
                            {CATEGORY_COLORS.map((color) => (
                                <button
                                    key={color}
                                    type="button"
                                    onClick={() => form.setData('color', color)}
                                    className={cn(
                                        'size-7 rounded-full transition-transform hover:scale-110',
                                        CATEGORY_COLOR_CLASSES[color].dot,
                                        form.data.color === color &&
                                            'ring-2 ring-ring ring-offset-2 ring-offset-background',
                                    )}
                                    aria-label={color}
                                    title={color}
                                />
                            ))}
                        </div>
                        <InputError message={form.errors.color} />
                    </div>
                    <DialogFooter>
                        <Button type="submit" disabled={form.processing}>
                            {category ? 'Save changes' : 'Create category'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
