<?php

namespace App\Http\Controllers;

use App\Ssl\ClientModes;
use App\Ssl\LetsEncrypt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class CertificateController extends Controller
{
    public static function routes()
    {
        Route::prefix('certificates')
            ->name('certificates.')
            ->controller(static::class)
            ->group(function () {
                Route::post('create-order', 'createOrder')->name('create-order');
            });
    }

    /**
     * @throws \Exception
     */
    public function createOrder(Request $request)
    {
        $le = new LetsEncrypt(
            username: $request->input('email'),
            mode: ClientModes::Live
        );

        return $le->createOrder(
            domains: $request->input('domains')
        );
    }
}
