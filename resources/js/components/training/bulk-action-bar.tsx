import { router } from '@inertiajs/react';
import { FolderInput, Trash2, X } from 'lucide-react';
import { ConfirmDeleteDialog } from '@/components/training/confirm-delete-dialog';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    bulkDestroy as categoriesBulkDestroy,
    move as categoriesMove,
} from '@/routes/training/categories';
import {
    bulkDestroy as itemsBulkDestroy,
    move as itemsMove,
} from '@/routes/training/items';
import type { MoveTarget } from '@/types/training';

/**
 * Floating bar for bulk operations on the content builder. Items move to a
 * category, categories move to a station; a mixed selection can only be deleted.
 */
export function BulkActionBar({
    selectedCategoryIds,
    selectedItemIds,
    moveTargets,
    onClear,
}: {
    selectedCategoryIds: number[];
    selectedItemIds: number[];
    moveTargets: MoveTarget[];
    onClear: () => void;
}) {
    const total = selectedCategoryIds.length + selectedItemIds.length;

    if (total === 0) {
        return null;
    }

    const onlyCategories =
        selectedCategoryIds.length > 0 && selectedItemIds.length === 0;
    const onlyItems =
        selectedItemIds.length > 0 && selectedCategoryIds.length === 0;

    const moveCategories = (sectionId: string) => {
        router.post(
            categoriesMove().url,
            { ids: selectedCategoryIds, section_id: Number(sectionId) },
            { preserveScroll: true, onSuccess: onClear },
        );
    };

    const moveItems = (categoryId: string) => {
        router.post(
            itemsMove().url,
            { ids: selectedItemIds, category_id: Number(categoryId) },
            { preserveScroll: true, onSuccess: onClear },
        );
    };

    const handleDelete = (done: () => void) => {
        const finish = () => {
            onClear();
            done();
        };

        const deleteCategories = () => {
            if (selectedCategoryIds.length === 0) {
                finish();

                return;
            }

            router.post(
                categoriesBulkDestroy().url,
                { ids: selectedCategoryIds },
                { preserveScroll: true, onSuccess: finish },
            );
        };

        if (selectedItemIds.length > 0) {
            router.post(
                itemsBulkDestroy().url,
                { ids: selectedItemIds },
                { preserveScroll: true, onSuccess: deleteCategories },
            );

            return;
        }

        deleteCategories();
    };

    return (
        <div className="pointer-events-none fixed inset-x-0 bottom-4 z-50 flex justify-center px-4">
            <div className="pointer-events-auto flex flex-wrap items-center gap-2 rounded-full border bg-background/95 px-3 py-2 shadow-lg backdrop-blur">
                <span className="px-1 text-sm font-medium whitespace-nowrap">
                    {total} selected
                </span>

                {onlyCategories && (
                    <Select onValueChange={moveCategories}>
                        <SelectTrigger size="sm" className="w-[190px]">
                            <FolderInput className="size-4" />
                            <SelectValue placeholder="Move to station…" />
                        </SelectTrigger>
                        <SelectContent side="top" sideOffset={8}>
                            {moveTargets.map((section) => (
                                <SelectItem
                                    key={section.id}
                                    value={String(section.id)}
                                >
                                    {section.title}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                )}

                {onlyItems && (
                    <Select onValueChange={moveItems}>
                        <SelectTrigger size="sm" className="w-[210px]">
                            <FolderInput className="size-4" />
                            <SelectValue placeholder="Move to category…" />
                        </SelectTrigger>
                        <SelectContent side="top" sideOffset={8}>
                            {moveTargets.map((section) => (
                                <SelectGroup key={section.id}>
                                    <SelectLabel>{section.title}</SelectLabel>
                                    {section.categories.length === 0 ? (
                                        <SelectItem
                                            value={`empty-${section.id}`}
                                            disabled
                                        >
                                            No categories
                                        </SelectItem>
                                    ) : (
                                        section.categories.map((category) => (
                                            <SelectItem
                                                key={category.id}
                                                value={String(category.id)}
                                            >
                                                {category.title}
                                            </SelectItem>
                                        ))
                                    )}
                                </SelectGroup>
                            ))}
                        </SelectContent>
                    </Select>
                )}

                <ConfirmDeleteDialog
                    title={`Delete ${total} item${total === 1 ? '' : 's'}?`}
                    description="This permanently deletes the selected content, including any sub-items and media."
                    onConfirm={handleDelete}
                    trigger={
                        <Button
                            variant="ghost"
                            size="sm"
                            className="text-muted-foreground hover:text-destructive"
                        >
                            <Trash2 className="size-4" /> Delete
                        </Button>
                    }
                />

                <Button
                    variant="ghost"
                    size="icon"
                    className="size-8"
                    onClick={onClear}
                    aria-label="Clear selection"
                >
                    <X className="size-4" />
                </Button>
            </div>
        </div>
    );
}
