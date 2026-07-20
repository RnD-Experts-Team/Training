import { router } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';
import type { FlashToast } from '@/types/ui';

/** Clear wording for a failed request that never became an Inertia page. */
function messageForStatus(status?: number): string {
    switch (status) {
        case 413:
            return 'That file is too large for the server. Try a smaller file.';
        case 419:
            return 'Your session expired. Refresh the page and try again.';
        case 429:
            return 'Too many requests. Please wait a moment and try again.';
        case 502:
        case 503:
        case 504:
            return 'The server is busy or unavailable. If you were uploading a large file, try a smaller one.';
        default:
            return status
                ? `Something went wrong (error ${status}). Please try again.`
                : 'Something went wrong. Please try again.';
    }
}

export function useFlashToast(): void {
    useEffect(() => {
        const offFlash = router.on('flash', (event) => {
            const flash = (event as CustomEvent).detail?.flash;
            const data = flash?.toast as FlashToast | undefined;

            if (!data) {
                return;
            }

            toast[data.type](data.message);
        });

        // Responses Inertia can't turn into a page — e.g. an Apache 503/413 HTML
        // page produced when the request never reached (or killed) PHP. Without
        // this the user sees nothing at all and assumes the app hung.
        const offHttp = router.on('httpException', (event) => {
            const status = (event as CustomEvent).detail?.response?.status as
                | number
                | undefined;

            toast.error(messageForStatus(status));
        });

        const offNetwork = router.on('networkError', () => {
            toast.error(
                'Connection lost. Check your internet connection and try again.',
            );
        });

        return () => {
            offFlash();
            offHttp();
            offNetwork();
        };
    }, []);
}
