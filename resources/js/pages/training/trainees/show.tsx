import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    ArrowLeft,
    ChevronDown,
    Clock,
    Pencil,
    Trash2,
    Users,
} from 'lucide-react';
import { useState } from 'react';
import { AssignManagersDialog } from '@/components/training/assign-managers-dialog';
import { CategorySection } from '@/components/training/category-section';
import { CompletionBar } from '@/components/training/completion-bar';
import { ConfirmDeleteDialog } from '@/components/training/confirm-delete-dialog';
import { RatingMeter } from '@/components/training/rating-meter';
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
    ProgressSection,
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

function itemsContain(items: EvaluationItemType[], stepId: number): boolean {
    return items.some(
        (item) => item.id === stepId || itemsContain(item.children, stepId),
    );
}

/** The category holding the current step, so it can open by default. */
function categoryOfStep(
    sections: ProgressSection[],
    stepId: number | null,
): number | null {
    if (stepId === null) {
        return null;
    }

    for (const section of sections) {
        for (const category of section.categories) {
            if (itemsContain(category.items, stepId)) {
                return category.id;
            }
        }
    }

    return null;
}

/** The section holding the current step, so it can open by default. */
function sectionOfStep(
    sections: ProgressSection[],
    stepId: number | null,
): number | null {
    if (stepId === null) {
        return sections[0]?.id ?? null;
    }

    for (const section of sections) {
        if (
            section.categories.some((category) =>
                itemsContain(category.items, stepId),
            )
        ) {
            return section.id;
        }
    }

    return sections[0]?.id ?? null;
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

    // Single-open accordions; the current step's section + category open first.
    const [openSectionId, setOpenSectionId] = useState<number | null>(() =>
        sectionOfStep(progress.sections, progress.currentStepId),
    );
    const [openCategoryId, setOpenCategoryId] = useState<number | null>(() =>
        categoryOfStep(progress.sections, progress.currentStepId),
    );

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
                                Average score
                            </p>
                            <RatingMeter
                                value={stats.average_rating}
                                size="md"
                            />
                        </div>
                    </div>
                </Card>

                {progress.sections.map((section) => {
                    const count = leafCount(
                        section.categories.flatMap((c) => c.items),
                    );

                    return (
                        <Card key={section.id} className="gap-0 py-0">
                            <Collapsible
                                open={openSectionId === section.id}
                                onOpenChange={(isOpen) =>
                                    setOpenSectionId(isOpen ? section.id : null)
                                }
                            >
                                <CollapsibleTrigger className="group flex w-full items-center gap-3 p-4 text-left">
                                    <ChevronDown className="size-4 shrink-0 text-muted-foreground transition-transform group-data-[state=closed]:-rotate-90" />
                                    <div className="flex min-w-0 flex-1 flex-col gap-0.5">
                                        <span className="truncate font-semibold">
                                            {section.title}
                                        </span>
                                        {section.hands_on_shifts && (
                                            <span className="flex items-center gap-1 text-xs text-muted-foreground">
                                                <Clock className="size-3 shrink-0" />
                                                Hands-on:{' '}
                                                {section.hands_on_shifts}
                                            </span>
                                        )}
                                    </div>
                                    {section.average_rating !== null && (
                                        <RatingMeter
                                            value={section.average_rating}
                                            size="sm"
                                            className="hidden shrink-0 sm:flex"
                                        />
                                    )}
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
                                <CollapsibleContent className="space-y-3 px-4 pb-4">
                                    {section.categories.map((category) => (
                                        <CategorySection
                                            key={category.id}
                                            category={category}
                                            traineeId={trainee.id}
                                            currentStepId={
                                                progress.currentStepId
                                            }
                                            open={
                                                openCategoryId === category.id
                                            }
                                            onOpenChange={(isOpen) =>
                                                setOpenCategoryId(
                                                    isOpen ? category.id : null,
                                                )
                                            }
                                        />
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
