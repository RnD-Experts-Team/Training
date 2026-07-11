type TooltipEntry = { value: number | string; name?: string };

/**
 * Theme-aware replacement for Recharts' default (white) tooltip.
 */
export function ChartTooltip({
    active,
    payload,
    label,
    unit = '',
}: {
    active?: boolean;
    payload?: TooltipEntry[];
    label?: string;
    unit?: string;
}) {
    if (!active || !payload || payload.length === 0) {
        return null;
    }

    return (
        <div className="rounded-md border bg-popover px-2.5 py-1.5 text-xs shadow-md">
            {label && (
                <p className="font-medium text-popover-foreground">{label}</p>
            )}
            <p className="text-muted-foreground tabular-nums">
                {payload[0].value}
                {unit}
            </p>
        </div>
    );
}
