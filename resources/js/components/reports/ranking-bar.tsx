import { cn } from '@/lib/utils';

/**
 * A single labelled horizontal bar used in ranking lists (stores, stations…).
 * `value` drives both the fill width (relative to `max`) and the readout.
 */
export function RankingBar({
    label,
    sublabel,
    value,
    max = 100,
    suffix = '%',
    emphasis = false,
}: {
    label: string;
    sublabel?: string;
    value: number | null;
    max?: number;
    suffix?: string;
    emphasis?: boolean;
}) {
    const pct = value !== null && max > 0 ? Math.round((value / max) * 100) : 0;

    return (
        <div className="flex items-center gap-3">
            <div className="w-32 shrink-0">
                <p className="truncate text-sm font-medium">{label}</p>
                {sublabel && (
                    <p className="truncate text-xs text-muted-foreground">
                        {sublabel}
                    </p>
                )}
            </div>
            <div className="relative h-6 flex-1 overflow-hidden rounded-md bg-muted">
                <div
                    className={cn(
                        'h-full rounded-md transition-[width]',
                        emphasis ? 'bg-emerald-500' : 'bg-primary/80',
                    )}
                    style={{ width: `${pct}%` }}
                />
            </div>
            <div className="w-12 shrink-0 text-right text-sm font-semibold tabular-nums">
                {value === null ? '—' : `${value}${suffix}`}
            </div>
        </div>
    );
}
