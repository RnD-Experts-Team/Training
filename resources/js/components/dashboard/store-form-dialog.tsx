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
import { store, update } from '@/routes/admin/stores';
import type { AdminStoreRow } from '@/types/training';

export function StoreFormDialog({
    storeRow,
    trigger,
}: {
    storeRow?: AdminStoreRow;
    trigger: ReactNode;
}) {
    const [open, setOpen] = useState(false);
    const form = useForm({
        name: storeRow?.name ?? '',
        address: storeRow?.address ?? '',
    });

    function submit(event: FormEvent) {
        event.preventDefault();
        const options = {
            preserveScroll: true,
            onSuccess: () => {
                setOpen(false);

                if (!storeRow) {
                    form.reset();
                }
            },
        };

        if (storeRow) {
            form.put(update(storeRow.id).url, options);
        } else {
            form.post(store().url, options);
        }
    }

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {storeRow ? 'Edit store' : 'New store'}
                    </DialogTitle>
                    <DialogDescription>
                        Stores group managers and trainees by location.
                    </DialogDescription>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    <div className="grid gap-2">
                        <Label htmlFor="store-name">Name</Label>
                        <Input
                            id="store-name"
                            value={form.data.name}
                            onChange={(e) =>
                                form.setData('name', e.target.value)
                            }
                            autoFocus
                            required
                        />
                        <InputError message={form.errors.name} />
                    </div>
                    <div className="grid gap-2">
                        <Label htmlFor="store-address">Address</Label>
                        <Input
                            id="store-address"
                            value={form.data.address}
                            onChange={(e) =>
                                form.setData('address', e.target.value)
                            }
                            placeholder="123 Main St, Springfield"
                        />
                        <InputError message={form.errors.address} />
                    </div>
                    <DialogFooter>
                        <Button type="submit" disabled={form.processing}>
                            {storeRow ? 'Save changes' : 'Create store'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
