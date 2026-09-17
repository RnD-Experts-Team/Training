import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft, CheckCircle2, RotateCcw, XCircle } from 'lucide-react';
import Heading from '@/components/heading';
import { ConfirmDeleteDialog } from '@/components/training/confirm-delete-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { cn } from '@/lib/utils';
import { destroy, index } from '@/routes/training/quiz-results';
import type { BreadcrumbItem } from '@/types';
import type { QuizResultDetail, QuizResultQuestion } from '@/types/training';

export default function QuizResultShow() {
    const { attempt, questions } = usePage<{
        attempt: QuizResultDetail;
        questions: QuizResultQuestion[];
    }>().props;

    return (
        <>
            <Head
                title={`${attempt.trainee.name} — ${attempt.section.title} quiz`}
            />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Link
                    href={index().url}
                    className="flex w-fit items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"
                >
                    <ArrowLeft className="size-4" /> Quiz Results
                </Link>

                <Card className="flex flex-wrap items-center justify-between gap-4 p-5">
                    <Heading
                        variant="small"
                        title={attempt.trainee.name}
                        description={`${attempt.section.title} quiz · Completed ${
                            attempt.completed_at
                                ? new Date(
                                      attempt.completed_at,
                                  ).toLocaleString()
                                : '—'
                        }`}
                    />
                    <div className="flex items-center gap-3">
                        <Badge
                            className="text-sm"
                            variant={
                                attempt.score !== null && attempt.score >= 70
                                    ? 'default'
                                    : 'secondary'
                            }
                        >
                            {attempt.score}% score
                        </Badge>
                        <ConfirmDeleteDialog
                            title="Reset this quiz?"
                            description="This deletes the attempt and its answers, allowing the station to be sent to this trainee again."
                            onConfirm={(close) =>
                                router.delete(destroy(attempt.id).url, {
                                    onSuccess: close,
                                })
                            }
                            trigger={
                                <Button variant="outline">
                                    <RotateCcw className="size-4" /> Reset
                                </Button>
                            }
                        />
                    </div>
                </Card>

                <div className="space-y-3">
                    {questions.map((question, index) => (
                        <Card key={question.id} className="gap-3 p-5">
                            <p className="text-sm font-medium">
                                {index + 1}. {question.prompt}
                            </p>
                            <div className="grid gap-2">
                                {question.options.map((option) => (
                                    <div
                                        key={option.id}
                                        className={cn(
                                            'flex items-center gap-2 rounded-md border p-2.5 text-sm',
                                            option.is_correct &&
                                                'border-emerald-500/50 bg-emerald-50/50 dark:bg-emerald-950/20',
                                            option.is_chosen &&
                                                !option.is_correct &&
                                                'border-destructive/50 bg-destructive/5',
                                        )}
                                    >
                                        {option.is_correct ? (
                                            <CheckCircle2 className="size-4 shrink-0 text-emerald-500" />
                                        ) : option.is_chosen ? (
                                            <XCircle className="size-4 shrink-0 text-destructive" />
                                        ) : (
                                            <span className="size-4 shrink-0" />
                                        )}
                                        <span
                                            className={cn(
                                                option.is_chosen &&
                                                    'font-medium',
                                            )}
                                        >
                                            {option.text}
                                        </span>
                                        {option.is_chosen && (
                                            <span className="ml-auto text-xs text-muted-foreground">
                                                Chosen
                                            </span>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </Card>
                    ))}
                </div>
            </div>
        </>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Quiz Results', href: index() },
];

QuizResultShow.layout = { breadcrumbs };
