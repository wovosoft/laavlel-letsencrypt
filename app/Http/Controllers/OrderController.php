<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderStoreRequest;
use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public static function routes(): void
    {
        Route::prefix('orders')
            ->name('orders.')
            ->controller(static::class)
            ->group(function () {
                Route::match(['get', 'post'], '/', 'index')->name('index');
                Route::put('store', 'store')->name('store');
                Route::match(['get', 'post'], 'options', 'options')->name('options');
            });
    }

    public function store(OrderStoreRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $order = new Order();
            $order->forceFill($request->validated());
            $order->saveOrFail();

            return back()->with('notification', [
                "message" => "Successfully Done",
                "variant" => "primary"
            ]);
        });
    }

    public function index(Request $request): Response
    {
        return Inertia::render("Orders/Index", [
            "title" => "Order List",
            "items" => fn() => Order::query()
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
}
