import { Head, Link, router, usePage } from '@inertiajs/react';
import { TrendingUp } from 'lucide-react';
import Heading from '@/components/heading';
import { CompletionBar } from '@/components/training/completion-bar';
import { Card } from '@/components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useStoreFilter, useSyncStoreFilter } from '@/hooks/use-store-filter';
import { index } from '@/routes/development-zone';
import { show as traineeShow } from '@/routes/trainees';
import type { BreadcrumbItem } from '@/types';
import type { DevelopmentZoneTrainee, StoreOption } from '@/types/training';

export default function DevelopmentZoneIndex() {
    const { trainees, stores, filters, canChooseStore } = usePage<{
        trainees: DevelopmentZoneTrainee[];
        stores: StoreOption[];
        filters: { store: number | null };
        canChooseStore: boolean;
    }>().props;

    const { setSelectedStoreId } = useStoreFilter();
    useSyncStoreFilter(filters.store);

    function filterStore(value: string) {
        const storeId = value === 'all' ? null : Number(value);
        setSelectedStoreId(storeId);
        router.get(index().url, storeId ? { store: storeId } : {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }

    return (
        <>
            <Head title="Development Zone" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-start justify-between gap-3">
                    <Heading
                        title="Development Zone"
                        description="Trainees flagged for extra coaching support, and progress on their individualized plans."
                    />
                    {canChooseStore && stores.length > 0 && (
                        <Select
                            value={
                                filters.store ? String(filters.store) : 'all'
                            }
                            onValueChange={filterStore}
                        >
                            <SelectTrigger className="w-40">
                                <SelectValue placeholder="All stores" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All stores</SelectItem>
                                {stores.map((store) => (
                                    <SelectItem
                                        key={store.id}
                                        value={String(store.id)}
                                    >
                                        {store.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    )}
                </div>

                {trainees.length === 0 ? (
                    <Card className="flex flex-col items-center justify-center gap-3 border-dashed p-12 text-center">
                        <TrendingUp className="size-10 text-muted-foreground" />
                        <p className="text-sm text-muted-foreground">
                            No one is flagged for development right now. Flag a
                            trainee from their profile to build them a focused
                            plan.
                        </p>
                    </Card>
                ) : (
                    <section className="surface-tray">
                        <ul className="surface-core divide-y divide-border/60 overflow-hidden">
                            {trainees.map((trainee) => (
                                <li key={trainee.id}>
                                    <Link
                                        href={`${traineeShow(trainee.id).url}?view=development`}
                                        className="flex flex-col gap-3 p-4 transition-colors hover:bg-muted/50 sm:flex-row sm:items-center sm:gap-4"
                                    >
                                        <div className="min-w-0 sm:flex-1">
                                            <p className="truncate font-medium">
                                                {trainee.name}
                                            </p>
                                            <p className="truncate text-xs text-muted-foreground">
                                                {[
                                                    trainee.position,
                                                    trainee.store.name,
                                                ]
                                                    .filter(Boolean)
                                                    .join(' · ') || '—'}
                                            </p>
                                        </div>
                                        <div className="flex items-center gap-3 sm:w-56">
                                            <div className="w-full">
                                                <CompletionBar
                                                    completed={
                                                        trainee.stats.completed
                                                    }
                                                    total={trainee.stats.total}
                                                />
                                            </div>
                                            <span className="shrink-0 text-xs text-muted-foreground tabular-nums">
                                                {trainee.stats.completed}/
                                                {trainee.stats.total}
                                            </span>
                                        </div>
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </section>
                )}
            </div>
        </>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Development Zone', href: index() },
];

DevelopmentZoneIndex.layout = { breadcrumbs };
