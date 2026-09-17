import { Head, useForm, usePage } from '@inertiajs/react';
import { CheckCircle2 } from 'lucide-react';
import type { FormEvent } from 'react';
import AppLogoIcon from '@/components/app-logo-icon';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { cn } from '@/lib/utils';
import { store } from '@/routes/quiz';

type Option = { id: number; text: string };
type Question = { id: number; prompt: string; options: Option[] };

export default function QuizShow() {
    const { traineeName, sectionTitle, completed, questions, token } = usePage<{
        traineeName: string;
        sectionTitle: string | null;
        completed: boolean;
        questions: Question[];
        token: string;
    }>().props;

    const form = useForm<{ answers: Record<number, number> }>({
        answers: {},
    });

    function choose(questionId: number, optionId: number) {
        form.setData('answers', {
            ...form.data.answers,
            [questionId]: optionId,
        });
    }

    function submit(event: FormEvent) {
        event.preventDefault();
        form.post(store(token).url);
    }

    const answeredCount = Object.keys(form.data.answers).length;
    const allAnswered =
        questions.length > 0 && answeredCount === questions.length;

    return (
        <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10">
            <Head
                title={sectionTitle ? `${sectionTitle} quiz` : 'Quick quiz'}
            />

            <div className="w-full max-w-lg">
                <div className="mb-6 flex flex-col items-center gap-3 text-center">
                    <div className="flex h-10 w-10 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground">
                        <AppLogoIcon className="size-6 fill-current" />
                    </div>
                    <div>
                        <h1 className="text-lg font-semibold tracking-tight">
                            {sectionTitle
                                ? `${sectionTitle} — Quick Quiz`
                                : 'Quick Quiz'}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Hi {traineeName}, this should only take a minute.
                        </p>
                    </div>
                </div>

                {completed ? (
                    <Card className="flex flex-col items-center gap-3 p-8 text-center">
                        <CheckCircle2 className="size-10 text-emerald-500" />
                        <h2 className="text-base font-semibold">
                            Thanks, you're done
                        </h2>
                        <p className="text-sm text-muted-foreground">
                            Your answers have been sent to the training team.
                            You can close this page now.
                        </p>
                    </Card>
                ) : (
                    <form onSubmit={submit} className="space-y-4">
                        {questions.map((question, index) => (
                            <Card key={question.id} className="gap-3 p-5">
                                <p className="text-sm font-medium">
                                    {index + 1}. {question.prompt}
                                </p>
                                <div className="grid gap-2">
                                    {question.options.map((option) => {
                                        const selected =
                                            form.data.answers[question.id] ===
                                            option.id;

                                        return (
                                            <button
                                                key={option.id}
                                                type="button"
                                                onClick={() =>
                                                    choose(
                                                        question.id,
                                                        option.id,
                                                    )
                                                }
                                                className={cn(
                                                    'rounded-md border p-2.5 text-left text-sm transition-colors',
                                                    selected
                                                        ? 'border-primary bg-primary/10 font-medium'
                                                        : 'hover:bg-muted/50',
                                                )}
                                            >
                                                {option.text}
                                            </button>
                                        );
                                    })}
                                </div>
                            </Card>
                        ))}

                        <Button
                            type="submit"
                            className="w-full"
                            disabled={!allAnswered || form.processing}
                        >
                            Submit answers
                        </Button>
                        {!allAnswered && (
                            <p className="text-center text-xs text-muted-foreground">
                                Answer all {questions.length} questions to
                                submit ({answeredCount}/{questions.length} so
                                far).
                            </p>
                        )}
                    </form>
                )}
            </div>
        </div>
    );
}
