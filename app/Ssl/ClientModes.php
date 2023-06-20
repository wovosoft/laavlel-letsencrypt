<?php

namespace App\Ssl;

use Afosto\Acme\Client;

enum ClientModes: string
{
    case Live = Client::MODE_LIVE;
    case Staging = Client::MODE_STAGING;
}
