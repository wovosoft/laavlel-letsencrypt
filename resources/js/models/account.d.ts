type Account = {
    id: number;
    person_id: number;
    account_no: string;
    opening_date: string /* Date */;
    closing_date: string /* Date */ | null;
    balance: string /* Date */;
    status: boolean;
    created_at: string /* Date */ | null;
    updated_at: string /* Date */ | null;
}
