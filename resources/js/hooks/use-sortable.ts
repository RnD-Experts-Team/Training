import { useState } from 'react';
import type { DragEvent } from 'react';

export type OrderedPayload = { id: number; order: number };

export type SortableHandleProps = {
    draggable: boolean;
    onDragStart: () => void;
    onDragEnter: () => void;
    onDragOver: (event: DragEvent) => void;
    onDrop: () => void;
    onDragEnd: () => void;
    'data-dragging'?: string;
};

/**
 * Lightweight native HTML5 drag-and-drop reordering for a list of objects
 * carrying a numeric `id`. Reorders optimistically on the client and calls
 * `persist` with the new `[{ id, order }]` payload on drop.
 */
export function useSortable<T extends { id: number }>(
    source: T[],
    persist: (payload: OrderedPayload[]) => void,
) {
    const [list, setList] = useState<T[]>(source);
    const [draggingId, setDraggingId] = useState<number | null>(null);

    // Re-sync during render whenever the server sends a fresh list (the
    // React-recommended alternative to syncing state inside an effect).
    const [knownSource, setKnownSource] = useState(source);

    if (source !== knownSource) {
        setKnownSource(source);
        setList(source);
    }

    function move(overId: number) {
        if (draggingId === null || draggingId === overId) {
            return;
        }

        setList((prev) => {
            const from = prev.findIndex((item) => item.id === draggingId);
            const to = prev.findIndex((item) => item.id === overId);

            if (from === -1 || to === -1) {
                return prev;
            }

            const next = [...prev];
            const [moved] = next.splice(from, 1);
            next.splice(to, 0, moved);

            return next;
        });
    }

    function commit() {
        if (draggingId === null) {
            return;
        }

        setDraggingId(null);
        persist(list.map((item, index) => ({ id: item.id, order: index })));
    }

    function itemProps(id: number): SortableHandleProps {
        return {
            draggable: true,
            onDragStart: () => setDraggingId(id),
            onDragEnter: () => move(id),
            onDragOver: (event: DragEvent) => event.preventDefault(),
            onDrop: () => commit(),
            onDragEnd: () => setDraggingId(null),
            'data-dragging': draggingId === id ? '' : undefined,
        };
    }

    return { list, draggingId, itemProps };
}
