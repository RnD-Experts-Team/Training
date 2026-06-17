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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { store, update } from '@/routes/training/items';
import { IMPORTANCE_OPTIONS } from '@/types/training';
import type { ChecklistItem, Importance } from '@/types/training';

export function ItemFormDialog({
    categoryId,
    item,
    parentId = null,
    trigger,
}: {
    categoryId: number;
    item?: ChecklistItem;
    parentId?: number | null;
    trigger: ReactNode;
}) {
    const [open, setOpen] = useState(false);
    const form = useForm({
        title: item?.title ?? '',
        content: item?.content ?? '',
        importance: (item?.importance ?? 'highly_important') as Importance,
        parent_id: item?.parent_id ?? parentId,
    });

    function submit(event: FormEvent) {
        event.preventDefault();
        const options = {
            preserveScroll: true,
            onSuccess: () => {
                setOpen(false);

                if (!item) {
                    form.reset('title', 'content');
                }
            },
        };

        if (item) {
            form.put(update(item.id).url, options);
        } else {
            form.post(store(categoryId).url, options);
        }
    }

    const isSubItem = (item?.parent_id ?? parentId) !== null;

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {item
                            ? 'Edit checklist item'
                            : isSubItem
                              ? 'New sub-item'
                              : 'New checklist item'}
                    </DialogTitle>
                    <DialogDescription>
                        A task a trainee must demonstrate, with an optional
                        expected answer.
                    </DialogDescription>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    <div className="grid gap-2">
                        <Label htmlFor="item-title">Task</Label>
                        <Input
                            id="item-title"
                            value={form.data.title}
                            onChange={(e) =>
                                form.setData('title', e.target.value)
                            }
                            autoFocus
                            required
                            placeholder="Demonstrate how to…"
                        />
                        <InputError message={form.errors.title} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="item-content">
                            Expected answer / notes
                        </Label>
                        <Textarea
                            id="item-content"
                            value={form.data.content}
                            onChange={(e) =>
                                form.setData('content', e.target.value)
                            }
                            className="min-h-24"
                        />
                        <InputError message={form.errors.content} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="item-importance">Importance</Label>
                        <Select
                            value={form.data.importance}
                            onValueChange={(value) =>
                                form.setData('importance', value as Importance)
                            }
                        >
                            <SelectTrigger
                                id="item-importance"
                                className="w-full"
                            >
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {IMPORTANCE_OPTIONS.map((option) => (
                                    <SelectItem
                                        key={option.value}
                                        value={option.value}
                                    >
                                        {option.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <InputError message={form.errors.importance} />
                    </div>

                    <DialogFooter>
                        <Button type="submit" disabled={form.processing}>
                            {item ? 'Save changes' : 'Create item'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
