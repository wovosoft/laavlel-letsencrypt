interface User{
	id: number
	name: string
	email: string
	email_verified_at: string
	password: string
	two_factor_secret: string
	two_factor_recovery_codes: string
	two_factor_confirmed_at: string
	remember_token: string
	current_team_id: number
	profile_photo_path: string
	created_at: string
	updated_at: string
}

interface Account{
	id: number
	user_id: number
	account_id: string
	email: string
	is_valid: "active" | "inactive"
	created_at: string
	updated_at: string
}

