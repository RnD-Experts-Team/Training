import { useForm } from '@inertiajs/react';
import { CheckCircle2 } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent, ReactNode } from 'react';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import { store, update } from '@/routes/training/quiz-questions';
import type { QuizQuestion } from '@/types/training';

const OPTION_LETTERS = ['A', 'B', 'C', 'D'];

export function QuizQuestionFormDialog({
    quizId,
    question,
    trigger,
}: {
    quizId?: number;
    question?: QuizQuestion;
    trigger: ReactNode;
}) {
    const [open, setOpen] = useState(false);

    const sortedOptions = question?.options
        ?.slice()
        .sort((a, b) => a.order - b.order);

    const form = useForm({
        prompt: question?.prompt ?? '',
        options: sortedOptions?.map((option) => option.text) ?? [
            '',
            '',
            '',
            '',
        ],
        correct_index: sortedOptions
            ? Math.max(
                  0,
                  sortedOptions.findIndex((option) => option.is_correct),
              )
            : 0,
    });

    function setOptionText(index: number, text: string) {
        const next = [...form.data.options];
        next[index] = text;
        form.setData('options', next);
    }

    function submit(event: FormEvent) {
        event.preventDefault();
        const options = {
            preserveScroll: true,
            onSuccess: () => setOpen(false),
        };

        if (question) {
            form.put(update(question.id).url, options);
        } else if (quizId) {
            form.post(store(quizId).url, options);
        }
    }

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>{trigger}</DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {question ? 'Edit question' : 'New question'}
                    </DialogTitle>
                    <DialogDescription>
                        Multiple choice — tap a letter to mark the correct
                        answer.
                    </DialogDescription>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    <div className="grid gap-2">
                        <Label htmlFor="prompt">Question</Label>
                        <Input
                            id="prompt"
                            value={form.data.prompt}
                            onChange={(e) =>
                                form.setData('prompt', e.target.value)
                            }
                            autoFocus
                            required
                        />
                        <InputError message={form.errors.prompt} />
                    </div>

                    <div className="grid gap-2">
                        <Label>Answers</Label>
                        {form.data.options.map((text, index) => {
                            const isCorrect = form.data.correct_index === index;

                            return (
                                <div
                                    key={index}
                                    className={cn(
                                        'flex items-center gap-2 rounded-lg border p-1 pl-1.5 transition-colors',
                                        isCorrect
                                            ? 'border-emerald-500/50 bg-emerald-500/5'
                                            : 'border-border',
                                    )}
                                >
                                    <button
                                        type="button"
                                        onClick={() =>
                                            form.setData('correct_index', index)
                                        }
                                        aria-pressed={isCorrect}
                                        aria-label={`Mark option ${OPTION_LETTERS[index]} as correct`}
                                        className={cn(
                                            'flex size-7 shrink-0 items-center justify-center rounded-full text-xs font-semibold transition-colors',
                                            isCorrect
                                                ? 'bg-emerald-500 text-white'
                                                : 'bg-muted text-muted-foreground hover:bg-accent',
                                        )}
                                    >
                                        {OPTION_LETTERS[index]}
                                    </button>
                                    <Input
                                        value={text}
                                        onChange={(e) =>
                                            setOptionText(index, e.target.value)
                                        }
                                        placeholder={`Option ${OPTION_LETTERS[index]}`}
                                        required
                                        className="h-8 border-0 bg-transparent px-1 shadow-none focus-visible:ring-0"
                                    />
                                    {isCorrect && (
                                        <CheckCircle2 className="size-4 shrink-0 text-emerald-500" />
                                    )}
                                </div>
                            );
                        })}
                        <InputError message={form.errors.options} />
                        <InputError message={form.errors.correct_index} />
                    </div>

                    <DialogFooter>
                        <Button type="submit" disabled={form.processing}>
                            Save question
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
