<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Wovosoft\LaravelLetsencryptCore\Client;

/**
 * App\Models\Domain
 *
 * @property int                                           $id
 * @property int                                           $account_id
 * @property string                                        $domain
 * @property bool                                          $is_ownership_verified
 * @property Carbon|null                                   $created_at
 * @property Carbon|null                                   $updated_at
 * @property-read \App\Models\Account                      $account
 * @property-read Collection<int, \App\Models\Certificate> $certificates
 * @property-read int|null                                 $certificates_count
 * @property-read Collection<int, \App\Models\Order>       $orders
 * @property-read int|null                                 $orders_count
 * @method static \Illuminate\Database\Eloquent\Builder|Domain newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Domain newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Domain query()
 * @method static \Illuminate\Database\Eloquent\Builder|Domain whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Domain whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Domain whereDomain($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Domain whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Domain whereIsOwnershipVerified($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Domain whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Domain extends Model
{
    use HasFactory;

    private ?Client $client = null;
    protected $with = [
        "account"
    ];

    protected $casts = [
        "is_ownership_verified" => "boolean"
    ];

    /**
     * @throws \Exception
     */
    public function leClient(bool $refresh = false): Client
    {
        if ($this->client && !$refresh) {
            return $this->client;
        }
        return new Client(
            mode: config("laravel-letsencrypt-core.mode"),
            username: $this->account?->email
        );
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
