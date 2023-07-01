export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: User;
    };
};

export type DatatableType<T> = {
    current_page: number,
    data: T[],
    first_page_url: string,
    from: number | null
    last_page: number
    last_page_url: string
    links: {
        url: string,
        label: string
        active: boolean
    }[],
    next_page_url: string | null
    path: string
    per_page: number
    prev_page_url: null | string
    to: null | number,
    total: number
}
