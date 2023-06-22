<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountStoreRequest;
use App\Models\Account;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public static function routes()
    {
        Route::prefix('accounts')
            ->name('accounts.')
            ->controller(static::class)
            ->group(function () {
                Route::match(['get', 'post'], '/', 'index')->name('index');
                Route::match(['get', 'post'], 'options', 'options')->name('options');
                Route::put('store', 'store')->name('store');
            });
    }

    public function store(AccountStoreRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $account = new Account();
            $account->forceFill($request->validated());
            $request->user()->accounts()->save(
                $account
            );

            return back()->with('notification', [
                'message' => "Successfully Done",
                'variant' => 'primary',
                'item' => $account
            ]);
        });
    }

    public function options(Request $request)
    {
        return $request
            ->user()
            ->accounts()
            ->select([
                'id',
                'user_id',
                'email'
            ])
            ->when($request->input('query'), function (Builder $builder, string $query) {
                $builder->where('email', 'like', "%$query%");
            })
            ->limit(30)
            ->get();
    }

    public function index(Request $request): Response
    {
        $items = fn() => $request->user()
            ->accounts()
            ->select([
                'id',
                'user_id',
                'email',
                'created_at'
            ])
            ->paginate(
                perPage: $request->input('per_page') ?: 15
            )
            ->appends($request->input());

        return Inertia::render("Accounts/Index", [
            "title" => "My Accounts",
            "items" => $items
        ]);
    }
}
