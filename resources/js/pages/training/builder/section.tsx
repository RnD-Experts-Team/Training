import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft, Layers, Pencil, Plus } from 'lucide-react';
import Heading from '@/components/heading';
import { CategoryBlock } from '@/components/training/category-block';
import { CategoryFormDialog } from '@/components/training/category-form-dialog';
import { SectionFormDialog } from '@/components/training/section-form-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { useSortable } from '@/hooks/use-sortable';
import { reorder } from '@/routes/training/categories';
import { index } from '@/routes/training/sections';
import type { BreadcrumbItem } from '@/types';
import type { Section } from '@/types/training';

export default function BuilderSection() {
    const { section } = usePage<{ section: Section }>().props;
    const categories = section.categories ?? [];

    const { list, itemProps } = useSortable(categories, (payload) =>
        router.post(
            reorder(section.id).url,
            { items: payload },
            { preserveScroll: true },
        ),
    );

    const timing = [
        section.pie_content_review && `Review: ${section.pie_content_review}`,
        section.screen_to_shoulder &&
            `Screen-to-shoulder: ${section.screen_to_shoulder}`,
        section.hands_on_shifts && `Hands-on: ${section.hands_on_shifts}`,
    ].filter(Boolean);

    return (
        <>
            <Head title={section.title} />

            <div className="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
                <Link
                    href={index().url}
                    className="flex w-fit items-center gap-1.5 text-sm text-muted-foreground hover:text-foreground"
                >
                    <ArrowLeft className="size-4" /> Content builder
                </Link>

                <div className="flex flex-wrap items-start justify-between gap-3">
                    <div className="min-w-0 space-y-2">
                        <Heading
                            title={section.title}
                            description={section.description ?? undefined}
                        />
                        {timing.length > 0 && (
                            <div className="flex flex-wrap gap-1.5">
                                {timing.map((label) => (
                                    <Badge key={label} variant="outline">
                                        {label}
                                    </Badge>
                                ))}
                            </div>
                        )}
                    </div>

                    <div className="flex flex-wrap gap-2">
                        <SectionFormDialog
                            section={section}
                            trigger={
                                <Button variant="outline">
                                    <Pencil className="size-4" /> Edit station
                                </Button>
                            }
                        />
                        <CategoryFormDialog
                            sectionId={section.id}
                            trigger={
                                <Button>
                                    <Plus className="size-4" /> New category
                                </Button>
                            }
                        />
                    </div>
                </div>

                {list.length === 0 ? (
                    <Card className="flex flex-col items-center justify-center gap-3 border-dashed p-12 text-center">
                        <Layers className="size-10 text-muted-foreground" />
                        <p className="text-sm text-muted-foreground">
                            No categories yet. Add a category to start building
                            this station&apos;s checklist.
                        </p>
                    </Card>
                ) : (
                    <div className="grid gap-3">
                        {list.map((category) => (
                            <CategoryBlock
                                key={category.id}
                                category={category}
                                sectionId={section.id}
                                dragProps={itemProps(category.id)}
                            />
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Content builder', href: index() },
];

BuilderSection.layout = { breadcrumbs };
