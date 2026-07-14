export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

/**
 * The shape Laravel's `LengthAwarePaginator` serializes to (top-level, not the
 * API-resource `data`/`meta` split).
 */
export type Paginated<T> = {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    prev_page_url: string | null;
    next_page_url: string | null;
};
