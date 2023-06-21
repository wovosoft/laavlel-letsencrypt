<?php

namespace App\Http\Controllers;

use Afosto\Acme\Data\Challenge;
use App\Ssl\ClientModes;
use App\Ssl\LetsEncrypt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

class CertificateController extends Controller
{
    public static function routes()
    {
        Route::prefix('certificates')
            ->name('certificates.')
            ->controller(static::class)
            ->group(function () {
                Route::get('create-order', 'createOrder')->name('create-order');
                Route::match(['get', 'post'], 'authorize-order', 'authorizeOrder')->name('authorize-order');
                Route::match(['get', 'post'], 'validate-domain', 'validateDomain')->name('validate-domain');
            });
    }

    public function createOrder(Request $request)
    {
        return Inertia::render("GuestSsl/Order", [
            "title" => "Create Order"
        ]);
    }


    /**
     * @throws \Exception
     */
    public function authorizeOrder(Request $request)
    {
        if ($request->isMethod('GET')) {
            return to_route('certificates.create-order');
        }

        $le = new LetsEncrypt(
            username: $request->input('email'),
            mode: ClientModes::Live
        );

        $order = $le->createOrder(
            domains: $request->input('domains')
        );

        $authorizations = $le->authorize($order);

        return Inertia::render("GuestSsl/Authorization", [
            "title" => "Authorize Order",
            "order" => [
                "email" => $request->input('email'),
                ...$le->transformOrder($order, $authorizations)
            ]
        ]);
    }

    /**
     * @throws \Exception
     */
    public function validateDomain(Request $request)
    {
        if ($request->isMethod('GET')) {
            return to_route('certificates.create-order');
        }
        $le = new LetsEncrypt(
            username: $request->input('email'),
            mode: ClientModes::Live
        );
        $challenge = new Challenge(...$request->input('challenge'));
        $status = $le->getClient()->validate($challenge);
        if ($status) {
            $order = $le->getOrder($request->input('order_id'));
            if ($le->getClient()->isReady($order)) {
                $certificate = $le->getCertificate($order);
                return [
                    "certificate" => $certificate->getCertificate(),
                    "private_key" => $certificate->getPrivateKey()
                ];
            }
        }
        throw new \Exception("Unable to verify Domain");
    }
}
