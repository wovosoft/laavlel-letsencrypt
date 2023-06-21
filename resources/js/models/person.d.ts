type Person = {
    id: number;
    first_name: string;
    last_name: string | null;
    full_name: string | number;
    phone: string | null;
    email: string | null;
    dob: string /* Date */ | null;
    address: string | null;
    description: any | null // NOT FOUND;
    created_at: string /* Date */ | null;
    updated_at: string /* Date */ | null;
}
