import { Deferred } from '@inertiajs/react';
import { RankingBar } from '@/components/reports/ranking-bar';
import { ReportCard, ReportEmpty } from '@/components/reports/report-card';
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
    ManagerActivityRow,
    StorePerformanceRow,
} from '@/types/reports';

function PanelSkeleton() {
    return (
        <div className="grid gap-4 lg:grid-cols-2">
            {[0, 1].map((i) => (
                <div key={i} className="surface-tray">
                    <div className="surface-core space-y-3 p-5">
                        <Skeleton className="h-4 w-40" />
                        <Skeleton className="h-40 w-full" />
                    </div>
                </div>
            ))}
        </div>
    );
}

export function StoresPanel({
    stores,
    managers,
    isSuperAdmin,
}: {
    stores?: StorePerformanceRow[];
    managers?: ManagerActivityRow[];
    isSuperAdmin: boolean;
}) {
    return (
        <Deferred
            data={['storePerformance', 'managerActivity']}
            fallback={<PanelSkeleton />}
        >
            <div className="grid gap-4 lg:grid-cols-2">
                <ReportCard
                    title="Store performance"
                    description="Completion rate, ranked"
                >
                    {!stores || stores.length === 0 ? (
                        <ReportEmpty message="No store data yet." />
                    ) : (
                        <div className="space-y-3">
                            {stores.map((store) => (
                                <RankingBar
                                    key={store.id}
                                    label={store.name}
                                    sublabel={`${store.trainees} trainees · ${
                                        store.average_score ?? '—'
                                    }% avg`}
                                    value={store.completion}
                                />
                            ))}
                        </div>
                    )}
                </ReportCard>

                <ReportCard
                    title={isSuperAdmin ? 'Trainer activity' : 'My activity'}
                    description="Evaluations recorded per trainer"
                >
                    {!managers || managers.length === 0 ? (
                        <ReportEmpty message="No trainer activity yet." />
                    ) : (
                        <div className="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Trainer</TableHead>
                                        <TableHead className="text-right">
                                            Trainees
                                        </TableHead>
                                        <TableHead className="text-right">
                                            Evals
                                        </TableHead>
                                        <TableHead className="text-right">
                                            Avg
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {managers.map((manager) => (
                                        <TableRow key={manager.id}>
                                            <TableCell>
                                                <p className="font-medium">
                                                    {manager.name}
                                                </p>
                                                {manager.store && (
                                                    <p className="text-xs text-muted-foreground">
                                                        {manager.store}
                                                    </p>
                                                )}
                                            </TableCell>
                                            <TableCell className="text-right tabular-nums">
                                                {manager.assigned_trainees}
                                            </TableCell>
                                            <TableCell className="text-right tabular-nums">
                                                {manager.evaluations_recorded}
                                            </TableCell>
                                            <TableCell className="text-right tabular-nums">
                                                {manager.average_score ?? '—'}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    )}
                </ReportCard>
            </div>
        </Deferred>
    );
}
