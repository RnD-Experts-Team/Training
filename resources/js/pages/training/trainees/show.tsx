import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft, ChevronDown, Pencil, Trash2, Users } from 'lucide-react';
import { AssignManagersDialog } from '@/components/training/assign-managers-dialog';
import { CompletionBar } from '@/components/training/completion-bar';
import { ConfirmDeleteDialog } from '@/components/training/confirm-delete-dialog';
import { EvaluationItem } from '@/components/training/evaluation-item';
import { StarRating } from '@/components/training/star-rating';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { destroy, edit, index } from '@/routes/trainees';
import type { BreadcrumbItem } from '@/types';
import type {
    EvaluationItem as EvaluationItemType,
    TraineeDetail,
    TraineeProgressData,
} from '@/types/training';

function leafCount(items: EvaluationItemType[]): {
    done: number;
    total: number;
} {
    return items.reduce(
        (acc, item) => {
            if (item.children.length > 0) {
                const child = leafCount(item.children);

                return {
                    done: acc.done + child.done,
                    total: acc.total + child.total,
                };
            }

            return {
                done: acc.done + (item.evaluation?.completed ? 1 : 0),
                total: acc.total + 1,
            };
        },
        { done: 0, total: 0 },
    );
}

export default function TraineeShow() {
    const { trainee, progress, canAssignManagers, availableManagers } =
        usePage<{
            trainee: TraineeDetail;
            progress: TraineeProgressData;
            canAssignManagers: boolean;
            availableManagers: { id: number; name: string }[];
        }>().props;

    const { stats } = progress;

    return (
        <>
            <Head title={trainee.name} />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Link
                    href={index().url}
                    className="flex w-fit items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"
                >
                    <ArrowLeft className="size-4" /> Trainees
                </Link>

                <Card className="gap-4 p-5">
                    <div className="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <h1 className="text-xl font-semibold tracking-tight">
                                {trainee.name}
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                {[trainee.position, trainee.store.name]
                                    .filter(Boolean)
                                    .join(' · ')}
                            </p>
                            {trainee.managers.length > 0 && (
                                <div className="mt-2 flex flex-wrap items-center gap-1.5">
                                    <Users className="size-3.5 text-muted-foreground" />
                                    {trainee.managers.map((m) => (
                                        <Badge key={m.id} variant="secondary">
                                            {m.name}
                                        </Badge>
                                    ))}
                                </div>
                            )}
                        </div>

                        <div className="flex flex-wrap gap-2">
                            {canAssignManagers && (
                                <AssignManagersDialog
                                    traineeId={trainee.id}
                                    available={availableManagers}
                                    assignedIds={trainee.managers.map(
                                        (m) => m.id,
                                    )}
                                    trigger={
                                        <Button variant="outline" size="sm">
                                            <Users className="size-4" />{' '}
                                            Managers
                                        </Button>
                                    }
                                />
                            )}
                            <Button variant="outline" size="sm" asChild>
                                <Link href={edit(trainee.id).url}>
                                    <Pencil className="size-4" /> Edit
                                </Link>
                            </Button>
                            <ConfirmDeleteDialog
                                title="Remove trainee?"
                                description="This permanently deletes the trainee and all of their evaluation records."
                                onConfirm={(close) =>
                                    router.delete(destroy(trainee.id).url, {
                                        onSuccess: close,
                                    })
                                }
                                trigger={
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        className="text-muted-foreground hover:text-destructive"
                                    >
                                        <Trash2 className="size-4" />
                                    </Button>
                                }
                            />
                        </div>
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="space-y-1">
                            <p className="text-xs text-muted-foreground">
                                Completion
                            </p>
                            <CompletionBar
                                completed={stats.completed}
                                total={stats.total}
                            />
                            <p className="text-xs text-muted-foreground">
                                {stats.completed} of {stats.total} steps
                                complete
                            </p>
                        </div>
                        <div className="space-y-1">
                            <p className="text-xs text-muted-foreground">
                                Average rating
                            </p>
                            <div className="flex items-center gap-2">
                                <StarRating
                                    value={
                                        stats.average_rating
                                            ? Math.round(stats.average_rating)
                                            : null
                                    }
                                    readOnly
                                    size="sm"
                                />
                                <span className="text-sm font-medium">
                                    {stats.average_rating ?? '—'}
                                </span>
                            </div>
                        </div>
                    </div>
                </Card>

                {progress.sections.map((section) => {
                    const count = leafCount(
                        section.categories.flatMap((c) => c.items),
                    );

                    return (
                        <Card key={section.id} className="gap-0 py-0">
                            <Collapsible defaultOpen>
                                <CollapsibleTrigger className="group flex w-full items-center gap-3 p-4 text-left">
                                    <ChevronDown className="size-4 text-muted-foreground transition-transform group-data-[state=closed]:-rotate-90" />
                                    <span className="flex-1 font-semibold">
                                        {section.title}
                                    </span>
                                    <Badge
                                        variant={
                                            count.done === count.total &&
                                            count.total > 0
                                                ? 'default'
                                                : 'secondary'
                                        }
                                    >
                                        {count.done}/{count.total}
                                    </Badge>
                                </CollapsibleTrigger>
                                <CollapsibleContent className="space-y-4 px-4 pb-4">
                                    {section.categories.map((category) => (
                                        <div
                                            key={category.id}
                                            className="space-y-2"
                                        >
                                            <h3 className="text-sm font-medium text-muted-foreground">
                                                {category.title}
                                            </h3>
                                            {category.items.map((item) => (
                                                <EvaluationItem
                                                    key={item.id}
                                                    item={item}
                                                    traineeId={trainee.id}
                                                    currentStepId={
                                                        progress.currentStepId
                                                    }
                                                />
                                            ))}
                                        </div>
                                    ))}
                                </CollapsibleContent>
                            </Collapsible>
                        </Card>
                    );
                })}
            </div>
        </>
    );
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Trainees', href: index() }];

TraineeShow.layout = { breadcrumbs };
