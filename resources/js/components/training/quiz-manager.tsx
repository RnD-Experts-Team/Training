import { router } from '@inertiajs/react';
import { FileQuestion, Pencil, Plus, Trash2 } from 'lucide-react';
import { ConfirmDeleteDialog } from '@/components/training/confirm-delete-dialog';
import { QuizQuestionFormDialog } from '@/components/training/quiz-question-form-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { destroy as destroyQuestion } from '@/routes/training/quiz-questions';
import {
    destroy as destroyQuiz,
    store as enableQuiz,
} from '@/routes/training/quizzes';
import type { Quiz } from '@/types/training';

const MAX_QUESTIONS = 5;
const MIN_TO_SEND = 3;

/**
 * The optional quiz block on a station's builder page. A station has at
 * most one quiz, capped at 5 short multiple-choice questions.
 */
export function QuizManager({
    sectionId,
    quiz,
}: {
    sectionId: number;
    quiz: Quiz | null;
}) {
    if (!quiz) {
        return (
            <Card className="flex flex-col items-center gap-3 border-dashed p-6 text-center">
                <FileQuestion className="size-8 text-muted-foreground" />
                <div>
                    <p className="text-sm font-medium">No quiz yet</p>
                    <p className="text-sm text-muted-foreground">
                        Add a short 3–5 question check trainees complete after
                        this station.
                    </p>
                </div>
                <Button
                    size="sm"
                    variant="outline"
                    onClick={() =>
                        router.post(
                            enableQuiz(sectionId).url,
                            {},
                            { preserveScroll: true },
                        )
                    }
                >
                    <Plus className="size-4" /> Add quiz
                </Button>
            </Card>
        );
    }

    return (
        <Card className="gap-3 p-4">
            <div className="flex flex-wrap items-center justify-between gap-2">
                <div className="flex items-center gap-2">
                    <FileQuestion className="size-4 text-muted-foreground" />
                    <h3 className="font-semibold">Quiz</h3>
                    <Badge variant="secondary">
                        {quiz.questions.length}/{MAX_QUESTIONS} questions
                    </Badge>
                </div>
                <div className="flex gap-2">
                    <QuizQuestionFormDialog
                        quizId={quiz.id}
                        trigger={
                            <Button
                                size="sm"
                                variant="outline"
                                disabled={
                                    quiz.questions.length >= MAX_QUESTIONS
                                }
                            >
                                <Plus className="size-4" /> Add question
                            </Button>
                        }
                    />
                    <ConfirmDeleteDialog
                        title="Remove quiz?"
                        description="This permanently deletes the quiz, its questions, and any sent or completed results for it."
                        onConfirm={(close) =>
                            router.delete(destroyQuiz(quiz.id).url, {
                                preserveScroll: true,
                                onSuccess: close,
                            })
                        }
                        trigger={
                            <Button
                                size="sm"
                                variant="ghost"
                                className="text-muted-foreground hover:text-destructive"
                            >
                                <Trash2 className="size-4" />
                            </Button>
                        }
                    />
                </div>
            </div>

            {quiz.questions.length === 0 ? (
                <p className="text-sm text-muted-foreground">
                    No questions yet. Add at least {MIN_TO_SEND} to make this
                    quiz sendable.
                </p>
            ) : (
                <ul className="space-y-2">
                    {quiz.questions.map((question, index) => (
                        <li
                            key={question.id}
                            className="flex items-start justify-between gap-3 rounded-md border p-3"
                        >
                            <div className="min-w-0">
                                <p className="text-sm font-medium">
                                    {index + 1}. {question.prompt}
                                </p>
                                <p className="mt-1 truncate text-xs text-muted-foreground">
                                    Correct:{' '}
                                    {
                                        question.options.find(
                                            (option) => option.is_correct,
                                        )?.text
                                    }
                                </p>
                            </div>
                            <div className="flex shrink-0 gap-1">
                                <QuizQuestionFormDialog
                                    question={question}
                                    trigger={
                                        <Button size="icon" variant="ghost">
                                            <Pencil className="size-4" />
                                        </Button>
                                    }
                                />
                                <Button
                                    size="icon"
                                    variant="ghost"
                                    className="text-muted-foreground hover:text-destructive"
                                    onClick={() =>
                                        router.delete(
                                            destroyQuestion(question.id).url,
                                            { preserveScroll: true },
                                        )
                                    }
                                >
                                    <Trash2 className="size-4" />
                                </Button>
                            </div>
                        </li>
                    ))}
                </ul>
            )}

            {quiz.questions.length > 0 &&
                quiz.questions.length < MIN_TO_SEND && (
                    <p className="text-xs text-muted-foreground">
                        Add at least {MIN_TO_SEND} questions before sending this
                        quiz to trainees.
                    </p>
                )}
        </Card>
    );
}
