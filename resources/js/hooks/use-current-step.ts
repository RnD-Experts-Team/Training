import { useEffect, useRef } from 'react';

/**
 * Returns a ref that scrolls its element into view when it becomes the
 * trainee's current step.
 */
export function useCurrentStep(isCurrent: boolean) {
    const ref = useRef<HTMLDivElement>(null);

    useEffect(() => {
        if (isCurrent && ref.current) {
            ref.current.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }, [isCurrent]);

    return ref;
}
