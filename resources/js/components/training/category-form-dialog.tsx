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
    const form = useForm({
        title: category?.title ?? '',
        description: category?.description ?? '',
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
