<?php

namespace App\Http\Controllers;

use App\Helpers\Messages;
use App\Http\Requests\AccountStoreRequest;
use App\Models\Account;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Wovosoft\LaravelLetsencryptCore\Client;
use Wovosoft\LaravelLetsencryptCore\Enums\Modes;

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
                Route::post('verify/{account}', 'verify')->name('verify');
            });
    }

    /**
     * @throws \Exception|\Throwable
     */
    public function verify(Account $account)
    {
        if ($account->user_id !== auth()->id()) {
            throw new \Exception("Account doesn't belongs to You");
        }

        $lc = new Client(
            mode: Modes::Staging,
            username: $account->email
        );

        $leAccount = $lc->getAccount();
        $account->account_id = $leAccount->getId();
        $account->is_valid = $leAccount->isValid();
        $account->saveOrFail();
        return success()
            ->with(...Messages::withData([
                "item"       => $account,
                "le_account" => $leAccount->toArray()
            ]));

    }

    /**
     * @throws \Throwable
     */
    public function store(AccountStoreRequest $request)
    {
        return transaction(function () use ($request) {
            $account = new Account();
            $account->forceFill($request->validated());
            $account->is_valid = false;
            $request->user()->accounts()->save(
                $account
            );

            return [
                "account" => $account
            ];
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

    /**
     * @throws \Exception
     */
    public function delete(Account $account)
    {
        if ($account->user_id !== auth()->id()) {
            throw new \Exception("Account doesn't belongs to You");
        }

        return DB::transaction(function () use ($account) {
            $account->deleteOrFail();
            return success();
        });
    }

    public function index(Request $request): Response
    {
        $items = fn() => $request->user()
            ->accounts()
            ->select([
                'id',
                'user_id',
                'account_id',
                'is_valid',
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
