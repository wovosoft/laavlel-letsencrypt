export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: App.Models.User;
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

export type AuthorizationType = "http-01" | "dns-01" | "tls-alpn-01";
export type LeOrderType = "pending" | "ready";

export interface AuthorizationFile {
    filename: string
    contents: string
}

export interface TxtRecord {
    name: string
    value: string
}

export interface AuthorizationChallenge {
    "authorizationURL": string
    "type": AuthorizationType
    "status": LeOrderType
    "url": string
    "token": string
}

export interface OrderAuthorization {
    "domain": string,
    "expires": string
    "challenges": AuthorizationChallenge[],
    "file": AuthorizationFile,
    "txt_record": TxtRecord
}
