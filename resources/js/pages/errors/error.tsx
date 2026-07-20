import { Head, Link, usePage } from '@inertiajs/react';
import {
    ArrowLeft,
    Clock,
    FileQuestion,
    Gauge,
    LayoutGrid,
    RotateCcw,
    ServerCrash,
    ShieldX,
    Wrench
    
} from 'lucide-react';
import type {LucideIcon} from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';
import { Button } from '@/components/ui/button';
import { dashboard } from '@/routes';

type ErrorMeta = {
    title: string;
    description: string;
    icon: LucideIcon;
};

const STATUSES: Record<number, ErrorMeta> = {
    403: {
        title: 'Access denied',
        description: "You don't have permission to view this page.",
        icon: ShieldX,
    },
    404: {
        title: 'Page not found',
        description: "The page you're looking for doesn't exist or has moved.",
        icon: FileQuestion,
    },
    413: {
        title: 'File too large',
        description:
            'That upload is bigger than the server accepts. Try a smaller file, or compress the video first.',
        icon: FileQuestion,
    },
    419: {
        title: 'Session expired',
        description: 'Your session timed out for security. Please refresh and try again.',
        icon: Clock,
    },
    429: {
        title: 'Too many requests',
        description: 'You’ve done that a few too many times. Wait a moment, then try again.',
        icon: Gauge,
    },
    500: {
        title: 'Something went wrong',
        description: 'An unexpected error occurred on our end. Please try again shortly.',
        icon: ServerCrash,
    },
    503: {
        title: 'Down for maintenance',
        description: 'We’re making things better. Please check back in a few minutes.',
        icon: Wrench,
    },
};

const FALLBACK: ErrorMeta = {
    title: 'Something went wrong',
    description: 'An unexpected error occurred. Please try again.',
    icon: ServerCrash,
};

export default function ErrorPage() {
    const { status } = usePage<{ status: number }>().props;
    const meta = STATUSES[status] ?? FALLBACK;
    const Icon = meta.icon;

    return (
        <>
            <Head title={`${status} · ${meta.title}`} />

            <div className="ambient-grid flex min-h-svh flex-col items-center justify-center gap-8 bg-background px-6 py-12 text-center">
                <Link
                    href={dashboard().url}
                    className="flex items-center gap-2 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground"
                >
                    <AppLogoIcon className="size-6" />
                    Training Project
                </Link>

                <div className="flex flex-col items-center gap-5">
                    <div className="flex size-16 items-center justify-center rounded-2xl bg-primary/10 text-primary ring-1 ring-primary/15">
                        <Icon className="size-8" strokeWidth={1.5} />
                    </div>

                    <p className="text-6xl font-semibold tracking-tight tabular-nums text-muted-foreground/60">
                        {status}
                    </p>

                    <div className="space-y-2">
                        <h1 className="text-2xl font-semibold tracking-tight">
                            {meta.title}
                        </h1>
                        <p className="mx-auto max-w-md text-sm text-muted-foreground">
                            {meta.description}
                        </p>
                    </div>
                </div>

                <div className="flex flex-wrap items-center justify-center gap-3">
                    <Button
                        variant="outline"
                        onClick={() => window.history.back()}
                    >
                        <ArrowLeft className="size-4" /> Go back
                    </Button>
                    {status === 419 ? (
                        <Button onClick={() => window.location.reload()}>
                            <RotateCcw className="size-4" /> Refresh
                        </Button>
                    ) : (
                        <Button asChild>
                            <Link href={dashboard().url}>
                                <LayoutGrid className="size-4" /> Dashboard
                            </Link>
                        </Button>
                    )}
                </div>
            </div>
        </>
    );
}
