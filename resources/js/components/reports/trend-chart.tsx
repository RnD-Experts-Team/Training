import {
    Area,
    AreaChart,
    CartesianGrid,
    ResponsiveContainer,
    Tooltip,
    XAxis,
    YAxis,
} from 'recharts';
import { ChartTooltip } from '@/components/reports/chart-tooltip';
import type { TrendPoint } from '@/types/reports';

/**
 * Weekly completed-evaluations trend as a filled area chart.
 */
export function TrendChart({ data }: { data: TrendPoint[] }) {
    return (
        <ResponsiveContainer width="100%" height={220}>
            <AreaChart data={data} margin={{ top: 8, right: 8, bottom: 0, left: -18 }}>
                <defs>
                    <linearGradient id="trend-fill" x1="0" y1="0" x2="0" y2="1">
                        <stop
                            offset="0%"
                            stopColor="var(--chart-1)"
                            stopOpacity={0.35}
                        />
                        <stop
                            offset="100%"
                            stopColor="var(--chart-1)"
                            stopOpacity={0}
                        />
                    </linearGradient>
                </defs>
                <CartesianGrid
                    vertical={false}
                    stroke="var(--border)"
                    strokeDasharray="3 3"
                />
                <XAxis
                    dataKey="label"
                    tick={{ fontSize: 11, fill: 'var(--muted-foreground)' }}
                    tickLine={false}
                    axisLine={false}
                    interval="preserveStartEnd"
                    minTickGap={16}
                />
                <YAxis
                    allowDecimals={false}
                    width={34}
                    tick={{ fontSize: 11, fill: 'var(--muted-foreground)' }}
                    tickLine={false}
                    axisLine={false}
                />
                <Tooltip
                    cursor={{ stroke: 'var(--border)' }}
                    content={<ChartTooltip unit=" done" />}
                />
                <Area
                    type="monotone"
                    dataKey="count"
                    stroke="var(--chart-1)"
                    strokeWidth={2}
                    fill="url(#trend-fill)"
                />
            </AreaChart>
        </ResponsiveContainer>
    );
}
