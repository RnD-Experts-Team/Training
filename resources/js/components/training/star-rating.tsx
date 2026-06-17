import { Star } from 'lucide-react';
import { useState } from 'react';
import { cn } from '@/lib/utils';

export function StarRating({
    value,
    onChange,
    readOnly = false,
    size = 'default',
}: {
    value: number | null;
    onChange?: (rating: number | null) => void;
    readOnly?: boolean;
    size?: 'default' | 'sm';
}) {
    const [hover, setHover] = useState<number | null>(null);
    const active = hover ?? value ?? 0;
    const starSize = size === 'sm' ? 'size-4' : 'size-5';

    return (
        <div className="flex items-center gap-0.5">
            {[1, 2, 3, 4, 5].map((star) => (
                <button
                    key={star}
                    type="button"
                    disabled={readOnly}
                    onMouseEnter={() => !readOnly && setHover(star)}
                    onMouseLeave={() => !readOnly && setHover(null)}
                    onClick={() => onChange?.(value === star ? null : star)}
                    className={cn(
                        'rounded-sm transition-colors',
                        !readOnly && 'cursor-pointer hover:scale-110',
                        readOnly && 'cursor-default',
                    )}
                    aria-label={`${star} star${star > 1 ? 's' : ''}`}
                >
                    <Star
                        className={cn(
                            starSize,
                            star <= active
                                ? 'fill-amber-400 text-amber-400'
                                : 'text-muted-foreground/40',
                        )}
                    />
                </button>
            ))}
        </div>
    );
}
