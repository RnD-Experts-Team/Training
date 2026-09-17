import { useForm } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent, ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import { update } from '@/routes/trainees/development';
import type { DevelopmentPickerSection } from '@/types/training';

export function DevelopmentPlanPicker({
    traineeId,
    sections,
    selectedIds,
    trigger,
}: {
    traineeId: number;
    sections: DevelopmentPickerSection[];
    selectedIds: number[];
    trigger: ReactNode;
}) {
    const [open, setOpen] = useState(false);
    const form = useForm<{ item_ids: number[] }>({ item_ids: selectedIds });

    // Reset to the currently-saved selection each time the dialog opens, so
    // a cancelled edit doesn't leave stale picks for next time.
    function onOpenChange(next: boolean) {
        if (next) {
            form.setData('item_ids', selectedIds);
        }

        setOpen(next);
    }

    function toggle(itemId: number, checked: boolean) {
        form.setData(
            'item_ids',
            checked
                ? [...form.data.item_ids, itemId]
                : form.data.item_ids.filter((id) => id !== itemId),
        );
    }

    function submit(event: FormEvent) {
        event.preventDefault();
        form.put(update(traineeId).url, {
            preserveScroll: true,
            onSuccess: () => setOpen(false),
        });
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent className="flex max-h-[85vh] flex-col sm:max-w-2xl">
                <DialogHeader>
                    <DialogTitle className="text-xl">
                        Edit development plan
                    </DialogTitle>
                    <DialogDescription className="text-base">
                        Choose the existing stations, categories, and items this
                        person should focus on.
                    </DialogDescription>
                </DialogHeader>
                <form
                    onSubmit={submit}
                    className="flex min-h-0 flex-1 flex-col gap-4"
                >
                    <div className="min-h-0 flex-1 space-y-3 overflow-y-auto rounded-lg border bg-muted/20 p-3">
                        {sections.map((section) => {
                            const categories = section.categories.filter(
                                (category) => category.items.length > 0,
                            );

                            if (categories.length === 0) {
                                return null;
                            }

                            return (
                                <div
                                    key={section.id}
                                    className="space-y-3 rounded-lg border bg-card p-4"
                                >
                                    <p className="text-base font-semibold tracking-tight">
                                        {section.title}
                                    </p>
                                    {categories.map((category) => (
                                        <div
                                            key={category.id}
                                            className="space-y-2 border-l-2 border-border pl-3"
                                        >
                                            <p className="text-sm font-semibold text-foreground/80">
                                                {category.title}
                                            </p>
                                            <div className="grid gap-2 sm:grid-cols-2">
                                                {category.items.map((item) => (
                                                    <Label
                                                        key={item.id}
                                                        className="flex items-center gap-2.5 rounded-md border p-2.5 text-sm font-normal"
                                                    >
                                                        <Checkbox
                                                            checked={form.data.item_ids.includes(
                                                                item.id,
                                                            )}
                                                            onCheckedChange={(
                                                                value,
                                                            ) =>
                                                                toggle(
                                                                    item.id,
                                                                    value ===
                                                                        true,
                                                                )
                                                            }
                                                        />
                                                        {item.title}
                                                    </Label>
                                                ))}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            );
                        })}
                    </div>
                    <DialogFooter className="items-center sm:justify-between">
                        <p className="text-sm text-muted-foreground">
                            {form.data.item_ids.length} selected
                        </p>
                        <Button type="submit" disabled={form.processing}>
                            Save plan
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
