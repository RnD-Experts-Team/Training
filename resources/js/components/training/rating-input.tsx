import { cn } from '@/lib/utils';

const PRESETS = [0, 25, 50, 75, 100];

/**
 * Editable 0–100 performance score. `null` means "not rated yet" (distinct
 * from a deliberate 0%). Quick-pick presets plus a precise number field.
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
    const handleNumberChange = (raw: string) => {
        if (raw === '') {
            onChange(null);

            return;
        }

        const parsed = Number(raw);

        if (Number.isNaN(parsed)) {
            return;
        }

        onChange(Math.max(0, Math.min(100, Math.round(parsed))));
    };

    return (
        <div className="space-y-2">
            <div className="flex items-center justify-between gap-2">
                <span className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                    Score
                </span>
                <div className="flex items-center gap-1">
                    <input
                        type="number"
                        inputMode="numeric"
                        min={0}
                        max={100}
                        value={value ?? ''}
                        disabled={disabled}
                        placeholder="—"
                        onChange={(e) => handleNumberChange(e.target.value)}
                        aria-label="Performance score"
                        className="h-8 w-16 rounded-md border border-input bg-transparent px-2 text-right text-sm font-semibold tabular-nums shadow-xs focus-visible:border-ring focus-visible:ring-[3px] focus-visible:ring-ring/50 focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                    />
                    <span className="text-sm font-semibold text-muted-foreground">
                        %
                    </span>
                </div>
            </div>

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
