<?php

namespace Wovosoft\LaravelLetsencryptCore\Ssl;


use Wovosoft\LaravelLetsencryptCore\Client;

enum ClientModes: string
{
    case Live = Client::MODE_LIVE;
    case Staging = Client::MODE_STAGING;
}
