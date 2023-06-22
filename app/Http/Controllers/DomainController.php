<?php

namespace App\Http\Controllers;

use App\Http\Requests\DomainStoreRequest;
use App\Models\Account;
use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class DomainController extends Controller
{
    public static function routes()
    {
        Route::prefix('domains')
            ->name('domains.')
            ->controller(static::class)
            ->group(function () {
                Route::match(['get', 'post'], '/', 'index')->name('index');
                Route::put('store/on-account/{account}', 'store')->name('store');
                Route::match(['get', 'post'], 'options', 'options')->name('options');
            });
    }

    public function store(DomainStoreRequest $request, Account $account)
    {
        return DB::transaction(function () use ($account, $request) {
            $domain = new Domain();
            $domain->forceFill($request->validated());
            $domain->is_ownership_verified = false;
            $account->domains()->save($domain);

            return back()->with('notification', [
                "message" => "Successfully Done",
                "item" => $domain
            ]);
        });
    }


    public function options(Request $request)
    {
        $items = fn() => $request
            ->user()
            ->domains()
            ->select([
                DB::raw('id as value'),
                DB::raw('domain as text')
            ])
            ->limit(30)
            ->get();

        return Inertia::render("Domains/Index", [
            "title" => "My Domains",
            "items" => $items
        ]);
    }

    public function index(Request $request)
    {
        $items = fn() => $request
            ->user()
            ->domains()
            ->with(['account:id,email'])
            ->select([
                'domains.id',
                'domains.account_id',
                'domains.domain',
                'domains.created_at'
            ])
            ->paginate(
                perPage: $request->input('per_page') ?: 15
            )
            ->appends($request->input());

        return Inertia::render("Domains/Index", [
            "title" => "My Domains",
            "items" => $items
        ]);
    }
}
