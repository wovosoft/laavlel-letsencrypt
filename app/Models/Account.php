<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Wovosoft\LaravelLetsencryptCore\LaravelClient;
use Wovosoft\LaravelLetsencryptCore\Ssl\ClientModes;

/**
 * App\Models\Account
 *
 * @property int $id
 * @property int $user_id
 * @property string|null $account_id
 * @property string $email
 * @property string|null $key
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Certificate> $certificates
 * @property-read int|null $certificates_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Domain> $domains
 * @property-read int|null $domains_count
 * @method static \Illuminate\Database\Eloquent\Builder|Account newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Account newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Account query()
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Account whereUserId($value)
 * @mixin \Eloquent
 */
class Account extends Model
{
    use HasFactory;

    /**
     * @throws \Exception
     */
    public function getAccount(): \Wovosoft\LaravelLetsencryptCore\Data\Account
    {
        $lc = new LaravelClient(
            mode: config("lets_encrypt.mode"),
            username: $this->email
        );
        return $lc->getAccount();
    }

    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    public function certificates(): HasManyThrough
    {
        return $this->hasManyThrough(
            Certificate::class,
            Domain::class
        );
    }
}
