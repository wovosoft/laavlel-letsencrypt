declare namespace App.Models{ 
	export interface Certificate {
		id: number;
		domain_id: number;
		issue_date: string;
		expiry_date: string;
		private_key: string;
		certificate: string;
		created_at: string;
		updated_at: string;
		account?: Account | null;
		domain?: Domain | null;
		is_expired: boolean;
	}

	export interface Domain {
		id: number;
		account_id: number;
		domain: string;
		is_ownership_verified: boolean;
		created_at: string;
		updated_at: string;
		account?: Account | null;
		certificates?: Certificate[] | null;
		orders?: Order[] | null;
	}

	export interface Account {
		id: number;
		user_id: number;
		account_id: string;
		email: string;
		is_valid: "active" | "inactive";
		created_at: string;
		updated_at: string;
		domains?: Domain[] | null;
		certificates?: Certificate[] | null;
	}

	export interface Order {
		id: number;
		domain_id: number;
		order_id: string;
		expires: string;
		created_at: string;
		updated_at: string;
		domain?: Domain | null;
		account?: Account | null;
	}

	export interface User {
		id: number;
		name: string;
		email: string;
		email_verified_at: string;
		password: string;
		two_factor_secret: string;
		two_factor_recovery_codes: string;
		two_factor_confirmed_at: string;
		remember_token: string;
		current_team_id: number;
		profile_photo_path: string;
		created_at: string;
		updated_at: string;
		accounts?: Account[] | null;
		domains?: Domain[] | null;
		profile_photo_url: unknown;
	}
}