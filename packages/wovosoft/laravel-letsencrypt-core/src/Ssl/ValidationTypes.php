<?php

namespace Wovosoft\LaravelLetsencryptCore\Ssl;


use Wovosoft\LaravelLetsencryptCore\Client;

enum ValidationTypes: string
{
    case Http = Client::VALIDATION_HTTP;
    case Dns = Client::VALIDATION_DNS;
}
