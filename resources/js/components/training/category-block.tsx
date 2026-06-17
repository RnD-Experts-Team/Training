import { router } from '@inertiajs/react';
import { ChevronDown, GripVertical, Pencil, Plus, Trash2 } from 'lucide-react';
import { CategoryFormDialog } from '@/components/training/category-form-dialog';
import { ChecklistItemRow } from '@/components/training/checklist-item-row';
import { ConfirmDeleteDialog } from '@/components/training/confirm-delete-dialog';
import { ItemFormDialog } from '@/components/training/item-form-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { useSortable } from '@/hooks/use-sortable';
import type { SortableHandleProps } from '@/hooks/use-sortable';
import { destroy } from '@/routes/training/categories';
import { reorder } from '@/routes/training/items';
import type { Category } from '@/types/training';

export function CategoryBlock({
    category,
    sectionId,
    dragProps,
}: {
    category: Category;
    sectionId: number;
    dragProps: SortableHandleProps;
}) {
    const items = category.items ?? [];
    const { list, itemProps } = useSortable(items, (payload) =>
        router.post(
            reorder(category.id).url,
            { items: payload },
            { preserveScroll: true },
        ),
    );

    return (
        <Card
            {...dragProps}
            className="min-w-0 gap-0 py-0 transition-opacity data-[dragging]:opacity-50"
        >
            <Collapsible defaultOpen>
                <div className="flex items-center gap-2 p-3">
                    <GripVertical className="size-4 shrink-0 cursor-grab text-muted-foreground" />
                    <CollapsibleTrigger className="group flex min-w-0 flex-1 items-center gap-2 text-left">
                        <ChevronDown className="size-4 shrink-0 text-muted-foreground transition-transform group-data-[state=closed]:-rotate-90" />
                        <span className="truncate font-semibold">
                            {category.title}
                        </span>
                        <Badge variant="secondary" className="shrink-0">
                            {items.length}
                        </Badge>
                    </CollapsibleTrigger>

                    <CategoryFormDialog
                        sectionId={sectionId}
                        category={category}
                        trigger={
                            <Button variant="ghost" size="icon">
                                <Pencil className="size-4" />
                            </Button>
                        }
                    />
                    <ConfirmDeleteDialog
                        title="Delete category?"
                        description="This permanently deletes the category and all of its items."
                        onConfirm={(close) =>
                            router.delete(destroy(category.id).url, {
                                preserveScroll: true,
                                onSuccess: close,
                            })
                        }
                        trigger={
                            <Button
                                variant="ghost"
                                size="icon"
                                className="text-muted-foreground hover:text-destructive"
                            >
                                <Trash2 className="size-4" />
                            </Button>
                        }
                    />
                    <ItemFormDialog
                        categoryId={category.id}
                        trigger={
                            <Button
                                variant="outline"
                                size="sm"
                                className="shrink-0"
                            >
                                <Plus className="size-3.5" />
                                <span className="hidden sm:inline">Item</span>
                            </Button>
                        }
                    />
                </div>

                <CollapsibleContent className="space-y-2 px-3 pb-3">
                    {category.description && (
                        <p className="text-sm text-muted-foreground">
                            {category.description}
                        </p>
                    )}
                    {list.length === 0 ? (
                        <p className="py-2 text-sm text-muted-foreground">
                            No items yet.
                        </p>
                    ) : (
                        list.map((item) => (
                            <ChecklistItemRow
                                key={item.id}
                                item={item}
                                categoryId={category.id}
                                dragProps={itemProps(item.id)}
                            />
                        ))
                    )}
                </CollapsibleContent>
            </Collapsible>
        </Card>
    );
}
