import type { ReactNode } from 'react';
import { cn } from '@/lib/utils';

export function HeroTile({
    eyebrow,
    title,
    subtitle,
    metricValue,
    metricLabel,
    action,
    className,
}: {
    eyebrow: string;
    title: string;
    subtitle?: string;
    metricValue: number | string;
    metricLabel: string;
    action?: ReactNode;
    className?: string;
}) {
    return (
        <div
            className={cn(
                'animate-rise relative isolate flex flex-col overflow-hidden rounded-2xl bg-gradient-to-br from-primary to-primary/75 p-6 text-primary-foreground shadow-sm',
                className,
            )}
        >
            {/* Ambient light blooms for depth. */}
            <div className="pointer-events-none absolute -top-12 -right-10 size-48 rounded-full bg-white/20 blur-3xl" />
            <div className="pointer-events-none absolute -bottom-16 -left-12 size-56 rounded-full bg-black/10 blur-3xl" />

            <div className="relative flex h-full flex-col">
                <p className="text-xs font-medium tracking-[0.22em] text-primary-foreground/70 uppercase">
                    {eyebrow}
                </p>
                <h1 className="mt-2 text-2xl font-semibold tracking-tight md:text-3xl">
                    {title}
                </h1>
                {subtitle && (
                    <p className="mt-1.5 max-w-md text-sm text-primary-foreground/80">
                        {subtitle}
                    </p>
                )}

                <div className="mt-auto flex flex-wrap items-end justify-between gap-4 pt-8">
                    <div>
                        <p className="text-4xl leading-none font-semibold tabular-nums md:text-5xl">
                            {metricValue}
                        </p>
                        <p className="mt-1.5 text-sm text-primary-foreground/80">
                            {metricLabel}
                        </p>
                    </div>
                    {action}
                </div>
            </div>
        </div>
    );
}
