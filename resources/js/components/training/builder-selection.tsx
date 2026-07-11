import { createContext, useContext } from 'react';

export type SelectionKind = 'category' | 'item';

export type BuilderSelection = {
    isSelected: (kind: SelectionKind, id: number) => boolean;
    toggle: (kind: SelectionKind, id: number) => void;
};

const BuilderSelectionContext = createContext<BuilderSelection | null>(null);

export const BuilderSelectionProvider = BuilderSelectionContext.Provider;

/**
 * Shared multi-select state for the content builder. Returns `null` outside a
 * provider so leaf components can opt out of rendering their checkboxes.
 */
export function useBuilderSelection(): BuilderSelection | null {
    return useContext(BuilderSelectionContext);
}
