<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class CertificateController extends Controller
{
    public static function routes()
    {
        Route::prefix('certificates')
            ->name('certificates.')
            ->controller(static::class)
            ->group(function () {
                Route::match(['get', 'post'], '/', 'index');
            });
    }

    /**
     * @throws \Exception
     */
    public function index(Request $request, Domain $domain): Response
    {
        if (!$domain->account()->where('user_id', '=', auth()->id())->exists()) {
            throw new \Exception("$domain doesn't belongs to You");
        }

        return Inertia::render("Certificates/Index", [
            "title" => $domain->domain . " certificates",
            "items" => $domain
                ->certificates()
                ->with(['domain:id,domain'])
                ->paginate(
                    perPage: $request->input('per_page') ?: 15
                )
                ->appends($request->input()),
        ]);
    }
}
