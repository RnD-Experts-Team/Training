import { router } from '@inertiajs/react';
import { ExternalLink } from 'lucide-react';
import { useState } from 'react';
import { ImportanceBadge } from '@/components/training/importance-badge';
import { StarRating } from '@/components/training/star-rating';
import { Checkbox } from '@/components/ui/checkbox';
import { Textarea } from '@/components/ui/textarea';
import { useCurrentStep } from '@/hooks/use-current-step';
import { cn } from '@/lib/utils';
import { update as updateEvaluation } from '@/routes/trainees/evaluations';
import type { EvaluationItem as EvaluationItemType } from '@/types/training';

function persist(
    traineeId: number,
    itemId: number,
    data: { completed: boolean; rating: number | null; notes: string | null },
) {
    router.put(updateEvaluation([traineeId, itemId]).url, data, {
        preserveScroll: true,
        preserveState: true,
        only: ['progress'],
    });
}

export function EvaluationItem({
    item,
    traineeId,
    currentStepId,
    isSub = false,
}: {
    item: EvaluationItemType;
    traineeId: number;
    currentStepId: number | null;
    isSub?: boolean;
}) {
    const ev = item.evaluation;

    const [completed, setCompleted] = useState(ev?.completed ?? false);
    const [rating, setRating] = useState<number | null>(ev?.rating ?? null);
    const [notes, setNotes] = useState(ev?.notes ?? '');

    // Re-sync local state when the server sends new data (e.g. cascade).
    const signature = `${ev?.completed ? 1 : 0}|${ev?.rating ?? ''}|${ev?.notes ?? ''}`;
    const [knownSignature, setKnownSignature] = useState(signature);

    if (signature !== knownSignature) {
        setKnownSignature(signature);
        setCompleted(ev?.completed ?? false);
        setRating(ev?.rating ?? null);
        setNotes(ev?.notes ?? '');
    }

    const isCurrent = item.id === currentStepId;
    const stepRef = useCurrentStep(isCurrent);

    function toggle(next: boolean) {
        setCompleted(next);
        persist(traineeId, item.id, {
            completed: next,
            rating,
            notes: notes || null,
        });
    }

    function rate(next: number | null) {
        setRating(next);
        persist(traineeId, item.id, {
            completed,
            rating: next,
            notes: notes || null,
        });
    }

    function commitNotes() {
        if ((ev?.notes ?? '') !== notes) {
            persist(traineeId, item.id, {
                completed,
                rating,
                notes: notes || null,
            });
        }
    }

    return (
        <div
            ref={stepRef}
            className={cn(
                'rounded-lg border p-3 transition-colors',
                completed
                    ? 'border-emerald-200 bg-emerald-50/40 dark:border-emerald-900 dark:bg-emerald-950/20'
                    : 'bg-background',
                isCurrent &&
                    'ring-2 ring-primary ring-offset-2 ring-offset-background',
                isSub && 'border-dashed',
            )}
        >
            <div className="flex items-start gap-3">
                <Checkbox
                    checked={completed}
                    onCheckedChange={(value) => toggle(value === true)}
                    className="mt-0.5"
                />

                <div className="min-w-0 flex-1 space-y-2">
                    <div className="flex flex-wrap items-center gap-2">
                        <span
                            className={cn(
                                'text-sm font-medium',
                                completed &&
                                    'text-muted-foreground line-through',
                            )}
                        >
                            {item.title}
                        </span>
                        <ImportanceBadge importance={item.importance} />
                        {isCurrent && (
                            <span className="rounded-full bg-primary px-2 py-0.5 text-xs font-medium text-primary-foreground">
                                Current step
                            </span>
                        )}
                    </div>

                    {item.content && (
                        <p className="rounded bg-muted/50 px-2 py-1.5 text-sm whitespace-pre-line text-muted-foreground">
                            {item.content}
                        </p>
                    )}

                    {item.media.length > 0 && (
                        <ul className="flex flex-wrap gap-2">
                            {item.media.map((m) => (
                                <li key={m.id}>
                                    <a
                                        href={m.display_url ?? '#'}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="flex items-center gap-1 rounded-md border bg-muted/40 px-2 py-1 text-xs hover:underline"
                                    >
                                        {m.label || m.type}
                                        <ExternalLink className="size-3 text-muted-foreground" />
                                    </a>
                                </li>
                            ))}
                        </ul>
                    )}

                    <div className="flex flex-wrap items-center gap-3 pt-1">
                        <StarRating value={rating} onChange={rate} size="sm" />
                        {rating !== null && (
                            <button
                                type="button"
                                onClick={() => rate(null)}
                                className="text-xs text-muted-foreground hover:text-foreground"
                            >
                                Clear
                            </button>
                        )}
                    </div>

                    <Textarea
                        value={notes}
                        onChange={(e) => setNotes(e.target.value)}
                        onBlur={commitNotes}
                        placeholder="Add a note…"
                        className="min-h-0 resize-none py-1.5 text-sm"
                        rows={1}
                    />
                </div>
            </div>

            {item.children.length > 0 && (
                <div className="mt-3 space-y-2 border-t pt-3 pl-7">
                    {item.children.map((child) => (
                        <EvaluationItem
                            key={child.id}
                            item={child}
                            traineeId={traineeId}
                            currentStepId={currentStepId}
                            isSub
                        />
                    ))}
                </div>
            )}
        </div>
    );
}
