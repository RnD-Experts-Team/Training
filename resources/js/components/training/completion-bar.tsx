import { cn } from '@/lib/utils';

export function CompletionBar({
    completed,
    total,
    className,
}: {
    completed: number;
    total: number;
    className?: string;
}) {
    const percent = total > 0 ? Math.round((completed / total) * 100) : 0;

    return (
        <div className={cn('flex items-center gap-2', className)}>
            <div className="h-2 w-full overflow-hidden rounded-full bg-muted">
                <div
                    className={cn(
                        'h-full rounded-full transition-all',
                        percent === 100 ? 'bg-emerald-500' : 'bg-primary',
                    )}
                    style={{ width: `${percent}%` }}
                />
            </div>
            <span className="w-10 shrink-0 text-right text-xs font-medium text-muted-foreground tabular-nums">
                {percent}%
            </span>
        </div>
    );
}
