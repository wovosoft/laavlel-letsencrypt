<?php

namespace App\Ssl;

use Afosto\Acme\Client;

enum ValidationTypes: string
{
    case Http = Client::VALIDATION_HTTP;
    case Dns = Client::VALIDATION_DNS;
}
