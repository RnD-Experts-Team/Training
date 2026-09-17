import { Head, Link, usePage } from '@inertiajs/react';
import { FileQuestion } from 'lucide-react';
import Heading from '@/components/heading';
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { index, show } from '@/routes/training/quiz-results';
import type { BreadcrumbItem } from '@/types';
import type { QuizAttemptRow } from '@/types/training';

export default function QuizResultsIndex() {
    const { attempts } = usePage<{ attempts: QuizAttemptRow[] }>().props;

    return (
        <>
            <Head title="Quiz Results" />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Heading
                    title="Quiz Results"
                    description="Every quiz sent and completed across the program. Kept out of the Manager's view — only the training team sees answers and scores."
                />

                {attempts.length === 0 ? (
                    <Card className="flex flex-col items-center justify-center gap-3 border-dashed p-12 text-center">
                        <FileQuestion className="size-10 text-muted-foreground" />
                        <p className="text-sm text-muted-foreground">
                            No quizzes have been sent yet.
                        </p>
                    </Card>
                ) : (
                    <section className="surface-tray">
                        <div className="surface-core overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Trainee</TableHead>
                                        <TableHead>Quiz</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead className="text-right">
                                            Score
                                        </TableHead>
                                        <TableHead className="text-right">
                                            Sent
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {attempts.map((attempt) => (
                                        <TableRow key={attempt.id}>
                                            <TableCell>
                                                <p className="font-medium">
                                                    {attempt.trainee.name}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {attempt.store.name}
                                                </p>
                                            </TableCell>
                                            <TableCell>
                                                {attempt.section.title}
                                            </TableCell>
                                            <TableCell>
                                                <Badge
                                                    variant={
                                                        attempt.status ===
                                                        'completed'
                                                            ? 'default'
                                                            : 'secondary'
                                                    }
                                                >
                                                    {attempt.status ===
                                                    'completed'
                                                        ? 'Completed'
                                                        : 'Sent'}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-right tabular-nums">
                                                {attempt.status ===
                                                    'completed' &&
                                                attempt.score !== null ? (
                                                    <Link
                                                        href={
                                                            show(attempt.id).url
                                                        }
                                                        className="font-medium text-primary hover:underline"
                                                    >
                                                        {attempt.score}%
                                                    </Link>
                                                ) : (
                                                    <span className="text-muted-foreground">
                                                        —
                                                    </span>
                                                )}
                                            </TableCell>
                                            <TableCell className="text-right text-sm text-muted-foreground">
                                                {new Date(
                                                    attempt.sent_at,
                                                ).toLocaleDateString()}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </div>
                    </section>
                )}
            </div>
        </>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Quiz Results', href: index() },
];

QuizResultsIndex.layout = { breadcrumbs };
