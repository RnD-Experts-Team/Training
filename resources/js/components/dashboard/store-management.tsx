import { router } from '@inertiajs/react';
import { Pencil, Plus, Store, Trash2 } from 'lucide-react';
import { StoreFormDialog } from '@/components/dashboard/store-form-dialog';
import { ConfirmDeleteDialog } from '@/components/training/confirm-delete-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { destroy } from '@/routes/admin/stores';
import type { AdminStoreRow } from '@/types/training';

export function StoreManagement({ stores }: { stores: AdminStoreRow[] }) {
    return (
        <section className="surface-tray min-w-0">
            <div className="surface-core overflow-hidden">
                <header className="flex items-center justify-between gap-3 border-b border-border/60 p-4">
                    <div className="min-w-0">
                        <h2 className="font-semibold tracking-tight">Stores</h2>
                        <p className="truncate text-sm text-muted-foreground">
                            {stores.length} location
                            {stores.length === 1 ? '' : 's'}
                        </p>
                    </div>
                    <StoreFormDialog
                        trigger={
                            <Button size="sm" className="shrink-0">
                                <Plus className="size-4" />
                                <span className="hidden sm:inline">
                                    Add store
                                </span>
                            </Button>
                        }
                    />
                </header>

                <ul className="divide-y divide-border/60">
                    {stores.map((store) => (
                        <li
                            key={store.id}
                            className="flex items-start gap-3 p-4"
                        >
                            <div className="flex size-9 shrink-0 items-center justify-center rounded-full bg-muted text-muted-foreground">
                                <Store className="size-4" strokeWidth={1.5} />
                            </div>
                            <div className="min-w-0 flex-1">
                                <p className="truncate font-medium">
                                    {store.name}
                                </p>
                                {store.address && (
                                    <p className="truncate text-xs text-muted-foreground">
                                        {store.address}
                                    </p>
                                )}
                                <div className="mt-1.5 flex flex-wrap gap-1.5">
                                    <Badge variant="secondary">
                                        {store.managers_count}{' '}
                                        {store.managers_count === 1
                                            ? 'manager'
                                            : 'managers'}
                                    </Badge>
                                    <Badge variant="secondary">
                                        {store.trainees_count}{' '}
                                        {store.trainees_count === 1
                                            ? 'trainee'
                                            : 'trainees'}
                                    </Badge>
                                </div>
                            </div>
                            <div className="flex shrink-0 items-center gap-1">
                                <StoreFormDialog
                                    storeRow={store}
                                    trigger={
                                        <Button variant="ghost" size="icon">
                                            <Pencil className="size-4" />
                                        </Button>
                                    }
                                />
                                <ConfirmDeleteDialog
                                    title="Remove store?"
                                    description={
                                        store.trainees_count > 0
                                            ? 'This store still has trainees. Move or remove them first — the request will be blocked otherwise.'
                                            : `This permanently deletes ${store.name}.`
                                    }
                                    onConfirm={(close) =>
                                        router.delete(destroy(store.id).url, {
                                            preserveScroll: true,
                                            onSuccess: close,
                                            onError: close,
                                        })
                                    }
                                    trigger={
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            className="text-muted-foreground hover:text-destructive"
                                        >
                                            <Trash2 className="size-4" />
                                        </Button>
                                    }
                                />
                            </div>
                        </li>
                    ))}
                </ul>
            </div>
        </section>
    );
}
