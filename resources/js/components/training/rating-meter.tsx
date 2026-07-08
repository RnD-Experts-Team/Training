import { cn } from '@/lib/utils';

/**
 * Read-only 0–100 score display: a small meter + percentage. Shows "—" when
 * there is no score yet.
 */
export function RatingMeter({
    value,
    size = 'sm',
    className,
}: {
    value: number | null;
    size?: 'sm' | 'md';
    className?: string;
}) {
    if (value === null) {
        return (
            <span className={cn('text-sm text-muted-foreground', className)}>
                —
            </span>
        );
    }

    const percent = Math.round(value);

    return (
        <div className={cn('flex items-center gap-2', className)}>
            <div
                className={cn(
                    'overflow-hidden rounded-full bg-muted',
                    size === 'md' ? 'h-2 w-24' : 'h-1.5 w-16',
                )}
            >
                <div
                    className={cn(
                        'h-full rounded-full',
                        percent >= 80 ? 'bg-emerald-500' : 'bg-primary',
                    )}
                    style={{ width: `${percent}%` }}
                />
            </div>
            <span
                className={cn(
                    'font-semibold tabular-nums',
                    size === 'md' ? 'text-base' : 'text-sm',
                )}
            >
                {percent}%
            </span>
        </div>
    );
}
