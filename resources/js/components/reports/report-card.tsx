import type { ReactNode } from 'react';
import { Card } from '@/components/ui/card';
import { cn } from '@/lib/utils';

/**
 * Titled surface used across the report panels.
 */
export function ReportCard({
    title,
    description,
    action,
    className,
    children,
}: {
    title: string;
    description?: string;
    action?: ReactNode;
    className?: string;
    children: ReactNode;
}) {
    return (
        <Card className={cn('gap-4 p-5', className)}>
            <div className="flex flex-wrap items-start justify-between gap-2">
                <div className="min-w-0">
                    <h3 className="font-semibold tracking-tight">{title}</h3>
                    {description && (
                        <p className="text-sm text-muted-foreground">
                            {description}
                        </p>
                    )}
                </div>
                {action}
            </div>
            {children}
        </Card>
    );
}

export function ReportEmpty({ message }: { message: string }) {
    return (
        <p className="py-8 text-center text-sm text-muted-foreground">
            {message}
        </p>
    );
}
