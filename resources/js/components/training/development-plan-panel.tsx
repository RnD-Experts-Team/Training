import { ListChecks, Target } from 'lucide-react';
import { CompletionBar } from '@/components/training/completion-bar';
import { DevelopmentPlanPicker } from '@/components/training/development-plan-picker';
import { EvaluationItem } from '@/components/training/evaluation-item';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import type {
    DevelopmentPickerSection,
    DevelopmentPlanData,
} from '@/types/training';

export function DevelopmentPlanPanel({
    traineeId,
    plan,
    picker,
    readOnly,
}: {
    traineeId: number;
    plan: DevelopmentPlanData;
    picker: DevelopmentPickerSection[];
    readOnly: boolean;
}) {
    return (
        <div className="space-y-4">
            <div className="flex flex-wrap items-center justify-between gap-3">
                <div className="max-w-xs flex-1 space-y-1">
                    <p className="text-xs text-muted-foreground">
                        {plan.stats.completed} of {plan.stats.total} plan items
                        complete
                    </p>
                    <CompletionBar
                        completed={plan.stats.completed}
                        total={plan.stats.total}
                    />
                </div>
                {!readOnly && (
                    <DevelopmentPlanPicker
                        traineeId={traineeId}
                        sections={picker}
                        selectedIds={plan.items.map((item) => item.id)}
                        trigger={
                            <Button variant="outline" size="sm">
                                <ListChecks className="size-4" /> Edit plan
                            </Button>
                        }
                    />
                )}
            </div>

            {plan.items.length === 0 ? (
                <Card className="flex flex-col items-center justify-center gap-3 border-dashed p-10 text-center">
                    <Target className="size-8 text-muted-foreground" />
                    <p className="text-sm text-muted-foreground">
                        No items in this plan yet. Pick a few specific tasks
                        from the curriculum to focus on.
                    </p>
                </Card>
            ) : (
                <div className="space-y-3">
                    {plan.items.map((item) => (
                        <div key={item.id} className="space-y-1">
                            <p className="text-xs text-muted-foreground">
                                {item.section_title} · {item.category_title}
                            </p>
                            <EvaluationItem
                                item={item}
                                traineeId={traineeId}
                                currentStepId={null}
                                readOnly={readOnly}
                            />
                        </div>
                    ))}
                </div>
            )}
        </div>
    );
}
