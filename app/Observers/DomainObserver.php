<?php

namespace App\Observers;

use App\Models\Domain;
use App\Models\Order;

class DomainObserver
{
    /**
     * Handle the Domain "created" event.
     */
    public function created(Domain $domain): void
    {
        //
    }

    /**
     * Handle the Domain "updated" event.
     */
    public function updated(Domain $domain): void
    {
        //
    }

    /**
     * Handle the Domain "deleted" event.
     */
    public function deleted(Domain $domain): void
    {
        $domain->orders()->each(fn(Order $order) => $order->deleteOrFail());
    }

    /**
     * Handle the Domain "restored" event.
     */
    public function restored(Domain $domain): void
    {

    }

    /**
     * Handle the Domain "force deleted" event.
     */
    public function forceDeleted(Domain $domain): void
    {
        //
    }
}
