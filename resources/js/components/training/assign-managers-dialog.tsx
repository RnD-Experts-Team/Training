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
import { update } from '@/routes/trainees/managers';

type ManagerOption = { id: number; name: string };

export function AssignManagersDialog({
    traineeId,
    available,
    assignedIds,
    trigger,
}: {
    traineeId: number;
    available: ManagerOption[];
    assignedIds: number[];
    trigger: ReactNode;
}) {
    const [open, setOpen] = useState(false);
    const form = useForm<{ manager_ids: number[] }>({
        manager_ids: assignedIds,
    });

    function toggle(id: number, checked: boolean) {
        form.setData(
            'manager_ids',
            checked
                ? [...form.data.manager_ids, id]
                : form.data.manager_ids.filter((m) => m !== id),
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
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Assign managers</DialogTitle>
                    <DialogDescription>
                        Choose which managers can evaluate this trainee.
                    </DialogDescription>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    {available.length === 0 ? (
                        <p className="text-sm text-muted-foreground">
                            No managers in this store yet.
                        </p>
                    ) : (
                        <div className="space-y-2">
                            {available.map((manager) => (
                                <Label
                                    key={manager.id}
                                    className="flex items-center gap-2 rounded-md border p-2 font-normal"
                                >
                                    <Checkbox
                                        checked={form.data.manager_ids.includes(
                                            manager.id,
                                        )}
                                        onCheckedChange={(value) =>
                                            toggle(manager.id, value === true)
                                        }
                                    />
                                    {manager.name}
                                </Label>
                            ))}
                        </div>
                    )}
                    <DialogFooter>
                        <Button type="submit" disabled={form.processing}>
                            Save
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
