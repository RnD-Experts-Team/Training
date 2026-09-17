import { Link } from '@inertiajs/react';
import { TrendingUp } from 'lucide-react';
import { CompletionBar } from '@/components/training/completion-bar';
import { Button } from '@/components/ui/button';
import { index as developmentZoneIndex } from '@/routes/development-zone';
import { show as traineeShow } from '@/routes/trainees';
import type { DevelopmentZoneTrainee } from '@/types/training';

/**
 * Trainees currently flagged for extra coaching support, with progress
 * against their own curated plan. Clicking a name jumps straight into that
 * trainee's Development Plan tab; "View all" goes to the full Development
 * Zone page for browsing and managing the whole list.
 */
export function DevelopmentZonePanel({
    trainees,
}: {
    trainees: DevelopmentZoneTrainee[];
}) {
    return (
        <section className="surface-tray">
            <div className="surface-core overflow-hidden">
                <header className="flex items-center justify-between gap-4 border-b border-border/60 p-4">
                    <div className="flex items-center gap-2">
                        <TrendingUp className="size-4 text-muted-foreground" />
                        <h2 className="font-semibold tracking-tight">
                            Development Zone
                        </h2>
                    </div>
                    <Button variant="outline" size="sm" asChild>
                        <Link href={developmentZoneIndex().url}>View all</Link>
                    </Button>
                </header>

                {trainees.length === 0 ? (
                    <p className="p-8 text-center text-sm text-muted-foreground">
                        No one flagged for development right now.
                    </p>
                ) : (
                    <ul className="divide-y divide-border/60">
                        {trainees.slice(0, 6).map((trainee) => (
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
                )}
            </div>
        </section>
    );
}
