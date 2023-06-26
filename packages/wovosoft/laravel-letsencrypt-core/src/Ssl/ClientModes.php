<?php

namespace Wovosoft\LaravelLetsencryptCore\Ssl;


use Wovosoft\LaravelLetsencryptCore\LaravelClient;

enum ClientModes: string
{
    case Live = LaravelClient::MODE_LIVE;
    case Staging = LaravelClient::MODE_STAGING;
}
