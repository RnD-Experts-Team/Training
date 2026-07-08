import { cn } from '@/lib/utils';

const PRESETS = [0, 25, 50, 75, 100];

/**
 * Editable 0–100 performance score. `null` means "not rated yet" (distinct
 * from a deliberate 0%). Friendly slider + quick-pick presets.
 */
export function RatingInput({
    value,
    onChange,
    disabled = false,
}: {
    value: number | null;
    onChange: (value: number | null) => void;
    disabled?: boolean;
}) {
    return (
        <div className="space-y-2">
            <div className="flex items-center justify-between">
                <span className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                    Score
                </span>
                <span
                    className={cn(
                        'text-sm font-semibold tabular-nums',
                        value === null
                            ? 'text-muted-foreground'
                            : 'text-foreground',
                    )}
                >
                    {value === null ? 'Not rated' : `${value}%`}
                </span>
            </div>

            <input
                type="range"
                min={0}
                max={100}
                step={5}
                value={value ?? 0}
                disabled={disabled}
                onChange={(e) => onChange(Number(e.target.value))}
                aria-label="Performance score"
                className="w-full cursor-pointer accent-primary disabled:cursor-not-allowed disabled:opacity-50"
            />

            <div className="flex flex-wrap items-center gap-1.5">
                {PRESETS.map((preset) => (
                    <button
                        key={preset}
                        type="button"
                        disabled={disabled}
                        onClick={() => onChange(preset)}
                        className={cn(
                            'rounded-full border px-2 py-0.5 text-xs tabular-nums transition-colors disabled:opacity-50',
                            value === preset
                                ? 'border-primary bg-primary/10 font-medium text-primary'
                                : 'text-muted-foreground hover:bg-muted',
                        )}
                    >
                        {preset}%
                    </button>
                ))}
                {value !== null && !disabled && (
                    <button
                        type="button"
                        onClick={() => onChange(null)}
                        className="ml-auto text-xs text-muted-foreground hover:text-foreground"
                    >
                        Clear
                    </button>
                )}
            </div>
        </div>
    );
}
