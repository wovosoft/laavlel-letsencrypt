<?php

namespace Wovosoft\LaravelLetsencryptCore\Ssl;


use Wovosoft\LaravelLetsencryptCore\LaravelClient;

enum ValidationTypes: string
{
    case Http = LaravelClient::VALIDATION_HTTP;
    case Dns = LaravelClient::VALIDATION_DNS;
}
