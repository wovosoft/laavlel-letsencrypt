<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Certificate
 *
 * @property int $id
 * @property int $domain_id
 * @property \Illuminate\Support\Carbon $issue_date
 * @property \Illuminate\Support\Carbon $expiry_date
 * @property string $private_key
 * @property string $certificate
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Account|null $account
 * @property-read \App\Models\Domain $domain
 * @property-read bool $is_expired
 * @method static \Illuminate\Database\Eloquent\Builder|Certificate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Certificate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Certificate query()
 * @method static \Illuminate\Database\Eloquent\Builder|Certificate whereCertificate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Certificate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Certificate whereDomainId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Certificate whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Certificate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Certificate whereIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Certificate wherePrivateKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Certificate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Certificate extends Model
{
    use HasFactory;

    protected $casts = [
        "expiry_date" => "datetime",
        "issue_date"  => "datetime",
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function isExpired(): Attribute
    {
        return Attribute::get(fn(): bool => $this->expiry_date->lt(now()));
    }
}
