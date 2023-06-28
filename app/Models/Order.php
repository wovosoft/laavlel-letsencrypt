<?php

namespace App\Models;

use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Collection;
use Throwable;
use Wovosoft\LaravelLetsencryptCore\Data\Authorization;

/**
 * App\Models\Order
 *
 * @property int $id
 * @property int $domain_id
 * @property string|null $order_id
 * @property string|null $expires
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Account|null $account
 * @property-read \App\Models\Domain $domain
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDomainId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereExpires($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Order extends Model
{
    use HasFactory;

    protected $casts = [
        "expires" => "datetime"
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function account(): HasOneThrough
    {
        return $this->hasOneThrough(
            Account::class,
            Domain::class
        );
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    public function leOrder(): \Wovosoft\LaravelLetsencryptCore\Data\Order
    {
        if ($this->order_id) {
            return $this->domain->leClient()->getOrder($this->order_id);
        }

        $order = $this->domain->leClient()->createOrder(
            $this->domain->pluck('domain')->toArray()
        );

        $this->order_id = $order->getId();
        $this->expires = $order->getExpiresAt();

        $this->saveOrFail();

        return $order;
    }

    /**
     * @return Collection
     * @throws Throwable
     */
    public function leAuthorizations(): Collection
    {
        return $this->domain->leClient()->authorize(
            $this->leOrder()
        );
    }
}
