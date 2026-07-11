import {
    Bar,
    BarChart,
    CartesianGrid,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import { ChartTooltip } from '@/components/reports/chart-tooltip';
import type { DistributionBand } from '@/types/reports';

/**
 * Histogram of evaluation scores across 20-point bands.
 */
export function DistributionChart({ data }: { data: DistributionBand[] }) {
    return (
        <ResponsiveContainer width="100%" height={220}>
            <BarChart data={data} margin={{ top: 8, right: 8, bottom: 0, left: -18 }}>
                <CartesianGrid
                    vertical={false}
                    stroke="var(--border)"
                    strokeDasharray="3 3"
                />
                <XAxis
                    dataKey="band"
                    tick={{ fontSize: 11, fill: 'var(--muted-foreground)' }}
                    tickLine={false}
                    axisLine={false}
                />
                <YAxis
                    allowDecimals={false}
                    width={34}
                    tick={{ fontSize: 11, fill: 'var(--muted-foreground)' }}
                    tickLine={false}
                    axisLine={false}
                />
                <Tooltip
                    cursor={{ fill: 'var(--muted)', opacity: 0.4 }}
                    content={<ChartTooltip unit=" scores" />}
                />
                <Bar
                    dataKey="count"
                    fill="var(--chart-1)"
                    radius={[6, 6, 0, 0]}
                    maxBarSize={56}
                />
            </BarChart>
        </ResponsiveContainer>
    );
}
