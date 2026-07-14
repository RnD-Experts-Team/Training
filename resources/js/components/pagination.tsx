import { router } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { Button } from '@/components/ui/button';
import type { Paginated } from '@/types/pagination';

/**
 * Prev/next pager for an Inertia `LengthAwarePaginator`. `only` names the prop
 * to reload so paginating one list doesn't disturb others on the page.
 */
export function Paginator({
    paginator,
    only,
    label = 'items',
}: {
    paginator: Pick<
        Paginated<unknown>,
        | 'current_page'
        | 'last_page'
        | 'total'
        | 'from'
        | 'to'
        | 'prev_page_url'
        | 'next_page_url'
    >;
    only: string;
    label?: string;
}) {
    if (paginator.total === 0) {
        return null;
    }

    const go = (url: string | null) => {
        if (url) {
            router.get(
                url,
                {},
                { preserveScroll: true, preserveState: true, only: [only] },
            );
        }
    };

    return (
        <nav className="flex flex-wrap items-center justify-between gap-3">
            <p className="text-xs text-muted-foreground tabular-nums">
                {paginator.from ?? 0}–{paginator.to ?? 0} of {paginator.total}{' '}
                {label}
            </p>
            <div className="flex items-center gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    disabled={!paginator.prev_page_url}
                    onClick={() => go(paginator.prev_page_url)}
                >
                    <ChevronLeft className="size-4" />
                    <span className="hidden sm:inline">Previous</span>
                </Button>
                <span className="text-xs text-muted-foreground tabular-nums">
                    {paginator.current_page} / {paginator.last_page}
                </span>
                <Button
                    variant="outline"
                    size="sm"
                    disabled={!paginator.next_page_url}
                    onClick={() => go(paginator.next_page_url)}
                >
                    <span className="hidden sm:inline">Next</span>
                    <ChevronRight className="size-4" />
                </Button>
            </div>
        </nav>
    );
}
