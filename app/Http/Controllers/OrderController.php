<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderStoreRequest;
use App\Models\Domain;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Wovosoft\LaravelLetsencryptCore\Client;
use Wovosoft\LaravelLetsencryptCore\Data\Authorization;
use Wovosoft\LaravelLetsencryptCore\Data\Challenge;

class OrderController extends Controller
{
    private ?Client $client = null;

    /**
     * @throws \Exception
     */
    private function le(Domain $domain): Client
    {
        if (!$this->client) {
            $this->client = $domain->leClient();
        }
        return $domain->leClient();
    }

    public static function routes(): void
    {
        Route::prefix('orders')
            ->name('orders.')
            ->controller(static::class)
            ->group(function () {
                Route::match(['get', 'post'], '/', 'index')->name('index');
                Route::put('store/for-domain/{domain}', 'store')->name('store');
                Route::match(['get', 'post'], 'options', 'options')->name('options');
                Route::post('get-authorizations/{order}', 'getAuthorizations')->name('get-authorizations');
                Route::post('validate-challenge/{order}', 'validateChallenge')->name('validate-challenge');
            });
    }

    public function store(OrderStoreRequest $request, Domain $domain)
    {
        return DB::transaction(function () use ($domain, $request) {
            $order = new Order();
            $order->forceFill($request->validated());
            $domain->orders()->save($order);

            $leOrder = $order->leOrder();

            return back()->with('notification', [
                "message" => "Successfully Done",
                "variant" => "primary",
                "le_order" => $leOrder->toArray()
            ]);
        });
    }

    public function index(Request $request): Response
    {
        return Inertia::render("Orders/Index", [
            "title" => "Order List",
            "items" => fn() => Order::query()
                ->whereExists(function (\Illuminate\Database\Query\Builder $builder) {
                    $builder
                        ->select("accounts.*")
                        ->from("accounts")
                        ->join("domains", "domains.account_id", "=", DB::raw("accounts.id"))
                        ->where("accounts.user_id", "=", auth()->id())
                        ->where("orders.domain_id", "=", DB::raw("domains.id"));
                })
                ->with(['domain'])
                ->when($request->input('query'), function (Builder $builder, string $query) {
                    $builder->where('domain', 'like', "%$query%");
                })
                ->paginate(
                    perPage: $request->input('per_page') ?: 15
                )
                ->appends($request->input())
        ]);
    }

    public function options(Request $request)
    {
        return Order::query()
            ->when($request->input('query'), function (Builder $builder, string $query) {
                $builder->where('domain', 'like', "%$query%");
            })
            ->limit(30)
            ->get();
    }


    /**
     * @throws \Throwable
     */
    public function getAuthorizations(Request $request, Order $order)
    {
        return $order->leAuthorizations()->toArray();
    }

    /**
     * @throws \Throwable
     */
    public function validateChallenge(Request $request, Order $order)
    {
        $http = Http::get($request->input('auth_url'));
        $challenge = collect($http->collect()->get('challenges'))
            ->first(fn($item) => $item['type'] === 'http-01');
        $challenge = new Challenge(
            ...[
                "authorizationURL" => $request->input('auth_url'),
                ...(array)$challenge
            ]
        );
        return $order->validateLeOrder($challenge);
//        return ->validate();
    }
}
