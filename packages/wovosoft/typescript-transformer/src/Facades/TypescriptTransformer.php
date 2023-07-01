<?php

namespace Wovosoft\TypescriptTransformer\Facades;

use Illuminate\Support\Facades\Facade;

class TypescriptTransformer extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'typescript-transformer';
    }
}
