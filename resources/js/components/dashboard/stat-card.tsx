import type { LucideIcon } from 'lucide-react';

export function StatCard({
    label,
    value,
    icon: Icon,
    hint,
}: {
    label: string;
    value: number | string;
    icon: LucideIcon;
    hint?: string;
}) {
    return (
        <div className="surface-tray animate-rise h-full">
            <div className="surface-core group flex h-full items-start justify-between gap-3 p-4 transition-colors hover:border-primary/30">
                <div className="flex h-full flex-col">
                    <p className="text-xs font-medium tracking-[0.12em] text-muted-foreground uppercase">
                        {label}
                    </p>
                    <p className="mt-auto pt-3 text-3xl font-semibold tracking-tight tabular-nums">
                        {value}
                    </p>
                    {hint && (
                        <p className="mt-1 text-xs text-muted-foreground">
                            {hint}
                        </p>
                    )}
                </div>
                <div className="flex size-10 items-center justify-center rounded-full bg-primary/10 text-primary ring-1 ring-primary/15 transition-transform group-hover:scale-105">
                    <Icon className="size-5" strokeWidth={1.5} />
                </div>
            </div>
        </div>
    );
}
