// Keep in sync with App\Concerns\TrainingValidationRules::CATEGORY_COLORS (PHP).
export const CATEGORY_COLORS = [
    'slate',
    'rose',
    'orange',
    'amber',
    'emerald',
    'teal',
    'sky',
    'violet',
] as const;

export type CategoryColor = (typeof CATEGORY_COLORS)[number];

type ColorClasses = { dot: string; border: string; tint: string };

// Literal class strings so Tailwind's content scanner keeps them.
export const CATEGORY_COLOR_CLASSES: Record<CategoryColor, ColorClasses> = {
    slate: {
        dot: 'bg-slate-400',
        border: 'border-l-slate-400',
        tint: 'bg-slate-50 dark:bg-slate-900/30',
    },
    rose: {
        dot: 'bg-rose-500',
        border: 'border-l-rose-500',
        tint: 'bg-rose-50 dark:bg-rose-950/30',
    },
    orange: {
        dot: 'bg-orange-500',
        border: 'border-l-orange-500',
        tint: 'bg-orange-50 dark:bg-orange-950/30',
    },
    amber: {
        dot: 'bg-amber-500',
        border: 'border-l-amber-500',
        tint: 'bg-amber-50 dark:bg-amber-950/30',
    },
    emerald: {
        dot: 'bg-emerald-500',
        border: 'border-l-emerald-500',
        tint: 'bg-emerald-50 dark:bg-emerald-950/30',
    },
    teal: {
        dot: 'bg-teal-500',
        border: 'border-l-teal-500',
        tint: 'bg-teal-50 dark:bg-teal-950/30',
    },
    sky: {
        dot: 'bg-sky-500',
        border: 'border-l-sky-500',
        tint: 'bg-sky-50 dark:bg-sky-950/30',
    },
    violet: {
        dot: 'bg-violet-500',
        border: 'border-l-violet-500',
        tint: 'bg-violet-50 dark:bg-violet-950/30',
    },
};

export function categoryColorClasses(
    color: string | null,
): ColorClasses | null {
    if (color && color in CATEGORY_COLOR_CLASSES) {
        return CATEGORY_COLOR_CLASSES[color as CategoryColor];
    }

    return null;
}
