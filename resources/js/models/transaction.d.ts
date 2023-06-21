type Transaction = {
    id: number;
    account_id: number;
    amount: number;
    status: string;
    type: string;
    created_at: string /* Date */ | null;
    updated_at: string /* Date */ | null;
}
