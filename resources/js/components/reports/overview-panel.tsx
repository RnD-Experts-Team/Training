import { Deferred } from '@inertiajs/react';
import {
    AlertTriangle,
    CheckCircle2,
    Gauge,
    GraduationCap,
    Users,
} from 'lucide-react';
import { StatCard } from '@/components/dashboard/stat-card';
import { DistributionChart } from '@/components/reports/distribution-chart';
import { ReportCard } from '@/components/reports/report-card';
import { TrendChart } from '@/components/reports/trend-chart';
import { Skeleton } from '@/components/ui/skeleton';
import type {
    DistributionBand,
    ReportOverview,
    TrendPoint,
} from '@/types/reports';

function ChartsSkeleton() {
    return (
        <div className="grid gap-4 lg:grid-cols-2">
            {[0, 1].map((i) => (
                <div key={i} className="surface-tray">
                    <div className="surface-core space-y-3 p-5">
                        <Skeleton className="h-4 w-32" />
                        <Skeleton className="h-[220px] w-full" />
                    </div>
                </div>
            ))}
        </div>
    );
}

export function OverviewPanel({
    overview,
    trend,
    distribution,
}: {
    overview: ReportOverview;
    trend?: TrendPoint[];
    distribution?: DistributionBand[];
}) {
    const score =
        overview.average_score !== null ? `${overview.average_score}%` : '—';

    return (
        <div className="space-y-6">
            <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                <StatCard
                    label="Trainees"
                    value={overview.trainees}
                    icon={Users}
                    hint={`${overview.evaluations_recorded} evaluations recorded`}
                />
                <StatCard
                    label="Completion"
                    value={`${overview.completion}%`}
                    icon={Gauge}
                    hint="of all training steps"
                />
                <StatCard
                    label="Avg score"
                    value={score}
                    icon={CheckCircle2}
                    hint="mean of scored items"
                />
                <StatCard
                    label="Fully trained"
                    value={overview.fully_trained}
                    icon={GraduationCap}
                    hint={`${overview.in_progress} in progress`}
                />
                <StatCard
                    label="At risk"
                    value={overview.at_risk}
                    icon={AlertTriangle}
                    hint={`${overview.not_started} not started`}
                />
            </div>

            <Deferred data={['trend', 'distribution']} fallback={<ChartsSkeleton />}>
                <div className="grid gap-4 lg:grid-cols-2">
                    <ReportCard
                        title="Completion trend"
                        description="Training steps completed per week"
                    >
                        <TrendChart data={trend ?? []} />
                    </ReportCard>
                    <ReportCard
                        title="Score distribution"
                        description="How evaluation scores are spread"
                    >
                        <DistributionChart data={distribution ?? []} />
                    </ReportCard>
                </div>
            </Deferred>
        </div>
    );
}
