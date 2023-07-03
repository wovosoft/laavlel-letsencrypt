<?php

namespace App\Http\Controllers;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use League\Flysystem\FilesystemException;
use Wovosoft\LaravelLetsencryptCore\Data\Challenge;
use Wovosoft\LaravelLetsencryptCore\Data\Order;
use Wovosoft\LaravelLetsencryptCore\Enums\Modes;
use Wovosoft\LaravelLetsencryptCore\Enums\LetsEncrypt;

class GuestCertificateController extends Controller
{
    public static function routes()
    {
        Route::prefix('guest-certificates')
            ->name('guest-certificates.')
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
            "title" => "Create Order",
        ]);
    }


    /**
     * @throws \Exception|GuzzleException
     * @throws FilesystemException
     */
    public function authorizeOrder(Request $request)
    {
        if ($request->isMethod('GET')) {
            return to_route('guest-certificates.create-order');
        }

        $le = new LetsEncrypt(
            username: $request->input('email'),
            mode: Modes::Staging
        );

        $order = $le->createOrder(
            domains: $request->input('domains')
        );

        if ($order->isReady()) {
            return Inertia::render(
                "GuestSsl/Certificates",
                $this->generateCertificates($le, $order)
            );
        }

        $authorizations = $le->authorize($order);

        return Inertia::render("GuestSsl/Authorization", [
            "title" => "Authorize Order",
            "order" => [
                "email" => $request->input('email'),
                ...$le->transformOrder($order, $authorizations),
            ],
        ]);
    }

    /**
     * @throws \Exception
     * @throws GuzzleException|FilesystemException
     */
    public function validateDomain(Request $request)
    {
        if ($request->isMethod('GET')) {
            return to_route('certificates.create-order');
        }
        $le = new LetsEncrypt(
            username: $request->input('email'),
            mode: Modes::Staging
        );

        $challenge = new Challenge(...$request->input('challenge'));

        $status = $le->getClient()->validate($challenge);
        if ($status) {
            $order = $le->getOrder($request->input('order_id'));

            return Inertia::render(
                "GuestSsl/Certificates",
                $this->generateCertificates($le, $order)
            );
        }
        throw new \Exception("Unable to verify Domain");
    }


    /**
     * @throws FilesystemException
     * @throws GuzzleException
     */
    private function generateCertificates(LetsEncrypt $le, Order $order): ?array
    {
        if ($order->isReady()) {
            $certificate = $le->getCertificate($order);
            return [
                "certificate" => $certificate->getCertificate(),
                "privateKey" => $certificate->getPrivateKey(),
            ];
        }
        return null;
    }
}
