import { ChevronDown } from 'lucide-react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { cn } from '@/lib/utils';
import type { StoreOption } from '@/types/training';

/**
 * Multi-select for a manager's stores. Value is the list of selected store ids;
 * the trigger shows the chosen store names (or a placeholder).
 */
export function StoreMultiSelect({
    value,
    options,
    onChange,
    id,
    className,
    disabled = false,
}: {
    value: number[];
    options: StoreOption[];
    onChange: (ids: number[]) => void;
    id?: string;
    className?: string;
    disabled?: boolean;
}) {
    const selected = options.filter((store) => value.includes(store.id));

    const toggle = (storeId: number) => {
        onChange(
            value.includes(storeId)
                ? value.filter((id) => id !== storeId)
                : [...value, storeId],
        );
    };

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    id={id}
                    type="button"
                    variant="outline"
                    disabled={disabled}
                    className={cn(
                        'h-auto min-h-9 w-full justify-between gap-2 px-3 py-1.5 font-normal',
                        className,
                    )}
                >
                    <span className="flex min-w-0 flex-wrap gap-1">
                        {selected.length === 0 ? (
                            <span className="text-muted-foreground">
                                Select stores
                            </span>
                        ) : (
                            selected.map((store) => (
                                <Badge
                                    key={store.id}
                                    variant="secondary"
                                    className="max-w-full"
                                >
                                    <span className="truncate">
                                        {store.name}
                                    </span>
                                </Badge>
                            ))
                        )}
                    </span>
                    <ChevronDown className="size-4 shrink-0 text-muted-foreground" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent
                align="start"
                className="max-h-64 w-(--radix-dropdown-menu-trigger-width) overflow-y-auto"
            >
                {options.length === 0 ? (
                    <p className="px-2 py-1.5 text-sm text-muted-foreground">
                        No stores yet.
                    </p>
                ) : (
                    options.map((store) => (
                        <DropdownMenuCheckboxItem
                            key={store.id}
                            checked={value.includes(store.id)}
                            onCheckedChange={() => toggle(store.id)}
                            onSelect={(event) => event.preventDefault()}
                        >
                            {store.name}
                        </DropdownMenuCheckboxItem>
                    ))
                )}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
