import { router } from '@inertiajs/react';
import { CheckCircle2, Copy, FileQuestion, Send } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { store as sendQuiz } from '@/routes/trainees/quiz-attempts';
import type { SectionQuiz } from '@/types/training';

const MIN_QUESTIONS_TO_SEND = 3;

/**
 * A section's quiz status on the trainee page. Deliberately shows only
 * Not sent / Sent (+ link) / Completed — never a score. Full results live
 * only in the training-team-only Quiz Results view.
 */
export function SectionQuizCard({
    traineeId,
    quiz,
    readOnly,
}: {
    traineeId: number;
    quiz: SectionQuiz;
    readOnly: boolean;
}) {
    const [copied, setCopied] = useState(false);

    function send() {
        router.post(
            sendQuiz(traineeId).url,
            { quiz_id: quiz.id },
            { preserveScroll: true },
        );
    }

    async function copyLink(link: string) {
        try {
            await navigator.clipboard.writeText(link);
            setCopied(true);
            setTimeout(() => setCopied(false), 2000);
        } catch {
            // Clipboard access can fail (permissions, insecure context) —
            // the link is still selectable/copyable from the input itself.
        }
    }

    if (!quiz.attempt) {
        const canSend = quiz.questions_count >= MIN_QUESTIONS_TO_SEND;

        return (
            <Card className="flex flex-wrap items-center justify-between gap-3 p-3">
                <div className="flex items-center gap-2 text-sm">
                    <FileQuestion className="size-4 text-muted-foreground" />
                    <span className="font-medium">Quiz</span>
                    <span className="text-muted-foreground">Not sent</span>
                </div>
                {!readOnly &&
                    (canSend ? (
                        <Button size="sm" variant="outline" onClick={send}>
                            <Send className="size-4" /> Send quiz
                        </Button>
                    ) : (
                        <span className="text-xs text-muted-foreground">
                            Needs at least {MIN_QUESTIONS_TO_SEND} questions
                        </span>
                    ))}
            </Card>
        );
    }

    if (quiz.attempt.status === 'completed') {
        return (
            <Card className="flex items-center gap-2 p-3 text-sm">
                <CheckCircle2 className="size-4 shrink-0 text-emerald-500" />
                <span className="font-medium">Quiz</span>
                <span className="text-muted-foreground">
                    Completed — results go to the training team
                </span>
            </Card>
        );
    }

    return (
        <Card className="flex flex-col gap-2 p-3">
            <div className="flex items-center gap-2 text-sm">
                <FileQuestion className="size-4 shrink-0 text-muted-foreground" />
                <span className="font-medium">Quiz</span>
                <span className="text-muted-foreground">
                    Sent — share this link with the employee
                </span>
            </div>
            <div className="flex gap-2">
                <Input
                    readOnly
                    value={quiz.attempt.link ?? ''}
                    className="text-xs"
                    onFocus={(event) => event.currentTarget.select()}
                />
                <Button
                    type="button"
                    size="sm"
                    variant="outline"
                    className="shrink-0"
                    onClick={() =>
                        quiz.attempt?.link && copyLink(quiz.attempt.link)
                    }
                >
                    <Copy className="size-4" /> {copied ? 'Copied' : 'Copy'}
                </Button>
            </div>
        </Card>
    );
}
