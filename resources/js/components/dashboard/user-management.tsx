import { router } from '@inertiajs/react';
import { Trash2, UserPlus } from 'lucide-react';
import { InviteUserDialog } from '@/components/dashboard/invite-user-dialog';
import { ConfirmDeleteDialog } from '@/components/training/confirm-delete-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { destroy, update } from '@/routes/admin/users';
import type {
    AdminUserRow,
    RoleOption,
    RoleValue,
    StoreOption,
} from '@/types/training';

export function UserManagement({
    users,
    stores,
    roleOptions,
    currentUserId,
}: {
    users: AdminUserRow[];
    stores: StoreOption[];
    roleOptions: RoleOption[];
    currentUserId: number;
}) {
    function save(user: AdminUserRow, role: RoleValue, storeId: number | null) {
        router.patch(
            update(user.id).url,
            { role, store_id: role === 'manager' ? storeId : null },
            { preserveScroll: true },
        );
    }

    function changeRole(user: AdminUserRow, role: RoleValue) {
        const storeId =
            role === 'manager'
                ? (user.store?.id ?? stores[0]?.id ?? null)
                : null;
        save(user, role, storeId);
    }

    return (
        <section className="surface-tray min-w-0">
            <div className="surface-core overflow-hidden">
                <header className="flex items-center justify-between gap-3 border-b border-border/60 p-4">
                    <div className="min-w-0">
                        <h2 className="font-semibold tracking-tight">Team</h2>
                        <p className="truncate text-sm text-muted-foreground">
                            {users.length} member{users.length === 1 ? '' : 's'}{' '}
                            · roles &amp; store access
                        </p>
                    </div>
                    <InviteUserDialog
                        roleOptions={roleOptions}
                        stores={stores}
                        trigger={
                            <Button size="sm" className="shrink-0">
                                <UserPlus className="size-4" />
                                <span className="hidden sm:inline">Invite</span>
                            </Button>
                        }
                    />
                </header>

                <ul className="divide-y divide-border/60">
                    {users.map((user) => {
                        const isSelf = user.id === currentUserId;

                        return (
                            <li
                                key={user.id}
                                className="grid gap-3 p-4 lg:grid-cols-[minmax(0,1fr)_9rem_11rem_auto] lg:items-center lg:gap-4"
                            >
                                <div className="min-w-0">
                                    <div className="flex items-center gap-2">
                                        <span className="truncate font-medium">
                                            {user.name}
                                        </span>
                                        {isSelf && (
                                            <Badge variant="secondary">
                                                You
                                            </Badge>
                                        )}
                                    </div>
                                    <span className="block truncate text-xs text-muted-foreground">
                                        {user.email}
                                    </span>
                                </div>

                                <div className="grid grid-cols-2 gap-2 lg:contents">
                                    <Select
                                        value={user.role}
                                        disabled={isSelf}
                                        onValueChange={(value) =>
                                            changeRole(user, value as RoleValue)
                                        }
                                    >
                                        <SelectTrigger
                                            size="sm"
                                            className="w-full"
                                        >
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {roleOptions.map((option) => (
                                                <SelectItem
                                                    key={option.value}
                                                    value={option.value}
                                                >
                                                    {option.label}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>

                                    {user.role === 'manager' ? (
                                        <Select
                                            value={
                                                user.store
                                                    ? String(user.store.id)
                                                    : ''
                                            }
                                            onValueChange={(value) =>
                                                save(
                                                    user,
                                                    'manager',
                                                    Number(value),
                                                )
                                            }
                                        >
                                            <SelectTrigger
                                                size="sm"
                                                className="w-full"
                                            >
                                                <SelectValue placeholder="Assign store" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {stores.map((s) => (
                                                    <SelectItem
                                                        key={s.id}
                                                        value={String(s.id)}
                                                    >
                                                        {s.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    ) : (
                                        <span className="flex items-center text-sm text-muted-foreground">
                                            All stores
                                        </span>
                                    )}
                                </div>

                                <div className="flex justify-end">
                                    {!isSelf && (
                                        <ConfirmDeleteDialog
                                            title="Remove user?"
                                            description={`This permanently deletes ${user.name}'s account.`}
                                            onConfirm={(close) =>
                                                router.delete(
                                                    destroy(user.id).url,
                                                    {
                                                        preserveScroll: true,
                                                        onSuccess: close,
                                                    },
                                                )
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
                                    )}
                                </div>
                            </li>
                        );
                    })}
                </ul>
            </div>
        </section>
    );
}
