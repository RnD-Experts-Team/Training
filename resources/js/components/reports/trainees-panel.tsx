import { Deferred } from '@inertiajs/react';
import { ReportCard, ReportEmpty } from '@/components/reports/report-card';
import { StatusBadge } from '@/components/reports/status-badge';
import { CompletionBar } from '@/components/training/completion-bar';
import { RatingMeter } from '@/components/training/rating-meter';
import { Badge } from '@/components/ui/badge';
import { Skeleton } from '@/components/ui/skeleton';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type {
    TraineeStatusReport,
    TraineeStatusValue,
} from '@/types/reports';

const ORDER: Record<TraineeStatusValue, number> = {
    at_risk: 0,
    in_progress: 1,
    not_started: 2,
    completed: 3,
};

function PanelSkeleton() {
    return (
        <div className="surface-tray">
            <div className="surface-core space-y-3 p-5">
                <Skeleton className="h-4 w-40" />
                <Skeleton className="h-64 w-full" />
            </div>
        </div>
    );
}

export function TraineesPanel({ report }: { report?: TraineeStatusReport }) {
    const rows = [...(report?.rows ?? [])].sort(
        (a, b) => ORDER[a.status] - ORDER[b.status],
    );

    const onboarding =
        report?.onboarding_days_avg != null
            ? `${report.onboarding_days_avg} days`
            : '—';

    return (
        <Deferred data="traineeStatus" fallback={<PanelSkeleton />}>
            <ReportCard
                title="Trainee status"
                description="At-risk trainees are listed first"
                action={
                    <Badge variant="secondary" className="shrink-0">
                        Avg onboarding: {onboarding}
                    </Badge>
                }
            >
                {rows.length === 0 ? (
                    <ReportEmpty message="No trainees in scope yet." />
                ) : (
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Trainee</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="w-40">
                                        Completion
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Score
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Last activity
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {rows.map((row) => (
                                    <TableRow key={row.id}>
                                        <TableCell>
                                            <p className="font-medium">
                                                {row.name}
                                            </p>
                                            <p className="text-xs text-muted-foreground">
                                                {[row.position, row.store]
                                                    .filter(Boolean)
                                                    .join(' · ')}
                                            </p>
                                        </TableCell>
                                        <TableCell>
                                            <StatusBadge status={row.status} />
                                        </TableCell>
                                        <TableCell>
                                            <CompletionBar
                                                completed={row.completion}
                                                total={100}
                                            />
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <RatingMeter
                                                value={row.average_score}
                                                className="justify-end"
                                            />
                                        </TableCell>
                                        <TableCell className="text-right text-sm text-muted-foreground tabular-nums">
                                            {row.last_activity ?? '—'}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>
                )}
            </ReportCard>
        </Deferred>
    );
}
