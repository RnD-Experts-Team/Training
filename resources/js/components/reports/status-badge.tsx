import { Badge } from '@/components/ui/badge';
import { cn } from '@/lib/utils';
import type { TraineeStatusValue } from '@/types/reports';

const CONFIG: Record<TraineeStatusValue, { label: string; className: string }> =
    {
        completed: {
            label: 'Completed',
            className:
                'border-emerald-500/30 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300',
        },
        in_progress: {
            label: 'In progress',
            className:
                'border-primary/30 bg-primary/10 text-primary dark:text-primary',
        },
        at_risk: {
            label: 'At risk',
            className:
                'border-destructive/30 bg-destructive/10 text-destructive',
        },
        not_started: {
            label: 'Not started',
            className: 'text-muted-foreground',
        },
    };

export function StatusBadge({ status }: { status: TraineeStatusValue }) {
    const config = CONFIG[status];

    return (
        <Badge variant="outline" className={cn('font-medium', config.className)}>
            {config.label}
        </Badge>
    );
}
