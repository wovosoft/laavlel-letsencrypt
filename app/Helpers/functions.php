<?php

use App\Helpers\Messages;
use Illuminate\Http\RedirectResponse;

if (!function_exists('success')) {
    function success(?array $message = []): RedirectResponse
    {
        return back()->with(...Messages::withSuccess($message));
    }
}

if (!function_exists('failed')) {
    function failed(Throwable $throwable, ?array $message = []): RedirectResponse
    {
        return back()
            ->with(...Messages::withFailed($throwable, $message));
    }
}

if (!function_exists("transaction")) {
    /**
     * @throws Throwable
     */
    function transaction(Closure $closure): RedirectResponse
    {
        $transaction = DB::transaction($closure);
        return success()->with(...Messages::withData($transaction));

//        try {
//            $transaction = DB::transaction($closure);
//            return success()->with(...Messages::withData($transaction));
//        } catch (Throwable $throwable) {
//            throw  $throwable;
//        }
    }
}
